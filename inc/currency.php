<?php
// Conversión/formato CLP↔USD/EUR — todo el cálculo interno sigue siempre en
// CLP, esto es solo para mostrar. Tasas fijas, no oficiales (mismo criterio
// que ya se usaba en currency.ts).
const CLP_PER_UNIT = ['CLP' => 1, 'USD' => 950, 'EUR' => 1020];

function format_price(int $clp, string $currency): string {
    $amount = $clp / CLP_PER_UNIT[$currency];
    $symbol = ['CLP' => '$', 'USD' => 'US$', 'EUR' => '€'][$currency];
    return $symbol . number_format(round($amount), 0, ',', '.');
}
