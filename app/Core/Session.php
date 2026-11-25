<?php

namespace App\Core;

/**
 * Gestiona la sessió i l'autenticació de l'usuari.
 */
class Session
{
    private int $duration = 2400;

    /**
     * Inicia la sessió.
     */
    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set("session.use_strict_mode", "1");

            session_set_cookie_params([
                "lifetime" => 0,
                "path" => "/",
                "httponly" => true,
                "secure" => !empty($_SERVER["HTTPS"]),
                "samesite" => "Lax",
            ]);

            session_start();
        }

        $this->checkExpiration();
    }

    /**
     * Comprova si la sessió ha caducat.
     */
    private function checkExpiration(): void
    {
        if (
            isset($_SESSION["user_id"]) &&
            isset($_SESSION["last_activity"]) &&
            time() - $_SESSION["last_activity"] > $this->duration
        ) {
            $this->destroy();

            return;
        }

        $_SESSION["last_activity"] = time();
    }

    /**
     * Regenera l'identificador de sessió.
     */
    public function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /**
     * Desa les dades d'autenticació de l'usuari a la sessió.
     * @param int $userId Identificador de l'usuari autenticat.
     * @param string $username Nom de l'usuari autenticat.
     */
    public function login(int $userId, string $username): void
    {
        $this->regenerate();

        $_SESSION["user_id"] = $userId;
        $_SESSION["user_name"] = $username;
        $_SESSION["last_activity"] = time();
    }

    /**
     * Destrueix la sessió.
     */
    public function destroy(): void
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                "",
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"],
            );
        }

        session_destroy();
    }

    /**
     * Comprova si hi ha un usuari autenticat.
     * @return bool Indica si hi ha un identificador d'usuari a la sessió.
     */
    public function isLoggedIn(): bool
    {
        return isset($_SESSION["user_id"]);
    }

    /**
     * Retorna l'ID de l'usuari actual.
     * @return int|null Identificador de l'usuari o null si no està autenticat.
     */
    public function getUserId(): ?int
    {
        return $_SESSION["user_id"] ?? null;
    }

    /**
     * Retorna el nom d'usuari de l'usuari actual.
     * @return string|null Nom d'usuari o null si no està autenticat.
     */
    public function getUserName(): ?string
    {
        return $_SESSION["user_name"] ?? null;
    }
}
