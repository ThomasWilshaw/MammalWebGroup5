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
            <div class="row"></div>
			<div class="row">
				<div class="column">
					<div class="ui message main">
						<h1 class="ui header">Edit pages</h1>
						<p>
                        Select the section of text you want to edit.
						<br/>
						Click "Submit" to submit the text.
                        </p>
					</div>
				</div>
			</div>
            <div class="row">
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
            </div>
			<div class = "row">
				<form class="ui form" id = "form1" style = "visibility:hidden">
					<div class="field">
						<label id = "label1">Text</label>
						<textarea id = "textbox1"> Error </textarea>
					</div>
					<button class = "ui button" type = "button" onClick ="submitChanges()">Submit</button>
				</form>
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
					
				if(section==1){
					var sectionName="Homepage News";
					var fileLocation="../../Pages/dynamicText/homePage/news.txt";
					}
					
				if(section==2){
					var sectionName="What's On";
					var fileLocation="../../Pages/dynamicText/homePage/whatsOn.txt";
				}
				if(section==3){
					var sectionName="Wellbeing News";
					var fileLocation="../../Pages/dynamicText/wellbeing/news.txt";
				}
				if(section==4){
					var sectionName="Dementia News";
					var fileLocation="../../Pages/dynamicText/dementia/news.txt";
				}
				if(section==5){
					var sectionName="Men's Shed News";
					var fileLocation="../../Pages/dynamicText/mensShed/news.txt";
				}
				if(section==6){
					var sectionName="Learning Disability News";
					var fileLocation="../../Pages/dynamicText/learningDisability/news.txt";
				}
				if(section==7){
					var sectionName="Workshops News";
					var fileLocation="../../Pages/dynamicText/workshops/news.txt";
				}
				
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
				//Uses rt.js for txt_write function.
				if(section==1){//if news on selected
                    txt_write("../../Pages/dynamicText/homePage/news.txt",textData);
				}
				if(section==2){//if whatsOn selected
                    txt_write("../../Pages/dynamicText/homePage/whatsOn.txt",textData);	
				}
				if(section==3){//if wellbeing selected
                    txt_write("../../Pages/dynamicText/wellbeing/news.txt",textData);
				}
				if(section==4){//if dementia selected
                    txt_write("../../Pages/dynamicText/dementia/news.txt",textData);
				}
				if(section==5){//if mensShed on selected
                    txt_write("../../Pages/dynamicText/mensShed/news.txt",textData);
				}
				if(section==6){//if learningDisability selected
                    txt_write("../../Pages/dynamicText/learningDisability/news.txt",textData);
				}
				if(section==7){//if workshops selected
                    txt_write("../../Pages/dynamicText/workshops/news.txt",textData);
				}
            //console.log("Changes submitted");
			//console.log(textData);
			window.alert("Changes submitted.");
            location.reload();
			}		
		</script>	
    </body>
</html>