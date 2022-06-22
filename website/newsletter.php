<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

include 'secret/secret.php';
include 'php/util.php';

require '3rd_party_libraries/PHPMailer/Exception.php';
require '3rd_party_libraries/PHPMailer/OAuth.php';
require '3rd_party_libraries/PHPMailer/PHPMailer.php';
require '3rd_party_libraries/PHPMailer/POP3.php';
require '3rd_party_libraries/PHPMailer/SMTP.php';

$errors = [];

$subscribe_successful = false;

if(!empty($_POST))
{
	$emailUnsafe = $_POST['email'];
	$emailSanitized = filter_var($emailUnsafe,FILTER_SANITIZE_EMAIL);
	if (empty($emailSanitized)) {
		$errorEmail = 'Email is empty';
		$errors[] = $errorEmail;
	}
	else if (!filter_var($emailSanitized, FILTER_VALIDATE_EMAIL)) {
		$errorEmail = 'Email is invalid';
		$errors[] = $errorEmail;
	}
	else if (strlen($emailSanitized) > 320){
		$errorEmail = "Email is too long";
		$errors[] = $errorEmail;
	}
	
	if (!array_key_exists('privacy_accepted', $_POST) || $_POST['privacy_accepted'] != true)
	{
		$errorPrivacy = 'Privay Policy must be read and accepted';
		$errors[] = $errorPrivacy;
	}
	
	
	
	if (isset($_POST['g-recaptcha-response']))
	{
		$recaptchaResponse = $_POST['g-recaptcha-response'];
		$recaptchaUrl = "https://www.google.com/recaptcha/api/siteverify?secret={$reCAPTCHA_secret_key}&response={$recaptchaResponse}";
		$verify = json_decode(file_get_contents($recaptchaUrl));
		
		if (!$verify->success) {
			$errors[] = 'Recaptcha failed';
		}
	}
	else
	{
		$errors[] = 'Recaptcha failed';
	}
	
	if (empty($errors))
	{
		$databaseConnection = mysqli_connect($dbHost, $dbInsertUserName, $dbInsertPassword, $dbName);
		if($databaseConnection)
		{
			// check if email exists
			$result = mysqli_query($databaseConnection,"SELECT id FROM Emails WHERE email = '{$emailSanitized}'");
			$numRows = mysqli_num_rows($result);
			if ($numRows > 0)
			{
				$row = mysqli_fetch_assoc($result);
				$id = $row['id'];
				mysqli_query($databaseConnection,"UPDATE Emails SET timestamp=CURRENT_TIMESTAMP WHERE id = '{$id}'");
			}
			else
			{
				// generate id
				do
				{
					$id = '';
					
					$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';
					for ($i = 0; $i < 32; ++$i){
						$id .= $characters[rand(0, strlen($characters) - 1)];
					}
					
					$result = mysqli_query($databaseConnection,"SELECT COUNT(id) as COUNT FROM Emails WHERE id = '{$id}'");
					$numRows = mysqli_num_rows($result);
					if ($numRows > 0)
					{
						$row = mysqli_fetch_assoc($result);
						$matchingIdCount = $row['COUNT'];
					}
					else
					{
						$errorContact = "Something went wrong. Please try again later.";
						$errors[] = $errorContact;
						$matchingIdCount = 0;
					}
					
				} while($matchingIdCount > 0);
				
				$result = mysqli_query($databaseConnection,"INSERT INTO Emails(id, email) VALUES ('{$id}','{$emailSanitized}')");
				if(!$result)
				{
					$errorContact = "Something went wrong. Please try again later.";
					$errors[] = $errorContact;
				}
			}
		}
		else
		{
			$errorContact = "Something went wrong. Please try again later.";
			$errors[] = $errorContact;
		}
		
		if (empty($errors))
		{
			$mail = new PHPMailer(true);
			
			try {
				// Server settings
				//$mail->SMTPDebug = SMTP::DEBUG_SERVER;
				$mail->isSMTP();
				$mail->Host = 'localhost';
				$mail->SMTPAuth = false;
				$mail->SMTPAutoTLS = false;
				$mail->Port = 25;
				
				// Recipients
				$mail->setFrom($noreplyEmail, 'noreply');
				$mail->AddReplyTo($noreplyEmail,'noreply');
				$mail->addAddress($emailSanitized);
				
				// Content
				$mail->isHTML(false);
				$mail->Subject = 'Rismosch: confirm your email';
				$mail->Body    = "Hello!\n\nThanks for signing up! Please go ahead and confirm your email by clicking the link below:\n\nhttps://www.rismosch.com/newsletter_confirm?id={$id}\n\nThis link will expire in 24 hours. If the email is not confirmed in 24 hours, you will be automatically unsubscribed from my newsletter.\n\nSincerely,\nSimon Sutoris\n\n---\n\nDO NOT REPLY TO THIS EMAIL.\nIf you have any questions, contact me here: https://www.rismosch.com/contact";
				
				$mail->send();
				
				// Success
				$subscribe_successful = true;
				
			} catch (Exception $e) {
				//$errorContact = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
				$errorContact = "Something went wrong. Please try again later.";
			}
		}
		
		if ($subscribe_successful) {
			header("Location: https://www.rismosch.com/newsletter_success");
			exit();
		}
	}
}

echo_head();

?>
	<script src="https://www.google.com/recaptcha/api.js"></script>
	
	<title>Newsletter</title>
	<meta name="description" content="Contact Simon Sutoris">
	<meta name="keywords" content="newsletter, subscribe, email">

	<meta name="robots" content="all">

	<meta property="og:title" content="Newsletter" />
	<meta property="og:type" content="website" />
	<meta property="og:url" content="https://www.rismosch.com/newsletter" />
	<meta property="og:image" content="https://www.rismosch.com/assets/meta_image_x10.png" />

	<meta name="author" content="Simon Sutoris">

</head>
<body>
	<div class="background">
		<?php
			echo_banner();
			echo_selector(-1);
		?>
		
		<div class="content" id="content">
			
			<h1>Newsletter</h1>

			<!--<p>&#128119; Under Construction... &#128296;</p>-->

			<div style="display:none" id="javascript_content">

				<div>
					
					<p>
						Subscribe to my newsletter to be notified when I upload a new blogpost or project.<br>
						Newsletter can be unsubscribed at any time.
					</p>
					<form action="newsletter" method="POST" id="contact-form">
						
						<br>
						<label>Email <span class="contact_error" id="display_error"><?php if(isset($errorEmail)) echo $errorEmail; ?></span><br>
						<input name="email" class="contact_input" id="newsletter_emailfield" type="text" value = "<?php if(isset($emailUnsafe)) echo $emailUnsafe; ?>"><br>
						</label>

						<label><span class="contact_error" id="display_error_subject"><?php if(isset($errorPrivacy)) echo $errorPrivacy; ?></span><br>
						<input name="privacy_accepted" style="width: 2em; height: 2em; position: relative; top: 4px;" type="checkbox">
						I have read and accept the <a href="https://www.rismosch.com/privacy" target="_blank" rel="noopener noreferrer">Privacy Policy</a>
						</label>
						
						<p><span class="contact_error"><?php if(isset($errorContact)) echo $errorContact; ?></span></p>
						<button
							class="g-recaptcha"
							id="submit_button"
							type="submit"
							data-sitekey="<?php echo $reCAPTCHA_web_key;?>"
							data-callback='onRecaptchaSuccess'
						>
						Subscribe
						</button>
						
						<img id="loading_animation" class="loading_animation pixel_image invisible" src="assets/icon_8bit/loading.gif">
					</form>
				</div>

			</div>

			<noscript>
				<p>Unfortunately, this page only works if JavaScript is enabled :(</p>
			</noscript>
			
		</div>
		
		<?php echo_foot(true); ?>
	</div>
	
	<script>
		
		document.getElementById("javascript_content").style.display = "block";
		
		function onRecaptchaSuccess () {
			return new Promise(function(resolve, reject){
				
				document.getElementById('loading_animation').classList.remove('invisible');
				document.getElementById('submit_button').style.display = "none";
				
				document.getElementById('contact-form').submit();
				
				resolve;
			});
		}
		
	</script>
</body>
</html>
