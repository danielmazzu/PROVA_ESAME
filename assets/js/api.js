/**
 * ============================================
 * API Client - Wrapper per Fetch
 * ============================================
 * Utilizzo:
 *   const data = await api.get('/api/todos/index.php');
 *   const data = await api.post('/api/auth/login.php', { username, password });
 *   const data = await api.put('/api/todos/update.php?id=1', { title: 'Nuovo' });
 *   const data = await api.delete('/api/todos/delete.php?id=1');
 * ============================================
 */

const api = {
    /**
     * Esegue una richiesta fetch con configurazione automatica.
     * @param {string} url - URL dell'endpoint
     * @param {object} options - Opzioni fetch aggiuntive
     * @returns {Promise<object>} Risposta JSON
     */
    async request(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        };

        const config = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...options.headers,
            },
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw {
                    status: response.status,
                    message: data.message || 'Errore del server.',
                    data: data
                };
            }

            return data;
        } catch (error) {
            if (error.status) {
                throw error; // Errore HTTP già formattato
            }
            throw {
                status: 0,
                message: 'Errore di connessione al server.',
                data: null
            };
        }
    },

    /**
     * GET request
     */
    async get(url) {
        return this.request(url, { method: 'GET' });
    },

    /**
     * POST request
     */
    async post(url, body = {}) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(body),
        });
    },

    /**
     * PUT request
     */
    async put(url, body = {}) {
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify(body),
        });
    },

    /**
     * DELETE request
     */
    async delete(url) {
        return this.request(url, { method: 'DELETE' });
    },
};

/* ============================================
 * Utility: Mostra alert nella pagina
 * ============================================ */

/**
 * Mostra un messaggio alert in un container specificato.
 * @param {string} containerId - ID dell'elemento container
 * @param {string} message - Messaggio da mostrare
 * @param {string} type - Tipo: 'success', 'danger', 'warning', 'info'
 * @param {number} autoDismiss - ms prima di rimuovere (0 = manuale)
 */
function showAlert(containerId, message, type = 'info', autoDismiss = 5000) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;

    container.innerHTML = '';
    container.appendChild(alert);

    if (autoDismiss > 0) {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-8px)';
            setTimeout(() => alert.remove(), 300);
        }, autoDismiss);
    }
}
