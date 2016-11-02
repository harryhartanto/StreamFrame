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
				<h3 class="panel-title"><span class="glyphicon glyphicon-file"></span> <b>Task Form</b></h3>
			</div>
			<div class="panel-body" id='taskFormDiv' style="font-size:13px;font-weight:normal;text-align:justify;">

        <form class="form-horizontal" id='createFormView' name='createFormView' >
        <div class="form-group" id="newTaskNameDiv">
          <label  class="control-label col-sm-2">New Task Name:</label>
          <div class="col-sm-10">
            <input class="form-control" type="text" name="newTaskName" id="newTaskName">
          </div>
        </div>
        <div class="form-group" id="modifyTaskDiv">
          <label  class="control-label col-sm-2">Modify Task: </label>
          <div class="col-sm-10">
            <select name="modifyTask" id="modifyTask" onchange="refreshParentID()"></select>
          </div>
        </div>
        <div class="form-group" id="parentTaskIDDiv">
          <label  class="control-label col-sm-2">Parent Task ID: </label>
          <div class="col-sm-10">
            <select name="parentTaskID" id="parentTaskID"></select>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-12">
            <button class="btn btn-success" type="button" id="submitBtn" value="Submit">Submit</button>
          </div>
        </div>
        </form>

			</div>
		</div>
	</div>

  <div id="logPanel">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><span class="glyphicon glyphicon-book"></span> <b>Log</b></h3>
			</div>
			<div class="panel-body" id='logPanelDiv' style="font-size:13px;font-weight:normal;text-align:justify;">
			  <textarea readonly rows="10" cols="120" id='logArea'></textarea>
			</div>
		</div>
	</div>

	<div id="listTasks">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><span class="glyphicon glyphicon-tasks"></span> <b>List Of Tasks</b></h3>
			</div>
			<div id="listOfTasks" class="panel-body" style="font-size:13px;font-weight:normal;text-align:justify;">
				  <form class="form-horizontal" id='filterView' name='filterView' >
					<div class="form-group" id="filterStatusDiv">
					  <div class="col-sm-12">
						<h class="glyphicon glyphicon-filter"></h>
						<select name="filterStatus" id="filterStatus" onchange="refreshListViewTask()">
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
				$("#modifyTask").css("color", "white");
			}
			else
			{
				$("#modifyTask").attr('disabled','disabled');
				$("#modifyTask").css("color", "black");
			}
		}
	);

	$("#submitBtn").click(
		function()
		{
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
						var message =selectedModifyTaskName+" "+data;
						updateLogArea(message);
						//alert(selectedModifyTaskName+" "+data);
						refreshListOfTask();
					}
					);
				}
				else
				{
					  var message ="Please select one of the task to be modified.";
					  updateLogArea(message);
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
						$("#modifyTask").css("color", "white");
						var message =newTaskName+" "+data;
						updateLogArea(message);
						refreshListOfTask();
					}
				);
			}
		}
	);

});

//refresh both dropdown modifyTask & parentTaskID
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

//refresh the view of List Task
function refreshListViewTask()
{
  var selectedFilterStatus = $("#filterStatus").val();
  $.post("function.php",
    {
      action: 'SHOW_NESTED_LIST',
      status: selectedFilterStatus
    },
    function(data, status)
    {
      $("#listTaskView").html(data);
    }
  );
}

//refresh dropdown parentTaskID to remove the selected modifyTask
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

//update status when mark/unmark status
function changeStatus(checkBox) {
  var temp = checkBox.value;
  var arrValue = temp.split("-");  
  var status = 0;
  if(checkBox.checked==true)
  {
	  if(arrValue[2]=='parent')
	  {
		  status = 1;						//update to DONE
	  }
	  else
	  {
		  status = 2;						//update to COMPLETED
	  }
	  updateStatus(arrValue[0],status); 		
  }
  else
  {
	  updateStatus(arrValue[0],status);		//update to IN PROGRESS
  }
}

//update status of the task and refresh the view of list task
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
				updateLogArea("["+selectedID+"]"+data);
				refreshListViewTask();
			}
		}
	);
}

//rename the title of task
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
				updateLogArea(data);
				refreshListOfTask();
			}
		}
		);
	}
}

//append the log by result information
function updateLogArea(message)
{

  var logArea = document.getElementById('logArea');
  var tempLog = logArea.value;

  if(tempLog!="")
  {
    tempLog +='\n';
  }
  tempLog+=message;
  logArea.value=tempLog;
  logArea.scrollTop = logArea.scrollHeight;
}
</script>
</html>
