<?php


 session_start();
 destroy_session();
 echo <<<_END
 <html>
<a href="auth.php"> Re-Login</a>
 </html>
_END;

function destroy_session(){
  $_SESSION=array();
  setcookie(session_name(),",time() - 2592000,'/");
  session_destroy();

}

 ?>
