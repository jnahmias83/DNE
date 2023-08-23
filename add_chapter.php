<?php
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$id = @$_GET['id'];
$project_id = @$_GET['project_id'];

$query = $mysqli->prepare("SELECT * FROM dne_chapters WHERE id = ? AND id_project = ?");
$query->bind_param("ii",$id,$project_id);
$query->execute();
$query->store_result();
$chapter = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$project_id );
$query->execute();
$query->store_result();
$project = fetch_unique($query);   
?>

		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" />
			<input type="hidden" id="project_id" value="<?=@$project_id?>" />	
			
			<br/>
			
			<div class="row" style="margin-top:25px;text-align:center;">
			    <div class="col-md-12">
				    <img src="images/davidnahmias_logo.png" width="170px" height="170px" />
				</div>
			</div>

			<div class="container">
			    <?php
				if($id == 0) { ?>
					<div class="row title">
						<div class="col-md-12" style="font-size:20px;">
							הוסף פרק לפרוייקט <span style="color:#5bbd8d;"><?=@$project->name?></span>
						</div>
					</div>
				<?php }
				else if($id > 0) { ?>
				   <div class="row title">
						<div class="col-md-12">
							<span style="color:#5bbd8d;"><?=@$chapter->name?></span>
						</div>
					</div>
				<?php } ?>			
				
				<div class="row" style="margin-top:20px;direction:rtl;">
					<div class="col-md-11">	
						<strong>שם:</strong>
						<br/>	
						<input type="text" class="form-control" style="width:250px;" name="name" id="name" placeholder="*שם" value="<?=@$chapter->name?>" />	
					</div>
				</div>
						
				<div class="row" style="margin-top:10px;direction:rtl;">
					<div class="col-md-12">
						<div id="div_message_alert_down"></div>	
						<input type="button" id="save_btn" name="save_btn" class="btn marginTop5 bgColorBlue colorWhite mb-2" value="שמור" />		
						<input type="button" id="cancel_btn" class="btn marginRight10 marginTop5 bgColorBlack colorWhite mb-2" value="ביטול" />				
					</div>
				</div>				
            </div>			
		</form>       
    </body>
</html>

<script>
$('#save_btn').click (function (e){ 
	var form_data = new FormData();	
	form_data.append('id',$('#id').val());
	form_data.append('id_project',$('#project_id').val());
	form_data.append('name',$('#name').val());
	$.ajax({
		type: 'POST',
		url: 'chapter_insert.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){ 
			if(data == 'empty')	{		
				if($('#name').val().length == 0)			
					$('#name').css('border-color','red');
				else if(!($('#name').val().length == 0))
					$('#name').css('border-color','initial');	
				
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;>נא למלה את כל השדות החובות</span>");
			}
			else
				location.href = 'chapters.php?project_id='+$('#project_id').val();		
		},
	});												       			   
})

$('#cancel_btn').click(function(){
    location.href = "chapters.php?project_id="+$('#project_id').val();	
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

.marginRight10 {
	margin-right: 10px;
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