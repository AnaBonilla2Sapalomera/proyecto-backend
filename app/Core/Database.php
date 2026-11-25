<?php

namespace App\Core;

use Exception;
use PDO;
use PDOException;

/**
 * Gestiona la connexió amb la base de dades.
 */
class Database
{
    private ?PDO $connection = null;

    /**
     * Retorna la connexió PDO, creant-la si cal.
     */
    public function connect(): PDO
    {
        if ($this->connection === null) {
            try {
                $this->connection = new PDO(
                    $GLOBALS["cfg"]["database"]["dsn"],
                    $GLOBALS["cfg"]["database"]["username"],
                    $GLOBALS["cfg"]["database"]["password"],
                );

                $this->connection->setAttribute(
                    PDO::ATTR_ERRMODE,
                    PDO::ERRMODE_EXCEPTION,
                );

                $this->connection->setAttribute(
                    PDO::ATTR_DEFAULT_FETCH_MODE,
                    PDO::FETCH_ASSOC,
                );
            } catch (PDOException $e) {
                error_log($e->getMessage());

                throw new Exception(
                    "No s'ha pogut connectar amb la base de dades.",
                );
            }
        }

        return $this->connection;
    }
}
