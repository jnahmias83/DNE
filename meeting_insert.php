<?php
include 'functions/functions.php';

$image1_name = '';
$image2_name = '';

if($_POST['id_chapter'] == 0 ||($_POST['id_task_type'] == 2 && $_POST['id_rdv'] == 0)|| empty($_POST['subject']) ||
   ($_POST['id_area'] == 0 && $_POST['area_name'] == '') || $_POST['id_task'] == 0 || $_POST['id_responsible'] == 0 ||
   $_POST['id_pass_on'] == 0) {
	echo "empty";
}
else {
	$id_area = @$_POST['id_area'];
    
	$blank = ' ';
	$id_progress_status = @$_POST['id_progress_status'];
	if($_POST['id_progress_status'] == 0 && $_POST['progress_status_name'] == '') {
		$query = "SELECT * FROM dne_progress_status WHERE name = ? AND id_project = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('si',$blank,$_POST['id_project']);   
	    $query->execute();
		$query->store_result();
		$query = fetch_unique($query);
		
		$id_progress_status = $query->id;
	}
	
	$white = '#ffffff';
	$black = '#000000';
	
	if(@$_POST['area_name'] != '') {
		$query = "SELECT * FROM dne_areas WHERE name = ? AND id_project = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('si',$_POST['area_name'],$_POST['id_project']);   
	    $query->execute();
		$query->store_result();
		$num_rows = $query->num_rows;
			
		if($num_rows > 0) {
			$area = fetch_unique($query);
		    $id_area = $area->id;
		}
		else {
		    $query = "INSERT INTO dne_areas (id_project,name) VALUES(?,?)";
			$query = $mysqli->prepare($query);
			$query->bind_param('is',$_POST['id_project'],$_POST['area_name']);   
			$query->execute();
			$id_area = $query->insert_id;
		}
	}
	if(@$_POST['progress_status_name'] != '') {
		$query = "SELECT * FROM dne_progress_status WHERE name = ? AND id_project = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('si',$_POST['progress_status_name'],$_POST['id_project']);   
	    $query->execute();
		$query->store_result();
		$num_rows = $query->num_rows;
			
		if($num_rows > 0) {
			$progress_status = fetch_unique($query);
		    $id_progress_status = $progress_status->id;
		}
		else {
		    $query = "INSERT INTO dne_progress_status (id_project,name,color,bgcolor) VALUES(?,?,?,?)";
			$query = $mysqli->prepare($query);
			$query->bind_param('isss',$_POST['id_project'],$_POST['progress_status_name'],$white,$black);   
			$query->execute();
			$id_progress_status = $query->insert_id;
			$is_appears = 1;
		}
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
	
	if($_POST['id'] == 0) {
	    if($_POST['id_progress_status'] != 0) {
		   $query = "SELECT * FROM dne_progress_status WHERE id = ?";
		   $query = $mysqli->prepare($query);
		   $query->bind_param('i',$_POST['id_progress_status']);   
		   $query->execute();
		   $query->store_result();
		   $query = fetch_unique($query);
		   if(@$query->name == 'ארכיון')
			 $is_appears = 0;
	    }
		
		if(isset($_FILES['image1']['name'])) {
			$image1_name = $_FILES['image1']['name'];
			$imageUploadPath = 'uploads/'.$image1_name;
		    $fileType = pathinfo($imageUploadPath, PATHINFO_EXTENSION); 
	 
			$allowTypes = array('jpg','png','jpeg','gif'); 
			if(in_array($fileType, $allowTypes)){ 
				$imageTemp = $_FILES["image1"]["tmp_name"]; 
			    $compressedImage = compressImage($imageTemp, $imageUploadPath, 75); 
			}
	    }
	
		if(isset($_FILES['image2']['name'])) {
			$image2_name = $_FILES['image2']['name'];
			$imageUploadPath = 'uploads/'.$image2_name;
		    $fileType = pathinfo($imageUploadPath, PATHINFO_EXTENSION); 
	 
			$allowTypes = array('jpg','png','jpeg','gif'); 
			if(in_array($fileType, $allowTypes)){ 
				$imageTemp = $_FILES["image2"]["tmp_name"]; 
			    $compressedImage = compressImage($imageTemp, $imageUploadPath, 75); 
			}
	    }
		
		$query = "INSERT INTO dne_meetings (id_project,id_chapter,id_task_type,id_rdv,subject,id_area,description,
		         id_task,id_responsible,id_pass_on,task_creation_date,destination_date,id_progress_status,
				 is_appears,is_change_row_style,image1,is_appears_img1,image2,is_appears_img2,updated_date) 
				 VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$query = $mysqli->prepare($query);
		$query->bind_param('iiiisisiiissiiisisis',$_POST['id_project'],$_POST['id_chapter'],$_POST['id_task_type'],
		                   $_POST['id_rdv'],$_POST['subject'],$id_area,$_POST['description'],$_POST['id_task'],
						   $_POST['id_responsible'],$_POST['id_pass_on'],$task_creation_date,
						   $_POST['destination_date'],$id_progress_status,$is_appears,$is_change_row_style,$image1_name,
						   $_POST['is_appears_img1'],$image2_name,$_POST['is_appears_img2'],date("Y-m-d"));
		$query->execute();
		echo "inserted";	
	}
	else if($_POST['id'] > 0) {
		if(isset($_FILES['image1']['name'])) {
			$image1_name = $_FILES['image1']['name'];
			$imageUploadPath = 'uploads/'.$image1_name;
		    $fileType = pathinfo($imageUploadPath, PATHINFO_EXTENSION); 
	 
			$allowTypes = array('jpg','png','jpeg','gif'); 
			if(in_array($fileType, $allowTypes)){ 
				$imageTemp = $_FILES["image1"]["tmp_name"]; 
			    $compressedImage = compressImage($imageTemp, $imageUploadPath, 75); 
			}
			
			$query = "UPDATE dne_meetings SET image1 = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('si',$image1_name,$_POST['id']);	
			$query->execute();
	    }
		
		if(isset($_FILES['image2']['name'])) {
			$image2_name = $_FILES['image2']['name'];
			$imageUploadPath = 'uploads/'.$image2_name;
		    $fileType = pathinfo($imageUploadPath, PATHINFO_EXTENSION); 
	 
			$allowTypes = array('jpg','png','jpeg','gif'); 
			if(in_array($fileType, $allowTypes)){ 
				$imageTemp = $_FILES["image2"]["tmp_name"]; 
			    $compressedImage = compressImage($imageTemp, $imageUploadPath, 75); 
			}
			
			$query = "UPDATE dne_meetings SET image2 = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('si',$image2_name,$_POST['id']);	
			$query->execute();
	    }
				
		if($ps_name == 'בביצוע') {
		   $query = "UPDATE dne_meetings SET status_updated_date = ?,status_in_ex_updated_date = ? WHERE id = ?";
		   $query = $mysqli->prepare($query);
		   $query->bind_param('ssi',date('Y-m-d'),date('Y-m-d'),$_POST['id']);	
		   $query->execute();
		}
		
		else if($ps_name == 'איחור') {
		   $query = "UPDATE dne_meetings SET status_updated_date = ?,status_late_updated_date = ? WHERE id = ?";
		   $query = $mysqli->prepare($query);
		   $query->bind_param('ssi',date('Y-m-d'),date('Y-m-d'),$_POST['id']);
		   $query->execute();
		}
		
	    else if($ps_name == 'בוצע/נמסר') {
			$task_creation_date = date('Y-m-d');
			$is_change_row_style = 1;
			
			$query = "UPDATE dne_meetings SET status_updated_date = ?,status_finished_updated_date = ? WHERE id = ?";
		    $query = $mysqli->prepare($query);
		    $query->bind_param('ssi',date('Y-m-d'),date('Y-m-d'),$_POST['id']);	
		    $query->execute();
	    }
		
		else if($ps_name == 'Hold') {
		   $query = "UPDATE dne_meetings SET status_updated_date = ?,status_hold_updated_date = ? WHERE id = ?";
		   $query = $mysqli->prepare($query);
		   $query->bind_param('ssi',date('Y-m-d'),date('Y-m-d'),$_POST['id']);	
		   $query->execute();
		}
		
		else if($ps_name == 'ארכיון') {
			$is_appears = 0;
			$query = "UPDATE dne_meetings SET status_updated_date = ?,status_archived_updated_date = ? WHERE id = ?";
		    $query = $mysqli->prepare($query);
		    $query->bind_param('ssi',date('Y-m-d'),date('Y-m-d'),$_POST['id']);	
		    $query->execute();
		}
		
		else if($ps_name == 'הנחיה/החלטה') {
		   $query = "UPDATE dne_meetings SET status_updated_date = ?,status_decision_updated_date = ? WHERE id = ?";
		   $query = $mysqli->prepare($query);
		   $query->bind_param('ssi',date('Y-m-d'),date('Y-m-d'),$_POST['id']);	
		   $query->execute();
		}
		
		$query = $mysqli->prepare("SELECT * FROM dne_meetings WHERE id = ?");
		$query->bind_param("i",$_POST['id']);
		$query->execute();
		$query->store_result();
		$meeting = fetch_unique($query);
		
	    if(($meeting->subject != $_POST['subject']) || ($meeting->id_area != $id_area) || ($meeting->description != $_POST['description']) || ($meeting->id_task != $id_task) ||
		  ($meeting->id_responsible != $_POST['id_responsible']) || ($meeting->id_pass_on != $_POST['id_pass_on']) || ($meeting->task_creation_date != $task_creation_date))
		{		
		    $query = "UPDATE dne_meetings SET updated_date = ? WHERE id = ?";
		    $query = $mysqli->prepare($query);
		    $query->bind_param('si',date('Y-m-d'),$_POST['id']);	
		    $query->execute();
		}
		
		$query = "UPDATE dne_meetings SET id_chapter = ?,id_task_type = ?,id_rdv = ?,subject = ?,id_area = ?,description = ?,
		          id_task = ?,id_responsible = ?,id_pass_on = ?,task_creation_date = ?,destination_date = ?,
				  id_progress_status = ?,is_appears = ?,is_change_row_style = ?,is_appears_img1 = ?,
				  is_appears_img2 = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('iiisisiiissiiiiii',$_POST['id_chapter'],$_POST['id_task_type'],$_POST['id_rdv'],$_POST['subject'],$id_area,$_POST['description'],
		                   $_POST['id_task'],$_POST['id_responsible'],$_POST['id_pass_on'],$task_creation_date,
						   $_POST['destination_date'],$id_progress_status,$is_appears,$is_change_row_style,
						   $_POST['is_appears_img1'],$_POST['is_appears_img2'],$_POST['id']);	
		$query->execute();
		echo 'updated';
	}
}
?>