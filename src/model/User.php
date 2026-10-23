<?php

class User
{
    private PDO $connection;

    public function __construct()
    {
        $database = new Database();
        $this->connection = $database->getConnection();
    }

    public function create( string $username, string $email, string $password, string $token_hash, string $expires_at): bool
    {
        $query = '
            INSERT INTO users (username, email, password, token_hash, expires_at)
            VALUES (:username, :email, :password, :token_hash, :expires_at)';

        $statement = $this->connection->prepare($query);

        return $statement->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':token_hash' => $token_hash,
            ':expires_at' => $expires_at,]);
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

    public function verifyEmailByTokenHash(string $hashToken): bool
    {
        $query = '
            UPDATE users
            SET authenticated = TRUE,
                token_hash = NULL,
                expires_at = NULL
            WHERE token_hash = :token_hash
                AND expires_at > UTC_TIMESTAMP()
                AND authenticated = FALSE';
        
        $statement = $this->connection->prepare($query);
        
        $statement->execute([':token_hash' => $hashToken]);

        return $statement->rowCount() === 1;
    }

    // SETTERS

    public function setUsername(int $user_id, string $username): bool
    {
        $query = '
            UPDATE users
            SET username = :username
            WHERE id = :id';
        
        $statement = $this->connection->prepare($query);

        return $statement->execute([
            ':username' => $username,
            ':id' => $user_id,]);
    }

    public function setEmail(int $user_id, string $email): bool
    {
        $query = '
            UPDATE users
            SET email = :email
            WHERE id = :id';
        
        $statement = $this->connection->prepare($query);

        return $statement->execute([
            ':email' => $email,
            ':id' => $user_id,]);
    }

    public function setPassword(int $user_id, string $password): bool
    {
        $query = '
            UPDATE users
            SET password = :password
            WHERE id = :id';

        $statement = $this->connection->prepare($query);

        return $statement->execute([
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':id' => $user_id,]);
    }

    public function setHashToken(int $user_id, string $hashToken): bool
    {
        $query = '
            UPDATE users
            SET token_hash = :hashToken
            WHERE id = :id';

        $statement = $this->connection->prepare($query);

        return $statement->execute([
            ':hashToken' => $hashToken,
            ':id' => $user_id,]);
    }

    public function setExpiresAt(int $user_id, string $expiresAt): bool
    {
        $query = '
            UPDATE users
            SET expires_at = :expiresAt
            WHERE id = :id';

        $statement = $this->connection->prepare($query);

        return $statement->execute([
            ':expiresAt' => $expiresAt,
            ':id' => $user_id,]);
    }

    public function setAuthenticated(int $user_id, bool $val): bool
    {
        $query = '
            UPDATE users
            SET authenticated = :val
            WHERE id = :id';
        
        $statement = $this->connection->prepare($query);

        return $statement->execute([
            ':val' => $val ? 1 : 0,
            ':id' => $user_id,]);
    }

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

    public function getPasswordHash(int $user_id): ?string
    {
        $query = '
            SELECT password
            FROM users
            WHERE id = :id
            LIMIT 1';

        $statement = $this->connection->prepare($query);

        $statement->execute([':id' => $user_id]);

        $hash = $statement->fetchColumn();

        return $hash !== false ? $hash : null;
    }

    public function getAuthenticated(int $user_id): bool
    {
        $query = '
            SELECT authenticated
            FROM users
            WHERE id = :id
            LIMIT 1';
        
        $statement = $this->connection->prepare($query);

        $statement->execute([':id' => $user_id,]);

        $result = $statement->fetchColumn();

        return (bool) $result;
    }

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