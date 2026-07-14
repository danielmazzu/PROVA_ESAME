<?php
// ============================================
// API: Login Utente
// POST /api/auth/login.php
// Body: { email, password }
// ============================================

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

// Validazione
if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email e password sono obbligatori.']);
    exit;
}

try {
    $pdo = getConnection();

    // Cerca utente per email
    $stmt = $pdo->prepare('SELECT id, nome, cognome, email, password, role FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Credenziali non valide.']);
        exit;
    }

    // Crea sessione con ruolo
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nome']    = $user['nome'];
    $_SESSION['cognome'] = $user['cognome'];
    $_SESSION['email']   = $user['email'];
    $_SESSION['role']    = $user['role'];

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
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server.']);
}
