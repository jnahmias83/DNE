<?php
include 'include/header.php';
include 'functions/functions.php';

$id = @$_GET['id'];
$type_sup = @$_GET['type_sup'];

$sup_type = 'S';
$des_type = 'D';

$title_page = 'Add supplier';
if($type_sup == 'D')
   $title_page = 'Add designer';
 
if($id > 0) {
	$query = $mysqli->prepare("SELECT * FROM dne_suppliers WHERE id = ?");
	$query->bind_param("i",$id);
	$query->execute();
	$query->store_result();
	$supplier = fetch_unique($query);
}

$query = $mysqli->prepare("SELECT DISTINCT name,id FROM dne_sup_field_of_work WHERE sup_type = ? ORDER BY name ASC");
$query->bind_param("s",$sup_type);
$query->execute();
$query->store_result();
$sup_field_of_work = fetch($query);

$query = $mysqli->prepare("SELECT DISTINCT name,id FROM dne_sup_field_of_work WHERE sup_type = ? ORDER BY name ASC");
$query->bind_param("s",$des_type);
$query->execute();
$query->store_result();
$des_field_of_work = fetch($query);
?>          

		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" /> 
			<input type="hidden" id="type" value="<?=@$type_sup?>" />
		
			<br/>

            <div class="row" style="margin-top:25px;text-align:center;">
			    <div class="col-md-12">
				    <img src="images/davidnahmias_logo.png" width="170px" height="170px" />
				</div>
			</div>
			
			<div class="container">
			   <?php
				if($id == 0) { ?>
					<div class="row" style="margin-top:20px;">
						<div class="col-md-12 title">
							<?=@$title_page?>
						</div>
					</div>
				<?php }
				else { ?>
					<div class="row" style="margin-top:20px;">
						<div class="col-md-12" style="font-size:26px;">
							<span style="color:#5bbd8d;"><?=@$supplier->name?></span>
						</div>
					</div>
				<?php }	?>				
				
				<div class="row" style="margin-top:20px;">
					<div class="col-md-4">
						<?php
						if($id > 0) { ?>
							<strong>Name:&nbsp;&nbsp;</strong>
							<br/>
						<?php } ?>					
						<input type="text" class="form-control" style="width:100%;" name="supplier_name" id="supplier_name" placeholder="*Name" value="<?=@$supplier->name?>" />	
					</div>
				</div>
				
				<div class="row" style="margin-top:20px;">
					<div class="col-md-4">
						<?php
						if($id > 0) { ?>
							<strong>Name He:&nbsp;&nbsp;</strong>
							<br/>
						<?php } ?>					
						<input type="text" class="form-control" style="width:100%;" name="supplier_name_he" id="supplier_name_he" placeholder="Name He" value="<?=@$supplier->name_he?>" />	
					</div>
				</div>
				
				<div class="row" style="margin-top:20px;">
					<div class="col-md-4">
						<?php
						if($id > 0) { ?>
							<strong>Nickname:&nbsp;&nbsp;</strong>
							<br/>
						<?php } ?>					
						<input type="text" class="form-control" style="width:100%;" name="supplier_nickname" id="supplier_nickname" placeholder="Nickname" value="<?=@$supplier->nickname?>" />	
					</div>
				</div>
				
				<div class="row" style="margin-top:20px;">
					<div class="col-md-4">
						<strong>Type:&nbsp;&nbsp;</strong>
							<br/>
						<?php
							$type = 'Supplier';
							if(@$type_sup == 'D')
								$type = 'Designer';
						?>
						<input type="text" class="form-control" style="margin-top:10px;width:100px;" name="supplier_type" id="supplier_type" value="<?=@$type?>" disabled="true" />              					
					</div>
				</div>
				
				<div id="suppliers_list_div">
					<div class="row" style="margin-top:20px;font-size:14px;">
						<div class="col-md-12">
							<strong>Domains:</strong>
						</div>
					</div>		
							
					<div class="row" style="margin-top:10px;">
						<div class="col-md-8">
							<select id="sup_field_of_work" class="form-control" style="width:170px;">							
								<?php 
								if($type_sup == 'S') { 
									foreach($sup_field_of_work as $item) {
									?>
										<option value="<?=@$item->id?>" <?php if($item->id == @$supplier->id_field_of_work) echo "selected";?>>
											<?=@$item->name?>
										</option>
										<?php
									}
								}
								else if($type_sup == 'D') { 
									foreach($des_field_of_work as $item) {
										?>
											<option value="<?=@$item->id?>" <?php if($item->id == @$supplier->id_field_of_work) echo "selected";?>>
												<?=@$item->name?>
											</option>
											<?php
										}
								}
								?>						
							</select>
						</div>
					</div>
				</div>
						
				<br/>
				
				<div class="row" style="margin-top:20px;">
					<div class="col-md-3">
						<?php
						if($id > 0) { ?>
							<strong>Phone:&nbsp;&nbsp;</strong>
							<br/>
						<?php } ?>					
						<input type="text" class="form-control" style="width:250px;" name="supplier_phone" id="supplier_phone" placeholder="Phone" value="<?=@$supplier->phone?>" />	
					</div>
					<div class="col-md-3">
						<?php
						if($id > 0) { ?>
							<strong>Cellular:&nbsp;&nbsp;</strong>
							<br/>
						<?php } ?>					
						<input type="text" class="form-control" style="width:250px;" name="supplier_mobile" id="supplier_mobile" placeholder="Cellular" value="<?=@$supplier->mobile?>" />	
					</div>
				</div>
				
				<div class="row" style="margin-top:20px;">
					<div class="col-md-3">
						<?php
						if($id > 0) { ?>
							<strong>Email office:&nbsp;&nbsp;</strong>
							<br/>
						<?php } ?>					
						<input type="email" class="form-control" style="width:250px;" name="supplier_email_office" id="supplier_email_office" placeholder="*Email office" value="<?=@$supplier->email_office?>" />	
					</div>
				</div>
				
				<br/>
				
				<div class="row" style="margin-top:20px;font-size:18px;">
					<div class="col-md-12">
						Bank details
					</div>					
				</div>
				
				<div class="row" style="margin-top:20px;">
					<div class="col-md-3">
						<?php
						if($id > 0) { ?>
							<strong>Account owner:&nbsp;&nbsp;</strong>
							<br/>
						<?php } ?>					
						<input type="text" class="form-control" style="width:250px;" name="supplier_account_owner" id="supplier_account_owner" placeholder="Account owner" value="<?=@$supplier->bank_account_owner?>" />	
					</div>
					<div class="col-md-3">
						<?php
						if($id > 0) { ?>
							<strong>Bank:&nbsp;&nbsp;</strong>
							<br/>
						<?php } ?>					
						<input type="text" class="form-control" style="width:250px;" name="supplier_bank_name" id="supplier_bank_name" placeholder="Bank" value="<?=@$supplier->bank_name?>" />	
					</div>
					<div class="col-md-3">
						<?php
						if($id > 0) { ?>
							<strong>Branch:&nbsp;&nbsp;</strong>
							<br/>
						<?php } ?>					
						<input type="text" class="form-control" style="width:250px;" name="supplier_bank_branche" id="supplier_bank_branche" placeholder="Branch" value="<?=@$supplier->bank_branche?>" />
					</div>
				</div>
				
				<div class="row" style="margin-top:20px;">
					<div class="col-md-3">
						<?php
						if($id > 0) { ?>
							<strong>Account #:&nbsp;&nbsp;</strong>
							<br/>
						<?php } ?>					
						<input type="text" class="form-control" style="width:250px;" name="supplier_account_number" id="supplier_account_number" placeholder="Account #" value="<?=@$supplier->bank_account_number?>" />	
					</div>
					<div class="col-md-3">
						<?php
						if($id > 0) { ?>
							<strong>Swift:&nbsp;&nbsp;</strong>
							<br/>
						<?php } ?>					
						<input type="text" class="form-control" style="width:250px;" name="supplier_swift" id="supplier_swift" placeholder="Swift" value="<?=@$supplier->swift?>" />	
					</div>
					<div class="col-md-3">
						<?php
						if($id > 0) { ?>
							<strong>Iban:&nbsp;&nbsp;</strong>
							<br/>
						<?php } ?>					
						<input type="text" class="form-control" style="width:250px;" name="supplier_iban" id="supplier_iban" placeholder="Iban" value="<?=@$supplier->iban?>" />	
					</div>
				</div>
																								
				<div class="row">
					<div class="col-md-4">
						<div id="div_message_alert_down" style="margin-top:10px;"></div>	
						<input type="button" id="cancel_btn" class="btn marginTop20 bgColorBlack colorWhite marginRight8 mb-2" value="Cancel" />
						<input type="button" id="save_btn" name="save_btn"  class="btn marginTop20 colorWhite bgColorBlue mb-2" 
						value="Save" />						
					</div>
				</div>					
			</div>	
		</form>
    </body>
</html>

<script>
$('#field_of_work_name').on('change', function (){
	$('#suppliers_list_div').hide();
});

$('#sup_field_of_work').chosen();

$('#save_btn').click (function (e){   
	var form_data = new FormData();	
	form_data.append('id',$('#id').val());
	form_data.append('supplier_name',$('#supplier_name').val());
	form_data.append('supplier_name_he',$('#supplier_name_he').val());
	form_data.append('supplier_nickname',$('#supplier_nickname').val());
	form_data.append('supplier_type',$('#supplier_type').val());
	form_data.append('id_field_of_work',$('#sup_field_of_work').val());
	form_data.append('supplier_phone',$('#supplier_phone').val());
	form_data.append('supplier_mobile',$('#supplier_mobile').val());
	form_data.append('supplier_email_office',$('#supplier_email_office').val());
	form_data.append('supplier_account_owner',$('#supplier_account_owner').val());
	form_data.append('supplier_bank_name',$('#supplier_bank_name').val());
	form_data.append('supplier_bank_branche',$('#supplier_bank_branche').val());
	form_data.append('supplier_account_number',$('#supplier_account_number').val());
	form_data.append('supplier_swift',$('#supplier_swift').val());
	form_data.append('supplier_iban',$('#supplier_iban').val());
	
	$.ajax({
		type: 'POST',
		url: 'supplier_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){  
			if(data == 'empty')	{
				if($('#supplier_name').val().length == 0)			
					$('#supplier_name').css('border-color','red');
				else if(!($('#supplier_name').val().length == 0))
					$('#supplier_name').css('border-color','initial');	
				
				if($('#supplier_email_office').val().length == 0)			
					$('#supplier_email_office').css('border-color','red');
				else if(!($('#supplier_email_office').val().length == 0))
					$('#supplier_email_office').css('border-color','initial');	
				
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;>Please fill all the mandatory fields</span>"); 
			}
			else if(data == 'exists') 
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;>Please enter a domain that not already exist on the following list</span>"); 
			else {
				if($('#id').val() > 0)
				   location.href = 'suppliers.php?type='+$('#type').val();	
                else
                   location.href = 'suppliers.php?type='+$('#supplier_type').val();	
			}			   
		},
	});												       			   
})

$('#cancel_btn').click(function(){
    location.href = 'suppliers.php?type='+$('#type').val();	
})
</script>

<style>
.title {
	font-size: 22px;
	color: #349feb;
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

.marginLeft20 {
	margin-left: 20px;
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