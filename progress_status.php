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

$query = $mysqli->prepare("SELECT * FROM dne_progress_status WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$progress_status_num_rows = $query->num_rows;
$progress_status = fetch($query);
?>

        <form method="post" action="" class="form-inline">	
		    <input type="hidden" id="project_id" value=<?=@$project_id?> />
			
			<div class="row" style="margin-top:25px;text-align:center;">
			    <div class="col-md-12">
				    <img src="images/davidnahmias_logo.png" width="170px" height="170px" />
				</div>
			</div>
			
			<br/>
			
			<div class="container">
			    <div class="row" style="text-align:center;">
					<div class="col-md-12">
						<input type="button" value="חזור לרשימת המשימות" class="btn marginTop20 mb-2" onclick="location.href='meetings.php?&project_id=<?=@$project_id?>';" />
					</div>
			    </div>
				<div class="row title">	
					<div class="col-md-12">
						<a style="text-decoration:underline;" href="project_home.php?id=<?=@$project_id?>">
							<?=@$project->name.'<br/> רשימת סטסטוסי התקדמות<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4)?>
						</a>
					</div>					
				</div>
				<div class="row" style="text-align:center;">
					<div class="col-md-12">
						<input type="button" value="הוסף סטטוס התקדמות חדש" class="btn marginTop20 mb-2" onclick="location.href='add_progress_status.php?id=0&project_id=<?=@$project_id?>';" />
						<input type="button" value="Default" class="btn marginTop20 mb-2" onclick="FillGlobalProgressStatus();" />
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
										<td style="text-align:center;"><img src="images/edit-button.svg" style="cursor:pointer;" title="עדכן" onclick="location.href='add_progress_status.php?id=<?=@$item->id?>&project_id=<?=@$project_id?>'" /></td>									
										<?php if(!(@$item->name == ' ' || @$item->name == 'בביצוע' || @$item->name == 'איחור' || @$item->name == 'בוצע/נמסר' || @$item->name == 'Hold' || @$item->name == 'ארכיון' || @$item->name == 'הנחיה/החלטה')) { ?>
										   <td style="text-align:center;">
											  <img src="images/delete.svg" style="cursor:pointer;" title="מחק" onclick="return removeProgressStatus(<?=@$item->id?>);" />	
										   </td>
									   <?php } else '&nbsp;' ?>
									</tr>
									<?php
								}
								?>
							</table>		
						</div>
					</div>
				<?php } ?>
			</div>
		</form> 
	</body>
</html>

<script>
function FillGlobalProgressStatus() {
	var form_data = new FormData();	
	form_data.append('id_project',$('#project_id').val());			
	$.ajax({
		type: 'POST',
		url: 'fill_global_progress_status.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){
			location.reload(true);			
		},
	});		
}

function removeProgressStatus(id) {
	if(confirm("האם אתה בטוח למחוק את הסטטוס ההתקדמות הזה ?")) {
        var form_data = new FormData();	
		form_data.append('global',0);
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