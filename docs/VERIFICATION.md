# DrillDeck verification

## Current environment

The project targets PHP 8.3+ and Node 22+. Run the checks below in a fresh environment before each release. The authoring environment is not a substitute for production acceptance testing.

The automated workflow covers PHP dependency installation, Laravel bootstrap, route listing, feature tests, and the frontend build with SQLite. The live MySQL environment and physical tablet workflows remain unverified until explicitly tested.

## Install and verify locally

Run from the project root:

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
# Configure MySQL in .env, or use a suitable test database.
php artisan migrate
php artisan route:list
php artisan test
npm run build
```

The composer.lock and package-lock.json dependency manifests are committed. Use composer install and npm ci for reproducible deployments.

## Authentication acceptance cases

1. Register a shop owner; confirm owner membership and six workflow columns.
2. Register a customer without shop name; confirm no shop is created.
3. Log in, log out, and confirm protected shop routes require authentication.
4. Request password reset; confirm reset notification and ability to submit a valid reset token.
5. Register or sign in using an invited employee email and accept their token.
6. Confirm customers cannot access employee orders or drilling records.

## Work order acceptance cases

1. Create and edit work order; change title, instructions, urgency and due date.
2. Assign employee belonging to shop; reject assignee from another shop.
3. Move ticket by drag/drop and status select; confirm single audit entry per actual transition.
4. Configure created/assigned/rush automation; confirm stage moves and audit records.
5. Pause a rule; confirm no automatic stage move.
6. Reject cross-shop reading, assignment and stage changes.
7. Check on phone and tablet.

## Known gaps

The application is still a scaffold until the above runtime checks pass. It also needs shop switching, full employee role lifecycle, ticket sort normalization, richer customer/drilling workflows, subscription billing, notifications, and production security review.
