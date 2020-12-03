<?php
    require_once 'login.php';
    require_once 'fatalerror.php';
    require_once 'defaultmodel.php';

    error_reporting(0);
    ini_set('display_errors', 0);

    session_start();
    if (isset($_SESSION['email']) && $_SESSION['check'] == hash('ripemd128', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']))
    {
        $_email_Id = $_SESSION['email'];

        session_regenerate_id();

      

        echo <<<_START
        <!doctype html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <title>Spanish Translate</title>
        </head>
        <body>
            <form method='post' action='translate.php' enctype='multipart/form-data'>
                <input type='submit' value='logout' name='logoutbutton'>
            </form>
            <br>
            <form method='post' action='translate.php' enctype='multipart/form-data' >
                Select File: <input type='file' name='uploadfileinput' size='10' >
                <input type='submit' value='Upload you own model'>
            </form>
            <br>
            <form method='post' action='translate.php' enctype='multipart/form-data' id='usrform'>
                <textarea name="englishcontent" form="usrform"></textarea>
                <input type="submit" value='Translate' name='translate'>
            </form>
_START;

        if(isset($_POST['logoutbutton'])){
            destroy_session_and_data();
            header("Location: index.php");
        }

        if(isset($_POST['translate'])){
            $_input = $_POST['englishcontent'];
            if(strlen($_input) > 0){
                $conn = new mysqli($_hostName, $_userName, $_passWord, $_dataBase);
                if ($conn->connect_error) die(mysql_fatal_error());

                $query = "SELECT * FROM Translations WHERE email='$_email_Id'";
                $result = $conn->query($query);

                if(!$result) die(mysql_fatal_error());
   
                $rows = $result->num_rows;

                $model = [];

                if($rows > 0){
                    for($i = 0; $i < $rows; $i++){
                        $result->data_seek($i);
                        $row = $result->fetch_array(MYSQLI_ASSOC);
                        
                        $_english = $row['english'];
                        $swedish = $row['swedish'];

                        $model[$_english] = $swedish;
                    }

                    $answer = translate($_input."   ", $model);

                    echo "Converting in your model <br>";

                    echo $answer;

                    $result->close();
                    $conn->close();
                }else{
                    echo " Converting in default model <br>";
                    $answer = translate($_input."   ", $_tester);
                    echo $answer;
                }
                

            }
        }

        if($_FILES){
            $_theInputType = $_FILES['uploadfileinput']['type'];
            if($_theInputType == "text/plain"){

                $_theName = $_FILES['uploadfileinput']['name'];

                $fh = fopen($_theName, 'r') or die("File Does not exist");

                $_theFileData = filter_var(file_get_contents($_theName), FILTER_SANITIZE_STRING);

                fclose($fh);

                $model = readContents($_theFileData);

                echo "<br><br> $ans <br><br>";

                $conn = new mysqli($_hostName, $_userName, $_passWord , $_dataBase);
                if ($conn->connect_error) die(mysql_fatal_error());
                
                foreach($model as $key => $value) {
                    $query = "INSERT INTO Translations (email, english, swedish) VALUES ('$_email_Id', '$key', '$value')";
                    $result = $conn->query($query);
                    if(!$result){
                        echo "Error: 500, Already Exists <br>";
                    }
                }

                $conn->close();

            }
        }
  
    }
    else{
        destroy_session_and_data();
        echo "Please <a href='auth.php'>click here</a> to log in.";
    } 


    function destroy_session_and_data()
	{
        $_SESSION = array();
        setcookie(session_name(), '', time() - 2592000, '/');
		session_destroy();
    }
    
    function readContents($data){
        $model = [];

        while(TRUE){
            $_index_Open = strpos($data, "(");
            $_index_Close = strpos($data, ")");
            
            if(isset($_index_Open) && isset($_index_Close) && strlen($data) > 0){

                $_comma_Index = strpos($data, ",");
                $_english = substr($data, $_index_Open + 1, $_comma_Index - $_index_Open - 1);

                $spanish = substr($data, $_comma_Index + 1, $_index_Close - $_comma_Index - 1);

                $model[$_english] = $spanish;

                $data = substr($data, $_index_Close+1, strlen($data));
            }
            else
            {
                break;
            }
        }

        return $model;
    }

    function translate($data, $_default){
        $_output = "";

        while(TRUE){
            $_index_Space = strpos($data, " ");

            if(isset($_index_Space) && strlen($data) > 0){
                $_english = substr($data, 0, $_index_Space);

                if(!$_default[$_english]){
                    $_swedish = $_english;
                }else{
                    $_swedish = $_default[$_english];
                }

                $_output = $_output . " " . $_swedish;

                $data = substr($data, $_index_Space + 1, strlen($data));
            }
            else
            {
                break;
            }

        }

        return $_output;
    }


?>