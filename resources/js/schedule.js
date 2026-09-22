const calendar = document.querySelector('[data-schedule]');
if (calendar) {
    const today = new Date();
    let selected = new Date(today.getFullYear(), today.getMonth(), today.getDate());
    let month = new Date(selected.getFullYear(), selected.getMonth(), 1);
    const sameDay = (a, b) => a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
    const fullDate = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    const monthDate = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' });
    function render() {
        calendar.querySelector('[data-month-label]').textContent = monthDate.format(month);
        calendar.querySelector('[data-selected-label]').textContent = fullDate.format(selected);
        const grid = calendar.querySelector('[data-calendar]');
        grid.replaceChildren();
        const start = new Date(month.getFullYear(), month.getMonth(), 1 - (month.getDay() + 6) % 7);
        for (let i = 0; i < 42; i++) {
            const date = new Date(start.getFullYear(), start.getMonth(), start.getDate() + i);
            const button = document.createElement('button');
            button.type = 'button';
            const isSelected = sameDay(date, selected);
            button.className = 'relative flex h-24 items-start justify-start border-b border-r border-slate-100 p-2 text-xs transition hover:bg-secondaryLight/20 sm:h-28 sm:p-3 ' + (isSelected ? 'bg-secondaryLight/20 ring-1 ring-inset ring-primary ' : '') + (date.getMonth() !== month.getMonth() ? 'bg-background/60 text-muted' : 'text-slate-600');
            button.setAttribute('aria-label', fullDate.format(date));
            button.setAttribute('aria-pressed', String(isSelected));
            if (sameDay(date, today)) button.setAttribute('aria-current', 'date');
            const number = document.createElement('span');
            number.className = 'flex h-7 w-7 items-center justify-center rounded-full ' + (sameDay(date, today) ? 'bg-primary font-semibold text-white' : '');
            number.textContent = date.getDate();
            button.append(number);
            button.addEventListener('click', () => {
                selected = date;
                month = new Date(date.getFullYear(), date.getMonth(), 1);
                render();
                [...grid.children].find(cell => cell.getAttribute('aria-pressed') === 'true')?.focus({ preventScroll: true });
            });
            grid.append(button);
        }
    }
    calendar.querySelector('[data-prev]').addEventListener('click', () => { month.setMonth(month.getMonth() - 1); render(); });
    calendar.querySelector('[data-next]').addEventListener('click', () => { month.setMonth(month.getMonth() + 1); render(); });
    calendar.querySelector('[data-today]').addEventListener('click', () => {
        selected = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        month = new Date(today.getFullYear(), today.getMonth(), 1);
        render();
    });
    render();
}
document.querySelector('[data-menu-toggle]')?.addEventListener('click', event => {
    const button = event.currentTarget;
    const expanded = button.getAttribute('aria-expanded') !== 'true';
    button.setAttribute('aria-expanded', String(expanded));
    document.getElementById('schedule-navigation').classList.toggle('hidden', !expanded);
});
