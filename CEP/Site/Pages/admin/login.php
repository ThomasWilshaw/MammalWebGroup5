<?php
session_name("rtProjectsAdmin");
session_start();

if($_GET['login']){
    if($_POST['username'] == 'rtProjectsAdmin' && hash("sha512", $_POST['password']) == '9b1eaf0b0a681ede13317a694e65d4c6a12ddca88fa4f4efe811ac2a52e9dd7c34d36d744c2bbc757dc8abed3f03aeed01f7550c1486c92997227f8aa3183dab'){
        $_SESSION['loggedin'] = 1;
        
        header("Location: admin.php");
        die();
        exit;
    } else header("Location: index.php");
}


?>