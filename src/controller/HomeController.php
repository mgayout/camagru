<?php

class HomeController
{

	private string $url = '/../view/main/home.php';

    public function index(): void
    {

		$username = $_SESSION['username'] ?? null;
		$user_id = $_SESSION['user_id'] ?? null;

		$title = null;
		$headerView	= isset($user_id) ? '/header-on.php' : '/header-off.php';
		$mainView	= __DIR__ . $this->url;
		$footerView	= '/footer.php';

        require __DIR__ . '/../view/layout.php';
    }
}