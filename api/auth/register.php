<?php
// ============================================
// API: Registrazione Utente
// POST /api/auth/register.php
// Body: { nome, cognome, email, password }
// ============================================

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// Leggi il body JSON
$data = json_decode(file_get_contents('php://input'), true);

$nome     = trim($data['nome'] ?? '');
$cognome  = trim($data['cognome'] ?? '');
$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$role     = 'dipendente'; // Default per registrazione pubblica

// Validazione
$errors = [];

if (empty($nome)) {
    $errors[] = 'Il nome è obbligatorio.';
}
if (empty($cognome)) {
    $errors[] = 'Il cognome è obbligatorio.';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email non valida o mancante.';
}
if (empty($password) || strlen($password) < 6) {
    $errors[] = 'La password deve essere di almeno 6 caratteri.';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $pdo = getConnection();

    // Verifica se email esiste già
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);

    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email già registrata.']);
        exit;
    }

    // Hash password e inserimento
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

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
