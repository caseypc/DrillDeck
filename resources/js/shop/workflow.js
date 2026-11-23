import Sortable from 'sortablejs';

const list=document.getElementById('workflow-sort');
const status=document.getElementById('column-save-status');
const csrf=document.querySelector('meta[name="csrf-token"]')?.content;
if (list && status) {
    let last=[...list.querySelectorAll('[data-column-id]')].map(el=>el.dataset.columnId);
    const tell=(message)=>{status.textContent=message;};
    Sortable.create(list,{
        animation:160,handle:'.column-drag-handle',delayOnTouchOnly:true,delay:120,
        async onEnd(){
            const sorted=[...list.querySelectorAll('[data-column-id]')].map(el=>el.dataset.columnId);
            if(sorted.every((id,i)=>id===last[i]))return;
            tell('Saving order…');
            try {
                const response=await fetch(list.dataset.reorderUrl,{
                    method:'POST',credentials:'same-origin',
                    headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','Content-Type':'application/json'},
                    body:JSON.stringify({column_ids:sorted.map(Number)}),
                });
                if(!response.ok)throw new Error('Unable to save column order');
                last=sorted;tell('Saved ✓');
            }catch{
                const nodes=new Map([...list.querySelectorAll('[data-column-id]')].map(node=>[node.dataset.columnId,node]));
                for(const id of last)list.appendChild(nodes.get(id));
                tell('Could not save — order restored.');
            }
        },
    });
}
