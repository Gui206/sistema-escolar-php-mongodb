<?php
class Curso
{
    const TURNOS = ['Matutino', 'Vespertino', 'Noturno', 'Integral'];
    const MODALIDADES = ['Presencial', 'EAD', 'Híbrido'];

    public static function colecao(): MongoDB\Collection
    {
        return Conexao::colecao('cursos');
    }

    public static function todos(string $busca = ''): array
    {
        $filtro = [];
        if ($busca !== '') {
            $regex = new MongoDB\BSON\Regex(preg_quote($busca, '/'), 'i');
            $filtro = ['$or' => [['nome' => $regex], ['codigo' => $regex], ['professor' => $regex]]];
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

    public static function doFormulario(): array
    {
        return [
            'nome'         => campo('nome'),
            'codigo'       => strtoupper(campo('codigo')),
            'cargaHoraria' => (int) campo('cargaHoraria'),
            'professor'    => campo('professor'),
            'vagas'        => (int) campo('vagas'),
            'turno'        => campo('turno'),
            'modalidade'   => campo('modalidade'),
            'dataInicio'   => campo('dataInicio'),
            'descricao'    => campo('descricao'),
        ];
    }

    public static function validar(array $d, ?string $id = null): array
    {
        $erros = [];
        if (mb_strlen($d['nome']) < 3) $erros['nome'] = 'Informe o nome do curso.';
        if ($d['codigo'] === '') $erros['codigo'] = 'Informe o código.';
        if ($d['cargaHoraria'] <= 0) $erros['cargaHoraria'] = 'Carga horária deve ser maior que zero.';
        if ($d['professor'] === '') $erros['professor'] = 'Informe o professor.';
        if ($d['vagas'] <= 0) $erros['vagas'] = 'Informe a quantidade de vagas.';
        if (!in_array($d['turno'], self::TURNOS, true)) $erros['turno'] = 'Escolha o turno.';
        if (!in_array($d['modalidade'], self::MODALIDADES, true)) $erros['modalidade'] = 'Escolha a modalidade.';

        if (!isset($erros['codigo'])) {
            $outro = ['_id' => ['$ne' => objectId($id) ?? new MongoDB\BSON\ObjectId()]];
            if (self::colecao()->countDocuments(['codigo' => $d['codigo']] + $outro) > 0) {
                $erros['codigo'] = 'Já existe um curso com este código.';
            }
        }
        // não deixa diminuir as vagas para menos que os matriculados
        if ($id && !isset($erros['vagas'])) {
            $ocupadas = Matricula::contarAtivasNoCurso(objectId($id));
            if ($d['vagas'] < $ocupadas) $erros['vagas'] = "O curso já tem $ocupadas alunos ativos.";
        }
        return $erros;
    }

    public static function salvar(array $dados, ?string $id = null): MongoDB\BSON\ObjectId
    {
        if ($id && ($oid = objectId($id))) {
            self::colecao()->updateOne(['_id' => $oid], ['$set' => $dados + ['atualizadoEm' => new MongoDB\BSON\UTCDateTime()]]);
            return $oid;
        }
        $dados['criadoEm'] = new MongoDB\BSON\UTCDateTime();
        return self::colecao()->insertOne($dados)->getInsertedId();
    }

    public static function excluir($id): bool
    {
        $oid = objectId($id);
        if (!$oid) return false;
        Matricula::excluirDoCurso($oid);
        return self::colecao()->deleteOne(['_id' => $oid])->getDeletedCount() > 0;
    }
}
