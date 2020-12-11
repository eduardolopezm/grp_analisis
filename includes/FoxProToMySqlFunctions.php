<?php
function cerosizq($numero,$ceros){
	$dif_diez = $ceros - strlen($numero);
	for($m = 0 ; $m < $dif_diez;$m++)
	{
	@$insertar_ceros .= 0;
	}
	return $insertar_ceros .= $numero;
}

function obtenernivelmayor($id, $nivelmax, $tablagral, $dblocal, $db){
	$sql = "SELECT PRE_NIVEL, PRE_IDUNI
            FROM `" . $tablagral . "1`
            WHERE PRE_IDPAD = '" . $id . "'
            ORDER BY PRE_NIVEL DESC
            LIMIT 1";
	$Result = mysqli_query($dblocal, $sql);
	if($myrow = mysqli_fetch_array($Result)) {
		$nivel = $myrow['PRE_NIVEL'];
		$id = $myrow['PRE_IDUNI'];
		//echo "<br>" . $id . " - " . $nivel;
		//exit;
		$nivelmayor = obtenernivelmayor($id,$nivel, $tablagral, $dblocal, $db);
	}else{
		$nivelmayor = $nivelmax;
	}
	 
	return $nivelmayor;
}

function siguientenivel($cveprodorigen, $stockid, $tablagral, $sufijo, $nivel, $dblocal,$db){
	$sql2 = "SELECT t5.PREF as PREFIJO, t5.NOMBRE, t5.COMPONENTE AS COMPONE, ROUND((t5.CANTIDAD),5) AS CANTIDAD,
                            ROUND(t5.TOTALMN,5) AS PRECIOMN, tp.PREFIJO, tp.NOMBRE, tp.UNIDAD, tp.DESCRIPCIO, tp.DESCCORTA,
                            tp.ELE_GRUPO, IFNULL(tn.OO,0) as OO, (1+(tn.INDIRECTOS/100)) as INDIRECTOS, (1+(tn.UTILIDAD/100)) AS UTILIDAD
                    FROM `" . $tablagral . "F` AS t5
                            LEFT JOIN `" . $tablagral . "P` AS tp ON t5.COMPONENTE = tp.NOMBRE
                            LEFT JOIN `" . $tablagral . "N` AS tn ON t5.NOMBRE = tn.NOMBRE
                    WHERE t5.NOMBRE = '" . $cveprodorigen . "'
                            AND tp.nombre IS NOT NULL";
	//echo "<br>" . $sql2;
	//exit;
	$Result2 = mysqli_query($dblocal, $sql2);
	$costototal = 0;

	if ( DB_num_rows($Result2) != 0 ) {
		while($myrow2 = mysqli_fetch_array($Result2)) {
			$cveproducto = $myrow2['COMPONE'];
			$stockid2 = str_replace("'","",$myrow2['COMPONE'] . $sufijo . "_" . $nivel);
			$stockid2 = str_replace("'","",$stockid2);
			$stockid2 = str_replace('"','pulg',$stockid2);

			//$categoryid2 = $myrow2['ELE_GRUPO'];
			$description2 = str_replace("'","",$myrow2['DESCCORTA']);
			$longdescription2 = str_replace("'","",$myrow2['DESCRIPCIO']);

			$description2 = str_replace('"','pulg',$description2);
			$longdescription2 = str_replace('"','pulg',$longdescription2);

			$units2 = $myrow2['UNIDAD'];
			$cantidad2 = $myrow2['CANTIDAD'];
			$costo2 = $myrow2['PRECIOMN'];
			$oo = $myrow2['OO'];
			$indirectos = $myrow2['INDIRECTOS'];
			$utilidad = $myrow2['UTILIDAD'];

			switch ($myrow2['PREFIJO']) {
				case '1': //PRODUCTO TERMINADO
					$mbflag2 = "B";
					break;
				case '2': //MANO DE OBRA
					$mbflag2 = "D";
					break;
				case '4': //HERRAMIENTA
					$mbflag2 = "B";
					break;
				default:
					$mbflag2 = "B";
			}

			$sqlx = "SELECT *
                            FROM `" . $_POST['presupuesto'] . "F` AS t5
                            WHERE t5.NOMBRE = '" . $cveproducto . "'";
			//echo "<br>" . $sqlx;

			$Resultx = mysqli_query($dblocal, $sqlx);
			if ( DB_num_rows($Resultx) > 0 ) {
				$mbflag2 = "M";
			}

			$sqlcat = "SELECT * FROM stockcategory WHERE categoryid = '" . $myrow2['PREFIJO'] . "'";
			$Resultcat = DB_query($sqlcat,$db);

			if($myrowcat = DB_fetch_array($Resultcat,$db)) {
				$categoryid2 = $myrowcat['categoryid'];
			}

			/*OBTENER UNIDAD DE MEDIDA*/
			$sqlumed = "SELECT * FROM unitsofmeasure WHERE unitname = '" . $units2 . "'";
			$Resultumed = DB_query($sqlumed, $db);

			if($myrowumed = DB_fetch_array($Resultumed,$db)) {
				$units2 = $myrowumed['unitname'];
			}else{
				$isqlumed = "INSERT INTO unitsofmeasure(unitid, unitname)
                            VALUES(NULL, '" . $units2 . "')";
				$iResultumed = DB_query($isqlumed, $db);

			}

			/*INSERTA PRODUCTO*/
			$sqlpro = "SELECT * FROM stockmaster WHERE stockid = '" . $stockid2 . "'";
			$Resultpro = DB_query($sqlpro, $db);

			if($myrowpro = DB_fetch_array($Resultpro,$db)) {
				$stockid2 = $myrowpro['stockid'];

				$usql = "UPDATE stockmaster
                                SET longdescription = '" . $longdescription2 . "',
                                    description = '" . $description2 . "',
                                    mbflag = '" . $mbflag2 . "',
                                    decimalplaces = '4', categoryid = '" . $categoryid2 . "' WHERE stockid='" . $stockid2 . "'";
				$uResult = DB_query($usql, $db);

				$dsql = "DELETE FROM stockcostsxlegal WHERE stockid = '" . $stockid2 . "'";
				$dResult = DB_query($dsql, $db);

				$isql = "INSERT INTO stockcostsxlegal(legalid, stockid, lastcost, avgcost, lastpurchase, lastpurchaseqty, lastupdatedate, trandate, Comments,
                            id, antiguedadHistorica, antiguedadFecha, antiguedadDisponible, pepscost, highercost, qty, nuevocosto, nivelmin, nivelmax)
                                SELECT legalid, '" . $stockid2 . "', 0 as lastcost, 0 as avgcost, Now() as lastpurchase, 0 as lastpurchaseqty,
                                    Now() as lastupdatedate, Now() as trandate, NULL as Comments, NULL as id, NULL as antiguedadHistorica, NULL as antiguedadFecha,
                                    NULL as antiguedadDisponible, NULL as pepscost, 0 as highercost, 0 as qty, 0 as nuevocosto, 1 as nivelmin, 1 as nivelmax
                                FROM legalbusinessunit";
				$iResult = DB_query($isql, $db);

				$isql = "INSERT INTO locstock(loccode, stockid, quantity, reorderlevel, ontransit, quantityv2, localidad, minimumlevel, timefactor, delay, qtybysend,
                            quantityprod, loccode_aux)
                                (SELECT locations.loccode,  '" . $stockid2 . "', 0 as quantity, 0 as reorderlevel, 0 as ontransit, 0 as quantityv2, NULL as localidad,
                                    0 as minimunlevel, 0 as timefactor, 0 as delay, 0 as qtybysend, 0 as quantityprod, '' as loccode_aux
                                FROM locations
                                    LEFT JOIN locstock ON locations.loccode = locstock.loccode and locstock.stockid = '" . $stockid2 . "'
                                WHERE locstock.stockid is null
                                )";
				$iResult = DB_query($isql, $db);
			}else{
				$isql = "INSERT INTO stockmaster (stockid, spes, categoryid, description, longdescription, manufacturer, stockautor, units, mbflag, lastcurcostdate,
                        actualcost, lastcost, materialcost, labourcost, overheadcost, lowestlevel, discontinued, controlled, eoq, volume,
                        kgs, barcode, discountcategory, taxcatid, taxcatidret, serialised, appendfile, perishable, decimalplaces, nextserialno,
                        pansize, shrinkfactor, netweight, idclassproduct, stocksupplier, securitypoint, pkg_type, idetapaflujo, flagcommission, fijo,
                        fecha_modificacion, stockupdate, isbn, grade, subject, deductibleflag, u_typeoperation, typeoperationdiot, height, width,
                        large, fichatecnica, percentfactorigi, OrigenCountry, OrigenDate, inpdfgroup, flagadvance)
                        VALUES ('" . $stockid2 . "', '', '" . $categoryid2 . "', '" . $description2 . "', '" . $longdescription2 . "', 'De prueba', '0', '" . $units2 . "', '" . $mbflag2 . "', Now(),
                        '0', '0', '0', '0', '0', '0', '0', '0', '0', '0',
                        '0', '', '', '0', '0', '0', '0', '0', '4', '0',
                        '0', '0', '0', 'normal', NULL, NULL, '0', '0', '0', '0',
                        Now(), '0', NULL, NULL, NULL, '0', '0', '0', '0', '0',
                        '0', '', NULL, NULL, NULL, '0', '0')";

				$iResult = DB_query($isql, $db);

				$isql = "INSERT INTO stockcostsxlegal(legalid, stockid, lastcost, avgcost, lastpurchase, lastpurchaseqty, lastupdatedate, trandate, Comments,
                            id, antiguedadHistorica, antiguedadFecha, antiguedadDisponible, pepscost, highercost, qty, nuevocosto, nivelmin, nivelmax)
                                (
                                SELECT legalid, '" . $stockid2 . "', 0 as lastcost, 0 as avgcost, Now() as lastpurchase, 0 as lastpurchaseqty,
                                    Now() as lastupdatedate, Now() as trandate, NULL as Comments, NULL as id, NULL as antiguedadHistorica, NULL as antiguedadFecha,
                                    NULL as antiguedadDisponible, NULL as pepscost, 0 as highercost, 0 as qty, 0 as nuevocosto, 1 as nivelmin, 1 as nivelmax
                                FROM legalbusinessunit)";
				$iResult = DB_query($isql, $db);

				$isql = "INSERT INTO locstock(loccode, stockid, quantity, reorderlevel, ontransit, quantityv2, localidad, minimumlevel, timefactor, delay, qtybysend,
                            quantityprod, loccode_aux)
                                (SELECT locations.loccode,  '" . $stockid2 . "', 0 as quantity, 0 as reorderlevel, 0 as ontransit, 0 as quantityv2, NULL as localidad,
                                    0 as minimunlevel, 0 as timefactor, 0 as delay, 0 as qtybysend, 0 as quantityprod, '' as loccode_aux
                                FROM locations
                                    LEFT JOIN locstock ON locations.loccode = locstock.loccode and locstock.stockid = '" . $stockid2 . "'
                                WHERE locstock.stockid is null
                                )";
				$iResult = DB_query($isql, $db);
			}

			/*FORMA EL PRODUCTO ENSAMBLADO*/

			$sqlbom = "SELECT * FROM bom WHERE parent = '" . $stockid . "' and component = '" . $stockid2 . "'";
			$Resultbom = DB_query($sqlbom, $db);

			if($myrowbom = DB_fetch_array($Resultbom,$db)) {
				$costototal = $costototal + ($cantidad2*$costo2);
			}else{
				if ($myrow2['UNIDAD'] == '(%)mo'){
					//$categoryid2 = "2";
					$categoryid2 = "AGREG";
					$percent = ($cantidad2*$costo2)/$oo;
					$cantidad2 = 0;
					$costo2 = 0;
				}else{
					$categoryid2 = 'All';
					$percent = 0;
				}
				$isql = "INSERT INTO bom(u_bom, parent, component, workcentreadded, loccode, effectiveafter, effectiveto, quantity, autoissue, categoryid,
                                percent, factortiempo, costoUnitario)
                            VALUES (NULL, '" . $stockid . "', '" . $stockid2 . "', '001', '', Now(), Now(), '" . $cantidad2 . "', '0', '" . $categoryid2 . "',
                                '" . $percent . "', 0, '" . $costo2 . "') ";
				$iResult = DB_query($isql, $db);
				$costototal = $costototal + ($cantidad2*$costo2);

			}
			//echo "<br>***" . $cveproducto . "***" . $stockid2 . "***" . $sufijo;
			siguientenivel($cveproducto, $stockid2, $tablagral, $sufijo, ($nivel+1), $dblocal,$db);
			
			
			
		}//TERMINA WHILE
	} //TERMINA IF
}

function ImportarFoxProToMySQL4($id, $inivel, $tablagral, $sufijo, $dblocal,$db){
	//OBTENRMOS EL NIVEL MAYOR
	/*
	$sql = "SELECT PRE_NIVEL
            FROM `" . $tablagral . "1`
            ORDER BY PRE_NIVEL DESC
            LIMIT 1";
	$Result = mysqli_query($dblocal, $sql);
	if($myrow = mysqli_fetch_array($Result)) {
		$nivel = $myrow['PRE_NIVEL'];
	}*/

	//$sufijo = '_COND33';
	//$sufijo = '_JOC1';
	//OBTENEMOS LOS PRODUCTOS QUE VAN A SER EL TIPO ENSAMBLADO

	$sql = "SELECT t1.PRE_ID, t1.PRE_IDUNI, t1.PRE_NIVEL, t1.PRE_IDPAD, t1.PRE_COM, t1.PRE_VOL, t1.PRE_PMN,
                tp.PREFIJO, tp.NOMBRE, tp.UNIDAD, tp.DESCRIPCIO, tp.DESCCORTA, tp.ELE_GRUPO
            FROM `" . $tablagral . "1` as t1
                LEFT JOIN `" . $tablagral . "P` as tp ON t1.PRE_COM = tp.nombre
                LEFT JOIN `" . $tablagral . "1` as t2  ON t1.PRE_IDPAD = t2.PRE_IDUNI
                LEFT JOIN `" . $tablagral . "A` as ta2 ON t2.PRE_COM = ta2.NOMBRE
            WHERE t1.PRE_NIVEL = '" . $inivel . "'
            	AND t1.PRE_IDUNI = '" . $id  . "' 
                AND tp.NOMBRE is not null
                and ta2.precio <> 0";
	echo "<br>sql: " . $sql;
	//exit;
	$Result = mysqli_query($dblocal, $sql);
	$costototal = 0;
	while($myrow = mysqli_fetch_array($Result)) {

		$nivel = $myrow['PRE_NIVEL'];
		//$stockid = $myrow['PRE_COM'];
		$claveproducto = $myrow['PRE_COM'];
		$stockid = str_replace("'","",$myrow['PRE_COM']) . "_" . $myrow['PRE_NIVEL'];

		if (is_numeric($myrow['PRE_COM'])){
			$stockid = cerosizq($myrow['PRE_COM'],4) . $sufijo . "_" . $myrow['PRE_NIVEL'];
		}else{
			$stockid = str_replace("'","",$myrow['PRE_COM']) . $sufijo . "_" . $myrow['PRE_NIVEL'];
		}

		$stockid = str_replace("'","",$stockid);
		$stockid = str_replace('"','pulg',$stockid);

		//$categoryid = $myrow['ELE_GRUPO'];
		$description = str_replace("'","",$myrow['DESCCORTA']);
		$longdescription = str_replace("'","",$myrow['DESCRIPCIO']);

		$description = str_replace('"','pulg',$description);
		$longdescription = str_replace('"','pulg',$longdescription);

		$units = $myrow['UNIDAD'];
		$cantidad = $myrow['PRE_VOL'];
		$mbflag = "M";


		/*OBTENER CATEGORIA*/
		$sqlcat = "SELECT * FROM stockcategory WHERE stocktype = 'F'";
		$Resultcat = DB_query($sqlcat,$db);

		if($myrowcat = DB_fetch_array($Resultcat,$db)) {
			$categoryid = $myrowcat['categoryid'];
		}

		if ($nivel==1){
			$categoryid = "PRTIPO";
		}else{
			$categoryid = "PAQUET";
		}

		/*OBTENER UNIDAD DE MEDIDA*/
		$sqlumed = "SELECT * FROM unitsofmeasure WHERE unitname = '" . $units . "'";
		$Resultumed = DB_query($sqlumed, $db);

		if($myrowumed = DB_fetch_array($Resultumed,$db)) {
			$units = $myrowumed['unitname'];
		}else{
			$isqlumed = "INSERT INTO unitsofmeasure(unitid, unitname)
                VALUES(NULL, '" . $units . "')";
			$iResultumed = DB_query($isqlumed, $db);

		}

		//INSERTAMOS PRODUCTO ENSAMBLADO

		$sqlpro = "SELECT * FROM stockmaster WHERE stockid = '" . $stockid . "'";
		$Resultpro = DB_query($sqlpro, $db);

		if($myrowpro = DB_fetch_array($Resultpro,$db)) {
			$stockid = $myrowpro['stockid'];

			$usql = "UPDATE stockmaster
                SET decimalplaces = '4',
                    categoryid = '" . $categoryid . "',
                    description = '" . $description . "',
                    longdescription = '" . $longdescription . "'
                WHERE stockid='" . $stockid . "'";
			$uResult = DB_query($usql, $db);

		}else{
			$isql = "INSERT INTO stockmaster (stockid, spes, categoryid, description, longdescription, manufacturer, stockautor, units, mbflag, lastcurcostdate,
                actualcost, lastcost, materialcost, labourcost, overheadcost, lowestlevel, discontinued, controlled, eoq, volume,
                kgs, barcode, discountcategory, taxcatid, taxcatidret, serialised, appendfile, perishable, decimalplaces, nextserialno,
                pansize, shrinkfactor, netweight, idclassproduct, stocksupplier, securitypoint, pkg_type, idetapaflujo, flagcommission, fijo,
                fecha_modificacion, stockupdate, isbn, grade, subject, deductibleflag, u_typeoperation, typeoperationdiot, height, width,
                large, fichatecnica, percentfactorigi, OrigenCountry, OrigenDate, inpdfgroup, flagadvance)
                VALUES ('" . $stockid . "', '', '" . $categoryid . "', '" . $description . "', '" . $longdescription . "', 'De prueba', '0', '" . $units . "', '" . $mbflag . "', Now(),
                '0', '0', '0', '0', '0', '0', '0', '0', '0', '0',
                '0', '', '', '0', '0', '0', '0', '0', '4', '0',
                '0', '0', '0', 'normal', NULL, NULL, '0', '0', '0', '0',
                Now(), '0', NULL, NULL, NULL, '0', '0', '0', '0', '0',
                '0', '', NULL, NULL, NULL, '0', '0')";

			$iResult = DB_query($isql, $db);
		}

		echo "<br>***" . $claveproducto . "***" . $stockid . "***" . $sufijo;
		siguientenivel($claveproducto, $stockid, $tablagral, $sufijo, ($nivel+1), $dblocal,$db);




		echo "<br>EXITO, SE CARGO EL PRESUPUESTO DE " . $stockid . " CON MONTO DE: " . $costototal;

	}
}


?>
