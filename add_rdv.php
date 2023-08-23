<?php
include 'include/header.php';
include 'functions/functions.php';

$project_id = @$_GET['project_id'];
$id = @$_GET['id'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$project = fetch_unique($query); 

$query = $mysqli->prepare("SELECT * FROM dne_responsibles WHERE id_project = ? ORDER BY name ASC");
$query->bind_param("i",$project_id);
$query->execute();
$query->store_result();
$responsibles = fetch($query);

$rdv_date = date('Y-m-d');

if($id > 0) {
   $query = $mysqli->prepare("SELECT * FROM dne_rdv WHERE id = ?");
   $query->bind_param("i",$id);
   $query->execute();
   $query->store_result();
   $rdv = fetch_unique($query);
   
   if($rdv->rdv_date != '0000-00-00') {
	   $rdv_date = $rdv->rdv_date;
   }
}

$rdv_persons_array = explode(",",@$rdv->rdv_persons);
?>

		<form method="post" action="" class="form-inline">
		    <input type="hidden" id="id" value="<?=@$id?>" />
			<input type="hidden" id="project_id" value="<?=@$project_id?>" />	
			
			<div class="row my-3" style="text-align:center;">
				<div class="col-md-12">
					<img src="images/davidnahmias_logo.png" width="170px" height="170px" />
				</div>
			</div>

            <div class="container">
			    <?php if($id == 0) { ?>
				<div class="row title">
					<div class="col-md-12">
						הוספת ישיבה לפרוייקט <span style="color:#5bbd8d;"><?=@$project->name?></span>
					</div>
				</div>
				<?php }
					else {
						?>
							<div class="row title">
							  <div class="col-md-12">
							  עדכון ישיבת <span style="color:#5bbd8d;"><?=@$rdv->rdv_name?></span>
							  </div>
							</div>
						<?php
					}
				?>

				<div class="row m-2" style="direction:rtl;">
					<div class="col-md-5">
						<strong>תאריך:</strong>	
						<input type="date" class="form-control my-2" name="rdv_date" id="rdv_date" value="<?=@$rdv_date?>" />
					</div>
					<div class="col-md-5">	
						<strong>נושא:</strong>				
						<input type="text" class="form-control my-2 px-2" style="width:400px;" name="rdv_name" id="rdv_name" placeholder="נושא" value="<?=@$rdv->rdv_name?>" />
					</div>
				</div>

				<div class="row m-2" style="font-size:14px;direction:rtl;">
					<div class="col-md-12">
						<strong>משתתפים:</strong>
					</div>
				</div>	

				<div class="row mx-2" style="direction:rtl;">
					<div class="col-md-12">		
						<?php 
						foreach($responsibles as $item) {
						?>
							<input type="checkbox" id="rdv_persons" value="<?=@$item->id?>" <?php if(in_array($item->id,$rdv_persons_array)) echo 'checked' ?> />
							&nbsp;<?=$item->name?>
							<br/>
							<?php
						}
						?>						
					</div>
				</div>

				<div class="row m-2" style="direction:rtl;">
					<div class="col-md-12">
						<div id="div_message_alert_down" style="margin-top:10px;"></div>
						<input type="button" id="save_btn" name="save_btn" class="btn marginTop5 bgColorBlue colorWhite mb-2" value="שמור" />					
						<input type="button" id="cancel_btn" class="btn marginTop5 bgColorBlack marginRight8 colorWhite mb-2" value="ביטול" />						
					</div>
				</div>
			</div>
		</form>
	</body>
</html>

<script>
$('#save_btn').click (function (e){ 
    var rdv_persons = '';
	$('#rdv_persons:checked').each(function(i){
	  rdv_persons+= $(this).val()+',';
	 });
	rdv_persons = rdv_persons.substring(0,rdv_persons.length - 1);
	
    var form_data = new FormData();	
	form_data.append('id',$('#id').val());
	form_data.append('id_project',$('#project_id').val());
	form_data.append('rdv_name',$('#rdv_name').val());
	form_data.append('rdv_persons',rdv_persons);
	form_data.append('rdv_date',$('#rdv_date').val());
	
	$.ajax({
		type: 'POST',
		url: 'rdv_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){  
			if(data == 'empty')	{
				if($('#rdv_name').val().length == 0)			
					$('#rdv_name').css('border-color','red');
				else if(!($('#rdv_name').val().length == 0))
					$('#rdv_name').css('border-color','initial');
				
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;>Please fill all the mandatory fields</span>"); 
			}
			else 
			   location.href = 'resume_rdv.php?project_id='+$('#project_id').val();	
		},
	});									       			   
});

$('#cancel_btn').click(function(){
    location.href = "resume_rdv.php?project_id="+$('#project_id').val();	
})
</script>

<style>
.title {
	font-size: 22px;
	color: #349feb;
	margin-top: 20px;
	direction: rtl;
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