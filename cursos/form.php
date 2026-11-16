<?php
/**cadastro-edicaocursos */
require __DIR__ . '/../src/init.php';

$id = $_GET['id'] ?? null;
$curso = $id ? Curso::buscar($id) : null;
if ($id && !$curso) {
    flash('danger', 'Curso não encontrado.');
    redirecionar('index.php');
}

$erros = [];
$dados = $curso ?? ['turno' => 'Noturno', 'modalidade' => 'Presencial'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = Curso::doFormulario();
    $erros = Curso::validar($dados, $id);
    if (!$erros) {
        $novoId = Curso::salvar($dados, $id);
        flash('success', $id ? 'Curso atualizado.' : 'Curso cadastrado.');
        redirecionar('ver.php?id=' . $novoId);
    }
}
$v = fn($campo) => h($dados[$campo] ?? '');

$raiz = '../';
$titulo = $id ? 'Editar curso' : 'Novo curso';
$menu = 'cursos';
require __DIR__ . '/../includes/cabecalho.php';
?>
<div class="ribbon">
    <div class="container-fluid px-3">
        <div class="caminho"><a href="index.php" class="text-reset">Cursos</a> › <?= $id ? 'Editar' : 'Novo' ?></div>
        <h1><?= $titulo ?></h1>
    </div>
</div>

<main class="container-fluid px-3 py-3" style="max-width: 980px">
    <?php if ($erros): ?>
        <div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle me-1"></i>Corrija os campos destacados.</div>
    <?php endif; ?>

    <form method="post" novalidate class="ficha">
        <div class="ficha-titulo"><i class="bi bi-journal-bookmark"></i> Dados do curso</div>
        <div class="ficha-corpo row g-3">
            <div class="col-12 col-md-8">
                <label for="nome" class="form-label obrigatorio">Nome do curso</label>
                <input type="text" id="nome" name="nome" class="form-control<?= classeErro($erros, 'nome') ?>" value="<?= $v('nome') ?>" maxlength="120" required>
                <?= msgErro($erros, 'nome') ?>
            </div>
            <div class="col-6 col-md-4">
                <label for="codigo" class="form-label obrigatorio">Código</label>
                <input type="text" id="codigo" name="codigo" class="form-control text-uppercase<?= classeErro($erros, 'codigo') ?>" value="<?= $v('codigo') ?>" maxlength="15" placeholder="Ex.: PHP01" required>
                <?= msgErro($erros, 'codigo') ?>
            </div>
            <div class="col-6 col-md-4">
                <label for="professor" class="form-label obrigatorio">Professor</label>
                <input type="text" id="professor" name="professor" class="form-control<?= classeErro($erros, 'professor') ?>" value="<?= $v('professor') ?>" required>
                <?= msgErro($erros, 'professor') ?>
            </div>
            <div class="col-6 col-md-2">
                <label for="cargaHoraria" class="form-label obrigatorio">Carga (h)</label>
                <input type="number" id="cargaHoraria" name="cargaHoraria" min="1" class="form-control<?= classeErro($erros, 'cargaHoraria') ?>" value="<?= $v('cargaHoraria') ?>" required>
                <?= msgErro($erros, 'cargaHoraria') ?>
            </div>
            <div class="col-6 col-md-2">
                <label for="vagas" class="form-label obrigatorio">Vagas</label>
                <input type="number" id="vagas" name="vagas" min="1" class="form-control<?= classeErro($erros, 'vagas') ?>" value="<?= $v('vagas') ?>" required>
                <?= msgErro($erros, 'vagas') ?>
            </div>
            <div class="col-6 col-md-4">
                <label for="dataInicio" class="form-label">Data de início</label>
                <input type="date" id="dataInicio" name="dataInicio" class="form-control" value="<?= $v('dataInicio') ?>">
            </div>
            <div class="col-6 col-md-4">
                <label for="turno" class="form-label obrigatorio">Turno</label>
                <select id="turno" name="turno" class="form-select<?= classeErro($erros, 'turno') ?>" required>
                    <?php foreach (Curso::TURNOS as $t): ?>
                        <option <?= ($dados['turno'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
                <?= msgErro($erros, 'turno') ?>
            </div>
            <div class="col-12 col-md-4">
                <label for="modalidade" class="form-label obrigatorio">Modalidade</label>
                <select id="modalidade" name="modalidade" class="form-select<?= classeErro($erros, 'modalidade') ?>" required>
                    <?php foreach (Curso::MODALIDADES as $m): ?>
                        <option <?= ($dados['modalidade'] ?? '') === $m ? 'selected' : '' ?>><?= $m ?></option>
                    <?php endforeach; ?>
                </select>
                <?= msgErro($erros, 'modalidade') ?>
            </div>
            <div class="col-12">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea id="descricao" name="descricao" rows="3" class="form-control" maxlength="1000"><?= $v('descricao') ?></textarea>
            </div>
            <div class="col-12 d-flex flex-wrap gap-2 justify-content-end">
                <a href="<?= $id ? 'ver.php?id=' . h($id) : 'index.php' ?>" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-verde px-4"><i class="bi bi-check-lg me-1"></i>Salvar</button>
            </div>
        </div>
    </form>
</main>
<?php require __DIR__ . '/../includes/rodape.php'; ?>
