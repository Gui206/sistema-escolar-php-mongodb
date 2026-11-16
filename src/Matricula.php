<?php

class Matricula
{
    const STATUS = ['Ativa', 'Trancada', 'Concluída', 'Cancelada'];

    public static function colecao(): MongoDB\Collection
    {
        return Conexao::colecao('matriculas');
    }

    public static function criarIndices(): void
    {
        self::colecao()->createIndex(['estudanteId' => 1, 'cursoId' => 1], ['unique' => true]);
    }

    public static function total(): int
    {
        return self::colecao()->countDocuments();
    }

    public static function contarAtivasNoCurso(?MongoDB\BSON\ObjectId $cursoId): int
    {
        return $cursoId ? self::colecao()->countDocuments(['cursoId' => $cursoId, 'status' => 'Ativa']) : 0;
    }

    /** Matricula. Devolve null se deu certo ou a mensagem de erro. */
    public static function matricular($estudanteId, $cursoId, string $status = 'Ativa'): ?string
    {
        $aluno = Estudante::buscar($estudanteId);
        $curso = Curso::buscar($cursoId);
        if (!$aluno) return 'Escolha um estudante.';
        if (!$curso) return 'Escolha um curso.';
        if (!in_array($status, self::STATUS, true)) $status = 'Ativa';

        if (self::colecao()->countDocuments(['estudanteId' => $aluno['_id'], 'cursoId' => $curso['_id']]) > 0) {
            return "{$aluno['nome']} já está matriculado(a) em {$curso['nome']}.";
        }
        if ($status === 'Ativa' && self::contarAtivasNoCurso($curso['_id']) >= $curso['vagas']) {
            return "O curso {$curso['nome']} não tem mais vagas.";
        }

        self::criarIndices();
        self::colecao()->insertOne([
            'estudanteId'   => $aluno['_id'],   // ← referência
            'cursoId'       => $curso['_id'],   // ← referência
            'dataMatricula' => new MongoDB\BSON\UTCDateTime(),
            'status'        => $status,
        ]);
        return null;
    }

    public static function alterarStatus($id, string $status): ?string
    {
        $mat = ($oid = objectId($id)) ? self::colecao()->findOne(['_id' => $oid]) : null;
        if (!$mat) return 'Matrícula não encontrada.';
        if (!in_array($status, self::STATUS, true)) return 'Status inválido.';
        if ($status === 'Ativa' && $mat['status'] !== 'Ativa') {
            $curso = Curso::buscar($mat['cursoId']);
            if ($curso && self::contarAtivasNoCurso($curso['_id']) >= $curso['vagas']) return 'O curso não tem mais vagas.';
        }
        self::colecao()->updateOne(['_id' => $oid], ['$set' => ['status' => $status]]);
        return null;
    }

   
    public static function todas(array $filtro = []): array
    {
        return self::colecao()->aggregate([
            ['$match' => (object) $filtro],
            ['$lookup' => ['from' => 'estudantes', 'localField' => 'estudanteId', 'foreignField' => '_id', 'as' => 'estudante']],
            ['$unwind' => '$estudante'],
            ['$lookup' => ['from' => 'cursos', 'localField' => 'cursoId', 'foreignField' => '_id', 'as' => 'curso']],
            ['$unwind' => '$curso'],
            ['$sort' => ['dataMatricula' => -1]],
        ])->toArray();
    }

    public static function doEstudante(MongoDB\BSON\ObjectId $estudanteId): array
    {
        return self::todas(['estudanteId' => $estudanteId]);
    }

    public static function doCurso(MongoDB\BSON\ObjectId $cursoId): array
    {
        $lista = self::todas(['cursoId' => $cursoId]);
        usort($lista, fn($a, $b) => strcoll($a['estudante']['nome'], $b['estudante']['nome']));
        return $lista;
    }

    public static function ativosPorCurso(): array
    {
        $r = [];
        foreach (self::colecao()->aggregate([
            ['$match' => ['status' => 'Ativa']],
            ['$group' => ['_id' => '$cursoId', 'total' => ['$sum' => 1]]],
        ]) as $g) {
            $r[(string) $g['_id']] = $g['total'];
        }
        return $r;
    }

    public static function cursosPorEstudante(): array
    {
        $r = [];
        foreach (self::colecao()->aggregate([
            ['$group' => ['_id' => '$estudanteId', 'total' => ['$sum' => 1]]],
        ]) as $g) {
            $r[(string) $g['_id']] = $g['total'];
        }
        return $r;
    }

    public static function excluir($id): bool
    {
        $oid = objectId($id);
        return $oid && self::colecao()->deleteOne(['_id' => $oid])->getDeletedCount() > 0;
    }

    public static function excluirDoEstudante(MongoDB\BSON\ObjectId $id): void
    {
        self::colecao()->deleteMany(['estudanteId' => $id]);
    }

    public static function excluirDoCurso(MongoDB\BSON\ObjectId $id): void
    {
        self::colecao()->deleteMany(['cursoId' => $id]);
    }
}
