<?php
session_start();
include("db.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['user'])) {
    $chat_with = intval($_GET['user']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $msg = trim($_POST['message']);
        if (!empty($msg)) {
            $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?,?,?)");
            $stmt->bind_param("iis", $user_id, $chat_with, $msg);
            $stmt->execute();
        }
    }

    $stmt = $conn->prepare("SELECT * FROM messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY created_at ASC");
    $stmt->bind_param("iiii", $user_id, $chat_with, $chat_with, $user_id);
    $stmt->execute();
    $messages = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Messages</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .chat-box { border: 1px solid #ccc; padding: 15px; border-radius: 10px; width: 500px; height: 400px; overflow-y: scroll; margin-bottom: 15px; }
        .msg { margin: 8px 0; padding: 8px; border-radius: 5px; max-width: 70%; }
        .me { background: #007bff; color: white; margin-left: auto; text-align: right; }
        .other { background: #f1f1f1; color: black; }
        form { display: flex; gap: 10px; }
        input[type=text] { flex: 1; padding: 10px; border-radius: 5px; border: 1px solid #ccc; }
        button { padding: 10px 15px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h2>💬 Chat</h2>
    <div class="chat-box">
        <?php if (isset($messages)): ?>
            <?php while ($msg = $messages->fetch_assoc()): ?>
                <div class="msg <?= ($msg['sender_id'] == $user_id) ? 'me' : 'other'; ?>">
                    <?= htmlspecialchars($msg['message']); ?><br>
                    <small><?= $msg['created_at']; ?></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Select a user to start chat.</p>
        <?php endif; ?>
    </div>

    <?php if (isset($chat_with)): ?>
    <form method="POST">
        <input type="text" name="message" placeholder="Type your message..." required>
        <button type="submit">Send</button>
    </form>
    <?php endif; ?>
</body>
</html>
