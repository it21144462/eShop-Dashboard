<?php include '../classess/Adminlogin.php';?>
<?php

//IT21568732
 // Enable a Content Security Policy (CSP) header
 header("Content-Security-Policy: default-src 'self'; frame-ancestors 'none'");
 // Set X-Content-Type-Options header to 'nosniff'
 header("X-Content-Type-Options: nosniff");

 // Remove or suppress the X-Powered-By header
 header_remove("X-Powered-By");
 header_remove("Server");

 session_start(
	 [
		 'cookie_httponly' => true,  // Set the HttpOnly flag
		 'cookie_samesite' => 'Lax', // Set to 'Strict' if needed
	 ]
 );


$al = new Adminlogin();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$adminUser = $_POST['adminUser'];
	$adminPassword = md5($_POST['adminPassword']);

	$loginchk = $al->adminlogin($adminUser,$adminPassword);
}
?>

<!DOCTYPE html>
<head>
<meta charset="utf-8">
<title>Admin Login</title>
    <link rel="stylesheet" type="text/css" href="css/stylelogin.css" media="screen" />
</head>
<body>
<div class="container">
	<section id="content">
		<form action="login.php" method="post">
			<h1>Admin Login</h1>
<span style="color: red;font-size: 18px;">
	<?php
if (isset($loginchk)) {
	echo $loginchk;
}

	?>

</span>

			<div>
				<input type="text" placeholder="Username"  name="adminUser"/>
			</div>
			<div>
				<input type="password" placeholder="Password"  name="adminPassword"/>
			</div>
			<div>
				<input type="submit" value="Log in" />
			</div>
		</form><!-- form -->
		<div class="button">
			<a href="#">Online shopping</a>
		</div><!-- button -->
	</section><!-- content -->
</div><!-- container -->
</body>
</html>