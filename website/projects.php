<?php

include 'php/head.php';

$article_type_id = 1;
include 'php/articles_database.php';

?>

	<title>Projects</title>
	<meta name="description" content="Projects of Simon Sutoris">
	<meta name="keywords" content="projects">

	<meta name="robots" content="all">

	<meta property="og:title" content="Projects" />
	<meta property="og:type" content="website" />
	<meta property="og:url" content="https://www.rismosch.com/projects" />
	<meta property="og:image" content="https://www.rismosch.com/assets/meta_image_x10.png" />

	<meta name="author" content="Simon Sutoris">

</head>
<body>
	<div class="background">
		<?php include 'php/banner.php'; ?>
		
		<?php $active_tab = 2; include 'php/selector.php'; ?>
		
		<div class="content" id="content">
			<h1>Projects</h1>
			<?php
				$dbSelectConnection = mysqli_connect($dbHost, $dbSelectUsername, $dbSelectPassword, $dbName);
				
				if($dbSelectConnection){
					
					$pageName = "projects";
					printDropdown($dbSelectConnection, $pageName);
					printArticles($dbSelectConnection, $pageName);
					printSelector($dbSelectConnection, $pageName);
					
				}
				else{
					echo "<h1>:(</h1><p>Error while loading articles.</p>";
				}
			?>
		</div>
		
		<?php include 'php/foot.php'; ?>
	</div>
</body>
</html>
