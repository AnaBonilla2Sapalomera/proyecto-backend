<?php
/**
 * @var ?string               $error
 * @var array<string, string> $old
 */
?>

<!DOCTYPE html>
<html lang="ca">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>PrjF1 Ana - Crear article</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
    <?php require 'nav.php'; ?>
    <h1>Crear article</h1>
    <form action="index.php?action=create" method="post" class="form">
        <label>
            <span>Títol</span>
            <input
                name="title"
                required
                value="<?= htmlspecialchars($old['title'] ?? ''); ?>"
            />
        </label>
        <label>
            <span>Contingut</span>
            <textarea name="content" required><?= htmlspecialchars(
                $old['content'] ?? '',
            ); ?></textarea>
        </label>
        <?php if ($error !== null) { ?>
            <p class="form-error">Error: <?= htmlspecialchars($error); ?></p>
        <?php } ?>
        <button type="submit" class="form-button">Enviar</button>
    </form>
</body>
</html>
