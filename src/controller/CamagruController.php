<?php

class CamagruController
{

	private string $url = '/../view/main/camagru.php';

    public function index(): void
    {

		$username = $_SESSION['username'] ?? null;
		$user_id = $_SESSION['user_id'] ?? null;

		$title = 'Page d\'accueil';
		$headerView	= isset($user_id) ? '/header-on.php' : '/header-off.php';
		$mainView	= __DIR__ . $this->url;
		$footerView	= '/footer.php';

        require __DIR__ . '/../view/layout.php';
    }
}