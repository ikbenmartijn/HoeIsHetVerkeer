<?php
	//Zeg hallo!
	echo "Answeringmachine is on!<hr/>";
	
	//maak verbinding met de databse
	echo "Verbinden met de database<br/>";
	include('db_connectie.php');
	$dbconnection = mysql_connect($dbhost, $dbuser, $dbpass) or die('Fout tijdens verbinden met de database');
	mysql_select_db($dbname, $dbconnection);
	
	//haal alle niet beantwoorde tweets op
	echo "Eventuele onbeantwoorde mentions ophalen<br/>";
	$querystring="SELECT * FROM incoming WHERE beantwoord=0;"; 
	$queryresult=mysql_query($querystring);
		
	//controleer of de query resultaat opleverde
	if(isset($queryresult)) {	
		//er zijn onbeantwoorde mentions
		//voor elke onbeantwoorde mention
		while($row = mysql_fetch_array($queryresult)){
			echo "<hr/>Onbeantwoorde mention in database<br/>";
			//genereer antwoord
			$antwoord = /*$row['username'] . " " . */MaakAntwoord($row['id'],$row['entities']); ///////////////////////AANPASSEN: ANTWOORDER.PHP OF USERNAME HIER WEG!
			//schrijf antwoord in database
			SchrijfAntwoordInDatabase($row['id'],$antwoord);
		}
		echo "<strong>Alle onbeantwoorde mentions zijn doorlopen.</font><hr/>Answering machine shutting down.</strong>";
	}
	else {
		//geen resultaat van de query, geen onbeantwoorde mentions
		echo "<strong><font color=red>Geen onbeantwoorde mentions.</font><hr/>Answering machine shutting down.</strong>";
	}
	
	//sluit verbinding met de database
	mysql_close($dbconnection);
	//einde van de answeringmachine


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////// FUNTIES VANAF HIER ///////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function MaakAntwoord($id,$entities) {
	//Zet $entities in een array
	$hashtagcollection=unserialize($entities);
	//Zet variabelen op 0
	$infoValue=0; //bepaalt als we ofwel weg ofwel event of combinatie van beide zoeken
	$wegcount=0; //aantal gevonden wegen
	$eventcount=0; //aantal gevonden events
	//maak de arrays waarin de gevonden wegen en events terechtkomen
	$wegarray=array();
	$eventarray=array();
	
	//Zoek eerst naar het aantal gevraagde events en welke events het zijn
	echo "<strong>Tweet analyseren op events:</strong><br/>";
	foreach($hashtagcollection as $hashtag) {
		$gevondenevent = AnalyseerZoekwoordOpType($hashtag->text);
		if($gevondenevent!=NULL && $eventcount==0) { //als dit het eerste gevonden event is
			$infoValue = $infoValue + 1;  	//mag maar 1 keer gebeuren
			$eventarray[] = $gevondenevent; //toevoegen aan de eventarray
			$eventcount++;				  	//aantal gevonden events +1
			echo "Gevonden: $gevondenevent<br/>";
		}
		elseif ($gevondenevent!=NULL && $eventcount>0) { //als dit de tweede of meerde event is die gevonden werd
			$eventarray[] = $gevondenevent;
			$eventcount++;
			echo "Gevonden: $gevondenevent<br/>";
		}
		else { //geen event geconden
			echo "Deze tag is geen event<br/>";
		}
	}
		
	echo "<strong>Tweet analyseren op wegen:</strong><br/>";
	foreach($hashtagcollection as $hashtag) {
		$gevondenweg = AnalyseerZoekwoordOpWeg($hashtag->text);
		if($gevondenweg!=NULL && $wegcount==0) { //als dit de eerste gevonden weg is
			$infoValue = $infoValue + 2;  	//mag maar 1 keer gebeuren
			$wegarray[] = $gevondenweg;		//toevoegen aan de wegarray
			$wegcount++;				  	//aantal gevonden wegen +1
			echo "Gevonden: $gevondenweg<br/>";
		}
		elseif ($gevondenweg!=NULL && $wegcount>0) { //als dit de tweede of meerde weg is die gevonden werd
			$wegarray[] = $gevondenweg;
			$wegcount++;
			echo "Gevonden: $gevondenweg<br/>";
		}
		else { //geen event geconden
			echo "Deze tag is geen weg<br/>";
		}
	}
	
	//einde analysefase
	echo "<strong>Einde tweetanalyse</strong><hr/>";
	
	//begin antwoordgeneratiefase
	//infoValue zal bepalen welke combinatie we hebben: 0: geen wegen/events, 1: enkel event, 2: enkel weg, 3: combinatie weg(en) en event(s)
	switch($infoValue) { //MAINSWITCH
		case 0: //MAINSWITCH: geen wegen/events gevonden in tweet
			//genereer random foutrapport
			$xfactor = rand(1,7);
			switch($xfactor) {
				case 1:
				$antwoord = "Geef me een weg of een gebeurtenis als hashtag en ik zorg voor een antwoord! Meer info op http://hoeishetverkeer.ikbenmartijn.be";
				break;	
			case 2:
				$antwoord = "Ik heb geen weg of gebeurtenis kunnen vinden, gebruik je hashtags? Meer info: http://hoeishetverkeer.ikbenmartijn.be";
				break;
			case 3:
				$antwoord = "Hmmm.. Vraag dat nog eens opnieuw met wegen en gebeurtenissen als hashtags. Meer info: http://hoeishetverkeer.ikbenmartijn.be";
				break;
			case 4:
				$antwoord = "Tof dat je tegen me praat, maar zonder weg of gebeurtenis als hashtag ben ik niks! Meer info: http://hoeishetverkeer.ikbenmartijn.be";
				break;
			case 5:
				$antwoord = "Ik wil best antwoorden maar ik begrijp je vraag niet. Gebruik je de juiste hashtags? Meer info: http://hoeishetverkeer.ikbenmartijn.be";
				break;
			case 6:
				$antwoord = "Net zoals jij eten nodig hebt, heb ik nood aan hashtags om mijn werk te kunnen doen! Meer info: http://hoeishetverkeer.ikbenmartijn.be";
				break;
			case 7:
				$antwoord = "Hashtags zijn mijn loon, zonder werk ik niet! Hash een weg of gebeurtenis en ik doe alles! Meer info: http://hoeishetverkeer.ikbenmartijn.be";
				break;
			}
			break; //einde case infoValue=0
		
		case 1: //MAINSWITCH: enkel event(s) gevonden 
			//maak de db-query
			//bepalen of het om 1 event gaat of meerdere om query te bouwen
			switch($eventcount) { //EVENTONLYQUERYSWITCH
				case 1: //slechts 1 event
					$eventsonlyquerystring = "SELECT * FROM cachedata WHERE type='".$eventarray[0]."';";
					break;
				default: //meer dan 1 events
					$eventsonlyquerystring = "SELECT * FROM cachedata WHERE";
					
					$forcounter=0;
					foreach($eventarray as $event) {
						$forcounter++;
						if($forcounter==1) {
							$eventsonlyquerystring = $eventsonlyquerystring . " type='".$event."'";
						}
						else {
							$eventsonlyquerystring = $eventsonlyquerystring . "OR type='".$event."'";
						}
					}
					
					$eventsonlyquerystring = $eventsonlyquerystring . ";";
					
					break;
			}
			
			//Query uitvoeren en naar array omzetten
			$resulteventsonlyquery = mysql_query($eventsonlyquerystring) or die("MySQL error. Query: " . $eventsonlyquerystring . "<br /> Error: (" . mysql_errno() . ") " . mysql_error());
			$resulteventsonlyarray = mysql_fetch_array($resulteventsonlyquery);
			//tel aantal rijen uit query en neem antwoordactie
			$eventonlyquerycount = mysql_num_rows($resulteventsonlyquery);
			
			switch($eventonlyquerycount) {
				case 0: //geen resultaten, dus ook geen problemen
					//meerdere events gevraagd of slechts 1 gevraagd
					if($eventcount==1) { //slechts 1 type event gevraagd
						$antwoord = "Er zijn geen ". EventSyntax($eventarray[0],"meer") . " op het hele wegennet! Happy driving!";
					}
					else { //meerdere types events gevraagd
						$antwoord = "Er zijn geen ";
						for($i=0;$i<$eventcount;$i++) {
							if($i==0) {
								$antwoord = $antwoord . EventSyntax($eventarray[$i],"meer");
							}
							else {
								$minevtcount = $eventcount-1;
								if($i==$minevtcount) { //check of het het laatste element van de array is
									$antwoord = $antwoord . " of " . EventSyntax($eventarray[$i],"meer");
								}
								else {
									$antwoord = $antwoord . ", " . EventSyntax($eventarray[$i],"meer");
								}
							}
						}
						$antwoord = $antwoord . " op het hele wegennet!";
					}
					break;
				case 1: //1 resultaat, klein probleempje, antwoorden in tweet
					$antwoord="Er ";
					
					switch($resulteventsonlyarray['type']) {
						case "traffic_jam":
							$antwoord = $antwoord . "staat een file op de " . $resulteventsonlyarray['road'];
							break;
						case "speed_traps":
							$antwoord = $antwoord . "staat een flitser op de " . $resulteventsonlyarray['road'];
							break;
						case "accident":
							$antwoord = $antwoord . "is een ongeval gebeurd op de " . $resulteventsonlyarray['road'];
							break;
						case "road_work":
							$antwoord = $antwoord . "is een werf op de " . $resulteventsonlyarray['road'];
							break;
						case "incident":
							$antwoord = $antwoord . "is een incident op de " . $resulteventsonlyarray['road'];
							break;
					}
					
					if($resulteventsonlyarray['routeFrom']!="") {
						$antwoord = $antwoord . " van " . $resulteventsonlyarray['routeFrom'] . 
							" naar ". $resulteventsonlyarray['routeTo'] . " " . $resulteventsonlyarray['segment'];
					}
					elseif($resulteventsonlyarray['routeIn']!="") {
						$antwoord = $antwoord . " " . $resulteventsonlyarray['details'] . " van " . 
							$resulteventsonlyarray['routeIn'] . " " . $resulteventsonlyarray['segment'];
					}
					else { 
						$antwoord = $antwoord . " " . $resulteventsonlyarray['segment'];
					}
					break;
				default: //meer dan 1 resultaat, beetje meer problemen, samenvatting geven in tweet en meer infopagina meegeven
					//query opnieuw maar nu gegroepeerd per weg
					//maak de db-query
					//bepalen of het om 1 event gaat of meerdere om query te bouwen
					switch($eventcount) { //MULTIEVENTONLYQUERYSWITCH
						case 1: //slechts 1 event
							$multieventsonlyquerystring = "SELECT count(id),road FROM cachedata WHERE type='".$eventarray[0]."' GROUP BY road ORDER BY COUNT(id),road ASC;";
							break;
						default: //meer dan 1 events
							$multieventsonlyquerystring = "SELECT count(id),road FROM cachedata WHERE";
						
							$forcounter=0;
							foreach($eventarray as $event) {
								$forcounter++;
								if($forcounter==1) {
									$multieventsonlyquerystring = $multieventsonlyquerystring . " type='".$event."'";
								}
								else {
									$multieventsonlyquerystring = $multieventsonlyquerystring . "OR type='".$event."'";
								}
							}
					
							$multieventsonlyquerystring = $multieventsonlyquerystring . " GROUP BY road ORDER BY COUNT(id),road ASC;";
							break;
					}	
					$resultmultieventsonlyquery = mysql_query($multieventsonlyquerystring) or die("MySQL error. Query: " . $multieventsonlyquerystring . "<br /> Error: (" . mysql_errno() . ") " . mysql_error());
					
					//tel aantal rijen uit query en neem antwoordactie
					$multieventonlyquerycount = mysql_num_rows($resultmultieventsonlyquery);
					
					switch($multieventonlyquerycount) { //tel aantal wegen
						case 1: //meerdere events, maar ze zijn allemaal op dezelfde weg...
							while($rij = mysql_fetch_array($resultmultieventsonlyquery)) {
								$antwoord = "Er zijn " . $rij[0] . " hindernissen op de ". $rij['road'] . "! ";
								
								$estring = "http://m.ikbenmartijn.be/m.php?only=none&e=";
								$estring = $estring . implode (",", $eventarray);
								$estring = $estring . "&r=".$rij['road'];
								
								//beperk desnoods het antwoord
								$antwoord = substr($antwoord, 0, 90) . $estring;
							}
							break;
						default: //meerdere events, en op verschillende wegen
						$antwoord = "Er ";
							$i=0;
							while($rij = mysql_fetch_array($resultmultieventsonlyquery)) {
								$i++;
								if($i==1) {
									if($rij[0]==1) { //1 event van type X
										$antwoord = $antwoord . "is een hindernis op de " . $rij['road'];
									}
									else { //1 event van type X
									
										$antwoord = $antwoord . "zijn " . $rij[0] . " hindernissen op de " . $rij['road'];
									}
								}
								else {
									if($i==$multieventonlyquerycount) { //check of het het laatste element van de array is
										$antwoord = $antwoord . " en ";
										
										if($rij[0]==1) { //1 event van type X
											$antwoord = $antwoord . "er is een hindernis op de " . $rij['road'] . ". ";
										}
										else { //1 event van type X
											$antwoord = $antwoord . "er zijn " . $rij[0] . " hindernissen op de " . $rij['road'] . ". ";
										}	
									}
									else {
										$antwoord = $antwoord . ", ";
										if($rij[0]==1) { //1 event van type X
											$antwoord = $antwoord . "een hindernis op de " . $rij['road'];
										}
										else { //1 event van type X
											$antwoord = $antwoord . $rij[0] . " hindernissen op de " . $rij['road'];
										}
									}
								}
							}
							$estring = "http://m.ikbenmartijn.be/m.php?only=e&e=";
							$estring = $estring . implode (",", $eventarray);
							
							//beperk desnoods het antwoord
							$antwoord = substr($antwoord, 0, 90) . "... " . $estring;
							break;
					}
					break;
			}
			break; //einde case infoValue=1
			
		case 2: //MAINSWITCH: enkel weg(en) gevonden
			//maak de db-query
			//bepalen of het om 1 weg gaat of meerdere om query te bouwen
			switch($wegcount) { //ROADONLYQUERYSWITCH
				case 1: //slechts 1 weg
					$roadsonlyquerystring = "SELECT * FROM cachedata WHERE road='".$wegarray[0]."';";
					echo "<h1>$roadsonlyquerystring</h1>";
					break;
				default: //meer dan 1 weg
					$roadsonlyquerystring = "SELECT * FROM cachedata WHERE";
					$forcounter=0;
					foreach($wegarray as $weg) {
						$forcounter++;
						if($forcounter==1) {
							$roadsonlyquerystring = $roadsonlyquerystring . " road='".$weg."'";
						}
						else {
							$roadsonlyquerystring = $roadsonlyquerystring . " OR road='".$weg."'";
						}
					}
					$roadsonlyquerystring = $roadsonlyquerystring . ";";
					break;
			} // EINDE ROADONLYQUERYSWITCH
			
			//query uitvoeren, je krijgt alle wegen die je gevraagd hebt. Per lijn 1 event
			$resultroadsonlyquery = mysql_query($roadsonlyquerystring) or die("MySQL error. Query: " . $roadsonlyquerystring . "<br /> Error: (" . mysql_errno() . ") " . mysql_error());
			$resultroadsonlyarray = mysql_fetch_array($resultroadsonlyquery);
			//tel aantal rijen uit query en neem antwoordactie
			$roadsonlyquerycount = mysql_num_rows($resultroadsonlyquery);
			echo "<h1>$roadsonlyquerycount</h1>";
			switch($roadsonlyquerycount) {
				case 0: //geen resultaten, dus ook geen problemen
					//meerdere wegen gevraagd of slechts 1 gevraagd
					if($wegcount==1) { //slechts 1 type event gevraagd
						$antwoord = "Er zijn geen hindernissen op de ". $wegarray[0] ."! Happy driving!";
					}
					else { //meerdere wegen gevraagd
						$antwoord = "Er zijn geen hindernissen op ";
						for($i=0;$i<$wegcount;$i++) {
							if($i==0) {
								$antwoord = $antwoord . " de " . $wegarray[$i];
							}
							else {
								$minroadcount = $wegcount-1;
								if($i==$minroadcount) { //check of het het laatste element van de array is
									$antwoord = $antwoord . " en de " . $wegarray[$i] . ".";
								}
								else {
									$antwoord = $antwoord . ", de " . $wegarray[$i];
								}
							}
						}
						$antwoord = $antwoord . " Happy driving!";
					}
					break;
				
				case 1: // 1 probleempje, zeggen op welke weg en meer info geven
					$antwoord="Er ";
					
					switch($resultroadsonlyarray['type']) {
						case "traffic_jam":
							$antwoord = $antwoord . "staat een file op de " . $resultroadsonlyarray['road'];
							break;
						case "speed_traps":
							$antwoord = $antwoord . "staat een flitser op de " . $resultroadsonlyarray['road'];
							break;
						case "accident":
							$antwoord = $antwoord . "is een ongeval gebeurd op de " . $resultroadsonlyarray['road'];
							break;
						case "road_work":
							$antwoord = $antwoord . "is een werf op de " . $resultroadsonlyarray['road'];
							break;
						case "incident":
							$antwoord = $antwoord . "is een incident op de " . $resultroadsonlyarray['road'];
							break;
					}
					
					if($resultroadsonlyarray['routeFrom']!="") {
						$antwoord = $antwoord . " van " . $resultroadsonlyarray['routeFrom'] . 
							" naar ". $resultroadsonlyarray['routeTo'] . " " . $resultroadsonlyarray['segment'];
					}
					elseif($resultroadsonlyarray['routeIn']!="") {
						$antwoord = $antwoord . " " . $resultroadsonlyarray['details'] . " van " . 
							$resultroadsonlyarray['routeIn'] . " " . $resultroadsonlyarray['segment'];
					}
					else { 
						$antwoord = $antwoord . " " . $resultroadsonlyarray['segment'];
					}
					break;
				
				default: //meerdere problemen op mogelijks verschillende wegen (wegcount checken daarvoor)	
					//query opnieuw maar nu gegroepeerd per event
					//maak de db-query
					//bepalen of het om 1 event gaat of meerdere om query te bouwen
					switch($wegcount) { //MULTIROADONLYQUERYSWITCH
						case 1: //slechts 1 weg ingegeven vb @hoeishetverkeer #A10
							$multiroadsonlyquerystring = "SELECT count(id),type FROM cachedata WHERE road='".$wegarray[0]."' GROUP BY type ORDER BY COUNT(id),type ASC;";
echo "<font color=blue>$multiroadsonlyquerystring</font>";						
break;

						default: //meer dan 1 weg ingegeven vb @hoeishetverkeer #A10 #R8
							$multiroadsonlyquerystring = "SELECT count(id),type FROM cachedata WHERE";
						
							$forcounter=0;
							foreach($wegarray as $weg) {
								$forcounter++;
								if($forcounter==1) {
									$multiroadsonlyquerystring = $multiroadsonlyquerystring . " road='".$weg."'";
								}
								else {
									$multiroadsonlyquerystring = $multiroadsonlyquerystring . " OR road='".$weg."'";
								}
							}
					
							$multiroadsonlyquerystring = $multiroadsonlyquerystring . " GROUP BY type ORDER BY COUNT(id),type ASC;";
							break;
					}
					//voer de query uit
					$resultmultiroadsonlyquery = mysql_query($multiroadsonlyquerystring) or die("MySQL error. Query: " . $multiroadsonlyquerystring . "<br /> Error: (" . mysql_errno() . ") " . mysql_error());
					
					//tel aantal rijen uit query en neem antwoordactie
					$multiroadsonlyquerycount = mysql_num_rows($resultmultiroadsonlyquery);
					echo "<h1>$multiroadsonlyquerycount</h1>";
					switch($multiroadsonlyquerycount) { //tel aantal wegen
						case 1: //een soort event
							while($rij = mysql_fetch_array($resultmultiroadsonlyquery)) {
								$antwoord = "Er zijn " . $rij['COUNT(id)'] . " " . EventSyntax($rij['type'],"meer") . " op de wegen die je vroeg! ";
								
								$estring = "http://m.ikbenmartijn.be/m.php?only=none&e=";
								$estring = $estring . implode (",", $wegarray);
								$estring = $estring . "&e=".$rij['type'];
								
								//beperk desnoods het antwoord
								$antwoord = substr($antwoord, 0, 90) . $estring;
							}
							break;
						default: //meerdere soorten events
						echo "<h1>Meerdere soorten events</h1>";
							$antwoord = "Er ";
							$i=0;
							while($rij = mysql_fetch_array($resultmultiroadsonlyquery)) {
								$i++;
								echo "<hr>";
								print_r($rij);
								echo $i;
								echo "<hr>";
								if($i==1) { //eerste keer van de while
									if($rij['count(id)']==1) { //1 event van type X
										$quickroadqstring = "";
										$quickroadqstring = "SELECT road FROM cachedata WHERE ";
										$qrqscounter=0;
										foreach($wegarray as $weg) {
											$qrqscounter++;
											echo "<h2>$qrqscounter</h2>";
											if($qrqscounter==1) {
												echo "HIEIIIEEEIERIRI";
												$addstring = "road='". $weg ."'";
												echo $addstring;
											}
											else {
												echo $qrqscounter;
												$addstring = "OR road='". $weg ."'";
												echo $addstring;
											}
											$quickroadqstring = $quickroadqstring.$addstring;
											echo "<h1>Q: $quickroadqstring</h1>";
										}
										$resultquickroadq = mysql_query($quickroadqstring) or die("MySQL error. Query: " . $quickroadqstring . "<br /> Error: (" . mysql_errno() . ") " . mysql_error());
										$quickroadqarray = mysql_fetch_array($resultquickroadq);
										$antwoord = $antwoord . "er is een " . EventSyntax($rij['type'],"enkel") ." op de " . $quickroadqarray['road'] ;
										echo "<h1> HIER $antwoord</h1>";
									}
									else { //meerdere events van type X								
										$antwoord = $antwoord . "er zijn " . $rij['count(id)'] . " ". EventSyntax($rij['type'],"meer") ." op de ";//WEGEN
										//check of het op meerdere wegen is of enkel op 1
										$quickroadqstring = "";
										$quickroadqstring = "SELECT road FROM cachedata WHERE type='". $rij['type'] . "' AND (";
										$qrqscounter=0;
print_r($wegarray);
										foreach($wegarray as $weg) {
											$qrqscounter++;
											if($qrqscounter==1) {
												$addstring = "road='". $weg ."'";
											}
											else {
												$addstring = " OR road='". $weg ."'";
											}
											$quickroadqstring = $quickroadqstring.$addstring;
										}
										
										$quickroadqstring = $quickroadqstring.") GROUP BY road;";
										echo "<h1>$quickroadqstring</h1>";
										$resultquickroadq = mysql_query($quickroadqstring) or die("MySQL error. Query: " . $quickroadqstring . "<br /> Error: (" . mysql_errno() . ") " . mysql_error());
										$quickroadcount = mysql_num_rows($resultquickroadq);
										if($quickroadcount==1) {
											$quickroadqarray = mysql_fetch_array($resultquickroadq);
											$antwoord = $antwoord . $quickroadqarray['road'];
										}
										else {
											$antwoord = $antwoord . "wegen";
										}
									}
								}
								else { //2e of xde keer van de while
									if($i==$multiroadsonlyquerycount) { //check of het het laatste element van de array is
										$antwoord = $antwoord . " en";
										
										if($rij['count(id)']==1) { //1 event van type X
											$quickroadqstring = "SELECT road FROM cachedata WHERE ";
											
											$qrqscounter=0;
											foreach($wegarray as $weg) {
												$qrqscounter++;
												if($qrqscounter==1) {
													echo $qrqscounter;
													$addstring = "road='". $weg ."'";
												}
												else {
													$addstring = " OR road='". $weg ."'";
												}
												echo "<h2>$addstring</h2>";
												$quickroadqstring = $quickroadqstring.$addstring;
											}
											$resultquickroadq = mysql_query($quickroadqstring) or die("MySQL error. Query: " . $quickroadqstring . "<br /> Error: (" . mysql_errno() . ") " . mysql_error());
											$quickroadqarray = mysql_fetch_array($resultquickroadq);
										
											$antwoord = $antwoord . " een " . EventSyntax($rij['type'],"enkel") ." op de " . $quickroadqarray['road']  . ". ";										
										}
										else {
											$antwoord = $antwoord . " " . $rij['count(id)'] . " ". EventSyntax($rij['type'],"meer") ." op de ";//WEGEN
											//check of het op meerdere wegen is of enkel op 1
											$quickroadqstring = "SELECT road FROM cachedata WHERE type='".$rij['type'] . "' AND (";
											$qrqscounter=0;
											foreach($wegarray as $weg) {
												$qrqscounter++;
												if($qrqscounter==1) {
													$addstring = "road='". $weg ."'";
												}
												else {
													$addstring = " OR road='". $weg ."'";
												}
												echo "<h2>$addstring</h2>";
												$quickroadqstring = $quickroadqstring.$addstring;
											}
											
											$quickroadqstring = $quickroadqstring.") GROUP BY road;";
											$resultquickroadq = mysql_query($quickroadqstring) or die("MySQL error. Query: " . $quickroadqstring . "<br /> Error: (" . mysql_errno() . ") " . mysql_error());
											$quickroadcount = mysql_num_rows($resultquickroadq);
											if($quickroadcount==1) {
												$quickroadqarray = mysql_fetch_array($resultquickroadq);
												$antwoord = $antwoord . $quickroadqarray['road'] . ".";
											}
											else {
												$antwoord = $antwoord . "wegen.";
											}
										}	
									}
									else {
										$antwoord = $antwoord . ", ";
										if($rij['count(id)']==1) { //1 event van type X
											$quickroadqstring = "SELECT road FROM cachedata WHERE ";
											foreach($wegarray as $weg) {
												$qrqscounter++;
												if($qrqscounter==1) {
													$addstring = "road='". $weg ."'";
												}
												else {
													$addstring = " OR road='". $weg ."'";
												}
												$quickroadqstring = $quickroadqstring.$addstring;
											}
											$resultquickroadq = mysql_query($quickroadqstring) or die("MySQL error. Query: " . $quickroadqstring . "<br /> Error: (" . mysql_errno() . ") " . mysql_error());
											$quickroadqarray = mysql_fetch_array($resultquickroadq);
										
											$antwoord = $antwoord . " er is een " . EventSyntax($rij['type'],"enkel") ." op de " . $quickroadqarray['road'];
										}
										else {
											$antwoord = $antwoord . "er zijn " . $rij['count(id)'] . " ". EventSyntax($rij['type'],"meer") ." op de ";//WEGEN
											//check of het op meerdere wegen is of enkel op 1
											$quickroadqstring = "SELECT road FROM cachedata WHERE type='".$rij['type'] . "' AND (";
											$qrqscounter=0;
											foreach($wegarray as $weg) {
												$qrqscounter++;
												if($qrqscounter==1) {
													$addstring = "road='". $weg ."'";
												}
												else {
													$addstring = " OR road='". $weg ."'";
												}
												$quickroadqstring = $quickroadqstring.$addstring;
											}
											
											$quickroadqstring = $quickroadqstring.") GROUP BY road;";
											echo "<h1>$quickroadqstring</h1>";
											$resultquickroadq = mysql_query($quickroadqstring) or die("MySQL error. Query: " . $quickroadqstring . "<br /> Error: (" . mysql_errno() . ") " . mysql_error());
											$quickroadcount = mysql_num_rows($resultquickroadq);
											if($quickroadcount==1) {
												$quickroadqarray = mysql_fetch_array($resultquickroadq);
												$antwoord = $antwoord . $quickroadqarray['road'];
											}
											else {
												$antwoord = $antwoord . "wegen";
											}
										}
									}
								}
							}
							$estring = "http://m.ikbenmartijn.be/m.php?only=r&r=";
							$estring = $estring . implode (",", $wegarray);
							
							//tel het aantal karakters in de estring
							
							
							//beperk desnoods het antwoord
							$antwoord = substr($antwoord, 0, 90) . "... " . $estring;
							break;
							echo "<h1>$antwoord</h1>";
					}
					break; //EINDE DEFAULT  
			}
			break; //einde case infoValue=2, terug naar mainswitch
			
		case 3: //MAINSWITCH: combinatie wege(en) en event(s)
			//query bouwen
			$roadsandeventsquerystring = "SELECT * FROM cachedata WHERE (";
			
			$forcounter=0;
			foreach($eventarray as $event) {
				$forcounter++;
				if($forcounter==1) {
					$roadsandeventsquerystring = $roadsandeventsquerystring . "type='".$event."'";
				}
				else {
					$roadsandeventsquerystring = $roadsandeventsquerystring . " OR type='".$event."'";
				}
			}
			
			$roadsandeventsquerystring = $roadsandeventsquerystring . ") AND (";
			
			$forcounter=0;
			foreach($wegarray as $weg) {
				$forcounter++;
				if($forcounter==1) {
					$roadsandeventsquerystring = $roadsandeventsquerystring . "road='".$weg."'";
				}
				else {
					$roadsandeventsquerystring = $roadsandeventsquerystring . " OR road='".$weg."'";
				}
			}
		
			$roadsandeventsquerystring = $roadsandeventsquerystring . ");";
			//query gebouwd, nu uitvoeren
			$resultroadsandeventsquery = mysql_query($roadsandeventsquerystring) or die("MySQL error. Query: " . $roadsandeventsquerystring . "<br /> Error: (" . mysql_errno() . ") " . mysql_error());
			//tel het aantal resultaten, we gaan ngl dat aantal verder verfijnen
			$roadsandeventsquerycount = mysql_num_rows($resultroadsandeventsquery);
			
			switch($roadsandeventsquerycount) {
				case 0:
					$antwoord = "Alles ziet er goed uit op je route! Happy driving!";
					break;
				case 1: //al die tags en toestanden hebben uiteindelijk maar 1 lijntje resultaat... Simpel: tweeten
					$roadsandeventsarray = mysql_fetch_array($resultroadsandeventsquery);
					
					$antwoord="Er ";
					
					switch($roadsandeventsarray['type']) {
						case "traffic_jam":
							$antwoord = $antwoord . "staat een file op de " . $roadsandeventsarray['road'];
							break;
						case "speed_traps":
							$antwoord = $antwoord . "staat een flitser op de " . $roadsandeventsarray['road'];
							break;
						case "accident":
							$antwoord = $antwoord . "is een ongeval gebeurd op de " . $roadsandeventsarray['road'];
							break;
						case "road_work":
							$antwoord = $antwoord . "is een werf op de " . $roadsandeventsarray['road'];
							break;
						case "incident":
							$antwoord = $antwoord . "is een incident op de " . $roadsandeventsarray['road'];
							break;
					}
					
					if($roadsandeventsarray['routeFrom']!="") {
						$antwoord = $antwoord . " van " . $roadsandeventsarray['routeFrom'] . 
							" naar ". $roadsandeventsarray['routeTo'] . " " . $roadsandeventsarray['segment'];
					}
					elseif($roadsandeventsarray['routeIn']!="") {
						$antwoord = $antwoord . " " . $roadsandeventsarray['details'] . " van " . 
							$roadsandeventsarray['routeIn'] . " " . $roadsandeventsarray['segment'];
					}
					else { 
						$antwoord = $antwoord . " " . $roadsandeventsarray['segment'];
					}
					echo "<h1>$antwoord</h1>";
					break; //einde case resultatentotaal = 1
					
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////// DOORDOEN VANAF HIER //////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

				default:
					$antwoord = "Er zijn meerdere events gaande op die wegen! Let op!";	
					$estring = "http://m.ikbenmartijn.be/m.php?only=none&e=";
					$estring = $estring . implode (",", $eventarray);
					$estring = $estring . "&r=";
					$estring = $estring . implode (",", $wegarray);
					$antwoord = $antwoord . " " . $estring;
					echo "<h1>$antwoord</h1>";
					break;	
			} //einde default bij meerdere resultaten totaalquery
		
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////// DOORDOEN TOT HIER //////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////		
		
			break; //einde case infoValue=3
	}
	//alle mogelijke cases zijn nu doorlopen, geef het antwoord terug aan de lusfunctie
	//einde MaakAntwoord()
	echo "ANTWOORD: $antwoord<hr/>";
	return $antwoord;
}

function AnalyseerZoekwoordOpType($zoekwoord) {
	//hier komt de parameter $type uit. Kan traffic_jam (file), accident (ongeval), speed_traps (snelheid) of road_work (werken) zijn. Default: null
	echo "Beginnen...<br/>";
	
	switch ($zoekwoord) {
    case "file":
        $type="traffic_jam";
        break;
    case "files":
        $type="traffic_jam";
        break;
    case "filevorming":
        $type="traffic_jam";
        break;
    case "vertraging":
        $type="traffic_jam";
        break;
    case "vertragingen":
        $type="traffic_jam";
        break;
    case "ongeval":
       $type="accident";
        break;
    case "ongevallen":
        $type="accident";
        break;
    case "incident":
       $type="incident";
        break;
    case "incidenten":
        $type="incident";
        break;
    case "flitser":
        $type="speed_traps";
        break;
    case "flitsers":
		$type="speed_traps";
        break;
    case "snelheidscontrole":
		$type="speed_traps";
        break;
    case "snelheidscontroles":
		$type="speed_traps";
        break;
    case "werken":
		$type="road_work";
        break;
    case "wegenwerken":
		$type="road_work";
        break;
    case "wegeniswerken":
		$type="road_work";
        break;
    case "werkzaamheden":
		$type="road_work";
        break;
	default:
		$type=null;
		break;
	}
	
	echo "Einde eventanalyse<br/>";
	return $type;
}

function AnalyseerZoekwoordOpWeg($zoekwoord) {
	echo "Beginnen...<br/>";
	
	switch (strtoupper($zoekwoord)) {
    case "A10":
        $weg="A10";
        break;
    case "A12":
        $weg="A12";
        break;
    case "A19":
        $weg="A19";
        break;
    case "A54":
        $weg="A54";
        break;
    case "E17":
        $weg="E17";
        break;
    case "E19":
       $weg="E19";
        break;
    case "E25":
        $weg="E25";
        break;
    case "E313":
       $weg="E313";
        break;
    case "E314":
        $weg="E314";
        break;
    case "E34":
        $weg="E34";
        break;
    case "E403":
		$weg="E403";
        break;
    case "E40":
		$weg="E40";
        break;
    case "E411":
		$type="E411";
        break;
    case "E429":
		$weg="E429";
        break;
    case "E42":
		$weg="E42";
        break;
    case "R0":
		$weg="R0";
        break;
	case "R1":
		$weg="R1";
        break;
    case "R20":
		$weg="R20";
        break;
    case "R2":
		$weg="R2";
        break;
	case "R3":
		$weg="R3";
        break;
    case "R4":
		$weg="R4";
        break;
    case "R8":
		$weg="R8";
        break;
	default:
		$weg=null;
		break;
	}
	
	echo "Einde weganalyse<br/>";
	return $weg;
}

function EventSyntax($type, $voud) {
	switch($type) {
		case "traffic_jam":
			if($voud=="enkel") {$eventsyntax="file";} else {$eventsyntax="files";}
			break;
		case "speed_traps":
			if($voud=="enkel") {$eventsyntax="flitser";} else {$eventsyntax="flitsers";}
			break;
		case "accident":
			if($voud=="enkel") {$eventsyntax="ongeval";} else {$eventsyntax="ongevallen";}
			break;
		case "road_work":
			if($voud=="enkel") {$eventsyntax="werf";} else {$eventsyntax="werven";}
			break;
		case "incident":
			if($voud=="enkel") {$eventsyntax="incident";} else {$eventsyntax="incidenten";}
			break;
	}
	return $eventsyntax;
}

function SchrijfAntwoordInDatabase($id,$antwoord) {
	echo "Antwoord in database schrijven<br/>";
	$querystring = "UPDATE incoming SET beantwoord=1, antwoord='" . $antwoord . "' WHERE id=$id;";
	mysql_query($querystring) or die("MySQL error. Query: " . $querystring . "<br /> Error: (" . mysql_errno() . ") " . mysql_error());
}
?>