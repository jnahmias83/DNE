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

$query = $mysqli->prepare("SELECT * FROM dne_custom_reports WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$custom_reports_num_rows = $query->num_rows;
$custom_reports = fetch($query);
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
			    <div class="row title">	
					<div class="col-md-12">
						<a style="text-decoration:underline;" href="project_home.php?id=<?=@$project_id?>">
							<?=@$project->name.'<br/>רשימת דוחות מיוחדים<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4)?>
						</a>
					</div>					
			    </div>
				<div class="row" style="text-align:center;">
					<div class="col-md-12">
						<input type="button" value="יצירת דו''ח חדש" class="btn marginTop20 mb-2" onclick="location.href='add_custom_report.php?id=0&project_id=<?=@$project_id?>';" />
					</div>
				</div>
				
				<br/>

				<?php if($custom_reports_num_rows > 0) { ?>		
					<div class="row" style="font-size:14px;text-align:center;">
						<div align="center" class="col-md-12 mx-2">
							<table id="responsibles_list" border="1" dir="rtl">						
								<tr style="background-color:silver;height:50px;">
									<th width="130px;" style="text-align:center;">שם</th>
									<th width="40px;" style="text-align:center;">&nbsp;</th>
								</tr>
			
								<?php
								$count = 0;
								foreach($custom_reports as $item) { 
								?>
									<tr style="height:35px;">
										<td style="text-align:right;padding-right:5px;"><a href="results_custom_report.php?id=<?=@$item->id?>" target="_blank"><?=@$item->request_name?></a></td>				
										<td style="text-align:center;"><img src="images/delete.svg" style="cursor:pointer;" title="מחק" onclick="return removeCustomReport(<?=@$item->id?>);" /></td>	
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
function removeCustomReport(id) {
	if(confirm("האם אתה בטוח למחוק את הדו\'\'ח המיוחד הזה ?")) {
        var form_data = new FormData();	
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'custom_report_delete.php',
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