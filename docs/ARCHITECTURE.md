# DrillDeck architecture

Laravel 13, Fortify, Blade, Bootstrap 5, Sass, Font Awesome Free, SortableJS, shared MySQL database.

## Shop tenancy

- Staff membership is defined by shop_users; customer orders are linked by customer_user_id.
- Staff operations must scope every shop-owned record to the authenticated shop, including order access, assigned employee and workflow destination columns.
- Only shop owners and managers may alter workflow settings, automation rules, or invite employees.
- Staff invitations use a random token stored as SHA-256 and require a matching authenticated email to be accepted.
- The first-pass application selects the first employee shop. A current-shop switcher is required for correct multi-shop staff operations.
- Shared bowling_ball_models contains product catalog data, not customer-private drilling data.

## Work order model

Orders are business work tickets, not generic tasks. Each ticket has a shop, customer details, optional authenticated customer, optional assigned employee, due date, priority, and a single current workflow column.

OrderWorkflowService centralizes manual and event-triggered transitions inside database transactions and writes order_status_history entries when the stage actually changes. Automations are shop-owned rules:

- created: after a ticket is opened
- assigned: when an employee is assigned
- rush: when an order is first marked rush

One rule is allowed per event per shop. Rules can be paused/removed. Rule execution is nonrecursive; multiple events during a create/update can cause sequential transitions. Customer-visible status comes from the current workflow column.

All records currently belong to the first authenticated employee shop; this is a documented MVP limitation, not an approved multi-shop design.

## Remaining hardening

- Confirm Composer/npm dependency installation and tests.
- Commit dependency lockfiles.
- Normalize ordering and concurrent drag operations.
- Add customer and product edit interfaces and drilling specification revisions.
- Do not use free-form drilling measurements to generate production drilling instructions.
- Validate staff invitation email delivery in the configured mail transport.
- Add role-aware policies for additional staff features and shop switching.
- Add real-time notifications and richer event-driven automation only when domain events are defined.
