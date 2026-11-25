<?php

namespace App\Models;

use PDO;

/**
 * Consulta i modifica els articles de la base de dades.
 */
class ArticleModel
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
     * Obtenir els articles paginats.
     * @param int $page Número de pàgina que es vol consultar.
     * @return array<int, mixed> Articles de la pàgina actual.
     */
    public function getPaginated(int $page): array
    {
        $limit = 5;
        $offset = ($page - 1) * $limit;

        $sql = '
            SELECT id, user_id, title, content
            FROM articles
            ORDER BY id DESC
            LIMIT :limit OFFSET :offset
        ';

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(":limit", $limit, PDO::PARAM_INT);
        $statement->bindValue(":offset", $offset, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    /**
     * Comprovar si hi ha articles en una pàgina.
     * @param int $page Número de pàgina que es vol consultar.
     * @return bool Si hi ha aquesta pàgina.
     */
    public function pageHasItems(int $page): bool
    {
        $limit = 5;
        $offset = ($page - 1) * $limit;

        $sql = '
            SELECT id
            FROM articles
            ORDER BY id DESC
            LIMIT 1 OFFSET :offset
        ';

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(":offset", $offset, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC) !== false;
    }

    /**
     * Obtenir un article.
     * @param int $id Identificador de l'article.
     * @return array|false L'article o false.
     */
    public function getById(int $id): array|false
    {
        $sql = '
            SELECT id, user_id, title, content
            FROM articles
            WHERE id = :id
        ';

        $statement = $this->connection->prepare($sql);
        $statement->execute([":id" => $id]);
        return $statement->fetch();
    }

    /**
     * Crear un article.
     * @param int $userId Identificador de l'autor.
     * @param string $title Títol del article.
     * @param string $content Contingut del article.
     * @return bool Si s'ha creat.
     */
    public function create(int $userId, string $title, string $content): bool
    {
        $sql = '
            INSERT INTO articles (user_id, title, content)
            VALUES (:user_id, :title, :content)
        ';

        $statement = $this->connection->prepare($sql);
        return $statement->execute([
            ":user_id" => $userId,
            ":title" => $title,
            ":content" => $content,
        ]);
    }

    /**
     * Actualitzar un article.
     * @param int $id Id del article.
     * @param int $userId Identificador de l'autor de l'article.
     * @param string $title Títol del article.
     * @param string $content Contingut del article.
     * @return bool Si s'ha actualitzat.
     */
    public function update(
        int $id,
        int $userId,
        string $title,
        string $content,
    ): bool {
        $sql = '
            UPDATE articles
            SET title = :title,
                content = :content
            WHERE id = :id AND user_id = :user_id
        ';

        $statement = $this->connection->prepare($sql);
        return $statement->execute([
            ":id" => $id,
            ":user_id" => $userId,
            ":title" => $title,
            ":content" => $content,
        ]);
    }

    /**
     * Eliminar un article.
     * @param int $id Id del article.
     * @return bool Si s'ha eliminat.
     */
    public function delete(int $id): bool
    {
        $sql = '
            DELETE FROM articles
            WHERE id = :id
        ';

        $statement = $this->connection->prepare($sql);
        return $statement->execute([":id" => $id]);
    }
}
