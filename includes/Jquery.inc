<?php
echo '	<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">';
echo '	<script src="//code.jquery.com/jquery-1.10.2.js"></script> ';
echo '	<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script> ';
//echo '	<link rel="stylesheet" href="/resources/demos/style.css">';
echo '	<link href="/jquery-ui-1.11.2.custom/jquery-ui.css" rel="stylesheet">';
echo '	<script>';
echo '	$(function() {
			    $( "#datepicker" ).datepicker({
			      showButtonPanel: true,
				  changeMonth: true,
      			  changeYear: true,
				  showOn: "button",
			      buttonImage: "images/calendar.gif",
			      buttonImageOnly: true,
			      buttonText: "Select date",
			 	  showWeek: true,
			      firstDay: 1,
				  dateFormat: "yy-mm-dd",
				  defaultDate:  "Now"
			    } );
			  
			});
			</script>';
echo '	<script>
		$(function() {
			    $( "#datepicker2" ).datepicker({
			      showButtonPanel: true,
				  changeMonth: true,
      			  changeYear: true,
				  showOn: "button",
			      buttonImage: "images/calendar.gif",
			      buttonImageOnly: true,
			      buttonText: "Select date",
			 	  showWeek: true,
			      firstDay: 1,
				  dateFormat: "yy-mm-dd",
				  defaultDate:  "Now"
			    } );
			
			});
			</script>';
echo '	  <script>
		  $(function() {
		    $( "#dialog" ).dialog();
		  });
		  </script>';
?>