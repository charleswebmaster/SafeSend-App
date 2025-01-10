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
    echo 'Internal Server Error';
    exit();
}

// Get the message ID from the query string
$message_id = $_GET['id'];

// Fetch the message from the database
$stmt = $pdo->prepare('SELECT * FROM messages WHERE id = ?');
$stmt->execute([$message_id]);
$message_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$message_data) {
    echo 'Message not found.';
    exit();
}

// Check if the message has expired
$current_time = new DateTime();
$expiration_time = new DateTime($message_data['expiration']);

if ($current_time > $expiration_time) {
    echo 'Message has expired.';
    exit();
}

// Check if the message has been viewed more than twice
if ($message_data['view_count'] >= 2) {
    echo 'This link has already been viewed.';
    exit();
}

// Display the message and increment the view count
// Get the entered passphrase from the POST request
$entered_passphrase = $_POST['passphrase'] ?? null;

// Check if the passphrase is required and valid
if ($message_data['passphrase']) {
    // If a passphrase is set, check if the entered passphrase matches the stored passphrase
    if ($entered_passphrase === null || $entered_passphrase !== $message_data['passphrase']) {
        // If the passphrase doesn't match or is not provided, prompt the user for the passphrase
        echo '<form method="post" action="?id=' . $message_id . '">';
        echo '<label for="passphrase">Enter passphrase:</label>';
        echo '<input type="password" name="passphrase" required>';
        echo '<button type="submit">Submit</button>';
        echo '</form>';
        exit(); // Stop further execution to wait for the correct passphrase
    }
}


?>

<!DOCTYPE html>
<html>
<head>
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
    

    <!-- Add your CSS styles here -->
  <!--  <style>
        /* Responsive styles for view.php */
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            margin: 0;
        }

        /* Message container */
        #messageContainer {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Media query for smaller screens */
        @media (max-width: 600px) {
            #messageContainer {
                width: 100%;
                padding: 10px;
            }
        }
    </style> -->
</head>
<body>
    <div id="messageContainer">
        
   <p> <?php    
        // Get the entered passphrase from the POST request
$entered_passphrase = $_POST['passphrase'] ?? null;

// Check if the passphrase is required and valid
if ($message_data['passphrase']) {
    // If a passphrase is set, check if the entered passphrase matches the stored passphrase
    if ($entered_passphrase === null || $entered_passphrase !== $message_data['passphrase']) {
        // If the passphrase doesn't match or is not provided, prompt the user for the passphrase
        echo '<form method="post" action="?id=' . $message_id . '">';
        echo '<label for="passphrase">Enter passphrase:</label>';
        echo '<input type="password" name="passphrase" required>';
        echo '<button type="submit">Submit</button>';
        echo '</form>';
        exit(); // Stop further execution to wait for the correct passphrase
    }
}

// If the passphrase matches, continue with the message display
?></p>
        
        <center>
            Note: <marquee>
                <p style="color:red">The private link that you received works only once, then disappears forever.
Confidential information shared via this tool is not stored anywhere once viewed by the receiving party.
Your message will be deleted after you click the link and view the information being shared.</p>
            </marquee>
        </center>
        <h4>Message:</h4>
       <center> <p><?php echo htmlspecialchars($message_data['message']); ?></p></center>
    </div>

    <?php
    // Increment the view count
    $stmt = $pdo->prepare('UPDATE messages SET view_count = view_count + 1 WHERE id = ?');
    $stmt->execute([$message_id]);
    ?>
    
    <center><button onclick="window.location.href='https://play.google.com/store/apps/details?id=com.techex.safesend'">Create Your Own Private Message</button></center>
</body>
</html>
