<?php
namespace Models;

class NotaFiscalModelo extends ModeloBase {
    protected string $tabela = 'notas_fiscais';

    public function totalPorMes(int $meses = 6): array {
        $q = $this->bd->prepare("SELECT DATE_FORMAT(data_emissao,'%Y-%m') AS mes, SUM(valor_total) AS total FROM notas_fiscais WHERE data_emissao >= DATE_SUB(NOW(), INTERVAL :m MONTH) GROUP BY mes ORDER BY mes");
        $q->execute([':m'=>$meses]);
        return $q->fetchAll();
    }

    public function listarComFiltros(array $filtros = []): array {
        $sql = "SELECT nf.*, f.razao_social AS nome_fornecedor FROM notas_fiscais nf LEFT JOIN fornecedores f ON f.id=nf.fornecedor_id WHERE 1=1";
        $p = [];
        if (!empty($filtros['numero']))  { $sql .= ' AND nf.numero LIKE :num';    $p[':num']    = "%{$filtros['numero']}%"; }
        if (!empty($filtros['chave']))   { $sql .= ' AND nf.chave_acesso LIKE :chave'; $p[':chave'] = "%{$filtros['chave']}%"; }
        if (!empty($filtros['periodo'])) { $sql .= ' AND DATE(nf.data_emissao)>=:per'; $p[':per'] = $filtros['periodo']; }
        if (!empty($filtros['status']))  { $sql .= ' AND nf.status = :status'; $p[':status'] = $filtros['status']; }
        $sql .= ' ORDER BY nf.data_emissao DESC';
        $q = $this->bd->prepare($sql); $q->execute($p);
        return $q->fetchAll();
    }

    /**
     * RF14: Buscar nota fiscal por ID com detalhes
     */
    public function buscarPorId(int $id): ?array {
        $q = $this->bd->prepare("
            SELECT nf.*, f.razao_social AS nome_fornecedor, f.cnpj AS cnpj_fornecedor,
                   u.nome AS nome_usuario
            FROM notas_fiscais nf
            LEFT JOIN fornecedores f ON f.id = nf.fornecedor_id
            LEFT JOIN usuarios u ON u.id = nf.usuario_id
            WHERE nf.id = :id LIMIT 1
        ");
        $q->execute([':id' => $id]);
        $nf = $q->fetch() ?: null;
        if ($nf) {
            $nf['itens'] = $this->buscarItens($id);
            $nf['ordens_vinculadas'] = $this->buscarOrdensVinculadas($id);
        }
        return $nf;
    }

    /**
     * RF14: Cadastro completo de nota fiscal
     */
    public function cadastrar(array $dados, int $usuarioId): int {
        // Verificar chave de acesso duplicada
        if (!empty($dados['chave_acesso'])) {
            $qCheck = $this->bd->prepare("SELECT id FROM notas_fiscais WHERE chave_acesso = :chave LIMIT 1");
            $qCheck->execute([':chave' => $dados['chave_acesso']]);
            if ($qCheck->fetch()) {
                throw new \Exception('Nota fiscal com esta chave de acesso já está cadastrada.');
            }
        }

        $q = $this->bd->prepare("
            INSERT INTO notas_fiscais (
                numero, serie, chave_acesso, fornecedor_id, usuario_id,
                natureza_operacao, data_emissao, data_entrada,
                modalidade_frete, transportadora, peso,
                valor_produtos, valor_frete, valor_desconto, valor_impostos,
                taxas_adicionais, valor_total, observacoes, xml_nfe, status, criado_em
            ) VALUES (
                :num, :serie, :chave, :fid, :uid,
                :nat, :dt_emissao, :dt_entrada,
                :frete, :transp, :peso,
                :v_prod, :v_frete, :v_desc, :v_imp,
                :v_taxas, :v_total, :obs, :xml, 'registrada', NOW()
            )
        ");
        $q->execute([
            ':num' => $dados['numero'],
            ':serie' => $dados['serie'] ?? null,
            ':chave' => $dados['chave_acesso'] ?? null,
            ':fid' => (int)$dados['fornecedor_id'],
            ':uid' => $usuarioId,
            ':nat' => $dados['natureza_operacao'] ?? null,
            ':dt_emissao' => $dados['data_emissao'],
            ':dt_entrada' => $dados['data_entrada'] ?? null,
            ':frete' => $dados['modalidade_frete'] ?? null,
            ':transp' => $dados['transportadora'] ?? null,
            ':peso' => $dados['peso'] ?? null,
            ':v_prod' => (float)($dados['valor_produtos'] ?? 0),
            ':v_frete' => (float)($dados['valor_frete'] ?? 0),
            ':v_desc' => (float)($dados['valor_desconto'] ?? 0),
            ':v_imp' => (float)($dados['valor_impostos'] ?? 0),
            ':v_taxas' => (float)($dados['taxas_adicionais'] ?? 0),
            ':v_total' => (float)($dados['valor_total'] ?? 0),
            ':obs' => $dados['observacoes'] ?? null,
            ':xml' => $dados['xml_nfe'] ?? null
        ]);
        $novoId = (int)$this->bd->lastInsertId();
        $this->registrarHistorico($this->tabela, $novoId, [], $dados, $usuarioId);
        return $novoId;
    }

    /**
     * RF14: Salvar itens da nota fiscal
     */
    public function salvarItens(int $notaId, array $itens): void {
        $this->bd->prepare("DELETE FROM nota_fiscal_itens WHERE nota_id = :nid")->execute([':nid' => $notaId]);
        $q = $this->bd->prepare("
            INSERT INTO nota_fiscal_itens (nota_id, produto_id, descricao, quantidade, unidade, preco_unitario, subtotal, ncm)
            VALUES (:nid, :pid, :desc, :qtd, :und, :preco, :sub, :ncm)
        ");
        foreach ($itens as $item) {
            $q->execute([
                ':nid' => $notaId,
                ':pid' => !empty($item['produto_id']) ? (int)$item['produto_id'] : null,
                ':desc' => $item['descricao'],
                ':qtd' => (float)$item['quantidade'],
                ':und' => $item['unidade'] ?? null,
                ':preco' => (float)$item['preco_unitario'],
                ':sub' => (float)$item['subtotal'],
                ':ncm' => $item['ncm'] ?? null
            ]);
        }
    }

    /**
     * RF14: Buscar itens de uma nota fiscal
     */
    public function buscarItens(int $notaId): array {
        $q = $this->bd->prepare("
            SELECT nfi.*, p.nome AS produto_nome, p.codigo AS produto_codigo
            FROM nota_fiscal_itens nfi
            LEFT JOIN produtos p ON p.id = nfi.produto_id
            WHERE nfi.nota_id = :nid
        ");
        $q->execute([':nid' => $notaId]);
        return $q->fetchAll();
    }

    /**
     * RF14: Vincular nota fiscal a uma ou mais ordens de compra (N:N)
     */
    public function vincularOrdem(int $notaId, int $ordemId): bool {
        // Verificar se já existe vínculo
        $qCheck = $this->bd->prepare("SELECT 1 FROM nota_fiscal_ordens WHERE nota_fiscal_id = :nid AND ordem_id = :oid");
        $qCheck->execute([':nid' => $notaId, ':oid' => $ordemId]);
        if ($qCheck->fetch()) return false;

        $q = $this->bd->prepare("INSERT INTO nota_fiscal_ordens (nota_fiscal_id, ordem_id) VALUES (:nid, :oid)");
        $q->execute([':nid' => $notaId, ':oid' => $ordemId]);

        // Atualizar status da NF para vinculada
        $this->bd->prepare("UPDATE notas_fiscais SET status = 'vinculada', atualizado_em = NOW() WHERE id = :nid")->execute([':nid' => $notaId]);

        return true;
    }

    /**
     * RF14: Buscar ordens de compra vinculadas a uma NF
     */
    public function buscarOrdensVinculadas(int $notaId): array {
        $q = $this->bd->prepare("
            SELECT oc.*, f.razao_social AS nome_fornecedor
            FROM ordens_compra oc
            INNER JOIN nota_fiscal_ordens nfo ON nfo.ordem_id = oc.id
            LEFT JOIN fornecedores f ON f.id = oc.fornecedor_id
            WHERE nfo.nota_fiscal_id = :nid
        ");
        $q->execute([':nid' => $notaId]);
        return $q->fetchAll();
    }

    /**
     * RF14: Importar dados de XML NF-e
     */
    public function importarXml(string $xmlContent): array {
        $dados = [];
        try {
            $xml = simplexml_load_string($xmlContent);
            if (!$xml) throw new \Exception('XML inválido');

            $nfe = $xml;
            // Navegar pela estrutura do XML NF-e
            if (isset($xml->NFe)) $nfe = $xml->NFe;
            if (isset($nfe->infNFe)) $nfe = $nfe->infNFe;

            // Dados do cabeçalho
            if (isset($nfe->ide)) {
                $dados['numero'] = (string)($nfe->ide->nNF ?? '');
                $dados['serie'] = (string)($nfe->ide->serie ?? '');
                $dados['natureza_operacao'] = (string)($nfe->ide->natOp ?? '');
                $dados['data_emissao'] = (string)($nfe->ide->dhEmi ?? '');
                if (!empty($dados['data_emissao'])) {
                    $dados['data_emissao'] = substr($dados['data_emissao'], 0, 10);
                }
                $dados['modalidade_frete'] = (string)($nfe->ide->modFrete ?? '');
            }

            // Chave de acesso
            if (isset($xml->protNFe->infProt->chNFe)) {
                $dados['chave_acesso'] = (string)$xml->protNFe->infProt->chNFe;
            } elseif (isset($nfe['Id'])) {
                $id = (string)$nfe['Id'];
                $dados['chave_acesso'] = str_replace('NFe', '', $id);
            }

            // Dados do emitente (fornecedor)
            if (isset($nfe->emit)) {
                $dados['fornecedor_cnpj'] = (string)($nfe->emit->CNPJ ?? '');
                $dados['fornecedor_razao'] = (string)($nfe->emit->xNome ?? '');
            }

            // Dados de transporte
            if (isset($nfe->transp)) {
                $dados['transportadora'] = (string)($nfe->transp->transporta->xNome ?? '');
                if (isset($nfe->transp->vol)) {
                    $dados['peso'] = (float)($nfe->transp->vol->pesoB ?? 0);
                }
            }

            // Totais
            if (isset($nfe->total->ICMSTot)) {
                $tot = $nfe->total->ICMSTot;
                $dados['valor_produtos'] = (float)($tot->vProd ?? 0);
                $dados['valor_frete'] = (float)($tot->vFrete ?? 0);
                $dados['valor_desconto'] = (float)($tot->vDesc ?? 0);
                $dados['valor_impostos'] = (float)($tot->vICMS ?? 0) + (float)($tot->vIPI ?? 0) + (float)($tot->vPIS ?? 0) + (float)($tot->vCOFINS ?? 0);
                $dados['valor_total'] = (float)($tot->vNF ?? 0);
            }

            // Itens
            $dados['itens'] = [];
            $dets = isset($nfe->det) ? $nfe->det : [];
            foreach ($dets as $det) {
                $prod = $det->prod;
                $dados['itens'][] = [
                    'descricao' => (string)($prod->xProd ?? ''),
                    'quantidade' => (float)($prod->qCom ?? 0),
                    'unidade' => (string)($prod->uCom ?? ''),
                    'preco_unitario' => (float)($prod->vUnCom ?? 0),
                    'subtotal' => (float)($prod->vProd ?? 0),
                    'ncm' => (string)($prod->NCM ?? '')
                ];
            }

            $dados['xml_nfe'] = $xmlContent;
        } catch (\Exception $e) {
            $dados['erro'] = $e->getMessage();
        }
        return $dados;
    }

    /**
     * RF14: Verificar divergências entre NF e OC vinculadas
     */
    public function verificarDivergencias(int $notaId, int $ordemId): array {
        $divergencias = [];

        // Buscar itens da NF
        $itensNF = $this->buscarItens($notaId);
        
        // Buscar itens da OC
        $qOC = $this->bd->prepare("
            SELECT oci.*, p.nome AS produto_nome
            FROM ordem_compra_itens oci
            LEFT JOIN produtos p ON p.id = oci.produto_id
            WHERE oci.ordem_id = :oid
        ");
        $qOC->execute([':oid' => $ordemId]);
        $itensOC = $qOC->fetchAll();

        // Comparar quantidades e valores
        foreach ($itensOC as $itemOC) {
            $encontrado = false;
            foreach ($itensNF as $itemNF) {
                if ($itemNF['produto_id'] == $itemOC['produto_id']) {
                    $encontrado = true;
                    if ((float)$itemNF['quantidade'] != (float)$itemOC['quantidade']) {
                        $divergencias[] = [
                            'tipo' => 'quantidade',
                            'produto' => $itemOC['produto_nome'],
                            'esperado' => $itemOC['quantidade'],
                            'recebido' => $itemNF['quantidade']
                        ];
                    }
                    if (abs((float)$itemNF['preco_unitario'] - (float)$itemOC['preco_unitario']) > 0.01) {
                        $divergencias[] = [
                            'tipo' => 'preco',
                            'produto' => $itemOC['produto_nome'],
                            'esperado' => $itemOC['preco_unitario'],
                            'recebido' => $itemNF['preco_unitario']
                        ];
                    }
                    break;
                }
            }
            if (!$encontrado) {
                $divergencias[] = [
                    'tipo' => 'ausente_nf',
                    'produto' => $itemOC['produto_nome'],
                    'mensagem' => 'Produto da OC não encontrado na NF'
                ];
            }
        }
        return $divergencias;
    }
}
