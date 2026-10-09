<?php

class LoginController
{

	private string $url = '/../view/main/login.php';
    
	public function index(): void
    {
        $errors = [];

		$username = $_SESSION['username'] ?? null;
		$user_id = $_SESSION['user_id'] ?? null;

		$title = 'Se connecter';
		$headerView	= isset($user_id) ? '/header-on.php' : '/header-off.php';
		$mainView	= __DIR__ . $this->url;
		$footerView	= '/footer.php';

        require __DIR__ . '/../view/layout.php';
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
            require __DIR__ . $this->url;
            return;
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user === null || !password_verify($password, $user['password'])) {
            $errors[] = 'Email ou mot de passe incorrect.';
            require __DIR__ . $this->url;
            return;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        header('Location: /home');
        exit;
    }
}