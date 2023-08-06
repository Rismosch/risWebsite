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
				<?php
					PrintArticleTableTop();

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
									Articles.id = 'the-world-between-my-mind-and-reality' OR
									Articles.id = 'quaternion-playground'
								)
							ORDER BY
								Articles.timestamp DESC
						";
						
						$result = mysqli_query($dbConn,$sqlSelectedArticles);
						$numRows = mysqli_num_rows($result);
						if($numRows > 0){
							
							while($row = mysqli_fetch_assoc($result))
							{
								PrintArticleTableEntry($row, true);
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

					PrintArticleTableBottom();
				?>
			</noscript>
            
			<div  style="display:none;" id="javascript_content">

                <?php
                    PrintArticleTableTop();
    
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
    								Articles.id = 'quaternion-playground'
    							)
    						ORDER BY
    							Articles.timestamp DESC
    					";
    					
    					$result = mysqli_query($dbConn,$sqlSelectedArticles);
    					$numRows = mysqli_num_rows($result);
    					if($numRows > 0){
    						
    						while($row = mysqli_fetch_assoc($result))
    						{
    							PrintArticleTableEntry($row, false);
    						}
    					
    					}
    					else
    					{
    						echo "<h3>:(</h3><p>Error while loading selected projects.</p>";
    					}
    				}
    				else{
    					echo "<h3>:(</h3><p>Error while loading selected projects.</p>";
    				}
                    PrintArticleTableBottom();
                ?>
            
				<iframe title="bandcamp widget" style="border: 0; width: 100%; height: 307px;" src="https://bandcamp.com/EmbeddedPlayer/album=2712586750/size=large/bgcol=ffffff/linkcol=0687f5/artwork=small/transparent=true/" seamless><a href="https://rismosch.bandcamp.com/album/the-world-between-my-mind-and-reality">The World Between My Mind And Reality by Rismosch</a></iframe>
				
				<!--<iframe title="bandcamp widget" style="border: 0; width: 100%; height: 307px;" src="https://bandcamp.com/EmbeddedPlayer/album=894520742/size=large/bgcol=ffffff/linkcol=0687f5/artwork=small/transparent=true/" seamless><a href="https://rismosch.bandcamp.com/album/angery">Angery by Rismosch</a></iframe>-->

				<br>
				<br>
			</div>

			
			<h2>Selected Blog Posts</h2>
			<?php
				PrintArticleTableTop();

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
								Articles.id = 'building-a-job-system' OR
								Articles.id = 'rebinding-controls-via-a-rebind-matrix' OR
								Articles.id = 'why-people-love-bad-art'
							)
						ORDER BY
							Articles.timestamp DESC
					";
					
					$result = mysqli_query($dbConn,$sqlSelectedArticles);
					$numRows = mysqli_num_rows($result);
					if($numRows > 0){
						
						while($row = mysqli_fetch_assoc($result))
						{
							PrintArticleTableEntry($row, false);
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

				PrintArticleTableBottom();
			?>
			
			<br>
		</div>
		
		<?php echo_foot(false); ?>
	</div>

	<script>
		document.getElementById("javascript_content").style.display = "inline";
	</script>
</body>
</html>
