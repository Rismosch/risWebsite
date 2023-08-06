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
					<td><?php late_image("assets/profile_pictures/2020.webp", "profile_picture", "");?></td>
					<td>Hello Internet, my name is Rismosch, or B. Eng. Simon Sutoris. I describe myself as a programmer and a music producer.</td>
				</tr>
			</table>
			
			<br>
			
			<table>
				<tr>
					<td>I am programming professionally in C#. But I do have good experience with a wide variety of programming languages; from high- to low level and everything in between. I am rather fond of Rust nowadays.</td>
					<td><?php late_image("assets/icon_8bit/prog.png", "about_picture pixel_image", "");?></td>
				</tr>
			</table>
			
			<br>
			
			<table>
				<tr>
					<td>My music is very synth-heavy, with fast and crazy drum breaks. I make Electronica, and I like to describe my music as an amalgamation of Drum and Bass and Punk. Also, I love distorted sounds.</td>
					<td><?php late_image("assets/icon_8bit/synth.png", "about_picture pixel_image", "");?></td>
				</tr>
			</table>
			
			<br>
			
			<!--<table>
				<tr>
					<td>I have good experience with Microsoft Office products and use them on an almost daily basis, most notably Microsoft Excel. I also dabbed a bit in Video Editing.</td>
					<td><?php late_image("assets/icon_8bit/video.png", "about_picture pixel_image", "");?></td>
				</tr>
			</table>-->
			
			<br>
			
			<p>German is my mother language, and my English is fairly decent.</p>
			
			<br>
			<br>
			
			<h2 style="text-align: center;">Résumé</h2>
			
			<table class="resume_table">
				<tr class="row_empty row_devider"><td></td></tr>
				<tr><td><b>2019 - Today</b></td></tr>
				<tr><td>employed as a developer at CMC-Kiesel GmbH</td></tr>
				<tr class="row_empty"><td></td></tr>
				<tr class="row_empty row_devider"><td></td></tr>
				<tr><td><b>2016 - 2020</b></td></tr>
				<tr><td>studied Technical Computer Science at Hochschule Albstadt-Sigmaringen</td></tr>
				<tr class="row_empty"><td></td></tr>
				<tr class="row_empty row_devider"><td></td></tr>
			</table>
		</div>
		
		<?php echo_foot(false); ?>
	</div>
</body>
</html>
