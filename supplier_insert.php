<?php
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

if(empty($_POST['supplier_name']) || empty($_POST['supplier_email_office'])) {
	echo "empty";
}
else {
    if($_POST['id'] == 0) {
		$query = "INSERT INTO dne_suppliers (name,name_he,nickname,type,id_field_of_work,phone,mobile,email_office,bank_account_owner,bank_name,
				  bank_branche,bank_account_number,swift,iban,created_date) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$query = $mysqli->prepare($query);
		$query->bind_param('ssssissssssssss',$_POST['supplier_name'],$_POST['supplier_name_he'],$_POST['supplier_nickname'],$_POST['supplier_type'],$_POST['id_field_of_work'],
						   $_POST['supplier_phone'],$_POST['supplier_mobile'],$_POST['supplier_email_office'],$_POST['supplier_account_owner'],$_POST['supplier_bank_name'],
						   $_POST['supplier_bank_branche'],$_POST['supplier_account_number'],$_POST['supplier_swift'],$_POST['supplier_iban'],date('Y-m-d'));				    
		$query->execute();
		echo "inserted";
    }
	else if($_POST['id'] > 0) {
		$query = "UPDATE dne_suppliers SET name = ?,name_he = ?,nickname = ?,id_field_of_work = ?,phone = ?,mobile = ?,email_office = ?,bank_account_owner = ?,bank_name = ?,
				  bank_branche = ?,bank_account_number = ?,swift = ?,iban = ?,updated_date = ?  WHERE id = ?";
		$query = $mysqli->prepare($query);
		$query->bind_param('sssissssssssssi',$_POST['supplier_name'],$_POST['supplier_name_he'],$_POST['supplier_nickname'],$_POST['id_field_of_work'],$_POST['supplier_phone'],
						   $_POST['supplier_mobile'],$_POST['supplier_email_office'],$_POST['supplier_account_owner'],$_POST['supplier_bank_name'],
						   $_POST['supplier_bank_branche'],$_POST['supplier_account_number'],$_POST['supplier_swift'],$_POST['supplier_iban'],date("Y-m-d"),$_POST['id']);
		$query->execute();
		echo 'updated';
	}
}
?>