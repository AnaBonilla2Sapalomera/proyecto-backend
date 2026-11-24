<?php

namespace App\Controllers;

use App\Core\Session;
use App\Models\ArticleModel;

class ArticleController
{
    private ArticleModel $articleModel;
    private Session $session;

    /**
     * Inicialitzar el controlador amb el model d'articles.
     */
    public function __construct(ArticleModel $articleModel, Session $session)
    {
        $this->articleModel = $articleModel;
        $this->session = $session;
    }

    /**
     * Mostra la llista de tots els articles.
     */
    public function index(): void
    {
        $page = filter_input(INPUT_GET, "page", FILTER_VALIDATE_INT);
        if ($page === false || $page === null || $page <= 0) {
            header("Location: index.php?page=1");
            return;
        }

        $articles = $this->articleModel->getPaginated($page);

        if (empty($articles)) {
            if ($page > 1) {
                header("Location: index.php?page=1");
                return;
            }
        }

        $nextPageHasArticles = $this->articleModel->pageHasItems($page + 1);

        require __DIR__ . "/../../views/articles.php";
    }

    public function showCreate(): void
    {
        if (!$this->session->isLoggedIn()) {
            header("Location: index.php?page=1");
            return;
        }

        $error = $_SESSION["error"] ?? null;
        $old = $_SESSION["old"] ?? [];

        unset($_SESSION["error"], $_SESSION["old"]);

        require __DIR__ . "/../../views/create.php";
    }

    public function create(): void
    {
        if (!$this->session->isLoggedIn()) {
            header("Location: index.php?page=1");
            return;
        }

        $userId = $this->session->getUserId();
        $title = trim($_POST["title"] ?? "");
        $content = trim($_POST["content"] ?? "");

        $_SESSION["old"] = [
            "title" => $title,
            "content" => $content,
        ];

        if (empty($title) || empty($content)) {
            $_SESSION["error"] = "El título y el contenido son obligatorios.";
            header("Location: index.php?action=create");
            return;
        }

        unset($_SESSION["old"]);

        $this->articleModel->create($userId, $title, $content);

        header("Location: index.php?action=index");
    }
}
