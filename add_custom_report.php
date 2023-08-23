<?php
session_start();
include 'include/header.php';
include 'functions/functions.php';

$project_id = @$_GET['project_id'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_meetings WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$meetings = fetch($query);

$chapters_array = array();
$areas_array = array();
$tasks_array = array();
$responsibles_array = array();
$pass_ons_array = array();
$progress_status_array = array();

foreach ($meetings as $item) {	
    $query = $mysqli->prepare("SELECT * FROM dne_chapters WHERE id = ? AND id_project = ?");
	$query->bind_param("ii",$item->id_chapter,$project_id);
	$query->execute();
	$query->store_result();
	$chapter = fetch_unique($query);
	$chapters_array[@$chapter->id]= @$chapter->name;
	
	$query = $mysqli->prepare("SELECT * FROM dne_tasks_types WHERE id = ?");
	$query->bind_param("i",$item->id_task_type);
	$query->execute();
	$query->store_result();
	$tasks_type = fetch_unique($query);
	$tasks_types_array[@$tasks_type->id]= @$tasks_type->name;
	
	$query = $mysqli->prepare("SELECT * FROM dne_areas WHERE id = ?");
    $query->bind_param("i",$item->id_area);
    $query->execute();
	$query->store_result();
	$area = fetch_unique($query);
	$areas_array[@$area->id]= @$area->name;
	
	$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id = ?");
    $query->bind_param("i",$item->id_task);
    $query->execute();
	$query->store_result();
	$task = fetch_unique($query);
	$tasks_array[@$task->id]= @$task->name;
	
	$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id = ?");
    $query->bind_param("i",$item->id_responsible);
    $query->execute();
	$query->store_result();
	$responsible = fetch_unique($query);
	$responsibles_array[@$responsible->id]= @$responsible->name;
	
	$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id = ?");
    $query->bind_param("i",$item->id_pass_on);
    $query->execute();
	$query->store_result();
	$pass_on = fetch_unique($query);
	$pass_ons_array[@$pass_on->id]= @$pass_on->name;
	
	$query = $mysqli->prepare("SELECT * FROM dne_progress_status WHERE id = ?");
    $query->bind_param("i",$item->id_progress_status);
    $query->execute();
	$query->store_result();
	$progress_status = fetch_unique($query);
	$progress_status_array[@$progress_status->id]= @$progress_status->name;
}

$query = $mysqli->prepare("SELECT * FROM dne_rdv WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$rdvs = fetch($query);

asort($chapters_array);
asort($tasks_types_array);
asort($areas_array);
asort($tasks_array);
asort($responsibles_array);
asort($pass_ons_array);
asort($progress_status_array);
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
						<span>
							<a style="text-decoration:underline;" href="project_home.php?id=<?=@$project_id?>">
								<?=@$project->name.'<br/>יצירת דו\'\'ח חדש<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4)?>
							</a>
						</span>
				    </div>					
			    </div>
			
				<div class="row" style="margin-top:30px;font-size:16px;text-align:center;direction:rtl;">
					<div class="col-md-12">
						<input type="text" id="request_name" style="width:400px;" placeholder="שם הדו''ח" />
					</div>
				</div>
				
				<div class="row" style="margin-top:30px;font-size:16px;text-align:center;direction:rtl;">
					<div class="col-md-12">
						<input type="text" id="title" style="width:400px;" placeholder="כותרת 1" />
					</div>
				</div>
				
				<div class="row" style="margin-top:30px;font-size:16px;text-align:center;direction:rtl;">
					<div class="col-md-12">
						<input type="text" id="subtitle" style="width:400px;" placeholder="כותרת 2" />
					</div>
				</div>
				
				<div class="row my-3" style="text-align:center;direction:rtl;">
					<div class="col-md-12">
						<strong>סינון:</strong>
					</div>
				</div>
				
				<div class="row" style="margin-top:10px;font-size:16px;text-align:center;direction:rtl;">
					<div class="col-md-12">
						<input type="text" id="subject" style="width:400px;" placeholder="נושא/תחום" />
					</div>
				</div>
				
				<div class="row" style="margin-top:30px;font-size:16px;text-align:center;direction:rtl;">
					<div class="col-md-12">
						<input type="text" id="description" style="width:400px;" placeholder="תיאור" />
					</div>
				</div>
				
				<div class="row" style="margin-top:20px;font-size:12px;text-align:center;direction:rtl;">
					<div align="center" class="col-md-12 mx-2" style="overflow-x:scroll;">
						<table border="1">							
							<tr style="background-color:silver;height:50px;">
								<th width="180px;" style="text-align:center;">פרקים</th>
								<th width="180px;" style="text-align:center;">שותף/ישיבה</th>
								<th width="180px;" style="text-align:center;">איזורים</th>
								<th width="140px;" style="text-align:center;">סוגי משימה</th>
								<th width="180px;" style="text-align:center;">אחראיים</th>
								<th width="180px;" style="text-align:center;font-size:13px;">להעביר ל/ <br/> לאשר מול</th>
								<th width="130px;" style="text-align:center;">סטטוס <br/> התקדמות</th>
							</tr>
							<tr>
								<td style="vertical-align:top;padding:10px;">
									<input type="checkbox" id="all_chapters" checked />&nbsp;הכל<br/><br/>
									<?php foreach ($chapters_array as $key=>$value) {
										 if(!empty($key) && !empty($value)) { ?>
											<input type="checkbox" id="chapters" value="<?=@$key?>" onclick="SetAllChapters();" checked />&nbsp;<?=$value?><br/>
										 <?php }
									}?>
								</td>
								<td style="vertical-align:top;padding:10px;">
									<input type="checkbox" id="all_tasks_types" checked />&nbsp;הכל<br/><br/>
									<?php foreach ($tasks_types_array as $key=>$value) {
										 if(!empty($key) && !empty($value)) { ?>
											<input type="checkbox" id="tasks_types" value="<?=@$key?>" onclick="SetAllTasksTypes(<?=$key?>);" checked />&nbsp;<?=$value?><br/>
										 <?php }
										 
									}?>
									<div id="div_rdvs_list">
										<select id="rdvs_list" class="my-2" style="width:200px;height:26px;">
											<option value="0">--- רשימת ישיבות ---</option>			
											<?php 
											foreach($rdvs as $item) {
											?>
												<option value="<?=@$item->id?>">
													<?=substr($item->rdv_date,8,2).'/'.substr($item->rdv_date,5,2).'/'.substr($item->rdv_date,0,4).'-'.@$item->rdv_name?>
												</option>
												<?php
											}
											?>						
										</select>
									</div>
								</td>
								<td style="vertical-align:top;padding:10px;">
									<input type="checkbox" id="all_areas" checked />&nbsp;הכל<br/><br/>
									<?php foreach ($areas_array as $key=>$value) {
										 if(!empty($key) && !empty($value)) { ?>
											<input type="checkbox" id="areas" value="<?=@$key?>" onclick="SetAllAreas();" checked />&nbsp;<?=$value?><br/>
										 <?php }
									}?>
								</td>
								<td style="vertical-align:top;padding:10px;">
									<input type="checkbox" id="all_tasks" checked />&nbsp;הכל<br/><br/>
									<?php foreach ($tasks_array as $key=>$value) {
										 if(!empty($key) && !empty($value)) { ?>
											<input type="checkbox" id="tasks" value="<?=@$key?>" onclick="SetAllTasks();" checked />&nbsp;<?=$value?><br/>
										 <?php }
									}?>
								</td>
								<td style="vertical-align:top;padding:10px;">
									<input type="checkbox" id="all_responsibles" checked />&nbsp;הכל<br/><br/>
									<?php foreach ($responsibles_array as $key=>$value) {
										 if(!empty($key) && !empty($value)) { ?>
											<input type="checkbox" id="responsibles" value="<?=@$key?>" onclick="SetAllResponsibles();" checked />&nbsp;<?=$value?><br/>
										 <?php }
									}?>
								</td>
								<td style="vertical-align:top;padding:10px;">
									<input type="checkbox" id="all_pass_ons" checked />&nbsp;הכל<br/><br/>
									<?php foreach ($pass_ons_array as $key=>$value) {
										 if(!empty($key) && !empty($value)) { ?>
											<input type="checkbox" id="pass_ons" value="<?=@$key?>" onclick="SetAllPassOns();" checked />&nbsp;<?=$value?><br/>
										 <?php }
									}?>
								</td>
								<td style="vertical-align:top;padding:10px;">
									<input type="checkbox" id="all_progress_status" checked />&nbsp;הכל<br/><br/>
									<?php foreach ($progress_status_array as $key=>$value) {
										 if(!empty($key) && !empty($value)) { ?>
											<input type="checkbox" id="progress_status" value="<?=@$key?>" onclick="SetAllProgressStatus();" <?php if(@$value != 'ארכיון' && @$value != 'בוצע/נמסר') echo 'checked';?> />&nbsp;<?=$value?><br/>
										 <?php }
									}?>
								</td>
							</tr>
						</table>		
					</div>
				</div>
				
				<div class="row" style="margin-top:30px;font-size:16px;text-align:center;direction:rtl;">
					<div class="col-md-12">
						<div class="row">
							<div class="col-md-12">
								<strong>תאריך יצירת משימה:</strong>
							</div>
						</div>
						<div class="row" style="margin-top:10px;">
							<div class="col-md-12">
							   <strong>מתאריך:</strong>&nbsp;
							   <input type="date" id="creation_date_start" style="width:370px;" />
							</div>
						</div>
						<div class="row" class="row" style="margin-top:20px;">
							<div class="col-md-12">
								<strong>עד תאריך:</strong>&nbsp;
						<input type="date" id="creation_date_end" style="width:370px;" />
							</div>
						</div>
					</div>
				</div>
				
				<div class="row" style="margin-top:30px;font-size:16px;text-align:center;direction:rtl;">
					<div class="col-md-12">
						<div class="row">
							<div class="col-md-12">
							   <strong>תאריך יעד:</strong>
							</div>
						</div>
						<div class="row" style="margin-top:10px;">
							<div class="col-md-12">
							   <strong style="margin-top:10px;">מתאריך:</strong>&nbsp;
							   <input type="date" id="destination_date_start" style="width:370px;" />
							</div>
						</div>
						<div class="row" style="margin-top:20px;">
							<div class="col-md-12">
								<strong>עד תאריך:</strong>&nbsp;
								<input type="date" id="destination_date_end" style="width:370px;" />
							</div>
						</div>
					</div>
				</div>
				
				<div class="row my-3" style="text-align:center;direction:rtl;">
					<div class="col-md-12">
						<strong>עיצוב דו''ח:</strong>
					</div>
				</div>
				
				<div class="row" style="margin-top:10px;direction:rtl;">
					<div class="col-md-12">					
						<strong>תמונות ?</strong>
						&nbsp;
						<input type="radio" id="is_images_yes" name="is_images" value="1" />&nbsp; כן
						&nbsp;
						<input type="radio" id="is_images_no" name="is_images" value="0" />&nbsp; לא
					</div>
				</div>
					
					
				<div class="row" style="margin-top:10px;direction:rtl;">
					<div class="col-md-12">					
						<strong>עם צבעים ?</strong>
						&nbsp;
						<input type="radio" id="is_colors_yes" name="is_colors" value="1" />&nbsp; כן
						&nbsp;
						<input type="radio" id="is_colors_no" name="is_colors" value="0" />&nbsp; לא
					</div>
				</div>	

				<div class="row" style="margin-top:10px;direction:rtl;">
					<div class="col-md-12">					
						<strong>שפה ?</strong>
						&nbsp;
						<input type="radio" id="lang_he" name="lang" value="HE" />&nbsp; עברית
						&nbsp;
						<input type="radio" id="lang_en" name="lang" value="EN" />&nbsp; אנגלית
					</div>
				</div>

				<div class="row my-3" style="text-align:center;direction:rtl;">
					<div class="col-md-12">
						<strong>רשימת שדות:</strong>
					</div>
				</div>	

				<div class="row my-3" style="direction:rtl;">
					<div class="col-md-12">
						<input id="columns_list" type="checkbox" value="subject" />&nbsp; נושא/תחום
						<br/>
						<input id="columns_list" type="checkbox" value="area" />&nbsp; איזור/נושא
						<br/>
						<input id="columns_list" type="checkbox" value="description" />&nbsp; תיאור
						<br/>
						<input id="columns_list" type="checkbox" value="task" />&nbsp; סוג משימה
						<br/>
						<input id="columns_list" type="checkbox" value="responsible" />&nbsp; אחראי
						<br/>
						<input id="columns_list" type="checkbox" value="pass on" />&nbsp; להעביר ל/לאשר מול
						<br/>
						<input id="columns_list" type="checkbox" value="task creation" />&nbsp; תאריך יצירת משימה
						<br/>
						<input id="columns_list" type="checkbox" value="destination date" />&nbsp; תאריך יעד
						<br/>
						<input id="columns_list" type="checkbox" value="progress status" />&nbsp; סטטוס התקדמות 
					</div>
				</div>				
				
				<div class="row" style="margin-top:30px;font-size:16px;text-align:center;direction:rtl;">
					<div class="col-md-12">
						<div id="div_message_alert_down" style="margin-top:10px;"></div>
						<button type="button" id="save_btn" class="btn marginTop10 bgColorBlue colorWhite mb-2">שמור</button>
					</div>
				</div> 
			</div>
		</form> 
	</body>
</html>

<script>
$('#all_chapters').click(function() {
    if($(this).is(':checked')) {
       $('input:checkbox[id="chapters"]').each(function () {
          $(this).prop("checked",true);
       });
	}
    else {
        $('input:checkbox[id="chapters"]').each(function () {
          $(this).prop("checked",false);
        });
	}
});

function SetAllChapters() {
	var allChecked = true;
	$('input:checkbox[id="chapters"]').each(function () {
          if(!$(this).is(":checked"))
			allChecked = false;
    });
	
	if(allChecked) 
		$('#all_chapters').prop("checked",true);
	else 
		$('#all_chapters').prop("checked",false);
}

$('#all_tasks_types').click(function() {
	$('#div_rdvs_list').toggle();
	
    if($(this).is(':checked')) {
       $('input:checkbox[id="tasks_types"]').each(function () {
          $(this).prop("checked",true);
       });
	}
    else {
        $('input:checkbox[id="tasks_types"]').each(function () {
          $(this).prop("checked",false);
        });
	}
});

function SetAllTasksTypes(id) {
	if(id == 2)
	  $('#div_rdvs_list').toggle();
  
	var allChecked = true;
	$('input:checkbox[id="tasks_types"]').each(function () {
          if(!$(this).is(":checked"))
			allChecked = false;
    });
	
	if(allChecked) 
		$('#all_tasks_types').prop("checked",true);
	else 
		$('#all_tasks_types').prop("checked",false);
}


$('#all_areas').click(function() {
    if($(this).is(':checked')) {
       $('input:checkbox[id="areas"]').each(function () {
          $(this).prop("checked",true);
       });
	}
    else {
        $('input:checkbox[id="areas"]').each(function () {
          $(this).prop("checked",false);
        });
	}
});

function SetAllAreas() {
	var allChecked = true;
	$('input:checkbox[id="areas"]').each(function () {
          if(!$(this).is(":checked"))
			allChecked = false;
    });
	
	if(allChecked) 
		$('#all_areas').prop("checked",true);
	else 
		$('#all_areas').prop("checked",false);
}

$('#all_tasks').click(function() {
    if($(this).is(':checked')) {
       $('input:checkbox[id="tasks"]').each(function () {
          $(this).prop("checked",true);
       });
	}
    else {
        $('input:checkbox[id="tasks"]').each(function () {
          $(this).prop("checked",false);
        });
	}
});

function SetAllTasks() {
	var allChecked = true;
	$('input:checkbox[id="tasks"]').each(function () {
          if(!$(this).is(":checked"))
			allChecked = false;
    });
	
	if(allChecked) 
		$('#all_tasks').prop("checked",true);
	else 
		$('#all_tasks').prop("checked",false);
}

$('#all_responsibles').click(function() {
    if($(this).is(':checked')) {
       $('input:checkbox[id="responsibles"]').each(function () {
          $(this).prop("checked",true);
       });
	}
    else {
        $('input:checkbox[id="responsibles"]').each(function () {
          $(this).prop("checked",false);
        });
	}
});

function SetAllResponsibles() {
	var allChecked = true;
	$('input:checkbox[id="responsibles"]').each(function () {
          if(!$(this).is(":checked"))
			allChecked = false;
    });
	
	if(allChecked) 
		$('#all_responsibles').prop("checked",true);
	else 
		$('#all_responsibles').prop("checked",false);
}

$('#all_pass_ons').click(function() {
    if($(this).is(':checked')) {
       $('input:checkbox[id="pass_ons"]').each(function () {
          $(this).prop("checked",true);
       });
	}
    else {
        $('input:checkbox[id="pass_ons"]').each(function () {
          $(this).prop("checked",false);
       });
	}
});

function SetAllPassOns() {
	var allChecked = true;
	$('input:checkbox[id="pass_ons"]').each(function () {
          if(!$(this).is(":checked"))
			allChecked = false;
    });
	
	if(allChecked) 
		$('#all_pass_ons').prop("checked",true);
	else 
		$('#all_pass_ons').prop("checked",false);
}

$('#all_progress_status').click(function() {
    if($(this).is(':checked')) {
       $('input:checkbox[id="progress_status"]').each(function () {
          $(this).prop("checked",true);
       });
	}
    else {
        $('input:checkbox[id="progress_status"]').each(function () {
          $(this).prop("checked",false);
       });
	}
});

function SetAllProgressStatus() {
	var allChecked = true;
	$('input:checkbox[id="progress_status"]').each(function () {
          if(!$(this).is(":checked"))
			allChecked = false;
    });
	
	if(allChecked) 
		$('#all_progress_status').prop("checked",true);
	else 
		$('#all_progress_status').prop("checked",false);
}

$('#save_btn').click (function (e){
	var subject = $('#subject').val();
	var description = $('#description').val();
	
    var chapters = '';
	$('#chapters:checked').each(function(i){
	  chapters+= $(this).val()+',';
	});
	chapters = chapters.substring(0,chapters.length - 1);
	
	var tasks_types = '';
	$('#tasks_types:checked').each(function(i){
	  tasks_types+= $(this).val()+',';
	});
	tasks_types = tasks_types.substring(0,tasks_types.length - 1);
	
	var areas = '';
	$('#areas:checked').each(function(i){
	  areas+= $(this).val()+',';
	});
	areas = areas.substring(0,areas.length - 1);
	
	var tasks = '';
	$('#tasks:checked').each(function(i){
	  tasks+= $(this).val()+',';
	});
	tasks = tasks.substring(0,tasks.length - 1);
	
	var responsibles = '';
	$('#responsibles:checked').each(function(i){
	  responsibles+= $(this).val()+',';
	});
	responsibles = responsibles.substring(0,responsibles.length - 1);
	
	var pass_ons = '';
	$('#pass_ons:checked').each(function(i){
	  pass_ons+= $(this).val()+',';
	});
	pass_ons = pass_ons.substring(0,pass_ons.length - 1);
	
	var progress_status = '';
	$('#progress_status:checked').each(function(i){
	  progress_status+= $(this).val()+',';
	});
	progress_status = progress_status.substring(0,progress_status.length - 1);
	
	var creation_date_start = $('#creation_date_start').val();
	var creation_date_end = $('#creation_date_end').val();
	
	var destination_date_start = $('#destination_date_start').val();
	var destination_date_end = $('#destination_date_end').val();
	
	var sql = 'SELECT c.name AS name,m.id AS id,m.id_task_type AS id_task_type,m.id_chapter AS id_chapter,m.is_pdf_appears AS is_pdf_appears,m.subject AS subject,m.id_rdv AS id_rdv,m.id_area,m.description,m.id_task,m.id_responsible,m.id_pass_on,m.task_creation_date,m.destination_date,m.id_progress_status,m.updated_date AS updated_date,m.image1 AS image1,m.is_appears_img1 AS is_appears_img1,m.image2 AS image2,m.is_appears_img2 AS is_appears_img2,m.is_change_row_style AS is_change_row_style FROM dne_meetings m LEFT JOIN dne_chapters c ON m.id_chapter = c.id LEFT JOIN dne_tasks t ON m.id_task = t.id WHERE m.id_project ='+$('#project_id').val()+ ' AND m.is_appears = 1 ';
	
	var params = [];
	
	if(chapters != '')
        params.push('m.id_chapter IN('+chapters+')');
	
	if(tasks_types != '')
        params.push('m.id_task_type IN('+tasks_types+')');
	
	if($('#rdvs_list').val() != 0)
		params.push('m.id_rdv = ' + $('#rdvs_list').val());
	
	if(subject != '')
	   params.push("m.subject LIKE '%"+subject+"%'");
	
	if(description != '')
		params.push("m.description LIKE '%"+description+"%'");
	
	if(areas != '')
        params.push('m.id_area IN('+areas+')');
	
	if(tasks != '')
        params.push('m.id_task IN('+tasks+')');
	
	if(responsibles != '')
        params.push('(m.id_responsible IN('+responsibles+') OR m.id_pass_on IN('+responsibles+'))');
	
	if(pass_ons != '')
        params.push('m.id_pass_on IN('+pass_ons+')');
	
	if(progress_status != '')
        params.push('m.id_progress_status IN('+progress_status+')');
	
	var creation_date = '';
	if(creation_date_start != '' && creation_date_end != '')
		creation_date = "m.task_creation_date BETWEEN '"+creation_date_start+"' AND '"+creation_date_end+"'";
	
	else if(creation_date_start != '' && creation_date_end == '')
		creation_date = "m.task_creation_date >= '"+creation_date_start+"'";
	
	else if(creation_date_start == '' && creation_date_end != '')
		creation_date = "m.task_creation_date <= '"+creation_date_end+"'";
	
	if(creation_date != '')
		params.push(creation_date);
	
	var destination_date = '';
	if(destination_date_start != '' && destination_date_end != '')
		destination_date = "m.destination_date BETWEEN '"+destination_date_start+"' AND '"+destination_date_end+"'";
	
	else if(destination_date_start != '' && destination_date_end == '')
		destination_date = "m.destination_date >= '"+destination_date_start+"'";
	
	else if(destination_date_start == '' && destination_date_end != '')
		destination_date = "m.destination_date <= '"+destination_date_end+"'";
	
	if(destination_date != '')
		params.push(destination_date);
	
	var params_str = '';
	if(params.length>0)
		params_str = params.join(' AND ');
	
	if(params_str != '')
	   sql+= ' AND '+params_str;
   
    var is_images = 0;
	if($('input[name="is_images"]:checked').length > 0)
	  is_images = $('input[name="is_images"]:checked').val();
  
    var is_colors = 0;
	if($('input[name="is_colors"]:checked').length > 0)
	  is_colors = $('input[name="is_colors"]:checked').val();
  
    var lang = 'EN';
	if($('input[name="lang"]:checked').length > 0)
	  lang = $('input[name="lang"]:checked').val();
    
    var columns_list = '';
	$('#columns_list:checked').each(function(i){
	  columns_list+= $(this).val()+',';
	});
	columns_list = columns_list.substring(0,columns_list.length - 1);
   
    var form_data = new FormData();	
	form_data.append('id_project',$('#project_id').val());
	form_data.append('request_name',$('#request_name').val());
	form_data.append('title',$('#title').val());
	form_data.append('subtitle',$('#subtitle').val());
	form_data.append('sql_str',sql);
	form_data.append('is_images',is_images);
	form_data.append('is_colors',is_colors);
	form_data.append('lang',lang);
	form_data.append('columns_list',columns_list);
		
	$.ajax({
		type: 'POST',
		url: 'custom_report_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){
			if(data == 'empty')	{		
				if($('#request_name').val().length == 0)			
					$('#request_name').css('border-color','red');
				else if(!($('#request_name').val().length == 0))
					$('#request_name').css('border-color','initial');	
				
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;>נא למלה את כל השדות החובות</span>");
			}
			else
			    location.href = 'custom_reports.php?project_id='+$('#project_id').val();		
		},
	});	
})
</script>

<style>
table,th,td {
   border:1px solid black;
}

.title {
	font-size: 22px;
	text-align: center;
	margin-top: 20px;
	color: #349feb;
}

a {
	color: inherit;
}

.bgColorBlue {
	background-color:#218FD6;
}

.marginTop10 {
  margin-top: 10px;
}

.colorWhite {
	color: white;
}

.btn:hover {
   color: white;
}

.bgColorBlue:hover {
	background-color:#3370d6;
}
</style>