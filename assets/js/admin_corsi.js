document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('corsi-table-body');
    const formFiltri = document.getElementById('form-filtri');
    const alerts = document.getElementById('alerts');
    const btnNuovoCorso = document.getElementById('btn-nuovo-corso');

    let corsi = [];

    async function loadCorsi() {
        try {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Caricamento in corso...</td></tr>';
            
            let url = '../api/corsi/index.php?';
            const categoria = document.getElementById('filtro-categoria').value.trim();
            const attivo = document.getElementById('filtro-attivo').value;
            
            if (categoria) url += `categoria=${encodeURIComponent(categoria)}&`;
            if (attivo !== '') url += `attivo=${encodeURIComponent(attivo)}`;

            const response = await api.get(url);
            corsi = response.data || [];
            renderCorsi();
        } catch (error) {
            showAlert(alerts, error.message, 'danger');
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Errore nel caricamento dei dati</td></tr>';
        }
    }

    function renderCorsi() {
        if (corsi.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Nessun corso trovato</td></tr>';
            return;
        }

        tableBody.innerHTML = corsi.map(c => {
            const isAttivo = parseInt(c.attivo) === 1;
            return `
                <tr>
                    <td>${c.id}</td>
                    <td><strong>${escapeHtml(c.titolo)}</strong></td>
                    <td>${escapeHtml(c.categoria)}</td>
                    <td>${c.durata_ore}</td>
                    <td><span class="badge ${isAttivo ? 'badge-success' : 'badge-danger'}">${isAttivo ? 'Attivo' : 'Disattivato'}</span></td>
                    <td>
                        <button class="btn btn-ghost btn-sm btn-edit" data-id="${c.id}" title="Modifica"><i class="ph ph-pencil-simple"></i></button>
                        <button class="btn btn-ghost btn-sm btn-toggle" data-id="${c.id}" title="${isAttivo ? 'Disattiva' : 'Attiva'}">
                            ${isAttivo ? '<i class="ph ph-prohibit"></i>' : '<i class="ph ph-check-circle"></i>'}
                        </button>
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
                openCorsoModal(id);
            });
        });

        tableBody.querySelectorAll('.btn-toggle').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = e.target.closest('button').dataset.id;
                const corso = corsi.find(c => c.id == id);
                const nuovoStato = parseInt(corso.attivo) === 1 ? 0 : 1;
                
                try {
                    await api.put(`../api/corsi/disattiva.php?id=${id}`, { attivo: nuovoStato });
                    showAlert(alerts, 'Stato del corso aggiornato.', 'success');
                    loadCorsi();
                } catch (error) {
                    showAlert(alerts, error.message, 'danger');
                }
            });
        });
    }

    function openCorsoModal(id = null) {
        if (document.querySelector('.modal-backdrop')) return;
        let corso = null;
        if (id) {
            corso = corsi.find(c => c.id == id);
        }

        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop';
        backdrop.innerHTML = `
            <div class="modal">
                <div class="modal-header">
                    <h3>${corso ? 'Modifica Corso' : 'Nuovo Corso'}</h3>
                    <button class="btn-close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="form-corso">
                        <div class="form-group">
                            <label class="form-label">Titolo</label>
                            <input type="text" class="form-input" id="c-titolo" value="${corso ? escapeHtml(corso.titolo) : ''}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Categoria</label>
                            <input type="text" class="form-input" id="c-categoria" value="${corso ? escapeHtml(corso.categoria) : ''}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Durata (Ore)</label>
                            <input type="number" class="form-input" id="c-durata" value="${corso ? corso.durata_ore : ''}" required min="1">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Descrizione</label>
                            <textarea class="form-input" id="c-descrizione">${corso && corso.descrizione ? escapeHtml(corso.descrizione) : ''}</textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-ghost" data-dismiss="modal">Annulla</button>
                    <button class="btn btn-primary" id="btn-salva-corso">Salva</button>
                </div>
            </div>
        `;

        document.body.appendChild(backdrop);

        const closeModal = () => backdrop.remove();
        backdrop.querySelectorAll('[data-dismiss="modal"]').forEach(el => el.addEventListener('click', closeModal));
        
        backdrop.querySelector('#btn-salva-corso').addEventListener('click', async () => {
            const payload = {
                titolo: backdrop.querySelector('#c-titolo').value,
                categoria: backdrop.querySelector('#c-categoria').value,
                durata_ore: backdrop.querySelector('#c-durata').value,
                descrizione: backdrop.querySelector('#c-descrizione').value
            };

            try {
                if (corso) {
                    await api.put(`../api/corsi/update.php?id=${corso.id}`, payload);
                    showAlert(alerts, 'Corso aggiornato!', 'success');
                } else {
                    await api.post(`../api/corsi/index.php`, payload);
                    showAlert(alerts, 'Corso creato!', 'success');
                }
                closeModal();
                loadCorsi();
            } catch (error) {
                alert(error.message); // simple alert for modal error
            }
        });
    }

    if (btnNuovoCorso) {
        btnNuovoCorso.addEventListener('click', () => openCorsoModal());
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
