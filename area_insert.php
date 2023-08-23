<?php
include 'functions/functions.php';

if(empty($_POST['name'])) {
	echo "empty";
}
else {
	if($_POST['id'] == 0) {
		$query = "INSERT INTO dne_areas (id_project,name) 
	              VALUES(?,?)";
        $query = $mysqli->prepare($query);
        $query->bind_param('is',$_POST['id_project'],$_POST['name']);   
        $query->execute();
        echo "inserted";
	}
	else if($_POST['id'] > 0) {
		$query = "UPDATE dne_areas SET name = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('si',$_POST['name'],$_POST['id']);	
		$query->execute();
		echo 'updated';
	}
}
?>