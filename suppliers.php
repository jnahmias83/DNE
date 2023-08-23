<?php
session_start();
include 'include/header.php';
include 'functions/functions.php';

$type_sup = @$_GET['type'];
if($type_sup == 'S') {
	$type = 'S';
	$title_add_btn = "Add new supplier";

	$query = $mysqli->prepare("SELECT * FROM dne_suppliers WHERE type = ? ORDER BY name");
	$query->bind_param("s",$type);
	$query->execute(); 
	$query->store_result();
	$suppliers = fetch($query);
}
else if($type_sup == 'D') {
	$type = 'D';
    $title_add_btn = "Add new designer";
	
    $query = $mysqli->prepare("SELECT * FROM dne_suppliers WHERE type = ? ORDER BY name");
    $query->bind_param("s",$type);
    $query->execute(); 
    $query->store_result();
    $designers = fetch($query);
}
?>

        <form method="post" action="" class="form-inline">
		    <input type="hidden" id="type" value="<?=@$type?>" />
			
            <div class="container">
			  <div class="row" style="margin-top:25px;text-align:center;">
			    <div class="col-md-12">
				    <img src="images/davidnahmias_logo.png" width="170px" height="170pxhttp://davidnahmiasengineering.com/domains.php" />
				</div>
			</div>
			
			<div class="row">
			    <div class="col-md-12">
				    <input type="button" value="<?=@$title_add_btn?>" class="btn mb-2" onclick="location.href='add_supplier.php?type_sup=<?=@$type_sup?>&id=0';" />
				</div>
			</div>		
			
			<?php if($_GET['type'] == 'S') { ?>
			    <div class="row title">
					<div class="col-md-12">
						Suppliers List
					</div>					
			    </div>
				
				<div class="row" style="font-size:18px;margin-top:5px;">
					<div class="col-md-12 mx-2">
						<div class="table-responsive" style="overflow-x:scroll;">
							<table class="table" id="suppliers_list" border="1">
								<thead>
									<tr>
										<th>Name</th>
										<th>Name He</th>
										<th>Nickname</th>
										<th>Domaine</th>
										<th>Phone</th>
										<th>Cellular</th>
										<th>Email office</th>						
										<th width="30px;">&nbsp;</th>
										<th width="30px;">&nbsp;</th>
									</tr>										
								</thead>
								
								<tfoot>
									<tr>
										<th>Name</th>
										<th>&nbsp;</th>
                                        <th>&nbsp;</th>
										<th>&nbsp;</th>
                                        <th>&nbsp;</th>
										<th>&nbsp;</th>
										<th>&nbsp;</th>							
										<th width="30px;">&nbsp;</th>
										<th width="30px;">&nbsp;</th>
									</tr>										
								</tfoot>
							
								<?php
								foreach($suppliers as $item) {
									$query = $mysqli->prepare("SELECT * FROM dne_sup_field_of_work WHERE id = ?");
									$query->bind_param("i",$item->id_field_of_work);
									$query->execute();
									$query->store_result();
									$sup_field_of_work = fetch_unique($query);
									?>
									<tr>					  		
										<td><?=@$item->name?></td>
										<td><?=@$item->name_he?></td>
										<td><?=@$item->nickname?></td>
										<td><?=$sup_field_of_work->name?></td>
										<td><?=@$item->phone?></td>
										<td><?=@$item->mobile?></td>
										<td><?=@$item->email_office?></td>
										<td><img src="images/edit-button.svg" style="padding-top:10px;cursor:pointer;" title="Edite" onclick="location.href='add_supplier.php?type_sup=S&id=<?=@$item->id?>'" /></td>
                                        <td><img src="images/delete.svg" style="padding-top:10px;cursor:pointer;" title="Remove" onclick="return removeSupplier(<?=@$item->id?>);" /></td>										
									</tr>
									<?php
								}
								?>
							</table>		
						</div>	
					</div>
				</div>	
			<?php } 
			else if($_GET['type'] == 'D') { ?>		
				<div class="row title">	
					<div class="col-md-12">
						Designers List
					</div>					
				</div>
				
				<div class="row" style="font-size:18px;margin-top:15px;">
					<div class="col-md-12 mx-2">
						<div class="table-responsive">
							<table class="table" id="designers_list" border="1">
								<thead>
									<tr>
										<th>Name</th>
										<th>Name He</th>
										<th>Nickname</th>
										<th>Domaine</th>
										<th>Phone</th>
										<th>Cellular</th>
										<th>Email office</th>				
										<th width="30px;">&nbsp;</th>
										<th width="30px;">&nbsp;</th>
									</tr>										
								</thead>
								
								<tfoot>
									<tr>
										<th>Name</th>
										<th>&nbsp;</th>
                                        <th>&nbsp;</th>
										<th>&nbsp;</th>
                                        <th>&nbsp;</th>
										<th>&nbsp;</th>
										<th>&nbsp;</th>									
										<th width="30px;">&nbsp;</th>
										<th width="30px;">&nbsp;</th>
									</tr>										
								</tfoot>
						
								<?php
								foreach($designers as $item) {
									$query = $mysqli->prepare("SELECT * FROM dne_sup_field_of_work WHERE id = ?");
									$query->bind_param("i",$item->id_field_of_work);
									$query->execute();
									$query->store_result();
									$sup_field_of_work = fetch_unique($query);
									?>
									<tr>					  		
										<td><?=@$item->name?></td>
										<td><?=@$item->name_he?></td>
										<td><?=@$item->nickname?></td>
										<td><?=@$sup_field_of_work->name?></td>
										<td><?=@$item->phone?></td>
										<td><?=@$item->mobile?></td>
										<td><?=@$item->email_office?></td>
										<td><img src="images/edit-button.svg" style="padding-top:10px;cursor:pointer;" title="Edite" onclick="location.href='add_supplier.php?type_sup=D&id=<?=@$item->id?>'" /></td>
                                        <td><img src="images/delete.svg" style="padding-top:10px;cursor:pointer;" title="Remove" onclick="return removeSupplier(<?=@$item->id?>);" /></td>										
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
		</form> 
	</body>
</html>

<script>
$(document).ready(function () {
	$('#suppliers_list').dataTable( {
        "aLengthMenu": [25, 50]
    });
	
	jQuery('#suppliers_list').dataTable().columnFilter({
		aoColumns: [ 
		   {type: "text"},		
			null,
			null,
			null,
			null,
			null,
			null,
			null,
			null
		]
	});
	
	$('#designers_list').dataTable( {
        "aLengthMenu": [25, 50]
    });
	
	jQuery('#designers_list').dataTable().columnFilter({
		aoColumns: [ 
		    {type: "text"},		
			null,
			null,
			null,
			null,
			null,
			null,
			null,
			null
		]
	});
});

function removeSupplier(s_id) {
	if(confirm("Are you sure to remove this supplier?")) {
        var form_data = new FormData();	
		form_data.append('id_supplier',s_id);			
		$.ajax({
			type: 'POST',
			url: 'supplier_delete.php',
			data: form_data,
			cache: false,
			processData: false,
			contentType: false,			
			success: function(data){
				location.href = 'suppliers.php?type='+$('#type').val();			
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
	font-size: 22px;
}

.btn {
	background-color: #218FD6;
	color: white;
	margin-top: 10px;
}

.btn:hover {
   background-color: #3370d6;
   color: white;
}
</style>