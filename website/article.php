<?php

include 'php/head.php';

if(isset($_GET["id"]))
	$article_id = intval($_GET["id"]);
else
	$article_id = 0;

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
					echo "<h1>:(</h1><p>Could not find article with id={$article_id} </p>";
				
				echo "\n";
			?>
		</div>
		
		<?php include 'php/foot.php'; ?>
		
		<button onclick="scrollToTop()" id="scroll_to_top" class="scroll_to_top">Top</button>
	</div>
</body>
</html>
