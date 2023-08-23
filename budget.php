<?php
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$project_id = @$_GET['project_id'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_vat");
$query->execute(); 
$query->store_result();
$vat = fetch_unique($query);

$query = $mysqli->prepare("SELECT o.id_projects_suppliers AS id_projects_suppliers,SUM(o.sum_order) AS total_sum_order,o.vat AS vat
                          FROM dne_orders o 
						  LEFT JOIN dne_projects_suppliers ps on o.id_projects_suppliers = ps.id
                          LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id						  
						  WHERE ps.id_project = ? 
						  GROUP BY o.id_projects_suppliers ORDER BY s.type,s.name");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$orders = fetch($query);

$total_sum_order = 0;
$total_sum_order_vat = 0;
$total_sum_order_vat_included = 0;

$total_sum_paid = 0;
$total_sum_paid_vat = 0;
$total_sum_paid_vat_included = 0;

$total_balance = 0;
$total_balance_vat = 0;
$total_balance_vat_included = 0;

$total_to_pay = 0;

$count = 0;
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
							<?=@$project->name."<br/> Budget Report <br/>".substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4)?>
						</a>
					</div>					
			    </div>
			
				<div class="row" style="font-size:14px;text-align:center;margin-top:25px;">
					<div align="center" class="col-md-12 mx-2">
						<table cellpadding="2" cellspacing="2">
							<tr style="height:25px;font-size:14px;">
								<th colspan="3" style="text-align:center;background-color:silver;border:1px solid black!important;">Supplier</th>
								<th colspan="3" style="text-align:center;border:1px solid black!important;">VAT excluded</th>
								<th style="background-color:white;">&nbsp;</th>
								<th colspan="2" style="text-align:center;background-color:#dcf1fa;border:1px solid black!important;">VAT included</th>
							</tr>					
							<tr style="height:35px;font-size:14px;">
								<th width="30px;" style="text-align:center;border:1px solid black!important;">&#x2116;</th>
								<th width="120px;" style="text-align:center;border:1px solid black!important;">Name</th>
								<th width="120px;" style="text-align:center;border:1px solid black!important;">Domain</th>
								<th width="120px;" style="text-align:center;border:1px solid black!important;">Total Order</th>
								<th width="120px" style="text-align:center;border:1px solid black!important;">Paid amount</th>
								<th width="120px;" style="text-align:center;border:1px solid black!important;">Balance</th>
								<th width="20px" style="background-color:white;">&nbsp;</th>
								<th width="120px;" style="text-align:center;border:1px solid black!important;">Balance</th>
								<th width="120px;" style="text-align:center;border:1px solid black!important;">Account to be <br/> paid at this date</th>
							</tr>

							<?php
							$count = 0;
							foreach($orders as $item) {							
								$query = $mysqli->prepare("SELECT SUM(p.paid_amount_vat_excluded) AS sum_paid_amount,SUM(p.approved_amount)-SUM(p.paid_amount) AS account_to_be_paid,
														  s.name AS name,s.name_he AS name_he,s.id_field_of_work AS id_field_of_work,sfow.name AS sfow_name
														  FROM dne_payments p
														  LEFT JOIN dne_projects_suppliers ps on p.id_projects_suppliers = ps.id 
														  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
														  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
														  WHERE ps.id = ? ORDER BY s.type,s.name");
								$query->bind_param("i",$item->id_projects_suppliers);
								$query->execute();
								$query->store_result();
								$payment = fetch_unique($query);
								
								$count++;
								
								$bg_color_elems = '#ffffff';
								if($count%2 != 0)
								   $bg_color_elems = '#dedede';
							   
								
								$total_sum_order_elem_display = '';
								if($item->total_sum_order > 0) 
									$total_sum_order_elem_display = number_format($item->total_sum_order,2,'.',',').'&#8362';

								$sum_paid_amount_display = '';
								if($payment->sum_paid_amount > 0) 
									$sum_paid_amount_display = number_format($payment->sum_paid_amount,2,'.',',').'&#8362';
								
								$balance = round($item->total_sum_order-$payment->sum_paid_amount);
								$balance_vat_included = round($balance*(1+($item->vat/100)));
								
								$account_to_be_paid_display = '';
								if($payment->account_to_be_paid > 0) { 
									$account_to_be_paid = round($payment->account_to_be_paid);
									$total_to_pay += $account_to_be_paid;
									$account_to_be_paid_display = number_format($account_to_be_paid,0,'.', ',').'&nbsp;&#x20aa;';
								}
								
								$total_sum_order += $item->total_sum_order;
								$total_sum_order_vat += $item->total_sum_order*($item->vat/100);
								$total_sum_order_vat_included += $item->total_sum_order*(1+($item->vat/100));	
								
								$total_sum_paid += $payment->sum_paid_amount;
								$total_sum_paid_vat += $payment->sum_paid_amount*($item->vat/100);
								$total_sum_paid_vat_included += $payment->sum_paid_amount*(1+($item->vat/100));
								
								$total_balance += $balance;
								$total_balance_vat += $balance*($item->vat/100);
								$total_balance_vat_included += $balance*(1+($item->vat/100));
								?>
								<tr style="height:25px;font-size:14px;background-color:<?=@$bg_color_elems?>">
									<td style="text-align:center;border:1px solid black!important;"><?=@$count?></td>
									<td style="text-align:left;padding-left:5px;border:1px solid black!important;"><a href="orders_payments.php?ps_id=<?=@$item->id_projects_suppliers?>"><?=@$payment->name?></a></td>
									<td style="text-align:left;padding-left:5px;border:1px solid black!important;"><?=@$payment->sfow_name?></td>	
									<td width="110px;" style="text-align:right;padding-right:5px;border:1px solid black!important;"><?=@$total_sum_order_elem_display?></td>
									<td width="110px;" style="text-align:right;padding-right:5px;border:1px solid black!important;"><?=@$sum_paid_amount_display?></td>
									<td width="110px;" style="text-align:right;padding-right:5px;border:1px solid black!important;"><?=number_format($balance,0,'.', ',')?>&#8362;</td>
									<td width="20px" style="background-color:white;">&nbsp;</td>
									<td width="110px;" style="text-align:right;padding-right:5px;border:1px solid black!important;"><?=number_format($balance_vat_included,0,'.', ',')?>&#8362;</td>
									<td width="110px;" style="text-align:right;padding-right:5px;border:1px solid black!important;"><?=@$account_to_be_paid_display?></td>							
								</tr>
								<?php
							}
							?>
							<tr>
							   <td colspan="9">&nbsp;</td>
							</tr>
							<tr height="30px;" style="font-size:14px;font-weight:bold;background-color:#dedede;">
								<td colspan="3" style="text-align:right;padding-right:5px;border:1px solid black!important;">Total VAT excluded</td>
								<td style="text-align:right;padding-right:5px;border:1px solid black!important;"><?=number_format($total_sum_order,0,'.',',')?>&#8362;</td>
								<td style="text-align:right;padding-right:5px;border:1px solid black!important;"><?=number_format($total_sum_paid,0,'.',',')?>&#8362;</td>
								<td style="text-align:right;padding-right:5px;border:1px solid black!important;"><?=number_format($total_balance,0,'.',',')?>&#8362;</td>
								<td width="20px" style="background-color:white;">&nbsp;</td>
								<td style="text-align:center;background-color:#dcf1fa;border:1px solid black!important;"><strong>Total balance</strong></td>  
								<td style="text-align:center;background-color:#dcf1fa;border:1px solid black!important;"><strong>Total to be paid</strong></td>
							</tr>
							<tr height="30px;" style="font-size:14px;font-weight:bold;background-color:#dcf1fa;">
								<td colspan="3" style="text-align:right;padding-right:5px;border:1px solid black!important;">Total VAT included</td>
								<td style="text-align:right;padding-right:5px;border:1px solid black!important;"><?=number_format($total_sum_order_vat_included,0,'.',',')?>&#8362;</td>
								<td style="text-align:right;padding-right:5px;border:1px solid black!important;"><?=number_format($total_sum_paid_vat_included,0,'.',',')?>&#8362;</td>
								<td style="text-align:right;padding-right:5px;border:1px solid black!important;"><?=number_format($total_balance_vat_included,0,'.',',')?>&#8362;</td>
								<td width="20px" style="background-color:white;">&nbsp;</td>
								<td style="text-align:right;padding-right:5px;background-color:#dcf1fa;border:1px solid black!important;"><?=number_format($total_balance_vat_included,0,'.',',')?>&#8362;</td>  
								<td style="text-align:right;padding-right:5px;background-color:#dcf1fa;border:1px solid black!important;"><?=number_format($total_to_pay,0,'.',',')?>&#8362;</td>
							</tr>
						</table>		
					</div>
				</div>
					
				<div class="row" style="text-align:center;">
					<div class="col-md-12">
						<a href="budget_report.php?project_id=<?=@$project_id?>" target="_blank">
							<input type="button" value="Budget Report" class="btn marginTop20" />
						</a>
					</div>
				</div>
			</div>
		</form> 
	</body>
</html>

<style>
table,th,td {
    border: none!important;
}

.title {
	font-size: 22px;
	color: #349feb;
	margin-top: 20px;
	text-align: center;
}

a {
   color: inherit;
}

.marginTop20 {
  margin-top: 20px;
}

.btn {
   color: white;
   background-color: #218FD6;
}

.btn:hover {
   color: white;
   background-color: #3370d6;
}
</style>