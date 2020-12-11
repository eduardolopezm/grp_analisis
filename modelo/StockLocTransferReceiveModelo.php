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
$funcion = 46;
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
$type                       = 16;

if ($option == 'RechazarEntrada') {
    $arraySalidas     = $_POST["arraySalidas"];
    $arraySalida      = $arraySalidas["arraySalidas"];
    $totalSalidas     = count($arraySalida);
    $ErrMsg           = "No se actualizaron Registros";
    $cantidadArticulo = '';
    $cveArticulo      = '';
    $almacenRem       = '';
    $hoy              = date("Y-m-d h:m:s");
    $usuariologueado  = $_SESSION['UserID'];

    for ($x=0; $x<$totalSalidas; $x++) {
        $reference    = $arraySalida[$x]  ;
            $sql = " SELECT shipqty,stockid,shiploc,recloc,idtransfer,
                            (SELECT locationname FROM locations WHERE loccode = shiploc) AS desc_shiploc,
                            (SELECT locationname FROM locations WHERE loccode = recloc) AS desc_recloc,
                            (SELECT tagref FROM locations WHERE loccode = shiploc) AS tagref
                    FROM loctransfers WHERE reference =$reference";
                   
            $TransResult = DB_query($sql, $db, $ErrMsg);
        foreach ($TransResult as $myrow) {
            $cantidadArticulo   = round($myrow ['shipqty']);
            $cveArticulo        = $myrow ['stockid'];
            $almacenRem         = $myrow ['shiploc'];
            $almacenDes         = $myrow ['recloc'];
            $idTransfer         = $myrow ['idtransfer'];
            $desc_shiploc       = $myrow ['desc_shiploc'];
            $desc_reclo         = $myrow ['desc_recloc'];
            $tagref             = $myrow ['tagref'];

            $tagEnvia=ExtractTagrefXLoc($almacenRem, $db);
            $hoyFormat =  date('d/m/Y', strtotime($hoy));
            $PeriodNo = GetPeriod($hoyFormat, $db, $tagEnvia);

            $costxserial = ValidBundleRefCost($cveArticulo, $almacenRem, '');

                
            $SQL = "INSERT INTO stockmoves (
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
                    16,
                    '" .$reference . "',
                    '" . $almacenRem ."',
                    '" . $hoy . "', '" . $PeriodNo . "',
                    '" . _('Salida al almacén: ') . ' ' . utf8_decode($desc_reclo) ."',
                    '-" . $cantidadArticulo . "', '" . $cantidadArticulo . "',
                    '".$costxserial."',
                    '".$costxserial."',
                    '" . $tagref. "'
                    );";

            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $sql = "UPDATE locstock SET ontransit=ontransit-" .$cantidadArticulo .",quantity = quantity-".$cantidadArticulo.
            " WHERE stockid='" .  $cveArticulo .
            "' AND loccode='" .$almacenRem . "'";

                
            $TransResultStoke = DB_query($sql, $db, $ErrMsg);
            if ($TransResultStoke) {
                $sql = "UPDATE locstock SET quantity = quantity+".$cantidadArticulo.
                " WHERE stockid='" .  $cveArticulo .
                "' AND loccode='" .$almacenDes . "'";

                $TransResultDest = DB_query($sql, $db, $ErrMsg);
            }
        }
    }
    
    $sql = " UPDATE loctransfers SET statustransfer= 'PorAutorizar' WHERE reference =$reference ";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    if ($TransResult) {
        $result      = true;
    }
}
if ($option == 'AutorizarEntrada') {
    $arraySalidas     = $_POST["arraySalidas"];
    $arraySalida      = $arraySalidas["arraySalidas"];
    $totalSalidas     = count($arraySalida);
    $ErrMsg           = "No se actualizaron Registros";
    $cantidadArticulo = '';
    $cveArticulo      = '';
    $almacenRem       = '';
    $hoy              = date("Y-m-d h:m:s");
    $usuariologueado  = $_SESSION['UserID'];

    // Se itera los checkbox seleccionados
    for ($x=0; $x<$totalSalidas; $x++) {
        $reference    = $arraySalida[$x]  ;
        $sql = "UPDATE loctransfers SET recdate = '$hoy', userrec = '$usuariologueado' WHERE reference =$reference ";
       // $TransResults = DB_query($sql, $db, $ErrMsg);

        if (true) {
            $sql = "SELECT loctransfers.shipqty,
            loctransfers.stockid,
            loctransfers.shiploc,
            loctransfers.recloc,
            loctransfers.idtransfer,
            (SELECT locationname FROM locations WHERE loccode = loctransfers.shiploc) AS desc_shiploc,
            (SELECT locationname FROM locations WHERE loccode = loctransfers.recloc) AS desc_recloc,
            (SELECT tagref FROM locations WHERE loccode = loctransfers.shiploc) AS tagref,
            (SELECT ln_ue FROM locations WHERE loccode = loctransfers.recloc) AS ue,
            (SELECT ln_ue FROM locations WHERE loccode = loctransfers.shiploc) AS ueSal
            FROM loctransfers 
            WHERE loctransfers.reference = $reference";
            $TransResult = DB_query($sql, $db, $ErrMsg);
            foreach ($TransResult as $myrow) {
                $cantidadArticulo   = round($myrow ['shipqty']);
                $cveArticulo        = $myrow ['stockid'];
                $almacenRem         = $myrow ['shiploc'];
                $almacenRemUe       = $myrow ['ueSal'];
                $almacenDes         = $myrow ['recloc'];
                $idTransfer         = $myrow ['idtransfer'];
                $desc_shiploc       = $myrow ['desc_shiploc'];
                $desc_reclo         = $myrow ['desc_recloc'];
                $tagref             = $myrow ['tagref'];
                $ue                 = $myrow["ue"];
                $tagEnvia  =  ExtractTagrefXLoc($almacenRem, $db);
                $hoyFormat =  date('d/m/Y', strtotime($hoy));
                $PeriodNo  = GetPeriod($hoyFormat, $db, $tagEnvia);

                $costxserial = ValidBundleRefCost($cveArticulo, $almacenRem, '');

                //Polizas contables

                $valores                = '';
                $valoresOntrasit        = '';
                $valoresGltransCargo    = '';
                $valoresGltransAbono    = '';
                $valoresOntrasit        = '';
                $diaP                   = date('d');
                $mesP                   = date('m');
                $anioP                  = date('Y');
                $transno                = '';
                // $transno                = GetNextTransNo($type, $db);
                $cargo                  = '';
                $abono                  = '';
                $estavgcostXlegal       = 0;
                $cantidagtrans          = $cantidadArticulo;
                $leyenda                = '';

                $salidaFolio   = $reference; // GetNextTransNo(($type+1), $db); //1001 salida de almacen
                $folioPolizaUe = fnObtenerFolioUeGeneral($db, $tagref, $ue, ($type));
                $leyenda       ="Salida :".$salidaFolio." de la solicitud al almacen:".$reference." del artíiculo $cveArticulo";
                $estavgcostXlegal=StockAvgcostXLegal($cveArticulo, $tagref, $db);

                
                 //adjglact  //stockact
                 $sqlPol="SELECT DISTINCT stockcategory.adjglact, stockcategory.ln_abono_salida 
                 FROM stockmaster 
                 INNER JOIN stockcategory  ON stockmaster.categoryid=stockcategory.categoryid 
                 WHERE stockid ='$cveArticulo'";
                 $ErrMsg = "No se obtuvo datos.";
                 $TransResultPol = DB_query($sqlPol, $db, $ErrMsg);
          
                while ($myrow = DB_fetch_array($TransResultPol)) {
                    $cargo=$myrow ['adjglact'];
                    $abono=$myrow['ln_abono_salida'];
                }


                $valoresGltransCargo.="('".($type)."','".$salidaFolio."','".$anioP.'-'.$mesP.'-'.$diaP."','".$PeriodNo."','".$cargo."','".$leyenda. " cargo','".(1*($cantidagtrans))."','".$tagref."','".$_SESSION ['UserID']."',".$cveArticulo .",'1','".$ue."', '".$folioPolizaUe."')";
                $valoresGltransAbono.="('".($type)."','".$salidaFolio."','".$anioP.'-'.$mesP.'-'.$diaP."','".$PeriodNo."','".$abono."','".$leyenda." abono','".(-1*($cantidagtrans))."','".$tagref."','".$_SESSION ['UserID']."',".$cveArticulo .",'1','".$ue."', '".$folioPolizaUe."')";



                 //posted en 1
                $SQL="INSERT INTO  gltrans(type,typeno,trandate,periodno,account,narrative,amount,tag,userid,stockid,posted,ln_ue,nu_folio_ue) VALUES ".$valoresGltransCargo;
                $ErrMsg = "No se agregó cambios al almacen";
               // echo $SQL;exit;
                $TransResult = DB_query($SQL, $db, $ErrMsg);
            

                $SQL="INSERT INTO  gltrans(type,typeno,trandate,periodno,account,narrative,amount,tag,userid,stockid,posted,ln_ue,nu_folio_ue) VALUES ".$valoresGltransAbono;
                $ErrMsg = "No se agregó cambios al almacen";
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                // salida del almacén de origen en la tabla stockmoves
                $SQL = "INSERT INTO stockmoves (
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
                tagref,
                ln_ue
                )
                VALUES (
                '" .$cveArticulo. "',
                '308',
                '" .$reference . "',
                '" . $almacenRem ."',
                '" . $hoy . "', '" . $PeriodNo . "',
                '" . _('Salida al almacén: ') . ' ' . utf8_decode($desc_reclo) ."',
                '" . abs($cantidadArticulo) * -1 . "', '" . $cantidadArticulo . "',
                '".$costxserial."',
                '".$costxserial."',
                '" . $tagref. "',
                '".$almacenRemUe."'
                );";
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                // entrada al almacén destino en la tabla stockmoves
                $SQL = "INSERT INTO stockmoves (
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
                tagref,
                ln_ue
                )
                VALUES (
                '" .$cveArticulo. "',
                '".$type."',
                '" .$reference . "',
                '" . $almacenDes ."',
                '" . $hoy . "', '" . $PeriodNo . "',
                '" . _('Salida desde almacén: ') . ' ' . utf8_decode($desc_shiploc) ."',
                '" . $cantidadArticulo . "', '" . $cantidadArticulo . "',
                '".$costxserial."',
                '".$costxserial."',
                '" . $tagref. "',
                '".$ue."'
                );";

                $TransResult = DB_query($SQL, $db, $ErrMsg);
                // restar el transito del almacen de origen
                $SQL = "INSERT INTO stockmoves (
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
                    '".$type."',
                    '" .$reference . "',
                    '" . $almacenRem ."',
                    '" . $hoy . "', '" . $PeriodNo . "',
                    '" . _('Salida al almacén: ') . ' ' . utf8_decode($desc_reclo) ."',
                    '-" . $cantidadArticulo . "', '" . $cantidadArticulo . "',
                    '".$costxserial."',
                    '".$costxserial."',
                    '" . $tagref. "'
                    );";

                // $TransResult = DB_query($SQL, $db, $ErrMsg);

                $sql = "UPDATE locstock SET ontransit=ontransit-" .$cantidadArticulo .",quantity = quantity-".$cantidadArticulo.
                " WHERE stockid='" .  $cveArticulo .
                "' AND loccode='" .$almacenRem . "'";
                $TransResultStoke = DB_query($sql, $db, $ErrMsg);

                $sql = "UPDATE locstock SET quantity = quantity + ".$cantidadArticulo.
                " WHERE stockid='" .  $cveArticulo .
                "' AND loccode='" .$almacenDes . "'";
                $TransResultDest = DB_query($sql, $db, $ErrMsg);
            }
        }
    }
    
    $sql = " UPDATE loctransfers SET statustransfer='Recibido' WHERE reference =$reference ";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    if ($TransResult) {
        $result      = true;
    }
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

        if ($sqlStatus === 11) {
            $sqlWhere .= " AND statustransfer in ($status )";
        } else {
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
            1=1 AND 
            ( statustransfer= 'Por Entregar' OR statustransfer = 'Recibido' )";
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

            if ($primerNumero == 2) {
                $tipoBien = 'Bien' ;
            }

            if ($myrow ['status'] == 'Por Entregar') {
                $status   = 'Por Recibir';
            } else {
                $status   = $myrow ['status'];
            }

            $info[] = array(
                'reference'  => $myrow ['reference'],
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
                'idRow'    =>  $myrow ['reference']
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
    $columnasNombres .= "{ name: 'sel', type: 'bool' },";
    $columnasNombres .= "{ name: 'idRow', type: 'string' }";

    $columnasNombres .= "]";

    // Columnas para el GRID
    $columnasNombresGrid  = "[";
    $columnasNombresGrid .= " { text: 'idRow', datafield: 'idRow', width: '5%', cellsalign: 'left', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Sel', datafield: 'sel',editable:true,columntype: 'checkbox', width: '4%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'tagref', width: '5%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UE', datafield: 'ln_ue', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'reference', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha', datafield: 'registerdate', width: '10%', cellsalign: 'right', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Tipo de Bien', datafield: 'tipoBien', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Almacén Origen', datafield: 'trfftoloc', width: '18%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Almacén Destino', datafield: 'trffromloc', width: '18%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Cantidad', datafield: 'importe', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'status', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Imprimir', datafield: 'f', width: '8%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";


   
    $nombreExcel = 'TransferenciaEntrada'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid,'nombreExcel' => $nombreExcel);
    $result = true;
}

$dataObj = array('sql' => '',"contenido" => $contenido,"result"=>$result);
echo json_encode($dataObj, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);



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
