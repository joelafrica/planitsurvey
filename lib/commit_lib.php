<!--<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">


</head>
<body>
-->
<?php

function validate_user_survey ($survey_id, $posted_values) {

	$questions_resultset = query_questions ($survey_id);
	while($question_row = mysqli_fetch_assoc( $questions_resultset )){
		if ($question_row['mandatory_flag'] == "Y") {		
			
			$v_selections = (!empty($posted_values['qid_'.$question_row['question_id']]) ? $posted_values['qid_'.$question_row['question_id']] : null);
			if (empty($v_selections) && $question_row['mandatory_flag'] == "Y"){
				$errors[$question_row['question_id']] = "This question is required";
			
			} else {
				//handle text controls
				while (list($v_key, $v_value) = each($v_selections)) {
					if (strpos($v_key, 'txt') === false) {
						//$errors[$question_row['question_id']] = "";
					
						break;
					} else {
						$v_value = trim($v_value);
					
						if ((empty($v_value)) && ($question_row['mandatory_flag'] == "Y")) {
							$errors[$question_row['question_id']] = "This question is required";
						
							break;
						}
					}
				}
			}
			
		} //end of only validate if mandatory = Y
	} //end of while
	return $errors;
}

function save_user_survey ($user_id, $survey_id, $posted_values) {

	try {

	$questions_resultset = query_questions ($survey_id);

	while($question_row = mysqli_fetch_assoc( $questions_resultset )){
	
		//get user answers if any
		$user_answers = (isset($posted_values['qid_'.$question_row['question_id']]) ? $posted_values['qid_'.$question_row['question_id']] : null);
	
		//get list of user answers option ids if any
		$selection_list  = "";
	
		if (empty($user_answers)) {
			$selection_list = "0";
		} else {
			while( list( $field, $value ) = each( $user_answers )) {
					$control_type = trim(substr($field,0,3));
					//note:  can't make switch to work???
					if ($control_type == "txt" || $control_type == "chk"){
						$selection_list .= substr($field, 4).",";
					} else {
						$selection_list .= $value.",";
					}    
			}
			$selection_list = substr($selection_list, 0, strlen($selection_list)-1);     	
		}
		//echo $selection_list."<br>";
	
		//clean database of any answers not in the new user selection list - prevents unchanged selections to be deleted unnecessarily
		delete_user_answers ($user_id, $survey_id, $question_row['question_id'], $selection_list);

		//update or insert any changes posted by user
		if (isset($posted_values['qid_'.$question_row['question_id']])){
			$new_selections = $posted_values['qid_'.$question_row['question_id']];
		
			while( list( $field, $value ) = each( $new_selections )) {
			
				$control_type = trim(substr($field,0,3));
				switch ($control_type){
					case "txt": 
								$answer_exists = check_answer_exist ($user_id, $survey_id, $question_row['question_id'], substr($field, 4));
								if ($answer_exists) {
								//if user_id, survey_id, question_id, option_id exist in user_answers, then execute update statement
									update_user_answers ($user_id, $survey_id, $question_row['question_id'], substr($field, 4), $value);
								} else {
								//else execute insert statement with answer_text populated with &value
									insert_user_answers ($user_id, $survey_id, $question_row['question_id'], substr($field, 4), $value);
								}
							
								break;
					case "chk":
								$answer_exists = check_answer_exist ($user_id, $survey_id, $question_row['question_id'], substr($field, 4));
								//if user_id, survey_id, question_id, option_id exist in user_answers, then do nothing
								if (!($answer_exists)) {
									insert_user_answers ($user_id, $survey_id, $question_row['question_id'], substr($field, 4), null);
								}
							
								break;
					case "rad":
								$answer_exists = check_answer_exist ($user_id, $survey_id, $question_row['question_id'], $value);
								if (!($answer_exists)) {
									insert_user_answers ($user_id, $survey_id, $question_row['question_id'], $value, null);
								}
							
								break;
							
				}
			}
		}

	} //end of looping questions
		
		$save_result ['success'] = true;
		return $save_result;
		
/*		switch ($posted_values['save']){
			case "Save":
						echo "Survey saved successfully.  You can return anytime to complete the survey!";
						break;
			case "Submit":
						echo "Survey successfully submitted.  Thank you!";
						break;
		}
*/	

	} catch (Exception $e) {
		
		$save_result ['success'] = false;
		return $save_result;
		
/*		echo "There was a problem submitting your request: ",  $e->getMessage(), "\n";
		echo "Please try again later.";
*/
	 
	} //end of try catch

} //end of function
?>

</body>
</html>