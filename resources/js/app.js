import './bootstrap';
import './page-loading';
document.addEventListener('click', (event) => {
    if (event.target.closest('[data-print-report]')) window.print();
});
import './schedule';

{
    const updateNavbar = () => {
        const employeeNavbar = document.querySelector('[data-employee-navbar]');
        if (employeeNavbar) employeeNavbar.dataset.scrolled = String(window.scrollY > 8);
    };
    document.addEventListener('livewire:navigated', updateNavbar);

    updateNavbar();
    window.addEventListener('scroll', updateNavbar, { passive: true });
    window.addEventListener('pageshow', updateNavbar);
}

import './approval';
import './whatsapp-template';
