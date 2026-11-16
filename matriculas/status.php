<?php
require __DIR__ . '/../src/init.php';
exigirPost();

$erro = Matricula::alterarStatus($_POST['id'] ?? '', $_POST['status'] ?? '');
$erro ? flash('danger', $erro) : flash('success', 'Status da matrícula alterado.');
redirecionar($_SERVER['HTTP_REFERER'] ?? 'index.php');
