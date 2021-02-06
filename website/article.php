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

echo "

	<title>{$title}</title>

	<meta name=\"robots\" content=\"all\">

	<meta property=\"og:title\" content=\"{$title}\" />
	<meta property=\"og:type\" content=\"article\" />
	<meta property=\"og:url\" content=\"https://www.rismosch.com/article?id={$article_id}\" />
	<meta property=\"og:image\" content=\"https://www.rismosch.com/assets/meta_image_x20.png\" />

	<meta name=\"author\" content=\"Simon Sutoris\">
";

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
			?>
			
			<div class="disqus-loading">Loading comments&hellip;</div>
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
				this.callbacks.onReady	= [function()
				{
					var el = document.querySelector( '.disqus-loading' );
					if( el.classList )
						el.classList.add( 'is-hidden' ); // IE 10+
					else
						el.className += ' ' + 'is-hidden'; // IE 8-9
				}];
			}
		});

		
	</script>
</body>
</html>
