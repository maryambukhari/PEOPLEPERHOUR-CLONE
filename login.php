<?php
session_start();
include 'db.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    if (!$email || !$password) $err = "Enter email and password.";
    else {
        $stmt = $conn->prepare("SELECT id,name,password,type FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $stmt->bind_result($id,$name,$hash,$type);
        if ($stmt->fetch()) {
            if (password_verify($password,$hash)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_type'] = $type;
                echo "<script>localStorage.setItem('flash','Login successful'); window.location='index.php';</script>";
                exit;
            } else { $err = "Invalid credentials."; }
        } else { $err = "Invalid credentials."; }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Login</title>
<style>
body{font-family:Arial;background:#f6f9fc}
.box{max-width:420px;margin:80px auto;background:#fff;padding:22px;border-radius:10px;box-shadow:0 8px 20px rgba(20,30,60,0.06)}
label{display:block;margin:12px 0 6px}
input{width:100%;padding:10px;border-radius:8px;border:1px solid #e6edf3}
.btn{background:#0b74de;color:#fff;padding:10px;border-radius:8px;border:none;cursor:pointer;margin-top:12px}
.err{color:#d04545}
</style>
</head>
<body>
<div class="box">
  <h3>Login</h3>
  <?php if($err) echo "<div class='err'>{$err}</div>"; ?>
  <form method="post" onsubmit="document.getElementById('lbtn').disabled=true;">
    <label>Email</label><input name="email" type="email" required />
    <label>Password</label><input name="password" type="password" required />
    <button id="lbtn" class="btn" type="submit">Login</button>
  </form>
  <div style="margin-top:10px">No account? <a href="signup.php">Sign up</a></div>
</div>
</body>
</html>
