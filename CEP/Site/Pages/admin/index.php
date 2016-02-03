<?php
session_name("rtProjectsAdmin");
session_start();

if(isset($_SESSION['loggedin']) and $_SESSION['loggedin'] == 1){
    header("Location: admin.php");//change!!
    die();
    exit;
}
?>

<!DOCTYPE html>
<html>
	<!-- Admin page for RT Projects, allowing editing of "What's on" and "News" text sections on homepage-->
    <title>RT Admin</title>
	<link rel="stylesheet" href="../../Semantic-UI-CSS/semantic.css">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="../../Pages/general/rt.js"></script>
  
    <body>
		<nav class="ui fixed menu inverted navbar">
			<a href="../../pages/index.html?test=true" class="brand item">RT Homepage</a>
			<a href="admin.php" class="active item">Admin page</a>
		</nav>
        
        <div class="ui page grid">
            <div class="row"></div>
			<div class="row">
				<div class="column">
					<div class="ui message main">
						<h1 class="ui header">Log In</h1>
						<p>Login page to edit News and What's On sections.</p>
					</div>
				</div>
			</div>
            
            <form class="ui form" action="login.php?login=1" method = "post" id="passwordForm">
                <div class="field">
                    <label>Username</label>
                    <input id="username" name="username" placeholder="Username..." type="text">
                </div>
                <div class="field">
                    <label>Password</label>
                    <input id="password" name="password" placeholder="Password..." type="password">
                </div>
                <button class="black ui button" type="submit">Submit</button>
            </form>
        </div>
    </body>
</html>
