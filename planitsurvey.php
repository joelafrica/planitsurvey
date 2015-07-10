<?php
	// Start the session
	session_start();
?>

<!DOCTYPE html>
<html>
<head>

<link rel="stylesheet" type="text/css" href="main.css">
<title>Planit Survey Questionnare</title>

<script type="text/javascript">

var formSubmitting = false;
var setFormSubmitting = function() { formSubmitting = true; };

window.onload = function() {
    window.addEventListener("beforeunload", function (e) {
        var confirmationMessage = 'It looks like you have been editing something. ';
        confirmationMessage += 'If you leave before saving, your changes will be lost.';

        if (formSubmitting) {
            return undefined;
        }

        (e || window.event).returnValue = confirmationMessage; //Gecko + IE
        return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
    });
};

</script>

<style>
html, body {
	text-align: center;
	background-color: #DEE4E4;
	margin: 0;
    padding: 0;
    height: 100%;
}
#header {
	width: 900px;
	margin: 0 auto;
	text-align: left;
	background-color: white;
	border-bottom-right-radius: 15px;
	border-bottom-left-radius: 15px;
	border-bottom: 2px solid #DEE4E4;	
	padding: 2px 0px 0px 4px;
	height: 16%;
	min-height: 100px;
	max-height: 100px;
	position: relative;
}
#container {
	width: 900px;
	margin: 0 auto;
	text-align: left;
	background-color: white;
	border-radius: 15px;
	border-radius: 15px;
	border-top: 2px solid #DEE4E4;
	border-bottom: 2px solid #DEE4E4;
	min-height: 70%;
	overflow: auto;
}

#footer {
	width: 900px;
	margin: 0 auto;
	text-align: right;
	background-color: white;
	border-top-right-radius: 15px;
	border-top-left-radius: 15px;
	border-top: 2px solid #DEE4E4;
	padding: 4px 4px 0px 0px;
	height: 13%;	
	position: relative;
}

#header img {
	width: 13%; 
  	height: auto;
  	position: absolute;
  	bottom: 6px;
  	left: 11px;
}

#header .headertext {
	font-family: ProximaNova-Regular, Helvetica, sans-serif;
	font-size: 22px;
	padding-left: 170px; 
	padding-top: 3px;
	color: #4747D1; 
}
#header .headersubtext {
	font-family: ProximaNova-Regular, Helvetica, sans-serif;
	font-size: 18px;
	color: black;
}


#footer img {
	width: 55%; 
  	height: auto;
    position: absolute;
  	top: 20px;
  	right: 20px;
}

#footer .footertext {
	text-align: left;
	line-height:15px;
	padding-top: 10px;
	padding-left: 20px; 
	font-family: ProximaNova-Regular, Helvetica, sans-serif;
	font-size: 12px;
	color: gray;
	text-decoration: none;
}

#footer .footerlink {
	text-decoration: none;
	color: #718EE8;
}




</style>

</head>
<body>

<div id="header">			
		<p class="headertext">Planit Software Testing - Perth, WA </br>
			<span class="headersubtext">Online Survey Portal</span>
		</p>
		<img src="images/planit.png"/>
	</div>
	
	<div id="container">


<form id="survey" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" onsubmit="setFormSubmitting()">
<!--<form id="survey" action="commitsurvey.php" method="post" onsubmit="setFormSubmitting()">-->
<?php

if (empty($_SESSION["userID"])) {
	$commit_results['action'] = "InactiveSession"; 
	$queryurl = urlencode(serialize($commit_results));
	header ("location: confirmation.php?action=".$queryurl);
	exit();
}

	
include './lib/db_lib.php';
include './lib/commit_lib.php';

$survey_id = $_SESSION["surveyID"];
$user_id = $_SESSION["userID"];


$validationSuccess = true;

if (!empty($_POST)) {

	switch ($_POST['save']) {
		case "Save": 
				$commit_results = "";
				$commit_results = save_user_survey ($user_id, $survey_id, $_POST);
				$commit_results['action'] = "Save"; 
				$queryurl = urlencode(serialize($commit_results));
				header ("location: confirmation.php?action=".$queryurl);
				exit();
				break;	
		case "Submit":
				$validation_results = null;
				$validation_results = validate_user_survey ($survey_id, $_POST);
				
				if (empty($validation_results))	{
					$commit_results = save_user_survey ($user_id, $survey_id, $_POST);
					$commit_results['action'] = "Submit";
					$queryurl = urlencode(serialize($commit_results));
					header ("location: confirmation.php?action=".$queryurl);
					exit();
				} else {
					$validationSuccess = false;
				}	
				break;
	} //end of switch POST
} // end of if



$questions = query_questions ($survey_id);
$num_of_questions = mysqli_num_rows($questions);

if ($num_of_questions > 0) {

	for ($x = 1; $x <= $num_of_questions; $x++){
		
		echo "<div class='container'>";
        $question_row = mysqli_fetch_assoc( $questions );
        
        //set mandatory flag
        $mandatory_label = ($question_row['mandatory_flag'] == "Y" ? " <span class='required' > *</span>" : "");
        
        echo "Question ".$x." of ".$num_of_questions.":  ".$question_row['question'].$mandatory_label;
	
		$options_user_answers = query_options_user_answers ($user_id, $question_row['question_id']);
		
		while($options_row = mysqli_fetch_assoc( $options_user_answers )) {
			
			$column_class = ($question_row['option_columns'] > 1 ? "twocolumn" : "onecolumn");
			
			switch ($options_row['control_type']){
				case "text":
						if ((!empty($_POST)) && ($_POST['save'] == "Submit")) {
							if (!empty($_POST['qid_'.$question_row['question_id']]['txt-'.$options_row['option_id']])) {
								$set_value = "value=".$_POST['qid_'.$question_row['question_id']]['txt-'.$options_row['option_id']];
							} else {
								$set_value = "value=''";
							}
						}
						else {
							$set_value = (!empty($options_row['answer_text']) ? "value=".$options_row['answer_text'] : "");
						}
						echo 	"<div class='".$column_class."'>"
        							.$options_row['label']
        							."<input type=".$options_row['control_type']
        								." name=qid_".$options_row['opt_question_id']."[txt-".$options_row['option_id']."]"
        								." id=oid_".$options_row['option_id']." ".$set_value." />
        						</div>";
						break;
						
				case "checkbox":
						if ((!empty($_POST)) && ($_POST['save'] == "Submit")) {
							if (!empty($_POST['qid_'.$question_row['question_id']]['chk-'.$options_row['option_id']])){
								$set_value = "checked";
							} else {
								$set_value = "";
							}
						} else {
							$set_value = (!empty($options_row['ans_option_id']) ? "checked" : "");
						}
												
        				echo 	"<div class='".$column_class."'>"
        							."<input type=".$options_row['control_type']
        							." name=qid_".$options_row['opt_question_id']."[chk-".$options_row['option_id']."]"
        							." id=oid_".$options_row['option_id']
        							." value=".$options_row['option_id']." ".$set_value." >"
        							."<label for=oid_".$options_row['option_id']." >".$options_row['label']."</label>"."</input>
        						</div>";
        				break;		
        			        			
				case "radio":
						if ((!empty($_POST)) && ($_POST['save'] == "Submit")) {
							if (!empty($_POST['qid_'.$question_row['question_id']]['rad'])){
								$post_value = $_POST['qid_'.$question_row['question_id']]['rad'];
								$set_value = ($post_value == $options_row['option_id'] ? "checked" : "");
							} else {
								$set_value = "";
							}
						} else {
							$set_value = (!empty($options_row['ans_option_id']) ? "checked" : "");
						}
						echo 	"<div class='".$column_class."'>"
        							."<input type=".$options_row['control_type']
        							." name=qid_".$options_row['opt_question_id']."[rad]"
        							." id=oid_".$options_row['option_id']
        							." value=".$options_row['option_id']." ".$set_value." >"
        							."<label for=oid_".$options_row['option_id']." >".$options_row['label']."</label>"."</input>
        						</div>";
        				break;	
			} 
			
		} //end of looping options with user answers
		
		//add validation meessage for mandatory fields
		if ((!empty($validation_results[$question_row['question_id']])) &&
			(!empty($_POST)) && ($_POST['save'] == "Submit")) {
				echo "<div class='onecolumn'><span class='error'>"."   ".$validation_results[$question_row['question_id']]."</span></div>";
		}
		
		echo "</div>";
	} //end of looping questions
	
} //end of if $num_of_questions


?>

<div class="commitbuttons">

	<input id='save' type='submit' name='save' value='Save' />

	<input id='submit' type='submit' name='save' value='Submit' />


</div>

</form>

</div>
	
	<div id="footer">
		<p class="footertext">
		Suite 1.5/9 Havelock Street, West Perth, WA 6005 </br> 
		T: 08 6109 3800</br>
		<a href="http://www.planit.net.au" class="footerlink">http://www.planit.net.au<a>
		</p>
		<img src="images/Footer-logos.jpg" />		
	</div>

</body>
</html>