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

        require LAYOUT_VIEW;
    }

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