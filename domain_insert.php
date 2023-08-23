<?php
include 'functions/functions.php';
if(empty($_POST['domain_name']) || empty($_POST['domain_name_he']) || empty($_POST['domain_nickname'])) {
	echo "empty";
}
else {
	if($_POST['id'] == 0) {
		$query = "INSERT INTO dne_sup_field_of_work (sup_type,name,name_he,nickname,color,bgcolor) VALUES(?,?,?,?,?,?)";
        $query = $mysqli->prepare($query);
        $query->bind_param('ssssss',$_POST['sup_type'],$_POST['domain_name'],$_POST['domain_name_he'],
		        $_POST['domain_nickname'],$_POST['domain_color'],$_POST['domain_bgcolor']);   
        $query->execute();
        echo "inserted";
	}
	else if($_POST['id'] > 0) {
		$query = "UPDATE dne_sup_field_of_work SET name = ?,name_he = ?,nickname = ?,color = ?,bgcolor = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('sssssi',$_POST['domain_name'],$_POST['domain_name_he'],$_POST['domain_nickname'],
		                   $_POST['domain_color'],$_POST['domain_bgcolor'],$_POST['id']);
		$query->execute();
		echo 'updated';
	}
}
?>