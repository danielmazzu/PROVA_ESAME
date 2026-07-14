<?php
// ============================================
// API: Registrazione Utente
// POST /api/auth/register.php
// Body: { username, email, password }
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

$username = trim($data['username'] ?? '');
$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

// Validazione
$errors = [];

if (empty($username) || strlen($username) < 3) {
    $errors[] = 'Username deve essere di almeno 3 caratteri.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email non valida.';
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

    // Verifica se username o email esistono già
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username OR email = :email');
    $stmt->execute(['username' => $username, 'email' => $email]);

    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Username o email già registrati.']);
        exit;
    }

    // Hash password e inserimento
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)');
    $stmt->execute([
        'username' => $username,
        'email'    => $email,
        'password' => $hashedPassword,
        'role'     => 'user'
    ]);

    echo json_encode(['success' => true, 'message' => 'Registrazione completata con successo.']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server.']);
}
