<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Support;

/**
 * Which right of withdrawal applies to this basket, and what the customer has
 * to have agreed to before it can be charged.
 *
 * ### Why a basket can carry two regimes at once
 *
 * They are different rights with different triggers:
 *
 * - **Goods** (§ 355, § 356 Abs. 2 Nr. 1 BGB). Fourteen days from receipt.
 *   Nothing is required of the customer at checkout beyond being informed —
 *   and asking them to "consent" to it would be worse than pointless, because
 *   the right is not theirs to give up.
 * - **Digital services** (§ 356 Abs. 4 BGB — and § 356 Abs. 5 for digital
 *   content). The right lapses on full performance, but only if the customer
 *   expressly asked for performance to begin inside the withdrawal period AND
 *   confirmed they knew that this costs them the right. Without both, TDS
 *   performs the service and the customer may still withdraw.
 *
 * So a basket with both needs both blocks shown, separately, and needs the
 * consent for the digital half only. That is the whole reason this is a class
 * rather than a boolean: the naive version — one checkbox, always required —
 * demands a consent that has no legal object for a basket of goods, which is
 * both wrong and the kind of wrong that looks like diligence.
 *
 * ### The wording travels with the order
 *
 * {@see \Tds\Ext\Shop\Domain\OrderRepository::open()} copies the text in
 * verbatim rather than referencing it. § 356 Abs. 4 BGB makes *the sentence the
 * customer agreed to* the provable thing, and that sentence will be edited over
 * the years. The constants here are the fallback for a request that sends none;
 * the checkout page sends what it actually displayed.
 */
final class Withdrawal
{
    /** Nothing in the basket is delivered — the digital-service regime alone. */
    public const DIGITAL = 'digital';
    /** Everything is delivered — the ordinary 14-day regime alone. */
    public const GOODS = 'goods';
    /** Both, and both blocks have to be shown. */
    public const MIXED = 'mixed';

    /**
     * @param bool $hasDigital  at least one line that is not shipped
     * @param bool $hasPhysical at least one line that is
     */
    public static function regime(bool $hasDigital, bool $hasPhysical): string
    {
        if ($hasDigital && $hasPhysical) {
            return self::MIXED;
        }
        return $hasPhysical ? self::GOODS : self::DIGITAL;
    }

    /**
     * Must the customer have ticked the early-performance box?
     *
     * True exactly when something in the basket is performed rather than
     * delivered. For a basket of pure goods this is FALSE, and the checkout
     * must not demand it — see the class doc.
     */
    public static function requiresConsent(string $regime): bool
    {
        return $regime === self::DIGITAL || $regime === self::MIXED;
    }

    /** The default wording, used when the page does not send its own. */
    public static function defaultText(string $regime): string
    {
        $digital = 'Ich verlange ausdrücklich, dass Sie vor Ende der Widerrufsfrist mit der '
            . 'Leistung beginnen. Mir ist bekannt, dass ich mein Widerrufsrecht mit '
            . 'vollständiger Erbringung der Leistung verliere.';

        return match ($regime) {
            self::DIGITAL => $digital,
            self::MIXED => $digital . ' Für die enthaltenen Waren bleibt mein Widerrufsrecht '
                . 'von vierzehn Tagen ab Erhalt davon unberührt.',
            // Nothing is agreed here, and nothing is asked. The stored text is
            // the INFORMATION that was given, which Art. 246a § 1 Abs. 2 EGBGB
            // requires before the order — not a consent, because there is none
            // to give.
            default => 'Sie haben das Recht, binnen vierzehn Tagen ab Erhalt der Ware ohne Angabe '
                . 'von Gründen diesen Vertrag zu widerrufen. Die Widerrufsbelehrung und das '
                . 'Muster-Widerrufsformular wurden mir vor der Bestellung zur Verfügung gestellt.',
        };
    }
}
