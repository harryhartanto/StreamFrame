<?php
$conn;
function connectDB()
{
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "test";

	// Create connection
	global $conn;
	$conn = mysqli_connect($servername, $username, $password, $dbname);

	// Check connection
	if (!$conn) {
		die("Connection failed: " . mysqli_connect_error());
	} 
	echo "Connected successfully";
	
}

function closeDB()
{	
	global $conn;
	mysqli_close($conn);
	echo "Disconected successfully";
}

function insert($sql)
{
	global $conn;
	connectDB();
	$statement = "INSERT INTO `tasks` (`id`, `title`, `status`, `parent_id`) VALUES (NULL, 'task B', '0', '0');";
	$result = mysqli_query($conn,$statement);
	if ($result) {
		echo "1 row inserted";
	} else {
		echo "0 results";
	}
	closeDB();
}

$taskName=$_POST['taskName'];
$parentTaskID=$_POST['parentTaskID'];
$action = $_POST['action'];
 
?>