<?php
session_start();
require_once('tcpdf_min/config/tcpdf_config.php');
require_once('tcpdf_min/tcpdf.php');
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$sql = @$_SESSION['sql'];
$project_id = @$_GET['project_id'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$dir_table = 'rtl';
$style_table = "margin-top:25px;margin-left:1%;";
$title = $project->name_he.'<br/>דו\'ח משימות<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4);

$counts_array = array();
$images_array = array();
$is_appears_img_array = array();
$chapters_array = array();

$id_responsibles_part = '';
if(strpos($sql,"m.id_responsible")!== false) {
	$sql_array = explode(' AND ',$sql);
							
	for($i=1;$i<sizeof($sql_array);$i++) {
		if(strpos($sql_array[$i],'m.id_responsible') !== false) {
			$id_responsibles_part = $sql_array[$i];
			$id_responsibles_part = str_replace('m.id_responsible IN(','',$id_responsibles_part);
			$id_responsibles_part = substr($id_responsibles_part, 0, -1);
		}
	}
	
	$id_responsibles_part_array = explode('OR',$id_responsibles_part);
	$id_responsibles_part = $id_responsibles_part_array[0];
	$id_responsibles_part = str_replace('(','', $id_responsibles_part);
	$id_responsibles_part = str_replace(')','', $id_responsibles_part);
}

$id_progress_status_part = '';
$id_progress_status_array = array();
if(strpos($sql,"m.id_progress_status")!== false) {
	$sql_array = explode(' AND ',$sql);
	
	for($i=1;$i<sizeof($sql_array);$i++) {
		if(strpos($sql_array[$i],'m.id_progress_status') !== false) {
			$id_progress_status_part = $sql_array[$i];
			$id_progress_status_part = str_replace('m.id_progress_status IN(','',$id_progress_status_part);
			$id_progress_status_part = substr($id_progress_status_part, 0, -1);
			$id_progress_status_array = explode(',',$id_progress_status_part);
		}
	}
	
	for($i=0;$i<sizeof($id_progress_status_array);$i++) {
		$query = $mysqli->prepare("SELECT name FROM dne_progress_status WHERE id = ?");
		$query->bind_param("i",$id_progress_status_array[$i]);
		$query->execute();
		$query->store_result();
		$query = fetch_unique($query);
		
		if($query->name == 'ארכיון' && sizeof($id_progress_status_array) == 1)
		   $sql = str_replace('m.is_appears = 1','m.is_appears = 0',$sql);
		else if($query->name == 'ארכיון' && sizeof($id_progress_status_array) > 1) {
			$sql = str_replace('m.is_appears = 1','m.is_appears IN(0,1)',$sql);
		}
	}
}

$query = $mysqli->prepare("SELECT * FROM dne_log_create_pdf WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$last_log_pdf_cd_num_rows = $query->num_rows;

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

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setFooterData(array(0,64,0), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setFontSubsetting(true);
$pdf->SetFont('freesans', '', 12);
$pdf->setPrintHeader(false);

$html_header = '<table><tr><td style="text-align:center;"><img src="images/davidnahmias_stripe.jpg" /><br/><br/></td></tr>';

$html1_body = '<tr style="font-size:16px;"><td width="40px;">&nbsp;</td><td style="text-align:center;padding-top:30px;"><span dir="'.$dir_table.'"><strong><u>'.$title.'</u></strong></span></td></tr></table>';
$html1_body.= '<div class="row">';
$html1_body.= '<div class="col-md-12">';
$html1_body.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="4%">&nbsp;</td><td><table cellpadding="4">';
$html1_body.='<tr style="height:50px;background-color:silver;font-size:13px;">';
$html1_body.='<th width="30" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">&#x2116;</th>';
$html1_body.='<th width="90" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">סוג משימה</th>';
$html1_body.='<th width="90" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">נושא/תחום</th>';
$html1_body.='<th width="90" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">איזור/נושא</th>';
$html1_body.='<th width="320" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">תיאור</th>';
$html1_body.='<th width="80" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">אחראי</th>';
$html1_body.='<th width="90" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">להעביר ל/ <br/> לאשר מול</th>';
$html1_body.='<th width="50" style="text-align:center;border:1px solid black;font-size:10px;font-weight:bold;">יצירת <br/> משימה</th>';
$html1_body.='<th width="50" style="text-align:center;border:1px solid black;font-size:10px;">תאריך <br/> יעד</th>';
$html1_body.='<th width="85" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">סטטוס <br/> התקדמות</th>';
$html1_body.='</tr>';

$position_where = strpos($sql,"WHERE");
$where_length = strlen($sql)-$position_where;
$where_part_sql = substr($sql,$position_where,$where_length);
$where_part_sql_array = explode(' AND ',$where_part_sql);
							
$chapter_filter = '';
if(strpos($where_part_sql,"m.id_chapter")!== false) {								
    for($i=0;$i<sizeof($where_part_sql_array);$i++) {
		if(strpos($where_part_sql_array[$i],'m.id_chapter') !== false) {
			$where_part_sql_array[$i] = str_replace('m.id_chapter','id',$where_part_sql_array[$i]);
		}
	}

	$where_part_sql = implode(' AND ',$where_part_sql_array);
	$chapter_filter = $where_part_sql;
	$chapter_filter_array = explode('AND ',$chapter_filter);
	$chapter_filter = $chapter_filter_array[2];
	$chapter_filter = ' AND '.$chapter_filter;
}

$sql_chapters = "SELECT * FROM dne_chapters WHERE id_project = ? ".$chapter_filter.' ORDER BY id_display';
$query = $mysqli->prepare($sql_chapters);
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$chapters = fetch($query);
	
foreach($chapters as $item) {	
	$chapter_id = $item->id;
	
	$sql_array = explode(' AND ',$sql);

	for($i=1;$i<sizeof($sql_array);$i++) {
		if(strpos($sql_array[$i],'m.id_chapter') !== false) {
			$sql_array[$i] = 'm.id_chapter ='.$chapter_id;
			$sql = implode(' AND ',$sql_array);
		}
	}
	
	if(strpos($sql,'m.id_chapter') === false) 
	   $sql.= ' AND m.id_chapter ='.$chapter_id;
	
	$is_pdf_appears = 1;
	$query = $mysqli->prepare($sql.' AND m.is_pdf_appears = ?
	                          ORDER BY t.id_display,m.subject,m.id_area,m.destination_date DESC');
	$query->bind_param("i",$is_pdf_appears);
	$query->execute(); 
	$query->store_result();
	$meetings_num_rows = $query->num_rows;
	$meetings = fetch($query);
	
	if($meetings_num_rows >0) {
		$html1_body.='<tr style="background-color:#a3def0;height:40px;font-size:11px;">';
		$html1_body.='<td colspan="12" style="text-align:right;padding-right:5px;border:1px solid black;"><strong>'.@$item->name.'</strong></td>';
		$html1_body.='</tr>';
		
		$count1 = 0;
		foreach($meetings as $item) {
			$count1++;
			
			$meeting_id = @$item->id;
			$id_rdv = @$item->id_rdv;
			$subject = @$item->subject;
			$area_id = @$item->id_area;
			$description = @$item->description;
			$task_id = @$item->id_task;
			$responsible_id = @$item->id_responsible;
			$pass_on_id = @$item->id_pass_on;
			$is_change_row_style = @$item->is_change_row_style;

			$color_num = 'black';
			if(@$item->image1 != '' || @$item->image2 != '')
			   $color_num = 'green';
			
			$update_cell_bg_color = 'background-color:white';
			
			$task_creation_date = '';
			if(@$item->task_creation_date != '0000-00-00')
				$task_creation_date = substr(@$item->task_creation_date,8,2).'/'.substr(@$item->task_creation_date,5,2);
			
			$destination_date = '';
			if(@$item->destination_date != '0000-00-00')
				$destination_date = substr(@$item->destination_date,8,2).'/'.substr(@$item->destination_date,5,2);
			
			$progress_status_id = @$item->id_progress_status;
			
			$query = $mysqli->prepare("SELECT name FROM dne_chapters WHERE id = ?");
			$query->bind_param("i",$item->id_chapter);
			$query->execute(); 
			$query->store_result();
			$chapter = fetch_unique($query);
			
			if($item->image1 != '' && $item->is_appears_img1 === 1) {
				array_push($counts_array,$count1);
				array_push($images_array,$item->image1);
				array_push($is_appears_img_array,$item->is_appears_img1);
				array_push($chapters_array,$chapter->name);
			}
			
			if($item->image2 != '' && $item->is_appears_img2 === 1) {
				array_push($counts_array,$count1);
				array_push($images_array,$item->image2);
				array_push($is_appears_img_array,$item->is_appears_img2);
				array_push($chapters_array,$chapter->name);
			}
			
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
			
			if(@$item->destination_date < date('Y-m-d')) { 
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
			
			if(($last_pdf_date <= $updated_date) && ($progress_status != 'בוצע/נמסר')) {
				$update_cell_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$subject_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$area_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$description_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$pass_on_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$task_creation_date_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$dest_date_bg_color = 'background-color:'.$project->bgcolor_new_task;
			}
														
			$html1_body.='<tr style="font-size:11px;">';	
            $html1_body.='<td width="30" style="text-align:center;'.@$update_cell_bg_color.';border:1px solid black;color:'.$color_num.'">'.@$count1.'</td>';
            $html1_body.='<td width="90" style="text-align:center;color:'.@$task_color.';background-color:'.@$task_bgcolor.';border:1px solid black;"><strong>'.@$task.'</strong></td>'; 			
			$html1_body.='<td width="90" style="text-align:right;padding-right:5px;'.@$subject_bg_color.';border:1px solid black;">'.@$subject.'</td>';	
			$html1_body.='<td width="90" style="text-align:right;padding-right:5px;'.@$area_bg_color.';border:1px solid black;">'.@$area.'</td>';
			$html1_body.='<td width="320" style="text-align:right;padding-right:5px;'.@$description_bg_color.';border:1px solid black;">'.@$description.'</td>';
			$html1_body.='<td width="80" style="text-align:center;color:'.@$responsible_color.';background-color:'.@$responsible_bgcolor.';border:1px solid black;"><strong>'.@$responsible.'</strong></td>';
			$html1_body.='<td width="90" style="text-align:center;border:1px solid black;'.@$pass_on_bg_color.'"><strong>'.@$pass_on.'</strong></td>';
			$html1_body.='<td width="50" style="text-align:center;border:1px solid black;font-size:10px;'.$task_creation_date_color.';'.$task_creation_date_bg_color.'">'.@$task_creation_date.'</td>';
			$html1_body.='<td width="50" style="text-align:center;border:1px solid black;font-size:10px;'.@$dest_date_color.';'.$dest_date_bg_color.'"><strong>'.@$destination_date.'</strong></td>';
			$html1_body.='<td width="85" style="text-align:center;color:'.@$progress_status_color.';background-color:'.@$progress_status_bgcolor.';border:1px solid black;"><strong>'.@$progress_status.'</strong></td>';
			$html1_body.='</tr>';
		}
	}
}
							
$html1_body.='</table></td></tr></table>';
$html1_body.='</div>';
$html1_body.='</div>';

$html1 = $html_header.$html1_body;

$pdf->setRTL(true);
$pdf->AddPage();
$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
$pdf->writeHTMLCell(0, 0, '', '', $html1, 0, 1, 0, true, '', true);

$html2_body = '<tr style="font-size:16px;"><td width="40px;">&nbsp;</td><td style="text-align:center;padding-top:30px;"><span dir="'.$dir_table.'"><strong><u>'.$title.'</u></strong></span></td></tr></table>';
$html2_body.= '<div class="row">';
$html2_body.= '<div class="col-md-12">';
$html2_body.= '<table dir="'.$dir_table.'" style="'.$style_table.'"><tr><td width="4%">&nbsp;</td><td><table cellpadding="4">';
$html2_body.='<tr style="height:50px;background-color:silver;font-size:13px;">';
$html2_body.='<th width="30" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">&#x2116;</th>';
$html2_body.='<th width="90" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">סוג משימה</th>';
$html2_body.='<th width="90" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">נושא/תחום</th>';
$html2_body.='<th width="90" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">איזור/נושא</th>';
$html2_body.='<th width="320" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">תיאור</th>';
$html2_body.='<th width="80" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">אחראי</th>';
$html2_body.='<th width="90" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">להעביר ל/ <br/> לאשר מול</th>';
$html2_body.='<th width="50" style="text-align:center;border:1px solid black;font-size:10px;font-weight:bold;">יצירת <br/> משימה</th>';
$html2_body.='<th width="50" style="text-align:center;border:1px solid black;font-size:10px;">תאריך <br/> יעד</th>';
$html2_body.='<th width="85" style="text-align:center;border:1px solid black;font-size:11px;font-weight:bold;">סטטוס <br/> התקדמות</th>';
$html2_body.='</tr>';

$is_html2_appears = false;

foreach($chapters as $item) {	
	$chapter_id = $item->id;
	
	$sql_array = explode(' AND ',$sql);

	for($i=1;$i<sizeof($sql_array);$i++) {
		if(strpos($sql_array[$i],'id_chapter') !== false) {
			$sql_array[$i] = 'id_chapter ='.$chapter_id;
			$sql = implode(' AND ',$sql_array);
		}
	}
	
	if(strpos($sql,'id_chapter') === false) 
	   $sql.= ' AND id_chapter ='.$chapter_id;
	
	$is_pdf_appears = 1;
	$query = $mysqli->prepare($sql.' AND m.is_pdf_appears = ? AND (image1 <> \'\' OR image2 <> \'\')
	                          ORDER BY t.id_display,m.subject,m.id_area,m.destination_date DESC');
	$query->bind_param("i",$is_pdf_appears);
	$query->execute(); 
	$query->store_result();
	$meetings_num_rows = $query->num_rows;
	$meetings = fetch($query);
	
	if($meetings_num_rows > 0) 
	   $is_html2_appears = true;
	
	if($meetings_num_rows > 0) {
		$html2_body.='<tr style="background-color:#a3def0;height:40px;font-size:11px;">';
		$html2_body.='<td colspan="12" style="text-align:right;padding-right:5px;border:1px solid black;"><strong>'.@$item->name.'</strong></td>';
		$html2_body.='</tr>';
		
		$count2 = 0;
		foreach($meetings as $item) {
			$count2++;
			
			$meeting_id = @$item->id;
			$id_rdv = @$item->id_rdv;
			$subject = @$item->subject;
			$area_id = @$item->id_area;
			$description = @$item->description;
			$task_id = @$item->id_task;
			$responsible_id = @$item->id_responsible;
			$pass_on_id = @$item->id_pass_on;
			$is_change_row_style = @$item->is_change_row_style;
			
			$image1 = @$item->image1;

			$color_num = 'black';
			if(@$item->image1 != '' || @$item->image2 != '')
			   $color_num = 'green';
			
			$update_cell_bg_color = 'background-color:white';
			
			$task_creation_date = '';
			if(@$item->task_creation_date != '0000-00-00')
				$task_creation_date = substr(@$item->task_creation_date,8,2).'/'.substr(@$item->task_creation_date,5,2);
			
			$destination_date = '';
			if(@$item->destination_date != '0000-00-00')
				$destination_date = substr(@$item->destination_date,8,2).'/'.substr(@$item->destination_date,5,2);
			
			$progress_status_id = @$item->id_progress_status;
			
			$query = $mysqli->prepare("SELECT name FROM dne_chapters WHERE id = ?");
			$query->bind_param("i",$item->id_chapter);
			$query->execute(); 
			$query->store_result();
			$chapter = fetch_unique($query);
			
			if($item->image1 != '' && $item->is_appears_img1 === 1) {
				array_push($counts_array,$count1);
				array_push($images_array,$item->image1);
				array_push($is_appears_img_array,$item->is_appears_img1);
				array_push($chapters_array,$chapter->name);
			}
			
			if($item->image2 != '' && $item->is_appears_img2 === 1) {
				array_push($counts_array,$count1);
				array_push($images_array,$item->image2);
				array_push($is_appears_img_array,$item->is_appears_img2);
				array_push($chapters_array,$chapter->name);
			}
			
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
			
			if(@$item->destination_date < date('Y-m-d')) { 
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
			
			if(($last_pdf_date <= $updated_date) && ($progress_status != 'בוצע/נמסר')) {
				$update_cell_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$subject_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$area_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$description_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$pass_on_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$task_creation_date_bg_color = 'background-color:'.$project->bgcolor_new_task;
				$dest_date_bg_color = 'background-color:'.$project->bgcolor_new_task;
			}
														
			$html2_body.='<tr style="font-size:11px;">';	
            $html2_body.='<td width="30" style="text-align:center;'.@$update_cell_bg_color.';border:1px solid black;color:'.$color_num.'">'.@$count2.'</td>';
            $html2_body.='<td width="90" style="text-align:center;color:'.@$task_color.';background-color:'.@$task_bgcolor.';border:1px solid black;"><strong>'.@$task.'</strong></td>';			
			$html2_body.='<td width="90" style="text-align:right;padding-right:5px;'.@$subject_bg_color.';border:1px solid black;">'.@$subject.'</td>';	
			$html2_body.='<td width="90" style="text-align:right;padding-right:5px;'.@$area_bg_color.';border:1px solid black;">'.@$area.'</td>';
			$html2_body.='<td width="320" style="text-align:right;padding-right:5px;'.@$description_bg_color.';border:1px solid black;">'.@$description.'</td>';
			$html2_body.='<td width="80" style="text-align:center;color:'.@$responsible_color.';background-color:'.@$responsible_bgcolor.';border:1px solid black;"><strong>'.@$responsible.'</strong></td>';
			$html2_body.='<td width="90" style="text-align:center;border:1px solid black;'.@$pass_on_bg_color.'"><strong>'.@$pass_on.'</strong></td>';
			$html2_body.='<td width="50" style="text-align:center;border:1px solid black;font-size:10px;'.$task_creation_date_color.';'.$task_creation_date_bg_color.'">'.@$task_creation_date.'</td>';
			$html2_body.='<td width="50" style="text-align:center;border:1px solid black;font-size:10px;'.@$dest_date_color.';'.$dest_date_bg_color.'"><strong>'.@$destination_date.'</strong></td>';
			$html2_body.='<td width="85" style="text-align:center;color:'.@$progress_status_color.';background-color:'.@$progress_status_bgcolor.';border:1px solid black;"><strong>'.@$progress_status.'</strong></td>';
			$html2_body.='</tr>';	

            if(strpos($image1,'Snag') === false)
			  $html2_body.='<tr><td colspan="10"><img src="uploads/'.$image1.'" height="200" width="200" style="object-fit:fixed;" /></td></tr>';
		}
	}
}
							
$html2_body.='</table></td></tr></table>';
$html2_body.='</div>';
$html2_body.='</div>';

if($is_html2_appears) {
	$html2 = $html_header.$html2_body;
	$pdf->setRTL(true);
	$pdf->AddPage();
	$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
	$pdf->writeHTMLCell(0, 0, '', '', $html2, 0, 1, 0, true, '', true);
}

ob_end_clean();

if($_SESSION['id_responsibles_part'] == '') 
   $pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Filtered Tasks Report-'.$project->nickname.'.pdf';
else {
	$id_reponsibles_part_array = explode(',',$_SESSION['id_responsibles_part']);
	$num_responsibles_filter = sizeof($id_reponsibles_part_array); 
	
	if($num_responsibles_filter > 1)
	   $pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Filtered Tasks Report-'.$project->nickname.'.pdf';
   
	else {
		$query = $mysqli->prepare("SELECT id_projects_suppliers FROM dne_responsibles WHERE id = ?");
		$query->bind_param("i",$_SESSION['id_responsibles_part']);
		$query->execute();
		$query->store_result();
		$query = fetch_unique($query);
		$id_projects_suppliers = $query->id_projects_suppliers;
	
		if($query->id_projects_suppliers > 0) {
			$query = $mysqli->prepare("SELECT s.nickname AS nickname 
									  FROM dne_projects_suppliers ps 
									  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
									  WHERE ps.id = ?");
			$query->bind_param("i",$id_projects_suppliers);
			$query->execute();
			$query->store_result();
			$query = fetch_unique($query);
			$sup_nickname = $query->nickname;
			$pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-'.$sup_nickname.'-Tasks Report-'.$project->nickname.'.pdf';
		}
		else 
			$pdf_name = substr(date('Y-m-d'),0,4).substr(date('Y-m-d'),5,2).substr(date('Y-m-d'),8,2).'-Filtered Tasks Report-'.$project->nickname.'.pdf';
		}
}
$pdf->Output($pdf_name,'I');
?>