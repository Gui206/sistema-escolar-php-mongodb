<?php
require __DIR__ . '/../src/init.php';
exigirPost();

Matricula::excluir($_POST['id'] ?? '')
    ? flash('success', 'Matrícula removida.')
    : flash('danger', 'Matrícula não encontrada.');

$voltar = $_POST['voltar'] ?? '';
redirecionar(preg_match('#^(estudantes|cursos)/ver\.php\?id=[a-f0-9]{24}$#', $voltar) ? '../' . $voltar : 'index.php');
