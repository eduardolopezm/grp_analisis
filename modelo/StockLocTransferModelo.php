<?php

/**
 * StockLocTransferReceiveModelo.php
 *
 * @category panel
 * @package  ap_grp
 * @author   Japheth Calzada López
 * @license  [<url>] [name]
 * @version  GIT: <1234>
 * @link     (target, link)
 * Fecha creacion: 23/08/2018
 * Fecha Modificacion: 23/08/2018
 *
 * @file: StockLocTransferReceiveModelo.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$PageSecurity = 1;
//$PageSecurity = 4;
$PathPrefix = '../';
$funcion = 45;
$contenido = array();
$result= '';
session_start();
include($PathPrefix . 'config.php');

include $PathPrefix . "includes/SecurityUrl.php";

include($PathPrefix . 'includes/ConnectDB.inc');

include($PathPrefix . 'includes/SecurityFunctions.inc');

include($PathPrefix . 'includes/SQL_CommonFunctions.inc');

// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');


// inclucion de modelos separados
include('./itinerarioModelo.php');


# tipo de movimiento ubicado en las tabas "systypesinvtrans" y "systypescat"
define('TYPEMOV', 501);

$option                     = $_POST['option'];
$columnasNombres            = '';
$info                       = null;
$sql                        = '';
if ($option == 'mostrarTipoProducto') {
    $info = array();
    $sql = " SELECT DISTINCT stockflag, stocknameflag FROM stocktypeflag WHERE sn_activo = '1' AND stockflag <> 'D' ORDER BY stocknameflag ASC ";
    $ErrMsg = "No se obtuvo los Tipos de Productos";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['stockflag'], 'texto' => $myrow ['stocknameflag'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarTipoComision') {
    $sql        = "Sin Query";
    $tipoSol[]  = array( 'value' =>1, 'texto' =>"Nacional" );
    $tipoSol[]  = array( 'value' =>2, 'texto' =>"Internacional" );
    $result     = true;
    $contenido  = array('datos' => $tipoSol);
}
if ($option == 'mostrarTipoGasto') {
    $sql = "SELECT id_nu_zona_economica as value,  ln_descripcion as texto FROM tb_cat_zonas_economicas WHERE ind_activo=1 ORDER BY ln_descripcion";
    $ErrMsg = "No se obtuvo las Partidas Genéricas";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $texto = htmlspecialchars(utf8_encode($myrow ['texto']), ENT_QUOTES);
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $texto );
    }
    $result     = true;
    $contenido  = array('datos' => $info);
}

if ($option == 'eliminarInformacion') {
    $idMonto      = $_POST["idMonto"];
    $jerarquia    = $_POST["jerarquia"];
    $zonaEconomica= $_POST["zonaEconomica"];
    $sql          = "";
    $existenteViatico = fnExistenteViatico($jerarquia, $zonaEconomica, $db);
    if (!$existenteViatico) {
        $sql = "UPDATE tb_monto_jerarquia SET ind_activo = 0 WHERE id_nu_monto_jerarquia = $idMonto";
        ;
        $ErrMsg = "No se obtuvo las información";
        $TransResult = DB_query($sql, $db, $ErrMsg);
        if ($TransResult) {
            $result     = true;
        }
    }
}
if ($option == 'cancelar') {
    $reference = $_POST['reference'];
    
    $info = array();

   
    $SQL = "UPDATE loctransfers SET statustransfer = 'Cancelado' WHERE reference = '$reference'";
    $ErrMsg2 = "No se pudo reindexar";
    $TransResult2 = DB_query($SQL, $db, $ErrMsg2);

    $contenido = "Se Avanzó la Requisición ";
    $result = true;
}

if ($option == 'avanzarTransferencia') {
    $arraySalidas = $_POST['reference'];
    
    if (is_array($_POST['reference'])) {
        $arraySalidas = implode(",", $_POST['reference']);
    }
    
    $info = array();

    $sql = "SELECT  reference,  statustransfer, 
            if(sn_estatus_anterior=1,'Capturado',if(sn_estatus_anterior=2,'Validar',if(sn_estatus_anterior=3,'PorAutorizar',if(sn_estatus_anterior=4,'PorEntregar',0)))) as sn_estatus_anterior, 
            if(sn_estatus_siguiente=1,'Capturado',if(sn_estatus_siguiente=2,'Validar',if(sn_estatus_siguiente=3,'PorAutorizar',if(sn_estatus_siguiente=4,'PorEntregar',0)))) as sn_estatus_siguiente
            FROM loctransfers p 
            JOIN tb_botones_status tbs on (p.statustransfer = tbs.statusname) AND tbs.sn_funcion_id IN (45)
            WHERE reference in ($arraySalidas)";

    $TransResult = DB_query($sql, $db);

    while ($myrow = DB_fetch_array($TransResult)) {
        $reference = $myrow['reference'];
        $statusNuevo = $myrow['sn_estatus_siguiente'];
        
        $SQL2 = "UPDATE loctransfers SET statustransfer = '$statusNuevo' WHERE reference = '$reference'";
        $ErrMsg2 = "No se pudo reindexar";
        $TransResult2 = DB_query($SQL2, $db, $ErrMsg2);
    }

    $contenido = "Se Avanzó la Requisición ";
    $result = true;
}

if ($option == 'statusTransferencia') {
    $reference = $_POST['reference'];
    $info = array();
    $sql = "SELECT statustransfer AS status FROM loctransfers WHERE reference = '$reference'";
    $ErrMsg = "No se obtuvieron los estatus para el proceso";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'status' => $myrow ['status']
        );
    }
    $contenido = array('datos' => $info);
    $result = true;
}
if ($option == 'rechazarSalida') {
    $arraySalidas = $_POST['arraySalidas'];
    $totalSalidas = count($arraySalidas);
    for ($x=0; $x<$totalSalidas; $x++) {
        $reference    = $arraySalidas[$x]  ;
    
        $info = array();

        $sql = "SELECT  reference,  statustransfer, 
                if(sn_estatus_anterior=1,'Capturado',if(sn_estatus_anterior=2,'Validar',if(sn_estatus_anterior=3,'PorAutorizar',if(sn_estatus_anterior=4,'PorEntregar',0)))) as sn_estatus_anterior, 
                if(sn_estatus_siguiente=1,'Capturado',if(sn_estatus_siguiente=2,'Validar',if(sn_estatus_siguiente=3,'PorAutorizar',if(sn_estatus_siguiente=4,'PorEntregar',0)))) as sn_estatus_siguiente
                FROM loctransfers p 
                JOIN tb_botones_status tbs on (p.statustransfer = tbs.statusname) AND tbs.sn_funcion_id IN (45)
                WHERE reference in ($reference ) ";

        $TransResult = DB_query($sql, $db);

        while ($myrow = DB_fetch_array($TransResult)) {
            $reference = $myrow['reference'];
            $statusNuevo = $myrow['sn_estatus_anterior'];
            if ($statusNuevo != '') {
                $sql = "UPDATE loctransfers SET statustransfer = '$statusNuevo' WHERE reference= $reference";
                $ErrMsg2 = "No se pudo rechazar la requisición";
                $TransResult2 = DB_query($sql, $db, $ErrMsg2);
            } else {
                $contenido = "No se Cancelo la Requisición ";
                $result = false;
            }
        }
    }
    $contenido = "Se Rechazarón las requisiciones seleccionadas";
    $result = true;
}

if ($option == 'AutorizarSalida') {
    $arraySalidas     = $_POST["arraySalidas"];
    $arraySalida      = $arraySalidas["arraySalidas"];
    $totalSalidas     = count($arraySalida);
    $ErrMsg           = "No se actualizaron Registros";
    $cantidadArticulo = '';
    $cveArticulo      = '';
    $almacenRem       = '';
    $hoy              = date("Y-m-d h:m:s");
    $user             =  $_SESSION ['UserID'];
 
    for ($x=0; $x<$totalSalidas; $x++) {
        $reference    = $arraySalida[$x]  ;

        $sql = " UPDATE loctransfers SET statustransfer='Por Entregar',shipdate = '$hoy', userauthorize= '$user'  WHERE reference =$reference ";
        $TransResult = DB_query($sql, $db, $ErrMsg);
        if ($TransResult) {
            $sql = " SELECT shipqty,stockid,shiploc, (SELECT locationname FROM locations WHERE loccode = shiploc) AS desc_shiploc,
            (SELECT locationname FROM locations WHERE loccode = recloc) AS desc_recloc,
            (SELECT tagref FROM locations WHERE loccode = shiploc) AS tagref FROM loctransfers WHERE reference =$reference";
            
            $TransResult = DB_query($sql, $db, $ErrMsg);
            if ($TransResult) {
                foreach ($TransResult as $myrow) {
                    $cantidadArticulo   = $myrow ['shipqty'];
                    $cveArticulo        = $myrow ['stockid'];
                    $almacenRem         = $myrow ['shiploc'];
                    $tagref             = $myrow ['tagref'];
                    $desc_shiploc       = $myrow ['desc_shiploc'];
                    $desc_reclo         = $myrow ['desc_recloc'];

                    $sql = "UPDATE locstock SET ontransit = ontransit+" .$cantidadArticulo .
                    " WHERE stockid='" .  $cveArticulo .
                    "' AND loccode='" .$almacenRem . "'";
                    $TransResult = DB_query($sql, $db, $ErrMsg);
                    $result      = true;

                    $tagEnvia=ExtractTagrefXLoc($almacenRem, $db);
                    $hoyFormat =  date('d/m/Y', strtotime($hoy));
                    $PeriodNo = GetPeriod($hoyFormat, $db, $tagEnvia);

                    $costxserial = ValidBundleRefCost($cveArticulo, $almacenRem, '');

                    $sql = "INSERT INTO stockmoves (
                            stockid,
                            type,
                            transno,
                            loccode,
                            trandate,
                            prd,
                            reference,
                            qty,
                            newqoh,
                            standardcost,
                            avgcost,
                            tagref
                            )
                        VALUES (
                            '" .$cveArticulo. "',
                            19,
                            '" .$reference . "',
                            '" . $almacenRem ."',
                            '" . $hoy . "', '" . $PeriodNo . "',
                            '" . _('Salida al almac�n: ') . ' ' . utf8_decode($desc_reclo) ."',
                            '" . abs($cantidadArticulo) * -1 . "', '" . $cantidadArticulo . "',
                            '".$costxserial."',
                            '".$costxserial."',
                            '" . $tagref. "'
                            );";
                
                    // $TransResult = DB_query($sql, $db, $ErrMsg);
                    $result= true;
                }
            }
        }
    }
}
if ($option == 'obtenerInformacion') {
    $ur                    = $_POST["ur"];
    $ue                    = $_POST["ue"];
    $tipoBien              = $_POST["tipoBien"];
    $numeroTransferencia   = $_POST["numeroTransferencia"];
    $status                = $_POST["status"];
    $sqlWhere              = '';
    $fechaini              = ($_POST["fechainicio"] != '') ? date("Y-m-d", strtotime($_POST["fechainicio"])) : '' ;
    $fechafin              = ($_POST["fechafin"] != '') ? date("Y-m-d", strtotime($_POST["fechafin"])) : '' ;
 
    $longUr  = strlen($ur);
    if ($longUr > 4) {
        $sqlWhere .= " AND tagref=$ur ";
    }
    if ($ue != "") {
        $sqlUE  = strpos($ue, "-");
        if ($sqlUE  == '') {
            $sqlWhere .= " AND ln_ue =$ue ";
        };
    }

    if ($tipoBien != '') {
        if ($tipoBien == "'B'") {
            $sqlWhere .= " AND stockid like  '2%' ";
        } else if ($tipoBien == "'I'") {
            $sqlWhere .= " AND stockid like  '5%' ";
        }
    }
    if ($numeroTransferencia != '') {
        $sqlWhere .= " AND reference =  $numeroTransferencia ";
    }
    if ($status != '') {
        $sqlStatus  = strpos($status, ",");
        if ($sqlStatus === 13 || $sqlStatus === 11) {
            $sqlEntregado  = strpos($status, "Entregado");
            if ($sqlEntregado == 16) {
                $status.= ",'Recibido'";
            }

            $sqlWhere .= " AND statustransfer in ($status )";
        } else {
            if ($status == "'Entregado'") {
                $status = "'Recibido'";
            }
               

            $sqlWhere .= " AND statustransfer =  $status ";
        }
    }

    if (!empty($fechaini) && !empty($fechafin)) {
        $sqlWhere.= "  AND registerdate>= '".$fechaini." 00:00:00' AND registerdate<='".$fechafin." 23:59:59' ";
    }

    if (!empty($fechaini) && empty($fechafin)) {
        $sqlWhere.= "  AND registerdate>= '".$fechaini." 00:00:00'";
    }

    if (!empty($fechafin) && empty($fechaini)) {
        $condicion.= "  AND registerdate<='".$fechafin." 23:59:59' ";
    }



    $sql = "SELECT reference,
				locations.locationname as trfftoloc ,
                (SELECT locationname FROM locations WHERE loccode= recloc) AS trffromloc,
				registerdate,
				 shiploc,
				sum(shipqty) as cantidad,
				loctransfers.debtorno,
				loctransfers.branchcode,
                statustransfer as status,stockid,tagref,ln_ue
			FROM loctransfers INNER JOIN locations  
				ON loctransfers.shiploc=locations.loccode
			WHERE
             1=1 ";
        $sql.= $sqlWhere;
        $sql.= "  GROUP BY reference ";
        $sql .= " ORDER BY reference desc";
        
        
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    if ($TransResult) {
        while ($myrow = DB_fetch_array($TransResult)) {
            $stockid            =    $myrow ['stockid'];
            $primerNumero       = substr($stockid, 0, 1);
            $tipoBien           = '';
            $status             = '';
            $liga               ='';
            switch ($myrow ['status']) {
                case 'Capturado':
                    $status  = $myrow ['status'];
                    break;

                case 'Recibido':
                    $status  = "Entregado";
                    break;
                case 'Validar':
                    $status  = "Por Validar";
                    break;
                case 'PorAutorizar':
                    $status  = "Por Autorizar";
                    break;
                case 'Por Entregar':
                    $status  = $myrow ['status'];
                    break;
                case 'Cancelado':
                    $status  ='Cancelada';
                    break;
            }
            $liga = "<a target='_self' href='./StockLocTransfer_V.php?"."reference=".$myrow ['reference']."&modificar=1' style='color: blue; '><u>".$myrow ['reference']."</u></a>" ;
           
           // $enc = new Encryption;
            
           // $url = $enc->encode($url);
            //$liga= "URL=" . $url;

            if ($primerNumero == 2) {
                $tipoBien = 'Bien' ;
            }

            $info[] = array(
                'reference'  => $liga,
                'trffromloc' =>  htmlspecialchars(utf8_encode($myrow ['trffromloc']), ENT_QUOTES),
                'registerdate'   =>  date('d-m-Y', strtotime($myrow ['registerdate'])),
                'shiploc'  => $myrow ['shiploc'],
                "importe"   => round($myrow ["cantidad"]) ,
                'trfftoloc' =>  htmlspecialchars(utf8_encode($myrow ['trfftoloc']), ENT_QUOTES),
                'status'    =>  $status,
                'tipoBien'  => $tipoBien,
                'tagref'    => $myrow ['tagref'],
                'ln_ue'    => $myrow ['ln_ue'],
                'sel'    => 'false',
                'idRow'    =>  $myrow ['reference'],
                'folio'    =>  $myrow ['reference']
            );
        }
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'reference', type: 'string' },";
    $columnasNombres .= "{ name: 'registerdate', type: 'string' },";
    $columnasNombres .= "{ name: 'shiploc', type: 'string' },";
    $columnasNombres .= "{ name: 'importe', type: 'string' },";
    $columnasNombres .= "{ name: 'trffromloc', type: 'string' },";
    $columnasNombres .= "{ name: 'trfftoloc', type: 'string' },";
    $columnasNombres .= "{ name: 'tipoBien', type: 'string' },";
    $columnasNombres .= "{ name: 'tagref', type: 'string' },";
    $columnasNombres .= "{ name: 'ln_ue', type: 'string' },";
    $columnasNombres .= "{ name: 'status', type: 'string' },";
    $columnasNombres .= "{ name: 'folio', type: 'string' },";
    $columnasNombres .= "{ name: 'sel', type: 'bool' },";
    $columnasNombres .= "{ name: 'idRow', type: 'string' }";

    $columnasNombres .= "]";

    // Columnas para el GRID
    $columnasNombresGrid  = "[";
    $columnasNombresGrid .= " { text: 'idRow', datafield: 'idRow', width: '5%', cellsalign: 'left', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Sel', datafield: 'sel',editable:true,columntype: 'checkbox', width: '4%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'tagref', width: '5%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UE', datafield: 'ln_ue', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'folio',cellsalign: 'center', align: 'center', hidden: true  },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'reference', editable:false, cellsalign: 'center', align: 'center', hidden: false  },";
    $columnasNombresGrid .= " { text: 'Fecha', datafield: 'registerdate', width: '10%', cellsalign: 'right', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Tipo de Bien', datafield: 'tipoBien', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Almacén Origen', datafield: 'trfftoloc', width: '18%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Almacén Destino', datafield: 'trffromloc', width: '18%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Cantidad', datafield: 'importe', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'status', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Imprimir', datafield: 'f', width: '8%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = 'TransferenciaSalida'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid,'nombreExcel' => $nombreExcel);
    $result = true;
}

$dataObj = array('sql' => '',"contenido" => $contenido,"result"=>$result);
echo json_encode($dataObj, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);


function getZonaEconomica($db)
{

    $data = array();

    $sql = "SELECT tb_cat_zonas_economicas.id_nu_zona_economica AS id_nu_zona_economica,ln_descripcion FROM tb_cat_zonas_economicas INNER JOIN tb_cat_entidad_federativa ON tb_cat_zonas_economicas.id_nu_zona_economica = tb_cat_entidad_federativa.id_nu_zona_economica WHERE tb_cat_entidad_federativa.id_nu_entidad_federativa=".$_POST["estado"];

     //var_export($sql);


     DB_Txn_Begin($db);

     $result = DB_query($sql, $db);

    if ($result==true) {
         DB_Txn_Commit($db);

        while ($rs = DB_fetch_array($result)) {
            $data["zona"]   = $rs["ln_descripcion"];
            $data["idZona"] = $rs["id_nu_zona_economica"];
        }
    } else {
         DB_Txn_Rollback($db);
    }
     return $data;
}
function fnExistenteViatico($jerarquia, $zonaEconomica, $db)
{
    $existeViatico = false;
    $sql = "SELECT 
                id_nu_jerarquia ,ch_zona_economica
            FROM 
                tb_viaticos vi  
                JOIN tb_empleados em ON vi.id_nu_empleado = em.id_nu_empleado
                JOIN tb_cat_puesto pues ON pues.id_nu_puesto = em.id_nu_puesto
                JOIN tb_solicitud_itinerario iti ON iti.id_nu_solicitud_viaticos = id_nu_viaticos
            WHERE
                id_nu_jerarquia = $jerarquia";
    $ErrMsg     = "Error al consultar la base de Datos";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        if ($myrow["ch_zona_economica"] == $zonaEconomica) {
            $existeViatico  = true;
        }
    }
    return $existeViatico;
}

function ValidBundleRefCost($StockID, $LocCode, $BundleRef)
{
    global $db;

    $SQL = "SELECT  standardcost
				FROM stockserialitems 
				WHERE stockid='" . $StockID . "' 
				AND loccode ='" . $LocCode . "' 
				AND serialno='" . $BundleRef . "'";
    $Result = DB_query($SQL, $db);
    
    if (DB_num_rows($Result)==0) {
        return 0;
    } else {
        $myrow = DB_fetch_row($Result);
        return $myrow[0]; /*The quantity in the bundle */
    }
}
