# Customer Bowling Ball and Drilling History

## Work order workflow

1. Open a ticket under /shop/orders/{order}. Select an existing customer ball or create and link a new one.
2. Prior drilling worksheets appear newest first, with their author, date, and recorded measurements.
3. Use the new drilling revision form to copy the latest sheet into a new draft. Previous versions remain unchanged.
4. Open any historical revision to view a read-only printable worksheet.
5. Use /shop/balls for all shop-owned ball records, including standalone customer balls.

## Customer profile and baseline fitting

- Each shop has a separate customer directory at /shop/customers. Profiles store contact details and notes, plus references to their balls and work orders.
- A customer can have multiple balls while maintaining one shared history of baseline hand-fitting revisions.
- Fitting history records handedness, grip style, spans, finger/thumb sizes, bridge, pitch notations, PAP, notes, who recorded it, and a monotonically allocated revision.
- New hand measurements create a new immutable fitting revision. Existing versions stay visible and printable.
- Ball drilling revisions may optionally reference a particular baseline fitting revision through fitting_spec_id, preserving the link even after a newer fitting is added.
- Customer portal work requests create a shop-specific customer profile tied to the authenticated user ID, not just a name.
- Existing customer balls and orders are not automatically merged by matching names. Staff can explicitly assign records to a selected profile with identity and cross-shop checks.
- A customer cannot see internal staff fitting records through the customer portal.

## Data relationships

- Customer balls represent shop-owned records of physical bowling balls. Each can optionally reference a registered customer and a manufacturer catalog model.
- Orders have a nullable customer_ball_id linking a work order to one ball.
- Each drilling specification is an append-only revision with an incrementing revision number per ball, an author, and an optional originating order.
- An existing unique constraint on customer_ball_id and revision protects against collisions; the revision service locks the parent ball row.
- Previous records remain compatible because new foreign keys are nullable.

## Access and history protection

- Only authenticated employees of the current shop can view ball inventory and drilling history.
- Ball and order IDs are scoped to the employee's shop.
- A registered customer order can only link a ball belonging to that user. Walk-in records currently match normalized customer names; the future customer directory will replace this temporary mechanism.
- Creating a ball directly from an order copies customer identity from that order.
- Unlinking a ball does not erase historical revisions or the originating order recorded on those revisions.
- No update or delete endpoints exist for previously saved revisions.

## Safety and remaining work

Recorded measurements are unverified and are not drilling instructions. A qualified technician must approve measurements before physical drilling. A work order currently holds at most one bowling ball. Fitting specification profiles, full pitch geometry, proper customer records, multi-ball orders, and electronic approval are still future work.
