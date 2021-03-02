<?php

include 'secret/secret.php';
include 'php/articles_database.php';
include 'php/util.php';

if(isset($_GET["id"]))
	$article_id = intval($_GET["id"]);
else
	$article_id = 0;

$dbConn = mysqli_connect($dbHost, $dbSelectUsername, $dbSelectPassword, $dbName);

$title = ":(";

if($dbConn){
	$articleData = GetArticleData($dbConn, $article_id);
	if(!is_null($articleData))
	{
		if($articleData['type_id'] == 0)
			$active_tab = 1;
		else if($articleData['type_id'] == 1)
			$active_tab = 2;
		else
			$active_tab = -1;

		$title = $articleData['title'];
	}
	else
	{
		$title = ':(';
	}
}

echo "

	<title>{$title}</title>

	<meta name=\"robots\" content=\"all\">

	<meta property=\"og:title\" content=\"{$title}\" />
	<meta property=\"og:type\" content=\"article\" />
	<meta property=\"og:url\" content=\"https://www.rismosch.com/article?id={$article_id}\" />
	<meta property=\"og:image\" content=\"https://www.rismosch.com/assets/meta_image_x10.png\" />

	<meta name=\"author\" content=\"Simon Sutoris\">
";

function get_source($file)
{
	global $article_id;
	
	return "https://www.rismosch.com/articles/{$article_id}/{$file}";
}

function echo_source($file)
{
	global $article_id;
	
	echo "https://www.rismosch.com/articles/{$article_id}/{$file}";
}

echo_head();

?>
</head>
<body>
	<div class="background">
		<?php
			echo_banner();
			echo_selector($active_tab);
		?>
		
		<div class="content" id="content">
			<?php
				echo "<h1>{$title}</h1>";
				
				$content = "articles/{$article_id}/content.php";
				if(file_exists($content))
				{
					if(!is_null($articleData))
					{
						$timestamp = strtotime($articleData['timestamp']);
						$newTimestampFormat = date('M jS, Y',$timestamp);
						
						echo "<p>{$articleData['category']} &#183; {$newTimestampFormat}</p>";
					}
					include $content;
				}
				else
				{
					echo "<p>Could not find article with id={$article_id}</p>";
					$article_id = 0;
				}
			?>
			
			<div class="disqus-loading">Loading comments&hellip;</div>
			<div class="disqus"></div>
			
		</div>
		
		<?php echo_foot(false); ?>
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
