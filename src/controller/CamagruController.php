<?php

class CamagruController
{
    public function index(): void
    {
        $username = $_SESSION['username'] ?? null;

        require __DIR__ . '/../view/camagru.php';
    }
}