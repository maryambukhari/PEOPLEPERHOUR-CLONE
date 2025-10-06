<?php
session_start();
session_unset();
session_destroy();
echo "<script>localStorage.setItem('flash','Logged out'); window.location='index.php';</script>";
exit;
