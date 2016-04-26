//MAP RELATED CODE
var map;
var locationsGlobal;
var locationsArray=[];
var markerList=[];//an array with my markers (up to 2);
	
function hideMap(){
	//hide and shrink the div that shows the google map
	$('#mapDiv').attr('style',"height:0px;visibility:hidden");
	//clicking the toggle button again will show the map
	$('#mapButton').attr('onClick',"drawMap(\'"+locationsGlobal+"\')");
	//hides map usage info
	$('#mapInfo').attr('style',"visibility:hidden");
}
	
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
	//clicking the toggle button again will hide the map
	$('#mapButton').attr('onClick',"hideMap()");
	
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
			drawnPoints.push([coordinatesArray[index][0],coordinatesArray[index][1]])
		}
	}
}
//END OF MAP RELATED CODE

function generateGraph(){
	
	
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

    var x = d3.scale.linear()
        .domain([0, 24])
        .range([0, width]);

    // Generate a histogram using twenty four uniformly-spaced bins.
    var data = d3.layout.histogram()
        .bins(x.ticks(24))
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

