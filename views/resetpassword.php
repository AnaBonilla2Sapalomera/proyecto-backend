<?php
/** @var ?string $error */
/** @var string $token */
?>
<!DOCTYPE html>
<html lang="ca">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>PrjF1 Ana - Canviar contrasenya</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
    <?php require "nav.php"; ?>
    <h1>Canviar la contrasenya</h1>
    <form action="index.php?action=resetpassword" method="post" class="form">
        <input type="hidden" name="token" value="<?= htmlspecialchars(
            $token,
        ) ?>">
        <label>
            Nova contrasenya
            <input type="password" name="password" autocomplete="new-password" required>
        </label>
        <label>
            Confirmar contrasenya
            <input type="password" name="confirmPassword" autocomplete="new-password" required>
        </label>
        <button type="submit" class="form-button">Guardar contrasenya</button>
        <?php if (isset($error)): ?>
            <p class="form-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </form>
</body>
</html>
