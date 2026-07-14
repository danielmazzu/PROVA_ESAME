<?php
// ============================================
// API: Statistiche Academy
// Endpoint: GET /api/statistiche/academy.php
// Filtri opzionali: ?mese=2026-07&categoria=Informatica&utente_id=2
// Scopo: Calcola le statistiche aggregate dei corsi raggruppate per mese e categoria.
//        Per ogni gruppo restituisce: numero assegnazioni, completamenti e percentuale.
//        Accessibile solo ai referenti.
// ============================================

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

session_start();

// Controllo autorizzazione: solo i referenti possono visualizzare le statistiche
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'referente') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accesso negato. Solo i referenti possono visualizzare le statistiche.']);
    exit;
}

// Accetta solo richieste GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Query di aggregazione con GROUP BY: raggruppa le assegnazioni per mese e categoria
    // TO_CHAR formatta la data nel formato "YYYY-MM" (es. "2026-07")
    // COUNT conta tutte le assegnazioni del gruppo
    // SUM con CASE conta solo quelle con stato "Completato"
    $query = "
        SELECT 
            TO_CHAR(a.data_assegnazione, 'YYYY-MM') as mese,
            c.categoria as categoria,
            COUNT(a.id) as numeroAssegnazioni,
            SUM(CASE WHEN a.stato = 'Completato' THEN 1 ELSE 0 END) as numeroCompletamenti
        FROM assegnazioni a
        JOIN corsi c ON a.corso_id = c.id
        WHERE 1=1
    ";
    
    $params = [];

    // Filtro opzionale per mese (formato YYYY-MM)
    if (!empty($_GET['mese'])) {
        $query .= " AND TO_CHAR(a.data_assegnazione, 'YYYY-MM') = :mese";
        $params['mese'] = $_GET['mese'];
    }

    // Filtro opzionale per categoria del corso
    if (!empty($_GET['categoria'])) {
        $query .= " AND c.categoria = :categoria";
        $params['categoria'] = $_GET['categoria'];
    }

    // Filtro opzionale per uno specifico dipendente
    if (!empty($_GET['utente_id'])) {
        $query .= " AND a.utente_id = :utente_id";
        $params['utente_id'] = (int)$_GET['utente_id'];
    }

    // Raggruppa per mese e categoria, ordina per mese decrescente
    $query .= " GROUP BY mese, categoria ORDER BY mese DESC, categoria ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatta i risultati calcolando la percentuale di completamento
    // NOTA: PostgreSQL converte gli alias non quotati in minuscolo,
    // quindi si usa 'numeroassegnazioni' (tutto minuscolo) per accedere ai risultati
    $formatted = array_map(function($row) {
        $assegnazioni = (int)($row['numeroassegnazioni'] ?? 0);
        $completamenti = (int)($row['numerocompletamenti'] ?? 0);
        // Calcola la percentuale arrotondata a 2 decimali (evita divisione per zero)
        $percentuale = $assegnazioni > 0 ? round(($completamenti / $assegnazioni) * 100, 2) : 0;
        
        return [
            'mese' => $row['mese'],
            'categoria' => $row['categoria'],
            'numeroAssegnazioni' => $assegnazioni,
            'numeroCompletamenti' => $completamenti,
            'percentualeCompletamento' => $percentuale
        ];
    }, $results);

    echo json_encode(['success' => true, 'data' => $formatted]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server: ' . $e->getMessage()]);
}
