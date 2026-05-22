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
        $sql .= ' ORDER BY nf.data_emissao DESC';
        $q = $this->bd->prepare($sql); $q->execute($p);
        return $q->fetchAll();
    }
}
