# Finishing the Wero adapter

`php/src/Payment/WeroProvider.php` is a seat, not an implementation. This is
what has to go into it once a payment service provider has been chosen, and
what must *not* change around it.

## Why it is unfinished on purpose

Wero is an account-to-account scheme on SEPA Instant, run by the European
Payments Initiative. A merchant does not integrate it directly — acceptance
comes through a PSP, and each one exposes it differently. Choosing that PSP is
a contract decision, and it had not been made when the payment abstraction went
in.

Leaving the seat empty would have meant discovering later which assumptions in
`PaymentProvider` Wero breaks, with a live shop already built on them. Writing
the seat first exercises the registry, the routing, the settings and the
checkout's method list against a provider that answers every question except
the two that need the contract.

## The safety property, and how not to break it

`isConfigured()` returning `false` is the *only* thing keeping Wero out of the
shop, and it is enough:

- `GET /shop/payment-methods` lists `PaymentRegistry::configured()`, so Wero
  never appears in the checkout.
- `POST /shop/checkout` calls `PaymentRegistry::usable()`, so a hand-crafted
  request naming `wero` gets a 503 rather than starting something that cannot
  finish.
- `PaymentRegistry::get()` *does* return it, so the webhook route reaches it and
  answers 503 instead of 404 — "we cannot verify this right now" rather than
  "this endpoint does not exist".

So: **do not make `isConfigured()` optimistic while the rest is incomplete.**
The moment it returns `true`, Wero is offered to customers.

## What to implement

Three methods, and nothing else in the shop should need to change.

### 1. `isConfigured()`

Extend to the PSP's actual credential set. The rule from `PayPalProvider`
applies: if a credential is needed to *confirm* a payment, its absence must make
the provider unconfigured. A method that can start a payment it can never
confirm is worse than one that is simply absent — the customer pays and the
order sits at `pending` forever.

### 2. `start(PaymentRequest $r): PaymentHandoff`

Create a payment at the PSP and return where to send the customer.

- Amount: `$r->grossCents` (minor units) or `$r->amountDecimal()` — most
  European A2A APIs want the decimal string. Never re-derive it from a float.
- Merchant reference: **`$r->token`**. This is the field the webhook match runs
  on; see below.
- Return URLs: `$r->successUrl` and `$r->cancelUrl`, unchanged.
- `PaymentHandoff` takes the redirect URL and the PSP's id for the attempt. The
  caller writes that id to `shop_order.provider_session_id` — do not write it
  yourself, `start()` must not mutate the order.
- Throw `PaymentNotConfigured` without credentials, `PaymentFailed` on any
  transport or API error. `HttpJson::expectJson()` already produces the latter.

### 3. `receiveWebhook(string $rawBody, array $headers): ?PaymentEvent`

- Verify the PSP's signature over the **raw body**. It is passed in untouched
  for exactly this reason; a parsed-and-re-encoded payload will not verify under
  any HMAC scheme.
- `$headers` arrive lower-cased — the route does that once so no adapter has to
  guess how the PSP capitalises them.
- Throw `PaymentNotConfigured` when the signing secret is absent. **Fail
  closed.** An endpoint that accepts unverifiable webhooks is a way to mark any
  order paid.
- Throw `WebhookNotVerified` when the signature does not check out, and say
  nothing else. An endpoint that explains why a forgery was rejected is an
  oracle for producing one that is not.
- Map the PSP's terminal states onto `PaymentEvent::PAID` and
  `PaymentEvent::REFUNDED`. Return `null` for a verified event you do not act
  on — the route answers 200, or the PSP retries it forever.
- Fill `orderToken` from the merchant reference you sent in `start()`. It is
  preferred over the PSP's own id when the order is matched, because it is the
  identifier we control.

## Two things that are Wero's, not the PSP's

**There is no chargeback.** Settlement is a real-time credit transfer and it is
final. A refund is a *fresh transfer the merchant initiates*, not a reversal of
the original. So the refund path has to be an actual implementation — there is
no "reversal" event to map, and `PaymentEvent::REFUNDED` will only ever arrive
because we asked for it.

**Settlement is near-instant, including outside banking hours.** The gap between
"customer approved" and "money arrived" that PayPal fills with a capture step
does not exist here. If the PSP still models an authorise/capture split, it is
the PSP's abstraction and not Wero's — read their docs rather than assuming
either shape.

## Settings

Already wired, in the `shop` namespace with env fallbacks
(`ShopModule::setting()`):

| Setting | Env | Secret |
|---|---|---|
| `wero_psp` | `SHOP_WERO_PSP` | no |
| `wero_api_key` | `SHOP_WERO_API_KEY` | yes |
| `wero_webhook_secret` | `SHOP_WERO_WEBHOOK_SECRET` | yes |

Add to that set if the PSP needs more; the fields are rendered by
`islands/ShopSettings.tsx`.

## The webhook URL to register with the PSP

```
https://api.tracht-digital.de/shop/payment/wero/webhook
```

It already routes and already answers 503. Register it before the adapter is
finished if the PSP wants it during onboarding — an unverifiable event is
refused, never accepted.

## Tests to write alongside

Mirror `php/tests/PaymentTest.php`'s Stripe block: a genuine signature, a
tampered body, a foreign secret, a replay outside the tolerance window, an empty
secret, and a malformed header. Those six are what decide whether a stranger can
mark an order paid, and they are cheap to write against a pure verifier — so
keep the signature check pure and static the way `WebhookVerifier` is.
