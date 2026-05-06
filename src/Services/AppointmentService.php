<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\AppointmentRepository;

class AppointmentService
{
    private AppointmentRepository $repo;

    public function __construct()
    {
        $this->repo = new AppointmentRepository();
    }

    /**
     * Генерирует список слотов для врача на указанную дату.
     *
     * Возвращает массив:
     * [
     *   ['time' => '09:00', 'datetime' => '2025-06-10 09:00:00', 'available' => true],
     *   ['time' => '09:30', 'datetime' => '2025-06-10 09:30:00', 'available' => false],
     *   ...
     * ]
     */
    public function getSlots(int $doctorId, string $date): array
    {
        // 1. Не показываем прошедшие даты
        if (strtotime($date) < strtotime('today')) {
            return [];
        }

        // 2. Проверяем исключение (отпуск / выходной)
        $exception = $this->repo->getException($doctorId, $date);
        if ($exception && $exception['is_day_off']) {
            return [];
        }

        // 3. date('N') → 1=Пн .. 7=Вс — совпадает с нашим day_of_week
        $dayOfWeek = (int)date('N', strtotime($date));
        $schedule  = $this->repo->getScheduleForDay($doctorId, $dayOfWeek);

        if (!$schedule) {
            return []; // врач не работает в этот день
        }

        // 4. Получаем занятые слоты
        $bookedTimes = $this->repo->getBookedTimes($doctorId, $date);

        // 5. Генерируем все слоты
        $slots       = [];
        $slotSeconds = (int)$schedule['slot_duration_min'] * 60;
        $current     = strtotime($date . ' ' . $schedule['start_time']);
        $end         = strtotime($date . ' ' . $schedule['end_time']);

        while ($current + $slotSeconds <= $end) {
            $timeStr = date('H:i', $current);

            // Не показываем уже прошедшее время сегодня
            $isPast = ($date === date('Y-m-d')) && ($current <= time());

            $slots[] = [
                'time'      => $timeStr,
                'datetime'  => $date . ' ' . $timeStr . ':00',
                'available' => !$isPast && !in_array($timeStr, $bookedTimes, true),
            ];

            $current += $slotSeconds;
        }

        return $slots;
    }

    /**
     * Возвращает ближайшие N дней, в которые врач работает
     * — для подсказки пользователю какие даты выбирать
     */
    public function getWorkingDays(int $doctorId, int $count = 14): array
    {
        $days   = [];
        $ts     = strtotime('today');

        for ($i = 0; $i < 60 && count($days) < $count; $i++) {
            $date      = date('Y-m-d', $ts);
            $dayOfWeek = (int)date('N', $ts);

            $schedule  = $this->repo->getScheduleForDay($doctorId, $dayOfWeek);
            $exception = $this->repo->getException($doctorId, $date);

            if ($schedule && (!$exception || !$exception['is_day_off'])) {
                $days[] = $date;
            }

            $ts += 86400;
        }

        return $days;
    }

    /**
     * Забронировать слот — с финальными проверками
     * Возвращает массив ошибок (пустой = успех)
     */
    public function book(
        int    $patientId,
        int    $doctorId,
        string $date,
        string $time
    ): array {
        $errors = [];

        // Проверка формата даты и времени
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
            !preg_match('/^\d{2}:\d{2}$/', $time)) {
            $errors['general'] = 'Некорректные данные.';
            return $errors;
        }

        $scheduledAt = $date . ' ' . $time . ':00';

        // Дата не в прошлом
        if (strtotime($scheduledAt) <= time()) {
            $errors['general'] = 'Нельзя записаться на прошедшее время.';
            return $errors;
        }

        // Слот действительно существует и свободен
        $slots     = $this->getSlots($doctorId, $date);
        $slotTimes = array_column(
            array_filter($slots, fn($s) => $s['available']),
            'time'
        );

        if (!in_array($time, $slotTimes, true)) {
            $errors['general'] = 'Выбранный слот недоступен. Пожалуйста, выберите другое время.';
            return $errors;
        }

        // Бронируем внутри транзакции с блокировкой — защита от race condition
        try {
            $this->repo->transaction(function () use ($patientId, $doctorId, $scheduledAt): void {
                // SELECT FOR UPDATE удерживает строку до commit/rollback,
                // не позволяя параллельному запросу пройти проверку одновременно
                if ($this->repo->lockSlot($doctorId, $scheduledAt)) {
                    throw new \DomainException('Выбранный слот уже занят. Пожалуйста, выберите другое время.');
                }

                if ($this->repo->alreadyBooked($patientId, $doctorId, $scheduledAt)) {
                    throw new \DomainException('У вас уже есть запись к этому врачу в выбранное время.');
                }

                $this->repo->create($patientId, $doctorId, $scheduledAt);
            });
        } catch (\DomainException $e) {
            $errors['general'] = $e->getMessage();
            return $errors;
        } catch (\PDOException $e) {
            // Страховка: уникальный индекс uq_appt_doctor_active_slot поймает
            // дубль, если транзакция по какой-то причине не сработала
            if ($e->getCode() === '23000') {
                $errors['general'] = 'Выбранный слот уже занят. Пожалуйста, выберите другое время.';
                return $errors;
            }
            throw $e;
        }

        return [];
    }
}