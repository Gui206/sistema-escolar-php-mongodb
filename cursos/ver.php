<?php
/**detalhe cursos  */
require __DIR__ . '/../src/init.php';

$c = Curso::buscar($_GET['id'] ?? '');
if (!$c) {
    flash('danger', 'Curso não encontrado.');
    redirecionar('index.php');
}
$matriculas = Matricula::doCurso($c['_id']);
$ativas = count(array_filter($matriculas, fn($m) => $m['status'] === 'Ativa'));
$jaInscritos = array_map(fn($m) => (string) $m['estudante']['_id'], $matriculas);
$disponiveis = array_filter(Estudante::todos(), fn($e) => !in_array((string) $e['_id'], $jaInscritos, true));
$pct = $c['vagas'] > 0 ? min(100, round($ativas / $c['vagas'] * 100)) : 0;

$raiz = '../';
$titulo = $c['nome'];
$menu = 'cursos';
require __DIR__ . '/../includes/cabecalho.php';
?>
<div class="ribbon">
    <div class="container-fluid px-3 d-flex flex-wrap align-items-center gap-2">
        <div class="me-auto">
            <div class="caminho"><a href="index.php" class="text-reset">Cursos</a> › <?= h($c['codigo']) ?></div>
            <h1><?= h($c['nome']) ?></h1>
        </div>
        <a href="form.php?id=<?= $c['_id'] ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>Editar</a>
        <form action="excluir.php" method="post" data-confirmar="Excluir o curso <?= h($c['nome']) ?>? As matrículas dele também serão apagadas.">
            <input type="hidden" name="id" value="<?= $c['_id'] ?>">
            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Excluir</button>
        </form>
    </div>
</div>

<main class="container-fluid px-3 py-3">
    <?php mostrarFlash(); ?>
    <div class="row g-3">
        <div class="col-12 col-lg-4">
            <div class="ficha">
                <div class="ficha-titulo"><i class="bi bi-journal-bookmark"></i> Dados do curso</div>
                <dl class="dados">
                    <dt>Código</dt><dd class="font-monospace"><?= h($c['codigo']) ?></dd>
                    <dt>Professor</dt><dd><?= h($c['professor']) ?></dd>
                    <dt>Carga horária</dt><dd><?= (int) $c['cargaHoraria'] ?> h</dd>
                    <dt>Turno</dt><dd><?= h($c['turno']) ?></dd>
                    <dt>Modalidade</dt><dd><?= h($c['modalidade']) ?></dd>
                    <dt>Início</dt><dd><?= formatarData($c['dataInicio'] ?? '') ?: '—' ?></dd>
                    <dt>Vagas</dt>
                    <dd>
                        <div class="ocupacao">
                            <div class="barra"><span class="<?= $pct >= 100 ? 'cheio' : '' ?>" style="width: <?= $pct ?>%"></span></div>
                            <small><?= $ativas ?>/<?= (int) $c['vagas'] ?></small>
                        </div>
                    </dd>
                </dl>
                <?php if (!empty($c['descricao'])): ?>
                    <div class="ficha-corpo small text-muted"><?= nl2br(h($c['descricao'])) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ALUNOS DO CURSO (relacionamento N:N) -->
        <div class="col-12 col-lg-8">
            <div class="folha">
                <div class="ficha-titulo">
                    <i class="bi bi-people"></i><b class="me-auto">Alunos matriculados</b>
                    <?php if ($disponiveis && $ativas < $c['vagas']): ?>
                        <form action="../matriculas/nova.php" method="post" class="d-flex gap-2 flex-wrap">
                            <input type="hidden" name="cursoId" value="<?= $c['_id'] ?>">
                            <input type="hidden" name="voltar" value="cursos/ver.php?id=<?= $c['_id'] ?>">
                            <select name="estudanteId" class="form-select form-select-sm" style="max-width:240px" required aria-label="Estudante">
                                <option value="">Adicionar aluno...</option>
                                <?php foreach ($disponiveis as $e): ?>
                                    <option value="<?= $e['_id'] ?>"><?= h($e['nome']) ?> (RA <?= h($e['ra']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-verde btn-sm"><i class="bi bi-plus-lg"></i> Matricular</button>
                        </form>
                    <?php elseif ($ativas >= $c['vagas']): ?>
                        <span class="etiqueta etiqueta-Cancelada">Turma lotada</span>
                    <?php endif; ?>
                </div>
                <div class="table-responsive">
                    <table class="table planilha">
                        <?= cabecalhoPlanilha(['Foto', 'Aluno', 'RA', 'E-mail', 'Matrícula', 'Status', '']) ?>
                        <tbody>
                        <?php if (!$matriculas): ?>
                            <tr class="vazia"><td colspan="8">Nenhum aluno matriculado neste curso.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($matriculas as $i => $m): $e = $m['estudante']; ?>
                            <tr>
                                <td class="num"><?= $i + 1 ?></td>
                                <td class="text-center">
                                    <?php if (!empty($e['fotoId'])): ?>
                                        <a href="#" class="ver-foto" data-bs-toggle="modal" data-bs-target="#modalFoto"
                                           data-foto="../estudantes/foto.php?id=<?= $e['fotoId'] ?>" data-nome="<?= h($e['nome']) ?>"><?= avatar($e, $raiz) ?></a>
                                    <?php else: ?><?= avatar($e, $raiz) ?><?php endif; ?>
                                </td>
                                <td><a href="../estudantes/ver.php?id=<?= $e['_id'] ?>" class="text-reset fw-semibold"><?= h($e['nome']) ?></a></td>
                                <td class="mono"><?= h($e['ra']) ?></td>
                                <td><?= h($e['email']) ?></td>
                                <td class="mono"><?= formatarDataMongo($m['dataMatricula']) ?></td>
                                <td><span class="etiqueta etiqueta-<?= h($m['status']) ?>"><?= h($m['status']) ?></span></td>
                                <td class="acoes">
                                    <form action="../matriculas/excluir.php" method="post" class="d-inline" data-confirmar="Remover <?= h($e['nome']) ?> deste curso?">
                                        <input type="hidden" name="id" value="<?= $m['_id'] ?>">
                                        <input type="hidden" name="voltar" value="cursos/ver.php?id=<?= $c['_id'] ?>">
                                        <button class="btn btn-outline-danger btn-celula" title="Remover do curso"><i class="bi bi-x-lg"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="barra-status"><span>Matriculados: <b><?= count($matriculas) ?></b> · Ativos: <b><?= $ativas ?></b></span><span>Vagas livres: <b><?= max(0, $c['vagas'] - $ativas) ?></b></span></div>
            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/../includes/rodape.php'; ?>
