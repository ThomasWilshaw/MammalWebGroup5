<!DOCTYPE html>
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
        echo'<li><a href="userHome.php?user='.$userID.'">Home</a></li>';
        echo'<li><a href="userDashboard.php?user='.$userID.'">Dashboard</a></li>';
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
		$("#dropdowns").load("dropdowns_images.php?"+userID+"userMode=u");
	}
	</script>
	
</body>

</html>