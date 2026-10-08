<?php

require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../config/session.php';

$router = new Router();

$camagruController = new CamagruController();
$loginController = new LoginController();
$registerController = new RegisterController();
$logoutController = new LogoutController();
$homeController = new HomeController();

$router->guest('/', [$camagruController, 'index']);

$router->guest('/login', [$loginController, 'index']);
$router->post('/login', [$loginController, 'login']);

$router->guest('/register', [$registerController, 'index']);
$router->post('/register', [$registerController, 'register']);

$router->auth('/logout', [$logoutController, 'logout']);

$router->auth('/home', [$homeController, 'index']);

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);