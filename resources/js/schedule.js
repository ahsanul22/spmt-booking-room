document.querySelector('[data-menu-toggle]')?.addEventListener('click', event => {
    const button = event.currentTarget;
    const expanded = button.getAttribute('aria-expanded') !== 'true';
    button.setAttribute('aria-expanded', String(expanded));
    document.getElementById('schedule-navigation').classList.toggle('hidden', !expanded);
});
