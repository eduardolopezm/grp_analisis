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
$funcion = 128;
if (!function_exists('_')) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

include $PathPrefix . "includes/SecurityUrl.php";
$enc = new Encryption;

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

$Niveles[1] = "Genero";
$Niveles[2] = "Grupo";
$Niveles[3] = "Rubro";
$Niveles[4] = "Cuenta";

// Validar Identificador
$validarIdentificador = 1;

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

if ($option == 'agregarCuenta') {
    $tipoAlta = $_POST['tipoAlta'];
    $cuenta = $_POST['cuenta'];
    $nombre = $_POST['nombre'];
    $naturaleza = $_POST['naturaleza'];
    $tipo = $_POST['tipo'];
    $ur = $_POST['txtUR'];
    $ue = $_POST['txtUE'];
    //$pp = $_POST['txtPP'];

    $validaciones = 0;
    $claveIden = "";

    /*
        if ($validarIdentificador == 1 && count(explode('.', $cuenta)) > 5) {

        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        //!!                                               !!
        //!!    Se comenta validacion ya que no se usara   !!
        //!!               el identificador.               !!
        //!!                                               !!
        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

        if (fnNivelesCuentaContableGeneral($db, $cuenta) >= 5 &&
            (trim($_POST['txtUR']) != '-1' && trim($_POST['txtUE']) != '-1' && trim($_POST['txtPP']) != '-1')) {
            // Validar UR
            $SQL = "SELECT tagref FROM tags WHERE tagref = '" . $ur . "'";
            $ErrMsg = "No se obtuvo información de la UR";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            if (DB_num_rows($TransResult) == 0) {
                $validaciones = 1;
                $Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No existe la UR ' . $ur . '</p>';
                $result = false;
            }
            // Validar UE
            $SQL = "SELECT ue FROM tb_cat_unidades_ejecutoras WHERE ue = '" . $ue . "'";
            $ErrMsg = "No se obtuvo información de la UE";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            if (DB_num_rows($TransResult) == 0) {
                $validaciones = 1;
                $Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No existe la UE ' . $ue . '</p>';
                $result = false;
            }
            // Validar Programa Presupuestario
            $SQL = "SELECT cppt FROM tb_cat_programa_presupuestario WHERE cppt = '" . $pp . "'";
            $ErrMsg = "No se obtuvo información del Programa Presupuestario";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            if (DB_num_rows($TransResult) == 0) {
                $validaciones = 1;
                $Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No existe el Programa Presupuestario ' . $pp . '</p>';
                $result = false;
            }

            $claveIden = $ur . "-" . $ue . "-" . $pp;
        }
    */

    if ($validaciones == 0) {
        $info = array();
        $SQL = "SELECT `accountname` FROM `chartmaster` WHERE `accountcode` = '" . $cuenta . "'";
        $ErrMsg = "No se obtuvo información de la cuenta";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) == 0) {
            $arrayCuenta = explode('.', $cuenta);
            $num = 1;
            $groupcode = "";
            for ($i = 0; $i < count($arrayCuenta); $i++) {
                if (($i + 1) < count($arrayCuenta)) {
                    if (empty($groupcode)) {
                        $groupcode .= "" . $arrayCuenta[$i];
                    } else {
                        $groupcode .= "." . $arrayCuenta[$i];
                    }
                }

                $num++;
            }

            if (trim($groupcode) == '') {
                $groupcode = $cuenta;
            }

            $grupo = "";
            $SQL = "SELECT accountname FROM chartmaster WHERE accountcode = '" . $tipo . "'";
            $ErrMsg = "No se obtuvo el grupo";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            while ($myrow = DB_fetch_array($TransResult)) {
                $grupo = $myrow['accountname'];
            }

            if (trim($grupo) == '') {
                $grupo = $nombre;
            }

            $SQL = "INSERT INTO chartmaster (
            accountcode,
            accountname,
            group_,
            naturaleza,
            tipo,
            groupcode,
            ln_clave,
            nu_nivel, 
            tagref)
            VALUES (
            '" . $cuenta . "',
            '" . $nombre . "',
            '" . $grupo . "',
            " . $naturaleza . ",
            '" . $tipo . "',
            '" . $groupcode . "',
            '" . $ue . "',
            '" . fnNivelesCuentaContableGeneral($db, $cuenta) . "',
            '".$ur."')";

            $ErrMsg = "No se agrego al cuenta";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $SQL = "DELETE FROM chartmasterxlegal WHERE accountcode = '" . $cuenta . "'";
            $ErrMsg = "No se Elimino Cuenta Proceso de Resposteo";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $SQL = "INSERT INTO chartmasterxlegal (accountcode, legalid)
            SELECT '" . $cuenta . "', legalid FROM legalbusinessunit";
            $ErrMsg = "No se Agrego Cuenta Proceso de Resposteo";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $Mensaje = '<h6><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Se agregó '.( !$tipoAlta ? "la" : ( $tipoAlta=="Cuenta" ? "la" : "el" ) ).' '.( $tipoAlta ? ( $tipoAlta=="Genero" ? "Género" : $tipoAlta ) : 'cuenta ' )." <strong>$cuenta  - $nombre</strong> ".  ' con éxito.</h6>';
            $result = true;
        } else {
            $myrow = DB_fetch_array($TransResult);

            $SQL = "SELECT `accountname` FROM `chartmaster` WHERE `accountcode` = '$cuenta' AND `ind_activo` = 0";
            $ErrMsg = "No se obtuvo información de la cuenta";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            if (DB_num_rows($TransResult)) {
                $SQL = "UPDATE `chartmaster` 
                SET `accountname` = '$nombre', `naturaleza` = '$naturaleza', `ind_activo` = 1
                        WHERE `accountcode` = '$cuenta'";
                $ErrMsg = "No se agregó la cuenta";
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $Mensaje = '<h6><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Se agregó '.( !$tipoAlta ? "la" : ( $tipoAlta=="Cuenta" ? "la" : "el" ) ).' '.( $tipoAlta ? ( $tipoAlta=="Genero" ? "Género" : $tipoAlta ) : 'cuenta ' )." <strong>$cuenta  - $nombre</strong> ".  ' con éxito.</h6>';
                $result = true;
            }else{
                $Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.( !$tipoAlta ? "La" : ( $tipoAlta=="Cuenta" ? "La" : "El" ) ).' '.( $tipoAlta ? ( $tipoAlta=="Genero" ? "Género" : $tipoAlta ) : 'cuenta ' )." <strong>$cuenta  - $myrow[accountname]</strong> ".' ya existe en el sistema.</p>';
                $result = false;
            }
        }
    }
}

if ($option == 'obtenerCuenta'){
    $cuenta = $_POST['cuenta'];

    $SQL = "SELECT `accountname` AS nombre, `naturaleza`, `nu_nivel` AS nivel, `ind_activo` AS estatusCuenta, 
            tagref, ln_clave 
            FROM `chartmaster` WHERE `accountcode` = '$cuenta'";

    $ErrMsg = "No se obtuvo información de la cuenta";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $Mensaje = "$ErrMsg <strong>$cuenta</strong>.";

    if (DB_num_rows($TransResult)) {
        $contenido = DB_fetch_array($TransResult);
        $result = true;
    }
}

if ($option == 'actualizarCuenta') {
    $tipoAlta = $_POST['tipoAlta'];
    $cuenta = $_POST['cuenta'];
    $nombre = $_POST['nombre'];
    $naturaleza = $_POST['naturaleza'];
    $uresponsable= $_POST['unidadnegocio'];
    $uejecutora= $_POST['unidadejecutora'];
    /*$ur = $_POST['txtUR'];
    $ue = $_POST['txtUE'];
    $pp = $_POST['txtPP'];*/
    $nivel = $_POST['nivel'];

    $validaciones = 0;
    $claveIden = "";

    //// Se agrega línea para que sólo valide cuando se haya elegido alguno de los tres componentes del diferenciador.
    $validarIdentificador = ( $nivel>4&&($ur!="-1"&&$ue!="-1"&&$pp!="-1") ? 1 : 0 );

    /*if ($validarIdentificador == 1) {
        // Validar UR
        $SQL = "SELECT tagref FROM tags WHERE tagref = '" . $ur . "'";
        $ErrMsg = "No se obtuvo información de la UR";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) == 0) {
            $validaciones = 1;
            $Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No existe la UR ' . $ur . '</p>';
            $result = false;
        }
        // Validar UE
        $SQL = "SELECT ue FROM tb_cat_unidades_ejecutoras WHERE ue = '" . $ue . "'";
        $ErrMsg = "No se obtuvo información de la UE";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) == 0) {
            $validaciones = 1;
            $Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No existe la UE ' . $ue . '</p>';
            $result = false;
        }
        // Validar Programa Presupuestario
        $SQL = "SELECT cppt FROM tb_cat_programa_presupuestario WHERE cppt = '" . $pp . "'";
        $ErrMsg = "No se obtuvo información del Programa Presupuestario";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) == 0) {
            $validaciones = 1;
            $Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No existe el Programa Presupuestario ' . $pp . '</p>';
            $result = false;
        }

        $claveIden = $ur . "-" . $ue . "-" . $pp;
    }*/

    //// Se agrega línea para que sólo se haga update de naturaleza cuando esta haya sido proporcionada por el usuario.
    $queryNaturaleza = ( $naturaleza ? " `naturaleza` = '" . $naturaleza . "', " : "" );
    //// Se agrega línea para que sólo se haga update de identificador cuando este haya sido proporcionado por el usuario o cuando haya sido enviado sin valores.
    $queryIdentificador = ( $claveIden||($ur=="-1"&&$ue=="-1"&&$pp=="-1") ? ", `ln_clave` = '" . $claveIden . "'" : "" );

    if ($validaciones == 0) {
        $SQL = "UPDATE `chartmaster` 
                SET `accountname` = '" . $nombre . "', ".$queryNaturaleza."
                    ln_clave='".$uejecutora."',
                    tagref='".$uresponsable."'
                WHERE `accountcode` = '" . $cuenta . "'";
        
        $ErrMsg = "No se agregó la cuenta";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $Mensaje = '<h6><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Cuenta ' . $cuenta . ' actualizada</h6>';
        $result = true;
    }
}

if ($option == 'cambiarEstatusCuenta') {
    $tipoAlta = ( array_key_exists($_POST['nivel'], $Niveles) ? $Niveles[$_POST['nivel']] : "" );
    $cuenta = $_POST['cuenta'];
    $tipoAccion = ( $_POST['tipoAccion'] ? 1 : 0 );
    $multiNivel = ( $_POST['multiNivel'] ? " y todas sus sub cuentas" : "" );
    $ReactivacionMultiple = $_POST['reactivacionMultiple'];
    $multiNivelQuery = ( $multiNivel&&!$ReactivacionMultiple||$multiNivel&&$ReactivacionMultiple=="1" ? "LIKE '$cuenta.%' OR `accountcode` = '$cuenta'" : "= '$cuenta'" );
    $nombre = "";
    $mensajeReload = "";
    // AND `ind_activo` = $tipoAccion
    $SQL = "SELECT `accountname` FROM `chartmaster` WHERE `accountcode` $multiNivelQuery  GROUP BY `tipo` ORDER BY `accountcode` ASC";
    $ErrMsg = "No se encontró la cuenta";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $nombre = DB_fetch_array($TransResult)['accountname'];
    if($nombre){
        $tieneSubnivelesActivos = comprobarSubniveles($db,$cuenta,1);
        $tieneSubnivelesInactivos = comprobarSubniveles($db,$cuenta,0);
        $puedeInactivarse = comprobarIntegridadReferencial($db,$cuenta,true);
        $Mensaje = '<h6><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.( !$tipoAlta ? "La" : ( $tipoAlta=="Cuenta" ? "La" : "El" ) ).' '.( $tipoAlta ? ( $tipoAlta=="Genero" ? "Género" : $tipoAlta ) : 'cuenta ' )." <strong>$cuenta - $nombre</strong> no puede inactivarse porque está siendo utilizad".( !$tipoAlta ? "a" : ( $tipoAlta=="Cuenta" ? "a" : "o" ) ).".</h6>";
        $ejecutarQuery = true;
        $ejecutarQuery = ( !$tipoAccion&&$multiNivel&&!$puedeInactivarse ? false : $ejecutarQuery );
        $ejecutarQuery = ( !$tipoAccion&&!$multiNivel&&$tieneSubnivelesActivos ? false : $ejecutarQuery );
        $ejecutarQuery = ( $tipoAccion&&!$multiNivel&&$tieneSubnivelesInactivos ? false : $ejecutarQuery );

        if($tipoAccion&&!$multiNivel&&$tieneSubnivelesInactivos){
            $Mensaje = '<h6><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.( !$tipoAlta ? "La" : ( $tipoAlta=="Cuenta" ? "La" : "El" ) ).' '.( $tipoAlta ? ( $tipoAlta=="Genero" ? "Género" : $tipoAlta ) : 'cuenta ' )." <strong>$cuenta - $nombre</strong> poseé ".$tieneSubnivelesInactivos." sub cuenta".( $tieneSubnivelesInactivos>1 ? "s" : "" )." inactiva".( $tieneSubnivelesInactivos>1 ? "s" : "" ).".<br>¿Desea reactivar ".( !$tipoAlta ? "la" : ( $tipoAlta=="Cuenta" ? "la" : "el" ) ).' '.( $tipoAlta ? ( $tipoAlta=="Genero" ? "Género" : $tipoAlta ) : 'cuenta ' )." y todas sus sub cuentas?</h6>";
        }

        if(!$tipoAccion&&!$multiNivel&&$tieneSubnivelesActivos){
            $Mensaje = '<h6><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.( !$tipoAlta ? "La" : ( $tipoAlta=="Cuenta" ? "La" : "El" ) ).' '.( $tipoAlta ? ( $tipoAlta=="Genero" ? "Género" : $tipoAlta ) : 'cuenta ' )." <strong>$cuenta - $nombre</strong> no puede inactivarse porque poseé ".$tieneSubnivelesActivos." sub cuenta".( $tieneSubnivelesActivos>1 ? "s" : "" )." activa".( $tieneSubnivelesActivos>1 ? "s" : "" ).".<br>¿Desea inactivar ".( !$tipoAlta ? "la" : ( $tipoAlta=="Cuenta" ? "la" : "el" ) ).' '.( $tipoAlta ? ( $tipoAlta=="Genero" ? "Género" : $tipoAlta ) : 'cuenta ' )." y todas sus sub cuentas?</h6>";
        }

        if($ejecutarQuery){
            $SQL = "UPDATE `chartmaster` SET `ind_activo` = $tipoAccion WHERE (`accountcode` $multiNivelQuery)";
            $ErrMsg = "No se encontró la cuenta";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            if($multiNivel&&$ReactivacionMultiple=="2"){
                $multiNivel = "";
            }
            if($multiNivel){
                $mensajeReload = "<br><br><center>(Se recomienda volver presionar el botón de filtrar para ver reflejados los cambios de estatus.)</center>";
            }

            $Mensaje = '<h6><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Se '.( $tipoAccion ? "reactivó" : "inactivó" ).' '.( !$tipoAlta ? "la" : ( $tipoAlta=="Cuenta" ? "la" : "el" ) ).' '.( $tipoAlta ? ( $tipoAlta=="Genero" ? "Género" : $tipoAlta ) : 'cuenta ' )." <strong>$cuenta - $nombre</strong>$multiNivel con éxito.$mensajeReload</h6>";
            $result = true;
        }
    }
}

if ($option == 'obtenerInfoCuentasCont') {
    //$legalid = $_POST['legalid'];

    $sqlWhere = "";

    // Columnas para el GRID
    $columnasNombres .= "[";
    /*if ($validarIdentificador == 1) {
        $columnasNombres .= "{ name: 'ln_clave', type: 'string' },";
    }*/
    $columnasNombres .= "{ name: 'accountcode', type: 'string' },";
    $columnasNombres .= "{ name: 'accountname', type: 'string' },";
    $columnasNombres .= "{ name: 'naturaleza', type: 'string' },";
    /*$columnasNombres .= "{ name: 'genero', type: 'string' },";
    //$columnasNombres .= "{ name: 'group_', type: 'string' },";
    $columnasNombres .= "{ name: 'rubro', type: 'string' },";
    $columnasNombres .= "{ name: 'grupo', type: 'string' },";
    $columnasNombres .= "{ name: 'cuenta', type: 'string' },";*/

    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    /*if ($validarIdentificador == 1) {
        $columnasNombresGrid .= " { text: 'Diferenciador', datafield: 'ln_clave', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    }*/
    $columnasNombresGrid .= " { text: 'Clave', datafield: 'accountcode', width: '25%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Nombre', datafield: 'accountname', width: '36%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Naturaleza', datafield: 'naturaleza', width: '12%', cellsalign: 'center', align: 'center', hidden: false },";
    /*$columnasNombresGrid .= " { text: 'Genero', datafield: 'genero', width: '15%', cellsalign: 'left', align: 'center', hidden: false },";
    //$columnasNombresGrid .= " { text: 'Grupo', datafield: 'group_', width: '10%', cellsalign: 'left', align: 'center', hidden: false },";
    /*$columnasNombresGrid .= " { text: 'Grupo', datafield: 'grupo', width: '15%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Rubro', datafield: 'rubro', width: '15%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Cuenta', datafield: 'cuenta', width: '15%', cellsalign: 'left', align: 'center', hidden: false },";*/

    $numColumnasExcel = 5;
    $columnasExcel = array(0, 1, 2, 3, 4);
    $columnasVisuales = array(0, 1, 2, 3, 4, 5);

    if ($validarIdentificador == 1) {
        // Col Diferenciador
        $numColumnasExcel++;
        array_push($columnasExcel, $numColumnasExcel);
        array_push($columnasVisuales, $numColumnasExcel);
    }

    $info = $_POST;
    //// CASE WHEN chartmaster.naturaleza=1 THEN 'Deudora' ELSE 'Acreedora' END AS naturalezaname,
    /*
        Se remueven campos que ya no están en uso y sus respectivos JOINs
        CASE WHEN pandl=0 THEN 'BALANCE' ELSE 'RESULTADOS' END AS acttype,
        chartmasterGenero.accountname as genero,
        chartmasterGrupo.accountname as grupo,
        chartmasterRubro.accountname as rubro,
        chartmasterCuenta.accountname as cuenta,

        LEFT JOIN accountgroups ON chartmaster.group_=accountgroups.groupname
        LEFT JOIN chartmaster chartmasterGenero ON LENGTH(chartmasterGenero.accountcode) = 1 and chartmasterGenero.accountcode = substring(chartmaster.accountcode, 1, 1)
        LEFT JOIN chartmaster chartmasterGrupo ON LENGTH(chartmasterGrupo.accountcode) = 3 and chartmasterGrupo.accountcode = substring(chartmaster.accountcode, 1, 3)
        LEFT JOIN chartmaster chartmasterRubro ON LENGTH(chartmasterRubro.accountcode) = 5 and chartmasterRubro.accountcode = substring(chartmaster.accountcode, 1, 5)
        LEFT JOIN chartmaster chartmasterCuenta ON LENGTH(chartmasterCuenta.accountcode) = 7 and chartmasterCuenta.accountcode = substring(chartmaster.accountcode, 1, 7)
     */
    $SQL = "SELECT DISTINCT 
            `chartmaster`.`accountcode`,
            `chartmaster`.`accountname`,
            `chartmaster`.`group_`,
            `chartmaster`.`naturaleza`,
            IF(`chartmaster`.`naturaleza`=1,'Deudora',IF(`chartmaster`.`naturaleza`=-1,'Acreedora',IF(`chartmaster`.`naturaleza`=2,'Deudora/Acreedora',''))) AS naturalezaname,
            `chartTipos`.`nombreMayor`,
            `chartmaster`.`tipo`,
            LENGTH(`chartmaster`.`accountcode`) as numCuenta,
            `chartmaster`.`nu_nivel` as numSubcuentas,
            `chartmaster`.`ln_clave`,
            IF(`chartmaster`.`ind_activo`=0,'Inactivo',IF(`chartmaster`.`ind_activo`=1,'Activo','')) AS 'Estatus',
            `chartmaster`.`ind_activo` AS 'EstatusCuenta', `chartmaster`.`tagref`
            FROM `chartmaster` 
            LEFT JOIN `chartTipos` ON `chartmaster`.`tipo` = `chartTipos`.`tipo`
            JOIN `tb_sec_users_ue` ON `tb_sec_users_ue`.`userid` LIKE '$_SESSION[UserID]'

            WHERE '$info[buscarConFiltros]' = '1'
            AND ( `chartmaster`.`ln_clave` = `tb_sec_users_ue`.`ue` OR LENGTH(`chartmaster`.`ln_clave`) < 2 OR `chartmaster`.`ln_clave` LIKE '%.%' )";
    
        // datos adicionales de filtrado
        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        //!!                                               !!
        //!!      Se comenta ya no se va a utilizar        !!
        //!!             aqui el identificador.            !!
        //!!                                               !!
        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

        if(!empty($info['selectUnidadNegocioFiltro']) && $info['selectUnidadNegocioFiltro']!="-1"){
            $SQL .= " AND ( `chartmaster`.`tagref`= '$info[selectUnidadNegocioFiltro]' OR `chartmaster`.`tagref` IS NULL )" ;
        }

        if(is_array($info['selectUnidadEjecutoraFiltro']) && count($info['selectUnidadEjecutoraFiltro'])){
            $SQL .= " AND ( `chartmaster`.`ln_clave` IN( '".implode("', '",$info['selectUnidadEjecutoraFiltro'])."' ) OR LENGTH(`chartmaster`.`ln_clave`) < 2 OR `chartmaster`.`ln_clave` LIKE '%.%' ) ";
        }
        
        /*if(is_array($info['busquedaPP'])&&count($info['busquedaPP'])){
            $SQL .= " AND ( `chartmaster`.`ln_clave` LIKE '%-".implode("' OR `chartmaster`.`ln_clave` LIKE '%-",$info['busquedaPP'])."' ) ";
        }
        */

    if(is_array($info['busquedaGenero']) && count($info['busquedaGenero'])){
        $ORSQL = "";
        $SQL .= " AND ( ";
        foreach($info['busquedaGenero'] AS $ID => $Valor){
            $SQL .= "$ORSQL`chartmaster`.`accountcode` = '$Valor'
                      OR SUBSTRING(`chartmaster`.`accountcode`,1,LENGTH('$Valor.')) = '$Valor.'";
            $ORSQL = " OR ";
        }
        $SQL .= " ) ";
    }

    if(isset($info['nivelDesagregacion']) and $info['nivelDesagregacion']!=""){
        $SQL .= " AND `chartmaster`.`nu_nivel` in (".$info['nivelDesagregacion'].") ";
    }
    if(isset($info['naturaleza']) and $info['naturaleza']!=""){
        $SQL .= " AND `chartmaster`.`naturaleza` in (".$info['naturaleza'].") ";
    }

    if(!empty($info['cuentaDesde'])&&empty($info['cuentaHasta'])){
        $SQL .= " AND `chartmaster`.`accountcode` = '$info[cuentaDesde]'";
    }elseif(empty($info['cuentaDesde'])&&!empty($info['cuentaHasta'])){
        $SQL .= " AND `chartmaster`.`accountcode` = '$info[cuentaHasta]'";
    }else{
        if(!empty($info['cuentaDesde'])){
            $SQL .= " AND `chartmaster`.`accountcode` BETWEEN '$info[cuentaDesde]' AND '$info[cuentaHasta]'";
        }
    }

    if(!empty($info['EstatusFiltro'])&&$info['EstatusFiltro']!="-1"){
        $info['EstatusFiltro'] = ( $info['EstatusFiltro']!="2" ? $info['EstatusFiltro'] : 0 );
        $SQL .= " AND `chartmaster`.`ind_activo` = '$info[EstatusFiltro]' ";
    }

    $SQL .= " GROUP BY `chartmaster`.`accountcode`
            ORDER BY `chartmaster`.`accountcode`
            ";
    //echo "<pre>".$SQL;
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $num = 1;

    $info = array();
    while ($myrow = DB_fetch_array($TransResult)) {
        $datos['accountcode'] = $myrow ['accountcode'];
        $datos['accountname'] = $myrow ['accountname'];
        $datos['naturaleza'] = $myrow ['naturalezaname'];
        $datos['genero'] = $myrow ['genero'];
        $datos['grupo'] = $myrow ['grupo'];
        $datos['rubro'] = $myrow ['rubro'];
        $datos['cuenta'] = $myrow ['cuenta'];
        $datos['tipo'] = $myrow ['nombreMayor'];
        $datos['ln_clave'] = $myrow ['ln_clave'];
        $datos['tagref']= $myrow ['tagref'];

        $claveSep = explode('-', $myrow['ln_clave']);
        $_POST['txtUR'] = $myrow ['tagref'];
        $_POST['txtUE'] = $myrow ['ln_clave'];
        $_POST['txtPP'] = "";

        $datos['estatus'] = $myrow['Estatus'];        

        $datos['Modificar'] = '<a onclick="fnModificarCuenta(\'' . $myrow ['accountcode'] . '\',\'' . $myrow ['accountname'] . '\',\'' . $myrow ['naturaleza'] . '\', \'' . $myrow ['tagref'] . '\', \'' . 
            $myrow ['ln_clave'] . '\', \'' . $_POST ['txtPP'] . '\',\'' . $myrow ['numSubcuentas'] . '\',' . 
            $myrow ['EstatusCuenta'] . ')"><span class="glyphicon glyphicon-edit"></span></a>';

        $nivel = 5;
        $numSub = 9;
        /*for ($i = 1; $i <= $myrow ['numSubcuentas']; $i++) {
            if ($num == 1) {
                $columnasNombres .= "{ name: 'subcuenta" . $nivel . "', type: 'string' },";
                $columnasNombresGrid .= " { text: 'Nivel" . $nivel . "', datafield: 'subcuenta" . $nivel . "', width: '10%', cellsalign: 'left', align: 'center', hidden: false },";

                $numColumnasExcel++;
                array_push($columnasExcel, $numColumnasExcel);
                array_push($columnasVisuales, $numColumnasExcel);
            }
            if ($myrow['numCuenta'] > 7) {
                $nombreNivel = "";
                if ($numSub <= $myrow['numCuenta']) {
                    $SQL = "SELECT accountname FROM chartmaster WHERE accountcode = '" . substr($myrow ['accountcode'], 0, $numSub) . "'";
                    $TransResult2 = DB_query($SQL, $db, $ErrMsg);
                    if ($myrow2 = DB_fetch_array($TransResult2)) {
                        $nombreNivel = $myrow2 ['accountname'];
                    }
                }
                $datos['subcuenta' . $nivel] = $nombreNivel;
            } else {
                $datos['subcuenta' . $nivel] = "";
            }
            $nivel++;
            $numSub = $numSub + 2;
        }*/

        $info[] = $datos;

        $num++;
    }

    // Columnas para el GRID
    $columnasNombres .= "{ name: 'tipo', type: 'string' },";
    $columnasNombres .= "{ name: 'estatus', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' }";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $columnasNombresGrid .= " { text: 'Género', datafield: 'tipo', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'estatus', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    // Col Tipo
    $numColumnasExcel++;
    array_push($columnasExcel, $numColumnasExcel);
    array_push($columnasVisuales, $numColumnasExcel);

    // Col Modificar
    $numColumnasExcel++;
    //array_push($columnasExcel, $numColumnasExcel);
    array_push($columnasVisuales, $numColumnasExcel);

    $nombreExcel = traeNombreFuncionGeneral($funcion, $db) . '_' . date('dmY');

    $contenido = array('datos' => $info,
        'columnasNombres' => $columnasNombres,
        'columnasNombresGrid' => $columnasNombresGrid,
        'nombreExcel' => $nombreExcel,
        'columnasExcel' => $columnasExcel,
        'columnasVisuales' => $columnasVisuales
    );
    $result = true;
}

if ($option == 'obtenerNaturaleza') {
    $cuenta = $_POST['cuenta'];
    $naturaleza = '';
    $SQL = "SELECT naturaleza FROM chartmaster WHERE accountcode = '" . $cuenta . "'";
    $ErrMsg = "No se obtuvo los Estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $naturaleza = $myrow['naturaleza'];
    }

    $contenido = $naturaleza;
    $result = true;
}

if ($option == 'mostrarGenero') {
    $info = array();
    //$SQL = "SELECT nu_clave as value, CONCAT(nu_clave, ' - ', txt_descripcion) as texto FROM tb_gl_genero WHERE sn_activo = '1'  ORDER BY nu_clave ASC";
    //// Se reemplaza WHERE length(accountcode) = 1 por `nu_nivel` = 1
    $SQL = "SELECT accountcode as value, CONCAT(accountcode, ' - ', accountname) as texto FROM chartmaster WHERE `nu_nivel` = 1 AND `ind_activo` = 1 ORDER BY LENGTH(value) ASC, value ASC";
    $ErrMsg = "No se obtuvo los Estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array('value' => $myrow ['value'], 'texto' => $myrow ['texto']);
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarGrupo') {
    $genero = $_POST['genero'];
    $sqlWhere = "";

    if (!empty($genero)) {
        $sqlWhere = " AND accountcode like '" . $genero . "%' ";
    }

    $info = array();
    // $SQL = "SELECT nu_clave as value, CONCAT(nu_clave, ' - ', txt_descripcion) as texto
    // FROM tb_gl_grupo WHERE sn_activo = '1' ".$sqlWhere." ORDER BY nu_clave ASC";
    //// Se reemplaza WHERE length(accountcode) = 3 por `nu_nivel` = 2
    $SQL = "SELECT accountcode as value, CONCAT(accountcode, ' - ', accountname) as texto FROM chartmaster WHERE `nu_nivel` = 2 " . $sqlWhere . " AND `ind_activo` = 1 ORDER BY LENGTH(value) ASC, value ASC";
    $ErrMsg = "No se obtuvo los Estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array('value' => $myrow ['value'], 'texto' => $myrow ['texto']);
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarRubro') {
    $grupo = $_POST['grupo'];
    $sqlWhere = "";

    if (!empty($grupo)) {
        $sqlWhere = " AND accountcode like '" . $grupo . "%'";
    }

    $info = array();
    // $SQL = "SELECT nu_clave as value, CONCAT(nu_clave, ' - ', txt_descripcion) as texto
    // FROM tb_gl_rubro WHERE sn_activo = '1' ".$sqlWhere." ORDER BY nu_clave ASC";
    //// Se reemplaza WHERE length(accountcode) = 5 por `nu_nivel` = 3
    $SQL = "SELECT accountcode as value, CONCAT(accountcode, ' - ', accountname) as texto FROM chartmaster WHERE `nu_nivel` = 3 " . $sqlWhere . " AND `ind_activo` = 1 ORDER BY LENGTH(value) ASC, value ASC";
    $ErrMsg = "No se obtuvo los Estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array('value' => $myrow ['value'], 'texto' => $myrow ['texto']);
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarCuenta') {
    $rubro = $_POST['rubro'];
    $sqlWhere = "";

    if (!empty($rubro)) {
        $sqlWhere = " AND accountcode like '" . $rubro . ".%'";
    }

    $info = array();
    // $SQL = "SELECT nu_clave as value, CONCAT(nu_clave, ' - ', txt_descripcion) as texto
    // FROM tb_gl_cuenta WHERE sn_activo = '1' ".$sqlWhere." ORDER BY nu_clave ASC";
    //// Se reemplaza WHERE length(accountcode) >= 7 por `nu_nivel` >= 4
    $SQL = "SELECT accountcode as value, CONCAT(accountcode, ' - ', accountname) as texto, accountname FROM chartmaster WHERE `nu_nivel` >= 4 " . $sqlWhere . " AND `ind_activo` = 1 ORDER BY LENGTH(value) ASC, value ASC";
    $ErrMsg = "No se obtuvo los Estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array('value' => $myrow ['value'], 'texto' => $myrow ['texto'],'accountname'=>$myrow['accountname']);
    }
    $contenido = array('datos' => $info,'rubro'=>$rubro);
    $result = true;
}

if ($option == 'cuentaAInactivar') {
    $tipoAlta = $_POST['tipoAlta'];
    $cuenta = $_POST['cuenta'];
    $nombre = "";
    $SQL = "SELECT `accountname` FROM `chartmaster` WHERE `accountcode`  = '$cuenta' AND `ind_activo` = 1";
    $ErrMsg = "No se encontró la cuenta";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $nombre = DB_fetch_array($TransResult)['accountname'];
    if($nombre){
        $tieneSubnivelesActivos = comprobarSubniveles($db,$cuenta,1);
        $puedeInactivarse = comprobarIntegridadReferencial($db,$cuenta,true);

        $contenido = true;
        $Mensaje = '<h6><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.( !$tipoAlta ? "La" : ( $tipoAlta=="Cuenta" ? "La" : "El" ) ).' '.( $tipoAlta ? ( $tipoAlta=="Genero" ? "Género" : $tipoAlta ) : 'cuenta ' )." <strong>$cuenta - $nombre</strong> no puede inactivarse porque poseé ".$tieneSubnivelesActivos." sub cuentas.<br>¿Desea inactivar ".( !$tipoAlta ? "la" : ( $tipoAlta=="Cuenta" ? "la" : "el" ) ).' '.( $tipoAlta ? ( $tipoAlta=="Genero" ? "Género" : $tipoAlta ) : 'cuenta ' )." y todas sus sub cuentas?</h6>";

        if($puedeInactivarse){
            $contenido = false;
            $Mensaje = '<h6><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.( !$tipoAlta ? "La" : ( $tipoAlta=="Cuenta" ? "La" : "El" ) ).' '.( $tipoAlta ? ( $tipoAlta=="Genero" ? "Género" : $tipoAlta ) : 'cuenta ' )." <strong>$cuenta - $nombre</strong> no puede inactivarse porque ese registro o alguna de sus sub cuentas están siendo utilizadas.</h6>";
        }

        if(!$tieneSubnivelesActivos){
            $Mensaje = '<h6><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> ¿Está seguro de que desea inactivar '.( !$tipoAlta ? "la" : ( $tipoAlta=="Cuenta" ? "la" : "el" ) ).' '.( $tipoAlta ? ( $tipoAlta=="Genero" ? "Género" : $tipoAlta ) : 'cuenta ' )." <strong>$cuenta - $nombre</strong>?.</h6>";
            $result = true;
        }
    }
}

if ($option == 'datosSelectGeneros') {
    $sql = "SELECT `accountcode` AS valor, CONCAT(`accountcode`,' - ',`accountname`) AS label
            FROM `chartmaster`
            WHERE `nu_nivel` = '1'
            ORDER BY LENGTH(`accountcode`) ASC, `accountcode` ASC";
    
    $contenido = obtenDatosSelect($db,$sql);
    $result = true;
}

if ($option == 'datosListaCuentas') {
    $sql = "SELECT `accountcode` AS `valor`, `accountname` AS `label`

            FROM `chartmaster`
            JOIN `tb_sec_users_ue` ON `tb_sec_users_ue`.`userid` LIKE '$_SESSION[UserID]'

            WHERE ( `chartmaster`.`ln_clave` = `tb_sec_users_ue`.`ue` OR LENGTH(`chartmaster`.`ln_clave`) < 2 OR `chartmaster`.`ln_clave` LIKE '%.%' )

            ORDER BY `accountcode` ASC";
    
    $contenido = obtenDatosLista($db,$sql);
    $result = true;
}

if($option == 'encryptarURL'){
    
    $url = $_POST['url'];
    $url = $enc->encode($url);
    $liga_folio= "URL=". $url;
    $Mensaje='GLTrialBalanceXLS.php?'.$liga_folio;
    $result = true;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);

/**
 * Función que obtiene la información para los distintos selects
 * @param   [DBInstance]    $db     Instancia de la base de datos
 * @return  [Array]         $data   Arreglo con la respuesta verdadera o falsa, según lo obtenido
 */
function obtenDatosSelect($db,$sql){
    // declaración de variables de la función
    $data = ['success'=>false, 'msg'=>'Ocurrió un incidente al obtener la información. Favor de contactar al administrador.'];
    $info = $_POST;

    $result = DB_query($sql, $db);

    // comprobación de existencia de la información
    if(DB_num_rows($result) == 0){
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

/**
 * Función que obtiene la información para las distintas listas de búsqueda
 * @param   [DBInstance]    $db     Instancia de la base de datos
 * @return  [Array]         $data   Arreglo con la respuesta verdadera o falsa, según lo obtenido
 */
function obtenDatosLista($db,$sql){
    // declaración de variables de la función
    $data = ['success'=>false, 'msg'=>'Ocurrió un incidente al obtener la información. Favor de contactar al administrador.'];
    $info = $_POST;

    $datosCortos = array();
    $datosLargos = array();

    $result = DB_query($sql, $db);

    // comprobación de existencia de la información
    if(DB_num_rows($result) == 0){
        $data['msg'] = 'No se encontraron los datos solicitados.';
        return $data;
    }

    while ($rs = DB_fetch_array($result)) {
        $veces = substr_count($rs['valor'],'.');

        if($veces<5){
            $datosCortos[] = [
                'value' => $rs['valor'],
                'text' => utf8_encode($rs['label'])
            ];
        }else{
            $datosLargos[] = [
                'value' => $rs['valor'],
                'text' => utf8_encode($rs['label'])
            ];
        }
    }

    $data['cuentasMenores'] = $datosCortos;
    $data['cuentasMayores'] = $datosLargos;
    // retorno de la información
    return $data;
}

function comprobarSubniveles($db,$cuenta,$tipoComprobacion){
    $SQL = "SELECT COUNT(`tipo`) AS 'RegistrosEncontrados' FROM `chartmaster` WHERE `accountcode` LIKE '$cuenta.%' AND `ind_activo` = '$tipoComprobacion' GROUP BY `tipo`";
    $ErrMsg = "No se encontró la cuenta";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    return DB_fetch_array($TransResult)['RegistrosEncontrados'];
}

function comprobarIntegridadReferencial($db,$cuenta,$multiNivel){
    $tablasAComprobar['stockcategory']['campos'] = ["stockact", "accountegreso", "adjglact", "ln_abono_salida"];
    $tablasAComprobar['stockcategory']['activo'] = "ind_activo";
    $tablasAComprobar['stockcategory']['activoVal'] = "1";
    $tablasAComprobar['tb_matriz_pagado']['campos'] = ["stockact", "accountegreso"];
    $tablasAComprobar['tb_matriz_pagado']['activo'] = "ind_activo";
    $tablasAComprobar['tb_matriz_pagado']['activoVal'] = "1";
    $tablasAComprobar['tb_matriz_ingreso']['campos'] = ["stockact", "accountegreso"];
    $tablasAComprobar['tb_matriz_ingreso']['activo'] = "ind_activo";
    $tablasAComprobar['tb_matriz_ingreso']['activoVal'] = "1";
    $tablasAComprobar['tb_matriz_extraptal']['campos'] = ["stockact", "accountegreso"];
    $tablasAComprobar['tb_matriz_extraptal']['activo'] = "ind_activo";
    $tablasAComprobar['tb_matriz_extraptal']['activoVal'] = "1";
    $tablasAComprobar['accountxsupplier']['campos'] = ["accountcode"];
    $tablasAComprobar['gltrans']['campos'] = ["account"];
    $tablasAComprobar['bankaccounts']['campos'] = ["accountcode"];
    $tablasAComprobar['tagsxbankaccounts']['campos'] = ["accountcode"];
    $tablasAComprobar['banktrans']['campos'] = ["bankact"];
    $tablasAComprobar['companies']['campos'] = ["gllink_presupuestalingreso", "gllink_presupuestalingresoEjecutar", "gllink_presupuestalingresoModificado", "gllink_presupuestalingresoDevengado", "gllink_presupuestalingresoRecaudado", "gllink_presupuestalegreso", "gllink_presupuestalegresoEjercer", "gllink_presupuestalegresoModificado", "gllink_presupuestalegresocomprometido", "gllink_presupuestalegresodevengado", "gllink_presupuestalegresoejercido", "gllink_presupuestalegresopagado"];
    $puedeInactivarse = true;

    if(is_array($tablasAComprobar)&&count($tablasAComprobar)){
        foreach($tablasAComprobar AS $tabla => $parametros){
            $sql = "";
            $cierreParametrosBusquedaCampos = "";
            if(is_array($parametros['campos'])&&count($parametros['campos'])){
                $parametrosBusquedaCampos = array();
                foreach($parametros['campos'] AS $campo){
                    $parametrosBusquedaCampos[] = "`$campo`".( $multiNivel ? "LIKE '$cuenta.%' OR `$campo` = '$cuenta'" : "= '$cuenta'" );
                }
                $sql = "SELECT COUNT(`$campo`) AS 'RegistrosEncontrados'

                FROM `$tabla`";
                $parametrosBusqueda = array();
                if(array_key_exists("activo", $parametros)&&array_key_exists("activoVal", $parametros)){
                    $parametrosBusqueda[] = "`$parametros[activo]` = '$parametros[activoVal]'";
                    $cierreParametrosBusquedaCampos = ")";
                }
                if(count($parametrosBusquedaCampos)){
                    $parametrosBusqueda[] = " ( ".implode(") OR (",$parametrosBusquedaCampos)." ) $cierreParametrosBusquedaCampos";
                }
                $sql .= " WHERE ".implode(" AND (",$parametrosBusqueda);
                $result = DB_query($sql, $db);

                // comprobación de existencia de la información
                if(DB_fetch_array($result)['RegistrosEncontrados']){
                    $puedeInactivarse = false;
                    break;
                }
            }
        }
    }

    return $puedeInactivarse;
}
