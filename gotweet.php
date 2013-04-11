<?php //include('dbchecker.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>@hoeishetverkeer vandaag?</title>
<script src="http://platform.twitter.com/anywhere.js?id=RcYSKp48299J2GS5SxHww&v=1" type="text/javascript"></script>
<link href='http://fonts.googleapis.com/css?family=Signika:300,400,700,600' rel='stylesheet' type='text/css'>
<style>
* {
	margin: 0;
	padding: 0;
}

body {
	background: #333 url(images/darkdenim3.png);
}

#wrapper {
	background: #DDD url(images/bgnoise_lg.png);
	width: 750px;
	height: 750px;
	
	-moz-box-shadow: 0 0 20px 5px #111;
	-webkit-box-shadow: 0 0 20px 5px #111;
	box-shadow: 0 0 20px 5px #111;
	
	margin-left: auto;
	margin-right: auto;
	margin-top: 40px;
	
	-moz-border-radius: 20px; 
	-webkit-border-radius: 20px;
	
	clear: both;
}

#uitleg {
	position: relative;
	top: 35px;
}

#uitleg h1 {
	font-family: Signika;
	font-weight: 700;
	font-size: 35px;
	text-align: center;
	margin-bottom: 15px;
}

#uitleg p {
	font-family: Signika;
	font-weight: 400;
	font-size: 20px;
	text-align: center;
	line-height: 1.2em;
	margin-bottom: 10px;
	
	width: 600px;
	margin-left: auto;
	margin-right: auto;
}

ul {
	font-family: Signika;
	font-weight: 400;
	font-size: 20px;
	text-align: center;
	line-height: 1.2em;
	margin-bottom: 50px;
	
	list-style: none;
	margin-top: 50px;
	margin-left: 100px;
}

li {
	text-align: left;
	margin-bottom: 15px;
	}

#uitleg p a {
	color: #000;
	text-decoration: none;
}

.lichter {
	font-family: Signika;
	font-weight: 300;
	font-size: 14px;
}

.beta {
	font-family: Signika;
	font-weight: 300;
	font-size: 10px;
	vertical-align: super;
}

#rot {
	position: relative;
	top: 37px; /*61px;*/
	
	height: 495px;
	
	margin-left: auto;
	margin-right: auto;
	
	text-align: center;
	
	overflow: hidden; 
}

#terug {
	position: relative;
	top: 215px;
	left: 15px;
	
	font-family: Signika;
	font-weight: 400;
	font-size: 20px;
}

#terug a {
	color: #000;
	text-decoration: none;
}

#footer {
	clear: both;
	width: 750px;
	margin-left: auto;
	margin-right: auto;
	margin-top: 15px;
}

#footer #text {
	width: 600px;
	float:left;
}

#footer #tweet {
	width: 100px;	
	float:right;
}

#footer p {
	color: #FFF;
	
	font-family: Signika;
	font-weight: 300;
	font-size: 11px;
	
	text-align: left;
	margin-left: 10px;
}

#footer a {
	color: #FFF;
	text-decoration: none;
}

.twitter-share-buttonp {
	float: right;
	width: 100px;
	position: relative;
}

#tbox {
	margin-left: auto;
	margin-right: auto;
	width: 400px;
}
</style>
</head>

<body>

<div id="wrapper">
	<div id="uitleg">
    	<h1>Probeer even deze tweets:</h1>
    	<p>
        	<ul>
            	<li>@hoeishetverkeer op de #E40?
                <li>@hoeishetverkeer Staan er ergens #flitsers?
                <li>@hoeishetverkeer Zijn er #ongevallen gebeurd op de #E403?
                <li>@hoeishetverkeer Staan er #files op de #E17 of op de #E40?
            </ul>
        </p>
		<div id="tbox" style="margin: 0 auto;"></div>
		<script type="text/javascript">
        
        twttr.anywhere(function (T) {
        
        T("#tbox").tweetBox({
        height: 150,
        width: 400,
		label: '',
        defaultContent: "@hoeishetverkeer op de #E17 vandaag?"
        });
        
        });
    </script>
	</div>

<div id="terug">
	<a href="index.php">&larr; Terug</a>
</div>

</div>

<div id="footer">
  <div id="text">
            <p>&copy Martijn Vandenberghe 2012 - <a href="https://twitter.com/#!/ikbenmartijn/" target="_blank">@ikbenmartijn</a>.<br/>Alle gebruikte data is afkomstig van de <a href="http://www.vrt.be/" target="_blank">VRT</a>. Dit is een inzending voor <a href="http://www.vrt.be/mashup" target="_blank">VRT Mashup</a>.</p>
        </div>
        <div id="tweet">
        <p class="twitter-share-buttonp"><a href="https://twitter.com/share" class="twitter-share-button" data-url="hoeishetverkeer.ikbenmartijn.be" data-text="Check het verkeer met @hoeishetverkeer!" data-via="ikbenmartijn" data-lang="nl">Tweeten</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></p>
		</div>
    </div>
</body>
</html>
