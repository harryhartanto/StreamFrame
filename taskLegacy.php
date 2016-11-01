<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="style.css" media="screen" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>
	<div id="taskForm">
		<div class="panel panel-default">
			<div class="panel-heading">
				
				<h3 class="panel-title"><span class="glyphicon glyphicon-file"></span>Task Form</b></h3>
			</div>
			<div class="panel-body" style="font-size:13px;font-weight:normal;text-align:justify;">						  
			  <table border = '0' id='createForm' name='createForm' >
				<thead>
				<tr>
					<td>New Task Name:</td>
					<td><input class="form-control" type="text" name="newTaskName" id="newTaskName"></td>
					<td id="info"></td>
				</tr>
				<tr>
					<td>Modify Task Name: </td>
					<td>
						<select  class="form-control" name="modifyTask" id="modifyTask" onchange="refreshParentID()"></select>
					</td>
					<td id="infoModify"></td>
				</tr>
				<tr>
					<td>Parent Task ID: </td>
					<td>
						<select class="form-control" name="parentTaskID" id="parentTaskID"></select>
					</td>
				</tr>
				<tr>
					<td><button class="btn btn-success" type="button" id="submitBtn" value="Submit">Submit</button></td>
				</tr>
				</table>
			</div>
		</div>
	</div>
	<div id="listTasks">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><span class="glyphicon glyphicon-tasks"></span> List Of Tasks</b></h3>
			</div>
			<div id="listOfTasks" class="panel-body" style="font-size:13px;font-weight:normal;text-align:justify;">
				  <form class="form-horizontal" id='filterView' name='filterView' >
					<div class="form-group">								  
					  <div class="col-sm-12">
						<span class="glyphicon glyphicon-filter"></span>
						<select class="form" name="filterStatus" id="filterStatus" onchange="filterStatus()">
							<option value='99'>ALL</option>
							<option value='0'>IN PROGRESS</option>
							<option value='1'>DONE</option>
							<option value='2'>COMPLETE</option>
						</select>
					  </div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<div id="listTaskView"></div>
						</div>
					</div>
				</form>
			</div>
		</div>
		
	</div>
	
</body>
<script>

$(document).ready(function()
{
	refreshListOfTask();
	$("#newTaskName").keyup(
		function()
		{
			var temp=$("#newTaskName").val();
			if(!temp.trim())
			{
				$("#modifyTask").removeAttr('disabled');
			}
			else
			{
				$("#modifyTask").attr('disabled','disabled');
			}
		}
	);

	$("#submitBtn").click(
		function()
		{
			$("#info").text("");
			$("#info").fadeIn(0);
			$("#infoModify").text("");
			$("#infoModify").fadeIn(0);
			var newTaskName=$("#newTaskName").val();
			var selectedParentTaskID = $("#parentTaskID").val();
			if(!newTaskName.trim())				//insert action
			{
				var selectedModifyTaskID=$("#modifyTask").val();
				var selectedModifyTaskName=$("#modifyTask option:selected").text();
				if(selectedModifyTaskName!="Please Select")
				{
					$.post("function.php",
					{
						modifyTaskID: selectedModifyTaskID,
						parentTaskID: selectedParentTaskID,
						action: 'MODIFY'
					},
					function(data, status)
					{
						$("#infoModify").text(selectedModifyTaskName+" "+data);
						$("#infoModify").fadeOut(5000);
						//alert(selectedModifyTaskName+" "+data);
						refreshListOfTask();
					}
					);
				}
				else
				{
					$("#infoModify").text("Please select one of the task to be modified.");
					$("#infoModify").fadeOut(5000);
				}
			}
			else 							   //modify action
			{
				$.post("function.php",
					{
						taskName: newTaskName,
						parentTaskID: selectedParentTaskID,
						action: 'INSERT'
					},
					function(data, status)
					{
						$("#newTaskName").val("");
						$("#modifyTask").removeAttr('disabled');
						$("#info").text(newTaskName+" "+data);
						$("#info").fadeOut(5000);
						refreshListOfTask();
					}
				);
			}
		}
	);


});

function refreshListOfTask()
{
	$.post("function.php",
		{
			action: 'SELECT_ALL',
			taskID: 'ALL'
		},
		function(data, status)
		{
			$("#parentTaskID").html(data);
			$("#modifyTask").html(data);
		}
	);		
	
	refreshListViewTask();
}

function refreshListViewTask()
{
	$.post("function.php",
			{
				action: 'SHOW_NESTED_LIST',
				status: '99'			//ALL
			},
			function(data, status)
			{
				$("#listTaskView").html(data);
			}
		);
}
function refreshParentID()
{
	var selectedModifyTask = $("#modifyTask").val();
	$.post("function.php",
		{
			action: 'SELECT_ALL',
			taskID: selectedModifyTask
		},
		function(data, status)
		{
			$("#parentTaskID").html(data);
		}
	);
}

function filterStatus()
{
	var selectedFilterStatus = $("#filterStatus").val();
	$.post("function.php",
		{
			action: 'SHOW_FLAT_LIST',
			status: selectedFilterStatus
		},
		function(data, status)
		{
			$("#listTaskView").html(data);
		}
	);
}

function updateStatus(selectedID, selectedStatus)
{
	$.post("function.php",
		{
			action: 'UPDATE_TASK_STATUS',
			id: selectedID,
			status: selectedStatus
		},
		function(data, status)
		{
			if(status=="success")
			{
				refreshListViewTask();
			}			
		}
	);
}

function changeStatus(checkBox) {
  var temp = checkBox.value;
  var arrValue = temp.split("-");
  if(checkBox.checked==true)
  {
	  updateStatus(arrValue[0],2); 		//update to COMPLETED
  }
  else
  {
	  updateStatus(arrValue[0],0);		//update to IN PROGRESS
  }
}
function changeTitle(header,e, selectedID)
{
	var newTaskName = header.innerHTML;
	if(e.keyCode==13)
	{
		$.post("function.php",
		{
			action: 'UPDATE_TASK_NAME',
			id: selectedID,
			taskName: newTaskName
		},
		function(data, status)
		{
			if(status=="success")
			{
				refreshListOfTask();
			}			
		}
		);
		
	}
}
</script>
</html>
