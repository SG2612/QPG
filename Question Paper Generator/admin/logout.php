<?php
session_start();

// Send headers to prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$_SESSION = array();
session_destroy();

// UPDATED to index.php
header("location: index.php");
exit;
?>