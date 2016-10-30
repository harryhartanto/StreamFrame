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
	if (!$conn)
	{
		die("Connection failed: " . mysqli_connect_error());
	}
	//echo "Connected successfully";

}

function closeDB()
{
	global $conn;
	mysqli_close($conn);
	//echo "Disconected successfully";
}

function insert($taskName,$parentTaskID)
{
	global $conn;
	connectDB();
	$sql = "INSERT INTO `tasks` (`id`, `title`, `status`, `parent_id`) VALUES (NULL, '".$taskName."', '0', '".$parentTaskID."');";
	$result = mysqli_query($conn,$sql);
	if ($result)
	{
		echo "Sucessfully Created!";
	}
	else
	{
		echo "Failed!";
	}
	closeDB();
}

function selectAll($taskID)
{
	global $conn;
	connectDB();
	$sql = "select id, title from tasks";
	$result = mysqli_query($conn,$sql);
	$finalResult = "<option value='0'>Please Select</option>";
	if (mysqli_num_rows($result) > 0)
	{
    // output data of each row
    while($row = mysqli_fetch_assoc($result))
		{
				if($row["id"]!=$taskID)
				{
					$finalResult .="<option value='" . $row["id"]. "'>[" . $row["id"]."] ". $row["title"]. "</option>";
				}
    }
	}
	closeDB();
	echo $finalResult;
}

function modifyTaskParent($modifyTaskID,$parentTaskID)
{
	global $conn;
	connectDB();

	//check down the trees
	$found = checkDownTree($modifyTaskID,$parentTaskID);
	if($found)
	{
		echo " is rejected due to Circular Dependencies!";
	}
	else
	{
		//update the parent_id
		$sql = "update tasks set parent_id=".$parentTaskID." where id = ".$modifyTaskID;
		$result = mysqli_query($conn,$sql);
		if ($result)
		{
			echo "is Sucessfully Updated!";
		}
		else
		{
			echo " is Failed!";
		}
	}

	closeDB();
}

function checkDownTree($modifyTaskID,$parentTaskID)
{
		global $conn;
		$found = FALSE;
		$sql = "select id from tasks where parent_id=".$modifyTaskID;
		$result = mysqli_query($conn,$sql);
		if (mysqli_num_rows($result) > 0)
		{
			while($row = mysqli_fetch_assoc($result))
			{
					if($row["id"]!=$parentTaskID)
					{
						$temp = checkDownTree($row["id"],$parentTaskID);
						if($temp==true)
						{
							$found = true;
							return $found;
						}
					}
					else
					{
						$found = true;
						return $found;
					}
			}
		}
		else //not a parent task
		{
			return $found;
		}
}

function showFlatList()
{
	global $conn;
	connectDB();
	$sql = "select id, title, status, parent_id from tasks";
	$result = mysqli_query($conn,$sql);
	$finalResult = "<ul>";
	$status = "";		//IN PROGRESS / DONE / COMPLETED
	if (mysqli_num_rows($result) > 0)
	{
		while($row = mysqli_fetch_assoc($result))
		{
				$checked = "checked";
				if($row["status"]==0)
				{
					$status = "IN PROGRESS";
					$checked = "";
				}
				else if($row["status"]==1)
				{
					$status = "DONE";
				}
				else
				{
					$status = "COMPLETED";
				}
				$finalResult .="<li>[".$row["id"]."] ".$row["title"]." <input type='checkbox' name='status' value='".$row["id"]."' ".$checked.">- ".$status."</li>";
		}
		$finalResult .= "</ul>";
		echo $finalResult;
	}
	else //nothing to show
	{
		echo "EMPTY";
	}
	closeDB();
}

$action = $_REQUEST['action'];

switch ($action) {
	case "INSERT":
		$taskName=$_REQUEST['taskName'];
		$parentTaskID=$_REQUEST['parentTaskID'];
		echo insert($taskName,$parentTaskID);
		break;

	case "SELECT_ALL":
		$taskID=$_REQUEST['taskID'];
		return selectAll($taskID);
		break;

	case "MODIFY":
		$modifyTaskID=$_REQUEST['modifyTaskID'];
		$parentTaskID=$_REQUEST['parentTaskID'];
		return modifyTaskParent($modifyTaskID,$parentTaskID);
		break;

	case "SHOW_FLAT_LIST":
		return showFlatList();
		break;

	default:
		echo "Invalid action request!";
		break;
}


?>
