<?php 
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$ps_id = @$_GET['ps_id'];

$query = $mysqli->prepare("SELECT p.id AS p_id,sfow.name AS sfow_name,s.name AS name
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
						  WHERE ps.id = ?");
$query->bind_param("i",$ps_id);
$query->execute(); 
$query->store_result();
$supplier = fetch_unique($query);

$query = $mysqli->prepare("SELECT o.id AS id,o.signature_date AS signature_date,o.description AS description,o.sum_order AS sum_order,o.pdf_order AS pdf_order,o.vat AS vat
						  FROM dne_orders o 
						  LEFT JOIN dne_projects_suppliers ps ON o.id_projects_suppliers = ps.id 
						  WHERE ps.id = ? ORDER BY o.signature_date");
$query->bind_param("i",$ps_id);
$query->execute(); 
$query->store_result();
$orders = fetch($query);

$query = $mysqli->prepare("SELECT p.id AS id,p.payment_date AS payment_date,p.description AS description,p.paid_amount_vat_excluded AS paid_amount_vat_excluded,
                          p.paid_amount AS paid_amount_vat_included,p.pdf_invoice AS pdf_invoice
						  FROM dne_payments p
						  LEFT JOIN dne_projects_suppliers ps ON p.id_projects_suppliers = ps.id 
						  WHERE ps.id = ? ORDER BY p.payment_date");
$query->bind_param("i",$ps_id);
$query->execute(); 
$query->store_result();
$payments_num_rows = $query->num_rows;
$payments = fetch($query);

$isPartialPayment = 0;		  
$query = $mysqli->prepare("SELECT p.id AS id,p.id_projects_suppliers AS id_projects_suppliers,p.description AS description,
						  p.submit_date AS submit_date,p.pdf_submission AS pdf_submission,p.submitted_account AS submitted_account,p.approval_date AS approval_date,
						  p.pdf_approval AS pdf_approval,p.approved_amount AS approved_amount,p.payment_date As payment_date,p.pdf_payment AS pdf_payment,
					      p.paid_amount AS paid_amount_vat_included,p.vat AS vat,p.paid_amount_vat_excluded AS paid_amount_vat_excluded,
						  p.invoice_date AS invoice_date,p.pdf_invoice AS pdf_invoice,s.nickname AS nickname				  
						  FROM dne_payments p
						  LEFT JOIN dne_projects_suppliers ps on p.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE p.IsTotalPayment = ? AND p.id_projects_suppliers = ? ORDER BY p.approval_date DESC");
$query->bind_param("ii",$isPartialPayment,$ps_id);
$query->execute(); 
$query->store_result();
$partial_payments_num_rows = $query->num_rows;
$partial_payments = fetch($query);
?>
		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
		    <div class="row" style="margin-top:25px;text-align:center;">
			    <div class="col-md-12">
				    <img src="images/davidnahmias_logo.png" width="170px" height="170px" />
				</div>
			</div>
			
			<div class="container">
			    <div class="row title">	
					<div class="col-md-12"> 
						<a style="text-decoration:underline;" href="project_home.php?id=<?=@$supplier->p_id?>">
							<?=@$supplier->name?><br/><?=@$supplier->sfow_name?><br/>Orders/Payments Report <br/><?=substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4)?>
						</a>			
					</div>					
			    </div>
				<div class="row" style="text-align:center;">
					<div class="col-md-12">
						<input type="button" id="add_order_btn" name="add_order_btn" value="Add Order" class="btn marginTop20 mb-2" onclick="location.href='add_order.php?project_id=<?=@$supplier->p_id?>&ps_id=<?=@$ps_id?>';" />
						<input type="button" id="add_order_btn" name="add_order_btn" value="Add Payment" class="btn marginTop20 marginLeft10 mb-2" onclick="location.href='add_payment.php?project_id=<?=@$supplier->p_id?>&ps_id=<?=@$ps_id?>';" />
					</div>
				</div>
				<div class="row" style="font-size:13px;margin-top:15px;">
					<div align="center" class="col-md-12 mx-2">
						<table id="orders_payments_list" style="margin-top:15px;">
							<tr style="height:40px;background-color:silver;">
								<th width="80px" style="text-align:center;border:1px solid black!important;">Date</th>
								<th width="130px" style="text-align:center;border:1px solid black!important;">Order/Payment</th>
								<th width="320px;" style="text-align:center;border:1px solid black!important;">Description</th>
								<th width="130px;" style="text-align:center;border:1px solid black!important;">Orders <br/> <span style="font-size:12px;">VAT Excluded</span></th>
								<th width="130px;" style="text-align:center;border:1px solid black!important;">Payments <br/> <span style="font-size:12px;">VAT Excluded</span></th>
								<th width="20px;" style="background-color:white;">&nbsp;</th>
								<th width="130px;" style="text-align:center;background-color:#dcf1fa;border:1px solid black!important;">Payments <br/> <span style="font-size:12px;">VAT Included</span></th>
							</tr>
					
							<?php 
							$total_sum_order = 0;
							$total_sum_order_vat_included = 0;
							
							foreach($orders as $item) { 
								$signature_date = '';
								if(@$item->signature_date != '0000-00-00')
									$signature_date = substr(@$item->signature_date,8,2).'/'.substr(@$item->signature_date,5,2).'/'.substr(@$item->signature_date,2,2);
								
								$sum_order = '';
								if(@$item->sum_order != 0.00)
									$sum_order = number_format(@$item->sum_order,0,'.',',');
								
								$total_sum_order += $item->sum_order;
							?>
								<tr style="height:25px;">
									<td style="text-align:left;padding-left:5px;border:1px solid black!important;"><?=@$signature_date?></td>
									<td style="text-align:left;padding-left:5px;border:1px solid black!important;"><a href="add_order.php?id=<?=@$item->id?>&project_id=<?=@$supplier->p_id?>">order</a></td>
									<td style="text-align:left;padding-left:5px;border:1px solid black!important;"><?=@$item->description?></td>
									<td style="text-align:right;padding-right:5px;border:1px solid black!important;"><?php if(@$item->pdf_order != '') { ?><a href="uploads/<?=@$item->pdf_order?>" title="View PDF" target="_blank"><?=@$sum_order?>&#8362;</a><?php } else echo @$sum_order.'&#8362;'?></td>
									<td style="text-align:right;padding-right:5px;border:1px solid black!important;">&nbsp;</td>
									<td style="background-color:white;">&nbsp;</td>
									<td style="text-align:right;padding-right:5px;border:1px solid black!important;">&nbsp;</td>
								</tr>
							<?php } 
							
							$total_sum_order_vat_included = $total_sum_order*(1+($item->vat/100));
							
							$total_paid_amount_vat_excluded = 0;
							$total_paid_amount_vat_included = 0;
							
							foreach($payments as $item) { 
								$payment_date = '';
								if(@$item->payment_date != '0000-00-00')
									$payment_date = substr(@$item->payment_date,8,2).'/'.substr(@$item->payment_date,5,2).'/'.substr(@$item->payment_date,2,2);
								
								$paid_amount_vat_excluded_display = '';
								if(@$item->paid_amount_vat_excluded != 0.00) {
									$paid_amount_vat_excluded = @$item->paid_amount_vat_excluded;
									$paid_amount_vat_excluded_display = '-'.number_format(@$paid_amount_vat_excluded,0,'.',',').'&#8362';
								}
								
								$paid_amount_vat_included_display = '';
								if(@$item->paid_amount_vat_included != 0.00) {
									$paid_amount_vat_included = @$item->paid_amount_vat_included;
									$paid_amount_vat_included_display = '-'.number_format(@$paid_amount_vat_included,0,'.',',').'&#8362';
								}

								$total_paid_amount_vat_excluded +=$item->paid_amount_vat_excluded;
								$total_paid_amount_vat_included +=$item->paid_amount_vat_included;
								$balance_vat_excluded = $total_sum_order-$total_paid_amount_vat_excluded;
								$balance_vat_included = $total_sum_order_vat_included-$total_paid_amount_vat_included;
							?>
								<tr style="height:25px;">
									<td style="text-align:left;padding-left:5px;border:1px solid black!important;"><?=@$payment_date?></td>
									<td style="text-align:left;padding-left:5px;border:1px solid black!important;"><a href="add_payment.php?id=<?=@$item->id?>&project_id=<?=@$supplier->p_id?>">payment</a></td>
									<td style="text-align:left;padding-left:5px;border:1px solid black!important;"><?=@$item->description?></td>
									<td style="text-align:right;padding-right:5px;border:1px solid black!important;">&nbsp;</td>
									<td style="text-align:right;padding-right:5px;border:1px solid black!important;"><?php if(@$item->pdf_invoice != '') { ?><a href="uploads/<?=@$item->pdf_invoice?>" title="View PDF" target="_blank"><?=@$paid_amount_vat_excluded_display?></a><?php } else { echo @$paid_amount_vat_excluded_display;}?></td>
									<td style="background-color:white;">&nbsp;</td>
									<td style="text-align:right;padding-right:5px;border:1px solid black!important;"><?=@$paid_amount_vat_included_display?></td>
								</tr>
							<?php } 
							$total_sum_order = number_format(@$total_sum_order,2,'.',',');
							
							if($payments_num_rows > 0) {
								$total_paid_amount_vat_excluded = '-'.number_format(@$total_paid_amount_vat_excluded,2,'.',',').'&#8362';
								$total_paid_amount_vat_included = '-'.number_format(@$total_paid_amount_vat_included,2,'.',',').'&#8362';
								$balance_vat_excluded = number_format(@$balance_vat_excluded,2,'.',',').'&#8362';
								$balance_vat_included = number_format(@$balance_vat_included,2,'.',',').'&#8362';
							}
							else {
								$total_paid_amount_vat_excluded = '';
								$total_paid_amount_vat_included = '';
								$balance_vat_excluded = $total_sum_order.'&#8362';
								$balance_vat_included = number_format($total_sum_order_vat_included,2,'.',',').'&#8362';
							}
							?>						
							<tr style="height:30px;background-color:silver;">
								<td colspan="3" style="text-align:right;padding-right:5px;border:1px solid black!important;"><strong>Total</strong></td>
								<td style="text-align:right;padding-right:5px;border:1px solid black!important;"><strong><?=@$total_sum_order?></strong></td>
								<td style="text-align:right;padding-right:5px;border:1px solid black!important;"><strong><?=@$total_paid_amount_vat_excluded?></strong></td>
								<td style="background-color:white;">&nbsp;</td>
								<td style="text-align:right;padding-right:5px;border:1px solid black!important;"><strong><?=@$total_paid_amount_vat_included?></strong></td>
							</tr>
							<tr style="height:30px;background-color:#dcf1fa;">
								<td colspan="4" style="text-align:right;padding-right:5px;border:1px solid black!important;"><strong>Balance</strong></td>
								<td style="text-align:right;padding-right:5px;border:1px solid black!important;"><strong><?=@$balance_vat_excluded?></strong></td>
								<td style="background-color:white;">&nbsp;</td>
								<td style="text-align:right;padding-right:5px;border:1px solid black!important;"><strong><?=@$balance_vat_included?></strong></td>
							</tr>
						</table>
					</div>
				</div>
				
				<?php if($partial_payments_num_rows > 0) { ?>
					<div class="row title">	
						<div class="col-md-12">
							Current accounts
						</div>					
					</div>
					<div class="row" style="font-size:14px;margin-top:5px;">
						<div align="center" class="col-md-12 mx-2">
							<table id="payments_list" style="margin-top:15px;">
								<tr style="background-color:silver;height:50px;">
									<th width="30px;" style="text-align:center;border:1px solid black!important;">&#x2116;</th>
									<th width="80px;" style="text-align:center;border:1px solid black!important;">Submit <br/> date</th>
									<th width="250px;" style="text-align:center;border:1px solid black!important;">Description</th>
									<th width="120px;" style="text-align:center;border:1px solid black!important;">Submitted <br/> account <br/> <em>VAT included</em></th>
									<th width="80px;;" style="text-align:center;border:1px solid black!important;">Approval <br/> date</th>
									<th width="120px;;" style="text-align:center;border:1px solid black!important;">Approved <br/> amount <br/> <em>VAT included</em></th>
									<th width="80px;" style="text-align:center;border:1px solid black!important;">Payment <br/> date</th>
									<th width="120px;" style="text-align:center;border:1px solid black!important;">Paid <br/> amount</th>	
									<th width="50px;" style="text-align:center;border:1px solid black!important;">VAT</th>
									<!--<th width="120px;" style="text-align:center;">Payments <br/> VAT <br/> Excluded</th>-->
									<th width="120px;" style="text-align:center;border:1px solid black!important;">Balance</td>
									<th width="80px;" style="text-align:center;border:1px solid black!important;">Invoice<br/> date</th>													
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
										<td style="text-align:center;border:1px solid black!important;"><?=@$count?></td>		
										<td style="text-align:left;padding-left:5px;border:1px solid black!important;"><?=$submit_date?></td>									
										<td style="text-align:left;padding-left:5px;border:1px solid black!important;"><?=@$item->description?></td>
										<td style="text-align:right;padding-right:5px;border:1px solid black!important;"><?php if(@$item->pdf_submission != '') { ?><a href="uploads/<?=@$item->pdf_submission?>" title="View PDF" target="_blank"><?=@$submitted_account?></a><?php } else { echo @$submitted_account; }?></td>
										<td style="text-align:left;padding-left:5px;border:1px solid black!important;"><?=$approval_date?></td> 
										<td style="text-align:right;padding-right:5px;border:1px solid black!important;"><?php if(@$item->pdf_approval != '') { ?><a href="uploads/<?=@$item->pdf_approval?>" title="View PDF" target="_blank"><?=@$approved_amount?></a><?php } else { echo @$approved_amount; }?></td>
										<td style="text-align:left;padding-left:5px;border:1px solid black!important;"><?=$payment_date?></td>
										<td style="text-align:right;padding-right:5px;border:1px solid black!important;"><?php if(@$item->pdf_payment != '') { ?><a href="uploads/<?=@$item->pdf_payment?>" title="View PDF" target="_blank"><?=@$paid_amount_vat_included?></a><?php } else { echo @$paid_amount_vat_included; }?></td>
										<td style="text-align:right;padding-right:5px;border:1px solid black!important;"><?=number_format(@$item->vat,0,'.',',')?>%</td>
										<!--<td style="text-align:right;padding-right:5px;"><?php if(@$item->pdf_payment != '') { ?><a href="uploads/<?=@$item->pdf_payment?>" title="View PDF" target="_blank"><?=@$paid_amount_vat_excluded?></a><?php } else { echo @$paid_amount_vat_excluded; }?></td>-->
										<td width="120px;" style="text-align:center;border:1px solid black!important;"><?=@$balance?></td>
										<td width="120px;" style="text-align:center;border:1px solid black!important;"><?php if(@$item->pdf_invoice != '') { ?><a href="uploads/<?=@$item->pdf_invoice?>" title="View PDF" target="_blank"><?=@$invoice_date?></a><?php } else echo @$invoice_date?></td>
									</tr>
									<?php
								} ?>
							</table>
						</div>
					</div>
				<?php } ?>
				
				<div class="row" style="text-align:center;">
					<div class="col-md-12">
						<a href="orders_payments_report.php?project_id=<?=$supplier->p_id?>&ps_id=<?=@$ps_id?>" target="_blank">
							<input type="button" value="Orders/Payments Report" class="btn marginTop20 mb-2" />
						</a>
					</div>
				</div>
			</div>
		</form>
	</body>
</html>

<style>
tr:nth-of-type(even) {
  background-color: #dedede!important;
}

tr:last-of-type {
  background-color:#dcf1fa!important;
}

table,th,td {
    border: none!important;
}

a {
	color: inherit;
}

.marginTop20 {
	margin-top: 20px;
}

.marginLeft10 {
	margin-left: 10px;
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