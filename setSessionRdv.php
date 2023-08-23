<?php
session_start();

$_SESSION['id_rdv'] = 0;
if($_POST['isActive'] == 1)
  $_SESSION['id_rdv'] = @$_POST['id_rdv'];
?>