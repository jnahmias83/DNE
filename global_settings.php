<?php
session_start();
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$query = $mysqli->prepare("SELECT * FROM dne_global_tasks ORDER BY name");
$query->execute(); 
$query->store_result();
$tasks_num_rows = $query->num_rows;
$tasks = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_global_progress_status");
$query->execute(); 
$query->store_result();
$progress_status_num_rows = $query->num_rows;
$progress_status = fetch($query);

$sup_type = 'E';
$query = $mysqli->prepare("SELECT color,bgcolor FROM dne_sup_field_of_work WHERE sup_type = ?");
$query->bind_param("s",$sup_type);
$query->execute(); 
$sfow = fetch_unique($query);

$query = $mysqli->prepare("SELECT bgcolor FROM dne_global_bgcolor_new_task LIMIT 1");
$query->execute(); 
$query->store_result();
$bg_color_new_task = fetch_unique($query);
?>

        <form method="post" action="" class="form-inline">	
			<div class="container">
			    <div class="row" style="margin-top:25px;text-align:center;">
					<div class="col-md-12">
						<img src="images/davidnahmias_logo.png" width="170px" height="170px" />
					</div>
				</div>

				<div class="row title">
				   <div class="col-md-12">
					   הגדרות כלליות
					   <br/>
					   <?=substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4)?>
				   </div>
				</div>
				
				<div class="row">
					<div class="col-md-4">
						<div class="row title">	
							<div class="col-md-12">
							   רשימת סטסטוסי התקדמות			
							</div>					
						</div>
						<div class="row" style="text-align:center;">
							<div class="col-md-12">
								<input type="button" value="הוסף סטטוס התקדמות חדש" class="btn marginTop20 mb-2" onclick="location.href='add_global_progress_status.php?id=0';" />
							</div>
						</div>
						
						<br/>

						<?php if($progress_status_num_rows > 0) { ?>		
							<div class="row" style="font-size:14px;text-align:center;">
								<div align="center" class="col-md-12 mx-2">
									<table id="progress_status_list" border="1" dir="rtl">						
										<tr style="background-color:silver;height:50px;">
											<th width="120px;" style="text-align:center;">שם</th>
											<th width="90px;" style="text-align:center;">צבע גופן</th>
											<th width="90px;" style="text-align:center;">צבע רקע</th>
											<th width="40px;" style="text-align:center;">&nbsp;</th>
											<?php if($progress_status_num_rows > 7) { ?>
											   <th width="40px;" style="text-align:center;">&nbsp;</th>
											<?php } ?>
										</tr>
					
										<?php
										$count = 0;
										foreach($progress_status as $item) {
											?>
											<tr style="height:35px;">
												<td style="text-align:right;padding-right:5px;"><?=@$item->name?></td>
												<td style="text-align:right;padding-right:5px;"><input type="color" style="width:90px;" disabled="true" value="<?=@$item->color?>" /></td>
												<td style="text-align:right;padding-right:5px;"><input type="color" style="width:90px;" disabled="true" value="<?=@$item->bgcolor?>" /></td>
												<td style="text-align:center;"><img src="images/edit-button.svg" style="cursor:pointer;" title="עדכן" onclick="location.href='add_global_progress_status.php?id=<?=@$item->id?>'" /></td>									
												<?php if(!(@$item->name == ' ' || @$item->name == 'בביצוע' || @$item->name == 'איחור' || @$item->name == 'בוצע/נמסר' || @$item->name == 'Hold' || @$item->name == 'ארכיון' || @$item->name == 'הנחיה/החלטה')) { ?>
													<td style="text-align:center;">
													   <img src="images/delete.svg" style="cursor:pointer;" title="מחק" onclick="return removeProgressStatus(<?=@$item->id?>);" />	
													</td>
												<?php } ?>
											</tr>
											<?php
										}
										?>
									</table>		
								</div>
							</div>
						<?php } ?>    
					</div>
					<div class="col-md-4">
						<div class="row title">	
							<div class="col-md-12">					
							   רשימת סוגי משימות
							</div>					
						</div>
						<div class="row" style="text-align:center;">
							<div class="col-md-12">
								<input type="button" value="הוסף סוג משימה חדש" class="btn marginTop20 mb-2" onclick="location.href='add_global_task.php?id=0';" />
							</div>
						</div>
						
						<br/>

						<?php if($tasks_num_rows > 0) { ?>		
							<div class="row" style="font-size:14px;text-align:center;">
								<div align="center" class="col-md-12 mx-2">
									<table id="tasks_list" border="1" dir="rtl">						
										<tr style="background-color:silver;height:50px;">
											<th width="110px;" style="text-align:center;">שם</th>
											<th width="90px;" style="text-align:center;">צבע גופן</th>
											<th width="90px;" style="text-align:center;">צבע רקע</th>
											<th width="40px;" style="text-align:center;">&nbsp;</th>
											<?php if($tasks_num_rows > 7) { ?>
											   <th width="40px;" style="text-align:center;">&nbsp;</th>
											<?php } ?>
										</tr>
					
										<?php
										$count = 0;
										foreach($tasks as $item) {
											?>
											<tr style="height:35px;">
												<td style="text-align:right;padding-right:5px;"><?=@$item->name?></td>
												<td style="text-align:right;padding-right:5px;"><input type="color" style="width:90px;" disabled="true" value="<?=@$item->color?>" /></td>
												<td style="text-align:right;padding-right:5px;"><input type="color" style="width:90px;" disabled="true" value="<?=@$item->bgcolor?>" /></td>								
												<td style="text-align:center;"><img src="images/edit-button.svg" style="cursor:pointer;" title="עדכן" onclick="location.href='add_global_task.php?id=<?=@$item->id?>'" /></td>									
													<?php if(!(@$item->name == 'תכנון' || @$item->name == 'ביצוע' || @$item->name == 'ניהול' || @$item->name == 'בקרת איכות' || @$item->name == 'הנחיית ביצוע' || @$item->name == 'סטטוס ביצוע' || @$item->name == 'בקשה/שאילתה')) { ?>
													<td style="text-align:center;">
													   <img src="images/delete.svg" style="cursor:pointer;" title="מחק" onclick="return removeTask(<?=@$item->id?>);" />	
													</td>
													<?php } ?>
											</tr>
											<?php
										}
										?>
									</table>		
								</div>
							</div>
						<?php } ?>
					</div>
					
					<div class="col-md-4" style="text-align:center;direction:rtl;">
						<div>
						    <h3>יזם:</h3>
						
							<div class="row">
							   <div class="col-md-12">
								  <strong>צבע גופן:</strong>
								  &nbsp:
								  <input type="color" id="etrp_color" name="etrp_color" style="width:200px;" onchange="setEtrpData(this.value,'');" value="<?=@$sfow->color?>" />
							   </div> 
							</div>
							
							<br/>
							
							<div class="row">
							   <div class="col-md-12">
								  <strong>צבע רקע:</strong>
								  &nbsp:
								  <input type="color" id="etrp_bgcolor" name="etrp_bgcolor" style="width:200px;" onchange="setEtrpData('',this.value);" value="<?=@$sfow->bgcolor?>" />
							   </div> 
							</div>
						</div>
						
						<div class="my-4">
						   <h3>רקע למשימה חדשה:</h3>
							
							<div class="row">
							   <div class="col-md-12">
								  <strong>צבע רקע:</strong>
								  &nbsp:
								  <input type="color" id="new_task_bgcolor" name="new_task_bgcolor" style="width:200px;" onchange="setNewTaskData(this.value);" value="<?=@$bg_color_new_task->bgcolor?>" />
							   </div> 
							</div>
						</div>
					</div>
				</div>
			</div>
		</form> 
	</body>
</html>

<script>
function setEtrpData(color,bgcolor) {
	var form_data = new FormData();	
	form_data.append('color',color);
	form_data.append('bgcolor',bgcolor);
	
	$.ajax({
		type: 'POST',
		url: 'setEtrpData.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){
			location.reload(true);			
		},
	});		
}

function setNewTaskData(bgcolor) {
	var form_data = new FormData();	
	form_data.append('bgcolor',bgcolor);
	
	$.ajax({
		type: 'POST',
		url: 'setNewTaskData.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){
			location.reload(true);			
		},
	});		
}

function removeTask(id) {
	if(confirm("האם אתה בטוח למחוק את סוג המשימה זו ?")) {
        var form_data = new FormData();	
		form_data.append('global',1);
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'task_delete.php',
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

function removeProgressStatus(id) {
	if(confirm("האם אתה בטוח למחוק את הסטטוס ההתקדמות הזה ?")) {
        var form_data = new FormData();	
		form_data.append('global',1);
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'progress_status_delete.php',
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
.title {
	margin-top: 25px;
	text-align: center;
	font-size: 20px;
	color: #349feb;
}

h3 {
	font-size: 22px;
	color: #5bbd8d;
}

.marginTop20 {
	margin-top: 20px;
}

.btn {
	background-color: #218FD6;
	color: white;
	margin-top: 10px;
}

.btn:hover {
   background-color: #3370d6;
   color: white;
}
</style>