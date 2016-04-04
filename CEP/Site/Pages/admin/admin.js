function selectSection(){//displays box to edit a section, when a section is selected
    var section=$("#select1").val();//the section selected for edit
    if(section==0){//if no section selected
        $("#form1").css('visibility', 'hidden');//don't show the text box
    }
    else{//if a section is selected
        $("#form1").css('visibility', 'visible');//show the text box
        
        var frameElement = document.getElementById("frame");//get iframe element
            
        if(section==1){
            frameElement.contentWindow.location.href = "../../index.html";//load preview
            var sectionName="Homepage News";
            var fileLocation="../../Pages/dynamicText/homePage/news.txt";
            }
            
        if(section==2){
            frameElement.contentWindow.location.href = "../../index.html";
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
}

function hideOnPhone(){
    //if on phone/tablet hide preview completely
    console.log("running");
    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {
        var el = document.getElementById("showFrame");
        el.style.display = "none";
        console.log("hiden");
    }
}

function setup(){
    hideOnPhone();
    //hide preview until page selected
    var el = document.getElementById("frameHide");
    el.style.display = "none";
}