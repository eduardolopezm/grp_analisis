<?php
/**
 * Proceso de Compra
 *
 * @category Proceso
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 08/08/2017
 * Fecha Modificación: 08/08/2017
 * Realizar proceso de compra
 */
/////
?>
<script>
    var ventana = null;
    var ventanaBig = null;

    function openNewWindowasdf(url){
        if (ventana==null || ventana.closed)
            ventana = window.open(url,'','width=350, height=400, scrollbars=yes'); //     500 y 200
        else 
            alert('Esta funcion ya se esta ejecuntando, favor de cerrarl la ventana antes de abrir otra');  
    }
    
    function openNewWindow(url){        
        window.open(url, '', 'width=350, height=400, scrollbars=yes');  
    }

    function openNewWindowBig(url){
        if (ventanaBig==null || ventanaBig.closed) 
            ventana = window.open(url,'','width=500,height=600'); 
        else 
            alert('Esta funcion ya se esta ejecuntando, favor de cerrar la ventana antes de abrir otra');    
    }
 
    function Next(id, evento){ 
        var x = document.getElementById(id).maxLength; 
        var textoArea = document.getElementById(id).value;
        var numeroCaracteres = textoArea.length;          
        
        if (evento) {
            var charCode = (evento.which) ? evento.which : event.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {        
                event.returnValue= false;
                return false;
            }
        }

        var res = id.split(".");
        var id2=parseInt(res[1]);
        var aumento=id2+1;
        var idfocus=res[0]+"."+aumento;

        if(x == numeroCaracteres+1)
        {
            document.getElementById(idfocus).focus();
        }
    }
</script>
<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
// ini_set('log_errors', 1);
// ini_set('error_log', dirname(__FILE__) . '/<error_log class="txt"></error_log>');

echo "<script language='javascript'>";
echo "function lockbutton(forma, boton){";
echo "boton.disabled=true;";
echo "forma.AutorizaOrden.click();";
echo "}";
echo "</script>";

$PageSecurity = 4;
$funcion=1371;
$title = _('Orden de Compra');
include "includes/SecurityUrl.php";
include('includes/DefinePOClass.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/GoodsReceivedFunction.php');
//include "includes/SecurityUrl.php";

include 'includes/session.inc';

$PaperSize = 'A4_Landscape';
include('includes/PDFStarter.php');

include('includes/header.inc');
include('includes/SecurityFunctions.inc');

//****************** P E R M I S O S ********************************
$ejecutar_debug = false;
$SelectOrderItemsFile=HavepermissionURL($_SESSION['UserID'], 4, $db);
$permisotextonarra=Havepermission($_SESSION['UserID'], 1144, $db);
$permisoeliminarpartidas=Havepermission($_SESSION['UserID'], 1145, $db);
$permiso_addprod_ot=Havepermission($_SESSION['UserID'], 1146, $db);
$permisomod_ot=Havepermission($_SESSION['UserID'], 1147, $db);
$permisoVerOT=Havepermission($_SESSION['UserID'], 1196, $db);
$permisoparadescontarelivaalprecio=Havepermission($_SESSION['UserID'], 1487, $db);//Definir permiso
//********************************************************************

$mensaje_emergente= "";
$procesoterminado= 0;
$diferenciasSuficienciaTotal = 0;
$valSuficienciaEstatus = 0;
$urSinAlmacen = 1;

include('Numbers/Words.php');
include('includes/XSAInvoicing.inc');
$Maximum_Number_Of_Parts_To_Show=50;
$GoodProcess=true;




if (isset($_SESSION['TruncarDigitos'])) {
    $digitos=$_SESSION['TruncarDigitos'];
} else {
    $digitos=4;
}

if (isset($_POST['supplierid'])) {
    $supplierid=$_POST['supplierid'];
} else {
    $supplierid=$_GET['supplierid'];
}

$TieToOrderNumber= "";
if (isset($_POST['TieToOrderNumber'])) {
    $TieToOrderNumber = $_POST['TieToOrderNumber'];
} elseif (isset($_GET['TieToOrderNumber']) && !empty($_GET['TieToOrderNumber'])) {
    $TieToOrderNumber = $_GET['TieToOrderNumber'];
}


$identifier = 0;
if (isset($_GET['ModifyOrderNumber'])) {
    $identifier = date('U');

    include 'includes/PO_ReadInOrder.inc';

    $_SESSION['LineItemsDatos'] = $_SESSION['PO'.$identifier]->LineItems;
    $_SESSION['OrderNoDatos'] = $_SESSION['PO'.$identifier]->OrderNo;
    $_SESSION['LinesOnOrderDatos'] = $_SESSION['PO'.$identifier]->LinesOnOrder;

    if ($_SESSION['PO'.$identifier]->estatus == 'Autorizado' && $_SESSION['PO'.$identifier]->SupplierID == '111111') {
        // Si es de requisicion poner
        $_SESSION['PO'.$identifier]->SupplierID = '';
        $_SESSION['PO'.$identifier]->SupplierName = '';
    }
} else {
    if (isset($_GET['identifier'])) {
        $identifier=$_GET['identifier'];
    }
    if ($identifier == 0) {
        prnMsg("No se puedo obtener la Información", "warn");
        include('includes/footer_Index.inc');
        exit();
    }

    // if (!isset($_SESSION['PO'.$identifier])) {
    //     header('Location:' . $rootpath . '/PO_Header.php?' . SID);
    //     exit;
    // }
}

if (isset($_POST['selectProveedorCambiar'])) {
    $_SESSION['PO'.$identifier]->SupplierID = $_POST['selectProveedorCambiar'];
}

if (isset($_POST['selectAlmacenOrdenCompra'])) {
    $_SESSION['PO'.$identifier]->Location = $_POST['selectAlmacenOrdenCompra'];
}

if (isset($_POST['UpdateLines']) or isset($_POST['btnMoneda'])) {
    unset($_POST['QuickEntry']);
}

$decimalesTipoCambio = 8;
if (empty($_SESSION['TCDecimals']) == false) {
    $decimalesTipoCambio = $_SESSION['TCDecimals'];
}

if (isset($_SESSION["AutomaticPurchData"])) {
    $automaticpurchdata= $_SESSION["AutomaticPurchData"];
} else {
    $automaticpurchdata= 1;
}

if (isset($_SESSION["FactorDeConversion"])) {
    $factorconversion= $_SESSION["FactorDeConversion"];
} else {
    $factorconversion= 1;
}

# ***** RECUPERA VALORES DE FECHAS *****
$arrfecha = explode("/", $_SESSION['PO'.$identifier]->Orig_OrderDate);
$FromDia = $arrfecha[2];
$FromMes = $arrfecha[1];
$FromAnio = $arrfecha[0];

if ($FromDia=='') {
    $arrfecha = explode("-", $_SESSION['PO'.$identifier]->Orig_OrderDate);
    $FromDia = $arrfecha[2];
    $FromMes = $arrfecha[1];
    $FromAnio = $arrfecha[0];
}
if (isset($_POST['FromYear'])) {
    $FromYear= $_POST['FromYear'];
} else {
    $FromYear=$FromAnio;
};

if (isset($_POST['FromMes'])) {
        $FromMes= $_POST['FromMes'];
} else {
        $FromMes=$FromMes;
};

if (isset($_POST['FromDia'])) {
    $FromDia= $_POST['FromDia'];
} else {
    $FromDia=$FromDia;
};

if (strlen($FromMes) == 1) {
    $FromMes = "0" . $FromMes;
}

if (strlen($FromDia) == 1) {
    $FromDia = "0" . $FromDia;
}



    $_SESSION['PO'.$identifier]->Orig_OrderDate = $FromYear . "-" . $FromMes . "-" . $FromDia;



if (isset($_POST['txtFechaRequerida'])) {
    // echo "<br>txtFechaRequerida: ".$_POST['txtFechaRequerida'];
    $txtFechaRequerida = date_create($_POST['txtFechaRequerida']);
    $_SESSION['PO'.$identifier]->Orig_OrderDate = date_format($txtFechaRequerida, 'Y-m-d');
}

// echo "<br>Orig_OrderDate antes: ".$_SESSION['PO'.$identifier]->Orig_OrderDate;
// if (isset($_POST['txtFechaOrden'])) {
//     $_SESSION['PO'.$identifier]->Orig_OrderDate = date_format($_POST['txtFechaOrden'],'Y-m-d');
// }
// echo "<br>Orig_OrderDate despues: ".$_SESSION['PO'.$identifier]->Orig_OrderDate;

if (isset($_POST['servicetype'])) {
    $_SESSION['PO'.$identifier]->ServiceType = $_POST['servicetype'];
}

// Ocultar busqueda de productos y entrada rapida
$ocultaBuscarEntrada = ' '; // style="display: none;"
if ($_SESSION['PO'.$identifier]->OrderNo != "" || $_SESSION['PO'.$identifier]->OrderNo != 0) {
    $ocultaBuscarEntrada = ' style="display: none;" ';
}

$color_administrativa= "#FFFFCC";
$color_economica= "#E0F0FF";
$color_funcional= "#FFCC00";
$color_cobertura= "#FF3300";

// Arreglo con la estructura de la clave presupuestal
$estructura_clave= array();

$estructura_clave[0]= array("concepto" => "A&ntilde;o", "ancho" => 4, "color" => $color_administrativa);
$estructura_clave[1]= array("concepto" => "Ramo", "ancho" => 2, "color" => $color_administrativa);
$estructura_clave[2]= array("concepto" => "Org. Sup.", "ancho" => 2, "color" => $color_administrativa);
$estructura_clave[3]= array("concepto" => "Unid. Pres.", "ancho" => 2, "color" => $color_administrativa);
$estructura_clave[4]= array("concepto" => "Rubro Ing.", "ancho" => 7, "color" => $color_economica);
$estructura_clave[5]= array("concepto" => "Tipo Gto.", "ancho" => 2, "color" => $color_economica);
$estructura_clave[6]= array("concepto" => "Obj. Gto.", "ancho" => 6, "color" => $color_economica);
$estructura_clave[7]= array("concepto" => "Funcion", "ancho" => 3, "color" => $color_funcional);
$estructura_clave[8]= array("concepto" => "SubFuncion", "ancho" => 2, "color" => $color_funcional);
$estructura_clave[9]= array("concepto" => "Eje Temat.", "ancho" => 3, "color" => $color_funcional);
$estructura_clave[10]= array("concepto" => "Sector", "ancho" => 2, "color" => $color_funcional);
$estructura_clave[11]= array("concepto" => "Programa", "ancho" => 5, "color" => $color_funcional);
$estructura_clave[12]= array("concepto" => "SubProg.", "ancho" => 2, "color" => $color_funcional);
$estructura_clave[13]= array("concepto" => "Objetivos", "ancho" => 3, "color" => $color_funcional);
$estructura_clave[14]= array("concepto" => "Proyecto", "ancho" => 4, "color" => $color_funcional);
$estructura_clave[15]= array("concepto" => "Estrat.", "ancho" => 3, "color" => $color_funcional);
// $estructura_clave[16]= array("concepto" => "Obra", "ancho" => 5, "color" => $color_funcional);
$estructura_clave[17]= array("concepto" => "Benef.", "ancho" => 3, "color" => $color_cobertura);
$estructura_clave[18]= array("concepto" => "Esp. Geog.", "ancho" => 5, "color" => $color_cobertura);

//$fecha_creada= date_create($_SESSION['PO'.$identifier]->Orig_OrderDate);
//$fecha_formateada= date_format($fecha_creada, "d/m/Y");
//$periodo = GetPeriod($fecha_formateada, $db, $tagref);   
//        
//echo "<br>";
//echo "El Periodo para la Fecha ".$fecha_formateada." es ".$periodo;
//echo "<br>";

# **********************************************************************
$InputError=0;
$rateFromAnyToMXN = "";
$oldcurrency = $_SESSION['PO'.$identifier]->CurrCode;
if (isset($_POST['btnMoneda'])) {
    //SI SE SOLICITO EL BOTON DE CAMBIO DE MONEDA ENTRA AQUI
    $newcurrency = $_POST['selMoneda'];
    //VERIFICA SI SE SOLICITO CAMBIO DE MONEDA O SOLO SE DIO CLICK A BOTON DE CAMBIO DE MONEDA CON LA MISMA MONEDA
    if ($newcurrency != $oldcurrency) {
        prnMsg("Se solicito cambio de moneda para esta orden de compra...!", "warn");
        $moneda = $newcurrency;
        if ($newcurrency == "MXN") {
            $moneda = $oldcurrency;
        }
        $qry = "select rate from tipocambio Where currency = '$moneda' Order by fecha DESC limit 1";
        $rsrate = DB_query($qry, $db);
        if (DB_num_rows($rsrate) > 0) {
            $rowrate = DB_fetch_array($rsrate);
            if ($newcurrency == "MXN") {
                $_SESSION['PO'.$identifier]->ExRate = 1;
                $rateFromAnyToMXN = 1/$rowrate['rate'];
                if ($_POST['tcmanual']!="" and $_POST['tcmanual'] > 1) {
                    $rateFromAnyToMXN = $_POST['tcmanual'];
                }
            } else {
                $_SESSION['PO'.$identifier]->ExRate = $rowrate['rate'];
                if ($_POST['tcmanual']!="" and $_POST['tcmanual'] > 1) {
                    $_SESSION['PO'.$identifier]->ExRate = 1/$_POST['tcmanual'];
                }
            }
            $_SESSION['PO'.$identifier]->CurrCode = $newcurrency;
        } else {
            prnMsg("No se pudo obtener el tipo de cambio para la moneda seleccionda", "warn");
            $InputError=1;
        }
    }
}

//PARA CREAR UNA NUEVA ORDEN DE COMPRA CON ALGUNAS DE LAS PARTIDAS SELECCIONADAS
if (isset($_POST['CrearOrdenPartidas'])) {
    include 'includes/purchordercreation.inc';
}

function fnAgregarRegistrosAutorizacionReq($db, $orderno, $periodo, $statusid = 3)
{
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
    p.nu_ue,
    p.nu_periodo
    FROM purchorders p 
    JOIN purchorderdetails pd on (p.orderno = pd.orderno) 
    LEFT JOIN chartdetailsbudgetbytag cdbt on (p.tagref = cdbt.tagref and pd.clavepresupuestal = cdbt.accountcode ) 
    WHERE p.orderno IN (".$orderno.") AND pd.status = 2
    GROUP BY idreq, noreq, tagref, cvepresupuestal, partida, nu_ue
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
        ///// Se sobreescribe $periodo
        $periodo = $myrow['nu_periodo'];

        // Panel Suficiencia
        $myrow['sn_funcion_id'] = 2302;
        // Estaus de Por Autorizar
        $myrow['statusid'] = $statusid;

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

        fnAgregarSuficienciaGeneral($db, $type, $transno, "Automática", $myrow['statusid'], $myrow['tagref'], 1, $myrow['sn_funcion_id'], $orderno, $ue);
    }

    return true;
}

if (isset($_POST['btnGenerarNuevaOrden'])) {
    // crear nueva orden con la requisicion pero diferente proveedor
    $partidasProveedor = $_POST['partidasProveedor'];
    $numPartida = 1;
    $ordernoNuevaPartidas = 0;
    $errores = 0;

    // Validar que no exista ese proveedor en la compra
    $SQL = "SELECT supplierno FROM purchorders WHERE supplierno = '".$_POST['selectProveedorCambiar']."' AND requisitionno = '".$_SESSION['PO'.$identifier]->RequisitionNo."' AND orderno != '".$_SESSION['PO'.$identifier]->OrderNo."'";
    $TransResult = DB_query($SQL, $db);
    if (DB_num_rows($TransResult) > 0) {
        $errores = 1;
        $mensaje_emergente .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La Requisición ya tiene el proveedor '.$_POST['selectProveedorCambiar'].' </p>';
        $procesoterminado = 2;
    }

    if (trim($_SESSION['PO'.$identifier]->SupplierID) == '-1') {
        // Debe seleccionar proveedor
        prnMsg(_('Seleccionar un Proveedor'), 'error');
        $errores=1;
    }

    if (count($partidasProveedor) == count($_SESSION['PO'.$identifier]->LineItems)) {
        $errores = 1;
        $mensaje_emergente .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se puede separar la compra con todas las partidas. Solo es necesario Autorizar</p>';
        $procesoterminado = 2;
    }

    // echo "<br>partidasProveedor: ".count($partidasProveedor);
    // echo "<br>LineItems: ".count($_SESSION['PO'.$identifier]->LineItems);

    if ($errores == 0) {
        foreach ($partidasProveedor as $partida) {
            if ($numPartida == 1) {
                // Generar orden nueva
                $SQL = "INSERT INTO purchorders (
                supplierno, comments, rate, allowprint, initiator, requisitionno, intostocklocation, deladd1, deladd2, deladd3, deladd4, deladd5, deladd6, contact, version, realorderno, deliveryby, status, stat_comment, tagref, dateprinted, orddate, validfrom, validto, revised, deliverydate, lastUpdated, autorizafecha, fecha_modificacion, consignment, autorizausuario, capturausuario, solicitausuario, status_aurora, supplierorderno, currcode, wo, foliopurch, telephoneContact, refundpercentpurch, totalrefundpercentpurch, systypeorder, noag_ad, servicetype, clavepresupuestal, fileRequisicion, nu_ue, ln_codigo_expediente
                ) SELECT '".$_POST['selectProveedorCambiar']."', comments, rate, allowprint, initiator, requisitionno, intostocklocation, deladd1, deladd2, deladd3, deladd4, deladd5, deladd6, contact, version, realorderno, deliveryby, status, stat_comment, tagref, dateprinted, orddate, validfrom, validto, revised, deliverydate, lastUpdated, autorizafecha, fecha_modificacion, consignment, autorizausuario, capturausuario, solicitausuario, status_aurora, supplierorderno, currcode, wo, foliopurch, telephoneContact, refundpercentpurch, totalrefundpercentpurch, systypeorder, noag_ad, servicetype, clavepresupuestal, fileRequisicion, nu_ue, ln_codigo_expediente
                FROM purchorders
                WHERE orderno = '".$_SESSION['PO'.$identifier]->OrderNo."' ";
                $ErrMsg = "No se agrego la nueva Orden de Compra";
                $TransResult = DB_query($SQL, $db, $ErrMsg);
                $ordernoNuevaPartidas = DB_Last_Insert_ID($db, 'purchorders', 'OrderNo');
            }
            if ($ordernoNuevaPartidas != 0) {
                $SQL = "UPDATE purchorderdetails SET orderno = '".$ordernoNuevaPartidas."' WHERE orderno = '".$_SESSION['PO'.$identifier]->OrderNo."' AND podetailitem = '".$partida."' ";
                $ErrMsg = "No se agrego la nueva Orden de Compra";
                $TransResult = DB_query($SQL, $db, $ErrMsg);
            }
            $numPartida ++;
            // echo "<br>partida: ".$partida;
        }
        if ($ordernoNuevaPartidas != 0) {
            // Renumerar orden actual
            $SQL = "SELECT podetailitem FROM purchorderdetails WHERE orderno = '".$_SESSION['PO'.$identifier]->OrderNo."' ";
            $TransResult = DB_query($SQL, $db);
            $numero = 1;
            while ($myrow = DB_fetch_array($TransResult)) {
                $SQL = "UPDATE purchorderdetails SET orderlineno_ = '".$numero."' WHERE podetailitem = '".$myrow['podetailitem']."'";
                $TransResult2 = DB_query($SQL, $db);
                $numero ++;
            }

            // Renumerar orden nueva
            $SQL = "SELECT podetailitem FROM purchorderdetails WHERE orderno = '".$ordernoNuevaPartidas."' ";
            $TransResult = DB_query($SQL, $db);
            $numero = 1;
            while ($myrow = DB_fetch_array($TransResult)) {
                $SQL = "UPDATE purchorderdetails SET orderlineno_ = '".$numero."' WHERE podetailitem = '".$myrow['podetailitem']."'";
                $TransResult2 = DB_query($SQL, $db);
                $numero ++;
            }

            // Cancelar Suficiencia del orderno y generar 1 para cada compra
            // $SQL = "SELECT tb_suficiencias.nu_type, tb_suficiencias.nu_transno FROM tb_suficiencias WHERE tb_suficiencias.nu_estatus <> '0' AND tb_suficiencias.sn_orderno = '".$_SESSION['PO'.$identifier]->OrderNo."'";
            // $ErrMsg = "No se obtuvieron los registros del Orden ".$_SESSION['PO'.$identifier]->OrderNo;
            // $TransResult = DB_query($SQL, $db, $ErrMsg);
            // $periodoNuevo = 0;
            // if ($myrow = DB_fetch_array($TransResult)) {
            //     $agrego = fnInsertPresupuestoLogMovContrarios($db, $myrow['nu_type'], $myrow['nu_transno']);
            //     if ($agrego) {
            //         $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-success" aria-hidden="true"></i> La Requisición '.$datosClave ['requisitionno'].' ha sido rechazada </p>';

            //         // Cancelar Suficiencia y precomprometido
            //         $SQL = "UPDATE tb_suficiencias SET tb_suficiencias.nu_estatus = 0, tb_suficiencias.sn_description = CONCAT(tb_suficiencias.sn_description, '. Cancelada por desagregación.')
            //         WHERE tb_suficiencias.nu_type = '".$myrow['nu_type']."' AND tb_suficiencias.nu_transno = '".$myrow['nu_transno']."'";
            //         $TransResult2 = DB_query($SQL, $db, $ErrMsg);
            //         $SQL = "UPDATE chartdetailsbudgetlog SET estatus = 0
            //         WHERE type = '".$myrow['nu_type']."' AND transno = '".$myrow['nu_transno']."'";
            //         $TransResult2 = DB_query($SQL, $db, $ErrMsg);
            //     } else {
            //         prnMsg('No se puedo Cancelar la Suficiencia de la '.$_SESSION['PO'.$identifier]->RequisitionNo, 'warn');
            //     }

            //     // Obtener periodo
            //     $SQL = "SELECT period FROM chartdetailsbudgetlog WHERE type = '".$myrow['nu_type']."' AND transno = '".$myrow['nu_transno']."'";
            //     $TransResult2 = DB_query($SQL, $db, $ErrMsg);
            //     if ($myrow2 = DB_fetch_array($TransResult2)) {
            //         $periodoNuevo = $myrow2['period'];
            //     }
            // }

            // Agregar nuevos Registros de Suficiencia y Preecomprometido de las Dos Ordenes
            // fnAgregarRegistrosAutorizacionReq($db, $_SESSION['PO'.$identifier]->OrderNo, $periodoNuevo, 3);
            // fnAgregarRegistrosAutorizacionReq($db, $ordernoNuevaPartidas, $periodoNuevo, 3);

            $nombreProveedor = "";
            $SQL= "SELECT suppname FROM suppliers WHERE supplierid = '".$_POST['selectProveedorCambiar']."'";
            $TransResult = DB_query($SQL, $db);
            if ($myrow = DB_fetch_array($TransResult)) {
                $nombreProveedor = $myrow['suppname'];
            }

            // prnMsg('Se genero la Orden de Compra '.$ordernoNuevaPartidas.' para el proveedor '.$_POST['selectProveedorCambiar'].' - '.$nombreProveedor, 'success');
            // echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . '/panel_ordenes_compra.php?' . '' . "'>";
            $mensaje_emergente = 'Separación de Requisición '.$_SESSION['PO'.$identifier]->RequisitionNo.' con el Proveedor '.$_POST['selectProveedorCambiar'].' - '.$nombreProveedor;
            include('includes/footer_Index.inc');

            ?>
            <script type="text/javascript">
                /**
                 * Función para regresar al panel
                 * @return {[type]} [description]
                 */
                function fnRegresarPanel() {
                    window.open("panel_ordenes_compra.php", "_self");
                }
            </script>
            <?php
            if ($mensaje_emergente != "") {
                ?>
                <script type="text/javascript">
                    var mensajeMod = "<?php echo $mensaje_emergente; ?>";
                    mensajeMod = '<p>'+mensajeMod+'</p>';
                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                    muestraModalGeneral(3, titulo, mensajeMod, '', 'fnRegresarPanel()');
                </script>
                <?php
            }
            exit();
        }
    }
}

// include para duplicar partidas de una misma OC
if (isset($_POST['DuplicaPartidas'])) {
    include 'includes/purchorderduplicateitems.inc';
}

/*Process Quick Entry */
if ($_SESSION['SearchBarcode'] == 1) {
    $flagentradarap = 0;
    for ($j=1; $j<=$_SESSION['QuickEntries']; $j++) {
        if (isset($_POST['part_' . $j]) and $_POST['part_' . $j]!='') {
            $flagentradarap = 1;
        }
    }
}

if (isset($_POST['QuickEntry']) or ($_SESSION['SearchBarcode'] == 1 and $flagentradarap == 1)) {
    $i=1;
    include('includes/SelectPurchOrderItemsProducts_IntoCartV2.inc');
}

if (isset($TieToOrderNumber) and $TieToOrderNumber != '') {
    //echo '<a href="'.$rootpath.'/'.$SelectOrderItemsFile.'?' . SID . 'ModifyOrderNumber='.$TieToOrderNumber.'"><b>' ._('Regresar al Pedido de Venta No.') . $TieToOrderNumber . '</a><br>';
    $_SESSION['PO'.$identifier]->RequisitionNo = $TieToOrderNumber;
    $_SESSION['PO'.$identifier]->totalSuficiencia = abs(fnObtenerTotalSuficienciaAuto($db, $_SESSION['PO'.$identifier]->suficienciaType, $_SESSION['PO'.$identifier]->suficienciaTransno, 263));
    //echo "<br>totalSuficiencia: ".$_SESSION['PO'.$identifier]->totalSuficiencia;
}
// echo "<br>totalSuficiencia: ".$_SESSION['PO'.$identifier]->totalSuficiencia;
// echo "<br>suficienciaType: ".$_SESSION['PO'.$identifier]->suficienciaType;
// echo "<br>suficienciaTransno: ".$_SESSION['PO'.$identifier]->suficienciaTransno;

if (isset($_POST['StockID2']) && $_GET['Edit']=='') {
    $sql = "SELECT
            stockmaster.description,
            purchdata.suppliers_partno,
            stockmaster.pkg_type,
            stockmaster.units,
            stockmaster.netweight,      
            stockmaster.kgs,
            stockmaster.volume
            FROM purchdata INNER JOIN stockmaster
            ON purchdata.stockid=stockmaster.stockid
            WHERE purchdata.stockid='" . $_POST['StockID2'] . "' AND
            purchdata.supplierno='".$_SESSION['PO'.$identifier]->SupplierID."'";
            
    $ErrMsg = _('The stock record of the stock selected') . ': ' . $_POST['Stock'] . ' ' .
        _('cannot be retrieved because');
    $DbgMsg = _('The SQL used to retrieve the supplier details and failed was');
    $result =DB_query($sql, $db, $ErrMsg, $DbgMsg);
    $myrow = DB_fetch_row($result);
    
    $_POST['ItemDescription'] = $myrow[0];
    $_POST['suppliers_partno'] = $myrow[1];
    $_POST['package'] = $myrow[2];
    $_POST['uom'] = $myrow[3];
    $_POST['nw'] = $myrow[4];
    $_POST['gw'] = $myrow[5];
    $_POST['cuft'] = $myrow[6];
}

if (isset($_POST['UpdateLines']) or isset($_POST['AutorizaOrden']) or isset($_POST['SurteOrden']) or isset($_POST['RechazaOrden']) or isset($_POST['PendingOrder'])) {
    // Obtener Estatus Suficiencia
    // Suficiencia por orden de compra
    $SQL = "SELECT nu_transno, nu_estatus FROM tb_suficiencias WHERE sn_orderno = '".$_SESSION['PO'.$identifier]->OrderNo."' ORDER BY nu_transno DESC LIMIT 1";
    // Suficiencia por requisicion
    $SQL = "SELECT tb_suficiencias.nu_transno, tb_suficiencias.nu_estatus, tb_suficiencias.nu_type
    FROM tb_suficiencias 
    LEFT JOIN purchorders ON purchorders.orderno = tb_suficiencias.sn_orderno
    WHERE purchorders.requisitionno = '".$_SESSION['PO'.$identifier]->RequisitionNo."' ORDER BY tb_suficiencias.nu_transno 
    DESC LIMIT 1";
    $datosSuf = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
    $myrowSuf = DB_fetch_array($datosSuf);

    $_SESSION['PO'.$identifier]->suficienciaTransno = $myrowSuf['nu_transno'];
    $_SESSION['PO'.$identifier]->suficienciaEstatus = $myrowSuf['nu_estatus'];

    foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
        //  echo "entra".$_POST['Narrative_'.$POLine->LineNo];
        if ($POLine->Deleted==false) {
            if ($_SESSION['PO'.$identifier]->Wo==0) {
                $POLine->Quantity=$_POST['Qty'.$POLine->LineNo];
            } else {
                if ($permisomod_ot==1) {
                    // validar cantidad en ot solicitada en otras ordenes de compra
                    $SQL="SELECT sum(quantityord)  as cuenta, qtywo
                            FROM purchorderdetails inner join purchorders on purchorders.orderno=purchorderdetails.orderno
                            WHERE purchorders.status not in ('Cancelled','Rejected') 
                              AND  itemcode='". $POLine->StockID ."' AND purchorderdetails.wo=".$_SESSION['PO'.$identifier]->Wo."
                              AND purchorderdetails.orderno!=".$_SESSION['ExistingPurchOrder'];
                    //echo '<pre>sql:'.$SQL;
                    $resultx =DB_query($SQL, $db, $ErrMsg, $DbgMsg);
                    $myrowo = DB_fetch_row($resultx);
                    $cantsol_ot=$myrowo[0];
                    
                    $SQL="SELECT  sum(qtywo)
                          FROM purchorderdetails inner join purchorders on purchorders.orderno=purchorderdetails.orderno
                          WHERE purchorders.status not in ('Cancelled','Rejected')
                            AND  itemcode='". $POLine->StockID ."' AND purchorderdetails.wo=".$_SESSION['PO'.$identifier]->Wo."
                            /* AND purchorderdetails.orderno=".$_SESSION['ExistingPurchOrder']." */";
                    //echo '<pre>sql:'.$SQL;
                    $resultx =DB_query($SQL, $db, $ErrMsg, $DbgMsg);
                    $myrowo = DB_fetch_row($resultx);
                    $cantWO=$myrowo[0];
                    
                    if (($cantWO-($cantsol_ot+$_POST['Qty'.$POLine->LineNo]))<=-0.1) {
                        prnMsg(_('Ha sobrepasado el total de producto solicitado para la Orden de Trabajo,por lo tanto la cantidad no sera actualizada; favor de verificar'), 'warn');
                    } else {
                        $POLine->Quantity=$_POST['Qty'.$POLine->LineNo];
                    }
                } else {
                    if ($POLine->Quantity!=$_POST['Qty'.$POLine->LineNo]) {
                        prnMsg(_('No cuenta con privilegios para modificar la cantidad de la orden de compra'), 'warn');
                    }
                }
            }
            
            $consulta_impuesto = "SELECT margenautcost, taxauthrates.taxrate
                    FROM stockcategory
                    INNER JOIN stockmaster ON stockcategory.categoryid = stockmaster.categoryid
                    INNER JOIN taxauthrates ON stockmaster.taxcatid = taxauthrates.taxcatid
                    WHERE stockmaster.stockid = '" . $POLine->StockID . "'";

            $rsm = DB_query($consulta_impuesto, $db);
            $rowm = DB_fetch_array($rsm);
            $porcentaje_impuesto= 1 + $rowm['taxrate'];
            
            $checkwithtax=$_POST['Itemwithtax_' . $POLine->LineNo];
            if ($checkwithtax==true) {
                    $_POST['Price'.$POLine->LineNo]=$_POST['Price'.$POLine->LineNo] * (1-($_POST['Discount_'.$POLine->LineNo]/100));
                    $_POST['Price'.$POLine->LineNo]=$_POST['Price'.$POLine->LineNo] * (1-($_POST['Discount_1'.$POLine->LineNo]/100));
                    $_POST['Price'.$POLine->LineNo]=$_POST['Price'.$POLine->LineNo] * (1-($_POST['Discount_2'.$POLine->LineNo]/100));
                                
                    $_POST['Price'.$POLine->LineNo] = ($_POST['Price'.$POLine->LineNo] /($porcentaje_impuesto));
                                        
                    $DiscountPercentage=0;
                    $DiscountPercentage1=0;
                    $DiscountPercentage2=0;
            }
                        
            $POLine->Price=$_POST['Price'.$POLine->LineNo];
            
            $POLine->estimated_cost=$_POST['estimated_cost'.$POLine->LineNo];
            $POLine->Desc1=$_POST['Desc1'.$POLine->LineNo];
            $POLine->Desc2=$_POST['Desc2'.$POLine->LineNo];
            $POLine->Desc3=$_POST['Desc3'.$POLine->LineNo];
            
            $fechaentrega = add_ceros(rtrim($_POST['diafechaentrega'.$POLine->LineNo]), 2)."/".add_ceros(rtrim($_POST['mesfechaentrega'.$POLine->LineNo]), 2)."/".rtrim($_POST['aniofechaentrega'.$POLine->LineNo]);

            $POLine->nw=$_POST['nw'.$POLine->LineNo];
            $POLine->ReqDelDate=$fechaentrega; //$_POST['ReqDelDate'.$POLine->LineNo];
            $POLine->Narrative=$_POST['Narrative_'.$POLine->LineNo];
            $POLine->Justification=$_POST['Justification_'.$POLine->LineNo];
            //actualiza % de devolucion
            $POLine->Devolucion=$_POST['Dev'.$POLine->LineNo];
            // echo '<br>Despues<pre>';
            // echo print_r($POLine);
        }
    }
}

// Validaciones para binees y servicios
$validacionServiciosIguales = 0;
$traeBienes = 0;
$traeServicios = 0;
$numServicios = 0;
$claveServicio = '';
$clavePresupuestal = '';
foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
    if ($POLine->Deleted==false) {
        if ($POLine->mbflag == 'B') {
            // Tiene bienes
            $traeBienes = 1;
        }
        if ($POLine->mbflag == 'D') {
            // Tiene servicios
            $traeServicios = 1;
            $numServicios ++;

            if ($numServicios == 1) {
                $claveServicio = $POLine->StockID;
                $clavePresupuestal = $POLine->clavepresupuestal;
            }

            if ($numServicios != 1 && $claveServicio != '' && $claveServicio != $POLine->StockID) {
                // Validar si son datos diferentes
                $validacionServiciosIguales = 1;
            }
        }
    }
}
if ($validacionServiciosIguales == 1) {
    // Mensaje si vienen servicios iguales
    $mensaje_emergente .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Existen Servicios Diferentes</p>';
    $procesoterminado = 2;
}

// var_dump($_SESSION['PO'.$identifier]->LineItems);

if (isset($_POST['btnAvanzar']) or isset($_POST['PendingOrder']) or isset($_POST['CancelaOrden']) or isset($_POST['AutorizaOrden']) or isset($_POST['RechazaOrden']) or isset($_POST['SurteOrden']) or isset($_POST['PorAutorizar'])) {
    //var_dump($_SESSION['PO'.$identifier]->LineItems);
    
    if ($_SESSION['PO'.$identifier]->DelAdd1=='' or strlen($_SESSION['PO'.$identifier]->DelAdd1)<3) {
        prnMsg(_('La Orden de Compra NO se pudo procesar porque no se especific&oacute; la direcci&oacute;n de entrega...'), 'error');
        $InputError=1;
    } elseif ($_SESSION['PO'.$identifier]->Location=='' or ! isset($_SESSION['PO'.$identifier]->Location)) {
        prnMsg(_('La Orden de Compra NO se pudo procesar porque no se especific&oacute; un Almac&eacute;n...'), 'error');
        $InputError=1;
    } elseif ($_SESSION['PO'.$identifier]->LinesOnOrder <=0) {
        prnMsg(_('La Orden de Compra NO se pudo procesar porque no se agregaron productos a esta Orden...'), 'error');
        
        $InputError=1;
    }

    if (trim($_SESSION['PO'.$identifier]->SupplierID) == '-1') {
        // Debe seleccionar proveedor
        prnMsg(_('Seleccionar un Proveedor'), 'error');
        $InputError=1;
    }
    
    // validar que exista preferencias de empresa para las cuentas contables
    $consulta = "SELECT * FROM companies LIMIT 1";
    $resultado = DB_query($consulta, $db, $ErrMsg, $DbgMsg);
    
    if (!DB_fetch_array($resultado)) {
        prnMsg(_('No tiene preferencias de empresa configuradas...'), 'warm');
        $InputError=1;
    }
    
    // traer el numero de la unidad de negocio
    $sql ='select tagref FROM locations l where loccode="'.$_SESSION['PO'.$identifier]->Location.'"';
    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
    $myrowloc = DB_fetch_array($result);
    $tagref = $myrowloc['tagref'];
    
    // traer el periodo que se va a afectar para las cuentas de presupuesto
    $fecha_creada= date_create($_SESSION['PO'.$identifier]->Orig_OrderDate);
    $fecha_formateada= date_format($fecha_creada, "d/m/Y");
    $periodo = GetPeriod($fecha_formateada, $db, $tagref);
    //echo "<script>alert('Periodo: ".$periodo."');</script>";
    //echo "<script>alert('Fecha: ".$fecha_formateada."');</script>";
      
    foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
             $total_clave_presupuestal=0;
            //buscar margen automatico para costo en categoria de inventario
            $consulta_impuesto = "SELECT margenautcost, taxauthrates.taxrate
                    FROM stockcategory
                    INNER JOIN stockmaster ON stockcategory.categoryid = stockmaster.categoryid
                    INNER JOIN taxauthrates ON stockmaster.taxcatid = taxauthrates.taxcatid
                    WHERE stockmaster.stockid = '" . $POLine->StockID . "'";

            $rsm = DB_query($consulta_impuesto, $db);
            $rowm = DB_fetch_array($rsm);
            $porcentaje_impuesto= 1 + $rowm['taxrate'];
                                
        if ($POLine->Deleted==false) {
            $clavepresupuestal= $_POST["clavepresupuestal_".$POLine->LineNo];
            //$clavepresupuestal_validar= substr($clavepresupuestal, 0, 31);
            $axuliar_clave_presupuestal = $clavepresupuestal;
              
            //if($clavepresupuestal != $axuliar_clave_presupuestal){
               /* foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine2) {

                    if($clavepresupuestal == $_POST["clavepresupuestal_".$POLine2->LineNo]){
                        $total_clave_presupuestal = $POLine2->subtotal_amount + $total_clave_presupuestal;
                    }
                }*/
            //}
               
                
         /*   if($_POST["presupuesto_".$POLine->LineNo]<$total_clave_presupuestal){
                $total_disponible = '**'.$_POST["presupuesto_".$POLine->LineNo];
                 $InputError=1;
                 prnMsg( _('Ha sobrepasado el total disponible de la clave presupuestal '._.$clavepresupuestal. '- '.$total_disponible.' ...'.$total_clave_presupuestal),'error');
                
            }*/
                    
            if (empty($clavepresupuestal)) {
                prnMsg(_('El producto '.$POLine->ItemDescription.' debe llevar Clave Presupuestal...'), 'error');
                $InputError=1;
            } else {
                foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine2) {
                    if ($clavepresupuestal == $_POST["clavepresupuestal_".$POLine2->LineNo]) {
                        $total_producto= ($POLine2->Quantity * $POLine2->Price) * $porcentaje_impuesto;
                        $total_clave_presupuestal= $total_producto + $total_clave_presupuestal ;
                    }
                }
                    //$total_producto= ($POLine->Quantity * $POLine->Price) * $porcentaje_impuesto;
                     //echo "<script>alert('Total producto: ".$total_producto."');</script>";
                if ($_SESSION['UserID'] == "admin") {
                     // echo $clavepresupuestal_validar;
                }
                    
                        //$presupuestodisponible= TraePresupuestoDisponible($clavepresupuestal, $tagref, $periodo, $db);
                    //echo "<script>alert('Disponible: ".$presupuestodisponible."');</script>";
                $total_producto= truncateFloat($total_producto, $digitos);
                    //echo "<script>alert('Total producto (truncado): ".$total_producto."');</script>";
                    //if ($total_clave_presupuestal > $presupuestodisponible) {
                if ("1" == "2") {   // cambios if
                    // echo $total_producto .'-'. $presupuestodisponible.'<br>'.$clavepresupuestal;
                   // prnMsg(_('No hay Presupuesto Disponible para el producto '.$POLine->ItemDescription.' o No Existe esa Clave Presupuestal...Verifique el Presupuesto.--total:'. $total_clave_presupuestal.'--clave'.$clavepresupuestal.'dispo-'.$presupuestodisponible),'error');
                    prnMsg(_('No hay Presupuesto Disponible para el producto '.$POLine->ItemDescription.';').'<br>'._('El disponible para la clave: '.$clavepresupuestal.' es de $'.$presupuestodisponible.' y lleva un acumulado de $'.$total_clave_presupuestal), 'error');
                       
                    $InputError=1;
                } else {
                    $sql ='SELECT tagref FROM locations l where loccode="'.$_SESSION['PO'.$identifier]->Location.'"';
                    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                    $myrowloc = DB_fetch_array($result);
                    $tagref = $myrowloc['tagref'];

                    $sql = "SELECT * FROM chartdetailsbudgetbytag WHERE tagref = '".$tagref."' AND accountcode = '".$clavepresupuestal."'";
                    //$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                    //DB_query($SQL, $db);

                    /*if (DB_num_rows($result) == 0) {
                         prnMsg(_('la clave presupuestal "'.$clavepresupuestal.'" para el producto '.$POLine->ItemDescription.' es la incorrecta para el tipo de gasto seleccionado'), 'error');
                        $InputError=1;
                    }*/
                }
            }

            if ($POLine->Price == '' or $POLine->Price == '0' and isset($_POST['AutorizaOrden'])) {
                prnMsg(_('La Orden de Compra NO se pudo procesar porque no se especifico el precio...'), 'error');
                $InputError=1;
            }

            if ($POLine->ReqDelDate=="") {
                prnMsg(_('La Orden de Compra no se pudo procesar porque la fecha de entrega en la linea '.$POLine->LineNo.' es vacia'), 'error');
                $InputError=1;
            }

            $POLine->clavepresupuestal= $clavepresupuestal;
        }
        $total_orden.=$total_producto;
    }
    
    /*Validaci�n que la suma de los productos que correspondan con la  clave presupuestal vs contra el disponible de esa clave sea correcta*/
    //var_dump($_SESSION['PO'.$identifier]->LineItems->clavepresupuestal);
    /*
    foreach ($M2t as $row=>$tmp) 
    {
    $presupuestodisponible_tot= TraePresupuestoDisponible($clavepresupuestal, $tagref, $periodo, $db);
    $total_orden= truncateFloat($total_orden, $digitos);
    if ($total_orden > $presupuestodisponible_tot)
    {
            prnMsg(_('No hay Presupuesto Disponible,  '.$POLine->ItemDescription.' o No Existe esa Clave Presupuestal...Verifique el Presupuesto.'),'error');
            $InputError=1;
    }
    /**/

    // Validar Total Suficiencia Inicio
    $totalValida = 0;
    $traeBienes = 0;
    $traeServicios = 0;
    foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
        if ($POLine->Deleted==false) {
            $price = $POLine->Price;
            if ($oldcurrency != $_SESSION['PO'.$identifier]->CurrCode) {
                if ($_SESSION['PO'.$identifier]->CurrCode == "MXN") {
                    $price *=$rateFromAnyToMXN;
                } else {
                    $price *=$_SESSION['PO'.$identifier]->ExRate;
                }
                $price = round($price, 4);
            }
            $LineTotal = $POLine->Quantity * $price * (1 - ($POLine->Desc1/100));
            $LineTotal = $LineTotal * (1 - ($POLine->Desc2/100));
            $LineTotal = $LineTotal * (1 - ($POLine->Desc3/100));
            $LineTotal = $LineTotal; /*+ $POLine->estimated_cost;*/

            $totalValida = $totalValida + $LineTotal;

            if ($POLine->mbflag == 'B') {
                // Tiene bienes
                $traeBienes = 1;
            }
            if ($POLine->mbflag == 'D') {
                // Tiene servicios
                $traeServicios = 1;
            }
        }
    }
    if (number_format($totalValida, 2, '.', '') > number_format($_SESSION['PO'.$identifier]->totalSuficiencia, 2, '.', '') && !isset($_POST['PendingOrder'])) {
        // Validacion Suficiencia y Compra
        $InputError=1;
    }
    if ($traeBienes == 1 && $traeServicios == 1) {
        // La compra tiene bienes y servicios
        $InputError=1;
    }
    // Validar Total Suficiencia FIN

    if ($InputError!=1) {
        //$GRN = GetNextTransNo(25, $db);//
        //$GRN = GetNextTransNo(555, $db);//Nuevo tipo para autorizaciòn de ordenes de compra
   
        $fechaembarque = $_POST['AnioEmb']."-".add_ceros($_POST['MesEmb'], 2)."-".add_ceros($_POST['DiaEmb'], 2);
        $fechaaduana = $_POST['AnioFechaAduana']."-".add_ceros($_POST['MesFechaAduana'], 2)."-".add_ceros($_POST['DiaFechaAduana'], 2);

        if (strpos($fechaembarque, "00")) {
            $fechaembarque= "1900-01-01";
        }

        if (strpos($fechaaduana, "00")) {
            $fechaaduana= "1900-01-01";
        }
        
        $emailsql='SELECT email FROM www_users WHERE userid="'.$_SESSION['UserID'].'"';
        $emailresult=DB_query($emailsql, $db);
        $emailrow=DB_fetch_array($emailresult);
        //$sql = 'BEGIN';
        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The database does not support transactions');
        $DbgMsg = _('The following SQL to start an SQL transaction was used');

        $sql ='SELECT tagref FROM locations l WHERE loccode="'.$_SESSION['PO'.$identifier]->Location.'"';
        $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
        $myrowloc = DB_fetch_array($result);
        $tagref = $myrowloc['tagref'];

        // Folio de la poliza por unidad ejecutora
        $folioPolizaUe = 0;
        if (isset($_POST['AutorizaOrden'])) {
            $GRN = GetNextTransNo(555, $db);    //Nuevo tipo para autorizaciòn de ordenes de compra
            $folioPolizaUe = fnObtenerFolioUeGeneral($db, $tagref, $_SESSION['PO'.$identifier]->unidadEjecutora, 555);
        }
        //$result = DB_query($sql,$db);
        //$result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
                
        // inicia bloque transacciones
        $result = DB_Txn_Begin($db);
                
        if ($_SESSION['ExistingPurchOrder']==0) {
            $date = date($_SESSION['DefaultDateFormat']);
            $StatusComment = $date.' - Creada: '.$_SESSION['UserID'].' - '.$_SESSION['PO'.$identifier]->StatusMessage.'<br>';
            /*Insert to purchase order header record */
            
            $sql ='SELECT tagref FROM locations l WHERE loccode="'.$_SESSION['PO'.$identifier]->Location.'"';
            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
            $myrowloc = DB_fetch_array($result);
            $tagref = $myrowloc['tagref'];

            $sqllegal = "SELECT legalid
                        FROM tags
                        WHERE tagref = '".$tagref."'";
                        
            $resultlegal = DB_query($sqllegal, $db);
            $myrowlegal = DB_fetch_array($resultlegal);
            //
            $_GET['Tagref'] = $tagref;
            $_GET['legalid'] = $myrowlegal['legalid'];
            //$_GET['tipodocto'] = 25;
            $_GET['tipodocto'] = 555;
                      
            $SQL= " SELECT l.taxid,l.address5,t.tagname,t.areacode,l.legalid,t.typeinvoice
                   FROM legalbusinessunit l, tags t
                   WHERE l.legalid=t.legalid AND tagref='".$tagref."'";
            
            $Result= DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
            
            if (DB_num_rows($Result)==1) {
                $myrowtags = DB_fetch_array($Result);
                $rfc=trim($myrowtags['taxid']);
                $keyfact=$myrowtags['address5'];
                $nombre=$myrowtags['tagname'];
                $area=$myrowtags['areacode'];
                $legaid=$myrowtags['legalid'];
                $tipofacturacionxtag=$myrowtags['typeinvoice'];
            }
            
            //****//
            $InvoiceNoTAG = DocumentNext(30, $tagref, $area, $legaid, $db);
            $transno_presupuesto = GetNextTransNo(49, $db);

            // generar folio de orden de compra
            if (empty($_SESSION['PO'.$identifier]->OrderNo2) && isset($_POST['AutorizaOrden'])) {
                $_SESSION['PO'.$identifier]->OrderNo2= GetNextTransNo(18, $db);
            }
            
            $separa = explode('|', $InvoiceNoTAG);
            $serie = $separa[1];
            $folio = $separa[0];
            $foliocompra= $serie.'|'.$folio ;
            
            if (isset($TieToOrderNumber)) {
                $_SESSION['PO'.$identifier]->RequisitionNo = $TieToOrderNumber;
            }
            //$_SESSION['PO'.$identifier]->Orig_OrderDate = FormatDateForSQL($_SESSION['PO'.$identifier]->Orig_OrderDate);
            
            if (empty($_SESSION['PO'.$identifier]->version)) {
                $_SESSION['PO'.$identifier]->version = "0";
            }
            if (empty($_SESSION['PO'.$identifier]->Typeorder)) {
                $_SESSION['PO'.$identifier]->Typeorder = "0";
            }
            if (empty($_SESSION['PO'.$identifier]->ServiceType)) {
                $_SESSION['PO'.$identifier]->ServiceType = "0";
            }
            if (empty($_SESSION['PO'.$identifier]->OrderNo2)) {
                $_SESSION['PO'.$identifier]->OrderNo2 = "0";
            }

            $sql = "INSERT INTO purchorders (
                    supplierno,
                    comments,
                    orddate,
                    rate,
                    currcode,
                    initiator,
                    requisitionno,
                    intostocklocation,
                    deladd1,
                    deladd2,
                    deladd3,
                    deladd4,
                    deladd5,
                    deladd6,
                    version,
                    realorderno,
                    revised,
                    deliveryby,
                    servicetype,
                    status,
                    autorizausuario,
                    autorizafecha,
                    stat_comment,
                    deliverydate,
                    foliopurch,
                    tagref,
                    noag_ad,
                    systypeorder,
                    contact,
                    telephoneContact,
                    clavepresupuestal
                    )
                VALUES(
                    '" . $_SESSION['PO'.$identifier]->SupplierID . "',
                    '" . $_SESSION['PO'.$identifier]->Comments . "',
                    '" . $_SESSION['PO'.$identifier]->Orig_OrderDate . "',
                    '" . number_format($_SESSION['PO'.$identifier]->ExRate, $decimalesTipoCambio) . "',
                    '" . $_SESSION['PO'.$identifier]->CurrCode . "',        
                    '" . $_SESSION['UserID'] . "',
                    '" . $_SESSION['PO'.$identifier]->RequisitionNo . "',
                    '" . $_SESSION['PO'.$identifier]->Location . "',
                    '" . $_SESSION['PO'.$identifier]->DelAdd1 . "',
                    '" . $_SESSION['PO'.$identifier]->DelAdd2 . "',
                    '" . $_SESSION['PO'.$identifier]->DelAdd3 . "',
                    '" . $_SESSION['PO'.$identifier]->DelAdd4 . "',
                    '" . $_SESSION['PO'.$identifier]->DelAdd5 . "',
                    '" . $_SESSION['PO'.$identifier]->DelAdd6 . "',
                    '" . $_SESSION['PO'.$identifier]->version . "',         
                    '" . $_SESSION['PO'.$identifier]->OrderNo2 . "',
                    '" . FormatDateForSQL($date) . "',
                    '" . $_SESSION['PO'.$identifier]->deliveryby . "',
                    '" . $_SESSION['PO'.$identifier]->ServiceType . "',
                ";
            
            if (isset($_POST['Commit'])) {
                $sql.="'" . 'Printed'. "',";
            } elseif (isset($_POST['PendingOrder'])) {
                $sql.="'" . 'Pending'. "',";
            } elseif (isset($_POST['CancelaOrden'])) {
                $sql.="'" . 'Cancelled'. "',";
            } elseif (isset($_POST['AutorizaOrden'])) {
                $sql.="'" . 'Authorised'. "',";
            } elseif (isset($_POST['RechazaOrden'])) {
                $sql.="'" . 'Rejected'. "',";
            } elseif (isset($_POST['SurteOrden'])) {
                $sql.="'" . 'Delivered'. "',";
            }
            
            if (isset($_POST['AutorizaOrden'])) {
                $sql .= "'" . $_SESSION['UserID'] . "',";
                $sql .= "NOW(),";
            } else {
                $sql .= "'',";
                $sql .= "NOW(),";
            }
            
            $sql.="     '" . $StatusComment . "',
                    '" . $_SESSION['PO'.$identifier]->Orig_OrderDate . "',
                    '" . $foliocompra . "',
                    '".$tagref."',
                    '" . $_POST['noag_ad'] . "',
                    '" . $_SESSION['PO'.$identifier]->Typeorder . "',
                    '" . $_SESSION['PO'.$identifier]->contact . "',
                    '" . $_SESSION['PO'.$identifier]->telephoneContact . "',
                    '" . $_SESSION['PO'.$identifier]->ClavePresupuestal. "'
                )";

            $ErrMsg =  _('The purchase order header record could not be inserted into the database because');
            $DbgMsg = _('The SQL statement used to insert the purchase order header record and failed was');
            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
            //echo 'sql:'.$sql;
            /*Get the auto increment value of the order number created from the SQL above */
            #$_SESSION['PO'.$identifier]->OrderNo =  GetNextTransNo(18, $db);
            
            $_SESSION['PO'.$identifier]->OrderNo = DB_Last_Insert_ID($db, 'purchorders', 'OrderNo');
            
            $aplicadevtotal=false;

            foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
                if ($POLine->Deleted==false) {
                    if ($aplicadevtotal==false and $POLine->totalpurch==1) {
                        $aplicadevtotal=true;
                    }
                    //ALTER TABLE `grupobrana`.`purchorderdetails` ADD COLUMN `justification` varchar(450) NOT NULL DEFAULT '' AFTER `narrative`;
                    //echo "<br>fecha:".$POLine->ReqDelDate;Orig_OrderDate
                    if (empty($POLine->Desc1)) {
                        $POLine->Desc1 = "0";
                    }
                    if (empty($POLine->Desc2)) {
                        $POLine->Desc2 = "0";
                    }
                    if (empty($POLine->Desc3)) {
                        $POLine->Desc3 = "0";
                    }
                    if (empty($POLine->Devolucion)) {
                        $POLine->Devolucion = "0";
                    }
                    if (empty($POLine->estimated_cost)) {
                        $POLine->estimated_cost = "0";
                    }
                    if (Havepermission($_SESSION['UserID'], 1440, $db) == 0) {
                        $fechaembarque = "1900-01-01"; //date('Y-m-d');
                        $fechaaduana = "1900-01-01"; //date('Y-m-d');
                    }

                    $sql = "INSERT INTO purchorderdetails (
                            orderno,
                            itemcode,
                            deliverydate,
                            itemdescription,
                            glcode,
                            unitprice,
                            quantityord,
                            shiptref,
                            jobref,
                            itemno,
                            uom,
                            suppliers_partno,
                            subtotal_amount,
                            package,
                            pcunit,
                            nw,
                            gw,
                            cuft,
                            total_quantity,
                            total_amount,
                            discountpercent1,
                            discountpercent2,
                            discountpercent3,
                            narrative,
                            justification,
                            refundpercent,
                            estimated_cost,
                            customs,
                            pedimento,
                            dateship,
                            datecustoms,
                            clavepresupuestal
                            )
                    VALUES (
                            '" . $_SESSION['PO'.$identifier]->OrderNo . "',
                            '" . $POLine->StockID . "',
                            '" . FormatDateForSQL($POLine->ReqDelDate) . "',
                            '" . DB_escape_string($POLine->ItemDescription) . "',
                            '" . $POLine->GLCode . "',
                            '" . $POLine->Price . "',
                            '" . $POLine->Quantity . "',
                            '" . $POLine->ShiptRef . "',
                            '" . $POLine->JobRef . "',
                            '" . $POLine->itemno . "',
                            '" . $POLine->uom . "',
                            '" . $POLine->suppliers_partno . "',
                            '" . $POLine->subtotal_amount . "',
                            '" . $POLine->package . "',
                            '" . $POLine->pcunit . "',
                            '" . $POLine->nw . "',
                            '" . $POLine->gw . "',
                            '" . $POLine->cuft . "',                    
                            '" . $POLine->total_quantity . "',
                            '" . $POLine->total_amount . "',
                            '" . $POLine->Desc1 . "',
                            '" . $POLine->Desc2 . "',
                            '" . $POLine->Desc3 . "',
                            '" . $POLine->Narrative . "',
                            '" . $POLine->Justification . "',
                            '" . $POLine->Devolucion . "',
                            '" . $POLine->estimated_cost . "',
                            '". $_POST['aduana']."',
                            '". $_POST['pedimento']."',
                            '". $fechaembarque."',      
                            '". $fechaaduana."',
                            '". $_POST['clavepresupuestal_'.$POLine->LineNo]."'
                            )";
                    $ErrMsg =_('One of the purchase order detail records could not be inserted into the database because');
                    $DbgMsg =_('The SQL statement used to insert the purchase order detail record and failed was');
                    $result =DB_query($sql, $db, $ErrMsg, $DbgMsg);
                    if ($POLine->PODetailRec=='') {
                        $POLine->PODetailRec= DB_Last_Insert_ID($db, 'purchorderdetails', 'podetailitem');
                    }
                }
                                
                if (isset($_POST['AutorizaOrden'])) {
                    if ($_SESSION['UserID'] == "admin") {
                        //echo '<;>entro1';
                    }
                    if ($POLine->Quantity != 0 and $POLine->Quantity != '' and isset($POLine->Quantity)) {
                        if ($_SESSION['UserID'] == "admin") {
                            //echo '<br>entro2';
                        }
                        if ($_SESSION['PO']->ExRate == '') {
                            $_SESSION['PO']->ExRate = 1;
                        }

                        //buscar margen automatico para costo en categoria de inventario
                        $qry = "SELECT margenautcost, taxauthrates.taxrate
                        FROM stockcategory
                        INNER JOIN stockmaster ON stockcategory.categoryid = stockmaster.categoryid
                        INNER JOIN taxauthrates ON stockmaster.taxcatid = taxauthrates.taxcatid
                        WHERE stockmaster.stockid = '" . $POLine->StockID . "'";
                        $rsm = DB_query($qry, $db);
                        $rowm = DB_fetch_array($rsm);
                        $margenautcost = $rowm['margenautcost'] / 100;
                        $porcentaje_impuesto= 1 + $rowm['taxrate'];
                        $POLine->Price += ($POLine->Price * $margenautcost);
                        $LocalCurrencyPrice = ($POLine->Price / $_SESSION['PO']->ExRate);

                        if ($POLine->StockID != '') {
                            $avgcost = 0;
                            $avgcost = $LocalCurrencyPrice;
                            $avgcost = $avgcost - ($avgcost * ($POLine->Desc1 / 100));
                            $avgcost = $avgcost - ($avgcost * ($POLine->Desc2 / 100));
                            $avgcost = $avgcost - ($avgcost * ($POLine->Desc3 / 100));
                        }
                                            
                            $purchdatasql = 'SELECT conversionfactor,price
                        FROM purchdata
                        WHERE purchdata.supplierno = "' . $_SESSION['PO']->SupplierID . '"
                        AND purchdata.stockid="' . $POLine->StockID . '"';
                        
                            $rsm = DB_query($purchdatasql, $db);
                            $rowm = DB_fetch_array($rsm);

                            $factordeConversion = 1;
                        if (is_numeric($rowm['conversionfactor'])) {
                                $factordeConversion = $rowm['conversionfactor'];
                        }
                                            
                        $CurrentStandardCost = $avgcost;
                        $CurrentStandardCost = $CurrentStandardCost / $factordeConversion;
                        //$totalcompra= ($CurrentStandardCost * $POLine->Quantity) * $porcentaje_impuesto;
                        $totalcompra= ($CurrentStandardCost * $POLine->Quantity);
                    }

                    $totalcompra= truncateFloat($totalcompra, $digitos);
                                    
                    //$PeriodNo = GetPeriod(date('d/m/Y'), $db, 0);ç
                    $fecha = explode('-', $_SESSION['PO'.$identifier]->Orig_OrderDate);
                    $fechaperiod = $fecha[2].'/'.$fecha[1].'/'.$fecha[0];

                    $infoClaves = array();
                    $infoClaves[] = array(
                        'accountcode' => $_POST['clavepresupuestal_'.$POLine->LineNo]
                    );
                    $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
                    if (!$respuesta['result']) {
                        $data['msg'] .= $respuesta['mensaje'];
                            $flag++;
                            continue;
                    }
                    /////$PeriodNo = GetPeriod($fechaperiod, $db, 0);
                    $PeriodNo = $respuesta['periodo'];
                    $fechapoliza = $respuesta['fecha'];
                                    
                    //$resultado= GeneraMovimientoContablePresupuesto(25, "POREJERCER", "COMPROMETIDO", $GRN, $PeriodNo,
                    //$totalcompra, $tagref, $_SESSION['PO'.$identifier]->Orig_OrderDate, $_POST['clavepresupuestal_'.$POLine->LineNo], $_SESSION['PO'.$identifier]->OrderNo,$db);
                                    
                    $resultado= GeneraMovimientoContablePresupuesto(
                        555,
                        "POREJERCER",
                        "COMPROMETIDO",
                        $GRN,
                        $PeriodNo,
                        $totalcompra,
                        $tagref,
                        $fechapoliza,
                        $_POST['clavepresupuestal_'.$POLine->LineNo],
                        $_SESSION['PO'.$identifier]->OrderNo,
                        $db,
                        false,
                        '',
                        '',
                        $_SESSION['PO'.$identifier]->Comments,
                        $_SESSION['PO'.$identifier]->unidadEjecutora,
                        1,
                        0,
                        $folioPolizaUe
                    );

                    // Actualizar enlace poliza
                    $sql = "UPDATE gltrans SET purchno = '".$_SESSION['PO'.$identifier]->OrderNo."' 
                    WHERE type = '555' AND typeno = '".$GRN."'";
                    $result =DB_query($sql, $db);

                    //echo "<br>GRN: ".$GRN;
                    // Log Presupuesto
                    $descriptionLog = "Autorización Orden de Compra ".$_SESSION['PO'.$identifier]->OrderNo2.". Requisición ".$_SESSION['PO'.$identifier]->RequisitionNo;
                    // Se resta a la suficiencia el total
                    $agregoLog = fnInsertPresupuestoLog($db, $_SESSION['PO'.$identifier]->suficienciaType, $_SESSION['PO'.$identifier]->suficienciaTransno, $tagref, $_POST['clavepresupuestal_'.$POLine->LineNo], $PeriodNo, $totalcompra, 263, "", $descriptionLog, 0, '', 0, $_SESSION['PO'.$identifier]->unidadEjecutora); // Suficiencia Automatica
                    $agregoLog = fnInsertPresupuestoLog($db, 555, $GRN, $tagref, $_POST['clavepresupuestal_'.$POLine->LineNo], $PeriodNo, $totalcompra, 258, "", $descriptionLog, 1, '', 0, $_SESSION['PO'.$identifier]->unidadEjecutora); // Abono
                    $agregoLog = fnInsertPresupuestoLog($db, 555, $GRN, $tagref, $_POST['clavepresupuestal_'.$POLine->LineNo], $PeriodNo, $totalcompra * -1, 259, "", $descriptionLog, 1, '', 0, $_SESSION['PO'.$identifier]->unidadEjecutora); // Cargo
                    //echo "<br>realizo movimientos 1055";
                    
                    // Obtener periodos para comenzar la separacion de montos
                    $SQL = "SELECT periods.periodno, cat_Months.mes FROM chartdetailsbudgetbytag
                    JOIN periods ON YEAR(periods.lastdate_in_period) = chartdetailsbudgetbytag.anho
                    JOIN cat_Months ON u_mes = MONTH(periods.lastdate_in_period)
                    WHERE chartdetailsbudgetbytag.accountcode = '".$_POST['clavepresupuestal_'.$POLine->LineNo]."'
                    AND periods.periodno <= '".GetPeriod(date('d/m/y'), $db)."'
                    ORDER BY periods.periodno DESC";
                    $ErrMsg = "No se obtuvieron los periodos de la clave ";
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                    $cantidadVal = abs($totalcompra);
                    $movimientoTipo = 'Suficiencia';
                    $TransResult = ""; // No reccorrer ciclo, esta pendiente el acomulado
                    while ($myrow = DB_fetch_array($TransResult)) {
                        // Validar y generar registros en log
                        $disponible = fnInfoPresupuesto($db, $_POST['clavepresupuestal_'.$POLine->LineNo], $myrow['periodno'], '', '', 0, 0, '', $_SESSION['PO'.$identifier]->suficienciaType, $_SESSION['PO'.$identifier]->suficienciaTransno, 'Reduccion', '', '', '', 1, '', '', 0, 0);
                        
                        foreach ($disponible as $dispo) {
                            if (abs($cantidadVal) == 0) {
                                // Terminar operaciones
                                break;
                            }
                            
                            if ($dispo[$myrow['mes'].$movimientoTipo] > abs(0)) {
                                // Si tiene disponible registrar en el log
                                $cantidadRegistro = 0;
                                if ($dispo[$myrow['mes'].$movimientoTipo] >= abs($cantidadVal)) {
                                    // Registrar cantidad y ya que se tiene mas disponible
                                    $cantidadRegistro = abs($cantidadVal);
                                } else if (abs($cantidadVal) >= $dispo[$myrow['mes'].$movimientoTipo]) {
                                    // Registrar disponible ya que es mayor la cantidad
                                    $cantidadRegistro = abs($dispo[$myrow['mes'].$movimientoTipo]);
                                }

                                $cantidadVal = abs($cantidadVal) - abs($cantidadRegistro);

                                // Log Presupuesto
                                $descriptionLog = "Autorización Orden de Compra ".$_SESSION['PO'.$identifier]->OrderNo2.". Requisición ".$_SESSION['PO'.$identifier]->RequisitionNo;
                                // Se resta a la suficiencia el total
                                $agregoLog = fnInsertPresupuestoLog($db, $_SESSION['PO'.$identifier]->suficienciaType, $_SESSION['PO'.$identifier]->suficienciaTransno, $tagref, $_POST['clavepresupuestal_'.$POLine->LineNo], $myrow['periodno'], $cantidadRegistro, 263, "", $descriptionLog, 0, '', 0, $_SESSION['PO'.$identifier]->unidadEjecutora); // Suficiencia Automatica
                                $agregoLog = fnInsertPresupuestoLog($db, 555, $GRN, $tagref, $_POST['clavepresupuestal_'.$POLine->LineNo], $myrow['periodno'], $cantidadRegistro, 258, "", $descriptionLog, 1, '', 0, $_SESSION['PO'.$identifier]->unidadEjecutora); // Abono
                                $agregoLog = fnInsertPresupuestoLog($db, 555, $GRN, $tagref, $_POST['clavepresupuestal_'.$POLine->LineNo], $myrow['periodno'], $cantidadRegistro * -1, 259, "", $descriptionLog, 1, '', 0, $_SESSION['PO'.$identifier]->unidadEjecutora); // Cargo

                                if (!$agregoLog) {
                                    $respuesta = false;
                                }
                            }
                        }

                        if (abs($cantidadVal) == 0) {
                            // Terminar operaciones
                            break;
                        }
                    }
                    
                    // Estatus Autorizado Suficiencia Automatica
                    $sql = "UPDATE tb_suficiencias SET nu_estatus = 4 WHERE nu_type = '".$_SESSION['PO'.$identifier]->suficienciaType."' and nu_transno = '".$_SESSION['PO'.$identifier]->suficienciaTransno."'";
                    $result =DB_query($sql, $db);

                    $sql = "UPDATE chartdetailsbudgetlog SET estatus = 4 WHERE type = '".$_SESSION['PO'.$identifier]->suficienciaType."' and transno = '".$_SESSION['PO'.$identifier]->suficienciaTransno."'";
                    $result =DB_query($sql, $db);

                    // Autorización de no existencia
                    $sql = "UPDATE tb_no_existencias SET status = 2 WHERE nu_id_requisicion = '".$_SESSION['PO'.$identifier]->RequisitionNo."'";
                    $result =DB_query($sql, $db);
                }
                //echo "<br>movimientos 1057";
                if (isset($_POST['AutorizaOrden']) && $automaticpurchdata == 1) {
                    $SQLOC = "SELECT * FROM purchdata WHERE purchdata.stockid = '".$POLine->StockID."'";
                    $ResultOC = DB_query($SQLOC, $db);
                    if (DB_num_rows($ResultOC) == 0) {
                        $sql = "INSERT INTO purchdata (supplierno, 
                                    stockid, 
                                    price, 
                                    conversionfactor, 
                                    leadtime, 
                                    preferred, 
                                    effectivefrom, 
                                    interno, 
                                    pcurrcode,
                                    tagref)
                                VALUE(
                                    '".$_SESSION['PO'.$identifier]->SupplierID."',
                                    '".$POLine->StockID."',
                                    '".$POLine->Price."',
                                    1,
                                    1,
                                    1,
                                    '". $_SESSION['PO'.$identifier]->Orig_OrderDate."',
                                    0,
                                    '". $_SESSION['PO'.$identifier]->CurrCode."',       
                                    '".$tagref."'   
                                )";
                        $result =DB_query($sql, $db, $ErrMsg, $DbgMsg);
                    }
                }
            }
            
            if (isset($_POST['PendingOrder'])) {
                // Env�a solicitud de cotizaci�n
                //echo 'entraaaaa unoo';
                if (strlen($_SESSION['POReceiveEmail'])>0) {
                    require_once('./includes/SendMailEstimated.inc');
                }
                //echo 'entraaaaa dos';//
            } else {
                $to     =  $_SESSION['FactoryManagerEmail'];
                $status = "";
                
                if (isset($_POST['Commit'])) {
                    $status = "Procesada";
                } else if (isset($_POST['CancelaOrden'])) {
                    $status = "Cancelada";
                } else if (isset($_POST['AutorizaOrden'])) {
                    $status = "Autorizada";
                } else if (isset($_POST['RechazaOrden'])) {
                    $status = "Rechazada";
                } else if (isset($_POST['SurteOrden'])) {
                    $status = "Cotizada";
                }
                
                if (empty($status) == false && 1 == 2) {
                    require_once('./includes/mail.php');
                    
                    $userId     = $_SESSION['UserID'];
                    $userName   = "";
                    if ($status != 'Autorizada') {
                        $rsUser = DB_query("SELECT realname, email FROM www_users WHERE userid = '$userId'", $db);
                        if ($rowUser = DB_fetch_array($rsUser)) {
                            $userName = ucwords(strtolower($rowUser['realname']));
                            $userEmail = $rowUser['email'];
                            if (empty($userEmail) == false) {
                                $to .= "," . $userEmail;
                            }
                        }
                    }

                    if ($status == 'Cotizada' || $status == 'Autorizada' || $status == 'Rechazada') {
                        $pedidoventa = '';
                        $sql = "
                            SELECT requisitionno
                            FROM purchorders
                            WHERE purchorders.orderno = '" . $_SESSION['PO'.$identifier]->OrderNo . "'
                        ";
                        
                        $rsTmp = DB_query($sql, $db);
                        if ($rowTmp = DB_fetch_array($rsTmp)) {
                            $pedidoventa = $rowTmp['requisitionno'];
                        }
                        
                        $rsTmp = DB_query("
                            SELECT www_users.email
                            FROM purchorders
                            INNER JOIN salesorders
                            ON purchorders.requisitionno = salesorders.orderno
                            INNER JOIN salesman
                            ON salesman.salesmancode = salesorders.salesman
                            INNER JOIN www_users
                            ON salesman.usersales = www_users.userid
                            WHERE purchorders.orderno = '" . $_SESSION['PO'.$identifier]->OrderNo . "'
                        ", $db);
                        
                        if ($rowTmp = DB_fetch_array($rsTmp)) {
                            if (empty($rowTmp['email']) == false) {
                                $to .= ',' . $rowTmp['email'];
                            }
                        }
                        
                        // se envia al que inicio el proceso de venta
                        $sql = "SELECT www_users.email
                            FROM purchorders
                            INNER JOIN salesorders
                            ON purchorders.requisitionno = salesorders.orderno          
                            INNER JOIN www_users
                            ON salesorders.UserRegister = www_users.userid
                            WHERE purchorders.orderno = '" . $_SESSION['PO'.$identifier]->OrderNo . "'";
                        
                        $rsTmp = DB_query($sql, $db);
                        
                        if ($rowTmp = DB_fetch_array($rsTmp)) {
                            if (empty($rowTmp['email']) == false) {
                                $to .= ',' . $rowTmp['email'];
                            }
                        }
                            
                        // Envia al proveedor que tiene asignada la orden de compra cuando es autorizada
                        if ($status == 'Autorizada') {
                            $sql = "SELECT email FROM purchorders
                                INNER JOIN suppliers ON suppliers.supplierid = purchorders.supplierno
                                WHERE purchorders.orderno = '{$_SESSION['PO'.$identifier]->OrderNo}'";
                            
                            $rsTmp = DB_query($sql, $db);
                            if ($rowTmp = DB_fetch_array($rsTmp)) {
                                if (empty($rowTmp['email']) == false) {
                                    $to .= ',' . $rowTmp['email'];
                                }
                            }
                        }
                    }
                    
                    // ACM: Modificaci�n para eviar se envien correos desde la cuenta del usuario, se configura para que se envie desde una cuenta general
                    if ($_SESSION['SMTP_isTRUE']) {
                        $mmail=$_SESSION['SMTP_emailSENDER'];
                        //Se utiliza el correo electr�nico en config = SMTP_emailSENDER como DE:GRP@tecnoaplicada.com
                    } else {
                        $mmail="GRP@tecnoaplicada.com";
                    }
                    if (strlen($_SESSION['POReceiveEmail'])>0) {
                        $mail = new Mail();
                        $mail->setTo($to);
                        $mail->setFrom($mmail);
                        $mail->setSender("Administrador del Sistema");
                        $mail->setSubject(strtoupper($_SESSION['DatabaseName']) . " - Notificaci�n Orden Compra No. " . $_SESSION['PO'.$identifier]->OrderNo);
                        $mail->setHtml("
                                El usuario $userName ha realizado la siguiente operacion: 
                                <br /> El estado de la orden de compra No. " . $_SESSION['PO'.$identifier]->OrderNo . " ha cambiado a $status
                                <br>La orden de venta asociada es: " . $pedidoventa);
                        $mail->send();
                    }
                }
            }

            /* end of the loop round the detail line items on the order */
            // si existe el total con un porcentaje de dev por compra
            /*if($aplicadevtotal==true ){
                $aplicadevtotal=true;
                $sql ='select max(refundpercent) as maxdev FROM purchorderdetails  where orderno="'. $_SESSION['PO'.$identifier]->OrderNo .'"';
                $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                $myrowloc = DB_fetch_array($result);
                $maxdevxpurch = $myrowloc['maxdev'];
            
            }*/
            if (empty($_SESSION['PO'.$identifier]->PorcDevTot)) {
                $_SESSION['PO'.$identifier]->PorcDevTot = "0";
            }
            $SQL="UPDATE purchorders SET refundpercentpurch='".$_SESSION['PO'.$identifier]->PorcDevTot."' WHERE orderno='".$_SESSION['PO'.$identifier]->OrderNo."'";
            $ErrMsg = _('La Actualizacion de la orden no se realizo');
            $DbgMsg=_('El SQL utilizado es: ');
            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
            //  echo '<br>sql:'.$SQL;
            /*******************************************************************************************************/
            //envio email a proveedor con la compra...
                
            if (isset($_POST['AutorizaOrden'])) {
                $OrderNoCompra=$_SESSION['PO'.$identifier]->OrderNo;
                $OrderNo=$_SESSION['PO'.$identifier]->OrderNo;
                $sqllegal = "SELECT legalid
                         FROM tags
                         WHERE tagref = '".$tagref."'";
                $resultlegal = DB_query($sqllegal, $db);
                $myrowlegal = DB_fetch_array($resultlegal);
                
                $_GET['Tagref'] = $tagref;
                $_GET['legalid'] = $myrowlegal['legalid'];
                //$_GET['tipodocto'] = 25;//
                $_GET['tipodocto'] = 555;

                debug_sql("Linea: ", __LINE__, $ejecutar_debug);
                               
                //include('includes/SendEmailCompra.inc');
                
                $opercompra='Autorizar';
                //valida que el producto se encuentre en compras si es que ha sido cancelado
                include('includes/ProcessCancelRequisitionsCompra.inc');
                //inserta en log fecha de entrega de producto
                include('includes/ProcessLogDateRequisitionsCompra.inc');
            }
            
            // se agrega include para cancelar los productos que tenian esta requisicion
            if (isset($_POST['CancelaOrden'])) {
                //echo "entra";
                $OrderNoCompra=$_SESSION['PO'.$identifier]->OrderNo;
                $OrderNo=$_SESSION['PO'.$identifier]->OrderNo;
                $AlmacenCancel=$_SESSION['PO'.$identifier]->Location;
                $opercompra='Cancel';
                include('includes/ProcessCancelRequisitionsCompra.inc');
            }

            /*******************************************************************************************************/
            /*************Valido que productos no pertencen a ese proveedor con esa orden de compra*****************/
            /*******************************************************************************************************/
            $SQLProvcompra=" SELECT purchorderdetails.itemcode,purchorderdetails.quantityord,purchorderdetails.itemdescription
            FROM purchorderdetails 
            INNER JOIN purchdata ON purchorderdetails.itemcode=purchdata.stockid
            WHERE purchorderdetails.orderno=".$_SESSION['PO'.$identifier]->OrderNo."
            AND purchdata.preferred=1
            AND  purchdata.supplierno<>'". $_SESSION['PO'.$identifier]->SupplierID ."'";
            $ErrMsg =_('No se obtuvieron los detalles de la compra de proveedor por que');
            $DbgMsg =_('El SQL utilizado es');
            //echo $SQLProvcompra.'<br>';
            $result =DB_query($SQLProvcompra, $db, $ErrMsg, $DbgMsg);
            $textomail="";
            $textomail="El usuario ".$_SESSION['UserID']." ha generado la orden de compra :" .$_SESSION['PO'.$identifier]->OrderNo. " con los siguientes productos que no se encuentran asignados al proveedor preferente:";
            if (DB_num_rows($result)>0) {
                while ($myroworden=DB_fetch_array($result)) {
                    $textomail=$textomail." <br>Producto: " .$myroworden['itemcode'].' - '.$myroworden['itemdescription']." cantidad ordenada:".$myroworden['quantityord'].'<br>';
                }
            }
            // echo $textomail;
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
            //direcci�n del remitente
            $headers .= "From: Soporte Tecnoaplicada <soporte@tecnoaplicada.com>\r\n";
            //direcci�n de respuesta, si queremos que sea distinta que la del remitente
            //$headers .= "Reply-To: mariano@desarrolloweb.com\r\n";
            //ruta del mensaje desde origen a destino
            $headers .= "Return-path: juan.mendoza@tecnoaplicada.com\r\n";
            //direcciones que recibi�n copia
            //$headers .= "Cc: game167@gmail.com\r\n";
            //direcciones que recibir�n copia oculta
            //$headers .= "Bcc: pepe@pepe.com,juan@juan.com\r\n";
            mail($_SESSION['FactoryManagerEmail'], 'Orden de compra con diferente proveedor al preferido', $textomail, $headers) ;

            echo '<p>';
             /*Insert the purchase order detail records */
            prnMsg(_('La Orden de Compra No.') . ' ' . $_SESSION['PO'.$identifier]->OrderNo2 . ' ' . _('para') . ' ' .
                $_SESSION['PO'.$identifier]->SupplierName . ' ' . _('ha sido registrada.'), 'success');
            
            $sqllegal = "SELECT legalid FROM tags WHERE tagref = '".$tagref."'";
            $resultlegal = DB_query($sqllegal, $db);
            $myrowlegal = DB_fetch_array($resultlegal);

            if (isset($_POST['Commit'])) {
            } elseif (isset($_POST['PendingOrder'])) {
            } elseif (isset($_POST['CancelaOrden'])) {
            } elseif (isset($_POST['AutorizaOrden'])) {
                if ($_POST['recibOCProd'] == 2) {
                    if (receivePurchOrder($_SESSION['PO'.$identifier]->OrderNo, $db)) {
                        prnMsg("Se realizo la recepcion de la orden compra", "success");
                    } else {
                        prnMsg("No se realizo la recepcion de la orden compra", "error");
                    }
                } else {
                    $totalcompra = 0;
                                    
                    foreach ($_SESSION['PO'.$identifier]->LineItems as $OrderLine) {
                        if ($OrderLine->Quantity != 0 and $OrderLine->Quantity != '' and isset($OrderLine->Quantity)) {
                            if ($_SESSION['PO']->ExRate == '') {
                                $_SESSION['PO']->ExRate = 1;
                            }

                            //buscar margen automatico para costo en categoria de inventario
                            $qry = "SELECT margenautcost, 
                                    taxauthrates.taxrate
                                    FROM stockcategory
                                    INNER JOIN stockmaster ON stockcategory.categoryid = stockmaster.categoryid
                                    INNER JOIN taxauthrates ON stockmaster.taxcatid = taxauthrates.taxcatid
                                    WHERE stockmaster.stockid = '" . $OrderLine->StockID . "'";
                            $rsm = DB_query($qry, $db);
                            $rowm = DB_fetch_array($rsm);
                            $margenautcost = $rowm['margenautcost'] / 100;
                            $porcentaje_impuesto= 1 + $rowm['taxrate'];
                            $OrderLine->Price += ($OrderLine->Price * $margenautcost);
                            $LocalCurrencyPrice = ($OrderLine->Price / $_SESSION['PO']->ExRate);
                            if ($OrderLine->StockID != '') {
                                $avgcost = 0;
                                $avgcost = $LocalCurrencyPrice;
                                $avgcost = $avgcost - ($avgcost * ($OrderLine->Desc1 / 100));
                                $avgcost = $avgcost - ($avgcost * ($OrderLine->Desc2 / 100));
                                $avgcost = $avgcost - ($avgcost * ($OrderLine->Desc3 / 100));
                            }
                                            
                            $purchdatasql = 'SELECT conversionfactor,price
                                            FROM purchdata
                                            WHERE purchdata.supplierno = "' . $_SESSION['PO']->SupplierID . '"
                                            AND purchdata.stockid="' . $OrderLine->StockID . '"';
                        
                            $rsm = DB_query($purchdatasql, $db);
                            $rowm = DB_fetch_array($rsm);

                            $factordeConversion = 1;
                            if (is_numeric($rowm['conversionfactor'])) {
                                $factordeConversion = $rowm['conversionfactor'];
                            }
                                            
                            $CurrentStandardCost = $avgcost;
                            $CurrentStandardCost = $CurrentStandardCost / $factordeConversion;
                            $totalcompra+= ($CurrentStandardCost * $OrderLine->Quantity);
                        }
                        $totalcompra= truncateFloat($totalcompra, $digitos);
                    }
                    /* $GRN = GetNextTransNo(25, $db);
                    $PeriodNo = GetPeriod(date('d/m/Y'), $db, $tagref);*/

                    /* $resultado= GeneraMovimientoContablePresupuesto(25, "POREJERCER", "COMPROMETIDO", $GRN, $PeriodNo,
                    $totalcompra, $tagref, $_SESSION['PO'.$identifier]->Orig_OrderDate, $db);*/
                }
                
                /*
                echo '<p class="page_title_text">';
                echo '<a target="_blank"  href="PO_PDFPurchOrder.php?' . SID . 'OrderNo=' . $_SESSION['PO'.$identifier]->OrderNo . '&identifier='.$identifier.'&tipodocto=25&Tagref='.$tagref.'&legalid='.$myrowlegal['legalid'].'">';
                echo '<img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Print') . '" alt=""><b>' . ' ' . _('IMPRIMIR ORDEN DE COMPRA NO.') . $_SESSION['PO'.$identifier]->OrderNo . '</a><BR>';
                
                $sqlval = "SELECT if(purchorders.supplierorderno is null,0,2) as prov
                            FROM purchorders
                            WHERE orderno = '" . $_SESSION['PO'.$identifier]->OrderNo ."'";
                $resultval = DB_query($sqlval, $db);
                $rowval = DB_fetch_array($resultval);
                if($rowval['prov'] == 0){
                    echo '<p class="page_title_text">';
                    echo '<a target="_blank"  href="SendPOPDFToSupplier.php?' . SID . 'OrderNo=' . $_SESSION['PO'.$identifier]->OrderNo .'&tipodocto=25&Tagref='.$tagref.'&legalid='.$myrowlegal['legalid'].'">';
                    echo '<img src="'.$rootpath.'/css/'.$theme.'/images/email.gif" title="' . _('Enviar pdf por email a proveedor') . '" alt=""><b>' . ' ' . _('Enviar PDF a proveedor') . '</a><BR>';
                    
                }
                
                
                echo '<p class="page_title_text">';
                echo '<a href="PO_Header.php?' . SID . '&ModifyOrderNumber=' . $_SESSION['PO'.$identifier]->OrderNo . '"><b>'. _('MODIFICAR ORDEN DE COMPRA NO.') . $_SESSION['PO'.$identifier]->OrderNo .'</b></a>';
    
                echo '<p>';
                echo '<div align="center"><a target="_blank" href="GoodsReceived.php?' . SID .'&PONumber='.$_SESSION['PO'.$identifier]->OrderNo.'&TieToOrderNumber='.$TieToOrderNumber.'"><b>IR A RECIBIR PRODUCTOS</b></a></div>';
                echo '<p>';*/
            } elseif (isset($_POST['RechazaOrden'])) {
                //              echo '<p class="page_title_text">';
                //              echo '<a href="PO_Header.php?' . SID . '&ModifyOrderNumber=' . $_SESSION['PO'.$identifier]->OrderNo . '"><b>'. _('MODIFICAR ORDEN DE COMPRA NO.') . $_SESSION['PO'.$identifier]->OrderNo .'</b></a>';
            } elseif (isset($_POST['SurteOrden'])) {
                //              echo '<p class="page_title_text">';
                //              echo '<a href="PO_Header.php?' . SID . '&ModifyOrderNumber=' . $_SESSION['PO'.$identifier]->OrderNo . '"><b>'. _('MODIFICAR ORDEN DE COMPRA NO.') . $_SESSION['PO'.$identifier]->OrderNo .'</b></a>';
            }
            /* SI ORDEN DE COMPRA YA EXISTE ENTONCES PROCESA LAS ACTUALIZACIONES Y CAMBIOS */
        } else {
            $_SESSION['PO'.$identifier]->version += 0.01;

            // generar folio de orden de compra
            if (empty($_SESSION['PO'.$identifier]->OrderNo2)) {
                $_SESSION['PO'.$identifier]->OrderNo2= GetNextTransNo(18, $db);
            }
    
            $date = date($_SESSION['DefaultDateFormat']);
            $StatusComment = $date.' - Modificada: '.$_SESSION['UserID'].' - '.$_SESSION['PO'.$identifier]->StatusMessage.'<br>';

            $sql ='select tagref FROM locations l where loccode="'.$_SESSION['PO'.$identifier]->Location.'"';
            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
            $myrowloc = DB_fetch_array($result);
            $tagref = $myrowloc['tagref'];
            
            $sqllegal = "SELECT legalid
                         FROM tags
                         WHERE tagref = '".$tagref."'";

            $resultlegal = DB_query($sqllegal, $db);
            $myrowlegal = DB_fetch_array($resultlegal);
            
            $_GET['Tagref'] = $tagref;
            $_GET['legalid'] = $myrowlegal['legalid'];
            $_GET['tipodocto'] = 555;
                        
            if (isset($TieToOrderNumber)) {
                $_SESSION['PO'.$identifier]->RequisitionNo = $TieToOrderNumber;
            } else {
                //echo 'Sin Liga a Orden de Venta';
            }

            //deliverydate='" . FormatDateForSQL($_SESSION['PO'.$identifier]->deliverydate) . "',
            if (empty($_SESSION['PO'.$identifier]->Typeorder)) {
                $_SESSION['PO'.$identifier]->Typeorder= "0";
            }

            if (empty($_SESSION['PO'.$identifier]->ServiceType)) {
                $_SESSION['PO'.$identifier]->ServiceType= "0";
            }

            if (empty($_SESSION['PO'.$identifier]->PorcDevTot)) {
                $_SESSION['PO'.$identifier]->PorcDevTot= "0";
            }
            
            $sql = "UPDATE purchorders SET
                    supplierno = '" . $_SESSION['PO'.$identifier]->SupplierID . "' ,
                    comments='" . $_SESSION['PO'.$identifier]->Comments . "',
                    rate='" . number_format($_SESSION['PO'.$identifier]->ExRate, $decimalesTipoCambio) . "',
                    currcode = '" . $_SESSION['PO'.$identifier]->CurrCode . "',     
                    realorderno= '" . $_SESSION['PO'.$identifier]->OrderNo2 . "',
                    version= '" .  $_SESSION['PO'.$identifier]->version . "',
                    deliveryby='" . $_SESSION['PO'.$identifier]->deliveryby . "',                    
                    revised= '" . FormatDateForSQL($date) . "',
                    intostocklocation='" . $_SESSION['PO'.$identifier]->Location . "',
                    noag_ad='" . $_POST['noag_ad'] . "',
                    deladd1='" . $_SESSION['PO'.$identifier]->DelAdd1 . "',
                    deladd2='" . $_SESSION['PO'.$identifier]->DelAdd2 . "',
                    deladd3='" . $_SESSION['PO'.$identifier]->DelAdd3 . "',
                    deladd4='" . $_SESSION['PO'.$identifier]->DelAdd4 . "',
                    deladd5='" . $_SESSION['PO'.$identifier]->DelAdd5 . "',
                    deladd6='" . $_SESSION['PO'.$identifier]->DelAdd6 . "',
                    allowprint='" . $_SESSION['PO'.$identifier]->AllowPrintPO . "',
                    deliverydate = '" . $_SESSION['PO'.$identifier]->Orig_OrderDate . "',
                    stat_comment= CONCAT(stat_comment,' ','" . $StatusComment . "'),
                    tagref='" . $tagref."',
                    systypeorder = '".$_SESSION['PO'.$identifier]->Typeorder."',
                    servicetype = '".$_SESSION['PO'.$identifier]->ServiceType."',
                    contact = '".$_SESSION['PO'.$identifier]->contact."',
                    telephoneContact = '".$_SESSION['PO'.$identifier]->telephoneContact."',
                    clavepresupuestal = '".$_SESSION['PO'.$identifier]->ClavePresupuestal."'
                ";

            if (isset($_POST['btnAvanzar'])) {
                if ($_SESSION['PO'.$identifier]->Stat== 'Autorizado') {
                    $sql.= ",status='Pending'";
                } else {
                    $sql.= ", status='".traeCambioEstatus($_SESSION['PO'.$identifier]->Stat, 1, 1371, $db)."'";
                }
            } elseif (isset($_POST['PendingOrder'])) {
                $sql.=",status='Autorizado'"; // Estatus de Requsicion Autorizada
            } elseif (isset($_POST['CancelaOrden'])) {
                $sql.=",status='Cancelled'";
            } elseif (isset($_POST['AutorizaOrden'])) {
                $sql.=",status='Authorised', autorizausuario='{$_SESSION['UserID']}', autorizafecha=NOW()";
            } elseif (isset($_POST['RechazaOrden'])) {
                $sql.= ", status='".traeCambioEstatus($_SESSION['PO'.$identifier]->Stat, 0, 1371, $db)."'";
            } elseif (isset($_POST['SurteOrden'])) {
                $sql.=",status='Delivered'";
            } elseif (isset($_POST['PorAutorizar'])) {
                $sql.=",status='InAuthProc'";
            }
            
            $sql.=" WHERE orderno = '" . $_SESSION['PO'.$identifier]->OrderNo ."'";

            $ErrMsg =  _('La orden de compra no se pudo actualizar porque');
            $DbgMsg = _('La instruccion SQL utilizada para actualizar la orden de compra en cabecera, FALLO');
            $result =DB_query($sql, $db, $ErrMsg, $DbgMsg, true);

            $SQL="UPDATE purchorders
            SET refundpercentpurch='".$_SESSION['PO'.$identifier]->PorcDevTot."'
            WHERE orderno='".$_SESSION['PO'.$identifier]->OrderNo."'";

            $ErrMsg = _('La Actualizacion de la orden no se realizo');
            $DbgMsg=_('El SQL utilizado es: ');
            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
            //se asigna un contador ala clase para saber cuantos productos tiene esa orden
            
            foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
                #$sql='UPDATE purchorders SET status="'._('Pending').'" WHERE orderno=' . $_SESSION['PO'.$identifier]->OrderNo;
                #$result=DB_query($sql,$db);
                $contador=count($_SESSION['PO'.$identifier]->LineItems);
                //echo '<br>po:'.$POLine->PODetailRec.'Delete:'.$POLine->Deleted.'stockid:'. $POLine->StockID;
                if ($POLine->Deleted=='') {
                    $POLine->Deleted=false;
                }

                if (empty($POLine->Desc1)) {
                    $POLine->Desc1= "0";
                }
                
                if (empty($POLine->Desc2)) {
                    $POLine->Desc2= "0";
                }

                if (empty($POLine->Desc3)) {
                    $POLine->Desc3= "0";
                }

                if (empty($POLine->Devolucion)) {
                    $POLine->Devolucion= "0";
                }

                if (empty($POLine->estimated_cost)) {
                    $POLine->estimated_cost= "0";
                }

                if ($POLine->Deleted==true) {
                    if ($POLine->PODetailRec!='') {
                        $sql = "SELECT count(*) as cuenta
                                FROM purchorderdetails
                                WHERE orderno= ' ". $_SESSION['PO'.$identifier]->OrderNo ."'";

                        $ErrMsg =  _('La orden de compra no se pudo eliminar porque');
                        $DbgMsg = _('La instrucci�n SQL utilizada para eliminar la orden de compra en cabecera, FALLO');
                        $result =DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
                        //SI EL RESULTADO DE LA CONSULTA ES IGUAL A 1, ASIGNA EL VALOR  A UN ARREGLO Y ESTE A SU VEZ A LA VARIABLE $validar
                        if (DB_num_rows($result)==1) {
                            $myrow=DB_fetch_array($result);
                            $validar = $myrow['cuenta'];
                            //SINO LA VARIABLE ES IGUAL A 0
                            // validar que la partida no tenga cantidad facturada para poder eliminar
                            $sql = "SELECT sum(qtyinvoiced)  as cuenta,sum(quantityrecd) as cuentarec
                                    FROM purchorderdetails
                                    WHERE podetailitem= ' ".  $POLine->PODetailRec ."'";
                            $ErrMsg =  _('La orden de compra no se pudo eliminar porque');
                            $DbgMsg = _('La instrucci�n SQL utilizada para eliminar la orden de compra en cabecera, FALLO');
                            $resultitem =DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
                            if (DB_num_rows($resultitem)>0) {
                                $myrowx=DB_fetch_array($resultitem);
                                $validafacturado = $myrowx['cuenta'];
                                $validarecibido= $myrowx['cuentarec'];
                                if ($validafacturado>0) {
                                    $validar=0;
                                    prnMsg(_('La Actualizacion NO puede ser Procesada') .'&nbsp&nbsp&nbsp' . _('La partida de la Orden de compra tiene productos facturados'), 'error');
                                } elseif ($validarecibido>0) {
                                    $validar=0;
                                    prnMsg(_('La Actualizacion NO puede ser Procesada') .'&nbsp&nbsp&nbsp' . _('La partida de la Orden de compra tiene productos recibidos'), 'error');
                                }
                            }
                        } else {
                            $validar =0;
                        }
                        
                        //el producto se podra  borrar siempre y cuando en la orden existan 2 o mas productos, esto se hace con la condicion de las variables contador y valida
                        if ($validar>1) {
                            $sql="DELETE FROM purchorderdetails WHERE podetailitem='" . $POLine->PODetailRec . "'";
                            $ErrMsg =  _('La orden de compra no se pudo eliminar porque');
                            $DbgMsg = _('La instrucci�n SQL utilizada para eliminar la orden de compra en cabecera, FALLO');
                            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                        } else {
                            $GoodProcess=false;
                            prnMsg(_('La Actualizacion NO puede ser Procesada') .'&nbsp&nbsp&nbsp' . _('La Orden de compra debe tener al menos UN Producto a Ordenar'), 'error');
                            echo '<p class="page_title_text">';
                            echo '<br><a href='.$rootpath.'/PO_Header.php?&ModifyOrderNumber='.$_SESSION['PO'.$identifier]->OrderNo.'>' . _(' Ir a Agregar Productos') . '</a>';
                            echo "<br><br><br><br><div style='text-align:center; margin: 0 auto;'><a href='".$rootpath."/PO_SelectOSPurchOrder.php?" . SID . "&ActivarBoton=1'>" . _(' B&uacute;squeda de Ordenes de Compra') . '</a></div>';

                            echo '<p>';
                            //$sql = 'COMMIT';
                            //$result = DB_query($sql,$db);
                            $result = DB_Txn_Commit($db);
                            include('includes/footer_Index.inc');
                            exit;
                        }
                    }
                } elseif ($POLine->PODetailRec=='') {
                //  echo '<pentraaaaaaaaa';
                    $sql = "INSERT INTO purchorderdetails (
                                    orderno,
                                    itemcode,
                                    deliverydate,
                                    itemdescription,
                                    glcode,
                                    unitprice,
                                    quantityord,
                                    shiptref,
                                    jobref,
                                    itemno,
                                    uom,
                                    suppliers_partno,
                                    subtotal_amount,
                                    package,
                                    pcunit,
                                    nw,
                                    gw,
                                    cuft,
                                    total_quantity,
                                    total_amount,
                                    discountpercent1,
                                    discountpercent2,
                                    discountpercent3,
                                    narrative,
                                    justification,
                                    refundpercent,
                                    estimated_cost,
                                    customs,
                                    pedimento,
                                    inputport,
                                    dateship,
                                    datecustoms,
                                    clavepresupuestal
                                    )
                                VALUES (
                                    '". $_SESSION['PO'.$identifier]->OrderNo . "',
                                    '" . $POLine->StockID . "',
                                    '" . FormatDateForSQL($POLine->ReqDelDate) . "',
                                    '" . DB_escape_string(htmlspecialchars_decode(str_replace("'", "", $POLine->ItemDescription), ENT_NOQUOTES)) . "',
                                    '" . $POLine->GLCode . "',
                                    '" . $POLine->Price . "',
                                    '" . $POLine->Quantity . "',
                                    '" . $POLine->ShiptRef . "',
                                    '" . $POLine->JobRef . "',
                                    '" . $POLine->itemno . "',
                                    '" . $POLine->uom . "',
                                    '" . $POLine->suppliers_partno . "',
                                    '" . $POLine->subtotal_amount . "',
                                    '" . $POLine->package . "',
                                    '" . $POLine->pcunit . "',
                                    '" . $POLine->nw . "',
                                    '" . $POLine->gw . "',
                                    '" . $POLine->cuft . "',
                                    '" . $POLine->total_quantity . "',
                                    '" . $POLine->total_amount . "',
                                    '" . $POLine->Desc1 . "',
                                    '" . $POLine->Desc2 . "',
                                    '" . $POLine->Desc3 . "',
                                    '" . DB_escape_string(htmlspecialchars_decode($POLine->Narrative, ENT_NOQUOTES)) . "',
                                    '" . DB_escape_string(htmlspecialchars_decode($POLine->Justification, ENT_NOQUOTES)). "',
                                    '" . $POLine->Devolucion . "',
                                    '" . $POLine->estimated_cost . "',
                                    '". $_POST['aduana']."',
                                    '". $_POST['pedimento']."',
                                    '". $_POST['inputport']."',
                                    '". $fechaembarque."',
                                    '". $fechaaduana."',
                                    '". $_POST['clavepresupuestal_'.$POLine->LineNo]."'
                                )";
                    
                    $ErrMsg = _('Uno de los registros de la �rden de compra no se pudo actualizar porque');
                    $DbgMsg = _('La instrucci�n SQL utilizada para actualizar el registro de la orden de compra FALLO');
                    $result =DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
                    $POLine->PODetailRec= DB_Last_Insert_ID($db, 'purchorderdetails', 'podetailitem');
                } else {
                    //echo "<br>fecha:".$POLine->ReqDelDate;
                    if ($POLine->Quantity==$POLine->QtyReceived) {
                        $sql = "UPDATE purchorderdetails SET
                                itemcode='" . $POLine->StockID . "',
                                deliverydate ='" . FormatDateForSQL($POLine->ReqDelDate) . "',
                                itemdescription='" . DB_escape_string(htmlspecialchars_decode(str_replace("'", "", $POLine->ItemDescription), ENT_NOQUOTES)) . "',
                                glcode='" . $POLine->GLCode . "',
                                unitprice='" . $POLine->Price . "',
                                quantityord='" . $POLine->Quantity . "',
                                shiptref='" . $POLine->ShiptRef . "',
                                jobref='" . $POLine->JobRef . "',
                                itemno='" . $POLine->itemno . "',
                                uom='" . $POLine->uom . "',
                                suppliers_partno='" .$POLine->suppliers_partno . "',
                                subtotal_amount='" . $POLine->subtotal_amount . "',
                                package='" . $POLine->package . "',
                                pcunit='" . $POLine->pcunit . "',
                                nw='" . $POLine->nw . "',
                                gw='" . $POLine->gw . "',
                                cuft='" . $POLine->cuft . "',
                                total_quantity='" . $POLine->total_quantity . "',
                                total_amount='" . $POLine->total_amount . "',
                                discountpercent1='" .$POLine->Desc1. "',
                                discountpercent2='" .$POLine->Desc2 . "',
                                discountpercent3='" .$POLine->Desc3 . "',
                                narrative='" . DB_escape_string(htmlspecialchars_decode($POLine->Narrative, ENT_NOQUOTES)) . "',
                                justification='" . DB_escape_string(htmlspecialchars_decode($POLine->Justification, ENT_NOQUOTES)) . "',
                                refundpercent='" .$POLine->Devolucion . "',
                                estimated_cost='" .$POLine->estimated_cost . "',
                                customs = '".$_POST['aduana']."',
                                pedimento = '".$_POST['pedimento']."',
                                dateship = '".$fechaembarque."',    
                                datecustoms = '".$fechaaduana."',   
                                inputport = '".$_POST['inputport']."',
                                completed=1,
                                clavepresupuestal = '". $_POST['clavepresupuestal_'.$POLine->LineNo]."'
                            WHERE podetailitem='" . $POLine->PODetailRec."'";
                    } else {
                        $sql = "UPDATE purchorderdetails SET
                                itemcode='" . $POLine->StockID . "',
                                deliverydate ='" . FormatDateForSQL($POLine->ReqDelDate) . "',
                                itemdescription='" . DB_escape_string(htmlspecialchars_decode(str_replace("'", "", $POLine->ItemDescription), ENT_NOQUOTES)) . "',
                                glcode='" . $POLine->GLCode . "',
                                unitprice='" . $POLine->Price . "',
                                quantityord='" . $POLine->Quantity . "',
                                shiptref='" . $POLine->ShiptRef . "',
                                jobref='" . $POLine->JobRef . "',
                                itemno='" . $POLine->itemno . "',
                                uom='" . $POLine->uom . "',
                                suppliers_partno='" . $POLine->suppliers_partno . "',
                                subtotal_amount='" . $POLine->subtotal_amount . "',
                                package='" . $POLine->package . "',
                                pcunit='" . $POLine->pcunit . "',
                                nw='" . $POLine->nw . "',
                                gw='" . $POLine->gw . "',
                                cuft='" . $POLine->cuft . "',
                                total_quantity='" . $POLine->total_quantity . "',
                                total_amount='" . $POLine->total_amount . "',
                                discountpercent1='" .$POLine->Desc1. "',
                                discountpercent2='" .$POLine->Desc2 . "',
                                discountpercent3='" .$POLine->Desc3 . "',
                                narrative='" . DB_escape_string(htmlspecialchars_decode($POLine->Narrative, ENT_NOQUOTES)) . "',
                                justification='" . $POLine->Justification . "',
                                refundpercent='" .$POLine->Devolucion . "',
                                estimated_cost='" .$POLine->estimated_cost . "',
                                customs = '".$_POST['aduana']."',
                                pedimento = '".$_POST['pedimento']."',
                                inputport = '".$_POST['inputport']."',  
                                datecustoms = '".$fechaaduana."',           
                                dateship = '".$fechaembarque."',
                                clavepresupuestal = '". $_POST['clavepresupuestal_'.$POLine->LineNo]."'
                                WHERE podetailitem='" . $POLine->PODetailRec."'";
                    }
                    
                    //  echo '<pre><br>sql:'.$sql;
                    $ErrMsg = _('Uno de los registros de la �rden de compra no se pudo actualizar porque');
                    $DbgMsg = _('La instruccin SQL utilizada para actualizar el registro de la orden de compra FALLO');
                    $result =DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
                                        
                    if ($_SESSION['ExistingPurchOrder'] != 0) {
                        if (isset($_POST['AutorizaOrden'])) {
                            if ($POLine->Quantity != 0 and $POLine->Quantity != '' and isset($POLine->Quantity)) {
                                if ($_SESSION['PO']->ExRate == '') {
                                    $_SESSION['PO']->ExRate = 1;
                                }

                                //buscar margen automatico para costo en categoria de inventario
                                $qry = "SELECT margenautcost, 
                                               taxauthrates.taxrate
                                        FROM stockcategory
                                        INNER JOIN stockmaster ON stockcategory.categoryid = stockmaster.categoryid
                                        INNER JOIN taxauthrates ON stockmaster.taxcatid = taxauthrates.taxcatid
                                        WHERE stockmaster.stockid = '" . $POLine->StockID . "'";
                                $rsm = DB_query($qry, $db);
                                $rowm = DB_fetch_array($rsm);

                                $margenautcost = $rowm['margenautcost'] / 100;
                                $porcentaje_impuesto= 1 + $rowm['taxrate'];
                                $POLine->Price += ($POLine->Price * $margenautcost);
                                $LocalCurrencyPrice = ($POLine->Price / $_SESSION['PO']->ExRate);

                                if ($POLine->StockID != '') {
                                    $avgcost = 0;
                                    $avgcost = $LocalCurrencyPrice;
                                    $avgcost = $avgcost - ($avgcost * ($POLine->Desc1 / 100));
                                    $avgcost = $avgcost - ($avgcost * ($POLine->Desc2 / 100));
                                    $avgcost = $avgcost - ($avgcost * ($POLine->Desc3 / 100));
                                }
                                            
                                $purchdatasql = 'SELECT conversionfactor,price
                                                FROM purchdata
                                                WHERE purchdata.supplierno = "' . $_SESSION['PO']->SupplierID . '"
                                                AND purchdata.stockid="' . $POLine->StockID . '"';

                                $rsm = DB_query($purchdatasql, $db);
                                $rowm = DB_fetch_array($rsm);

                                $factordeConversion = 1;
                                if (is_numeric($rowm['conversionfactor'])) {
                                    $factordeConversion = $rowm['conversionfactor'];
                                }
                                            
                                $CurrentStandardCost = $avgcost;
                                $CurrentStandardCost = $CurrentStandardCost / $factordeConversion;
                                //$totalcompra= ($CurrentStandardCost * $POLine->Quantity) * $porcentaje_impuesto;
                                $totalcompra= ($CurrentStandardCost * $POLine->Quantity);
                            }

                            $totalcompra= truncateFloat($totalcompra, $digitos);
                            $fecha_creada= date_create($_SESSION['PO'.$identifier]->Orig_OrderDate);
                            $fecha_formateada= date_format($fecha_creada, "d/m/Y");

                            $fecha = explode('-', $_SESSION['PO'.$identifier]->Orig_OrderDate);
                            $fechaperiod = $fecha[2].'/'.$fecha[1].'/'.$fecha[0];

                            $infoClaves = array();
                            $infoClaves[] = array(
                                'accountcode' => $_POST['clavepresupuestal_'.$POLine->LineNo]
                            );
                            $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
                            if (!$respuesta['result']) {
                                $data['msg'] .= $respuesta['mensaje'];
                                    $flag++;
                                    continue;
                            }
                            /////$PeriodNo = GetPeriod($fecha_formateada, $db, 0);
                            $PeriodNo = $respuesta['periodo'];
                            $fechapoliza = $respuesta['fecha'];

                            //  $resultado= GeneraMovimientoContablePresupuesto(25, "POREJERCER", "COMPROMETIDO", $GRN, $PeriodNo,
                            //                                                $totalcompra, $tagref, $_SESSION['PO'.$identifier]->Orig_OrderDate,$_POST['clavepresupuestal_'.$POLine->LineNo], $_SESSION['PO'.$identifier]->OrderNo,$db);
                            
                            $resultado= GeneraMovimientoContablePresupuesto(
                                555,
                                "POREJERCER",
                                "COMPROMETIDO",
                                $GRN,
                                $PeriodNo,
                                $totalcompra,
                                $tagref,
                                $fechapoliza,
                                $_POST['clavepresupuestal_'.$POLine->LineNo],
                                $_SESSION['PO'.$identifier]->OrderNo,
                                $db,
                                false,
                                '',
                                '',
                                $_SESSION['PO'.$identifier]->Comments,
                                $_SESSION['PO'.$identifier]->unidadEjecutora,
                                1,
                                0,
                                $folioPolizaUe
                            );

                            // Actualizar enlace poliza
                            $sql = "UPDATE gltrans SET purchno = '".$_SESSION['PO'.$identifier]->OrderNo."' 
                            WHERE type = '555' AND typeno = '".$GRN."'";
                            $result =DB_query($sql, $db);
                            
                            //echo "<br>GRN: ".$GRN;
                            // Log Presupuesto
                            $descriptionLog = "Autorización Orden de Compra ".$_SESSION['PO'.$identifier]->OrderNo2.". Requisición ".$_SESSION['PO'.$identifier]->RequisitionNo;
                            // Se resta a la suficiencia el total
                            $agregoLog = fnInsertPresupuestoLog($db, $_SESSION['PO'.$identifier]->suficienciaType, $_SESSION['PO'.$identifier]->suficienciaTransno, $tagref, $_POST['clavepresupuestal_'.$POLine->LineNo], $PeriodNo, $totalcompra, 263, "", $descriptionLog, 0, '', 0, $_SESSION['PO'.$identifier]->unidadEjecutora); // Suficiencia Automatica
                            $agregoLog = fnInsertPresupuestoLog($db, 555, $GRN, $tagref, $_POST['clavepresupuestal_'.$POLine->LineNo], $PeriodNo, $totalcompra, 258, "", $descriptionLog, 1, '', 0, $_SESSION['PO'.$identifier]->unidadEjecutora); // Abono
                            $agregoLog = fnInsertPresupuestoLog($db, 555, $GRN, $tagref, $_POST['clavepresupuestal_'.$POLine->LineNo], $PeriodNo, $totalcompra * -1, 259, "", $descriptionLog, 1, '', 0, $_SESSION['PO'.$identifier]->unidadEjecutora); // Cargo
                            //echo "<br>realizo movimientos 1842";
                            
                            // Obtener periodos para comenzar la separacion de montos
                            $SQL = "SELECT periods.periodno, cat_Months.mes FROM chartdetailsbudgetbytag
                            JOIN periods ON YEAR(periods.lastdate_in_period) = chartdetailsbudgetbytag.anho
                            JOIN cat_Months ON u_mes = MONTH(periods.lastdate_in_period)
                            WHERE chartdetailsbudgetbytag.accountcode = '".$_POST['clavepresupuestal_'.$POLine->LineNo]."'
                            AND periods.periodno <= '".GetPeriod(date('d/m/y'), $db)."'
                            ORDER BY periods.periodno DESC";
                            $ErrMsg = "No se obtuvieron los periodos de la clave ";
                            $TransResult = DB_query($SQL, $db, $ErrMsg);
                            $cantidadVal = abs($totalcompra);
                            $movimientoTipo = 'Suficiencia';
                            $TransResult = ""; // No reccorrer ciclo, esta pendiente el acomulado
                            while ($myrow = DB_fetch_array($TransResult)) {
                                // Validar y generar registros en log
                                $disponible = fnInfoPresupuesto($db, $_POST['clavepresupuestal_'.$POLine->LineNo], $myrow['periodno'], '', '', 0, 0, '', $_SESSION['PO'.$identifier]->suficienciaType, $_SESSION['PO'.$identifier]->suficienciaTransno, 'Reduccion', '', '', '', 1, '', '', 0, 0);
                                
                                foreach ($disponible as $dispo) {
                                    if (abs($cantidadVal) == 0) {
                                        // Terminar operaciones
                                        break;
                                    }
                                    
                                    if ($dispo[$myrow['mes'].$movimientoTipo] > abs(0)) {
                                        // Si tiene disponible registrar en el log
                                        $cantidadRegistro = 0;
                                        if ($dispo[$myrow['mes'].$movimientoTipo] >= abs($cantidadVal)) {
                                            // Registrar cantidad y ya que se tiene mas disponible
                                            $cantidadRegistro = abs($cantidadVal);
                                        } else if (abs($cantidadVal) >= $dispo[$myrow['mes'].$movimientoTipo]) {
                                            // Registrar disponible ya que es mayor la cantidad
                                            $cantidadRegistro = abs($dispo[$myrow['mes'].$movimientoTipo]);
                                        }

                                        $cantidadVal = abs($cantidadVal) - abs($cantidadRegistro);

                                        // Log Presupuesto
                                        $descriptionLog = "Autorización Orden de Compra ".$_SESSION['PO'.$identifier]->OrderNo2.". Requisición ".$_SESSION['PO'.$identifier]->RequisitionNo;
                                        // Se resta a la suficiencia el total
                                        $agregoLog = fnInsertPresupuestoLog($db, $_SESSION['PO'.$identifier]->suficienciaType, $_SESSION['PO'.$identifier]->suficienciaTransno, $tagref, $_POST['clavepresupuestal_'.$POLine->LineNo], $myrow['periodno'], $cantidadRegistro, 263, "", $descriptionLog, 0, '', 0, $_SESSION['PO'.$identifier]->unidadEjecutora); // Suficiencia Automatica
                                        $agregoLog = fnInsertPresupuestoLog($db, 555, $GRN, $tagref, $_POST['clavepresupuestal_'.$POLine->LineNo], $myrow['periodno'], $cantidadRegistro, 258, "", $descriptionLog, 1, '', 0, $_SESSION['PO'.$identifier]->unidadEjecutora); // Abono
                                        $agregoLog = fnInsertPresupuestoLog($db, 555, $GRN, $tagref, $_POST['clavepresupuestal_'.$POLine->LineNo], $myrow['periodno'], $cantidadRegistro * -1, 259, "", $descriptionLog, 1, '', 0, $_SESSION['PO'.$identifier]->unidadEjecutora); // Cargo

                                        if (!$agregoLog) {
                                            $respuesta = false;
                                        }
                                    }
                                }

                                if (abs($cantidadVal) == 0) {
                                    // Terminar operaciones
                                    break;
                                }
                            }
                            
                            // Estatus Autorizado Suficiencia Automatica
                            $sql = "UPDATE tb_suficiencias SET nu_estatus = 4 WHERE nu_type = '".$_SESSION['PO'.$identifier]->suficienciaType."' and nu_transno = '".$_SESSION['PO'.$identifier]->suficienciaTransno."'";
                            $result =DB_query($sql, $db);

                            $sql = "UPDATE chartdetailsbudgetlog SET estatus = 4 WHERE type = '".$_SESSION['PO'.$identifier]->suficienciaType."' and transno = '".$_SESSION['PO'.$identifier]->suficienciaTransno."'";
                            $result =DB_query($sql, $db);

                            // Autorización de no existencia
                            $sql = "UPDATE tb_no_existencias SET status = 2 WHERE nu_id_requisicion = '".$_SESSION['PO'.$identifier]->RequisitionNo."'";
                            $result =DB_query($sql, $db);
                        }
                    }
                    //echo "<br>movimientos 1845";
                    if ($_SESSION['ExistingPurchOrder'] != 0) {
                        if (Havepermission($_SESSION['UserID'], 714, $db) == 1) {
                            include_once 'includes/mail.php';
                            include_once 'includes/GetOrderComments.inc';
                                
                            $orderNoTmp = $_SESSION['ExistingPurchOrder'];
                            $poItemTmp  = $POLine->PODetailRec;
                            $message    = $_POST['messages'][$orderNoTmp][$poItemTmp];
                                
                            if (empty($message) == false) {
                                $SQL = "
                                    INSERT INTO ordercomments (
                                        orderno,
                                        orderlineno,
                                        userid,
                                        comment,
                                        date
                                    ) VALUES (
                                        '$orderNoTmp',
                                        '$poItemTmp',
                                        '" . $_SESSION['UserID'] . "',
                                        '$message',
                                        NOW()
                                    )
                                ";
                                    
                                DB_query($SQL, $db);
                                $idMessage = DB_Last_Insert_ID($db);
                                $idMessages[] = $idMessage;
                            }
                        }
                    }
                }

                if (isset($_POST['AutorizaOrden']) && $automaticpurchdata == 1) {
                    $SQLOC = "SELECT *
                        FROM purchdata
                        WHERE purchdata.stockid = '".$POLine->StockID."'";
                    $ResultOC = DB_query($SQLOC, $db);
                    if (DB_num_rows($ResultOC) == 0) {//
                        $sql = "INSERT INTO purchdata (supplierno, 
                                                        stockid, 
                                                        price, 
                                                        conversionfactor, 
                                                        leadtime, 
                                                        preferred, 
                                                        effectivefrom, 
                                                        interno, 
                                                        pcurrcode,
                                                        tagref)
                                VALUE(
                                    '".$_SESSION['PO'.$identifier]->SupplierID."',
                                    '".$POLine->StockID."',
                                    '".$POLine->Price."',
                                    1,
                                    1,
                                    1,
                                    '". $_SESSION['PO'.$identifier]->Orig_OrderDate."',
                                    0,
                                    '". $_SESSION['PO'.$identifier]->CurrCode."',       
                                    '".$tagref."'   
                                )";
                        
                        $result =DB_query($sql, $db, $ErrMsg, $DbgMsg);
                    }
                }
            }
            //cambios ibeth
            //echo '<pre>'.$sql;
            $sql = "SELECT 
                itemcode,
                deliverydate,
                itemdescription,
                glcode,
                qtyinvoiced,
                unitprice,  
                actprice,   
                stdcostunit,
                quantityord,
                quantityrecd,
                shiptref,
                jobref,
                completed,  
                itemno,
                subtotal_amount,
                nw,
                gw,
                cuft,
                total_quantity,
                total_amount,
                discountpercent1,
                discountpercent2,
                discountpercent3,
                narrative,
                justification,
                estimated_cost
                FROM purchorderdetails
                WHERE quantityord <> quantityrecd
                AND orderno= ' ". $_SESSION['PO'.$identifier]->OrderNo ." '
                AND completed = 0";
            $ErrMsg = _('Hay un problema al seleccionar parte de los registros a mostrar porque');
            $DbgMsg = _('La instrucci�n SQL que fallo fue');
            $SearchResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
        
            if (DB_num_rows($SearchResult)==0) {
                $date = date($_SESSION['DefaultDateFormat']);
                $StatusComment = $date.' - Modificada: '.$_SESSION['UserID'].' - '.$_SESSION['PO'.$identifier]->StatusMessage.'<br>';
                
                $sql = "UPDATE purchorders
                    SET status= 'Completed',
                        stat_comment = CONCAT(stat_comment,' ','".$StatusComment."')
                    WHERE orderno=' ". $_SESSION['PO'.$identifier]->OrderNo ." '";
                $ErrMsg =  _('La orden de compra no se pudo actualizar porque');
                $DbgMsg = _('La instrucci�n SQL utilizada para actualizar registro de la orden de compra registro , fallo');
                $result =DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
                    
                $sql = "UPDATE purchorderdetails
                    SET completed= '1'
                    WHERE orderno=' ". $_SESSION['PO'.$identifier]->OrderNo ." '";
                $ErrMsg =  _('La orden de compra no se pudo actualizar porque');
                $DbgMsg = _('La instrucci�n SQL utilizada para actualizar registro de la orden de compra registro , fallo');
                $result =DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
                
                prnMsg(_('No hay productos para mostrar que cumplen los criterios previstos'), 'warn');
            }
    
            //cambios purchorderdetails.completed='1'";
            $numorden=$_SESSION['PO'.$identifier]->OrderNo;
            $contador=count($_SESSION['PO'.$identifier]->LineItems);
            /*******************************************************************************************************/
            //envio email a proveedor con la compra...
            if (isset($_POST['AutorizaOrden'])) {
                $OrderNoCompra=$_SESSION['PO'.$identifier]->OrderNo;
                $OrderNo=$_SESSION['PO'.$identifier]->OrderNo;
                //include('includes/SendEmailCompra.inc');
                $opercompra='Autorizar';
                //valida que el producto se encuentre en compras si es que ha sido cancelado
                include('includes/ProcessCancelRequisitionsCompra.inc');
                //inserta en log fecha de entrega de producto
                include('includes/ProcessLogDateRequisitionsCompra.inc');
            }
            // se agrega include para cancelar los productos que tenian esta requisicion
            if (isset($_POST['CancelaOrden'])) {
                //echo "entra";
                $OrderNoCompra=$_SESSION['PO'.$identifier]->OrderNo;
                $OrderNo=$_SESSION['PO'.$identifier]->OrderNo;
                $AlmacenCancel=$_SESSION['PO'.$identifier]->Location;
                $opercompra='Cancel';
                include('includes/ProcessCancelRequisitionsCompra.inc');
            }
            /*******************************************************************************************************/
            //condicion para saber si la orden contiene 2 o mas productos y enviar mensaje de eliminacion exitosa
            if ($GoodProcess==true) {
                //echo '<br><br>';
                $mensaje_emergente = "";
                if (isset($_POST['AutorizaOrden'])) {
                    $mensaje_emergente = "La Orden de Compra No. ".$_SESSION['PO'.$identifier]->OrderNo2." se ha Autorizado exitosamente.";
                } else {
                    $mensaje_emergente = "La Orden de Compra No. ".$_SESSION['PO'.$identifier]->OrderNo2." se ha Actualizo exitosamente.";
                }
                prnMsg($mensaje_emergente, 'success');

                $sqllegal = "SELECT legalid
                         FROM tags
                         WHERE tagref = '".$tagref."'";
                
                $resultlegal = DB_query($sqllegal, $db);
                $myrowlegal = DB_fetch_array($resultlegal);
                 /*if ($_SESSION['PO'.$identifier]->AllowPrintPO==1 and $contador>1){
                 //    echo '<br><a target="_blank" href="'.$rootpath.'/PO_PDFPurchOrder.php?' . SID . '&OrderNo=' . $_SESSION['PO'.$identifier]->OrderNo . '">' . _('Print Purchase Order') . '</a>';
                 }*/
                if (isset($_POST['Commit'])) {
//                  echo '<p class="page_title_text">';
//                  echo '<a target="_blank"  href="PO_PDFPurchOrder.php?' . SID . 'OrderNo=' . $_SESSION['PO'.$identifier]->OrderNo . '&identifier='.$identifier.'&tipodocto=25&Tagref='.$tagref.'&legalid='.$myrowlegal['legalid'].'">';
//                  echo '<img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Print') . '" alt=""><b>' . ' ' . _('IMPRIMIR ORDEN DE COMPRA NO.') . $_SESSION['PO'.$identifier]->OrderNo . '</a><BR>';
//      
//                  echo '<p class="page_title_text">';
//                  echo '<a href="PO_Header.php?' . SID . '&ModifyOrderNumber=' . $_SESSION['PO'.$identifier]->OrderNo . '"><b>'. _('MODIFICAR ORDEN DE COMPRA NO.') . $_SESSION['PO'.$identifier]->OrderNo .'</b></a>';
//      
//                  echo '<p>';
//                  echo '<div align="center"><a target="_blank" href="GoodsReceived.php?' . SID .'&PONumber='.$_SESSION['PO'.$identifier]->OrderNo.'&TieToOrderNumber='.$TieToOrderNumber.'"><b>IR A RECIBIR PRODUCTOS</b></a></div>';
//                  echo '<p>';
                } elseif (isset($_POST['PendingOrder'])) {
//                  echo '<p class="page_title_text">';
//                  echo '<a href="PO_Header.php?' . SID . '&ModifyOrderNumber=' . $_SESSION['PO'.$identifier]->OrderNo . '"><b>'. _('MODIFICAR ORDEN DE COMPRA NO.') . $_SESSION['PO'.$identifier]->OrderNo .'</b></a>';
                } elseif (isset($_POST['CancelaOrden'])) {
//                  echo '<p class="page_title_text">';
//                  echo '<a href="PO_Header.php?' . SID . '&ModifyOrderNumber=' . $_SESSION['PO'.$identifier]->OrderNo . '"><b>'. _('MODIFICAR ORDEN DE COMPRA NO.') . $_SESSION['PO'.$identifier]->OrderNo .'</b></a>';
                } elseif (isset($_POST['SurteOrden'])) {
//                  echo '<p class="page_title_text">';
//                  echo '<a href="PO_Header.php?' . SID . '&ModifyOrderNumber=' . $_SESSION['PO'.$identifier]->OrderNo . '"><b>'. _('MODIFICAR ORDEN DE COMPRA NO.') . $_SESSION['PO'.$identifier]->OrderNo .'</b></a>';
                } elseif (isset($_POST['AutorizaOrden'])) {
                    if ($_POST['recibOCProd'] == 2) {
                        if (receivePurchOrder($_SESSION['PO'.$identifier]->OrderNo, $db)) {
                            prnMsg("Se realizo la recepcion de la orden compra", "success");
                        } else {
                            prnMsg("No se realizo la recepcion de la orden compra", "error");
                        }
                    } else {
                        $totalcompra = 0;
                                    
                        foreach ($_SESSION['PO'.$identifier]->LineItems as $OrderLine) {
                            if ($OrderLine->Quantity != 0 and $OrderLine->Quantity != '' and isset($OrderLine->Quantity)) {
                                if ($_SESSION['PO']->ExRate == '') {
                                    $_SESSION['PO']->ExRate = 1;
                                }

                                //buscar margen automatico para costo en categoria de inventario
                                $qry = "SELECT margenautcost, 
                                                taxauthrates.taxrate
                                        FROM stockcategory
                                        INNER JOIN stockmaster ON stockcategory.categoryid = stockmaster.categoryid
                                        INNER JOIN taxauthrates ON stockmaster.taxcatid = taxauthrates.taxcatid
                                        WHERE stockmaster.stockid = '" . $OrderLine->StockID . "'";
                                $rsm = DB_query($qry, $db);
                                $rowm = DB_fetch_array($rsm);
                                $margenautcost = $rowm['margenautcost'] / 100;
                                $porcentaje_impuesto= 1 + $rowm['taxrate'];
                                $OrderLine->Price += ($OrderLine->Price * $margenautcost);
                                $LocalCurrencyPrice = ($OrderLine->Price / $_SESSION['PO']->ExRate);
                                if ($OrderLine->StockID != '') {
                                    $avgcost = 0;
                                    $avgcost = $LocalCurrencyPrice;
                                    $avgcost = $avgcost - ($avgcost * ($OrderLine->Desc1 / 100));
                                    $avgcost = $avgcost - ($avgcost * ($OrderLine->Desc2 / 100));
                                    $avgcost = $avgcost - ($avgcost * ($OrderLine->Desc3 / 100));
                                }
                                            
                                $purchdatasql = 'SELECT conversionfactor,price
                                                FROM purchdata
                                                WHERE purchdata.supplierno = "' . $_SESSION['PO']->SupplierID . '"
                                                AND purchdata.stockid="' . $OrderLine->StockID . '"';
                        
                                $rsm = DB_query($purchdatasql, $db);
                                $rowm = DB_fetch_array($rsm);

                                $factordeConversion = 1;
                                if (is_numeric($rowm['conversionfactor'])) {
                                        $factordeConversion = $rowm['conversionfactor'];
                                }
                                            
                                $CurrentStandardCost = $avgcost;
                                $CurrentStandardCost = $CurrentStandardCost / $factordeConversion;
                                $totalcompra+= ($CurrentStandardCost * $OrderLine->Quantity);
                            }
                        }
                        $totalcompra= truncateFloat($totalcompra, $digitos);
                        //$GRN = GetNextTransNo(25, $db);
                        //$GRN = GetNextTransNo(555, $db);
                        $PeriodNo = GetPeriod(date('d/m/Y'), $db, $tagref);
                           
                        /*$resultado= GeneraMovimientoContablePresupuesto(25, "POREJERCER", "COMPROMETIDO", $GRN, $PeriodNo,
                        $totalcompra, $tagref, $_SESSION['PO'.$identifier]->Orig_OrderDate, $db);*/
                    }
//                  echo '<p class="page_title_text">';
//                  echo '<a target="_blank"  href="PO_PDFPurchOrder.php?' . SID . 'OrderNo=' . $_SESSION['PO'.$identifier]->OrderNo . '&identifier='.$identifier.'&tipodocto=25&Tagref='.$tagref.'&legalid='.$myrowlegal['legalid'].'">';
//                  echo '<img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Print') . '" alt=""><b>' . ' ' . _('IMPRIMIR ORDEN DE COMPRA NO.') . $_SESSION['PO'.$identifier]->OrderNo . '</a><BR>';
//                  
//                  $sqlval = "SELECT if(purchorders.supplierorderno is null,0,2) as prov
//                          FROM purchorders
//                          WHERE orderno = '" . $_SESSION['PO'.$identifier]->OrderNo ."'";
//                  $resultval = DB_query($sqlval, $db);
//                  $rowval = DB_fetch_array($resultval);
//                  if($rowval['prov'] == 0){
//                      echo '<p class="page_title_text">';
//                      echo '<a target="_blank"  href="SendPOPDFToSupplier.php?' . SID . 'OrderNo=' . $_SESSION['PO'.$identifier]->OrderNo .'&tipodocto=25&Tagref='.$tagref.'&legalid='.$myrowlegal['legalid'].'">';
//                      echo '<img src="'.$rootpath.'/css/'.$theme.'/images/email.gif" title="' . _('Enviar pdf por email a proveedor') . '" alt=""><b>' . ' ' . _('Enviar PDF a proveedor') . '</a><BR>';
//                  }
//                  echo '<p class="page_title_text">';
//                  echo '<a href="PO_Header.php?' . SID . '&ModifyOrderNumber=' . $_SESSION['PO'.$identifier]->OrderNo . '"><b>'. _('MODIFICAR ORDEN DE COMPRA NO.') . $_SESSION['PO'.$identifier]->OrderNo .'</b></a>';
//      
//                  echo '<p>';
//                  echo '<div align="center"><a target="_blank" href="GoodsReceived.php?' . SID .'&PONumber='.$_SESSION['PO'.$identifier]->OrderNo.'&TieToOrderNumber='.$TieToOrderNumber.'"><b>IR A RECIBIR PRODUCTOS</b></a></div>';
//                  echo '<p>';
                } elseif (isset($_POST['RechazaOrden'])) {
//                  echo '<p class="page_title_text">';
//                  echo '<a href="PO_Header.php?' . SID . '&ModifyOrderNumber=' . $_SESSION['PO'.$identifier]->OrderNo . '"><b>'. _('MODIFICAR ORDEN DE COMPRA NO.') . $_SESSION['PO'.$identifier]->OrderNo .'</b></a>';
                }
                
//              echo '<p class="page_title_text">';
//              echo '<a href="PO_SelectOSPurchOrder.php?' . SID . '"><b>'. _('REGRESAR A LA BUSQUEDA DE ORDENES DE COMPRA') .'</b></a>';
//              echo '</p>';
                
                if (isset($TieToOrderNumber) and $TieToOrderNumber<>'') {
//                  echo '<p>';
//                  echo '<div align="center"><a href="'.$SelectOrderItemsFile.'?' . SID .'&ModifyOrderNumber='.$TieToOrderNumber.'"><b>REGRESAR AL PEDIDO DE VENTA NO. '.$TieToOrderNumber.'</b></a></div>';
//                  echo '<p>';
                }
                //
                if (isset($_POST['PendingOrder'])) {
                    // Env�a solicitud de cotizaci�n
                    // echo 'entraaaa';
                    require_once('./includes/SendMailEstimated.inc');
                }
                 
                $to =  $_SESSION['FactoryManagerEmail'];
                
                if (isset($_POST['Commit'])) {
                    $status = "Procesada";
                } else if (isset($_POST['CancelaOrden'])) {
                    $status = "Cancelada";
                } else if (isset($_POST['AutorizaOrden'])) {
                    $status = "Autorizada";
                } else if (isset($_POST['RechazaOrden'])) {
                    $status = "Rechazada";
                } else if (isset($_POST['SurteOrden'])) {
                    $status = "Cotizada";
                } else {
                    $status = "";
                }
            
                if (empty($status) == false && 1 == 2) {
                    require_once('./includes/mail.php');
                    
                    $userId     = $_SESSION['UserID'];
                    $userName   = "";
                    if ($status != 'Autorizada') {
                        $rsUser     = DB_query("SELECT realname, email FROM www_users WHERE userid = '$userId'", $db);
                        if ($rowUser = DB_fetch_array($rsUser)) {
                            $userName = ucwords(strtolower($rowUser['realname']));
                            $userEmail = $rowUser['email'];
                            if (empty($userEmail) == false) {
                                $to .= "," . $userEmail;
                            }
                        }
                    }
                    
                    if ($status == 'Cotizada' || $status == 'Autorizada' || $status == 'Rechazada') {
                        $pedidoventa = '';
                        $sql = "
                            SELECT requisitionno
                            FROM purchorders
                            WHERE purchorders.orderno = '" . $_SESSION['PO'.$identifier]->OrderNo . "'
                        ";
                        
                        $rsTmp = DB_query($sql, $db);
                        if ($rowTmp = DB_fetch_array($rsTmp)) {
                            $pedidoventa = $rowTmp['requisitionno'];
                        }
                        
                        $rsTmp = DB_query("
                            SELECT www_users.email
                            FROM purchorders 
                            INNER JOIN salesorders
                            ON purchorders.requisitionno = salesorders.orderno
                            INNER JOIN salesman
                            ON salesman.salesmancode = salesorders.salesman
                            INNER JOIN www_users
                            ON salesman.usersales = www_users.userid
                            WHERE purchorders.orderno = '" . $_SESSION['PO'.$identifier]->OrderNo . "'
                        ", $db);
                        
                        if ($rowTmp = DB_fetch_array($rsTmp)) {
                            if (empty($rowTmp['email']) == false) {
                                $to .= ',' . $rowTmp['email'];
                            }
                        }
                        
                        // se envia al que inicio el proceso de venta
                        $sql = "SELECT www_users.email
                            FROM purchorders
                            INNER JOIN salesorders
                            ON purchorders.requisitionno = salesorders.orderno  
                            INNER JOIN www_users
                            ON salesorders.UserRegister = www_users.userid
                            WHERE purchorders.orderno = '" . $_SESSION['PO'.$identifier]->OrderNo . "'";
                        
                        $rsTmp = DB_query($sql, $db);
                        
                        if ($rowTmp = DB_fetch_array($rsTmp)) {
                            if (empty($rowTmp['email']) == false) {
                                $to .= ',' . $rowTmp['email'];
                            }
                        }
                        
                        // Envia al proveedor que tiene asignada la orden de compra cuando es autorizada
                        if ($status == 'Autorizada') {
                            $sql = "SELECT email FROM purchorders
                                INNER JOIN suppliers ON suppliers.supplierid = purchorders.supplierno
                                WHERE purchorders.orderno = '{$_SESSION['PO'.$identifier]->OrderNo}'";
                                
                            $rsTmp = DB_query($sql, $db);
                            if ($rowTmp = DB_fetch_array($rsTmp)) {
                                if (empty($rowTmp['email']) == false) {
                                    $to .= ',' . $rowTmp['email'];
                                }
                            }
                        }
                    }
                    
                    $mail = new Mail();
                    $mail->setTo($to);
                    $mail->setFrom("soporte@tecnoaplicada.com");
                    $mail->setSender("Soporte");
                    $mail->setSubject(strtoupper($_SESSION['DatabaseName']) . " - Notificaci&oacute;n Orden Compra No. " . $_SESSION['PO'.$identifier]->OrderNo2);
                    $mail->setHtml("
                        El usuario $userName ha realizado la siguiente operacion: <br />
                        El estado de la orden de compra No. " . $_SESSION['PO'.$identifier]->OrderNo2 . " ha cambiado a " . $status . "
                        <br>La orden de venta asociada es: " . $pedidoventa);
                    $mail->send();
                }
            }
        } /*end of if its a new order or an existing one */

        //$sql = 'COMMIT';
        //$result = DB_query($sql,$db);
        
        if (Havepermission($_SESSION['UserID'], 714, $db) == 1) {
            if (empty($idMessages) == false && 1 == 2) {
                $commentsHTML = getOrderCommentsTable($orderNoTmp, 0, $db, $idMessages);
                
                $emails_tmp = array();
                    
                $SQL = "SELECT UserRegister, usersales, w1.email AS email1, w2.email AS email2
                        FROM purchorders
                        INNER JOIN salesorders ON purchorders.requisitionno = salesorders.orderno
                        INNER JOIN salesman ON salesman.salesmancode = salesorders.salesman
                        INNER JOIN www_users w1 ON salesman.usersales = w1.userid
                        INNER JOIN www_users w2 ON salesorders.UserRegister = w2.userid
                        WHERE purchorders.orderno = '$orderNoTmp'";

                $result = DB_query($SQL, $db);
                    
                if ($row = DB_fetch_array($result)) {
                    $userregister   = $row['UserRegister'];
                    $usersale       = $row['UserSales'];
                    $email1         = $row['email1'];
                    $email2         = $row['email2'];
        
                    if ($userregister != $usersale) {
                        if (empty($row['email1']) == false) {
                            $emails_tmp[] = $row['email1'];
                        }
                        if (empty($row['email2']) == false) {
                            $emails_tmp[] = $row['email2'];
                        }
                    } else {
                        if (empty($row['email1']) == false) {
                            $emails_tmp[] = $row['email1'];
                        }
                    }
                }
                
                $mail = new Mail();
                if (empty($emails_tmp) == true) {
                    //  $emails_tmp = $_SESSION['FactoryManagerEmail'];//
                    $mail->setTo($_SESSION['FactoryManagerEmail']);
                } else {
                    $mail->setTo(implode(',', $emails_tmp));
                }
                                    
                $mail->setFrom("soporte@tecnoaplicada.com");
                $mail->setSender("tecnoaplicada");
                $mail->setSubject("Nuevo Comentario En Orden de Compra No. " . $orderNoTmp);
                $mail->setHtml("El usuario " . $_SESSION['UserID'] . " ha realizado los siguientes comentarios:<br /><br />$commentsHTML");
                $mail->send();
            }
        }

        // Validar si existen ordenes sin autizar
        $SQL = "SELECT purchorders.orderno FROM purchorders WHERE purchorders.requisitionno IN ('".$_SESSION['PO'.$identifier]->RequisitionNo."')
        AND purchorders.status IN (SELECT tb_botones_status.statusname FROM tb_botones_status WHERE tb_botones_status.sn_funcion_id = '2265')";
        $ErrMsg2 = "Ordenes de Compra sin Autorizar de la Requisición ".$_SESSION['PO'.$identifier]->RequisitionNo;
        $result = DB_query($SQL, $db, $ErrMsg2);
        if (DB_num_rows($result) == 0) {
            // Todo esta autorizado revisar diferencias
            $SQL = "SELECT
            SUM(chartdetailsbudgetlog.qty) as total,
            chartdetailsbudgetlog.cvefrom
            FROM tb_suficiencias
            JOIN chartdetailsbudgetlog ON chartdetailsbudgetlog.type = tb_suficiencias.nu_type AND chartdetailsbudgetlog.transno = tb_suficiencias.nu_transno
            WHERE tb_suficiencias.nu_transno = '".$_SESSION['PO'.$identifier]->suficienciaTransno."' AND chartdetailsbudgetlog.nu_tipo_movimiento = 263
            GROUP BY cvefrom
            HAVING total <> 0
            ";
            $ErrMsg2 = "No se obtuvieron diferencias de la Requisición ".$_SESSION['PO'.$identifier]->RequisitionNo;
            $TransResult2 = DB_query($SQL, $db, $ErrMsg2);
            while ($myrow = DB_fetch_array($TransResult2)) {
                // Regresar diferencias entre los nuevos montos y la suficiencia
                // echo "<br>total: ".$myrow['total'];
                $descriptionLog = "Autorización Orden de Compra ".$_SESSION['PO'.$identifier]->OrderNo2.". Requisición ".$_SESSION['PO'.$identifier]->RequisitionNo.". Diferencias con la Suficiencia Presupuestal";
                $agregoLog = fnInsertPresupuestoLog($db, $_SESSION['PO'.$identifier]->suficienciaType, $_SESSION['PO'.$identifier]->suficienciaTransno, $tagref, $myrow['cvefrom'], $PeriodNo, abs($myrow['total']), 263, "", $descriptionLog, 0, '', 0, $_SESSION['PO'.$identifier]->unidadEjecutora); // Suficiencia Automatica
            }
        }

        // Agregar aqui las opciones finales del proceso
        $status = 'SELECT status FROM purchorders WHERE orderno="'.$_SESSION['PO'.$identifier]->OrderNo.'"';
        $restatus = DB_query($status, $db);
        list($status) = DB_fetch_array($restatus);

        echo "<br>";
        echo '<table class="table table-bordered">';
        echo '<tr class="header-verde">';
        echo '<th align="center" colspan=3 style="text-align:center;"><b>'._("Orden de Compra").'</b></th>';
        //echo '<th align="center" colspan=1 style="text-align:center;"><b>'._("Póliza").'</b></th>';
        echo '<th align="center" style="text-align:center;" colspan=4><b>'._("Acciones Siguientes").'</b></th>';
        echo '</tr>';
        echo '<tr>';
        echo '<td align="center" style="text-align:center;"><b>'.$_SESSION['PO'.$identifier]->OrderNo2."</b></td>";
        echo '<td align="center" style="text-align:center;">';
        if ($status!=='Authorised') {
            $enc = new Encryption;
            $url = "&ModifyOrderNumber=>" . $_SESSION['PO'.$identifier]->OrderNo;
            $url = $enc->encode($url);
            $liga= "URL=" . $url;
            echo '<a href="PO_Items.php?' . $liga . '"><b>'. _('Modificar').'</b></a>';
        }
        echo '</td >';
        echo '<td align="center"> ';
        //echo '<p class="page_title_text"><a target="_blank"  href="PO_PDFPurchOrder.php?' . SID . 'OrderNo=' . $_SESSION['PO'.$identifier]->OrderNo . '&identifier='.$identifier.'&tipodocto=25&Tagref='.$tagref.'&legalid='.$myrowlegal['legalid'].'">';
        
        $enc = new Encryption;
        $url = "&OrderNo=>" . $_SESSION['PO'.$identifier]->OrderNo . "&identifier=>" . $identifier . "&tipodocto=>555&Tagref=>" . $tagref . "&legalid=>" . $myrowlegal['legalid'];
        $url = $enc->encode($url);
        $liga= "URL=" . $url;

        //echo '<p class="page_title_text"><a target="_blank"  href="PO_PDFPurchOrder.php?' . SID . 'OrderNo=' . $_SESSION['PO'.$identifier]->OrderNo . '&identifier='.$identifier.'&tipodocto=555&Tagref='.$tagref.'&legalid='.$myrowlegal['legalid'].'">';
        echo '<p class="page_title_text"><a target="_blank"  href="PO_PDFPurchOrder.php?' . $liga . '">';
        echo '<img src="images/printer.png" title="' . _('Imprimir') . '" alt=""></a></p>';
        echo '</td>';

        // Consultar si la orden tiene un proveedor asignado
        $sqlval = "SELECT if(purchorders.supplierorderno is null, 0, 2) as prov FROM purchorders WHERE orderno = '" . $_SESSION['PO'.$identifier]->OrderNo ."'";
        $resultval = DB_query($sqlval, $db);
        $rowval = DB_fetch_array($resultval);

        // si no tiene proveedor asignar para enviar correo se muestra la liga para enviar
        if ($rowval['prov'] == 0) {
            // echo '<td align="center">';
            // echo '<p class="page_title_text">';
            // $enc = new Encryption;
            // $url = "&OrderNo=>" . $_SESSION['PO'.$identifier]->OrderNo . "&tipodocto=>555&Tagref=>" . $tagref . "&legalid=>" . $myrowlegal['legalid'];
            // $url = $enc->encode($url);
            // $liga= "URL=" . $url;
            // echo '<a target="_blank"  href="SendPOPDFToSupplier.php?' . $liga . '">';
            // echo '<img src="'.$rootpath.'/css/'.$theme.'/images/email.gif" title="' . _('Enviar a proveedor') . '" alt=""></a><BR>';
            // echo '</td>';
        }
        
        /* No mostrar Informacion de Poliza
        if ($_SESSION['PO'.$identifier]->total!=0) {
            if ($status!=='Authorised') {
                echo '<td class="page_title_text" title="No disponible hasta autorizar la Orden de Compra">Póliza ND</td>';
            } else {
                echo '<td align="center">';
                echo '<p class="page_title_text">';
                $enc = new Encryption;
                $url = "&FromCust=>1&ToCust=>1&PrintPDF=>Yes&TransNo=>" . $GRN . "&periodo=>" . $periodo . "&transdate=>" . $date . "&type=>555";
                $url = $enc->encode($url);
                $liga= "URL=" . $url;
                echo '<a target="_blank"  href="PrintJournal.php?' . $liga . '">';
                echo '<img src="images/printer.png" title="' . _('Póliza') . '" alt=""></a></p>';
                echo '</td>';
            }
        } else {
            echo '<td title="No disponible cuando la factura es por $0.00">Póliza ND</td>';
        }*/
        
        /* No mostrar Informacion de Recibir Productos
        if ($status!=='Authorised') {
            echo '<td class="page_title_text" title="No disponible hasta autorizar la Orden de Compra">Recibir Producto</td>';
        } else {
            $enc = new Encryption;
            $url = "&PONumber=>" . $_SESSION['PO'.$identifier]->OrderNo . "&TieToOrderNumber=>" . $TieToOrderNumber;
            $url = $enc->encode($url);
            $liga= "URL=" . $url;
            echo '<td align="left">';
            echo '<div align="left"><a target="_blank" href="GoodsReceived.php?' . $liga . '"><b>Recibir Productos</b></a></div>';
            echo '</td>';
        }*/
        
        echo '<td>';
        echo '<a href="panel_ordenes_compra.php?' . SID . '"><b>'. _('Búsqueda de Ordenes de Compra') .'</b></a>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
                
        
        //insertar en log de estatus
        $qry = "insert into logpurchorderstatus (orderno,userid,status,registerdate)
                Select orderno,'".$_SESSION['UserID']."',status,now()
                FROM purchorders
                WHERE orderno = ".$_SESSION['PO'.$identifier]->OrderNo;

        $Result = DB_query($qry, $db);
        
        if ($factorconversion == 1) {
            //actualiza factor de conversion en productos
            $SQL="UPDATE  purchorderdetails,(  
                    SELECT  purchorderdetails.podetailitem,purchdata.conversionfactor 
                    FROM purchorderdetails  
                        INNER JOIN purchorders on purchorderdetails.orderno=purchorders.orderno
                        INNER JOIN purchdata ON purchdata.supplierno=purchorders.supplierno 
                            AND purchorderdetails.itemcode=purchdata.stockid
                    WHERE purchorderdetails.orderno = '".$_SESSION['PO'.$identifier]->OrderNo."'
                    
                    ) AS X
                SET purchorderdetails.factorconversion=conversionfactor
                WHERE purchorderdetails.podetailitem=X.podetailitem";
            $Result = DB_query($SQL, $db, '', '', true);
        }
        
        // Elimina informacion de tabla de propiedades
        $SQL = "DELETE FROM salesstockproperties 
                WHERE typedocument=30 and orderno=".$_SESSION['PO'.$identifier]->OrderNo;
        $ErrMsg="No se pudo actualizar los campos extra";
        // $Result = DB_query($SQL, $db, $ErrMsg);
        $tipodefacturacion=30;
        
        foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
            $lineaorden='_'.$POLine->LineNo;
            //********************************* CAMPOS EXTRA **********************************************
            $totalcampos=$_POST['TotalPropDefault_'.$POLine->LineNo];
            
            if ($totalcampos>0) {
                for ($i=0; $i<$totalcampos; $i++) {
                    $stockid=$_POST['PropDefaultval'.$lineaorden.'_'.$i];
                    $valorstock=$_POST['PropDefault'.$lineaorden.'_'.$i];
                    $tipoobj=$_POST['tipoobjeto'.$lineaorden.'_'.$i];
                    $consulta=$_POST['consulta'.$lineaorden.'_'.$i];
                    $campo=$_POST['campo'.$lineaorden.'_'.$i];
                    $classe = $_POST['class'.$lineaorden.'_'.$i];
                    $required = $_POST['required'.$lineaorden.'_'.$i];
                    $requiredtoday = $_POST['requiredtoday'.$lineaorden.'_'.$i];
                    $labelprop = $_POST['label'.$lineaorden.'_'.$i];
            
                    
            
                    if ($tipoobj=='checkbox') {
                        if (isset($_POST['PropDefault'.$lineaorden.'_'.$i])) {
                            $valorstock="SI";
                        } else {
                            $valorstock="NO";
                        }
                    }
                    $valorbase=$valorstock;
                    if (strlen($consulta)>5) {
                        $sqlcampos=$consulta.' and '. $campo.' = "'.$valorstock.'"';
                        //echo $sqlcampos;
                        $DbgMsg = _('El SQL utilizado para obtener el valor del campo es');
                        $ErrMsg = _('No se pudo obtener el valor, por que');
                        $Result = DB_query($sqlcampos, $db, $ErrMsg, $DbgMsg, true);
                        $Rowcampos = DB_fetch_array($Result);
                        $valorbase=$Rowcampos[1];
                    }
                    if ($valorstock=="0" and strlen($consulta)>5 and $tipoobjeto == 5) {
                        $sqlcampos=$consulta.' and salesmanname like "%sin trabajador%" and tags.tagref= '.$_SESSION['Items'.$identifier]->Tagref.' limit 1';
                        $DbgMsg = _('El SQL utilizado para obtener el valor del campo es');
                        $ErrMsg = _('No se pudo obtener el valor, por que');
                        $Result = DB_query($sqlcampos, $db, $ErrMsg, $DbgMsg, true);
                        $Rowcampos = DB_fetch_array($Result);
                        $valorbase=$Rowcampos[1];
                        $valorstock=$Rowcampos[0];
                    }
                    if ($valorstock!="0" and $valorstock != "") {
                        //$existeprop=ValidaSalesProperty($stockid,$_SESSION['PO'.$identifier]->OrderNo,$POLine->PODetailRec,trim($valorstock),$tipodefacturacion,$db);
                        $existeprop=0;
                        if ($existeprop==0) {
                            $SQL = "INSERT INTO salesstockproperties (
                                    stkcatpropid,
                                    orderno,
                                    orderlineno,
                                    valor,
                                    InvoiceValue,
                                    typedocument
                                )
                                VALUES
                                (
                                    '". $stockid . "',
                                    '" . $_SESSION['PO'.$identifier]->OrderNo. "',
                                    '" . $POLine->PODetailRec . "',
                                    '" . trim($valorstock) . "',
                                    '" . trim($valorbase) . "',
                                    '". $tipodefacturacion . "'
                                )";
                            $ErrMsg="Error al insertar los valores extra";
                            // $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                            //echo '<br>sql:'.$SQL;
                        }
                    }
                //***********************+Campo de Categoria********************************
                    $stockid1 = explode("/", $_POST['txtRuta'.$StockItem->LineNumber]);
                    foreach ($stockid1 as $arrCategorias) {
                        $categoriadetail=explode(".", $arrCategorias);
                        if ($categoriadetail[0]!='') {
                            $SQL = "INSERT INTO salesstockproperties (
                                    stkcatpropid,
                                    orderno,
                                    orderlineno,
                                    valor,
                                    InvoiceValue,
                                    typedocument
            
                                )
                                VALUES
                                (
                                    '". $categoriadetail[0]. "',
                                    '" . $_SESSION['PO'.$identifier]->OrderNo . "',
                                    '" . $POLine->PODetailRec. "',
                                    '" . trim($categoriadetail[1]) . "',
                                    '" . trim($categoriadetail[1]) . "',
                                    '". $tipodefacturacion . "'
                                )";
                            $ErrMsg="Error al insertar los valores extra";
                            // $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                        }//Fin de insert de propiedades en menu desplegable
                    }//Fin de categorias de producto en menu desplegable
                } //Fin de recorrido de partidas de pedido
            }  // Fin de alta de propiedades y numeros de serie para pedido de venta
        }
        $Result = DB_Txn_Commit($db);
        
        //si tiene permiso para envio de orden de compra segun estatus de la misma
        if (Havepermission($_SESSION['UserID'], 643, $db)==1) {
            $OrderNo = $_SESSION['PO'.$identifier]->OrderNo;
            //echo 'carxx';((
//                        ini_set('display_errors', 1);
//  ini_set('log_errors', 1);
//  ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); error_reporting(E_ALL);
            //include('PDFPurchOrderMail.php');
        }

        // echo "<br>txtRechazoSuficiencia: ".$_POST['txtRechazoSuficiencia'];
        
        $liga = "panel_ordenes_compra.php";
        if (isset($_POST['txtRechazoSuficiencia']) && $_POST['txtRechazoSuficiencia'] == '1') {
            // echo "<br> se rechazo la suficiencia";
            $urlGeneral = "&transno=>" . $_SESSION['PO'.$identifier]->suficienciaTransno . "&type=>" . $_SESSION['PO'.$identifier]->suficienciaType;
            $enc = new Encryption;
            $url = $enc->encode($urlGeneral);
            $liga = "suficiencia_manual.php?URL=" . $url;
        }

        unset($_SESSION['PO'.$identifier]->OrderNo);
        unset($_SESSION['PO'.$identifier]); /*Clear the PO data to allow a newy to be input*/
        //echo "<br><div style='text-align:center; margin: 0 auto;'><a href='".$rootpath."/PO_SelectOSPurchOrder.php?" . SID . "&ActivarBoton=1'>" . _(' B&uacute;squeda de Ordenes de Compra') . '</a></div>';
        include('includes/footer_Index.inc');
        
        ?>
        <script type="text/javascript">
            /**
             * Función para regresar al panel
             * @return {[type]} [description]
             */
            function fnRegresarPanel() {
                window.open("<?php echo $liga; ?>", "_self");
            }
        </script>
        <?php
        if ($mensaje_emergente != "") {
            ?>
            <script type="text/javascript">
                var mensajeMod = "<?php echo $mensaje_emergente; ?>";
                mensajeMod = '<p>'+mensajeMod+'</p>';
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                muestraModalGeneral(3, titulo, mensajeMod, '', 'fnRegresarPanel()');
            </script>
            <?php
        }
        exit;
    } /*end if there were no input errors trapped */
} /* end of the code to do transfer the PO object to the database  - user hit the place PO*/


/*ie seach for stock items */
if (isset($_POST['Search'])) {
    if ($_POST['Keywords'] and $_POST['StockCode']) {
        $msg=_('Stock description keywords have been used in preference to the Stock code extract entered');
    }
    //echo "entra";
    if ($_POST['Keywords']) {
        //insert wildcard characters in spaces

        $i=0;
        $SearchString = '%';
        while (strpos($_POST['Keywords'], ' ', $i)) {
            $wrdlen=strpos($_POST['Keywords'], ' ', $i) - $i;
            $SearchString=$SearchString . substr($_POST['Keywords'], $i, $wrdlen) . '%';
            $i=strpos($_POST['Keywords'], ' ', $i) +1;
        }
        $SearchString = $SearchString. substr($_POST['Keywords'], $i).'%';
        // se agrega la consulta por codigo de barras
        
        
        if ($_POST['StockCat']=='All') {
            $sql = "SELECT DISTINCT stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.barcode,
                    stockmaster.manufacturer
                FROM stockmaster INNER JOIN stockcategory
                ON stockmaster.categoryid=stockcategory.categoryid
                LEFT JOIN purchdata ON purchdata.stockid = stockmaster.stockid
                , sec_stockcategory sec
                WHERE stockmaster.mbflag!='A'
                AND stockmaster.mbflag!='K'";
            if ($_SESSION['ProhibitPurchD']==1) {
                $sql = $sql. " AND stockmaster.mbflag!='D'";
            }
            $sql = $sql. "  AND stockmaster.discontinued!=1
                AND stockmaster.categoryid=sec.categoryid 
                AND stockmaster.description " . LIKE . " '$SearchString'";
            if ($_POST['CurrCode'] <> "*") {
                $sql = $sql. " AND purchdata.pcurrcode = '".$_POST['CurrCode']."'";
            }
            // AND userid='".$_SESSION['UserID']."'
            $sql = $sql." ORDER BY stockmaster.stockid";
        } else {
            $sql = "SELECT DISTINCT stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.barcode,
                    stockmaster.manufacturer
                FROM stockmaster INNER JOIN stockcategory
                ON stockmaster.categoryid=stockcategory.categoryid
                LEFT JOIN purchdata ON purchdata.stockid = stockmaster.stockid
                WHERE stockmaster.mbflag!='A'
                AND stockmaster.mbflag!='K'";
            if ($_SESSION['ProhibitPurchD']==1) {
                $sql = $sql. " AND stockmaster.mbflag!='D'";
            }
            $sql = $sql. " and stockmaster.discontinued!=1
                AND stockmaster.description " . LIKE . " '$SearchString'
                AND stockmaster.categoryid='" . $_POST['StockCat'] . "'";
            if ($_POST['CurrCode'] <> "*") {
                $sql = $sql. " AND purchdata.pcurrcode = '".$_POST['CurrCode']."'";
            }
                $sql = $sql. " ORDER BY stockmaster.stockid";
        }
    } elseif ($_POST['StockCode']) {
        $_POST['StockCode'] = '%' . $_POST['StockCode'] . '%';

        if ($_POST['StockCat']=='All') {
            $sql = "SELECT DISTINCT stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.barcode,
                    stockmaster.manufacturer
                FROM stockmaster INNER JOIN stockcategory
                ON stockmaster.categoryid=stockcategory.categoryid
                LEFT JOIN (SELECT * FROM purchdata WHERE purchdata.stockid = '{$_POST['StockCode']}' GROUP BY purchdata.stockid) AS purchdata
                ON purchdata.stockid = stockmaster.stockid
                , sec_stockcategory sec
                WHERE stockmaster.mbflag!='A'
                AND stockmaster.mbflag!='K'
                ";
            if ($_SESSION['ProhibitPurchD']==1) {
                $sql = $sql. " AND stockmaster.mbflag!='D'";
            }
            $sql = $sql. " and stockmaster.discontinued!=1";
            
            if ($_SESSION['SearchBarcode']==0) {
                //$sql=$sql." AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'";
                $sql=$sql." AND (replace(replace(stockmaster.stockid,'.','_'),' ','_') like '". $_POST['StockCode']  ."' or stockmaster.stockid like '".$_POST['StockCode'] ."')";
            } else {
                //$sql=$sql." AND stockmaster.barcode " . LIKE . " '" . $_POST['StockCode'] . "'";
                $sql = $sql ." AND (stockmaster.barcode LIKE  '%". $_POST['StockCode'] ."%' or stockmaster.stockid LIKE  '%". $_POST['StockCode'] ."%')";
            }
            $sql=$sql." AND stockmaster.categoryid=sec.categoryid ";
            // AND userid='".$_SESSION['UserID']."'
            if ($_POST['CurrCode'] <> "*") {
                $sql = $sql. " AND purchdata.pcurrcode = '".$_POST['CurrCode']."'";
            }
            $sql = $sql." ORDER BY stockmaster.stockid";
        } else {
            $sql = "SELECT DISTINCT stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.barcode,
                    stockmaster.manufacturer
                FROM stockmaster INNER JOIN stockcategory
                ON stockmaster.categoryid=stockcategory.categoryid
                LEFT JOIN (SELECT * FROM purchdata WHERE purchdata.stockid = '{$_POST['StockCode']}' GROUP BY purchdata.stockid) AS purchdata
                ON purchdata.stockid = stockmaster.stockid
                WHERE stockmaster.mbflag!='A'
                AND stockmaster.mbflag!='K'";
            if ($_SESSION['ProhibitPurchD']==1) {
                $sql = $sql. " AND stockmaster.mbflag!='D'";
            }
            $sql = $sql. "
                and stockmaster.discontinued!=1";
            if ($_SESSION['SearchBarcode']==0) {
                $sql=$sql." AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'";
            } else {
                $sql = $sql ." AND (stockmaster.barcode LIKE  '%". $_POST['StockCode'] ."%' or stockmaster.stockid LIKE  '%". $_POST['StockCode'] ."%')";
            }
            $sql=$sql." AND stockmaster.categoryid='" . $_POST['StockCat'] . "'";
            if ($_POST['CurrCode'] <> "*") {
                $sql = $sql. " AND purchdata.pcurrcode = '".$_POST['CurrCode']."'";
            }
            $sql = $sql." ORDER BY stockmaster.stockid";
        }
    } else {
        if ($_POST['StockCat']=='All') {
            $sql = "SELECT DISTINCT stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.barcode,
                    stockmaster.manufacturer
                FROM stockmaster INNER JOIN stockcategory
                ON stockmaster.categoryid=stockcategory.categoryid
                LEFT JOIN purchdata ON purchdata.stockid = stockmaster.stockid
                , sec_stockcategory sec
                WHERE stockmaster.mbflag!='A'
                AND stockmaster.mbflag!='K'
                ";
            if ($_SESSION['ProhibitPurchD']==1) {
                $sql = $sql. " AND stockmaster.mbflag!='D'";
            }
            $sql = $sql. "
                AND stockmaster.discontinued!=1
                AND stockmaster.categoryid=sec.categoryid ";
            // AND userid='".$_SESSION['UserID']."'
            if ($_POST['CurrCode'] <> "*") {
                $sql = $sql. " AND purchdata.pcurrcode = '".$_POST['CurrCode']."'";
            }
            $sql = $sql." ORDER BY stockmaster.stockid";
        } else {
            $sql = "SELECT stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.barcode,
                    stockmaster.manufacturer
                FROM stockmaster INNER JOIN stockcategory
                ON stockmaster.categoryid=stockcategory.categoryid
                LEFT JOIN purchdata ON purchdata.stockid = stockmaster.stockid
                WHERE stockmaster.mbflag!='A'
                AND stockmaster.mbflag!='K'";
            if ($_SESSION['ProhibitPurchD']==1) {
                $sql = $sql. " AND stockmaster.mbflag!='D'";
            }
            $sql = $sql. "
                and stockmaster.discontinued!=1
                AND stockmaster.categoryid='" . $_POST['StockCat'] . "'";
            if ($_POST['CurrCode'] <> "*") {
                $sql = $sql. " AND purchdata.pcurrcode = '".$_POST['CurrCode']."'";
            }
            $sql = $sql." ORDER BY stockmaster.stockid";
        }
    }

    $ErrMsg = _('There is a problem selecting the part records to display because');
    $DbgMsg = _('The SQL statement that failed was');
    $SearchResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
    //echo "<pre>" . $sql;
    if (DB_num_rows($SearchResult)==0 && $debug==1) {
            prnMsg(_('Productos no encontrados...'), 'warn');
    }
    
    if (DB_num_rows($SearchResult)==1) {
        $myrow=DB_fetch_array($SearchResult);
        $_GET['NewItem'] = $myrow['stockid'];
        DB_data_seek($SearchResult, 0);
    }
} //end of if search

/* Always do the stuff below if not looking for a supplierid */

if (isset($_GET['Delete'])) {
    if ($_SESSION['PO'.$identifier]->Some_Already_Received($_POST['LineNo'])==0) {
        $_SESSION['PO'.$identifier]->LineItems[$_GET['Delete']]->Deleted=true;
        include('includes/PO_UnsetFormVbls.php');
    } else {
        prnMsg(_('This item cannot be deleted because some of it has already been received'), 'warn');
    }
}


if (isset($_POST['LookupPrice']) and isset($_POST['StockID2'])) {
    $sql = "SELECT purchdata.price,
            purchdata.conversionfactor,
            purchdata.supplierdescription
        FROM purchdata
        WHERE  purchdata.supplierno = '" . $_SESSION['PO'.$identifier]->SupplierID . "'
        AND purchdata.stockid = '". strtoupper($_POST['StockID2']) . "'";

    $ErrMsg = _('The supplier pricing details for') . ' ' . strtoupper($_POST['StockID']) . ' ' . _('could not be retrieved because');
    $DbgMsg = _('The SQL used to retrieve the pricing details but failed was');
    $LookupResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

    if (DB_num_rows($LookupResult)==1) {
        $myrow = DB_fetch_array($LookupResult);
        $_POST['Price'] = $myrow['price']/$myrow['conversionfactor'];
    } else {
        prnMsg(_('Sorry') . ' ... ' . _('there is no purchasing data set up for this supplier') . '  - ' . $_SESSION['PO'.$identifier]->SupplierID . ' ' . _('and item') . ' ' . strtoupper($_POST['StockID']), 'warn');
    }
}

/*Start assuming the best ... now look for the worst*/
if (isset($_POST['UpdateLine'])) {
    $AllowUpdate=true;

    if ($_POST['Qty']==0 or $_POST['Price'] < 0) {
        $AllowUpdate = false;
        prnMsg(_('The Update Could Not Be Processed') . '<br>' . _('You are attempting to set the quantity ordered to zero, or the price is set to an amount less than 0'), 'error');
    }

    if ($_SESSION['PO'.$identifier]->LineItems[$_POST['LineNo']]->QtyInv > $_POST['Qty'] or $_SESSION['PO'.$identifier]->LineItems[$_POST['LineNo']]->QtyReceived > $_POST['Qty']) {
        $AllowUpdate = false;
        prnMsg(_('The Update Could Not Be Processed') . '<br>' . _('You are attempting to make the quantity ordered a quantity less than has already been invoiced or received this is of course prohibited') . '. ' . _('The quantity received can only be modified by entering a negative receipt and the quantity invoiced can only be reduced by entering a credit note against this item'), 'error');
    }

    if ($_SESSION['PO'.$identifier]->GLLink==1) {
        /*Check for existance of GL Code selected */
        $sql = 'SELECT accountname 
                FROM chartmaster 
                WHERE accountcode =' .  $_SESSION['PO'.$identifier]->LineItems[$_POST['LineNo']]->GLCode;
        $ErrMsg = _('The account name for') . ' ' . $_POST['GLCode'] . ' ' . _('could not be retrieved because');
        $DbgMsg = _('The SQL used to retrieve the account details but failed was');
        $GLActResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
        if (DB_error_no($db)!=0 or DB_num_rows($GLActResult)==0) {
            $AllowUpdate = false;
            prnMsg(_('The Update Could Not Be Processed') . '<br>' . _('The GL account code selected does not exist in the database see the listing of GL Account Codes to ensure a valid account is selected'), 'error');
        } else {
            $GLActRow = DB_fetch_row($GLActResult);
            $GLAccountName = $GLActRow[0];
        }
    }

    include('PO_Chk_ShiptRef_JobRef.php');

    if (!isset($_POST['JobRef'])) {
        $_POST['JobRef']='';
    }
    $Narrative = $_POST['Narrative_' . $_SESSION['PO'.$identifier]->LineItems[$_POST['LineNo']]];
    if (isset($Narrative)) {
        $Narrative = $_POST['Narrative_' . $_SESSION['PO'.$identifier]->LineItems[$_POST['LineNo']]];
    } else {
        $Narrative = $POLine->Narrative;
    }
    
    $Justification = $_POST['Justification_' . $_SESSION['PO'.$identifier]->LineItems[$_POST['LineNo']]];
    if (isset($Justification)) {
            $Justification = $_POST['Justification_' . $_SESSION['PO'.$identifier]->LineItems[$_POST['LineNo']]];
    } else {
            $Justification = $POLine->Justification;
    }
    
    
    if ($AllowUpdate == true) {
        $fechaentrega = add_ceros(rtrim($_POST['diafechaentrega'.$_POST['LineNo']]), 2)."/".add_ceros(rtrim($_POST['mesfechaentrega'.$_POST['LineNo']]), 2)."/".rtrim($_POST['aniofechaentrega'.$_POST['LineNo']]);
        
        $_SESSION['PO'.$identifier]->update_order_item(
            $_POST['LineNo'],
            $_POST['Qty'],
            $price,
            $_POST['ItemDescription'],
            $_POST['GLCode'],
            $GLAccountName,
            $fechaentrega,
            $_POST['ShiptRef'],
            $_POST['JobRef'],
            $_POST['itemno'],
            $_SESSION['PO'.$identifier]->LineItems[$_POST['LineNo']]->uom,
            $_POST['suppliers_partno'],
            $_POST['Qty']*$_POST['Price'],
            $_POST['package'],
            $_POST['pcunit'],
            $_POST['nw'],
            $_POST['gw'],
            $_POST['cuft'],
            $_POST['Qty'],
            $_POST['Qty']*$_POST['Price'],
            $Narrative,
            $Justification,
            $_POST["clavepresupuestal_".$_SESSION['PO'.$identifier]->LineNo]
        );

        include('includes/PO_UnsetFormVbls.php');
    }
}

/*Inputs from the form directly without selecting a stock item from the search */
if (isset($_POST['EnterLine'])) {
    $AllowUpdate = true; /*always assume the best */

    if (!is_numeric($_POST['Qty'])) {
        $AllowUpdate = false;
        prnMsg(_('Cannot Enter this order line') . '<br>' . _('The quantity of the order item must be numeric'), 'error');
    }
    
    if ($_POST['Qty']<0) {
        $AllowUpdate = false;
        prnMsg(_('Cannot Enter this order line') . '<br>' . _('The quantity of the ordered item entered must be a positive amount'), 'error');
    }
    
    if (!is_numeric($_POST['Price'])) {
        $AllowUpdate = false;
        prnMsg(_('Cannot Enter this order line') . '<br>' . _('The price entered must be numeric'), 'error');
    }
    
    if (!is_date($_POST['ReqDelDate'])) {
        $AllowUpdate = false;
        prnMsg(_('Cannot Enter this order line') . '</b><br>' . _('The date entered must be in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
    }


    /*Then its not a stock item */
    /*need to check GL Code is valid if GLLink is active */
    if ($_SESSION['PO'.$identifier]->GLLink==1) {
        $sql = "SELECT accountname 
                        FROM chartmaster 
                        WHERE accountcode ='" . $_POST['GLCode'] ."'";
        
        $ErrMsg =  _('The account details for') . ' ' . $_POST['GLCode'] . ' ' . _('could not be retrieved because');
        $DbgMsg =  _('The SQL used to retrieve the details of the account, but failed was');
        $GLValidResult = DB_query($sql, $db, $ErrMsg, $DbgMsg, false, false);
        
        if (DB_error_no($db) !=0) {
            $AllowUpdate = false;
            prnMsg(_('The validation process for the GL Code entered could not be executed because') . ' ' . DB_error_msg($db), 'error');
            
            if ($debug==1) {
                prnMsg(_('The SQL used to validate the code entered was') . ' ' . $sql, 'error');
            }
            
            include('includes/footer_Index.inc');
            exit;
        }
        
        if (DB_num_rows($GLValidResult) == 0) { /*The GLCode entered does not exist */
            $AllowUpdate = false;
            prnMsg(_('Cannot enter this order line') . ':<br>' . _('The general ledger code') . ' - ' . $_POST['GLCode'] . ' ' . _('is not a general ledger code that is defined in the chart of accounts') . ' . ' . _('Please use a code that is already defined') . '. ' . _('See the Chart list from the link below'), 'error');
        } else {
            $myrow = DB_fetch_row($GLValidResult);
            $GLAccountName = $myrow[0];
        }
    } /* dont bother checking the GL Code if there is no GL code to check ie not linked to GL */
    else {
        $_POST['GLCode']=0;
    }
    
    if (strlen($_POST['ItemDescription'])<=3) {
        $AllowUpdate = false;
        prnMsg(_('Cannot enter this order line') . ':<br>' . _('The description of the item being purchase is required where a non-stock item is being ordered'), 'warn');
    }

    if ($AllowUpdate == true) {
        $fechaentrega = date("d/m/Y");

        $_SESSION['PO'.$identifier]->add_to_order(
            $_SESSION['PO'.$identifier]->LinesOnOrder+1,
            '',
            0, /*Serialised */
            0, /*Controlled */
            $_POST['Qty'],
            $_POST['ItemDescription'],
            $_POST['Price'],
            $_POST['Desc1'],
            $_POST['Desc2'],
            $_POST['Desc3'],
            _('each'),
            $_POST['GLCode'],
            $fechaentrega,
            $_POST['ShiptRef'],
            $_POST['JobRef'],
            0,
            0,
            $GLAccountName,
            2,
            $_POST['itemno'],
            $_POST['uom'],
            $_POST['suppliers_partno'],
            $_POST['subtotal_amount'],
            $_POST['package'],
            $_POST['pcunit'],
            $_POST['nw'],
            $_POST['gw'],
            $_POST['cuft'],
            $_POST['total_quantity'],
            $_POST['total_amount'],
            '',
            '',
            '',
            0,
            0,
            0,
            $_POST['estimated_cost'],
            $_POST['clavepresupuestal_'.$_SESSION['PO'.$identifier]->LineNo]
        );
        include('includes/PO_UnsetFormVbls.php');
    }
}

if (isset($_POST['NewItem'])) {
    $sql ='select legalid FROM tags,locations where tags.tagref=locations.tagref and loccode="'.$_SESSION['PO'.$identifier]->Location.'"';
    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
    $myrowloc = DB_fetch_array($result);
    $legalid = $myrowloc['legalid'];
    
    foreach ($_POST as $key => $value) {
        if (substr($key, 0, 3)=='qty') {
            $ItemCode=substr($key, 3, strlen($key)-3);
            $Quantity=$value;
            $AlreadyOnThisOrder =0;
        
            $NewPrecio = $_POST['price'.$ItemCode];
            $NewDesc1 = $_POST['desc1'.$ItemCode];
            $NewDesc2 = $_POST['desc2'.$ItemCode];
            $NewDesc3 = $_POST['desc3'.$ItemCode];
            $NewEstimatedCost = $_POST['estimated_cost'.$ItemCode];
            
            $NewDescXmonto = $_POST['descmonto'.$ItemCode];
            
            if (is_numeric($NewDescXmonto)) {
                            $NewDesc1 = $NewDescXmonto/$NewPrecio*100;
                            $NewDesc2 = 0;
                            $NewDesc3 = 0;

                            $_POST['desc1'.$ItemCode] = $NewDesc1;
                            $_POST['desc2'.$ItemCode] = $NewDesc2;
                            $_POST['desc3'.$ItemCode] = $NewDesc3;
            }

            if ($_SESSION['PO_AllowSameItemMultipleTimes'] ==false) {
                if (count($_SESSION['PO'.$identifier]->LineItems)!=0) {
                    foreach ($_SESSION['PO'.$identifier]->LineItems as $OrderItem) {
                       /* do a loop round the items on the order to see that the item
                        is not already on this order */
                        if (($OrderItem->StockID == $ItemCode) and ($OrderItem->Deleted==false)) {
                                $AlreadyOnThisOrder = 1;
                                prnMsg(_('The item') . ' ' . $ItemCode . ' ' . _('is already on this order') . '. ' . _('The system will not allow the same item on the order more than once') . '. ' . _('However you can change the quantity ordered of the existing line if necessary'), 'error');
                        }
                    } /* end of the foreach loop to look for preexisting items of the same code */
                }
            }
            
            if ($AlreadyOnThisOrder!=1 and $Quantity>0) {
                $purchdatasql='SELECT COUNT(supplierno)
                                FROM purchdata
                                WHERE purchdata.supplierno = "' . $_SESSION['PO'.$identifier]->SupplierID . '"
                                AND purchdata.stockid="'. $ItemCode . '"';
                                
                $purchdataresult=DB_query($purchdatasql, $db);
                $myrow=DB_fetch_row($purchdataresult);
                                
                if ($myrow[0]>0) {
                    $sql = "SELECT stockmaster.description,
                        stockmaster.stockid,
                        stockmaster.units,
                        stockmaster.decimalplaces,
                        stockmaster.kgs,
                        stockmaster.netweight,
                        stockcategory.stockact,
                        chartmaster.accountname,
                        purchdata.price,
                        purchdata.supplierno,
                        purchdata.conversionfactor,             
                        purchdata.supplierdescription,
                        purchdata.suppliersuom,
                        purchdata.suppliers_partno,
                        purchdata.leadtime,
                        stockmaster.serialised,
                        stockmaster.controlled,
                        stockmaster.manufacturer,
                        stockmaster.barcode
                        
                    FROM stockcategory,
                        chartmaster,
                        stockmaster LEFT JOIN purchdata
                    ON stockmaster.stockid = purchdata.stockid
                    AND purchdata.supplierno = '" . $_SESSION['PO'.$identifier]->SupplierID . "'
                    WHERE chartmaster.accountcode = stockcategory.stockact
                        AND stockcategory.categoryid = stockmaster.categoryid
                        AND (replace(replace(stockmaster.stockid,'.','_'),' ','_') ='". $ItemCode . "'
                                or stockmaster.stockid = '".$ItemCode."')
                        AND purchdata.effectivefrom = 
                            (SELECT max(effectivefrom) 
                                FROM purchdata 
                                WHERE  (replace(replace(purchdata.stockid,'.','_'),' ','_') ='". $ItemCode . "'
                                or purchdata.stockid = '".$ItemCode."')
                                AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "')";
                } else {
                    $sql='SELECT stockmaster.description,
                        stockmaster.stockid,
                        stockmaster.units,
                        stockmaster.decimalplaces,
                        stockmaster.kgs,
                        stockmaster.netweight,
                        stockcategory.stockact,
                        chartmaster.accountname,
                        stockmaster.serialised,
                        stockmaster.controlled,
                        stockmaster.manufacturer,
                        stockmaster.barcode
                    FROM stockcategory,
                        chartmaster,
                        stockmaster
                    WHERE chartmaster.accountcode = stockcategory.stockact
                        AND stockcategory.categoryid = stockmaster.categoryid
                        AND (replace(replace(stockmaster.stockid,".","_")," ","_") = "'. $ItemCode . '" OR stockmaster.stockid="'.$ItemCode.'")';
                }

                $ErrMsg = _('The supplier pricing details for') . ' ' . $ItemCode . ' ' . _('could not be retrieved because');
                $DbgMsg = _('The SQL used to retrieve the pricing details but failed was');
                //echo "<pre>$sql";
                $result1 = DB_query($sql, $db, $ErrMsg, $DbgMsg);

                if ($myrow = DB_fetch_array($result1)) {
                    $factordeConversion = $myrow['conversionfactor'];
                    if (!is_numeric($factordeConversion)) {
                        $factordeConversion = 1;
                    }
                    
                    if (is_numeric($myrow['price'])) {
                        $ItemCode=$myrow['stockid'];
                        //Si el precio lo capturo el usuario, nos vamos con este... si no tomamos
                        // el ultimo costo y despues el precio de datos de compra de este proveedor...
                        if ($NewPrecio == "" || $NewPrecio==0) {
                            $isLastCost = false;
                            
                            //buscar ultimo costo
                            $qry = "Select lastcost FROM stockcostsxlegalnew
                                    WHERE stockid='$ItemCode'
                                    AND legalid=$legalid";
                                                        
                            $rlast = DB_query($qry, $db);
                            if ($lastcost = DB_fetch_array($rlast)) {
                                                            $stkprice = $lastcost[0]*$factordeConversion;
                                if ($lastcost[0] > 0) {
                                    $isLastCost = true;
                                }
                            }
                            
                            // Obtener el costo de la ultima compra si viene por variable de configuracion ...
                            if (empty($_SESSION['GetLastCostFromPurchOrder']) == false) {
                                $sqlPo = "SELECT unitprice FROM purchorderdetails WHERE itemcode = '$ItemCode' ORDER BY podetailitem DESC LIMIT 1";
                                $rsPo = DB_query($sqlPo, $db);
                                if ($rowPo = DB_fetch_array($rsPo)) {
                                    $stkprice = $rowPo['unitprice'];
                                    $isLastCost = false;
                                }
                            }
                                
                            if ($stkprice == 0 || $stkprice=="") {
                                $stkprice = $myrow['price'];
                            }
                                    
                            # ############################################ #
                            # CAMBIOS DE ACUERDO A LA MONEDA DEL PROVEEDOR #
                            # ############################################ #
                            
                            // Dejar por defecto la moneda que tiene la orden de compra en la moneda del proveedor
                            $suppCurrcode = $_SESSION['PO'.$identifier]->CurrCode;
                            // Obtenemos la moneda del proveedor preferente
                            $sqlSupp = "SELECT currcode FROM suppliers WHERE supplierid = '{$myrow['supplierno']}'";
                            $rsSupp = DB_query($sqlSupp, $db);
                            if ($rowSupp = DB_fetch_array($rsSupp)) {
                                if (empty($rowSupp['currcode']) == false) {
                                    $suppCurrcode = $rowSupp['currcode'];
                                }
                            }
                                
                                                            // Si el precio viene del ultimo costo calcular de esta forma
                            if ($isLastCost) {
                                $tc = 1 / $_SESSION['PO'.$identifier]->ExRate;
                                $stkprice = $stkprice / $tc;
                            } else {
                                // Si viene el precio de los datos de compra
                                // Si la moneda de la orden de compra no es pesos mexicanos y la moneda del
                                // provedor es pesos mexicanos hacer la conversion por division
                                if ($_SESSION['PO'.$identifier]->CurrCode != 'MXN' && $suppCurrcode == 'MXN') {
                                    $tc = 1 / $_SESSION['PO'.$identifier]->ExRate;
                                    $stkprice = $stkprice / $tc;
                                }
                                
                                // Si la moneda de la orden de compra es pesos mexicanos y la moneda del
                                // provedor no es pesos mexicanos hacer la conversion por multiplicacion
                                if ($_SESSION['PO'.$identifier]->CurrCode == 'MXN' && $suppCurrcode != 'MXN') {
                                    $rateSupp = $_SESSION['PO'.$identifier]->ExRate;
                                    $sql = "SELECT rate FROM tipocambio WHERE currency = '$suppCurrcode' ORDER BY fecha DESC LIMIT 1";
                                    $rsTc = DB_query($sql, $db);
                                    if ($rowTc = DB_fetch_array($rsTc)) {
                                        $rateSupp = $rowTc['rate'];
                                    }
                                
                                    $tc = 1 / $rateSupp;
                                    $stkprice = $stkprice * $tc;
                                }
                            }
                        } else {
                            $stkprice = $NewPrecio;
                        }
                        
                        // AGREGAR EL DESCUENTO Y % DE DEVOLUCION
                        // verifica el porcetaje de devolucion
                        
                        $percentdevolucion=TraePercentDevXSupplier($_SESSION['PO'.$identifier]->SupplierID, $ItemCode, $myrow['manufacturer'], $_SESSION['PO'.$identifier]->DefaultSalesType, $db);
                        
                        $separa = explode('|', $percentdevolucion);
                        $Devolucion = $separa[0]*100;
                        $Discount=$separa[1]*100;
                        $totalpurch=$separa[2];

                        $fechaentrega = date("d/m/Y");
                        
                        $_SESSION['PO'.$identifier]->add_to_order(
                            $_SESSION['PO'.$identifier]->LinesOnOrder+1,
                            $ItemCode,
                            $myrow['serialised'], /*Serialised */
                            $myrow['controlled'], /*Controlled */
                            $Quantity, /* Qty */
                            $myrow['description'],
                            $stkprice,
                            $Discount,
                            0,
                            0,
                            $myrow['units'],
                            $myrow['stockact'],
                            $fechaentrega,
                            0,
                            0,
                            0,
                            0,
                            0,
                            $myrow['accountname'],
                            $myrow['decimalplaces'],
                            $ItemCode,
                            $myrow['suppliersuom'],
                            $myrow['suppliers_partno'],
                            $Quantity*$stkprice,
                            $myrow['leadtime'],
                            '',
                            $myrow['netweight'],
                            $myrow['kgs'],
                            '',
                            $Quantity,
                            $Quantity*$stkprice,
                            '',
                            '',
                            $myrow['barcode'],
                            $Devolucion,
                            $totalpurch,
                            0,
                            0,
                            $_POST['clavepresupuestal_'.$_SESSION['PO'.$identifier]->LineNo]
                        );
                    } else { /*There was no supplier purchasing data for the item selected so enter a purchase order line with zero price */
                        // AGREGAR EL DESCUENTO Y % DE DEVOLUCION
                        // verifica el porcetaje de devolucion
                        $ItemCode=$myrow['stockid'];
                        $percentdevolucion=TraePercentDevXSupplier($_SESSION['PO'.$identifier]->SupplierID, $ItemCode, $myrow['manufacturer'], $_SESSION['PO'.$identifier]->DefaultSalesType, $db);
                        //echo $percentdevolucion;
                        //echo 'entra'.$percentdevolucion;
                        $separa = explode('|', $percentdevolucion);
                        $Devolucion = $separa[0]*100;
                        $Discount=$separa[1]*100;
                        $totalsale=$separa[2];
                        
                        $fechaentrega = date("d/m/Y");
                        
                        if ($NewPrecio=="" || $NewPrecio==0) {
                            //buscar ultimo costo
                            $qry = "Select lastcost FROM stockcostsxlegal
                                    WHERE stockid='$ItemCode'
                                    and legalid=$legalid
                                    ";
                            $rlast = DB_query($qry, $db);
                            $lastcost = DB_fetch_array($rlast);
                            $NewPrecio = $lastcost[0];
                        }
                        
                        // Obtener el costo de la ultima compra si viene por variable de configuracion ...
                        if (empty($_SESSION['GetLastCostFromPurchOrder']) == false) {
                            $sqlPo = "SELECT unitprice FROM purchorderdetails WHERE itemcode = '$ItemCode' ORDER BY podetailitem DESC LIMIT 1";
                            $rsPo = DB_query($sqlPo, $db);
                            if ($rowPo = DB_fetch_array($rsPo)) {
                                $NewPrecio = $rowPo['unitprice'];
                            }
                        }
                        
                        $_SESSION['PO'.$identifier]->add_to_order(
                            $_SESSION['PO'.$identifier]->LinesOnOrder+1,
                            $ItemCode,
                            $myrow['serialised'], /*Serialised */
                            $myrow['controlled'], /*Controlled */
                            $Quantity, /* Qty */
                            $myrow['description'],
                            $NewPrecio,
                            $NewDesc1,
                            $NewDesc2,
                            $NewDesc3,
                            $myrow['units'],
                            $myrow['stockact'],
                            $fechaentrega,
                            $Discount,
                            0,
                            0,
                            0,
                            0,
                            $myrow['accountname'],
                            $myrow['decimalplaces'],
                            $ItemCode,
                            '',
                            '',
                            0,
                            0,
                            '',
                            0,
                            0,
                            0,
                            $NewPrecio,
                            $NewPrecio*$Quantity,
                            '',
                            '',
                            $myrow['barcode'],
                            $Devolucion,
                            $totalpurch,
                            0,
                            $NewEstimatedCost,
                            $_POST['clavepresupuestal_'.$_SESSION['PO'.$identifier]->LineNo]
                        );
                    }
                } else {
                    prnMsg(_('El elemento') . ' ' . $ItemCode . ' ' . _('no existe en la base de datos, verifique.'), 'error');
                    if ($debug==1) {
                        echo "<br>".$sql;
                    }

                    include('includes/footer_Index.inc');
                    
                    exit;
                }
            }
        }
    }
}

$enc = new Encryption;
$url = "&identifier=>" . $identifier;
$url = $enc->encode($url);
$liga= "URL=" . $url;

echo "<form name=form1 action='" . $_SERVER['PHP_SELF'] . "?" . $liga . "' method=post>";
echo "<input type='hidden' name=supplierid value='".$supplierid."'>";
echo "<input type=hidden name='TieToOrderNumber' value=".$TieToOrderNumber.">";

$SQL = "SELECT supplierid, suppname, taxid, address1, address2, address3, address4,p.terms, currcode,address5
    FROM suppliers s, paymentterms p
    WHERE p.termsindicator=s.paymentterms AND supplierid='" . $supplierid . "'";
$ErrMsg = _('The Supplier name requested cannot be retrieved because');
$result = DB_query($SQL, $db, $ErrMsg);

if ($myrow=DB_fetch_row($result)) {
        $SuppName = $myrow[1];
        $SupplierName = $myrow[0].' - '.$myrow[2]." - ".$myrow[1];
        $address = $myrow[2].' '.$myrow[3].' '.$myrow[4].' '.$myrow[5];
            
    if ($myrow[8]!="") {
        $address.="  CP ".$myrow[8];
    }

    $terminospago = $myrow[7];
    $moneda = $myrow[8];
}

// $orderGetParam = "&panelCompraGen=1";
// if (empty($_SESSION['PO'.$identifier]->OrderNo) == false) {
//     $orderGetParam .= "&ModifyOrderNumber=" . $_SESSION['PO'.$identifier]->OrderNo;
// }
//     echo '<table cellpadding=2 border=0 width="80%" style="border-collapse: collapse; border-color:lightgray;">';
//     echo '<tr>';
//     echo '<td colspan=2 style="text-align:center; border:inset 0pt">
//         <a href="PO_Header.php?' . SID . '&back=1&identifier='. $identifier . $orderGetParam . '"><img src="images/b_regresar_25.png" height=20 title="REGRESAR AL ENCABEZADO DE LA ORDEN DE COMPRA"></a>
//         </td>';
//     echo '<td colspan=2  style="text-align:center; border:inset 0pt">
//     <a href="' . $rootpath . '/PO_Header.php?' .SID . '&NewOrder=Yes"><img src="images/nueva_or_25.png" title="NUEVA ORDEN DE COMPRA"></a>
//     </td>';
//     echo'</tr>';
//     echo '<tr><td>&nbsp;</td></tr>';
//     echo '</table>';

    /*echo "<tr>";
    echo '<td colspan=2 style="text-align:center">
            <a href="SelectSupplierForPurchOrder.php?' . SID . '&OCprov=1&identifier='. $identifier. $orderGetParam .'&supplierid='.$supplierid.'">Modificar el proveedor</a>
        </td>';
    echo "</tr>";
    */

    echo '<div class="panel panel-default pull-right col-lg-12 col-md-12 col-sm-12 p0 m0">
            <div class="panel-heading" role="tab" id="headingOne">
              <h4 class="panel-title row">
                <div class="col-md-6 col-xs-6 text-left">
                  <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelInfoCompra" aria-expanded="true" aria-controls="collapseOne">
                    <b>Información de la Compra</b>
                  </a>
                </div>
              </h4>
            </div>
            <div id="PanelInfoCompra" name="PanelInfoCompra" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel-body">';
    ?>
    <div class="col-md-6 col-xs-12">
        <component-label-text label="Folio de Requisición:" id="txtOfAuto" name="txtOfAuto" value="<?php echo $_SESSION['PO'.$identifier]->RequisitionNo; ?>"></component-label-text>
        <!-- <component-label-text label="Dependencia:" id="txtNombreDependencia" name="txtNombreDependencia" value="<?php echo $_SESSION['PO'.$identifier]->legalname; ?>"></component-label-text> -->
        <component-label-text label="UR:" id="txtNombreUR" name="txtNombreUR" value="<?php echo $_SESSION['PO'.$identifier]->tagname; ?>"></component-label-text>
        <?php
        echo '<component-label-text style="display: none;" label="Fecha Requerida2:" id="txtFechaReq" name="txtFechaReq" value="'.$_SESSION['PO'.$identifier]->Orig_OrderDate.'"></component-label-text>';
        echo "<input type='hidden' name='FromDia' value='" . $FromDia . "'>";
        echo "<input type='hidden' name='FromMes' value='" . $FromMes . "'>";
        echo "<input type='hidden' name='FromAnio' value='" . $FromYear . "'>";

        $txtFechaRequerida = date_create($_SESSION['PO'.$identifier]->Orig_OrderDate);
        $txtFechaRequerida = date_format($txtFechaRequerida, 'd-m-Y');
        ?>
        <component-label-text label="Total Suficiencia: " id="txtTotalSuficiencia" name="txtTotalSuficiencia" value="<?php echo '$ '.number_format($_SESSION['PO'.$identifier]->totalSuficiencia, 2); ?>"></component-label-text>
        <component-date-label id="txtFechaRequerida" name="txtFechaRequerida" label="Fecha Requerida: " value="<?php echo $txtFechaRequerida; ?>" style="width: 100%;"></component-date-label>
    </div>
    <?php

    $url = "&panelCompraGen=>1&back=>1&identifier=>".$identifier;
    if (empty($_SESSION['PO'.$identifier]->OrderNo) == false) {
        $url .= "&ModifyOrderNumber=>" . $_SESSION['PO'.$identifier]->OrderNo;
    }
    $enc = new Encryption;
    $url = $enc->encode($url);
    $liga= "URL=" . $url;
?>  
    <!-- <div class="col-md-1">
        <a class="btn btn-default botonVerde glyphicon glyphicon-retweet" href="<?php echo 'PO_Header.php?'.$liga; ?>" title="Seleccionar Proveedor"></a>
    </div> -->
    <div class="col-md-6 col-xs-12">
        <div class="form-inline row">
          <div class="col-md-3">
              <span><label>Proveedor Orden Compra: </label></span>
          </div>
          <div class="col-md-9">
              <select id="selectProveedorCambiar" name="selectProveedorCambiar" class="form-control selectProveedorCambiar"><?php /* ?>
                <option value="-1">Seleccionar...</option>
                <?php */
                $qry = "SELECT DISTINCT `supplierid`, CONCAT(`supplierid`, ' - ' , `suppname`) as `suppname` FROM `suppliers` WHERE `supplierid` = '".$_SESSION['PO'.$identifier]->SupplierID."'";
                $rscurr = DB_query($qry, $db);
                while ($rowcurr = DB_fetch_array($rscurr)) {
                    if ($rowcurr['supplierid'] == $_SESSION['PO'.$identifier]->SupplierID) {
                        echo "<option selected value='".$rowcurr['supplierid']."'>".$rowcurr['suppname']."</option>";
                    } else {
                        echo "<option value='".$rowcurr['supplierid']."'>".$rowcurr['suppname']."</option>";
                    }
                }
                ?>
              </select>
          </div>
      </div>
      
        <?php
        $ocultarAlmancen = ' style="display: none;" ';
        if ($traeBienes == 1) {
            // Si tine bienes mostrar almacen
            $ocultarAlmancen = '';
        }
        ?>
      <div class="form-inline row" <?php echo $ocultarAlmancen; ?>>
          <div class="col-md-3">
              <span><label>Almacén Orden Compra: </label></span>
          </div>
          <div class="col-md-9">
              <select id="selectAlmacenOrdenCompra" name="selectAlmacenOrdenCompra" class="form-control selectAlmacenOrdenCompra">
                <?php
                if ($traeBienes == 1) {
                    // Si tine bienes mostrar almacen
                    $qry = "SELECT locations.loccode, CONCAT(locations.loccode,' - ',locations.locationname) as locationname
                    FROM locations, sec_loccxusser
                    WHERE 
                    locations.loccode=sec_loccxusser.loccode 
                    AND sec_loccxusser.userid='" . $_SESSION['UserID'] . "'
                    AND locations.tagref = '".$_SESSION['PO'.$identifier]->tag."'
                    ORDER BY locationname";
                    $rscurr = DB_query($qry, $db);
                    while ($rowcurr = DB_fetch_array($rscurr)) {
                        $urSinAlmacen = 0;
                        if ($rowcurr['loccode'] == $_SESSION['PO'.$identifier]->Location) {
                            echo "<option selected value='".$rowcurr['loccode']."'>".$rowcurr['locationname']."</option>";
                        } else {
                            echo "<option value='".$rowcurr['loccode']."'>".$rowcurr['locationname']."</option>";
                        }
                    }
                } else {
                    $urSinAlmacen = 0;
                    echo "<option selected value='".$_SESSION['PO'.$identifier]->Location."'>".$_SESSION['PO'.$identifier]->Location."</option>";
                }
                ?>
              </select>
          </div>
      </div>
      <!-- <component-button type="submit" id="UpdateLines" name="UpdateLines" value="Actualizar Información" class="glyphicon glyphicon-refresh"></component-button> -->
    </div>
    <?php

    if (empty($almacen)) {
        $ssql='SELECT locationname FROM locations WHERE loccode="'.$_SESSION['PO'.$identifier]->Location.'"';
        $result = DB_query($ssql, $db, $ErrMsg);
        $myrow = DB_fetch_array($result);
        $almacen = $myrow["locationname"];
    }

    $url = "&back=>1&identifier=>".$identifier;
    if (empty($_SESSION['PO'.$identifier]->OrderNo) == false) {
        $url .= "&ModifyOrderNumber=>" . $_SESSION['PO'.$identifier]->OrderNo;
    }

    $enc = new Encryption;
    $url = $enc->encode($url);
    $liga= "URL=" . $url;

    // echo '<div class="col-md-1">';
    //     echo '<a class="btn btn-default botonVerde glyphicon glyphicon-retweet" href="PO_Header.php?'.$liga.'" title="Seleccionar Almacén"></a>';
    // echo '</div>';

    ?>
    <div class="row"></div>
    
    <div class="col-md-4 col-xs-12"></div>
    <div class="col-md-4 col-xs-12"></div>
    <div class="row"></div>

    <div class="col-md-3 hidden">
        <component-label-text label="Términos de pago:" id="txtDir" name="txtDir" value="<?php echo $terminospago; ?>"></component-label-text>
    </div>
    <div class="col-md-4 hidden">
        <component-label-text label="Dirección:" id="txtDir" name="txtDir" value="<?php echo $address; ?>"></component-label-text>
    </div>
    <?php


    $consulta = "SELECT currencydefault FROM companies WHERE currencydefault='".$_SESSION["CountryOfOperation"]."' LIMIT 1";
    $resultado = DB_query($consulta, $db);
    $mostrarmoneda = "";
    if (DB_num_rows($resultado) != 0) {
        $mostrarmoneda= 'style="display: none;"'; // display:none;
    }

    echo '<div class="col-md-4" '.$mostrarmoneda.'>
            <div class="form-inline row">
              <div class="col-md-3" style="vertical-align: middle;">
                  <span><label>Moneda: </label></span>
              </div>
              <div class="col-md-9">
                <select name="selMoneda" id="selMoneda" class="selMoneda">';
    $qry = "select currabrev,currency from currencies";
    $rscurr = DB_query($qry, $db);
        
    while ($rowcurr = DB_fetch_array($rscurr)) {
        if ($rowcurr['currabrev'] == $_SESSION['PO'.$identifier]->CurrCode) {
            echo "<option selected value='".$rowcurr['currabrev']."'>".$rowcurr['currency']."</option>";
        } else {
            echo "<option value='".$rowcurr['currabrev']."'>".$rowcurr['currency']."</option>";
        }
    }
            echo '</select>';
        echo "</div>";
    echo '</div>
        </div>';
    echo '<div class="col-md-4" '.$mostrarmoneda.'>';
        echo '<component-button type="submit" id="btnMoneda" name="btnMoneda" value="Cambiar" class=""></component-button>';
    echo '</div>';
    echo '<div class="col-md-4" '.$mostrarmoneda.'>';
        echo '<component-text-label label="TC:" id="tcmanual" name="tcmanual" value="'.number_format(1/$_SESSION['PO'.$identifier]->ExRate, $decimalesTipoCambio).'"></component-text-label>';
    echo '</div>';

    $ssql='SELECT sum(ovamount+ovgst-alloc) as saldo FROM supptrans WHERE supplierno="'.$supplierid.'"';
    $result = DB_query($ssql, $db, $ErrMsg);
    $myrow = DB_fetch_array($result);
    $saldo = $myrow['saldo'];
    echo '<div class="row"></div>';
    echo '<div class="col-md-4" style="display: none;">';
        echo '<component-label-text label="Saldo:" id="txtSaldo" name="txtSaldo" value="'.number_format($saldo, 2).'"></component-label-text>';
    echo '</div>';

    $ssql='SELECT locationname FROM locations WHERE loccode="'.$_SESSION['PO'.$identifier]->Location.'"';
    $result = DB_query($ssql, $db, $ErrMsg);
    $myrow = DB_fetch_array($result);
    $almacen = $myrow[0];
    
    $qry = "SELECT distinct customs, pedimento, dateship, datecustoms, inputport, noag_ad
            FROM purchorderdetails 
            inner join purchorders on purchorders.orderno=purchorderdetails.orderno
            WHERE purchorders.orderno = '".$_SESSION['PO'.$identifier]->OrderNo."'";
    $rs = DB_Query($qry, $db);
    $rows = DB_fetch_array($rs);
    if (!isset($_POST['aduana'])) {
        $_POST['aduana'] = $rows['customs'];
    }
    if (!isset($_POST['pedimento'])) {
        $_POST['pedimento'] = $rows['pedimento'];
    }
    if (!isset($_POST['inputport'])) {
        $_POST['inputport'] = $rows['inputport'];
    }
    if (!isset($_POST['noag_ad'])) {
        $_POST['noag_ad'] = $rows['noag_ad'];
    }

    $fechaembarque = $rows['dateship'];
    if (!isset($_POST['DiaEmb'])) {
        if ($fechaembarque!="") {
            $arrfecha = explode("-", $fechaembarque);
            $_POST['DiaEmb'] = $arrfecha[2];
            $_POST['MesEmb'] = $arrfecha[1];
            $_POST['AnioEmb'] = $arrfecha[0];
        }
    }
    $fechaaduana = $rows['datecustoms'];
    if (!isset($_POST['DiaFechaAduana'])) {
        if ($fechaaduana!="") {
            $arrfecha = explode("-", $fechaaduana);
            $_POST['DiaFechaAduana'] = $arrfecha[2];
            $_POST['MesFechaAduana'] = $arrfecha[1];
            $_POST['AnioFechaAduana'] = $arrfecha[0];
        }
    }

    echo '<div class="row"></div>';

    $sql = "SELECT service_type_id, service_type FROM purchorderservicetypes";
    $result = DB_query($sql, $db);
    if (DB_fetch_array($result)>0) {
        echo '<div class="col-md-4">
                <div class="form-inline row">
                  <div class="col-md-3" style="vertical-align: middle;">
                      <span><label>Tipo de Servicio: </label></span>
                  </div>
                  <div class="col-md-9">
                    <select name="servicetype" id="servicetype" class="servicetype">';
        while ($row = DB_fetch_array($result)) {
            $serviceTypeId = $_SESSION['PO'.$identifier]->ServiceType;
            if ($row['service_type_id'] === $serviceTypeId) {
                echo "<option selected='selected' value='{$row['service_type_id']}'>{$row['service_type']}</option>";
            } else {
                echo "<option value='{$row['service_type_id']}'>{$row['service_type']}</option>";
            }
        }
                echo '</select>';
            echo "</div>";
        echo '</div>
            </div>';
    }

    echo '<div class="col-md-4" style="display: none;">
            <div class="form-inline row">
              <div class="col-md-3" style="vertical-align: middle;">
                  <span><label>Recibir Orden de Compra: </label></span>
              </div>
              <div class="col-md-9">
                <select name="recibOCProd" id="recibOCProd" class="recibOCProd">';
                //echo "<option value=2>"._("Si")."</option>";
    if (!isset($_POST['recibOCProd']) or $_POST['recibOCProd'] == "") {
        $_POST['recibOCProd'] = 1;
    }
    if ($_POST['recibOCProd'] == 1) {
        echo "<option value=2>"._("Si")."</option>";
        echo "<option selected value=1>"._("No")."</option>";
    } elseif ($_POST['recibOCProd'] == 2) {
        echo "<option selected value=2>"._("Si")."</option>";
        echo "<option value=1>"._("No")."</option>";
    }
            echo '</select>';
        echo "</div>";
    echo '</div>
        </div>';

    if (Havepermission($_SESSION['UserID'], 1440, $db) == 1) {
        echo '<div class="row"></div>';
        echo '<div class="col-md-4">';
            echo '<component-text-label label="No Guía: " id="pedimento" name="pedimento" value="'.$_POST['pedimento'].'"></component-text-label>';
        echo '</div>';

        $DiaEmb = $_POST['DiaEmb'];
        if ($DiaEmb == "") {
            $DiaEmb = date('d');
        }
        $MesEmb = $_POST['MesEmb'];
        if ($MesEmb == "") {
            $MesEmb = date('m');
        }
            
        $AnioEmb = $_POST['AnioEmb'];
        if ($AnioEmb == "") {
            $AnioEmb = date('Y');
        }

        echo '<div class="col-md-1">';
        echo '<label>Fecha Envío:</label>';
        echo '</div>';
        echo '<div class="col-md-1">';
        echo "<select Name='DiaEmb' id='DiaEmb' class='DiaEmb'>";
        $sql = "SELECT * FROM cat_Days";
        $Todias = DB_query($sql, $db);
        while ($myrowTodia=DB_fetch_array($Todias, $db)) {
            $Todiabase=$myrowTodia['DiaId'];
            if (rtrim(intval($DiaEmb))==rtrim(intval($Todiabase))) {
                echo "<option  VALUE='" . $myrowTodia['Dia'] .  "' selected>" .$myrowTodia['Dia'] . '</option>';
            } else {
                echo "<option  VALUE='" . $myrowTodia['Dia'] .  "'>" .$myrowTodia['Dia'] . '</option>';
            }
        }
        echo "</select>";
        echo '</div>';
        echo '<div class="col-md-1">';
        echo "<select Name='MesEmb' id='MesEmb' class='MesEmb'>";
        $sql = "SELECT * FROM cat_Months";
        $ToMeses = DB_query($sql, $db);
        while ($myrowToMes=DB_fetch_array($ToMeses, $db)) {
            $ToMesbase=$myrowToMes['u_mes'];
            if (rtrim(intval($MesEmb))==rtrim(intval($ToMesbase))) {
                echo "<option  VALUE='" . $myrowToMes['u_mes'] .  "' selected>" . $myrowToMes['mes'] . "</option>";
            } else {
                echo "<option  VALUE='" . $myrowToMes['u_mes'] .  "'>" .$myrowToMes['mes'] . "</option>";
            }
        }
        echo "</select>";
        echo '</div>';
        echo '<div class="col-md-1">';
        echo '<component-text name="AnioEmb" id="AnioEmb" maxlength="4" value="'.$AnioEmb.'"></component-text>';
        echo '</div>';

        echo '<div class="col-md-4">';
            echo '<component-text-label label="Puerto entrada: " id="inputport" name="inputport" value="'.$_POST['inputport'].'"></component-text-label>';
        echo '</div>';

        echo '<div class="row"></div>';

        echo '<div class="col-md-4">';
            echo '<component-text-label label="Ref. Aduana: " id="aduana" name="aduana" value="'.$_POST['aduana'].'"></component-text-label>';
        echo '</div>';

        $DiaFechaAduana = $_POST['DiaFechaAduana'];
        if ($DiaFechaAduana == "") {
            $DiaFechaAduana = date('d');
        }
        $MesFechaAduana = $_POST['MesFechaAduana'];
        if ($MesFechaAduana == "") {
            $MesFechaAduana = date('m');
        }
        $AnioFechaAduana = $_POST['AnioFechaAduana'];
        if ($AnioFechaAduana == "") {
            $AnioFechaAduana = date('Y');
        }

        echo '<div class="col-md-1">';
        echo '<label>Fecha Arribo Aduana:</label>';
        echo '</div>';
        echo '<div class="col-md-1">';
        echo "<select Name='DiaFechaAduana' id='DiaFechaAduana' class='DiaFechaAduana'>";
        $sql = "SELECT * FROM cat_Days";
        $Todias = DB_query($sql, $db);
        while ($myrowTodia=DB_fetch_array($Todias, $db)) {
            $Todiabase=$myrowTodia['DiaId'];
            if (rtrim(intval($DiaFechaAduana))==rtrim(intval($Todiabase))) {
                echo "<option  VALUE='" . $myrowTodia['Dia'] .  "' selected>" .$myrowTodia['Dia'] . '</option>';
            } else {
                echo "<option  VALUE='" . $myrowTodia['Dia'] .  "'>" .$myrowTodia['Dia'] . '</option>';
            }
        }
        echo "</select>";
        echo '</div>';
        echo '<div class="col-md-1">';
        echo "<select Name='MesFechaAduana' id='MesFechaAduana' class='MesFechaAduana'>";
        $sql = "SELECT * FROM cat_Months";
        $ToMeses = DB_query($sql, $db);
        while ($myrowToMes=DB_fetch_array($ToMeses, $db)) {
            $ToMesbase=$myrowToMes['u_mes'];
            if (rtrim(intval($MesFechaAduana))==rtrim(intval($ToMesbase))) {
                echo "<option  VALUE='" . $myrowToMes['u_mes'] .  "' selected>" . $myrowToMes['mes'] . "</option>";
            } else {
                echo "<option  VALUE='" . $myrowToMes['u_mes'] .  "'>" .$myrowToMes['mes'] . "</option>";
            }
        }
        echo "</select>";
        echo '</div>';
        echo '<div class="col-md-1">';
        echo '<component-text name="AnioFechaAduana" id="AnioFechaAduana" maxlength="4" value="'.$AnioFechaAduana.'"></component-text>';
        echo '</div>';

        echo '<div class="col-md-4">
                <div class="form-inline row">
                  <div class="col-md-3" style="vertical-align: middle;">
                      <span><label>Agente Aduanal: </label></span>
                  </div>
                  <div class="col-md-9">
                    <select name="noag_ad" id="noag_ad" class="noag_ad">';
        $SQL = "SELECT  supplierid,suppname ";
        $SQL = $SQL .   " FROM suppliers where suppname<>'' and flagagentaduanal=1";
        $SQL = $SQL .   " ORDER BY suppname";
        $ErrMsg = _('No transactions were returned by the SQL because');
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        echo "<option selected value='0'>Sin Agente...</option>";
        while ($myrow=DB_fetch_array($TransResult)) {
            if ($myrow['supplierid'] == $_POST['noag_ad']) {
                echo "<option selected value='" . $myrow['supplierid'] . "'>" . $myrow['suppname'] . "</option>";
            } else {
                echo "<option value='" . $myrow['supplierid'] . "'>" . $myrow['suppname'] . "</option>";
            }
        }
                echo '</select>';
            echo "</div>";
        echo '</div>
            </div>';
    }

    echo '      </div>
            </div>
        </div>';
    
    // echo '<tr>';
    // if ($_SESSION['PO'.$identifier]->Wo>0) {
    //     echo '<td nowrap style="text-align:right"><b>' . _(' Ligada a Orden de Trabajo') . ':</b></td><td>' .   $_SESSION['PO'.$identifier]->Wo .' -> '.$_SESSION['PO'.$identifier]->WoDescription . '</td>';
    //     echo "<td colspan=2>&nbsp;</td>";
    // }
    // echo '</tr>';
            
    /*
    echo "<tr style='background-color: #F7F2E0;'>";
    echo '<td  style="text-align:right">';
    echo '<strong>' . _('Clave Presupuestal') . ':</strong></td>';
    echo "<td colspan=2><input type='text' name='clavepresupuestal' id='clavepresupuestal' value='".$_SESSION['PO'.$identifier]->ClavePresupuestal."' size='50' style='font-weight: bold;background-color: #FAFAFA;'></td>";
    echo "<td style='background-color: white;text-align:center;'><a href=\"javascript:openNewWindow('popup_apoc.php');\">Buscar Claves</a></td>";
    echo "</tr>";*/
                
    //echo '</td></tr>';

    //echo  _('Purchase Order') . ': <font color=BLUE size=4><b>' . $_SESSION['PO'.$identifier]->OrderNo . ' ' . $_SESSION['PO'.$identifier]->SupplierName . ' </b></font> - ' . _('All amounts stated in') . ' ' . $_SESSION['PO'.$identifier]->CurrCode . '<br>';

    if (count($_SESSION['PO'.$identifier]->LineItems)>0 and !isset($_GET['Edit'])) {
        if (isset($_SESSION['PO'.$identifier]->OrderNo)) {
            //echo '<div style="text-align:center"><b>' . _('Detalle de la Orden de Compra No.') .' '. $_SESSION['PO'.$identifier]->OrderNo . '</b></div><br />';
        
            if (Havepermission($_SESSION['UserID'], 714, $db) == 1) {
                // echo "<div style='text-align:center'><a href=\"javascript:newPopup('$rootpath/OrderCommentsLog.php?" . SID . "&orderno={$_SESSION['PO'.$identifier]->OrderNo}&from=purchpage')\">" . _('Historial Comentarios') . "</a></div>";
                // echo "<script type='text/javascript'>";
                // echo "function newPopup(url) {";
                // echo "popupWindow = window.open(url,'popUpWindow','height=650,width=500,left=10,top=10,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no,status=yes');";
                // echo "}";
                // echo "</script>";
            }
        }

        echo '<div class="row"></div>';

        echo "<div bgcolor align=center><br>";
        echo "<font size=w color='#A00000'><b> * Los precios son obligatorios *</font></div><br>";

        echo '<div class="table-responsive">';// Div Scroll style="width: 100%; overflow: scroll; font-size: 9px;"

        echo '<table class="table table-bordered table-condensed fts12" >'; // cellpadding=2 colspan=7 border=1 style="border-collapse: collapse; border-color:lightgray;"

        // <th style='display:none;'>" . _('Codigo Barra') . "</th>
        // <th style='display:none;'>" . _('Desc 2') . "</th>
        // <th style='display:none;'>" . _('Desc 3') . "</th>
        // <th>" . _('Desc 1 %') . "</th>
        // <th style='display:none;'>" . _('Costos Adicionales') . "</th>
        // <th style='display:none;'>" . _('Peso') . "</th>
        // <th style='display:none;'>" . _('% Dev.') . "</th>
        // <th style='display:none;'>" . _('F. Entrega') ."</th>

        $lineheader = "
        <tr class='header-verde'>
        <th style='text-align: center;'>" . _('Sel') . "</th>
        <th style='text-align: center;'>" . _('No') . "</th>
        <th style='text-align: center;'>" . _('Código') . "</th>
        <th style='text-align: center;'>" . _('Descripción') . "</th>
        <th style='text-align: center;'>" . _('Cantidad') . "</th>
        <th style='text-align: center;'>" . _('') . "</th>
        <th style='text-align: center;'>" . _('Precio') .' ('.$_SESSION['PO'.$identifier]->CurrCode.  ")</th>
        <th style='text-align: center;'>" . _('Subtotal') .' ('.$_SESSION['PO'.$identifier]->CurrCode.  ")</th>
        ";
        if ($permisoeliminarpartidas==1) {
            $lineheader=$lineheader."<th style='display:none;'>&nbsp;</th>";
        }
        $lineheader=$lineheader."</tr>";
        echo $lineheader;

        $_SESSION['PO'.$identifier]->total = 0;
        $k = 0;  //row colour counter
        $cont =0;
        $estatusEnPendiente = 0;
        $errorprecios=0;
    
        /* FIJA ESTAS COMO NO PERMITIDO EN UN INICIO */
        $AutCreate=0;
        $AutAuthorise=0;
        $AutCancell=0;
        $AutDeliver=0;
        $checkTotal = 0;
        $nolinea=0;

        $traeBienes = 0;
        $traeServicios = 0;
        
        // ciclo principal que reccore las lineas de detalle de compras
        foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
            $nolinea=$nolinea+1;

            if ($POLine->Deleted==false) {
                $cont = $cont + 1;

                if ($POLine->mbflag == 'B') {
                    // Tiene bienes
                    $traeBienes = 1;
                }
                if ($POLine->mbflag == 'D') {
                    // Tiene servicios
                    $traeServicios = 1;
                }
            
                $price = $POLine->Price;
            
                if ($oldcurrency != $_SESSION['PO'.$identifier]->CurrCode) {
                    if ($_SESSION['PO'.$identifier]->CurrCode == "MXN") {
                        $price *=$rateFromAnyToMXN;
                    } else {
                        $price *=$_SESSION['PO'.$identifier]->ExRate;
                    }
                    $price = round($price, 4);
                }
            
                $LineTotal = $POLine->Quantity * $price * (1 - ($POLine->Desc1/100));
                $LineTotal = $LineTotal * (1 - ($POLine->Desc2/100));
                $LineTotal = $LineTotal * (1 - ($POLine->Desc3/100));
                $LineTotal = $LineTotal; /*+ $POLine->estimated_cost;*/
            
                $checkTotal = $checkTotal + $LineTotal;
            
                $DisplayLineTotal = number_format($LineTotal, $POLine->DecimalPlaces);
                
                if ($price > 1) {
                    $DisplayPrice = number_format($price, 2, '.', '');
                } else {
                    $DisplayPrice = number_format($price, 2, '.', '');
                }
            
                /* CUADRAR CONTRA TOTAL ULTIMA PARTIDA */
                if (isset($_POST['cuadrar'])) {
                    if (sizeof($_SESSION['PO'.$identifier]->LineItems) == $cont) {
                        //Esta es la ultima partida
                        if ($_SESSION['PO'.$identifier]->total <> $_POST['totCuadre']) {
                            $diffCuadre = $_POST['totCuadre'] - $checkTotal;
                        
                            $diffEnPrecio = ($diffCuadre/$POLine->Quantity)/(1 - ($POLine->Desc1/100))/(1 - ($POLine->Desc2/100))/(1 - ($POLine->Desc3/100));
                            if ($price > 1) {
                                $DisplayPrice = number_format($price+$diffEnPrecio, 2, '.', '');
                            } else {
                                $DisplayPrice = number_format($price+$diffEnPrecio, 2, '.', '');
                            }
                        }
                    }
                }
            
                $DisplayDesc1 = number_format($POLine->Desc1, 2);
                $DisplayDesc2 = number_format($POLine->Desc2, 2);
                $DisplayDesc3 = number_format($POLine->Desc3, 2);
                $DisplayEstimatedCost = number_format($POLine->estimated_cost, 4, '.', '');
                $DisplayDev = number_format($POLine->Devolucion, 2);
            
                if ($DisplayPrice<=0) {
                    $errorprecios=1;
                }
            
                $DisplayQuantity = number_format($POLine->Quantity, $POLine->DecimalPlaces, '.', '');

                $suggestedPurchase = 0;
                
                if ($nolinea==10) {
                    echo $lineheader;
                    $nolinea=0;
                }
            
                $uomsql='SELECT stockupdate
                    FROM stockmaster
                    WHERE  stockid="'.$POLine->StockID.'"';
            
                $uomresult=DB_query($uomsql, $db);
                if (DB_num_rows($uomresult)>0) {
                    $uomrow=DB_fetch_array($uomresult);
                    if (strlen($uomrow['stockupdate'])>0) {
                        $stockupdate=$uomrow['stockupdate'];
                    } else {
                        $stockupdate=$POLine->Units;
                    }
                } else {
                    $stockupdate=0;
                }
            
                echo '<tr>';
            
                $uomsql='SELECT conversionfactor, suppliersuom, price
                    FROM purchdata
                    WHERE supplierno="'.$_SESSION['PO'.$identifier]->SupplierID.'"
                    AND stockid="'.$POLine->StockID.'"';

                $uomresult=DB_query($uomsql, $db);
                if (DB_num_rows($uomresult)>0) {
                    $uomrow=DB_fetch_array($uomresult);
                    if (strlen($uomrow['suppliersuom'])>0) {
                        $uom=$uomrow['suppliersuom'];
                    } else {
                        $uom=$POLine->Units;
                    }
                } else {
                    $uom=$POLine->Units;
                }
            
                $checkboxPartida = "";
                if ($_SESSION['ExistingPurchOrder'] != 0) {
                    //$checkboxPartida = "<input type='checkbox' name='partidas[]' value='$POLine->PODetailRec' />";
                }
                // echo "<br>PODetailRec: ".$POLine->PODetailRec;
                if ($_SESSION['PO'.$identifier]->separarOrdenCompra == 'P') {
                    $checkboxPartida = "<input type='checkbox' name='partidasProveedor[]' value='$POLine->PODetailRec' />";
                }
                $recibidas = "";
                $qry = "Select quantityrecd FROM purchorderdetails
                    WHERE orderno = '".$_SESSION['PO'.$identifier]->OrderNo."'
                    and itemcode = '".$POLine->StockID."'
                    ";
                $rsrec = DB_query($qry, $db);
                $rowrec = DB_fetch_array($rsrec);
                if ($rowrec['quantityrecd'] > 0) {
                    $recibidas = "<br>Rec: ".$rowrec['quantityrecd'];
                }
            
                // echo "<td>$POLine->StockID</td><td>$POLine->ItemDescription</td>td> align=right>$DisplayQuantity</td><td>$POLine->Units</td><td>$POLine->ReqDelDate</td>td> align=right>$DisplayPrice</td>td> align=right>$DisplayLineTotal</font></td><td><a href='" . $_SERVER['PHP_SELF'] . "?" . SID . "&Edit=" . $POLine->LineNo . "'>" . _('Select') . "</a></td></tr>";
                // <td style='display:none;'><b>$POLine->barcode</b></td>
                // <td nowrap><input type=text class='number form-control' name=Desc1$POLine->LineNo size=5 maxlength=8 value=".$DisplayDesc1."></td>
                // <td nowrap style='display:none;'><input type=text class=number name=Desc2$POLine->LineNo size=5 maxlength=8 value=".$DisplayDesc2.">%</td>
                // <td nowrap style='display:none;'><input type=text class=number name=Desc3$POLine->LineNo size=5 maxlength=8 value=".$DisplayDesc3.">%</td>
                // <td nowrap style='display:none;'><input type=text class=number name=estimated_cost$POLine->LineNo size=10 value=".$DisplayEstimatedCost."></td>
                // <td style='display:none;'><input type=text class=number name=nw$POLine->LineNo size=6 value=".$POLine->nw."></td>
                // <td style='display:none;' nowrap><input type=text class=number name=Dev$POLine->LineNo size=5 maxlength=8 value=".$DisplayDev.">%</td>
                echo "<td align='center'>" . $checkboxPartida . "</td> ";
                echo "<td align='center'>" . $POLine->LineNo . "</td> ";
                // echo "<td class='numero_normal'><a href='Stocks.php?" . SID . "&StockID=" . $POLine->StockID . "&frompage=PO_Header.php&ModifyOrderNumber=" . $_SESSION['PO'.$identifier]->OrderNo . "&PONumber=" . $_SESSION['PO'.$identifier]->OrderNo . "'>$POLine->StockID</a></td>";
                //<input type=text class='number form-control' name=Qty$POLine->LineNo size=6 value=".$DisplayQuantity.">"."$recibidas
                echo "<td align='center'>".$POLine->StockID."</td>";
                echo "<td>".$POLine->ItemDescription."</td>";
                
                echo "<td style='width: 10%;' align='center'>";
                //echo "<component-number id=Qty$POLine->LineNo name=Qty$POLine->LineNo maxlength=6 value=".$DisplayQuantity." style='text-align: right;'></component-number>";
                echo "<label style='width: 98%;'>".$DisplayQuantity."</label>";
                echo "<input type='hidden' name=Qty$POLine->LineNo id=Qty$POLine->LineNo value='".$DisplayQuantity."' />";
                echo "</td>";

                echo "<td class='pt15' align='center'>".$uom."</td>";
                //echo "<td><input type=text class='number form-control' name=Price$POLine->LineNo size=10 value=".$DisplayPrice."></td>";
                echo "<td style='width: 13%;'>
                <component-decimales id=Price$POLine->LineNo name=Price$POLine->LineNo maxlength=6 value=".$DisplayPrice." style='text-align: right;'></component-decimales>
                </td>";
                
                echo "<td class='text-right'>$ " . $DisplayLineTotal . "</td>
                <td style='display:none;'><!--<input type=text class=date alt='".$_SESSION['DefaultDateFormat']."' name=ReqDelDate$POLine->LineNo size=11 value=".$POLine->ReqDelDate.">-->
                        <select name='diafechaentrega$POLine->LineNo'>
                ";
                    $dia = substr($POLine->ReqDelDate, 0, 2);
                if ($dia=="") {
                    $dia = date("d");
                }
                        
                    $mes = substr($POLine->ReqDelDate, 3, 2);
                if ($mes=="") {
                    $mes =date("m");
                }
                        
                    $anio = substr($POLine->ReqDelDate, 6, 4);
                if ($anio=="") {
                    $anio = date("Y");
                }
                    
                    $sql = "SELECT * FROM cat_Days";
                    $dias = DB_query($sql, $db, '', '');
                while ($myrowdia=DB_fetch_array($dias, $db)) {
                    $diabase=$myrowdia['DiaId'];
                    if (rtrim(intval($dia))==rtrim(intval($diabase))) {
                        echo '<option  VALUE="' . $myrowdia['DiaId'] .  '  " selected>' .$myrowdia['Dia'];
                    } else {
                        echo '<option  VALUE="' . $myrowdia['DiaId'] .  '" >' .$myrowdia['Dia'];
                    }
                }
    
                    echo "</select>&nbsp;<select name='mesfechaentrega$POLine->LineNo'>";
                   $sql = "SELECT * FROM cat_Months";
                   $Meses = DB_query($sql, $db);
                while ($myrowMes=DB_fetch_array($Meses, $db)) {
                    $Mesbase=$myrowMes['u_mes'];
                    if (rtrim(intval($mes))==rtrim(intval($Mesbase))) {
                        echo '<option  VALUE="' . $myrowMes['u_mes'] .  '  " selected>' .$myrowMes['mes'];
                    } else {
                        echo '<option  VALUE="' . $myrowMes['u_mes'] .  '" >' .$myrowMes['mes'];
                    }
                }
                   
                echo "</select>";
                echo "&nbsp;<input name='aniofechaentrega$POLine->LineNo' type='text' size='4' value='$anio'>";
                echo"</td>";
            
                if ($permisoeliminarpartidas==1) {
                    $enc = new Encryption;
                    $url = "&identifier=>" . $identifier . "&Delete=>" . $POLine->LineNo . "&TieToOrderNumber=>" . $TieToOrderNumber . "&supplierid=>" . $supplierid;
                    $url = $enc->encode($url);
                    $liga= "URL=" . $url;
                    echo " <td style='display:none;'><a href='" . $_SERVER['PHP_SELF'] . "?" . $liga . "'><img src=images/eliminar.png title='ELIMINAR PARTIDA'></a></td>";
                }

                echo '</tr>';

                // Mostrar informacion adicional de campos extra

                $lineaorden=$POLine->LineNo;
                $lineaordenx=$POLine->LineNo;
                $lineaordeny=$POLine->PODetailRec;
                
                if ($lineaordeny=='') {
                    $lineaordeny=0;
                }
                        
                $ordenexiste=$_SESSION['ExistingPurchOrder'];
                $StockID_Prop=$POLine->StockID;
                $typesales=30;

                // echo '<tr style="align:left" valign="top">';
                // echo '<td valign="top" colspan=2 style="align:left">';
                // echo '</td>';
                // echo '<td valign="top" colspan=11 style="align:left">';
                // include('includes/Show_Stockcatproperties.php');
                // echo '</td>';
                // echo '</tr>';
                
                if (!empty($POLine->clavepresupuestal) && ($POLine->clavepresupuestal == $_POST["clavepresupuestal_".$POLine->LineNo] || empty($_POST["clavepresupuestal_".$POLine->LineNo]))) {
                    $clavepresupuestal= $POLine->clavepresupuestal;
                } else {
                    $clavepresupuestal= $_POST["clavepresupuestal_".$POLine->LineNo];
                }

                $totalPartida = abs(fnObtenerTotalSuficienciaAuto($db, $_SESSION['PO'.$identifier]->suficienciaType, $_SESSION['PO'.$identifier]->suficienciaTransno, 263, $clavepresupuestal));
                // echo "<br>totalPartida: ".$totalPartida;
                if (number_format($totalPartida, 2, '.', '') < number_format($DisplayLineTotal, 2, '.', '')) {
                    // Validacion Suficiencia y Compra
                    $diferenciasSuficienciaTotal = 1;
                    $mensaje_emergente .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Renglon '.$POLine->LineNo.' con diferencias en la Clave Presupuestal.</p>';
                    // $mensaje_emergente .= '<p>Total Renglon '.$POLine->LineNo.' $ '.number_format($DisplayLineTotal, 2).'</p>';
                    // $mensaje_emergente .= '<p>Total Suficiencia $ '.number_format($totalPartida, 2).'</p>';
                    $procesoterminado = 2;
                }
                                            
                echo "<tr>";

                // consultar la unidad de negocio para considerar en la busqueda de la clave
                $sql ='select tagref FROM locations l where loccode="'.$_SESSION['PO'.$identifier]->Location.'"';
                $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                $myrowloc = DB_fetch_array($result);
                $tagref = $myrowloc['tagref'];

                echo "<td colspan=8 nowrap>";

                $readonly = " readonly='true' ";
                if (empty($clavepresupuestal)) {
                    echo "&nbsp;<a href=\"javascript:openNewWindow('ConsultaClavePresuestal.php?identificador=".$identifier."&linea=".$POLine->LineNo."&tagref=".$tagref."&tipo=2&separado=1');\"><img src='images/buscar_clave_25.png' height='20' title='Buscar Clave'></a>";
                    echo "&nbsp;&nbsp;<input type='text' name='clavepresupuestal_".$POLine->LineNo."' id='clavepresupuestal_".$POLine->LineNo."' value='".$clavepresupuestal."' style='font-weight: bold;background-color: #FAFAFA; width: 98%;'>";
                } else {
                    echo "&nbsp;&nbsp;<label style='width: 98%;'>".$clavepresupuestal."</label>";
                    echo "&nbsp;&nbsp;<input type='hidden' name='clavepresupuestal_".$POLine->LineNo."' id='clavepresupuestal_".$POLine->LineNo."' value='".$clavepresupuestal."' style='font-weight: bold;background-color: #FAFAFA; width: 98%;'>";
                }

                echo "&nbsp;&nbsp;<input type='hidden' name='presupuesto_".$POLine->LineNo."' id='clavepresupuestal_".$POLine->LineNo."' value='".$clavepresupuestal."' style='font-weight: bold;background-color: #FAFAFA;'>";
                echo "</td>";
                /*
                // Crear tabla para mostrar la estructura de la clave presupuestal
                echo '<table cellpadding=2 colspan=7 border=1 style="border-collapse: collapse; border-color:lightgray; display:none;">';
                //echo "<tr><td colspan= 22 style='text-align:center;font-size:14px;'><strong>" . _('Clave Presupuestal') . "</strong></td></tr>";

                // ciclo que recorre los elementos de la clave para mostrar
                echo "<tr>";
                foreach ($estructura_clave as $clave => $datos) {
                    echo "<td style='text-align:center;'>".$datos["concepto"]."</td>";
                }
                        echo "</tr>";
                        
                        echo "<tr>
                                <td ".$estilo_alineacion."><input type='text' style='text-align:center;border-width:0;' onkeypress='Next(this.id, event)' name='anio_".$POLine->LineNo."' id='".$POLine->LineNo.".1' value='".$anio."' size='4' maxlength='4' readonly></td>
                                <td ".$estilo_alineacion."><input type='text' style='text-align:center;border-width:0;' onkeypress='Next(this.id, event)' name='ramo_".$POLine->LineNo."' id='".$POLine->LineNo.".2' value='".$ramo."' size='2' maxlength='2' readonly></td>      
                                <td ".$estilo_alineacion."><input type='text' style='text-align:center;border-width:0;' onkeypress='Next(this.id, event)' name='organoSuperior_".$POLine->LineNo."' id='".$POLine->LineNo.".3' value='".$organosuperior."' size='2' maxlength='2' readonly></td>      
                                <td ".$estilo_alineacion."><input type='text' style='text-align:center;border-width:0;' onkeypress='Next(this.id, event)' name='unidadPresupuestal_".$POLine->LineNo."' id='".$POLine->LineNo.".4' value='".$unidadpresupuestal."' size='2' maxlength='2' readonly></td>                                      

                                <td ".$estilo_alineacion."><input type='text' style='text-align:center;border-width:0;' onkeypress='Next(this.id, event)' name='rubroIngreso_".$POLine->LineNo."' id='".$POLine->LineNo.".5' value='".$rubroingreso."' size='7' maxlength='7' readonly></td>     
                                <td ".$estilo_alineacion."><input type='text' style='text-align:center;border-width:0;' onkeypress='Next(this.id, event)' name='tipoGasto_".$POLine->LineNo."' id='".$POLine->LineNo.".6' value='".$tipogasto."' size='2' maxlength='2' readonly></td>    
                                <td ".$estilo_alineacion."><input type='text' style='text-align:center;border-width:0;' onkeypress='Next(this.id, event)' name='objetoGasto_".$POLine->LineNo."' id='".$POLine->LineNo.".7' value='".$objetogasto."' size='6' maxlength='6' readonly></td>                                   

                                <td ".$estilo_alineacion."><input type='text' style='text-align:center;border-width:0;' onkeypress='Next(this.id, false)' name='finalidadFuncion_".$POLine->LineNo."' id='".$POLine->LineNo.".8' value='".$finalidad_funcion."' size='3' maxlength='3' readonly></td>     
                                <td ".$estilo_alineacion."><input type='text' style='text-align:center;border-width:0;' onkeypress='Next(this.id, false)' name='subFuncion_".$POLine->LineNo."' id='".$POLine->LineNo.".9' value='".$subfuncion."' size='2' maxlength='2' readonly></td>      
                                <td ".$estilo_alineacion."><input type='text' style='text-align:center;border-width:0;' onkeypress='Next(this.id, false)' name='ejeTematicoPE_".$POLine->LineNo."' id='".$POLine->LineNo.".10' value='".$ejetematico."' size='3' maxlength='3' readonly></td>     
                                <td ".$estilo_alineacion."><input type='text' style='text-align:center;border-width:0;' onkeypress='Next(this.id, false)' name='sector_".$POLine->LineNo."' id='".$POLine->LineNo.".11' value='".$sector."' size='2' maxlength='2' readonly></td>     
                                <td ".$estilo_alineacion."><input type='text' style='text-align:center;border-width:0;' onkeypress='Next(this.id, false)' name='programa_".$POLine->LineNo."' id='".$POLine->LineNo.".12' value='".$programa."' size='5' maxlength='5' readonly></td>     
                                <td ".$estilo_alineacion."><input type='text' style='text-align:center;border-width:0;' onkeypress='Next(this.id, false)' name='subprograma_".$POLine->LineNo."' id='".$POLine->LineNo.".13' value='".$subprograma."' size='2' maxlength='2' readonly></td>   
                                <td ".$estilo_alineacion."><input type='text' style='text-align:center;border-width:0;' onkeypress='Next(this.id, false)' name='objetivos_".$POLine->LineNo."' id='".$POLine->LineNo.".14' value='".$objetivos."' size='3' maxlength='3' readonly></td>   
                                <td ".$estilo_alineacion."><input type='text' style='text-align:center;border-width:0;' onkeypress='Next(this.id, false)' name='proyecto_".$POLine->LineNo."' id='".$POLine->LineNo.".15' value='".$proyecto."' size='4' maxlength='4' readonly></td>     
                                <td ".$estilo_alineacion."><input type='text' style='text-align:center;border-width:0;' onkeypress='Next(this.id, false)' name='estrategia_".$POLine->LineNo."' id='".$POLine->LineNo.".16' value='".$estrategias."' size='3' maxlength='3' readonly></td>";
                                // <td ".$estilo_alineacion."><input type='text' style='text-align:center;' onkeypress='Next(this.id, false)' name='obra_".$POLine->LineNo."' id='".$POLine->LineNo.".17' value='".$obra."' size='5' maxlength='5'></td>

                          echo "<td ".$estilo_alineacion."><input type='text' style='text-align:center;border-width:0;' onkeypress='Next(this.id, false)' name='beneficiario_".$POLine->LineNo."' id='".$POLine->LineNo.".17' value='".$beneficiario."' size='3' maxlength='3' readonly></td>     
                                <td ".$estilo_alineacion."><input type='text' style='text-align:center;border-width:0;' onkeypress='Next(this.id, false)' name='espacioGeografico_".$POLine->LineNo."' id='".$POLine->LineNo.".18' value='".$espaciogeografico."' size='5' maxlength='5' readonly></td>
                            <tr>";
                        echo "</table>";
                        
                echo "</td>";
                */
                echo "</tr>";
            
            //          echo "<tr style='height:5pt;background-color:#C2D1E0'><td colspan=13></td></tr>";
                
                //actualizar arreglo si hubo cambio de moneda
                if ($oldcurrency != $_SESSION['PO'.$identifier]->CurrCode) {
                    $_SESSION['PO'.$identifier]->update_order_item(
                        $POLine->LineNo,
                        $POLine->Quantity,
                        $price,
                        $POLine->Desc1,
                        $POLine->Desc2,
                        $POLine->Desc3,
                        $POLine->ItemDescription,
                        $POLine->GLCode,
                        $POLine->GLAccountNameG,
                        $POLine->ReqDelDate,
                        $POline->ShiptRef,
                        $POLine->JobRef,
                        $POLine->itemno,
                        $POLine->uom,
                        $POLine->suppliers_partno,
                        $POLine->Quantity*$price,
                        $POLine->package,
                        $POLine->pcunit,
                        $POLine->nw,
                        $POLine->gw,
                        $POLine->cuft,
                        $POLine->Quantity,
                        $POLine->Quantity*$price,
                        $POLine->Narrative,
                        $POLine->Justification,
                        $POLine->estimated_cost,
                        $_POST["clavepresupuestal_".$_SESSION['PO'.$identifier]->LineNo]
                    );
                }
            
            
            
            
                /************************************************************************************/
                /* A NIVEL CATEGORIA DE PRODUCTO AGREGAR ESTA CONDICION DE PERMITIR TEXTO NARRATIVO */
                
                $allowNarrRes = DB_query("select allowNarrativePOLine from stockmaster LEFT JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
                              where stockid = '" . $POLine->StockID . "'", $db);
                if (DB_num_rows($allowNarrRes)>0) {
                    $allowNarrRow = DB_fetch_row($allowNarrRes);
                    $allowNarr = $allowNarrRow[0];
                } else {
                    $allowNarr =0;
                }
                
                //para productos no inventariables agregar caja para captura de texto
                if ($POLine->StockID=="") {
                    $allowNarr = 1;
                }
                
                /************************************************************************************/
                if ($allowNarr == 1 and $permisotextonarra==1) {
                    echo $RowStarter;
                    echo '<tr><td>' . _('Texto') . ':</td><td colspan=14><textarea name="Narrative_' . $POLine->LineNo . '" cols="45" rows="2">' . stripslashes(AddCarriageReturns($POLine->Narrative)) . '</textarea></td></tr>';
                } else {
                    echo '<input type=hidden name="Narrative" value="">';
                }
                
                if ($_SESSION['ExistingPurchOrder'] != 0) {
                    if (Havepermission($_SESSION['UserID'], 714, $db) == 1) {
                        include_once 'includes/mail.php';
                        include_once 'includes/GetOrderComments.inc';
                
                        $orderNoTmp = $_SESSION['ExistingPurchOrder'];
                        $poItemTmp  = $POLine->PODetailRec;
                
                        $commentsHTML = getOrderCommentsTable($orderNoTmp, $poItemTmp, $db);
                
                        echo '<tr>';
                        echo '<td valign="top" colspan="2"><strong>' . _('Mensaje') . ':</strong></td>';
                        echo '<td colspan="5"><textarea cols="45" rows="3" name="messages[' . $orderNoTmp . '][' . $poItemTmp . ']">' . $_POST['messages'][$orderNoTmp][$poItemTmp] . '</textarea></td>';
                        echo '<td valign="top" colspan="2"><strong>' . _('Historial Mensajes') .':</strong></td>';
                        echo '<td colspan="6">';
                            echo '<div style="width:610px; height:70px; overflow:auto; border: 1px dotted #000">';
                                echo $commentsHTML;
                            echo '</div>';
                        echo '</td>';
                        echo '</tr>';
                    }
                }
                
                $_SESSION['PO'.$identifier]->total = $_SESSION['PO'.$identifier]->total + $LineTotal;
        
                // permiso para ver avance de compras en base a OT
                if ($_SESSION['PO'.$identifier]->Wo>0 and $permisoVerOT==1) {
                    $SQL="SELECT *   
                      FROM woreq_purchorders where wo=".$_SESSION['PO'.$identifier]->Wo." 
                        AND stockid='".$POLine->StockID."'
                        AND masterparentid='".$POLine->womasterid."'";
                    //echo '<pre>sql:'.$SQL;
                    $excresult=DB_query($SQL, $db);
                    if (DB_num_rows($excresult)>0) {
                        $excrow=DB_fetch_array($excresult);
                        $sql = "SELECT stockserialitems.serialno
                            FROM stockserialitems
                            WHERE stockserialitems.wo = '".$_SESSION['PO'.$identifier]->Wo."'
                            AND stockserialitems.stockid = '".$POLine->womasterid."'";
                        $reswo = DB_query($sql, $db);
                        while ($rowwo = DB_fetch_array($reswo)) {
                            $serial = $rowwo['serialno'];
                        }
                    
                        echo $RowStarter;
                        echo '<tr style="text-align:right; background-color:white">
                        <td colspan=3 style="text-align:right;background-color:lightgreen"><b>' . _('Inf OT ').$_SESSION['PO'.$identifier]->Wo.':</b></td>';
                        echo '<td colspan=12>'. _(' No Identificacion: <b>') .$serial. '</b>
                         Requerido:<b>' . number_format($excrow['qtypu'], 4)  . '</b>
                          Comprado:<b>' . number_format($excrow['purchqty'], 4)  . '</b>
                          Excedente:<b>' . number_format($excrow['purchqty_exc'], 4)  . '</b>
                          Emitido:<b>' . number_format($excrow['transferqty'], 4)  . '</b>
                          Pendiente:<b>' . number_format($excrow['qtypu']-($excrow['purchqty']+$excrow['purchqty_exc']), 4)  . '</b></td>
                        </tr>';
                    }
                }
            
            
            
            
                // solo por permiso de seguridad
                $estatusEnPendiente=1;
                if (Havepermission($_SESSION['UserID'], 256, $db)==1) {
                    $estatusEnPendiente=0;
                    /* INICIO DE BUSQUEDA DE DISPONIBILIDAD EN ESTE ALMACEN */
                    $sql='SELECT locstock.loccode, 
                        locstock.stockid, 
                        locstock.quantity, 
                        locstock.reorderlevel, 
                        locstock.ontransit,
                        stockmaster.categoryid
                    FROM locstock JOIN stockmaster ON locstock.stockid = stockmaster.stockid
                    WHERE locstock.stockid = "'.$POLine->StockID.'"
                        AND locstock.loccode = "'. $_SESSION['PO'.$identifier]->Location.'"';
                        
                        //WHERE supplierno="'.$_SESSION['PO'.$identifier]->SupplierID.'"
    
                    $thiscategory = '';
                
                    // $excresult=DB_query($sql, $db);
                    // if (DB_num_rows($excresult)>0) {
                    //     $excrow=DB_fetch_array($excresult);
                    //     echo $RowStarter;
                    //     echo '<tr style="text-align:right; background-color:white">
                    //     <td colspan=3 style="text-align:right;background-color:lightgreen">' . _('disponibilidad') . ':</td>
                    //     <td colspan=12>-> exist:<b>' . $excrow['quantity']  . '</b>
                    //       optimo:<b>' . $excrow['reorderlevel']  . '</b>
                    //       transito:<b>' . $excrow['ontransit']  . '</b></td>
                    //     </tr>';
                    //     $suggestedPurchase = $excrow['reorderlevel'] - $excrow['quantity'] + $excrow['ontransit'];
                    
                    //     $thiscategory = $excrow['categoryid'];
                    // }
                
                    /* INICIO DE BUSQUEDA DE DISPONIBILIDAD EN OTROS ALMACENES */
                    $sql='SELECT locstock.loccode, 
                        locstock.stockid, 
                        locstock.quantity, 
                        locstock.reorderlevel, 
                        locstock.ontransit,
                        locations.locationname
                    FROM locstock JOIN locations ON locstock.loccode = locations.loccode
                    WHERE locstock.stockid = "'.$POLine->StockID.'"
                        AND locstock.loccode <> "'. $_SESSION['PO'.$identifier]->Location.'"
                        AND locstock.quantity > 0';
                    // $excresult=DB_query($sql, $db);
                    // if (DB_num_rows($excresult)>0) {
                    //     while ($excrow=DB_fetch_array($excresult)) {
                    //         echo '<tr style="text-align:right; background-color:white">';
                        
                    //         $color = 'darkgray';
                    //         if (($excrow['quantity'] - $excrow['reorderlevel'] - $excrow['ontransit']) > 0) {
                    //             $suggestedPurchase = $suggestedPurchase - ($excrow['quantity'] - $excrow['reorderlevel'] - $excrow['ontransit']);
                    //             $color = 'lightgreen';
                    //         }
                        
                    //         echo '<td colspan=2 style="text-align:right;background-color:white">'._('solicitar traspaso->').':</td>';
                    //         echo '<td style="text-align:right;background-color:'.$color.'">excedente:<b>' . ($excrow['quantity'] - $excrow['reorderlevel'] - $excrow['ontransit'])  . '</b></td>
                    //         <td style="text-align:right;background-color:'.$color.'">' . ($excrow['quantity'])  . ' exist</td>
                    //         <td style="text-align:right;background-color:'.$color.'">' . ($excrow['ontransit'])  . ' trans</td>
                    //         <td style="text-align:right;background-color:'.$color.'">' . ($excrow['reorderlevel'])  . ' optim</td>
                    //           <td style="text-align:left;background-color:'.$color.'">' . $excrow['locationname'] . '</td>
                    //         </tr>';
                    //     }
                    // }
                
                    /* INICIO DE BUSQUEDA DE ORDENES DE COMPRA PENDIENTES */
                    $sql="SELECT purchorders.orderno, purchorders.comments, SUM(purchorderdetails.quantityord ) AS ENCOMPRA
                    FROM  purchorderdetails
                    INNER JOIN purchorders ON purchorderdetails.orderno=purchorders.orderno
                            AND purchorders.status not in ('cancelled') 
                    WHERE purchorders.intostocklocation = '". $_SESSION['PO'.$identifier]->Location."' 
                            AND purchorderdetails.itemcode = '" . $POLine->StockID . "'
                            AND '' <> '" . $POLine->StockID . "'
                            AND purchorders.orderno <> '".$_SESSION['PO'.$identifier]->OrderNo."'
                    GROUP BY purchorders.orderno, purchorders.comments 
                    HAVING SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd)>0";
                    // $excresult=DB_query($sql, $db);
                    // if (DB_num_rows($excresult)>0) {
                    //     while ($excrow=DB_fetch_array($excresult)) {
                    //         echo '<tr style="text-align:right; background-color:white">
                    //         <td colspan=3 style="text-align:right;background-color:yellow">' . _('en orden de compra') . ':</td>
                    //         <td><b>' . $excrow['ENCOMPRA']  . '</b></td>
                    //         <td>O.C.:' . $excrow['orderno']  . '</td>
                    //         <td colspan=2>' . $excrow['comments']  . '</td>
                    //         </tr>';
                            
                    //         $suggestedPurchase = $suggestedPurchase - $excrow['ENCOMPRA'];
                    //     }
                    // }
                
                
                    /* INICIO DE BUSQUEDA DE PEDIDOS ABIERTOS O CERRADOS EN DONDE ESTA ESTE PRODUCTO RESERVADO */
                    $sql='SELECT salesorders.orderno, salesorders.deliverto, salesorders.quotation, SUM(salesorderdetails.quantity ) AS enventa
                      FROM  salesorderdetails
                      INNER JOIN salesorders ON salesorderdetails.orderno=salesorders.orderno
                            AND salesorders.quotation NOT IN (4,3,1)
                    WHERE salesorderdetails.fromstkloc = "'. $_SESSION['PO'.$identifier]->Location.'" 
                            AND salesorderdetails.stkcode = "'.$POLine->StockID.'"
                    GROUP BY salesorders.orderno, salesorders.deliverto, salesorders.quotation ';
                
                    // $excresult=DB_query($sql, $db);
                    $excresult = "";
                    if (DB_num_rows($excresult)>0) {
                        while ($excrow=DB_fetch_array($excresult)) {
                            echo '<tr style="text-align:right; background-color:white">
                            <td colspan=3 style="text-align:right;background-color:orange">' . _('pedido de venta ->') . ':</td>
                            <td style="text-align:right;background-color:orange"><b>' . -$excrow['enventa']  . '</b></td>
                            <td>pedido:' . $excrow['orderno']  . '</td>
                            <td colspan=10>' . $excrow['deliverto']  . '</td>
                            </tr>';
                            $suggestedPurchase = $suggestedPurchase + $excrow['enventa'];
                        }
                    }
                    /**alarma de que el precio es mayor**/
                
                    $sql="SELECT purchorders.orderno, purchorderdetails.unitprice AS precio
                    FROM  purchorderdetails
                    INNER JOIN purchorders ON purchorderdetails.orderno=purchorders.orderno
                            AND purchorders.status not in ('cancelled') 
                    WHERE purchorders.intostocklocation = '". $_SESSION['PO'.$identifier]->Location."' 
                            AND purchorderdetails.itemcode = '" . $POLine->StockID . "'
                            AND '' <> '" . $POLine->StockID . "'
                            AND purchorders.orderno <> '".$_SESSION['PO'.$identifier]->OrderNo."'
                    ORDER BY purchorders.orderno DESC
                    LIMIT 1";
                    //echo $sql.'<br><br>';
                    $Priceresult=DB_query($sql, $db);
                    $vertexto=1;
                    if (DB_num_rows($Priceresult)>0) {
                        $priceorder=DB_fetch_array($Priceresult);
                        $precioant=$priceorder['precio'];
                        $ordernoant=$priceorder['orderno'];
                    } else {
                        $vertexto=0;
                    }
                
                    if ($DisplayPrice>$precioant and $vertexto==1) {
                        // echo '<tr style="text-align:center; font-size: 10px; background-color:yellow">';
                        // echo '<td colspan=15 style="text-align:left;background-color:#F3F781; font-size:12px;"><font color="darkred"><b>' . _('El precio de la ultima orden de compra (').$ordernoant ._(') para este producto se realizo con un precio de $').number_format($precioant, 2)._('.').'</b></font></td>';
                        // echo'<tr>';
                    }
                
                    /* FIN DE BUSQUEDA DE EXCEPCIONES*/
                
                    if (($suggestedPurchase - $DisplayQuantity) >= 0) {
                        echo '<tr style="text-align:right; font-size: 10px; background-color:white; display: none;">
                                <td colspan=3 style="text-align:right;background-color:yellow; font-size:12px;"><b>' . _('Compra Sugerida') . ':</b></td>
                                <td colspan=1 style="text-align:right;background-color:yellow; font-size:12px;color: black;"><b>' . $suggestedPurchase  . '</b></td>
                                <td colspan=11><b></b></td>
                                </tr>';
                    } else {
                        $estatusEnPendiente = 1;
                        echo '<tr style="text-align:right; font-size: 10px; background-color:white; display: none;">
                                <td colspan=3 style="text-align:right; font-size:12px;"><b>' . _('Escribe Justificación:<br>Requerida para Autorización *') . '</b></td>
                                <td colspan=1 style="text-align:right; font-size:12px;"><b>' . $DisplayQuantity  . '</b></td>
                                <td colspan=4><b><textarea name="Justification_' . $POLine->LineNo . '" class="form-control" cols="75" rows="2">' . stripslashes(AddCarriageReturns($POLine->Justification)) . '</textarea></b></td>
                                </tr>';
                    }
                }
                $noPorLimite = 0;
                if ($POLine->StockID != '') {
                    //Verifica para productos inventariables...
                    $authsql='SELECT authlevel, cancreate, canauthorise, cancancell, cancomplete 
                        FROM purchorderauth 
                        WHERE userid="'.$_SESSION['UserID'].'"
                        AND currabrev="'.$_SESSION['PO'.$identifier]->CurrCode.'"
                        AND (category="All" OR category = "'.$thiscategory.'")';
    
                    //echo $authsql;
                    $authresult=DB_query($authsql, $db);
                    if ($myrow=DB_fetch_array($authresult)) {
                        $AuthorityLevel=$myrow['authlevel'];
                    
                        if ($LineTotal > $AuthorityLevel) {
                            $AutCreate=1;
                            $AutAuthorise=1;
                            $AutCancell=1;
                            $AutDeliver=1;
                        
                            $noPorLimite = 1;
                        
                            /* SI NO TIENE PERMISOS PARA CREAR ORDEN DE COMPRA PARA ESTA CATEGORIA DE PRODUCTO DESPLIEGA ALARMA */
                            echo '<tr style="text-align:left; font-size: 10px; background-color:#eba254">
                            <td colspan=3 style="text-align:left; font-size:12px;color: white;"><b>' . _('NO PUEDE CREAR ORDEN') . '</b></td>
                            <td colspan=10 style="text-align:left; font-size:12px;color: white;"><b>NO TIENE PERMISOS PARA CREAR ORDENES DE COMPRA PARA ESTA CATEGORIA O CUENTA CONTABLE...<br>CONSULTE CON SU ADMINISTRADOR DEL SISTEMA...</b></td>
                            </tr>';
                        
                            echo '<tr style="text-align:left; font-size: 10px; background-color:#eba254">
                            <td colspan=3 style="text-align:left;font-size:12px;color: white;"><b>' . _('SOBREPASA LIMITE DE COMPRA') . '</b></td>
                            <td colspan=10 style="text-align:left; font-size:12px;color: white;"><b>SU LIMITE DE COMPRA PARA ESTA CATEGORIA O CUENTA ES DE:'.$AuthorityLevel.'</b></td>
                            </tr>';//
                        } else {
                            if ($AutCreate==0) {
                                $AutCreate=$myrow['cancreate'];
                                if ($AutCreate==1) {
                                    /* SI NO TIENE PERMISOS PARA CREAR ORDEN DE COMPRA PARA ESTA CATEGORIA DE PRODUCTO DESPLIEGA ALARMA */
                                    echo '<tr style="text-align:left; font-size: 10px; background-color:#eba254">
                                    <td colspan=3 style="text-align:left; font-size:12px;color: white;"><b>' . _('NO PUEDE CREAR ORDEN') . '</b></td>
                                    <td colspan=10 style="text-align:left;font-size:12px;color: white;"><b>NO TIENE PERMISOS PARA CREAR ORDENES DE COMPRA PARA ESTA CATEGORIA O CUENTA CONTABLE...<br>CONSULTE CON SU ADMINISTRADOR DEL SISTEMA...</b></td>
                                    </tr>';
                                }
                            }
                            if ($AutAuthorise==0) {
                                $AutAuthorise=$myrow['canauthorise'];
                            }
                            if ($AutCancell==0) {
                                $AutCancell=$myrow['cancancell'];
                            }
                            if ($AutDeliver==0) {
                                $AutDeliver=$myrow['cancomplete'];
                            }
                        }
                    } else {
                        /* SI CUALQUIER CATEGORIA NO TIENE PERMISO, FIJAR EL PERMISO PARA TODAS */
                        $AutCreate=1;
                        $AutAuthorise=1;
                        $AutCancell=1;
                        $AutDeliver=1;
                    }
                } else {
                    //Verifica para productos NO inventariables...
                    $authsql='SELECT authlevel, cancreate, canauthorise, cancancell, cancomplete 
                        FROM purchorderauth 
                        WHERE userid="'.$_SESSION['UserID'].'"
                        AND currabrev="'.$_SESSION['PO'.$identifier]->CurrCode.'"
                        AND (account="All" OR account = "'.$POLine->GLCode.'")
                        ORDER BY account LIMIT 1';
    
                    //echo $authsql;
                    $authresult=DB_query($authsql, $db);
                    if ($myrow=DB_fetch_array($authresult)) {
                        $AuthorityLevel=$myrow['authlevel'];
                    
                        if ($LineTotal > $AuthorityLevel) {
                            $AutCreate=1;
                            $AutAuthorise=1;
                            $AutCancell=1;
                            $AutDeliver=1;
                        
                            $noPorLimite = 1;
                        
                            /* SI NO TIENE PERMISOS PARA CREAR ORDEN DE COMPRA PARA ESTA CATEGORIA DE PRODUCTO DESPLIEGA ALARMA */
                            echo '<tr style="text-align:right; font-size: 10px; background-color:#eba254">
                            <td colspan=3 style="text-align:right; font-size:12px;color: white;"><b>' . _('NO PUEDE CREAR ORDEN') . '</b></td>
                            <td colspan=10 style="text-align:right; font-size:12px;color: white;"><b>NO TIENE PERMISOS PARA CREAR ORDENES DE COMPRA PARA ESTA CATEGORIA O CUENTA CONTABLE...<br>CONSULTE CON SU ADMINISTRADOR DEL SISTEMA...</b></td>
                            </tr>';
                        
                            echo '<tr style="text-align:right; font-size: 10px; background-color:#eba254">
                            <td colspan=3 style="text-align:right; font-size:12px;color: white;"><b>' . _('SOBREPASA LIMITE DE COMPRA') . '</b></td>
                            <td colspan=10 style="text-align:right; font-size:12px;color: white;"><b>SU LIMITE DE COMPRA PARA ESTA CATEGORIA O CUENTA ES DE:'.$AuthorityLevel.'</b></td>
                            </tr>';
                        } else {
                            if ($AutCreate==0) {
                                $AutCreate=$myrow['cancreate'];
                                if ($AutCreate==1) {
                                    /* SI NO TIENE PERMISOS PARA CREAR ORDEN DE COMPRA PARA ESTA CATEGORIA DE PRODUCTO DESPLIEGA ALARMA */
                                    echo '<tr style="text-align:right; font-size: 10px; background-color:#eba254">
                                    <td colspan=3 style="text-align:right; font-size:12px;color: white;"><b>' . _('NO PUEDE CREAR ORDEN') . '</b></td>
                                    <td colspan=10 style="text-align:right; font-size:12px;color: white;"><b>NO TIENE PERMISOS PARA CREAR ORDENES DE COMPRA PARA ESTA CATEGORIA O CUENTA CONTABLE...<br>CONSULTE CON SU ADMINISTRADOR DEL SISTEMA...</b></td>
                                    </tr>';
                                }
                            }
                            if ($AutAuthorise==0) {
                                $AutAuthorise=$myrow['canauthorise'];
                            }
                            if ($AutCancell==0) {
                                $AutCancell=$myrow['cancancell'];
                            }
                            if ($AutDeliver==0) {
                                $AutDeliver=$myrow['cancomplete'];
                            }
                        }
                    } else {
                        /* SI CUALQUIER CATEGORIA U CUENTA NO TIENE PERMISO, FIJAR EL PERMISO PARA TODAS */
                        $AutCreate=1;
                        $AutAuthorise=1;
                        $AutCancell=1;
                        $AutDeliver=1;
                    }
                }
            }
        }

        $DisplayTotal = number_format($_SESSION['PO'.$identifier]->total, 2);
        echo '<tr><td colspan="7" style="text-align: right;">' . _('Totales:') . '</td><td style="text-align: right;">$ ' . $DisplayTotal . '</td>';

        if (number_format($_SESSION['PO'.$identifier]->total, 2, '.', '') > number_format($_SESSION['PO'.$identifier]->totalSuficiencia, 2, '.', '')) {
            // Validacion Suficiencia y Compra
            $diferenciasSuficienciaTotal = 1;
            $mensaje_emergente .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Existen diferencias entre la Suficiencia Generada y el Total de Orden de Compra</p>';
            $mensaje_emergente .= '<p>Total Suficiencia $ '.number_format($_SESSION['PO'.$identifier]->totalSuficiencia, 2).'</p>';
            $mensaje_emergente .= '<p>Total Orden de Compra $ '.number_format($_SESSION['PO'.$identifier]->total, 2).'</p>';
            $urlGeneral = "&transno=>" . $_SESSION['PO'.$identifier]->RequisitionNo . "&type=>" . '19';
            $enc = new Encryption;
            $url = $enc->encode($urlGeneral);
            $liga= "URL=" . $url;
            // $mensaje_emergente .= '<p><a target="_blank"  href="suficiencia_manual.php?' . $liga . '">Actualizar Suficiencia</a></p>';
            $mensaje_emergente .= '<p><component-button type="button" id="btnRechazarSuf" name="btnRechazarSuf" value="Rechazar Suficiencia" class="glyphicon glyphicon-arrow-left" onclick="fnRechazarSuficiencia('.$_SESSION['PO'.$identifier]->suficienciaType.', '.$_SESSION['PO'.$identifier]->suficienciaTransno.', '.$_SESSION['PO'.$identifier]->RequisitionNo.')"></component-button><p>';
            $procesoterminado = 2;
        }

        if ($urSinAlmacen == 1) {
            $mensaje_emergente .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No existe un Almacén para la Unidad Responsable</p>';
            $procesoterminado = 2;
        }

        if ($_SESSION['PO'.$identifier]->suficienciaEstatus != '4') {
            $valSuficienciaEstatus = 1;
            $mensaje_emergente .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La Suficiencia Presupuestal con Folio '.$_SESSION['PO'.$identifier]->suficienciaTransno.' no se encuentra Autorizada. Es necesario para poder Autorizar la Orden de Compra</p>';
            $procesoterminado = 2;
        }

        // Se comenta validacion, ya que se pueden gener de ambas
        /*if ($traeBienes == 1 && $traeServicios == 1) {
            // La compra tiene bienes y servicios
            $valSuficienciaEstatus = 1;
            $mensaje_emergente .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se puede realizar la compra si tiene Bienes y Servicios</p>';
            $procesoterminado = 2;
        }*/
    
        echo '<td colspan=2 class="pie_derecha" style="display: none;">cuadrar:<input type=checkbox name=cuadrar>
                    <input type=text class=number name=totCuadre size=10>'.$diffEnPrecio.'</td></tr></table>';


        echo '</div>';// Div Scroll
    
        /*
        if ($errorprecios>0)
        {
    
          if (isset($_POST['UpdateLines']))
          {
        prnMsg(_('Los precios de cada producto deben de ir capturados....'),"error");
          }
          echo '<br><div class="centre"><input type="submit" name="UpdateLines" value="Calcular Totales">';
        }   
    
        if ($errorprecios==0)
        {
          echo '&nbsp;&nbsp;<div class="centre"><input type="submit" name="Commit" style="font-weight:bold;" value=" PROCESAR ORDEN ">';
        }
    
        */
       
        $SQL = "SELECT DISTINCT tb_botones_status.functionid,
                tb_botones_status.statusid,
                tb_botones_status.statusname,
                tb_botones_status.namebutton,
                tb_botones_status.functionid,
                tb_botones_status.adecuacionPresupuestal,
                tb_botones_status.clases,
                tb_botones_status.sn_estatus_siguiente, 
                tb_botones_status.sn_nombre_secundario, purchorderstatus.showname, tb_botones_status.sn_orden
                FROM tb_botones_status
                INNER JOIN purchorderstatus ON tb_botones_status.statusname= purchorderstatus.status
                JOIN sec_profilexuser ON sec_profilexuser.userid = '".$_SESSION['UserID']."'
                JOIN sec_funxprofile ON sec_funxprofile.profileid = sec_profilexuser.profileid
                WHERE 
                (tb_botones_status.sn_funcion_id = '1371')
                AND (tb_botones_status.sn_flag_disponible = 1)
                AND
                (tb_botones_status.functionid = sec_funxprofile.functionid 
                OR 
                tb_botones_status.functionid = (SELECT sec_funxuser.functionid FROM sec_funxuser WHERE sec_funxuser.functionid = tb_botones_status.functionid AND userid = '".$_SESSION['UserID']."')
                )
                ORDER BY sn_orden";
        
        $ErrMsg = "No se obtuvieron los botones para el proceso";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
       
        echo '<br>
        <div class="panel panel-default">
            <div class="panel-body" align="center" id="divBotones" name="divBotones">';

        echo '&nbsp;&nbsp;&nbsp;<a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="panel_ordenes_compra.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a>';

        if ($_SESSION['PO'.$identifier]->separarOrdenCompra == 'P') {
            echo '<component-button type="submit" id="btnGenerarNuevaOrden" name="btnGenerarNuevaOrden" value="Genera Orden de Compra" class="glyphicon glyphicon-plus"></component-button>';
        }

        echo '&nbsp;&nbsp;&nbsp;
            <button type="submit" id="UpdateLines" name="UpdateLines" class="btn btn-default botonVerde glyphicon glyphicon-refresh"> Calcular Totales</button>';

        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array(
            'statusid' => $myrow ['sn_estatus_siguiente'],
            'statusname' => $myrow ['statusname'],
            'namebutton' => $myrow ['namebutton'],
            'functionid' => $myrow ['functionid'],
            'clases' => $myrow ['clases']
            );
            if ($diferenciasSuficienciaTotal == 1 || $urSinAlmacen == 1 || $valSuficienciaEstatus == 1 || $validacionServiciosIguales == 1) {
                if ($myrow ['namebutton'] != 'AutorizaOrden') {
                    echo '&nbsp;&nbsp;&nbsp;
                    <button type="submit" id="'.$myrow ['namebutton'].'" name="'.$myrow ['namebutton'].'" class="btn btn-default botonVerde '.$myrow ['clases'].'"> '.$myrow['showname'].'</button>';
                }
            } else {
                echo '&nbsp;&nbsp;&nbsp;
                <button type="submit" id="'.$myrow ['namebutton'].'" name="'.$myrow ['namebutton'].'" class="btn btn-default botonVerde '.$myrow ['clases'].'"> '.$myrow['showname'].'</button>';
            }
        }
        echo '</div>
        </div>';

        if ($diferenciasSuficienciaTotal == 1 || $urSinAlmacen == 1) {
            $mensaje_emergente .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se puede Autorizar la Orden de Compra</p>';
        }

        echo '<component-button type="submit" id="PendingOrder" name="PendingOrder" value="Guardar" class="glyphicon glyphicon-floppy-disk" style="display: none;"></component-button>';

        echo '<input type="hidden" id="txtRechazoSuficiencia" name="txtRechazoSuficiencia" value="0" />';
    
        /* Ocultar botones, los obtiene de la tabla
        echo '<br><div class="centre">';
        if (Havepermission($_SESSION['UserID'], 1442, $db) == 1) {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;<button type='submit' style='cursor:pointer; border:0; background-color:transparent;' name='DuplicaPartidas' value='DUPLICAR PARTIDAS'>
                                                    <img src='images/duplicar2_25.png' title='DUPLICAR PARTIDAS'>
                                                </button>";
        }
        echo '&nbsp;&nbsp;&nbsp;&nbsp;<button type="submit" style="cursor:pointer; border:0; background-color:transparent;" name="UpdateLines" value="Calcular Totales">
                                                    <img src="images/calcular_25.png" title="CALCULAR TOTALES">
                                                </button>';
        if ($AutCreate == 0) {
            if ($estatusEnPendiente == 1) {
                if (Havepermission($_SESSION['UserID'], 1443, $db) == 1) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;<button type='submit' style='cursor:pointer; border:0; background-color:transparent;' name='PendingOrder' value='SOLICITUD'>
                                                    <img src='images/b_solicitud2.png' title='Buscar'>
                                                </button>";
                }
            } else {
                echo "&nbsp;&nbsp;&nbsp;&nbsp;<input type=submit name=Commit style='font-weight:bold;' value='" . _('PROCESAR ORDEN') . "' style='font-weight:normal;'>";
            }
        }
        if ($AutCancell == 0) {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;<button type='submit' style='cursor:pointer; border:0; background-color:transparent;' name='CancelaOrden' value='CANCELAR'>
                        <img src='images/cancelar_25.png' title='Buscar'>
                    </button>";
        }
        if ($AutDeliver == 0) {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;<button type='submit' style='cursor:pointer; border:0; background-color:transparent;' name='SurteOrden' value='SOLICITAR'>
                        <img src='images/b_solicitar_25.png' title='Buscar'>
                    </button>";
        }
        if ($AutAuthorise == 0) {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' onclick='lockbutton(form, this)' style='cursor:pointer; border:0; background-color:transparent;' name='AutorizaOrden2' value='AUTORIZAR'>
                        <img src='images/autorizar_25.png' title='Autorizar Orden'>
                    </button>";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;<button type='submit' style='cursor:pointer; border:0; background-color:transparent; display:none;' name='AutorizaOrden' value='AUTORIZAR'>
                        <img src='images/autorizar_25.png' title='Autorizar Orden'>
                    </button>";
        
            echo "&nbsp;&nbsp;&nbsp;&nbsp;<button type='submit' style='cursor:pointer; border:0; background-color:transparent;' name='RechazaOrden' value='RECHAZAR'>
                        <img src='images/rechazar_25.png' title='Rechazar Orden'>
                    </button>";
        }
        echo '</div>';*/
    
        if (!isset($_POST['NewItem']) and isset($_GET['Edit'])) {
        }
    }


    if (isset($SearchResult)) {
        echo "<br><br>";

        echo '<div style="width: 100%;  overflow: scroll;">';// Div Scroll

        echo "<table class='table table-bordered' cellspacing=0 align=center border=1 width='100%' bordercolor=lightgray cellpadding=2    colspan=0 style='margin-top:0' colspan=7>";

        // <th>" . _('Código Barra') . "</th>
        // <th>" . _('Costos Adicionales') . "</th>
        // <th>" . _('Desc2') . "</th>
        // <th>" . _('Desc3') . "</th>
        // <th>" . _('Desc X Monto') . "</th>
        
        $tableheader = "<tr class='header-verde'>
                    <th>" . _('Código')  . "</th>
                    <th>" . _('Descripción') . "</th>
                    <th>" . _('Unidades') . "</th>
                    <th>" . _('Ultimo<br>Precio') . "</th>
                    <th><a href='#end'><font color=#ffffff><u>"._('Ir al Final de la lista')."</u></font></a></th>
                    <th>" . _('Exist') . "</th>
                    <th>" . _('Optimo') . "</th>
                    <th>" . _('Transito') . "</th>
                    <th>" . _('Cant') . "</th>
                    <th>" . _('Precio') . "</th>
                    <th>" . _('Desc1') . "</th>
                    </tr>";
        echo $tableheader;

        $j = 1;
        $k=0; //row colour counter

        while ($myrow=DB_fetch_array($SearchResult)) {
            if ($k==1) {
                echo '<tr>'; // bgcolor=#ffffff
                $k=0;
            } else {
                echo '<tr>'; // class="OddTableRows"
                $k=1;
            }

            $filename = $myrow['stockid'] . '.jpg';
            if (file_exists($_SESSION['part_pics_dir'] . '/' . $filename)) {
                $ImageSource = '<img src="'.$rootpath . '/' . $_SESSION['part_pics_dir'] . '/' . $myrow['stockid'] .
                            '.jpg" width="50" height="50">';
            } else {
                $ImageSource = _('No Image');
            }

            $uomsql='SELECT conversionfactor, suppliersuom, price
                FROM purchdata
                WHERE supplierno="'.$_SESSION['PO'.$identifier]->SupplierID.'"
                AND stockid="'.$myrow['stockid'].'"';

            $uomresult=DB_query($uomsql, $db);
            if (DB_num_rows($uomresult)>0) {
                $uomrow=DB_fetch_array($uomresult);
                if (strlen($uomrow['suppliersuom'])>0) {
                    $uom=$uomrow['suppliersuom'];
                } else {
                    $uom=$myrow['units'];
                }
                $precioDatosCompra = $myrow['price'];
                $factordeConversionBusqueda = $myrow['conversionfactor'];
            } else {
                $uom=$myrow['units'];
                $precioDatosCompra = 0;
                $factordeConversionBusqueda = 1;
            }

            $sqlprov="SELECT purchorderdetails.unitprice
                  FROM purchorders INNER JOIN purchorderdetails ON purchorders.orderno=purchorderdetails.orderno
                  AND purchorders.supplierno='".$_SESSION['PO'.$identifier]->SupplierID."'
                  AND purchorderdetails.itemcode='".$myrow['stockid']."'
                  ORDER BY purchorderdetails.orderno DESC
                  LIMIT 1";
            //echo $sqlprov;
            $resulttprov=DB_query($sqlprov, $db);

            if (DB_num_rows($resulttprov)>0) {
                $myrowprov=DB_fetch_array($resulttprov);
                $precioprov=$myrowprov['unitprice'];
            } else {
                $precioprov=0;
                //Si no hay ultima compra, usar el de datos de compra
                $precioprov=$precioDatosCompra;
            }

            /*OBTENER Exist Optimo Transito */
            $sql='SELECT locstock.loccode, 
                        locstock.stockid, 
                        locstock.quantity, 
                        locstock.reorderlevel, 
                        locstock.ontransit,
                        stockmaster.categoryid
                FROM locstock JOIN stockmaster ON locstock.stockid = stockmaster.stockid
                WHERE locstock.stockid = "'.$myrow['stockid'].'"
                        AND locstock.loccode = "'. $_SESSION['PO'.$identifier]->Location .'"';
            $excresult=DB_query($sql, $db);

            if (DB_num_rows($excresult)>0) {
                $excrow=DB_fetch_array($excresult);
            }

            if ($_SESSION['AplicaDevolucion']==1) {
                $percentdevolucion=TraePercentDevXSupplier($_SESSION['PO'.$identifier]->SupplierID, $myrow['stockid'], $myrow['manufacturer'], $_SESSION['PO'.$identifier]->DefaultSalesType, $db);
                //echo $percentdevolucion;
                $separa = explode('|', $percentdevolucion);
                $Devolucion = $separa[0]*100;
                $Discount=$separa[1]*100;
                $totalsale=$separa[2];
            } else {
                $Discount='';
            }

            //$Discount=100;
            
            // <td class='texto_normal3'>%s</td>
            // <td class='numero_normal'><input class='number' type='text' size=10 name='estimated_cost%s'></td>
            // <td class='numero_normal'><input class='number' type='text' size=6 name='desc2%s'></td>
            // <td class='numero_normal'><input class='number' type='text' size=6 name='desc3%s'></td>
            // <td class='numero_normal'><input class='number' type='text' size=12 name='descmonto%s'></td>
            
            // $myrow['barcode'],
            // $myrow['stockid'],
            // $myrow['stockid'],
            // $myrow['stockid'],
            // $myrow['stockid']
            printf(
                "<td class='texto_normal3'>%s</td>
                <td class='texto_normal3'>%s</td>
                <td class='texto_normal3'>%s</td>
                <td class='numero_celda'>%s</td>
                <td class='texto_normal3'>%s</td>
                <td class='numero_normal'>%s</td>
                <td class='numero_normal'>%s</td>
                <td class='numero_normal'>%s</td>
                <td class='numero_normal'><input class='number' type='text' size=6 name='qty%s'></td>
                <td class='numero_normal'><input class='number' type='text' size=10 name='price%s'></td>
                <td class='numero_normal'><input class='number' type='text' size=6 name='desc1%s' value='%s'></td>
                </tr>",
                $myrow['stockid'],
                strtoupper($myrow['description']),
                $uom,
                '$'.number_format($precioprov, 2),
                $ImageSource,
                $excrow['quantity'],
                $excrow['reorderlevel'],
                $excrow['ontransit'],
                $myrow['stockid'],
                $myrow['stockid'],
                $myrow['stockid'],
                $Discount
            );

            $PartsDisplayed++;
            if ($PartsDisplayed == $Maximum_Number_Of_Parts_To_Show) {
                break;
            }
            #end of page full new headings if
        } #end of while loop

        echo '</table>';

        echo '</div>';// Div Scroll

        if ($PartsDisplayed == $Maximum_Number_Of_Parts_To_Show) {
        /*$Maximum_Number_Of_Parts_To_Show defined in config.php */

            prnMsg(_('Solo los primeros') . ' ' . $Maximum_Number_Of_Parts_To_Show . ' ' . _('registros pueden desplegarse') . '. ' .
            _('Por favor limita los criterios de b�squeda...'), 'info');
        }
        echo '<a name="end"></a><br>
                <div class="centre">
                    <button type="submit" style="cursor:pointer; border:0; background-color:transparent;" name="NewItem" value="ORDENAR">
                        <img src="images/agregar2_25.png" title="ORDENAR">
                    </button>
                </div>';
    } #end if SearchResults to show

    if (isset($_POST['NonStockOrder'])) {
        echo '<br><br>
                <fieldset class="cssfieldset" style="width:30%">
                <table>
                    <tr>
                        <td class="texto_lista">'._('Descripci&oacute;n:').'</td>';
        echo '              <td><input type=text name=ItemDescription size=40></td>
                    </tr>';
        echo '          <tr>
                        <td class="texto_lista">'._('Cuenta Contable:').'</td>';
        echo '              <td><select name="GLCode">';
    
    
        $sql="SELECT 
            chartmaster.accountcode,
            chartmaster.accountname,
            chartmaster.group_,
            accountxsupplier.concepto as concepto
          FROM accountxsupplier JOIN chartmaster ON accountxsupplier.accountcode = chartmaster.accountcode
          WHERE accountxsupplier.supplierid = '".$supplierid."'
          ORDER BY accountxsupplier.concepto ASC";
    
        /*
        $sql = "SELECT purchorderauth.authlevel, 
            purchorderauth.category, 
            purchorderauth.account as accountcode, 
            chartpurch.concept as accountname
        FROM purchorderauth JOIN chartpurch ON purchorderauth.account = chartpurch.accountcode
        WHERE purchorderauth.userid = '".$_SESSION['UserID']."'";
                       */
          
    
        $result=DB_query($sql, $db);
        if (DB_num_rows($result) > 0) {
            while ($myrow=DB_fetch_array($result)) {
                echo '<option value="'.$myrow['accountcode'].'">'.$myrow['concepto'].'</option>';
            }
        } else {
            // SI NO TIENE ELEMENTOS DESPLEGAR PROCEDIMIENTO PARA OBTENERLOS
            echo 'Configurar cuentas contables de gastos para este proveedor...';
        }
    
        echo '              </td>
                    </tr>';
        echo '          <tr>
                        <td class="texto_lista">'._('Cantidad:').'</td>';
        echo '              <td><input type=text class=number name=Qty size=10></td>
                    </tr>';
        echo '          <tr><td class="texto_lista">'._('Precio Unitario:').'</td>';
        echo '              <td><input type=text class=number name=Price size=10></td>
                    </tr>';
        echo '          <tr><td class="texto_lista">'._('Fecha de Entrega:').'</td>';
        echo '              <td><input type=text class=date alt="'.$_SESSION['DefaultDateFormat'].'" name=ReqDelDate size=11
                            value="'.$_SESSION['PO'.$identifier]->deliverydate .'"></td>
                    </tr>';
        echo '      </table>
            </fieldset>';
        echo '      <table align="center"><tr><td>';
        echo '<button type="submit" style="cursor:pointer; border:0; background-color:transparent;" name="EnterLine" value="Registrar">
                        <img src="images/guardar_net_25.png" title="REGISTRAL">
                    </button>';
        echo '      </td></tr></table>';
    //  echo '<input type=submit name="EnterLine" value="Registrar">';
    }

//echo '<hr>';

/* Now show the stock item selection search stuff below */
    $InputErrorAdd=1;
    if ($_SESSION['PO'.$identifier]->Wo!=0) {
        if ($permiso_addprod_ot==0) {
            $InputErrorAdd=0;
            echo '<br><hr><div style="text-align:center"></div>';
        }
    }
    if (!isset($_GET['Edit']) and $InputErrorAdd==1) {
        #$sql="SELECT categoryid, categorydescription FROM stockcategory WHERE stocktype<>'L' AND stocktype<>'D' ORDER BY categorydescription";
    
        /*$sql='SELECT sto.categoryid, categorydescription FROM stockcategory sto, sec_stockcategory sec
        WHERE /*stocktype<>"L" AND stocktype<>"D" AND */ /*sto.categoryid=sec.categoryid AND userid="'.$_SESSION['UserID'].'" ORDER BY categorydescription';*/
        $sql = 'SELECT s.categoryid,s.stocktype, s.categorydescription,
                             ProdGroup.Description as grupo,
                    ProdLine.Description as linea ';
        $sql .= ' FROM stockcategory s
                    INNER JOIN  ProdLine ON s.prodLineId=ProdLine.Prodlineid
                    INNER JOIN  ProdGroup ON  ProdGroup.Prodgroupid=ProdLine.Prodgroupid
                    , sec_stockcategory sxu ';
        $sql .= ' WHERE s.categoryid = sxu.categoryid';
        $sql .= ' AND sxu.userid="'.$_SESSION['UserID'].'"
                    ORDER BY ProdGroup.Description ,ProdLine.Description,s.categorydescription
                    ';
        $ErrMsg = _('The supplier category details could not be retrieved because');
        $DbgMsg = _('The SQL used to retrieve the category details but failed was');
        $result1 = DB_query($sql, $db, $ErrMsg, $DbgMsg);
        //echo "<tr><td>";
        echo '<table border=0 width=80% CELLSPACING=0 cellpadding="0">';
      
    
        /*echo'<tr><td colspan=2><br><p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' .
        _('Buscar') . '" alt="">' . ' ' . _('Alta de Productos a la orden de compra') . '';

        echo "</font></td></tr>";*/
        echo "<tr>";
        echo '<td>';

        echo '<div class="container" '.$ocultaBuscarEntrada.'>
                <div class="panel panel-default col-lg-12 col-md-12 col-sm-12 p0 m0">
                    <div class="panel-heading" role="tab" id="headingOne">
                      <h4 class="panel-title row">
                        <div class="col-md-6 col-xs-6 text-left">
                          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBuscarProductos" aria-expanded="true" aria-controls="collapseOne">
                            <b>Buscar Productos</b>
                          </a>
                        </div>
                      </h4>
                    </div>
                    <div id="PanelBuscarProductos" name="PanelBuscarProductos" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                    <div class="panel-body">';
        echo '<div class="col-md-12">
                <div class="form-inline row">
                  <div class="col-md-3" style="vertical-align: middle;">
                      <span><label>Categoría: </label></span>
                  </div>
                  <div class="col-md-9">
                    <select name="StockCat" id="StockCat" class="StockCat">';
        $grupoANT='';
        $lineaANT='';
        if ($_POST['StockCat'] == "All") {
            echo '<option selected value="All">' . _('Todas las categorias') . '</option>';
        } else {
            echo '<option value="All">' . _('Todas categorias') . '</option>';
        }
        while ($myrowCAT=DB_fetch_array($result1)) {
            if ($grupoANT!=$myrowCAT['grupo']) {
                echo '<option VALUE="*****">*' . $myrowCAT['grupo'].'</option>';
            }
        
            if ($lineaANT!=$myrowCAT['linea']) {
                echo '<option VALUE="+++++"><b>&nbsp;&nbsp;--' . $myrowCAT['linea']. '</b></option>';
            }
    
            if ($myrowCAT['categoryid']==$_POST['StockCat']) {
                echo '<option selected VALUE="'. $myrowCAT['categoryid'] . '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $myrowCAT['categorydescription'] . '</option>';
            } else {
                echo '<option VALUE="'. $myrowCAT['categoryid'] . '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $myrowCAT['categorydescription'] . '</option>';
            }
    
            $grupoANT=$myrowCAT['grupo'];
            $lineaANT=$myrowCAT['linea'];
        }
                echo '</select>';
            echo "</div>";
        echo '</div>
            </div>';
        echo '<div class="col-md-12">';
            echo '<br>';
            echo '<component-text-label label="Título del Producto: " id="Keywords" name="Keywords" value="'.$_POST['Keywords'].'" maxlength="25"></component-text-label>';
        echo '</div>';
        echo '<div class="col-md-12">';
            echo '<br>';
            echo '<component-text-label label="Código del Producto: " id="StockCode" name="StockCode" value="'.$_POST['StockCode'].'" maxlength="18"></component-text-label>';
        echo '</div>';
        echo '<div class="col-md-12">
                <br>
                <div class="form-inline row">
                  <div class="col-md-3" style="vertical-align: middle;">
                      <span><label>Moneda: </label></span>
                  </div>
                  <div class="col-md-9">
                    <select name="CurrCode" id="CurrCode" class="CurrCode">';
        $sql2 = "SELECT currencies.currabrev,
                currencies.currency
         FROM currencies";
        $result2 = DB_query($sql2, $db);
        echo "<option selected value='*'>Todas las monedas</option>";
    
        while ($myrow2 = DB_fetch_array($result2)) {
            if ($myrow2['currabrev'] == $_POST['CurrCode']) {
                echo "<option selected value='".$myrow2['currabrev']."'>".$myrow2['currency']."</option>";
            } else {
                echo "<option value='".$myrow2['currabrev']."'>".$myrow2['currency']."</option>";
            }
        }
                echo '</select>';
            echo "</div>";
        echo '</div>
            </div>';
        echo '<div class="col-md-12" align="center">';
        echo '<br>';
        echo '<a target="_blank" href="'.$rootpath.'/Stocks.php"><img src="images/nuevo_pro_25.png" title="Registrar Nuevo Producto"></a>';
        echo '<button type="submit" style="cursor:pointer; border:0; background-color:transparent;" name="Search" value="Buscar Productos">
            <img src="images/buscar_prod_25.png" title="Buscar">
         </button>
         <button type="submit" style="cursor:pointer; border:0; background-color:transparent;" name="NonStockOrder" value="Buscar Productos">
            <img src="images/pdto_sin_inventario_25.png" title="Productos Sin Control de Inventario">
         </button>';
        echo '</div>';
        echo '      </div>
                </div>
            </div>';

        echo '</td>';

        echo '<td>';

        echo '<div class="container" '.$ocultaBuscarEntrada.'>
                <div class="panel panel-default col-lg-12 col-md-12 col-sm-12 p0 m0">
                    <div class="panel-heading" role="tab" id="headingOne">
                      <h4 class="panel-title row">
                        <div class="col-md-6 col-xs-6 text-left">
                          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelEntradaRapida" aria-expanded="true" aria-controls="collapseOne">
                            <b>Entrada Rapida</b>
                          </a>
                        </div>
                      </h4>
                    </div>
                    <div id="PanelEntradaRapida" name="PanelEntradaRapida" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                    ';
        echo '<input type="hidden" name="PartSearch" value="Yes">';
        echo '<input type="hidden" name="CurrAbrev" value="'.$_SESSION['CurrAbrev'].'">';
        echo '<input type="hidden" name="Tagref" value="'.$_SESSION['Tagref'] .'">';

        echo '<table class="table table-bordered">
            <tr class="header-verde">';
        echo '<th>' . _('Código') . '</th>
              <th>' . _('Cantidad') . '</th>
              <th>' . _('Precio') . '</th>                        
        </tr>';
        $_SESSION['QuickEntries']=10;
        for ($i=1; $i<=$_SESSION['QuickEntries']; $i++) {
            echo '<tr>';
            echo '  <td>
                        <component-text name="part_' . $i . '" id="part_' . $i . '" maxlength="20"></component-text>
                    </td>
                    <td>
                        <component-number name="qty_' . $i . '" id="qty_' . $i . '" maxlength="6"></component-number>
                    </td>
                    <td>
                        <component-number name="price_' . $i . '" id="price_' . $i . '" maxlength="15"></component-number>
                    </td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '<br><div align="center">
                <input type=hidden name="lineaxs" value='.$_SESSION['QuickEntries'].'>
                <button type="submit" style="cursor:pointer; border:0; background-color:transparent;" name="QuickEntry" value="AGREGAR">
                    <img src="images/agregar2_25.png" title="AGREGAR">
                </button>
            </div>';
        echo '      
                </div>
            </div>';
    
    
        echo '</td>';

        echo '</tr>';

        echo '</table>';

        $PartsDisplayed =0;
    }


    if (isset($SearchResult) && false == true) {
        echo "<table cellpadding=2 colspan=7 border=1>";

        $tableheader = "<tr>
            <th>" . _('C&oacute;digo')  . "</th>
            <th>" . _('C&oacute;digo Barra') . "</th>
            <th>" . _('Descripci&oacute;n') . "</th>
            <th>" . _('Unidades') . "</th>
            <th>" . _('Ultimo<br>Precio') . "</th>
            <th><a href='#end'>"._('Ir al Final de la lista')."</a></th>
            <th>" . _('Exist') . "</th>
            <th>" . _('Optimo') . "</th>
            <th>" . _('Transito') . "</th>
            <th>" . _('Cant') . "</th>
            <th>" . _('Precio') . "</th>
            <th>" . _('Costos Adicionales') . "</th>
            <th>" . _('Desc1') . "</th>
            <th>" . _('Desc2') . "</th>
            <th>" . _('Desc3') . "</th>
            <th>" . _('Desc X Monto') . "</th>
            </tr>";
        echo $tableheader;

        $j = 1;
        $k=0; //row colour counter

        while ($myrow=DB_fetch_array($SearchResult)) {
            if ($k==1) {
                echo '<tr class="EvenTableRows">';
                $k=0;
            } else {
                echo '<tr class="OddTableRows">';
                $k=1;
            }

            $filename = $myrow['stockid'] . '.jpg';
            if (file_exists($_SESSION['part_pics_dir'] . '/' . $filename)) {
                $ImageSource = '<img src="'.$rootpath . '/' . $_SESSION['part_pics_dir'] . '/' . $myrow['stockid'] .
                            '.jpg" width="50" height="50">';
            } else {
                $ImageSource = _('No Image');
            }

            $uomsql='SELECT conversionfactor, suppliersuom, price
                    FROM purchdata
                    WHERE supplierno="'.$_SESSION['PO'.$identifier]->SupplierID.'"
                    AND stockid="'.$myrow['stockid'].'"';

            $uomresult=DB_query($uomsql, $db);
            if (DB_num_rows($uomresult)>0) {
                $uomrow=DB_fetch_array($uomresult);
                if (strlen($uomrow['suppliersuom'])>0) {
                    $uom=$uomrow['suppliersuom'];
                } else {
                    $uom=$myrow['units'];
                }
                $precioDatosCompra = $myrow['price'];
                $factordeConversionBusqueda = $myrow['conversionfactor'];
            } else {
                $uom=$myrow['units'];
                $precioDatosCompra = 0;
                $factordeConversionBusqueda = 1;
            }
            
            $sqlprov="SELECT purchorderdetails.unitprice
                      FROM purchorders INNER JOIN purchorderdetails ON purchorders.orderno=purchorderdetails.orderno
                      AND purchorders.supplierno='".$_SESSION['PO'.$identifier]->SupplierID."'
                      AND purchorderdetails.itemcode='".$myrow['stockid']."'
                      ORDER BY purchorderdetails.orderno DESC
                      LIMIT 1";
            //echo $sqlprov;
            $resulttprov=DB_query($sqlprov, $db);
            
            if (DB_num_rows($resulttprov)>0) {
                $myrowprov=DB_fetch_array($resulttprov);
                $precioprov=$myrowprov['unitprice'];
            } else {
                $precioprov=0;
                //Si no hay ultima compra, usar el de datos de compra
                $precioprov=$precioDatosCompra;
            }
            
            /*OBTENER Exist Optimo Transito */
            $sql='SELECT locstock.loccode, 
                            locstock.stockid, 
                            locstock.quantity, 
                            locstock.reorderlevel, 
                            locstock.ontransit,
                            stockmaster.categoryid
                    FROM locstock JOIN stockmaster ON locstock.stockid = stockmaster.stockid
                    WHERE locstock.stockid = "'.$myrow['stockid'].'"
                            AND locstock.loccode = "'. $_SESSION['PO'.$identifier]->Location .'"';
            $excresult=DB_query($sql, $db);

            if (DB_num_rows($excresult)>0) {
                $excrow=DB_fetch_array($excresult);
            }
                        
            if ($_SESSION['AplicaDevolucion']==1) {
                $percentdevolucion=TraePercentDevXSupplier($_SESSION['PO'.$identifier]->SupplierID, $myrow['stockid'], $myrow['manufacturer'], $_SESSION['PO'.$identifier]->DefaultSalesType, $db);
                //echo $percentdevolucion;
                $separa = explode('|', $percentdevolucion);
                $Devolucion = $separa[0]*100;
                $Discount=$separa[1]*100;
                $totalsale=$separa[2];
            } else {
                $Discount='';
            }
            
            //$Discount=100;
            printf(
                "<td><font size=1>%s</td>
            <td><font size=1>%s</td>
            <td><font size=1>%s</td>
            <td><font size=1>%s</td>
            <td class=number ><font size=1>%s</td>
            <td><font size=1>%s</td>
            <td style='text-align:center'><font size=1>%s</td>
            <td style='text-align:center'><font size=1>%s</td>
            <td style='text-align:center'><font size=1>%s</td>
            <td><input class='number' type='text' size=6 name='qty%s'></td>
            <td><input class='number' type='text' size=10 name='price%s'></td>
            <td><input class='number' type='text' size=10 name='estimated_cost%s'></td>
            <td><input class='number' type='text' size=6 name='desc1%s' value='%s'></td>
            <td><input class='number' type='text' size=6 name='desc2%s'></td>
            <td><input class='number' type='text' size=6 name='desc3%s'></td>
            <td><input class='number' type='text' size=12 name='descmonto%s'></td>
            </tr>",
                $myrow['stockid'],
                $myrow['barcode'],
                strtoupper($myrow['description']),
                $uom,
                '$'.number_format($precioprov, 2),
                $ImageSource,
                $excrow['quantity'],
                $excrow['reorderlevel'],
                $excrow['ontransit'],
                $myrow['stockid'],
                $myrow['stockid'],
                $myrow['stockid'],
                $myrow['stockid'],
                $Discount,
                $myrow['stockid'],
                $myrow['stockid'],
                $myrow['stockid']
            );

                $PartsDisplayed++;
            if ($PartsDisplayed == $Maximum_Number_Of_Parts_To_Show) {
                break;
            }
            #end of page full new headings if
        } #end of while loop
    
        echo '</table>';
    
        if ($PartsDisplayed == $Maximum_Number_Of_Parts_To_Show) {
        /*$Maximum_Number_Of_Parts_To_Show defined in config.php */

            prnMsg(_('Solo los primeros') . ' ' . $Maximum_Number_Of_Parts_To_Show . ' ' . _('registros pueden desplegarse') . '. ' .
            _('Por favor limita los criterios de b�squeda...'), 'info');
        }
        echo '<a name="end"></a><br><div class="centre"><input type="submit" name="NewItem" value="ORDENAR"></div>';
    }
    //echo '<hr>';
    echo '</form>';
    //print_r($_SESSION['PO'.$identifier]);
    include('includes/footer_Index.inc');
    //print_r($_SESSION['PO'.$identifier]);
    if ($procesoterminado != 0) {
        fnmuestraModalGeneral($procesoterminado, $mensaje_emergente);

        // Renderizar componentes del mensaje
        echo "<script>";
        echo 'fnEjecutarVueGeneral(\'ModalGeneral_Mensaje\');';
        echo "</script>";
    }
?>
<script>
    var ventana = null;
    var ventanaBig = null;

    /**
     * Función para regresar al panel
     * @return {[type]} [description]
     */
    function fnRegresarPanel() {
        window.open("panel_ordenes_compra.php", "_self");
    }

    /**
     * Función para peracion al rezachar la suficiencia
     * @param  integer type          Tipo de Documento
     * @param  integer transno       Folio del Documento
     * @param  integer requisitionno Número de Requisición
     * @return {[type]}               [description]
     */
    function fnRechazarSuficiencia(type, transno, requisitionno) {
        $('#txtRechazoSuficiencia').val('1');

        dataObj = { 
            option: 'rechazarSuficienciaOrdenCompra',
            type: type,
            transno: transno,
            requisitionno: requisitionno
        };
        $.ajax({
            async:false,
            cache:false,
            method: "POST",
            dataType:"json",
            url: "modelo/suficiencia_manual_panel_modelo.php",
            data:dataObj
        })
        .done(function( data ) {
            //console.log("Bien");
            if(data.result){
                //Si trae informacion
                //ocultaCargandoGeneral();
                var notificacion = '<div class="alert alert-success alert-dismissable">' + '<button type="button" class="close" data-dismiss="alert">&times;</button>' + '<p>' + data.contenido.mensaje + '</p>' + '</div>';
                //$('#ModalGeneral_Advertencia').empty();
                //$('#ModalGeneral_Advertencia').append(notificacion);
                // fnRegresarPanel();
                var Link_Panel = document.getElementById("PendingOrder");
                Link_Panel.click();
            }else{
                //ocultaCargandoGeneral();
                var notificacion = '<div class="alert alert-danger alert-dismissable">' + '<button type="button" class="close" data-dismiss="alert">&times;</button>' + '<p>' + data.contenido.mensaje + '</p>' + '</div>';
                //$('#ModalGeneral_Advertencia').empty();
                //$('#ModalGeneral_Advertencia').append(notificacion);
                // fnRegresarPanel();
                var Link_Panel = document.getElementById("PendingOrder");
                Link_Panel.click();
            }
        })
        .fail(function(result) {
            ocultaCargandoGeneral();
            //console.log("ERROR");
            //console.log( result );
        });
    }
    
    function openNewWindow(url){
        if (ventana==null || ventana.closed)
            ventana = window.open(url,'','width=750, height=500'); //     500 y 200
        else
            alert('Esta funcion ya se esta ejecuntando, favor de cerrarl la ventana antes de abrir otra');  
    }

    function openNewWindowBig(url){
        if (ventanaBig==null || ventanaBig.closed)
            ventana = window.open(url,'','width=500,height=600'); 
        else
            alert('Esta funcion ya se esta ejecuntando, favor de cerrarl la ventana antes de abrir otra');  
    }
    // Aplicar formato del SELECT
    fnFormatoSelectGeneral(".selMoneda");
    fnFormatoSelectGeneral(".FromDia");
    fnFormatoSelectGeneral(".FromMes");
    fnFormatoSelectGeneral(".servicetype");
    fnFormatoSelectGeneral(".recibOCProd");
    fnFormatoSelectGeneral(".StockCat");
    fnFormatoSelectGeneral(".CurrCode");
    fnFormatoSelectGeneral(".DiaEmb");
    fnFormatoSelectGeneral(".MesEmb");
    fnFormatoSelectGeneral(".DiaFechaAduana");
    fnFormatoSelectGeneral(".MesFechaAduana");
    fnFormatoSelectGeneral(".noag_ad");
    fnFormatoSelectGeneral(".selectProveedorCambiar");
    fnFormatoSelectGeneral(".selectAlmacenOrdenCompra");
</script>
