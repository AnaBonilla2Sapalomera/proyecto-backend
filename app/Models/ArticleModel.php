<?php

namespace App\Models;

/**
 * Consulta i modifica els articles de la base de dades.
 */
class ArticleModel
{
    /**
     * @param \PDO $connection connexió amb la base de dades
     */
    public function __construct(private \PDO $connection) {}

    /**
     * Obtenir els articles paginats.
     *
     * @param int $page número de pàgina que es vol consultar
     *
     * @return array<int, mixed> articles de la pàgina actual
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
        $statement->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * Comprovar si hi ha articles en una pàgina.
     *
     * @param int $page número de pàgina que es vol consultar
     *
     * @return bool si hi ha aquesta pàgina
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
        $statement->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch(\PDO::FETCH_ASSOC) !== false;
    }

    /**
     * Obtenir un article.
     *
     * @param int $id identificador de l'article
     *
     * @return array|false L'article o false
     */
    public function getById(int $id): array|false
    {
        $sql = '
            SELECT id, user_id, title, content
            FROM articles
            WHERE id = :id
        ';

        $statement = $this->connection->prepare($sql);
        $statement->execute([':id' => $id]);

        return $statement->fetch();
    }

    /**
     * Crear un article.
     *
     * @param int    $userId  identificador de l'autor
     * @param string $title   títol del article
     * @param string $content contingut del article
     *
     * @return bool si s'ha creat
     */
    public function create(int $userId, string $title, string $content): bool
    {
        $sql = '
            INSERT INTO articles (user_id, title, content)
            VALUES (:user_id, :title, :content)
        ';

        $statement = $this->connection->prepare($sql);

        return $statement->execute([
            ':user_id' => $userId,
            ':title' => $title,
            ':content' => $content,
        ]);
    }

    /**
     * Actualitzar un article.
     *
     * @param int    $id      id del article
     * @param int    $userId  identificador de l'autor de l'article
     * @param string $title   títol del article
     * @param string $content contingut del article
     *
     * @return bool si s'ha actualitzat
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
            ':id' => $id,
            ':user_id' => $userId,
            ':title' => $title,
            ':content' => $content,
        ]);
    }

    /**
     * Eliminar un article.
     *
     * @param int $id     id del article
     * @param int $userId id de l'usuari
     *
     * @return bool si s'ha eliminat
     */
    public function delete(int $id, int $userId): bool
    {
        $sql = '
            DELETE FROM articles
            WHERE id = :id AND user_id = :user_id
        ';

        $statement = $this->connection->prepare($sql);

        return $statement->execute([':id' => $id, ':user_id' => $userId]);
    }
}
