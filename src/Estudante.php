<?php

class Estudante
{
    const TAMANHO_MAX_FOTO = 2 * 1024 * 1024; // 2 MB
    const TIPOS_FOTO = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    public static function colecao(): MongoDB\Collection
    {
        return Conexao::colecao('estudantes');
    }

    public static function todos(string $busca = ''): array
    {
        $filtro = [];
        if ($busca !== '') {
            $regex = new MongoDB\BSON\Regex(preg_quote($busca, '/'), 'i');
            $filtro = ['$or' => [
                ['nome' => $regex], ['ra' => $regex], ['email' => $regex],
                ['cpf' => new MongoDB\BSON\Regex(preg_quote(somenteNumeros($busca) ?: $busca, '/'))],
                ['endereco.cidade' => $regex],
            ]];
        }
        return self::colecao()->find($filtro, ['sort' => ['nome' => 1]])->toArray();
    }

    public static function buscar($id): ?array
    {
        $oid = objectId($id);
        return $oid ? self::colecao()->findOne(['_id' => $oid]) : null;
    }

    public static function total(): int
    {
        return self::colecao()->countDocuments();
    }

    /** Monta o documento a partir do formulário ($_POST) */
    public static function doFormulario(): array
    {
        return [
            'nome'           => campo('nome'),
            'ra'             => campo('ra'),
            'cpf'            => somenteNumeros(campo('cpf')),
            'email'          => mb_strtolower(campo('email')),
            'telefone'       => formatarTelefone(campo('telefone')),
            'dataNascimento' => campo('dataNascimento'),
            'sexo'           => campo('sexo'),
            'endereco'       => [               // ← documento embutido
                'cep'         => formatarCep(campo('cep')),
                'logradouro'  => campo('logradouro'),
                'numero'      => campo('numero'),
                'complemento' => campo('complemento'),
                'bairro'      => campo('bairro'),
                'cidade'      => campo('cidade'),
                'estado'      => strtoupper(campo('estado')),
            ],
        ];
    }

    /** Valida os dados; devolve um array campo => mensagem de erro */
    public static function validar(array $d, ?string $id = null): array
    {
        $erros = [];
        if (mb_strlen($d['nome']) < 3) $erros['nome'] = 'Informe o nome completo.';
        if ($d['ra'] === '') $erros['ra'] = 'Informe o RA.';
        if (strlen($d['cpf']) !== 11) $erros['cpf'] = 'O CPF precisa ter 11 números.';
        if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $erros['email'] = 'E-mail inválido.';
        if ($d['dataNascimento'] === '') $erros['dataNascimento'] = 'Informe a data de nascimento.';

        $e = $d['endereco'];
        if (strlen(somenteNumeros($e['cep'])) !== 8) $erros['cep'] = 'CEP precisa ter 8 números.';
        if ($e['logradouro'] === '') $erros['logradouro'] = 'Informe a rua.';
        if ($e['numero'] === '') $erros['numero'] = 'Informe o número.';
        if ($e['bairro'] === '') $erros['bairro'] = 'Informe o bairro.';
        if ($e['cidade'] === '') $erros['cidade'] = 'Informe a cidade.';
        if (!in_array($e['estado'], ESTADOS, true)) $erros['estado'] = 'Escolha o estado.';

        // CPF e RA não podem repetir (ignora o próprio aluno ao editar)
        $outro = ['_id' => ['$ne' => objectId($id) ?? new MongoDB\BSON\ObjectId()]];
        if (!isset($erros['cpf']) && self::colecao()->countDocuments(['cpf' => $d['cpf']] + $outro) > 0) {
            $erros['cpf'] = 'Já existe um estudante com este CPF.';
        }
        if (!isset($erros['ra']) && self::colecao()->countDocuments(['ra' => $d['ra']] + $outro) > 0) {
            $erros['ra'] = 'Já existe um estudante com este RA.';
        }
        return $erros;
    }

    /** Valida o arquivo de foto enviado; devolve mensagem de erro ou null */
    public static function validarFoto(?array $arquivo): ?string
    {
        if (!$arquivo || $arquivo['error'] === UPLOAD_ERR_NO_FILE) return null;
        if ($arquivo['error'] !== UPLOAD_ERR_OK) return 'Não foi possível enviar a foto.';
        if ($arquivo['size'] > self::TAMANHO_MAX_FOTO) return 'A foto pode ter no máximo 2 MB.';
        $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($arquivo['tmp_name']);
        if (!isset(self::TIPOS_FOTO[$tipo])) return 'Use uma foto JPG, PNG ou WEBP.';
        return null;
    }

    /** Salva (cria ou atualiza). Devolve o _id. */
    public static function salvar(array $dados, ?string $id = null, ?array $foto = null, bool $removerFoto = false): MongoDB\BSON\ObjectId
    {
        $atual = $id ? self::buscar($id) : null;

        // troca / remove a foto antiga no GridFS
        if ($atual && !empty($atual['fotoId']) && ($removerFoto || ($foto && $foto['error'] === UPLOAD_ERR_OK))) {
            self::apagarFoto($atual['fotoId']);
            $dados['fotoId'] = null;
        }
        if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
            $dados['fotoId'] = self::guardarFoto($foto, $dados['nome']);
        }

        if ($atual) {
            $dados['atualizadoEm'] = new MongoDB\BSON\UTCDateTime();
            self::colecao()->updateOne(['_id' => $atual['_id']], ['$set' => $dados]);
            return $atual['_id'];
        }
        $dados['criadoEm'] = new MongoDB\BSON\UTCDateTime();
        return self::colecao()->insertOne($dados)->getInsertedId();
    }

    /** Exclui o aluno, a foto dele e as matrículas dele */
    public static function excluir($id): bool
    {
        $aluno = self::buscar($id);
        if (!$aluno) return false;
        if (!empty($aluno['fotoId'])) self::apagarFoto($aluno['fotoId']);
        Matricula::excluirDoEstudante($aluno['_id']);
        self::colecao()->deleteOne(['_id' => $aluno['_id']]);
        return true;
    }

    private static function guardarFoto(array $foto, string $nome): MongoDB\BSON\ObjectId
    {
        $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($foto['tmp_name']);
        $stream = fopen($foto['tmp_name'], 'rb');
        $fotoId = Conexao::fotos()->uploadFromStream(
            'aluno-' . date('YmdHis') . '.' . self::TIPOS_FOTO[$tipo],
            $stream,
            ['metadata' => ['contentType' => $tipo, 'aluno' => $nome]]
        );
        fclose($stream);
        return $fotoId;
    }

    private static function apagarFoto($fotoId): void
    {
        try {
            Conexao::fotos()->delete($fotoId instanceof MongoDB\BSON\ObjectId ? $fotoId : objectId($fotoId));
        } catch (Throwable $e) {
            // a foto já não existia: tudo bem
        }
    }
}
