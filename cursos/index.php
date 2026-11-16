<?php
require __DIR__ . '/../src/init.php';

$busca  = trim($_GET['busca'] ?? '');
$cursos = Curso::todos($busca);
$ativos = Matricula::ativosPorCurso();
$totalVagas = array_sum(array_column($cursos, 'vagas'));
$totalOcupadas = array_sum(array_map(fn($c) => $ativos[(string) $c['_id']] ?? 0, $cursos));

$raiz = '../';
$titulo = 'Cursos';
$menu = 'cursos';
require __DIR__ . '/../includes/cabecalho.php';
?>
<div class="ribbon">
    <div class="container-fluid px-3 d-flex flex-wrap align-items-center gap-2 gap-md-3">
        <div class="me-auto">
            <div class="caminho">Sistema Escolar › Cursos</div>
            <h1>Cursos</h1>
        </div>
        <form class="barra-formula" method="get" role="search">
            <span class="fx">fx</span>
            <input type="search" name="busca" value="<?= h($busca) ?>" placeholder="Buscar por nome, código ou professor" aria-label="Buscar curso">
            <button type="submit" aria-label="Buscar"><i class="bi bi-search"></i></button>
        </form>
        <a href="form.php" class="btn btn-verde btn-sm"><i class="bi bi-plus-square me-1"></i>Novo curso</a>
    </div>
</div>

<main class="container-fluid px-3 py-3">
    <?php mostrarFlash(); ?>
    <div class="folha">
        <div class="table-responsive">
            <table class="table planilha">
                <?= cabecalhoPlanilha(['Código', 'Curso', 'Professor', 'Carga (h)', 'Turno', 'Modalidade', 'Início', 'Vagas ocupadas', 'Ações']) ?>
                <tbody>
                <?php if (!$cursos): ?>
                    <tr class="vazia"><td colspan="10">
                        <?= $busca ? 'Nenhum curso encontrado para "' . h($busca) . '".' : 'Nenhum curso cadastrado. Clique em <b>Novo curso</b>.' ?>
                    </td></tr>
                <?php endif; ?>
                <?php foreach ($cursos as $i => $c):
                    $ocupadas = $ativos[(string) $c['_id']] ?? 0;
                    $pct = $c['vagas'] > 0 ? min(100, round($ocupadas / $c['vagas'] * 100)) : 0; ?>
                    <tr>
                        <td class="num"><?= $i + 1 ?></td>
                        <td class="mono fw-semibold"><?= h($c['codigo']) ?></td>
                        <td><a href="ver.php?id=<?= $c['_id'] ?>" class="fw-semibold text-reset"><?= h($c['nome']) ?></a></td>
                        <td><?= h($c['professor']) ?></td>
                        <td class="celula-num"><?= (int) $c['cargaHoraria'] ?></td>
                        <td><?= h($c['turno']) ?></td>
                        <td><?= h($c['modalidade']) ?></td>
                        <td class="mono"><?= formatarData($c['dataInicio'] ?? '') ?: '—' ?></td>
                        <td>
                            <div class="ocupacao" title="<?= $ocupadas ?> de <?= (int) $c['vagas'] ?> vagas">
                                <div class="barra"><span class="<?= $pct >= 100 ? 'cheio' : '' ?>" style="width: <?= $pct ?>%"></span></div>
                                <small><?= $ocupadas ?>/<?= (int) $c['vagas'] ?></small>
                            </div>
                        </td>
                        <td class="acoes">
                            <a href="ver.php?id=<?= $c['_id'] ?>" class="btn btn-outline-secondary btn-celula" title="Ver alunos"><i class="bi bi-eye"></i></a>
                            <a href="form.php?id=<?= $c['_id'] ?>" class="btn btn-outline-primary btn-celula" title="Editar"><i class="bi bi-pencil"></i></a>
                            <form action="excluir.php" method="post" class="d-inline" data-confirmar="Excluir o curso <?= h($c['nome']) ?>? As matrículas dele também serão apagadas.">
                                <input type="hidden" name="id" value="<?= $c['_id'] ?>">
                                <button class="btn btn-outline-danger btn-celula" title="Excluir"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="barra-status">
            <span><?= $busca ? 'Filtro: <b>' . h($busca) . '</b> · <a href="index.php">limpar</a>' : 'Pronto' ?></span>
            <span>Registros: <b><?= count($cursos) ?></b> · Vagas: <b><?= $totalOcupadas ?>/<?= $totalVagas ?></b> · Soma carga: <b><?= array_sum(array_column($cursos, 'cargaHoraria')) ?> h</b></span>
        </div>
        <nav class="abas-folha" aria-label="Planilhas">
            <a href="../estudantes/index.php">Estudantes</a>
            <a href="index.php" class="ativa">Cursos</a>
            <a href="../matriculas/index.php">Matrículas</a>
        </nav>
    </div>
</main>
<?php require __DIR__ . '/../includes/rodape.php'; ?>
