<?php
/* $Revision: 1.13.1 $ */
/*cambios
1.- se agrego el include('includes/SecurityFunctions.inc');
2.- se arreglo la variable $funcion*/
$PageSecurity = 2;
include('includes/session.inc');


$funcion=815;
include('includes/SecurityFunctions.inc');




$title = _('Reporte General de Cuentas x Pagar');
include('includes/header.inc');
$debug = 1;
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' ' . $title.'<br>';
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
  /*   if ($fechainic > $fechafinc){
          $InputError = 1;
     prnMsg(_('La fecha inicial no puede ser mayor a la fecha final! Por favor seleccionar nuevamente los periodos de fecha'),'error');
     } else {
          $InputError = 0;
     }
  */

/* OBTENGO  FECHAS */
	echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'>";
	echo "<table>";
	echo '<tr>
		<td colspan=2>
			<p align=center><b>** SELECCIONA EL CRITERIO DE CONSULTA</b><br><br>
		</td>';
	echo '</tr>';
	/* SELECCIONA EL RANGO DE FECHAS */
	echo '<tr>';
	echo '	<td colspan=2>';
	echo '		&nbsp;
		</td>
	      </tr>';
	echo '<tr>';
	echo '<td colspan=2>';
		echo '<table>';
			echo '<tr>';
				echo '<td>' . _('AL:') . '</td>';
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
		echo '	&nbsp;
		      </td>
	       </tr>';
	/* FIN DE SELECCION DEL RANGO DE FECHAS */
	//Selecciona Razon Social
	echo '<tr>
		<td>'
			._('Seleccione Una Razon Social:').'
		</td>
		<td>';
			///Pinta las razones sociales
			$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
			$SQL = $SQL .	" FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
			$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
			$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "'
					  GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.tagref";
			echo '<select name="legalid">';
			$result=DB_query($SQL,$db);
			while ($myrow=DB_fetch_array($result)){
				if (isset($_POST['legalid']) and $_POST['legalid']==$myrow["legalid"]){
					echo '<option selected value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
				} else {
					echo '<option value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
				}
			}
			echo '</select>
		</td>';
	echo '</tr>';
	// Fin Razon Social
	/************************************/
	/* SELECCION DEL REGION */

	echo '<tr>
		<td>'
			. _('X Region') . ':' .'
		</td>';
	echo'	<td>';
			$sql = "SELECT regions.regioncode, CONCAT(regions.regioncode,' - ',regions.name) as name FROM regions JOIN areas ON areas.regioncode = regions.regioncode
					JOIN tags ON tags.areacode = areas.areacode JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
				  WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
				  GROUP BY regions.regioncode, regions.name";
			$result=DB_query($sql,$db);
			echo "<select tabindex='4' name='xRegion'>";
				echo "<option selected value='0'>Todas las regiones...</option>";
				while ($myrow=DB_fetch_array($result)){
				      if ($myrow['regioncode'] == $_POST['xRegion']){
						echo "<option selected value='" . $myrow["regioncode"] . "'>" . $myrow['name'];
				      } else {
					      echo "<option value='" . $myrow['regioncode'] . "'>" . $myrow['name'];
				      }
				}
			echo '</select>
		</td>';
	echo '</tr>';
	/*FIN DE REGION*/
	/* SELECCION DEL AREA */
	echo '<tr>
		<td>'
			. _('X Area') . ':' .'
		</td>';
	echo'	<td>';
			$sql = "SELECT areas.areacode, CONCAT(areas.areacode,' - ',areas.areadescription) as name
				FROM areas
				JOIN tags ON tags.areacode = areas.areacode JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
			  WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			  GROUP BY areas.areacode, areas.areadescription";
			$result=DB_query($sql,$db);
			echo "<select tabindex='4' name='xArea'>";
			echo "<option selected value='0'>Todas las areas...</option>";
			while ($myrow=DB_fetch_array($result)){
			      if ($myrow['areacode'] == $_POST['xArea']){
					echo "<option selected value='" . $myrow["areacode"] . "'>" . $myrow['name'];
			      } else {
				      echo "<option value='" . $myrow['areacode'] . "'>" . $myrow['name'];
			      }
			}
			echo '</select>
		</td>';
	echo '</tr>';
	/*FIN DE AREA*/
	/* SELECCION DE DEPARTAMENTO       */
	echo '<tr>
		<td>' . _('X Departamento') . ':' . "</td>
		<td>";
			/*
			$sql = "SELECT descripcion as name
				FROM catdepartamentos
				JOIN tags ON tags.tagref = catdepartamentos.tagref JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
			  WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			  GROUP BY descripcion";
			$result=DB_query($sql,$db);
			echo "<select tabindex='4' name='xDepartamento'>";
				echo "<option selected value='0'>Todos los departamentos...</option>";
				while ($myrow=DB_fetch_array($result)){
				      if ($myrow['name'] == $_POST['xDepartamento']){
						echo "<option selected value='" . $myrow["name"] . "'>" . $myrow['name'];
				      } else {
					      echo "<option value='" . $myrow['name'] . "'>" . $myrow['name'];
				      }
				}
			echo '</select>
			*/
			$sql = "SELECT departments.u_department, departments.department
				FROM departments
				JOIN tags ON tags.u_department = departments.u_department JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
			  WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'";
			$result=DB_query($sql,$db);
			echo "<select tabindex='4' name='xDepartamento'>";
				echo "<option selected value='0'>Todos los departamentos...</option>";
				while ($myrow=DB_fetch_array($result)){
				      if ($myrow['name'] == $_POST['xDepartamento']){
						echo "<option selected value='" . $myrow["u_department"] . "'>" . $myrow['department'];
				      } else {
					      echo "<option value='" . $myrow['u_department'] . "'>" . $myrow['department'];
				      }
				}
			echo '</select>
		</td>';
	echo '</tr>';
	/*FIN DEPARTAMENTO*/
	/* SELECCION DE LA UNIDAD DE NEGOCIO DE PRODUCTOS */
	echo "<tr>
		<td>" . _('X Unidad de Negocio') . ":</td>
		<td>";
			$SQL = "SELECT  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription";//areas.areacode, areas.areadescription";
				$SQL = $SQL .	" FROM sec_unegsxuser u,tags t join areas ON t.areacode = areas.areacode ";
				$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
				$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "'
						ORDER BY t.tagref, areas.areacode";
			$ErrMsg = _('No transactions were returned by the SQL because');
			$TransResult = DB_query($SQL,$db,$ErrMsg);
			echo "<select name='unidadnegocio'>";
				echo "<option selected value='0'>Todas a las que tengo accceso...</option>";
				while ($myrow=DB_fetch_array($TransResult)) {
					if ($myrow['tagref'] == $_POST['unidadnegocio']){
						echo "<option selected value='" . $myrow['tagref'] . "'>" . $myrow['tagdescription'] . "</option>";
					}else{
						echo "<option value='" . $myrow['tagref'] . "'>" . $myrow['tagdescription'] . "</option>";
					}
				}
			echo "</select>";
	echo "  </td>";
	echo "</tr>";

	/*FIN UNIDAD DE NEGOCIO*/
	/*
	echo '<tr>
		<td>' . _('X Tipo Cliente') . ':' . '</td>
		<td>';
			$SQL = "SELECT  * FROM debtortype";//areas.areacode, areas.areadescription";
			$ErrMsg = _('No existe informacion');
			$TransResult = DB_query($SQL,$db,$ErrMsg);
			echo "<select name='Debtortype'>";
				echo "<option selected value='0'>Todos Los Tipos...</option>";
				while ($myrow=DB_fetch_array($TransResult)) {
					if ($myrow['typeid'] == $_POST['Debtortype']){
						echo "<option selected value='" . $myrow['typeid'] . "'>" . $myrow['typename'] . "</option>";
					}else{
						echo "<option value='" . $myrow['typeid'] . "'>" . $myrow['typename'] . "</option>";
					}
				}


	echo '		</select>
		</td>
	</tr>';
	*/
	if (!isset($_POST['clavecliente'])) {
	    $_POST['clavecliente'] = '*';
	}
	/* SELECCION DEL CLIENTE */
	echo "<tr>
		<td>" . _('X Clave de Proveedor') . ":</td>
		<td>";
			echo "<input type=text name='clavecliente' value='".$_POST['clavecliente']."'>:* para todos.";
	echo "	</td>";
	echo "</tr>";
	/*FIN CLAVE CLIENTE*/
	echo '<tr>
		<td>
			<br><b>** DETALLE DEL REPORTE</b>
		</td>
		<td>';
	echo '  </td>
	       </tr>';
	echo '<tr>
		<td>' . _('Nivel de Detalle') . ':' . "</td>
		<td>
			<select tabindex='5' name='DetailedReport'>";
				if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Tag')
					echo "<option selected value='Tag'>" . _('X Unidad de Negocio');
				else
					echo "<option value='Tag'>" . _('X Unidad de Negocio');

				if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Cliente')
					echo "<option selected value='Cliente'>" . _('X Proveedor');
				else
					echo "<option value='Cliente'>" . _('X Proveedor');

				if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Factura')
					echo "<option selected value='Factura'>" . _('X Factura');
				else
					echo "<option value='Factura'>" . _('X Factura');

				if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'FacturaProveedor')
					echo "<option selected value='FacturaProveedor'>" . _('X Factura-Proveedor');
				else
					echo "<option value='FacturaProveedor'>" . _('X Factura-Proveedor');

	echo '		</select>
		</td>
	      </tr>';
	/*echo '<tr>
		<td>' . _('X Tipo Cuenta') . ':' . "</td>
		<td>
			<select tabindex='5' name='DetailedReportCuenta'>";
				if (isset($_POST['DetailedReportCuenta']) and $_POST['DetailedReportCuenta'] == 'Todos')
					echo "<option selected value='Todos'>" . _('Todas Las Cuentas');
				else
					echo "<option value='Todos'>" . _('Todas Las Cuentas');

				if (isset($_POST['DetailedReportCuenta']) and $_POST['DetailedReportCuenta'] == 'Cargos')
					echo "<option selected value='Cargos'>" . _('X Ventas');
				else
					echo "<option value='Cargos'>" . _('X Ventas');

				if (isset($_POST['DetailedReportCuenta']) and $_POST['DetailedReportCuenta'] == 'Documentos')
					echo "<option selected value='Documentos'>" . _('X Documentos');
				else
					echo "<option value='Documentos'>" . _('X Documentos');

				if (isset($_POST['DetailedReportCuenta']) and $_POST['DetailedReportCuenta'] == 'Anticipos')
					echo "<option selected value='Anticipos'>" . _('X Anticipos');
				else
					echo "<option value='Anticipos'>" . _('X Anticipos');

	echo '		</select>
		</td>
	      </tr>';  */
echo '  </table>
		<br><div class="centre"><input tabindex="6" type=submit name="ReportePantalla" value="' . _('Genera Reporte en Pantalla') . '"></div>';
	echo '<br><div class="centre"><input tabindex="7" type=hidden name="PrintPDF" value="' . _('Imprime Archivo PDF') . '"></div>';

If (isset($_POST['ReportePantalla'])){
	$SQL2=" ";
	$SQLBase="SELECT
		S.trandate,
		T.tagref,
		T.tagdescription as tagname,
		D.department as departamento,
		L.legalname,
		R.name as regionname,
		A.areadescription as areadescription,
		ifnull(abonos.abono,0) as abonado,
		suppreference as folio,
		S.supplierno as debtorno,
		M.suppname as name,
		SUM(((ovamount+ovgst)-ifnull(abonos.abono,0))/rate) as saldo,
		SUM(case when datediff('".$fechafin."', trandate) >=1 and datediff('".$fechafin."', trandate) <=30 then (((ovamount+ovgst)-ifnull(abonos.abono,0))/rate)  else 0 end) as vencido1_30,
		SUM(case when datediff('".$fechafin."', trandate) >=31 and datediff('".$fechafin."', trandate) <=60 then (((ovamount+ovgst)-ifnull(abonos.abono,0))/rate)  else 0 end) as vencido1_60,
		SUM(case when datediff('".$fechafin."', trandate) >=61 and datediff('".$fechafin."', trandate) <=90 then (((ovamount+ovgst)-ifnull(abonos.abono,0))/rate)  else 0 end) as vencido1_90,
		SUM(case when datediff('".$fechafin."', trandate) >=91 then (((ovamount+ovgst)-ifnull(abonos.abono,0))/rate)  else 0 end) as vencidomas_90,
		SUM(case when datediff('".$fechafin."', trandate) <0 or datediff('".$fechafin."', trandate) =0 then  (((ovamount+ovgst)-ifnull(abonos.abono,0))/rate) else 0 end)  as novencido
	     FROM    supptrans S";
	$SQLCargo=$SQLBase. "  LEFT JOIN
				(
					SELECT SUM(amt) as abono, transid_allocto as cargo
					FROM suppallocs
					WHERE datealloc<='".$fechafin."'
					GROUP BY transid_allocto
				) as abonos  ON abonos.cargo=S.id";

	$SQLBase="SELECT
		S.trandate,
		T.tagref,
		T.tagdescription as tagname,
		D.department as departamento,
		L.legalname,
		R.name as regionname,
		A.areadescription as areadescription,
		ifnull(abonos.abono,0) as abonado,
		suppreference as folio,
		S.supplierno as debtorno,
		M.suppname as name,
		SUM((ifnull(abonos.abono,ovamount+ovgst))/rate) as saldo,
		SUM(case when datediff('".$fechafin."', trandate) >=1 and datediff('".$fechafin."', trandate) <=30 then ifnull(abonos.abono,ovamount+ovgst)/rate  else 0 end) as vencido1_30,
		SUM(case when datediff('".$fechafin."', trandate) >=31 and datediff('".$fechafin."', trandate) <=60 then ifnull(abonos.abono,ovamount+ovgst)/rate  else 0 end) as vencido1_60,
		SUM(case when datediff('".$fechafin."', trandate) >=61 and datediff('".$fechafin."', trandate) <=90 then ifnull(abonos.abono,ovamount+ovgst)/rate  else 0 end) as vencido1_90,
		SUM(case when datediff('".$fechafin."', trandate) >=91 then ifnull(abonos.abono,ovamount+ovgst)/rate  else 0 end) as vencidomas_90,
		SUM(case when datediff('".$fechafin."', trandate) <0 or datediff('".$fechafin."', trandate) =0 then  ifnull(abonos.abono,ovamount+ovgst)/rate else 0 end)  as novencido
	     FROM    supptrans S";



	$SQLAbono=$SQLBase. "  LEFT JOIN
			(
				SELECT SUM(amt*-1) as abono, transid_allocfrom as cargo
				FROM suppallocs
				WHERE datealloc<='".$fechafin."'
				GROUP BY transid_allocfrom
			) as abonos  ON abonos.cargo=S.id";

	$SQLAbonos2="SELECT
		S.trandate ,
		T.tagref,
		T.tagdescription as tagname,
		D.department as departamento,
		L.legalname,
		R.name as regionname,
		A.areadescription as areadescription,
		ifnull(abonos.abono,0) as abonado,
		suppreference as folio,
		S.supplierno as debtorno,
		M.suppname as name,
		SUM((ifnull((ovamount+ovgst)-abonos.abono,ovamount+ovgst))/rate) as saldo,
		SUM(case when datediff('".$fechafin."', trandate) >=1 and datediff('".$fechafin."', trandate) <=30 then ((ifnull((ovamount+ovgst)-abonos.abono,ovamount+ovgst))/rate) else 0 end) as vencido1_30,
		SUM(case when datediff('".$fechafin."', trandate) >=31 and datediff('".$fechafin."', trandate) <=60 then ((ifnull((ovamount+ovgst)-abonos.abono,ovamount+ovgst))/rate) else 0 end) as vencido1_60,
		SUM(case when datediff('".$fechafin."', trandate) >=61 and datediff('".$fechafin."', trandate) <=90 then ((ifnull((ovamount+ovgst)-abonos.abono,ovamount+ovgst))/rate)  else 0 end) as vencido1_90,
		SUM(case when datediff('".$fechafin."', trandate) >=91 then ((ifnull((ovamount+ovgst)-abonos.abono,ovamount+ovgst))/rate)  else 0 end) as vencidomas_90,
		SUM(case when datediff('".$fechafin."', trandate) <0 or datediff('".$fechafin."', trandate) =0 then  ((ifnull((ovamount+ovgst)-abonos.abono,ovamount+ovgst))/rate) else 0 end)  as novencido
	     FROM    supptrans S";



	$SQLAbonos2=$SQLAbonos2. "  LEFT JOIN
			(
				SELECT SUM(amt*-1) as abono, transid_allocfrom as cargo
				FROM suppallocs
				WHERE datealloc<='".$fechafin."'
				GROUP BY transid_allocfrom
			) as abonos  ON abonos.cargo=S.id";


      $SQLFrom="INNER JOIN suppliers M ON S.supplierno=M.supplierid
		INNER JOIN tags T ON T.tagref=S.tagref
		INNER JOIN departments D  ON D.u_department=T.u_department
		INNER JOIN legalbusinessunit L ON L.legalid=T.legalid
		INNER JOIN areas A ON A.areacode=T.areacode
		INNER JOIN regions R ON R.regioncode=A.regioncode
	    WHERE
		 S.origtrandate<='".$fechafin."' AND S.transtext not like '%CANCELAD%'
		 ";






	if (isset($_POST['legalid']) and $_POST['legalid']!=0){
		$SQL2=$SQL2." AND T.legalid=".$_POST['legalid'];
		//echo "entra";
	}
	if (isset($_POST['xRegion']) and $_POST['xRegion']!=0){
		$SQL2= $SQL2." AND R.regioncode='".$_POST['xRegion']."'";
	}
	if (isset($_POST['xArea']) and $_POST['xArea']!=0){
		$SQL2=$SQL2." AND A.areacode='".$_POST['xArea']."'";
	}
	if (isset($_POST['xDepartamento']) and $_POST['xDepartamento']!=0){
		$SQL2=$SQL2." AND D.u_department='".$_POST['xDepartamento']."'";
	}
	if (isset($_POST['unidadnegocio']) and $_POST['unidadnegocio']!=0){
		$SQL2=$SQL2." AND T.tagref='".$_POST['unidadnegocio']."'";
	}
	if (isset($_POST['clavecliente']) and $_POST['clavecliente']!='*'){
		$SQL2=$SQL2." AND S.supplierno='".$_POST['clavecliente']."'";
	}/*
	if (isset($_POST['Debtortype']) and $_POST['Debtortype']!=0){
		$SQL2=$SQL2." AND M.typeid='".$_POST['Debtortype']."'";
	}*/
	$SQLcondiciones=$SQL2;
	$SQL=$SQL." ".$SQL2;
	$SQLAgrupar=' GROUP BY tagname,folio,name,debtorno
	HAVING abs(saldo)>.9
	';

	$SQLCargos=$SQLCargo.' '.$SQLFrom.' '.$SQL2 .' AND S.type in (20,34,470) ' . $SQLAgrupar.' ';
	$SQLAbonos=$SQLAbono.' '.$SQLFrom.' '.$SQL2  .' AND S.type in (22,24,32,33,480,410)  and abonos.abono is null '. $SQLAgrupar;
	//$SQLAbonos=$SQLAbono.' '.$SQLFrom.' '.$SQL2  .' AND S.type in (11,12,13,80,420,430,450,460) '. $SQLAgrupar;
	$SQLAbonos2=$SQLAbonos2.' '.$SQLFrom.' '.$SQL2  .' AND S.type in (22,24,32,33,480,121,410) and abs(ovamount+ovgst)-abs(abonos.abono)>.9  and abonos.abono is not null'. $SQLAgrupar;
	//echo $SQLAbonos;
	$SQL2='';
	/*if ($_POST['DetailedReport'] == 'Tag') {
		$SQL2=" GROUP BY T.tagdescription,concat('SAT:',ifnull(S.folio,''),'<br> ERP:',S.transno),S.debtorno, M.name";
	}elseif ($_POST['DetailedReport'] == 'Cliente') {
		$SQL2=" GROUP BY T.tagdescription,concat('SAT:',ifnull(S.folio,''),'<br> ERP:',S.transno),S.debtorno, M.name ";
	}else{
		$SQL2=" GROUP BY T.tagdescription,concat('SAT:',ifnull(S.folio,''),'<br> ERP:',S.transno),S.debtorno, M.name ";
	}*/

	$SQL=$SQLCargos.' UNION '.$SQLAbonos . ' UNION '.$SQLAbonos2;
	$SQLconsultas=$SQLCargos.'<br> UNION <br>'.$SQLAbonos . ' <br>UNION <br>'.$SQLAbonos2;
	$SQL=$SQL.' ORDER BY tagname,name,folio ';

// 	echo '<br><pre>'.$SQL.'<br>';

	$ReportResult = DB_query($SQL,$db,'','',False,False); /*dont trap errors */
	if (DB_error_no($db) !=0) {
		$title = _('Reporte General de Antig�edad de Cartera ') . ' - ' . _('Reporte de Problema') ;
		prnMsg(_('Los detalles de cartera no se pudieron recuperar porque el SQL fallo') .  ' ' . DB_error_msg($db),'error');
		exit;
	}
	$DoctoTotPagos = 0;
	$VenceTotPagos = 0;
	$ProvTotPagos = 0;
	$RegionTotPagos = 0;
	$TotPagos = 0;

	echo "<table border='0' cellpadding='2' cellspacing='2'>";
	echo "<tr>";
	echo "<td width='2'><img src='images/red_flag_16.png' width='16' height='16'></td>";
	echo "<td style='font-size:8pt; font-weight:normal;'>Mayor a 90 dias</td>";
	echo "<td width='2'><img src='images/brown_flag_16.png' width='16' height='16'></td>";
	echo "<td style='font-size:8pt; font-weight:normal;'>Entre 60 y 90 dias</td>";
	echo "<td width='2'><img src='images/orange_flag_16.png' width='16' height='16'></td>";
	echo "<td style='font-size:8pt; font-weight:normal;'>Entre 30 y 60 dias</td>";
	echo "<td width='2'><img src='images/green_flag_16.png' width='16' height='16'></td>";
	echo "<td style='font-size:8pt; font-weight:normal;'>Menor a 30 Dias</td>";
	echo "</tr>";
	echo "</table>";





	echo '<table cellpadding=2 border=1>';

	    $headerEncabezado = '<tr>
			<th colspan=1><b></b></th>
			<th colspan=1><b>' . _('Departamento') . '</b></th>
			<th colspan=1><b>' . _('Region') . '</b></th>
			<th colspan=2><b>' . _('Area') . '</b></th>
			<th colspan=3><b>' . _('Unidad Negocio') . '</b></th>
			</tr>';

      if ($_POST['DetailedReport'] == 'Tag') {
	    $headerDetalle = '<tr>
			<th colspan=1><b></b></th>
			<th colspan=1><b>' . _('Unidad Negocio') . '</b></th>
			<th><b>' . _('Saldo') . '</b></th>
			<th><b>' . _('No Vencido') . '</b></th>
			<th><b>' . _('1-30 Dias') . '</b></th>
			<th><b>' . _('> 30 Dias') . '</b></th>
			<th><b>' . _('> 60 Dias') . '</b></th>
			<th><b>' . _('> 90 Dias') . '</b></th>
			</tr>';
      } elseif ($_POST['DetailedReport'] == 'Cliente') {
	    $headerDetalle = '<tr>
			<th colspan=1><b>&nbsp;</b></th>
			<th colspan=2 style="font-size:8pt;"><b>' . _('PROVEEDOR') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('SALDO') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('NO VENCIDO') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('1-30 DIAS') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('> 30 DIAS') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('> 60 DIAS') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('> 90 DIAS') . '</b></th>
			</tr>';
        } elseif ($_POST['DetailedReport'] == 'FacturaProveedor'){
        	$headerDetalle = '<tr>
        	<th colspan=1><b>&nbsp;</b></th>
			<th  style="font-size:8pt;"><b>' . _('PROVEEDOR') . '</b></th>
			<th ><b>' . _('Folio') . '</b></th>
			<th><b>' . _('Saldo') . '</b></th>
			<th><b>' . _('No Vencido') . '</b></th>
			<th><b>' . _('1-30 Dias') . '</b></th>
			<th><b>' . _('> 30 Dias') . '</b></th>
			<th><b>' . _('> 60 Dias') . '</b></th>
			<th><b>' . _('> 90 Dias') . '</b></th>
			</tr>';

        }else{

	    $headerDetalle = '<tr>
			<th colspan=1><b></b></th>
			<th colspan=1><b>' . _('Folio') . '</b></th>
			<th><b>' . _('Saldo') . '</b></th>
			<th><b>' . _('No Vencido') . '</b></th>
			<th><b>' . _('1-30 Dias') . '</b></th>
			<th><b>' . _('> 30 Dias') . '</b></th>
			<th><b>' . _('> 60 Dias') . '</b></th>
			<th><b>' . _('> 90 Dias') . '</b></th>
			</tr>';
	}

	$i = 0;
	$antRegion = '';
	$antProv = '';
	$antVence = '';
	$antNombre = '';
	$lineasConMismoDocumento = 0;
	$lineasantesdeencabezado = 0;
	$primeraEntrada = 1;
	/************************************/
	/* SELECCION DE ESTATUS DE DOCUMENTO*/
	/************************************/
	$AnteriorLegalName = "";
	$AnteriorRegionName = "";
	$AnteriorAreaDesc = "";
	$AnteriorDepartamento = "";
	$AnteriorUnidadNegocio = "";

	$AnteriorCliente = "";
	$AnteriorFactura = "";
	$totalQuantity = 0;
	$totalAmount = 0;
	$totalIVA = 0;
	$totalCost = 0;
	$totalCostAvg = 0;
	$FtotalQuantity = 0;
	$FtotalAmount = 0;
	$FtotalIVA = 0;
	$FtotalCost = 0;
	$FtotalCostAvg = 0;
	$GtotalQuantity = 0;
	$GtotalAmount = 0;
	$GtotalIVA = 0;
	$GtotalCost = 0;
	$GtotalCostAvg = 0;

	$saldoTotalXCliente=0;
	$saldonovencidoXCliente=0;
	$saldovencido1_30XCliente=0;
	$saldovencido1_60XCliente=0;
	$saldovencido1_90XCliente=0;
	$saldovencidomas_90XCliente=0;

	$saldoTotalXtag=0;
	$saldonovencidoXtag=0;
	$saldovencido1_30Xtag=0;
	$saldovencido1_60Xtag=0;
	$saldovencido1_90Xtag=0;
	$saldovencidomas_90Xtag=0;


	echo $headerDetalle;

	$cuenta=0;
	//echo "<br>detalle" . $_POST['DetailedReport'];
	while ($SlsAnalysis = DB_fetch_array($ReportResult,$db)){
		if ($_POST['DetailedReport'] == 'Cliente') {
			if ($AnteriorRegionName != $SlsAnalysis['regionname']){
				if ($cuenta > 0){
					if ($saldovencidomas_90XCliente > 1){
						$flag = "<img src='images/red_flag_16.png' width='16' height='16'>";
					}else{
						if ($saldovencido1_90XCliente > 1){
							$flag = "<img src='images/brown_flag_16.png' width='16' height='16'>";
						}else{
							if ($saldovencido1_60XCliente > 1){
								$flag = "<img src='images/orange_flag_16.png' width='16' height='16'>";
							}else{
								if ($saldovencido1_30XCliente > 1){
									$flag = "<img src='images/green_flag_16.png' width='16' height='16'>";
								}else{
									$flag = "&nbsp;";
								}
							}
						}
					}
					$registro = "<tr><td width='2'>" . $flag . "</td>";

					if (($antProv != $SlsAnalysis['debtorno']) and ($antProv != "")){
						$registro = $registro . '<td style="text-align:left; font-size:8pt; font-weight:normal;">' . $antProv . '</td>';
						$registro = $registro . '<td style="text-align:left; font-size:8pt; font-weight:normal;">' . $AnteriorCliente  . '</td>';
						$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldoTotalXCliente,2)  . '</td>';
						$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldonovencidoXCliente,2)  . '</td>';
						$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencido1_30XCliente,2)  . '</td>';
						$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencido1_60XCliente,2)  . '</td>';
						$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencido1_90XCliente,2)  . '</td>';
						$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencidomas_90XCliente,2)  . '</td>';
						$registro = $registro . "</tr>";
						echo $registro;
						$saldoTotalXCliente = 0;
						$saldonovencidoXCliente=0;
						$saldovencido1_30XCliente=0;
						$saldovencido1_60XCliente=0;
						$saldovencido1_90XCliente=0;
						$saldovencidomas_90XCliente=0;
						$antProv = $SlsAnalysis['debtorno'];
						$AnteriorCliente = $SlsAnalysis['name'];
					}
					$antProv  = "";
					echo '<tr bgcolor="#FFFF38" >';
					echo '<td colspan="3"  nowrap class="number"><b> '. _('TOTAL') . ' ' . $AnteriorRegionName . ': &nbsp;</b></td>';

					echo '<td nowrap class="number"> $' .number_format($saldoTotalxRegion,2)  . '</td>';
					echo '<td nowrap class="number"> $' .number_format($saldonovencidoxRegion,2)  . '</td>';
					echo '<td nowrap class="number"> $' .number_format($saldovencido1_30xRegion,2)  . '</td>';
					echo '<td nowrap class="number"> $' .number_format($saldovencido1_60xRegion,2)  . '</td>';
					echo '<td nowrap class="number"> $' .number_format($saldovencido1_90xRegion,2)  . '</td>';
					echo '<td nowrap class="number"> $' .number_format($saldovencidomas_90xRegion,2)  . '</td>';
					echo '</tr>';

					$saldoTotalxRegion = 0;
					$saldonovencidoxRegion = 0;
					$saldovencido1_30xRegion = 0;
					$saldovencido1_60xRegion = 0;
					$saldovencido1_90xRegion = 0;
					$saldovencidomas_90xRegion = 0;

				}
				echo "<tr>";
					echo "<td colspan='8' style='font-size:8pt;'>" . $SlsAnalysis['regionname'] . "</td>";
				echo "</tr>";
				$AnteriorRegionName = $SlsAnalysis['regionname'];
			}

			if ($saldovencidomas_90XCliente > 1){
				$flag = "<img src='images/red_flag_16.png' width='16' height='16'>";
			}else{
				if ($saldovencido1_90XCliente > 1){
					$flag = "<img src='images/brown_flag_16.png' width='16' height='16'>";
				}else{
					if ($saldovencido1_60XCliente > 1){
						$flag = "<img src='images/orange_flag_16.png' width='16' height='16'>";
					}else{
						if ($saldovencido1_30XCliente > 1){
							$flag = "<img src='images/green_flag_16.png' width='16' height='16'>";
						}else{
							$flag = "&nbsp;";
						}
					}
				}
			}
			$registro = "<tr><td width='2'>" . $flag . "</td>";


			if (($antProv != $SlsAnalysis['debtorno']) and ($antProv != "")){
				$registro = $registro . '<td style="text-align:left; font-size:8pt; font-weight:normal;">' . $antProv . '</td>';
				$registro = $registro . '<td style="text-align:left; font-size:8pt; font-weight:normal;">' . $AnteriorCliente  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldoTotalXCliente,2)  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldonovencidoXCliente,2)  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencido1_30XCliente,2)  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencido1_60XCliente,2)  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencido1_90XCliente,2)  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencidomas_90XCliente,2)  . '</td>';
				$registro = $registro . "</tr>";
				echo $registro;
				$saldoTotalXCliente = 0;
				$saldonovencidoXCliente=0;
				$saldovencido1_30XCliente=0;
				$saldovencido1_60XCliente=0;
				$saldovencido1_90XCliente=0;
				$saldovencidomas_90XCliente=0;
				$antProv = $SlsAnalysis['debtorno'];
				$AnteriorCliente = $SlsAnalysis['name'];
			}
			if ($antProv == ""){
				$antProv = $SlsAnalysis['debtorno'];
				$AnteriorCliente = $SlsAnalysis['name'];
			}

			$saldoTotalXCliente = $saldoTotalXCliente +  $SlsAnalysis['saldo'];
			$saldonovencidoXCliente = $saldonovencidoXCliente +  $SlsAnalysis['novencido'];
			$saldovencido1_30XCliente = $saldovencido1_30XCliente + $SlsAnalysis['vencido1_30'];
			$saldovencido1_60XCliente = $saldovencido1_60XCliente + $SlsAnalysis['vencido1_60'];
			$saldovencido1_90XCliente = $saldovencido1_90XCliente + $SlsAnalysis['vencido1_90'];
			$saldovencidomas_90XCliente = $saldovencidomas_90XCliente + $SlsAnalysis['vencidomas_90'];

			$saldoTotal=$saldoTotal+$SlsAnalysis['saldo'];
			$saldonovencido=$saldonovencido+$SlsAnalysis['novencido'];
			$saldovencido1_30=$saldovencido1_30+$SlsAnalysis['vencido1_30'];
			$saldovencido1_60=$saldovencido1_60+$SlsAnalysis['vencido1_60'];
			$saldovencido1_90=$saldovencido1_90+$SlsAnalysis['vencido1_90'];
			$saldovencidomas_90=$saldovencidomas_90+$SlsAnalysis['vencidomas_90'];

			$saldoTotalxRegion = $saldoTotalxRegion + $SlsAnalysis['saldo'];
			$saldonovencidoxRegion = $saldonovencidoxRegion + $SlsAnalysis['novencido'];
			$saldovencido1_30xRegion = $saldovencido1_30xRegion + $SlsAnalysis['vencido1_30'];
			$saldovencido1_60xRegion = $saldovencido1_60xRegion + $SlsAnalysis['vencido1_60'];
			$saldovencido1_90xRegion = $saldovencido1_90xRegion + $SlsAnalysis['vencido1_90'];
			$saldovencidomas_90xRegion = $saldovencidomas_90xRegion + $SlsAnalysis['vencidomas_90'];
		}


		if ($_POST['DetailedReport'] == 'Tag') {
			if ($AnteriorRegionName != $SlsAnalysis['regionname']){
				if ($cuenta > 0){
					if ($saldovencidomas_90Xtag > 1){
						$flag = "<img src='images/red_flag_16.png' width='16' height='16'>";
					}else{
						if ($saldovencido1_90Xtag > 1){
							$flag = "<img src='images/brown_flag_16.png' width='16' height='16'>";
						}else{
							if ($saldovencido1_60Xtag > 1){
								$flag = "<img src='images/orange_flag_16.png' width='16' height='16'>";
							}else{
								if ($saldovencido1_30Xtag > 1){
									$flag = "<img src='images/green_flag_16.png' width='16' height='16'>";
								}else{
									$flag = "&nbsp;";
								}
							}
						}
					}
					$registro = "<tr><td width='2'>" . $flag . "</td>";

					if (($AnteriorUnidadNegocio != $SlsAnalysis['tagname']) and ($AnteriorUnidadNegocio != "")){
						$registro = $registro . '<td style="text-align:left; font-size:8pt; font-weight:normal;">' . $AnteriorUnidadNegocio  . '</td>';
						$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldoTotalXtag,2)  . '</td>';
						$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldonovencidoXtag,2)  . '</td>';
						$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencido1_30Xtag,2)  . '</td>';
						$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencido1_60Xtag,2)  . '</td>';
						$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencido1_90Xtag,2)  . '</td>';
						$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencidomas_90Xtag,2)  . '</td>';
						$registro = $registro . "</tr>";
						echo $registro;

						$saldoTotalXtag=0;
						$saldonovencidoXtag=0;
						$saldovencido1_30Xtag=0;
						$saldovencido1_60Xtag=0;
						$saldovencido1_90Xtag=0;
						$saldovencidomas_90Xtag=0;


						$AnteriorUnidadNegocio = $SlsAnalysis['tagname'];

					}
					$AnteriorUnidadNegocio  = "";
					echo '<tr bgcolor="#FFFF38" >';
					echo '<td colspan="2"  nowrap class="number"><b> '. _('TOTAL') . ' ' . $AnteriorRegionName . ': &nbsp;</b></td>';

					echo '<td nowrap class="number"> $' .number_format($saldoTotalxRegion,2)  . '</td>';
					echo '<td nowrap class="number"> $' .number_format($saldonovencidoxRegion,2)  . '</td>';
					echo '<td nowrap class="number"> $' .number_format($saldovencido1_30xRegion,2)  . '</td>';
					echo '<td nowrap class="number"> $' .number_format($saldovencido1_60xRegion,2)  . '</td>';
					echo '<td nowrap class="number"> $' .number_format($saldovencido1_90xRegion,2)  . '</td>';
					echo '<td nowrap class="number"> $' .number_format($saldovencidomas_90xRegion,2)  . '</td>';
					echo '</tr>';

					$saldoTotalxRegion = 0;
					$saldonovencidoxRegion = 0;
					$saldovencido1_30xRegion = 0;
					$saldovencido1_60xRegion = 0;
					$saldovencido1_90xRegion = 0;
					$saldovencidomas_90xRegion = 0;

				}
				echo "<tr>";
					echo "<td colspan='8' style='font-size:8pt;'>" . $SlsAnalysis['regionname'] . "</td>";
				echo "</tr>";
				$AnteriorRegionName = $SlsAnalysis['regionname'];
			}

			if ($saldovencidomas_90Xtag > 1){
				$flag = "<img src='images/red_flag_16.png' width='16' height='16'>";
			}else{
				if ($saldovencido1_90Xtag > 1){
					$flag = "<img src='images/brown_flag_16.png' width='16' height='16'>";
				}else{
					if ($saldovencido1_60Xtag > 1){
						$flag = "<img src='images/orange_flag_16.png' width='16' height='16'>";
					}else{
						if ($saldovencido1_30Xtag > 1){
							$flag = "<img src='images/green_flag_16.png' width='16' height='16'>";
						}else{
							$flag = "&nbsp;";
						}
					}
				}
			}
			$registro = "<tr><td width='2'>" . $flag . "</td>";


			if (($AnteriorUnidadNegocio != $SlsAnalysis['tagname']) and ($AnteriorUnidadNegocio != "")){
				$registro = $registro . '<td style="text-align:left; font-size:8pt; font-weight:normal;">' . $AnteriorUnidadNegocio  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldoTotalXtag,2)  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldonovencidoXtag,2)  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencido1_30Xtag,2)  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencido1_60Xtag,2)  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencido1_90Xtag,2)  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencidomas_90Xtag,2)  . '</td>';
				$registro = $registro . "</tr>";
				echo $registro;
				$saldoTotalXtag=0;
				$saldonovencidoXtag=0;
				$saldovencido1_30Xtag=0;
				$saldovencido1_60Xtag=0;
				$saldovencido1_90Xtag=0;
				$saldovencidomas_90Xtag=0;
				$AnteriorUnidadNegocio = $SlsAnalysis['tagname'];
			}
			if ($AnteriorUnidadNegocio == ""){
				$AnteriorUnidadNegocio = $SlsAnalysis['tagname'];

			}

			$saldoTotalXtag = $saldoTotalXtag +  $SlsAnalysis['saldo'];
			$saldonovencidoXtag = $saldonovencidoXtag +  $SlsAnalysis['novencido'];
			$saldovencido1_30Xtag = $saldovencido1_30Xtag + $SlsAnalysis['vencido1_30'];
			$saldovencido1_60Xtag = $saldovencido1_60Xtag + $SlsAnalysis['vencido1_60'];
			$saldovencido1_90Xtag = $saldovencido1_90Xtag + $SlsAnalysis['vencido1_90'];
			$saldovencidomas_90Xtag = $saldovencidomas_90Xtag + $SlsAnalysis['vencidomas_90'];

			$saldoTotal=$saldoTotal+$SlsAnalysis['saldo'];
			$saldonovencido=$saldonovencido+$SlsAnalysis['novencido'];
			$saldovencido1_30=$saldovencido1_30+$SlsAnalysis['vencido1_30'];
			$saldovencido1_60=$saldovencido1_60+$SlsAnalysis['vencido1_60'];
			$saldovencido1_90=$saldovencido1_90+$SlsAnalysis['vencido1_90'];
			$saldovencidomas_90=$saldovencidomas_90+$SlsAnalysis['vencidomas_90'];

			$saldoTotalxRegion = $saldoTotalxRegion + $SlsAnalysis['saldo'];
			$saldonovencidoxRegion = $saldonovencidoxRegion + $SlsAnalysis['novencido'];
			$saldovencido1_30xRegion = $saldovencido1_30xRegion + $SlsAnalysis['vencido1_30'];
			$saldovencido1_60xRegion = $saldovencido1_60xRegion + $SlsAnalysis['vencido1_60'];
			$saldovencido1_90xRegion = $saldovencido1_90xRegion + $SlsAnalysis['vencido1_90'];
			$saldovencidomas_90xRegion = $saldovencidomas_90xRegion + $SlsAnalysis['vencidomas_90'];
		}




		if ($_POST['DetailedReport'] == 'Factura') {
			if ($AnteriorRegionName != $SlsAnalysis['regionname']){
				if ($cuenta > 0){
					echo '<tr bgcolor="#FFFF38" >';
					echo '<td colspan="2"  nowrap class="number"><b> '. _('TOTAL') . ' ' . $AnteriorRegionName . ': &nbsp;</b></td>';

					echo '<td nowrap class="number"> $' .number_format($saldoTotalxRegion,2)  . '</td>';
					echo '<td nowrap class="number"> $' .number_format($saldonovencidoxRegion,2)  . '</td>';
					echo '<td nowrap class="number"> $' .number_format($saldovencido1_30xRegion,2)  . '</td>';
					echo '<td nowrap class="number"> $' .number_format($saldovencido1_60xRegion,2)  . '</td>';
					echo '<td nowrap class="number"> $' .number_format($saldovencido1_90xRegion,2)  . '</td>';
					echo '<td nowrap class="number"> $' .number_format($saldovencidomas_90xRegion,2)  . '</td>';
					echo '</tr>';

					$saldoTotalxRegion = 0;
					$saldonovencidoxRegion = 0;
					$saldovencido1_30xRegion = 0;
					$saldovencido1_60xRegion = 0;
					$saldovencido1_90xRegion = 0;
					$saldovencidomas_90xRegion = 0;

				}
				echo "<tr>";
					echo "<td colspan='8' style='font-size:8pt;'>" . $SlsAnalysis['regionname'] . "</td>";
				echo "</tr>";
				$AnteriorRegionName = $SlsAnalysis['regionname'];
			}

			if ($SlsAnalysis['vencidomas_90'] > 1){
				$flag = "<img src='images/red_flag_16.png' width='16' height='16'>";
			}else{
				if ($SlsAnalysis['vencido1_90'] > 1){
					$flag = "<img src='images/brown_flag_16.png' width='16' height='16'>";
				}else{
					if ($SlsAnalysis['vencido1_60'] > 1){
						$flag = "<img src='images/orange_flag_16.png' width='16' height='16'>";
					}else{
						if ($SlsAnalysis['vencido1_30'] > 1){
							$flag = "<img src='images/green_flag_16.png' width='16' height='16'>";
						}else{
							$flag = "&nbsp;";
						}
					}
				}
			}
			$registro = "<tr><td width='2'>" . $flag . "</td>";
			$registro = $registro . '<td style="text-align:left; font-size:8pt; font-weight:normal;">' . $SlsAnalysis['folio']  . '</td>';
			$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($SlsAnalysis['saldo'],2)  . '</td>';
			$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($SlsAnalysis['novencido'],2)  . '</td>';
			$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($SlsAnalysis['vencido1_30'],2)  . '</td>';
			$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($SlsAnalysis['vencido1_60'],2)  . '</td>';
			$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($SlsAnalysis['vencido1_90'],2)  . '</td>';
			$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($SlsAnalysis['vencidomas_90'],2)  . '</td>';
			$registro = $registro . "</tr>";
			echo $registro;

			$saldoTotal=$saldoTotal+$SlsAnalysis['saldo'];
			$saldonovencido=$saldonovencido+$SlsAnalysis['novencido'];
			$saldovencido1_30=$saldovencido1_30+$SlsAnalysis['vencido1_30'];
			$saldovencido1_60=$saldovencido1_60+$SlsAnalysis['vencido1_60'];
			$saldovencido1_90=$saldovencido1_90+$SlsAnalysis['vencido1_90'];
			$saldovencidomas_90=$saldovencidomas_90+$SlsAnalysis['vencidomas_90'];

			$saldoTotalxRegion = $saldoTotalxRegion + $SlsAnalysis['saldo'];
			$saldonovencidoxRegion = $saldonovencidoxRegion + $SlsAnalysis['novencido'];
			$saldovencido1_30xRegion = $saldovencido1_30xRegion + $SlsAnalysis['vencido1_30'];
			$saldovencido1_60xRegion = $saldovencido1_60xRegion + $SlsAnalysis['vencido1_60'];
			$saldovencido1_90xRegion = $saldovencido1_90xRegion + $SlsAnalysis['vencido1_90'];
			$saldovencidomas_90xRegion = $saldovencidomas_90xRegion + $SlsAnalysis['vencidomas_90'];
		}

		if ($_POST['DetailedReport'] == 'FacturaProveedor') {
			if ($AnteriorRegionName != $SlsAnalysis['regionname']){
				if ($cuenta > 0){
					echo '<tr bgcolor="#FFFF38" >';
					echo '<td colspan="3"  nowrap class="number"><b> '. _('TOTAL') . ' ' . $AnteriorRegionName . ': &nbsp;</b></td>';

					echo '<td nowrap class="number"> $' .number_format($saldoTotalxRegion,2)  . '</td>';
					echo '<td nowrap class="number"> $' .number_format($saldonovencidoxRegion,2)  . '</td>';
					echo '<td nowrap class="number"> $' .number_format($saldovencido1_30xRegion,2)  . '</td>';
					echo '<td nowrap class="number"> $' .number_format($saldovencido1_60xRegion,2)  . '</td>';
					echo '<td nowrap class="number"> $' .number_format($saldovencido1_90xRegion,2)  . '</td>';
					echo '<td nowrap class="number"> $' .number_format($saldovencidomas_90xRegion,2)  . '</td>';
					echo '</tr>';

					$saldoTotalxRegion = 0;
					$saldonovencidoxRegion = 0;
					$saldovencido1_30xRegion = 0;
					$saldovencido1_60xRegion = 0;
					$saldovencido1_90xRegion = 0;
					$saldovencidomas_90xRegion = 0;

				}
				echo "<tr>";
				echo "<td colspan='9' style='font-size:8pt;'>" . $SlsAnalysis['regionname'] . "</td>";
				echo "</tr>";
				$AnteriorRegionName = $SlsAnalysis['regionname'];
			}

			if ($SlsAnalysis['vencidomas_90'] > 1){
				$flag = "<img src='images/red_flag_16.png' width='16' height='16'>";
			}else{
				if ($SlsAnalysis['vencido1_90'] > 1){
					$flag = "<img src='images/brown_flag_16.png' width='16' height='16'>";
				}else{
					if ($SlsAnalysis['vencido1_60'] > 1){
						$flag = "<img src='images/orange_flag_16.png' width='16' height='16'>";
					}else{
						if ($SlsAnalysis['vencido1_30'] > 1){
							$flag = "<img src='images/green_flag_16.png' width='16' height='16'>";
						}else{
							$flag = "&nbsp;";
						}
					}
				}
			}
			$registro = "<tr><td width='2'>" . $flag . "</td>";
			$registro = $registro . '<td style="text-align:left; font-size:8pt; font-weight:normal;">' . $SlsAnalysis['name']  . '</td>';
			$registro = $registro . '<td style="text-align:left; font-size:8pt; font-weight:normal;">' . $SlsAnalysis['folio']  . '</td>';
			$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($SlsAnalysis['saldo'],2)  . '</td>';
			$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($SlsAnalysis['novencido'],2)  . '</td>';
			$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($SlsAnalysis['vencido1_30'],2)  . '</td>';
			$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($SlsAnalysis['vencido1_60'],2)  . '</td>';
			$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($SlsAnalysis['vencido1_90'],2)  . '</td>';
			$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($SlsAnalysis['vencidomas_90'],2)  . '</td>';
			$registro = $registro . "</tr>";
			echo $registro;

			$saldoTotal=$saldoTotal+$SlsAnalysis['saldo'];
			$saldonovencido=$saldonovencido+$SlsAnalysis['novencido'];
			$saldovencido1_30=$saldovencido1_30+$SlsAnalysis['vencido1_30'];
			$saldovencido1_60=$saldovencido1_60+$SlsAnalysis['vencido1_60'];
			$saldovencido1_90=$saldovencido1_90+$SlsAnalysis['vencido1_90'];
			$saldovencidomas_90=$saldovencidomas_90+$SlsAnalysis['vencidomas_90'];

			$saldoTotalxRegion = $saldoTotalxRegion + $SlsAnalysis['saldo'];
			$saldonovencidoxRegion = $saldonovencidoxRegion + $SlsAnalysis['novencido'];
			$saldovencido1_30xRegion = $saldovencido1_30xRegion + $SlsAnalysis['vencido1_30'];
			$saldovencido1_60xRegion = $saldovencido1_60xRegion + $SlsAnalysis['vencido1_60'];
			$saldovencido1_90xRegion = $saldovencido1_90xRegion + $SlsAnalysis['vencido1_90'];
			$saldovencidomas_90xRegion = $saldovencidomas_90xRegion + $SlsAnalysis['vencidomas_90'];
		}

		$cuenta=$cuenta+1;
	} /*end while loop */
// totales generales

	if ($cuenta > 0){
		if ($_POST['DetailedReport'] == 'Cliente') {
			if ($saldovencidomas_90XCliente > 1){
				$flag = "<img src='images/red_flag_16.png' width='16' height='16'>";
			}else{
				if ($saldovencido1_90XCliente > 1){
					$flag = "<img src='images/brown_flag_16.png' width='16' height='16'>";
				}else{
					if ($saldovencido1_60XCliente > 1){
						$flag = "<img src='images/orange_flag_16.png' width='16' height='16'>";
					}else{
						if ($saldovencido1_30XCliente > 1){
							$flag = "<img src='images/green_flag_16.png' width='16' height='16'>";
						}else{
							$flag = "&nbsp;";
						}
					}
				}
			}
			$registro = "<tr><td width='2'>" . $flag . "</td>";

			if (($antProv != $SlsAnalysis['debtorno']) and ($antProv != "")){
				$registro = $registro . '<td style="text-align:left; font-size:8pt; font-weight:normal;">' . $antProv . '</td>';
				$registro = $registro . '<td style="text-align:left; font-size:8pt; font-weight:normal;">' . $AnteriorCliente  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldoTotalXCliente,2)  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldonovencidoXCliente,2)  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencido1_30XCliente,2)  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencido1_60XCliente,2)  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencido1_90XCliente,2)  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencidomas_90XCliente,2)  . '</td>';
				$registro = $registro . "</tr>";
				echo $registro;
				$saldoTotalXCliente = 0;
				$saldonovencidoXCliente=0;
				$saldovencido1_30XCliente=0;
				$saldovencido1_60XCliente=0;
				$saldovencido1_90XCliente=0;
				$saldovencidomas_90XCliente=0;
				$antProv = $SlsAnalysis['debtorno'];
				$AnteriorCliente = $SlsAnalysis['name'];
			}
			$antProv  = "";
			echo '<tr bgcolor="#FFFF38" >';
			echo '<td colspan="3"  nowrap class="number"><b> '. _('TOTAL') . ' ' . $AnteriorRegionName . ': &nbsp;</b></td>';

			echo '<td nowrap class="number"> $' .number_format($saldoTotalxRegion,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldonovencidoxRegion,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_30xRegion,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_60xRegion,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_90xRegion,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencidomas_90xRegion,2)  . '</td>';
			echo '</tr>';
			echo '<tr bgcolor="#FF5A38" >';
			echo '<td colspan="3"  nowrap class="number"><b> '. _('TOTALES') . ': &nbsp;</b></td>';
			echo '<td nowrap class="number"> $' .number_format($saldoTotal,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldonovencido,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_30,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_60,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_90,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencidomas_90,2)  . '</td>';
			echo '</tr>';
		}

		if ($_POST['DetailedReport'] == 'Tag') {
			if ($saldovencidomas_90Xtag > 1){
				$flag = "<img src='images/red_flag_16.png' width='16' height='16'>";
			}else{
				if ($saldovencido1_90Xtag > 1){
					$flag = "<img src='images/brown_flag_16.png' width='16' height='16'>";
				}else{
					if ($saldovencido1_60Xtag > 1){
						$flag = "<img src='images/orange_flag_16.png' width='16' height='16'>";
					}else{
						if ($saldovencido1_30Xtag > 1){
							$flag = "<img src='images/green_flag_16.png' width='16' height='16'>";
						}else{
							$flag = "&nbsp;";
						}
					}
				}
			}
			$registro = "<tr><td width='2'>" . $flag . "</td>";

			if (($AnteriorUnidadNegocio != $SlsAnalysis['tagname']) and ($AnteriorUnidadNegocio != "")){

				$registro = $registro . '<td style="text-align:left; font-size:8pt; font-weight:normal;">' . $AnteriorUnidadNegocio  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldoTotalXtag,2)  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldonovencidoXtag,2)  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencido1_30Xtag,2)  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencido1_60Xtag,2)  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencido1_90Xtag,2)  . '</td>';
				$registro = $registro . '<td class="number" style="font-size:8pt; font-size:8pt; font-weight:normal;"> $' .number_format($saldovencidomas_90Xtag,2)  . '</td>';
				$registro = $registro . "</tr>";

				echo $registro;
				$saldoTotalXCliente = 0;
				$saldonovencidoXCliente=0;
				$saldovencido1_30XCliente=0;
				$saldovencido1_60XCliente=0;
				$saldovencido1_90XCliente=0;
				$saldovencidomas_90XCliente=0;
				$antProv = $SlsAnalysis['debtorno'];
				$AnteriorCliente = $SlsAnalysis['name'];
			}
			$AnteriorUnidadNegocio  = "";
			echo '<tr bgcolor="#FFFF38" >';
			echo '<td colspan="2"  nowrap class="number"><b> '. _('TOTAL') . ' ' . $AnteriorRegionName . ': &nbsp;</b></td>';

			echo '<td nowrap class="number"> $' .number_format($saldoTotalxRegion,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldonovencidoxRegion,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_30xRegion,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_60xRegion,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_90xRegion,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencidomas_90xRegion,2)  . '</td>';
			echo '</tr>';
			echo '<tr bgcolor="#FF5A38" >';
			echo '<td colspan="2"  nowrap class="number"><b> '. _('TOTALES') . ': &nbsp;</b></td>';
			echo '<td nowrap class="number"> $' .number_format($saldoTotal,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldonovencido,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_30,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_60,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_90,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencidomas_90,2)  . '</td>';
			echo '</tr>';
		}
		if ($_POST['DetailedReport'] == 'Factura') {

			echo '<tr bgcolor="#FFFF38" >';
			echo '<td colspan="2"  nowrap class="number"><b> '. _('TOTAL') . ' ' . $AnteriorRegionName . ': &nbsp;</b></td>';

			echo '<td nowrap class="number"> $' .number_format($saldoTotalxRegion,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldonovencidoxRegion,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_30xRegion,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_60xRegion,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_90xRegion,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencidomas_90xRegion,2)  . '</td>';
			echo '</tr>';
			echo '<tr bgcolor="#FF5A38" >';
			echo '<td colspan="2"  nowrap class="number"><b> '. _('TOTALES') . ': &nbsp;</b></td>';
			echo '<td nowrap class="number"> $' .number_format($saldoTotal,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldonovencido,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_30,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_60,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_90,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencidomas_90,2)  . '</td>';
			echo '</tr>';
		}

		if ($_POST['DetailedReport'] == 'FacturaProveedor') {

			echo '<tr bgcolor="#FFFF38" >';
			echo '<td colspan="3"  nowrap class="number"><b> '. _('TOTAL') . ' ' . $AnteriorRegionName . ': &nbsp;</b></td>';

			echo '<td nowrap class="number"> $' .number_format($saldoTotalxRegion,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldonovencidoxRegion,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_30xRegion,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_60xRegion,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_90xRegion,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencidomas_90xRegion,2)  . '</td>';
			echo '</tr>';
			echo '<tr bgcolor="#FF5A38" >';
			echo '<td colspan="3"  nowrap class="number"><b> '. _('TOTALES') . ': &nbsp;</b></td>';
			echo '<td nowrap class="number"> $' .number_format($saldoTotal,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldonovencido,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_30,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_60,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencido1_90,2)  . '</td>';
			echo '<td nowrap class="number"> $' .number_format($saldovencidomas_90,2)  . '</td>';
			echo '</tr>';
		}
	}



	echo "</table><tr><td colspan=12><br><br><br></td></tr>";
	echo '</form>';

} elseIf (isset($_POST['PrintPDF'])
	AND isset($_POST['FromCriteria'])
	AND strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND strlen($_POST['ToCriteria'])>=1) {

	include('includes/PDFStarter.php');

	$FontSize=12;
	$pdf->addinfo('Title',_('Listado Antiguedad de Saldos'));
	$pdf->addinfo('Subject',_('Antiguedad Saldos Proveedores'));

	$PageNumber=0;
	$line_height=12;

      /*Now figure out the aged analysis for the Supplier range under review */

	if ($_POST['All_Or_Overdues']=='All'){
		$SQL = "SELECT suppliers.supplierid, suppliers.suppname, currencies.currency, paymentterms.terms,
	SUM(supptrans.ovamount + supptrans.ovgst  - supptrans.alloc) as balance,
	SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
		CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= paymentterms.daysbeforedue  THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
	ELSE
		CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= 0 THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
	END) AS due,
	Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
		CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
	ELSE
		CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') ."), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
	END) AS overdue1,
	Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
		CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue	AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
	ELSE
		CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
	END) AS overdue2
	FROM suppliers, paymentterms, currencies,  supptrans JOIN sec_unegsxuser ON supptrans.tagref = sec_unegsxuser.tagref JOIN tags ON sec_unegsxuser.tagref=tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
	WHERE suppliers.paymentterms = paymentterms.termsindicator
	AND suppliers.currcode = currencies.currabrev
	AND suppliers.supplierid = supptrans.supplierno
	AND suppliers.supplierid >= '" . $_POST['FromCriteria'] . "'
	AND suppliers.supplierid <= '" . $_POST['ToCriteria'] . "'
	AND  suppliers.currcode ='" . $_POST['Currency'] . "'
	AND (supptrans.tagref =".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
	GROUP BY suppliers.supplierid,
		suppliers.suppname,
		currencies.currency,
		paymentterms.terms,
		paymentterms.daysbeforedue,
		paymentterms.dayinfollowingmonth
	HAVING Sum(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) <>0";

	} else {

	      $SQL = "SELECT suppliers.supplierid,
	      		suppliers.suppname,
			currencies.currency,
			paymentterms.terms,
			SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance,
			SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
				CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= paymentterms.daysbeforedue  THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			ELSE
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= 0 THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			END) AS due,
			Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			ELSE
				CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			END) AS overdue1,
			SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue	AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			ELSE
				CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			END) AS overdue2
			FROM suppliers,
				paymentterms,
				currencies,
				supptrans JOIN sec_unegsxuser ON supptrans.tagref = sec_unegsxuser.tagref JOIN tags ON sec_unegsxuser.tagref=tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			WHERE suppliers.paymentterms = paymentterms.termsindicator
			AND suppliers.currcode = currencies.currabrev
			and suppliers.supplierid = supptrans.supplierno
			AND suppliers.supplierid >= '" . $_POST['FromCriteria'] . "'
			AND suppliers.supplierid <= '" . $_POST['ToCriteria'] . "'
			AND suppliers.currcode ='" . $_POST['Currency'] . "'
			AND (supptrans.tagref =".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			GROUP BY suppliers.supplierid,
				suppliers.suppname,
				currencies.currency,
				paymentterms.terms,
				paymentterms.daysbeforedue,
				paymentterms.dayinfollowingmonth
			HAVING Sum(IF (paymentterms.daysbeforedue > 0,
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END,
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END)) > 0";

	}

	$SupplierResult = DB_query($SQL,$db,'','',False,False); /*dont trap errors */

	if (DB_error_no($db) !=0) {
	  $title = _('Analisis de Antiguedad de Saldos Proveedores') . ' - ' . _('Reporte de Problema') ;
	  include("includes/header.inc");
	  prnMsg(_('The Supplier details could not be retrieved by the SQL because') .  ' ' . DB_error_msg($db),'error');
	   echo "<br><a href='$rootpath/index.php?" . SID . "'>" . _('Regrsar al Menu...') . '</a>';
	   if ($debug==1){
		echo "<br>$SQL";
	   }
	   include('includes/footer.inc');
	   exit;
	}

	include ('includes/PDFAgedSuppliersPageHeader.inc');
	$TotBal = 0;
	$TotDue = 0;
	$TotCurr = 0;
	$TotOD1 = 0;
	$TotOD2 = 0;

	While ($AgedAnalysis = DB_fetch_array($SupplierResult,$db)){

		$DisplayDue = number_format($AgedAnalysis['due']-$AgedAnalysis['overdue1'],2);
		$DisplayCurrent = number_format($AgedAnalysis['balance']-$AgedAnalysis['due'],2);
		$DisplayBalance = number_format($AgedAnalysis['balance'],2);
		$DisplayOverdue1 = number_format($AgedAnalysis['overdue1']-$AgedAnalysis['overdue2'],2);
		$DisplayOverdue2 = number_format($AgedAnalysis['overdue2'],2);

		$TotBal += $AgedAnalysis['balance'];
		$TotDue += ($AgedAnalysis['due']-$AgedAnalysis['overdue1']);
		$TotCurr += ($AgedAnalysis['balance']-$AgedAnalysis['due']);
		$TotOD1 += ($AgedAnalysis['overdue1']-$AgedAnalysis['overdue2']);
		$TotOD2 += $AgedAnalysis['overdue2'];

		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,220-$Left_Margin,$FontSize,$AgedAnalysis['supplierid'] . ' - ' . $AgedAnalysis['suppname'],'left');
		$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayBalance,'right');
		$LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,$DisplayCurrent,'right');
		$LeftOvers = $pdf->addTextWrap(340,$YPos,60,$FontSize,$DisplayDue,'right');
		$LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,$DisplayOverdue1,'right');
		$LeftOvers = $pdf->addTextWrap(460,$YPos,60,$FontSize,$DisplayOverdue2,'right');

		$YPos -=$line_height;
		if ($YPos < $Bottom_Margin + $line_height){
		      include('includes/PDFAgedSuppliersPageHeader.inc');
		}

		if ($_POST['DetailedReport']=='Yes'){

		   $FontSize=6;
		   /*draw a line under the Supplier aged analysis*/
		   $pdf->line($Page_Width-$Right_Margin, $YPos+10,$Left_Margin, $YPos+10);

		   $sql = "SELECT systypescat.typename, supptrans.suppreference, supptrans.trandate,
			   (supptrans.ovamount + supptrans.ovgst - supptrans.alloc) as balance,
			   CASE WHEN paymentterms.daysbeforedue > 0 THEN
			   	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= paymentterms.daysbeforedue  THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			   ELSE
			   	CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= 0 THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			   END AS due,
			   CASE WHEN paymentterms.daysbeforedue > 0 THEN
			   	CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue	   AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			   ELSE
			   	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1','MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			   END AS overdue1,
			   CASE WHEN paymentterms.daysbeforedue > 0 THEN
			   	CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			   ELSE
			   	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1','MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			   END AS overdue2
			   FROM suppliers,
			   	paymentterms,
				systypescat, supptrans JOIN sec_unegsxuser ON supptrans.tagref = sec_unegsxuser.tagref JOIN tags ON sec_unegsxuser.tagref=tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			   WHERE systypescat.typeid = supptrans.type
			   AND (supptrans.tagref =".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			   AND suppliers.paymentterms = paymentterms.termsindicator
			   AND suppliers.supplierid = supptrans.supplierno
			   AND ABS(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) >0.009
			   AND supptrans.settled = 0
			   AND supptrans.supplierno = '" . $AgedAnalysis["supplierid"] . "'";

		    $DetailResult = DB_query($sql,$db,'','',False,False); /*dont trap errors - trapped below*/
		    if (DB_error_no($db) !=0) {
			$title = _('Aged Supplier Account Analysis - Problem Report');
			include('includes/header.inc');
			echo '<br>' . _('The details of outstanding transactions for Supplier') . ' - ' . $AgedAnalysis['supplierid'] . ' ' . _('could not be retrieved because') . ' - ' . DB_error_msg($db);
			echo "<br><a href='$rootpath/index.php'>" . _('Back to the menu') . '</a>';
			if ($debug==1){
			   echo '<br>' . _('The SQL that failed was') . '<br>' . $sql;
			}
			include('includes/footer.inc');
			exit;
		    }

		    while ($DetailTrans = DB_fetch_array($DetailResult)){

			    $LeftOvers = $pdf->addTextWrap($Left_Margin+5,$YPos,60,$FontSize,$DetailTrans['typename'],'left');
			    $LeftOvers = $pdf->addTextWrap($Left_Margin+65,$YPos,50,$FontSize,$DetailTrans['suppreference'],'left');
			    $DisplayTranDate = ConvertSQLDate($DetailTrans['trandate']);
			    $LeftOvers = $pdf->addTextWrap($Left_Margin+105,$YPos,70,$FontSize,$DisplayTranDate,'left');

			    $DisplayDue = number_format($DetailTrans['due']-$DetailTrans['overdue1'],2);
			    $DisplayCurrent = number_format($DetailTrans['balance']-$DetailTrans['due'],2);
			    $DisplayBalance = number_format($DetailTrans['balance'],2);
			    $DisplayOverdue1 = number_format($DetailTrans['overdue1']-$DetailTrans['overdue2'],2);
			    $DisplayOverdue2 = number_format($DetailTrans['overdue2'],2);

			    $LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayBalance,'right');
			    $LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,$DisplayCurrent,'right');
			    $LeftOvers = $pdf->addTextWrap(340,$YPos,60,$FontSize,$DisplayDue,'right');
			    $LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,$DisplayOverdue1,'right');
			    $LeftOvers = $pdf->addTextWrap(460,$YPos,60,$FontSize,$DisplayOverdue2,'right');

			    $YPos -=$line_height;
			    if ($YPos < $Bottom_Margin + $line_height){
				$PageNumber++;
				include('includes/PDFAgedSuppliersPageHeader.inc');
				$FontSize=6;
			    }
		    } /*end while there are detail transactions to show */
		    /*draw a line under the detailed transactions before the next Supplier aged analysis*/
		   $pdf->line($Page_Width-$Right_Margin, $YPos+10,$Left_Margin, $YPos+10);
		   $FontSize=8;
		} /*Its a detailed report */
	} /*end Supplier aged analysis while loop */

	$YPos -=$line_height;
	if ($YPos < $Bottom_Margin + (2*$line_height)){
		$PageNumber++;
		include('includes/PDFAgedSuppliersPageHeader.inc');
	} elseif ($_POST['DetailedReport']=='Yes') {
		//dont do a line if the totals have to go on a new page
		$pdf->line($Page_Width-$Right_Margin, $YPos+10 ,220, $YPos+10);
	}

	$DisplayTotBalance = number_format($TotBal,2);
	$DisplayTotDue = number_format($TotDue,2);
	$DisplayTotCurrent = number_format($TotCurr,2);
	$DisplayTotOverdue1 = number_format($TotOD1,2);
	$DisplayTotOverdue2 = number_format($TotOD2,2);

	$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayTotBalance,'right');
	$LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,$DisplayTotCurrent,'right');
	$LeftOvers = $pdf->addTextWrap(340,$YPos,60,$FontSize,$DisplayTotDue,'right');
	$LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,$DisplayTotOverdue1,'right');
	$LeftOvers = $pdf->addTextWrap(460,$YPos,60,$FontSize,$DisplayTotOverdue2,'right');

	$YPos -=$line_height;
	$pdf->line($Page_Width-$Right_Margin, $YPos ,220, $YPos);

	$buf = $pdf->output();
	$len = strlen($buf);
	header('Content-type: application/pdf');
	header("Content-Length: $len");
	header('Content-Disposition: inline; filename=AgedSuppliers.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	$pdf->stream();

} else { /*The option to print PDF was not hit */




} /*end of else not PrintPDF */

include('includes/footer.inc');
?>
