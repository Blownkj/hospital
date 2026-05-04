<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Middleware\AuthMiddleware;
use App\Repositories\ArticleRepository;
use App\Repositories\DoctorRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\ServiceRepository;
use App\Repositories\StatisticsRepository;

class PublicController extends BaseController
{
    private DoctorRepository     $doctors;
    private ServiceRepository    $services;
    private ArticleRepository    $articles;
    private StatisticsRepository $statsRepo;

    public function __construct()
    {
        $this->doctors   = new DoctorRepository();
        $this->services  = new ServiceRepository();
        $this->articles  = new ArticleRepository();
        $this->statsRepo = new StatisticsRepository();
    }

    // GET /
    public function home(): void
    {
        $doctors = $this->doctors->getAllWithRating();

        $stats = [
            'doctors'  => count($doctors),
            'patients' => $this->statsRepo->getPatientCount(),
            'reviews'  => $this->statsRepo->getReviewCount(),
        ];

        View::render('public/home', [
            'pageTitle'       => 'Главная',
            'doctors'         => $doctors,
            'stats'           => $stats,
            'latestReviews'   => $this->statsRepo->getLatestReviews(),
            'specializations' => $this->statsRepo->getSpecializations(),
            'recentArticles'  => $this->articles->getRecent(3),
        ]);
    }

        public function about(): void
    {
        View::render('public/about', [
            'pageTitle' => 'О клинике',
        ]);
    }

    public function faq(): void
    {
        $questions = [
            [
                'q' => 'Как записаться на приём?',
                'a' => 'Зарегистрируйтесь на сайте, войдите в личный кабинет и нажмите «Записаться к врачу». Выберите специализацию, врача, удобную дату и время.',
            ],
            [
                'q' => 'Как отменить запись?',
                'a' => 'В личном кабинете перейдите в раздел «Мои записи» и нажмите «Отменить» напротив нужной записи. Отменить можно не позднее чем за час до приёма.',
            ],
            [
                'q' => 'Как получить результаты анализов?',
                'a' => 'Результаты анализов отображаются в вашей медицинской карте в личном кабинете после того как врач их внесёт.',
            ],
            [
                'q' => 'Можно ли записаться к конкретному врачу?',
                'a' => 'Да. На странице «Врачи» выберите нужного специалиста и нажмите «Записаться на приём» на его странице.',
            ],
            [
                'q' => 'Как работает режим приёма?',
                'a' => 'Клиника работает по расписанию каждого врача. Доступные слоты для записи формируются автоматически на основе рабочих часов специалиста.',
            ],
            [
                'q' => 'Где посмотреть назначения после приёма?',
                'a' => 'Все назначения хранятся в разделе «Медицинская карта». Там же можно распечатать лист назначений.',
            ],
            [
                'q' => 'Как оставить отзыв о враче?',
                'a' => 'После завершённого приёма в разделе «Отзывы» личного кабинета появится возможность оценить врача. Отзыв публикуется после проверки администратором.',
            ],
            [
                'q' => 'Мои данные защищены?',
                'a' => 'Все персональные и медицинские данные хранятся в зашифрованном виде и доступны только вам и вашему лечащему врачу.',
            ],
        ];

        View::render('public/faq', [
            'pageTitle' => 'Частые вопросы',
            'questions' => $questions,
        ]);
    }



    public function doctors(): void
    {
        $query  = trim($_GET['q'] ?? '');
        $specId = (int)($_GET['spec'] ?? 0);
    
        $doctors = $this->doctors->search($query, $specId);
        $specs   = $this->doctors->getAllSpecializations();
    
        View::render('public/doctors', [
            'pageTitle' => 'Наши врачи',
            'doctors'   => $doctors,
            'specs'     => $specs,
            'query'     => $query,
            'specId'    => $specId,
        ]);
    }

    

    // GET /services
    public function services(): void
    {
        $grouped = $this->services->getAllGroupedBySpecialization();

        View::render('public/services', [
            'pageTitle' => 'Прайс-лист',
            'grouped'   => $grouped,
        ]);
    }

    // GET /contact
    public function contact(): void
    {
        View::render('public/contact', [
            'pageTitle' => 'Контакты',
            'csrf'      => Session::generateCsrfToken(),
            'sent'      => false,
        ]);
    }

    // POST /contact
    public function contactSend(): void
    {
        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Недействительный токен. Попробуйте снова.');
            AuthMiddleware::redirect('/contact');
        }

        View::render('public/contact', [
            'pageTitle' => 'Контакты',
            'csrf'      => Session::generateCsrfToken(),
            'sent'      => true,
        ]);
    }
    
    // GET /articles
    public function articles(): void
    {
        $all        = $this->articles->getAll();
        $categories = $this->articles->getCategories();
        $filter     = trim($_GET['category'] ?? '');

        $filtered = $filter
            ? array_values(array_filter($all, fn($a) => $a['category'] === $filter))
            : $all;

        View::render('public/articles', [
            'pageTitle'  => 'Статьи о здоровье',
            'articles'   => $filtered,
            'categories' => $categories,
            'filter'     => $filter,
        ]);
    }

    // GET /articles/{slug}
    public function article(string $slug): void
    {
        $article = $this->articles->findBySlug($slug);

        if (!$article) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        $related = array_values(array_filter(
            $this->articles->getRecent(6),
            fn($a) => $a['slug'] !== $slug
        ));
        $related = array_slice($related, 0, 2);

        View::render('public/article', [
            'pageTitle' => $article['title'],
            'article'   => $article,
            'related'   => $related,
        ]);
    }

    // GET /doctors/{id}
    public function doctor(string $id): void
    {
        $doctorId = (int) $id;
        $doctor   = $this->doctors->findById($doctorId);

        if (!$doctor) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        $schedule = $this->doctors->getSchedule($doctorId);
        $reviews  = (new ReviewRepository())->getApprovedForDoctor($doctorId);
        $rating   = (new ReviewRepository())->getAverageRating($doctorId);

        // Индексируем расписание по дню недели
        $scheduleByDay = [];
        foreach ($schedule as $row) {
            $scheduleByDay[(int)$row['day_of_week']] = $row;
        }

        View::render('public/doctor', [
            'pageTitle'     => $doctor['full_name'],
            'doctor'        => $doctor,
            'scheduleByDay' => $scheduleByDay,
            'reviews'       => $reviews,
            'rating'        => $rating,
        ]);
    }
}