<?php
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$id = @$_GET['id'];

if($id > 0) {
	$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
    $query->bind_param("i",$id);
    $query->execute();
	$query->store_result();
	$project = fetch_unique($query);
	
	$type = 'E';
	
	$query = $mysqli->prepare("SELECT s.name_he,s.nickname_he
	                          FROM dne_projects_suppliers ps
							  LEFT JOIN dne_projects p ON p.id = ps.id_project
							  LEFT JOIN dne_suppliers s ON s.id = ps.id_supplier
							  WHERE p.id = ? and s.type = ?");
    $query->bind_param("is",$project->id,$type);
    $query->execute();
	$query->store_result();
	$project_supplier = fetch_unique($query);
}

$query = $mysqli->prepare("SELECT bgcolor FROM dne_global_bgcolor_new_task LIMIT 1");
$query->execute(); 
$query->store_result();
$bg_color_new_task = fetch_unique($query);
?>          

		<form method="" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" /> 
							
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
							Add new project
						</div>
					</div>
				<?php }
				else { ?>
					<div class="row title">
						<div class="col-md-12">
							<?=@$project->nickname?>
						</div>
					</div>
				<?php }
				
				if($id > 0 ) { ?>
					<div class="row" style="margin-top:20px;font-size:16px;">
						<div class="col-md-12">
							<strong>Project name:</strong>
						</div>
					</div>
				<? } ?>		
				
				<div class="row" style="margin-top:10px;font-size:16px;">
					<div class="col-md-4">		
						<input type="text" class="form-control" style="width:100%;" name="project_name" id="project_name" placeholder="*Project name" value="<?=@$project->name?>" />	
					</div>
				</div>
				
				<?php 
				if($id > 0 ) { ?>
					<div class="row" style="margin-top:20px;font-size:16px;">
						<div class="col-md-12">
							<strong>Project name He:</strong>
						</div>
					</div>
				<? } ?>		
				
				<div class="row" style="margin-top:10px;font-size:16px;">
					<div class="col-md-4">		
						<input type="text" class="form-control" style="width:100%;" name="project_name_he" id="project_name_he" placeholder="*Project name He" value="<?=@$project->name_he?>" />	
					</div>
				</div>
				
				<?php if($id > 0 ) { ?>
					<div class="row" style="margin-top:20px;font-size:16px;">
						<div class="col-md-12">
							<strong>Project nickname:</strong>
						</div>
					</div>
				<? } ?>	
					
				<div class="row" style="margin-top:10px;">
					<div class="col-md-4" style="padding-top:2px;">					
						<input type="text" class="form-control" style="width:100%;" name="project_nickname" id="project_nickname" placeholder="*Project nickname" value="<?=@$project->nickname?>" />	
					</div>
				</div>
				
				<?php if($id > 0 ) { ?>
					<div class="row" style="margin-top:20px;font-size:16px;">
						<div class="col-md-12">
							<strong>Project email client 1:</strong>
						</div>
					</div>
				<? } ?>	
				
				<div class="row" style="margin-top:10px;">
					<div class="col-md-4" style="padding-top:2px;">					
						<input type="text" class="form-control" style="width:100%;" name="project_email_client_1" id="project_email_client_1" placeholder="Project email client 1" value="<?=@$project->email_client_1?>" />	
					</div>
				</div>
				
				<?php if($id > 0 ) { ?>
					<div class="row" style="margin-top:20px;font-size:16px;">
						<div class="col-md-12">
							<strong>Project email client 2:</strong>
						</div>
					</div>
				<? } ?>	
				
				<div class="row" style="margin-top:10px;">
					<div class="col-md-4" style="padding-top:2px;">					
						<input type="text" class="form-control" style="width:100%;" name="project_email_client_2" id="project_email_client_2" placeholder="Project email client 2" value="<?=@$project->email_client_2?>" />	
					</div>
				</div>
				
				<?php if($id > 0 ) { ?>
					<div class="row" style="margin-top:20px;font-size:16px;">
						<div class="col-md-12">
							<strong>Project entrepreneur name:</strong>
						</div>
					</div>
				<? } ?>
				
				<div class="row" style="margin-top:10px;">
					<div class="col-md-4" style="padding-top:2px;">					
						<input type="text" class="form-control" style="width:100%;" name="project_initiator_name" id="project_initiator_name" placeholder="Project entrepreneur name He" value="<?=@$project_supplier->name_he?>" />	
					</div>
				</div>
				
				<?php if($id > 0 ) { ?>
					<div class="row" style="margin-top:20px;font-size:16px;">
						<div class="col-md-12">
							<strong>Project entrepreneur nickname:</strong>
						</div>
					</div>
				<? } ?>	
				
				<div class="row" style="margin-top:10px;">
					<div class="col-md-4" style="padding-top:2px;">					
						<input type="text" class="form-control" style="width:100%;" name="project_initiator_nickname" id="project_initiator_nickname" placeholder="Project entrepreneur nickname he " value="<?=@$project_supplier->nickname_he?>" />	
					</div>
				</div>
				
				<?php if($id > 0 ) { ?>
					<div class="row" style="margin-top:20px;font-size:16px;">
						<div class="col-md-12">
							<strong>Backround color of new Tasks in reports:</strong>
						</div>	
					</div>
					
					<div class="row" style="margin-top:10px;">
						<div class="col-md-4" style="padding-top:2px;">					
							<input type="color" class="form-control" style="width:50%;" name="project_bgcolor_new_task" id="project_bgcolor_new_task" value="<?=@$bg_color_new_task->bgcolor?>" />	
						</div>
					</div>
					
					<div class="row" style="margin-top:20px;font-size:16px;">
						<div class="col-md-12">
							<strong>Current Backround color of new Tasks in reports:</strong>
						</div>	
					</div>
					
					<div class="row" style="margin-top:10px;">
						<div class="col-md-4" style="padding-top:2px;">					
							<input type="color" class="form-control" style="width:50%;" disabled="true" value="<?=@$project->bgcolor_new_task?>" />	
						</div>
					</div>
					
					<div class="row" style="margin-top:10px;">
						<div class="col-md-12">					
							Is project appears?
						</div>
					</div>
					
					<div class="row" style="margin-top:10px;">
						<div class="col-md-12">
						   <input type="radio" id="is_project_appears_1" name="is_project_appears" value="1" <?php if($id == 0 || ($id > 0 && $project->is_project_appears == 1)) echo "checked";?> />&nbsp; Yes
						   <input type="radio" id="is_project_appears_2" name="is_project_appears" value="0" <?php if($id == 0 || ($id > 0 && $project->is_project_appears == 0)) echo "checked";?> />&nbsp; No
						</div>
					</div>
				<?php } ?>
			
				<div class="row marginTop20">
					<div class="col-md-4">
						<select id="project_lang" class="form-control" style="padding-left:5px;width:100px;border-color:initial;">
						   <option value="EN" <?php if(@$project->lang == 'EN') echo 'selected'?>>English</option>
						   <option value="FR" <?php if(@$project->lang == 'FR') echo 'selected'?>>Français</option>
						   <option value="HE" <?php if(@$project->lang == 'HE') echo 'selected'?>>עברית</option>						   
						</select>     
					</div>
				</div>
																								
				<div class="row">
					<div class="col-md-4">
						<div id="div_message_alert_down" style="margin-top:10px;"></div>	
						<input type="button" id="cancel_btn" class="btn marginTop20 colorWhite bgColorBlack marginRight8 mb-2" value="Annuler" />
						<input type="button" id="save_btn" name="save_btn" class="btn marginTop20 colorWhite bgColorBlue mb-2" 
						value="Valider" />						
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
		form_data.append('project_name',$('#project_name').val());
		form_data.append('project_name_he',$('#project_name_he').val());
		form_data.append('project_nickname',$('#project_nickname').val());
		form_data.append('project_email_client_1',$('#project_email_client_1').val());
		form_data.append('project_email_client_2',$('#project_email_client_2').val());
		form_data.append('project_initiator_name',$('#project_initiator_name').val());
		form_data.append('project_initiator_nickname',$('#project_initiator_nickname').val());
		form_data.append('project_bgcolor_new_task',$('#project_bgcolor_new_task').val());
		form_data.append('is_project_appears',$('input[name="is_project_appears"]:checked').val())
		form_data.append('project_lang',$('#project_lang').val());
		$.ajax({
			type: 'POST',
			url: 'project_insert.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){  
                if(data == 'empty')	{
					if($('#project_name').val().length == 0)			
					    $('#project_name').css('border-color','red');
					else if(!($('#project_name').val().length == 0))
						$('#project_name').css('border-color','initial');	
						
				    if($('#project_name_he').val().length == 0)			
					    $('#project_name_he').css('border-color','red');
					else if(!($('#project_name_he').val().length == 0))
						$('#project_name_he').css('border-color','initial');	
					
					if($('#project_nickname').val().length == 0)			
					    $('#project_nickname').css('border-color','red');
					else if(!($('#project_nickname').val().length == 0))
						$('#project_nickname').css('border-color','initial');	
					
					$('#div_message_alert_down').html("<span style=color:red;font-size:13px;>Please fill all the mandatory fields</span>"); 
				}
				else 
				   location.href = 'projects.php';				
			},
		});												       			   
})

$('#cancel_btn').click(function(){
    location.href = "projects.php";	
})
</script>

<style>
.title {
	margin-top: 20px;
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