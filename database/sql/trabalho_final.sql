-- =====================================================================
-- Academia de Lutas  |  Trabalho final de Banco de Dados II
--
--   Índices, functions, procedures e triggers sobre as tabelas
--   clientes, matriculas, tamanhos e pagamento.
--
-- ORDEM (no mesmo banco que o sistema Laravel usa, ex.: Neon):
--   1. php artisan migrate --seed   (cria users, sessions etc.)
--   2. database/sql/academia_completo.sql   (as 4 tabelas + 500 registros)
--   3. este arquivo
--
-- Pode ser rodado mais de uma vez: tudo usa IF NOT EXISTS / OR REPLACE e
-- as demonstrações desfazem o que alteram. Roda em uma única transação:
-- se algo falhar, nada muda.
-- =====================================================================

BEGIN;

-- As tabelas do sistema ficam no schema public (o mesmo que o Laravel usa).
SET search_path TO public;


-- =====================================================================
-- 1. ÍNDICES
-- =====================================================================

CREATE INDEX IF NOT EXISTS idx_cliente_tamanhos
    ON tamanhos (cliente_id, tamanho_faixa, tamanho_kimono);

CREATE INDEX IF NOT EXISTS idx_infos_pagamento
    ON pagamento (cliente_id, dia_pagamento, forma_pagamento, status_pagamento);


-- =====================================================================
-- 2. FUNCTIONS
-- =====================================================================

-- ---------------------------------------------------------------------
-- 2.1 Quantidade total de mensalidades recebidas (status 'Pago') de uma
--     modalidade, considerando um plano. Padrão: Jiu-Jitsu, plano Mensal.
--       SELECT calculo_total_mensalidade();
--       SELECT calculo_total_mensalidade('Judo', 'Anual');
-- ---------------------------------------------------------------------
-- Remove uma versão antiga que devolvia VARCHAR (o tipo de retorno não
-- pode ser trocado com CREATE OR REPLACE).
DROP FUNCTION IF EXISTS calculo_total_mensalidade(VARCHAR, VARCHAR);

CREATE OR REPLACE FUNCTION calculo_total_mensalidade(
    m_modalidade VARCHAR = 'Jiu-Jitsu',
    p_plano      VARCHAR = 'Mensal'
)
RETURNS INT
LANGUAGE plpgsql
AS $$
DECLARE
    t_total_mes INT;
BEGIN
    SELECT COUNT(*)
      INTO t_total_mes
      FROM pagamento p
      JOIN matriculas m ON m.cliente_id = p.cliente_id
     WHERE m.modalidade = m_modalidade
       AND p.plano = p_plano
       AND p.status_pagamento = 'Pago';

    RAISE NOTICE 'O resultado é %', t_total_mes;

    RETURN t_total_mes;
END;
$$;

-- ---------------------------------------------------------------------
-- 2.2 Valor total a receber dos alunos faixa preta, com 10% de desconto.
--     p_plano = valor da mensalidade.
--       SELECT desconto_faixas_pretas(150);
-- ---------------------------------------------------------------------
DROP FUNCTION IF EXISTS desconto_faixas_pretas(INT, VARCHAR, INT);

CREATE OR REPLACE FUNCTION desconto_faixas_pretas(
    p_plano    INT,
    m_faixa    VARCHAR = 'Preta',
    p_desconto INT = 10
)
RETURNS INT
LANGUAGE plpgsql
AS $$
DECLARE
    d_desconto_faixa INT;
    d_total          INT;
BEGIN
    SELECT COUNT(DISTINCT cliente_id)
      INTO d_desconto_faixa
      FROM matriculas
     WHERE faixa ILIKE m_faixa;

    d_total := d_desconto_faixa * (p_plano - (p_plano * p_desconto / 100));

    RAISE NOTICE 'O total das % faixas pretas com % por cento de desconto é: %',
        d_desconto_faixa, p_desconto, d_total;

    RETURN d_total;
END;
$$;


-- =====================================================================
-- 3. STORED PROCEDURES
-- =====================================================================

-- ---------------------------------------------------------------------
-- 3.1 Atualiza a faixa de todos os faixa branca de Jiu-Jitsu para azul.
--       CALL atualizar_faixa();
-- ---------------------------------------------------------------------
CREATE OR REPLACE PROCEDURE atualizar_faixa(
    m_modalidade VARCHAR = 'Jiu-Jitsu',
    m_faixa      VARCHAR = 'Branca',
    m_faixa_nova VARCHAR = 'Azul'
)
LANGUAGE plpgsql
AS $$
BEGIN
    UPDATE matriculas
       SET faixa = m_faixa_nova
     WHERE faixa = m_faixa
       AND modalidade = m_modalidade;
END;
$$;

-- ---------------------------------------------------------------------
-- 3.2 Volta as faixas ao estado guardado em matriculas_original
--     (cópia tirada logo antes da demonstração, na seção 5).
--     Só mexe nas linhas que realmente mudaram.
--       CALL restaurar_faixa();
-- ---------------------------------------------------------------------
CREATE OR REPLACE PROCEDURE restaurar_faixa()
LANGUAGE plpgsql
AS $$
BEGIN
    UPDATE matriculas m
       SET faixa = o.faixa
      FROM matriculas_original o
     WHERE o.id = m.id
       AND o.faixa IS DISTINCT FROM m.faixa;
END;
$$;

-- ---------------------------------------------------------------------
-- 3.3 Troca o status dos pagamentos ATIVOS de um plano (padrão: os anuais
--     'Devendo' passam a 'Pago'). É a mesma procedure que o sistema
--     Laravel chama em PagamentoService::atualizarStatus().
--       CALL atualizar_pagamento();
-- ---------------------------------------------------------------------
CREATE OR REPLACE PROCEDURE atualizar_pagamento(
    p_status_pagamento VARCHAR = 'Devendo',
    p_plano            VARCHAR = 'Anual',
    p_pagamento_novo   VARCHAR = 'Pago'
)
LANGUAGE plpgsql
AS $$
BEGIN
    UPDATE pagamento
       SET status_pagamento = p_pagamento_novo
     WHERE status_pagamento = p_status_pagamento
       AND plano = p_plano
       AND ativo = TRUE;
END;
$$;

-- ---------------------------------------------------------------------
-- 3.4 Volta o status dos pagamentos ao estado guardado em
--     pagamento_original. (Chamar atualizar_pagamento('Pago','Anual',
--     'Devendo') NÃO desfaz: marcaria como 'Devendo' também quem já
--     estava 'Pago' antes.)
--       CALL restaurar_pagamento();
-- ---------------------------------------------------------------------
CREATE OR REPLACE PROCEDURE restaurar_pagamento()
LANGUAGE plpgsql
AS $$
BEGIN
    UPDATE pagamento p
       SET status_pagamento = o.status_pagamento
      FROM pagamento_original o
     WHERE o.id = p.id
       AND o.status_pagamento IS DISTINCT FROM p.status_pagamento;
END;
$$;


-- =====================================================================
-- 4. TRIGGERS
-- =====================================================================

-- ---------------------------------------------------------------------
-- 4.1 Histórico de faixas: sempre que a faixa de uma matrícula muda,
--     grava a mudança em historico_faixa. Vale também para alterações
--     feitas pelo sistema Laravel (tela de matrículas).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS historico_faixa (
    id_historico   SERIAL PRIMARY KEY,
    cliente_id     INT,
    modalidade     VARCHAR,
    faixa_anterior VARCHAR,
    faixa_nova     VARCHAR,
    data_alteracao TIMESTAMP DEFAULT now()
);

CREATE OR REPLACE FUNCTION registrar_historico_faixa()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    IF OLD.faixa IS DISTINCT FROM NEW.faixa THEN
        INSERT INTO historico_faixa (cliente_id, modalidade, faixa_anterior, faixa_nova, data_alteracao)
        VALUES (OLD.cliente_id, OLD.modalidade, OLD.faixa, NEW.faixa, now());
    END IF;

    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS trg_historico_faixa ON matriculas;

CREATE TRIGGER trg_historico_faixa
AFTER UPDATE ON matriculas
FOR EACH ROW
EXECUTE FUNCTION registrar_historico_faixa();

-- ---------------------------------------------------------------------
-- 4.2 Bloqueia pagamento de aluno inativo (BEFORE UPDATE em pagamento).
--     Se ativo = FALSE, não deixa marcar como 'Pago' e devolve um erro
--     explicando. Evita registrar dinheiro de quem já saiu.
--     (O academia_completo.sql já cria este trigger; aqui ele é recriado
--     com a mensagem do trabalho.)
-- ---------------------------------------------------------------------
CREATE OR REPLACE FUNCTION bloqueia_pagamento()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    IF NEW.ativo = FALSE AND NEW.status_pagamento = 'Pago' THEN
        RAISE EXCEPTION 'O aluno % está inativo e por conta disso não pode ter o pagamento registrado',
            NEW.cliente_id;
    END IF;

    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS trg_bloqueio_pagamento ON pagamento;

CREATE TRIGGER trg_bloqueio_pagamento
BEFORE UPDATE ON pagamento
FOR EACH ROW
EXECUTE FUNCTION bloqueia_pagamento();


-- =====================================================================
-- 5. DEMONSTRAÇÕES
--    Cada uma desfaz o que alterou, então os dados terminam como estavam.
-- =====================================================================

-- 5.1 Functions
SELECT calculo_total_mensalidade();

SELECT desconto_faixas_pretas(150);

-- Mesma conta da function, em um SELECT comum:
SELECT 'O total dos alunos ' || COUNT(DISTINCT cliente_id)
    || ' faixas pretas com 10 por cento de desconto é: '
    || COUNT(DISTINCT cliente_id) * (150 - (150 * 10 / 100)) AS mensagem
  FROM matriculas
 WHERE faixa ILIKE 'Preta';

-- 5.2 Faixas: guarda o estado atual, promove os brancos de Jiu-Jitsu
--     (o trigger grava o histórico) e depois restaura.
DROP TABLE IF EXISTS matriculas_original;
CREATE TABLE matriculas_original AS SELECT * FROM matriculas;

CALL atualizar_faixa();

SELECT * FROM historico_faixa ORDER BY id_historico DESC;

CALL restaurar_faixa();

-- 5.3 Pagamentos: guarda o estado atual, marca os anuais como pagos e
--     depois restaura.
DROP TABLE IF EXISTS pagamento_original;
CREATE TABLE pagamento_original AS SELECT * FROM pagamento;

CALL atualizar_pagamento();

SELECT plano, status_pagamento, COUNT(*) AS total
  FROM pagamento
 WHERE ativo
 GROUP BY plano, status_pagamento
 ORDER BY plano, status_pagamento;

CALL restaurar_pagamento();

-- 5.4 Trigger de bloqueio: tenta marcar como 'Pago' o pagamento inativo
--     do cliente 5. O erro é capturado e mostrado como NOTICE, para não
--     desfazer o resto do script (no pgAdmin/Neon tudo roda junto).
DO $$
BEGIN
    UPDATE pagamento SET status_pagamento = 'Pago' WHERE cliente_id = 5;
    RAISE NOTICE 'ATENÇÃO: o trigger não bloqueou o pagamento.';
EXCEPTION
    WHEN raise_exception THEN
        RAISE NOTICE 'Trigger funcionou -> %', SQLERRM;
END;
$$;

SELECT cliente_id, ativo, status_pagamento FROM pagamento WHERE cliente_id = 5;

COMMIT;
