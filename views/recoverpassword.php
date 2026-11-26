<?php
/**
 * @var ?string $error
 * @var ?string $notice
 */
?>
<!DOCTYPE html>
<html lang="ca">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>PrjF1 Ana - Recuperar contrasenya</title>
	<link rel="stylesheet" href="/style.css">
</head>
<body>
    <?php require 'nav.php'; ?>
    <h1>Recuperar la contrasenya</h1>
    <p>Introdueix el correu del teu compte i t'enviarem un enllaç per canviar-la.</p>
    <form action="/recover-password" method="post" class="form">
        <label>
            Correu electrònic
            <input type="email" name="email" autocomplete="email" required>
        </label>
        <button type="submit" class="form-button">Enviar enllaç</button>
        <?php if (isset($error)) { ?>
            <p class="form-error"><?= htmlspecialchars($error); ?></p>
        <?php } ?>
        <?php if (isset($notice)) { ?>
            <p><?= htmlspecialchars($notice); ?></p>
        <?php } ?>
        <a href="/login">Tornar a iniciar sessió</a>
    </form>
</body>
</html>
