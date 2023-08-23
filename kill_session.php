<?php
session_start();

if(isset($_SESSION['id_user']) || isset($_SESSION['report_date']) || isset($_SESSION['sql']) || isset($_SESSION['id_responsibles_part'])){
	session_unset();
}
?>