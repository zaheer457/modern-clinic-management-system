document.addEventListener('click',e=>{
 const btn=e.target.closest('[data-confirm]');
 if(btn && !confirm(btn.dataset.confirm)) e.preventDefault();
});
document.querySelectorAll('input[type=file]').forEach(i=>i.addEventListener('change',()=>{if(i.files[0] && i.files[0].size>3145728){alert('Image must be 3MB or smaller.');i.value='';}}));
