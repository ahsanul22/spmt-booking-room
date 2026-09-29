document.querySelectorAll('[data-menu-toggle]').forEach(button => {
    const navigation = document.getElementById(button.getAttribute('aria-controls'));
    if (!navigation) return;

    const setExpanded = expanded => {
        button.setAttribute('aria-expanded', String(expanded));
        button.setAttribute('aria-label', expanded ? 'Tutup navigasi' : 'Buka navigasi');
        navigation.classList.toggle('hidden', !expanded);
        navigation.classList.toggle('flex', expanded);
    };

    button.addEventListener('click', () => {
        setExpanded(button.getAttribute('aria-expanded') !== 'true');
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && button.getAttribute('aria-expanded') === 'true') {
            setExpanded(false);
            button.focus();
        }
    });

    window.matchMedia('(min-width: 1024px)').addEventListener('change', () => setExpanded(false));
});
