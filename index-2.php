
<?php
    
    require_once 'login.php';
    require_once 'fatalerror.php';
    require_once 'defaultmodel.php';

    error_reporting(0);
    ini_set('display_errors', 0);

    echo <<<_START
        <!doctype html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <title>LAME TRANSLATOR</title>
        </head>
        <body style="background-color: gray;">
            <div>
                <h3 class="mainHeading" align="center" style="color:Black;">Translations made easier with Lame Translator!!! Convert ENGLISH to SWEDISH </h3>
            </div>
            <div>
                <div>
                    <p class="semiHeading" style=" color: Black;">Used the translator already?  Enter your credentials here:  <a href="authentication.php">login</a></p>
                </div> 
                <div class="center">
                <p style="color: Black;"> Haven't tried it out? sign up here</p>
                    <h3 style="color: Black;">Register</h3>
                    <form style="background-color: gray;" method='post' action='index.php' enctype='multipart/form-data' >
                        Enter your email:<br>
                        <input type="email" maxlength="40" name="email" required><br>
                        password: <br>
                        <input type="password" minlength="8" name="passwordone" required><br>
                        <br>
                        
                        <input type='submit' value='sign up'>
                    </form>
                </div> 
                <br>
                <form style="background-color: gray;" method='post' action='index.php' enctype='multipart/form-data' id='usrform'>
                    <textarea name="englishcontent" form="usrform"></textarea>
                    <input type="submit" value='Translate' name='translate'>
                </form>   
            </div>
_START;
   

    if(isset($_POST['email']) && isset($_POST['passwordone'])){

        $_email_Id = filter_var(($_POST["email"]), FILTER_SANITIZE_EMAIL);
        $_password_One = filter_var($_POST["passwordone"], FILTER_SANITIZE_STRING);
        

        if(strlen($_password_One) >= 8){

            $conn = new mysqli($_hostName, $_userName, $_passWord, $_dataBase);
            if ($conn->connect_error) die(mysql_fatal_error());

            $saltOne = returnRandomSalt();
            $saltTwo = returnRandomSalt();

            $token = hash('ripemd128', $saltOne.$_password_One.$saltTwo);
            
            $query = "INSERT INTO Users (email, passwordtoken, saltone, salttwo) VALUES ('$_email_Id', '$token', '$saltOne', '$saltTwo')";

            $result = $conn->query($query);

            if (!$result) {
                
                echo "Error: 500";
            }
            else{
                echo "Sign Up successful, Welcome to the translator, you can now login";
            }
            $conn->close();

        }else{
            echo "Please enter a password greater that 8 chars";
        }


    }

    

    if(isset($_POST['translate'])){
        $englishcontent = $_POST['englishcontent'];
        if(strlen($englishcontent) > 0){
            echo "Translating using the default model: <br>";
            $result = translate($englishcontent."   ", $_tester);
            echo $result;
        }
    }    

    
    function returnRandomSalt() {
        $_characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789/\\][{}\'";:?.>,<!@#$%^&*()-_=+|';
        $Strlen = 14;
   
        $ans = "";
        for ($i = 0; $i < $Strlen; $i++) {
            $ans .= $_characters[mt_rand(0, strlen($_characters) - 1)];
        }
   
        return $ans;
   }

   function translate($_input, $_default){
        $_afterTranslation = "";

        while(TRUE){
            $_index_Of_Space = strpos($_input, " ");

            if(isset($_index_Of_Space) && strlen($_input) > 0){
                $_english = substr($_input, 0, $_index_Of_Space);

                if(!$_default[$_english]){
                    $swedish = $_english;
                }else{
                    $swedish = $_default[$_english];
                }

                $_afterTranslation = $_afterTranslation . " " . $swedish;

                $_input = substr($_input, $_index_Of_Space + 1, strlen($_input));
            }
            else
            {
                break;
            }

        }

        return $_afterTranslation;
    }


    echo <<<_END
        </body>
        </html>
_END;
?>