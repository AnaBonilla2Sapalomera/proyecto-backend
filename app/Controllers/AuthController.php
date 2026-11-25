<?php

namespace App\Controllers;

use App\Core\Session;
use App\Functions\Validator;
use App\Models\UserModel;

/**
 * Gestiona el registre, l'inici de sessió i el tancament de sessió.
 */
class AuthController
{
    /**
     * Inicialitza les dependències del controlador.
     * @param UserModel $userModel Model que consulta i desa els usuaris.
     * @param Session $session Servei que gestiona la sessió de l'usuari.
     */
    public function __construct(
        private UserModel $userModel,
        private Session $session,
    ) {}

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
            $this->setRegisterError(
                "El nom d'usuari o l'adreça electrònica ja està registrat.",
            );
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
            $this->setLoginError(
                "L'adreça electrònica o la contrasenya són incorrectes.",
            );
            return;
        }

        unset($_SESSION["old"]);

        $this->session->login($user["id"], $user["username"]);

        header("Location: index.php?action=index");
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
