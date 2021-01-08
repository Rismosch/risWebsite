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
				<li class="selector_tab active_tab" id="selector_tab active_tab">
					<a href="https://www.rismosch.com/about">
						<div><b>About</b></div>
					</a>
				</li>
			</ul>
		</div>
		
		<div class="content" id="content">
			<h1>About</h1>
			<table>
				<tr>
					<td><img class="profile_picture" id="profile_picture" src="assets/Profile Picture.jpg"></td>
					<td>Hello Internet, my name is Rismosch, or B. Eng. Simon Sutoris. I describe myself as a programmer and a music producer.</td>
				</tr>
			</table>
			
			<br>
			
			<table>
				<tr>
					<td>Currently, I am programming professionally in C#. But in school and homeprojects I came in contact with many different programming languages.</td>
					<td><img class="about_picture pixel_image" id="about_picture" src="assets/icon_8bit/prog.png"></td>
				</tr>
			</table>
			
			<br>
			
			<table>
				<tr>
					<td>My music is very synth-heavy, with fast and crazy drum breaks. I describe my music as an amalgamation of early Drum and Bass/Jungle and Punk Rock. I use FL Studio and a small collection of external synthesizers.</td>
					<td><img class="about_picture pixel_image" id="about_picture" src="assets/icon_8bit/synth.png"></td>
				</tr>
			</table>
			
			<br>
			
			<table>
				<tr>
					<td>I have good experience with Microsoft Office products and use them on an almost daily basis, most notably Microsoft Excel. I dabbed a bit in Video Editing.</td>
					<td><img class="about_picture pixel_image" id="about_picture" src="assets/icon_8bit/video.png"></td>
				</tr>
			</table>
			
			<br>
			
			<p></p>
			<br>
			<p>German is my mother language, and I can understand and write English fairly well.</p>
			
			<br>
			<br>
			
			<h2 style="text-align: center;">Résumé</h2>
			
			<table class="resume_table">
				<tr class="row_empty row_devider"><td colspan="2"></td></tr>
				<tr><td colspan="2"><b>2019 - Today</b></td></tr>
				<tr><td colspan="2">employed as a developer at CMC-Kiesel GmbH</td></tr>
				<tr>
					<td>Programming Languages:</td>
					<td>
						<p>
							C#, <br>
							Unity, <br>
							XAML (WPF), <br>
							PowerShell <br>
						</p>
					</td>
				</tr>
				<tr class="row_empty"><td colspan="2"></td></tr>
				<tr class="row_empty row_devider"><td colspan="2"></td></tr>
				<tr><td colspan="2"><b>2016 - 2020</b></td></tr>
				<tr><td colspan="2">studied Technical Computer Science at Hochschule Albstadt-Sigmaringen</td></tr>
				<tr>
					<td>Programming Languages:</td>
					<td>
						<p>
							C/C++, <br>
							Java, <br>
							Python, <br>
							HTML, <br>
							CSS, <br>
							JavaScript, <br>
							Shell Script, <br>
							PowerShell, <br>
							SQL <br>
						</p>
					</td>
				</tr>
				<tr class="row_empty"><td colspan="2"></td></tr>
				<tr class="row_empty row_devider"><td colspan="2"></td></tr>
				<tr><td colspan="2"><b>Personal Projects</b></td></tr>
				<tr>
					<td>Programming Languages:</td>
					<td>
						<p>
							C/C++, <br>
							OpenGL, <br>
							Minecraft Redstone, <br>
							HTML, <br>
							CSS, <br>
							JavaScript, <br>
							PHP, <br>
							SQL <br>
						</p>
					</td>
				</tr>
				<tr class="row_empty"><td colspan="2"></td></tr>
				<tr class="row_empty row_devider"><td colspan="2"></td></tr>
			</table>
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
