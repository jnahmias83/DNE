<?php
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$id = @$_GET['id'];

$domain_color = '#000000';
$domain_bgcolor = '#ffffff';

if($id > 0) {
	$query = $mysqli->prepare("SELECT * FROM dne_sup_field_of_work WHERE id = ?");
	$query->bind_param("i",$id );
	$query->execute();
	$query->store_result();
	$domain = fetch_unique($query);
	
	if($domain->color != '')
		$domain_color = $domain->color;
	
	if($domain->bgcolor != '')
		$domain_bgcolor = $domain->bgcolor;
} 
?>

		<form method="post" action="" enctype="multipart/form- data" class="form-inline">
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
					<div class="row title mx-2" style="margin-top:30px;">
						<div class="col-md-12" style="font-size:20px;">
							<span>Add a domain</span>
						</div>
					</div>
				<?php } ?>
				
				<div class="row mx-2" style="margin-top:10px;text-align:center;">
					<div class="col-md-12">
						<?php
						if($id == 0) { ?>
							<select id="sup_type" class="form-control" style="margin-top:10px;padding-left:5px;width:60%;border-color:initial;">
							   <option value="S">Supplier</option>
							   <option value="D">Designer</option>		 
							</select>
						<?php }
						else { 
								$type = 'Supplier';
								if(@$domain->sup_type == 'D')
								   $type = 'Designer';
						?>
							<input type="text" class="form-control" style="margin-top:10px;width:60%;" name="supplier_type" id="supplier_type" value="<?=@$type?>" disabled="true" />
						<?php }
						?>                  					
					</div>
				</div>

				<div class="row mx-2" style="margin-top:10px;">
					<div class="col-md-12">
						<strong>Name:</strong>
						<input type="text" class="form-control my-2" name="domain_name" id="domain_name" style="width:60%;" placeholder="*Name" value="<?=@$domain->name?>" />
					</div>
				</div>			
				
				<div class="row mx-2" style="margin-top:10px;">
					<div class="col-md-12">
						<strong>Name He:</strong>
						<input type="text" class="form-control my-2" name="domain_name_he" id="domain_name_he" style="width:60%;" placeholder="*Name He" value="<?=@$domain->name_he?>" />
					</div>
				</div>			
				
				<div class="row mx-2" style="margin-top:10px;">
					<div class="col-md-12">
						<strong>Nickname:</strong>
						<input type="text" class="form-control my-2" name="domain_nickname" id="domain_nickname" style="width:60%;" placeholder="*Nickname" value="<?=@$domain->nickname?>" />
					</div>
				</div>	

				<div class="row mx-2" style="margin-top:10px;">
					<div class="col-md-12">
						<strong>Color:</strong>
						<input type="color" class="form-control my-2" name="domain_color" id="domain_color" style="width:60%;" placeholder="Color" value="<?=@$domain_color?>" />
					</div>
				</div>	

				<div class="row mx-2" style="margin-top:10px;">
					<div class="col-md-12">
						<strong>Bgcolor:</strong>
						<input type="color" class="form-control my-2" name="domain_bgcolor" id="domain_bgcolor" style="width:60%;" placeholder="Bgcolor" value="<?=@$domain_bgcolor?>" />
					</div>
				</div>				
				
				<div class="row">
					<div class="col-md-12">
						<div id="div_message_alert_down" style="margin-top:10px;"></div>	
						<input type="button" id="cancel_btn" class="btn marginTop20 bgColorBlack colorWhite marginRight8 mb-2" value="Cancel" />
						<input type="button" id="save_btn" name="save_btn" class="btn marginTop20 colorWhite bgColorBlue mb-2" 
						value="Save" />						
					</div>
				</div>
            </div>			
		</form>       
    </body>
</html>

<script>
$('#save_btn').click (function (e){ 
	var form_data = new FormData();	
	form_data.append('id',$('#id').val());
	form_data.append('sup_type',$('#sup_type').val());
	form_data.append('domain_name',$('#domain_name').val());
	form_data.append('domain_name_he',$('#domain_name_he').val());
	form_data.append('domain_nickname',$('#domain_nickname').val());
	form_data.append('domain_color',$('#domain_color').val());
	form_data.append('domain_bgcolor',$('#domain_bgcolor').val());
	$.ajax({
		type: 'POST',
		url: 'domain_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){  
			if(data == 'empty')	{
				if($('#domain_name').val().length == 0)			
					$('#domain_name').css('border-color','red');
				else if(!($('#domain_name').val().length == 0))
					$('#domain_name').css('border-color','initial');	
				
				if($('#domain_name_he').val().length == 0)			
					$('#domain_name_he').css('border-color','red');
				else if(!($('#domain_name_he').val().length == 0))
					$('#domain_name_he').css('border-color','initial');	
				
				if($('#domain_nickname').val().length == 0)			
					$('#domain_nickname').css('border-color','red');
				else if(!($('#domain_nickname').val().length == 0))
					$('#domain_nickname').css('border-color','initial');	
				
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;>Please fill all the mandatory fields</span>"); 
			}
			else
				location.href = 'domains.php';		
		},
	});												       			   
})

$('#cancel_btn').click(function(){
    location.href = 'domains.php';	
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