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

// Check table columns first
$columnsRes = $conn->query("SHOW COLUMNS FROM users");
$columns = [];
while ($col = $columnsRes->fetch_assoc()) {
    $columns[] = $col['Field'];
}

$selectFields = "id, name, email, created_at";
if (in_array("bio", $columns)) $selectFields .= ", bio";
if (in_array("skills", $columns)) $selectFields .= ", skills";

$stmt = $conn->prepare("SELECT $selectFields FROM users WHERE id=? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    die("❌ User not found!");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($user['name']); ?> - Profile</title>
    <style>
        body { font-family: Arial; margin: 30px; background:#f9f9f9; }
        .card { border: 1px solid #ccc; padding: 20px; border-radius: 10px; width: 420px; background:#fff; box-shadow:0 0 10px rgba(0,0,0,0.1);}
        .btn { padding: 8px 15px; background: #007bff; color: #fff; border: none; border-radius: 5px; text-decoration: none; margin-right:5px; }
        .btn:hover { background:#0056b3; }
    </style>
</head>
<body>
    <div class="card">
        <h2>👤 <?= htmlspecialchars($user['name']); ?></h2>
        <p><b>Email:</b> <?= htmlspecialchars($user['email']); ?></p>
        <p><b>Joined:</b> <?= htmlspecialchars($user['created_at']); ?></p>

        <?php if (isset($user['bio'])): ?>
            <p><b>Bio:</b> <?= !empty($user['bio']) ? htmlspecialchars($user['bio']) : "No bio set"; ?></p>
        <?php endif; ?>

        <?php if (isset($user['skills'])): ?>
            <p><b>Skills:</b> <?= !empty($user['skills']) ? htmlspecialchars($user['skills']) : "No skills added"; ?></p>
        <?php endif; ?>

        <a class="btn" href="edit_profile.php">✏️ Edit Profile</a>
        <a class="btn" href="messages.php?user=<?= $user['id']; ?>">💬 Messages</a>
    </div>
</body>
</html>
