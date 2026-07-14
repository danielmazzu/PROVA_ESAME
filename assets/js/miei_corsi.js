document.addEventListener('DOMContentLoaded', () => {
    const listContainer = document.getElementById('corsi-list');
    const formFiltri = document.getElementById('form-filtri');
    const emptyState = document.getElementById('empty-state');
    const alerts = document.getElementById('alerts');

    let assegnazioni = [];

    async function loadCorsi() {
        try {
            listContainer.innerHTML = '<div class="loading-overlay"><div class="spinner"></div></div>';
            
            let url = '../api/assegnazioni/index.php?';
            const stato = document.getElementById('filtro-stato').value;
            const categoria = document.getElementById('filtro-categoria').value.trim();
            
            if (stato) url += `stato=${encodeURIComponent(stato)}&`;
            if (categoria) url += `categoria=${encodeURIComponent(categoria)}`;

            const response = await api.get(url);
            assegnazioni = response.data || [];
            renderCorsi();
        } catch (error) {
            showAlert(alerts, error.message, 'danger');
            listContainer.innerHTML = '';
        }
    }

    function renderCorsi() {
        if (assegnazioni.length === 0) {
            listContainer.innerHTML = '';
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
            listContainer.innerHTML = assegnazioni.map(a => createCard(a)).join('');
            attachEvents();
        }
    }

    function createCard(a) {
        const dateAssegnazione = new Date(a.data_assegnazione).toLocaleDateString('it-IT');
        const dateScadenza = new Date(a.data_scadenza).toLocaleDateString('it-IT');
        const isCompletato = a.stato === 'Completato';
        const isScaduto = a.stato === 'Scaduto';
        const canComplete = a.stato === 'Assegnato';

        let statusClass = 'badge-primary';
        if (isCompletato) statusClass = 'badge-success';
        if (isScaduto) statusClass = 'badge-danger';
        if (a.stato === 'Annullato') statusClass = 'badge-warning';

        return `
            <div class="card" data-id="${a.id}">
                <div class="card-body">
                    <div class="flex-between" style="margin-bottom: 10px;">
                        <span class="badge ${statusClass}">${a.stato}</span>
                        <span style="font-size: 0.9em; color: var(--gray-500)">${a.categoria}</span>
                    </div>
                    <h3>${escapeHtml(a.corso_titolo)}</h3>
                    <p style="margin: 10px 0; color: var(--gray-600); font-size: 0.9em;">
                        Assegnato il: ${dateAssegnazione}<br>
                        Scadenza: <strong>${dateScadenza}</strong><br>
                        Durata: ${a.durata_ore} ore
                    </p>
                    ${isCompletato && a.data_completamento ? `<p style="color: var(--success); font-size: 0.9em; margin-bottom: 10px;">Completato il: ${new Date(a.data_completamento).toLocaleDateString('it-IT')}</p>` : ''}
                    
                    <div style="margin-top: 15px; border-top: 1px solid var(--gray-200); padding-top: 15px;">
                        ${canComplete ? `<button class="btn btn-success btn-completa" data-id="${a.id}">Segna come Completato</button>` : ''}
                        ${!canComplete ? `<button class="btn btn-secondary" disabled>${a.stato}</button>` : ''}
                    </div>
                </div>
            </div>
        `;
    }

    function attachEvents() {
        listContainer.querySelectorAll('.btn-completa').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = e.target.dataset.id;
                if (!confirm('Confermi di aver completato questo corso?')) return;

                try {
                    await api.put(`../api/assegnazioni/completa.php?id=${id}`, {});
                    showAlert(alerts, 'Corso completato con successo!', 'success');
                    loadCorsi(); // Reload data
                } catch (error) {
                    showAlert(alerts, error.message, 'danger');
                }
            });
        });
    }

    if (formFiltri) {
        formFiltri.addEventListener('submit', (e) => {
            e.preventDefault();
            loadCorsi();
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showAlert(container, message, type) {
        container.innerHTML = `<div class="alert alert-${type}">${escapeHtml(message)}</div>`;
        setTimeout(() => container.innerHTML = '', 4000);
    }

    // Init
    loadCorsi();
});
