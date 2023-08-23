<?php
session_start();
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');	

$query = $mysqli->prepare("SELECT id,name FROM dne_responsibles WHERE id_projects_suppliers = ? ORDER BY name");
$query->bind_param("i",$_POST['id_projects_suppliers']);
$query->execute();
$query->store_result();
$query_num_rows = $query->num_rows;
$query = fetch($query);

$_SESSION['id_projects_suppliers'] = $_POST['id_projects_suppliers'];

echo "<br/>";
if($query_num_rows > 0) {
	echo "<div style='font-size:16px;'>אחראיים:</div>";

   foreach ($query as $item) {
	  echo "<input type='checkbox' id='responsibles' value=\"".htmlspecialchars($item->id)."\" checked />&nbsp;<span style='font-size:16px;'>".$item->name."</span><br/>";
   }
}
?>