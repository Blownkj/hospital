<?php
declare(strict_types=1);

namespace App\Core;

class Paginator
{
    public readonly int $totalPages;
    public readonly int $offset;

    public function __construct(
        public readonly int $total,
        public readonly int $perPage,
        public readonly int $currentPage,
    ) {
        $this->totalPages = max(1, (int) ceil($total / max(1, $perPage)));
        $this->offset     = ($currentPage - 1) * $perPage;
    }

    public function hasPrev(): bool { return $this->currentPage > 1; }
    public function hasNext(): bool { return $this->currentPage < $this->totalPages; }
    public function prevPage(): int { return max(1, $this->currentPage - 1); }
    public function nextPage(): int { return min($this->totalPages, $this->currentPage + 1); }

    /** Компактный диапазон страниц вокруг текущей (для отрисовки кнопок) */
    public function pages(int $around = 2): array
    {
        $start = max(1, $this->currentPage - $around);
        $end   = min($this->totalPages, $this->currentPage + $around);
        return range($start, $end);
    }
}
