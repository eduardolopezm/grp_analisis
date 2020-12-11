<?php
/**
 * Funciones Generales
 *
 * @category
 * @package ap_grp
 * @author Todos los desarrolladores
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 08/08/2017
 * Fecha Modificación: 08/08/2017
 * Modelos para las operaciones Generales
 */

//ini_set('display_errors', 1); 
//ini_set('log_errors', 1);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
//error_reporting(E_ALL);
//
session_start();

$PageSecurity = 1;
$PathPrefix = '../';
$abajo = true;

// include($PathPrefix.'abajo.php');
include($PathPrefix.'config.php');
include($PathPrefix.'includes/ConnectDB.inc');

if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}

include $PathPrefix."includes/SecurityUrl.php";

include($PathPrefix.'includes/SecurityFunctionsHeader.inc');
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
$objetoJSONDirecto = false;

$SQL = "SET NAMES 'utf8'";

$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

/*
 * Consulta que trae las retenciones del proveedor
 * @date:19.09.17
 * @author: Desarrollo
*/
if ($option == 'mostrarRetencionesProveedor') {
    $info = array();
    $SQL = "SELECT id as value, CONCAT(txt_descripcion, ' ', nu_porcentaje, '%') as texto 
    FROM tb_retenciones 
    WHERE nu_active = 1 AND nu_suppliers = 1 ORDER BY txt_descripcion ASC";
    $ErrMsg = "No se obtuvieron las retenciones de proveedores";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

/*
 * Consulta que trae los tipos de operación (Rectificaciones) en el panel de oficios de rectificacion
 * @date:13.09.17
 * @author: Desarrollo
*/
if ($option == 'mostrarPagosRectificacion') {
    $info = array();
    $SQL = "SELECT typeid as value, CONCAT(typeid, ' - ', typename) as texto 
    FROM systypescat 
    WHERE nu_rectificaciones_pagos = 1 ORDER BY typeid ASC";
    $ErrMsg = "No se obtuvieron los tipos de rectificación del panel";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

/*
 * Consulta que trae los tipos de operación (Pagos) en el panel del devengado
 * @date:23.08.17
 * @author: Desarrollo
*/
if ($option == 'mostrarPagosPanel') {
    $info = array();
    $SQL = "SELECT typeid as value, CONCAT(typeid, ' - ', typename) as texto FROM systypescat WHERE nu_panel_pagos = 1 ORDER BY typeid ASC";
    $ErrMsg = "No se obtuvieron los tipos de pagos del panel";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

/*
 * Consulta que trae los tipos de operación (Pagos) en el panel del tesoreria
 * @date:23.08.17
 * @author: Desarrollo
*/
if ($option == 'mostrarPagosTesoreria') {
    $info = array();
    $SQL = "SELECT typeid as value, CONCAT(typeid, ' - ', typename) as texto FROM systypescat WHERE nu_tesoreria_pagos = 1 ORDER BY typeid ASC";
    $ErrMsg = "No se obtuvieron los tipos de pagos de tesoreria";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'urlPresupuesto') {
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $tipo = $_POST['tipo'];

    $urlGeneral = "&transno=>" . $transno . "&type=>" . $type;

    if ($tipo == 'Excel') {
        // Si es excel
        $urlGeneral .= "&PrintExcel=>true";
    } else {
        // Es pdf
        $urlGeneral .= "&PrintPDF=>true";
    }

    $enc = new Encryption;
    $url = $enc->encode($urlGeneral);
    $liga= "URL=" . $url;
    $impresion = 'impresionAdecuacion.php?'.$liga.'';

    $contenido = $impresion;
    $result = true;
}

if ($option == 'mostrarRazonSocial') {
    $info = array();
    $SQL = "SELECT legalbusinessunit.legalid, CONCAT(legalbusinessunit.legalid, ' - ', legalbusinessunit.legalname) as legalname
            FROM sec_unegsxuser u, tags t
            JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid
            WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "'
            GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname
            ORDER BY legalbusinessunit.legalid ";
    $ErrMsg = "No se obtuvieron las razones sociales";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'legalid' => $myrow ['legalid'], 'legalname' => $myrow ['legalname'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarUnidadNegocio') {
    $info = array();
    $condicion= "";
    $ErrMsg = "No se obtuvieron las URG";

    if (isset($_POST["legalid"])) {
        $condicion= " AND t.legalid IN('".implode("', '", explode(",,,", $_POST['legalid']))."')";
    }

    // join areas ON t.areacode = areas.areacode
    $SQL = "SELECT  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription
            FROM sec_unegsxuser u, tags t
            WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' ".$condicion."
            ORDER BY t.tagref ";  
            // , areas.areacode

    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'tagref' => $myrow ['tagref'], 'tagdescription' => $myrow ['tagdescription'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarUnidadEjecutora14001') {
    $info = array();
    $condicion= "";
    $ErrMsg = "No se obtuvieron las UE";

        
    $SQL= "SELECT DISTINCT t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription,  tce.ue as ue, CONCAT(tce.ue, ' - ', tce.desc_ue) as uedescription, tce.desc_ue as descrip
    FROM sec_unegsxuser u
    INNER JOIN tags t on (u.tagref = t.tagref)
    INNER JOIN tb_cat_unidades_ejecutoras tce on  (tce.ur = t.tagref)
    INNER JOIN tb_sec_users_ue ON tb_sec_users_ue.userid = u.userid AND tb_sec_users_ue.tagref = t.tagref AND tb_sec_users_ue.ue = tce.ue
    WHERE tce.active = 1 and tce.ue = '14001'
    ORDER BY ue,descrip  ASC";


    
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'ue' => $myrow ['ue'], 'uedescription' => $myrow ['uedescription']);
    }

    $contenido = array('datos' => $info);
    $result = true;

    /*if($_SESSION['UserID'] == "lcarcases") {
        var_export($contenido);
    } */

}

if ($option == 'mostrarUnidadEjecutora') {
    $info = array();
    $condicion= "";
    $ErrMsg = "No se obtuvieron las UE";

    if (isset($_POST["tagref"])) {
        $condicion= " AND t.tagref = '$_POST[tagref]'";
    }

    $sqlJoinAlmacen = "";
    if (isset($_POST['soloAlmancen']) && $_POST['soloAlmancen'] == '1') {
        $sqlJoinAlmacen = " JOIN locations ON locations.tagref = t.tagref AND locations.ln_ue = tce.ue
        JOIN sec_loccxusser ON sec_loccxusser.loccode = locations.loccode AND sec_loccxusser.userid = '$_SESSION[UserID]'";
    }
        
    $SQL= "SELECT  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription,  tce.ue as ue, CONCAT(tce.ue, ' - ', tce.desc_ue) as uedescription, tce.desc_ue as descrip
    FROM sec_unegsxuser u
    INNER JOIN tags t on (u.tagref = t.tagref)
    INNER JOIN tb_cat_unidades_ejecutoras tce on  (tce.ur = t.tagref)
    INNER JOIN tb_sec_users_ue ON tb_sec_users_ue.userid = u.userid AND tb_sec_users_ue.tagref = t.tagref AND tb_sec_users_ue.ue = tce.ue
    ".$sqlJoinAlmacen."
    WHERE tce.active = 1 and u.userid = '$_SESSION[UserID]' $condicion
    ORDER BY ue,descrip  ASC";



    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'ue' => $myrow ['ue'], 'uedescription' => $myrow ['uedescription']);
    }

    $contenido = array('datos' => $info);
    $result = true;

    /*if($_SESSION['UserID'] == "lcarcases") {
        var_export($contenido);
    } */

}



if ($option == 'mostrarUnidadEjecutoraGeneral') {
    $tagref = implode("', '", explode(",,,", $_POST['tagref']));
    $multiple = 0;

    if (isset($_POST['multiple'])) {
        $multiple = $_POST['multiple'];
    }

    $sqlWhere = "";
    if ($tagref != '-1' and !empty($tagref)) {
        $sqlWhere = " AND t.tagref IN('$tagref') ";
    }/* else if ($tagref != '-1' and !empty($tagref) and $multiple == '1') {
        $sqlWhere = " AND t.tagref IN(".$tagref.") ";
    }*/

    $info = array();
    /*$SQL = "SELECT  t.tagref as value, CONCAT(t.tagref, ' - ', t.tagdescription) as texto, t.tagref
            FROM sec_unegsxuser u,tags t 
            WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' ".$sqlWhere." 
            ORDER BY t.tagref ";*/
    $SQL= "SELECT t.legalid as dependencia,  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription,  tce.ue as value, CONCAT(tce.ue, ' - ', tce.desc_ue) as texto 
    FROM sec_unegsxuser u
    INNER JOIN tags t on (u.tagref = t.tagref)
    INNER JOIN tb_cat_unidades_ejecutoras tce on  (tce.ur = t.tagref)
    JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = tce.ur AND tb_sec_users_ue.ln_aux1 = tce.ln_aux1
    WHERE tce.active = 1 
    AND u.userid = '" . $_SESSION['UserID'] . "'
    AND tb_sec_users_ue.userid = '" . $_SESSION['UserID'] . "' ".$sqlWhere."
    ORDER BY t.tagref";
    $SQL = "SELECT t.legalid as dependencia, t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription,  tce.ue as value, CONCAT(tce.ue, ' - ', tce.desc_ue) as texto
    FROM sec_unegsxuser u
    INNER JOIN tags t on (u.tagref = t.tagref)
    INNER JOIN tb_cat_unidades_ejecutoras tce on  (tce.ur = t.tagref)
    INNER JOIN tb_sec_users_ue ON tb_sec_users_ue.userid = u.userid AND tb_sec_users_ue.tagref = t.tagref AND tb_sec_users_ue.ue = tce.ue
    WHERE tce.active = 1 and u.userid = '" . $_SESSION['UserID'] . "' ".$sqlWhere."
    ORDER BY value, texto ASC";
    $ErrMsg = "No se obtuvieron las UE";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'], 'dependencia' => $myrow ['dependencia'] );
    }

    /*if($_SESSION['UserID'] == "lcarcases") {
        var_export($SQL);
    }*/

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarObjetoParcialGeneral') {
    $objPrincipal = implode("', '", explode(",,,", $_POST['objPrincipal']));

    $sqlWhere = "";
    if ($objPrincipal != '-1' and !empty($objPrincipal)) {
        $sqlWhere = " AND locstock.loccode IN('$objPrincipal') ";
    }

    $info = array();
    $SQL = "SELECT stockmaster.stockid as value, CONCAT(stockmaster.stockid, ' - ', stockmaster.description) as texto  
    FROM stockmaster 
    JOIN locstock ON locstock.stockid = stockmaster.stockid
    JOIN sec_objetoprincipalxuser ON sec_objetoprincipalxuser.loccode = locstock.loccode AND sec_objetoprincipalxuser.userid = '".$_SESSION['UserID']."'
    WHERE stockmaster.discontinued = 1 ".$sqlWhere."
    ORDER BY locstock.loccode, stockmaster.stockid ASC ";
    $ErrMsg = "No se obtuvieron los registros";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto']);
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarDocumentosPoliza') {
    $info = array();
    $SQL = "SELECT typeid as value, CONCAT(typeid, ' - ', typename) as texto FROM systypescat WHERE nu_activo = 1 ORDER BY typeid";
    $ErrMsg = "No se obtuvieron los documentos de pólizas";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}



if ($option == 'mostrarContratos') {
    $info = array();
    $SQL = " SELECT configContratos.id_contratos as value, CONCAT(configContratos.id_contratos,' - ',configContratos.id_loccode, ' - ', locations.locationname) as texto 
    FROM tb_contratos_contribuyentes as configContratos 
    JOIN sec_contratoxuser ON sec_contratoxuser.id_contratos = configContratos.id_contratos AND sec_contratoxuser.userid = '".$_SESSION['UserID']."'
    LEFT JOIN locations ON locations.loccode = configContratos.id_loccode 
    WHERE nu_estatus = 1 
    ORDER BY value";
    $ErrMsg = "No se obtuvieron los contratos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarIdentificadorCuentas') {
    $identificador = $_POST['identificador'];
    $sqlWhere = "";
    if (trim($identificador) != '') {
        $sqlWhere = " AND ln_clave = '".$identificador."' ";
    }
    $info = array();
    $SQL = "SELECT accountcode, CONCAT(accountcode, ' - ', accountname) as accountname, group_ as padre
    FROM chartmaster
    WHERE LENGTH(accountcode)>=7 ".$sqlWhere."
    ORDER BY group_, accountcode";
    $ErrMsg = "No se obtuvieron las Cuentas con Identificador";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['accountcode'], 'texto' => $myrow ['accountname'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarIdentificadorExtraPresupuestal') {
    $identificador = $_POST['identificador'];
    $sqlWhere = "";
    if (trim($identificador) != '') {
        $sqlWhere = " WHERE ln_clave = '".$identificador."' ";
    }
    $info = array();
    $SQL = "SELECT categoryid, categorydescription FROM tb_matriz_extraptal ".$sqlWhere." ORDER BY categorydescription";
    $ErrMsg = "No se obtuvieron las Cuentas con Identificador";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['categoryid'], 'texto' => $myrow ['categorydescription'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarConfiguracionClave') {
    $sqlWhere = "";
    if (isset($_POST['year']) && !empty($_POST['year'])) {
        // Si viene año
        $sqlWhere = " AND nu_anio = '".$_POST['year']."'";
    }

    if (isset($_POST['tipo']) && !empty($_POST['tipo'])) {
        $sqlWhere = " AND tipo_config = '".$_POST['tipo']."'";
    }
    
    $info = array();
    $SQL = "SELECT DISTINCT idClavePresupuesto as value, CONCAT(idClavePresupuesto, ' - ', nombreConfig) as texto, idClavePresupuesto
    FROM budgetConfigClave 
    WHERE 1 = 1 ".$sqlWhere."
    ORDER BY idClavePresupuesto ASC";
    $ErrMsg = "No se obtuvo la configuración del presupuesto";
    $transResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($transResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarTipoSuficiencia') {
    $info = array();
    $SQL = "SELECT nu_tipo as value, sn_nombre as texto FROM tb_suficiencias_cat ORDER BY sn_nombre";
    $ErrMsg = "No se obtuvieron los tipos de suficiencia";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarRegion') {
    $info = array();
    $SQL = "SELECT DISTINCT regions.regioncode, CONCAT(regions.regioncode,' - ',regions.name) as name
            FROM regions
            JOIN areas ON areas.regioncode = regions.regioncode
            JOIN tags ON tags.areacode = areas.areacode JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
            WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'";

    $ErrMsg = "No se obtuvieron las regiones";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'regioncode' => $myrow ['regioncode'], 'regionname' => $myrow ['name'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarArea') {
    $info = array();
    $SQL = "SELECT DISTINCT areas.areacode, CONCAT(areas.areacode,' - ',areas.areadescription) as name
            FROM areas
            JOIN tags ON tags.areacode = areas.areacode
            JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
            WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'";

    $ErrMsg = "No se obtuvieron las areas";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'areacode' => $myrow ['areacode'], 'areaname' => $myrow ['name'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarAlmacen') {
    $info = array();

    $SQL = "SELECT DISTINCT locations.loccode, CONCAT(locations.loccode,' - ',locations.locationname) as locationname
            FROM locations, sec_loccxusser
            WHERE locations.loccode=sec_loccxusser.loccode AND sec_loccxusser.userid='" . $_SESSION['UserID'] . "'";

    if (isset($_POST['ur']) and $_POST['ur'] != "" and $_POST['ur'] != "-1") {
        $SQL .= " AND locations.tagref IN (".$_POST['ur'].") ";
    }

    if (isset($_POST['ur']) and $_POST['ur'] != "" and $_POST['ur'] != "-1") {
        $SQL .= " AND locations.ln_ue IN (".$_POST['ue'].") ";
    }

    $SQL .= "ORDER BY locationname";


    $ErrMsg = "No se obtuvieron los almacenes";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'loccode' => $myrow ['loccode'], 'locationname' => $myrow ['locationname'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarDepartamentos') {
    $info = array();
    $SQL = "SELECT u_department, CONCAT(u_department,' - ',department) as name FROM departments";
    $ErrMsg = "No se obtuvieron los almacenes";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'u_department' => $myrow ['u_department'], 'departmentname' => $myrow ['name'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarCapitulos') {
    $info = array();
    $SQL = "SELECT ccap, CONCAT(ccap,' - ',descripcion) as name FROM tb_cat_partidaspresupuestales_capitulo";
    $ErrMsg = "No se obtuvieron los capitulos de las partidas presupuestales";

    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'capituloid' => $myrow ['ccap'], 'capituloname' => $myrow ['name'] );
    }
    //echo "aqui";

    $contenido = array('datos' => $info);
    //print_r($contenido);
    $result = true;
}

if ($option == 'mostrarCapitulosRadicacion') {
    $info = array();
    /*Solicitan que no muestre capitulo 1000, no se asigno por usuario, ya que podria afectarle en otro flujo*/
    $SQL = "SELECT ccap, CONCAT(ccap,' - ',descripcion) as name FROM tb_cat_partidaspresupuestales_capitulo where ccapmiles > 1000";
    $ErrMsg = "No se obtuvieron los capitulos de las partidas presupuestales";

    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'capituloid' => $myrow ['ccap'], 'capituloname' => $myrow ['name'] );
    }
    //echo "aqui";

    $contenido = array('datos' => $info);
    //print_r($contenido);
    $result = true;
}

if ($option == 'mostrarConceptos') {
    $info = array();
    $SQL = "Select ccap*10+ccon as conceptoid, concat(ccon, ' - ', descripcion) as name from tb_cat_partidaspresupuestales_concepto";
    $ErrMsg = "No se obtuvieron los conceptos de las partidas presupuestales";

    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'conceptoid' => $myrow ['conceptoid'], 'conceptoname' => $myrow ['name'] );
    }
    //echo "aqui";

    $contenido = array('datos' => $info);
    //print_r($contenido);
    $result = true;
}

if ($option == 'mostrarPartidasGenericas') {
    $info = array();
    $SQL = "Select (ccap*10+ccon)*10+cparg as partidagenericaid, concat(cparg, ' - ', descripcion) as name from tb_cat_partidaspresupuestales_partidagenerica";
    $ErrMsg = "No se obtuvieron las partidas genericas";

    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'partidagenericaid' => $myrow ['partidagenericaid'], 'partidagenericaname' => $myrow ['name'] );
    }
    //echo "aqui";

    $contenido = array('datos' => $info);
    //print_r($contenido);
    $result = true;
}

if ($option == 'mostrarPartidasEspecificas') {
    $info = array();
    $SQL = "Select partidacalculada, descripcion as partidadescripcion from tb_cat_partidaspresupuestales_partidaespecifica where activo ='S'";
    $ErrMsg = "No se obtuvieron las partidas especificas";

    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'partidacalculada' => $myrow ['partidacalculada'], 'partidadescripcion' => $myrow ['partidadescripcion']);
    }
    //echo "aqui";

    $contenido = array('datos' => $info);
    //print_r($contenido);
    $result = true;
}

if ($option == 'mostrarGeografico') {
    $info = array();
    $SQL = "SELECT cg, CONCAT(cg,' - ',descripcion) as descripcion FROM g_cat_geografico WHERE activo = 'S'";
    $ErrMsg = "No se obtuvieron los estados";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'cg' => $myrow ['cg'], 'descripcion' => $myrow ['descripcion'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarProveedor') {
    $info = array();
    $SQL = "SELECT supplierid, CONCAT(supplierid, ' - ', suppname) as proveedordescription  FROM suppliers ";
    $ErrMsg = "No se obtuvo el Proveedor";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'supplierid' => $myrow ['supplierid'], 'proveedordescription' => $myrow ['proveedordescription'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarStock') {
    $info = array();
    $SQL = "SELECT c_ClaveProdServ as stockID, CONCAT(c_ClaveProdServ, ' - ', Descripcion) as stockDescripcion 
    FROM sat_stock 
    ORDER BY c_ClaveProdServ ASC";
    $ErrMsg = "No se obtuvo el producto";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'stockID' => $myrow ['stockID'], 'stockDescripcion' => $myrow ['stockDescripcion'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarPago') {
    $info = array();
    $SQL = "SELECT paymentid as paymentID, CONCAT(paymentid, ' - ', paymentname) as paymentDescripcion 
    FROM sat_paymentmethodssat
    WHERE active = 1 AND invoiceuse = 1 
    ORDER BY paymentid, paymentname ASC";
    $ErrMsg = "No se obtuvo el metodo de pago";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'paymentID' => $myrow ['paymentID'], 'paymentDescripcion' => $myrow ['paymentDescripcion'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarGlAccounts') {
    $info = array();
    $SQL = "SELECT accountcode,
        accountname
        FROM chartmaster,
            accountgroups
        WHERE chartmaster.group_ = accountgroups.groupname
        AND accountgroups.pandl = 0
        ORDER BY accountcode";
    $ErrMsg = "No se obtuvo las cuentas de banco";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'accountcode' => $myrow ['accountcode'], 'accountname' => $myrow ['accountname'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarBankAccounts') {
    $info = array();
    $SQL = "SELECT bank_id, bank_name
    FROM banks
    WHERE bank_active = 1
    ORDER BY bank_name";
    $ErrMsg = "No se obtuvo las cuentas de banco";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'accountcode' => $myrow ['bank_id'], 'accountname' => $myrow ['bank_name'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}



if ($option == 'mostrarUnidad') {
    $info = array();
    $SQL = "SELECT unitsofmeasure.unitname as unitID, CONCAT(sat_unitsofmeasure.c_ClaveUnidad, ' - ', unitsofmeasure.unitname, ' ', sat_unitsofmeasure.Simbolo) as unitDescripcion 
    FROM unitsofmeasure
    JOIN sat_unitsofmeasure ON sat_unitsofmeasure.c_ClaveUnidad = unitsofmeasure.c_ClaveUnidad
    ORDER BY unitsofmeasure.unitname ASC";
    $ErrMsg = "No se obtuvo la unidad";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'unitID' => $myrow ['unitID'], 'unitDescripcion' => $myrow ['unitDescripcion'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarRegimenFiscal') {
    $info = array();
    $SQL = "SELECT sat_regimenfiscal.c_RegimenFiscal as idRegimen, CONCAT(sat_regimenfiscal.c_RegimenFiscal, ' - ', sat_regimenfiscal.descripcion) as regDescripcion 
    FROM sat_regimenfiscal
    ORDER BY sat_regimenfiscal.c_RegimenFiscal ASC";
    $ErrMsg = "No se obtuvo el Regimen Fiscal";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'idRegimen' => $myrow ['idRegimen'], 'regDescripcion' =>  substr($myrow ['regDescripcion'], 0, 58));
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarEstado') {
    $info = array();
    $SQL = "SELECT * FROM states
    ORDER BY state ASC";
    $ErrMsg = "No se obtuvo el Regimen Fiscal";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'state' => $myrow ['state']);
    }

    $contenido = array('datos' => $info);
    $result = true;
}



if ($option == 'mostrarPais') {
    $info = array();
    $SQL = "SELECT descripcion as pais FROM sat_paises
    ORDER BY descripcion ASC";
    $ErrMsg = "No se obtuvo el Regimen Fiscal";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'pais' => $myrow ['pais']);
    }

    $contenido = array('datos' => $info);
    $result = true;
}




if ($option == 'mostrarCFDI') {
    $info = array();
    $SQL = "SELECT sat_usocfdi.c_UsoCFDI as idCdfi, CONCAT(sat_usocfdi.c_UsoCFDI, ' - ', sat_usocfdi.descripcion) as cfdiDescripcion 
    FROM sat_usocfdi
    ORDER BY sat_usocfdi.c_UsoCFDI ASC";
    $ErrMsg = "No se obtuvo el Regimen Fiscal";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'idCdfi' => $myrow ['idCdfi'], 'cfdiDescripcion' =>  substr($myrow ['cfdiDescripcion'], 0, 58));
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarCuentaBanco') {
    $info = array();
    $SQL = "SELECT DISTINCT accountcode, CONCAT(bankaccountnumber,' - ',bankaccountname) as locationname FROM bankaccounts WHERE nu_activo = 1  ORDER BY bankaccountnumber, bankaccountname ASC";
    $ErrMsg = "No se obtuvo la Identificación de la Fuente";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'loccode' => $myrow ['accountcode'], 'locationname' => $myrow ['locationname'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarReporteContrato') {
    $info = array();
    $SQL = "SELECT DISTINCT 
    tb_reportes_contratos.nu_tipo AS loccode, 
    tb_reportes_contratos.sn_nombre  AS locationname
    FROM tb_reportes_contratos WHERE tb_reportes_contratos.sn_activo = 1  ORDER BY tb_reportes_contratos.nu_tipo ASC";
    $ErrMsg = "No se obtuvo la Identificación de la Fuente";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'loccode' => $myrow ['loccode'], 'locationname' => $myrow ['locationname'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}


if ($option == 'mostrarObjetoPrincipalUsuarios') {
    $info = array();
    $SQL = "SELECT DISTINCT locations.loccode, CONCAT(locations.loccode,' - ',locations.locationname) as locationname 
    FROM locations 
    WHERE locations.tipo = 'ObjetoPrincipal' and locations.activo = 1  ORDER BY locations.loccode, locations.locationname ASC";
    $ErrMsg = "No se obtuvo la Identificación de la Fuente";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'loccode' => $myrow ['loccode'], 'locationname' => $myrow ['locationname'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarObjetoPrincipal') {
    $info = array();
    $SQL = "SELECT locations.loccode, CONCAT(locations.loccode,' - ',locations.locationname) as locationname
    FROM locations 
    INNER JOIN sec_objetoprincipalxuser ON sec_objetoprincipalxuser.loccode = locations.loccode AND sec_objetoprincipalxuser.userid = '" . $_SESSION['UserID'] . "'
    WHERE locations.tipo = 'ObjetoPrincipal' and locations.activo = 1  ORDER BY locations.loccode, locations.locationname ASC";
    $ErrMsg = "No se obtuvo la Identificación de la Fuente";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'loccode' => $myrow ['loccode'], 'locationname' => $myrow ['locationname'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarFuenteRecurso') {
    $info = array();
    $SQL = "SELECT DISTINCT id_identificacion, CONCAT(id_identificacion, ' - ', desc_identificacion) as fuentedescription  FROM tb_cat_identificacion_fuente WHERE activo = 1 ORDER BY id_identificacion ASC";
    $ErrMsg = "No se obtuvo la Identificación de la Fuente";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'id_identificacion' => $myrow ['id_identificacion'], 'fuentedescription' => $myrow ['fuentedescription'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarFinalidad') {
    $info = array();
    $SQL = "SELECT DISTINCT id_finalidad, CONCAT(id_finalidad, ' - ', desc_fin) as finalidaddescription  FROM g_cat_finalidad WHERE activo = 1 ORDER BY id_finalidad ASC";
    $ErrMsg = "No se obtuvo la Finalidad";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'id_finalidad' => $myrow ['id_finalidad'], 'finalidaddescription' => $myrow ['finalidaddescription'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarRubroIngreso') {
    $info = array();
    $SQL = "SELECT DISTINCT clave, CONCAT(clave, ' - ', descripcion) as rubroDescripcion  FROM rubro_ingreso WHERE activo = 1 ORDER BY clave ASC";
    $ErrMsg = "No se obtuvo el rubro";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'clave' => $myrow ['clave'], 'rubroDescription' => $myrow ['rubroDescripcion'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarTipoIngreso') {
    $info = array();
    $SQL = "SELECT DISTINCT clave, CONCAT(clave, ' - ', descripcion) as tipoDescription  FROM tipo_ingreso WHERE activo = 1 ORDER BY clave ASC";
    $ErrMsg = "No se obtuvo el tipo";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'clave' => $myrow ['clave'], 'tipoDescription' => $myrow ['tipoDescription'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if($option == 'mostrarObjetoParcial'){
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

if ($option == 'mostrarTipoTarifas') {
    $anio = $_POST['anio'];
    $salesType = '';

    $info = array();

    $SQL = "SELECT tb_cat_tarifas.ln_nombre as nombre, tb_cat_tarifas.id_tarifa as idTarifa FROM salestypes LEFT JOIN tb_cat_tarifas ON tb_cat_tarifas.sales_type = salestypes.sales_type AND tb_cat_tarifas.active = '1' WHERE salestypes.anio = '".$anio."'";
    $ErrMsg = "No se obtuvo el tipo";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'clave' => $myrow ['idTarifa'], 'nombre' => $myrow ['nombre'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarValoresObjetosParcialesContrato') {
    $objeto_id = $_POST['objeto_id'];
    $info = array();

    $SQL = "SELECT objetosContrato.amt_valor as valor, objetosContrato.ln_metros as variable FROM tb_cat_objetos_contrato as objetosContrato WHERE objetosContrato.id_objetos = '".$objeto_id."' AND objetosContrato.ind_activo = '1'";
    $ErrMsg = "No se obtuvo los objetos contrato";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'valor' => $myrow ['valor'], 'variable' => $myrow ['variable'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarObjetosParcialesContrato') {
    $idconfigContratos = $_POST['id_configContratos'];
    $info = array();

    $SQL = "SELECT CONCAT(objetosContrato.id_stock, ' - ', objetosContrato.ln_metros) as nombre, objetosContrato.id_objetos as id_objetos, amt_valor as price FROM tb_cat_objetos_contrato as objetosContrato WHERE objetosContrato.id_contratos = '".$idconfigContratos."' AND objetosContrato.ind_activo = '1'";
    $ErrMsg = "No se obtuvo los objetos contrato";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'clave' => $myrow ['id_objetos'], 'nombre' => $myrow ['nombre'], 'price' => $myrow['price'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'checkPermissAll') {
    $userID = $_SESSION['UserID'];
    $SQL = "SELECT id_contratos as contratoID
	FROM sec_contratoxuser conf
	WHERE  conf.userid = '".$userID."'
	AND conf.id_contratos = '7'";
    $ErrMsg = "No se obtuvo la config contrato";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'isPermiss' => $myrow ['contratoID'] != '' ? true : false);
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarObjetosParcialesContratos2') {
    $idconfigContratos = $_POST['id_configContratos'];
    $info = array();

    $SQL = "SELECT DISTINCT
	prices.stockid as id_objetos,
    CONCAT(`locstock`.`stockid`, ' - ' , `stockmaster`.`description`) AS 'nombre',
   	ROUND(if(prices.tipo = 'UMA', prices.nu_price, tb_cat_tarifas.valor * prices.nu_price),2) as price
    FROM prices 
	JOIN tb_cat_tarifas on tb_cat_tarifas.id_tarifa = prices.tipo
    JOIN stockmaster on (stockmaster.stockid = prices.stockid)
    JOIN locstock ON (locstock.stockid = prices.stockid)
    JOIN locations on (locations.loccode = prices.id_op)
    WHERE locstock.loccode = 'TRAN'
    ORDER BY prices.stockid ASC";
    $ErrMsg = "No se obtuvo los objetos contrato";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'clave' => $myrow ['id_objetos'], 'nombre' => $myrow ['nombre'], 'price' => $myrow['price'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}



if ($option == 'mostrarFuncion') {
    $info = array();
    $SQL = "SELECT DISTINCT id_funcion, CONCAT(id_funcion, ' - ', desc_fun) as funciondescription  FROM g_cat_funcion WHERE activo = 1 ORDER BY id_funcion ASC";
    $ErrMsg = "No se obtuvo la Función";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'id_funcion' => $myrow ['id_funcion'], 'funciondescription' => $myrow ['funciondescription'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'tipoDocumentoParaPagosaProveedores') {
    $info = array();
    $SQL = "SELECT typeid,typename FROM systypescat
        WHERE typeid IN('480','24','121',501,20)
        ORDER BY typename";

    $ErrMsg = "No se obtuvo algún tipo de documento";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'id' => $myrow ['typeid'], 'nombre' => $myrow ['typename'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'tipoPagoTesoreria') {
    $info = array();
    $SQL = "SELECT paymentmethodssat.paymentid as id ,paymentmethodssat.paymentname as nombre FROM paymentmethodssat WHERE paymentmethodssat.sn_tesoreriaTipoPago=1";

    $ErrMsg = "No se obtuvo algún tipo de documento";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'id' => $myrow ['id'], 'nombre' => $myrow ['nombre'] );
    }

    $contenido = array('TiposPago' => $info);
    $result = true;
}

if ($option == 'diasFeriados') {
    $anioActual=date('Y');
    $info = array();

    $SQL="SELECT CASE WHEN mes >=1 AND mes <=9 THEN CONCAT('0',mes) ELSE mes END as mes,CASE WHEN dia >=1 AND dia <=9 THEN CONCAT('0',dia) ELSE dia END as dia from DWH_Tiempo where   Fecha >=STR_TO_DATE('".$anioActual."-01-01','%Y-%m-%d %H:%i:%s') and  Fecha <= STR_TO_DATE('".$anioActual."-12-31','%Y-%m-%d %H:%i:%s') and Feriado=1  and NombreDia!='Domingo'";

    $ErrMsg = "Sin dias feriados";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[]=$anioActual."-".$myrow ['mes']."-".$myrow ['dia'];
    }

    $contenido = array('diasFeriados' => $info);
    $result = true;
}

if ($option == 'mostrarMeses') {
    $meses= array('1' =>'Enero','2' =>'Febrero','3' =>'Marzo',
                  '4' =>'Abril','5' =>'Mayo','6' =>'Junio',
                  '7' =>'Julio','8' =>'Agosto','9' =>'Septiembre',
                  '10' =>'Octubre','11' =>'Noviembre','12' =>'Diciembre' );

    $contenido = array('Meses' => $meses);
    $result = true;
}

if ($option == 'mostrarPartidaEspecificaInstrumentales') {
    $tagref = $_POST['tagref'];
    $periodo = $_POST['periodo'];
    $info = array();
    $SQL = "SELECT DISTINCT tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada,
            tb_cat_partidaspresupuestales_partidaespecifica.descripcion AS partidadescripcion
            FROM tb_cat_partidaspresupuestales_partidaespecifica
            INNER JOIN tb_partida_articulo ON tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada= tb_partida_articulo.partidaEspecifica
            WHERE tb_cat_partidaspresupuestales_partidaespecifica.ccap = 5
            ORDER BY tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada";
    $ErrMsg = "Sin dias feriados";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'partidacalculada' => $myrow ['partidacalculada'],
            'partidadescripcion' => $myrow ['partidadescripcion'],
            'tagref' => $tagref,
            'clavePresupuestal' => '');
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarPartidaEspecificaProductos') {
    //$tagref = '500';
    $tagref = $_POST['tagref'];
    $periodo = $_POST['periodo'];
    $datoFuncionGeneral = $_POST['fnGeneral'];

    $info = array();

    

    $SQL= "SELECT DISTINCT tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada,
            tb_cat_partidaspresupuestales_partidaespecifica.descripcion AS partidadescripcion
            FROM tb_cat_partidaspresupuestales_partidaespecifica
            JOIN tb_partida_articulo ON tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada= tb_partida_articulo.partidaEspecifica
            JOIN stockmaster ON tb_partida_articulo.eq_stockid= stockmaster.eq_stockid
            WHERE tb_cat_partidaspresupuestales_partidaespecifica.ccap in (2,5)
            AND tb_cat_partidaspresupuestales_partidaespecifica.activo = 1
            AND stockmaster.mbflag = 'B'
            -- AND tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada NOT IN (22106,26103)
            -- AND tb_cat_partidaspresupuestales_partidaespecifica.descripcion NOT LIKE '%gaso%'
            ORDER BY tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada";

    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $ErrMsg = "No se obtuvo el registro";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        if ($datoFuncionGeneral == 1) {
            // Para funcion General
            $info[] = array(
                'value' => $myrow ['partidacalculada'],
                'texto' => $myrow ['partidacalculada'],
                'tagref' => $tagref,
                'clavePresupuestal' => '');
        } else {
            $info[] = array(
                'partidacalculada' => $myrow ['partidacalculada'],
                'partidadescripcion' => $myrow ['partidadescripcion'],
                'tagref' => $tagref,
                'clavePresupuestal' => '');
        }
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarPartidaEspecificaServicios') {
    $tagref = $_POST['tagref'];
    $periodo = $_POST['periodo'];
    $datoFuncionGeneral = $_POST['fnGeneral'];

    $info = array();

    $SQL= "SELECT DISTINCT tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada,
            tb_cat_partidaspresupuestales_partidaespecifica.descripcion AS partidadescripcion
            FROM tb_cat_partidaspresupuestales_partidaespecifica
            JOIN tb_partida_articulo ON tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada= tb_partida_articulo.partidaEspecifica
            JOIN stockmaster ON tb_partida_articulo.eq_stockid= stockmaster.eq_stockid
            WHERE tb_cat_partidaspresupuestales_partidaespecifica.ccap = 3
            AND tb_cat_partidaspresupuestales_partidaespecifica.ccon <> 7
            AND tb_cat_partidaspresupuestales_partidaespecifica.activo = 1
            AND stockmaster.mbflag = 'D'
            ORDER BY tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada";

    $ErrMsg = "No se obtuvieron las partidas especificas";

    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $ErrMsg = "No se obtuvo el registro";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        if ($datoFuncionGeneral == 1) {
            // Para funcion General
            $info[] = array(
                'value' => $myrow ['partidacalculada'],
                'texto' => $myrow ['partidacalculada'],
                'tagref' => $tagref,
                'clavePresupuestal' => '');
        } else {
            $info[] = array(
                'partidacalculada' => $myrow ['partidacalculada'],
                'partidadescripcion' => $myrow ['partidadescripcion'],
                'tagref' => $tagref,
                'clavePresupuestal' => '');
        }
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarPartidaEspecificaCveArticulo') {
    $info = array();
    $SQL = "SELECT DISTINCT stockmaster.stockid as idProducto,
                stockmaster.description as descripcionProducto,
                stockmaster.units as unidad,
                stockcostsxlegal.lastcost as precioEstimado,
                stockmaster.eq_stockid as codeProdPartida,
                stockmaster.mbflag as tipo,
                tb_partida_articulo.eq_stockid,
                tb_partida_articulo.partidaEspecifica as idPartidaEspecifica,
                tb_partida_articulo.descPartidaEspecifica as descPartidaEspecifica,
                tb_partida_articulo.niv
            FROM stockmaster
            JOIN tb_partida_articulo ON (stockmaster.eq_stockid =  tb_partida_articulo.eq_stockid)
            LEFT JOIN stockcostsxlegal ON (stockmaster.stockid =  stockcostsxlegal.stockid)
            WHERE stockmaster.mbflag = 'B'
            ORDER BY idPartidaEspecifica, descripcionProducto";

    $ErrMsg = "No se obtuvo el Articulo";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'idProducto' => $myrow ['idProducto'],
            'descripcionProducto' => $myrow ['descripcionProducto'],
            'unidad' => $myrow ['unidad'],
            'precioEstimado' => $myrow ['precioEstimado'],
            'idPartidaEspecifica' => $myrow ['idPartidaEspecifica'],
            'descPartidaEspecifica' => $myrow ['descPartidaEspecifica'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

// Seccion que regresa el listado de Estatus para las Ordenes de Compra
if ($option == 'traeEstatusRequisiciones') {
    $info = array();
    $funciones= "";
    $ErrMsg = "No se obtuvieron los estatus de requisiciones";
    $funcion= $_POST["numeroFuncion"];

    // considerar los estatus dependiendo de la funcion que los solicita
    //if ($_POST["filtro"] == "requisiciones") {
        $funciones= " AND sn_funcion_id= '".$funcion."' ";
    //}

    // Consultar estatus
    $consulta= "SELECT * FROM tb_botones_status WHERE sn_flag_disponible=1 AND `sn_captura_requisicion` = '1' ".$funciones." ORDER BY statusid";
    $resultado = DB_query($consulta, $db, $ErrMsg);

    while ($registro = DB_fetch_array($resultado)) {
        $info[] = array(
            'id' => $registro ['id'],
            'descripcion' => $registro ['sn_nombre_secundario'],
            'estatus' => $registro ['statusname'],
            'boton' => $registro ['namebutton']
        );
    }

    $contenido = array('datos' => $info);
    $result = true;
    $SQL= $consulta;
}

// Seccion que regresa el listado de Estatus General
if ($option == 'traeEstatusGeneral') {
    $info = array();
    $funciones= "";
    $ErrMsg = "No se obtuvieron los estatus de requisiciones";
    $funcion= $_POST["numeroFuncion"];

    // considerar los estatus dependiendo de la funcion que los solicita
    //if ($_POST["filtro"] == "requisiciones") {
    $funciones= " AND sn_funcion_id= '".$funcion."' ";
    //}

    // Consultar estatus
    // el campo sn_captura_requisicion funciona para saber si es un estatus o boton de accion
    $consulta= "SELECT DISTINCT tb_botones_status.id, tb_botones_status.statusid,
                tb_botones_status.statusname, tb_botones_status.namebutton, tb_botones_status.functionid,
                tb_botones_status.sn_estatus_siguiente, tb_botones_status.clases, tb_botones_status.sn_nombre_secundario
                FROM tb_botones_status
                WHERE sn_flag_disponible=1 AND sn_captura_requisicion=1
                AND tb_botones_status.functionid IN (SELECT functionid
                FROM sec_profilexuser INNER JOIN sec_funxprofile ON sec_profilexuser.userid= '".$_SESSION["UserID"]."'
                UNION
                SELECT functionid FROM sec_funxuser WHERE userid= '".$_SESSION["UserID"]."'
                GROUP BY functionid) ".$funciones."
                ORDER BY functionid";

    $resultado = DB_query($consulta, $db, $ErrMsg);

    while ($registro = DB_fetch_array($resultado)) {
        $info[] = array(
            'id' => $registro ['id'],
            'descripcion' => $registro ['sn_nombre_secundario'],
            'estatus' => $registro ['statusid'],
            'nombreestatus' => $registro ['statusname'],
            'boton' => $registro ['namebutton']
        );
    }

    $contenido = array('datos' => $info);
    $result = true;
    $SQL= "";
}
if ($option == 'traeEstatusGeneral1') {
    $info = array();
    $funciones= "";
    $ErrMsg = "No se obtuvieron los estatus de requisiciones";
    $funcion= $_POST["numeroFuncion"];

    // considerar los estatus dependiendo de la funcion que los solicita
    //if ($_POST["filtro"] == "requisiciones") {
    $funciones= " AND sn_funcion_id= '".$funcion."' ";
    //}

    // Consultar estatus
    // el campo sn_captura_requisicion funciona para saber si es un estatus o boton de accion
    $consulta= "SELECT DISTINCT tb_botones_status.id, tb_botones_status.statusid,
                tb_botones_status.statusname, tb_botones_status.namebutton, tb_botones_status.functionid,
                tb_botones_status.sn_estatus_siguiente, tb_botones_status.clases, tb_botones_status.sn_nombre_secundario
                FROM tb_botones_status
                WHERE sn_flag_disponible=1 AND sn_captura_requisicion=1
                AND tb_botones_status.functionid IN (SELECT functionid
                FROM sec_profilexuser INNER JOIN sec_funxprofile ON sec_profilexuser.userid= '".$_SESSION["UserID"]."'
                UNION
                SELECT functionid FROM sec_funxuser WHERE userid= '".$_SESSION["UserID"]."'
                GROUP BY functionid) ".$funciones."
                ORDER BY functionid";

    $resultado = DB_query($consulta, $db, $ErrMsg);

    while ($registro = DB_fetch_array($resultado)) {
        $info[] = array(
            'id' => $registro ['id'],
            'descripcion' => $registro ['sn_nombre_secundario'],
            'estatus' => $registro ['statusid'],
            'nombreestatus' => $registro ['statusname'],
            'boton' => $registro ['namebutton']
        );
    }

    $contenido = array('datos' => $info);
    $result = true;
    $SQL= "";
}
// Seccion que regresa el listado de Estatus para las Ordenes de Compra
if ($option == 'mostrarAnios') {
    $info = array();
    $ErrMsg = "No se obtuvieron los Años del componente";
    $AnioInicial = 2008;
    $AnioFinal = 2030;

    $Anio = $AnioInicial;

    $SQL = "SELECT distinct DATE_FORMAT(periods.lastdate_in_period, '%Y') AS year FROM periods";
    $ErrMsg = "No se obtuvieron los años";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'id' => $myrow['year'],
            'descripcion' => $myrow['year']
        );
    }

    // while ($Anio <= $AnioFinal) {
    //     $info[] = array(
    //         'id' => $Anio,
    //         'descripcion' => $Anio
    //     );
    //     $Anio ++;
    // }

    $contenido = array('datos' => $info);
    $result = true;
}

// modelo de datos que regresa el listado de botones a utilizar
// de acuerdo al permiso y funcion
if ($option == 'obtenerBotones') {
    $info = array();
    $funcionid= $_POST["funcionid"];

    // consulta para sacar los botones a utilizar de acuerdo a la configuracion de permisos del usuario
    $SQL = "SELECT DISTINCT tb_botones_status.statusid,
            tb_botones_status.statusname,
            tb_botones_status.namebutton,
            tb_botones_status.functionid,
            tb_botones_status.adecuacionPresupuestal,
            tb_botones_status.clases,
            tb_botones_status.sn_estatus_siguiente
            FROM tb_botones_status
            JOIN sec_profilexuser ON sec_profilexuser.userid = '".$_SESSION['UserID']."'
            JOIN sec_funxprofile ON sec_funxprofile.profileid = sec_profilexuser.profileid
            WHERE
            (tb_botones_status.sn_funcion_id = '".$funcionid."')
            AND (tb_botones_status.sn_flag_disponible = 1)
            AND (tb_botones_status.functionid = sec_funxprofile.functionid
            OR
            tb_botones_status.functionid = (SELECT sec_funxuser.functionid FROM sec_funxuser WHERE sec_funxuser.functionid = tb_botones_status.functionid AND sec_funxuser.userid = '".$_SESSION['UserID']."' AND permiso=1)
            )
            ORDER BY tb_botones_status.statusid ASC, tb_botones_status.statusname ASC";

    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'statusid' => $myrow ['sn_estatus_siguiente'],
            'statusname' => $myrow ['statusname'],
            'namebutton' => $myrow ['namebutton'],
            'functionid' => $myrow ['functionid'],
            'clases' => $myrow ['clases']
        );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

// modelo de datos que regresa el listado cuentas contables
if ($option == 'traeCuentasContables') {
    $info = array();
}

if ($option == 'solucitudAlmacenRequisicion') {
    $arrayDatosRequisicion = array();
    $idReq = $_POST['idrequi'];
    $comments = $_POST['comments'];
    $tagref = $_POST['tagref'];
    $ue = $_POST['ue'];
    $status = 43;
    $ordenElemento = 0;
    $user = $_SESSION ['UserID'];
    $datosRequisicion = $_POST['datosRequisicion'];
    $transno = GetNextTransNo(1000, $db);
    $nombreEstatus = "Avanzada al autorizador";
    //print_r($datosRequisicion);
    $SQL = "INSERT INTO tb_solicitudes_almacen (nu_tag,ln_usuario,estatus,nu_folio,txt_observaciones,ln_nombre_estatus,ln_ue) VALUES ('". $tagref . "','".$_SESSION ['UserID']."'". ",'".$status."','".$transno."','".$comments."','".$nombreEstatus."','".$ue."')";
    $ErrMsg = "No se encontro un requisicion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    for ($a=0; $a<count($datosRequisicion); $a++) {
         $arrayDatosRequisicion[] = explode(",", $datosRequisicion[$a]);
    }

    $cadena='';
    for ($f=0; $f<count($arrayDatosRequisicion); $f++) {
        $ordenElemento = $ordenElemento + 1;
        $cadenaFinal ="";
        $cadena.="('".$transno."','PZA','".$ordenElemento."',1,";
        for ($c=3; $c<7; $c++) {
            if ($c == 5) {
            } else {
                $cadena.="'".$arrayDatosRequisicion[$f][$c]."',";
            }
        }
         $cadena=substr($cadena, 0, -1);
         $cadena.='),';
    }
            $cadena=substr($cadena, 0, -1);
            $values = "".$cadena."";

            print_r($values);

            $SQLSA = "INSERT INTO tb_solicitudes_almacen_detalle (nu_id_solicitud,ln_unidad_medida,ln_renglon,ln_arctivo,ln_clave_articulo,txt_descripcion,nu_cantidad) VALUES ".$values;
            $ErrMsgSA = "Fallo la solicitud automatica";
            $TransResultSA = DB_query($SQLSA, $db, $ErrMsgSA);


    $contenido = "solicitud almacen automatica";
    $result = true;
}
if ($option == 'generarNoExistencia') {
    $arrayDatosNoExistvalores = array();
    $idReq = $_POST['idrequi'];
    $noReq = $_POST['norequi'];
    $norequi = GetNextTransNo(19, $db);
    $folioNoExistencia = GetNextTransNo(1004, $db);
    $comments = $_POST['comments'];
    $dependencia = $_POST['dependencia'];
    $tagref = $_POST['tagref'];
    $ue = $_POST['ue'];
    $fDelivery = date("Y-m-d", strtotime($_POST['fDelivery']));
    $generaNoExistencia = "0";
    $datosNoExistvalores = $_POST['noExistvalores'];
    $ordenElemento = 0;
    $usuarioNoExistencia = $_SESSION['UserID'];

    if (count($datosNoExistvalores) == 0 || $datosNoExistvalores == '') {
        $contenido = "Error no hay No Existencias" ;
        $result = false;
    } else {
        // inserta el registro en la tabla de no existencias
        $SQLNE="INSERT INTO tb_no_existencias (nu_id_no_existencia, nu_id_requisicion, dtm_fecharegistro, nu_tag, nu_ue, ln_usuario, status, txt_observaciones, nu_dependencia) VALUES (".$folioNoExistencia.", ".$noReq.", current_timestamp(), '$tagref','$ue', '$usuarioNoExistencia', '1', '$comments','$dependencia')";
        $ErrMsgNE = "No se pudo repicar la requisicion";
        $TransResultNE = DB_query($SQLNE, $db, $ErrMsgNE);
        $generaNoExistencia = DB_Last_Insert_ID($db, 'tb_no_existencias', 'nu_id_no_existencia');
        $cadenaNoExist='';
        for ($a=1; $a<count($datosNoExistvalores); $a++) {
            $ordenElemento++;
            $orden = $datosNoExistvalores[$a][0]['orden'];
            $cvepre = $datosNoExistvalores[$a][0]['cvepre'];
            $item = $datosNoExistvalores[$a][0]['item'];
            $desc = $datosNoExistvalores[$a][0]['desc'];
            $qty = $datosNoExistvalores[$a][0]['qty'];

            $cadenaNoExist.= "('".$noReq."','".$generaNoExistencia."','PZA','',1,'".$ordenElemento."','".$cvepre."','".$item."','".$desc."','".$qty."')";
            $cadenaNoExist.= ",";
        }
        $cadenaNoExist=substr($cadenaNoExist, 0, -1);

        //echo  "cadena No existencia: " . $cadenaNoExist;
        $SQLNED="INSERT INTO tb_no_existencia_detalle (nu_id_requisicion, nu_id_no_existencia, ln_unidad_medida, ln_cams, ln_activo, ln_renglon, ln_partida_esp, ln_item_code, txt_item_descripcion, nu_cantidad ) VALUES
                ".$cadenaNoExist;
        $ErrMsgNED = "No se pudo repicar la requisicion";
        $TransResultNED = DB_query($SQLNED, $db, $ErrMsgNED);


        //$contenido = "Se creo una solicitud de no existencia apartir de la requisicion ";
        $contenido = array('datos' => $folioNoExistencia);
        $result = true;
    }
}

if ($option == 'validaNoExistencia') {
    $idReq = $_POST['idrequi'];
    $SQL = "SELECT nu_tag, nu_ue, nu_id_requisicion, nu_id_no_existencia, status FROM tb_no_existencias WHERE nu_id_no_existencia != '' AND nu_id_requisicion = '$idReq' ";
    $ErrMsg = "No se pudo repicar la requisicion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($row = DB_fetch_array($TransResult)) {
        $id_no_existencia = $row ['nu_id_no_existencia'];
    }
    $contenido = array('datos' => $id_no_existencia);
    $result = true;
}

if ($option == 'mostrarLastInsert') {
    $table = $_POST['table'];
    $id = $_POST['id'];
    $SQL = "SELECT MAX(".$id.") as lastID FROM ".$table." WHERE debtorno";
    $ErrMsg = "No se pudo obtener el ultimo ID";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($row = DB_fetch_array($TransResult)) {
        $lastID = $row ['lastID'];
    }
    $contenido = array('datos' => $lastID);
    $result = true;
}

if ($option == 'mostrarRegionEstado') {
    $state = $_POST['state'];

    $sql = "SELECT id_state
		FROM `states`
		WHERE `state` = '".$state."' ";
    $TransResult = DB_query($sql, $db, $ErrMsg);

	$rs = DB_fetch_array($TransResult);

	$sql = "SELECT ln_nombre as region
		FROM `tb_cat_municipio`
        WHERE `id_nu_entidad_federativa` = '".$rs['id_state']."'
        ORDER BY ln_nombre ASC";
        
    $info = array();
   
    $ErrMsg = "No se obtuvo la Region";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'ln_nombre' => $myrow ['region']);
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'validaSolAlmacen') {
    $idReq = $_POST['idrequi'];
    $SQL = "SELECT nu_id_solicitud ,nu_tag, nu_folio,ln_ue, nu_id_requisicion FROM tb_solicitudes_almacen WHERE nu_id_requisicion = '$idReq'";
    $ErrMsg = "No se pudo repicar la requisicion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($row = DB_fetch_array($TransResult)) {
        $folio = $row ['nu_folio'];
    }
    $contenido = array('datos' => $folio);
    $result = true;
}

if ($option == 'generarSolAlmacen') {
    $arrayDatosRequisicion = array();
    $idReq = $_POST['idrequi'];
    $noReq = $_POST['norequi'];
    $newNoRequi = GetNextTransNo(19, $db);
    $folioNoExistencia = GetNextTransNo(1004, $db);
    $genNuevaRequisicion = "0";
    $comments = $_POST['comments'] ;
    $dependencia = $_POST['dependencia'];
    $tagref = $_POST['tagref'];
    $ue = $_POST['ue'];
    $fDelivery = date("Y-m-d", strtotime($_POST['fDelivery']));
    $statusNewRequi = 'Capturado';
    $status = 43;
    $ordenElemento = 0;
    $ordenElementoNE = 0;
    $usuarioSolAlmacen = $_SESSION ['UserID'];
    $usuarioNoExistencia = $_SESSION['UserID'];
    $generaSolAlmacen = "0";
    $datosSolAlmacen = $_POST['solAlmacen'];
    $datosNoExistvalores = $_POST['noExistvalores'];
    $generaNoExistencia = "0";
    $transno = GetNextTransNo(1000, $db);
    $nombreEstatus = "Avanzada al autorizador";
    //print_r($datosRequisicion);

    if (count($datosSolAlmacen) == 0 || $datosSolAlmacen == '') {
        $contenido = "Error no se puede hacer una solictud automatica" ;
        $result = false;
    } else {
        // inserta la requisicion replicada
        $SQL = "INSERT INTO purchorders
        (
            supplierno,comments,rate,allowprint,initiator,requisitionno,intostocklocation,
            deladd1,deladd2,deladd3,deladd4,deladd5,deladd6,contact,version,realorderno,deliveryby,
            status,stat_comment,tagref,dateprinted,orddate,validfrom,validto,revised,deliverydate,lastUpdated,
            autorizafecha,fecha_modificacion,consignment,autorizausuario,capturausuario,solicitausuario,status_aurora,
            supplierorderno,currcode,wo,foliopurch,telephoneContact,refundpercentpurch,totalrefundpercentpurch,systypeorder,
            noag_ad,servicetype,clavepresupuestal,fileRequisicion,nu_ue,nu_anexo_tecnico
        ) VALUES
        (
            '111111','".$comments."... requisición generada a partir de la requisición :".$noReq."',1,1,'$usuarioSolAlmacen','$newNoRequi',4,'dir1','dir2','dir3','dir4','dir5','dir6','','0.00','','','$statusNewRequi',concat(curdate(),' - Order Creada ',curdate(),' - Replica: $usuarioSolAlmacen'),'$tagref','1900-01-01 01:01:01',concat(curdate(),' ',TIME(NOW())),current_timestamp(),'$fDelivery','1900-01-01','$fDelivery',current_timestamp(),'1900-01-01',current_timestamp(),'0','$usuarioSolAlmacen','$usuarioSolAlmacen','$usuarioSolAlmacen','','','MXN',0,'','',0,0,0,'',0,'',0,'$ue',0
        )";
        $ErrMsg = "No se encontro un requisicion";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $genNuevaRequisicion = DB_Last_Insert_ID($db, 'purchorders', 'OrderNo');
        // inserta el registro en la tabla de solicitud almacen
        $SQLSolAl = "INSERT INTO tb_solicitudes_almacen (nu_tag,ln_usuario,estatus,nu_folio,txt_observaciones,ln_nombre_estatus,ln_ue,ln_tipo_solicitud, nu_id_requisicion) VALUES ('". $tagref . "','".$_SESSION ['UserID']."','".$status."','".$transno."','".$comments."Solicitud generada para la requisición :".$newNoRequi."','".$nombreEstatus."','".$ue."','Automática', ".$genNuevaRequisicion.")";
        $ErrMsgSolAl = "No se encontro una requisicion";
        $TransResultSolAl = DB_query($SQLSolAl, $db, $ErrMsgSolAl);

        $SQLNE="INSERT INTO tb_no_existencias (nu_id_no_existencia, nu_id_requisicion, dtm_fecharegistro, nu_tag, nu_ue, ln_usuario, status, txt_observaciones, nu_dependencia) VALUES (".$folioNoExistencia.", ".$genNuevaRequisicion.", current_timestamp(), '$tagref','$ue', '$usuarioNoExistencia', '1', '".$comments."No existencia generada para la requisición: ".$newNoRequi."','$dependencia')";
        $ErrMsgNE = "No se pudo repicar la requisicion";
        $TransResultNE = DB_query($SQLNE, $db, $ErrMsgNE);
        $generaNoExistencia = DB_Last_Insert_ID($db, 'tb_no_existencias', 'nu_id_no_existencia');
        $cadenaNoExist='';

        // CAmbia estatus de la requisición Original
        $SQLOriginal = "UPDATE purchorders SET status = 'Original' where orderno = '$idReq'";
        $ErrMsgOriginal = "No se encontro requisicion";
        $TransResultOriginal = DB_query($SQLOriginal, $db, $ErrMsgOriginal);

        $cadenaSolAlmacen='';
        $cadenaRequi='';

        /*$candenaWhenOntrasit=" ";
        $articulosClaveOntrasit=""; */

        for ($a=1; $a<count($datosSolAlmacen); $a++) {
            $ordenElemento++;
            $item = $datosSolAlmacen[$a][0]['item'];
            $desc = $datosSolAlmacen[$a][0]['desc'];
            $qty = $datosSolAlmacen[$a][0]['qty'];
            $precio = $datosSolAlmacen[$a][0]['precio'];
            $total_quantity = $qty * $precio;
            //if(strpos($item, 2, 0) == 0){
                $cadenaSolAlmacen.= "('".$transno."','PZA','".$ordenElemento."',1,'".$item."','".$desc."','".$qty."')";
                $cadenaSolAlmacen.= ",";
            //}
            /*$articulosClaveOntrasit.= "'".$item."',";
            $candenaWhenOntrasit.=" WHEN stockid='".strtoupper($item)."' THEN (locstock.ontransit + ".$qty.")"; */
        }
        $cadenaSolAlmacen=substr($cadenaSolAlmacen, 0, -1);

        for ($a=1; $a<count($datosNoExistvalores); $a++) {
            $ordenElementoNE++;
            $ordenNE = $datosNoExistvalores[$a][0]['ordenNE'];
            $cvepreNE = $datosNoExistvalores[$a][0]['cvepreNE'];
            $itemNE = $datosNoExistvalores[$a][0]['itemNE'];
            $descNE = $datosNoExistvalores[$a][0]['descNE'];
            $qtyNE = $datosNoExistvalores[$a][0]['qtyNE'];
            $precioNE = $datosNoExistvalores[$a][0]['precioNE'];
            $total_quantityNE = $qtyNE * $precioNE;

            $cadenaFinal ="'".$ordenElementoNE."','".$fDelivery."','1.1.5.1.1', 0, 0, 0, 0, 0, 0, '', 0, '', 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, current_timestamp(), 0, 0, 0, 0, 0,'', '', 0, '', '', '', '1900-01-01', '1900-01-01', current_timestamp(),'', 1, 1, 0, '', '', 2";
            $cadenaRequi.="('".$genNuevaRequisicion."',".$cadenaFinal.",'".$cvepreNE."','".$itemNE."','".$descNE."','".$precioNE."',0.00,0.00,'".$qtyNE."','".$total_quantityNE."')";
            $cadenaRequi .= ",";

            $cadenaNoExist.= "('".$genNuevaRequisicion."','".$generaNoExistencia."','PZA','',1,'".$ordenElementoNE."','".$cvepreNE."','".$itemNE."','".$descNE."','".$qtyNE."','".$cvepreNE."')";
            $cadenaNoExist.= ",";
        }
        $cadenaRequi=substr($cadenaRequi, 0, -1);
        $cadenaNoExist=substr($cadenaNoExist, 0, -1);

        $SQLPD="INSERT INTO purchorderdetails (
            orderno,
            orderlineno_,
            deliverydate,
            glcode,
            actprice,
            stdcostunit,
            shiptref,
            jobref,
            completed,
            itemno,
            uom,
            subtotal_amount,
            package,
            pcunit,
            nw,
            suppliers_partno,
            gw,
            cuft,
            total_amount,
            discountpercent1,
            discountpercent2,
            discountpercent3,
            narrative,
            justification,
            refundpercent,
            lastUpdated,
            totalrefundpercent,
            estimated_cost,
            saleorderno_,
            wo,
            qtywo,
            womasterid,
            wocomponent,
            idgroup,
            typegroup,
            customs,
            pedimento,
            dateship,
            datecustoms,
            fecha_modificacion,
            inputport,
            factorconversion,
            invoice_rate,
            flagautoemision,
            sn_descripcion_larga,
            renglon,
            status,
            clavepresupuestal, itemcode, itemdescription, unitprice, quantityrecd, qtyinvoiced, quantityord, total_quantity)
            VALUES
                ".$cadenaRequi;
                $ErrMsgPD = "No se pudo repicar la requisicion" . $noReq;
                $TransResultPD = DB_query($SQLPD, $db, $ErrMsgPD);

        $SQLSA = "INSERT INTO tb_solicitudes_almacen_detalle (nu_id_solicitud,ln_unidad_medida,ln_renglon,ln_arctivo,ln_clave_articulo,txt_descripcion,nu_cantidad) VALUES ".$cadenaSolAlmacen;
        $ErrMsgSA = "Fallo la solicitud automatica";
        $TransResultSA = DB_query($SQLSA, $db, $ErrMsgSA);
        $articulosClaveOntrasit=substr($articulosClaveOntrasit, 0, -1);

        $SQLNED="INSERT INTO tb_no_existencia_detalle (nu_id_requisicion, nu_id_no_existencia, ln_unidad_medida, ln_cams, ln_activo, ln_renglon, ln_partida_esp, ln_item_code, txt_item_descripcion, nu_cantidad, clavepresupuestal ) VALUES
                ".$cadenaNoExist;
        $ErrMsgNED = "No se pudo repicar la requisicion";
        $TransResultNED = DB_query($SQLNED, $db, $ErrMsgNED);

        /*$SQL=" UPDATE locstock SET   locstock.ontransit = CASE ".  $candenaWhenOntrasit ." END WHERE locstock.stockid IN (".$articulosClaveOntrasit.") AND loccode='"."3"."';";
        $ErrMsg = "No se agregó cambios al almacen";
        $TransResult = DB_query($SQL, $db, $ErrMsg);  */

        $contenido = array('datos' => $transno, 'datoNe' => $generaNoExistencia);
        $result = true;
    }
}

if ($option == 'actualizarNoExistencia') {
    $idReq = $_POST['idrequi'];
    $folioNoE = $_POST['folioNoE'];
    $datosNoExistvalores = $_POST['noExistvalores'];
    $ordenElemento = 0;

    for ($a=0; $a<count($datosNoExistvalores); $a++) {
        $ordenNE = $datosNoExistvalores[$ordenElemento][0]['ordenNE'];
        $cvepreNE = $datosNoExistvalores[$ordenElemento][0]['cvepreNE'];
        $itemNE = $datosNoExistvalores[$ordenElemento][0]['itemNE'];
        $descNE = $datosNoExistvalores[$ordenElemento][0]['descNE'];
        $qtyNE = $datosNoExistvalores[$ordenElemento][0]['qtyNE'];

        $SQL = "UPDATE tb_no_existencia_detalle SET nu_cantidad = '$qtyNE' WHERE nu_id_no_existencia = '$folioNoE' AND nu_id_requisicion = '$idReq' AND ln_item_code = '$itemNE' AND ln_activo = 1 AND nu_id_no_existencia_detalle > 0";
        $ErrMsg = "Error al actualizar la no existencia";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $ordenElemento++;
    }

    $result = true;
}

if ($option == 'actualizarSolAlmacen') {
    $idReq = $_POST['idrequi'];
    $datosSolAlmacen = $_POST['solAlmacen'];
    $ordenElemento = 0;

    for ($a=0; $a<count($datosSolAlmacen); $a++) {
        $item = $datosSolAlmacen[$ordenElemento][0]['item'];
        $desc = $datosSolAlmacen[$ordenElemento][0]['desc'];
        $qty = $datosSolAlmacen[$ordenElemento][0]['qty'];
        $precio = $datosSolAlmacen[$ordenElemento][0]['precio'];

        $SQL = "UPDATE tb_solicitudes_almacen_detalle SET nu_cantidad = '$qty' WHERE nu_id_solicitud = (select nu_folio from tb_solicitudes_almacen where nu_id_requisicion = '$idReq') AND ln_clave_articulo = '$item' AND ln_arctivo = 1 AND nu_id_detalle > 0 ";
        $ErrMsg = "Error al actualizar la solicitud";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $ordenElemento++;
    }

    $result = true;
}

/**
 * Consulta de los datos para el elemento select de sub funcion
 * @date:26.03.18
 * @author: Desarrollo
 */
if ($option == 'muestraSubFuncion') {
    unset($contenido);
    unset($result);
    unset($sql);
    unset($ErrMsg);
    unset($resulset);
    unset($condicion);
    $contenido = [];
    $condicion='';
    $ErrMsg = "No se obtuvo datos de las sub funciones";

    $condicion .= (!empty($_POST['fi'])? " AND `id_finalidad` = {$_POST['fi']} " : '');
    $condicion .= (!empty($_POST['fu'])? " AND `id_funcion` = {$_POST['fu']} " : '');

    $sql = "SELECT `id_subfuncion` as val, CONCAT(`id_subfuncion`,' - ',`desc_subfun`) as text FROM `g_cat_sub_funcion` WHERE `activo` = 1 ".$condicion." ORDER BY `id_subfuncion` asc";
    $resulset = DB_query($sql, $db, $ErrMsg);
    while ($rs = DB_fetch_array($resulset)) {
        $contenido[] = [ 'val'=>$rs['val'], 'text'=>$rs['text'] ];
    }
    $result = true;
}

/**
 * Consulta de los datos para el elemento select de reasignacion
 * @date:26.03.18
 * @author: Desarrollo
 */
if ($option == 'muestraReasignacion') {
    unset($contenido);
    unset($result);
    unset($sql);
    unset($ErrMsg);
    unset($resulset);
    unset($condicion);
    $contenido = [];
    $ErrMsg = "No se obtuvo datos de la reasignacion";

    $sql = "SELECT `cprg` as val, CONCAT(`cprg`,' - ',`desc_rg`) as text FROM `g_cat_reasignacion` WHERE `activo` = 1 ORDER BY `cprg` asc";
    $resulset = DB_query($sql, $db, $ErrMsg);
    while ($rs = DB_fetch_array($resulset)) {
        $contenido[] = [ 'val'=>$rs['val'], 'text'=>$rs['text'] ];
    }
    $result = true;
}

/**
 * Consulta de los datos para el elemento select de actividades institucionales
 * @date:26.03.18
 * @author: Desarrollo
 */
if ($option == 'muestraActividadInstitusional') {
    unset($contenido);
    unset($result);
    unset($sql);
    unset($ErrMsg);
    unset($resulset);
    unset($condicion);
    $contenido = [];
    $condicion='';
    $ErrMsg = "No se obtuvo datos de las actividades institucionales";

    $condicion .= (!empty($_POST['ra'])? " AND cve_ramo = {$_POST['ra']}" :'');

    $sql = "SELECT `cain` as val, CONCAT(`cain`,' - ',`descripcion`) as text FROM `tb_cat_actividad_institucional` WHERE `activo` = 1 ".$condicion." ORDER BY `cain` asc";
    $resulset = DB_query($sql, $db, $ErrMsg);
    while ($rs = DB_fetch_array($resulset)) {
        $contenido[] = [ 'val'=>$rs['val'], 'text'=>$rs['text'] ];
    }
    $result = true;
}

/**
 * Consulta de los datos para el elemento select del programa presupuestario
 * @date:26.03.18
 * @author: Desarrollo
 */
if ($option == 'muestraProgramaPresupuestario') {
    unset($contenido);
    unset($result);
    unset($sql);
    unset($ErrMsg);
    unset($resulset);
    unset($condicion);
    $contenido = [];
    $condicion='';
    $ErrMsg = "No se obtuvo datos del programa presupuestario";

    $condicion .= (!empty($_POST['ra'])? " AND cve_ramo = {$_POST['ra']}" :'');

        $sql = "SELECT cpp.`cppt` as val, CONCAT(cpp.`cppt`,' - ',cpp.`descripcion`) as text
            FROM `tb_cat_programa_presupuestario` as cpp
            INNER JOIN g_cat_ramo as cr on cpp.`cve_ramo` = cr.`cve_ramo`
            WHERE `activo` = 1 ".$condicion." ORDER BY cpp.`cppt` asc";
    $resulset = DB_query($sql, $db, $ErrMsg);
    while ($rs = DB_fetch_array($resulset)) {
        $contenido[] = [ 'val'=>$rs['val'], 'text'=>$rs['text'] ];
    }
    $result = true;
}

/**
 * Consulta de los datos para el elemento select del programa presupuestario
 * @date:26.03.18
 * @author: Desarrollo
 */
if ($option == 'muestraProgramaExtraPresupuestario') {
    unset($contenido);
    unset($result);
    unset($sql);
    unset($ErrMsg);
    unset($resulset);
    unset($condicion);
    $contenido = [];
    $condicion='';
    $ErrMsg = "No se obtuvo datos del programa presupuestario";

    $condicion .= (!empty($_POST['ra'])? " AND cve_ramo = {$_POST['ra']}" :'');

    $sql = "SELECT `cpp`.`cppt` AS val, CONCAT(`cpp`.`cppt`,' - ',`cpp`.`descripcion`) AS `text`
                FROM `tb_cat_programa_presupuestario` AS `cpp`
                INNER JOIN `g_cat_ramo` AS cr on `cpp`.`cve_ramo` = `cr`.`cve_ramo`

                WHERE `activo` = 1
                $condicion
            UNION
                SELECT `pe` AS `val`, CONCAT(`pe`, ' - ', `descripcion`) AS `text`

                FROM `tb_cat_programa_extrapresupuestario`
                WHERE `activo` = '1'
            ORDER BY `val` asc";
    $resulset = DB_query($sql, $db, $ErrMsg);
    while ($rs = DB_fetch_array($resulset)) {
        $contenido[] = [ 'val'=>$rs['val'], 'text'=>$rs['text'] ];
    }
    $result = true;
}

/**
 * Consulta de los datos para el elemento select del componente presupuestario
 * @date:26.03.18
 * @author: Desarrollo
 */
if ($option == 'muestraComponentePresupuestario') {
    unset($contenido);
    unset($result);
    unset($sql);
    unset($ErrMsg);
    unset($resulset);
    unset($condicion);
    $contenido = [];
    $condicion='';
    $ErrMsg = "No se obtuvo datos del componente presupuestario";

    $sql = "SELECT `cp` as val, CONCAT(`cp`,' - ',`descripcion`) as text FROM `tb_cat_componente_presupuestario` WHERE `activo` = 1 ORDER BY `cp` asc";
    $resulset = DB_query($sql, $db, $ErrMsg);
    while ($rs = DB_fetch_array($resulset)) {
        $contenido[] = [ 'val'=>$rs['val'], 'text'=>$rs['text'] ];
    }
    $result = true;
}

/**
 * Consulta de los datos para el elemento select del componente partida especifica
 * @date:29.03.18
 * @author: Desarrollo
 */
if ($option == 'muestraComponentePartidaEspesifica') {
    unset($contenido);
    unset($result);
    unset($sql);
    unset($ErrMsg);
    unset($resulset);
    unset($condicion);
    $contenido = [];
    $condicion='';
    $ErrMsg = "No se obtuvo datos del componente partida espesifica";

    $sql = "SELECT partidacalculada as val, CONCAT(partidacalculada,' - ',descripcion) as text 
        FROM tb_cat_partidaspresupuestales_partidaespecifica WHERE `activo` = '1' ORDER BY `partidacalculada` asc";
    $resulset = DB_query($sql, $db, $ErrMsg);
    while ($rs = DB_fetch_array($resulset)) {
        $contenido[] = [ 'val'=>$rs['val'], 'text'=>$rs['text'] ];
    }
    $result = true;
}

/**
 * Consulta de los datos para el elemento select del componente tipo de gasto
 * @date:29.03.18
 * @author: Desarrollo
 */
if ($option == 'muestraComponenteTipoGasto') {
    unset($contenido);
    unset($result);
    unset($sql);
    unset($ErrMsg);
    unset($resulset);
    unset($condicion);
    $contenido = [];
    $condicion='';
    $ErrMsg = "No se obtuvo datos del componente tipo de gasto";

    $sql = "SELECT `ctga` as val, CONCAT(`ctga`,' - ',`descripcion`) as text 
        FROM g_cat_tipo_de_gasto WHERE `activo` = 'S' ORDER BY `ctga` asc";
    $resulset = DB_query($sql, $db, $ErrMsg);
    while ($rs = DB_fetch_array($resulset)) {
        $contenido[] = [ 'val'=>$rs['val'], 'text'=>$rs['text'] ];
    }
    $result = true;
}

/**
 * Consulta de los datos para el elemento select del componente tipo de gasto
 * @date:29.03.18
 * @author: Desarrollo
 */
if ($option == 'muestraComponenteFuenteFinanciamiento') {
    unset($contenido);
    unset($result);
    unset($sql);
    unset($ErrMsg);
    unset($resulset);
    unset($condicion);
    $contenido = [];
    $condicion='';
    $ErrMsg = "No se obtuvo datos del componente tipo de gasto";

    $sql = "SELECT `cfin` as val, CONCAT(`cfin`,' - ',`descripcion`) as text 
        FROM g_cat_fuente_de_financiamiento WHERE `activo` = 'S' ORDER BY `cfin` asc";
    $resulset = DB_query($sql, $db, $ErrMsg);
    while ($rs = DB_fetch_array($resulset)) {
        $contenido[] = [ 'val'=>$rs['val'], 'text'=>$rs['text'] ];
    }
    $result = true;
}

/**
 * Consulta de los datos para el elemento select del componente Entidades federativas
 * @date:29.03.18
 * @author: Desarrollo
 */
if ($option == 'muestraComponenteEntidadesFederativas') {
    unset($contenido);
    unset($result);
    unset($sql);
    unset($ErrMsg);
    unset($resulset);
    unset($condicion);
    $contenido = [];
    $condicion='';
    $ErrMsg = "No se obtuvo datos del componente entidades federativas";

    $sql = "SELECT `cg` as val, CONCAT(`cg`,' - ',`descripcion`) as text FROM g_cat_geografico WHERE `activo` = 'S' ORDER BY `cg` asc";
    $resulset = DB_query($sql, $db, $ErrMsg);
    while ($rs = DB_fetch_array($resulset)) {
        $contenido[] = [ 'val'=>$rs['val'], 'text'=>$rs['text'] ];
    }
    $result = true;
}

/**
 * Consulta de los datos para el elemento select del componente programa presupuestario
 * @date:29.03.18
 * @author: Desarrollo
 */
if ($option == 'muestraComponenteProgramaPresupuestario') {
    unset($contenido);
    unset($result);
    unset($sql);
    unset($ErrMsg);
    unset($resulset);
    unset($condicion);
    $contenido = [];
    $condicion='';
    $ErrMsg = "No se obtuvo datos del componente program presupuestario";

    $sql = "SELECT `pyin` as val, CONCAT(`pyin`,' - ',`nomb`) as text FROM g_cat_ppi WHERE `activo` = 'S' ORDER BY `pyin` asc";
    $resulset = DB_query($sql, $db, $ErrMsg);
    while ($rs = DB_fetch_array($resulset)) {
        $contenido[] = [ 'val'=>$rs['val'], 'text'=>$rs['text'] ];
    }
    $result = true;
}

/**
 * Consulta de los datos para el elemento select del componente programa presupuestario
 * @date:05.04.18
 * @author: Desarrollo
 */
if ($option == 'muestraAnexoUno') {
    unset($contenido);
    unset($result);
    unset($sql);
    unset($ErrMsg);
    unset($resulset);
    unset($condicion);
    $contenido = [];
    $condicion='';
    $ErrMsg = "No se obtuvo datos del componente program presupuestario";

    $condicion .= (!empty($_POST['ur'])?" AND `ur` = '".$_POST['ur']."' " : '');

    $sql = "SELECT `ln_aux1` as val, CONCAT(`ln_aux1`,'-',`desc_ue`) as text 
        FROM tb_cat_unidades_ejecutoras WHERE active = 1 $condicion order by `ln_aux1` asc";
    $resulset = DB_query($sql, $db, $ErrMsg);
    while ($rs = DB_fetch_array($resulset)) {
        $contenido[] = [ 'val'=>$rs['val'], 'text'=>$rs['text'] ];
    }
    $result = true;
}

/**
 * Consulta de la descripción de la partida genérica
 * @date:11.04.18
 * @author: Desarrollo
 */
if ($option == 'muestraDesPartidaGenerica') {
    $partidaGenerica = $_POST['partidaGenerica'];
    $descripcion = '';
    $SQL = "SELECT descripcion FROM tb_cat_partidaspresupuestales_partidagenerica 
    WHERE pargcalculado = '".$partidaGenerica."'";
    $ErrMsg = "No se obtener la descripción de la partida genérica";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $descripcion = $row ['descripcion'];
        }
        $result = true;
    } else {
        $result = false;
    } 
    $contenido = $descripcion;
}

if ($option == 'muestraDesCRI') {
    $claveCri = $_POST['claveCri'];
    $descripcion = '';
    $SQL = "SELECT descripcion FROM clasificador_ingreso 
    WHERE rtc = '".$claveCri."'";
    $ErrMsg = "No se obtener la descripción del Clasificador de Rubro Ingreso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $descripcion = $row ['descripcion'];
        }
        $result = true;
    } else {
        $result = false;
    }
    $contenido = $descripcion;
}

if ($option == 'muestraDescripcionProducto') {
    $idProducto = $_POST['idProducto'];
    $descripcion = '';
    $SQL = "SELECT Descripcion FROM sat_stock 
    WHERE c_ClaveProdServ = '".$idProducto."'";
    $ErrMsg = "No se obtener la descripción del producto";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $descripcion = $row ['Descripcion'];
        }
        $result = true;
    } else {
        $result = false;
    }
    $contenido = $descripcion;
}

/**
 * Consulta para obtener a los empleados
 * @date:09.05.18
 * @author: Desarrollo
 */
if ($option == 'muestraEmpleados') {
    $SQL = "SELECT id_nu_empleado, concat(ln_nombre,' ', sn_primer_apellido,' ',sn_segundo_apellido) AS empleado FROM tb_empleados WHERE 1=1 ";
    
    if (isset($_POST['ur'])) {
        //hay que mandar con la separacion con comas si son mas de uno
        $datosUR = $_POST["ur"];
    }

    if (!empty($datosUR)) {
        $SQL .= " AND tagref IN (".$datosUR.")";
    } else {
        $SQL .= " AND tagref IN (SELECT tagref FROM sec_unegsxuser WHERE userid= '".$_SESSION["UserID"]."') ";
    }

    if (isset($_POST['ue'])) {
        //hay que mandar con la separacion con comas si son mas de uno
        $datosUE = $_POST["ue"];
    }

    if (!empty($datosUE)) {
        $SQL .= " AND ue IN (".$datosUE.")";
    }

    $SQL .= " ORDER BY concat(ln_nombre,' ', sn_primer_apellido,' ',sn_segundo_apellido) ";

    //echo "sql:".$SQL;
    $ErrMsg = "No se obtener la descripción de la partida genérica";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if ($TransResult) {
        $result = true;
    } else {
        $result = false;
    }

    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'val'=>$row['id_nu_empleado'], 'text'=>$row['empleado']];
        }
        $result = true;
    }
}

/**
 * Consulta para obtener los patrimonios disponibles
 * @date:11.04.18
 * @author: Desarrollo
 */
if ($option == 'muestraPatrimonio') {
    $SQL = "SELECT assetid,CONCAT(barcode,' - ' ,description) AS patrimonio FROM fixedassets WHERE active = 1 AND status=1 ";

    if (isset($_POST['ur'])) {
        //hay que mandar con la separacion con comas si son mas de uno
        $datosUR = $_POST["ur"];
    }

    if (!empty($datosUR)) {
        $SQL .= " AND fixedassets.tagrefowner IN (".$datosUR.")";
    } else {
        $SQL .= " AND fixedassets.tagrefowner IN (SELECT tagref FROM sec_unegsxuser WHERE userid= '".$_SESSION["UserID"]."') ";
    }

    if (isset($_POST['ue'])) {
        //hay que mandar con la separacion con comas si son mas de uno
        $datosUE = $_POST["ue"];
    }

    if (!empty($datosUE)) {
        $SQL .= " AND fixedassets.ue IN (".$datosUE.")";
    }
    //echo "sql:".$SQL;
    $ErrMsg = "No se obtener la descripción de la partida genérica";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'val'=>$row['assetid'], 'text'=>$row['patrimonio']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

/**
 * Consulta para obtener los patrimonios general
 * @date:11.04.18
 * @author: Desarrollo
 */

if ($option == 'muestraPatrimonio_General') {
    $SQL = "SELECT assetid,CONCAT(barcode,' - ' ,description) AS patrimonio FROM fixedassets WHERE active = 1   ";

    if (isset($_POST['ur'])) {
        //hay que mandar con la separacion con comas si son mas de uno
        $datosUR = $_POST["ur"];
    }

    if (!empty($datosUR)) {
        $SQL .= " AND fixedassets.tagrefowner IN (".$datosUR.")";
    } else {
        $SQL .= " AND fixedassets.tagrefowner IN (SELECT tagref FROM sec_unegsxuser WHERE userid= '".$_SESSION["UserID"]."') ";
    }

    $ErrMsg = "No se obtener la descripción de la partida genérica";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if ($TransResult) {
        $result = true;
    } else {
        $result = false;
    }

    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'val'=>$row['assetid'], 'text'=>$row['patrimonio']];
        }
        $result = true;
    }
}

/**
 * Consulta para obtener los estatus de patrimonio
 * @date: 08.04.18
 * @author: Desarrollo
 */
if ($option == 'muestraActivoEspecifico') {
    $SQL = "SELECT fixedassets.assetid,
                    fixedassets.barcode,
                    fixedassets.description,  
                    fixedassets.status,  
                    fixedassets.tipo_bien,  
                    fixedAssetCategoryBien.`description` as tipoBien  
            FROM fixedassets 
            LEFT JOIN fixedAssetCategoryBien ON fixedassets.`tipo_bien` = fixedAssetCategoryBien.`id`
            WHERE assetid = ".$_POST['assetid'].";";
    
    $ErrMsg = "No se obtener la descripción deL activo especifico";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'id'=>$row['assetid'], 'descripcion'=>$row['description'], 'idtipoBien' => $row['tipo_bien'], 'tipoBien' => $row['tipoBien']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

/**
 * Consulta para obtener los estatus de patrimonio
 * @date: 23.05.18
 * @author: Desarrollo
 */
if ($option == 'existeEnResguardo') {
    $SQL = "SELECT *  FROM fixedasset_detalle_resguardos WHERE estatus='1' and assetid='".$_POST['assetid']."';";
    
    $ErrMsg = "No se obtener la descripción deL activo especifico";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'existe'=>'1'];
        }
        $result = true;
    } else {
        $result = false;
    }
}

/**
 * Consulta para obtener los estatus de patrimonio
 * @date: 08.04.18
 * @author: Desarrollo
 */
if ($option == 'muestraEstatusPatrimonio') {
    $SQL = "SELECT fixedassetstatusid,fixedassetstatus AS estatus FROM fixedassetstatus  WHERE activo = 1 ORDER BY fixedassetstatus;";
    
    $ErrMsg = "No se obtener la descripción de la partida genérica";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'val'=>$row['fixedassetstatusid'], 'text'=>$row['estatus']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

/**
 * Consulta para obtener configuracion de los componentes de la clave presupeustal
 * @date:04.05.18
 * @author: Desarrollo
 */
if ($option == 'datosConfiguracionClave') {
    $idClavePresupuesto = $_POST['idClavePresupuesto'];
    $info = array();
    $SQL = "SELECT 
            campoPresupuesto,
            tabla,
            campo,
            nombre,
            txt_sql_nueva,
            nu_tam_est_ejer
            FROM budgetConfigClave 
            WHERE idClavePresupuesto = '".$idClavePresupuesto."' ORDER BY orden ASC ";
    $ErrMsg = "No se obtuvo la configuración del presupuesto detallada";
    $transResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($transResult)) {
        $infoSelect = array();

        if ($myrow ['campoPresupuesto'] == 'anho') {
            $infoSelect[] = array( 'value' => date('Y'), 'texto' => '' );
        } else {
            //$SQL = "SELECT ".$myrow ['campo']." FROM ".$myrow ['tabla']." GROUP BY ".$myrow ['campo']." ORDER BY ".$myrow ['campo'];
            if (!empty(trim($myrow ['txt_sql_nueva']))) {
                $SQL = $myrow ['txt_sql_nueva'];
                $ErrMsg = "No se obtuvo la configuración de ".$myrow ['nombre']." ";
                $transResultConfig = DB_query($SQL, $db, $ErrMsg);
                while ($myrowConfig = DB_fetch_array($transResultConfig)) {
                    //$infoSelect[] = array( 'value' => $myrowConfig [$myrow ['campo']], 'texto' => $myrowConfig [$myrow ['campo']] );
                    $infoSelect[] = array( 'value' => $myrowConfig ['value'], 'texto' => $myrowConfig ['texto'] );
                }
            } else {
                $infoSelect[] = array( 'value' => '', 'texto' => 'Sin Configuración' );
            }
        }

        $info[] = array( 'campoPresupuesto' => $myrow ['campoPresupuesto'], 'tabla' => $myrow ['tabla'], 'campo' => $myrow ['campo'], 'nombre' => $myrow ['nombre'], 'tamEstEjercicio' => $myrow ['nu_tam_est_ejer'], 'infoSelect' => $infoSelect );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

/**
 * Consulta para obtener partidas especficas para el activo fijo
 * @date:07.05.18
 * @author: Desarrollo
 */
if ($option == 'muestraCategoriaActivo') {
    // $SQL = "SELECT categoryid , CONCAT(categoryid,' - ',categorydescription) AS categorydescriptionname
    //         FROM fixedassetcategories
    //         ORDER BY categorydescription;";

    $SQL = "SELECT DISTINCT p_especificas.partidacalculada, CONCAT(p_especificas.partidacalculada,' - ',p_especificas.descripcion) as descripcion 
            FROM  tb_cat_partidaspresupuestales_partidaespecifica p_especificas 
            WHERE p_especificas.ccap = 5 and concat(ccap,ccon,cparg) IN (SELECT DISTINCT categoryid FROM fixedassetcategories)  ORDER BY p_especificas.partidacalculada;";
    
    $ErrMsg = "No se obtener la descripción de la partida genérica";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'val'=>$row['partidacalculada'], 'text'=>$row['descripcion']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

if ($option == 'llenarSelect') {
    $SQL=$_POST['strSQL'];
    
    $ErrMsg = "No se obtener la descripción de la partida genérica";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'val'=>$row['valor'], 'text'=>$row['descripcion']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

// opcion para traer listado de productos para lista dinamica
if ($option == 'traeProductosBusqueda') {
    try {
        $consulta="SELECT DISTINCT CONCAT(stockmaster.stockid, ' | ', stockmaster.description) AS articulo, 
                tb_partida_articulo.partidaEspecifica, stockmaster.categoryid,
                stockmaster.stockid,
                stockmaster.description,
                stockmaster.units  
                FROM tb_partida_articulo 
                INNER JOIN stockmaster ON  tb_partida_articulo.eq_stockid=stockmaster.eq_stockid 
                WHERE partidaEspecifica NOT LIKE '5%'
                AND stockmaster.mbflag='B'
                ORDER BY partidaEspecifica, stockmaster.stockid";

        $resultado = DB_query($consulta, $db);

        while ($registro = DB_fetch_array($resultado)) {
            $contenido[] = [
                'articulo'=> $registro['articulo'],
                'partida'=>$registro['partidaEspecifica'],
                'clave'=>$registro['stockid'] ,
                'descrip'=>$registro['description'],
                'unidad'=>$registro['units']
            ];
        }
         $result = true;
    } catch (Exception $e) {
        $ErrorMsg= $e->getMessage();
        DB_Txn_Rollback($db);
    }
}

/*
 * Consulta para obtener los estatus configurado para la funcionalidad de Radicación y Ministración
 * @date:18.06.17
 * @author: Desarrollo
*/

if ($option == 'muestraEstatusRadicado') {
    $SQL = "SELECT id, estatus FROM tb_estatus_radicados WHERE activo = 1;";

    $ErrMsg = "No se obtuvo estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'val'=>$row['id'], 'text'=>$row['estatus']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

/*
 * Consulta que trae los tipos de proveedores
 * @date:28.06.18
 * @author: Desarrollo
*/

if ($option == 'traeTiposProveedores') {
    $SQL = "SELECT typeid, typename FROM supplierstype;";

    $ErrMsg = "No se obtuvo tipos de proveedor";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'id'=>$row['typeid'], 'nombretipo'=>$row['typename']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

/*
 * Consulta que trae los estatus de ministracion
 * @date:28.06.17
 * @author: Desarrollo
*/

if ($option == 'obtenerEstatusMinistracion') {
    $SQL = "SELECT id, estatus FROM tb_estatus_ministracion;";

    $ErrMsg = "No se obtuvo los estatus de ministracion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'id'=>$row['id'], 'estatus'=>$row['estatus']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

/*
 * Consulta que trae información para mostrar en una lista
 * @date:02.08.18
 * @author: Desarrollo
*/
if ($option == 'obtenerInfoListaGeneral') {
    $tipo = $_POST['tipo'];
    $info = array();

    if ($tipo == 'proveedor') {
        // Obtener proveedores activos del sistema
        $SQL = "SELECT CONCAT(supplierid, ' - ', suppname) as value, CONCAT(supplierid, ' - ', suppname) as texto 
        FROM suppliers WHERE active = 1";
        $ErrMsg = "No se obtuvieron los Proveedores";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $datos['value'] = $myrow ['value'];
            $datos['texto'] = $myrow ['texto'];

            $info[] = $datos;
        }
    }

    if (count($info) == 0) {
        // Si no tiene proveedores
        $datos['value'] = '';
        $datos['texto'] = 'Sin Información';
        $info[] = $datos;
    }

    $contenido = array('datos' => $info);
    $result = true;
}

/*
 * Consulta que trae los estatus de ministracion
 * @date:28.06.17
 * @author: Desarrollo
*/

if ($option == 'obtenerCuentasBancos') {
    $SQL = "SELECT accountcode as id, bankaccountname as descripcion FROM bankaccounts;";

    $ErrMsg = "No se obtuvo los estatus de ministracion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'id'=>$row['id'], 'descripcion'=>$row['descripcion']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

/*
 * Consulta que trae los tipos de clc del ministrado 
 * @date:28.06.17
 * @author: Desarrollo
*/

if ($option == 'obtenerTipoCLC') {

    $SQL = "SELECT id, descripcion FROM clc_Ministracion WHERE activo=1;";

    $ErrMsg = "No se obtuvo los tipos de clc de ministracion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'value'=>$row['id'], 'label'=>$row['descripcion'], 'title'=>$row['descripcion']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

/**
* Combo de beneficiarios para el ministrado
* @date: 28.06.17
* @author: Desarrollo
**/

if ($option == 'obtenerBeneficiario') {

    $SQL = "SELECT id,nombre_cuenta FROM tb_beneficiario_concentradora WHERE `ur`='".$_POST['ur']."' AND `ue`='' and activo=1 and tipo=1;";

    $ErrMsg = "No se obtuvo los tipos de clc de ministracion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'id'=>$row['id'], 'descripcion'=>$row['nombre_cuenta']];
        }
        $result = true;
    } else {
        $result = false;
    }
}


/**
* Combo de beneficiarios para el ministrado
* @date: 28.06.17
* @author: Desarrollo
**/

if ($option == 'obtenerBeneficiarioRadicado') {

    $SQL = "SELECT id,nombre_cuenta FROM tb_beneficiario_concentradora WHERE `ur`='".$_POST['ur']."' AND `ue`='".$_POST['ue']."' and activo=1 and tipo=1;";

    $ErrMsg = "No se obtuvo los tipos de clc de ministracion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'id'=>$row['id'], 'descripcion'=>$row['nombre_cuenta']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

/**
* Combo de cuenta concentradora para el ministrado
* @date: 28.06.17
* @author: Desarrollo
**/

if ($option == 'obtenerClabeConcentradora') {

    $arrCapitulos = explode(",", $_POST['capitulos']);
    $arrIdentificador = explode(",", $_POST['identificador']);

    $str="";
    
    for ($i=0; $i < count($arrIdentificador) ; $i++) { 
        //Tipo 1 para capitulos con una cuenta concentradora
        //Tipo 2 para capitulos con mas de una cuenta concentradora 
        if($arrIdentificador[$i] =="1"){
            $str=" OR tipodefault=1 ";
        }
    }

    $SQL = "SELECT DISTINCT id,cuenta 
            FROM tb_beneficiario_concentradora 
            WHERE `ur`='".$_POST['ur']."' AND `ue`='' and activo=1 and tipo =2 
            and (ln_capitulo in (".$_POST['capitulos'].") ".$str.") and identificador in (".$_POST['identificador'].") ;";

    $ErrMsg = "No se obtuvo los tipos de clc de ministracion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'id'=>$row['id'], 'descripcion'=>$row['cuenta']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

if ($option == 'obtenerClabeConcentradoraRadicado') {

    $arrCapitulos = explode(",", $_POST['capitulos']);
    $arrIdentificador = explode(",", $_POST['identificador']);

    $str="";
    
    for ($i=0; $i < count($arrIdentificador) ; $i++) { 
        //Tipo 1 para capitulos con una cuenta concentradora
        //Tipo 2 para capitulos con mas de una cuenta concentradora 
        if($arrIdentificador[$i] =="1"){
            $str=" OR tipodefault=1 ";
        }
    }

    $SQL = "SELECT DISTINCT id,cuenta 
            FROM tb_beneficiario_concentradora 
            WHERE `ur`='".$_POST['ur']."' AND `ue`='".$_POST['ue']."' and activo=1 and tipo =2 
            and (ln_capitulo in (".$_POST['capitulos'].") ".$str.") and identificador in (".$_POST['identificador'].") ;";

    $ErrMsg = "No se obtuvo los tipos de clc de ministracion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'id'=>$row['id'], 'descripcion'=>$row['cuenta']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

if ($option == 'obtenerInfoBeneficiario') {

    $SQL = "SELECT id,nombre,rfc,cuenta FROM tb_beneficiario_concentradora WHERE id=".$_POST['idBeneficiario'].";";

    $ErrMsg = "No se obtuvo los tipos de clc de ministracion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'id'=>$row['id'], 'descripcion'=>$row['nombre'], 'rfc'=>$row['rfc'], 'cuenta'=>$row['cuenta']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

if ($option == 'mostrarCapitulosMinistracion') {
    $info = array();
    $SQL = "SELECT ccap, CONCAT(ccapmiles,' - ',descripcion) as name,coalesce(tb_bc.identificador,1) as identificador 
            FROM tb_cat_partidaspresupuestales_capitulo
            LEFT JOIN tb_beneficiario_concentradora tb_bc ON tb_cat_partidaspresupuestales_capitulo.ccap= tb_bc.ln_capitulo";
    $ErrMsg = "No se obtuvieron los capitulos de las partidas presupuestales";

    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'capituloid' => $myrow ['ccap'], 'capituloname' => $myrow ['name'],'identificador' => $myrow ['identificador'] );
    }
    //echo "aqui";

    $contenido = array('datos' => $info);
    //print_r($contenido);
    $result = true;
}

if ($option == 'obtenerFirmantesMinistracion') {
    $info = array();
    $SQL = "SELECT 
                tb_detalle_firmas.id_nu_detalle_firmas as id,CONCAT(tb_detalle_firmas.titulo,' ',tb_empleados.ln_nombre,' ',tb_empleados.sn_primer_apellido ,' ',tb_empleados.sn_segundo_apellido) as firmante,
                tb_detalle_firmas.informacion,
                tb_reporte_firmas.id_dafault
            FROM tb_cat_reportes_conac tb_cat_firmas
            LEFT JOIN tb_reportes_conac_firmas tb_conf_firmas on tb_cat_firmas.id_nu_reportes_conac = tb_conf_firmas.id_nu_reportes_conac and tb_conf_firmas.ur='".$_POST['ur']."'
            LEFT JOIN tb_reporte_firmas on tb_conf_firmas.id_nu_reportes_conac_firmas = tb_reporte_firmas.id_nu_reportes_conac_firmas
            LEFT JOIN tb_detalle_firmas on tb_reporte_firmas.id_nu_detalle_firmas  = tb_detalle_firmas.id_nu_detalle_firmas
            LEFT JOIN tb_empleados ON tb_detalle_firmas.id_nu_empleado = tb_empleados.id_nu_empleado
            WHERE sn_tipo = 'ministracion';";
    $ErrMsg = "No se obtuvieron los capitulos de las partidas presupuestales";
    //echo $SQL;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'id' => $myrow ['id'], 'descripcion' => $myrow ['firmante'],'default' => $myrow['id_dafault']);
    }
    //echo "aqui";

    $contenido = array('datos' => $info);
    //print_r($contenido);
    $result = true;
}

if ($option == 'obtenerFirmantesRadicacion') {
    $info = array();
    $SQL = "SELECT 
                tb_detalle_firmas.id_nu_detalle_firmas as id,CONCAT(tb_detalle_firmas.titulo,' ',tb_empleados.ln_nombre,' ',tb_empleados.sn_primer_apellido ,' ',tb_empleados.sn_segundo_apellido) as firmante,
                tb_detalle_firmas.informacion,
                tb_reporte_firmas.id_dafault
            FROM tb_cat_reportes_conac tb_cat_firmas
            LEFT JOIN tb_reportes_conac_firmas tb_conf_firmas on tb_cat_firmas.id_nu_reportes_conac = tb_conf_firmas.id_nu_reportes_conac and tb_conf_firmas.ur='".$_POST['ur']."' and tb_conf_firmas.ue='".$_POST['ue']."' 
            LEFT JOIN tb_reporte_firmas on tb_conf_firmas.id_nu_reportes_conac_firmas = tb_reporte_firmas.id_nu_reportes_conac_firmas
            LEFT JOIN tb_detalle_firmas on tb_reporte_firmas.id_nu_detalle_firmas  = tb_detalle_firmas.id_nu_detalle_firmas
            LEFT JOIN tb_empleados ON tb_detalle_firmas.id_nu_empleado = tb_empleados.id_nu_empleado
            WHERE sn_tipo = 'radicacion';";
    $ErrMsg = "No se obtuvieron los capitulos de las partidas presupuestales";
    //echo $SQL;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'id' => $myrow ['id'], 'descripcion' => $myrow ['firmante'],'default' => $myrow['id_dafault']);
    }
    //echo "aqui";

    $contenido = array('datos' => $info);
    //print_r($contenido);
    $result = true;
}

/**
* Combo de tipo de bien patrimonio
* @date: 28.06.17
* @author: Desarrollo
**/
if ($option == 'obtenerTipoBien') {

    $SQL = "SELECT id, description FROM fixedAssetCategoryBien WHERE active = 1;";

    $ErrMsg = "No se obtuvo los tipos de bien";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'id'=>$row['id'], 'descripcion'=>$row['description']];
        }
        $result = true;
    } else {
        $result = false;
    }
}


/**
* Combo de disposicion final patrimonio
* @date: 28.06.17
* @author: Desarrollo
**/

if ($option == 'obtenerDisposicionFinal') {

    $SQL = "SELECT fixedassetstatusid, fixedassetstatus as descripcion FROM fixedassetstatus WHERE desincorporacion ='1' AND activo = 1;";

    $ErrMsg = "No se obtuvo los tipos de bien";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'id'=>$row['fixedassetstatusid'], 'descripcion'=>$row['descripcion']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

/**
* Combo de tipo de bien propietario
* @date: 29.06.17
* @author: Desarrollo
**/
if ($option == 'obtenerTipoPropietario') {

    $SQL = "SELECT id, description FROM fixedAssetOwnerType WHERE active = 1;";

    $ErrMsg = "No se obtuvo los tipos de propietario";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'id'=>$row['id'], 'descripcion'=>$row['description']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

/**
* Combo de estatus de poliza
* @date: 29.06.17
* @author: Desarrollo
**/
if ($option == 'obtenerTipoPoliza') {

    $SQL = "SELECT id, description FROM tb_fixedassetEstatusPoliza WHERE active = 1;";

    $ErrMsg = "No se obtuvo los estatus de poliza";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'id'=>$row['id'], 'descripcion'=>$row['description']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

/**
* Combo de estatus de poliza
* @date: 29.06.17
* @author: Desarrollo
**/
if ($option == 'obtenerEstatusMantenimiento') {

    $SQL = "SELECT id, description FROM tb_fixedassetmaintenance_status WHERE active = 1;";

    $ErrMsg = "No se obtuvo los estatus de mantenimiento patrimonio";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'id'=>$row['id'], 'descripcion'=>$row['description']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

/**
* Combo de estatus de poliza
* @date: 29.06.17
* @author: Desarrollo
**/
if ($option == 'obtenerTipoMantenimiento') {

    $SQL = "SELECT id, description FROM tb_fixedassetmaintenance_types WHERE active = 1;";

    $ErrMsg = "No se obtuvo los tipo de mantenimiento patrimonio";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'id'=>$row['id'], 'descripcion'=>$row['description']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

/**
* Combo de estatus de resguardo
* @date: 29.06.17
* @author: Desarrollo
**/
if ($option == 'obtenerEstatusResguardo') {

    $SQL = "SELECT id, description FROM tb_resguardo_status WHERE active = 1;";

    $ErrMsg = "No se obtuvo los tipo de mantenimiento patrimonio";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'id'=>$row['id'], 'descripcion'=>$row['description']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

/**
* Combo de estatus de resguardo
* @date: 29.06.17
* @author: Desarrollo
**/
if ($option == 'obtenerMesesUltimoRadicado') {

    $SQL = "SELECT coalesce(max(ln_mes),0) as mes FROM tb_radicacion WHERE  YEAR(fecha_elab) = YEAR(curdate()) and  estatus != 6 and ln_ur = '".$_POST['ur']."' and ln_ue='".$_POST['ue']."';";

    $ErrMsg = "No se obtuvo los tipo de mantenimiento patrimonio";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'mes'=>$row['mes']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

/**
* Combo de perfiles de usuario
* @date: 29.06.17
* @author: Desarrollo
**/
if ($option == 'obtenerPerfilUsuario') {

    $SQL = "SELECT profileid, name FROM sec_profiles WHERE active = 1 AND name !='';";

    $ErrMsg = "No se obtuvo los perfiles de usuario";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'id'=>$row['profileid'], 'descripcion'=>$row['name']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

/**
* Combo de estatus de usuario
* @date: 29.06.17
* @author: Desarrollo
***/
if ($option == 'obtenerEstatusUsuario') {

    $SQL = "SELECT id, descripcion FROM tb_www_user_estatus WHERE active = 1;";

    $ErrMsg = "No se obtuvo los estatus de usuario";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            $contenido[] = [ 'id'=>$row['id'], 'descripcion'=>$row['descripcion']];
        }
        $result = true;
    } else {
        $result = false;
    }
}

if($option=='uePorUsuario'){
    $sql = "SELECT `ue`

            FROM `tb_sec_users_ue`

            WHERE `userid` LIKE '$_SESSION[UserID]'";

    $contenido = uePorUsuario($db,$sql);
    $result = $contenido['success'];
    $objetoJSONDirecto = true;
}

if($option=='datosListaCuentasTotales'){
    $sql = "SELECT DISTINCT `cm`.`accountcode` AS `valor`, `cm`.`accountname` AS `etiqueta`, cm.`nu_nivel` AS `nivel`

            FROM `chartmaster` AS `cm`
            JOIN `accountgroups` ON `cm`.`group_` = `accountgroups`.`groupname`

            WHERE `cm`.`nu_nivel` <= 5

            ORDER BY LENGTH(`cm`.`accountcode`) ASC, `cm`.`accountcode` ASC";

    $contenido = obtenDatosCuenta($db,$sql);
    $result = $contenido['success'];
    $objetoJSONDirecto = true;
}

if($option=='datosListaContribuyente'){
    $sql = "SELECT DISTINCT `cm`.`debtorno` AS `valor`, `cm`.`name` AS `etiqueta`, cm.`name3` AS `nivel`

    FROM `debtorsmaster` AS `cm`
    ORDER BY LENGTH(`cm`.`debtorno`) ASC;";

    $contenido = obtenDatosCuenta($db,$sql);
    $result = $contenido['success'];
    $objetoJSONDirecto = true;
}

if ($option == 'mostrarContribuyentes') {
    $info = array();
    $sql = "SELECT DISTINCT `cm`.`debtorno` AS `valor`, `cm`.`name` AS `etiqueta`, custbranch.taxid as rfc
    FROM `debtorsmaster` AS `cm`
    JOIN custbranch on custbranch.debtorno = cm.debtorno
    WHERE `cm`.`activo` = 1
    ORDER BY `cm`.`debtorno` ASC";

    $ErrMsg = "No se obtuvieron los Contribuyentes";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow['valor'].' - '.$myrow['etiqueta'].' - '.$myrow['rfc'], 'texto' => $myrow ['valor'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarConfiguracionDefault') {
    $info = array();
    // $SQL = "SELECT `cm`.`debtorno` AS `valor`, `cm`.`name` AS `etiqueta`, configContratos.id_contratos as contratoID, configContratos.enum_periodo as periodo
    // FROM tb_contratos_contribuyentes as configContratos 
    // JOIN sec_contratoxuser ON sec_contratoxuser.id_contratos = configContratos.id_contratos AND sec_contratoxuser.userid = '".$_SESSION['UserID']."'
       // JOIN `debtorsmaster` AS cm ON cm.debtorno = configContratos.debtorno;";
    $SQL = "SELECT DISTINCT `cm`.`debtorno` AS `valor`, `cm`.`name` AS `etiqueta`,  configContratos.tagref_default as tagref, configContratos.ln_un_default as ln_ue, configContratos.id_contratos as contratoID, configContratos.enum_periodo as periodo, configContratos.nu_val_atributo1
    FROM tb_contratos_contribuyentes as configContratos 
       JOIN `debtorsmaster` AS cm ON cm.debtorno = configContratos.debtorno
       WHERE configContratos.id_contratos = '".$_POST['confContrato_id']."'";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        // Obtener etiqueta para validacion
        $validarAtributo1Label = "";
        $validarAtributo1Id = "";
        if ($myrow ['nu_val_atributo1'] > 0) {
            $SQL = "SELECT 
            tb_cat_atributos_contrato.id_atributos,
            tb_cat_atributos_contrato.ln_etiqueta 
            FROM tb_cat_atributos_contrato 
            WHERE tb_cat_atributos_contrato.id_contratos = '".$myrow ['contratoID']."' 
            LIMIT 1 OFFSET ".($myrow ['nu_val_atributo1'] - 1);
            $resultEtiqueta = DB_query($SQL, $db, $ErrMsg);
            while ($myrowEtiqueta = DB_fetch_array($resultEtiqueta)) {
                $validarAtributo1Label = $myrowEtiqueta['ln_etiqueta'];
                $validarAtributo1Id = $myrowEtiqueta['id_atributos'];
            }
        }
        $info[] = array( 
            'value' => $myrow ['valor'], 
            'texto' => $myrow ['etiqueta'],
            'contratoID' => $myrow ['contratoID'],
            'periodo' => $myrow ['periodo'],
            'validarAtributo1' => $myrow ['nu_val_atributo1'],
            'validarAtributo1Label' => $validarAtributo1Label,
            'validarAtributo1Id' => $validarAtributo1Id,
            'tagref' => $myrow ['tagref'],
            'ln_ue' => $myrow ['ln_ue']
        );
    }
    $data['sql'] = $SQL;

    $contenido = array('datos' => $info);
    $result = true;
}


if($option=='datosListaCuentasBancarias'){
    $sql = "SELECT DISTINCT `bankaccounts`.`accountcode` AS `valor`,
            `bankaccounts`.`bankaccountname` AS `etiqueta`,
            `chartmaster`.`nu_nivel` AS `nivel`,
            `bankaccounts`.`currcode` AS `moneda`

            FROM `bankaccounts`, `chartmaster`, `tagsxbankaccounts`, `sec_unegsxuser`, `tb_sec_users_ue` AS `uu`

            WHERE `bankaccounts`.`accountcode`=`chartmaster`.`accountcode`
            AND `bankaccounts`.`accountcode` = `tagsxbankaccounts`.`accountcode`
            AND `tagsxbankaccounts`.`tagref` = `sec_unegsxuser`.`tagref`
            AND `sec_unegsxuser`.`userid` = '$_SESSION[UserID]'
            -- AND `uu`.`userid` LIKE '$_SESSION[UserID]'
            -- AND `uu`.`ue` LIKE `chartmaster`.`ln_clave`";
    
    $contenido = obtenDatosCuenta($db,$sql);
    $result = $contenido['success'];
    $objetoJSONDirecto = true;
}

if($option=='consultaDinamicaListadoCuentasTotales'){
    $info = $_POST;

    $condicionAdicional = ( $info['nivel']>5 ? "AND `uu`.`ue` LIKE `cm`.`ln_clave`" : "" );

    $sql = "SELECT DISTINCT `cm`.`accountcode` AS `valor`, `cm`.`accountname` AS `etiqueta`, cm.`nu_nivel` AS `nivel`

            FROM `chartmaster` AS `cm`
            JOIN `accountgroups` ON `cm`.`group_` = `accountgroups`.`groupname`
            JOIN `tb_sec_users_ue` AS `uu` ON `uu`.`userid` LIKE '$_SESSION[UserID]'

            WHERE `cm`.`accountcode` LIKE '$info[cuenta]%'
            AND `cm`.`nu_nivel` = '$info[nivel]'

            ORDER BY LENGTH(`cm`.`accountcode`) ASC, `cm`.`accountcode` ASC";
    
    $contenido = obtenCuentasAdicionales($db,$sql);
    $result = $contenido['success'];
    $objetoJSONDirecto = true;
}

if($option=='consultaDinamicaListadoCuentasBancarias'){
    $data = ['success'=>false, 'msg'=>'Ocurrió un incidente al obtener la información. Favor de contactar al administrador.'];

    $data['msg'] = 'No se encontraron cuentas adicionales.';

    $contenido = $data;
    $result = $contenido['success'];
    $objetoJSONDirecto = true;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);

/*if($_SESSION['UserID'] == "lcarcases") {
        var_export($dataObj);
} */

if($objetoJSONDirecto){
    header('Content-type:application/json;charset=utf-8');
    echo json_encode($contenido);
}else{
    header('Content-type: text/html; charset=ISO-8859-1');
    echo json_encode($dataObj);
}

function uePorUsuario($db,$sql){
    $data = ['success'=>false, 'msg'=>'Ocurrió un incidente al obtener la información. Favor de contactar al administrador.'];

    $datosArreglo = array();

    $result = DB_query($sql, $db);

    // comprobación de existencia de la información
    if(DB_num_rows($result)==0){
        $data['msg'] = 'No se encontraron los datos solicitados.';
        return $data;
    }

    while($rs = DB_fetch_array($result)){
        $datosArreglo[] = $rs['ue'];
    }

    $data['registrosEncontrados'] = $datosArreglo;
    $data['success'] = true;

    // retorno de la información
    return $data;
}

function obtenDatosCuenta($db,$sql){
    // declaración de variables de la función
    $data = ['success'=>false, 'msg'=>'Ocurrió un incidente al obtener la información. Favor de contactar al administrador.'];
    $info = $_POST;

    $datosArreglo = array();

    $result = DB_query($sql, $db);

    // comprobación de existencia de la información
    if(DB_num_rows($result)==0){
        $data['msg'] = 'No se encontraron los datos solicitados.';
        return $data;
    }

    while($rs = DB_fetch_array($result)){
        $datosArreglo[] = [
            'valor' => $rs['valor'],
            'texto' => utf8_encode($rs['etiqueta']),
            'nivel' => $rs['nivel']
        ];
    }

    $data['cuentasEncontradas'] = $datosArreglo;
    $data['success'] = true;

    // retorno de la información
    return $data;
}

function obtenCuentasAdicionales($db,$sql){
    // declaración de variables de la función
    $data = ['success'=>false, 'msg'=>'Ocurrió un incidente al obtener la información. Favor de contactar al administrador.'];
    $info = $_POST;

    $datosArreglo = array();

    $result = DB_query($sql, $db);

    // comprobación de existencia de la información
    if(DB_num_rows($result)==0){
        $data['msg'] = 'No se encontraron los datos solicitados.';
        return $data;
    }

    if(DB_num_rows($result)==$info['cuentasEnObjeto']){
        $data['msg'] = 'No se encontraron cuentas adicionales.';
        return $data;
    }

    while($rs = DB_fetch_array($result)){
        $datosArreglo[] = [
            'valor' => $rs['valor'],
            'texto' => utf8_encode($rs['etiqueta']),
            'nivel' => $rs['nivel']
        ];
    }

    $data['cuentasEncontradas'] = $datosArreglo;
    $data['success'] = true;

    // retorno de la información
    return $data;
}
