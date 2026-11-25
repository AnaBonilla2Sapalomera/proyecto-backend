<?php

namespace App\Models;

use PDO;
use PDOException;

/**
 * Consulta i modifica els usuaris de la base de dades.
 */
class UserModel
{
    private PDO $connection;

    /**
     * @param PDO $connection Connexió amb la base de dades.
     */
    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Cerca un usuari pel nom o l'adreça electrònica.
     * @param string $value Nom d'usuari o correu electrònic que es vol cercar.
     * @return array<string, mixed>|false Dades de l'usuari o false si no existeix.
     */
    public function findByUsernameOrEmail(string $value): mixed
    {
        $sql = "
            SELECT id, username, email, password, created_at
            FROM users
            WHERE username = :value OR email = :value
        ";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute(["value" => $value]);

        return $stmt->fetch();
    }

    /**
     * Comprova si el nom o el correu ja estan registrats.
     * @param string $username Nom d'usuari que es vol comprovar.
     * @param string $email Correu electrònic que es vol comprovar.
     * @return bool Indica si algun dels dos valors ja existeix.
     */
    public function exists(string $username, string $email): bool
    {
        $sql = "
            SELECT id
            FROM users
            WHERE username = :username OR email = :email
        ";

        $stmt = $this->connection->prepare($sql);

        $stmt->execute([
            "username" => $username,
            "email" => $email,
        ]);

        return $stmt->fetch() !== false;
    }

    /**
     * Crea un usuari amb la contrasenya xifrada.
     * @param string $username Nom del nou usuari.
     * @param string $email Correu electrònic del nou usuari.
     * @param string $password Contrasenya xifrada que es desa al compte.
     * @return bool Indica si s'ha creat.
     */
    public function create(
        string $username,
        string $email,
        string $password,
    ): bool {
        $sql = "
            INSERT INTO users (username, email, password)
            VALUES (:username, :email, :password)
        ";

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            "username" => $username,
            "email" => $email,
            "password" => $password,
        ]);
    }

    /**
     * Cerca un usuari per adreça electrònica.
     * @param string $email Correu electrònic que es vol cercar.
     * @return array<string, mixed>|false Dades bàsiques de l'usuari o false si no existeix.
     */
    public function findByEmail(string $email): mixed
    {
        $stmt = $this->connection->prepare(
            "SELECT id, username, email FROM users WHERE email = :email",
        );
        $stmt->execute(["email" => $email]);
        return $stmt->fetch();
    }

    /**
     * Desa el token de recuperació i elimina els tokens anteriors.
     * @param int $userId Identificador de l'usuari.
     * @param string $tokenHash Hash del token de recuperació.
     * @param string $expiresAt Data i hora de caducitat del token.
     */
    public function storePasswordResetToken(
        int $userId,
        string $tokenHash,
        string $expiresAt,
    ): void {
        $this->connection->beginTransaction();
        try {
            $delete = $this->connection->prepare(
                "DELETE FROM password_resets WHERE user_id = :user_id",
            );
            $delete->execute(["user_id" => $userId]);

            $insert = $this->connection->prepare(
                "INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, :expires_at)",
            );
            $insert->execute([
                "user_id" => $userId,
                "token_hash" => $tokenHash,
                "expires_at" => $expiresAt,
            ]);
            $this->connection->commit();
        } catch (PDOException $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }

    /**
     * Canvia la contrasenya si el token encara és vàlid.
     * @param string $tokenHash Hash del token rebut.
     * @param string $passwordHash Nova contrasenya xifrada.
     * @return bool Indica si s'ha actualitzat la contrasenya.
     */
    public function resetPassword(string $tokenHash, string $passwordHash): bool
    {
        $this->connection->beginTransaction();
        try {
            $find = $this->connection->prepare(
                "SELECT user_id FROM password_resets WHERE token_hash = :token_hash AND expires_at > UTC_TIMESTAMP() FOR UPDATE",
            );
            $find->execute(["token_hash" => $tokenHash]);
            $reset = $find->fetch();
            if (!$reset) {
                $this->connection->commit();
                return false;
            }

            $update = $this->connection->prepare(
                "UPDATE users SET password = :password WHERE id = :user_id",
            );
            $update->execute([
                "password" => $passwordHash,
                "user_id" => $reset["user_id"],
            ]);
            $delete = $this->connection->prepare(
                "DELETE FROM password_resets WHERE user_id = :user_id",
            );
            $delete->execute(["user_id" => $reset["user_id"]]);
            $this->connection->commit();
            return true;
        } catch (PDOException $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }
}
