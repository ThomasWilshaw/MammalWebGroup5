<?php
session_name("rtProjectsAdmin");
session_start();

if($_SESSION['loggedin'] != 1){
    header("Location: index.php");
    die();
    exit;
}
?>

<!DOCTYPE html>
<html>
	<!-- Admin page for RT Projects, allowing editing of "What's on" and "News" text sections on homepage-->
    <title>RT Admin</title>
	<link rel="stylesheet" href="../../Semantic-UI-CSS/semantic.css">
    <link rel="stylesheet" href="admin.css">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="../../Pages/general/rt.js"></script>
    <script src="admin.js"></script>
  
    <body>
		<nav class="ui fixed menu inverted navbar">
			<a href="../../pages/index.html?test=true" class="brand item">RT Homepage</a>
			<a href="admin.php" class="active item">Admin page</a>
            <div class="right menu">
                <a href="logout.php" class="item">Log Out</a>
            </div>
		</nav>
        
        <div class="ui left aligned page grid">
			<div class="row">
				<div class="column">
					<div class="ui message main">
						<h1 class="ui header">Edit pages</h1>
						<p>
                        Select the section of text you want to edit.
						<br/>
						Click "Submit" to submit the text.
                        <br/>
                        Preview does not display on phones and may not be 100% accurate. 
                        </p>
					</div>
				</div>
			</div>
            
            <div class="two column row">
                
                <div class="six wide column">
                    
                        <select class="ui search dropdown" id="select1" onChange="selectSection()">
                            <option value="0">Section</option>
                            <option value="1">Homepage - News</option>
                            <option value="2">Homepage - What's On</option>
                            <option value="3">Wellbeing - News</option>
                            <option value="4">Dementia - News</option>
                            <option value="5">Men's Shed - News</option>
                            <option value="6">Learning Disability - News</option>
                            <option value="7">Workshops - News</option>
                        </select>
                   
                   
                        <form class="ui form" id = "form1" style = "visibility:hidden">
                            <div class="field">
                                <label id = "label1">Text</label>
                                <textarea id = "textbox1"> Error </textarea>
                            </div>
                            <button class = "ui button" type = "button" onClick ="submitChanges()">Submit</button>
                        </form>
                    
                </div>
                
                <div class="ui vertical divider"></div>
                 
                <div class="eight wide column">
                    
                        <div id="showFrame">
                            <div id="frameHide">
                                <iframe id="frame" src="../index.html"></iframe>   
                            </div>
                        </div>
                </div>  
            </div>          
        </div>	
		<script>		
            window.onload = setup();
		</script>	
    </body>
</html>