<?php

include 'php/head.php';
include 'php/articles_database.php';

if(isset($_GET["id"]))
	$article_id = intval($_GET["id"]);
else
	$article_id = 0;

$dbConn = mysqli_connect($dbHost, $dbSelectUsername, $dbSelectPassword, $dbName);

$title = ":(";

if($dbConn){
	$typeId = GetArticleType($dbConn, $article_id);
	
	if($typeId == 0)
		$active_tab = 1;
	else if($typeId == 1)
		$active_tab = 2;

	$title = GetArticleTitle($dbConn, $article_id);
}

echo "<title>{$title}</title>";

?>
</head>
<body>
	<div class="background">
		<?php include 'php/banner.php'; ?>
		
		<?php include 'php/selector.php'; ?>
		
		<div class="content" id="content">
			<?php
				echo "<h1>{$title}</h1>";
				
				$content = "articles/{$article_id}/content.php";
				if(file_exists($content))
					include $content;
				else
				{
					echo "<p>Could not find article with id={$article_id}</p>";
					$article_id = 0;
				}
				
				/*
				echo "
				<div id=\"disqus_thread\"></div>
				<script>
					var disqus_config = function () {
						this.page.identifier 	= \"{$article_id}\";
						this.page.url			= \"https://www.rismosch.com/article?id={$article_id}\";
						this.page.title			= \"{$title}\";
					};
					
					(function() {
						var d = document, s = d.createElement('script');
						s.src = 'https://rismosch.disqus.com/embed.js';
						s.setAttribute('data-timestamp', +new Date());
						(d.head || d.body).appendChild(s);
					})();
				</script>
				<noscript>Please enable JavaScript to view the <a href=\"https://disqus.com/?ref_noscript\">comments powered by Disqus.</a></noscript>\n";
				*/
			?>
			
			<div class="disqus"></div>
			
		</div>
		
		<?php include 'php/foot.php'; ?>
	</div>
	
	<script>

		
		disqusLoader( '.disqus',
		{
			scriptUrl:		'//rismosch.disqus.com/embed.js',
			disqusConfig:	function()
			{
				this.page.identifier 	= <?php echo "'{$article_id}'"; ?>;
				this.page.url			= <?php echo "'https://www.rismosch.com/article?id={$article_id}'"; ?>;
				this.page.title			= <?php echo "'{$title}'"; ?>;
			}
		});

		
	</script>
</body>
</html>
