<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';
session_start();

// Query jobs
$sql = "SELECT j.id, j.title, j.description, j.budget, u.name as client 
        FROM jobs j 
        JOIN users u ON j.client_id = u.id
        ORDER BY j.id DESC";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
<title>Browse Jobs | PeoplePerHour Clone</title>
<style>
body{margin:0;padding:0;font-family:Arial;background:#f4f7fb;}
.header{background:#2c3e50;color:#fff;padding:20px;text-align:center;}
.header h1{margin:0;font-size:32px;}
.container{width:85%;margin:30px auto;}
.job{background:#fff;padding:20px;margin-bottom:20px;border-radius:15px;
     box-shadow:0 6px 15px rgba(0,0,0,0.1);transition:.3s;}
.job:hover{transform:translateY(-5px);}
h3{margin:0;color:#2c3e50;}
p{margin:8px 0;color:#555;}
.badge{display:inline-block;background:#27ae60;color:#fff;padding:5px 12px;border-radius:20px;font-size:13px;}
.actions a{display:inline-block;margin:5px 5px 0 0;padding:10px 15px;border-radius:8px;text-decoration:none;
           color:#fff;font-weight:bold;transition:.3s;}
.actions a.bid{background:#2980b9;}
.actions a.bid:hover{background:#3498db;}
.actions a.pay{background:#27ae60;}
.actions a.pay:hover{background:#2ecc71;}
.no-jobs{text-align:center;color:#999;font-size:18px;margin-top:50px;}
</style>
</head>
<body>
<div class="header">
    <h1>💼 Available Jobs</h1>
    <p>Find freelance projects and start bidding</p>
</div>

<div class="container">
<?php if($res->num_rows > 0): ?>
    <?php while($job = $res->fetch_assoc()): ?>
    <div class="job">
        <h3><?php echo htmlspecialchars($job['title']); ?></h3>
        <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
        <p><span class="badge">Budget: $<?php echo $job['budget']; ?></span></p>
        <p><b>Client:</b> <?php echo htmlspecialchars($job['client']); ?></p>
        <div class="actions">
            <a href="bid.php?job_id=<?php echo $job['id']; ?>" class="bid">💡 Bid Now</a>
            <a href="payment.php?job_id=<?php echo $job['id']; ?>" class="pay">💳 Pay Now</a>
        </div>
    </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="no-jobs">🚫 No jobs available at the moment.</div>
<?php endif; ?>
</div>
</body>
</html>
