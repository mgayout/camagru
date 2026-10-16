<?php

class LoginController
{
    private function renderLogin(array $errors, ?string $username = null, ?int $user_id = null, ?string $email = null): void
    {
        $title = 'Se connecter';
        $headerView = isset($user_id) ? HEADERON_VIEW : HEADEROFF_VIEW;
        $mainView = LOGIN_VIEW;

        require LAYOUT_VIEW;
    }

	public function index(): void
    {
        $errors = [];

		$username = $_SESSION['username'] ?? null;
		$user_id = $_SESSION['user_id'] ?? null;
        $email = $_SESSION['email'] ?? null;

		$this->renderLogin($errors, $username, $user_id, $email);
    }

    public function login(): void
    {
        $errors = [];

        $username = $_SESSION['username'] ?? '';
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $user_id = $_SESSION['user_id'] ?? null;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'adresse email est invalide.';
        }

        if ($password === '') {
            $errors[] = 'Le mot de passe est obligatoire.';
        }

        if (!empty($errors)) {
            $this->renderLogin($errors, $username, $user_id, $email);
            return;
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user === null || !password_verify($password, $user['password'])) {
            $errors[] = 'Email ou mot de passe incorrect.';
            $this->renderLogin($errors, $username, $user_id, $email);
            return;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];

        header('Location: /camagru');
        exit;
    }
}