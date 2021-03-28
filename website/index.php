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
					$dbConn = mysqli_connect($dbHost, $dbSelectUsername, $dbSelectPassword, $dbName);
					
					if($dbConn)
					{
						$sqlLatestArticle ="
							SELECT
								Articles.id AS id,
								Article_Types.name AS type,
								Article_Categories.name AS category,
								Articles.title AS title,
								Articles.timestamp AS timestamp,
								Articles.link AS link,
								Articles.thumbnail_path AS thumbnail_path
							FROM
								Articles,
								Article_Categories,
								Article_Types
							WHERE
								Articles.category_id = Article_Categories.id AND
								Articles.type_id = Article_Types.id AND
								Articles.id = 11
							ORDER BY
								Articles.timestamp DESC
							LIMIT
								0,
								1
						";
						
						$result = mysqli_query($dbConn,$sqlLatestArticle);
						$numRows = mysqli_num_rows($result);
						if($numRows > 0){
							
							$row = mysqli_fetch_assoc($result);
							
							$timestamp = strtotime($row['timestamp']);
							$newTimestampFormat = date('M jS, Y',$timestamp);
							
							if(!is_null($row['link']))
								$link = $row['link'];
							else
								$link = "https://www.rismosch.com/article?id={$row['id']}";
							
							$thumbnail = GetThumbnailPath($row);
							
							echo "
							<a title=\"{$row['title']}\" href=\"{$link}\" class=\"articles_entry_link\">
								<div class=\"articles_mobile\">
									<table class=\"articles_entry\">
										<tr>
											<td>
												<div class=\"articles_thumbnail_wrapper_outside\">
													<div class=\"articles_thumbnail_wrapper_inside\">
														<img
															class=\"articles_thumbnail\"
															src=\"{$thumbnail}\"
															alt=\"\"
														>
													</div>
												</div>
											</td>
										</tr>
										<tr>
											<td>
												<div class=\"articles_thumbnail_information\">
													<h3>{$row['title']}</h3>
													<p>{$row['category']} &#183; {$newTimestampFormat}</p>
												</div>
											</td>
										</tr>
									</table>
								</div>
								<div class=\"articles_desktop\">
									<table class=\"articles_entry\">
										<tr>
											<td class=\"articles_thumbnail_row_desktop\">
												<div class=\"articles_thumbnail_wrapper\">
													<img
														class=\"articles_thumbnail\"
														src=\"{$thumbnail}\"
														alt=\"\"
													>
												</div>
											</td>
											<td>
												<div class=\"articles_thumbnail_information\">
													<h3>{$row['title']}</h3>
													<br>
													<p>{$row['category']} &#183; {$newTimestampFormat}</p>
												</div>
											</td>
										</tr>
									</table>
								</div>
							</a>
							";
						
						}
						else
						{
							echo "<h3>:(</h3><p>Error while loading latest article.</p>";
						}
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
