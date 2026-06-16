-- ============================================================
-- SCOPi — Estrutura do Banco de Dados
-- Sistema de Compras e Orçamentos de Produtos Inteligente
-- ============================================================

SET NAMES utf8mb4;
CREATE DATABASE IF NOT EXISTS scopi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE scopi;

SET FOREIGN_KEY_CHECKS = 0;

-- ── Usuários (RF03) ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS usuarios (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome             VARCHAR(150)  NOT NULL,
    email            VARCHAR(150)  NOT NULL UNIQUE,
    senha            VARCHAR(255)  NOT NULL,
    matricula        VARCHAR(30)   NULL,
    contato          VARCHAR(30)   NULL,
    departamento_id  INT UNSIGNED  NULL,
    perfil           ENUM('administrador','cadastrador','comprador','gerente','contabilidade','usuario') NOT NULL DEFAULT 'usuario',
    situacao         ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
    tentativas_falhas TINYINT UNSIGNED NOT NULL DEFAULT 0,
    ultima_tentativa DATETIME     NULL,
    criado_em        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em    DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email    (email),
    INDEX idx_situacao (situacao),
    INDEX idx_perfil   (perfil)
) ENGINE=InnoDB;

-- ── Departamentos (RF04) ──────────────────────────────────
CREATE TABLE IF NOT EXISTS departamentos (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome         VARCHAR(120)  NOT NULL,
    codigo       VARCHAR(20)   NOT NULL UNIQUE,
    gerente_id   INT UNSIGNED  NULL,
    situacao     ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
    criado_em    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_situacao (situacao),
    CONSTRAINT fk_departamento_gerente FOREIGN KEY (gerente_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Recuperação de Senha (RF01) ───────────────────────────
CREATE TABLE IF NOT EXISTS recuperacao_senha (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL UNIQUE,
    token      VARCHAR(64) NOT NULL,
    expira_em  DATETIME NOT NULL,
    CONSTRAINT fk_rec_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Tabelas Geográficas para Endereços (RF06) ──────────────
CREATE TABLE IF NOT EXISTS paises (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    sigla_iso2 CHAR(2) NULL,
    sigla_iso3 CHAR(3) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS estados (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    sigla CHAR(2) NOT NULL,
    pais_id INT UNSIGNED NOT NULL,
    UNIQUE KEY uq_estado_pais (sigla, pais_id),
    CONSTRAINT fk_estado_pais FOREIGN KEY (pais_id) REFERENCES paises(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cidades (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    estado_id INT UNSIGNED NOT NULL,
    UNIQUE KEY uq_cidade_estado (nome, estado_id),
    CONSTRAINT fk_cidade_estado FOREIGN KEY (estado_id) REFERENCES estados(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Categorias de produto (RF08) ─────────────────────────
CREATE TABLE IF NOT EXISTS categorias (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome      VARCHAR(100) NOT NULL UNIQUE,
    codigo    VARCHAR(20)  NOT NULL UNIQUE,
    situacao  ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo'
) ENGINE=InnoDB;

-- ── Fornecedores (RF05) ───────────────────────────────────
CREATE TABLE IF NOT EXISTS fornecedores (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    razao_social      VARCHAR(200) NOT NULL,
    nome_fantasia     VARCHAR(200) NULL,
    cnpj              VARCHAR(20)  NOT NULL UNIQUE,
    inscricao_estadual VARCHAR(30) NULL,
    logradouro        VARCHAR(250) NULL,
    numero            VARCHAR(30)  NULL,
    complemento       VARCHAR(150) NULL,
    bairro            VARCHAR(150) NULL,
    cep               VARCHAR(10)  NULL,
    cidade_id         INT UNSIGNED NULL,
    email             VARCHAR(150) NULL,
    contato           VARCHAR(50)  NULL,
    responsavel       VARCHAR(150) NULL,
    codigo            VARCHAR(20)  NOT NULL UNIQUE,
    tipo              ENUM('matriz','filial') NOT NULL DEFAULT 'matriz',
    matriz_id         INT UNSIGNED NULL,
    situacao          ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
    criado_em         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em     DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_situacao (situacao),
    INDEX idx_cnpj     (cnpj),
    CONSTRAINT fk_fornecedor_matriz FOREIGN KEY (matriz_id) REFERENCES fornecedores(id) ON DELETE SET NULL,
    CONSTRAINT fk_fornecedor_cidade FOREIGN KEY (cidade_id) REFERENCES cidades(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Relação N:N Fornecedor ↔ Categorias (RF05/RF08) ──────
-- "uma ou mais categorias de mercadorias vendidas"
CREATE TABLE IF NOT EXISTS fornecedor_categorias (
    fornecedor_id INT UNSIGNED NOT NULL,
    categoria_id  INT UNSIGNED NOT NULL,
    PRIMARY KEY (fornecedor_id, categoria_id),
    CONSTRAINT fk_fc_fornecedor FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE CASCADE,
    CONSTRAINT fk_fc_categoria  FOREIGN KEY (categoria_id)  REFERENCES categorias(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Produtos (RF07) ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS produtos (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome         VARCHAR(200) NOT NULL,
    descricao    TEXT         NULL,
    codigo       VARCHAR(20)  NOT NULL UNIQUE,
    categoria_id INT UNSIGNED NULL,
    situacao     ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
    criado_em    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME    NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_situacao (situacao),
    CONSTRAINT fk_produto_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Solicitações (RF09) ───────────────────────────────────
CREATE TABLE IF NOT EXISTS solicitacoes (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero           VARCHAR(30)  NOT NULL UNIQUE,
    departamento_id  INT UNSIGNED NOT NULL,
    usuario_id       INT UNSIGNED NOT NULL,
    gerente_id       INT UNSIGNED NULL,
    justificativa    TEXT         NOT NULL,
    status           ENUM('aberto','autorizado','em_cotacao','concluido','cancelado') NOT NULL DEFAULT 'aberto',
    autorizado_em    DATETIME     NULL,
    criado_em        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em    DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status          (status),
    INDEX idx_departamento    (departamento_id),
    CONSTRAINT fk_sol_departamento FOREIGN KEY (departamento_id) REFERENCES departamentos(id),
    CONSTRAINT fk_sol_usuario      FOREIGN KEY (usuario_id)      REFERENCES usuarios(id),
    CONSTRAINT fk_sol_gerente      FOREIGN KEY (gerente_id)      REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Itens das Solicitações ────────────────────────────────
CREATE TABLE IF NOT EXISTS solicitacao_itens (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    solicitacao_id  INT UNSIGNED NOT NULL,
    numero_item     INT UNSIGNED NOT NULL COMMENT 'Número sequencial do item na solicitação',
    produto_id      INT UNSIGNED NOT NULL,
    quantidade      DECIMAL(10,2) NOT NULL DEFAULT 1,
    observacao      TEXT          NULL,
    status          ENUM('aberto','autorizado','em_cotacao','concluido','cancelado') NOT NULL DEFAULT 'aberto' COMMENT 'Segue os mesmos status da solicitação',
    CONSTRAINT fk_si_solicitacao FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes(id) ON DELETE CASCADE,
    CONSTRAINT fk_si_produto     FOREIGN KEY (produto_id)     REFERENCES produtos(id)
) ENGINE=InnoDB;

-- ── Cotações (RF10) ───────────────────────────────────────
-- Status conforme requisito: aberta, fechada, concluida, cancelada
CREATE TABLE IF NOT EXISTS cotacoes (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero          VARCHAR(30)  NOT NULL UNIQUE,
    solicitacao_id  INT UNSIGNED NOT NULL,
    usuario_id      INT UNSIGNED NOT NULL,
    status          ENUM('aberta','fechada','concluida','cancelada') NOT NULL DEFAULT 'aberta',
    data_abertura   DATE         NULL,
    data_encerramento DATE       NULL,
    observacao      TEXT         NULL,
    criado_em       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    CONSTRAINT fk_cot_solicitacao FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes(id),
    CONSTRAINT fk_cot_usuario     FOREIGN KEY (usuario_id)     REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- ── Condições de Pagamento ────────────────────────
CREATE TABLE IF NOT EXISTS condicoes_pagamento (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo       CHAR(2)       NOT NULL UNIQUE,
    descricao    VARCHAR(150)  NOT NULL,
    situacao     ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
    criado_em    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_situacao (situacao)
) ENGINE=InnoDB;

-- ── Itens da Cotação ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS cotacao_itens (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cotacao_id      INT UNSIGNED NOT NULL,
    numero_item     INT UNSIGNED NOT NULL COMMENT 'Número sequencial do item na cotação',
    solicitacao_item_id INT UNSIGNED NULL,
    produto_id      INT UNSIGNED NOT NULL,
    quantidade      DECIMAL(10,2) NOT NULL DEFAULT 1,
    preco_unitario  DECIMAL(10,2) NULL,
    prazo_entrega   VARCHAR(100)  NULL COMMENT 'Prazo de entrega específico para este item (sugestão)',
    condicao_pagamento_id INT UNSIGNED NULL COMMENT 'Condição de pagamento sugerida para este item',
    CONSTRAINT fk_ci_cotacao FOREIGN KEY (cotacao_id) REFERENCES cotacoes(id) ON DELETE CASCADE,
    CONSTRAINT fk_ci_produto FOREIGN KEY (produto_id) REFERENCES produtos(id),
    CONSTRAINT fk_ci_cond_pag FOREIGN KEY (condicao_pagamento_id) REFERENCES condicoes_pagamento(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Convites de cotação para fornecedores (RF10/RF11) ─────
-- Cada fornecedor recebe um token único para responder à cotação
-- Dados globais da resposta do fornecedor para toda a cotação
CREATE TABLE IF NOT EXISTS cotacao_fornecedores (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cotacao_id          INT UNSIGNED  NOT NULL,
    fornecedor_id       INT UNSIGNED  NOT NULL,
    token               VARCHAR(64)   NOT NULL UNIQUE,
    status              ENUM('pendente','visualizado','respondido','recusado') NOT NULL DEFAULT 'pendente',
    enviado_em          DATETIME      NULL,
    respondido_em       DATETIME      NULL,
    transportadora      VARCHAR(150)  NULL COMMENT 'Transportadora oferecida globalmente pelo fornecedor',
    cnpj_transportadora VARCHAR(18)   NULL COMMENT 'CNPJ da transportadora',
    modalidade_frete    VARCHAR(100)  NULL COMMENT 'CIF ou FOB - global para toda a cotação',
    observacao          TEXT          NULL COMMENT 'Observação geral do fornecedor',
    taxas_adicionais    DECIMAL(12,2) NULL DEFAULT 0.00,
    validade_proposta   DATE          NULL,
    prazo_entrega       INT UNSIGNED  NULL,
    numero_envio        INT UNSIGNED  NOT NULL DEFAULT 0 COMMENT 'Contador de quantas vezes o fornecedor enviou resposta',
    vencedora           TINYINT(1)    NOT NULL DEFAULT 0,
    CONSTRAINT fk_cf_cotacao    FOREIGN KEY (cotacao_id)    REFERENCES cotacoes(id)    ON DELETE CASCADE,
    CONSTRAINT fk_cf_fornecedor FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id),
    UNIQUE KEY uk_cot_forn (cotacao_id, fornecedor_id)
) ENGINE=InnoDB;

-- ── Propostas dos fornecedores (RF12) ─────────────────────
-- Respostas POR ITEM da cotação (um fornecedor responde com múltiplas propostas, uma por item)
CREATE TABLE IF NOT EXISTS cotacao_propostas (
    id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cotacao_fornecedor_id INT UNSIGNED  NOT NULL,
    produto_id            INT UNSIGNED  NOT NULL,
    modelo                VARCHAR(200)  NULL COMMENT 'Modelo/variação do produto oferecido',
    quantidade            DECIMAL(10,2) NOT NULL COMMENT 'Quantidade respondida para este item',
    preco_unitario        DECIMAL(12,4) NOT NULL COMMENT 'Preço unitário para este item',
    prazo_entrega         INT UNSIGNED  NULL COMMENT 'Prazo de entrega em dias para este item',
    condicao_pagamento_id INT UNSIGNED  NULL COMMENT 'Condição de pagamento selecionada para este item',
    taxas                 DECIMAL(12,2) NULL DEFAULT 0.00 COMMENT 'Taxas/impostos adicionais para este item',
    garantia              VARCHAR(250)  NULL COMMENT 'Garantia oferecida para este item',
    disponivel            TINYINT(1)    NOT NULL DEFAULT 1 COMMENT '0 = fornecedor não possui o item',
    observacao            TEXT          NULL COMMENT 'Observação específica do fornecedor para este item',
    vencedora             TINYINT(1)    NOT NULL DEFAULT 0,
    CONSTRAINT fk_cp_cf      FOREIGN KEY (cotacao_fornecedor_id) REFERENCES cotacao_fornecedores(id) ON DELETE CASCADE,
    CONSTRAINT fk_cp_produto FOREIGN KEY (produto_id)            REFERENCES produtos(id),
    CONSTRAINT fk_cp_cond_pag FOREIGN KEY (condicao_pagamento_id) REFERENCES condicoes_pagamento(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Ordens de Compra (RF13) ───────────────────────────────
-- Status conforme requisito: aberta, autorizada, enviada, parcialmente_atendida, concluida, cancelada
CREATE TABLE IF NOT EXISTS ordens_compra (
    id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero                VARCHAR(30)   NOT NULL UNIQUE,
    cotacao_id            INT UNSIGNED  NULL,
    token                 VARCHAR(64)   NULL UNIQUE,
    solicitacao_id        INT UNSIGNED  NULL,
    fornecedor_id         INT UNSIGNED  NOT NULL,
    condicao_pagamento    VARCHAR(150)  NULL,
    usuario_id            INT UNSIGNED  NOT NULL,
    aprovador_id          INT UNSIGNED  NULL,
    modalidade_frete      VARCHAR(100)  NULL,
    transportadora        VARCHAR(150)  NULL,
    cnpj_transportadora   VARCHAR(18)   NULL,
    prazo_entrega         VARCHAR(100)  NULL,
    valor_total           DECIMAL(14,2) NOT NULL DEFAULT 0,
    status                ENUM('aberto','autorizado','enviado','parcialmente_atendido','concluido','cancelado','aprovado') NOT NULL DEFAULT 'aberto',
    aceito_fornecedor     TINYINT(1)    NOT NULL DEFAULT 0,
    aceito_em             DATETIME      NULL,
    emitido_em            DATE          NULL,
    autorizado_em         DATETIME      NULL,
    enviado_em            DATETIME      NULL,
    observacao            TEXT          NULL,
    criado_em             DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em         DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status       (status),
    INDEX idx_fornecedor   (fornecedor_id),
    CONSTRAINT fk_oc_cotacao      FOREIGN KEY (cotacao_id)      REFERENCES cotacoes(id)      ON DELETE SET NULL,
    CONSTRAINT fk_oc_solicitacao  FOREIGN KEY (solicitacao_id)  REFERENCES solicitacoes(id)  ON DELETE SET NULL,
    CONSTRAINT fk_oc_fornecedor   FOREIGN KEY (fornecedor_id)   REFERENCES fornecedores(id),
    CONSTRAINT fk_oc_usuario      FOREIGN KEY (usuario_id)      REFERENCES usuarios(id),
    CONSTRAINT fk_oc_aprovador    FOREIGN KEY (aprovador_id)    REFERENCES usuarios(id)      ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Itens da Ordem de Compra ──────────────────────────────
CREATE TABLE IF NOT EXISTS ordem_compra_itens (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ordem_id        INT UNSIGNED  NOT NULL,
    numero_item     INT UNSIGNED  NOT NULL COMMENT 'Número sequencial do item na ordem',
    solicitacao_item_id INT UNSIGNED NULL,
    produto_id      INT UNSIGNED  NOT NULL,
    quantidade      DECIMAL(10,2) NOT NULL,
    preco_unitario  DECIMAL(12,4) NOT NULL,
    subtotal        DECIMAL(14,2) GENERATED ALWAYS AS (quantidade * preco_unitario) STORED,
    condicao_pagamento_id INT UNSIGNED NULL COMMENT 'FK para condicoes_pagamento confirmada',
    prazo_entrega   DATE          NULL COMMENT 'Data de entrega prevista para este item',
    quantidade_atendida DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Quantidade já recebida via NF',
    status          ENUM('aberto','autorizado','enviado','parcialmente_atendido','concluido','cancelado','aprovado') NOT NULL DEFAULT 'aberto' COMMENT 'Segue os mesmos status da ordem de compra',
    status_item     ENUM('pendente','parcial','atendido','cancelado') NOT NULL DEFAULT 'pendente' COMMENT 'Status do recebimento do item',
    CONSTRAINT fk_oci_ordem   FOREIGN KEY (ordem_id)   REFERENCES ordens_compra(id) ON DELETE CASCADE,
    CONSTRAINT fk_oci_produto FOREIGN KEY (produto_id) REFERENCES produtos(id),
    CONSTRAINT fk_oci_solic_item FOREIGN KEY (solicitacao_item_id) REFERENCES solicitacao_itens(id) ON DELETE SET NULL,
    CONSTRAINT fk_oci_cond_pag FOREIGN KEY (condicao_pagamento_id) REFERENCES condicoes_pagamento(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Notas Fiscais (RF14) ──────────────────────────────────
CREATE TABLE IF NOT EXISTS notas_fiscais (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero            VARCHAR(20)   NOT NULL,
    serie             VARCHAR(5)    NULL,
    chave_acesso      VARCHAR(44)   NULL UNIQUE COMMENT 'Chave NF-e de 44 dígitos',
    fornecedor_id     INT UNSIGNED  NOT NULL,
    usuario_id        INT UNSIGNED  NOT NULL,
    natureza_operacao VARCHAR(100)  NULL,
    data_emissao      DATE          NOT NULL,
    data_entrada      DATE          NULL,
    modalidade_frete  VARCHAR(100)  NULL,
    transportadora    VARCHAR(150)  NULL,
    peso              DECIMAL(12,4) NULL,
    valor_produtos    DECIMAL(14,2) NOT NULL DEFAULT 0,

    valor_desconto    DECIMAL(12,2) NULL DEFAULT 0,
    valor_impostos    DECIMAL(12,2) NULL DEFAULT 0,
    taxas_adicionais  DECIMAL(12,2) NULL DEFAULT 0,
    valor_total       DECIMAL(14,2) NOT NULL DEFAULT 0,
    observacoes       TEXT          NULL,
    xml_nfe           LONGTEXT      NULL COMMENT 'XML completo da NF-e importada',
    status            ENUM('registrada','vinculada','cancelada') NOT NULL DEFAULT 'registrada',
    criado_em         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em     DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_fornecedor   (fornecedor_id),
    INDEX idx_data_emissao (data_emissao),
    INDEX idx_status       (status),
    CONSTRAINT fk_nf_fornecedor FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id),
    CONSTRAINT fk_nf_usuario   FOREIGN KEY (usuario_id)   REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- ── Relação N:N Nota Fiscal ↔ Ordens de Compra (RF14) ────
-- Permite vincular uma NF a várias OCs e uma OC a várias NFs
CREATE TABLE IF NOT EXISTS nota_fiscal_ordens (
    nota_fiscal_id INT UNSIGNED NOT NULL,
    ordem_id       INT UNSIGNED NOT NULL,
    PRIMARY KEY (nota_fiscal_id, ordem_id),
    CONSTRAINT fk_nfo_nota  FOREIGN KEY (nota_fiscal_id) REFERENCES notas_fiscais(id) ON DELETE CASCADE,
    CONSTRAINT fk_nfo_ordem FOREIGN KEY (ordem_id)       REFERENCES ordens_compra(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Itens da Nota Fiscal ──────────────────────────────────
CREATE TABLE IF NOT EXISTS nota_fiscal_itens (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nota_id        INT UNSIGNED  NOT NULL,
    produto_id     INT UNSIGNED  NULL,
    descricao      VARCHAR(200)  NOT NULL,
    quantidade     DECIMAL(10,4) NOT NULL,
    unidade        VARCHAR(10)   NULL,
    preco_unitario DECIMAL(12,4) NOT NULL,
    subtotal       DECIMAL(14,2) NOT NULL,
    ncm            VARCHAR(10)   NULL,
    ordem_compra_item_id INT UNSIGNED NULL,
    numero_item_pedido VARCHAR(50) NULL COMMENT 'nItemPed do XML',
    CONSTRAINT fk_nfi_nota    FOREIGN KEY (nota_id)    REFERENCES notas_fiscais(id) ON DELETE CASCADE,
    CONSTRAINT fk_nfi_produto FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL,
    CONSTRAINT fk_nfi_oci     FOREIGN KEY (ordem_compra_item_id) REFERENCES ordem_compra_itens(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Notificações Internas (RF15) ──────────────────────────
CREATE TABLE IF NOT EXISTS notificacoes (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT UNSIGNED NOT NULL,
    assunto     VARCHAR(255) NOT NULL,
    mensagem    TEXT         NOT NULL,
    categoria   VARCHAR(50)  NULL COMMENT 'solicitacao, cotacao, ordem, nota, alerta, sistema',
    lida        TINYINT(1)   NOT NULL DEFAULT 0,
    criado_em   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario  (usuario_id),
    INDEX idx_lida     (lida),
    CONSTRAINT fk_notif_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Histórico de Cadastros (auditoria) ───────────
CREATE TABLE IF NOT EXISTS historico_cadastros (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entidade VARCHAR(50) NOT NULL,
    entidade_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    evento VARCHAR(50) NOT NULL,
    campo_alterado VARCHAR(100) NULL COMMENT 'Campo que foi alterado (em caso de edição)',
    valor_anterior TEXT NULL COMMENT 'Valor antes da alteração',
    valor_atual TEXT NULL COMMENT 'Valor depois da alteração',
    detalhes TEXT NULL COMMENT 'Detalhes adicionais da ação',
    data_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_entidade (entidade, entidade_id),
    INDEX idx_usuario_id (usuario_id),
    CONSTRAINT fk_hist_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- Migração: adicionar coluna detalhes caso o banco já exista sem ela
-- ALTER TABLE historico_cadastros ADD COLUMN IF NOT EXISTS detalhes TEXT NULL COMMENT 'Detalhes adicionais da ação' AFTER valor_atual;

-- ============================================================
-- DADOS INICIAIS
-- ============================================================

-- Departamentos padrão
INSERT INTO departamentos (nome, codigo, situacao) VALUES
    ('Administração', 'dep0001', 'ativo'),
    ('Compras',       'dep0002',  'ativo'),
    ('Financeiro',    'dep0003',   'ativo'),
    ('Contabilidade', 'dep0004',  'ativo'),
    ('Operações',     'dep0005',  'ativo');

-- Usuário administrador padrão
-- Senha: admin@123 (hash bcrypt gerado pelo PHP)
INSERT INTO usuarios (nome, email, senha, matricula, departamento_id, perfil, situacao) VALUES
    ('Administrador do Sistema',
     'admin@scopi.com',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     '25000001',
     1,
     'administrador',
     'ativo');

-- Atualizar gerente_id do departamento de administração
UPDATE departamentos SET gerente_id = 1 WHERE codigo = 'dep0001';

-- Categorias de produto iniciais
INSERT INTO categorias (nome, codigo, situacao) VALUES
    ('Material de Escritório', 'cat001', 'ativo'),
    ('Equipamentos de TI',     'cat002', 'ativo'),
    ('Limpeza e Higiene',      'cat003', 'ativo'),
    ('Manutenção',             'cat004', 'ativo'),
    ('Outros',                 'cat005', 'ativo');

-- ── Condições de Pagamento iniciais ──────────────────────
INSERT INTO condicoes_pagamento (codigo, descricao, situacao) VALUES
    ('01', 'À Vista',                     'ativo'),
    ('02', '30 dias',                     'ativo'),
    ('03', '30/60 dias',                  'ativo'),
    ('04', '30/60/90 dias',               'ativo'),
    ('05', '28 dias (Boleto)',            'ativo'),
    ('06', '28/56 dias (Boleto)',         'ativo'),
    ('07', '28/56/84 dias (Boleto)',      'ativo'),
    ('08', 'Antecipado',                  'ativo'),
    ('09', '15 dias',                     'ativo'),
    ('10', '7 dias',                      'ativo');

-- ── Países ───────────────────────────────────────────────────
INSERT INTO paises (nome, sigla_iso2, sigla_iso3) VALUES
    ('Brasil',          'BR', 'BRA'),
    ('Argentina',       'AR', 'ARG'),
    ('Estados Unidos',  'US', 'USA'),
    ('China',           'CN', 'CHN'),
    ('Alemanha',        'DE', 'DEU');

-- ── Estados (Brasil) ─────────────────────────────────────────
INSERT INTO estados (nome, sigla, pais_id) VALUES
    ('São Paulo',           'SP', 1),
    ('Rio de Janeiro',      'RJ', 1),
    ('Minas Gerais',        'MG', 1),
    ('Rio Grande do Sul',   'RS', 1),
    ('Paraná',              'PR', 1),
    ('Santa Catarina',      'SC', 1),
    ('Bahia',               'BA', 1),
    ('Goiás',               'GO', 1),
    ('Distrito Federal',    'DF', 1),
    ('Ceará',               'CE', 1);

-- ── Cidades ──────────────────────────────────────────────────
INSERT INTO cidades (nome, estado_id) VALUES
    -- SP (id=1)
    ('São Paulo',         1),
    ('Campinas',          1),
    ('Guarulhos',         1),
    ('Ribeirão Preto',    1),
    ('Sorocaba',          1),
    -- RJ (id=2)
    ('Rio de Janeiro',    2),
    ('Niterói',           2),
    ('Duque de Caxias',   2),
    -- MG (id=3)
    ('Belo Horizonte',    3),
    ('Uberlândia',        3),
    ('Contagem',          3),
    -- RS (id=4)
    ('Porto Alegre',      4),
    ('Caxias do Sul',     4),
    ('Pelotas',           4),
    -- PR (id=5)
    ('Curitiba',          5),
    ('Londrina',          5),
    ('Maringá',           5),
    -- SC (id=6)
    ('Florianópolis',     6),
    ('Joinville',         6),
    ('Blumenau',          6),
    -- BA (id=7)
    ('Salvador',          7),
    ('Feira de Santana',  7),
    -- GO (id=8)
    ('Goiânia',           8),
    ('Anápolis',          8),
    -- DF (id=9)
    ('Brasília',          9),
    -- CE (id=10)
    ('Fortaleza',         10),
    ('Juazeiro do Norte', 10);

-- ── Usuários adicionais ──────────────────────────────────────
-- Senha padrão: admin@123 (mesmo hash bcrypt do administrador)
INSERT INTO usuarios (nome, email, senha, matricula, departamento_id, perfil, situacao) VALUES
    ('Carlos Eduardo Mendes',  'comprador@scopi.com',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     '25000002', 2, 'comprador', 'ativo'),

    ('Fernanda Lima Oliveira', 'gerente@scopi.com',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     '25000003', 2, 'gerente', 'ativo'),

    ('Ricardo Souza Costa',    'cadastrador@scopi.com',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     '25000004', 1, 'cadastrador', 'ativo'),

    ('Patrícia Alves Ramos',   'contabilidade@scopi.com',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     '25000005', 4, 'contabilidade', 'ativo'),

    ('João Pedro Ferreira',    'joao.ferreira@scopi.com',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     '25000006', 5, 'usuario', 'ativo'),

    ('Ana Paula Martins',      'ana.martins@scopi.com',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     '25000007', 3, 'usuario', 'ativo');

-- Atualiza gerente do departamento de Compras

-- ── Fornecedores ─────────────────────────────────────────────
INSERT INTO fornecedores
    (razao_social, nome_fantasia, cnpj, inscricao_estadual,
     logradouro, numero, bairro, cep, cidade_id,
     email, contato, responsavel, codigo, tipo, situacao)
VALUES
    ('Papelaria Nacional Distribuidora Ltda', 'PaperMax',
     '12.345.678/0001-90', '123.456.789.110',
     'Av. Industrial', '1500', 'Distrito Industrial', '01310-100', 1,
     'vendas@papermax.com.br', '(11) 3333-1000', 'Marcos Andrade',
     'forn000001', 'matriz', 'ativo'),

    ('Tech Solutions Informática S/A', 'TechSol',
     '23.456.789/0001-01', '234.567.890.220',
     'Rua dos Tecnólogos', '320', 'Centro', '04001-000', 1,
     'comercial@techsol.com.br', '(11) 4444-2000', 'Luciana Pires',
     'forn000002', 'matriz', 'ativo'),

    ('LimpPro Produtos de Limpeza Eireli', 'LimpPro',
     '34.567.890/0001-12', '345.678.901.330',
     'Rua das Flores', '80', 'Jardim América', '80010-030', 15,
     'contato@limppro.com.br', '(41) 5555-3000', 'Sandra Rocha',
     'forn000003', 'matriz', 'ativo'),

    ('Manutech Serviços e Equipamentos Ltda', 'Manutech',
     '45.678.901/0001-23', '456.789.012.440',
     'Av. das Máquinas', '950', 'Polo Industrial', '30110-000', 9,
     'orcamentos@manutech.com.br', '(31) 6666-4000', 'Roberto Dias',
     'forn000004', 'matriz', 'ativo'),

    ('Multisuprimentos Gerais Comércio Ltda', 'MultiSup',
     '56.789.012/0001-34', '567.890.123.550',
     'Rua Comércio Local', '200', 'Setor Comercial', '70070-010', 25,
     'vendas@multisup.com.br', '(61) 7777-5000', 'Camila Torres',
     'forn000005', 'matriz', 'ativo'),

    ('InfoTech Equipamentos e Suprimentos S/A', 'InfoTech',
     '67.890.123/0001-45', '678.901.234.660',
     'Av. Tecnologia', '1200', 'TechPark', '04309-010', 1,
     'vendas@infotech.com.br', '(11) 8888-6000', 'Felipe Nunes',
     'forn000006', 'filial', 'ativo'),

    ('Sul Distribuidora de Materiais Ltda', 'SulMatérias',
     '78.901.234/0001-56', '789.012.345.770',
     'Rua 7 de Setembro', '450', 'Centro', '90010-190', 12,
     'comercial@sulmaterias.com.br', '(51) 9999-7000', 'Denise Vargas',
     'forn000007', 'matriz', 'ativo');

-- ── Fornecedor ↔ Categorias ──────────────────────────────────
-- cat 1=Material de Escritório, 2=Equipamentos de TI,
--     3=Limpeza e Higiene,      4=Manutenção,  5=Outros
INSERT INTO fornecedor_categorias (fornecedor_id, categoria_id) VALUES
    (1, 1), (1, 5),        -- PaperMax:    escritório + outros
    (2, 2), (2, 1),        -- TechSol:     TI + escritório
    (3, 3), (3, 5),        -- LimpPro:     limpeza + outros
    (4, 4), (4, 2),        -- Manutech:    manutenção + TI
    (5, 1), (5, 3), (5, 5),-- MultiSup:    escritório + limpeza + outros
    (6, 2), (6, 4),        -- InfoTech:    TI + manutenção
    (7, 1), (7, 3), (7, 4);-- SulMatérias: escritório + limpeza + manutenção

-- ── Produtos ─────────────────────────────────────────────────
INSERT INTO produtos (nome, descricao, codigo, categoria_id, situacao) VALUES
    -- Material de Escritório
    ('Resma de Papel A4 500fls',           'Papel sulfite A4 75g/m², pacote com 500 folhas',                   'prod000001', 1, 'ativo'),
    ('Caneta Esferográfica Azul (cx 50un)','Caneta esferográfica ponta média, caixa com 50 unidades',          'prod000002', 1, 'ativo'),
    ('Grampeador Médio 26/6',              'Grampeador de mesa capacidade 30 folhas, grampo 26/6',             'prod000003', 1, 'ativo'),
    ('Pasta Arquivo com Aba Elástica A4',  'Pasta arquivo em cartão, fechamento elástico, formato A4',         'prod000004', 1, 'ativo'),
    ('Post-it 76x76mm (bloco 100fls)',     'Bloco de notas adesivas amarelas, 100 folhas',                     'prod000005', 1, 'ativo'),
    ('Marcador de Texto (cx 12)',          'Caneta marcador de texto, cores sortidas, caixa com 12',           'prod000006', 1, 'ativo'),
    -- Equipamentos de TI
    ('Monitor LED 24" Full HD',            'Monitor 24 pol., resolução 1920x1080, HDMI/VGA',                   'prod000007', 2, 'ativo'),
    ('Teclado USB ABNT2',                  'Teclado USB padrão ABNT2, com fio',                                'prod000008', 2, 'ativo'),
    ('Mouse Óptico USB',                   'Mouse óptico com fio, 1000 DPI, 3 botões',                         'prod000009', 2, 'ativo'),
    ('Cartucho de Tinta Preta HP 664',     'Cartucho de tinta preta original HP série 664',                    'prod000010', 2, 'ativo'),
    ('Cabo HDMI 2m',                       'Cabo HDMI 2.0, 2 metros, com filtro anti-interferência',           'prod000011', 2, 'ativo'),
    ('Pendrive USB 3.0 32GB',              'Pendrive USB 3.0, capacidade 32 GB',                               'prod000012', 2, 'ativo'),
    -- Limpeza e Higiene
    ('Detergente Líquido 500ml (fardo 24)','Detergente neutro 500ml, fardo com 24 unidades',                   'prod000013', 3, 'ativo'),
    ('Papel Toalha Interfolha (pct 1000)', 'Papel toalha interfolha branco, 2 dobras, 1000 folhas',            'prod000014', 3, 'ativo'),
    ('Álcool 70% Líquido 1L',              'Álcool etílico hidratado 70% INPM, frasco 1 litro',                'prod000015', 3, 'ativo'),
    ('Saco de Lixo 100L (rolo 10un)',      'Saco plástico para lixo preto 100 litros, rolo com 10',            'prod000016', 3, 'ativo'),
    -- Manutenção
    ('Lâmpada LED Tubular 20W',            'Lâmpada LED tubular T8 20W bivolt',                                'prod000017', 4, 'ativo'),
    ('Tomada 2P+T 10A',                    'Tomada elétrica 2 pinos + terra 10A padrão NBR 14136',             'prod000018', 4, 'ativo'),
    ('Fita Isolante 20m',                  'Fita isolante PVC 20 metros, 70°C',                                'prod000019', 4, 'ativo'),
    ('Pilha Alcalina AA (cx 24un)',         'Pilha alcalina tipo AA 1,5V, caixa com 24 unidades',               'prod000020', 4, 'ativo');
