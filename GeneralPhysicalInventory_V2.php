<?php
	ob_start("ob_gzhandler");
	include ('includes/session.inc');
	$funcion = 1827;
	include('includes/SQL_CommonFunctions.inc');
	$title = _('Inventario Fisico');

	if (!isset($_POST['excel'])) {
		include ('includes/header.inc');
	}
	include('includes/SecurityFunctions.inc');

	if(!isset($_SESSION['enproceso'])) {
		$_SESSION['enproceso'] = false;
	}

	function nextInventory($loccode) {
		global $db;

		$folio = "";
		$SQL = "SELECT id, loccode, sequence FROM systypepinventory WHERE loccode  = '" . $loccode . "'";
		$result = DB_query($SQL, $db);
		if(DB_num_rows($result) > 0) {
			$row = DB_fetch_row($result);
			$sequence = $row[2] + 1;
			$folio = $loccode . '-' . $sequence;
			$SQL = "UPDATE systypepinventory SET sequence = '" . $sequence . "' WHERE id = '" . $row[0] . "'";
			$result = DB_query($SQL, $db);
		} else {
			$SQL = "INSERT INTO systypepinventory (loccode, sequence) VALUES ('" . $loccode . "', 1)";
			$result = DB_query($SQL, $db);
			$folio = $loccode . '-1';
		}
		return $folio; 
	}


	$folio = "";
	if (isset($_POST['folio']) and !empty($_POST['folio']) ) {
		$folio = $_POST['folio'];
	}

	if ( isset($_GET['folio']) and !empty($_GET['folio']) ) {
		$folio = $_GET['folio'];
	}

	if(isset($_POST['procesarV2']) and $_POST['procesarV2'] == "1") {
		foreach ($_POST['acontar'] as $acontar) {

			if($_POST['cantidad_' . $acontar] != "" and is_numeric($_POST['cantidad_' . $acontar])) {
				// folio_lineitem
				$informacionlinea = explode('_', $acontar);
				$sqlinstock = "SELECT physicalinventorydetails.id,
									       physicalinventorydetails.instock,
									       physicalinventorydetails.count1,
									       physicalinventorydetails.count2,
									       physicalinventorydetails.count3, 
									       physicalinventorydetails.stockid
									FROM physicalinventorydetails
									WHERE physicalinventorydetails.folio = '" . $informacionlinea[0] . "'
								  AND physicalinventorydetails.lineitem = '" . $informacionlinea[1] . "'";
				$resultsqlinstock = DB_query($sqlinstock, $db);
				$row = DB_fetch_row($resultsqlinstock);
				$detailid = $row[0];
				$instock = $row[1];
				$conteo1 = $row[2];
				$stockid = $row[5];

				
				$terminarconteo = false;
				// Habilitar tercen conteo desde el reporte 1828 solo de ser necesario
				if($_POST['conteo_' . $acontar] == 3) {
					$terminarconteo = true;
				}

				if($_POST['conteo_' . $acontar] == 2) {
					// Se termina proceso de conteo
					// $terminarconteo = $_POST['cantidad_' . $acontar] == $conteo1;
					$terminarconteo = true;
				}

				if($instock == $_POST['cantidad_' . $acontar] or $_POST['cantidad_' . $acontar] == -2 or $terminarconteo) {
					$completo = 1;
				} else {
					$completo = 0;
				}

				if( $_POST['cantidad_' . $acontar] >= 0) {
					$adjustment = " adjustment = '" . ($_POST['cantidad_' . $acontar] - $instock) . "', ";
				} else {
					$adjustment = "";
				}

				$sqlupdatedetail = "UPDATE physicalinventorydetails 
									SET count" . $_POST['conteo_' . $acontar] . " = '" . $_POST['cantidad_' . $acontar] . "', 
										" . $adjustment . "
										countno = countno + 1, 
										inprocess = 0, 
										useridinprocess = '', 
										useridcount" . $_POST['conteo_' . $acontar] . " = '" . $_SESSION['UserID'] . "', 
										completed = '" . $completo . "' 
									WHERE id = '" . $detailid . "'";
				// echo '<br>';
				// echo '<pre>';
				// echo $sqlupdatedetail;
				// echo '</pre>';
				
				$resultsqlupdatedetail = DB_query($sqlupdatedetail, $db);

				if(!$resultsqlupdatedetail) {
					prnMsg('Ocurrio un error al procesar la cantidad.', 'error');
				}
			}
		}

		$sqlnextcount = "SELECT count(*) FROM physicalinventorydetails WHERE folio = '" . $folio . "' AND completed = 1";

		$resultsqlnextcount = DB_query($sqlnextcount, $db);
		$row = DB_fetch_row($resultsqlnextcount);
		$completados = $row[0];

		$sqlnextcount = "SELECT count(*) FROM physicalinventorydetails WHERE folio = '" . $folio . "'";

		$resultsqlnextcount = DB_query($sqlnextcount, $db);
		$row = DB_fetch_row($resultsqlnextcount);
		$totaldeproductos = $row[0];

		if($completados == $totaldeproductos) {
			// 2 = por ajustar
			$sqlcountno = "UPDATE physicalinventory SET status = '2' WHERE folio = '" . $folio . "'";
			$resultsqlcountno = DB_query($sqlcountno, $db);
			if($resultsqlcountno) {
				prnMsg(_('Se completo el proceso de conteo para el folio: ' . $folio),'sucess');
			} else {
				prnMsg('Ocurrio un error al terminar el proceso de conteo para el folio: ' . $folio, 'error');
			}
		} else {
			prnMsg('Se procesaron las cantidades del conteo.','sucess');
		}

		unset($_POST['linea']);
		unset($_POST['categoria']);
		unset($_POST['almacen']);
		unset($folio);
		$_SESSION['enproceso'] = false;
	}

	if(isset($_POST['iniciarconteo'])) {

		if(count($_POST['detalleid']) > 0) {
			$ids = array_map(function ($value) {
				return "'" . $value . "'";
			}, $_POST['detalleid']);
			$ids =  implode(',', $ids);
		}

		if(!empty($ids)) {

			$SQL = "SELECT count(*) AS enproceso
					FROM physicalinventorydetails
					WHERE id IN (" . $ids . ")
					  AND inprocess = 1";

			$resultenproces = DB_query($SQL, $db);
			$noenproc = DB_fetch_row($resultenproces);

			if($noenproc[0] == 0) {

				$SQL = "UPDATE physicalinventorydetails 
						SET physicalinventorydetails.inprocess = '1', 
							physicalinventorydetails.useridinprocess = '" . $_SESSION['UserID'] . "' 
						WHERE id IN (" . $ids . ")";
				$result = DB_query($SQL, $db);
				if($result) {
					$_SESSION['enproceso'] = true;
				} else {
					$_SESSION['enproceso'] = false;
				}

			} else {
				prnMsg('Existen productos que ya estan en proceso de conteo, solo un usuario a la ves puede realizar el conteo.', 'error');
			}
		}
	}

	if(isset($_POST['crear'])) {
		
		$folio = nextInventory($_POST['almacen']);
		$_POST['folio'] = $folio;
		$_GET['folio'] = $folio;

		$line = 1;

		$sqlinv = "INSERT INTO physicalinventorydetails ( folio, lineitem, stockid, prodlineid, categoryid, instock)
					SELECT '" . $folio . "',
					       @curRow := @curRow + 1 AS contador,
					       stockmaster.stockid,
					       ProdLine.Prodlineid,
					       stockcategory.categoryid,
					       locstock.quantity
					FROM locstock 
					JOIN (SELECT @curRow := 0) r
					INNER JOIN stockmaster ON stockmaster.stockid = locstock.stockid
					INNER JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
					LEFT JOIN ProdLine ON stockcategory.ProdLineId = ProdLine.Prodlineid
					WHERE locstock.loccode = '" . $_POST['almacen'] . "'
					  AND locstock.quantity > 0";

		// linea						  
		if(count($_POST['linea']) > 0) {
			$lineas = array_map(function ($value) {
				return "'" . $value . "'";
			}, $_POST['linea']);
			$lineas =  implode(',', $lineas);
			$sqlinv .= " AND ProdLine.Prodlineid IN (" . $lineas . ")";
		}

		// categoria
		if(count($_POST['categoria']) > 0) {
			$categorias = array_map(function ($value) {
				return "'" . $value . "'";
			}, $_POST['categoria']);
			$categorias =  implode(',', $categorias);
			$sqlinv .= " AND stockcategory.categoryid IN (" . $categorias . ")";
		}

		$sqlinv .= " ORDER BY ProdLine.Prodlineid, stockcategory.categoryid";

		$resultsqlhead = DB_query($sqlinv, $db);

		$sqlhead = "INSERT INTO physicalinventory (
								folio, 
								loccode, 
								until, 
								trandate, 
								prodlineid, 
								categoryid) 
					VALUES ('" . $folio . "', 
							'" . $_POST['almacen'] . "', 
							'" . $_POST['hasta'] . "', 
							'" . date('Y-m-d') . "', 
							'" . str_replace("'", "", str_replace(",", "|", $lineas)) . "', 
							'" . str_replace("'", "", str_replace(",", "|", $categorias)) . "')";
		$resultsqlhead = DB_query($sqlhead, $db);
		
	}

	$SQL = "SELECT locations.loccode,
			       locations.locationname
			FROM locations
			INNER JOIN sec_loccxusser ON locations.loccode=sec_loccxusser.loccode
			WHERE sec_loccxusser.userid='" . $_SESSION['UserID'] . "'
			ORDER BY locationname";
	$result = DB_query($SQL, $db);
	$almacen_option = '';
	while($rs = DB_fetch_array($result)) {
		if( isset($_POST['almacen']) and $rs['loccode'] == $_POST['almacen']) {
			$almacen_option .= '<option value="' . $rs['loccode'] . '" selected>' . $rs['locationname'] . '</option>';
		} else {
			$almacen_option .= '<option value="' . $rs['loccode'] . '">' . $rs['locationname'] . '</option>';
		}
	}

	$SQL = "SELECT Prodlineid, Description FROM ProdLine";
	$result = DB_query($SQL, $db);
	$lineas_option = '';
	while($rs = DB_fetch_array($result)) {
		if(isset($_POST['linea']) and in_array($rs['Prodlineid'], $_POST['linea'])) {
			$lineas_option .= '<option value="' . $rs['Prodlineid'] . '" selected>' . $rs['Description'] . '</option>';
		} else {
			$lineas_option .= '<option value="' . $rs['Prodlineid'] . '">' . $rs['Description'] . '</option>';
		}
	}

	$SQL = "SELECT sto.categoryid,
			       categorydescription
			FROM stockcategory sto,
			     sec_stockcategory sec
			WHERE sto.categoryid=sec.categoryid
			  AND userid='" . $_SESSION['UserID'] . "'
			ORDER BY sto.categoryid,
			         categorydescription";
	$result = DB_query($SQL, $db);
	$categorias_option = '';
	while($rs = DB_fetch_array($result)) {
		if(isset($_POST['categoria']) and in_array($rs['categoryid'], $_POST['categoria'])) {
			$categorias_option .= '<option value="' . $rs['categoryid'] . '" selected>' . $rs['categoryid'] . ' ' . $rs['categorydescription'] . '</option>';
		} else {
			$categorias_option .= '<option value="' . $rs['categoryid'] . '">' . $rs['categoryid'] . ' ' . $rs['categorydescription'] . '</option>';
		}
	}

	if (isset($_POST['excel'])) {

		$filename = !empty($folio) ? 'Inventario_Fisico_' . $folio : 'Inventario_Fisico';
		header("Content-Type: application/xls");    
		header("Content-Disposition: attachment; filename=$filename.xls");  
		header("Pragma: no-cache"); 
		header("Expires: 0");
	} else {
		echo '<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">';
		echo '<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">';
		echo '<link rel="stylesheet" href="css/bootstrap-multiselect.css" type="text/css">';
		echo '<link rel="stylesheet" type="text/css" href="css/sweetalert.css">';
	}

?>

<style type="text/css">
	.custom-panel {
		width:95%;
		margin: 30px auto;
		padding: 
	}

	#resultados {
		width: 95%;
		margin: 15px auto;
		padding: 10px 0 10px 0;
	}

	.tablehead th {
		background-color: #337AB7;
		color: #fff;
	}

	.factura td {
		font-size:0.9em;
		font-family:arial;
		font-weight: bold;
	}

	.totales td {
		background-color: #337AB7;
		color: #fff;
		font-size:0.9em;
		font-family:arial;
		font-weight: bold;
	}

	.row-padding {
		padding: 10px 0 10px 0;
		text-align: center;
	}

	.number {
		text-align: right;
	}

</style>

<?php

$panelfiltros = '<div class="panel panel-primary custom-panel">
	<div class="panel-heading">Seleccion de Filtros y Parametros Generales</div>
	<div class="panel-body">
		<form class="form-horizontal" action="GeneralPhysicalInventory_V2.php" method="POST" id="filtros" name="filtros">
			<div class="row row-padding">
				<div class="col-md-6">
			  		<div class="form-group form-group-sm">
				    	<label class="col-sm-6 control-label" for="almacen">Almacen:</label>
				    	<div class="col-sm-6">
				    		<div class="input-group">
				      			<select class="form-control input-sm" id="almacen" name="almacen">
									' . $almacen_option . '
						  		</select>
				      		</div>
				    	</div>
				  	</div>
			  	</div>
			  	<div class="col-md-6">
			  		<div class="form-group form-group-sm">
				    	<label class="col-sm-6 control-label" for="hasta">Hasta:</label>
				    	<div class="col-sm-6">
				    		<div class="input-group">
				      			<input type="text" class="form-control input-sm" name="hasta" id="hasta" placeholder="Fecha" value="' . date('Y-m-d') . '">
				      			<div class="input-group-addon"><span id="ihasta" class="glyphicon glyphicon-calendar" aria-hidden="true"></span></div>
				      		</div>
				    	</div>
				  	</div>
			  	</div>
			</div>
			<div class="row row-padding">
			  	<div class="col-md-6">
			  		<div class="form-group form-group-sm">
				    	<label class="col-sm-6 control-label" for="linea">Linea:</label>
				    	<div class="col-sm-6">
				    		<div class="input-group">
				      			<select class="form-control input-sm" name="linea[]" id="linea" multiple="multiple">
					      			' . $lineas_option . '
					      		</select>
				      		</div>
				    	</div>
				  	</div>
			  	</div>
			  	<div class="col-md-6">
			  		<div class="form-group form-group-sm">
				    	<label class="col-sm-6 control-label" for="categoria">Categoria:</label>
				    	<div class="col-sm-6">
				    		<div class="input-group">
					      		<select class="form-control input-sm" name="categoria[]" id="categoria" multiple="multiple">
					      			' . $categorias_option . '
					      		</select>
					      	</div>
				    	</div>
				  	</div>
			  	</div>
			</div>
			<div class="row row-padding">
			  	<div class="col-md-12" style="text-align: center;">
			  		<div class="form-group form-group-sm">
				    	<label class="col-sm-6 control-label" for="folio">Folio:</label>
				    	<div class="col-sm-6">
				    		<div class="input-group">
				      			<input type="text" class="form-control input-sm" name="folio" id="folio" placeholder="Folio" value="">
				      		</div>
				    	</div>
				  	</div>
			  	</div>
			</div>
			<div class="row">
			  	<div class="col-md-12" style="text-align: center;">
			  		<button type="submit" name="mostrar" class="btn btn-primary" style="width: 100px;"><span class="glyphicon glyphicon-list" aria-hidden="true"></span> Mostrar</button>
			  		' . ( (isset($_POST['mostrar'])) ? '<button type="submit" name="crear" class="btn btn-success" style="width: 100px;"><span class="glyphicon glyphicon-saved" aria-hidden="true"></span> Crear</button>' : '' )  .' 
			  	</div>
			</div>
		</form>
	</div>
</div>';

if( !empty($folio) or isset($_POST['excel'])) {


	$slqpinvhead = "SELECT physicalinventory.folio,
					       physicalinventory.loccode,
					       physicalinventory.until,
					       physicalinventory.status,
					       physicalinventory.trandate,
					       locations.locationname,
					       physicalinventory.prodlineid,
					       physicalinventory.categoryid
					FROM physicalinventory
					INNER JOIN locations ON locations.loccode = physicalinventory.loccode
					WHERE physicalinventory.folio = '" . $folio . "'";
	$resultslqpinvhead = DB_query($slqpinvhead, $db);
	$pinvhead = DB_fetch_row($resultslqpinvhead);
	$status = $pinvhead[3];
	$loccode = $pinvhead[1];
	$fecha = $pinvhead[2];
	$almacen = $pinvhead[5];

	$sqlenproceso = "SELECT count(physicalinventorydetails.inprocess) AS enproceso
					FROM physicalinventorydetails
					INNER JOIN physicalinventory ON physicalinventory.folio = physicalinventorydetails.folio
					INNER JOIN stockmaster ON stockmaster.stockid = physicalinventorydetails.stockid
					INNER JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
					INNER JOIN locations ON locations.loccode = physicalinventory.loccode
					LEFT JOIN ProdLine ON stockcategory.ProdLineId = ProdLine.Prodlineid
					WHERE physicalinventorydetails.folio = '".$folio."' 
					  AND physicalinventorydetails.inprocess = '1'";


	$sqlpinv = "SELECT physicalinventorydetails.folio,
				       physicalinventorydetails.lineitem,
				       physicalinventorydetails.countno,
				       physicalinventory.loccode,
				       locations.tagref, 
				       ProdLine.Prodlineid,
				       ProdLine.Description,
				       stockcategory.categoryid,
				       stockcategory.categorydescription,
				       physicalinventorydetails.stockid,
				       stockmaster.description AS productdescription,
				       physicalinventorydetails.instock,
				       physicalinventorydetails.id,
				       physicalinventorydetails.count1,
				       physicalinventorydetails.count2,
				       physicalinventorydetails.count3, 
				       physicalinventorydetails.adjustment, 
				       physicalinventorydetails.inprocess, 
				       physicalinventorydetails.useridinprocess, 
				       physicalinventorydetails.completed
				FROM physicalinventorydetails
				INNER JOIN physicalinventory ON physicalinventory.folio = physicalinventorydetails.folio
				INNER JOIN stockmaster ON stockmaster.stockid = physicalinventorydetails.stockid
				INNER JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
				INNER JOIN locations ON locations.loccode = physicalinventory.loccode 
				LEFT JOIN ProdLine ON stockcategory.ProdLineId = ProdLine.Prodlineid
				WHERE physicalinventorydetails.folio = '" . $folio . "'";

	if(isset($_POST['linea']) and count($_POST['linea']) > 0) {
		$lineas = array_map(function ($value) {
			return "'" . $value . "'";
		}, $_POST['linea']);
		$lineas =  implode(',', $lineas);
		$sqlpinv .= " AND ProdLine.Prodlineid IN (" . $lineas . ")";
		$sqlenproceso .= " AND ProdLine.Prodlineid IN (" . $lineas . ")";
	}

	// categoria
	if(isset($_POST['categoria']) and count($_POST['categoria']) > 0) {
		$categorias = array_map(function ($value) {
			return "'" . $value . "'";
		}, $_POST['categoria']);
		$categorias =  implode(',', $categorias);
		$sqlpinv .= " AND stockcategory.categoryid IN (" . $categorias . ")";
		$sqlenproceso .= " AND stockcategory.categoryid IN (" . $categorias . ")";
	}

	// $sqlpinv .= " ORDER BY ProdLine.Prodlineid, stockcategory.categoryid";
	$sqlpinv .= " ORDER BY physicalinventorydetails.folio, physicalinventorydetails.lineitem";
	$sqlenproceso .= " ORDER BY physicalinventorydetails.folio, physicalinventorydetails.lineitem";

	$resultsqlinv = DB_query($sqlpinv, $db);
	if(DB_num_rows($resultsqlinv) > 0) {

		$resultsqlenproceso = DB_query($sqlenproceso, $db);
		$filaenproceso = DB_fetch_row($resultsqlenproceso);
		if($filaenproceso[0] > 0) {
			$_SESSION['enproceso'] = true;
		}
		// 1 = En Conteo
		// 2 = Por Ajustar
		// 3 = Ajustado o Terminado
		switch ($status) {
			case 1:
				$labelconteo = 'En proceso de Conteo';
				$accion = '<input type="hidden" id="procesarV2" name="procesarV2" value="0" /> 
						<button type="submit" id="mostrarconteo" name="mostrarconteo" class="btn btn-primary" style="width: 100px;"><span class="glyphicon glyphicon-list" aria-hidden="true"></span> Filtrar</button> 
						<button type="submit" id="excel" name="excel" class="btn btn-primary" style="width: 100px;"><span class="glyphicon glyphicon-list" aria-hidden="true"></span> Excel</button>
						<br />
						<br />';
				if($_SESSION['enproceso']) {
					$accion .= '<button type="submit" id="procesar" class="btn btn-primary" style="width: 100px;"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Procesar</button>';
				} else {
					$accion .= '<button type="submit" name="iniciarconteo" class="btn btn-primary" style="width: 100px;"><span class="glyphicon glyphicon-play-circle" aria-hidden="true"></span> Iniciar</button>';
				}
				$accion .= '<br />
						<br />
						<a href="./GeneralPhysicalInventory_V2.php"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span> Nuevo Conteo</a>';
				break;
			case 2:
				$labelconteo = 'Generar Ajuste o Tercer Conteo';
				$accion = '<p class="text-primary"><a href="./ReportGeneralPhysicalInventory.php?&folio=' . $folio . '" target="_blank"><span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span> Ir al reporte 1828 para generar los ajustes necesarios o en su caso generar el tercer conteo.</a></p><br /><a href="./GeneralPhysicalInventory_V2.php"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span> Nuevo Conteo</a>';
				break;
			case 3:
				$labelconteo = 'Ajustes Completos';
				$accion = '<p class="text-primary"><a href="./ReportGeneralPhysicalInventory.php?&folio=' . $folio . '" target="_blank"><span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span> Ir al reporte 1828 para ver informacion detallada.</a></p><br /><a href="./GeneralPhysicalInventory_V2.php"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span> Nuevo Conteo</a>';
				break;
			default:
				$labelconteo = ':( Algo salio mal, esto jamas deberia suceder';
				$accion = '';
				break;
		}


		echo '<form class="form-horizontal" action="GeneralPhysicalInventory_V2.php" method="POST" id="conteo" name="conteo">';
		if(!isset($_POST['excel'])) {
			echo '<div class="panel panel-primary custom-panel">
				<div class="panel-heading">Inventario Fisico</div>
				<div class="panel-body">
						<div class="row row-padding">
							<div class="col-md-4">
						  		<p>Folio: <b>' . $folio . '</b></p>
						  	</div>
						  	<div class="col-md-4">
						  		<p><b>' . $labelconteo . '</b></p>
						  	</div>
						  	<div class="col-md-4">
						  		<p>Fecha: <b>' . $fecha . '</b></p>
						  	</div>
						</div>
						<div class="row row-padding">
							<div class="col-md-12">
						  		<p>Almacen: <b>' . $almacen . '</b></p>
						  	</div>
						</div>
						<div class="row row-padding">
						  	<div class="col-md-6">
						  		<div class="form-group form-group-sm">
							    	<label class="col-sm-6 control-label" for="linea">Linea:</label>
							    	<div class="col-sm-6">
							    		<div class="input-group">
							      			<select class="form-control input-sm" name="linea[]" id="linea" multiple="multiple">
								      			' . $lineas_option . '
								      		</select>
							      		</div>
							    	</div>
							  	</div>
						  	</div>
						  	<div class="col-md-6">
						  		<div class="form-group form-group-sm">
							    	<label class="col-sm-6 control-label" for="categoria">Categoria:</label>
							    	<div class="col-sm-6">
							    		<div class="input-group">
								      		<select class="form-control input-sm" name="categoria[]" id="categoria" multiple="multiple">
								      			' . $categorias_option . '
								      		</select>
								      	</div>
							    	</div>
							  	</div>
						  	</div>
						</div>
						<div class="row row-padding">
						  	<div class="col-md-12" style="text-align: center;">
						  		<input type="hidden" name="folio" value="' . $folio . '" />
						  		<input type="hidden" name="fecha" value="' . $fecha . '" />
						  		<input type="hidden" name="loccode" value="' . $loccode . '" />
						  		' . $accion  .'
						  	</div>
						</div>
				</div>
			</div>';
		}

		echo '<div id="resultados">
				<table class="table table-hover table-condensed">
					<thead>
						<tr class="tablehead">
							<th nowrap>#</th>
							<th nowrap>Folio</th>
							<th nowrap>Codigo</th>
							<th nowrap>Descripcion</th>
							<th nowrap>Categoria</th>';			
		
		if($status == 1) {
			echo '<th nowrap>Conteo</th>';
			echo '<th nowrap>Cantidad</th>';
		} 

							
		echo '</tr>
			</thead>
			<thbody>';
		$lines = 1;
		$ajustar = '';
		$rowbody = '';
		$totalgeneral = 0;

		while ($detalles = DB_fetch_array($resultsqlinv)) {

			$foliodetalle = $detalles['folio'] . '_'  . $detalles['lineitem'];

			$diferencia = $detalles['adjustment'];

			if($detalles['completed'] == 0 or $status != 1) {

				$rowbody = '<td>' . $lines . '<input type="hidden" name="acontar[]" value="' . $foliodetalle . '" /></td>
					<td nowrap>' . $detalles['folio'] . '-' . $detalles['lineitem'] . '</td>
					<td nowrap>' . $detalles['stockid'] . '</td>
					<td>' . $detalles['productdescription'] . '</td>
					<td nowrap>' . $detalles['categoryid'] . ' ' . $detalles['categorydescription'] . '</td>';

				if($status == 1) {
					$rowbody .= '<td>' . $detalles['countno'] . '<input type="hidden" name="conteo_' . $foliodetalle . '" value="' . $detalles['countno'] . '" /></td>';
					if($detalles['inprocess'] == 1 and $_SESSION['UserID'] != $detalles['useridinprocess']) {
						$rowbody .= '<td class="danger">EN PROCESO DE CONTEO<input type="hidden" name="detalleid[]" value="' . $detalles['id'] . '" /></td>';
					}
					else if($detalles['inprocess'] == 1) {
						if($detalles['countno'] == 3) {
							$soloconteo3 = 'value="' . $detalles['count2'] . '"';
						} else {
							$soloconteo3 = '';
						}
						$rowbody .= '<td><input type="text" class="cantidades" name="cantidad_' . $foliodetalle . '" placeholder="Ingresa Cantidad" ' . $soloconteo3 . '/></td>';
					} else {
						$rowbody .= '<td class="info">PENDIENTE POR INICIAR CONTEOS<input type="hidden" name="detalleid[]" value="' . $detalles['id'] . '" /></td>';
					}
				} 
				$lines++;

				if($diferencia != 0 and $status != 1) {
					$ajustar = 'class="warning"';
				} else {
					$ajustar = '';
				}
				echo '<tr ' . $ajustar . '>';
				echo $rowbody;
				echo '</tr>';
			}
		}

		if($lines == 1) {
			echo '<tr><td colspan="12" style="text-align:center;">Despues del ultimo conteo ya no se encontraron difrencias para los filtros dados</td></tr>';
		}
		
		echo '		</thbody>
				</table>
			</div>';
		echo '</form>';
	} else {
		echo $panelfiltros;
		echo '<div id="resultados" class="bg-warning">';
		echo '<p style="text-align: center; font-weight: bold;"> No existen registros para el folio: ' . $folio . ' </p>';
		echo '</div>';
	}
} else if (isset($_POST['mostrar'])) {

	if(!isset($_POST['excel'])) {
		echo $panelfiltros;
	}

	$sqlinv = "SELECT locstock.loccode,
				       stockmaster.stockid,
				       stockmaster.description AS codedescription,
				       ProdLine.Prodlineid,
				       ProdLine.Description,
				       stockcategory.categoryid,
				       stockcategory.categorydescription,
				       locstock.quantity
				FROM locstock
				INNER JOIN stockmaster ON stockmaster.stockid = locstock.stockid
				INNER JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
				LEFT JOIN ProdLine ON stockcategory.ProdLineId = ProdLine.Prodlineid
				WHERE locstock.loccode = '" . $_POST['almacen'] . "'
				  AND locstock.quantity > 0";

	// linea						  
	if(count($_POST['linea']) > 0) {
		$lineas = array_map(function ($value) {
			return "'" . $value . "'";
		}, $_POST['linea']);
		$lineas =  implode(',', $lineas);
		$sqlinv .= " AND ProdLine.Prodlineid IN (" . $lineas . ")";
	}

	// categoria
	if(count($_POST['categoria']) > 0) {
		$categorias = array_map(function ($value) {
			return "'" . $value . "'";
		}, $_POST['categoria']);
		$categorias =  implode(',', $categorias);
		$sqlinv .= " AND stockcategory.categoryid IN (" . $categorias . ")";
	}

	$sqlinv .= " ORDER BY ProdLine.Prodlineid, stockcategory.categoryid";
	
	$resultsqlinv = DB_query($sqlinv, $db);
	if(DB_num_rows($resultsqlinv) > 0) {
		echo '<div id="resultados">
				<table class="table table-hover table-condensed">
					<thead>
						<tr class="tablehead">
							<th>#</th>
							<th>Almacen</th>
							<th>Codigo</th>
							<th>Descripcion</th>
							<th>Linea</th>
							<th>Categoria</th>
						</tr>
					</thead>
					<thbody>';
		$lines = 1;
		while ($detalles = DB_fetch_array($resultsqlinv)) {
			echo '<tr>
					<td>' . $lines . '</td>
					<td>' . $detalles['loccode'] . '</td>
					<td>' . $detalles['stockid'] . '</td>
					<td>' . $detalles['codedescription'] . '</td>
					<td>' . $detalles['Description'] . '</td>
					<td>' . $detalles['categoryid'] . ' ' . $detalles['categorydescription'] . '</td>
				</tr>';
			$lines++;
		}
		echo '		</thbody>
				</table>
			</div>';
	} else {
		echo '<div id="resultados" class="bg-warning">';
		echo '<p style="text-align: center; font-weight: bold;"> No hay registros con los filtros seleccionados </p>';
		echo '</div>';
	}
} else {
	echo $panelfiltros;
}

if(!isset($_POST['excel'])) {

?>

<script type="text/javascript" src="javascripts/jquery-1.12.0.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<script type="text/javascript" src="javascripts/bootstrap-3.3.2.min.js"></script>
<script type="text/javascript" src="javascripts/bootstrap-multiselect.js"></script>
<script type="text/javascript" src="javascripts/bootstrap-multiselect-collapsible-groups.js"></script> 
<script type="text/javascript" src="javascripts/sweetalert.min.js"></script>
<script type="text/javascript">
	$(document).ready(function() {

		var multiselect_config = {
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true,
            includeSelectAllOption: true,
            maxHeight: 200,
            buttonWidth: '207px',
            nonSelectedText:"Seleccionar",
            selectAllText: "Seleccionar todos",
            numberDisplayed: 0
        };

        $('#linea').multiselect(multiselect_config);
        $('#categoria').multiselect(multiselect_config);
        $('#almacen').multiselect(multiselect_config);

        var datapicker_config = {
		    dateFormat: "yy-mm-dd",
		    language: "es"
		};

        $('#hasta').datepicker(datapicker_config);

        $('#ihasta').on('click', function() {
			$('#hasta').datepicker('show');        	
        });
    });

    $(function () {

	    $("#filtros").on('submit', function(e) {
	        var almacencount = $("#almacen :selected").length;
	        var categoriacount = $("#categoria :selected").length;
	        var lineacount = $("#linea :selected").length;
	        var folio = $("#folio").val();

	        if( (almacencount <= 0 || categoriacount <= 0 || lineacount <= 0) && folio == '') {
	        	sweetAlert("Oops...", "Debes seleccionar al menos una linea y categoria!", "error");
	        	e.preventDefault();
	        }
	    });


	    $('#filtros, #conteo').on('keyup keypress', function(e) {
		  	var keyCode = e.keyCode || e.which;
		  	if (keyCode === 13) { 
		    	e.preventDefault();
		    	return false;
		  	}
		});


	    $("#procesar").on('click', function(e) {

	    	e.preventDefault();
	    	var procesar = true;

	    	$('.cantidades').each(function(i, obj) {
			    if(obj.value == '' || obj.value < 0 || !$.isNumeric(obj.value)) {
			    	procesar = false;
			    }
			});

	    	if(!procesar) {
	    		sweetAlert("Oops...", "&iquest;Existen cantidades vacias, no validas o menores a 0, debes llenar todo antes de procesar.", "error");
	    	} else {
	    		swal({
	    			title: "Estas seguro que quieres procesar?",
	    			text: "",
	    			type: "warning",
	    			showCancelButton: true,
	    			confirmButtonColor: "#DD6B55",
	    			confirmButtonText: "Si, procesar!",
	    			closeOnConfirm: true
				},
				function(){
					$('#procesarV2').val("1");
					$('#procesar').html('<span class="glyphicon glyphicon-time" aria-hidden="true"></span>');
					$('#procesar').prop('disabled', true);
					$('#mostrarconteo').prop('disabled', true);
					$('#excel').prop('disabled', true);
					$('#conteo').submit();
				});
	    		
	    	}
	    });
    });
</script>

<?php

	include ('includes/footer.inc');
}
ob_end_flush();

?>
