<?php
define('DB_USER', "root"); // db user
define('DB_PASSWORD', "ae7b02522a760215480517b1572a39aa9a0ea60f439a622f"); // db password (mention your db password here)
define('DB_DATABASE', "fintrack"); // database name
define('DB_SERVER', "localhost"); // db server
 
$con = mysqli_connect(DB_SERVER,DB_USER,DB_PASSWORD,DB_DATABASE);
 
// Check connection
if(mysqli_connect_errno())
{
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
 
?>