document.querySelectorAll('[data-wa-editor]').forEach((editor) => {
    const textarea = editor.querySelector('textarea[name="body"]');
    const fields = Array.from(editor.querySelectorAll('[data-wa-field]'));
    const notice = editor.querySelector('[data-wa-notice]');
    const count = editor.querySelector('[data-wa-character-count]');
    const tokensFor = (field) => JSON.parse(field.dataset.waTokens);

    const sync = () => {
        if (count) count.textContent = `${textarea.value.length} / ${textarea.maxLength}`;
        fields.forEach((field) => {
            const tokens = tokensFor(field);
            field.checked = tokens.every((token) => textarea.value.includes(token));
            field.indeterminate = !field.checked && tokens.some((token) => textarea.value.includes(token));
        });
    };

    fields.forEach((field) => {
        field.disabled = false;
        field.addEventListener('change', () => {
            const tokens = tokensFor(field);
            const previous = textarea.value;
            let next = previous;
            if (field.checked) {
                if (!tokens.every((token) => previous.includes(token))) {
                    // Add only missing values when an existing custom line contains part of a group.
                    const missing = tokens.filter((token) => !previous.includes(token));
                    const line = missing.length === tokens.length ? field.dataset.waLine : missing.join(' ');
                    next = previous + (previous && !previous.endsWith('\n') ? '\n' : '') + line;
                }
            } else {
                next = previous.split('\n').map((line) => {
                    if (line.trim() === field.dataset.waLine) return null;
                    if (tokens.length === 2 && /^Jam:\s*\{start_time\}.*\{end_time\}\s*WIB\s*$/.test(line)) return null;
                    if (tokens.length === 1 && tokens[0] === '{review_url}' && /^Review(?:\s*\([^)]*\))?:\s*\{review_url\}\s*$/.test(line)) return null;
                    return tokens.reduce((text, token) => text.split(token).join(''), line);
                }).filter((line) => line !== null).join('\n');
            }
            if (next.length > textarea.maxLength || !next.trim()) {
                notice.textContent = next.trim() ? 'Pesan sudah terlalu panjang. Pendekkan teks sebelum menambahkan data.' : 'Tambahkan teks pesan sebelum menghapus data terakhir.';
                sync();
                return;
            }
            notice.textContent = '';
            textarea.value = next;
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });
    textarea.addEventListener('input', () => {
        notice.textContent = '';
        sync();
    });
    sync();
});

document.querySelectorAll('[data-wa-settings]').forEach((form) => {
    const state = form.querySelector('[data-wa-save-state]');
    const summary = form.querySelector('[data-wa-recipient-summary]');
    const draft = form.querySelector('[data-wa-draft]');
    const snapshot = () => {
        const data = new FormData(form);
        return JSON.stringify({
            active: data.getAll('is_active').at(-1),
            pics: data.getAll('include_room_pics').at(-1),
            recipients: data.getAll('recipient_ids[]').sort(),
            body: data.get('body'),
        });
    };
    const initial = snapshot();
    const originalState = state.textContent;
    const update = () => {
        const count = form.querySelectorAll('input[name="recipient_ids[]"]:checked').length;
        const pics = form.querySelector('input[name="include_room_pics"][type="checkbox"]')?.checked;
        summary.textContent = pics ? `Penerima: PIC ruangan${count ? ` + ${count} Admin` : ''}.` : (count ? `Penerima: ${count} akun dipilih.` : 'Penerima belum dipilih.');
        const changed = snapshot() !== initial;
        state.textContent = changed ? 'Perubahan belum disimpan. Klik Simpan agar berlaku.' : originalState;
        draft.hidden = !changed && form.dataset.waUnsaved !== '1';
    };
    form.addEventListener('input', update);
    form.addEventListener('change', update);
    update();
});

document.querySelectorAll('[data-wa-open-pics]').forEach((link) => {
    link.addEventListener('click', () => {
        const panel = document.querySelector('#pic-readiness');
        if (panel) {
            panel.open = true;
            panel.querySelector('summary')?.focus({ preventScroll: true });
        }
    });
});
