<!DOCTYPE html>
<html>
<head>
	<title>
		MammalWeb Dashboard
	</title>

	<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="MW.css">
	<meta charset="utf-8">
    <style>

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
</head>
<body>
	<script src="http://code.jquery.com/jquery-latest.min.js"></script>
	<script src="scientistDashboardCode.js"></script>
	<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>

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
	        <li><a href="scientistHome.html">Home</a></li>
	        <li class="active"><a href="#">Dashboard<span class="sr-only">(current)</span></a></li>
	        <li><a href="scientistSearch.html">Search</a></li>
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
				<div class="col-md-2 col-xs-0 margin-shape">
				<!-- margin-left -->
					stuff
				</div>
			
				<div class="col-md-8 col-xs-12 form-col">
				<!-- form-middle-col -->
					<h6>Only aggregate classification which are of high certainty are used for these graphs</h6>

					<h3>Select animal to see what time of day photos of them are captured:</h3>
					<div class="container">
					    <div class="row">
							<div class="col-sm-4">
							    <form id="inputs" role="form">
									<select name="species[]" class="form-control" id="speciesSelectTime" form="inputs" size=6>
										<?php		
											include('config.php');
											
											//establish connection
											$connection=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);//establishes the sql connection

											//Get species list - $speciesMap holds an associative array of number(int)->species(string) as found in the options table
											$speciesMap=loadSpeciesMap($connection);
											$speciesValues=populateCategory($connection,"species","animal");

											foreach($speciesValues as $speciesValue){
												$thisField=strip_tags($speciesMap[$speciesValue]);
												if(!($thisField=="Like")){
													echo'<option value="'.$speciesValue.'">'.$thisField.'</option>';
												}
											}
										?>
							  		</select>
							  		<button type="button" id="timeButton">Submit</button>
							  		<button type="button" id="clearTimeButton">Clear</button>
							  	</form>
							</div>
						</div>
					</div>

					<div id="timeChart">
					</div>

					<h3>Select animal to see what time of year photos of them are captured:</h3>
					<div class="container">
					    <div class="row">
							<div class="col-sm-4">
							    <form id="inputs" role="form">
									<select name="species[]" class="form-control" id="speciesSelectMonth" form="inputs" size=6>
										<?php		
											include('config.php');
											
											$speciesMap=loadSpeciesMap($connection);
											$speciesValues=populateCategory($connection,"species","animal");

											foreach($speciesValues as $speciesValue){
												$thisField=strip_tags($speciesMap[$speciesValue]);
												if(!($thisField=="Like")){
													echo'<option value="'.$speciesValue.'">'.$thisField.'</option>';
												}
											}

						
											$connection->close();//closes connection when you're done with it
										?>
							  		</select>
							  		<button type="button" id="monthButton">Submit</button>
							  		<button type="button" id="clearMonthButton">Clear</button>
							  	</form>
							</div>
						</div>
					</div>

					<div id="monthChart">
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
						?>

				</div>
			
				<div class="col-md-2 col-xs-0 margin-shape">
				<!-- margin-left -->
					stuff
				</div>
			</div>
		</div>

	<!--Footer-->
		
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12" id="footer">
					footer
				</div>
			</div>
		</div>

	</body>
</html>