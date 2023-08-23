<?php
include 'functions/functions.php';

if($_POST['table_name'] == 'dne_meetings') {
	$query = $mysqli->prepare("SELECT * FROM dne_meetings WHERE id = ?");
	$query->bind_param("i",$_POST['id']);
	$query->execute();
	$query->store_result();
    $meeting = fetch_unique($query);
	
	$is_appears = 1;
	
	$query = "INSERT INTO dne_meetings (id_project,id_chapter,id_task_type,id_rdv,subject,id_area,description,
	          id_task,id_responsible,id_pass_on,task_creation_date,destination_date,id_progress_status,
			  status_in_ex_updated_date,status_late_updated_date,status_finished_updated_date,status_hold_updated_date,
			  status_archived_updated_date,status_decision_updated_date,status_updated_date,last_pdf_created,
			  last_pdf_created_date,is_appears,is_pdf_appears,is_change_row_style,image1,is_appears_img1,
			  image2,is_appears_img2,updated_date) 
			  VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$query = $mysqli->prepare($query);
	$query->bind_param('iiiisisiiississsssssssiiisisis',$meeting->id_project,$meeting->id_chapter,
	                   $meeting->id_task_type,$meeting->id_rdv,$meeting->subject,$meeting->id_area,
					   $meeting->description,$meeting->id_task,$meeting->id_responsible,$meeting->id_pass_on,
					   $meeting->task_creation_date,$meeting->destination_date,$meeting->id_progress_status,
					   $meeting->status_in_ex_updated_date,$meeting->status_late_updated_date,
					   $meeting->status_finished_updated_date,$meeting->status_hold_updated_date,
					   $meeting->status_archived_updated_date,$meeting->status_decision_updated_date,
					   $meeting->status_updated_date,$meeting->last_pdf_created,$meeting->last_pdf_created_date,
					   $is_appears,$meeting->is_pdf_appears,$meeting->is_change_row_style,$meeting->image1,
					   $meeting->is_appears_img1,$meeting->image2,$meeting->is_appears_img2,date('Y-m-d'));
	$query->execute();
}
?>