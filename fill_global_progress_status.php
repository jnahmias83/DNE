<?php 
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT * FROM dne_global_progress_status");
$query->execute();
$query->store_result();
$global_progress_status = fetch($query);

foreach($global_progress_status as $item) {
	$query = $mysqli->prepare("SELECT * FROM dne_progress_status WHERE id_project = ? AND name = ?");
    $query->bind_param("is",$_POST['id_project'],$item->name);
	$query->execute();
    $query->store_result();
    $num_progress_status = $query->num_rows;
	
	if($num_progress_status == 0) {
		$query = "INSERT INTO dne_progress_status (id_project,name,color,bgcolor) VALUES(?,?,?,?)";
	    $query = $mysqli->prepare($query);
	    $query->bind_param('isss',$_POST['id_project'],$item->name,$item->color,$item->bgcolor);  
	    $query->execute();
	}
}
?>