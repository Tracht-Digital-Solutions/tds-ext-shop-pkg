<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Payment;

/**
 * The providers this shop knows about, and which of them can actually be used.
 *
 * The distinction is the point. A provider is REGISTERED as soon as its class
 * exists; it is OFFERED only once {@see PaymentProvider::isConfigured()} says
 * yes. That single gate is what lets an unfinished adapter sit in the tree
 * without endangering anything: `/shop/payment-methods` lists
 * {@see configured()}, the checkout re-checks before starting a payment, and a
 * hand-crafted request naming an unconfigured provider gets the same 422 as one
 * naming a provider that does not exist.
 *
 * Order matters and is deliberate: it is the order the checkout renders, and
 * the first entry is what a customer with no preference will take.
 */
final class PaymentRegistry
{
    /** @var array<string,PaymentProvider> */
    private array $providers = [];

    /** @param iterable<PaymentProvider> $providers in display order */
    public function __construct(iterable $providers = [])
    {
        foreach ($providers as $provider) {
            $this->providers[$provider->id()] = $provider;
        }
    }

    /** Every registered provider, configured or not. For diagnostics, not for a menu. */
    public function all(): array
    {
        return $this->providers;
    }

    /**
     * The provider behind an id, or null.
     *
     * Returns unconfigured providers too, because a WEBHOOK has to reach one:
     * an endpoint that 404s when its secret is missing is indistinguishable
     * from a wrong URL, and the provider's own 503 says the useful thing. The
     * checkout does its own `isConfigured()` check before starting a payment.
     */
    public function get(string $id): ?PaymentProvider
    {
        return $this->providers[$id] ?? null;
    }

    /**
     * What may be offered to a customer, in display order.
     *
     * @return list<array{id:string,label:string}>
     */
    public function configured(): array
    {
        $out = [];
        foreach ($this->providers as $provider) {
            if ($provider->isConfigured()) {
                $out[] = ['id' => $provider->id(), 'label' => $provider->label()];
            }
        }
        return $out;
    }

    /**
     * The provider to start a payment with, or null if it may not be used.
     *
     * One method for "exists" and "is usable" so no caller can check the first
     * and forget the second.
     */
    public function usable(string $id): ?PaymentProvider
    {
        $provider = $this->get($id);
        return $provider !== null && $provider->isConfigured() ? $provider : null;
    }

    /** The id the checkout defaults to when the request names none. */
    public function defaultId(): ?string
    {
        return $this->configured()[0]['id'] ?? null;
    }
}
