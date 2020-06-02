<h3>Threshold Testing</h3>
<form action='batch.php' method="post">
Please enter the object ID to get the formated Threshold.<br>
You can enter in multiple Object Id's by seperating them with a comma.
Example: 110978977730741, 110978976472911
<br><br>
	Object Id <input type='text' size='50' name='objectId'><br>
	Debug mode <label><input type="radio" value='true' name='debugMode' id="on"> On</label>
	<label><input type="radio" value='false' checked name='debugMode' id="off"> Off</label><br>
	<input type=submit>
</form>