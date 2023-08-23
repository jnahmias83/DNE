<?php
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

if(empty($_POST['project_name']) || empty($_POST['project_name_he']) || empty($_POST['project_nickname'])) {
	  echo "empty"; 
}
else {
    if($_POST['id'] == 0) {
		$type = 'E';
		$id_field_of_work = 113;
		$query = "INSERT INTO dne_suppliers (name_he,nickname_he,type,id_field_of_work) VALUES(?,?,?,?)";
		$query = $mysqli->prepare($query);
		$query->bind_param('sssi',$_POST['project_initiator_name'],$_POST['project_initiator_nickname'],$type,$id_field_of_work);
		$query->execute();
		$id_supplier = $query->insert_id;	
		
		$query = "INSERT INTO dne_projects (name,name_he,nickname,email_client_1,email_client_2,lang,created_date) VALUES(?,?,?,?,?,?,?)";
	    $query = $mysqli->prepare($query);
	    $query->bind_param('sssssss',$_POST['project_name'],$_POST['project_name_he'],strtoupper($_POST['project_nickname']),$_POST['project_email_client_1'],
		                    $_POST['project_email_client_2'],$_POST['project_lang'],date('Y-m-d'));
		$query->execute();
	    $id_project = $query->insert_id;
		
	    $query = "INSERT INTO dne_projects_suppliers (id_project,id_supplier) VALUES(?,?)";
	    $query = $mysqli->prepare($query);
		$query->bind_param('ii',$id_project,$id_supplier);
		$query->execute();
		
		$query = "INSERT INTO dne_progress_status (id_project,name) VALUES(?,?)";
		$query = $mysqli->prepare($query);
		$progress_status_1 = ' ';
		$query->bind_param('is',$id_project,$progress_status_1);   
		$query->execute();
		
		$query = "INSERT INTO dne_progress_status (id_project,name) VALUES(?,?)";
		$query = $mysqli->prepare($query);
		$progress_status_2 = 'בביצוע';
		$query->bind_param('is',$id_project,$progress_status_2);   
		$query->execute();
		
		$query = "INSERT INTO dne_progress_status (id_project,name,bgcolor) VALUES(?,?,?)";
		$query = $mysqli->prepare($query);
		$progress_status_3 = 'איחור';
		$bgColor = '#f04337';
		$query->bind_param('iss',$id_project,$progress_status_3,$bgColor);   
		$query->execute();
		
		$query = "INSERT INTO dne_progress_status (id_project,name) VALUES(?,?)";
		$query = $mysqli->prepare($query);
		$progress_status_4 = 'בוצע/נמסר';
		$query->bind_param('is',$id_project,$progress_status_4);   
		$query->execute();
		
	    $query = "INSERT INTO dne_progress_status (id_project,name) VALUES(?,?)";
		$query = $mysqli->prepare($query);
		$progress_status_5 = 'Hold';
		$query->bind_param('is',$id_project,$progress_status_5);  
		$query->execute();
		
		$query = "INSERT INTO dne_progress_status (id_project,name) VALUES(?,?)";
		$query = $mysqli->prepare($query);
		$progress_status_6 = 'ארכיון';
		$query->bind_param('is',$id_project,$progress_status_6);  
		$query->execute();
		
		$query = "INSERT INTO dne_progress_status (id_project,name) VALUES(?,?)";
		$query = $mysqli->prepare($query);
		$progress_status_7 = 'הנחיה/החלטה';
		$query->bind_param('is',$id_project,$progress_status_7);  
		$query->execute();
		
		$first = 1;
		$query = "INSERT INTO dne_tasks (id_display,id_project,name) VALUES(?,?,?)";
		$query = $mysqli->prepare($query);
		$task_kind_1 = 'ניהול';
		$query->bind_param('iis',$first,$id_project,$task_kind_1);  
		$query->execute();
		
		$second = 2;
		$query = "INSERT INTO dne_tasks (id_display,id_project,name) VALUES(?,?,?)";
		$query = $mysqli->prepare($query);
		$task_kind_2 = 'תכנון';
		$query->bind_param('iis',$second,$id_project,$task_kind_2);  
		$query->execute();
		
		$third = 3;
		$query = "INSERT INTO dne_tasks (id_display,id_project,name) VALUES(?,?,?)";
		$query = $mysqli->prepare($query);
		$task_kind_3 = 'בקשה/שאילתה';
		$query->bind_param('iis',$third,$id_project,$task_kind_3);  
		$query->execute();
		
		$fourth = 4;
		$query = "INSERT INTO dne_tasks (id_display,id_project,name) VALUES(?,?,?)";
		$query = $mysqli->prepare($query);
		$task_kind_4 = 'ביצוע';
	    $query->bind_param('iis',$fourth,$id_project,$task_kind_4);  
		$query->execute();
		
		$fitht = 5;
		$query = "INSERT INTO dne_tasks (id_display,id_project,name) VALUES(?,?,?)";
		$query = $mysqli->prepare($query);
		$task_kind_5 = 'בקרת איכות';
		$query->bind_param('iis',$fitht,$id_project,$task_kind_5);  
	    $query->execute();
		
		$sixth = 6;
		$query = "INSERT INTO dne_tasks (id_display,id_project,name) VALUES(?,?,?)";
		$query = $mysqli->prepare($query);
		$task_kind_6 = 'הנחיית ביצוע';
		$query->bind_param('iis',$sixth,$id_project,$task_kind_6);  
		$query->execute();
		
		$seventh = 7;
		$query = "INSERT INTO dne_tasks (id_display,id_project,name) VALUES(?,?,?)";
		$query = $mysqli->prepare($query);
		$task_kind_7 = 'סטטוס ביצוע';
	    $query->bind_param('iis',$seventh,$id_project,$task_kind_7);  
		$query->execute();
		
		$query = "INSERT INTO dne_log_create_pdf (id_project,last_pdf_created_date) VALUES(?,?)";
		$query = $mysqli->prepare($query);
		$query->bind_param('is',$id_project,date('Y-m-d',strtotime("-1 days")));  
	    $query->execute();
		
		echo "inserted";
}
else if($_POST['id'] > 0) {
	   $type = 'E';
	   $id_field_of_work = 113;
	   
	   $query = $mysqli->prepare("SELECT ps.id_supplier AS id_supplier,ps.id FROM dne_projects_suppliers ps
								 LEFT JOIN dne_suppliers s ON s.id = ps.id_supplier
								 WHERE id_project = ? AND s.type = ?");
	   $query->bind_param("is",$_POST['id'],$type);
	   $query->execute();
	   $query->store_result();
	   $project_supplier_num_rows = $query->num_rows;
	   $project_supplier = fetch_unique($query);
	   
	    if($project_supplier_num_rows > 0) {
		   $query = "DELETE FROM dne_suppliers WHERE id = ?";
		   $query = $mysqli->prepare($query);
		   $query->bind_param('i',$project_supplier->id_supplier);
		   $query->execute();
		   
		   $query = "DELETE FROM dne_projects_suppliers WHERE id = ?";
		   $query = $mysqli->prepare($query);
		   $query->bind_param('i',$project_supplier->id);
		   $query->execute();
		}
		
		$query = "INSERT INTO dne_suppliers (name_he,nickname_he,type,id_field_of_work) VALUES(?,?,?,?)";
		$query = $mysqli->prepare($query);
		$query->bind_param('sssi',$_POST['project_initiator_name'],$_POST['project_initiator_nickname'],$type,$id_field_of_work);
		$query->execute();
		$id_supplier = $query->insert_id;
		
		$query = "INSERT INTO dne_projects_suppliers (id_project,id_supplier) values(?,?)";
		$query = $mysqli->prepare($query);
		$query->bind_param('ii',$_POST['id'],$id_supplier);
		$query->execute();
			
		$query = "UPDATE dne_projects SET name = ?,name_he = ?,nickname = ?,email_client_1 = ?,email_client_2 = ?,
		          bgcolor_new_task = ?,is_project_appears = ?,lang = ?,updated_date = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('ssssssissi',$_POST['project_name'],$_POST['project_name_he'],strtoupper($_POST['project_nickname']),
		                   $_POST['project_email_client_1'],$_POST['project_email_client_2'],$_POST['project_bgcolor_new_task'],
						   $_POST['is_project_appears'],$_POST['project_lang'],date("Y-m-d"),$_POST['id']);	
		$query->execute();
		echo 'updated';
    }
}
?>