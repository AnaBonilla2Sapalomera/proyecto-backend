<?php

namespace App\Functions;

/**
 * Valida les dades dels formularis.
 */
class Validator
{
    /**
     * Comprova que els camps obligatoris no estiguin buits ni continguin espais.
     * @param array<string> $fields Valors dels camps obligatoris.
     * @return bool Indica si tots els camps contenen informació.
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
     * @param string $email Adreça electrònica que es vol validar.
     * @return bool Indica si el format és vàlid.
     */
    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Comprova que les dues contrasenyes coincideixin.
     * @param string $password Contrasenya introduïda.
     * @param string $confirmPassword Confirmació de la contrasenya.
     * @return bool Indica si les dues contrasenyes coincideixen.
     */
    public static function passwordConfirmation(
        string $password,
        string $confirmPassword,
    ): bool {
        return $password === $confirmPassword;
    }

    /**
     * Comprova que la contrasenya compleixi els requisits de seguretat.
     * @param string $password Contrasenya que es vol validar.
     * @return bool Indica si compleix els requisits de seguretat.
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
