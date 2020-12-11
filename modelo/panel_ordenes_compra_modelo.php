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
//
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
 
$PageSecurity = 2;
$funcion=1371;
$PathPrefix = '../';

session_start();

// incluir archivos de apoyo
include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');
include($PathPrefix."includes/SecurityUrl.php");

// declaracion de variables locales
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

if ($option == 'traeOrdenesCompras') {
    $info = array();
    $condicion= " 1=1 ";
    $condicionHaving = "";
    $fechaini= $_POST["fechainicio"];// date("Y-m-d", strtotime($_POST["fechainicio"]));
    $fechafin= $_POST["fechafin"];// date("Y-m-d", strtotime($_POST["fechafin"]));
    $dependencia= $_POST["dependencia"];
    $unidadres= $_POST["unidadres"];
    $idrequisicion= $_POST["requisicion"];
    $realorderno = $_POST['folioCompra'];
    $idproveedor= $_POST["idproveedor"];
    $nomproveedor= $_POST["nomproveedor"];
    $estatus= $_POST["estatus"];
    $codigoExpediente = $_POST['codigoExpediente'];
    //$estatus= 'Autorizado';
    $funcion= $_POST["funcion"];
    $ue = $_POST['ue'];

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

    // separar la seleccion multiple de las unidades ejecutoras
    $datosUE = "";
    foreach ($ue as $key) {
        if (empty($datosUE)) {
            $datosUE .= "'".$key."'";
        } else {
            $datosUE .= ", '".$key."'";
        }
    }

    // separar la seleccion multiple de los estatus
    $datosEstatus = "";
    $tamEstatus = count($estatus);
    foreach ($estatus as $key) {
        if ($key == 'Autorizado') {
            // Requisición Autorizada
            if (empty($condicionHaving)) {
                $condicionHaving .= " HAVING status = '".$key."' ";
            } else {
                $condicionHaving .= " OR status = '".$key."' ";
            }
        }
        if ($key == 'Pending') {
            // Recepción Parcial
            if (empty($condicionHaving)) {
                $condicionHaving .= " HAVING status = '".$key."' ";
            } else {
                $condicionHaving .= " OR status = '".$key."' ";
            }
        }
        if ($key == 'Authorised') {
            // Orden Autorizada
            if (empty($condicionHaving)) {
                $condicionHaving .= " HAVING status = '".$key."' ";
            } else {
                $condicionHaving .= " OR status = '".$key."' ";
            }
        }

        if ($key == 'Completed') {
            // Facturadas
            if (empty($condicionHaving)) {
                $condicionHaving .= " HAVING status = '".$key."' ";
            } else {
                $condicionHaving .= " OR status = '".$key."' ";
            }
            $condicionHaving .=  " AND (quantityord = qtyinvoiced) ";
        }

        if ($key == 'Completed_FacturaParcial') {
            $key = 'Completed';
            if (empty($condicionHaving)) {
                $condicionHaving .= " HAVING status = '".$key."' ";
            } else {
                $condicionHaving .= " OR status = '".$key."' ";
            }
            // $condicion.= " OR purchorderdetails.completed = '1' ";
            $condicionHaving .= " AND (quantityord <> qtyinvoiced AND qtyinvoiced > 0) ";
        }

        if ($key == 'Completed_CompraRecibida') {
            $key = 'Completed';
            if (empty($condicionHaving)) {
                $condicionHaving .= " HAVING status = '".$key."' ";
            } else {
                $condicionHaving .= " OR status = '".$key."' ";
            }
            $condicion.= " OR purchorderdetails.completed = '1' ";
            $condicionHaving .= " AND (qtyinvoiced = 0) ";
        }

        if (empty($datosEstatus)) {
            $datosEstatus .= "'".$key."'";
        } else {
            $datosEstatus .= ", '".$key."'";
        }
    }

    $condicion.= " AND purchorders.requisitionno!= 0 AND purchorders.requisitionno!= '' ";

    if (!empty($fechaini) && !empty($fechafin)) {
        $fechaini = date_create($fechaini);
        $fechaini = date_format($fechaini, 'Y-m-d');

        $fechafin = date_create($fechafin);
        $fechafin = date_format($fechafin, 'Y-m-d');

        $condicion .= " AND purchorders.orddate between '".$fechaini." 00:00:00' AND '".$fechafin." 23:59:59' ";
    } elseif (!empty($fechaini)) {
        $fechaini = date_create($fechaini);
        $fechaini = date_format($fechaini, 'Y-m-d');

        $condicion .= " AND purchorders.orddate >= '".$fechaini." 00:00:00' ";
    } elseif (!empty($fechafin)) {
        $fechafin = date_create($fechafin);
        $fechafin = date_format($fechafin, 'Y-m-d');

        $condicion .= " AND purchorders.orddate <= '".$fechafin." 23:59:59' ";
    }

    if (!empty($datosDependencia)) {
        $condicion .= " AND tags.legalid IN (".$datosDependencia.") ";
    }

    if (!empty($datosUR)) {
        $condicion.= " AND tags.tagref IN (".$datosUR.") ";
    } else {
        $condicion.= " AND tags.tagref IN (SELECT tagref FROM sec_unegsxuser WHERE userid= '".$_SESSION["UserID"]."') ";
    }

    if (!empty($datosUE)) {
        $condicion .= " AND purchorders.nu_ue IN (".$datosUE.") ";
    }

    if (!empty($idproveedor)) {
        $condicion.= " AND purchorders.supplierno LIKE '%".$idproveedor."%' ";
    }

    if (!empty($nomproveedor)) {
        $condicion.= " AND suppliers.suppname LIKE '%".$nomproveedor."%' ";
    }

    if (!empty($datosEstatus)) {
        $condicion.= " AND purchorders.status  IN (".$datosEstatus.") ";
    }

    if (!empty($idrequisicion) && intval($idrequisicion)!= 0) {
        // $condicion= " purchorders.requisitionno like '%".$idrequisicion."%' ";
        $condicion .= " AND purchorders.requisitionno = '".$idrequisicion."' ";
    }

    if (!empty($realorderno) && intval($realorderno)!= 0) {
        // $condicion= " purchorders.realorderno like '%".$realorderno."%' ";
        $condicion .= " AND purchorders.realorderno = '".$realorderno."' ";
    }

    if (!empty($codigoExpediente)) {
        $condicion .= " AND purchorders.ln_codigo_expediente LIKE '%".$codigoExpediente."%' ";
    }

    // Consulta para extraer los datos para el panel
    $consulta= "SELECT locationname,
				purchorders.orderno,
				IF(purchorders.supplierorderno IS NULL, 'NA', purchorders.supplierorderno) AS supplierorderno,
				suppliers.suppname,
				suppliers.supplierid,
				DATE_FORMAT(purchorders.orddate,'%d/%m/%Y') as orddate,
				purchorders.initiator,
				tb_botones_status.sn_nombre_secundario,
				purchorders.requisitionno,
				purchorders.allowprint,
				purchorders.tagref,
				purchorders.currcode,
				sum(case when purchorderdetails.quantityord < purchorderdetails.qtyinvoiced THEN 0 ELSE purchorderdetails.quantityord - purchorderdetails.qtyinvoiced end) as productosfacturados,
				SUM((purchorderdetails.unitprice*purchorderdetails.quantityord)*(1-(discountpercent1/100))*(1-(discountpercent2/100))*(1-(discountpercent3/100))) AS ordervalue,
				SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) as productospendientes,
				SUM(purchorderdetails.quantityrecd) as productosrecibidos,
				'' as foliofiscal,
				purchorders.wo,
				purchorders.autorizausuario,
				DATE_FORMAT(purchorders.autorizafecha,'%Y/%m/%d') as fechaauto,
				tags.legalid,
				tags.tagref,
				DATE_FORMAT(purchorders.deliverydate,'%d/%m/%Y') as fecharequerida,
				purchorders.comments, purchorders.realorderno,
                purchorderdetails.completed,
                SUM(purchorderdetails.quantityord) AS quantityord,
                SUM(purchorderdetails.qtyinvoiced) AS qtyinvoiced,
                purchorders.status,
                tb_botones_status.sn_funcion_id
				FROM purchorders
				INNER JOIN purchorderdetails ON purchorders.orderno = purchorderdetails.orderno AND purchorderdetails.status NOT IN(0,3)
                INNER JOIN tb_botones_status ON purchorders.status= tb_botones_status.statusname AND (tb_botones_status.sn_funcion_id= '".$funcion."' OR (tb_botones_status.sn_funcion_id=2265 AND tb_botones_status.statusname='Autorizado'))
				INNER JOIN tags on purchorders.tagref=tags.tagref
				LEFT JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
				INNER JOIN legalbusinessunit on tags.legalid=legalbusinessunit.legalid
				LEFT JOIN locations ON purchorders.intostocklocation = locations.loccode
				LEFT JOIN areas on areas.areacode=tags.areacode
                JOIN sec_unegsxuser ON sec_unegsxuser.tagref = purchorders.tagref AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
                JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = purchorders.tagref AND tb_sec_users_ue.ue = purchorders.nu_ue AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."'
				WHERE ".$condicion."
                GROUP BY locationname,
                purchorders.orderno,
                supplierorderno,
                suppliers.suppname,
                suppliers.supplierid,
                orddate,
                purchorders.initiator,
                tb_botones_status.sn_nombre_secundario,
                purchorders.requisitionno,
                purchorders.allowprint,
                purchorders.tagref,
                purchorders.currcode,
                foliofiscal,
                purchorders.wo,
                purchorders.autorizausuario,
                fechaauto,
                tags.legalid,
                tags.tagref,
                fecharequerida,
                purchorders.comments, purchorders.realorderno,
                purchorderdetails.completed,
                purchorders.status, tb_botones_status.sn_funcion_id
                ".$condicionHaving."
				ORDER BY CAST(purchorders.requisitionno AS SIGNED) DESC ";
    // INNER JOIN tb_botones_status ON purchorders.status= tb_botones_status.statusname AND tb_botones_status.sn_flag_disponible=1 AND (sn_funcion_id= '".$funcion."' OR (sn_funcion_id=2265 AND tb_botones_status.statusname='Autorizado'))
    // echo "<pre>".$consulta;
    // exit();
    $ErrMsg = "No se pudo obtener la consulta de requisiciones";
    $resultado = DB_query($consulta, $db, $ErrMsg);
    while ($registro= DB_fetch_array($resultado)) {
        $imprimir = "";
        $opciones = "";
        $ligaGenerarOC = "";
        $seleccionar = '';

        //if ($registro['sn_funcion_id'] == '1371') {
            // Impresion OC
            // &OrderNo=107&tipodocto=555&Tagref=100&legalid=1
            $enc = new Encryption;
            $url = "&OrderNo=>".$registro['orderno']."&tipodocto=>555&Tagref=>".$registro['tagref']."&legalid=>".$registro['legalid'];
            $url = $enc->encode($url);
            $liga= "URL=" . $url;

            $imprimir .= "<a id='idBtnImpresionOC' target='_blank' href='./PO_PDFPurchOrder.php?".$liga."'><span class='glyphicon glyphicon glyphicon-print'></span></a><br>";
        //}
        if ($registro["status"] != 'Authorised' && $registro["status"] != 'Completed') {
            // Modificar OC
            // $url = "&ModifyOrderNumber=".$registro['orderno']."&idrequisicion=".$registro['requisitionno']."&panelCompraGen=1";
            $enc = new Encryption;
            $url = "&ModifyOrderNumber=>".$registro['orderno']."&idrequisicion=>".$registro['requisitionno'];
            if ($registro['sn_funcion_id'] != '1371') {
                $url .= "&panelCompraGen=>1";
            }
            $url = $enc->encode($url);
            $liga= "URL=" . $url;
            // $opciones .= "<a id='idBtnCompraOC' target='_blank' href='./PO_Header.php?".$liga."'><span class=''></span> Generar OC</a>";

            $enc = new Encryption;
            $url = "&ModifyOrderNumber=>".$registro['orderno'];
            $url = $enc->encode($url);
            $liga= "URL=" . $url;
            //$liga = "ModifyOrderNumber=".$registro['orderno'];
            $opciones .= "<a id='idBtnCompraOC' href='./PO_Items.php?".$liga."'><span class=''></span> Generar OC</a>"; // target='_blank'
            $ligaGenerarOC = "PO_Items.php?".$liga;

            $seleccionar = '<input type="checkbox" id="checkbox_'.$registro ['orderno'].'" name="checkbox_'.$registro ['orderno'].'" title="Seleccionar" value="'.$registro ['orderno'].'" onchange="fnValidarProcesoCambiarEstatus()" />';
        }
        if ($registro["status"] == 'Authorised' && $registro["completed"] == '0') {
            // Rececpción OC
            //$registro["sn_nombre_secundario"] = "Compra Autorizada";
            // $url = "&PONumber=".$registro['orderno'];
            $enc = new Encryption;
            $url = "&PONumber=>".$registro['orderno'];
            $url = $enc->encode($url);
            $liga= "URL=" . $url;

            //$opciones .= "<a id='idBtnRecepcionOC' target='_blank' href='./GoodsReceived.php?".$liga."'><span class=''></span> Recibir OC</a>";
        }
        if ($registro["status"] == 'Completed' && $registro["completed"] == '1' && ($registro["quantityord"] != $registro["qtyinvoiced"])) {
            // Alta de Factura
            $registro["sn_nombre_secundario"] = "Compra Recibida";
            if ($registro['qtyinvoiced'] != 0) {
                $registro["sn_nombre_secundario"] = "Factura Parcial";
            }
            
            // $url = "&SupplierID=".$registro['supplierid']."&unidadnegocio=".$registro['tagref'];//."&GoodRecived=YES";
            $enc = new Encryption;
            $url = "&SupplierID=>".$registro['supplierid']."&unidadnegocio=>".$registro['tagref'];
            $url = $enc->encode($url);
            $liga= "URL=" . $url;

            //$opciones .= "<a id='idBtnAltaFacturaOC' target='_blank' href='./SupplierInvoice.php?".$liga."'><span class=''></span> Facturar OC</a>";
        }

        if ($registro['status'] == 'Autorizado' && $registro["supplierid"] == '111111') {
            // Si es requisicion autorizada no mostrar proveedor
            $registro["supplierid"] = "";
            $registro["suppname"] = "";
        }

        $info[] = array(
            'id1' =>false,
            "idrequisicion"=> $registro["requisitionno"],
            "numerorequisicion"=> $registro["requisitionno"],
            "idproveedor" => $registro["supplierid"],
            "nombreproveedor" => ($registro["suppname"]),
            "estatus" => $registro["sn_nombre_secundario"],
            "totalrequisicion" => $registro["ordervalue"],
            "totalrequisicion2" => $registro["ordervalue"],
            "seleccionar" => $seleccionar,
            "fechaCaptura" => $registro["orddate"],
            "fecharequerida" => $registro["fecharequerida"],
            "observaciones" => $registro["comments"],
            "orderno" => $registro["orderno"],
            "ordencompra" => $registro["realorderno"],
            //"compra" => '<a onclick="fnEliminar('.$myrow ['id_funcion'].',\''.$myrow ['desc_fun'].'\','.$myrow ['idFinalidad'].')">Eliminar</a>'
            //"compra" => "<a target='_self' href='./PO_Header.php?ModifyOrderNumber=".$registro["orderno"]."&idrequisicion=".$registro["requisitionno"]."' style='color: blue; '><u>".$registro["requisitionno"]."</u></a>"
            //"compra" => "<a id='idBtnCompra' target='_blank' href='./PO_Header.php?'><span class='glyphicon glyphicon-open-file'></span></a>"
            //"compra" => "<a id='idBtnCompra' target='_blank' href='./PO_Header.php?ModifyOrderNumber=".$registro["orderno"]."&idrequisicion=".$registro["requisitionno"]."'><span class='glyphicon glyphicon-open-file'></span></a>"
            "imprimir" => $imprimir,
            "compra" => $opciones,
            "ligaGenerarOC" => $ligaGenerarOC
            //"compra" => "<a id='idBtnCompra' target='_blank' href='./PO_Header.php?back=1&identifier=".date('U')."&ModifyOrderNumber=".$registro["orderno"]."&idrequisicion=".$registro["requisitionno"]."'><span class='glyphicon glyphicon-open-file'></span></a>"
        );
    }

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'nombreExcel' => $nombreExcel);
    $result = true;
}

if ($option == 'mostrarEstatusOC') {
    $info = array();
    
    $SQL = "SELECT tb_botones_status.statusname as value, tb_botones_status.sn_nombre_secundario as texto FROM tb_botones_status
    WHERE tb_botones_status.sn_funcion_id in (2265)
    AND tb_botones_status.statusname='Autorizado'
    UNION
    SELECT tb_botones_status.statusname as value, tb_botones_status.sn_nombre_secundario as texto
    FROM tb_botones_status 
    WHERE tb_botones_status.sn_funcion_id in (1371) 
    AND tb_botones_status.statusname in ('Authorised', 'Pending', 'Completed')";
    $ErrMsg = "No se obtuvo los Tipos de Documentos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
        if ($myrow['value'] == 'Completed') {
            $info[] = array( 'value' => $myrow ['value'].'_FacturaParcial', 'texto' => 'Factura Parcial' );
            $info[] = array( 'value' => $myrow ['value'].'_CompraRecibida', 'texto' => 'Compra Recibida' );
        }
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'validarEstatusOrdenCompra') {
    $dataJsonNoCapturaSeleccionados = $_POST['dataJsonNoCapturaSeleccionados'];
    $statusid = $_POST['statusid'];

    $info = array();
    $mensajeErrores = "";
    $result = true;
    foreach ($dataJsonNoCapturaSeleccionados as $datosClave) {
        $actualizar = 1;
        $SQL = "SELECT purchorders.status, purchorders.requisitionno FROM purchorders WHERE purchorders.orderno = '".$datosClave['orderno']."'";
        $ErrMsg = "No se obtuvieron los registros del Orden ".$datosClave ['orderno'];
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            if ($statusid == '99') {
                // Validar si se separo la compra,
                $SQL = "SELECT purchorders.orderno, purchorders.realorderno, purchorders.status, purchorders.supplierno, CONCAT(purchorders.supplierno, ' - ', suppliers.suppname) as suppname
                    FROM purchorders
                    LEfT JOIN suppliers ON suppliers.supplierid = purchorders.supplierno
                    WHERE purchorders.requisitionno = '".$myrow ['requisitionno']."'";
                $TransResult2 = DB_query($SQL, $db, $ErrMsg);
                if (DB_num_rows($TransResult2) > 1) {
                    // Si se separo validar separaciones
                    while ($myrow2 = DB_fetch_array($TransResult2)) {
                        // Validar datos
                        if ($myrow2["status"] == 'Authorised' || $myrow2["status"] == 'Completed') {
                            // Si esta autorizada o con recepciones
                            $result = false;
                            $actualizar = 0;
                            $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La Requisición '.$myrow ['requisitionno'].' no se puede reversar. Se genero la Orden de Compra '.$myrow2 ['realorderno'].' con el proveedor '.$myrow2 ['suppname'].'</p>';
                        }

                        // Validar si tiene salidas de almacen
                        $SQL = "SELECT 
                        SUM(tb_salidas_almacen_detalle.nu_cantidad_entregada) as cantidad, tb_solicitudes_almacen.nu_folio
                        FROM tb_solicitudes_almacen 
                        JOIN tb_solicitudes_almacen_detalle ON tb_solicitudes_almacen_detalle.nu_id_solicitud = tb_solicitudes_almacen.nu_folio AND tb_solicitudes_almacen_detalle.ln_arctivo = 1
                        JOIN tb_salidas_almacen_detalle ON tb_salidas_almacen_detalle.nu_id_solicitud = tb_solicitudes_almacen.nu_folio
                        WHERE tb_solicitudes_almacen.nu_id_requisicion = '".$myrow2 ['orderno']."'";
                        $TransResult3 = DB_query($SQL, $db, $ErrMsg);
                        while ($myrow3 = DB_fetch_array($TransResult3)) {
                            if (!empty($myrow3['cantidad']) && $myrow3['cantidad'] > 0) {
                                // Si tiene salidas de almacén
                                $result = false;
                                $actualizar = 0;
                                $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La Requisición '.$myrow ['requisitionno'].' no se puede reversar. La solicitud al almacén '.$myrow3 ['nu_folio'].' ya tuvo salidas</p>';
                            }
                        }
                    }
                } else {
                    // Si solo es es una orden de compra
                    // Validar si tiene salidas de almacen
                    $SQL = "SELECT 
                    SUM(tb_salidas_almacen_detalle.nu_cantidad_entregada) as cantidad, tb_solicitudes_almacen.nu_folio
                    FROM tb_solicitudes_almacen 
                    JOIN tb_solicitudes_almacen_detalle ON tb_solicitudes_almacen_detalle.nu_id_solicitud = tb_solicitudes_almacen.nu_folio AND tb_solicitudes_almacen_detalle.ln_arctivo = 1
                    JOIN tb_salidas_almacen_detalle ON tb_salidas_almacen_detalle.nu_id_solicitud = tb_solicitudes_almacen.nu_folio
                    WHERE tb_solicitudes_almacen.nu_id_requisicion = '".$datosClave ['orderno']."'";
                    $TransResult3 = DB_query($SQL, $db, $ErrMsg);
                    while ($myrow3 = DB_fetch_array($TransResult3)) {
                        if (!empty($myrow3['cantidad']) && $myrow3['cantidad'] > 0) {
                            // Si tiene salidas de almacén
                            $result = false;
                            $actualizar = 0;
                            $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La Requisición '.$myrow ['requisitionno'].' no se puede reversar. La solicitud al almacén '.$myrow3 ['nu_folio'].' ya tuvo salidas</p>';
                        }
                    }
                }
            }
            if ($myrow["status"] == 'Authorised' || $myrow["status"] == 'Completed') {
                $result = false;
                $actualizar = 0;
                if ($statusid == '99') {
                    // Es rechazar
                    $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La Requisición '.$myrow ['requisitionno'].' no puede ser rechazada. Ya se genero la Orden de Compra </p>';
                } else {
                    $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La Requisición '.$myrow ['requisitionno'].' tiene Orden de Compra generada </p>';
                }
            }
        }

        $info[] = array(
            'orderno' => $datosClave ['orderno'],
            'actualizar' => $actualizar
        );
    }
    
    $contenido = array('datos' => $info, 'mensajeErrores' => $mensajeErrores);
}

if ($option == 'rechazarRequisiciones') {
    $dataJsonNoCapturaSeleccionados = $_POST['dataJsonNoCapturaSeleccionados'];
    $statusid = $_POST['statusid'];

    $info = array();
    $mensajeErrores = "";
    $result = true;
    foreach ($dataJsonNoCapturaSeleccionados as $datosClave) {
        $actualizar = 1;

        // Ver nota de código documentado entre /* y */
        $SQL = "UPDATE `purchorders` SET `status` = 'ProceCompra', `supplierno` = '111111', `comments` = substr(`purchorders`.`comments`, 1, LOCATE('NOTA: ', `purchorders`.`comments`, 1) - 1)
                WHERE `orderno` = '$datosClave[orderno]'";
        DB_query($SQL, $db, $ErrMsg);

        $SQL = "UPDATE `tb_proceso_compra` SET `id_nu_estatus` = '2'
                WHERE `orderno` = '$datosClave[orderno]'
                AND `requisitionno` = '$datosClave[requisitionno]'";
        DB_query($SQL, $db, $ErrMsg);

        $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-success" aria-hidden="true"></i> La Requisición '.$datosClave ['requisitionno'].' ha sido reversada </p>';

        /*
        //  Todo este código documentado se remueve para usarse en proceso de Rechazo en procesoDeCompraModelo.php y se reempñaza con las líneas anteriores

        // Obtener orderno original (Inicial)
        $SQL = "SELECT MIN(purchorders.orderno) as ordernoOriginal 
        FROM purchorders 
        WHERE purchorders.requisitionno = '".$datosClave ['requisitionno']."'";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $myrow = DB_fetch_array($TransResult);
        $datosClave ['orderno'] = $myrow['ordernoOriginal'];

        // Agregar detalle al orderno original
        $SQL = "UPDATE purchorderdetails
        JOIN purchorders ON purchorders.orderno = purchorderdetails.orderno
        SET purchorderdetails.orderno = '".$datosClave ['orderno']."'
        WHERE purchorders.requisitionno = '".$datosClave ['requisitionno']."'";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        // Actualizar orden de los bienes y servicios
        $SQL = "UPDATE purchorderdetails
        SET purchorderdetails.orderlineno_ = purchorderdetails.nu_original
        WHERE 
        purchorderdetails.status = 2
        AND purchorderdetails.orderno = '".$datosClave ['orderno']."'";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $SQL = "SELECT tb_suficiencias.nu_type, tb_suficiencias.nu_transno
        FROM tb_suficiencias 
        WHERE tb_suficiencias.nu_estatus <> '0' AND tb_suficiencias.sn_orderno = '".$datosClave['orderno']."'";
        $ErrMsg = "No se obtuvieron los registros del Orden ".$datosClave ['orderno'];
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if ($myrow = DB_fetch_array($TransResult)) {
            $agrego = fnInsertPresupuestoLogMovContrarios($db, $myrow['nu_type'], $myrow['nu_transno']);
            if ($agrego) {
                $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-success" aria-hidden="true"></i> La Requisición '.$datosClave ['requisitionno'].' ha sido reversada </p>';

                // Cancelar Suficiencia y precomprometido
                $SQL = "UPDATE tb_suficiencias SET tb_suficiencias.nu_estatus = 0, tb_suficiencias.sn_description = CONCAT(tb_suficiencias.sn_description, '. Reversada')
                WHERE tb_suficiencias.nu_type = '".$myrow['nu_type']."' AND tb_suficiencias.nu_transno = '".$myrow['nu_transno']."'";
                $TransResult2 = DB_query($SQL, $db, $ErrMsg);
                $SQL = "UPDATE chartdetailsbudgetlog SET estatus = 0 
                WHERE type = '".$myrow['nu_type']."' AND transno = '".$myrow['nu_transno']."'";
                $TransResult2 = DB_query($SQL, $db, $ErrMsg);

                // Actualizar requisición
                $SQL = "UPDATE purchorders SET status = 'PorAutorizar', supplierno = '111111', comments = substr(purchorders.comments, 1, LOCATE('NOTA: ', purchorders.comments, 1) - 1)
                WHERE orderno = '".$datosClave ['orderno']."'";
                $TransResult2 = DB_query($SQL, $db, $ErrMsg);

                // Actualizar detalle, sumar lo de la solicitud al almacén
                $SQL = "UPDATE purchorderdetails
                LEFT JOIN tb_solicitudes_almacen_detalle ON tb_solicitudes_almacen_detalle.ln_ontransit = purchorderdetails.podetailitem AND tb_solicitudes_almacen_detalle.ln_arctivo = 1
                SET purchorderdetails.quantityord = purchorderdetails.quantityord + CASE WHEN tb_solicitudes_almacen_detalle.nu_cantidad IS NULL THEN 0 ELSE tb_solicitudes_almacen_detalle.nu_cantidad END
                WHERE purchorderdetails.orderno = '".$datosClave ['orderno']."'
                AND purchorderdetails.status = '2'";
                $TransResult2 = DB_query($SQL, $db, $ErrMsg);

                // Actualizar no existencia
                $SQL = "UPDATE tb_no_existencias SET status = '1' 
                WHERE nu_id_requisicion = '".$datosClave ['requisitionno']."'";
                $TransResult2 = DB_query($SQL, $db, $ErrMsg);

                // Actualizar no existencia
                $SQL="UPDATE  tb_solicitudes_almacen SET estatus='65', ln_nombre_estatus='Por Autorizar' 
                WHERE nu_id_requisicion = '".$datosClave ['orderno']."'";
                $TransResult2 = DB_query($SQL, $db, $ErrMsg4);
            } else {
                $result = false;
                $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La Requisición '.$datosClave ['requisitionno'].' no pudo ser reversada </p>';
            }
        }

        */

        $info[] = array(
            'orderno' => $datosClave['orderno'],
            'actualizar' => $actualizar
        );
    }
    
    $contenido = array('datos' => $info, 'mensajeErrores' => $mensajeErrores);
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
            tb_botones_status.functionid = (SELECT sec_funxuser.functionid FROM sec_funxuser WHERE sec_funxuser.functionid = tb_botones_status.functionid AND sec_funxuser.userid = '".$_SESSION['UserID']."' AND sec_funxuser.permiso = 1)
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

$dataObj = array(
    'sql' => '',
    'contenido' => $contenido,
    'result' => $result,
    'RootPath' => $RootPath,
    'ErrMsg' => $ErrMsg,
    'Mensaje' => $Mensaje);

echo json_encode($dataObj);
