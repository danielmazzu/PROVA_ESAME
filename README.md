# Linee Guida e Regole di Sviluppo - Academy Aziendale

Questo documento definisce le regole fondamentali da seguire durante lo sviluppo dell'applicazione per la gestione dei percorsi formativi dell'Academy aziendale.

## Regole Fondamentali di Sviluppo

1. **Codice Snello e Chiaro**:
   - Scrivere codice pulito, modulare e facilmente manutenibile.
   - Evitare duplicazioni (principio DRY - Don't Repeat Yourself).
   - Utilizzare nomi esplicativi per variabili, funzioni e componenti.

2. **Commenti Brevi e Significativi**:
   - Inserire commenti brevi e concisi per spiegare la logica complessa o il motivo di determinate scelte progettuali. Il codice in sé dovrebbe comunque rimanere il più auto-esplicativo possibile.

3. **Controllo Preventivo sulle Modifiche e Cancellazioni (Safe Editing)**:
   - **Cruciale**: Prima di apportare qualsiasi modifica o cancellazione a porzioni di codice o testo esistente, effettuare *sempre* un controllo preventivo.
   - Verificare attentamente le righe esatte che si stanno per rimuovere o sovrascrivere per accertarsi che siano effettivamente quelle corrette, riducendo al minimo il rischio di regressioni o perdita accidentale di logica funzionante.

## Regole Specifiche per il Progetto (Full Stack CSR + API Backend)

Oltre alle regole di base, per questa specifica tipologia di applicazione devono essere rispettate le seguenti direttive:

4. **Netta Separazione Frontend / Backend**:
   - **Frontend (Client-Side Rendering)**: Deve occuparsi solo della logica di visualizzazione, della navigazione, dell'interfaccia utente (UI) e del consumo delle API.
   - **Backend**: Deve agire come un servizio API puro. Non deve generare HTML (no Server-Side Rendering), ma limitarsi a processare richieste, eseguire logica di business e restituire dati strutturati (es. JSON).

5. **Sicurezza, Autenticazione e Autorizzazione**:
   - Tutte le rotte API private devono essere protette da meccanismi di autenticazione (es. token JWT o sessioni sicure).
   - **Zero Trust**: Il backend deve sempre verificare il ruolo dell'utente (Dipendente vs Referente Academy) prima di consentire un'operazione o restituire dati, senza fidarsi ciecamente di ciò che viene richiesto dal frontend.
   - Il frontend deve adattare l'interfaccia in base al ruolo, ma il controllo finale spetta al backend.

6. **Validazione Rigorosa dei Dati**:
   - **Lato Server (Obbligatoria)**: Tutti i dati in ingresso tramite API devono essere validati prima di interagire con il database per prevenire malfunzionamenti o vulnerabilità.
   - **Lato Client (Consigliata)**: Duplicare le validazioni principali sui form frontend per offrire un feedback immediato e migliorare l'esperienza utente.

7. **Gestione degli Errori e Feedback all'Utente**:
   - Il backend deve restituire codici di stato HTTP appropriati (200, 400, 401, 403, 404, 500) accompagnati da messaggi d'errore strutturati.
   - Il frontend deve intercettare e gestire tutti gli stati dell'applicazione: visualizzare loader durante i caricamenti, mostrare alert o toast di conferma per le operazioni riuscite e comunicare in modo chiaro gli errori (ad es. "Credenziali errate").

8. **Documentazione API e Dati di Test**:
   - Mantenere chiara la struttura degli endpoint API. Strumenti come Swagger/OpenAPI o collection esportate (Postman, Insomnia) sono incoraggiati.
   - Il database deve prevedere script di popolamento (seeding) con dati realistici, necessari per testare tutti i casi d'uso, le ricerche e le statistiche previste dai requisiti.
