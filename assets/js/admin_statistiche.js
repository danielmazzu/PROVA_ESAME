/**
 * ============================================
 * Statistiche Academy - Pagina Admin
 * ============================================
 * Gestisce la visualizzazione delle statistiche aggregate dei corsi.
 * Il referente puo':
 * - Visualizzare i dati raggruppati per mese e categoria
 * - Filtrare per mese, categoria e specifico dipendente
 * - Vedere la percentuale di completamento con barra di progresso visuale
 * ============================================
 */

document.addEventListener('DOMContentLoaded', () => {
    // Riferimenti agli elementi HTML
    const tableBody = document.getElementById('statistiche-table-body'); // Corpo della tabella statistiche
    const formFiltri = document.getElementById('form-filtri');           // Form con i filtri
    const alerts = document.getElementById('alerts');                     // Container messaggi di feedback

    // Array per i dati delle statistiche
    let statistiche = [];

    /**
     * Carica le statistiche dal backend con i filtri selezionati.
     * L'API restituisce i dati aggregati per mese e categoria.
     */
    async function loadStatistiche() {
        try {
            // Mostra un messaggio di caricamento nella tabella
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Caricamento in corso...</td></tr>';
            
            // Costruisce l'URL con i parametri dei filtri
            let url = '../api/statistiche/academy.php?';
            const mese = document.getElementById('filtro-mese').value;
            const categoria = document.getElementById('filtro-categoria').value.trim();
            // Verifica se il campo filtro-dipendente esiste nella pagina
            const dipendente = document.getElementById('filtro-dipendente') ? document.getElementById('filtro-dipendente').value.trim() : '';
            
            // Aggiunge i filtri attivi come query parameters
            if (mese) url += `mese=${encodeURIComponent(mese)}&`;
            if (categoria) url += `categoria=${encodeURIComponent(categoria)}&`;
            if (dipendente) url += `utente_id=${encodeURIComponent(dipendente)}`;

            // Chiama l'API e salva i risultati
            const response = await api.get(url);
            statistiche = response.data || [];
            renderStatistiche(); // Aggiorna la tabella
        } catch (error) {
            showAlert(alerts, error.message, 'danger');
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Errore nel caricamento dei dati</td></tr>';
        }
    }

    /**
     * Renderizza le righe della tabella statistiche.
     * Per ogni riga mostra: mese, categoria, numero assegnazioni, completamenti e percentuale.
     * Il colore del badge della percentuale cambia in base al valore:
     * - 100%: verde (badge-success)
     * - < 50%: giallo (badge-warning)
     * - 50-99%: blu (badge-primary)
     */
    function renderStatistiche() {
        if (statistiche.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Nessuna statistica trovata</td></tr>';
            return;
        }

        tableBody.innerHTML = statistiche.map(s => {
            // Determina il colore del badge in base alla percentuale di completamento
            let badgeClass = 'badge-primary';
            if (s.percentualeCompletamento === 100) badgeClass = 'badge-success';
            else if (s.percentualeCompletamento < 50) badgeClass = 'badge-warning';

            return `
                <tr>
                    <td><strong>${escapeHtml(s.mese)}</strong></td>
                    <td>${escapeHtml(s.categoria)}</td>
                    <td>${s.numeroAssegnazioni}</td>
                    <td>${s.numeroCompletamenti}</td>
                    <td>
                        <div style="display:flex; align-items:center; gap: 10px;">
                            <span class="badge ${badgeClass}">${s.percentualeCompletamento}%</span>
                            <!-- Barra di progresso visuale -->
                            <div style="flex-grow:1; background:var(--gray-200); height:8px; border-radius:4px; overflow:hidden;">
                                <div style="background:var(--primary); height:100%; width:${s.percentualeCompletamento}%;"></div>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // Gestione submit del form filtri
    if (formFiltri) {
        formFiltri.addEventListener('submit', (e) => {
            e.preventDefault(); // Impedisce il ricaricamento della pagina
            loadStatistiche();  // Ricarica le statistiche con i nuovi filtri
        });
    }

    // Funzione di sicurezza anti-XSS
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Mostra un alert temporaneo
    function showAlert(container, message, type) {
        container.innerHTML = `<div class="alert alert-${type}">${escapeHtml(message)}</div>`;
        setTimeout(() => container.innerHTML = '', 4000);
    }

    // Caricamento iniziale delle statistiche
    loadStatistiche();
});
