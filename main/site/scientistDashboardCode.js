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

window.onload=function(){
    $("#timeButton").click(function(){
        
        $.ajax({
            url: "getAnimalWithTime.php",
            type: "GET",
            //This should send the users ID
            data: "animal_id="+$("#speciesSelectTime").val(),
            success: function (response){
                if (response != ''){
                    $("#timeChart").html(" ");
                    var values=jQuery.parseJSON(response);
                    drawChart(values,"timeChart");
                }
            }
        });
    });

    $("#monthButton").click(function(){       
        $.ajax({
            url: "getAnimalMonth.php",
            type: "GET",
            //This should send the users ID
            data: "animal_id="+$("#speciesSelectMonth").val(),
            success: function (response){
                if (response != ''){
                    $("#monthChart").html(" ");
                    var values=jQuery.parseJSON(response);
                    
                    drawChart(values,"monthChart");
                }
            }
        });
    });

    $("#clearTimeButton").click(function(){
        $("#timeChart").html(" ");
    });

    $("#clearMonthButton").click(function(){
        $("#monthChart").html(" ");
    });
}

function drawChart(values,id){
    var formatCount = d3.format(",.0f");

    var margin = {top: 10, right: 30, bottom: 30, left: 30},
        width = 960 - margin.left - margin.right,
        height = 500 - margin.top - margin.bottom;

    if("timeChart"==id){
    	var topD=24;
    }
    else{
    	var topD=12;
    }
    var x = d3.scale.linear()
        .domain([0, topD])
        .range([0, width]);

    // Generate a histogram using twenty four uniformly-spaced bins.
    var data = d3.layout.histogram()
        .bins(x.ticks(topD))
        (values);

    var y = d3.scale.linear()
        .domain([0, d3.max(data, function(d) { return d.y; })])
        .range([height, 0]);

    var xAxis = d3.svg.axis()
        .scale(x)
        .orient("bottom");

    var svg = d3.select("#"+id).append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    var bar = svg.selectAll(".bar")
        .data(data)
        .enter().append("g")
        .attr("class", "bar")
        .attr("transform", function(d) { return "translate(" + x(d.x) + "," + y(d.y) + ")"; });

    bar.append("rect")
        .attr("x", 1)
        .attr("width", x(data[0].dx) - 1)
        .attr("height", function(d) { return height - y(d.y); });

    bar.append("text")
        .attr("dy", ".75em")
        .attr("y", 6)
        .attr("x", x(data[0].dx) / 2)
        .attr("text-anchor", "middle")
        .text(function(d) { return formatCount(d.y); });

    svg.append("g")
        .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .call(xAxis);
}

//draws a smaller chart with axis markers dependant on the values
function drawChartSmall(values,id,xLabel){
	
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
	
$("#marginLeft").attr('style',"height:500px");
$("#marginRight").attr('style',"height:1000px");
}
