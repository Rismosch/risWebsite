<?php include 'php/head.php'; ?>

	<title>Rismosch</title>
	<meta name="robots" content="noindex">

	<meta name="author" content="Simon Sutoris">

</head>
<body>
	<div class="background">
		<?php include 'php/banner.php'; ?>
		
		<?php include 'php/selector.php'; ?>
		
		<div class="content" id="content">
			
			<br><h2>Subscribe to my Newsletter</h2>
			
			<form id="newsletter-form" onsubmit="return SubmitNewsletter()">
				<input name="email" class="newsletter_input" id="newsletter_emailfield" type="text">
				<button
					class="g-recaptcha"
					type="submit"
					data-sitekey="<?php echo $reCAPTCHA_web_key;?>"
					data-callback='onRecaptchaSuccess'
				>
				Subscribe
				</button>
				<p id="display_error">test</p>
			</form>
		</div>
		
		<?php $uses_captcha = true; include 'php/foot.php'; ?>
	</div>
	
</body>
<script>
	
	const constraints = {
		email: {
			presence: { allowEmpty: false },
			email: true
		}
	}
	
	const form = document.getElementById("newsletter-form");
	const display_error = document.getElementById("display_error");
	
	/*form.addEventListener('submit', function(event) {
		Console.Log("Subscribe pressed");
		event.preventDefault();
		
		const formValues = {
			email: form.elements.email.value
		}
		
		const errors = validate(formValues, constraints);
		
		if(errors) {
			event.preventDefault();
			
			if(errors.email)
				display_error.innerHTML = errors.email[0];
			else
				display_error.innerHTML = "";
		}
	},false);*/
	
	function onRecaptchaSuccess () {
		return new Promise(function(resolve, reject)
		{
			console.log("Recaptcha");
			
			const formValues = {
				email: form.elements.email.value
			}
			
			const errors = validate(formValues, constraints);
			
			if(errors) {
				event.preventDefault();
				
				if(errors.email)
					display_error.innerHTML = errors.email[0];
				else
					display_error.innerHTML = "";
			}
			else
			{
				console.log("submit!");
				//document.getElementById('newsletter-form').submit();
			}
			
			resolve();
		});
	}
</script>
</html>
