window.onload = function() {
  var person_id=194;
  var uploadArray = [
    {times: []}
  ];
  var uploads=0;
  $.ajax({
    url: "getUploadData.php",
    type: "GET",
    //This should send the users ID
    data: "person_id="+person_id,
    success: function (response) {
      if (response != ''){
         var uploadData=jQuery.parseJSON(response);
         var numUploads=0;
          for(var i in uploadData){
            if(uploadData.hasOwnProperty(i)){
              numUploads+=1;
            }
          }
         jQuery.each(uploadData,function(key,value){
            var d=new Date(value["timestamp"]);
            //Can add more data to this object to enable more interesting things later
            uploadArray[0]["times"][uploads]={"starting_time":d.getTime(),"id":"upload"+uploads,"num_photos":value["num_photos"], "color":"#0033"+(25+Math.round(74*(uploads/numUploads)))};
            uploads++;
         });
         //Necessary to have ending time, otherwise tries to make infinite timeline which goes badly. Could also do when constructing timeline with the .ending(date) method
         if(uploads>0){
            uploadArray[0]["times"][uploads-1]["ending_time"]=new Date().getTime();
            $("#details").text("You have " + uploads + " total uploads");
         }
         buildTimeline();
       }
    }
  ,error: function(response){
    $("details").text("An error occurred");
  }
  });
  console.log()
  var width = 600;

  function buildTimeline() {
    if(uploadArray[0]["times"].length>0){
      var chart = d3.timeline()
      .width(width*2)
      .margin({left:70, right:50, top:0, bottom:0})
      .display("circle")
      .tickFormat({
        format: d3.time.format("%b %Y"),
        tickTime: d3.time.month,
        tickInterval: 1,
        tickSize: 10
      })
      .rotateTicks(20)
      .beginning(uploadArray[0]["times"][0]["starting_time"])
      .showTimeAxisTick()
      .stack()
      .hover(function (d, i, datum) {
      // d is the current rendering object
      // i is the index during d3 rendering
      // datum is the id object
        var div = $('#hoverRes');
        var colors = chart.colors();
        //div.find('.coloredDiv').css('background-color', colors(i))
        var dDay=new Date(d["starting_time"]).getDate();
        var dMonth=new Date(d["starting_time"]).getMonth();
        var dYear=new Date(d["starting_time"]).getFullYear();
        div.find('#hoverDetails').text("This was " + d["id"] + ", uploaded on " + dDay+"/"+ dMonth+"/"+ dYear + " and included " + d["num_photos"] + " photos");
      })
      .mouseover(function(d,i,datum){
        d3.select("#"+d["id"]).style("fill", "red");
      })
      .mouseout(function(d,i,datum){
        d3.select("#"+d["id"]).style("fill", d["color"]);
      });
      var svg = d3.select("#timeline").append("svg").attr("width", width)
          .datum(uploadArray).call(chart);
    }
    else{
      $("#details").text("You don't have any uploads yet");
    }
  }

  $.ajax({
    url: "getFavouriteURLS.php",
    type: "GET",
    data: "person_id="+person_id,
    success: function (response) {
      if (response != 'no_likes'){
        console.log(response);
        var urls=jQuery.parseJSON(response);
        //For each photo, we create new html elements on te page that are inside the carousel
        //First entry is different (active) and it's easier to do it outside the loop
        $("#favouriteImageCarouselIndicators").append('<li data-target="#favouriteImageCarousel" data-slide-to="0" class="active"></li>');
        $("#favouriteImageCarouselInner").append('<div class="item active"><img src="'+urls[0]+'" alt="favourite"></div>');
        for(i in urls){
          /*li data-target=\"#favouriteImageCarousel\" data-slide-to=\"" + i + "\"");*/
          if(i>0){
            $("#favouriteImageCarouselIndicators").append('<li data-target="#favouriteImageCarousel" data-slide-to="'+i+'"></li>');
            $("#favouriteImageCarouselInner").append('<div class="item "><img src="'+urls[i]+'" alt="favourite"></div>');
          }
        }
        $("#favouriteImageCarousel").carousel();
        $(".item").click(function(){
          $("#favouriteImageCarousel").carousel(1);
        });

        // Enable Carousel Controls
        $(".left").click(function(){
            $("#favouriteImageCarousel").carousel("prev");
        });

        $(".right").click(function(){
            $("#favouriteImageCarousel").carousel("next");
        });
      }
      else{
        $("#favouriteImageCarousel").removeClass("carousel slide");
        $("#favouriteImageCarousel").html("<p>You don't have any favourited photos yet- click the thumbs up on a photo whilst spotting to save it for later.</p>");
      }
    }
  });  
}