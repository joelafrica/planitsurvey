<?php

function connect_db (){
	$server = "localhost";
	$username = "root";
	$password = "";
	$dbname = "planit_survey_all";
	
	$connection = mysqli_connect($server, $username, $password, $dbname) or die(mysql_error());
	return $connection;
}

function close_db ($connection) {
	mysqli_close($connection);
}

function query_user_details ($tokenid) {
	$connection = connect_db ();
	
	$sql = "SELECT token_id, survey_id, user_id 
			FROM token
			WHERE token_id =".$tokenid;
			
	$query_results = mysqli_query($connection, $sql) or die(mysql_error());
	
	return $query_results;
	
	close_db ($connection);
}

function query_questions ($surveyid) {
	$connection = connect_db ();
	
	$sql = "SELECT * 
			FROM questions 
			WHERE survey_id=".$surveyid;
			
	$query_results = mysqli_query($connection, $sql) or die(mysql_error());
	
	return $query_results;
	
	close_db ($connection);
}

function query_options_user_answers ($user_id, $question_id) {
	$connection = connect_db ();
	
	$sql = "SELECT 	options.option_id,
    				options.question_id as opt_question_id,
        			options.label,
        			options.control_type,
        			user_answers.option_id as ans_option_id,
        			user_answers.answer_text 
    		FROM options
    		LEFT JOIN user_answers 
        	ON options.option_id = user_answers.option_id
        	AND user_answers.user_id=".$user_id
            ." WHERE options.question_id=".$question_id
            ." ORDER BY options.question_id,options.option_id"; 
            
    $query_results = mysqli_query($connection, $sql) or die(mysql_error());
	
	return $query_results;
	
	close_db ($connection); 
}

function delete_user_answers ($user_id, $survey_id, $question_id, $option_id_list) {
	$connection = connect_db ();
	
	$sql = "DELETE FROM user_answers 
        		WHERE user_id=".$user_id
        		." AND survey_id=".$survey_id
        		." AND question_id=".$question_id
        		." AND option_id IN (SELECT option_id
        							FROM options
        							WHERE option_id NOT IN (".$option_id_list.")
        							AND question_id=".$question_id
        							." AND control_type <> 'text')";
	
	mysqli_query($connection, $sql) or die(mysql_error());
	
	close_db ($connection);
}

function update_user_answers ($user_id, $survey_id, $question_id, $option_id, $value) {
	$connection = connect_db ();
	
	$sql = "UPDATE user_answers
   			SET answer_text='".$value
   			."' WHERE user_id=".$user_id
   			." AND survey_id=".$survey_id
   			." AND question_id=".$question_id
   			." AND option_id=".$option_id;
	
	mysqli_query($connection, $sql) or die(mysql_error());
	
	close_db ($connection);
}

function insert_user_answers ($user_id, $survey_id, $question_id, $option_id, $value) {
	$connection = connect_db ();
	
	$sql = "INSERT INTO user_answers
   			VALUES (".$user_id.",".$survey_id.",".$question_id.",".$option_id.",'".$value."',null)";
	
	mysqli_query($connection, $sql) or die(mysql_error());
	
	close_db ($connection);
}


function check_answer_exist ($user_id, $survey_id, $question_id, $option_id) {
	$connection = connect_db ();
	
	$sql = "SELECT *
   			FROM user_answers
   			WHERE user_id=".$user_id
   			." AND survey_id=".$survey_id
   			." AND question_id=".$question_id
   			." AND option_id=".$option_id;
   			
   	$query_results = mysqli_query($connection, $sql) or die(mysql_error());
	
	$answer_exists = (mysqli_num_rows($query_results)>0 ? true : false);
	
	return $answer_exists;
	
	close_db ($connection);
}
?>