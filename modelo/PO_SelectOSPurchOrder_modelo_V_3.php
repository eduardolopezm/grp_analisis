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

$PageSecurity = 1;
$funcion=2244;
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
$contenido = array();
$result = false;
$SQL = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";
$periodo = GetPeriod(date('d/m/Y'), $db);

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

if ($option == 'traeRequisiciones') {
    $info = array();
    $condicion= " 1=1 ";
    $fechaini= date("Y-m-d", strtotime($_POST["fechainicio"]));
    $fechafin= date("Y-m-d", strtotime($_POST["fechafin"]));
    $dependencia= $_POST["dependencia"];
    $unidadres= $_POST["unidadres"];
    $unidadeje= $_POST["unidadeje"];
    $idrequisicion= $_POST["requisicion"];
    $idproveedor= $_POST["idproveedor"];
    $nomproveedor= $_POST["nomproveedor"];
    $estatus= $_POST["estatus"];
    $funcion= $_POST["funcion"];
    $seleccionar= "";

    // separar la seleccion multiple de las unidades responsables
    if (is_array($unidadres)) {
        $unidadres= implode(",", $unidadres);
    }

    // separar la seleccion multiple de estatus
    if (is_array($estatus)) {
        $estatus= implode(",", $estatus);
    }

    $condicion.= " AND purchorders.requisitionno!= 0 AND purchorders.requisitionno!= '' AND purchorders.orddate>= '".$fechaini." 00:00:00' AND purchorders.orddate<='".$fechafin." 23:59:59' ";

    if (!empty($unidadres) && !strpos("@".$unidadres, "-1")) {
        $condicion.= " AND tags.tagref IN (".$unidadres.") ";
    } else {
        $condicion.= " AND tags.legalid='".$dependencia."' AND tags.tagref IN (SELECT tagref FROM sec_unegsxuser WHERE userid= '".$_SESSION["UserID"]."') ";
    }

    if (!empty($unidadeje) && !strpos("@".$unidadeje, "-1")) {
        $condicion.= " AND purchorders.nu_ue = '".$unidadeje."' ";
    }

    if (!empty($idproveedor)) {
        $condicion.= " AND purchorders.supplierno LIKE '%".$idproveedor."%' ";
    }

    if (!empty($nomproveedor)) {
        $condicion.= " AND suppliers.suppname LIKE '%".$nomproveedor."%' ";
    }

    if (!empty($estatus) && !strpos("@".$estatus, "-1")) {
        $condicion.= " AND purchorders.status IN (".$estatus.") ";
    }

    if (!empty($idrequisicion) && intval($idrequisicion)!= 0) {
        $condicion= " purchorders.requisitionno= '".$idrequisicion."' ";
    }

    // Consulta para extraer los datos para el panel
    $consulta= "SELECT purchorders.requisitionno, locationname,
				purchorders.orderno as orderno,
				purchorders.status_aurora,
				IF(purchorders.supplierorderno IS NULL, 'NA', purchorders.supplierorderno) AS supplierorderno,
				suppliers.suppname,
				suppliers.supplierid,
				DATE_FORMAT(purchorders.orddate,'%Y/%m/%d') as orddate,
				purchorders.initiator,
				purchorders.status,
				purchorders.allowprint,
				purchorders.tagref,
				purchorders.currcode,
				SUM(case when stockmaster.stockid IS NULL THEN 0 WHEN purchorderdetails.quantityord < purchorderdetails.qtyinvoiced THEN 0 ELSE purchorderdetails.quantityord - purchorderdetails.qtyinvoiced END) as productosfacturados,              
                SUM(CASE WHEN stockmaster.stockid IS NULL THEN 0 ELSE (purchorderdetails.unitprice*purchorderdetails.quantityord)*(1-(discountpercent1/100))*(1-(discountpercent2/100))*(1-(discountpercent3/100)) END) AS ordervalue,
                SUM(CASE WHEN stockmaster.stockid IS NULL THEN 0 ELSE purchorderdetails.quantityord - purchorderdetails.quantityrecd END) as productospendientes,               
                SUM(CASE WHEN stockmaster.stockid IS NULL THEN 0 ELSE purchorderdetails.quantityrecd END) AS productosrecibidos,                
				'' as foliofiscal,
				purchorders.wo,
				purchorders.autorizausuario,
				DATE_FORMAT(purchorders.autorizafecha,'%Y/%m/%d') as fechaauto,
				tags.legalid,
				tags.tagref as ur,
				DATE_FORMAT(purchorders.deliverydate,'%d/%m/%Y') as fecharequerida,
				purchorders.comments
				FROM purchorders
				INNER JOIN purchorderdetails ON purchorders.orderno = purchorderdetails.orderno AND purchorderdetails.status NOT IN(0,3)
				INNER JOIN tb_botones_status ON purchorders.status= tb_botones_status.statusname AND tb_botones_status.sn_flag_disponible=1 AND sn_funcion_id= '".$funcion."' 
				INNER JOIN tags on purchorders.tagref=tags.tagref
				INNER JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
				INNER JOIN legalbusinessunit on tags.legalid=legalbusinessunit.legalid
				LEFT JOIN locations ON purchorders.intostocklocation = locations.loccode
				LEFT JOIN areas on areas.areacode=tags.areacode
                LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
				WHERE ".$condicion."
				GROUP BY locationname,
				purchorders.orderno,
				purchorders.status_aurora,
				IF(purchorders.supplierorderno IS NULL, 'NA', purchorders.supplierorderno),
				suppliers.suppname,
				suppliers.supplierid,
				DATE_FORMAT(purchorders.orddate,'%Y/%m/%d'),
				purchorders.initiator,
				purchorders.status,
				purchorders.requisitionno,
				purchorders.allowprint,
				purchorders.tagref,
				purchorders.currcode,
				purchorders.wo,
				purchorders.autorizausuario,
				DATE_FORMAT(purchorders.autorizafecha,'%Y/%m/%d'),
				tags.legalid,
				tags.tagref,
				DATE_FORMAT(purchorders.deliverydate,'%d/%m/%Y'),
				purchorders.comments
				ORDER BY purchorders.requisitionno DESC";

    $ErrMsg = "No se pudo obtener la consulta de requisiciones";
    $resultado = DB_query($consulta, $db, $ErrMsg);

    while ($registro= DB_fetch_array($resultado)) {
        $enc = new Encryption;
        $url = "&ModifyOrderNumber=>" . $registro["orderno"] . "&idrequisicion=> ". $registro["requisitionno"];
        $url = $enc->encode($url);
        $liga= "URL=" . $url;

        $seleccionar= '<input type="checkbox" id="checkbox_'.$registro['requisitionno'].'" name="checkbox_'.$registro['requisitionno'].'" title="Seleccionar" value="'.$registro['requisitionno'].'" />';

        $info[] = array('id1'=>false,
            "idrequisicion"=> "<a target='_self' href='./Captura_Requisicion_V_3.php?$liga' style='color: blue; '><u>".$registro["requisitionno"]."</u></a>",
            //"idrequisicion"=> $registro["requisitionno"],
            "idrequisicionH" => $registro["orderno"],
            "numerorequisicion" => $registro["requisitionno"],
            "idproveedor" => $registro["supplierid"],
            "ur" => $registro["ur"],
            "nombreproveedor" => utf8_decode($registro["suppname"]),
            "estatus" => $registro["status"],
            "totalrequisicion" => $registro["ordervalue"],
            "seleccionar" => $seleccionar,
            "fecharequerida" => $registro["fecharequerida"],
            "observaciones" => $registro["comments"]);
    }

    $contenido = array('datosCatalogo' => $info);
    $result = true;
}

if ($option == 'cancelarRequisicion') {
    $req = $_POST['noReq'];
    $arrayreq = implode(",", $req);
    $status = 'Cancelado';

    $info = array();
    $SQL = "UPDATE purchorders SET status = '$status', fecha_modificacion = current_timestamp() where orderno  in (".$arrayreq.")";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $contenido = "Se cancelan los elementos seleccionados ";
    $result = true;
}

// Opcion que guarda los datos de la requisicion una vez autorizada
if ($option == 'autorizarRequisicion') {
    $arrayreq = $_POST['noReq'];
    
    if (is_array($_POST['noReq'])) {
        $arrayreq = implode(",", $_POST['noReq']);
    }
    
    $status = 'Autorizado';

    $info = array();
    $SQL = "UPDATE purchorders SET status = '$status', fecha_modificacion = current_timestamp() where orderno  in ($arrayreq)";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido = "Se autorizan los elementos seleccionados ";

    $type = 263;
    $transno = GetNextTransNo($type, $db);

    $SQL2 = "
    SELECT 
    SUM(pd.quantityord * pd.unitprice) AS total, 
    p.orderno as idreq, 
    p.requisitionno as noreq, 
    p.tagref as tagref,  
    pd.clavepresupuestal as cvepresupuestal, 
    cdbt.partida_esp as partida, 
    tb_botones_status.sn_estatus_anterior as statusid, 
    tb_botones_status.sn_funcion_id
    FROM purchorders p 
    JOIN purchorderdetails pd on (p.orderno = pd.orderno) 
    LEFT JOIN chartdetailsbudgetbytag cdbt on (p.tagref = cdbt.tagref and pd.clavepresupuestal = cdbt.accountcode ) 
    INNER JOIN tb_botones_status ON p.status= tb_botones_status.statusname 
    WHERE p.orderno IN ($arrayreq) AND pd.status = 2
    GROUP BY idreq, noreq, tagref, cvepresupuestal, partida, statusid, sn_funcion_id
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

        // Panel Suficiencia
        $myrow['sn_funcion_id'] = 2302;
        // Estaus de Por Autorizar
        $myrow['statusid'] = 3;

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
            $description
        );
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
            $myrow['sn_funcion_id']
        );

        fnAgregarSuficienciaGeneral($db, $type, $transno, "Automática", $myrow['statusid'], $myrow['tagref'], 1, $myrow['sn_funcion_id'], $orderno);
    }

    $result = true;
}

if ($option == 'statusRequisicion') {
    $idreq = $_POST['idReq'];
    //$req = implode(",", $idreq);
    
    $info = array();
    $SQL = "SELECT orderno,status FROM purchorders WHERE orderno = '$idreq'";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
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
            JOIN tb_botones_status tbs on (p.status = tbs.statusname) 
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
    $nombreMes = "";
    $contenido = "";
    $result = true;
    $disponiblepartida= array();

    $SQLMes = "SELECT periods.periodno, periods.lastdate_in_period, cat_Months.mes as mesName
                FROM periods 
            LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
            WHERE periods.lastdate_in_period like '%".$myrow ['anho']."%' AND periods.periodno = '".$periodo."'
            ORDER BY periods.lastdate_in_period asc";

    $ErrMsgMes = "No se obtuvo informacion";
    $resultPeriods = DB_query($SQLMes, $db, $ErrMsgMes);

    while ($rowPeriods = DB_fetch_array($resultPeriods)) {
        $nombreMes = $rowPeriods ['mesName'];
    }

    $SQL = "SELECT p.orderno as idReq, p.requisitionno as noReq, p.status as statusReq, p.tagref as tagref, p.validfrom, p.validto,
            pd.itemcode as itemcode, pd.itemdescription as itemdescription, pd.unitprice as precio, pd.quantityord as cantidad, pd.orderlineno_, pd.clavepresupuestal as clavepre, pd.sn_descripcion_larga, pd.status as statusItem, pd.renglon
            FROM purchorders p
            JOIN purchorderdetails pd ON (p.orderno = pd.orderno) 
            WHERE p.orderno = $req AND pd.status NOT IN(0,3)";

    $ErrMsg = "No se obtuvo informacion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $idrequi = $myrow ['idReq'];
        $cvepre = $myrow ['clavepre'];
        $itemcode = $myrow['itemcode'];
        $nombre_producto= $myrow['itemdescription'];
        $partida_producto= $myrow['orderlineno_'];
        $precio = $myrow ['precio'];
        $cantidad = $myrow ['cantidad'];
        $tot = $precio * $cantidad;

        if (substr($itemcode, 0, 1) != "3") {
            // validar existencia de almacen con la cantidad solicitada
            $SQL2 = "SELECT quantity 
                    FROM locstock 
                    INNER JOIN sec_loccxusser USING (loccode)
                    where stockid = '$itemcode'
                    AND sec_loccxusser.userid= '".$_SESSION["UserID"]."'";

            $ErrMsg2 = "No se obtuvo informacion";
            $TransResult2 = DB_query($SQL2, $db, $ErrMsg2);

            while ($myrowqty = DB_fetch_array($TransResult2)) {
                $existencia = $myrowqty['quantity'];
                $disp = $existencia - $cantidad;
                
                if ($disp > 0) {
                    $result = false;

                    if (empty($myrow ['noReq'])) {
                        $contenido.= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;La cantidad de bienes solicitada en el renglón '.$partida_producto.' tiene existencia en el almacén.</p>';
                    } else {
                        $contenido.= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;La cantidad de bienes solicitada en la requisición '.$myrow['noReq'].' en el renglón '.$partida_producto.' tiene existencia en el almacén.</p>';
                    }
                }
            }
        }
            
        $infopre = fnInfoPresupuesto($db, $cvepre, $periodo);

        $disponiblepartida[$infopre[0]["partida_esp"]]+= $tot;

        if (empty($infopre[0][$nombreMes])) {
            //$contenido.= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;No se encontró información para la Clave Presupuestal '.$cvepre.' en lel renglón '.$partida_producto.'.</p>';
            $contenido.= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;No se cuenta con disponible para la clave presupuestal  '.$cvepre.' en el mes en curso para el renglón '.$partida_producto.'.</p>';
            $result = false;
        } else {
            if ($infopre[0][$nombreMes] > $disponiblepartida[$infopre[0]["partida_esp"]]) {
                if ($result) {
                    $result = true;
                }
            } else {
                if (empty($myrow['noReq'])) {
                    //$contenido.= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;La partida '.$partida_producto.' no cuenta con presupuesto disponible.</p>';
                    $contenido.= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El importe total en el renglón '.$partida_producto.' excedió el disponible del mes en curso.</p>';
                } else {
                    $contenido.= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El importe total en la requisición '.$myrow['noReq'].' en el renglón '.$partida_producto.' excedió el disponible del mes en curso.</p>';
                }
                
                $result = false;
            }
        }
    }
}
if ($option == 'validarRequisicionPanel') {
    //setlocale(LC_MONETARY, 'es_MX');
    $req = $_POST['idReq'];
    $nombreMes = "";
    $contenido = "";
    $result = "";
    $info = array();
    //Regresa el nomre del mes actual para poder calcular el presupuesto actual 
    $SQLMes = "SELECT periods.periodno, periods.lastdate_in_period, cat_Months.mes as mesName
                FROM periods 
            LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
            WHERE periods.lastdate_in_period like '%".$myrow ['anho']."%' AND periods.periodno = '".$periodo."'
            ORDER BY periods.lastdate_in_period asc";

    $ErrMsgMes = "No se obtuvo informacion";
    $resultPeriods = DB_query($SQLMes, $db, $ErrMsgMes);
    while ($rowPeriods = DB_fetch_array($resultPeriods)) {
        $nombreMes = $rowPeriods ['mesName'];
    }
    $SQL = "SELECT p.orderno as idReq, p.requisitionno as noReq, p.status as statusReq, p.tagref as tagref,p.nu_ue as ue , p.comments as comments, p.deliverydate as fdelivery, p.validfrom, p.validto, pd.itemcode as itemcode, pd.itemdescription as itemdescription, pd.unitprice as precio, pd.quantityord as cantidad, pd.orderlineno_, pd.clavepresupuestal as clavepre, pd.sn_descripcion_larga as longdesc, pd.status as statusItem, pd.renglon as renglon
            FROM purchorders p
            JOIN purchorderdetails pd ON (p.orderno = pd.orderno) 
            WHERE p.orderno = $req AND pd.status NOT IN(0,3)";
    $ErrMsg = "No se obtuvo informacion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    while ($myrow = DB_fetch_array($TransResult)) {
        $infopre = fnInfoPresupuesto($db, $myrow ['clavepre'], $periodo);
        $pptoActal = $infopre[0][$nombreMes];
        $itemcode = $myrow['itemcode'];
        if (substr($itemcode, 0, 1) != "3") {
            // validar existencia de almacen con la cantidad solicitada
            $SQLqty = "SELECT quantity 
                    FROM locstock 
                    INNER JOIN sec_loccxusser USING (loccode)
                    where stockid = '".$myrow ['itemcode']."'
                    AND sec_loccxusser.userid= '".$_SESSION["UserID"]."'";
            $ErrMsgqty = "No se obtuvo informacion";
            $TransResultqty = DB_query($SQLqty, $db, $ErrMsgqty);
            
            while ($myrowqty = DB_fetch_array($TransResultqty)) {
                $qtyStock = $myrowqty['quantity'];
            }
        }

        $qtyDisp = $qtyStock - $myrow ['cantidad']; 

        $info[] = array(
            'idrequi' => $myrow ['idReq'],
            'norequi' => $myrow ['noReq'],
            'tagref' => $myrow ['tagref'],
            'ue' => $myrow ['ue'],
            'comments' => $myrow ['comments'],
            'longdesc' => $myrow ['longdesc'],
            'renglon' => $myrow ['renglon'],
            'fdelivery' => $myrow ['fdelivery'],
            'clavepre' => $myrow ['clavepre'],
            'itemcode' => $myrow ['itemcode'],
            'itemdescription' => $myrow ['itemdescription'],
            'orderlineno_' => $myrow ['orderlineno_'],
            'precio' => $myrow ['precio'],
            'cantidad' => $myrow ['cantidad'],
            'tot' => ($myrow ['precio'] * $myrow ['cantidad']),
            'pptoActual' => $pptoActal,
            'qtyStock' => $qtyStock,
            'qtyStockDisp' => $qtyDisp
        );        
    }
    
    $contenido = array('datos' => $info);
    $result = true;
}

if (empty($consulta)) {
    $consulta= $SQL;
}

$dataObj = array(
    'sql' => '',
    'contenido' => $contenido,
    'result' => $result,
    'RootPath' => $RootPath,
    'ErrMsg' => $ErrMsg,
    'Mensaje' => $Mensaje);

echo json_encode($dataObj);
