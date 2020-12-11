<?php
/**
 * Suficiencia Manual
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 31/10/2017
 * Modelos para las operaciones del panel de Suficiencia Manual
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
//Envio a capa
$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
$funcion=81;
include $PathPrefix."includes/SecurityUrl.php";
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

if ($option == 'obtenerInformacion') {
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];
    $ue = $_POST['ue'];
    $patidaGen = $_POST['patidaGen'];
    $patidaEsp = $_POST['patidaEsp'];
    $txtDescripcion = $_POST['txtDescripcion'];
    $tipoProducto = $_POST['tipoProducto'];

    $sqlWhere = "";

    if ($patidaGen != '') {
        $sqlWhere .= " AND stockmaster.categoryid IN (".$patidaGen.") ";
    }
    if ($patidaEsp != '') {
        $sqlWhere .= " AND tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada IN (".$patidaEsp.") ";
    }

    if (trim($txtDescripcion) != '') {
        $sqlWhere .= " AND (stockmaster.stockid like '%".$txtDescripcion."%' OR stockmaster.description like '%".$txtDescripcion."%' )";
    }

    if ($tipoProducto != '') {
        switch ($tipoProducto) {
            case 'B':
                $sqlWhere .= " AND stockmaster.mbflag IN (".$tipoProducto.") AND tb_cat_partidaspresupuestales_partidaespecifica.ccap in (2,5)";
                break;
            case 'D':
                $sqlWhere .= " AND stockmaster.mbflag IN (".$tipoProducto.") tb_cat_partidaspresupuestales_partidaespecifica.ccap = 3 AND tb_cat_partidaspresupuestales_partidaespecifica.ccon != 7";
                break;
            default:
                $sqlWhere .= " AND stockmaster.mbflag IN (".$tipoProducto.")";
                break;
        }
    }

    $info = array();
    $SQL = "
    SELECT 
    stockmaster.stockid,
    stockmaster.description,
    stockmaster.longdescription,
    stockmaster.categoryid,
    stockmaster.units,
    stockmaster.mbflag,
    stockmaster.nu_cve_familia,
    CASE WHEN stockmaster.discontinued = 1 THEN 'Inactivo' ELSE 'Activo' END AS discontinued,
    stocktypeflag.stocknameflag,
    stockmaster.eq_stockid,
    CONCAT(tb_partida_articulo.partidaEspecifica, ' - ', tb_partida_articulo.descPartidaEspecifica) as descPartidaEspecifica,
    tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada as partida
    FROM stockmaster
    LEFT JOIN stocktypeflag ON stocktypeflag.stockflag = stockmaster.mbflag
    LEFT JOIN tb_partida_articulo ON tb_partida_articulo.eq_stockid = stockmaster.eq_stockid
    LEFT JOIN tb_cat_partidaspresupuestales_partidaespecifica ON tb_partida_articulo.partidaEspecifica = tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada
    WHERE 1 = 1 ".$sqlWhere;
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    // $sqlEQGeneral="SELECT eq_stockid from tb_partida_articulo where eq_stockid like'G%'";
    // $resultEQGeneral= DB_query($sqlEQGeneral, $db);
    // $myrowEQGeneral = DB_fetch_array($resultEQGeneral);

    while ($myrow = DB_fetch_array($TransResult)) {
        $enc = new Encryption;
        $url = "&StockID=>".$myrow['stockid']."&PartidaID=>".$myrow['partida']."&mbflag=>".$myrow['mbflag']."&modificar=>1";
        $url = $enc->encode($url);
        $liga= "URL=" . $url;


        $url2 = "&StockID=>".$myrow['stockid']."&PartidaID=>".$myrow['partida']."&mbflag=>".$myrow['mbflag']."&ver=>1";
        $url2 = $enc->encode($url2);
        $liga2= "URL=" . $url2;

        $descLenc =  utf8_encode($myrow ['longdescription']);
        $descLde =  utf8_decode($myrow ['longdescription']);
        $descLong =  htmlspecialchars(utf8_encode($myrow ['longdescription']));
        if($myrow ['mbflag'] == 'B'){
            if($myrow ['nu_cve_familia'] > 0){
                $new_stockid = $myrow ['stockid']. ' - ' . $myrow ['nu_cve_familia']; 
            }else{
                $new_stockid = $myrow ['stockid'];
            }
        }else{
            $new_stockid = $myrow ['stockid'];
        }

        $familia=$myrow ['nu_cve_familia'];
        if($familia == '0'){
            $familia="";
        }

        $eq_stockid=$myrow ['eq_stockid'];
        $rest = substr($myrow ['eq_stockid'], 0,1);
        //echo "sdasd:".substr($myrow ['eq_stockid'], 0,1);
        if($rest =="G"){
            $eq_stockid="";
        }

        if(empty($eq_stockid) or $eq_stockid=="0"){
            $eq_stockid="";
        }


        // $liga = "&StockID=".$myrow['stockid'];
        $info[] = array(
            'stockid' => $myrow ['stockid'],
            'description' => $myrow ['description'],
            //'description' => utf8_encode($myrow ['description']),
            'longdescription' => $myrow ['longdescription'],
            //'longdescription' => utf8_encode($myrow ['longdescription']),
            //'longdescription' => htmlspecialchars(utf8_decode($myrow ['longdescription'])),
            //'longdescription' => $descLong,
            'mbflag' => $myrow ['mbflag'],
            'units' => $myrow ['units'],
            'discontinued' => $myrow ['discontinued'],
            'categoryid' => $myrow ['categoryid'],
            'stocknameflag' => $myrow ['stocknameflag'],
            'eq_stockid' => $eq_stockid,
            'new_stockid' => $new_stockid ,
            'nu_cve_familia' => $familia,
            'partida' => $myrow ['partida'],
            'descPartidaEspecifica' => $myrow ['descPartidaEspecifica'],
            'Ver' => '<a href="Stocks_V_2.php?'.$liga2.'"><span class="glyphicon glyphicon-eye-open"></span></a>',
            'Modificar' => '<a href="Stocks_V_2.php?'.$liga.'"><span class="glyphicon glyphicon-edit"></span></a>',
        );
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'new_stockid', type: 'string' },";
    $columnasNombres .= "{ name: 'nu_cve_familia', type: 'string' },";
    $columnasNombres .= "{ name: 'description', type: 'string' },";
    $columnasNombres .= "{ name: 'longdescription', type: 'string' },";
    $columnasNombres .= "{ name: 'units', type: 'string' },";
    $columnasNombres .= "{ name: 'discontinued', type: 'string' },";
    $columnasNombres .= "{ name: 'partida', type: 'string' },";
    //$columnasNombres .= "{ name: 'categoryid', type: 'string' },";
    $columnasNombres .= "{ name: 'stocknameflag', type: 'string' },";
    $columnasNombres .= "{ name: 'eq_stockid', type: 'string' },";
    $columnasNombres .= "{ name: 'Ver', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Tipo', datafield: 'stocknameflag', width: '9%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Clave', datafield: 'new_stockid', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";

    $columnasNombresGrid .= " { text: 'Familia', datafield: 'nu_cve_familia', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";

    $columnasNombresGrid .= " { text: 'CABMS', datafield: 'eq_stockid', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Partida Específica', datafield: 'partida', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    //$columnasNombresGrid .= " { text: 'Partida Genérica', datafield: 'categoryid', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción Corta', datafield: 'description', width: '15%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción Larga', datafield: 'longdescription', width: '20%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Unidad', datafield: 'units', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'discontinued', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Ver', datafield: 'Ver', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";
    
    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

if($option == 'mostrarUnidad'){
    $info = array();
    $mbflag = $_POST['mbflag'];
    $SQL = "SELECT unitid, unitname FROM unitsofmeasure WHERE mbflag = '".$mbflag."'";
    $ErrMsg = "No se obtuvo las unidades de medida";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['unitid'], 'texto' => $myrow ['unitname'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarPartidasGen') {
    $info = array();
    $mbflag = $_POST['mbflag'];
    switch ($mbflag) {
        case 'B':            
            $whereGenPartida = " WHERE categoryid BETWEEN 200 AND 299 OR categoryid BETWEEN 500 AND 599 ";
            break;
        case 'D':
            $whereGenPartida = " WHERE categoryid BETWEEN 300 AND 369 OR categoryid BETWEEN 380 AND 399 ";
            break;
        default:
            $whereGenPartida = "";
            break;
    }
    $SQL = "SELECT distinct categoryid, CONCAT(categoryid, ' - ', categorydescription) as categorydescription FROM stockcategory ".$whereGenPartida." ORDER BY categoryid ASC";
    $ErrMsg = "No se obtuvo las Partidas Genéricas";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['categoryid'], 'texto' => $myrow ['categorydescription'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarPartidasEsp') {
    $info = array();
    $mbflag = $_POST['mbflag'];
    switch ($mbflag) {
        case 'B':            
            $whereEspPartida = "WHERE ccap = 2 ";//SE COMENTA POR QUE CONTADORA MECIONA QUE NO DEBE IR AL ALTA --  OR ccap = 5
            break;
        case 'D':
            $whereEspPartida = " WHERE ccap = 3 AND ccon != 7 ";
            break;
        default:
            $whereEspPartida = " WHERE ( ccap = 2 OR ccap = 3 ) AND ccon != 7 ";
            break;
    }
    $SQL = "SELECT partidacalculada, CONCAT(partidacalculada, ' - ', descripcion) as descripcionPartidaEsp FROM tb_cat_partidaspresupuestales_partidaespecifica ".$whereEspPartida." ORDER BY partidacalculada ASC";
    $ErrMsg = "No se obtuvo las Partidas Genéricas";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['partidacalculada'], 'texto' => $myrow ['descripcionPartidaEsp'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarTipoProducto') {
    $info = array();
    $SQL = "SELECT distinct stockflag, stocknameflag FROM stocktypeflag WHERE sn_activo = '1' ORDER BY stocknameflag ASC";
    $ErrMsg = "No se obtuvo los Tipos de Productos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['stockflag'], 'texto' => $myrow ['stocknameflag'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarCogProducto') {
    $mbflag = $_POST['mbflag'];
    $categoryID = $_POST['categoryID'];
    $partidaID = $_POST['partidaID'];
    $sqlWhere = "";
    if($mbflag == 'B'){
        $sqlWhere = " WHERE partidaEspecifica = 2000 OR partidaEspecifica between 20000 AND 29999 OR  partidaEspecifica between 50000 AND 59999";
    }
    if($mbflag == 'D'){
        $sqlWhere = " WHERE partidaEspecifica = 3000 OR partidaEspecifica between 30000 AND 36999 OR  partidaEspecifica between 38000 AND 39999";
    }
    if($categoryID != ''){
        $SQL="SELECT DISTINCT tpa.eq_stockid, CONCAT(tpa.eq_stockid, ' - ',tpa.descPartidaEspecifica) as descPartidaEspecifica, s.categoryid FROM stockmaster s 
        INNER JOIN tb_partida_articulo tpa on (s.eq_stockid = tpa.eq_stockid) WHERE s.categoryid = $categoryID ";
        $ErrMsg = "No se obtuvo el COG de Producto";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array( 'value' => $myrow ['eq_stockid'], 'texto' => $myrow ['descPartidaEspecifica'] );
        }
    }else if($partidaID != ''){
        $SQL="SELECT DISTINCT tpa.eq_stockid, CONCAT(tpa.eq_stockid, ' - ',tpa.descPartidaEspecifica) as descPartidaEspecifica,  tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada, tpa.partidaEspecifica, s.mbflag 
    FROM stockmaster s 
        INNER JOIN tb_partida_articulo tpa on (s.eq_stockid = tpa.eq_stockid)
        INNER JOIN tb_cat_partidaspresupuestales_partidaespecifica ON (tpa.partidaEspecifica = tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada) WHERE tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = '".$partidaID."' GROUP BY tpa.eq_stockid, descPartidaEspecifica, tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada, tpa.partidaEspecifica  ";
        $ErrMsg = "No se obtuvo el COG de Producto";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array( 'value' => $myrow ['eq_stockid'], 'texto' => $myrow ['descPartidaEspecifica'], 'mbflag' => $myrow['mbflag'] );
        }
    }else{
        $info = array();
        $SQL = "SELECT distinct eq_stockid, CONCAT(eq_stockid, ' - ',descPartidaEspecifica) as descPartidaEspecifica FROM tb_partida_articulo ".$sqlWhere." ORDER BY descPartidaEspecifica ASC";
        $ErrMsg = "No se obtuvo el COG de Producto";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array( 'value' => $myrow ['eq_stockid'], 'texto' => $myrow ['descPartidaEspecifica'] );
        }
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarProductos') {
    $info = array();
    $SQL = "SELECT distinct stockid, CONCAT(stockid, ' - ',description) as description FROM stockmaster WHERE stockid <> '' ORDER BY description ASC";
    $ErrMsg = "No se obtuvieron los Productos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['stockid'], 'texto' => $myrow ['description'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

/****************************************** Stocks ********************************************************/

/*if ($option == 'mostrarSegunTipo') {
    $infoPartida = array();
    $infoPartidaEsp = array();
    $infoUnidad = array();
    $infoCabms = array();
    $mbflag = $_POST['mbflag'];
    $flag = "";
    $categoryID = $_POST['categoryID'];
    $partidaID = $_POST['partidaID'];
    $sqlWhere = "";
    switch ($mbflag) {
        case 'B':            
            $whereEspPartida = " WHERE ccap = 2";
            $whereGenPartida = " WHERE categoryid BETWEEN 200 AND 299 OR categoryid BETWEEN 500 AND 599 ";
            $sqlWhere = " WHERE partidaEspecifica = 2000 OR partidaEspecifica between 20000 AND 29999 OR  partidaEspecifica between 50000 AND 59999";
            $flag = 'B';
            break;
        case 'D':
            $whereEspPartida = " WHERE ccap = 3 AND ccon != 7 ";
            $whereGenPartida = " WHERE categoryid BETWEEN 300 AND 369 OR categoryid BETWEEN 380 AND 399 ";
            $sqlWhere = " WHERE partidaEspecifica = 3000 OR partidaEspecifica between 30000 AND 36999 OR  partidaEspecifica between 38000 AND 39999";
            $flag = 'D';
            break;
        default:
            $whereEspPartida = "";
            $whereGenPartida = "";
            $flag = "B','D";
            $sqlWhere = "";
            break;
    }
    $SQL = "SELECT DISTINCT partidacalculada, CONCAT(partidacalculada, ' - ', descripcion) as descripcionPartidaEsp FROM tb_cat_partidaspresupuestales_partidaespecifica ".$whereEspPartida." ORDER BY partidacalculada ASC";
    $ErrMsg = "No se obtuvo las Partidas Específica";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $infoPartidaEsp[] = array( 'value' => $myrow ['partidacalculada'], 'texto' => $myrow ['descripcionPartidaEsp'] );
    }
    $SQL = "SELECT DISTINCT categoryid, CONCAT(categoryid, ' - ', categorydescription) as categorydescription FROM stockcategory ".$whereGenPartida." ORDER BY categoryid ASC";
    $ErrMsg = "No se obtuvo las Partidas Genéricas";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $infoPartida[] = array( 'value' => $myrow ['categoryid'], 'texto' => $myrow ['categorydescription'] );
    }
    $SQL1 = "SELECT unitid, unitname FROM unitsofmeasure WHERE mbflag in ('".$flag."')";
    $ErrMsg1 = "No se obtuvo las unidades de medida";
    $TransResult1 = DB_query($SQL1, $db, $ErrMsg1);
    while ($myrow1 = DB_fetch_array($TransResult1)) {
        $infoUnidad[] = array( 'value' => $myrow1 ['unitid'], 'texto' => $myrow1 ['unitname'] );
    }
    if($categoryID != ''){
        $SQL2="SELECT DISTINCT tpa.eq_stockid, CONCAT(tpa.eq_stockid, ' - ',tpa.descPartidaEspecifica) as descPartidaEspecifica, s.categoryid FROM stockmaster s 
        INNER JOIN tb_partida_articulo tpa on (s.eq_stockid = tpa.eq_stockid) WHERE s.categoryid = $categoryID ";
        $ErrMsg2 = "No se obtuvo el COG de Producto";
        $TransResult2 = DB_query($SQL2, $db, $ErrMsg2);
        while ($myrow2 = DB_fetch_array($TransResult2)) {
            $infoCabms[] = array( 'value' => $myrow2 ['eq_stockid'], 'texto' => $myrow2 ['descPartidaEspecifica'] );
        }
    }else if($partidaID != ''){
        $SQL2="SELECT DISTINCT tpa.eq_stockid, CONCAT(tpa.eq_stockid, ' - ',tpa.descPartidaEspecifica) as descPartidaEspecifica,  tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada, tpa.partidaEspecifica 
    FROM stockmaster s 
        INNER JOIN tb_partida_articulo tpa on (s.eq_stockid = tpa.eq_stockid)
        INNER JOIN tb_cat_partidaspresupuestales_partidaespecifica ON (tpa.partidaEspecifica = tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada) WHERE tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = '".$partidaID."' GROUP BY tpa.eq_stockid, descPartidaEspecifica, tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada, tpa.partidaEspecifica ";
        $ErrMsg2 = "No se obtuvo el COG de Producto";
        $TransResult2 = DB_query($SQL2, $db, $ErrMsg2);
        while ($myrow2 = DB_fetch_array($TransResult2)) {
            $infoCabms[] = array( 'value' => $myrow2 ['eq_stockid'], 'texto' => $myrow2 ['descPartidaEspecifica'] );
        }
    }else{
        $SQL2 = "SELECT distinct eq_stockid, CONCAT(eq_stockid, ' - ',descPartidaEspecifica) as descPartidaEspecifica FROM tb_partida_articulo ".$sqlWhere." ORDER BY descPartidaEspecifica ASC";
        $ErrMsg2 = "No se obtuvo el COG de Producto";
        $TransResult2 = DB_query($SQL2, $db, $ErrMsg2);
        while ($myrow2 = DB_fetch_array($TransResult2)) {
            $infoCabms[] = array( 'value' => $myrow2 ['eq_stockid'], 'texto' => $myrow2 ['descPartidaEspecifica'] );
        }
    }
    $contenido = array('datosPartidaEsp' => $infoPartidaEsp, 'datosPartida' => $infoPartida, 'datosUnidad' => $infoUnidad, 'datosCabms' => $infoCabms);
    $result = true;
}*/
if ($option == 'mostrarInfoTipo') {
    $infoPartida = array();
    $infoPartidaEsp = array();
    $infoUnidad = array();
    $infoCabms = array();
    $mbflag = $_POST['mbflag'];
    $flag = "";
    $sqlWhere = "";
    switch ($mbflag) {
        case 'B':            
            $whereEspPartida = " WHERE ccap = 2 OR ccap= 5";
            $whereGenPartida = " WHERE categoryid BETWEEN 200 AND 299 OR categoryid BETWEEN 500 AND 599 ";
            $sqlWhere = " WHERE partidaEspecifica = 2000 OR partidaEspecifica between 20000 AND 29999 OR  partidaEspecifica between 50000 AND 59999";
            $flag = 'B';
            break;
        case 'D':
            $whereEspPartida = " WHERE ccap = 3 AND ccon != 7 ";
            $whereGenPartida = " WHERE categoryid BETWEEN 300 AND 369 OR categoryid BETWEEN 380 AND 399 ";
            $sqlWhere = " WHERE partidaEspecifica = 3000 OR partidaEspecifica between 30000 AND 36999 OR  partidaEspecifica between 38000 AND 39999";
            $flag = 'D';
            break;
        default:
            $whereEspPartida = "";
            $whereGenPartida = "";
            $flag = "B','D";
            $sqlWhere = "";
            break;
    }
    $SQL = "SELECT DISTINCT partidacalculada, CONCAT(partidacalculada, ' - ', descripcion) as descripcionPartidaEsp FROM tb_cat_partidaspresupuestales_partidaespecifica ".$whereEspPartida." ORDER BY partidacalculada ASC";
    $ErrMsg = "No se obtuvo las Partidas Específica";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $infoPartidaEsp[] = array( 'value' => $myrow ['partidacalculada'], 'texto' => $myrow ['descripcionPartidaEsp'] );
    }
    $SQL = "SELECT DISTINCT categoryid, CONCAT(categoryid, ' - ', categorydescription) as categorydescription FROM stockcategory ".$whereGenPartida." ORDER BY categoryid ASC";
    $ErrMsg = "No se obtuvo las Partidas Genéricas";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $infoPartida[] = array( 'value' => $myrow ['categoryid'], 'texto' => $myrow ['categorydescription'] );
    }
    $SQL1 = "SELECT DISTINCT unitid, unitname FROM unitsofmeasure WHERE mbflag in ('".$flag."')";
    $ErrMsg1 = "No se obtuvo las unidades de medida";
    $TransResult1 = DB_query($SQL1, $db, $ErrMsg1);
    while ($myrow1 = DB_fetch_array($TransResult1)) {
        $infoUnidad[] = array( 'value' => $myrow1 ['unitid'], 'texto' => $myrow1 ['unitname'] );
    }
    $SQL2 = "SELECT DISTINCT eq_stockid, CONCAT(eq_stockid, ' - ',descPartidaEspecifica) as descPartidaEspecifica FROM tb_partida_articulo ".$sqlWhere." ORDER BY descPartidaEspecifica ASC";
    $ErrMsg2 = "No se obtuvo el COG de Producto";
    $TransResult2 = DB_query($SQL2, $db, $ErrMsg2);
    while ($myrow2 = DB_fetch_array($TransResult2)) {
        $infoCabms[] = array( 'value' => $myrow2 ['eq_stockid'], 'texto' => $myrow2 ['descPartidaEspecifica'] );
    }
    $contenido = array('datosPartidaEsp' => $infoPartidaEsp, 'datosPartida' => $infoPartida, 'datosUnidad' => $infoUnidad, 'datosCabms' => $infoCabms);
    $result = true;
}

if ($option == 'mostrarInfoCABMS') {
    $infoCat = array();
    $infoPartidaEsp = array();
    $infoUnidad = array();
    $infoCabms = array();
    $eqStockid = $_POST['eqStockid'];
    $sqlWhere = "";
    /*$SQL = " SELECT stockid, categoryid, descripcion as descCat, units, mbflag, discontinued, s.eq_stockid, pp.partidacalculada, CONCAT(pp.partidacalculada, ' - ', pp.descripcion) as descripcionPartidaEsp, s.eq_stockid,
    FROM stockmaster s
    INNER JOIN tb_partida_articulo tpa on (s.eq_stockid = tpa.eq_stockid)
    INNER JOIN tb_cat_partidaspresupuestales_partidaespecifica pp ON (tpa.partidaEspecifica = pp.partidacalculada)
    WHERE s.eq_stockid= '".$eqStockid."' ";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $infoCabms[] = array( 
            'stockid' => $myrow ['stockid'], 
            'categoryid' => $myrow ['categoryid'],
            'descCat' => $myrow ['descCat'], 
            'units' => $myrow ['units'], 
            'mbflag' => $myrow ['mbflag'], 
            'discontinued' => $myrow ['discontinued'], 
            'eq_stockid' => $myrow ['eq_stockid'], 
            'partidacalculada' => $myrow ['partidacalculada'],
            'descripcionPartidaEsp' => $myrow ['descripcionPartidaEsp']  
        );
    }*/
    $SQL = "SELECT DISTINCT eq_stockid, CONCAT(eq_stockid, ' - ',descPartidaEspecifica) as descPartidaEspecifica , pp.partidacalculada, CONCAT(pp.partidacalculada, ' - ', pp.descripcion) as descripcionPartidaEsp, IF (partidacalculada  > 20000 AND partidacalculada < 29999,'B', IF (partidacalculada  > 30000 AND partidacalculada < 36999,'D', IF (partidacalculada  > 38000 AND partidacalculada < 39999,'D',IF (partidacalculada  > 50000 AND partidacalculada < 59999,'D','')))) AS MBFLAG
    FROM tb_partida_articulo tpa
    INNER JOIN tb_cat_partidaspresupuestales_partidaespecifica pp ON (tpa.partidaEspecifica = pp.partidacalculada)
    WHERE eq_stockid= '".$eqStockid."'";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
     while ($myrow = DB_fetch_array($TransResult)) {
        $infoCabms[] = array( 
            'eq_stockid' => $myrow ['eq_stockid'], 
            'descPartidaEspecifica' => $myrow ['descPartidaEspecifica'],
            'mbflag' => $myrow ['mbflag'], 
            'partidacalculada' => $myrow ['partidacalculada'], 
            'descripcionPartidaEsp' => $myrow ['descripcionPartidaEsp']
        );
    }
    /*
    switch ($myrow ['mbflag']) {
        case 'B':            
            $whereEspPartida = " WHERE ccap = 2 AND ccap = 5";
            $whereGenPartida = " WHERE categoryid BETWEEN 200 AND 299 OR categoryid BETWEEN 500 AND 599 ";
            $sqlWhere = " WHERE partidaEspecifica = 2000 OR partidaEspecifica between 20000 AND 29999 OR  partidaEspecifica between 50000 AND 59999";
            $flag = 'B';
            break;
        case 'D':
            $whereEspPartida = " WHERE ccap = 3 AND ccon != 7 ";
            $whereGenPartida = " WHERE categoryid BETWEEN 300 AND 369 OR categoryid BETWEEN 380 AND 399 ";
            $sqlWhere = " WHERE partidaEspecifica = 3000 OR partidaEspecifica between 30000 AND 36999 OR  partidaEspecifica between 38000 AND 39999";
            $flag = 'D';
            break;
        default:
            $whereEspPartida = "";
            $whereGenPartida = "";
            $flag = "B','D";
            $sqlWhere = "";
            break;
    }*/
    if($myrow ['mbflag'] == 'B'){
        $whereEspPartida = " WHERE ccap = 2 OR ccap = 5";
        $whereGenPartida = " WHERE categoryid BETWEEN 200 AND 299 OR categoryid BETWEEN 500 AND 599 ";
        $sqlWhere = " WHERE partidaEspecifica = 2000 OR partidaEspecifica between 20000 AND 29999 OR  partidaEspecifica between 50000 AND 59999";
        $flag = 'B';
    }
    if($myrow ['mbflag'] == 'D'){
        $whereEspPartida = " WHERE ccap = 3 AND ccon != 7 ";
        $whereGenPartida = " WHERE categoryid BETWEEN 300 AND 369 OR categoryid BETWEEN 380 AND 399 ";
        $sqlWhere = " WHERE partidaEspecifica = 3000 OR partidaEspecifica between 30000 AND 36999 OR  partidaEspecifica between 38000 AND 39999";
        $flag = 'D';
    }
    if($myrow ['mbflag'] == '' || $myrow ['mbflag'] == 'undefined' || $myrow ['mbflag'] == '0' || $myrow ['mbflag'] == 0 || $myrow ['mbflag'] == null){
        $whereEspPartida = "";
        $whereGenPartida = "";
        $flag = "B','D";
        $sqlWhere = "";
    }

    $sql2 = "SELECT DISTINCT stockflag, stocknameflag FROM stocktypeflag WHERE sn_activo = '1' ORDER BY stocknameflag ASC";
    $ErrMsg2 = "No se obtuvo los tipos";
    $result2 = DB_query($sql2, $db, $ErrMsg2);
    while ($myrow2=DB_fetch_array($result2)) {
        $infoMbflag[] = array( 'value' => $myrow2 ['stockflag'], 'texto' => $myrow2 ['stocknameflag'] );
    }
    $SQL = "SELECT DISTINCT partidacalculada, CONCAT(partidacalculada, ' - ', descripcion) as descripcionPartidaEsp FROM tb_cat_partidaspresupuestales_partidaespecifica ".$whereEspPartida." ORDER BY partidacalculada ASC";
    $ErrMsg = "No se obtuvo las Partidas Específica";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $infoPartidaEsp[] = array( 'value' => $myrow ['partidacalculada'], 'texto' => $myrow ['descripcionPartidaEsp'] );
    }
    $SQL = "SELECT DISTINCT categoryid, CONCAT(categoryid, ' - ', categorydescription) as categorydescription FROM stockcategory ".$whereGenPartida." ORDER BY categoryid ASC";
    $ErrMsg = "No se obtuvo las Partidas Genéricas";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $infoCat[] = array( 'value' => $myrow ['categoryid'], 'texto' => $myrow ['categorydescription'] );
    }
    $SQL1 = "SELECT DISTINCT unitid, unitname FROM unitsofmeasure WHERE mbflag in ('".$flag."')";
    $ErrMsg1 = "No se obtuvo las unidades de medida";
    $TransResult1 = DB_query($SQL1, $db, $ErrMsg1);
    while ($myrow1 = DB_fetch_array($TransResult1)) {
        $infoUnidad[] = array( 'value' => $myrow1 ['unitid'], 'texto' => $myrow1 ['unitname'] );
    }
    $contenido = array('datosPartidaEsp' => $infoPartidaEsp, 'datosCat' => $infoCat, 'datosUnidad' => $infoUnidad, 'datosMbflag' => $infoMbflag, 'datosCabms' => $infoCabms);
    $result = true;
}

if($option == 'bloquearStock'){
    $StockID = $_POST['stockID'];
    $sql= "SELECT COUNT(*) FROM stockmoves WHERE stockid='$StockID'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_row($result);
    if ($myrow[0]>0) {
        $contenido = 1;
        $result = true;
    } else {
        $sql= "SELECT COUNT(*) FROM bom WHERE component='$StockID'";
        $result = DB_query($sql, $db);
        $myrow = DB_fetch_row($result);
        if ($myrow[0]>0) {
            $contenido = 1;
            $result = true;
        } else {
            $sql= "SELECT COUNT(*) FROM salesorderdetails WHERE stkcode='$StockID'";
            $result = DB_query($sql, $db);
            $myrow = DB_fetch_row($result);
            if ($myrow[0]>0) {
                $contenido = 1;
                $result = true;
            } else {
                $sql= "SELECT COUNT(*) FROM salesanalysis WHERE stockid='$StockID'";
                $result = DB_query($sql, $db);
                $myrow = DB_fetch_row($result);
                if ($myrow[0]>0) {
                    $contenido = 1;
                    $result = true;
                } else {
                    $sql= "SELECT COUNT(*) FROM purchorderdetails WHERE itemcode='$StockID'";
                    $result = DB_query($sql, $db);
                    $myrow = DB_fetch_row($result);
                    if ($myrow[0]>0) {
                        $contenido = 1;
                        $result = true;
                    } else {
                        $sql = "SELECT SUM(quantity) AS qoh FROM locstock WHERE stockid='$StockID'";
                        $result = DB_query($sql, $db);
                        $myrow = DB_fetch_row($result);
                        if ($myrow[0]!=0) {
                            $contenido = $bloqueoError;
                            $result = true;
                        } else {
                            $sql = "SELECT count(*) as trans FROM loctransfers WHERE stockid = '$StockID' and shipqty > recqty";
                            $result = DB_query($sql, $db);
                            $myrow = DB_fetch_row($result);
                            if ($myrow[0]!=0) {
                                $contenido = 1;
                                $result = true;
                            }
                        }
                    }
                }
            }
        }
    }
}

/****************************************** Stocks ********************************************************/



$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
