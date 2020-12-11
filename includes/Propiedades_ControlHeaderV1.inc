<?php 
/* Elaboro: Jesus Guadalupe Vargas Montes
	Fecha Elaboracion: 31 de Mayo 2013  
	Descripcion: Contiene todas las operaciones sql de Propiedades_Control tanto para insertar como modificar o eliminar
 */
// sdsdsInserta el nuevo registro


if(isset($_POST['enviar'])){
	$ValidacionAlta = 0;
	if($_POST['proyecto'] == '*'){
		$ValidacionAlta = 1;
		prnMsg(_('Debe seleccionar un proyecto para la propiedad'),'error');
	}
	if ($ValidacionAlta <= 0){
		$FechaVencimiento = $_POST['FromYear'].'-'.$_POST['FromMes'].'-'.$_POST['FromDia'];
		$fechaactual = date("Y-m-d");
		$SQL = "SELECT MAX(prdpropiedad.numeropropiedad) 
				FROM prdpropiedad 
				WHERE prdpropiedad.u_proyecto =".$_POST['u_proyecto'];
		$result = DB_query($SQL,$result);
		while($myrow = DB_fetch_array($result)){
			$numeropropiedad = $myrow['numeropropiedad'];
		}
		$numeropropiedad = $numeropropiedad + 1;
		$SQL = "INSERT INTO prdpropiedad (u_proyecto, tipopropiedad, manzanapropiedad, lotepropiedad,
				modelopropiedad, direccionpropiedad, supterrenopropiedad, supconstruidopropiedad, precioventapropiedad, fechaaltapropiedad,
				fechavencimiento, estatuspropiedadid,confirmadopropiedad, estatusprospectopropiedadid,numeropropiedad,numerooficial,StockId)
		Values ('".$_POST['proyecto']."', '".$_POST['tipopropiedad']."', '".$_POST['manzanapropiedad']."',
				'".$_POST['lotepropiedad']."','".$_POST['modelopropiedad']."','".$_POST['direccionpropiedad']."','".$_POST['supterrenopropiedad']."',
				'".$_POST['supconstruidopropiedad']."','".$_POST['precioventapropiedad']."','".$fechaactual."','".$FechaVencimiento."','".$_POST['EstatusPropiedad']."',
				1,3,'".$numeropropiedad."','".$_POST['numerooficial']."','".$_POST['StockID']."')";
		$ErrMsg = _('La inserccion de la propiedad fracaso porque');
		prnMsg( _('La propiedad').' ' .$_POST['nombrepropiedad'] . ' ' . _('se ha creado exitosamente...!'),'info');
		$result = DB_query($SQL,$db,$ErrMsg);
		if ($_POST['chkproducto'] == 1){
		////////////////////////////////////////////////////////////
		$sql = "INSERT INTO stockmaster (
		stockid,
		description,
		longdescription,
		categoryid,
		units,
		mbflag,
		discontinued,
		controlled,
		serialised,
		taxcatid,
		idclassproduct,
		taxcatidret
		)
		VALUES ('".$_POST['StockID']."',
		'" . $_POST['Description'] . "',
		'" . $_POST['LongDescription'] . "',
				'" . $_POST['CategoryID'] . "',
							'" . $_POST['Units'] . "',
									'" . $_POST['MBFlag'] . "',
									" . $_POST['Discontinued'] . ",
							" . $_POST['Controlled'] . ",
									" . $_POST['Serialised']. ",
									" . $_POST['TaxCat'] . ",
									'" . $_POST['IDclassproduct'] . "',
							'" . $_POST['TaxCatRet'] . "'
									)";
		
									$ErrMsg =  _('El codigo de producto no se puede agregar');
				$DbgMsg = _('El SQL utilizado es');
				//echo '<pre>'.$sql;
				$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);
						if (DB_error_no($db) ==0) {
		
						$sql = "INSERT INTO locstock (loccode,
													stockid)
										SELECT locations.loccode,
										'" . $_POST['StockID'] . "'
								FROM locations";
		
					$ErrMsg =  _('El codigo') . ' ' .$_POST['StockID'] .  ' ' . _('no se agrego a los almacenes');
					$DbgMsg = _('El SQL utilizado es');
					$InsResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);
								
							$sql = "INSERT INTO stockcostsxlegal (lastupdatedate, stockid, lastcost, avgcost, legalid)
										SELECT NOW(),'" .$_POST['StockID']. "',0,0,legalbusinessunit.legalid
										FROM legalbusinessunit";
		
					$ErrMsg =  _('El codigo') . ' ' . $_POST['StockID'].  ' ' . _('no se agrego a tabla de stockcostsxlegal');
					$DbgMsg = _('El SQL utilizado es');
					$InsResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);
								
		$sql = "INSERT INTO stockcostsxtag (lastupdatedate, stockid, lastcost, avgcost, tagref)
										SELECT NOW(),'" . $_POST['StockID']. "',0,0,tags.tagref
												FROM tags";
		
					$ErrMsg =  _('El codigo') . ' ' . $_POST['StockID'] .  ' ' . _('no se agrego a tabla de stockcostsxtag');
					$DbgMsg = _('El SQL utilizado es');
							$InsResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);
						}
		////////////////////////////////////////////////////////////
	}
	}
}elseif($_GET['Oper'] == 'OpendMovimiento'){
	$SQL = "UPDATE prdpropiedad SET
 					prdpropiedad.confirmadopropiedad = 0
			Where prdpropiedad.idpropiedad ='".$_GET['idpropiedad']."'
			AND prdpropiedad.u_proyecto ='".$_GET['u_proyecto']."'";
	$result = DB_query($SQL,$db);
}elseif($_GET['Oper'] == 'CloseMovimiento'){
	$SQL = "UPDATE prdpropiedad SET
 					prdpropiedad.confirmadopropiedad = 1
			Where prdpropiedad.idpropiedad ='".$_GET['idpropiedad']."'
			AND prdpropiedad.u_proyecto ='".$_GET['u_proyecto']."'";
	$result = DB_query($SQL,$db);
}elseif(isset($_POST['procesar'])){
	if($_POST['Oper'] == 'cambiaMontos'){
		$arraycheckbox = $_POST['modifica'];
		for ($contmod=0;$contmod<Count($arraycheckbox);$contmod++){
			$IdpropiedadCont = $arraycheckbox[$contmod];
			//echo 'valor'.$IdpropiedadCont;
			$SQL = "UPDATE prdpropiedad
						SET nombrepropiedad='" .$_POST['nompromod_'.$IdpropiedadCont]." ',
							tipopropiedad= '".$_POST['tpopromod_'.$IdpropiedadCont]."',
							manzanapropiedad='".$_POST['manpromod_'.$IdpropiedadCont]."',
							lotepropiedad='" .$_POST['lotpromod_'.$IdpropiedadCont]." ',
							modelopropiedad= '".$_POST['modpromod_'.$IdpropiedadCont]."',
							supterrenopropiedad='".$_POST['supterpromod_'.$IdpropiedadCont]."',
							supconstruidopropiedad='" .$_POST['supconpromod_'.$IdpropiedadCont]." ',
							precioventapropiedad= '".$_POST['prevenpromod_'.$IdpropiedadCont]."',
							fechavencimiento='".$fechavencimientofor."',
							estatuspropiedadid= '".$_POST['EstatusPropiedadMod_'.$IdpropiedadCont]."',
						    direccionpropiedad='".$_POST['dirpromod_'.$IdpropiedadCont]."',
						    numerooficial = '".$_POST['numofipromod_'.$IdpropiedadCont]."',
							confirmadopropiedad = 1
						WHERE idpropiedad='".$IdpropiedadCont."'";
			$result = DB_query($SQL, $db);
		}
	}//prueba
	if($_POST['Oper'] == "CierMovimiento"){
		$arraycheckbox = $_POST['modifica'];
		for ($contmod=0;$contmod<Count($arraycheckbox);$contmod++){
			$IdpropiedadCont = $arraycheckbox[$contmod];
			$SQL = "UPDATE prdpropiedad
						SET confirmadopropiedad = 1
						WHERE idpropiedad='".$IdpropiedadCont."'";
			$result = DB_query($SQL,$db);
		}
	}
	if($_POST['Oper'] == "AbreMovimiento"){
		$arraycheckbox = $_POST['modifica'];
		for ($contmod=0;$contmod<Count($arraycheckbox);$contmod++){
			$IdpropiedadCont = $arraycheckbox[$contmod];
			$SQL = "UPDATE prdpropiedad
						SET confirmadopropiedad = 0
						WHERE idpropiedad='".$IdpropiedadCont."'";
			$result = DB_query($SQL,$db);
		}
	}
}
if($_GET['Oper'] == "sigestatus"){
	$estatusprospectopropiedadid = $_GET['estatusprospectopropiedadid'] + 1;
	$SQL = "SELECT prdestatusprospect_propiedad.estatusfinal,
					prdestatusprospect_propiedad.estatusprospectopropiedadid,
					prdestatusprospect_propiedad.estatusapartado 
			FROM prdestatusprospect_propiedad
			WHERE prdestatusprospect_propiedad.estatusprospectopropiedadid ='".$estatusprospectopropiedadid."'";
	//echo $SQL;
	$result = DB_query($SQL,$db);
	while($myrow = DB_fetch_array($result)){
		$finalestatus = $myrow['estatusfinal'];
		$estatusid = $myrow['estatusprospectopropiedadid'];
		$flagapartado = $myrow['estatusapartado'];
	}
	if($estatusi== ""){
		$estatusprospectopropiedadid = $estatusid + 1;
		$SQL = "SELECT prdestatusprospect_propiedad.estatusfinal,
					prdestatusprospect_propiedad.estatusprospectopropiedadid
					prdestatusprospect_propiedad.estatusapartado 
			FROM prdestatusprospect_propiedad
			WHERE prdestatusprospect_propiedad.estatusprospectopropiedadid ='".$estatusprospectopropiedadid."'";
		echo $SQL;
		$result = DB_query($SQL,$db);
		while($myrow = DB_fetch_array($result)){
			$finalestatus = $myrow['estatusfinal'];
			$estatusid = $myrow['estatusprospectopropiedadid'];
			$flagapartado = $myrow['estatusapartado'];
		}
	}
	//echo $finalestatus;
	if ($finalestatus == 1){
		$SQL = "UPDATE prdpropiedad
						SET estatuspropiedadid = 4,
							estatusprospectopropiedadid = '".$estatusid."'
						WHERE idpropiedad='".$_GET['idpropiedad']."'";
		//echo '<pre>'.$SQL;
		$result = DB_query($SQL, $db);
	}elseif ($finalestatus == 0){
		if($flagapartado == 1){
			$SQL = "UPDATE prdpropiedad
						SET estatuspropiedadid = 3
							estatusprospectopropiedadid = '".$estatusid."'
						WHERE idpropiedad='".$_GET['idpropiedad']."'";
			//echo $SQL;
			$result = DB_query($SQL, $db);
		}else{
			$SQL = "UPDATE prdpropiedad
						SET estatusprospectopropiedadid = '".$estatusid."'
						WHERE idpropiedad='".$_GET['idpropiedad']."'";
			//echo $SQL;
			$result = DB_query($SQL, $db);
		}
	}
}
if($_GET['Oper'] == "canestatus"){
	$SQL = "SELECT prdestatusprospect_propiedad.estatusprospectopropiedadid
			FROM prdestatusprospect_propiedad
			WHERE prdestatusprospect_propiedad.estatusfinal = 2";
	$result = DB_query($SQL,$db);
	while($myrow = DB_fetch_array($result)){
		$estatusid = $myrow['estatusprospectopropiedadid'];
	}
	$SQL = "UPDATE prdpropiedad
						SET estatuspropiedadid = 1,
							estatusprospectopropiedadid = '".$estatusid."'
						WHERE idpropiedad='".$_GET['idpropiedad']."'";
		$result = DB_query($SQL, $db);
	$SQL = "SELECT pm.u_movimiento
			FROM prospect_movimientos pm
			WHERE pm.idpropiedad ='".$_GET['idpropiedad']."'";
	$result = DB_query($SQL, $db);
	while($myrow = DB_fetch_array($result)){
		$umovimiento = $myrow['u_movimiento'];
	}
	$SQL = "UPDATE prospect_movimientos 
			SET prospect_movimientos.idpropiedad = 0,
				prospect_movimientos.idstatus = 5
			WHERE prospect_movimientos.u_movimiento ='".$umovimiento."'";
	$result = DB_query($SQL,$db);
}
if($_GET['Oper'] == "EliminarMovimiento"){
	$SQL = "DELETE FROM prdpropiedad where prdpropiedad.idpropiedad='".$_GET['idpropiedad']."'";
	$result = DB_query($SQL, $db);
}
if($_POST['Oper'] == "elimMovimiento"){
	$arraycheckbox = $_POST['modifica'];
	for ($contmod=0;$contmod<Count($arraycheckbox);$contmod++){
		$IdpropiedadCont = $arraycheckbox[$contmod];
		$SQL = "DELETE FROM prdpropiedad where prdpropiedad.idpropiedad='".$IdpropiedadCont."'";
		$result = DB_query($SQL,$db);
	}
}
?>