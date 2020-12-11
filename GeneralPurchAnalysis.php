<?php
/* $Revision: 1.13.1 $ */
////
/*desarrollo- 27/MARZO/2011 - AGREGUE OPCION PARA QUE SOLO DESPLIEGUE LAS LINEAS NO COMPLETADAS */
ob_start("ob_gzhandler");

/*cambios
1.- se agrego el include('includes/SecurityFunctions.inc');
2.- se arreglo la variable $funcion*/
$PageSecurity = 2;
include('includes/session.inc');
 $funcion=870;
include('includes/SecurityFunctions.inc');
$title = _('Reporte General de Compras');

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

// echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' ' . $title.'<br>';

if (!isset($_POST["EnviaExcel"])){
	include('includes/header.inc');

	$debug = 1;
	// Tabla para el titulo de la pagina
	echo "<table align='center' border=0 style='background-color:#ffff;' width=100% nowrap>";
	echo '	<tr>
    		<td class="fecha_titulo">
    			<img src="images/reporte_35.png" width=30 alt="">' . $title . '<br>
    		</td>';
	echo '	</tr>
	  </table><br>';
	
	/*if $FromCriteria is not set then show a form to allow input	*/
	echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'>";
			echo '<fieldset class="cssfieldset" style="width:90%; margin:auto; color:#2a6c22; border:2px solid #c9ccc9">';
	echo '	<legend>' .('Criterio de Consulta'). '</legend>';
	echo "	<table border=0 align=center>";
	/* SELECCIONA EL RANGO DE FECHAS */
	echo '		<tr>';
	echo '			<td class="texto_lista">' . _('Desde:') . '<select Name="FromDia">';
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
					 
	echo'			</td>'; 
	echo '			<td colspan=2><select Name="FromMes">';
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
						      
	echo '			</td>
				    <td class="texto_lista">' . _('Hasta:') . '';
	echo'			<select Name="ToDia">';
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
	echo '			</td>';
	echo'			<td colspan=2>';
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
					 
	echo'			</td>';
	   echo '	</tr>';
	/* SELECCIONA EL RANGO DE FECHAS */
      //Select the razon social
      
	echo '		<tr>
					<td class="texto_lista" colspan="3">'._('Seleccione Una Raz&oacute;n Social:').'</td>
					<td colspan="3"><select name="legalid">';
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
	echo '</select>&nbsp;&nbsp;<input type="submit"  value=" -> ">
					</td>
				</tr>';
	
	//**************************************************************************************
	
	//**************************************************************************************
	// End select Razon Social
	/************************************/
	/* SELECCION DEL REGION */
	$where="";
	if ($_POST['legalid'])
		$where = "and tags.legalid = ".$_POST['legalid'];
	
	echo '		<tr>
					<td colspan=3 valign="top">
						<table align=center bgcolor=#eeeeee width=100%>';
	echo '					<tr>
								<td class="titulos_principales4" colspan=3>' .('Regiones').'</td>';
	echo '					</tr>';
	echo '					<tr>
								<td class="texto_lista">' . _('Regi&oacute;n') . ':' . "</td>
								<td colspan=2><select tabindex='4' name='xRegion'>";
	$sql = "SELECT regions.regioncode, CONCAT(regions.regioncode,' - ',regions.name) as name 
			FROM regions JOIN areas ON areas.regioncode = regions.regioncode
			JOIN tags ON tags.areacode = areas.areacode JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
		  WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
		  $where		
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
	echo '</select>&nbsp;&nbsp;<input type="submit"  value=" -> ">
								</td>
							</tr>';
	/************************************/
	/************************************/
	/* SELECCION DEL AREA */
	
	if ($_POST['xRegion'] != 0 and $_POST['xRegion'] != "")
		$where.=" and areas.regioncode = '".$_POST['xRegion']."'";
	
	echo '					<tr>
								<td class="texto_lista">' . _('Area') . ':' . "</td>
								<td><select tabindex='4' name='xArea'>";

	$sql = "SELECT areas.areacode, CONCAT(areas.areacode,' - ',areas.areadescription) as name
			FROM areas 
			JOIN tags ON tags.areacode = areas.areacode JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
		  WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
		  $where
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
	echo '</select>&nbsp;&nbsp;<input type="submit"  value=" -> ">
								</td>
							</tr>';
	/************************************/
	
	/************************************/
	/* SELECCION DEL DEPARTAMENTO       */
	echo '					<tr>
								<td class="texto_lista">' . _('Departamento') . ':' . "</td>
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
	echo '</select>&nbsp;&nbsp;<input type="submit"  value=" -> ">
								</td>
							</tr>';
	/************************************/
	
	
	/************************************/
	/* SELECCION DE LA UNIDAD DE NEGOCIO DE PRODUCTOS */
	
	if ($_POST['xDepartamento']!=0 and $_POST['xDepartamento']!=""){
		$where.=" and tags.u_department = '".$_POST['xDepartamento']."'";
	}
	
	echo "					<tr>
								<td class='texto_lista'>" . _('Unidad de Negocio') . ":</td>
								<td>";
	
	$SQL = "SELECT  tags.tagref, CONCAT(tags.tagref, ' - ', tags.tagdescription) as tagdescription";//areas.areacode, areas.areadescription";
		$SQL = $SQL .	" FROM sec_unegsxuser u,tags join areas ON tags.areacode = areas.areacode ";
		$SQL = $SQL .	" WHERE u.tagref = tags.tagref 
							$where ";
		$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' 
				ORDER BY tags.tagref, areas.areacode";
	//echo "<pre>$SQL";
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
	 
	echo "</select>&nbsp;&nbsp;<input type='submit'  value=' -> '>";
	echo "						</td>
							</tr>";
	/************************************/
	
	
	/************************************/
      /************************************/
      /* SELECCION DEL ALMACEN DE PRODUCTOS */
	if ($_POST['unidadnegocio']!=0 and $_POST['unidadnegocio']!="")
		$cond = "and l.tagref = '".$_POST['unidadnegocio']."'";
	
	
      echo "				<tr>
      							<td class='texto_lista'>" . _('Almacen') . ":</td>
      							<td>";
	echo "<select name='almacen'>";
	$SQL = "SELECT  l.loccode, l.locationname  FROM  sec_loccxusser s, locations l 
			WHERE s.userid = '" . $_SESSION['UserID'] . "'
	    	and s.loccode = l.loccode
		$cond
		";

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
	echo "						</td>
							</tr>
						</table>
					</td>";
	
	/************************************/
	echo '			<td colspan=3 valign="top">
						<table align=center bgcolor=#eeeeee width=100%>';
	echo '					<tr>';
	echo '						<td class="titulos_principales4" colspan="3">' .('Proveedores'). '</td>
							</tr>';
	
	/* SELECCION DEL TIPO DE PROVEEDOR */
	echo "					<tr>
								<td class='texto_lista' nowrap>" . _('Tipo de Proveedor') . ":</td>
								<td>";
	echo "<select name='tipocliente'>";
	$SQL = "SELECT  typeid, typename FROM supplierstype";

	$ErrMsg = _('No transactions were returned by the SQL because');
	$TransResult = DB_query($SQL,$db,$ErrMsg);
	
	echo "<option selected value='0'>Todos los tipos de proveedores...</option>";
	
	while ($myrow=DB_fetch_array($TransResult)) {
		if ($myrow['typeid'] == $_POST['tipocliente']){
			echo "<option selected value='" . $myrow['typeid'] . "'>" . $myrow['typename'] . "</option>";	
		}else{
			echo "<option value='" . $myrow['typeid'] . "'>" . $myrow['typename'] . "</option>";
		}
	}
	 
	echo "</select>";
	echo "<input type=submit name='searchSuppliers' VALUE='" . _('->') . "'>
								</td>
							</tr>";
	
	/************************************/
	/* SELECCION DE PROVEEDORES */
	
	echo "					<tr>
								<td class='texto_lista' nowrap>" . _('Nombre Proveedor') . ":</td>
								<td>";
	echo "<select name='proveedores[]' multiple='multiple'>";
	if(empty($_POST['tipocliente'])) {
		$SQL = "SELECT supplierid, suppname FROM suppliers";
	} else {
		$SQL = "SELECT supplierid, suppname FROM suppliers WHERE typeid = '" . $_POST['tipocliente'] . "'";
	}
	$ErrMsg = _('No transactions were returned by the SQL because');
	$TransResult = DB_query($SQL,$db,$ErrMsg);
	
	if(in_array('0', $_POST['proveedores'])) {
		echo "<option selected='selected' value='0'>Todos los proveedores...</option>";
	} else {
		echo "<option value='0'>Todos los proveedores...</option>";
	}
	
	while ($myrow=DB_fetch_array($TransResult)) {
		if (in_array($myrow['supplierid'], $_POST['proveedores'])) {
			echo "<option selected value='" . $myrow['supplierid'] . "'>" . $myrow['suppname'] . "</option>";
		} else {
			echo "<option value='" . $myrow['supplierid'] . "'>" . $myrow['suppname'] . "</option>";
		}
	}
	
	echo "</select>";
	echo "						</td>
							</tr>";
	/************************************/
	/************************************/
	
	/************************************/
	/* SELECCION DE VENDEDOR POR FACTURA 
	echo "<tr><td>" . _('X Vendedor de Factura') . ":</td><td>";
	echo "<select name='vendedordefactura'>";
	$SQL = "SELECT  salesmancode, salesmanname FROM salesman where type = 1";

	$ErrMsg = _('No transactions were returned by the SQL because');
	$TransResult = DB_query($SQL,$db,$ErrMsg);
	
	echo "<option selected value='-1'>Todos los vendedores...</option>";
	
	while ($myrow=DB_fetch_array($TransResult)) {
		if ($myrow['salesmancode'] == $_POST['vendedordefactura']){
			echo "<option selected value='" . $myrow['salesmancode'] . "'>" . $myrow['salesmanname'] . "</option>";	
		}else{
			echo "<option value='" . $myrow['salesmancode'] . "'>" . $myrow['salesmanname'] . "</option>";
		}
	}
	 
	echo "</select>";
	echo "</td></tr>";
	
	echo "<tr><td>" . _('X Vendedor de Cliente') . ":</td><td>";
	echo "<select name='vendedordecliente'>";
	$SQL = "SELECT  salesmancode, salesmanname FROM salesman where type = 1";

	$ErrMsg = _('No transactions were returned by the SQL because');
	$TransResult = DB_query($SQL,$db,$ErrMsg);
	
	echo "<option selected value='-1'>Todos los vendedores...</option>";
	
	while ($myrow=DB_fetch_array($TransResult)) {
		if ($myrow['salesmancode'] == $_POST['vendedordecliente']){
			echo "<option selected value='" . $myrow['salesmancode'] . "'>" . $myrow['salesmanname'] . "</option>";	
		}else{
			echo "<option value='" . $myrow['salesmancode'] . "'>" . $myrow['salesmanname'] . "</option>";
		}
	}
	 
	echo "</select>";
	echo "</td></tr>";
	/************************************/
	
	
	if (!isset($_POST['clavecliente'])) {
	    $_POST['clavecliente'] = '*';  
	} 
	/************************************/
	/* SELECCION DEL PROVEDOR */
	echo "					<tr>
								<td class='texto_lista' nowrap>" . _('Clave de Proveedor') . ":</td>
								<td>"; 
	echo "<input type=text name='clavecliente' value='".$_POST['clavecliente']."'>:* para todos.";
	echo "						</td>
							</tr>";
	/************************************/
	echo "				</table>
					</td>
				</tr>"; 
	
	echo '		<tr>
					<td colspan=3 valign="top">
						<table align=center bgcolor=#eeeeee width=100%>
							<tr>
								<td class="titulos_principales4" colspan=2>' .('Productos'). '</td>';
	echo '					</tr>';
	
	/************************************/
	/* SELECCION DEL GRUPO DE PRODUCTOS */
	echo '					<tr>
								<td class="texto_lista">' . _('Del Grupo') . ':' . "</td>
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
	echo '</select>
								</td>
							</tr>';
	/************************************/
	/************************************/
	/* SELECCION DEL LINEA DE PRODUCTOS */
	echo '					<tr>
								<td class="texto_lista">' . _('Linea') . ':' . "</td>
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
	echo '</select>	
								</td>
							</tr>';
	
	
	/************************************/
	/* SELECCION DEL CATEGORIA DE PRODUCTOS */
	echo '					<tr>
								<td class="texto_lista">' . _('Categor&iacute;a') . ':' . "</td>
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
	echo '</select>
								</td>
							</tr>';
	echo '				</table>
					</td>';
	/************************************/
	
	echo '			<td colspan=3 valign="top">
						<table align=center bgcolor=#eeeeee width=100%>
							<tr>
								<td class="titulos_principales4" colspan=2>' .('Detalle del Reporte'). '</td>';
	echo '					</tr>';
	
	echo '					<tr>
								<td class="texto_lista">' . _('Detalle Producto') . ':' . "</td>
								<td><select tabindex='5' name='detalleproducto'>";
		if (isset($_POST['detalleproducto']) and $_POST['detalleproducto'] == 'Todos')
			echo "<option selected value='Todos'>" . _('Todos');
		else
			echo "<option value='Todos'>" . _('Todos');
			
		if (isset($_POST['detalleproducto']) and $_POST['detalleproducto'] == 'Inventariado')
			echo "<option selected value='Inventariado'>" . _('Inventariado');
		else
			echo "<option value='Inventariado'>" . _('Inventariado');
			
		if (isset($_POST['detalleproducto']) and $_POST['detalleproducto'] == 'NOInventariado')
			echo "<option selected value='NOInventariado'>" . _('NO Inventariado');
		else
			echo "<option value='NOInventariado'>" . _('NO Inventariado');
			
		if (isset($_POST['detalleproducto']) and $_POST['detalleproducto'] == 'SOLOPendientes')
			echo "<option selected value='SOLOPendientes'>" . _('Solo Pendientes');
		else
			echo "<option value='SOLOPendientes'>" . _('Solo Pendientes');
		
		
	
	echo '					<tr>
								<td class="texto_lista">' . _('Nivel de Detalle') . ':' . "</td>
								<td><select tabindex='5' name='DetailedReport'>";

	if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Region')
		echo "<option selected value='Region'>" . _('Regi&oacute;n');
	else
		echo "<option value='Region'>" . _('Regi&oacute;n');

	if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Area')
		echo "<option selected value='Area'>" . _('&Aacute;rea');
	else
		echo "<option value='Area'>" . _('&Aacute;rea');
	
	if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'UnidadNegocio')
		echo "<option selected value='UnidadNegocio'>" . _('Unidad Negocios');
	else
		echo "<option value='UnidadNegocio'>" . _('Unidad Negocios');
	
	
        if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Linea')
		echo "<option selected value='Linea'>" . _('L&iacute;nea Producto');
	else
		echo "<option value='Linea'>" . _('L&iacute;nea Producto');
		
        if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Categoria')
		echo "<option selected value='Categoria'>" . _('Categor&iacute;a Producto');
	else
		echo "<option value='Categoria'>" . _('Categor&iacute;a Producto');
	/*
	if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Factura')
		echo "<option selected value='Factura'>" . _('X Factura');
	else
		echo "<option value='Factura'>" . _('X Factura');
	*/	
        if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Producto')
		echo "<option selected value='Producto'>" . _('Clave Producto');
	else
		echo "<option value='Producto'>" . _('Clave Producto');
	
	if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'proveedor')
		echo "<option selected value='proveedor'>" . _('Proveedor');
	else
		echo "<option value='proveedor'>" . _('Proveedor');
	if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Factura')
		echo "<option selected value='Factura'>" . _('Factura');
	else
		echo "<option value='Factura'>" . _('Factura');
		
	echo '</select>
								</td>
							</tr>';
	echo '				</table>
					</td>
				</tr>
			</table>
		</fieldset>';
	echo '	<div class="centre">
				<button type=submit name="ReportePantalla" style="cursor:pointer; border:0; background-color:transparent;" >
					<img src="images/buscar_25.png" title="REPORTE EN PANTALLA">
				</button>
				<button type=submit name="EnviaExcel" style="cursor:pointer; border:0; background-color:transparent;">
					<img src="images/exprtr_excel_25.png" title="GENERA REPORTE EN EXCEL">
				</button>
			</div><br>';
	//echo '<br><div class="centre"><input tabindex="7" type=submit name="PrintPDF" value="' . _('Imprime Archivo PDF') . '"></div>';
	$supplierWhereCond = "";
	if(empty($_POST['proveedores']) == FALSE) {
		if(in_array('0', $_POST['proveedores']) == FALSE) {
			$suppliers = array();
			foreach($_POST['proveedores'] as $supplierId) {
					$suppliers[] = "'$supplierId'";
			}
			$supplierWhereCond .= " and p.supplierno IN (" . implode(',', $suppliers). ")";
		}
	}
}  // fin de la condicion para enviar la informacion a Excel


// 	Imprimir informacion en pantalla o excel
if (isset($_POST['ReportePantalla']) || isset($_POST['EnviaExcel']))
{
	if (isset($_POST['EnviaExcel'])) {
		header("Content-type: application/ms-excel");
		# replace excelfile.xls with whatever you want the filename to default to
		header("Content-Disposition: attachment; filename=ReporteGeneralCompras.xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		
		echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
		echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
		echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
	}
	
	
	if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Region')  {
		$SQL = "SELECT r.regioncode, r.name,
			sum(d.quantityord) as quantityord,
			sum(case when month(p.orddate) = month(gr.deliverydate) then gr.qtyrecd else 0 end) as quantityrecd,
			sum(d.qtyinvoiced) as qtyinvoiced,
			avg(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100))) as avgunitprice,
			avg(d.stdcostunit) as avgstdcostunit,
			avg(d.actprice) as avgactprice,
			sum(d.quantityord*(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100)))) as ordcostototal,
			sum(d.quantityrecd*d.stdcostunit) as reccostototal,
			sum(d.qtyinvoiced*d.actprice) as faccostototal
			FROM  purchorders p
			left join purchorderdetails d on  p.orderno = d.orderno
			-- left join grns gr ON gr.podetailitem = d.podetailitem and gr.itemcode = d.itemcode and gr.qtyrecd <> 0
			left join (select grnno, sum(qtyrecd) as qtyrecd, deliverydate, podetailitem  from grns group by podetailitem having sum(qtyrecd) <> 0) as gr
			on gr.podetailitem = d.podetailitem
			
			left join stockmaster s on d.itemcode = s.stockid
			left join stockcategory c on s.categoryid = c.categoryid
			left join ProdLine i ON c.prodLineId = i.Prodlineid
			left join ProdGroup g ON i.Prodgroupid = g.Prodgroupid
			left join locations a on p.intostocklocation = a.loccode
			left join tags t on p.tagref  = t.tagref
			left join legalbusinessunit l on t.legalid = l.legalid
			left join departments m on t.u_department = m.u_department
			left join areas e on t.areacode = e.areacode
			left join regions r on e.regioncode = r.regioncode
			WHERE orddate between '" . $fechaini . "' and '" . $fechafin . "'
			AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
			AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			AND (p.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			AND (p.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')
			AND (g.Prodgroupid = '" . $_POST['xGrupo'] . "' or  '" . $_POST['xGrupo'] . "'='0')
			AND (i.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea']."'='0')
			and (c.categoryid = '" . $_POST['xCategoria']. "' or '" . $_POST['xCategoria']."'='0') $supplierWhereCond";
			if ($_POST['detalleproducto'] == 'Inventariado'){
				$SQL = $SQL  . " and s.mbflag = 'B'";
			}
			if ($_POST['detalleproducto'] == 'NOInventariado'){
				$SQL = $SQL  . " and s.mbflag <> 'B'";
			} elseif ($_POST['detalleproducto'] == 'SOLOPendientes'){
				$SQL = $SQL  . " and d.qtyinvoiced <> gr.qtyrecd";
			}
			
			
		$SQL = $SQL  . " GROUP BY r.regioncode
			ORDER BY r.name";
	
	}elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Area')  {
		$SQL = "SELECT e.areacode, e.areadescription,
			sum(d.quantityord) as quantityord,
			sum(case when month(p.orddate) = month(gr.deliverydate) then gr.qtyrecd else 0 end) as quantityrecd,
			sum(d.qtyinvoiced) as qtyinvoiced,
			avg(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100))) as avgunitprice,
			avg(d.stdcostunit) as avgstdcostunit,
			avg(d.actprice) as avgactprice,
			sum(d.quantityord*(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100)))) as ordcostototal,
			sum(d.quantityrecd*d.stdcostunit) as reccostototal,
			sum(d.qtyinvoiced*d.actprice) as faccostototal
			FROM  purchorders p
			left join purchorderdetails d on  p.orderno = d.orderno
			-- left join grns gr ON gr.podetailitem = d.podetailitem and gr.itemcode = d.itemcode and gr.qtyrecd <> 0
			left join (select grnno, sum(qtyrecd) as qtyrecd, deliverydate, podetailitem  from grns group by podetailitem having sum(qtyrecd) <> 0) as gr
			on gr.podetailitem = d.podetailitem	
			
			left join stockmaster s on d.itemcode = s.stockid
			left join stockcategory c on s.categoryid = c.categoryid
			left join ProdLine i ON c.prodLineId = i.Prodlineid
			left join ProdGroup g ON i.Prodgroupid = g.Prodgroupid
			left join locations a on p.intostocklocation = a.loccode
			left join tags t on p.tagref  = t.tagref
			left join legalbusinessunit l on t.legalid = l.legalid
			left join departments m on t.u_department = m.u_department
			left join areas e on t.areacode = e.areacode
			left join regions r on e.regioncode = r.regioncode
			WHERE orddate between '" . $fechaini . "' and '" . $fechafin . "'
			AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
			AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			AND (p.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			AND (p.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')
			AND (g.Prodgroupid = '" . $_POST['xGrupo'] . "' or  '" . $_POST['xGrupo'] . "'='0')
			AND (i.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea']."'='0')
			and (c.categoryid = '" . $_POST['xCategoria']. "' or '" . $_POST['xCategoria']."'='0') $supplierWhereCond";
			if ($_POST['detalleproducto'] == 'Inventariado'){
				$SQL = $SQL  . " and s.mbflag = 'B'";
			}
			if ($_POST['detalleproducto'] == 'NOInventariado'){
				$SQL = $SQL  . " and s.mbflag <> 'B'";
			} elseif ($_POST['detalleproducto'] == 'SOLOPendientes'){
				$SQL = $SQL  . " and d.qtyinvoiced <> gr.qtyrecd";
			}
			
			
		$SQL = $SQL  . " GROUP BY e.areacode
			ORDER BY e.areadescription";
	
	}elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'UnidadNegocio')  {
		$SQL = "SELECT t.tagref, t.tagdescription,
			sum(d.quantityord) as quantityord,
			sum(case when month(p.orddate) = month(gr.deliverydate) then gr.qtyrecd else 0 end) as quantityrecd,
			sum(d.qtyinvoiced) as qtyinvoiced,
			avg(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100))) as avgunitprice,
			avg(d.stdcostunit) as avgstdcostunit,
			avg(d.actprice) as avgactprice,
			sum(d.quantityord*(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100)))) as ordcostototal,
			sum(d.quantityrecd*d.stdcostunit) as reccostototal,
			sum(d.qtyinvoiced*d.actprice) as faccostototal
			FROM  purchorders p
			left join purchorderdetails d on  p.orderno = d.orderno
			-- left join grns gr ON gr.podetailitem = d.podetailitem and gr.itemcode = d.itemcode and gr.qtyrecd <> 0
			left join (select grnno, sum(qtyrecd) as qtyrecd, deliverydate, podetailitem  from grns group by podetailitem having sum(qtyrecd) <> 0) as gr
			on gr.podetailitem = d.podetailitem
				
			left join stockmaster s on d.itemcode = s.stockid
			left join stockcategory c on s.categoryid = c.categoryid
			left join ProdLine i ON c.prodLineId = i.Prodlineid
			left join ProdGroup g ON i.Prodgroupid = g.Prodgroupid
			left join locations a on p.intostocklocation = a.loccode
			left join tags t on p.tagref  = t.tagref
			left join legalbusinessunit l on t.legalid = l.legalid
			left join departments m on t.u_department = m.u_department
			left join areas e on t.areacode = e.areacode
			left join regions r on e.regioncode = r.regioncode
			WHERE orddate between '" . $fechaini . "' and '" . $fechafin . "'
			AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
			AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			AND (p.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			AND (p.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')
			AND (g.Prodgroupid = '" . $_POST['xGrupo'] . "' or  '" . $_POST['xGrupo'] . "'='0')
			AND (i.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea']."'='0')
			and (c.categoryid = '" . $_POST['xCategoria']. "' or '" . $_POST['xCategoria']."'='0') $supplierWhereCond";
			if ($_POST['detalleproducto'] == 'Inventariado'){
				$SQL = $SQL  . " and s.mbflag = 'B'";
			}
			if ($_POST['detalleproducto'] == 'NOInventariado'){
				$SQL = $SQL  . " and s.mbflag <> 'B'";
			} elseif ($_POST['detalleproducto'] == 'SOLOPendientes'){
				$SQL = $SQL  . " and d.qtyinvoiced <> gr.qtyrecd";
			}
			
			
		$SQL = $SQL  . " GROUP BY t.tagref
			ORDER BY t.tagdescription";
	
	}
	elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Producto') {
		$SQL = "SELECT  p.orderno,
			p.supplierno,
			p.orddate,
			p.intostocklocation,
			p.tagref,
			d.podetailitem,
			d.itemcode,
			d.itemdescription,
			SUM(d.qtyinvoiced) as qtyinvoiced,
			avg(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100))) as unitprice,
			avg(d.actprice) as actprice,
			avg(d.stdcostunit) as stdcostunit,
			sum(d.quantityord) as quantityord,
			sum(d.quantityrecd) as quantityrecd,
			/* ifnull((Select SUM(qtyrecd) 
					FROM grns 
					WHERE grns.podetailitem = d.podetailitem 
					and grns.itemcode = d.itemcode 
					and grns.qtyrecd <> 0
					and month(p.orddate) = month(grns.deliverydate)),0) as quantityrecd,*/
			avg((d.discountpercent1/100)) as discountpercent1,
			avg((d.discountpercent2/100)) as discountpercent2,
			avg((d.discountpercent3/100)) as discountpercent3
			from  purchorders p
			left join purchorderdetails d on  p.orderno = d.orderno
			left join stockmaster s on d.itemcode = s.stockid
			left join stockcategory c on s.categoryid = c.categoryid
			left join ProdLine i ON c.prodLineId = i.Prodlineid
			left join ProdGroup g ON i.Prodgroupid = g.Prodgroupid
			left join locations a on p.intostocklocation = a.loccode
			left join tags t on p.tagref  = t.tagref 
			left join legalbusinessunit l on t.legalid = l.legalid
			left join departments m on t.u_department = m.u_department
			left join areas e on t.areacode = e.areacode
			left join regions r on e.regioncode = r.regioncode
			where orddate between '" . $fechaini . "' and '" . $fechafin . "'
			AND status <> 'Cancelled'
			AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
			AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			and (a.loccode = '".$_POST['almacen']."' or '".$_POST['almacen']."'='0')
			AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			AND (p.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			AND (p.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')
			AND (g.Prodgroupid = '" . $_POST['xGrupo'] . "' or  '" . $_POST['xGrupo'] . "'='0')
			AND (i.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea']."'='0')
			and (c.categoryid = '" . $_POST['xCategoria']. "' or '" . $_POST['xCategoria']."'='0') $supplierWhereCond";
			if ($_POST['detalleproducto'] == 'Inventariado'){
				$SQL = $SQL  . " and s.mbflag = 'B'";	
			} elseif ($_POST['detalleproducto'] == 'NOInventariado'){
				$SQL = $SQL  . " and s.mbflag <> 'B'";	
			} elseif ($_POST['detalleproducto'] == 'SOLOPendientes'){
				$SQL = $SQL  . " and d.qtyinvoiced <> gr.qtyrecd";	
			}
			
			$SQL = $SQL  . " Group By p.orderno, d.itemcode  
							 order by p.orderno";
			
			//echo $SQL;
	/* ES DETALLADO POR DOCUMENTO */
	} //elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Factura')  {
	//}
	elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Categoria')  {
		$SQL = "SELECT c.categoryid, c.categorydescription,
			sum(d.quantityord) as quantityord,
			sum(case when month(p.orddate) = month(gr.deliverydate) then gr.qtyrecd else 0 end) as quantityrecd,
			sum(d.qtyinvoiced) as qtyinvoiced,
			avg(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100))) as avgunitprice,
			avg(d.stdcostunit) as avgstdcostunit,
			avg(d.actprice) as avgactprice,
			sum(d.quantityord*(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100)))) as ordcostototal,
			sum(d.quantityrecd*d.stdcostunit) as reccostototal,
			sum(d.qtyinvoiced*d.actprice) as faccostototal
			FROM  purchorders p
			left join purchorderdetails d on  p.orderno = d.orderno
			-- left join grns gr ON gr.podetailitem = d.podetailitem and gr.itemcode = d.itemcode and gr.qtyrecd <> 0
			left join (select grnno, sum(qtyrecd) as qtyrecd, deliverydate, podetailitem  from grns group by podetailitem having sum(qtyrecd) <> 0) as gr
			on gr.podetailitem = d.podetailitem
				
			left join stockmaster s on d.itemcode = s.stockid
			left join stockcategory c on s.categoryid = c.categoryid
			left join ProdLine i ON c.prodLineId = i.Prodlineid
			left join ProdGroup g ON i.Prodgroupid = g.Prodgroupid
			left join locations a on p.intostocklocation = a.loccode
			left join tags t on p.tagref  = t.tagref 
			left join legalbusinessunit l on t.legalid = l.legalid
			left join departments m on t.u_department = m.u_department
			left join areas e on t.areacode = e.areacode
			left join regions r on e.regioncode = r.regioncode
			WHERE orddate between '" . $fechaini . "' and '" . $fechafin . "'
			AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
			AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			AND (p.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			AND (p.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')
			AND (g.Prodgroupid = '" . $_POST['xGrupo'] . "' or  '" . $_POST['xGrupo'] . "'='0')
			AND (i.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea']."'='0')
			and (c.categoryid = '" . $_POST['xCategoria']. "' or '" . $_POST['xCategoria']."'='0') $supplierWhereCond";
			if ($_POST['detalleproducto'] == 'Inventariado'){
				$SQL = $SQL  . " and s.mbflag = 'B'";	
			}
			if ($_POST['detalleproducto'] == 'NOInventariado'){
				$SQL = $SQL  . " and s.mbflag <> 'B'";	
			} elseif ($_POST['detalleproducto'] == 'SOLOPendientes'){
				$SQL = $SQL  . " and d.qtyinvoiced <> gr.qtyrecd";	
			}
			
			
			$SQL = $SQL  . " GROUP BY c.categoryid, c.categorydescription
			ORDER BY c.categoryid, c.categorydescription";
	} elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'ContableVts')  {
        } elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'ContableCosto')  {
	} elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Linea')  {
	    	$SQL = "SELECT i.Prodlineid, i.Description,
			sum(d.quantityord) as quantityord,
			sum(case when month(p.orddate) = month(gr.deliverydate) then gr.qtyrecd else 0 end) as quantityrecd,
			sum(d.qtyinvoiced) as qtyinvoiced,
			avg(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100))) as avgunitprice,
			avg(d.stdcostunit) as avgstdcostunit,
			avg(d.actprice) as avgactprice,
			sum(d.quantityord*(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100)))) as ordcostototal,
			sum(d.quantityrecd*d.stdcostunit) as reccostototal,
			sum(d.qtyinvoiced*d.actprice) as faccostototal
			FROM  purchorders p
			left join purchorderdetails d on  p.orderno = d.orderno
			-- left join grns gr ON gr.podetailitem = d.podetailitem and gr.itemcode = d.itemcode and gr.qtyrecd <> 0
	    	left join (select grnno, sum(qtyrecd) as qtyrecd, deliverydate, podetailitem  from grns group by podetailitem having sum(qtyrecd) <> 0) as gr
			on gr.podetailitem = d.podetailitem
	    			
			left join stockmaster s on d.itemcode = s.stockid
			left join stockcategory c on s.categoryid = c.categoryid
			left join ProdLine i ON c.prodLineId = i.Prodlineid
			left join ProdGroup g ON i.Prodgroupid = g.Prodgroupid
			left join locations a on p.intostocklocation = a.loccode
			left join tags t on p.tagref  = t.tagref 
			left join legalbusinessunit l on t.legalid = l.legalid
			left join departments m on t.u_department = m.u_department
			left join areas e on t.areacode = e.areacode
			left join regions r on e.regioncode = r.regioncode
			WHERE orddate between '" . $fechaini . "' and '" . $fechafin . "'
			AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
			AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			AND (p.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			AND (p.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')
			AND (g.Prodgroupid = '" . $_POST['xGrupo'] . "' or  '" . $_POST['xGrupo'] . "'='0')
			AND (i.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea']."'='0')
			and (c.categoryid = '" . $_POST['xCategoria']. "' or '" . $_POST['xCategoria']."'='0') $supplierWhereCond";
			if ($_POST['detalleproducto'] == 'Inventariado'){
				$SQL = $SQL  . " and s.mbflag = 'B'";	
			}
			if ($_POST['detalleproducto'] == 'NOInventariado'){
				$SQL = $SQL  . " and s.mbflag <> 'B'";	
			} elseif ($_POST['detalleproducto'] == 'SOLOPendientes'){
				$SQL = $SQL  . " and d.qtyinvoiced <> gr.qtyrecd";	
			}
			
			
			$SQL = $SQL  . " GROUP BY i.Prodlineid, i.Description
			ORDER BY i.Prodlineid, i.Description";
	} elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'proveedor')  {
	    	$SQL = "SELECT p.supplierno, pr.suppname,
			sum(d.quantityord) as quantityord,
			sum(case when month(p.orddate) = month(gr.deliverydate) then gr.qtyrecd else 0 end) as quantityrecd,
			sum(d.qtyinvoiced) as qtyinvoiced,
			avg(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100))) as avgunitprice,
			avg(d.stdcostunit) as avgstdcostunit,
			avg(d.actprice) as avgactprice,
			sum(d.quantityord*(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100)))) as ordcostototal,
			sum(d.quantityrecd*d.stdcostunit) as reccostototal,
			sum(d.qtyinvoiced*d.actprice) as faccostototal
			FROM  purchorders p
			left join suppliers pr ON p.supplierno = pr.supplierid
			left join purchorderdetails d on  p.orderno = d.orderno
			-- left join grns gr ON gr.podetailitem = d.podetailitem and gr.itemcode = d.itemcode and gr.qtyrecd <> 0
	    	left join (select grnno, sum(qtyrecd) as qtyrecd, deliverydate, podetailitem  from grns group by podetailitem having sum(qtyrecd) <> 0) as gr
			on gr.podetailitem = d.podetailitem
	    			
			left join stockmaster s on d.itemcode = s.stockid
			left join stockcategory c on s.categoryid = c.categoryid
			left join ProdLine i ON c.prodLineId = i.Prodlineid
			left join ProdGroup g ON i.Prodgroupid = g.Prodgroupid
			left join locations a on p.intostocklocation = a.loccode
			left join tags t on p.tagref  = t.tagref 
			left join legalbusinessunit l on t.legalid = l.legalid
			left join departments m on t.u_department = m.u_department
			left join areas e on t.areacode = e.areacode
			left join regions r on e.regioncode = r.regioncode
			WHERE orddate between '" . $fechaini . "' and '" . $fechafin . "'
			AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
			AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			AND (p.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			AND (p.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')
			AND (g.Prodgroupid = '" . $_POST['xGrupo'] . "' or  '" . $_POST['xGrupo'] . "'='0')
			AND (i.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea']."'='0')
			and (c.categoryid = '" . $_POST['xCategoria']. "' or '" . $_POST['xCategoria']."'='0') $supplierWhereCond";
			if ($_POST['detalleproducto'] == 'Inventariado'){
				$SQL = $SQL  . " and s.mbflag = 'B'";	
			}
			if ($_POST['detalleproducto'] == 'NOInventariado'){
				$SQL = $SQL  . " and s.mbflag <> 'B'";	
			} elseif ($_POST['detalleproducto'] == 'SOLOPendientes'){
				$SQL = $SQL  . " and d.qtyinvoiced <> gr.qtyrecd";	
			}
			
			
			$SQL = $SQL  . " GROUP BY p.supplierno, pr.suppname
			ORDER BY p.supplierno, pr.suppname";
			//echo $SQL; 
	}elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Factura')  {
		//echo "entra";
	    	$SQL = "SELECT p.supplierno, pr.suppname,
			sum(d.quantityord) as quantityord,
			sum(case when month(p.orddate) = month(gr.deliverydate) then gr.qtyrecd else 0 end) as quantityrecd,
			sum(d.qtyinvoiced) as qtyinvoiced,
			avg(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100))) as avgunitprice,
			avg(d.stdcostunit) as avgstdcostunit,
			avg(d.actprice) as avgactprice,
			sum(d.quantityord*(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100)))) as ordcostototal,
			sum(supptransdetails.qty*d.stdcostunit) as reccostototal,
			sum(supptransdetails.qty*supptransdetails.price) as faccostototal,
			supptrans.suppreference
			FROM  purchorders p
			left join suppliers pr ON p.supplierno = pr.supplierid			
			left join purchorderdetails d on  p.orderno = d.orderno
			-- left join grns gr ON gr.podetailitem = d.podetailitem and gr.itemcode = d.itemcode and gr.qtyrecd <> 0
			left join (select grnno, sum(qtyrecd) as qtyrecd, deliverydate, podetailitem  from grns group by podetailitem having sum(qtyrecd) <> 0) as gr
			on gr.podetailitem = d.podetailitem
	    			
	    	left join supptransdetails ON p.orderno = supptransdetails.orderno and supptransdetails.grns=gr.grnno and supptransdetails.stockid=d.itemcode
			left join supptrans ON  supptransdetails.supptransid=supptrans.id
			left join stockmaster s on d.itemcode = s.stockid
			left join stockcategory c on s.categoryid = c.categoryid
			left join ProdLine i ON c.prodLineId = i.Prodlineid
			left join ProdGroup g ON i.Prodgroupid = g.Prodgroupid
			left join locations a on p.intostocklocation = a.loccode
			left join tags t on p.tagref  = t.tagref 
			left join legalbusinessunit l on t.legalid = l.legalid
			left join departments m on t.u_department = m.u_department
			left join areas e on t.areacode = e.areacode
			left join regions r on e.regioncode = r.regioncode
			WHERE orddate between '" . $fechaini . "' and '" . $fechafin . "'
			AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
			AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			AND (p.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			AND (p.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')
			AND (g.Prodgroupid = '" . $_POST['xGrupo'] . "' or  '" . $_POST['xGrupo'] . "'='0')
			AND (i.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea']."'='0')
			and (c.categoryid = '" . $_POST['xCategoria']. "' or '" . $_POST['xCategoria']."'='0') $supplierWhereCond";
			if ($_POST['detalleproducto'] == 'Inventariado'){
				$SQL = $SQL  . " and s.mbflag = 'B'";	
			}
			if ($_POST['detalleproducto'] == 'NOInventariado'){
				$SQL = $SQL  . " and s.mbflag <> 'B'";	
			} elseif ($_POST['detalleproducto'] == 'SOLOPendientes'){
				$SQL = $SQL  . " and d.qtyinvoiced <> gr.qtyrecd";	
			}
			
			
			$SQL = $SQL  . " GROUP BY p.supplierno, pr.suppname,supptrans.suppreference
			ORDER BY p.supplierno, pr.suppname,supptrans.suppreference";
			//echo $SQL; 
	}
	// echo "<pre>".$SQL;
	/*if ($_SESSION['UserID']=="admin"){
		echo "<pre>".$SQL;
		//exit;
	}*/////
	$ReportResult = DB_query($SQL,$db,'','',False,False); /*dont trap errors */

	if (DB_error_no($db) !=0) {
		$title = _('Reporte General de COMPRAS') . ' - ' . _('Reporte de Problema') ;
		prnMsg(_('Los detalles de ventas no se pudieron recuperar porque el SQL fallo') .  ' ' . DB_error_msg($db),'error');
		if ($debug==1){
			//echo "<br>".$SQL;
		}
		exit;
	}
	$DoctoTotPagos = 0;
	$VenceTotPagos = 0;
	$ProvTotPagos = 0;
	$RegionTotPagos = 0;
	$TotPagos = 0;
	
	echo '<table cellspacing=0 border=1 align="center" bordercolor=lightgray cellpadding=3>';
	
	if ($_POST['DetailedReport'] == 'Producto') {
		$headerDetalle = '	<tr>
								<th class="titulos_principales">' . _('Clave') . '</th>
								<th class="titulos_principales">' . _('Producto') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Ord') . '</th>
								<th class="titulos_principales">' . _('Precio<br>OCompra') . '</th>
								<th class="titulos_principales">' . _('Total<br>OCompra') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Rec') . '</th>
								<th class="titulos_principales">' . _('Precio<br>Recepci&oacute;n') . '</th>
								<th class="titulos_principales">' . _('Total<br>Recepci&oacute;n') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Fact') . '</th>
								<th class="titulos_principales">' . _('Precio<br>Factura') . '</th>
								<th class="titulos_principales">' . _('Total<br>Factura') . '</th>
							</tr>';
		
	} elseif ($_POST['DetailedReport'] == 'Categoria') {
		$headerDetalle = '	<tr>
								<th class="titulos_principales">' . _('Clave') . '</th>
								<th class="titulos_principales">' . _('Categoria') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Ord') . '</th>
								<th class="titulos_principales">' . _('Precio<br>OCompra') . '</th>
								<th class="titulos_principales">' . _('Total<br>OCompra') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Rec') . '</th>
								<th class="titulos_principales">' . _('Precio<br>Recepci&oacute;n') . '</th>
								<th class="titulos_principales">' . _('Total<br>Recepci&oacute;n') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Fact') . '</th>
								<th class="titulos_principales">' . _('Precio<br>Factura') . '</th>
								<th class="titulos_principales">' . _('Total<br>Factura') . '</th>
							</tr>';
        } elseif ($_POST['DetailedReport'] == 'ContableVts') {
        } elseif ($_POST['DetailedReport'] == 'ContableCosto') {
	} elseif ($_POST['DetailedReport'] == 'Linea') {
		$headerDetalle = '	<tr>
								<th class="titulos_principales">' . _('Clave') . '</th>
								<th class="titulos_principales">' . _('Linea') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Ord') . '</th>
								<th class="titulos_principales">' . _('Precio<br>OCompra') . '</th>
								<th class="titulos_principales">' . _('Total<br>OCompra') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Rec') . '</th>
								<th class="titulos_principales">' . _('Precio<br>Recepci&oacute;n') . '</th>
								<th class="titulos_principales">' . _('Total<br>Recepci&oacute;n') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Fact') . '</th>
								<th class="titulos_principales">' . _('Precio<br>Factura') . '</th>
								<th class="titulos_principales">' . _('Total<br>Factura') . '</th>
							</tr>';
	} elseif ($_POST['DetailedReport'] == 'proveedor') {
		$headerDetalle = '	<tr>
								<th class="titulos_principales">' . _('Codigo<br>Proveedor') . '</th>
								<th class="titulos_principales">' . _('Proveedor') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Ord') . '</th>
								<th class="titulos_principales">' . _('Precio<br>OCompra') . '</th>
								<th class="titulos_principales">' . _('Total<br>OCompra') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Rec') . '</th>
								<th class="titulos_principales">' . _('Precio<br>Recepci&oacute;n') . '</th>
								<th class="titulos_principales">' . _('Total<br>Recepci&oacute;n') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Fact') . '</th>
								<th class="titulos_principales">' . _('Precio<br>Factura') . '</th>
								<th class="titulos_principales">' . _('Total<br>Factura') . '</th>
							</tr>';
	}elseif ($_POST['DetailedReport'] == 'Factura') {
		$headerDetalle = '	<tr>
								<th class="titulos_principales">' . _('Factura') . '</th>
								<th class="titulos_principales">' . _('Proveedor') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Ord') . '</th>
								<th class="titulos_principales">' . _('Precio<br>OCompra') . '</th>
								<th class="titulos_principales">' . _('Total<br>OCompra') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Rec') . '</th>
								<th class="titulos_principales">' . _('Precio<br>Recepci&oacute;n') . '</th>
								<th class="titulos_principales">' . _('Total<br>Recepci&oacute;n') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Fact') . '</th>
								<th class="titulos_principales">' . _('Precio<br>Factura') . '</th>
								<th class="titulos_principales">' . _('Total<br>Factura') . '</th>
							</tr>';
	}elseif ($_POST['DetailedReport'] == 'Region') {
		$headerDetalle = '	<tr>
								<th class="titulos_principales">' . _('Codigo<br>Region') . '</th>
								<th class="titulos_principales">' . _('Region') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Ord') . '</th>
								<th class="titulos_principales">' . _('Precio<br>OCompra') . '</th>
								<th class="titulos_principales">' . _('Total<br>OCompra') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Rec') . '</th>
								<th class="titulos_principales">' . _('Precio<br>Recepci&oacute;n') . '</th>
								<th class="titulos_principales">' . _('Total<br>Recepci&oacute;n') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Fact') . '</th>
								<th class="titulos_principales">' . _('Precio<br>Factura') . '</th>
								<th class="titulos_principales">' . _('Total<br>Factura') . '</th>
							</tr>';
	}elseif ($_POST['DetailedReport'] == 'Area') {
		$headerDetalle = '	<tr>
								<th class="titulos_principales">' . _('Codigo<br>Area') . '</th>
								<th class="titulos_principales">' . _('Area') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Ord') . '</th>
								<th class="titulos_principales">' . _('Precio<br>OCompra') . '</th>
								<th class="titulos_principales">' . _('Total<br>OCompra') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Rec') . '</th>
								<th class="titulos_principales">' . _('Precio<br>Recepci&oacute;n') . '</th>
								<th class="titulos_principales">' . _('Total<br>Recepci&oacute;n') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Fact') . '</th>
								<th class="titulos_principales">' . _('Precio<br>Factura') . '</th>
								<th class="titulos_principales">' . _('Total<br>Factura') . '</th>
							</tr>';
	}elseif ($_POST['DetailedReport'] == 'UnidadNegocio') {
		$headerDetalle = '	<tr>
								<th class="titulos_principales">' . _('Codigo<br>Unid.Neg') . '</th>
								<th class="titulos_principales">' . _('Unid.Negocios') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Ord') . '</th>
								<th class="titulos_principales">' . _('Precio<br>OCompra') . '</th>
								<th class="titulos_principales">' . _('Total<br>OCompra') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Rec') . '</th>
								<th class="titulos_principales">' . _('Precio<br>Recepci&oacute;n') . '</th>
								<th class="titulos_principales">' . _('Total<br>Recepci&oacute;n') . '</th>
								<th class="titulos_principales">' . _('Cant<br>Fact') . '</th>
								<th class="titulos_principales">' . _('Precio<br>Factura') . '</th>
								<th class="titulos_principales">' . _('Total<br>Factura') . '</th>
							</tr>';
	}
	echo $headerDetalle;
	$botonProcesar = '		<tr>
								<td colspan=10></td>
								<td colspan=2><input type=submit name="guardaCalificaciones" value="' . _('Guarda Valores de Calificaci&oacute;n de Cartera') . '"></td>
							</tr>';

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
	
	$htmlCalificaDocto = ""; 
	$SQL = "SELECT  idstatus, accountstatus from accountstatus order by accountstatus";
	
	$ErrMsg = _('No transactions were returned by the SQL because');
	$TransResult = DB_query($SQL,$db,$ErrMsg);
	
	$htmlCalificaDocto = $htmlCalificaDocto. "<option selected value='0'>califique...</option>";
	while ($myrow=DB_fetch_array($TransResult)) {
		if ($myrow['idstatus'] == $_POST['estatusDocumento']){
		      $htmlCalificaDocto = $htmlCalificaDocto. "<option selected value=" . $myrow['idstatus'] . ">" . $myrow['accountstatus'] . "</option>";	
		}else{
		      $htmlCalificaDocto = $htmlCalificaDocto. "<option value=" . $myrow['idstatus'] . ">" . $myrow['accountstatus'] . "</option>";
		}
	}
	$htmlCalificaDocto = $htmlCalificaDocto. "</select>";
	/************************************/
	
	$AnteriorLegalName = "";
	$AnteriorRegionName = "";
	$AnteriorAreaDesc = "";
	$AnteriorDepartamento = "";
	$AnteriorUnidadNegocio = "";
	$AnteriorOrdenCompra = "";
	
      
	$AnteriorCliente = "";
	$AnteriorFactura = "";
      
	$totalQuantity = 0;
	$totalAmount = 0;
	$totalIVA = 0;
	$totalCost = 0;
	$totalCostAvg = 0;
	$totalIVACompra =0;
	
	$cantordencompra = 0;
	$cantrecepcion = 0;
	$cantfactura = 0;
	$totalcostoordencompra = 0;
	$totalcostorecepcion = 0;
	$totalcostofactura = 0 ;
	
	//TOTALES POR ORDEN DE COMPRA
	$OCcantordencompra = 0;
	$OCcantrecepcion = 0;
	$OCcantfactura = 0;
	$OCtotalcostoordencompra = 0;
	$OCtotalcostorecepcion = 0;
	$OCtotalcostofactura = 0 ;
	
      
	$FtotalQuantity = 0;
	$FtotalAmount = 0;
	$FtotalIVA = 0;
	$FtotalCost = 0;
	$FtotalCostAvg = 0;
	$FtotalIVACompra=0;
      
	$GtotalQuantity = 0;
	$GtotalAmount = 0;
	$GtotalIVA = 0;
	$GtotalCost = 0;
	$GtotalCostAvg = 0;
	$GtotalIVACompra =0;
      
	setlocale(LC_MONETARY, 'es_MX');
	while ($SlsAnalysis = DB_fetch_array($ReportResult,$db)){
		
		if ($_POST['DetailedReport'] == 'Producto') {
			if ($AnteriorOrdenCompra != $SlsAnalysis['orderno']){
				if ($primeraEntrada > 1){
					echo "<tr style='background-color:#F0F0F0'>";
					echo "	<td class='numero_celda' colspan='2'>OC: " . $AnteriorOrdenCompra . "</td>";
					echo "	<td class='numero_celda'>" .$OCcantordencompra . "</td>";
					echo "	<td class='numero_celda' nowrap>&nbsp;</td>";
					echo "	<td class='numero_celda' nowrap>" . money_format('%(#7.2n', ($OCtotalcostoordencompra)) . "</td>";
					echo "	<td class='numero_celda'>" . $OCcantrecepcion . "</td>";
					echo "	<td class='numero_celda' nowrap>&nbsp;</td>";
					echo "	<td class='numero_celda' nowrap>" . money_format('%(#7.2n', ($OCtotalcostorecepcion)) . "</td>";
					echo "	<td class='numero_celda'>" . $OCcantfactura . "</td>";
					echo "	<td class='numero_celda' nowrap>&nbsp;</td>";
					echo "	<td class='numero_celda' nowrap>" . money_format('%(#7.2n', ($OCtotalcostofactura)) . "</td>";
					echo "</tr>";	
				}
				//TOTALES POR ORDEN DE COMPRA
				$OCcantordencompra = 0;
				$OCcantrecepcion = 0;
				$OCcantfactura = 0;
				$OCtotalcostoordencompra = 0;
				$OCtotalcostorecepcion = 0;
				$OCtotalcostofactura = 0 ;
				$AnteriorOrdenCompra = $SlsAnalysis['orderno'];
			}
		}
		$primeraEntrada = $primeraEntrada + 1;
		
		//$preciocompra = $SlsAnalysis['unitprice'] - ($SlsAnalysis['unitprice']*$SlsAnalysis['discountpercent1']);
		//$preciocompra = $preciocompra  - ($SlsAnalysis['unitprice']*$SlsAnalysis['discountpercent2']);
		//$preciocompra = $preciocompra  - ($SlsAnalysis['unitprice']*$SlsAnalysis['discountpercent3']);
		
		if ($_POST['DetailedReport'] == 'Producto') {
			$codigo = $SlsAnalysis['itemcode'];
			//ES SOLO PARA INDICAR SI EL PRODUCTO LLEVA DESCUENTO
			if (($SlsAnalysis['discountpercent1']!=0) or ($SlsAnalysis['discountpercent2']!=0) or ($SlsAnalysis['discountpercent3']!=0)){
				$asterisco = 1;
			}else{
				$asterisco = 0;
			}
			$descripcion = $SlsAnalysis['itemdescription'];
			$cantorden = $SlsAnalysis['quantityord'];
			$preciocompra = $SlsAnalysis['unitprice'] - ($SlsAnalysis['unitprice']*$SlsAnalysis['discountpercent1']);
			$preciocompra = $preciocompra  - ($SlsAnalysis['unitprice']*$SlsAnalysis['discountpercent2']);
			$preciocompra = $preciocompra  - ($SlsAnalysis['unitprice']*$SlsAnalysis['discountpercent3']);
			
			$cantrec = $SlsAnalysis['quantityrecd'];
			$preciorec = $SlsAnalysis['stdcostunit'];
			
			$cantfac = $SlsAnalysis['qtyinvoiced'];
			$preciofac = $SlsAnalysis['actprice'];
			
			$costototalord = $cantorden * $preciocompra;
			$costototalrec = $cantrec * $preciorec;
			$costototalfac = $cantfac*$preciofac;
			
		} elseif ($_POST['DetailedReport'] == 'Categoria') {
			$codigo = $SlsAnalysis['categoryid'];
			//ES SOLO PARA INDICAR SI EL PRODUCTO LLEVA DESCUENTO
			$asterisco = 0;
			$descripcion = $SlsAnalysis['categorydescription'];
			$cantorden = $SlsAnalysis['quantityord'];
			$preciocompra = $SlsAnalysis['avgunitprice'];
			
			$cantrec = $SlsAnalysis['quantityrecd'];
			$preciorec = $SlsAnalysis['avgstdcostunit'];
			
			$cantfac = $SlsAnalysis['qtyinvoiced'];
			$preciofac = $SlsAnalysis['avgactprice'];
			
			$costototalord = $SlsAnalysis['ordcostototal'];
			$costototalrec = $SlsAnalysis['reccostototal'];
			$costototalfac = $SlsAnalysis['faccostototal'];
		}elseif ($_POST['DetailedReport'] == 'Linea') {
			$codigo = $SlsAnalysis['Prodlineid'];
			//ES SOLO PARA INDICAR SI EL PRODUCTO LLEVA DESCUENTO
			$asterisco = 0;
			$descripcion = $SlsAnalysis['Description'];
			$cantorden = $SlsAnalysis['quantityord'];
			$preciocompra = $SlsAnalysis['avgunitprice'];
			
			$cantrec = $SlsAnalysis['quantityrecd'];
			$preciorec = $SlsAnalysis['avgstdcostunit'];
			
			$cantfac = $SlsAnalysis['qtyinvoiced'];
			$preciofac = $SlsAnalysis['avgactprice'];
			
			$costototalord = $SlsAnalysis['ordcostototal'];
			$costototalrec = $SlsAnalysis['reccostototal'];
			$costototalfac = $SlsAnalysis['faccostototal'];
		}elseif ($_POST['DetailedReport'] == 'proveedor') {
			$codigo = $SlsAnalysis['supplierno'];
			//ES SOLO PARA INDICAR SI EL PRODUCTO LLEVA DESCUENTO
			$asterisco = 0;
			$descripcion = $SlsAnalysis['suppname'];
			$cantorden = $SlsAnalysis['quantityord'];
			$preciocompra = $SlsAnalysis['avgunitprice'];
			
			$cantrec = $SlsAnalysis['quantityrecd'];
			$preciorec = $SlsAnalysis['avgstdcostunit'];
			
			$cantfac = $SlsAnalysis['qtyinvoiced'];
			$preciofac = $SlsAnalysis['avgactprice'];
			
			$costototalord = $SlsAnalysis['ordcostototal'];
			$costototalrec = $SlsAnalysis['reccostototal'];
			$costototalfac = $SlsAnalysis['faccostototal'];
		}elseif ($_POST['DetailedReport'] == 'Factura') {
			$codigo = $SlsAnalysis['suppreference'];
			//ES SOLO PARA INDICAR SI EL PRODUCTO LLEVA DESCUENTO
			$asterisco = 0;
			$descripcion = $SlsAnalysis['supplierno'].' '.$SlsAnalysis['suppname'];
			$cantorden = $SlsAnalysis['quantityord'];
			$preciocompra = $SlsAnalysis['avgunitprice'];
			
			$cantrec = $SlsAnalysis['quantityrecd'];
			$preciorec = $SlsAnalysis['avgstdcostunit'];
			
			$cantfac = $SlsAnalysis['qtyinvoiced'];
			$preciofac = $SlsAnalysis['avgactprice'];
			
			$costototalord = $SlsAnalysis['ordcostototal'];
			$costototalrec = $SlsAnalysis['reccostototal'];
			$costototalfac = $SlsAnalysis['faccostototal'];
		}elseif ($_POST['DetailedReport'] == 'Region') {
			$codigo = $SlsAnalysis['regioncode'];
			//ES SOLO PARA INDICAR SI EL PRODUCTO LLEVA DESCUENTO
			$asterisco = 0;
			$descripcion = $SlsAnalysis['name'];
			$cantorden = $SlsAnalysis['quantityord'];
			$preciocompra = $SlsAnalysis['avgunitprice'];
			
			$cantrec = $SlsAnalysis['quantityrecd'];
			$preciorec = $SlsAnalysis['avgstdcostunit'];
			
			$cantfac = $SlsAnalysis['qtyinvoiced'];
			$preciofac = $SlsAnalysis['avgactprice'];
			
			$costototalord = $SlsAnalysis['ordcostototal'];
			$costototalrec = $SlsAnalysis['reccostototal'];
			$costototalfac = $SlsAnalysis['faccostototal'];
		}elseif ($_POST['DetailedReport'] == 'Area') {
			$codigo = $SlsAnalysis['areacode'];
			//ES SOLO PARA INDICAR SI EL PRODUCTO LLEVA DESCUENTO
			$asterisco = 0;
			$descripcion = $SlsAnalysis['areadescription'];
			$cantorden = $SlsAnalysis['quantityord'];
			$preciocompra = $SlsAnalysis['avgunitprice'];
			
			$cantrec = $SlsAnalysis['quantityrecd'];
			$preciorec = $SlsAnalysis['avgstdcostunit'];
			
			$cantfac = $SlsAnalysis['qtyinvoiced'];
			$preciofac = $SlsAnalysis['avgactprice'];
			
			$costototalord = $SlsAnalysis['ordcostototal'];
			$costototalrec = $SlsAnalysis['reccostototal'];
			$costototalfac = $SlsAnalysis['faccostototal'];
		}elseif ($_POST['DetailedReport'] == 'UnidadNegocio') {
			$codigo = $SlsAnalysis['tagref'];
			//ES SOLO PARA INDICAR SI EL PRODUCTO LLEVA DESCUENTO
			$asterisco = 0;
			$descripcion = $SlsAnalysis['tagdescription'];
			$cantorden = $SlsAnalysis['quantityord'];
			$preciocompra = $SlsAnalysis['avgunitprice'];
			
			$cantrec = $SlsAnalysis['quantityrecd'];
			$preciorec = $SlsAnalysis['avgstdcostunit'];
			
			$cantfac = $SlsAnalysis['qtyinvoiced'];
			$preciofac = $SlsAnalysis['avgactprice'];
			
			$costototalord = $SlsAnalysis['ordcostototal'];
			$costototalrec = $SlsAnalysis['reccostototal'];
			$costototalfac = $SlsAnalysis['faccostototal'];
		}
	
		echo "<tr>";
		echo "<td class='texto_normal2'>" . $codigo . "</td>";
		if ($asterisco == 1){
			echo "<td class='texto_normal2'>" . $descripcion . "*</td>";
		}else{
			echo "<td class='texto_normal2'>" . $descripcion . "</td>";
		}
		echo "<td class='numero_celda'>" .  $cantorden . "</td>";
		echo "<td class='numero_celda' nowrap>" . money_format('%(#6.2n', $preciocompra) . "</td>";
		echo "<td class='numero_celda' nowrap>" . money_format('%(#7.2n', ($costototalord)) . "</td>";
		echo "<td class='numero_celda'>" . $cantrec . "</td>";
		echo "<td class='numero_celda' nowrap>" . money_format('%(#6.2n', $preciorec) . "</td>";
		echo "<td class='numero_celda' nowrap>" . money_format('%(#7.2n', ($costototalrec)) . "</td>";
		echo "<td class='numero_celda'>" . $cantfac . "</td>";
		echo "<td class='numero_celda' nowrap>" . money_format('%(#6.2n', $preciofac) . "</td>";
		echo "<td class='numero_celda' nowrap>" . money_format('%(#7.2n', ($costototalfac)) . "</td>";
		echo "</tr>";
		
		$cantordencompra = $cantordencompra + $cantorden;
		$cantrecepcion = $cantrecepcion + $cantrec;
		$cantfactura = $cantfactura + $cantfac;
		$totalcostoordencompra = $totalcostoordencompra + ($costototalord);
		$totalcostorecepcion = $totalcostorecepcion + ($costototalrec);
		$totalcostofactura = $totalcostofactura + ($costototalfac);
		
		if ($_POST['DetailedReport'] == 'Producto') {
			$OCcantordencompra = $OCcantordencompra + $cantorden;
			$OCcantrecepcion = $OCcantrecepcion + $cantrec;
			$OCcantfactura = $OCcantfactura + $cantfac;
			$OCtotalcostoordencompra = $OCtotalcostoordencompra + ($costototalord);
			$OCtotalcostorecepcion = $OCtotalcostorecepcion + ($costototalrec);
			$OCtotalcostofactura = $OCtotalcostofactura + ($costototalfac);
		}
	    
	} /*end while loop */
	
	if ($_POST['DetailedReport'] == 'Producto') {
		
		echo "<tr style='background-color:#F0F0F0'>";
		echo "	<td class='texto_normal2' colspan='2'>OC: " . $AnteriorOrdenCompra . "</td>";
		echo "	<td class='numero_celda'>" .$OCcantordencompra . "</td>";
		echo "	<td class='numero_celda' nowrap>&nbsp;</td>";
		echo "	<td class='numero_celda' nowrap>" . money_format('%(#7.2n', ($OCtotalcostoordencompra)) . "</td>";
		echo "	<td class='numero_celda'>" . $OCcantrecepcion . "</td>";
		echo "	<td class='numero_celda' nowrap>&nbsp;</td>";
		echo "	<td class='numero_celda' nowrap>" . money_format('%(#7.2n', ($OCtotalcostorecepcion)) . "</td>";
		echo "	<td class='numero_celda'>" . $OCcantfactura . "</td>";
		echo "	<td class='numero_celda' nowrap>&nbsp;</td>";
		echo "	<td class='numero_celda' nowrap>" . money_format('%(#7.2n', ($OCtotalcostofactura)) . "</td>";
		echo "</tr>";	
		
	}
	
	echo "<tr style='background-color:#e3a8a8; color=#ffffff'>";
	echo "	<td class='texto_normal2'>&nbsp;</td>";
	echo "	<td class='texto_normal2'>&nbsp;</td>";
	echo "	<td class='numero_celda'>" .$cantordencompra . "</td>";
	echo "	<td class='numero_celda' nowrap>&nbsp;</td>";
	echo "	<td class='numero_celda' nowrap>" . money_format('%(#7.2n', ($totalcostoordencompra)) . "</td>";
	echo "	<td class='numero_celda'>" . $cantrecepcion . "</td>";
	echo "	<td class='numero_celda' nowrap>&nbsp;</td>";
	echo "	<td class='numero_celda' nowrap>" . money_format('%(#7.2n', ($totalcostorecepcion)) . "</td>";
	echo "	<td class='numero_celda'>" . $cantfactura . "</td>";
	echo "	<td class='numero_celda' nowrap>&nbsp;</td>";
	echo "	<td class='numero_celda' nowrap>" . money_format('%(#7.2n', ($totalcostofactura)) . "</td>";
	echo "</tr>";
	

/**INICIO MUESTRA RECEPCIONES DEL MES, DE ORDENES DE COMPRADE MESES ANTERIORES
**/
	$costogralfacturado = $totalcostofactura;
	$costogralrecepcion = $totalcostorecepcion;
	echo "<tr>
			<td colspan=12><br><br></tr>";
	echo "<tr>
			<td colspan=12 class='titulos_principales6'>" . _('RECEPCIONES DEL MES') . "</td>
		</tr>";
	if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Producto') {
		$headerDetalle = '<tr>
		<th class="titulosprincipales">' . _('Clave') . '</th>
		<th class="titulosprincipales">' . _('Producto') . '</th>
		<th class="titulosprincipales" colspan="3"><b>&nbsp;</th>
		<th class="titulosprincipales">' . _('Cant<br>Recibida') . '</th>
		<th class="titulosprincipales">' . _('Precio<br>Recepci&oacute;n') . '</th>
		<th class="titulosprincipales">' . _('$Total<br>Recepci&oacute;n') . '</th>
		<th class="titulosprincipales" colspan="3">&nbsp;</th>
		</tr>';
		
		$SQL = "SELECT  s.stockid,
			s.description,
			gr.qtyrecd,
			gr.stdcostunit
		FROM  grns  gr
			LEFT JOIN purchorderdetails d ON gr.podetailitem = d.podetailitem and gr.itemcode = d.itemcode 
			LEFT JOIN purchorders p ON  p.orderno = d.orderno
			LEFT JOIN stockmaster s on d.itemcode = s.stockid
			LEFT JOIN stockcategory c on s.categoryid = c.categoryid
			LEFT JOIN ProdLine i ON c.prodLineId = i.Prodlineid
			LEFT JOIN ProdGroup g ON i.Prodgroupid = g.Prodgroupid
			LEFT JOIN locations a on p.intostocklocation = a.loccode
			LEFT JOIN tags t on p.tagref  = t.tagref 
			LEFT JOIN legalbusinessunit l on t.legalid = l.legalid
			LEFT JOIN departments m on t.u_department = m.u_department
			LEFT JOIN areas e on t.areacode = e.areacode
			LEFT JOIN regions r on e.regioncode = r.regioncode
		WHERE 	gr.deliverydate BETWEEN '" . $fechaini . "' and '" . $fechafin . "'
			AND gr.qtyrecd <> 0
			AND month(p.orddate) <> month(gr.deliverydate)
			AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
			AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			AND (a.loccode = '".$_POST['almacen']."' or '".$_POST['almacen']."'='0')
			AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			AND (p.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			AND (p.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')
			AND (g.Prodgroupid = '" . $_POST['xGrupo'] . "' or  '" . $_POST['xGrupo'] . "'='0')
			AND (i.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea']."'='0')
			AND (c.categoryid = '" . $_POST['xCategoria']. "' or '" . $_POST['xCategoria']."'='0') $supplierWhereCond";
			if ($_POST['detalleproducto'] == 'Inventariado'){
				$SQL = $SQL  . " AND s.mbflag = 'B'";	
			}
			if ($_POST['detalleproducto'] == 'NOInventariado'){
				$SQL = $SQL  . " AND s.mbflag <> 'B'";	
			}

		$SQL = $SQL  . " ORDER BY  gr.grnbatch";
			
		//echo "<br>" . $SQL . "<br>";
		$ReportResult = DB_query($SQL,$db,'','',False,False); 
		if (DB_error_no($db) !=0) {
			$title = _('Reporte General de COMPRAS') . ' - ' . _('Reporte de Problema') ;
			prnMsg(_('Las recepciones no se pudieron recuperar porque el SQL fallo') .  ' ' . DB_error_msg($db),'error');
			if ($debug==1){
				//echo "<br>".$SQL;
			}
			exit;
		}
		//echo '<pre>' . $SQL;
		
		echo $headerDetalle;
		setlocale(LC_MONETARY, 'es_MX');
		$cantfactura = 0;
		$totalcostofactura = 0;
		while ($SlsAnalysis = DB_fetch_array($ReportResult,$db)){
			$codigo = $SlsAnalysis['stockid'];
			$descripcion = $SlsAnalysis['description'];
			$cantrec = $SlsAnalysis['qtyrecd'];
			$preciorec = $SlsAnalysis['stdcostunit'];
			$costototalrec = $cantrec * $preciorec;
			
			echo "<tr>";
				echo "<td class='peque'>" . $codigo . "</td>";
				echo "<td class='peque'>" . $descripcion . "</td>";
				echo "<td class='pequenum' colspan='3'>&nbsp;</td>";
				echo "<td class='pequenum'>" . $cantrec . "</td>";
				echo "<td class='pequenum' nowrap>" . money_format('%(#6.2n', $preciorec) . "</td>";
				echo "<td class='pequenum' nowrap>" . money_format('%(#7.2n', ($costototalrec)) . "</td>";
				echo "<td class='pequenum' colspan='3'>&nbsp;</td>";
			echo "</tr>";
			$cantrecibidos = $cantrecibidos + $cantrec;
			$totalcostorecibidos = $totalcostorecibidos + ($costototalrec);
		}
		echo "<tr>";
			echo "<td class='pie_derecha2' colspan='5'>" .  _('Total') . ": </td>";
			echo "<td class='pie_derecha2'>" . $cantrecibidos . "</td>";
			echo "<td class='pie_derecha2' nowrap>&nbsp;</td>";
			echo "<td class='pie_derecha2' nowrap>" . money_format('%(#7.2n', ($totalcostorecibidos)) . "</td>";
			echo "<td class='pie_derecha2' colspan='3'></td>";
		echo "</tr>";
		$costogralrecibidos = $totalcostorecibidos;
		echo "<tr>";
			echo "<td class='pie_derecha2' colspan='5'>" .  _('Total') . ": </td>";
			echo "<td class='pie_derecha2'>&nbsp;</td>";
			echo "<td class='pie_derecha2' nowrap>&nbsp;</td>";
			echo "<td class='pie_derecha2' nowrap>" . money_format('%(#7.2n', ($costogralrecepcion+$costogralrecibidos)) . "</td>";
			echo "<td class='pie_derecha2' colspan='3'></td>";
		echo "</tr>";
	}
	//}
			
/**FIN MUESTRA RECEPCIONES DEL MES, DE ORDENES DE COMPRA DE MESES ANTERIORES
**/
      
      
      
	$costogralfacturado = $totalcostofactura;
	echo "<tr><td colspan=12><br><br><br></td></tr>";
	echo "<tr><td colspan=12 bgcolor='#c9ccc9' class='titulos_sec' >" . _('NOTAS DE CREDITO FULL') . "</td></tr>";
	if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Producto') {
		$headerDetalle = '<tr>
		<th class="titulosprincipales">' . _('Clave') . '</th>
		<th class="titulosprincipales">' . _('Producto') . '</th>
		<th class="titulosprincipales" colspan="6">&nbsp;</th>
		<th class="titulosprincipales">' . _('Cant<br>N.Credito') . '</th>
		<th class="titulosprincipales">' . _('Precio<br>N.Credito') . '</th>
		<th class="titulosprincipales">' . _('$Total<br>N.Credito') . '</th>
		</tr>';
		
		$SQL = "SELECT  f.folio, 
			f.transno,
			s.stockid,
			s.description,
			k.qty,
			k.standardcost
		FROM  supptrans  f
			LEFT JOIN suppnotesorders n ON f.order_ = n.orderno
			LEFT JOIN suppnotesorderdetails d ON n.orderno = d.orderno
			LEFT JOIN stockmaster s on d.stkcode = s.stockid
			LEFT JOIN stockmoves k ON f.type = k.type and f.transno = k.transno and s.stockid = k.stockid
			LEFT JOIN stockcategory c on s.categoryid = c.categoryid
			LEFT JOIN ProdLine i ON c.prodLineId = i.Prodlineid
			LEFT JOIN ProdGroup g ON i.Prodgroupid = g.Prodgroupid
			LEFT JOIN  locations a on d.fromstkloc = a.loccode
			LEFT JOIN tags t on f.tagref  = t.tagref 
			LEFT JOIN legalbusinessunit l on t.legalid = l.legalid
			LEFT JOIN departments m on t.u_department = m.u_department
			LEFT JOIN areas e on t.areacode = e.areacode
			LEFT JOIN regions r on e.regioncode = r.regioncode
		WHERE f.type=33 and f.origtrandate BETWEEN '" . $fechaini . "' and '" . $fechafin . "'
			AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
			AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			AND (a.loccode = '".$_POST['almacen']."' or '".$_POST['almacen']."'='0')
			AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			AND (f.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			AND (f.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')
			AND (g.Prodgroupid = '" . $_POST['xGrupo'] . "' or  '" . $_POST['xGrupo'] . "'='0')
			AND (i.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea']."'='0')
			AND (c.categoryid = '" . $_POST['xCategoria']. "' or '" . $_POST['xCategoria']."'='0')";
			if ($_POST['detalleproducto'] == 'Inventariado'){
				$SQL = $SQL  . " AND s.mbflag = 'B'";	
			}
			if ($_POST['detalleproducto'] == 'NOInventariado'){
				$SQL = $SQL  . " AND s.mbflag <> 'B'";	
			}

		$SQL = $SQL  . " ORDER BY  f.transno";
		
		//echo $SQL;
		$ReportResult = DB_query($SQL,$db,'','',False,False); /*dont trap errors */
	
		if (DB_error_no($db) !=0) {
			$title = _('Reporte General de COMPRAS') . ' - ' . _('Reporte de Problema') ;
			prnMsg(_('Los detalles de Notas de Credito Full no se pudieron recuperar porque el SQL fallo') .  ' ' . DB_error_msg($db),'error');
			if ($debug==1){
				//echo "<br>".$SQL;
			}
			exit;
		}
		//echo $SQL;
		
		echo $headerDetalle;
		setlocale(LC_MONETARY, 'es_MX');
		$cantfactura = 0;
		$totalcostofactura = 0;
		while ($SlsAnalysis = DB_fetch_array($ReportResult,$db)){
			$codigo = $SlsAnalysis['stockid'];
			$descripcion = $SlsAnalysis['description'];
			$cantfac = $SlsAnalysis['qty'];
			$preciofac = $SlsAnalysis['standardcost'];
			$costototalfac = $cantfac * $preciofac;
			
			echo "<tr>";
				echo "<td class='peque'>" . $codigo . "</td>";
				echo "<td class='peque'>" . $descripcion . "</td>";
				echo "<td class='pequenum' colspan='6'>&nbsp;</td>";
				echo "<td class='pequenum'>" . $cantfac . "</td>";
				echo "<td class='pequenum' nowrap>" . money_format('%(#6.2n', $preciofac) . "</td>";
				echo "<td class='pequenum' nowrap>" . money_format('%(#7.2n', ($costototalfac)) . "</td>";
			echo "</tr>";
			$cantfactura = $cantfactura + $cantfac;
			$totalcostofactura = $totalcostofactura + ($costototalfac);
		}
		echo "<tr>";
			echo "<td class='pie_derecha2' colspan='8'>" .  _('Total') . ": </td>";
			echo "<td class='pie_derecha2'>" . $cantfactura . "</td>";
			echo "<td class='pie_derecha2' nowrap>&nbsp;</td>";
			echo "<td class='pie_derecha2' nowrap>" . money_format('%(#7.2n', ($totalcostofactura)) . "</td>";
		echo "</tr>";
		$costogralnotacredito = $totalcostofactura;
		echo "<tr>";
			echo "<td class='pie_derecha2' colspan='8'>" .  _('Total') . ": </td>";
			echo "<td class='pie_derecha2'>&nbsp;</td>";
			echo "<td class='pie_derecha2' nowrap>&nbsp;</td>";
			echo "<td class='pie_derecha2' nowrap>" . money_format('%(#7.2n', ($costogralfacturado+$costogralnotacredito)) . "</td>";
		echo "</tr>";
		
	} elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Categoria')  {
		$headerDetalle = '<tr>
		<th class="titulos_principales">' . _('Clave') . '</th>
		<th class="titulos_principales">' . _('Categor&iacute;a') . '</th>
		<th class="titulos_principales" colspan="6">&nbsp;</th>
		<th class="titulos_principales">' . _('Cant<br>N.Credito') . '</th>
		<th class="titulos_principales">' . _('Precio<br>N.Credito') . '</th>
		<th class="titulos_principales">' . _('$Total<br>N.Credito') . '</th>
		</tr>';
		$SQL = "SELECT c.categoryid, c.categorydescription, sum(k.qty) as qty, avg(k.standardcost) as standardcost, sum(k.qty*k.standardcost) as tstandardcost
			FROM  supptrans  f
				LEFT JOIN suppnotesorders n ON f.order_ = n.orderno
				LEFT JOIN suppnotesorderdetails d ON n.orderno = d.orderno
				LEFT JOIN stockmaster s on d.stkcode = s.stockid
				LEFT JOIN stockmoves k ON f.type = k.type and f.transno = k.transno and s.stockid = k.stockid
				LEFT JOIN stockcategory c on s.categoryid = c.categoryid
				LEFT JOIN ProdLine i ON c.prodLineId = i.Prodlineid
				LEFT JOIN ProdGroup g ON i.Prodgroupid = g.Prodgroupid
				LEFT JOIN  locations a on d.fromstkloc = a.loccode
				LEFT JOIN tags t on f.tagref  = t.tagref 
				LEFT JOIN legalbusinessunit l on t.legalid = l.legalid
				LEFT JOIN departments m on t.u_department = m.u_department
				LEFT JOIN areas e on t.areacode = e.areacode
				LEFT JOIN regions r on e.regioncode = r.regioncode
			WHERE f.type=33 and f.origtrandate BETWEEN '" . $fechaini . "' and '" . $fechafin . "'
				AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
				AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
				AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
				AND (a.loccode = '".$_POST['almacen']."' or '".$_POST['almacen']."'='0')
				AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
				AND (f.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
				AND (f.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')
				AND (g.Prodgroupid = '" . $_POST['xGrupo'] . "' or  '" . $_POST['xGrupo'] . "'='0')
				AND (i.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea']."'='0')
				AND (c.categoryid = '" . $_POST['xCategoria']. "' or '" . $_POST['xCategoria']."'='0')";
				if ($_POST['detalleproducto'] == 'Inventariado'){
					$SQL = $SQL  . " AND s.mbflag = 'B'";	
				}
				if ($_POST['detalleproducto'] == 'NOInventariado'){
					$SQL = $SQL  . " AND s.mbflag <> 'B'";	
				}
			
			
		$SQL = $SQL  . " GROUP BY c.categoryid, c.categorydescription ORDER BY c.categoryid, c.categorydescription";
		
		
		$ReportResult = DB_query($SQL,$db,'','',False,False); /*dont trap errors */
	
		if (DB_error_no($db) !=0) {
			$title = _('Reporte General de COMPRAS') . ' - ' . _('Reporte de Problema') ;
			prnMsg(_('Los detalles de Notas de Credito Full no se pudieron recuperar porque el SQL fallo') .  ' ' . DB_error_msg($db),'error');
			if ($debug==1){
				//echo "<br>".$SQL;
			}
			exit;
		}
		//echo $SQL;
		
		echo $headerDetalle;
		setlocale(LC_MONETARY, 'es_MX');
		$cantfactura = 0;
		$totalcostofactura = 0;
		while ($SlsAnalysis = DB_fetch_array($ReportResult,$db)){
			$codigo = $SlsAnalysis['categoryid'];
			$descripcion = $SlsAnalysis['categorydescription'];
			$cantfac = $SlsAnalysis['qty'];
			$preciofac = $SlsAnalysis['standardcost'];
			$costototalfac = $SlsAnalysis['tstandardcost'];
			
			echo "<tr>";
				echo "<td class='peque'>" . $codigo . "</td>";
				echo "<td class='peque'>" . $descripcion . "</td>";
				echo "<td class='pequenum' colspan='6'>&nbsp;</td>";
				echo "<td class='pequenum'>" . $cantfac . "</td>";
				echo "<td class='pequenum' nowrap>" . money_format('%(#6.2n', $preciofac) . "</td>";
				echo "<td class='pequenum' nowrap>" . money_format('%(#7.2n', ($costototalfac)) . "</td>";
			echo "</tr>";
			$cantfactura = $cantfactura + $cantfac;
			$totalcostofactura = $totalcostofactura + ($costototalfac);
		}
		echo "<tr>";
			echo "<td class='pie_derecha2' colspan='8'>" .  _('Total') . ": </td>";
			echo "<td class='pie_derecha2'>" . $cantfactura . "</td>";
			echo "<td class='pie_derecha2' nowrap>&nbsp;</td>";
			echo "<td class='pie_derecha2' nowrap>" . money_format('%(#7.2n', ($totalcostofactura)) . "</td>";
		echo "</tr>";
		$costogralnotacredito = $totalcostofactura;
		echo "<tr>";
			echo "<td class='pie_derecha2' colspan='8'>" .  _('Total') . ": </td>";
			echo "<td class='pie_derecha2'>&nbsp;</td>";
			echo "<td class='pie_derecha2' nowrap>&nbsp;</td>";
			echo "<td class='pie_derecha2' nowrap>" . money_format('%(#7.2n', ($costogralfacturado+$costogralnotacredito)) . "</td>";
		echo "</tr>";
		
	} elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Linea')  {
		$headerDetalle = '<tr>
		<th class="titulos_principales">' . _('Clave') . '</th>
		<th class="titulos_principales">' . _('Linea') . '</th>
		<th class="titulos_principales" colspan="6"><b>&nbsp;</th>
		<th class="titulos_principales">' . _('Cant<br>N.Credito') . '</th>
		<th class="titulos_principales">' . _('Precio<br>N.Credito') . '</th>
		<th class="titulos_principales">' . _('Total<br>N.Credito') . '</th>
		</tr>';
	    	$SQL = "SELECT i.Prodlineid, i.Description, sum(k.qty) as qty, avg(k.standardcost) as standardcost, sum(k.qty*k.standardcost) as tstandardcost
			FROM  supptrans  f
			LEFT JOIN suppnotesorders n ON f.order_ = n.orderno
			LEFT JOIN suppnotesorderdetails d ON n.orderno = d.orderno
			LEFT JOIN stockmaster s on d.stkcode = s.stockid
			LEFT JOIN stockmoves k ON f.type = k.type and f.transno = k.transno and s.stockid = k.stockid
			LEFT JOIN stockcategory c on s.categoryid = c.categoryid
			LEFT JOIN ProdLine i ON c.prodLineId = i.Prodlineid
			LEFT JOIN ProdGroup g ON i.Prodgroupid = g.Prodgroupid
			LEFT JOIN  locations a on d.fromstkloc = a.loccode
			LEFT JOIN tags t on f.tagref  = t.tagref 
			LEFT JOIN legalbusinessunit l on t.legalid = l.legalid
			LEFT JOIN departments m on t.u_department = m.u_department
			LEFT JOIN areas e on t.areacode = e.areacode
			LEFT JOIN regions r on e.regioncode = r.regioncode
			WHERE f.type=33 and f.origtrandate BETWEEN '" . $fechaini . "' and '" . $fechafin . "'
			AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
			AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			AND (a.loccode = '".$_POST['almacen']."' or '".$_POST['almacen']."'='0')
			AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			AND (f.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			AND (f.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')
			AND (g.Prodgroupid = '" . $_POST['xGrupo'] . "' or  '" . $_POST['xGrupo'] . "'='0')
			AND (i.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea']."'='0')
			AND (c.categoryid = '" . $_POST['xCategoria']. "' or '" . $_POST['xCategoria']."'='0')";
			if ($_POST['detalleproducto'] == 'Inventariado'){
				$SQL = $SQL  . " AND s.mbflag = 'B'";	
			}
			if ($_POST['detalleproducto'] == 'NOInventariado'){
				$SQL = $SQL  . " AND s.mbflag <> 'B'";	
			}
			
		$SQL = $SQL  . " GROUP BY i.Prodlineid, i.Description ORDER BY i.Prodlineid, i.Description";
		
		//echo $SQL;
		$ReportResult = DB_query($SQL,$db,'','',False,False); /*dont trap errors */
	
		if (DB_error_no($db) !=0) {
			$title = _('Reporte General de COMPRAS') . ' - ' . _('Reporte de Problema') ;
			prnMsg(_('Los detalles de Notas de Credito Full no se pudieron recuperar porque el SQL fallo') .  ' ' . DB_error_msg($db),'error');
			if ($debug==1){
				//echo "<br>".$SQL;
			}
			exit;
		}
		//echo $SQL;
		
		echo $headerDetalle;
		setlocale(LC_MONETARY, 'es_MX');
		$cantfactura = 0;
		$totalcostofactura = 0;
		while ($SlsAnalysis = DB_fetch_array($ReportResult,$db)){
			$codigo = $SlsAnalysis['Prodlineid'];
			$descripcion = $SlsAnalysis['Description'];
			$cantfac = $SlsAnalysis['qty'];
			$preciofac = $SlsAnalysis['standardcost'];
			$costototalfac = $SlsAnalysis['tstandardcost'];
			
			echo "<tr>";
				echo "<td class='peque'>" . $codigo . "</td>";
				echo "<td class='peque'>" . $descripcion . "</td>";
				echo "<td class='pequenum' colspan='6'>&nbsp;</td>";
				echo "<td class='pequenum'>" . $cantfac . "</td>";
				echo "<td class='pequenum' nowrap>" . money_format('%(#6.2n', $preciofac) . "</td>";
				echo "<td class='pequenum' nowrap>" . money_format('%(#7.2n', ($costototalfac)) . "</td>";
			echo "</tr>";
			$cantfactura = $cantfactura + $cantfac;
			$totalcostofactura = $totalcostofactura + ($costototalfac);
		}
		echo "<tr>";
			echo "<td class='pie_derecha2' colspan='8'>" .  _('Total') . ": </td>";
			echo "<td class='pie_derecha2'>" . $cantfactura . "</td>";
			echo "<td class='pie_derecha2' nowrap>&nbsp;</td>";
			echo "<td class='pie_derecha2' nowrap>" . money_format('%(#7.2n', ($totalcostofactura)) . "</td>";
		echo "</tr>";
		$costogralnotacredito = $totalcostofactura;
		echo "<tr>";
			echo "<td class='pie_derecha2' colspan='8'>" .  _('Total') . ": </td>";
			echo "<td class='pie_derecha2'>&nbsp;</td>";
			echo "<td class='pie_derecha2' nowrap>&nbsp;</td>";
			echo "<td class='pie_derecha2' nowrap>" . money_format('%(#7.2n', ($costogralfacturado+$costogralnotacredito)) . "</td>";
		echo "</tr>";
	} elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'proveedor')  {
		$headerDetalle = '<tr>
		<th><b>' . _('Clave<br>Proveedor') . '</b></th>
		<th><b>' . _('Proveedor') . '</b></th>
		<th colspan="8"><b>&nbsp;</b></th>
		<th><b>' . _('$Total<br>N.Credito') . '</b></th>
		</tr>';
	    	$SQL = "SELECT s.supplierid, s.suppname, sum(f.ovamount)  as tstandardcost
			FROM  supptrans  f
			LEFT JOIN suppliers s on f.supplierno  = s.supplierid
			LEFT JOIN tags t on f.tagref  = t.tagref 
			LEFT JOIN legalbusinessunit l on t.legalid = l.legalid
			LEFT JOIN departments m on t.u_department = m.u_department
			LEFT JOIN areas e on t.areacode = e.areacode
			LEFT JOIN regions r on e.regioncode = r.regioncode
			WHERE f.type IN (33,37)
			AND f.origtrandate BETWEEN '" . $fechaini . "' and '" . $fechafin . "'
			AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
			AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			AND (f.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			AND (f.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')";
		$SQL = $SQL  . " GROUP BY s.supplierid, s.suppname ORDER BY s.supplierid, s.suppname";
		
		//echo $SQL;
		$ReportResult = DB_query($SQL,$db,'','',False,False); /*dont trap errors */
	
		if (DB_error_no($db) !=0) {
			$title = _('Reporte General de COMPRAS') . ' - ' . _('Reporte de Problema') ;
			prnMsg(_('Los detalles de Notas de Credito Full no se pudieron recuperar porque el SQL fallo') .  ' ' . DB_error_msg($db),'error');
			if ($debug==1){
				//echo "<br>".$SQL;
			}
			exit;
		}
		//echo $SQL;
		
		echo $headerDetalle;
		setlocale(LC_MONETARY, 'es_MX');
		$cantfactura = 0;
		$totalcostofactura = 0;
		while ($SlsAnalysis = DB_fetch_array($ReportResult,$db)){
			$codigo = $SlsAnalysis['supplierid'];
			$descripcion = $SlsAnalysis['suppname'];
			$costototalfac = $SlsAnalysis['tstandardcost'];
			
			echo "<tr>";
				echo "<td class='peque'>" . $codigo . "</td>";
				echo "<td class='peque'>" . $descripcion . "</td>";
				echo "<td class='pequenum' colspan='8'>&nbsp;</td>";
				echo "<td class='pequenum' nowrap>" . money_format('%(#7.2n', ($costototalfac)) . "</td>";
			echo "</tr>";

			$totalcostofactura = $totalcostofactura + ($costototalfac);
		}
		echo "<tr>";
			echo "<td class='pie_derecha2' colspan='9'>" .  _('Total') . ": </td>";
			echo "<td class='pie_derecha2' nowrap>&nbsp;</td>";
			echo "<td class='pie_derecha2' nowrap>" . money_format('%(#7.2n', ($totalcostofactura)) . "</td>";
		echo "</tr>";
		$costogralnotacredito = $totalcostofactura;
		echo "<tr>";
			echo "<td class='pie_derecha2' colspan='8'>" .  _('Total') . ": </td>";
			echo "<td class='pie_derecha2'>&nbsp;</td>";
			echo "<td class='pie_derecha2' nowrap>&nbsp;</td>";
			echo "<td class='pie_derecha2' nowrap>" . money_format('%(#7.2n', ($costogralfacturado+$costogralnotacredito)) . "</td>";
		echo "</tr>";
		
	}	
	echo '</table>';
	
	echo '</form>';
			
}

// parte de codigo que no se ejecuta ya que se quito el boton de imprimir a PDF
elseif(isset($_POST['PrintPDF']))  // Envia a imprimir a PDF
{
	if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Region')  {
		$SQL = "SELECT r.regioncode, r.name,
			sum(d.quantityord) as quantityord,
			sum(case when month(p.orddate) = month(gr.deliverydate) then gr.qtyrecd else 0 end) as quantityrecd,
			sum(d.qtyinvoiced) as qtyinvoiced,
			avg(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100))) as avgunitprice,
			avg(d.stdcostunit) as avgstdcostunit,
			avg(d.actprice) as avgactprice,
			sum(d.quantityord*(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100)))) as ordcostototal,
			sum(d.quantityrecd*d.stdcostunit) as reccostototal,
			sum(d.qtyinvoiced*d.actprice) as faccostototal
			FROM  purchorders p
			left join purchorderdetails d on  p.orderno = d.orderno
			left join grns gr ON gr.podetailitem = d.podetailitem and gr.itemcode = d.itemcode and gr.qtyrecd <> 0
			left join stockmaster s on d.itemcode = s.stockid
			left join stockcategory c on s.categoryid = c.categoryid
			left join ProdLine i ON c.prodLineId = i.Prodlineid
			left join ProdGroup g ON i.Prodgroupid = g.Prodgroupid
			left join locations a on p.intostocklocation = a.loccode
			left join tags t on p.tagref  = t.tagref
			left join legalbusinessunit l on t.legalid = l.legalid
			left join departments m on t.u_department = m.u_department
			left join areas e on t.areacode = e.areacode
			left join regions r on e.regioncode = r.regioncode
			WHERE orddate between '" . $fechaini . "' and '" . $fechafin . "'
			AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
			AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			AND (p.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			AND (p.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')
			AND (g.Prodgroupid = '" . $_POST['xGrupo'] . "' or  '" . $_POST['xGrupo'] . "'='0')
			AND (i.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea']."'='0')
			and (c.categoryid = '" . $_POST['xCategoria']. "' or '" . $_POST['xCategoria']."'='0') $supplierWhereCond";
		if ($_POST['detalleproducto'] == 'Inventariado'){
			$SQL = $SQL  . " and s.mbflag = 'B'";
		}
		if ($_POST['detalleproducto'] == 'NOInventariado'){
			$SQL = $SQL  . " and s.mbflag <> 'B'";
		} elseif ($_POST['detalleproducto'] == 'SOLOPendientes'){
			$SQL = $SQL  . " and d.qtyinvoiced <> gr.qtyrecd";
		}
			
			
		$SQL = $SQL  . " GROUP BY r.regioncode
			ORDER BY r.name";
	
	}elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Area')  {
		$SQL = "SELECT e.areacode, e.areadescription,
			sum(d.quantityord) as quantityord,
			sum(case when month(p.orddate) = month(gr.deliverydate) then gr.qtyrecd else 0 end) as quantityrecd,
			sum(d.qtyinvoiced) as qtyinvoiced,
			avg(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100))) as avgunitprice,
			avg(d.stdcostunit) as avgstdcostunit,
			avg(d.actprice) as avgactprice,
			sum(d.quantityord*(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100)))) as ordcostototal,
			sum(d.quantityrecd*d.stdcostunit) as reccostototal,
			sum(d.qtyinvoiced*d.actprice) as faccostototal
			FROM  purchorders p
			left join purchorderdetails d on  p.orderno = d.orderno
			left join grns gr ON gr.podetailitem = d.podetailitem and gr.itemcode = d.itemcode and gr.qtyrecd <> 0
			left join stockmaster s on d.itemcode = s.stockid
			left join stockcategory c on s.categoryid = c.categoryid
			left join ProdLine i ON c.prodLineId = i.Prodlineid
			left join ProdGroup g ON i.Prodgroupid = g.Prodgroupid
			left join locations a on p.intostocklocation = a.loccode
			left join tags t on p.tagref  = t.tagref
			left join legalbusinessunit l on t.legalid = l.legalid
			left join departments m on t.u_department = m.u_department
			left join areas e on t.areacode = e.areacode
			left join regions r on e.regioncode = r.regioncode
			WHERE orddate between '" . $fechaini . "' and '" . $fechafin . "'
			AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
			AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			AND (p.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			AND (p.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')
			AND (g.Prodgroupid = '" . $_POST['xGrupo'] . "' or  '" . $_POST['xGrupo'] . "'='0')
			AND (i.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea']."'='0')
			and (c.categoryid = '" . $_POST['xCategoria']. "' or '" . $_POST['xCategoria']."'='0') $supplierWhereCond";
		if ($_POST['detalleproducto'] == 'Inventariado'){
			$SQL = $SQL  . " and s.mbflag = 'B'";
		}
		if ($_POST['detalleproducto'] == 'NOInventariado'){
			$SQL = $SQL  . " and s.mbflag <> 'B'";
		} elseif ($_POST['detalleproducto'] == 'SOLOPendientes'){
			$SQL = $SQL  . " and d.qtyinvoiced <> gr.qtyrecd";
		}
			
			
		$SQL = $SQL  . " GROUP BY e.areacode
			ORDER BY e.areadescription";
	
	}elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'UnidadNegocio')  {
		$SQL = "SELECT t.tagref, t.tagdescription,
			sum(d.quantityord) as quantityord,
			sum(case when month(p.orddate) = month(gr.deliverydate) then gr.qtyrecd else 0 end) as quantityrecd,
			sum(d.qtyinvoiced) as qtyinvoiced,
			avg(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100))) as avgunitprice,
			avg(d.stdcostunit) as avgstdcostunit,
			avg(d.actprice) as avgactprice,
			sum(d.quantityord*(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100)))) as ordcostototal,
			sum(d.quantityrecd*d.stdcostunit) as reccostototal,
			sum(d.qtyinvoiced*d.actprice) as faccostototal
			FROM  purchorders p
			left join purchorderdetails d on  p.orderno = d.orderno
			left join grns gr ON gr.podetailitem = d.podetailitem and gr.itemcode = d.itemcode and gr.qtyrecd <> 0
			left join stockmaster s on d.itemcode = s.stockid
			left join stockcategory c on s.categoryid = c.categoryid
			left join ProdLine i ON c.prodLineId = i.Prodlineid
			left join ProdGroup g ON i.Prodgroupid = g.Prodgroupid
			left join locations a on p.intostocklocation = a.loccode
			left join tags t on p.tagref  = t.tagref
			left join legalbusinessunit l on t.legalid = l.legalid
			left join departments m on t.u_department = m.u_department
			left join areas e on t.areacode = e.areacode
			left join regions r on e.regioncode = r.regioncode
			WHERE orddate between '" . $fechaini . "' and '" . $fechafin . "'
			AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
			AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			AND (p.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			AND (p.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')
			AND (g.Prodgroupid = '" . $_POST['xGrupo'] . "' or  '" . $_POST['xGrupo'] . "'='0')
			AND (i.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea']."'='0')
			and (c.categoryid = '" . $_POST['xCategoria']. "' or '" . $_POST['xCategoria']."'='0') $supplierWhereCond";
		if ($_POST['detalleproducto'] == 'Inventariado'){
			$SQL = $SQL  . " and s.mbflag = 'B'";
		}
		if ($_POST['detalleproducto'] == 'NOInventariado'){
			$SQL = $SQL  . " and s.mbflag <> 'B'";
		} elseif ($_POST['detalleproducto'] == 'SOLOPendientes'){
			$SQL = $SQL  . " and d.qtyinvoiced <> gr.qtyrecd";
		}
			
			
		$SQL = $SQL  . " GROUP BY t.tagref
			ORDER BY t.tagdescription";
	
	}
	elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Producto') {
		$SQL = "SELECT  p.orderno,
			p.supplierno,
			p.orddate,
			p.intostocklocation,
			p.tagref,
			d.podetailitem,
			d.itemcode,
			d.itemdescription,
			SUM(d.qtyinvoiced) as qtyinvoiced,
			avg(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100))) as unitprice,
			avg(d.actprice) as actprice,
			avg(d.stdcostunit) as stdcostunit,
			sum(d.quantityord) as quantityord,
			sum(d.quantityrecd) as quantityrecd,
			/* ifnull((Select SUM(qtyrecd)
					FROM grns
					WHERE grns.podetailitem = d.podetailitem
					and grns.itemcode = d.itemcode
					and grns.qtyrecd <> 0
					and month(p.orddate) = month(grns.deliverydate)),0) as quantityrecd,*/
			avg((d.discountpercent1/100)) as discountpercent1,
			avg((d.discountpercent2/100)) as discountpercent2,
			avg((d.discountpercent3/100)) as discountpercent3
			from  purchorders p
			left join purchorderdetails d on  p.orderno = d.orderno
			left join stockmaster s on d.itemcode = s.stockid
			left join stockcategory c on s.categoryid = c.categoryid
			left join ProdLine i ON c.prodLineId = i.Prodlineid
			left join ProdGroup g ON i.Prodgroupid = g.Prodgroupid
			left join locations a on p.intostocklocation = a.loccode
			left join tags t on p.tagref  = t.tagref
			left join legalbusinessunit l on t.legalid = l.legalid
			left join departments m on t.u_department = m.u_department
			left join areas e on t.areacode = e.areacode
			left join regions r on e.regioncode = r.regioncode
			where orddate between '" . $fechaini . "' and '" . $fechafin . "'
			AND status <> 'Cancelled'
			AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
			AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			and (a.loccode = '".$_POST['almacen']."' or '".$_POST['almacen']."'='0')
			AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			AND (p.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			AND (p.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')
			AND (g.Prodgroupid = '" . $_POST['xGrupo'] . "' or  '" . $_POST['xGrupo'] . "'='0')
			AND (i.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea']."'='0')
			and (c.categoryid = '" . $_POST['xCategoria']. "' or '" . $_POST['xCategoria']."'='0') $supplierWhereCond";
		if ($_POST['detalleproducto'] == 'Inventariado'){
			$SQL = $SQL  . " and s.mbflag = 'B'";
		} elseif ($_POST['detalleproducto'] == 'NOInventariado'){
			$SQL = $SQL  . " and s.mbflag <> 'B'";
		} elseif ($_POST['detalleproducto'] == 'SOLOPendientes'){
			$SQL = $SQL  . " and d.qtyinvoiced <> gr.qtyrecd";
		}
			
		$SQL = $SQL  . " Group By p.orderno, d.itemcode
							 order by p.orderno";
			
		//echo $SQL;
		/* ES DETALLADO POR DOCUMENTO */
	} //elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Factura')  {
	//}
	elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Categoria')  {
		$SQL = "SELECT c.categoryid, c.categorydescription,
			sum(d.quantityord) as quantityord,
			sum(case when month(p.orddate) = month(gr.deliverydate) then gr.qtyrecd else 0 end) as quantityrecd,
			sum(d.qtyinvoiced) as qtyinvoiced,
			avg(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100))) as avgunitprice,
			avg(d.stdcostunit) as avgstdcostunit,
			avg(d.actprice) as avgactprice,
			sum(d.quantityord*(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100)))) as ordcostototal,
			sum(d.quantityrecd*d.stdcostunit) as reccostototal,
			sum(d.qtyinvoiced*d.actprice) as faccostototal
			FROM  purchorders p
			left join purchorderdetails d on  p.orderno = d.orderno
			left join grns gr ON gr.podetailitem = d.podetailitem and gr.itemcode = d.itemcode and gr.qtyrecd <> 0
			left join stockmaster s on d.itemcode = s.stockid
			left join stockcategory c on s.categoryid = c.categoryid
			left join ProdLine i ON c.prodLineId = i.Prodlineid
			left join ProdGroup g ON i.Prodgroupid = g.Prodgroupid
			left join locations a on p.intostocklocation = a.loccode
			left join tags t on p.tagref  = t.tagref
			left join legalbusinessunit l on t.legalid = l.legalid
			left join departments m on t.u_department = m.u_department
			left join areas e on t.areacode = e.areacode
			left join regions r on e.regioncode = r.regioncode
			WHERE orddate between '" . $fechaini . "' and '" . $fechafin . "'
			AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
			AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			AND (p.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			AND (p.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')
			AND (g.Prodgroupid = '" . $_POST['xGrupo'] . "' or  '" . $_POST['xGrupo'] . "'='0')
			AND (i.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea']."'='0')
			and (c.categoryid = '" . $_POST['xCategoria']. "' or '" . $_POST['xCategoria']."'='0') $supplierWhereCond";
		if ($_POST['detalleproducto'] == 'Inventariado'){
			$SQL = $SQL  . " and s.mbflag = 'B'";
		}
		if ($_POST['detalleproducto'] == 'NOInventariado'){
			$SQL = $SQL  . " and s.mbflag <> 'B'";
		} elseif ($_POST['detalleproducto'] == 'SOLOPendientes'){
			$SQL = $SQL  . " and d.qtyinvoiced <> gr.qtyrecd";
		}
			
			
		$SQL = $SQL  . " GROUP BY c.categoryid, c.categorydescription
			ORDER BY c.categoryid, c.categorydescription";
	} elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'ContableVts')  {
	} elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'ContableCosto')  {
	} elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Linea')  {
		$SQL = "SELECT i.Prodlineid, i.Description,
			sum(d.quantityord) as quantityord,
			sum(case when month(p.orddate) = month(gr.deliverydate) then gr.qtyrecd else 0 end) as quantityrecd,
			sum(d.qtyinvoiced) as qtyinvoiced,
			avg(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100))) as avgunitprice,
			avg(d.stdcostunit) as avgstdcostunit,
			avg(d.actprice) as avgactprice,
			sum(d.quantityord*(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100)))) as ordcostototal,
			sum(d.quantityrecd*d.stdcostunit) as reccostototal,
			sum(d.qtyinvoiced*d.actprice) as faccostototal
			FROM  purchorders p
			left join purchorderdetails d on  p.orderno = d.orderno
			left join grns gr ON gr.podetailitem = d.podetailitem and gr.itemcode = d.itemcode and gr.qtyrecd <> 0
			left join stockmaster s on d.itemcode = s.stockid
			left join stockcategory c on s.categoryid = c.categoryid
			left join ProdLine i ON c.prodLineId = i.Prodlineid
			left join ProdGroup g ON i.Prodgroupid = g.Prodgroupid
			left join locations a on p.intostocklocation = a.loccode
			left join tags t on p.tagref  = t.tagref
			left join legalbusinessunit l on t.legalid = l.legalid
			left join departments m on t.u_department = m.u_department
			left join areas e on t.areacode = e.areacode
			left join regions r on e.regioncode = r.regioncode
			WHERE orddate between '" . $fechaini . "' and '" . $fechafin . "'
			AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
			AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			AND (p.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			AND (p.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')
			AND (g.Prodgroupid = '" . $_POST['xGrupo'] . "' or  '" . $_POST['xGrupo'] . "'='0')
			AND (i.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea']."'='0')
			and (c.categoryid = '" . $_POST['xCategoria']. "' or '" . $_POST['xCategoria']."'='0') $supplierWhereCond";
			
			if ($_POST['detalleproducto'] == 'Inventariado'){
				$SQL = $SQL  . " and s.mbflag = 'B'";
			}
			if ($_POST['detalleproducto'] == 'NOInventariado'){
				$SQL = $SQL  . " and s.mbflag <> 'B'";
			} elseif ($_POST['detalleproducto'] == 'SOLOPendientes'){
				$SQL = $SQL  . " and d.qtyinvoiced <> gr.qtyrecd";
			}
			
			
		$SQL = $SQL  . " GROUP BY i.Prodlineid, i.Description
			ORDER BY i.Prodlineid, i.Description";
	} elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'proveedor')  {
		$SQL = "SELECT p.supplierno, pr.suppname,
			sum(d.quantityord) as quantityord,
			sum(case when month(p.orddate) = month(gr.deliverydate) then gr.qtyrecd else 0 end) as quantityrecd,
			sum(d.qtyinvoiced) as qtyinvoiced,
			avg(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100))) as avgunitprice,
			avg(d.stdcostunit) as avgstdcostunit,
			avg(d.actprice) as avgactprice,
			sum(d.quantityord*(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100)))) as ordcostototal,
			sum(d.quantityrecd*d.stdcostunit) as reccostototal,
			sum(d.qtyinvoiced*d.actprice) as faccostototal
			FROM  purchorders p
			left join suppliers pr ON p.supplierno = pr.supplierid
			left join purchorderdetails d on  p.orderno = d.orderno
			left join grns gr ON gr.podetailitem = d.podetailitem and gr.itemcode = d.itemcode and gr.qtyrecd <> 0
			left join stockmaster s on d.itemcode = s.stockid
			left join stockcategory c on s.categoryid = c.categoryid
			left join ProdLine i ON c.prodLineId = i.Prodlineid
			left join ProdGroup g ON i.Prodgroupid = g.Prodgroupid
			left join locations a on p.intostocklocation = a.loccode
			left join tags t on p.tagref  = t.tagref
			left join legalbusinessunit l on t.legalid = l.legalid
			left join departments m on t.u_department = m.u_department
			left join areas e on t.areacode = e.areacode
			left join regions r on e.regioncode = r.regioncode
			WHERE orddate between '" . $fechaini . "' and '" . $fechafin . "'
			AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
			AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			AND (p.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			AND (p.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')
			AND (g.Prodgroupid = '" . $_POST['xGrupo'] . "' or  '" . $_POST['xGrupo'] . "'='0')
			AND (i.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea']."'='0')
			and (c.categoryid = '" . $_POST['xCategoria']. "' or '" . $_POST['xCategoria']."'='0') $supplierWhereCond";
		if ($_POST['detalleproducto'] == 'Inventariado'){
			$SQL = $SQL  . " and s.mbflag = 'B'";
		}
		if ($_POST['detalleproducto'] == 'NOInventariado'){
			$SQL = $SQL  . " and s.mbflag <> 'B'";
		} elseif ($_POST['detalleproducto'] == 'SOLOPendientes'){
			$SQL = $SQL  . " and d.qtyinvoiced <> gr.qtyrecd";
		}
			
			
		$SQL = $SQL  . " GROUP BY p.supplierno, pr.suppname
			ORDER BY p.supplierno, pr.suppname";
		//echo $SQL;
	}elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Factura')  {
		//echo "entra";
		$SQL = "SELECT p.supplierno, pr.suppname,
			sum(d.quantityord) as quantityord,
			sum(case when month(p.orddate) = month(gr.deliverydate) then gr.qtyrecd else 0 end) as quantityrecd,
			sum(d.qtyinvoiced) as qtyinvoiced,
			avg(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100))) as avgunitprice,
			avg(d.stdcostunit) as avgstdcostunit,
			avg(d.actprice) as avgactprice,
			sum(d.quantityord*(((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100))) - (((d.unitprice - (d.unitprice*(d.discountpercent1/100))) - ((d.unitprice - (d.unitprice*(d.discountpercent1/100))) * (d.discountpercent2/100)))*(d.discountpercent3/100)))) as ordcostototal,
			sum(supptransdetails.qty*d.stdcostunit) as reccostototal,
			sum(supptransdetails.qty*supptransdetails.price) as faccostototal,
			supptrans.suppreference
			FROM  purchorders p
			left join suppliers pr ON p.supplierno = pr.supplierid
			left join purchorderdetails d on  p.orderno = d.orderno
			left join grns gr ON gr.podetailitem = d.podetailitem and gr.itemcode = d.itemcode and gr.qtyrecd <> 0
			left join supptransdetails ON p.orderno = supptransdetails.orderno and supptransdetails.grns=gr.grnno and supptransdetails.stockid=d.itemcode
			left join supptrans ON  supptransdetails.supptransid=supptrans.id
			left join stockmaster s on d.itemcode = s.stockid
			left join stockcategory c on s.categoryid = c.categoryid
			left join ProdLine i ON c.prodLineId = i.Prodlineid
			left join ProdGroup g ON i.Prodgroupid = g.Prodgroupid
			left join locations a on p.intostocklocation = a.loccode
			left join tags t on p.tagref  = t.tagref
			left join legalbusinessunit l on t.legalid = l.legalid
			left join departments m on t.u_department = m.u_department
			left join areas e on t.areacode = e.areacode
			left join regions r on e.regioncode = r.regioncode
			WHERE orddate between '" . $fechaini . "' and '" . $fechafin . "'
			AND (t.legalid = ".$_POST['legalid']." or ".$_POST['legalid']."=0)
			AND (e.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			AND (e.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			AND (m.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			AND (p.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			AND (p.supplierno = '" . $_POST['clavecliente']."' or '".$_POST['clavecliente']."'='*')
			AND (g.Prodgroupid = '" . $_POST['xGrupo'] . "' or  '" . $_POST['xGrupo'] . "'='0')
			AND (i.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea']."'='0')
			and (c.categoryid = '" . $_POST['xCategoria']. "' or '" . $_POST['xCategoria']."'='0') $supplierWhereCond";
		if ($_POST['detalleproducto'] == 'Inventariado'){
			$SQL = $SQL  . " and s.mbflag = 'B'";
		}
		if ($_POST['detalleproducto'] == 'NOInventariado'){
			$SQL = $SQL  . " and s.mbflag <> 'B'";
		} elseif ($_POST['detalleproducto'] == 'SOLOPendientes'){
			$SQL = $SQL  . " and d.qtyinvoiced <> gr.qtyrecd";
		}
			
			
		$SQL = $SQL  . " GROUP BY p.supplierno, pr.suppname,supptrans.suppreference
			ORDER BY p.supplierno, pr.suppname,supptrans.suppreference";
		//echo $SQL;
	}
	
	//echo "<pre>".$SQL;
	//if ($_SESSION['UserID']=="desarrollo"){
	//echo "<pre>".$SQL;
		//exit;
		//}
		$ReportResult = DB_query($SQL,$db,'','',False,False); /*dont trap errors */
	
		if (DB_error_no($db) !=0) {
			$title = _('Reporte General de COMPRAS') . ' - ' . _('Reporte de Problema') ;
			prnMsg(_('Los detalles de ventas no se pudieron recuperar porque el SQL fallo') .  ' ' . DB_error_msg($db),'error');
			if ($debug==1){
				//echo "<br>".$SQL;
			}
			exit;
		}
		$DoctoTotPagos = 0;
		$VenceTotPagos = 0;
		$ProvTotPagos = 0;
		$RegionTotPagos = 0;
		$TotPagos = 0;
		
		$_SESSION["GeneralCompras"]= array();
		$_SESSION["GeneralCompras"]["complementos"]= array();
		$_SESSION["GeneralCompras"]["encabezado"]= array();
		$_SESSION["GeneralCompras"]["detalle"]= array();
					
		$_SESSION["GeneralCompras"]["complementos"][]= array("fechaini"=>$fechaini, 
					"fechafin"=>$fechafin);
			
	
		if ($_POST['DetailedReport'] == 'Producto') {
			$headerDetalle = '<tr>
			<th class="titulos_principales"' . _('Clave') . '</th>
			<th class="titulos_principales"' . _('Producto') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Ord') . '</th>
			<th class="titulos_principales"' . _('Precio<br>OCompra') . '</th>
			<th class="titulos_principales"' . _('$Total<br>OCompra') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Rec') . '</th>
			<th class="titulos_principales"' . _('Precio<br>Recepcion') . '</th>
			<th class="titulos_principales"' . _('$Total<br>Recepcion') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Fact') . '</th>
			<th class="titulos_principales"' . _('Precio<br>Factura') . '</th>
			<th class="titulos_principales"' . _('$Total<br>Factura') . '</th>
			</tr>';
	
		} elseif ($_POST['DetailedReport'] == 'Categoria') {
			$headerDetalle = '<tr>
			<th class="titulos_principales"' . _('Clave') . '</th>
			<th class="titulos_principales"' . _('Categoria') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Ord') . '</th>
			<th class="titulos_principales"' . _('Precio<br>OCompra') . '</th>
			<th class="titulos_principales"' . _('$Total<br>OCompra') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Rec') . '</th>
			<th class="titulos_principales"' . _('Precio<br>Recepcion') . '</th>
			<th class="titulos_principales"' . _('$Total<br>Recepcion') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Fact') . '</th>
			<th class="titulos_principales"' . _('Precio<br>Factura') . '</th>
			<th class="titulos_principales"' . _('$Total<br>Factura') . '</th>
			</tr>';
		} elseif ($_POST['DetailedReport'] == 'ContableVts') {
		} elseif ($_POST['DetailedReport'] == 'ContableCosto') {
		} elseif ($_POST['DetailedReport'] == 'Linea') {
			$headerDetalle = '<tr>
			<th class="titulos_principales"' . _('Clave') . '</th>
			<th class="titulos_principales"' . _('Linea') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Ord') . '</th>
			<th class="titulos_principales"' . _('Precio<br>OCompra') . '</th>
			<th class="titulos_principales"' . _('$Total<br>OCompra') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Rec') . '</th>
			<th class="titulos_principales"' . _('Precio<br>Recepcion') . '</th>
			<th class="titulos_principales"' . _('$Total<br>Recepcion') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Fact') . '</th>
			<th class="titulos_principales"' . _('Precio<br>Factura') . '</th>
			<th class="titulos_principales"' . _('$Total<br>Factura') . '</th>
			</tr>';
		} elseif ($_POST['DetailedReport'] == 'proveedor') {
			$headerDetalle = '<tr>
			<th class="titulos_principales"' . _('Codigo<br>Proveedor') . '</th>
			<th class="titulos_principales"' . _('Proveedor') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Ord') . '</th>
			<th class="titulos_principales"' . _('Precio<br>OCompra') . '</th>
			<th class="titulos_principales"' . _('$Total<br>OCompra') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Rec') . '</th>
			<th class="titulos_principales"' . _('Precio<br>Recepcion') . '</th>
			<th class="titulos_principales"' . _('$Total<br>Recepcion') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Fact') . '</th>
			<th class="titulos_principales"' . _('Precio<br>Factura') . '</th>
			<th class="titulos_principales"' . _('$Total<br>Factura') . '</th>
			</tr>';
		}elseif ($_POST['DetailedReport'] == 'Factura') {
			$headerDetalle = '<tr>
			<th class="titulos_principales"' . _('Factura') . '</th>
			<th class="titulos_principales"' . _('Proveedor') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Ord') . '</th>
			<th class="titulos_principales"' . _('Precio<br>OCompra') . '</th>
			<th class="titulos_principales"' . _('$Total<br>OCompra') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Rec') . '</th>
			<th class="titulos_principales"' . _('Precio<br>Recepcion') . '</th>
			<th class="titulos_principales"' . _('$Total<br>Recepcion') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Fact') . '</th>
			<th class="titulos_principales"' . _('Precio<br>Factura') . '</th>
			<th class="titulos_principales"' . _('$Total<br>Factura') . '</th>
			</tr>';
		}elseif ($_POST['DetailedReport'] == 'Region') {
			$headerDetalle = '<tr>
			<th class="titulos_principales"' . _('Codigo<br>Region') . '</th>
			<th class="titulos_principales"' . _('Region') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Ord') . '</th>
			<th class="titulos_principales"' . _('Precio<br>OCompra') . '</th>
			<th class="titulos_principales"' . _('$Total<br>OCompra') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Rec') . '</th>
			<th class="titulos_principales"' . _('Precio<br>Recepcion') . '</th>
			<th class="titulos_principales"' . _('$Total<br>Recepcion') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Fact') . '</th>
			<th class="titulos_principales"' . _('Precio<br>Factura') . '</th>
			<th class="titulos_principales"' . _('$Total<br>Factura') . '</th>
			</tr>';
		}elseif ($_POST['DetailedReport'] == 'Area') {
			$headerDetalle = '<tr>
			<th class="titulos_principales"' . _('Codigo<br>Area') . '</th>
			<th class="titulos_principales"' . _('Area') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Ord') . '</th>
			<th class="titulos_principales"' . _('Precio<br>OCompra') . '</th>
			<th class="titulos_principales"' . _('$Total<br>OCompra') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Rec') . '</th>
			<th class="titulos_principales"' . _('Precio<br>Recepcion') . '</th>
			<th class="titulos_principales"' . _('$Total<br>Recepcion') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Fact') . '</th>
			<th class="titulos_principales"' . _('Precio<br>Factura') . '</th>
			<th class="titulos_principales"' . _('$Total<br>Factura') . '</th>
			</tr>';
		}elseif ($_POST['DetailedReport'] == 'UnidadNegocio') {
			$headerDetalle = '<tr>
			<th class="titulos_principales"' . _('Codigo<br>Unid.Neg') . '</th>
			<th class="titulos_principales"' . _('Unid.Negocios') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Ord') . '</th>
			<th class="titulos_principales"' . _('Precio<br>OCompra') . '</th>
			<th class="titulos_principales"' . _('$Total<br>OCompra') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Rec') . '</th>
			<th class="titulos_principales"' . _('Precio<br>Recepcion') . '</th>
			<th class="titulos_principales"' . _('$Total<br>Recepcion') . '</th>
			<th class="titulos_principales"' . _('Cant<br>Fact') . '</th>
			<th class="titulos_principales"' . _('Precio<br>Factura') . '</th>
			<th class="titulos_principales"' . _('$Total<br>Factura') . '</th>
			</tr>';
		}
		
		
		$cadena= str_replace("</tr>", "", str_replace("<tr>", "", $headerDetalle));
		$cadena= str_replace("</b></th>", "", str_replace("<th><b>", "|", $cadena));
		$cadena=  str_replace("<br>", " ", $cadena);
		$_SESSION["GeneralCompras"]["encabezado"]= explode("|", $cadena);
	
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
	/*
		$htmlCalificaDocto = "";
		$SQL = "SELECT  idstatus, accountstatus from accountstatus order by accountstatus";
	
		$ErrMsg = _('No transactions were returned by the SQL because');
		$TransResult = DB_query($SQL,$db,$ErrMsg);
	
		$htmlCalificaDocto = $htmlCalificaDocto. "<option selected value='0'>califique...</option>";
		while ($myrow=DB_fetch_array($TransResult)) {
			if ($myrow['idstatus'] == $_POST['estatusDocumento']){
				$htmlCalificaDocto = $htmlCalificaDocto. "<option selected value=" . $myrow['idstatus'] . ">" . $myrow['accountstatus'] . "</option>";
			}else{
				$htmlCalificaDocto = $htmlCalificaDocto. "<option value=" . $myrow['idstatus'] . ">" . $myrow['accountstatus'] . "</option>";
			}
		}
		$htmlCalificaDocto = $htmlCalificaDocto. "</select>"; */
		/************************************/
	
		$AnteriorLegalName = "";
		$AnteriorRegionName = "";
		$AnteriorAreaDesc = "";
		$AnteriorDepartamento = "";
		$AnteriorUnidadNegocio = "";
		$AnteriorOrdenCompra = "";
	
	
		$AnteriorCliente = "";
		$AnteriorFactura = "";
	
		$totalQuantity = 0;
		$totalAmount = 0;
		$totalIVA = 0;
		$totalCost = 0;
		$totalCostAvg = 0;
		$totalIVACompra =0;
	
		$cantordencompra = 0;
		$cantrecepcion = 0;
		$cantfactura = 0;
		$totalcostoordencompra = 0;
		$totalcostorecepcion = 0;
		$totalcostofactura = 0 ;
	
		//TOTALES POR ORDEN DE COMPRA
		$OCcantordencompra = 0;
		$OCcantrecepcion = 0;
		$OCcantfactura = 0;
		$OCtotalcostoordencompra = 0;
		$OCtotalcostorecepcion = 0;
		$OCtotalcostofactura = 0 ;
	
	
		$FtotalQuantity = 0;
		$FtotalAmount = 0;
		$FtotalIVA = 0;
		$FtotalCost = 0;
		$FtotalCostAvg = 0;
		$FtotalIVACompra=0;
	
		$GtotalQuantity = 0;
		$GtotalAmount = 0;
		$GtotalIVA = 0;
		$GtotalCost = 0;
		$GtotalCostAvg = 0;
		$GtotalIVACompra =0;
	
		setlocale(LC_MONETARY, 'es_MX');
		while ($SlsAnalysis = DB_fetch_array($ReportResult,$db)){
			/*
			if ($_POST['DetailedReport'] == 'Producto') {
				if ($AnteriorOrdenCompra != $SlsAnalysis['orderno']){
					if ($primeraEntrada > 1){
						echo "<tr style='background-color:#F0F0F0'>";
						echo "<td style='text-align:right;' class='peque' colspan='2'>OC: " . $AnteriorOrdenCompra . "</td>";
						echo "<td class='pequenum'>" .$OCcantordencompra . "</td>";
						echo "<td class='pequenum' nowrap>&nbsp;</td>";
						echo "<td class='pequenum' nowrap>" . money_format('%(#7.2n', ($OCtotalcostoordencompra)) . "</td>";
						echo "<td class='pequenum'>" . $OCcantrecepcion . "</td>";
						echo "<td class='pequenum' nowrap>&nbsp;</td>";
						echo "<td class='pequenum' nowrap>" . money_format('%(#7.2n', ($OCtotalcostorecepcion)) . "</td>";
						echo "<td class='pequenum'>" . $OCcantfactura . "</td>";
						echo "<td class='pequenum' nowrap>&nbsp;</td>";
						echo "<td class='pequenum' nowrap>" . money_format('%(#7.2n', ($OCtotalcostofactura)) . "</td>";
						echo "</tr>";
					}
					//TOTALES POR ORDEN DE COMPRA
					$OCcantordencompra = 0;
					$OCcantrecepcion = 0;
					$OCcantfactura = 0;
					$OCtotalcostoordencompra = 0;
					$OCtotalcostorecepcion = 0;
					$OCtotalcostofactura = 0 ;
					$AnteriorOrdenCompra = $SlsAnalysis['orderno'];
				}
			}
			*/
			$primeraEntrada = $primeraEntrada + 1;
	
			//$preciocompra = $SlsAnalysis['unitprice'] - ($SlsAnalysis['unitprice']*$SlsAnalysis['discountpercent1']);
			//$preciocompra = $preciocompra  - ($SlsAnalysis['unitprice']*$SlsAnalysis['discountpercent2']);
			//$preciocompra = $preciocompra  - ($SlsAnalysis['unitprice']*$SlsAnalysis['discountpercent3']);
	
			if ($_POST['DetailedReport'] == 'Producto') {
				$codigo = $SlsAnalysis['itemcode'];
				//ES SOLO PARA INDICAR SI EL PRODUCTO LLEVA DESCUENTO
				if (($SlsAnalysis['discountpercent1']!=0) or ($SlsAnalysis['discountpercent2']!=0) or ($SlsAnalysis['discountpercent3']!=0)){
					$asterisco = 1;
				}else{
					$asterisco = 0;
				}
				$descripcion = $SlsAnalysis['itemdescription'];
				$cantorden = $SlsAnalysis['quantityord'];
				$preciocompra = $SlsAnalysis['unitprice'] - ($SlsAnalysis['unitprice']*$SlsAnalysis['discountpercent1']);
				$preciocompra = $preciocompra  - ($SlsAnalysis['unitprice']*$SlsAnalysis['discountpercent2']);
				$preciocompra = $preciocompra  - ($SlsAnalysis['unitprice']*$SlsAnalysis['discountpercent3']);
					
				$cantrec = $SlsAnalysis['quantityrecd'];
				$preciorec = $SlsAnalysis['stdcostunit'];
					
				$cantfac = $SlsAnalysis['qtyinvoiced'];
				$preciofac = $SlsAnalysis['actprice'];
					
				$costototalord = $cantorden * $preciocompra;
				$costototalrec = $cantrec * $preciorec;
				$costototalfac = $cantfac*$preciofac;
					
			} elseif ($_POST['DetailedReport'] == 'Categoria') {
				$codigo = $SlsAnalysis['categoryid'];
				//ES SOLO PARA INDICAR SI EL PRODUCTO LLEVA DESCUENTO
				$asterisco = 0;
				$descripcion = $SlsAnalysis['categorydescription'];
				$cantorden = $SlsAnalysis['quantityord'];
				$preciocompra = $SlsAnalysis['avgunitprice'];
					
				$cantrec = $SlsAnalysis['quantityrecd'];
				$preciorec = $SlsAnalysis['avgstdcostunit'];
					
				$cantfac = $SlsAnalysis['qtyinvoiced'];
				$preciofac = $SlsAnalysis['avgactprice'];
					
				$costototalord = $SlsAnalysis['ordcostototal'];
				$costototalrec = $SlsAnalysis['reccostototal'];
				$costototalfac = $SlsAnalysis['faccostototal'];
			}elseif ($_POST['DetailedReport'] == 'Linea') {
				$codigo = $SlsAnalysis['Prodlineid'];
				//ES SOLO PARA INDICAR SI EL PRODUCTO LLEVA DESCUENTO
				$asterisco = 0;
				$descripcion = $SlsAnalysis['Description'];
				$cantorden = $SlsAnalysis['quantityord'];
				$preciocompra = $SlsAnalysis['avgunitprice'];
					
				$cantrec = $SlsAnalysis['quantityrecd'];
				$preciorec = $SlsAnalysis['avgstdcostunit'];
					
				$cantfac = $SlsAnalysis['qtyinvoiced'];
				$preciofac = $SlsAnalysis['avgactprice'];
					
				$costototalord = $SlsAnalysis['ordcostototal'];
				$costototalrec = $SlsAnalysis['reccostototal'];
				$costototalfac = $SlsAnalysis['faccostototal'];
			}elseif ($_POST['DetailedReport'] == 'proveedor') {
				$codigo = $SlsAnalysis['supplierno'];
				//ES SOLO PARA INDICAR SI EL PRODUCTO LLEVA DESCUENTO
				$asterisco = 0;
				$descripcion = $SlsAnalysis['suppname'];
				$cantorden = $SlsAnalysis['quantityord'];
				$preciocompra = $SlsAnalysis['avgunitprice'];
					
				$cantrec = $SlsAnalysis['quantityrecd'];
				$preciorec = $SlsAnalysis['avgstdcostunit'];
					
				$cantfac = $SlsAnalysis['qtyinvoiced'];
				$preciofac = $SlsAnalysis['avgactprice'];
					
				$costototalord = $SlsAnalysis['ordcostototal'];
				$costototalrec = $SlsAnalysis['reccostototal'];
				$costototalfac = $SlsAnalysis['faccostototal'];
			}elseif ($_POST['DetailedReport'] == 'Factura') {
				$codigo = $SlsAnalysis['suppreference'];
				//ES SOLO PARA INDICAR SI EL PRODUCTO LLEVA DESCUENTO
				$asterisco = 0;
				$descripcion = $SlsAnalysis['supplierno'].' '.$SlsAnalysis['suppname'];
				$cantorden = $SlsAnalysis['quantityord'];
				$preciocompra = $SlsAnalysis['avgunitprice'];
					
				$cantrec = $SlsAnalysis['quantityrecd'];
				$preciorec = $SlsAnalysis['avgstdcostunit'];
					
				$cantfac = $SlsAnalysis['qtyinvoiced'];
				$preciofac = $SlsAnalysis['avgactprice'];
					
				$costototalord = $SlsAnalysis['ordcostototal'];
				$costototalrec = $SlsAnalysis['reccostototal'];
				$costototalfac = $SlsAnalysis['faccostototal'];
			}elseif ($_POST['DetailedReport'] == 'Region') {
				$codigo = $SlsAnalysis['regioncode'];
				//ES SOLO PARA INDICAR SI EL PRODUCTO LLEVA DESCUENTO
				$asterisco = 0;
				$descripcion = $SlsAnalysis['name'];
				$cantorden = $SlsAnalysis['quantityord'];
				$preciocompra = $SlsAnalysis['avgunitprice'];
					
				$cantrec = $SlsAnalysis['quantityrecd'];
				$preciorec = $SlsAnalysis['avgstdcostunit'];
					
				$cantfac = $SlsAnalysis['qtyinvoiced'];
				$preciofac = $SlsAnalysis['avgactprice'];
					
				$costototalord = $SlsAnalysis['ordcostototal'];
				$costototalrec = $SlsAnalysis['reccostototal'];
				$costototalfac = $SlsAnalysis['faccostototal'];
			}elseif ($_POST['DetailedReport'] == 'Area') {
				$codigo = $SlsAnalysis['areacode'];
				//ES SOLO PARA INDICAR SI EL PRODUCTO LLEVA DESCUENTO
				$asterisco = 0;
				$descripcion = $SlsAnalysis['areadescription'];
				$cantorden = $SlsAnalysis['quantityord'];
				$preciocompra = $SlsAnalysis['avgunitprice'];
					
				$cantrec = $SlsAnalysis['quantityrecd'];
				$preciorec = $SlsAnalysis['avgstdcostunit'];
					
				$cantfac = $SlsAnalysis['qtyinvoiced'];
				$preciofac = $SlsAnalysis['avgactprice'];
					
				$costototalord = $SlsAnalysis['ordcostototal'];
				$costototalrec = $SlsAnalysis['reccostototal'];
				$costototalfac = $SlsAnalysis['faccostototal'];
			}elseif ($_POST['DetailedReport'] == 'UnidadNegocio') {
				$codigo = $SlsAnalysis['tagref'];
				//ES SOLO PARA INDICAR SI EL PRODUCTO LLEVA DESCUENTO
				$asterisco = 0;
				$descripcion = $SlsAnalysis['tagdescription'];
				$cantorden = $SlsAnalysis['quantityord'];
				$preciocompra = $SlsAnalysis['avgunitprice'];
					
				$cantrec = $SlsAnalysis['quantityrecd'];
				$preciorec = $SlsAnalysis['avgstdcostunit'];
					
				$cantfac = $SlsAnalysis['qtyinvoiced'];
				$preciofac = $SlsAnalysis['avgactprice'];
					
				$costototalord = $SlsAnalysis['ordcostototal'];
				$costototalrec = $SlsAnalysis['reccostototal'];
				$costototalfac = $SlsAnalysis['faccostototal'];
			}
	
			/*
			echo "<tr>";
			echo "<td class='peque'>" . $codigo . "</td>";
			if ($asterisco == 1){
				echo "<td class='peque'>" . $descripcion . "*</td>";
			}else{
				echo "<td class='peque'>" . $descripcion . "</td>";
			}
			echo "<td class='pequenum'>" .  $cantorden . "</td>";
			echo "<td class='pequenum' nowrap>" . money_format('%(#6.2n', $preciocompra) . "</td>";
			echo "<td class='pequenum' nowrap>" . money_format('%(#7.2n', ($costototalord)) . "</td>";
			echo "<td class='pequenum'>" . $cantrec . "</td>";
			echo "<td class='pequenum' nowrap>" . money_format('%(#6.2n', $preciorec) . "</td>";
			echo "<td class='pequenum' nowrap>" . money_format('%(#7.2n', ($costototalrec)) . "</td>";
			echo "<td class='pequenum'>" . $cantfac . "</td>";
			echo "<td class='pequenum' nowrap>" . money_format('%(#6.2n', $preciofac) . "</td>";
			echo "<td class='pequenum' nowrap>" . money_format('%(#7.2n', ($costototalfac)) . "</td>";
			echo "</tr>";*/
	
			$_SESSION["GeneralCompras"]["Detalle"][]= array("codigo"=>$codigo, 
															"descripcion"=>$descripcion,
															"cantord"=>$cantorden,
															"precompra"=>$preciocompra,
															"totalcompra"=>$costototalord,
															"cantrec"=>$cantrec,
															"prerec"=>$preciorec,
															"totalrec"=>$costototalrec,
															"cantfac"=>$cantfac,
															"prefac"=>$preciofac,
															"totalfac"=>$costototalfac);
			
			$cantordencompra = $cantordencompra + $cantorden;
			$cantrecepcion = $cantrecepcion + $cantrec;
			$cantfactura = $cantfactura + $cantfac;
			$totalcostoordencompra = $totalcostoordencompra + ($costototalord);
			$totalcostorecepcion = $totalcostorecepcion + ($costototalrec);
			$totalcostofactura = $totalcostofactura + ($costototalfac);
	
			if ($_POST['DetailedReport'] == 'Producto') {
				$OCcantordencompra = $OCcantordencompra + $cantorden;
				$OCcantrecepcion = $OCcantrecepcion + $cantrec;
				$OCcantfactura = $OCcantfactura + $cantfac;
				$OCtotalcostoordencompra = $OCtotalcostoordencompra + ($costototalord);
				$OCtotalcostorecepcion = $OCtotalcostorecepcion + ($costototalrec);
				$OCtotalcostofactura = $OCtotalcostofactura + ($costototalfac);
			}
		  
	} /*end while loop */
	
	echo "<script type='text/javascript'>window.open('PDFGeneralCompras.php?detalletipo=".$_POST['DetailedReport']."')</script>";
} // termina condicion de imprimir a PDF (excluido)

if (!isset($_POST["EnviaExcel"])){
	include('includes/footer.inc');
}

ob_end_flush();

?>
