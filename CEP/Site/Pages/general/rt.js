function txt_read(pathString, divName){
                var paths = {path : pathString};
				$("#"+divName).load("../general/read.php", $.param(paths));
                console.log("text read");
			}
			
function txt_read_home(pathString, divName){
                var paths = {path : pathString};
				$("#"+divName).load("Pages/general/read.php", $.param(paths));
                console.log("text read");
			}
			

function txt_write(fileLocation,textData){//n.b. the location is the path from the file calling this method to the intended target file
$.post("../../Pages/general/writeText.php",{textData : textData , fileLocation : fileLocation});
}
			

            
function fade(x){
                $("#"+x.id).addClass('fade');
            }
            
function undoFade(x){
    $("#"+x.id).removeClass('fade');
    }

    
function scale(x){
    $("#"+x.id).addClass('scale');
    }
            
function undoScale(x){
    $("#"+x.id).removeClass('scale');
    }