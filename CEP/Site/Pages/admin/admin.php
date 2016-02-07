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
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="../../Pages/general/rt.js"></script>
  
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
                    
                        <style>
                            /*#wrap { width: 90vw; height: 60vw; padding: 0; position:relative; left:0px; top:0px; overflow: hidden; }*/
                            #frame { width: 90vw; height: 60vw; /*position:relative; left:0px; top:0px;*/ }
                            #frame { -ms-zoom: 0.5; -moz-transform: scale(0.5); -moz-transform-origin: 0px 0; -o-transform: scale(0.5); -o-transform-origin: 0 0; -webkit-transform: scale(0.5); -webkit-transform-origin: 0 0; }
                        </style>
                        <div id="showFrame">
                            <div id="frameHide">
                                <iframe id="frame" src="../index.html"></iframe>
                                
                            </div>
                        </div>
                   
                </div>  
            </div>
           
        </div>	
		<script>		
		function selectSection(){//displays box to edit a section, when a section is selected
			var section=$("#select1").val();//the section selected for edit
			if(section==0){//if no section selected
				$("#form1").css('visibility', 'hidden');//don't show the text box
			}
			else{//if a section is selected
				$("#form1").css('visibility', 'visible');//show the text box
                
                var frameElement = document.getElementById("frame");//get iframe element
					
				if(section==1){
                    frameElement.contentWindow.location.href = "../index.html";
					var sectionName="Homepage News";
					var fileLocation="../../Pages/dynamicText/homePage/news.txt";
					}
					
				if(section==2){
                    frameElement.contentWindow.location.href = "../index.html";
					var sectionName="What's On";
					var fileLocation="../../Pages/dynamicText/homePage/whatsOn.txt";
				}
				if(section==3){
                    frameElement.contentWindow.location.href = "../wellbeing/wellbeing.html";
					var sectionName="Wellbeing News";
					var fileLocation="../../Pages/dynamicText/wellbeing/news.txt";
				}
				if(section==4){
                    frameElement.contentWindow.location.href = "../dementia/dementia.html";
					var sectionName="Dementia News";
					var fileLocation="../../Pages/dynamicText/dementia/news.txt";
				}
				if(section==5){
                    frameElement.contentWindow.location.href = "../mensShed/mensShed.html";
					var sectionName="Men's Shed News";
					var fileLocation="../../Pages/dynamicText/mensShed/news.txt";
				}
				if(section==6){
                    frameElement.contentWindow.location.href = "../learningDisability/learningDisability.html";
					var sectionName="Learning Disability News";
					var fileLocation="../../Pages/dynamicText/learningDisability/news.txt";
				}
				if(section==7){
                    frameElement.contentWindow.location.href = "../workshops/workshops.html";
					var sectionName="Workshops News";
					var fileLocation="../../Pages/dynamicText/workshops/news.txt";
				}
                var el = document.getElementById("frameHide");//get frame div
                el.style.display = "block";//show frame div
				
				$("#label1").replaceWith(
				'<label id = "label1">'+sectionName+'</label>'
				);				
				$.post("../../Pages/general/readAdmin.php/",{path : fileLocation},function(data)//gets the relevant text
					{
						console.log("recieved response:");
						console.log(data);
						responseArray=data;
						stringData="";
						for (var i = 0; i < responseArray.length; i++) {
							stringData=stringData+responseArray[i];
							stringData=stringData+"\n";
						}
						$("#textbox1").replaceWith(
						'<textarea id = "textbox1">'
						+stringData
						+'</textarea>'
						);
					console.log(stringData);
					}
					,"json");	
			}
		}
		function submitChanges(){//submits the changes made to the text to the file.
			var section=$("#select1").val();//the section selected for edit
			var textData=$("#textbox1").val();//the text to put in this section
            var frameElement = document.getElementById("frame"); //get iframe element
				//Uses rt.js for txt_write function.
				if(section==1){//if news on selected
                    txt_write("../../Pages/dynamicText/homePage/news.txt",textData);
                    frameElement.contentWindow.location.href = "../index.html"; //reload iframe
				}
				if(section==2){//if whatsOn selected
                    txt_write("../../Pages/dynamicText/homePage/whatsOn.txt",textData);	
                    frameElement.contentWindow.location.href = "../index.html";
				}
				if(section==3){//if wellbeing selected
                    txt_write("../../Pages/dynamicText/wellbeing/news.txt",textData);
                    frameElement.contentWindow.location.href = "../wellbeing/wellbeing.html";
				}
				if(section==4){//if dementia selected
                    txt_write("../../Pages/dynamicText/dementia/news.txt",textData);
                    frameElement.contentWindow.location.href = "../dementia/dementia.html";
				}
				if(section==5){//if mensShed on selected
                    txt_write("../../Pages/dynamicText/mensShed/news.txt",textData);
                    frameElement.contentWindow.location.href = "../mensShed/mensShed.html";
				}
				if(section==6){//if learningDisability selected
                    txt_write("../../Pages/dynamicText/learningDisability/news.txt",textData);
                    frameElement.contentWindow.location.href = "../learningDisability/learningDisability.html";
				}
				if(section==7){//if workshops selected
                    txt_write("../../Pages/dynamicText/workshops/news.txt",textData);
                    frameElement.contentWindow.location.href = "../workshops/workshops.html";
				}
            //console.log("Changes submitted");
			//console.log(textData);
			window.alert("Changes submitted.");
            //location.reload();
			}
        function hideOnPhone(){
            console.log("running");
            if( /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {
                var el = document.getElementById("showFrame");
                el.style.display = "none";
                console.log("hiden");
            }
        }
        function setup(){
            hideOnPhone();
            var el = document.getElementById("frameHide");
            el.style.display = "none";
        }
        window.onload = setup();
		</script>	
    </body>
</html>