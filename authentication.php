<?php
    require_once 'login.php';
    require_once 'fatalerror.php';

    if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
	{
        $conn = new mysqli($_hostName, $_userName, $_passWord, $_dataBase);
        if($conn->connect_error) die (mysql_fatal_error());

        $_email_Id = mysql_entities_fix_string($conn, $_SERVER['PHP_AUTH_USER']);
        $_password = mysql_entities_fix_string($conn, $_SERVER['PHP_AUTH_PW']);
        
        $query = "SELECT * FROM Users WHERE email='$_email_Id'";
        
        $result = $conn->query($query);
        
        if(!$result) die("Invalid Email Id/password Combination");
        
        $row = $result->fetch_array(MYSQLI_ASSOC);

        $saltOne = $row['saltone'];
        $saltTwo = $row['salttwo'];
        $hashtoken = $row['passwordtoken'];

        $token = hash('ripemd128', $saltOne.$_password.$saltTwo);
        
		if ($token == $hashtoken){
			session_start();
            $_SESSION['email'] = $_email_Id;
            $_SESSION['check'] = hash('ripemd128', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);

            echo "Welcome Back. Login Successful<br>";
            echo "<a href='translate.php'>Click Here To Continue</a>";
			
		}	
        else die("Invalid Email Id/password Combination");
        
        $result->close();
		$conn->close();
	}
	else
	{
		header('WWW-Authenticate: Basic realm="Restricted Section"');
		header("HTTP/1.0 401 Unauthorized");
		die (" Enter your Email Id and Password ");
    }
    
    function mysql_entities_fix_string($connection, $string) {
        return htmlentities(mysql_fix_string($connection, $string));
    }
     
     function mysql_fix_string($connection, $string) {
         if (get_magic_quotes_gpc()) $_string = stripslashes($string);
         return $connection->real_escape_string($string);
    }
?>