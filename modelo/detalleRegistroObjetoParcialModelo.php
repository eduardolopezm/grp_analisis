<?php
/** 
 * Modelo para el ABC de Fuente de Financiamiento
 *
 * @category ABC
 * @package ap_grp
 * @author Jesús Reyes Santos <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 07/11/2019
 * Fecha Modificación: 07/11/2019
 * Se realizan operación pero el Alta, Baja y Modificación. Mediante las validaciones creadas para la operación seleccionada
 */


//ini_set('display_errors', 1);
//ini_set('log_errors', 1);
//error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=2507;
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

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

if ($option == 'mostrarCatalogo') {
    $loccode = $_POST['loccode'];
    $stockid = $_POST['stockid'];
    $id_nu_objeto_detalle = $_POST['id_nu_objeto_detalle'];

    $info = array();
    $SQL ="SELECT DISTINCT
    CONCAT(`locstock`.`loccode`, ' - ' , `locations`.`locationname`) AS 'principalConcatenado',
    CONCAT(`locstock`.`stockid`, ' - ' , `stockmaster`.`description`) AS 'parcialConcatenado',
    bankaccounts.bankaccountname as nombreBanco,
    locstock.loccode as objetoPricipal, 
    tb_cat_objeto_detalle.ano as anio, 
    tb_cat_objeto_detalle.clave_presupuestal as clavePresupuestal, 
    tb_cat_objeto_detalle.cuenta_banco as cuentaBanco, 
    tb_cat_objeto_detalle.cuenta_abono as cuentaAbono, 
    tb_cat_objeto_detalle.cuenta_cargo as cuentaCargo,
    CASE WHEN tb_cat_objeto_detalle.estatus = 1 THEN 'Activo' ELSE 'Inactivo' END AS activo,
    tb_cat_objeto_detalle.estatus as estado, 
    locations.locationname as desc_principal, 
    locstock.stockid as objetoParcial, 
    stockmaster.description as desc_parcial, 
    tb_cat_objeto_detalle.id_nu_objeto_detalle as id_detalle
    FROM tb_cat_objeto_detalle 
    JOIN stockmaster on (stockmaster.stockid = tb_cat_objeto_detalle.stockid)
    JOIN locstock ON (locstock.stockid = tb_cat_objeto_detalle.stockid)
    JOIN locations on (locations.loccode = locstock.loccode)
    LEFT JOIN bankaccounts on (bankaccounts.accountcode = tb_cat_objeto_detalle.cuenta_banco)
    WHERE stockmaster.tipo_dato = 2 and stockmaster.discontinued = 1 and locations.tipo = 'ObjetoPrincipal' and locations.activo = 1
    ORDER BY locations.loccode, stockmaster.stockid,  tb_cat_objeto_detalle.id_nu_objeto_detalle ASC";
    $ErrMsg = "No se obtuvieron las Subfunciones";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'Detalle' => $myrow ['id_detalle'],
                'Ano' => $myrow ['anio'],
                'CP' => $myrow ['clavePresupuestal'],
                'CB' => $myrow ['cuentaBanco'],
                'CA' => $myrow ['cuentaAbono'],
                'CC' => $myrow ['cuentaCargo'],
                'Estado' => $myrow ['estado'],
                'idPrincipal' => $myrow ['objetoPricipal'],
                'descPrincipal' => $myrow ['desc_principal'],
                'idParcial' => $myrow ['objetoParcial'],
                'descParcial' => $myrow ['desc_parcial'],
                'PrincipalConcatenado' => $myrow ['principalConcatenado'],
                'ParcialConcatenado' => $myrow ['parcialConcatenado'],
                'Banco' => $myrow ['nombreBanco'],
                'Activo' => $myrow ['activo'],
                'Modificar' => '<a onclick="fnModificar('.$myrow ['id_detalle'].',\''.$myrow ['anio'].'\',\''.$myrow ['clavePresupuestal'].'\',\''.$myrow ['cuentaBanco'].'\',\''.$myrow ['cuentaAbono'].'\',\''.$myrow ['cuentaCargo'].'\',\''.$myrow ['estado'].'\',\''.$myrow ['objetoPricipal'].'\',\''.$myrow ['desc_principal'].'\',\''.$myrow ['objetoParcial'].'\',\''.$myrow ['desc_parcial'].'\')"><span class="glyphicon glyphicon-edit"></span></a>');
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'PrincipalConcatenado', type: 'string' },";
    $columnasNombres .= "{ name: 'ParcialConcatenado', type: 'string' },";
    $columnasNombres .= "{ name: 'Ano', type: 'string' },";
    $columnasNombres .= "{ name: 'CP', type: 'string' },";
    $columnasNombres .= "{ name: 'Banco', type: 'string' },";
    $columnasNombres .= "{ name: 'CA', type: 'string' },";
    $columnasNombres .= "{ name: 'CC', type: 'string' },";
    $columnasNombres .= "{ name: 'Activo', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Objeto/Principal', datafield: 'PrincipalConcatenado', width: '13%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Objeto/Parcial', datafield: 'ParcialConcatenado', width: '12%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Año', datafield: 'Ano', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Clave Presupuestal', datafield: 'CP', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Cuenta de Banco', datafield: 'Banco', width: '20%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Cuenta de Abono', datafield: 'CA', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Cuenta de cargo', datafield: 'CC', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'Activo', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '5%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

if ($option == 'AgregarCatalogo') {
    $idDetalle = $_POST['idDetalle'];
    $finalidad = $_POST['finalidad'];
    $funcion = $_POST['funcion'];
    $clave = $_POST['clave'];
    $descripcion = $_POST['descripcion'];
    $banco = $_POST['banco'];
    $abono = $_POST['abono'];
    $cargo = $_POST['cargo'];
    $estatus = $_POST['estatus'];
    $proceso = $_POST['proceso'];
    if ($proceso == 'Modificar') {
        $SQL = "SELECT estatus, id_nu_objeto_detalle FROM tb_cat_objeto_detalle WHERE  estatus = '1' and ano = '$clave' and loccode = '$finalidad' and stockid = '$funcion'";
        $ErrMsg = "No se obtuvieron los detalles";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    
        if (DB_num_rows($TransResult) == 0) {
            // Si no hay activo
            $info = array();
            $SQL = "UPDATE tb_cat_objeto_detalle SET ano = '$clave', clave_presupuestal = '$descripcion', cuenta_banco = '$banco', cuenta_abono = '$abono', cuenta_cargo = '$cargo', estatus = '$estatus' WHERE id_nu_objeto_detalle = '$idDetalle'";
            $ErrMsg = "No se agregó la informacion";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $contenido = "Se modificó el registro del Catálogo Detalle de Objeto Parcial con éxito";
            $result = true;
        } else {
            // $TransResult = DB_query($SQL, $db, $ErrMsg);
            $myrow = DB_fetch_array($TransResult);
            // echo "\n estatus: ".$myrow['estatus'];
            // echo "\n id_nu_objeto_detalle: ".$myrow['id_nu_objeto_detalle'];
            // echo "\n idDetalle: ".$idDetalle;
            // exit();
            if($myrow['estatus'] == 1 && $myrow['id_nu_objeto_detalle'] == $idDetalle){
                // Si hay activo y es el mismo registro
                $SQL = "UPDATE tb_cat_objeto_detalle SET ano = '$clave', clave_presupuestal = '$descripcion', cuenta_banco = '$banco', cuenta_abono = '$abono', cuenta_cargo = '$cargo', estatus = '$estatus' WHERE id_nu_objeto_detalle = '$idDetalle'";
                $ErrMsg = "No se agregó la informacion";
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $contenido = "Se modificó el registro del Catálogo Detalle de Objeto Parcial con éxito";
                $result = true;
            } elseif($myrow['estatus'] == 1 && $myrow['id_nu_objeto_detalle'] != $idDetalle){
                $Mensaje = "Proceso completado.";
                $contenido = "No se modificó el registro del Catálogo Detalle de Objeto Parcial, porque ya existe uno activo.";
                $result = true;
            } else {
                $Mensaje = "Proceso completado.";
                $contenido = "No se modificó el registro del Catálogo Detalle de Objeto Parcial, porque ya existe.";
                $result = true;
            }
        }
    } else {
        if(fnValidarExiste($funcion , $db)){
            $SQL = "SELECT estatus FROM tb_cat_objeto_detalle WHERE  estatus = '$estatus' and ano = '$clave' and loccode = '$finalidad' and stockid = '$funcion'";
            $ErrMsg = "No se obtuvieron los detalles";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

                if (DB_num_rows($TransResult) == 0) {
                    $info = array();
                    $SQL = "INSERT INTO tb_cat_objeto_detalle (`loccode`, `stockid`, `ano`, `clave_presupuestal`, `cuenta_banco`, `cuenta_abono`, `cuenta_cargo`, `estatus`)
                            VALUES ('".$finalidad."','".$funcion."','".$clave."','".$descripcion."','".$banco."','".$abono."','".$cargo."','".$estatus."')";


                    $ErrMsg = "No se agrego la informacion";
                    $TransResult = DB_query($SQL, $db, $ErrMsg);

                    $contenido = "Se agregó el registro del Detalle de Objeto Parcial con éxito";
    
                    $result = true;
                } else {
                    $myrow = DB_fetch_array($TransResult);

                    if($myrow['activo']==1){
                        $Mensaje = "3|Error al insertar el registro del Catálogo Detalle de Objeto Parcial.";
                        $contenido = "Ya existe Detalle de Objeto Parcial con la clave: ";
                        $result = false;
                    }else{
                        $Mensaje = "Proceso completado.";
                        $contenido = "No se agregó el registro del Catálogo Detalle de Objeto Parcial, porque ya existe.";

                        $SQL = "UPDATE tb_cat_objeto_detalle SET ano = '$clave', clave_presupuestal = '$descripcion', cuenta_banco = '$banco', cuenta_abono = '$abono', cuenta_cargo = '$cargo', estatus = '$estatus' WHERE loccode = '$finalidad' and stockid = '$funcion' and id_nu_objeto_detalle = '$idDetalle'";

                        $TransResult = DB_query($SQL, $db, $ErrMsg);

                        $result = true;
                    }
                }
            
            
        }else{
            $contenido = "No existe Detalle de Objeto Parcial";
            $result = false;
        }
    }
}

if ($option == 'eliminarUR') {
    $fin = $_POST['idFinalidad'];
    $fun = $_POST['idFuncion'];
    $clave = $_POST['idSubfuncion'];
    $descripcion = $_POST['descripcion'];

    $info = array();
    
    $SQL = "UPDATE tb_cat_fuente_financiamiento SET activo = 0 WHERE id_identificacion = '$fin' and id_fuente = '$fun' and id_financiamiento = '$clave'";
    $ErrMsg = "No se realizó:  ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido = "Se eliminó el registro ".$clave." del Catálogo Fuente de Financiamiento con éxito";
    $result = true;
}

if ($option == 'mostrarClaveFuncion') {
    $id_identificacion = $_POST['id_identificacion'];
    // $loccode = $_POST['loccode'];
    // $accountcode = $_POST['accountcode'];
    // $SQL = "SELECT discontinued FROM stockmaster WHERE  stockid = '$clave' ORDER BY stockid ASC";
    //         $ErrMsg = "No se obtuvieron las unidades resposables";
    //         $TransResult = DB_query($SQL, $db, $ErrMsg);
    //         if (DB_num_rows($TransResult) == 0) {
    //             $info = array();
    //             $tagrefbank = "";
    //             $SQL = "SELECT tagref FROM bankaccounts WHERE accountcode = '$accountcode'";
    //             $TransResult = DB_query($SQL, $db, $ErrMsg);
    //             while ($myrow = DB_fetch_array($TransResult)) {
    //                 $tagrefbank = $myrow ['tagref'];
    //             }
    //         }
    
    // $SQL = "SELECT discontinued FROM stockmaster WHERE  stockid = '$clave' ORDER BY stockid ASC";
    //         $ErrMsg = "No se obtuvieron las unidades resposables";
    //         $TransResult = DB_query($SQL, $db, $ErrMsg);
    //         if (DB_num_rows($TransResult) == 0) {
    //             $info = array();
    //             $tagref = "";
    //             $SQL = "SELECT tagref FROM locations WHERE loccode = '$loccode'";
    //             $TransResult = DB_query($SQL, $db, $ErrMsg);
    //             while ($myrow = DB_fetch_array($TransResult)) {
    //                 $tagref = $myrow ['tagref'];
    //             }
    //         }

    $SQL = " SELECT DISTINCT
            chartdetailsbudgetbytag.accountcode as value,
            chartdetailsbudgetbytag.accountcode as texto,
            clasificador_ingreso.descripcion as nombre
            FROM chartdetailsbudgetbytag
            JOIN budgetConfigClave ON budgetConfigClave.idClavePresupuesto = chartdetailsbudgetbytag.idClavePresupuesto
            JOIN clasificador_ingreso ON clasificador_ingreso.rtc = chartdetailsbudgetbytag.rtc
            WHERE 
            budgetConfigClave.tipo_config = 1 
            AND chartdetailsbudgetbytag.anho = '$id_identificacion'";
    $ErrMsg = "No se obtuvo la Función";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    //and chartdetailsbudgetbytag.tagref = '$tagref' and chartdetailsbudgetbytag.tagref = '$tagrefbank'
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'id_fuente' => $myrow ['value'], 'nombr' => $myrow ['nombre'] , 'descripcion' => $myrow ['texto']);
    }

    $contenido = array('datos' => $info);
    $result = true;
}


if ($option == 'mostrarFuncion') {
    $id_identificacion = $_POST['id_identificacion'];
    $info = array();
    $SQL = "SELECT stockmaster.stockid, CONCAT(stockmaster.stockid, ' - ', stockmaster.description) as fuentedescription  FROM stockmaster JOIN locstock ON (locstock.stockid = stockmaster.stockid) WHERE stockmaster.discontinued = 1 and locstock.loccode = '$id_identificacion'  ORDER BY locstock.loccode, stockmaster.stockid ASC ";
    $ErrMsg = "No se obtuvo la Función";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'id_fuente' => $myrow ['stockid'], 'fuentedescription' => $myrow ['fuentedescription'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

function fnValidarExiste($idFuncion, $db){
    $SQL = "SELECT * FROM stockmaster WHERE discontinued = 1 and stockid = '".$idFuncion."'";
    $ErrMsg = "No se encontro la informacion de ".$idFuncion;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $existeFun = true;
    }else{
        $existeFun = false;
    }
    return $existeFun;
}

if ($option == 'obtenerInformacion') {
    $tipoAnio = $_POST['tipoAnio'];
    $tipoParcial = $_POST['tipoParcial'];
    $tipoAlmacen = $_POST['tipoAlmacen'];

    $sqlWhere = "";

    
    if(!empty($tipoAlmacen) &&  $tipoAlmacen != "'0'"){
        $sqlWhere .= " AND locstock.loccode LIKE ".$tipoAlmacen." ";
    }

    
    if (!empty($tipoParcial) &&  $tipoParcial != 0) {
        $sqlWhere .= " AND (locstock.stockid like '%".$tipoParcial."%' OR locstock.stockid like '%".$tipoParcial."%' )";
    }

    if (!empty($tipoAnio) &&  $tipoAnio != 0) {
        $sqlWhere .= " AND (tb_cat_objeto_detalle.ano like '%".$tipoAnio."%' OR tb_cat_objeto_detalle.ano like '%".$tipoAnio."%' )";
    }
    /*if (trim($tipoParcial) != '') {
        $sqlWhere .= " AND (locstock.stockid like $tipoParcial OR locstock.stockid like $tipoParcial )";
    }*/

 
    $info = array();
    
    $SQL ="SELECT DISTINCT
    CONCAT(`locstock`.`loccode`, ' - ' , `locations`.`locationname`) AS 'principalConcatenado',
    CONCAT(`locstock`.`stockid`, ' - ' , `stockmaster`.`description`) AS 'parcialConcatenado',
    bankaccounts.bankaccountname as nombreBanco,
    locstock.loccode as objetoPricipal, 
    tb_cat_objeto_detalle.ano as anio, 
    tb_cat_objeto_detalle.clave_presupuestal as clavePresupuestal, 
    tb_cat_objeto_detalle.cuenta_banco as cuentaBanco, 
    tb_cat_objeto_detalle.cuenta_abono as cuentaAbono, 
    tb_cat_objeto_detalle.cuenta_cargo as cuentaCargo,
    CASE WHEN tb_cat_objeto_detalle.estatus = 1 THEN 'Activo' ELSE 'Inactivo' END AS activo,
    tb_cat_objeto_detalle.estatus as estado, 
    locations.locationname as desc_principal, 
    locstock.stockid as objetoParcial, 
    stockmaster.description as desc_parcial, 
    tb_cat_objeto_detalle.id_nu_objeto_detalle as id_detalle
    FROM tb_cat_objeto_detalle 
    JOIN stockmaster on (stockmaster.stockid = tb_cat_objeto_detalle.stockid)
    JOIN locstock ON (locstock.stockid = tb_cat_objeto_detalle.stockid)
    JOIN locations on (locations.loccode = locstock.loccode)
    JOIN bankaccounts on (bankaccounts.accountcode = tb_cat_objeto_detalle.cuenta_banco)
    WHERE stockmaster.tipo_dato = 2 and stockmaster.discontinued = 1 and locations.tipo = 'ObjetoPrincipal' and locations.activo = 1"
    .$sqlWhere;
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    /*echo "<pre>".$SQL;
    exit();*/

    // $sqlEQGeneral="SELECT eq_stockid from tb_partida_articulo where eq_stockid like'G%'";
    // $resultEQGeneral= DB_query($sqlEQGeneral, $db);
    // $myrowEQGeneral = DB_fetch_array($resultEQGeneral);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'Detalle' => $myrow ['id_detalle'],
                'Ano' => $myrow ['anio'],
                'CP' => $myrow ['clavePresupuestal'],
                'CB' => $myrow ['cuentaBanco'],
                'CA' => $myrow ['cuentaAbono'],
                'CC' => $myrow ['cuentaCargo'],
                'Estado' => $myrow ['estado'],
                'idPrincipal' => $myrow ['objetoPricipal'],
                'descPrincipal' => $myrow ['desc_principal'],
                'idParcial' => $myrow ['objetoParcial'],
                'descParcial' => $myrow ['desc_parcial'],
                'PrincipalConcatenado' => $myrow ['principalConcatenado'],
                'ParcialConcatenado' => $myrow ['parcialConcatenado'],
                'Banco' => $myrow ['nombreBanco'],
                'Activo' => $myrow ['activo'],
                'Modificar' => '<a onclick="fnModificar('.$myrow ['id_detalle'].',\''.$myrow ['anio'].'\',\''.$myrow ['clavePresupuestal'].'\',\''.$myrow ['cuentaBanco'].'\',\''.$myrow ['cuentaAbono'].'\',\''.$myrow ['cuentaCargo'].'\',\''.$myrow ['estado'].'\',\''.$myrow ['objetoPricipal'].'\',\''.$myrow ['desc_principal'].'\',\''.$myrow ['objetoParcial'].'\',\''.$myrow ['desc_parcial'].'\')"><span class="glyphicon glyphicon-edit"></span></a>');
    }
    
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'PrincipalConcatenado', type: 'string' },";
    $columnasNombres .= "{ name: 'ParcialConcatenado', type: 'string' },";
    $columnasNombres .= "{ name: 'Ano', type: 'string' },";
    $columnasNombres .= "{ name: 'CP', type: 'string' },";
    $columnasNombres .= "{ name: 'Banco', type: 'string' },";
    $columnasNombres .= "{ name: 'CA', type: 'string' },";
    $columnasNombres .= "{ name: 'CC', type: 'string' },";
    $columnasNombres .= "{ name: 'Activo', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Objeto/Principal', datafield: 'PrincipalConcatenado', width: '13%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Objeto/Parcial', datafield: 'ParcialConcatenado', width: '12%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Año', datafield: 'Ano', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Clave Presupuestal', datafield: 'CP', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Cuenta de Banco', datafield: 'Banco', width: '20%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Cuenta de Abono', datafield: 'CA', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Cuenta de cargo', datafield: 'CC', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'Activo', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '5%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
