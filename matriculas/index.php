<?php
require __DIR__ . '/../src/init.php';

$cursoFiltro  = $_GET['curso'] ?? '';
$statusFiltro = $_GET['status'] ?? '';
$filtro = [];
if ($oid = objectId($cursoFiltro)) $filtro['cursoId'] = $oid;
if (in_array($statusFiltro, Matricula::STATUS, true)) $filtro['status'] = $statusFiltro;

$matriculas = Matricula::todas($filtro);
$cursos = Curso::todos();

$raiz = '../';
$titulo = 'Matrículas';
$menu = 'matriculas';
require __DIR__ . '/../includes/cabecalho.php';
?>
<div class="ribbon">
    <div class="container-fluid px-3 d-flex flex-wrap align-items-center gap-2 gap-md-3">
        <div class="me-auto">
            <div class="caminho">Sistema Escolar › Matrículas</div>
            <h1>Matrículas</h1>
        </div>
        <form method="get" class="d-flex flex-wrap gap-2">
            <select name="curso" class="form-select form-select-sm" style="max-width:220px" aria-label="Filtrar por curso" onchange="this.form.submit()">
                <option value="">Todos os cursos</option>
                <?php foreach ($cursos as $c): ?>
                    <option value="<?= $c['_id'] ?>" <?= $cursoFiltro === (string) $c['_id'] ? 'selected' : '' ?>><?= h($c['nome']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="form-select form-select-sm" style="max-width:160px" aria-label="Filtrar por status" onchange="this.form.submit()">
                <option value="">Todos os status</option>
                <?php foreach (Matricula::STATUS as $s): ?>
                    <option <?= $statusFiltro === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <a href="nova.php" class="btn btn-verde btn-sm"><i class="bi bi-link me-1"></i>Nova matrícula</a>
    </div>
</div>

<main class="container-fluid px-3 py-3">
    <?php mostrarFlash(); ?>
    <div class="folha">
        <div class="table-responsive">
            <table class="table planilha">
                <?= cabecalhoPlanilha(['Foto', 'Estudante', 'RA', 'Curso', 'Código', 'Data da matrícula', 'Status', 'Ações']) ?>
                <tbody>
                <?php if (!$matriculas): ?>
                    <tr class="vazia"><td colspan="9">Nenhuma matrícula encontrada.</td></tr>
                <?php endif; ?>
                <?php foreach ($matriculas as $i => $m): $e = $m['estudante']; ?>
                    <tr>
                        <td class="num"><?= $i + 1 ?></td>
                        <td class="text-center"><?= avatar($e, $raiz) ?></td>
                        <td><a href="../estudantes/ver.php?id=<?= $e['_id'] ?>" class="text-reset fw-semibold"><?= h($e['nome']) ?></a></td>
                        <td class="mono"><?= h($e['ra']) ?></td>
                        <td><a href="../cursos/ver.php?id=<?= $m['curso']['_id'] ?>" class="text-reset"><?= h($m['curso']['nome']) ?></a></td>
                        <td class="mono"><?= h($m['curso']['codigo']) ?></td>
                        <td class="mono"><?= formatarDataMongo($m['dataMatricula']) ?></td>
                        <td>
                            <form action="status.php" method="post" class="d-inline">
                                <input type="hidden" name="id" value="<?= $m['_id'] ?>">
                                <select name="status" class="form-select form-select-sm py-0" style="width:auto" onchange="this.form.submit()" aria-label="Status da matrícula">
                                    <?php foreach (Matricula::STATUS as $s): ?>
                                        <option <?= $m['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td class="acoes">
                            <form action="excluir.php" method="post" class="d-inline" data-confirmar="Excluir a matrícula de <?= h($e['nome']) ?> em <?= h($m['curso']['nome']) ?>?">
                                <input type="hidden" name="id" value="<?= $m['_id'] ?>">
                                <button class="btn btn-outline-danger btn-celula" title="Excluir"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="barra-status">
            <span><?= $filtro ? 'Filtrado · <a href="index.php">limpar filtros</a>' : 'Pronto' ?></span>
            <span>Registros: <b><?= count($matriculas) ?></b> · Ativas: <b><?= count(array_filter($matriculas, fn($m) => $m['status'] === 'Ativa')) ?></b></span>
        </div>
        <nav class="abas-folha" aria-label="Planilhas">
            <a href="../estudantes/index.php">Estudantes</a>
            <a href="../cursos/index.php">Cursos</a>
            <a href="index.php" class="ativa">Matrículas</a>
        </nav>
    </div>
</main>
<?php require __DIR__ . '/../includes/rodape.php'; ?>
