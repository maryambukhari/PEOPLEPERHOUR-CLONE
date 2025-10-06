<?php
include 'db.php';
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='client'){
    echo "<script>alert('Only clients can post jobs!');window.location.href='index.php';</script>";
    exit;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $budget = $_POST['budget'];
    $client_id = $_SESSION['user_id'];

    $stmt=$conn->prepare("INSERT INTO jobs (client_id,title,description,budget,status) VALUES (?,?,?,?, 'open')");
    $stmt->bind_param("issd",$client_id,$title,$desc,$budget);

    if($stmt->execute()){
        echo "<script>alert('Job posted successfully');window.location.href='jobs.php';</script>";
        exit;
    } else {
        $error="Error posting job!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Post Job</title>
<style>
body{background:#f4f4f4;font-family:Arial;}
.box{width:450px;margin:60px auto;background:#fff;padding:20px;border-radius:10px;box-shadow:0 3px 7px rgba(0,0,0,0.2);}
h2{text-align:center;color:#2c3e50;margin-bottom:15px;}
input,textarea{width:100%;padding:10px;margin:10px 0;border:1px solid #ccc;border-radius:6px;}
button{background:#2980b9;color:#fff;width:100%;padding:10px;border:none;border-radius:6px;cursor:pointer;}
button:hover{background:#3498db;}
</style>
</head>
<body>
<div class="box">
<h2>Post a Job</h2>
<?php if(!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="post">
    <input type="text" name="title" placeholder="Job Title" required>
    <textarea name="description" placeholder="Job Description" required></textarea>
    <input type="number" step="0.01" name="budget" placeholder="Budget (USD)" required>
    <button type="submit">Post Job</button>
</form>
</div>
</body>
</html>
