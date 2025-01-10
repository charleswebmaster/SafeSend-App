<?php
// Database connection parameters
$host = 'localhost'; // Change to your database host
$dbname = 'charulpd_linkgen'; // Change to your database name
$username = 'charulpd_linkgen'; // Change to your database username
$password = 'Insta@=+{}{?G4224'; // Change to your database password
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    // Set error mode for PDO to throw exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Log and respond with an error if the database connection fails
    error_log($e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Retrieve JSON request data
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Invalid request data']);
    exit();
}

// Extract message, passphrase, and expiration time from the data
$message = $data['message'] ?? null;
$passphrase = $data['passphrase'] ?? null;
$expiration = $data['expiration'] ?? null;

if (!$message || !$expiration) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Message and expiration time are required']);
    exit();
}

// Calculate the expiration date and time
$expiration_datetime = new DateTime();
$expiration_datetime->modify("+$expiration seconds");

// Insert message, passphrase, expiration time, and view count (initialized to 0) into the database
try {
    $stmt = $pdo->prepare('INSERT INTO messages (message, passphrase, expiration, view_count) VALUES (?, ?, ?, ?)');
    $stmt->execute([$message, $passphrase, $expiration_datetime->format('Y-m-d H:i:s'), 0]);
    // Get the last inserted ID
    $message_id = $pdo->lastInsertId();
} catch (PDOException $e) {
    // Log and respond with an error if the insertion fails
    error_log($e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Failed to save message to the database']);
    exit();
}

// Generate the link for accessing the message
$link = "https://apps.techex.com.ng/safesend/linkgen/view.php?id=$message_id";

// Return the link as a JSON response
header('Content-Type: application/json');
echo json_encode(['link' => $link]);
?>
