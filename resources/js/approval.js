function initializeApproval() {
    document.querySelectorAll('[data-approval-dialog-trigger]').forEach((trigger) => {
        if (trigger.dataset.uiInitialized) return;
        trigger.dataset.uiInitialized = 'true';
        const dialog = document.getElementById(trigger.dataset.approvalDialogTrigger);
        if (!dialog || typeof dialog.showModal !== 'function') return;
        trigger.hidden = false;
        trigger.addEventListener('click', () => dialog.showModal());
    });

    document.querySelectorAll('[data-approval-dialog]').forEach((dialog) => {
        if (dialog.dataset.uiInitialized) return;
        dialog.dataset.uiInitialized = 'true';
        const form = dialog.querySelector('form');
        const notes = form.elements.decision_notes;
        dialog.querySelector('[data-close-decision]').addEventListener('click', () => dialog.close());
        notes.addEventListener('input', () => notes.setCustomValidity(''));
        form.querySelectorAll('[name="decision"]').forEach((button) => {
            button.addEventListener('click', () => {
                notes.setCustomValidity(button.value === 'rejected' && !notes.value.trim()
                    ? 'Catatan penolakan wajib diisi.' : '');
            });
        });
        form.addEventListener('submit', (event) => {
            if (form.dataset.submitting) event.preventDefault();
            else form.dataset.submitting = 'true';
        });
        if (dialog.dataset.reopen) dialog.showModal();
    });

    document.querySelectorAll('[data-dialog-open]').forEach((trigger) => {
        if (trigger.dataset.uiInitialized) return;
        trigger.dataset.uiInitialized = 'true';
        const dialog = document.getElementById(trigger.dataset.dialogOpen);
        if (!dialog) return;
        trigger.addEventListener('click', () => dialog.showModal());
        dialog.querySelectorAll('[data-dialog-close]').forEach((close) => {
            close.addEventListener('click', () => dialog.close());
        });
    });

}

initializeApproval();
document.addEventListener('livewire:navigated', initializeApproval);
