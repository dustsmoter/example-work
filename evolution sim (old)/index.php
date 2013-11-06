<?php


?>

<html>
<head>
<title>Test Sim 0.1 Alpha</title>

<script type="text/javascript" src="prototype.js"></script>

</head>

<body>

<div id="content" style="float: left; margin-right: 10px;">
    <img style="width: 1000px; height: 1000px;" id="map" src="map.php">
</div>

<div id="controls">
    <br><br><br><br>
    <div id="debug">Simulation Running.</div>
    <br><br>
    <input type="button" value="Start" onclick="start();"> <input type="button" value="Stop" onclick="stop();"> <input type="button" value="Reset" onclick="reset();">
    <br><br>
    <div id="info"></div>
</div>

<script type="text/javascript">
    var objTick;

  objTick = new Ajax.PeriodicalUpdater('', 'map.php?reset=0&rand=' + Math.random(), {
                            method: 'get',
                            frequency: 1,
                            requestHeaders: { 
                                                "Pragma":            "no-cache", 
                                                "Cache-Control":     "no-store, no-cache, must-revalidate, post-check=0, pre-check=0", 
                                                "Expires":           0, 
                                                "Last-Modified":     new Date(0), // January 1, 1970 
                                                "If-Modified-Since": new Date(0)
                                            } ,
                            onSuccess: function(response) {
                                                             var responseArray = response.responseText.split("|");
                                                             $('map').src = responseArray[0];
                                                             
                                                             $('info').innerHTML = "Population Count: " + responseArray[1];
                                                             $('info').innerHTML += "<br>Baby Count: " + responseArray[2];
                                                          }
});

function start()
{
      objTick.start();
      $('debug').innerHTML = "Simulation Running.";
}

function stop()
{
      objTick.stop();
      $('debug').innerHTML = "Simulation Stopped.";
}

function reset()
{
    objTick.stop();
    
    $('debug').innerHTML = "Simulation Resetting...Please wait.";
    new Ajax.Request('map.php?reset=1&rand=' + Math.random(), {
    onSuccess: function(response) {
    // Handle the response content...
    $('debug').innerHTML = "Simulation Successfully Reset!";
  },
   onFailure: function(response) {
    // Handle the response content...
    
  }
});
}

</script>

</body>
</html>

