<?php
namespace App\Controllers;
class MigrateController extends Controller {
    public function index() {
        \ = \Config\Database::obterInstancia();
        try { \->exec("ALTER TABLE solicitacao_itens ADD COLUMN status VARCHAR(30) DEFAULT 'aberto'"); echo "Ok1\n"; } catch(\Exception \){ echo \->getMessage()."\n"; }
        try { \->exec("ALTER TABLE ordem_compra_itens ADD COLUMN status VARCHAR(30) DEFAULT 'aberto'"); echo "Ok2\n"; } catch(\Exception \){ echo \->getMessage()."\n"; }
        try { \->exec("ALTER TABLE cotacao_itens ADD COLUMN solicitacao_item_id INT UNSIGNED NULL"); echo "Ok3\n"; } catch(\Exception \){ echo \->getMessage()."\n"; }
        try { \->exec("ALTER TABLE ordem_compra_itens ADD COLUMN solicitacao_item_id INT UNSIGNED NULL"); echo "Ok4\n"; } catch(\Exception \){ echo \->getMessage()."\n"; }
    }
}
