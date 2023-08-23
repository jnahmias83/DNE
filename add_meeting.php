<?php
include 'include/header.php';
include 'functions/functions.php';

$id = @$_GET['id'];
$chapter_id = @$_GET['chapter_id'];
$project_id = @$_GET['project_id'];
$fromResumeRdvs = @$_GET['fromResumeRdvs'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$project = fetch_unique($query); 

$query = $mysqli->prepare("SELECT * FROM dne_meetings WHERE id = ?");
$query->bind_param("i",$id);
$query->execute();
$query->store_result();
$meeting = fetch_unique($query);
	
$task_creation_date = @$meeting->task_creation_date;
if($id == 0 ||($id > 0 && @$meeting->task_creation_date == '0000-00-00'))
  $task_creation_date = @$_SESSION['report_date'];

if($fromResumeRdvs == 1 && $id == 0) {
	if(isset($_SESSION['id_rdv'])) {
		$query = $mysqli->prepare("SELECT * FROM dne_rdv WHERE id = ?");
        $query->bind_param("i",$_SESSION['id_rdv']);
		$query->execute();
		$query->store_result();
		$rdv = fetch_unique($query);
		$task_creation_date = @$rdv->rdv_date;
	}
}

$destination_date = @$meeting->destination_date;
if($id == 0 ||($id > 0 && @$meeting->destination_date == '0000-00-00')) {
	$date = new DateTime(date("Y-m-d"));
    $date->modify('+7 day');
    $destination_date = $date->format('Y-m-d');
}

$query = $mysqli->prepare("SELECT * FROM dne_chapters WHERE id_project = ? ORDER BY name ASC");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$chapters = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_rdv WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$rdvs = fetch($query);
	
$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id_project = ? ORDER BY name ASC");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$responsibles = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id_project = ? ORDER BY name ASC");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$tasks = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_areas WHERE id_project = ? ORDER BY name ASC");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$areas = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_progress_status WHERE id_project = ?");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$progress_status = fetch($query);
?>

		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" />
			<input type="hidden" id="project_id" value="<?=@$project_id?>" />
			<input type="hidden" id="fromResumeRdvs" value="<?=@$fromResumeRdvs?>" />
            <input type="hidden" id="id_rdv" value="<?=@$_SESSION['id_rdv']?>" />			
            <input type="hidden" id="is_appears_img1_hidden" value="<?=@$meeting->is_appears_img1?>" />		
            <input type="hidden" id="is_appears_img2_hidden" value="<?=@$meeting->is_appears_img2?>" />			
			
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
						<div class="col-md-12" style="font-size:20px;">
							<span>הוספת משימה לפרוייקט </span> <span style="color:#5bbd8d;"><?=@$project->name?></span>
						</div>
					</div>
				<?php } ?>
				
				<div class="row" style="margin-top:20px;font-size:14px;direction:rtl;">
					<div class="col-md-12">
						<strong>פרק:</strong>
						&nbsp;
						<select id="chapter" class="form-control my-2" style="width:200px;">	
							<option value="0">--- רשימת פרקים ----</option>
							<?php 
							foreach($chapters as $item) {
							?>
								<option value="<?=@$item->id?>" <?php if(($item->id === @$meeting->id_chapter) || ($item->id == $chapter_id)) echo "selected";?>>
									<?=@$item->name?>
								</option>
								<?php
							}
							?>						
						</select>
					</div>
				</div>	
				
				<div class="row mx-3 py-2" style="margin-top:20px;direction:rtl;border:2px solid blue;">
					<div class="col-md-12">
						<input type="radio" id="task_type_1" name="task_type" value="1" <?php if($id == 0 || ($id > 0 && $meeting->id_task_type == 1)) echo "checked";?> /> 
						&nbsp;
						ניהול ופיקוח שותף
						
						<br/>
						
						<input type="radio" class="my-3" id="task_type_2" name="task_type" value="2" <?php if($id > 0 && $meeting->id_task_type == 2) echo "checked";?> />
						&nbsp;
						סיכום ישיבה
						
						<span class="m-2" id="span_rdvs_list">
							 <select id="rdv" style="width:200px;height:35px;">
								<option value="0">------ בחר ישיבה ------</option>
								 <?php 
								foreach($rdvs as $item) {
								?>
									<option value="<?=@$item->id?>" <?php if($item->id == @$_SESSION['id_rdv']) echo "selected";?>>
										<?=substr($item->rdv_date,8,2).'/'.substr($item->rdv_date,5,2).'/'.substr($item->rdv_date,0,4).'-'.@$item->rdv_name?>
									</option>
									<?php
								}
								?>			
							 </select>
						</span>
					</div>
				</div>
				
				<div class="row" style="margin-top:10px;font-size:14px;direction:rtl;">
					<div class="col-md-12" style="padding-top:2px;">
						<strong>נושא/תחום:</strong>				
						<input type="text" class="form-control" style="margin-top:5px;width:300px;" name="subject" id="subject" placeholder="*נושא/תחום" value="<?=@$meeting->subject?>" />
					</div>
				</div>	
				
				<div class="row" style="margin-top:20px;font-size:14px;direction:rtl;">
					<div class="col-md-12">
						<strong>אזור/נושא:</strong>
					</div>
				</div>		

				<div class="row" style="margin-top:2px;font-size:14px;direction:rtl;">
					<div class="col-md-12">
						<span id="areas_list_span">
							<select id="area" style="width:200px;height:35px;">		
								<option value="0">--- רשימת איזורים ----</option>								
								<?php 
								foreach($areas as $item) {
								?>
									<option value="<?=@$item->id?>" <?php if($item->id == @$meeting->id_area) echo "selected";?>>
										<?=@$item->name?>
									</option>
									<?php
								}
								?>						
							</select>	
						</span>
						&nbsp;			
						<input type="text" style="margin-top:10px;width:200px;" name="area_name" id="area_name" placeholder="אזור/נושא" />		
					</div>
				</div>
				
				<div class="row" style="margin-top:10px;font-size:14px;direction:rtl;">
					<div class="col-md-12" style="padding-top:2px;">
							<strong>תיאור:</strong>
							<br/>	
						<textarea class="form-control" name="description" id="description" rows="5" cols="30" placeholder="תיאור ..."><?=@$meeting->description?></textarea>
					</div>
				</div>
				
				<div style="display:flex;justify-content:space-around;flex-wrap:wrap;margin-top:20px;direction:rtl;">
					<div style="text-align:center;width:30%;">   
						<strong>אחראי:</strong>
						<div class="row" style="margin-top:10px;">
							<div class="col-md-12">
								<select id="responsible" style="height:35px;" onchange="setSelectedKindOfTask(this.value);">
									<option value="0">--- רשימת אחראיים ---</option>			
									<?php 
									foreach($responsibles as $item) {
									?>
										<option value="<?=@$item->id?>" <?php if($item->id == @$meeting->id_responsible) echo "selected";?>>
											<?=@$item->name?>
										</option>
										<?php
									}
									?>						
								</select>
							</div>
						</div>
					</div>
					
					<div style="text-align:center;width:30%;">   
						<strong>להעביר ל/לאשר מול:</strong>      
						<div class="row" style="margin-top:10px;">
							<div class="col-md-12">
								<select id="pass_on" style="height:35px;">
									<option value="0">--- רשימת אחראיים ---</option>			
									<?php 
									foreach($responsibles as $item) {
									?>
										<option value="<?=@$item->id?>" <?php if($item->id == @$meeting->id_pass_on) echo "selected";?>>
											<?=@$item->name?>
										</option>
										<?php
									}
									?>						
								</select>
							</div>
						</div>
					</div>
					
					<div style="text-align:center;width:30%;">
						<strong>סוגי משימות:</strong>
						<div class="row" style="margin-top:10px;">
							<div class="col-md-12">
								<span id="tasks_list_span">
									<select id="task" style="height:35px;">		
										<option value="0">--- רשימת סוגי משימות ----</option>								
										<?php 
										foreach($tasks as $item) {
										?>
											<option value="<?=@$item->id?>" <?php if($item->id == @$meeting->id_task) echo "selected";?>>
												<?=@$item->name?>
											</option>
											<?php
										}
										?>						
									</select>	
								</span>
							</div>
						</div>
					</div>
				</div>
				
				<div class="row" style="margin-top:10px;direction:rtl;">
					<div class="col-md-5 p-2 m-2" style="border:1px solid black;">
						<strong>תאריך יצירת משימה:</strong>
						<br/>			
						<input type="date" class="form-control" style="margin-top:10px;" name="task_creation_date" id="task_creation_date" value="<?=@$task_creation_date?>" />
					</div>
					<div class="col-md-5 p-2 m-2" style="border:1px solid black;">
						<strong>תאריך יעד:</strong>
						<br/>			
						<input type="date" class="form-control" style="margin-top:10px;" name="destination_date" id="destination_date" value="<?=@$destination_date?>" />
					</div>
				</div>	
				
				<div class="row" style="margin-top:20px;font-size:14px;direction:rtl;">
					<div class="col-md-12">
						<strong>סטטוס התקדמות משימה:</strong>
					</div>
				</div>		
				
				<div class="row" style="margin-top:5px;font-size:14px;direction:rtl;">
					<div class="col-md-12">
						<span id="progress_status_list_span">
							<select id="progress_status" style="width:220px;height:26px;" placeholder="סטטוס התקדמות">
								<option value="0">--- רשימת סטסטוסי התקדמות ----</option>
								<?php 
								foreach($progress_status as $item) {
								?>
									<option value="<?=@$item->id?>" <?php if($item->id == @$meeting->id_progress_status) echo "selected";?>>
										<?=@$item->name?>
									</option>
									<?php
								}
								?>						
							</select>	
						</span>
						&nbsp;			
						<input type="text" style="margin-top:10px;width:200px;" name="progress_status_name" id="progress_status_name" placeholder="סטטוס התקדמות" />					
					</div>
				</div>
				
				<?php if($id > 0 && @$meeting->status_updated_date != '0000-00-00') { ?>
					<div class="row" style="margin-top:20px;font-size:14px;direction:rtl;">
						<div class="col-md-12">
						<strong>תאריך עדכון סטטוס: <?=substr(@$meeting->status_updated_date,8,2).'/'.substr(@$meeting->status_updated_date,5,2).'/'.substr(@$meeting->status_updated_date,0,4)?></strong>
						</div>
					</div>		
				<?php } ?>
				
				<div class="row" style="margin-top:20px;font-size:14px;direction:rtl;">
					<div class="col-md-11" style="padding-top:2px;">
						<input type="checkbox" id="is_appears_img1" <?php if(@$meeting->is_appears_img1 == 1) echo 'checked';?> />
						&nbsp;
						<span style="font-size:14px;font-weight:bold;">תמונה 1:</span>
						<br/>					
						<input type="file" class="form-control" name="image1" id="image1" style="margin-top:10px;" />
						<?php if($id > 0) { ?>&nbsp;<a href="uploads/<?=@$meeting->image1?>" target="_blank"><?=@$meeting->image1?></a><?php } ?>			
					</div>
				</div>
				
				<div class="row" style="margin-top:20px;font-size:14px;direction:rtl;">
					<div class="col-md-11" style="padding-top:2px;">
						<input type="checkbox" id="is_appears_img2" <?php if(@$meeting->is_appears_img2 == 1) echo 'checked';?> />
						&nbsp;
						<span style="font-size:14px;font-weight:bold;">תמונה 2:</span>
						<br/>					
						<input type="file" class="form-control" name="image2" id="image2" style="margin-top:10px;" />
						<?php if($id > 0) { ?>&nbsp;<a href="uploads/<?=@$meeting->image2?>" target="_blank"><?=@$meeting->image2?></a><?php } ?>			
					</div>
				</div>
				
				<?php if($id > 0 && @$meeting->last_pdf_created != '') { ?>
					<div class="row" style="margin-top:20px;font-size:14px;direction:rtl;">
						<div class="col-md-12">
						<strong>Last PDF created: <?=@$meeting->last_pdf_created?></strong>
						</div>
					</div>		
				<?php } 
				
				if($id > 0 && @$meeting->last_pdf_created_date != '0000-00-00') { ?>
					<div class="row" style="margin-top:20px;font-size:14px;direction:rtl;">
						<div class="col-md-12">
						<strong>Last PDF created date: <?=substr(@$meeting->last_pdf_created_date,8,2).'/'.substr(@$meeting->last_pdf_created_date,5,2).'/'.substr(@$meeting->last_pdf_created_date,0,4)?></strong>
						</div>
					</div>		
				<?php } ?>
				
				<div class="row" style="margin-top:10px;margin-bottom:20px;direction:rtl;">
					<div class="col-md-12">
						<div id="div_message_alert_down" style="margin-top:10px;"></div>
						<input type="button" id="save_btn" name="save_btn" class="btn marginTop5 bgColorBlue colorWhite mb-2" value="שמור" />					
						<input type="button" id="cancel_btn" class="btn marginTop5 bgColorBlack colorWhite marginRight8 mb-2" value="ביטול" />						
					</div>
				</div>			   
			</div>					
		</form>       
    </body>
</html>

<script>
var image1;
var image2;

$(document).ready(function() {
	if($('#id_rdv').val() > 0) {
	  $("#task_type_2").attr('checked',true);
	  $('#span_rdvs_list').show();
	}
	else if($('input[name="task_type"]:checked').val() == 1)
	   $('#span_rdvs_list').hide();
	else if($('input[name="task_type"]:checked').val() == 2)
	   $('#span_rdvs_list').show();
});

$(document).on('change','#image1',function() {
    image1 = $('#image1')[0].files[0];
});

$(document).on('change','#image2',function() {
    image2 = $('#image2')[0].files[0];
});

var is_appears_img1 = 0;
if($('#id').val() > 0)
   is_appears_img1 = $('#is_appears_img1_hidden').val();
$("#is_appears_img1").change(function() {
    if(this.checked) {
       is_appears_img1 = 1;
    }
	else 
	   is_appears_img1 = 0;
});
	
var is_appears_img2 = 0;
if($('#id').val() > 0)
   is_appears_img2 = $('#is_appears_img2_hidden').val();
$("#is_appears_img2").change(function() {
    if(this.checked) {
       is_appears_img2 = 1;
    }
	else 
	   is_appears_img2 = 0;
});

function setSelectedKindOfTask(id_responsible) {
	if($('#id').val() == 0) {
		var form_data = new FormData();	
	    form_data.append('id_project',$('#project_id').val());
	    form_data.append('id_responsible',id_responsible)
	
		$.ajax({
			type: 'POST',
			url: 'set_kind_of_task.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){  
			   $('#task').val(data);		   
			},
		});				
	}
}

$("input:radio[name='task_type']").on('change', function () {
    if($(this).val() == 1) {
		$('#span_rdvs_list').hide();
	}
	else if($(this).val() == 2) {
		$('#span_rdvs_list').show();
	}
});

$('#area_name').on('change', function (){
	$('#areas_list_span').hide();
});

$('#progress_status_name').on('change', function (){
	$('#progress_status_list_div').hide();
});

$('#save_btn').click (function (e){ 
    var form_data = new FormData();	
	form_data.append('id',$('#id').val());
	form_data.append('id_project',$('#project_id').val());
	form_data.append('id_chapter',$('#chapter').val());
	form_data.append('id_task_type',$('input[name="task_type"]:checked').val());
	form_data.append('id_rdv',$('#rdv').val());
	form_data.append('subject',$('#subject').val());
	form_data.append('area_name',$('#area_name').val());
	form_data.append('id_area',$('#area').val());
	form_data.append('description',$('#description').val());
	form_data.append('id_task',$('#task').val());
	form_data.append('id_responsible',$('#responsible').val());
	form_data.append('id_pass_on',$('#pass_on').val());
	form_data.append('task_creation_date',$('#task_creation_date').val());
	form_data.append('destination_date',$('#destination_date').val());
	form_data.append('progress_status_name',$('#progress_status_name').val());
	form_data.append('id_progress_status',$('#progress_status').val());
	form_data.append('image1',image1);
	form_data.append('is_appears_img1',is_appears_img1);
	form_data.append('image2',image2);
	form_data.append('is_appears_img2',is_appears_img2);
	
	$.ajax({
		type: 'POST',
		url: 'meeting_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){  
			if(data == 'empty')	{
				if($('#chapter').val() == 0)			
					$('#chapter').css('border-color','red');
				else if(!($('#chapter').val() == 0))
					$('#chapter').css('border-color','initial');
				
                if($('input[name="task_type"]:checked').val() == 2 && $('#rdv').val() == 0)
					$('#rdv').css('border-color','red');
				else if($('input[name="task_type"]:checked').val() == 2 && $('#rdv').val() != 0)
					$('#rdv').css('border-color','initial');
				
				if($('#subject').val().length == 0)			
					$('#subject').css('border-color','red');
				else if(!($('#subject').val().length == 0))
					$('#subject').css('border-color','initial');
						
				if($('#area').val() == 0 && $('#area_name').val().length == 0)			
					$('#area').css('border-color','red');
				else if(!($('#area').val() == 0 && $('#area_name').val().length == 0))
					$('#area').css('border-color','initial');
				
				if($('#task').val() == 0)			
					$('#task').css('border-color','red');
				else if(!($('#task').val() == 0))
					$('#task').css('border-color','initial');
				
				if($('#responsible').val() == 0)			
					$('#responsible').css('border-color','red');
				else if(!($('#responsible').val() == 0))
					$('#responsible').css('border-color','initial');
				
				if($('#pass_on').val() == 0)			
					$('#pass_on').css('border-color','red');
				else if(!($('#pass_on').val() == 0))
					$('#pass_on').css('border-color','initial');
				
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;>Please fill all the mandatory fields</span>"); 
			}
			else {
				let url = $('#fromResumeRdvs').val()? 'resume_rdv.php?&project_id='+$('#project_id').val():'meetings.php?project_id='+$('#project_id').val();
				location.href = url;
			}					   	
		},
	});									       			   
})

$('#cancel_btn').click(function(){
    let url = $('#fromResumeRdvs').val()? 'resume_rdv.php?&project_id='+$('#project_id').val():'meetings.php?project_id='+$('#project_id').val();
	location.href = url;
})
</script>

<style>
.title {
	font-size: 22px;
	color: #349feb;
	margin-top: 20px;
	direction: rtl;
	text-align: center;
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
