window.onload = function() {
  var uploadArray = [
    {times: []}
  ];
  var uploads=0;
  $.ajax({
    url: "getUploadData.php",
    type: "GET",
    //This should send the users ID
    data: "person_id=182",
    success: function (response) {
      if (response != ''){
         var uploadData=jQuery.parseJSON(response);
         jQuery.each(uploadData,function(key,value){
            var d=new Date(value);
            //Can add more data to this object to enable more interesting things later
            uploadArray[0]["times"][uploads]={"starting_time":d.getTime(),"id":"u"+uploads};
            uploads++;
         });
         console.log(uploadArray);
         //Necessary to have ending time, otherwise tries to make infinite timeline which goes badly. Could also do when constructing timeline with the .ending(date) method
         if(uploads>0){
            uploadArray[0]["times"][uploads-1]["ending_time"]=new Date().getTime();
         }
         timelineHover();
       }
    }
  });
  console.log()
  var width = 800;

  function timelineHover() {
    if(uploadArray[0]["times"].length>0){
      var chart = d3.timeline()
      .width(width*3)
      .margin({left:70, right:50, top:0, bottom:0})
      .display("circle")
      .tickFormat({
        format: d3.time.format("%b %Y"),
        tickTime: d3.time.weeks,
        tickInterval: 4,
        tickSize: 10
      })
      .rotateTicks(20)
      .beginning(uploadArray[0]["times"][0]["starting_time"])
      .showTimeAxisTick()
      //TODO: Add hovering/clicking bringing up more data ie number of photos in upload, where the upload was from etc
      .hover(function (d, i, datum) {
      // d is the current rendering object
      // i is the index during d3 rendering
      // datum is the id object
        var div = $('#hoverRes');
        var colors = chart.colors();
        div.find('.coloredDiv').css('background-color', colors(i))
        div.find('#name').text(d["id"]);
      });
      var svg = d3.select("#timeline").append("svg").attr("width", width)
          .datum(uploadArray).call(chart);
    }
    else{
      document.getElementById("timeline").innerHTML = "No uploads (yet)";
    }
  }
}