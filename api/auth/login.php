<?php
// ============================================
// API: Login Utente
// Endpoint: POST /api/auth/login.php
// Body JSON atteso: { "email": "...", "password": "..." }
// Scopo: Autentica un utente verificando le credenziali e crea una sessione PHP.
// ============================================

// Imposta l'header della risposta come JSON per tutte le risposte
header('Content-Type: application/json');

// Accetta solo richieste POST (il login invia dati sensibili, non deve usare GET)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // 405 = Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit;
}

// Importa la funzione getConnection() per connettersi al database
require_once __DIR__ . '/../../config/database.php';

// Legge il corpo della richiesta HTTP e lo decodifica da JSON a un array PHP
$data = json_decode(file_get_contents('php://input'), true);

// Estrae e pulisce i campi dal body della richiesta
$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

// Validazione lato server: entrambi i campi sono obbligatori
if (empty($email) || empty($password)) {
    http_response_code(400); // 400 = Bad Request
    echo json_encode(['success' => false, 'message' => 'Email e password sono obbligatori.']);
    exit;
}

try {
    $pdo = getConnection();

    // Cerca l'utente nel database tramite email (prepared statement per prevenire SQL Injection)
    $stmt = $pdo->prepare('SELECT id, nome, cognome, email, password, role FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    // Verifica: l'utente esiste E la password inserita corrisponde all'hash salvato nel DB
    // password_verify() confronta la password in chiaro con l'hash bcrypt
    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401); // 401 = Unauthorized
        echo json_encode(['success' => false, 'message' => 'Credenziali non valide.']);
        exit;
    }

    // Login riuscito: crea una sessione PHP per mantenere l'utente autenticato
    // I dati salvati in $_SESSION sono disponibili in tutte le pagine successive
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nome']    = $user['nome'];
    $_SESSION['cognome'] = $user['cognome'];
    $_SESSION['email']   = $user['email'];
    $_SESSION['role']    = $user['role'];    // 'dipendente' o 'referente'

    // Risposta di successo con i dati dell'utente (senza la password)
    echo json_encode([
        'success' => true,
        'message' => 'Login effettuato con successo.',
        'user' => [
            'id'      => $user['id'],
            'nome'    => $user['nome'],
            'cognome' => $user['cognome'],
            'email'   => $user['email'],
            'role'    => $user['role']
        ]
    ]);

} catch (PDOException $e) {
    // Errore generico del database (non espone dettagli tecnici al client)
    http_response_code(500); // 500 = Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Errore del server.']);
}
