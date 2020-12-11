<?php
/**
 * Archivo para administrar padron de beneficiarios
 *
 * @category Description
 * @package  Ap_grp
 * @author   Armando Barrientos Martinez <armando.barrientos@tecnoaplicada.com>
 * @license  [<url>] [name]
 * @version  GIT: <8253daa3769440ef773e28a6329b7d109851e0c4>
 * @link     (target, link)
 * Fecha Creación: 01/08/2017
 * Fecha Modificación: 21/08/2017
 * Se realiza la consulta de informacion para las requisiciones y mostrar los datos en todo el proceso de adquisiciones.
 */
///////////
$PageSecurity = 1;
$funcion=2265;
$PathPrefix = '../';

session_start();

// incluir archivos de apoyo
include($PathPrefix. "includes/SecurityUrl.php");
include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');
include($PathPrefix .'includes/DateFunctions.inc');

// declaracion de variables locales
$ErrMsg = _('');
$sqlFinal = '';
$contenido = array();
$result = false;
$SQL = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";
/////$periodo = GetPeriod(date('d/m/Y'), $db);
$periodo = GetPeriod( ( isset($_SESSION['ejercicioFiscal'])&&$_SESSION['ejercicioFiscal']!=date('Y') ? date('d')."/12/$_SESSION[ejercicioFiscal]" : date('d/m/Y') ), $db);

header('Content-type: text/html; charset=ISO-8859-1');
//header('Content-Type: application/json; charset=utf-8');
$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

if ($option == 'traeRequisiciones') {
    $idproveedor='';
    $nomproveedor='';
    $idrequisicion='';
    $codigoExpediente = '';

    $info = array();
    $condicion= " 1=1 ";
    $fechaini= ($_POST["fechainicio"] != '') ? date("Y-m-d", strtotime($_POST["fechainicio"])) : '' ;
    $fechafin= ($_POST["fechafin"] != '') ? date("Y-m-d", strtotime($_POST["fechafin"])) : '' ;
    $dependencia= $_POST["dependencia"];
    $unidadres= $_POST["unidadres"];
    $unidadeje= $_POST["unidadeje"];
    if (isset($_POST["requisicion"])) {
        $idrequisicion= $_POST["requisicion"];
    }
    
    if (isset($_POST["idproveedor"])) {
          $idproveedor= $_POST["idproveedor"];
          $nomproveedor= $_POST["nomproveedor"];
    }

    if (isset($_POST['codigoExpediente'])) {
        $codigoExpediente = $_POST['codigoExpediente'];
    }
  
    $estatus= $_POST["estatus"];
    $funcion= $_POST["funcion"];
    $seleccionar= "";

    // separar la seleccion multiple de la dependencia
    $datosDependencia = "";
    foreach ($dependencia as $key) {
        if (empty($datosDependencia)) {
            $datosDependencia .= "'".$key."'";
        } else {
            $datosDependencia .= ", '".$key."'";
        }
    }

    // separar la seleccion multiple de las unidades responsables
    $datosUR = "";
    foreach ($unidadres as $key) {
        if (empty($datosUR)) {
            $datosUR .= "'".$key."'";
        } else {
            $datosUR .= ", '".$key."'";
        }
    }

    // separar la seleccion multiple de estatus
    if (is_array($estatus)) {
        $estatus= implode(",", $estatus);
    }
    if (!empty($fechaini) && !empty($fechafin)) {
        $condicion.= " AND purchorders.requisitionno!= 0 AND purchorders.requisitionno!= '' AND purchorders.orddate>= '".$fechaini." 00:00:00' AND purchorders.orddate<='".$fechafin." 23:59:59' ";
    }

    if (!empty($fechaini) && empty($fechafin)) {
        $condicion.= " AND purchorders.requisitionno!= 0 AND purchorders.requisitionno!= '' AND purchorders.orddate>= '".$fechaini." 00:00:00'";
    }

    if (!empty($fechafin) && empty($fechaini)) {
        $condicion.= " AND purchorders.requisitionno!= 0 AND purchorders.requisitionno!= '' AND purchorders.orddate<='".$fechafin." 23:59:59' ";
    }

    if (!empty($datosDependencia)) {
        $condicion .= " AND tags.legalid IN (".$datosDependencia.") ";
    }

    if ($datosUR != "''") {
        $condicion.= " AND tags.tagref IN (".$datosUR.") ";
    } 
    /*else {
        //$condicion.= " AND tags.tagref IN (SELECT tagref FROM sec_unegsxuser WHERE userid= '".$_SESSION["UserID"]."') ";
        $condicion.= " 1 = 1 ";
    }*/

    if (!empty($unidadeje) && !strpos("@".$unidadeje, "-1")) {
        $condicion.= " AND purchorders.nu_ue = '".$unidadeje."' ";
    }

    if (!empty($idproveedor)) {
        $condicion.= " AND purchorders.supplierno LIKE '%".$idproveedor."%' ";
    }

    if (!empty($nomproveedor)) {
        $condicion.= " AND suppliers.suppname LIKE '%".$nomproveedor."%' ";
    }

    if (!empty($codigoExpediente)) {
        $condicion.= " AND purchorders.ln_codigo_expediente LIKE '%".$codigoExpediente."%' ";
    }

    if (!empty($estatus) && !strpos("@".$estatus, "-1")) {
        $condicion.= " AND purchorders.status IN (".$estatus.") ";
    }

    if (!empty($idrequisicion) && intval($idrequisicion)!= 0) {
        $condicion= " purchorders.requisitionno= '".$idrequisicion."' ";
    }

    // Consulta para extraer los datos para el panel
    $consulta = "SELECT 
    SUM(CASE WHEN stockmaster.stockid IS NULL THEN 0 ELSE (purchorderdetails.unitprice*purchorderdetails.quantityord)*(1-(discountpercent1/100))*(1-(discountpercent2/100))*(1-(discountpercent3/100)) END) AS ordervalue,
    purchorders.requisitionno,
    purchordersOrder.orderno as orderno,
    '' as status,
    tags.tagref as ur,
    purchorders.nu_ue,
    DATE_FORMAT(purchorders.deliverydate,'%d/%m/%Y') as fecharequerida,
    purchorders.comments,
    tags.legalid
    FROM purchorders
    LEFT JOIN (
    SELECT MIN(orderno) as orderno, requisitionno FROM purchorders GROUP BY requisitionno
    ) as purchordersOrder ON purchordersOrder.requisitionno = purchorders.requisitionno
    LEFT JOIN purchorderdetails ON purchorders.orderno = purchorderdetails.orderno AND purchorderdetails.status NOT IN(0,3)
    -- INNER JOIN tb_botones_status ON purchorders.status= tb_botones_status.statusname AND sn_funcion_id= '2265' 
    INNER JOIN tags on purchorders.tagref=tags.tagref
    LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = purchorders.tagref AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = purchorders.tagref AND tb_sec_users_ue.ue = purchorders.nu_ue AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."'
    WHERE
    ".$condicion."
    GROUP BY 
    requisitionno,
    orderno,
    status,
    ur,
    DATE_FORMAT(purchorders.deliverydate,'%d/%m/%Y'),
    purchorders.comments
    ORDER BY CAST(purchorders.requisitionno AS SIGNED) DESC";
    $ErrMsg = "No se pudo obtener la consulta de requisiciones";
    $resultado = DB_query($consulta, $db, $ErrMsg);
    while ($registro= DB_fetch_array($resultado)) {
        // Obtener estatus
        // $SQL = "SELECT status FROM purchorders WHERE orderno = '".$registro['orderno']."'";
        $SQL = "SELECT purchorders.status, tb_botones_status.sn_mensaje_opcional
        FROM purchorders 
        LEFT JOIN tb_botones_status ON tb_botones_status.statusname = purchorders.status AND tb_botones_status.sn_funcion_id IN (2365, 1371)
        WHERE purchorders.orderno = '".$registro['orderno']."'";
        $result = DB_query($SQL, $db, $ErrMsg);
        while ($row= DB_fetch_array($result)) {
            if (trim($row['sn_mensaje_opcional']) != '') {
                // Si no esta vacio
                $registro['status'] = $row['sn_mensaje_opcional'];
            } else {
                $registro['status'] = $row['status'];
            }
        }

        // Validar si el estatus es el de la compra
        $SQL = "SELECT sn_funcion_id FROM tb_botones_status WHERE sn_funcion_id = '1371' AND statusname = '".$registro['status']."'";
        $result = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($result) > 0) {
            $registro['status'] = 'Autorizado';
        }

        $enc = new Encryption;
        $url = "&ModifyOrderNumber=>" . $registro["orderno"] . "&idrequisicion=> ". $registro["requisitionno"];
        $url = $enc->encode($url);
        $liga= "URL=" . $url;

        $seleccionar= '<input type="checkbox" id="checkbox_'.$registro['requisitionno'].'" name="checkbox_'.$registro['requisitionno'].'" title="Seleccionar" value="'.$registro['requisitionno'].'" />';

        // Liga para impresión
        $enc = new Encryption;
        $url = "&OrderNo=>".$registro['orderno']."&tipodocto=>555&Tagref=>".$registro['ur']."&legalid=>".$registro['legalid'];
        $url = $enc->encode($url);
        $ligaImpresion= "URL=" . $url;

        $imprimir = "<a id='idBtnImpresionOC' target='_blank' href='./impresionRequisicion.php?".$ligaImpresion."'><span class='glyphicon glyphicon glyphicon-print'></span></a><br>";

        $info[] = array(
                'id1'=>false,
                "idrequisicion"=> "<a target='_self' href='./Captura_Requisicion_V_4.php?$liga' style='color: blue; '><u>".$registro["requisitionno"]."</u></a>",
                //"idrequisicion"=> "<span  style='color: blue; ' onclick='location=\"./Captura_Requisicion_V_3.php?$liga\"'><u>".$registro["requisitionno"]."</u></span>",
                //"idrequisicion"=> $registro["requisitionno"],
                "idrequisicionH" => $registro["orderno"],
                "numerorequisicion" => $registro["requisitionno"],
                "idproveedor" => '', //$registro["supplierid"],
                "ur" => $registro["ur"],
                "ue" => $registro["nu_ue"],
                "nombreproveedor" => '', //utf8_encode($registro["suppname"]),
                "estatus" => $registro['status']!="ProceCompra" ? $registro["status"] : "En Proceso de Compra",
                "totalrequisicion" => $registro["ordervalue"],
                "seleccionar" => $seleccionar,
                "fecharequerida" => $registro["fecharequerida"],
                "observaciones" => ($registro["comments"]),
                "imprimir" => $imprimir
            );
    }
    $sqlFinal = $consulta;
    $contenido = array('datosCatalogo' => $info);
    $result = true;
}

// Petición para cancelar una requisición
if ($option == 'cancelarRequisicion') {
    $req = $_POST['noReq'];
    $arrayreq = implode(",", $req);
    $status = 'Cancelado';
    $info = array();

    $SQLLocstock = "SELECT defaultlocation FROM www_users WHERE userid= '".$_SESSION['UserID']."'";
    $ErrMsgLocstock = "No se obtuvo informacion";
    $TransResultLocstock = DB_query($SQLLocstock, $db, $ErrMsgLocstock);
    while ($myrowLocstock = DB_fetch_array($TransResultLocstock)) {
        $idLocstock = $myrowLocstock['defaultlocation'];
    }

    // Obtener número de requisición para cancelacion de no existencia
    $requisitionno = "";
    $SQL = "SELECT requisitionno FROM purchorders WHERE orderno IN (".$arrayreq.")";
    $TransResult = DB_query($SQL, $db, $ErrMsgLocstock);
    while ($myrow = DB_fetch_array($TransResult)) {
        if ($requisitionno == '') {
            $requisitionno = "'".$myrow['requisitionno']."'";
        } else {
            $requisitionno .= ", '".$myrow['requisitionno']."'";
        }
    }

    // primero se actualiza la No Existencia
    $SQLNoExistenciaStatus = "UPDATE tb_no_existencias SET status = 0 WHERE nu_id_requisicion in (".$requisitionno.") and nu_id_no_existencia != 0";
    $ErrMsgNoExistenciaStatus = "error al cancelar la no existencia";
    $TransNoExistenciaStatus = DB_query($SQLNoExistenciaStatus, $db, $ErrMsgNoExistenciaStatus);

    $SQLFindSolAlmacen = "SELECT nu_folio, nu_id_requisicion, nu_tag ,ln_ue, ln_clave_articulo, txt_descripcion, nu_cantidad, ln_ontransit 
    FROM tb_solicitudes_almacen 
    INNER JOIN tb_solicitudes_almacen_detalle ON (tb_solicitudes_almacen.nu_folio = tb_solicitudes_almacen_detalle.nu_id_solicitud ) 
    WHERE tb_solicitudes_almacen.nu_id_requisicion in (".$arrayreq.") AND ln_arctivo = 1 AND ln_renglon != 0";
    $ErrMsgFindSolAlmacen = "Error en la consulta";
    $TransResultFindSolAlmacen = DB_query($SQLFindSolAlmacen, $db, $ErrMsgFindSolAlmacen);
    while ($rowRemoveOntransit= DB_fetch_array($TransResultFindSolAlmacen)) {
        $item = $rowRemoveOntransit['ln_clave_articulo'];
        $qty = $rowRemoveOntransit['nu_cantidad'];
        $folioSolAlmacen = $rowRemoveOntransit['nu_folio'];
        $ln_ontransit = $rowRemoveOntransit['ln_ontransit'];

        if ($ln_ontransit > 0 || $ln_ontransit !== 'undefined' || $ln_ontransit != '' || $ln_ontransit != null) {
            $SQLCancelarOntransit = "UPDATE locstock SET ontransit = (ontransit - ".$qty.") WHERE stockid = '".$item."' AND loccode = ".$idLocstock;
            $ErrMsgCancelarOntransit = "No se obtuvieron los botones para el proceso";
            $TransResultCancelarOntransit = DB_query($SQLCancelarOntransit, $db, $ErrMsgCancelarOntransit);
        }
        //print_r($SQLCancelarOntransit);
    }

    $SQL = "UPDATE tb_solicitudes_almacen SET estatus = 0, ln_nombre_estatus = 'Cancelada'  WHERE nu_id_requisicion in (".$arrayreq.") and nu_folio = '$folioSolAlmacen' and nu_id_solicitud != 0";
    $ErrMsg = "error al cancelar la solicitud al almacén";
    $Trans = DB_query($SQL, $db, $ErrMsg);

    //actualizar anexo tecnico vinculado a la requisicion
    $SQLDelAnexo = "UPDATE tb_cnfg_anexo_tecnico 
                    SET nu_requisicion = '0', ind_status = 2, nu_orden_requisicion = 0 
                    WHERE nu_requisicion in (".$arrayreq.") AND nu_tagref = '$ur' AND nu_ue = '$ue' ";

    $ErrMsgDelAnexo = "Problemas para modificar el anexo tecnico";
    $TransResultDelAnexo = DB_query($SQLDelAnexo, $db, $ErrMsgDelAnexo);

    // modificar estatus de requisicion a cancelada
    $SQL = "UPDATE purchorders SET status = '$status', fecha_modificacion = current_timestamp(), nu_anexo_tecnico = 0 
            WHERE orderno  in (".$arrayreq.")";
            
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido = "Se cancelan los elementos seleccionados y los procesos integrados ";
    $result = true;
}

// Opcion que guarda los datos de la requisicion una vez autorizada
if ($option == 'autorizarRequisicion') {
    $arrayreq = $_POST['noReq'];
    $mensajeError = "";
    $itemTipe = array();
    if (is_array($_POST['noReq'])) {
        $arrayreq = implode(",", $_POST['noReq']);
    }
    $status = 'ProceCompra';
    $info = array();
    $SQLRequisitionno = "SELECT requisitionno FROM purchorders WHERE orderno in (".$arrayreq.") ";
    /*$SQLRequisitionno = "SELECT stockmaster.stockid, purchorderdetails.itemcode, stockmaster.mbflag FROM purchorders
    INNER JOIN purchorderdetails on(purchorders.orderno = purchorderdetails.orderno ) 
    INNER JOIN stockmaster on (stockmaster.stockid = purchorderdetails.itemcode )
    WHERE purchorders.orderno in (".$arrayreq.") ";
    $ErrMsgRequisitionno = "No se encontro numero de requisición";
    $TransResultRequisitionno = DB_query($SQLRequisitionno, $db, $ErrMsgRequisitionno);
    while ($myRowRequisitionno = DB_fetch_array($TransResultRequisitionno)) {
        $requisitionno = $myRowRequisitionno['requisitionno'];
        $itemTipe = $myRowRequisitionno['mbflag'];
        $mensajeError .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;Es necesario generar una no existencia para la requisicion '. $requisitionno . '</p>';
    }*/
    $ErrMsgRequisitionno = "No se encontro numero de requisición";
    $TransResultRequisitionno = DB_query($SQLRequisitionno, $db, $ErrMsgRequisitionno);
    while ($myRowRequisitionno = DB_fetch_array($TransResultRequisitionno)) {
        $requisitionno = $myRowRequisitionno['requisitionno'];
        $mensajeError .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;Es necesario generar una no existencia para la requisicion '. $requisitionno . '</p>';
    }

    $errorValidacion = 0;
    // Validar que si tiene bienes
    $tieneBienes = 0;
    $SQL = "SELECT stockmaster.mbflag,
    purchorderdetails.quantityord,
    tb_solicitudes_almacen_detalle.nu_cantidad
    FROM purchorderdetails
    JOIN purchorders ON purchorders.orderno = purchorderdetails.orderno
    JOIN stockmaster ON stockmaster.stockid = purchorderdetails.itemcode
    LEFT JOIN tb_solicitudes_almacen_detalle ON tb_solicitudes_almacen_detalle.ln_ontransit = purchorderdetails.podetailitem
    WHERE purchorders.orderno IN (".$arrayreq.") AND purchorderdetails.status = 2";
    $ErrMsgRequisitionno = "No se obtuvieron para bienes o servicios";
    $result = DB_query($SQL, $db, $ErrMsgRequisitionno);
    while ($myrow = DB_fetch_array($result)) {
        if ($myrow['mbflag'] == 'B') {
            // Tiene bienes
            $tieneBienes = 1;
        }
    }

    if ($tieneBienes == 1) {
        // Si la requisición tiene bienes
        // $SQLNoE = "SELECT nu_id_no_existencia FROM tb_no_existencias WHERE nu_id_requisicion in (".$SQLRequisitionno.") AND status = 1 ";
        // $ErrMsgNoE = "No se encontro la no existencia de la requisicion";
        // $TransResultNoE = DB_query($SQLNoE, $db, $ErrMsgNoE);
        // if (!DB_fetch_array($TransResultNoE)) {
        //     $errorValidacion = 1;
        // }

        $SQLNoE = "SELECT tb_no_existencias.nu_id_no_existencia
        FROM tb_no_existencias
        JOIN tb_no_existencia_detalle ON tb_no_existencia_detalle.nu_id_no_existencia = tb_no_existencias.nu_id_no_existencia
        WHERE tb_no_existencias.nu_id_requisicion in (".$SQLRequisitionno.") AND tb_no_existencias.status = 1 AND tb_no_existencia_detalle.ln_activo = 1
        ";
        $ErrMsgNoE = "No se encontro la no existencia de la requisicion";
        // Se comenta validacion, no tomar en cuenta solicitud de la no existencia
        /*$TransResultNoE = DB_query($SQLNoE, $db, $ErrMsgNoE);
        if (DB_num_rows($TransResultNoE) == 0) {
            $errorValidacion = 1;
        }*/
    }
    

    if ($errorValidacion == 1) {
        $contenido = $mensajeError;
        $result = false;
    } else {
        $type = 263;
        $transno = GetNextTransNo($type, $db);

        // Actualizacion de justificacion para informacion de solictudes
        $textoInformativo = '';
        $SQL = "SELECT
        purchorderdetails.itemcode,
        purchorderdetails.quantityord,
        purchorderdetails.ln_clave_iden,
        tb_solicitudes_almacen.nu_folio,
        tb_solicitudes_almacen_detalle.nu_cantidad,
        tb_solicitudes_almacen_detalle.nu_id_detalle,
        tb_no_existencias.nu_id_no_existencia,
        tb_no_existencia_detalle.nu_cantidad as solNoExis
        FROM purchorders
        JOIN purchorderdetails ON purchorderdetails.orderno = purchorders.orderno
        JOIN stockmaster ON stockmaster.stockid = purchorderdetails.itemcode
        LEFT JOIN tb_solicitudes_almacen ON tb_solicitudes_almacen.nu_id_requisicion = purchorders.orderno
        LEFT JOIN tb_solicitudes_almacen_detalle ON tb_solicitudes_almacen_detalle.nu_id_solicitud = tb_solicitudes_almacen.nu_folio AND tb_solicitudes_almacen_detalle.ln_ontransit = purchorderdetails.podetailitem
        LEFT JOIN tb_no_existencias ON tb_no_existencias.nu_id_requisicion = purchorders.requisitionno
        LEFT JOIN tb_no_existencia_detalle ON tb_no_existencia_detalle.nu_id_no_existencia = tb_no_existencias.nu_id_no_existencia AND tb_no_existencia_detalle.ln_renglon = purchorderdetails.orderlineno_
        WHERE 
        purchorders.orderno in (".$arrayreq.")
        AND purchorderdetails.status = 2
        AND stockmaster.mbflag = 'B'
        ";
        $ErrMsg5 = "No se encontro la no existencia de la requisicion";
        $TransResult5 = DB_query($SQL, $db, $ErrMsg5);
        $num = 1;
        while ($myRow5 = DB_fetch_array($TransResult5)) {
            if ($num == 1) {
                $textoInformativo .= 'NOTA:';
            }
            if ($num == 1 && $myRow5['nu_id_no_existencia'] != '') {
                $textoInformativo .= ' La requisición ha generado la No Existencia con folio '.$myRow5['nu_id_no_existencia'];
            }
            if ($num == 1 && $myRow5['nu_folio'] != '') {
                // Validar que tenga registros la solicitud al almacén
                $SQL = "SELECT nu_id_solicitud FROM tb_solicitudes_almacen_detalle WHERE nu_id_solicitud = '".$myRow5['nu_folio']."' and ln_arctivo = 1 AND nu_cantidad != 0";
                $ErrMsg5 = "Validar Registros de Solicitud al Almacén";
                $TransResult6 = DB_query($SQL, $db, $ErrMsg5);
                if (DB_num_rows($TransResult6) > 0) {
                    $textoInformativo .= ', la Solicitud al Almacén '.$myRow5['nu_folio'];
                }
            }
            if ($num == 1) {
                $textoInformativo .= ' y la Suficiencia Presupuestal '.$transno;
            }

            // Actualizar Identificador en la solitud al almancén
            $SQL = "UPDATE tb_solicitudes_almacen_detalle SET ln_clave_iden = '".$myRow5['ln_clave_iden']."'
            WHERE nu_id_detalle = '".$myRow5['nu_id_detalle']."'";
            $ErrMsgNoE = "No se actualizo el identificador en la solicitud al almancén";
            $result = DB_query($SQL, $db, $ErrMsgNoE);
            
            // $textoInformativo .= '\nArtículo:  '.$myRow5['itemcode'].': ';
            // $textoInformativo .= '\nCantidad Solicitada '.$myRow5['quantityord'].'';

            // if ($myRow5['nu_cantidad'] != '') {
            //     $textoInformativo .= '\nCantidad Solicitud Almacén '.$myRow5['nu_cantidad'].'';
            // }

            // if ($myRow5['solNoExis'] != '') {
            //     $textoInformativo .= '\nCantidad Solicitud No Existencia '.$myRow5['solNoExis'].'';
            // }

            $num ++;
        }

        if (trim($textoInformativo) != '') {
            $SQL = "UPDATE purchorders SET comments = CONCAT(comments, '. ', '".($textoInformativo)."') WHERE orderno IN ($arrayreq)";
            $ErrMsgNoE = "No se encontro la no existencia de la requisicion";
            $TransResultNoE = DB_query($SQL, $db, $ErrMsgNoE);
        }

        $SQLNoE = "UPDATE tb_no_existencias SET status = 2 WHERE nu_id_requisicion in (".$SQLRequisitionno.") AND status = 1 AND nu_id_no_existencia != 0";
        $ErrMsgNoE = "No se encontro la no existencia de la requisicion";
        $TransResultNoE = DB_query($SQLNoE, $db, $ErrMsgNoE);

        $SQL4="UPDATE  tb_solicitudes_almacen SET estatus='30', ln_nombre_estatus='En almacén' WHERE nu_id_requisicion in (".$arrayreq.") ";
        $ErrMsg4 = "No actualizao la solicitud del alamacen al autorizar requisicion";
        $TransResult4 = DB_query($SQL4, $db, $ErrMsg4);

        $SQL5 = "SELECT ne.nu_id_requisicion, ne.nu_id_no_existencia, nd.nu_id_no_existencia_detalle,  nd.ln_item_code, nd.nu_cantidad FROM tb_no_existencias ne 
        INNER JOIN tb_no_existencia_detalle nd on (ne.nu_id_no_existencia = nd.nu_id_no_existencia) WHERE ne.nu_id_requisicion in (".$arrayreq.")";
        $SQL5 = "SELECT
        purchorders.orderno,
        purchorderdetails.podetailitem,
        purchorderdetails.itemcode,
        IFNULL(tb_no_existencia_detalle.nu_cantidad, 0) as nu_cantidad
        FROM purchorders
        JOIN purchorderdetails ON purchorderdetails.orderno = purchorders.orderno
        JOIN tb_no_existencia_detalle ON tb_no_existencia_detalle.podetailitem = purchorderdetails.podetailitem AND tb_no_existencia_detalle.ln_activo = 1
        JOIN stockmaster ON stockmaster.stockid = purchorderdetails.itemcode
        WHERE
        purchorders.requisitionno in (".$requisitionno.")
        AND purchorderdetails.status = 2
        AND stockmaster.mbflag = 'B'
        ";
        $ErrMsg5 = "No se encontro la no existencia de la requisicion";
        $TransResult5 = DB_query($SQL5, $db, $ErrMsg5);
        while ($myRow5 = DB_fetch_array($TransResult5)) {
             $SQL6="UPDATE purchorderdetails SET quantityord = ".$myRow5 ['nu_cantidad']." WHERE podetailitem = '".$myRow5 ['podetailitem']."'";
            $ErrMsg6 = "No actualizo la cantidad de la requisicion";
            $TransResult6 = DB_query($SQL6, $db, $ErrMsg6);
        }

        // Actualizar orden original para visualizacion
        $SQL = "UPDATE purchorderdetails SET nu_original = orderlineno_ WHERE orderno IN ($arrayreq)";
        $ErrMsg6 = "No actualizo el orden original de la requisicion";
        $TransResult6 = DB_query($SQL, $db, $ErrMsg6);

        $SQL2 = "
        SELECT 
        SUM(pd.quantityord * pd.unitprice) AS total, 
        p.orderno as idreq, 
        p.requisitionno as noreq, 
        p.tagref as tagref,  
        pd.clavepresupuestal as cvepresupuestal, 
        cdbt.partida_esp as partida, 
        tb_botones_status.sn_estatus_anterior, 
        tb_botones_status.sn_funcion_id,
        p.nu_ue
        FROM purchorders p 
        JOIN purchorderdetails pd on (p.orderno = pd.orderno) 
        LEFT JOIN chartdetailsbudgetbytag cdbt on (p.tagref = cdbt.tagref and pd.clavepresupuestal = cdbt.accountcode ) 
        INNER JOIN tb_botones_status ON p.status= tb_botones_status.statusname AND tb_botones_status.sn_funcion_id = '2265'
        WHERE p.orderno IN ($arrayreq) AND pd.status = 2 AND pd.quantityord <> 0
        GROUP BY idreq, noreq, tagref, cvepresupuestal, partida, sn_estatus_anterior, sn_funcion_id, nu_ue
        ORDER BY noreq";

        $ErrMsg2 = "No se obtuvieron los botones para el proceso";
        $TransResult2 = DB_query($SQL2, $db, $ErrMsg2);
        while ($myrow = DB_fetch_array($TransResult2)) {
            $orderno = $myrow['idreq'];
            $tagref = $myrow['tagref'];
            $clave = $myrow['cvepresupuestal'];
            $total= $myrow['total'];//$myrow['cantidad']*$myrow['precio'];
            $partida_esp = $myrow['partida'];
            $description = "Autorización Requisición ".$myrow['noreq'];
            $ue = $myrow['nu_ue'];
            // Panel Suficiencia
            $myrow['sn_funcion_id'] = 2302;
            // Estaus de Por Autorizar
            $myrow['statusid'] = 4;

            // suficiencia automatica
            $validacion2 = fnInsertPresupuestoLog(
                $db,
                $type,
                $transno,
                $tagref,
                $clave,
                $periodo,
                $total * -1,
                263,
                $partida_esp,
                $description,
                0,
                $myrow['statusid'],
                $myrow['sn_funcion_id'],
                $ue
            );
            $ordenOperacion = 'DESC';
            $movimientoTipo = '';
            // $respuesta = fnInsertPresupuestoLogAcomulado($db, $type, $transno, $tagref, $clave, $periodo, $total * -1, 263, $partida_esp, $description, 0, $myrow['statusid'], $myrow['sn_funcion_id'], $ue, $ordenOperacion, 'disponible', $movimientoTipo, 'Reduccion', 1, '', '', 0);

            // precomprometido
            $validacion = fnInsertPresupuestoLog(
                $db,
                $type,
                $transno,
                $tagref,
                $clave,
                $periodo,
                $total * -1,
                258,
                $partida_esp,
                $description,
                1,
                '',
                0,
                $ue
            );
            $ordenOperacion = 'DESC';
            $movimientoTipo = '';
            // $respuesta = fnInsertPresupuestoLogAcomulado($db, $type, $transno, $tagref, $clave, $periodo, $total * -1, 258, $partida_esp, $description, 1, '', 0, $ue, $ordenOperacion, 'disponible', $movimientoTipo, 'Reduccion', 1, '', '', 0);

            fnAgregarSuficienciaGeneral($db, $type, $transno, "Automática", $myrow['statusid'], $myrow['tagref'], 1, $myrow['sn_funcion_id'], $orderno, $ue);
            $SQL3="UPDATE chartdetailsbudgetlog  SET estatus='".$myrow['statusid'] ."' WHERE transno=$transno and type='".$type."'";
            $ErrMsg3 = "No actualizao el log ";
            $TransResult3 = DB_query($SQL3, $db, $ErrMsg3);

            $SQL = "UPDATE purchorders SET status = '$status', fecha_modificacion = current_timestamp(), `nu_periodo` = '$periodo' WHERE orderno  in ($arrayreq)";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
        }

        $ordenes = ( is_array($_POST['noReq']) ? $_POST['noReq'] : [ $_POST['noReq'] ] );

        foreach($ordenes AS $orderNo){
            if($orderNo){
                // Se obtiene la Partida Específica
                $sql = "SELECT pa.`partidaEspecifica` AS `partidaEspecifica`, SUM(pod.`unitprice` * pod.`quantityord`) AS `montoRequisicion`, `po`.`requisitionno`, po.ln_codigo_expediente, po.comments

                        FROM `purchorderdetails` AS pod
                        INNER JOIN `purchorders` AS po ON po.`orderno` = pod.`orderno`
                        LEFT JOIN `stockmaster` AS sm ON sm.`stockid` LIKE pod.`itemcode`
                        LEFT JOIN `tb_partida_articulo` AS pa ON pa.`eq_stockid` = sm.`eq_stockid`

                        WHERE pod.`orderno` = '$orderNo'

                        GROUP BY pod.`orderno`";
                $datosRequisicion = DB_fetch_array(DB_query($sql, $db));

                // Se obtienen UR Y UE
                $sql = "SELECT `tagref` AS `UR`, `nu_ue` AS `UE` FROM `purchorders` WHERE `orderno` = '$orderNo'";
                $URUE = DB_fetch_array(DB_query($sql, $db));

                // Se obtiene Folio de Proceso de Compra
                $sql = "SELECT CONCAT(YEAR(NOW()),'$URUE[UR]','$URUE[UE]',LPAD(COUNT(`id_nu_ue`)+1,6,0)) AS `Folio`

                        FROM `tb_proceso_compra`

                        WHERE YEAR(`dtm_fecha_creacion`) = YEAR(NOW())
                        AND `tagref` LIKE '$URUE[UR]'
                        AND `id_nu_ue` LIKE '$URUE[UE]'";

                $folioCompra = DB_fetch_array(DB_query($sql, $db))['Folio'];

                // Nuevo código para verificar si el registro ya existe, y de ser así hacer UPDATE en lugar de INSERT
                $sql = "SELECT *

                        FROM `tb_proceso_compra`

                        WHERE `orderno` = '$orderNo'
                        AND `requisitionno` LIKE '$datosRequisicion[requisitionno]'
                        AND `tagref` LIKE '$URUE[UR]'
                        AND `id_nu_ue` LIKE '$URUE[UE]'";

                $id_nu_compra = DB_fetch_array(DB_query($sql, $db))['id_nu_compra'];

                if(!$id_nu_compra){
                    $sql = "INSERT INTO `tb_proceso_compra`(`orderno`,`requisitionno`,`sn_folio_compra`,`tagref`,`id_nu_ue`,`ln_partida_especifica`,`sn_monto_requisicion`,`sn_monto_compra`,`dtm_fecha_creacion`,`dtm_fecha_modificacion`, ln_codigo_expediente, ln_observaciones)
                            SELECT `orderno`, `requisitionno`, '$folioCompra' AS `folioCompra`, `tagref`, `nu_ue`, '$datosRequisicion[partidaEspecifica]' AS `partidaEspecifica`, '$datosRequisicion[montoRequisicion]' AS `montoRequisicion`, '$datosRequisicion[montoRequisicion]' AS `montoCompra`, NOW(), NOW(), '$datosRequisicion[ln_codigo_expediente]', '$datosRequisicion[comments]' FROM `purchorders` WHERE `orderno` = '$orderNo'";
                }else{
                    $sql = "UPDATE `tb_proceso_compra` SET `ln_partida_especifica` = '$datosRequisicion[partidaEspecifica]',`sn_monto_requisicion` = '$datosRequisicion[montoRequisicion]',`sn_monto_compra` = '$datosRequisicion[montoRequisicion]',`dtm_fecha_modificacion` = NOW(), `ind_activo` = '1', ln_codigo_expediente = '$datosRequisicion[ln_codigo_expediente]', ln_observaciones = '$datosRequisicion[comments]'
                    WHERE `id_nu_compra` = '$id_nu_compra'";
                }

                DB_query($sql, $db);
            }
        }
        
        $result = true;
        $contenido = "Se autorizan los elementos seleccionados ";
    }
}

if ($option == 'buscarPerfilUsr') {
    $info = array();
    //$SQL = "SELECT userid FROM sec_profilexuser WHERE profileid IN (9,10,11)";
    $SQL = "SELECT userid, profileid FROM sec_profilexuser WHERE userid = '".$_SESSION['UserID']."'";
    $ErrMsg = "No se encontro un perfil";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'userid' => $myrow ['userid'],
            'profileid' => $myrow ['profileid']
        );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'diferenciasRequisicion') {
    $idreq = $_POST['idReq'];
    
    $textoInformativo = '';
    // Actualizacion de justificacion para informacion de solictudes
    $SQL = "SELECT
    purchorders.requisitionno,
    purchorderdetails.itemcode,
    purchorderdetails.quantityord,
    tb_solicitudes_almacen.nu_folio,
    tb_solicitudes_almacen_detalle.nu_cantidad,
    tb_no_existencias.nu_id_no_existencia,
    tb_no_existencia_detalle.nu_cantidad as solNoExis
    FROM purchorders
    JOIN purchorderdetails ON purchorderdetails.orderno = purchorders.orderno
    JOIN stockmaster ON stockmaster.stockid = purchorderdetails.itemcode
    LEFT JOIN tb_solicitudes_almacen ON tb_solicitudes_almacen.nu_id_requisicion = purchorders.orderno
    LEFT JOIN tb_solicitudes_almacen_detalle ON tb_solicitudes_almacen_detalle.nu_id_solicitud = tb_solicitudes_almacen.nu_folio AND tb_solicitudes_almacen_detalle.ln_ontransit = purchorderdetails.podetailitem
    LEFT JOIN tb_no_existencias ON tb_no_existencias.nu_id_requisicion = purchorders.orderno
    LEFT JOIN tb_no_existencia_detalle ON tb_no_existencia_detalle.nu_id_no_existencia = tb_no_existencias.nu_id_no_existencia AND tb_no_existencia_detalle.ln_renglon = purchorderdetails.orderlineno_
    WHERE 
    purchorders.orderno = '".$idreq."'
    AND purchorderdetails.status = 2
    AND stockmaster.mbflag = 'B'
    AND purchorderdetails.quantityord != tb_no_existencia_detalle.nu_cantidad
    ";
    $ErrMsg5 = "No se encontro la no existencia de la requisicion";
    $TransResult5 = DB_query($SQL, $db, $ErrMsg5);
    $num = 1;
    while ($myRow5 = DB_fetch_array($TransResult5)) {
        if ($num == 1) {
            $textoInformativo .= 'NOTA:';
        }
        if ($num == 1 && $myRow5['nu_id_no_existencia'] != '') {
            $textoInformativo .= ' La requisición a generado la No Existencia con folio '.$myRow5['nu_id_no_existencia'];
        }
        if ($num == 1 && $myRow5['nu_folio'] != '') {
            $textoInformativo .= ', la Solicitud al Almacén '.$myRow5['nu_folio'];
        }
        // if ($num == 1) {
        //     $textoInformativo .= ' y la Suficiencia Presupuestal '.$transno;
        // }

        // $textoInformativo .= '<br>Artículo:  '.$myRow5['itemcode'].': ';
        // $textoInformativo .= '<br>Cantidad Solicitada '.$myRow5['quantityord'].'';

        // if ($myRow5['nu_cantidad'] != '') {
        //     $textoInformativo .= '<br>Cantidad Solicitud Almacén '.$myRow5['nu_cantidad'].'';
        // }

        // if ($myRow5['solNoExis'] != '') {
        //     $textoInformativo .= '<br>Cantidad No Existencia '.$myRow5['solNoExis'].'';
        // }

        $num ++;
    }
    $contenido = $textoInformativo;
    $result = true;
}

if ($option == 'statusRequisicion') {
    $idreq = $_POST['idReq'];
    //$req = implode(",", $idreq);
    
    $info = array();
    $SQL = "SELECT orderno,status FROM purchorders WHERE orderno = '$idreq'";
    $ErrMsg = "No se obtuvieron los estatus para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'orderno' => $myrow ['orderno'],
            'status' => $myrow ['status']
        );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'avanzarRequisicion') {
    $arrayreq = $_POST['noReq'];
    
    if (is_array($_POST['noReq'])) {
        $arrayreq = implode(",", $_POST['noReq']);
    }
    
    $info = array();

    $SQL = "SELECT orderno, requisitionno, status, 
            if(sn_estatus_anterior=1,'Capturado',if(sn_estatus_anterior=2,'Validar',if(sn_estatus_anterior=3,'PorAutorizar',if(sn_estatus_anterior=4,'Autorizado',0)))) as sn_estatus_anterior, 
            if(sn_estatus_siguiente=1,'Capturado',if(sn_estatus_siguiente=2,'Validar',if(sn_estatus_siguiente=3,'PorAutorizar',if(sn_estatus_siguiente=4,'Autorizado',0)))) as sn_estatus_siguiente
            FROM purchorders p 
            JOIN tb_botones_status tbs on (p.status = tbs.statusname) AND tbs.sn_funcion_id IN (2265,1371)
            WHERE orderno in ($arrayreq)";

    $TransResult = DB_query($SQL, $db);

    while ($myrow = DB_fetch_array($TransResult)) {
        $idreq = $myrow['orderno'];
        $statusNuevo = $myrow['sn_estatus_siguiente'];
        
        $SQL2 = "UPDATE purchorders SET status = '$statusNuevo' WHERE orderno = '$idreq'";
        $ErrMsg2 = "No se pudo reindexar";
        $TransResult2 = DB_query($SQL2, $db, $ErrMsg2);
    }

    $contenido = "Se Avanzó la Requisición ";
    $result = true;
}

// Opcion para rechazar requisicion seleccionada
if ($option == 'rechazarRequisicion') {
    $arrayreq = $_POST['noReq'];
    
    if (is_array($_POST['noReq'])) {
        $arrayreq = implode(",", $_POST['noReq']);
    }

    $info = array();
    $SQL = "SELECT 
    orderno, requisitionno, status, 
    if(sn_estatus_anterior=1,'Capturado',if(sn_estatus_anterior=2,'Validar',if(sn_estatus_anterior=3,'PorAutorizar',if(sn_estatus_anterior=4,'Autorizado',0)))) as sn_estatus_anterior, 
    if(sn_estatus_siguiente=1,'Capturado',if(sn_estatus_siguiente=2,'Validar',if(sn_estatus_siguiente=3,'PorAutorizar',if(sn_estatus_siguiente=4,'Autorizado',0)))) as sn_estatus_siguiente
    FROM purchorders p 
    JOIN tb_botones_status tbs on (p.status = tbs.statusname) 
    WHERE orderno in ($arrayreq)";

    $ErrMsg = "No se pudo rechazar las requisiciones seleccionadas";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $idreq = $myrow['orderno'];
        $statusNuevo = $myrow['sn_estatus_anterior'];
        if ($statusNuevo != '') {
            $SQL2 = "UPDATE purchorders SET status = '$statusNuevo' WHERE orderno = '$idreq'";
            $ErrMsg2 = "No se pudo rechazar la requisición";
            $TransResult2 = DB_query($SQL2, $db, $ErrMsg2);
        } else {
            $contenido = "No se Cancelo la Requisición ";
            $result = false;
        }
    }

    $contenido = "Se Rechazarón las requisiciones seleccionadas";
    $result = true;
}

// opcion que valida la existencia y presupuesto disponible para la requisicion
if ($option == 'validarRequisicion') {
    $req = $_POST['idReq'];
    $ur = $_POST['ur'];
    $nombreMes = "";
    $contenido = "";
    $result = true;
    ///// Se remueve la reasignación de periodo
    /////$periodo = GetPeriod(date('d/m/Y'), $db);
    $disponiblepartida= array();
    $visualOrden = 0;

    $SQLMes = "SELECT periods.periodno, periods.lastdate_in_period, cat_Months.mes as mesName
                FROM periods 
            LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
            WHERE periods.periodno = '$periodo'
            ORDER BY periods.lastdate_in_period asc"; ///// ESTA LÍNEA SE REMOVIÓ DEL WHERE periods.lastdate_in_period like '%$myrow[anho]%' AND 

    $ErrMsgMes = "No se obtuvo informacion";
    $resultPeriods = DB_query($SQLMes, $db, $ErrMsgMes);
    while ($rowPeriods = DB_fetch_array($resultPeriods)) {
        $nombreMes = $rowPeriods ['mesName'];
    }

    $SQL = "SELECT p.orderno as idReq, p.requisitionno as noReq, p.status as statusReq, p.tagref as tagref, p.validfrom, p.validto,
            pd.itemcode as itemcode, pd.itemdescription as itemdescription, pd.unitprice as precio, pd.quantityord as cantidad, pd.orderlineno_, pd.clavepresupuestal as clavepre, pd.sn_descripcion_larga, pd.status as statusItem, pd.renglon, sm.mbflag as mbflag
            FROM purchorders p
            JOIN purchorderdetails pd ON (p.orderno = pd.orderno)
            JOIN stockmaster sm ON ( pd.itemcode = sm.stockid)         
            WHERE p.orderno = $req AND pd.status NOT IN(0,3)";

    $ErrMsg = "No se obtuvo informacion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $visualOrden++;
        $idrequi = $myrow ['idReq'];
        $tagref = $myrow ['tagref'];
        $cvepre = $myrow ['clavepre'];
        $mbflag = $myrow ['mbflag'];
        $itemcode = $myrow['itemcode'];
        $nombre_producto= $myrow['itemdescription'];
        $partida_producto= $myrow['orderlineno_'];
        $precio = $myrow ['precio'];
        $cantidad = $myrow ['cantidad'];
        $tot = $precio * $cantidad;

        //if (substr($itemcode, 1, 1) != "8") {
        if ($mbflag != 'D' && ($cantidadSol!=0 &&(!is_null($cantidadSol)))) {
            // validar existencia de almacen con la cantidad solicitada
            /*$SQL2 = "SELECT (quantity - ontransit) as quantity
                    FROM locstock 
                    INNER JOIN sec_loccxusser USING (loccode)
                    where stockid = '".$myrow ['itemcode']."'
                    AND sec_loccxusser.userid= '".$_SESSION["UserID"]."'";*/
            $SQL2="SELECT (quantity - ontransit) as quantity
                    FROM locstock 
                    INNER JOIN sec_loccxusser ON (locstock.loccode = sec_loccxusser.loccode)
                    INNER JOIN www_users ON (sec_loccxusser.userid = www_users.userid and sec_loccxusser.loccode = www_users.defaultlocation)
                    INNER JOIN sec_unegsxuser ON (sec_unegsxuser.userid = www_users.userid)
                    where stockid = '".$myrow ['itemcode']."'
                    AND sec_loccxusser.userid= '".$_SESSION["UserID"]."' and tagref= '".$tagref."'";

            $ErrMsg2 = "No se obtuvo informacion";
            $TransResult2 = DB_query($SQL2, $db, $ErrMsg2);

            while ($myrowqty = DB_fetch_array($TransResult2)) {
                $existencia = $myrowqty['quantity'];
                $disp = $existencia - $cantidad;
                if ($disp > 0) {
                    $result = true;

                    // if (empty($myrow ['noReq'])) {
                    //     $contenido.= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;La cantidad de bienes solicitada en el renglón '.$visualOrden.' tiene existencia en el almacén.</p>';
                    // } else {
                    //     $contenido.= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;La cantidad de bienes solicitada en la requisición '.$myrow['noReq'].' en el renglón '.$visualOrden.' tiene existencia en el almacén.</p>';
                    // }
                }
            }
        }
            
        $infopre = fnInfoPresupuesto($db, $cvepre, $periodo);

        $disponiblepartida[$cvepre]+= $tot;
        if (empty($infopre[0][$nombreMes.'Acomulado'])) {
            $SQLPrecomprometidos="SELECT * FROM chartdetailsbudgetlog WHERE tagref = '$ur' AND period = '$periodo' AND nu_tipo_movimiento = 258";
            $ErrMsgPrecomprometidos = "No se obtuvo informacion";
            $TransResultPrecomprometidos = DB_query($SQLPrecomprometidos, $db, $ErrMsgPrecomprometidos);
            if (DB_num_rows($TransResultPrecomprometidos) > 0) {
                $contenido.= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;La clave presupuestal '.$cvepre.' en el renglón '.$visualOrden.' no tiene recurso disponible en el mes en curso.</p>';
            } else {
                $contenido.= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;No se cuenta con disponible para la clave presupuestal  '.$cvepre.' en el mes en curso para el renglón '.$visualOrden.'.</p>';
            }
            //$contenido.= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;No se encontró información para la Clave Presupuestal '.$cvepre.' en lel renglón '.$partida_producto.'.</p>';
            $result = false;
        } else {
            if ($infopre[0][$nombreMes.'Acomulado'] >= $disponiblepartida[$cvepre]) {
                if ($result) {
                    $result = true;
                }
            } else {
                if (empty($myrow['noReq'])) {
                    //$contenido.= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;La partida '.$partida_producto.' no cuenta con presupuesto disponible.</p>';
                    $contenido.= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El importe total en el renglón '.$visualOrden.' excedió el disponible del mes en curso.</p>';
                } else {
                    $contenido.= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El importe total en la requisición '.$myrow['noReq'].' en el renglón '.$visualOrden.' excedió el disponible del mes en curso.</p>';
                }
                
                $result = false;
            }
        }
    }
}

if (empty($consulta)) {
    $consulta= $SQL;
}

$dataObj = array(
    'sql' => $sqlFinal,
    'contenido' => $contenido,
    'result' => $result,
    'RootPath' => $RootPath,
    'ErrMsg' => $ErrMsg,
    'Mensaje' => $Mensaje);
//echo json_encode($dataObj, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
echo json_encode($dataObj);
