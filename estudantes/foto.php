<?php
require __DIR__ . '/../src/init.php';

$fotoId = objectId($_GET['id'] ?? '');
if (!$fotoId) {
    http_response_code(404);
    exit;
}

$bucket = Conexao::fotos();
try {
    $info = $bucket->findOne(['_id' => $fotoId]);
    if (!$info) throw new Exception('não existe');
    $stream = $bucket->openDownloadStream($fotoId);
} catch (Throwable $e) {
    http_response_code(404);
    exit;
}

header('Content-Type: ' . ($info['metadata']['contentType'] ?? 'image/jpeg'));
header('Content-Length: ' . $info['length']);
header('Cache-Control: public, max-age=86400');
fpassthru($stream);
