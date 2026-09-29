import './bootstrap';
import './schedule';

const employeeNavbar = document.querySelector('[data-employee-navbar]');

if (employeeNavbar) {
    const updateNavbar = () => {
        employeeNavbar.dataset.scrolled = String(window.scrollY > 8);
    };

    updateNavbar();
    window.addEventListener('scroll', updateNavbar, { passive: true });
    window.addEventListener('pageshow', updateNavbar);
}
