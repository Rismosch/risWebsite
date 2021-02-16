<?php

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
					If you have any questions, reply to this email or contact me here: <a href=\"https://www.rismosch.com/contact\" style=\"color: #29adff;\">CONTACT</a> If you don't want to receive further newsletters, unsubscribe here: <a href=\"https://www.rismosch.com/newsletter_delete?id={$id}\" style=\"color: #29adff;\">UNSUBSCRIBE</a>
				</p>
			</td>
		</tr>
	</table>
</body>
	";
}

?>