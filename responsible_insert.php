<?php
include 'functions/functions.php';

if(empty($_POST['name'])) {
	echo "empty";
}
else {
	if($_POST['id'] == 0) {
		$query = "INSERT INTO dne_responsibles (id_project,id_projects_suppliers,name,color,bgcolor,email) 
	              VALUES(?,?,?,?,?,?)";
        $query = $mysqli->prepare($query);
        $query->bind_param('iissss',$_POST['id_project'],$_POST['id_projects_suppliers'],$_POST['name'],$_POST['color'],$_POST['bgcolor'],$_POST['email']);   
        $query->execute();
        echo "inserted";
	}
	else if($_POST['id'] > 0) {
		$query = "UPDATE dne_responsibles SET id_projects_suppliers = ?,name = ?,color = ?,bgcolor = ?,email = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('issssi',$_POST['id_projects_suppliers'],$_POST['name'],$_POST['color'],$_POST['bgcolor'],$_POST['email'],$_POST['id']);	
		$query->execute();
		echo 'updated';
	}
}
?>