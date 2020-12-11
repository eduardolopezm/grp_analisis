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
//
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
$funcion=2345;
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

$tipoMovGenReduccion = "254"; // Tipo Movimiento Reduccion
$tipoMovGenAmpliacion = "253"; // Tipo Movimiento Ampliacion

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

if ($option == 'obtenerInformacionDetalle') {
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $tipoPoliza = $_POST['tipoPoliza'];

    $style = 'style="text-align:center;"';

    $detallePoliza .= '<div class="table-responsive">
    <table class="table table-bordered" name="tablaDetalle" id="tablaDetalle">';

    // Encabezados
    $detallePoliza .= '<tr class="header-verde">';
    $detallePoliza .= '<td '.$style.'></td>';
    if ($tipoPoliza == '1') {
        // Formulario Vehículo
        $detallePoliza .= '<td '.$style.'>Vehículo</td>';
        $detallePoliza .= '<td '.$style.'>Marca</td>';
        $detallePoliza .= '<td '.$style.'>Submarca</td>';
        $detallePoliza .= '<td '.$style.'>Año</td>';
        $detallePoliza .= '<td '.$style.'>Precio</td>';
        $detallePoliza .= '<td '.$style.'>Ubicación</td>';
    } else if ($tipoPoliza == '2') {
        // Formulario Muebles
        $detallePoliza .= '<td '.$style.'>Tipo</td>';
        $detallePoliza .= '<td '.$style.'>Marca</td>';
        $detallePoliza .= '<td '.$style.'>Serie</td>';
        $detallePoliza .= '<td '.$style.'>No. Inventario</td>';
        $detallePoliza .= '<td '.$style.'>Factura</td>';
        $detallePoliza .= '<td '.$style.'>Precio</td>';
        $detallePoliza .= '<td '.$style.'>Ubicación</td>';
    } else if ($tipoPoliza == '3') {
        // Formulario Inmuebles
        $detallePoliza .= '<td '.$style.'>Tipo</td>';
        $detallePoliza .= '<td '.$style.'>Niveles</td>';
        $detallePoliza .= '<td '.$style.'>Uso</td>';
        $detallePoliza .= '<td '.$style.'>Año Contrucción</td>';
        $detallePoliza .= '<td '.$style.'>Valor Avalúo</td>';
        $detallePoliza .= '<td '.$style.'>Ubicación</td>';
    } else if ($tipoPoliza == '4') {
        // Formulario Vida
        $detallePoliza .= '<td '.$style.'>Paterno</td>';
        $detallePoliza .= '<td '.$style.'>Materno</td>';
        $detallePoliza .= '<td '.$style.'>Nombre</td>';
        $detallePoliza .= '<td '.$style.'>Tipo</td>';
        $detallePoliza .= '<td '.$style.'>Precio</td>';
    }
    $detallePoliza .= '</tr>';

    $SQL = "
    SELECT 
    tb_gestion_poliza_detalle.nu_type, 
    tb_gestion_poliza_detalle.nu_transno, 
    tb_gestion_poliza_detalle.ln_vehiculo, 
    tb_gestion_poliza_detalle.ln_marca, 
    tb_gestion_poliza_detalle.ln_submarca, 
    tb_gestion_poliza_detalle.nu_anio, 
    tb_gestion_poliza_detalle.nu_precio, 
    tb_gestion_poliza_detalle.ln_ubicacion, 
    tb_gestion_poliza_detalle.ln_tipo, 
    tb_gestion_poliza_detalle.ln_serie, 
    tb_gestion_poliza_detalle.ln_inventario, 
    tb_gestion_poliza_detalle.ln_factura, 
    tb_gestion_poliza_detalle.ln_niveles, 
    tb_gestion_poliza_detalle.ln_uso, 
    tb_gestion_poliza_detalle.ln_anio_construccion, 
    tb_gestion_poliza_detalle.nu_valor_avaluo, 
    tb_gestion_poliza_detalle.ln_paterno, 
    tb_gestion_poliza_detalle.ln_materno, 
    tb_gestion_poliza_detalle.ln_nombre, 
    tb_gestion_poliza_detalle.nu_aseguramiento,
    tb_cat_aseguramientos.ln_nombre as nombreAseguramiento
    FROM tb_gestion_poliza_detalle
    LEFT JOIN tb_cat_aseguramientos ON tb_cat_aseguramientos.id = tb_gestion_poliza_detalle.nu_aseguramiento
    WHERE tb_gestion_poliza_detalle.nu_type = '".$type."' AND tb_gestion_poliza_detalle.nu_transno = '".$transno."'
    ";
    $ErrMsg = "No se obtuvo la Información de Detalle";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $num = 1;
    while ($myrow = DB_fetch_array($TransResult)) {
        $detallePoliza .= '<tr>';
        $detallePoliza .= '<td '.$style.'>'.$num.'</td>';
        if ($tipoPoliza == '1') {
            // Formulario Vehículo
            // numElementos = 6;
            $detallePoliza .= '<td '.$style.'>'.$myrow['ln_vehiculo'].'</td>';
            $detallePoliza .= '<td '.$style.'>'.$myrow['ln_marca'].'</td>';
            $detallePoliza .= '<td '.$style.'>'.$myrow['ln_submarca'].'</td>';
            $detallePoliza .= '<td '.$style.'>'.$myrow['nu_anio'].'</td>';
            $detallePoliza .= '<td '.$style.'>'.$myrow['nu_precio'].'</td>';
            $detallePoliza .= '<td '.$style.'>'.$myrow['ln_ubicacion'].'</td>';
        } else if ($tipoPoliza == '2') {
            // Formulario Muebles
            // numElementos = 7;
            $detallePoliza .= '<td '.$style.'>'.$myrow['ln_tipo'].'</td>';
            $detallePoliza .= '<td '.$style.'>'.$myrow['ln_marca'].'</td>';
            $detallePoliza .= '<td '.$style.'>'.$myrow['ln_serie'].'</td>';
            $detallePoliza .= '<td '.$style.'>'.$myrow['ln_inventario'].'</td>';
            $detallePoliza .= '<td '.$style.'>'.$myrow['ln_factura'].'</td>';
            $detallePoliza .= '<td '.$style.'>'.$myrow['nu_precio'].'</td>';
            $detallePoliza .= '<td '.$style.'>'.$myrow['ln_ubicacion'].'</td>';
        } else if ($tipoPoliza == '3') {
            // Formulario Inmuebles
            // numElementos = 6;
            $detallePoliza .= '<td '.$style.'>'.$myrow['ln_tipo'].'</td>';
            $detallePoliza .= '<td '.$style.'>'.$myrow['ln_niveles'].'</td>';
            $detallePoliza .= '<td '.$style.'>'.$myrow['ln_uso'].'</td>';
            $detallePoliza .= '<td '.$style.'>'.$myrow['ln_anio_construccion'].'</td>';
            $detallePoliza .= '<td '.$style.'>'.$myrow['nu_valor_avaluo'].'</td>';
            $detallePoliza .= '<td '.$style.'>'.$myrow['ln_ubicacion'].'</td>';
        } else if ($tipoPoliza == '4') {
            // Formulario Vida
            // numElementos = 5;
            $detallePoliza .= '<td '.$style.'>'.$myrow['ln_paterno'].'</td>';
            $detallePoliza .= '<td '.$style.'>'.$myrow['ln_materno'].'</td>';
            $detallePoliza .= '<td '.$style.'>'.$myrow['ln_nombre'].'</td>';
            $detallePoliza .= '<td '.$style.'>'.$myrow['nombreAseguramiento'].'</td>';
            $detallePoliza .= '<td '.$style.'>'.$myrow['nu_precio'].'</td>';
        }

        $detallePoliza .= '</tr>';

        $num ++;
    }

    $detallePoliza .='</table>
    </div>';

    $datosRespuesta['detallePoliza'] = $detallePoliza;

    $contenido = array('datos' => $datosRespuesta);
    $result = true;
}

if ($option == 'obtenerInformacion') {
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];
    $fechaDesde = $_POST['fechaDesde'];
    $fechaHasta = $_POST['fechaHasta'];
    $ue = $_POST['ue'];
    $tipoPoliza = $_POST['tipoPoliza'];
    $folio = $_POST['folio'];
    $folioPoliza = $_POST['folioPoliza'];
    $selectAseguradora = $_POST['selectAseguradora'];
    $selectCobertura = $_POST['selectCobertura'];

    $sqlWhere = "";

    if (!empty($legalid)) {
        $sqlWhere .= " AND tags.legalid IN (".$legalid.") ";
    }
    if (!empty($tagref) and $tagref != '-1') {
        $sqlWhere .= " AND tb_gestion_poliza.sn_tagref IN (".$tagref.") ";
    }
    if (!empty($fechaDesde) && !empty($fechaHasta)) {
        $fechaDesde = date_create($fechaDesde);
        $fechaDesde = date_format($fechaDesde, 'Y-m-d');

        $fechaHasta = date_create($fechaHasta);
        $fechaHasta = date_format($fechaHasta, 'Y-m-d');

        $sqlWhere .= " AND tb_gestion_poliza.dtm_registro between '".$fechaDesde." 00:00:00' AND '".$fechaHasta." 23:59:59' ";
    } elseif (!empty($fechaDesde)) {
        $fechaDesde = date_create($fechaDesde);
        $fechaDesde = date_format($fechaDesde, 'Y-m-d');

        $sqlWhere .= " AND tb_gestion_poliza.dtm_registro >= '".$fechaDesde." 00:00:00' ";
    } elseif (!empty($fechaHasta)) {
        $fechaHasta = date_create($fechaHasta);
        $fechaHasta = date_format($fechaHasta, 'Y-m-d');

        $sqlWhere .= " AND tb_gestion_poliza.dtm_registro <= '".$fechaHasta." 23:59:59' ";
    }

    if ($ue != '') {
        $sqlWhere .= " AND tb_gestion_poliza.ln_ue IN (".$ue.") ";
    }

    if ($tipoPoliza != '') {
        $sqlWhere .= " AND tb_gestion_poliza.nu_tipo IN (".$tipoPoliza.") ";
    }

    if ($folio != '') {
        $sqlWhere .= " AND tb_gestion_poliza.nu_transno = '".$folio."' ";
    }

    if ($folioPoliza != '') {
        $sqlWhere .= " AND tb_gestion_poliza.ln_folio like '%".$folioPoliza."%' ";
    }

    if ($selectAseguradora != '') {
        $sqlWhere .= " AND tb_gestion_poliza.nu_aseguradora IN (".$selectAseguradora.") ";
    }

    if ($selectCobertura != '') {
        $sqlWhere .= " AND tb_gestion_poliza.nu_cobertura IN (".$selectCobertura.") ";
    }

    $info = array();
    $SQL = "
    SELECT  
    tb_gestion_poliza.nu_type,
    tb_gestion_poliza.nu_transno,
    tb_gestion_poliza.sn_tagref,
    tb_gestion_poliza.ln_folio,
    tb_gestion_poliza.nu_deducible,
    tb_gestion_poliza.nu_coaseguro,
    tb_gestion_poliza.nu_tipo,
    DATE_FORMAT(tb_gestion_poliza.dtm_registro, '%d-%m-%Y') as fechaCaptura,
    DATE_FORMAT(tb_gestion_poliza.dtm_desde, '%d-%m-%Y') as fechaDesde,
    DATE_FORMAT(tb_gestion_poliza.dtm_hasta, '%d-%m-%Y') as fechaHasta,
    tags.tagdescription,
    tb_cat_unidades_ejecutoras.desc_ue,
    tb_cat_tipo_poliza.ln_nombre as tipoPoliza,
    tb_cat_aseguradoras.ln_nombre as nombreAseguradora,
    tb_cat_tipo_cobertura.ln_nombre as nombreCobertura
    FROM tb_gestion_poliza
    JOIN tags ON tags.tagref = tb_gestion_poliza.sn_tagref
    JOIN tb_cat_tipo_poliza ON tb_cat_tipo_poliza.id = tb_gestion_poliza.nu_tipo
    LEFT JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ue = tb_gestion_poliza.ln_ue AND tb_cat_unidades_ejecutoras.ur = tb_gestion_poliza.sn_tagref
    JOIN tb_cat_aseguradoras ON tb_cat_aseguradoras.id = tb_gestion_poliza.nu_aseguradora
    JOIN tb_cat_tipo_cobertura ON tb_cat_tipo_cobertura.id = tb_gestion_poliza.nu_cobertura
    WHERE 1 = 1 ".$sqlWhere."
    ORDER BY nu_transno DESC
    ";
    $ErrMsg = "No se obtuvo la Información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $urlGeneral = "&transno=>" . $myrow['nu_transno'] . "&type=>" . $myrow['nu_type'];
        $enc = new Encryption;
        $url = $enc->encode($urlGeneral);
        $liga= "URL=" . $url;
        $operacion = '<a type="button" id="btnAbrirDetalle_'.$myrow['nu_transno'].'" name="btnAbrirDetalle_'.$myrow['nu_transno'].'" href="gestionPolizas.php?'.$liga.'" title="Abrir Detalle" style="color: blue;">'.$myrow ['nu_transno'].'</a>'; // target="_blank"
        $detalleFolio = '<a id="btnDetalle_'.$myrow['nu_transno'].'" onclick="fnMostrarDetallePoliza('.$myrow['nu_type'].','.$myrow['nu_transno'].', '.$myrow['nu_tipo'].')" title="Mostrar Detalle" style="color: blue;">'.$myrow['ln_folio'].'</a>';
        $info[] = array(
            'nu_type' => $myrow ['nu_type'],
            'nu_transno' => $myrow ['nu_transno'],
            'nu_transno_link' => $operacion,
            'ln_folio' => $myrow ['ln_folio'],
            'ln_folio_detalle' => $detalleFolio,
            'nu_deducible' => $myrow ['nu_deducible'],
            'nu_coaseguro' => $myrow ['nu_coaseguro'],
            'fechaCaptura' => $myrow ['fechaCaptura'],
            'fechaDesde' => $myrow ['fechaDesde'],
            'fechaHasta' => $myrow ['fechaHasta'],
            'tipoPoliza' => $myrow ['tipoPoliza'],
            'nombreAseguradora' => $myrow ['nombreAseguradora'],
            'nombreCobertura' => $myrow ['nombreCobertura']
        );
    }

    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'nu_transno', type: 'string' },";
    $columnasNombres .= "{ name: 'nu_transno_link', type: 'string' },";
    $columnasNombres .= "{ name: 'tipoPoliza', type: 'string' },";
    $columnasNombres .= "{ name: 'ln_folio', type: 'string' },";
    $columnasNombres .= "{ name: 'ln_folio_detalle', type: 'string' },";
    $columnasNombres .= "{ name: 'nombreAseguradora', type: 'string' },";
    $columnasNombres .= "{ name: 'nombreCobertura', type: 'string' },";
    $columnasNombres .= "{ name: 'nu_deducible', type: 'string' },";
    $columnasNombres .= "{ name: 'nu_coaseguro', type: 'string' },";
    $columnasNombres .= "{ name: 'fechaDesde', type: 'string' },";
    $columnasNombres .= "{ name: 'fechaHasta', type: 'string' },";
    $columnasNombres .= "{ name: 'fechaCaptura', type: 'string' }";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $colResumenTotal= ", aggregates: [{'<b>Total</b>' :".
                            "function (aggregatedValue, currentValue) {".
                                "var total = currentValue;".
                                "return aggregatedValue + total;".
                            "}".
                        "}] ";
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'nu_transno', width: '7%', cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'nu_transno_link', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Tipo', datafield: 'tipoPoliza', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Folio Póliza', datafield: 'ln_folio', width: '10%', cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Folio Póliza', datafield: 'ln_folio_detalle', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Aseguradora', datafield: 'nombreAseguradora', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Cobertura', datafield: 'nombreCobertura', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Deducible', datafield: 'nu_deducible', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Co-aseguro', datafield: 'nu_coaseguro', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Inicio', datafield: 'fechaDesde', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fin', datafield: 'fechaHasta', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Captura', datafield: 'fechaCaptura', width: '8%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";
    
    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

if ($option == 'obtenerBotones') {
    $info = array();
    $SQL = "SELECT 
            distinct tb_botones_status.functionid,
            tb_botones_status.statusid,
            tb_botones_status.statusname,
            tb_botones_status.namebutton,
            tb_botones_status.functionid,
            tb_botones_status.adecuacionPresupuestal,
            tb_botones_status.clases
            FROM tb_botones_status
            JOIN sec_profilexuser ON sec_profilexuser.userid = '".$_SESSION['UserID']."'
            JOIN sec_funxprofile ON sec_funxprofile.profileid = sec_profilexuser.profileid
            WHERE 
            (tb_botones_status.sn_funcion_id = '".$funcion."')
            AND (tb_botones_status.sn_flag_disponible = 1)
            AND (tb_botones_status.sn_panel_adecuacion_presupuestal = 1)
            AND
            (tb_botones_status.functionid = sec_funxprofile.functionid 
            OR 
            tb_botones_status.functionid = (SELECT sec_funxuser.functionid FROM sec_funxuser WHERE sec_funxuser.functionid = tb_botones_status.functionid)
            ) 
            ORDER BY tb_botones_status.functionid ASC
            ";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'statusid' => $myrow ['statusid'],
            'statusname' => $myrow ['statusname'],
            'namebutton' => $myrow ['namebutton'],
            'functionid' => $myrow ['functionid'],
            'clases' => $myrow ['clases']
        );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarTipoPolizaSeguros') {
    // Tipos de Pólizas para Seguros Patrimoniales
    $info = array();
    $SQL = "SELECT id, ln_nombre FROM tb_cat_tipo_poliza WHERE nu_activo = 1 ORDER BY ln_nombre ASC";

    $ErrMsg = "No se obtuvieron los Tipos de Póliza de Seguros Patrimoniales";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['id'], 'texto' => $myrow ['ln_nombre'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarAseguradoraPolizaSeguros') {
    // Tipos de Aseguradoras para Seguros Patrimoniales
    $info = array();
    $SQL = "SELECT id, ln_nombre FROM tb_cat_aseguradoras WHERE nu_activo = 1 ORDER BY ln_nombre ASC";

    $ErrMsg = "No se obtuvieron las Aseguradoras de Seguros Patrimoniales";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['id'], 'texto' => $myrow ['ln_nombre'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarCoberturaPolizaSeguros') {
    // Tipos de Aseguradoras para Seguros Patrimoniales
    $info = array();
    $SQL = "SELECT id, ln_nombre FROM tb_cat_tipo_cobertura WHERE nu_activo = 1 ORDER BY ln_nombre ASC";

    $ErrMsg = "No se obtuvieron las Coberturas de Seguros Patrimoniales";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['id'], 'texto' => $myrow ['ln_nombre'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarAseguramientoPolizaSeguros') {
    // Tipos de Aseguramientos para Seguros Patrimoniales (Póliza de Vida)
    $info = array();
    $SQL = "SELECT id, ln_nombre FROM tb_cat_aseguramientos WHERE nu_activo = 1 ORDER BY ln_nombre ASC";

    $ErrMsg = "No se obtuvieron los Aseguramientos de Seguros Patrimoniales";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['id'], 'texto' => $myrow ['ln_nombre'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarPatrimonioPorTipo') {
    // Tipos de Aseguramientos para Seguros Patrimoniales (Póliza de Vida)
    $info = array();

    $UR = $_POST['ur'];
    $UE = $_POST['ue'];
    $tp = $_POST['tipoPatrimonio'];
    
    $SQL = "SELECT assetid, barcode as descripcion
            FROM fixedassets 
            WHERE tagrefowner = '".$UR."' AND ue = '".$UE."' AND tipo_bien = '".$tp."' AND active = 1 AND ownertype = 1; ";

    $ErrMsg = "No se obtuvieron los patrimonios";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['assetid'], 'texto' => $myrow ['descripcion'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarEmpleados') {
    // Tipos de Aseguramientos para Seguros Patrimoniales (Póliza de Vida)
    $info = array();

    $UR = $_POST['ur'];
    $UE = $_POST['ue'];
    $tp = $_POST['tipoPatrimonio'];
    
    $SQL = "SELECT id_nu_empleado,concat(ln_nombre,' ',sn_primer_apellido,' ',sn_segundo_apellido) AS empleado 
    FROM tb_empleados 
    WHERE tagref='".$UR."'  AND ue ='".$UE."' AND ind_activo = 1;";

    $ErrMsg = "No se obtuvieron los patrimonios";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['id_nu_empleado'], 'texto' => $myrow ['empleado'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}


if($option=='obtenerInfoPatrimonio'){

    $SQL = "SELECT * 
            FROM fixedassets 
            WHERE assetid = '".$_POST['assetid']."'";

    $ErrMsg = "No se obtuvieron los patrimonios";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {

        $info[] = array( 
            'serie' => $myrow ['serialno'], 
            'modelo' => $myrow ['model'],
            'marca' => $myrow ['marca'], 
            'factura' => $myrow ['factura'], 
            'anio' => $myrow ['anio'],
            'color' => $myrow ['color'],
            'placas' => $myrow ['placas'],
            'precio' => $myrow ['cost'],
            'ubicacion' => $myrow ['assetlocation']
        );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if($option=='obtenerInfoEmpleado'){

    $SQL = "SELECT * 
            FROM tb_empleados 
            WHERE id_nu_empleado = '".$_POST['assetid']."'";

    $ErrMsg = "No se obtuvieron los patrimonios";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {

        $info[] = array( 
            'paterno' => $myrow ['sn_primer_apellido'], 
            'materno' => $myrow ['sn_segundo_apellido'],
            'nombre' => $myrow ['ln_nombre'], 
            'curp' => $myrow ['sn_curp'], 
            'rfc' => $myrow ['sn_rfc']
        );
    }

    $contenido = array('datos' => $info);
    $result = true;
}


$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
