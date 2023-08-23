<?php
session_start();
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


$query = $mysqli->prepare("SELECT p.id_projects_suppliers AS id_projects_suppliers,SUM(p.approved_amount) AS sum_approved_amount,SUM(p.paid_amount) AS sum_paid_amount,
                          SUM(p.approved_amount)-SUM(p.paid_amount) AS sum_to_pay_amount,s.name AS name,s.bank_account_owner AS bank_account_owner,s.bank_name AS bank_name,
						  s.bank_branche AS bank_branche,s.bank_account_number AS bank_account_number,s.swift AS swift,s.iban AS iban,
						  ps.is_appears_pdf_wires AS is_appears_pdf_wires
						  FROM dne_payments p 
						  LEFT JOIN dne_projects_suppliers ps on p.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id 
						  WHERE ps.id_project = ?
						  GROUP BY p.id_projects_suppliers ORDER BY s.type,s.name");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$payments = fetch($query);
?>

		<form method="post" action="" class="form-inline">	
		    <input type="hidden" id="project_id" name="project_id" value="<?=@$project->id?>" />
			
			<div class="row" style="margin-top:25px;text-align:center;">
				<div class="col-md-12">
					<img src="images/davidnahmias_logo.png" width="170px" height="170px" />
				</div>
			</div>

            <div class="container">
			    <div class="row title">	
					<div class="col-md-12">
						<a style="text-decoration:underline;" href="project_home.php?id=<?=@$project_id?>">
							<?=@$project->name."<br/>Payments wires list<br/>".substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4)?> 	
						</a>
					</div>					
			    </div>
				<div class="row" style="text-align:center;padding-top:20px;">
					<div align="center" class="col-md-12 mx-2">
						<table border="1" cellpadding="2" cellspacing="2">		
							<tr style="font-size:14px;height:30px;background-color:silver;">
								<th width="160px;" style="text-align:center;">Supplier Name</th>
								<th width="130px" style="text-align:center;">Amount to pay/ <br/> סכום לתשלום</th>
								<th width="80px;" style="text-align:center;">Swift</th>
								<th width="80px;" style="text-align:center;">Iban</th>
								<th width="80px;" style="text-align:center;">חשבון</th>
								<th width="70px;" style="text-align:center;">Branch/ <br/> סניף</th>
								<th width="70px;" style="text-align:center;">Bank/ <br/> בנק</th>
								<th width="170px;" style="text-align:center;">שם חשבון</th>	
								<th width="30px;" style="text-align:center;">&#x2116;</th>
								<th width="30px;" style="text-align:center;">&nbsp;</th>	
							</tr>

							<?php
							$count = 0;
							
							$total_sum_to_pay_amount = 0;
							
							foreach($payments as $item) {		 					
								if($item->sum_approved_amount - $item->sum_paid_amount > 0) {										
									$count++;
									
									$sum_to_pay_amount = '';
									if($item->sum_to_pay_amount > 0) {
										$sum_to_pay_amount = $item->sum_to_pay_amount;
										$total_sum_to_pay_amount +=$item->sum_to_pay_amount;
									}
									?>
									<tr style="height:25px;font-size:14px;">
										<td style="text-align:left;padding-left:5px;"><?=@$item->name?></td>
										<td style="text-align:center;"><?=number_format(@$sum_to_pay_amount,2,'.',',')?>&nbsp;&#8362;</td>
										<td style="text-align:center;"><?=@$item->swift?></td>
										<td style="text-align:center;"><?=@$item->iban?></td>
										<td style="text-align:center;"><?=@$item->bank_account_number?></td>
										<td style="text-align:center;"><?=@$item->bank_branche?></td>
										<td style="text-align:center;"><?=@$item->bank_name?></td>
										<td style="text-align:right;padding-right:5px;"><?=@$item->bank_account_owner?></td>
										<td style="text-align:center;"><?=@$count?></td>
										<td style="text-align:center;"><input type="checkbox" id="cb_<?=@$item->id_projects_suppliers?>" <?php if(@$item->is_appears_pdf_wires == 1) echo "checked";?> onclick="setIsAppearsPDFWires(<?=@$item->id_projects_suppliers?>);" /></td>									
									</tr>
									<?php
								}
							}
							?>
							<tr style="height:25px;font-size:13px;">
								<td>&nbsp;</td>
								<td style="text-align:center;background-color:#dcf1fa;"><strong><span style="font-size:11px;">Total ammount to pay:</span><br/><?=number_format(@$total_sum_to_pay_amount,2,'.',',')?>&nbsp;&#8362;</strong></td>
								<td colspan="8">&nbsp;</td>
							</tr>
						</table>	
					</div>
				</div>
				
				<div class="row" style="text-align:center;">
					<div class="col-md-3"></div>
					<div class="col-md-6">
						<div class="row marginTop20">
						   <div class="col-md-6">
								<input type="checkbox" id="add_budget_report" />&nbsp;Add budget report 
							</div>
							<div class="col-md-6">
								<input type="checkbox" id="add_suppliers_balances_reports" />&nbsp;Add suppliers balances reports
							</div>
							<!--<div class="col-md-4">
								<?php if($current_payments_num_rows > 0) { ?>
									<input type="checkbox" id="add_current_payments_report" />&nbsp;Add current payments report
								<?php } ?>
							</div>-->
					   </div>
					</div>
					<div class="col-md-3"></div>
				</div>
				<div class="row" style="text-align:center;">
					<div class="col-md-12">
						<a onclick="toPaymentsWiresReport();">
							<input type="button" value="Payments wires list PDF" class="btn marginTop20 mb-2" />
						</a>
					</div>
				</div>
			</div>
		</form>
	</body>
</html>

<script>
function setIsAppearsPDFWires(id_projects_suppliers) {
	var isChecked = $('#cb_'+id_projects_suppliers).is(':checked');
	var isAppearsPDFWires = isChecked? 1:0;
	
	var form_data = new FormData();
	form_data.append('id_projects_suppliers',id_projects_suppliers);
	form_data.append('is_appears_pdf_wires',isAppearsPDFWires);
	
	$.ajax({
		 type: 'POST',
		 url: 'set_is_appears_pdf_wires.php',
		 data: form_data,
		 cache: false,
		 processData: false,
		 contentType: false,			
		 success: function(data){ 
	         location.reload();
		 },
	 })
}

function toPaymentsWiresReport() {
	var addBudgetReport = 0;
    if($('#add_budget_report').is(":checked"))
		addBudgetReport = 1;
	
	var addSuppliersBalancesReports = 0;
    if($('#add_suppliers_balances_reports').is(":checked"))
		addSuppliersBalancesReports = 1;
	
	var addCurrentPaymentsReport = 0;
    if($('#add_current_payments_report').is(":checked"))
		addCurrentPaymentsReport = 1;
	
	window.open('payments_wires_report.php?project_id='+$('#project_id').val()+'&abr='+addBudgetReport+'&asbr='+addSuppliersBalancesReports+'&acpr='+addCurrentPaymentsReport,'_blank');
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