<?php

$arquivo = 'entrada.txt';
$cep = null;

function organizaArquivoCidades($arquivo) {
    $linhas = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $faixas = [];


    foreach ($linhas as $linha) {
        if (trim($linha) === '--') {
            $proximaLinhaEhOCep = true;
            continue;
        }

        if (isset($proximaLinhaEhOCep) && $proximaLinhaEhOCep) {
            $cep = (int)trim($linha);
            break;
        }

        list($cidade, $inicio, $fim) = explode(',', $linha);
        $faixas[] = [
            'cidade' => $cidade,
            'inicio' => (int)$inicio,
            'fim' => (int)$fim
        ];
    }

    return $faixas;
}

function procurarCidade() {

    $faixas =  organizaArquivoCidades($arquivo);

    foreach ($faixas as $faixa) {
        if ($cep >= $faixa['inicio'] && $cep <= $faixa['fim']) {
            return $faixa['cidade'];
        }
    }
    return "não foi encontrada nenhuma faixa.";
}

echo procurarCidade();
