<?php

namespace ProcessWire;

require_once __DIR__ . '/UniRateException.php';
require_once __DIR__ . '/UniRateClient.php';

/**
 * UniRate — live currency exchange rates, conversion, supported currencies and VAT
 * rates for ProcessWire, powered by the UniRate API (https://unirateapi.com).
 *
 * Dependency-free (uses PHP's bundled cURL + JSON extensions only). Results are
 * cached through ProcessWire's WireCache so the API is not hit on every request,
 * and any API error is logged and degraded to null rather than thrown into a
 * page render.
 *
 * Usage from a template:
 *
 *     $unirate = $modules->get('UniRate');
 *     echo $unirate->convert(100, 'USD', 'EUR');   // 92.5
 *     echo $unirate->getRate('USD', 'GBP');        // 0.79
 *     $codes = $unirate->getCurrencies();          // ['USD', 'EUR', ...]
 *     $vat   = $unirate->getVatRates('DE');        // ['country' => 'DE', ...]
 *
 * @property string $api_key
 * @property string $base_url
 * @property int $timeout
 * @property int $cache_lifetime
 */
class UniRate extends WireData implements Module, ConfigurableModule
{
    /**
     * @return array<string,mixed>
     */
    public static function getModuleInfo(): array
    {
        return [
            'title' => 'UniRate Currency',
            'version' => '0.1.0',
            'summary' => 'Live currency exchange rates, conversion, supported currencies and VAT rates via the UniRate API. Dependency-free; results cached through WireCache.',
            'author' => 'UniRate',
            'href' => 'https://github.com/UniRate-API/processwire-unirate',
            'icon' => 'exchange',
            'singular' => true,
            'autoload' => false,
            'requires' => ['ProcessWire>=3.0.0', 'PHP>=7.4.0'],
        ];
    }

    public function __construct()
    {
        parent::__construct();
        // Config defaults — overridden by the values saved in the module config screen.
        $this->set('api_key', '');
        $this->set('base_url', 'https://api.unirateapi.com');
        $this->set('timeout', 15);
        $this->set('cache_lifetime', 3600);
    }

    /**
     * Current exchange rate for a currency pair, or null if the request failed.
     */
    public function getRate(string $from, string $to): ?float
    {
        return $this->cached("rate:$from:$to", function () use ($from, $to) {
            return $this->client()->getRate($from, $to);
        });
    }

    /**
     * Convert an amount between two currencies, or null if the request failed.
     */
    public function convert(float $amount, string $from, string $to): ?float
    {
        return $this->cached("convert:$amount:$from:$to", function () use ($amount, $from, $to) {
            return $this->client()->convert($amount, $from, $to);
        });
    }

    /**
     * Supported currency codes, or null if the request failed.
     *
     * @return string[]|null
     */
    public function getCurrencies(): ?array
    {
        return $this->cached('currencies', function () {
            return $this->client()->getCurrencies();
        });
    }

    /**
     * VAT rates for all countries, or a single country when a code is given;
     * null if the request failed.
     *
     * @return array<string,mixed>|null
     */
    public function getVatRates(?string $country = null): ?array
    {
        return $this->cached('vat:' . ($country ?? 'all'), function () use ($country) {
            return $this->client()->getVatRates($country);
        });
    }

    private function client(): UniRateClient
    {
        return new UniRateClient(
            (string) $this->api_key,
            (string) $this->base_url,
            (int) $this->timeout
        );
    }

    /**
     * Fetch through WireCache; on any API error log a warning and return null so
     * templates degrade gracefully instead of throwing.
     *
     * @return mixed
     */
    private function cached(string $key, callable $callback)
    {
        $cache = $this->wire()->cache;
        $id = 'unirate_' . md5($key);

        $cached = $cache->get($id);
        if ($cached !== null && $cached !== '') {
            return $cached;
        }

        try {
            $result = $callback();
        } catch (UniRateException $e) {
            $this->wire()->log->error('UniRate: ' . $e->getMessage());

            return null;
        }

        $lifetime = (int) $this->cache_lifetime;
        if ($lifetime > 0) {
            $cache->save($id, $result, $lifetime);
        }

        return $result;
    }

    /**
     * Module configuration screen.
     *
     * @param InputfieldWrapper $inputfields
     */
    public function getModuleConfigInputfields(InputfieldWrapper $inputfields): InputfieldWrapper
    {
        $modules = $this->wire()->modules;

        /** @var InputfieldText $key */
        $key = $modules->get('InputfieldText');
        $key->attr('name', 'api_key');
        $key->label = $this->_('UniRate API key');
        $key->description = $this->_('Create a free key at https://unirateapi.com. Required for every request.');
        $key->attr('value', $this->api_key);
        $key->columnWidth = 100;
        $inputfields->add($key);

        /** @var InputfieldText $base */
        $base = $modules->get('InputfieldText');
        $base->attr('name', 'base_url');
        $base->label = $this->_('API base URL');
        $base->description = $this->_('Leave as the default unless you have been told otherwise.');
        $base->attr('value', $this->base_url);
        $base->columnWidth = 50;
        $inputfields->add($base);

        /** @var InputfieldInteger $timeout */
        $timeout = $modules->get('InputfieldInteger');
        $timeout->attr('name', 'timeout');
        $timeout->label = $this->_('Request timeout (seconds)');
        $timeout->attr('value', (int) $this->timeout);
        $timeout->columnWidth = 25;
        $inputfields->add($timeout);

        /** @var InputfieldInteger $cache */
        $cache = $modules->get('InputfieldInteger');
        $cache->attr('name', 'cache_lifetime');
        $cache->label = $this->_('Cache lifetime (seconds)');
        $cache->description = $this->_('How long results are cached. 0 disables caching.');
        $cache->attr('value', (int) $this->cache_lifetime);
        $cache->columnWidth = 25;
        $inputfields->add($cache);

        return $inputfields;
    }
}
