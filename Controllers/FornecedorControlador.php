<?php namespace Controllers; use Config\Auxiliares; use Models\FornecedorModelo;
class FornecedorControlador extends BaseController {
    private FornecedorModelo $m;
    public function __construct() { $this->m = new FornecedorModelo(); }

    public function listar(): void {
        Auxiliares::exigirPerfil('administrador', 'cadastrador', 'comprador', 'gerente', 'usuario', 'contabilidade');
        $filtros = $_GET;
        $fornecedores = $this->m->listarComFiltros($filtros);

        if (isset($_GET['busca_modal'])) {
            $this->json(true, '', $fornecedores);
            return;
        }

        $matrizes = $this->m->listarMatrizes();
        // Carregar categorias para o formulário (RF05/RF08)
        $bd = \Config\BancoDados::obterInstancia()->obterConexao();
        $categorias = $bd->query("SELECT id, nome FROM categorias ORDER BY nome")->fetchAll();
        $this->renderizar('cadastros/fornecedores', compact('fornecedores','filtros','matrizes','categorias'));
    }
    public function dados(): void { 
        Auxiliares::exigirPerfil('administrador', 'cadastrador', 'comprador', 'gerente', 'usuario', 'contabilidade'); 
        $r = $this->m->buscarPorId((int)($_GET['id'] ?? 0)); 
        $r ? $this->json(true,'',$r) : $this->json(false,'Não encontrado.'); 
    }
    public function consultarCodigo(): void {
        $codigo = trim($_GET['codigo'] ?? '');
        if (empty($codigo)) { $this->json(false, 'Código inválido.'); return; }
        
        $matrizApenas = isset($_GET['matriz']) && $_GET['matriz'] === '1';
        
        if ($matrizApenas) {
            $matriz = $this->m->buscarMatrizPorCodigo($codigo);
            if ($matriz) {
                $this->json(true, '', $matriz);
            } else {
                $this->json(false, 'Matriz não encontrada ou inativa.');
            }
        } else {
            $bd = \Config\BancoDados::obterInstancia()->obterConexao();
            $q = $bd->prepare("SELECT id, razao_social as nome FROM fornecedores WHERE codigo = :codigo AND situacao = 'ativo'");
            $q->execute([':codigo' => $codigo]);
            $res = $q->fetch(\PDO::FETCH_ASSOC);
            if ($res) {
                $this->json(true, '', $res);
            } else {
                $this->json(false, 'Fornecedor não encontrado ou inativo.');
            }
        }
    }
    public function salvar(): void {
        Auxiliares::exigirPerfil('administrador','cadastrador');
        $resp = Auxiliares::usuarioLogado(); $id   = (int)($_POST['id'] ?? 0);
        $dados = array_intersect_key($_POST, array_flip([
            'razao_social','nome_fantasia','cnpj','inscricao_estadual',
            'logradouro','numero','complemento','bairro','cep',
            'email','contato','responsavel','tipo','matriz_id'
        ]));
        // RF05/RF08: categorias N:N
        $dados['categorias'] = $_POST['categorias'] ?? [];
        
        if (empty($dados['razao_social'])) { $this->json(false,'Razão social obrigatória.'); return; }
        if (empty($dados['cnpj'])) { $this->json(false,'CNPJ obrigatório.'); return; }
        
        if (!validar_cnpj($dados['cnpj'])) {
            $this->json(false, 'CNPJ inválido.');
            return;
        }

        $dados['tipo'] = $dados['tipo'] ?? 'matriz';
        $dados['matriz_id'] = !empty($dados['matriz_id']) ? (int)$dados['matriz_id'] : null;

        if ($dados['tipo'] === 'filial' && empty($dados['matriz_id'])) {
            $this->json(false, 'Uma filial deve possuir uma matriz vinculada.');
            return;
        }

        // Se for cadastrado como matriz, limpa matriz_id
        if ($dados['tipo'] === 'matriz') {
            $dados['matriz_id'] = null;
        }

        // Tratar localidade (País, Estado, Cidade)
        $dadosLocalidade = [
            'pais_id' => !empty($_POST['pais_id']) ? (int)$_POST['pais_id'] : null,
            'pais_nome' => $_POST['nome_pais'] ?? null,
            'estado_id' => !empty($_POST['estado_id']) ? (int)$_POST['estado_id'] : null,
            'estado_nome' => $_POST['nome_estado'] ?? null,
            'estado_sigla' => $_POST['sigla_estado'] ?? null,
            'cidade_id' => !empty($_POST['cidade_id']) ? (int)$_POST['cidade_id'] : null,
            'cidade_nome' => $_POST['nome_cidade'] ?? null,
        ];

        $cidadeId = $this->m->garantirLocalidade($dadosLocalidade);
        $dados['cidade_id'] = $cidadeId > 0 ? $cidadeId : null;

        try {
            if ($id === 0) { 
                $novoId = $this->m->cadastrar($dados, $resp['id']);
                $this->json(true,'Fornecedor cadastrado.',['id'=>$novoId]); 
            } else { 
                // Impedir que o fornecedor seja matriz de si mesmo
                if ($dados['tipo'] === 'filial' && $dados['matriz_id'] === $id) {
                    $this->json(false, 'O fornecedor não pode ser filial de si mesmo.');
                    return;
                }
                $ok=$this->m->atualizar($id,$dados,$resp['id']); 
                $this->json($ok,$ok?'Atualizado.':'Erro.'); 
            }
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), '1062')) {
                $this->json(false, 'Já existe um fornecedor cadastrado com este CNPJ.');
            } else {
                $this->json(false, 'Erro no banco de dados: ' . $e->getMessage());
            }
        }
    }
    public function inativar(): void {
        Auxiliares::exigirPerfil('administrador','cadastrador');
        $usuario = Auxiliares::usuarioLogado();
        $ok = $this->m->inativar((int)($_POST['id']??0), (int)$usuario['id']);
        $this->json($ok, $ok ? 'Inativado.' : 'Erro.');
    }

    public function reativar(): void {
        Auxiliares::exigirPerfil('administrador','cadastrador');
        $usuario = Auxiliares::usuarioLogado();
        $ok = $this->m->reativar((int)($_POST['id']??0), (int)$usuario['id']);
        $this->json($ok, $ok ? 'Reativado.' : 'Erro.');
    }

    public function consultarCnpj(): void {
        Auxiliares::exigirPerfil('administrador', 'cadastrador');
        $cnpj = preg_replace('/\D/', '', $_GET['cnpj'] ?? '');
        if (strlen($cnpj) !== 14) {
            $this->json(false, 'CNPJ inválido. O CNPJ deve conter 14 dígitos numéricos.');
            return;
        }

        $url = "https://publica.cnpj.ws/cnpj/" . $cnpj;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if ($data) {
                $this->json(true, 'CNPJ consultado com sucesso.', $data);
                return;
            }
        } elseif ($httpCode === 404) {
            $this->json(false, 'CNPJ não encontrado na base da Receita Federal.');
            return;
        } elseif ($httpCode === 429) {
            $this->json(false, 'Limite de requisições excedido. Tente novamente mais tarde.');
            return;
        }

        $this->json(false, 'Não foi possível consultar o CNPJ no momento. Tente novamente ou digite manualmente.');
    }



    public function exportar(): void {
        Auxiliares::exigirPerfil('administrador', 'cadastrador', 'comprador', 'gerente', 'usuario', 'contabilidade');
        $filtros = $_GET;
        $fornecedores = $this->m->listarComFiltros($filtros);

        $cabecalhos = ['ID', 'Razão Social', 'CNPJ', 'Email', 'Contato', 'Tipo', 'Matriz', 'Situação', 'Criado Em'];
        $dadosCsv = [];
        foreach ($fornecedores as $f) {
            $dadosCsv[] = [
                $f['id'],
                $f['razao_social'],
                $f['cnpj'],
                $f['email'],
                $f['contato'],
                ucfirst($f['tipo']),
                $f['nome_matriz'] ?? '-',
                ucfirst($f['situacao']),
                date('d/m/Y H:i', strtotime($f['criado_em']))
            ];
        }

        Auxiliares::gerarCSV('fornecedores', $cabecalhos, $dadosCsv);
    }
}

