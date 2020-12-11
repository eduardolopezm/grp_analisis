<?php
/* $Revision: 1.13 $ */
/*
 ARCHIVO MODIFICADOR POR: CGM
FECHA: 30-MAY-2014

UPDATE sec_functions
SET url='GeneralReportDiot_V6_0.php'
WHERE functionid='660';

*/
$PageSecurity = 2;
include('includes/session.inc');

$funcion=660;
include('includes/SecurityFunctions.inc');

$title = _('Reporte de Pagos DIOT');
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
     if ($fechainic > $fechafinc){
          $InputError = 1;
     prnMsg(_('La fecha inicial no puede ser mayor a la fecha final! Por favor seleccionar nuevamente los periodos de fecha'),'error');
     } else {
          $InputError = 0;
     }	
     
     
/* OBTENGO  FECHAS */

	
/*if $FromCriteria is not set then show a form to allow input	*/

	echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'><table>";
	
	
	echo '<tr><td colspan=2><p align=center><b>** SELECCIONA EL CRITERIO DE BUSQUEDA</b><br><br></td>';
	echo '</tr>';


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


/* SELECCIONA EL RANGO DE FECHAS */



	/************************************/
	//SELECCION RAZON SOCIAL
	echo '<tr><td>'._('X Razon Social:').'<td><select name="legalid">';
		
	///Pinta las razones sociales
	$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "'
			  GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.tagref";		

	$result=DB_query($SQL,$db);
	echo "<option selected value='0'>Todas las razones sociales...</option>";
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['legalid']) and $_POST['legalid']==$myrow["legalid"]){
			echo '<option selected value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
		} else {
			echo '<option value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
		}
	}
	echo '</select></td></tr>';	
	
	/* SELECCION DEL CATEGORIA DE PRODUCTOS */
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
	/* SELECCION DE AREA*/
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
	/* SELECCION DE DEPARTAMENTO*/
	echo '<tr><td>' . _('X Departamento') . ':' . "</td>
		<td><select tabindex='4' name='xDepartamento'>";

	$sql = "SELECT u_department, CONCAT(u_department,' - ',department) as name FROM departments";
	$result=DB_query($sql,$db);
	
	echo "<option selected value='0'>Todos los departamentos...</option>";

	while ($myrow=DB_fetch_array($result)){
	      if ($myrow['u_department'] == $_POST['xDepartamento']){
			echo "<option selected value='" . $myrow["u_department"] . "'>" . $myrow['name'];
	      } else {
		      echo "<option value='" . $myrow['u_department'] . "'>" . $myrow['name'];
	      }
	}
	echo '</select></td></tr>';
	
	/************************************/
	/* SELECCION DE LA UNIDAD DE NEGOCIO DE PRODUCTOS */
	echo "<tr><td>" . _('X Unidad de Negocio') . ":</td><td>";
	echo "<select name='unidadnegocio'>";
	$SQL = "SELECT  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription";//areas.areacode, areas.areadescription";
		$SQL = $SQL .	" FROM sec_unegsxuser u,tags t join areas ON t.areacode = areas.areacode ";
		$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
		$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' 
				ORDER BY t.tagref, areas.areacode";

	$ErrMsg = _('No transactions were returned by the SQL because');
	$TransResult = DB_query($SQL,$db,$ErrMsg);
	
	echo "<option selected value='0'>Todas a las que tengo accceso...</option>";
	
	while ($myrow=DB_fetch_array($TransResult)) {
		if ($myrow['tagref'] == $_POST['unidadnegocio']){
			echo "<option selected value='" . $myrow['tagref'] . "'>" . $myrow['tagdescription'] . "</option>";	
		}else{
			echo "<option value='" . $myrow['tagref'] . "'>" . $myrow['tagdescription'] . "</option>";
		}
	}
	 
	echo "</select>";
	echo "</td></tr>";
	/************************************/
/* SELECCION DE DEPARTAMENTO*/
	echo '<tr><td>' . _('X Tipo de Proveedor') . ':' . "</td>
		<td><select tabindex='4' name='xTipoProveedor'>";

	$sql = "SELECT *  FROM supplierstype";
	$result=DB_query($sql,$db);
	
	echo "<option selected value='0'>Todos los tipos...</option>";

	while ($myrow=DB_fetch_array($result)){
	      if ($myrow['typeid'] == $_POST['xTipoProveedor']){
			echo "<option selected value='" . $myrow["typeid"] . "'>" . $myrow['typename'];
	      } else {
		      echo "<option value='" . $myrow['typeid'] . "'>" . $myrow['typename'];
	      }
	}
	echo '</select></td></tr>';

	/************************************/
	/* SELECCION DEL PROVEEDOR */
	echo "<tr><td>" . _('X Proveedor') . ":</td><td>";
	echo "<select name='proveedor'>";
	$SQL = "SELECT  supplierid,suppname ";
		$SQL = $SQL .	" FROM suppliers where suppname<>'' ";
		$SQL = $SQL .	" ORDER BY suppname"; 
				

	
	$ErrMsg = _('No transactions were returned by the SQL because');
	$TransResult = DB_query($SQL,$db,$ErrMsg);
	
	echo "<option selected value='0'>Todos a las que tengo accceso...</option>";
	
	while ($myrow=DB_fetch_array($TransResult)) {
		if ($myrow['supplierid'] == $_POST['proveedor']){
			echo "<option selected value='" . $myrow['supplierid'] . "'>" . $myrow['suppname'] . "</option>";	
		}else{
			echo "<option value='" . $myrow['supplierid'] . "'>" . $myrow['suppname'] . "</option>";
		}
	}
	 
	echo "</select>";
	echo "</td></tr>";
	/************************************/
	
	echo '<tr><td><br><b>** DETALLE DEL REPORTE</b></td>
		<td>';
	echo '</td></tr>';
	
	echo '<tr><td>' . _('X Region/Sucursal/Docto') . ':' . "</td>
		<td><select tabindex='5' name='DetailedReport'>";
	
		
        if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Prov')
		echo "<option selected value='Prov'>" . _('X Proveedor');
	else
		echo "<option value='Prov'>" . _('X Proveedor');
		
        if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Docto')
		echo "<option selected value='Docto'>" . _('X Documento');
	else
		echo "<option value='Docto'>" . _('X Documento');
	echo '</select></td></tr>';
	
	
	echo '</table>
		<br><div class="centre"><input tabindex="6" type=submit name="ReportePantalla" value="' . _('Genera Reporte en Pantalla') . '"></div>';
	echo '<br><div class="centre"><input tabindex="7" type=hidden name="PrintPDF" value="' . _('Imprime Archivo PDF') . '"></div>';
	echo '<br><div class="centre"><input tabindex="7" type=submit name="Generabatch" value="' . _('Genera Archivo Batch') . '"></div>';
////
If (isset($_POST['ReportePantalla'])){
			
	$sqliva="select purchtaxglaccount from taxauthorities";
	$result = DB_query($sqliva,$db);
	$myrow = DB_fetch_row($result);
	$taxglcodepaid=$myrow[0];
	
	
	$SQL = "Select tags.tagdescription as name, systypescat.typename, supptrans.supplierno,
		       suppliers.suppname, suppliers.taxid, suppliers.lastpaiddate, lastpaid,
		       supptrans.trandate, supptrans.origtrandate, supptrans.suppreference, supptrans.transtext, supptrans.transno,
		       supptrans.duedate, supptrans.promisedate, supptrans.ovamount, supptrans.ovgst, supptrans.alloc, aplicado, total, siniva, iva,
		       CASE WHEN aplicado is null THEN 1 ELSE 0 END as SinAplicacion,
			  (case when conta.monto is null then 0 else conta.monto end) as retconta,
			 (case when conta2.cuenta is null then 0 else conta2.cuenta end) as excento,
			(case when conta3.tipoconta=supptrans.type and supptrans.transno=conta3.typeno then ovamount*-1  else 0 end) as ivatasacero
		from supptrans JOIN systypescat ON supptrans.type = systypescat.typeid
		      JOIN tags ON supptrans.tagref = tags.tagref
		      JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
		      JOIN areas ON tags.areacode = areas.areacode
		      JOIN regions ON areas.regioncode = regions.regioncode
		      LEFT JOIN suppliers ON supptrans.supplierno = suppliers.supplierid
		      LEFT JOIN Tipooperacionproveedor ON Tipooperacionproveedor.u_typeoperation =  suppliers.u_typeoperation and Tipooperacionproveedor.flagaplica = 1
		      LEFT JOIN ( Select suppallocs.transid_allocfrom,
		      					sum(((suppallocs.amt/(1+(suppt2.ovgst/suppt2.ovamount)))))as aplicado,
		      		 			sum(suppt2.ovamount) as siniva, sum(suppt2.ovgst) as iva, sum(suppt2.ovamount+suppt2.ovgst)  as total
							from suppallocs LEFT JOIN supptrans as suppt2 ON  suppallocs.transid_allocto = suppt2.id
							GROUP BY suppallocs.transid_allocfrom
		      		) as qryapplica ON supptrans.id = qryapplica.transid_allocfrom
		        LEFT JOIN ( SELECT SUM(amount*-1) as monto,type as tipoconta,typeno
		      			  FROM gltrans
		      			  WHERE account in ('".$_SESSION['CompanyRecord']["gllink_retencioniva"] ."','".$_SESSION['CompanyRecord']["gllink_retencionIVAarrendamiento"] ."','".$_SESSION['CompanyRecord']["gllink_retencionFletes"] ."')
		      				group by type,typeno
		      		) as conta ON conta.tipoconta=supptrans.type and supptrans.transno=conta.typeno
		      	 LEFT JOIN ( SELECT count(*) as cuenta,type as tipoconta,typeno
		      			  FROM gltrans
		      			  INNER JOIN taxcategories on taxcategories.taxcatname=trim(replace(trim(replace(cat_cuenta,'IVA2','')),'IVA',''))
		      			  WHERE taxcategories.taxcatid=5 -- AND gltrans.amount<0
		      			  group by type,typeno
		      		) as conta2 ON conta2.tipoconta=supptrans.type and supptrans.transno=conta2.typeno
		      	  LEFT JOIN ( SELECT count(*) as cuentacero,type as tipoconta,typeno
		      			  FROM gltrans
		      			  INNER JOIN taxcategories on taxcategories.taxcatname=trim(replace(trim(replace(cat_cuenta,'IVA2','')),'IVA',''))
		      			  WHERE taxcategories.taxcatid=2 -- AND gltrans.amount<0
		      			  group by type,typeno
		      		) as conta3 ON conta3.tipoconta=supptrans.type and supptrans.transno=conta3.typeno
		where
		      supptrans.type in ('22','24','121','501',32,33)
		      and transno not in (select transno from supptrans where type in (32,33) and typeoperationdiot=0 ) 
		      and (suppliers.supplierid = '".$_POST['proveedor']."' or '".$_POST['proveedor']."'='0')
		      and (suppliers.typeid = '".$_POST['xTipoProveedor']."' or '".$_POST['xTipoProveedor']."'='0')
		      and (tags.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
		      and (areas.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
		      and (supptrans.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
		      and supptrans.trandate >= '".$fechaini."' and supptrans.trandate <= '".$fechafin."'";
		      
	if ($_POST['DetailedReport'] == 'Docto')
		$SQL .= "Order by SinAplicacion, supptrans.origtrandate, tags.tagdescription, taxid";
	else
		$SQL .= "Order by SinAplicacion, suppliers.suppname";
	
	//echo '<pre><br>sql:'.$SQL;
	$ReportResult = DB_query($SQL,$db,'','',False,False); /*dont trap errors */

	if (DB_error_no($db) !=0) {
	  $title = _('Reporte de Pagos a Proveedores') . ' - ' . _('Reporte de Problema') ;
	  //include("includes/header.inc");
	  prnMsg(_('Los detalles de proveedores no se pudieron recuperar porque el SQL fallo') .  ' ' . DB_error_msg($db),'error');

	   if ($debug==1){
		echo "<br>".$SQL;
	   }
	   exit;
	}
	
	$DoctoTotPagos = 0;
	$VenceTotPagos = 0;
	$ProvTotPagos = 0;
	$RegionTotPagos = 0;
	
	$TotPagos = 0;
	$TotPagosSIN = 0;
	$TotPagosT0 = 0;
	$TotPagosT16 = 0;
	
	$RegionTotPagosT0 = 0;
	$RegionTotPagosT16 = 0;

	$TotPagosSINT0 = 0;
	$TotPagosSINT16 = 0;
	
	$TotPagosExc = 0;
	$RegionTotPagosExc = 0;
	$ProvTotPagosExc = 0;

	$TotPagosExcento = 0;
	$RegionTotPagosExcento = 0;
	$ProvTotPagosExcento = 0;
	
	echo '<table cellpadding=2 border=1>';
			
	$headerLineaProductos = '<tr>
			<th><b>' . _('Region') . '</b></th>
			<th><b>' . _('Documento') . '</b></th>
			<th><b>' . _('Codigo') . '</b></th>
			<th><b>' . _('Nombre') . '</b></th>
			<th><b>' . _('RFC') . '</b></th>
			<th><b>' . _('Folio') . '</b></th>
			<th><b>' . _('Fecha Pago') . '</b></th>
			<th><b>' . _('Monto Pago') . '</b></th>
			<th><b>' . _('Base 16%') . '</b></th>
			<th><b>' . _('Base 0%') . '</b></th>
			
			';
	if( $_POST['DetailedReport'] == 'Docto'){
		$headerLineaProductos=$headerLineaProductos.'<th><b>' . _('IVA') . '</b></th>';
	}
	
	$headerLineaProductos=$headerLineaProductos.'<th><b>' . _('Retenido') . '</b></th>';
	$headerLineaProductos=$headerLineaProductos.'<th><b>' . _('Excento') . '</b></th>';
	$headerLineaProductos=$headerLineaProductos.'</tr>';
	$i = 0;
	
	$antRegion = '';
	$antProv = '';
	$antVence = '';
	$antNombre = '';
	
	$lineasConMismoDocumento = 0;
	$primeraEntrada = 1;
	
	echo $headerLineaProductos;
	
	While ($InvAnalysis = DB_fetch_array($ReportResult,$db)){
	
		$thisVence = $InvAnalysis['duedate'];
		$thisRegion = $InvAnalysis['name'];
		$thisProv = $InvAnalysis['taxid'];
		$thisNombre = $InvAnalysis['suppname'];
	      
		$lineasConMismoDocumento = $lineasConMismoDocumento + 1;
		
		
		/* LAS AGRUPACIONES Y SUBTOTALES SON DIFERENTES CUANDO LO PIDO POR PROVEEDOR */
		if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Prov') {
		
		
			if ($antVence <> $thisVence) {
				if ($primeraEntrada == 0) {
					
					/*
					echo '<tr>';
					echo '<td class=norm colspan=7><b>- TOTAL '.$antVence.'</b></td>
						<td class=normnum>'.number_format($VenceTotPagos,2).'</td>
						</tr>';
					*/
					$VenceTotPagos = 0;
				}
			}
			
			if ($antNombre <> $thisNombre) {
				if ($primeraEntrada == 0) {
					
						
					echo '<tr>';
					echo '<td colspan=7></td>
						<td class=normnum><b>'.number_format($RegionTotPagos,2).'</b></td>
						<td class=normnum><b>'.number_format($RegionTotPagosT16,2).'</b></td>
						<td class=normnum><b>'.number_format($RegionTotPagosT0,2).'</b></td>
						<td class=normnum><b>'.number_format($RegionTotPagosExc,2).'</b></td>
						<td class=normnum><b>'.number_format($RegionTotPagosExcento,2).'</b></td>
						</tr>';
					echo '<tr>';
					$RegionTotPagos = 0;
				    
					$RegionTotPagosT0 = 0;
					$RegionTotPagosExc=0;
					$RegionTotPagosT16 = 0;
					$RegionTotPagosExcento = 0;
				}
			}
	
			
			
			if ($antVence <> $thisVence) {
				/*echo '<tr>';
				echo '<td class=GroupTableRows colspan=8><b>'.$thisVence.'</b></td>
					</tr>';*/
				
				
				$antVence = $thisVence;
			}
			
		} else {
			if ($antRegion <> $thisRegion or $antVence <> $thisVence) {
				if ($primeraEntrada == 0) {
					if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Docto') {
					  /*
					  echo '<tr>';
					  echo '<td class=norm colspan=7><b>- - TOTAL '.$antRegion.'</b></td>
						  <td class=normnum>'.number_format($RegionTotPagos,2).'</td>
						  </tr>';
					  */
					}
					$RegionTotPagos = 0;
					$RegionTotPagosT0 = 0;
					$RegionTotPagosT16 = 0;
				}
			}
	
			if ($antVence <> $thisVence) {
				if ($primeraEntrada == 0) {
					
					/*
					echo '<tr>';
					echo '<td class=norm colspan=7><b>- TOTAL '.$antVence.'</b></td>
						<td class=normnum>'.number_format($VenceTotPagos,2).'</td>
						</tr>';
					*/
					$VenceTotPagos = 0;
				}
			}
			
			
			if ($antVence <> $thisVence) {
				/*echo '<tr>';
				echo '<td class=GroupTableRows colspan=8><b>'.$thisVence.'</b></td>
					</tr>';
				echo $headerLineaProductos;
				*/
				$antVence = $thisVence;
			}
			
			if ($antRegion <> $thisRegion or $antVence <> $thisVence) {
				/*echo '<tr>';
				echo '<td class=GroupTableRows colspan=8><b>- '.$thisRegion.'</b></td>
					</tr>';*/
				
				$antRegion = $thisRegion;
				
			}
		
		}
		$primeraEntrada = 0;
		
		if ($InvAnalysis['SinAplicacion'] == 1 AND $primeraEntrada2 != 1) {
			echo '<tr style="background-color:orange" >';
				/*DESPLIEGA VALOR DE SUBTOTAL X UN Y FOLIO*/
			      echo '<td class=norm colspan=7><b>TOTAL GENERAL</b></td>
				      <td class=normnum>'.number_format($TotPagosSIN,2).'</td>
				     
				      <td class=normnum>'.number_format($TotPagosT16,2).'</td>
				       <td class=normnum>'.number_format($TotPagosT0,2).'</td>		
				      		';
			      
			      if( $_POST['DetailedReport'] == 'Docto'){
			      	echo  '<td class=normnum>'.number_format($TRt16,2).'</td>';
			      }
			      echo  '<td class=normnum>'.number_format($TotPagosExc,2).'</td>';
			      echo  '<td class=normnum>'.number_format($TotPagosExcento,2).'</td>';
				     echo '</tr>';
			$primeraEntrada2 = 1;
			
			echo '<tr>';
				/*DESPLIEGA VALOR DE SUBTOTAL X UN Y FOLIO*/
			      echo '<td class=norm colspan=8><br><br><b>PAGOS SIN APLICAR</b></td>
				      </tr>';
				      
			echo $headerLineaProductos;
			
			$TotPagosSIN = 0;
			$TotPagosSINT0 = 0;
			$TotPagosSINT16 = 0;
			$TRt16=0;
			$TotPagosExc=0;
			 $TotPagosExcento= 0;
		}
		
		/* LAS AGRUPACIONES Y SUBTOTALES SON DIFERENTES CUANDO LO PIDO POR PROVEEDOR */
		if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Prov') {
			if ($antNombre <> $thisNombre or $antVence <> $thisVence) {
				echo '<tr>';
				echo '<td class=GroupTableRows colspan=12><b>- '.$thisNombre.'</b></td>
					</tr>';
					
				echo $headerLineaProductos;
				
				$antNombre = $thisNombre;
			}
		}
		
		if ($InvAnalysis['aplicado'] != null) {
			if ($i == 0) {
				echo '<tr class="EvenTableRows">';
				$i = 1;
			} else {
				echo '<tr class="OddTableRows">';
				$i = 0;
			}
		} else {
			echo '<tr style="background-color:yellow">';
		}
	      
		
		$flagexcento=0;
		if($InvAnalysis['excento']>0){
			$flagexcento=1;
			$InvAnalysis['excento']=0;
		}
		
		
		
		echo '<td class=peque>'.$InvAnalysis['name'].'</td>
			<td class=peque >'.substr($InvAnalysis['typename'],0,12).'</td>
			<td class=peque>'.$InvAnalysis['supplierno'].'</td>
			<td class=peque>'.$InvAnalysis['suppname'].'</td>
			<td class=peque>'.$InvAnalysis['taxid'].'</td>
			<td class=peque>'.$InvAnalysis['transno'].'</td>
			<td class=pequenum>'.$InvAnalysis['trandate'].'</td>
			<td class=pequenum>'.'$ '.number_format(($InvAnalysis['ovamount']+$InvAnalysis['ovgst'])*-1,2).'</td>';
				
		if ($InvAnalysis['aplicado'] != null) {
			$tasacero=round((1-(($InvAnalysis['iva']/0.16)/$InvAnalysis['siniva']))*$InvAnalysis['aplicado']);
			$tasaexcenta=0;
			
			if($flagexcento==1){
				$tasaexcenta=$tasacero;
				$tasacero=0;
			}
			
			$tasacero=$InvAnalysis['ivatasacero'];
			if($tasacero>0){
				$base16=0;
			}else{
				$base16=round((($InvAnalysis['iva']/0.16)/$InvAnalysis['siniva'])*$InvAnalysis['aplicado']);
			}
			echo '	<td class=pequenum>'.'$'.number_format($base16,2).'</td>';
			echo '	<td class=pequenum> '.'$'.number_format($tasacero,2).'</td>';
			
		} else {
			echo '	<td class=pequenum>'.'$'.number_format(($InvAnalysis['ovamount']+$InvAnalysis['ovgst'])*-1,2).'</td>';
			echo '	<td class=pequenum>'.'$'.number_format(0,2).'</td>';
		}
		
		if( $_POST['DetailedReport'] == 'Docto'){
			
			if ($InvAnalysis['aplicado'] != null) {
				$t16=round((($InvAnalysis['iva']/0.16)/$InvAnalysis['siniva'])*$InvAnalysis['aplicado']);
				$t16=(($t16)*.16);
				
				$tasacero=$InvAnalysis['ivatasacero'];
				if($tasacero>0){
					$t16=0;
				}
				
				echo '	<td class=pequenum>'.'$'.number_format($t16,2).'</td>';
			}else{
				$t16=($InvAnalysis['ovamount']+$InvAnalysis['ovgst'])*-1;
				$t16=(($t16)*.16);
				$tasacero=$InvAnalysis['ivatasacero'];
				if($tasacero>0){
					$t16=0;
				}
				
				echo '	<td class=pequenum>'.'$'.number_format(($t16),2).'</td>';
			}
		}
		echo '	<td class=pequenum>'.'$'.number_format($InvAnalysis['retconta'],2).'</td>';
		echo '	<td class=pequenum>'.'$'.number_format($tasaexcenta,2).'</td>';
		
		echo '	</tr>';
		$Tt16=$Tt16+$t16;
		$TRt16=$TRt16+$t16;
		
		if ($InvAnalysis['aplicado'] != null) {
			
			$TotPagosT0 = $TotPagosT0 + $tasacero;//round((1-(($InvAnalysis['iva']/0.16)/$InvAnalysis['siniva']))*$InvAnalysis['aplicado']);
			$TotPagosT16 = $TotPagosT16 + $base16;//round((($InvAnalysis['iva']/0.16)/$InvAnalysis['siniva'])*$InvAnalysis['aplicado']);
			
			$TotPagosSINT0 = $TotPagosSINT0 +$tasacero;//+ round((1-(($InvAnalysis['iva']/0.16)/$InvAnalysis['siniva']))*$InvAnalysis['aplicado']);
			$TotPagosSINT16 = $TotPagosSINT16 + round((($InvAnalysis['iva']/0.16)/$InvAnalysis['siniva'])*$InvAnalysis['aplicado']);
			
			$RegionTotPagosT0 = $RegionTotPagosT0 +$tasacero;//+ round((1-(($InvAnalysis['iva']/0.16)/$InvAnalysis['siniva']))*$InvAnalysis['aplicado']);
			$RegionTotPagosT16 = $RegionTotPagosT16 + round((($InvAnalysis['iva']/0.16)/$InvAnalysis['siniva'])*$InvAnalysis['aplicado']);	
			
		} else {
			$TotPagosT0 = $TotPagosT0 + 0;
			$TotPagosT16 = $TotPagosT16 + ($InvAnalysis['ovamount']+$InvAnalysis['ovgst'])*-1;
			
			$TotPagosSINT0 = $TotPagosSINT0 + 0;
			$TotPagosSINT16 = $TotPagosSINT16 + ($InvAnalysis['ovamount']+$InvAnalysis['ovgst'])*-1;
			
			$RegionTotPagosT0 = $RegionTotPagosT0 + 0;
			$RegionTotPagosT16 = $RegionTotPagosT16 + ($InvAnalysis['ovamount']+$InvAnalysis['ovgst'])*-1;
		}
		
		$TotPagosExcento =$TotPagosExcento +$tasaexcenta;
		
		$RegionTotPagosExcento = $RegionTotPagosExcento+$tasaexcenta;
		$ProvTotPagosExcento = $ProvTotPagosExcento+$tasaexcenta;
		$GtotPagosExcento=$GtotPagosExcento+$tasaexcenta;
		
		$DoctoTotPagos = $DoctoTotPagos + ($InvAnalysis['ovamount']+$InvAnalysis['ovgst'])*-1;
		$VenceTotPagos = $VenceTotPagos + ($InvAnalysis['ovamount']+$InvAnalysis['ovgst'])*-1;
		$ProvTotPagos = $ProvTotPagos + ($InvAnalysis['ovamount']+$InvAnalysis['ovgst'])*-1;
		$RegionTotPagos = $RegionTotPagos + ($InvAnalysis['ovamount']+$InvAnalysis['ovgst'])*-1;
		
		$TotPagos = $TotPagos + ($InvAnalysis['ovamount']+$InvAnalysis['ovgst'])*-1;
		$TotPagosSIN = $TotPagosSIN + ($InvAnalysis['ovamount']+$InvAnalysis['ovgst'])*-1;
		
		$TotPagosExc=$TotPagosExc+$InvAnalysis['retconta'];
		
		$RegionTotPagosExc = $RegionTotPagosExc+$InvAnalysis['retconta'];
		$ProvTotPagosExc = $ProvTotPagosExc+$InvAnalysis['retconta'];
		$GtotPagosExc=$GtotPagosExc+$InvAnalysis['retconta'];
		
			
	} /*end while loop */
	   
	   
	if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Prov') {
		
		if ($primeraEntrada == 0) {
			echo '<tr>';
			echo '<td colspan=7></td>
				<td class=normnum><b>'.number_format($RegionTotPagos,2).'</b></td>
			
				
					<td class=normnum><b>'.number_format($RegionTotPagosT16,2).'</b></td>
					<td class=normnum><b>'.number_format($RegionTotPagosT0,2).'</b></td>		
						';
			
				
			if( $_POST['DetailedReport'] == 'Docto'){
				echo  '<td class=normnum>'.number_format($TRt16,2).'</td>';
			}
			echo  '<td class=normnum>'.number_format($RegionTotPagosExc,2).'</td>';
			echo  '<td class=normnum>'.number_format($RegionTotPagosExcento,2).'</td>';
				echo '</tr>';
			echo '<tr>';
			
			echo '<td class=norm colspan=11><b>---------------------------------------------------------</b></td>
				<td class=normnum></td>
				</tr>';
		    $RegionTotPagos = 0;
		}
			
	  } else {
		
		if ($primeraEntrada == 0) {
		      if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Docto') {
			/*
			echo '<tr>';
			echo '<td class=norm colspan=7><b>- - TOTAL '.$antRegion.'</b></td>
				<td class=normnum>'.number_format($RegionTotPagos,2).'</td>
				</tr>';
			*/
		      }
		      $RegionTotPagos = 0;
		}
	  
	  }
	  
	/*
	echo '<tr style="background-color:orange" >';
	
      echo '<td class=norm colspan=6><br><br><b>TOTAL PAGOS SIN APLICAR</b></td>
	      <td class=normnum>'.number_format($TotPagosSIN,2).'</td>
	      <td class=normnum>'.number_format($TotPagosSINT16,2).'</td>
	      <td class=normnum>'.number_format($TotPagosSINT0,2).'</td>
	      </tr>';
	*/
	
      echo '<tr style="background-color:yellow" >';
	/*DESPLIEGA VALOR DE SUBTOTAL X UN Y FOLIO*/
      echo '<td class=norm colspan=7><b>TOTAL GENERAL</b></td>
	      <td class=normnum>'.number_format($TotPagos,2).'</td>
	      <td class=normnum>'.number_format($TotPagosT16,2).'</td>
	      <td class=normnum>'.number_format($TotPagosT0,2).'</td>
	      ';
      if( $_POST['DetailedReport'] == 'Docto'){
      	echo  '<td class=normnum>'.number_format($Tt16,2).'</td>';
      }
      echo  '<td class=normnum>'.number_format($GtotPagosExc,2).'</td>';
      echo  '<td class=normnum>'.number_format($GtotPagosExcento,2).'</td>';
	echo '</tr>';
	/*echo '<tr style="background-color:yellow" >';
	
      echo '<td class=norm colspan=7></td>
	      <td class=normnum><b>IVA</b></td>
	      <td class=normnum>'.number_format(($TotPagosT16)*.16,2).'</td>
	      <td class=normnum></td>
	      </tr>';*/
			
} elseIf (isset($_POST['PrintPDF'])
	AND isset($_POST['FromCriteria'])
	AND strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND strlen($_POST['ToCriteria'])>=1){


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

} elseif(isset($_POST['Generabatch'])) { /*The option to print PDF was not hit */
//echo 'entraaa';

	//trae cuenta de iva pagado
	
	;
	$sqliva="select purchtaxglaccount from taxauthorities";
	$result = DB_query($sqliva,$db);
	$myrow = DB_fetch_row($result);
	$taxglcodepaid=$myrow[0];
	
	$SQL = "Select tags.tagdescription as name, systypescat.typename, supptrans.supplierno,
		       suppliers.suppname, suppliers.taxid, suppliers.lastpaiddate, lastpaid,
		       supptrans.trandate, supptrans.origtrandate, supptrans.suppreference, supptrans.transtext, supptrans.transno,
		       supptrans.duedate, supptrans.promisedate,sum(supptrans.ovamount) as ovamount, sum(supptrans.ovgst) as ovgst,
		       sum(supptrans.alloc) as alloc, sum(aplicado) as aplicado, sum(total) as total, sum(siniva) as siniva, sum(iva) as iva,
			   sum(cuenta) as ivaexcento,
		       CASE WHEN aplicado is null THEN 1 ELSE 0 END as SinAplicacion,
			
		       suppliers.u_typediot,
			   suppliers.u_typeoperation,
			case when type in (33,32) and typeoperationdiot=1 then ovgst else -1 end as ivadevo,
			sum(case when conta.monto is null then 0 else conta.monto end) as retconta
		from supptrans JOIN systypescat ON supptrans.type = systypescat.typeid
			
		      JOIN tags ON supptrans.tagref = tags.tagref
		      JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
		      JOIN areas ON tags.areacode = areas.areacode
		      JOIN regions ON areas.regioncode = regions.regioncode
		      LEFT JOIN suppliers ON supptrans.supplierno = suppliers.supplierid
		      	join Tipooperacionproveedor ON Tipooperacionproveedor.u_typeoperation= suppliers.u_typeoperation
		      LEFT JOIN (Select suppallocs.transid_allocfrom,
		      		/*sum(suppallocs.amt) as aplicado, */
		      		sum(((suppallocs.amt/(1+(suppt2.ovgst/suppt2.ovamount))))) as aplicado,
		      		sum(suppt2.ovamount) as siniva, 
		      		sum(suppt2.ovgst) as iva, sum(suppt2.ovamount+suppt2.ovgst)  as total
						from suppallocs LEFT JOIN supptrans as suppt2 ON  suppallocs.transid_allocto = suppt2.id
						GROUP BY suppallocs.transid_allocfrom) as qryapplica ON supptrans.id = qryapplica.transid_allocfrom
		      LEFT JOIN ( SELECT SUM(amount*-1) as monto,type as tipoconta,typeno
		      			  FROM gltrans
		      			  WHERE account in ('".$_SESSION['CompanyRecord']["gllink_retencioniva"] ."','".$_SESSION['CompanyRecord']["gllink_retencionIVAarrendamiento"] ."','".$_SESSION['CompanyRecord']["gllink_retencionFletes"] ."')
		      				group by type,typeno
		      		) as conta ON conta.tipoconta=supptrans.type and supptrans.transno=conta.typeno
		      			  		
		        LEFT JOIN ( SELECT count(*) as cuenta,type as tipoconta,typeno
		      			  FROM gltrans
		      			  WHERE account in ('".$taxglcodepaid ."')
		      				group by type,typeno
		      		) as conta2 ON conta2.tipoconta=supptrans.type and supptrans.transno=conta2.typeno
		      			  					  		
		where Tipooperacionproveedor.flagaplica=1
		      AND supptrans.type in ('22','24','121','501')
		      and (suppliers.supplierid = '".$_POST['proveedor']."' or '".$_POST['proveedor']."'='0')
		      and (suppliers.typeid = '".$_POST['xTipoProveedor']."' or '".$_POST['xTipoProveedor']."'='0')
		      and (tags.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
		      and (areas.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
		      and (supptrans.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
		      		and abs(ovamount+ovgst)>0
		      and supptrans.trandate >= '".$fechaini."' and supptrans.trandate <= '".$fechafin."'
		      		";
	$SQL .= " Group by suppliers.taxid ";
		      
	if ($_POST['DetailedReport'] == 'Docto')
		$SQL .= "Order by SinAplicacion, supptrans.origtrandate, tags.tagdescription, taxid";
	else
		$SQL .= "Order by SinAplicacion, suppliers.suppname";
	
	
	$SQL = "Select 
			 systypescat.typename, supptrans.supplierno,
		       suppliers.suppname, suppliers.taxid, suppliers.lastpaiddate, lastpaid,
		       supptrans.trandate, supptrans.origtrandate, supptrans.suppreference,
			supptrans.transtext, supptrans.transno,
		       supptrans.duedate, supptrans.promisedate,sum(supptrans.ovamount) as ovamount, 
			sum(supptrans.ovgst) as ovgst,
		       sum(supptrans.alloc) as alloc, sum(aplicado) as aplicado, sum(total) as total, 
			sum(siniva) as siniva, sum(iva) as iva,
			    sum(case when conta2.tipoconta=supptrans.type and supptrans.transno=conta2.typeno then ovamount*-1  else 0 end ) as ivaexcento,
		       suppliers.u_typediot,
			   suppliers.u_typeoperation,
				sum(case when conta3.tipoconta=supptrans.type and supptrans.transno=conta3.typeno then ovamount*-1  else 0 end) as ivatasacero,
			sum(case when conta4.tipoconta=supptrans.type and supptrans.transno=conta4.typeno then ovamount*-1  else 0 end) as ivatasabase,
			sum( CASE WHEN aplicado is null THEN 1 ELSE 0 END) as SinAplicacion,
			  sum(case when conta.monto is null then 0 else conta.monto end) as retconta,
			 sum(case when conta2.cuenta is null then 0 else conta2.cuenta end) as excento,
			sum(case when type in (33,32) then ovgst*-1 else 0 end) as ivadevo
		from supptrans JOIN systypescat ON supptrans.type = systypescat.typeid
		      JOIN tags ON supptrans.tagref = tags.tagref
		      JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
		      JOIN areas ON tags.areacode = areas.areacode
		      JOIN regions ON areas.regioncode = regions.regioncode
		      LEFT JOIN suppliers ON supptrans.supplierno = suppliers.supplierid
		       JOIN Tipooperacionproveedor ON Tipooperacionproveedor.u_typeoperation =  suppliers.u_typeoperation and Tipooperacionproveedor.flagaplica = 1
		      LEFT JOIN ( Select suppallocs.transid_allocfrom,
		      					sum(((suppallocs.amt/(1+(suppt2.ovgst/suppt2.ovamount)))))as aplicado,
		      		 			sum(suppt2.ovamount) as siniva, sum(suppt2.ovgst) as iva, sum(suppt2.ovamount+suppt2.ovgst)  as total
							from suppallocs LEFT JOIN supptrans as suppt2 ON  suppallocs.transid_allocto = suppt2.id
							GROUP BY suppallocs.transid_allocfrom
		      		) as qryapplica ON supptrans.id = qryapplica.transid_allocfrom
		        LEFT JOIN ( SELECT SUM(amount*-1) as monto,type as tipoconta,typeno
		      			  FROM gltrans
		      			  WHERE account in ('".$_SESSION['CompanyRecord']["gllink_retencioniva"] ."','".$_SESSION['CompanyRecord']["gllink_retencionIVAarrendamiento"] ."','".$_SESSION['CompanyRecord']["gllink_retencionFletes"] ."')
		      				group by type,typeno
		      		) as conta ON conta.tipoconta=supptrans.type and supptrans.transno=conta.typeno
		      	 LEFT JOIN ( SELECT count(*) as cuenta,type as tipoconta,typeno
		      			  FROM gltrans
		      			  INNER JOIN taxcategories on taxcategories.taxcatname=trim(replace(trim(replace(cat_cuenta,'IVA2','')),'IVA',''))
		      			  WHERE taxcategories.taxcatid=5 -- AND gltrans.amount<0
		      			  group by type,typeno
		      		) as conta2 ON conta2.tipoconta=supptrans.type and supptrans.transno=conta2.typeno
		      	 LEFT JOIN ( SELECT count(*) as cuentacero,type as tipoconta,typeno
		      			  FROM gltrans
		      			  INNER JOIN taxcategories on taxcategories.taxcatname=trim(replace(trim(replace(cat_cuenta,'IVA2','')),'IVA',''))
		      			  WHERE taxcategories.taxcatid=2 -- AND gltrans.amount<0
		      			  group by type,typeno
		      		) as conta3 ON conta3.tipoconta=supptrans.type and supptrans.transno=conta3.typeno
		      	 LEFT JOIN ( SELECT count(*) as cuentacero,type as tipoconta,typeno
		      			  FROM gltrans
		      			  INNER JOIN taxcategories on taxcategories.taxcatname=trim(replace(trim(replace(cat_cuenta,'IVA2','')),'IVA',''))
		      			  WHERE taxcategories.taxcatid=4 -- AND gltrans.amount<0
		      			  group by type,typeno
		      		) as conta4 ON conta4.tipoconta=supptrans.type and supptrans.transno=conta4.typeno
		
		where
		      supptrans.type in ('22','24','121','501',32,33)
		      and (suppliers.supplierid = '".$_POST['proveedor']."' or '".$_POST['proveedor']."'='0')
		      and (suppliers.typeid = '".$_POST['xTipoProveedor']."' or '".$_POST['xTipoProveedor']."'='0')
		      and (tags.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
		      and (areas.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
		      and (supptrans.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
		      and supptrans.trandate >= '".$fechaini."' and supptrans.trandate <= '".$fechafin."'";
	$SQL .= " Group by suppliers.taxid ";
	if ($_POST['DetailedReport'] == 'Docto')
		$SQL .= "Order by SinAplicacion, supptrans.origtrandate, tags.tagdescription, taxid";
	else
		$SQL .= "Order by SinAplicacion, suppliers.suppname";
	
	
	//echo '<pre>'.$SQL;
	$ReportResult = DB_query($SQL,$db,'','',False,False); /*dont trap errors */

	if (DB_error_no($db) !=0) {
	  $title = _('Reporte de Pagos a Proveedores') . ' - ' . _('Reporte de Problema') ;
	  //include("includes/header.inc");
	  prnMsg(_('Los detalles de proveedores no se pudieron recuperar porque el SQL fallo') .  ' ' . DB_error_msg($db),'error');

	   if ($debug==1){
		echo "<br>".$SQL;
	   }
	   exit;
	}
	
	$antRegion = '';
	$antProv = '';
	$antVence = '';
	$antNombre = '';
	
	$lineasConMismoDocumento = 0;
	$primeraEntrada = 1;
	
	echo $headerLineaProductos;
	$indice=1;
	$archivo="";
	While ($InvAnalysis = DB_fetch_array($ReportResult,$db)){
		
		if ($indice == 1){
			$mitxt = "SATXMes/DIOT".trim($fechaini).trim($fechafin).".txt";
			$mitxtdos="DIOT".trim($fechaini).trim($fechaini);//Ponele el nombre que quieras al archivo
			$fp = fopen($mitxt,"w");
		}
	
	
		$flagexcento=0;
		if($InvAnalysis['excento']>0){
			$flagexcento=1;
			$InvAnalysis['excento']=0;
		}
		$tasaexcenta=0;
		if ($InvAnalysis['aplicado'] != null) {
			// calculo tasa cero
			$tasacero=round((1-(($InvAnalysis['iva']/0.16)/$InvAnalysis['siniva']))*$InvAnalysis['aplicado']);
			
			//	echo 'xxxx<br>monto base:'.$montobase*.16.'iva:'.$InvAnalysis['iva'].'tasas cero:'.($montobase*.16)-$InvAnalysis['iva'];
			$ivaexcento=0;
			if($flagexcento==1){
				$tasaexcenta=$tasacero;
				$tasacero=0;
			}
			
			$montobase=round((($InvAnalysis['iva']/0.16)/$InvAnalysis['siniva'])*$InvAnalysis['aplicado']);
			if(abs(($montobase*.16)-$InvAnalysis['iva'])<=1){
				$tasacero=0;
			}
			
		} else {
			$montobase=round((($InvAnalysis['iva']/0.16)/$InvAnalysis['siniva'])*$InvAnalysis['aplicado']);
			$tasacero=0;
			$ivaexcento=0;
		}
	
		if($InvAnalysis['ivadevo']>0){
			$ivaabono=number_format(abs($InvAnalysis['ivadevo']), 0, '.', '');
		}else{
			$ivaabono=0;
			$ivaabono=number_format($ivaabono, 0, '.', '');
		}
		//echo 'ivadebo:'.$
		if($InvAnalysis['retconta']>0){
			$retconta=number_format(abs($InvAnalysis['retconta']), 0, '.', '');
		}else{
			$retconta=0;
			$retconta=number_format($retconta, 0, '.', '');
		}
		
		$tasacero=number_format(abs($InvAnalysis['ivatasacero']), 0, '.', '');
		$tasaexcenta=number_format(abs($InvAnalysis['ivaexcento']), 0, '.', '');
		$montobase=number_format(abs($InvAnalysis['ivatasabase']), 0, '.', '');
		
		// importacion va en el 18
		$archivo='';
		$archivo=trim($InvAnalysis['u_typediot'])."|".trim(($InvAnalysis['u_typeoperation']));
		$archivo=$archivo."|".trim(($InvAnalysis['taxid']))."|||||";
		//$archivo=$archivo."|";
		$archivo=$archivo.abs($montobase).'||0|0||0||||||'.abs($tasacero).'|'.abs($tasaexcenta).'|'.$retconta.'|'.$ivaabono."|".chr(13).chr(10);
		
		
		$archivo_utf8=$archivo;
		fwrite($fp,$archivo_utf8);
		
		
		
		
		
		$indice=$indice+1;
		
			
	} /*end while loop */
	   
	 fclose($fp);
	//$ok=comprimir($mitxt);
/*
	if ($ok)
		echo "Archivo comprimido correctamente con el nombre ".$ok;
	*/
	echo "<br><br><div class=centre><a target='_blank' href=\"".$mitxt."\"><font size=3 ><b>Descargar Archivo ".$mitxtdos."
	<font size=1  color=darkred ><br>Nota: Click Derecho -->Guardar Destino para bajar el archivo de manera local.</b></font></a></div><br><br>";  
	
	
	
	

} /*end of else not PrintPDF */

include('includes/footer.inc');
?>
