import './inactivity';

const INACTIVITY_MINUTES = 15;
let timer;

function resetTimer() {
    clearTimeout(timer);
    timer = setTimeout(async () => {
        // Cerrar sesión via POST al logout
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/cerrar-sesion-ahora';

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = document.querySelector('meta[name="csrf-token"]').content;

        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    }, INACTIVITY_MINUTES * 60 * 1000);
}

['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(event => {
    document.addEventListener(event, resetTimer, { passive: true });
});

resetTimer(); // Iniciar al cargar