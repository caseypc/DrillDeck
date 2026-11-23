import '../scss/app.scss';
import 'bootstrap';
import './shared/forms.js';

if(document.querySelector('[data-kanban-board], [data-move-select]')) import('./shop/kanban.js');
if(document.getElementById('workflow-sort')) import('./shop/workflow.js');
if(document.querySelector('[data-ball-form]')) import('./shop/ball-form.js');
if(document.querySelector('.fitting-diagram')) import('./shop/fitting-diagram.js');
if(document.querySelector('[data-catalog-list]')) import('./shop/catalog.js');
