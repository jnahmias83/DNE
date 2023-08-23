<?php
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

$query = $mysqli->prepare("SELECT max(id_display) AS max_id,min(id_display) AS min_id FROM dne_chapters WHERE id_project = ?");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$chapter = fetch_unique($query);

$query = $mysqli->prepare("SELECT * FROM dne_chapters WHERE id_project = ? ORDER BY id_display");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$chapters_num_rows = $query->num_rows;
$chapters = fetch($query);
?>

        <form method="post" action="" class="form-inline">	
		    <input type="hidden" id="id_project" name="id_project" value="<?=@$project_id?>" />
			
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
							<?=@$project->name.'<br/> רשימת פרקים<br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4)?>
						</a>
					</div>					
				</div>
				<div class="row" style="text-align:center;">
					<div class="col-md-12">
						<input type="button" value="הוסף פרק חדש" class="btn marginTop20 mb-2" onclick="location.href='add_chapter.php?id=0&project_id=<?=@$project_id?>';" />
					</div>
				</div>
				
				<br/>

				<?php if($chapters_num_rows > 0) { ?>		
					<div class="row" style="font-size:14px;text-align:center;">
						<div align="center" class="col-md-12 mx-2">
							<table id="chapters_list" border="1" dir="rtl">					
								<tr style="background-color:silver;height:50px;">
									<th width="50px;" style="text-align:center;">&nbsp;</th>
									<th width="150px;" style="text-align:center;">שם</th>
									<th width="40px;" style="text-align:center;">&nbsp;</th>
									<th width="40px;" style="text-align:center;">&nbsp;</th>
								</tr>
			
								<?php
								foreach($chapters as $item) {
									?>
									<tr style="height:35px;">
										<td style="text-align:center;"> 
											<?php if($item->id_display === $chapter->min_id) { ?>
												<a onclick="mooveRecord(<?=@$item->id?>,<?=@$item->id_display?>,'down');">&darr;</a>
											<?php } else if($item->id_display === $chapter->max_id) { ?>
												<a onclick="mooveRecord(<?=@$item->id?>,<?=@$item->id_display?>,'up');">&uarr;</a>
											<?php } else { ?>
											   <a onclick="mooveRecord(<?=@$item->id?>,<?=@$item->id_display?>,'down');">&darr;</a> 
											   &nbsp;&nbsp;  
											   <a onclick="mooveRecord(<?=@$item->id?>,<?=@$item->id_display?>,'up');">&uarr;</a>
											<?php } ?>
										</td>
										<td style="text-align:right;padding-right:5px;"><?=@$item->name?></td>
										<td style="text-align:center;"><img src="images/edit-button.svg" style="cursor:pointer;" title="עדכן" onclick="location.href='add_chapter.php?id=<?=@$item->id?>&project_id=<?=@$project_id?>'" /></td>									
										<td style="text-align:center;"><img src="images/delete.svg" style="cursor:pointer;" title="מחק" onclick="return removeChapter(<?=@$item->id?>);" /></td>	
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
function mooveRecord(id,id_display,direction) {
	var form_data = new FormData();	
	form_data.append('id',id);
	form_data.append('id_display',id_display);	
	form_data.append('id_project',$('#id_project').val());
	form_data.append('direction',direction);
	$.ajax({
		type: 'POST',
		url: 'moove_chapter.php',
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,			
		success: function(data){
			location.reload(true);				
		},
	});
}
function removeChapter(id) {
	if(confirm("האם אתה בטוח למחוק את הפרק הזה ?")) {
        var form_data = new FormData();	
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'chapter_delete.php',
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