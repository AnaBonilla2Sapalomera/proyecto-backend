<?php
/**
 * @var array<string, mixed>  $article
 * @var ?string               $error
 * @var array<string, string> $old
 */
$title = $old['title'] ?? $article['title'];
$content = $old['content'] ?? $article['content'];
?>
<!DOCTYPE html>
<html lang="ca">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>PrjF1 Ana - Editar article</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
    <?php require 'nav.php'; ?>
    <h1>Editar article</h1>
    <form action="index.php?action=edit" method="post" class="form">
        <input type="hidden" name="id" value="<?= htmlspecialchars(
            $article['id'],
        ); ?>">
        <label>
            <span>Títol</span>
            <input name="title" required value="<?= htmlspecialchars(
                $title,
            ); ?>">
        </label>
        <label>
            <span>Contingut</span>
            <textarea name="content" required><?= htmlspecialchars(
                $content,
            ); ?></textarea>
        </label>
        <?php if (isset($error)) { ?>
            <p class="form-error"><?= htmlspecialchars($error); ?></p>
        <?php } ?>
        <button type="submit" class="form-button">Desar els canvis</button>
        <a href="index.php?action=index">Cancel·lar</a>
    </form>
</body>
</html>
