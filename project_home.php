<?php
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$id = @$_GET['id'];

$query = $mysqli->prepare("SELECT * FROM dne_projects WHERE id = ?");
$query->bind_param("i",$id);
$query->execute();
$query->store_result();
$project = fetch_unique($query);
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
					   Project <span style="color:#5bbd8d;"><?=@$project->name?></span>
					</div>
			    </div>
			    <div class="row mt-4">
					<div class="col-xs-6 col-sm-6 col-md-4 my-3">
						<input type="button" class="btn margin-bottom" onclick="location.href='add_sup_to_proj.php?id=<?=@$id?>';" value="Suppliers" />
					</div>
					<div class="col-xs-6 col-sm-6 col-md-4 my-3">
						<input type="button" class="btn margin-bottom" onclick="location.href='orders.php?project_id=<?=@$id?>';" value="Orders" />
					</div>
					<div class="col-xs-6 col-sm-6 col-md-4 my-3">
						<input type="button" class="btn margin-bottom" onclick="location.href='payments.php?project_id=<?=@$id?>';" value="Payments" />
					</div>
					<div class="col-xs-6 col-sm-6 col-md-4 my-3">
						<input type="button" class="btn margin-bottom" onclick="location.href='budget.php?project_id=<?=@$id?>';" value="Budget Report" />
					</div>
					<div class="col-xs-6 col-sm-6 col-md-4 my-3">
						<input type="button" class="btn margin-bottom" onclick="location.href='payments_wires.php?project_id=<?=@$id?>';" value="Payments Wires" />
					</div>
					<div class="col-xs-6 col-sm-6 col-md-4 my-3">
						<input type="button" class="btn margin-bottom" onclick="location.href='budget_costs_eval.php?project_id=<?=@$id?>';" value="Budget Costs Eval" />
					</div>
					<div class="col-xs-6 col-sm-6 col-md-4 my-3">
						<input type="button" class="btn margin-bottom" onclick="location.href='meetings.php?project_id=<?=@$id?>';" value="Tasks Report" />
					</div>
			    </div>
			</div>
		</form>
	</body>
</html>

<style>
.title {
	font-size: 22px;
	color: #349feb;
	text-align: center;
	margin-top: 20px;
}

.btn {
	font-size: 18px;
	border-radius: 20px;
	background-color: #34bdeb;
	padding: 15px 25px;
	color: white;
	width: 200px;
}

.btn:hover {
	background-color:#349feb;
	color: white;
}

.margin-bottom {
	margin-bottom: 20px;
}
</style>