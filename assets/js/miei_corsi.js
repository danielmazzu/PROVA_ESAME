/**
 * ============================================
 * I Miei Corsi - Pagina del Dipendente
 * ============================================
 * Gestisce la visualizzazione dei corsi assegnati al dipendente loggato.
 * Il dipendente puo':
 * - Visualizzare tutti i propri corsi con il relativo stato
 * - Filtrare per stato (Assegnato, Completato, Scaduto) e per categoria
 * - Segnare un corso come "Completato" cliccando sul pulsante dedicato
 * ============================================
 */

document.addEventListener('DOMContentLoaded', () => {
    // Riferimenti agli elementi HTML della pagina
    const listContainer = document.getElementById('corsi-list');   // Container dove verranno renderizzate le card dei corsi
    const formFiltri = document.getElementById('form-filtri');     // Form con i filtri (stato, categoria)
    const emptyState = document.getElementById('empty-state');     // Messaggio "Nessun corso trovato"
    const alerts = document.getElementById('alerts');              // Container per i messaggi di feedback

    // Array che conterra' le assegnazioni caricate dal server
    let assegnazioni = [];

    /**
     * Carica le assegnazioni del dipendente dal backend tramite API GET.
     * Applica i filtri selezionati dall'utente nel form.
     */
    async function loadCorsi() {
        try {
            // Mostra uno spinner di caricamento mentre i dati vengono recuperati
            listContainer.innerHTML = '<div class="loading-overlay"><div class="spinner"></div></div>';
            
            // Costruisce l'URL con i filtri selezionati come query parameters
            let url = '../api/assegnazioni/index.php?';
            const stato = document.getElementById('filtro-stato').value;
            const categoria = document.getElementById('filtro-categoria').value.trim();
            
            // Aggiunge i filtri all'URL solo se hanno un valore
            if (stato) url += `stato=${encodeURIComponent(stato)}&`;
            if (categoria) url += `categoria=${encodeURIComponent(categoria)}`;

            // Chiama l'API e salva i dati ricevuti
            const response = await api.get(url);
            assegnazioni = response.data || [];
            renderCorsi(); // Aggiorna la visualizzazione
        } catch (error) {
            showAlert(alerts, error.message, 'danger');
            listContainer.innerHTML = '';
        }
    }

    /**
     * Renderizza le card dei corsi nel container HTML.
     * Se non ci sono corsi, mostra il messaggio "empty state".
     */
    function renderCorsi() {
        if (assegnazioni.length === 0) {
            listContainer.innerHTML = '';
            emptyState.classList.remove('hidden'); // Mostra il messaggio "nessun corso"
        } else {
            emptyState.classList.add('hidden');
            // Genera l'HTML per ogni assegnazione usando la funzione createCard
            listContainer.innerHTML = assegnazioni.map(a => createCard(a)).join('');
            attachEvents(); // Collega i click handler ai pulsanti "Completa"
        }
    }

    /**
     * Genera l'HTML di una singola card corso.
     * Il colore del badge cambia in base allo stato dell'assegnazione:
     * - Assegnato: verde (badge-primary)
     * - Completato: blu (badge-info)
     * - Scaduto: rosso (badge-danger)
     * - Annullato: giallo (badge-warning)
     * 
     * @param {object} a - Oggetto assegnazione con i dati del corso
     * @returns {string} HTML della card
     */
    function createCard(a) {
        // Formatta le date nel formato italiano (gg/mm/aaaa)
        const dateAssegnazione = new Date(a.data_assegnazione).toLocaleDateString('it-IT');
        const dateScadenza = new Date(a.data_scadenza).toLocaleDateString('it-IT');
        const isCompletato = a.stato === 'Completato';
        const isScaduto = a.stato === 'Scaduto';
        const canComplete = a.stato === 'Assegnato'; // Solo i corsi "Assegnato" possono essere completati

        // Determina la classe CSS del badge in base allo stato
        let statusClass = 'badge-primary';
        if (isCompletato) statusClass = 'badge-info';
        if (a.stato === 'Scaduto') statusClass = 'badge-danger';
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

    /**
     * Collega gli event listener ai pulsanti "Segna come Completato".
     * Quando il dipendente clicca, viene chiesta conferma e poi inviata
     * la richiesta PUT al backend per aggiornare lo stato dell'assegnazione.
     */
    function attachEvents() {
        listContainer.querySelectorAll('.btn-completa').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = e.target.dataset.id; // Recupera l'ID dell'assegnazione dal data attribute
                if (!confirm('Confermi di aver completato questo corso?')) return; // Chiede conferma

                try {
                    // Invia la richiesta di completamento al backend
                    await api.put(`../api/assegnazioni/completa.php?id=${id}`, {});
                    showAlert(alerts, 'Corso completato con successo!', 'success');
                    loadCorsi(); // Ricarica i dati per aggiornare la visualizzazione
                } catch (error) {
                    showAlert(alerts, error.message, 'danger');
                }
            });
        });
    }

    // Gestione submit del form filtri: ricarica i corsi con i nuovi filtri
    if (formFiltri) {
        formFiltri.addEventListener('submit', (e) => {
            e.preventDefault();
            loadCorsi();
        });
    }

    /**
     * Funzione di sicurezza: previene attacchi XSS (Cross-Site Scripting)
     * escapando i caratteri HTML speciali (<, >, &, ", ')
     */
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Funzione locale per mostrare alert temporanei
    function showAlert(container, message, type) {
        container.innerHTML = `<div class="alert alert-${type}">${escapeHtml(message)}</div>`;
        setTimeout(() => container.innerHTML = '', 4000); // Rimuove dopo 4 secondi
    }

    // Caricamento iniziale: recupera e mostra i corsi al primo accesso alla pagina
    loadCorsi();
});
