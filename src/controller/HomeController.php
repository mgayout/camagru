<?php

class HomeController
{
    public function index(): void
    {

		$username = $_SESSION['username'] ?? null;
		$user_id = $_SESSION['user_id'] ?? null;

		$title = null;
		$headerView	= isset($user_id) ? HEADERON_VIEW : HEADEROFF_VIEW;
		$mainView	= HOME_VIEW;

        require LAYOUT_VIEW;
    }
}