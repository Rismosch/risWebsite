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

$thumbnail_path = GetThumbnailPath($articleData);

echo_head();

$cssFile = "articles/{$article_id}/article.css";
if(file_exists($cssFile))
	echo "<link rel=\"stylesheet\" href=\"{$cssFile}\">";

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

$showPrevious = true;
function hide_previous_post()
{
	global $showPrevious;
	$showPrevious = false;
}

$showNext = true;
function hide_next_post()
{
	global $showNext;
	$showNext = false;
}

$showOther = true;
function hide_other_post()
{
	global $showOther;
	$showOther = false;
}

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
						$sqlNext = GetNextPreviousSql("> '{$articleData['timestamp']}'");
						$sqlPrevious = GetNextPreviousSql("< '{$articleData['timestamp']}'");
						echo "<p style=\"text-align:center; display:block;\">";
						
						// Next Button
						if($showNext)
						{
							$result = mysqli_query($dbConn,$sqlNext);
							$numRows = mysqli_num_rows($result);
							if($numRows > 0)
							{
								$row = mysqli_fetch_assoc($result);
								$articleTitle = "Next Post: {$row['title']}";
								
								echo "<p><a class=\"button\" href=\"https://www.rismosch.com/article?id={$row['id']}\">{$articleTitle}</a></p>";
							}
						}
						
						// Previous Button
						if($showPrevious)
						{
							$result = mysqli_query($dbConn,$sqlPrevious);
							$numRows = mysqli_num_rows($result);
							if($numRows > 0)
							{
								$row = mysqli_fetch_assoc($result);
								$articleTitle = "Previous Post: {$row['title']}";
								
								echo "<p><a class=\"button\" href=\"https://www.rismosch.com/article?id={$row['id']}\">{$articleTitle}</a></p>";
							}
						}
						
						// Other Button
						if($showOther)
						{
							echo "
								<p>
									<a
										style=\"display:inline-block; margin-top: 5px;\"
										class=\"button\" href=\"https://www.rismosch.com/blog?category={$articleData['category_id']}\"
									>
										More \"{$articleData['category']}\"-related Blog Posts
									</a>
								</p>
							";
						}
						
						echo "</p>";
					}
				}
				else
				{
					echo "<p>Could not find the article you were looking for</p>";
					$article_id = 0;
				}
			?>
			
			<div style="margin-top:5px;">
				<div style="padding: 5px;border:5px solid var(--pico-8-cyan);background-color:var(--pico-8-white);" id="data-collection-warning" class="invisible">
					<p style="text-align: justify; margin-left: 20px; margin-right: 20px;">
						I don't use cookies to track your data.
						My comments however, which are powered by <a href="https://disqus.com/" target="_blank" rel="noopener noreferrer">Disqus</a>, definitely do.
						So you should probably read their <a href="https://help.disqus.com/en/articles/1717103-disqus-privacy-policy" target="_blank" rel="noopener noreferrer">Privacy Policy</a>.<br>
						<br>
						Unless you click the button below, no data will be tracked.
					</p>
					<p style="text-align:center;">
						<button onclick="ShowComments()">Accept and view comments</button>
					</p>
				</div>
				
				<div id="comments_block" class="invisible">
					<div class="disqus-loading">Loading comments&hellip;</div>
					<div class="disqus"></div>
				</div>
			</div>
		</div>
		
		<?php echo_foot(false); ?>
	</div>
	
	<script>
		
		const disqusDataCollectionCookieName = "DisqusDataCollectionWarning";
		
		ShowDisqusDataCollectionWarning();
		function ShowDisqusDataCollectionWarning()
		{
			var cookie = getCookie(disqusDataCollectionCookieName);
			if(cookie)
			{
				ShowComments();
			}
			else
			{
				HideComments();
			}
		}
		
		function ShowComments()
		{
			setCookie(disqusDataCollectionCookieName, true, 604800000) // 1 week = 7*24*60*60*1000 milliseconds
			
			document.getElementById("data-collection-warning").classList.add("invisible");
			document.getElementById("comments_block").classList.remove("invisible");
			
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
		}
		
		function HideComments()
		{
			document.getElementById("data-collection-warning").classList.remove("invisible");
			document.getElementById("comments_block").classList.add("invisible");
		}
		
	</script>
</body>
</html>
