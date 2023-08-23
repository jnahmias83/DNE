<?php
include 'functions/functions.php';

if(empty($_POST['name'])) {
	echo "empty";
}
else {
	if($_POST['id_project'] != 0) {
		if($_POST['id'] == 0) {
			$query = $mysqli->prepare("SELECT name FROM dne_progress_status WHERE name = ? AND id_project = ?");
			$query->bind_param("si",$_POST['name'],$_POST['id_project']);
			$query->execute(); 
			$query->store_result();
		
		    if($query->num_rows == 0) {
				$query = "INSERT INTO dne_progress_status (id_project,name,color,bgcolor) 
						  VALUES(?,?,?,?)";
				$query = $mysqli->prepare($query);
				$query->bind_param('isss',$_POST['id_project'],$_POST['name'],$_POST['color'],$_POST['bgcolor']);   
				$query->execute();
				echo "inserted";
	        }
			else echo 'exists';
		}
		else if($_POST['id'] > 0) {
			$query = "UPDATE dne_progress_status SET name = ?,color = ?,bgcolor = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('sssi',$_POST['name'],$_POST['color'],$_POST['bgcolor'],$_POST['id']);	
			$query->execute();
			echo 'updated';
		}
	}
	else {
		if($_POST['id'] == 0) {
			$query = $mysqli->prepare("SELECT name FROM dne_global_progress_status WHERE name = ?");
			$query->bind_param("s",$_POST['name']);
			$query->execute(); 
			$query->store_result();
		
			if($query->num_rows == 0) {			
				$query = "INSERT INTO dne_global_progress_status (name,color,bgcolor) 
						  VALUES(?,?,?)";
				$query = $mysqli->prepare($query);
				$query->bind_param('sss',$_POST['name'],$_POST['color'],$_POST['bgcolor']);   
				$query->execute();
				echo "inserted";
			}
			else echo 'exists';
		}
		else if($_POST['id'] > 0) {
			$query = "UPDATE dne_global_progress_status SET name = ?,color = ?,bgcolor = ? WHERE id = ?";
			$query = $mysqli->prepare($query);
			$query->bind_param('sssi',$_POST['name'],$_POST['color'],$_POST['bgcolor'],$_POST['id']);	
			$query->execute();
			echo 'updated';
		}
	}
}
?>