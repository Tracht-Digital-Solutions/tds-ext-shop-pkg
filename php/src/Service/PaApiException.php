<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Service;

/**
 * A failed Product Advertising API call.
 *
 * The distinction that matters is **permanent versus temporary**, because the
 * two demand opposite behaviour and the sync has no person to ask:
 *
 * - **Permanent** — the credentials are wrong, or Amazon has withdrawn API
 *   access because qualifying sales stopped. Retrying cannot help, and
 *   hammering a revoked account is exactly what the licence objects to. The
 *   sync stops and the panel says why.
 * - **Temporary** — throttling or a network blip. Back off and try later.
 *
 * Getting this backwards is expensive in one direction only: treating a
 * permanent failure as temporary produces an endless retry loop against an
 * account that is already in trouble.
 */
final class PaApiException extends \RuntimeException
{
    public function __construct(
        string $reason,
        public readonly int $status,
    ) {
        parent::__construct("PA-API: {$reason} (HTTP {$status})");
    }

    /** Retrying will not help until a human changes something. */
    public function isPermanent(): bool
    {
        if ($this->status === 401 || $this->status === 403) {
            return true;
        }
        foreach (['AccessDenied', 'InvalidAssociate', 'InvalidSignature', 'InvalidPartnerTag'] as $code) {
            if (str_contains($this->getMessage(), $code)) {
                return true;
            }
        }
        return false;
    }

    /** Slow down; the account is fine. */
    public function isThrottled(): bool
    {
        return $this->status === 429 || str_contains($this->getMessage(), 'TooManyRequests');
    }
}
