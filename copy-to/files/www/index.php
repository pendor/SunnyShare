<?php if(!session_id()) session_start(); ?><!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="/style.css"/>
	<title>Sunny+Share - Share Freely!</title>
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no, width=device-width"/>
</head>
<body>
  <div id="header">
		<a id="logo" href="/"><img src="/logo.png" alt="Sunny+Share" title="Sunny+Share - Share Freely"/></a>
		<a href="/" class="current">Home</a> &bul;
		<a href="/Shared/">Library</a> &bul;
		<a href="/about.html">About</a> 
  </div>

  <div id="content">
    <div class="box">
    <h1>Welcome!</h1>
    
    <p>You've discovered a Sunny+Share box placed near-by by someone who
      values sharing and collaboration in the local area.  You may use
      this service to chat with others who are near by and share files.
    </p>
    
    <p>
      The contents of this service are <b>not</b> available on the public
      internet.  You can access them only while near-by and connected to
      the Sunny+Share WiFi connection.  Please respect this service and 
      share only material appropriate for the space you're in.  Inappropriate 
      or unlawful content will be removed.</p>
      
    <p>For more information about this service, see <a href="/about.html">About Sunny+Share</a></p>
	  </div>
  		<div id="shoutbox">
  		  <h2>Chat with others near-by in real time:</h2>
  			<noscript><h2>Sorry, but chat won't work without JavaScript enabled.</h2></noscript>
  			<?php include("shoutbox/shoutbox.inc.php"); ?>
  		</div>
  </div>
</body>
</html>
