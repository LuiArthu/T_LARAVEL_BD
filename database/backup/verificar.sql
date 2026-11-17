-- =====================================================================
-- Fotografia do banco para comparar ORIGEM x RESTAURADO.
--
-- Para cada tabela: quantidade de linhas e um checksum MD5 de todo o
-- conteúdo (linhas ordenadas). Se dois bancos derem a mesma saída, os
-- dados são idênticos. Depois lista functions, procedures, triggers e
-- índices.
--
-- Uso: psql "$URL" -X -q -f database/backup/verificar.sql
-- (sessions/cache/jobs ficam de fora: mudam sozinhas enquanto o app roda)
-- =====================================================================

\pset format unaligned
\pset tuples_only on
\pset fieldsep ' | '

SELECT format(
    'SELECT %L, count(*), md5(coalesce(string_agg(t::text, %L ORDER BY t::text), %L)) FROM %I.%I t',
    'tabela ' || n.nspname || '.' || c.relname, E'\n', '', n.nspname, c.relname
)
  FROM pg_class c
  JOIN pg_namespace n ON n.oid = c.relnamespace
 WHERE c.relkind = 'r'
   AND n.nspname NOT IN ('pg_catalog', 'information_schema')
   AND n.nspname NOT LIKE 'pg\_%'
   AND c.relname NOT IN ('sessions', 'cache', 'cache_locks', 'jobs', 'failed_jobs', 'job_batches')
 ORDER BY n.nspname, c.relname
\gexec

SELECT 'rotina', n.nspname,
       CASE p.prokind WHEN 'p' THEN 'procedure' ELSE 'function' END,
       p.proname || '(' || pg_get_function_identity_arguments(p.oid) || ')'
  FROM pg_proc p
  JOIN pg_namespace n ON n.oid = p.pronamespace
 WHERE n.nspname NOT IN ('pg_catalog', 'information_schema')
   AND n.nspname NOT LIKE 'pg\_%'
 ORDER BY 2, 4;

SELECT 'trigger', n.nspname, c.relname, t.tgname
  FROM pg_trigger t
  JOIN pg_class c ON c.oid = t.tgrelid
  JOIN pg_namespace n ON n.oid = c.relnamespace
 WHERE NOT t.tgisinternal
 ORDER BY 2, 3, 4;

SELECT 'indice', schemaname, tablename, indexname
  FROM pg_indexes
 WHERE schemaname NOT IN ('pg_catalog', 'information_schema')
   AND schemaname NOT LIKE 'pg\_%'
 ORDER BY 2, 3, 4;
