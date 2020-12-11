<?php

/* 
 
 
 CREATE TABLE `paymenttermbynumber` (
	`termsindicator` varchar(20),
	`daysbeforedue` int,
	`numberpay` int
);

ALTER TABLE `paymenttermbynumber` 
ADD COLUMN `daygrace` int AFTER `numberpay`, 
ADD COLUMN `cashdiscount` float AFTER `daygrace`;

ALTER TABLE `paymentterms` 
ADD COLUMN `cashdiscount` float DEFAULT '0' AFTER `fixdate`, 
ADD COLUMN `daygrace` int DEFAULT '0' AFTER `cashdiscount`;

 * 
 * 
 *desarrollo- 25/ABR/2012 - Agregue FIXDATE a 
 * captura para pago a una fecha fija para temporada de Librerias Hifdalgo */

/*cambios
1.- se agrego el include('includes/SecurityFunctions.inc');*/

/*
$Revision: 4.0
$Modificado por: Desarrollador
$FechaModificacion: 24 Nov 2009
$CAMBIOS: 1.- Agrego numero de pagos y si se generan pagares
	  2.- Traduccion a Espaï¿½ol
$Modificado por: Desarrollador
$FechaModificacion: 26 Ene 2010
$CAMBIOS: 1.- Agregue campo de automatic para generacion de pagares por default o con edicion
	  
*/
/*
* AHA
* 7-Nov-2014
* Cambio de ingles a espa–ol los mensajes de usuario de tipo error,info,warning, y success.
*/

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Mantenimiento de Metodos de Pago');

include('includes/header.inc');
$funcion=121;
include('includes/SecurityFunctions.inc');

/* OBTENGO FECHAS*/

if (isset($_POST['FromYear'])) {
        $FromYear=$_POST['FromYear'];
}

    if (isset($_POST['FromMes'])) {
        $FromMes=$_POST['FromMes'];
    }
    
    if (isset($_POST['FromDia'])) {
        $FromDia=$_POST['FromDia'];
    }
    
     /*$fechaini= rtrim($FromYear).'-'.rtrim($FromMes).'-'.rtrim($FromDia);
     $fechainic=mktime(0,0,0,rtrim($FromMes),rtrim($FromDia),rtrim($FromYear));
     $fechaini= rtrim($FromYear).'-'.add_ceros(rtrim($FromMes),2).'-'.add_ceros(rtrim($FromDia),2);	*/
    if ($FromYear=="" or $FromYear="0000" )
    	$fechaini = "";
    else
    	$fechaini= rtrim($FromYear).'-'.add_ceros(rtrim($FromMes),2).'-'.add_ceros(rtrim($FromDia),2);

if (isset($_GET['SelectedTerms'])){
	$SelectedTerms = $_GET['SelectedTerms'];
} elseif (isset($_POST['SelectedTerms'])){
	$SelectedTerms = $_POST['SelectedTerms'];
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

if (isset($_POST['submit'])) {
	$flaginsert = 0;
	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	$i=1;

	//first off validate inputs are sensible

	if (strlen($_POST['TermsIndicator']) < 1) {
		$InputError = 1;
		prnMsg(_('El codigo del metodo de pago no puede ser vacio'),'error');
		$Errors[$i] = 'TermsIndicator';
		$i++;
	}
	if (strlen($_POST['TermsIndicator']) > 2) {
		$InputError = 1;
		prnMsg(_('El codigo del metodo de pago debe ser dos caracteres o menos de largo'),'error');
		$Errors[$i] = 'TermsIndicator';
		$i++;
	}
	if (empty($_POST['DayNumber']) OR !is_numeric($_POST['DayNumber']) OR $_POST['DayNumber'] <= 0){
		$InputError = 1;
		prnMsg( _('El numero de dias o el dia del siguiente mes debe ser numerico') ,'error');
		$Errors[$i] = 'DayNumber';
		$i++;
	}
	if (empty($_POST['Terms']) OR strlen($_POST['Terms']) > 40) {
		$InputError = 1;
		prnMsg( _('La descripcion debe ser maximo de 40 caracteres o menos de longitud') ,'error');
		$Errors[$i] = 'Terms';
		$i++;
	}

	if ($_POST['DayNumber'] > 30 AND empty($_POST['DaysOrFoll'])) {
		$InputError = 1;
		prnMsg( _('Cuando el check box no esta seleccionado para indicar el dia del siguiente mes, entonces') . ', ' . _('el dia de vencimiento no puede ser despues del dia 30') . '. ' . _('Un numero entre 1 y 30 es necesario') ,'error');
		$Errors[$i] = 'DayNumber';
		$i++;
	}
	if ($_POST['DayNumber']>999 AND !empty($_POST['DaysOrFoll'])) {
		$InputError = 1;
		prnMsg( _('Cuando el check box si esta seleccionado para indicar que el termino es un numero de dias despues de los cuales la cuenta se vence') . ', ' . _('el numero capturado no puede ser mas de 361 dias') ,'error');
		$Errors[$i] = 'DayNumber';
		$i++;
	}
	if ($_POST['numberOfPayments']<1 AND !empty($_POST['generateCreditNote'])) {
		$InputError = 1;
		prnMsg( _('Si se van a generar pagares, el numero de pagares debe ser por lo menos uno') ,'error');
		$Errors[$i] = 'numberOfPayments';
		$i++;
	}
	
	/*SelectedTerms could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
	if (isset($_POST['generateCreditNote']) and $_POST['generateCreditNote']=='on') {
		$pagaresFlag = 1;		
	} else {
		$pagaresFlag = 0;	
	}
	if (isset($_POST['generateAutomatic']) and $_POST['generateAutomatic']=='on') {
		$generateAutomatic = 1;		
	} else {
		$generateAutomatic = 0;	
	}
	
	
	if (!isset($_POST['numberOfPayments']) or $_POST['numberOfPayments']==''){
		$_POST['numberOfPayments'] = 0;		
	}
	
	$valorfecha = "'" . $fechaini ."'";
	if ($fechaini=="" )
		$valorfecha = "null";
	
	if (isset($SelectedTerms) AND $InputError !=1) {

		if($valorfecha == "null"){
			
			if (isset($_POST['DaysOrFoll']) and $_POST['DaysOrFoll']=='on') {
				$sql = "UPDATE paymentterms SET
					terms='" . $_POST['Terms'] . "',
					dayinfollowingmonth=0,
					daysbeforedue=" . $_POST['DayNumber'] . ",
					generateCreditNote=" . $pagaresFlag . ",
					numberOfPayments=" . $_POST['numberOfPayments'] . ",
					cashdiscount=" . $_POST['cashdiscount'] . ",
					daygrace=" . $_POST['daygrace'] . ",
					automatic=" . $generateAutomatic . "
				WHERE termsindicator = '" . $SelectedTerms . "'";
			} else {
				$sql = "UPDATE paymentterms SET
					terms='" . $_POST['Terms'] . "',
					dayinfollowingmonth=" . $_POST['DayNumber'] . ",
					daysbeforedue=0,
					generateCreditNote=" . $pagaresFlag . ",
					numberOfPayments=" . $_POST['numberOfPayments'] . ",
					cashdiscount=" . $_POST['cashdiscount'] . ",
					daygrace=" . $_POST['daygrace'] . ",
					automatic=" . $generateAutomatic . "
				WHERE termsindicator = '" . $SelectedTerms . "'";
			}
			
		}else{
			if (isset($_POST['DaysOrFoll']) and $_POST['DaysOrFoll']=='on') {
				$sql = "UPDATE paymentterms SET
					terms='" . $_POST['Terms'] . "',
					dayinfollowingmonth=0,
					daysbeforedue=" . $_POST['DayNumber'] . ",
					generateCreditNote=" . $pagaresFlag . ",
					numberOfPayments=" . $_POST['numberOfPayments'] . ",
					cashdiscount=" . $_POST['cashdiscount'] . ",
					daygrace=" . $_POST['daygrace'] . ",
					automatic=" . $generateAutomatic . ",
					fixdate='" . $valorfecha ."'
				WHERE termsindicator = '" . $SelectedTerms . "'";
			} else {
				$sql = "UPDATE paymentterms SET
					terms='" . $_POST['Terms'] . "',
					dayinfollowingmonth=" . $_POST['DayNumber'] . ",
					daysbeforedue=0,
					generateCreditNote=" . $pagaresFlag . ",
					numberOfPayments=" . $_POST['numberOfPayments'] . ",
					cashdiscount=" . $_POST['cashdiscount'] . ",
					daygrace=" . $_POST['daygrace'] . ",
					automatic=" . $generateAutomatic . ",
					fixdate='" . $valorfecha ."'
				WHERE termsindicator = '" . $SelectedTerms . "'";
			}
		}
		
		

		$msg = _('El registro de terminos de pago se ha actualizado') . '.';
	} else if ($InputError !=1) {
		$flaginsert = 1;
	/*Selected terms is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new payment terms form */
		if($valorfecha == "null"){
			if ($_POST['DaysOrFoll']=='on') {
				$sql = "INSERT INTO paymentterms (termsindicator,
								terms,
								daysbeforedue,
								dayinfollowingmonth,
								generateCreditNote,
								numberOfPayments,
								cashdiscount,
								daygrace,
								automatic
							)
						VALUES (
							'" . $_POST['TermsIndicator'] . "',
							'" . $_POST['Terms'] . "',
							" . $_POST['DayNumber'] . ",
							0,
							" . $pagaresFlag . ",
							" . $_POST['numberOfPayments'] . ",
							'" . $_POST['cashdiscount'] . "',
							'" . $_POST['daygrace'] . "',
							" . $generateAutomatic . "
						)";
			} else {
				$sql = "INSERT INTO paymentterms (termsindicator,
								terms,
								daysbeforedue,
								dayinfollowingmonth,
								generateCreditNote,
								numberOfPayments,
								cashdiscount,
								daygrace,
								automatic
							)
						VALUES (
							'" . $_POST['TermsIndicator'] . "',
							'" . $_POST['Terms'] . "',
							0,
							" . $_POST['DayNumber'] . ",
							" . $pagaresFlag . ",
							" . $_POST['numberOfPayments'] . ",
							'" . $_POST['cashdiscount'] . "',
							'" . $_POST['daygrace'] . "',
							" . $generateAutomatic . "
							)";
			}
				
		}else{
			if ($_POST['DaysOrFoll']=='on') {
				$sql = "INSERT INTO paymentterms (termsindicator,
								terms,
								daysbeforedue,
								dayinfollowingmonth,
								generateCreditNote,
								numberOfPayments,
								cashdiscount,
								daygrace,
								automatic,
								fixdate
							)
						VALUES (
							'" . $_POST['TermsIndicator'] . "',
							'" . $_POST['Terms'] . "',
							" . $_POST['DayNumber'] . ",
							0,
							" . $pagaresFlag . ",
							" . $_POST['numberOfPayments'] . ",
							'" . $_POST['cashdiscount'] . "',
							'" . $_POST['daygrace'] . "',
							" . $generateAutomatic . ",
							'" . $valorfecha ."'
						)";
			} else {
				$sql = "INSERT INTO paymentterms (termsindicator,
								terms,
								daysbeforedue,
								dayinfollowingmonth,
								generateCreditNote,
								numberOfPayments,
								cashdiscount,
								daygrace,
								automatic,
								fixdate
							)
						VALUES (
							'" . $_POST['TermsIndicator'] . "',
							'" . $_POST['Terms'] . "',
							0,
							" . $_POST['DayNumber'] . ",
							" . $pagaresFlag . ",
							" . $_POST['numberOfPayments'] . ",
							'" . $_POST['cashdiscount'] . "',
							'" . $_POST['daygrace'] . "',
							" . $generateAutomatic . ",
							'" . $valorfecha ."'
							)";
			}
				
		}
		//
		$msg = _('El registro de terminos de pago se ha agregado') . '.';
	}
	if ($InputError !=1){
		//run the SQL from either of the above possibilites
	//echo $sql;
		 $result = DB_query($sql,$db);
		 
		 if($flaginsert == 1){
		 	$sql = "INSERT INTO sec_paymentterms (termsindicator, 
		 										userid)
					VALUES('".$_POST['TermsIndicator']."', 
							'".$_SESSION['UserID']."')";
		 	$result = DB_query($sql, $db);
		 }
		 
		 //elimina de la tabla de vencimientos
		 $SQL="DELETE FROM paymenttermbynumber where termsindicator='". $_POST['TermsIndicator']."'";
		 $result = DB_query($SQL,$db);
		 //inserta en la tabla paymenttermbynumber
		for($i=1;$i<=$_POST['numberOfPayments'];$i++){
			$numberOfPayment=$_POST['numberOfPayment_'.$i];
			$DaysOfPayment=$_POST['DaysOfPayment_'.$i];
			$cashdiscount1=$_POST['cashdiscount_'.$i];
			$daygrace1=$_POST['daygrace_'.i];
			$SQL="INSERT INTO paymenttermbynumber(termsindicator,numberpay,daysbeforedue,cashdiscount,daygrace)
					 VALUES('".$_POST['TermsIndicator']."','".$numberOfPayment."','".$DaysOfPayment."','".$cashdiscount1."','".$daygrace1."')";
		//	ECHO '<br><pre>'.$SQL;
			$result = DB_query($SQL,$db);
		 	
		 }
		 
		prnMsg($msg,'success');
		unset($SelectedTerms);
		unset($_POST['DaysOrFoll']);
		unset($_POST['TermsIndicator']);
		unset($_POST['Terms']);
		unset($_POST['DayNumber']);
		unset($_POST['generateCreditNote']);
		unset($_POST['numberOfPayments']);
		unset($_POST['generateAutomatic']);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN DebtorsMaster

	$sql= "SELECT COUNT(*) FROM debtorsmaster WHERE debtorsmaster.paymentterms = '$SelectedTerms'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0] > 0) {
		prnMsg( _('No puedo eliminar este termino de pago porque alguna cuenta de cliente ha sido creada con este termino de pago'),'warn');
		echo '<br> ' . _('Existen') . ' ' . $myrow[0] . ' ' . _('cuentas de clientes que hacen referencia a este termino de pago');
	} else {
		$sql= "SELECT COUNT(*) FROM suppliers WHERE suppliers.paymentterms = '$SelectedTerms'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0] > 0) {
			prnMsg( _('No puedo eliminar este termino de pago porque alguna cuenta de proveedores ha sido creada referenciando este termino de pago'),'warn');
			echo '<br> ' . _('Existen') . ' ' . $myrow[0] . ' ' . _('cuentas de proveedores haciendo referencia a este termino de pago');
		} else {
			//only delete if used in neither customer or supplier accounts

			$sql="DELETE FROM paymentterms WHERE termsindicator='$SelectedTerms'";
			$result = DB_query($sql,$db);
			prnMsg( _('El registro de definicion de termino de pago ha sido eliminado') . '!','success');
		}
	}
	//end if payment terms used in customer or supplier accounts

}

if (!isset($SelectedTerms)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTerms will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of payment termss will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records */

	$sql = 'SELECT termsindicator, terms, daysbeforedue, dayinfollowingmonth,
			case when generateCreditNote=1 then "SI" else "NO" end,numberOfPayments,
			case when automatic=1 then "SI" else "NO" end,
			fixdate,daygrace,cashdiscount
		FROM paymentterms';
	$result = DB_query($sql, $db);

	echo '<table border=1>';
	echo '<tr><th>' . _('Codigo') . '</th>
		<th>' . _('Descripcion') . '</th>
		<th>' . _('Dia Siguiente Mes') . '</th>
		<th>' . _('Vence en (No. Dias)') . '</th>
		<th>' . _('Pagares') . '</th>
		<th>' . _('Automatico') . '</th>
		<th>' . _('No. Vencimientos') . '</th>
		<th>' . _('% Pronto Pago') . '</th>
		<th>' . _('Dias Gracia') . '</th>
		<th>' . _('Fecha Fija') . '</th>
		</tr>';

	while ($myrow=DB_fetch_row($result)) {

		if ($myrow[3]==0) {
			$FollMthText = _('N/A');
		} else {
			$FollMthText = $myrow[3] . _('o');
		}

		if ($myrow[2]==0) {
			$DueAfterText = _('N/A');
		} else {
			$DueAfterText = $myrow[2] . ' ' . _('dias');
		}

		printf("<tr><td>%s</td>
	        <td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td><a href=\"%s&SelectedTerms=%s\">" . _('Modificar') . "</a></td>
		<td><a href=\"%s&SelectedTerms=%s&delete=1\">" . _('Eliminar') . "</a></td>
		</tr>",
		$myrow[0],
		$myrow[1],
		$FollMthText,
		$DueAfterText,
		$myrow[4],
		$myrow[6],
		$myrow[5],
		$myrow[7],
		$myrow[8],
		$myrow[9],
		$_SERVER['PHP_SELF'] . '?' . SID,
		$myrow[0],
		$_SERVER['PHP_SELF']. '?' . SID,
		$myrow[0]);

	} //END WHILE LIST LOOP
	echo '</table><p>';
} //end of ifs and buts!

if (isset($SelectedTerms)) {
	echo '<div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '?' . SID  .'">' . _('Despliega todos los Terminos de Pago') . '</a></div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	if (isset($SelectedTerms)) {
		//editing an existing payment terms

		$sql = "SELECT termsindicator,
				terms,
				daysbeforedue,
				dayinfollowingmonth,
				generateCreditNote,
				numberOfPayments,
				automatic,
				fixdate,
				daygrace,cashdiscount
			FROM paymentterms
			WHERE termsindicator='$SelectedTerms'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['TermsIndicator'] = $myrow['termsindicator'];
		$_POST['Terms']  = $myrow['terms'];
		$DaysBeforeDue  = $myrow['daysbeforedue'];
		$DayInFollowingMonth  = $myrow['dayinfollowingmonth'];
		$_POST['generateCreditNote'] = $myrow['generateCreditNote'];
		$_POST['numberOfPayments'] = $myrow['numberOfPayments'];
		$_POST['generateAutomatic']=	$myrow['automatic'];
		$_POST['daygrace']=	$myrow['daygrace'];
		$_POST['cashdiscount']=	$myrow['cashdiscount'];
		
		$FromDia = substr($myrow['fixdate'],8,2);
		$FromMes = substr($myrow['fixdate'],5,2);
		$FromYear = substr($myrow['fixdate'],0,4);
		
		echo '<input type=hidden name="SelectedTerms" VALUE="' . $SelectedTerms . '">';
		echo '<input type=hidden name="TermsIndicator" VALUE="' . $_POST['TermsIndicator'] . '">';
		echo '<input type=hidden name="generateCreditNote" VALUE="' . $_POST['generateCreditNote'] . '">';
		echo '<input type=hidden name="numberOfPayments" VALUE="' . $_POST['numberOfPayments'] . '">';
		echo '<input type=hidden name="generateAutomatic" VALUE="' . $_POST['generateAutomatic'] . '">';
		
		echo '<table><tr><td>' . _('Codigo') . ':</td><td>';
		echo $_POST['TermsIndicator'] . '</td></tr>';

	} else { //end of if $SelectedTerms only do the else when a new record is being entered

		if (!isset($_POST['TermsIndicator'])) $_POST['TermsIndicator']='';
		if (!isset($DaysBeforeDue)) $DaysBeforeDue=0;
		//if (!isset($DayInFollowingMonth)) $DayInFollowingMonth=0;
		unset($DayInFollowingMonth); // Rather unset for a new record
		if (!isset($_POST['Terms'])) $_POST['Terms']='';

		echo '<table><tr><td>' . _('Codigo') . ':</td><td><input type="Text" name="TermsIndicator"
		 ' . (in_array('TermsIndicator',$Errors) ? 'class="inputerror"' : '' ) .' value="' . $_POST['TermsIndicator'] .
			'" size=3 maxlength=2></td></tr>';
	}

	echo '<tr><td>'. _('Descripcion'). ':</td>
	<td>
	<input type="text"' . (in_array('Terms',$Errors) ? 'class="inputerror"' : '' ) .' name="Terms" VALUE="'.$_POST['Terms']. '" size=35 maxlength=40>
	</td></tr>
	<tr><td>'._('Vence en No. Dias').':</td>
	<td><input type="checkbox" name="DaysOrFoll"';
	if ( isset($DayInFollowingMonth) && !$DayInFollowingMonth) { echo "checked"; }
	echo ' ></td></tr><tr><td>'._('Dia del Mes que Vence').':</td><td>
		<input type="Text"' . (in_array('DayNumber',$Errors) ? 'class="inputerror"' : '' ) .' name="DayNumber" class=number  size=4 maxlength=3 VALUE=';
	if ($DaysBeforeDue !=0) {
			echo $DaysBeforeDue;
			} else {
			if (isset($DayInFollowingMonth)) {echo $DayInFollowingMonth;}
			}
	echo '></td></tr>
	
	<tr><td>'._('Genera Pagares').':</td>
	<td><input type="checkbox" name="generateCreditNote"';
	if ( isset($_POST['generateCreditNote'])) {
		if ($_POST['generateCreditNote'] == 1) {echo "checked";}
	}
	echo ' ></td></tr>';
	
	echo '<tr><td>'._('Genera Pagares Automatico').':</td>
	<td><input type="checkbox" name="generateAutomatic"';
	if ( isset($_POST['generateAutomatic'])) {
		if ($_POST['generateAutomatic'] == 1) {echo "checked";}
	}
	echo ' ></td></tr>';
	
	
	
	// Se dio de alta dias de gracia para el termino de pago
	
	echo '<tr><td>'._('Dias de Gracia').':</td><td>
		<input type="Text"' . (in_array('daygrace',$Errors) ? 'class="inputerror"' : '' ) .' name="daygrace" class=number  size=4 maxlength=3 VALUE=';
	echo $_POST['daygrace'];
	echo '></td></tr>';
	
	echo '<tr><td>'._('Descuento Pronto Pago').':</td><td>
		<input type="Text"' . (in_array('cashdiscount',$Errors) ? 'class="inputerror"' : '' ) .' name="cashdiscount" class=number  size=4 maxlength=3 VALUE=';
	echo $_POST['cashdiscount'];
	echo '></td></tr>';
	
	/****************************************************************/
	
	echo '<tr><td>'._('Numero de Vencimientos').':</td><td>
		<input type="Text"' . (in_array('numberOfPayments',$Errors) ? 'class="inputerror"' : '' ) .' name="numberOfPayments" class=number  size=4 maxlength=3 VALUE=';
	echo $_POST['numberOfPayments'];
	echo '><input type="Submit" name="vervencimientos" value="'._('Muestra Vencimientos').'"></td></tr>';
	// se agrega periodo de dias por numero de pago para los casos en los que los dias no son fijos
	//echo '<tr><td colspan=2>'._('Dias x numero de vencimiento ').':</td></tr>';

	
	//$SQL="Select * from "
	
	
	
	
	
	/*echo '<tr>';
	       echo '<td colspan=2>';
	       echo '&nbsp;</td></tr>';
*/
	       echo '<tr>';
	       echo '<td colspan=2>
	                <table>
	    		     <tr>';
				    echo '<td>' . _('Vencimiento Fijo:') . '</td>
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
				    </td>';
				echo '</tr>';
			echo '<table>';			
		   echo '</td>';	
	       echo '</tr>';
	       
	       echo '<tr><td></td><td>';
	       echo '<table border=1>';
	       echo '<tr><th>'._('Numero de Vencimiento').'</th>';
	       echo '<th>'._('Dias de Vencimiento').'</th>';
	       echo '<th>'._('Dias de Gracia').'</th>';
	       echo '<th>'._('% Descuento Pronto Pago').'</th>';
	       echo'</tr>';
	       for($i=1;$i<=$_POST['numberOfPayments'];$i++){
	       	$SQL='Select * from paymenttermbynumber where numberpay='.$i .' and termsindicator="'.$_POST['TermsIndicator'].'"';
	       	$result = DB_query($SQL, $db);
	       
	       	if (DB_num_rows($result)>0){
	       		$myrowpay = DB_fetch_array($result);
	       		$numberOfPayment=$i;
	       		$DaysOfPayment=$myrowpay['daysbeforedue'];
	       		$cashdiscount=$myrowpay['cashdiscount'];
	       		$daygrace=$myrowpay['daygrace'];
	       		//echo 'entraa';	
	       			
	       	}else{
	       		$numberOfPayment=$i;
	       		$DaysOfPayment=$DaysBeforeDue;
	       		$cashdiscount=0;
	       		$daygrace=0;
	       
	       	}
	      // 	echo 'dias'.$DaysOfPayment.'<br>ss:'.$DaysBeforeDue; // 
	       	echo '<tr>';
	       	echo '<td class=center ><input type="Text" name="numberOfPayment_'.$i.'" class=number  size=4 maxlength=3 VALUE=' . $numberOfPayment.'></td>';
	       	echo '<td class=center><input type="Text" name="DaysOfPayment_'.$i.'" class=number  size=4 maxlength=3 VALUE="' . $DaysOfPayment.'"></td>';
	       	echo '<td class=center><input type="Text" name="cashdiscount_'.$i.'" class=number  size=4 maxlength=3 VALUE="' . $cashdiscount.'"></td>';
	       	echo '<td class=center><input type="Text" name="daygrace_'.$i.'" class=number  size=4 maxlength=3 VALUE="' . $daygrace.'"></td>';
	       	
	       	echo '</tr>';
	       
	       }
	       echo '</table>';
	       echo '</td></tr>';


	       echo '<tr>';
	       echo '<td colspan=2>';
	       echo '&nbsp;</td></tr>';
	
	echo '</table><div class="centre">
			<input type="Submit" name="submit" value="'._('Procesa Informacion').'">
	</form></div>';
} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>