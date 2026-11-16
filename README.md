# Sistema Escolar — PHP + MongoDB

Atividade 3 — Desenvolvimento Back-End (UNIPAR · Sistemas de Informação)
Professor: Carlos Eduardo Simões Pelegrin

**Equipe:** Guilherme Belo Della Bella

Sistema web para cadastrar **estudantes** (com endereço e foto), **cursos** e as **matrículas** que ligam um ao outro.
Feito em **PHP 8**, com banco **MongoDB** (Atlas ou local) e interface em **Bootstrap 5**, com visual de planilha.

## Funcionalidades

- **Estudantes:** cadastrar, listar, buscar, editar e excluir. Cada aluno tem 7 campos pessoais (nome, RA, CPF, e-mail, telefone, nascimento e sexo), um endereço com 7 campos e uma **foto**.
- **Cursos:** cadastrar, listar, buscar, editar e excluir. São 9 campos: nome, código, carga horária, professor, vagas, turno, modalidade, data de início e descrição.
- **Matrículas:** um estudante pode estar em vários cursos e um curso pode ter vários estudantes (relação N:N).
  - Não deixa matricular o mesmo aluno duas vezes no mesmo curso (índice único).
  - Não deixa passar do número de vagas do curso.
  - Status da matrícula: Ativa, Trancada, Concluída ou Cancelada.
- **Endereço automático pelo CEP**, via AJAX na API do ViaCEP.
- **Foto do aluno** guardada no próprio MongoDB (GridFS), com visualização ampliada.
- Validação de todos os campos, CPF e RA sem repetição, e layout responsivo (funciona no celular).

## Modelagem no MongoDB (relacionamentos)

O trabalho pedia **"não pode ser tudo em documento embutido"**. Por isso foram usados os dois tipos de relacionamento:

| Coleção | O que guarda | Tipo de relação |
|---|---|---|
| `estudantes` | dados do aluno + `endereco` | o endereço é **embutido** (1:1, pertence só ao aluno) |
| `cursos` | dados do curso | coleção independente |
| `matriculas` | `estudanteId` + `cursoId` + data + status | **referência** (N:N entre estudantes e cursos) |
| `fotos.files` / `fotos.chunks` | arquivos das fotos (GridFS) | o estudante guarda só o `fotoId` (**referência**) |

```js
// estudantes
{ _id: ObjectId("..."), nome: "Ana Souza", ra: "2024002", cpf: "98765432100",
  email: "ana@email.com", telefone: "(44) 99999-0000", dataNascimento: "2004-01-02", sexo: "Feminino",
  fotoId: ObjectId("..."),                       // → fotos (GridFS)
  endereco: { cep: "87501-000", logradouro: "Av. Paraná", numero: "1200",
              complemento: "", bairro: "Centro", cidade: "Umuarama", estado: "PR" } }

// cursos
{ _id: ObjectId("..."), nome: "PHP Back-End", codigo: "PHP01", cargaHoraria: 80,
  professor: "Carlos", vagas: 30, turno: "Noturno", modalidade: "Presencial",
  dataInicio: "2026-08-03", descricao: "..." }

// matriculas  ← relacionamento N:N
{ _id: ObjectId("..."), estudanteId: ObjectId("..."), cursoId: ObjectId("..."),
  dataMatricula: ISODate("..."), status: "Ativa" }
```

Para mostrar o nome do aluno e do curso na lista de matrículas, o sistema usa o `$lookup` do MongoDB (o equivalente ao JOIN do SQL), em `src/Matricula.php`.

**Não é preciso criar tabelas nem rodar SQL.** O MongoDB cria o banco e as coleções sozinho na primeira gravação.

## Estrutura

```
├── config.exemplo.php   modelo de configuração (copiar para config.php)
├── index.php            painel
├── src/
│   ├── init.php         carregado em todas as páginas
│   ├── Conexao.php      conexão com o MongoDB (padrão Singleton)
│   ├── Estudante.php    regras do estudante + foto (GridFS)
│   ├── Curso.php        regras do curso
│   ├── Matricula.php    relacionamento N:N ($lookup, vagas, duplicidade)
│   └── funcoes.php      funções auxiliares
├── estudantes/          index (lista) · form (cadastro/edição) · ver (ficha) · foto · excluir
├── cursos/              index · form · ver · excluir
├── matriculas/          index · nova · status · excluir
├── includes/            cabeçalho e rodapé das páginas
└── assets/              CSS (visual de planilha) e JavaScript (máscaras, CEP, foto)
```

## Como rodar

**Requisitos:** XAMPP (PHP 8.1 ou superior), [Composer](https://getcomposer.org), extensão `mongodb` do PHP e um banco MongoDB (Atlas ou local).

1. Coloque a pasta do projeto em `C:\xampp\htdocs\trabalho-mongo`.
2. Instale a extensão do MongoDB no PHP:
   - baixe o `php_mongodb.dll` compatível em <https://github.com/mongodb/mongo-php-driver/releases> (mesma versão do PHP, `ts`, `x64`);
   - copie para `C:\xampp\php\ext\`;
   - no `php.ini`, adicione `extension=mongodb` e deixe `extension=zip` sem o `;`;
   - reinicie o Apache.
3. Na pasta do projeto, rode:
   ```
   composer install
   ```
4. Copie `config.exemplo.php` para `config.php` e coloque o endereço do seu banco:
   - **Atlas:** `mongodb+srv://usuario:senha@cluster0.xxxxx.mongodb.net/`
   - **Local:** `mongodb://localhost:27017`
5. Acesse <http://localhost/trabalho-mongo>.

> **Observação:** algumas redes (de faculdade ou de empresa) bloqueiam a porta 27017, que é a usada pelo Atlas. Se aparecer erro de conexão, teste em outra rede (por exemplo, os dados móveis do celular) ou use o MongoDB local.

## Tecnologias

PHP 8 · MongoDB (driver oficial `mongodb/mongodb`) · GridFS · Bootstrap 5.3 · Bootstrap Icons · ViaCEP (AJAX/fetch)
