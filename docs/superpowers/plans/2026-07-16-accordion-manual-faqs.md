# FAQ Accordion Manual Entry Mode — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let editors enter FAQ items directly on the FAQ Accordion widget instead of only pulling from the page's `faqs` ACF repeater.

**Architecture:** Add a `Source` select (`acf` | `manual`, default `acf`) that conditionally reveals either the existing ACF notice or a new Elementor repeater. `render()` is restructured to normalize whichever source into a flat `$items` array, then run one shared markup loop — so the two sources differ only in where the array comes from.

**Tech Stack:** PHP, WordPress, Elementor Widget API (`\Elementor\Widget_Base`, `\Elementor\Repeater`, `Controls_Manager`), ACF (`have_rows` / `the_row` / `get_sub_field`).

**Spec:** `docs/superpowers/specs/2026-07-16-accordion-manual-faqs-design.md`

## Global Constraints

- All work is in one file: `inc/elementor-widgets/accordion-widget.php`. Do not modify `assets/sass/_accordion.scss`, the inline accordion script, or the ACF field group.
- Escape all output: `esc_html()` for questions, `wp_kses_post()` for answers, `esc_attr()` for element IDs, `esc_js()` for the widget ID passed into the inline script.
- Text domain is `text-domain` (matches the rest of this file — do not "fix" it here).
- Control conditions use Elementor's `'condition' => [ 'source' => '...' ]` array syntax.
- Use the `if ( ... ) : ... endif;` / `while ( ... ) : ... endwhile;` template syntax in markup blocks, matching the existing file.
- Do not compile assets — no SCSS/JS build output is involved in this change.
- Bump the theme version in `style.css` before the final commit (Task 3). Never mention Claude in commit messages.

## No Test Suite — How Verification Works

This theme has no PHPUnit, composer, or package.json. Every task is verified by:

1. `php -l inc/elementor-widgets/accordion-widget.php` → must print `No syntax errors detected`.
2. Scripted manual checks in the browser, written out per task.

Run browser checks against the Local site for `mppl-financial-dev`. You need a page that already uses the **FAQ Accordion** widget and has the ACF `faqs` repeater populated — find one before starting Task 1, and note its URL. If no such page exists, create a draft page, add the widget, and populate the page's FAQs field.

---

### Task 1: Restructure `render()` into normalize-then-render (no behavior change)

Pure refactor. The widget must render **byte-identical** output before and after, except that rows with an empty question no longer leave a gap in element IDs. This task adds no controls and no manual mode.

**Files:**
- Modify: `inc/elementor-widgets/accordion-widget.php:57-165` (the whole `render()` method)

**Interfaces:**
- Consumes: nothing from earlier tasks.
- Produces: `protected function get_acf_faq_items(): array` — returns a list of `[ 'question' => string, 'answer' => string ]`, empty-question rows already filtered out. Task 2 calls this and adds a sibling with the same return shape.

- [ ] **Step 1: Capture the current rendered output as a baseline**

Open the page using the widget in a normal browser tab (not the Elementor editor), View Source, and copy the full `<div class="accordion" ...>...</div>` block into a scratch file. You will diff against this in Step 4.

- [ ] **Step 2: Add the `get_acf_faq_items()` method**

Insert this method into the `Accordion_Widget` class, immediately **after** the closing brace of `register_controls()` and **before** `render()`:

```php
    /**
     * Read the page's ACF "faqs" repeater into a flat list.
     *
     * @return array List of [ 'question' => string, 'answer' => string ].
     */
    protected function get_acf_faq_items() {
        $items = [];

        $post_id = get_queried_object_id();
        if ( empty( $post_id ) ) {
            $post_id = get_the_ID();
        }

        if ( ! have_rows( 'faqs', $post_id ) ) {
            return $items;
        }

        while ( have_rows( 'faqs', $post_id ) ) : the_row();
            $question = get_sub_field( 'faq_question' );

            if ( ! $question ) {
                continue;
            }

            $items[] = [
                'question' => $question,
                'answer'   => get_sub_field( 'faq_answer' ),
            ];
        endwhile;

        return $items;
    }
```

- [ ] **Step 3: Replace the body of `render()`**

Replace the entire existing `render()` method (from `protected function render() {` through its closing brace, currently lines 57-165) with this. The `<script>` block is unchanged from the original — it is reproduced in full here so you do not have to reconstruct it:

```php
    protected function render() {
        $open_first = $this->get_settings_for_display( 'open_first_item' ) === 'yes';
        $widget_id  = 'accordion-' . $this->get_id();

        $items = $this->get_acf_faq_items();

        if ( empty( $items ) ) {
            return;
        }
        ?>
        <div class="accordion" id="<?php echo esc_attr( $widget_id ); ?>">
            <?php foreach ( $items as $index => $item ) :
                $item_id  = $widget_id . '-' . $index;
                $btn_id   = $item_id . '-btn';
                $panel_id = $item_id . '-panel';
                $is_open  = $open_first && $index === 0;
            ?>
            <div class="accordion__item">
                <h3 class="accordion__header">
                    <button
                        class="accordion__button"
                        id="<?php echo esc_attr( $btn_id ); ?>"
                        type="button"
                        aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
                        aria-controls="<?php echo esc_attr( $panel_id ); ?>"
                    >
                        <span class="accordion__title"><?php echo esc_html( $item['question'] ); ?></span>
                        <span class="accordion__icon" aria-hidden="true">
                            <svg class="accordion__icon-chevron" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            <svg class="accordion__icon-close" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </span>
                    </button>
                </h3>
                <div
                    class="accordion__panel<?php echo $is_open ? ' is-open' : ''; ?>"
                    id="<?php echo esc_attr( $panel_id ); ?>"
                    role="region"
                    aria-labelledby="<?php echo esc_attr( $btn_id ); ?>"
                    <?php echo ! $is_open ? 'hidden' : ''; ?>
                >
                    <div class="accordion__content">
                        <?php echo wp_kses_post( $item['answer'] ); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <script>
        (function() {
            var acc = document.getElementById('<?php echo esc_js( $widget_id ); ?>');
            if ( ! acc ) return;

            function setOpen( btn, panel, open ) {
                btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
                panel.classList.toggle( 'is-open', open );

                if ( open ) {
                    panel.removeAttribute( 'hidden' );
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                } else {
                    panel.style.maxHeight = '0';
                    panel.addEventListener( 'transitionend', function handler() {
                        if ( btn.getAttribute( 'aria-expanded' ) === 'false' ) {
                            panel.setAttribute( 'hidden', '' );
                        }
                        panel.removeEventListener( 'transitionend', handler );
                    } );
                }
            }

            acc.querySelectorAll( '.accordion__button' ).forEach( function( btn ) {
                var panel = document.getElementById( btn.getAttribute( 'aria-controls' ) );
                if ( ! panel ) return;

                // Set initial max-height for open items
                if ( btn.getAttribute( 'aria-expanded' ) === 'true' ) {
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                }

                btn.addEventListener( 'click', function() {
                    var isOpen = btn.getAttribute( 'aria-expanded' ) === 'true';

                    // Close any other open item first
                    acc.querySelectorAll( '.accordion__button[aria-expanded="true"]' ).forEach( function( openBtn ) {
                        if ( openBtn !== btn ) {
                            var openPanel = document.getElementById( openBtn.getAttribute( 'aria-controls' ) );
                            if ( openPanel ) setOpen( openBtn, openPanel, false );
                        }
                    } );

                    setOpen( btn, panel, ! isOpen );
                } );
            } );
        })();
        </script>
        <?php
    }
```

Note what changed and what did not: `$index` is now the `foreach` key rather than a hand-incremented counter; `$faq_question` / `$faq_answer` became `$item['question']` / `$item['answer']`; the `if ( ! $faq_question ) { $index++; continue; }` guard is gone because filtering happens in `get_acf_faq_items()`. Markup, classes, ARIA attributes, and the script are untouched.

- [ ] **Step 4: Verify syntax and identical output**

Run: `php -l inc/elementor-widgets/accordion-widget.php`
Expected: `No syntax errors detected in inc/elementor-widgets/accordion-widget.php`

Then reload the page from Step 1, View Source, and diff the `<div class="accordion">` block against your baseline.
Expected: identical, including every generated `id` attribute. (If the page's FAQs field had a row with an empty question, the baseline will have an ID gap — e.g. `-0`, `-2` — and the new output will be contiguous `-0`, `-1`. That single difference is the intended fix; anything else is a regression.)

Also confirm in the browser that clicking each header still opens it and closes the previously open one, and that the first item starts open.

- [ ] **Step 5: Commit**

```bash
git add inc/elementor-widgets/accordion-widget.php
git commit -m "refactor accordion widget to normalize FAQ items before rendering"
```

---

### Task 2: Add the Source control and manual entry mode

**Files:**
- Modify: `inc/elementor-widgets/accordion-widget.php` — `register_controls()`, plus one line in `render()`

**Interfaces:**
- Consumes: `get_acf_faq_items()` from Task 1 — returns a list of `[ 'question' => string, 'answer' => string ]`.
- Produces: `protected function get_manual_faq_items( $settings ): array` — same return shape, read from the `manual_faqs` repeater setting. Control names introduced: `source` (values `acf` | `manual`), `manual_faqs` (repeater; per-row fields `faq_question`, `faq_answer`).

- [ ] **Step 1: Add the `source` control**

In `register_controls()`, insert this **immediately after** the `start_controls_section( 'content_section', ... )` call and **before** the existing `faqs_notice` control, so Source appears first in the panel:

```php
        $this->add_control( 'source', [
            'label'   => __( 'Source', 'text-domain' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'acf',
            'options' => [
                'acf'    => __( 'Page FAQs (ACF)', 'text-domain' ),
                'manual' => __( 'Manual Entry', 'text-domain' ),
            ],
        ] );
```

`'default' => 'acf'` is what keeps existing widget instances rendering as they do today — they have no saved `source` value, and Elementor falls back to the control default.

- [ ] **Step 2: Condition the existing notice on the ACF source**

Add a `condition` key to the existing `faqs_notice` control so the ACF explanation only shows when it is true. The control becomes:

```php
        $this->add_control( 'faqs_notice', [
            'type'            => \Elementor\Controls_Manager::RAW_HTML,
            'raw'             => __( 'Items are pulled from the "FAQs" ACF repeater field on the current page.', 'text-domain' ),
            'content_classes' => 'elementor-descriptor',
            'condition'       => [ 'source' => 'acf' ],
        ] );
```

- [ ] **Step 3: Add the manual FAQs repeater**

Insert this **after** the `faqs_notice` control and **before** the `open_first_item` control:

```php
        $repeater = new \Elementor\Repeater();

        $repeater->add_control( 'faq_question', [
            'label'       => __( 'Question', 'text-domain' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label_block' => true,
            'default'     => '',
        ] );

        $repeater->add_control( 'faq_answer', [
            'label'   => __( 'Answer', 'text-domain' ),
            'type'    => \Elementor\Controls_Manager::WYSIWYG,
            'default' => '',
        ] );

        $this->add_control( 'manual_faqs', [
            'label'       => __( 'FAQ Items', 'text-domain' ),
            'type'        => \Elementor\Controls_Manager::REPEATER,
            'fields'      => $repeater->get_controls(),
            'title_field' => '{{{ faq_question }}}',
            'condition'   => [ 'source' => 'manual' ],
            'default'     => [],
        ] );
```

`title_field` makes each collapsed row show its question text instead of "Item #1".

- [ ] **Step 4: Add the `get_manual_faq_items()` method**

Insert this immediately **after** `get_acf_faq_items()` and **before** `render()`:

```php
    /**
     * Read the widget's manual FAQ repeater into a flat list.
     *
     * @param array $settings Widget settings from get_settings_for_display().
     * @return array List of [ 'question' => string, 'answer' => string ].
     */
    protected function get_manual_faq_items( $settings ) {
        $items = [];
        $rows  = ! empty( $settings['manual_faqs'] ) ? $settings['manual_faqs'] : [];

        foreach ( $rows as $row ) {
            $question = ! empty( $row['faq_question'] ) ? $row['faq_question'] : '';

            if ( ! $question ) {
                continue;
            }

            $items[] = [
                'question' => $question,
                'answer'   => ! empty( $row['faq_answer'] ) ? $row['faq_answer'] : '',
            ];
        }

        return $items;
    }
```

- [ ] **Step 5: Dispatch on the selected source in `render()`**

In `render()`, replace these two lines:

```php
        $open_first = $this->get_settings_for_display( 'open_first_item' ) === 'yes';
        $widget_id  = 'accordion-' . $this->get_id();

        $items = $this->get_acf_faq_items();
```

with:

```php
        $settings   = $this->get_settings_for_display();
        $open_first = $settings['open_first_item'] === 'yes';
        $widget_id  = 'accordion-' . $this->get_id();

        $items = 'manual' === $settings['source']
            ? $this->get_manual_faq_items( $settings )
            : $this->get_acf_faq_items();
```

Everything below the `if ( empty( $items ) )` guard is unchanged — both sources feed the same markup loop.

- [ ] **Step 6: Verify syntax**

Run: `php -l inc/elementor-widgets/accordion-widget.php`
Expected: `No syntax errors detected in inc/elementor-widgets/accordion-widget.php`

- [ ] **Step 7: Verify in the Elementor editor and on the front end**

Work through each of these on the page from Task 1:

1. **Existing instance untouched.** Load the page front end before opening the editor. Expected: renders exactly as it did after Task 1 — the saved widget picks up `source = acf` by default.
2. **Control conditions.** Open the widget in the Elementor editor. Expected: Source shows "Page FAQs (ACF)"; the ACF notice is visible; no FAQ Items repeater. Switch Source to "Manual Entry" — expected: notice disappears, FAQ Items repeater appears. Switch back — expected: reverses.
3. **Manual items render.** With Source on "Manual Entry", add two items. Give the first a rich-text answer containing a bold word, a link, and a bulleted list. Update, then load the front end. Expected: both questions render; the first answer shows bold, link, and list markup intact — matching how an ACF answer renders.
4. **Open-first applies to manual.** Expected: the first manual item starts open with `aria-expanded="true"`; clicking the second opens it and closes the first.
5. **Empty question is skipped.** Add a third item and leave its Question blank. Update, reload. Expected: only the two complete items render, no empty accordion row, and the rendered IDs are contiguous (`-0`, `-1`).
6. **Two accordions, one page.** Drop a second FAQ Accordion on the page set to Manual Entry with its own items. Expected: each accordion's IDs are namespaced by its own widget ID, and opening an item in one does not affect the other.

If any check fails, fix it and re-run the full list before committing.

- [ ] **Step 8: Commit**

```bash
git add inc/elementor-widgets/accordion-widget.php
git commit -m "add manual FAQ entry mode to accordion widget"
```

---

### Task 3: Bump the theme version

**Files:**
- Modify: `style.css:8`

- [ ] **Step 1: Bump the version**

In `style.css`, change the theme header line:

```
Version: 1.0.5
```

to:

```
Version: 1.0.6
```

- [ ] **Step 2: Confirm the working tree is clean apart from the bump**

Run: `git status --short`
Expected: only ` M style.css`. If `inc/elementor-widgets/accordion-widget.php` still shows as modified, Task 2 was not committed — commit it first.

- [ ] **Step 3: Commit**

```bash
git add style.css
git commit -m "bump theme version to 1.0.6"
```

---

## Spec Coverage

| Spec requirement | Task |
|---|---|
| `source` SELECT, default `acf` | 2 (Step 1) |
| `faqs_notice` conditioned on `source = acf` | 2 (Step 2) |
| `manual_faqs` REPEATER conditioned on `source = manual`, with `title_field` | 2 (Step 3) |
| `faq_question` TEXT / `faq_answer` WYSIWYG | 2 (Step 3) |
| `open_first_item` unchanged, applies to both sources | 1 (Step 3), verified 2 (Step 7.4) |
| Backward compatibility for saved instances | 2 (Step 1), verified 2 (Step 7.1) |
| Normalize-then-render, early return when empty | 1 (Steps 2-3) |
| Contiguous element IDs / empty-question fix | 1 (Step 4), verified 2 (Step 7.5) |
| Escaping identical across sources | 1 (Step 3) — single shared markup loop |
| No SCSS / script / ACF field group changes | Global Constraints |
| Version bump before GitHub | 3 |
