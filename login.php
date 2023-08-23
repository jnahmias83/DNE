<?php
include 'include/header.php';
include 'functions/functions.php';

foreach($query as $item) {
   $lang_str[$item->code] = $item->fr;
}

if(isset($_POST['login_btn'])) {
	$query = $mysqli->prepare("SELECT * FROM dne_users WHERE username = ? and password = ?");
    $query->bind_param("ss",$_POST['username'],$_POST['password']);
    $query->execute();
	$query->store_result();
	
	if($query->num_rows>0) {
		$query = fetch_unique($query);
		
		session_start();
		$_SESSION['id_user'] = $query->id;

		header("Location:projects.php");
	}
	else {
		$msg_alert = "Invalid username or password!<br/><br/>";
	}	
}
?>

        <form method="post" action="" class="form" onsubmit="return validPassword();">   				    			
			<div class="row" style="margin-top:25px;text-align:center;">
			    <div class="col-md-12">
				    <img src="images/davidnahmias_logo.png" width="170" height="170" />
				</div>
			</div>
			
			<div class="container mt-5">
				<div class="form-floating mb-3">
				  <input type="text" class="form-control" id="username" name="username" placeholder="Username" />
				  <label for="username">Username</label>
				</div>
				
				<div class="form-floating mb-3">
				  <input type="password" class="form-control" id="password" name="password" placeholder="Password" />
				  <label for="password">Password</label>
				</div>
				
				<div class="row" id="div_alert" style="padding-left:41%;font-size:20px;color:red;">
					 <?=@$msg_alert?>
				</div>			
				
				<div class="row" style="text-align:center;font-size:20px;">
					<div class="col-md-12">
						<button type="submit" class="btn btn-block mb-4" id="login_btn" name="login_btn">Login</button>
					</div>
				</div>
			</div>
		</form>
	</body>	
</html>

<script>
function validPassword(){
  var upperCase = new RegExp('[A-Z]');
  var lowerCase = new RegExp('[a-z]');
  var digit = new RegExp('[0-9]');

  if($('#password').val().match(upperCase) && $('#password').val().match(lowerCase) && $('#password').val().match(digit) && $('#password').val().length>=8)  
  {
       return true;
  }
  else
  {
       alert('Your password must contain at least one uppercase letter, one lowercase letter, one number and eight characters.');
	   return false;
  }
}
</script>

<style>
.btn {
	background-color:#218FD6;
    color: white;
}

.btn:hover {
   background-color:#3370d6;
   color: white;
}
</style>