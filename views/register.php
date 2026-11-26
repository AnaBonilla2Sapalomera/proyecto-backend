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
	<title>PrjF1 Ana - Registre</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
    <?php require 'nav.php'; ?>
    <h1>Registre</h1>

    <form action="index.php?action=register" method="post" class="form">

        <label>
            Nom d'usuari
            <input
                type="text"
                name="username"
                value="<?= htmlspecialchars(
                    $old['username'] ?? '',
                ); ?>"
                required
            >
        </label>

        <label>
            Correu electrònic
            <input
                type="email"
                name="email"
                value="<?= htmlspecialchars($old['email'] ?? ''); ?>"
                required
            >
        </label>

        <label>
            Contrasenya
            <input type="password" name="password" autocomplete="new-password" required>
        </label>

        <label>
            Confirmar contrasenya
            <input type="password" name="confirmPassword" autocomplete="new-password" required>
        </label>

        <button type="submit" class="form-button">Registrar-se</button>

        <?php if (isset($error)) { ?>
            <p class="form-error"><?= htmlspecialchars($error); ?></p>
        <?php } ?>

        <a href="index.php?action=login">Ja tens compte? Inicia sessió</a>

    </form>

</body>
</html>
