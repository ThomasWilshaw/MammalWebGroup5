<!DOCTYPE html>


<?php
    
    $animalid = $_POST["species"][0];
    //echo $animalid;
    //$animalid=json_encode($animalid);
    $url="http://localhost/groupproject5/main/site/getAnimalMonth.php";
    $post = array("animal_id" => $animalid);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $array = curl_exec($ch);
    curl_close($ch);
    $total=sizeof(json_decode($array));
?>

    <a href="selectAnimalMonth.php">Back</a>

<meta charset="utf-8">
<style>

body {
  font: 10px sans-serif;
}

.bar rect {
  fill: steelblue;
  shape-rendering: crispEdges;
}

.bar text {
  fill: #fff;
}

.axis path, .axis line {
  fill: none;
  stroke: #000;
  shape-rendering: crispEdges;
}

</style>
<body>
<font size="4">
<div id="top">

    

    
    </div>
        </font>
<script src="//d3js.org/d3.v3.min.js"></script>
<script>
// each element in value represents one instance for the corresponding histogram value
var values = <?php echo ($array); ?>; 
var info = "Total: ";
var sentence = "This shows classified photos of this animal against month of year";
var length = values.length;
info = info.concat(length);
info = info.concat("<br></br>");
info = info.concat(sentence);
document.getElementById("top").innerHTML = info;
// A formatter for counts.
var formatCount = d3.format(",.0f");

var margin = {top: 10, right: 30, bottom: 30, left: 30},
    width = 960 - margin.left - margin.right,
    height = 500 - margin.top - margin.bottom;

var x = d3.scale.linear()
    .domain([0, 12])
    .range([0, width]);

// Generate a histogram using twenty four uniformly-spaced bins.
var data = d3.layout.histogram()
    .bins(x.ticks(12))
    (values);

var y = d3.scale.linear()
    .domain([0, d3.max(data, function(d) { return d.y; })])
    .range([height, 0]);

var xAxis = d3.svg.axis()
    .scale(x)
    .orient("bottom");

var svg = d3.select("body").append("svg")
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

</script>