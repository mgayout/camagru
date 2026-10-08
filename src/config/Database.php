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

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}