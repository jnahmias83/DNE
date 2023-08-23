<?php
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');	

$query = $mysqli->prepare("SELECT p.name AS name
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  WHERE ps.id = ?");
$query->bind_param("i",$_POST['id_projects_suppliers']);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$pdf_submission_name = '';
$pdf_approval_name = '';
$pdf_payment_name = '';
$pdf_invoice_name = '';

$paid_amount_vat_excluded = 0;
if($_POST['paid_amount'] != 0)
   $paid_amount_vat_excluded = $_POST['paid_amount']/(1+($_POST['vat']/100));

if($_POST['id'] == 0) {
	if(isset($_FILES['pdf_submission']['name'])) {
		$pdf_submission_name = 'pdf_submission_'.$project->name.'_'.str_replace(' ','_',$_FILES['pdf_submission']['name']);
		move_uploaded_file($_FILES['pdf_submission']['tmp_name'],'uploads/'.$pdf_submission_name);
	}
	
	if(isset($_FILES['pdf_approval']['name'])) {
		$pdf_approval_name = 'pdf_approval_'.$project->name.'_'.str_replace(' ','_',$_FILES['pdf_approval']['name']);
		move_uploaded_file($_FILES['pdf_approval']['tmp_name'],'uploads/'.$pdf_approval_name);
	}
	
	if(isset($_FILES['pdf_payment']['name'])) {
		$pdf_payment_name = 'pdf_payment_'.$project->name.'_'.str_replace(' ','_',$_FILES['pdf_payment']['name']);
		move_uploaded_file($_FILES['pdf_payment']['tmp_name'],'uploads/'.$pdf_payment_name);
	}
	
	if(isset($_FILES['pdf_invoice']['name'])) {
		$pdf_invoice_name = 'pdf_invoice_'.$project->name.'_'.str_replace(' ','_',$_FILES['pdf_invoice']['name']);
		move_uploaded_file($_FILES['pdf_invoice']['tmp_name'],'uploads/'.$pdf_invoice_name);
	}
	
	$query = "INSERT INTO dne_payments (id_projects_suppliers,description,pdf_submission,submit_date,submitted_account,pdf_approval,approval_date,approved_amount,pdf_payment,payment_date,paid_amount,
	          paid_amount_vat_excluded,pdf_invoice,invoice_date,vat,created_date) 
			  VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$query = $mysqli->prepare($query);
	$query->bind_param('isssdssdssddssds',$_POST['id_projects_suppliers'],$_POST['description'],$pdf_submission_name,$_POST['submit_date'],$_POST['submitted_account'],$pdf_approval_name,$_POST['approval_date'],
	                   $_POST['approved_amount'],$pdf_payment_name,$_POST['payment_date'],$_POST['paid_amount'],$paid_amount_vat_excluded,$pdf_invoice_name,$_POST['invoice_date'],$_POST['vat'],date('Y-m-d'));   
	$query->execute();
	if($_POST['create_order_from_payment'] == 1) {
	    $approved_amount_vat_excluded = 0;
        if($_POST['approved_amount'] != '')
           $approved_amount_vat_excluded = $_POST['approved_amount']/(1+($_POST['vat']/100));
	    $query = "INSERT INTO dne_orders (id_projects_suppliers,sum_order,pdf_order,vat,signature_date,description,created_date) 
	              VALUES(?,?,?,?,?,?,?)";
        $query = $mysqli->prepare($query);
        $query->bind_param('idsdsss',$_POST['id_projects_suppliers'],$approved_amount_vat_excluded,$pdf_invoice_name,$_POST['vat'],$_POST['submit_date'],$_POST['description'],date('Y-m-d'));   
        $query->execute();  	
	}
	echo "inserted";
}
else if($_POST['id'] > 0) {
	$query = "UPDATE dne_payments SET id_projects_suppliers = ?,description = ?,submit_date = ?,submitted_account = ?,approval_date = ?,
			  approved_amount = ?,payment_date = ?,paid_amount = ?,paid_amount_vat_excluded = ?,invoice_date = ?,vat = ?,updated_date = ?
			  WHERE id = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('issdsdsddsdsi',$_POST['id_projects_suppliers'],$_POST['description'],$_POST['submit_date'],$_POST['submitted_account'],$_POST['approval_date'],$_POST['approved_amount'],
					   $_POST['payment_date'],$_POST['paid_amount'],$paid_amount_vat_excluded,$_POST['invoice_date'],$_POST['vat'],date("Y-m-d"),$_POST['id']);	
	$query->execute();
	
	if(isset($_FILES['pdf_submission']['name'])) {
		$pdf_submission_name = 'pdf_submission_'.$project->name.'_'.str_replace(' ','_',$_FILES['pdf_submission']['name']);
		move_uploaded_file($_FILES['pdf_submission']['tmp_name'],'uploads/'.$pdf_submission_name);
		
		$query = "UPDATE dne_payments SET pdf_submission = ? WHERE id = ?";
	    $query = $mysqli->prepare($query);
	    $query->bind_param('si',$pdf_submission_name,$_POST['id']);	
	    $query->execute();
	}
	
	if(isset($_FILES['pdf_approval']['name'])) {
		$pdf_approval_name = 'pdf_approval_'.$project->name.'_'.str_replace(' ','_',$_FILES['pdf_approval']['name']);
		move_uploaded_file($_FILES['pdf_approval']['tmp_name'],'uploads/'.$pdf_approval_name);
		
		$query = "UPDATE dne_payments SET pdf_approval = ? WHERE id = ?";
	    $query = $mysqli->prepare($query);
	    $query->bind_param('si',$pdf_approval_name,$_POST['id']);	
	    $query->execute();
	}
	
	if(isset($_FILES['pdf_payment']['name'])) {
		$pdf_payment_name = 'pdf_payment_'.$project->name.'_'.str_replace(' ','_',$_FILES['pdf_payment']['name']);
		move_uploaded_file($_FILES['pdf_payment']['tmp_name'],'uploads/'.$pdf_payment_name);
		
		$query = "UPDATE dne_payments SET pdf_payment = ? WHERE id = ?";
	    $query = $mysqli->prepare($query);
	    $query->bind_param('si',$pdf_payment_name,$_POST['id']);	
	    $query->execute();
	}
	
	if(isset($_FILES['pdf_invoice']['name'])) {
		$pdf_invoice_name = 'pdf_invoice_'.$project->name.'_'.str_replace(' ','_',$_FILES['pdf_invoice']['name']);
		move_uploaded_file($_FILES['pdf_invoice']['tmp_name'],'uploads/'.$pdf_invoice_name);
		
		$query = "UPDATE dne_payments SET pdf_invoice = ? WHERE id = ?";
	    $query = $mysqli->prepare($query);
	    $query->bind_param('si',$pdf_invoice_name,$_POST['id']);	
	    $query->execute();
	}
	
	echo 'updated';
}
?>