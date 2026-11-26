<nav>
    <a href="index.php?action=index">Inici</a>
    <?php if ($GLOBALS['session']->isLoggedIn()) { ?>
        <a href="index.php?action=create">Crear article</a>
        <form action="index.php?action=logout" method="post">
            <button type="submit" class="link">Tancar sessió</button>
        </form>
        <div>Usuari: <?= $GLOBALS['session']->getUserName(); ?></div>
    <?php } else { ?>
        <a href="index.php?action=login">Iniciar sessió</a>
        <a href="index.php?action=register">Registre</a>
    <?php } ?>
</nav>
