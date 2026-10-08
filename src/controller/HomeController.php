<?php

class HomeController
{
    public function index(): void
    {
        $username = $_SESSION['username'] ?? null;

        require __DIR__ . '/../view/home.php';
    }
}