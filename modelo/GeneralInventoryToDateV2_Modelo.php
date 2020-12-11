<?php
/**
 * Locations.php
 *
 * @category    Modelo
 * @package     ap_grp/modelo
 * @author      JP
 * @version     1.0.0
 * @date:       10.03.18
 *
 * Programa para afectación de páneles, captura y administración de la información de los catálogos.
 *
 */
session_start();

/* DECLARACIÓN DE VARIABLES */
$PageSecurity = 8;
$PathPrefix = '../';
$funcion = 703;

/* INCLUSIÓN DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/SecurityUrl.php');
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');

/*********************************************** CONSTANTES ***********************************************/

/*********************************************** DESARROLLO DE FUNCIONES ***********************************************/
/**
 * Función para la busqueda de la información que llenará la tabla principal
 * @param   [DBInstance]    $db     Instancia de la base de datos
 * @return  [Array]         $data   Arreglo con la respuesta verdadera o falsa, según lo obtenido
 */
function show($db)
{
    // declaración de variables de la función
    $info = $_POST['info'];
    if (isset($info['fechaHasta'])) {
        $fechaFin = date("Y-m-d", strtotime($info['fechaHasta']));
    } else {
        $fechaFin = date("Y-m-d");
    }

    $data = ['success'=>false,'msg'=>'No hay lugares que coincidan con un registro provincia tributaria para mostrar. Compruebe que las provincias tributarias se configuran para todos los lugares de despacho','content'=>[]];

    $sql = "SELECT tpa.eq_stockid AS cabms, tpa.partidaEspecifica, stockmaster.stockid, stockmaster.units, stockmaster.description, stockmoves.loccode AS loccode, locations.locationname, '' AS localidad, '' AS tipo, stockmoves.carga_inicial,stockmoves.Existencias, 
			locstock.ontransit  AS /*locstock.ontransit*/ EnTransito, 

			0 AS pedventa, 0 AS Embarque, locstock.reorderlevel AS Autorizado,locstock.reorderlevel, (CASE WHEN compras.ENCOMPRA IS NULL THEN 0 ELSE compras.ENCOMPRA END) AS pedcompra, (CASE WHEN compras.PiezasOrden IS NULL THEN 0 ELSE compras.PiezasOrden END) AS PiezasOrden , (CASE WHEN PiezasComprasPendientes.CantPiezasOrden IS NULL THEN 0 ELSE PiezasComprasPendientes.CantPiezasOrden END) AS CantPiezasPendientesOrden, stockcostsxlegal.lastcost AS LastCosto, max(stockcostsxlegal.highercost) AS MaxCosto, 

			CASE WHEN stockmoves.CostoPromedio != stockcostsxlegal.avgcost THEN stockcostsxlegal.avgcost ELSE stockmoves.CostoPromedio END AS CostoPromedio,

			stockcostsxlegal.avgcost, 
			CASE WHEN inventarioInicial.total IS NOT NULL 
			THEN inventarioInicial.total 
			ELSE 0 
			END AS totalInicial, 

			CASE WHEN inventarioEntradas.total <> '' THEN inventarioEntradas.total ELSE 0 END AS totalEntradas, CASE WHEN inventarioSalidas.total <> '' THEN abs(inventarioSalidas.total) ELSE 0 END AS totalSalidas 

			FROM stockmaster
			JOIN locations
			LEFT JOIN(
						SELECT locstock.stockid, locstock.loccode, SUM(locstock.ontransit) AS ontransit, SUM(locstock.reorderlevel) AS reorderlevel
						FROM locstock
						GROUP BY locstock.loccode, locstock.stockid
			) AS locstock ON locstock.stockid = stockmaster.stockid AND locstock.loccode= locations.loccode

			LEFT JOIN (
						SELECT stockmoves.tagref, stockmoves.stockid, stockmoves.loccode, 
						(CASE WHEN SUM(stockmoves.qty) BETWEEN -0.01 AND 0.01 THEN 0.00 ELSE SUM(stockmoves.qty) END) - SUM(CASE WHEN stockmoves.type IN (16,1001) THEN (stockmoves.qty) ELSE 0.00 END) AS Existencias, 
						sum(CASE WHEN stockmoves.type IN (300) THEN (stockmoves.qty) ELSE 0.00 END) AS carga_inicial,
						CASE WHEN sum(stockmoves.qty) BETWEEN -0.01 AND 0.01 THEN 0.00 ELSE sum(CASE WHEN stockmoves.type IN (31,35,590,591) THEN stockmoves.standardcost ELSE stockmoves.qty*stockmoves.standardcost END) / sum(stockmoves.qty) END AS CostoPromedio,ln_ue,
						sum(CASE WHEN stockmoves.type IN (16) THEN (stockmoves.qty) ELSE 0.00 END) AS qty_salida_solicitud
						FROM stockmoves 
						WHERE stockid like '%$info[claveprod]%' AND
						DATE_FORMAT(stockmoves.trandate, '%Y-%m-%d') <= '$fechaFin' $sqlUnidadEjecutora
						 AND (stockmoves.loccode = '0' OR '0'='0') AND (tagref = '$info[selectUnidadNegocioFiltro]' OR '$info[selectUnidadNegocioFiltro]'='0')
						GROUP BY stockmoves.stockid, stockmoves.loccode
			) AS stockmoves ON stockmoves.stockid = stockmaster.stockid AND stockmoves.loccode = locations.loccode

			LEFT JOIN(
						SELECT sa.nu_tag,sa.ln_almacen,sad.ln_clave_articulo,sum(sad.nu_cantidad) AS cantidad_transito
						FROM tb_solicitudes_almacen sa
						LEFT JOIN tb_solicitudes_almacen_detalle sad ON sa.`nu_folio` = sad.`nu_id_solicitud`
						WHERE (sa.nu_tag = '$info[selectUnidadNegocioFiltro]' OR '$info[selectUnidadNegocioFiltro]' = '0') $sqlUnidadEjecutoraTransito AND DATE_FORMAT(dtm_fecharegistro, '%Y-%m-%d') <= '$fechaFin' AND sad.ln_clave_articulo like '%$info[claveprod]%' AND estatus IN (30)
						GROUP BY sa.ln_almacen, sad.ln_clave_articulo
			) AS dtOntransit ON stockmaster.stockid = dtOntransit.ln_clave_articulo AND stockmoves.loccode = dtOntransit.ln_almacen

			LEFT JOIN tags ON locations.tagref = tags.tagref 
			LEFT JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid 
			LEFT JOIN stockcostsxlegal ON tags.legalid = stockcostsxlegal.legalid AND stockmoves.stockid = stockcostsxlegal.stockid 
			LEFT JOIN tb_partida_articulo tpa ON (stockmaster.eq_stockid = tpa.eq_stockid)
			LEFT JOIN tb_cat_partidaspresupuestales_partidaespecifica tb_pe on tpa.partidaEspecifica = tb_pe.partidacalculada

			LEFT JOIN (
						SELECT purchorderdetails.itemcode AS producto, sum(purchorderdetails.quantityord - purchorderdetails.quantityrecd) AS CantPiezasOrden, 'purchorders.intosectorlocation' AS intosectorlocation
						FROM purchorderdetails
						INNER JOIN purchorders ON purchorderdetails.orderno=purchorders.orderno
						WHERE purchorders.status = 'Authorised'
						GROUP BY purchorderdetails.itemcode, intosectorlocation
			) AS PiezasComprasPendientes ON PiezasComprasPendientes.producto = stockmoves.stockid 

			LEFT JOIN(
						SELECT SUM(purchorderdetails.quantityord) AS ENCOMPRA, purchorders.intostocklocation AS almacencompra, purchorderdetails.itemcode AS producto, 'purchorders.intosectorlocation' AS localidad, sum(CASE WHEN purchorders.status = 'Authorised' THEN purchorderdetails.quantityord ELSE 0 END) AS PiezasOrden
						FROM purchorderdetails
						INNER JOIN purchorders ON purchorderdetails.orderno=purchorders.orderno
						WHERE purchorders.status not IN ('Cancelled')
						GROUP BY purchorders.intostocklocation,purchorderdetails.itemcode
						HAVING SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd)>0
			) AS compras ON compras.almacencompra=stockmoves.loccode AND compras.producto = stockmoves.stockid 

			LEFT JOIN(
						SELECT stockmoves.stockid, stockmoves.loccode, SUM(qty) AS total
						FROM stockmoves
						WHERE DATE_FORMAT(stockmoves.trandate, '%Y-%m-%d') <= '$fechaFin'
						GROUP BY stockmoves.stockid, stockmoves.loccode
			) AS inventarioInicial ON inventarioInicial.stockid = stockmaster.stockid AND inventarioInicial.loccode = locations.loccode 

			LEFT JOIN(
						SELECT SUM(qty) AS total, stockmoves.stockid, stockmoves.loccode
						FROM stockmoves
						WHERE stockmoves.type IN (SELECT systypescat.typeid
													FROM systypescat
													WHERE systypescat.nu_inventario_entrada = 1 AND systypescat.typeid !='300' )
						AND DATE_FORMAT(stockmoves.trandate, '%Y-%m-%d') <= '$fechaFin'
						GROUP BY stockmoves.stockid, stockmoves.loccode
			) AS inventarioEntradas ON inventarioEntradas.stockid = stockmoves.stockid AND inventarioEntradas.loccode = stockmoves.loccode 

			LEFT JOIN(
						SELECT SUM(qty) AS total, stockmoves.stockid, stockmoves.loccode 
						FROM stockmoves 
						WHERE stockmoves.type IN (SELECT systypescat.typeid
													FROM systypescat
													WHERE systypescat.nu_inventario_salida = 1
						)
						AND DATE_FORMAT(stockmoves.trandate, '%Y-%m-%d') <= '$fechaFin'
						GROUP BY stockmoves.stockid, stockmoves.loccode
			) AS inventarioSalidas ON inventarioSalidas.stockid = stockmoves.stockid AND inventarioSalidas.loccode = stockmoves.loccode 

			WHERE stockmaster.discontinued NOT IN (3)  AND tb_pe.ccap in(2)
			AND (stockmaster.categoryid = '0' OR '0'='0')";
            
    // datos adicionales de filtrado
    // $sql .= " AND ( '$info[selectUnidadNegocioFiltro]'='0' OR `stockmoves`.tagref = '$info[selectUnidadNegocioFiltro]' ) ";

    //
    //Se comento ya que se hizo multiple el combo
    //
    // if(!empty($info['selectUnidadEjecutoraFiltro']) and $info['selectUnidadEjecutoraFiltro'] != '-1'){
    // 	$sql .= " AND ( '$info[selectUnidadEjecutoraFiltro]'='0' OR `stockmoves`.`ln_ue` = '$info[selectUnidadEjecutoraFiltro]' )";
    // }

    if (is_array($info['selectUnidadEjecutoraFiltro'])&&count($info['selectUnidadEjecutoraFiltro'])) {
        $sql .= " AND (`locations`.`ln_ue` = '".implode("' OR `locations`.`ln_ue` = '", $info['selectUnidadEjecutoraFiltro'])."' )";
    }
    if (!empty($info['SoloExistencias'])&&$info['SoloExistencias']!="-1") {
        $sql .= " AND `Existencias` ".( $info['SoloExistencias']=="1" ? ">" : "<=" )." 0 ";
    }
    if (is_array($info['selPartida'])&&count($info['selPartida'])) {
        $sql .= " AND ( `tpa`.`partidaEspecifica` LIKE '%".implode("%' OR `tpa`.`partidaEspecifica` LIKE '%", $info['selPartida'])."%' ) ";
    }
    if (is_array($info['selAlmacen'])&&count($info['selAlmacen'])) {
        $sql .= " AND (`locstock`.`loccode` = '".implode("' OR `locstock`.`loccode` = '", $info['selAlmacen'])."' )";
    }
    if (isset($info['claveprod'])) {
        $sql .= " AND `stockmaster`.`stockid` LIKE '%$info[claveprod]%' ";
    }

    // datos adicionales de ordenamiento
    $sql .= " GROUP BY cabms, partidaEspecifica, stockid, units, description, loccode, locationname, localidad, tipo, EnTransito, Autorizado, pedventa, Embarque, pedcompra, PiezasOrden , CantPiezasPendientesOrden, LastCosto, Existencias, CostoPromedio, avgcost, totalInicial, totalEntradas, totalSalidas";
    $result = DB_query($sql, $db);

    if ($_SESSION['UserID'] == 'desarrollo') {
        // echo "<pre>".$sql;
        // exit();
    }

    // procesamiento de la información obtenida
    $decimales      = 2;
    $sindecimales   = 0;
    $totales        = array();
    $semaforo       = '';
    
    while ($rs = DB_fetch_array($result)) {
        $disponibles       = utf8_encode(number_format(($rs['carga_inicial'] + $rs['totalEntradas'])-$rs['EnTransito']-$rs['totalSalidas'], $sindecimales));
        $reorderLevel      = utf8_encode(number_format($rs['reorderlevel'], $sindecimales));
        $formula           = ($disponibles*100)/$reorderLevel;
        //echo "->$formula";
        if ($formula >50) {
            $semaforo = '<div class="semaforoVere"></div>';
        } else if ($formula >30 && $formula <= 50) {
            $semaforo = '<div class="semaforoAmarillo"></div>';
        } else if ($formula > 0 && $formula<= 30) {
            $semaforo = '<div class="semaforoRojo"></div>';
        } else if ($formula <= 0) {
            $semaforo = '<div class="semaforoRojo"></div>';
        }
        
        $data['content'][] = [
            'partidaespecifica'=>utf8_encode($rs['partidaEspecifica']),// 0
            'clave'=>utf8_encode($rs['stockid']),// 1
            'descripcion'=>utf8_encode($rs['description']),// 2
            'almacen'=>utf8_encode($rs['loccode']." - ".$rs['locationname']),// 3
            'unidadmedida'=>utf8_encode($rs['units']),// 4
            'inventarioinicial'=>utf8_encode(number_format($rs['carga_inicial'], $sindecimales)),// 5
            'entradas'=>utf8_encode(number_format($rs['totalEntradas'], $sindecimales)),// 6
            'salidas'=>utf8_encode(number_format($rs['totalSalidas'], $sindecimales)),// 7
            'existencias'=>utf8_encode(number_format($rs['Existencias']-$rs['totalSalidas'], $sindecimales)),// 8
            'entransito'=>utf8_encode(number_format($rs['EnTransito'], $sindecimales)),// 9
            'disponibles'=> $disponibles,// 10
            'semaforo'=> $semaforo,
            'costopromedio'=>utf8_encode("$".number_format($rs['CostoPromedio'], $decimales)),// 11
            'ultimocosto'=>utf8_encode("$".number_format($rs['LastCosto'], $decimales)),// 12
            'costomasalto'=>utf8_encode("$".number_format($rs['MaxCosto'], $decimales)),// 13
            'valorinventario'=>utf8_encode("$".number_format($rs['CostoPromedio']*$rs['Existencias'], $decimales)),// 14
        ];
        $totales['inventarioinicial'] += $rs['carga_inicial'];
        $totales['entradas'] += $rs['totalEntradas'];
        $totales['salidas'] += $rs['totalSalidas'];
        $totales['existencias'] += $rs['Existencias'];
        $totales['entransito'] += $rs['EnTransito'];
        $totales['disponibles'] += ($rs['Existencias']-$rs['EnTransito']);
        $totales['valorinventario'] += $rs['CostoPromedio']*$rs['Existencias'];
    }
    if (count($totales)) {
        $totales['inventarioinicial'] = utf8_encode(number_format($totales['inventarioinicial'], $sindecimales));
        $totales['entradas'] = utf8_encode(number_format($totales['entradas'], $sindecimales));
        $totales['salidas'] = utf8_encode(number_format($totales['salidas'], $sindecimales));
        $totales['existencias'] = utf8_encode(number_format($totales['existencias'], $sindecimales));
        $totales['entransito'] = utf8_encode(number_format($totales['entransito'], $sindecimales));
        $totales['disponibles'] = utf8_encode(number_format($totales['disponibles'], $sindecimales));
        $totales['valorinventario'] = utf8_encode("$".number_format($totales['valorinventario'], $decimales));
    }
    $data['query'] = "";
    $data['post'] = $info;
    $data['success'] = true;
    $data['totales'] = $totales;
    // retorno de la información
    return $data;
}
/*********************************************** DESARROLLO DE FUNCIONES ***********************************************/

/* EJECUCIÓN DE FUNCIONES */
$data = call_user_func_array($_POST['method'], [$db]);
/* MODIFICACIÓN DE HEADER */
header('Content-type:application/json;charset=utf-8');
/* ENVÍO DE INFORMACIÓN */
echo json_encode($data);

/*********************************************** FUNCIONES ÚTILES ***********************************************/



function ejecutaQuery($db, $sql)
{
    # consulta de existencia
    $result = DB_query($sql, $db);
    return DB_fetch_array($result)['RegistrosEncontrados'];
}

/**
 * Función para obtención de información para los distintos selects
 * @param   [DBInstance]    $db     Instancia de la base de datos
 * @return  [Array]         $data   Arreglo con la respuesta verdadera o falsa, según lo obtenido
 */
function obtenDatosSelect($db, $sql)
{
    // declaración de variables de la función
    $data = ['success'=>false, 'msg'=>'Ocurrió un incidente al obtener la información. Favor de contactar al administrador.'];
    $info = $_POST;

    $result = DB_query($sql, $db);

    // comprobación de existencia de la información
    if (DB_num_rows($result) == 0) {
        $data['msg'] = 'No se encontraron los datos solicitados.';
        return $data;
    }
    // prcesamiento de la información obtenida
    while ($rs = DB_fetch_array($result)) {
        $data['content'][] = [
            'label'=>utf8_encode($rs['label']),
            'title'=>utf8_encode($rs['label']),
            'value'=>$rs['valor']
        ];
    }
    $data['success'] = true;
    // retorno de la información
    return $data;
}

function obtenDatosLista($db, $sql)
{
    // declaración de variables de la función
    $data = ['success'=>false, 'msg'=>'Ocurrió un incidente al obtener la información. Favor de contactar al administrador.'];
    $info = $_POST;

    $datos = array();

    $result = DB_query($sql, $db);

    // comprobación de existencia de la información
    if (DB_num_rows($result) == 0) {
        $data['msg'] = 'No se encontraron los datos solicitados.';
        return $data;
    }

    while ($rs = DB_fetch_array($result)) {
        $datos[] = [
            'value' => $rs['valor'],
            'text' => utf8_encode($rs['label']),
            'extra' => utf8_encode($rs['extra'])
        ];
    }

    $data['productos'] = $datos;
    $data['success'] = true;
    // retorno de la información
    return $data;
}

function datosselectAlmacen($db)
{
    $sql = "SELECT DISTINCT `loccode` AS valor, CONCAT(`loccode`,' - ',`locationname`) AS label
			FROM `locations` 
			ORDER BY LENGTH(`loccode`) ASC, `loccode` ASC, `locationname` ASC";

    $sql = "SELECT DISTINCT locations.loccode as valor, CONCAT(locations.loccode,' - ',locations.locationname) as label
	FROM locations, sec_loccxusser
	WHERE locations.loccode=sec_loccxusser.loccode 
	AND sec_loccxusser.userid='" . $_SESSION['UserID'] . "'
	ORDER BY label";

    return obtenDatosSelect($db, $sql);
}

function datosselectPartidaEspecifica($db)
{
    $sql = "SELECT DISTINCT `tb_partida_articulo`.`partidaEspecifica` AS valor, `tb_partida_articulo`.`partidaEspecifica` AS label 
			FROM `tb_partida_articulo` 
			INNER JOIN `stockmaster` ON `tb_partida_articulo`.`eq_stockid` = `stockmaster`.`eq_stockid` 
			WHERE `partidaEspecifica` LIKE '2%'
			AND `stockmaster`.`mbflag` = 'B'
			ORDER BY `partidaEspecifica`";

            //antes `partidaEspecifica` not LIKE '5%'

    return obtenDatosSelect($db, $sql);
}

function datosListaCuentaCargo($db)
{
    $sql = "SELECT `stockid` AS valor, `description` AS label, `tb_partida_articulo`.`partidaEspecifica` AS extra
			FROM `stockmaster`
			INNER JOIN `tb_partida_articulo` ON `stockmaster`.`eq_stockid` = `tb_partida_articulo`.`eq_stockid`
			WHERE `stockmaster`.`mbflag`='B'";

    return obtenDatosLista($db, $sql);
}

function datosListaCuentasCargo($db){
    // declaración de variables de la función
    $data = ['success'=>false, 'msg'=>'Ocurrió un incidente al obtener la información. Favor de contactar al administrador.'];
    $info = $_POST;

    $registrosEncontrados = array();

    $sql = "SELECT `stockid` AS valor, `description` AS label, `tb_partida_articulo`.`partidaEspecifica` AS extra

            FROM `stockmaster`
            INNER JOIN `tb_partida_articulo` ON `stockmaster`.`eq_stockid` = `tb_partida_articulo`.`eq_stockid`

            WHERE `stockmaster`.`mbflag`='B'";

    $result = DB_query($sql, $db);

    // comprobación de existencia de la información
    if(DB_num_rows($result) == 0){
        $data['msg'] = 'No se encontraron los datos solicitados.';
        return $data;
    }

    while ($rs = DB_fetch_array($result)) {
        $registrosEncontrados[] = [
            'valor' => $rs['valor'],
            'texto' => utf8_encode($rs['label']),
            'partida' => utf8_encode($rs['extra'])
        ];
    }

    $data['registrosEncontrados'] = $registrosEncontrados;
    $data['success'] = true;
    // retorno de la información
    return $data;
}
