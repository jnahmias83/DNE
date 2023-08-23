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

$query = $mysqli->prepare("SELECT bce.id AS id,bce.name AS name,bce.description AS description,bce.pdf_evaluation AS pdf_evaluation,bce.evaluation_cost AS evaluation_cost,
                          bce.evaluation_date AS evaluation_date,sfow.name AS sfow_name
                          FROM dne_budget_costs_eval bce
						  LEFT JOIN dne_sup_field_of_work sfow ON bce.id_field_of_work = sfow.id
						  WHERE id_project = ?
						  ORDER BY bce.name DESC");
$query->bind_param("i",$project_id);
$query->execute(); 
$query->store_result();
$budget_costs_eval_num_rows = $query->num_rows;
$budget_costs_eval = fetch($query);
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
						<a style="text-decoration:underline;" href="project_home.php?id=<?=@$project_id?>">
							<?=@$project->name.'<br/> Budget Evaluation Costs <br/>'.substr(date('Y-m-d'),8,2).'/'.substr(date('Y-m-d'),5,2).'/'.substr(date('Y-m-d'),0,4)?>
						</a>
					</div>					
			    </div>
			<div class="row" style="text-align:center;">
				<div class="col-md-12">
					<input type="button" value="Add new cost evaluation" class="btn marginTop20 mb-2" onclick="location.href='add_cost_eval.php?id=0&project_id=<?=@$project_id?>';" />
				</div>
			</div>
			
			<br/>

			<?php if($budget_costs_eval_num_rows > 0) { ?>		
				<div class="row" style="font-size:14px;text-align:center;">
					<div align="center" class="col-md-12 mx-2">
						<table border="1">						
							<tr style="background-color:silver;height:50px;">
								<th width="30px;" style="text-align:center;">&#x2116;</th>
								<th width="180px;" style="text-align:center;">Name</th>
								<th width="120px;" style="text-align:center;">Domain</th>
								<th width="250px;" style="text-align:center;">Description</th>
								<th width="120px;" style="text-align:center;">Evaluation Cost</th>
								<th width="90px;" style="text-align:center;">Evaluation <br/> Date</th>
								<th width="40px;" style="text-align:center;">&nbsp;</th>
								<th width="40px;" style="text-align:center;">&nbsp;</th>
							</tr>
		
							<?php
							$count = 0;
							foreach($budget_costs_eval as $item) {
								$count++;
								
								$evaluation_date = '';
								if(@$item->evaluation_date != '0000-00-00')
									$evaluation_date = substr(@$item->evaluation_date,8,2).'/'.substr(@$item->evaluation_date,5,2).'/'.substr(@$item->evaluation_date,2,2);
								
								$evaluation_cost_display = '';
								if(@$item->evaluation_cost > 0) {
								   $evaluation_cost_display = round($item->evaluation_cost);
								   $evaluation_cost_display = number_format($evaluation_cost_display,0,'.',',').'&#8362;';
								}
								?>
								<tr height="60px;">
									<td style="text-align:center;"><?=@$count?></td>
									<td style="text-align:left;padding-left:5px;"><?=@$item->name?></td>
									<td style="text-align:left;padding-left:5px;"><?=@$item->sfow_name?></td>
									<td style="text-align:left;padding-left:5px;"><?=@$item->description?></td>											
									<td style="text-align:right;padding-right:5px;""><?php if(@$item->pdf_evaluation != '') { ?><a href="uploads/<?=@$item->pdf_evaluation?>" title="View PDF" target="_blank"><?=@$evaluation_cost_display?></a><?php } else echo @$evaluation_cost_display?></td>
									<td style="text-align:left;padding-left:5px;"><?=@$evaluation_date?></td>
									<td style="text-align:center;"><img src="images/edit-button.svg" style="padding:10px;cursor:pointer;" title="Edite" onclick="location.href='add_cost_eval.php?id=<?=@$item->id?>&project_id=<?=@$project_id?>'" /></td>									
									<td style="text-align:center;"><img src="images/delete.svg" style="cursor:pointer;" title="מחק" onclick="return removeCostEval(<?=@$item->id?>);" /></td>
								</tr>
								<?php
							}
							?>
						</table>		
					</div>
				</div>
				
				<div class="row" style="text-align:center;">
					<div class="col-md-12">
						<a href="budget_costs_eval_report.php?project_id=<?=@$project_id?>" target="_blank">
							<input type="button" value="Budget Costs Evaluation Report" class="btn marginTop20 mb-2" />
						</a>
					</div>
				</div>
			<?php } ?>
			</div>
		</form> 
	</body>
</html>

<script>
function removeCostEval(id) {
	if(confirm("Are you sure to remove this cost eval?")) {
        var form_data = new FormData();	
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'cost_eval_delete.php',
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