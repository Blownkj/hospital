<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class ArticleRepository extends BaseRepository
{
    protected string $table = 'articles';

    public function getAll(): array
    {
        return $this->db
            ->query("SELECT id, slug, title, excerpt, category, read_time, published_at
                     FROM articles ORDER BY published_at DESC")
            ->fetchAll();
    }

    public function getRecent(int $limit = 3): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, slug, title, excerpt, category, read_time, published_at
             FROM articles ORDER BY published_at DESC LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM articles WHERE slug = ? LIMIT 1"
        );
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getCategories(): array
    {
        return $this->db
            ->query("SELECT DISTINCT category FROM articles ORDER BY category")
            ->fetchAll(\PDO::FETCH_COLUMN);
    }
}
