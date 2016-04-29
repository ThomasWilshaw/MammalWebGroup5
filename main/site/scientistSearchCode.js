window.onload = function(){
	$("#imageSearchButton").click(function(){
		$("#dropdowns").load("dropdowns_images.php?mode=s");
	});

	$("#siteSearchButton").click(function(){
		$("#dropdowns").load("dropdowns_sites.php?mode=s");
	});
}