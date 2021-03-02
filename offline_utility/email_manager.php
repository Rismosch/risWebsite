<?php

include 'secret.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title>Email Manager</title>
</head>
<body>
	<h1>Email Manager</h1>
	
	<form action="email_manager.php" method="POST" id="reload-form">
		<input type="hidden" name="command" value="reload">
		<button type="submit">Reload</button>
	</form>
	
	<form action="email_manager.php" method="POST" id="delete-form">
		<input type="hidden" name="command" value="delete">
	</form>
	
	<?php
	
	$databaseConnection = mysqli_connect($dbHost, $dbUsername, $dbPassword, $dbName);
	if($databaseConnection)
	{
		echo "<p style=\"color:green;\">connected to database</p>";
		
		if(!empty($_POST))
		{
			$command = $_POST['command'];
			
			if($command === "delete")
			{
				$deleteEmailsResult = mysqli_query($databaseConnection,"DELETE FROM Emails WHERE timestamp <= NOW() - INTERVAL 1 DAY AND confirmed=0");
				if($deleteEmailsResult)
					echo "<p style=\"color:green;\">deleted all expired emails</p>";
				else
					echo "<p style=\"color:red;\">error while deleting emails</p>";
			}
		}
		
		$totalEmailsResult = mysqli_query($databaseConnection,"SELECT * FROM Emails ORDER BY timestamp DESC");
		$totalEmailsNumRows = mysqli_num_rows($totalEmailsResult);
		
		$confirmedEmailsResult = mysqli_query($databaseConnection,"SELECT * FROM Emails WHERE confirmed=1");
		$confirmedEmailsNumRows = mysqli_num_rows($confirmedEmailsResult);
		
		if($totalEmailsNumRows != 0)
			$confirmedRatio = ($confirmedEmailsNumRows / $totalEmailsNumRows) * 100;
		else
			$confirmedRatio = "--";
		
		$expiredEmailsResult = mysqli_query($databaseConnection,"SELECT * FROM Emails WHERE confirmed=0 AND timestamp <= NOW() - INTERVAL 1 DAY");
		$expiredEmailsNumRows = mysqli_num_rows($expiredEmailsResult);
		
		echo "
		<table>
			<tr>
				<td>total emails:</td><td>{$totalEmailsNumRows}</td>
			</tr>
			<tr>
				<td>confirmed:</td><td>{$confirmedEmailsNumRows} &#8594; {$confirmedRatio}%</td>
			</tr>
			<tr "; if($expiredEmailsNumRows > 0) echo "style=\"background-color:red;\""; echo ">
				<td>expired:</td><td>{$expiredEmailsNumRows}</td><td><button onclick=\"DeleteExpiredEmails()\" "; if($expiredEmailsNumRows <= 0) echo "disabled"; echo ">Delete</button></td>
			</tr>
		</table>
		
		<table>
			<tr>
				<th>id</th><th>email</th><th>confirmed</th><th>timestamp</th>
			</tr>
		";
		
		if($totalEmailsNumRows > 0)
		{
			while($row = mysqli_fetch_assoc($totalEmailsResult))
			{
				echo "
				<tr "; if($row['confirmed'] != 1) echo "style=\"background-color: orange;\""; echo ">
					<td>{$row['id']}</td><td>{$row['email']}</td><td>{$row['confirmed']}</td><td>{$row['timestamp']}</td>
				</tr>
				";
			}
		}
		
		echo "
		</table>
		";
	}
	else
	{
		echo "<p style=\"color:red;\">could not connect to database</p>";
	}
	
	?>
	
	<script>
	
	function DeleteExpiredEmails()
	{
		document.getElementById('delete-form').submit();
	}
	</script>
</body>
</html>