<!DOCTYPE html>
<html>
<head>
	<title>
		MammalWeb Dashboard
	</title>

	<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="MW.css">
	<script src="http://code.jquery.com/jquery-latest.min.js"></script>
	<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
	<script src="d3-timeline.js"></script>
	<script src="bootstrap/js/bootstrap.min.js"></script>

	<script src="userDashboardCode.js"></script>

	<style type="text/css">
	    .axis path,
	    .axis line {
	      fill: none;
	      stroke: black;
	      shape-rendering: crispEdges;
	    }

	    .axis text {
	      font-family: sans-serif;
	      font-size: 9px;
	    }

	    .timeline-label {
	      font-family: sans-serif;
	      font-size: 12px;
	    }

	    .coloredDiv {
	      height:20px; width:20px; float:left;
	    }
	  </style>
</head>
<body>

<nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="http://www.mammalweb.org/">Mammal Web</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li><a href="userHome.html">Home</a></li>
        <li class="active"><a href="#">Dashboard<span class="sr-only">(current)</span></a></li>
        <li><a href="userSearch.php">Search</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li><a href="#">Logout</a></li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>

<!--Main element -->

	<div class="container-fluid">
		<div class="row">
			<div class="col-md-1 col-xs-0 margin-shape" id="test">
			<!-- margin-left -->
			</div>
		
			<div class="col-md-10 col-xs-12 form-col">
			<!-- form-middle-col -->
			
			
			
				<!-- an optional portion of the page that displays graphs and information about the most recent filter search-->
					<?php
						set_time_limit(120);
						//sets a 2 minute timeout 
		
						include('config.php');
						$query="";
						//establish connection
						$connection=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);//establishes the sql connections
						
						//escapes all $_REQUEST data against potential sql injection
						makeSecureForSQL($connection);
						//Get species list - $speciesMap holds an associative array of number(int)->species(string) as found in the options table
						$speciesMap=loadSpeciesMap($connection);
						$searchType="0";//1 for images, 2 for sites
						$searchTypeName="";//the name in english of the search type
						$categoryList =array(); //allowed categories for graph gen on images
						$mappedList=array();
						if(isset($_REQUEST['data'])){
							//mode 1 or mode 2 means the page was visited from an image search
							if(($_REQUEST['mode']=="2") or ($_REQUEST['mode']=="1")){
								$categoryList =["site_id","person_id","species","gender","age","habitat_id","water_id"]; //allowed categories for graph gen on images
								$mappedList=["species","gender","age","water_id"];
								$query="SELECT * FROM aggregate INNER JOIN photo ON aggregate.photo_id=photo.photo_id INNER JOIN site ON photo.site_id=site.site_id";
							}
							else{
								//mode 3 onwards means the page was visited from a site search
								if($_REQUEST['mode']=="3"){
									$categoryList =["site_id","person_id","purpose_id","habitat_id","water_id","camera_id","camera_height"]; //allowed categories for graph gen on images
									$mappedList=["purpose_id","water_id","camera_id"];
									$query="SELECT * FROM site";	
								}
							}
							if(($_REQUEST['mode']=="2") or ($_REQUEST['mode']=="1")){
								$searchTypeName="images";
							}
							else{
								$searchTypeName="sites";
							}
							$query=$query." WHERE ".$_REQUEST['data'].";";
							$sqlResults=$connection->query($query);
							$resultsArray=array();//sql results in array form ready for sending to javascript
							foreach($categoryList as $value){
								$tempArray=array();
								$resultsArray[$value]=$tempArray;
							}
							$counter=mysqli_num_rows($sqlResults);
							$locations="";
							if(isset($sqlResults->num_rows) && $sqlResults->num_rows>0){ 
								while($row=$sqlResults->fetch_assoc()){
									if($row["latitude"]!="NULL"){
										$locations=$locations.$row["latitude"]."a".$row["longitude"]."b";
									}//adding each latitude and longitude coordinate to the locations variable
									foreach($row as $key=>$value){
										if(in_array($key,$categoryList)){
											$mappedValue=$value;
											if(in_array($key,$mappedList)){
												if($value>2){
													$mappedValue=$speciesMap[$value];
												}
												else{
													$mappedValue="undefined";
												}
											}
											$tempArray=$resultsArray[$key];
											$mappedValue=str_replace("'"," ",$mappedValue);
											array_push($tempArray,$mappedValue);
											$resultsArray[$key]=$tempArray;
										}
									}
								}
								$resultsJSON=json_encode($resultsArray);
							}
							//the optional section
							echo'<h3>Graphs of your most recent search:</h3>';
								echo'<div class="row">';
									echo'<div class="col-sm-6">';
										echo '<p>'.$counter." ".$searchTypeName.' results were found. ';
										echo'<br>';
										echo'The distribution of your results is show on the map below:';
										echo'</p>';
									echo'</div>';
									echo'<div class="col-sm-6">';
										echo '<p>To generate a bar graph of your recent search relating to a certain attribute, ';
										echo'<br>';
										echo'select an attribute below:';
										echo'</p>';
									echo'</div>';
								echo'</div>';
							
							echo'<div class="row">';
								echo'<div class="col-sm-6">';
									echo'<br>';
									echo '<br>';
									echo'<p id="mapInfo" style="visibility:hidden;">This map shows the geographical distribution of your search results.';
									echo'<br>';
									echo'Left click and draw the map to move';
									echo'<br>';
									echo'Zoom with the buttons in the bottom right of the map, or the mouse wheel';
									echo'</p>';
								echo'</div>';
							
								$attributeValues=$categoryList;
								//the dropdowns menu for a custom graph
									echo'<div class="col-sm-6">';
										echo'<form id="inputs">';
											echo'  <label for="attributeSelect">Select category:</label>';
											echo'  <select multiple name="attribute[]" class="form-control" id="attributeSelect" form="inputs" size=5>';
											foreach($attributeValues as $attributeValue)
												{
													echo'<option value="'.$attributeValue.'">'.$attributeValue.'</option>';										
												}
											echo'  </select>';
										echo'</form>';
										echo"<button type=\"button\" class=\"btn btn-primary\" id=\"graphButton\" onClick='generateGraph(".$resultsJSON.")'>Generate Graph</button>";
									echo'</div>';
							echo'</div>';
							
							echo'<br>';
							
							echo'<div class="row">';
								echo'<div class="col-sm-6" id="mapDiv" style="height:0px;visibility:hidden">';
								//map will be drawn here
								echo'</div>';
								echo'<div class="col-sm-6" id="graphDiv">';
								//custom graph will be drawn here
								echo'</div>';
							echo'</div>';
							echo'<br>';
							echo'<br>';
						}
						$connection->close();
					?>
			
				<h3>Your favourite photos</h3>
				<div id="favouriteImageCarousel" class="carousel slide">
				  <!-- Indicators -->
				  <ol class="carousel-indicators" id="favouriteImageCarouselIndicators">
				  </ol>

				  <!-- Wrapper for slides -->
				  <div class="carousel-inner" role="listbox" id="favouriteImageCarouselInner">
				  </div>

				  <!-- Left and right controls -->
				  <a class="left carousel-control" role="button" data-slide="prev">
				    <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
				    <span class="sr-only">Previous</span>
				  </a>
				  <a class="right carousel-control" role="button" data-slide="next">
				    <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
				    <span class="sr-only">Next</span>
				  </a>
				</div>
				<button type="button" id="carouselPauseButton">Pause</button>

				<h3>Your photo uploads and classifications:</h3>
			    <div id="timeline"></div>
			    <div id="hoverRes">
			    	<div id="hoverDetails"></div>
			      <div id="details"><p>Loading...</p></div>
			    </div>
			</div>
		
			<div class="col-md-1 col-xs-0 margin-shape">
			<!-- margin-left -->
			</div>
		</div>
	</div>


	
	<?php

	function populateCategory($connection,$category,$tableName){
		//returns an array of possible values for an attribute that appear in the database
		//in the named table
		//creating and sending query
		$sql="SELECT DISTINCT ".$category." FROM `".$tableName."`"; 
		//replace "animal" with any other table part of the database initialised in the dbname variable.
		$categoryQuery=$connection->query($sql);
		//using query results
		$categoryArray=array();
		while($attribute=$categoryQuery->fetch_assoc()){
			if(trim($attribute[$category])!=""){
				array_push($categoryArray,$attribute[$category]);
			}
		}
		return $categoryArray;			
	}
	///////////////////////////////////////////////////////////////////////////////////////////////////
	
	function loadSpeciesMap($connection){
		$sql="SELECT option_id,option_name FROM options";  
		$speciesquery=$connection->query($sql);

		$speciesmap=array();
		while($row=$speciesquery->fetch_assoc()){
			$speciesmap[$row["option_id"]]=$row["option_name"];
		}
		$speciesmap[0]="Undefined";
		return $speciesmap;
	}
	/////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////
	/*this function will be used to make sure every variable set in the $_REQUEST array doesn't contain
	injected SQL. It doesn't need to operate on the keys of the array, only the values, as only specific
	keys will be used in the arrayToQuery function to generate the SQL query.
	The $connection parameter is included because the mysqli real_escape_string() function takes this
	as a parameter to see which characters are acceptable*/
	function makeSecureForSQL($myConnection){
		//parses through everything in $_REQUEST
		foreach($_REQUEST as $key=>$value)
		{
			if(is_array($value)){
				foreach($value as $valueKey=>$valueValue)
				{
					//escapes any character that may enable an sql injection attack
					$value[$valueKey]=$myConnection->real_escape_string($valueValue);
				}
			}
			else{
				//escapes any character that may enable an sql injection attack
				$_REQUEST[$key]=$myConnection->real_escape_string($value);
			}
		}
	}
	/////////////////////////////////////////////////////////////////////////////////////////////
	function getCategories($connection,$table){//returns attributes of a table as an array
		//creating and sending query
		$sql="SHOW COLUMNS FROM ".$table;  //replace "animal" with any other table part of the database initialised in the dbname variable.
		$categoryQuery=$connection->query($sql);
		//using query results
		$categoryArray=array();
		while($attribute=$categoryQuery->fetch_assoc()){
			array_push($categoryArray,$attribute['Field']);
		}
		return $categoryArray;
	}
	?>



	 <!--google maps javascript api-->
	 <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC3bN3ZwaXsZ2Eloq_4KOn2CQrXcvL6fIo"></script>
     
	 <?php
	 if(isset($_REQUEST['data'])){
	 //drawing the map (using code from the dashboard js file)	
	 echo'<script>drawMap("'.$locations.'");</script>';
	 }
	 ?>
</body>
</html>