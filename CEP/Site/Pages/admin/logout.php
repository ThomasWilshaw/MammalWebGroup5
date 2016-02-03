<?php
session_name("rtProjectsAdmin");
session_start();
$_SESSION['loggedin'] = 0;
header("Location: index.php");
die();

?>