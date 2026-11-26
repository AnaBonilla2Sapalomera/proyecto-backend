<nav>
    <a href="/">Inici</a>
    <?php if ($GLOBALS['session']->isLoggedIn()) { ?>
        <a href="/article/create">Crear article</a>
        <form action="/logout" method="post">
            <button type="submit" class="link">Tancar sessió</button>
        </form>
        <div>Usuari: <?= $GLOBALS['session']->getUserName(); ?></div>
    <?php } else { ?>
        <a href="/login">Iniciar sessió</a>
        <a href="/register">Registre</a>
    <?php } ?>
</nav>
