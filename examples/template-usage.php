<?php

namespace ProcessWire;

/**
 * Example ProcessWire template showing the UniRate module in use.
 *
 * Drop this into a template file (e.g. /site/templates/basic-page.php) after
 * installing the module and entering your API key in the module's config screen.
 *
 * $modules, $wire, etc. are the usual ProcessWire API variables available in a
 * template context.
 */

/** @var UniRate $unirate */
$unirate = $modules->get('UniRate');

// Convert an amount.
$price = $unirate->convert(100, 'USD', 'EUR');
if ($price !== null) {
    echo "100 USD = " . number_format($price, 2) . " EUR\n";
}

// Single exchange rate.
$rate = $unirate->getRate('USD', 'GBP');
if ($rate !== null) {
    echo "1 USD = {$rate} GBP\n";
}

// Supported currency codes (handy for a <select> of currencies).
$codes = $unirate->getCurrencies();
if ($codes !== null) {
    echo count($codes) . " currencies supported\n";
}

// VAT rate for a country.
$vat = $unirate->getVatRates('DE');
if ($vat !== null) {
    echo "Germany VAT: " . $vat['vat_data']['vat_rate'] . "%\n";
}
