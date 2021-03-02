<?php

include 'secret/secret.php';
include 'php/articles_database.php';
include 'php/util.php';

echo_head();

?>

	<title>Rismosch</title>
	<meta name="description" content="Blog and Project-Collection of Simon Sutoris">
	<meta name="keywords" content="home, resumee, cv, curriculum vitae, blog, projects, programming, music, gamedev">

	<meta name="robots" content="all">

	<meta property="og:title" content="Rismosch" />
	<meta property="og:type" content="website" />
	<meta property="og:url" content="https://www.rismosch.com/" />
	<meta property="og:image" content="https://www.rismosch.com/assets/meta_image_x10.png" />

	<meta name="author" content="Simon Sutoris">

</head>
<body>
	<div class="background">
		<?php
			echo_banner();
			echo_selector(0);
		?>
		
		<div class="content" id="content">
			<h1>Home</h1>
			
			<iframe style="border: 0; width: 100%; height: 406px;" src="https://bandcamp.com/EmbeddedPlayer/album=3691389739/size=large/bgcol=ffffff/linkcol=0687f5/artwork=small/transparent=true/" seamless><a href="https://rismosch.bandcamp.com/album/the-land-behind-the-waves">The Land Behind The Waves by Rismosch</a></iframe>
			
			<br>
			<br>
			<h2>Latest Post</h2>
			<table style="width: 100%;">
				<tr class="row_empty row_devider"><td></td></tr>
				<tr><td>
				<?php
					$dbSelectConnection = mysqli_connect($dbHost, $dbSelectUsername, $dbSelectPassword, $dbName);
					
					if($dbSelectConnection){
						printLatestArticle($dbSelectConnection);
					}
					else{
						echo "<h3>:(</h3><p>Error while loading latest article.</p>";
					}
				?>
				</td></tr>
				<tr class="row_empty"><td></td></tr>
				<tr class="row_empty row_devider"><td></td></tr>
			</table>
		</div>
		
		<?php echo_foot(false); ?>
	</div>
</body>
</html>
