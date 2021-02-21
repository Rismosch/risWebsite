<?php

include 'php/head.php';

$safe_characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';

if(isset($_GET["id"]))
{
	$email_id_is_safe = true;
	
	$unsafe_email_id = $_GET["id"];
	$unsafe_email_id_chars = str_split($unsafe_email_id);
	$count = 0;
	foreach($unsafe_email_id_chars as $unsafe_email_id_char)
	{
		if (empty($unsafe_email_id_char))
			continue;
		
		if(strpos($safe_characters, $unsafe_email_id_char) !== false)
			continue;
		
		$email_id_is_safe = false;
		break;
	}
	
	if($email_id_is_safe)
		$safe_email_id = $unsafe_email_id;
	else
		$safe_email_id = '+';
}
else
	$safe_email_id = '+';

?>

	<title>Unsubscribe</title>
	<meta name="description" content="Unsubscribe from rismosch.com">

	<meta name="robots" content="noindex">

	<meta property="og:title" content="Unsubscribe" />
	<meta property="og:type" content="website" />
	<meta property="og:url" content="https://www.rismosch.com/privacy" />
	<meta property="og:image" content="https://www.rismosch.com/assets/meta_image_x10.png" />

	<meta name="author" content="Simon Sutoris">

</head>
<body>
	<div class="background">
		<?php include 'php/banner.php'; ?>
		
		<?php include 'php/selector.php'; ?>
		
		<div class="content" id="content">
			<h1>Newsletter</h1>
			<?php
				$success = "<p style=\"color: var(--pico-8-green);\">Successfully unsubscribed &#10003;</p><p>I deleted your email from my database!</p>";
				$error = "<p style=\"color: var(--pico-8-red);\">Could not unsubscribe &#10007;</p><p>Please try again later or contact me <a href=\"https://www.rismosch.com/contact\">here</a>.</p>";
				
				$databaseConnection = mysqli_connect($dbHost, $dbDeleteUserName, $dbDeletePassword, $dbName);
				if($databaseConnection)
				{
					$result = mysqli_query($databaseConnection,"DELETE FROM Emails WHERE id='{$safe_email_id}'");
					
					if($result)
					{
						echo $success;
					}
					else
					{
						echo $error; //mysqli_error($databaseConnection)
					}
				}
				else
				{
					echo $error;
				}
			?>
		</div>
		
		<?php include 'php/foot.php'; ?>
	</div>
</body>
</html>