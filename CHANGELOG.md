# Changelog

## 0.1.0 — 2026-10-03

- Initial release.
- Configurable service module exposing `getRate`, `convert`, `getCurrencies`, and
  `getVatRates` over the UniRate API free-tier endpoints.
- Dependency-free (bundled cURL + JSON extensions); results cached via `WireCache`;
  API errors logged and degraded to `null`.
