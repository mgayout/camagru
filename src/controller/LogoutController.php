<?php

class LogoutController
{
    public function logout(): void
    {
        $_SESSION = [];

        session_destroy();

        header('Location: /');
        exit;
    }
}