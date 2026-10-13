const indicator = document.querySelector('[data-page-loading]');

if (indicator) {
    let delay;
    let recovery;

    const reset = () => {
        clearTimeout(delay);
        clearTimeout(recovery);
        indicator.hidden = true;
    };

    const start = () => {
        reset();
        delay = setTimeout(() => { indicator.hidden = false; }, 350);
        // A download or a cancelled navigation can leave the current page open.
        recovery = setTimeout(reset, 15000);
    };

    const isPageNavigation = (url, target) => {
        if (target && target.toLowerCase() !== '_self') return false;
        const destination = new URL(url, document.baseURI);
        return destination.origin === location.origin
            && ['http:', 'https:'].includes(destination.protocol)
            && !(destination.pathname === location.pathname
                && destination.search === location.search)
            && !/\.(csv|pdf|zip)$/i.test(destination.pathname);
    };

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');
        if (!link || event.defaultPrevented || event.button !== 0
            || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey
            || link.hasAttribute('download') || link.hasAttribute('wire:click')
            || link.hasAttribute('data-no-page-loading')) return;
        if (isPageNavigation(link.href, link.target)) start();
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        const submitter = event.submitter;
        // Defer until existing handlers have had a chance to cancel the submit.
        queueMicrotask(() => {
            if (event.defaultPrevented || form.method.toLowerCase() === 'dialog'
                || form.hasAttribute('data-no-page-loading')) return;
            const target = submitter?.getAttribute('formtarget') ?? form.target;
            const action = submitter?.getAttribute('formaction') ?? form.action;
            const destination = new URL(action, document.baseURI);
            if ((!target || target.toLowerCase() === '_self')
                && destination.origin === location.origin
                && ['http:', 'https:'].includes(destination.protocol)
                && !/\.(csv|pdf|zip)$/i.test(destination.pathname)) start();
        });
    });

    window.addEventListener('pageshow', reset);
    window.addEventListener('pagehide', reset);
    window.addEventListener('load', reset);
    if (document.readyState !== 'complete') start();
}
