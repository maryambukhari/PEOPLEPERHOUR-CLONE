<?php include 'db.php'; ?>
<?php
// Featured freelancers
$freelancers = $conn->query("SELECT * FROM users WHERE type='freelancer' LIMIT 6");
// Latest jobs
$jobs = $conn->query("SELECT * FROM jobs ORDER BY created_at DESC LIMIT 6");
?>
<!DOCTYPE html>
<html>
<head>
<title>PeoplePerHour Clone</title>
<style>
/* Global */
body{margin:0;font-family:'Segoe UI',Arial,sans-serif;background:#f9f9fb;color:#333;}
a{text-decoration:none;color:inherit;}
h1,h2,h3{margin:0;}

/* Header */
header{background:linear-gradient(135deg,#2c3e50,#34495e);color:#fff;padding:20px;text-align:center;}
header h1{font-size:32px;margin-bottom:10px;}
header .nav{margin-top:10px;}
header button{background:#27ae60;color:#fff;border:none;padding:10px 20px;margin:0 5px;border-radius:5px;cursor:pointer;transition:.3s;}
header button:hover{background:#2ecc71;transform:translateY(-2px);}

/* Hero Section */
.hero{background:linear-gradient(120deg,#6a11cb 0%,#2575fc 100%);color:#fff;text-align:center;padding:60px 20px;}
.hero h2{font-size:36px;margin-bottom:20px;}
.hero p{font-size:18px;margin-bottom:20px;}
.hero .search-box{max-width:500px;margin:20px auto;display:flex;background:#fff;border-radius:50px;overflow:hidden;box-shadow:0 4px 8px rgba(0,0,0,0.2);}
.hero input{flex:1;padding:12px;border:none;outline:none;font-size:16px;}
.hero button{background:#2575fc;color:#fff;padding:12px 20px;border:none;cursor:pointer;transition:.3s;}
.hero button:hover{background:#6a11cb;}

/* Container */
.container{width:90%;margin:40px auto;}
.section-title{font-size:24px;margin-bottom:20px;color:#2c3e50;border-left:5px solid #27ae60;padding-left:10px;}

/* Categories */
.categories{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:15px;}
.category{background:#fff;padding:20px;border-radius:12px;text-align:center;cursor:pointer;transition:.3s;box-shadow:0 4px 6px rgba(0,0,0,0.1);}
.category:hover{background:#27ae60;color:#fff;transform:scale(1.05);}

/* Cards */
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;}
.card{background:#fff;border-radius:12px;padding:20px;box-shadow:0 4px 6px rgba(0,0,0,0.1);transition:.3s;}
.card:hover{transform:translateY(-5px);box-shadow:0 6px 12px rgba(0,0,0,0.2);}
.card h3{color:#2c3e50;}
.card p{font-size:14px;margin:10px 0;}
.card button{background:#27ae60;color:#fff;padding:8px 15px;border:none;border-radius:5px;cursor:pointer;}
.card button:hover{background:#2ecc71;}

/* Footer */
footer{background:#2c3e50;color:#fff;text-align:center;padding:20px;margin-top:40px;}
</style>
<script>
function goTo(page){ window.location.href = page; }
</script>
</head>
<body>

<!-- Header -->
<header>
  <h1>PeoplePerHour Clone</h1>
  <div class="nav">
    <button onclick="goTo('signup.php')">Sign Up</button>
    <button onclick="goTo('login.php')">Login</button>
    <button onclick="goTo('post_job.php')">Post a Job</button>
  </div>
</header>

<!-- Hero Section -->
<div class="hero">
  <h2>Hire Experts. Get Work Done.</h2>
  <p>Connect with skilled freelancers for any project you have in mind.</p>
  <div class="search-box">
    <input type="text" placeholder="Search jobs or freelancers...">
    <button>Search</button>
  </div>
</div>

<!-- Categories -->
<div class="container">
  <h2 class="section-title">Popular Categories</h2>
  <div class="categories">
    <div class="category" onclick="goTo('jobs.php?cat=web')">Web Development</div>
    <div class="category" onclick="goTo('jobs.php?cat=design')">Graphic Design</div>
    <div class="category" onclick="goTo('jobs.php?cat=writing')">Writing & Translation</div>
    <div class="category" onclick="goTo('jobs.php?cat=marketing')">Digital Marketing</div>
    <div class="category" onclick="goTo('jobs.php?cat=video')">Video & Animation</div>
    <div class="category" onclick="goTo('jobs.php?cat=business')">Business</div>
  </div>
</div>

<!-- Featured Freelancers -->
<div class="container">
  <h2 class="section-title">Featured Freelancers</h2>
  <div class="cards">
    <?php while($row = $freelancers->fetch_assoc()){ ?>
      <div class="card">
        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
        <p><?php echo htmlspecialchars($row['skills']); ?></p>
        <button onclick="goTo('profile.php?id=<?php echo $row['id']; ?>')">View Profile</button>
      </div>
    <?php } ?>
  </div>
</div>

<!-- Latest Jobs -->
<div class="container">
  <h2 class="section-title">Latest Jobs</h2>
  <div class="cards">
    <?php while($job = $jobs->fetch_assoc()){ ?>
      <div class="card">
        <h3><?php echo htmlspecialchars($job['title']); ?></h3>
        <p><?php echo substr($job['description'],0,100); ?>...</p>
        <p><b>Budget:</b> $<?php echo $job['budget']; ?></p>
        <button onclick="goTo('jobs.php?id=<?php echo $job['id']; ?>')">View Job</button>
      </div>
    <?php } ?>
  </div>
</div>

<!-- Footer -->
<footer>
  <p>&copy; <?php echo date('Y'); ?> PeoplePerHour Clone. All rights reserved.</p>
</footer>

</body>
</html>
