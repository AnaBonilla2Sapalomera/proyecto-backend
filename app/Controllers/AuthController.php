<?php

namespace App\Controllers;

use App\Core\Session;
use App\Functions\Validator;
use App\Models\UserModel;
use App\Services\EmailService;

class AuthController
{
    private UserModel $userModel;
    private Session $session;
    private EmailService $emailService;

    public function __construct(
        UserModel $userModel,
        Session $session,
        EmailService $emailService,
    ) {
        $this->userModel = $userModel;
        $this->session = $session;
        $this->emailService = $emailService;
    }

    public function showRegister(): void
    {
        $error = $_SESSION["error"] ?? null;
        $old = $_SESSION["old"] ?? [];

        unset($_SESSION["error"], $_SESSION["old"]);

        require __DIR__ . "/../../views/register.php";
    }

    public function register(): void
    {
        $username = trim($_POST["username"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";
        $confirmPassword = $_POST["confirmPassword"] ?? "";

        $_SESSION["old"] = [
            "username" => $username,
            "email" => $email,
        ];

        if (
            !Validator::requiredFields([
                $username,
                $email,
                $password,
                $confirmPassword,
            ])
        ) {
            $this->setRegisterError("Todos los campos son obligatorios.");
            return;
        }

        if (!Validator::email($email)) {
            $this->setRegisterError("El correo electrónico no es válido.");
            return;
        }

        if (!Validator::passwordConfirmation($password, $confirmPassword)) {
            $this->setRegisterError("Las contraseñas no coinciden.");
            return;
        }

        if (!Validator::passwordStrength($password)) {
            $this->setRegisterError(
                "La contraseña debe tener al menos 10 caracteres, mayúsculas, minúsculas, números y símbolos.",
            );
            return;
        }

        if ($this->userModel->exists($username, $email)) {
            $this->setRegisterError("El usuario o correo ya está registrado.");
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

        if (!$this->userModel->create($username, $email, $hashedPassword)) {
            $this->setRegisterError("No se ha podido completar el registro.");
            return;
        }

        unset($_SESSION["old"]);

        $user = $this->userModel->findByUsernameOrEmail($email);
        $this->session->login($user["id"], $user["username"]);

        header("Location: index.php?action=index");
    }

    public function showLogin(): void
    {
        $error = $_SESSION["error"] ?? null;
        $notice = $_SESSION["notice"] ?? null;
        $old = $_SESSION["old"] ?? [];

        unset($_SESSION["error"], $_SESSION["old"], $_SESSION["notice"]);

        require __DIR__ . "/../../views/login.php";
    }

    public function login(): void
    {
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        $_SESSION["old"] = [
            "email" => $email,
        ];

        if (!Validator::requiredFields([$email, $password])) {
            $this->setLoginError("Todos los campos son obligatorios.");
            return;
        }

        if (!Validator::email($email)) {
            $this->setLoginError("El correo electrónico no es válido.");
            return;
        }

        $user = $this->userModel->findByUsernameOrEmail($email);

        if (!$user || !password_verify($password, $user["password"])) {
            $this->setLoginError("El correo o la contraseña son incorrectos.");
            return;
        }

        unset($_SESSION["old"]);

        $this->session->login($user["id"], $user["username"]);

        header("Location: index.php?action=index");
    }

    public function showRecoverPassword(): void
    {
        $error = $_SESSION["error"] ?? null;
        $notice = $_SESSION["notice"] ?? null;
        unset($_SESSION["error"], $_SESSION["notice"]);
        require __DIR__ . "/../../views/recoverpassword.php";
    }

    public function requestPasswordRecovery(): void
    {
        $email = trim($_POST["email"] ?? "");
        if (!Validator::email($email)) {
            $_SESSION["error"] = "Introduce un correo electrónico válido.";
            header("Location: index.php?action=recoverpassword");
            return;
        }

        $user = $this->userModel->findByEmail($email);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = gmdate("Y-m-d H:i:s", time() + 3600);
            $this->userModel->storePasswordResetToken(
                (int) $user["id"],
                hash("sha256", $token),
                $expiresAt,
            );

            $resetUrl =
                "http://localhost:8081/index.php?action=resetpassword&token=" .
                urlencode($token);
            $message =
                "<p>Hola " .
                htmlspecialchars($user["username"], ENT_QUOTES, "UTF-8") .
                ",</p>" .
                "<p>Recibimos una solicitud para cambiar la contraseña de tu cuenta. " .
                "<a href=\"" .
                htmlspecialchars($resetUrl, ENT_QUOTES, "UTF-8") .
                "\">Cámbiala aquí</a>.</p>" .
                "<p>El enlace caduca en una hora. Si no solicitaste este cambio, ignora este correo.</p>";
            if (
                !$this->emailService->sendEmail(
                    $user["email"],
                    "Recuperar contraseña",
                    $message,
                )
            ) {
                error_log(
                    "No se pudo enviar el correo de recuperación para el usuario " .
                        $user["id"],
                );
            }
        }

        $_SESSION["notice"] =
            "Si existe una cuenta con ese correo, recibirás un enlace para cambiar la contraseña.";
        header("Location: index.php?action=recoverpassword");
    }

    public function showResetPassword(): void
    {
        $error = $_SESSION["error"] ?? null;
        unset($_SESSION["error"]);
        $token = $_GET["token"] ?? "";
        require __DIR__ . "/../../views/resetpassword.php";
    }

    public function resetPassword(): void
    {
        $token = $_POST["token"] ?? "";
        $password = $_POST["password"] ?? "";
        $confirmPassword = $_POST["confirmPassword"] ?? "";

        if (!Validator::requiredFields([$token, $password, $confirmPassword])) {
            $_SESSION["error"] = "Todos los campos son obligatorios.";
        } elseif (
            !Validator::passwordConfirmation($password, $confirmPassword)
        ) {
            $_SESSION["error"] = "Las contraseñas no coinciden.";
        } elseif (!Validator::passwordStrength($password)) {
            $_SESSION["error"] =
                "La contraseña debe tener al menos 10 caracteres, mayúsculas, minúsculas, números y símbolos.";
        } elseif (
            !$this->userModel->resetPassword(
                hash("sha256", $token),
                password_hash($password, PASSWORD_ARGON2ID),
            )
        ) {
            $_SESSION["error"] =
                "El enlace no es válido o ha caducado. Solicita uno nuevo.";
        } else {
            $_SESSION["notice"] =
                "Contraseña actualizada. Ya puedes iniciar sesión.";
            header("Location: index.php?action=login");
            return;
        }

        header(
            "Location: index.php?action=resetpassword&token=" .
                urlencode($token),
        );
    }

    public function logout(): void
    {
        $this->session->destroy();
        header("Location: index.php?action=index");
    }

    private function setRegisterError(string $message): void
    {
        $_SESSION["error"] = $message;
        header("Location: index.php?action=register");
    }

    private function setLoginError(string $message): void
    {
        $_SESSION["error"] = $message;
        header("Location: index.php?action=login");
    }
}
