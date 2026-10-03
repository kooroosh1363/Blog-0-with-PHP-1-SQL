<?php

declare(strict_types=1);

namespace Scribe;

use InvalidArgumentException;
use PDO;

final class PostRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public static function isValidSlug(string $slug): bool
    {
        return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) === 1
            && strlen($slug) >= 3
            && strlen($slug) <= 120;
    }

    public function create(
        string $title,
        string $slug,
        string $excerpt,
        string $body,
        string $status = 'draft'
    ): int {
        $title = trim($title);
        $slug = trim($slug);
        $excerpt = trim($excerpt);
        $body = trim($body);

        if ($title === '' || strlen($title) > 160) {
            throw new InvalidArgumentException('Title must contain 1–160 characters.');
        }

        if (!self::isValidSlug($slug)) {
            throw new InvalidArgumentException('Slug must use lowercase letters, numbers, and single hyphens.');
        }

        if (strlen($excerpt) > 320) {
            throw new InvalidArgumentException('Excerpt must be 320 characters or fewer.');
        }

        if ($body === '') {
            throw new InvalidArgumentException('Body cannot be empty.');
        }

        if (!in_array($status, ['draft', 'published'], true)) {
            throw new InvalidArgumentException('Status must be draft or published.');
        }

        $publishedAt = $status === 'published'
            ? gmdate('Y-m-d H:i:s')
            : null;

        $statement = $this->pdo->prepare(
            'INSERT INTO posts (slug, title, excerpt, body, status, published_at)
             VALUES (:slug, :title, :excerpt, :body, :status, :published_at)'
        );
        $statement->execute([
            ':slug' => $slug,
            ':title' => $title,
            ':excerpt' => $excerpt,
            ':body' => $body,
            ':status' => $status,
            ':published_at' => $publishedAt,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function listPublished(string $query, int $limit, int $offset): array
    {
        $query = trim($query);
        $limit = max(1, min($limit, 50));
        $offset = max(0, $offset);
        $like = '%' . $this->escapeLike($query) . '%';

        $statement = $this->pdo->prepare(
            "SELECT id, slug, title, excerpt, body, published_at, created_at
             FROM posts
             WHERE status = 'published'
               AND (
                    :query = ''
                    OR title LIKE :like ESCAPE '!'
                    OR excerpt LIKE :like ESCAPE '!'
               )
             ORDER BY COALESCE(published_at, created_at) DESC, id DESC
             LIMIT :limit OFFSET :offset"
        );
        $statement->bindValue(':query', $query, PDO::PARAM_STR);
        $statement->bindValue(':like', $like, PDO::PARAM_STR);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function countPublished(string $query): int
    {
        $query = trim($query);
        $like = '%' . $this->escapeLike($query) . '%';

        $statement = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM posts
             WHERE status = 'published'
               AND (
                    :query = ''
                    OR title LIKE :like ESCAPE '!'
                    OR excerpt LIKE :like ESCAPE '!'
               )"
        );
        $statement->execute([
            ':query' => $query,
            ':like' => $like,
        ]);

        return (int) $statement->fetchColumn();
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        if (!self::isValidSlug($slug)) {
            return null;
        }

        $statement = $this->pdo->prepare(
            "SELECT id, slug, title, excerpt, body, published_at, created_at
             FROM posts
             WHERE slug = :slug AND status = 'published'
             LIMIT 1"
        );
        $statement->execute([':slug' => $slug]);
        $post = $statement->fetch();

        return $post === false ? null : $post;
    }

    private function escapeLike(string $value): string
    {
        return str_replace(
            ['!', '%', '_'],
            ['!!', '!%', '!_'],
            $value
        );
    }
}
