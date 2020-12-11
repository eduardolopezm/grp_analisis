<?php
/**
 * Plan de Cuentas
 *
 * @category
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 08/08/2017
 * Fecha Modificación: 08/08/2017
 * Modelos para el Plan de Cuentas
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
$funcion=128;
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');
// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";
$añoGeneral = '2017';//date('Y');

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

if ($option == 'agregarCuenta') {
    $cuenta = $_POST['cuenta'];
    $nombre = $_POST['nombre'];
    $naturaleza = $_POST['naturaleza'];
    $tipo = $_POST['tipo'];

    $info = array();
    $SQL = "SELECT accountcode FROM chartmaster WHERE accountcode = '".$cuenta."'";
    $ErrMsg = "No se obtuvo información de la cuenta";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) == 0) {
        $arrayCuenta=explode('.', $cuenta);
        $num = 1;
        $groupcode = "";
        for ($i=0; $i<count($arrayCuenta); $i++) {
            if (($i + 1) < count($arrayCuenta)) {
                if (empty($groupcode)) {
                    $groupcode .= "".$arrayCuenta[$i];
                } else {
                    $groupcode .= ".".$arrayCuenta[$i];
                }
            }

            $num ++;
        }

        $grupo = "";
        $SQL = "SELECT accountname FROM chartmaster WHERE accountcode = '".$tipo."'";
        $ErrMsg = "No se obtuvo el grupo";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $grupo = $myrow['accountname'];
        }

        $SQL = "INSERT INTO chartmaster (
        accountcode,
        accountname,
        group_,
        naturaleza,
        tipo,
        groupcode)
        VALUES (
        '" . $cuenta . "',
        '" . $nombre . "',
        '" . $grupo . "',
        " . $naturaleza . ",
        '" . $tipo . "',
        '" . $groupcode . "')";
        $ErrMsg = "No se agrego al cuenta";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        
        $contenido = '<h3><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Cuenta '.$cuenta.' agregada</h3>';
        $result = true;
    } else {
        $contenido = '<h3><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ya existe la cuenta</h3>';
        $result = false;
    }
}

if ($option == 'actualizarCuenta') {
    $cuenta = $_POST['cuenta'];
    $nombre = $_POST['nombre'];
    $naturaleza = $_POST['naturaleza'];

    $SQL = "UPDATE chartmaster SET accountname = '".$nombre."', naturaleza = '".$naturaleza."'
    WHERE accountcode = '".$cuenta."'";
    $ErrMsg = "No se agrego al cuenta";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    $contenido = '<h3><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Cuenta '.$cuenta.' actualizada</h3>';
    $result = true;
}

if ($option == 'obtenerInfoCuentasCont') {
    //$legalid = $_POST['legalid'];

    $sqlWhere = "";

    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'accountcode', type: 'string' },";
    $columnasNombres .= "{ name: 'accountname', type: 'string' },";
    $columnasNombres .= "{ name: 'naturaleza', type: 'string' },";
    $columnasNombres .= "{ name: 'genero', type: 'string' },";
    //$columnasNombres .= "{ name: 'group_', type: 'string' },";
    $columnasNombres .= "{ name: 'rubro', type: 'string' },";
    $columnasNombres .= "{ name: 'grupo', type: 'string' },";
    $columnasNombres .= "{ name: 'cuenta', type: 'string' },";

    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Clave', datafield: 'accountcode', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Nombre', datafield: 'accountname', width: '20%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Naturaleza', datafield: 'naturaleza', width: '8%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Genero', datafield: 'genero', width: '15%', cellsalign: 'left', align: 'center', hidden: false },";
    //$columnasNombresGrid .= " { text: 'Grupo', datafield: 'group_', width: '10%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Grupo', datafield: 'grupo', width: '15%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Rubro', datafield: 'rubro', width: '15%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Cuenta', datafield: 'cuenta', width: '15%', cellsalign: 'left', align: 'center', hidden: false },";

    $info = array();
    $SQL = "
    SELECT DISTINCT 
    chartmaster.accountcode,
    chartmaster.accountname,
    chartmaster.group_,
    CASE WHEN pandl=0 THEN 'BALANCE' ELSE 'RESULTADOS' END AS acttype,
    chartmaster.naturaleza,
    CASE WHEN chartmaster.naturaleza=1 THEN 'DEUDORA' ELSE 'ACREEDORA' END AS naturalezaname,
    chartTipos.nombreMayor,
    chartmaster.tipo,
    chartmasterGenero.accountname as genero,
    chartmasterGrupo.accountname as grupo,
    chartmasterRubro.accountname as rubro,
    chartmasterCuenta.accountname as cuenta,
    LENGTH(chartmaster.accountcode) as numCuenta,
    (SELECT TRUNCATE((MAX(LENGTH(chartmaster.accountcode)) - 7) / 2, 0) FROM chartmaster) as numSubcuentas
    FROM chartmaster 
    LEFT JOIN accountgroups ON chartmaster.group_=accountgroups.groupname
    LEFT JOIN chartTipos ON chartmaster.tipo = chartTipos.tipo
    LEFT JOIN chartmaster chartmasterGenero ON LENGTH(chartmasterGenero.accountcode) = 1 and chartmasterGenero.accountcode = substring(chartmaster.accountcode, 1, 1)
    LEFT JOIN chartmaster chartmasterGrupo ON LENGTH(chartmasterGrupo.accountcode) = 3 and chartmasterGrupo.accountcode = substring(chartmaster.accountcode, 1, 3)
    LEFT JOIN chartmaster chartmasterRubro ON LENGTH(chartmasterRubro.accountcode) = 5 and chartmasterRubro.accountcode = substring(chartmaster.accountcode, 1, 5)
    LEFT JOIN chartmaster chartmasterCuenta ON LENGTH(chartmasterCuenta.accountcode) = 7 and chartmasterCuenta.accountcode = substring(chartmaster.accountcode, 1, 7)
    ORDER BY chartmaster.accountcode
            ";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $num = 1;
    while ($myrow = DB_fetch_array($TransResult)) {
        $datos['accountcode'] = $myrow ['accountcode'];
        $datos['accountname'] = $myrow ['accountname'];
        $datos['naturaleza'] = $myrow ['naturalezaname'];
        $datos['genero'] = $myrow ['genero'];
        $datos['grupo'] = $myrow ['grupo'];
        $datos['rubro'] = $myrow ['rubro'];
        $datos['cuenta'] = $myrow ['cuenta'];
        $datos['tipo'] = $myrow ['nombreMayor'];
        $datos['Modificar'] = '<a onclick="fnModificarCuenta(\''.$myrow ['accountcode'].'\',\''.$myrow ['accountname'].'\',\''.$myrow ['naturaleza'].'\')"><span class="glyphicon glyphicon-edit"></span></a>';

        $nivel = 5;
        $numSub = 9;
        for ($i=1; $i <= $myrow ['numSubcuentas']; $i++) {
            if ($num == 1) {
                $columnasNombres .= "{ name: 'subcuenta".$nivel."', type: 'string' },";
                $columnasNombresGrid .= " { text: 'Nivel".$nivel."', datafield: 'subcuenta".$nivel."', width: '10%', cellsalign: 'left', align: 'center', hidden: false },";
            }
            if ($myrow['numCuenta'] > 7) {
                $nombreNivel = "";
                if ($numSub <= $myrow['numCuenta']) {
                    $SQL = "SELECT accountname FROM chartmaster WHERE accountcode = '".substr($myrow ['accountcode'], 0, $numSub)."'";
                    $TransResult2 = DB_query($SQL, $db, $ErrMsg);
                    if ($myrow2 = DB_fetch_array($TransResult2)) {
                        $nombreNivel = $myrow2 ['accountname'];
                    }
                }
                $datos['subcuenta'.$nivel] = $nombreNivel;
            } else {
                $datos['subcuenta'.$nivel] = "";
            }
            $nivel ++;
            $numSub = $numSub + 2;
        }

        $info[] = $datos;

        $num ++;
    }

    // Columnas para el GRID
    $columnasNombres .= "{ name: 'tipo', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' }";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $columnasNombresGrid .= " { text: 'Tipo', datafield: 'tipo', width: '14%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '5%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid);
    $result = true;
}

if ($option == 'mostrarGenero') {
    $info = array();
    //$SQL = "SELECT nu_clave as value, CONCAT(nu_clave, ' - ', txt_descripcion) as texto FROM tb_gl_genero WHERE sn_activo = '1'  ORDER BY nu_clave ASC";
    $SQL = "SELECT accountcode as value, CONCAT(accountcode, ' - ', accountname) as texto FROM chartmaster WHERE length(accountcode) = 1 ORDER BY value ASC";
    $ErrMsg = "No se obtuvo los Estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarGrupo') {
    $genero = $_POST['genero'];
    $sqlWhere = "";

    if (!empty($genero)) {
        $sqlWhere = " AND accountcode like '".$genero."%' ";
    }

    $info = array();
    // $SQL = "SELECT nu_clave as value, CONCAT(nu_clave, ' - ', txt_descripcion) as texto
    // FROM tb_gl_grupo WHERE sn_activo = '1' ".$sqlWhere." ORDER BY nu_clave ASC";
    $SQL = "SELECT accountcode as value, CONCAT(accountcode, ' - ', accountname) as texto FROM chartmaster WHERE length(accountcode) = 3 ".$sqlWhere." ORDER BY value ASC";
    $ErrMsg = "No se obtuvo los Estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarRubro') {
    $grupo = $_POST['grupo'];
    $sqlWhere = "";

    if (!empty($grupo)) {
        $sqlWhere = " AND accountcode like '".$grupo."%'";
    }

    $info = array();
    // $SQL = "SELECT nu_clave as value, CONCAT(nu_clave, ' - ', txt_descripcion) as texto
    // FROM tb_gl_rubro WHERE sn_activo = '1' ".$sqlWhere." ORDER BY nu_clave ASC";
    $SQL = "SELECT accountcode as value, CONCAT(accountcode, ' - ', accountname) as texto FROM chartmaster WHERE length(accountcode) = 5 ".$sqlWhere." ORDER BY value ASC";
    $ErrMsg = "No se obtuvo los Estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarCuenta') {
    $rubro = $_POST['rubro'];
    $sqlWhere = "";

    if (!empty($rubro)) {
        $sqlWhere = " AND accountcode like '".$rubro."%'";
    }

    $info = array();
    // $SQL = "SELECT nu_clave as value, CONCAT(nu_clave, ' - ', txt_descripcion) as texto
    // FROM tb_gl_cuenta WHERE sn_activo = '1' ".$sqlWhere." ORDER BY nu_clave ASC";
    $SQL = "SELECT accountcode as value, CONCAT(accountcode, ' - ', accountname) as texto FROM chartmaster WHERE length(accountcode) = 7 ".$sqlWhere." ORDER BY value ASC";
    $ErrMsg = "No se obtuvo los Estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
