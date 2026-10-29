<?php

class CamagruController
{
    public function index(): void
    {

		$username = $_SESSION['username'] ?? null;
		$user_id = $_SESSION['user_id'] ?? null;

		$title = 'Page d\'accueil';
		$headerView	= isset($user_id) ? HEADERON_VIEW : HEADEROFF_VIEW;
		$mainView	= CAMAGRU_VIEW;

        require LAYOUT_VIEW;
    }

	
}
