<?php

namespace App\Models;

use PDO;

class UserModel
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

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

    public function findByEmail(string $email): mixed
    {
        $stmt = $this->connection->prepare(
            "SELECT id, username, email FROM users WHERE email = :email",
        );
        $stmt->execute(["email" => $email]);
        return $stmt->fetch();
    }

    public function storePasswordResetToken(int $userId, string $tokenHash, string $expiresAt): void
    {
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
        } catch (\Throwable $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }

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
        } catch (\Throwable $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }
}
