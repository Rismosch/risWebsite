<?php

// Constants
$categoryFilterStringDefault = "true";
$categoryDefault = 0;
$showDefault = 10;
$pageDefault = 1;

// URL Parameters
if(isset($_GET["category"]))
	$category = intval($_GET["category"]);
else
	$category = $categoryDefault;

if(
	$category != 1 &&
	$category != 2 &&
	$category != 42)
	$category = $categoryDefault;

if($category != $categoryDefault)
	$categoryFilterString = "Articles.category_id = " . $category;
else
	$categoryFilterString = "true";


if(isset($_GET["show"]))
	$show = intval($_GET["show"]);
else
	$show = $showDefault;

if($show <= 0)
	$show = $showDefault;


if(isset($_GET["page"]))
	$page = intval($_GET["page"]);
else
	$page = $pageDefault;

if($page <= 0)
	$page = $pageDefault;

$offset = ($page - 1) * $show;


if(!isset($article_type_id))
	$article_type_id = 0;


if($category != $categoryDefault)
	$selectedCategoryFilterString = "Articles.category_id = " . $category;
else
	$selectedCategoryFilterString = "true";

// Functions
function printDropdown($dbConn, $pageName)
{
	global
		$category,
		$show;
	
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
	
	$selectedCategoryResult = mysqli_query($dbConn,$sqlSelectedCategory);
	$selectedCategoryNumRows = mysqli_num_rows($selectedCategoryResult);
	if($selectedCategoryNumRows > 0)
	{
		$selectedCategoryRow = mysqli_fetch_assoc($selectedCategoryResult);
		$selectedCategoryId = $selectedCategoryRow['id'];
		$selectedCategoryName = $selectedCategoryRow['name'];
	}
	else
	{
		$selectedCategoryId = 0;
		$selectedCategoryName = "All";
	}
	echo "
		<div class=\"dropdown\">
			<button class=\"dropdownButton dropdownCategory\" id=\"dropdownButton\">Filter</button>
			<div class=\"dropdownContent dropdownCategory\">
				<a href=\"https://www.rismosch.com/{$pageName}?category=0&show={$show}&page=0\" ";
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
				<a href=\"https://www.rismosch.com/{$pageName}?category={$categoryRow['id']}&show={$show}&page=0\" ";
			if ($selectedCategoryId == $categoryRow['id'])
				echo "class=\"dropdown_selected\"";
			echo ">{$categoryRow['name']}</a>\n";
		}
	}
	echo "
			</div>
		</div>
	";
	
	echo "<p style=\"color:var(--pico-8-dark-grey);\">{$selectedCategoryName} &#183; ";
}

function printArticles($dbConn, $pageName)
{
	global
		$categoryFilterString,
		$show,
		$offset,
		$article_type_id;
	
	
	$sqlArticles = "
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
			Articles.category_id = Article_Categories.id AND
			Articles.type_id = Article_Types.id AND
			Articles.type_id = {$article_type_id} AND
			{$categoryFilterString}
		ORDER BY
			Articles.timestamp DESC
		LIMIT
			{$offset},
			{$show}
	";
	
	$result = mysqli_query($dbConn,$sqlArticles);
	$numRows = mysqli_num_rows($result);
	
	$totalRowsResult = mysqli_query($dbConn,"SELECT COUNT(id) as count FROM Articles WHERE type_id={$article_type_id} AND {$categoryFilterString}");
	$row = mysqli_fetch_assoc($totalRowsResult);
	echo "{$numRows} of total {$row['count']} posts</p>";
	
	if($numRows > 0)
	{
		echo "
			<table style=\"width: 100%;\">
				<tr class=\"row_empty\"><td></td></tr>
				<tr class=\"row_empty row_devider\"><td></td></tr>
		";
		while($row = mysqli_fetch_assoc($result))
		{
			$timestamp = strtotime($row['timestamp']);
			$newTimestampFormat = date('M jS, Y',$timestamp);
			
			if(!is_null($row['link']))
				$link = $row['link'];
			else
				$link = "https://www.rismosch.com/article?id={$row['id']}";
			
			$thumbnail = GetThumbnailPath($row);
			
			echo
			"<tr><td><a title=\"{$row['title']}\" href=\"{$link}\" class=\"articles_entry_link\">
				<div class=\"articles_mobile\">
					<table class=\"articles_entry\">
						<tr>
							<td>
								<div class=\"articles_thumbnail_wrapper_outside\">
									<div class=\"articles_thumbnail_wrapper_inside\">
										"; late_image($thumbnail, "articles_thumbnail", ""); echo "
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								<div class=\"articles_thumbnail_information\">
									<h3>{$row['title']}</h3>
									<p>{$row['category']} &#183; {$newTimestampFormat}</p>
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
									"; late_image($thumbnail, "articles_thumbnail", ""); echo "
								</div>
							</td>
							<td>
								<div class=\"articles_thumbnail_information\">
									<h3>{$row['title']}</h3>
									<br>
									<p>{$row['category']} &#183; {$newTimestampFormat}</p>
								</div>
							</td>
						</tr>
					</table>
				</div>
			</a></td></tr>
			<tr class=\"row_empty\"><td></td></tr>
			<tr class=\"row_empty row_devider\"><td>
			</td></tr>
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
}

function GetNextPreviousSql($nextPreviousTimestamp, $orderBy)
{
	return "
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
		Articles.category_id = Article_Categories.id AND
		Articles.type_id = Article_Types.id AND
		Articles.link IS NULL AND
		Articles.timestamp {$nextPreviousTimestamp}
	ORDER BY
		Articles.timestamp {$orderBy}
	LIMIT
		0,
		1
	";
}

function GetRandomSql($exclude)
{
	return "
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
		Articles.category_id = Article_Categories.id AND
		Articles.type_id = Article_Types.id AND
		Articles.link IS NULL AND
		Articles.id <> \"{$exclude}\"
	ORDER BY
		RAND()
	LIMIT
		0,
		1
	";
}

function printSelector($dbConn, $pageName)
{
	global
		$category,
		$selectedCategoryFilterString,
		$show,
		$page,
		$article_type_id;
	
	$sqlCount ="
		SELECT
			COUNT(id) as COUNT
		FROM
			Articles
		WHERE
			Articles.type_id = {$article_type_id} AND
			{$selectedCategoryFilterString}
	";
	
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
				<a title=\"{$previousPage}\" href=\"https://www.rismosch.com/{$pageName}?category={$category}&show={$show}&page={$previousPage}\" class=\"button\">&lt;</a>
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
				<a title=\"{$i}\" href=\"https://www.rismosch.com/{$pageName}?category={$category}&show={$show}&page={$i}\" class=\"button\">";
			
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
				<a title=\"{$nextPage}\" href=\"https://www.rismosch.com/{$pageName}?category={$category}&show={$show}&page={$nextPage}\" class=\"button\">&gt;</a>
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

function GetThumbnailPath($articleData)
{
	if(isset($articleData))
	{
		$thumbnail_path = $articleData['thumbnail_path'];
		if(is_null($thumbnail_path))
		{
			$thumbnail_path = "articles/{$articleData['id']}/thumbnail.webp";
		}
		
		if(!file_exists($thumbnail_path))
		{
			$thumbnail_path = "assets/thumbnails/default.webp";
		}
	}
	else
		$thumbnail_path = "assets/thumbnails/default.webp";
	
	return $thumbnail_path;
}

function GetArticleData($dbConn, $articleId)
{
	$sqlArticleData ="
	SELECT
		Articles.id AS id,
		Articles.type_id AS type_id,
		Article_Types.name AS type,
		Articles.category_id AS category_id,
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
		Articles.category_id = Article_Categories.id AND
		Articles.type_id = Article_Types.id AND
		Articles.id = '{$articleId}'
	";
	
	$result = mysqli_query($dbConn, $sqlArticleData);
	$numRows = mysqli_num_rows($result);
	if($numRows > 0)
	{
		return mysqli_fetch_assoc($result);
	}
	
	return NULL;
}

?>