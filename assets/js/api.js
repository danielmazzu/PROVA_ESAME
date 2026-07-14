/**
 * ============================================
 * API Client - Wrapper per Fetch API
 * ============================================
 * Questo modulo centralizza tutte le chiamate HTTP verso il backend PHP.
 * Utilizza la Fetch API nativa del browser per inviare richieste REST (GET, POST, PUT, DELETE).
 * 
 * Vantaggi di questo approccio:
 * - Un unico punto di gestione degli errori di rete
 * - Header JSON configurati automaticamente per ogni richiesta
 * - Formato coerente per tutte le risposte (successo ed errore)
 * 
 * Utilizzo nei file JS delle singole pagine:
 *   const data = await api.get('../api/corsi/index.php');
 *   const data = await api.post('../api/auth/login.php', { email, password });
 *   const data = await api.put('../api/corsi/update.php?id=1', { titolo: 'Nuovo' });
 *   const data = await api.delete('../api/corsi/delete.php?id=1');
 * ============================================
 */

const api = {
    /**
     * Metodo base: esegue una richiesta HTTP con fetch e gestisce la risposta.
     * Tutti gli altri metodi (get, post, put, delete) chiamano questo internamente.
     * 
     * @param {string} url - URL dell'endpoint API
     * @param {object} options - Opzioni di configurazione per fetch (method, headers, body)
     * @returns {Promise<object>} Oggetto JSON restituito dal server
     * @throws Oggetto errore con status, message e data
     */
    async request(url, options = {}) {
        // Header di default: tutte le richieste inviano e ricevono JSON
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',   // Il body della richiesta e' in formato JSON
                'Accept': 'application/json',           // Si aspetta una risposta JSON dal server
            },
        };

        // Unisce le opzioni di default con quelle specifiche della richiesta
        const config = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...options.headers,
            },
        };

        try {
            // Esegue la richiesta HTTP al backend
            const response = await fetch(url, config);
            // Decodifica la risposta JSON del server
            const data = await response.json();

            // Se il server ha risposto con un codice di errore (4xx o 5xx)
            if (!response.ok) {
                throw {
                    status: response.status,                       // Codice HTTP (es. 400, 401, 403, 500)
                    message: data.message || 'Errore del server.', // Messaggio dal backend
                    data: data                                      // Dati completi della risposta
                };
            }

            return data; // Restituisce i dati JSON in caso di successo
        } catch (error) {
            if (error.status) {
                throw error; // Errore HTTP gia' formattato dal blocco sopra
            }
            // Errore di rete (server non raggiungibile, timeout, ecc.)
            throw {
                status: 0,
                message: 'Errore di connessione al server.',
                data: null
            };
        }
    },

    /**
     * Richiesta GET: recupera dati dal server (es. lista corsi, lista assegnazioni)
     */
    async get(url) {
        return this.request(url, { method: 'GET' });
    },

    /**
     * Richiesta POST: crea nuove risorse sul server (es. nuovo corso, nuova assegnazione)
     * @param {object} body - Dati da inviare nel corpo della richiesta
     */
    async post(url, body = {}) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(body), // Converte l'oggetto JS in stringa JSON
        });
    },

    /**
     * Richiesta PUT: aggiorna risorse esistenti (es. modifica corso, completa assegnazione)
     * @param {object} body - Dati aggiornati da inviare
     */
    async put(url, body = {}) {
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify(body),
        });
    },

    /**
     * Richiesta DELETE: elimina risorse dal server (es. elimina corso)
     */
    async delete(url) {
        return this.request(url, { method: 'DELETE' });
    },
};

/* ============================================
 * Utility Globale: Mostra messaggi di alert nella pagina
 * Usata da tutte le pagine per mostrare feedback all'utente
 * (conferme, errori, avvisi) senza ricaricare la pagina.
 * ============================================ */

/**
 * Mostra un messaggio alert temporaneo in un container HTML specificato.
 * 
 * @param {string} containerId - ID dell'elemento HTML dove inserire l'alert
 * @param {string} message - Testo del messaggio da mostrare
 * @param {string} type - Tipo di alert: 'success' (verde), 'danger' (rosso), 'warning' (giallo), 'info' (blu)
 * @param {number} autoDismiss - Millisecondi prima della scomparsa automatica (0 = rimane visibile)
 */
function showAlert(containerId, message, type = 'info', autoDismiss = 5000) {
    const container = document.getElementById(containerId);
    if (!container) return;

    // Crea un nuovo elemento div per l'alert
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;

    // Svuota eventuali alert precedenti e inserisce il nuovo
    container.innerHTML = '';
    container.appendChild(alert);

    // Se autoDismiss > 0, rimuove l'alert dopo il tempo specificato con animazione di fade-out
    if (autoDismiss > 0) {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-8px)';
            setTimeout(() => alert.remove(), 300); // Rimuove dal DOM dopo l'animazione
        }, autoDismiss);
    }
}
