# StreamFrame

There are 2 files of PHP, taskLegacy.php as the main user interface and function.php as the backend process and 1 css for the style.
I am using XAMPP and phpmyadmin as the DB (localhost, username : "root", password :"", DB :"test") you may see in the function.php at connectDB() section.

Guide to use the web application
How to create new Task:
 - Fill the task name in "New Task Name" (when you type in here, the dropdown of "Modify Task" is disabled)
 - If you want to create task without assign it to any task, please click "Submit" button
 - If you want it to be under certain task, you may pick any task in "Parent Task ID" dropdown (Pick "Please Select" meaning create independent/non parent task), click "Submit" button

How to modify task:
 - Ensure the input field of "New Task Name" is empty
 - Pick one of the value in "Modify Task"
 - Pick the desired parent in "Parent Task ID" dropdown (Pick "Please Select" meaning modify the task into independent/non parent task)
 - click "Submit" button

How to rename the task name:
 - You may directly change the name by clicking the name in "List Of Tasks" section. (when you hover, the pointer will be changed to "I")
 - After change you have to press ENTER key to reflect the changes.

This application created with assumptions below:
 - Parent Task cannot change its status, hence there is no checkbox in the Parent Task level.
   Reason : if able to change the status, what happened to its children if the Status of the parent is COMPLETED and unmark into DONE? which children need to update into IN PROGRESS?
 - Only child/independent task can change the status
 - When you filtered to certain status, you still can see and unmark/mark the other's status.

Thank You!
