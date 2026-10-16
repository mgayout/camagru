<?php

class SettingsController
{
    public function index(): void
    {

		$username = $_SESSION['username'] ?? null;
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

    // NOTIFICATION

    public function updateNotification(): void
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

        $type = $_POST['type'] ?? '';
        $value = $_POST['value'] ?? null;

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

        if (!$success) {
            http_response_code(500);
            echo json_encode(['success' => false]);
            return;
        }

        echo json_encode(['success' => true]);
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
}