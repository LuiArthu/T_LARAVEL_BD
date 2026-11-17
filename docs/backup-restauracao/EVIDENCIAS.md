# Backup e restauração do banco: evidências

**Sistema:** Gerenciamento de Academia de Lutas (Laravel 11 + PostgreSQL no Neon)
**Data da execução:** 17/11/2026, 00:17
**Servidor:** PostgreSQL 18.7 (Neon), banco `neondb`
**Ferramentas:** `pg_dump` e `pg_restore` 18.7

## 1. Procedimento

| Etapa | Comando | O que faz |
|---|---|---|
| Backup | `bash database/backup/backup.sh` | `pg_dump --format=custom` de todo o banco `neondb` (schemas `public`, `backup` e `academia_lutas_BD`), mais uma "fotografia" de cada tabela (linhas + checksum MD5) |
| Restauração | `bash database/backup/restaurar.sh storage/backups/academia_2026-11-17_001710.dump` | Cria o banco **separado** `neondb_restore` no Neon e aplica `pg_restore`. O banco do sistema não é tocado. |
| Verificação | (automática, no fim da restauração) | Roda [verificar.sql](../../database/backup/verificar.sql) no banco restaurado e compara com a fotografia da origem |
| Teste funcional | consultas na seção 5 | Executa functions, procedures e trigger **no banco restaurado** |

Formato *custom* (`-Fc`): arquivo compactado, que permite restaurar objetos isolados com `pg_restore`.
`--no-owner --no-privileges`: permite restaurar em outro banco ou servidor, com outro usuário.

Restauração manual equivalente (ex.: pelo terminal ou pgAdmin → *Restore*):

```bash
pg_dump  "$DB_URL" -Fc --no-owner --no-privileges -f academia.dump
pg_restore --dbname "$DB_URL_DESTINO" --no-owner --no-privileges --exit-on-error academia.dump
```

## 2. Backup

```
== BACKUP  17/11/2026 00:17:10
   servidor: 18.7 (a21afb5)  |  banco: neondb
   ferramenta: pg_dump (PostgreSQL) 18.7 (Ubuntu 18.7-1.pgdg24.04+2)
   arquivo:  storage/backups/academia_2026-11-17_001710.dump (164K)
   sha256:   ebb3c352f850da6454c14e1b24fa75609af9061f649d608fd80a2d1a5cc33eb5
   conteúdo: 21 tabelas, 18 functions/procedures, 4 triggers, 37 índices
```

O log completo do `pg_dump` está em [academia_2026-11-17_001710.log](academia_2026-11-17_001710.log), com 80 linhas e 0 erros.

## 3. Restauração

```
== RESTAURAÇÃO  17/11/2026 00:17:31
   arquivo:  storage/backups/academia_2026-11-17_001710.dump
   sha256:   ebb3c352f850da6454c14e1b24fa75609af9061f649d608fd80a2d1a5cc33eb5
   destino:  neondb_restore (o sistema continua usando neondb)
== COMPARAÇÃO origem x restaurado
   IDÊNTICO: 21 tabelas com as mesmas linhas e checksums, mesmas functions, procedures, triggers e índices.
```

O SHA-256 é o mesmo do backup, então o arquivo restaurado é exatamente o arquivo gerado.
O log completo do `pg_restore` está em [academia_2026-11-17_001710.restore.log](academia_2026-11-17_001710.restore.log), com 0 erros.

## 4. Comparação origem × restaurado

O checksum é o MD5 de todas as linhas da tabela, ordenadas. Basta um valor diferente em qualquer linha para o checksum mudar.

| Tabela | Linhas (origem) | Checksum (origem) | Linhas (restaurado) | Checksum (restaurado) | Resultado |
|---|---:|---|---:|---|---|
| `academia_lutas_BD.clientes` | 500 | `7ff57b715f32` | 500 | `7ff57b715f32` | ✅ igual |
| `academia_lutas_BD.historico_faixa` | 96 | `a22cd611c780` | 96 | `a22cd611c780` | ✅ igual |
| `academia_lutas_BD.matriculas` | 500 | `d0bed28ea259` | 500 | `d0bed28ea259` | ✅ igual |
| `academia_lutas_BD.matriculas_original` | 500 | `d0bed28ea259` | 500 | `d0bed28ea259` | ✅ igual |
| `academia_lutas_BD.pagamento` | 500 | `b796e6476a39` | 500 | `b796e6476a39` | ✅ igual |
| `academia_lutas_BD.tamanhos` | 500 | `3c2c6e7a922e` | 500 | `3c2c6e7a922e` | ✅ igual |
| `backup.clientes_inicial` | 500 | `7ff57b715f32` | 500 | `7ff57b715f32` | ✅ igual |
| `backup.matriculas_inicial` | 500 | `d0bed28ea259` | 500 | `d0bed28ea259` | ✅ igual |
| `backup.pagamento_inicial` | 500 | `c03e7d529f8f` | 500 | `c03e7d529f8f` | ✅ igual |
| `backup.tamanhos_inicial` | 500 | `3c2c6e7a922e` | 500 | `3c2c6e7a922e` | ✅ igual |
| `public.clientes` | 500 | `7ff57b715f32` | 500 | `7ff57b715f32` | ✅ igual |
| `public.historico_faixa` | 360 | `6dd4b6910dfb` | 360 | `6dd4b6910dfb` | ✅ igual |
| `public.matriculas` | 500 | `d0bed28ea259` | 500 | `d0bed28ea259` | ✅ igual |
| `public.matriculas_original` | 500 | `d0bed28ea259` | 500 | `d0bed28ea259` | ✅ igual |
| `public.migrations` | 11 | `d399685cd352` | 11 | `d399685cd352` | ✅ igual |
| `public.modalidades` | 5 | `12e218d45307` | 5 | `12e218d45307` | ✅ igual |
| `public.pagamento` | 500 | `c03e7d529f8f` | 500 | `c03e7d529f8f` | ✅ igual |
| `public.pagamento_original` | 500 | `c03e7d529f8f` | 500 | `c03e7d529f8f` | ✅ igual |
| `public.password_reset_tokens` | 0 | `d41d8cd98f00` | 0 | `d41d8cd98f00` | ✅ igual |
| `public.tamanhos` | 500 | `3c2c6e7a922e` | 500 | `3c2c6e7a922e` | ✅ igual |
| `public.users` | 3 | `d58d1fc65507` | 3 | `d58d1fc65507` | ✅ igual |

Também são iguais as 18 functions/procedures, os 4 triggers e os 37 índices. As fotografias completas estão em [origem.txt](academia_2026-11-17_001710.origem.txt) e [restaurado.txt](academia_2026-11-17_001710.restaurado.txt). O `diff` entre os dois arquivos saiu vazio.

## 5. Teste funcional no banco restaurado

Para mostrar que os objetos do trabalho foram restaurados e **funcionam**, e não apenas que os dados foram copiados:

```
banco: neondb_restore
NOTICE:  O resultado é 164
calculo_total_mensalidade() = 164
NOTICE:  O total das 8 faixas pretas com 10 por cento de desconto é: 1080
desconto_faixas_pretas(150) = 1080
fn_inadimplentes() = 73 linhas
NOTICE:  trg_bloqueio_pagamento bloqueou: O aluno 5 está inativo e por conta disso não pode ter o pagamento registrado
```

Os resultados são os mesmos do banco original: 164 e 1080, e o trigger bloqueou a operação.

## 6. Arquivos

| Arquivo | Conteúdo |
|---|---|
| `storage/backups/academia_2026-11-17_001710.dump` | O backup (fora do git: contém CPFs) |
| [academia_2026-11-17_001710.log](academia_2026-11-17_001710.log) | Saída do `pg_dump` |
| [academia_2026-11-17_001710.restore.log](academia_2026-11-17_001710.restore.log) | Saída do `pg_restore` |
| [academia_2026-11-17_001710.origem.txt](academia_2026-11-17_001710.origem.txt) | Fotografia do banco no backup |
| [academia_2026-11-17_001710.restaurado.txt](academia_2026-11-17_001710.restaurado.txt) | Fotografia do banco restaurado |
| [academia_2026-11-17_001710.teste_funcional.txt](academia_2026-11-17_001710.teste_funcional.txt) | Saída do teste funcional |
| [backup.sh](../../database/backup/backup.sh) / [restaurar.sh](../../database/backup/restaurar.sh) / [verificar.sql](../../database/backup/verificar.sql) | Scripts usados |
