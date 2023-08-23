<?php
include 'functions/functions.php';

$query = $mysqli->prepare("DELETE FROM dne_areas WHERE id = ?");
$query->bind_param("i",$_POST['id']);
$query->execute();?>