<?php 

require_once('config.php');
require_once('functions.php');

if(isset($_POST['message'])) {
  $message = trim($_POST['message']);
  $name = trim($_POST['name']);
  
  if(strlen($name) && strlen($message)) {
    if(strlen($message) > $chat_maxLen) {
    	$message = substr($message, 0, $chat_maxLen);
    	$str_position = strrpos($message, ' ');
    	$message = substr($message, 0, $str_position) . " ...";
    }

    //add cookie to store name
    setcookie("name_chat", $name, $cookieTime, '/');
    
    echo $newJson = chat_addMessage($name, $message, $secId);
    exit;
  }
} else if(isset($_POST['d']) && isset($_POST['tm'])) {
  // Try to delete a message
  echo chat_delMessage($secId, $_POST['tm']);
}

function drawChatBox() {
  global $chat_dataFile, $chat_refreshTime;
?>
<div class="grid" id="chat"></div>
<script type="text/javascript">
var cacheCount = 1;
var refresh = <?=$chat_refreshTime?>;
var refreshHandle = null;

function postMessage() {
  if(refreshHandle != null) {
    window.clearInterval(refreshHandle);
   refreshHandle = null;
  }
  
  var ajax = new XMLHttpRequest();
  ajax.onreadystatechange = function() {
    if(ajax.readyState == 4 && ajax.status == 200) {
      $('#message').val('');
      drawChat(JSON.parse(ajax.responseText));
      cacheCount++;
      refreshHandle = window.setInterval(fetchChat, refresh);
    }
  }
  
	ajax.open('POST', "/<?= pathinfo(__FILE__, PATHINFO_BASENAME) ?>");
	var formdata = new FormData();
	formdata.append('name', $('#name').val());
	formdata.append('message', $('#message').val());
	ajax.send(formdata);
}

function fetchChat() {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() {
    if(xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      drawChat(JSON.parse(xmlhttp.responseText));
    }
  };

  xmlhttp.open('GET', "/<?= pathinfo($chat_dataFile, PATHINFO_BASENAME) ?>?c=" + cacheCount);
  xmlhttp.send();
}

function drawChat(arr) {
  var chat = $('#chat');
  
  var matchedIds = [ ];
  
  for(var i = 0; i < arr.length ; i++) {
    matchedIds.push('el' + arr[i].t);
    var posted = moment.unix(arr[i].t);
    var relTime = posted.fromNow();
    var absTime = posted.format('MMMM Do YYYY, h:mm:ss a');
    var msg;
    if(arr[i].hasOwnProperty('p') && arr[i].p == '1') {
      msg = arr[i].n;
    } else {
      msg = htmlEntities(arr[i].n);
    }
    
    var secId = getCookie('secid');
    if(chat.find('#el' + arr[i].t).length == 0) {
      var msgHash = arr[i].hasOwnProperty('i') ? arr[i].i : 'INVALID';
      var del = '';
      var delHash = Sha1.hash(secId + arr[i].t);
      if(delHash == msgHash) {
        del = ' <a href="#" class="delbtn" onclick="delMesg(' + arr[i].t + ', $(this).parent());">Delete</a> '
      }
      chat.prepend('<div id="el' + arr[i].t + '" class="grid-item chatbox" style="background-color: ' + arr[i].c + '">' +
        '<span class="chat-name">' + msg + del + '</span><br/> ' +
        '<span class="chat-message">' + htmlEntities(arr[i].m) + '</span><br/>' + 
        '<abbr class="chat-time" title="' + absTime + '">' + relTime + '</abbr></div>');
    }
  }
  
  $('#chat').find('div').each(function() {
    var thisId = $(this).attr('id')
    if(matchedIds.indexOf(thisId) == -1) {
      $(this).remove();
    }
  });
  
  chat.masonry('reloadItems');
  chat.masonry('layout');
}

function delMesg(tm, el) {
  if(refreshHandle != null) {
    window.clearInterval(refreshHandle);
   refreshHandle = null;
  }
  el.remove();
  
  var ajax = new XMLHttpRequest();
  ajax.onreadystatechange = function() {
    if(ajax.readyState == 4 && ajax.status == 200) {
      drawChat(JSON.parse(ajax.responseText));
      cacheCount++;
      refreshHandle = window.setInterval(fetchChat, refresh);
    }
  }
  
	ajax.open('POST', "/<?= pathinfo(__FILE__, PATHINFO_BASENAME) ?>");
	var formdata = new FormData();
	formdata.append('tm', tm);
	formdata.append('d', '1');
	ajax.send(formdata);
}


$('#chat').masonry({itemSelector: '.grid-item'});

fetchChat();
refreshHandle = window.setInterval(fetchChat, refresh);
</script>

<?php } 

function drawChatForm() {
?>
<form name="inpform" method="post" onsubmit="postMessage(); return false;">
<b>Nickname:</b> 
<input name="name" id="name" type="text" 
  value="<?= isset($_COOKIE['name_chat']) ? $_COOKIE['name_chat'] : '' ?>" size="20" /><input type="submit" value="Post" /><br/>
<input type="text" id="message" name="message" placeholder="Type your message here" size="40"/>
</form> 
<?php } ?>
