<?php

/* Archivo modificado por: Maria Alejandra Rosas Portillo
    Fecha de modificacion: 10-11-09 
    Cambios:
    10-11-09 - 1.- Se dejo toda la funcionalidad en blanco para poder ser reutilizado
    20-12-09 - 2.- Implementacion de Reporte de Detalle de transacciones contables
    25-12-09 -desarrollo- Se implemento reporte de transacciones contables, fecha de hoy por omision y
			subtotales por folio y unidad de negocio.
    Fin de cambios
*/
/*
 * AHA
* 6-Nov-2014
* Cambio de ingles a espa–ol los mensajes de usuario de tipo error,info,warning, y success.
*/

/* Escribir comentario de como funciona esta pagina AQUI*/

/*******************************************************************************************/
/*******************************************************************************************/
//Encabezado de pagina
/*******************************************************************************************/

$PageSecurity = 8;

include ('includes/session.inc');
$title = _('Reporte de Transacciones Contables Descuadradas');
include('includes/SQL_CommonFunctions.inc');
//include('includes/AccountSectionsDef.inc'); //this reads in the Accounts Sections array

/*******************************************************************************************/
//Fin de encabezado de pagina
/*******************************************************************************************/
/*******************************************************************************************/

//este if valida que la fecha inicial no sea mayor a la fecha final y ademas valida que las fechas tengan valor
if (isset($_POST['FromPeriod']) and isset($_POST['ToPeriod']) and $_POST['FromPeriod'] > $_POST['ToPeriod']){
	prnMsg(_('La fecha inicial no puede ser mayor a la fecha final! Por favor seleccionar nuevamente los periodos de fecha'),'error');
	$_POST['SelectADifferentPeriod']=_('Selecciona diferentes fechas');
}
//se valida si el campo de las fechas esta vacio o la captura de las fechas tuvo un error 
if ((! isset($_POST['FromPeriod']) AND ! isset($_POST['ToPeriod'])) OR isset($_POST['SelectADifferentPeriod'])){

        /*******************************************************************************************/
        /*******************************************************************************************/
        //Codigo que se ejecuta la primera vez que entramos a la pagina
        /*******************************************************************************************/

	include  ('includes/header.inc');
	echo '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
	
	if (isset($_POST['FromPeriod'])) {
		$desdePeriodo = $_POST['FromPeriod'];
	} else {
		$desdePeriodo = date("Y/m/d");
	}
	
	if (isset($_POST['ToPeriod'])) {
		$hastaPeriodo = $_POST['ToPeriod'];
	} else {
		$hastaPeriodo = date("Y/m/d");
	}
	
	/* Muestra la forma de captura de fechas*/
	echo '<table><tr><td>' . _('desde fecha:') . "</td><td><input type='text' class=date alt='Y/m/d'
		 name='FromPeriod' size=11 value='" . $desdePeriodo . "'></td></tr>";
	
	echo '<tr><td>' . _('hasta fecha:') . "</td><td><input type='text' class=date alt='Y/m/d'
		 name='ToPeriod' size=11 value='" . $hastaPeriodo . "'></td></tr>";
			
	echo "<tr><td><br></td><td></td></tr>";		
			
	//Select the razon social
	echo '<tr><td>'._('Seleccione Una Razon Social:').'<td><select name="legalid">';
		
	///Pinta las razones sociales
	$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "'
			  GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.tagref";		

	$result=DB_query($SQL,$db);
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['legalid']) and $_POST['legalid']==$myrow["legalid"]){
			echo '<option selected value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
		} else {
			echo '<option value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
		}
	}
	echo '</select></td>';
	// End select Razon Social

	echo "<tr><td><br></td><td></td></tr>";	
	/************************************/
	/* SELECCION DEL REGION */
	echo '<tr><td>' . _('X Region') . ':' . "</td>
		<td><select tabindex='4' name='xRegion'>";

	$sql = "SELECT regions.regioncode, CONCAT(regions.regioncode,' - ',regions.name) as name FROM regions JOIN areas ON areas.regioncode = regions.regioncode
			JOIN tags ON tags.areacode = areas.areacode JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
		  WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
		  GROUP BY regions.regioncode, regions.name";
	
	$result=DB_query($sql,$db);
	
	echo "<option selected value='0'>Todas las regiones...</option>";

	while ($myrow=DB_fetch_array($result)){
	      if ($myrow['regioncode'] == $_POST['xRegion']){
			echo "<option selected value='" . $myrow["regioncode"] . "'>" . $myrow['name'];
	      } else {
		      echo "<option value='" . $myrow['regioncode'] . "'>" . $myrow['name'];
	      }
	}
	echo '</select></td></tr>';
	/************************************/
	
	/************************************/
	/* SELECCION DEL AREA */
	echo '<tr><td>' . _('X Area') . ':' . "</td>
		<td><select tabindex='4' name='xArea'>";

	$sql = "SELECT areas.areacode, CONCAT(areas.areacode,' - ',areas.areadescription) as name
			FROM areas 
			JOIN tags ON tags.areacode = areas.areacode JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
		  WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
		  GROUP BY areas.areacode, areas.areadescription";
	
	$result=DB_query($sql,$db);
	
	echo "<option selected value='0'>Todas las areas...</option>";

	while ($myrow=DB_fetch_array($result)){
	      if ($myrow['areacode'] == $_POST['xArea']){
			echo "<option selected value='" . $myrow["areacode"] . "'>" . $myrow['name'];
	      } else {
		      echo "<option value='" . $myrow['areacode'] . "'>" . $myrow['name'];
	      }
	}
	echo '</select></td></tr>';
	/************************************/
	
	/************************************/
	/* SELECCION DEL DEPARTAMENT0 */
	echo '<tr><td>' . _('X Departamento') . ':' . "</td>
		<td><select tabindex='4' name='xDepto'>";

	$sql = "SELECT department as departamento, departments.u_department
			FROM departments
			JOIN tags ON tags.u_department = departments.u_department
			JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
		  WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
		  GROUP BY departments.department";
	
	$result=DB_query($sql,$db);
	
	echo "<option selected value='0'>Todos los departamentos...</option>";

	while ($myrow=DB_fetch_array($result)){
	      if ($myrow['u_department'] == $_POST['xDepto']){
			echo "<option selected value='" . $myrow["u_department"] . "'>" . $myrow['departamento'];
	      } else {
		      echo "<option value='" . $myrow['u_department'] . "'>" . $myrow['departamento'];
	      }
	}
	echo '</select></td></tr>';
	/************************************/
	
	echo "<tr><td><br></td><td></td></tr>";
	
	/* Muestra Seleccion de la unidad de negocio*/
	echo '<tr><td>' . _('Selecciona Unidad de Negocio:') .'</td><td><select name="tag">';

	/******************************************/
	//Pinta las unidades de negocio por usuario	
	$SQL = "SELECT t.tagref,t.tagdescription";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t ";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' ORDER BY t.tagref";
		
	$result=DB_query($SQL,$db);
	
	echo '<option selected value=0>Todas a las que tengo acceso...';
	
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['tag']) and $_POST['tag']==$myrow['tagref']){
			echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
		} else {
			echo '<option value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
		}
	}
	echo '</select></td></tr>';
	// End select tag
	/******************************************/
	
	echo '<tr><td>' . _('Selecciona Tipo Polizas:') ."</td><td><select name='cbotipopoliza' style='font-size:8pt;'>";
	echo "<option value='-1'>TODOS</option>";
	$SQL = "SELECT  t.typeid, t.typename
		FROM systypescat t
		ORDER BY t.typeid";
	
	$ErrMsg = _('No hay transacciones fueron devueltos por el SQL porque');
	$TransResult = DB_query($SQL,$db,$ErrMsg);
	while ($myrow=DB_fetch_array($TransResult)) {
		if ($myrow['typeid'] == $tipopoliza){
			echo "<option selected value='" . $myrow['typeid'] . "'>" . $myrow['typeid']. ' ' .$myrow['typename'] . "</option>";	
		}else{
			echo "<option value='" . $myrow['typeid'] . "'>" . $myrow['typeid'].' '.$myrow['typename'] . "</option>";
		}
	}
	
	echo "</select>";
	echo "</td>";
	echo "</tr>";
	
	echo "<tr><td><br></td><td></td></tr>";
	
	echo '<tr><td>' . _('Tipo de Descuadres Buscados:') ."</td><td><select name='tipoDescuadre' style='font-size:8pt;'>";
	echo "<option value='1'>X Razon Social</option>";
	echo "<option value='2'>X Unidad de Negocio</option>";
	echo "</select>";
	echo "</td>";
	echo "</tr>
	
	<tr><td><br></td><td></td></tr>
	
	</table>";

	echo '<div class="centre"><input type=submit Name="ShowTB" Value="' . _('Mostrar en pantalla') .'">';
	echo "</div>";
        
        /*******************************************************************************************/
        //Fin del Codigo que se ejecuta la primera vez que entramos a la pagina
        /*******************************************************************************************/
        /*******************************************************************************************/
        
} else {

        /*******************************************************************************************/
        /*******************************************************************************************/
        //Codigo que se ejecuta para mostrar la tabla en pantalla con datos arrojados por la consulta SQL 
        /*******************************************************************************************/

	include('includes/header.inc');
	echo '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
	echo '<input type=hidden name="FromPeriod" VALUE="' . $_POST['FromPeriod'] . '"><input type=hidden name="ToPeriod" VALUE="' . $_POST['ToPeriod'] . '">';

	$NumberOfMonths = 1;

	$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];
	
	$SQL = "select 	counterindex, gltrans.type, gltrans.typeno, gltrans.account, DATE_FORMAT(gltrans.trandate,'%Y/%m/%d') as trandate,
				periodno, narrative, amount, gltrans.tag, typename,
				accountname, naturaleza, nombremayor ";
				
	if ($_POST['tipoDescuadre'] == 1) {
		$SQL = $SQL .		
		" from 	(select tags.legalid, type, typeno, sum(amount) 
					from gltrans JOIN tags ON gltrans.tag = tags.tagref
					group by tags.legalid, type, typeno
					having abs(sum(amount))  > 5) as descuadres JOIN
				gltrans ON descuadres.type = gltrans.type AND descuadres.typeno = gltrans.typeno";
	} else {
		$SQL = $SQL .		
		" from 	(select tag, type, typeno, sum(amount) 
					from gltrans
					group by tag, type, typeno
					having abs(sum(amount))  > 5) as descuadres JOIN
				gltrans ON descuadres.tag = gltrans.tag AND descuadres.type = gltrans.type AND descuadres.typeno = gltrans.typeno";
	}
				
	$SQL = $SQL .					
		"	, systypescat, chartmaster, chartTipos,
			sec_unegsxuser
			join tags ON sec_unegsxuser.tagref = tags.tagref
			JOIN areas ON tags.areacode = areas.areacode
		        JOIN regions ON areas.regioncode = regions.regioncode
			join legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
		where 	(gltrans.tag = ".$_POST['tag']." or '0' = '".$_POST['tag']."')
			and (tags.u_department = ".$_POST['xDepto']." or '0' = '".$_POST['xDepto']."')
			and (areas.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			and (areas.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			and (tags.legalid = ".$_POST['legalid'].") and
			gltrans.tag = sec_unegsxuser.tagref and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "' and
			DATE_FORMAT(gltrans.trandate,'%Y/%m/%d') >= '".$_POST['FromPeriod']."' and
			DATE_FORMAT(gltrans.trandate,'%Y/%m/%d') <= '".$_POST['ToPeriod']."' and
			gltrans.type = systypescat.typeid and
			(gltrans.type =  ". $_POST['cbotipopoliza'] ." OR '-1' = '". $_POST['cbotipopoliza'] ."') and
			gltrans.account = chartmaster.accountcode and
			chartmaster.tipo = chartTipos.tipo
		order by counterindex";
	
	$AccountsResult = DB_query($SQL,
				$db,
				 _('No fueron entregados registros del SQL por'),
				 _('The SQL that failed was:'));

	if ($_POST['tag'] != 0) {
		///Pinta las unidades de negocio por usuario	
		$SQL = "SELECT t.tagref,t.tagdescription";
		$SQL = $SQL .	" FROM sec_unegsxuser u,tags t ";
		$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
		$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' and t.tagref = " . $_POST['tag'];
		$SQL = $SQL .   " ORDER BY t.tagref";
			
		$result=DB_query($SQL,$db);
		if ($myrow=DB_fetch_array($result)){
			echo '<div class="centre"><font size=4 color=BLUE><b>' . $myrow['tagdescription'];
			echo '</b></font></div><br>';
		}
	} else {
		echo '<div class="centre"><font size=4 color=BLUE><b>TODAS LAS UNIDADES DE NEGOCIOS PARA ESTE USUARIO';
		echo '</b></font></div><br>';
	}
	
	echo '<div class="centre"><font size=3 color=BLUE><b>'. _('Reporte desde: ') . $_POST['FromPeriod'] . _(' hasta: ') . $_POST['ToPeriod'] .'</b></font></div><br>';	
	
	//echo $SQL;

	/*show a table of the accounts info returned by the SQL
	Account Code ,   Account Name , Month Actual, Month Budget, Period Actual, Period Budget */
	//muetra la tabla con los resultados obtenidos por la consulta SQL
	echo '<table cellpadding=2>';
	$TableHeader = '<tr>
			<th>' . _('Fecha') . '</th>
			<th>' . _('UN') . '</th>
			<th>' . _('indice') . '</th>
			<th>' . _('Docto') . '</th>
			<th>' . _('Folio') . '</th>
			<th>' . _('Cuenta Contable') . '</th>
			<th>' . _('Monto en Pesos') . '</th>
			<th>' . _('Nat') .'</th>
			<th>' . _('Concepto') .'</th>
			</tr>';


	$j = 1;
	$k = 0; //row colour counter

	echo $TableHeader;
	
	$AcumBalancePerFolio = 0;
	$UNFolioAnterior = '';
	$UNFolio = '';
	$UNType = '';
	$existenmovs = 0;
	
	$TotalBalance = 0;
	while ($myrow=DB_fetch_array($AccountsResult)) {
		$existenmovs = 1;
		if ($j == 1) {
			$UNFolioAnterior = $myrow['tag'].'/'.$myrow['typeno'];
			$UNFolio = $myrow['typeno'];
			$UNType = $myrow['type'];
		}
		
		if ($myrow['tag'].'/'.$myrow['typeno'] != $UNFolioAnterior) {
			$descuadres='yes';
			
			$liga = 'GLJournalV2_0.php?&NewJournal=Yes&type=' . $UNType . '&typeno=' . $UNFolio. '&descuadres=' . $descuadres;
			$generarnvo = "<a TARGET='_blank' href='" . $liga . "'>" . _('Corregir Poliza Descuadrada...') . "</a>";
			
			/*
			$SQL = "SELECT  *
				FROM debtortrans
				WHERE transno = ".$UNFolio." and type = ".$UNType;
				
			$result=DB_query($SQL,$db);			
			if ($myrow=DB_fetch_array($result)){
				$ligadocto = "FOLIO FISCAL:" . $myrow[''] . "'>" . _('Corregir Poliza Descuadrada...') . "</a>";
			}
			*/
			echo '<tr style="background-color:yellow" class="EvenTableRows">';
			
			/*
			$SQL = 'INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag) ';
			$SQL= $SQL . 'VALUES ('.$UNType.',
						' . $UNFolio . ",
						'" . $UNFecha . "',
						" . $UNPeriodo . ",
						'148000',
						'" . $Proveedor . ' - ' . _('GRN') . ' ????? - ????? x ??? @  ' .
                                                     _('std cost of') . ' ??????? ' .
						' CORRECCION'. "',
						" . ( $AcumBalancePerFolio * -1) .
						",'".$UNTag."')";
			$ErrMsg = _('No pude insertar el movimiento en contabilidad porque');
			$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
			//$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			*/
			
			$SQL = 'UPDATE gltrans SET amount = amount * -1 where counterindex = '.$indiceACorregir;
			
			$ErrMsg = _('No se pudo insertar el movimiento en contabilidad porque');
			$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
			//$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			
			/*DESPLIEGA VALOR DE SUBTOTAL X UN Y FOLIO*/
			printf('<td class="peque" >%s</td>
				<td class="peque" >%s</td>
				<td class="peque" >%s</td>
				<td class="peque" >%s</td>
				<td class="pequenum">%s</td>
				<td class="peque" >%s</td>
				<td class="pequenum">%s</td>
				<td class="pequenum">%s</td>
				<td class="peque" >%s</td>
				</tr>',
				'total',
				$UNTag,
				$UNPeriodo,
				$UNType,
				$UNFolio,
				$generarnvo,
				number_format($AcumBalancePerFolio,2),
				$SQL,
				$Proveedor);
			
			
			/*INICIALIZA SUBTOTAL Y COMIENZA A CONTAR PARA NUEVA COMBINACION*/
			$AcumBalancePerFolio = 0;
		}
		
		$UNFolioAnterior = $myrow['tag'].'/'.$myrow['typeno'];
		
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		printf('<td class="peque" >%s</td>
			<td class="peque" >%s</td>
			<td class="peque" >%s</td>
    			<td class="peque" >%s</td>
			<td class="pequenum">%s</td>
			<td class="peque" >%s</td>
			<td class="pequenum">%s</td>
			<td class="pequenum">%s</td>
			<td class="peque" >%s</td>
			</tr>',
			$myrow['trandate'],
			$myrow['tag'],
			$myrow['counterindex'],
			$myrow['typename'],
			$myrow['typeno'],
			$myrow['accountname'],
			number_format($myrow['amount'],2),
			$myrow['naturaleza'],
			$myrow['narrative']);
		
		$UNFolio = $myrow['typeno'];
		$UNType = $myrow['type'];
		$UNFecha = $myrow['trandate'];
		$UNPeriodo = $myrow['periodno'];
		$UNTag = $myrow['tag'];
		$Proveedor = substr($myrow['narrative'],0,strpos($myrow['narrative'],' -'));
		$montoACorregir = $myrow['amount'];
		$cuentaACorregir = $myrow['account'];
		$indiceACorregir = $myrow['counterindex'];
		$TotalBalance = $TotalBalance + $myrow['amount'];
		$AcumBalancePerFolio = $AcumBalancePerFolio + $myrow['amount'];
		$j++;
	}
	
	if ($existenmovs == 1){
		$descuadres='yes';
		$liga = 'GLJournalV2_0.php?&NewJournal=Yes&type=' . $UNType . '&typeno=' . $UNFolio. '&descuadres=' . $descuadres;
		$generarnvo = "<a TARGET='_blank' href='" . $liga . "'>" . _('Corregir Poliza Descuadrada...') . "</a>";
		
		/*DESPLIEGA VALOR DE SUBTOTAL X UN Y FOLIO*/
		echo '<tr style="background-color:yellow" class="EvenTableRows">';
		printf('<td class="peque" >%s</td>
			<td class="peque" >%s</td>
			<td class="peque" >%s</td>
			<td class="peque" >%s</td>
			<td class="pequenum">%s</td>
			<td class="peque" >%s</td>
			<td class="pequenum">%s</td>
			<td class="pequenum">%s</td>
			<td class="peque" >%s</td>
			</tr>',
			'total',
			$UNTag,
			$UNPeriodo,
			$UNType,
			$UNFolio,
			$generarnvo,
			number_format($AcumBalancePerFolio,2),
			$SQL,
			$Proveedor);	
	}
	
	
	
	
	/*INICIALIZA SUBTOTAL Y COMIENZA A CONTAR PARA NUEVA COMBINACION*/
	$AcumBalancePerFolio = 0;
	
	
	/*
	$SQL = "SELECT  *
		FROM debtortrans
		WHERE transno = ".$UNFolio." and type = ".$UNType;
		
	$result=DB_query($SQL,$db);			
	if ($myrow=DB_fetch_array($result)){
		$ligadocto = "FOLIO FISCAL:" . $myrow[''] . "'>" . _('Corregir Poliza Descuadrada...') . "</a>";
	}
	*/
	$SQL = 'INSERT INTO gltrans (type,
					typeno,
					trandate,
					periodno,
					account,
					narrative,
					amount,
					tag) ';
	$SQL= $SQL . 'VALUES ('.$UNType.',
				' . $UNFolio . ",
				'" . FormatDateForSQL($UNFecha) . "',
				" . $UNPeriodo . ",
				148000,
				'AJUSTE AUTOMATICO X CORRECCION',
				" . ( $AcumBalancePerFolio * -1) .
				",'".$UNTag."')";
	$ErrMsg = _('No se pudo insertar el movimiento en contabilidad porque');
	$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
	//$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				
	$SQL = 'UPDATE gltrans SET amount = amount * -1 where counterindex = '.$indiceACorregir;
			
	$ErrMsg = _('No se pudo insertar el movimiento en contabilidad porque');
	$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
	//$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			
	/*DESPLIEGA VALOR DE SUBTOTAL DE ULTIMA UNIDAD / FOLIO EN LA LISTA*/
	
	echo '<tr style="background-color:rgb(240,238,168)" class="EvenTableRows">';
	
	printf('<td class="peque" >%s</td>
	<td class="peque" >%s</td>
	<td class="peque" >%s</td>
	<td class="peque" >%s</td>
	<td class="pequenum">%s</td>
	<td class="peque" >%s</td>
	<td class="pequenum">%s</td>
	<td class="pequenum">%s</td>
	<td class="peque" >%s</td>
	</tr>',
	'total',
	$myrow['tag'],
	'',
	'',
	$myrow['typeno'],
	'',
	number_format($AcumBalancePerFolio,2),
	'',
	'');
	
	printf('<tr bgcolor="lightgray">
			<td colspan=2><font color=BLUE><b>' . _('Totales') . '</b></font></td>
			<td class=number></td>
			<td class=number></td>
			<td class=number></td>
			<td class=number></td>
			<td class=number>%s</td>
			<td class=number></td>
			<td class=number></td>
			<td class=number></td>
		</tr>',
		number_format($TotalBalance,2));

	echo '</table>';
	echo '<div class="centre"><input type=submit Name="SelectADifferentPeriod" Value="' . _('Seleccionar un periodo diferente') . '"></div>';
	
	
	$SQL = "select 	counterindex, gltrans.type, gltrans.typeno, gltrans.account, DATE_FORMAT(gltrans.trandate,'%Y/%m/%d') as trandate,
			periodno, narrative, amount, tag, typename,
			accountname, naturaleza, nombremayor 
		from 	(select type, typeno, sum(amount) 
				from gltrans
				group by type, typeno
				having abs(sum(amount))  > 10) as descuadres JOIN
			gltrans ON descuadres.type = gltrans.type AND descuadres.typeno = gltrans.typeno,
			systypescat, chartmaster, chartTipos
		where 	(gltrans.tag = 0) and
			DATE_FORMAT(gltrans.trandate,'%Y/%m/%d') >= '".$_POST['FromPeriod']."' and
			DATE_FORMAT(gltrans.trandate,'%Y/%m/%d') <= '".$_POST['ToPeriod']."' and
			(gltrans.type = ".$_POST['cbotipopoliza']." OR '-1'='".$_POST['cbotipopoliza']."') and
			gltrans.type = systypescat.typeid and
			gltrans.account = chartmaster.accountcode and
			chartmaster.tipo = chartTipos.tipo
		order by gltrans.type, gltrans.typeno,counterindex";
	
	$AccountsResult = DB_query($SQL,
				$db,
				 _('No fueron entregados registros del SQL por'),
				 _('The SQL that failed was:'));

	
	echo '<div class="centre"><font size=4 color=BLUE><b>TODAS LAS UNIDADES DE NEGOCIOS PARA ESTE USUARIO';
	echo '</b></font></div><br>';
	
	echo '<div class="centre"><font size=3 color=BLUE><b>'. _('Reporte desde: ') . $_POST['FromPeriod'] . _(' hasta: ') . $_POST['ToPeriod'] .'</b></font></div><br>';	
	
	//echo $SQL;

	/*show a table of the accounts info returned by the SQL
	Account Code ,   Account Name , Month Actual, Month Budget, Period Actual, Period Budget */
	//muetra la tabla con los resultados obtenidos por la consulta SQL
	echo '<table cellpadding=2>';
	$TableHeader = '<tr>
			<th>' . _('Fecha') . '</th>
			<th>' . _('UN') . '</th>
			<th>' . _('indice') . '</th>
			<th>' . _('Docto') . '</th>
			<th>' . _('Folio') . '</th>
			<th>' . _('Cuenta Contable') . '</th>
			<th>' . _('Monto en Pesos') . '</th>
			<th>' . _('Nat') .'</th>
			<th>' . _('Concepto') .'</th>
			</tr>';


	$j = 1;
	$k = 0; //row colour counter

	echo $TableHeader;
	
	$AcumBalancePerFolio = 0;
	$UNFolioAnterior = '';
	$UNFolio = '';
	$UNType = '';
	
	$TotalBalance = 0;
	while ($myrow=DB_fetch_array($AccountsResult)) {
		if ($j == 1) {
			$UNFolioAnterior = $myrow['tag'].'/'.$myrow['typeno'];
			$UNFolio = $myrow['typeno'];
			$UNType = $myrow['type'];
		}
		
		if ($myrow['tag'].'/'.$myrow['typeno'] != $UNFolioAnterior) {
			
			$liga = 'GLJournalV2_0.php?&NewJournal=Yes&type=' . $UNType . '&typeno=' . $UNFolio;
			$generarnvo = "<a TARGET='_blank' href='" . $liga . "'>" . _('Corregir Poliza Descuadrada...') . "</a>";
			
			/*
			$SQL = "SELECT  *
				FROM debtortrans
				WHERE transno = ".$UNFolio." and type = ".$UNType;
				
			$result=DB_query($SQL,$db);			
			if ($myrow=DB_fetch_array($result)){
				$ligadocto = "FOLIO FISCAL:" . $myrow[''] . "'>" . _('Corregir Poliza Descuadrada...') . "</a>";
			}
			*/
			echo '<tr style="background-color:yellow" class="EvenTableRows">';
			
			/*
			$SQL = 'INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag) ';
			$SQL= $SQL . 'VALUES ('.$UNType.',
						' . $UNFolio . ",
						'" . $UNFecha . "',
						" . $UNPeriodo . ",
						'148000',
						'" . $Proveedor . ' - ' . _('GRN') . ' ????? - ????? x ??? @  ' .
                                                     _('std cost of') . ' ??????? ' .
						' CORRECCION'. "',
						" . ( $AcumBalancePerFolio * -1) .
						",'".$UNTag."')";
			$ErrMsg = _('No pude insertar el movimiento en contabilidad porque');
			$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
			//$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			*/
			
			$SQL = 'UPDATE gltrans SET amount = amount * -1 where counterindex = '.$indiceACorregir;
			
			$ErrMsg = _('No se pudo insertar el movimiento en contabilidad porque');
			$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
			//$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			
			/*DESPLIEGA VALOR DE SUBTOTAL X UN Y FOLIO*/
			printf('<td class="peque" >%s</td>
				<td class="peque" >%s</td>
				<td class="peque" >%s</td>
				<td class="peque" >%s</td>
				<td class="pequenum">%s</td>
				<td class="peque" >%s</td>
				<td class="pequenum">%s</td>
				<td class="pequenum">%s</td>
				<td class="peque" >%s</td>
				</tr>',
				'total',
				$UNTag,
				$UNPeriodo,
				$UNType,
				$UNFolio,
				$generarnvo,
				number_format($AcumBalancePerFolio,2),
				$SQL,
				$Proveedor);
			
			
			/*INICIALIZA SUBTOTAL Y COMIENZA A CONTAR PARA NUEVA COMBINACION*/
			$AcumBalancePerFolio = 0;
		}
		
		$UNFolioAnterior = $myrow['tag'].'/'.$myrow['typeno'];
		
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		printf('<td class="peque" >%s</td>
			<td class="peque" >%s</td>
			<td class="peque" >%s</td>
    			<td class="peque" >%s</td>
			<td class="pequenum">%s</td>
			<td class="peque" >%s</td>
			<td class="pequenum">%s</td>
			<td class="pequenum">%s</td>
			<td class="peque" >%s</td>
			</tr>',
			$myrow['trandate'],
			$myrow['tag'],
			$myrow['counterindex'],
			$myrow['typename'],
			$myrow['typeno'],
			$myrow['accountname'],
			number_format($myrow['amount'],2),
			$myrow['naturaleza'],
			$myrow['narrative']);
		
		$UNFolio = $myrow['typeno'];
		$UNType = $myrow['type'];
		$UNFecha = $myrow['trandate'];
		$UNPeriodo = $myrow['periodno'];
		$UNTag = $myrow['tag'];
		$Proveedor = substr($myrow['narrative'],0,strpos($myrow['narrative'],' -'));
		$montoACorregir = $myrow['amount'];
		$cuentaACorregir = $myrow['account'];
		$indiceACorregir = $myrow['counterindex'];
		$TotalBalance = $TotalBalance + $myrow['amount'];
		$AcumBalancePerFolio = $AcumBalancePerFolio + $myrow['amount'];
		$j++;
	}
	
	$liga = 'GLJournalV2_0.php?&NewJournal=Yes&type=' . $UNType . '&typeno=' . $UNFolio;
	$generarnvo = "<a TARGET='_blank' href='" . $liga . "'>" . _('Corregir Poliza Descuadrada...') . "</a>";
	
	/*
	$SQL = "SELECT  *
		FROM debtortrans
		WHERE transno = ".$UNFolio." and type = ".$UNType;
		
	$result=DB_query($SQL,$db);			
	if ($myrow=DB_fetch_array($result)){
		$ligadocto = "FOLIO FISCAL:" . $myrow[''] . "'>" . _('Corregir Poliza Descuadrada...') . "</a>";
	}
	*/
	$SQL = 'INSERT INTO gltrans (type,
					typeno,
					trandate,
					periodno,
					account,
					narrative,
					amount,
					tag) ';
	$SQL= $SQL . 'VALUES ('.$UNType.',
				' . $UNFolio . ",
				'" . FormatDateForSQL($UNFecha) . "',
				" . $UNPeriodo . ",
				148000,
				'AJUSTE AUTOMATICO X CORRECCION',
				" . ( $AcumBalancePerFolio * -1) .
				",'".$UNTag."')";
	$ErrMsg = _('No se pudo insertar el movimiento en contabilidad porque');
	$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
	//$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				
	$SQL = 'UPDATE gltrans SET amount = amount * -1 where counterindex = '.$indiceACorregir;
			
	$ErrMsg = _('No se pudo insertar el movimiento en contabilidad porque');
	$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
	//$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			
	/*DESPLIEGA VALOR DE SUBTOTAL DE ULTIMA UNIDAD / FOLIO EN LA LISTA*/
	
	echo '<tr style="background-color:rgb(240,238,168)" class="EvenTableRows">';
	
	printf('<td class="peque" >%s</td>
	<td class="peque" >%s</td>
	<td class="peque" >%s</td>
	<td class="peque" >%s</td>
	<td class="pequenum">%s</td>
	<td class="peque" >%s</td>
	<td class="pequenum">%s</td>
	<td class="pequenum">%s</td>
	<td class="peque" >%s</td>
	</tr>',
	'total',
	$myrow['tag'],
	'',
	'',
	$myrow['typeno'],
	$generarnvo,
	number_format($AcumBalancePerFolio,2),
	'',
	'');
	
	printf('<tr bgcolor="lightgray">
			<td colspan=2><font color=BLUE><b>' . _('Totales') . '</b></font></td>
			<td class=number></td>
			<td class=number></td>
			<td class=number></td>
			<td class=number></td>
			<td class=number>%s</td>
			<td class=number></td>
			<td class=number></td>
			<td class=number></td>
		</tr>',
		number_format($TotalBalance,2));

	echo '</table>';
	echo '<div class="centre"><input type=submit Name="SelectADifferentPeriod" Value="' . _('Seleccionar un periodo diferente') . '"></div>';
}

        /*******************************************************************************************/
        //Fin del codigo que se ejecuta para mostrar la tabla en pantalla con datos arrojados por la consulta SQL 
        /*******************************************************************************************/
        /*******************************************************************************************/
        
echo '</form>';
include('includes/footer.inc');

?>