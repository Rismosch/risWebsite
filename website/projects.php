<?php

include 'php/head.php';

$article_type_id = 1;
include 'php/articles_database.php';

?>
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
		
		<button onclick="scrollToTop()" id="scroll_to_top" class="scroll_to_top">Top</button>
		
	</div>
</body>
</html>
