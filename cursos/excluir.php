<?php
require __DIR__ . '/../src/init.php';
exigirPost();

if (Curso::excluir($_POST['id'] ?? '')) {
    flash('success', 'Curso excluído (com as matrículas dele).');
} else {
    flash('danger', 'Curso não encontrado.');
}
redirecionar('index.php');
