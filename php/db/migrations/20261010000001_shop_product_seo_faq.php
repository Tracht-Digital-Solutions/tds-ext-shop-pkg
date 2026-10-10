<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * The fields a product page needs to stand on its own in search and in AI
 * answers, per language — and the same for a category page.
 *
 * ### Per translation
 *
 * - `meta_title`: the `<title>` when the visible heading is too long or too
 *   plain for a result line. Null falls back to the title.
 * - `summary`: the "Kurz gesagt" answer — two or three sentences an assistant
 *   can quote as they stand. Separate from the teaser, which sells; this
 *   answers.
 * - `facts`: JSON list of `{label, value}` (Dauer, Umfang, Ergebnis …). The one
 *   part of a product an answer engine extracts most reliably is a short list
 *   of named values.
 * - `faq`: JSON list of `{q, a}`, rendered visibly AND as FAQPage. Never only
 *   as markup — structured data for questions nobody can see is a guideline
 *   violation, not an optimisation.
 *
 * JSON in `text` columns rather than `json`: the values are only ever read and
 * written whole, and a `json` column would buy validation the repository
 * already does, at the price of MariaDB/MySQL behaving differently on it.
 *
 * ### Per category
 *
 * `intro_*` and `faq_*`: until now both lived in the shop site's source
 * (`categoryCopy.ts`) for two hand-picked slugs, so every other category page
 * shipped without either.
 */
final class ShopProductSeoFaq extends AbstractMigration
{
    public function change(): void
    {
        $this->table('shop_product_translation')
            ->addColumn('meta_title', 'string', ['limit' => 70, 'null' => true, 'after' => 'meta_description'])
            ->addColumn('summary', 'string', ['limit' => 400, 'null' => true, 'after' => 'meta_title'])
            ->addColumn('facts', 'text', ['null' => true, 'after' => 'summary'])
            ->addColumn('faq', 'text', ['null' => true, 'after' => 'facts'])
            ->update();

        $this->table('shop_category')
            ->addColumn('intro_de', 'text', ['null' => true, 'after' => 'name_en'])
            ->addColumn('intro_en', 'text', ['null' => true, 'after' => 'intro_de'])
            ->addColumn('faq_de', 'text', ['null' => true, 'after' => 'intro_en'])
            ->addColumn('faq_en', 'text', ['null' => true, 'after' => 'faq_de'])
            ->update();
    }
}
