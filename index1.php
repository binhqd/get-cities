<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Geocomplete</title>
<script src="http://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places"></script>
<script src="js/jquery.min.js"></script>
<script src="js/jquery.geocomplete.min.js"></script>
</head>
<body>
	<form action="request.php" method="POST">
		<input id="geocomplete" name="geocomplete" style="width: 300px;" type="text" placeholder="Location address" /> 
		<input type="button" name="request_geo" id='btnGetInfo' value="Request Geocomplete">
		
		<br/>
		<input type='text' name=''
	</form>
</body>
<script>
		$(function(){
	        $("#geocomplete").geocomplete();

	        $('#btnGetInfo').click(function() {
	            $.ajax({
	        	  
		        });
		    });
      	});
      	
	</script>
</html>