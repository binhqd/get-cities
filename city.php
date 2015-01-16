<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>autocomplete demo</title>
  <link rel="stylesheet" href="./styles/jquery-ui.css">
  <script src="./scripts/jquery-1.10.2.js"></script>
  <script src="./scripts/jquery-ui.js"></script>
</head>
<body>
 
<label for="autocomplete">Select a programming language: </label>
<input id="autocomplete">
 
<script>
var tags = [ "c++", "java", "php", "coldfusion", "javascript", "asp", "ruby" ];
$( "#autocomplete" ).autocomplete({
  source: function( request, response ) {
          $.ajax({
              
          });
      }
});
</script>
 
</body>
</html>