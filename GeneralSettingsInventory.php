<?php
/* $Revision: 1.13.1 $ */
/*cambios
1.- se agrego el include('includes/SecurityFunctions.inc');
2.- se arreglo la variable $funcion*/
$PageSecurity = 2;
include('includes/session.inc');
/*
$funcion=330;
include('includes/SecurityFunctions.inc');
*/
$title = _('Reporte General de Ajustes de Costo de Inventarios');
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
		echo '<table
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
		 echo '</table>';			
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
						echo "<option selected value='" . $myrow["u_department"] . "'>" . $myrow['name'];
				      } else {
					      echo "<option value='" . $myrow['u_department'] . "'>" . $myrow['name'];
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
	
	
	if (!isset($_POST['clavecliente'])) {
	    $_POST['clavecliente'] = '*';  
	} 
	/* SELECCION DEL CLIENTE */
	echo "<tr>
		<td>" . _('X Cuenta Contable') . ":</td>
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
					
				if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Factura')
					echo "<option selected value='Factura'>" . _('X Cuenta Contable');
				else
					echo "<option value='Factura'>" . _('X Cuenta Contable');
		
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
	$SQLBase="SELECT gltrans.account as cuentact,
			sum(case when chartmaster.tipo=4 then gltrans.amount else 0 end ) as monto,
			month(gltrans.trandate) as mes,
			year(gltrans.trandate) as anio,
			tags.tagref,
			tags.tagdescription as tagname,
			D.department as departamento,
			L.legalname,
			R.name as regionname,
			A.areadescription as areadescription,
			chartmaster.accountname as accountname,
			gltrans.narrative as narrative,
			gltrans.typeno,
			gltrans.trandate
		  FROM gltrans INNER JOIN tags ON tags.tagref=gltrans.tag
			INNER JOIN chartmaster ON chartmaster.accountcode=gltrans.account";
				
      $SQLFrom="
		INNER JOIN departments D  ON D.u_department=tags.u_department
		INNER JOIN legalbusinessunit L ON L.legalid=tags.legalid
		INNER JOIN areas A ON A.areacode=tags.areacode
		INNER JOIN regions R ON R.regioncode=A.regioncode
	    WHERE gltrans.type=35  and chartmaster.tipo=4
		 AND gltrans.trandate between '".$fechaini."' AND '".$fechafin."'";
		 
	if (isset($_POST['legalid']) and $_POST['legalid']!=0){
		$SQL2=$SQL2." AND L.legalid=".$_POST['legalid'];
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
		$SQL2=$SQL2." AND tags.tagref='".$_POST['unidadnegocio']."'";
	}
	if (isset($_POST['clavecliente']) and $_POST['clavecliente']!='*'){
		$SQL2=$SQL2." AND chartmaster.accountcode='".$_POST['clavecliente']."'";
	}
	if($_POST['DetailedReport'] == 'Tag'){
	$SQLGroupby=" GROUP BY chartmaster.accountname,R.name,
		     L.legalname,D.department,tags.tagdescription,tags.tagref,
		     year(gltrans.trandate),month(gltrans.trandate),gltrans.account";
	}else{
		$SQLGroupby=" GROUP BY gltrans.typeno, gltrans.narrative,chartmaster.accountname,R.name,
		     L.legalname,D.department,tags.tagdescription,tags.tagref,
		     year(gltrans.trandate),month(gltrans.trandate),gltrans.account";
		
	}
	$SQL=$SQLBase." ".$SQLFrom." ".$SQL2." ".$SQLGroupby;
	$SQL=$SQL.' having sum(abs(gltrans.amount))>0 ORDER BY tagname,month(gltrans.trandate),year(gltrans.trandate),gltrans.account ';
	
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
	
	
	
	if ($_POST['DetailedReport'] == 'Factura'){
		$colspandos=2;
	}else{
		$colspandos=1;
	}
	
	echo '<table cellpadding=2 border=1>';
	
	    $headerEncabezado = '<tr>
			<th colspan=1><b>#</b></th>
			<th colspan='.$colspandos.'><b>' . _('Departamento') . '</b></th>
			<th colspan=1><b>' . _('Region') . '</b></th>
			<th colspan=2><b>' . _('Area') . '</b></th>
			<th colspan=3><b>' . _('Unidad Negocio') . '</b></th>
			</tr>';
	    
      if ($_POST['DetailedReport'] == 'Tag') {
	    $headerDetalle = '<tr>
			<th colspan=1><b>#</b></th>
			<th colspan=1><b>' . _('Unidad Negocio') . '</b></th>
			<th><b>' . _('Periodo') . '</b></th>
			<th><b>' . _('Cuenta Contable') . '</b></th>
			<th><b>' . _('Monto') . '</b></th>
			</tr>';
      } else {
	    $headerDetalle = '<tr>
			<th colspan=1><b></b></th>
			<th colspan=1><b>' . _('Unidad Negocio') . '</b></th>
			<th colspan=1><b>' . _('Periodo') . '</b></th>
			<th><b>' . _('Cuenta Contable') . '</b></th>
			<th><b>' . _('Fecha') . '</b></th>
			<th><b>' . _('No. Transaccion') . '</b></th>
			<th><b>' . _('Monto') . '</b></th>
			<th><b>' . _('Descripcion') . '</b></th>
		
			</tr>';
      }
	$i = 0;
	
	
	echo $headerDetalle;
	
	$cuenta=0;
	$taganterior="";
      While ($SlsAnalysis = DB_fetch_array($ReportResult,$db)){
		$cuenta=$cuenta+1;
		
		if(strlen($taganterior)==0){
			echo '<tr class="center" align=center bgcolor="yellow">';
			echo '<td colspan=1><b></b></td>
			      <td colspan=1><b>' . $SlsAnalysis['departamento'] . '</b></td>
			      <td colspan=1><b>' . $SlsAnalysis['regionname'] . '</b></td>
			      <td colspan=6><b>' . $SlsAnalysis['areadescription'] . '</b></td>';
			echo '</tr>';
		}
		
		if ($_POST['DetailedReport'] == 'Tag' and $taganterior<>$SlsAnalysis['tagname'] and strlen($taganterior)>0) {
			echo '<tr bgcolor="#FF5A38">';
			echo '<td colspan=4 nowrap class="number"><b> ' ._('TOTALES POR UNIDAD DE NEGOCIO')  . '</b></td>';
			echo '<td nowrap class="number"> <b>$' .number_format($saldoTotalXtag,2)  . '</b></td>';
			echo '</tr>';
			echo '<tr class="center" align=center bgcolor="yellow">';
			echo '<td colspan=1><b></b></td>
			      <td colspan=1><b>' . $SlsAnalysis['departamento'] . '</b></td>
			      <td colspan=1><b>' . $SlsAnalysis['regionname'] . '</b></td>
			      <td colspan=3><b>' . $SlsAnalysis['areadescription'] . '</b></td>';
			echo '</tr>';
			$saldoTotalXtag=0;
		} elseif ($taganterior<>$SlsAnalysis['tagname'] and strlen($taganterior)>0){
			echo '<tr bgcolor="#FF5A38">';
			echo '<td colspan=6 nowrap class="number"><b> ' ._('TOTALES POR UNIDAD DE NEGOCIO')  . '</b></td>';
			echo '<td  nowrap class="number"> <b>$' .number_format($saldoTotalXtag,2)  . '</b></td>';
			echo '<td  nowrap class="number"> </td>';
			echo '</tr>';
			echo '<tr class="center" align=center bgcolor="yellow">';
			echo '<td colspan=1><b></b></td>
			      <td colspan=1><b>' . $SlsAnalysis['departamento'] . '</b></td>
			      <td colspan=1><b>' . $SlsAnalysis['regionname'] . '</b></td>
			      <td colspan=5><b>' . $SlsAnalysis['areadescription'] . '</b></td>';
			echo '</tr>';
			$saldoTotalXtag=0;
		}
		if ($_POST['DetailedReport'] == 'Tag'){
			echo '<tr>';
			echo '<td>'.$cuenta.'</td>';
			echo '<td colspan="1" nowrap class="number">'.$SlsAnalysis['tagname'].'</td>';
			echo '<td colspan="1" nowrap class="number">'.glsnombremeslargo($SlsAnalysis['mes']).' '.$SlsAnalysis['anio'].'</td>';
			echo '<td colspan="1" nowrap class="number">'.$SlsAnalysis['accountname'].'</td>';
			echo '<td colspan="1" nowrap class="number"> $'.number_format($SlsAnalysis['monto'],2).'</td>';
			echo '</tr>';
		}
		else{
			echo '<tr>';
			echo '<td>'.$cuenta.'</td>';
			echo '<td colspan="1" nowrap class="number">'.$SlsAnalysis['tagname'].'</td>';
			echo '<td colspan="1" nowrap class="number">'.glsnombremeslargo($SlsAnalysis['mes']).' '.$SlsAnalysis['anio'].'</td>';
			echo '<td colspan="1" nowrap class="number">'.$SlsAnalysis['accountname'].'</td>';
			echo '<td colspan="1" nowrap class="number">'.$SlsAnalysis['trandate'].'</td>';
			echo '<td colspan="1" nowrap class="number">'.$SlsAnalysis['typeno'].'</td>';
			echo '<td colspan="1" nowrap class="number"> $'.number_format($SlsAnalysis['monto'],2).'</td>';
			echo '<td colspan="1">'.$SlsAnalysis['narrative'].'</td>';
			echo '</tr>';
		}
		$saldoTotalXtag=$saldoTotalXtag+$SlsAnalysis['monto'];
		$taganterior=$SlsAnalysis['tagname'];
		$saldoTotal=$saldoTotal+$SlsAnalysis['monto'];
      } /*end while loop */
	 
	 
	 if ($_POST['DetailedReport'] == 'Tag' and $taganterior<>$SlsAnalysis['tagname'] and strlen($taganterior)>0) {
			echo '<tr bgcolor="#FF5A38">';
			echo '<td colspan=4 nowrap class="number"><b> ' ._('TOTALES POR UNIDAD DE NEGOCIO')  . '</b></td>';
			echo '<td nowrap class="number"> <b>$' .number_format($saldoTotalXtag,2)  . '</b></td>';
			echo '</tr>';
			echo '<tr bgcolor="yellow">';
			echo '<td colspan=4 nowrap class="number"><b> ' ._('TOTAL GENERAL')  . '</b></td>';
			echo '<td nowrap class="number"> <b>$' .number_format($saldoTotal,2)  . '</b></td>';
			echo '</tr>';
			$saldoTotalXtag=0;
		} elseif ($taganterior<>$SlsAnalysis['tagname'] and strlen($taganterior)>0){
			echo '<tr bgcolor="yellow">';
			echo '<td colspan=6 nowrap class="number"><b> ' ._('TOTALES POR UNIDAD DE NEGOCIO')  . '</b></td>';
			echo '<td  nowrap class="number"> <b>$' .number_format($saldoTotalXtag,2)  . '</b></td>';
			echo '<td  nowrap class="number"> </td>';
			echo '</tr>';
			$saldoTotalXtag=0;
			echo '<tr bgcolor="#FF5A38">';
			echo '<td colspan=6 nowrap class="number"><b> ' ._('TOTAL GENERAL')  . '</b></td>';
			echo '<td  nowrap class="number"> <b>$' .number_format($saldoTotal,2)  . '</b></td>';
			echo '<td  nowrap class="number"> </td>';
			echo '</tr>';
		}
// totales generales
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
