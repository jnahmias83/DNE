<?php 
include 'functions/functions.php';

$query = $mysqli->prepare("SELECT * FROM dne_global_tasks");
$query->execute();
$query->store_result();
$global_tasks = fetch($query);

foreach($global_tasks as $item) {
	$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id_project = ? AND name = ?");
    $query->bind_param("is",$_POST['id_project'],$item->name);
	$query->execute();
    $query->store_result();
    $num_task = $query->num_rows;
	
	if($num_task == 0) {
		$query = "INSERT INTO dne_tasks (id_project,name,color,bgcolor) VALUES(?,?,?,?)";
	    $query = $mysqli->prepare($query);
	    $query->bind_param('isss',$_POST['id_project'],$item->name,$item->color,$item->bgcolor);  
	    $query->execute();
	}
}
?>