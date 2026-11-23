import Sortable from 'sortablejs';

const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

async function saveMove(url, data) {
    const response = await fetch(url, {
        method: 'PATCH',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
        },
        body: JSON.stringify(data),
    });

    if (!response.ok) {
        throw new Error('Unable to move ticket');
    }
}

document.querySelectorAll('[data-column-list]').forEach((list) => {
    Sortable.create(list, {
        group: 'drilldeck-orders',
        animation: 160,
        draggable: '[data-order-id]',
        handle: '.ticket-handle',
        delayOnTouchOnly: true,
        delay: 170,
        touchStartThreshold: 5,
        onEnd: async (event) => {
            const card = event.item;
            const originalParent = event.from;
            const originalSibling = event.oldIndex;
            try {
                await saveMove(card.dataset.moveUrl, {
                    workflow_column_id: Number(event.to.dataset.columnList),
                    sort_order: event.newIndex,
                });
                location.reload();
            } catch {
                const remaining = originalParent.querySelectorAll('[data-order-id]');
                if (originalSibling >= remaining.length) originalParent.appendChild(card);
                else originalParent.insertBefore(card, remaining[originalSibling]);
                alert('The move could not be saved. Please try again.');
            }
        },
    });
});

// On small touchscreens, a native select is more reliable than drag-and-drop.
document.querySelectorAll('[data-move-select]').forEach((select) => {
    select.addEventListener('change', async () => {
        const original = select.dataset.current;
        select.disabled = true;
        try {
            await saveMove(select.dataset.moveUrl, {
                workflow_column_id: Number(select.value),
            });
            location.reload();
        } catch {
            select.value = original;
            select.disabled = false;
            alert('Status update failed.');
        }
    });
});
