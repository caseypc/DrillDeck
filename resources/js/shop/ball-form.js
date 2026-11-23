for(const form of document.querySelectorAll('[data-ball-form]')){
    const brand=form.querySelector('[data-ball-brand]');
    const product=form.querySelector('[data-ball-product]');
    const syncProduct=()=>{
        const id=brand.selectedOptions[0]?.dataset.optionId || '';
        for(const option of product.options){
            if(!option.dataset.parent)continue;
            option.hidden=!!id && option.dataset.parent!==id;
            option.disabled=!!id && option.dataset.parent!==id;
        }
        if(product.selectedOptions[0]?.disabled)product.value='';
    };
    brand.addEventListener('change',syncProduct);
    syncProduct();

    for(const select of form.querySelectorAll('select[name]')){
        const custom=form.querySelector('[data-custom-for="'+select.name+'"]');
        if(!custom)continue;
        const sync=()=>{
            const show=select.value==='__other__';
            custom.classList.toggle('d-none',!show);
            custom.required=show;
            custom.disabled=!show;
        };
        select.addEventListener('change',sync);sync();
    }
}
