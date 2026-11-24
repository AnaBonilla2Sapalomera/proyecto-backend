<?php

namespace App\Functions;

class Validator
{
    /**
     * Comprova que tots els camps obligatoris continguin informació.
     *
     * @param array<string> $fields
     */
    public static function requiredFields(array $fields): bool
    {
        foreach ($fields as $field) {
            if (trim($field) === "") {
                return false;
            }
        }

        return true;
    }

    /**
     * Comprova que el correu electrònic tingui un format vàlid.
     */
    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Comprova que les dues contrasenyes coincideixin.
     */
    public static function passwordConfirmation(
        string $password,
        string $confirmPassword,
    ): bool {
        return $password === $confirmPassword;
    }

    /**
     * Comprova que la contrasenya compleixi els requisits de seguretat.
     */
    public static function passwordStrength(string $password): bool
    {
        return strlen($password) >= 10 &&
            preg_match("/[A-Z]/", $password) === 1 &&
            preg_match("/[a-z]/", $password) === 1 &&
            preg_match("/[0-9]/", $password) === 1 &&
            preg_match("/[^a-zA-Z0-9]/", $password) === 1;
    }
}
