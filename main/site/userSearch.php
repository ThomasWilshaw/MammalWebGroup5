<!DOCTYPE html>
<!--
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
-->
<html>
<head>
	<title>
		MammalWeb Search
	</title>

	<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="MW.css">
	<script src="http://code.jquery.com/jquery-latest.min.js"></script>
	
</head>
<body>
<?php 
	//passing user variable
	if(isset($_REQUEST['user'])){
		$userID=$_REQUEST['user'];
	}
	else{
		$userID=182;//default for testing
	}

?>
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
		<?php
        echo'<li><a href="userHome.php?user='.$userID.'&userMode=u">Home</a></li>';
        echo'<li><a href="userDashboard.php?user='.$userID.'&userMode=u">Dashboard</a></li>';
		?>
        <li class="active"><a href="#">Search<span class="sr-only">(current)</span></a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li><a href="login.html">Logout</a></li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>

<!--Main element -->

	<div class="container-fluid">
		<div class="row">
			<div class="col-md-1 col-xs-0 margin-shape">
			<!-- margin-left -->
			</div>
		
			<div class="col-md-10 col-xs-12 form-col" id="dropdowns">
			<!-- form-middle-col -->
				Loading search...
			</div>
		
			<div class="col-md-1 col-xs-0 margin-shape">
			<!-- margin-right-->
			</div>
		</div>
	</div>

<!--Footer-->
	
	<div class="container-fluid">
		<div class="row">
			<div class = "col-md-12" id="footer">
			</div>
		</div>
	</div>

	<script>//load image search dropdowns into relevant div
	var userID="<?php echo $userID; ?>";
	window.onload = function(){
		$("#dropdowns").load("dropdowns_images.php?user="+userID+"&userMode=u");
	}
	</script>
	
</body>

</html>