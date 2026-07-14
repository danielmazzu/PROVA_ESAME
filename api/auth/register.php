<?php
// ============================================
// API: Registrazione Utente
// Endpoint: POST /api/auth/register.php
// Body JSON atteso: { "nome": "...", "cognome": "...", "email": "...", "password": "..." }
// Scopo: Registra un nuovo utente con ruolo "dipendente" e password criptata con bcrypt.
// ============================================

header('Content-Type: application/json');

// Accetta solo richieste POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// Legge e decodifica il body JSON della richiesta
$data = json_decode(file_get_contents('php://input'), true);

// Estrae i campi dal body e li pulisce (trim rimuove spazi superflui)
$nome     = trim($data['nome'] ?? '');
$cognome  = trim($data['cognome'] ?? '');
$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$role     = 'dipendente'; // Il ruolo e' sempre "dipendente" per la registrazione pubblica

// Validazione lato server: tutti i campi devono rispettare i requisiti
$errors = [];

if (empty($nome)) {
    $errors[] = 'Il nome è obbligatorio.';
}
if (empty($cognome)) {
    $errors[] = 'Il cognome è obbligatorio.';
}
// filter_var verifica che l'email abbia un formato valido (es. utente@dominio.it)
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email non valida o mancante.';
}
// La password deve avere almeno 6 caratteri per un minimo di sicurezza
if (empty($password) || strlen($password) < 6) {
    $errors[] = 'La password deve essere di almeno 6 caratteri.';
}

// Se ci sono errori di validazione, restituisce 400 con i messaggi concatenati
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $pdo = getConnection();

    // Verifica se l'email e' gia' registrata nel database (unicita' dell'email)
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);

    if ($stmt->fetch()) {
        http_response_code(409); // 409 = Conflict (risorsa gia' esistente)
        echo json_encode(['success' => false, 'message' => 'Email già registrata.']);
        exit;
    }

    // Cripta la password con bcrypt (algoritmo di hashing sicuro e irreversibile)
    // password_hash genera automaticamente un salt unico per ogni password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Inserisce il nuovo utente nel database con prepared statement (anti SQL Injection)
    $stmt = $pdo->prepare('INSERT INTO users (nome, cognome, email, password, role) VALUES (:nome, :cognome, :email, :password, :role)');
    $stmt->execute([
        'nome'     => $nome,
        'cognome'  => $cognome,
        'email'    => $email,
        'password' => $hashedPassword,
        'role'     => $role
    ]);

    echo json_encode(['success' => true, 'message' => 'Registrazione completata con successo.']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server.']);
}
