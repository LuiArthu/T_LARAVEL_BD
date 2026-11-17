# Guia do projeto: onde está cada requisito

Sistema de Gerenciamento de Academia de Lutas · Laravel 11 + PostgreSQL (Neon)
Projeto Avaliativo do 2º Bimestre: Banco de Dados II + Back-end

Este guia cruza **cada requisito do enunciado** com o arquivo onde ele está implementado, aponta o que ainda falta e serve de roteiro para a apresentação e a arguição.

Legenda: ✅ atende · ⚠️ atende em parte ou tem risco na arguição · ❌ não atende

---

## 1. Checklist dos requisitos

### Banco de Dados II

| Requisito | Situação | Onde está |
|---|---|---|
| PostgreSQL hospedado no Neon | ✅ | `.env` → `DB_CONNECTION=pgsql` + `DB_URL` do Neon (PostgreSQL 18.7) |
| Mínimo **2 índices** relevantes | ✅ (3) ⚠️ justificar | [academia_completo.sql:2141](../database/sql/academia_completo.sql) `idx_matriculas_cliente_modalidade` · [trabalho_final.sql:27](../database/sql/trabalho_final.sql) `idx_cliente_tamanhos` · [trabalho_final.sql:30](../database/sql/trabalho_final.sql) `idx_infos_pagamento` |
| Mínimo **2 functions** | ✅ (3) | `fn_inadimplentes` ([academia_completo.sql:2151](../database/sql/academia_completo.sql)) · `calculo_total_mensalidade` ([trabalho_final.sql:48](../database/sql/trabalho_final.sql)) · `desconto_faixas_pretas` ([trabalho_final.sql:79](../database/sql/trabalho_final.sql)) |
| Mínimo **2 stored procedures** | ✅ (5) | `sp_registrar_matricula` ([academia_completo.sql:2185](../database/sql/academia_completo.sql)) · `atualizar_pagamento` ([trabalho_final.sql:153](../database/sql/trabalho_final.sql)) · `atualizar_faixa` (:114) · `restaurar_faixa` (:135) · `restaurar_pagamento` (:176) |
| Mínimo **2 triggers** | ✅ (2) | `trg_historico_faixa` ([trabalho_final.sql:223](../database/sql/trabalho_final.sql)) · `trg_bloqueio_pagamento` ([trabalho_final.sql:251](../database/sql/trabalho_final.sql)) |
| Objetos ligados às regras de negócio | ✅ | Inadimplência, matrícula sem duplicidade, graduação de faixa, bloqueio de pagamento de aluno inativo |
| Backup e restauração **com evidências** | ✅ | [database/backup/](../database/backup/) + [backup-restauracao/EVIDENCIAS.md](backup-restauracao/EVIDENCIAS.md) |

### Back-end

| Requisito | Situação | Onde está |
|---|---|---|
| Laravel conectado ao PostgreSQL | ✅ | `config/database.php` (conexão `pgsql`) + `.env` |
| CRUD das principais entidades em **MVC** | ✅ | Clientes, Matrículas, Tamanhos, Pagamentos (seção 3) |
| Interfaces **Blade** (cadastro, edição, listagem, visualização) | ✅ | `resources/views/{clientes,matriculas,tamanhos,pagamentos}/` (`index`, `create`, `edit`, `show`, `_form`) |
| **API REST** com CRUD de pelo menos 1 entidade | ✅ (2) | [routes/api.php](../routes/api.php): `/api/matriculas` e `/api/pagamentos` |
| JSON + métodos HTTP + códigos de status | ✅ | 200, 201 (criação), 204 (exclusão), 401, 403, 404, 422 |
| Controllers e rotas | ✅ | `app/Http/Controllers/`, `routes/web.php`, `routes/api.php` |
| **Resource Controllers** | ✅ | `Route::resource(...)` ([web.php:80-118](../routes/web.php)) e `Route::apiResource(...)` ([api.php:35-36](../routes/api.php)) |
| **API Resources** | ✅ | [MatriculaResource](../app/Http/Resources/MatriculaResource.php), [PagamentoResource](../app/Http/Resources/PagamentoResource.php) |
| **Services** | ✅ | `app/Services/` (Cliente, Matricula, Pagamento, Tamanho) |
| **Models e migrations** | ✅ | `app/Models/`, `database/migrations/` |
| Demonstrar **uso de function/procedure/trigger pelo Laravel** | ✅ | Tela **Banco de dados** (`/banco`) e histórico de faixas na tela do cliente, via [BancoService](../app/Services/BancoService.php). Ver ponto 1 |
| Testes automatizados (≥ 2 web + ≥ 2 API) | ✅ | 7 testes web e 5 de API; a suíte inteira tem **40 testes, todos passando** |

### Versionamento, documentação e entrega

| Requisito | Situação | Onde está |
|---|---|---|
| Repositório Git com histórico | ⚠️ | Esta cópia veio de um `.zip` (`-main`) **sem a pasta `.git`**. Ver ponto crítico 3 |
| Instruções de configuração e execução | ✅ | [README.md](../README.md) |
| Scripts SQL (estrutura, dados e objetos) | ✅ | [academia_completo.sql](../database/sql/academia_completo.sql) + [trabalho_final.sql](../database/sql/trabalho_final.sql) |
| Instruções dos testes e do ambiente de testes | ✅ | Seção 6 deste guia |
| Rotas web e de API documentadas, com exemplos | ✅ | Seções 4 e 5 deste guia |
| Grupo com **3 a 4 integrantes** | ❌ | O README lista só 2 nomes. Ver ponto crítico 2 |
| Relatório em PDF do grupo | ⚠️ | Só existe o relatório individual do Juan ([docs/relatorio-pessoa3/](relatorio-pessoa3/)) |

---

## 2. Pontos críticos (em ordem de risco)

### ✅ 1. Integração Laravel ↔ functions, procedure e trigger (resolvido)

O enunciado pede para *"demonstrar a integração entre a aplicação e os recursos desenvolvidos no banco"*.

**Como estava:** nenhuma tela chamava objeto do banco. O `PagamentoService::atualizarStatus()` (procedure) não tinha botão, e `fn_inadimplentes` e `sp_registrar_matricula` tinham sido reescritas em PHP.

**Como ficou:**

| Onde, no sistema | Objeto do PostgreSQL | Código |
|---|---|---|
| Menu **Banco de dados** → cartão "Mensalidades pagas" | function `calculo_total_mensalidade` | `DB::scalar('SELECT calculo_total_mensalidade(?, ?)')` |
| Menu **Banco de dados** → cartão "Faixas pretas com desconto" | function `desconto_faixas_pretas` | `DB::scalar('SELECT desconto_faixas_pretas(?)')` |
| Menu **Banco de dados** → cartão e tabela "Inadimplentes" | function `fn_inadimplentes` | `DB::select('SELECT * FROM fn_inadimplentes(?)')` |
| Menu **Banco de dados** → botão "Executar procedure" (só admin) | procedure `atualizar_pagamento` | `DB::statement('CALL atualizar_pagamento(?, ?, ?)')` |
| Menu **Banco de dados** → "Histórico de faixas" e **tela do cliente** → "Histórico de faixas" | trigger `trg_historico_faixa` (tabela `historico_faixa`) | `DB::table('historico_faixa')` |
| Salvar um pagamento inativo como "Pago" | trigger `trg_bloqueio_pagamento` | dispara sozinho no `UPDATE` |

Arquivos: [BancoService.php](../app/Services/BancoService.php) (todas as chamadas SQL), [BancoController.php](../app/Http/Controllers/BancoController.php), [banco/index.blade.php](../resources/views/banco/index.blade.php), [banco/_historico.blade.php](../resources/views/banco/_historico.blade.php), rotas em [web.php](../routes/web.php) (`banco.index`, `banco.quitar-plano`). Cada cartão mostra o comando SQL executado, o que ajuda a explicar na apresentação.

Verificado no Neon: 164 mensalidades pagas (Jiu-Jitsu/Mensal), R$ 1.080 (faixas pretas), 73 inadimplentes; a procedure muda 17 pagamentos anuais; editar uma faixa gera uma linha nova no histórico.

`sp_registrar_matricula` continua reescrita em PHP no `MatriculaService`. Se perguntarem, a justificativa é: *"a regra existe nos dois lugares; o PHP dá mensagens de validação amigáveis no formulário e na API, e a procedure garante a regra para quem grava direto no banco"*.

### ❌ 2. Grupo com 2 integrantes no README

O enunciado exige **mínimo de 3**. O README lista Ana Clara Cubas e Samara Almeida Ferreira, mas existe um relatório de **Juan Henrique Fernandes Rodrigues** (pessoa 3). Coloque o terceiro integrante no README e no relatório.

### ⚠️ 3. Git: esta pasta não tem histórico

A pasta `T_LARAVEL_BD-main` é um download `.zip` do GitHub, sem `.git`. O enunciado avalia o *"histórico de desenvolvimento compatível com a evolução do projeto"*, e o link do repositório é um entregável.

Tudo o que foi alterado aqui ainda **não está no repositório**: a rota inicial, o `trabalho_final.sql`, os scripts de backup, as evidências, os testes corrigidos e este guia. É preciso levar esses arquivos para o clone do repositório e fazer commit. Também falta colocar o link do repositório no README.

### ⚠️ 4. Índices: prepare a justificativa

O enunciado fala em índices **relevantes**, e o professor pode perguntar "qual consulta usa esse índice?".

| Índice | Consulta que ele ajuda | Risco |
|---|---|---|
| `idx_matriculas_cliente_modalidade (cliente_id, modalidade)` | "o cliente já tem matrícula nesta modalidade?" (`sp_registrar_matricula`) e join clientes → matrículas | baixo, bem justificado |
| `idx_cliente_tamanhos (cliente_id, tamanho_faixa, tamanho_kimono)` | join clientes → tamanhos (a FK `cliente_id` não tem índice automático no PostgreSQL) | médio: as colunas de tamanho quase nunca são filtradas |
| `idx_infos_pagamento (cliente_id, dia_pagamento, forma_pagamento, status_pagamento)` | join clientes → pagamento; como é *covering*, permite *index-only scan* | médio: os filtros da tela e da procedure são `status_pagamento`, `plano` e `ativo`, que não são a coluna líder |

Uma alternativa mais fácil de defender seria `pagamento (status_pagamento, plano) WHERE ativo`, um índice parcial que atende exatamente `atualizar_pagamento` e `fn_inadimplentes`. Para mostrar o uso, rode `EXPLAIN ANALYZE` na apresentação.

### ⚠️ 5. Objetos do banco ficam fora das migrations

As functions, procedures e triggers só existem se os scripts SQL forem rodados à mão. As migrations não os criam e os testes rodam em SQLite. É uma decisão válida, mas saiba explicá-la: *"os objetos estão versionados em `database/sql/` e são aplicados no Neon pelos scripts; os testes usam SQLite em memória por velocidade e isolamento, então a procedure é validada pelo script e pela demonstração"*.

---

## 3. Mapa do código (MVC)

### Caminho de uma requisição

```
Navegador / cliente HTTP
   │
   ▼
routes/web.php ou routes/api.php          ← rota (Route::resource / apiResource)
   │  middleware: auth (web) ou api.basic (API) + can:... (policies)
   ▼
app/Http/Controllers/XController.php      ← Controller (Resource Controller)
   │  valida com app/Http/Requests/XRequest.php (FormRequest)
   ▼
app/Services/XService.php                 ← regras de negócio, consultas
   │
   ▼
app/Models/X.php  ──►  PostgreSQL (Neon)  ← Model Eloquent; triggers disparam aqui
   │
   ▼
resources/views/x/*.blade.php  (web)   ou   app/Http/Resources/XResource.php  (API, JSON)
```

### Arquivos por entidade

| Camada | Clientes | Matrículas | Tamanhos | Pagamentos |
|---|---|---|---|---|
| Rota web | [web.php:80](../routes/web.php) | [web.php:100](../routes/web.php) | [web.php:109](../routes/web.php) | [web.php:118](../routes/web.php) |
| Rota API | n/a | [api.php:35](../routes/api.php) | n/a | [api.php:36](../routes/api.php) |
| Controller web | [ClienteController](../app/Http/Controllers/ClienteController.php) | [MatriculaController](../app/Http/Controllers/MatriculaController.php) | [TamanhoController](../app/Http/Controllers/TamanhoController.php) | [PagamentoController](../app/Http/Controllers/PagamentoController.php) |
| Controller API | n/a | [Api/MatriculaController](../app/Http/Controllers/Api/MatriculaController.php) | n/a | [Api/PagamentoController](../app/Http/Controllers/Api/PagamentoController.php) |
| Validação (FormRequest) | [ClienteRequest](../app/Http/Requests/ClienteRequest.php) | [MatriculaRequest](../app/Http/Requests/MatriculaRequest.php) | [TamanhoRequest](../app/Http/Requests/TamanhoRequest.php) | [PagamentoRequest](../app/Http/Requests/PagamentoRequest.php) |
| Service | [ClienteService](../app/Services/ClienteService.php) | [MatriculaService](../app/Services/MatriculaService.php) | [TamanhoService](../app/Services/TamanhoService.php) | [PagamentoService](../app/Services/PagamentoService.php) |
| Model | [Cliente](../app/Models/Cliente.php) | [Matricula](../app/Models/Matricula.php) | [Tamanho](../app/Models/Tamanho.php) | [Pagamento](../app/Models/Pagamento.php) |
| Migration | [create_clientes](../database/migrations/2026_10_31_184321_create_clientes_table.php) | [create_matriculas](../database/migrations/2026_10_31_190234_create_matriculas_table.php) | [create_tamanhos](../database/migrations/2026_10_31_192012_create_tamanhos_table.php) | [create_pagamento](../database/migrations/2026_11_02_121712_create_pagamento_table.php) |
| API Resource | n/a | [MatriculaResource](../app/Http/Resources/MatriculaResource.php) | n/a | [PagamentoResource](../app/Http/Resources/PagamentoResource.php) |
| Policy (permissão) | [ClientePolicy](../app/Policies/ClientePolicy.php) | [MatriculaPolicy](../app/Policies/MatriculaPolicy.php) | [TamanhoPolicy](../app/Policies/TamanhoPolicy.php) | [PagamentoPolicy](../app/Policies/PagamentoPolicy.php) |
| Views Blade | [clientes/](../resources/views/clientes/) | [matriculas/](../resources/views/matriculas/) | [tamanhos/](../resources/views/tamanhos/) | [pagamentos/](../resources/views/pagamentos/) |
| Factory (testes) | [ClienteFactory](../database/factories/ClienteFactory.php) | [MatriculaFactory](../database/factories/MatriculaFactory.php) | [TamanhoFactory](../database/factories/TamanhoFactory.php) | [PagamentoFactory](../database/factories/PagamentoFactory.php) |

### Peças compartilhadas

| Arquivo | Para que serve |
|---|---|
| [AutorizaRecurso.php](../app/Http/Controllers/Concerns/AutorizaRecurso.php) | Aplica as policies em cada ação do Resource Controller (`index` → `viewAny`, `store` → `create`, …) |
| [StaffPolicy.php](../app/Policies/StaffPolicy.php) | Regra única de permissão: admin e instrutor **veem, criam e editam**; só admin **exclui**; aluno não acessa |
| [ApiBasicAuth.php](../app/Http/Middleware/ApiBasicAuth.php) | Autenticação da API por HTTP Basic (e-mail e senha de um usuário), sem sessão |
| [BancoService.php](../app/Services/BancoService.php) + [BancoController.php](../app/Http/Controllers/BancoController.php) | **Ponte com o PostgreSQL**: chama as functions e a procedure e lê a tabela do trigger (tela `/banco`) |
| [BuscaTexto.php](../app/Services/Concerns/BuscaTexto.php) | Busca por nome ou CPF do cliente, usada nas listagens |
| [bootstrap/app.php](../bootstrap/app.php) | Registra `routes/api.php` (prefixo `/api`) e os aliases de middleware (`api.basic`, `admin`, `role`) |
| `resources/views/components/crud/` | Componentes Blade reaproveitados nas telas (layout, campo, badge, botão de excluir, tabela vazia) |

### Permissões por papel

| Papel | Ver | Criar / editar | Excluir |
|---|---|---|---|
| admin | ✅ | ✅ | ✅ |
| instrutor | ✅ | ✅ | ❌ (403) |
| aluno | ❌ (403) | ❌ | ❌ |

---

## 4. Rotas web

Todas exigem login (middleware `auth`), exceto `/login`.

| Método | URL | Nome | O que faz |
|---|---|---|---|
| GET | `/` | n/a | Redireciona para `/dashboard` (sem login, vai para `/login`) |
| GET | `/dashboard` | `dashboard` | Painel: total de clientes e modalidades, clientes recentes |
| GET | `/modalidades` | `modalidades.index` | Modalidades com o número de matrículas de cada uma |
| GET | `/clientes` | `clientes.index` | Lista com busca por nome ou CPF |
| GET | `/clientes/create` | `clientes.create` | Formulário de cadastro |
| POST | `/clientes` | `clientes.store` | Salva o cliente |
| GET | `/clientes/{id}` | `clientes.show` | Detalhe do cliente, com matrículas, tamanhos e pagamentos |
| GET | `/clientes/{id}/edit` | `clientes.edit` | Formulário de edição |
| PUT/PATCH | `/clientes/{id}` | `clientes.update` | Atualiza |
| DELETE | `/clientes/{id}` | `clientes.destroy` | Exclui (só admin) |
| | `/matriculas`… | `matriculas.*` | Mesmas 7 rotas; busca por cliente e filtro por modalidade |
| | `/tamanhos`… | `tamanhos.*` | Mesmas 7 rotas |
| | `/pagamentos`… | `pagamentos.*` | Mesmas 7 rotas; filtros por situação, plano e ativo, mais o cartão de inadimplentes |
| GET | `/banco` | `banco.index` | Resultado das functions (`?modalidade=&plano=&valor=`), inadimplentes e histórico do trigger (admin e instrutor) |
| POST | `/banco/quitar-plano` | `banco.quitar-plano` | Executa a procedure `atualizar_pagamento` para o plano escolhido (só admin) |
| GET | `/alunos` | n/a | Redireciona (301) para `/clientes` (endereço antigo) |

Cada linha `Route::resource('x', XController::class)` gera as 7 rotas acima. Para listar todas:

```bash
php artisan route:list --except-vendor
```

---

## 5. Rotas da API REST

Prefixo `/api`. Autenticação **HTTP Basic** com o e-mail e a senha de um usuário (os usuários de teste estão em [UserSeeder.php](../database/seeders/UserSeeder.php)). Respostas em **JSON**.

| Método | URL | Objetivo | Sucesso | Erros |
|---|---|---|---|---|
| GET | `/api/pagamentos` | Lista paginada. Filtros: `busca`, `status`, `plano`, `ativo`, `per_page` | 200 | 401, 403 |
| POST | `/api/pagamentos` | Cria | **201** | 401, 403, 422 |
| GET | `/api/pagamentos/{id}` | Detalha | 200 | 401, 403, 404 |
| PUT/PATCH | `/api/pagamentos/{id}` | Atualiza | 200 | 401, 403, 404, 422 |
| DELETE | `/api/pagamentos/{id}` | Exclui (só admin) | **204** | 401, 403, 404 |
| | `/api/matriculas`… | Mesmas 5 operações. Filtros: `busca`, `modalidade`, `faixa`, `cliente_id` | | |

Significado dos códigos: **401** sem login ou senha errada · **403** papel sem permissão · **404** registro não existe · **422** dados inválidos (o JSON diz qual campo).

### Exemplos (com `php artisan serve` rodando)

```bash
# Listar inadimplentes (ativos e devendo)
curl -u EMAIL:SENHA "http://127.0.0.1:8000/api/pagamentos?status=Devendo&ativo=1"

# Criar um pagamento → 201
curl -u EMAIL:SENHA -X POST http://127.0.0.1:8000/api/pagamentos \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"cliente_id":1,"dia_pagamento":10,"plano":"Mensal","forma_pagamento":"Credito","ativo":true,"status_pagamento":"Pago"}'

# Atualizar → 200
curl -u EMAIL:SENHA -X PUT http://127.0.0.1:8000/api/pagamentos/1 \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"cliente_id":1,"dia_pagamento":15,"plano":"Anual","forma_pagamento":"Debito","ativo":true,"status_pagamento":"Devendo"}'

# Excluir → 204 (somente admin)
curl -u EMAIL:SENHA -X DELETE http://127.0.0.1:8000/api/pagamentos/1 -H "Accept: application/json"

# Criar matrícula → 201
curl -u EMAIL:SENHA -X POST http://127.0.0.1:8000/api/matriculas \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"cliente_id":1,"modalidade":"Judo","faixa":"Amarela","data_inicio":"2026-11-17"}'
```

Formato da listagem (gerado pelo API Resource + paginação):

```json
{
  "data": [
    { "id": 1, "cliente_id": 1, "dia_pagamento": 10, "plano": "Mensal",
      "forma_pagamento": "Credito", "ativo": true, "status_pagamento": "Pago",
      "cliente": { "id": 1, "nome": "…" } }
  ],
  "links": { "first": "…", "last": "…", "prev": null, "next": "…" },
  "meta":  { "current_page": 1, "per_page": 15, "total": 500 }
}
```

O `PagamentoResource` devolve **só o id e o nome** do cliente, sem CPF nem endereço. É uma decisão de privacidade e vale citar na apresentação.

---

## 6. Testes automatizados

| Exigência | Testes | Arquivo |
|---|---|---|
| 2 testes de rotas **web** | `test_visitante_vai_para_o_login_e_a_equipe_lista_e_busca_clientes` · `test_equipe_cadastra_cliente_validando_os_dados_e_aluno_e_bloqueado` | [ClienteRoutesTest.php](../tests/Feature/Web/ClienteRoutesTest.php) |
| | `test_visitante_vai_para_o_login_equipe_lista_e_aluno_e_bloqueado` · `test_equipe_cadastra_pagamento_validando_os_dados` · `test_somente_admin_exclui_pagamento` | [PagamentoRoutesTest.php](../tests/Feature/Web/PagamentoRoutesTest.php) |
| 2 testes de rotas de **API** | `test_ciclo_completo_de_matriculas_pela_api` | [MatriculaApiTest.php](../tests/Feature/Api/MatriculaApiTest.php) |
| | `test_api_exige_autenticacao_e_so_a_equipe_acessa` · `test_api_lista_pagamentos_em_json_e_filtra_por_situacao` · `test_ciclo_completo_de_pagamentos_pela_api` · `test_api_valida_os_dados_do_pagamento` | [PagamentoApiTest.php](../tests/Feature/Api/PagamentoApiTest.php) |
| | `test_somente_a_equipe_ve_a_tela_do_banco` · `test_somente_admin_executa_a_procedure_e_o_plano_e_validado` | [BancoRoutesTest.php](../tests/Feature/Web/BancoRoutesTest.php) |
| Extra: Service | Validação de parâmetros de `atualizarStatus()` | [PagamentoServiceTest.php](../tests/Unit/PagamentoServiceTest.php) |

Os testes de API conferem os códigos **200, 201, 204, 401, 403 e 422** e a estrutura do JSON (`assertJsonStructure`).

### Ambiente de testes

O [phpunit.xml](../phpunit.xml) força um banco **SQLite em memória** (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`, `DB_URL=` vazio). Os testes usam `RefreshDatabase`: recriam as tabelas a cada execução e **nunca tocam no Neon**.

```bash
npm run build            # as views usam Vite; sem o build os testes de tela falham
php artisan test         # resultado atual: 40 testes, 208 asserções, todos passando
```

Por garantia, já que o `.env` aponta para o Neon, também dá para rodar assim:

```bash
DB_CONNECTION=sqlite DB_DATABASE=:memory: DB_URL= php artisan test
```

---

## 7. Banco de dados: objetos e onde o app os usa

| Objeto | Tipo | Regra de negócio | Usado pelo Laravel? |
|---|---|---|---|
| `fn_inadimplentes(modalidade)` | function | Lista quem está ativo e devendo | ✅ Tela Banco de dados (`BancoService::inadimplentes`) |
| `calculo_total_mensalidade(modalidade, plano)` | function | Conta mensalidades pagas por modalidade | ✅ Tela Banco de dados (`BancoService::totalMensalidadesPagas`) |
| `desconto_faixas_pretas(valor)` | function | Total com 10% de desconto para faixas pretas | ✅ Tela Banco de dados (`BancoService::totalFaixasPretasComDesconto`) |
| `sp_registrar_matricula(...)` | procedure | Matrícula sem duplicidade; faixa N/A em Boxe, Muay Thai e Personal | ⚠️ Regra reescrita em PHP (`MatriculaService`) |
| `atualizar_pagamento(...)` | procedure | Muda o status de todos os ativos de um plano | ✅ Botão "Executar procedure" (`BancoService::quitarPlano` → `PagamentoService::atualizarStatus`) |
| `atualizar_faixa` / `restaurar_faixa` / `restaurar_pagamento` | procedures | Graduação em lote e reversão | Só no SQL (demonstração do script) |
| `trg_historico_faixa` | trigger | Registra toda mudança de faixa | ✅ Dispara ao editar a matrícula; o histórico aparece na tela do cliente e no Banco de dados |
| `trg_bloqueio_pagamento` | trigger | Impede marcar como "Pago" o pagamento de aluno inativo | ✅ Dispara em gravações do app (o `PagamentoRequest` também valida) |

### Ordem para montar o banco no Neon

1. `php artisan migrate:fresh --seed`: tabelas do Laravel e usuários de teste.
2. [academia_completo.sql](../database/sql/academia_completo.sql): 4 tabelas, 500 registros em cada, objetos e schema `backup`.
3. [trabalho_final.sql](../database/sql/trabalho_final.sql): índices, functions, procedures, triggers e demonstrações. Pode rodar várias vezes.

Backup e restauração: [database/backup/](../database/backup/) e [EVIDENCIAS.md](backup-restauracao/EVIDENCIAS.md).

---

## 8. Roteiro sugerido para os 10 minutos

1. **(1 min)** Tema e escolha do banco: academia de lutas, banco do 1º bimestre com 4 tabelas e 500 registros.
2. **(3 min)** Telas: login → dashboard → CRUD de Pagamentos (cadastrar, filtrar inadimplentes, editar, excluir como admin e tentar como instrutor).
3. **(2 min)** API: `curl` de GET (200), POST (201), POST inválido (422), DELETE (204), sem senha (401).
4. **(2 min)** Banco, tudo pela tela:
   - menu **Banco de dados**: trocar a modalidade e mostrar as functions recalculando;
   - clicar em **Executar procedure** e ver a mensagem "N pagamentos passaram para Pago";
   - editar a faixa de uma matrícula e mostrar a linha nova em **Histórico de faixas** na tela do cliente (trigger).

   Depois da demonstração, rode `CALL restaurar_pagamento();` no SQL Editor do Neon para devolver os pagamentos ao estado original.
5. **(1 min)** Backup e restauração: mostrar o `EVIDENCIAS.md` com a tabela "idêntico".
6. **(1 min)** `php artisan test`: 40 testes passando.

### Perguntas prováveis

- *O que é um Resource Controller?* Uma linha `Route::resource` cria as 7 rotas do CRUD, ligadas aos métodos `index`, `create`, `store`, `show`, `edit`, `update` e `destroy`.
- *Para que serve o API Resource?* Controla o formato do JSON e esconde campos (o CPF não sai na API de pagamentos).
- *Por que Services?* A mesma regra atende o controller web e o da API, sem duplicar código.
- *Por que validar no Laravel **e** no trigger?* O FormRequest dá uma mensagem amigável na tela; o trigger garante a regra mesmo para quem grava direto no banco (pgAdmin, outro sistema).
- *Por que os testes usam SQLite?* Isolamento e velocidade: não apagam dados do Neon. A consequência é que os objetos do PostgreSQL não são exercitados nos testes.
- *Por que o índice X?* Ver a tabela do ponto crítico 4.
