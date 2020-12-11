<?php
/* $Revision: 1.13 $ */
/*cambios
1.- se agrego el include('includes/SecurityFunctions.inc');
2.- se arreglo la variable $funcion*/
$PageSecurity = 2;
include('includes/session.inc');

$funcion=702;
include('includes/SecurityFunctions.inc');

//valores para metodos de costeo

$costeoxrazonsocial = 1;
$costeoxunidadnegocio = 2;
$costeogeneral = 3;
$costeoxserie = 4;

if (!isset($_POST['PrintEXCEL'])) {
      
      $title = _('Reporte General de Estado de Existencias');
      include('includes/header.inc');
      
      echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' ' . $title.'<br>';

	
/*if $FromCriteria is not set then show a form to allow input	*/

      echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'><table>";
	
      //1.- Razon Social
      //2.- Unidad de negocio
      //3.- General
      //4.- Series
      
      
      echo '<tr><td>' . _('Metodo de Costeo') . ':' . "</td>
	    <td><select tabindex='4' name='metodocosteo'>";
	    if ($_POST['metodocosteo'] == $costeoxrazonsocial){
		  echo "<option selected value='" . $costeoxrazonsocial . "'>X Razon Social</option>";  
	    }else{
		  echo "<option value='" . $costeoxrazonsocial . "'>X Razon Social</option>";    
	    }
	    if ($_POST['metodocosteo'] == $costeoxunidadnegocio){
		  echo "<option selected value='" . $costeoxunidadnegocio . "'>X Unidad de Negocio</option>";
	    }else{
		  echo "<option value='" . $costeoxunidadnegocio . "'>X Unidad de Negocio</option>";
	    }
	    if ($_POST['metodocosteo'] == $costeogeneral){
		  echo "<option selected value='" . $costeogeneral . "'>General</option>";
	    }else{
		  echo "<option value='" . $costeogeneral . "'>General</option>";
	    }
	    if ($_POST['metodocosteo'] == $costeoxserie){
		  echo "<option selected value='" . $costeoxserie . "'>X Series</option>";
	    }else{
		  echo "<option value='" . $costeoxserie . "'>X Series</option>";
	    }
	    
	echo '</select></td></tr>';
	
	
	echo '<tr><td><b>** PRODUCTOS</b></td>
		<td>';
	echo '</td></tr>';
	
	/************************************/
	/* SELECCION DEL GRUPO DE PRODUCTOS */
	echo '<tr><td>' . _('Del Grupo') . ':' . "</td>
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
	/* SELECCION DEL GRUPO DE PRODUCTOS */
	echo '<tr><td>' . _('Al Grupo') . ':' . "</td>
		<td><select tabindex='4' name='AlGrupo'>";

	$sql = 'SELECT Prodgroupid, description FROM ProdGroup';
	$result=DB_query($sql,$db);
		
	echo "<option selected value='0'>Todos los grupos...</option>";
	
	while ($myrow=DB_fetch_array($result)){
	      if ($myrow['Prodgroupid'] == $_POST['AlGrupo']){
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
	echo '<tr><td>' . _('X Categoria') . ':' . "</td>
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
	
	/************************************/
	
	echo '<tr><td><br><b>** REGIONES</b></td>
		<td>';
	echo '</td></tr>';

	/************************************/
	
      /************************************/
      /* SELECCION DE LA RAZON SOCIAL */
      echo "<tr><td>" . _('X Razon Social') . ":</td><td>";
      echo "<select name='razonsocial'>";
      $SQL = "SELECT  legalid, legalname";
	    $SQL = $SQL .	" FROM legalbusinessunit";

      $ErrMsg = _('No transactions were returned by the SQL because');
      $TransResult = DB_query($SQL,$db,$ErrMsg);
      
      echo "<option selected value='0'>Todas a las que tengo accceso...</option>";
      
      while ($myrow=DB_fetch_array($TransResult)) {
	    if ($myrow['legalid'] == $_POST['razonsocial']){
		  echo "<option selected value='" . $myrow['legalid'] . "'>" . $myrow['legalname'] . "</option>";	
	    }else{
		  echo "<option value='" . $myrow['legalid'] . "'>" . $myrow['legalname'] . "</option>";
	    }
      }
       
      echo "</select>";
      echo "</td></tr>";

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
      /************************************/
      /* SELECCION DE AREA  	          */
	echo '<tr><td>' . _('X Area') . ':' . "</td>
		<td><select tabindex='4' name='xArea'>";

	$sql = "SELECT areacode, CONCAT(areacode,' - ',areadescription) as name FROM areas";
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

	/************************************/	
	
	
	echo '<tr><td><br><b>** DETALLE DEL REPORTE</b></td>
		<td>';
	echo '</td></tr>';
	
	echo '<tr><td>' . _('X Region o X Sucursal') . ':' . "</td>
		<td><select tabindex='5' name='DetailedReport'>";
	
	if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'No')
		echo "<option selected value='No'>" . _('X Region');
	else
		echo "<option value='No'>" . _('X Region');
	
	if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Yes')
		echo "<option selected value='Yes'>" . _('X Sucursal');
	else
		echo "<option value='Yes'>" . _('X Sucursal');
	
	if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Prod')
		echo "<option selected value='Prod'>" . _('X Clave Producto');
	else
		echo "<option value='Prod'>" . _('X Clave Producto');
      
      
      
	echo '</select></td></tr>';
	
      
       /************************************/
		
	echo '<tr><td>' . _('X Inventarios') . ':' . "</td>
		<td><select tabindex='5' name='tipoinventario'>";
	
	$sqlXtipoInventario = "";
	
	if (isset($_POST['tipoinventario']) and $_POST['tipoinventario'] == 'todos') {
		echo "<option selected value='todos'>" . _('Todos...');
		$sqlXtipoInventario = "";
	}
	else
		echo "<option value='todos'>" . _('Todos');
	
	if (isset($_POST['tipoinventario']) and $_POST['tipoinventario'] == 'normales') {
		echo "<option selected value='normales'>" . _('almacen productos');
		$sqlXtipoInventario = " and locations.temploc = 0 ";
	}
	else
		echo "<option value='normales'>" . _('almacen productos');
	
	if (isset($_POST['tipoinventario']) and $_POST['tipoinventario'] == 'servicios') {
		echo "<option selected value='servicios'>" . _('almacen servicios');
		$sqlXtipoInventario = " and locations.temploc = 1 ";
	}
	else
		echo "<option value='servicios'>" . _('almacen servicios');
	echo '</select></td></tr>';
	
	
	/************************************/
	echo '<tr><td>' . _('X Optimo Definido') . ':' . "</td>
		<td><select tabindex='4' name='xOptimo'>";
      if ($_POST['xOptimo']=="0"){
	    echo '<option value="-1">' . _('Todos los optimos');
	    echo '<option selected value="0">' . _('Con Optimo');
	    echo '<option value="1">' . _('Sin Optimo');
      } elseif ($_POST['xOptimo']=="1") {
	    echo '<option value="-1">' . _('Todos los optimos');
	    echo '<option value="0">' . _('Con Optimo');
	    echo '<option selected value="1">' . _('Sin Optimo');
      } else {
	    echo '<option selected value="-1">' . _('Todos los optimos');
	    echo '<option value="0">' . _('Con Optimo');
	    echo '<option value="1">' . _('Sin Optimo');
      }
      
	echo '</select></td></tr>';
	
	echo "<tr><td>Costo Cero:</td><td>";
	if ((isset($_POST['costocero'])) and  ($_POST['costocero'] <> "")){
	    echo "<input type='checkbox' name='costocero' value='1' checked>";
	}else{
	    echo "<input type='checkbox' name='costocero' value='1'>";
	}
	echo "</td></tr>";
	
	echo '</table>';
	
	/************************************/
	echo' <br><div class="centre"><input tabindex="6" type=submit name="ReportePantalla" value="' . _('Genera Reporte en Pantalla') . '"></div>';
	echo '<br><div class="centre"><input tabindex="7" type=submit name="PrintEXCEL" value="' . _('Exportar a Excel') . '"></div>';
	
}else{
      $sqlXtipoInventario = "";
	if (isset($_POST['tipoinventario']) and $_POST['tipoinventario'] == 'todos') {
		$sqlXtipoInventario = "";
	}
	
	if (isset($_POST['tipoinventario']) and $_POST['tipoinventario'] == 'normales') {
		$sqlXtipoInventario = " and locations.temploc = 0 ";
	}
	
	if (isset($_POST['tipoinventario']) and $_POST['tipoinventario'] == 'servicios') {
		$sqlXtipoInventario = " and locations.temploc = 1 ";
	}
}

If (isset($_POST['ReportePantalla']) OR isset($_POST['PrintEXCEL'])){
      
      if (isset($_POST['PrintEXCEL'])) {
	
		header("Content-type: application/ms-excel");
		# replace excelfile.xls with whatever you want the filename to default to
		header("Content-Disposition: attachment; filename=excelreport.xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	
		echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
		echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
		echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
      }
	    //4.- Series
      if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'No') {
	    	  $SQL = "select ProdGroup.Description as grupo, ProdLine.Description as linea,
				    stockcategory.categorydescription as tipo, locstock.loccode,
				    '' as stockid, '' as description, '' as manufacturer,
				    regions.name as areadescription,
			sum(case when compras.ENCOMPRA is null then 0 else compras.ENCOMPRA end ) as pedcompra,
			sum(case when ventas.enventa is null then 0 else ventas.enventa end ) as pedventa,";
				    
			if($_POST['metodocosteo'] == $costeoxserie){	      
			      $SQL = $SQL . " sum(stockserialitems.quantity) as Existencias,";
			}else{
			      //$SQL = $SQL . " sum(locstock.quantity) as Existencias,";
			      $SQL = $SQL. " sum((locstock.quantity -locstock.qtybysend)-locstock.ontransit) as Existencias,";
			}
			
			$SQL = $SQL . " sum(locstock.reorderlevel) as Autorizado,
				    sum(locstock.ontransit) as EnTransito,";
				    
		  if ($_POST['metodocosteo'] == $costeogeneral) {
			$SQL = $SQL . "sum(stockmaster.materialcost*(locstock.quantity+locstock.ontransit))/sum(locstock.quantity+locstock.ontransit) as CostoPromedio";
		  }elseif(($_POST['metodocosteo'] == $costeoxrazonsocial) or ($_POST['metodocosteo'] == $costeoxunidadnegocio)){
			$SQL = $SQL . "sum(avgcost*(locstock.quantity+locstock.ontransit))/sum(locstock.quantity+locstock.ontransit) as CostoPromedio";
			//$SQL = $SQL . "avgcost as CostoPromedio";
		  }elseif($_POST['metodocosteo'] == $costeoxserie){
			$SQL = $SQL . "sum(stockserialitems.standardcost*(locstock.quantity+locstock.ontransit))/sum(locstock.quantity+locstock.ontransit) as CostoPromedio";
			//$SQL = $SQL . "stockserialitems.standardcost as CostoPromedio";
		  }
		  
		  /*
		  $SQL = $SQL . " from locstock JOIN stockmaster ON locstock.stockid =  stockmaster.stockid";
		
		  if ($_POST['metodocosteo'] == $costeoxrazonsocial) {
			$SQL = $SQL . " LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid";
		  }elseif($_POST['metodocosteo'] == $costeoxunidadnegocio){
			$SQL = $SQL . " LEFT JOIN stockcostsxtag ON stockmaster.stockid = stockcostsxtag.stockid";
		  }elseif($_POST['metodocosteo'] == $costeoxserie){
			$SQL = $SQL . " LEFT JOIN stockserialitems ON stockmaster.stockid = stockserialitems.stockid";
		  }
		  */
		  
		  if ($_POST['metodocosteo'] == $costeoxrazonsocial) {
			$SQL = $SQL . " FROM stockcostsxlegal JOIN  stockmaster ON stockmaster.stockid = stockcostsxlegal.stockid";
		  }elseif($_POST['metodocosteo'] == $costeoxunidadnegocio){
			$SQL = $SQL . " FROM stockcostsxtag JOIN stockmaster ON stockmaster.stockid = stockcostsxtag.stockid";
		  }elseif($_POST['metodocosteo'] == $costeoxserie){
			$SQL = $SQL . " FROM stockserialitems JOIN stockmaster ON stockmaster.stockid = stockserialitems.stockid";
		  }
		  $SQL = $SQL ." JOIN locstock ON locstock.stockid =  stockmaster.stockid";
		  
			
		  $SQL = $SQL . " JOIN locations ON locstock.loccode = locations.loccode
		  
		  LEFT JOIN(  SELECT SUM(purchorderdetails.quantityord ) AS ENCOMPRA,
				    purchorders.intostocklocation as almacencompra,
				    purchorderdetails.itemcode as producto
			      FROM  purchorderdetails
			      INNER JOIN purchorders
			      ON purchorderdetails.orderno=purchorders.orderno
			      AND status  not in ('cancelled')
			GROUP BY purchorders.intostocklocation,purchorderdetails.itemcode
			HAVING SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd)>0
		  ) AS compras ON compras.almacencompra=locstock.loccode AND compras.producto= locstock.stockid
			
		  LEFT JOIN(  SELECT SUM(salesorderdetails.quantity ) AS enventa,
				    salesorderdetails.fromstkloc AS almacenventa,
				    salesorderdetails.stkcode AS productoventa
			      FROM  salesorderdetails
			      INNER JOIN salesorders ON salesorderdetails.orderno=salesorders.orderno
			      AND salesorders.quotation NOT IN (4,3)
			GROUP BY salesorderdetails.fromstkloc ,salesorderdetails.stkcode
		  ) AS ventas ON ventas.almacenventa=locstock.loccode AND ventas.productoventa= locstock.stockid
		  
			JOIN tags ON locations.tagref = tags.tagref
			JOIN departments ON tags.u_department=departments.u_department";
			
		  /*if ($_POST['metodocosteo'] == $costeoxrazonsocial) {
			$SQL = $SQL . " JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid";
		  }*/
		  $SQL = $SQL . " JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
			      JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			      JOIN areas ON tags.areacode = areas.areacode
			      JOIN regions ON areas.regioncode = regions.regioncode
			      JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
			      JOIN ProdLine ON stockcategory.ProdLineId = ProdLine.Prodlineid
			      JOIN ProdGroup ON ProdLine.Prodgroupid = ProdGroup.Prodgroupid";
		  
		  if ($_POST['metodocosteo'] == $costeogeneral) {
			$SQL = $SQL . " where 1 /*mbflag = 'B'*/";
		  }elseif($_POST['metodocosteo'] == $costeoxrazonsocial) {
			$SQL = $SQL . " where stockcostsxlegal.legalid = legalbusinessunit.legalid
			      /*and mbflag = 'B'*/";
		  }elseif($_POST['metodocosteo'] == $costeoxunidadnegocio){
			$SQL = $SQL . " where stockcostsxtag.tagref = tags.tagref
			      /*and mbflag = 'B'*/";
		  }elseif($_POST['metodocosteo'] == $costeoxserie){
			$SQL = $SQL . " where stockserialitems.loccode = locstock.loccode
			      /*and mbflag = 'B'*/";
		  }
		  	      if ($_POST['xOptimo']==0){
				    $SQL=$SQL.' and locstock.reorderlevel>0 ';				    
			      } elseif ($_POST['xOptimo']==1){
				    $SQL=$SQL.' and locstock.reorderlevel=0 ';				    
			      }
		  $SQL = $SQL . " and (ProdGroup.Prodgroupid = ".$_POST['xGrupo']." or ".$_POST['xGrupo']."=0)
			  and (ProdLine.ProdLineId = ".$_POST['xLinea']." or ".$_POST['xLinea']."=0)";
		  if (strlen($_POST['xCategoria'])>0 and $_POST['xCategoria']!='0' and $_POST['xCategoria']!=''){
			$SQL=$SQL." and (stockmaster.categoryid = '".$_POST['xCategoria']."')";
		  }
		  $SQL=$SQL." AND (locstock.loccode = '".$_POST['almacen']."' or '".$_POST['almacen']."'='0')
			AND (legalbusinessunit.legalid = '".$_POST['razonsocial']."' or ".$_POST['razonsocial']."=0)
			and (areas.regioncode = '".$_POST['xRegion']."' or ".$_POST['xRegion']."=0)
			and (locations.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			and (areas.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			and (departments.u_department = ".$_POST['xDepto']." or ".$_POST['xDepto']."=0)
		  ".$sqlXtipoInventario."
		  GROUP BY  ProdGroup.Description, ProdLine.Description, stockcategory.categorydescription, locstock.loccode,
				  '', '', '',
				  regions.name
		  HAVING Existencias <> 0";
	    
		
	   // echo $SQL;
			
	/* YES ES DETALLADO POR SUCURSAL*/
	} elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Yes') {
	    //echo "entra";
		$SQL = "select ProdGroup.Description as grupo, ProdLine.Description as linea,
			      stockcategory.categorydescription as tipo, locstock.loccode,
			      '' as stockid, '' as description, '' as manufacturer,
			      areas.areadescription,
			sum(case when compras.ENCOMPRA is null then 0 else compras.ENCOMPRA end ) as pedcompra,
			sum(case when ventas.enventa is null then 0 else ventas.enventa end ) as pedventa,";
			
			if($_POST['metodocosteo'] == $costeoxserie){	      
			      $SQL = $SQL . " sum(stockserialitems.quantity) as Existencias,";
			}else{
			      //$SQL = $SQL . " sum(locstock.quantity) as Existencias,";
				$SQL = $SQL. " sum((locstock.quantity -locstock.qtybysend)-locstock.ontransit) as Existencias,";
			}      
			      
			$SQL = $SQL . " sum(locstock.reorderlevel) as Autorizado, 
			      sum(locstock.ontransit) as EnTransito,";
			      
		  if ($_POST['metodocosteo'] == $costeogeneral) {
			$SQL = $SQL . "sum(stockmaster.materialcost*(locstock.quantity+locstock.ontransit))/sum(locstock.quantity+locstock.ontransit) as CostoPromedio";
		  }elseif(($_POST['metodocosteo'] == $costeoxrazonsocial) or ($_POST['metodocosteo'] == $costeoxunidadnegocio)){
			$SQL = $SQL . "sum(avgcost*(locstock.quantity+locstock.ontransit))/sum(locstock.quantity+locstock.ontransit) as CostoPromedio";
			//$SQL = $SQL . "avgcost as CostoPromedio";
		  }elseif($_POST['metodocosteo'] == $costeoxserie){
			$SQL = $SQL . "sum(stockserialitems.standardcost*(locstock.quantity+locstock.ontransit))/sum(locstock.quantity+locstock.ontransit) as CostoPromedio";
			//$SQL = $SQL . "stockserialitems.standardcost as CostoPromedio";
		  }
		  
		  /*
		  $SQL = $SQL .  " from locstock JOIN stockmaster ON locstock.stockid =  stockmaster.stockid";
		  
		  if ($_POST['metodocosteo'] == $costeoxrazonsocial) {
			$SQL = $SQL . " LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid";
		  }elseif($_POST['metodocosteo'] == $costeoxunidadnegocio){
			$SQL = $SQL . " LEFT JOIN stockcostsxtag ON stockmaster.stockid = stockcostsxtag.stockid";
		  }elseif($_POST['metodocosteo'] == $costeoxserie){
			$SQL = $SQL . " LEFT JOIN stockserialitems ON stockmaster.stockid = stockserialitems.stockid";
		  }
		  */
		  
		  if ($_POST['metodocosteo'] == $costeoxrazonsocial) {
			$SQL = $SQL . " FROM stockcostsxlegal JOIN  stockmaster ON stockmaster.stockid = stockcostsxlegal.stockid";
		  }elseif($_POST['metodocosteo'] == $costeoxunidadnegocio){
			$SQL = $SQL . " FROM stockcostsxtag JOIN stockmaster ON stockmaster.stockid = stockcostsxtag.stockid";
		  }elseif($_POST['metodocosteo'] == $costeoxserie){
			$SQL = $SQL . " FROM stockserialitems JOIN stockmaster ON stockmaster.stockid = stockserialitems.stockid";
		  }
		  
		  $SQL = $SQL ." JOIN locstock ON locstock.stockid =  stockmaster.stockid";
		  
		  $SQL = $SQL . " JOIN locations ON locstock.loccode = locations.loccode
		  
		  LEFT JOIN(  SELECT SUM(purchorderdetails.quantityord ) AS ENCOMPRA,
				    purchorders.intostocklocation as almacencompra,
				    purchorderdetails.itemcode as producto
			      FROM  purchorderdetails
			      INNER JOIN purchorders
			      ON purchorderdetails.orderno=purchorders.orderno
			      AND status  not in ('cancelled')
			GROUP BY purchorders.intostocklocation,purchorderdetails.itemcode
			HAVING SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd)>0
		  ) AS compras ON compras.almacencompra=locstock.loccode AND compras.producto= locstock.stockid
			
		  LEFT JOIN(  SELECT SUM(salesorderdetails.quantity ) AS enventa,
				    salesorderdetails.fromstkloc AS almacenventa,
				    salesorderdetails.stkcode AS productoventa
			      FROM  salesorderdetails
			      INNER JOIN salesorders ON salesorderdetails.orderno=salesorders.orderno
			      AND salesorders.quotation NOT IN (4,3)
			GROUP BY salesorderdetails.fromstkloc ,salesorderdetails.stkcode
		  ) AS ventas ON ventas.almacenventa=locstock.loccode AND ventas.productoventa= locstock.stockid
		  
			JOIN tags ON locations.tagref = tags.tagref
			JOIN departments ON tags.u_department=departments.u_department";
			
		  /*if ($_POST['metodocosteo'] == $costeoxrazonsocial) {
			
		  }*/
		  
		  $SQL = $SQL . " JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
			      JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			      JOIN areas ON tags.areacode = areas.areacode
			      JOIN regions ON areas.regioncode = regions.regioncode
			      JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
			      JOIN ProdLine ON stockcategory.ProdLineId = ProdLine.Prodlineid
			      JOIN ProdGroup ON ProdLine.Prodgroupid = ProdGroup.Prodgroupid";
			
			if ($_POST['metodocosteo'] == $costeogeneral) {
			      $SQL = $SQL . " where 1 /*mbflag = 'B'*/";
			}elseif($_POST['metodocosteo'] == $costeoxrazonsocial) {
			      $SQL = $SQL . " where stockcostsxlegal.legalid = legalbusinessunit.legalid
				    /*and mbflag = 'B'*/";
			}elseif($_POST['metodocosteo'] == $costeoxunidadnegocio){
			      $SQL = $SQL . " where stockcostsxtag.tagref = tags.tagref
				    /*and mbflag = 'B'*/";
			}elseif($_POST['metodocosteo'] == $costeoxserie){
			      $SQL = $SQL . " where stockserialitems.loccode = locstock.loccode
				    /*and mbflag = 'B'*/";
			}
			      if ($_POST['xOptimo']==0){
				    $SQL=$SQL.' and locstock.reorderlevel>0 ';				    
			      } elseif ($_POST['xOptimo']==1){
				    $SQL=$SQL.' and locstock.reorderlevel=0 ';				    
			      }
		  $SQL = $SQL . " and (ProdGroup.Prodgroupid = ".$_POST['xGrupo']." or ".$_POST['xGrupo']."=0)
				and (ProdLine.ProdLineId = ".$_POST['xLinea']." or ".$_POST['xLinea']."=0)";
			if (strlen($_POST['xCategoria'])>0 and $_POST['xCategoria']!='0' and $_POST['xCategoria']!=''){
			      $SQL=$SQL." and (stockmaster.categoryid = '".$_POST['xCategoria']."')";
			}
			$SQL = $SQL." AND (locstock.loccode = '".$_POST['almacen']."' or '".$_POST['almacen']."'='0')
			      AND (legalbusinessunit.legalid = '".$_POST['razonsocial']."' or ".$_POST['razonsocial']."=0)
			      and (areas.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
				and (locations.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
				and (areas.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
				and (departments.u_department = ".$_POST['xDepto']." or ".$_POST['xDepto']."=0)
			".$sqlXtipoInventario."	
			GROUP BY  ProdGroup.Description, ProdLine.Description, stockcategory.categorydescription, locstock.loccode,
					'', '', '', areas.areadescription
			HAVING Existencias <> 0";
		//  echo $SQL;
	} elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Prod') {
	    
	        //sum(stockmaster.materialcost*locstock.quantity)/sum(locstock.quantity) as CostoPromedio
		
		  $SQL = "select locations.locationname as grupo, ProdLine.Description as linea,
			      stockcategory.categorydescription as tipo, locstock.loccode,
			      stockmaster.stockid, stockmaster.description, stockmaster.manufacturer,
			      areas.areacode,
			      areas.areadescription, stockmaster.controlled,
			      stockmaster.serialised,
			      sum(case when compras.ENCOMPRA is null then 0 else compras.ENCOMPRA end ) as pedcompra,
			      sum(case when ventas.enventa is null then 0 else ventas.enventa end ) as pedventa,";
		  
		  if($_POST['metodocosteo'] == $costeoxserie){	      
			$SQL = $SQL . " sum(stockserialitems.quantity) as Existencias,";
		  }else{
			//$SQL = $SQL . " sum(locstock.quantity) as Existencias,";
		  	$SQL = $SQL. " sum((locstock.quantity -locstock.qtybysend)-locstock.ontransit) as Existencias,";
		  }
			      
			$SQL = $SQL . " sum(locstock.reorderlevel) as Autorizado, 
				        sum(locstock.ontransit) as EnTransito,";
		  
		  if ($_POST['metodocosteo'] == $costeogeneral) {
			$SQL = $SQL . "sum(stockmaster.materialcost*(locstock.quantity+locstock.ontransit))/sum(locstock.quantity+locstock.ontransit) as CostoPromedio";
		  }elseif(($_POST['metodocosteo'] == $costeoxrazonsocial) or ($_POST['metodocosteo'] == $costeoxunidadnegocio)){
			$SQL = $SQL . "sum(avgcost*(locstock.quantity+locstock.ontransit))/sum(locstock.quantity+locstock.ontransit) as CostoPromedio";
			//$SQL = $SQL . "avgcost as CostoPromedio";
		  }elseif($_POST['metodocosteo'] == $costeoxserie){
			$SQL = $SQL . "sum(stockserialitems.standardcost*(locstock.quantity+locstock.ontransit))/sum(locstock.quantity+locstock.ontransit) as CostoPromedio";
			//$SQL = $SQL . "stockserialitems.standardcost as CostoPromedio";
		  }
		
		  /*
		  $SQL = $SQL ." from locstock JOIN stockmaster ON locstock.stockid =  stockmaster.stockid";
		  
		  if ($_POST['metodocosteo'] == $costeoxrazonsocial) {
			$SQL = $SQL . " LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid";
		  }elseif($_POST['metodocosteo'] == $costeoxunidadnegocio){
			$SQL = $SQL . " LEFT JOIN stockcostsxtag ON stockmaster.stockid = stockcostsxtag.stockid";
		  }elseif($_POST['metodocosteo'] == $costeoxserie){
			$SQL = $SQL . " LEFT JOIN stockserialitems ON stockmaster.stockid = stockserialitems.stockid";
		  }
		  */
		  
		  /*
		  if ($_POST['metodocosteo'] == $costeoxrazonsocial) {
			$SQL = $SQL . " FROM stockcostsxlegal JOIN  stockmaster ON stockmaster.stockid = stockcostsxlegal.stockid";
		  }elseif($_POST['metodocosteo'] == $costeoxunidadnegocio){
			$SQL = $SQL . " FROM stockcostsxtag JOIN stockmaster ON stockmaster.stockid = stockcostsxtag.stockid";
		  }elseif($_POST['metodocosteo'] == $costeoxserie){
			$SQL = $SQL . " FROM stockserialitems JOIN stockmaster ON stockmaster.stockid = stockserialitems.stockid";
		  }else{
			$SQL = $SQL . " FROM ";
		  }
		  */
		  
		  $SQL = $SQL . " FROM locstock JOIN locations ON locstock.loccode = locations.loccode 
			JOIN tags ON locations.tagref = tags.tagref ";
			
		  
		  if ($_POST['metodocosteo'] == $costeoxrazonsocial) {
			$SQL = $SQL . " JOIN stockcostsxlegal ON tags.legalid = stockcostsxlegal.legalid AND locstock.stockid = stockcostsxlegal.stockid";
		  }elseif($_POST['metodocosteo'] == $costeoxunidadnegocio){
			$SQL = $SQL . " JOIN stockcostsxtag ON tags.tagref = stockcostsxtag.tagref AND locstock.stockid = stockcostsxtag.stockid";
		  }elseif($_POST['metodocosteo'] == $costeoxserie){
			$SQL = $SQL . " JOIN stockserialitems ON locations.loccode = stockserialitems.loccode AND locstock.stockid = stockserialitems.stockid";
		  }else{
			$SQL = $SQL . " ";
		  }
		
			
			
		  $SQL = $SQL . " JOIN stockmaster ON locstock.stockid = stockmaster.stockid
			JOIN departments ON tags.u_department=departments.u_department 
			JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
			JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			JOIN areas ON tags.areacode = areas.areacode 
			JOIN regions ON areas.regioncode = regions.regioncode 
			JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid 
			JOIN ProdLine ON stockcategory.ProdLineId = ProdLine.Prodlineid 
			JOIN ProdGroup ON ProdLine.Prodgroupid = ProdGroup.Prodgroupid ";
			
		  $SQL = $SQL . " 
		  
		  LEFT JOIN(  SELECT SUM(purchorderdetails.quantityord ) AS ENCOMPRA,
				    purchorders.intostocklocation as almacencompra,
				    purchorderdetails.itemcode as producto
			      FROM  purchorderdetails
			      INNER JOIN purchorders
			      ON purchorderdetails.orderno=purchorders.orderno
			      AND status  not in ('cancelled')
			GROUP BY purchorders.intostocklocation,purchorderdetails.itemcode
			HAVING SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd)>0
		  ) AS compras ON compras.almacencompra=locstock.loccode AND compras.producto= locstock.stockid
			
		  LEFT JOIN(  SELECT SUM(salesorderdetails.quantity ) AS enventa,
				    salesorderdetails.fromstkloc AS almacenventa,
				    salesorderdetails.stkcode AS productoventa
			      FROM  salesorderdetails
			      INNER JOIN salesorders ON salesorderdetails.orderno=salesorders.orderno
			      AND salesorders.quotation NOT IN (4,3)
			GROUP BY salesorderdetails.fromstkloc ,salesorderdetails.stkcode
		  ) AS ventas ON ventas.almacenventa=locstock.loccode AND ventas.productoventa= locstock.stockid";
			
		  /*
		  if ($_POST['metodocosteo'] == $costeoxrazonsocial) {
			$SQL = $SQL . " JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid";
		  }*/				
				
			if ($_POST['metodocosteo'] == $costeogeneral) {
			      $SQL = $SQL . " where  (locstock.quantity <> 0 or locstock.reorderlevel <> 0) /*and mbflag = 'B'*/ ";
			      
			      if ((isset($_POST['costocero'])) and  ($_POST['costocero'] <> "")){
			            $SQL = $SQL . " and stockmaster.materialcost = 0";
			      }
			      
			}elseif($_POST['metodocosteo'] == $costeoxrazonsocial) {
			      $SQL = $SQL . " where  (locstock.quantity <> 0 or locstock.reorderlevel <> 0) /*and mbflag = 'B'*/ and stockcostsxlegal.legalid = legalbusinessunit.legalid
				    ";
			      
			      if ((isset($_POST['costocero'])) and  ($_POST['costocero'] <> "")){
			            $SQL = $SQL . " and avgcost = 0";
			      }	    
				    			      
		
			    
				    
			}elseif($_POST['metodocosteo'] == $costeoxunidadnegocio){
			      $SQL = $SQL . " where  (locstock.quantity <> 0 or locstock.reorderlevel <> 0) /*and mbflag = 'B'*/ and stockcostsxtag.tagref = tags.tagref
				    ";
			      if ((isset($_POST['costocero'])) and  ($_POST['costocero'] <> "")){
				    $SQL = $SQL . " and avgcost = 0";
			      }	    
				    
			}elseif($_POST['metodocosteo'] == $costeoxserie){
			      $SQL = $SQL . " where  (locstock.quantity <> 0 or locstock.reorderlevel <> 0) /*and mbflag = 'B'*/ and stockserialitems.loccode = locstock.loccode
				    ";
				    
			      if ((isset($_POST['costocero'])) and  ($_POST['costocero'] <> "")){
				    $SQL = $SQL . " and stockserialitems.standardcost = 0";
			      }
			}
			
			if ((strlen($_POST['xCategoria'])>0) and ($_POST['xCategoria']!='0') and ($_POST['xCategoria']!='')){
			      $SQL=$SQL." and (stockmaster.categoryid = '".$_POST['xCategoria']."')";
			}
		  
			if ($_POST['xOptimo']==0){
			      $SQL=$SQL.' and locstock.reorderlevel>0 ';				    
			} elseif ($_POST['xOptimo']==1){
			      $SQL=$SQL.' and locstock.reorderlevel=0 ';				    
			}
			      
		  $SQL = $SQL . " and (ProdGroup.Prodgroupid >= ".$_POST['xGrupo']." or ".$_POST['xGrupo']."=0)
				  and (ProdGroup.Prodgroupid <= ".$_POST['AlGrupo']." or ".$_POST['AlGrupo']."=0)
				and (ProdLine.ProdLineId = ".$_POST['xLinea']." or ".$_POST['xLinea']."=0)";
				
		  
			
		  $SQL=$SQL." AND (locstock.loccode = '".$_POST['almacen']."' or '".$_POST['almacen']."'='0')
			      AND (legalbusinessunit.legalid = '".$_POST['razonsocial']."' or '".$_POST['razonsocial']."'='0')		
			      and (areas.regioncode = '".$_POST['xRegion']."' or '".$_POST['xRegion']."'='0')
			      and (locations.tagref = '".$_POST['unidadnegocio']."' or '".$_POST['unidadnegocio']."'='0')
			      and (areas.areacode = '".$_POST['xArea']."' or '".$_POST['xArea']."'='0')
			      and (departments.u_department = '".$_POST['xDepto']."' or '".$_POST['xDepto']."'='0')
			      
			".$sqlXtipoInventario."	
			GROUP BY  locations.locationname, ProdLine.Description, stockcategory.categorydescription, locstock.loccode,
					stockmaster.stockid, stockmaster.description, stockmaster.manufacturer,
					areas.areacode, areas.areadescription,
					stockmaster.controlled, stockmaster.serialised
			HAVING (Existencias + EnTransito) <> 0";
			
		
	}
	/*if($_SESSION['UserID'] == "admin"){
		echo '<pre> SQl: '.$SQL;
	}*/
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
	
	$ProdTotExistencias = 0;
	$ProdTotEnTransito = 0;
	$ProdTotAutorizado = 0;
	$ProdTotPedVenta = 0;
	$ProdTotPedCompra = 0;
	$ProdTotCosto = 0;
	$ProdTotCostoNoAsig = 0;
	
	$TipoTotExistencias = 0;
	$TipoTotEnTransito = 0;
	$TipoTotAutorizado = 0;
	$TipoTotPedVenta = 0;
	$TipoTotPedCompra = 0;
	$TipoTotCosto = 0;
	$TipoTotCostoNoAsig = 0;
	
	$LineaTotExistencias = 0;
	$LineaTotEnTransito = 0;
	$LineaTotAutorizado = 0;
	$LineaTotPedVenta = 0;
	$LineaTotPedCompra = 0;
	$LineaTotCosto = 0;
	$LineaTotCostoNoAsig = 0;
	
	$GrupoTotExistencias = 0;
	$GrupoTotEnTransito = 0;
	$GrupoTotAutorizado = 0;
	$GrupoTotPedVenta = 0;
	$GrupoTotPedCompra = 0;
	$GrupoTotCosto = 0;
	$GrupoTotCostoNoAsig = 0;

	$TotExistencias = 0;
	$TotEnTransito = 0;
	$TotAutorizado = 0;
	$TotPedVenta = 0;
	$TipoTotPedCompra = 0;
	$TotCosto = 0;
	$TotCostoNoAsig = 0;
	
	echo '<table cellpadding=2 border=1>';
			
	$headerLineaProductos = '<tr>
			<th><b>' . _('Clave') . '</b></th>
			<th><b>' . _('Descripcion') . '</b></th>
			<th><b>' . _('Categoria') . '</b></th>';
		
	if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Yes') {
		$headerLineaProductos = $headerLineaProductos. '
			<th><b>' . _('Sucursal') . '</b></th>';
	} elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'No') {
		$headerLineaProductos = $headerLineaProductos. '
			<th><b>' . _('Region') . '</b></th>';
	} else {
		$headerLineaProductos = $headerLineaProductos. '
			<th><b>' . _('Sucursal') . '</b></th>';
	}
	
	$headerLineaProductos = $headerLineaProductos. '
			<th><b>' . _('Exis') . '</b></th>
			<th><b>' . _('Trans') . '</b></th>
			<th><b>' . _('Disp') . '</b></th>
			<th><b>' . _('Optimo') . '</b></th>
			<th><b>' . _('Pedido Venta.') . '</b></th>
			<th><b>' . _('Orden Compra.') . '</b></th>
			<th><b>' . _('Costo') . '</b></th>
			<th><b>' . _('Valor Inv') . '</b></th>
			<th><b>' . _('Valor No Asignado') . '</b></th>
			</tr>';

	$i = 0;
	
	$antGrupo = '';
	$antLinea = '';
	$antTipo = '';
	$antProd = '';
	
	$lineasConMismoProducto = 0;
	$primeraEntrada = 1;
	
      $encabezadocada = 0;
      while ($InvAnalysis = DB_fetch_array($ReportResult,$db)){
	    $encabezadocada = $encabezadocada + 1;
	    if ($encabezadocada >= 30) {
		  echo $headerLineaProductos;
		  $encabezadocada = 0;
	    }
	    $thisGrupo = $InvAnalysis['grupo'];
	    $thisLinea = $InvAnalysis['linea'];
	    $thisTipo = $InvAnalysis['tipo'];
	    $thisProd = $InvAnalysis['stockid'];
		
	    $lineasConMismoProducto = $lineasConMismoProducto + 1;
		
	    if ($antProd <> $thisProd) {
		  if ($primeraEntrada == 0 and $lineasConMismoProducto > 1) {
			echo '<tr>';
			echo '<td></td><td class=norm colspan=3><b>TOTAL '.$antProd.'</b></td>
			      <td class=normnum>'.$ProdTotExistencias.'</td>
			      <td class=normnum>'.$ProdTotEnTransito.'</td>
			      <td class=normnum>'.($ProdTotExistencias-$ProdTotEnTransito).'</td>
			      <td class=normnum>'.$ProdTotAutorizado.'</td>
			      <td class=normnum>'.$ProdTotPedVenta.'</td>
			      <td class=normnum>'.$ProdTotPedCompra.'</td>
			      <td></td>
			      <td class=normnum>'.number_format($ProdTotCosto,2).'</td>
			      <td class=normnum>'.number_format($ProdTotCostoNoAsig,2).'</td>
			      </tr>';
				
			$ProdTotExistencias = 0;
			$ProdTotEnTransito = 0;
			$ProdTotAutorizado = 0;
			$ProdTotPedVenta = 0;
			$ProdTotPedVenta = 0;
			$ProdTotCosto = 0;
			$ProdTotCostoNoAsig = 0;
		  }
		  $lineasConMismoProducto = 0;
	    }
		
	    if ($antTipo <> $thisTipo) {
		  if ($primeraEntrada == 0) {
			echo '<tr>';
			echo '<td class=norm colspan=4><b>- - TOTAL '.$antTipo.'</b></td>
			      <td class=pequenum>'.$TipoTotExistencias.'</td>
			      <td class=pequenum>'.$TipoTotEnTransito.'</td>
			      <td class=pequenum>'.($TipoTotExistencias-$TipoTotEnTransito).'</td>
			      <td class=pequenum>'.$TipoTotAutorizado.'</td>
			      <td class=pequenum>'.$TipoTotPedVenta.'</td>
			      <td class=pequenum>'.$TipoTotPedCompra.'</td>
			      <td></td>
			      <td class=pequenum>'.number_format($TipoTotCosto,2).'</td>
			      <td class=pequenum>'.number_format($TipoTotCostoNoAsig,2).'</td>
			      </tr>';
			$TipoTotExistencias = 0;
			$TipoTotEnTransito = 0;
			$TipoTotAutorizado = 0;
			$TipoTotPedVenta = 0;
			$TipoTotPedCompra = 0;
			$TipoTotCosto = 0;
			$TipoTotCostoNoAsig = 0;
		  }
	    }

	    if ($antLinea <> $thisLinea) {
		  if ($primeraEntrada == 0) {
			echo '<tr>';
			echo '<td class=norm colspan=4><b>- TOTAL '.$antLinea.'</b></td>
			      <td class=pequenum>'.$LineaTotExistencias.'</td>
			      <td class=pequenum>'.$LineaTotEnTransito.'</td>
			      <td class=pequenum>'.($LineaTotExistencias-$LineaTotEnTransito).'</td>
			      <td class=pequenum>'.$LineaTotAutorizado.'</td>
			      <td class=pequenum>'.$LineaTotPedVenta.'</td>
			      <td class=pequenum>'.$LineaTotPedCompra.'</td>
			      <td></td>
			      <td class=pequenum>'.number_format($LineaTotCosto,2).'</td>
			      <td class=pequenum>'.number_format($LineaTotCostoNoAsig,2).'</td>
			      </tr>';
				  
			$LineaTotExistencias = 0;
			$LineaTotEnTransito = 0;
			$LineaTotAutorizado = 0;
			$LineaTotPedVenta = 0;
			$LineaTotPedCompra = 0;
			$LineaTotCosto = 0;
			$LineaTotCostoNoAsig = 0;
		  }
	    }
		
	    if ($antGrupo <> $thisGrupo) {
		  if ($primeraEntrada == 0) {
			echo '<tr>';
			echo '<td class=norm colspan=4><b>TOTAL '.$antGrupo.'</b></td>
			      <td class=pequenum>'.$GrupoTotExistencias.'</td>
			      <td class=pequenum>'.$GrupoTotEnTransito.'</td>
			      <td class=pequenum>'.($GrupoTotExistencias-$GrupoTotEnTransito).'</td>
			      <td class=pequenum>'.$GrupoTotAutorizado.'</td>
			      <td class=pequenum>'.$GrupoTotPedCompra.'</td>
			      <td></td>
			      <td class=pequenum>'.number_format($GrupoTotCosto,2).'</td>
			      <td class=pequenum>'.number_format($GrupoTotCostoNoAsig,2).'</td>
			      </tr>';
				
			$GrupoTotExistencias = 0;
			$GrupoTotEnTransito = 0;
			$GrupoTotAutorizado = 0;
			$GrupoTotPedVenta = 0;
			$GrupoTotPedCompra = 0;
			$GrupoTotCosto = 0;
			$GrupoTotCostoNoAsig = 0;
		  }
	    }
		
	    if ($antGrupo <> $thisGrupo) {
		  echo '<tr>';
		  echo '<td class=GroupTableRows colspan=12><b>'.$thisGrupo.'</b></td>
			</tr>';
		  $antGrupo = $thisGrupo;
	    }
		
	    if ($antLinea <> $thisLinea) {
		  echo '<tr>';
		  echo '<td class=GroupTableRows colspan=12><b>- '.$thisLinea.'</b></td>
			</tr>';
		  $antLinea = $thisLinea;
	    }
		
	    if ($antTipo <> $thisTipo) {
		  echo '<tr>';
		  echo '<td class=GroupTableRows colspan=12><b>- - '.$thisTipo.'</b></td>
			</tr>';
		  echo $headerLineaProductos;
		  $antTipo = $thisTipo;
	    }
		
	    $antProd = $thisProd;
		
	    $primeraEntrada = 0;		
	    if ($i == 0) {
		  echo '<tr class="EvenTableRows">';
		  $i = 1;
	    }else{
		  echo '<tr class="OddTableRows">';
		  $i = 0;
	    }
		
	    $ProdTotExistencias = $ProdTotExistencias + $InvAnalysis['Existencias'];
	    $ProdTotEnTransito = $ProdTotEnTransito + $InvAnalysis['EnTransito'];
	    $ProdTotAutorizado = $ProdTotAutorizado + $InvAnalysis['Autorizado'];
	    $ProdTotPedVenta = $ProdTotPedVenta + $InvAnalysis['pedventa'];
	    $ProdTotPedCompra = $ProdTotPedCompra + $InvAnalysis['pedcompra'];
	    //$ProdTotCosto = $ProdTotCosto + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']+$InvAnalysis['EnTransito']));
	    //$ProdTotCostoNoAsig = $ProdTotCostoNoAsig + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']+$InvAnalysis['EnTransito']-$InvAnalysis['pedventa']));
	    
	    $TipoTotExistencias = $TipoTotExistencias + $InvAnalysis['Existencias'];
	    $TipoTotEnTransito = $TipoTotEnTransito + $InvAnalysis['EnTransito'];
	    $TipoTotAutorizado = $TipoTotAutorizado + $InvAnalysis['Autorizado'];
	    $TipoTotPedVenta = $TipoTotPedVenta + $InvAnalysis['pedventa'];
	    $TipoTotPedCompra = $TipoTotPedCompra + $InvAnalysis['pedcompra'];
	    //$TipoTotCosto = $TipoTotCosto + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']+$InvAnalysis['EnTransito']));
	    //$TipoTotCostoNoAsig = $TipoTotCostoNoAsig + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']+$InvAnalysis['EnTransito']-$InvAnalysis['pedventa']));
	    
	    $LineaTotExistencias = $LineaTotExistencias + $InvAnalysis['Existencias'];
	    $LineaTotEnTransito = $LineaTotEnTransito + $InvAnalysis['EnTransito'];
	    $LineaTotAutorizado = $LineaTotAutorizado + $InvAnalysis['Autorizado'];
	    $LineaTotPedVenta = $LineaTotPedVenta + $InvAnalysis['pedventa'];
	    $LineaTotPedCompra = $LineaTotPedCompra + $InvAnalysis['pedcompra'];
	    //$LineaTotCosto = $LineaTotCosto + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']+$InvAnalysis['EnTransito']));
	    //$LineaTotCostoNoAsig = $LineaTotCostoNoAsig + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']+$InvAnalysis['EnTransito']-$InvAnalysis['pedventa']));
	    
	    $GrupoTotExistencias = $GrupoTotExistencias + $InvAnalysis['Existencias'];
	    $GrupoTotEnTransito = $GrupoTotEnTransito + $InvAnalysis['EnTransito'];
	    $GrupoTotAutorizado = $GrupoTotAutorizado + $InvAnalysis['Autorizado'];
	    $GrupoTotPedVenta = $GrupoTotPedVenta + $InvAnalysis['pedventa'];
	    $GrupoTotPedCompra = $GrupoTotPedCompra + $InvAnalysis['pedcompra'];
	    //$GrupoTotCosto = $GrupoTotCosto + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']+$InvAnalysis['EnTransito']));
	    //$GrupoTotCostoNoAsig = $GrupoTotCostoNoAsig + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']+$InvAnalysis['EnTransito']-$InvAnalysis['pedventa']));
	    
	    $TotExistencias = $TotExistencias + $InvAnalysis['Existencias'];
	    $TotEnTransito = $TotEnTransito + $InvAnalysis['EnTransito'];
	    $TotAutorizado = $TotAutorizado + $InvAnalysis['Autorizado'];
	    $TotPedVenta = $TotPedVenta + $InvAnalysis['pedventa'];
	    $TotPedCompra = $TotPedCompra + $InvAnalysis['pedcompra'];
		
	    //$TotCosto = $TotCosto + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']+$InvAnalysis['EnTransito']));
	    //$TotCostoNoAsig = $TotCostoNoAsig + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']+$InvAnalysis['EnTransito']-$InvAnalysis['pedventa']));
		//echo $_POST['DetailedReport'];
	    if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Prod') {
		  if ($InvAnalysis['controlled'] == 1 AND $InvAnalysis['serialised'] == 1 AND FALSE) {
			echo '<td class=peque>'.$InvAnalysis['stockid'].'</td>
			      <td class=peque >'.$InvAnalysis['description'].'</td>
			      <td class=pequenum>'.$InvAnalysis['tipo'].'</td>
			      <td class=pequenum>'.$InvAnalysis['areadescription'].'</td>
			      <td class=pequenum>'.$InvAnalysis['Existencias'].'</td>
			      <td class=pequenum>'.$InvAnalysis['EnTransito'].'</td>
			      <td class=pequenum>'.($InvAnalysis['Existencias']-$InvAnalysis['EnTransito']).'</td>
			      <td class=pequenum>'.$InvAnalysis['Autorizado'].'</td>
			      <td class=pequenum>'.$InvAnalysis['pedventa'].'</td>
			      <td class=pequenum>'.$InvAnalysis['pedcompra'].'</td>
			      <td class=pequenum>----</td>
			      <td class=pequenum>----</td>
			      </tr>';
				
			$SQL = "Select *
			      from locstock JOIN stockmaster ON locstock.stockid =  stockmaster.stockid
			      JOIN locations ON locstock.loccode = locations.loccode
			      JOIN tags ON locations.tagref = tags.tagref
			      JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			      JOIN areas ON tags.areacode = areas.areacode
			      JOIN regions ON areas.regioncode = regions.regioncode
			      JOIN stockserialitems ON locstock.stockid =  stockserialitems.stockid
				    AND locstock.loccode = stockserialitems.loccode
			      where stockserialitems.quantity>0 and locstock.stockid = '" . $InvAnalysis['stockid'] . "'
			      and areas.areacode= '" .  $InvAnalysis['areacode'] . "'
			      and stockserialitems.loccode = '".$InvAnalysis['loccode']."'";
				
			$SQL=$SQL." and (areas.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			      and (locations.tagref = ".$_POST['unidadnegocio']." or ".$_POST['unidadnegocio']."=0)
			      ".$sqlXtipoInventario;	
				  
			$result=DB_query($SQL,$db);
				    
				    
			while ($myrow=DB_fetch_array($result)){
			      echo '<tr>';
			      echo '<td colspan=2></td>
				    <td class="peque">'.$myrow['serialno'].'</td>
				    <td colspan=5 class=normnum></td>
				    <td class=normnum></td>
				    <td class=normnum></td>
				    <td class=pequenum>'.number_format($myrow['standardcost'],2).'</td>
				    <td class=pequenum>'.number_format($myrow['standardcost'],2).'</td>
				    </tr>';
				    $costoxserie = $myrow['standardcost'];
				    $ProdTotCosto = $ProdTotCosto + ($costoxserie);
				    $ProdTotCostoNoAsig = $ProdTotCostoNoAsig + ($costoxserie);
				    $TipoTotCosto = $TipoTotCosto + ($costoxserie);
				    $TipoTotCostoNoAsig = $TipoTotCostoNoAsig + ($costoxserie);
				    $LineaTotCosto = $LineaTotCosto + ($costoxserie);
				    $LineaTotCostoNoAsig = $LineaTotCostoNoAsig + ($costoxserie);
				    $GrupoTotCosto = $GrupoTotCosto + ($costoxserie);
				    $GrupoTotCostoNoAsig = $GrupoTotCostoNoAsig + ($costoxserie);
				    $TotCosto = $TotCosto + ($costoxserie);
				    $TotCostoNoAsig = $TotCostoNoAsig + ($costoxserie);			
			}
			
		  } else {
			//echo "entra";
			echo '<td class=peque>'.$InvAnalysis['stockid'].'</td>
			      <td class=peque >'.$InvAnalysis['description'].'</td>
			      <td class=pequenum>'.$InvAnalysis['tipo'].'</td>
			      <td class=pequenum>'.$InvAnalysis['areadescription'].'</td>
			      <td class=pequenum>'.$InvAnalysis['Existencias'].'</td>
			      <td class=pequenum>'.$InvAnalysis['EnTransito'].'</td>
			      <td class=pequenum>'.($InvAnalysis['Existencias']-$InvAnalysis['EnTransito']).'</td>
			      <td class=pequenum>'.$InvAnalysis['Autorizado'].'</td>
			      <td class=pequenum>'.$InvAnalysis['pedventa'].'</td>
			      <td class=pequenum>'.$InvAnalysis['pedcompra'].'</td>
			      <td class=pequenum>'.'$ '.number_format($InvAnalysis['CostoPromedio'],2).'</td>
			      <td class=pequenum>'.'$ '.number_format($InvAnalysis['CostoPromedio']*(/*$InvAnalysis['EnTransito']+$*/$InvAnalysis['Existencias']),2).'</td>
			      <td class=pequenum>'.'$ '.number_format($InvAnalysis['CostoPromedio']*(/*$InvAnalysis['EnTransito']+*/$InvAnalysis['Existencias']-$InvAnalysis['pedventa']),2).'</td>
			      </tr>';
			$ProdTotCosto = $ProdTotCosto + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']/*+$InvAnalysis['EnTransito']*/));
			$ProdTotCostoNoAsig = $ProdTotCostoNoAsig + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']/*+$InvAnalysis['EnTransito']*/-$InvAnalysis['pedventa']));
			$TipoTotCosto = $TipoTotCosto + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']/*+$InvAnalysis['EnTransito']*/));
			$TipoTotCostoNoAsig = $TipoTotCostoNoAsig + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']/*+$InvAnalysis['EnTransito']*/-$InvAnalysis['pedventa']));
			$LineaTotCosto = $LineaTotCosto + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']/*+$InvAnalysis['EnTransito']*/));
			$LineaTotCostoNoAsig = $LineaTotCostoNoAsig + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']/*+$InvAnalysis['EnTransito']*/-$InvAnalysis['pedventa']));
			$GrupoTotCosto = $GrupoTotCosto + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']/*+$InvAnalysis['EnTransito']*/));
			$GrupoTotCostoNoAsig = $GrupoTotCostoNoAsig + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']/*+$InvAnalysis['EnTransito']*/-$InvAnalysis['pedventa']));
			$TotCosto = $TotCosto + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']/*+$InvAnalysis['EnTransito']*/));
			$TotCostoNoAsig = $TotCostoNoAsig + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']/*+$InvAnalysis['EnTransito']*/-$InvAnalysis['pedventa']));			
		  }
	    } else {
		  echo '<td class=peque>'.$InvAnalysis['stockid'].'</td>
			<td class=peque >'.$InvAnalysis['description'].'</td>
			<td class=pequenum>'.$InvAnalysis['tipo'].'</td>
			<td class=pequenum>'.$InvAnalysis['areadescription'].'</td>
			<td class=pequenum>'.$InvAnalysis['Existencias'].'</td>
			<td class=pequenum>'.$InvAnalysis['EnTransito'].'</td>
			<td class=pequenum>'.($InvAnalysis['Existencias']-$InvAnalysis['EnTransito']).'</td>
			<td class=pequenum>'.$InvAnalysis['Autorizado'].'</td>
			<td class=pequenum>'.$InvAnalysis['pedventa'].'</td>
			<td class=pequenum>'.$InvAnalysis['pedcompra'].'</td>
			<td class=pequenum>'.'$ '.number_format($InvAnalysis['CostoPromedio'],2).'</td>
			<td class=pequenum>'.'$ '.number_format($InvAnalysis['CostoPromedio']*(/*$InvAnalysis['EnTransito']+*/$InvAnalysis['Existencias']),2).'</td>
			<td class=pequenum>'.'$ '.number_format($InvAnalysis['CostoPromedio']*(/*$InvAnalysis['EnTransito']+*/$InvAnalysis['Existencias']-$InvAnalysis['pedventa']),2).'</td>
			</tr>';
			$ProdTotCosto = $ProdTotCosto + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']/*+$InvAnalysis['EnTransito']*/));
			$ProdTotCostoNoAsig = $ProdTotCostoNoAsig + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']/*+$InvAnalysis['EnTransito']*/-$InvAnalysis['pedventa']));
			$TipoTotCosto = $TipoTotCosto + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']/*+$InvAnalysis['EnTransito']*/));
			$TipoTotCostoNoAsig = $TipoTotCostoNoAsig + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']/*+$InvAnalysis['EnTransito']*/-$InvAnalysis['pedventa']));
			$LineaTotCosto = $LineaTotCosto + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']/*+$InvAnalysis['EnTransito']*/));
			$LineaTotCostoNoAsig = $LineaTotCostoNoAsig + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']/*+$InvAnalysis['EnTransito']*/-$InvAnalysis['pedventa']));
			$GrupoTotCosto = $GrupoTotCosto + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']/*+$InvAnalysis['EnTransito']*/));
			$GrupoTotCostoNoAsig = $GrupoTotCostoNoAsig + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']/*+$InvAnalysis['EnTransito']*/-$InvAnalysis['pedventa']));
			$TotCosto = $TotCosto + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']/*+$InvAnalysis['EnTransito']*/));
			$TotCostoNoAsig = $TotCostoNoAsig + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']/*+$InvAnalysis['EnTransito']*/-$InvAnalysis['pedventa']));			
	    }
		
			
      } /*end while loop */
	   
      if ($lineasConMismoProducto >= 1) {
	    if ($primeraEntrada == 0) {
		  echo '<tr>';
		  echo '<td></td><td class=norm colspan=3><b>TOTAL '.$antProd.'</b></td>
			<td class=normnum>'.$ProdTotExistencias.'</td>
			<td class=normnum>'.$ProdTotEnTransito.'</td>
			<td class=normnum>'.($ProdTotExistencias+$ProdTotEnTransito).'</td>
			<td class=normnum>'.$ProdTotAutorizado.'</td>
			<td class=normnum>'.$ProdTotPedVenta.'</td>
			<td class=normnum>'.$ProdTotPedCompra.'</td>
			<td class=normnum></td>
			<td class=normnum>'.number_format($ProdTotCosto,2).'</td>
			<td class=normnum>'.number_format($ProdTotCostoNoAsig,2).'</td>
			</tr>';
	    }
      }
	
      if ($primeraEntrada == 0) {
	    echo '<tr>';
	    echo '<td class=norm colspan=4><b>- - TOTAL '.$antTipo.'</b></td>
		  <td class=pequenum>'.$TipoTotExistencias.'</td>
		  <td class=pequenum>'.$TipoTotEnTransito.'</td>
		  <td class=pequenum>'.($TipoTotExistencias+$TipoTotEnTransito).'</td>
		  <td class=pequenum>'.$TipoTotAutorizado.'</td>
		  <td class=pequenum>'.$TipoTotPedVenta.'</td>
		  <td class=pequenum>'.$TipoTotPedCompra.'</td>
		  <td class=pequenum></td>
		  <td class=pequenum>'.number_format($TipoTotCosto,2).'</td>
		  <td class=pequenum>'.number_format($TipoTotCostoNoAsig,2).'</td>
		  </tr>';
      }
	
      if ($primeraEntrada == 0) {
	    echo '<tr>';
	    echo '<td class=norm colspan=4><b>- TOTAL '.$antLinea.'</b></td>
		  <td class=pequenum>'.$LineaTotExistencias.'</td>
		  <td class=pequenum>'.$LineaTotEnTransito.'</td>
		  <td class=pequenum>'.($LineaTotExistencias+$LineaTotEnTransito).'</td>
		  <td class=pequenum>'.$LineaTotAutorizado.'</td>
		  <td class=pequenum>'.$LineaTotPedVenta.'</td>
		  <td class=pequenum>'.$LineaTotPedCompra.'</td>
		  <td class=pequenum></td>
		  <td class=pequenum>'.number_format($LineaTotCosto,2).'</td>
		  <td class=pequenum>'.number_format($LineaTotCostoNoAsig,2).'</td>
		  </tr>';			
      }
	
      if ($primeraEntrada == 0) {
	    echo '<tr>';
	    echo '<td class=norm colspan=4><b>TOTAL '.$antGrupo.'</b></td>
		  <td class=pequenum>'.$GrupoTotExistencias.'</td>
		  <td class=pequenum>'.$GrupoTotEnTransito.'</td>
		  <td class=pequenum>'.($GrupoTotExistencias+$GrupoTotEnTransito).'</td>
		  <td class=pequenum>'.$GrupoTotAutorizado.'</td>
		  <td class=pequenum>'.$GrupoTotPedVenta.'</td>
		  <td class=pequenum>'.$GrupoTotPedCompra.'</td>
		  <td class=pequenum></td>
		  <td class=pequenum>'.number_format($GrupoTotCosto,2).'</td>
		  <td class=pequenum>'.number_format($GrupoTotCostoNoAsig,2).'</td>
		  </tr>';
      }

	echo '<tr class="EvenTableRows">';
	/*DESPLIEGA VALOR DE SUBTOTAL X UN Y FOLIO*/
	printf('<td><b>d%s</b></td>
		<td><b>%s</b></td>
		<td><b>%s</b></td>
		<td><b>%s</b></td>
		<td><b>%s</b></td>
		<td><b>%s</b></td>
		<td><b>%s</b></td>
		<td><b>%s</b></td>
		<td><b>%s</b></td>
		<td><b>%s</b></td>
		<td><b>%s</b></td>
		<td><b>%s</b></td>
		<td><b>%s</b></td>
		</tr></table>',
		'TOTALES',
		'','','',
		number_format($TotExistencias,0),
		number_format($TotEnTransito,0),
		number_format($TotExistencias+$TotEnTransito,0),
		number_format($TotAutorizado,0),
		number_format($TotPedVenta,0),
		number_format($TotPedCompra,0),'',
		'$ '.number_format($TotCosto,2),
		'$ '.number_format($TotCostoNoAsig,2));
			
	if (isset($_POST['PrintEXCEL'])) {
		exit;
	}		
			
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

} else { /*The option to print PDF was not hit */

} /*end of else not PrintPDF */

include('includes/footer.inc');
?>
