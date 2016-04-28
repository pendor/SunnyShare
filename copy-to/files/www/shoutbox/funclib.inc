<?php
/*********************************************************************************************************
 This code is part of the ShoutBox software (www.gerd-tentler.de/tools/shoutbox), copyright by
 Gerd Tentler. Obtain permission before selling this code or hosting it on a commercial website or
 redistributing it over the Internet or in any other medium. In all cases copyright must remain intact.
*********************************************************************************************************/

//========================================================================================================
// Functions
//========================================================================================================

	//------------------------------------------------------------------------------------------------------
	// View MySQL error message
	//
	function sql_error() {
		echo '<font color="red">' . mysql_errno() . ': ' . mysql_error() . '</font><br>';
	}

	//------------------------------------------------------------------------------------------------------
	// Connect with MySQL server and select database
	// Arguments: server name, user name, user password, database name
	//
	function db_open($db_server, $db_user, $db_pass, $db_name) {
		$status = true;

		if(!@mysql_connect($db_server, $db_user, $db_pass)) {
			sql_error();
			$status = false;
		}
		else if(!@mysql_select_db($db_name)) {
			sql_error();
			$status = false;
		}
		return $status;
	}

	//------------------------------------------------------------------------------------------------------
	// Convert timestamp to YYYY-MM-DD HH:MM:SS
	// Arguments: timestamp (YYYYMMDDHHMMSS)
	//
	function timeStamp($ts) {
		return substr($ts, 0, 4) . '-' . substr($ts, 4, 2) . '-' . substr($ts, 6, 2) . ' ' .
			   substr($ts, 8, 2) . ':' . substr($ts, 10, 2) . ':' . substr($ts, 12);
	}

	//------------------------------------------------------------------------------------------------------
	// Cut text to a specified length
	// Arguments: text, length
	//
	function cutString($str, $length) {
		if(strlen($str) > $length) {
			$words = explode(' ', $str);
			$wCnt = count($words);

			if($wCnt == 1) {
				$str = substr($str, 0, $length) . '...';
			}
			else {
				$str = '';
				$cnt = 0;

				while($cnt < $wCnt && strlen($str) < $length) {
					$str .= trim($words[$cnt++]) . ' ';
				}
				if($cnt < $wCnt) $str .= '...';
			}
		}
		return $str;
	}

	//------------------------------------------------------------------------------------------------------
	// Search text between start-tag and end-tag
	// Arguments: text, start-tag, end-tag
	//
	function searchCode($str, $pStart, $pEnd) {
		$matches = array();
		$a = strlen($pStart);
		$b = strlen($pEnd);

		do {
			$y = 0;

			if(strlen($str) > strlen($pStart) + strlen($pEnd)) {
				$s = strtolower($str);
				$x = strpos($s, $pStart);
				$y = strpos($s, $pEnd, $x + $a);
				$z = strpos($s, $pStart, $x + $a);

				if($x >= 0 && $y) {
					while($z && $z < $y) {
						$y = strpos($s, $pEnd, $y + $b);
						$z = strpos($s, $pStart, $z + $a);
					}
					$y += $b;
					$matches[] = substr($str, $x, $y - $x);
					$str = substr($str, $y);
				}
			}
		}
		while($x >= 0 && $y > 0);

		return $matches;
	}

	//------------------------------------------------------------------------------------------------------
	// Convert text between start-tag and end-tag to base64
	// Arguments: text, start-tag, end-tag
	//
	function encodeString($str, $pStart, $pEnd) {
		$matches = searchCode($str, $pStart, $pEnd);

		for($i = 0; $i < count($matches); $i++) {
			$str = str_replace($matches[$i], chr(1) . base64_encode($matches[$i]) . chr(2), $str);
		}
		return $str;
	}

	//------------------------------------------------------------------------------------------------------
	// Decode base64-text
	// Arguments: text
	//
	function decodeString($str) {
		if(preg_match_all('/' . chr(1) . '([^' . chr(2) . ']+)' . chr(2) . '/', $str, $m)) {
			for($i = 0; $i < count($m[0]); $i++) {
				$str = str_replace($m[0][$i], base64_decode($m[1][$i]), $str);
			}
		}
		return $str;
	}

	//------------------------------------------------------------------------------------------------------
	// Delete repeated characters (more than 3 times)
	// Arguments: text
	//
	function checkRepeats($str) {
		$newstr = substr($str, 0, 3);

		for($i = 3; $i < strlen($str); $i++) {
			if($str[$i] != $str[$i-1] || $str[$i] != $str[$i-2] || $str[$i] != $str[$i-3]) $newstr .= $str[$i];
		}
		return $newstr;
	}

	//------------------------------------------------------------------------------------------------------
	// Replace long words with image
	// Arguments: text, max. word length
	//
	function checkLongWords($str, $wordLength) {
		global $imgPath;

		if($wordLength && strlen($str) > $wordLength) {
			$html = array();

			if(preg_match_all('/<[a-z\/][^>]+>/i', $str, $m)) {
				for($i = 0; $i < count($m[0]); $i++) {
					$html[$i] = $m[0][$i];
					$str = str_replace($m[0][$i], " &HTML$i; ", $str);
				}
			}

			$str = str_replace("\r\n", "\n", $str);
			$str = str_replace("\r", "\n", $str);
			$lines = explode("\n", $str);
			$str = '';

			for($i = 0; $i < count($lines); $i++) {
				$words = explode(' ', $lines[$i]);

				for($j = 0; $j < count($words); $j++) {
					$word = function_exists('html_entity_decode') ? html_entity_decode($words[$j]) : $words[$j];

					if(strlen($word) > $wordLength && !preg_match('/&#\d{1,6};/', $words[$j])) {
						if(preg_match('%^(ftp|https?)://%i', $word)) {
							$str .= "<span title=\"$word\" style=\"cursor:default\">" .
											substr($word, 0, $wordLength) . '...' . '</span>';
						}
						else $str .= '<img src="' . $imgPath . 'angry.gif" width="31" height="20" align="absmiddle">';
					}
					else $str .= $words[$j] . ' ';
				}
				if($i < count($lines) - 1) $str .= "\n";
			}

			if(preg_match_all('/ &HTML(\d+); /', $str, $m)) {
				for($i = 0; $i < count($m[0]); $i++) {
					$str = str_replace($m[0][$i], $html[$m[1][$i]], $str);
				}
			}
		}
		return $str;
	}

	//------------------------------------------------------------------------------------------------------
	// Add image size and replace oversized images with thumbnail and invalid images with icon
	// Arguments: text, max. image width
	//
	function checkImages($str, $maxWidth) {
		global $imgPath;

		if(preg_match_all('/ src="([^">]+)"/i', $str, $m)) {
			for($i = 0; $i < count($m[0]); $i++) {
				list($width, $height, $type) = @getimagesize($m[1][$i]);

				if(!$width || $type < 1 || $type > 3) {
					$width = 17;
					$height = 14;
					$img = $imgPath . 'noimage.gif';
				}
				else if($width > $maxWidth) {
					$perc = $maxWidth / $width;
					$width = round($width * $perc);
					$height = round($height * $perc);
					$img = $imgPath . "thumbnail.php?width=$width&height=$height&file=" . urlencode($m[1][$i]);
				}
				else $img = $m[1][$i];

				if($img != $imgPath . 'nourl.gif' && $img != $imgPath . 'nohtml.gif') {
					$size = 'width="' . $width . '" height="' . $height . '"';
					$str = str_replace($m[0][$i], ' src="' . $img . '" ' . $size, $str);
				}
			}
		}
		return $str;
	}

	//------------------------------------------------------------------------------------------------------
	// Check if text contains HTML links or URLs
	// Arguments: text, replace text (optional)
	//
	function checkLinks($str, $replace = '') {
		$link1 = '<a .+?>.*?<\/a>';
		$link2 = '<iframe .+?>.*?<\/iframe>';
		$url1 = '(https?|ftp):\/\/([\w._-]+:[\w._-]+@)?[\w#._\/~-]+(\?([\w_-]+(=[\w+%?#_-]+(&(amp;)?)?)?)*)?';
		$url2 = 'www\.\w[\w-]+\.[\w#._\/~-]+(\?([\w_-]+(=[\w+%?#_-]+(&(amp;)?)?)?)*)?';

		if($replace != '') {
			return preg_replace("/$link1|$link2|$url1|$url2/i", $replace, $str);
		}
		return preg_match("/$link1|$link2|$url1|$url2/i", $str);
	}

	//------------------------------------------------------------------------------------------------------
	// Replace smilies
	// Arguments: text
	//
	function replaceSmilies($str) {
		global $sm, $imgPath;

		if(count($sm)) {
			$str = str_replace('://', ':�/', $str);
			reset($sm);

			while(list($code, $img) = each($sm)) {
				$image = '<img src="' . $imgPath . 'smilies/' . $img . '" width="15" height="15" align="absmiddle">';
				$str = str_replace($code, $image, $str);
			}
			$str = str_replace(':�/', '://', $str);
		}
		return $str;
	}

	//------------------------------------------------------------------------------------------------------
	// Replace bad words with image
	// Arguments: text
	//
	function replaceNonos($str) {
		global $nonos, $imgPath;

		$repl = '<img src="' . $imgPath . 'angry.gif" border="0" width="31" height="20" align="absmiddle">';
		$c = '(\_|[^\d\w\r\n])*';
		$cl = strlen($c);

		for($i = 0; $i < count($nonos); $i++) {
			if($nonos[$i] != '') {
				$expr = chunk_split($nonos[$i], 1, $c);
				$str = preg_replace('/' . substr($expr, 0, strlen($expr) - $cl) . '/i', $repl, $str);
			}
		}
		return $str;
	}

	//------------------------------------------------------------------------------------------------------
	// Replace URLs with image
	// Arguments: text
	//
	function replaceURLs($str) {
		global $allowUBBs, $imgPath;

		if($allowUBBs) $str = encodeString($str, '[code]', '[/code]');
		$img = ' <img src="' . $imgPath . 'nourl.gif" border="0" width="30" height="15" align="absmiddle"> ';

		$str = preg_replace('/\[url(=[^\]]+)?\]([^\[]+)\[(\/|\*)url\]/i', $img, $str);

		if(preg_match_all('/(<img [^>]+>)|(\[img\][^\[]+\[(\/|\*)img\])/i', $str, $m)) {
			for($i = 0; $i < count($m[0]); $i++) {
				$str = str_replace($m[0][$i], '�i1�' . base64_encode($m[0][$i]) . '�i2�', $str);
			}
		}
		$str = checkLinks($str, $img);

		if(preg_match_all('/�i1�([^�]+)�i2�/', $str, $m)) {
			for($i = 0; $i < count($m[0]); $i++) {
				$str = str_replace($m[0][$i], base64_decode($m[1][$i]), $str);
			}
		}
		if($allowUBBs) $str = decodeString($str);

		return $str;
	}

	//------------------------------------------------------------------------------------------------------
	// Replace HTML tags with image
	// Arguments: text
	//
	function replaceHTML($str) {
		global $allowUBBs, $imgPath;

		if($allowUBBs) $str = encodeString($str, '[code]', '[/code]');
		$img = ' �i1�img src="' . $imgPath . 'nohtml.gif" border="0" width="30" height="15" align="absmiddle"�i2� ';
		$str = preg_replace('/<[a-z\/][^>]*>/i', $img, $str);
		$str = preg_replace('/&([^#])/', '&amp;\\1', $str);
		$str = str_replace('<', '&lt;', $str);
		$str = str_replace('>', '&gt;', $str);
		$str = str_replace('�i1�', '<', $str);
		$str = str_replace('�i2�', '>', $str);
		if($allowUBBs) $str = decodeString($str);

		return $str;
	}

	//------------------------------------------------------------------------------------------------------
	// Replace UBB codes with HTML tags
	// Arguments: text, [text is one line (true or false)]
	//
	function replaceUBBs($str, $textline = false) {
		global $allowURLs;

		if(!$textline) {
			$matches = searchCode($str, '[code]', '[/code]');

			for($i = 0; $i < count($matches); $i++) {
				$new = preg_replace('/ {2}/', '&nbsp; ', htmlspecialchars($matches[$i]));
				$new = preg_replace("/\[code\](\r?\n)*/i", '<div class="cssCode">', $new);
				$new = preg_replace("/(\r?\n)*\[(\/|\*)code\]/i", '</div>', $new);
				if(!$allowURLs) $new = replaceURLs($new);
				$str = str_replace($matches[$i], $new, $str);
			}
			$str = preg_replace("/(<\/div>)\r?\n/", '\\1', $str);

			$str = preg_replace("/\[quote\](\r?\n)*/i", '<div class="cssQuote">', $str);
			$str = preg_replace("/\[quote=[\"\']?([^\"\'\]]+)[\"\']?\](\r?\n)*/i", '<div class="cssQuote">\\1: ', $str);
			$str = preg_replace("/(\r?\n)*\[(\/|\*)quote\](\r?\n)?/i", '</div>', $str);
			$str = preg_replace('/\[img\]([^"\[]+)\[(\/|\*)img\]/i', ' <img src="\\1" align="absmiddle"> ', $str);
			$str = preg_replace('/\[(sup|sub)\]/i', '<\\1>', $str);
			$str = preg_replace('/\[(\/|\*)(sup|sub)\]/i', '</\\2>', $str);
			$str = preg_replace('/(\r?\n)*\[\*\]/', '<li>', $str);
			$str = preg_replace('/\[list( |&nbsp;)+type=[\"\']?(1|a|i)[\"\']?\](\r?\n)*/i', '<ol type="\\2" style="margin-top:0px; margin-bottom:0px">', $str);
			$str = preg_replace('/\[list\](\r?\n)*/i', '<ol style="margin-top:0px; margin-bottom:0px">', $str);
			$str = preg_replace('/(\r?\n)*\[(\/|\*)list\](\r?\n)?/i', '</ol>', $str);
		}
		else $str = preg_replace('/\[(\/|\*)?(code|quote|img|sup|sub|list( type=(1|a|i))?|\*)\]/i', '', $str);

		$str = preg_replace('/\[(b|i|u)\]/i', '<\\1>', $str);
		$str = preg_replace('/\[(\/|\*)(b|i|u)\]/i', '</\\2>', $str);
		$str = preg_replace('/\[email\]([^"\[]+)\[(\/|\*)email\]/i', '<a href="mailto:\\1">\\1</a>', $str);

		if($allowURLs) {
			$str = preg_replace('/\[url\]((https?|ftp):\/\/[^"\[]+)\[(\/|\*)url\]/i', '<a href="\\1" target="_blank">\\1</a>', $str);
			$str = preg_replace('/\[url\]([^"\[]+)\[(\/|\*)url\]/i', '<a href="http://\\1" target="_blank">\\1</a>', $str);
			$str = preg_replace('/\[url=[\"\']?((https?|ftp):\/\/[^"\[]+)[\"\']?\]([^\[]+)\[(\/|\*)url\]/i', '<a href="\\1" target="_blank">\\3</a>', $str);
			$str = preg_replace('/\[url=[\"\']?([^\"\'\]]+)[\"\']?\]([^\[]+)\[(\/|\*)url\]/i', '<a href="http://\\1" target="_blank">\\2</a>', $str);
		}
		return $str;
	}

	//------------------------------------------------------------------------------------------------------
	// Get entity for multibyte character
	// Arguments: multibyte character (2 bytes)
	//
	function mb2entity($char) {
		$code = ord($char[1]) % 128;
		$c = (ord($char[0]) % 252 % 248 % 240 % 224 % 192) + 128;
		$code += ($c % 128) * 64;
		return '&#' . $code . ';';
	}

	//------------------------------------------------------------------------------------------------------
	// Replace multibyte characters with entities
	// Arguments: text
	//
	function replaceMbChars($str) {
		return preg_replace('/[\xC0-\xF7][\x80-\xBF]/e', 'mb2entity("\\0")', $str);
	}

	//------------------------------------------------------------------------------------------------------
	// Format text
	// Arguments: text, max. word length, max. image width, [text is one line (true or false)]
	//
	function format($str, $wordLength, $imgWidth, $textline = false) {
		global $textLength, $allowHTML, $allowUBBs, $allowURLs;

		$str = preg_replace('/(\s){2}/', '\\1', $str);
		$str = replaceMbChars($str);
		if($textLength) $str = cutString($str, $textLength);
		if(!$allowHTML) $str = replaceHTML($str);
		if(!$allowURLs) $str = replaceURLs($str);
		if($allowUBBs) $str = replaceUBBs($str, $textline);
		if($allowHTML || $allowUBBs) $str = checkImages($str, $imgWidth);
		$str = checkRepeats($str);
		$str = checkLongWords($str, $wordLength);
		$str = replaceSmilies($str);
		$str = replaceNonos($str);
		$str = nl2br($str);

		return $str;
	}

	//------------------------------------------------------------------------------------------------------
	// Check for spam
	// Arguments: message ID, timestamp, name, e-mail, subject, text, [message signature], [empty field]
	//
	function checkSpam($id, $tstamp, $name, $email, $subject, $text, $signature = '', $empty = '') {
		global $PHP_SELF, $HTTP_REFERER, $HTTP_USER_AGENT, $agents, $allowURLs, $allowHTML,
			   $enableIDs, $enableSignature, $enableLinkCheck, $enableRefererCheck, $enableAgentCheck;

		$sec = time() - $tstamp;

		if('' != $empty) return true;
		if($name && preg_match("/\r|\n/", $name)) return true;
		if($email && preg_match("/[\r\n<>\"]/", $email)) return true;
		if($subject && preg_match("/\r|\n/", $subject)) return true;
		if($tstamp != -1 && ($sec < 5 || $sec > 20 * 60)) return true;
		if($enableIDs && (!$id || $id != $_SESSION['msgID'])) return true;
		if($enableSignature && (!$signature || $signature != $_SESSION['secCode'])) return true;
		if($enableRefererCheck && !ereg($PHP_SELF, $HTTP_REFERER)) return true;
		if($enableLinkCheck && !$allowURLs && checkLinks($text)) return true;
		if($enableAgentCheck) {
			for($i = $found = 0; $i < count($agents) && !$found; $i++) {
				if(eregi($agents[$i], $HTTP_USER_AGENT)) $found++;
			}
			if(!$found) return true;
		}
		return false;
	}
?>
