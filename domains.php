<?php
include 'include/header.php';
include 'functions/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$sup_type = 'S';
$des_type = 'D';

$query = $mysqli->prepare("SELECT * FROM dne_sup_field_of_work WHERE sup_type = ? ORDER BY name");
$query->bind_param("s",$sup_type);
$query->execute(); 
$query->store_result();
$sup_fow_num_rows = $query->num_rows;
$sup_domains = fetch($query);

$query = $mysqli->prepare("SELECT * FROM dne_sup_field_of_work WHERE sup_type = ? ORDER BY name");
$query->bind_param("s",$des_type);
$query->execute(); 
$query->store_result();
$des_fow_num_rows = $query->num_rows;
$des_domains = fetch($query);
?>

        <form method="post" action="" class="form-inline">
		
		    <div class="container">
			    <div class="row" style="margin-top:25px;text-align:center;">
					<div class="col-md-12">
						<img src="images/davidnahmias_logo.png" width="170px" height="170px" />
					</div>
			    </div>
			
				<div class="row" style="text-align:center;">
					<div class="col-md-12">
						<input type="button" value="Add new domain" class="btn marginTop20 mb-2" onclick="location.href='add_domain.php?id=0'" />
					</div>
				</div>

				<div class="row">
					<?php if($sup_fow_num_rows > 0) { ?>
						<div class="col-md-6">
							<div class="row title">	
								<div class="col-md-12">
									Suppliers domains List
								</div>					
							</div>
						
							<div class="row" style="font-size:14px;margin-top:20px;">
								<div class="col-md-12 mx-2">
									<table cellpadding="2" cellspacing="2" border="1">
										<tr style="height:35px;background-color:silver;">
											<th width="120px;" style="text-align:center;">Name</th>
											<th width="120px;" style="text-align:center;">Name He</th>
											<th width="120px;" style="text-align:center;">Nickame</th>
											<th width="30px;" style="text-align:center;">Color</th>
											<th width="30px;" style="text-align:center;">BgColor</th>
											<th width="120px;" style="text-align:center;">Related <br/> Suppliers</th>
											<th width="30px;">&nbsp;</th>
											<th width="30px;">&nbsp;</th>
										</tr>										
									
										<?php
										foreach($sup_domains as $item) {
											$id_domain = @$item->id;
											
											$query = $mysqli->prepare("SELECT s.name AS name 
																	  FROM dne_suppliers s
																	  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
																	  WHERE sfow.id = ?");
											$query->bind_param("i",$item->id);
											$query->execute(); 
											$query->store_result();
											$suppliers_num_rows = $query->num_rows;
											$suppliers = fetch($query);
											?>
											<tr>					  		
												<td style="text-align:left;padding-left:5px;"><?=@$item->name?></td>
												<td style="text-align:left;padding-left:5px;"><?=@$item->name_he?></td>
												<td style="text-align:left;padding-left:5px;"><?=@$item->nickname?></td>
												<td style="text-align:left;padding-left:5px;"><input type="color" disabled="true" value="<?=@$item->color?>" /></td>
												<td style="text-align:left;padding-left:5px;"><input type="color" disabled="true" value="<?=@$item->bgcolor?>" /></td>
												<td style="text-align:left;padding-left:5px;">
												   <?php 
												   if($suppliers_num_rows > 0) {
													   foreach($suppliers as $item) {
														  echo @$item->name."<br/>";
														}
												   }
												   ?>
												</td>
												<td style="text-align:center;"><img src="images/edit-button.svg" style="padding:10px;cursor:pointer;" title="Edite" onclick="location.href='add_domain.php?id=<?=@$id_domain?>'" /></td>										
												<td style="text-align:center;"><img src="images/delete.svg" style="cursor:pointer;" title="מחק" onclick="return removeDomain(<?=@$id_domain?>);" /></td>
											</tr>
											<?php
										}
										?>
									</table>	
								</div>						
							</div>	
						</div>
					<?php } 			
					if($des_fow_num_rows > 0) { ?>
						<div class="col-md-6">
							<div class="row title">	
								<div class="col-md-12">
									Designers domains List
								</div>					
							</div>
						
							<div class="row" style="font-size:14px;margin-top:20px;">
								<div class="col-md-12 mx-2">
									<table cellpadding="2" cellspacing="2" border="1">
										<tr style="height:35px;background-color:silver;">
											<th width="120px;" style="text-align:center;">Name</th>
											<th width="120px;" style="text-align:center;">Name He</th>
											<th width="120px;" style="text-align:center;">Nickname</th>
											<th width="30px;" style="text-align:center;">Color</th>
											<th width="30px;" style="text-align:center;">BgColor</th>
											<th width="150px;" style="text-align:center;">Related <br/> Designers</th>
											<th width="30px;">&nbsp;</th>
											<th width="30px;">&nbsp;</th>
										</tr>										
									
										<?php
										foreach($des_domains as $item) {
											$id_domain = @$item->id;
											
											$query = $mysqli->prepare("SELECT s.name AS name 
																	  FROM dne_suppliers s
																	  LEFT JOIN dne_sup_field_of_work sfow ON s.id_field_of_work = sfow.id
																	  WHERE sfow.id = ?");
											$query->bind_param("i",$item->id);
											$query->execute(); 
											$query->store_result();
											$designers_num_rows = $query->num_rows;
											$designers = fetch($query);
											?>
											<tr>					  		
												<td style="text-align:left;padding-left:5px;"><?=@$item->name?></td>
												<td style="text-align:left;padding-left:5px;"><?=@$item->name_he?></td>
												<td style="text-align:left;padding-left:5px;"><?=@$item->nickname?></td>
												<td style="text-align:left;padding-left:5px;"><input type="color" disabled="true" value="<?=@$item->color?>" /></td>
												<td style="text-align:left;padding-left:5px;"><input type="color" disabled="true" value="<?=@$item->bgcolor?>" /></td>
												<td style="text-align:left;padding-left:5px;">
												   <?php 
												   if($designers_num_rows > 0) {
													   foreach($designers as $item) {
														  echo @$item->name."<br/>";
														}
												   }
												   ?>
												</td>
												<td style="text-align:center;"><img src="images/edit-button.svg" style="padding:10px;cursor:pointer;" title="Edite" onclick="location.href='add_domain.php?id=<?=@$id_domain?>'" /></td>		
												<td style="text-align:center;"><img src="images/delete.svg" style="cursor:pointer;" title="מחק" onclick="return removeDomain(<?=@$id_domain?>);" /></td>											
											</tr>
											<?php
										}
										?>
									</table>	
								</div>						
							</div>	
						</div>
					<?php } ?>
				</div>
			</div>
		</form> 
	</body>
</html>

<script>
function removeDomain(id) {
	if(confirm("Are you sure to remove this domain?")) {
        var form_data = new FormData();
		form_data.append('id',id);			
		$.ajax({
			type: 'POST',
			url: 'domain_delete.php',
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
	margin-top: 15px;
	margin-left: 18px;
	font-size: 22px;
	text-align: center;
}

.btn {
	background-color: #218FD6;
	color: white;
	margin-top: 10px;
}

.marginTop20 {
	margin-top: 20px;
}

.btn:hover {
   background-color: #3370d6;
   color: white;
}
</style>