<?php
require __DIR__ . '/../src/init.php';

$id = $_GET['id'] ?? null;
$estudante = $id ? Estudante::buscar($id) : null;
if ($id && !$estudante) {
    flash('danger', 'Estudante não encontrado.');
    redirecionar('index.php');
}

$erros = [];
$dados = $estudante ?? ['endereco' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = Estudante::doFormulario();
    $erros = Estudante::validar($dados, $id);
    $foto  = $_FILES['foto'] ?? null;
    if ($erroFoto = Estudante::validarFoto($foto)) $erros['foto'] = $erroFoto;

    if (!$erros) {
        $novoId = Estudante::salvar($dados, $id, $foto, isset($_POST['removerFoto']));
        flash('success', $id ? 'Estudante atualizado.' : 'Estudante cadastrado.');
        redirecionar('ver.php?id=' . $novoId);
    }
    $dados['fotoId'] = $estudante['fotoId'] ?? null; // mantém a foto atual na prévia
}

$end = $dados['endereco'] ?? [];
$v = fn($campo) => h($dados[$campo] ?? '');
$ve = fn($campo) => h($end[$campo] ?? '');

$raiz = '../';
$titulo = $id ? 'Editar estudante' : 'Novo estudante';
$menu = 'estudantes';
require __DIR__ . '/../includes/cabecalho.php';
?>
<div class="ribbon">
    <div class="container-fluid px-3">
        <div class="caminho"><a href="index.php" class="text-reset">Estudantes</a> › <?= $id ? 'Editar' : 'Novo' ?></div>
        <h1><?= $titulo ?></h1>
    </div>
</div>

<main class="container-fluid px-3 py-3">
    <?php if ($erros): ?>
        <div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle me-1"></i>Corrija os campos destacados.</div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" novalidate>
        <div class="row g-3">
            <!-- FOTO -->
            <div class="col-12 col-lg-3">
                <div class="ficha h-100">
                    <div class="ficha-titulo"><i class="bi bi-camera"></i> Foto do aluno</div>
                    <div class="ficha-corpo foto-upload">
                        <div class="previa" id="previaFoto">
                            <?php if (!empty($dados['fotoId'])): ?>
                                <img src="foto.php?id=<?= $dados['fotoId'] ?>" alt="Foto atual">
                            <?php else: ?>
                                <span class="avatar-vazio"><?= h(iniciais($dados['nome'] ?? '') ?: '?') ?></span>
                            <?php endif; ?>
                        </div>
                        <label for="foto" class="btn btn-outline-success btn-sm"><i class="bi bi-upload me-1"></i>Escolher foto</label>
                        <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" class="d-none<?= classeErro($erros, 'foto') ?>">
                        <?php if (isset($erros['foto'])): ?><div class="text-danger small mt-2"><?= h($erros['foto']) ?></div><?php endif; ?>
                        <div class="form-text">JPG, PNG ou WEBP · até 2 MB</div>
                        <?php if (!empty($dados['fotoId'])): ?>
                            <div class="form-check d-inline-block mt-2">
                                <input class="form-check-input" type="checkbox" name="removerFoto" id="removerFoto">
                                <label class="form-check-label small" for="removerFoto">Remover foto</label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-9 d-flex flex-column gap-3">
                <!-- DADOS PESSOAIS -->
                <div class="ficha">
                    <div class="ficha-titulo"><i class="bi bi-person-vcard"></i> Dados pessoais</div>
                    <div class="ficha-corpo row g-3">
                        <div class="col-12 col-md-8">
                            <label for="nome" class="form-label obrigatorio">Nome completo</label>
                            <input type="text" id="nome" name="nome" class="form-control<?= classeErro($erros, 'nome') ?>" value="<?= $v('nome') ?>" maxlength="120" required>
                            <?= msgErro($erros, 'nome') ?>
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="ra" class="form-label obrigatorio">RA</label>
                            <input type="text" id="ra" name="ra" class="form-control<?= classeErro($erros, 'ra') ?>" value="<?= $v('ra') ?>" maxlength="20" required>
                            <?= msgErro($erros, 'ra') ?>
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="cpf" class="form-label obrigatorio">CPF</label>
                            <input type="text" id="cpf" name="cpf" data-mascara="cpf" inputmode="numeric" class="form-control<?= classeErro($erros, 'cpf') ?>" value="<?= $v('cpf') ?>" placeholder="000.000.000-00" required>
                            <?= msgErro($erros, 'cpf') ?>
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="dataNascimento" class="form-label obrigatorio">Nascimento</label>
                            <input type="date" id="dataNascimento" name="dataNascimento" class="form-control<?= classeErro($erros, 'dataNascimento') ?>" value="<?= $v('dataNascimento') ?>" required>
                            <?= msgErro($erros, 'dataNascimento') ?>
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="sexo" class="form-label">Sexo</label>
                            <select id="sexo" name="sexo" class="form-select">
                                <?php foreach (['' => 'Prefiro não informar', 'Feminino' => 'Feminino', 'Masculino' => 'Masculino', 'Outro' => 'Outro'] as $valor => $rotulo): ?>
                                    <option value="<?= $valor ?>" <?= ($dados['sexo'] ?? '') === $valor ? 'selected' : '' ?>><?= $rotulo ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-8">
                            <label for="email" class="form-label obrigatorio">E-mail</label>
                            <input type="email" id="email" name="email" class="form-control<?= classeErro($erros, 'email') ?>" value="<?= $v('email') ?>" required>
                            <?= msgErro($erros, 'email') ?>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="tel" id="telefone" name="telefone" data-mascara="telefone" class="form-control" value="<?= $v('telefone') ?>" placeholder="(44) 99999-9999">
                        </div>
                    </div>
                </div>

                <!-- ENDEREÇO (documento embutido) -->
                <div class="ficha">
                    <div class="ficha-titulo"><i class="bi bi-geo-alt"></i> Endereço</div>
                    <div class="ficha-corpo row g-3">
                        <div class="col-6 col-md-3">
                            <label for="cep" class="form-label obrigatorio">CEP</label>
                            <input type="text" id="cep" name="cep" data-mascara="cep" inputmode="numeric" class="form-control<?= classeErro($erros, 'cep') ?>" value="<?= $ve('cep') ?>" placeholder="00000-000" required>
                            <?= msgErro($erros, 'cep') ?>
                            <div class="form-text" id="cepAviso">Digite o CEP para preencher o endereço.</div>
                        </div>
                        <div class="col-12 col-md-7 order-md-0">
                            <label for="logradouro" class="form-label obrigatorio">Rua / Avenida</label>
                            <input type="text" id="logradouro" name="logradouro" class="form-control<?= classeErro($erros, 'logradouro') ?>" value="<?= $ve('logradouro') ?>" required>
                            <?= msgErro($erros, 'logradouro') ?>
                        </div>
                        <div class="col-6 col-md-2">
                            <label for="numero" class="form-label obrigatorio">Número</label>
                            <input type="text" id="numero" name="numero" class="form-control<?= classeErro($erros, 'numero') ?>" value="<?= $ve('numero') ?>" required>
                            <?= msgErro($erros, 'numero') ?>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="complemento" class="form-label">Complemento</label>
                            <input type="text" id="complemento" name="complemento" class="form-control" value="<?= $ve('complemento') ?>" placeholder="Apto, bloco...">
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="bairro" class="form-label obrigatorio">Bairro</label>
                            <input type="text" id="bairro" name="bairro" class="form-control<?= classeErro($erros, 'bairro') ?>" value="<?= $ve('bairro') ?>" required>
                            <?= msgErro($erros, 'bairro') ?>
                        </div>
                        <div class="col-8 col-md-3">
                            <label for="cidade" class="form-label obrigatorio">Cidade</label>
                            <input type="text" id="cidade" name="cidade" class="form-control<?= classeErro($erros, 'cidade') ?>" value="<?= $ve('cidade') ?>" required>
                            <?= msgErro($erros, 'cidade') ?>
                        </div>
                        <div class="col-4 col-md-2">
                            <label for="estado" class="form-label obrigatorio">UF</label>
                            <select id="estado" name="estado" class="form-select<?= classeErro($erros, 'estado') ?>" required>
                                <option value="">--</option>
                                <?php foreach (ESTADOS as $uf): ?>
                                    <option <?= ($end['estado'] ?? '') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?= msgErro($erros, 'estado') ?>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <a href="<?= $id ? 'ver.php?id=' . h($id) : 'index.php' ?>" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-verde px-4"><i class="bi bi-check-lg me-1"></i>Salvar</button>
                </div>
            </div>
        </div>
    </form>
</main>
<?php require __DIR__ . '/../includes/rodape.php'; ?>
