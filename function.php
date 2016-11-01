<?php
$conn;

$action = $_REQUEST['action'];
//main menu
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
		$status=$_REQUEST['status'];
		return showFlatList($status);
		break;

	case "SHOW_NESTED_LIST":
		$status=$_REQUEST['status'];
		return showNestedHierarchyList($status);
		break;

	case "UPDATE_TASK_STATUS":
		$id=$_REQUEST['id'];
		$status=$_REQUEST['status'];
		return updateTaskStatus($id,$status);
		break;

	case "UPDATE_TASK_NAME":
		$id=$_REQUEST['id'];
		$taskName=$_REQUEST['taskName'];
		return updateTaskName($id,$taskName);
		break;

	default:
		echo "Invalid action request!";
		break;
}

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
}

function closeDB()
{
	global $conn;
	mysqli_close($conn);
}

//create new task
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

//get all the task details
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

//update parent of certain task, and update parent status if any
function modifyTaskParent($modifyTaskID,$parentTaskID)
{
	global $conn;
	connectDB();

	//check down the trees to check the circular dependencies
	$found = checkDownTree($modifyTaskID,$parentTaskID);
	if($found)
	{
		echo " is rejected due to Circular Dependencies!";
	}
	else
	{
		//get the former parent
		$sql = "SELECT parent_id FROM `tasks` WHERE id = '".$modifyTaskID."'";
		$result = mysqli_query($conn,$sql);
		$oldParentID = 0;
		while($row = mysqli_fetch_assoc($result))
		{
			$oldParentID = $row["parent_id"];
		}

		//update to the new parent_id
		$sql = "update tasks set parent_id=".$parentTaskID." where id = ".$modifyTaskID;
		$result = mysqli_query($conn,$sql);

		//search the former siblings
		$sql2 = "select id, title, status from tasks where parent_id = '".$oldParentID."' LIMIT 1";
		$result2 = mysqli_query($conn,$sql2);
		$oldSiblingID = 0;
		$oldSiblingTitle = "";
		$oldSiblingStatus = 0;
		if (mysqli_num_rows($result2) > 0)
		{
			while($row2 = mysqli_fetch_assoc($result2))
			{
				$oldSiblingID = $row2["id"];
				$oldSiblingTitle = $row2["title"];
				$oldSiblingStatus = $row2["status"];
			}
		}

		if ($result)
		{
			$sql = "select status from tasks where id = ".$modifyTaskID;
			$result = mysqli_query($conn,$sql);
			closeDB();
			while($row = mysqli_fetch_assoc($result))
			{
				updateTaskStatus($modifyTaskID,$row["status"]);				//update the whole new family
			}


			if($oldSiblingID!=0)
			{
				echo "Got siblings : [".$oldSiblingID."] - ".$oldSiblingTitle." ";
				updateTaskStatus($oldSiblingID,$oldSiblingStatus);			//update the whole old family if any
			}
		}
		else
		{
			echo " is Failed!";
		}
	}

}

//recursive function to find the circular dependencies
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

//to print the task list in flat mode
function showFlatList($status)
{
	global $conn;
	connectDB();
	$sql = "select id, title, status, parent_id from tasks ";
	$condition = "";
	if($status!='99')
	{
		$condition = "where status=".$status;
	}
	$sql .=$condition;
	$result = mysqli_query($conn,$sql);
	$finalResult = "<ul>";
	$status = "";		//IN PROGRESS / DONE / COMPLETED
	if (mysqli_num_rows($result) > 0)
	{
		while($row = mysqli_fetch_assoc($result))
		{
			$checked = "checked";
			$status = translateStatus($row["status"]);
			if($status=="IN PROGRESS")
			{
				$checked ="";
			}
			$finalResult .="<li>[".$row["id"]."] ".$row["title"]." <input onclick='changeStatus(this)' type='checkbox' name='status' value='".$row["id"]."-".$row["status"]."' ".$checked.">- ".$status."</li>";
		}
		$finalResult .= "</ul>";
		echo $finalResult;
	}
	else //nothing to show
	{
		$finalResult .= "NOTHING TO SHOW</ul>";
		echo $finalResult;
	}
	closeDB();
}

//update the status of the selected id and the whole family related to it.
function updateTaskStatus($id,$status)
{
	global $conn;
	connectDB();
	$sql = "update tasks set status = ".$status." where id = ".$id;
	$result = mysqli_query($conn,$sql);
	if ($result)
	{
		updateParentTaskStatus($id);
		echo "Sucessfully Updated!; ";
	}
	else
	{
		echo "Failed!";
	}
	closeDB();
}

//update the whole family related to the selected id
function updateParentTaskStatus($id)
{
	global $conn;
	$sql = "select parent_id from tasks where id = ".$id;
	$result = mysqli_query($conn,$sql);
	while($row = mysqli_fetch_assoc($result))
	{
		$parent_id = $row["parent_id"];
		if($parent_id!=0)
		{
			$sql2 = "select avg(status) avg from tasks where parent_id=".$parent_id;
			$result2 = mysqli_query($conn,$sql2);
			{
				while($row2 = mysqli_fetch_assoc($result2))
				{
					$newStatus=0;
					if($row2["avg"]==2)
					{
						$newStatus=2;
					}
					else if($row2["avg"]>0)
					{
						$newStatus=1;
					}
					else
					{
						$newStatus=0;
					}
				}
				$sql3 = "update tasks set status = ".$newStatus." where id = ".$parent_id;
				$result3 = mysqli_query($conn,$sql3);
			}
			updateParentTaskStatus($parent_id);
		}
	}
}

//to print the task list in nested hierarchy mode
function showNestedHierarchyList($status)
{
	global $conn;
	connectDB();
	$sql = "select id, title, status, parent_id from tasks where parent_id=0";
	$result = mysqli_query($conn,$sql);
	$finalResult = "";
	$tempResult = "";
	if (mysqli_num_rows($result) > 0)
	{
		while($row = mysqli_fetch_assoc($result))
		{
			$output = checkNestedList($row["id"],$row["title"],$row["status"],$row["parent_id"],$status);
			$arrOutput = explode("|",$output);
			$tempResult .=$arrOutput[3];
		}
		$finalResult .= $tempResult;
		echo $finalResult;
	}
	else //nothing to show
	{
		$finalResult .= "NOTHING TO SHOW";
		echo $finalResult;
	}
	closeDB();
}

//drill down every single of task and form it into nested hierarchy form
function checkNestedList($id, $title, $status, $parent_id, $statusFilter)
{
	global $conn;
	$sql = "select id, title, status, parent_id from tasks where parent_id=".$id;
	$finalResult = "";
	$tempResult ="";
	$countDependencies= 0;
	$countDone= 0;
	$countComplete= 0;
	$result = mysqli_query($conn,$sql);
	if (mysqli_num_rows($result) > 0)
	{
		while($row = mysqli_fetch_assoc($result))
		{
			$output = checkNestedList($row["id"],$row["title"],$row["status"],$row["parent_id"],$statusFilter);
			$arrOutput = explode("|",$output);
			$countDependencies++;
			$countDependencies += $arrOutput[0];
			$countDone += $arrOutput[1];
			$countComplete += $arrOutput[2];
			$tempResult .=$arrOutput[3];
		}
		$statusValue = translateStatus($status);
		$opacity = "";
		if($statusFilter!=99 AND $statusFilter!=$status)
		{
			$opacity = "Filtered";
		}
		$finalResult .="<li>[".$id."] <h id='statusValue".$status.$opacity."'><h id='editableTaskName' contenteditable='true' onkeypress='changeTitle(this, event,".$id.")' >".$title."</h> (Total Dependencies = ".$countDependencies.", Total Done = ".$countDone.", Total Completed = ".$countComplete.") - ".$statusValue."</h></li>";
		$finalResult .="<ul>".$tempResult."</ul>";
		if($status=='1')
		{
			$countDone++;
		}
		if($status=='2')
		{
			$countComplete++;
		}
		return $countDependencies."|".$countDone."|".$countComplete."|".$finalResult;
	}
	else //not a parent task
	{
		if($status=='1')
		{
			$countDone++;
		}
		if($status=='2')
		{
			$countComplete++;
		}

		$checked = "checked";
		$statusValue = translateStatus($status);
		if($statusValue=="IN PROGRESS")
		{
			$checked ="";
		}
		$opacity = "";
		if($statusFilter!=99 AND $statusFilter!=$status)
		{
			$opacity = "Filtered";
		}
		return $countDependencies."|".$countDone."|".$countComplete."|<li>[".$id."] <h id='statusValue".$status.$opacity."'><h id='editableTaskName' contenteditable='true' onkeypress='changeTitle(this, event,".$id.")' >".$title."</h> <input onclick='changeStatus(this)' type='checkbox' id='checkBoxStat' name='status' value='".$id."-".$status."' ".$checked.">- ".$statusValue."</h></li>";
	}
}

function translateStatus($status)
{
	if($status==0)
	{
		return "IN PROGRESS";
	}
	else if($status==1)
	{
		return "DONE";
	}
	else
	{
		return "COMPLETED";
	}
}

//rename the title of the task
function updateTaskName($id,$taskName)
{
	global $conn;
	connectDB();
	$sql = "update tasks set title = '".$taskName."' where id = ".$id;
	$result = mysqli_query($conn,$sql);
	if ($result)
	{
		echo "Sucessfully Updated!";
	}
	else
	{
		echo "Failed!";
	}
	closeDB();
}
?>
