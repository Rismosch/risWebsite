<?php

$article_type_id = 0;

include 'secret/secret.php';
include 'php/articles_database.php';
include 'php/util.php';

echo_head();

?>

	<title>Blog</title>
	<meta name="description" content="Blog of Simon Sutoris">
	<meta name="keywords" content="blog">

	<meta name="robots" content="all">

	<meta property="og:title" content="Blog" />
	<meta property="og:type" content="website" />
	<meta property="og:url" content="https://www.rismosch.com/blog" />
	<meta property="og:image" content="https://www.rismosch.com/assets/meta_image_x10.png" />

	<meta name="author" content="Simon Sutoris">

</head>
<body>
	<div class="background">
		<?php
			echo_banner();
			echo_selector(1);
		?>
		
		<div class="content" id="content">
			<h1>Blog</h1>
			<?php
				$dbSelectConnection = mysqli_connect($dbHost, $dbSelectUsername, $dbSelectPassword, $dbName);
				
				if($dbSelectConnection){
					
					$pageName = "blog";
					printDropdown($dbSelectConnection, $pageName);
					printArticles($dbSelectConnection, $pageName);
					printSelector($dbSelectConnection, $pageName);
					
				}
				else{
					echo "<h1>:(</h1><p>Error while loading articles.</p>";
				}
			?>
		</div>
		
		<?php echo_foot(false); ?>
	</div>
</body>
</html>
