<?php
/*
 * MODELO de configuração.
 * 1. Copie este arquivo e renomeie a cópia para "config.php"
 * 2. Troque o endereço abaixo pelo seu (Atlas ou local)
 *
 * O config.php real NÃO vai para o GitHub (está no .gitignore),
 * assim a senha do banco não fica pública.
 */
return [
    // Atlas (nuvem):
    'uri'   => 'mongodb+srv://USUARIO:SENHA@cluster0.xxxxx.mongodb.net/',
    // ou local:  'mongodb://localhost:27017'

    'banco' => 'escola',
];
