<?php
session_start();

include 'functions/functions.php';

if(empty($_POST['rdv_name']) || strlen($_POST['rdv_persons']) == 0) {
	echo "empty";
}
else {
	if($_POST['id'] == 0) {
		$query = "INSERT INTO dne_rdv (id_project,rdv_name,rdv_persons,rdv_date) 
			      VALUES(?,?,?,?)";
	    $query = $mysqli->prepare($query);
	    $query->bind_param('isss',$_POST['id_project'],$_POST['rdv_name'],$_POST['rdv_persons'],$_POST['rdv_date']);   
	    $query->execute();
		
		$_SESSION['id_rdv'] = $query->insert_id;
	    echo "inserted";
	}
	else {
		$query = "UPDATE dne_rdv SET rdv_name = ?,rdv_persons = ? ,rdv_date = ? WHERE id = ?"; 			   
	    $query = $mysqli->prepare($query);
	    $query->bind_param('sssi',$_POST['rdv_name'],$_POST['rdv_persons'],$_POST['rdv_date'],$_POST['id']);   
	    $query->execute();
	    echo "updated";
	}
}
?>