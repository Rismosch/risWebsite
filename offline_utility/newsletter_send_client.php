<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

include 'secret.php';

require 'PHPMailer/Exception.php';
require 'PHPMailer/OAuth.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/POP3.php';
require 'PHPMailer/SMTP.php';

$subject = '';
$message = '';
$messageHtml = '';
if(!empty($_POST))
{
	$subject = $_POST['subject'];
	$message = $_POST['message'];
	
	$message_chars = str_split($message);
	foreach($message_chars as $message_char)
	{
		if(empty($message_char))
			continue;
		
		if(strpos("\n", $message_char) !== false)
			$messageHtml .= '<br>';
		else
			$messageHtml .= $message_char;
	}
}

function generateEmail($id, $content)
{
	return "
<body style=\"background-color: #1d2b53; font-family: Arial, sans-serif; font-size: 1.2em;\">
	<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#000000\">
		<tr>
			<td align=\"center\">
				<a href=\"https://www.rismosch.com/\"><img style=\"display:block;\" align=\"center\" src=\"https://www.rismosch.com/assets/meta_image_x5.png\"></a>
			</td>
		</tr>
		<tr>
			<td bgcolor=\"#c2c3c7\" style=\"border: 5px solid #fff1e8; padding: 10px;\">
				<p style=\"color: #000000;\">
					{$content}
				</p>
			</td>
		</tr>
		<tr>
			<td tyle=\"padding: 10px;\">
				<p style=\"color: #fff1e8\">
					If you have any questions, reply to this email or contact me here: <a href=\"https://www.rismosch.com/contact\" style=\"color: #29adff;\">CONTACT</a>
					<br><br>
					If you don't want to receive further messages, unsubscribe here: <a href=\"https://www.rismosch.com/newsletter_delete?id={$id}\" style=\"color: #29adff;\">UNSUBSCRIBE</a>
				</p>
			</td>
		</tr>
	</table>
</body>
	";
}

function generateAltEmail($id, $content)
{
	return "{$content}\n\n\nIf you have any questions, reply to this email or contact me here: https://www.rismosch.com/contact\n\nIf you don't want to receive further messages, unsubscribe here: https://www.rismosch.com/newsletter_delete?id={$id}";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title>Newsletter Send Client</title>
</head>
<body>
	<h1>Newsletter Send Client</h1>
	
	<form action="newsletter_send_client.php" method="POST">
		<table>
			<tr>
				<td>Subject</td><td><input name="subject" type="text" value="<?php echo $subject; ?>"></td>
			</tr>
		</table>
		
		<p>Message</p>
		<textarea name="message" rows="10" cols="35"><?php echo $message; ?></textarea>
		
		<br><br>
		
		<input type="submit">
	</form>
	
	<br>
	
	<?php
		
		if(!empty($_POST))
		{
			
			if(empty($message) || empty($subject))
				echo "<b>Error</b>: subject or message is empty";
			else
			{
				$databaseConnection = mysqli_connect($dbHost, $dbUsername, $dbPassword, $dbName);
				if($databaseConnection)
				{
					echo "connected to database<br>";
					
					$result = mysqli_query($databaseConnection,"SELECT * FROM Emails");
					$numRows = mysqli_num_rows($result);
					
					echo "{$numRows} emails found<br>";
					
					if ($numRows > 0)
					{
						echo "sending emails...<br>";
						
						$sendingEmailsStart = microtime(true);
						
						$emailCount = 0;
						while($row = mysqli_fetch_assoc($result))
						{
							$rowId = $row['id'];
							$rowEmail = $row['email'];
							$rowConfirmed = $row['confirmed'];
							$rowTimestamp = $row['timestamp'];
							
							$mail = new PHPMailer(true);
							try {
								// Server settings
								$mail->SMTPDebug = SMTP::DEBUG_SERVER;
								$mail->isSMTP();
								$mail->Host = $smtpHost;
								$mail->SMTPAuth = false;
								$mail->Username   = $smtpUsername;
								$mail->Password   = $smtpPassword;
								$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
								$mail->Port       = $smtpPort;
								
								// Recipients
								$mail->setFrom('contact@rismosch.com', 'Rismosch');
								$mail->addAddress($rowEmail);
								
								// Content
								$mail->isHTML(true);
								$mail->Subject = $subject;
								$mail->Body    = generateEmail($rowId,$messageHtml);
								$mail->AltBody = generateAltEmail($rowId,$message);
								
								$mail->send();
								
								// Success
								$subscribe_successful = true;
								
							} catch (Exception $e) {
								$errorContact = "Error while sending Email {$emailCount}/{$numRows}: {$mail->ErrorInfo}";
							}
							
							++$emailCount;
						}
						echo "{$emailCount}/{$numRows} emails send<br>";
						
						$sendingEmailsTime = microtime(true) - $sendingEmailsStart;
						
						$utime = sprintf('%.4f', $sendingEmailsTime);
						$raw_time = DateTime::createFromFormat('U.u', $utime);
						$today = $raw_time->format('H:i:s.u');
						
						echo "elapsed time: {$today}<br>";
					}
					
					echo "<b>DONE.</b>";
				}
				else
					echo "<b>Error</b>: Could not connect to database";
			}
		}
		
	?>
</body>
</html>