<?php

function accessNavigate($nivel=Null){
    if($nivel == 1){
        include('Views/templates/nav_user.php');
    }else if($nivel == 2){
        include('Views/templates/nav_admin.php');
    }else{
        include('Views/templates/nav.php');
    }
}

function view($viewName, $data = [])
{
    $viewPath = "Views/{$viewName}.php";

    if (file_exists($viewPath)) {
        // Extrai as variáveis do array $data para dentro da view
        extract($data);
        include $viewPath;
    } else {
        echo "Página não encontrada!";
    }
}

function base_url($path = '') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8050';
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '';
    if ($scriptName === '/' || $scriptName === '\\' || $scriptName === '.') {
        $scriptName = '';
    }
    $url = rtrim($protocol . "://" . $host . $scriptName, '/');

    $path = trim($path, '/');
    return $path === '' ? $url : $url . '/' . $path;
}

function msg($texto, $tipo = 'success'){
    $alertType = "alert-{$tipo}";
    if($tipo == 'danger'){
        $icone = '<i class="bi bi-exclamation-triangle-fill"></i>';
    }
    else{
        $icone = '<i class="bi bi-check-circle-fill"></i>';
    }
    
    return '
        <div class="alert '.$alertType.'" role="alert">
        '.$icone.' '.$texto.' 
        </div>
        ';
}


function validar_cnpj($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '', (string)$cnpj);
    if (strlen($cnpj) !== 14) return false;
    if (preg_match('/(\d)\1{13}/', $cnpj)) return false;
    for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
        $soma += $cnpj[$i] * $j;
        $j = ($j == 2) ? 9 : $j - 1;
    }
    $resto = $soma % 11;
    if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) return false;
    for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
        $soma += $cnpj[$i] * $j;
        $j = ($j == 2) ? 9 : $j - 1;
    }
    $resto = $soma % 11;
    return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
}
?>
