<?php
// Se sube a grupo sii, jibe, placa
//mg, server 16
if($_SESSION['UserID'] == "desarrollo"){
    /*ini_set('display_errors', 1); 
    ini_set('log_errors', 1); */
    //ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); 
    //error_reporting(E_ALL);
}
if(isset($_GET['error'])){
    ini_set('display_errors', 1); 
    ini_set('log_errors', 1); 
    error_reporting(E_ALL);
    //ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); 
}
//Se agrega pra evitar que se muestre el hecho de tipo de cambio
// se hace modificacion para quitar caracteres especiales
// se agrega Pymas para mostrar el codigo del cliente
// Se aagregó el num de cliente a jibe y matel
// para sustitucion a produccion
 
$reportepdf= true;
$PageSecurity = 1;
include('config.php');
include ('includes/session.inc');

if (isset($_GET['Type']) && $_GET['Type'] == '12') {
    // Es recibo de pago
    
    // include('includes/session.inc');
    $PrintPDF = $_GET ['PrintPDF'];
    $_POST ['PrintPDF'] = $PrintPDF;
    include('jasper/JasperReport.php');
    include("includes/SecurityUrl.php");

    $logo_legal = $_SESSION['LogoFile'];
   

    $SQL = "SET NAMES 'utf8'";
    $TransResult = DB_query($SQL, $db);

    $SQL = "SELECT legalbusinessunit.logo, debtortransFactura.order_, www_users.realname, '' as cajaName,
    DATE_FORMAT(debtortrans.trandate, '%d/%m/%Y') as fechaRecibo,
    DATE_FORMAT(debtortrans.trandate, '%H:%i:%s') as horaRecibo,
    salesorders.comments,
    www_users.obraid as caja,
    debtortransFactura.nocuenta
    FROM debtortrans
    INNER JOIN tags ON debtortrans.tagref = tags.tagref
    INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
    JOIN custallocns ON custallocns.transid_allocfrom = debtortrans.id
    JOIN debtortrans debtortransFactura ON debtortransFactura.id = custallocns.transid_allocto
    JOIN www_users ON www_users.userid = debtortrans.userid
    JOIN salesorders ON salesorders.orderno = debtortransFactura.order_
    WHERE debtortrans.transNo = '".$_GET['TransNo']."' and debtortrans.type = '".$_GET['Type']."'";
    $rs = DB_query($SQL,$db);
    if ($myrows = DB_fetch_array($rs)) {
        $jreport= "";
        $JasperReport = new JasperReport($confJasper);
        $jreport = $JasperReport->compilerReport("/rptReciboPago");

        $JasperReport->addParameter("pCaja", $myrows["caja"]);
        $JasperReport->addParameter("pUsuario", $myrows["realname"]);
        $JasperReport->addParameter("pDocumentoPago", $myrows["order_"]);
        $JasperReport->addParameter("transno", $_GET['TransNo']);
        $JasperReport->addParameter("fechaRecibo", $myrows["fechaRecibo"]);
        $JasperReport->addParameter("horaRecibo", $myrows["horaRecibo"]);
        $JasperReport->addParameter("comments", $myrows["comments"]);
        $JasperReport->addParameter("referencia", $myrows["nocuenta"]);

        $conexion = $JasperReport->getConexionDB($_SESSION ["DatabaseName"], $dbuser, $dbpassword);
        $jPrint = $JasperReport->fillReport($jreport, $JasperReport->getParameters(), $conexion);
        $pdfBytes = $JasperReport->exportReportPDF($jPrint);

        header('Content-type: application/pdf');
        header('Content-Length: ' . strlen($pdfBytes));
        header('Content-Disposition: inline; filename=report.pdf');

        echo $pdfBytes;
    } else {
        echo "<h3>Pago Cancelado</h3>";
    }
    exit();
}

$sql3 = "SELECT legalbusinessunit.tipologo, legalbusinessunit.mensaje, legalbusinessunit.logoAlterno
        FROM debtortrans
        INNER JOIN tags ON debtortrans.tagref = tags.tagref
        INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
        WHERE transNo=" . $_GET['TransNo'] . " and type=" . $_GET['Type'];

if (isset($_GET['OrderNo'])) {
    $OrderNo = $_GET['OrderNo'];
} else {
    $OrderNo = "";
}
$mCliente =0;
if(isset($_GET['mostrarCodigoCliente']))
{
    $mCliente = (int)$_GET['mostrarCodigoCliente'];
}
if($_SESSION['DatabaseName'] == 'erpplacacentro_DES' or $_SESSION['DatabaseName'] == 'erpplacacentro_CAPA' or $_SESSION['DatabaseName'] == 'erpplacacentro' OR $_SESSION['DatabaseName'] == 'erptycqsa_DES' or $_SESSION['DatabaseName'] == 'erptycqsa_CAPA' or $_SESSION['DatabaseName'] == 'erptycqsa' OR $_SESSION['DatabaseName'] == 'erppymas_DES' or $_SESSION['DatabaseName'] == 'erppymas_CAPA' or $_SESSION['DatabaseName'] == 'erppymas' OR $_SESSION['DatabaseName'] == 'erpgruposii_DES' or $_SESSION['DatabaseName'] == 'erpgruposii_CAPA' or $_SESSION['DatabaseName'] == 'erpgruposii' OR $_SESSION['DatabaseName'] == 'erpjibe_DES' or $_SESSION['DatabaseName'] == 'erpjibe_CAPA' or $_SESSION['DatabaseName'] == 'erpjibe' OR $_SESSION['DatabaseName'] == 'erpmatelpuente_DES' OR $_SESSION['DatabaseName'] == 'erpmatelpuente_CAPA' OR $_SESSION['DatabaseName'] == 'erpmatelpuente'){
    $mCliente =1;
}
//
//

$ErrMsg = _('El Sql que fallo fue');
$DbgMsg = _('No se pudo saber si ha sido cancelado');

$result3 = DB_query($sql3, $db);

$row4 = DB_fetch_array($result3);
$flaglogo = intval($row4['tipologo']);
$mensaje = strval($row4['mensaje']);

    $facturacliente = intval($_SESSION["FacturaCliente"]);    

$logoalterno = $row4["logoAlterno"];
$mostrardes = 1;

if (isset($_SESSION["MostrarDescuentos"])) {
    $mostrardes = intval($_SESSION["MostrarDescuentos"]);
}
// Consulta para sacar los pagares que tiene la factura
$sql4 = "SELECT date(d2.trandate) as trandate ,
               truncate(d2.ovamount+d2.ovgst,2) AS total
        FROM debtortrans
        INNER JOIN debtortrans d2 ON d2.type=70
        AND d2.order_=debtortrans.transno
        WHERE debtortrans.type=10
          AND debtortrans.transno=" . $_GET['TransNo'] . "
        ORDER BY d2.id";

$ErrMsg = _('El Sql que fallo fue');
$DbgMsg = _('No se pudo saber si ha sido cancelado');

$result4 = DB_query($sql4, $db);
$pagares = "";

for ($i = 1; $i <= DB_num_rows($result4); $i++) {
    $registro = DB_fetch_array($result4);
    
    // $fecha= date_create($registro["trandate"]);
    // $formato= date_format($fecha, "d/m/Y");
    $pagares.= "No: " . $i . " Vence: " . $registro["trandate"] . " \$" . $registro["total"] . "  ";
}

if(isset($_GET['sustitucion'] ) AND $_GET['sustitucion'] =='1'){
    $sqlxml = "SELECT Xmls.xmlImpresion,
                   Xmls.fiscal
            FROM   log_cancelacion_sustitucion AS Xmls
            WHERE TYPE = '" . $_GET['Type'] . "'
              AND transNo = '" . $_GET['TransNo'] . "'
              AND (Xmls.xmlImpresion IS NOT NULL
                   AND Xmls.xmlImpresion <> '')";

}else{
    $sqlxml = "SELECT Xmls.xmlImpresion,
                   Xmls.fiscal
            FROM Xmls
            WHERE TYPE = '" . $_GET['Type'] . "'
              AND transNo = '" . $_GET['TransNo'] . "'
              AND (Xmls.xmlImpresion IS NOT NULL
                   AND Xmls.xmlImpresion <> '')";

}


$resultxml = DB_query($sqlxml, $db);

if (DB_num_rows($resultxml) > 0) {
    $existe = 1;
    $rowfiscal = DB_fetch_array($resultxml);
    $flagfiscal = $rowfiscal[1];
} else {
    $existe = 0;
}
// validacion para que siempre tomo el formato impreso de multiservice
if ($_SESSION['DatabaseName'] == "erpmservice_DES" or $_SESSION['DatabaseName'] == "erpmservice_CAPA" or $_SESSION['DatabaseName'] == "erpmservice") {
    
    //if($_GET ['Type'] != 13){
    $existe = 0;
    //
    //}
    
}
$BDS = array("erpplacacentro" => "erpplacacentro", 
            "erpplacentro_CAPA" => "erpplacentro_CAPA", 
            "erpplacacentro_DES" => "erpplacacentro_DES", 
            "erptmi" => "erptmi", 
            "erptmi_CAPA" => "erptmi_CAPA", 
            "erptmi_DES" => "erptmi_DES", 
            "gruposervillantas_DES" => "gruposervillantas_DES", 
            "gruposervillantas_2_DES" => "gruposervillantas_2_DES", 
            "gruposervillantas" => "gruposervillantas", 
            "erpmservice_DES" => "erpmservice_DES", 
            "erpmservice" => "erpmservice", 
            "erpmservice_CAPA" => "erpmservice_CAPA", 
            "servillantas" => "servillantas"
);

if ($existe == 0) {
    
    if (in_array($_SESSION['DatabaseName'], $BDS) == true) {
        
        // die("entra aqui");
        $sql2 = "SELECT debtortrans.id
            FROM debtortrans
            WHERE debtortrans.uuid <> ''
            ORDER BY debtortrans.id asc
            LIMIT 1";
         //
        $result2 = DB_query($sql2, $db);
        $myrow2 = DB_fetch_array($result2);
        $primerid = $myrow2['id'];
        $sql2 = "SELECT debtortrans.id
             FROM debtortrans
             WHERE debtortrans.type = '" . $_GET['Type'] . "'
             AND debtortrans.transno = " . $_GET['TransNo'];
        $result2 = DB_query($sql2, $db);
        $myrow2 = DB_fetch_array($result2);
        $id = $myrow2['id'];
        
        // Si es la base de datos de grupo servillantas
        if (preg_match("/gruposervillantas/", $_SESSION['DatabaseName'])) {
            if ($_GET['Type'] == 12) {
                 // Es recibo ...
                include_once ('PDFReceiptServillantas.php');
            } 
            elseif ($_GET['Type'] == 11) {
                 // Es nota devolucion ...
                include_once ('PDFNoteCreditDirectServillantas.php');
            } 
            else {
                include_once ('PDFInvoiceServillantas.php');
            }
        } 
        elseif (preg_match("/servillantas/", $_SESSION['DatabaseName'])) {
            if ($_GET['Type'] == 12) {
                 // Es recibo ...
                include_once ('PDFReceiptServillantas.php');
            } 
            elseif ($_GET['Type'] == 11) {
                 // Es nota devolucion ...
                include_once ('PDFNoteCreditDirectServillantas.php');
            } 
            elseif ($_GET['Type'] == 13 or $_GET['Type'] == 21) {
                 // Es nota directa ...
                
                include_once ('PDFCreditDirect.php');
            } 
            else {
                include_once ('PDFInvoiceServillantas.php');
            }
        }
        if (preg_match("/mservice/", $_SESSION['DatabaseName'])) {
            
            if ($_GET['Type'] == 12) {
                 // Es recibo ...
                include_once ('PDFReceiptMService.php');
            } 
            elseif ($_GET['Type'] == 11) {
                 // Es nota devolucion ...
                include_once ('PDFNoteCreditDirectServillantas.php');
            } 
            elseif ($_GET['Type'] == 13 or $_GET['Type'] == 21) {
                 // Es nota directa ...
                include_once ('PDFCreditDirect.php');
            } 
            else {
                include_once ('PDFInvoiceMService.php');
            }
        } 
        elseif ($primerid > $id) {
            
            include ('includes/SQL_CommonFunctions.inc');
            include ('Numbers/Words.php');
            include ('includes/SendInvoicing.inc');
            include ('includes/PDFInvoicePlacacentro.inc');
        } 
        else {
            
            if (preg_match("/mservice/", $_SESSION['DatabaseName'])) {
                
                if ($_GET['Type'] == 12) {
                     // Es recibo ...
                    // error_reporting(E_ALL);
                    // ini_set('display_errors', '1');
                    
                    include_once ('PDFReceiptMService.php');
                } 
                elseif ($_GET['Type'] == 11) {
                     // Es nota devolucion ...
                    include_once ('PDFNoteCreditDirectServillantas.php');
                } 
                else {
                    include_once ('PDFInvoiceServillantas.php');
                    
                    // include_once ('PDFInvoiceMService.php');
                    
                }
            }
            $PageSecurity = 2;
            
            // include('includes/session.inc');
            $funcion = 191;
            $seriestotal = array();
            
            include ('includes/SQL_CommonFunctions.inc');
            include ('includes/FreightCalculation.inc');
            include ('includes/GetSalesTransGLCodes.inc');
            
            // para cuentas referenciadas
            include ('Numbers/Words.php');
            include ('includes/XSAInvoicing.inc');
            
            // include ('XSAInvoicing2.inc');
            include ('includes/SendInvoicing.inc');
            include_once ('phpqrcode/qrlib.php');
            include ('includes/pdfFacturaClass.inc');
            $pdf = new pdfFactura();
            $pdf->printPDF();
             //
            
        }
    } 
    elseif ($_SESSION['DatabaseName'] == "erpmservice" or $_SESSION['DatabaseName'] == "erpmservice_CAPA" or $_SESSION['DatabaseName'] == "erpmservice_DES") {
        
        // include('companies/'.$_SESSION['DatabaseName'].'/PDFInvoiceTemplateV2.inc');
        if ($flagfiscal == 0) {
            if (preg_match("/mservice/", $_SESSION['DatabaseName'])) {
                
                if ($_GET['Type'] == 12) {
                     // Es recibo ...
                    // error_reporting(E_ALL);
                    // ini_set('display_errors', '1');
                    
                    include_once ('PDFReceiptMService.php');
                } 
                elseif ($_GET['Type'] == 11) {
                     // Es nota devolucion ...
                    include_once ('PDFNoteCreditDirectServillantas.php');
                } 
                else {
                    include_once ('PDFInvoiceMService.php');
                }
            }
        }
    } 
    else {
        
        $PageSecurity = 2;
        
        $funcion = 191;
        $seriestotal = array();
        if (preg_match("/mservice/", $_SESSION['DatabaseName'])) {
            
            if ($_GET['Type'] == 12) {
                 // Es recibo ...
                
                include_once ('PDFReceiptMService.php');
            }
        }
        
        include ('includes/SQL_CommonFunctions.inc');
        include ('includes/FreightCalculation.inc');
        include ('includes/GetSalesTransGLCodes.inc');
        
        // para cuentas referenciadas
        include ('Numbers/Words.php');
        include ('includes/XSAInvoicing.inc');
        
        // include ('XSAInvoicing2.inc');
        include ('includes/SendInvoicing.inc');
        include_once ('phpqrcode/qrlib.php');
        include ('includes/pdfFacturaClass.inc');
        $pdf = new pdfFactura();
        $pdf->printPDF();
    }
} 
else {
              
    
    if ($flagfiscal == 0 and $_GET['Type'] != 13) {
        if (preg_match("/mservice/", $_SESSION['DatabaseName'])) {
            
            if ($_GET['Type'] == 12) {
                 // Es recibo ...
                // error_reporting(E_ALL);
                // ini_set('display_errors', '1');
                
                include_once ('PDFReceiptMService.php');
            } 
            elseif ($_GET['Type'] == 11) {
                 // Es nota devolucion ...
                include_once ('PDFNoteCreditDirectServillantas.php');
            } 
            else {
               
                include_once ('PDFInvoiceMService.php');
            }
        }
    }
    
    $PageSecurity = 2;
    require_once ('jasper/ReportsWithXML.php');
    
    // $funcion=191;
    global $db;
    
    if(isset($_GET['sustitucion'] ) AND $_GET['sustitucion'] =='1'){
        $sql = "SELECT Xmls.rfcEmisor,
                   Xmls.xmlImpresion xmlImpresion,
                   Xmls.fiscal,
                   Xmls.type,
                   legalbusinessunit.logo,
                   legalbusinessunit.legalid
            FROM log_cancelacion_sustitucion AS Xmls
            LEFT JOIN legalbusinessunit ON legalbusinessunit.taxid=Xmls.rfcEmisor
            WHERE Xmls.transNo=" . $_GET['TransNo'] . "
              AND Xmls.type=" . $_GET['Type'];

    }else{
        $sql = "SELECT Xmls.rfcEmisor,
                   Xmls.xmlImpresion,
                   Xmls.fiscal,
                   Xmls.type,
                   legalbusinessunit.logo,
                   legalbusinessunit.legalid
            FROM Xmls
            LEFT JOIN legalbusinessunit ON legalbusinessunit.taxid=Xmls.rfcEmisor
            WHERE Xmls.transNo=" . $_GET['TransNo'] . "
              AND Xmls.type=" . $_GET['Type'];

    }
    
    
      if($_SESSION['UserID'] == "desarrollo"){
        // echo '<br> SQL001 <pre>'.$sql;//
    //    exit;
        }
    $ErrMsg = _('El Sql que fallo fue');
    $DbgMsg = _('No se pudo obtener los datos de la unidad de negocio');
    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
    
    $sqlLogo = "SELECT confvalue from config where confname='rootPath'";
    $resultLogo = DB_query($sqlLogo, $db, $ErrMsg, $DbgMsg, true);
    
    // $result=DB_query($sql,$db,$ErrMsg,$DbgMsg,true);//
    
    $queryNombre = "SELECT typename
                    FROM systypescat
                    WHERE typeid=" . $_GET['Type'];
    $ResultNombre = DB_query($queryNombre, $db, $ErrMsg, $DbgMsg, true);
    $nombre = '';
    
    if ($mynombre = DB_fetch_array($ResultNombre)) {
        $nombre = $mynombre['typename'];
    }
    
    if (DB_num_rows($result) > 0) {
       // echo "<br> ENTRO ANTES AQUI.";  
        if ($row = DB_fetch_array($result)) 
        {
          //  echo "<br>ENTRO AQUI 2";
            
            $row2 = DB_fetch_array($resultLogo);
            
            // $logo2=$row2["confvalue"].$row["rfcEmisor"].".jpg";
            $logo = $row2["confvalue"].$row["logo"];
            $logoalterno_2 = $row2["confvalue"] . $logoalterno;
           
            
            if (!file_exists($logo)) {
                $logo = $row2["confvalue"] . "logo.jpg";
            }
            $cancelado = 0;
            $copias = 1;
            if (in_array($row["type"], array(110, 119, 10, 11))) {
                 // strpos($_SESSION['DatabaseName'], 'mservice') and
                $copias = $_SESSION['NumeroDeCopiasEnFacturas'];
            }
            
            ////
            if ($_SESSION['DatabaseName'] == "erpplacacentro" || $_SESSION['DatabaseName'] == "erpplacacentro_CAPA") {
                
                if (!file_exists($logo)) {
                    $logo = "http://erpplacacentro.portalito.com/erpdistribucion/companies/erpplacacentro/logo.jpg";
                }
                //Se modfico ya que placacentro agrego una nueva razon social 3  y tiene su propio logo
                //$logo = "http://erpplacacentro.portalito.com/erpdistribucion/companies/erpplacacentro/logo.jpg";
            } 
            elseif ($_SESSION['DatabaseName'] == "erptmi" || $_SESSION['DatabaseName'] == "erptmi_CAPA") {
                if ($_GET['tagref'] == 18) {
                    $logo = "http://erptmi.portalito.com/erpdistribucion/companies/erptmi/SAT/RodrigoSotoPesquera/logo.jpg";
                }elseif ($_GET['tagref'] == 28) {
                    $logo = "http://erptmi.portalito.com/erpdistribucion/companies/erptmi/logoStore.jpg";
                }else {
                    $logo = "http://erptmi.portalito.com/erpdistribucion/companies/erptmi/logo.jpg";
                }
            } 
            elseif ($_SESSION['DatabaseName'] == "erpmservice" || $_SESSION['DatabaseName'] == "erpmservice_CAPA") {
                $logo = "http://erpmservice.portalito.com/erpdistribucion/companies/erpmservice/logo.jpg";
            }
            elseif($_SESSION['DatabaseName'] == "erpdace" || $_SESSION['DatabaseName'] == "erpdace_CAPA"){
                // $logo = "http://erpdace.portalito.com/erpdace/companies/erpmdace/logodace.jpg";
                $logo = "http://erpdace.portalito.com/erpdistribucion/companies/erpdace/logodace.jpg";

                // echo "Entra: ".$_SESSION['DatabaseName'];
                // exit;
            }
            
            //
            
            $Cantidad_Emision = 'SELECT SUM(plcdata.value) AS total, folio
                                 FROM worequirements
                                 INNER JOIN workorders ON workorders.wo = worequirements.wo
                                 INNER JOIN plcdata ON plcdata.stockid = worequirements.stockid
                                 AND plcdata.wo = worequirements.wo
                                 INNER JOIN stockmaster ON stockmaster.stockid = worequirements.stockid
                                 INNER JOIN stockmaster AS encabezado ON encabezado.stockid = worequirements.parentstockid
                                 INNER JOIN debtortrans ON workorders.orderno = debtortrans.order_
                                 AND debtortrans.type = 119
                                 AND debtortrans.transno="' . $_GET['TransNo'] . '"
                                 AND plcdata.transno = debtortrans.transno
                                 WHERE folio IN
                                     (SELECT folio
                                      FROM debtortrans
                                      WHERE order_="' . $OrderNo . '")
                                 GROUP BY folio;';
            
            $result_Emision = DB_query($Cantidad_Emision, $db);
            $myrow_emision = DB_fetch_array($result_Emision);
            $emision = $myrow_emision['total'];     
            
            // echo "<br> sql : ".$Cantidad_Emision;
            // echo "<br> Emision".$emision;
            
            if (isset($_GET['cfd'])) {
                
                // En caso de ser Recibo de Nomina , egresos, $_GET['cfd']=2
                $sql = "SELECT id,
                                noempleado,
                                folio,
                                uuid,
                                cancelado
                         FROM nom_recibo
                         WHERE transNo=" . $_GET['TransNo'] . "
                           AND TYPE=" . $_GET['Type'];
                $ErrMsg = _('El Sql que fallo fue');
                $DbgMsg = _('No se pudo saber si ha sido cancelado ' . $nombre);
                $result2 = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
                $row3 = DB_fetch_array($result2);
                if (!(strpos($row3['invtext'], 'CANCELAD') === FALSE)) $cancelado = 1;
                $row["xmlImpresion"] = str_replace("&QUOT;", "'", $row["xmlImpresion"]);
                //$row["xmlImpresion"] = str_replace("&amp;", " ", $row["xmlImpresion"]);


                $pdfBytes = reportXML($row["xmlImpresion"], $row["fiscal"], $logo, $nombre, $row["type"], $cancelado, $copias, "nomina", $flaglogo, $facturacliente, $mensaje, $pagares, "0", $logoalterno_2, $mostrardes, $OrderNo);
            } 
            else {
            
                // En caso de ser Comprobante CFDi de ingresos,egresos,traslado
                $sql = "SELECT id,
                                tagref,
                                transno,
                                type,
                                debtorno,
                                trandate,
                                invtext,
                                lasttrandate,
                                ovamountcancel,
                                canceldate,
                                branchcode, 
                                ovamount,
                                substring(cadena, 3, 3) as version
                         FROM debtortrans
                         WHERE transNo=" . $_GET['TransNo'] . "
                           AND type=" . $_GET['Type'];
                
                $ErrMsg = _('El Sql que fallo fue');
                $DbgMsg = _('No se pudo saber si ha sido cancelado');
                $result2 = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
                $row3 = DB_fetch_array($result2);
                $foliointerno = $row3["transno"];
                if ((!(strpos($row3['invtext'], 'CANCELAD')  === FALSE) OR !(strpos($row3['invtext'], 'Cancelad')  === FALSE)) and $row3['ovamount'] == 0) {
                    $cancelado = 1;
                }

                $row["xmlImpresion"] = str_replace("&amp;", "&", $row["xmlImpresion"]);
                $row["xmlImpresion"] = str_replace("&QUOT;", "'", $row["xmlImpresion"]);
                $row["xmlImpresion"] = str_replace("&quot;", "'", $row["xmlImpresion"]);
                // se retiro & en el array porque hay clientes que tiene &, no agregar mhp
                $row["xmlImpresion"] = str_replace(array("\R\N", "\R", "\N","&#10;","&amp;amp;"), " ", $row["xmlImpresion"]);
                $row["xmlImpresion"] = str_replace('<pago10: ', "<", $row["xmlImpresion"]);
                // se agrega porque el rfc o el nombre del cliente tiene amperson y el jasper generar error si se envia (&) grupo si
                $row["xmlImpresion"] = str_replace("&", "&amp;", $row["xmlImpresion"]);
                // $row["xmlImpresion"] = str_replace("&AMP;AMP", "&amp;", $row["xmlImpresion"]);


                /*echo "<br>fiscal: ".$row["fiscal"]."<br>";
                echo "<br>logo: ".$logo."<br>";
                echo "<br>nombre: ".$nombre."<br>";
                echo "<br>type: ".$row["type"]."<br>";
                echo "<br>cancelado: ".$cancelado."<br>";
                echo "<br>copias: ".$copias."<br>";
                echo "<br>flaglogo: ".$flaglogo."<br>";
                echo "<br>facturacliente: ".$facturacliente."<br>";
                echo "<br>mensaje: ".$mensaje."<br>";
                echo "<br>pagares: ".$pagares."<br>";
                echo "<br>foliointerno: ".$foliointerno."<br>";
                echo "<br>logoalterno_2: ".$logoalterno_2."<br>";
                echo "<br>mostrardes: ".$mostrardes."<br>";
                echo "<br>OrderNo: ".$OrderNo."<br>";
                echo "<br>emision: ".$emision."<br>";
                echo "<br>branchcode: ".$row3["branchcode"]."<br>";
                echo "<br>DesgloseIVA: ".$_SESSION['DesgloseIVA']."<br>";
                echo "version: ".$row3["version"];*/
                              
                // AND $_SESSION['facturacliente']==1
                
                if ($row3["version"]=='3.3'){
                    // pruebas pdf 3.3
                    $realogo = $row2["confvalue"] . $_SESSION["LogoFile"]; 
                    $pdfBytes = reportXML3($row["xmlImpresion"], $row["fiscal"], $realogo, $nombre, $row["type"], $cancelado, $copias, "comprobante", $flaglogo, $facturacliente, $mensaje, $pagares, $foliointerno, $logoalterno_2, $mostrardes, $OrderNo, (int) $emision, $row3["branchcode"],$_SESSION['DesgloseIVA'],$mCliente,$confJasper);

                }else{
            
                   $realogo = $row2["confvalue"] . $_SESSION["LogoFile"]; 
                    $pdfBytes = reportXML($row["xmlImpresion"], $row["fiscal"], $realogo, $nombre, $row["type"], $cancelado, $copias, "comprobante", $flaglogo, $facturacliente, $mensaje, $pagares, $foliointerno, $logoalterno_2, $mostrardes, $OrderNo, (int) $emision, $row3["branchcode"],$_SESSION['DesgloseIVA'],$mCliente,$confJasper);
                
                }
            }            
            // echo $row["xmlImpresion"], $row["fiscal"], $logo, $nombre, $row["type"], $cancelado, $copias, "comprobante", $flaglogo, $facturacliente, $mensaje, $pagares, $foliointerno, $logoalterno_2, $mostrardes, $OrderNo, (int) $emision, $row3["branchcode"],$_SESSION['DesgloseIVA'],$mCliente;

           /*if (!isset($_GET["error"])) {
                if($_SESSION['UserID'] == "desarrollo"){
                    
                    header('Content-type: application/pdf');
                    header('Content-Length: ' . strlen($pdfBytes));
                    //A pasado dos veces que se cambia a la extencion zzz
                    header('Content-Disposition: inline; filename=report.pdf');
                    echo $pdfBytes;

                }
            }*/
           
            header('Content-type: application/pdf');
            header('Content-Length: ' . strlen($pdfBytes)); 
            header('Content-Disposition: inline; filename=report.pdf');
            echo $pdfBytes;

            
        }
    }
}
?> 
