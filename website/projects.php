<?php

include 'php/head.php';

$article_type_id = 1;
include 'php/articles.php';

?>
</head>
<body>
	<div class="background">
		<?php include 'php/banner.php'; ?>
		
		<?php $active_tab = 2; include 'php/selector.php'; ?>
		
		<div class="content" id="content">
			<h1>Projects</h1>
			<?php
				if($dbConn){
					
					$pageName = "projects";
					printDropdown($pageName);
					printArticles($pageName);
					printSelector($pageName);
					
				}
				else{
					echo "<p>ERROR: Could not connect to database.</p>";
				}
			?>
		</div>
		
		<?php include 'php/foot.php'; ?>
		
		<button onclick="scrollToTop()" id="scroll_to_top" class="scroll_to_top">Top</button>
	</div>
</body>
</html>
