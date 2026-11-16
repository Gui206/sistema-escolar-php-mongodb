<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    exit('Biblioteca do MongoDB não instalada. Rode "composer install" na pasta do projeto.');
}
require_once $autoload;
require_once __DIR__ . '/funcoes.php';
require_once __DIR__ . '/Conexao.php';
require_once __DIR__ . '/Estudante.php';
require_once __DIR__ . '/Curso.php';
require_once __DIR__ . '/Matricula.php';

set_exception_handler(function (Throwable $e) {
    http_response_code(500);
    $conexao = $e instanceof MongoDB\Driver\Exception\ConnectionException
        || $e instanceof MongoDB\Driver\Exception\ConnectionTimeoutException
        || $e instanceof MongoDB\Driver\Exception\InvalidArgumentException
        || $e instanceof ConfigException;
    echo '<!doctype html><meta charset="utf-8"><title>Erro</title>'
        . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">'
        . '<div class="container py-5" style="max-width:720px"><div class="alert alert-danger">'
        . '<h5 class="alert-heading">' . ($conexao ? 'Não foi possível falar com o banco de dados' : 'Ocorreu um erro') . '</h5>'
        . '<p class="mb-2">' . h($e->getMessage()) . '</p>'
        . ($conexao ? '<hr><p class="mb-0 small">Confira o <code>config.php</code> e a sua internet. Redes de faculdade/empresa costumam bloquear a porta 27017 do MongoDB Atlas: teste com os dados móveis do celular.</p>' : '')
        . '</div><a href="javascript:history.back()" class="btn btn-outline-secondary">Voltar</a></div>';
});
