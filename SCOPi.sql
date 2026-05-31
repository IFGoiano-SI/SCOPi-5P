-- ============================================================
-- SCOPi — Estrutura do Banco de Dados
-- Sistema de Compras e Orçamentos de Produtos Inteligente
-- ============================================================
-- Execute este script no seu MySQL/MariaDB:
-- mysql -u root -p < sql.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS scopi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE scopi;

SET FOREIGN_KEY_CHECKS = 0;

-- ── Departamentos (RF04) ──────────────────────────────────
CREATE TABLE IF NOT EXISTS departamentos (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome         VARCHAR(120)  NOT NULL,
    codigo       VARCHAR(20)   NOT NULL UNIQUE,
    gerente_id   INT UNSIGNED  NULL,
    situacao     ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
    criado_em    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_situacao (situacao)
) ENGINE=InnoDB;

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
    INDEX idx_perfil   (perfil),
    CONSTRAINT fk_usuario_departamento FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Recuperação de Senha (RF01) ───────────────────────────
CREATE TABLE IF NOT EXISTS recuperacao_senha (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL UNIQUE,
    token      VARCHAR(64) NOT NULL,
    expira_em  DATETIME NOT NULL,
    CONSTRAINT fk_rec_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Agora atribui gerente_id como FK após criar usuários
ALTER TABLE departamentos
    ADD CONSTRAINT fk_departamento_gerente
    FOREIGN KEY (gerente_id) REFERENCES usuarios(id) ON DELETE SET NULL;

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
    status           ENUM('em_aberto','autorizada','em_cotacao','recusada','concluida','cancelada') NOT NULL DEFAULT 'em_aberto',
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
    produto_id      INT UNSIGNED NOT NULL,
    quantidade      DECIMAL(10,2) NOT NULL DEFAULT 1,
    observacao      TEXT          NULL,
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

-- ── Itens da Cotação ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS cotacao_itens (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cotacao_id      INT UNSIGNED  NOT NULL,
    produto_id      INT UNSIGNED  NOT NULL,
    quantidade      DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_ci_cotacao  FOREIGN KEY (cotacao_id)  REFERENCES cotacoes(id) ON DELETE CASCADE,
    CONSTRAINT fk_ci_produto  FOREIGN KEY (produto_id)  REFERENCES produtos(id)
) ENGINE=InnoDB;

-- ── Convites de cotação para fornecedores (RF10/RF11) ─────
-- Cada fornecedor recebe um token único para responder à cotação
CREATE TABLE IF NOT EXISTS cotacao_fornecedores (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cotacao_id      INT UNSIGNED  NOT NULL,
    fornecedor_id   INT UNSIGNED  NOT NULL,
    token           VARCHAR(64)   NOT NULL UNIQUE,
    status          ENUM('pendente','visualizado','respondido','recusado') NOT NULL DEFAULT 'pendente',
    enviado_em      DATETIME      NULL,
    respondido_em   DATETIME      NULL,
    modalidade_frete VARCHAR(100)  NULL,
    transportadora  VARCHAR(150)  NULL,
    condicao_pagamento VARCHAR(150) NULL,
    impostos        DECIMAL(12,2) NULL DEFAULT 0.00,
    taxas_adicionais DECIMAL(12,2) NULL DEFAULT 0.00,
    validade_proposta DATE         NULL,
    garantia        VARCHAR(250)  NULL,
    prazo_entrega   INT           NULL COMMENT 'dias',
    observacao      TEXT          NULL,
    vencedora       TINYINT(1)    NOT NULL DEFAULT 0,
    CONSTRAINT fk_cf_cotacao    FOREIGN KEY (cotacao_id)    REFERENCES cotacoes(id)    ON DELETE CASCADE,
    CONSTRAINT fk_cf_fornecedor FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id),
    UNIQUE KEY uk_cot_forn (cotacao_id, fornecedor_id)
) ENGINE=InnoDB;

-- ── Propostas dos fornecedores (RF12) ─────────────────────
CREATE TABLE IF NOT EXISTS cotacao_propostas (
    id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cotacao_fornecedor_id INT UNSIGNED  NOT NULL,
    produto_id            INT UNSIGNED  NOT NULL,
    modelo                VARCHAR(200)  NULL,
    quantidade            DECIMAL(10,2) NOT NULL,
    preco_unitario        DECIMAL(12,4) NOT NULL,
    prazo_entrega         INT           NULL COMMENT 'dias',
    disponivel            TINYINT(1)    NOT NULL DEFAULT 1 COMMENT '0 = fornecedor não possui o item',
    observacao            TEXT          NULL,
    CONSTRAINT fk_cp_cf      FOREIGN KEY (cotacao_fornecedor_id) REFERENCES cotacao_fornecedores(id) ON DELETE CASCADE,
    CONSTRAINT fk_cp_produto FOREIGN KEY (produto_id)            REFERENCES produtos(id)
) ENGINE=InnoDB;

-- ── Ordens de Compra (RF13) ───────────────────────────────
-- Status conforme requisito: aberta, autorizada, enviada, parcialmente_atendida, concluida, cancelada
CREATE TABLE IF NOT EXISTS ordens_compra (
    id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero                VARCHAR(30)   NOT NULL UNIQUE,
    cotacao_id            INT UNSIGNED  NULL,
    solicitacao_id        INT UNSIGNED  NULL,
    fornecedor_id         INT UNSIGNED  NOT NULL,
    usuario_id            INT UNSIGNED  NOT NULL,
    aprovador_id          INT UNSIGNED  NULL,
    condicao_pagamento    VARCHAR(150)  NULL,
    modalidade_frete      VARCHAR(100)  NULL,
    prazo_entrega         VARCHAR(50)   NULL,
    valor_total           DECIMAL(14,2) NOT NULL DEFAULT 0,
    status                ENUM('aberta','autorizada','enviada','parcialmente_atendida','concluida','cancelada') NOT NULL DEFAULT 'aberta',
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
    produto_id      INT UNSIGNED  NOT NULL,
    quantidade      DECIMAL(10,2) NOT NULL,
    preco_unitario  DECIMAL(12,4) NOT NULL,
    subtotal        DECIMAL(14,2) GENERATED ALWAYS AS (quantidade * preco_unitario) STORED,
    quantidade_atendida DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Quantidade já recebida via NF',
    status_item     ENUM('pendente','parcial','atendido','cancelado') NOT NULL DEFAULT 'pendente',
    CONSTRAINT fk_oci_ordem   FOREIGN KEY (ordem_id)   REFERENCES ordens_compra(id) ON DELETE CASCADE,
    CONSTRAINT fk_oci_produto FOREIGN KEY (produto_id) REFERENCES produtos(id)
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
    valor_frete       DECIMAL(12,2) NULL DEFAULT 0,
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
    CONSTRAINT fk_nfi_nota    FOREIGN KEY (nota_id)    REFERENCES notas_fiscais(id) ON DELETE CASCADE,
    CONSTRAINT fk_nfi_produto FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL
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

-- ── Histórico de alterações (auditoria - RNF05) ───────────
CREATE TABLE IF NOT EXISTS historico_alteracoes (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tabela          VARCHAR(60)  NOT NULL,
    registro_id     INT UNSIGNED NOT NULL,
    dados_anteriores JSON        NULL,
    dados_novos     JSON         NULL,
    usuario_id      INT UNSIGNED NOT NULL,
    criado_em       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tabela     (tabela, registro_id),
    INDEX idx_usuario_id (usuario_id),
    CONSTRAINT fk_hist_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- DADOS INICIAIS
-- ============================================================

-- Departamentos padrão
INSERT INTO departamentos (nome, codigo, situacao) VALUES
    ('Administração', 'DEP-ADMIN', 'ativo'),
    ('Compras',       'DEP-COMP',  'ativo'),
    ('Financeiro',    'DEP-FIN',   'ativo'),
    ('Contabilidade', 'DEP-CONT',  'ativo'),
    ('Operações',     'DEP-OPER',  'ativo');

-- Usuário administrador padrão
-- Senha: admin@123 (hash bcrypt gerado pelo PHP)
INSERT INTO usuarios (nome, email, senha, matricula, departamento_id, perfil, situacao) VALUES
    ('Administrador do Sistema',
     'admin@scopi.com',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'ADM-001',
     1,
     'administrador',
     'ativo');

-- Atualiza gerente do departamento admin
UPDATE departamentos SET gerente_id = 1 WHERE codigo = 'DEP-ADMIN';

-- Categorias de produto iniciais
INSERT INTO categorias (nome, situacao) VALUES
    ('Material de Escritório', 'ativo'),
    ('Equipamentos de TI',     'ativo'),
    ('Limpeza e Higiene',      'ativo'),
    ('Manutenção',             'ativo'),
    ('Outros',                 'ativo');
