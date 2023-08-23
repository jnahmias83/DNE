<?php
session_start();
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$project_id = @$_GET['project_id'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT p.id AS id,p.approved_amount AS approved_amount,p.paid_amount AS paid_amount_vat_included
                          FROM dne_payments p
						  LEFT JOIN dne_projects_suppliers ps on p.id_projects_suppliers = ps.id
						  WHERE ps.id_project = ?");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$payments = fetch($query);

foreach ($payments as $item) {
	$IsTotalPayment = 1;
	
	if($item->approved_amount == 0.00 || $item->approved_amount < $item->paid_amount_vat_included) {
		$query = "UPDATE dne_payments SET approved_amount = ?,IsTotalPayment = ? WHERE id = ?";
	    $query = $mysqli->prepare($query);
	    $query->bind_param('dii',$item->paid_amount_vat_included,$IsTotalPayment,$item->id);	
	    $query->execute();
	}
	
	if($item->paid_amount_vat_included == $item->approved_amount) {
		$query = "UPDATE dne_payments SET IsTotalPayment = ? WHERE id = ?";
	    $query = $mysqli->prepare($query);
	    $query->bind_param('ii',$IsTotalPayment,$item->id);	
	    $query->execute();
	}
}

$isTotalPayment = 1;		  
$query = $mysqli->prepare("SELECT p.id AS id,p.id_projects_suppliers AS id_projects_suppliers,p.description AS description,
						  p.submit_date AS submit_date,p.pdf_submission AS pdf_submission,p.submitted_account AS submitted_account,p.approval_date AS approval_date,
						  p.pdf_approval AS pdf_approval,p.approved_amount AS approved_amount,p.payment_date As payment_date,p.pdf_payment AS pdf_payment,
					      p.paid_amount AS paid_amount_vat_included,p.vat AS vat,p.paid_amount_vat_excluded AS paid_amount_vat_excluded,
						  p.invoice_date AS invoice_date,p.pdf_invoice AS pdf_invoice,s.nickname AS nickname				  
						  FROM dne_payments p
						  LEFT JOIN dne_projects_suppliers ps on p.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE p.IsTotalPayment = ? AND ps.id_project = ? ORDER BY p.payment_date DESC");
$query->bind_param("ii",$isTotalPayment,$project_id);
$query->execute(); 
$query->store_result();
$total_payments_num_rows = $query->num_rows;
$total_payments = fetch($query);

$isPartialPayment = 0;		  
$query = $mysqli->prepare("SELECT p.id AS id,p.id_projects_suppliers AS id_projects_suppliers,p.description AS description,
						  p.submit_date AS submit_date,p.pdf_submission AS pdf_submission,p.submitted_account AS submitted_account,p.approval_date AS approval_date,
						  p.pdf_approval AS pdf_approval,p.approved_amount AS approved_amount,p.payment_date As payment_date,p.pdf_payment AS pdf_payment,
					      p.paid_amount AS paid_amount_vat_included,p.vat AS vat,p.paid_amount_vat_excluded AS paid_amount_vat_excluded,
						  p.invoice_date AS invoice_date,p.pdf_invoice AS pdf_invoice,s.nickname AS nickname				  
						  FROM dne_payments p
						  LEFT JOIN dne_projects_suppliers ps on p.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE p.IsTotalPayment = ? AND ps.id_project = ? ORDER BY p.approval_date DESC");
$query->bind_param("ii",$isPartialPayment,$project_id);
$query->execute(); 
$query->store_result();
$partial_payments_num_rows = $query->num_rows;
$partial_payments = fetch($query);
?>

        <form method="post" action="" class="form-inline">	
			<div class="row" style="margin-top:25px;text-align:center;">
			    <div class="col-md-12">
				    <img src="images/davidnahmias_logo.png" width="170px" height="170px" />
				</div>
			</div>
			
		    <div class="container">
			    <div class="row title">	
					<div class="col-md-12">
						<a style="text-decoration:underline;" href="project_home.php?id=<?=@$project_id?>">
							<?=@$project->name.'<br/> Payments Report <br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4)?>
						</a>
					</div>					
			    </div>
				<div class="row" style="text-align:center;">
					<div class="col-md-12">
						<input type="button" value="Add new payment" class="btn marginTop20 mb-2" onclick="location.href='add_payment.php?id=0&project_id=<?=@$project_id?>';" />
					</div>
				</div>	
				
				<?php if($partial_payments_num_rows > 0 || $total_payments_num_rows) { ?>
					<div class="row" style="font-size:14px;margin-top:20px;">
						<div align="center" class="col-md-12 mx-2">
							<table id="payments_list" style="margin-top:15px;" border="1">
								<tr style="background-color:silver;height:50px;">
									<th width="30px;" style="text-align:center;">&#x2116;</th>
									<th width="100px;" style="text-align:center;">Supplier <br/> Name</th>
									<th width="80px;" style="text-align:center;">Submit <br/> date</th>
									<th width="250px;" style="text-align:center;">Description</th>
									<th width="120px;" style="text-align:center;">Submitted <br/> account <br/> <em>VAT included</em></th>
									<th width="80px;;" style="text-align:center;">Approval <br/> date</th>
									<th width="120px;;" style="text-align:center;">Approved <br/> amount <br/> <em>VAT included</em></th>
									<th width="80px;" style="text-align:center;">Payment <br/> date</th>
									<th width="120px;" style="text-align:center;">Paid <br/> amount</th>	
									<th width="50px;" style="text-align:center;">VAT</th>
									<!--<th width="120px;" style="text-align:center;">Payments <br/> VAT <br/> Excluded</th>-->
									<th width="120px;" style="text-align:center;">Balance</td>
									<th width="80px;" style="text-align:center;">Invoice<br/> date</th>													
									<th width="40px;">&nbsp;</th>
									<th width="40px;">&nbsp;</th>
								</tr>
								
								<?php
								$count = 0;
								foreach($partial_payments as $item) {
									$count++;
									
									$submit_date = '';
									if(@$item->submit_date != '0000-00-00')
										$submit_date = substr(@$item->submit_date,8,2).'/'.substr(@$item->submit_date,5,2).'/'.substr(@$item->submit_date,2,2);
									
									$submitted_account = '';
									if(@$item->submitted_account != 0.00)
									   $submitted_account = number_format(@$item->submitted_account,2,'.',',').'&nbsp;&#8362;';
									
									$approval_date = '';
									if(@$item->approval_date != '0000-00-00')
										$approval_date = substr(@$item->approval_date,8,2).'/'.substr(@$item->approval_date,5,2).'/'.substr(@$item->approval_date,2,2);
									
									$approved_amount = '';
									if(@$item->approved_amount != 0.00)
									   $approved_amount = number_format(@$item->approved_amount,2,'.',',').'&nbsp;&#8362;';
								   
									$payment_date = '';
									if(@$item->payment_date != '0000-00-00')
										$payment_date = substr(@$item->payment_date,8,2).'/'.substr(@$item->payment_date,5,2).'/'.substr(@$item->payment_date,2,2);
									
									$paid_amount_vat_included = '';
									if(@$item->paid_amount_vat_included != 0.00)
										$paid_amount_vat_included = number_format(@$item->paid_amount_vat_included,2,'.',',').'&nbsp;&#8362;';
									
									$paid_amount_vat_excluded = '';
									if(@$item->paid_amount_vat_excluded != 0.00)
										$paid_amount_vat_excluded = number_format(@$item->paid_amount_vat_excluded,2,'.',',').'&nbsp;&#8362;';
									
									$balance = number_format(@$item->approved_amount - @$item->paid_amount_vat_included,2,'.',',').'&nbsp;&#8362;';
									
									$invoice_date = '';
									if(@$item->invoice_date != '0000-00-00')
										$invoice_date = substr(@$item->invoice_date,8,2).'/'.substr(@$item->invoice_date,5,2).'/'.substr(@$item->invoice_date,2,2);
									?>
									<tr style="height:30px;">
										<td style="text-align:center;"><?=@$count?></td>
										<td style="text-align:left;padding-left:5px;"><?=@$item->nickname?></td>			
										<td style="text-align:left;padding-left:5px;"><?=$submit_date?></td>									
										<td style="text-align:left;padding-left:5px;"><?=@$item->description?></td>
										<td style="text-align:right;padding-right:5px;"><?php if(@$item->pdf_submission != '') { ?><a href="uploads/<?=@$item->pdf_submission?>" title="View PDF" target="_blank"><?=@$submitted_account?></a><?php } else { echo @$submitted_account; }?></td>
										<td style="text-align:left;padding-left:5px;"><?=$approval_date?></td> 
										<td style="text-align:right;padding-right:5px;"><?php if(@$item->pdf_approval != '') { ?><a href="uploads/<?=@$item->pdf_approval?>" title="View PDF" target="_blank"><?=@$approved_amount?></a><?php } else { echo @$approved_amount; }?></td>
										<td style="text-align:left;padding-left:5px;"><?=$payment_date?></td>
										<td style="text-align:right;padding-right:5px;"><?php if(@$item->pdf_payment != '') { ?><a href="uploads/<?=@$item->pdf_payment?>" title="View PDF" target="_blank"><?=@$paid_amount_vat_included?></a><?php } else { echo @$paid_amount_vat_included; }?></td>
										<td style="text-align:right;padding-right:5px;"><?=number_format(@$item->vat,0,'.',',')?>%</td>
										<!--<td style="text-align:right;padding-right:5px;"><?php if(@$item->pdf_payment != '') { ?><a href="uploads/<?=@$item->pdf_payment?>" title="View PDF" target="_blank"><?=@$paid_amount_vat_excluded?></a><?php } else { echo @$paid_amount_vat_excluded; }?></td>-->
										<td width="120px;" style="text-align:center;"><?=@$balance?></td>
										<td width="120px;" style="text-align:center;"><?php if(@$item->pdf_invoice != '') { ?><a href="uploads/<?=@$item->pdf_invoice?>" title="View PDF" target="_blank"><?=@$invoice_date?></a><?php } else echo @$invoice_date?></td>
										<td style="text-align:center;"><img src="images/edit-button.svg" style="cursor:pointer;" title="Edite" onclick="location.href='add_payment.php?id=<?=@$item->id?>&project_id=<?=@$project_id?>'" /></td>									
										<td style="text-align:center;"><img src="images/delete.svg" style="cursor:pointer;" title="Remove" onclick="return removePayment(<?=@$item->id?>);" /></td>	
									</tr>
									<?php
								}
								foreach($total_payments as $item) {
									$count++;
									
									$submit_date = '';
									if(@$item->submit_date != '0000-00-00')
										$submit_date = substr(@$item->submit_date,8,2).'/'.substr(@$item->submit_date,5,2).'/'.substr(@$item->submit_date,2,2);
									
									$submitted_account = '';
									if(@$item->submitted_account != 0.00)
									   $submitted_account = number_format(@$item->submitted_account,2,'.',',').'&nbsp;&#8362;';
									
									$approval_date = '';
									if(@$item->approval_date != '0000-00-00')
										$approval_date = substr(@$item->approval_date,8,2).'/'.substr(@$item->approval_date,5,2).'/'.substr(@$item->approval_date,2,2);
									
									$approved_amount = '';
									if(@$item->approved_amount != 0.00)
									   $approved_amount = number_format(@$item->approved_amount,2,'.',',').'&nbsp;&#8362;';
								   
									$payment_date = '';
									if(@$item->payment_date != '0000-00-00')
										$payment_date = substr(@$item->payment_date,8,2).'/'.substr(@$item->payment_date,5,2).'/'.substr(@$item->payment_date,2,2);
									
									$paid_amount_vat_included = '';
									if(@$item->paid_amount_vat_included != 0.00)
										$paid_amount_vat_included = number_format(@$item->paid_amount_vat_included,2,'.',',').'&nbsp;&#8362;';
									
									$paid_amount_vat_excluded = '';
									if(@$item->paid_amount_vat_excluded != 0.00)
										$paid_amount_vat_excluded = number_format(@$item->paid_amount_vat_excluded,2,'.',',').'&nbsp;&#8362;';
									
									$balance = number_format(@$item->approved_amount - @$item->paid_amount_vat_included,2,'.',',').'&nbsp;&#8362;';
									
									$invoice_date = '';
									if(@$item->invoice_date != '0000-00-00')
										$invoice_date = substr(@$item->invoice_date,8,2).'/'.substr(@$item->invoice_date,5,2).'/'.substr(@$item->invoice_date,2,2);
									?>
									<tr style="height:30px;background-color:#bebebe;">
										<td style="text-align:center;"><?=@$count?></td>
										<td style="text-align:left;padding-left:5px;"><?=@$item->nickname?></td>			
										<td style="text-align:left;padding-left:5px;"><?=$submit_date?></td>									
										<td style="text-align:left;padding-left:5px;"><?=@$item->description?></td>
										<td style="text-align:right;padding-right:5px;"><?php if(@$item->pdf_submission != '') { ?><a href="uploads/<?=@$item->pdf_submission?>" title="View PDF" target="_blank"><?=@$submitted_account?></a><?php } else { echo @$submitted_account; }?></td>
										<td style="text-align:left;padding-left:5px;"><?=$approval_date?></td> 
										<td style="text-align:right;padding-right:5px;"><?php if(@$item->pdf_approval != '') { ?><a href="uploads/<?=@$item->pdf_approval?>" title="View PDF" target="_blank"><?=@$approved_amount?></a><?php } else { echo @$approved_amount; }?></td>
										<td style="text-align:left;padding-left:5px;"><?=$payment_date?></td>
										<td style="text-align:right;padding-right:5px;"><?php if(@$item->pdf_payment != '') { ?><a href="uploads/<?=@$item->pdf_payment?>" title="View PDF" target="_blank"><?=@$paid_amount_vat_included?></a><?php } else { echo @$paid_amount_vat_included; }?></td>
										<td style="text-align:right;padding-right:5px;"><?=number_format(@$item->vat,0,'.',',')?>%</td>
										<!--<td style="text-align:right;padding-right:5px;"><?php if(@$item->pdf_payment != '') { ?><a href="uploads/<?=@$item->pdf_payment?>" title="View PDF" target="_blank"><?=@$paid_amount_vat_excluded?></a><?php } else { echo @$paid_amount_vat_excluded; }?></td>-->
										<td width="120px;" style="text-align:center;"><?=@$balance?></td>
										<td width="120px;" style="text-align:center;"><?php if(@$item->pdf_invoice != '') { ?><a href="uploads/<?=@$item->pdf_invoice?>" title="View PDF" target="_blank"><?=@$invoice_date?></a><?php } else echo @$invoice_date?></td>
										<td style="text-align:center;"><img src="images/edit-button.svg" style="cursor:pointer;" title="Edite" onclick="location.href='add_payment.php?id=<?=@$item->id?>&project_id=<?=@$project_id?>'" /></td>									
										<td style="text-align:center;"><img src="images/delete.svg" style="cursor:pointer;" title="Remove" onclick="return removePayment(<?=@$item->id?>);" /></td>	
									</tr>
									<?php
								}
								?>
							</table>		
						</div>
					</div>
					<div class="row" style="text-align:center;">
						<div class="col-md-12">
							<a href="payments_report.php?project_id=<?=@$project_id?>" target="_blank">
								<input type="button" value="Payments Report" class="btn marginTop20 mb-2" />
							</a>
						</div>
					</div>				
				<?php } ?>
			</div>
		</form> 
	</body>
</html>

<script>
function removePayment(id) {
	if(confirm("Are you sure to remove this payment?")) {
        var form_data = new FormData();	
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'payment_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
				location.reload(true);			
			},
		});		
    }
    return false;
}
</script>

<style>
a {
	color: inherit;
}

.marginTop20 {
	margin-top: 20px;
}

.title {
	font-size: 20px;
	color: #349feb;
	text-align: center;
	margin-top: 20px;
	text-decoration:underline;
}

.btn {
	background-color:#218FD6;
	color: white;
}

.btn:hover {
   background-color:#3370d6;
   color: white;
}
</style>