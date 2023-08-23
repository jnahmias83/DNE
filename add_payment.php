<?php
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$id = @$_GET['id'];
$project_id = @$_GET['project_id'];
$ps_id = @$_GET['ps_id'];


$query = $mysqli->prepare("SELECT p.id AS id,p.id_projects_suppliers AS id_projects_suppliers,p.description AS description,p.pdf_submission AS pdf_submission,p.submit_date AS submit_date,p.submitted_account AS submitted_account,
                          p.pdf_approval AS pdf_approval,p.approval_date AS approval_date,p.approved_amount AS approved_amount,p.pdf_payment AS pdf_payment,p.payment_date AS payment_date,p.paid_amount AS paid_amount,
						  p.pdf_invoice AS pdf_invoice,p.invoice_date AS invoice_date,p.vat AS vat
                          FROM dne_payments p 
						  LEFT JOIN dne_projects_suppliers ps ON p.id_projects_suppliers = ps.id
						  WHERE p.id = ?");
$query->bind_param("i",$id );
$query->execute();
$query->store_result();
$payment = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT ps.id AS id,s.name AS name
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? 
                          ORDER BY name ASC");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$suppliers = fetch($query);      
?>

		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" />
			<input type="hidden" id="project_id" value="<?=@$project_id?>" />	
			
			<br/>
			
			<div class="row" style="margin-top:25px;text-align:center;">
			    <div class="col-md-12">
				    <img src="images/davidnahmias_logo.png" width="170px" height="170px" />
				</div>
			</div>

			<div class="container">
			   <?php
				if($id == 0) { ?>
					<div class="row title">
						<div class="col-md-12" style="font-size:20px;">
							Add a payment for the project <span style="color:#5bbd8d;"><?=@$project->name?></span>
						</div>
					</div>
				<?php } ?>
				
				<br/>

				<div class="row" style="margin-top:2px;font-size:14px;">
					<div class="col-md-12">
					<strong>Suppliers:</strong>
					</div>
				</div>		
							
				<div class="row" style="margin-top:10px;">
					<div class="col-md-4">
						<select id="suppliers" class="form-control" style="width:170px;">							
							<?php 
								foreach($suppliers as $item) {
								?>
									<option value="<?=@$item->id?>" <?php if(($id == 0 && $item->id == @$ps_id) || ($item->id == @$payment->id_projects_suppliers)) echo "selected";?>>
										<?=@$item->name?>
									</option>
									<?php
								}
							?>										
						</select>
					</div>
				</div>
				
				<div class="row" style="margin-top:10px;">
					<div class="col-md-4" style="padding-top:2px;">
						<?php
						if($id > 0) { ?>
							<strong>Description:</strong>
							<br/>
						<?php } ?>					
						<textarea class="form-control" name="description" id="description" rows="5" cols="18" placeholder="Description..."><?=@$payment->description?></textarea>
					</div>
				</div>
				
				<div class="row m-4">
					<div class="card col-5 m-4" style="padding:0!important;">
						 <div class="card-header">Submission - חשבון שהוגש</div>
						<div class="card-body">
							<p>
								<span style="font-size:14px;font-weight:bold;">PDF submission:</span>
								<br/>					
								<input type="file" class="form-control w-50" name="pdf_submission" id="pdf_submission" style="margin-top:10px;" />
								<?php if($id > 0) { ?>&nbsp;<a href="uploads/<?=@$payment->pdf_submission?>" target="_blank"><?=@$payment->pdf_submission?></a><?php } ?>			
							</p>
							<p>
							   <span style="font-size:14px;font-weight:bold;">Submit date:</span>
							   <br/>					
							   <input type="date" name="submit_date" id="submit_date" class="form-control w-50" value="<?=@$payment->submit_date?>" />	
							</p>
							<p>
							   <?php
							   if($id > 0) { ?>
								  <strong>Submitted account - סך כולל מע''ם:</strong>
								  <br/>
								<?php } ?>	
								<input type="text" class="form-control w-50" name="submitted_account" id="submitted_account" placeholder="Submitted account - סך כולל מע''ם" value="<?=@$payment->submitted_account?>" />						
							</p>
						</div>
					</div>
					<div class="card col-5 m-4" style="padding:0!important;">
						<div class="card-header">Approbation - חשבון שאופשר</div>
						<div class="card-body">
							<p>
							   <span style="font-size:14px;font-weight:bold;">PDF approval:</span>
							   <br/>					
							   <input type="file" class="form-control w-50" name="pdf_approval" id="pdf_approval" />
							   <?php if($id > 0) { ?>&nbsp;<a href="uploads/<?=@$payment->pdf_approval?>" target="_blank"><?=@$payment->pdf_approval?></a><?php } ?>			
							</p>
							<p>
							   <span style="font-size:14px;font-weight:bold;">Approval date:</span>
							   <br/>					
							   <input type="date" class="form-control w-50" name="approval_date" id="approval_date" value="<?=@$payment->approval_date?>" />	
							</p>
							<p>
								<?php
								if($id > 0) { ?>
									<span style="font-size:14px;font-weight:bold;">Approved amount - סך מאושר לתשלום כולל מע''ם:</strong></span>
									<br/>
								<?php } ?>
								
								<input type="text" name="approved_amount" id="approved_amount" placeholder="Approved amount - סך שאושר לתשלום כולל מע''ם" value="<?=@$payment->approved_amount?>" style="width:320px;height:35px;" />	
								<?php if($id === 0) { ?>
								   <br/><br/>
								   <input type="checkbox" id="create_order_cb" name="create_order_cb" />&nbsp;Create order for this account
								<?php  } ?>
							</p>
						</div>
					</div>
					<div class="card col-5 m-4" style="padding:0!important;">
						<div class="card-header">Payment - תשלום שבוצע</div>
						<div class="card-body">
							<p>
							   <span style="font-size:14px;font-weight:bold;">PDF payment:</span>
							   <br/>					
							   <input type="file" class="form-control w-50" name="pdf_payment" id="pdf_payment" />
							   <?php if($id > 0) { ?>&nbsp;<a href="uploads/<?=@$payment->pdf_payment?>" target="_blank"><?=@$payment->pdf_payment?></a><?php } ?>		
							</p>
							<p>
							  <span style="font-size:14px;font-weight:bold;">Payment date:</span>
							  <br/>					
							  <input type="date" class="form-control w-50" name="payment_date" id="payment_date" value="<?=@$payment->payment_date?>" />	
							</p>
							<p>
							  <?php
							  if($id > 0) { ?>
								<strong>Paid amount:</strong>
								<br/>
							  <?php } ?>					
							  <input type="text" class="form-control w-50" name="paid_amount" id="paid_amount" placeholder="Paid amount" value="<?=@$payment->paid_amount?>" />
							</p>
						</div>	
					</div>
					<div class="card col-5 m-4" style="padding:0!important;">
						<div class="card-header">Invoice - חשבונית מס</div>
						<div class="card-body">
						   <p>
							 <span style="font-size:14px;font-weight:bold;">PDF invoice:</span>
							 <br/>					
							 <input type="file" class="form-control w-50" name="pdf_invoice" id="pdf_invoice" />
							 <?php if($id > 0) { ?>&nbsp;<a href="uploads/<?=@$payment->pdf_invoice?>" target="_blank"><?=@$payment->pdf_invoice?></a><?php } ?>		
						   </p>
						   <p>
							 <span style="font-size:14px;font-weight:bold;">Invoice date:</span>
							 <br/>					
							 <input type="date" class="form-control w-50" name="invoice_date" id="invoice_date" value="<?=@$payment->invoice_date?>" />	
						   </p>
						</div>
					</div>
				</div>
				<div class="row" style="margin-top:20px;">
					<div class="col-md-4">                   					
						<?php
						if($id > 0) { ?>
							<strong> VAT:</strong>
							<br/>
						<?php } ?>			
						<input type="text" style="width:30%;" name="vat" id="vat" placeholder="VAT" value="<?php if($id == 0) echo "17";else echo @$payment->vat?>" />&nbsp;%	
					</div>
				</div>						
				
				<div class="row">
					<div class="col-md-4">			
						<input type="button" id="cancel_btn" class="btn marginTop20 bgColorBlack colorWhite marginRight8 mb-2" value="Cancel" />
						<input type="button" id="save_btn" name="save_btn" class="btn marginTop20 bgColorBlue colorWhite mb-2" 
						value="Save" />						
					</div>
				</div>				
            </div>
		</form>       
    </body>
</html>

<script>
var pdf_submission;
var pdf_approval;
var pdf_payment;
var pdf_invoice;

$(document).on('change','#pdf_submission',function() {
    pdf_submission = $('#pdf_submission')[0].files[0];
});

$(document).on('change','#pdf_approval',function() {
    pdf_approval = $('#pdf_approval')[0].files[0];
});

$(document).on('change','#pdf_payment',function() {
    pdf_payment = $('#pdf_payment')[0].files[0];
});

$(document).on('change','#pdf_invoice',function() {
    pdf_invoice = $('#pdf_invoice')[0].files[0];
});

$('#suppliers').chosen();

var create_order_from_payment = 0;

$('#create_order_cb').click (function (e){  
    if ($(this).is(':checked'))
      create_order_from_payment = 1;
    else if(!($(this).is(':checked'))) 
	  create_order_from_payment = 0;
});

$('#save_btn').click (function (e){  
	var form_data = new FormData();	
	form_data.append('id',$('#id').val());
	form_data.append('id_projects_suppliers',$('#suppliers').val());
	form_data.append('description',$('#description').val());
	form_data.append('pdf_submission',pdf_submission);
	form_data.append('submit_date',$('#submit_date').val());
	form_data.append('submitted_account',$('#submitted_account').val());
	form_data.append('pdf_approval',pdf_approval);
	form_data.append('approval_date',$('#approval_date').val());
	form_data.append('approved_amount',$('#approved_amount').val());
	form_data.append('pdf_payment',pdf_payment);
	form_data.append('payment_date',$('#payment_date').val());
	form_data.append('paid_amount',$('#paid_amount').val());
	form_data.append('pdf_invoice',pdf_invoice);
	form_data.append('invoice_date',$('#invoice_date').val());
	form_data.append('vat',$('#vat').val());	
	form_data.append('create_order_from_payment',create_order_from_payment);
	$.ajax({
		type: 'POST',
		url: 'payment_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){  
			location.href = 'payments.php?project_id='+$('#project_id').val();		
		},
	});												       			   
})

$('#cancel_btn').click(function(){
    location.href = "payments.php?project_id="+$('#project_id').val();	
})
</script>

<style>
.title {
	font-size: 22px;
	color: #349feb;
	margin-top: 20px;
}

.bgColorBlack {
	background-color: black;
}

.bgColorBlue {
	background-color:#218FD6;
}

.marginTop20 {
  margin-top: 20px;
}

.marginRight8 {
	margin-right: 8px;
}

.colorWhite {
	color: white;
}

.btn:hover {
   color: white;
}

.bgColorBlack:hover {
	background-color: #45484d;
}

.bgColorBlue:hover {
	background-color:#3370d6;
}
</style>