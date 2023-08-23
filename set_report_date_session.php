<?php
session_start();
echo $_SESSION['report_date'] = @$_POST['report_date'];
?>