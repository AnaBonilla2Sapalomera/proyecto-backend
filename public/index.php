<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Controllers\ArticleController;
use App\Controllers\AuthController;
use App\Controllers\PasswordRecoveryController;
use App\Core\Database;
use App\Core\Session;
use App\Models\ArticleModel;
use App\Models\UserModel;
use App\Services\EmailService;
use Dotenv\Dotenv;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

Dotenv::createImmutable(dirname(__DIR__))->load();

$GLOBALS['cfg'] = [
    'database' => require __DIR__ . '/../config/database.php',
    'mail' => require __DIR__ . '/../config/mail.php',
];

$session = new Session();
$session->start();

$database = new Database();
$connection = $database->connect();

$mailTransport = Transport::fromDsn($GLOBALS['cfg']['mail']['dsn']);
$mailer = new Mailer($mailTransport);
$emailService = new EmailService($mailer);

$articleController = new ArticleController(
    new ArticleModel($connection),
    $session,
);
$authController = new AuthController(
    new UserModel($connection),
    $session,
);
$passwordRecoveryController = new PasswordRecoveryController(
    new UserModel($connection),
    $emailService,
);

// para las vistas

$GLOBALS['session'] = $session;

// router start

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

switch ($path) {
    case '/':
        if ($method !== 'GET') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        $articleController->index();
        break;

    case '/article/create':
        if ($method === 'GET') {
            $articleController->showCreate();
        } elseif ($method === 'POST') {
            $articleController->create();
        } else {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        break;

    case '/article/edit':
        if ($method === 'GET') {
            $articleController->showEdit();
        } elseif ($method === 'POST') {
            $articleController->update();
        } else {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        break;

    case '/article/delete':
        if ($method !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        $articleController->delete();
        break;

    case '/register':
        if ($method === 'GET') {
            $authController->showRegister();
        } elseif ($method === 'POST') {
            $authController->register();
        } else {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        break;

    case '/login':
        if ($method === 'GET') {
            $authController->showLogin();
        } elseif ($method === 'POST') {
            $authController->login();
        } else {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        break;

    case '/logout':
        if ($method !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        $authController->logout();
        break;

    case '/recover-password':
        if ($method === 'GET') {
            $passwordRecoveryController->showRecoverPassword();
        } elseif ($method === 'POST') {
            $passwordRecoveryController->requestPasswordRecovery();
        } else {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        break;

    case '/reset-password':
        if ($method === 'GET') {
            $passwordRecoveryController->showResetPassword();
        } elseif ($method === 'POST') {
            $passwordRecoveryController->resetPassword();
        } else {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        break;

    default:
        http_response_code(404);
        exit('Not Found');
        break;
}
