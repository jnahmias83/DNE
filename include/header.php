<?php 
session_start();

if(strpos($_SERVER['REQUEST_URI'],'login') === false &&
   (!isset($_SESSION['id_user']))){	
   header('Location:login.php');
}
?>

<head>
<title>DNE</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<script src="https://code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css" />
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="assets/dataTables/css/jquery.dataTables.css" rel="stylesheet" type="text/css" />
<link href="assets/dataTables/css/jquery.dataTables_themeroller.css" rel="stylesheet" type="text/css" />
<link href="css/custom.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous" />
<link rel="stylesheet" type="text/css" href="css/style.css" />
<script src="assets/dataTables/js/jquery.dataTables.js" type="text/javascript"></script>
<script src="assets/dataTables/js/jquery.dataTables.columnFilter.js" type="text/javascript"></script>
</head>

<html>
    <body>
	    <?php
        if(strpos($_SERVER['REQUEST_URI'],'login') === false && strpos($_SERVER['REQUEST_URI'],'pdf_report_a') === false &&
		   strpos($_SERVER['REQUEST_URI'],'pdf_report_b') === false && strpos($_SERVER['REQUEST_URI'],'pdf_report_c') === false) { ?>
		    <nav class="navbar navbar-expand-md navbar-dark bg-dark">
			  <div class="container-fluid">   
				<button
				  class="navbar-toggler"
				  type="button"
				  data-bs-toggle="collapse"
				  data-bs-target="#navbarSupportedContent"
				  aria-controls="navbarSupportedContent"
				  aria-expanded="false"
				  aria-label="Toggle navigation"
				>
				  <span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarSupportedContent">
				  <ul class="navbar-nav me-auto mb-2 mb-lg-0">
					<li class="nav-item">
					  <a
						class="nav-link <?php if(strpos($_SERVER['REQUEST_URI'],'projects') !== false) echo "active"?>" 
						aria-current="page"
						href="projects.php">PROJECTS</a
					  >
					</li>
					<li class="nav-item">            
					  <a class="nav-link <?php if(strpos($_SERVER['REQUEST_URI'],'S') !== false) echo "active"?>" href="suppliers.php?type=S">SUPPLIERS</a>
					</li>
					<li class="nav-item">            
					  <a class="nav-link <?php if(strpos($_SERVER['REQUEST_URI'],'type=D') !== false) echo "active"?>" href="suppliers.php?type=D">DESIGNERS</a>
					</li>
					<li class="nav-item">            
					  <a class="nav-link <?php if(strpos($_SERVER['REQUEST_URI'],'domains') !== false) echo "active"?>" href="domains.php">DOMAINS</a>
					</li>
					<?php if(strpos($_SERVER['REQUEST_URI'],'projects') !== false) { ?>
						<a href="javascript:void(0);" onclick="logout();">
						   <i class="fa fa-sign-out" style="font-size:40px;" aria-hidden="true"></i>
						</a>
					<?php } ?>
				  </ul>
				</div>
			  </div>
			</nav>
		<?php }	?>
		
		<style>
		.navbar {
            position: sticky;
			top: 0;
			z-index: 4;
        }
		
		.active {
			text-decoration:underline;
		}
		
		.nav-link {
			color:#dedede!important;
			font-weight:bold;
		}
		</style>