# Checkout, payment and invoicing

## Legal requirements that shaped the code

- **§ 312j Abs. 3 BGB, the order button.** It must read "Zahlungspflichtig bestellen", with the
  mandatory details directly above. Stripe's hosted button says "Bezahlen", so **our** `/kasse`
  page carries the details, the confirmation and the button. Pressing it creates the payment
  session; the provider only handles payment. Sending a visitor straight to the provider skips
  the declaration.
- **§ 3a Abs. 5 UStG, where we may sell.** An electronic service to an EU consumer elsewhere moves
  the place of supply and eventually needs OSS. `SHOP_ALLOWED_COUNTRIES` defaults to `DE`, checked
  on **our** server before the provider, so the refusal can be explained.
- **Two rights of withdrawal.**
  - Goods: 14 days from receipt (§ 355, § 356 Abs. 2 Nr. 1 BGB). Nothing is asked of the customer;
    a tick box would be consent with no legal object.
  - Services: the right lapses on full performance only with an express request to start early and
    an acknowledgement of the consequence (§ 356 Abs. 4 BGB). `POST /shop/checkout` refuses a basket
    with service lines without `withdrawalConsent: true`.
  - A mixed basket shows both blocks and asks consent for the service half only. The check is
    therefore **conditional** and runs after the basket is resolved; nothing is written before it.
    The rule is pure and lives in `Support\Withdrawal` (`WithdrawalTest`).
  - `shop_order.withdrawal_consent_text` stores the **wording**, not a flag, because the sentence
    will change over the years.

## Money

- Integer **cents** throughout; `vat_rate_bp` in **basis points** (1900 = 19 %).
- `OrderRepository::price()` rounds **the tax**, then adds. Deriving tax back out of a gross loses a
  cent on about a third of amounts, and the net figure feeds the VAT return.
- **Rounding is per line, then summed**; line figures appear on the invoice.
- Every amount is **frozen** into the order at purchase and never recomputed.
- **Prices always come from the database.** `sellableMany()` takes slug and quantity only. Quantity
  is capped at 99 per line; an unbounded quantity is an unbounded charge.
- **One place computes money:** `resolveBasket()` in `ShopModule`, used by both `POST /shop/quote`
  (basket page) and `POST /shop/checkout`. Two implementations would drift.

## Shipping

Shipping is an ancillary service and takes the VAT rate of the goods it delivers (Abschn. 3.10
UStAE). With 19 % and 7 % items in one parcel, the charge is split by net value and taxed in parts.
`Support\Shipping` does it; the last bucket absorbs the rounding remainder so the parts sum to the
charge (`ShippingTest`). A blanket 19 % is wrong for mixed baskets.

## Address and line snapshots

- Guest checkout: the address is a fact about **this order**, all-or-nothing. An incomplete address
  is refused, never stored.
- `shop_order_item.requires_shipping` is a snapshot like title and price; it decides which
  withdrawal regime applied to that line.

## Payment providers (`php/src/Payment/`)

`PaymentProvider` implementations behind a `PaymentRegistry`: `StripeProvider`,
`PayPalProvider`, `WeroProvider`.

- **Registered vs configured is the load-bearing distinction.** A provider is registered when its
  class exists and **offered** only when `isConfigured()` is true. `GET /shop/payment-methods`
  lists configured ones; `POST /shop/checkout` re-checks with `usable()`.
  `PaymentRegistry::get()` still returns an unconfigured provider, so its webhook answers 503
  rather than 404.
- **`WeroProvider` sits in the tree unfinished** and reports unconfigured, so it never reaches a
  customer. Don't make it optimistic; see `docs/wero-adapter.md`.
- **PayPal is not shaped like Stripe.** `checkout.session.completed` means money moved;
  `CHECKOUT.ORDER.APPROVED` doesn't. `PayPalProvider` captures on that webhook and reports paid
  only on `PAYMENT.CAPTURE.COMPLETED`. Capturing in the webhook (not on the customer's return)
  means someone who approves and closes the tab still pays. Hence `receiveWebhook`, not
  `parseWebhook`.
- **Orders are matched on our own token first** (Stripe metadata, PayPal `custom_id`). Provider ids
  aren't always in the event that matters.
- Columns `shop_order.payment_provider`, `provider_session_id`, `provider_payment_ref` replaced the
  Stripe-named ones. The old columns are still written for Stripe and read by nothing, for one
  release of rollback safety; a later migration drops them.

## Webhooks

- One endpoint per provider: `/shop/payment/{provider}/webhook`, outside `/content/shop` (no
  provider holds a site key).
- Stripe: HMAC over the **raw** body. PayPal: a round trip to its verification endpoint. Pass raw
  bytes through untouched; a re-encoded payload won't verify.
- `/shop/stripe/webhook` stays mounted and behaves identically, because it is the URL configured in
  the Stripe dashboard. Renaming a route a third party calls loses payments silently (Stripe retries
  into a 404 for three days). Remove it only after the dashboard is repointed and logs are quiet.
- `markPaid()` is idempotent via `WHERE status = 'pending'`. A verified but unhandled event answers
  200; anything else makes the provider retry forever.
- A missing webhook secret answers **503**, never 200, so an unconfigured host can't accept any POST
  as payment.

## Fulfilment

These are **services, not downloads**: no file, no signed URL, no download counter. A paid order
waits in `/shop/bestellungen` until someone does the work and marks it done
(`POST /shop/orders/{id}/fulfil`).

## Invoicing through Lexware

`Service\OrderInvoicing` invoices a paid order through Lexware Office
(`POST /shop/orders/{id}/invoice`; `Service\OrderInvoiceBuilder` builds the payload).

- **Lexware is the leading system.** It assigns the number, renders the PDF and keeps the archive.
  This module records what Lexware decided (`shop_order.invoice_*`) and never invents an invoice
  number (§ 14 UStG wants one unbroken sequence with one author).
- `LexwareClient` belongs to `tds-ext-lexware-pkg`, which may not be installed. It is resolved from
  the container by name, guarded by **`class_exists()`**, never `$c->has()` (which autowiring makes
  always true).
- Nothing in invoicing may throw at its caller.

## Referrals

The shop records who recommended a purchase. It keeps no commission logic; that belongs to
`tds-ext-referrals-pkg`, behind the contract's `Commerce\SaleEvents` (≥ 1.15).

- `POST /shop/checkout` takes `referral {code, via}` and `referredBy` (free text).
  `Support\OrderReferral::fromCheckout()` stores a code only when `resolveReferral()` knows
  it. An unknown code is dropped and never blocks the purchase.
- `shop_order.referral_code`, `referral_via` and `referred_by_note` are frozen like every
  other order fact. `byToken()` hides them from the customer view.
- The webhook calls `SaleEvents::paid()` on **every** PAID delivery, after invoicing, and
  `reversed()` on REFUNDED. Net is the goods net (sum of lines), never shipping.
- `GET /shop/referral/{code}` is browser-called (not site-key) and returns only the public
  display name for "Empfohlen von …".
