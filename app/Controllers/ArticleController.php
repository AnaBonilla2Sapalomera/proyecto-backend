<?php

namespace App\Controllers;

use App\Core\Session;
use App\Functions\Validator;
use App\Models\ArticleModel;

/**
 * Gestiona les accions relacionades amb els articles.
 */
class ArticleController
{
    /**
     * Inicialitza el model d'articles i la sessió.
     *
     * @param ArticleModel $articleModel model que consulta i modifica els articles
     * @param Session      $session      servei que comprova l'estat de la sessió
     */
    public function __construct(
        private ArticleModel $articleModel,
        private Session $session,
    ) {}

    /**
     * Mostra la llista paginada d'articles.
     */
    public function index(): void
    {
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
        if ($page === false || $page === null || $page <= 0) {
            header('Location: index.php?page=1');

            return;
        }

        $articles = $this->articleModel->getPaginated($page);

        if (empty($articles)) {
            if ($page > 1) {
                header('Location: index.php?page=1');

                return;
            }
        }

        $nextPageHasArticles = $this->articleModel->pageHasItems($page + 1);

        require __DIR__ . '/../../views/articles.php';
    }

    /**
     * Mostra el formulari per crear un article.
     */
    public function showCreate(): void
    {
        if (!$this->session->isLoggedIn()) {
            header('Location: index.php?action=login');

            return;
        }

        $error = $_SESSION['error'] ?? null;
        $old = $_SESSION['old'] ?? [];

        unset($_SESSION['error'], $_SESSION['old']);

        require __DIR__ . '/../../views/create.php';
    }

    /**
     * Valida i desa un article nou.
     */
    public function create(): void
    {
        if (!$this->session->isLoggedIn()) {
            header('Location: index.php?action=login');

            return;
        }

        $userId = $this->session->getUserId();
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        $_SESSION['old'] = [
            'title' => $title,
            'content' => $content,
        ];

        if (!Validator::requiredFields([$title, $content])) {
            $_SESSION['error'] = 'El títol i el contingut són obligatoris.';
            header('Location: index.php?action=create');

            return;
        }

        unset($_SESSION['old']);

        $this->articleModel->create($userId, $title, $content);

        header('Location: index.php?action=index');
    }

    /**
     * Mostra el formulari per editar un article propi.
     */
    public function showEdit(): void
    {
        if (!$this->session->isLoggedIn()) {
            header('Location: index.php?action=login');

            return;
        }

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $userId = $this->session->getUserId();
        $article = $id ? $this->articleModel->getById($id) : false;
        if (!$article || $article['user_id'] !== $userId) {
            header('Location: index.php?page=1');

            return;
        }

        $error = $_SESSION['error'] ?? null;
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['error'], $_SESSION['old']);
        require __DIR__ . '/../../views/edit.php';
    }

    /**
     * Valida i actualitza un article propi.
     */
    public function update(): void
    {
        if (!$this->session->isLoggedIn()) {
            header('Location: index.php?action=login');

            return;
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $userId = $this->session->getUserId();
        $article = $id ? $this->articleModel->getById($id) : false;
        if (!$article || $article['user_id'] !== $userId) {
            header('Location: index.php?page=1');

            return;
        }

        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        $_SESSION['old'] = ['title' => $title, 'content' => $content];

        if (!Validator::requiredFields([$title, $content])) {
            $_SESSION['error'] = 'El títol i el contingut són obligatoris.';
            header("Location: index.php?action=edit&id=$id");

            return;
        }

        if (!$this->articleModel->update($id, $userId, $title, $content)) {
            $_SESSION['error'] = "No s'ha pogut actualitzar l'article.";
            header("Location: index.php?action=edit&id=$id");

            return;
        }

        unset($_SESSION['error'], $_SESSION['old']);

        header('Location: index.php?action=index');
    }

    /**
     * Elimina un article propi.
     */
    public function delete(): void
    {
        if (!$this->session->isLoggedIn()) {
            header('Location: index.php?action=login');

            return;
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            header('Location: index.php?page=1');

            return;
        }

        $userId = $this->session->getUserId();
        $this->articleModel->delete($id, $userId);

        header('Location: index.php?action=index');
    }
}
