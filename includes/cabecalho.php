<?php

$raiz   = $raiz ?? '';
$titulo = $titulo ?? 'Sistema Escolar';
$menu   = $menu ?? '';
$itensMenu = [
    'inicio'     => ['Início', 'index.php', 'grid-3x3-gap'],
    'estudantes' => ['Estudantes', 'estudantes/index.php', 'people'],
    'cursos'     => ['Cursos', 'cursos/index.php', 'journal-bookmark'],
    'matriculas' => ['Matrículas', 'matriculas/index.php', 'link-45deg'],
];
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($titulo) ?> · Sistema Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= $raiz ?>assets/css/estilo.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-md navbar-dark barra-app py-1">
    <div class="container-fluid px-3">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $raiz ?>index.php">
            <span class="logo">SE</span> Sistema Escolar
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-controls="menu" aria-expanded="false" aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto gap-1 py-2 py-md-0">
                <?php foreach ($itensMenu as $chave => [$nome, $link, $icone]): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $menu === $chave ? 'active' : '' ?>" href="<?= $raiz . $link ?>">
                            <i class="bi bi-<?= $icone ?> me-1"></i><?= $nome ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>
