<?php
	// Start the session
	session_start();
?>

<!DOCTYPE html>
<head>
	<link rel="stylesheet" type="text/css" href="main.css">
    <title>Login Planit Survey</title>
</head>
<body>

<!-- CONTENT WRAPPER -->
<div id="wrapper">
	<!-- START HEADER CONTAINER -->
	<div id="header-container">
		<div id="survey_label">	
				<h3 class="title_planit">Planit Software Testing - Perth, WA</h3>
				<h2 class="title_survey">Technical Questionnaire</h2>
				<div id="logo"><img src="images/planit.png" alt="planit" height="90" width="110"></div>
		</div>
	</div>

	<!-- START MAIN CONTAINER -->
	<div id="main">
		
		<?php
			if (empty($_SESSION["userID"])) {
				$confirmation_action['action'] = "InactiveSession";
			} else {
				$confirmation_action = unserialize(urldecode($_GET['action']));
			}
			switch ($confirmation_action['action']){
				case "Save":
						echo "Survey saved successfully.  You can return anytime to complete the survey!";
						break;
				case "Submit":
						echo "Survey successfully submitted.  Thank you!";
						break;
				case "InactiveSession":
						echo "No active session.  click link below to enter token and start survey.";
						break;
			}
			
		
		// Unset all of the session variables.
		$_SESSION = array();

		// If it's desired to kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', 0,
				$params["path"], $params["domain"],
				$params["secure"], $params["httponly"]
			);
		}
		
		
		session_destroy();	
		?>
		
	</div>

	<!-- START FOOTER CONTAINER -->
	<div id="footer-container"> 
		<p class="contact_us" align="right">Suite 1.5/9 Havelock Street, West Perth, WA 6005   <br>   T: 08 6109 3800   <br>   http://www.planit.net.au </p>
	</div>
</div>


</body>
</html>
