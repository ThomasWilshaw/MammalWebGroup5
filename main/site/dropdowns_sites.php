<!DOCTYPE html>
 <!--  PHP with functions to populate dropdowns and generate sql queries  -->
<html>

<head>
	<title>PHP DROPDOWN MENU POPULATION FROM TABLES</title>
	<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="MW.css">
	<script	src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

</head>

<body>

	<?php 
		
		include('config.php');
		
		//establish connection
		$connection=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);//establishes the sql connection

		$dropdownCategories=getCategories($connection);
		//An array. uses the function below to get an array of the categories in the table
		
		//Get species list - $speciesMap holds an associative array of number(int)->species(string) as found in the options table
		$speciesMap=loadSpeciesMap($connection);
		
		//visible part of page:
		echo "<h1>Search Sites:</h1><br/>";
		
		/*the dropdowns themselves:
		NOTE, the dropdown for each attribute here is hardcoded
		but this is not necessary.
		since the function getCategories can return an array of attributes in a table
		it would be easy enough to then generate a dropdown for each attribute, outputting similar
		stuff to the repeated echoing below */
		
		//We default to scientist mode if it's not set for site dropdowns
		$mode="s";
		if(isset($_GET["userMode"])){
			$mode=$_GET["userMode"];
		}
		echo '<form id="inputs" role="form" action="site_display.php" method="post">';
		echo '<input type="hidden" name="userMode" value="'.$mode.'" />';//to send user mode onwards, either s (scientist) or u (user)
		echo '<div class="container">';
			
			$speciesValues=populateCategory($connection,"species","animal");
			
			
			echo'<div class="row">';
				echo'<div class="col-sm-4">';
					echo'<label for="speciesSelect">Specific species spotted at site:</label>';
					echo'<select multiple name="species[]" class="form-control" id="speciesSelect" form="inputs" size=5>';
					echo'<option value="any">Any</option>';
					echo'<option value="-1">No Data</option>';
					foreach($speciesValues as $speciesValue)
					{
						$thisField=strip_tags($speciesMap[$speciesValue]);
						if(!($thisField=="Like")){
						echo'<option value="'.$speciesValue.'">'.$thisField.'</option>';
						}
					}
					echo'</select>';
				echo'</div>';
			
				echo'<div class="col-sm-4">';	
					$person_idValues=populateCategory($connection,"person_id","photo");
					echo'<div class="form-group">';
						echo'  <label for="person_idSelect">Specific person_id:</label>';
						echo'  <select multiple name="person_id[]" class="form-control" id="person_idSelect" form="inputs" size=5>';
						echo'<option value="any">Any</option>';
						foreach($person_idValues as $person_idValue)
						{
							$thisField=strip_tags($person_idValue);
							echo'<option value="'.$person_idValue.'">'.$thisField.'</option>';
						}
						echo'  </select>';
					echo'</div>';
				echo'</div>';
			
				echo'<div class="col-sm-4">';
					$site_idValues=populateCategory($connection,"site_id","site");
					echo'<div class="form-group">';
						echo'  <label for="site_idSelect">Specific Site id:</label>';
						echo'  <select multiple name="site_id[]" class="form-control" id="site_idSelect" form="inputs" size=5>';
						echo'<option value="any">Any</option>';
						foreach($site_idValues as $site_idValue)
						{
							$thisField=strip_tags($site_idValue);
							echo'<option value="'.$site_idValue.'">'.$thisField.'</option>';
						}
						echo'  </select>';
					echo'</div>';
				echo'</div>';
			echo'</div>';
			
			echo'<div class="row">';
				echo'<br/>';
			echo'</div>';
			
			echo'<div class="row">';
				echo'<div class="col-sm-4">';	
					echo'  <label id="photoCountLabelLabel">Number of photos taken at site:</label>';
					echo'<br/>';
					echo'  <label for="photoCount1" id="photoCountLabel1">Between</label>';
					echo'  <input type = "number" name="photoCount1" class="form-control" id="photoCount1" form="inputs">';
					echo'  <label for="photoCount2" id="photoCountLabel2">and</label>';
					echo'  <input type = "number" name="photoCount2" class="form-control" id="photoCount2" form="inputs">';
				echo'</div>';
			
				echo'<div class="col-sm-4">';
					echo'  <label id="photoCountLabelLabel">Number of sequences created at site:</label>';
					echo'<br/>';
					echo'  <label for="photoCount1" id="sequenceCountLabel1">Between</label>';
					echo'  <input type = "number" name="sequenceCount1" class="form-control" id="sequenceCount1" form="inputs">';
					echo'  <label for="photoCount2" id="sequenceCountLabel2">and</label>';
					echo'  <input type = "number" name="sequenceCount2" class="form-control" id="sequenceCount2" form="inputs">';
				echo'</div>';
				
				echo'<div class="col-sm-4">';
					echo'<div class="form-group">';
						echo' <label for="contains_human">Humans Present:</label><br/>';
						echo'<input type="radio" name="contains_human" value=1 form="inputs">Yes<br/>';
						echo'<input type="radio" name="contains_human" value=0 form="inputs">No<br/>';
						echo'<input type="radio" name="contains_human" value="any" form="inputs">Any<br/>';
					echo'</div>';
				echo'</div>';
			echo'</div>';
			
			echo'<div class="row">';
				echo'<br/>';
				echo'<div class="col-sm-8">';	
					echo'<div class="form-group">';
						echo' <label for="timeSelection1">Between specific times:</label>';
						echo'<div id="timeSelection1">';
							echo'<div class="col-xs-2">';
								echo'<label for="time1raw1">hour</label>';
								echo'<select class="form-control" id="time1raw1" name="time1raw1" form="inputs" step="1">';
									echo'<option value="00">00</option>';
									echo'<option value="01">01</option>';
									echo'<option value="02">02</option>';
									echo'<option value="03">03</option>';
									echo'<option value="04">04</option>';
									echo'<option value="05">05</option>';
									echo'<option value="06">06</option>';
									echo'<option value="07">07</option>';
									echo'<option value="08">08</option>';
									echo'<option value="09">09</option>';
									echo'<option value="10">10</option>';
									echo'<option value="11">11</option>';
									echo'<option value="12">12</option>';
									echo'<option value="13">13</option>';
									echo'<option value="14">14</option>';
									echo'<option value="15">15</option>';
									echo'<option value="16">16</option>';
									echo'<option value="17">17</option>';
									echo'<option value="18">18</option>';
									echo'<option value="19">19</option>';
									echo'<option value="20">20</option>';
									echo'<option value="21">21</option>';
									echo'<option value="22">22</option>';
									echo'<option value="23">23</option>';
								echo'</select>';
							echo'</div>';
							echo'<div class="col-xs-2">';
								echo'<label for="time1raw2">minute</label>';
								echo'<select class="form-control" id="time1raw2" name="time1raw2" form="inputs" step="1">';
									echo'<option value="00">00</option>';
									echo'<option value="01">01</option>';
									echo'<option value="02">02</option>';
									echo'<option value="03">03</option>';
									echo'<option value="04">04</option>';
									echo'<option value="05">05</option>';
									echo'<option value="06">06</option>';
									echo'<option value="07">07</option>';
									echo'<option value="08">08</option>';
									echo'<option value="09">09</option>';
									echo'<option value="10">10</option>';
									echo'<option value="11">11</option>';
									echo'<option value="12">12</option>';
									echo'<option value="13">13</option>';
									echo'<option value="14">14</option>';
									echo'<option value="15">15</option>';
									echo'<option value="16">16</option>';
									echo'<option value="17">17</option>';
									echo'<option value="18">18</option>';
									echo'<option value="19">19</option>';
									echo'<option value="20">20</option>';
									echo'<option value="21">21</option>';
									echo'<option value="22">22</option>';
									echo'<option value="23">23</option>';
									echo'<option value="24">24</option>';
									echo'<option value="25">25</option>';
									echo'<option value="26">26</option>';
									echo'<option value="27">27</option>';
									echo'<option value="28">28</option>';
									echo'<option value="29">29</option>';
									echo'<option value="30">30</option>';
									echo'<option value="31">31</option>';
									echo'<option value="32">32</option>';
									echo'<option value="33">33</option>';
									echo'<option value="34">34</option>';
									echo'<option value="35">35</option>';
									echo'<option value="36">36</option>';
									echo'<option value="37">37</option>';
									echo'<option value="38">38</option>';
									echo'<option value="39">39</option>';
									echo'<option value="40">40</option>';
									echo'<option value="41">41</option>';
									echo'<option value="42">42</option>';
									echo'<option value="43">43</option>';
									echo'<option value="44">44</option>';
									echo'<option value="45">45</option>';
									echo'<option value="46">46</option>';
									echo'<option value="47">47</option>';
									echo'<option value="48">48</option>';
									echo'<option value="49">49</option>';
									echo'<option value="50">50</option>';
									echo'<option value="51">51</option>';
									echo'<option value="52">52</option>';
									echo'<option value="53">53</option>';
									echo'<option value="54">54</option>';
									echo'<option value="55">55</option>';
									echo'<option value="56">56</option>';
									echo'<option value="57">57</option>';
									echo'<option value="58">58</option>';
									echo'<option value="59">59</option>';
								echo'</select>';
							echo'</div>';
							echo'<div class="col-xs-2">';
								echo'<label for="time1raw3">second</label>';
								echo'<select class="form-control" id="time1raw3" name="time1raw3" form="inputs" step="1">';
									echo'<option value="00">00</option>';
									echo'<option value="01">01</option>';
									echo'<option value="02">02</option>';
									echo'<option value="03">03</option>';
									echo'<option value="04">04</option>';
									echo'<option value="05">05</option>';
									echo'<option value="06">06</option>';
									echo'<option value="07">07</option>';
									echo'<option value="08">08</option>';
									echo'<option value="09">09</option>';
									echo'<option value="10">10</option>';
									echo'<option value="11">11</option>';
									echo'<option value="12">12</option>';
									echo'<option value="13">13</option>';
									echo'<option value="14">14</option>';
									echo'<option value="15">15</option>';
									echo'<option value="16">16</option>';
									echo'<option value="17">17</option>';
									echo'<option value="18">18</option>';
									echo'<option value="19">19</option>';
									echo'<option value="20">20</option>';
									echo'<option value="21">21</option>';
									echo'<option value="22">22</option>';
									echo'<option value="23">23</option>';
									echo'<option value="24">24</option>';
									echo'<option value="25">25</option>';
									echo'<option value="26">26</option>';
									echo'<option value="27">27</option>';
									echo'<option value="28">28</option>';
									echo'<option value="29">29</option>';
									echo'<option value="30">30</option>';
									echo'<option value="31">31</option>';
									echo'<option value="32">32</option>';
									echo'<option value="33">33</option>';
									echo'<option value="34">34</option>';
									echo'<option value="35">35</option>';
									echo'<option value="36">36</option>';
									echo'<option value="37">37</option>';
									echo'<option value="38">38</option>';
									echo'<option value="39">39</option>';
									echo'<option value="40">40</option>';
									echo'<option value="41">41</option>';
									echo'<option value="42">42</option>';
									echo'<option value="43">43</option>';
									echo'<option value="44">44</option>';
									echo'<option value="45">45</option>';
									echo'<option value="46">46</option>';
									echo'<option value="47">47</option>';
									echo'<option value="48">48</option>';
									echo'<option value="49">49</option>';
									echo'<option value="50">50</option>';
									echo'<option value="51">51</option>';
									echo'<option value="52">52</option>';
									echo'<option value="53">53</option>';
									echo'<option value="54">54</option>';
									echo'<option value="55">55</option>';
									echo'<option value="56">56</option>';
									echo'<option value="57">57</option>';
									echo'<option value="58">58</option>';
									echo'<option value="59">59</option>';
								echo'</select>';
							echo'</div>';
							echo'<div class="col-xs-2">';
								echo'<label for="time1raw4">day</label>';
								echo'<select class="form-control" id="time1raw4" name="time1raw4" form="inputs" step="1">';
									echo'<option value="01">01</option>';
									echo'<option value="02">02</option>';
									echo'<option value="03">03</option>';
									echo'<option value="04">04</option>';
									echo'<option value="05">05</option>';
									echo'<option value="06">06</option>';
									echo'<option value="07">07</option>';
									echo'<option value="08">08</option>';
									echo'<option value="09">09</option>';
									echo'<option value="10">10</option>';
									echo'<option value="11">11</option>';
									echo'<option value="12">12</option>';
									echo'<option value="13">13</option>';
									echo'<option value="14">14</option>';
									echo'<option value="15">15</option>';
									echo'<option value="16">16</option>';
									echo'<option value="17">17</option>';
									echo'<option value="18">18</option>';
									echo'<option value="19">19</option>';
									echo'<option value="20">20</option>';
									echo'<option value="21">21</option>';
									echo'<option value="22">22</option>';
									echo'<option value="23">23</option>';
									echo'<option value="24">24</option>';
									echo'<option value="25">25</option>';
									echo'<option value="26">26</option>';
									echo'<option value="27">27</option>';
									echo'<option value="28">28</option>';
									echo'<option value="29">29</option>';
									echo'<option value="30">30</option>';
									echo'<option value="31">31</option>';
								echo'</select>';
							echo'</div>';
							echo'<div class="col-xs-2">';
								echo'<label for="time1raw5">Month</label>';
								echo'<select class="form-control" id="time1raw5" name="time1raw5" form="inputs" step="1">';
									echo'<option value="01">01</option>';
									echo'<option value="02">02</option>';
									echo'<option value="03">03</option>';
									echo'<option value="04">04</option>';
									echo'<option value="05">05</option>';
									echo'<option value="06">06</option>';
									echo'<option value="07">07</option>';
									echo'<option value="08">08</option>';
									echo'<option value="09">09</option>';
									echo'<option value="10">10</option>';
									echo'<option value="11">11</option>';
									echo'<option value="12">12</option>';
								echo'</select>';
							echo'</div>';
							echo'<div class="col-xs-2">';
								echo'<label for="time1raw6">Year</label>';
								echo'<input type="number" min="1950" max="2200" class="form-control" id="time1raw6" name="time1raw6" form="inputs" step="1">';
							echo'</div>';
						echo'</div>';
						echo'<label for="timeSelection2">and:</label>';
						echo'<div id="timeSelection2">';
							echo'<div class="col-xs-2">';
								echo'<label for="time2raw1">hour</label>';
								echo'<select class="form-control" id="time2raw1" name="time2raw1" form="inputs" step="1">';
									echo'<option value="00">00</option>';
									echo'<option value="01">01</option>';
									echo'<option value="02">02</option>';
									echo'<option value="03">03</option>';
									echo'<option value="04">04</option>';
									echo'<option value="05">05</option>';
									echo'<option value="06">06</option>';
									echo'<option value="07">07</option>';
									echo'<option value="08">08</option>';
									echo'<option value="09">09</option>';
									echo'<option value="10">10</option>';
									echo'<option value="11">11</option>';
									echo'<option value="12">12</option>';
									echo'<option value="13">13</option>';
									echo'<option value="14">14</option>';
									echo'<option value="15">15</option>';
									echo'<option value="16">16</option>';
									echo'<option value="17">17</option>';
									echo'<option value="18">18</option>';
									echo'<option value="19">19</option>';
									echo'<option value="20">20</option>';
									echo'<option value="21">21</option>';
									echo'<option value="22">22</option>';
									echo'<option value="23">23</option>';
								echo'</select>';
							echo'</div>';
							echo'<div class="col-xs-2">';
								echo'<label for="time2raw2">minute</label>';
								echo'<select class="form-control" id="time2raw2" name="time2raw2" form="inputs" step="1">';
									echo'<option value="00">00</option>';
									echo'<option value="01">01</option>';
									echo'<option value="02">02</option>';
									echo'<option value="03">03</option>';
									echo'<option value="04">04</option>';
									echo'<option value="05">05</option>';
									echo'<option value="06">06</option>';
									echo'<option value="07">07</option>';
									echo'<option value="08">08</option>';
									echo'<option value="09">09</option>';
									echo'<option value="10">10</option>';
									echo'<option value="11">11</option>';
									echo'<option value="12">12</option>';
									echo'<option value="13">13</option>';
									echo'<option value="14">14</option>';
									echo'<option value="15">15</option>';
									echo'<option value="16">16</option>';
									echo'<option value="17">17</option>';
									echo'<option value="18">18</option>';
									echo'<option value="19">19</option>';
									echo'<option value="20">20</option>';
									echo'<option value="21">21</option>';
									echo'<option value="22">22</option>';
									echo'<option value="23">23</option>';
									echo'<option value="24">24</option>';
									echo'<option value="25">25</option>';
									echo'<option value="26">26</option>';
									echo'<option value="27">27</option>';
									echo'<option value="28">28</option>';
									echo'<option value="29">29</option>';
									echo'<option value="30">30</option>';
									echo'<option value="31">31</option>';
									echo'<option value="32">32</option>';
									echo'<option value="33">33</option>';
									echo'<option value="34">34</option>';
									echo'<option value="35">35</option>';
									echo'<option value="36">36</option>';
									echo'<option value="37">37</option>';
									echo'<option value="38">38</option>';
									echo'<option value="39">39</option>';
									echo'<option value="40">40</option>';
									echo'<option value="41">41</option>';
									echo'<option value="42">42</option>';
									echo'<option value="43">43</option>';
									echo'<option value="44">44</option>';
									echo'<option value="45">45</option>';
									echo'<option value="46">46</option>';
									echo'<option value="47">47</option>';
									echo'<option value="48">48</option>';
									echo'<option value="49">49</option>';
									echo'<option value="50">50</option>';
									echo'<option value="51">51</option>';
									echo'<option value="52">52</option>';
									echo'<option value="53">53</option>';
									echo'<option value="54">54</option>';
									echo'<option value="55">55</option>';
									echo'<option value="56">56</option>';
									echo'<option value="57">57</option>';
									echo'<option value="58">58</option>';
									echo'<option value="59">59</option>';
								echo'</select>';
							echo'</div>';
							echo'<div class="col-xs-2">';
								echo'<label for="time2raw3">second</label>';
								echo'<select class="form-control" id="time2raw3" name="time2raw3" form="inputs" step="1">';
									echo'<option value="00">00</option>';
									echo'<option value="01">01</option>';
									echo'<option value="02">02</option>';
									echo'<option value="03">03</option>';
									echo'<option value="04">04</option>';
									echo'<option value="05">05</option>';
									echo'<option value="06">06</option>';
									echo'<option value="07">07</option>';
									echo'<option value="08">08</option>';
									echo'<option value="09">09</option>';
									echo'<option value="10">10</option>';
									echo'<option value="11">11</option>';
									echo'<option value="12">12</option>';
									echo'<option value="13">13</option>';
									echo'<option value="14">14</option>';
									echo'<option value="15">15</option>';
									echo'<option value="16">16</option>';
									echo'<option value="17">17</option>';
									echo'<option value="18">18</option>';
									echo'<option value="19">19</option>';
									echo'<option value="20">20</option>';
									echo'<option value="21">21</option>';
									echo'<option value="22">22</option>';
									echo'<option value="23">23</option>';
									echo'<option value="24">24</option>';
									echo'<option value="25">25</option>';
									echo'<option value="26">26</option>';
									echo'<option value="27">27</option>';
									echo'<option value="28">28</option>';
									echo'<option value="29">29</option>';
									echo'<option value="30">30</option>';
									echo'<option value="31">31</option>';
									echo'<option value="32">32</option>';
									echo'<option value="33">33</option>';
									echo'<option value="34">34</option>';
									echo'<option value="35">35</option>';
									echo'<option value="36">36</option>';
									echo'<option value="37">37</option>';
									echo'<option value="38">38</option>';
									echo'<option value="39">39</option>';
									echo'<option value="40">40</option>';
									echo'<option value="41">41</option>';
									echo'<option value="42">42</option>';
									echo'<option value="43">43</option>';
									echo'<option value="44">44</option>';
									echo'<option value="45">45</option>';
									echo'<option value="46">46</option>';
									echo'<option value="47">47</option>';
									echo'<option value="48">48</option>';
									echo'<option value="49">49</option>';
									echo'<option value="50">50</option>';
									echo'<option value="51">51</option>';
									echo'<option value="52">52</option>';
									echo'<option value="53">53</option>';
									echo'<option value="54">54</option>';
									echo'<option value="55">55</option>';
									echo'<option value="56">56</option>';
									echo'<option value="57">57</option>';
									echo'<option value="58">58</option>';
									echo'<option value="59">59</option>';
								echo'</select>';
							echo'</div>';
							echo'<div class="col-xs-2">';
								echo'<label for="time2raw4">day</label>';
								echo'<select class="form-control" id="time2raw4" name="time2raw4" form="inputs" step="1">';
									echo'<option value="01">01</option>';
									echo'<option value="02">02</option>';
									echo'<option value="03">03</option>';
									echo'<option value="04">04</option>';
									echo'<option value="05">05</option>';
									echo'<option value="06">06</option>';
									echo'<option value="07">07</option>';
									echo'<option value="08">08</option>';
									echo'<option value="09">09</option>';
									echo'<option value="10">10</option>';
									echo'<option value="11">11</option>';
									echo'<option value="12">12</option>';
									echo'<option value="13">13</option>';
									echo'<option value="14">14</option>';
									echo'<option value="15">15</option>';
									echo'<option value="16">16</option>';
									echo'<option value="17">17</option>';
									echo'<option value="18">18</option>';
									echo'<option value="19">19</option>';
									echo'<option value="20">20</option>';
									echo'<option value="21">21</option>';
									echo'<option value="22">22</option>';
									echo'<option value="23">23</option>';
									echo'<option value="24">24</option>';
									echo'<option value="25">25</option>';
									echo'<option value="26">26</option>';
									echo'<option value="27">27</option>';
									echo'<option value="28">28</option>';
									echo'<option value="29">29</option>';
									echo'<option value="30">30</option>';
									echo'<option value="31">31</option>';
								echo'</select>';
							echo'</div>';
							echo'<div class="col-xs-2">';
								echo'<label for="time2raw5">Month</label>';
								echo'<select class="form-control" id="time2raw5" name="time2raw5" form="inputs" step="1">';
									echo'<option value="01">01</option>';
									echo'<option value="02">02</option>';
									echo'<option value="03">03</option>';
									echo'<option value="04">04</option>';
									echo'<option value="05">05</option>';
									echo'<option value="06">06</option>';
									echo'<option value="07">07</option>';
									echo'<option value="08">08</option>';
									echo'<option value="09">09</option>';
									echo'<option value="10">10</option>';
									echo'<option value="11">11</option>';
									echo'<option value="12">12</option>';
								echo'</select>';
							echo'</div>';
							echo'<div class="col-xs-2">';
								echo'<label for="time2raw6">Year</label>';
								echo'<input type="number" min="1950" max="2200" class="form-control" id="time2raw6" name="time2raw6" form="inputs" step="1">';
							echo'</div>';
						echo'</div>';
					echo'</div>';
				echo'</div>';
				
			
				echo'<div class="col-sm-4">';
					$habitat_idValues=populateCategory($connection,"habitat_id","site");
					echo'<div class="form-group">';
						echo'  <label for="site_idSelect">Specific habitats:</label>';
						echo'  <select multiple name="habitat_id[]" class="form-control" id="habitat_idSelect" form="inputs" size=5>';
						echo'<option value="any">Any</option>';
						foreach($habitat_idValues as $habitat_idValue)
						{
							$thisField=strip_tags($speciesMap[$habitat_idValue]);
							echo'<option value="'.$habitat_idValue.'">'.$thisField.'</option>';
						}
						echo'</select>';
					echo'</div>';			
				echo'</div>';
				
				echo'<div class="col-sm-4">';
					echo'<p id="mapInfo" style="visibility:hidden;">-Left click on the map to place a corner of your selection area.';
					echo'<br/>';
					echo'-Click again to place the opposite corner and draw the box.';
					echo'<br/>';
					echo'-A third click clears the selection box';
					echo'</p>';
				echo'</div>';	
				
			echo'</div>';
			
			echo'<div class="row">';
				echo'<div class="col-sm-4">';
					echo'<label id="latLabelLabel">Latitude:</label>';
					echo'<br/>';
					echo'<label for="lat1" id="latLabel1">Between</label>';
					echo'<input type = "number" step="any" name="lat1" class="form-control" id="lat1" form="inputs">';
					echo'<label for="lat2" id="latLabel2">and</label>';
					echo'<input type = "number" step="any" name="lat2" class="form-control" id="lat2" form="inputs">';
				echo'</div>';
				
				echo'<div class="col-sm-4">';
					echo'<label id="longLabelLabel">Longitude:</label>';
					echo'<br/>';
					echo'<label for="long1" id="longLabel1">Between</label>';
					echo'<input type = "number" step="any" name="long1" class="form-control" id="long1" form="inputs">';
					echo'<label for="long2" id="longLabel2">and</label>';
					echo'<input type = "number" step="any" name="long2" class="form-control" id="long2" form="inputs">';
				echo'</div>';
				
				echo'<div class="col-sm-4">';
					echo'<br/><br/>';
					echo'<button type="button" class="btn btn-secondary" id="mapButton" onClick="drawMap()">Toggle Map</button>';
				echo'</div>';
			echo'</div>';
			
			echo'<br/>';
			
			echo'<div class="row">';
				echo'<div class="col-sm-16" id="mapDiv" style="height:0px;visibility:hidden">';
					
				echo'</div>';
			echo'</div>';
			
			echo'<br/><br/>';
			
			echo '<input type="submit" class="btn btn-primary btn-lg btn-block" value="Submit"> ';	
			echo'<br/>';			
		echo '</div>';
		echo '</form>';
		
		$connection->close();//closes connection when you're done with it
		
		/* Four custom functions: 
		
		getCategories: returns all the attributes in a table as an array
		
		populateCategory: returns an array of all unique values for a given category in the database, as an array
		
		loadSpeciesMap: from will's code - using the options table to convert integer values stored in tables to 
		the relevant string
				e.g. 2 might represent a species of "bear" 
				
		*/
		/////////////////////////////////////////////////////////////////////////////////////////////
		function getCategories($connection){//returns attributes of a table as an array
			//creating and sending query
			$sql="SHOW COLUMNS FROM `animal`";  //replace "animal" with any other table part of the database initialised in the dbname variable.
			$categoryQuery=$connection->query($sql);
			//using query results
			$categoryArray=array();
			while($attribute=$categoryQuery->fetch_assoc()){
				array_push($categoryArray,$attribute['Field']);
			}
			return $categoryArray;
		}
		////////////////////////////////////////////////////////////////////////////////////////////////////	
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
		

	
	<script>
		
		//FEATURE SUPPORT CHECKING
		//checking if the datetime-local element is supported in the current browser
		//if it is, replace the contents of the timeSelection section with datetime-local version
		
		//create a test input 
		var i = document.createElement("input");
		//set i to the date time local type
		i.setAttribute("type", "datetime-local");

		if(i.type =="text"){//if it is not supported, datetime-local changes to text type
			console.log("browser does not support datetime_local inputs");
		}
		else{//if it is supported, load it instead of the basic time selection boxes
			$("#timeSelection1").replaceWith('<div id="timeSelection1"><input type="datetime-local" class="form-control" id="time1Input" name="time1" form="inputs" step="1"></div>');
			$("#timeSelection2").replaceWith('<div id="timeSelection2"><input type="datetime-local" class="form-control" id="time2Input" name="time2" form="inputs" step="1"></div>');
		}
	
	
	
	   //map related functions
		var map;
		var markerList=[];//an array with my markers (up to 2);
		
		function hideMap(){
			//hide and shrink the div that shows the google map
			$('#mapDiv').attr('style',"height:0px;visibility:hidden");
			//clicking the toggle button again will show the map
			$('#mapButton').attr('onClick',"drawMap()");
			//hides map usage info
			$('#mapInfo').attr('style',"visibility:hidden");
		}
		
		//used to clear an array of markers from my google map
		function clearArrayOfMarkers(markerArray){
			for (item in markerArray) {
				markerArray[item].setMap(null);
			}
		}
		
		//these will hold the coordinates of the box when selected
		var latitude1=0;
		var longitude1=0;
		var latitude2=0;
		var longitude2=0;
		
		//this variable is my polyline
		var myPolyLine;
		
		function drawMap() {
			//expand and show the div that will display the google map
			$('#mapDiv').attr('style',"height:800px;visibility:visible");
			//display map usage info
			$('#mapInfo').attr('style',"visibility:visible");
			//create the google map
			map = new google.maps.Map(document.getElementById('mapDiv'), {
			center: {lat: 54.7650, lng: -1.5782},
			zoom: 8
			});
	
			var pointsClicked=0;
			//listener for click event
            google.maps.event.addListener(map, "click", function(event) {
				//user clicks at a specific latitude and longitude
                var latitude = event.latLng.lat();
                var longitude = event.latLng.lng();
				
				if(pointsClicked==0)//if this is the first point, the top left of the box
				{
				//places marker there
                  var marker = new google.maps.Marker({
                        position: new google.maps.LatLng(latitude,longitude),
                        map: map,
                  });
				  markerList.push(marker);
				  latitude1=latitude;
				  longitude1=longitude;
				  //update top left lat long values, all to 6 d.p.
				  document.getElementById("lat1").value = latitude.toFixed(6);
				  document.getElementById("long1").value = longitude.toFixed(6);
				  pointsClicked+=1;//count this point as clicked
				}
				   
				else
				{
					if(pointsClicked==1)//if this is the second, the bottom right
					{
					//places marker there
					  var marker = new google.maps.Marker({
							position: new google.maps.LatLng(latitude,longitude),
							map: map,
					  });
					  markerList.push(marker);
					  latitude2=latitude;
					  longitude2=longitude;
					  //update bottom right lat long values, all to 6 d.p.
					  document.getElementById("lat2").value = latitude.toFixed(6);
					  document.getElementById("long2").value = longitude.toFixed(6);
					  pointsClicked+=1;//count this point as clicked
					  
					  //draw the box
					  assignBox();
					}   
					else{
						pointsClicked=0;//resets points clicked if something went wrong or over clicked
						clearArrayOfMarkers(markerList);//clearing markers from map
						markerList=[];
						removeBox();
					}
				}	
            });
			
			//draw my box between selected points on map
			function assignBox(){
				var corners=[
					{lat: latitude1, lng: longitude1},
					{lat: latitude1, lng: longitude2},
					{lat: latitude2, lng: longitude2},
					{lat: latitude2, lng: longitude1},
					{lat: latitude1, lng: longitude1}
				  ];
				myPolyLine = new google.maps.Polyline({
					path: corners,
					strokeColor: '#FF0000',
					strokeOpacity: 1.0,
					strokeWeight: 1
				  });
				myPolyLine.setMap(map);
			}
			
			//undraw my box between selected points on map
			function removeBox(){
				myPolyLine.setMap(null);
			}
			
		
			//clicking the toggle button again will hide the map
			$('#mapButton').attr('onClick',"hideMap()");
			}
	</script>
	 <!--google maps javascript api-->
	 <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC3bN3ZwaXsZ2Eloq_4KOn2CQrXcvL6fIo" async defer></script>
</body>
</html>
