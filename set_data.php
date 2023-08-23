<?php 
include 'functions/functions.php';

if($_POST['field'] == "is_pdf_appears") {
	$query = "UPDATE dne_meetings SET is_pdf_appears = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('ii',$_POST['is_pdf_appears'],$_POST['meeting_id']);	
	$query->execute();
}

else if($_POST['field'] == "id_area") {
	$query = "UPDATE dne_meetings SET id_area = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('ii',$_POST['id_area'],$_POST['meeting_id']);	
	$query->execute();
}

else if($_POST['field'] == "id_task") {
	$query = "UPDATE dne_meetings SET id_task = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('ii',$_POST['id_task'],$_POST['meeting_id']);	
	$query->execute();
}

else if($_POST['field'] == "id_responsible") {
	$query = "UPDATE dne_meetings SET id_responsible = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('ii',$_POST['id_responsible'],$_POST['meeting_id']);	
	$query->execute();
}

else if($_POST['field'] == "id_pass_on") {
	$query = "UPDATE dne_meetings SET id_pass_on = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('ii',$_POST['id_pass_on'],$_POST['meeting_id']);	
	$query->execute();
}

else if($_POST['field'] == "task_creation_date") {
	$query = "UPDATE dne_meetings SET task_creation_date = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('si',$_POST['task_creation_date'],$_POST['meeting_id']);	
	$query->execute();
}

else if($_POST['field'] == "destination_date") {
	$query = "UPDATE dne_meetings SET destination_date = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('si',$_POST['destination_date'],$_POST['meeting_id']);	
	$query->execute();
}

else if($_POST['field'] == "id_progress_status") {
	$query = "UPDATE dne_meetings SET id_progress_status = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('ii',$_POST['id_progress_status'],$_POST['meeting_id']);	
	$query->execute();
}

$task_creation_date = @$_POST['task_creation_date'];
$is_change_row_style = 0;
$is_appears = 1;

$query = "SELECT * FROM dne_tasks WHERE id = ?";
$query = $mysqli->prepare($query);
$query->bind_param('i',$_POST['id_task']);   
$query->execute();
$query->store_result();
$query = fetch_unique($query);
$task_name = $query->name;

$query = "SELECT * FROM dne_progress_status WHERE id = ?";
$query = $mysqli->prepare($query);
$query->bind_param('i',$_POST['id_progress_status']);   
$query->execute();
$query->store_result();
$query = fetch_unique($query);
$ps_name = $query->name;

if($task_name == 'הנחיית ביצוע' || $task_name == 'סטטוס ביצוע' || $task_name == 'בקשה/שאילתה' || $task_name == 'בקרת איכות' || $ps_name == 'Hold' || $ps_name == 'הנחיה/החלטה') 
   $is_change_row_style = 1;

if($ps_name == 'בביצוע') {
   $query = "UPDATE dne_meetings SET status_in_ex_updated_date = ? WHERE id = ?";
   $query = $mysqli->prepare($query);
   $query->bind_param('si',date('Y-m-d'),$_POST['meeting_id']);	
   $query->execute();
}
		
else if($ps_name == 'איחור') {
   $query = "UPDATE dne_meetings SET status_late_updated_date = ? WHERE id = ?";
   $query = $mysqli->prepare($query);
   $query->bind_param('si',date('Y-m-d'),$_POST['meeting_id']);	
   $query->execute();
}

else if($ps_name == 'בוצע/נמסר') {
	$task_creation_date = date('Y-m-d');
	$is_change_row_style = 1;
	
	$query = "UPDATE dne_meetings SET status_finished_updated_date = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('si',date('Y-m-d'),$_POST['meeting_id']);	
	$query->execute();
}

else if($ps_name == 'Hold') {
   $query = "UPDATE dne_meetings SET status_hold_updated_date = ? WHERE id = ?";
   $query = $mysqli->prepare($query);
   $query->bind_param('si',date('Y-m-d'),$_POST['meeting_id']);	
   $query->execute();
}

else if($ps_name == 'ארכיון') {
	$is_appears = 0;
	
	$query = "UPDATE dne_meetings SET status_archived_updated_date = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('si',date('Y-m-d'),$_POST['meeting_id']);	
	$query->execute();
}

else if($ps_name == 'הנחיה/החלטה') {
   $query = "UPDATE dne_meetings SET status_decision_updated_date = ? WHERE id = ?";
   $query = $mysqli->prepare($query);
   $query->bind_param('si',date('Y-m-d'),$_POST['meeting_id']);	
   $query->execute();
}

if(@$_POST['field']!= 'destination_date' && @$_POST['field']!= 'id_progress_status') {
	$query = "UPDATE dne_meetings SET updated_date = ? WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('si',date('Y-m-d'),$_POST['meeting_id']);	
	$query->execute();
}

$query = "UPDATE dne_meetings SET is_change_row_style = ?, is_appears = ? WHERE id = ?";
   $query = $mysqli->prepare($query);
   $query->bind_param('iii',$is_change_row_style,$is_appears,$_POST['meeting_id']);	
   $query->execute();
?>