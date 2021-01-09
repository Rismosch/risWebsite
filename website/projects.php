<?php

include_once 'secret/contactEmail.php';
include_once 'secret/dbConn.php';
include_once 'secret/reCAPTCHA.php';

include_once 'Mobile-Detect-2.8.34/Mobile_Detect.php';
$detect = new Mobile_Detect;
$isMobile = $detect->isMobile() && !$detect->isTablet();


$categoryFilterStringDefault = "true";
$categoryDefault = 0;
$showDefault = 10;
$pageDefault = 1;

if(isset($_GET["ct"]))
	$category = intval($_GET["ct"]);
else
	$category = $categoryDefault;

if($category >= 1 && $category <= 3)
	$categoryFilterString = "Article_Categories.id = " . $category;
else
	$categoryFilterString = "true";


if(isset($_GET["ls"]))
	$show = intval($_GET["ls"]);
else
	$show = $showDefault;

if($show <= 0)
	$show = $showDefault;


if(isset($_GET["pg"]))
	$page = intval($_GET["pg"]);
else
	$page = $pageDefault;

if($page <= 0)
	$page = $pageDefault;

$offset = ($page - 1) * $show;

$sql ="
SELECT
	Articles.id AS id,
	Article_Types.name AS type,
	Article_Categories.name AS category,
	Articles.title AS title,
	Articles.timestamp AS timestamp,
	Articles.link AS link,
	Articles.thumbnail_path AS thumbnail_path
FROM
	Articles,
	Article_Categories,
	Article_Types
WHERE
	Articles.type_id = Article_Types.id AND
	Articles.category_id = Article_Categories.id AND
	Articles.type_id = 1 AND
	{$categoryFilterString}
ORDER BY
	Articles.timestamp
LIMIT
	{$offset},
	{$show}
";

$countSql ="
SELECT
	COUNT(id) as COUNT
FROM
	Articles
WHERE
	Articles.type_id = 1
";

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title>Rismosch</title>
	
	<link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
	<link rel="icon" type="image/png" href="favicon.png" sizes="32x32">
	<link rel="shortcut icon" href="favicon.ico">
	<meta name="msapplication-TileImage" content="mstile-144x144.png">
	<meta name="msapplication-TileColor" content="#00aba9">
	
	<link rel="stylesheet" href="css/colors.css">
	<link rel="stylesheet" href="css/main.css">
	<link rel="stylesheet" href="css/<?php
		if( $isMobile )
			echo "mobile";
		else
			echo "desktop";
	?>.css">
	
	<script src="scripts/jquery-3.5.1.min.js"></script>
	<script src="scripts/banner.js"></script>
	<script src="scripts/continuousSession.js"></script>
	<script src="scripts/cookie.js"></script>
	<script src="scripts/util.js"></script>
</head>
<body>
	<div class="background">
		<a href="https://www.rismosch.com/">
			<img
				id="banner"
				class="banner pixel_image"
				src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
				onmouseover="playHoverAnimation()"
			>
		</a>
		
		<div class="selector" id="selector">
			<ul class="selector_tabs" id="selector_tabs">
				<li class="selector_tab" id="selector_tab">
					<a href="https://www.rismosch.com/">
						<div><b>Home</b></div>
					</a>
				</li>
				<li class="selector_tab" id="selector_tab">
					<a href="https://www.rismosch.com/blog">
						<div><b>Blog</b></div>
					</a>
				</li>
				<li class="selector_tab active_tab" id="selector_tab active_tab">
					<a href="https://www.rismosch.com/projects">
						<div><b>Projects</b></div>
					</a>
				</li>
				<li class="selector_tab" id="selector_tab">
					<a href="https://www.rismosch.com/about">
						<div><b>About</b></div>
					</a>
				</li>
			</ul>
		</div>
		
		<div class="content" id="content">
			<h1>Projects</h1>
			<?php
				if($dbConn){
					
					$result = mysqli_query($dbConn,$sql);
					$numRows = mysqli_num_rows($result);
					if($numRows > 0)
					{
						echo "<table style=\"width: 100%;\"><tr class=\"row_empty row_devider\"><td></td></tr>";
						while($row = mysqli_fetch_assoc($result))
						{
							$timestamp = strtotime($row['timestamp']);
							$newTimestampFormat = date('M jS, Y',$timestamp);
							
							echo "
								<tr>
									<td>
										<table>
											<tr>
												<td>
													<a href=\"{$row['link']}\">
														<img class=\"articles_thumbnail\" src=\"{$row['thumbnail_path']}\">
													</a>
												</td>
												<td>
													<a href=\"{$row['link']}\" class=\"articles_entry_link\">
														<h3>{$row['title']}</h3>
														<p>{$row['category']}</p>
													</a>
													<p>{$newTimestampFormat}</p>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr class=\"row_empty\"><td></td></tr>
								<tr class=\"row_empty row_devider\"><td></td></tr>
							";
						}
						echo "</table>";
					}
					
					echo "<div class=\"page_selector\">";
					
					$countResult = mysqli_query($dbConn,$countSql);
					$countNumRows = mysqli_num_rows($countResult);
					if($countNumRows > 0)
					{
						$countRow = mysqli_fetch_assoc($countResult);
						$totalCount = $countRow['COUNT'];
						$pageCount = intdiv($totalCount,$show);
						
						if($totalCount % $show != 0)
							++$pageCount;
						
						if($page > 1)
						{
							$previousPage = $page - 1;
							echo "<a title=\"{$previousPage}\" href=\"https://www.rismosch.com/projects?ct={$category}&ls={$show}&pg={$previousPage}\">&lt;</a>";
						}
						else
						{
							echo "<a class=\"page_selector_button_invisible\"></a>";
						}
						
						for($i = 1; $i <= $pageCount; ++$i)
						{
							echo "<a title=\"{$i}\" href=\"https://www.rismosch.com/projects?ct={$category}&ls={$show}&pg={$i}\">";
							
							if($page == $i)
								echo "<b>{$i}</b>";
							else
								echo $i;
							
							echo"</a>";
						}
						
						if($page < $pageCount)
						{
							$nextPage = $page + 1;
							echo "<a title=\"{$nextPage}\" href=\"https://www.rismosch.com/projects?ct={$category}&ls={$show}&pg={$nextPage}\">&gt;</a>";
						}
						else
						{
							echo "<a class=\"page_selector_button_invisible\"></a>";
						}
					}
					
					echo "</div>";
				}
				else{
					echo "<p>ERROR: Could not connect to database.</p>";
				}
			?>
		</div>
		
		<div class="foot" id="foot">
			
			<div class="socials" id="socials">
				<a title="YouTube" href="https://www.youtube.com/channel/UCrWSfmTaXTN_LzEsVRKNJTw">
					<img class="social_icon" src="assets/icon_social/youtube.png">
				</a>
				<a title="Bandcamp" href="https://rismosch.bandcamp.com">
					<img class="social_icon" src="assets/icon_social/bandcamp.png">
				</a>
				<a title="itch.io" href="https://rismosch.itch.io/">
					<img class="social_icon" src="assets/icon_social/itch_io.png">
				</a>
				<a title="GitHub" href="https://github.com/Rismosch">
					<img class="social_icon" src="assets/icon_social/github.png">
				</a>
				<a title="Twitter" href="https://twitter.com/Rismosch">
					<img class="social_icon" src="assets/icon_social/twitter.png">
				</a>
			</div>
			
			<div class="foot_links">
				<p><a href="https://www.rismosch.com/privacy">Privacy Policy</a> &nbsp; | &nbsp; <a href="https://www.rismosch.com/licenses">Licenses</a> &nbsp; | &nbsp; <a href="https://www.rismosch.com/contact">Contact</a></p>
			</div>
			
			<div class="foot_links">
				<p>Copyright &#169; 2020 Simon Sutoris</p>
			</div>
			
		</div>
		
		<button onclick="scrollToTop()" id="scroll_to_top" class="scroll_to_top">Top</button>
	</div>
</body>
</html>
