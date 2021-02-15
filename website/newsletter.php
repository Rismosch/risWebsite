<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

include 'php/head.php';

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
	else if (!filter_var(utf8_decode($emailSanitized), FILTER_VALIDATE_EMAIL)) {
		$errorEmail = 'Email is invalid';
		$errors[] = $errorEmail;
	}
	else if (strlen($emailSanitized) > 320){
		$errorEmail = "Email is too long";
		$errors[] = $errorEmail;
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
		$dbInsertConnection = mysqli_connect($dbHost, $dbInsertUserName, $dbInsertPassword, $dbName);
		if($dbInsertConnection)
		{
			// check if email exists
			$result = mysqli_query($dbInsertConnection,"SELECT id, confirmed FROM Emails WHERE email = '{$emailSanitized}'");
			$numRows = mysqli_num_rows($result);
			if ($numRows > 0)
			{
				$row = mysqli_fetch_assoc($result);
				$id = $row['id'];
				$confirmed = $row['confirmed'];
				
				if ($confirmed == 1)
				{
					$errorContact = "Something went wrong. Please try again later.";
					$errors[] = $errorContact;
				}
				else
				{
					$result = mysqli_query($dbInsertConnection,"UPDATE Emails SET timestamp=CURRENT_TIMESTAMP WHERE id='{$id}'");
					if(!$result)
					{
						$errorContact = "Something went wrong. Please try again later.";
						$errors[] = $errorContact;
					}
				}
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
					
					$result = mysqli_query($dbInsertConnection,"SELECT COUNT(id) as COUNT FROM Emails WHERE id = '{$id}'");
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
				
				$result = mysqli_query($dbInsertConnection,"INSERT INTO Emails(id, email) VALUES ('{$id}','{$emailSanitized}')");
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
				$mail->setFrom($contactEmail, 'Rismosch');
				$mail->addAddress($emailSanitized);
				
				// Content
				$mail->isHTML(true);
				$mail->Subject = 'Rismosch: confirm your email';
				$mail->Body    = "Hello World";
				//$mail->AltBody = "Name: {$nameSanitized}\nEmail: {$emailSanitized}\nMessage:\n\n{$messageSanitized}";
				
				$mail->send();
				
				// Success
				$subscribe_successful = true;
				
			} catch (Exception $e) {
				//$errorContact = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
				$errorContact = "Something went wrong. Please try again later.";
			}
		}
	}
}

?>
	
	<title>Newsletter</title>
	<meta name="description" content="Contact Simon Sutoris">
	<meta name="keywords" content="newsletter, subscribe, email">

	<meta name="robots" content="all">

	<meta property="og:title" content="Newsletter" />
	<meta property="og:type" content="website" />
	<meta property="og:url" content="https://www.rismosch.com/newsletter" />
	<meta property="og:image" content="https://www.rismosch.com/assets/meta_image_x20.png" />

	<meta name="author" content="Simon Sutoris">

</head>
<body>
	<div class="background">
		<?php include 'php/banner.php'; ?>
		
		<?php include 'php/selector.php'; ?>
		
		<div class="content" id="content">
			
			<h1>Newsletter</h1>
			
			<div style="display:<?php if($subscribe_successful) echo "block"; else echo "none"?>;">
			
				<p style="color: var(--pico-8-green);">Success &#10003;</p>
				<p>Thanks for signing up! I have sent you a message to confirm your email.</p>
				
			</div>
			
			<div style="display:<?php if($subscribe_successful) echo "none"; else echo "block"?>;">
				
				<p>Subscribe to my newsletter to be notified when I upload a new blogpost or project :)</p>
				<p>Newsletter can be unsubscribed at any time.</p>
				<br>
				
				<form action="newsletter" method="POST" id="contact-form">
					
					<p>Email <span class="contact_error" id="display_error"><?php if(isset($errorEmail)) echo $errorEmail; ?></span></p>
					<input name="email" class="contact_input" id="newsletter_emailfield" type="text" value = "<?php if(isset($emailUnsafe)) echo $emailUnsafe; ?>">
					
					<p><span class="contact_error"><?php if(isset($errorContact)) echo $errorContact; ?></span></p>
					<p>
						<button
							class="g-recaptcha"
							id="submit_button"
							type="submit"
							data-sitekey="<?php echo $reCAPTCHA_web_key;?>"
							data-callback='onRecaptchaSuccess'
						>
						Subscribe
						</button>
					</p>
					
					<img id="loading_animation" class="loading_animation pixel_image invisible" src="assets/icon_8bit/loading.gif">
				</form>
			</div>
		</div>
		
		<?php $uses_captcha = true; include 'php/foot.php'; ?>
	</div>
	
	<script>
		function onRecaptchaSuccess () {
			return new Promise(function(resolve, reject){
				
				document.getElementById('loading_animation').classList.remove('invisible');
				document.getElementById('submit_button').classList.add('invisible');
				
				document.getElementById('contact-form').submit();
				
				resolve;
			});
		}
	</script>
</body>
</html>