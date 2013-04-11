<?php
	include('db_connectie.php');
	$dbconnection = mysql_connect($dbhost, $dbuser, $dbpass) or die('Fout tijdens verbinden met de database');
	mysql_select_db($dbname, $dbconnection);
	
	//check of er enkel van 1 iets data is binnengekomen
	$enkel = $_GET['only'];
		
	$querystring = "SELECT * FROM cachedata WHERE (";
	//mogelijke uitkomsten: enkel events, enkel wegen, combinatie van meer dan 1 bij beide
	//eerste mogelijkheid: 1 weg, geen events
	switch($enkel) {
		case "r": //enkel wegen meegegeven
			//vul de array met info uit de url
			$wegarray = explode(",", $_GET['r']);
			//query: SELECT * FROM cachedata WHERE (r OR r OR r...)
			$i=0;
			$count = count($wegarray);
			
			foreach($wegarray as $weg) {
				$i++;
				if($i!=$count) {
					$querystring = $querystring . "road='$weg' OR ";
				}
				else {
					$querystring = $querystring . "road='$weg') ORDER BY road,type ASC;";
				}
			}
			
			break;
		case "e": //enkel events meegegeven
			//vul de arrays met info uit de url
			$eventarray = explode(",", $_GET['e']);
			//query: SELECT * FROM cachedata WHERE (e OR e OR e ...)
			$i=0;
			$count = count($eventarray);
			
			foreach($eventarray as $type) {
				$i++;
				if($i!=$count) {
					$querystring = $querystring . "type='$type' OR ";
				}
				else {
					$querystring = $querystring . "type='$type') ORDER BY type,road ASC;";
				}
			}
			break;
		case "none"://van beide gevonden
			//vul de arrays met info uit de url
			$wegarray = explode(",", $_GET['r']);
			$eventarray = explode(",", $_GET['e']);
			//query: SELECT * FROM cachedata WHERE (r OR r OR r...) AND (e OR e OR e ...)
			$i=0;
			$count = count($wegarray);
			
			foreach($wegarray as $weg) {
				$i++;
				if($i!=$count) {
					$querystring = $querystring . "road='$weg' OR ";
				}
				else {
					$querystring = $querystring . "road='$weg')";
				}
			}
			
			$querystring = $querystring . " AND (";
			
			$i=0;
			$count = count($eventarray);
			
			foreach($eventarray as $type) {
				$i++;
				if($i!=$count) {
					$querystring = $querystring . "type='$type' OR ";
				}
				else {
					$querystring = $querystring . "type='$type') ORDER BY type,road ASC;";
				}
			}
			break;
	}
	$queryresult = mysql_query($querystring);
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
			padding: 0;
			background: #DDD;
			
			font-family: Signika;
			font-weight: 300;
			font-size: 15px;
			
			text-shadow: 0px 1px 0px #FFF;
		}  
    </style>
</head>
<body><!-- onLoad="initialize()">-->
    <div id="overzichtstabel">
    <table border="0" cellspacing="0">
    	<?php
		$oddcount=0;
		while($rij = mysql_fetch_array($queryresult)) {
			$oddcount++;
			if($oddcount&1) { echo "<tr bgcolor='#FFFFFF'>"; } else { echo "<tr>"; }
				echo "<td width=55 height=50 align=left>";
				echo "<img src='images/mobile/" . $rij['type'] . ".png' alt='" .  $rij['type'] . "' width=32 height=32 style='margin-left:10px;' />";
				echo "</td>";
				echo "<td style='padding-top: 5px; padding-bottom: 5px; padding-right:10px;'>";
				$antwoord="Er ";		
				switch($rij['type']) {
					case "traffic_jam":
						$antwoord = $antwoord . "staat een file op de " . $rij['road'];
						break;
					case "speed_traps":
						$antwoord = $antwoord . "staat een flitser op de " . $rij['road'];
						break;
					case "accident":
						$antwoord = $antwoord . "is een ongeval gebeurd op de " . $rij['road'];
						break;
					case "road_work":
						$antwoord = $antwoord . "is een werf op de " . $rij['road'];
						break;
					case "incident":
						$antwoord = $antwoord . "is een incident op de " . $rij['road'];
						break;
				}
				
				if($rij['routeFrom']!="") {
					$antwoord = $antwoord . " van " . $rij['routeFrom'] . 
						" naar ". $rij['routeTo'] . " " . $rij['segment'];
				}
				elseif($rij['routeIn']!="") {
					$antwoord = $antwoord . " " . $rij['details'] . " van " . 
						$rij['routeIn'] . " " . $rij['segment'];
				}
				else { 
					$antwoord = $antwoord . " " . $rij['segment'];
				}		
				echo "<p>$antwoord</p>";
				echo "</td>";
			echo "</tr>";
		}
		?>
	</table>
    </div>
</body>
</html>