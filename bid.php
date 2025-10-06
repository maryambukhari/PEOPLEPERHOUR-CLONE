<?php
include 'db.php';
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='freelancer'){
    echo "<script>alert('Only freelancers can bid on jobs!');window.location.href='jobs.php';</script>";
    exit;
}

if(!isset($_GET['job_id'])){
    echo "<script>alert('Invalid Job!');window.location.href='jobs.php';</script>";
    exit;
}
$job_id = intval($_GET['job_id']);

if($_SERVER['REQUEST_METHOD']==='POST'){
    $amount = $_POST['amount'];
    $desc = trim($_POST['description']);
    $freelancer_id = $_SESSION['user_id'];

    $stmt=$conn->prepare("INSERT INTO bids (job_id,freelancer_id,amount,description) VALUES (?,?,?,?)");
    $stmt->bind_param("iids",$job_id,$freelancer_id,$amount,$desc);

    if($stmt->execute()){
        echo "<script>alert('Bid placed successfully');window.location.href='jobs.php';</script>";
        exit;
    } else {
        $error="Error placing bid!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Place Bid</title>
<style>
body{background:#f4f4f4;font-family:Arial;}
.box{width:450px;margin:60px auto;background:#fff;padding:20px;border-radius:10px;box-shadow:0 3px 7px rgba(0,0,0,0.2);}
h2{text-align:center;color:#2c3e50;margin-bottom:15px;}
input,textarea{width:100%;padding:10px;margin:10px 0;border:1px solid #ccc;border-radius:6px;}
button{background:#27ae60;color:#fff;width:100%;padding:10px;border:none;border-radius:6px;cursor:pointer;}
button:hover{background:#2ecc71;}
</style>
</head>
<body>
<div class="box">
<h2>Place Your Bid</h2>
<?php if(!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="post">
    <input type="number" step="0.01" name="amount" placeholder="Bid Amount (USD)" required>
    <textarea name="description" placeholder="Proposal/Message" required></textarea>
    <button type="submit">Submit Bid</button>
</form>
</div>
</body>
</html>
