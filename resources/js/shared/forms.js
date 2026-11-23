// Form-submit indicators are progressive enhancement; browser validation still runs.
for(const form of document.querySelectorAll('form')){
    form.addEventListener('submit',event=>{
        if(event.defaultPrevented)return;
        queueMicrotask(()=>{
            if(event.defaultPrevented||!form.checkValidity())return;
            for(const button of form.querySelectorAll('button[type="submit"],button:not([type])')){
                if(button.dataset.busy==='1')continue;
                button.dataset.busy='1';
                button.dataset.label=button.innerHTML;
                button.insertAdjacentHTML('afterbegin','<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>');
                button.disabled=true;
            }
        });
    });
}
window.addEventListener('pageshow',()=>{
    for(const button of document.querySelectorAll('button[data-busy="1"]')){
        button.innerHTML=button.dataset.label;
        button.disabled=false;
        delete button.dataset.busy;
    }
});
const formatPhone=(value)=>{
    const raw=value.trim();
    if(raw.startsWith('+')&&!raw.startsWith('+1'))return raw;
    let digits=raw.replace(/\D/g,'');
    if(digits.length===11&&digits[0]==='1')digits=digits.slice(1);
    if(digits.length===10)return '('+digits.slice(0,3)+') '+digits.slice(3,6)+'-'+digits.slice(6);
    return raw;
};
for(const input of document.querySelectorAll('input[data-phone]')){
    input.addEventListener('blur',()=>{input.value=formatPhone(input.value);});
}
for(const input of document.querySelectorAll('input[data-autocomplete]'))input.autocomplete='on';
