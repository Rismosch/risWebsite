<?php

include 'php/head.php';

$article_type_id = 0;
include 'php/articles_database.php';

?>
</head>
<body>
	<div class="background">
		<?php include 'php/banner.php'; ?>
		
		<?php $active_tab = 1; include 'php/selector.php'; ?>
		
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
		
		<?php include 'php/foot.php'; ?>
		
	</div>
</body>
</html>
