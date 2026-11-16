<?php
require __DIR__ . '/src/init.php';

$totalEstudantes = Estudante::total();
$totalCursos     = Curso::total();
$totalMatriculas = Matricula::total();
$ultimas         = array_slice(Matricula::todas(), 0, 8);

$raiz = '';
$titulo = 'Início';
$menu = 'inicio';
require __DIR__ . '/includes/cabecalho.php';
?>
<div class="ribbon">
    <div class="container-fluid px-3">
        <div class="caminho">Sistema Escolar</div>
        <h1>Painel</h1>
    </div>
</div>

<main class="container-fluid px-3 py-3">
    <?php mostrarFlash(); ?>

    <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-lg-4">
            <a class="cartao-numero" href="estudantes/index.php">
                <span class="icone"><i class="bi bi-people"></i></span>
                <span><span class="valor d-block"><?= $totalEstudantes ?></span><span class="rotulo">Estudantes</span></span>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-lg-4">
            <a class="cartao-numero" href="cursos/index.php">
                <span class="icone"><i class="bi bi-journal-bookmark"></i></span>
                <span><span class="valor d-block"><?= $totalCursos ?></span><span class="rotulo">Cursos</span></span>
            </a>
        </div>
        <div class="col-12 col-lg-4">
            <a class="cartao-numero" href="matriculas/index.php">
                <span class="icone"><i class="bi bi-link-45deg"></i></span>
                <span><span class="valor d-block"><?= $totalMatriculas ?></span><span class="rotulo">Matrículas</span></span>
            </a>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="estudantes/form.php" class="btn btn-verde btn-sm"><i class="bi bi-person-plus me-1"></i>Novo estudante</a>
        <a href="cursos/form.php" class="btn btn-outline-success btn-sm"><i class="bi bi-plus-square me-1"></i>Novo curso</a>
        <a href="matriculas/nova.php" class="btn btn-outline-success btn-sm"><i class="bi bi-link me-1"></i>Nova matrícula</a>
    </div>

    <div class="folha">
        <div class="table-responsive">
            <table class="table planilha">
                <?= cabecalhoPlanilha(['Estudante', 'Curso', 'Data', 'Status']) ?>
                <tbody>
                <?php if (!$ultimas): ?>
                    <tr class="vazia"><td colspan="5">Nenhuma matrícula ainda. Cadastre um estudante e um curso e faça a primeira matrícula.</td></tr>
                <?php endif; ?>
                <?php foreach ($ultimas as $i => $m): ?>
                    <tr>
                        <td class="num"><?= $i + 1 ?></td>
                        <td>
                            <a href="estudantes/ver.php?id=<?= $m['estudante']['_id'] ?>" class="d-flex align-items-center gap-2 text-decoration-none text-reset">
                                <?= avatar($m['estudante'], $raiz) ?> <?= h($m['estudante']['nome']) ?>
                            </a>
                        </td>
                        <td><a href="cursos/ver.php?id=<?= $m['curso']['_id'] ?>" class="text-reset"><?= h($m['curso']['nome']) ?></a></td>
                        <td class="mono"><?= formatarDataMongo($m['dataMatricula']) ?></td>
                        <td><span class="etiqueta etiqueta-<?= h($m['status']) ?>"><?= h($m['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="barra-status"><span>Últimas matrículas</span><span>Pronto</span></div>
    </div>
</main>
<?php require __DIR__ . '/includes/rodape.php'; ?>
