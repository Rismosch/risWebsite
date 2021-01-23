<?php

include 'php/head.php';
include 'php/articles_database.php';

if(isset($_GET["id"]))
	$article_id = intval($_GET["id"]);
else
	$article_id = 0;

$dbSelectConnection = mysqli_connect($dbHost, $dbSelectUsername, $dbSelectPassword, $dbName);

if($dbSelectConnection){
	$typeId = GetArticleType($dbSelectConnection, $article_id);
	
	if($typeId == 0)
		$active_tab = 1;
	else if($typeId == 1)
		$active_tab = 2;
}

?>
</head>
<body>
	<div class="background">
		<?php include 'php/banner.php'; ?>
		
		<?php include 'php/selector.php'; ?>
		
		<div class="content" id="content">
			<?php
				$content = "articles/{$article_id}/content.php";
				if(file_exists($content))
					include $content;
				else
					echo "<h1>:(</h1><p>Could not find article with id={$article_id}</p>";
				
				echo "\n";
			?>
		</div>
		
		<?php include 'php/foot.php'; ?>
	</div>
</body>
</html>
