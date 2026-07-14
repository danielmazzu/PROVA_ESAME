/**
 * ============================================
 * Elenco Dipendenti - Pagina Admin
 * ============================================
 * Gestisce la visualizzazione della tabella dei dipendenti registrati.
 * Il referente puo' consultare l'elenco completo dei dipendenti con i loro dati.
 * Questa pagina e' utile per verificare gli ID dei dipendenti quando si creano
 * le assegnazioni o si filtrano le statistiche.
 * ============================================
 */

document.addEventListener('DOMContentLoaded', () => {
    // Riferimenti agli elementi HTML
    const tableBody = document.getElementById('utenti-table-body'); // Corpo della tabella utenti
    const alerts = document.getElementById('alerts');                // Container messaggi di feedback

    // Array per i dati degli utenti
    let utenti = [];

    /**
     * Carica la lista dei dipendenti dal backend tramite API GET.
     * L'API restituisce solo gli utenti con ruolo "dipendente" (non i referenti).
     */
    async function loadUtenti() {
        try {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Caricamento in corso...</td></tr>';
            
            const response = await api.get('../api/utenti/index.php');
            utenti = response.data || [];
            renderUtenti();
        } catch (error) {
            showAlert(alerts, error.message, 'danger');
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Errore nel caricamento dei dati</td></tr>';
        }
    }

    /**
     * Renderizza le righe della tabella con i dati dei dipendenti.
     * Per ogni utente mostra: ID, nome, cognome, email e ruolo.
     */
    function renderUtenti() {
        if (utenti.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Nessun utente trovato</td></tr>';
            return;
        }

        tableBody.innerHTML = utenti.map(u => `
            <tr>
                <td>${u.id}</td>
                <td><strong>${escapeHtml(u.nome)}</strong></td>
                <td><strong>${escapeHtml(u.cognome)}</strong></td>
                <td>${escapeHtml(u.email)}</td>
                <td><span class="badge badge-secondary">${escapeHtml(u.role)}</span></td>
            </tr>
        `).join('');
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

    // Caricamento iniziale della lista dipendenti
    loadUtenti();
});
