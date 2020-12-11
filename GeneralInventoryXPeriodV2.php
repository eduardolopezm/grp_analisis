<?php
/* $Revision: 1.13 $ */

/*desarrollo- 08/OCTUB/2013 - Correccion de inclusion de Work Order Issues y Recepciones */
/*desarrollo- 18/MARZO/2011 - Elimine condicion de bandera para productos inventariables pu es no checaba contra recepciones */
/*desarrollo- 17/MARZO/2011 - Modifique calculo de conceptos manuales y montos de detalle al final del reporte */
/*desarrollo- 17/MARZO/2011 - Cambio el calculo del costo desde el stockmoves y no del costo promedio x legal */

/*cambios
1.- se agrego el include('includes/SecurityFunctions.inc');
2.- se arreglo la variable $funcion*/
$PageSecurity = 2;

include('includes/session.inc');


$funcion=816;
include('includes/SecurityFunctions.inc');

function LocalSinCeros($numero) {
	if ($numero == 0)
		return '';
	else
		return $numero;
}

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






$title = _('Reporte de Inventarios E/S por Fecha');

if(isset($_POST['PrintEXCEL'])){
	
}else{
	include('includes/header.inc');
}


if (!isset($_POST['PrintEXCEL'])) {
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' ' . $title.'<br>';

	
/*if $FromCriteria is not set then show a form to allow input	*/

      echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'><table>";
	
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
	       
	echo '<tr><td><b>** PRODUCTOS</b></td>
		<td>';
	echo '</td></tr>';
	
	/************************************/
	/* SELECCION DEL GRUPO DE PRODUCTOS */
	echo '<tr><td>' . _('X Grupo') . ':' . "</td>
		<td><select tabindex='4' name='xGrupo'>";

	$sql = 'SELECT Prodgroupid, description FROM ProdGroup';
	$result=DB_query($sql,$db);
		
	echo "<option selected value='0'>Todos los grupos...</option>";
	
	while ($myrow=DB_fetch_array($result)){
	      if ($myrow['Prodgroupid'] == $_POST['xGrupo']){
			echo "<option selected value='" . $myrow["Prodgroupid"] . "'>" . $myrow['description'];
	      } else {
		      echo "<option value='" . $myrow['Prodgroupid'] . "'>" . $myrow['description'];
	      }
	}
	echo '</select></td></tr>';
	/************************************/
	
	/************************************/
	/* SELECCION DEL LINEA DE PRODUCTOS */
	echo '<tr><td>' . _('X Linea') . ':' . "</td>
		<td><select tabindex='4' name='xLinea'>";

	$sql = 'SELECT Prodlineid, Description FROM ProdLine';
	$result=DB_query($sql,$db);
	
	echo "<option selected value='0'>Todas las lineas...</option>";
	
	while ($myrow=DB_fetch_array($result)){
	      if ($myrow['Prodlineid'] == $_POST['xLinea']){
			echo "<option selected value='" . $myrow["Prodlineid"] . "'>" . $myrow['Description'];
	      } else {
		      echo "<option value='" . $myrow['Prodlineid'] . "'>" . $myrow['Description'];
	      }
	}
	echo '</select></td></tr>';
	/************************************/
	
	/************************************/
	/* SELECCION DEL CATEGORIA DE PRODUCTOS */
	echo '<tr><td>' . _('X Tipo') . ':' . "</td>
		<td><select tabindex='4' name='xCategoria'>";

	#$sql = 'SELECT categoryid, categorydescription FROM stockcategory';
	$sql='SELECT sto.categoryid, categorydescription FROM stockcategory sto, sec_stockcategory sec WHERE sto.categoryid=sec.categoryid AND userid="'.$_SESSION['UserID'].'" ORDER BY categorydescription';
	
	$result=DB_query($sql,$db);
	
	echo "<option selected value='0'>Todas las categorias...</option>";

	while ($myrow=DB_fetch_array($result)){
	      if ($myrow['categoryid'] == $_POST['xCategoria']){
			echo "<option selected value='" . $myrow["categoryid"] . "'>" . $myrow['categorydescription'];
	      } else {
		      echo "<option value='" . $myrow['categoryid'] . "'>" . $myrow['categorydescription'];
	      }
	}
	echo '</select></td></tr>';
	/************************************/
	
	echo '<tr><td><br><b>** REGIONES</b></td>
		<td>';
	echo '</td></tr>';


	    //Select the razon social
	echo '<tr><td>'._('Seleccione Una Razon Social:').'<td><select name="legalid">';
		echo "<option selected value='0'>Todas las Razones";
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

	/************************************/
	/* SELECCION DE REGION              */
	echo '<tr><td>' . _('X Region') . ':' . "</td>
		<td><select tabindex='4' name='xRegion'>";

	$sql = "SELECT regioncode, CONCAT(regioncode,' - ',name) as name FROM regions";
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
	/* SELECCION DE LA UNIDAD DE NEGOCIO DE PRODUCTOS */
	echo "<tr><td>" . _('X Unidad de Negocio') . ":</td><td>";
	echo "<select name='unidadnegocio'>";
	$SQL = "SELECT  t.tagref, CONCAT(t.tagref,' - ',t.tagdescription) as tagdescription ";//areas.areacode, areas.areadescription";
		$SQL = $SQL .	" FROM sec_unegsxuser u,tags t join areas ON t.areacode = areas.areacode ";
		$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
		$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' 
				ORDER BY areas.areacode";

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
	/* SELECCION DE DEPARTAMENTO  	          */
	echo '<tr><td>' . _('X Departamento') . ':' . "</td>
		<td><select tabindex='4' name='xDepto'>";

	$sql = "SELECT u_department, CONCAT(u_department,' - ',department) as name FROM departments";
	$result=DB_query($sql,$db);
	
	echo "<option selected value='0'>Todos los departamentos...</option>";

	while ($myrow=DB_fetch_array($result)){
	      if ($myrow['u_department'] == $_POST['xDepto']){
			echo "<option selected value='" . $myrow["u_department"] . "'>" . $myrow['name'];
	      } else {
		      echo "<option value='" . $myrow['u_department'] . "'>" . $myrow['name'];
	      }
	}
	echo '</select></td></tr>';
	/************************************/
      /************************************/
      /* SELECCION DEL ALMACEN DE PRODUCTOS */
      echo "<tr><td>" . _('X Almacen') . ":</td><td>";
	echo "<select name='almacen'>";
	$SQL = "SELECT  l.loccode, l.locationname  FROM  sec_loccxusser s, locations l WHERE s.userid = '" . $_SESSION['UserID'] . "'
	    and s.loccode = l.loccode";

	$ErrMsg = _('No transactions were returned by the SQL because');
	$TransResult = DB_query($SQL,$db,$ErrMsg);
	
	echo "<option selected value='0'>Todas a las que tengo accceso...</option>";
	
	while ($myrow=DB_fetch_array($TransResult)) {
		if ($myrow['loccode'] == $_POST['almacen']){
			echo "<option selected value='" . $myrow['loccode'] . "'>" . $myrow['locationname'] . "</option>";	
		}else{
			echo "<option value='" . $myrow['loccode'] . "'>" . $myrow['locationname'] . "</option>";
		}
	}
	 
	echo "</select>";
	echo "</td></tr>";
	
	/*****************************/
	
	echo '<tr><td><br><b>** DETALLE DEL REPORTE</b></td>
		<td>';
	echo '</td></tr>';
	
	echo '<tr><td>' . _('A que nivel') . ':' . "</td>
		<td><select tabindex='5' name='DetailedReport'>";
	/*
	if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'No')
		echo "<option selected value='No'>" . _('X Region');
	else
		echo "<option value='No'>" . _('X Region');
	
	if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Yes')
		echo "<option selected value='Yes'>" . _('X Sucursal');
	else
		echo "<option value='Yes'>" . _('X Sucursal');
	*/
	if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Prod')
		echo "<option selected value='Prod'>" . _('X Clave Producto');
	else
		echo "<option value='Prod'>" . _('X Clave Producto');
	echo '</select></td></tr>';
	
	echo "<tr><td>";
	echo "Solo Productos Inv Final:";
	echo "</td><td>";
	if ($_POST['coninvfinal'] != ""){
		echo "<input type='checkbox' name='coninvfinal' value='1' checked>";
	}else{
		echo "<input type='checkbox' name='coninvfinal' value='1'>";
	}
	echo "</tr>";
	
	echo "<tr><td>";
	echo "Solo Productos Con Optimo Definido:";
	echo "</td><td>";
	if ($_POST['conOptimo'] != ""){
		echo "<input type='checkbox' name='conOptimo' value='1' checked>";
	}else{
		echo "<input type='checkbox' name='conOptimo' value='1'>";
	}
	echo "</tr>";
	
	
	echo "<tr><td>";
	echo "Detalle de Entradas y Salidas:";
	echo "</td><td>";
	if ($_POST['conDetalleES'] != ""){
		echo "<input type='checkbox' name='conDetalleES' value='1' checked>";
	}else{
		echo "<input type='checkbox' name='conDetalleES' value='1'>";
	}
	echo "</tr>";
      
       /************************************/

	echo '</table>
		<br><div class="centre"><input tabindex="6" type=submit name="ReportePantalla" value="' . _('Genera Reporte en Pantalla') . '"></div>';
	echo '<br><div class="centre"><input tabindex="7" type=hidden name="PrintPDF" value="' . _('Imprime Archivo PDF') . '"></div>';
	echo '<br><div class="centre"><input tabindex="7" type=submit name="PrintEXCEL" value="' . _('Exportar a Excel') . '"></div>';
	
}

if (isset($_POST['ReportePantalla']) or isset($_POST['PrintEXCEL'])){
	
	if (isset($_POST['PrintEXCEL'])) {
	
		header("Content-type: application/ms-excel");
		# replace excelfile.xls with whatever you want the filename to default to
		header("Content-Disposition: attachment; filename=ReportedeInventariosE/S.xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	
		echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
		echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
		echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
      }
			
	if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Prod') {
			
		$SQL = "select  v.stockid, 
			m.description as producto,
			c.categorydescription as categoria,
			p.description as linea,
			(sum(v.qty*v.standardcost)/ sum(v.qty)) as cost,
			
			sum(locstock.reorderlevel) as reorderlevel,
			sum(case when v.trandate < '" . $fechaini . "'  then v.qty else 0 end) as invinicial,
			sum(case when v.type in (11,25,26,300)  and v.trandate >= '" . $fechaini . "' then v.qty else 0 end) as entradas,
			sum(case when v.type in (16,17,41)  and v.trandate >= '" . $fechaini . "' then v.qty else 0 end) as entradasINOUT,
			sum(case when v.type in (10,28,33,110,111)  and v.trandate >= '" . $fechaini . "' then v.qty else 0 end) as salidas,
			
			sum(case when v.type in (11,25,26,300)  and v.trandate >= '" . $fechaini . "' then v.qty*v.standardcost else 0 end) as entradasvalor,
			sum(case when v.type in (16,17,41)  and v.trandate >= '" . $fechaini . "' then v.qty*v.standardcost else 0 end) as entradasINOUTvalor,
			sum(case when v.type in (10,28,33,110,111)  and v.trandate >= '" . $fechaini . "' then v.qty*v.standardcost else 0 end) as salidasvalor,
			
			sum(case when v.type in (10,110,111) and v.trandate >= '" . $fechaini . "' then v.qty else 0 end) as ventas,
			sum(case when v.type in (10,110,111) and v.trandate >= '" . $fechaini . "' then v.qty*v.standardcost else 0 end) as ventasvalor,
			sum(case when v.type = 33  and v.trandate >= '" . $fechaini . "' then v.qty else 0 end) as ncprov,
			sum(case when v.type = 33  and v.trandate >= '" . $fechaini . "' then v.qty*v.standardcost else 0 end) as ncprovvalor,
			sum(case when v.type = 11  and v.trandate >= '" . $fechaini . "' then v.qty else 0 end) as notascredito,
			sum(case when v.type = 11  and v.trandate >= '" . $fechaini . "' then v.qty*v.standardcost else 0 end) as notascreditovalor,
			sum(case when v.type in (16,28)  and v.trandate >= '" . $fechaini . "' then v.qty else 0 end) as traspasos,
			sum(case when v.type in (16,28)  and v.trandate >= '" . $fechaini . "' then v.qty*v.standardcost else 0 end) as traspasosvalor,
			sum(case when v.type in (17,41)  and v.trandate >= '" . $fechaini . "' then v.qty else 0 end) as ajustes,
			sum(case when v.type in (17,41)  and v.trandate >= '" . $fechaini . "' then v.qty*v.standardcost else 0 end) as ajustesvalor,
			sum(case when v.type in (25,26)  and v.trandate >= '" . $fechaini . "' then v.qty else 0 end) as compras,
			sum(case when v.type in (25,26)  and v.trandate >= '" . $fechaini . "' then v.qty*v.standardcost else 0 end) as comprasvalor,
			
			sum(case when v.type in (300)  and v.trandate >= '" . $fechaini . "' then v.qty else 0 end) as cargamanual,
			sum(case when v.type in (300)  and v.trandate >= '" . $fechaini . "' then v.qty*v.standardcost else 0 end) as cargamanualvalor,
			
			sum(case when v.trandate < '" . $fechaini . "'  then v.qty*v.standardcost else 0 end) as invinicialvalor
			
			from   stockmoves v
			JOIN locstock ON v.loccode = locstock.loccode AND v.stockid = locstock.stockid
			JOIN locations l ON v.loccode = l.loccode
			JOIN tags t ON l.tagref = t.tagref
			JOIN departments ON t.u_department=departments.u_department
			JOIN legalbusinessunit b on t.legalid = b.legalid
			
			JOIN sec_unegsxuser s ON s.tagref = t.tagref AND s.userid = 'ADMIN'
			JOIN areas  a ON t.areacode = a.areacode
			JOIN regions r ON a.regioncode = r.regioncode, 
			stockmaster m 
			JOIN stockcategory c ON m.categoryid = c.categoryid
			JOIN ProdLine p ON c.ProdLineId = p.Prodlineid
			JOIN ProdGroup g ON p.Prodgroupid = g.Prodgroupid
			
			where v.trandate <= '" . $fechafin . "'
			and v.stockid = m.stockid and m.mbflag in ('B','M')
			and (g.Prodgroupid = '".$_POST['xGrupo']."' or '".$_POST['xGrupo']."'='0')
			and (p.ProdLineId = '".$_POST['xLinea']."' or '".$_POST['xLinea']."'='0')
			and (m.categoryid = '".$_POST['xCategoria']."' or '" . $_POST['xCategoria']. "'='0')
			and (a.areacode = '".$_POST['xArea']."' or '".$_POST['xArea']."'='0')
				and (a.regioncode = '".$_POST['xRegion']."' or '".$_POST['xRegion']."'='0')
				and (l.loccode = '".$_POST['almacen']."' or '".$_POST['almacen']."'='0')
				and (l.tagref = '".$_POST['unidadnegocio']."' or '".$_POST['unidadnegocio']."'='0')
				and (t.legalid = '".$_POST['legalid']."' or '".$_POST['legalid']."'='0')
				and (departments.u_department = '".$_POST['xDepto']."' or '".$_POST['xDepto']."'='0')";
			
			//JOIN stockcostsxlegal x ON b.legalid = x.legalid AND x.stockid = v.stockid
			
			if ($_POST['conOptimo']<>''){
				$SQL = $SQL . " and locstock.reorderlevel > 0";	
			}
				
			$SQL = $SQL . " group by v.stockid, m.description, c.categorydescription, p.description";
			
		
		if ($_POST['coninvfinal']<>''){ 
			$SQL = $SQL . " having (invinicial  + (entradas - salidas)) <> 0";	
		} else { 
			//$SQL = $SQL . " having  (abs(invinicial) + abs(entradas) + abs(salidas)) > 0";	
		}
		
		$SQL = $SQL . " order by p.description, c.categorydescription, m.description";
	}
	
	//echo $SQL; 
	
	$ReportResult = DB_query($SQL,$db,'','',False,False); /*dont trap errors */

	if (DB_error_no($db) !=0) {
	  $title = _('Estado General de Inventarios') . ' - ' . _('Reporte de Problema') ;
	  //include("includes/header.inc");
	  prnMsg(_('Los detalles del inventarios no se pudieron recuperar porque el SQL fallo') .  ' ' . DB_error_msg($db),'error');

	   if ($debug==1){
		echo "<br>$SQL";
	   }
	   exit;
	}
	
	if ($_POST['conDetalleES']<>'') {
		$encabezadogeneral = '
			<tr><th style="font-size:8pt;"><b>' . _('#') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('Codigo') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('Producto') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('Costo') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('Optimo') . '</b></th>
			<th colspan="2" style="font-size:8pt;"><b>' . _('Inv<br>Inicial') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('Carga<br>Manual') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('Compras') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('N.Credito') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('N.Credito<br>Prov') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('Traspasos') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('Ajustes') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('Ventas') . '</b></th>
			<th colspan="2" style="font-size:8pt;"><b>' . _('Inv Final') . '</b></th>
			</tr>';
		
	} else {
		$encabezadogeneral = '
			<tr><th style="font-size:8pt;"><b>' . _('#') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('Codigo') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('Producto') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('Costo') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('Optimo') . '</b></th>
			<th colspan="2" style="font-size:8pt;"><b>' . _('Inv<br>Inicial') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('Entradas') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('Valor Entradas') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('Ajustes<br>Traspasos') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('Valor Ajustes<br>Traspasos') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('Salidas') . '</b></th>
			<th style="font-size:8pt;"><b>' . _('Valor Salidas') . '</b></th>
			<th colspan="2" style="font-size:8pt;"><b>' . _('Inv Final') . '</b></th>
			</tr>';
	}	
 	
	

	$i = 0;
	echo "<table border='1' cellpadding='2' cellspacing='0' width='765'>";
	
	
	$categoriaant = ""; 
	$linea = "";
	
	$tinvinicial = 0;
	$tcostoinicial = 0;
	$tentradas = 0;
	$tentradasINOUT = 0;
	$tsalidas = 0;
	
	
	$tentradasval = 0;
	$tentradasINOUTval = 0;
	$tsalidasval = 0;
	
	$tinvfinal = 0;
	$tcostofinal = 0;
	
	$tventas = 0;
	$tnotascredito = 0;
	$tnotascreditoPROV = 0;
	$ttraspasos = 0;
	$tajustes = 0;
	$tcompras = 0;
	$tcargamanual = 0;
	$tventasval = 0;
	$tnotascreditoval = 0;
	$tnotascreditoPROVval = 0;
	$ttraspasosval = 0;
	$tajustesval = 0;
	$tcomprasval = 0;
	$tcargamanualval = 0;
	
	$tmontoVentas = 0;
	$tmontocompras = 0;
	$tmontoNC = 0;
	
	$tlinvinicial = 0;
	$tlcostoinicial = 0;
	$tlentradas = 0;
	$tlentradasINOUT = 0;
	$tlsalidas = 0;
	$tlinvfinal = 0;
	$tlcostofinal = 0;
	$tlventas = 0;
	$tlnotascredito = 0;
	$tlnotascreditoPROV = 0;
	$tltraspasos = 0;
	$tlajustes = 0;
	$tlcompras = 0;
	$tlcargamanual = 0;
	$tlventasval = 0;
	$tlnotascreditoval = 0;
	$tlnotascreditoPROVval = 0;
	$tltraspasosval = 0;
	$tlajustesval = 0;
	$tlcomprasval = 0;
	$tlcargamanualval = 0;
	
	$tcinvinicial = 0;
	$tccostoinicial = 0;
	$tcentradas = 0;
	$tcentradasINOUT = 0;
	$tcsalidas = 0;
	$tcinvfinal = 0;
	$tccostofinal = 0;
	
	$tcventas = 0;
	$tcnotascredito = 0;
	$tcnotascreditoPROV = 0;
	$tctraspasos = 0;
	$tcajustes = 0;
	$tccompras = 0;
	$tccargamanual = 0;
	$tcventasval = 0;
	$tcnotascreditoval = 0;
	$tcnotascreditoPROVval = 0;
	$tctraspasosval = 0;
	$tcajustesval = 0;
	$tccomprasval = 0;
	$tccargamanualval = 0;
	
	$indice = 0;
	$idxheader = 0;
	while ($InvAnalysis = DB_fetch_array($ReportResult,$db)){
		$indice++;
		$idxheader++;
		
		$stockid = $InvAnalysis['stockid'];
		$producto = $InvAnalysis['producto'];
		$costoProm = $InvAnalysis['costo'];
		$optimo = $InvAnalysis['reorderlevel'];
		$categoria = $InvAnalysis['categoria'];
		$linea = $InvAnalysis['linea'];
		$invinicial = $InvAnalysis['invinicial'];
		$entradas = $InvAnalysis['entradas'];
		$entradasINOUT = $InvAnalysis['entradasINOUT'];
		$salidas = $InvAnalysis['salidas'];
		$invfinal = $invinicial +  ($entradas + $entradasINOUT + $salidas);
		$costo = $InvAnalysis['cost'];
		
		$invinicialval = $InvAnalysis['invinicialvalor'];
		$entradasval = $InvAnalysis['entradasvalor'];
		$entradasINOUTval = $InvAnalysis['entradasINOUTvalor'];
		$salidasval = $InvAnalysis['salidasvalor'];
		$invfinalval = $invinicialval +  ($entradasval + $entradasINOUTval + $salidasval);
	
		
		$ventas = $InvAnalysis['ventas'];
		$notascredito = $InvAnalysis['notascredito'];
		$notascreditoPROV = $InvAnalysis['ncprov'];
		$traspasos = $InvAnalysis['traspasos'];
		$ajustes = $InvAnalysis['ajustes'];
		$compras = $InvAnalysis['compras'];
		$cargamanual = $InvAnalysis['cargamanual'];
		
		$ventasval = $InvAnalysis['ventasvalor'];
		$notascreditoval = $InvAnalysis['notascreditovalor'];
		$notascreditoPROVval = $InvAnalysis['ncprovvalor'];
		$traspasosval = $InvAnalysis['traspasosvalor'];
		$ajustesval = $InvAnalysis['ajustesvalor'];
		$comprasval = $InvAnalysis['comprasvalor'];
		$cargamanualval = $InvAnalysis['cargamanualvalor'];
		
		if ($invfinal==0) {
			//$costo = 0;
		}
		
		if (($linea <> $lineaant) or ($categoria <> $categoriaant)){
			
			if ($categoria <> $categoriaant){
				
				if ($indice != 1){
					
					if ($_POST['conDetalleES']<>'') {
						echo "<tr bgcolor='#DF0101'>";
							echo "<td style='font-size:8pt; font-weight:bold; color:white;' colspan='5' >******TOTAL CATEGORIA " . $categoriaant . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcinvinicial . "</td>";
							echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF0101'>
								<td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td><td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tccostoinicial,2) . "</td></tr></table></td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tccargamanual . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tccompras . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcnotascredito . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcnotascreditoPROV . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tctraspasos . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcajustes . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcventas . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcinvfinal . "</td>";
							echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF0101'>
							<td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td><td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tccostofinal,2) . "</td></tr></table></td>";
						echo "</tr>";
					} else {
						echo "<tr bgcolor='#DF0101'>";
							echo "<td style='font-size:8pt; font-weight:bold; color:white;' colspan='5' >******TOTAL CATEGORIA " . $categoriaant . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcinvinicial . "</td>";
							echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF0101'>
								<td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td><td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tccostoinicial,2) . "</td></tr></table></td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcentradas . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'></td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcentradasINOUT . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'></td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcsalidas . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'></td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcinvfinal . "</td>";
							echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF0101'>
							<td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td><td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tccostofinal,2) . "</td></tr></table></td>";
						echo "</tr>";
					}
					
					$tcinvinicial = 0;
					$tccostoinicial = 0;
					$tcentradas = 0;
					$tcentradasINOUT = 0;
					$tcsalidas = 0;
					$tcinvfinal = 0;
					$tccostofinal = 0;
					
					$tcventas = 0;
					$tcnotascredito = 0;
					$tcnotascreditoPROV = 0;
					$tctraspasos = 0;
					$tcajustes = 0;
					$tccompras = 0;
					$tccargamanual = 0;
					
					$tcventasval = 0;
					$tcnotascreditoval = 0;
					$tcnotascreditoPROVval = 0;
					$tctraspasosval = 0;
					$tcajustesval = 0;
					$tccomprasval = 0;
					$tccargamanualval = 0;
					
				}
			}
			
			if ($linea <> $lineaant){
				if ($indice != 1){
					if ($_POST['conDetalleES']<>'') {
						echo "<tr bgcolor='#DF7401'>";
							echo "<td style='font-size:8pt; font-weight:bold; color:white;' colspan='5'>***TOTAL LINEA " . $lineaant . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlinvinicial . "</td>";
							echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF7401'><td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td><td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tlcostoinicial,2) . "</td></tr></table></td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlcargamanual . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlcompras . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlnotascredito . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlnotascreditoPROV . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tltraspasos . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlajustes . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlventas . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlinvfinal . "</td>";
							echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF7401'>
							<td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td><td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tlcostofinal,2) . "</td></tr></table></td>";
						echo "</tr>";
						echo "<tr><td colspan='9'>&nbsp;</td></tr>";
					} else {
						echo "<tr bgcolor='#DF7401'>";
							echo "<td style='font-size:8pt; font-weight:bold; color:white;' colspan='5'>***TOTAL LINEA " . $lineaant . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlinvinicial . "</td>";
							echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF7401'><td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td><td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tlcostoinicial,2) . "</td></tr></table></td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlentradas . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'></td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlentradasINOUT . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'></td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlsalidas . "</td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'></td>";
							echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlinvfinal . "</td>";
							echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF7401'>
							<td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td><td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tlcostofinal,2) . "</td></tr></table></td>";
						echo "</tr>";
						echo "<tr><td colspan='9'>&nbsp;</td></tr>";
					}
					
					$tlinvinicial = 0;
					$tlcostoinicial = 0;
					$tlentradas = 0;
					$tlentradasINOUT = 0;
					$tlsalidas = 0;
					$tlinvfinal = 0;
					$tlcostofinal = 0;
					
					$tlventas = 0;
					$tlnotascredito = 0;
					$tlnotascreditoPROV = 0;
					$tltraspasos = 0;
					$tlajustes = 0;
					$tlcompras = 0;
					$tlcargamanual = 0;
					
					$tlventasval = 0;
					$tlnotascreditoval = 0;
					$tlnotascreditoPROVval = 0;
					$tltraspasosval = 0;
					$tlajustesval = 0;
					$tlcomprasval = 0;
					$tlcargamanualval = 0;
				}	
			}
			
			if ($linea <> $lineaant){
				echo "<tr><td colspan='11'>";
				echo "***" . $linea;
				echo "</td></tr>";
				$lineaant = $linea;
			}
			
			if ($categoria <> $categoriaant){
				echo "<tr><td colspan='11'>";
				echo "****************" . $categoria;
				echo "</td></tr>";
				$categoriaant = $categoria;	
				echo $encabezadogeneral;
			}
			
			
		}
		
		if ($idxheader == 30) {
			echo $encabezadogeneral;
			$idxheader = 0;
		}
		
		
		if ($_POST['conDetalleES']<>'') {
			
			if (abs($cargamanual)+abs($compras)+abs($notascredito)+abs($notascreditoPROV)+abs($traspasos)+abs($ajustes)+abs($ventas) > 0) {
				echo "<tr style='background-color:yellow'>";
			} else
				echo "<tr>";
				
			echo "<td style='text-align:right;'>".$indice."</td>";
			echo "<td style='font-size:9pt; font-weight:normal;'>" . $stockid . "</td>";
			echo "<td style='font-size:7pt; font-weight:normal;'>" . $producto . "</td>";
			echo "<td style='font-size:7pt; font-weight:normal;text-align:right; '><b>" . number_format($costo,2) . "</b></td>";
			echo "<td style='font-size:7pt; font-weight:normal;text-align:right; '><b>" . number_format($optimo,0) . "</b></td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:normal;'>&nbsp;&nbsp;&nbsp;&nbsp;" . $invinicial . "</td>";
			echo "<td><table align='left'><tr><td style='text-align:left; font-size:9pt; font-size:9pt; font-weight:normal;'>$</td>
			<td width='100' style='text-align:right; font-size:9pt; font-size:9pt; font-weight:normal;'>" . number_format($invinicialval,2) . "</td></tr></table></td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:normal;'>" . LocalSinCeros($cargamanual) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:normal;'>" . LocalSinCeros($compras) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:normal;'>" . LocalSinCeros($notascredito) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:normal;'>" . LocalSinCeros($notascreditoPROV) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:normal;'>" . LocalSinCeros($traspasos) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:normal;'>" . LocalSinCeros($ajustes) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:normal;'>" . LocalSinCeros($ventas) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:normal;'>&nbsp;&nbsp;&nbsp;&nbsp;" . $invfinal . "</td>";
			echo "<td><table align='left' border='0'><tr><td style='text-align:left; font-size:9pt; font-weight:normal;'>$</td>
			<td width='100' style='text-align:right; font-size:9pt; font-size:9pt; font-weight:normal;'>" . number_format($invfinalval,2) . "</td></tr></table></td>";
			echo "</tr>";
		} else {
			if (($entradas+$entradasINOUT+$salidas) > 0) {
				echo "<tr style='background-color:yellow'>";
			} else
				echo "<tr>";
				
			echo "<td style='text-align:right;'>".$indice."</td>";
			echo "<td style='font-size:9pt; font-weight:normal;'>" . $stockid . "</td>";
			echo "<td style='font-size:7pt; font-weight:normal;'>" . $producto . "</td>";
			echo "<td style='font-size:7pt; font-weight:normal;text-align:right; '><b>" . number_format($costo,2) . "</b></td>";
			echo "<td style='font-size:7pt; font-weight:normal;text-align:right; '><b>" . number_format($optimo,0) . "</b></td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:normal;'>&nbsp;&nbsp;&nbsp;&nbsp;" . $invinicial . "</td>";
			echo "<td><table align='left'><tr><td style='text-align:left; font-size:9pt; font-size:9pt; font-weight:normal;'>$</td>
			<td width='100' style='text-align:right; font-size:9pt; font-size:9pt; font-weight:normal;'>" . number_format($invinicialval,2) . "</td></tr></table></td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:normal;'>" . LocalSinCeros($entradas) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:normal;'>" . number_format($entradasval,2) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:normal;'>" . LocalSinCeros($entradasINOUT) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:normal;'>" . number_format($entradasINOUTval,2) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:normal;'>" . LocalSinCeros($salidas) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:normal;'>" . number_format($salidasval,2) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:normal;'>&nbsp;&nbsp;&nbsp;&nbsp;" . $invfinal . "</td>";
			echo "<td><table align='left' border='0'><tr><td style='text-align:left; font-size:9pt; font-weight:normal;'>$</td>
			<td width='100' style='text-align:right; font-size:9pt; font-size:9pt; font-weight:normal;'>" . number_format($invfinalval,2) . "</td></tr></table></td>";
			echo "</tr>";
		}
		
		
		
		$tcinvinicial = $tcinvinicial + $invinicial;
		$tccostoinicial = $tccostoinicial + ($invinicialval);
		$tcentradas = $tcentradas + $entradas;
		$tcentradasINOUT = $tcentradasINOUT + $entradasINOUT;
		$tcsalidas = $tcsalidas + $salidas;
		$tcinvfinal = $tcinvfinal + $invfinal;
		$tccostofinal = $tccostofinal + ($invfinalval);
		
		$tcventas = $tcventas + $ventas;
		$tcnotascredito = $tcnotascredito + $notascredito;
		$tcnotascreditoPROV = $tcnotascreditoPROV + $notascreditoPROV;
		$tctraspasos = $tctraspasos + $traspasos;
		$tcajustes = $tcajustes + $ajustes;
		$tccompras = $tccompras + $compras;
		$tccargamanual = $tccargamanual + $cargamanual;
		
		$tcventasval = $tcventasval + $ventasval;
		$tcnotascreditoval = $tcnotascreditoval + $notascreditoval;
		$tcnotascreditoPROVval = $tcnotascreditoPROVval + $notascreditoPROVval;
		$tctraspasosval = $tctraspasosval + $traspasosval;
		$tcajustesval = $tcajustesval + $ajustesval;
		$tccomprasval = $tccomprasval + $comprasval;
		$tccargamanualval = $tccargamanualval + $cargamanualval;
		
		$tlinvinicial = $tlinvinicial + $invinicial;
		$tlcostoinicial = $tlcostoinicial + ($invinicialval);
		$tlentradas = $tlentradas + $entradas;
		$tlentradasINOUT = $tlentradasINOUT + $entradasINOUT;
		$tlsalidas = $tlsalidas + $salidas;
		$tlinvfinal = $tlinvfinal + $invfinal;
		$tlcostofinal = $tlcostofinal + ($invfinalval);
		
		$tlventas = $tlventas + $ventas;
		$tlnotascredito = $tlnotascredito + $notascredito;
		$tlnotascreditoPROV = $tlnotascreditoPROV + $notascreditoPROV;
		$tltraspasos = $tltraspasos + $traspasos;
		$tlajustes = $tlajustes + $ajustes;
		$tlcompras = $tlcompras + $compras;
		$tlcargamanual = $tlcargamanual + $cargamanual;
		
		$tlventasval = $tlventasval + $ventasval;
		$tlnotascreditoval = $tlnotascreditoval + $notascreditoval;
		$tlnotascreditoPROVval = $tlnotascreditoPROVval + $notascreditoPROVval;
		$tltraspasosval = $tltraspasosval + $traspasosval;
		$tlajustesval = $tlajustesval + $ajustesval;
		$tlcomprasval = $tlcomprasval + $comprasval;
		$tlcargamanualval = $tlcargamanualval + $cargamanualval;
		
		$tinvinicial = $tinvinicial + $invinicial;
		$tcostoinicial = $tcostoinicial + ($invinicialval);
		$tentradas = $tentradas + $entradas;
		$tentradasINOUT = $tentradasINOUT + $entradasINOUT;
		$tsalidas = $tsalidas + $salidas;
		$tentradasval = $tentradasval + $entradasval;
		$tentradasINOUTval = $tentradasINOUTval + $entradasINOUTval;
		$tsalidasval = $tsalidasval + $salidasval;
		
		
		
		$tinvfinal = $tinvfinal + $invfinal;
		$tcostofinal = $tcostofinal + ($invfinalval);
		
		$tventas = $tventas + $ventas;
		$tnotascredito = $tnotascredito + $notascredito;
		$tnotascreditoPROV = $tnotascreditoPROV + $notascreditoPROV;
		$ttraspasos = $ttraspasos + $traspasos;
		$tajustes = $tajustes + $ajustes;
		$tcompras = $tcompras + $compras;
		$tcargamanual = $tcargamanual + $cargamanual;
		
		$tventasval = $tventasval + $ventasval;
		$tnotascreditoval = $tnotascreditoval + $notascreditoval;
		$tnotascreditoPROVval = $tnotascreditoPROVval + $notascreditoPROVval;
		$ttraspasosval = $ttraspasosval + $traspasosval;
		$tajustesval = $tajustesval + $ajustesval;
		$tcomprasval = $tcomprasval + $comprasval;
		$tcargamanualval = $tcargamanualval + $cargamanualval;
		
		$tmontoVentas = $tmontoVentas + ($ventas*$costo);
		$tmontoNC = $tmontoNC + ($notascredito*$costo);
		$tmontocompras = $tmontocompras + ($compras*$costo);
		
	} /*end while loop */
	   
	echo $encabezadogeneral;
	
	if ($_POST['conDetalleES']<>'') {
		echo "<tr bgcolor='#DF0101'>";
			echo "<td style='font-size:8pt; font-weight:bold; color:white;' colspan='5' >******TOTAL CATEGORIA " . $categoriaant . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcinvinicial . "</td>";
			echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF0101'><td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td>
			<td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tccostoinicial,2) . "</td></tr></table></td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tccargamanual . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tccompras . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcnotascredito . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcnotascreditoPROV . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tctraspasos . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcajustes . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcventas . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcinvfinal . "</td>";
			echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF0101'><td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td>
			<td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tccostofinal,2) . "</td></tr></table></td>";
		echo "</tr>";
		
		echo "<tr bgcolor='#DF7401'>";
			echo "<td style='font-size:8pt; font-weight:bold; color:white;' colspan='5'>***TOTAL LINEA " . $lineaant . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlinvinicial . "</td>";
			echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF7401'><td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td><td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tlcostoinicial,2) . "</td></tr></table></td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlcargamanual . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlcompras . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlnotascredito . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlnotascreditoPROV . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tltraspasos . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlajustes . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlventas . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlinvfinal . "</td>";
			echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF7401'><td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td><td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tlcostofinal,2) . "</td></tr></table></td>";
		echo "</tr>";
		
		echo "<tr bgcolor='#00AD1A'>";
			echo "<td style='font-size:8pt; font-weight:bold; color:white;' colspan='5'>***TOTAL GENERAL </td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tinvinicial . "</td>";
			echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF7401'>
			<td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td><td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tcostoinicial,2) . "</td></tr></table></td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcargamanual . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcompras . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tnotascredito . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tnotascreditoPROV . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $ttraspasos . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tajustes . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tventas . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tinvfinal . "</td>";
			echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF7401'><td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td><td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tcostofinal,2) . "</td></tr></table></td>";
		echo "</tr>";
		
		echo "<tr bgcolor='#00AD1A'>";
			echo "<td style='font-size:8pt; font-weight:bold; color:white;' colspan='5'>***TOTAL MONTOS </td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'></td>";
			echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF7401'>
			<td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td><td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'></td></tr></table></td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tcargamanualval,2) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tcomprasval,2) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tnotascreditoval,2) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tnotascreditoPROVval,2) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($ttraspasosval,2) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tajustesval,2) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tventasval,2) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'></td>";
			echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF7401'>
			<td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td><td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'></td></tr></table></td>";
		echo "</tr>";
		
	} else {
		echo "<tr bgcolor='#DF0101'>";
			echo "<td style='font-size:8pt; font-weight:bold; color:white;' colspan='5' >******TOTAL CATEGORIA " . $categoriaant . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcinvinicial . "</td>";
			echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF0101'><td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td><td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tccostoinicial,2) . "</td></tr></table></td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcentradas . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'></td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcentradasINOUT . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'></td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcsalidas . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'></td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tcinvfinal . "</td>";
			echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF0101'><td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td><td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tccostofinal,2) . "</td></tr></table></td>";
		echo "</tr>";
		
		echo "<tr bgcolor='#DF7401'>";
			echo "<td style='font-size:8pt; font-weight:bold; color:white;' colspan='5'>***TOTAL LINEA " . $lineaant . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlinvinicial . "</td>";
			echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF7401'><td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td><td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tlcostoinicial,2) . "</td></tr></table></td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlentradas . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'></td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlentradasINOUT . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'></td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlsalidas . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'></td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tlinvfinal . "</td>";
			echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF7401'><td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td><td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tlcostofinal,2) . "</td></tr></table></td>";
		echo "</tr>";
		
		echo "<tr bgcolor='#00AD1A'>";
			echo "<td style='font-size:8pt; font-weight:bold; color:white;' colspan='5'>***TOTAL GENERAL </td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tinvinicial . "</td>";
			echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF7401'><td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td><td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tcostoinicial,2) . "</td></tr></table></td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tentradas . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tentradasval,2) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tentradasINOUT . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tentradasINOUTval,2) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tsalidas . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tsalidasval,2) . "</td>";
			echo "<td style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . $tinvfinal . "</td>";
			echo "<td><table align='left' cellpadding=0 cellspacing=0 ><tr bgcolor='#DF7401'>
			<td style='text-align:left; font-size:9pt; font-weight:bold; color:white;'>$</td><td width='90' style='text-align:right; font-size:9pt; font-weight:bold; color:white;'>" . number_format($tcostofinal,2) . "</td></tr></table></td>";
		echo "</tr>";
	}
	

}elseif(isset($_POST['PrintPDF'])){
	

}else{ /*The option to print PDF was not hit */

} /*end of else not PrintPDF */
   
   if (isset($_POST['PrintEXCEL'])) {
		exit;
	}

include('includes/footer.inc');
?>
