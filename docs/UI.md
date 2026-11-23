# DrillDeck UI

## Basis
includes a vertical sidebar, workspace identity/switcher area, grouped navigation, persistent top search bar, user menu, and responsive offcanvas navigation. This implementation follows its composition and design tokens while using the repository's existing Bootstrap 5, Blade, Sass, and Font Awesome dependencies.

## Blade structure
- `layouts/shop.blade.php` — authenticated staff application shell.
- `layouts/public.blade.php` — public, authentication and customer pages.
- `layouts/partials/head.blade.php` — shared meta, Vite, theme initializer.
- `layouts/partials/theme.blade.php` — automatic browser-preference color mode.
- `layouts/partials/brand.blade.php` — shared brand link.
- `layouts/partials/sidebar.blade.php` — grouped, role-aware staff navigation.
- `layouts/partials/topbar.blade.php` — persistent search (currently searches orders via existing GET /shop/orders?q=...), user dropdown.
- `layouts/partials/alerts.blade.php` — common flash and validation errors.
- Existing `shop/`, `public/`, `customer/`, `auth/` views retain domain-specific content and forms.

## Sass structure
- `app.scss` — single Vite entrypoint.
- `abstracts/_variables.scss` — Bootstrap Sass overrides.
- `base/_theme.scss` — semantic colors and browser-driven dark-mode overrides.
- `layout/_shell.scss` — offcanvas sidebar, top search, workspace header and page layout.
- `components/_ui.scss` — stats, data tables, list rows, form controls.
- `pages/_workspaces.scss` — established Kanban, drilling, photo, fitting and catalog visuals.
- `pages/_public.scss` — public/customer layout styling.

## Functional boundaries
- Search is **not** a fake control: the header submits `q` to the existing orders index search. Future cross-entity search can replace its action without replacing the input.
- The workspace block displays the current shop; shop-switching is not yet implemented. No nonfunctional switcher button.
- Theme follows `prefers-color-scheme` (including live changes); no preference persistence or optional override.

## Validation checklist
- [ ] `npm ci && npm run build`
- [ ] `php artisan view:cache`
- [ ] `php artisan test`
- [ ] Check 375px, 768px, 1024px, 1440px widths.
- [ ] Navigate sidebar by keyboard and mobile offcanvas.
- [ ] Enter search query in header and confirm order result filtering.
- [ ] Verify both live light/dark transitions, form contrast, select controls and table readability.
- [ ] Verify every staff workflow plus public and customer pages.
- [ ] Confirm staff-only links remain permission gated and no cross-shop data leaks.