<?php

include 'secret/secret.php';
include 'php/articles_database.php';
include 'php/util.php';

if(isset($_GET["id"]))
	$article_id = intval($_GET["id"]);
else
	$article_id = 0;

/*if(isset($_GET["page"]))
	$fileToLoad = 'page_' . intval($_GET["page"]);
else*/
	$fileToLoad = 'page_0';

$dbConn = mysqli_connect($dbHost, $dbSelectUsername, $dbSelectPassword, $dbName);

$title = ":(";
$active_tab = -1;
if($dbConn){
	$articleData = GetArticleData($dbConn, $article_id);
	if(!is_null($articleData))
	{
		if($articleData['type_id'] == 0)
			$active_tab = 1;
		else if($articleData['type_id'] == 1)
			$active_tab = 2;

		$title = $articleData['title'];
	}
	else
	{
		$title = ':(';
	}
}

if(isset($articleData))
{
	$thumbnail_path = $articleData['thumbnail_path'];
	if(is_null($thumbnail_path))
	{
		$thumbnail_path = "articles/{$articleData['id']}/thumbnail.jpg";
	}
	
	if(!file_exists($thumbnail_path))
	{
		$thumbnail_path = "assets/thumbnails/default.jpg";
	}
}
else
	$thumbnail_path = "assets/thumbnails/default.jpg";

echo_head();

echo "

	<title>{$title}</title>

	<meta name=\"robots\" content=\"all\">

	<meta property=\"og:title\" content=\"{$title}\" />
	<meta property=\"og:type\" content=\"article\" />
	<meta property=\"og:url\" content=\"https://www.rismosch.com/article?id={$article_id}\" />
	<meta property=\"og:image\" content=\"https://www.rismosch.com/{$thumbnail_path}\" />

	<meta name=\"author\" content=\"Simon Sutoris\">
";

function get_source($file)
{
	global $article_id;
	
	return "https://www.rismosch.com/articles/{$article_id}/{$file}";
}

/*function get_page_url($number)
{
	global $article_id;
	
	return "https://www.rismosch.com/article?id={$article_id}&page={$number}";
}*/

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
				
				$content = "articles/{$article_id}/{$fileToLoad}.php";
				if(file_exists($content))
				{
					if(!is_null($articleData))
					{
						$timestamp = strtotime($articleData['timestamp']);
						$newTimestampFormat = date('M jS, Y',$timestamp);
						
						echo "<p>{$articleData['category']} &#183; {$newTimestampFormat}</p>";
					}
					include $content;
					
					if($dbConn && isset($articleData))
					{
						printArticleFoot($dbConn,$articleData);
					}
				}
				else
				{
					echo "<p>Could not find the article you were looking for</p>";
					$article_id = 0;
				}
			?>
			
			<div style="margin-top:5px;">
				<table style="border:5px solid var(--pico-8-red); background-color:var(--pico-8-white);" id="data-collection-warning" class="invisible">
					<tr>
						<td><b>Comments are powered by a third party service, which may collect your data. Which data is collected is beyond my control. I strongly recommend that you read their privacy policy.</b></td>
						<td><button onclick="AcceptDisqusDataCollection()">Accept</button></td>
					</tr>
				</table>
				<div class="disqus-loading">Loading comments&hellip;</div>
				<div class="disqus"></div>
			</div>
		</div>
		
		<?php echo_foot(false); ?>
	</div>
	
	<script>
		
		const disqusDataCollectionCookieName = "DisqusDataCollectionWarning";
		function AcceptDisqusDataCollection()
		{
			SetAcceptCookie();
			document.getElementById("data-collection-warning").classList.add("invisible");
		}
		
		ShowDisqusDataCollectionWarning();
		function ShowDisqusDataCollectionWarning()
		{
			var cookie = getCookie(disqusDataCollectionCookieName);
			if(cookie)
				SetAcceptCookie();
			else
				document.getElementById("data-collection-warning").classList.remove("invisible");
		}
		
		function SetAcceptCookie()
		{
			setCookie(disqusDataCollectionCookieName, true, 31536000000) // 365*24*60*60*1000 milliseconds
		}
		
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
