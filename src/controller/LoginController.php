<?php

class LoginController
{
    public function index(): void
    {
        $errors = [];

        require __DIR__ . '/../view/login.php';
    }

    public function login(): void
    {
        $errors = [];

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L’adresse email est invalide.';
        }

        if ($password === '') {
            $errors[] = 'Le mot de passe est obligatoire.';
        }

        if (!empty($errors)) {
            require __DIR__ . '/../view/login.php';
            return;
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user === null || !password_verify($password, $user['password'])) {
            $errors[] = 'Email ou mot de passe incorrect.';
            require __DIR__ . '/../view/login.php';
            return;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        header('Location: /home');
        exit;
    }
}