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
			
			<h2>Selected Projects</h2>

			<noscript>
				<!-- <p><a href="https://rismosch.bandcamp.com/album/angery" target="_blank" rel="noopener noreferrer">https://rismosch.bandcamp.com/album/angery</a></p> -->
				<table style="width: 100%;">
					<tr class="row_empty row_devider"><td></td></tr>
					<?php
						// echo "<tr><td>query here...</td></tr>";
						
						$dbConn = mysqli_connect($dbHost, $dbSelectUsername, $dbSelectPassword, $dbName);
						
						if($dbConn)
						{
							$sqlSelectedArticles ="
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
									Articles.type_id = Article_Types.id AND (
										Articles.id = 'angery'
									)
								ORDER BY
									Articles.timestamp DESC
							";
							
							$result = mysqli_query($dbConn,$sqlSelectedArticles);
							$numRows = mysqli_num_rows($result);
							if($numRows > 0){
								
								while($row = mysqli_fetch_assoc($result))
								{
									$timestamp = strtotime($row['timestamp']);
									$newTimestampFormat = date('M jS, Y',$timestamp);
									
									if(!is_null($row['link']))
										$link = $row['link'];
									else
										$link = "https://www.rismosch.com/article?id={$row['id']}";
									
									$thumbnail = GetThumbnailPath($row);
									
									echo
									"<tr><td><a title=\"{$row['title']}\" href=\"{$link}\" class=\"articles_entry_link\">
										<div class=\"articles_mobile\">
											<table class=\"articles_entry\">
												<tr>
													<td>
														<div class=\"articles_thumbnail_wrapper_outside\">
															<div class=\"articles_thumbnail_wrapper_inside\">
																<img
																	src='{$thumbnail}'
																	class='articles_thumbnail'
																	style=''
																	alt=''
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
																src='{$thumbnail}'
																class='articles_thumbnail'
																style=''
																alt=''
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
									</a></td></tr>
									<tr class=\"row_empty\"><td></td></tr>
									<tr class=\"row_empty row_devider\"><td></td></tr>
									";
								}
							
							}
							else
							{
								echo "<h3>:(</h3><p>Error while loading selected articles.</p>";
							}
						}
						else{
							echo "<h3>:(</h3><p>Error while loading selected articles.</p>";
						}
						
					?>
				</table>
			</noscript>

			<div  style="display:none;" id="javascript_content">
				<iframe style="border: 0; width: 100%; height: 307px;" src="https://bandcamp.com/EmbeddedPlayer/album=894520742/size=large/bgcol=ffffff/linkcol=0687f5/artwork=small/transparent=true/" seamless><a href="https://rismosch.bandcamp.com/album/angery">Angery by Rismosch</a></iframe>

				<br>
				<br>
			</div>

			
			<h2>Selected Blog Posts</h2>
			<table style="width: 100%;">
				<tr class="row_empty row_devider"><td></td></tr>
				<?php
					
					$dbConn = mysqli_connect($dbHost, $dbSelectUsername, $dbSelectPassword, $dbName);
					
					if($dbConn)
					{
						$sqlSelectedArticles ="
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
								Articles.type_id = Article_Types.id AND (
									Articles.id = 'post-crisis' OR
									Articles.id = 'why-people-love-bad-art' OR
									Articles.id = 'i-made-a-website-only-with-notepad-plus-plus'
								)
							ORDER BY
								Articles.timestamp DESC
						";
						
						$result = mysqli_query($dbConn,$sqlSelectedArticles);
						$numRows = mysqli_num_rows($result);
						if($numRows > 0){
							
							while($row = mysqli_fetch_assoc($result))
							{
								$timestamp = strtotime($row['timestamp']);
								$newTimestampFormat = date('M jS, Y',$timestamp);
								
								if(!is_null($row['link']))
									$link = $row['link'];
								else
									$link = "https://www.rismosch.com/article?id={$row['id']}";
								
								$thumbnail = GetThumbnailPath($row);
								
								echo
								"<tr><td><a title=\"{$row['title']}\" href=\"{$link}\" class=\"articles_entry_link\">
									<div class=\"articles_mobile\">
										<table class=\"articles_entry\">
											<tr>
												<td>
													<div class=\"articles_thumbnail_wrapper_outside\">
														<div class=\"articles_thumbnail_wrapper_inside\">
															"; late_image($thumbnail, "articles_thumbnail", ""); echo "
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
														"; late_image($thumbnail, "articles_thumbnail", ""); echo "
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
								</a></td></tr>
								<tr class=\"row_empty\"><td></td></tr>
								<tr class=\"row_empty row_devider\"><td></td></tr>
								";
							}
						
						}
						else
						{
							echo "<h3>:(</h3><p>Error while loading selected articles.</p>";
						}
					}
					else{
						echo "<h3>:(</h3><p>Error while loading selected articles.</p>";
					}
				?>
			</table>
			
			<br>
			<br>
			
			<!--<h2>Latest Blog Post</h2>
			<table style="width: 100%;">
				<tr class="row_empty row_devider"><td></td></tr>
				<?php
					echo "<tr><td>query here...</td></tr>";
					/*
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
								Articles.type_id = Article_Types.id
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
							
							echo
							"<tr><td><a title=\"{$row['title']}\" href=\"{$link}\" class=\"articles_entry_link\">
								<div class=\"articles_mobile\">
									<table class=\"articles_entry\">
										<tr>
											<td>
												<div class=\"articles_thumbnail_wrapper_outside\">
													<div class=\"articles_thumbnail_wrapper_inside\">
														"; late_image($thumbnail, "articles_thumbnail", ""); echo "
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
													"; late_image($thumbnail, "articles_thumbnail", ""); echo "
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
							</a></td></tr>
							<tr class=\"row_empty\"><td></td></tr>
							<tr class=\"row_empty row_devider\"><td>
							</td></tr>
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
					*/
				?>
			</table>-->
		</div>
		
		<?php echo_foot(false); ?>
	</div>

	<script>
		document.getElementById("javascript_content").style.display = "inline";
	</script>
</body>
</html>
