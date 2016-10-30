<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
</head>
<body>
	<div id="createTaskForm">

	  <table border = '0' id='createForm' name='createForm' >
		<thead>
		<tr>
			<td>New Task Name:</td>
			<td><input type="text" name="newTaskName" id="newTaskName"></td>
			<td id="info"></td>
		</tr>
		<tr>
			<td>Modify Task Name: </td>
			<!--td><input type="text" name="modifyTask" id="modifyTask"></td-->
			<td>
			<select name="modifyTask" id="modifyTask" onchange="refreshParentID()">
			</td>
			<td id="infoModify"></td>
		</tr>
		<tr>
			<td>Parent Task ID: </td>
			<td>
			<select name="parentTaskID" id="parentTaskID">
			</td>
		</tr>
		<tr>
			<td><input type="button" id="submitBtn" value="Submit"></td>
			<td><input type="button" id="test" value="Test" ></td>
		</tr>
	</table>
</div>
<div id="listTaskView"></div>
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
			if(!newTaskName.trim())	//insert action
			{
				var selectedModifyTaskID=$("#modifyTask").val();
				var selectedModifyTaskName=$("#modifyTask option:selected").text();
				$.post("connection.php",
					{
						modifyTaskID: selectedModifyTaskID,
						parentTaskID: selectedParentTaskID,
						action: 'MODIFY'
					},
					function(data, status)
					{
						// $("#infoModify").text(selectedModifyTaskName+" "+data);
						// $("#infoModify").fadeOut(5000);
						alert(selectedModifyTaskName+" "+data);
						refreshListOfTask();
						// alert("Data: " + data + "\nStatus: " + status);
					}
				);
			}
			else 							   //modify action
			{
				$.post("connection.php",
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
						// alert("Data: " + data + "\nStatus: " + status);
					}
				);
			}
		}
	);

	$("#test").click(
		function()
		{
			$.post("connection.php",
				{
					action: 'SELECT_ALL'
				},
				function(data, status)
				{
					$("#parentTaskID").html(data);
				}
			);
			// var result = "<option value='0'>Please Select</option><option value='1'>Yaha!</option>";
			// $("#parentTaskID").html(result);
		}
	);

	function refreshListOfTask()
	{
		$.post("connection.php",
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

		$.post("connection.php",
			{
				action: 'SHOW_FLAT_LIST'
			},
			function(data, status)
			{
				$("#listTaskView").html(data);
			}
		);
	}

});
function refreshParentID()
{
	var selectedModifyTask = $("#modifyTask").val();
	$.post("connection.php",
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
</script>
</html>
