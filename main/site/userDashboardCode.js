/*
Allow access to simple statistics and download of the Mammal Web database (http://www.mammalweb.org/)
Copyright (C) 2016  Freddie Keen, Quentin Lam, Will Taylor, Tom White, 
Thomas Wilshaw
contact: cs-seg5@durham.ac.uk


This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
//MAP RELATED CODE
var map;
var locationsGlobal;
var locationsArray=[];
var markerList=[];//an array with my markers (up to 2);
	
	
function drawMap(locations) {
	//DEALING WITH DATA
	/*getting latitude and longitude data from locations string 
	encoded latalongb for each point as one long string*/
	locationsGlobal=locations.trim();
	locationsArray=locationsGlobal.split("b");
	coordinatesArray=[];
	var latitude;
	var longitude;
	var pointArray;
	for (var index = 0; index < locationsArray.length; ++index) {
		pointArray=locationsArray[index].split("a");
		if((pointArray.length>1))
		{
		latitude=pointArray[0];
		longitude=pointArray[1];
		coordinatesArray.push([latitude,longitude]);
		}
	}
	
	//DRAWING MAP AND TAKING CARE OF PAGE THINGS
	//expand and show the div that will display the google map
	$('#mapDiv').attr('style',"height:400px;visibility:visible");
	//display map usage info
	$('#mapInfo').attr('style',"visibility:visible");
	//create the google map
	map = new google.maps.Map(document.getElementById('mapDiv'), {
	center: {lat: 54.7650, lng: -1.5782},
	zoom: 8
	});
	
	var drawnPoints=[]
	//ADDING MARKERS ON TO MAP
	//draw one marker for each lat lang pair stored 
	for (var index = 0; index < coordinatesArray.length; ++index) {
		//check if there is already a marker for this point
		if(!(drawnPoints.indexOf([coordinatesArray[index][0],coordinatesArray[index][1]])>-1))
		{
			var marker = new google.maps.Marker({
			position: new google.maps.LatLng(coordinatesArray[index][0],coordinatesArray[index][1]),
			map: map,
			});
			drawnPoints.push([coordinatesArray[index][0],coordinatesArray[index][1]]);
		}
	}
}
//END OF MAP RELATED CODE

//drawing the custom graph into the graph div
function generateGraph(values){
		$("#graphDiv").html(" ");
		
		var valuesToUse=$("#attributeSelect").val()
        drawChartSmall(values[valuesToUse],"graphDiv",valuesToUse);
}


//draws a smaller chart with axis markers dependant on the values
function drawChartSmall(values,id,xLabel){
	
	if(values.length>0){
		//dimensions with margin
		var margin = {top: 30, right: 30, bottom: 30, left: 30},
		width = 640 - margin.left - margin.right,
		height = 400 - margin.top - margin.bottom;
		
		//SETTING UP AND HANDLING DATA
		//array to hold unique values from values
		var uniqueValues=[];
		var valueMap=[];//the number of occurences of each value
		for (var index = 0; index < values.length; ++index) {
			//check if this value is already in the value counts list
			if(!(values[index] in valueMap))
			{
				valueMap[values[index]]=1;
			}
			else{
				valueMap[values[index]]=(valueMap[values[index]]+1);
			}
			//check if this value is already in the unique value list
			if(!(uniqueValues.indexOf(values[index])>-1))
			{
				uniqueValues.push(values[index]);
			}
		}
		var dataset = [];
		for (var index=0;index<uniqueValues.length;++index){
			var dictObject = { key: uniqueValues[index] , value: valueMap[uniqueValues[index]]  };
			dataset.push(dictObject);
		}
		//DATA NOW READY TO BE USED

		
		//x scale and x domain
		var xScale = d3.scale.ordinal()
						.domain(d3.range(dataset.length))
						.rangeRoundBands([0, width], 0.1); 
		
		//y scale and domain
		var yScale = d3.scale.linear()
						.domain([0, d3.max(dataset, function(d) {return d.value;})])
						.range([0, height]);
		
		//creating svg object with assigned width and height
		var svg = d3.select("#"+id)
					.append("svg")
					.attr("width", width + margin.left + margin.right)
					.attr("height", height + margin.top + margin.bottom);
		//shortcut to accessing key part of data dictionary objects each time			
		var key = function(d) {
			return d.key;
		};
		//drawing rectangles
		var redShades=["Red","DarkRed","OrangeRed","FireBrick"];
		svg.selectAll("rect")
		   .data(dataset, key)
		   .enter()
		   .append("rect")
		   .attr("x", function(d, i) {
				return xScale(i);
		   })
		   .attr("y", function(d) {
				return height - yScale(d.value);
		   })
		   .attr("width", xScale.rangeBand())
		   .attr("height", function(d) {
				return yScale(d.value);
		   })
		   .attr("fill",function(d,i){return redShades[i%3]});//shaded in different reds
		//adding labels for each bar showing what they are
		svg.selectAll(".xaxis text")
		.data(dataset, key)
		.enter()
		.append("text")
		.text(function(d) {
			return d.key;
		})
		.attr("text-anchor", "middle")
		.attr("x", function(d, i) {
			return xScale(i) + xScale.rangeBand() / 2;
		})
		.attr("y",  height + margin.bottom)
		.attr("font-size", "11px")
		.attr("fill", "black");
		 
		//adding a label on each bar showing its value
		svg.selectAll(".yaxis text")
		.data(dataset, key)
		.enter()
		.append("text")
		.text(function(d) {
			return d.value;
		})
		.attr("text-anchor", "middle")
		.attr("x", function(d, i) {
			return xScale(i) + xScale.rangeBand() / 2;
		})
		.attr("y", function(d) {
			return height - yScale(d.value) - 10;
		})
		.attr("font-size", "11px")
		.attr("fill", "black");
		
		//adding another label on each bar showing its value
		svg.selectAll(".zaxis text")
		.data(dataset, key)
		.enter()
		.append("text")
		.text(function(d) {
			return d.value;
		})
		.attr("text-anchor", "middle")
		.attr("x", function(d, i) {
			return xScale(i) + xScale.rangeBand() / 2;
		})
		.attr("y", function(d) {
			return height - yScale(d.value) + 10;
		})
		.attr("font-size", "11px")
		.attr("fill", "white");
		
		//adding x axis label showing what is being measured on the x axis	
		svg.append("text")
	    .attr("text-anchor", "middle")
	    .attr("x", width/2)
	    .attr("y", height +55)
	    .text(xLabel);
	}
	else{
		$("#"+id).append("<p>No reliable data for that</p>");
	}
}

window.onload = function() {
	
	//Replace this with id of loggegd in user in actual system
	var person_id=182;
	//finding the person id if there is one in the url parameters
	var urlParams=location.search.slice(1);
	var paramsArray=urlParams.split("&");
	$.each(paramsArray, function( index, value ) {
    var tempArray=value.split("=");
	if(tempArray[0]=="user"){
		person_id=tempArray[1];
	}
    });
	


	
	$("#userIDDiv").html("<p>User number "+person_id+"</p>");
	var timelineArray = [
	{label:"uploads", times: []},
	{label:"classifications", times: []}
	];
	var uploads=0;
	var classifications=0;
	$.ajax({
	    url: "getUploadData.php",
	    type: "GET",
	    //This should send the users ID
	    data: "person_id="+person_id,
	    /*cache: false,
	    dataType: "text",
	    jsonp:false,
	    timeout:99999,*/
	    success: function (response) {
	    	if (response != ''){
			    var uploadData=jQuery.parseJSON(response);
			    var numUploads=0;
			    for(var i in uploadData["uploads"]){
			        numUploads+=1;
			    }
			    var numClass=0;
			    for(var i in uploadData["classifications"]){
			        numClass+=1;
			    }

			    jQuery.each(uploadData["uploads"],function(key,value){
				    if(value["num_photos"]>0){
				    	var dStr=value["timestamp"].replace(/ /,"T");
				    	var d=new Date(dStr);
				    	//Javascript date ranges from 0-11 rather than 1-12 QQ
				    	d.setMonth(d.getMonth()+1);

				    	timelineArray[0]["times"][uploads]={"starting_time":d.getTime(),"id":"upload"+(uploads+1),"num_photos":value["num_photos"], "color":"#0033"+(20+Math.round(79*(uploads/numUploads)))};
				    	uploads++;
			    	}
			    });

			    var curDate=null;
			    var count=0;

			    var d;
			    jQuery.each(uploadData["classifications"],function(key,value){
			    	var dStr=value.replace(/ /,"T");
					d=new Date(dStr);

					if(curDate==null){
						//Again, -1 is because js Date month goes from 0-11 whereas values in database are 1-12
						curDate=new Date(d.getFullYear(),d.getMonth()-1,d.getDate());
						count=1;
					}
					else{
						if(curDate.getDate()==d.getDate() && curDate.getMonth()==d.getMonth()-1 && curDate.getFullYear()==d.getFullYear()){
							count+=1;
						}
						else{
							timelineArray[1]["times"][classifications]={"starting_time":d.getTime(),"id":"class"+(classifications+1), "num_photos":count};
							if(count>50){
								timelineArray[1]["times"][classifications]["color"]="#5EFF00";
							}
							else if(count>25){
								timelineArray[1]["times"][classifications]["color"]="#22E600";
							}
							else if(count>7){
								timelineArray[1]["times"][classifications]["color"]="#19A600";
							}
							else{
								timelineArray[1]["times"][classifications]["color"]="#0C5200";
							}
							classifications++;
							count=1;
							curDate=new Date(d.getFullYear(),d.getMonth()-1,d.getDate());
						}
					}
			    });

			    //Necessary to have ending time, otherwise tries to make infinite timeline which goes badly. Could also do when constructing timeline with the .ending(date) method
			    var earliest=null;
			    if(uploads>0){
			       timelineArray[0]["times"][uploads-1]["ending_time"]=new Date().getTime();
			       $("#details").text("You have " + numUploads + " total uploads");
			       earliest=timelineArray[0]["times"][0]["starting_time"]
			    }
			    else{
			    	$("#details").text("You have don\'t have any uploads yet");
			    }

			    if(numClass>0){
			    	//Have to add last day of photos (Copy pasting this feels horrid)
			    	timelineArray[1]["times"][classifications]={"starting_time":d.getTime(),"id":"class"+(classifications+1), "num_photos":count};
					if(count>50){
						timelineArray[1]["times"][classifications]["color"]="#5EFF00";
					}
					else if(count>25){
						timelineArray[1]["times"][classifications]["color"]="#22E600";
					}
					else if(count>7){
						timelineArray[1]["times"][classifications]["color"]="#19A600";
					}
					else{
						timelineArray[1]["times"][classifications]["color"]="#0C5200";
					}
					classifications++;
			       timelineArray[1]["times"][classifications-1]["ending_time"]=new Date().getTime();
			       $("#details").append("<br>You have " + numClass + " total image classifications");
			       var firstClassTime=timelineArray[1]["times"][0]["starting_time"];
			       if(firstClassTime<earliest || earliest==null){
			       		earliest=firstClassTime;
			       }
			    }
			    else{
			    	$("#details").append("<br>You don\'t have any classifications");
			    }
			    buildTimeline(earliest);
	       }
	    }
	  ,error: function(response){
	    $("details").text("An error occurred");
	  }
  });

  var width = 1200;
  var highlightID=null;
  var cycling=true;

  function buildTimeline(start) {
    if(timelineArray[0]["times"].length>0 || timelineArray[1]["times"].length>0){
      var chart = d3.timeline()
      .width(width)
      .margin({left:70, right:50, top:0, bottom:0})
      .display("circle")
      .tickFormat({
        format: d3.time.format("%b %Y"),
        tickTime: d3.time.month,
        tickInterval: 1,
        tickSize: 10
      })
      .rotateTicks(20)
      .beginning(start)
      .showTimeAxisTick()
      .stack()
      .hover(function (d, i, datum) {
      // d is the current rendering object
      // i is the index during d3 rendering
      // datum is the id object
        var div = $('#hoverRes');
        var dDay=new Date(d["starting_time"]).getDate();
        var dMonth=new Date(d["starting_time"]).getMonth();
        var dYear=new Date(d["starting_time"]).getFullYear();

        //Check if elemnt being hovered over represents classifications or uploads
        if(d["id"].slice(0,3)=="upl"){
        	var uNum=parseInt(d["id"].slice(6));

        	var detText= "This was your " + uNum;
        	if(uNum%10==1){
        		detText+="st";
        	}
        	else if(uNum%10==2){
        		detText+="nd";
        	}
        	else if(uNum%10==3){
        		detText+="rd";
        	}
        	else{
	        	detText+="th";
	        }
	        detText+=" upload, uploaded on " + dDay+"/"+ dMonth+"/"+ dYear + " and included " + d["num_photos"] + " photos";
        	div.find('#hoverDetails').text(detText);
        }
        else{
        	div.find('#hoverDetails').text("On " + dDay+"/"+ dMonth+"/"+ dYear + ", you classified " + d["num_photos"] +" photos");
        }
        
      })
      .mouseover(function(d,i,datum){
      	if(highlightID!=null){
      		d3.select("#"+highlightID).style("fill", $("#"+highlightID)[0]["__data__"]["color"]);
      	}
        d3.select("#"+d["id"]).style("fill", "red");
        highlightID=d["id"];
      });
      var svg = d3.select("#timeline").append("svg").attr("width", width)
          .datum(timelineArray).call(chart);
    }
    else{
      $("#details").text("You don't have any uploads or classifications yet");
    }
  }

  //Gets the urls for photos that have been favourited by the user and constructs a carousel from them
  $.ajax({
    url: "getFavouriteURLS.php",
    type: "GET",
    data: "person_id="+person_id,
    success: function (response) {
      if (response != 'no_likes'){
        var urls=jQuery.parseJSON(response);
        //For each photo, we create new html elements on te page that are inside the carousel
        //First entry is different (active) and it's easier to do it outside the loop
        $("#favouriteImageCarouselIndicators").append('<li data-target="#favouriteImageCarousel" data-slide-to="0" class="active"></li>');
        $("#favouriteImageCarouselInner").append('<div class="item active"><img src="'+urls[0]+'" alt="favourite"></div>');
        for(i in urls){
          /*li data-target=\"#favouriteImageCarousel\" data-slide-to=\"" + i + "\"");*/
          if(i>0){
            $("#favouriteImageCarouselIndicators").append('<li data-target="#favouriteImageCarousel" data-slide-to="'+i+'"></li>');
            $("#favouriteImageCarouselInner").append('<div class="item "><img src="'+urls[i]+'" alt="favourite"></div>');
          }
        }
        $("#favouriteImageCarousel").carousel({
        	interval:6000,
        	pause:false
        });

        $("#carouselPauseButton").click(function(){
        	if(!cycling){
        		$("#favouriteImageCarousel").carousel("cycle");
        		$("#carouselPauseButton").html("Pause");
        		cycling=true;
        	}
        	else{
        		$("#favouriteImageCarousel").carousel("pause");
        		$("#carouselPauseButton").html("Start");
        		cycling=false;
        	}
        });

        // Enable Carousel Controls
        $(".left").click(function(){
            $("#favouriteImageCarousel").carousel("prev");
        });

        $(".right").click(function(){
            $("#favouriteImageCarousel").carousel("next");
        });
      }
      else{
        $("#favouriteImageCarousel").removeClass("carousel slide");
        $("#favouriteImageCarousel").html("<p>You don't have any favourited photos yet- click the thumbs up on a photo whilst spotting to save it for later.</p>");
        $("#carouselPauseButton").hide();
      }
    }
  });  
}

$("#marginLeft").attr('style',"visibility:visible");
$("#marginRight").attr('style',"visibility:visible");

