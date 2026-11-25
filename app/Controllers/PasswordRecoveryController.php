<?php

namespace App\Controllers;

use App\Functions\Validator;
use App\Models\UserModel;
use App\Services\EmailService;

/**
 * Gestiona la recuperació i el canvi de contrasenya.
 */
class PasswordRecoveryController
{
    public function __construct(
        private UserModel $userModel,
        private EmailService $emailService,
    ) {}

    /**
     * Mostra el formulari per demanar un enllaç de recuperació.
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
}
