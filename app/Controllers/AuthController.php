<?php

namespace App\Controllers;

use App\Core\Session;
use App\Functions\Validator;
use App\Models\UserModel;
use App\Services\EmailService;

/**
 * Gestiona el registre, l'inici de sessió i les contrasenyes.
 */
class AuthController
{
    private UserModel $userModel;
    private Session $session;
    private EmailService $emailService;

    /**
     * Inicialitza les dependències del controlador.
     * @param UserModel $userModel Model que consulta i desa els usuaris.
     * @param Session $session Servei que gestiona la sessió de l'usuari.
     * @param EmailService $emailService Servei que envia els correus electrònics.
     */
    public function __construct(
        UserModel $userModel,
        Session $session,
        EmailService $emailService,
    ) {
        $this->userModel = $userModel;
        $this->session = $session;
        $this->emailService = $emailService;
    }

    /**
     * Mostra el formulari de registre.
     */
    public function showRegister(): void
    {
        $error = $_SESSION["error"] ?? null;
        $old = $_SESSION["old"] ?? [];

        unset($_SESSION["error"], $_SESSION["old"]);

        require __DIR__ . "/../../views/register.php";
    }

    /**
     * Valida i registra un usuari.
     */
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
            $this->setRegisterError("Tots els camps són obligatoris.");
            return;
        }

        if (!Validator::email($email)) {
            $this->setRegisterError("L'adreça electrònica no és vàlida.");
            return;
        }

        if (!Validator::passwordConfirmation($password, $confirmPassword)) {
            $this->setRegisterError("Les contrasenyes no coincideixen.");
            return;
        }

        if (!Validator::passwordStrength($password)) {
            $this->setRegisterError(
                "La contrasenya ha de tenir com a mínim 10 caràcters, lletres majúscules i minúscules, números i símbols.",
            );
            return;
        }

        if ($this->userModel->exists($username, $email)) {
            $this->setRegisterError("El nom d'usuari o l'adreça electrònica ja està registrat.");
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

        if (!$this->userModel->create($username, $email, $hashedPassword)) {
            $this->setRegisterError("No s'ha pogut completar el registre.");
            return;
        }

        unset($_SESSION["old"]);

        $user = $this->userModel->findByUsernameOrEmail($email);
        $this->session->login($user["id"], $user["username"]);

        header("Location: index.php?action=index");
    }

    /**
     * Mostra el formulari d'inici de sessió.
     */
    public function showLogin(): void
    {
        $error = $_SESSION["error"] ?? null;
        $notice = $_SESSION["notice"] ?? null;
        $old = $_SESSION["old"] ?? [];

        unset($_SESSION["error"], $_SESSION["old"], $_SESSION["notice"]);

        require __DIR__ . "/../../views/login.php";
    }

    /**
     * Valida les credencials i inicia la sessió.
     */
    public function login(): void
    {
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        $_SESSION["old"] = [
            "email" => $email,
        ];

        if (!Validator::requiredFields([$email, $password])) {
            $this->setLoginError("Tots els camps són obligatoris.");
            return;
        }

        if (!Validator::email($email)) {
            $this->setLoginError("L'adreça electrònica no és vàlida.");
            return;
        }

        $user = $this->userModel->findByUsernameOrEmail($email);

        if (!$user || !password_verify($password, $user["password"])) {
            $this->setLoginError("L'adreça electrònica o la contrasenya són incorrectes.");
            return;
        }

        unset($_SESSION["old"]);

        $this->session->login($user["id"], $user["username"]);

        header("Location: index.php?action=index");
    }

    /**
     * Mostra el formulari de recuperació de contrasenya.
     */
    public function showRecoverPassword(): void
    {
        $error = $_SESSION["error"] ?? null;
        $notice = $_SESSION["notice"] ?? null;
        unset($_SESSION["error"], $_SESSION["notice"]);
        require __DIR__ . "/../../views/recoverpassword.php";
    }

    /**
     * Envia un enllaç per recuperar la contrasenya.
     */
    public function requestPasswordRecovery(): void
    {
        $email = trim($_POST["email"] ?? "");
        if (!Validator::email($email)) {
            $_SESSION["error"] = "Introdueix una adreça electrònica vàlida.";
            header("Location: index.php?action=recoverpassword");
            return;
        }

        $user = $this->userModel->findByEmail($email);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = gmdate("Y-m-d H:i:s", time() + 3600);
            $this->userModel->storePasswordResetToken(
                $user["id"],
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
                "<p>Hem rebut una sol·licitud per canviar la contrasenya del teu compte. " .
                "<a href=\"" .
                htmlspecialchars($resetUrl, ENT_QUOTES, "UTF-8") .
                "\">Canvia-la aquí</a>.</p>" .
                "<p>L'enllaç caduca d'aquí a una hora. Si no has demanat aquest canvi, ignora aquest correu.</p>";
            if (
                !$this->emailService->sendEmail(
                    $user["email"],
                    "Recuperar la contrasenya",
                    $message,
                )
            ) {
                error_log(
                    "No s'ha pogut enviar el correu de recuperació per a l'usuari " .
                        $user["id"],
                );
            }
        }

        $_SESSION["notice"] =
            "Si existeix un compte amb aquesta adreça electrònica, rebràs un enllaç per canviar la contrasenya.";
        header("Location: index.php?action=recoverpassword");
    }

    /**
     * Mostra el formulari per establir una contrasenya nova.
     */
    public function showResetPassword(): void
    {
        $error = $_SESSION["error"] ?? null;
        unset($_SESSION["error"]);
        $token = $_GET["token"] ?? "";
        require __DIR__ . "/../../views/resetpassword.php";
    }

    /**
     * Valida el token i actualitza la contrasenya.
     */
    public function resetPassword(): void
    {
        $token = $_POST["token"] ?? "";
        $password = $_POST["password"] ?? "";
        $confirmPassword = $_POST["confirmPassword"] ?? "";

        if (!Validator::requiredFields([$token, $password, $confirmPassword])) {
            $_SESSION["error"] = "Tots els camps són obligatoris.";
        } elseif (
            !Validator::passwordConfirmation($password, $confirmPassword)
        ) {
            $_SESSION["error"] = "Les contrasenyes no coincideixen.";
        } elseif (!Validator::passwordStrength($password)) {
            $_SESSION["error"] =
                "La contrasenya ha de tenir com a mínim 10 caràcters, lletres majúscules i minúscules, números i símbols.";
        } elseif (
            !$this->userModel->resetPassword(
                hash("sha256", $token),
                password_hash($password, PASSWORD_ARGON2ID),
            )
        ) {
            $_SESSION["error"] =
                "L'enllaç no és vàlid o ha caducat. Demana'n un de nou.";
        } else {
            $_SESSION["notice"] =
                "Contrasenya actualitzada. Ja pots iniciar sessió.";
            header("Location: index.php?action=login");
            return;
        }

        header(
            "Location: index.php?action=resetpassword&token=" .
                urlencode($token),
        );
    }

    /**
     * Tanca la sessió de l'usuari.
     */
    public function logout(): void
    {
        $this->session->destroy();
        header("Location: index.php?action=index");
    }

    /**
     * Desa l'error de registre i torna al formulari.
     * @param string $message Missatge d'error de registre que es desa a la sessió.
     */
    private function setRegisterError(string $message): void
    {
        $_SESSION["error"] = $message;
        header("Location: index.php?action=register");
    }

    /**
     * Desa l'error d'inici de sessió i torna al formulari.
     * @param string $message Missatge d'error d'inici de sessió que es desa a la sessió.
     */
    private function setLoginError(string $message): void
    {
        $_SESSION["error"] = $message;
        header("Location: index.php?action=login");
    }
}
