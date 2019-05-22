<?php

require_once '/opt/lampp/htdocs/login.php';

$conn = new mysqli($hn,$un,$pw,$db);
if($conn->connect_error)die($conn->$connect_error);


if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){
  $un_temp=mysql_entities_fix_string($conn, $_SERVER['PHP_AUTH_USER']);
  $pw_temp=mysql_entities_fix_string($conn,$_SERVER['PHP_AUTH_PW']);
  $q="SELECT * FROM credential WHERE username='$un_temp'";
  $r=$conn-> query($q);

  if(!$r) die ($conn-> error);
  elseif($r->num_rows){
    $row=$r->fetch_array(MYSQLI_NUM);
    $r->close();
    $salt1='@ry*h';
    $salt2='pa!n';
    $token=hash('ripemd128',"$salt1$pw_temp$salt2");
    if($token==$row[2]){
           session_start();
           $_SESSION['username']=$un_temp;
           $_SESSION['password']=$pw_temp;
           $_SESSION['email']=$row[0];
           $_SESSION['check']=hash('ripemd128',$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
           echo "LogIn successfully.<br>";
           die("<p><a href=home.php>Continue to HomePage</a></p>");
        }
         else{
           die("Not this username/password");}

        }
  else {die("invalid username and password");}
}
else{
  header('WWW-Authenticate:Basic realm="Restricted Section"');
  header('HTTP/1.0 401 Unahthorized');
  die ("Please enter your username and password");
}

$conn-> close();

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
