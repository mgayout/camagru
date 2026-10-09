<?php

class RegisterController
{

	private string $url = '/../view/main/register.php';

    public function index(): void
    {
        $errors = [];

		$username = $_SESSION['username'] ?? null;
		$user_id = $_SESSION['user_id'] ?? null;

		$title = 'S\'inscrire';
		$headerView	= isset($user_id) ? '/header-on.php' : '/header-off.php';
		$mainView	= __DIR__ . $this->url;
		$footerView	= '/footer.php';

        require __DIR__ . '/../view/layout.php';
    }

    public function register(): void
    {
        $errors = [];

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '') {
            $errors[] = 'Le nom d’utilisateur est obligatoire.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L’adresse email est invalide.';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }

        $user = new User();

        if ($user->existsByUsername($username)) {
            $errors[] = 'Ce nom d’utilisateur est déjà utilisé.';
        }

        if ($user->existsByEmail($email)) {
            $errors[] = 'Cette adresse email est déjà utilisée.';
        }

        if (!empty($errors)) {
            require __DIR__ . $this->url;
            return;
        }

        $user->create($username, $email, $password);

        header('Location: /login');
        exit;
    }
}