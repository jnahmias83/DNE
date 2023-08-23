<?php
include 'functions/functions.php';

if(empty($_POST['name'])) {
	echo "empty";
}
else {
	if($_POST['id'] == 0) {
		$query = $mysqli->prepare("SELECT max(id_display) AS max_id FROM dne_chapters WHERE id_project = ?");
        $query->bind_param("i",$_POST['id_project']);
        $query->execute(); 
        $query->store_result();
        $chapter = fetch_unique($query);
		$max_id = $chapter->max_id;
		$max_id_plus_one = $max_id+1;
		
		$query = "INSERT INTO dne_chapters (id_project,id_display,name) 
	              VALUES(?,?,?)";
        $query = $mysqli->prepare($query);
        $query->bind_param('iis',$_POST['id_project'],$max_id_plus_one,$_POST['name']); 
        $query->execute();
        echo "inserted";
	}
	else if($_POST['id'] > 0) {
		$query = "UPDATE dne_chapters SET name = ? WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('si',$_POST['name'],$_POST['id']);	
		$query->execute();
		echo 'updated';
	}
}
?>