<?php
include 'functions/functions.php';

if(empty($_POST['request_name'])) {
	echo "empty";
}
else {
	if($_POST['id'] == 0) {
		$query = "INSERT INTO dne_custom_reports (id_project,request_name,title,subtitle,sql_str,is_images,is_colors,lang,
		          columns_list) 
	              VALUES(?,?,?,?,?,?,?,?,?)";
        $query = $mysqli->prepare($query);
        $query->bind_param('issssiiss',$_POST['id_project'],$_POST['request_name'],$_POST['title'],$_POST['subtitle'],
		                   $_POST['sql_str'],$_POST['is_images'],$_POST['is_colors'],$_POST['lang'],$_POST['columns_list']);   
        $query->execute();
        echo "inserted";
	}
}
?>