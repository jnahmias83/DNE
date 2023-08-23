<?php
include 'include/header.php';
include 'functions/functions.php';

$is_inactive_project = @$_GET['is_inactive_project'];
if($is_inactive_project == 1) {
	$is_active_project = 0;
	$title = "Inactive Projects List";
}
   
if($is_inactive_project == '') {
   $is_active_project = 1;
   $title = "Projects List";
}
   
$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE is_project_appears = ?");
$query->bind_param('i',$is_active_project);	
$query->execute(); 
$query->store_result();
$projects = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_vat");
$query->execute(); 
$query->store_result();
$vat = fetch_unique($query);

if(isset($_POST['save_vat_btn'])) {
	$query = "UPDATE dne_vat SET vat = ?";
	$query = $mysqli->prepare($query);
	$query->bind_param('d',$_POST['vat']);	
	$query->execute();
	
	header('Location:projects.php');
}
?>
        <form method="post" action="" class="form-inline">	
			<div class="row" style="margin-top:25px;text-align:center;">
			    <div class="col-md-12">
				    <img src="images/davidnahmias_logo.png" width="170px" height="170px" />
				</div>
			</div>
			
			<div class="container">
				<div class="row title">	
					<div class="col-md-12">
						<?=@$title?>
					</div>					
				</div>
			
				<?php 
				if($is_active_project == 1) { ?>
				
				<div class="row" style="margin-top:25px;text-align:center;">
					<div class="col-md-12">
						<input type="button" value="Add a project" class="btn mb-2" onclick="location.href='add_project.php?id=0';" />
					</div>
				</div>	
	 
				<br/>
				<?php } ?>
				
				<?php 
				if($is_active_project == 1) {
					?>
					<div class="row" style="text-align:center;">
						<div class="col-md-12">
						   <input type="button" value="פרוייקטים רדומים" class="btn btn-primary" style="font-size:20px;border-radius:15px;" onclick="location.href='projects.php?is_inactive_project=1'" />   
						</div>
					</div>
					<br/>
					<?php 
				}
				else echo '<br/>';
				?>
					
					<div class="row">
						<?php foreach($projects as $item) { ?>
						<div align="center" class="col-md-2">
							<img src="images/edit-button.svg" style="padding-bottom:10px;" title="Edite" onclick="location.href='add_project.php?id=<?=@$item->id?>';" />
							<br/>	
							<input type="button" value="<?=@$item->nickname?>" class="btn padding-btn margin-bottom" onclick="location.href='project_home.php?id=<?=@$item->id?>'" />&nbsp;						
						</div>
						<?php } ?>
					</div>
				
				<br/><br/>
				
				<div class="row">
				   <div class="col-md-12" style="margin-bottom:10px;text-align:center;">
					  <button type="button" class="btn" onclick="location.href='global_settings.php'"><i title="Settings" class="fa fa-cogs" style="font-size:25px;"></i></button>
					  <strong>VAT:</strong>&nbsp;<input type="text" style="width:90px;" id="vat" name="vat" value="<?=@$vat->vat?>" />&nbsp;%
					  &nbsp;
					  <input type="submit" value="Save" class="btn btn-primary" name="save_vat_btn" style="font-size:20px;border-radius:15px;" />
				  </div>
			   </div>
			</div>
		</form> 
	</body>
</html>

<script>
function logout(){
	$.post("kill_session.php",function(data){
		location.href = 'login.php';
	});
}
</script>

<style>
.title {
	margin-top: 25px;
	text-align: center;
	font-size: 22px;
	color: #349feb;
}

.btn {
	font-size: 18px;
	border-radius: 20px;
	background-color: #34bdeb;
	padding: 10px 15px;
	color: white;
}

.btn:hover {
	background-color:#349feb;
	color: white;
}

.margin-bottom {
	margin-bottom: 20px;
}

.padding-btn {
	padding: 15px 25px;
}
</style>