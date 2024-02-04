<?php

include 'secret/secret.php';

echo "<br>connecting to database...<br>";

$dbConn = mysqli_connect($dbHost, $dbSelectUsername, $dbSelectPassword, $dbName);


if($dbConn){
    echo "<br>retreiving articles...<br>";

    $sql = "
        SELECT
            Articles.id AS id,
            Articles.title AS title,
            Articles.description AS description,
            Article_Categories.name AS category,
            Articles.timestamp AS timestamp
        FROM
            Articles,
            Article_Categories
        WHERE
            Articles.category_id = Article_Categories.id AND
            Articles.link IS NULL
        ORDER BY
            Articles.timestamp DESC
    ";
    
	$result = mysqli_query($dbConn,$sql);
	$rowCount = mysqli_num_rows($result);
    echo "<br>found {$rowCount} rows<br>";

    if ($rowCount > 0) {
    
        echo "<br>generating rss...<br>";

        $rss = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>";

        $rss .= "<rss version=\"2.0\">";

        $rss .= "<channel>";
        $rss .= "<title>Rismosch</title>";
        $rss .= "<description>Blog and Project-Collection of Simon Sutoris</description>";
        $rss .= "<link>https://www.rismosch.com/</link>";

        $lastBuildDate = date('r', time());
        $rss .= "<lastBuildDate>{$lastBuildDate}</lastBuildDate>";
        
        $rss .= "<image>";
        $rss .= "<title>Rismosch Logo</title>";
        $rss .= "<url>https://www.rismosch.com/assets/meta_image_x10.png</url>";
        $rss .= "<link>https://www.rismosch.com/</link>";
        $rss .= "</image>";
    
        $rowNumber = 0;
        while($row = mysqli_fetch_assoc($result))
        {
            $rowNumber += 1;

            $id = $row['id'];
            echo "<br>{$rowNumber}/{$rowCount} generating item for \"{$id}\"...<br>";

            $rss .= "<item>";

            $title = $row['title'];
            $link = "https://www.rismosch.com/article?id={$id}";
            $description = $row['description'];
            $category = $row['category'];
            $timestamp = $row['timestamp'];
            $datetime = strtotime($row['timestamp']);
            $date = date('r', $datetime);

            $rss .= "<title>{$title}</title>";
            $rss .= "<link>{$link}</link>";
            $rss .= "<description>{$description}</description>";
            $rss .= "<author>Simon Sutoris</author>";
            $rss .= "<category>{$category}</category>";
            $rss .= "<guid>{$id}</guid>";
            $rss .= "<pubDate>{$date}</pubDate>";
            
            $rss .= "</item>";
        }

        $rss .= "</channel>";
        $rss .= "</rss>";

        echo "<br>saving file...<br>";
        file_put_contents("index.xml", $rss);

        echo "<br>done<br>";

    } else {
        echo "<br>no rows found<br>";
    }

} else {
    echo "<br>failed to connect to database<br>";
}

?>