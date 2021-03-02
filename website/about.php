<?php

include 'php/util.php';

echo_head();

?>

	<title>About</title>
	<meta name="description" content="Resumee of Simon Sutoris">
	<meta name="keywords" content="about, resumee, cv, curriculum vitae">

	<meta name="robots" content="all">

	<meta property="og:title" content="About" />
	<meta property="og:type" content="article" />
	<meta property="og:url" content="https://www.rismosch.com/about" />
	<meta property="og:image" content="https://www.rismosch.com/assets/meta_image_x10.png" />

	<meta name="author" content="Simon Sutoris">
</head>
<body>
	<div class="background">
		<?php
			echo_banner();
			echo_selector(3);
		?>
		
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
					<td>My music is very synth-heavy, with fast and crazy drum breaks. I describe my music as an amalgamation of early Drum and Bass/Jungle and Punk Rock. Also, I love distortion.</td>
					<td><img class="about_picture pixel_image" id="about_picture" src="assets/icon_8bit/synth.png"></td>
				</tr>
			</table>
			
			<br>
			
			<table>
				<tr>
					<td>I have good experience with Microsoft Office products and use them on an almost daily basis, most notably Microsoft Excel. I also dabbed a bit in Video Editing.</td>
					<td><img class="about_picture pixel_image" id="about_picture" src="assets/icon_8bit/video.png"></td>
				</tr>
			</table>
			
			<br>
			
			<p></p>
			<br>
			<p>German is my mother language, and my English is fairly decent.</p>
			
			<br>
			<br>
			
			<h2 style="text-align: center;">Résumé</h2>
			
			<table class="resume_table">
				<tr class="row_empty row_devider"><td colspan="2"></td></tr>
				<tr><td colspan="2"><b>2019 - Today</b></td></tr>
				<tr><td colspan="2">employed as a developer at CMC-Kiesel GmbH</td></tr>
				<tr class="row_empty"><td colspan="2"></td></tr>
				<tr>
					<td>Programming Languages:</td>
					<td>
						<p>
							C#, <br>
							Unity, <br>
							XAML (WPF) <br>
						</p>
					</td>
				</tr>
				<tr class="row_empty"><td colspan="2"></td></tr>
				<tr class="row_empty row_devider"><td colspan="2"></td></tr>
				<tr><td colspan="2"><b>2016 - 2020</b></td></tr>
				<tr><td colspan="2">studied Technical Computer Science at Hochschule Albstadt-Sigmaringen</td></tr>
				<tr class="row_empty"><td colspan="2"></td></tr>
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
			</table>
		</div>
		
		<?php echo_foot(false); ?>
	</div>
</body>
</html>
