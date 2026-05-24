const fs = require('fs');
const path = require('path');

const files = [
    'resources/views/bank-sampah/deposits/create.blade.php',
    'resources/views/bank-sampah/deposits/edit.blade.php',
    'resources/views/bank-sampah/sales/create.blade.php',
    'resources/views/bank-sampah/sales/edit.blade.php'
];

files.forEach(file => {
    let content = fs.readFileSync(file, 'utf8');
    
    // Determine title
    let title = "";
    if(file.includes('deposits/create')) title = 'Catat Setoran Sampah';
    if(file.includes('deposits/edit')) title = 'Edit Setoran Sampah';
    if(file.includes('sales/create')) title = 'Catat Penjualan';
    if(file.includes('sales/edit')) title = 'Edit Penjualan';

    // Replace top wrapper
    const topWrapperRegex = /<div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6">/g;
    content = content.replace(topWrapperRegex, `<x-form-card title="${title}"><div class="p-6">`);
    
    // Replace bottom wrapper
    const bottomRegex = /<\/form>\s*<\/div>\s*\{\{-- Category Picker Modal --\}\}/g;
    content = content.replace(bottomRegex, `</form></div></x-form-card>\n\n        {{-- Category Picker Modal --}}`);
    
    fs.writeFileSync(file, content);
    console.log("Updated", file);
});
