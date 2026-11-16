<?php
/** Ficha do estudante: dados, foto e cursos em que está matriculado */
require __DIR__ . '/../src/init.php';

$e = Estudante::buscar($_GET['id'] ?? '');
if (!$e) {
    flash('danger', 'Estudante não encontrado.');
    redirecionar('index.php');
}
$matriculas = Matricula::doEstudante($e['_id']);
$jaInscrito = array_map(fn($m) => (string) $m['curso']['_id'], $matriculas);
$disponiveis = array_filter(Curso::todos(), fn($c) => !in_array((string) $c['_id'], $jaInscrito, true));
$end = $e['endereco'] ?? [];

$raiz = '../';
$titulo = $e['nome'];
$menu = 'estudantes';
require __DIR__ . '/../includes/cabecalho.php';
?>
<div class="ribbon">
    <div class="container-fluid px-3 d-flex flex-wrap align-items-center gap-2">
        <div class="me-auto">
            <div class="caminho"><a href="index.php" class="text-reset">Estudantes</a> › Ficha</div>
            <h1><?= h($e['nome']) ?></h1>
        </div>
        <a href="form.php?id=<?= $e['_id'] ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>Editar</a>
        <form action="excluir.php" method="post" data-confirmar="Excluir <?= h($e['nome']) ?>? As matrículas dele também serão apagadas.">
            <input type="hidden" name="id" value="<?= $e['_id'] ?>">
            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Excluir</button>
        </form>
    </div>
</div>

<main class="container-fluid px-3 py-3">
    <?php mostrarFlash(); ?>
    <div class="row g-3">
        <div class="col-12 col-lg-4 col-xl-3">
            <div class="ficha text-center">
                <div class="ficha-titulo"><i class="bi bi-camera"></i> Foto</div>
                <div class="ficha-corpo">
                    <?php if (!empty($e['fotoId'])): ?>
                        <a href="#" class="ver-foto d-inline-block" data-bs-toggle="modal" data-bs-target="#modalFoto"
                           data-foto="foto.php?id=<?= $e['fotoId'] ?>" data-nome="<?= h($e['nome']) ?>" title="Ampliar foto">
                            <?= avatar($e, $raiz, 'lg') ?>
                        </a>
                        <div class="small text-muted mt-2"><i class="bi bi-zoom-in"></i> Clique para ampliar</div>
                    <?php else: ?>
                        <?= avatar($e, $raiz, 'lg') ?>
                        <div class="small text-muted mt-2">Sem foto · <a href="form.php?id=<?= $e['_id'] ?>">adicionar</a></div>
                    <?php endif; ?>
                    <div class="fw-semibold mt-3"><?= h($e['nome']) ?></div>
                    <div class="small text-muted">RA <?= h($e['ra']) ?></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8 col-xl-9 d-flex flex-column gap-3">
            <div class="row g-3">
                <div class="col-12 col-xl-6">
                    <div class="ficha h-100">
                        <div class="ficha-titulo"><i class="bi bi-person-vcard"></i> Dados pessoais</div>
                        <dl class="dados">
                            <dt>CPF</dt><dd class="font-monospace"><?= h(formatarCpf($e['cpf'])) ?></dd>
                            <dt>E-mail</dt><dd><?= h($e['email']) ?></dd>
                            <dt>Telefone</dt><dd><?= h($e['telefone']) ?: '—' ?></dd>
                            <dt>Nascimento</dt><dd><?= formatarData($e['dataNascimento']) ?></dd>
                            <dt>Sexo</dt><dd><?= h($e['sexo']) ?: '—' ?></dd>
                        </dl>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="ficha h-100">
                        <div class="ficha-titulo"><i class="bi bi-geo-alt"></i> Endereço</div>
                        <dl class="dados">
                            <dt>CEP</dt><dd class="font-monospace"><?= h($end['cep'] ?? '') ?></dd>
                            <dt>Rua</dt><dd><?= h($end['logradouro'] ?? '') ?>, <?= h($end['numero'] ?? '') ?></dd>
                            <dt>Complemento</dt><dd><?= h($end['complemento'] ?? '') ?: '—' ?></dd>
                            <dt>Bairro</dt><dd><?= h($end['bairro'] ?? '') ?></dd>
                            <dt>Cidade / UF</dt><dd><?= h($end['cidade'] ?? '') ?> / <?= h($end['estado'] ?? '') ?></dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- CURSOS DO ALUNO (relacionamento N:N) -->
            <div class="folha">
                <div class="ficha-titulo">
                    <i class="bi bi-journal-bookmark"></i><b class="me-auto">Cursos matriculados</b>
                    <?php if ($disponiveis): ?>
                        <form action="../matriculas/nova.php" method="post" class="d-flex gap-2 flex-wrap">
                            <input type="hidden" name="estudanteId" value="<?= $e['_id'] ?>">
                            <input type="hidden" name="voltar" value="estudantes/ver.php?id=<?= $e['_id'] ?>">
                            <select name="cursoId" class="form-select form-select-sm" style="max-width:240px" required aria-label="Curso">
                                <option value="">Matricular em...</option>
                                <?php foreach ($disponiveis as $c): ?>
                                    <option value="<?= $c['_id'] ?>"><?= h($c['nome']) ?> (<?= h($c['codigo']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-verde btn-sm"><i class="bi bi-plus-lg"></i> Matricular</button>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="table-responsive">
                    <table class="table planilha">
                        <?= cabecalhoPlanilha(['Curso', 'Código', 'Professor', 'Turno', 'Matrícula', 'Status', '']) ?>
                        <tbody>
                        <?php if (!$matriculas): ?>
                            <tr class="vazia"><td colspan="8">Ainda não está matriculado em nenhum curso.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($matriculas as $i => $m): ?>
                            <tr>
                                <td class="num"><?= $i + 1 ?></td>
                                <td><a href="../cursos/ver.php?id=<?= $m['curso']['_id'] ?>" class="text-reset fw-semibold"><?= h($m['curso']['nome']) ?></a></td>
                                <td class="mono"><?= h($m['curso']['codigo']) ?></td>
                                <td><?= h($m['curso']['professor']) ?></td>
                                <td><?= h($m['curso']['turno']) ?></td>
                                <td class="mono"><?= formatarDataMongo($m['dataMatricula']) ?></td>
                                <td><span class="etiqueta etiqueta-<?= h($m['status']) ?>"><?= h($m['status']) ?></span></td>
                                <td class="acoes">
                                    <form action="../matriculas/excluir.php" method="post" class="d-inline" data-confirmar="Remover a matrícula em <?= h($m['curso']['nome']) ?>?">
                                        <input type="hidden" name="id" value="<?= $m['_id'] ?>">
                                        <input type="hidden" name="voltar" value="estudantes/ver.php?id=<?= $e['_id'] ?>">
                                        <button class="btn btn-outline-danger btn-celula" title="Remover matrícula"><i class="bi bi-x-lg"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="barra-status"><span>Cursos: <b><?= count($matriculas) ?></b></span><span>Carga horária total: <b><?= array_sum(array_map(fn($m) => $m['curso']['cargaHoraria'], $matriculas)) ?> h</b></span></div>
            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/../includes/rodape.php'; ?>
