<?php
class ConfigException extends RuntimeException {}


class Conexao
{
    private static ?MongoDB\Database $banco = null;

    private function __construct() {}

    public static function banco(): MongoDB\Database
    {
        if (self::$banco === null) {
            $arquivo = __DIR__ . '/../config.php';
            if (!file_exists($arquivo)) {
                throw new ConfigException('Arquivo config.php não encontrado. Copie o config.exemplo.php para config.php e coloque o endereço do seu banco.');
            }
            $config = require $arquivo;
            $cliente = new MongoDB\Client($config['uri'], [], [
                // devolve os documentos como arrays comuns do PHP
                'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
            ]);
            self::$banco = $cliente->selectDatabase($config['banco'] ?? 'escola');
        }
        return self::$banco;
    }

    public static function colecao(string $nome): MongoDB\Collection
    {
        return self::banco()->selectCollection($nome);
    }

    public static function fotos(): MongoDB\GridFS\Bucket
    {
        return self::banco()->selectGridFSBucket(['bucketName' => 'fotos']);
    }
}
