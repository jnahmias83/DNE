<?php
session_start();
include 'include/header.php';
include 'functions/functions.php';
include "functions/PHPMailer/PHPMailer.php";
include 'functions/PHPMailer/SMTP.php';
include 'functions/PHPMailer/Exception.php';
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

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
$all_meetings_num_rows = $query->num_rows;
	
$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id_project = ? ORDER BY name ASC");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$responsibles = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id_project = ? ORDER BY name ASC");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$tasks = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_areas WHERE id_project = ? ORDER BY name ASC");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$areas = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_progress_status WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$progress_status_s = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_log_create_pdf WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$last_log_pdf_cd_num_rows = $query->num_rows;
$last_log_pdf_cd_num_rows;

if ($last_log_pdf_cd_num_rows === 1) {
	$last_log_pdf_cd = fetch_unique($query);
	$last_pdf_date = $last_log_pdf_cd->last_pdf_created_date;
}
else {
	$query = $mysqli->prepare("SELECT * FROM dne_log_create_pdf WHERE id_project = ? ORDER BY id DESC LIMIT 1,1");
    $query->bind_param("i",$project_id);
    $query->execute();
    $query->store_result();
	$last_log_pdf_cd = fetch_unique($query);
	$last_pdf_date = $last_log_pdf_cd->last_pdf_created_date;
}

if(isset($_POST['send_email_btn'])) {
	php_mailer('jnahmias83@gmail.com','DNE Projects','jnahmias83@gmail.com','test','message','uploads/pdf_approval_P1_doc02403820220131153151.pdf');
	php_mailer('jnahmias83@gmail.com','DNE Projects','david@nahmias-eng.com','test','message','uploads/pdf_approval_P1_doc02403820220131153151.pdf');
}
?>

        <form method="post" action="" class="form-inline">
		    <input type="hidden" id="project_id" name="project_id" value="<?=@$project->id?>" />
			
			<div class="container">
			   <div class="row" style="margin-top:25px;text-align:center;">
			    <div class="col-md-12">
				    <img src="images/davidnahmias_logo.png" width="170px" height="170px" />
				</div>
			</div>
			
	        <div class="container">
			    <div class="row title">	
					<div class="col-md-12">
						<a style="text-decoration:underline;" href="project_home.php?id=<?=@$project_id?>">
							<?=@$project->name.'<br/> דו\'ח סטטוס פרוייקט<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4)?>
						</a>
					</div>					
			    </div>
			
				<div class="row" style="margin-top:25px;text-align:center;direction:rtl;">
					<div class="col-md-12">
					   <strong style="font-size:16px;">תאריך דיווח:&nbsp;</strong>
					   <input type="date" class="form-inline" style="padding-left:8px;" id="report_date" name="report_date" value="<?=date('Y-m-d')?>" />
					</div>
				</div>
				
				<div class="row" style="text-align:center;direction:rtl;">
					<div class="col-md-12">
					   <input type="button" id="to_add_meeting_btn" value="הוסך משימה" class="btn btn-primary mb-2" style="margin-top:20px;" />
					</div>
				</div>
				
				<div class="row" style="text-align:center;direction:rtl;">
					<div class="col-md-12">
						<input type="button" value="הפקת דו''ח מיוחד" class="btn marginTop20 marginRight10 mb-2" onclick="location.href='custom_reports.php?&project_id=<?=@$project_id?>';" />
						<input type="button" value="סיכום ישיבה" class="btn bgColorYellow text-success marginTop20 marginRight10 mb-2" onclick="location.href='resume_rdv.php?&project_id=<?=@$project_id?>';" />
						<input type="button" value="רשימת פרקים" class="btn marginTop20 marginRight10 mb-2" onclick="location.href='chapters.php?&project_id=<?=@$project_id?>';" />
						<input type="button" value="רשימת אחראיים" class="btn marginTop20 marginRight10 mb-2" onclick="location.href='responsibles.php?&project_id=<?=@$project_id?>';" />
						<input type="button" value="רשימת סוגי משימות" class="btn marginTop20 marginRight10 mb-2" onclick="location.href='tasks.php?&project_id=<?=@$project_id?>';" />
						<input type="button" value="רשימת איזורים" class="btn marginTop20 marginRight10 mb-2" onclick="location.href='areas.php?&project_id=<?=@$project_id?>';" />
						<input type="button" value="רשימת סטסטוסי התקדמות" class="btn marginTop20 marginRight10 mb-2" onclick="location.href='progress_status.php?&project_id=<?=@$project_id?>';" />
					</div>
				</div>
				
				<br/>

				<?php if($all_meetings_num_rows > 0) { ?>
					<div class="row" style="font-size:12px;text-align:center;direction:rtl;">
						<div align="center" class="col-md-12 mx-2" style="overflow-x:scroll;">
							<table border="1">							
								<tr style="background-color:silver;height:50px;">
									<th width="50px;" colspan="2" style="text-align:center;">&nbsp;</th>
									<th width="120px;" style="text-align:center;">נושא/תחום</th>
									<th width="120px;" style="text-align:center;">איזור/נושא</th>
									<th width="360px;" style="text-align:center;font-size:13px;">תיאור</th>
									<th width="120px;" style="text-align:center;">סוג משימה</th>								
									<th width="120px;" style="text-align:center;">אחראי</th>
									<th width="120px;" style="text-align:center;font-size:13px;">להעביר ל/ <br/> לאשר מול</th>
									<th width="75px;" style="text-align:center;">תאריך <br/> יצירת משימה</th>
									<th width="75px;" style="text-align:center;">תאריך יעד</th>
									<th width="100px;" style="text-align:center;">סטטוס <br/> התקדמות</th>
									<th width="30px;" style="text-align:center;">&nbsp;</th>
									<th width="30px;" style="text-align:center;">&nbsp;</th>
								</tr>
			
								<?php
								$query = $mysqli->prepare("SELECT * FROM dne_chapters WHERE id_project = ? ORDER BY id_display");
								$query->bind_param("i",$project_id);
								$query->execute(); 
								$query->store_result();
								$chapters = fetch($query);
									
								foreach($chapters as $item) {	
									$chapter_id = $item->id;
									$is_appears = 1;
									
									$query = $mysqli->prepare("SELECT m.id AS id,m.id_chapter AS id_chapter,m.id_rdv AS id_rdv,m.subject AS subject,m.id_area AS id_area,m.description AS description,m.id_task AS id_task ,m.id_responsible AS id_responsible,
															  m.id_pass_on AS id_pass_on,m.task_creation_date AS task_creation_date,m.destination_date AS destination_date,
															  m.id_progress_status AS id_progress_status,m.id_task_type AS id_task_type,m.is_change_row_style AS is_change_row_style,m.image1 AS image1,m.image2 AS image2,
															  m.updated_date AS updated_date
															  FROM dne_meetings m 
															  LEFT JOIN dne_tasks t ON m.id_task = t.id
															  WHERE m.id_project = ? AND m.id_chapter = ? AND m.is_appears = ?
															  ORDER BY t.id_display,m.subject,m.id_area,m.destination_date DESC");
									$query->bind_param("iii",$project_id,$chapter_id,$is_appears);
									$query->execute(); 
									$query->store_result();
									$meetings_num_rows = $query->num_rows;
									$meetings = fetch($query);
									
									if($meetings_num_rows > 0) {
									?>
										<tr style="background-color:#a3def0;height:40px;">
										  <td colspan="14" style="text-align:right;padding-right:5px;">
											  <a style="text-decoration:underline;" onclick="redirectToAddTaskForThisChapter(<?=@$chapter_id?>);"><strong><?=@$item->name?></strong></a>
										  </td>
										</tr>
										<?php 
										$count = 0;
										foreach($meetings as $item) {
											$meeting_id = @$item->id;
											$id_rdv = @$item->id_rdv;
											$id_chapter = @$item->id_chapter;
											$id_task_type = @$item->id_task_type;
											$subject = @$item->subject;
											$area_id = @$item->id_area;
											$description = @$item->description;
											$task_id = @$item->id_task;
											$responsible_id = @$item->id_responsible;
											$pass_on_id = @$item->id_pass_on;
											
											$bgcolor_num = 'white';
											if(@$item->image1 != '' || @$item->image2 != '')
											   $bgcolor_num = 'green';
											
											$is_change_row_style = @$item->is_change_row_style;
											
											$update_cell_bgcolor = 'background-color:white';
											
											$task_creation_date = '';
											if(@$item->task_creation_date != '0000-00-00')
												$task_creation_date = @$item->task_creation_date;
											
											$destination_date = '';
											if(@$item->destination_date != '0000-00-00')
												$destination_date = @$item->destination_date;
											
											$progress_status_id = @$item->id_progress_status;
											
											$is_appears_img1 = @$item->is_appears_img1;
											
											$updated_date = @$item->updated_date;
								
											$subject_bg_color = 'background-color:white';
											
											$query = $mysqli->prepare("SELECT * FROM dne_areas WHERE id = ?");
											$query->bind_param("i",$item->id_area);
											$query->execute();
											$query->store_result();
											$query = fetch_unique($query);
											$area = @$query->name;
											$area_bg_color = 'background-color:white';
											
											$description_bg_color = 'background-color:white';
											
											$query = $mysqli->prepare("SELECT * FROM dne_tasks WHERE id = ?");
											$query->bind_param("i",$item->id_task);
											$query->execute();
											$query->store_result();
											$query = fetch_unique($query);
											$task = @$query->name;
											$task_color = @$query->color;
											$task_bgcolor = @$query->bgcolor;
											
											$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id = ?");
											$query->bind_param("i",$item->id_responsible);
											$query->execute();
											$query->store_result();
											$query = fetch_unique($query);
											$responsible = @$query->name;
											$responsible_color = @$query->color;
											$responsible_bgcolor = @$query->bgcolor;
											
											$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id = ?");
											$query->bind_param("i",$item->id_pass_on);
											$query->execute();
											$query->store_result();
											$query = fetch_unique($query);
											$pass_on = @$query->name;
											$pass_on_bg_color = 'background-color:white';
											
											$query = $mysqli->prepare("SELECT * FROM dne_progress_status WHERE id = ?");
											$query->bind_param("i",$item->id_progress_status);
											$query->execute();
											$query->store_result();
											$query = fetch_unique($query);
											$progress_status = @$query->name;
											$progress_status_color = @$query->color;
											$progress_status_bgcolor = @$query->bgcolor;
											
											$task_creation_date_color = 'color:black';
											if($id_rdv > 0) 
												$task_creation_date_color = 'color:green';
											
											$task_creation_date_bg_color = 'background-color:white';
											
											$dest_date_color = 'color:black';
											$dest_date_bg_color = 'background-color:white';
											
											if($destination_date < date('Y-m-d')) { 
											   $dest_date_color = 'color:red;';
											}
											
											if($is_change_row_style === 1) {
												if($progress_status == 'בוצע/נמסר') {
												   $subject_bg_color = 'background-color:#dedede';
												   $area_bg_color = 'background-color:#dedede';
												   $description_bg_color = 'background-color:#dedede';
												   $task_bgcolor = '#dedede';
												   $responsible_bgcolor = '#dedede';
												   $pass_on_bg_color = 'background-color:#dedede';
												   $task_creation_date_bg_color = 'background-color:#dedede';
												   $dest_date_color = 'color:#dedede';
												   $dest_date_bg_color = 'background-color:#dedede';
												   $progress_status_bgcolor = '#dedede';
												}
												else if($task == 'בקרת איכות') {
												   $subject_bg_color = 'background-color:#fafd49';
												   $area_bg_color = 'background-color:#fafd49';
												   $description_bg_color = 'background-color:#fafd49';
												}
												else 
													$dest_date_color = 'color:white';
											}
											
											if(($last_pdf_date < $updated_date) && ($progress_status != 'בוצע/נמסר')) {
												$update_cell_bgcolor = 'background-color:'.$project->bgcolor_new_task;
												$subject_bg_color = 'background-color:'.$project->bgcolor_new_task;
												$area_bg_color = 'background-color:'.$project->bgcolor_new_task;
												$description_bg_color = 'background-color:'.$project->bgcolor_new_task;
												$pass_on_bg_color = 'background-color:'.$project->bgcolor_new_task;
												$task_creation_date_bg_color = 'background-color:'.$project->bgcolor_new_task;
												$dest_date_bg_color = 'background-color:'.$project->bgcolor_new_task;
											}
											$count++;
											?>
											<input type="hidden" id="id_chapter_<?=@$meeting_id?>" value="<?=@$id_chapter?>" />
											<input type="hidden" id="id_task_type_<?=@$meeting_id?>" value="<?=@$id_task_type?>" />
											<input type="hidden" id="is_appears_img1_<?=@$meeting_id?>" value="<?=@$is_appears_img1?>" />
											<input type="hidden" id="is_appears_img2_<?=@$meeting_id?>" value="<?=@$is_appears_img2?>" />								
											<tr>
												<td style="text-align:center;"><a onclick="DuplicateRecord(<?=@$meeting_id?>)" title="עותק" style="text-decoration:underline;cursor:pointer;"><i class="fa fa-plus"></i></a></td>
												<td style="text-align:center;<?=@$update_cell_bgcolor?>;background-color:<?=@$bgcolor_num?>"><a onclick="location.href='add_meeting.php?&project_id=<?=@$project_id?>&id=<?=@$meeting_id?>'" title="עדכן" style="text-decoration:underline;cursor:pointer;"><?=@$color_num?><?=@$count?></a></td>
												<td style="text-align:right;padding-right:5px;<?=@$subject_bg_color?>;">
													<div style="text-align:center;color:red;font-size:10px;" id="div_message_alert_down"></div>
													<input type="text" name="subject_<?=@$meeting_id?>" id="subject_<?=@$meeting_id?>" value="<?=@$subject?>" style="direction:rtl;width:98%;font-size:12px;<?=@$subject_bg_color?>;" />
												</td>	
												<td style="text-align:right;padding-right:5px;<?=@$area_bg_color?>;">
													<select id="area_<?=@$meeting_id?>" class="form-control" style="direction:rtl;width:98%;font-size:12px;<?=@$area_bg_color?>;font-weight:bold;" onchange="setData(<?=@$meeting_id?>,'id_area');">	
														<?php 
														foreach($areas as $item) {
														?>
															<option value="<?=@$item->id?>" <?php if($item->id == @$area_id) echo "selected";?>>
																<strong><?=@$item->name?></strong>
															</option>
															<?php
														}
														?>						
													</select>
												</td>
												<td style="text-align:right;padding-right:5px;<?=@$description_bg_color?>;"><textarea name="description_<?=@$meeting_id?>" id="description_<?=@$meeting_id?>" style="direction:rtl;width:98%;font-size:12px;<?=@$description_bg_color?>;"><?=@$description?></textarea></td>
												<td style="text-align:right;padding-right:5px;background-color:<?=@$task_bgcolor?>">
													<select id="task_<?=@$meeting_id?>" class="form-control" style="direction:rtl;width:98%;font-size:12px;color:<?=@$task_color?>;background-color:<?=@$task_bgcolor?>;font-weight:bold;" onchange="setData(<?=@$meeting_id?>,'id_task');">	
														<?php 
														foreach($tasks as $item) {
														?>
															<option value="<?=@$item->id?>" <?php if($item->id == @$task_id) echo "selected";?>>
																<strong><?=@$item->name?></strong>
															</option>
															<?php
														}
														?>						
													</select>
												</td>
												<td style="text-align:right;padding-right:5px;background-color:<?=@$responsible_bgcolor?>;">
													<select id="responsible_<?=@$meeting_id?>" style="direction:rtl;width:98%;font-size:12px;color:<?=@$responsible_color?>;background-color:<?=@$responsible_bgcolor?>;font-weight:bold;" onchange="setData(<?=@$meeting_id?>,'id_responsible');">	
														<?php 
														foreach($responsibles as $item) {
														?>
															<option value="<?=@$item->id?>" <?php if($item->id == @$responsible_id) echo "selected";?>>
																<strong><?=@$item->name?></strong>
															</option>
															<?php
														}
														?>						
													</select>
												</td>
												<td style="text-align:right;padding-right:5px;<?=@$pass_on_bg_color?>;">
													<select id="pass_on_<?=@$meeting_id?>" class="form-control" style="direction:rtl;width:98%;font-size:12px;<?=@$pass_on_bg_color?>;font-weight:bold;" onchange="setData(<?=@$meeting_id?>,'id_pass_on');">	
														<?php 
														foreach($responsibles as $item) {
														?>
															<option value="<?=@$item->id?>" <?php if($item->id == @$pass_on_id) echo "selected";?>>
																<strong><?=@$item->name?></strong>
															</option>
															<?php
														}
														?>						
													</select>
												</td>
												<td style="text-align:right;padding-right:5px;<?=@$task_creation_date_bg_color?>;">
													<input type="date" name="task_creation_date_<?=@$meeting_id?>" id="task_creation_date_<?=@$meeting_id?>" value="<?=@$task_creation_date?>" style="direction:rtl;width:98%;font-size:13px;<?=@$task_creation_date_color?>;<?=@$task_creation_date_bg_color?>;" onchange="setData(<?=@$meeting_id?>,'task_creation_date');" />
												</td>
												<td style="text-align:right;padding-right:5px;<?=@$dest_date_color?>;<?=@$dest_date_bg_color?>;">		
													<input type="date" name="destination_date_<?=@$meeting_id?>" id="destination_date_<?=@$meeting_id?>" value="<?=@$destination_date?>" style="direction:rtl;width:98%;font-size:13px;<?=@$dest_date_color?>;<?=@$dest_date_bg_color?>;font-weight:bold;" onchange="setData(<?=@$meeting_id?>,'destination_date');" />	
												</td>
												<td style="text-align:right;padding-right:5px;color:<?=@$progress_status_color?>;background-color:<?=@$progress_status_bgcolor?>;">
													<select id="progress_status_<?=@$meeting_id?>" class="form-control" style="direction:rtl;width:98%;font-size:12px;background-color:<?=@$progress_status_bgcolor?>;font-weight:bold;" onchange="setData(<?=@$meeting_id?>,'id_progress_status');">	
														<option>---סטטוס---</option>
														<?php 
														foreach($progress_status_s as $item) {
														?>
															<option value="<?=@$item->id?>" <?php if($item->id == @$progress_status_id) echo "selected";?>>
																<strong><?=@$item->name?></strong>
															</option>
															<?php
														}
														?>						
													</select>
												</td>
												<td style="text-align:center;"><img src="images/edit-button.svg" style="padding:10px;cursor:pointer;" title="Edite" onclick="editeMeeting(<?=@$meeting_id?>);" /></td>									
												<td style="text-align:center;"><img src="images/delete.svg" style="cursor:pointer;" title="מחק" onclick="return removeMeeting(<?=@$meeting_id?>);" /></td>	
											</tr>
											<?php
										}
									}
								}
								?>
							</table>		
						</div>
					</div>
					
					<div class="row" style="margin-top:20px;text-align:center;">
						<div class="col-md-12" style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;">
							<!--<input type="submit" value="שלח דוא''ל" name="send_email_btn" class="btn btn-primary" style="margin-right:10px;font-size:14px;width:140px;" />-->
							<div><input type="button" value="דו''ח ספק" class="btn marginRight10 mb-2" onclick="location.href='meetings_filters_by_responsible.php?project_id=<?=@$project_id?>'" /></div>
							<div><input type="button" class="btn marginRight10 mb-2" value="סינון/חיפוש" onclick="location.href='meetings_filters.php?project_id=<?=@$project_id?>'" /></div>			    
							<div><input type="button" value="משימות חדשות / מעודכנות" class="btn marginRight10 mb-2" style="background-color:<?=@$project->bgcolor_new_task?>;font-weight:bold;" onclick="location.href='new_meetings.php?project_id=<?=@$project_id?>'"  /></div>
							<div><input type="button" value="דו'ח סטטוס פרוייקט" class="btn marginRight10" onclick="toMeetingsReport();" /></div>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
		</form> 
	</body>
</html>

<script>
$('#to_add_meeting_btn').click (function (e){  
	var form_data = new FormData();		
	form_data.append('report_date',$('#report_date').val());
	
	$.ajax({
		type: 'POST',
		url: 'set_report_date_session.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){  
		   location.href='add_meeting.php?id=0&project_id='+$('#project_id').val();
		},
	});						       			   
})

function redirectToAddTaskForThisChapter(chapter_id) {
	var form_data = new FormData();		
	form_data.append('report_date',$('#report_date').val());

	$.ajax({
		type: 'POST',
		url: 'set_report_date_session.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){  
		   location.href='add_meeting.php?id=0&chapter_id='+chapter_id+'&project_id='+$('#project_id').val();
		},
	});
}

function setData(meeting_id,field) {
	var form_data = new FormData();	
	form_data.append('meeting_id',meeting_id);
	
	if(field == 'id_area') {
	    var id_area_elem = '#area_'+meeting_id;
		form_data.append('field','id_area');
		form_data.append('id_area',$(id_area_elem).val());
	}
	else if(field == 'id_task') {
	    var id_task_elem = '#task_'+meeting_id;
		form_data.append('field','id_task');
		form_data.append('id_task',$(id_task_elem).val());
	}
	else if(field == 'id_responsible') {
	    var id_responsible_elem = '#responsible_'+meeting_id;
		form_data.append('field','id_responsible');
		form_data.append('id_responsible',$(id_responsible_elem).val());
	}
	else if(field == 'id_pass_on') {
	    var id_pass_on_elem = '#pass_on_'+meeting_id;
		form_data.append('field','id_pass_on');
		form_data.append('id_pass_on',$(id_pass_on_elem).val());
	}
	else if(field == 'task_creation_date') {
	    var task_creation_date_elem = '#task_creation_date_'+meeting_id;
		form_data.append('field','task_creation_date');
		form_data.append('task_creation_date',$(task_creation_date_elem).val());
	}
	else if(field == 'destination_date') {
	    var destination_date_elem = '#destination_date_'+meeting_id;
		form_data.append('field','destination_date');
		form_data.append('destination_date',$(destination_date_elem).val());
	}
	else if(field == 'id_progress_status') {
	    var progress_status_elem = '#progress_status_'+meeting_id;
		form_data.append('field','id_progress_status');
		form_data.append('id_progress_status',$(progress_status_elem).val());
	}
	
	$.ajax({
		type: 'POST',
		url: 'set_data.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){;
			window.location.reload();
		},
	});	
}

function DuplicateRecord(meeting_id) {
	var form_data = new FormData();	
	form_data.append('table_name','dne_meetings');
	form_data.append('id',meeting_id);
	$.ajax({
		type: 'POST',
		url: 'duplicate_record.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){  
		   location.href='meetings.php?project_id='+$('#project_id').val();
		},
	});					
}

function editeMeeting(meeting_id) {
	var subject = '#subject_'+meeting_id;
	var area = '#area_'+meeting_id;
	var description = '#description_'+meeting_id;
	var task = '#task_'+meeting_id;
	var responsible = '#responsible_'+meeting_id;
	var pass_on = '#pass_on_'+meeting_id;
	var task_creation_date = '#task_creation_date_'+meeting_id;
	var destination_date = '#destination_date_'+meeting_id;
	var progress_status = '#progress_status_'+meeting_id;
	var is_appears_img1 = '#is_appears_img1_'+meeting_id;
	var is_appears_img2 = '#is_appears_img2_'+meeting_id;
	var id_task_type = '#id_task_type_'+meeting_id;
	var id_chapter = '#id_chapter_'+meeting_id;

	var form_data = new FormData();
	form_data.append('id',meeting_id);
	form_data.append('id_project',$('#project_id').val());
	form_data.append('subject',$(subject).val());
	form_data.append('id_area',$(area).val());
	form_data.append('description',$(description).val());
	form_data.append('id_task',$(task).val());
	form_data.append('id_responsible',$(responsible).val());
	form_data.append('id_pass_on',$(pass_on).val());
	form_data.append('task_creation_date',$(task_creation_date).val());
	form_data.append('destination_date',$(destination_date).val());
	form_data.append('id_progress_status',$(progress_status).val());
	form_data.append('is_appears_img1',$(is_appears_img1).val());
	form_data.append('is_appears_img2',$(is_appears_img2).val());
	form_data.append('id_task_type',$(id_task_type).val());
	form_data.append('id_chapter',$(id_chapter).val());
	
	$.ajax({
		type: 'POST',
		url: 'meeting_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){ 
			if(data == 'empty')	{
				if($(subject).val().length == 0)			
					$(subject).css('border-color','red');
				else if(!($(subject).val().length == 0))
					$(subject).css('border-color','initial');	
				
				$('#div_message_alert_down').html("Please fill this field"); 
			}
			else 
				location.reload();
		},
	})
}

function removeMeeting(id) {
	if(confirm("האם אתה בטוח למחוק את המשימה הזאת ?")) {
        var form_data = new FormData();	
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'meeting_delete.php',
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

function toMeetingsReport() {
	var form_data = new FormData();	
	form_data.append('from','meetings');	
	form_data.append('id_project',$('#project_id').val());			
	$.ajax({
		type: 'POST',
		url: 'last_pdf_data_update.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){
			window.open('meetings_report.php?project_id='+$('#project_id').val(),'_blank');
		},
	});		
}
</script>

<style>
.title {
	margin-top: 25px;
	text-align: center;
	font-size: 20px;
	color: #349feb;
	direction: rtl;
}

a {
	color: inherit;
}

.marginTop20 {
	margin-top: 20px;
}

.marginRight10 {
	margin-top: 10px;
}

.btn {
	background-color: #218FD6;
	color: white;
}

.btn:hover {
   background-color: #3370d6;
   color: white;
}

.bgColorYellow {
	background-color: yellow;
}

.bgColorYellow:hover {
	background-color: #f5ef42;
}
</style>