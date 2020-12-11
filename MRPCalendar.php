<?php
/*  
 SP 25-03-2013 Agregue campos de horai y horaf. Modifique proceso de crear calendario y el de actualizaciones.
 script
 alter table mrpcalendar
 add column horai int(6),
 add column horaf int(6)
 
 
 *desarrollo- 19/FEBRERO/2012 - Cambie las funciones de PHP de diferencia de fechas por funciones de MySQL */

$PageSecurity=9;

include('includes/session.inc');
$title = _('MRP Calendar');
include('includes/header.inc');
$funcion=142;
include('includes/SecurityFunctions.inc');

if (isset($_POST['submit'])) {
    submit($db,$ChangeDate);
} elseif (isset($_POST['update'])) { 
    update($db);
} elseif (isset($_POST['listall'])) {
    listall($db);
} elseif($_GET['update']==1) 
	displayupdate($db,$_GET['calendardate']);
else {
    display($db,$ChangeDate);
}


function submit(&$db,&$ChangeDate)  //####SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
{
	/* OBTENGO FECHAS*/
	
	//initialize no input errors
	$InputError = 0;
	
	if (isset($_POST['FromYear'])) {
		$FromYear=$_POST['FromYear'];
	} else {
		$FromYear=date('Y');
	}

	if (isset($_POST['FromMes'])) {
		$FromMes=$_POST['FromMes'];
	} else {
		$FromMes=date('m');
	}
	    
	if (isset($_POST['FromDia'])) {
		$FromDia=$_POST['FromDia'];
	} else {
		$FromDia=date('d');
	}
    
	if (isset($_POST['ToYear'])) {
		$ToYear=$_POST['ToYear'];
	} else {
		$ToYear=date('Y');
	}
    
	if (isset($_POST['ToMes'])) {
		$ToMes=$_POST['ToMes'];
	} else {
		$ToMes=date('m');
	}
	
	if (isset($_POST['ToDia'])) {
		$ToDia=$_POST['ToDia'];
	} else {
		$ToDia=date('d');
	}
	    
	$fechaini= rtrim($FromYear).'-'.rtrim($FromMes).'-'.rtrim($FromDia);
	$fechafin= rtrim($ToYear). '-'.rtrim($ToMes).'-'.rtrim($ToDia);
	 
	$fechainic=mktime(0,0,0,rtrim($FromMes),rtrim($FromDia),rtrim($FromYear));
	$fechafinc=mktime(23,59,59,rtrim($ToMes),rtrim($ToDia),rtrim($ToYear));
	 
	$fechaini= rtrim($FromYear).'-'.add_ceros(rtrim($FromMes),2).'-'.add_ceros(rtrim($FromDia),2);
	$fechafin= rtrim($ToYear).'-'.add_ceros(rtrim($ToMes),2).'-'.add_ceros(rtrim($ToDia),1) . ' 23:59:59';
	
	$horai = $_POST['horai'];
	$horaf = $_POST['horaf'];
	
	 
	$InputError = 0;
	if ($fechainic > $fechafinc){
		$InputError = 1;
		prnMsg(_('La fecha inicial no puede ser mayor a la fecha final! Por favor seleccionar nuevamente los periodos de fecha'),'error');
	} else {
		$InputError = 0;
	}
	
// Use FormatDateForSQL to put the entered dates into right format for sql
// Use ConvertSQLDate to put sql formatted dates into right format for functions such as
// DateDiff and DateAdd
	$sql = "Select DATEDIFF('".$fechafin."','".$fechaini."') as dias";
	$ErrMsg = _('The SQL failed with the message');
	$result = DB_query($sql,$db,$ErrMsg);
	if ($myrow = DB_fetch_array($result)) {
		$datediff = $myrow['dias'];
	} else {
		$datediff = 0;
	}
		
	if ($datediff < 1) {
		$InputError = 1;
		prnMsg(_('La Fecha final debe ser mayor a la Fecha de inicio'),'error');

	}

	if ($InputError == 1) {
		display($db,$ChangeDate);
		return;     
	}
     
	$sql = 'DROP TABLE IF EXISTS mrpcalendar';
	$sql = "Delete FROM mrpcalendar WHERE calendardate between '$fechaini' and '$fechafin'";
	$result = DB_query($sql,$db);
	
	$sql = 'CREATE TABLE mrpcalendar (
				calendardate date NOT NULL,
				daynumber int(6) NOT NULL,
				manufacturingflag smallint(6) NOT NULL default "1",
				horai int(6),
				horaf int(6),
				INDEX (daynumber),
				PRIMARY KEY (calendardate))';
	$ErrMsg = _('The SQL to to create passbom failed with the message');
	//$result = DB_query($sql,$db,$ErrMsg);
	
	$i = 0;
	
	// $daystext used so can get text of day based on the value get from DayOfWeekFromSQLDate of
	// the calendar date. See if that text is in the ExcludeDays array
	$daysText = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
	$ExcludeDays = array($_POST['Sunday'],$_POST['Monday'],$_POST['Tuesday'],$_POST['Wednesday'],
						 $_POST['Thursday'],$_POST['Friday'],$_POST['Saturday']);
	
	$caldate = $fechaini;
	for ($i = 0; $i <= $datediff; $i++) {
		//$dateadd = FormatDateForSQL(DateAdd($caldate,"d",$i));
		
		$sql = "Select DATE_ADD('".$caldate."',INTERVAL ".$i." DAY) as nuevafecha";
		$ErrMsg = _('The SQL failed with the message');
		$result = DB_query($sql,$db,$ErrMsg);
		if ($myrow = DB_fetch_array($result)) {
			$dateadd = $myrow['nuevafecha'];
		} else {
			$dateadd = $caldate;
		}
		 
		 // If the check box for the calendar date's day of week was clicked, set the manufacturing flag to 0
		 $dayofweek = DayOfWeekFromSQLDate($dateadd);
		 $manuflag = 1;
		 foreach ($ExcludeDays as $exday) {
			 if ($exday == $daysText[$dayofweek]) {
				 $manuflag = 0;
			 }
		 }
		 
		 $sql = "INSERT INTO mrpcalendar (
					calendardate,
					daynumber,
					manufacturingflag,
					horai,
					horaf)
				 VALUES ('$dateadd',
						'1',
						'$manuflag',
		 				'$horai',
		 				'$horaf'
		 				)";
		$result = DB_query($sql,$db,$ErrMsg);
	}
	
	// Update daynumber. Set it so non-manufacturing days will have the same daynumber as a valid
	// manufacturing day that precedes it. That way can read the table by the non-manufacturing day,
	// subtract the leadtime from the daynumber, and find the valid manufacturing day with that daynumber.
	$daynumber = 1;
	$sql = 'SELECT * FROM mrpcalendar ORDER BY calendardate';
	$result = DB_query($sql,$db,$ErrMsg);
	while ($myrow = DB_fetch_array($result)) {
		   if ($myrow['manufacturingflag'] == "1") {
			   $daynumber++;
		   }
		   $caldate = $myrow['calendardate'];
		   $sql = "UPDATE mrpcalendar SET daynumber = '$daynumber'
					WHERE calendardate = '$caldate'";
		   $resultupdate = DB_query($sql,$db,$ErrMsg);
	}
	prnMsg(_("The MRP Calendar has been created"),'succes');
	display($db,$ChangeDate);

} // End of function submit()


function update(&$db){
// Change manufacturing flag for a date. The value "1" means the date is a manufacturing date.
// After change the flag, re-calculate the daynumber for all dates.
	
	$Chgfecha = $_POST['fecha'];
	$newmanufacturingflag = $_POST['chkmanufactured'];
	$horai = $_POST['horai'];
	$horaf = $_POST['horaf'];
	
	$InputError = 0;
	$caldate = $Chgfecha;
	
	$sql="SELECT COUNT(*) FROM mrpcalendar 
	      WHERE calendardate='$caldate'
	      GROUP BY calendardate";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0] < 1)  {
		$InputError = 1;
		prnMsg(_('Invalid Change Date').$caldate,'error');
	}
	
	if ($InputError == 1) {
		display($db,$caldate);
		return;     
	}
	
	if ($newmanufacturingflag=="on")
		$newmanufacturingflag=1;
	else
		$newmanufacturingflag=0;
	
	$sql = "UPDATE mrpcalendar SET manufacturingflag = '$newmanufacturingflag',
									horai = $horai,
									horaf = $horaf
				WHERE calendardate = '$caldate'";
	$resultupdate = DB_query($sql,$db,$ErrMsg);
	prnMsg(_("The MRP calendar record for $caldate has been updated"),'succes');
	unset ($caldate);
	display($db,$caldate);
	
	// Have to update daynumber any time change a date from or to a manufacturing date
	// Update daynumber. Set it so non-manufacturing days will have the same daynumber as a valid
	// manufacturing day that precedes it. That way can read the table by the non-manufacturing day,
	// subtract the leadtime from the daynumber, and find the valid manufacturing day with that daynumber.
	$daynumber = 1;
	$sql = 'SELECT * FROM mrpcalendar ORDER BY calendardate';
	$result = DB_query($sql,$db,$ErrMsg);
	while ($myrow = DB_fetch_array($result)) {
		   if ($myrow['manufacturingflag'] == "1") {
			   $daynumber++;
		   }
		   $caldate = $myrow['calendardate'];
		   $sql = "UPDATE mrpcalendar SET daynumber = '$daynumber'
					WHERE calendardate = '$caldate'";
		   $resultupdate = DB_query($sql,$db,$ErrMsg);
	} // End of while

} // End of function update()


function listall(&$db)  //####LISTALL_LISTALL_LISTALL_LISTALL_LISTALL_LISTALL_LISTALL_####
{
// List all records in date range
    /* OBTENGO FECHAS*/
	
	//initialize no input errors
	$InputError = 0;
	
	if (isset($_POST['FromYear'])) {
		$FromYear=$_POST['FromYear'];
	} else {
		$FromYear=date('Y');
	}

	if (isset($_POST['FromMes'])) {
		$FromMes=$_POST['FromMes'];
	} else {
		$FromMes=date('m');
	}
	    
	if (isset($_POST['FromDia'])) {
		$FromDia=$_POST['FromDia'];
	} else {
		$FromDia=date('d');
	}
    
	if (isset($_POST['ToYear'])) {
		$ToYear=$_POST['ToYear'];
	} else {
		$ToYear=date('Y');
	}
    
	if (isset($_POST['ToMes'])) {
		$ToMes=$_POST['ToMes'];
	} else {
		$ToMes=date('m');
	}
	
	if (isset($_POST['ToDia'])) {
		$ToDia=$_POST['ToDia'];
	} else {
		$ToDia=date('d');
	}
	    
	$fechaini= rtrim($FromYear).'-'.rtrim($FromMes).'-'.rtrim($FromDia);
	$fechafin= rtrim($ToYear). '-'.rtrim($ToMes).'-'.rtrim($ToDia);
	 
	$fechainic=mktime(0,0,0,rtrim($FromMes),rtrim($FromDia),rtrim($FromYear));
	$fechafinc=mktime(23,59,59,rtrim($ToMes),rtrim($ToDia),rtrim($ToYear));
	 
	$fechaini= rtrim($FromYear).'-'.add_ceros(rtrim($FromMes),2).'-'.add_ceros(rtrim($FromDia),2);
	$fechafin= rtrim($ToYear).'-'.add_ceros(rtrim($ToMes),2).'-'.add_ceros(rtrim($ToDia),1) . ' 23:59:59';
	 
	$InputError = 0;
	if ($fechainic > $fechafinc){
		$InputError = 1;
		prnMsg(_('La fecha inicial no puede ser mayor a la fecha final! Por favor seleccionar nuevamente los periodos de fecha'),'error');
	} else {
		$InputError = 0;
	}
    
	$sql = "SELECT calendardate,
	               daynumber,
	               manufacturingflag,
	               DAYNAME(calendardate) as dayname,
	               horai,horaf
		FROM mrpcalendar
		WHERE calendardate >='$fechaini'
		  AND calendardate <='$fechafin'
		Order By calendardate";

	$ErrMsg = _('The SQL to find the parts selected failed with the message');
	$result = DB_query($sql,$db,$ErrMsg);
		
	echo "</br><table border=1>
		<tr BGCOLOR =#800000>
		    <th>" . _('Fecha') . "</th>
			<th>" . _('Dia Semana') . "</th>
			<th>" . _('Manufactura') . "</th>
			<th>" . _('Hora Inicio') . "</th>
			<th>" . _('Hora fin') . "</th>				
			<th>&nbsp;</th>
					
		</tr></font>";
	
	$ctr = 0;
	while ($myrow = DB_fetch_array($result)) {
		
		if ($myrow['manufacturingflag'] == 0) {
			$flag = _('No');
			printf("<tr STYLE='background-color:lightgray'><td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>	
				<td>%s</td>	
				<td>%s</td>		
				</tr>",
				ConvertSQLDate($myrow[0]),
				_($myrow[3]),
				$flag,
				'',
				'',
			    "<a href='".$_SERVER['PHP_SELF']."?update=1&calendardate=".$myrow[0]."'>editar</a>");
		} else {
			$flag = _('Yes');
			printf("<tr><td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>	
				<td>%s</td>	
				<td>%s</td>		
				</tr>",
				ConvertSQLDate($myrow[0]),
				_($myrow[3]),
				$flag,
				$myrow[4],
				$myrow[5],
			"<a href='".$_SERVER['PHP_SELF']."?update=1&calendardate=".$myrow[0]."'>editar</a>");
		}
		
	} //END WHILE LIST LOOP
	
	echo '</table>';
	echo '</br></br>';
	unset ($ChangeDate);
	display($db,$ChangeDate);



} // End of function listall()

function displayupdate(&$db, $fecha) {
	 
	$sql = "SELECT daynumber,
	manufacturingflag,
	horai,horaf
	FROM mrpcalendar
	WHERE calendardate ='$fecha'
	";

	$ErrMsg = _('The SQL to find the parts selected failed with the message');
	$result = DB_query($sql,$db,$ErrMsg);

	echo "<form action=" . $_SERVER['PHP_SELF'] . "?" . SID ." method=post></br></br>";
	
	echo "</br><table border=0>
		<tr BGCOLOR =#800000>
		    <th colspan=2 style='text-align:center'>" . _('Editando Fecha ') .$fecha. "</th>
			
		</tr>";

	$ctr = 0;
	if ($myrow = DB_fetch_array($result)) {
		$chk="checked";
		if ($myrow['manufacturingflag'] == 0)
			$chk="";

		echo "<tr>
			<td>Manufactura</td>
			<td><input type='checkbox' $chk name='chkmanufactured'></td>
			</tr>	";

		echo '<tr><td>Hora Inicial</td><td><select name=horai>';
		
    		for($i=1; $i<=23; $i++){
    			if ($myrow['horai'] == $i)	
    				echo '<option selected value='.$i.'>'.$i.'</option>';
    			else
    				echo '<option value='.$i.'>'.$i.'</option>';
    		}
		
	    echo '	</select></td></tr>
			    <tr><td>Hora Final</td><td><select name=horaf>';
			
				    for($i=1; $i<=23; $i++){
				    	if ($myrow['horaf'] == $i)	
	    					echo '<option selected value='.$i.'>'.$i.'</option>';
				    	else 
				    		echo '<option value='.$i.'>'.$i.'</option>';
				    }
				 
		
	    echo '	</select></td>	</tr></table>';

    	echo "</br></br><div class='centre'><input type='submit' name='update' value='" . _('Actualizar') . "'>
    										<input type='hidden' name='fecha' value='$fecha'>
    						</div>";
    
			
	} //if
	
	unset ($_GET['update']);
	unset ($_GET['calendardate']);
	
	echo "</form>";

} // End of function displayupdate

function display(&$db,&$ChangeDate)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called, and is also invoked at the end of all of the other functions.

	/* OBTENGO FECHAS*/
	
	if (isset($_POST['FromYear'])) {
		$FromYear=$_POST['FromYear'];
	} else {
		$FromYear=date('Y');
	}

	if (isset($_POST['FromMes'])) {
		$FromMes=$_POST['FromMes'];
	} else {
		$FromMes=date('m');
	}
	    
	if (isset($_POST['FromDia'])) {
		$FromDia=$_POST['FromDia'];
	} else {
		$FromDia=date('d');
	}
    
	if (isset($_POST['ToYear'])) {
		$ToYear=$_POST['ToYear'];
	} else {
		$ToYear=date('Y');
	}
    
	if (isset($_POST['ToMes'])) {
		$ToMes=$_POST['ToMes'];
	} else {
		$ToMes=date('m');
	}
	
	if (isset($_POST['ToDia'])) {
		$ToDia=$_POST['ToDia'];
	} else {
		$ToDia=date('d');
	}
	    
	$fechaini= rtrim($FromYear).'-'.rtrim($FromMes).'-'.rtrim($FromDia);
	$fechafin= rtrim($ToYear). '-'.rtrim($ToMes).'-'.rtrim($ToDia);
	 
	$fechainic=mktime(0,0,0,rtrim($FromMes),rtrim($FromDia),rtrim($FromYear));
	$fechafinc=mktime(23,59,59,rtrim($ToMes),rtrim($ToDia),rtrim($ToYear));
	 
	$fechaini= rtrim($FromYear).'-'.add_ceros(rtrim($FromMes),2).'-'.add_ceros(rtrim($FromDia),2);
	$fechafin= rtrim($ToYear).'-'.add_ceros(rtrim($ToMes),2).'-'.add_ceros(rtrim($ToDia),1) . ' 23:59:59';
	 
	$InputError = 0;
	if ($fechainic > $fechafinc){
		$InputError = 1;
		prnMsg(_('La fecha inicial no puede ser mayor a la fecha final! Por favor seleccionar nuevamente los periodos de fecha'),'error');
	} else {
		$InputError = 0;
	}
	
	if (isset($_POST['ChgFromYear'])) {
		$ChgFromYear=$_POST['ChgFromYear'];
	} else {
		$ChgFromYear=date('Y');
	}

	if (isset($_POST['ChgFromMes'])) {
		$ChgFromMes=$_POST['ChgFromMes'];
	} else {
		$ChgFromMes=date('m');
	}
	
	if (isset($_POST['ChgFromDia'])) {
		$ChgFromDia=$_POST['ChgFromDia'];
	} else {
		$ChgFromDia=date('d');
	}
	
	$Chgfecha= rtrim($ChgFromYear).'-'.rtrim($ChgFromMes).'-'.rtrim($ChgFromDia);
	
	echo "<form action=" . $_SERVER['PHP_SELF'] . "?" . SID ." method=post></br></br>";

	echo '<table>';

	/* SELECCIONA EL RANGO DE FECHAS */

	       echo '<tr>';
	       echo '<td colspan=2>';
	       echo '&nbsp;</td></tr>';

	       echo '<tr>';
	       echo '<td colspan=2>
	                <table>
	    		     <tr>';
				    echo '<td>' . _('Desde:') . '</td>
				    <td><select Name="FromDia">';
					 $sql = "SELECT * FROM cat_Days";
					 $dias = DB_query($sql,$db);
					 while ($myrowdia=DB_fetch_array($dias,$db)){
					     $diabase=$myrowdia['DiaId'];
					     if (rtrim(intval($FromDia))==rtrim(intval($diabase))){ 
						 echo '<option  VALUE="' . $myrowdia['DiaId'] .  '  " selected>' .$myrowdia['Dia'];
					     }else{
						 echo '<option  VALUE="' . $myrowdia['DiaId'] .  '" >' .$myrowdia['Dia'];
					     }
					 }
					 
				    echo'</td>'; 
				    echo '<td><select Name="FromMes">';
					      $sql = "SELECT * FROM cat_Months";
					      $Meses = DB_query($sql,$db);
					      while ($myrowMes=DB_fetch_array($Meses,$db)){
						  $Mesbase=$myrowMes['u_mes'];
						  if (rtrim(intval($FromMes))==rtrim(intval($Mesbase))){ 
						      echo '<option  VALUE="' . $myrowMes['u_mes'] .  '  " selected>' .$myrowMes['mes'];
						  }else{
						      echo '<option  VALUE="' . $myrowMes['u_mes'] .  '" >' .$myrowMes['mes'];
						  }
					      }
					      
					      echo '</select>';
					      echo '&nbsp;<input name="FromYear" type="text" size="4" value='.$FromYear.'>';
						      
				      echo '</td>
				    <td>
					 &nbsp;
				    </td>
				    <td>' . _('Hasta:') . '</td>';
				    echo'<td><select Name="ToDia">';
					      $sql = "SELECT * FROM cat_Days";
					      $Todias = DB_query($sql,$db);
					      while ($myrowTodia=DB_fetch_array($Todias,$db)){
						  $Todiabase=$myrowTodia['DiaId'];
						  if (rtrim(intval($ToDia))==rtrim(intval($Todiabase))){ 
						      echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '  " selected>' .$myrowTodia['Dia'];
						  }else{
						      echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '" >' .$myrowTodia['Dia'];
						  }
					      }
				    echo '</td>';
				    echo'<td>';
					 echo'<select Name="ToMes">';
					 $sql = "SELECT * FROM cat_Months";
					 $ToMeses = DB_query($sql,$db);
					 while ($myrowToMes=DB_fetch_array($ToMeses,$db)){
					     $ToMesbase=$myrowToMes['u_mes'];
					     if (rtrim(intval($ToMes))==rtrim(intval($ToMesbase))){ 
						 echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '  " selected>' .$myrowToMes['mes'];
					     }else{
						 echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" >' .$myrowToMes['mes'];
					     }
					 }
					 echo '</select>';
					 echo '&nbsp;<input name="ToYear" type="text" size="4" value='.$ToYear.'>';
					 
				    echo'</td>';
				echo '</tr>';
			echo '<table>';			
		   echo '</td>';	
	       echo '</tr>';


	       echo '<tr>';
	       echo '<td colspan=2>';
	       echo '&nbsp;</td></tr>';
	       
    echo '	<tr><td colspan=2><b>'._('A Excepcion de los dias:').'</b></td>
    			<td><b>Hora Inicial</b>&nbsp;<select name=horai>';
    
    		for($i=1; $i<=23; $i++){
    			echo '<option value='.$i.'>'.$i.'</option>';
    		}
    
    echo '	</select></td>
    			<td><b>Hora Final</b>&nbsp;<select name=horaf>';
    
			    for($i=1; $i<=23; $i++){
			    	echo '<option value='.$i.'>'.$i.'</option>';
			    }
			    
    
    echo '	</select></td>	</tr>
     
        <td>' . _('Lunes') . ":</td>
	    <td><input type='checkbox' name='Monday' value='Monday'></td>
	</tr>
     <tr>
        <td>" . _('Martes') . ":</td>
	    <td><input type='checkbox' name='Tuesday' value='Tuesday'></td>
	</tr>
     <tr>
        <td>" . _('Miercoles') . ":</td>
	    <td><input type='checkbox' name='Wednesday' value='Wednesday'></td>
	</tr>
     <tr>
        <td>" . _('Jueves') . ":</td>
	    <td><input type='checkbox' name='Thursday' value='Thursday'></td>
	</tr>
     <tr>
        <td>" . _('Viernes') . ":</td>
	    <td><input type='checkbox' name='Friday' value='Friday'></td>
	</tr>
	<tr>
        <td>" . _('Sabado') . ":</td>
	    <td><input type='checkbox' name='Saturday' value='Saturday'></td>
	</tr>
     <tr>
        <td>" . _('Domingo') . ":</td>
	    <td><input type='checkbox' name='Sunday' value='Sunday'></td>
	</tr>
     <tr>
	<tr></tr><tr></tr>
	<tr>
	    <td></td>
	</tr>
	<tr>
	    <td></td>
	</tr>
	<tr>
	    <td></td>
	    <td><input type='submit' name='submit' value='" . _('Crear Calendario') . "'></td>
	    <td></td>
	    <td><input type='submit' name='listall' value='" . _('Listar Rango de Fechas') . "'></td>
	</tr>
	</table>
	</br>";

echo '</br></br><hr/>';
echo '<table>';
/*
echo '<tr>';
	       echo '<td colspan=2>
	                <table>
	    		     <tr>';
				    echo '<td>' . _('Desde:') . '</td>
				    <td><select Name="ChgFromDia">';
					 $sql = "SELECT * FROM cat_Days";
					 $dias = DB_query($sql,$db);
					 while ($myrowdia=DB_fetch_array($dias,$db)){
					     $diabase=$myrowdia['DiaId'];
					     if (rtrim(intval($ChgFromDia))==rtrim(intval($diabase))){ 
						 echo '<option  VALUE="' . $myrowdia['DiaId'] .  '  " selected>' .$myrowdia['Dia'];
					     }else{
						 echo '<option  VALUE="' . $myrowdia['DiaId'] .  '" >' .$myrowdia['Dia'];
					     }
					 }
					 
				    echo'</td>'; 
				    echo '<td><select Name="ChgFromMes">';
					      $sql = "SELECT * FROM cat_Months";
					      $Meses = DB_query($sql,$db);
					      while ($myrowMes=DB_fetch_array($Meses,$db)){
						  $Mesbase=$myrowMes['u_mes'];
						  if (rtrim(intval($ChgFromMes))==rtrim(intval($Mesbase))){ 
						      echo '<option  VALUE="' . $myrowMes['u_mes'] .  '  " selected>' .$myrowMes['mes'];
						  }else{
						      echo '<option  VALUE="' . $myrowMes['u_mes'] .  '" >' .$myrowMes['mes'];
						  }
					      }
					      
					      echo '</select>';
					      echo '&nbsp;<input name="ChgFromYear" type="text" size="4" value='.$ChgFromYear.'>';
						      
				      echo '</td>
				    <td>
					 &nbsp;
				    </td>
				</tr>';
	       
echo '</table>';
echo "</br></br><div class='centre'><input type='submit' name='update' value='" . _('Actualizar') . "'></div>";
  */
 
echo '</form>';

} // End of function display()


include('includes/footer.inc');
?>
