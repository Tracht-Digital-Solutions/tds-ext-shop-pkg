<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Domain;

use PDO;

/** Advertising slots. Small on purpose — the product selection lives in {@see ProductRepository}. */
final class PlacementRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** One active slot by key, or null. An inactive slot reads as absent. */
    public function active(string $key): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM shop_placement WHERE `key` = :key AND active = 1 LIMIT 1',
        );
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    /** Every slot, for the panel's manager. */
    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM shop_placement ORDER BY surface ASC, `key` ASC');
        return $stmt === false ? [] : ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    /** @param array<string,mixed> $fields */
    public function update(string $key, array $fields): bool
    {
        $allowed = ['label', 'surface', 'strategy', 'selector', 'heading_de', 'heading_en', 'max_items', 'active'];
        $set = [];
        $args = ['key' => $key];
        foreach ($fields as $name => $value) {
            if (!in_array($name, $allowed, true)) {
                continue;
            }
            $set[] = "`$name` = :$name";
            $args[$name] = $value;
        }
        if ($set === []) {
            return false;
        }
        $stmt = $this->pdo->prepare(
            'UPDATE shop_placement SET ' . implode(', ', $set) . ' WHERE `key` = :key',
        );
        return $stmt->execute($args);
    }

    /**
     * Replace a slot's pinned products with the given order.
     *
     * Delete-then-insert inside a transaction, because the alternative — diffing
     * the existing rows — has to get `position` right for the moved ones too,
     * and a half-applied reorder is worse than a rejected one.
     *
     * @param list<int> $productIds in display order
     */
    public function setItems(int $placementId, array $productIds): void
    {
        $this->pdo->beginTransaction();
        try {
            $del = $this->pdo->prepare('DELETE FROM shop_placement_item WHERE placement_id = :pid');
            $del->execute(['pid' => $placementId]);

            $ins = $this->pdo->prepare(
                'INSERT INTO shop_placement_item (placement_id, product_id, position)'
                . ' VALUES (:pid, :product, :pos)',
            );
            $pos = 0;
            foreach ($productIds as $productId) {
                $ins->execute(['pid' => $placementId, 'product' => (int) $productId, 'pos' => $pos++]);
            }
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
