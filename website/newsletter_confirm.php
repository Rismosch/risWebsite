<?php

include 'secret/secret.php';
include 'php/util.php';

$postIsEmpty = empty($_POST);
$captchaSuccess = false;
if(!$postIsEmpty)
{
	if (isset($_POST['g-recaptcha-response']))
	{
		$recaptchaResponse = $_POST['g-recaptcha-response'];
		$recaptchaUrl = "https://www.google.com/recaptcha/api/siteverify?secret={$reCAPTCHA_secret_key}&response={$recaptchaResponse}";
		$verify = json_decode(file_get_contents($recaptchaUrl));
		
		$captchaSuccess = $verify->success;
	}
	
	$safe_characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';
	$email_id_is_safe = true;
	
	$unsafe_email_id = $_POST["id"];
	$unsafe_email_id_chars = str_split($unsafe_email_id);
	foreach($unsafe_email_id_chars as $unsafe_email_id_char)
	{
		if(empty($unsafe_email_id_char))
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

echo_head();

?>
	<script src="https://www.google.com/recaptcha/api.js"></script>
	
	<title>Confirm Email</title>
	<meta name="description" content="Confirm your email on rismosch.com">

	<meta name="robots" content="noindex">

	<meta property="og:title" content="Confirm Email" />
	<meta property="og:type" content="website" />
	<meta property="og:url" content="https://www.rismosch.com/newsletter_confirm" />
	<meta property="og:image" content="https://www.rismosch.com/assets/meta_image_x10.png" />

	<meta name="author" content="Simon Sutoris">

</head>
<body <?php if($postIsEmpty) echo 'onload="ReloadPageWithPost()"';?>>
	<div class="background">
		<?php
			echo_banner();
			echo_selector(-1);
		?>
		
		<div class="content" id="content">
			<h1>Newsletter</h1>
			
			<img id="loading_animation" class="loading_animation pixel_image <?php if($captchaSuccess) echo 'invisible';?>" src="assets/icon_8bit/loading.gif">
			
			<form action="newsletter_confirm" method="POST" id="contact-form">
				<input type="hidden" name="id" value="<?php if(isset($_GET["id"])) echo $_GET["id"]; else echo "+";?>">
				<div class="g-recaptcha"
					data-sitekey="<?php echo $reCAPTCHA_web_key;?>"
					data-callback="onRecaptchaSuccess"
					data-size="invisible">
				</div>
			</form>
			
			<?php
				if($captchaSuccess)
				{
					$success = "<p style=\"color: var(--pico-8-green);\">Success &#10003;</p><p>Your email was confirmed!</p>";
					$error = "<p style=\"color: var(--pico-8-red);\">Error &#10007;</p><p>Could not confirm email. Please try again later or contact me <a href=\"https://www.rismosch.com/contact\">here</a>.</p>";
					
					$databaseConnection = mysqli_connect($dbHost, $dbInsertUserName, $dbInsertPassword, $dbName);
					if($databaseConnection)
					{
						$emailNotExpiredResult = mysqli_query($databaseConnection, "SELECT * FROM Emails WHERE id='{$safe_email_id}' AND timestamp >= NOW() - INTERVAL 1 DAY");
						$emailNotExpiredCount = mysqli_num_rows($emailNotExpiredResult);
						
						if($emailNotExpiredCount > 0)
						{
							$result = mysqli_query($databaseConnection, "UPDATE Emails SET confirmed=true WHERE id='{$safe_email_id}'");
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
							echo "<p style=\"color: var(--pico-8-red);\">Error &#10007;</p><p>Confirmation link expired or email was not found.</p>";
						}
					}
					else
					{
						echo $error;
					}
				}
				else if(!$postIsEmpty)
				{
					header("Location: https://www.rismosch.com/");
					die();
				}
			?>
		</div>
		
		<?php echo_foot(true); ?>
	</div>
	<script>
		function ReloadPageWithPost()
		{
			grecaptcha.execute();
		}
		
		function onRecaptchaSuccess ()
		{
			return new Promise(function(resolve, reject){
				
				document.getElementById('contact-form').submit();
				
				resolve;
			});
		}
	</script>
</body>
</html>