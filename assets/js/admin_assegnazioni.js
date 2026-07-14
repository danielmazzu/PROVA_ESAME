document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('assegnazioni-table-body');
    const formFiltri = document.getElementById('form-filtri');
    const alerts = document.getElementById('alerts');
    const btnNuova = document.getElementById('btn-nuova-assegnazione');

    let assegnazioni = [];

    async function loadAssegnazioni() {
        try {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Caricamento in corso...</td></tr>';
            
            let url = '../api/assegnazioni/index.php?';
            const stato = document.getElementById('filtro-stato').value;
            
            if (stato) url += `stato=${encodeURIComponent(stato)}`;

            const response = await api.get(url);
            assegnazioni = response.data || [];
            renderAssegnazioni();
        } catch (error) {
            showAlert(alerts, error.message, 'danger');
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Errore nel caricamento dei dati</td></tr>';
        }
    }

    function renderAssegnazioni() {
        if (assegnazioni.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Nessuna assegnazione trovata</td></tr>';
            return;
        }

        tableBody.innerHTML = assegnazioni.map(a => {
            const dateScadenza = new Date(a.data_scadenza).toLocaleDateString('it-IT');
            let statusClass = 'badge-primary';
            if (a.stato === 'Completato') statusClass = 'badge-success';
            if (a.stato === 'Scaduto') statusClass = 'badge-danger';
            if (a.stato === 'Annullato') statusClass = 'badge-warning';

            return `
                <tr>
                    <td>${a.id}</td>
                    <td>${escapeHtml(a.utente_nome + ' ' + a.utente_cognome)}</td>
                    <td>${escapeHtml(a.corso_titolo)}</td>
                    <td>${dateScadenza}</td>
                    <td><span class="badge ${statusClass}">${a.stato}</span></td>
                    <td>
                        <button class="btn btn-ghost btn-sm btn-edit" data-id="${a.id}" title="Modifica Scadenza"><i class="ph ph-calendar-blank"></i></button>
                        ${a.stato !== 'Completato' && a.stato !== 'Annullato' ? 
                            `<button class="btn btn-ghost btn-sm btn-annulla" data-id="${a.id}" title="Annulla"><i class="ph ph-x-circle"></i></button>` : ''}
                    </td>
                </tr>
            `;
        }).join('');

        attachEvents();
    }

    function attachEvents() {
        tableBody.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.closest('button').dataset.id;
                openModalUpdateScadenza(id);
            });
        });

        tableBody.querySelectorAll('.btn-annulla').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = e.target.closest('button').dataset.id;
                if (!confirm('Vuoi davvero annullare questa assegnazione?')) return;
                
                try {
                    await api.put(`../api/assegnazioni/annulla.php?id=${id}`, {});
                    showAlert(alerts, 'Assegnazione annullata.', 'success');
                    loadAssegnazioni();
                } catch (error) {
                    showAlert(alerts, error.message, 'danger');
                }
            });
        });
    }

    // Modal per creare con select per dipendenti e corsi
    async function openModalCreate() {
        let corsiOptions = '<option value="">Seleziona un corso...</option>';
        let utentiOptions = '<option value="">Seleziona un dipendente...</option>';

        try {
            // Fetch corsi (solo quelli attivi possibilmente, o tutti per l'admin)
            const resCorsi = await api.get('../api/corsi/index.php');
            if (resCorsi.data) {
                corsiOptions += resCorsi.data.map(c => `<option value="${c.id}">${escapeHtml(c.titolo)}</option>`).join('');
            }

            // Fetch utenti (solo dipendenti)
            const resUtenti = await api.get('../api/utenti/index.php');
            if (resUtenti.data) {
                utentiOptions += resUtenti.data.map(u => `<option value="${u.id}">${escapeHtml(u.cognome + ' ' + u.nome)} - ${escapeHtml(u.email)}</option>`).join('');
            }
        } catch (e) {
            showAlert(alerts, 'Errore nel caricamento delle liste: ' + e.message, 'danger');
            return; // Blocca apertura se fallisce
        }

        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop';
        backdrop.innerHTML = `
            <div class="modal">
                <div class="modal-header">
                    <h3>Nuova Assegnazione</h3>
                    <button type="button" class="btn-close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="form-assegnazione">
                        <div class="form-group">
                            <label class="form-label">Corso</label>
                            <select class="form-input" id="a-corso" required>
                                ${corsiOptions}
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Dipendente</label>
                            <select class="form-input" id="a-utente" required>
                                ${utentiOptions}
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Data Scadenza</label>
                            <input type="date" class="form-input" id="a-scadenza" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-dismiss="modal">Chiudi</button>
                    <button type="button" class="btn btn-primary" id="btn-salva-assegnazione">Salva</button>
                </div>
            </div>
        `;

        document.body.appendChild(backdrop);
        const closeModal = () => backdrop.remove();
        backdrop.querySelectorAll('[data-dismiss="modal"]').forEach(el => el.addEventListener('click', closeModal));
        
        const btnSalva = document.getElementById('btn-salva-assegnazione');
        btnSalva.addEventListener('click', async () => {
            btnSalva.disabled = true;
            btnSalva.textContent = 'Salvataggio...';

            const payload = {
                corso_id: document.getElementById('a-corso').value,
                utente_id: document.getElementById('a-utente').value,
                data_scadenza: document.getElementById('a-scadenza').value
            };

            if (!payload.corso_id || !payload.utente_id || !payload.data_scadenza) {
                alert("Compila tutti i campi!");
                btnSalva.disabled = false;
                btnSalva.textContent = 'Salva';
                return;
            }

            try {
                await api.post(`../api/assegnazioni/create.php`, payload);
                showAlert(alerts, 'Assegnazione creata!', 'success');
                closeModal();
                loadAssegnazioni();
            } catch (error) {
                alert(error.message);
                btnSalva.disabled = false;
                btnSalva.textContent = 'Salva';
            }
        });
    }

    function openModalUpdateScadenza(id) {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop';
        backdrop.innerHTML = `
            <div class="modal">
                <div class="modal-header">
                    <h3>Modifica Scadenza</h3>
                    <button class="btn-close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nuova Data Scadenza</label>
                        <input type="date" class="form-input" id="a-up-scadenza" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-ghost" data-dismiss="modal">Annulla</button>
                    <button class="btn btn-primary" id="btn-salva-scadenza">Salva</button>
                </div>
            </div>
        `;

        document.body.appendChild(backdrop);
        const closeModal = () => backdrop.remove();
        backdrop.querySelectorAll('[data-dismiss="modal"]').forEach(el => el.addEventListener('click', closeModal));
        
        const btnSalvaScadenza = document.getElementById('btn-salva-scadenza');
        btnSalvaScadenza.addEventListener('click', async () => {
            btnSalvaScadenza.disabled = true;
            btnSalvaScadenza.textContent = 'Salvataggio...';
            try {
                await api.put(`../api/assegnazioni/update.php?id=${id}`, {
                    data_scadenza: document.getElementById('a-up-scadenza').value
                });
                showAlert(alerts, 'Scadenza aggiornata!', 'success');
                closeModal();
                loadAssegnazioni();
            } catch (error) {
                alert(error.message);
                btnSalvaScadenza.disabled = false;
                btnSalvaScadenza.textContent = 'Salva';
            }
        });
    }

    if (btnNuova) {
        btnNuova.addEventListener('click', () => openModalCreate());
    }

    if (formFiltri) {
        formFiltri.addEventListener('submit', (e) => {
            e.preventDefault();
            loadAssegnazioni();
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
    loadAssegnazioni();
});
