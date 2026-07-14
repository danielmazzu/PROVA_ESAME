document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('statistiche-table-body');
    const formFiltri = document.getElementById('form-filtri');
    const alerts = document.getElementById('alerts');

    let statistiche = [];

    async function loadStatistiche() {
        try {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Caricamento in corso...</td></tr>';
            
            let url = '../api/statistiche/academy.php?';
            const mese = document.getElementById('filtro-mese').value;
            const categoria = document.getElementById('filtro-categoria').value.trim();
            
            if (mese) url += `mese=${encodeURIComponent(mese)}&`;
            if (categoria) url += `categoria=${encodeURIComponent(categoria)}`;

            const response = await api.get(url);
            statistiche = response.data || [];
            renderStatistiche();
        } catch (error) {
            showAlert(alerts, error.message, 'danger');
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Errore nel caricamento dei dati</td></tr>';
        }
    }

    function renderStatistiche() {
        if (statistiche.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Nessuna statistica trovata</td></tr>';
            return;
        }

        tableBody.innerHTML = statistiche.map(s => {
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
                            <div style="flex-grow:1; background:var(--gray-200); height:8px; border-radius:4px; overflow:hidden;">
                                <div style="background:var(--primary); height:100%; width:${s.percentualeCompletamento}%;"></div>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    if (formFiltri) {
        formFiltri.addEventListener('submit', (e) => {
            e.preventDefault();
            loadStatistiche();
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
    loadStatistiche();
});
