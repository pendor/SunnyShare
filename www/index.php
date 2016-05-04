<?php include("shout.php"); ?><!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="/style.css"/>
	<title>Sunny+Share - Share Freely!</title>
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no, width=device-width"/>
  <script src="combo.js" type="text/javascript"></script>
</head>
<body>
  <div id="header">
		<a id="logo" href="/"><img src="/logo.png" alt="Sunny+Share" title="Sunny+Share - Share Freely"/></a>
		<a href="/Shared/">Library</a>
		<a href="/about.html">?</a> 
  </div>

  <div id="content">
    <div class="box">
      <a href="#" onclick="$(this).parent().hide(1000);" class="boxclose">X</a>
    <h1>What's This?!?</h1>
    
    <p>You've discovered a Sunny+Share box placed near-by by someone who
      values sharing and collaboration in the local area.  You may use
      this service to leave messages for others who are near by and share files.
      This site is <b>not</b> available on the internet.  You can access it only
      when near by &amp; connected to the Sunny+Share WiFi connection.
    </p>
          
    <p>For more information about this service, see <a href="/about.html">About Sunny+Share</a></p>
	  </div>
		<div id="shoutbox">
		  <h2>Announcement board: Leave messages for near-by wanderers</h2>
			<noscript><h2>Sorry, but chat won't work without JavaScript enabled.</h2></noscript>
      <?php 
      drawChatForm();
      drawChatBox();
      ?>
		</div>
  </div>
</body>
</html>
