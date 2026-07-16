# FAQ Accordion — Manual Entry Mode

**Date:** 2026-07-16
**File:** `inc/elementor-widgets/accordion-widget.php`

## Problem

The FAQ Accordion widget renders only from the `faqs` ACF repeater on the current page. Any page needing FAQs that differ from that field — or a second accordion on a page that already uses the field — cannot use the widget. Editors need a way to enter FAQ items directly on the widget.

## Approach

Add an explicit `Source` control with two options: the existing ACF repeater, or manual entry via an Elementor repeater on the widget itself. The sources are mutually exclusive; the editor chooses deliberately rather than relying on implicit fallback.

## Controls

All changes are inside the existing `content_section`.

| Control | Type | Notes |
|---|---|---|
| `source` | `SELECT` | Options: `acf` → "Page FAQs (ACF)", `manual` → "Manual Entry". Default `acf`. |
| `faqs_notice` | `RAW_HTML` | Existing control; gains `'condition' => [ 'source' => 'acf' ]`. |
| `manual_faqs` | `REPEATER` | `'condition' => [ 'source' => 'manual' ]`. `title_field => '{{{ faq_question }}}'`. |
| `open_first_item` | `SWITCHER` | Unchanged; applies to both sources. |

Repeater fields on `manual_faqs`:

- `faq_question` — `TEXT`, label "Question".
- `faq_answer` — `WYSIWYG`, label "Answer".

Field names intentionally match the ACF sub-field names so both sources normalize identically.

### Backward compatibility

Widget instances saved before this change have no `source` value stored. Elementor returns the control default for missing keys, so those instances resolve to `acf` and render exactly as they do today. No migration needed.

## Render

`render()` is restructured into two steps.

**1. Normalize.** Build a flat `$items` array of `[ 'question' => string, 'answer' => string ]` from the selected source:

- `acf` — resolve the post ID via `get_queried_object_id()` with a `get_the_ID()` fallback (existing logic), then walk `have_rows( 'faqs', $post_id )` / `the_row()` / `get_sub_field()`.
- `manual` — read the `manual_faqs` settings array.

Entries with an empty question are skipped during normalization, not during markup. Return early if `$items` is empty.

**2. Render.** One shared loop over `$items` emits the accordion markup, ARIA wiring, and the inline script. This is the current markup, unchanged.

### Fixed as part of this work

The current loop increments `$index` on skipped rows, so a row with an empty question leaves a gap in the generated element IDs. Normalizing before rendering makes indices contiguous. Element IDs remain namespaced by `$this->get_id()`, so multiple accordions on one page still do not collide.

## Escaping

Unchanged, and identical for both sources:

- Questions — `esc_html()`.
- Answers — `wp_kses_post()`. The Elementor WYSIWYG control returns raw HTML, the same as the ACF WYSIWYG sub-field.
- Element IDs — `esc_attr()`; `esc_js()` for the ID passed into the inline script.

## Out of scope

- `assets/sass/_accordion.scss` — no changes. The markup contract is identical for both sources.
- The inline accordion script — no changes, for the same reason.
- The ACF `faqs` field group definition.

## Verification

- An existing page using the widget renders unchanged with no editor action (default `source` resolves to `acf`).
- Switching to Manual Entry shows the repeater and hides the ACF notice; switching back reverses it.
- A manual item with a rich-text answer renders links and lists the same as an ACF answer.
- Open/close behavior, `aria-expanded`, and `open_first_item` behave the same for both sources.
- A manual item with an empty question is skipped without breaking the items after it.
- Two accordions on one page operate independently.
