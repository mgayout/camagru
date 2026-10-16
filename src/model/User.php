<?php

class User
{
    private PDO $connection;

    public function __construct()
    {
        $database = new Database();
        $this->connection = $database->getConnection();
    }

    public function create(
        string $username,
        string $email,
        string $password
    ): bool {
        $query = '
            INSERT INTO users (username, email, password)
            VALUES (:username, :email, :password)
        ';

        $statement = $this->connection->prepare($query);

        return $statement->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }

    public function delete(int $user_id): bool
    {
        $query = '
            DELETE FROM users
            WHERE id = :id
        ';

        $statement = $this->connection->prepare($query);

        $statement->execute([':id' => $user_id,]);

        return $statement->rowCount() > 0;
    }

    public function existsByUsername(string $username): bool
    {
        $query = '
            SELECT id
            FROM users
            WHERE username = :username
            LIMIT 1
        ';

        $statement = $this->connection->prepare($query);
        $statement->execute([
            ':username' => $username,
        ]);

        return $statement->fetch() !== false;
    }

    public function existsByEmail(string $email): bool
    {
        $query = '
            SELECT id
            FROM users
            WHERE email = :email
            LIMIT 1
        ';

        $statement = $this->connection->prepare($query);
        $statement->execute([
            ':email' => $email,
        ]);

        return $statement->fetch() !== false;
    }

	public function findByEmail(string $email): ?array
	{
		$query = '
			SELECT id, username, email, password
			FROM users
			WHERE email = :email
			LIMIT 1
		';

		$statement = $this->connection->prepare($query);

		$statement->execute([
			':email' => $email,
		]);

		$user = $statement->fetch(PDO::FETCH_ASSOC);

		return $user !== false ? $user : null;
	}

    // SETTERS

    public function setCommentNotification(int $user_id, bool $val): bool
    {
        $query = '
            UPDATE users
            SET comment_notification = :val
            WHERE id = :id';
        
        $statement = $this->connection->prepare($query);

        return $statement->execute([
            ':val' => $val ? 1 : 0,
            ':id' => $user_id,]);
    }

    public function setLikeNotification(int $user_id, bool $val): bool
    {
        $query = '
            UPDATE users
            SET like_notification = :val
            WHERE id = :id';
        
        $statement = $this->connection->prepare($query);

        return $statement->execute([
            ':val' => $val ? 1 : 0,
            ':id' => $user_id,]);
    }

    // GETTERS

    public function getCommentNotification(int $user_id): bool
    {
        $query = '
            SELECT comment_notification
            FROM users
            WHERE id = :id
            LIMIT 1';

        $statement = $this->connection->prepare($query);

        $statement->execute([':id' => $user_id,]);
        
        $result = $statement->fetchColumn();

        return (bool) $result;
    }

    public function getLikeNotification(int $user_id): bool
    {
        $query = '
            SELECT like_notification
            FROM users
            WHERE id = :id
            LIMIT 1';

        $statement = $this->connection->prepare($query);

        $statement->execute([':id' => $user_id,]);
        
        $result = $statement->fetchColumn();

        return (bool) $result;
    }

}