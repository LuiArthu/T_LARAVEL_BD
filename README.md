# Sistema de Gerenciamento de Academia de Lutas

## Integrantes
- Ana Clara Cubas
- Samara Almeida Ferreira

## Descrição
Sistema web para gerenciar alunos e modalidades de uma academia de lutas,
com controle de acesso por papéis (admin, instrutor, aluno).

## Tecnologias
- Laravel 11
- PHP 8.2+
- PostgreSQL (Neon)
- Blade
- Laravel Breeze

## Instalação
\`\`\`bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# configurar .env com DB_CONNECTION=pgsql e DB_URL do seu banco Neon
php artisan migrate:fresh --seed
npm run build
\`\`\`

## Execução
\`\`\`bash
php artisan serve
\`\`\`
Acesse http://127.0.0.1:8000

## Módulos de gestão (clientes, matrículas, tamanhos e pagamentos)

CRUD web em `/clientes`, `/matriculas`, `/tamanhos` e `/pagamentos`, e API JSON
de matrículas e pagamentos em `/api/matriculas` e `/api/pagamentos` (HTTP Basic
com e-mail e senha de um usuário). Rotas de pagamentos: `docs/rotas-pagamentos.md`.
Ver, criar e editar: admin e instrutor. Excluir: somente admin.

- Regras de negócio em `app/Services`, validações em `app/Http/Requests`.
- Matrícula: Boxe, Muay Thai e Personal não têm faixa (gravada como `N/A`) e um
  cliente não pode ter duas matrículas na mesma modalidade.
- Pagamento: dia de 1 a 28 e, como na trigger do banco, um pagamento inativo
  não pode ficar como `Pago` (o app valida isso e devolve a mensagem de erro).
- `database/sql/academia_completo.sql`: estrutura, dados, índice, function
  `fn_inadimplentes`, procedures `sp_registrar_matricula` e `atualizar_pagamento`,
  trigger `trg_bloqueio_pagamento` e backups (PostgreSQL). A procedure e a trigger
  só existem se o banco for criado por este script (as migrations não as criam);
  `PagamentoService::atualizarStatus()` depende da procedure.
  **Use o script OU as migrations para criar estas 4 tabelas, não os dois**:
  o script recria as tabelas e, se rodar `php artisan migrate` depois, ele
  tentará criá-las de novo.
- Testes: `php artisan test` (precisa de `npm run build` antes por causa do Vite).
