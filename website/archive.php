<?php

include 'php/util.php';

echo_head();

// URL Parameters
$sortDefault = 3; // date descending
if (isset($_GET["sort"]))
    $sort = intval($_GET["sort"]);
else
    $sort = $sortDefault;

if ($sort < 0)
    $sort = 0;
if ($sort > 5)
    $sort = 5;

$sortByNameAsc = $sort == 0;
$sortByNameDesc = $sort == 1;
$sortByDateAsc = $sort == 2;
$sortByDateDesc = $sort == 3;
$sortBySizeAsc = $sort == 4;
$sortBySizeDesc = $sort == 5;

?>

	<title>Archive</title>
	<meta name="description" content="Resumee of Simon Sutoris">
	<meta name="keywords" content="about, resumee, cv, curriculum vitae">

	<meta name="robots" content="all">

	<meta property="og:title" content="About" />
	<meta property="og:type" content="article" />
	<meta property="og:url" content="https://www.rismosch.com/about" />
	<meta property="og:image" content="https://www.rismosch.com/assets/meta_image_x10.png" />

	<meta name="author" content="Simon Sutoris">
</head>
<body>
	<div class="background">
		<?php
			echo_banner();
			echo_selector(3);
		?>
		
		<div class="content" id="content">
            <h1>Archive</h1>
            
            <p style="background-color:var(--pico-8-white); border: 5px solid var(--pico-8-cyan); padding: 20px;">Every file in this archive is available free of charge and usable without restriction. Go ham with it.</p>

            <?php
            $up = "&#9650;";
            $down = "&#9660;";

            if ($sortByNameAsc)
            {
                $nameSortChar = $up;
                $nameSortLink = 1;
                $scanDirSort = SCANDIR_SORT_ASCENDING;
            }
            elseif ($sortByNameDesc)
            {
                $nameSortChar = $down;
                $nameSortLink = 0;
                $scanDirSort = SCANDIR_SORT_DESCENDING;
            }
            else
            {
                $nameSortChar = "";
                $nameSortLink = 0;
                $scanDirSort = SCANDIR_SORT_NONE;
            }

            if ($sortByDateAsc)
            {
                $dateSortChar = $up;
                $dateSortLink = 3;
            }
            elseif ($sortByDateDesc)
            {
                $dateSortChar = $down;
                $dateSortLink = 2;
            }
            else
            {
                $dateSortChar = "";
                $dateSortLink = 3;
            }

            if ($sortBySizeAsc)
            {
                $sizeSortChar = $up;
                $sizeSortLink = 5;
            }
            elseif ($sortBySizeDesc)
            {
                $sizeSortChar = $down;
                $sizeSortLink = 4;
            }
            else
            {
                $sizeSortChar = "";
                $sizeSortLink = 4;
            }

            $archive_directory = "archive_files/";
            $outdated_directory = "archive_files/outdated/";
            
            $files = array();
            $outdated_files = array();
            
            $scanned_files = array_diff(scandir($archive_directory, $scanDirSort), array('..','.'));
            
            foreach ($scanned_files as $scanned_file)
            {
                $scanned_file_name = "{$archive_directory}{$scanned_file}";
                
                if ($scanned_file_name == "archive_files/outdated") {
                    continue;
                }
                
                $file = array(
                    "name" => $scanned_file_name,
                    "date" => filemtime($scanned_file_name),
                    "size" => filesize($scanned_file_name),
                );

                $files[] = $file;
            }
            
            $scanned_files = array_diff(scandir($outdated_directory, $scanDirSort), array('..','.','bak'));
            
            foreach ($scanned_files as $scanned_file)
            {
                $scanned_file_name = "{$outdated_directory}{$scanned_file}";
                
                if ($scanned_file_name == "archive_files/outdated") {
                    continue;
                }
                
                $file = array(
                    "name" => $scanned_file_name,
                    "date" => filemtime($scanned_file_name),
                    "size" => filesize($scanned_file_name),
                );

                $outdated_files[] = $file;
            }

            if ($sortByDateAsc)
            {
                usort($files, function($a, $b) {
                    return $a['date'] <=> $b['date'];
                });
                usort($outdated_files, function($a, $b) {
                    return $a['date'] <=> $b['date'];
                });
            }
            elseif ($sortByDateDesc)
            {
                usort($files, function($a, $b) {
                    return $b['date'] <=> $a['date'];
                });
                usort($outdated_files, function($a, $b) {
                    return $b['date'] <=> $a['date'];
                });
            }
            elseif ($sortBySizeAsc)
            {
                usort($files, function($a, $b) {
                    return $a['size'] <=> $b['size'];
                });
                usort($outdated_files, function($a, $b) {
                    return $a['size'] <=> $b['size'];
                });
            }
            elseif ($sortBySizeDesc)
            {
                usort($files, function($a, $b) {
                    return $b['size'] <=> $a['size'];
                });
                usort($outdated_files, function($a, $b) {
                    return $b['size'] <=> $a['size'];
                });
            }
            
            echo "
                <table style=\"width:100%;\">
                    <tr style=\"text-align: left;\">
                        <th><a href=\"https://www.rismosch.com/archive?sort={$nameSortLink}\" style=\"color: var(--pico-8-blue);\">Name{$nameSortChar}</a></th>
                        <th><a href=\"https://www.rismosch.com/archive?sort={$dateSortLink}\" style=\"color: var(--pico-8-blue);\">Last modified{$dateSortChar}</a></th>
                        <th><a href=\"https://www.rismosch.com/archive?sort={$sizeSortLink}\" style=\"color: var(--pico-8-blue);\">Size{$sizeSortChar}</a></th>
                    </tr>";

            foreach ($files as $file)
            {
                $name = $file["name"];
                $date = $file["date"];
                $size = $file["size"];

                $download_link = "https://www.rismosch.com/{$name}";
                $formatted_name = basename($name);
                $formatted_date = date("M jS, Y", $date);
                $formatted_time = date("G:i:s", $date);
                $formatted_timestamp = "{$formatted_date}<br>{$formatted_time}";

                $size_kb = intdiv($size, 1000);
                $size_mb = intdiv($size_kb, 1000);
                $size_mb_dezimals = $size_kb % 1000;

                if ($size_mb_dezimals == 0)
                    $formatted_size = "{$size_mb} MB";
                elseif ($size_mb_dezimals < 10)
                    $formatted_size = "{$size_mb}.00{$size_mb_dezimals} MB";
                elseif ($size_mb_dezimals < 100)
                    $formatted_size = "{$size_mb}.0{$size_mb_dezimals} MB";
                else
                    $formatted_size = "{$size_mb}.{$size_mb_dezimals} MB";

                echo "
                    <tr style=\"height: 3em;\">
                        <td style=\"word-break: break-word;\"><a href=\"{$download_link}\">{$formatted_name}</a></td>
                        <td style=\"text-align: right;\">{$formatted_timestamp}</td>
                        <td style=\"text-align: right; white-space: nowrap;\">{$formatted_size}</td>
                    </tr>";
            }

            echo "
                </table>
            ";
            
            ?>
            
            <h2>Outdated</h2>
            
            <p style="background-color:var(--pico-8-white); border: 5px solid var(--pico-8-cyan); padding: 20px;">The files below contain older versions of the files above.</p>
            
            <?php
            
            echo "
                <table style=\"width:100%;\">
                    <tr style=\"text-align: left;\">
                        <th><a href=\"https://www.rismosch.com/archive?sort={$nameSortLink}\" style=\"color: var(--pico-8-blue);\">Name{$nameSortChar}</a></th>
                        <th><a href=\"https://www.rismosch.com/archive?sort={$dateSortLink}\" style=\"color: var(--pico-8-blue);\">Last modified{$dateSortChar}</a></th>
                        <th><a href=\"https://www.rismosch.com/archive?sort={$sizeSortLink}\" style=\"color: var(--pico-8-blue);\">Size{$sizeSortChar}</a></th>
                    </tr>";

            foreach ($outdated_files as $file)
            {
                $name = $file["name"];
                $date = $file["date"];
                $size = $file["size"];

                $download_link = "https://www.rismosch.com/{$name}";
                $formatted_name = basename($name);
                $formatted_date = date("M jS, Y", $date);
                $formatted_time = date("G:i:s", $date);
                $formatted_timestamp = "{$formatted_date}<br>{$formatted_time}";

                $size_kb = intdiv($size, 1000);
                $size_mb = intdiv($size_kb, 1000);
                $size_mb_dezimals = $size_kb % 1000;

                if ($size_mb_dezimals == 0)
                    $formatted_size = "{$size_mb} MB";
                elseif ($size_mb_dezimals < 10)
                    $formatted_size = "{$size_mb}.00{$size_mb_dezimals} MB";
                elseif ($size_mb_dezimals < 100)
                    $formatted_size = "{$size_mb}.0{$size_mb_dezimals} MB";
                else
                    $formatted_size = "{$size_mb}.{$size_mb_dezimals} MB";

                echo "
                    <tr style=\"height: 3em;\">
                        <td style=\"word-break: break-word;\"><a href=\"{$download_link}\">{$formatted_name}</a></td>
                        <td style=\"text-align: right;\">{$formatted_timestamp}</td>
                        <td style=\"text-align: right; white-space: nowrap;\">{$formatted_size}</td>
                    </tr>";
            }

            echo "
                </table>
            ";
            ?>
		</div>
		
		<?php echo_foot(false); ?>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function(event) { 
            var scrollpos = localStorage.getItem('scrollpos');
            if (scrollpos) window.scrollTo(0, scrollpos);
        });

        window.onbeforeunload = function(e) {
            localStorage.setItem('scrollpos', window.scrollY);
        };
    </script>
</body>
</html>
