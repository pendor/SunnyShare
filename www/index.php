<?php 
require_once('functions.php');
require_once('shout.php'); 
printHeader();
?>
    <div class="box" id="aboutbox">
      <a href="#" onclick="document.cookie = 'boxclosed=1'; $(this).parent().hide(1000);" class="boxclose">X</a>
      <h1>What's This?!?</h1>
    
      <p>You've discovered a Sunny+Share box placed near-by by someone who
        values sharing and collaboration in the local area.  You may use
        this service to leave messages for others who are near by and share files.
        This site is <b>not</b> available on the internet.  You can access it only
        when near by &amp; connected to the Sunny+Share WiFi connection.
      </p>
          
      <p>For more information about this service, see <a href="/about.php">About Sunny+Share</a></p>
	  </div>
    
		<div id="shoutbox">
		  <h2>Announcement board: Leave messages for near-by wanderers</h2>
			<noscript><h2>Sorry, but chat won't work without JavaScript enabled.</h2></noscript>
      <?php 
      drawChatForm();
      drawChatBox();
      ?>
		</div>
    <script type="text/javascript">
    if(document.cookie.indexOf('boxclosed') >= 0) {
      $('#aboutbox').hide();
    }
    </script>
<?php printFooter(); ?>
