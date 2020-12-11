<?php

/* MODIFICADO POR Desarrollador
   FECHA: 13/FEBRERO/2011
   CAMBIOS:
			1.- Limpie las agrupaciones y agregue tipos de documentos que faltaban.
			2.- Cuadre algunos registros del SuppAllocns
	FIN DE CAMBIOS
   MODIFICADO POR Desarrollador
   FECHA: 14/FEBRERO/2011
   CAMBIOS:
		1.- Agregue checkbox para eliminar documentos ya saldados
   FIN DE CAMBIOS
*/

/*cambios
1.- SE AGREGO EL COMBO DE DETALLE
2.- SE CREO EL CODIGO PARA IMPRIMIR EN EXCEL
3.- SE CREO EL CODIGO PARA IMPRIMIR EN PDF*/

/*CAMBIOS REALIZADOS POR ISRAEL BARRERA*/


$PageSecurity = 2;
include('includes/session.inc');
$funcion=213;
include('includes/SecurityFunctions.inc');
$title = _('Reporte General de Cuentas X Pagar X Proveedor');

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
if (!isset($_POST['PrintEXCEL'])) {
include('includes/header.inc');
$debug = 1;
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' ' . $title.'<br>';
/* OBTENGO FECHAS*/

     
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
	/*
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
	echo '</tr>';*/
	/*FIN DE REGION*/
	/* SELECCION DEL AREA */
	/*echo '<tr>
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
	echo '</tr>';*/
	/*FIN DE AREA*/
	/* SELECCION DE DEPARTAMENTO       */
	echo '<tr>
		<td>' . _('X Departamento') . ':' . "</td>
		<td>";
			$sql = "SELECT departments.u_department,departments.department as name
				FROM departments 
				JOIN tags ON tags.u_department = departments.u_department
				JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
			  WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			  GROUP BY department
			  ORDER BY department";
			$result=DB_query($sql,$db);	  
			echo "<select tabindex='4' name='xDepartamento'>";
				echo "<option selected value='0'>Todos los departamentos...</option>";
				while ($myrow=DB_fetch_array($result)){
				      if ($myrow['u_department'] == $_POST['xDepartamento']){
						echo "<option selected value='" . $myrow["u_department"] .'_'. $myrow['name']. "'>" . $myrow['name'];
				      } else {
					      echo "<option value='" . $myrow['u_department'] .'_'. $myrow['name']. "'>" . $myrow['name'];
				      }
				}
			echo '</select>
		</td>';
	echo '</tr>';
	/*FIN DEPARTAMENTO*/
	/* SELECCION DE LA UNIDAD DE NEGOCIO DE PRODUCTOS */
	echo "<tr>
		<td>" . _('X Sucursal') . ":</td>
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
						echo "<option selected value='" . $myrow['tagref'] .'_'. $myrow['tagdescription']. "'>" . $myrow['tagdescription'] . "</option>";	
					}else{
						echo "<option value='" . $myrow['tagref'] .'_'. $myrow['tagdescription']. "'>" . $myrow['tagdescription'] . "</option>";
					}
				}
			echo "</select>";
	echo "  </td>";
	echo "</tr>";
	
	/*FIN UNIDAD DE NEGOCIO*/
	
	/*echo '<tr>
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
	
	/************************************/
	/* SELECCION DEL TIPO DE PROVEEDOR */
	echo "<tr><td>" . _('X Tipo de Proveedor') . ":</td><td>";
	echo "<select name='tipoproveedor'>";
	$SQL = "SELECT  typeid, typename FROM supplierstype";

	$ErrMsg = _('No transactions were returned by the SQL because');
	$TransResult = DB_query($SQL,$db,$ErrMsg);
	
	echo "<option selected value='0'>Todos los tipos de Proveedor...</option>";
	
	while ($myrow=DB_fetch_array($TransResult)) {
		if ($myrow['typeid'] == $_POST['tipoproveedor']){
			echo "<option selected value='" . $myrow['typeid'] . "'>" . $myrow['typename'] . "</option>";	
		}else{
			echo "<option value='" . $myrow['typeid'] . "'>" . $myrow['typename'] . "</option>";
		}
	}
	 
	echo "</select>";
	echo "</td></tr>";
	
	/************************************/
	
	if (!isset($_POST['clavecliente'])) {
	    $_POST['clavecliente'] = '*';  
	}
	
	/* SELECCION DEL PROVEEDOR */
	echo "<tr>
		<td>" . _('X Clave de Proveedor') . ":</td>
		<td>";
			echo "<input type=text name='clavecliente' value='".$_POST['clavecliente']."'>:* para todos.";
	echo "	</td>";
	
	echo "</tr>";
	/*FIN CLAVE PROVEEDOR*/
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
				if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Global')
					echo "<option selected value='Global'>" . _('Global');
				else
					echo "<option value='Global'>" . _('Global');
					
				if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Detalle')
					echo "<option selected value='Detalle'>" . _('Detalle');
				else
					echo "<option value='Detalle'>" . _('Detalle');
						
	echo '		</select>
		</td>
	      </tr>';
	if ($_POST['ReportSaldo']=='1'){
		$CheckReportSaldo='checked';
	} else{
		$CheckReportSaldo='';
	}
    
	echo '<tr>
		<td>' . _('Solo Documentos Con Saldo') . ':' . "</td>";
		
	echo '	<td><input type="checkbox" name="ReportSaldo" value=1 '.$CheckReportSaldo.'>
		</td>
	      </tr>';
	echo "<tr>";
		echo "<td colspan='2'>";
			echo "<table border='0' cellpadding='0' cellspacing='0'>";
				echo "<tr>";
				echo "<td><input tabindex='6' type='submit' name='ReportePantalla' value='" . _('Mostrar en Pantalla') . "'></td>";
				echo "<td><input tabindex='7' type='submit' name='PrintPDF' value='" . _('Exportar a PDF') . "'></td>";
				echo "<td><input tabindex='7' type='submit' name='PrintEXCEL' value='" . _('Exportar a Excel') . "'></td>";
				echo "</tr>";
			echo "</table>";
		echo "</td>";
	echo "</tr>";
	
	echo "</table>";
	//<br><div class="centre"></div>';
	//echo '<br><div class="centre"></div>';
	//echo '<br><div class="centre"></div>';
}
	
//$arreglo=explode("_",$_POST['legalid']);
//$Razon=$arreglo[0];

$arreglo=explode("_",$_POST['xDepartamento']);
$Departamento=$arreglo[0];

$arreglo=explode("_",$_POST['unidadnegocio']);
$unidad=$arreglo[0];
if ($_POST['ReportSaldo']=='1'){
	$ReportSaldo=1;
} else{
	$ReportSaldo=0;
}

If (isset($_POST['ReportePantalla']) or isset($_POST['PrintEXCEL'])){
	
	
	if (isset($_POST['PrintEXCEL'])) {
	
		header("Content-type: application/ms-excel");
		# replace excelfile.xls with whatever you want the filename to default to
		header("Content-Disposition: attachment; filename=ReporteCXPProv.xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	
		echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
		echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
		echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
	    }
	/*CONSULTA POR DETALLE*/
	if ($_POST['DetailedReport'] == 'Detalle'){
	
		$SQL2=" ";
		$SQLBase="SELECT S.supplierno as debtorno,
					M.suppname as name,
					CT.typename as concepto, suppreference as folio,S.origtrandate, 
					S.trandate, S.rate as tipocambio,
					sum( (ovamount+ovgst)/rate ) as monto,
					sum( ifnull(abonos.abono/rate,0) ) as abono,
					SUM( (ovamount+ovgst)/rate - ifnull(abonos.abono/rate,0) ) as saldo
				FROM supptrans S";
		$SQLCargo=$SQLBase. "  LEFT JOIN 
					(
					      SELECT SUM(amt) as abono, transid_allocto as cargo 
					      FROM suppallocs
					      WHERE datealloc<='".$fechafin."'
					      GROUP BY transid_allocto
					) as abonos  ON abonos.cargo=S.id";
	
		$SQLBase="SELECT S.supplierno as debtorno,
					M.suppname as name,
					CT.typename as concepto, suppreference as folio,S.origtrandate, 
					S.trandate, S.rate as tipocambio,
					sum((ovamount+ovgst)/rate) as monto,
					sum(ifnull(abonos.abono/rate,0)) as abono,
					SUM((ifnull(abonos.abono,ovamount+ovgst))/rate) as saldo
				FROM supptrans S";
			
		$SQLAbono=$SQLBase. "  LEFT JOIN 
					(
						SELECT SUM(amt*-1) as abono, transid_allocfrom as cargo 
						FROM suppallocs
						WHERE datealloc<='".$fechafin."'
						GROUP BY transid_allocfrom
					) as abonos  ON abonos.cargo=S.id";
	
		$SQLAbonos2="SELECT S.supplierno as debtorno,
				M.suppname as name,
				CT.typename as concepto, suppreference as folio,S.origtrandate, 
				S.trandate, S.rate as tipocambio,
				sum((ovamount+ovgst)/rate) as monto,
				sum(ifnull(abonos.abono/rate,0)) as abono,
				SUM((ifnull((ovamount+ovgst)-abonos.abono,ovamount+ovgst))/rate) as saldo
				FROM    supptrans S";
		$SQLAbonos2=$SQLAbonos2. "  LEFT JOIN 
					(
						SELECT SUM(amt*-1) as abono, transid_allocfrom as cargo 
						FROM suppallocs
						WHERE datealloc<='".$fechafin."'
						GROUP BY transid_allocfrom
					) as abonos  ON abonos.cargo=S.id";
		$SQLFrom="INNER JOIN suppliers M ON S.supplierno=M.supplierid
			INNER JOIN supplierstype st ON M.typeid = st.typeid
			INNER JOIN tags T ON T.tagref=S.tagref
			INNER JOIN departments D  ON D.u_department=T.u_department
			INNER JOIN legalbusinessunit L ON L.legalid=T.legalid
			INNER JOIN areas A ON A.areacode=T.areacode
			INNER JOIN regions R ON R.regioncode=A.regioncode
			INNER JOIN systypescat CT ON CT.typeid=S.type
			WHERE S.trandate<='".$fechafin."' AND (S.transtext not like '%CANCELAD%' OR S.transtext is null)";
		$SQL2 = $SQL2 . " AND (T.legalid = " . $_POST['legalid'] . " or " . $_POST['legalid'] . " = 0)";
		$SQL2 = $SQL2 . " AND (R.regioncode = '" . $_POST['xRegion'] . "' or '" . $_POST['xRegion'] . "' = '')";
		$SQL2 = $SQL2 . " AND (A.areacode = '" . $_POST['xArea'] . "' or '" . $_POST['xArea'] . "' = '')";
		$SQL2 = $SQL2 . " AND (D.u_department = '" .$_POST['xDepartamento'] . "' or " . $_POST['xDepartamento'] . " = 0)";
		$SQL2 = $SQL2 . " AND (T.tagref = '" . $_POST['unidadnegocio'] . "' or '" . $_POST['unidadnegocio'] . "' = '0')";
		$SQL2 = $SQL2 . " AND (st.typeid = '" . $_POST['tipoproveedor'] . "' or " . $_POST['tipoproveedor'] . " = 0)";
		$SQL2 = $SQL2 . " AND (S.supplierno = '" . $_POST['clavecliente'] . "' or '" . $_POST['clavecliente'] . "' = '*')";
	 
	 
		$SQLcondiciones=$SQL2;
		$SQL=$SQL . " " . $SQL2;
		$SQLAgrupar=' GROUP BY S.supplierno,
				       S.trandate, folio, S.transno';
		    
		$SQLCargos=$SQLCargo.' '.$SQLFrom.' '.$SQL2 .' AND S.type in (20,34,117,470) ' . $SQLAgrupar.' ';
		if ($ReportSaldo==1){
			$SQLCargos=$SQLCargos.'  HAVING abs(saldo)>1';
		}
		$SQLAbonos=$SQLAbono.' '.$SQLFrom.' '.$SQL2  .' AND S.type in (22,24,32,33,37,116,121,480,490)  and abonos.abono is null '. $SQLAgrupar;
		if ($ReportSaldo==1){
		       $SQLAbonos=$SQLAbonos.'  HAVING abs(saldo)>1';
		}
		//$SQLAbonos=$SQLAbono.' '.$SQLFrom.' '.$SQL2  .' AND S.type in (11,12,13,80,420,430,450,460) '. $SQLAgrupar;
		$SQLAbonos2=$SQLAbonos2.' '.$SQLFrom.' '.$SQL2  .' AND S.type in (22,24,32,33,37,116,121,480,490) and abs(ovamount+ovgst)-abs(abonos.abono)>.9  and abonos.abono is not null'. $SQLAgrupar;
		if ($ReportSaldo==1){
			$SQLAbonos2=$SQLAbonos2.'  HAVING abs(saldo)>1';
		}
		//echo $SQLAbonos;
		$SQL2='';
		$SQL=$SQLCargos.' UNION '.$SQLAbonos . ' UNION '.$SQLAbonos2;
		$SQLconsultas=$SQLCargos.'<br> UNION <br>'.$SQLAbonos . ' <br>UNION <br>'.$SQLAbonos2;
		$SQL=$SQL.' ORDER BY debtorno, name,origtrandate,folio ';
		// echo '<br>'.$SQLconsultas.'<br><br>';
		if($ReportSaldo==1){
		}
	/*CONSULTA GLOBAL*/
	}else{
		$SQL2=" ";
		$SQLBase="SELECT S.supplierno as debtorno,
				M.suppname as name, S.rate as tipocambio,
				sum(ifnull(abonos.abono/rate,0)) as abono,
				sum((ovamount+ovgst)/rate) as monto,
				SUM(((ovamount+ovgst)-ifnull(abonos.abono,0))/rate) as saldo
			FROM    supptrans S";
		$SQLCargo=$SQLBase. "  LEFT JOIN 
				(
					SELECT SUM(amt) as abono, transid_allocto as cargo 
					FROM suppallocs
					WHERE datealloc<='".$fechafin."'
					GROUP BY transid_allocto
				) as abonos  ON abonos.cargo=S.id";
	
		$SQLBase="SELECT S.supplierno as debtorno,
				M.suppname as name,S.rate as tipocambio,
				sum(ifnull(abonos.abono/rate,0)) as abono,
				sum((ovamount+ovgst)/rate) as monto,
				SUM((ifnull(abonos.abono,ovamount+ovgst))/rate) as saldo
			FROM    supptrans S";

		$SQLAbono=$SQLBase. "  LEFT JOIN 
				(
					SELECT SUM(amt*-1) as abono, transid_allocfrom as cargo 
					FROM suppallocs
					WHERE datealloc<='".$fechafin."'
					GROUP BY transid_allocfrom
				) as abonos  ON abonos.cargo=S.id";
	
		$SQLAbonos2="SELECT S.supplierno as debtorno,
				M.suppname as name,S.rate as tipocambio,
				sum(ifnull(abonos.abono/rate,0)) as abono,
				sum((ovamount+ovgst)/rate) as monto,
				SUM((ifnull((ovamount+ovgst)-abonos.abono,ovamount+ovgst))/rate) as saldo
			FROM    supptrans S";
			
			
			
		$SQLAbonos2=$SQLAbonos2. "  LEFT JOIN 
				(
					SELECT SUM(amt*-1) as abono, transid_allocfrom as cargo 
					FROM suppallocs
					WHERE datealloc<='".$fechafin."'
					GROUP BY transid_allocfrom
				) as abonos  ON abonos.cargo=S.id";
	
				
		$SQLFrom="INNER JOIN suppliers M ON S.supplierno=M.supplierid
				INNER JOIN supplierstype st ON M.typeid = st.typeid
				INNER JOIN tags T ON T.tagref=S.tagref
				INNER JOIN departments D  ON D.u_department=T.u_department
				INNER JOIN legalbusinessunit L ON L.legalid=T.legalid
				INNER JOIN areas A ON A.areacode=T.areacode
				INNER JOIN regions R ON R.regioncode=A.regioncode
				INNER JOIN systypescat CT ON CT.typeid=S.type
			WHERE S.trandate<='".$fechafin."' AND (S.transtext not like '%CANCELAD%' OR S.transtext is null)";
	 
		$SQL2 = $SQL2 . " AND (T.legalid = " . $_POST['legalid'] . " or " . $_POST['legalid'] . " = 0)";
		$SQL2 = $SQL2 . " AND (R.regioncode = '" . $_POST['xRegion'] . "' or '" . $_POST['xRegion'] . "' = '')";
		$SQL2 = $SQL2 . " AND (A.areacode = '" . $_POST['xArea'] . "' or '" . $_POST['xArea'] . "' = '')";
		$SQL2 = $SQL2 . " AND (D.u_department = '" .$_POST['xDepartamento'] . "' or " . $_POST['xDepartamento'] . " = 0)";
		$SQL2 = $SQL2 . " AND (T.tagref = '" . $_POST['unidadnegocio'] . "' or '" . $_POST['unidadnegocio'] . "' = '0')";
		$SQL2 = $SQL2 . " AND (st.typeid = '" . $_POST['tipoproveedor'] . "' or " . $_POST['tipoproveedor'] . " = 0)";
		$SQL2 = $SQL2 . " AND (S.supplierno = '" . $_POST['clavecliente'] . "' or '" . $_POST['clavecliente'] . "' = '*')";
	 
		$SQLcondiciones=$SQL2;
		$SQL=$SQL." ".$SQL2;
		$SQLAgrupar=' GROUP BY S.supplierno, S.rate ';
			   
		$SQLCargos=$SQLCargo.' '.$SQLFrom.' '.$SQL2 .' AND S.type in (20,34,117,470) ' . $SQLAgrupar.' ';
		if ($ReportSaldo==1){
		       $SQLCargos=$SQLCargos.'  HAVING abs(saldo)>1';
		}
		$SQLAbonos=$SQLAbono.' '.$SQLFrom.' '.$SQL2  .' AND S.type in (22,24,32,33,37,116,121,480,490)  and abonos.abono is null '. $SQLAgrupar;
		if ($ReportSaldo==1){
		       $SQLAbonos=$SQLAbonos.'  HAVING abs(saldo)>1';
		}
		$SQLAbonos2=$SQLAbonos2.' '.$SQLFrom.' '.$SQL2  .' AND S.type in (22,24,32,33,37,116,121,480,490) and abs(ovamount+ovgst)-abs(abonos.abono)>.9  and abonos.abono is not null'. $SQLAgrupar;
		if ($ReportSaldo==1){
		       $SQLAbonos2=$SQLAbonos2.'  HAVING abs(saldo)>1';
		}
		$SQL2='';
	       
		$SQL=$SQLCargos.' UNION '.$SQLAbonos . ' UNION '.$SQLAbonos2;
		$SQLconsultas=$SQLCargos.'<br> UNION <br>'.$SQLAbonos . ' <br>UNION <br>'.$SQLAbonos2;
		$SQL=$SQL.' ORDER BY debtorno, name';
		//echo '<br>'.$SQLconsultas.'<br>';
	
	}
	//echo $SQL;	
	 $ReportResult = DB_query($SQL,$db,'','',False,False); /*dont trap errors */
	 if (DB_error_no($db) !=0) {
		$title = _('Reporte General de Antigüedad de Cartera ') . ' - ' . _('Reporte de Problema') ;
		prnMsg(_('Los detalles de cartera no se pudieron recuperar porque el SQL fallo') .  ' ' . DB_error_msg($db),'error');
		exit;
	 }
	 $DoctoTotPagos = 0;
	 $VenceTotPagos = 0;
	 $ProvTotPagos = 0;
	 $RegionTotPagos = 0;
	 $TotPagos = 0;
	
	 echo '<table cellpadding=2 border=1>';
	/*SI EL REPORTE ES POR DETALLE IMPRIME ESTE ENCABESADO*/
	if ($_POST['DetailedReport'] == 'Detalle'){
		$headerDetalle = '<tr>
		      <th colspan=1 style="font-size:7pt;"><b>' . _('') . '</b></th>
		      <th colspan=1 style="font-size:7pt;"><b>' . _('Concepto') . '</b></th>
		      <th colspan=1 style="font-size:7pt;"><b>' . _('Folio') . '</b></th>
		      <th colspan=1 style="font-size:7pt;"><b>' . _('Fecha') . '</b></th>
		      <th colspan=1 style="font-size:7pt;"><b>' . _('Vencimiento') . '</b></th>
		      <th style="font-size:7pt;"><b>' . _('Tipo Cambio') . '</b></th>
		      <th style="font-size:7pt;"><b>' . _('Monto') . '</b></th>
		      <th style="font-size:7pt;"><b>' . _('Abonos') . '</b></th>
		      <th style="font-size:7pt;"><b>' . _('Saldo') . '</b></th>
		      </tr>';
	/*SI NO IMPRIME ESTE..*/
	}else{
		$headerDetalle = '<tr>
			<th colspan=1 style="font-size:7pt;"><b>' . _('') . '</b></th>
			<th colspan=1 style="font-size:7pt;"><b>' . _('Cod') . '</b></th>
			<th colspan=1 style="font-size:7pt;"><b>' . _('Proveedor') . '</b></th>
			<th style="font-size:7pt;"><b>' . _('Tipo Cambio') . '</b></th>
			<th style="font-size:7pt;"><b>' . _('Monto') . '</b></th>
			<th style="font-size:7pt;"><b>' . _('Abonos') . '</b></th>
			<th style="font-size:7pt;"><b>' . _('Saldo') . '</b></th>
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
	if($_POST['DetailedReport'] == 'Tag'){
		echo $headerDetalle;
	}
	echo $headerDetalle;
	$cuenta=1;
	$cuentaProveedor = 0;
     
while ($SlsAnalysis = DB_fetch_array($ReportResult,$db)){
	/*IMPRIME TOTOALES X PROVEEDOR*/
	if (($AnteriorCliente!= $SlsAnalysis['debtorno'] and $cuentaProveedor>0)and ($_POST['DetailedReport'] == 'Detalle'))
	{
		echo "<tr bgcolor='yellow'>";
		echo '<td class="number" style="font-size:7pt;"> ' .number_format($cuentaProveedor,0)  . '</td>';
		echo '<td colspan=5 class="number" style="font-size:7pt;"> TOTALES X PROVEEDOR </td>';
		echo '<td class="number" style="font-size:7pt;"> $' .number_format($MontoXcliente,2)  . '</td>';
		echo '<td class="number" style="font-size:7pt;"> $' .number_format($AbonoXcliente,2)  . '</td>';
		echo '<td class="number" style="font-size:7pt;"> $' .number_format($saldoTotalXCliente,2)  . '</td>';
		echo "</tr>";
		$saldoTotalXCliente=0;
		$saldonovencidoXCliente=0;
		$saldovencido1_30XCliente=0;
		$saldovencido1_60XCliente=0;
		$saldovencido1_90XCliente=0;
		$saldovencidomas_90XCliente=0;
		$MontoXcliente=0;
		$AbonoXcliente=0;
		$cuentaProveedor = 0;
		
		
	}
	/*SI ES POR DETALLE IMPRIME EL NUMERO Y NOMBRE DEL PROVEEDOR*/
	if (($AnteriorCliente!= $SlsAnalysis['debtorno']) and ($_POST['DetailedReport'] == 'Detalle'))
	{
		echo "<tr bgcolor='#FF5A38'>";
				echo '<td colspan=8  style="font-size:7pt;"><a target="_blank"
				href="ReportSupplierInqueryV3.php?&SupplierID=' . $SlsAnalysis['debtorno'] . '">
				<img src="part_pics/Report.png" width="16" height="16"></a>&nbsp;' . $SlsAnalysis['debtorno'].' - ' .$SlsAnalysis['name'] . '</td>';
			echo "</tr>";
		
	}
	
	if ($_POST['DetailedReport'] == 'Detalle'){
		
		echo "<tr>";
		echo '<td class="number" style="font-size:7pt;"> ' .number_format($cuenta,0)  . '</td>';
		echo '<td style="font-size:7pt;"> ' .$SlsAnalysis['concepto']  . '</td>';
		echo '<td style="font-size:7pt;"> ' .$SlsAnalysis['folio']  . '</td>';
		echo '<td class="number" style="font-size:7pt;"> ' .ConvertSQLDate($SlsAnalysis['origtrandate'])  . '</td>';
		echo '<td class="number" style="font-size:7pt;"> ' .ConvertSQLDate($SlsAnalysis['trandate'])  . '</td>';
		echo '<td class="number" style="font-size:7pt;"> $' .number_format(1/$SlsAnalysis['tipocambio'],2)  . '</td>';
		echo '<td class="number" style="font-size:7pt;"> $' .number_format($SlsAnalysis['monto'],2)  . '</td>';
		echo '<td class="number" style="font-size:7pt;"> $' .number_format($SlsAnalysis['abono'],2)  . '</td>';
		echo '<td class="number" style="font-size:7pt;"> $' .number_format($SlsAnalysis['saldo'],2)  . '</td>';
		echo "</tr>";
	
	}else{
		echo "<tr>";
		echo '<td class="number" style="font-size:7pt;"> ' .number_format($cuenta,0)  . '</td>';
		echo '<td style="font-size:7pt;"><a href="ReportSupplierInqueryV3.php?&SupplierID=' . $SlsAnalysis['debtorno'] . '" target="_blank"><img src="part_pics/Report.png" width="16" height="16"></a>&nbsp;' . $SlsAnalysis['debtorno']  . '</td>';
		echo '<td style="font-size:7pt;"> ' .$SlsAnalysis['name']  . '</td>';
		echo '<td class="number" style="font-size:7pt;"> $' .number_format(1/$SlsAnalysis['tipocambio'],2)  . '</td>';
		echo '<td class="number" style="font-size:7pt;"> $' .number_format($SlsAnalysis['monto'],2)  . '</td>';
		echo '<td class="number" style="font-size:7pt;"> $' .number_format($SlsAnalysis['abono'],2)  . '</td>';
		echo '<td class="number" style="font-size:7pt;"> $' .number_format($SlsAnalysis['saldo'],2)  . '</td>';
		echo "</tr>";
		
	}
	
		$AnteriorCliente=$SlsAnalysis['debtorno'];
		 // saldos por cliente
		$saldoTotalXCliente=$saldoTotalXCliente+$SlsAnalysis['saldo'];
		$MontoXcliente=$MontoXcliente+$SlsAnalysis['monto'];
		$AbonoXcliente=$AbonoXcliente+$SlsAnalysis['abono'];
		//totales de reporte 
		$saldoTotal=$saldoTotal+$SlsAnalysis['saldo'];
		$MontoTotal=$MontoTotal+$SlsAnalysis['monto'];
		$AbonoTotal=$AbonoTotal+$SlsAnalysis['abono'];
		$cuenta=$cuenta+1;
		$cuentaProveedor=$cuentaProveedor+1;

}
      
      if ($_POST['DetailedReport'] == 'Detalle'){
	echo "<tr bgcolor='yellow'>";
	echo '<td class="number" style="font-size:7pt;"> ' .number_format($cuentaProveedor,0)  . '</td>';
	echo '<td colspan=5 class="number" style="font-size:7pt;"> TOTAL X PROVEEDOR </td>';
	echo '<td class="number" style="font-size:7pt;"> $' .number_format($MontoXcliente,2)  . '</td>';
	echo '<td class="number" style="font-size:7pt;"> $' .number_format($AbonoXcliente,2)  . '</td>';
	echo '<td class="number" style="font-size:7pt;"> $' .number_format($saldoTotalXCliente,2)  . '</td>';
	echo "</tr>";
      }
      
      echo '<tr bgcolor="#FF5A38" >';
      if ($_POST['DetailedReport'] == 'Detalle'){
		echo '<td class="number" style="font-size:7pt;"> ' .number_format($cuenta,0)  . '</td>';
		echo '<td colspan=5 nowrap class="number" style="font-size:7pt;"><b>TOTALES</b></td>';
      }else{
		echo '<td class="number" style="font-size:7pt;"> ' .number_format($cuenta,0)  . '</td>';
		echo '<td colspan=3 nowrap class="number" style="font-size:7pt;"><b>TOTALES</b></td>';
      }
	echo '<td nowrap class="number" style="font-size:7pt;"> $' .number_format($MontoTotal,2)  . '</td>';
	echo '<td nowrap class="number" style="font-size:7pt;"> $' .number_format($AbonoTotal,2)  . '</td>';
	echo '<td nowrap class="number" style="font-size:7pt;"> $' .number_format($saldoTotal,2)  . '</td>';
	echo '</tr>';
	echo "</table><tr><td colspan=12><br><br><br></td></tr>"; 
	echo '</form>';
	
      if (isset($_POST['PrintEXCEL'])) {
		exit;
	}
			
} elseIf (isset($_POST['PrintPDF'])
	AND isset($_POST['FromCriteria'])
	AND strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND strlen($_POST['ToCriteria'])>=1) {

} else { /*The option to print PDF was not hit */

} /*end of else not PrintPDF */


if (isset($_POST['PrintPDF'])) {
      
      $DiaFin=$_POST['ToDia'];
      $MesFin=$_POST['ToMes'];
      $AnioFin=$_POST['ToYear'];
      $xRazonSocial=$_POST['legalid'];
      //$xArea=$_POST['xArea'];
      $xDepartamento=$_POST['xDepartamento'];
      $xUnidadDeNegocio=$_POST['unidadnegocio'];
      $xClaveCliente=$_POST['clavecliente'];
     // $xNivelDetalle=$_POST['DetailedReport'];
      
 echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath ."/PDFGeneralOldBalancesPayablesXSuppliers.php?&DiaFin=".$DiaFin."
	&MesFin=".$MesFin."&AnioFin=".$AnioFin."&xRazonSocial=".$xRazonSocial."&xDepartamento=".$xDepartamento."
	&xUnidadDeNegocio=".$xUnidadDeNegocio."&xClaveCliente=".$xClaveCliente."'>";	

}

include('includes/footer.inc');
?>
