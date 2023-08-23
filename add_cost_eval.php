<?php
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$id = @$_GET['id'];
$project_id = @$_GET['project_id'];

$sup_type = 'S';
$des_type = 'D';

$display_suppliers_list = 'none';
$display_designers_list = 'none';

if($id > 0) {
	$query = $mysqli->prepare("SELECT * FROM dne_budget_costs_eval WHERE id = ?");
    $query->bind_param("i",$id);
    $query->execute();
    $query->store_result();
    $budget_cost_eval = fetch_unique($query);

    $query = $mysqli->prepare("SELECT * FROM dne_sup_field_of_work WHERE id = ?");
    $query->bind_param("i",$budget_cost_eval->id_field_of_work);
    $query->execute();
    $query->store_result();
    $field_of_work = fetch_unique($query);
	
	$query = $mysqli->prepare("SELECT cem.id AS cem_id,cem.id_budget_costs_eval AS id_budget_costs_eval, cem.description AS description,cem.unit AS unit,cem.quantity AS quantity,cem.price AS price 
	                          FROM dne_costs_eval_modul cem
							  LEFT JOIN dne_budget_costs_eval bce ON cem.id_budget_costs_eval = bce.id
							  WHERE bce.id = ?");
    $query->bind_param("i",$id);
    $query->execute();
    $query->store_result();
	$costs_eval_modul_num_rows = $query->num_rows;
    $costs_eval_modul = fetch($query);
	
	$display_cem_btn = 'none';
	$display_costs_eval_table = 'none';
	
	if($costs_eval_modul_num_rows == 0)
	   $display_cem_btn = 'block';  
  
	if($costs_eval_modul_num_rows > 0) 
	   $display_costs_eval_table = 'block';
	
	if(@$field_of_work->sup_type == 'S') 
	    $display_suppliers_list = 'block';
	else if(@$field_of_work->sup_type == 'D') 
		$display_designers_list = 'block';
}

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT DISTINCT name,id FROM dne_sup_field_of_work WHERE sup_type = ? ORDER BY name ASC");
$query->bind_param("s",$sup_type);
$query->execute();
$query->store_result();
$sup_domains = fetch($query);

$query = $mysqli->prepare("SELECT DISTINCT name,id FROM dne_sup_field_of_work WHERE sup_type = ? ORDER BY name ASC");
$query->bind_param("s",$des_type);
$query->execute();
$query->store_result();
$des_domains = fetch($query);
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
							Add a cost evaluation for the project <span style="color:#5bbd8d;"><?=@$project->name?></span>
						</div>
					</div>
				<?php } 
				else if($id > 0) { ?>
					<div class="row title">
						<div class="col-md-12">
							<span style="color:#5bbd8d;"><?=@$budget_cost_eval->name?></span>
						</div>
					</div>
				<?php } ?>	
				
				<div class="row" style="margin-top:20px;font-size:14px;">
					<div class="col-md-12">
						<input type="radio" id="domain_type" name="domain_type" value="S" onclick="displayDomainsList();" <?php if(@$field_of_work->sup_type == 'S') echo 'checked';?> />&nbsp;Supplier&nbsp;&nbsp;
						<input type="radio" id="domain_type" name="domain_type" value="D" onclick="displayDomainsList();" <?php if(@$field_of_work->sup_type == 'D') echo 'checked';?> />&nbsp;Designer
					</div>
				</div>
				
				<div id="div_suppliers_domains" style="display:<?=@$display_suppliers_list?>">
					<div class="row" style="margin-top:20px;font-size:14px;">
						<div class="col-md-12">
							<strong>Suppliers domains:</strong>
						</div>
					</div>		
							
					<div class="row" style="margin-top:10px;">
						<div class="col-md-8">
							<select id="suppliers" class="form-control" style="width:200px;">							
								<option value="0">--- Choose a domain ---</option>
								<?php 
									foreach($sup_domains as $item) {
									?>
										<option value="<?=@$item->id?>" <?php if(@$item->id == @$budget_cost_eval->id_field_of_work) echo 'selected';?>>
											<?=@$item->name?>
										</option>
										<?php
									}
								?>										
							</select>
						</div>
					</div>
				</div>
					
				<div id="div_designers_domains" style="display:<?=@$display_designers_list?>">
					<div class="row" style="margin-top:20px;font-size:14px;">
						<div class="col-md-12">
							<strong>Designers domains:</strong>
						</div>
					</div>		
								
					<div class="row" style="margin-top:10px;">
						<div class="col-md-8">
							<select id="designers" class="form-control" style="width:200px;">		
								<option value="0">--- Choose a domain ---</option>					
								<?php 
									foreach($des_domains as $item) {
									?>
										<option value="<?=@$item->id?>" <?php if(@$item->id == @$budget_cost_eval->id_field_of_work) echo 'selected';?>>
											<?=@$item->name?>
										</option>
										<?php
									}
								?>										
							</select>
						</div>
					</div>
				</div>
				
				<div class="row" style="margin-top:20px;">
					<div class="col-md-4" style="padding-top:2px;">
						<?php
						if($id > 0) { ?>
							<strong>Description:</strong>
							<br/>
						<?php } ?>					
						<textarea class="form-control" name="description" id="description" rows="5" cols="18" placeholder="Description..."><?=@$budget_cost_eval->description?></textarea>
					</div>
				</div>
				
				<div class="row" style="margin-top:20px;">
					<div class="col-md-11">
						<span style="font-size:14px;font-weight:bold;">PDF evaluation:</span>
						<br/>					
						<input type="file" class="form-control" name="pdf_evaluation" id="pdf_evaluation" style="margin-top:10px;" />
						<?php if($id > 0) { ?>&nbsp;<a href="uploads/<?=@$budget_cost_eval->pdf_evaluation?>" target="_blank"><?=@$budget_cost_eval->pdf_evaluation?></a><?php } ?>			
					</div>
				</div>
				
				<div class="row" style="margin-top:20px;display:<?=@$display_cem_btn?>">
					<div class="col-md-11">
						<input type="button" id="add_costs_eval_modul_btn" class="btn btn-primary mb-2" value="Add costs evaluation modul" />
					</div>
				</div>
				
				<?php if($id > 0) { ?>
					<div id="costs_eval_modul_div" style="margin-top:10px;border:2px solid blue;width:90%;direction:rtl;">
						<div class="row" style="padding-top:10px;">
							<div class="col-md-11 mx-3">
								<textarea class="form-control" id="cost_eval_description" name="cost_eval_description" rows="3" cols="26" placeholder="תיאור ..."></textarea>
							</div>
						</div>
						<div class="row" style="padding-top:10px;">
							<div class="col-md-11 mx-3">
								<input type="text" id="cost_eval_unit" name="cost_eval_unit" style="width:30%;" placeholder="יחידה" />
							</div>
						</div>
						<div class="row" style="padding-top:10px;">
							<div class="col-md-11 mx-3">
								<input type="number" id="cost_eval_quantity" name="cost_eval_quantity" style="width:30%;" placeholder="כמות" />
							</div>
						</div>
						<div class="row" style="padding-top:10px;">
							<div class="col-md-11 mx-3">
								<input type="text" id="cost_eval_price" name="cost_eval_price" style="width:30%;" placeholder="מחיר" />
							</div>
						</div>
						<div class="row" style="margin-top:10px;margin-bottom:10px;">
							<div class="col-md-11 mx-3">
							<div id="ce_div_message_alert_down"></div>
							   <input type="button" id="save_add_cost_eval_btn" style="margin-top:10px;" class="btn bgColorBlue colorWhite mb-2" value="שמור" />
							</div>
						</div>
						<div class="row" style="font-size:14px;display:<?=@$display_costs_eval_table?>" >
							<div class="col-md-12 mx-3" style="padding-top:20px;margin-bottom:20px;">
								<table border="1" cellpadding="5" cellspacing="5">
									<tr style="height:50px;background-color:silver;">
										<th style="text-align:center;" width="40px;">מס'</th>
										<th style="text-align:center;" width="200px;">תיאור</th>
										<th style="text-align:center;" width="60px;">יחידה</th>
										<th style="text-align:center;" width="80px;">כמות</th>
										<th style="text-align:center;" width="100px;">מחיר</th>
										<th style="text-align:center;" width="100px;">סך מחיר</th>
										<th width="40px;">&nbsp;</th>
										<th width="40px;">&nbsp;</th>
									</tr>
									
									<?php 
									$count = 0;
									$sum_total_price_vat_excluded = 0;
									$sum_total_price_vat = 0;
									$sum_total_price_vat_included = 0;
									
									foreach($costs_eval_modul as $item) { 
										$count++;
										
										$price = number_format(@$item->price,2,'.',',');
										$total_price = ($item->quantity)*($item->price);
										$sum_total_price_vat_excluded += $total_price;
										$sum_total_price_vat_included += 1.17*$total_price;
										$sum_total_price_vat += 0.17*$total_price;
										$total_price = number_format($total_price,2,'.',',');
									?>
									<tr height="35px;">
									   <td style="text-align:center;"><?=@$count?></td>
									   <td style="text-align:right;"><textarea id="cem_elem_description_<?=@$item->cem_id?>" style="width:100%;padding-right:5px;"><?=@$item->description?></textarea></td>
									   <td style="text-align:left;"><input type="text" id="cem_elem_unit_<?=@$item->cem_id?>" style="width:100%;" value="<?=@$item->unit?>" /></td>
									   <td style="text-align:right;"><input type="number" id="cem_elem_quantity_<?=@$item->cem_id?>" style="width:100%;" value="<?=@$item->quantity?>" /></td>
									   <td style="text-align:right;"><input type="text" id="cem_elem_price_<?=@$item->cem_id?>" style="width:100%;" value="<?=@$item->price?>" /></td>
									   <td style="text-align:right;padding-right:5px;"><?=@$total_price?></td>
									   <td style="text-align:center;"><img src="images/edit-button.svg" style="cursor:pointer;" title="Edite" onclick="editeCostsEvalModul(<?=@$item->cem_id?>);" /></td>
									   <td style="text-align:center;"><img src="images/delete.svg" style="cursor:pointer;" title="Remove" onclick="return removeCostsEvalModul(<?=@$item->cem_id?>);" /></td>	
									</tr>
									<?php } 
									$sum_total_price_vat_excluded = number_format($sum_total_price_vat_excluded,2,'.',',');
									$sum_total_price_vat = number_format($sum_total_price_vat,2,'.',',');
									$sum_total_price_vat_included = number_format($sum_total_price_vat_included,2,'.',',');
									?>
									<tr height="35px;">
										<td colspan="5" style="text-align:right;padding-right:5px;background-color:silver;"><strong>סכום סך מחיר לא כולל מע''ם</strong></td>
										<td style="text-align:right;padding-right:5px;"><strong><?=@$sum_total_price_vat_excluded?></strong></td>
										<td colspan="2">&nbsp;</td>
									</tr>
									<tr height="35px;">
										<td colspan="5" style="text-align:right;padding-right:5px;background-color:silver;"><strong>סכום סך מחיר מע''ם (17%)</strong></td>
										<td style="text-align:right;padding-right:5px;"><strong><?=@$sum_total_price_vat?></strong></td>
										<td colspan="2">&nbsp;</td>
									</tr>
									<tr height="35px;">
										<td colspan="5" style="text-align:right;padding-right:5px;background-color:silver;"><strong>סכום סך מחיר כולל מע''ם</strong></td>
										<td style="text-align:right;padding-right:5px;"><strong><?=@$sum_total_price_vat_included?></strong></td>
										<td colspan="2">&nbsp;</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				<?php } ?>
				
				<div class="row" style="margin-top:20px;">
					<div class="col-md-11" style="padding-top:2px;">
						<?php
						if($id > 0) { ?>
							<strong>Evaluation cost:</strong>
							<br/>
						<?php } ?>					
						<input type="text" class="form-control" style="150px" name="evaluation_cost" id="evaluation_cost" placeholder="Evaluation cost" value="<?=@$budget_cost_eval->evaluation_cost?>" />	
					</div>
				</div>
				
				<div class="row" style="margin-top:20px;">
					<div class="col-md-4" style="padding-top:2px;">
						<span style="font-size:14px;font-weight:bold;">Evaluation date:</span>
						<br/>						
						<input type="date" class="form-control" name="evaluation_date" id="evaluation_date" style="margin-top:10px;" value="<?=@$budget_cost_eval->evaluation_date?>" />	
					</div>
				</div>
				
				<div class="row">
					<div class="col-md-4">
						<div id="div_message_alert_down" style="margin-top:10px;"></div>	
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
var pdf_evaluation;
var domain_type;

$(document).on('change','#pdf_evaluation',function() {
    pdf_evaluation = $('#pdf_evaluation')[0].files[0];
});

function displayDomainsList() {
	if($("input:radio[name='domain_type']").is(':checked'))
	    domain_type = $('#domain_type:checked').val();

	if(domain_type == 'S') {
		$('#div_suppliers_domains').css('display','block');
		$('#div_designers_domains').css('display','none');
	}
	else if(domain_type == 'D') {
		$('#div_suppliers_domains').css('display','none');
		$('#div_designers_domains').css('display','block');
	}
}

$('#add_costs_eval_modul_btn').click (function (e){ 
    if($("input:radio[name='domain_type']").is(':checked'))
	    domain_type = $('#domain_type:checked').val();
	
	var id_field_of_work = 0;
	if(domain_type == 'S')
	   id_field_of_work = $('#suppliers').val();
    else if(domain_type == 'D')
	   id_field_of_work = $('#designers').val();
	
	var form_data = new FormData();
	form_data.append('fromAddCem','Yes');
	form_data.append('id',0);
	form_data.append('id_project',$('#project_id').val());
	form_data.append('id_field_of_work',id_field_of_work);
	form_data.append('description',$('#description').val());
	form_data.append('pdf_evaluation',pdf_evaluation);
	form_data.append('evaluation_cost',$('#evaluation_cost').val());
	form_data.append('evaluation_date',$('#evaluation_date').val());
	
	$.ajax({
		type: 'POST',
		url: 'cost_eval_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){			
              location.href = 'add_cost_eval.php?id='+data+'&project_id='+$('#project_id').val();		
		},
	});		       			   
})

function editeCostsEvalModul(cem_id) {
	var cem_elem_description = '#cem_elem_description_'+cem_id;
	var cem_elem_unit = '#cem_elem_unit_'+cem_id;
	var cem_elem_quantity = '#cem_elem_quantity_'+cem_id;
	var cem_elem_price = '#cem_elem_price_'+cem_id;
	
	var form_data = new FormData();
	form_data.append('action','update');
	form_data.append('id',cem_id);
	form_data.append('description',$(cem_elem_description).val());
	form_data.append('unit',$(cem_elem_unit).val());
	form_data.append('quantity',$(cem_elem_quantity).val());
	form_data.append('price',$(cem_elem_price).val());
	
	$.ajax({
		type: 'POST',
		url: 'cost_eval_modul_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){ 
			if(data == 'empty')	{
				if($(cem_elem_unit).val().length == 0)			
					$(cem_elem_unit).css('border-color','red');
				else if(!($(cem_elem_unit).val().length == 0))
					$(cem_elem_unit).css('border-color','initial');	
				
				if($(cem_elem_quantity).val().length == 0)			
					$(cem_elem_quantity).css('border-color','red');
				else if(!($(cem_elem_quantity).val().length == 0))
					$(cem_elem_quantity).css('border-color','initial');	
				
				if($(cem_elem_price).val().length == 0)			
					$(cem_elem_price).css('border-color','red');
				else if(!($(cem_elem_price).val().length == 0))
					$(cem_elem_price).css('border-color','initial');	
				
				$('#ce_div_message_alert_down').html("<span style=color:red;font-size:13px;>Please fill all the mandatory fields</span>"); 
			}
			else
				location.reload();
		},
	});
}

function removeCostsEvalModul(cem_id) {
	if(confirm("Are you sure to remove this supplier?")) {
        var form_data = new FormData();	
		form_data.append('cem_id',cem_id);			
		$.ajax({
			type: 'POST',
			url: 'cost_eval_modul_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
				location.reload();			
			},
		});		
    }
    return false;
}

$('#save_add_cost_eval_btn').click (function (e){ 
	var form_data = new FormData();
	form_data.append('action','insert');
	form_data.append('id_budget_costs_eval',$('#id').val());
	form_data.append('description',$('#cost_eval_description').val());
	form_data.append('unit',$('#cost_eval_unit').val());
	form_data.append('quantity',$('#cost_eval_quantity').val());
	form_data.append('price',$('#cost_eval_price').val());
	
	$.ajax({
		type: 'POST',
		url: 'cost_eval_modul_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){ 
			if(data == 'empty')	{
				if($('#cost_eval_unit').val().length == 0)			
					$('#cost_eval_unit').css('border-color','red');
				else if(!($('#cost_eval_unit').val().length == 0))
					$('#cost_eval_unit').css('border-color','initial');	
				
				if($('#cost_eval_quantity').val().length == 0)			
					$('#cost_eval_quantity').css('border-color','red');
				else if(!($('#cost_eval_quantity').val().length == 0))
					$('#cost_eval_quantity').css('border-color','initial');	
				
				if($('#cost_eval_price').val().length == 0)			
					$('#cost_eval_price').css('border-color','red');
				else if(!($('#cost_eval_price').val().length == 0))
					$('#cost_eval_price').css('border-color','initial');	
				
				$('#ce_div_message_alert_down').html("<span style=color:red;font-size:13px;>Please fill all the mandatory fields</span>"); 
			}
			else
				location.reload();
		},
	});												       			   
})

$('#save_btn').click (function (e){ 
    if($("input:radio[name='domain_type']").is(':checked'))
	    domain_type = $('#domain_type:checked').val();
	
	var form_data = new FormData();	
	form_data.append('id',$('#id').val());
	form_data.append('id_project',$('#project_id').val());
	
	if(domain_type == 'S')
	   form_data.append('id_field_of_work',$('#suppliers').val());
    else if(domain_type == 'D')
	   form_data.append('id_field_of_work',$('#designers').val());
	form_data.append('description',$('#description').val());
	form_data.append('pdf_evaluation',pdf_evaluation);
	form_data.append('evaluation_cost',$('#evaluation_cost').val());
	form_data.append('evaluation_date',$('#evaluation_date').val());
	
	$.ajax({
		type: 'POST',
		url: 'cost_eval_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){  
		   location.href = 'budget_costs_eval.php?project_id='+$('#project_id').val();		
		},
	});										       			   
})

$('#cancel_btn').click(function(){
    location.href = "budget_costs_eval.php?project_id="+$('#project_id').val();
})
</script>

<style>
table,th,td {
   border:1px solid black;
}

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