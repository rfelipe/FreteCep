<?php

function organizaArquivo($arquivo) {
    $linhas = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    $faixas = [];
    $custoLinhas = [];
    $ceps = [];
    $modo = 'faixas';

    foreach ($linhas as $linha) {
        if (trim($linha) === '--') {
            if ($modo === 'faixas') {
                $modo = 'custos';
                continue;
            } elseif ($modo === 'custos') {
                $modo = 'ceps';
                continue;
            }
        }

        if ($modo === 'faixas') {
            $partes = explode(',', $linha);
            if (count($partes) === 3) {
                list($cidade, $inicio, $fim) = $partes;
                $faixas[] = [
                    'cidade' => $cidade,
                    'inicio' => (int)$inicio,
                    'fim' => (int)$fim
                ];
            }
        } elseif ($modo === 'custos') {
            $partes = explode(',', $linha);
            if (count($partes) === 3) {
                list($origem, $destino, $custo) = $partes;
                $custoLinhas[] = [
                    'origem' => $origem,
                    'destino' => $destino,
                    'custo' => (float)$custo
                ];
            }
        } elseif ($modo === 'ceps') {
            $ceps = array_map('intval', explode(',', $linha));
        }
    }

    return [$faixas, $custoLinhas, $ceps];
}

function cepParaCidade($cep, $faixas) {
    foreach ($faixas as $faixa) {
        if ($cep >= $faixa['inicio'] && $cep <= $faixa['fim']) {
            return $faixa['cidade'];
        }
    }
    return null;
}

function construirGrafo($conexoes) {
    $grafo = [];

    foreach ($conexoes as $conexao) {
        $grafo[$conexao['origem']][$conexao['destino']] = $conexao['custo'];
        $grafo[$conexao['destino']][$conexao['origem']] = $conexao['custo'];
    }

    return $grafo;
}

function dijkstra($grafo, $inicio, $fim) {
    $distancia = [];
    $visitados = [];
    $anterior = [];

    foreach ($grafo as $cidade => $_) {
        $distancia[$cidade] = INF;
        $anterior[$cidade] = null;
    }

    $distancia[$inicio] = 0;

    while (!empty($distancia)) {
        $cidadeAtual = array_search(min($distancia), $distancia);
        if ($cidadeAtual === $fim) break;

        foreach ($grafo[$cidadeAtual] as $vizinho => $custo) {
            if (isset($visitados[$vizinho])) continue;

            $novaDist = $distancia[$cidadeAtual] + $custo;
            if ($novaDist < $distancia[$vizinho]) {
                $distancia[$vizinho] = $novaDist;
                $anterior[$vizinho] = $cidadeAtual;
            }
        }

        $visitados[$cidadeAtual] = true;
        unset($distancia[$cidadeAtual]);
    }

    $rota = [];
    $cidade = $fim;
    while ($cidade !== null) {
        array_unshift($rota, $cidade);
        $cidade = $anterior[$cidade];
    }

    return [
        'rota' => $rota,
        'custo' => $distancia[$fim] ?? INF
    ];
}


list($faixas, $conexoes, $ceps) = organizaArquivo('data/entrada.txt');

$origemCep = $ceps[0];
$destinoCep = $ceps[1];

$origemCidade = cepParaCidade($origemCep, $faixas);
$destinoCidade = cepParaCidade($destinoCep, $faixas);

if (!$origemCidade || !$destinoCidade) {
    echo "Uma ou ambas as cidades não foram encontradas.\n";
    exit;
}

$grafo = construirGrafo($conexoes);
$resultado = dijkstra($grafo, $origemCidade, $destinoCidade);

if ($resultado['custo'] === INF) {
    echo "Não foi possível encontrar uma rota {$origemCidade} e {$destinoCidade}.\n";
} else {
    echo "Rota mais barata de $origemCidade para $destinoCidade:\n";
    echo implode(' → ', $resultado['rota']) . "\n";
    echo "Custo total: " . number_format($resultado['custo'], 2) . "\n";
}
