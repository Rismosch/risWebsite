<?php

include 'php/head.php';

$safe_characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';

if(isset($_GET["id"]))
{
	$email_id_is_safe = true;
	
	$unsafe_email_id = $_GET["id"];
	$unsafe_email_id_chars = str_split($unsafe_email_id);
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

	<title>Confirm Email</title>
	<meta name="description" content="Confirm your email on rismosch.com">

	<meta name="robots" content="noindex">

	<meta property="og:title" content="Confirm Email" />
	<meta property="og:type" content="website" />
	<meta property="og:url" content="https://www.rismosch.com/newsletter_confirm" />
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
				$success = "<p style=\"color: var(--pico-8-green);\">Success &#10003;</p><p>Your email was confirmed!</p>";
				$error = "<p style=\"color: var(--pico-8-red);\">Could not confrim email &#10007;</p><p>Please try again later or contact me <a href=\"https://www.rismosch.com/contact\">here</a>.</p>";
				
				$databaseConnection = mysqli_connect($dbHost, $dbInsertUserName, $dbInsertPassword, $dbName);
				if($databaseConnection)
				{
					$result = mysqli_query($databaseConnection, "UPDATE Emails SET confirmed=true, timestamp=CURRENT_TIMESTAMP WHERE id='{$safe_email_id}'");
					
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