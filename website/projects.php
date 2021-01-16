<?php

include 'php/head.php';

$categoryFilterStringDefault = "true";
$categoryDefault = 0;
$showDefault = 10;
$pageDefault = 1;

if(isset($_GET["ct"]))
	$category = intval($_GET["ct"]);
else
	$category = $categoryDefault;

if($category < 1 || $category > 3)
	$category = $categoryDefault;

if($category != $categoryDefault)
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


$sqlCategories ="
SELECT
	id,
	name
FROM
	Article_Categories
";

$sqlSelectedCategory ="
SELECT
	id,
	name
FROM
	Article_Categories
WHERE
	id = {$category}
";

$sqlArticles ="
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

if($category != $categoryDefault)
	$selectedCategoryFilterString = "Articles.category_id = " . $category;
else
	$selectedCategoryFilterString = "true";

$sqlCount ="
SELECT
	COUNT(id) as COUNT
FROM
	Articles
WHERE
	Articles.type_id = 1 AND
	{$selectedCategoryFilterString}
";

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
					
					// Category Dropdown
					$selectedCategoryResult = mysqli_query($dbConn,$sqlSelectedCategory);
					$selectedCategoryNumRows = mysqli_num_rows($selectedCategoryResult);
					if($selectedCategoryNumRows > 0)
					{
						$selectedCategoryRow = mysqli_fetch_assoc($selectedCategoryResult);
						$selectedCategoryId = $selectedCategoryRow['id'];
					}
					else
					{
						$selectedCategoryId = 0;
					}
					echo "
						<div class=\"dropdown\">
							<button onclick=\"showDropdown()\" class=\"dropdownButton\" id=\"dropdownButton\">Filter</button>
							<div id=\"dropdownList\" class=\"dropdownContent\">
								<a href=\"https://www.rismosch.com/projects?ct=0&ls={$show}&pg=0\" ";
					if ($selectedCategoryId == 0)
						echo "class=\"dropdown_selected\"";
					echo ">Show All</a>\n";
					$categoriesResult = mysqli_query($dbConn,$sqlCategories);
					$categoriesNumRows = mysqli_num_rows($categoriesResult);
					if($categoriesNumRows > 0)
					{
						while($categoryRow = mysqli_fetch_assoc($categoriesResult))
						{
							echo "
								<a href=\"https://www.rismosch.com/projects?ct={$categoryRow['id']}&ls={$show}&pg=0\" ";
							if ($selectedCategoryId == $categoryRow['id'])
								echo "class=\"dropdown_selected\"";
							echo ">{$categoryRow['name']}</a>\n";
						}
					}
					echo "
							</div>
						</div>
					";
					
					// Articles Visualisation
					$result = mysqli_query($dbConn,$sqlArticles);
					$numRows = mysqli_num_rows($result);
					if($numRows > 0)
					{
						echo "
							<table style=\"width: 100%;\"><tr class=\"row_empty row_devider\"><td></td></tr>
						";
						while($row = mysqli_fetch_assoc($result))
						{
							$timestamp = strtotime($row['timestamp']);
							$newTimestampFormat = date('M jS, Y',$timestamp);
							
							if(!is_null($row['link']))
								$link = $row['link'];
							else
								$link = "https://www.rismosch.com/article?id={$row['id']}";
							
							if(!is_null($row['thumbnail_path']))
								$thumbnail = $row['thumbnail_path'];
							else
								$thumbnail = "https://www.rismosch.com/articles/{$row['id']}/thumbnail.png";
							
							echo "
								<tr>
									<td>
										<a href=\"{$link}\" class=\"articles_entry_link\">
											<table class=\"articles_entry clickable\">
												<tr>
													<td>
														<img class=\"articles_thumbnail\" src=\"{$thumbnail}\">
													</td>
													<td>
														<div>
															<h3>{$row['title']}</h3>
															<p>{$row['category']}</p>
															<p>{$newTimestampFormat}</p>
														</div>
													</td>
												</tr>
											</table>
										</a>
									</td>
								</tr>
								<tr class=\"row_empty\"><td></td></tr>
								<tr class=\"row_empty row_devider\"><td></td></tr>
							";
						}
						echo "
							</table>
						";
					}
					else
					{
						echo "<p>no articles found &#175;&#92;&#95;&#40;&#12484;&#41;&#95;&#47;&#175;</p>";
					}
					
					// Page Selector
					echo "
						<div class=\"page_selector\">
					";
					$countResult = mysqli_query($dbConn,$sqlCount);
					$countNumRows = mysqli_num_rows($countResult);
					if($countNumRows > 0)
					{
						$countRow = mysqli_fetch_assoc($countResult);
						$totalCount = $countRow['COUNT'];
						$pageCount = intdiv($totalCount,$show);
						
						if($totalCount % $show != 0)
							++$pageCount;
						
						if($page > 1 && $page < $pageCount + 1)
						{
							$previousPage = $page - 1;
							echo "
								<a title=\"{$previousPage}\" href=\"https://www.rismosch.com/projects?ct={$category}&ls={$show}&pg={$previousPage}\" class=\"button\">&lt;</a>
							";
						}
						else
						{
							echo "
								<a class=\"button button_inactive\">&lt;</a>
							";
						}
						
						for($i = 1; $i <= $pageCount; ++$i)
						{
							echo "
								<a title=\"{$i}\" href=\"https://www.rismosch.com/projects?ct={$category}&ls={$show}&pg={$i}\" class=\"button\">";
							
							if($page == $i)
								echo "<u><b>{$i}</b></u>";
							else
								echo $i;
							
							echo "</a>\n";
						}
						
						if($page < $pageCount && $page > 0)
						{
							$nextPage = $page + 1;
							echo "
								<a title=\"{$nextPage}\" href=\"https://www.rismosch.com/projects?ct={$category}&ls={$show}&pg={$nextPage}\" class=\"button\">&gt;</a>
							";
						}
						else
						{
							echo "
								<a class=\"button button_inactive\">&gt;</a>
							";
						}
					}
					echo "
						</div>
					";
					
				}
				else{
					echo "<p>ERROR: Could not connect to database.</p>";
				}
			?>
		</div>
		
		<?php include 'php/foot.php'; ?>
		
		<button onclick="scrollToTop()" id="scroll_to_top" class="scroll_to_top">Top</button>
	</div>
	
	<script>
	function showDropdown() {
		document.getElementById("dropdownList").classList.toggle("show");
	}

	window.onclick = function(event) {
		if (!event.target.matches('.dropdownButton'))
		{
			var dropdowns = document.getElementsByClassName("dropdownContent");
			for (var i = 0; i < dropdowns.length; ++i)
			{
				var openDropdown = dropdowns[i];
				if (openDropdown.classList.contains('show')) {
					openDropdown.classList.remove('show');
				}
			}
		}
	}
	</script>
</body>
</html>
