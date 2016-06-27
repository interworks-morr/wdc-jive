/**
 * Retrieves Jive community data for a given site 
 * and returns it (or just the schema) in the format needed by
 * a Tableau Web Data Connector
 * 
 * @author Matthew Orr <matthew.orr@interworks.com>
 */

$(function() {
	
	//event handlers
	function submitButtonOnClick()
	{
		try
		{
			tableau.connectionName = "Jive: " + $('#site').val();
			
			//store the form data because the submit causes it to disappear
			var formData = {"site":$('#site').val(),
			                "place":$("#available_places_list").val()}
			tableau.connectionData = JSON.stringify(formData);
			tableau.username = $("#username").val();
			tableau.password = $("#password").val();
			tableau.submit();
		}
		catch(error)
		{
			alert("There was a problem using the Tableau web data connector javascript library. " + error);
		}
	}
	
	//tableau web data connector functionality
	try
	{
		var myConnector = tableau.makeConnector();
		myConnector.getSchema = function (schemaCallback)
		{
			var formData = JSON.parse(tableau.connectionData);
			var url = "fetch_data.php";
			var args = {'type': "schema",
			            'site': formData["site"],
			            'username': tableau.username,
			            'password': tableau.password};
			$.post(url,args,function (data)
			{
				data = JSON.parse(data);
				schemaCallback(data);
			});
		};
		
		myConnector.getData = function(table, doneCallback)
		{
			var formData = JSON.parse(tableau.connectionData);
			var url = "fetch_data.php";
			var args = {'type': "data",
			            'site': formData["site"],
			            'username': tableau.username,
			            'password': tableau.password,
			            'place': formData["place"],
			            'table': table.tableInfo.id}
			$.post(url,args,function (data)
			{
				data = JSON.parse(data);
				table.appendRows(data);
				doneCallback();
			});
		};
		tableau.registerConnector(myConnector);
	}
	catch(error)
	{
		alert("There was a problem loading the Tableau web data connector javascript library.");
	}
	
	
	//onload functionality
	function wdcInitialize()
	{
		//show/hide warning message
		$('#tableau-warning-msg').hide();
		if (typeof tableauVersionBootstrap  == 'undefined' || !tableauVersionBootstrap)
		{
			$('#tableau-warning-msg').show();
		}
		
		//set up event handler for submit button
		$("#submitButton").click(submitButtonOnClick);
	}
	$(document).ready(wdcInitialize);
});
