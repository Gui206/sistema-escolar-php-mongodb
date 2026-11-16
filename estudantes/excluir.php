<?php
require __DIR__ . '/../src/init.php';
exigirPost();

if (Estudante::excluir($_POST['id'] ?? '')) {
    flash('success', 'Estudante excluído (com a foto e as matrículas dele).');
} else {
    flash('danger', 'Estudante não encontrado.');
}
redirecionar('index.php');
