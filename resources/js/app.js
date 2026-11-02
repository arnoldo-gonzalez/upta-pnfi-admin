

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

window.printReport = function(url) {
    let iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = 'none';
    iframe.style.visibility = 'hidden';
    iframe.src = url;

    document.body.appendChild(iframe);

    iframe.onload = function() {
        try {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        } catch (e) {
            console.error("Error al imprimir:", e);
        }
        
        // Destruimos el iframe después de un tiempo para limpiar la memoria
        setTimeout(() => {
            document.body.removeChild(iframe);
        }, 3000); 
    };
};