<?php
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$id = @$_GET['id'];

$query = $mysqli->prepare("SELECT * FROM dne_global_progress_status WHERE id = ?");
$query->bind_param("i",$id);
$query->execute();

$query->store_result();
$progress_status = fetch_unique($query);
?>
		<form method="post" action="" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" id="id" value="<?=@$id?>" />					
			<br/>
			
			<div class="container">
			    <div class="row" style="margin-top:25px;text-align:center;">
					<div class="col-md-12">
						<img src="images/davidnahmias_logo.png" width="170px" height="170px" />
					</div>
			    </div>

				<?php
				if($id == 0) { ?>
					<div class="row title">
						<div class="col-md-12">
							הוסף סטטוס התקדמות
						</div>
					</div>
				<?php }
				else if($id > 0) { ?>
				   <div class="row" style="margin-top:20px;direction:rtl;">
						<div class="col-md-12" style="font-size:20px;">
							<span style="color:#5bbd8d;"><?=@$progress_status->name?></span>
						</div>
					</div>
				<?php } ?>			
				
				<div class="row" style="margin-top:20px;direction:rtl;">
					<div class="col-md-12">	
						<strong>שם:</strong>
						<br/>	
						<input type="text" class="form-control" style="width:250px;" name="name" id="name" placeholder="*שם" value="<?=@$progress_status->name?>" 
						<?php if(@$id > 0 && (@$progress_status->name == ' ' || @$progress_status->name == 'בביצוע' || @$progress_status->name == 'איחור' || @$progress_status->name == 'בוצע/נמסר' || @$progress_status->name == 'Hold' || @$progress_status->name == 'ארכיון' || @$progress_status->name == 'הנחיה/החלטה')) echo "readonly";?> />	
					</div>
				</div>

				<div class="row" style="margin-top:20px;direction:rtl;">
					<div class="col-md-12">
						<strong>צבע גופן:</strong>
						&nbsp;						
						<input type="color" class="form-control" style="width:150px;" name="color" id="color" placeholder="צבע גופן" value="<?=@$progress_status->color?>" />
					</div>
				</div>

				<div class="row" style="margin-top:20px;direction:rtl;">
					<div class="col-md-12">
						<strong>צבע רקע:</strong>
						&nbsp;						
						<input type="color" class="form-control" style="width:150px;" name="bgcolor" id="bgcolor" placeholder="צבע רקע" value="<?php if($id == 0) echo '#ffffff';else echo @$progress_status->bgcolor;?>" />
					</div>
				</div>				
						
				<div class="row" style="margin-top:10px;direction:rtl;">
					<div class="col-md-12">
						<div id="div_message_alert_down" style="margin-top:10px;"></div>	
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
	form_data.append('id_project',0);
	form_data.append('name',$('#name').val());
	form_data.append('color',$('#color').val());
	form_data.append('bgcolor',$('#bgcolor').val());
	$.ajax({
		type: 'POST',
		url: 'progress_status_insert.php',
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
			else if(data == 'exists') 
				$('#div_message_alert_down').html("<span style=color:red;font-size:13px;>סטטוס התקדמות זה כבר קיים במערכת</span>");
			
			else
				location.href = 'global_settings.php';		
		},
	});												       			   
})

$('#cancel_btn').click(function(){
    location.href = "global_settings.php";	
})
</script>

<style>
.title {
	margin-top: 10px;
	font-size: 22px;
	color: #349feb;
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