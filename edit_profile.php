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

// ✅ Check which columns exist
$colsRes = $conn->query("SHOW COLUMNS FROM users");
$cols = [];
while ($c = $colsRes->fetch_assoc()) {
    $cols[] = $c['Field'];
}

$selectFields = "id, name, email";
if (in_array("bio", $cols)) $selectFields .= ", bio";
if (in_array("skills", $cols)) $selectFields .= ", skills";

$stmt = $conn->prepare("SELECT $selectFields FROM users WHERE id=? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name']);
    $email  = trim($_POST['email']);
    $bio    = isset($_POST['bio']) ? trim($_POST['bio']) : null;
    $skills = isset($_POST['skills']) ? trim($_POST['skills']) : null;

    if (!empty($name) && !empty($email)) {
        // Build dynamic query
        if (in_array("bio", $cols) && in_array("skills", $cols)) {
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, bio=?, skills=? WHERE id=?");
            $stmt->bind_param("ssssi", $name, $email, $bio, $skills, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
            $stmt->bind_param("ssi", $name, $email, $user_id);
        }

        if ($stmt->execute()) {
            $success = "✅ Profile updated successfully!";
            $stmt = $conn->prepare("SELECT $selectFields FROM users WHERE id=? LIMIT 1");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $error = "❌ Something went wrong!";
        }
    } else {
        $error = "⚠️ Name and Email cannot be empty!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f3f4f6; padding:30px; }
        .form-box { max-width:500px; margin:auto; background:#fff; padding:25px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1);}
        h2 { margin-bottom:20px; text-align:center; }
        label { font-weight:bold; display:block; margin:10px 0 5px; }
        input, textarea { width:100%; padding:10px; border:1px solid #ccc; border-radius:5px; margin-bottom:15px; }
        button { background:#007bff; color:#fff; padding:12px; border:none; border-radius:5px; cursor:pointer; width:100%; font-size:16px; }
        button:hover { background:#0056b3; }
        .msg { text-align:center; padding:10px; margin-bottom:15px; border-radius:5px; }
        .success { background:#d4edda; color:#155724; }
        .error { background:#f8d7da; color:#721c24; }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>✏️ Edit Profile</h2>
        
        <?php if ($success): ?><div class="msg success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="msg error"><?= $error ?></div><?php endif; ?>
        
        <form method="POST">
            <label>Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']); ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>

            <?php if (in_array("bio", $cols)): ?>
                <label>Bio:</label>
                <textarea name="bio" rows="4"><?= htmlspecialchars($user['bio'] ?? ""); ?></textarea>
            <?php endif; ?>

            <?php if (in_array("skills", $cols)): ?>
                <label>Skills (comma separated):</label>
                <input type="text" name="skills" value="<?= htmlspecialchars($user['skills'] ?? ""); ?>">
            <?php endif; ?>

            <button type="submit">💾 Save Changes</button>
        </form>
    </div>
</body>
</html>
