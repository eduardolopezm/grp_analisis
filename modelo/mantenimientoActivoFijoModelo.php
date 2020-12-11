<?php
/**
 * Mantenimiento de activo fijo.
 *
 * @category     Panel
 * @package      ap_grp
 * @version      0.1
 * @link /modelo/mantenimientoActivoFijoModelo.php
 * Fecha Creación: 06.06.18
 * Se genera el presente programa para la visualización de la información
 * de los mantenimiento de los activos fijos.
 */

$PageSecurity = 1;
$PathPrefix = '../';
$funcion=1987;

//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include $PathPrefix . "includes/SecurityUrl.php";
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

header('Content-type: text/html; charset=ISO-8859-1');
$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option =$_POST['option'];
$enc = new Encryption;


if($option == "obtenerMantenimientos"){

	$SQL="SELECT fixedassetmaintenance.id,
                fixedassetmaintenance.ur,
                fixedassetmaintenance.ue,
                fixedassetmaintenance.tipoMantenimiento,
                fixedassetmaintenance.mttoid,
                fixedassetmaintenance.description,
                DATE_FORMAT(datetimeup,'%d-%m-%Y') AS datetimeup,
                DATE_FORMAT(datetimeup,'%d-%m-%Y') AS dateMtto,
                fixedassetmaintenance.tipo_bien,
                fixedassetmaintenance.status,
                tb_fixedassetmaintenance_types.description as desc_tipo_mantenimiento,
                fixedAssetCategoryBien.description as desc_tipo_bien,
                tb_fixedassetmaintenance_status.description as desc_estatus
        FROM fixedassetmaintenance
        LEFT JOIN tb_fixedassetmaintenance_types on fixedassetmaintenance.tipoMantenimiento =  tb_fixedassetmaintenance_types.id
        LEFT JOIN fixedAssetCategoryBien ON fixedassetmaintenance.tipo_bien = fixedAssetCategoryBien.id
        LEFT JOIN tb_fixedassetmaintenance_status ON fixedassetmaintenance.status = tb_fixedassetmaintenance_status.id
        WHERE 1=1 ";

    if(isset($_POST['txtUR']) and $_POST['txtUR']!=""){
         $SQL.=" AND  fixedassetmaintenance.ur in (".$_POST['txtUR'].")";
    }

    if(isset($_POST['txtUE']) and $_POST['txtUE']!=""){
         $SQL.=" AND  fixedassetmaintenance.ue in (".$_POST['txtUE'].")";
    }

    if (isset($_POST['txtFolio']) and $_POST['txtFolio']!=""){
        $SQL.=" AND fixedassetmaintenance.mttoid like '%".$_POST['txtFolio']."%'";
    }else{
  
    	if(isset($_POST['dpDesde']) and $_POST['dpDesde']!=""){
        	$SQL.=" AND datetimeup >= '".fnFormatoFechaYMD($_POST['dpDesde'],'-')."'";
    	}
    	if(isset($_POST['dpHasta']) and $_POST['dpHasta']!=""){
        	$SQL.=" AND datetimeup <= '".fnFormatoFechaYMD($_POST['dpHasta'],'-')." 23:59:59'";
    	}

        if(isset($_POST['txtPrioridad']) and $_POST['txtPrioridad']!=""){
             $SQL.=" AND  fixedassetmaintenance.prioridad = '".$_POST['txtPrioridad']."'";
        }

        if(isset($_POST['txtStatus']) and $_POST['txtStatus']!=""){
             $SQL.=" AND  fixedassetmaintenance.status in (".$_POST['txtStatus'].")";
        }
        if(isset($_POST['txtNumInventario']) and $_POST['txtNumInventario']!=""){
             $SQL.=" AND  fixedassets.barcode like '%".$_POST['txtNumInventario']."%'";
        }
        if(isset($_POST['selectTipoMantenimiento']) and $_POST['selectTipoMantenimiento']!=""){
            $SQL.=" AND  fixedassetmaintenance.tipoMantenimiento in (".$_POST['selectTipoMantenimiento'].")";
        }
        if(isset($_POST['selectTipoBien']) and $_POST['selectTipoBien']!=""){
            $SQL.=" AND  fixedassetmaintenance.tipo_bien in (".$_POST['selectTipoBien'].")";
        }
    }

    $SQL.=" ORDER BY fixedassetmaintenance.mttoid DESC;";

    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
        $enc = new Encryption;
        $url = "&Folio=>" . $myrow["mttoid"];
        $url = $enc->encode($url);
        $liga= "URL=" . $url;
        $liga_folio ="<a target='_self' href='./mantenimientoActivoFijoDetalle.php?$liga' style='color: blue;'><u>".$myrow ['mttoid']."</u></a>";

        $info[] = 
            array(  
                    'idCHK'=> false, 
            		'UR' => $myrow ['ur'],
                    'UE' => $myrow ['ue'],
                    'folio' => $myrow ['mttoid'], 
                    'folio_liga' => $liga_folio, 
                    'fecharegistro' => $myrow ['datetimeup'],  
                    'fechamtto' => $myrow ['dateMtto'],  
                    'inventario' => $myrow ['desc_tipo_bien'],  
                    'tipomantenimiento' => $myrow ['desc_tipo_mantenimiento'],  
                    'idstatus' => $myrow ['status'],
                    'status' => $myrow ['desc_estatus'],
                    'observacion' => $myrow ['description']
                );
    }

        // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'idCHK', type: 'bool' },";
    $columnasNombres .= "{ name: 'UR', type: 'string' },";
    $columnasNombres .= "{ name: 'UE', type: 'string' },";
    $columnasNombres .= "{ name: 'folio', type: 'string' },";
    $columnasNombres .= "{ name: 'folio_liga', type: 'string' },";
    $columnasNombres .= "{ name: 'fecharegistro', type: 'string' },";
    $columnasNombres .= "{ name: 'fechamtto', type: 'string' },";
    $columnasNombres .= "{ name: 'inventario', type: 'string' },";
    $columnasNombres .= "{ name: 'tipomantenimiento', type: 'string' },";
    $columnasNombres .= "{ name: 'idstatus', type: 'string' },";
    $columnasNombres .= "{ name: 'status', type: 'string' },";
    $columnasNombres .= "{ name: 'observacion', type: 'string' }";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Sel', datafield:'idCHK', editable:true, columntype: 'checkbox', width: '3%', cellsalign: 'center', align: 'center' },";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'UR', editable:false, width: '6%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UE', datafield: 'UE', editable:false, width: '6%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha Captura', datafield: 'fecharegistro',editable:false, width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'folio',editable:false, width: '10%', cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'folio_liga',editable:false, width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Inventario', datafield: 'inventario',editable:false, width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Programación Mtto', datafield: 'fechamtto',editable:false, width: '10%', cellsalign: 'center', align: 'center', hidden: false },";

    $columnasNombresGrid .= " { text: 'Tipo Mantenimiento', datafield: 'tipomantenimiento',editable:false, width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'idstatus',editable:false, width: '10%', cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'status',editable:false, width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Observación', datafield: 'observacion',editable:false, width: '25%', cellsalign: 'left', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

function fnFormatoFechaYMD($fecha,$separador){
    $fechaFormateada = "";

    if($fecha == ""){
        $fechaFormateada = "0000-00-00";
    }else{
        list($dia, $mes, $anio) = explode($separador, $fecha);
        $fechaFormateada = $anio.'-'.$mes.'-'.$dia;
    }
    
    return $fechaFormateada;
}



$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $TransResult, 'ErrMsg' => $ErrMsg, 'DbgMsg' => $DbgMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);

?>


