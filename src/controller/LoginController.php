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

    private function renderForgetPassword(array $errors, bool $success, ?string $username = null, ?int $user_id = null, ?string $email = null): void
    {
        $title = 'Oublie du mot de passe';
        $headerView = isset($user_id) ? HEADERON_VIEW : HEADEROFF_VIEW;
        $mainView = FORGET_PASSWORD_VIEW;

        require LAYOUT_VIEW;
    }

    private function renderResetPasswordForm(array $errors, bool $success, ?string $username = null, ?int $user_id = null, ?string $email = null, string $token = ''): void
    {
        $title = 'Changement du mot de passe';
        $headerView = isset($user_id) ? HEADERON_VIEW : HEADEROFF_VIEW;
        $mainView = RESET_PASSWORD_VIEW;

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

        if (!is_string($password) || $password === '') {
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

        $user_id = (int) $user['id'];
        $isAuthenticated = $userModel->getAuthenticated($user_id);

        if (!$isAuthenticated) {
            $errors[] = 'Profil non authentifié.';
            $this->renderLogin($errors, $username, null, $email);
            return;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];

        header('Location: /camagru');
        exit;
    }

    public function forgotPassword(): void
    {
        $errors = [];
        $success = false;
        $username = $_SESSION['username'] ?? null;
		$user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
        $email = $_SESSION['email'] ?? null;

        $this->renderForgetPassword($errors, $success, $username, $user_id, $email);
    }

    public function sendResetLink(): void
    {
        $errors = [];
        $success = false;
        $username = null;
		$user_id = null;
        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors = ['Veuillez saisir une adresse email valide.'];
            $this->renderForgetPassword($errors, $success, $username, $user_id, $email);
            return;
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        $success = true;
        
        if ($user !== null) {
            $username = (string) $user['username'];
            $user_id = (int) $user['id'];
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = (new DateTimeImmutable('+30 minutes', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');

            $userModel->setHashToken($user_id, $tokenHash);
            $userModel->setExpiresAt($user_id, $expiresAt);
        
            $isSent = $this->sendResetLinkMail($username, $email, $token);
        }

        $this->renderForgetPassword($errors, $success, $username, $user_id, $email);
    }

    public function resetPasswordForm(): void
    {
        $errors = [];
        $success = false;
        $username = null;
		$user_id = null;
        $email = null;

        $token = $_GET['token'] ?? '';

        if (!is_string($token) || !preg_match('/\A[a-f0-9]{64}\z/', $token)) {
            http_response_code(400);
            $errors[] = 'Le lien de réinitialisation est invalide ou incomplet.';
            $this->renderResetPasswordForm($errors, $success, $username, $user_id, $email, '');
            return;
        }

        $userModel = new User();
        $hashToken = hash('sha256', $token);

        $user = $userModel->findByToken($hashToken);

        if ($user === null) {
            http_response_code(400);
            echo 'Lien invalide, expiré ou déjà utilisé.';
            header('Location: /login');
            return;
        }

        $user_id = $user['id'];

        if ($userModel->isTokenExpired($user_id)) {
            http_response_code(400);
            echo 'Lien invalide, expiré ou déjà utilisé.';
            header('Location: /login');
            return;
        }

        $this->renderResetPasswordForm($errors, $success, $username, $user_id, $email, $token);
    }

    public function resetPassword(): void
    {
        $errors = [];
        $success = false;
        $token = $_POST['token'] ?? '';
        $new_password = $_POST['password'] ?? '';
        $confirm_password = $_POST['password_confirmation'] ?? '';

        $username = null;
        $user_id = null;
        $email = null;

        $registerModel = new RegisterController();

        if ($registerModel->passwordCheck($new_password) ||
            $registerModel->passwordCheck($confirm_password) ||
            $new_password !== $confirm_password) {
            http_response_code(400);
            $this->renderResetPasswordForm($errors, $success, $username, $user_id, $email, $token);
            return;
        }

        $hashToken = hash('sha256', $token);
        $userModel = new User();
        $user = $userModel->findByToken($hashToken);

        if ($user === null) {
            http_response_code(400);
            $errors[] = 'Lien invalide, expiré ou déjà utilisé.';
            $this->renderResetPasswordForm($errors, $success, $username, $user_id, $email, $token);
            return;
        }

        $user_id = $user['id'];
        $success = $userModel->setPassword($user_id, $new_password);

        if ($success) {
            $userModel->setHashToken($user_id, null);
            $userModel->setExpiresAt($user_id, null);
        }

        header('Location: /login');
        exit();
    }

    public function sendResetLinkMail(string $username, string $email, string $token): bool
    {
        $subject = "Modification de mot de passe";

        $url = 'http://localhost:8080/reset-password?token=' . urlencode($token);
        $message = "Bonjour {$username} !\n\n
            Veuillez cliquer sur le lien ci-dessous pour modifier le mot de passe de votre compte camagru :\n\n
            {$url}";
        
        $headers = [
            "From" => "noreply@camagru.test",
            "Content-Type" => "text/plain; charset=UTF-8",];

        $result = mail($email, $subject, $message, $headers);

        return $result;
    }

}