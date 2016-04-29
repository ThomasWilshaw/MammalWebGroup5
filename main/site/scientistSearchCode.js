window.onload = function(){
	$("#imageSearchButton").click(function(){
		$("#dropdowns").load("dropdowns_images.php?userMode=s");
	});

	$("#siteSearchButton").click(function(){
		$("#dropdowns").load("dropdowns_sites.php?userMode=s");
	});
}