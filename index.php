<?php

require "vendor/autoload.php";

use App\Core\Database;
use App\Core\Session;
use App\Controllers\ArticleController;
use App\Controllers\AuthController;
use App\Models\ArticleModel;
use App\Models\UserModel;
use App\Services\EmailService;
use Dotenv\Dotenv;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

Dotenv::createImmutable(__DIR__)->load();

$GLOBALS["cfg"] = [
    "database" => require "config/database.php",
    "mail" => require "config/mail.php",
];

$session = new Session();
$session->start();

$database = new Database();
$connection = $database->connect();

$mailTransport = Transport::fromDsn($GLOBALS["cfg"]["mail"]["dsn"]);
$mailer = new Mailer($mailTransport);
$emailService = new EmailService($mailer);

$articleController = new ArticleController(
    new ArticleModel($connection),
    $session,
);
$authController = new AuthController(
    new UserModel($connection),
    $session,
    $emailService,
);

// para las vistas

$GLOBALS["session"] = $session;

// router start

$action = $_GET["action"] ?? "index";
$method = $_SERVER["REQUEST_METHOD"];

switch ($action) {
    case "index":
        if ($method !== "GET") {
            http_response_code(405);
            exit("Method Not Allowed");
        }

        $articleController->index();
        break;

    case "create":
        if ($method === "GET") {
            $articleController->showCreate();
        } elseif ($method === "POST") {
            $articleController->create();
        } else {
            http_response_code(405);
            exit("Method Not Allowed");
        }
        break;

    case "edit":
        if ($method === "GET") {
            $articleController->showEdit();
        } elseif ($method === "POST") {
            $articleController->update();
        } else {
            http_response_code(405);
            exit("Method Not Allowed");
        }
        break;

    case "register":
        if ($method === "GET") {
            $authController->showRegister();
        } elseif ($method === "POST") {
            $authController->register();
        } else {
            http_response_code(405);
            exit("Method Not Allowed");
        }
        break;

    case "login":
        if ($method === "GET") {
            $authController->showLogin();
        } elseif ($method === "POST") {
            $authController->login();
        } else {
            http_response_code(405);
            exit("Method Not Allowed");
        }
        break;

    case "recoverpassword":
        if ($method === "GET") {
            $authController->showRecoverPassword();
        } elseif ($method === "POST") {
            $authController->requestPasswordRecovery();
        } else {
            http_response_code(405);
            exit("Method Not Allowed");
        }
        break;

    case "resetpassword":
        if ($method === "GET") {
            $authController->showResetPassword();
        } elseif ($method === "POST") {
            $authController->resetPassword();
        } else {
            http_response_code(405);
            exit("Method Not Allowed");
        }
        break;

    case "logout":
        if ($method !== "POST") {
            http_response_code(405);
            exit("Method Not Allowed");
        }

        $authController->logout();
        break;

    default:
        http_response_code(404);
        exit("Not Found");
        break;
}
