<dialog id="admin-delete-dialog" class="catalog-dialog" aria-labelledby="delete-heading">
    <h2 id="delete-heading">Delete this record?</h2>
    <p id="delete-message" style="margin-top:16px"></p>
    <p class="catalog-hint">This action cannot be undone.</p>
    <div class="catalog-form-actions"><button type="button" id="delete-cancel" class="button button-soft">Cancel</button><button type="button" id="delete-confirm" class="button admin-delete-confirm">Delete</button></div>
</dialog>
<script>
(() => {
    const deleteDialog = document.querySelector('#admin-delete-dialog');
    let pendingDeleteForm;
    document.addEventListener('submit', event => {
        if (!event.target.matches('[data-delete-name]')) return;
        event.preventDefault();
        pendingDeleteForm = event.target;
        document.querySelector('#delete-message').textContent = 'Delete "' + pendingDeleteForm.dataset.deleteName + '"?';
        deleteDialog.showModal();
        document.querySelector('#delete-cancel').focus();
    });
    document.querySelector('#delete-cancel').addEventListener('click', () => deleteDialog.close());
    document.querySelector('#delete-confirm').addEventListener('click', event => {
        if (!pendingDeleteForm) return;
        event.target.disabled = true;
        pendingDeleteForm.submit();
    });
    deleteDialog.addEventListener('close', () => { pendingDeleteForm = null; });
    const search = document.querySelector('#admin-search');
    if (!search) return;
    const form = search.closest('form');
    const status = document.querySelector('#admin-search-status');
    let timeout;
    let controller;
    let version = 0;
    const loadResults = async (requestVersion) => {
        controller = new AbortController();
        const url = new URL(location.href);
        url.searchParams.delete('page');
        url.searchParams.delete('edit');
        if (search.value.trim()) url.searchParams.set('search', search.value.trim());
        else url.searchParams.delete('search');
        status.textContent = 'Searching…';
        document.querySelector('#admin-results').setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(url, {signal: controller.signal, headers: {'X-Requested-With': 'XMLHttpRequest'}});
            if (!response.ok) throw new Error('Search failed');
            const html = new DOMParser().parseFromString(await response.text(), 'text/html');
            const results = html.querySelector('#admin-results');
            if (!results) throw new Error('Please sign in again');
            if (requestVersion !== version) return;
            document.querySelector('#admin-results').replaceWith(results);
            history.replaceState(null, '', url);
            status.textContent = 'Results updated.';
        } catch (error) {
            if (error.name !== 'AbortError' && requestVersion === version) {
                status.textContent = 'Unable to update results. Please try Search again or refresh the page.';
            }
        } finally {
            if (requestVersion === version) document.querySelector('#admin-results').removeAttribute('aria-busy');
        }
    };
    const schedule = (delay) => {
        clearTimeout(timeout);
        controller?.abort();
        version++;
        const requestVersion = version;
        timeout = setTimeout(() => loadResults(requestVersion), delay);
    };
    search.addEventListener('input', () => schedule(250));
    form.addEventListener('submit', event => { event.preventDefault(); schedule(0); });
    form.querySelector('[data-clear-search]')?.addEventListener('click', event => {
        event.preventDefault();
        search.value = '';
        search.focus();
        schedule(0);
    });
})();
</script>
