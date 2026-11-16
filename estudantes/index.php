<?php
require __DIR__ . '/../src/init.php';

$busca      = trim($_GET['busca'] ?? '');
$estudantes = Estudante::todos($busca);
$cursosPor  = Matricula::cursosPorEstudante();
$comFoto    = count(array_filter($estudantes, fn($e) => !empty($e['fotoId'])));

$raiz = '../';
$titulo = 'Estudantes';
$menu = 'estudantes';
require __DIR__ . '/../includes/cabecalho.php';
?>
<div class="ribbon">
    <div class="container-fluid px-3 d-flex flex-wrap align-items-center gap-2 gap-md-3">
        <div class="me-auto">
            <div class="caminho">Sistema Escolar › Estudantes</div>
            <h1>Estudantes</h1>
        </div>
        <form class="barra-formula" method="get" role="search">
            <span class="fx">fx</span>
            <input type="search" name="busca" value="<?= h($busca) ?>" placeholder="Buscar por nome, RA, CPF, e-mail ou cidade" aria-label="Buscar estudante">
            <button type="submit" aria-label="Buscar"><i class="bi bi-search"></i></button>
        </form>
        <a href="form.php" class="btn btn-verde btn-sm"><i class="bi bi-person-plus me-1"></i>Novo estudante</a>
    </div>
</div>

<main class="container-fluid px-3 py-3">
    <?php mostrarFlash(); ?>

    <div class="folha">
        <div class="table-responsive">
            <table class="table planilha">
                <?= cabecalhoPlanilha(['Foto', 'Nome', 'RA', 'CPF', 'E-mail', 'Telefone', 'Nascimento', 'Cidade / UF', 'Cursos', 'Ações']) ?>
                <tbody>
                <?php if (!$estudantes): ?>
                    <tr class="vazia"><td colspan="11">
                        <?= $busca ? 'Nenhum estudante encontrado para "' . h($busca) . '".' : 'Nenhum estudante cadastrado. Clique em <b>Novo estudante</b>.' ?>
                    </td></tr>
                <?php endif; ?>
                <?php foreach ($estudantes as $i => $e): ?>
                    <tr>
                        <td class="num"><?= $i + 1 ?></td>
                        <td class="text-center">
                            <?php if (!empty($e['fotoId'])): ?>
                                <a href="#" class="ver-foto" data-bs-toggle="modal" data-bs-target="#modalFoto"
                                   data-foto="foto.php?id=<?= $e['fotoId'] ?>" data-nome="<?= h($e['nome']) ?>" title="Ver foto">
                                    <?= avatar($e, $raiz) ?>
                                </a>
                            <?php else: ?>
                                <?= avatar($e, $raiz) ?>
                            <?php endif; ?>
                        </td>
                        <td><a href="ver.php?id=<?= $e['_id'] ?>" class="fw-semibold text-reset"><?= h($e['nome']) ?></a></td>
                        <td class="mono"><?= h($e['ra']) ?></td>
                        <td class="mono"><?= h(formatarCpf($e['cpf'])) ?></td>
                        <td><?= h($e['email']) ?></td>
                        <td class="mono"><?= h($e['telefone']) ?></td>
                        <td class="mono"><?= formatarData($e['dataNascimento']) ?></td>
                        <td><?= h($e['endereco']['cidade'] ?? '') ?> / <?= h($e['endereco']['estado'] ?? '') ?></td>
                        <td class="celula-num"><?= $cursosPor[(string) $e['_id']] ?? 0 ?></td>
                        <td class="acoes">
                            <a href="ver.php?id=<?= $e['_id'] ?>" class="btn btn-outline-secondary btn-celula" title="Ver ficha"><i class="bi bi-eye"></i></a>
                            <a href="form.php?id=<?= $e['_id'] ?>" class="btn btn-outline-primary btn-celula" title="Editar"><i class="bi bi-pencil"></i></a>
                            <form action="excluir.php" method="post" class="d-inline" data-confirmar="Excluir <?= h($e['nome']) ?>? As matrículas dele também serão apagadas.">
                                <input type="hidden" name="id" value="<?= $e['_id'] ?>">
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
            <span>Registros: <b><?= count($estudantes) ?></b> · Com foto: <b><?= $comFoto ?></b></span>
        </div>
        <nav class="abas-folha" aria-label="Planilhas">
            <a href="index.php" class="ativa">Estudantes</a>
            <a href="../cursos/index.php">Cursos</a>
            <a href="../matriculas/index.php">Matrículas</a>
        </nav>
    </div>
</main>
<?php require __DIR__ . '/../includes/rodape.php'; ?>
