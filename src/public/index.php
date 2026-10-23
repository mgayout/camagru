<?php

require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/paths.php';

$router = new Router();

$homeController = new HomeController();
$loginController = new LoginController();
$registerController = new RegisterController();
$camagruController = new CamagruController();
$logoutController = new LogoutController();
$settingsController = new SettingsController();


$router->guest('/', [$homeController, 'index']);

$router->guest('/register', [$registerController, 'index']);
$router->post('/register', [$registerController, 'register']);
$router->get('/verify-email', [$registerController, 'verifyEmail']);

$router->guest('/login', [$loginController, 'index']);
$router->post('/login', [$loginController, 'login']);

$router->guest('/forgot-password', [$loginController, 'forgotPassword']);
$router->post('/forgot-password', [$loginController, 'sendResetLink']);

$router->guest('/reset-password', [$loginController, 'resetPasswordForm']);
$router->post('/reset-password', [$loginController, 'resetPassword']);

$router->auth('/camagru', [$camagruController, 'index']);

$router->auth('/logout', [$logoutController, 'logout']);

$router->auth('/settings', [$settingsController, 'index']);
$router->post('/settings/updateUsername', [$settingsController, 'updateUsername']);
$router->post('/settings/updateEmail', [$settingsController, 'updateEmail']);
$router->post('/settings/updatePassword', [$settingsController, 'updatePassword']);
$router->post('/settings/updateNotification', [$settingsController, 'updateNotification']);
$router->post('/delete', [$settingsController, 'delete']);

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);
