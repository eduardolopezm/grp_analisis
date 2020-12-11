<?php
/**
 * Modelo para el ABC de Finalidad
 *
 * @category ABC
 * @package ap_grp
 * @author Luis Aguilar Sandoval <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 02/08/2017
 * Se realizan operación pero el Alta, Baja y Modificación. Mediante las validaciones creadas para la operación seleccionada
 */

 //ini_set('display_errors', 1);
 //ini_set('log_errors', 1);
 //error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

//Envio a capa

session_start();
$PageSecurity = 11;
$PathPrefix = '../';

include($PathPrefix. "includes/SecurityUrl.php");
include($PathPrefix.'abajo.php');
include($PathPrefix.'config.php');
include($PathPrefix.'includes/ConnectDB.inc');

$funcion = 81;
include($PathPrefix.'includes/SecurityFunctionsHeader.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');
include($PathPrefix .'includes/DateFunctions.inc');

$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

if ($option == 'eliminarProducto') {
    $StockID = $_POST['stockid'];
    $sql="DELETE FROM stockmaster WHERE stockid='$StockID'";
    $TransResult=DB_query($sql, $db);
    $contenido = $StockID;
    $result = true;
}

if ($option == 'modificarProducto') {
    $arrayStock = $_POST['arrayStock'];

    for ($a=0; $a<count($arrayStock); $a++) {
        $tipo = $arrayStock['tipo'];
        $partida = $arrayStock['partida'];
        $cambs = $arrayStock['cambs'];
        $stockid = $arrayStock['code'];
        if ($tipo == 'B') {
            $fam = $arrayStock['fam'];
        } else {
            $fam = 0;
        }
        $status = $arrayStock['status'];
        $unidad = $arrayStock['unidad'];
        $longDesc = $arrayStock['longDesc'];
        $shortDesc = $arrayStock['shortDesc'];

        $cadena = " mbflag = '".$tipo."', categoryid = '".substr($partida, 0, 3)."', eq_stockid = '". $cambs."', nu_cve_familia = '".$fam."', discontinued = ".$status.", units = '".$unidad."', description = '".$shortDesc."', longdescription = '".$longDesc."' ";
    }

    $SQL = "UPDATE stockmaster SET ".$cadena." WHERE stockid = '".$stockid."'" ;
    $ErrMsg = "No se obtuvo los Tipos de Productos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $contenido = $stockid;
    $result = true;
}

if ($option == 'guardarProducto') {
    $arrayStock = $_POST['arrayStock'];

    for ($a=0; $a<count($arrayStock); $a++) {
        $tipo = $arrayStock['tipo'];
        $partida = $arrayStock['partida'];
        $cambs = $arrayStock['cambs'];
        $stockid = $arrayStock['code'];
        if ($tipo == 'B') {
            $fam = $arrayStock['fam'];
        } else {
            $fam = 0;
        }
        $status = $arrayStock['status'];
        $unidad = $arrayStock['unidad'];
        $longDesc = $arrayStock['longDesc'];
        $shortDesc = $arrayStock['shortDesc'];

        if (empty($cambs) or $cambs=="") {
            $sql = "SELECT eq_stockid FROM tb_partida_articulo WHERE partidaEspecifica = '".$partida."'";
            $ErrMsg = "No se obtuvo los Tipos de Productos";
            $result= DB_query($sql, $db, $ErrMsg);
            $myrow = DB_fetch_array($result);
            $cambs = $myrow['eq_stockid'];
        }

        $cadena ="('".$stockid."','$shortDesc','$longDesc','','','".substr($partida, 0, 3)."','0','".$unidad."','$tipo','0',".$status.",'0','0','0','0.0000','0.0000','0.00','0.00','0.00','','','4','2','','0','0','1','0','0',0,'','','','".$cambs."','0','','0','$fam')";
    }

    if (fnDatosStockID($stockid, $db) != '') {
        $contenido = $stockid;
        $result = false;
    } else {
        $sql = "INSERT INTO stockmaster (
              stockid,
              description,
              longdescription,
              manufacturer,
              stockautor,
              categoryid,
              percentfactorigi,
              units,
              mbflag,
              eoq,
              discontinued,
              controlled,
              serialised,
              perishable,
              volume,
              kgs,
              large,
              width,
              height,
              barcode,
              discountcategory,
              taxcatid,
              decimalplaces,
              appendfile,
              shrinkfactor,
              pansize,
              idclassproduct,
              taxcatidret,
              idetapaflujo,
              flagadvance,
              fichatecnica,
              pkg_type,
              stocksupplier,
              eq_stockid,
              eq_conversion_factor,
              unitequivalent,
              flagcommission,
              nu_cve_familia
              )
            VALUES ". $cadena;
        
        $ErrMsg = "No se obtuvo los Tipos de Productos";
        $TransResult = DB_query($sql, $db, $ErrMsg);

        $sql = "INSERT INTO locstock (loccode, stockid)
        SELECT locations.loccode, '" . $stockid . "' FROM locations";
        $ErrMsg =  _('El código') . ' ' . $stockid .  ' ' . _('no se agrego a los almacenes');
        $InsResult = DB_query($sql, $db, $ErrMsg);
        
        $sql = "INSERT INTO stockcostsxlegal (lastupdatedate, stockid, lastcost, avgcost, legalid, lastpurchaseqty)
        SELECT NOW(), '" . $stockid . "', 0, 0, legalbusinessunit.legalid, 0
        FROM legalbusinessunit";
        $ErrMsg =  _('El código') . ' ' . $stockid .  ' ' . _('no se agrego a tabla de stockcostsxlegal');
        $InsResult = DB_query($sql, $db, $ErrMsg);
        
        $sql = "INSERT INTO stockcostsxlegalnew (lastupdatedate, stockid, lastcost, avgcost, legalid)
        SELECT NOW(), '" . $stockid . "', 0, 0, legalbusinessunit.legalid
        FROM legalbusinessunit";
        $ErrMsg =  _('El código') . ' ' . $stockid .  ' ' . _('no se agrego a tabla de stockcostsxlegal');
        $InsResult = DB_query($sql, $db, $ErrMsg);
        
        $sql = "INSERT INTO stockcostsxtag (lastupdatedate, stockid, lastcost, avgcost, tagref)
        SELECT NOW(), '" . $stockid . "', 0, 0, tags.tagref
        FROM tags";
        $ErrMsg =  _('El código') . ' ' . $stockid .  ' ' . _('no se agrego a tabla de stockcostsxtag');
        $InsResult = DB_query($sql, $db, $ErrMsg);

        $contenido = $stockid;
        $result = true;
    }
}

if ($option == 'buscarProducto') {
    $StockID = $_POST['stockid'];
    $contenido = array('datos' => fnDatosStockID($StockID, $db));
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

if ($option == 'mostrarUnits') {
    $infoUnits = array();
    $mbflag = $_POST['mbflag'];
    $infoUnits = fnUnits($mbflag, $db);
    $contenido = array('datos' => $infoUnits);
    $result = true;
}

if ($option == 'mostrarPartida') {
    $infoPartidaEsp = array();
    $mbflag = $_POST['mbflag'];
    $infoPartidaEsp = fnPartidaEspecifica($mbflag, $db);
    $contenido = array('datos' => $infoPartidaEsp);
    $result = true;
}

if ($option == 'mostrarCabms') {
    $infoCabms = array();
    $mbflag = $_POST['mbflag'];
    $partidaID = $_POST['partidaid'];
    $infoCabms = fnCabms($mbflag, $partidaID, $db);
    $contenido = array('datos' => $infoCabms);
    $result = true;
}

if ($option == 'bloquearStock') {
    $StockID= $_POST['stockID'];

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
                            } else {
                                $sql = "SELECT count(*) as producto FROM tb_solicitudes_almacen_detalle 
                                        WHERE ln_clave_articulo = '".$StockID."'";
                                
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
}

if ($option == 'validarCMABSGeneral') {
    $SQL ="SELECT eq_stockid FROM tb_partida_articulo WHERE partidaEspecifica = '".$_POST['partida']."'";
    $result = DB_query($SQL, $db);
    $myrow = DB_fetch_array($result);

    $eq_stockid=$myrow ['eq_stockid'];
    $rest = substr($myrow ['eq_stockid'], 0, 1);
        
    if ($rest =="G") {
        $info[] = array("cambsGeneral"=>"1");
    } else {
        $info[] = array("cambsGeneral"=>"0");
    }

    $contenido = array('datos' => $info);

    $result=true;
}

function fnPartidaEspecifica($mbflag, $db)
{
    $arrayPartida = array();
    $sqlWhereMbflag = "";

    if ($mbflag == 'B') {
        $sqlWhereMbflag = "WHERE ccap = 2 "; // ae quito ya que la contadora dijo que no debian ir OR ccap = 5, por que aparecio el 58904
    }
    if ($mbflag == 'D') {
        $sqlWhereMbflag = " WHERE ccap = 3 AND ccon != 7 ";
    }
    $SQL = "SELECT DISTINCT partidacalculada, CONCAT(partidacalculada, ' - ', descripcion) as descripcionPartidaEsp FROM tb_cat_partidaspresupuestales_partidaespecifica ".$sqlWhereMbflag." ORDER BY partidacalculada ASC";
    $ErrMsg = "No se obtuvo los Tipos de Productos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $arrayPartida[] = array( 'value' => $myrow ['partidacalculada'], 'texto' => $myrow ['descripcionPartidaEsp'] );
    }
    return $arrayPartida;
}

function fnCabms($mbflag, $partidaID, $db)
{
    $arrayCabms = array();
    $sqlWhere = "";
    if ($mbflag == 'B') {
        $sqlWhere = " WHERE partidaEspecifica = 2000 OR partidaEspecifica between 20000 AND 29999 OR  partidaEspecifica between 50000 AND 59999";
    }
    if ($mbflag == 'D') {
        $sqlWhere = " WHERE partidaEspecifica = 3000 OR partidaEspecifica between 30000 AND 36999 OR  partidaEspecifica between 38000 AND 39999";
    }
    if ($partidaID != '') {
        $SQL="SELECT DISTINCT tpa.eq_stockid, CONCAT(tpa.eq_stockid, ' - ',tpa.descPartidaEspecifica) AS descPartidaEspecifica,  tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada, tpa.partidaEspecifica
            FROM tb_cat_partidaspresupuestales_partidaespecifica
            INNER JOIN tb_partida_articulo tpa ON tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = tpa.partidaEspecifica
            WHERE tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = '".$partidaID."'  and tpa.eq_stockid not like'G%' GROUP BY tpa.eq_stockid, descPartidaEspecifica, tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada, tpa.partidaEspecifica  ";
        $ErrMsg = "No se obtuvo el COG de Producto";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $arrayCabms[] = array( 'value' => $myrow ['eq_stockid'], 'texto' => $myrow ['descPartidaEspecifica'] );
        }
    } else {
        $info = array();
        $SQL = "SELECT distinct eq_stockid, CONCAT(eq_stockid, ' - ',descPartidaEspecifica) as descPartidaEspecifica FROM tb_partida_articulo ".$sqlWhere." ORDER BY descPartidaEspecifica ASC";
        $ErrMsg = "No se obtuvo el COG de Producto";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $arrayCabms[] = array( 'value' => $myrow ['eq_stockid'], 'texto' => $myrow ['descPartidaEspecifica'] );
        }
    }

    //echo "sql:".$SQL;
    return $arrayCabms;
}

function fnUnits($mbflag, $db)
{
    $arrayUnits = array();
    $sqlWhereMbflag = "";
    if ($mbflag == 'B') {
        $sqlWhereMbflag = "WHERE mbflag = 'B' ";
    }
    if ($mbflag == 'D') {
        $sqlWhereMbflag = " WHERE mbflag = 'D' ";
    }
    $SQL = "SELECT unitid, unitname FROM unitsofmeasure ".$sqlWhereMbflag." ORDER by unitname";
    $ErrMsg = "No se obtuvo los Tipos de Productos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $arrayUnits[] = array( 'value' => $myrow ['unitid'], 'texto' => $myrow ['unitname'] );
    }
    return $arrayUnits;
}

function fnDatosStockID($StockID, $db)
{
    $arrayDatosStock = array();
    $sql = "SELECT stockid,
        description,
        longdescription,
        manufacturer,
        categoryid,
        units,
        mbflag,
        discontinued,
        controlled,
        serialised,
        perishable,
        eoq,
        volume,
        kgs,
        large,
        width,
        height,
        barcode,
        discountcategory,
        taxcatid,
        decimalplaces,
        appendfile,
        nextserialno,
        idclassproduct,
        taxcatidRet,
        idetapaflujo,
        stockautor,
        fichatecnica,
        percentfactorigi,
        pkg_type,
        stocksupplier,
        flagadvance,
        s.eq_stockid,
        eq_conversion_factor,
        unitequivalent,
        partidacalculada as partidaid,
        nu_cve_familia
    FROM stockmaster s
        INNER JOIN tb_partida_articulo tpa on (s.eq_stockid = tpa.eq_stockid)
        INNER JOIN tb_cat_partidaspresupuestales_partidaespecifica ON (tpa.partidaEspecifica = tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada) 
        WHERE stockid = '$StockID'";
    $result = DB_query($sql, $db);

    // $sqlEQGeneral="SELECT eq_stockid from tb_partida_articulo where eq_stockid like'G%'";
    // $resultEQGeneral= DB_query($sqlEQGeneral, $db);
    // $myrowEQGeneral = DB_fetch_array($resultEQGeneral);

    if (DB_num_rows($result) > 0) {
        while ($myrow = DB_fetch_array($result)) {
            if ($myrow ['mbflag'] == 'D') {
                $myrow ['nu_cve_familia'] = '';
            }

            $eq_stockid=$myrow ['eq_stockid'];
            if (empty($eq_stockid) or $eq_stockid=="0") {
                $eq_stockid="";
            }
            // $rest = substr($myrow ['eq_stockid'], 0,1);
        
            // if($rest =="G"){
            //     $eq_stockid="";
            // }


            $arrayDatosStock[] = array(
                'stockid' => $myrow ['stockid'],
                'description' => $myrow ['description'],
                'longdescription' => $myrow ['longdescription'],
                'manufacturer' => $myrow ['manufacturer'],
                'categoryid' => $myrow ['categoryid'],
                'units' => $myrow ['units'],
                'mbflag' => $myrow ['mbflag'],
                'discontinued' => $myrow ['discontinued'],
                'controlled' => $myrow ['controlled'],
                'serialised' => $myrow ['serialised'],
                'perishable' => $myrow ['perishable'],
                'eoq' => $myrow ['eoq'],
                'volume' => $myrow ['volume'],
                'kgs' => $myrow ['kgs'],
                'large' => $myrow ['large'],
                'width' => $myrow ['width'],
                'height' => $myrow ['height'],
                'barcode' => $myrow ['barcode'],
                'discountcategory' => $myrow ['discountcategory'],
                'taxcatid' => $myrow ['taxcatid'],
                'decimalplaces' => $myrow ['decimalplaces'],
                'appendfile' => $myrow ['appendfile'],
                'nextserialno' => $myrow ['nextserialno'],
                'idclassproduct' => $myrow ['idclassproduct'],
                'taxcatidRet' => $myrow ['taxcatidRet'],
                'idetapaflujo' => $myrow ['idetapaflujo'],
                'stockautor' => $myrow ['stockautor'],
                'fichatecnica' => $myrow ['fichatecnica'],
                'percentfactorigi' => $myrow ['percentfactorigi'],
                'pkg_type' => $myrow ['pkg_type'],
                'stocksupplier' => $myrow ['stocksupplier'],
                'flagadvance' => $myrow ['flagadvance'],
                'eq_stockid' => $eq_stockid,
                'eq_conversion_factor' => $myrow ['eq_conversion_factor'],
                'unitequivalent' => $myrow ['unitequivalent'],
                'partidaid' => $myrow ['partidaid'],
                'nu_cve_familia' => $myrow ['nu_cve_familia'] );
        }
    } else {
        $arrayDatosStock = "";
    }
    
    return $arrayDatosStock;
}

//Enviar a Capa


$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);

echo json_encode($dataObj);
