<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
</head>
<body>
	<div id="createTaskForm">
	
	  <table border = '0' id='tbl_SEARCH_COMP_RESULT_NAME' name='tbl_SEARCH_COMP_RESULT_NAME' >
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
			<input list="listModifyTask" name="modifyTask" id="modifyTask">
			  <datalist id="listModifyTask">
				<option data-id="1" value="Internet Explorer">
				<option data-id="2" value="Firefox">
				<option data-id="3" value="Chrome">
				<option data-id="4" value="Opera">
				<option data-id="5" value="Safari">
			  </datalist>
			</td> 
		</tr>
		<tr>
			<td>Parent Task ID: </td> 
			<td> 
			<select name="parentTaskID" id="parentTaskID">
			  <option value="1">Volvo</option>
			  <option value="2">Saab</option>
			  <option value="3">Mercedes</option>
			  <option value="4">Audi</option>
			</td> 
		</tr>
		<tr>
			<td><input type="button" id="submitBtn" value="Submit"></td>
		</tr>
	
</body>
<script>

$(document).ready(function(){
	$("#newTaskName").keyup(function(){
		var temp=$("#newTaskName").val();
		if(!temp.trim())
		{
			$("#modifyTask").removeAttr('disabled');
		}
		else
		{
			$("#modifyTask").attr('disabled','disabled');
		}
	});
	
	$("#submitBtn").click(function(){
		var temp=$("#newTaskName").val();
		if(!temp.trim())
		{
			$("#modifyTask").removeAttr('disabled');
		}
		else
		{
			var taskName = temp;
			var parentTaskID = $("#parentTaskID").val();
			$("#info").text(parentTaskID);
		}
	});
});

</script>
</html>