document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('utenti-table-body');
    const alerts = document.getElementById('alerts');

    let utenti = [];

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
    loadUtenti();
});
