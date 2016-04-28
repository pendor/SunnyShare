======================================================================================================================
INTRODUCTION
======================================================================================================================
This is a PHP chat-box with integrated spam protection. It supports smilies / multiple languages and can be used
with or without a MySQL database.

======================================================================================================================
LICENSE
======================================================================================================================
This script is freeware for non-commercial use. If you like it, please feel free to make a donation!
However, if you intend to use the script in a commercial project, please donate at least EUR 10.
You can make a donation on my website: http://www.gerd-tentler.de/tools/shoutbox/.

======================================================================================================================
USAGE
======================================================================================================================
Extract the files to your webserver and adapt the configuration (config_main.inc.php, shoutbox.css) to your needs.
Then include shoutbox.inc.php in your website (PHP file) where you want your chat-box to appear. ShoutBox will try
to create the necessary MySQL table automatically, according to your database settings in config_main.inc.php. If
you don't set a database, ShoutBox will use the data directory to save the chat-box entries, so make sure that PHP
has write permission to this directory.

Since version 1.8, there are two configuration files:

- config_main.inc.php is the main configuration file; you must adapt it to your needs
- config_local.inc.php is only used for offline testing; if you don't test offline, you don't need to modify
  this file

NOTE: You should change the administrator password (config_main.inc.php) for security reasons! Also please note
that you need PHP 4.1.0 or higher with session support if you want to log in as administrator.

In the configuration you can enable or disable the input of HTML tags, URLs, and UBB codes. If you enable UBB
or HTML, but disable URLs, the UBB code [url] and the HTML tag <A> won't work. In order to make them work, you
have to enable URLs, too. However, you should consider that this will make your chat-box more vulnerable to
malicious URLs.

NOTE: For security reasons, it's strongly recommended to disable HTML. By default, HTML and URLs are disabled.
Enable them on your own risk!

If UBB is enabled (it is by default), images can be inserted with the UBB code [img], and if HTML is enabled, they
can be inserted with the <IMG> tag. Note that if the image width is bigger than the chat-box width, ShoutBox will
try to resize the image.

You can also enable or disable unique message IDs which help to protect your chat-box from spam bots. Please note
that like the admin feature this feature needs PHP 4.1.0 or higher with session support. If message IDs are enabled
(they are by default), you must start a session in your PHP script where you included shoutbox.inc.php. This has to
be done before any headers are sent:

  if(!session_id()) session_start();

Additionally you can enable or disable user agent checks against spam bots. Please note that the Apache variable
HTTP_USER_AGENT is required for this check, so if you are using another HTTP server, make sure that this variable
is supported or disable this check.

Here's an example how you can add the chat-box to your website:

  <?php if(!session_id()) session_start(); ?>
  <html>
  <head>
  ...
  </head>
  <body>
  ...
  <?php
    $pathToShoutBox = 'tools/shoutbox';
    include("$pathToShoutBox/shoutbox.inc.php");
  ?>
  ...
  </body>
  </html>

The file must be saved with the extension .php, for instance chat.php, and of course your webserver must have PHP
installed.

$pathToShoutBox is the path where you extracted the ShoutBox files. In this example, it is a relative path to the
subfolder "tools/shoutbox", but of course you can also use an absolute path instead.

Don't forget to set the $boxFolder variable within the configuration file accordingly - this has to be a WEB path,
though. In this example, the chat-box is located within the subfolder "tools/shoutbox" of the website, so this is
also the correct setting for the $boxFolder variable.

It is not necessary to use a MySQL database, the chat-box will also work without it. But if you want to use a
database, you must adapt the configuration file and enter the credentials there.

======================================================================================================================
Source code + example available at http://www.gerd-tentler.de/tools/shoutbox/.
======================================================================================================================
