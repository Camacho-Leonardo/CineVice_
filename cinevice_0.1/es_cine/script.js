document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const suggestions = document.getElementById('suggestions');
    const mensajeError = document.getElementById('mensaje-error');
    const contenido = document.getElementById('contenido');
    const generoDropdown = document.getElementById('generoDropdown');

    // Buscar por texto ingresado
    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim().toLowerCase();
        if (!query) {
            suggestions.innerHTML = '';
            mostrarTodasLasPeliculas();
            return;
        }

        fetch(`busqueda.php?q=${encodeURIComponent(query)}`)
            .then(res => res.text())
            .then(data => {
                if (data.startsWith('FILTRAR:')) {
                    contenido.innerHTML = data.replace('FILTRAR:', '');
                } else if (data.trim()) {
                    suggestions.innerHTML = data;
                    mensajeError.classList.remove('visible');
                } else {
                    suggestions.innerHTML = '';
                    mensajeError.classList.add('visible');
                    setTimeout(() => mensajeError.classList.remove('visible'), 3000);
                }
            });
    });

    // Buscar por clic en sugerencia
    suggestions.addEventListener('click', (e) => {
        const target = e.target.closest('.suggestion');
        if (target) {
            window.location.href = `detalle.php?peli_id=${target.dataset.id}`;
        }
    });

    // Filtro por selección en dropdown
    generoDropdown.addEventListener('change', (e) => {
        const genero = e.target.value;
        if (genero !== '') {
            searchInput.value = genero;
            searchInput.dispatchEvent(new Event('input'));
        } else {
            mostrarTodasLasPeliculas();
        }
    });

    function mostrarTodasLasPeliculas() {
        window.location.reload();
    }
});
