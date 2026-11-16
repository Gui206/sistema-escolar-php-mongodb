<?php
require __DIR__ . '/../src/init.php';

function destinoVoltar(): string
{
    $voltar = $_POST['voltar'] ?? '';
    return preg_match('#^(estudantes|cursos)/ver\.php\?id=[a-f0-9]{24}$#', $voltar) ? '../' . $voltar : 'index.php';
}

$erro = null;
$escolhido = ['estudanteId' => $_GET['estudante'] ?? '', 'cursoId' => $_GET['curso'] ?? '', 'status' => 'Ativa'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $escolhido = [
        'estudanteId' => $_POST['estudanteId'] ?? '',
        'cursoId'     => $_POST['cursoId'] ?? '',
        'status'      => $_POST['status'] ?? 'Ativa',
    ];
    $erro = Matricula::matricular($escolhido['estudanteId'], $escolhido['cursoId'], $escolhido['status']);
    if (!$erro) {
        flash('success', 'Matrícula realizada.');
        redirecionar(destinoVoltar());
    }
    if (!empty($_POST['voltar'])) {   
        flash('danger', $erro);
        redirecionar(destinoVoltar());
    }
}

$estudantes = Estudante::todos();
$cursos = Curso::todos();
$ativos = Matricula::ativosPorCurso();

$raiz = '../';
$titulo = 'Nova matrícula';
$menu = 'matriculas';
require __DIR__ . '/../includes/cabecalho.php';
?>
<div class="ribbon">
    <div class="container-fluid px-3">
        <div class="caminho"><a href="index.php" class="text-reset">Matrículas</a> › Nova</div>
        <h1>Nova matrícula</h1>
    </div>
</div>

<main class="container-fluid px-3 py-3" style="max-width: 760px">
    <?php if ($erro): ?>
        <div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle me-1"></i><?= h($erro) ?></div>
    <?php endif; ?>
    <?php if (!$estudantes || !$cursos): ?>
        <div class="alert alert-warning">
            Para matricular, cadastre antes pelo menos
            <?= !$estudantes ? '<a href="../estudantes/form.php">um estudante</a>' : '' ?>
            <?= !$estudantes && !$cursos ? ' e ' : '' ?>
            <?= !$cursos ? '<a href="../cursos/form.php">um curso</a>' : '' ?>.
        </div>
    <?php endif; ?>

    <form method="post" class="ficha">
        <div class="ficha-titulo"><i class="bi bi-link-45deg"></i> Ligar estudante ao curso</div>
        <div class="ficha-corpo row g-3">
            <div class="col-12">
                <label for="estudanteId" class="form-label obrigatorio">Estudante</label>
                <select id="estudanteId" name="estudanteId" class="form-select" required>
                    <option value="">Escolha o estudante...</option>
                    <?php foreach ($estudantes as $e): ?>
                        <option value="<?= $e['_id'] ?>" <?= $escolhido['estudanteId'] === (string) $e['_id'] ? 'selected' : '' ?>>
                            <?= h($e['nome']) ?> — RA <?= h($e['ra']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-8">
                <label for="cursoId" class="form-label obrigatorio">Curso</label>
                <select id="cursoId" name="cursoId" class="form-select" required>
                    <option value="">Escolha o curso...</option>
                    <?php foreach ($cursos as $c):
                        $ocupadas = $ativos[(string) $c['_id']] ?? 0; ?>
                        <option value="<?= $c['_id'] ?>" <?= $escolhido['cursoId'] === (string) $c['_id'] ? 'selected' : '' ?>>
                            <?= h($c['nome']) ?> (<?= h($c['codigo']) ?>) — <?= $ocupadas ?>/<?= (int) $c['vagas'] ?> vagas<?= $ocupadas >= $c['vagas'] ? ' · LOTADO' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <?php foreach (Matricula::STATUS as $s): ?>
                        <option <?= $escolhido['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 d-flex flex-wrap gap-2 justify-content-end">
                <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-verde px-4"><i class="bi bi-check-lg me-1"></i>Matricular</button>
            </div>
        </div>
    </form>
</main>
<?php require __DIR__ . '/../includes/rodape.php'; ?>
