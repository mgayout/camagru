<?php

class RegisterController
{
    private function renderRegister(array $errors, ?string $username = null, ?int $user_id = null, ?string $email = null): void
    {
        $title = 'S\'inscrire';
        $headerView = isset($user_id) ? HEADERON_VIEW : HEADEROFF_VIEW;
        $mainView = REGISTER_VIEW;

        require LAYOUT_VIEW;
    }

    public function index(): void
    {
        $errors = [];

		$username = $_SESSION['username'] ?? null;
		$user_id = $_SESSION['user_id'] ?? null;
        $email = $_SESSION['email'] ?? null;

        $this->renderRegister($errors, $username, $user_id, $email);
    }

    public function register(): void
    {
        $errors = [];

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $user_id = $_SESSION['user_id'] ?? null;

        // Empty field [user, email, pwd]
        
        if ($username === '')
            $errors[] = 'Le nom d\'utilisateur est obligatoire.';

        if ($email === '')
            $errors[] = 'L\'adresse email est obligatoire.';

        if ($password === '')
            $errors[] = 'Le mot de passe est obligatoire.';

        // Invalid format [user, email, pwd]

        if ($this->usernameCheck($username))
            $errors[] = 'Le nom d\'utilisateur est invalide.';

        if ($this->emailCheck($email))
            $errors[] = 'L\'adresse email est invalide.';

        if ($this->passwordCheck($password))
            $errors[] = 'Le mot de passe est invalide.';

        // Already used [user, email]

        if (!empty($errors)) {
            $this->renderRegister($errors, $username, $user_id, $email);
            return;
        }

        $user = new User();

        if ($user->existsByUsername($username))
            $errors[] = 'Ce nom d\'utilisateur est déjà utilisé.';

        if ($user->existsByEmail($email))
            $errors[] = 'Cette adresse email est déjà utilisée.';

        if (!empty($errors)) {
            $this->renderRegister($errors, $username, $user_id, $email);
            return;
        }

        $token = hash('sha256', bin2hex(random_bytes(32)));

        $isSent = $this->sendAuthenticatedMail($username, $email, $token);

        if (!$isSent) {
            $errors[] = 'L\'envoi du mail d\'authentification a échoué';
            $this->renderRegister($errors, $username, $user_id, $email);
            return;
        }

        $user->create($username, $email, $password);

        header('Location: /login');
        exit;
    }

    public function usernameCheck(string $username): bool
    {
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username))
            return true;
        return false;
    }

    public function emailCheck(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            return true;
        return false;
    }

    public function passwordCheck(string $password): bool
    {
        if (strlen($password) < 12 ||
            strlen($password) > 30 ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password) ||
            !preg_match('/[^a-zA-Z0-9]/', $password))
            return true;
        return false;
    }

    public function sendAuthenticatedMail(string $username, string $email, string $token): bool
    {
        $subject = "Mail d'authentification";

        $url = 'http://localhost:8080/verify-email?token=' . urlencode($token);
        $message = "Bonjour {$username} !\n\n
            Veuillez cliquer sur le lien ci-dessous pour valider la création de votre compte camagru :\n\n
            {$url}";
        
        $headers = [
            "From" => "noreply@camagru.test",
            "Content-Type" => "text/plain; charset=UTF-8",];

        $result = mail($email, $subject, $message, $headers);

        return $result;
    }
}