<?php

function h($valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function objectId($id): ?MongoDB\BSON\ObjectId
{
    try {
        return new MongoDB\BSON\ObjectId((string) $id);
    } catch (Throwable $e) {
        return null;
    }
}

function campo(string $nome): string
{
    return trim((string) ($_POST[$nome] ?? ''));
}

function somenteNumeros(string $texto): string
{
    return preg_replace('/\D/', '', $texto);
}

function flash(string $tipo, string $mensagem): void
{
    $_SESSION['flash'] = [$tipo, $mensagem];
}

function mostrarFlash(): void
{
    if (empty($_SESSION['flash'])) return;
    [$tipo, $mensagem] = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $icone = $tipo === 'success' ? 'check-circle' : 'exclamation-triangle';
    echo '<div class="alert alert-' . h($tipo) . ' alert-dismissible fade show d-flex align-items-center gap-2" role="alert">'
        . '<i class="bi bi-' . $icone . '"></i><div>' . h($mensagem) . '</div>'
        . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button></div>';
}

function redirecionar(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function exigirPost(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Método não permitido');
    }
}

function formatarCpf(string $cpf): string
{
    $n = somenteNumeros($cpf);
    return strlen($n) === 11 ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $n) : $cpf;
}

function formatarTelefone(string $tel): string
{
    $n = somenteNumeros($tel);
    if (strlen($n) === 11) return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $n);
    if (strlen($n) === 10) return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $n);
    return $tel;
}

function formatarCep(string $cep): string
{
    $n = somenteNumeros($cep);
    return strlen($n) === 8 ? substr($n, 0, 5) . '-' . substr($n, 5) : $cep;
}

function formatarData(?string $data): string
{
    if (!$data) return '';
    $d = DateTime::createFromFormat('Y-m-d', $data);
    return $d ? $d->format('d/m/Y') : $data;
}

function formatarDataMongo($data): string
{
    return $data instanceof MongoDB\BSON\UTCDateTime
        ? $data->toDateTime()->setTimezone(new DateTimeZone('America/Sao_Paulo'))->format('d/m/Y H:i')
        : '';
}

/** Iniciais do nome, usadas quando o aluno não tem foto */
function iniciais(string $nome): string
{
    $partes = preg_split('/\s+/', trim($nome));
    $ini = mb_substr($partes[0] ?? '', 0, 1) . (count($partes) > 1 ? mb_substr(end($partes), 0, 1) : '');
    return mb_strtoupper($ini);
}

/** Mostra a foto do aluno (ou um círculo com as iniciais) */
function avatar(array $estudante, string $raiz, string $tamanho = 'sm'): string
{
    $classe = 'avatar avatar-' . $tamanho;
    if (!empty($estudante['fotoId'])) {
        $url = $raiz . 'estudantes/foto.php?id=' . $estudante['fotoId'];
        return '<img src="' . h($url) . '" class="' . $classe . '" alt="Foto de ' . h($estudante['nome']) . '" loading="lazy">';
    }
    return '<span class="' . $classe . ' avatar-vazio" aria-hidden="true">' . h(iniciais($estudante['nome'] ?? '?')) . '</span>';
}

/**
 * Cabeçalho de tabela no estilo planilha:
 * uma linha com as letras (A, B, C...) e outra com os nomes das colunas.
 */
function cabecalhoPlanilha(array $titulos): string
{
    $letras = '<tr class="letras"><th class="num"></th>';
    $nomes  = '<tr class="titulos"><th class="num">#</th>';
    foreach (array_values($titulos) as $i => $titulo) {
        $letras .= '<th>' . chr(65 + $i) . '</th>';
        $nomes  .= '<th>' . h($titulo) . '</th>';
    }
    return '<thead>' . $letras . '</tr>' . $nomes . '</tr></thead>';
}

function classeErro(array $erros, string $campo): string
{
    return isset($erros[$campo]) ? ' is-invalid' : '';
}

function msgErro(array $erros, string $campo): string
{
    return isset($erros[$campo]) ? '<div class="invalid-feedback">' . h($erros[$campo]) . '</div>' : '';
}

const ESTADOS =['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
