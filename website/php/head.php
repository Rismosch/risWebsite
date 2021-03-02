<?php

include_once 'secret/secret.php';

include_once '3rd_party_libraries/Mobile-Detect-2.8.34/Mobile_Detect.php';
$detect = new Mobile_Detect;
$isMobile = $detect->isMobile() && !$detect->isTablet();

echo '
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	
	<link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
	<link rel="icon" type="image/png" href="favicon.png" sizes="32x32">
	<link rel="shortcut icon" href="favicon.ico">
	<meta name="msapplication-TileImage" content="mstile-144x144.png">
	<meta name="msapplication-TileColor" content="#00aba9">
	
	'; if(!$isMobile) echo '<link rel="stylesheet" href="css/desktop.css">'; echo '
	<link rel="stylesheet" href="css/main.css">
	
	<script src="3rd_party_libraries/disqusloader.js"></script>
	<script src="util.js"></script>
	
	<script src="https://www.google.com/recaptcha/api.js"></script>
';

?>