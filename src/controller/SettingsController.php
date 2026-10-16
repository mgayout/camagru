<?php

class SettingsController
{
    public function index(): void
    {

		$username = $_SESSION['username'] ?? null;
        $email = $_SESSION['email'] ?? null;
		$user_id = $_SESSION['user_id'] ?? null;

		$title = 'Paramètres';
		$headerView	= isset($user_id) ? HEADERON_VIEW : HEADEROFF_VIEW;
		$mainView	= SETTINGS_VIEW;

        $commentNotification = false;
        $likeNotification = false;

        if ($user_id !== null) {
            $userModel = new User();
            $commentNotification = $userModel->getCommentNotification((int) $user_id);
            $likeNotification = $userModel->getLikeNotification((int) $user_id);
        }

        require LAYOUT_VIEW;
    }

    // USER

    public function updateUsername(): void
    {
        $data = $this->firstCheckRequest();

        if ($data === null)
            return;

        $username = trim($data['username'] ?? '');

        $registerModel = new RegisterController();

        if ($registerModel->usernameCheck($username)) {
            http_response_code(400);
            echo json_encode(['success' => false]);
            return;
        }

        $user_id = (int) $_SESSION['user_id'];
        $userModel = new User();
        $success = $userModel->setUsername($user_id, $username);

        if ($success)
            $_SESSION['username'] = $username;

        $this->lastCheckRequest($success);
    }

    public function updateEmail(): void
    {
        $data = $this->firstCheckRequest();

        if ($data === null)
            return;

        $email = trim($data['email'] ?? '');

        $registerModel = new RegisterController();

        if ($registerModel->emailCheck($email)) {
            http_response_code(400);
            echo json_encode(['success' => false]);
            return;
        }

        $user_id = (int) $_SESSION['user_id'];
        $userModel = new User();
        $success = $userModel->setEmail($user_id, $email);

        if ($success)
            $_SESSION['email'] = $email;

        $this->lastCheckRequest($success);
    }

    public function updatePassword(): void
    {
        $data = $this->firstCheckRequest();

        if ($data === null)
            return;

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

        $this->lastCheckRequest($success);
    }

    // NOTIFICATION

    public function updateNotification(): void
    {
        $data = $this->firstCheckRequest();

        if ($data === null)
            return;

        $type = $data['type'];
        $value = $data['value'];

        if (!in_array($type, ['comment', 'like'], true) || !in_array($value, ['0', '1'], true)) {
            http_response_code(400);
            echo json_encode(['success' => false]);
            return;
        }

        $user_id = (int) $_SESSION['user_id'];
        $enabled = $value === '1';

        $userModel = new User();

        if ($type === 'comment')
            $success = $userModel->setCommentNotification($user_id, $enabled);
        else if ($type === 'like')
            $success = $userModel->setLikeNotification($user_id, $enabled);

        $this->lastCheckRequest($success);
    }

    // DELETE

    public function delete(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $user = new User();

        if ($user->delete((int) $_SESSION['user_id'])) {
            $_SESSION = [];
            session_destroy();

            header('Location: /');
            exit;
        }

        http_response_code(500);
        echo 'Impossible de supprimer le compte.';
    }

    // UTILS

    private function firstCheckRequest(): ?array
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false]);
            return null;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false]);
            return null;
        }

        return $_POST;
    }

    private function lastCheckRequest(bool $success): void
    {
        if (!$success) {
            http_response_code(500);
            echo json_encode(['success' => false]);
            return;
        }

        echo json_encode(['success' => true]);
    }
}