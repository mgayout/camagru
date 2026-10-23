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

    private function renderResetPassword(array $errors, bool $success, ?string $username = null, ?int $user_id = null, ?string $email = null): void
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
		$user_id = $_SESSION['user_id'] ?? null;
        $email = $_SESSION['email'] ?? null;

        $this->renderForgetPassword($errors, $success, $username, $user_id, $email);
    }

    public function sendResetLink(): void
    {
        $errors = [];
        $success = false;
        $username = $_SESSION['username'] ?? null;
		$user_id = $_SESSION['user_id'] ?? null;
        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors = ['Veuillez saisir une adresse email valide.'];
            $success = false;
            $this->renderForgetPassword($errors, $success, $username, $user_id, $email);
            return;
        }

        $userModel = new User();
        $user = $userModel->findByMail($email);

        $success = true;
        
        if ($user !== null) {
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
        $success = [];

        $token = $_GET['token'] ?? '';

        if ( !is_string($token) || !preg_match('/\A[a-f0-9]{64}\z/', $token)) {
            $errors[] = 'Le lien de réinitialisation est invalide ou incomplet.';
            $token = '';
        }

        $this->renderResetPassword($errors, $success, $username, $user_id, $email);
    }

    public function resetPassword(string $username, string $lastPassword, string $newPassword, string $confirmPassword): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false]);
            return;
        }

        $current_password = $data['current_password'] ?? '';
        $new_password = $data['new_password'] ?? '';
        $confirm_password = $data['confirm_password'] ?? '';

        $user_id = (int) $_SESSION['user_id'];
        $registerModel = new RegisterController();
        $userModel = new User();
        $passwordHash = $userModel->getPasswordHash($user_id);

        if (!password_verify($current_password, $passwordHash) ||
            $registerModel->passwordCheck($new_password) ||
            $registerModel->passwordCheck($confirm_password) ||
            $new_password !== $confirm_password) {
                http_response_code(400);
                echo json_encode(['success' => false]);
                return;
        }

        $success = $userModel->setPassword($user_id, $new_password);

        if (!$success) {
            http_response_code(500);
            echo json_encode(['success' => false]);
            return;
        }

        echo json_encode(['success' => true]);
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