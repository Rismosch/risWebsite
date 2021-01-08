<?php

include_once 'secret/contactEmail.php';
include_once 'secret/dbConn.php';
include_once 'secret/reCAPTCHA.php';

include_once 'Mobile-Detect-2.8.34/Mobile_Detect.php';
$detect = new Mobile_Detect;
$isMobile = $detect->isMobile() && !$detect->isTablet();

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title>Rismosch</title>
	
	<link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
	<link rel="icon" type="image/png" href="favicon.png" sizes="32x32">
	<link rel="shortcut icon" href="favicon.ico">
	<meta name="msapplication-TileImage" content="mstile-144x144.png">
	<meta name="msapplication-TileColor" content="#00aba9">
	
	<link rel="stylesheet" href="css/colors.css">
	<link rel="stylesheet" href="css/main.css">
	<link rel="stylesheet" href="css/<?php
		if( $isMobile )
			echo "mobile";
		else
			echo "desktop";
	?>.css">
	
	<script src="scripts/jquery-3.5.1.min.js"></script>
	<script src="scripts/banner.js"></script>
	<script src="scripts/continuousSession.js"></script>
	<script src="scripts/cookie.js"></script>
	<script src="scripts/util.js"></script>
</head>
<body>
	<div class="background">
		<a href="https://www.rismosch.com/">
			<img
				id="banner"
				class="banner pixel_image"
				src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
				onmouseover="playHoverAnimation()"
			>
		</a>
		
		<div class="selector" id="selector">
			<ul class="selector_tabs" id="selector_tabs">
				<li class="selector_tab" id="selector_tab">
					<a href="https://www.rismosch.com/">
						<div><b>Home</b></div>
					</a>
				</li>
				<li class="selector_tab" id="selector_tab">
					<a href="https://www.rismosch.com/blog">
						<div><b>Blog</b></div>
					</a>
				</li>
				<li class="selector_tab" id="selector_tab">
					<a href="https://www.rismosch.com/projects">
						<div><b>Projects</b></div>
					</a>
				</li>
				<li class="selector_tab" id="selector_tab">
					<a href="https://www.rismosch.com/about">
						<div><b>About</b></div>
					</a>
				</li>
			</ul>
		</div>
		
		<div class="content" id="content">
			<h1>Licenses</h1>
			
			<h2>Used Logos</h2>
			
			<p>The 5 social-media logos below each site on rismosch.com were not designed by me and don't belong to me. Each logo has been taken from the official brand-resources site of the respective platform.</p>
			
			<table class="licenses_table">
				<tr class="row_empty row_devider"><td></td></tr>
				
				<tr><td><b>YouTube</b></td></tr>
				<tr><td><a href="https://www.youtube.com/intl/de/about/brand-resources/#logos-icons-colors">source</a> last referenced 24th OCT, 2020</td></tr>
				
				<tr class="row_empty"><td></td></tr>
				<tr class="row_empty row_devider"><td></td></tr>
				
				<tr><td><b>Bandcamp</b></td></tr>
				<tr><td><a href="https://bandcamp.com/buttons">source</a> last referenced 24th OCT, 2020</td></tr>
				
				<tr class="row_empty"><td></td></tr>
				<tr class="row_empty row_devider"><td></td></tr>
				
				<tr><td><b>itch.io</b></td></tr>
				<tr><td><a href="https://itch.io/press-kit">source</a> last referenced 24th OCT, 2020</td></tr>
				
				<tr class="row_empty"><td></td></tr>
				<tr class="row_empty row_devider"><td></td></tr>
				
				<tr><td><b>GitHub</b></td></tr>
				<tr><td><a href="https://github.com/logos">source</a> last referenced 24th OCT, 2020</td></tr>
				
				<tr class="row_empty"><td></td></tr>
				<tr class="row_empty row_devider"><td></td></tr>
				
				<tr><td><b>Twitter</b></td></tr>
				<tr><td><a href="https://about.twitter.com/de/company/brand-resources.html">source</a> last referenced 24th OCT, 2020</td></tr>
				
				<tr class="row_empty"><td></td></tr>
				<tr class="row_empty row_devider"><td></td></tr>
			</table>
			
			<br>
			
			<h2>Privacy Policy</h2>
			
			<p>My Privacy Policy was created with the help of the <a href="https://www.privacypolicygenerator.info">Privacy Policy Generator</a> and the <a href="https://www.privacypolicyonline.com/privacy-policy-generator/">Online Privacy Policy Generator</a>, last referenced 24th OCT, 2020</p>
			
			<br>
			
			<h2>Source Code</h2>
			
			<p>I used following 3rd party code and techologies:</p>
			
			<table class="licenses_table">
				<tr class="row_empty row_devider"><td></td></tr>
				
				<tr><td><b>jQuery v3.5.1</b></td></tr>
				<tr><td>by JS Foundation and other contributors</td></tr>
				<tr><td><a href="https://jquery.com/">source</a> last referenced 13th DEC, 2020</td></tr>
				
				<tr class="row_empty"><td></td></tr>
				<tr class="row_empty row_devider"><td></td></tr>
				
				<tr><td><b>reCAPTCHA v2</b></td></tr>
				<tr><td>by Google</td></tr>
				<tr><td><a href="https://www.google.com/recaptcha/about/">source</a> last referenced 13th DEC, 2020</td></tr>
				
				<tr class="row_empty"><td></td></tr>
				<tr class="row_empty row_devider"><td></td></tr>
				
				<tr><td><b>validate.js 0.13.1</b></td></tr>
				<tr><td>by Nicklas Ansman, Wrapp</td></tr>
				<tr><td><a href="https://cdnjs.cloudflare.com/ajax/libs/validate.js/0.13.1/validate.min.js">source</a> last referenced 13th DEC, 2020</td></tr>
				
				<tr class="row_empty"><td></td></tr>
				<tr class="row_empty row_devider"><td></td></tr>
				
				<tr><td><b>Mobile-Detect-2.8.34</b></td></tr>
				<tr><td>by Serban Ghita, Nick Ilyin and contributors</td></tr>
				<tr><td><a href="http://mobiledetect.net/">source</a> last referenced 04th JAN, 2021</td></tr>
				
				<tr class="row_empty"><td></td></tr>
				<tr class="row_empty row_devider"><td></td></tr>
			</table>
			
			<p>Everything else has been created by me. The source code is public and can be found <a href="https://github.com/Rismosch/risWebsite">here</a>.</p>
			
			<br>
			
			<p>All other non mentioned resources, such as images, music, code, and any other files that are viewable or downloadable found on rismosch.com, were created by me or used with permission.</p>
		</div>
		
		<div class="foot" id="foot">
			
			<div class="socials" id="socials">
				<a title="YouTube" href="https://www.youtube.com/channel/UCrWSfmTaXTN_LzEsVRKNJTw">
					<img class="social_icon" src="assets/icon_social/youtube.png">
				</a>
				<a title="Bandcamp" href="https://rismosch.bandcamp.com">
					<img class="social_icon" src="assets/icon_social/bandcamp.png">
				</a>
				<a title="itch.io" href="https://rismosch.itch.io/">
					<img class="social_icon" src="assets/icon_social/itch_io.png">
				</a>
				<a title="GitHub" href="https://github.com/Rismosch">
					<img class="social_icon" src="assets/icon_social/github.png">
				</a>
				<a title="Twitter" href="https://twitter.com/Rismosch">
					<img class="social_icon" src="assets/icon_social/twitter.png">
				</a>
			</div>
			
			<div class="foot_links">
				<p><a href="https://www.rismosch.com/privacy">Privacy Policy</a> &nbsp; | &nbsp; <a href="https://www.rismosch.com/licenses">Licenses</a> &nbsp; | &nbsp; <a href="https://www.rismosch.com/contact">Contact</a></p>
			</div>
			
			<div class="foot_links">
				<p>Copyright &#169; 2020 Simon Sutoris</p>
			</div>
			
		</div>
		
		<button onclick="scrollToTop()" id="scroll_to_top" class="scroll_to_top">Top</button>
	</div>
</body>
</html>
