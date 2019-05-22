<?php
require_once '/opt/lampp/htdocs/login.php';



$conn = new mysqli($hn,$un,$pw,$db);
if($conn->connect_error)die($conn->$connect_error);

ini_set('session.gc_maxlifetime',60*60*24);
function destroy_session(){
  $_SESSION=array();
  setcookie(session_name(),",time() - 2592000,'/");
  session_destroy();

}

$hasModel=True;

session_start();

if(isset($_SESSION['username'])){
  $username=$_SESSION['username'];
  $password=$_SESSION['password'];
  $email=$_SESSION['email'];
  if($_SESSION['check']!=hash('ripemd128',$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])){
    destroy_session();
  }

  $queryModel = "SELECT * FROM transModel WHERE username='$username'";
  $resultModel = $conn->query($queryModel);
  if(!$resultModel) die ($conn -> error);else{
    $row=$resultModel-> num_rows;

  if($row==0){
    $hasModel=False;
  }
}

  echo <<<_END
  <html>
  <h1>Lame Translate</h1>
  <form action="home.php" method="post" enctype='multipart/form-data'>
  <p>File with English Text</p>
  <input type="file" name="filename1" required="required"></input>
  <p>File with Translation</p>
  <input type="file" name="filename2" required="required"></input>
  <input type="submit" value="Upload Files"></input>
  </form>

  </html>
_END;

}
else{
  echo "Please <a href='auth.php'> Go Login </a>to Set Up Your Translation Model or Try out the Default one here .";
}

echo <<<_END
<html>
<form action="home.php" method="post" enctype='multipart/form-data'>
<p></p>
<textarea rows='4' cols='40' placeholder="input text for translation" name='input' required="required"></textarea>
<p></p>
<input type="submit" value="Upload Text"></input>
</form>

<p></p>
</html>
_END;

if(isset($_SESSION['username'])){
  echo <<<_END
  <a href=logout.php>Logout</a>.<br>
_END;
}





if($_FILES)
{
		$filename1 = $_FILES['filename1']['name'];
    $filename2 = $_FILES['filename2']['name'];
		if($_FILES['filename1']['type'] == 'text/plain' && $_FILES['filename2']['type'] == 'text/plain') {
      $fh1=fopen($filename1,'r+') or die ("Failed to open file");
      $fh2=fopen($filename2,'r+') or die ("Failed to open file");
      flock($fh1,LOCK_EX);
      $content1 = file_get_contents($filename1);
      flock($fh1,LOCK_UN);
      flock($fh2,LOCK_EX);
      $content2 = file_get_contents($filename2);
      flock($fh2,LOCK_UN);
      $content1_str = mysql_entities_fix_string($conn, $content1);
      $content2_str = mysql_entities_fix_string($conn, $content2);
      if(sizeof(explode(' ',$content1_str))!=sizeof(explode(' ',$content2_str))){
         echo 'Dictionary not valid because of Size Mismatch and Translation with this model may malfunction.Please check and upload your new dictionary again!! ';
       }else{
        if(!$hasModel){
         $query = "INSERT INTO transModel VALUES ('$username' , '$content1_str','$content2_str')";
         $hasModel=True;
         }

        else{
         $query = "UPDATE transModel SET originalT ='$content1_str', transT = '$content2_str' WHERE username='$username'";
        }
      $result = $conn->query($query);
      if (!$result) die("Failed: " .$conn->error);

          $result->close();


       }


  }
  else
  {
    echo "'$filename' is not accepted";
  }

}

if(isset($_SESSION['username'])){
  if(!empty($_POST['input'])){
    if($hasModel){
            $query2 = "SELECT * FROM transModel WHERE username='$username'";
            $result2 = $conn->query($query2);
            if(!$result2) die ($conn -> error);
            else{
                $result2-> data_seek(0);
                $originalText=$result2-> fetch_assoc()['originalT'];

                $result2-> data_seek(0);
                $translatedT=$result2-> fetch_assoc()['transT'];
                }

                $array1= explode(' ',$originalText);
                $array2= explode(' ',$translatedT);
                $outputArray;
                if(sizeof($array1)!=sizeof($array2)){
                   echo 'Dictionary not valid because of Size Mismatch and Translation may malfunction.Please check and upload your dictionary again ';
                }
                else{
                for($i=0;$i<sizeof($array1);++$i){
                    $outputArray[$array1[$i]]=$array2[$i];

                             }
                    $inputText = mysql_entities_fix_string($conn, $_POST['input']);
                    $inputArray= explode(' ',$inputText);

                    for($j=0;$j<sizeof($inputArray);++$j){
                          echo $outputArray[$inputArray[$j]].' ';
                    }
                  }



               $result2->close();
        }else{
          echo "you don't have any Translation Model uploaded yet, default translation(no translation) mode will be applied.";
          echo "To upload your dictionary, please upload two files above.<br>";
          $inputText = mysql_entities_fix_string($conn, $_POST['input']);
          echo '<br>Translation Result: .<br>'.$inputText;
        }

}
}

if(!isset($_SESSION['username'])){
   if(!empty($_POST['input'])){
      $inputText = mysql_entities_fix_string($conn, $_POST['input']);
      echo '<br>Translation Result: .<br>'.$inputText;
   }
}



  $resultModel->close();
  $conn->close();


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
