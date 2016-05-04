<!DOCTYPE html>
<html>
<head>
	<title>
		MammalWeb User
	</title>

	<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="MW.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script type="text/javascript" src="MW.js"></script>
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
        <li class="active"><a href="#">Home <span class="sr-only">(current)</span></a></li>
		<?php
        echo'<li><a href="userDashboard.php?user='.$userID.'&userMode=u">Dashboard</a></li>';
        echo'<li><a href="userSearch.php?user='.$userID.'&userMode=u">Search</a></li>';
		?>
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
		
			<div class="col-md-10 col-xs-12 form-col">
			<!-- form-middle-col -->

			
			</div>
			
		
			<div class="col-md-1 col-xs-0 margin-shape">
			<!-- margin-left -->

			</div>
		</div>
	</div>



</body>
</html>