<?php

function get_source($file)
{
	return "article_previewer\\{$file}";
}

function echo_source($file)
{
	echo "article_previewer\\{$file}";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1" />

<title>Article Previewer</title>

<style>
:root{
	--pico-8-black:         #000000;
	--pico-8-blue:          #1d2b53;
	--pico-8-purple:        #7e2553;
	--pico-8-green:         #008751;
	--pico-8-brown:         #ab5236;
	--pico-8-dark-grey:     #5f574f;
	--pico-8-light-grey:    #c2c3c7;
	--pico-8-white:         #fff1e8;
	--pico-8-red:           #ff004d;
	--pico-8-orange:        #ffa300;
	--pico-8-yellow:        #ffec27;
	--pico-8-lime:          #00e436;
	--pico-8-cyan:          #29adff;
	--pico-8-washed-grey:   #83769c;
	--pico-8-pink:          #ff77a8;
	--pico-8-flesh:         #ffccaa;
}

html {
	overflow-y: scroll;
	font-size: 1.2em;
}

body{
	background-color: var(--pico-8-blue);
	font-family: Arial, sans-serif;
	-webkit-text-size-adjust: none;
	-moz-text-size-adjust: none;
	-ms-text-size-adjust: none;
	text-size-adjust: none;
}

a:link{
	color: var(--pico-8-blue);
}

a:visited{
	color: var(--pico-8-purple);
}

a:active{
	color: var(--pico-8-red);
}

div.background{
	margin: auto;
	padding: 5px;
	background-color: var(--pico-8-black);
}

.pixel_image{
	image-rendering: -moz-crisp-edges;         /* Firefox */
	image-rendering: -o-crisp-edges;           /* Opera */
	image-rendering: -webkit-optimize-contrast;/* Webkit (non-standard naming) */
	image-rendering: pixelated;
	-ms-interpolation-mode: nearest-neighbor;  /* IE (non-standard property) */
}

tr.row_empty td{
	height: 0.6em !important;
}

tr.row_devider td{
	border-top-width: 5px;
	border-top-style: dashed;
	border-top-color: var(--pico-8-white);
}

button,
a.button{
	display: inline-block;
	font-family: Arial, sans-serif;
	font-size: 1em;
	text-align: center;
	background-color: var(--pico-8-cyan);
	color: var(--pico-8-white);
	border: none;
	cursor: pointer;
	
	min-width: 20px;
	padding: 10px;
}

button:active,
a.button:active{
	background-color: var(--pico-8-red);
}

a.button:link,
a.button:visited{
	text-decoration: none;
	color: var(--pico-8-white);
}

a.button_inactive,
a.button_inactive:active{
	background-color: var(--pico-8-light-grey);
	color: var(--pico-8-black);
	cursor: default;
}

.invisible{
	display: none;
}

div.content{
	width: auto;
	background-color: var(--pico-8-light-grey);
	border-style: solid;
	border-color: var(--pico-8-white);
	
	border-width: 5px;
}

@media only screen and (min-width:1046px)
{
	div.background{
		width: 1000px;
	}
}

@media only screen and (max-width: 1045px)
{
	div.background{
		width: auto;
	}
}

@media only screen and (min-width: 481px)
{
	div.content{
		padding-top: 10px;
		padding-left: 50px;
		padding-right: 50px;
		padding-bottom: 20px;
	}
	
	.desktop{
		display: block;
	}
	
	.mobile{
		display: none;
	}
}

@media only screen and (max-width: 680px)
{
	div.content{
		padding-top: 0px;
		padding-left: 5px;
		padding-right: 5px;
		padding-bottom: 20px;
	}
	
	.desktop{
		display: none;
	}
	
	.mobile{
		display: block;
	}
}

</style>
</head>
<body>
	<div class="background">
		<img style="display:block; margin: auto;" src="https://www.rismosch.com/assets/meta_image_x5.png">
		<div class="content" id="content">
			<h1>Some Title</h1>
			<p>Category &#183; Date</p>
			<?php
				$content = "article_previewer/content.php";
				if(file_exists($content))
				{
					include $content;
				}
				else
				{
					echo "<h1>:(</h1><p>Could not find file:<br>\"{$content}\"</p>";
				}
			?>
			<div style="border: 1px solid var(--pico-8-white); display: block; width: 100%; height: 500px;">
				<p style="margin: auto;"> Comments</p>
			</div>
		</div>
	</div>
</body>
</html>
