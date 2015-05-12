<?php

function gerarNossoNumero()
{
    $carteira = zeroFill('25', 2);
 
    $sequencial = zeroFill('7', 11);

    // Calcula o digito veriricador da soma da carteira com o número sequencial
    $calculo = calculoDvNossoNumero($carteira . $sequencial);

    return "{$carteira}/{$sequencial}-{$calculo}";
}

function calculoDvNossoNumero($sequencia)
{
    $soma = 0;
    $peso = 2;

    for ($i = strlen($sequencia) - 1; $i >= 0; $i--) {
        $produto = (int)(substr($sequencia, $i, 1)) * $peso;
        $soma += $produto;
        $peso = ($peso == 7) ? 2 : $peso + 1;
    }

    $resto = $soma % 11;

    if ($resto == 0)
        return '0';
    else if ($resto == 1)
        return 'P';
    else
        return (11 - $resto);
}

function zeroFill($valor, $digitos)
{
    return str_pad($valor, $digitos, '0', STR_PAD_LEFT);
}

$intro = '<div id="meu-id">Hello World</div>';
$regex = '#\<div id="meu-id"\>(.+?)\<\/div\>#s';

preg_match($regex, $intro, $matches);
$match = $matches[1];

echo $match;