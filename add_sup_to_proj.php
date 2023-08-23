<?php
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$id = @$_GET['id'];

$sup_type = "S";
$des_type = "D";

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT distinct sfow.name_he AS fow,sfow.id AS id 
                          FROM dne_suppliers s 
                          LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id 
						  WHERE sfow.sup_type = ?
						  ORDER BY sfow.name_he ASC");
$query->bind_param("s",$sup_type);
$query->execute();
$query->store_result();
$sup_field_of_work = fetch($query);

$query = $mysqli->prepare("SELECT distinct sfow.name_he AS fow,sfow.id AS id
                          FROM dne_suppliers s 
                          LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id 
						  WHERE sfow.sup_type = ?
						  ORDER BY sfow.name_he ASC");
$query->bind_param("s",$des_type);
$query->execute();
$query->store_result();
$des_field_of_work = fetch($query);

$query = $mysqli->prepare("SELECT ps.id AS id,ps.id_project AS id_project,s.name_he AS name_he,s.phone AS phone,s.email_office AS email_office,sfow.name_he AS sfow_name_he
						  FROM dne_projects_suppliers AS ps 
						  LEFT JOIN dne_projects AS p ON ps.id_project = p.id 
						  LEFT JOIN dne_suppliers AS s ON ps.id_supplier = s.id
						  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
						  WHERE p.id = ? AND s.type = ?
						  ORDER BY sfow.name_he,s.name_he");
$query->bind_param("is",$id,$sup_type);
$query->execute(); 
$query->store_result();
$existing_sup_proj_num_rows = $query->num_rows;
$existing_sup_proj = fetch($query);

$query = $mysqli->prepare("SELECT ps.id AS id,ps.id_project AS id_project,s.name_he AS name_he,s.phone AS phone,s.email_office AS email_office,sfow.name_he AS sfow_name_he
						  FROM dne_projects_suppliers AS ps 
						  LEFT JOIN dne_projects AS p ON ps.id_project = p.id 
						  LEFT JOIN dne_suppliers AS s ON ps.id_supplier = s.id
						  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
						  WHERE p.id = ? AND s.type = ?
						  ORDER BY sfow.name_he,s.name_he");
$query->bind_param("is",$id,$des_type);
$query->execute(); 
$query->store_result();
$existing_des_proj_num_rows = $query->num_rows;
$existing_des_proj = fetch($query);
?>          

		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" />
			
			<br/>
			
			<div class="row" style="margin-top:25px;text-align:center;">
			    <div class="col-md-12">
				    <img src="images/davidnahmias_logo.png" width="170px" height="170px" />
				</div>
			</div>
			
			<div class="container">
			    <div class="row title">	
					<div class="col-md-12">
						<a href="project_home.php?id=<?=@$id?>">
							<?=@$project->name_he?> <br/> רשימת אנשי קשר <br/> <?=substr(date('Y-m-d'),8,2)."/".substr(date('Y-m-d'),5,2)."/".substr(date('Y-m-d'),0,4)?>
						</a>
					</div>					
			    </div>																							

				<div class="row title-2">	
					<div class="col-md-12">
						רשימת מתכננים ויועצים
					</div>					
				</div>

				<br/>
				
				<?php if($existing_des_proj_num_rows > 0) { ?>
					<div class="row" style="font-size:14px;direction:rtl;">
						<div align="center" class="col-md-12 mx-2">
							<table border="1" cellpadding="5" cellspacing="5">
								<tr style="background-color:silver;">
									<th style="text-align:center;" width="130px;">תחום</th>
									<th style="text-align:center;" width="160px;">שם</th>
									<th style="text-align:center;" width="120px;">טלפון</th>
									<th style="text-align:center;" width="200px;">דוא''ל</th>
									<th width="40px;">&nbsp;</th>									
								</tr>
								<?php foreach($existing_des_proj as $item) { ?>
								<tr style="height:35px;">
									<td style="padding-left:10px;"><?=@$item->sfow_name_he?></td>
									<td style="padding-left:10px;"><?=@$item->name_he?></td>
									<td style="text-align:center;"><?=@$item->phone?></td>
									<td style="text-align:center;"><?=@$item->email_office?></td>
									<td style="text-align:center;"><img src="images/delete.svg" style="cursor:pointer;" title="מחיקה" onclick="return removeSupplierFromProject(<?=@$item->id?>);" /></td>	
								</tr>
								<?php } ?>
							</table>
						</div>
					</div>
					<div class="row" style="margin-top:25px;text-align:center;font-size:22px;">	
						<div class="col-md-12">
							<a href="sup_to_proj_report.php?sup_type=D&project_id=<?=@$id?>" target="_blank"><input type="button" value="יצירת PDF" class="btn" /></a>						
						</div>
					</div>
				<?php } ?>
				<div class="row" style="margin-top:20px;direction:rtl;">
					<div align="center" class="col-md-12">
						<input type="button" id="save_des_btn" name="save_btn" class="btn mb-2" value="הוסף מתכננן חדש" />						
						&nbsp;
						<select id="designers_domain" class="form-control" style="width:240px;">
							<option value="0">-- בחור תחום מתכנן --</option>
							<?php 
								foreach($des_field_of_work as $item) {
									$query = $mysqli->prepare("SELECT COUNT(id) AS num_sups FROM dne_suppliers WHERE id_field_of_work = ? AND type = ?");
									$query->bind_param("is",$item->id,$des_type);
									$query->execute();
									$query->store_result();
									$query = fetch_unique($query);
									$num_sups = $query->num_sups;
									
									
									$query = $mysqli->prepare("SELECT COUNT(ps.id_supplier) AS num_sups_proj
															  FROM dne_projects_suppliers ps
															  LEFT JOIN dne_projects p ON ps.id_project = p.id
															  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
															  WHERE p.id = ? AND s.id_field_of_work = ? AND s.type = ?");
									$query->bind_param("iis",$id,$item->id,$des_type);
									$query->execute();
									$query->store_result();
									$query = fetch_unique($query);
									$num_sups_proj = $query->num_sups_proj;
									if($num_sups_proj < $num_sups) {
									?>
										<option value="<?=$item->id?>">
											<?=@$item->fow?>
										</option>
									<?php
									}
								}
							?>										
						</select>
						&nbsp;
						<span id="span_designers_name"></span>
					</div>
				</div>	
				
				<div class="row title-2">	
					<div class="col-md-12">
						רשימת קבלנים וספקים
					</div>					
				</div>					
				
				<?php if($existing_sup_proj_num_rows > 0) { ?>
					<br/>
					
					<div class="row" style="font-size:14px;direction:rtl;">
						<div align="center" class="col-md-12 mx-2">
							<table border="1" cellpadding="5" cellspacing="5">
								<tr style="background-color:silver;">
									<th style="text-align:center;" width="130px;">תחום</th>
									<th style="text-align:center;" width="160px;">שם</th>
									<th style="text-align:center;" width="120px;">טלפון</th>
									<th style="text-align:center;" width="200px;">דוא''ל</th>
									<th width="40px;">&nbsp;</th>								
								</tr>
								<?php foreach($existing_sup_proj as $item) { ?>
								<tr style="height:35px;">
									<td style="padding-left:10px;"><?=@$item->sfow_name_he?></td>
									<td style="padding-left:10px;"><?=@$item->name_he?></td>
									<td style="text-align:center;"><?=@$item->phone?></td>
									<td style="text-align:center;"><?=@$item->email_office?></td>
									<td style="text-align:center;"><img src="images/delete.svg" style="cursor:pointer;" title="מחיקה" onclick="return removeSupplierFromProject(<?=@$item->id?>);" /></td>	
								</tr>
								<?php } ?>
							</table>
						</div>
					</div>
					
					<div class="row" style="margin-top:25px;text-align:center;font-size:22px;">	
						<div class="col-md-12">
							<a href="sup_to_proj_report.php?sup_type=S&project_id=<?=@$id?>" target="_blank"><input type="button" value="יצירת PDF" class="btn" /></a>
						</div>
					</div>
				<?php } ?>			
				<div class="row" style="margin-top:25px;direction:rtl;">
					<div align="center" class="col-md-12">
						<input type="button" id="save_sup_btn" name="save_btn" class="btn mb-2" value="הוסף ספק חדש" />						
						&nbsp;
						<select id="suppliers_domain" class="form-control" style="width:240px;">
							<option value="0">-- בחור תחום ספק--</option>
							<?php 
								foreach($sup_field_of_work as $item) {
									$query = $mysqli->prepare("SELECT COUNT(id) AS num_sups FROM dne_suppliers WHERE id_field_of_work = ? AND type = ?");
									$query->bind_param("is",$item->id,$sup_type);
									$query->execute();
									$query->store_result();
									$query = fetch_unique($query);
									$num_sups = $query->num_sups;
									
									
									$query = $mysqli->prepare("SELECT COUNT(ps.id_supplier) AS num_sups_proj
															  FROM dne_projects_suppliers ps
															  LEFT JOIN dne_projects p ON ps.id_project = p.id
															  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
															  WHERE p.id = ? AND s.id_field_of_work = ? AND s.type = ?");
									$query->bind_param("iis",$id,$item->id,$sup_type);
									$query->execute();
									$query->store_result();
									$query = fetch_unique($query);
									$num_sups_proj = $query->num_sups_proj;
									//if($num_sups_proj < $num_sups) {
									?>
										<option value="<?=$item->id?>">
											<?=@$item->fow?>
										</option>
									<?php
									//}
								}
							?>										
						</select>
						&nbsp;
						<span id="span_suppliers_name"></span>
					</div>
				</div>	
            </div>			
		</form>
    </body>
</html>

<script>
function removeSupplierFromProject(ps_id) {
	if(confirm("Are you sure to remove this supplier?")) {
        var form_data = new FormData();	
		form_data.append('id_projects_suppliers',ps_id);			
		$.ajax({
			type: 'POST',
			url: 'sup_from_proj_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
				location.href = 'add_sup_to_proj.php?id='+$('#id').val();				
			},
		});		
    }
    return false;
}

$('#suppliers_domain').chosen();
$('#designers_domain').chosen();

$('#suppliers_domain').on('change', function (){
	$.post('populate_select_sup_name.php',{id_project:$('#id').val(),sup_type:'S',id_field_of_work:$(this).val()},function(data){
		$('#span_suppliers_name').html(data);
	});
});

$('#designers_domain').on('change', function (){
	$.post('populate_select_sup_name.php',{id_project:$('#id').val(),sup_type:'D',id_field_of_work:$(this).val()},function(data){
		$('#span_designers_name').html(data);
	});
});

$('#save_sup_btn').click (function (e){ 
	var form_data = new FormData();	
	
	form_data.append('id_project',$('#id').val());
	form_data.append('id_supplier',$('#suppliers_name').val());			
	$.ajax({
		type: 'POST',
		url: 'sup_to_proj_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){   	
			location.href = 'add_sup_to_proj.php?id='+$('#id').val();			
		},
	});												       			   
});

$('#save_des_btn').click (function (e){ 
	var form_data = new FormData();	
	form_data.append('id_project',$('#id').val());
	form_data.append('id_supplier',$('#designers_name').val());
	$.ajax({
		type: 'POST',
		url: 'sup_to_proj_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){   	
			location.href = 'add_sup_to_proj.php?id='+$('#id').val();			
		},
	});												       			   
});
</script>

<style>
table,th,td{
	border:1px solid black;
}

a {
	color: inherit;
}

.title {
	font-size: 22px;
	color: #349feb;
	text-align: center;
	margin-top: 10px;
	text-decoration:underline;
}

.title-2 {
	margin-top: 35px;
	text-align: center;
	font-size: 22px;
	direction: rtl;
}

.btn {
	background-color:#218FD6;
	color: white;
}

.btn:hover {
   background-color:#3370d6;
   color: white;
}
</style>