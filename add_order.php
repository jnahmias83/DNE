<?php
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$id = @$_GET['id'];
$project_id = @$_GET['project_id'];
$ps_id = @$_GET['ps_id'];


$query = $mysqli->prepare("SELECT o.id AS id,o.id_projects_suppliers AS id_projects_suppliers,o.sum_order AS sum_order,o.pdf_order AS pdf_order,o.vat AS vat,o.signature_date AS signature_date,o.description AS description
						  FROM dne_orders o
						  LEFT JOIN dne_projects_suppliers ps ON o.id_projects_suppliers = ps.id
						  WHERE o.id = ?");
$query->bind_param("i",$id);
$query->execute();
$query->store_result();
$order = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);   

$query = $mysqli->prepare("SELECT ps.id AS id,s.name AS name
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? 
                          ORDER BY name ASC");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$suppliers = fetch($query);   

$query = $mysqli->prepare("SELECT * FROM dne_vat");
$query->execute(); 
$query->store_result();
$vat = fetch_unique($query);   
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
						<div class="col-md-12">
							<span>Add an order for the project</span> <span style="color:#5bbd8d;"><?=@$project->name?></span>
						</div>
					</div>
				<?php } ?>

				<div class="row" style="margin-top:20px;font-size:14px;">
					<div class="col-md-12">
					<strong>Suppliers:</strong>
					</div>
				</div>		
							
				<div class="row" style="margin-top:10px;">
					<div class="col-md-8">
						<select id="suppliers" class="form-control" style="width:170px;">							
							<?php 
								foreach($suppliers as $item) {
								?>
									<option value="<?=@$item->id?>" <?php if(($id == 0 && $item->id == @$ps_id) ||($id > 0 && $item->id == @$order->id_projects_suppliers)) echo "selected";?>>
										<?=@$item->name?>
									</option>
									<?php
								}
							?>										
						</select>
					</div>
				</div>
				
				<div class="row" style="margin-top:20px;">
					<div class="col-md-11">
						<?php
						if($id > 0) { ?>
							<strong>Total order (VAT excluded) - סכום ההזמנה לא כולל מע''ם:</strong>
							<br/>
						<?php } ?>					
						<input type="text" class="form-control" style="150px" name="sum_order" id="sum_order" placeholder="*Total order (VAT excluded) - סכום ההזמנה לא כולל מע''ם" value="<?=@$order->sum_order?>" />	
					</div>
				</div>
				
				<div class="row" style="margin-top:20px;">
					<div class="col-md-11">
						<span style="font-size:14px;font-weight:bold;">PDF order:</span>
						<br/>					
						<input type="file" class="form-control" name="pdf_order" id="pdf_order" style="margin-top:10px;" />
						<?php if($id > 0) { ?>&nbsp;<a href="uploads/<?=@$order->pdf_order?>" target="_blank"><?=@$order->pdf_order?></a><?php } ?>			
					</div>
				</div>

				<div class="row" style="margin-top:20px;">
					<div class="col-md-11">
						<strong>VAT:</strong>
						&nbsp;						
						<input type="text" style="150px;" name="vat" id="vat" placeholder="*VAT" value="<?php if($id == 0) echo @$vat->vat;else echo $order->vat?>" />&nbsp;%	
					</div>
				</div>			
				
				<div class="row" style="margin-top:20px;">
					<div class="col-md-4">
						<span style="font-size:14px;font-weight:bold;">Signature date:</span>
						<br/>						
						<input type="date" class="form-control" name="signature_date" id="signature_date" style="margin-top:10px;" value="<?=@$order->signature_date?>" />	
					</div>
				</div>
				
				<div class="row" style="margin-top:20px;">
					<div class="col-md-4">
						<?php
						if($id > 0) { ?>
							<strong>Description:</strong>
							<br/>
						<?php } ?>					
						<textarea class="form-control" name="description" id="description" rows="5" cols="18" placeholder="Description..."><?=@$order->description?></textarea>
					</div>
				</div>
				
				<div class="row">
					<div class="col-md-4">
						<div id="div_message_alert_down" style="margin-top:10px;"></div>	
						<input type="button" id="cancel_btn" class="btn marginTop5 bgColorBlack colorWhite marginRight8 mb-2" value="Cancel" />
						<input type="button" id="save_btn" name="save_btn" class="btn marginTop5 bgColorBlue colorWhite mb-2" 
						value="Save" />						
					</div>
				</div>				
			</div>
		</form>       
    </body>
</html>

<script>
var pdf_order;

$(document).on('change','#pdf_order',function() {
    pdf_order = $('#pdf_order')[0].files[0];
});

$('#suppliers').chosen();

$('#save_btn').click (function (e){ 
	var form_data = new FormData();	
	form_data.append('id',$('#id').val());
	form_data.append('id_projects_suppliers',$('#suppliers').val());
	form_data.append('sum_order',$('#sum_order').val());
	form_data.append('pdf_order',pdf_order);
	form_data.append('vat',$('#vat').val());
	form_data.append('signature_date',$('#signature_date').val());
	form_data.append('description',$('#description').val());
	$.ajax({
		type: 'POST',
		url: 'order_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){  
			if(data == 'empty')	{
				if($('#sum_order').val().length == 0)			
					$('#sum_order').css('border-color','red');
				else if(!($('#sum_order').val().length == 0))
					$('#sum_order').css('border-color','initial');	

				if($('#signature_date').val().length == 0)			
					$('#signature_date').css('border-color','red');
				else if(!($('#signature_date').val().length == 0))
					$('#signature_date').css('border-color','initial');		
				
				if($('#pdf_order').val().length == 0)			
					$('#pdf_order').css('border-color','red');
				else if(!($('#pdf_order').val().length == 0))
					$('#pdf_order').css('border-color','initial');	
				
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;>Please fill all the mandatory fields</span>"); 
			}
			else
				location.href = 'orders.php?project_id='+$('#project_id').val();		
		},
	});												       			   
})

$('#cancel_btn').click(function(){
    location.href = "orders.php?project_id="+$('#project_id').val();	
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

.marginTop5 {
  margin-top: 5px;
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