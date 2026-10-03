<?php

namespace ProcessWire;

/**
 * Raised when a UniRate API request fails (auth, rate limit, bad currency, transport, or decode).
 *
 * The HTTP status is carried as the exception code (0 for transport/decode errors).
 */
class UniRateException extends \RuntimeException
{
}
