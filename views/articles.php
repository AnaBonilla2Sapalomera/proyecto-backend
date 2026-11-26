<?php
/**
 * @var int               $page
 * @var array<int, mixed> $articles
 * @var bool              $nextPageHasArticles
 */

// Obtenir els paràmetres actuals de la URL
$query = $_GET;

// Crear la URL de la pàgina anterior
$query['page'] = max(1, $page - 1);
$prevUrl = '?' . http_build_query($query);

// Crear la URL de la pàgina següent
$query['page'] = $page + 1;
$nextUrl = '?' . http_build_query($query);

// Comprovar si els enllaços estan habilitats
$prevDisabled = $page <= 1;
$nextDisabled = !$nextPageHasArticles;
?>

<!DOCTYPE html>
<html lang="ca">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>PrjF1 Ana - Llista d'articles</title>
	<link rel="stylesheet" href="/style.css">
</head>
<body>
    <?php require 'nav.php'; ?>
    <h1>Llista d'articles</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Títol</th>
                <th>Contingut</th>
                <th>Accions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $article) { ?>
                <tr>
                    <td><?= htmlspecialchars($article['id']); ?></td>
                    <td><?= htmlspecialchars($article['title']); ?></td>
                    <td style="width: 60%"><?= htmlspecialchars(
                        $article['content'],
                    ); ?></td>
                    <td>
                        <?php if (
                            $GLOBALS['session']->isLoggedIn()
                            && $GLOBALS['session']->getUserId()
                                == $article['user_id']
                        ) { ?>
                            <a href="/article/edit?id=<?= $article[
                                'id'
                            ]; ?>">Editar</a>
                            -
                            <form action="/article/delete" method="post" style="display: inline">
                                <input type="hidden" name="id" value="<?= htmlspecialchars(
                                    $article['id'],
                                ); ?>">
                                <button type="submit" class="articles-button">Eliminar</button>
                            </form>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <nav>
        <?php if ($prevDisabled) { ?>
            <span>Anterior</span>
        <?php } else { ?>
            <a href="<?= htmlspecialchars($prevUrl); ?>">Anterior</a>
        <?php } ?>

        <span> - </span>

        <?php if ($nextDisabled) { ?>
            <span>Següent</span>
        <?php } else { ?>
            <a href="<?= htmlspecialchars($nextUrl); ?>">Següent</a>
        <?php } ?>
    </nav>
</body>
</html>
