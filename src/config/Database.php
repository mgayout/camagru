<?php

class Database
{
    private PDO $connection;

    public function __construct()
    {
        $host = 'db';
        $database = 'camagru';
        $username = 'camagru';
        $password = 'camagru';

        $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";

        $this->connection = new PDO($dsn, $username, $password);

        $this->connection->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );
    }

    // GETTERS

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function getTotalPhotos(): int
    {
        $query = '
            SELECT COUNT(*)
            FROM photos';

        $statement = $this->connection->prepare($query);

        $statement->execute();

        $result = $statement->fetchColumn();

        return (int) $result;
    }

    public function getPhotosPaginated(?int $user_id, int $limit, int $offset): array
    {
        $query = '
            SELECT
                p.id,
                p.user_id,
                p.filename,
                p.created_at,

                (
                    SELECT COUNT(*)
                    FROM photo_likes pl
                    WHERE pl.photo_id = p.id
                ) AS likes_count,

                (
                    SELECT COUNT(*)
                    FROM photo_comments pc
                    WHERE pc.photo_id = p.id
                ) AS comments_count,

                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM photo_likes pl_user
                        WHERE pl_user.photo_id = p.id
                        AND pl_user.user_id = :user_id
                    )
                    THEN 1
                    ELSE 0
                END AS user_liked

            FROM photos p

            ORDER BY p.created_at DESC, p.id DESC

            LIMIT :limit OFFSET :offset';

        $statement = $this->connection->prepare($query);

        $statement->bindValue(
            ':user_id',
            $user_id,
            $user_id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL
        );

        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);

        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);

        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLike(int $photo_id, int $user_id): bool
    {
        $query = '
            SELECT id
            FROM photo_likes
            WHERE photo_id = :photo_id
            AND user_id = :user_id';

        $statement = $this->connection->prepare($query);

        $statement->execute([
            ':photo_id' => $photo_id,
            ':user_id' => $user_id
        ]);

        return (bool) $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function getCommentsByPhotoIds(array $photoIds): array
    {
        if (empty($photoIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($photoIds), '?'));

        $query = "
            SELECT
                pc.id,
                pc.photo_id,
                pc.user_id,
                pc.content,
                pc.created_at,
                u.username

            FROM photo_comments pc

            INNER JOIN users u ON u.id = pc.user_id

            WHERE pc.photo_id IN ($placeholders)

            ORDER BY pc.created_at ASC
        ";

        $statement = $this->connection->prepare($query);
        $statement->execute($photoIds);

        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        $commentsByPhoto = [];

        foreach ($results as $comment) {
            $commentsByPhoto[$comment['photo_id']][] = $comment;
        }

        return $commentsByPhoto;
    }

    public function getUsernameByPhotoId(int $photo_id): string
    {
        $query = '
            SELECT users.username
            FROM photos
            INNER JOIN users ON photos.user_id = users.id
            WHERE photos.id = :photo_id
        ';

        $statement = $this->connection->prepare($query);
        $statement->execute(['photo_id' => $photo_id]);

        return $statement->fetchColumn();
    }

    public function getNotificationStatus(string $username, string $like_or_comment): bool
    {
        $columns = [
            'like' => 'like_notification',
            'comment' => 'comment_notification'
        ];

        if (!isset($columns[$like_or_comment])) {
            throw new InvalidArgumentException('Type de notification invalide.');
        }

        $column = $columns[$like_or_comment];

        $query = "
            SELECT $column
            FROM users
            WHERE username = :username
        ";

        $statement = $this->connection->prepare($query);
        $statement->execute([':username' => $username]);

        return (bool) $statement->fetchColumn();
    }

    public function getPhotosByUserId(int $user_id): array
    {
        $query = '
            SELECT id, filename, created_at
            FROM photos
            WHERE user_id = :user_id
            ORDER BY created_at DESC, id DESC';
        
        $statement = $this->connection->prepare($query);

        $statement->execute([':user_id' => $user_id]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPhotoByIdAndUserId(int $photoId, int $user_id): array|false
    {
        $query = '
            SELECT id, filename
            FROM photos
            WHERE id = :id
            AND user_id = :user_id';
        
        $statement = $this->connection->prepare($query);

        $statement->execute([
            ':id' => $photoId,
            ':user_id' => $user_id
        ]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    // SETTERS

    public function setLikeOn(int $photo_id, int $user_id): bool
    {
        $query = '
            INSERT INTO photo_likes (photo_id, user_id)
            VALUES (:photo_id, :user_id)';
        
        $statement = $this->connection->prepare($query);

        return $statement->execute([
            ':photo_id' => $photo_id,
            ':user_id' => $user_id
        ]);
    }

    public function setLikeOff(int $photo_id, int $user_id): bool
    {
        $query = '
            DELETE FROM photo_likes
            WHERE photo_id = :photo_id
            AND user_id = :user_id';
        
        $statement = $this->connection->prepare($query);

        return $statement->execute([
            ':photo_id' => $photo_id,
            ':user_id' => $user_id
        ]);
    }

    public function setComment(int $photo_id, int $user_id, string $content): bool
    {
        $query = '
            INSERT INTO photo_comments (photo_id, user_id, content)
            VALUES (:photo_id, :user_id, :content)';
        
        $statement = $this->connection->prepare($query);

        return $statement->execute([
            ':photo_id' => $photo_id,
            ':user_id' => $user_id,
            ':content' => $content
        ]);
    }

    public function setPhoto(int $user_id, string $filename): bool
    {
        $query = '
            INSERT INTO photos (user_id, filename)
            VALUES (:user_id, :filename)';
        
        $statement = $this->connection->prepare($query);

        return $statement->execute([
            ':user_id' => $user_id,
            ':filename' => $filename
        ]);
    }

    public function deletePhotoByIdAndUserId(int $photo_id, int $user_id): bool
    {
        $query = '
            DELETE FROM photos
            WHERE id = :id
            AND user_id = :user_id';
        
        $statement = $this->connection->prepare($query);

        $statement->execute([
            ':id' => $photo_id,
            ':user_id' => $user_id
        ]);

        return $statement->rowCount() === 1;
    }
}