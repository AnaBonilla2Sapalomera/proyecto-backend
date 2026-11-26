<?php
/**
 * @var ?string               $error
 * @var ?string               $notice
 * @var array<string, string> $old
 */
?>

<!DOCTYPE html>
<html lang="ca">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>PrjF1 Ana - Iniciar sessió</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
    <?php require 'nav.php'; ?>
    <h1>Iniciar sessió</h1>

    <form action="index.php?action=login" method="post" class="form">

        <label>
            Correu electrònic
            <input
                type="text"
                name="email"
                value="<?= htmlspecialchars($old['email'] ?? ''); ?>"
                required
            >
        </label>

        <label>
            Contrasenya
            <input type="password" name="password" required>
        </label>

        <button type="submit" class="form-button">Iniciar sessió</button>

        <?php if (isset($error)) { ?>
            <p class="form-error"><?= htmlspecialchars($error); ?></p>
        <?php } ?>
        <?php if (isset($notice)) { ?>
            <p><?= htmlspecialchars($notice); ?></p>
        <?php } ?>

        <a href="index.php?action=recoverpassword">Recuperar la contrasenya</a>
        <a href="index.php?action=register">No tens compte? Registra-t'hi</a>

    </form>

</body>
</html>
