# UniRate Currency for ProcessWire

Live currency **exchange rates**, **conversion**, **supported-currency lists**, and
**VAT rates** for [ProcessWire](https://processwire.com), powered by the
[UniRate API](https://unirateapi.com).

- **Dependency-free** — uses PHP's bundled cURL and JSON extensions only. No Composer
  runtime packages are added to your site.
- **Cached** — results are stored through ProcessWire's `WireCache`, so the API is not
  hit on every page render.
- **Fails gracefully** — any API error is logged and the call returns `null` instead of
  throwing into a page render.

## Requirements

- ProcessWire 3.0+
- PHP 7.4+ with the `curl` and `json` extensions (both standard)
- A free UniRate API key — grab one at <https://unirateapi.com>

## Installation

**Via the modules directory / admin**

1. In the ProcessWire admin go to **Modules → Install → Add Module From Directory**
   and enter the class name `UniRateCurrency`, or install from URL:
   `https://github.com/UniRate-API/UniRateCurrency/archive/refs/heads/main.zip`
2. Click **Install**.
3. Open the module's config screen and paste your **UniRate API key**.

**Manually**

Copy this repository into `/site/modules/UniRateCurrency/`, then click
**Modules → Refresh** and install **UniRate Currency**.

**Via Composer**

```bash
composer require unirate-api/unirate-currency
```

## Usage

Get the module instance anywhere you have the ProcessWire API available
(templates, other modules, bootstrap scripts):

```php
$unirate = $modules->get('UniRateCurrency');

// Convert an amount
echo $unirate->convert(100, 'USD', 'EUR');   // 92.5

// Single exchange rate
echo $unirate->getRate('USD', 'GBP');        // 0.79

// Supported currency codes
$codes = $unirate->getCurrencies();          // ['USD', 'EUR', 'GBP', ...]

// VAT rate for a country (ISO-3166 alpha-2)
$vat = $unirate->getVatRates('DE');          // ['country' => 'DE', 'vat_data' => [...]]
echo $vat['vat_data']['vat_rate'];           // 19.0

// All VAT rates
$all = $unirate->getVatRates();              // ['vat_rates' => ['DE' => [...], ...]]
```

Each method returns `null` (and writes a line to the ProcessWire error log) if the
API request fails, so your templates can degrade gracefully:

```php
$price = $unirate->convert(100, 'USD', 'EUR');
echo $price !== null ? number_format($price, 2) . ' EUR' : 'Rate unavailable';
```

See [`examples/template-usage.php`](examples/template-usage.php) for a complete template.

## Configuration

The module config screen (**Modules → UniRate Currency**) exposes:

| Setting | Default | Notes |
|---|---|---|
| API key | — | Required. From <https://unirateapi.com>. |
| API base URL | `https://api.unirateapi.com` | Leave as default unless told otherwise. |
| Request timeout | `15` | Seconds. |
| Cache lifetime | `3600` | Seconds. `0` disables caching. |

## Methods

| Method | Returns |
|---|---|
| `getRate(string $from, string $to): ?float` | Exchange rate for the pair |
| `convert(float $amount, string $from, string $to): ?float` | Converted amount |
| `getCurrencies(): ?array` | List of supported currency codes |
| `getVatRates(?string $country = null): ?array` | VAT rates (all countries, or one) |

Only **free-tier** endpoints are exposed. UniRate's Pro-gated historical and
time-series endpoints are intentionally omitted so the module never surfaces a
`403` to a site visitor — use the full
[PHP client](https://github.com/UniRate-API/unirate-api-php) if you need those.

## Caching

Responses are cached through `WireCache` keyed on the call arguments, for
**Cache lifetime** seconds (default one hour). Clear the ProcessWire cache (or
set a shorter lifetime) if you need fresher rates.

## Related clients

Part of the UniRate client family — see also the official
[PHP](https://github.com/UniRate-API/unirate-api-php),
[Python](https://github.com/UniRate-API/unirate-api-python),
[Node](https://github.com/UniRate-API/unirate-api-nodejs), and
[Grav](https://github.com/UniRate-API/grav-unirate) packages.

## License

[MIT](LICENSE) © 2026 Unirate Team
