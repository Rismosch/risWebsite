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
            Article_Categories.name AS category,
            Articles.timestamp AS timestamp,
            Articles.Description AS description
        FROM
            Articles,
            Article_Categories
        WHERE
            Articles.category_id = Article_Categories.id AND
            Articles.link IS NULL AND
            Articles.type_id = 0
        ORDER BY
            Articles.timestamp DESC
    ";
    
	$result = mysqli_query($dbConn,$sql);
	$rowCount = mysqli_num_rows($result);
    echo "<br>found {$rowCount} rows<br>";

    if ($rowCount > 0) {
    
        echo "<br>generating rss...<br>";

        $rss = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>";

        $rss .= "<rss version=\"2.0\" ";
        $rss .= "xmlns:content=\"http://purl.org/rss/1.0/modules/content/\" ";
        $rss .= "xmlns:wfw=\"http://wellformedweb.org/CommentAPI/\" ";
        $rss .= "xmlns:dc=\"http://purl.org/dc/elements/1.1/\" ";
        $rss .= "xmlns:atom=\"http://www.w3.org/2005/Atom\" ";
        $rss .= "xmlns:sy=\"http://purl.org/rss/1.0/modules/syndication/\" ";
        $rss .= "xmlns:slash=\"http://purl.org/rss/1.0/modules/slash/\" ";
        $rss .= "xmlns:georss=\"http://www.georss.org/georss\" ";
        $rss .= "xmlns:geo=\"http://www.w3.org/2003/01/geo/wgs84_pos#\" ";
        $rss .= ">";

        $rss .= "<channel>";
        $rss .= "<title>Rismosch</title>";
        $rss .= "<atom:link href=\"https://www.rismosch.com/rss.xml\" rel=\"self\" type=\"application/rss+xml\" />";
        $rss .= "<link>https://www.rismosch.com</link>";
        $rss .= "<description>Blog and Project-Collection of Simon Sutoris</description>";

        $lastBuildDate = date('r', time());
        $rss .= "<lastBuildDate>{$lastBuildDate}</lastBuildDate>";

        $rss .= "<image>";
        $rss .= "<url>https://rismosch.com/favicon.png</url>";
        $rss .= "<title>Rismosch</title>";
        $rss .= "<link>https://www.rismosch.com</link>";
        $rss .= "<width>32</width>";
        $rss .= "<height>32</height>";
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
            $category = $row['category'];
            $timestamp = $row['timestamp'];
            $datetime = strtotime($row['timestamp']);
            $date = date('r', $datetime);
            $description = $row['description'];

            $rss .= "<title>{$title}</title>";
            $rss .= "<link>{$link}</link>";
            $rss .= "<dc:creator><![CDATA[Simon Sutoris]]></dc:creator>";
            $rss .= "<category>![CDATA[{$category}]]</category>";
            $rss .= "<guid isPermaLink=\"false\">{$id}</guid>";
            $rss .= "<pubDate>{$date}</pubDate>";
            $rss .= "<description>{$description}</description>";

            $rss .= "<content:encoded><![CDATA[";
            
            $rss_content_url = "https://www.rismosch.com/articles/${id}/page_0.php";
            $ch = curl_init($rss_content_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $content = curl_exec($ch);
            $rss .= $content;

            $rss .= "]]></content:encoded>";
            
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