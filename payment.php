<?php
// payment.php — robust, auto-adapting to your DB table structure
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';
session_start();

// Validate job_id from GET (use JS redirect to jobs.php if missing)
if (!isset($_GET['job_id']) || !ctype_digit(strval($_GET['job_id']))) {
    echo "<script>alert('Invalid Job Request'); window.location='jobs.php';</script>";
    exit;
}
$job_id = intval($_GET['job_id']);

// Fetch job details (safe prepared statement)
$stmt = $conn->prepare("SELECT j.id, j.title, j.budget, u.name AS client_name 
                        FROM jobs j 
                        JOIN users u ON j.client_id = u.id 
                        WHERE j.id = ? LIMIT 1");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

if (!$job) {
    echo "<script>alert('Job not found'); window.location='jobs.php';</script>";
    exit;
}

/*
  Ensure payments table exists. If it does not exist, create a standard payments table.
  This prevents "unknown column" errors and keeps things consistent.
*/
$check = $conn->query("SHOW TABLES LIKE 'payments'");
if ($check->num_rows === 0) {
    $createSQL = "CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        job_id INT NOT NULL,
        user_id INT DEFAULT NULL,
        amount DECIMAL(10,2) NOT NULL,
        method VARCHAR(100) DEFAULT NULL,
        status ENUM('pending','completed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->query($createSQL);
    // proceed even if create fails; next logic will check columns and show errors if any
}

// Get actual columns present in payments table
$colsRes = $conn->query("SHOW COLUMNS FROM payments");
$cols = [];
while ($row = $colsRes->fetch_assoc()) {
    $cols[] = $row['Field'];
}

// Allowed insertable columns we support (in preferred order)
$allowed = ['job_id','user_id','amount','method','status'];

// Build columns, values and types depending on which columns exist
$insertCols = [];
$insertValues = [];
$types = '';

foreach ($allowed as $col) {
    if (in_array($col, $cols)) {
        $insertCols[] = $col;
        switch ($col) {
            case 'job_id':
                $insertValues[] = $job_id; $types .= 'i'; // integer
                break;
            case 'user_id':
                // if user logged in use their id, else use NULL-equivalent 0
                $insertValues[] = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0; $types .= 'i';
                break;
            case 'amount':
                $insertValues[] = (float)$job['budget']; $types .= 'd'; // double/decimal
                break;
            case 'method':
                // method will be filled from POST when submitting the form (we set default empty here)
                // we'll replace this later if the form posts
                $insertValues[] = ''; $types .= 's';
                break;
            case 'status':
                $insertValues[] = 'pending'; $types .= 's';
                break;
        }
    }
}

// When the user submits the form
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize posted method
    $postedMethod = isset($_POST['method']) ? trim($_POST['method']) : '';

    // Update method value if payments table has 'method' column
    if (in_array('method', $insertCols)) {
        // find index of 'method' in $insertCols and set the corresponding $insertValues entry
        $idx = array_search('method', $insertCols);
        $insertValues[$idx] = $postedMethod !== '' ? $postedMethod : null;
    }

    // Recalculate types and values to bind (because we mutated insertValues)
    // Build placeholders
    if (count($insertCols) === 0) {
        $error = "Payments table has no supported columns. Please check database schema.";
    } else {
        $placeholders = implode(',', array_fill(0, count($insertCols), '?'));
        $colList = implode(',', $insertCols);
        $sql = "INSERT INTO payments ({$colList}) VALUES ({$placeholders})";

        $stmtIns = $conn->prepare($sql);
        if (!$stmtIns) {
            $error = "Prepare failed: " . $conn->error;
        } else {
            // bind params dynamically: build types string matching insertCols
            $typesDynamic = '';
            $bindVals = [];
            foreach ($insertCols as $k => $c) {
                // Lock the type for this column
                if ($c === 'job_id' || $c === 'user_id') { $typesDynamic .= 'i'; $bindVals[] = (int)$insertValues[$k]; }
                elseif ($c === 'amount') { $typesDynamic .= 'd'; $bindVals[] = (float)$insertValues[$k]; }
                else { $typesDynamic .= 's'; $bindVals[] = (string)$insertValues[$k]; }
            }

            // call_user_func_array needs references
            $bind_names = [];
            $bind_names[] = $typesDynamic;
            for ($i = 0; $i < count($bindVals); $i++) {
                // necessary to create references
                $bind_names[] = & $bindVals[$i];
            }
            // bind
            call_user_func_array([$stmtIns, 'bind_param'], $bind_names);

            // execute
            if ($stmtIns->execute()) {
                $success = "✅ Payment recorded successfully.";
                // redirect using JS to jobs.php (user wanted JS redirects)
                echo "<script>alert('Payment recorded successfully'); window.location='jobs.php';</script>";
                exit;
            } else {
                $error = "Execute failed: " . $stmtIns->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Payment — PeoplePerHour Clone</title>
<style>
/* Attractive internal CSS */
body{font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin:0; background: linear-gradient(135deg,#f6f8fb,#e8f1f6); color:#2b3a42;}
.box{max-width:680px;margin:40px auto;background:#fff;padding:28px;border-radius:14px;box-shadow:0 12px 40px rgba(20,30,60,0.08);}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;}
.header h1{font-size:20px;margin:0;color:#123;}
.job-card{background:#fafcff;border-left:5px solid #3b82f6;padding:14px;border-radius:8px;margin-bottom:18px;}
.job-card p{margin:6px 0;color:#123;}
.form-row{margin:12px 0;}
select,input[type=submit]{width:100%;padding:12px;border-radius:8px;border:1px solid #e3e8ef;font-size:15px;}
input[type=submit]{background:#16a34a;color:#fff;border:none;cursor:pointer;font-weight:600;transition:all .18s;}
input[type=submit]:hover{transform:translateY(-2px);background:#22c55e;}
.note{font-size:13px;color:#6b7280;margin-top:8px;}
.error{background:#fff3f3;border-left:4px solid #ff5a5a;padding:10px;border-radius:6px;margin-top:8px;color:#972020;}
.success{background:#f0fff4;border-left:4px solid #18a66f;padding:10px;border-radius:6px;margin-top:8px;color:#0b6b3b;}
.row{display:flex;gap:12px}
@media(max-width:700px){ .row{flex-direction:column} .box{margin:20px;padding:16px} }
</style>
</head>
<body>
<div class="box">
  <div class="header">
    <h1>💳 Make a Payment</h1>
    <div style="font-size:13px;color:#556">Job ID: <?php echo htmlspecialchars($job['id']); ?></div>
  </div>

  <div class="job-card">
    <p><strong><?php echo htmlspecialchars($job['title']); ?></strong></p>
    <p>Client: <?php echo htmlspecialchars($job['client_name']); ?></p>
    <p>Amount: <strong>$<?php echo number_format($job['budget'],2); ?></strong></p>
  </div>

  <?php if(!empty($error)) echo "<div class='error'>".$error."</div>"; ?>
  <?php if(!empty($success)) echo "<div class='success'>".$success."</div>"; ?>

  <form method="post" style="margin-top:10px">
    <div class="form-row">
      <label style="display:block;font-weight:600;margin-bottom:8px;">Select Payment Method</label>

      <?php
      // If payments table does not have 'method' column, hide select and just show info
      if (!in_array('method', $cols)) {
          echo "<div class='note'>Payment method not stored in database (your payments table doesn't have a 'method' column). The system will record amount and job id only.</div>";
      } else {
      ?>
      <select name="method" required>
        <option value="">— Choose method —</option>
        <option value="Cash on Delivery">Cash on Delivery</option>
        <option value="Credit Card (Dummy)">Credit Card (Dummy)</option>
        <option value="PayPal (Dummy)">PayPal (Dummy)</option>
      </select>
      <?php } ?>
    </div>

    <div class="row">
      <input type="submit" value="Confirm Payment">
      <button type="button" onclick="window.location='jobs.php';" style="padding:12px;border-radius:8px;border:1px solid #dbe7f7;background:#fff;cursor:pointer;">Cancel</button>
    </div>

    <div class="note">This is a demo/dummy payment: the system records the payment entry in the database but no real gateway is called.</div>
  </form>
</div>
</body>
</html>
