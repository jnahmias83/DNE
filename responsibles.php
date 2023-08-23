<?php
session_start();
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$project_id = @$_GET['project_id'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$project = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id_project = ? ORDER BY name");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$responsibles_num_rows = $query->num_rows;
$responsibles = fetch($query);
?>

        <form method="post" action="" class="form-inline">	
		    <input type="hidden" id="project_id" name="project_id" value="<?=@$project->id?>" />
			
			<div class="row" style="margin-top:25px;text-align:center;">
			    <div class="col-md-12">
				    <img src="images/davidnahmias_logo.png" width="170px" height="170px" />
				</div>
			</div>
			
			<br/>
			
			<div class="container">
			    <div class="row" style="text-align:center;">
					<div class="col-md-12">
						<input type="button" value="חזור לרשימת המשימות" class="btn btn-primary mb-2" style="margin-top:20px;" onclick="location.href='meetings.php?&project_id=<?=@$project_id?>';" />
					</div>
			    </div>
				<div class="row title">	
					<div class="col-md-12">
						<a style="text-decoration:underline;" href="project_home.php?id=<?=@$project_id?>">
							<?=@$project->name.'<br/> רשימת אחריים<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4)?>
						</a>
					</div>					
				</div>
				<div class="row" style="text-align:center;">
					<div class="col-md-12">
						<input type="button" value="הוסף אחראי חדש" class="btn marginTop20 mb-2" onclick="location.href='add_responsible.php?id=0&project_id=<?=@$project_id?>';" />
					</div>
				</div>
				
				<br/>

				<?php if($responsibles_num_rows > 0) { ?>		
					<div class="row" style="font-size:14px;text-align:center;">
						<div align="center" class="col-md-12 mx-2">
							<table id="responsibles_list" border="1" dir="rtl">						
								<tr style="background-color:silver;height:50px;">
									<th width="130px;" style="text-align:center;">שם</th>
									<th width="170px;" style="text-align:center;">משרד/חברה</th>
									<th width="90px;" style="text-align:center;">צבע גופן</th>
									<th width="90px;" style="text-align:center;">צבע רקע</th>
									<th width="220px;" style="text-align:center;">דוא''ל</th>
									<th width="40px;" style="text-align:center;">&nbsp;</th>
									<th width="40px;" style="text-align:center;">&nbsp;</th>
								</tr>
			
								<?php
								$count = 0;
								foreach($responsibles as $item) { 
									$query = $mysqli->prepare("SELECT s.name_he AS name_he
															  FROM dne_projects_suppliers ps
															  LEFT JOIN dne_suppliers s ON ps.id_supplier = s.id
															  WHERE ps.id = ?");
									$query->bind_param("i",$item->id_projects_suppliers);
									$query->execute();
									$query->store_result();
									$supplier = fetch_unique($query);
								?>
									<tr style="height:35px;">
										<td style="text-align:right;padding-right:5px;"><?=@$item->name?></td>
										<td style="text-align:right;padding-right:5px;"><?=@$supplier->name_he?></td>
										<td style="text-align:right;padding-right:5px;"><input type="color" style="width:90px;" disabled="true" value="<?=@$item->color?>" /></td>
										<td style="text-align:right;padding-right:5px;"><input type="color" style="width:90px;" disabled="true" value="<?=@$item->bgcolor?>" /></td>
										<td style="text-align:right;padding-right:5px;"><?=@$item->email?></td>
										<td style="text-align:center;"><img src="images/edit-button.svg" style="cursor:pointer;" title="עדכן" onclick="location.href='add_responsible.php?id=<?=@$item->id?>&project_id=<?=@$project_id?>'" /></td>									
										<td style="text-align:center;"><img src="images/delete.svg" style="cursor:pointer;" title="מחק" onclick="return removeResponsible(<?=@$item->id?>);" /></td>	
									</tr>
									<?php
								}
								?>
							</table>		
						</div>
					</div>
					
					<div class="row" style="text-align:center;">
						<div class="col-md-12">
						   <input type="button" value="ברירת מחדל גופנים ורקעים" class="btn marginTop20 mb-2" onclick="setDefaultColorAndBgColor();" />
						</div>
					</div> 
				<?php } ?>
			</div>
		</form> 
	</body>
</html>

<script>
function removeResponsible(id) {
	if(confirm("האם אתה בטוח למחוק את האחראי הזה ?")) {
        var form_data = new FormData();	
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'responsible_delete.php',
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

function setDefaultColorAndBgColor() {
	var form_data = new FormData();	
	form_data.append('project_id',$('#project_id').val());
	$.ajax({
		type: 'POST',
		url: 'setResponsiblesData.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){  
           window.location.reload();
		},
	});												       			   
}
</script>

<style>
.title {
	font-size: 22px;
	color: #349feb;
	margin-top: 20px;
	direction: rtl;
	text-align: center;
}

a {
   color: inherit;
}

.marginTop20 {
  margin-top: 20px;
}

.btn {
   color: white;
   background-color: #218FD6;
}

.btn:hover {
   color: white;
   background-color: #3370d6;
}
</style>