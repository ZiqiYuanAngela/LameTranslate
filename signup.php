<?php

require_once '/opt/lampp/htdocs/login.php';

$conn = new mysqli($hn,$un,$pw,$db);
if($conn->connect_error)die($conn->$connect_error);

echo <<<_END
<html>
<h1>SignUp</h1>
<form action="signup.php" method="post" enctype='multipart/form-data'>
<input placeholder="email" name='email' required="required"></input>
<input placeholder="username" name="username" required="required"></input>
<input type="password" placeholder="password" name="password" required="required"></input>
<input type="submit" value="Submit"></input>
</form>
<a href="auth.php"> LogIn</a>
</html>
_END;



if(!empty($_POST['email']) && !empty($_POST['username']) && !empty($_POST['password'])){
  $email = mysql_entities_fix_string($conn, $_POST['email']);
  $username = mysql_entities_fix_string($conn, $_POST['username']);
  $password = mysql_entities_fix_string($conn, $_POST['password']);
  $salt1='@ry*h';
  $salt2='pa!n';
  $token=hash('ripemd128',"$salt1$password$salt2");
  $query = "INSERT INTO credential VALUES ('$email' , '$username','$token')";
  $result = $conn->query($query);
  if (!$result) {die("Failed: " .$conn->error);}
  else {
    $result->close();
    $conn->close();

  }
}

function mysql_entities_fix_string($conn, $string)
{
  return htmlentities(mysql_fix_string($conn, $string));
}

function mysql_fix_string($conn, $string)
{
  if (get_magic_quotes_gpc()) $string = stripslashes($string);
  return $conn->real_escape_string($string);
}





 ?>
