<?php
	//vul de arrays met info uit de url
	$wegarray = explode(",", $_GET['wegen']);
	$eventarray = explode(",", $_GET['events']);
	
	//check het aantal gegevens in de arrays en ga verder ngl die resultaten
	$wegarraycount = count($wegarray);
	$eventarraycount = count($eventarray);
	
	//mogelijke uitkomsten: enkel events, enkel wegen, combinatie van meer dan 1 bij beide
	//eerste mogelijkheid: 1 weg, geen events
	switch($wegarraycount) {
		case 0: //geen wegen gevonden, enkel events
			break;
		case 1: //1 weg gevonden, misschien meerdere events
			switch($eventarray) {
				case 0: //geen events, enkel weg
				
			}
			break;
	}
?>

<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link href='http://fonts.googleapis.com/css?family=Signika:400,300' rel='stylesheet' type='text/css'>
    <style type="text/css">
		html { height: 100% }
		body { height: 100%;
			margin: 0;
			padding: 10px;
			background: #DDD;
			
			border: 5px solid #FFF;
			
			font-family: Signika;
			font-weight: 300;
			font-size: 15px;
			
			text-shadow: 0px 1px 0px #FFF;
		
		}
		/*
		#map_canvas { 
			position: relative;
			width: 90%;
			}
		#map_canvas { 
			position: relative;
			top: -10;
			left: 0;
			z-index: 1;
			}
		#map_topbar {
			background: url(images/blauw_driehoekje.png) repeat-x;
			background-position: top center;
			position: relative;
			top: 0;
			left: 0;
			z-index: 99999;
			}*/
	  
	  
    </style>
    <!--<script type="text/javascript"
      src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCC5xqbHxk3iluhdKtesx0MtRPOFvZvp90&sensor=false">
    </script>
    <script type="text/javascript">
      function initialize() {
        var myOptions = {
          center: new google.maps.LatLng(50.843304,4.360073),
          zoom: 7,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map(document.getElementById("map_canvas"),
            myOptions);
      }
    </script>-->
  </head>
<body><!-- onLoad="initialize()">-->
  	<div id="header">
    	Sorry!!!
  </div>
    <div id="synthese">
    	<p>
        Deze pagina's komen binnenkort online! Beloofd!
        </p>
  </div>
    <!--
    <div id="map_wrapper">
    	<div id="map_topbar" style="height:10px;"></div>
    	<div id="map_canvas" style="width:100%; height:350px;"></div>
    </div>
    <div id="uitgebreid"></div>
    <div id="footer"></div>
    -->
</body>
</html>