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

			<table>
				<tr>
					<td><?php late_image("assets/profile_pictures/2020.webp", "profile_picture", "");?></td>
					<td>Hello Internet &#128075; My name is Simon. Online you'll find me as Rismosch. I'm a graphics programmer, but currently I am studying &#129418;</td>
				</tr>
			</table>
			
			<br>
			
			<table>
				<tr>
					<td>This website serves as a collection and archive of my home projects. And the blog I inconsistently update is mostly incoherent rambling. It's more of a public diary than anything else.</td>
				</tr>
			</table>
			
			<br>
			
			<h2>Selected Projects</h2>

			<div>
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
									Articles.id = 'ris_engine' OR
									Articles.id = 'color-picker'
								)
						";
						
						$result = mysqli_query($dbConn,$sqlSelectedArticles);
						$numRows = mysqli_num_rows($result);
						if($numRows > 0){
							$row0 = mysqli_fetch_assoc($result);
							$row1 = mysqli_fetch_assoc($result);
							PrintArticleTableEntry($row1, false, "category");
							PrintArticleTableEntry($row0, false, "category");
							
							//while($row = mysqli_fetch_assoc($result))
							//{
							//	PrintArticleTableEntry($row, false);
							//}
						
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
							Blog_Types.name AS blog_type,
							Articles.title AS title,
							Articles.timestamp AS timestamp,
							Articles.link AS link,
							Articles.thumbnail_path AS thumbnail_path
						FROM
							Articles,
							Article_Categories,
							Article_Types,
							Blog_Types
						WHERE
							Articles.category_id = Article_Categories.id AND
							Articles.blog_type_id = Blog_Types.id AND
							Articles.type_id = Article_Types.id AND (
						-- don't forget to sort articles in the php code below
								Articles.id = 'furries-are-cool' OR
								Articles.id = 'the-good-code-manifesto' OR
								Articles.id = 'why-i-make-my-own-game-engine'
							)
						ORDER BY
							Articles.timestamp DESC
					";
					
					$result = mysqli_query($dbConn,$sqlSelectedArticles);
					$numRows = mysqli_num_rows($result);
					if($numRows > 0){
						$row0 = mysqli_fetch_assoc($result);
						$row1 = mysqli_fetch_assoc($result);
						$row2 = mysqli_fetch_assoc($result);
						PrintArticleTableEntry($row0, false, "blog_type");
						PrintArticleTableEntry($row1, false, "blog_type");
						PrintArticleTableEntry($row2, false, "blog_type");
						
						//while($row = mysqli_fetch_assoc($result))
						//{
						//	PrintArticleTableEntry($row, false);
						//}
					
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
</body>
</html>
