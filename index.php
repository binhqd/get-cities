<!doctype html>
<html class="no-js">
<head>
<meta charset="utf-8">
<title>City Suggestion</title>
<meta name="description" content="">
<meta name="viewport" content="width=device-width">
<link rel="shortcut icon" href="/favicon.ico">
<!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
<!-- build:css(.) styles/vendor.css -->
<!-- bower:css -->
<link rel="stylesheet" href="./styles/bootstrap/bootstrap.css" />
<!-- endbower -->
<!-- endbuild -->
<!-- build:css(.tmp) styles/main.css -->
<link rel="stylesheet" href="styles/main.css">
<!-- endbuild -->
<style>
.headrow td {
	font-weight: bold;
	font-size: 16px;
}

tr.head td {
	background: #ddd
}
</style>
</head>
<body>
	<!--[if lt IE 10]>
      <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
    <![endif]-->


	<div class="container">
		<div class="header">
			<ul class="nav nav-pills pull-right">
				<li class="active"><a href="#">Home</a></li>
				<li><a href="#">About</a></li>
				<li><a href="#">Contact</a></li>
			</ul>
			<h3 class="text-muted">City suggestion</h3>
		</div>

		<!-- $info here -->

		<form novalidate class="form-horizontal" role="form" name="userForm">
			<input ng-model="form.id" type="hidden" />
			<div class="form-group">
				<label for="txtName" class="col-sm-2 control-label">Location</label>
				<div class="col-sm-4">
					<input class="form-control" id="geocomplete" name="geocomplete"
						style="width: 300px;" type="text" placeholder="Location address" />

				</div>
			</div>
			<div class="form-group">
				<label for="txtEdition" class="col-sm-2 control-label">Country</label>
				<div class="col-sm-4">
					<input type="text" name='country' class="form-control"
						id="txtCountry" placeholder="Country" />
				</div>
			</div>

			<div class="form-group">
				<label for="txtEdition" class="col-sm-2 control-label">City</label>
				<div class="col-sm-4">
					<input type="text" name='city' class="form-control" id="txtCity"
						placeholder="City" />
				</div>
			</div>

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" class="btn btn-default btn-primary">Save</button>
					<button type="reset" class="btn btn-default">Reset</button>
				</div>
			</div>
		</form>

		<div class="footer">
			<p>
				<a
					href='https://www.linkedin.com/profile/view?id=183138464&trk=nav_responsive_tab_profile'>Binh
					Quan</a>
			</p>
		</div>

	</div>


	<!-- build:js(.) scripts/vendor.js -->
	<!-- bower:js -->
	<script src="./scripts/jquery/jquery.js"></script>
	<!-- endbower -->
	<!-- endbuild -->

	<!-- Google Analytics: change UA-XXXXX-X to be your site's ID. -->

	<!-- build:js(.) scripts/plugins.js -->
	<script src="./scripts/bootstrap/affix.js"></script>
	<script src="./scripts/bootstrap/alert.js"></script>
	<script src="./scripts/bootstrap/dropdown.js"></script>
	<script src="./scripts/bootstrap/tooltip.js"></script>
	<script src="./scripts/bootstrap/modal.js"></script>
	<script src="./scripts/bootstrap/transition.js"></script>
	<script src="./scripts/bootstrap/button.js"></script>
	<script src="./scripts/bootstrap/popover.js"></script>
	<script src="./scripts/bootstrap/carousel.js"></script>
	<script src="./scripts/bootstrap/scrollspy.js"></script>
	<script src="./scripts/bootstrap/collapse.js"></script>
	<script src="./scripts/bootstrap/tab.js"></script>

	<!-- endbuild -->

	<!-- build:js({app,.tmp}) scripts/main.js -->
	<script src="./scripts/main.js"></script>
	<script
		src="http://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places"></script>
	<script src="./scripts/jquery.geocomplete.min.js"></script>
	<!-- endbuild -->

	<script>
	$(document).ready(function() {
	    $("#geocomplete").geocomplete()
	    .bind("geocode:result", function(event, result){
		    //console.log(result);
		    //console.log($("#geocomplete").val());
    		setWaiting();
            $.ajax({
        	   url : './request.php?q=' + encodeURIComponent($('#geocomplete').val()),
        	   dataType : 'json',
        	   success : function(res) {
        	       $('#txtCountry').val(res.country.name);
        	       
        	       if (!!res.city) {
        	           $('#txtCity').val(res.city.name);
        	       } else if (!!res.province) {
        	           $('#txtCity').val(res.province.name);
        	       } else if (!!res.community) {
        	           $('#txtCity').val(res.community.name);
        	       } else if (!!res.town) {
        	           $('#txtCity').val(res.town.name);
        	       } else if (!!res.district) {
        	           $('#txtCity').val(res.district.name);
        	       }
        	       
        	       setEnable();
               }, 
               error : function(xhr) {
        	       console.log(xhr);
               }
	        });
         });

	    function setWaiting() {
		    $('#txtCountry').attr('disabled', 'disabled').val('Waiting ... ');
		    $('#txtCity').attr('disabled', 'disabled').val('Waiting ... ');
	    }

	    function setEnable() {
		    $('#txtCountry').removeAttr('disabled');
		    $('#txtCity').removeAttr('disabled');
	    }

	});
	</script>
</body>
</html>
