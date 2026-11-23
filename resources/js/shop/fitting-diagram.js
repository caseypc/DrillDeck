for(const panel of document.querySelectorAll('.fitting-diagram')){
    const form=panel.closest('.fitting-workspace')?.querySelector('form');
    if(!form)continue;
    const sync=()=>{
        for(const label of panel.querySelectorAll('[data-fit-label]')){
            const input=form.elements.namedItem(label.dataset.fitLabel);
            label.textContent=(input?.value||'—').slice(0,22);
        }
    };
    form.addEventListener('input',sync);form.addEventListener('change',sync);sync();
}
