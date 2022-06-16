<?php

include 'secret/secret.php';
include 'php/articles_database.php';
include 'php/util.php';

if(isset($_GET["id"]))
{
	//$article_id = intval($_GET["id"]);
	
	$safe_characters = 'abcdefghijklmnopqrstuvwxyz-0123456789';
	$id_is_safe = true;
	
	$unsafe_article_id = $_GET["id"];
	$unsafe_article_id_chars = str_split($unsafe_article_id);
	foreach($unsafe_article_id_chars as $unsafe_article_id_char)
	{
		if(empty($unsafe_article_id_char))
			continue;
		
		if(strpos($safe_characters, $unsafe_article_id_char) !== false)
			continue;
		
		$id_is_safe = false;
		break;
	}
	
	if($id_is_safe)
		$article_id = $unsafe_article_id;
	else
		$article_id = "error";
	
}
else
	$article_id = "error";

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

?>
</head>
<body>
	<div class="unselectable" style="position: absolute; z-index: 42; display: none; color:var(--pico-8-cyan); background-color:var(--pico-8-white); padding-left: 5px; padding-right: 5px;" id="copied_to_clipboard">Copied!</div>
	<div class="background">
		<?php
			echo_banner();
			echo_selector($active_tab);
		?>
		
		<div class="content" id="content">
			<?php
				$content = "articles/{$article_id}/{$fileToLoad}.php";
				
				if(file_exists($content))
				{
					if(isset($articleData))
					{
						if($dbConn)
						{
							$sqlNext = GetNextPreviousSql("> '{$articleData['timestamp']}'","ASC");
							$sqlRandom = GetRandomSql($articleData['id']);
							$sqlPrevious = GetNextPreviousSql("< '{$articleData['timestamp']}'","DESC");
							
							// Next Article Data
							$result = mysqli_query($dbConn,$sqlNext);
							$numRows = mysqli_num_rows($result);
							if($numRows > 0)
							{
								$row = mysqli_fetch_assoc($result);
								
								$timestamp = strtotime($row['timestamp']);
								$newTimestampFormat = date('M jS, Y',$timestamp);
								
								if(!is_null($row['link']))
									$link = $row['link'];
								else
									$link = "https://www.rismosch.com/article?id={$row['id']}";
								
								$nextArticleData = array(
									'id' => $row['id'],
									'type' => $row['type'],
									'category' => $row['category'],
									'title' => $row['title'],
									'timestamp' => $newTimestampFormat,
									'link' => $link,
									'thumbnail_path' => GetThumbnailPath($row),
								);

								$footArticleData = $nextArticleData;
								$footArticlePrefix = "Next Post";
							}
							else {
								// Random Article Data
								$result = mysqli_query($dbConn,$sqlRandom);
								$numRows = mysqli_num_rows($result);
								if($numRows > 0)
								{
									$row = mysqli_fetch_assoc($result);

									$timestamp = strtotime($row['timestamp']);
									$newTimestampFormat = date('M jS, Y',$timestamp);

									if(!is_null($row['link']))
										$link = $row['link'];
									else
										$link = "https://www.rismosch.com/article?id={$row['id']}";
										
									$footArticleData = array(
										'id' => $row['id'],
										'type' => $row['type'],
										'category' => $row['category'],
										'title' => $row['title'],
										'timestamp' => $newTimestampFormat,
										'link' => $link,
										'thumbnail_path' => GetThumbnailPath($row),
									);
									
									$footArticlePrefix = "Random Post";
								}
							}
							
							// Previous Article Data
							$result = mysqli_query($dbConn,$sqlPrevious);
							$numRows = mysqli_num_rows($result);
							if($numRows > 0)
							{
								$row = mysqli_fetch_assoc($result);
								
								$timestamp = strtotime($row['timestamp']);
								$newTimestampFormat = date('M jS, Y',$timestamp);
								
								if(!is_null($row['link']))
									$link = $row['link'];
								else
									$link = "https://www.rismosch.com/article?id={$row['id']}";
								
								$prevArticleData = array(
									'id' => $row['id'],
									'type' => $row['type'],
									'category' => $row['category'],
									'title' => $row['title'],
									'timestamp' => $newTimestampFormat,
									'link' => $link,
									'thumbnail_path' => GetThumbnailPath($row),
								);
							}
						}
						
						$timestamp = strtotime($articleData['timestamp']);
						$newTimestampFormat = date('M jS, Y',$timestamp);
						
						// Article Links
						echo "<p style=\"text-align: center; margin-top: 13px;\">";
						
						// Previous
						echo "<span style=\"float: left; text-align: left;\">";
						
						if(isset($prevArticleData))
							echo "<a title=\"{$prevArticleData['title']}\" href=\"{$prevArticleData['link']}\">";
						else
							echo "<span class=\"unselectable\" style=\"color:var(--pico-8-light-grey)\">";
						
						echo "&#9664; prev";
						
						if(isset($prevArticleData))
							echo "</a>";
						else
							echo "</span>";
						
						echo "</span>";
						
						// Permalink
						echo "<span style=\"color: var(--pico-8-blue); display: none;\" id=\"permalink_header\"><a onclick=\"CopyPermalink(event)\" style=\"cursor: pointer; text-decoration: underline;\">permalink</a></span>";
						
						// Next
						echo "<span style=\"float: right; text-align: right;\">";
						
						if(isset($nextArticleData))
							echo "<a title=\"{$nextArticleData['title']}\" href=\"{$nextArticleData['link']}\" style=\"margin-right: 5px;\">";
						else
							echo "<span class=\"unselectable\" style=\"color:var(--pico-8-light-grey)\">";
						
						echo "next &#9654;";
						
						if(isset($nextArticleData))
							echo "</a>";
						else
							echo "</span>";
						
						echo "</span></p>";
						
						// Title
						echo "<noscript><br></noscript><h1>{$title}</h1><p>{$articleData['category']} &#183; {$newTimestampFormat}</p>";
					}
					
					// Content
					include $content;
					
					// Foot Article Widgets
					if(isset($footArticleData))
					{
						echo "<p style=\"border-bottom-width: 5px; border-bottom-style: dashed; border-bottom-color:var(--pico-8-white);\"></p>";
						echo"
						<table style=\"width: 100%;\">
							<tr><td><a title=\"{$footArticleData['title']}\" href=\"{$footArticleData['link']}\" class=\"articles_entry_link\">
							<div class=\"articles_mobile\">
								<table class=\"articles_entry\">
									<tr>
										<td>
											<div class=\"articles_thumbnail_wrapper_outside\">
												<div class=\"articles_thumbnail_wrapper_inside\">
													"; late_image($footArticleData['thumbnail_path'], "articles_thumbnail", ""); echo "
												</div>
											</div>
										</td>
									</tr>
									<tr>
										<td>
											<div class=\"articles_thumbnail_information\">
												<h3>{$footArticlePrefix}: {$footArticleData['title']}</h3>
												<p>{$footArticleData['category']} &#183; {$footArticleData['timestamp']}</p>
											</div>
										</td>
									</tr>
								</table>
							</div>
							<div class=\"articles_desktop\">
								<table class=\"articles_entry\">
									<tr>
										<td class=\"articles_thumbnail_row_desktop\">
											<div class=\"articles_thumbnail_wrapper\">
												"; late_image($footArticleData['thumbnail_path'], "articles_thumbnail", ""); echo "
											</div>
										</td>
										<td>
											<div class=\"articles_thumbnail_information\">
												<h3>{$footArticlePrefix}: {$footArticleData['title']}</h3>
												<br>
												<p>{$footArticleData['category']} &#183; {$footArticleData['timestamp']}</p>
											</div>
										</td>
									</tr>
								</table>
							</div>
							</a></td></tr>
						</table>
						";
					}
					
					echo "<p style=\"border-bottom-width: 5px; border-bottom-style: dashed; border-bottom-color:var(--pico-8-white);\"></p>";
					
					if(isset($prevArticleData))
					{
						echo "
							<table>
								<tr>
									<td>&#9664;</td>
									<td><a title=\"{$prevArticleData['title']}\" style=\"display:inline-block;\" href=\"{$prevArticleData['link']}\">Previous Post: {$prevArticleData['title']}</a></td>
								</tr>
							</table>
						";
					}
					
					if(isset($articleData))
					{
						echo "
							<table style=\"margin-top:10px;\">
								<tr>
									<td>&#9654;</td>
									<td><a title=\"Blog\" style=\"display:inline-block;\" href=\"https://www.rismosch.com/blog?category={$articleData['category_id']}\">More <b>{$articleData['category']}</b> related Posts</a></td>
								</tr>
							</table>
						";
					}
					
					echo "
						<table style=\"margin-top:10px; display: none;\" id=\"permalink_foot\">
							<tr>
								<td>&#9654;</td>
								<td><span style=\"color:var(--pico-8-blue);\"><a onclick=\"CopyPermalink(event)\" style=\"cursor: pointer; text-decoration: underline;\">permalink</a></span></td>
							</tr>
						</table>
					";
					
					echo "<p style=\"border-bottom-width: 5px; border-bottom-style: dashed; border-bottom-color:var(--pico-8-white);\"></p>";
				}
				else
				{
					echo "<h1>{$title}</h1><p>Could not find the article you were looking for</p>";
					$article_id = "error";
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
		
		document.getElementById("permalink_header").style.display = "inline";
		document.getElementById("permalink_foot").style.display = "inline-block";

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
		
		var copied_to_clipboard_animation_id;
		var copied_to_clipboard_currentFrame = 0;
		var copied_to_clipboard_animationIsPlaying = false;
		
		var copied_to_clipboard = document.getElementById("copied_to_clipboard");
		var positionX = 0;
		var positionY = 0;
		
		function CopyPermalink(event)
		{
			// Copy Text
			var textArea = document.createElement("textarea");
			
			textArea.style.position = 'fixed';
			textArea.style.top = 0;
			textArea.style.left = 0;
			textArea.style.width = '2em';
			textArea.style.height = '2em';
			textArea.style.padding = 0;
			textArea.style.border = 'none';
			textArea.style.outline = 'none';
			textArea.style.boxShadow = 'none';
			textArea.style.background = 'transparent';
			
			textArea.value = "https://www.rismosch.com/article?id="<?php echo "+\"{$article_id}\"";?>;
			
			document.body.appendChild(textArea);
			textArea.focus();
			textArea.select();
			
			try {
				var successful = document.execCommand('copy');
				var msg = successful ? 'successful' : 'unsuccessful';
				console.log('Copying text command was ' + msg);
			} catch (err) {
				console.log('Oops, unable to copy');
			}
			
			document.body.removeChild(textArea);
			
			// Play Animation
			positionX = event.clientX;
			positionY = event.clientY + window.scrollY;
			
			copied_to_clipboard_currentFrame = 0;
			if(!copied_to_clipboard_animationIsPlaying)
				copied_to_clipboard_animation_id = setInterval(animate_clipboard, 10);
			
			copied_to_clipboard_animationIsPlaying = true;
		}
		
		function animate_clipboard()
		{
			++copied_to_clipboard_currentFrame;
			
			var x = positionX;
			var y = positionY - copied_to_clipboard_currentFrame;
			
			if(copied_to_clipboard_currentFrame < 35)
			{
				copied_to_clipboard.style.top = y+"px";
				copied_to_clipboard.style.left = x+"px";
				copied_to_clipboard.style.display = "block";
			}
			
			if(copied_to_clipboard_currentFrame >= 150)
			{
				clearInterval(copied_to_clipboard_animation_id);
				copied_to_clipboard_animationIsPlaying = false;
				copied_to_clipboard.style.display = "none";
			}
		}
		
	</script>
</body>
</html>
