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

$query = $mysqli->prepare("SELECT o.id AS id,ps.id_supplier AS id_supplier,o.sum_order AS sum_order,o.pdf_order AS pdf_order,o.signature_date AS signature_date,
                          o.description AS description,s.name AS name,sfow.name AS sfow_name
                          FROM dne_orders o
						  LEFT JOIN dne_projects_suppliers ps ON o.id_projects_suppliers = ps.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
						  WHERE ps.id_project = ? ORDER BY o.signature_date DESC,s.type,s.name");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$orders_num_rows = $query->num_rows;
$orders = fetch($query);
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
						   <?=@$project->name.'<br/>Orders Report <br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4)?>
						</a>	
					</div>					
			    </div>
				<div class="row" style="text-align:center;">
					<div class="col-md-12">
						<input type="button" value="Add new order" class="btn marginTop20 mb-2" onclick="location.href='add_order.php?id=0&project_id=<?=@$project_id?>';" />
					</div>
				</div>
				
				<br/>

				<?php if($orders_num_rows > 0) { ?>		
					<div class="row" style="font-size:14px;text-align:center;">
						<div align="center" class="col-md-12 mx-2">
							<table id="orders_list" border="1">						
								<tr style="background-color:silver;height:50px;">
									<th width="30px;" style="text-align:center;">&#x2116;</th>
									<th width="90px;" style="text-align:center;">Signature <br/> date</th>
									<th width="130px;" style="text-align:center;">Supplier <br/> Name</th>
									<th width="130px;" style="text-align:center;">Supplier <br/> Domain</th>
									<th width="250px" style="text-align:center;">Description</th>
									<th width="120px;" style="text-align:center;">Total <br/> orders</th>
									<th width="40px;" style="text-align:center;">&nbsp;</th>
									<th width="40px;" style="text-align:center;">&nbsp;</th>
								</tr>
			
								<?php
								$count = 0;
								$total_sum_orders = 0;
								foreach($orders as $item) {
									$count++;
									
									$signature_date = '';
									if(@$item->signature_date != '0000-00-00')
										$signature_date = substr(@$item->signature_date,8,2).'/'.substr(@$item->signature_date,5,2).'/'.substr(@$item->signature_date,2,2);
									
									$sum_order = '';
									if(@$item->sum_order != 0.00)
										$sum_order = number_format(@$item->sum_order,0,'.',',');
									
									$total_sum_orders += $item->sum_order;
									?>
									<tr style="height:35px;">
										<td style="text-align:center;"><?=@$count?></td>
										<td style="text-align:left;padding-left:5px;"><?=@$signature_date?></td>
										<td style="text-align:left;padding-left:5px;"><?=@$item->name?></td>	
										<td style="text-align:left;padding-left:5px;"><?=@$item->sfow_name?></td>
										<td style="text-align:left;padding-left:5px;"><?=@$item->description?></td>										
										<td style="text-align:right;padding-right:5px;""><?php if(@$item->pdf_order != '') { ?><a href="uploads/<?=@$item->pdf_order?>" title="View PDF" target="_blank"><?=@$sum_order.'&nbsp;&#8362;'?></a><?php } else { echo @$sum_order.'&nbsp;&#8362;'; }?></td>
										<td style="text-align:center;"><img src="images/edit-button.svg" style="cursor:pointer;" title="Edite" onclick="location.href='add_order.php?id=<?=@$item->id?>&project_id=<?=@$project_id?>'" /></td>									
										<td style="text-align:center;"><img src="images/delete.svg" style="cursor:pointer;" title="Remove" onclick="return removeOrder(<?=@$item->id?>);" /></td>	
									</tr>
									<?php
								}
								$total_sum_orders = number_format(@$total_sum_orders,2,'.',',');
								?>
								<tr style="height:30px;background-color:#dcf1fa;">
								   <td colspan="5" style="text-align:right;padding-right:5px;"><strong>Total orders</strong></td>
								   <td style="text-align:right;padding-right:5px;background-color:#dcf1fa;"><strong><?=@$total_sum_orders?>&nbsp;&#8362;</strong></td>
								   <td colspan="2">&nbsp;</td>
								</tr>
							</table>		
						</div>
					</div>
					
					<div class="row" style="margin-top:20px;text-align:center;">
						<div class="col-md-12">
							<a href="orders_report.php?project_id=<?=@$project_id?>" target="_blank">
								<input type="button" value="Orders Report" class="btn" />
							</a>
						</div>
					</div>
				<?php } ?>
			</div>
		</form> 
	</body>
</html>

<script>
function removeOrder(id) {
	if(confirm("Are you sure to remove this order?")) {
        var form_data = new FormData();	
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'order_delete.php',
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
tr:nth-of-type(even) {
  background-color: #dedede!important;
}

tr:last-of-type {
  background-color:#dcf1fa!important;
}

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