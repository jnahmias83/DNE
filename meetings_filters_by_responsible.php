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

$sup_type = 'S';
$des_type = 'D';
$etrp_type = 'E';

$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS name_he
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? AND s.type = ?
                          ORDER BY name_he ASC");
$query->bind_param("is",$project_id,$sup_type);
$query->execute();
$query->store_result();
$suppliers = fetch($query);


$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS name_he
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? AND s.type = ?
                          ORDER BY name_he ASC");
$query->bind_param("is",$project->id,$des_type);
$query->execute();
$query->store_result();
$designers = fetch($query);

$query = $mysqli->prepare("SELECT ps.id AS id,s.name_he AS name_he
                          FROM dne_projects_suppliers ps
						  LEFT JOIN dne_projects p ON ps.id_project = p.id
						  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
						  WHERE ps.id_project = ? AND s.type = ?
                          ORDER BY name_he ASC");
$query->bind_param("is",$project_id,$etrp_type);
$query->execute();
$query->store_result();
$entrepreneur = fetch_unique($query);

$responsibles_array = array();

foreach ($meetings as $item) {	
	$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id = ?");
    $query->bind_param("i",$item->id_responsible);
    $query->execute();
	$query->store_result();
	$responsible = fetch_unique($query);
	$responsibles_array[@$responsible->id]= @$responsible->name;
}

asort($responsibles_array);
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
						<a style="text-decoration:underline;" href="project_home.php?id=<?=@$project_id?>">
							<?=@$project->name.'<br/> דו\'ח סטטוס פרוייקט<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4)?>
						</a>
					</div>					
			    </div>
			
				<div class="row" style="margin-top:20px;font-size:14px;text-align:center;direction:rtl;">
					<div class="col-md-6 mx-2">
						<select id="suppliers" style="width:200px;height:24px;">
							<option value="0">--- משרד/חברה ---</option>
							<optgroup label="חברה">
							<?php foreach ($suppliers as $item) { ?>
								<option value="<?=@$item->id?>"><?=@$item->name_he?></option>
							<?php } ?>
							</optgroup>
							<optgroup label="משרד">
							<?php foreach ($designers as $item) { ?>
								<option value="<?=@$item->id?>"><?=@$item->name_he?></option>
							<?php } ?>
							</optgroup>
							<optgroup label="יזם">
								<option value="<?=@$entrepreneur->id?>"><?=@$entrepreneur->name_he?></option>
							</optgroup>
						</select>
					</div>
				
					<div class="col-md-6 mx-2" id="responsibles_list"></div>
				</div>
				
				<div class="row" style="margin-top:30px;font-size:16px;text-align:center;direction:rtl;">
					<div class="col-md-12">
						<button type="button" class="btn marginTop20 mb-2" onclick="filterMeetings();">חפש <i title="חפס" class="fa fa-search" style="font-size:25px;"></i></button>
					</div>
				</div>
			</div>
		</form> 
	</body>
</html>

<script>
$('#suppliers').on('change', function (){
	$.post('populate_responsibles_list.php',{id_projects_suppliers:$(this).val()},function(data){
		$('#responsibles_list').html(data);
	});
});

function filterMeetings() {
	var responsibles = '';
	$('#responsibles:checked').each(function(i){
	  responsibles+= $(this).val()+',';
	});
	responsibles = responsibles.substring(0,responsibles.length - 1);
	
	var sql = 'SELECT c.name AS name,m.id AS id,m.id_task_type AS id_task_type,m.id_chapter AS id_chapter,m.is_pdf_appears AS is_pdf_appears,m.subject AS subject,m.id_rdv AS id_rdv,m.id_area,m.description,m.id_task,m.id_responsible,m.id_pass_on,m.task_creation_date,m.destination_date,m.id_progress_status,m.updated_date AS updated_date,m.image1 AS image1,m.is_appears_img1 AS is_appears_img1,m.image2 AS image2,m.is_appears_img2 AS is_appears_img2,m.is_change_row_style AS is_change_row_style FROM dne_meetings m LEFT JOIN dne_chapters c ON m.id_chapter = c.id LEFT JOIN dne_tasks t ON m.id_task = t.id WHERE m.id_project ='+$('#project_id').val()+ ' AND m.is_appears = 1 ';
	
	var params = [];
	
	if(responsibles != '') {
		params.push('(m.id_responsible IN('+responsibles+') OR m.id_pass_on IN('+responsibles+'))');
	}
        
	var params_str = '';
	if(params.length>0)
		params_str = params.join(' AND ');
	
	if(params_str != '')
	   sql+= ' AND '+params_str;
   
    var form_data = new FormData();	
		form_data.append('sql',sql);			
		$.ajax({
			type: 'POST',
			url: 'set_session_filter_meetings_sql.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
				location.href = 'results_filters_meetings_by_responsible.php?project_id='+$('#project_id').val();	
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

.btn {
	background-color: #218FD6;
	color: white;
}

.btn:hover {
   background-color: #3370d6;
   color: white;
}
</style>