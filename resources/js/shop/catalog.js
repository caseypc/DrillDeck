import Sortable from 'sortablejs';

const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

for (const list of document.querySelectorAll('[data-catalog-list]')) {
    const kind = list.dataset.catalogKind;
    const status = document.querySelector('[data-catalog-status="' + kind + '"]');
    let saved = Array.from(list.querySelectorAll('[data-catalog-option-id]'),
        (node) => Number(node.dataset.catalogOptionId));
    let saving = false;
    let sortable;

    const writeStatus = (message) => { if (status) status.textContent = message; };
    const restore = () => {
        const nodes = new Map(Array.from(list.querySelectorAll('[data-catalog-option-id]'),
            (node) => [Number(node.dataset.catalogOptionId), node]));
        for (const id of saved) list.appendChild(nodes.get(id));
    };

    sortable = Sortable.create(list, {
        animation: 160,
        handle: '.catalog-drag-handle',
        draggable: '[data-catalog-option-id]',
        delayOnTouchOnly: true,
        delay: 150,
        touchStartThreshold: 5,
        onEnd: async () => {
            const ids = Array.from(list.querySelectorAll('[data-catalog-option-id]'),
                (node) => Number(node.dataset.catalogOptionId));

            if (saving || ids.every((id, index) => id === saved[index])) return;
            saving = true;
            sortable.option('disabled', true);
            writeStatus('Saving order…');

            try {
                const response = await fetch(list.dataset.reorderUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ kind, option_ids: ids }),
                });
                if (!response.ok) throw new Error('Save failed');
                saved = ids;
                writeStatus('Saved');
            } catch {
                restore();
                writeStatus('Could not save. Order restored — refresh and try again.');
            } finally {
                saving = false;
                sortable.option('disabled', false);
            }
        },
    });
}

const kindSelect = document.querySelector('#catalog-kind');
const manufacturerField = document.querySelector('[data-catalog-new-parent]');
const parentSelect = document.querySelector('#catalog-parent-new');
const toggleManufacturer = () => {
    if (!kindSelect || !manufacturerField || !parentSelect) return;
    const isProduct = kindSelect.value === 'ball_name';
    manufacturerField.classList.toggle('d-none', !isProduct);
    parentSelect.disabled = !isProduct;
    parentSelect.required = isProduct;
};
kindSelect?.addEventListener('change', toggleManufacturer);
toggleManufacturer();
