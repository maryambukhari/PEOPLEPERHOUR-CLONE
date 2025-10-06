<?php
session_start();
include 'db.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $type = $_POST['type']; // freelancer or client
    $skills = $_POST['skills'] ?? '';
    $experience = $_POST['experience'] ?? '';
    $portfolio = $_POST['portfolio'] ?? '';

    if (!$name || !$email || !$password || !in_array($type,['freelancer','client'])) {
        $err = "Please fill all required fields.";
    } else {
        // check email
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $err = "Email already registered.";
        } else {
            $hash = password_hash($password,PASSWORD_BCRYPT);
            $ins = $conn->prepare("INSERT INTO users (name,email,password,type,skills,experience,portfolio) VALUES (?,?,?,?,?,?,?)");
            $ins->bind_param("sssssss",$name,$email,$hash,$type,$skills,$experience,$portfolio);
            if ($ins->execute()) {
                $uid = $ins->insert_id;
                $_SESSION['user_id'] = $uid;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_type'] = $type;
                echo "<script>localStorage.setItem('flash','Signup successful'); window.location='index.php';</script>";
                exit;
            } else {
                $err = "Error creating account.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Sign Up</title>
<style>
body{font-family:Arial;background:#f6f9fc;margin:0;padding:0}
.wrap{max-width:700px;margin:40px auto;background:#fff;padding:25px;border-radius:12px;box-shadow:0 8px 20px rgba(20,30,60,0.08)}
h2{margin:0 0 10px;color:#1f3a57}
label{display:block;margin:12px 0 6px;font-weight:600;color:#333}
input,textarea,select{width:100%;padding:10px;border-radius:8px;border:1px solid #e2e8f0}
.button{background:#0b74de;color:#fff;padding:10px 14px;border-radius:8px;border:none;cursor:pointer;margin-top:12px}
.small{font-size:13px;color:#666;margin-top:8px}
.error{color:#d04545;margin-top:10px}
.top-row{display:flex;gap:12px}
.top-row > *{flex:1}
@media(max-width:600px){.top-row{flex-direction:column}}
</style>
<script>
function goTo(p){ window.location = p; }
</script>
</head>
<body>
<div class="wrap">
  <h2>Create Account</h2>
  <?php if($err){ echo "<div class='error'>{$err}</div>"; } ?>
  <form method="post" onsubmit="document.getElementById('submitBtn').disabled=true;">
    <div class="top-row">
      <div>
        <label>Name</label>
        <input name="name" required />
      </div>
      <div>
        <label>Email</label>
        <input name="email" type="email" required />
      </div>
    </div>

    <label>Password</label>
    <input name="password" type="password" required />

    <label>Account Type</label>
    <select name="type" required>
      <option value="freelancer">Freelancer</option>
      <option value="client">Client</option>
    </select>

    <label>Skills (comma separated) - Freelancers only</label>
    <input name="skills" />

    <label>Experience / Bio</label>
    <textarea name="experience" rows="3"></textarea>

    <label>Portfolio links (comma separated)</label>
    <input name="portfolio" />

    <button id="submitBtn" class="button" type="submit">Sign Up</button>
  </form>

  <div class="small">Already have an account? <a href="login.php">Login</a></div>
</div>
</body>
</html>
