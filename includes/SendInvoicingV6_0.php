<?php
///////////
 if($_SESSION['UserID'] =="desarrollo"){
    //ini_set('display_errors', 1);
    // ini_set('log_errors', 1);
    // ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
    // error_reporting(E_ALL);
 }
 // correcion tarea 78016
// produc. pisos y mas, jibe
// Se envía para anticipos
// MG, server 16 , todo dis
/**Desarrollado
 * MODIFICADO POR: Sofia López
 * Se agregaron variables no declaradas correctamente
 * 
 * Se agregaron campos a xml
 * 
 * Se manda a producción - TAREA 77715
 */
/*
A PRODUCCION DE MG COMPLEMENTO DE COMERCIO EXTERIOR
 */
$debug = false;
/*
    ***************
 * CGM 26/12/2013 Se agrego generacion de addenda en funcion para comprobantes de tipo cfdi**
 *
 * CGM 9/12/2013 Si tipofacturacionxtag=11 entonces nota de credito
 * Si no sera = a 1 que son configuraciones para documentos de cargo (Facturas, remisiones,etc)
 * Se agrego validacion de tipo de documento para notas de credito;
 * Se agregó cpnsulta para tomar el campo donde se almacen la tarjeta de red M
 *
 */
//Se hace correcion para retenciones
// se envía a todos los clientes excepto a pisumma
// se hace cambio para el cp, y para que el tipo 13 no tome el complemto de pago
// se envía a servillantas
// se envia a produccion de gruposii y servillantas
// Se envía a pisumma  y a todos --
// Correcion de numero de parcialidad en grupo sii a jocapra
//  Correcion de numero de IVA EN NOTAS DE CREDITO PARA TODOS;
//  Correción en subtotales cuando no lleva impuestos
//  a produccion MG
//  Se hicieron cambios para la factura de sustitucion
//  DGJ 17/setp/2018 Se agregó validación para que por el momento si es tipo de ralacion 08, no se agregue al xml tarea 75212
//  Correcion de complemento de pago
// cambios anticipos 03/diciembre /2018
// DGJ: 02/enero /2019 Se hicieron cambios en el nodo de retenciones tarea 76450
/*
 DGJ: 04/07/2019 TAREA 77965 a produccion 
 */
/*
 DGJ: 25/07/2019 Factoraje
 */
/*
 DGJ: 02/08/2019 Factoraje tarea 79109
 */
/*
 DGJ: 02/08/2019 Parcialidadades factoraje tarea 79026
 */
/*
 DGJ: 03/10/2019 Cambios para pagareas y complemento tarea  79574
 */
/*
 DGJ: 11/10/2019 Cambios tarea 79410
 */
/*  
 DGJ: 05/10/2019 cambio para timbrado de tipo 21 tarea 79671
 */
function AgregaAddendaXML($xmlSat, $debtorno, $iddocto, $db) {
    // Carga el xmlSat para modificarlo.
    $xml = new DOMdocument ();
    $xml->loadXml($xmlSat);
    $root = new DOMXPath($xml);
    $comprobante = $root->query("/cfdi:Comprobante");
    $emisor = $root->query("/cfdi:Comprobante/cfdi:Receptor");

    if ($_SESSION['FacturaVersion'] == "3.3") {

        $rfccliente = $emisor->item(0)->getAttribute("Rfc");

        if($rfccliente == ""){
            $rfccliente = $emisor->item(0)->getAttribute("rfc");
        }
    }else{
        $rfccliente = $emisor->item(0)->getAttribute("rfc");
    }
    $root = $comprobante->item(0);

    $SQL = "SELECT custbranch.typeaddenda,typeaddenda.archivoaddenda
                FROM  custbranch
                INNER JOIN typeaddenda on custbranch.typeaddenda=typeaddenda.id_addenda
                WHERE taxid='" . $rfccliente . "'
                    AND debtorno='" . $debtorno . "'";

    $Result = DB_query($SQL, $db);
    if (DB_num_rows($Result) == 0) {
        $typeaddenda = 0;
    } else {
        $myrowpag = DB_fetch_array($Result);
        $typeaddenda = $myrowpag ['typeaddenda'];
        $fileaddenda = $myrowpag ['archivoaddenda'];
    }

    if($_SESSION['UserID'] == 'desarrollo'){
        echo "<pre>";
        echo "<br> Llega a funcion de addenda";
        echo "<br>version = ".$_SESSION['FacturaVersion'];
        echo "<br>fileaddenda -> ".$fileaddenda;
        echo "<br>Consulta -> ".$SQL;
        echo "<br>POST -> ".print_r($_POST ['addenda']);
        echo "</pre>";
    }

    if ($typeaddenda > 0) {

        // Remover addendas en caso de que tenga - @inicio
        $addendas = array();
        $xmlRoot = $xml->documentElement;
        foreach ($xmlRoot->getElementsByTagName('Addenda') as $addenda) {
            $addendas [] = $addenda;
        }

        foreach ($addendas as $addenda) {
            $xmlRoot->removeChild($addenda);
        }
        // Remover addendas en caso de que tenga - @fin

        include_once ('includes/'.$fileaddenda);

        if($_SESSION['UserID'] == 'desarrollo'){
            echo "<pre>";
            echo "<br> Despues de include addenda";
            echo "</pre>";
        }

        // $comprobante->item(0)->appendChild($addenda);
        // $xml->formatOutput = true;
        // $xmlwhitAddenda = $xml->saveXML();

        $xml->formatOutput = true;
        $xmlwhitAddenda = $xml->saveXML();
    } else {
        $xmlwhitAddenda = $xmlSat;
    }

    if ($_SESSION ['UserID'] == 'desarrollo') {
        echo '<br /> ADDENDA: ';
        echo htmlentities ( $xmlwhitAddenda );
        echo '<br />';
    }

    return $xmlwhitAddenda;
}

/**
 * Funcion para agregar los diferentes complementos configurados al xml
 * @param String $xmlsat   CFDI generado
 * @param String $debtorno Codigo del cliente
 * @param String $iddocto  Id de debtortrans generado
 * @param DBConn $db       Link de la base de datos
 */
function AgregaComplementoXML($xmlsat, $debtorno, $iddocto, $db) {


    $xml = new DOMdocument ();
    $xml->loadXml($xmlsat);
    $root = new DOMXPath($xml);
    $comprobante = $root->query("/cfdi:Comprobante");
    $complemento = $root->query("/cfdi:Comprobante/cfdi:Complemento");
    $root = $complemento->item(0);

    $SQL = "SELECT cfdicomplement.id,
                   cfdicomplement.complement,
                   cfdicomplement.complementfile
            FROM debtorcomplement
            INNER JOIN cfdicomplement ON cfdicomplement.id = debtorcomplement.idcomplement
            WHERE debtorcomplement.debtorno = '" . $debtorno . "'
              AND cfdicomplement.active = 1";

    $Result = DB_query($SQL, $db);
    if (DB_num_rows($Result) == 0) {
        $idcomplement = 0;
    } else {
        $myrowpag = DB_fetch_array($Result);
        $idcomplement = $myrowpag ['id'];
        $complementfile = $myrowpag ['complementfile'];
    }

    if ($idcomplement > 0) {

        // $complements = array ();
        // $xmlRoot = $xml->documentElement;
        // foreach ( $xmlRoot->getElementsByTagName ( 'Complemento' ) as $complement ) {
        //  $complements [] = $complement;
        // }
        // foreach ( $complements as $complement ) {
        //  $xmlRoot->removeChild ( $complement );
        // }

        include_once ($complementfile);

        $xml->formatOutput = true;
        $xmlwhitcomplemt = $xml->saveXML();
    } else {
        $xmlwhitcomplemt = $xmlsat;
    }

    return $xmlwhitcomplemt;
}

function generaXMLIntermedio($txtinput, $xml, $cadenaOriginal, $cantidadLetra, $orderNo, $db, $tipofacturacionxtag, $tagref, $typeinvoice = 0, $transnoinovice = 0, $debtorId = 0,$nivelfacturacion=2) {
    //   echo __line__.'--'.__FILE__."<pre>entra:".$cadenaOriginal;
    //printecho("entroant generaXMLIntermedio", "echo");
    // Se eliminan los nasmespaces para evitar error de validacion de estructura de xml
    if ($_SESSION['UserID'] == 'desarrollo') { 
       // var_dump($txtinput);
    }
    /* echo '<pre>';
      var_dump($txtinput); */
    //echo "<br>generaXMLIntermedio";

    $debug_sql = false;
    $cadenaOriginal= caracteresEspecialesFactura($cadenaOriginal);

    $xmlSat = str_replace('xmlns="http://www.sat.gob.mx/cfd/2"', '', $xml);
    $xmlSat = str_replace('xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"', '', $xmlSat);
    $xmlSat = str_replace('xsi:schemaLocation="http://www.sat.gob.mx/cfd/2 http://www.sat.gob.mx/sitio_internet/cfd/2/cfdv22.xsd"', '', $xmlSat);
    $xmlSat = str_replace('xmlns:cfdi="http://www.sat.gob.mx/cfd/3"', '', $xmlSat);
    $xmlSat = str_replace('xmlns:ecfd="http://www.southconsulting.com/schemas/strict"', '', $xmlSat);
    $xmlSat = str_replace('xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd"', '', $xmlSat);
    $xmlSat = str_replace('xsi:schemaLocation="http://www.sat.gob.mx/TimbreFiscalDigital http://www.sat.gob.mx/sitio_internet/TimbreFiscalDigital/TimbreFiscalDigital.xsd"', '', $xmlSat);
    $xmlSat = str_replace('xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital"', '', $xmlSat);
    $xmlSat = str_replace('xmlns:nomina="http://www.sat.gob.mx/nomina"', '', $xmlSat);
    $xmlSat = str_replace('cfdi:', '', $xmlSat);
    $xmlSat = str_replace('tfd:', '', $xmlSat);
    $xmlSat = str_replace('implocal:', '', $xmlSat);
    $xmlSat = str_replace('nomina:', '', $xmlSat);

    ini_set("memory_limit","1024M");
    // echo 'cadena -><pre>'.$txtinput;
    // Carga el xmlSat para modificarlo. 
    $domXml = new DOMdocument ();
    //$domXml->loadXml($xmlSat);
    $domXml->loadXML($xmlSat, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);

    $arraycadena = explode('@%', $txtinput);
    // lee primero el arreglo y pone el monto de los productos
    $encabezado = explode('|', $arraycadena [0]);
    //echo "<br><pre>".var_dump($encabezado);
    if($_SESSION['UserID'] == "desarrollo"){
        // echo "<br> tipofacturacionxtag:".$tipofacturacionxtag;
        // echo "<br> tipofacturacionxtag length:".strlen($tipofacturacionxtag);

    }
    if ($tipofacturacionxtag != 1 and strlen($tipofacturacionxtag) > 0) {
        $tipodocto = $tipofacturacionxtag;
    } else {
        // documento de tipo factura
        $tipodocto = 1;
    }

    // Cargamos el xpath.
    $xpath = new DOMXPath($domXml);
    $comprobante = $xpath->query("/Comprobante");
    $emisor = $xpath->query("/Comprobante/Emisor");
    $receptor = $xpath->query("/Comprobante/Receptor");
    $emisordomfiscal = $xpath->query("/Comprobante/Emisor/DomicilioFiscal");
    
    //echo '<br><pre>emisor:'.htmlentities($xml);
    if($_SESSION['UserID'] == "desarrollo"){
        //echo '<br><pre>xml _intermdio:'.htmlentities($xml);
    }
    $rfcEmisor = $emisor->item(0)->getAttribute("rfc");

    $fechaEmision = $comprobante->item(0)->getAttribute("fecha");

    $moneda = $comprobante->item(0)->getAttribute("Moneda");

    $version = $comprobante->item(0)->getAttribute("version");

    if ($xpath->query("/Comprobante/Impuestos/Retenciones") and $version == '3.3') {
        // ****** Retenciones
        $nodoRetenciones = $xpath->query("/Comprobante/Impuestos/Retenciones");
        //if ($nodoRetenciones->item(0)->getElementsByTagName('Retencion')) {
        if ($nodoRetenciones->item(0) != null) {
            //echo "<br>tiene retenciones";
            $retencionesDatos = $nodoRetenciones->item(0)->getElementsByTagName('Retencion');
            for($i = 0; $i < $retencionesDatos->length; $i++) {
                $codigoImpuesto = "";
                if ($retencionesDatos->item($i)->getAttribute("Impuesto")) {
                    $codigoImpuesto = $retencionesDatos->item($i)->getAttribute("Impuesto");
                }else if ($retencionesDatos->item($i)->getAttribute("impuesto")) {
                    $codigoImpuesto = $retencionesDatos->item($i)->getAttribute("impuesto");
                }
                $desRet = "";
                // Dejar vacio para validacion de impuestos locales, si es retencion saldria el codigo y solo agregar descripcion
                $sql = "SELECT descripcion FROM sat_impuestos WHERE c_Impuesto = '".$codigoImpuesto."'";
                //echo "<br>sql: ".$sql;
                $result = DB_query($sql, $db);
                if ( $row = DB_fetch_array($result) ) {
                    $desRet = utf8_encode($row['descripcion']);
                }

                $descripcionRetencion = $domXml->createAttribute("descripcion");
                $descripcionRetencion->value = ''.$desRet;
                $retencionesDatos->item($i)->appendChild($descripcionRetencion);
            }
        }else{
            //echo "<br>no tiene retenciones";
        }
        // ****** Retenciones
    }
    
    if ($comprobante->item(0)->getAttribute("metodoDePago") and $version == '3.2') {
        //Agregar descripcion al metodo de pago
        $metodoDePago = $comprobante->item(0)->getAttribute("metodoDePago");
        $sql = "SELECT namesat FROM paymentmethods WHERE codesat = '".$metodoDePago."'";
        $result = DB_query($sql, $db);
        if ( $row = DB_fetch_array($result) ) {
            //Mostrar descripcion del metodo de pago
            $metodoDePago .= " - ".utf8_encode($row['namesat']);
        }
        $comprobante->item(0)->setAttribute("metodoDePago", $metodoDePago);
    }
    
    if ($comprobante->item(0)->getAttribute("tipoDeComprobante") and $version == '3.3') {
        //Agregar descripcion del tipo de comprobante
        $TipoDeComprobante = $comprobante->item(0)->getAttribute("tipoDeComprobante");
        $sql = "SELECT descripcion FROM sat_tiposcomprobante WHERE c_TipoDeComprobante = '".$TipoDeComprobante."'";
        $result = DB_query($sql, $db);
        if ( $row = DB_fetch_array($result) ) {
            $TipoDeComprobante .= " - ".utf8_encode($row['descripcion']);
        }
        $comprobante->item(0)->setAttribute("tipoDeComprobante", $TipoDeComprobante);
    }
 
    if ($comprobante->item(0)->getAttribute("MetodoPago") and $version == '3.3') {
        //Agregar descripcion al metodo de pago
        $MetodoPago = $comprobante->item(0)->getAttribute("MetodoPago");
        $sql = "SELECT paymentname FROM sat_paymentmethodssat WHERE paymentid = '".$MetodoPago."'";
        $result = DB_query($sql, $db);
        if ( $row = DB_fetch_array($result) ) {
            $MetodoPago .= " - ".utf8_encode($row['paymentname']);
        }
        $comprobante->item(0)->setAttribute("MetodoPago", $MetodoPago);
    }

    if ($comprobante->item(0)->getAttribute("descuento") and $version == '3.3') {
        $Descuento33= $comprobante->item(0)->getAttribute("descuento");
    }
 
    if ($comprobante->item(0)->getAttribute("FormaPago") and $version == '3.3') {
        //Agregar descripcion de la forma de pago
        $FormaPago = $comprobante->item(0)->getAttribute("FormaPago");
        $sql = "SELECT paymentname FROM paymentmethodssat WHERE paymentid = '".$FormaPago."'";
        $result = DB_query($sql, $db);
        if ( $row = DB_fetch_array($result) ) {
            $FormaPago .= " - ".utf8_encode($row['paymentname']);
        }
        $comprobante->item(0)->setAttribute("FormaPago", $FormaPago);
    }
 
    if ($emisor->item(0)->getAttribute("RegimenFiscal") and $version == '3.3') {
        //Agregar descripcion del regimen fiscal
        $RegimenFiscal = $emisor->item(0)->getAttribute("RegimenFiscal");
        $sql = "SELECT descripcion FROM sat_regimenfiscal WHERE c_RegimenFiscal = '".$RegimenFiscal."'";
        $result = DB_query($sql, $db);
        if ( $row = DB_fetch_array($result) ) {
            $RegimenFiscal .= " - ".utf8_encode($row['descripcion']);
        }
        $emisor->item(0)->setAttribute("RegimenFiscal", $RegimenFiscal);
    }
 
    if ($receptor->item(0)->getAttribute("UsoCFDI") and $version == '3.3') {
        //Agregar descripcion del uso del cfgdi
        $UsoCFDI = $receptor->item(0)->getAttribute("UsoCFDI");
        $sql = "SELECT descripcion FROM sat_usocfdi WHERE c_UsoCFDI = '".$UsoCFDI."'";
        $result = DB_query($sql, $db);
        if ( $row = DB_fetch_array($result) ) {
            $UsoCFDI .= " - ".utf8_encode($row['descripcion']);
        }
        $receptor->item(0)->setAttribute("UsoCFDI", $UsoCFDI);
    }

    
    $sqlrazon = "SELECT legalbusinessunit.telephone as telephone,tags.phone as phone,tags.address1 as calle,tags.address2 as numero,tags.address3 as colonia,tags.address4 as municipio,tags.address5 as estado, tags.address6 as pais,tags.tagname
                    FROM tags
                    INNER JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
                    WHERE tags.tagref ='" . $tagref . "'";

    //echo "Sqlrazon: " . $sqlrazon;


    debug_sql($sqlrazon, __LINE__,$debug_sql,__FILE__);
    

    $resrazon = DB_query($sqlrazon, $db);

    $myrrowrazon = DB_fetch_array($resrazon);
    
    $telefono = $myrrowrazon['telephone'];

    $telefonoemi = $domXml->createAttribute('telefono');
    
    $telefonoemi->value = ReglasXCadena($telefono);
    $emisordomfiscal->item(0)->appendChild($telefonoemi);

    //***********AGHREGAR TELEFONO A LA IMPRESION*****************/
    
    $expedido = $xpath->query("/Comprobante/Emisor/ExpedidoEn");
    
    $tagname_sucursal = $myrrowrazon['tagname'];
    $tel = $myrrowrazon['phone'];
    $calle = $myrrowrazon['calle'];
    $numero = $myrrowrazon['numero'];
    $colonia = $myrrowrazon['colonia'];
    $estado = $myrrowrazon['estado'];
    $pais = $myrrowrazon['pais'];
    $municipio = $myrrowrazon['municipio'];

    
    $tagname_sucursal_emi = $domXml->createAttribute('referencia');
    $telemi = $domXml->createAttribute('telefono');
    $calleemi = $domXml->createAttribute('calle');
    $numeroemi = $domXml->createAttribute('noExterior');
    $coloniaemi = $domXml->createAttribute('colonia');
    $municipioemi = $domXml->createAttribute('municipio');
    $estadoemi = $domXml->createAttribute('estado');
    $paisemi = $domXml->createAttribute('pais');

    $tagname_sucursal_emi->value = ReglasXCadena($tagname_sucursal);
    $telemi->value = ReglasXCadena($tel);
    $calleemi->value = ReglasXCadena($calle);
    $numeroemi->value = ReglasXCadena($numero);
    $coloniaemi->value = ReglasXCadena($colonia);
    $municipioemi->value = ReglasXCadena($municipio);
    $estadoemi->value = ReglasXCadena($estado);
    $paisemi->value = ReglasXCadena($pais);
    
    $expedido->item(0)->appendChild($tagname_sucursal_emi);
    $expedido->item(0)->appendChild($telemi);
    $expedido->item(0)->appendChild($calleemi);
    $expedido->item(0)->appendChild($numeroemi);
    $expedido->item(0)->appendChild($coloniaemi);
    $expedido->item(0)->appendChild($municipioemi);
    $expedido->item(0)->appendChild($estadoemi);
    $expedido->item(0)->appendChild($paisemi);
    //***********AGHREGAR TELEFONO A LA IMPRESION*****************/

    /*$cadenaiva = $domXml->createAttribute("ivadocumento");
    $cadenaiva->value = "$encabezado[7]";
    $comprobante->item(0)->appendChild($cadenaiva);

    $totaldocumento = $domXml->createAttribute("totaldocumento");
    $totaldocumento->value = "$encabezado[6]";
    $comprobante->item(0)->appendChild($totaldocumento);*/
    
    $ivadoc = $encabezado[7];
    
    $totaldoc = $encabezado[6];
    
    if ($typeinvoice == "12") {
        $ivadoc = 0;
        $totaldoc = 0;
    }
    
    $cadenaiva = $domXml->createAttribute("ivadocumento");
    
    $cadenaiva->value = "$ivadoc";
    
    $comprobante->item(0)->appendChild($cadenaiva);

    $totaldocumento = $domXml->createAttribute("totaldocumento");
    
    $totaldocumento->value = "$totaldoc";
    
    $comprobante->item(0)->appendChild($totaldocumento);

    /*
        Agregar si no se muestra el % del iva en documento 200 
        cuando se agrega el iva manual, funcion 960
    */
    if (isset($_SESSION['IvaManualAbonoDirectoClientes']) == 1) {
        if ($_SESSION['IvaManualAbonoDirectoClientes'] == 1) {
            $Show_PorcentajeIva = $domXml->createAttribute("Show_PorcentajeIva");
            $Show_PorcentajeIva->value = "1";
            $comprobante->item(0)->appendChild($Show_PorcentajeIva);
        }
    }
    //echo "<br>IvaManualAbonoDirectoClientes: ".$_SESSION['IvaManualAbonoDirectoClientes'];
    
    $ajustes = ajustarXmlImpresion($domXml);

    //Agregar moneda despues de ajuste impresion para no afectar validaciones
    if ($comprobante->item(0)->getAttribute("Moneda")) {
        //Agregar descripcion de la moneda
        $Moneda = $comprobante->item(0)->getAttribute("Moneda");
        $sql = "SELECT currency FROM currencies WHERE currabrev = '".$Moneda."'";
        $result = DB_query($sql, $db);
        if ( $row = DB_fetch_array($result) ) {
            $Moneda .= " - ".utf8_encode($row['currency']);
        }
        $comprobante->item(0)->setAttribute("Moneda", $Moneda);
    }

    // Creamos atributo de cadena
    $cadena = $domXml->createAttribute("cadenaOriginal");
    $cadena->value = "$cadenaOriginal";

    $sqlservice = "SELECT type, branchcode 
                        FROM debtortrans
                        WHERE order_ = " . $orderNo;
    debug_sql($sqlservice, __LINE__,$debug_sql,__FILE__);
    $resultservice = DB_query($sqlservice, $db);
    $rowservice = DB_fetch_array($resultservice);
    $typeservice = $rowservice ['type'];
    $branchcode = $rowservice ['branchcode'];
    if ($_SESSION ['UserID'] == "desarrollo") {
         echo '<br>typeservice'.$typeservice;
    }
    
    
    if ($typeservice == 119) {
        if ($_SESSION ['DatabaseName'] == "erpmservice" or $_SESSION ['DatabaseName'] == "erpmservice_CAPA" or $_SESSION ['DatabaseName'] == "erpmservice_DES" or $_SESSION ['DatabaseName'] == "erpmservice_DIST") {
            if (isset($_SESSION ['DecimalPlacesLetra']) and $_SESSION ['DecimalPlacesLetra'] != "") {
                $montoctvs2 = substr($ajustes ['subtotal'], 0, $_SESSION ['DecimalPlacesLetra']);
            } else {
                $montoctvs2 = substr($ajustes ['subtotal'], 0, 6);
            }

            // $montoctvs2 = $separa[1];//
            $montoletra = Numbers_Words::toWords($ajustes ['subtotal'], 'es');
            // $montocentavos=Numbers_Words::toWords($montoctvs2,'es');
            if ($moneda=='MXN' or $moneda=='XXX'){
                $montoletra=ucwords($montoletra) . " Pesos ". $montoctvs2 ." /100 M.N.";
            }elseif ($moneda=='EUR'){
                $montoletra=ucwords($montoletra) . " Euros ". $montoctvs2 ." /100 EUR";
            }else{
                $montoletra=ucwords($montoletra) . " Dolares ". $montoctvs2 ."/100 USD";
            }
            $cantidad = $domXml->createAttribute("cantidadLetra");
            $cantidad->value = "$montoletra";
        } else {
            $cantidad = $domXml->createAttribute("cantidadLetra");

            $totaletrasc=$comprobante->item(0)->getAttribute("total");
            if ($_SESSION ['UserID'] == "desarrollo") {
                 echo '<br>TOTAL: '.$totaletrasc;
            }
            $separac=explode(".",$totaletrasc);
            $montoctvs2c = $separac[1];
            $montoctvs1c = $separac[0];
            if ($montoctvs2c>995){
                $montoctvs1c=$montoctvs1c+1;
            }
            $montoletrac=Numbers_Words::toWords($montoctvs1c,'es');
            $totaletrasc=number_format($totaletrasc,2);
            $separac=explode(".",$totaletrasc);
            $montoctvs2c = $separac[1];
            if ($montoctvs2c>995){
                $montoctvs2c=0;
            }
            if ($moneda=='MXN' or $moneda=='XXX'){
                $cantidadletra=ucwords($montoletrac) . " Pesos ". $montoctvs2c ." /100 M.N.";
            }elseif ($moneda=='EUR'){
                $cantidadletra=ucwords($montoletrac) . " Euros ". $montoctvs2c ." /100 EUR";
            }else{
                $cantidadletra=ucwords($montoletrac) . " Dolares ". $montoctvs2c ."/100 USD";
            }    

            $cantidad->value = "$cantidadletra";
        }
    } else {
        
        $cantidad = $domXml->createAttribute("cantidadLetra");

        $totaletrasc=$comprobante->item(0)->getAttribute("total");
        $separac=explode(".",$totaletrasc);
        $montoctvs2c = $separac[1];
        $montoctvs1c = $separac[0];
        if ($montoctvs2c>995){
            $montoctvs1c=$montoctvs1c+1;
        }
        $montoletrac=Numbers_Words::toWords($montoctvs1c,'es');
        $totaletrasc=number_format($totaletrasc,2);
        $separac=explode(".",$totaletrasc);
        $montoctvs2c = $separac[1];
        if ($montoctvs2c>995){
            $montoctvs2c=0;
        }
        if ($moneda=='MXN' or $moneda=='XXX'){
            $cantidadletra=ucwords($montoletrac) . " Pesos ". $montoctvs2c ." /100 M.N.";
        }elseif ($moneda=='EUR'){
            $cantidadletra=ucwords($montoletrac) . " Euros ". $montoctvs2c ." /100 EUR";
        }else{
            $cantidadletra=ucwords($montoletrac) . " Dolares ". $montoctvs2c ."/100 USD";
        }    

        $cantidad->value = "$cantidadletra";
    }


    $comprobante->item(0)->appendChild($cantidad);
    $comprobante->item(0)->appendChild($cadena);
    $sqlservice = "SELECT type 
                        FROM debtortrans
                        WHERE order_ = " . $orderNo;
    debug_sql($sqlservice, __LINE__,$debug_sql,__FILE__);
    $resultservice = DB_query($sqlservice, $db);
    $rowservice = DB_fetch_array($resultservice);
    $typeservice = $rowservice ['type'];
    if ($_SESSION ['UserID'] == "desarrollo") {
        // echo '<br>typeservice'.$typeservice;
    }
    if ($typeservice == 119) {
        if ($_SESSION ['DatabaseName'] == "erpmservice" or $_SESSION ['DatabaseName'] == "erpmservice_CAPA" or $_SESSION ['DatabaseName'] == "erpmservice_DES" or $_SESSION ['DatabaseName'] == "erpmservice_DIST") {
            if (isset($_SESSION ['DecimalPlacesLetra']) and $_SESSION ['DecimalPlacesLetra'] != "") {
                $montoctvs2 = substr($ajustes ['subtotal'], 0, $_SESSION ['DecimalPlacesLetra']);
            } else {
                $montoctvs2 = substr($ajustes ['subtotal'], 0, 6);
            }

            // $montoctvs2 = $separa[1];//
            $montoletra = Numbers_Words::toWords($ajustes ['subtotal'], 'es');
            // $montocentavos=Numbers_Words::toWords($montoctvs2,'es');
            if ($moneda=='MXN' or $moneda=='XXX'){
                $montoletra=ucwords($montoletra) . " Pesos ". $montoctvs2 ." /100 M.N.";
            }elseif ($moneda=='EUR'){
                $montoletra=ucwords($montoletra) . " Euros ". $montoctvs2 ." /100 EUR";
            }else{
                $montoletra=ucwords($montoletra) . " Dolares ". $montoctvs2 ."/100 USD";
            }
            $cantidadAjuste = $domXml->createAttribute("cantidadLetraAjuste");
            $cantidadAjuste->value = $montoletra;
            $comprobante->item(0)->appendChild($cantidadAjuste);
        } else {
            $cantidadAjuste = $domXml->createAttribute("cantidadLetraAjuste");
            $cantidadAjuste->value = $ajustes ['cantidadLetra'];
            $comprobante->item(0)->appendChild($cantidadAjuste);
        }
    } else {
        $cantidadAjuste = $domXml->createAttribute("cantidadLetraAjuste");
        $cantidadAjuste->value = $ajustes ['cantidadLetra'];
        $comprobante->item(0)->appendChild($cantidadAjuste);
    }
    
    // Ajuste Cantidad Letra
    // Ajuste Total
    // echo 'total:'.$ajustes['total'];

    $totalAjuste = $domXml->createAttribute("totalAjuste");
    $totalAjuste->value = $ajustes ['total'];
    $comprobante->item(0)->appendChild($totalAjuste);

    // Ajuste SubTotal
    $subTotalAjuste = $domXml->createAttribute("subTotalAjuste");
    $subTotalAjuste->value = $ajustes ['subtotal'];
    $comprobante->item(0)->appendChild($subTotalAjuste);

    // Ajuste IVA
    $ivaAjuste = $domXml->createAttribute("ivaAjuste");
    $ivaAjuste->value = $ajustes ['iva'];
    $comprobante->item(0)->appendChild($ivaAjuste);

    if ($tipodocto == 1 || $tipodocto == 119) {
        $pedido = $domXml->createAttribute("pedidoVenta");
        $pedido->value = "$orderNo";
        $comprobante->item(0)->appendChild($pedido);
    } elseif ($tipodocto == 11) {
        $pedido = $domXml->createAttribute("pedidoVenta");
        $pedido->value = "$orderNo";
        $comprobante->item(0)->appendChild($pedido);
    }

    $input = str_replace(chr(13) . chr(10) . '0', '@%', utf8_encode($txtinput));
    $arraycadena = array();
    $arraycadena = explode('@%', $input);

    $encabezadoLine = explode('|', $arraycadena [0]);
    $comentario_factura = "";

    //Validar si mostrar comentarios
    $mostrarComentarios = 1;




    if ($typeinvoice != 0 and $transnoinovice != 0) {
        $consulta = "Select invtext, showcomments from debtortrans
                    where debtortrans.type= " . $typeinvoice . "                            
                    AND debtortrans.transno = '" . $transnoinovice . "'";
        debug_sql($consulta, __LINE__,$debug_sql,__FILE__);
        $resultado = DB_query($consulta, $db);

        if (DB_num_rows($resultado) > 0) {
            $renglon_comentario = DB_fetch_array($resultado);
            $comentario_factura = $renglon_comentario ["invtext"];
            $mostrarComentarios = $renglon_comentario ["showcomments"]; // 0 no muestra comentarios, 1 si los muestra
        }
    } else {
        // consultar comentarios del documento
        if (!empty($orderNo)) {
            $consulta = "Select invtext, showcomments
                        from debtortrans
                        where debtortrans.order_= '" . $orderNo . "' 
                        AND debtortrans.type='".$typeinvoice."'";

            debug_sql($consulta, __LINE__,$debug_sql,__FILE__);

            $resultado = DB_query($consulta, $db);

            if (DB_num_rows($resultado) > 0) {
                $renglon_comentario = DB_fetch_array($resultado);
                $comentario_factura = $renglon_comentario ["invtext"];
                $mostrarComentarios = $renglon_comentario ["showcomments"]; // 0 no muestra comentarios, 1 si los muestra
            }
        }
    }

    //EPM
    //No mostrar el bombeo como partida mandarla a informacion adicional
    //se deja en stockmoves
    $arrayBDBombeo = array('erptycqsa' , 'erptycqsa_CAPA', 'erptycqsa_DES' );
    if(in_array($_SESSION['DatabaseName'], $arrayBDBombeo)){
        if($typeinvoice != 0 and $transnoinovice != 0) {
                $sqlBombeo="SELECT stockmaster.description FROM stockmoves LEFT JOIN stockmaster ON stockmoves.stockid = stockmaster.stockid WHERE stockmoves.stockid ='SB' AND stockmoves.transno='".$transnoinovice."' AND stockmoves.type='".$typeinvoice."' GROUP BY stockmoves.stockid;";
                $rsBombeo=DB_query($sqlBombeo,$db);
        }else{
            if (!empty($orderNo)) {
                $consulta = "Select invtext, showcomments,transno
                            from debtortrans
                            where debtortrans.order_= '" . $orderNo . "' 
                            AND debtortrans.type='".$typeinvoice."'";

                $resultado = DB_query($consulta, $db);
                $renglon_comentario = DB_fetch_array($resultado);

                $sqlBombeo="SELECT stockmaster.description FROM stockmoves LEFT JOIN stockmaster ON stockmoves.stockid = stockmaster.stockid WHERE stockmoves.stockid ='SB' AND stockmoves.transno='".$renglon_comentario['transno']."' AND stockmoves.type='".$typeinvoice."' GROUP BY stockmoves.stockid;";
                $rsBombeo=DB_query($sqlBombeo,$db);
            }
        }

        if(!empty($comentario_factura) or $comentario_factura!=""){
            $comentario_factura.=", ";
        }

        $separacion="";
        if( DB_num_rows($rsBombeo) > 1 ){
            $separacion=", ";
        }
        
        while ($rowBombeo = DB_fetch_array($rsBombeo)) {
          $comentario_factura.=  $rowBombeo['description'].$separacion;
        }
    }
    
    if (!empty($comentario_factura) and $mostrarComentarios == 1) {
        $comentarios = $domXml->createAttribute("comentarios");
        $comentarios->value = ReglasXCadena(utf8_encode($comentario_factura));

        $comprobante->item(0)->appendChild($comentarios);
    } else {
        if ($encabezadoLine [19] != '' and $mostrarComentarios == 1) {
            $comentarios = $domXml->createAttribute("comentarios");
            $comentarios->value = $encabezadoLine [19];
            $comprobante->item(0)->appendChild($comentarios);
        }
    }

    if ($encabezadoLine [9] != '') {
        $descuento = $domXml->createAttribute("descuento");

        if(empty($Descuento33) or $Descuento33 ==""){
            $descuento->value = "$encabezadoLine[9]";
        }else{
            $descuento->value = $Descuento33;
        }
        
        $comprobante->item(0)->appendChild($descuento);
    }

    $input = str_replace(chr(13) . chr(10) . '0', '@%', utf8_decode($txtinput));
    $arraycadena = array();
    $arraycadena = explode('@%', $input);
    $embarquerLine = "";//explode('|', $arraycadena [count($arraycadena) - 1]);
    $nombre_embarque = "";//$embarquerLine [1];
    $arrayfolio = explode('|', $arraycadena [0]);
    $foliofact = $arrayfolio [2] . '|' . $arrayfolio [3]; 
    //
    if ($typeinvoice != 0 and $transnoinovice != 0) {
        $consulta = "Select custbranch.specialinstructions,
                        custbranch.brpostaddr1 as calle,
                        custbranch.brnumext,
                        custbranch.brnumint,
                        custbranch.brpostaddr2 as colonia,
                        custbranch.brpostaddr3 as municipio,
                        custbranch.brpostaddr5 as cp,
                        custbranch.brpostaddr4 as estado,
                        custbranch.custpais as pais
                    from debtortrans
                    inner join custbranch on debtortrans.debtorno= custbranch.debtorno and custbranch.branchcode = debtortrans.branchcode
                    where debtortrans.type= " . $typeinvoice . "
                        AND debtortrans.transno = '" . $transnoinovice . "'";
        debug_sql($consulta, __LINE__,$debug_sql,__FILE__);
        $resultado = DB_query($consulta, $db);
        if($_SESSION['UserID']=='desarrollo'){
           // echo '<pre>sql : '.$consulta.'</pre>';
        }
        if (DB_num_rows($resultado) > 0) {
            $renglon_embarque = DB_fetch_array($resultado);
            $nombre_embarque = utf8_encode(utf8_decode($renglon_embarque ["specialinstructions"]));
            $calle_embarque = $renglon_embarque ["calle"];
            $brnumext_embarque = $renglon_embarque ["brnumext"];
            $brnumint_embarque = $renglon_embarque ["brnumint"];
            $colonia_embarque = $renglon_embarque ["colonia"];
            //echo "<br>".$colonia_embarque."<br>";//cambios1
            $municipio_embarque = $renglon_embarque ["municipio"];
            $cp_embarque = $renglon_embarque ["cp"];
            $estado_embarque = $renglon_embarque ["estado"];
            $pais_embarque = $renglon_embarque ["pais"];
        }
    } else {
        if (!empty($orderNo)) {
            $consulta = "Select custbranch.specialinstructions,
                                custbranch.brpostaddr1 as calle,
                                custbranch.brnumext,
                                custbranch.brnumint,
                                custbranch.brpostaddr2 as colonia,
                                custbranch.brpostaddr3 as municipio,
                                custbranch.brpostaddr5 as cp,
                                custbranch.brpostaddr4 as estado,
                                custbranch.custpais as pais
                    from debtortrans
                    inner join custbranch on debtortrans.debtorno= custbranch.debtorno and custbranch.branchcode = debtortrans.branchcode
                    where debtortrans.order_= " . $orderNo . "
                        AND debtortrans.folio = '" . $foliofact . "'";
            debug_sql($consulta, __LINE__,$debug_sql,__FILE__);
            $resultado = DB_query($consulta, $db);
             if($_SESSION['UserID']=='desarrollo'){
               // echo '<pre>sql 2 : '.$consulta.'</pre>';
            }
            if (DB_num_rows($resultado) > 0) {
                $renglon_embarque = DB_fetch_array($resultado);
                $nombre_embarque = utf8_encode(utf8_decode($renglon_embarque ["specialinstructions"])); 
                $calle_embarque = $renglon_embarque ["calle"];
                $brnumext_embarque = $renglon_embarque ["brnumext"];
                $brnumint_embarque = $renglon_embarque ["brnumint"];
                $colonia_embarque = $renglon_embarque ["colonia"];
                //echo "<br>".$colonia_embarque."<br>";//cambios1
                $municipio_embarque = $renglon_embarque ["municipio"];
                $cp_embarque = $renglon_embarque ["cp"];
                $estado_embarque = $renglon_embarque ["estado"];
                $pais_embarque = $renglon_embarque ["pais"];
            }
        }
    }

    // echo "<br>mensaje de prueba";
    // echo "<br>typeinvoice: ".$typeinvoice;
    // echo "<br>transnoinovice: ".$transnoinovice;
    // echo "<br>embarquerLine: ".$embarquerLine [2];
    // echo "<br>numero elegido: ".$embarquerLine [1];
    // echo "<br>numero elegido 2: ".$embarquerLine [0];
    // echo "<br>embarquerLine array: ";
    // print_r($embarquerLine);
    // echo "<br>numero array: ".count($arraycadena);
    // echo "<br>input: ".$txtinput."<br>";
    for ($cad = 0; $cad <= count($arraycadena) - 1; $cad ++) {
        // Recorrer para obtener si agrega datos de embarque, posicion 9, debe tener calle
        $datos = explode('|', $arraycadena [$cad]);
        if ($datos [0] == '9') {
            $embarquerLine = $datos[2]; // Calle configurada
            $nombre_embarque = utf8_encode(utf8_decode($datos[1]));
        }
    }
    // Se agrega el nodo embarque
    //if ($embarquerLine [2] != '') {
    if ($embarquerLine != '') {
        $embarque = $domXml->createElement("Embarque");
        $nombreEnbarque = $domXml->createAttribute("nombre");
        // $nombreEnbarque->value=$embarquerLine[1];

        /*if ($_SESSION ['DatabaseName'] == 'erpjibe' or $_SESSION ['DatabaseName'] == 'erpjibe_CAPA' or $_SESSION ['DatabaseName'] == 'erpjibe_DES') {
              $nombre_embarque=$nombre_embarque;
        }else{
            $nombre_embarque=utf8_encode($nombre_embarque);
        }*/
        $nombreEnbarque->value = $nombre_embarque;
        $embarque->appendChild($nombreEnbarque);
        /*
         * $nombre_embarque= $renglon_embarque["specialinstructions"];
         * $calle_embarque = $renglon_embarque["calle"];
         * $brnumext_embarque = $renglon_embarque["brnumext"];
         * $brnumint_embarque = $renglon_embarque["brnumint"];
         * $colonia_embarque = $renglon_embarque["colonia"];
         * $municipio_embarque = $renglon_embarque["municipio"];
         * $cp_embarque = $renglon_embarque["cp"];
         * $estado_embarque = $renglon_embarque["estado"];
         * $pais_embarque = $renglon_embarque["pais"];
         */
        $domicilio = $domXml->createElement("Domicilio");
        $calle = $domXml->createAttribute("calle");
        $calle->value = utf8_encode(utf8_decode($calle_embarque));
        $domicilio->appendChild($calle);
        $noExt = $domXml->createAttribute("noExterior");
        $noExt->value = $brnumext_embarque;
        $domicilio->appendChild($noExt);
        $noInt = $domXml->createAttribute("noInterior");
        $noInt->value = $brnumint_embarque;
        $domicilio->appendChild($noInt);
        $colonia = $domXml->createAttribute("colonia");
        $colonia->value = utf8_encode(utf8_decode($colonia_embarque));
        $domicilio->appendChild($colonia);
        $localidad = $domXml->createAttribute("localidad");
        $localidad->value = utf8_encode('');
        $domicilio->appendChild($localidad);
        $referencia = $domXml->createAttribute("referencia");
        $referencia->value = utf8_encode('');
        $domicilio->appendChild($referencia);
        $municipio = $domXml->createAttribute("municipio");
        $municipio->value = utf8_encode(utf8_decode($municipio_embarque));
        $domicilio->appendChild($municipio);
        $estado = $domXml->createAttribute("estado");
        $estado->value = utf8_encode(utf8_decode($estado_embarque));
        $domicilio->appendChild($estado);
        $codigoPostal = $domXml->createAttribute("codigoPostal");
        $codigoPostal->value = $cp_embarque;
        $domicilio->appendChild($codigoPostal);
        $pais = $domXml->createAttribute("pais");
        $pais->value = utf8_encode(utf8_decode($pais_embarque));
        $domicilio->appendChild($pais); //

        $embarque->appendChild($domicilio);

        $receptor = $domXml->getElementsByTagName('Receptor')->item(0);
        $domXml->documentElement->insertBefore($embarque, $receptor->nextSibling);
    }

    $input = str_replace(chr(13) . chr(10) . '0', '@%', utf8_encode($txtinput));
    $arraycadena = array();
    $arraycadena = explode('@%', $input);
    $noConcep = 0;


    for ($line = 0; $line <= count($arraycadena) - 2; $line ++) {
        /* if($_SESSION['UserID'] == "desarrollo" and $debug){
          echo '<pre>ArrayCadena: '.print_r($arraycadena [$line]);
          } */
        $encabezadoLine = explode('|', $arraycadena [$line]);
        if ($encabezadoLine [0] == '5') {
            // echo '<pre>'.print_r($encabezadoLine);
            $descuento1 = $encabezadoLine [10];
            $descuento2 = $encabezadoLine [11];
            $descuento3 = $encabezadoLine [12];
            $importeCondescuentos = $encabezadoLine [13];
            $almacen = $encabezadoLine [17];
            $emision = '';
            if (isset($encabezadoLine [18])) {
                $emision = $encabezadoLine [18];
            }

            $trablinea = '';
            if (isset($encabezadoLine [19])) {
                $trablinea = $encabezadoLine [19];
            }

            // echo '<br>Var Emision:'.$emision;
            $concepto = $xpath->query('/Comprobante/Conceptos/Concepto');
            
            if ($concepto->length > 0) {

                if (empty($descuento1) == true) {
                    $descuento1 = 0;
                }

                $desc1 = $domXml->createAttribute('descuento1');
                $desc1->value = "$descuento1";

                $concepto->item($noConcep)->appendChild($desc1);

                if (empty($descuento1) == true) {
                    $descuento2 = 0;
                }
                $desc2 = $domXml->createAttribute('descuento2');
                $desc2->value = "$descuento2";
                $concepto->item($noConcep)->appendChild($desc2);

                if (empty($descuento1) == true) {
                    $descuento3 = 0;
                }
                $desc3 = $domXml->createAttribute('descuento3');
                $desc3->value = "$descuento3";
                $concepto->item($noConcep)->appendChild($desc3);

                if (empty($descuento1) == true) {
                    $importeCondescuentos = 0;
                }
                $importeDescuentos = $domXml->createAttribute('importeDescuentos');
                $importeDescuentos->value = "$importeCondescuentos";
                $concepto->item($noConcep)->appendChild($importeDescuentos);

                $almacen1 = $domXml->createAttribute('almacen');
                $SQL = "Select Titulo,Texto,consulta,noColumns from PDFTemplates where tipodocto=" . $tipodocto . " AND Texto='SHOW_ALMACEN_DESCRIPTION'";
                $ErrMsg = _('El Sql que fallo fue');
                $DbgMsg = _('No se pudo obtener los datos de la unidad de negocio');
                debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                if (DB_num_rows($Result) > 0) {
                    $Sel_Almacen = "SELECT locationname FROM `locations` WHERE `loccode` = '" . $almacen . "'";
                    debug_sql($Sel_Almacen, __LINE__,$debug_sql,__FILE__);
                    $res_almacen = DB_query($Sel_Almacen, $db);
                    list($almacen) = DB_fetch_array($res_almacen);
                    $almacen = $almacen;
                }

                $almacen1->value = "$almacen";
                $concepto->item($noConcep)->appendChild($almacen1);

                $emision1 = $domXml->createAttribute('emision');
                $emision1->value = $emision;

                $concepto->item($noConcep)->appendChild($emision1);

                // Ajuste Importe Concepto
                $importeAjuste = $domXml->createAttribute('importeAjuste');

                /* if($_SESSION['UserID'] == "desarrollo" and $debug){
                  echo '<pre>Ajustes: '.print_r($ajustes);
                  } */

                $importeAjuste->value = $ajustes ['conceptos'] [$noConcep];
                $concepto->item($noConcep)->appendChild($importeAjuste);

                $stockidtemp = $concepto->item($noConcep);
                $stockX = $xpath->query('/Comprobante/Conceptos/Concepto');
                $stockidstr = $stockX->item($noConcep)->getAttribute('noIdentificacion');

                // para las notas de credito directas
                $Descripcion = $stockX->item($noConcep)->getAttribute('descripcion');


                // $sqlfacturasrelacion = "SELECT  notesorders_invoice.*, debtortrans.`ovamount`, debtortrans.`ovgst`, round((notesorders_invoice.monto/ ( debtortrans.`ovamount`+ debtortrans.`ovgst`))*100,2) AS porcentaje, debtortrans.folio, Round(abs(debtortransmovs.ovgst) / abs(debtortransmovs.ovamount), 2)  as porcentaje2
                // FROM notesorders_invoice
                // INNER JOIN  debtortrans ON  debtortrans.id = transid_relacion
                // INNER JOIN debtortransmovs ON debtortransmovs.type = debtortrans.type AND debtortransmovs.transno = debtortrans.transno
                // WHERE transid = (SELECT d.id from debtortrans d WHERE type = '".$typeinvoice."' AND transno='".$transnoinovice."');";
                $sqlfacturasrelacion = "SELECT  notesorders_invoice.monto, round((notesorders_invoice.monto/ ( debtortrans.`ovamount`+ debtortrans.`ovgst`))*100,2) AS porcentaje, debtortrans.folio, Round(abs(debtortransmovs.ovgst) / abs(debtortransmovs.ovamount), 2)  as porcentaje2, debtortransmovs.id
                FROM notesorders_invoice
                JOIN  debtortrans ON  debtortrans.id = transid_relacion
                JOIN  debtortrans debtortrans2 ON debtortrans2.id = notesorders_invoice.transid 
                JOIN debtortransmovs ON debtortransmovs.type = debtortrans2.type AND debtortransmovs.transno = debtortrans2.transno AND round(notesorders_invoice.monto, 2) = round(abs(debtortransmovs.ovgst) + abs(debtortransmovs.ovamount), 2)
                WHERE transid = (SELECT d.id from debtortrans d WHERE type = '".$typeinvoice."' AND transno='".$transnoinovice."')";
                $resultfacturas = DB_query($sqlfacturasrelacion, $db);
                $numRegisDesc = 1;
                $facturasrelacion = "";
                // echo "<br>porcentaje2: ".$encabezadoLine [16];
                while ($myrowfa = DB_fetch_array($resultfacturas)) {
                    // if ($numRegisDesc == ($noConcep + 1)) {
                    // echo "<br>porcentaje2: ".$myrowfa['porcentaje2'];
                    // echo "<br>folio: ".$myrowfa['folio'];
                    // echo "<br>********";
                    if ($encabezadoLine [16] == $myrowfa['porcentaje2']) {
                        if (strpos($facturasrelacion, ", ".$myrowfa['porcentaje']." % al saldo del folio: ".$myrowfa['folio']) === false) {
                            $facturasrelacion .=", ".$myrowfa['porcentaje']." % al saldo del folio: ".$myrowfa['folio'];
                        }
                    }
                    $numRegisDesc ++;
                }
                // echo "<br>facturasrelacion: ".$facturasrelacion;
                $stockX->item($noConcep)->setAttribute("descripcion",$Descripcion." ".$facturasrelacion);
                //$Concepto->item ($noConcep)->setAttribute("descripcion",$myrowgetitem['description']." ".$facturasrelacion);

                // para las notas de credito directas

                $sqlpropertyes = "SELECT DISTINCT p.InvoiceValue AS val,
                                                    p.complemento,
                                                    sp.label,
                                                    description
                                    FROM salesstockproperties p,
                                         stockcatproperties sp,
                                         stockmaster sm
                                    WHERE p.stkcatpropid=sp.stkcatpropid
                                      AND sp.categoryid=sm.categoryid
                                      AND sp.reqatprint=1
                                      AND p.orderno='" . $orderNo . "'
                                      AND p.typedocument='" . $typeinvoice . "'
                                      AND sm.stockid='" . $stockidstr . "'";
                //$resultint=DB_query($sqlpropertyes,$db);

                /* if ($_SESSION ['UserID'] == 'desarrollo') {
                  echo '<pre><br>$sqlpropertyes: '. $sqlpropertyes;

                  // echo '<pre><br>$stockidtemp: '. $stockidtemp;
                  } */
                //$impresion = false;
                /* while ($myrowint=DB_fetch_array($resultint)){
                  $impresion = true;
                  $descriptioncategory = $descriptioncategory.$myrowint['description'];
                  } */

                /* if($impresion == true){
                  if($_SESSION['UserID'] == "desarrollo"){
                  echo 'entro';
                  }
                  //                        $concepto->item ( $noConcep )[4]=$concepto->item ( $noConcep )[1].$myrowint['description'];
                  if(is_object($stockX->item($noConcep))){
                  $stockX->item($noConcep)->setAttribute('descripcion',$stockidstr." ".$myrowint['description']);
                  }
                  }
                  else{
                  //$Concepto->item ($count)->setAttribute("descripcion",$myrowgetitem['description']);
                  } */
                //
                //Agregar el nombre del trabajador a la descipcion de la partida        
                $SQLtrab = $sqlpropertyes . " AND orderlineno='" . $trablinea . "'";
                debug_sql($SQLtrab, __LINE__,$debug_sql,__FILE__);
                $resultint = DB_query($SQLtrab, $db);
                $myrowint = DB_fetch_array($resultint);
                if (!empty($myrowint['val'])) {
                    $trabajador = $domXml->createAttribute('trabajador');
                    $trabajador->value = ReglasXCadena($myrowint['val']);
                    $concepto->item($noConcep)->appendChild($trabajador);
                }

                $sqlmedidas = "SELECT stockserialitems.serialno,
                                                    stockserialitems.thickness,
                                                    stockserialitems.width,
                                                    stockserialitems.large,
                                                    stockserialitems.customs_number,
                                                    stockserialitems.customs_date,
                                                    stockserialitems.pedimento
                                            FROM salesorderdetails inner join 
                                                INNER JOIN salesorders ON salesorderdetails.orderno = salesorders.orderno 
                                                INNER JOIN salesorderstockserialsinvoiced ON salesorderdetails.orderno = salesorderstockserialsinvoiced.orderno 
                                                    AND salesorderstockserialsinvoiced.orderdetailno = salesorderdetails.orderlineno
                                                INNER JOIN stockserialitems ON stockserialitems.serialno = salesorderstockserialsinvoiced.serialno 
                                                    AND stockserialitems.loccode =salesorderdetails.fromstkloc 
                                                    AND stockserialitems.localidad = salesorderdetails.localidad
                                            WHERE salesorderstockserialsinvoiced.type = '" . $typeinvoice . "'
                                            AND salesorderstockserialsinvoiced.transno = '" . $transnoinovice . "' 
                                            AND salesorderstockserialsinvoiced.orderno = '" . $orderNo . "'
                                            AND salesorderstockserialsinvoiced.orderdetailno = '" . $trablinea . "'
                                            AND salesorderstockserialsinvoiced.stockid = '" . $stockidstr . "'";


                $sqlmedidas = "SELECT salesorderstockserialsinvoiced.* ,
                                                    stockserialitems.customs_number,
                                                    stockserialitems.customs_date,
                                                    stockserialitems.pedimento,     categoryid
                                            FROM salesorderstockserialsinvoiced 
                                                        LEFT JOIN stockserialitems ON stockserialitems.serialno = salesorderstockserialsinvoiced.serialno 
                                                        AND salesorderstockserialsinvoiced.loccode = stockserialitems.loccode 
                                                        AND salesorderstockserialsinvoiced.localidad = stockserialitems.localidad
                                                        AND salesorderstockserialsinvoiced.stockid = stockserialitems.stockid
                                                        inner join stockmaster ON salesorderstockserialsinvoiced.stockid = stockmaster.stockid
                                            WHERE salesorderstockserialsinvoiced.transno = '" . $transnoinovice . "' 
                                                AND salesorderstockserialsinvoiced.type = '" . $typeinvoice . "' 
                                                AND salesorderstockserialsinvoiced.stockid = '" . $stockidstr . "' 
                                                AND salesorderstockserialsinvoiced.orderno = '" . $orderNo . "'
                                                AND salesorderstockserialsinvoiced.orderdetailno = '" . $trablinea . "'";
                debug_sql($sqlmedidas, __LINE__,$debug_sql,__FILE__);
                $resmedidas = DB_query($sqlmedidas, $db);
               
                debug_sql($sqlmedidas, __line__,false,__FILE__);
                $medserie = "";
                $medthick = "";
                $medwidth = "";   
                $medlarge = "";
                $descripmed = ""; //
            while($mrowmedidas = DB_fetch_array($resmedidas)){
                 $serie = explode('X', $mrowmedidas['serialno']);
                    //$mrowmedidas = DB_fetch_array($resmedidas);
                    $pietablon =  ($serie[1] * $serie[2] * $serie[3]) / 12;
                    $medserie2 = '';
                    $medserie3 = '';
                    if($mrowmedidas['categoryid']==19){
                        $medserie2 =  $serie[1] . "X" . $serie[2] . "X" . $serie[3] . 
                        $medserie3  = " PieTablon: " .  number_format($pietablon,2);
                    }
                    $medserie .= "<br> Serie " . $serie[0]." [" . $mrowmedidas['moveqty'] . " PZ " .$medserie2 ."]".$medserie3;
                    
                    
                    
                    if(isset($mrowmedidas['customs_number']) and $mrowmedidas['customs_number']>0){
                        $aduana = "Aduana: ".$mrowmedidas['customs_number'];
                    }
                    if(isset($mrowmedidas['pedimento']) and $mrowmedidas['pedimento']>0){
                        $Pedimento = "Pedimento: ".$mrowmedidas['pedimento'];
                    }
                    if(isset($mrowmedidas['customs_date']) and $mrowmedidas['customs_date']!='0000-00-00'){
                        $Fecha = 'Fecha Entrada: '.$mrowmedidas['customs_date'];
                    }
                    
                  //  $medserie .= "<br>".$aduana .' '.$Pedimento.' '.$Fecha;
                    
            } 
                if (DB_num_rows($resmedidas) > 0 && $_SESSION['UsarMedidas'] == 1) {
                   
                    if ($mrowmedidas['thickness'] <> 0) {
                        $medGrueso = " Grueso " . $mrowmedidas['thickness'];
                    }

                    if ($mrowmedidas['width'] <> 0) {
                        $medAncho = " Ancho  " . $mrowmedidas['width'];
                    }
                    if ($mrowmedidas['large'] <> 0) {
                        $medLargo = " Largo " . $mrowmedidas['large'];
                    }
                    $descripmed = $medserie;
                } else {
                    $numberserie= "";
                    $xserie=0;
                    if (!strpos("@".$_SESSION ['DatabaseName'], "gruposervillantas")) {
                        $sqlserial = "SELECT serialno
                                                        FROM stockmoves 
                                                        INNER JOIN stockserialmoves ON stockserialmoves.stockmoveno = stockmoves.stkmoveno
                                                        WHERE stockmoves.type = '" . $typeinvoice . "'
                                                        AND stockmoves.transno = '" . $transnoinovice . "'
                                                        AND stockmoves.reference = '" . $orderNo . "'
                                                        AND stockmoves.ref4 = '" . $trablinea . "'
                                                        AND stockmoves.stockid = '" . $stockidstr . "'";
                        debug_sql($sqlserial, __LINE__, $debug_sql, __FILE__);
                        $resserial = DB_query($sqlserial, $db);
                        if (DB_num_rows($resserial) > 0) {
                            $myrowserial = DB_fetch_array($resserial);
                            $descripmed = " SERIE " . $myrowserial['serialno'];
                        }
                    }else{
                        $sqlserials="SELECT DISTINCT stockserialitems.serialno AS serie
                            FROM workorders 
                            INNER JOIN stockserialitems ON workorders.wo= stockserialitems.wo
                            WHERE orderno= '".$orderNo."'
                            AND stockserialitems.customs= '".$stockidstr."' 
                            AND qualitytext NOT LIKE '%rechaz%' AND stockserialitems.serialno NOT LIKE 'R-%'
                            AND stockserialitems.stockid= 'cascli'";

                            $resultado= DB_query($sqlserials,$db);
                            
                            while ($myrownseries=DB_fetch_array($resultado)) {
                                if ($stockidstr != 'CASCLI'){
                                    
                                    $consulta= "SELECT DISTINCT stockserialitems.serialno AS serie
                                                FROM workorders 
                                                INNER JOIN stockserialitems ON workorders.wo= stockserialitems.wo
                                                WHERE orderno= '".$orderNo."'
                                                AND stockserialitems.customs= '".$stockidstr."'
                                                AND qualitytext LIKE '%rechaz%' 
                                                AND stockserialitems.serialno LIKE '%".$myrownseries['serie']."%';";
                                    $resultado2= DB_query($consulta, $db);

                                    if ($registro=DB_fetch_array($resultado2)){
                                        continue;
                                    }
                                }                       
                                
                                if ($xserie==0){
                                    $numberserie=' Series: '.$myrownseries['serie'];
                                }else{
                                    $numberserie=$numberserie.','.$myrownseries['serie'];
                                }
                                
                                $xserie++;
                            }
                            $descripmed = $numberserie;
                    }
                    
                }
                if (empty($descripmed) == false) {
                    $XmlMed = $domXml->createAttribute('Medidas');
                    $XmlMed->value = ReglasXCadena($descripmed);
                    $concepto->item($noConcep)->appendChild($XmlMed);
                }

                /* if($_SESSION['UserID'] == "desarrollo" and $debug){
                  //                echo '<pre>Conceptos ->: '.print_r(explode('|',$concepto->item ( $noConcep )->firstChild->nodeValue));
                  } */
            }
            $noConcep = $noConcep + 1;
        }
    }

    $SQL = "Select Titulo,Texto,consulta,noColumns from PDFTemplates where tipodocto=" . $tipodocto;
    $ErrMsg = _('El Sql que fallo fue');
    $DbgMsg = _('No se pudo obtener los datos de la unidad de negocio');
    if ($_SESSION['UserID'] == "desarrollo"){
        // echo "<br>transno:". $transnoinovice . '" , type="' . $typeinvoice ;
        // echo "<br>SQL:<pre>".$SQL."</pre>";
    }
    $rowDebtorTransId=0;
    debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
    //echo "<br>".$SQL." impresion";
    $orderNoCommit=$orderNo;
    if (DB_num_rows($Result) > 0) {
        while ($row = DB_fetch_array($Result)) {
            $query = $row ['consulta'];

            //echo "<br>consulta: ".$query." impresion";
            if ($query != null && !empty($query)) {
                // echo "Titulo:".$row['Titulo'];
                if (($row ['Titulo'] != 'InformacionSucursales')) {
                    // Esta condicion se hizo para Tycqsa, si el tipo de doc. es 22 pasar el debtortrans id en los sqls de PDFTemplates ...
                    if ($debtorId == 0 AND $typeinvoice == '12' AND ( $_SESSION ['DatabaseName'] == 'gruposervillantas' OR $_SESSION ['DatabaseName'] == 'gruposervillantas_DES' OR $_SESSION ['DatabaseName'] == 'gruposervillantas_CAPA')) {

                        $sql = 'SELECT id FROM debtortrans where transno="' . $transnoinovice . '" AND type="' . $typeinvoice . '"';
                        $result = DB_query($sql, $db);
                        $myrow = DB_fetch_array($result);
                        $orderNo = $myrow['id'];
                    }

                    if($_SESSION ['DatabaseName'] == 'erptycqsa' OR $_SESSION ['DatabaseName'] == 'erptycqsa_DES' OR $_SESSION ['DatabaseName'] == 'erptycqsa_CAPA'){
                        if($row['Titulo'] == 'InformacionBancaria'){
                            $sql = 'SELECT id FROM debtortrans where transno="' . $transnoinovice . '" AND type="' . $typeinvoice . '"';
                            if($_SESSION['UserID'] == "desarrollo"){
                                //echo "<pre>sql: ".$sql;
                            }
                            $result = DB_query($sql, $db);
                            $myrow = DB_fetch_array($result);
                            $orderNo = $myrow['id'];
                        }else{
                            $orderNo=$orderNoCommit;
                        }
                    }

                    //Se agrego ya que el nodo de infoCliente no se muestra en el xmlImpresion opr que se busca por orden 
                    if (($tipodocto == 13 OR $tipodocto == 21) and ($row['Titulo'] == 'InformacionComercial' or $row['Titulo'] == 'InfoCliente')) {

                         $sqlDebtorTransId = 'SELECT id FROM debtortrans where transno="' . $transnoinovice . '" AND type="' . $typeinvoice . '"';
                        $resultDebtorTransId = DB_query($sqlDebtorTransId, $db);
                        $myrowDebtorTransId = DB_fetch_array($resultDebtorTransId);
                        $rowDebtorTransId = $myrowDebtorTransId['id'];
                    }
                        // redrogo PDFTemplates 

                    if ($tipodocto == 22 ) {
                        $query .= $debtorId;
                    }elseif ($tipodocto == 13 OR  $tipodocto == 21) {
                        $query .= $rowDebtorTransId;
                    } elseif ($tipodocto == 11 ) {
                        $query .= $orderNo;
                        if($row ['Titulo'] == 'InformacionBancaria'){
                            $query .= " AND debtortrans.type = 11";
                        }
                    }
                    else {
                        $query .= $orderNo;
                    }
                }


                $result2 = DB_query($query, $db, $ErrMsg, $DbgMsg, true);
                if (DB_num_rows($result2) > 0) {
                    $noColumns = $row ['noColumns'];
                    // GET result query config
                    if($row ['Texto']=='REMISION'){
                        //echo "<br> Textotubos = ".$row ['Texto']." <- ";
                        $cadenaRemisiones = "";
                        $contador = 0;
                        $contador2 = 0;
                        $numRegistros = DB_num_rows($result2);
                        $contador3 = $numRegistros;
                        //echo "NumRegistros".$numRegistros; 
                        while ($row2 = DB_fetch_array($result2)) {
                            
                            for ($i = 0; $i < $noColumns; $i ++) {
                                $cadenaRemisiones = $cadenaRemisiones.$row2 [$i]."      ";
                                $contador = $contador +1;
                            }
                            
                            $contador3 = $contador3 -1;
                            if(($contador == 2 OR $numRegistros ==1) OR ($contador == 1 AND $contador3 == 0 )) {
                                //echo "<br> Remisiones: ".$cadenaRemisiones;
                                if($contador2 == 0){
                                    //echo "<br> contador: ".$contador2;
                                    if ($row ['Titulo'] != null && !empty($row ['Titulo'])) {
                                    $nodeDB = $domXml->createElement($row ['Titulo']);
                                    } else {
                                        $nodeDB = $domXml->createElement('DefaultNode');
                                    }
                                }
                                   $node = $domXml->createElement("Descripciones");
                                    $attribute = $domXml->createAttribute("descripcion0");
                                    $attribute->value = $row ['Texto'];
                                    $node->appendChild($attribute);
                                    $nodeDB->appendChild($node);
                                    $comprobante->item(0)->appendChild($nodeDB);

                                    $nodeXPath = $xpath->query($row ['Titulo']);
                                    if ($noColumns == 1) {
                                        $valoretiqueta = $row ['Texto'];
                                        $etiqueta = $domXml->createAttribute("etiqueta");
                                        $etiqueta->value = "$valoretiqueta";
                                        $node->appendChild($etiqueta);
                                        //echo "<br> 917".$valoretiqueta;
                                    }
                                    $attribute = $domXml->createAttribute("descripcion0");
                                    $attribute->value = utf8_encode($cadenaRemisiones);
                                    $node->appendChild($attribute);
                                    $contador2 = $contador2 +1;

                                //}
                                $contador = 0;
                                $cadenaRemisiones = "";

                            }
                        }

                    }else{
                        while ($row2 = DB_fetch_array($result2)) {
                            $nodeXPath = $xpath->query($row ['Titulo']);
                            if ($nodeXPath->length > 0) {
                                // El nodo ya existe por lo que no se crea
                                $node = $domXml->createElement("Descripciones");
                                if ($noColumns == 1) {
                                    $valoretiqueta = $row ['Texto'];
                                    $etiqueta = $domXml->createAttribute("etiqueta");    
                                    $etiqueta->value = "$valoretiqueta";
                                    $node->appendChild($etiqueta);
                                    //echo "<br> 917".$valoretiqueta;
                                }
                                // se crean los atributos con respecto al numero de columnas de la query
                                for ($i = 0; $i < $noColumns; $i ++) {

                                    if($row ['Texto']=='Tarjeta Red M:' AND ( $_SESSION ['DatabaseName'] == 'erpplacacentro' OR $_SESSION ['DatabaseName'] == 'erpplacacentro_DES' OR $_SESSION ['DatabaseName'] == 'erpplacacentro_CAPA')){
                                        
                                        // se hace cambio para placacentro  para que tome el valor de salesorder
                                        $sqlEtiqueta="SELECT  salesorders.discountcard FROM salesorders  WHERE orderno=".$orderNo;
                                        //echo "<br>".$sqlEtiqueta." - ";
                                        $resultEtiqueta = DB_query($sqlEtiqueta, $db);
                                        $myrowEtiqueta = DB_fetch_array($resultEtiqueta);
                                        $attribute = $domXml->createAttribute("descripcion" . $i);
                                        //$attribute->value = $row2 [$i];
                                        $attribute->value = utf8_encode($myrowEtiqueta['discountcard']);
                                        $node->appendChild($attribute);

                                    }else{
                                        $attribute = $domXml->createAttribute("descripcion" . $i);
                                        //$attribute->value = $row2 [$i];
                                        $attribute->value = utf8_encode($row2 [$i]);   
                                        $node->appendChild($attribute);
                                    }
                                    
                                    //echo "<br> 925".utf8_encode($row2 [$i]);
                                }
                                $nodeXPath->item(0)->appendChild($node);
                                $comprobante->item(0)->appendChild($nodeXPath->item(0));
                            } else {
                                // Se crea el nodo especificado
                                $vrvalidacionrcp=0;
                               
                                if ($row ['Titulo'] != null && !empty($row ['Titulo'])) {
                                    $nodeXPath = $domXml->createElement($row ['Titulo']);
                                } else {
                                    $nodeXPath = $domXml->createElement('DefaultNode');
                                }
                                $node = $domXml->createElement("Descripciones");
                                if ($noColumns == 1) {
                                    $valoretiqueta = $row ['Texto'];
                                    $etiqueta = $domXml->createAttribute("etiqueta");
                                    if(($typeinvoice==200 OR $typeinvoice==12) && $valoretiqueta=="Condiciones de pago: "){
                                        $valoretiqueta= "";
                                        $vrvalidacionrcp=1;
                                        
                                    }    
                                    $etiqueta->value = "$valoretiqueta";
                                    $node->appendChild($etiqueta);
                                }
                                for ($ii = 0; $ii < $noColumns; $ii ++) {
                                    $attribute = $domXml->createAttribute("descripcion$ii");
                                    //$attribute->value = $row2 [$ii];
                                    if($vrvalidacionrcp==1){
                                        $attribute->value="";
                                    }else{
                                        $attribute->value = utf8_encode($row2 [$ii]);
                                    }
                                    $node->appendChild($attribute);
                                }
                                $nodeXPath->appendChild($node);
                                $comprobante->item(0)->appendChild($nodeXPath);
                            }
                        }

                    } //tubos
                    
                    // END WHILE
                }
            } else {
                if ($row ['Texto'] != null && !empty($row ['Texto'])) {
                    // Crea Nodos y atributos
                    if ($row ['Titulo'] != null && !empty($row ['Titulo'])) {
                        $nodeXPath = $xpath->query($row ['Titulo']);
                    } else {
                        $nodeXPath = $xpath->query('DefaultNode');
                    }
                    if ($nodeXPath->length > 0) {
                        // El nodo ya existe por lo que no se crea
                        $node = $domXml->createElement("Descripciones");
                        $attribute = $domXml->createAttribute("descripcion0");
                        $attribute->value = $row ['Texto'];
                        $node->appendChild($attribute);
                        $nodeXPath->item(0)->appendChild($node);
                        $comprobante->item(0)->appendChild($nodeXPath->item(0));
                    } else {
                        // Se crea el nodo especificado
                        if ($row ['Titulo'] != null && !empty($row ['Titulo'])) {
                            $nodeDB = $domXml->createElement($row ['Titulo']);
                        } else {
                            $nodeDB = $domXml->createElement('DefaultNode');
                        }
                        $node = $domXml->createElement("Descripciones");
                        $attribute = $domXml->createAttribute("descripcion0");
                        $attribute->value = $row ['Texto'];
                        $node->appendChild($attribute);
                        $nodeDB->appendChild($node);
                        $comprobante->item(0)->appendChild($nodeDB);
                    }
                }
            }

            if ($_SESSION['UserID'] == "desarrollo"){
                // echo "<br>titulo:".$row ['Titulo'];
                // echo "<br>Consulta:".$query;
            }
            
        }
    }


    // Creamos Nodo Pagares
    // $lastElemnt=$domXml->documentElement->lastChild;
    // $pagares=$domXml->createElement("Pagares");
    // $lastElemnt->parentNode->insertBefore($pagares,$lastElemnt->nextSibling);

    $array ["rfcEmisor"] = "$rfcEmisor";
    $array ["fechaEmision"] = "$fechaEmision";
    $xmlImpresion = $domXml->saveXml();

    //echo '<br>generaXMLIntermedio:'.htmlentities($xmlImpresion)."<br>";
    $array ["xmlImpresion"] = "$xmlImpresion";

    if($_SESSION['UserID'] == 'desarrollo'){
        //echo '<br>ver xml resultante:'.htmlentities($xmlImpresion)."<br>";
    }
    // echo '<br><br><pre>XML CAR :'.htmlentities($xmlImpresion);
    debug_sql($array, __LINE__,$debug_sql,__FILE__);
    return $array;
}

function ajustarXmlImpresion(&$domXml) {
    $decimales = 2;

    /*if($_SESSION['DatabaseName'] == 'erpmatelpuente' OR $_SESSION['DatabaseName'] == 'erpmatelpuente_CAPA' OR $_SESSION['DatabaseName'] == 'erpmatelpuente_DES'){
        $decimales = 6;
    }*/
    
    $xpath = new DOMXPath($domXml);
    $comprobante = $xpath->query("/Comprobante");
    $total = number_format($comprobante->item(0)->getAttribute("total"), $decimales, '.', '');
    
    $descuento = 0;
    if (!empty($comprobante->item(0)->getAttribute("descuento"))) {
        $descuento = $comprobante->item(0)->getAttribute("descuento");
    }

    $ivadocto = 0;
    if (!empty($comprobante->item(0)->getAttribute("ivadocumento"))) {
        $ivadocto = $comprobante->item(0)->getAttribute("ivadocumento");
    }

    $subtotal = number_format($comprobante->item(0)->getAttribute("subTotal"), $decimales, '.', '');
    $descuento = number_format($descuento, $decimales, '.', '');
    $ivadocto = number_format($ivadocto, $decimales, '.', '');
    $iva = $total - ($subtotal + $descuento);
    if ($iva != $ivadocto) {
        $iva = abs($ivadocto);
    }
    $totaldocto = number_format($comprobante->item(0)->getAttribute("totaldocumento"), $decimales, '.', '');

    if ($total != $totaldocto) {
        //$total = abs($totaldocto);
    }

    $moneda = $comprobante->item(0)->getAttribute("Moneda");
    $conceptos = $xpath->query('/Comprobante/Conceptos/Concepto');
    $totalConceptos = 0;
    $epsilon = 0.00001;
    $ajustes = array();

    $separa = explode(".", $total);
    $montoletra = $separa [0];
    $montoctvs2= $separa [1];
    $montoletra = Numbers_Words::toWords($montoletra, 'es');    

    if ($moneda=='MXN' or $moneda=='XXX'){
        $montoletra=ucwords($montoletra) . " Pesos ". $montoctvs2 ." /100 M.N.";
    }elseif ($moneda=='EUR'){
        $montoletra=ucwords($montoletra) . " Euros ". $montoctvs2 ." /100 EUR";
    }else{
        $montoletra=ucwords($montoletra) . " Dolares ". $montoctvs2 ."/100 USD";
    }

    $Datosbien = ($subtotal - $descuento) + $iva; //Datos del xml
    if ($_SESSION['UserID'] == "admin") {
        /*echo "<br>subtotal: ".$subtotal."<br>";
        echo "<br>descuento: ".$descuento."<br>";
        echo "<br>iva: ".$iva."<br>";
        echo "<br>total: ".$total."<br>";
        echo "<br>Datosbien: ".$Datosbien."<br>";*/
    }

    $Datos1 = explode('.', number_format($Datosbien,0,'.',''));
    $Datos2 = explode('.', number_format($total,0,'.',''));

    if ($Datos1[0] == $Datos2[0]) {
        //Si los datos estan correctos
        $ajustes ['total'] = $total;
        $ajustes ['subtotal'] = $subtotal;
        $ajustes ['iva'] = $iva;
        $ajustes ['cantidadLetra'] = $montoletra;
    }else{
        //Si el subtotal se timbro restando el descuento, sumarlo para impresion
        $ajustes ['total'] = $total;
        $ajustes ['subtotal'] = $subtotal+$descuento;
        $ajustes ['iva'] = $iva;
        $ajustes ['cantidadLetra'] = $montoletra;
    }
    
    for ($i = 0; $i < $conceptos->length; $i ++) {
        $concepto = $conceptos->item($i);
        $importe = number_format($concepto->getAttribute("importe"), $decimales, '.', '');
        //echo "<br> matel: ".number_format($concepto->getAttribute("importe"), $decimales, '.', '');
        $totalConceptos += $importe;
        $ajustes ['conceptos'] [$i] = $importe;
    }

    // No son iguales, comparaci�n para n�meros flotantes
    /*$diferencia = $subtotal - $totalConceptos;
    if ((abs($diferencia) < $epsilon) == false) {
        if ($conceptos->length > 0) {
            // Sumar al ultimo concepto la diferencia
            $ajustes ['conceptos'] [$conceptos->length - 1] += $diferencia;
        }
    }*/

    return $ajustes;
}

function generaXML($cadena_original, $tipocomprobante, $tagref, $serie, $folio, $iddocto, $carpeta, $orderno = 0, $db) {
    //echo "<br>".$cadena_original."<br>";

    global $xml, $cadena, $conn, $sello, $cadenasellar, $totalimporte;
    $banderaimpuestos = false;
    // echo '<br><pre>' . print_r ( $banderaimpuestos );
    $banderaconceptos = false;
    $cadena = str_replace(chr(13) . chr(10) . '0', '@%', $cadena_original);
    $tipocomprobante = strtolower($tipocomprobante);
    $noatt = array();
    $arraycadena = array();
    $nufa = $serie . $folio; // Junta el numero de factura serie + folio
    $impuestofact = 0;
    $cadenasellar = "";

    $xml = new DOMdocument('1.0', 'UTF-8');
    $root = $xml->createElement("Comprobante");
    $root = $xml->appendChild($root);

    cargaAtt($root, array(
        "xmlns" => "http://www.sat.gob.mx/cfd/2",
        "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
        "xsi:schemaLocation" => "http://www.sat.gob.mx/cfd/2  http://www.sat.gob.mx/sitio_internet/cfd/2/cfdv22.xsd"
    ));
    // $SQL=" SELECT l.taxid,a.address5,t.tagname,t.areacode,l.legalid,l.legalname as empresa,a.areadescription as legalname,
    // a.address1 as calle,a.address2 as noExterior,a.address3 as colonia,
    // a.address4 as localidad,a.address3 as municipio,a.address5 as estado,
    // a.cp as cp,
    // t.address1 as calleexpedido,t.address2 as noExteriorexpedido,
    // t.address3 as coloniaexpedido,
    // t.address4 as localidadexpedido,
    // t.address4 as municipioexpedido,
    // t.address5 as estadoexpedido,
    // t.cp as codigoPostalExpedido,
    // t.address6 as paisexpedido,
    // a.Anioaprobacion,
    // a.Noaprobacion,
    // a.Nocertificado,
    // l.FileSAT,
    // l.regimenfiscal
    // FROM areas a, tags t, legalbusinessunit l
    // WHERE a.areacode=t.areacode
    // and l.legalid=t.legalid
    // AND tagref='".$tagref."'";

    $SQL = " SELECT l.taxid,l.address5,t.tagname,t.areacode,l.legalid,l.legalname as empresa,a.areadescription as legalname,
            l.address1 as calle,l.address2 as colonia,
            l.address3 as localidad,l.address3 as municipio,l.address4 as estado,
            l.address5 as cp,
            t.address1 as calleexpedido,
            t.address2 as noExteriorexpedido,
            t.address3 as coloniaexpedido,
            t.address4 as localidadexpedido,
            t.address4 as municipioexpedido,
            t.address5 as estadoexpedido,
            t.cp as codigoPostalExpedido,
            t.address6 as paisexpedido,
            a.Anioaprobacion,
            a.Noaprobacion,
            a.Nocertificado,
            l.FileSAT,
            l.regimenfiscal
            FROM areas a, tags t, legalbusinessunit l
            WHERE a.areacode=t.areacode
            and l.legalid=t.legalid
            AND tagref='" . $tagref . "'";

    $ErrMsg = _('El Sql que fallo fue');
    $DbgMsg = _('No se pudo obtener los datos de la unidad de negocio');
    //echo $SQL;
    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
    if (DB_num_rows($Result) == 1) {
        $myrowtags = DB_fetch_array($Result);
        $rfc = trim($myrowtags ['taxid']);
        $keyfact = $myrowtags ['address5'];
        $nombre = $myrowtags ['tagname'];
        $area = $myrowtags ['areacode'];
        $legaid = $myrowtags ['legalid'];
        $legalname = $myrowtags ['empresa'];
    }
    
    $arraycadena = explode('@%', $cadena);
    // lee primero el arreglo y pone el monto de los productos
    $impuestosinifact = 0;
    $totalimporte = 0;
    for ($cad = 4; $cad <= count($arraycadena) - 1; $cad ++) {
        $linea = $arraycadena [$cad];
        $datos = explode('|', $linea);
        // echo '<br>Datos1.2:' . print_r ( $datos );
        if ($cad >= 4 and $datos [0] == '5') {

            if ($carpeta == 'Recibo') {
                $importe = $datos [6];
                $unidades = $datos [7];
            } elseif ($carpeta == 'NCargo' or $carpeta == 'NCreditoDirect') {
                $importe = $datos [13];
                $unidades = "unidades";
            } else {
                $importe = $datos [5] * $datos [3]; // $datos[13];
                // echo '<br>importe envia:'.$importe;
                $unidades = $datos [7];
            }
            $totalimporte = $totalimporte + $importe;
        } elseif ($cad >= 4 and $datos [0] == '6') {

            $impuestosinifact = $impuestosinifact + trim($datos [3]);
            // $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[1]))."|".trim(ReglasXCadena($datos[2]))."|".trim(ReglasXCadena($datos[3]));
        } // fin de if de cad
    }
    $totalimporte = number_format($totalimporte, 2, '.', '');
    // echo '<br>total:'.$totalimporte.'<br>';
    // exit;
    for ($cad = 0; $cad <= count($arraycadena) - 1; $cad ++) {
        $linea = $arraycadena [$cad];
        $datos = explode('|', $linea);
        // echo '<br>Datos1.1:' . print_r ( $datos );
        if ($cad == 0) {
            /* if($_SESSION['UserID'] == "desarrollo" and $debug){
              echo '<br><pre>CAD=0';
              } */
            $vendedor = $datos [15];
            $seriekm = $datos [15];
            $datosdos = explode('|', $arraycadena [1]);
            $aprobaxfolio = TraeAprobacionxFolio($rfc, $serie, $folio, $db);
            $aprobacionfolios = explode('|', $aprobaxfolio);
            $Certificado = $aprobacionfolios [0];
            $Noaprobacion = $aprobacionfolios [1];
            $anioAprobacion = $aprobacionfolios [2];
            $descuentofact = number_format($datos [9], 2, '.', '');
            if (empty($descuentofact)) {
                $descuentofact = 0;
            }

            cargaAtt($root, array(
                "version" => "2.2",
                "serie" => $serie,
                "folio" => $folio,
                "fecha" => str_replace(' ', 'T', str_replace('/', '-', $datos [4])),
                "sello" => "@",
                "noAprobacion" => trim($Noaprobacion),
                "anoAprobacion" => $anioAprobacion,
                "tipoDeComprobante" => trim($tipocomprobante),
                "formaDePago" => $datosdos [1],
                "noCertificado" => trim($Certificado),
                "certificado" => "@",
                "condicionesDePago" => 'MONEDA: ' . $datos [12] . ', TC:' . $datos [13],
                "subTotal" => $totalimporte,
                "descuento" => $descuentofact,
                "TipoCambio" => fnDecimalFormat($datos [13],2),
                "Moneda" => $datos [12],
                "total" => number_format(($totalimporte - $descuentofact) + $impuestosinifact, 2, '.', ''),
                "metodoDePago" => $datosdos [3],
                "LugarExpedicion" => $myrowtags ['municipioexpedido'] . ',' . $myrowtags ['estadoexpedido'],
                "NumCtaPago" => $datosdos [5]
            ));
            $fechaamece = str_replace(' ', 'T', str_replace('/', '-', $datos [4]));
            $cantidadletra = $datos [11];
            if (empty($datos [2])) { // Si no tiene serie
                $cadenasellar = $cadenasellar . "|2.2|" . trim(ReglasXCadena($datos [3])) . "|" . trim(ReglasXCadena(str_replace(' ', 'T', str_replace('/', '-', $datos [4]))));
            } else {
                $cadenasellar = $cadenasellar . "|2.2|" . trim(ReglasXCadena($datos [2])) . "|" . trim(ReglasXCadena($datos [3])) . "|" . trim(ReglasXCadena(str_replace(' ', 'T', str_replace('/', '-', $datos [4]))));
            }

            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($Noaprobacion)) . "|" . trim(ReglasXCadena($anioAprobacion)) . "|" . trim(ReglasXCadena($tipocomprobante));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datosdos [1])) . "|MONEDA: " . trim(ReglasXCadena($datos [12])) . ReglasXCadena(', TC:') . trim(ReglasXCadena($datos [13]));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($totalimporte));
            $cadenasellar = $cadenasellar . "|" . ReglasXCadena($descuentofact);
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena(number_format(($totalimporte - $descuentofact) + $impuestosinifact, 2, '.', '')));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datosdos [3]));

            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['municipioexpedido'] . ',' . $myrowtags ['estadoexpedido']));
            $cadenasellar = $cadenasellar . "|" . ReglasXCadena($datosdos [5]);
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [13]));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [12]));
        } elseif ($cad == 1) {
            /* if($_SESSION['UserID'] == "desarrollo" and $debug){
              echo '<br><pre>CAD=1';
              } */
            $emisor = $xml->createElement("Emisor");
            $emisor = $root->appendChild($emisor);
            // cargaAtt($emisor, array("rfc"=>$rfc,"nombre"=>$legalname));
            cargaAtt($emisor, array(
                "rfc" => trim($rfc),
                "nombre" => trim($legalname)
            ));
            $domfis = $xml->createElement("DomicilioFiscal"); // $xml->createElement("DomicilioFiscal");
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($rfc)) . "|" . trim(ReglasXCadena($legalname));

            $domfis = $emisor->appendChild($domfis);
            cargaAtt($domfis, array(
                "calle" => $myrowtags ['calle'],
                "noExterior" => $myrowtags ['noExterior'],
                "noInterior" => "",
                "colonia" => $myrowtags ['colonia'],
                "referencia" => $legalname,
                "municipio" => $myrowtags ['municipio'],
                "estado" => $myrowtags ['estado'],
                "pais" => "MEXICO",
                "codigoPostal" => $myrowtags ['cp']
            ));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['calle'])) . "|" . trim(ReglasXCadena($myrowtags ['noExterior']));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['colonia'])); // ."|".trim($myrowtags['municipio']);
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($legalname)) . "|" . trim(ReglasXCadena($myrowtags ['municipio']));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['estadoexpedido'])) . "|" . ReglasXCadena("MEXICO") . "|" . trim(ReglasXCadena($myrowtags ['cp']));

            $expedido = $xml->createElement("ExpedidoEn");
            $expedido = $emisor->appendChild($expedido);
            //CGA 23-09-2016 agegar telefonos a expedido
            cargaAtt($expedido, array(
                "calle" => $myrowtags ['calleexpedido'],
                "noExterior" => $myrowtags ['noExteriorexpedido'],
                "noInterior" => "",
                "colonia" => $myrowtags ['colonia'],
                "referencia" => $myrowtags ['tagname'],
                "municipio" => $myrowtags ['municipioexpedido'],
                "estado" => $myrowtags ['estadoexpedido'],
                "pais" => $myrowtags ['paisexpedido'],
                "codigoPostal" => $myrowtags ['codigoPostalExpedido']
            ));

            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['calleexpedido'])) . "|" . trim(ReglasXCadena($myrowtags ['noExteriorexpedido']));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['colonia'])); // ."|".trim($myrowtags['municipioexpedido']);
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['tagname'])) . "|" . trim(ReglasXCadena($myrowtags ['municipioexpedido']));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['estadoexpedido'])) . "|" . trim(ReglasXCadena($myrowtags ['paisexpedido']));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['codigoPostalExpedido']));

            // regimen fiscal
            $regimenfiscal = $xml->createElement("RegimenFiscal");
            $regimenfiscal = $emisor->appendChild($regimenfiscal);
            cargaAtt($regimenfiscal, array(
                "Regimen" => $myrowtags ['regimenfiscal']
            ));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['regimenfiscal']));

            // }// fin de si tiene rows el tagref
        } elseif ($cad == 2) {
            /* if($_SESSION['UserID'] == "desarrollo" and $debug){
              echo '<br><pre>CAD=2';
              } */
            $receptor = $xml->createElement("Receptor");
            $receptor = $root->appendChild($receptor);
            cargaAtt($receptor, array(
                "rfc" => trim($datos[2]),
                "nombre" => trim($datos [3])
            ));
            $rfccliente = trim($datos [2]);
            $debtorno = trim($datos [1]);
            // echo '<br>nombre cliente:'.$datos[3];
            // echo '<br>nombre dos:'.trim(ReglasXCadena($datos[3]));
            // echo '<br>nombre tres:'.DB_escape_string(trim(ReglasXCadena($datos[3])));

            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [2])) . "|" . trim(ReglasXCadena($datos [3]));

            $coloniarecep = $datos [8];
            //echo "<br> SendInvoicingV6_0 colonia: ".$coloniarecep."<br>";
            
            $telrecep = $datos [10];
            $cad = $cad + 1;
            $linea = $arraycadena [$cad];
            $datos = explode('|', $linea);
            // echo '<br>'.ReglasXCadena($coloniarecep).'<br>';
            // echo '<br>Datos1<pre>' . print_r ( $datos );
            $domicilio = $xml->createElement("Domicilio");
            $domicilio = $receptor->appendChild($domicilio);
            if ($rfccliente != 'XAXX010101000') {
                cargaAtt($domicilio, array(
                    "calle" => trim($datos [4]),
                    "noExterior" => trim($datos [5]),
                    "noInterior" => trim($datos [6]),
                    "colonia" => trim($coloniarecep),
                    "referencia" => trim($telrecep),
                    "localidad" => trim($datos [10]),
                    "municipio" => trim($datos [10]),
                    "estado" => trim($datos [11]),
                    "codigoPostal" => trim($datos [12]),
                    "pais" => $datos [3]
                ));
                // echo '<br>datos:'.$datos[5].'<br>';
                if (strlen(trim($datos [4])) > 0) {
                    $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [4]));
                }
                if (strlen(trim($datos [5])) > 0) {
                    $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [5]));
                }
                if (strlen(trim($datos [6])) > 0) {
                    $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [6]));
                }
                if (strlen(trim($coloniarecep)) > 0) {
                    $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($coloniarecep));
                }
                if (strlen(trim(trim($datos [10]))) > 0) {
                    $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [10]));
                }
                if (strlen(trim($telrecep)) > 0) {
                    $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($telrecep));
                }

                if (strlen(trim(trim($datos [10]))) > 0) {
                    $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [10]));
                }
                if (strlen(trim(trim($datos [11]))) > 0) {
                    $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [11]));
                }
                $cadenasellar = $cadenasellar . "|MEXICO";

                if (strlen(trim(trim($datos [12]))) > 0) {
                    $cadenasellar = $cadenasellar . "|" . trim($datos [12]);
                }
            } else {
                cargaAtt($domicilio, array(
                    "calle" => "",
                    "noExterior" => "",
                    "colonia" => "",
                    "referencia" => "",
                    "localidad" => "",
                    "municipio" => "",
                    "estado" => "",
                    "pais" => "MEXICO",
                    "codigoPostal" => ""
                ));

                $cadenasellar = $cadenasellar . "|MEXICO";
            }
        } elseif ($cad >= 4 and $datos [0] == '5') {
            /* if($_SESSION['UserID'] == "desarrollo" and $debug){
              echo '<br><pre>CAD>=4 DATOS 5';

              } */
            if ($banderaconceptos == false) {
                $conceptos = $xml->createElement("Conceptos");
                $conceptos = $root->appendChild($conceptos);

                $banderaconceptos = true;
            }
            $concepto = $xml->createElement("Concepto");
            $concepto = $conceptos->appendChild($concepto);
            if ($carpeta == 'Recibo') {
                $importe = $datos [6];
                $unidades = $datos [7];
            } elseif ($carpeta == 'NCargo' or $carpeta == 'NCreditoDirect') {
                $importe = $datos [13];
                $unidades = "unidades";
            } else {
                $importe = $datos [5] * $datos [3]; // $datos[13];
                $unidades = $datos [7];
            }

            /* if($_SESSION['UserID'] == "desarrollo" and $debug){
              //                echo '<pre>Contenido de los datos: '.print_r($datos);
              } */

            //      die("END");

            cargaAtt($concepto, array(
                "cantidad" => trim($datos [3]),
                "unidad" => trim($unidades),
                "noIdentificacion" => trim($datos [2]),
                "descripcion" => trim($datos [4] . $descrprop . " "),
                "valorUnitario" => trim($datos [5]),
                "importe" => trim($importe)
            ));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [3])) . "|" . trim(ReglasXCadena($unidades));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [2])) . "|" . trim(ReglasXCadena($datos [4]));
            // echo $cadenasellar;

            $totalimporte = $totalimporte + $importe;
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [5])) . "|" . trim(ReglasXCadena($importe));

            // informcion aduanera
            if (strlen($datos [14]) > 1) {
                $InformacionAduanera = $xml->createElement("InformacionAduanera");
                $InformacionAduanera = $concepto->appendChild($InformacionAduanera);

                cargaAtt($InformacionAduanera, array(
                    "numero" => trim($datos [15]),
                    "fecha" => trim($datos [16]),
                    "aduana" => trim($datos [14])
                ));

                $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [15])) . "|" . trim(ReglasXCadena($datos [16]));
                $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [14]));
            }
        } elseif ($cad >= 4 and $datos [0] == '6') {
            /* if($_SESSION['UserID'] == "desarrollo" and $debug){
              echo '<br><pre>CAD>=4 DATOS 6';

              } */
            // echo '<br><pre>BAnIMp2' . print_r ( $banderaimpuestos );
            if ($banderaimpuestos == false) {
                $impuestos = $xml->createElement("Impuestos");
                $impuestos = $root->appendChild($impuestos);
                $traslados = $xml->createElement("Traslados");
                $traslados = $impuestos->appendChild($traslados);

                $banderaimpuestos = true;
            }
            $traslado = $xml->createElement("Traslado");
            $traslado = $traslados->appendChild($traslado);

            cargaAtt($traslado, array(
                "impuesto" => trim($datos [1]),
                "tasa" => trim($datos [2]),
                "importe" => trim($datos [3])
            ));

            $impuestofact = $impuestofact + trim($datos [3]);
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [1])) . "|" . trim(ReglasXCadena($datos [2])) . "|" . trim(ReglasXCadena($datos [3]));
        }
        // fin de if de cad
    }
    // echo '<br>BanImp3<pre>' . print_r ( $banderaimpuestos );
    if ($banderaimpuestos == true) {
        $impuestos->SetAttribute("totalImpuestosTrasladados", $impuestofact);
    } else {
        $impuestos = $xml->createElement("Impuestos");
        $impuestos = $root->appendChild($impuestos);
    }
    // $root->SetAttribute("subTotal",$totalimporte);
    // $root->SetAttribute("total",($totalimporte+$impuestofact));

    if ($banderaimpuestos == false) {
        $cadenasellar = $cadenasellar; // ."||";
    } else {
        $cadenasellar = $cadenasellar . "|" . ReglasXCadena($impuestofact); // ."||";
    }

    $SQL = "
            SELECT custbranch.typeaddenda,typeaddenda.archivoaddenda
            FROM  custbranch
            INNER JOIN typeaddenda on custbranch.typeaddenda=typeaddenda.id_addenda
            WHERE taxid='" . $rfccliente . "'
                    AND debtorno='" . $debtorno . "'
                            ";

    $Result = DB_query($SQL, $db);
    if (DB_num_rows($Result) == 0) {
        $typeaddenda = 0;
    } else {
        $myrowpag = DB_fetch_array($Result);
        $typeaddenda = $myrowpag ['typeaddenda'];
        $fileaddenda = $myrowpag ['archivoaddenda'];
    }

    if ($typeaddenda > 0) {
        include_once ($fileaddenda);
    }

    $cadenasellar = '|' . $cadenasellar . "||";
    // echo '<br>cadena enviada:'.$cadenasellar.'<br>';
    // inicializa y termina la cadena original con el doble ||
    if ($_SESSION ['DatabaseName'] == 'erpmservice' or $_SESSION ['DatabaseName'] == 'erpmservice_CAPA' or $_SESSION ['DatabaseName'] == 'erpmservice_DES') {
        $aprobaxfolio = TraeAprobacionxFolio($rfccliente, $serie, $folio, $db);
        $aprobacionfolios = explode('|', $aprobaxfolio);
        $certificado = $aprobacionfolios [0];
        $Noaprobacion = $aprobacionfolios [1];
        $anioAprobacion = $aprobacionfolios [2];
    } else {
        $certificado = $myrowtags ['FileSAT'];
    }
    // echo $certificado;
    $maquina = trim(`uname -n`);
    // echo '<br>nombre maquina'.$maquina;
    // echo "<pre>".$cadenasellar;

    $ruta = "/var/www/html" . dirname($_SERVER ['PHP_SELF']) . "/companies/" . $_SESSION ['DatabaseName'] . "/SAT/" . str_replace('.', '', str_replace(' ', '', $legalname)) . "/";
    $file = $ruta . $certificado . ".key.pem"; // Ruta al archivo

    $pkeyid = openssl_get_privatekey(file_get_contents($file));
    openssl_sign($cadenasellar, $crypttext, $pkeyid, OPENSSL_ALGO_SHA1);
    openssl_free_key($pkeyid);
    $sello = base64_encode($crypttext); // lo codifica en formato base64
    $root->setAttribute("sello", $sello);
    $file = $ruta . $certificado . ".cer.pem"; // Ruta al archivo

    $file = utf8_encode($file);

    $datos = file($file);
    $certificado = "";
    $carga = false;
    for ($i = 0; $i < sizeof($datos); $i ++) {
        if (strstr($datos [$i], "END CERTIFICATE"))
            $carga = false;
        if ($carga)
            $certificado .= trim($datos [$i]);
        if (strstr($datos [$i], "BEGIN CERTIFICATE"))
            $carga = true;
    }

    $root->setAttribute("certificado", $certificado);
    // }}}
    // {{{ Genera un archivo de texto con el mensaje XML + EDI O lo guarda en cfdsello
    $xml->formatOutput = true;
    $todo = $xml->saveXML();
    $dir = "/var/www/html/" . dirname($_SERVER ['PHP_SELF']) . "/companies/" . $_SESSION ['DatabaseName'] . "/SAT/" . str_replace('.', '', str_replace(' ', '', $legalname)) . "/XML/" . $carpeta . "/";
    // echo $dir;
    // $dir = dirname($_SERVER['PHP_SELF'])."/SAT/";
    // echo "despues de save = "; var_dump($todo); echo "\n";
    if ($dir != "/dev/null") {
        $xml->formatOutput = true;
        // echo "entra".$dir.$nufa.'<br />';
        $xml->save($dir . $nufa . ".xml");
    } else {
        $paso = $todo;
        $conn->replace("cfdsello", array(
            "selldocu" => $nufa,
            "sellcade" => $cadena_original,
            "sellxml" => $paso
                ), "selldocu", true);
    }

    // }}}
    // echo "antes de return = $todo\n";
    // guardamos la cadena y sello en la base de datos*
    $sql = "update debtortrans
      set sello='" . $sello . "',
            cadena='" .utf8_decode( str_replace("&", "&amp;", DB_escape_string($cadenasellar)) )  . "'
                    where id=" . $iddocto;
    $ErrMsg = _('El Sql que fallo fue');
    $DbgMsg = _('No se pudo actualizar el sello y cadena del documento');
    $Result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
    //echo "function XSAInvoicing cadenasellar: ".$cadenasellar."      -    ";
    // echo '<pre>xml:<br>'.htmlentities($todo);
    $array ["xml"] = "$todo";
    $array ["cadenaOriginal"] = "$cadenasellar";
    $array ["cantidadLetra"] = $cantidadletra;

    if ($_SESSION ['UserID'] == "desarrollo") {
        //echo '<br> generaXML : ' . htmlentities($todo) . '<br>';
    }

    //echo "<br> SendInvoicingV6_0 xml: ".htmlentities($todo)."<br>";
    //echo "<br> SendInvoicingV6_0 cadenaOriginal: ".($cadenasellar)."<br>";

    // echo "Cantidad letra: ".$cantidadletra;

    return $array;
}

function cargaAtt(&$nodo, $attr) {
    // +-------------------------------------------------------------------------------+
    // | Ademas le concatena a la variable global los valores para la cadena origianl |
    // +-------------------------------------------------------------------------------+
    global $xml, $cadena;
    $quitar = array(
        'sello' => 1,
        'noCertificado' => 1,
        'certificado' => 1
    );
    foreach ($attr as $key => $val) {
        $val = preg_replace('/\s\s+/', ' ', $val); // Regla 5c
        $val = preg_replace('/\t/', ' ', $val); // Regla 5a
        $val = preg_replace('/\r/', ' ', $val); // Regla 5a
        $val = preg_replace('/\n/', ' ', $val); // Regla 5a
        $val = trim($val); // Regla 5b

        if (strlen($val) > 0) { // Regla 6
            $val = str_replace("|", "/", $val); // Regla 1
            if (detectUTF8($val)) {
                
                    
                
                $val = $val;
                
                
            } else{
                if ($key == 'TipoProceso'){
                    if ($_SESSION ['UserID'] == 'saplicaciones' ) {
                        for ($ii=0; $ii<strlen($val); $ii+=1){
                            $character=ord(substr($val,$ii,1));
                            //echo "<br>:"  . substr($val,$ii,1) . " => " . $character;
                        }
                        //echo "<br>ENTRA AKI 2";
                    }
                    
                    $val = mb_convert_encoding($val, "UTF-8", "ISO-8859-1");   
                }else{
                    $val = utf8_encode($val);
                }
            }
            $nodo->setAttribute($key, $val);
            if (!isset($quitar [$key]))
                if (substr($key, 0, 3) != "xml" && substr($key, 0, 4) != "xsi:")
                    $cadena .= $val . "|";
        }
    }
}

function detectUTF8($string) {
    return preg_match('%(?:
            [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
            |\xE0[\xA0-\xBF][\x80-\xBF]              # excluding overlongs
            |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
            |\xED[\x80-\x9F][\x80-\xBF]              # excluding surrogates
            |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
            |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
            |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
    )+%xs', $string);
}

// {{{ Funcion que concatena el valor a la cadena original
function catCadena($val) {
    // +-------------------------------------------------------------------------------+
    // | Concatena los atributos a la cadena original |
    // +-------------------------------------------------------------------------------+
    global $cadena;
    $val = preg_replace('/\s\s+/', ' ', $val); // Regla 5a y 5c
    $val = trim($val); // Regla 5b
    if (strlen($val) > 0) { // Regla 6
        $val = str_replace("|", "/", $val); // Regla 1
        if (detectUTF8($val))
            $val = $val;
        else
            $val = utf8_encode($val);

        $cadena .= $val . "|";
    }
}

function ReglasXCadena($val) {
    // +-------------------------------------------------------------------------------+
    // | Concatena los atributos a la cadena original |
    // +-------------------------------------------------------------------------------+
    // global $cadena;
    $val = preg_replace('/\s\s+/', ' ', $val); // Regla 5c
    $val = preg_replace('/\t/', ' ', $val); // Regla 5a
    $val = preg_replace('/\r/', ' ', $val); // Regla 5a
    $val = preg_replace('/\n/', ' ', $val); // Regla 5a
    $val = trim($val); // Regla 5b

    if (strlen($val) > 0) { // Regla 6
        $val = str_replace("|", "/", $val); // Regla 1
        if (detectUTF8($val))
            $val = $val;
        else
            $val = utf8_encode($val);
    }
    return $val;
}

function carga_eles($obj, $ele) {
    global $root, $xml;
    foreach ($ele as $key => $val) {
        $tmp = $xml->createElement($key, utf8_encode(trim($val)));
        $tmp = $obj->appendChild($tmp);
    }
    $tmp = $root->appendChild($obj);
}

// }}}
// {{{ carga_att : genera atributos al elemento indicado
function carga_att($obj, $ele) {
    global $root, $xml;
    foreach ($ele as $key => $val)
        $obj->setAttribute($key, utf8_encode(trim($val)));
}

function TraeAprobacionxFolio($rfcempre, $serieap, $folioap, &$db) {
    global $aprobacionxfoliox;
    $SQLAprobacion = "SELECT  anioAprobacion,noAprobacion,certificado
            FROM AprobacionFolios
            WHERE serie='" . $serieap . "'
                    AND " . $folioap . " BETWEEN Inicial AND final
          AND rfc='" . $rfcempre . "'";
    if($_SESSION['UserID'] == "desarrollo"){

     //echo '<pre><br>'.$SQLAprobacion;
    }
    $ResultAprobacion = DB_query($SQLAprobacion, $db);
    if (DB_num_rows($ResultAprobacion) > 0) {
        $myrowaprobacion = DB_fetch_array($ResultAprobacion);
        $aprobacionxfoliox = $myrowaprobacion ['certificado'] . '|' . $myrowaprobacion ['noAprobacion'] . '|' . $myrowaprobacion ['anioAprobacion'];
    }
    return $aprobacionxfoliox;
}

function TraeTimbreCFDI($cfdi) {
    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, $cfdi, $tags);
    xml_parser_free($parser);

    $elements = array(); // the currently filling [child] XmlElement array
    $stack = array();
    $DatosCFDI = array();
    foreach ($tags as $tag) {
        $index = count($elements);
        
        if ($tag ['type'] == "complete" || $tag ['type'] == "open") {
                
                $elements [$index] = new XmlElement ();
                $elements [$index]->name = $tag ['tag'];
                if (isset($tag ['attributes'])){
                    $elements [$index]->attributes = $tag ['attributes'];    
                }
                

            if ($elements [$index]->name == 'tfd:TimbreFiscalDigital') {
                $DatosCFDI = $tag ['attributes'];

            }else{
                $DatosCFDI['FechaTimbrado'] = "1900-01-01";
                $DatosCFDI['UUID'] = "Fake";
                $DatosCFDI['selloCFD'] = "Fake";
                $DatosCFDI['noCertificadoSAT'] = "Fake";
                $DatosCFDI['selloSAT'] = "Fake";
            }
            //$elements [$index]->content = $tag ['value'];
            if ($tag ['type'] == "open") { // push
                    $elements [$index]->children = array();
                    $stack [count($stack)] = &$elements;
                    $elements = &$elements [$index]->children;
            }    
            
            
        }
        if ($tag ['type'] == "close") { // pop
            $elements = &$stack [count($stack) - 1];
            unset($stack [count($stack) - 1]);
        }
    }
    return ($DatosCFDI);
}

// +-------------------------------------------------------------------------------+
// | Funcion para generacion de documentos de tipo CFDI |
// +-------------------------------------------------------------------------------+
function generaXMLCFDI($cadena_original, $tipocomprobante, $tagref, $serie, $folio, $iddocto, $carpeta, $orderno = 0, $db) {
    // **************************** //
    // No dejar echo en la funcion //
    // Afecta al punto de venta   //
    // **************************** //

    // SE AGREGA $_SESSION['FlagIva'] = 1 PARA QUE CALCULE SUMA TOTAL DE IVA

    if ($_SESSION['UserID'] == 'aenriquez' || $_SESSION['UserID'] == 'desarrollo' ){
        echo 'SendInvoicingV6_0.php:<br>'.$cadena_original;
    }

    global $xml, $cadena, $conn, $sello, $cadenasellar, $totalimporte;
    $banderaimpuestos = false;
    $banderaconceptos = false;
    $banderacomplemento = false;
    $banderaimpuestoslocales = false;
    $cadena = str_replace(chr(13) . chr(10) . '0', '@%', $cadena_original);
    //error_reporting ( E_ALL );
    $tipocomprobante = strtolower($tipocomprobante);
    $noatt = array();
    $arraycadena = array();
    $nufa = $serie . $folio; // Junta el numero de factura serie + folio
    $impuestofact = 0;
    $cadenasellar = "";
    $xml = new DOMdocument('1.0', 'UTF-8');
    //$xml = new DOMdocument ();
    $decimalplaces = 6;

    if ($_SESSION ['DecimalPlacesInvoice'] == '') {
        $_SESSION ['DecimalPlacesInvoice'] = 6;
    }
    if(isset($_SESSION ['DecimalPlacesInvoice'])){
        $decimalplaces = $_SESSION ['DecimalPlacesInvoice'];    
    }
    
    $root = $xml->createElement("cfdi:Comprobante");
    $root = $xml->appendChild($root);

    cargaAtt($root, array(
        "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
        "xmlns:cfdi" => "http://www.sat.gob.mx/cfd/3",
        "xmlns:ecfd" => "http://www.southconsulting.com/schemas/strict",
        "xsi:schemaLocation" => "http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd http://www.sat.gob.mx/implocal http://www.sat.gob.mx/sitio_internet/cfd/implocal/implocal.xsd",
        // "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
        "xmlns:implocal" => "http://www.sat.gob.mx/implocal",
            // "xsi:schemaLocation" => "http://www.sat.gob.mx/implocal http://www.sat.gob.mx/sitio_internet/cfd/implocal/implocal.xsd" 
    ));

    $SQL = " SELECT l.taxid,l.address5,t.tagname,t.areacode,l.legalid,l.legalname as empresa,a.areadescription as legalname,
            l.address1 as calle,
            l.address2 as colonia,
            l.address3 as localidad,
            l.address3 as municipio,
            l.address4 as estado,
            l.address5 as cp,
            t.address1 as calleexpedido,t.address2 as noExteriorexpedido,
            t.address3 as coloniaexpedido,
            t.address4 as localidadexpedido,
            t.address4 as municipioexpedido,
            t.address5 as estadoexpedido,
            t.cp as codigoPostalExpedido,
            t.address6 as paisexpedido,
            a.Anioaprobacion,
            a.Noaprobacion,
            a.Nocertificado,
            l.FileSAT,
            l.regimenfiscal,
            '' as noExterior

            FROM areas a, tags t, legalbusinessunit l
            WHERE a.areacode=t.areacode
            and l.legalid=t.legalid
            AND tagref='" . $tagref . "'";
    $ErrMsg = _('El Sql que fallo fue');
    $DbgMsg = _('No se pudo obtener los datos de la unidad de negocio');
    // echo $SQL;
    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
    if (DB_num_rows($Result) == 1) {
        $myrowtags = DB_fetch_array($Result);
        $rfc = trim($myrowtags ['taxid']);
        $rfc = strtoupper($rfc);
        $keyfact = $myrowtags ['address5'];
        $nombre = $myrowtags ['tagname'];
        $area = $myrowtags ['areacode'];
        $legaid = $myrowtags ['legalid'];
        $legalname = $myrowtags ['empresa'];
    }

    $arraycadena = explode('@%', $cadena);

    // lee primero el arreglo y pone el monto de los productos
    $impuestosinifact = 0;
    $totalimporte = 0;
    $totalDescuento = 0;
    $flagAnticipo = 0; //Si es anticipo el articulo
    for ($cad = 0; $cad <= count($arraycadena) - 1; $cad ++) {
        $linea = $arraycadena [$cad];
        $datos = explode('|', $linea);

        if ($cad == 0 and $datos [0] == '1') {
            $totalDescuento = $datos [9]; //Total descuento cambios ant
            //echo "<br>totalDescuento: ".$totalDescuento."<br>";
        }elseif ($cad >= 4 and $datos [0] == '5') {

            if ($carpeta == 'Recibo') {
                $importe = $datos [6];
                $unidades = $datos [7];
            } elseif ($carpeta == 'NCargo' or $carpeta == 'NCreditoDirect') {
                $importe = $datos [13];
                $unidades = "unidades";
            } else {
                //$totalDescuento = $datos[13]; //Total descuento cambios ant
                $importe = ($datos [5] * $datos [3]);// - $totalDescuento; // $datos[13]; 
                // echo '<br>importe envia:'.$importe;
                $unidades = $datos [7];
            }

            /*Anticipo*/
            //Si es un anticipo
            $sqlAnt = "SELECT flagadvance FROM stockmaster WHERE stockid = '".trim($datos [2])."'";
            $ErrMsg = _('El Sql que fallo fue');
            $DbgMsg = _('No se pudo obtener los datos del articulo');
            //echo "<br>".$sqlAnt."<br>";
            $ResultAnt = DB_query($sqlAnt, $db, $ErrMsg, $DbgMsg, true);
            if ($myrowAnt = DB_fetch_array($ResultAnt)) {
                if ($myrowAnt['flagadvance'] == 1) {
                    $flagAnticipo = $myrowAnt['flagadvance'];
                }
            }
            //echo "<br>flagadvance: ".$flagadvance."<br>";
            /*if ($flagadvance == 1) {
                //Obtener total de la partida para suma
                $sqlAnt = "SELECT (salesorderdetails.unitprice * salesorderdetails.quantity) + ((salesorderdetails.unitprice * salesorderdetails.quantity)*taxauthrates.taxrate) as importeAnticipo
                            FROM debtortrans
                            LEFT JOIN salesorderdetails ON salesorderdetails.orderno = debtortrans.order_
                            LEFT JOIN stockmaster ON stockmaster.stockid = salesorderdetails.stkcode
                            LEFT JOIN taxauthrates ON taxauthrates.taxcatid = stockmaster.taxcatid
                            WHERE folio = '".$serie."|".$folio."' and salesorderdetails.stkcode = '".trim($datos [2])."'";
                $ErrMsg = _('El Sql que fallo fue');
                $DbgMsg = _('No se pudo obtener los datos del articulo');
                //echo "<br>".$sqlAnt."<br>";
                $ResultAnt = DB_query($sqlAnt, $db, $ErrMsg, $DbgMsg, true);
                if (DB_num_rows($ResultAnt) == 1) {
                    $myrowAnt = DB_fetch_array($ResultAnt);
                    $importe = number_format($myrowAnt['importeAnticipo'], 2);
                }
                $importe = $datos [6];
                $importe = str_replace(',', '', $importe);
                $importe = number_format($importe, 2);
            }*/
            //echo "<br>importe tot: ".$importe."<br>";
            $importe = str_replace(',', '', $importe);
            $totalimporte = $totalimporte + $importe;
        } elseif ($cad >= 4 and ( $datos [0] == '7' /* or $datos [0] == '6' */)) {
            
            $impuestosinifact = $impuestosinifact + trim($datos [3]);

            // $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[1]))."|".trim(ReglasXCadena($datos[2]))."|".trim(ReglasXCadena($datos[3]));
        } // fin de if de cad
    }    
    /*if ($flagAnticipo == 1) {
        //Si viene anticipo restar descuento
        $totalimporte = number_format($totalimporte - $totalDescuento, $decimalplaces, '.', '');    
    }else{
        $totalimporte = number_format($totalimporte, $decimalplaces, '.', '');
    }*/

    $totalimporte = number_format($totalimporte, $decimalplaces, '.', '');

    if ($_SESSION['UserID'] == 'desarrollo' or $_SESSION['UserID'] == 'admin') { //Mostrar datos
        //echo "<br>totalimporte: ".$totalimporte."<br>";
        //echo "<br>totalimporte formato: ".number_format($totalimporte, 2)."<br>";
    }
    //Cadena de imp locales
    $cadimp = "";
    $tRet = 0;
    $tTras = 0;
    $descuentofactAM = 0;
    $sum = 0;
    
    if (!empty($_SESSION['Flag_Amortizacion'])){
    if ($_SESSION['Flag_Amortizacion'] == 1) {

        for ($cadnew = 5; $cadnew <= count($arraycadena) - 1; $cadnew++) {
            $renglon = $arraycadena[$cadnew];
            $reng = explode('|', $renglon);
            if ($reng[0] == '8') {
                $sum = $sum + $reng[3];
                if ($_SESSION['UserID'] == 'desarrollo'){
                    echo 'ingresa 8: '.$reng[3];
                    echo 'sum 8 : '.$sum;
                }
            }
        }

        for ($cadnew = 4; $cadnew <= count($arraycadena) - 1; $cadnew++) {
            $renglon = $arraycadena[$cadnew];
            $reng = explode('|', $renglon);

            if ($reng[0] == '5') {
                if ($reng[2] != 'AM') {
                    $subTotal = $subTotal + $reng[6];
                } elseif ($reng[2] == 'AM') {
                    $descuento = $descuento + abs($reng[6]);
                }
                $totalimporte = $subTotal;
                $descuentofactAM = $descuento;
            }
        }
    }}

    $cadenasellarimp = "";
    $banderageneraimpuestos= false;
    $banderaimpuestosretenidos = false;

    for ($cad = 0; $cad <= count($arraycadena) - 1; $cad ++) {

        //$ok = $tRetok;
        //  echo '->'.$tRet.'<br>';

        $linea = $arraycadena [$cad];

        $datos = explode('|', $linea); //
         //echo "<br>Dato<pre>CAD:" . $cad . "<br>" . var_dump( $datos );

        if ($cad == 0) {
           // echo "CAD: $cad";
            //echo "<br>Dato<pre>CAD:" . $cad . "<br>" . var_dump( $datos );
            $vendedor = $datos [15];
            $seriekm = $datos [15];
            $datosdos = explode('|', $arraycadena [1]);
            $aprobaxfolio = "";
            $aprobaxfolio = TraeAprobacionxFolio($rfc, $serie, $folio, $db);
            $aprobacionfolios = explode('|', $aprobaxfolio);
            //cambiar
            $Certificado = $aprobacionfolios [0];
            $Noaprobacion = $aprobacionfolios [1];
            $anioAprobacion = $aprobacionfolios [2];
            $descuentofact = number_format($datos [9], $decimalplaces, '.', '') + $descuentofactAM;
            if (empty($descuentofact)) {
                $descuentofact = 0;
            }
            
            // v alida tipo de documento
            $SQL = "select * from debtortrans where id=" . $iddocto;
            $Result1 = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
            if (DB_num_rows($Result1) == 1) {
                $myrowtype = DB_fetch_array($Result1);
                $tipodocumento = trim($myrowtype ['type']);
            }
            if ($tipodocumento != 12) {
                $datos [19] = '';
                $datos [20] = '';
                $datos [21] = '';
            }

            if ($_SESSION['FlagIva'] == 1) {
                
            }

            $subTotal = 0;
            //if ($flagAnticipo == 1 and preg_match("/erpgruposii/", $_SESSION['DatabaseName'])) {
            //    $subTotal = number_format($totalimporte - $totalDescuento, $decimalplaces, '.', '');
            //}else{
                $subTotal = number_format($totalimporte, $decimalplaces, '.', '');
            //}

            $total = number_format((($totalimporte - $totalDescuento) + $impuestosinifact - abs($sum)), $decimalplaces, '.', '');  //cambios ant ($totalimporte - $descuentofact), number_format((($totalimporte) + $impuestosinifact - abs($sum)), $decimalplaces, '.', '')

            if ( ($subTotal < 0.01 and $subTotal > 0) or $subTotal <= 0 ){
                $subTotal = (number_format(0, $decimalplaces, '.', ''));
            }

            if ( ($total < 0.01 and $total > 0) or $total <= 0 ){
                $total = (number_format(0, $decimalplaces, '.', ''));
            }
            
            //echo "<br>totalimporte: ".$totalimporte."<br>";
            //echo "<br>totalDescuento: ".$totalDescuento."<br>";
            //echo "<br>impuestosinifact: ".$impuestosinifact."<br>";
            //echo "<br>sum: ".$sum."<br>";
            //echo "<br>total: ".$total."<br>";

            cargaAtt($root, array(
                "version" => "3.2",
                "serie" => $serie,
                "folio" => $folio,
                "fecha" => str_replace(' ', 'T', str_replace('/', '-', $datos [4])),
                "sello" => "@",
                "tipoDeComprobante" => trim($tipocomprobante),
                "formaDePago" => $datosdos [1],
                "noCertificado" => trim($Certificado),
                "certificado" => "@",
                "subTotal" => $subTotal,
                "descuento" => $descuentofact,
                "total" => $total,  //cambios ant ($totalimporte - $descuentofact), number_format((($totalimporte) + $impuestosinifact - abs($sum)), $decimalplaces, '.', '')
                "metodoDePago" => $datosdos [3],
                "TipoCambio" => $datos [13],
                "Moneda" => $datos [12],
                "LugarExpedicion" => $myrowtags ['municipioexpedido'] . ',' . $myrowtags ['estadoexpedido'],
                "NumCtaPago" => $datosdos [5],
                "FolioFiscalOrig" => $datos [19],
                "FechaFolioFiscalOrig" => str_replace(' ', 'T', str_replace('/', '-', $datos [20])),
                "MontoFolioFiscalOrig" => $datos [21]
            )); //

            $fechaamece = str_replace(' ', 'T', str_replace('/', '-', $datos [4]));
            $cantidadletra = $datos [11];
            $cadenasellar = $cadenasellar . "|3.2|" . trim(ReglasXCadena(str_replace(' ', 'T', str_replace('/', '-', $datos [4]))));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($tipocomprobante));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datosdos [1]));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($subTotal));
            $cadenasellar = $cadenasellar . "|" . trim($descuentofact);
            // $cadenasellar=$cadenasellar."|".trim($Certificado);
            // $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($totalimporte))."|".trim(number_format($datos[8],2))."|".trim(ReglasXCadena(number_format($totalimporte+$impuestosinifact,2,'.','')));
            // $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datosdos[3]));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [13]));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [12]));
            $cadenasellar = $cadenasellar . "|" . ReglasXCadena($total); //cambios ant ($totalimporte - $descuentofact), number_format((($totalimporte) + $impuestosinifact - abs($sum)), $decimalplaces, '.', '')
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datosdos [3]));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['municipioexpedido'] . ',' . $myrowtags ['estadoexpedido']));
            $cadenasellar = $cadenasellar . "|" . ReglasXCadena($datosdos [5]);
            if (strlen(trim($datos [19])) > 0) {
                $cadenasellar = $cadenasellar . "|" . ReglasXCadena($datos [19]);
            }
            if (strlen(trim($datos [20])) > 0) {
                $cadenasellar = $cadenasellar . "|" . str_replace(' ', 'T', str_replace('/', '-', $datos [20]));
            }
            if (strlen(trim($datos [21])) > 0) {
                $cadenasellar = $cadenasellar . "|" . ReglasXCadena($datos [21]);
            }
            //
            // $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[13]));
            // $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[12]));
        } elseif ($cad == 1) {


            $emisor = $xml->createElement("cfdi:Emisor");
            $emisor = $root->appendChild($emisor);
            // cargaAtt($emisor, array("rfc"=>$rfc,"nombre"=>$legalname));
            cargaAtt($emisor, array(
                "rfc" => trim($rfc),
                "nombre" => trim($legalname)
            ));
            $domfis = $xml->createElement("cfdi:DomicilioFiscal"); // $xml->createElement("DomicilioFiscal");
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($rfc)) . "|" . trim(ReglasXCadena($legalname));

            $domfis = $emisor->appendChild($domfis);
            cargaAtt($domfis, array(
                "calle" => (($myrowtags ['calle'])),
                "noExterior" => $myrowtags ['noExterior'],
                "noInterior" => "",
                "colonia" => $myrowtags ['colonia'],
                "referencia" => $legalname,
                "municipio" => $myrowtags ['municipio'],
                "estado" => $myrowtags ['estado'],
                "pais" => "MEXICO",
                "codigoPostal" => $myrowtags ['cp']
            ));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['calle'])); // ."|".trim(ReglasXCadena($myrowtags['noExterior']));

            if (strlen(trim($myrowtags ['noExterior'])) > 0) {
                $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['noExterior']));
            }

            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['colonia'])); // ."|".trim($myrowtags['municipio']);
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($legalname)) . "|" . trim(ReglasXCadena($myrowtags ['municipio']));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['estado'])) . "|MEXICO|" . trim(ReglasXCadena($myrowtags ['cp']));

            $expedido = $xml->createElement("cfdi:ExpedidoEn");

            $expedido = $emisor->appendChild($expedido);
            cargaAtt($expedido, array(
                "calle" => $myrowtags ['calleexpedido'],
                "noExterior" => $myrowtags ['noExteriorexpedido'],
                "noInterior" => "",
                "colonia" => $myrowtags ['coloniaexpedido'],
                "referencia" => $myrowtags ['tagname'],
                "municipio" => $myrowtags ['municipioexpedido'],
                "estado" => $myrowtags ['estadoexpedido'],
                "pais" => $myrowtags ['paisexpedido'],
                "codigoPostal" => $myrowtags ['codigoPostalExpedido']
            ));


            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['calleexpedido'])) . "|" . trim(ReglasXCadena($myrowtags ['noExteriorexpedido']));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['coloniaexpedido'])); // ."|".trim($myrowtags['municipioexpedido']);
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['tagname'])) . "|" . trim(ReglasXCadena($myrowtags ['municipioexpedido']));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['estadoexpedido'])) . "|" . trim(ReglasXCadena($myrowtags ['paisexpedido']));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['codigoPostalExpedido']));
            // regimen fiscal
            $regimenfiscal = $xml->createElement("cfdi:RegimenFiscal");
            $regimenfiscal = $emisor->appendChild($regimenfiscal);
            cargaAtt($regimenfiscal, array(
                "Regimen" => $myrowtags ['regimenfiscal']
            ));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($myrowtags ['regimenfiscal']));

        } elseif ($cad == 2) {


            //$sqldebtor = "SELECT brname,taxid FROM custbranch WHERE debtorno = '" . $_SESSION['CustomerID'] . "'";
            //$sqldebtor = "SELECT brname,taxid FROM custbranch WHERE debtorno = '" . $datos [1] . "'";
            $sqldebtor = "SELECT brname,taxid FROM custbranch
            INNER JOIN debtortrans ON debtortrans.branchcode = custbranch.branchcode AND custbranch.debtorno = debtortrans.debtorno
            WHERE debtortrans.id='".$iddocto."'";
            if($_SESSION['UserID'] == 'desarrollo'){
                echo "<br> consulta datos cliente: ".$sqldebtor.'ID DOCT'.$iddocto;
            }
            $debtrResult = DB_query($sqldebtor, $db);
            $debtrRow = DB_fetch_array($debtrResult);
            $receptor = $xml->createElement("cfdi:Receptor");
            $receptor = $root->appendChild($receptor);
            $datos2 = strtoupper($debtrRow['taxid']);
            cargaAtt($receptor, array(
                "rfc" => trim($datos2),
                "nombre" => trim(utf8_encode($debtrRow['brname']))
            ));
            $rfccliente = strtoupper($datos [2]);
            $rfccliente = trim($rfccliente);
            $debtorno = trim($datos [1]);
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [2])) . "|" . trim(ReglasXCadena($datos [3]));

            $coloniarecep = $datos [8];
            $telrecep = $datos [10];
            $cad = $cad + 1;
            $linea = $arraycadena [$cad];
            $datos = explode('|', $linea);
            // echo '<br>'.ReglasXCadena($coloniarecep).'<br>';
            $domicilio = $xml->createElement("cfdi:Domicilio");
            $domicilio = $receptor->appendChild($domicilio);
            //"colonia" => trim($coloniarecep),
            if ($rfccliente != 'XAXX010101000') {
                cargaAtt($domicilio, array(
                    "calle" => trim($datos [4]),
                    "noExterior" => trim($datos [5]),
                    "noInterior" => trim($datos [6]),
                    "colonia" => trim($datos [7]),
                    "referencia" => trim($telrecep),
                    "localidad" => "",
                    "municipio" => trim($datos [10]),
                    "estado" => trim($datos [11]),
                    "codigoPostal" => trim($datos [12]),
                    "pais" => trim($datos [3])
                ));
                // echo '<br>datos:'.$datos[5].'<br>';
                if (strlen(trim($datos [4])) > 0) {
                    $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [4]));
                }
                if (strlen(trim($datos [5])) > 0) {
                    $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [5]));
                }
                if (strlen(trim($datos [6])) > 0) {
                    $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [6]));
                }
                if (strlen(trim($coloniarecep)) > 0) {
                    $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($coloniarecep));
                }

                /*
                 * // Atributo de localidad del receptor, lo quite arriba y en esta cadena ...
                 * if (strlen(trim(trim($datos[10])))>0){
                 * $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[10]));
                 * }
                 */

                if (strlen(trim($telrecep)) > 0) {
                    $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($telrecep));
                }

                if (strlen(trim(trim($datos [10]))) > 0) {
                    $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [10]));
                }
                if (strlen(trim(trim($datos [11]))) > 0) {
                    $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [11]));
                }
                $cadenasellar = $cadenasellar . "|" . trim($datos [3]);
                if (strlen(trim(trim($datos [12]))) > 0) {
                    $cadenasellar = $cadenasellar . "|" . trim($datos [12]);
                }
            } else {
                cargaAtt($domicilio, array(
                    "calle" => "",
                    "noExterior" => "",
                    "colonia" => "",
                    "referencia" => "",
                    "localidad" => "",
                    "municipio" => "",
                    "estado" => "",
                    "pais" => "MEXICO",
                    "codigoPostal" => ""
                ));

                $cadenasellar = $cadenasellar . "|MEXICO";
            }
        } elseif ($cad >= 4 and $datos [0] == '5') {
            if ($banderaconceptos == false) {
                $conceptos = $xml->createElement("cfdi:Conceptos");
                $conceptos = $root->appendChild($conceptos);

                $banderaconceptos = true;
            }
            $concepto = $xml->createElement("cfdi:Concepto");
            $concepto = $conceptos->appendChild($concepto);
            if ($carpeta == 'Recibo') {
                $importe = $datos [6];
                $unidades = $datos [7];
            } elseif ($carpeta == 'NCargo' or $carpeta == 'NCreditoDirect') {
                $importe = $datos [13];
                $unidades = "No Aplica";
            } else {
                $importe = $datos [5] * $datos [3]; // $datos[13];
                $unidades = $datos [7];
            }
             //echo "unidades".$datos[7].'---'.$datos[3].' Importe ....'.$importe;
            //echo "<br>IE: " . $_SERVER['HTTP_USER_AGENT'];

            if (isset($_SERVER['HTTP_USER_AGENT']) &&
                    ((strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false) || (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') !== false))) {
                $texto = $datos[4];
            } else {
                /*
                  $texto = str_replace(chr(195) . chr(131) . chr(194) . chr(177), utf8_encode("&ntilde;"), $datos[4]); //ñ
                  $texto = str_replace(chr(195) . chr(131) . chr(194) . chr(145), utf8_encode("&Ntilde;"), $texto); //Ñ
                  $texto = str_replace(chr(195) . chr(131) . chr(194) . chr(161), utf8_encode("&aacute;"), $texto); //a
                  $texto = str_replace(chr(195) . chr(131) . chr(194) . chr(169), utf8_encode("&eacute;"), $texto); //e
                  $texto = str_replace(chr(195) . chr(131) . chr(194) . chr(173), utf8_encode("&iacute;"), $texto); //i
                  $texto = str_replace(chr(195) . chr(131) . chr(194) . chr(179), utf8_encode("&oacute;"), $texto); //o
                  $texto = str_replace(chr(195) . chr(131) . chr(194) . chr(186), utf8_encode("&uacute;"), $texto); //u
                  $texto = str_replace(chr(195) . chr(131) . chr(194) . chr(129), utf8_encode("&Aacute;"), $texto); //A
                  $texto = str_replace(chr(195) . chr(131) . chr(194) . chr(137), utf8_encode("&Eacute;"), $texto); //E
                  $texto = str_replace(chr(195) . chr(131) . chr(194) . chr(141), utf8_encode("&Iacute;"), $texto); //I
                  $texto = str_replace(chr(195) . chr(131) . chr(194) . chr(147), utf8_encode("&Oacute;"), $texto); //O
                  $texto = str_replace(chr(195) . chr(131) . chr(194) . chr(154), utf8_encode("&Uacute;"), $texto); //U
                 */
                $texto = $datos[4];
            }
            $descripcion = trim($datos[4]);

            
            if($_SESSION['UsarMedidas']==1){
               if ($datos[16] != "") {
                $descripcion = $descripcion . "  Aduana: " . $datos[16] . ", No. Pedimento: " . $datos[21] . ", Fecha Aduana: " . $datos[20] . "  ";
                } 
            }

            $valorUnitario = $datos [5];

            /*Anticipo*/
            //Si es un anticipo, obtener el importe del anticipo, para que pueda dar valor 0
            /*$flagadvance = 0;
            $sqlAnt = "SELECT flagadvance FROM stockmaster WHERE stockid = '".trim($datos [2])."'";
            $ErrMsg = _('El Sql que fallo fue');
            $DbgMsg = _('No se pudo obtener los datos del articulo');
            //echo "<br>".$sqlAnt."<br>";
            $ResultAnt = DB_query($sqlAnt, $db, $ErrMsg, $DbgMsg, true);
            if (DB_num_rows($ResultAnt) == 1) {
                $myrowAnt = DB_fetch_array($ResultAnt);
                $flagadvance = $myrowAnt['flagadvance'];
            }
            //echo "<br>flagadvance: ".$flagadvance."<br>";

            if ($flagadvance == 1) {
                //Mostrar el importe donde
                $valorUnitario = $datos [6];
                $importe = $datos [6];
            }*/

            $valorUnitario = str_replace(',', '', $valorUnitario);
            $importe = str_replace(',', '', ($importe));
            $cantidad = abs($datos[3]); //Cantidad siempre en positivo
            cargaAtt($concepto, array(
                "cantidad" => trim($cantidad),
                "unidad" => trim($unidades),
                "noIdentificacion" => trim($datos [2]),
                "descripcion" => trim($descripcion),
                "valorUnitario" => trim(number_format($valorUnitario, $decimalplaces, '.', '')),
                "importe" => trim(number_format(($importe), $decimalplaces, '.', ''))
            ));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($cantidad)) . "|" . trim(ReglasXCadena($unidades));
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [2])) . "|" . trim(ReglasXCadena($descripcion));
            // echo $cadenasellar;

            $totalimporte = $totalimporte + $importe;
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena(number_format($valorUnitario , $decimalplaces, '.', ''))) . "|" . trim(ReglasXCadena(number_format(($importe), $decimalplaces, '.', '')));
        } elseif ($cad >= 4 and $datos [0] == '7') {


            if ($banderageneraimpuestos == false) {
                $impuestos = $xml->createElement("cfdi:Impuestos");
                $impuestos = $root->appendChild($impuestos);
                $banderageneraimpuestos = true;
            }

            if ($banderaimpuestos == false ) {
                $traslados = $xml->createElement("cfdi:Traslados");
                $traslados = $impuestos->appendChild($traslados);
                $banderaimpuestos = true;
                // Agregar cuando sea addenda MAPFRE ...
                if ($rfccliente == 'MTE440316E54' || $_SESSION['FlagIva'] == 1) {
                    $traslado = $xml->createElement("cfdi:Traslado");
                    $traslado = $traslados->appendChild($traslado);
                    cargaAtt($traslado, array(
                        "impuesto" => trim($datos [1]),
                        "tasa" => trim($datos [2]),
                        "importe" => trim(number_format($impuestosinifact, $decimalplaces, '.', ''))
                    ));
                    $impuestofact = $impuestosinifact;
                    $cadenasellarimp = $cadenasellarimp . "|" . trim(ReglasXCadena($datos [1])) . "|" . trim(ReglasXCadena($datos [2])) . "|" . trim(ReglasXCadena(number_format($impuestofact, $decimalplaces, '.', '')));
                }
            }

            // No agregar cuando sea addenda MAPFRE ...
            if ($rfccliente != 'MTE440316E54' && $_SESSION['FlagIva'] != 1) {
                $traslado = $xml->createElement("cfdi:Traslado");
                $traslado = $traslados->appendChild($traslado);
                cargaAtt($traslado, array(
                    "impuesto" => trim($datos [1]),
                    "tasa" => trim($datos [2]),
                    "importe" => trim(number_format($datos [3], $decimalplaces, '.', ''))
                ));
                $impuestofact = $impuestofact + trim($datos [3]);
                $cadenasellarimp = $cadenasellarimp . "|" . trim(ReglasXCadena($datos [1])) . "|" . trim(ReglasXCadena($datos [2])) . "|" . trim(ReglasXCadena(number_format($datos [3], $decimalplaces, '.', '')));
            }
        } elseif ($cad >= 4 and $datos [0] == '6') {

            if ($banderageneraimpuestos == false) {
                $impuestos = $xml->createElement("cfdi:Impuestos");
                $impuestos = $root->appendChild($impuestos);
                $banderageneraimpuestos = true;
            }

            if ($banderaimpuestosretenidos == false) {
                $Retenciones = $xml->createElement("cfdi:Retenciones");
                $Retenciones = $impuestos->appendChild($Retenciones);
                $banderaimpuestosretenidos = true;
            }

            $retenido = $xml->createElement("cfdi:Retencion");
            $retenido = $Retenciones->appendChild($retenido);
            cargaAtt($retenido, array(
                "impuesto" => trim($datos [1]),
                "importe" => trim(number_format(abs($datos [3]), $decimalplaces, '.', ''))
            ));

            $impuestoretenido = $impuestoretenido + trim($datos [3]);
            $cadenasellar = $cadenasellar . "|" . trim(ReglasXCadena($datos [1])) . "|" . trim(ReglasXCadena(number_format(abs($datos [3]), $decimalplaces, '.', '')));
        } elseif ($cad >= 4 and $datos [0] == '8') {
            if ($banderacomplemento == false) {
                $complemento = $xml->createElement("cfdi:Complemento");
                cargaAtt($complemento, array(
                    "xmlns:cfdi" => "http://www.sat.gob.mx/cfd/3",
                ));
                $banderacomplemento = true;
                $banderaimpuestoslocales = true;
            }
            

            if ($banderaimploc == false) {
                $impLocal = $xml->createElement("implocal:ImpuestosLocales");
                $banderaimploc = true;
            }
            /*
             * cargaAtt ( $impLocal, array (
             * "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
             * "xmlns:implocal" => "http://www.sat.gob.mx/implocal",
             * "xsi:schemaLocation" => "http://www.sat.gob.mx/implocal http://www.sat.gob.mx/sitio_internet/cfd/implocal/implocal.xsd"
             * ) );
             */
            $impLocal = $complemento->appendChild($impLocal);

            $ind = 1;

            //
            //
            //while ( $ind < count ( $datos ) ) {
            $desc = $datos [1];
            $porc = $datos [2];
            $cant = $datos [3];

            if ($cant < 0) {
                $tRet += abs($cant);

                $retLocales = $xml->createElement("implocal:RetencionesLocales");
                $retLocales = $impLocal->appendChild($retLocales);

                cargaAtt($retLocales, array(
                    "ImpLocRetenido" => $desc,
                    "TasadeRetencion" => $porc,
                    "Importe" => str_replace(",", "", number_format(abs($cant), 2))
                ));
            } elseif ($cant != '' and $cant >= 0) {
                $tTras += $cant;

                $trasLocales = $xml->createElement("implocal:TrasladosLocales");
                $trasLocales = $impLocal->appendChild($trasLocales);

                cargaAtt($trasLocales, array(
                    "ImpLocTrasladado" => $desc,
                    "TasadeTraslado" => $porc,
                    "Importe" => str_replace(",", "", number_format($cant, 2))
                ));
            }
            if ($desc != '' AND $porc != '' AND $cant != '') {
                $cadimp .= "|" . $desc . "|" . $porc . "|" . str_replace(",", "", number_format(abs($cant), 2));
            }


            /* if($_SESSION['UserID']=='desarrollo'){
              echo '<br>-->aqui esta seccion--> '.$cadimp.'<br>';
              } */

            $ind += 3;
            //}
        }
        // fin de if de cad
    }



    //$cadenasellar.=$cadimp;
    // impuestos retenidos federales
    if ($banderaimpuestosretenidos == true) {
        $impuestos->SetAttribute("totalImpuestosRetenidos", number_format(abs($impuestoretenido), $decimalplaces, '.', ''));
    }

    if ($banderaimpuestosretenidos == false) {
        $cadenasellar = $cadenasellar; // ."||";
    } else {
        $cadenasellar = $cadenasellar . "|" . ReglasXCadena(number_format(abs($impuestoretenido), $decimalplaces, '.', '')); // ."||";
    }
    $sqlservice = "SELECT type 
                        FROM debtortrans
                        WHERE order_ = " . $orderno;
    $resultservice = DB_query($sqlservice, $db);
    $rowservice = DB_fetch_array($resultservice);
    $typeservice = $rowservice ['type'];
    if ($typeservice == 119) {
        if ($_SESSION ['DatabaseName'] == "erpmservice" or $_SESSION ['DatabaseName'] == "erpmservice_CAPA" or $_SESSION ['DatabaseName'] == "erpmservice_DES" or $_SESSION ['DatabaseName'] == "erpmservice_DIST") {
            $banderaimpuestos == false;
        }
    }

    if ($banderaimpuestos == true) {
        $impuestofact = number_format($impuestofact, $decimalplaces, '.', ''); //cambios ant

        if ( ($impuestofact < 0.01 and $impuestofact > 0) or $impuestofact <= 0){
            $impuestofact = number_format(0, $decimalplaces, '.', '');
        }

        $impuestos->SetAttribute("totalImpuestosTrasladados", $impuestofact);  //cambios ant number_format($impuestofact, $decimalplaces, '.', '')
    } else {
        $impuestos = $xml->createElement("cfdi:Impuestos");
        $impuestos = $root->appendChild($impuestos);
    }

    
    
    //  for ($cad = 0; $cad <= count($arraycadena) - 1; $cad ++) {
    $cadenaINE = "";
    if ($cad >= 4 and $datos [0] == '10') {
        /*        
        ini_set('display_errors', 1);
        ini_set('log_errors', 1);
        ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
        error_reporting(E_ALL);
        */  
        /*
        if($datos [1]==1){
            $TipoProceso = 'Ordinario';
        }elseif($datos [1]==2){
            $TipoProceso = utf8_decode('Precampaña');
        }elseif($datos [1]==3){
            $TipoProceso = utf8_decode('Campaña') ;
        }*/
            
        $TipoProceso = utf8_decode($datos [1]);
        $TipoProceso =  str_replace(chr(63),chr(241), $TipoProceso);
        $tipocomite = $datos [2];
        /*
        if($datos [6]==1){
            $ambito = "Federal";
        }elseif($datos [6]==2){
            $ambito = "Local";
        }elseif($datos [6]==3){
            $ambito = utf8_decode('Campaña') ;
        }*/
         
        $ambito = $datos [6];
        
        $complemento = $xml->createElement("cfdi:Complemento");
        $complemento = $root->appendChild($complemento);
        $ine = $xml->createElement("ine:INE");
        $ine = $complemento->appendChild($ine);
        cargaAtt($ine, array(
            "xmlns:ine" => "http://www.sat.gob.mx/ine",
            "Version" => "1.1",
            "TipoProceso" =>$TipoProceso,
            "TipoComite" => $tipocomite
        ));
        $complementoINEentidad = $xml->createElement("ine:Entidad");
        $complementoINEentidad = $ine->appendChild($complementoINEentidad);
        cargaAtt($complementoINEentidad, array(
            "ClaveEntidad" => $datos [3],
            "Ambito"=>$ambito
        ));
        /*
        $complementoINEentidadConta = $xml->createElement("ine:Contabilidad");
        $complementoINEentidadConta = $complementoINEentidad->appendChild($complementoINEentidadConta);
        cargaAtt($complementoINEentidadConta, array(
            "IdContabilidad" => $datos [5]
            
        ));
        */
        if ($ambito != ""){
            $ambito = "|" . $ambito;
        }else{
            $ambito = "";
        }
        
        if($datos [4] != ""){
            $complementoINEentidadConta = $xml->createElement("ine:Contabilidad");
            $complementoINEentidadConta = $complementoINEentidad->appendChild($complementoINEentidadConta);
            cargaAtt($complementoINEentidadConta, array(
                "IdContabilidad" => $datos [4]
                
            ));
            $cadenaINE.= '|1.1|' . ($TipoProceso) . '|' . $tipocomite . '|' . ($datos [3]) . ($ambito) . '|' . ($datos [4]);
        }else{
            $cadenaINE.= '|1.1|' . ($TipoProceso) . '|' . $tipocomite . '|' . ($datos [3]) . ($ambito);
        }
        
        if ($_SESSION ['UserID'] == 'saplicaciones' ) {
            //echo "<br>1:" . $cadenaINE;
        }
    }
        
    if ($banderaimpuestos == false) {
        $cadenasellar = $cadenasellar; // ."||";
    } else {
        $cadenasellar = $cadenasellar . $cadenasellarimp . "|" . ReglasXCadena(number_format($impuestofact, $decimalplaces, '.', '')); // ."||";
    }
    // $banderaimpuestoslocales = true;
    if ($banderaimpuestoslocales == true) {

        cargaAtt($impLocal, array(
            "version" => "1.0",
            "TotaldeRetenciones" => number_format(abs($tRet), $decimalplaces, '.', ''),
            "TotaldeTraslados" => number_format(abs($tTras), $decimalplaces, '.', '')
        ));

        $cadimp = utf8_encode("|1.0|" . number_format(abs($tRet), $decimalplaces, '.', '') . "|" . number_format(abs($tTras), $decimalplaces, '.', '') . trim($cadimp));


        $complemento = $root->appendChild($complemento);
    }

    // echo 'addenda pemex:'.htmlentities($addenda);


    $cadenaINE = mb_convert_encoding($cadenaINE, "UTF-8", "ISO-8859-1");
    $cadenasellar = '|' . $cadenasellar . $cadimp . $cadenaINE. "||";
    // agregado porque la cdena original debe ser codificada en utf8 segun anexo 20 del sat**
    $cadenasellarx = $cadenasellar;
    // $cadenasellar = (DB_escape_string($cadenasellar));
    // echo '<br><br>'.$cadenasellar.'<br><br>';
    // inicializa y termina la cadena original con el doble ||
    if ($_SESSION ['DatabaseName'] == 'erpmservice' or $_SESSION ['DatabaseName'] == 'erpmservice_CAPA' or $_SESSION ['DatabaseName'] == 'erpmservice_DES') {
        $aprobaxfolio = TraeAprobacionxFolio($rfccliente, $serie, $folio, $db);
        $aprobacionfolios = explode('|', $aprobaxfolio);
        $certificado = $aprobacionfolios [0];
        $Noaprobacion = $aprobacionfolios [1];
        $anioAprobacion = $aprobacionfolios [2];
    } else {
        $certificado = $myrowtags ['FileSAT'];
    }
    
    $crypttext="";
    $maquina = trim(`uname -n`);
    //$ruta = "/var/www/html" . dirname($_SERVER ['PHP_SELF']) . "/companies/" . $_SESSION ['DatabaseName'] . "/SAT/" . str_replace('.', '', str_replace(' ', '', $legalname)) . "/";
    $arrpath = explode('/', dirname($_SERVER ['PHP_SELF']));
    $rootdirectory = $arrpath[1];
    $ruta = "/var/www/html" . "/" . $rootdirectory . "/companies/" . $_SESSION ['DatabaseName'] . "/SAT/" . str_replace(',', '', str_replace('.', '', str_replace(' ', '', $legalname))) . "/";

    $file = $ruta . $certificado . ".key.pem"; // Ruta al archivo


    if (file_exists($file)) {
        $pkeyid = "";
        $pkeyid = openssl_get_privatekey(file_get_contents($file));
        openssl_sign($cadenasellar, $crypttext, $pkeyid, OPENSSL_ALGO_SHA1);
        openssl_free_key($pkeyid);
    }else{
        $pkeyid = "";
    }
    
    $sello = base64_encode($crypttext); // lo codifica en formato base64

    $root->setAttribute("sello", $sello);
    
    $file = $ruta . $certificado . ".cer.pem"; // Ruta al archivo
    $file = utf8_encode($file);
    $certificado = "";
    $carga = false;
        
    if (file_exists($file)) {
        $datos = file($file);
        
        for ($i = 0; $i < sizeof($datos); $i ++) {
            if (strstr($datos [$i], "END CERTIFICATE")){
                $carga = false;
            }
            if ($carga){
                $certificado .= trim($datos [$i]);
            }
            if (strstr($datos [$i], "BEGIN CERTIFICATE")){
                $carga = true;
            }
        }
    }
    
    $root->setAttribute("certificado", $certificado);
    $xml->formatOutput = true;
    $todo = $xml->saveXML();

    $dir = "/var/www/html/" . dirname($_SERVER ['PHP_SELF']) . "/companies/" . $_SESSION ['DatabaseName'] . "/SAT/" . str_replace('.', '', str_replace(' ', '', $legalname)) . "/XML/" . $carpeta . "/";
    // echo "dir: " . $dir . "<br/>";
    if ($dir != "/dev/null") {
        // $xml->formatOutput = true;
        // $xml->save($dir.$nufa.".xml");
    } else {
        $paso = $todo;
        $conn->replace("cfdsello", array(
            "selldocu" => $nufa,
            "sellcade" => $cadena_original,
            "sellxml" => $paso
                ), "selldocu", true);
    }
    // guardamos la cadena y sello en la base de datos
    $sql = "UPDATE debtortrans
            SET sello='" . $sello . "',
                cadena='" . utf8_decode( addslashes($cadenasellar) ) . "'
            WHERE id=" . $iddocto;
    //echo "<br>function generaXMLCFDI final: ".$cadenasellar." <br>";
    $ErrMsg = _('El Sql que fallo fue');
    $DbgMsg = _('No se pudo actualizar el sello y cadena del documento');
    $Result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
    //echo '<pre>XML '.__FILE__.'---'.__LINE__;
    if ($_SESSION ['UserID'] == 'saplicaciones' || $_SESSION ['UserID'] == 'desarrollo' ) {
        echo "<br>generaXMLCFDI:" .  htmlentities($todo)."<br>";
    }
    $addendaXML = "";
    $array ["xml"] = "$todo";
    $array ["cadenaOriginal"] = "$cadenasellar";
    $array ["cantidadLetra"] = $cantidadletra;
    $array ["xmladdenda"] = "$addendaXML";
    // return($todo);
    return $array;
}

function generaXMLCFDI3_3($cadena_original, $tipocomprobante, $tagref, $serie, $folio, $iddocto, $carpeta, $orderno = 0, $db) {
    // *********************************************** //
    // ********************************************** //
    // ******** No dejar echo en la funcion *********//
    // ******** Declarar Variables a usar ********  //
    // ******** Afecta al punto de venta ********* //
    // ****************************************** //
    // ***************************************** //
    
    // SE AGREGA $_SESSION['FlagI22va'] = 1 PARA QUE CALCULE SUMA TOTAL DE IVA
    $cadena_original = str_replace('&quot;', '"', $cadena_original);
        

    if ($_SESSION['UserID'] == 'aenriquez' || $_SESSION['UserID'] == 'desarrollo' ){
        echo 'SendInvoicingV6_0.php: <br> '.$cadena_original."<br>";
    }

    global $xml, $cadena, $conn, $sello, $cadenasellar, $totalimporte;
    $banderaimpuestos = false;
    $banderaconceptos = false;
    $banderacomplemento = false;
    $banderacomercioexterior=false;
    $banderaimpuestoslocales = false;
    $cadena = str_replace(chr(13) . chr(10) . '0', '@%', $cadena_original);
    $stockidPrducto = "";

    //error_reporting ( E_ALL );
    $tipocomprobante = strtolower($tipocomprobante);
    $noatt = array();
    $arraycadena = array();
    $nufa = $serie . $folio; // Junta el numero de factura serie + folio
    $impuestofact = 0;
    $cadenasellar = "";
    $xml = new DOMdocument('1.0', 'UTF-8');
    //$xml = new DOMdocument ();
    $decimalplaces = 6;

    if ($_SESSION ['DecimalPlacesInvoice'] == '') {
        $_SESSION ['DecimalPlacesInvoice'] = 6;
    }
    if(isset($_SESSION ['DecimalPlacesInvoice'])){
        $decimalplaces = $_SESSION ['DecimalPlacesInvoice'];    
    }
    $decimalesFac = 0;
    if($_SESSION['DatabaseName'] == 'erpmatelpuente' OR $_SESSION['DatabaseName'] == 'erpmatelpuente_CAPA' OR $_SESSION['DatabaseName'] == 'erpmatelpuente_DES'){
        $decimalesFac = 6;
    }else{
        $decimalesFac = 2;
    }

    //Obtener datos CFDI 3.3
    //$TipoDeComprobante = "";
    $FormaPago = "";
    $MetodoPago = "";
    $CondicionesDePago = "";
    $claveFactura = "";
    $UsoCFDI = "";
    $RegimenFiscal = "";
    $Cliente_ResidenciaFiscal = "";

    //tipo Documento
    $tipoDoc="";

    //Comercio exterior 
    $municipio="";
    $estado="";
    $colonia="";
    $pais="";

    $SQL = "SELECT 
            debtortrans.type,
            debtortrans.c_TipoDeComprobante,
            debtortrans.c_UsoCFDI,
            debtortrans.c_paymentid,
            debtortrans.claveFactura,
            currencies.decimalplaces,
            sat_paises.c_Pais,
            /*sat_paises.local,*/
            custbranch.taxid as rfc
            FROM debtortrans
            LEFT JOIN currencies ON currencies.currabrev = debtortrans.currcode
            LEFT JOIN custbranch ON custbranch.branchcode = debtortrans.branchcode AND custbranch.debtorno = debtortrans.debtorno
            LEFT JOIN sat_paises ON sat_paises.descripcion = custbranch.custpais
            WHERE debtortrans.id = '".$iddocto."'";
    $ErrMsg = _('El Sql que fallo fue');
    $DbgMsg = _('No se pudo obtener: Tipo de Comprobante, Uso CFDI, Metodo de Pago');
    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
    if (DB_num_rows($result) > 0) {
        $row = DB_fetch_array($result);
        $tipoDoc = trim($row ['type']);
        $tipocomprobante = trim($row ['c_TipoDeComprobante']);
        $UsoCFDI = trim($row ['c_UsoCFDI']);

        $MetodoPago = trim($row ['c_paymentid']);
        $claveFactura = trim($row ['claveFactura']);
        $decimalplaces = trim($row ['decimalplaces']);                   
        if($row['rfc'] == "XEXX010101000") // condicion tabla paises solo si es extrangero
        {
            $Cliente_ResidenciaFiscal = trim($row ['c_Pais']);
        }
        
    }

    $decimalplaces = $_SESSION ['DecimalPlacesInvoice']; 

    if ($_SESSION['UserID'] == "desarrollo") {
        // echo "<br>SQL: ".$SQL;
        // echo "<br>decimalplaces: ".$decimalplaces;
    }
    
    
    $root = $xml->createElement("cfdi:Comprobante");
    $root = $xml->appendChild($root);

    if($tipoDoc == 200 OR $tipoDoc == 12){

        cargaAtt($root, array(
            "xmlns:cfdi" => "http://www.sat.gob.mx/cfd/3",
            "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
            "xmlns:pago10" => "http://www.sat.gob.mx/Pagos",
            "xsi:schemaLocation" => "http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd http://www.sat.gob.mx/Pagos http://www.sat.gob.mx/sitio_internet/cfd/Pagos/Pagos10.xsd",
        ));
    }else{
        cargaAtt($root, array(
            "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
            "xmlns:cfdi" => "http://www.sat.gob.mx/cfd/3",
            //"xmlns:ecfd" => "http://www.southconsulting.com/schemas/strict",
            "xsi:schemaLocation" => "http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd",
            // "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
            //"xmlns:implocal" => "http://www.sat.gob.mx/implocal",
            // "xsi:schemaLocation" => "http://www.sat.gob.mx/implocal http://www.sat.gob.mx/sitio_internet/cfd/implocal/implocal.xsd" 
        ));
    }

        /*$sql = 'SELECT folio, type, uuid
            FROM log_complemento_sustitucion 
            WHERE sustitucion_from = '.$iddocto;
        $query = DB_query($sql, $db);

        if($_SESSION ['UserID'] == 'desarrollo' ) {
        echo '<br>'.$sql;
        }
        if(DB_num_rows($query)>0){
            $row = DB_fetch_array($query);
            if($row['type'] == '12' or $row['type'] == '200'){
                    
                $uuidrelacionados = $xml->createElement("cfdi:CfdiRelacionados");
                $uuidrelacionados = $root->appendChild($uuidrelacionados);

                $uuidrelacionado = $xml->createElement("cfdi:CfdiRelacionado");
                $uuidrelacionado = $uuidrelacionados->appendChild($uuidrelacionado);

                cargaAtt($uuidrelacionado, array(
                    "UUID" => trim($row['uuid'])
                ));

                $uuidrelacionados->SetAttribute("TipoRelacion", '04');
            }
        }*/


    $SQL = " SELECT l.taxid,l.address5,t.tagname,t.areacode,l.legalid,l.legalname as empresa,a.areadescription as legalname,
            l.address1 as calle,
            l.address2 as colonia,
            l.address3 as localidad,
            l.address3 as municipio,
            l.address4 as estado,
            l.address5 as cp,
            t.address1 as calleexpedido,
            t.address2 as noExteriorexpedido,
            t.address3 as coloniaexpedido,
            t.address4 as localidadexpedido,
            t.address4 as municipioexpedido,
            t.address5 as estadoexpedido,
            t.cp as codigoPostalExpedido,
            t.address6 as paisexpedido,
            a.Anioaprobacion,
            a.Noaprobacion,
            a.Nocertificado,
            l.FileSAT,
            l.regimenfiscal,
            '' as noExterior,
            l.c_RegimenFiscal
            FROM areas a, tags t, legalbusinessunit l
            WHERE a.areacode=t.areacode
            and l.legalid=t.legalid
            AND tagref='" . $tagref . "'";
    $ErrMsg = _('El Sql que fallo fue');
    $DbgMsg = _('No se pudo obtener los datos de la unidad de negocio');
    // echo $SQL;
    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
    if (DB_num_rows($Result) == 1) {
        $myrowtags = DB_fetch_array($Result);
        $rfc = trim($myrowtags ['taxid']);
        $rfc = strtoupper($rfc);
        $keyfact = $myrowtags ['address5'];
        $nombre = $myrowtags ['tagname'];
        $area = $myrowtags ['areacode'];
        $legaid = $myrowtags ['legalid'];
        $legalname = $myrowtags ['empresa'];
        $RegimenFiscal = $myrowtags ['c_RegimenFiscal'];
        $calle=$myrowtags ['calleexpedido'];
        $numeroExterior=$myrowtags ['noExteriorexpedido'];
        $codigoPostal=$myrowtags ['codigoPostalExpedido'];

        
        //Comercio exterior  validacion - pisumma
        if($_SESSION['DatabaseName'] == 'erppisumma_DES' || $_SESSION['DatabaseName'] == 'erppisumma' || $_SESSION['DatabaseName'] == 'erppisumma_CAPA'){
            $sqlCP="
                SELECT cp.c_municipio AS municipiosat, cp.c_estado AS estadosat, col.c_colonia AS coloniasat
                FROM codigopostal_sat AS cp
                left join colonia_sat AS col ON col.c_cp=cp.c_cp
                WHERE cp.c_cp='".$codigoPostal."'";
            $Resultcp = DB_query($sqlCP, $db, $ErrMsg, $DbgMsg, true);
            if (DB_num_rows($Resultcp) == 1) {
                $myrowcp = DB_fetch_array($Resultcp);
                $municipio=$myrowcp ['municipiosat'];
                $estado=$myrowcp ['estadosat'];
                $colonia=$myrowcp ['coloniasat'];
                $pais='MEX';
            }
        }
    }

    $arraycadena = explode('@%', $cadena);
    
    // lee primero el arreglo y pone el monto de los productos
    $impuestosinifact = 0;
    $totalimporte = 0;
    $totalDescuento = 0;
    $flagAnticipo = 0; //Si es anticipo el articulo
    $TotalImpuestosLocales = 0;
    $NCIva =0;
    $descuentofact = 0;
    $descuentofactFin = 0;
    $totalFin=0;

    for ($cad = 0; $cad <= count($arraycadena) - 1; $cad ++) {
        $linea = $arraycadena [$cad];
        $datos = explode('|', $linea);
        if ($cad >= 4 and ( $datos [0] == '7' /* or $datos [0] == '6' */)) {
            $NCIva = trim($datos [2]);
            //echo "<br>iva".$NCIva."<br>";
            //var_dump($linea);
        }
    }
    
    for ($cad = 0; $cad <= count($arraycadena) - 1; $cad ++) {
        $linea = $arraycadena [$cad];
        $datos = explode('|', $linea);
        //echo "<br>cad:".$cad ." | datos:". $datos [0];

        if ($cad == 0 and $datos [0] == '1') {
            $totalDescuento = $datos [9]; //Total descuento cambios ant
            //echo "<br>totalDescuento: ".$totalDescuento."<br>";
            // if ($carpeta == 'Recibo') {
            //     $tipocomprobante = $datos [22];
            //     $UsoCFDI = $datos [23];
            //     $MetodoPago = $datos [24];
            //     $claveFactura = $datos [25];
            //     $decimalplaces = $datos [26];
            // }else{
            //     $tipocomprobante = $datos [20];
            //     $UsoCFDI = $datos [21];
            //     $MetodoPago = $datos [22];
            //     $claveFactura = $datos [23];
            //     $decimalplaces = $datos [24];
            // }
        }elseif ($cad >= 4 and $datos [0] == '5') {
            $descuentoRow=0;
            if ($carpeta == 'Recibo') {
                $importe = $datos [6];
                $unidades = $datos [7];
                $descuentoRow=0;
            } elseif ($carpeta == 'NCargo' or $carpeta == 'NCreditoDirect') {
                $importe = $datos [13];
                $unidades = "unidades";
                $descuentoRow=0;
            } else {
                //$totalDescuento = $datos[13]; //Total descuento cambios ant
                //$importe = ($datos [5] * $datos [3]);// - $totalDescuento; // $datos[13]; 

                //**********
                $importe =  trim($datos [5]) * trim($datos [3]);// (trim(number_format(($datos [5]), $decimalplaces, '.', '')) * trim(number_format(($datos [3]), $decimalplaces, '.', '')));
                //$importe = (trim(number_format(($datos [5]), $decimalplaces, '.', '')) * trim(number_format(($datos [3]), $decimalplaces, '.', ''))); 
                //**********


                // echo '<br>importe envia:'.$importe;
                $unidades = $datos [7];
                $descuentoRow=$datos [13];
                $descuentofact = $descuentofact + trim(number_format(($datos [13]), $decimalplaces, '.', ''));
                //$descuentofactFin += $datos [13];
                //$descuentofactFin += trim(number_format(($datos [13]), 2, '.', ''));
                $descuentofactFin += trim(number_format(($datos [13]), $decimalesFac, '.', ''));//matel
                //echo "<br>".$descuentofactFin." DESC ".trim(number_format(($datos [13]), 2, '.', ''));
            }

            /*Anticipo*/
            //Si es un anticipo
            $sqlAnt = "SELECT flagadvance FROM stockmaster WHERE stockid = '".trim($datos [2])."'";
            $ErrMsg = _('El Sql que fallo fue');
            $DbgMsg = _('No se pudo obtener los datos del articulo');
            //echo "<br>".$sqlAnt."<br>";
            $ResultAnt = DB_query($sqlAnt, $db, $ErrMsg, $DbgMsg, true);
            if ($myrowAnt = DB_fetch_array($ResultAnt)) {
                if ($myrowAnt['flagadvance'] == 1) {
                    $flagAnticipo = $myrowAnt['flagadvance'];
                }
            }
            //echo "<br>importe tot: ".$importe."<br>";
            $importe = str_replace(',', '', $importe);
            $totalimporte = $totalimporte + $importe;
            //$totalimporte = $totalimporte + number_format($importe, $decimalplaces, '.', '');

            if ($carpeta == 'NCreditoDirect' or $carpeta=='NCredito') {
                //echo "<br>- importe".$importe." desc".trim(number_format((2), $decimalplaces, '.', ''));

                //$importe = $importe - trim(number_format(($descuentoRow), 2, '.', ''));
                //$importe = $importe - trim($descuentoRow);

                //Obtener datos de impuestos del producto
                $Impuesto = "002";
                $TipoFactor = "Tasa";
                if($tipoDoc==200){
                    $TasaOCuota = '0.000000';
                    $ImporteTax = 0;
                }else{
                    $TasaOCuota = ($NCIva/100);
                    $ImporteTax = $importe * $TasaOCuota;
                }
                //echo "<br>".$TasaOCuota. " - ".$NCIva;


                $Impuesto = "";
                $TipoFactor = "";
                $TasaOCuota = "";
                $ImporteTax = 0;

                $sqlImp = "SELECT 
                        taxauthorities.c_Impuesto,
                        taxauthrates.taxrate,
                        sat_tasas.c_TipoFactor
                        FROM stockmaster
                        LEFT JOIN taxauthrates ON taxauthrates.taxcatid = stockmaster.taxcatid
                        LEFT JOIN taxauthorities ON taxauthorities.taxid = taxauthrates.taxauthority
                        LEFT JOIN taxcategories ON taxcategories.taxcatid = stockmaster.taxcatid
                        LEFT JOIN sat_tasas ON sat_tasas.id = taxcategories.id_Tasa
                        WHERE stockmaster.stockid = '".trim($datos [2])."'";
                $ErrMsg = _('El Sql que fallo fue');
                $DbgMsg = _('No se pudo obtener el impuesto del articulo '.trim($datos [2]));
                //echo "<br>IMPUESTOS: ".$sqlImp."<br>";
                $resultImp = DB_query($sqlImp, $db, $ErrMsg, $DbgMsg, true);

                //$importe = $importe - trim(number_format(($descuentoRow), $decimalplaces, '.', ''));
                $importe = $importe - trim(number_format(($descuentoRow), $decimalesFac, '.', ''));//matel

                if (DB_num_rows($resultImp) == 1) {
                    $rowImp = DB_fetch_array($resultImp);
                    $Impuesto = $rowImp['c_Impuesto'];
                    $TipoFactor = $rowImp['c_TipoFactor'];
                    $TasaOCuota = $rowImp['taxrate'];
                    //var_dump($rowImp);
                    //echo "<br>1_".$TasaOCuota;
                    if($TasaOCuota == 0) {
                        $TasaOCuota = '0.000000';
                    }
                    //echo "<br>2_".$TasaOCuota;
                    $ImporteTax = $importe * $rowImp['taxrate'];

                }
                
                $impuestosinifact = $impuestosinifact + number_format(trim($ImporteTax), $decimalplaces, '.', '');
                //$impuestosinifact = $impuestosinifact + trim($ImporteTax);
                //echo "<br>- impuesto2".trim($ImporteTax)." importe".$importe;
                
                if ($_SESSION['UserID'] == 'admin') {
                    //echo "<br>".trim(number_format(($importe), $decimalplaces, '.', ''))." ".trim(number_format(($TasaOCuota), '6', '.', ''))." ".trim(number_format(($ImporteTax), $decimalplaces, '.', ''));
                    //echo "<br>".$ImporteTax;
                }
            }else if ($carpeta == 'Facturas') {
                //Obtener datos de impuestos del producto
                $Impuesto = "";
                $TipoFactor = "";
                $TasaOCuota = "";
                $ImporteTax = 0;
                $banderageneraimpuestos = false;
                $sqlImp = "SELECT 
                        taxauthorities.c_Impuesto,
                        taxauthrates.taxrate,
                        sat_tasas.c_TipoFactor, taxcatname
                        FROM stockmaster
                        LEFT JOIN taxauthrates ON taxauthrates.taxcatid = stockmaster.taxcatid
                        LEFT JOIN taxauthorities ON taxauthorities.taxid = taxauthrates.taxauthority
                        LEFT JOIN taxcategories ON taxcategories.taxcatid = stockmaster.taxcatid
                        LEFT JOIN sat_tasas ON sat_tasas.id = taxcategories.id_Tasa
                        WHERE stockmaster.stockid = '".trim($datos [2])."'";
                $ErrMsg = _('El Sql que fallo fue');
                $DbgMsg = _('No se pudo obtener el impuesto del articulo '.trim($datos [2]));
                //echo "<br> IMUESTOS: ".$sqlImp."<br>";
                $resultImp = DB_query($sqlImp, $db, $ErrMsg, $DbgMsg, true);

                $importe = $importe - trim(number_format(($descuentoRow), $decimalplaces, '.', ''));

                if (DB_num_rows($resultImp) == 1) {
                    $rowImp = DB_fetch_array($resultImp);
                    if(strtoupper( $rowImp['taxcatname'] )!= 'EXENTO'){
                        $SinExcentos = 0;
                    }
                    $Impuesto = $rowImp['c_Impuesto'];
                    $TipoFactor = $rowImp['c_TipoFactor'];
                    $TasaOCuota = $rowImp['taxrate'];
                    if($TasaOCuota == 0) {
                        $TasaOCuota = '0.000000';
                    }
                    $ImporteTax = $importe * $rowImp['taxrate'];

                }
                
                //$impuestosinifact = $impuestosinifact + trim(number_format(($ImporteTax), $decimalplaces, '.', '')); ANTERIOR
                $impuestosinifact = $impuestosinifact +trim($ImporteTax);
               
                if ($_SESSION['UserID'] == 'admin') {
                    //echo "<br>ImporteTax: ".$ImporteTax;
                    //echo "<br>ImporteTax: format: ".trim(number_format(($ImporteTax), $decimalplaces, '.', ''));
                }

            }
        } elseif ($cad >= 4 and $datos [0] == '6') {
            //se agrego para las retenciones Factura
            //if($_SESSION['UserID'] == "desarrollo"){echo 'ingreso';}
            
            //$impuestosinifact = $impuestosinifact + trim(number_format(($datos[3]), $decimalplaces, '.', ''));//ANTERIOR
            $impuestosinifact = $impuestosinifact + trim(number_format(($datos[3]), $decimalesFac, '.', ''));//matel
            //$impuestosinifact = $impuestosinifact + trim($datos[3]);

            // $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[1]))."|".trim(ReglasXCadena($datos[2]))."|".trim(ReglasXCadena($datos[3]));
        }elseif ($cad >= 4 and ( $datos [0] == '7' /* or $datos [0] == '6' */)) {
            $NCIva = trim($datos [2]);
            //$impuestosinifact = $impuestosinifact + trim($datos [3]);
            if ($carpeta != 'Facturas' && $carpeta != 'NCreditoDirect' && $carpeta != 'NCredito') {
                //$impuestosinifact = $impuestosinifact + trim(number_format(($datos[3]), $decimalplaces, '.', ''));// ANTERIOR
                $impuestosinifact = $impuestosinifact + trim($datos[3]);
            }

            // $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[1]))."|".trim(ReglasXCadena($datos[2]))."|".trim(ReglasXCadena($datos[3]));
        } elseif ($cad >= 4 and $datos [0] == '8') {
            $cant = $datos [3];
            if ($cant < 0) {
                $TotalImpuestosLocales += abs($cant);
            } elseif ($cant != '' and $cant >= 0) {
                $TotalImpuestosLocales += $cant;
            }
            
        } // fin de if de cad
    }
    
    /*if ($flagAnticipo == 1) {
        //Si viene anticipo restar descuento
        $totalimporte = number_format($totalimporte - $totalDescuento, $decimalplaces, '.', '');    
    }else{
        $totalimporte = number_format($totalimporte, $decimalplaces, '.', '');
    }*/

    //$totalimporte = number_format($totalimporte, $decimalplaces, '.', '');

    if ($_SESSION['UserID'] == 'desarrollo' or $_SESSION['UserID'] == 'admin') { //Mostrar datos
        //echo "<br>totalimporte: ".$totalimporte."<br>";
        //echo "<br>totalimporte formato: ".number_format($totalimporte, 2)."<br>";
    }
    //Cadena de imp locales
    $cadimp = "";
    $tRet = 0;
    $tTras = 0;
    $descuentofactAM = 0;
    $sum = 0;
    
    if (!empty($_SESSION['Flag_Amortizacion'])){
    if ($_SESSION['Flag_Amortizacion'] == 1) {

        for ($cadnew = 5; $cadnew <= count($arraycadena) - 1; $cadnew++) {
            $renglon = $arraycadena[$cadnew];
            $reng = explode('|', $renglon);
            if ($reng[0] == '8') {
                $sum = $sum + $reng[3];
            }
        }

        for ($cadnew = 4; $cadnew <= count($arraycadena) - 1; $cadnew++) {
            $renglon = $arraycadena[$cadnew];
            $reng = explode('|', $renglon);

            if ($reng[0] == '5') {
                if ($reng[2] != 'AM') {
                    $subTotal = $subTotal + $reng[6];
                } elseif ($reng[2] == 'AM') {
                    $descuento = $descuento + abs($reng[6]);
                }
                $totalimporte = $subTotal;
                $descuentofactAM = $descuento;
            }
        }
    }}

    
    $cadenasellarimp = "";
    $banderageneraimpuestos= false;
    $banderaimpuestosretenidos = false;
    $banderacomplemento = false;
    $banderaUUIDRelacionados = false;
    $banderaUUIDRelacionadosNC = false;

    // ****** Retenciones Concepto
    $impuestosConcepto = ""; // Nodo Concepto
    $retencionesConcepto = ""; // Nodo Retenciones
    $impuestoretenido=0;
    $SinExcentos = 1;
    // ****** Retenciones Concepto
    
    //******* Impuestos Agrupados
    $impuestosAgrupadosTrasladados = array();
    $traslados = "";
    $impuestosAgrupadosRetenciones = array();
    $Retenciones = "";
    //******* Impuestos Agrupados

    //******* Comercio exterior
    $ComercioExterior = "";  
    $emisorcce = "";
    $receptorcce = "";
    $mercancias = "";
    $crearcomercioexterior=false;
    $fraccionarancelaria="";
    $paisreceptor="";
    $monedace="";
    $cantidadAduana=1;
    //*******  Comercio exterior

    //***** Complemento de servicios parciales
    $vrcomplementoservices="";
    $totalimporte= 0;
    //****

    //echo "<br>array count :". count($arraycadena);// 
    for ($cad = 0; $cad <= count($arraycadena) - 1; $cad ++) {
        $decimalplaces= 2;
        //$ok = $tRetok;
        //  echo '->'.$tRet.'<br>';

        $linea = $arraycadena [$cad];

        $datos = explode('|', $linea); //
        // echo "<br>Dato<pre>CAD:" . $cad . "<br>" . print_r ( $datos );// 

        if ($cad == 0) {
            $vendedor = $datos [15];
            $seriekm = $datos [15];
            $datosdos = explode('|', $arraycadena [1]);
            $aprobaxfolio = "";
            $aprobaxfolio = TraeAprobacionxFolio($rfc, $serie, $folio, $db);
            $aprobacionfolios = explode('|', $aprobaxfolio);
            //cambiar
            $Certificado = $aprobacionfolios [0];
            $Noaprobacion = $aprobacionfolios [1];
            $anioAprobacion = $aprobacionfolios [2];
            $descuentofact = number_format($descuentofact + $descuentofactAM, $decimalplaces, '.', '');
            //echo "<br>string:".$descuentofact;
            if (empty($descuentofact) || $descuentofact == 0) {
                //$descuentofact = 0;
                $descuentofact = number_format(0, $decimalplaces, '.', '');
            }
            
            // v alida tipo de documento
            $SQL = "SELECT * from debtortrans where id=" . $iddocto;
            $Result1 = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
            if (DB_num_rows($Result1) == 1) {
                $myrowtype = DB_fetch_array($Result1);
                $tipodocumento = trim($myrowtype ['type']);
            }
            if ($tipodocumento != 12) {
                $datos [19] = '';
                $datos [20] = '';
                $datos [21] = '';
            }

            if ($_SESSION['FlagIva'] == 1) {
                
            }

            $subTotal = 0;
            //if ($flagAnticipo == 1 and preg_match("/erpgruposii/", $_SESSION['DatabaseName'])) {
            //    $subTotal = number_format($totalimporte - $totalDescuento, $decimalplaces, '.', '');
            //}else{
            //echo "<br>totalimporte: ". $totalimporte;
            $subTotal = $totalimporte; //number_format($totalimporte, $decimalplaces, '.', '');
            //$subTotal = number_format($totalimporte, $decimalplaces, '.', '');

            //}

            //ANTES SE MODIFICO EL 16-10-2017
            //$total = number_format((($totalimporte - $totalDescuento) + $impuestosinifact - abs($sum)), $decimalplaces, '.', '');  //cambios ant ($totalimporte - $descuentofact), number_format((($totalimporte) + $impuestosinifact - abs($sum)), $decimalplaces, '.', '')

            //             echo "<br>totalimporte: ".$totalimporte;
           // echo "<br>descuentofactFin Antes: : ".$descuentofactFin;


            // redrogo

            $descuentofactFin = exp_to_dec($descuentofactFin); // funcion que permite realizar el cambio de la notacion de numero exponencial a numero con sus digitos completos.. funcion tomada desde la misma libreria de php con la siguiente pagina para su consulta posterior... http://www.php.net/manual/es/language.types.float.php#90754





           // echo "<br>descuentofactFin DEscpues: : ".$descuentofactFin."<br>";

            // echo "<br>impuestosinifact:".$impuestosinifact;
            // echo "<br>sum:".$sum;

            $descuentofactFin = fnDecimalFormat($descuentofactFin ,6) ;
            $totalimporte= fnDecimalFormat($totalimporte ,6) ;
            
            //$total = number_format((($totalimporte - $descuentofactFin) + $impuestosinifact - abs($sum) ), $decimalplaces, '.', '');
            //$total = (($totalimporte - $descuentofactFin) + $impuestosinifact - abs($sum) );//Anterior
            //$total = ((fnDecimalFormat($totalimporte,2) -fnDecimalFormat($descuentofactFin,2)) + fnDecimalFormat($impuestosinifact,2) - fnDecimalFormat(abs($sum),2 ));// Anterior
            //Se hizo este cambio porque el total no coincidia con los decimales
            /*$total = fnDecimalFormat($totalimporte,2) -fnDecimalFormat($descuentofactFin,2);
            $total = number_format((float) $total, 2, '.', '');
            $total = $total + fnDecimalFormat($impuestosinifact, 2) - fnDecimalFormat(abs($sum),2 );*/

            //$total = $total + 0.000001;
            
             //echo  "subtotal: ".fnDecimalFormat($totalimporte,2)." descuento ".fnDecimalFormat($descuentofactFin,2)."impuesto ".fnDecimalFormat($impuestosinifact,2)." sum: ".$sum. "total".$total." total:".$total."-- ".$impuestosinifact;//."sum".fnDecimalFormat(abs($sum),2 );

            $total = ((number_format($totalimporte, $decimalplaces, '.', '')-number_format($descuentofactFin, $decimalplaces, '.', ''))+ number_format($impuestosinifact, $decimalplaces, '.', '')- abs($sum)) ;

            //echo "<br> TOTALES: ".number_format($totalimporte, $decimalplaces, '.', '')." -".number_format($descuentofactFin, $decimalplaces, '.', '')." - ". number_format($impuestosinifact, $decimalplaces, '.', '')." - ".abs($sum) ;
            //$total = number_format((($totalimporte - $descuentofact) + $impuestosinifact - abs($sum) ), $decimalplaces, '.', '');  //cambios ant ($totalimporte - $descuentofact), number_format((($totalimporte) + $impuestosinifact - abs($sum)), $decimalplaces, '.', '')
            //echo "<br> impuestos:".$impuestosinifact." - ".$impuestoretenido;

            if ( ($subTotal < 0.01 and $subTotal > 0) or $subTotal <= 0 ){
                $subTotal = (number_format(0, $decimalplaces, '.', ''));
            }

            if ( ($total < 0.01 and $total > 0) or $total <= 0 ){
                $total = (number_format(0, $decimalplaces, '.', ''));
            }
           
            if ($TotalImpuestosLocales != 0) {

                //Validar si tiene impuestos locales, para cambios con subtotal
                //$subTotal = $total;
                //$total = $total + $TotalImpuestosLocales;
                //$total = number_format($total  + $TotalImpuestosLocales+$impuestosinifact,'.','');
                //$total = $total  - fnDecimalFormat($TotalImpuestosLocales,2);
                
                $total = number_format($total  - $TotalImpuestosLocales,'.','');

            }
            
            // echo "<br>totalimporte: ".$totalimporte."<br>";
            // echo "<br>totalDescuento: ".$totalDescuento."<br>";
            // echo "<br>impuestosinifact: ".$impuestosinifact."<br>";
            // echo "<br>sum: ".$sum."<br>";
            // echo "<br>total: ".$total."<br>";
            
            $Moneda = $datos [12];
            $monedace= $datos [12];
            if($Moneda != 'MXN') {
                $TipoCambio = fnDecimalFormat($datos [13],2);
                if($_SESSION['UserID'] == 'desarrollo'){
                    echo "<br>tipo cambio:".$TipoCambio;
                }
            } else {
                $TipoCambio = '';
            }

            //Antes tenia agregado en el if --> or $carpeta == 'NCreditoDirect'
            //y Se agrego el tipo de documento 200 - Abono Directo de Cliente a Bancos
            // ya que este tipo de documento manda igual la carpeta 'NCreditoDirect'
            //y afecta al tipo 13 - Notas de Credito Directa
            /*if ($carpeta == 'Recibo' or $tipoDoc==200) {
                //echo "<br>Entra en recibo";
                //$subTotal = number_format(0,'.','');
                $subTotal = 0;//(number_format(0, $decimalplaces, '.', ''));
                //$total = number_format(0,'.','');
                $total = 0;//(number_format(0, $decimalplaces, '.', ''));
            }*/

            //Actualizar monto letra en la cadena
            $totaletrasc=$total;
            $separac=explode(".",$totaletrasc);
            if (count($separac) == 1) {
                $separac[1] = 0;
            }
            $montoctvs2c = $separac[1];
            $montoctvs1c = $separac[0];
            if ($montoctvs2c>995){
                $montoctvs1c=$montoctvs1c+1;
            }
            $montoletrac=Numbers_Words::toWords($montoctvs1c,'es');
            $totaletrasc=number_format($totaletrasc,2);
            $separac=explode(".",$totaletrasc);
            $montoctvs2c = $separac[1];
            if ($montoctvs2c>995){
                $montoctvs2c=0;
            }
            if ($Moneda=='MXN' or $Moneda=='XXX'){
                $cantidadletra=ucwords($montoletrac) . " Pesos ". $montoctvs2c ." /100 M.N.";
            }elseif ($Moneda=='EUR'){
                $cantidadletra=ucwords($montoletrac) . " Euros ". $montoctvs2c ." /100 EUR";
            }else{
                $cantidadletra=ucwords($montoletrac) . " Dolares ". $montoctvs2c ."/100 USD";
            }

            if ($carpeta == 'Recibo' or $tipoDoc==200) {
                $subTotal="0";
                $total="0";
            }else{
                $subTotal = fnDecimalFormat($subTotal,2);//number_format($subTotal, $decimalplaces, '.', ''); //n //en jibe falta un centavo
                //$total;
                $total =  fnDecimalFormat($total, 2);//number_format($total, $decimalplaces, '.', '');//fnDecimalFormat($total, 2);
            }
            if($_SESSION['UserID'] == "desarrollo"){
                //echo '<br>SubTotal: '.$subTotal;
                //echo '<br>total: '.$total;
            }

                $cppp = $codigoPostal;

           

            cargaAtt($root, array(
                "Version" => "3.3",
                "Serie" => $serie,
                "Folio" => $folio,
                "Fecha" => str_replace(' ', 'T', str_replace('/', '-', $datos [4])),
                "Sello" => "@",
                "TipoDeComprobante" => trim($tipocomprobante),
                //"formaDePago" => $datosdos [1],
                "FormaPago" => $datosdos [3],
                "NoCertificado" => trim($Certificado),
                "Certificado" => "@",
                "SubTotal" => $subTotal,
                "Descuento" =>  number_format($descuentofactFin,2,'.',''), //fnDecimalFormat($descuentofactFin,2),//en jibe no cuadra por un centavo
                "Total" => $total,  //cambios ant ($totalimporte - $descuentofact), number_format((($totalimporte) + $impuestosinifact - abs($sum)), $decimalplaces, '.', '')
                //"metodoDePago" => $datosdos [3],
                "MetodoPago" => $MetodoPago,
                "TipoCambio" => $TipoCambio,
                "Moneda" => $Moneda,
                "LugarExpedicion" => $myrowtags ['municipioexpedido'] . ',' . $myrowtags ['estadoexpedido'],
                "LugarExpedicion" => $cppp,
                // "NumCtaPago" => $datosdos [5],
                //"FolioFiscalOrig" => $datos [19],
                //"FechaFolioFiscalOrig" => str_replace(' ', 'T', str_replace('/', '-', $datos [20])),
                //"MontoFolioFiscalOrig" => $datos [21]
            ));

            if ($carpeta == 'Recibo' or $tipoDoc==200) {
                // Se debe eliminar nodo marca error
                $root->removeAttribute('FormaPago');
                $root->removeAttribute('Descuento');
                $root->setAttribute("Moneda", "XXX");
            }

            $fechaamece = str_replace(' ', 'T', str_replace('/', '-', $datos [4]));
            //$cantidadletra = $datos [11];

            if (!empty($claveFactura)) {
                cargaAtt($root, array(
                    "Confirmacion" => $claveFactura,
                ));
            }
        } elseif ($cad >= 1 and $datos [0] == '3') {



            
            if ($banderacomplemento == false) {
                $complemento = $xml->createElement("cfdi:Complemento");
                $banderacomplemento = true;
                //echo 'ingreso';
            }

            if($banderacomercioexterior==false){
                $ComercioExterior = $xml->createElement("cce11:ComercioExterior");
                $banderacomercioexterior=true;
            }
            //echo 'ingreso complemento';

            /*$ComercioExterior = $complemento->appendChild($ComercioExterior);
           */

            $emisor = $xml->createElement("cfdi:Emisor");
            $emisor = $root->appendChild($emisor);

            $emisorcce = $xml->createElement("cce11:Emisor");
            $emisorcce = $ComercioExterior->appendChild($emisorcce);
            // cargaAtt($emisor, array("rfc"=>$rfc,"nombre"=>$legalname));
            cargaAtt($emisor, array(
                "Rfc" => trim($rfc),
                "Nombre" => trim($legalname),
                "RegimenFiscal" => trim($RegimenFiscal)
            ));


            $emisordomiciliocce = $xml->createElement("cce11:Domicilio");
            $emisordomiciliocce = $emisorcce->appendChild($emisordomiciliocce);

            cargaAtt($emisordomiciliocce, array(
                "Calle" => trim($calle),
                "NumeroExterior" => trim($numeroExterior),
                "Colonia" => trim($colonia),
                "Municipio" => trim($municipio),
                "Estado" => trim($estado),
                "Pais" => trim($pais),
                "CodigoPostal" => trim($codigoPostal)
            ));

            $receptor = $xml->createElement("cfdi:Receptor");
            $receptor = $root->appendChild($receptor);
            $datos2 = strtoupper($datos [2]);

            cargaAtt($receptor, array(
                "Rfc" => trim($datos2),
                "Nombre" => trim($datos [3])
            ));

            $receptorcce = $xml->createElement("cce11:Receptor");
            $receptorcce = $ComercioExterior->appendChild($receptorcce);
            
            $receptordomiciliocce = $xml->createElement("cce11:Domicilio");
            $receptordomiciliocce = $receptorcce->appendChild($receptordomiciliocce);

            //Comercio exterior
            $estador="";
            if($_SESSION['DatabaseName'] == 'erppisumma_DES' || $_SESSION['DatabaseName'] == 'erppisumma' || $_SESSION['DatabaseName'] == 'erppisumma_CAPA' || $_SESSION['DatabaseName'] == 'erpmg_DES' || $_SESSION['DatabaseName'] == 'erpmg' || $_SESSION['DatabaseName'] == 'erpmg_CAPA'){

                $sqled = 'SELECT c_estado AS estado
                            FROM estado_sat
                            WHERE LOWER(estado)=LOWER("'.trim($datos [12]).'")';

                $ErrMsg = _('El Sql que fallo fue');
                $DbgMsg = _('No se pudo obtener la clave del articulo '.trim($datos [2]));
                //echo "<br>".$sqlAnt."<br>";
                $resulted= DB_query($sqled, $db, $ErrMsg, $DbgMsg, true);
                if ($_SESSION ['UserID'] == 'desarrollo') {
                   // echo 'numreg: '.$sqled;
                }

                if (DB_num_rows($resulted) == 1) {
                    $rowed= DB_fetch_array($resulted);
                    $estador = $rowed['estado'];
                }else{
                    $estador = $datos [12];
                }
            }


            cargaAtt($receptordomiciliocce, array(
                "Calle" => trim($datos [5]),
                "NumeroExterior" => trim($datos [6]),
                "Colonia" => trim($datos [7]),
                "Estado" => $estador,
                "Pais" => trim($datos [4]),
                "CodigoPostal" => trim($datos [13])
            ));

            $paisreceptor=$datos [4];

            $rfccliente = strtoupper($datos [2]);
            $rfccliente = trim($rfccliente);
            $debtorno = trim($datos [1]);


            if (!empty($Cliente_ResidenciaFiscal)) {
                    cargaAtt($receptor, array(
                        "ResidenciaFiscal" => $Cliente_ResidenciaFiscal
                    ));
            }

            cargaAtt($receptor, array(
                "UsoCFDI" => $UsoCFDI
            ));
        } elseif ($cad >= 1 and $datos [0] == '4') {
            
        } elseif ($cad >= 4 and $datos [0] == '5') {
            $descuentoRow = 0;            

            if ($banderaconceptos == false) {
                $conceptos = $xml->createElement("cfdi:Conceptos");
                $conceptos = $root->appendChild($conceptos);

                $mercancias = $xml->createElement("cce11:Mercancias");
                $mercancias = $ComercioExterior->appendChild($mercancias);
                
                $banderaconceptos = true;
            }
            
            $concepto = $xml->createElement("cfdi:Concepto");
            $concepto = $conceptos->appendChild($concepto);

            $mercancia = $xml->createElement("cce11:Mercancia");
            $mercancia = $mercancias->appendChild($mercancia);

            if ($carpeta == 'Recibo') {
                $importe = $datos [6];
                $unidades = $datos [7];
                $descuentoRow=0;
            } elseif ($carpeta == 'NCargo' or $carpeta == 'NCreditoDirect') {
                $importe = $datos [13];
                $unidades = "No Aplica";
                $descuentoRow=0;
            } else {
                //Validación de No. pedimento
                $vrnoPedimento=$datos [21];
                //if ($_SESSION['UserID'] == 'admin') { echo "<br>".$vrnoPedimento; }
                $importe = $datos [6];// * $datos [3]; // $datos[13];
                $unidades = $datos [7];
                $descuentoRow=$datos [13];
                $importe = ($datos [5] * $datos [3]);
                //$importe = (trim(number_format(($datos [5]), $decimalplaces, '.', '')) * trim(number_format(($datos [3]), $decimalplaces, '.', '')));
            }
            //echo "unidades".$datos[7].'---'.$datos[3].' Importe ....'.$importe;
            //echo "<br>IE: " . $_SERVER['HTTP_USER_AGENT'];

            if (isset($_SERVER['HTTP_USER_AGENT']) &&
                    ((strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false) || (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') !== false))) {
                $texto = $datos[4];
            } else {
                $texto = $datos[4];
            }
            $descripcion = trim($datos[4]);

            if($_SESSION['UsarMedidas']==1){
               if ($datos[16] != "") {
                $descripcion = $descripcion . "  Aduana: " . $datos[16] . ", No. Pedimento: " . $datos[21] . ", Fecha Aduana: " . $datos[20] . "  ";
                } 
            }
            //Validación de No. pedimento
            if($_SESSION['DatabaseName'] == 'erppisumma_DES' || $_SESSION['DatabaseName'] == 'erppisumma' || $_SESSION['DatabaseName'] == 'erppisumma_CAPA'){
                if ($datos[16] != "") {
                //$descripcion = $descripcion . "  Aduana: " . $datos[16] . ", No. Pedimento: " . $datos[21] . ", Fecha Aduana: " . $datos[20] . "  ";
                    $descripcion = $descripcion . " No. Pedimento: " . $datos[21] . "  ";
                    if ($_SESSION['UserID'] == 'desarrollo') {
                        //echo '<br>description :'.$descripcion;
                    }
                }
            }
            $valorUnitario = $datos [5];
            $stockidPrducto = trim($datos [2]);

            $c_ClaveProdServ = "";
            $c_ClaveUnidad = "";
            if ($carpeta == 'Recibo' or $carpeta == 'NCreditoDirect' or $carpeta == 'NCargo') {
                //Codigo catalogo sat_stock
                $c_ClaveProdServ = "84111506";
                //Codigo catalogo sat_unitsofmeasure
                $c_ClaveUnidad = "ACT";
            }else{

                $SQLFix="SELECT * FROM fixedassets WHERE  barcode = '".trim($datos [2])."'";
                $rsFix = DB_query($SQLFix, $db, '', '', true);
                $stockidPrductoTMP = trim($datos [2]);
                if(DB_num_rows($rsFix) >= 1){
                    $stockidPrductoTMP = "ACT01";
                }

                $sqlClave = "SELECT stockmaster.sat_stock_code, unitsofmeasure.c_ClaveUnidad, stockmaster.stockid
                        FROM stockmaster
                        LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
                        LEFT JOIN unitsofmeasure ON unitsofmeasure.unitname = stockmaster.units
                        WHERE stockid = '".trim($stockidPrductoTMP)."'";
                $ErrMsg = _('El Sql que fallo fue');
                $DbgMsg = _('No se pudo obtener la clave del articulo '.trim($datos [2]));
                //echo "<br>sql details".$sqlAnt."<br>";
                $resultClave = DB_query($sqlClave, $db, $ErrMsg, $DbgMsg, true);
                if (DB_num_rows($resultClave) == 1) {
                    $rowClave = DB_fetch_array($resultClave);
                    $c_ClaveProdServ = $rowClave['sat_stock_code'];
                    $c_ClaveUnidad = $rowClave['c_ClaveUnidad'];
                    $stockidPrducto = $rowClave['stockid'];
                }else{
                    $sqlClave = 'SELECT stockmaster.sat_stock_code, unitsofmeasure.c_ClaveUnidad, stockmaster.stockid
                        FROM stockmaster
                        LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
                        LEFT JOIN unitsofmeasure ON unitsofmeasure.unitname = stockmaster.units
                        WHERE stockmaster.description = "'.trim($datos [4]).'"';

                        //echo "<br> CLAVE: ".$sqlClave;
                        $resultClave = DB_query($sqlClave, $db, $ErrMsg, $DbgMsg, true);
                        $rowClave = DB_fetch_array($resultClave);
                        $c_ClaveProdServ = $rowClave['sat_stock_code'];
                        $c_ClaveUnidad = $rowClave['c_ClaveUnidad'];
                        $stockidPrducto = $rowClave['stockid'];

                        //echo "<br> : clave : ".$c_ClaveProdServ;

                        //var_dump($rowClave);


                }
            }

            if (empty($c_ClaveProdServ)) {
                $c_ClaveProdServ = "84111506";
            }

            $valorUnitario = str_replace(',', '', $valorUnitario);
            $importe = str_replace(',', '', ($importe));
            $cantidad = abs($datos[3]); //Cantidad siempre en positivo

            $valorUnitario = trim(number_format($valorUnitario, $decimalesFac, '.', ''));

            //echo "<br> Valor unitario: ".$valorUnitario;
            $importe = trim(number_format(($importe), $decimalesFac, '.', ''));

            //Antes tenia agregado en el if --> or $carpeta == 'NCreditoDirect'
            if ($carpeta == 'Recibo' or $tipoDoc==200) {
                $valorUnitario = 0;
                $importe = 0;
                $descuentoRow = 0;
                $descripcion = 'Pago';
            }elseif ($carpeta == 'NCreditoDirect'){
                $descuentoRow = 0;
                //$descripcion = 'Pago';
            }
            if($_SESSION['UserID'] == 'desarrollo'){
                echo "descuento xml:".trim(number_format(($descuentoRow), $decimalplaces, '.', ''));
            }
            // se hace cambio para redondear a dos decimales
            /*cargaAtt($concepto, array(
                "ClaveProdServ" => trim($c_ClaveProdServ),
                "ClaveUnidad" => trim($c_ClaveUnidad),
                "Cantidad" => trim($cantidad),
                "Unidad" => trim($unidades),
                "NoIdentificacion" => trim($datos [2]),
                "Descripcion" => trim($descripcion),
                "ValorUnitario" => fnDecimalFormat(trim($valorUnitario),6),
                //"ValorUnitario" => trim(number_format($valorUnitario, 6, '.', '')),
                "Importe" => fnDecimalFormat(trim($importe),6),
                //"Importe" => trim(number_format(($importe), 6, '.', '')),
                "Descuento" => fnDecimalFormat(trim($descuentoRow),6)
                //"Descuento" => trim(number_format(($descuentoRow), $decimalplaces, '.', ''))
            ));*/

            cargaAtt($concepto, array(
                "ClaveProdServ" => trim($c_ClaveProdServ),
                "ClaveUnidad" => trim($c_ClaveUnidad),
                "Cantidad" => trim($cantidad),
                "Unidad" => trim($unidades),
                "NoIdentificacion" => trim($datos [2]),
                "Descripcion" => trim($descripcion),
                //"ValorUnitario" => fnDecimalFormat(trim($valorUnitario),6),
                "ValorUnitario" => $valorUnitario,// se cambió de $decimalplaces a $decimalesFac para matel
                //"Importe" => fnDecimalFormat(trim($importe),6),
                "Importe" => $importe,
                //"Descuento" => fnDecimalFormat(trim($descuentoRow),6)
                "Descuento" => trim(number_format(($descuentoRow), $decimalesFac, '.', ''))
            ));

            if ($_SESSION['UserID'] == 'desarrollo') {

                //echo '<br>description :'.$descripcion;
            }

            /*$sql = 'SELECT folio, type, uuid
                    FROM log_complemento_sustitucion 
                    WHERE sustitucion_from = '.$iddocto;
            $query = DB_query($sql, $db);

            if($_SESSION ['UserID'] == 'desarrollo' ) {
                echo '<br>'.$sql;
            }
            if(DB_num_rows($query)>0){
                $row = DB_fetch_array($query);
                if($row['type'] == '12' or $row['type'] == '200'){
                        
                    $uuidrelacionados = $xml->createElement("cfdi:CfdiRelacionados");
                    $uuidrelacionados = $root->appendChild($uuidrelacionados);

                    $uuidrelacionado = $xml->createElement("cfdi:CfdiRelacionado");
                    $uuidrelacionado = $uuidrelacionados->appendChild($uuidrelacionado);

                    cargaAtt($uuidrelacionado, array(
                        "UUID" => trim($row['uuid'])
                    ));

                    $uuidrelacionados->SetAttribute("TipoRelacion", '04');
                }
            }*/

            //Comercio exterior 
            $unidadaduana="";

            $fraccionarancelaria="";


            if($_SESSION['DatabaseName'] == 'erppisumma_DES' || $_SESSION['DatabaseName'] == 'erppisumma' || $_SESSION['DatabaseName'] == 'erppisumma_CAPA' OR $_SESSION['DatabaseName'] == 'erpmg_DES' || $_SESSION['DatabaseName'] == 'erpmg_CAPA' || $_SESSION['DatabaseName'] == 'erpmg'){

                

                $sqlfa = "SELECT stocksupplier AS fraccionArancelaria, fraccion.unidad_medida AS unidadAduana
                            FROM stockmaster AS prod
                            left join sat_fraccionArancelaria AS fraccion ON fraccion.c_fraccionArancelaria=prod.stocksupplier
                            WHERE stockid = '".trim($datos [2])."'";
                $ErrMsg = _('El Sql que fallo fue');
                $DbgMsg = _('No se pudo obtener la clave del articulo '.trim($datos [2]));
                //echo "<br>".$sqlAnt."<br>";
                $resultfa= DB_query($sqlfa, $db, $ErrMsg, $DbgMsg, true);
                if (DB_num_rows($resultfa) == 1) {
                    $rowfa = DB_fetch_array($resultfa);
                    $fraccionarancelaria = $rowfa['fraccionArancelaria'];
                    $unidadaduana=$rowfa['unidadAduana'];
                }
                //echo "ORDERNO = ".$orderno;// es provisional para pisumma
                /*if($orderno == '3035'){
                    $cantidadAduana = '27600';
                }else{*/
                $cantidadAduana = $cantidad;
                //}
                // Complementos facturación MHP v2.1
                 if($tipoDoc==10 OR $tipoDoc==110){
                    $sqlComplement='SELECT xmlnode,value
                       FROM orderdetailscomplement AS v
                       INNER JOIN fielddetailscomplement AS f ON f.fieldid=v.fieldid
                       WHERE f.idcomplement=2 AND v.orderno="'.$orderno .'" AND stkcode="'.trim($datos [2]).'" AND orderlineno='.$datos[19];


                    $ErrMsg = _('El SQL cantidad aduana que fallo fue');
                    $DbgMsg = _('No se pudo obtener la clave del articulo '.trim($datos [2]));

                    
                    
                    $resComplement= DB_query($sqlComplement, $db, $ErrMsg, $DbgMsg, true);
                    if (DB_num_rows($resComplement) == 1) {
                        $rowcmp = DB_fetch_array($resComplement);
                        $cantidadAduana = $rowcmp['value'];
                    } 
                }
            }

            cargaAtt($mercancia, array(
                "NoIdentificacion" => trim($datos [2]),
                "FraccionArancelaria" => $fraccionarancelaria,
                "CantidadAduana"=>$cantidadAduana,
                "UnidadAduana" =>$unidadaduana,
                "ValorUnitarioAduana" => number_format(trim($importe) /$cantidadAduana,2,'.',''), //trim(number_format(($importe), $decimalplaces, '.', ''))/$cantidad,
                "ValorDolares" => fnDecimalFormat(trim($importe),2) //trim(number_format(($importe), $decimalplaces, '.', ''))
            ));

            if ($carpeta == 'Recibo' or $tipoDoc==200) {
                // Se debe eliminar nodo marca error
                $concepto->removeAttribute('NoIdentificacion');
                $concepto->removeAttribute('Unidad');
                $concepto->removeAttribute('Descuento');
            }
            

            //$totalimporte = $totalimporte + number_format(trim($importe), $decimalplaces, '.', '');
            $totalimporte = $totalimporte + number_format(trim($importe), $decimalesFac, '.', ''); // para matel
            //echo "<br> TOTAL IMPORTE. ".$totalimporte;

            if($_SESSION['UserID'] == "desarrollo"){
                //echo "<br>";
            }

            if (($carpeta == 'NCreditoDirect' or $carpeta=='NCredito' OR $carpeta=='NCargo') AND $tipoDoc!=200){
                $Impuestos = $xml->createElement("cfdi:Impuestos");
                $Traslados = $xml->createElement("cfdi:Traslados");
                $Traslado = $xml->createElement("cfdi:Traslado");
                $expedido = $Impuestos->appendChild($Traslados);
                $Traslado = $Traslados->appendChild($Traslado);

                $nuevo = $concepto->appendChild($Impuestos);

                //$importe = $importe - trim(number_format(($descuentoRow), $decimalplaces, '.', ''));//ANT
                /*$importe = $importe - trim($descuentoRow);*/
                //Obtener datos de impuestos del producto
                $Impuesto = "002";
                $TipoFactor = "Tasa";
                if($tipoDoc==200){
                    $TasaOCuota = '0.000000';
                    $ImporteTax = 0;
                }else{
                    $NCIva = trim($datos[14]);
                    $NCIva = str_replace(',', '', $NCIva);
                    $TasaOCuota = ($NCIva/100);
                    $ImporteTax = $importe * $TasaOCuota;
                }
                //Obtener datos de impuestos del producto
                //$Impuesto = "";
                //$TipoFactor = "";
                //$TasaOCuota = "";
                //$ImporteTax = "";
                
                $datos[2] = caracteresEspecialesFactura($datos[2]);
                $sqlImp = "SELECT 
                        taxauthorities.c_Impuesto,
                        taxauthrates.taxrate,
                        sat_tasas.c_TipoFactor,
                        taxcategories.taxcatname
                        FROM stockmaster
                        LEFT JOIN taxauthrates ON taxauthrates.taxcatid = stockmaster.taxcatid
                        LEFT JOIN taxauthorities ON taxauthorities.taxid = taxauthrates.taxauthority
                        LEFT JOIN taxcategories ON taxcategories.taxcatid = stockmaster.taxcatid
                        LEFT JOIN sat_tasas ON sat_tasas.id = taxcategories.id_Tasa
                        WHERE stockmaster.stockid = '".trim($datos [2])."'";
                $ErrMsg = _('El Sql que fallo fue');
                $DbgMsg = _('No se pudo obtener el impuesto del articulo '.trim($datos [2]));
                
                //echo "<br>".$sqlAnt."<br>";

                $resultImp = DB_query($sqlImp, $db, $ErrMsg, $DbgMsg, true);

                $importe = $importe - $descuentoRow;
                //$importe = $importe - trim(number_format(($descuentoRow), $decimalplaces, '.', ''));
                if(strtoupper( $rowImp['taxcatname'] )!= 'EXENTO'){
                    $SinExcentos=0;
                    if (DB_num_rows($resultImp) == 1) {
                        $rowImp = DB_fetch_array($resultImp);
                        $Impuesto = $rowImp['c_Impuesto'];
                        $TipoFactor = $rowImp['c_TipoFactor'];
                        $TasaOCuota = $rowImp['taxrate'];
                        if($TasaOCuota == 0) {
                            $TasaOCuota = '0.000000';
                        }
                        $ImporteTax = $importe * $rowImp['taxrate'];
                    }
                    
                    cargaAtt($Traslado, array(
                        "Base" =>  trim(number_format(($importe), $decimalplaces, '.', '')),//fnDecimalFormat(trim($importe),6), se hace cambio para redondeo
                        "Impuesto" => trim($Impuesto),
                        "TipoFactor" => trim($TipoFactor),
                        "TasaOCuota" => trim(number_format($TasaOCuota, 6, '.', '')),
                        "Importe" =>  trim(number_format(($ImporteTax), $decimalesFac, '.', ''))//fnDecimalFormat(trim($ImporteTax),6)matel
                    ));

                    //******* Impuestos Agrupados
                    $encontro = 0;
                    foreach ($impuestosAgrupadosTrasladados as $key2 => $value3) {
                        if ($value3['Impuesto'] == $Impuesto && $value3['TipoFactor'] == $TipoFactor && $value3['TasaOCuota'] == $TasaOCuota) {
                            $encontro = 1;
                            $impuestosAgrupadosTrasladados[$key2]['Importe']+= number_format(trim($ImporteTax), $decimalplaces, '.', '');
                        }
                    }
                    if ($encontro == 0) {
                        $impAgrupado = array();
                        $impAgrupado['Impuesto'] = trim($Impuesto);
                        $impAgrupado['TipoFactor'] = trim($TipoFactor);
                        $impAgrupado['TasaOCuota'] = trim(number_format(($TasaOCuota), '6', '.', ''));
                        if($_SESSION['DatabaseName']== 'erpmatelpuente' OR  $_SESSION['DatabaseName']== 'erpmatelpuente_CAPA' OR $_SESSION['DatabaseName']== 'erpmatelpuente_DES'){
                            $impAgrupado['Importe'] = fnDecimalFormat(trim($ImporteTax),$decimalesFac);

                        }else{
                            $impAgrupado['Importe'] =  trim(number_format(($ImporteTax), $decimalplaces, '.', ''));//fnDecimalFormat(trim($ImporteTax),6);

                        }

                        array_push($impuestosAgrupadosTrasladados, $impAgrupado);
                    }
                }
                //$impuestofact = $impuestofact + $ImporteTax;
                $impuestofact = $impuestofact + trim(number_format(($ImporteTax), $decimalplaces, '.', ''));
                //******* Impuestos Agrupados
                
                if ($_SESSION['UserID'] == 'admin') {
                    //echo "<br>".trim(number_format(($importe), $decimalplaces, '.', ''))." ".trim(number_format(($TasaOCuota), '6', '.', ''))." ".trim(number_format(($ImporteTax), $decimalplaces, '.', ''));
                    //echo "<br>".$ImporteTax;
                }
            }

            if ($carpeta == 'Facturas') {

                /*$impuestosConcepto = $xml->createElement("cfdi:Impuestos");
                $Traslados = $xml->createElement("cfdi:Traslados");
                $Traslado = $xml->createElement("cfdi:Traslado");
                $expedido = $impuestosConcepto->appendChild($Traslados);
                $Traslado = $Traslados->appendChild($Traslado);

                $nuevo = $concepto->appendChild($impuestosConcepto);*/

                //Obtener datos de impuestos del producto
                $Impuesto = "";
                $TipoFactor = "";
                $TasaOCuota = "";
                $ImporteTax = "";
                $datos[2] = caracteresEspecialesFactura($datos[2]);
                //$banderageneraimpuestos = false;

                //Validacion si es un Activo fijo
                $SQLFix="SELECT * FROM fixedassets WHERE  barcode = '".trim($datos [2])."'";
                $rsFix = DB_query($SQLFix, $db, '', '', true);
                $stockidPrductoTMP =$stockidPrducto;
                if(DB_num_rows($rsFix) >= 1){
                    $stockidPrductoTMP = "ACT01";
                }
                if($_SESSION['UserID'] == "desarrollo"){
                    echo "<pre>::".$SQLFix;
                    echo "es activo fijo::".DB_num_rows($rsFix);
                    echo "::producto:".$stockidPrductoTMP;
                    echo "</pre>";
                }

                $sqlImp = "SELECT 
                        taxauthorities.c_Impuesto,
                        taxauthrates.taxrate,
                        sat_tasas.c_TipoFactor, taxcategories.taxcatname
                        FROM stockmaster
                        LEFT JOIN taxauthrates ON taxauthrates.taxcatid = stockmaster.taxcatid
                        LEFT JOIN taxauthorities ON taxauthorities.taxid = taxauthrates.taxauthority
                        LEFT JOIN taxcategories ON taxcategories.taxcatid = stockmaster.taxcatid
                        LEFT JOIN sat_tasas ON sat_tasas.id = taxcategories.id_Tasa
                        WHERE stockmaster.stockid = '".trim($stockidPrductoTMP)."'";
                $ErrMsg = _('El Sql que fallo fue');
                $DbgMsg = _('No se pudo obtener el impuesto del articulo '.trim($datos [2]));
                //echo "<br>".$sqlAnt."<br>";
                $resultImp = DB_query($sqlImp, $db, $ErrMsg, $DbgMsg, true);

                $importe = $importe - $descuentoRow;
                //$importe = $importe - trim(number_format(($descuentoRow), $decimalplaces, '.', ''));

                if (DB_num_rows($resultImp) == 1) {
                    $rowImp = DB_fetch_array($resultImp);
                    $Impuesto = $rowImp['c_Impuesto'];
                    $TipoFactor = $rowImp['c_TipoFactor'];
                    $TasaOCuota = $rowImp['taxrate'];
                    if($TasaOCuota == 0) {
                        $TasaOCuota = '0.000000';
                    }
                    $ImporteTax = $importe * $rowImp['taxrate'];
                }

                if(strtoupper( $rowImp['taxcatname'] )!= 'EXENTO'){

                    //echo "<br> entra excentos";
                    $SinExcentos = 0;
                    $impuestosConcepto = $xml->createElement("cfdi:Impuestos");
                    $Traslados = $xml->createElement("cfdi:Traslados");
                    $Traslado = $xml->createElement("cfdi:Traslado");
                    $expedido = $impuestosConcepto->appendChild($Traslados);
                    $Traslado = $Traslados->appendChild($Traslado);

                    $nuevo = $concepto->appendChild($impuestosConcepto);
                    //$banderageneraimpuestos = tru;
                
                    cargaAtt($Traslado, array(
                        "Base" =>  trim(number_format(($importe), $decimalesFac, '.', '')),//fnDecimalFormat(trim($importe),6),matel
                        "Impuesto" => trim($Impuesto),
                        "TipoFactor" => trim($TipoFactor),
                        "TasaOCuota" => trim(number_format(($TasaOCuota), '6', '.', '')),
                        "Importe" => trim(number_format(($ImporteTax), $decimalesFac, '.', ''))//fnDecimalFormat($ImporteTax,6) matel
                    ));
                    //echo "<br> impuestos".trim(number_format(($ImporteTax), $decimalesFac, '.', ''));
                    //******* Impuestos Agrupados
                    $encontro = 0;
                    foreach ($impuestosAgrupadosTrasladados as $key2 => $value3) {
                        if ($value3['Impuesto'] == $Impuesto && $value3['TipoFactor'] == $TipoFactor && $value3['TasaOCuota'] == $TasaOCuota) {
                            $encontro = 1;
                            //$impuestosAgrupadosTrasladados[$key2]['Importe'] += fnDecimalFormat(trim($ImporteTax),6);
                            $impuestosAgrupadosTrasladados[$key2]['Importe'] += trim(number_format(($ImporteTax), $decimalesFac, '.', ''));// sumatario de impuestos trasladados total importe
                        }
                    }
                    if ($encontro == 0) {
                        $impAgrupado = array();
                        $impAgrupado['Impuesto'] = trim($Impuesto);
                        $impAgrupado['TipoFactor'] = trim($TipoFactor);
                        $impAgrupado['TasaOCuota'] = trim(number_format(($TasaOCuota), '6', '.', ''));
                        //$impAgrupado['Importe'] = fnDecimalFormat(trim($ImporteTax),6);
                        $impAgrupado['Importe'] = trim(number_format(($ImporteTax), $decimalesFac, '.', ''));

                        array_push($impuestosAgrupadosTrasladados, $impAgrupado);
                    }

                }

                
                //$impuestofact = $impuestofact + $ImporteTax;
                $impuestofact = $impuestofact + trim(number_format(($ImporteTax), $decimalesFac, '.', ''));
                //******* Impuestos Agrupados
                
                if ($_SESSION['UserID'] == 'desarrollo') {
                    //echo "<br>".trim(number_format(($importe), $decimalplaces, '.', ''))." ".trim(number_format(($TasaOCuota), '6', '.', ''))." ".trim(number_format(($ImporteTax), $decimalplaces, '.', ''));
                    echo "<br> tax: ".$ImporteTax;
                }
                 //Validación de No. pedimento
                if($_SESSION['DatabaseName'] == 'erppisumma_DES' || $_SESSION['DatabaseName'] == 'erppisumma' || $_SESSION['DatabaseName'] == 'erppisumma_CAPA' 
                || $_SESSION['DatabaseName'] == 'erpmg_DES' 
                || $_SESSION['DatabaseName'] == 'erpmg' 
                || $_SESSION['DatabaseName'] == 'erpmg_CAPA' ){
                    if(isset($vrnoPedimento) and strlen($vrnoPedimento) > 1){  

                        $InformacionAduanera = $xml->createElement("cfdi:InformacionAduanera");
                        $xml->PreserveWhitespace = true;
                        $InformacionAduanera = $concepto->appendChild($InformacionAduanera);
                        $NumeroPedimento = $vrnoPedimento;
                        $InformacionAduanera->SetAttribute("NumeroPedimento",$NumeroPedimento);
                    }


                    if(strtolower($paisreceptor) != 'mexico' && ($monedace=='USD' || $monedace=='EUR')){
                        if ($banderacomplemento == false) {
                            $complemento = $xml->createElement("cfdi:Complemento");
                            $banderacomplemento = true;
                        }
                        $crearcomercioexterior=true;
                    }
                }
                // COMIENZA RETENCIONES
            
                if ($_SESSION['UserID'] == 'desarrollo') {
                        echo "<br>datos imp: ".$datos [3]." - ".$banderageneraimpuestos;

                       // var_dump($datos);
                    }
                if ($banderageneraimpuestos == false AND  $SinExcentos == 0) {

                    $impuestos = $xml->createElement("cfdi:Impuestos");
                    $impuestos = $root->appendChild($impuestos);
                    $banderageneraimpuestos = true;
                }

                $SQL = "Select debtortranstaxesclient.*,sec_taxes.* from debtortranstaxesclient, sec_taxes
                where debtortranstaxesclient.idtax = sec_taxes.idtax
                and debtortranstaxesclient.iddoc = ".$iddocto." AND flagimpuestolocal = 0";

                $resultado = DB_query( $SQL, $db);

                if(DB_num_rows($resultado)>0){
                    /*$banderaimpuestosretenidos = true;
                    // $banderaimpuestosretenidos = true;

                    $Retenciones1 = $xml->createElement("cfdi:Retenciones");
                    $Retencion1 = $xml->createElement("cfdi:Retencion");
                    $expedido = $impuestosConcepto->appendChild($Retenciones1);
                    $Retencion1 = $Retenciones1->appendChild($Retencion1);

                    $nuevo = $concepto->appendChild($impuestosConcepto);*/

                    //if ($banderaimpuestosretenidos == false) {
                    $Retenciones = $xml->createElement("cfdi:Retenciones");
                    // ****** Retenciones Concepto
                    $retencionesConcepto = $xml->createElement("cfdi:Retenciones");
                    //if(empty($impuestosConcepto)){
                    //    $retencionesConcepto = $Impuestos->appendChild($retencionesConcepto);

                    // }else{
                    $nuevo = $impuestosConcepto->appendChild($retencionesConcepto);

                    // }
                    // ****** Retenciones Concepto

                    $banderaimpuestosretenidos = true;
                    /*$Retenciones = $xml->createElement("cfdi:Retenciones");

                    // ****** Retenciones Concepto
                    $retencionesConcepto = $xml->createElement("cfdi:Retenciones");
                    $retencionesConcepto = $Impuestos->appendChild($retencionesConcepto);
                    // ****** Retenciones Concepto
                    $Retenciones = $impuestos->appendChild($Retenciones);*/
                    //}

                    // $retenido = $xml->createElement("cfdi:Retencion");
                    // $retenido = $Retenciones->appendChild($retenido);

                    // cargaAtt($retenido, array(
                    //     "Impuesto" => trim($datos [5]),
                    //     "Importe" => trim(number_format(abs($datos [3]), $decimalplaces, '.', ''))
                    // ));

                    // $impuestoretenido = $impuestoretenido + trim($datos [3]);

                    while ($datosRete = DB_fetch_array($resultado)){
                        // ****** Retenciones Concepto
                        $retenidoConceptos = $xml->createElement("cfdi:Retencion");
                        $retenidoConceptos = $retencionesConcepto->appendChild($retenidoConceptos);

                        $TasaOCuota = $datosRete['percent'];
                        if ($TasaOCuota > 0) {
                            $TasaOCuota = $TasaOCuota / 100;
                        }
                        //$impuestoretenido = $impuestoretenido + trim($importe * $TasaOCuota);

                        $impuestoretenido = $impuestoretenido +trim(number_format(($importe * $TasaOCuota), $decimalesFac, '.', ''));


                        // echo "<br> Impuesto retenido =".$impuestoretenido ;
                        cargaAtt($retenidoConceptos, array(
                            "Base" => trim(number_format(($importe), $decimalplaces, '.', '')),
                            "Impuesto" => trim($datosRete['c_Impuesto']),
                            "TipoFactor" => trim($datosRete['c_TipoFactor']),
                            "TasaOCuota" => trim(number_format(($TasaOCuota), '6', '.', '')),
                            "Importe" => trim(number_format(($importe * $TasaOCuota), $decimalplaces, '.', ''))
                        ));
                        // ****** Retenciones Concepto
                        //******* Impuestos Agrupados
                        $encontroret = 0;
                        foreach ($impuestosAgrupadosRetenciones as $key2 => $value3) {
                            if ($value3['Impuesto'] == $datosRete['c_Impuesto'] && $value3['TipoFactor'] == $datosRete['c_TipoFactor'] && $value3['TasaOCuota'] == $TasaOCuota) {
                                $encontroret = 1;
                                //$impuestosAgrupadosRetenciones[$key2]['Importe'] += trim(abs($importe * $TasaOCuota));
                                $impuestosAgrupadosRetenciones[$key2]['Importe'] += trim(number_format(($importe * $TasaOCuota), $decimalesFac, '.', ''));
                            }
                        }
                        if ($encontroret == 0) {
                            $impAgrupado = array();
                            $impAgrupado['Base'] = trim(number_format(($importe), $decimalplaces, '.', ''));
                            $impAgrupado['Impuesto'] = trim($datosRete['c_Impuesto']);
                            $impAgrupado['TipoFactor'] = trim($datosRete['c_TipoFactor']);
                            $impAgrupado['TasaOCuota'] = $TasaOCuota;
                            $impAgrupado['Importe'] = trim(abs($importe * $TasaOCuota));

                            array_push($impuestosAgrupadosRetenciones, $impAgrupado);
                        }
                    }
                }

                
            // FIN DE RETENCIONES
            }
        } elseif ($cad >= 4 and $datos [0] == '7') {
            //echo "<br> BANSERA: ".$banderageneraimpuestos;
            if ($banderageneraimpuestos == false AND $SinExcentos == 0) {
                $impuestos = $xml->createElement("cfdi:Impuestos");
                $impuestos = $root->appendChild($impuestos);
                $banderageneraimpuestos = true;
            }

            if ($banderaimpuestos == false AND $SinExcentos == 0) {
                $traslados = $xml->createElement("cfdi:Traslados");
                $traslados = $impuestos->appendChild($traslados);
                $banderaimpuestos = true;
                // Agregar cuando sea addenda MAPFRE ...
                if ($rfccliente == 'MTE440316E54' || $_SESSION['FlagIva'] == 1) {
                    // $traslado = $xml->createElement("cfdi:Traslado");
                    // $traslado = $traslados->appendChild($traslado);
                    $datos[2] = caracteresEspecialesFactura($datos[2]);
                    if(empty($datos [2]) or $datos [2] == 0){
                        $FormatTasaOCuota='0.000000';
                    }else{
                        $FormatTasaOCuota= trim(number_format($datos [2]/100, '6', '.', ''));
                    }

                    // cargaAtt($traslado, array(
                    //     "Impuesto" => trim($datos [4]),
                    //     "TasaOCuota" => $FormatTasaOCuota,
                    //     "Importe" => trim(number_format($impuestosinifact, $decimalplaces, '.', '')),
                    //     "TipoFactor" => trim($datos [5]),
                    // ));
                    //$impuestofact = trim(number_format(($impuestosinifact), $decimalplaces, '.', ''));
                }
            }

            // No agregar cuando sea addenda MAPFRE ...
            if ($rfccliente != 'MTE440316E54' && $_SESSION['FlagIva'] != 1) {
                // $traslado = $xml->createElement("cfdi:Traslado");
                // $traslado = $traslados->appendChild($traslado);

                if(empty($datos [2])){
                    $FormatTasaOCuota='0.0000';
                }else{
                    $FormatTasaOCuota= trim(number_format(($datos [2]/100), '6', '.', ''));
                }

                if ($_SESSION['UserID'] == 'admin') {
                    //echo "<br>datos imp: ".$datos [3];
                }

                // cargaAtt($traslado, array(
                //     "Impuesto" => trim($datos [4]),
                //     "TasaOCuota" => $FormatTasaOCuota,
                //     "Importe" => trim(number_format($datos [3], $decimalplaces, '.', '')),
                //     "TipoFactor" => trim($datos [5]),
                // ));
                //$impuestofact = $impuestofact + trim(number_format(trim($datos [3]), $decimalplaces, '.', ''));
            }
        } elseif ($cad >= 4 and $datos [0] == '6') { 
            $Retenciones = $impuestos->appendChild($Retenciones);
        } elseif ($cad >= 4 and $datos [0] == '8') {
            if ($banderacomplemento == false) {
                $complemento = $xml->createElement("cfdi:Complemento");

                //Probando con el timbrado 3.3
                // cargaAtt($complemento, array(
                //     "xmlns:cfdi" => "http://www.sat.gob.mx/cfd/3",
                // ));
                $banderacomplemento = true;
            }

            if ($banderaimploc == false) {
                $impLocal = $xml->createElement("implocal:ImpuestosLocales");
                $banderaimploc = true;
                $banderaimpuestoslocales = true;
            }
            /*
             * cargaAtt ( $impLocal, array (
             * "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
             * "xmlns:implocal" => "http://www.sat.gob.mx/implocal",
             * "xsi:schemaLocation" => "http://www.sat.gob.mx/implocal http://www.sat.gob.mx/sitio_internet/cfd/implocal/implocal.xsd"
             * ) );
             */
            $impLocal = $complemento->appendChild($impLocal);

            $ind = 1;

            //while ( $ind < count ( $datos ) ) {
            $desc = $datos [1];
            $porc = $datos [2];
            $cant = $datos [3];

            if ($cant < 0) {
                $tRet += abs($cant);

                $retLocales = $xml->createElement("implocal:RetencionesLocales");
                $retLocales = $impLocal->appendChild($retLocales);

                cargaAtt($retLocales, array(
                    "ImpLocRetenido" => $desc,
                    "TasadeRetencion" => number_format($porc,2),
                    "Importe" => str_replace(",", "", number_format(abs($cant), 2))
                ));
            } elseif ($cant != '' and $cant >= 0) {
                $tTras += $cant;

                $trasLocales = $xml->createElement("implocal:TrasladosLocales");
                $trasLocales = $impLocal->appendChild($trasLocales);

                cargaAtt($trasLocales, array(
                    "ImpLocTrasladado" => $desc,
                    "TasadeTraslado" => $porc,
                    "Importe" => str_replace(",", "", number_format($cant, 2))
                ));
            }
            if ($desc != '' AND $porc != '' AND $cant != '') {
                $cadimp .= "|" . $desc . "|" . $porc . "|" . str_replace(",", "", number_format(abs($cant), 2));
            }

            /* if($_SESSION['UserID']=='desarrollo'){
              echo '<br>-->aqui esta seccion--> '.$cadimp.'<br>';
              } */

            $ind += 3;
            //}
        } elseif ($cad >= 1 and $datos [0] == '11') {
            //echo "<br> entra anticipos<br>";
            if(trim($datos [1]) != ''){
                if ($banderaUUIDRelacionados == false) {
                $uuidrelacionados = $xml->createElement("cfdi:CfdiRelacionados");
                $uuidrelacionados = $root->appendChild($uuidrelacionados);

                $banderaUUIDRelacionados = true;
                }
                $uuidrelacionado = $xml->createElement("cfdi:CfdiRelacionado");
                $uuidrelacionado = $uuidrelacionados->appendChild($uuidrelacionado);

                cargaAtt($uuidrelacionado, array(
                    "UUID" => trim($datos [2])
                ));
                if ($banderaUUIDRelacionados == true) {
                    $uuidrelacionados->SetAttribute("TipoRelacion", trim($datos [1])); // se comentó para que se tome del sendinvpicing
                }

            }
        } 
        elseif ($cad >= 1 and $datos [0] == '12') {
            //echo "<br> entra anticipos <br>.PRUEBA ".trim($datos [1]);
            if(trim($datos [1]) != ''){
                if ($banderaUUIDRelacionadosNC == false) {
                    $uuidrelacionadosNC = $xml->createElement("cfdi:CfdiRelacionados");
                    $uuidrelacionadosNC = $root->appendChild($uuidrelacionadosNC);

                    $banderaUUIDRelacionadosNC = true;
                }
                $uuidrelacionadoNC = $xml->createElement("cfdi:CfdiRelacionado");
                $uuidrelacionadoNC = $uuidrelacionadosNC->appendChild($uuidrelacionadoNC);
                if ($banderaUUIDRelacionadosNC == true) {
                    $uuidrelacionadosNC->SetAttribute("TipoRelacion", trim($datos [1])); // se comentó para que se tome del sendinvpicing
                }

                cargaAtt($uuidrelacionadoNC, array(
                    "UUID" => trim($datos [2])
                ));
            }// fin de if de cad
        }

    }

    // impuestos retenidos federales
    
    if ($banderaimpuestosretenidos == true) {
        $impuestos->SetAttribute("TotalImpuestosRetenidos", number_format(abs($impuestoretenido), $decimalplaces, '.', ''));

        
        //******* Impuestos Agrupados
        foreach ($impuestosAgrupadosRetenciones as $key2 => $value3) {
            // echo "<br>****************";
            //echo "<br>Base: RETENCIONES 1".$value3['Base'];
            // echo "<br>Impuesto: ".$value3['Impuesto'];
            // echo "<br>TipoFactor: ".$value3['TipoFactor'];
            // echo "<br>TasaOCuota: ".$value3['TasaOCuota'];
            // echo "<br>Importe: ".$value3['Importe'];
            $retenido = $xml->createElement("cfdi:Retencion");

            $retenido = $Retenciones->appendChild($retenido);


            cargaAtt($retenido, array(
                "Impuesto" => $value3['Impuesto'],
                "Importe" => number_format($value3['Importe'], $decimalplaces, '.', '')
            ));
        }
        //******* Impuestos Agrupados
    }

    if ($banderaUUIDRelacionados == true) {
        //$uuidrelacionados->SetAttribute("TipoRelacion", "07"); // se comentó para que se tome del xsainvpicing(sustitucion) 
    }

    if ($banderaUUIDRelacionadosNC == true) {
        $c_TipoRelacion="";
        if($carpeta=="NCreditoDirect" ){
            $c_TipoRelacion="01";
        }else{
            $c_TipoRelacion="03";
        }
        if($tipoDoc==200 OR $tipoDoc==12){
            $c_TipoRelacion="08";
        }
        //$uuidrelacionadosNC->SetAttribute("TipoRelacion", $c_TipoRelacion); // Se comentó para que se tome del sendiInvoicing(sustitucion) 
    }

    $sqlservice = "SELECT type 
                        FROM debtortrans
                        WHERE order_ = " . $orderno;
    $resultservice = DB_query($sqlservice, $db);
    $rowservice = DB_fetch_array($resultservice);
    $typeservice = $rowservice ['type'];
    if ($typeservice == 119) {
        if ($_SESSION ['DatabaseName'] == "erpmservice" or $_SESSION ['DatabaseName'] == "erpmservice_CAPA" or $_SESSION ['DatabaseName'] == "erpmservice_DES" or $_SESSION ['DatabaseName'] == "erpmservice_DIST") {
            $banderaimpuestos == false;
        }
    }
    if ($banderaimpuestos == true AND  $SinExcentos == 0) {
        //$impuestofact = $impuestofact; //cambios ant
        //$impuestofact = fnDecimalFormat($impuestofact ,2);
        $impuestofact = number_format($impuestofact, $decimalplaces, '.', ''); //cambios ant

        if ( ($impuestofact < 0.01 and $impuestofact > 0) or $impuestofact <= 0){
            $impuestofact = number_format(0, $decimalplaces, '.', '');
        }
        //Antes  or $carpeta == 'NCreditoDirect'
        //prov¡blemas con el tipo 13 - Notas de Credito Directa
        if ($carpeta == 'Recibo' or $tipoDoc ==200 ) {
            //$impuestofact = 0;
            $impuestofact = number_format(0, $decimalplaces, '.', '');
        }
        //echo "<br>TotalImpuestosTrasladados: ".$impuestofact;
        $impuestos->SetAttribute("TotalImpuestosTrasladados", $impuestofact);  //cambios ant number_format($impuestofact, $decimalplaces, '.', '')

        // Actualizar Total de encabezado considerando la aritmetica final
        // subtotal - descuentos + todos impuestos = Total 
        /*$total = ((number_format($totalimporte, $decimalplaces, '.', '')-number_format($descuentofactFin, $decimalplaces, '.', ''))+ number_format($impuestofact, $decimalplaces, '.', '')-abs($sum)) - number_format(abs($impuestoretenido), $decimalplaces, '.', '')-number_format(abs($TotalImpuestosLocales), $decimalplaces, '.', '');;
        
        $total = number_format($total, $decimalplaces, '.', '');*/ // foramto a dos
        if($_SESSION['DatabaseName'] == 'erpmatelpuente_CAPA' OR  $_SESSION['DatabaseName'] == 'erpmatelpuente' OR $_SESSION['DatabaseName'] == 'erpmatelpuente_DES'){
            $total = ((round($totalimporte, 2)-fnDecimalFormat($descuentofactFin, 6))+ round($impuestofact, 2)-abs($sum)) - fnDecimalFormat(abs($impuestoretenido), $decimalplaces)-fnDecimalFormat(abs($TotalImpuestosLocales), 6);;
        
            $total = round($total, $decimalplaces); // foramto a dos

        }else{
            $total = ((number_format($totalimporte, $decimalplaces, '.', '')-number_format($descuentofactFin, $decimalplaces, '.', ''))+ number_format($impuestofact, $decimalplaces, '.', '')-abs($sum)) - number_format(abs($impuestoretenido), $decimalplaces, '.', '')-number_format(abs($TotalImpuestosLocales), $decimalplaces, '.', '');;
        
            $total = number_format($total, $decimalplaces, '.', ''); // foramto a dos

        }

        if ($_SESSION ['UserID'] == 'desarrollo') {
           echo '<br>subtotal2 :'.$totalimporte;     
        }
        if ($carpeta == 'Recibo' or $tipoDoc ==200 ) {
            $root->SetAttribute("SubTotal", number_format($totalimporte, 0, '.', '')); 
            $root->SetAttribute("Total", 0); 
        }else{
            $root->SetAttribute("SubTotal", number_format($totalimporte, $decimalplaces, '.', ''));
            $root->SetAttribute("Total", $total);   
        }

        //echo "TOTAL IMPORTE 2:  ".$totalimporte;

        //******* Impuestos Agrupados
        foreach ($impuestosAgrupadosTrasladados as $key2 => $value3) {
            // echo "<br>****************";
            // echo "<br>Base: ".$value3['Base'];
            // echo "<br>Impuesto: ".$value3['Impuesto'];
            // echo "<br>TipoFactor: ".$value3['TipoFactor'];
            // echo "<br>TasaOCuota: ".$value3['TasaOCuota'];
            // echo "<br>Importe: ".$value3['Importe'];
            $traslado = $xml->createElement("cfdi:Traslado");
            $traslado = $traslados->appendChild($traslado);

            //$value3['Importe'] = floor($value3['Importe'] * 100) / 100;

            if($_SESSION['Timbrador'] == "SOFTTI") // validacion para identificar el timbrador dado que Softti si valida los decimales a dos digitos...
            {
                $decimalsFormat = 2;
            }
            else
            {
               $decimalsFormat = 6; 
            }
            cargaAtt($traslado, array(
                "Impuesto" => $value3['Impuesto'],
                "TasaOCuota" => $value3['TasaOCuota'],
                //"Importe" => fnDecimalFormat($value3['Importe'],2),
                "Importe" => number_format($value3['Importe'], 2, '.', ''),
                "TipoFactor" => $value3['TipoFactor']
            ));
        }
        //******* Impuestos Agrupados

    } else {
        if($SinExcentos =='0' or  $tipoDoc == 200 or  $tipoDoc ==12){
            $impuestos = $xml->createElement("cfdi:Impuestos");
            $impuestos = $root->appendChild($impuestos);
        }
        

        $total = ((number_format($totalimporte, $decimalplaces, '.', '')-number_format($descuentofactFin, $decimalplaces, '.', ''))+ number_format($impuestofact, $decimalplaces, '.', '')-abs($sum)) - number_format(abs($impuestoretenido), $decimalplaces, '.', '')-number_format(abs($TotalImpuestosLocales), $decimalplaces, '.', '');;
        
        $total = number_format($total, $decimalplaces, '.', ''); // foramto a dos

        if ($_SESSION ['UserID'] == 'desarrollo') {
           // echo '<br>subtotal2 :'.$totalimporte;     
        }
        if ($carpeta == 'Recibo' or $tipoDoc ==200 ) {
            $root->SetAttribute("SubTotal", number_format($totalimporte, 0, '.', ''));
            $root->SetAttribute("Total", 0);
        }else{
            $root->SetAttribute("SubTotal", number_format($totalimporte, $decimalplaces, '.', ''));
            $root->SetAttribute("Total", $total);     
        }
    }



    //if ($banderacomplemento == false  and ($carpeta == 'Recibo' or $carpeta == 'NCreditoDirect')) {
    if ($banderacomplemento == false ){
        $complemento = $xml->createElement("cfdi:Complemento");
        $banderacomplemento = true;
    }

    if ($banderacomplemento == true  and ($carpeta == 'Recibo' or $carpeta == 'NCreditoDirect') AND $tipoDoc != 13) {
        //echo "<br>complemento";
        //$complemento = $xml->createElement("cfdi:Complemento");
        $complemento = $root->appendChild($complemento);
        
        
    }

    if($crearcomercioexterior){
        //Asignar el nodo de comercio exterior
        cargaAtt($root, array(
            "xmlns:cce11" => "http://www.sat.gob.mx/ComercioExterior11",
            "xsi:schemaLocation" => "http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd http://www.sat.gob.mx/ComercioExterior11 http://www.sat.gob.mx/sitio_internet/cfd/ComercioExterior11/ComercioExterior11.xsd"
        ));
        $sqlsm = "SELECT NumRegIdTrib
                        FROM debtorsmaster
                        WHERE debtorno = '".$debtorno."'";
        $ErrMsg = _('El Sql que fallo fue');
        $DbgMsg = _('No se pudo obtener la clave del articulo '.trim($datos [2]));
        //echo "<br>".$sqlAnt."<br>";
        $resultsm= DB_query($sqlsm, $db, $ErrMsg, $DbgMsg, true);
        if ($_SESSION ['UserID'] == 'desarrollo') {
           // echo 'numreg: '.$sqlsm;
        }
        if (DB_num_rows($resultsm) == 1) {
            $rowsm = DB_fetch_array($resultsm);
            $NumRegIdTrib = $rowsm['NumRegIdTrib'];
        }
        $sqlDatos = 'SELECT valor
            FROM debtorcomplement
            INNER JOIN fieldcomplement ON fieldcomplement.fieldid=debtorcomplement.idcomplement
            WHERE xmlnode="Incoterm" AND debtorno="'.$orderno.'"';
        
        $rsDatos = DB_query($sqlDatos, $db);
        $incoterm="";
        if (DB_num_rows($rsDatos) == 1) {
            $rowsd = DB_fetch_array($rsDatos);
            $incoterm = $rowsd['valor'];
        }
        
        cargaAtt($receptor, array(
            "ResidenciaFiscal" => $paisreceptor,
            "NumRegIdTrib" => $NumRegIdTrib
        ));
        
        cargaAtt($ComercioExterior, array(
            "Version" => '1.1',
            "TipoOperacion" => "2",
            "ClaveDePedimento" => "A1",
            "CertificadoOrigen" => "0",
            "Incoterm" => $incoterm,
            "Subdivision" => "0",
            "TipoCambioUSD" => fnDecimalFormat($TipoCambio,2),
            "TotalUSD" => $total
        ));
        $ComercioExterior = $complemento->appendChild($ComercioExterior); 
        $complemento = $root->appendChild($complemento);
        //$banderacomplemento = true;
    }
    
    $vrcomplementoservices=fnComplementoServicesp($xml,$orderno,$db);
    if($vrcomplementoservices!=""){
        if ($banderacomplemento == false ){
            $complemento = $xml->createElement("cfdi:Complemento");
            $banderacomplemento = true;
        }
        $vrcomplementoservices = $complemento->appendChild($vrcomplementoservices); 
        $complemento = $root->appendChild($complemento);
    }
    
    if ($carpeta == 'Recibo' or $tipoDoc==200) {
        // Se debe eliminar nodo marca error
        //$conceptos->removeChild($impuestos);
        $root->removeChild($impuestos);
    }
    
    if (($carpeta == 'Recibo' or $carpeta == 'NCreditoDirect') AND $tipoDoc != 13) {

        //Agregar datos de los pagos
        $transid_allocto = "";  //Id de factura
        $FechaPago = "";
        $FormaDePagoP = "";
        $MonedaP = "";
        $Monto = "";
        $CtaOrdenante = "";
        $NumOperacion = "";
        $totalFactura = 0;
        $RfcEmisorCtaOrd = "XAXX010101000";
        $NomBancoOrdExt = "Nombre Banco";
        $decimalplaces = $_SESSION ['DecimalPlacesInvoice'];
        $totalPagoRegistrado = 0;
        $totalRegistros = 0;
        $diferencia = 0;
        $folioDocumento = '';
        $serieDocumento = '';
        $datosFolio ='';
        
        //Datos facturas apliacadas con el pago
        //if($_SESSION ['DatabaseName'] == 'erpjocapra' or $_SESSION ['DatabaseName'] == 'erpjocapra_CAPA' or $_SESSION ['DatabaseName'] == 'erpjocapra_DES' OR $_SESSION ['DatabaseName'] == 'erpgruposii' or $_SESSION ['DatabaseName'] == 'erpgruposii_CAPA' or $_SESSION ['DatabaseName'] == 'erpgruposii_DES' OR $_SESSION ['DatabaseName'] == 'erpmg' or $_SESSION ['DatabaseName'] == 'erpmg_CAPA' or $_SESSION ['DatabaseName'] == 'erpmg_DES' OR $_SESSION ['DatabaseName'] == 'erpcodigoqro' or $_SESSION ['DatabaseName'] == 'erpcodigoqro_CAPA' or $_SESSION ['DatabaseName'] == 'erpcodigoqro_DES' OR $_SESSION ['DatabaseName'] == 'erpplacacentro' or $_SESSION ['DatabaseName'] == 'erpplacacentro_CAPA'){
            $SQL = "SELECT 
                    sum(debtortrans.ovamount) as subtotalFactura, 
                    sum(debtortrans.ovgst) as ivaFactura, 
                    sum(debtortrans.ovamount + debtortrans.ovgst) as totalFactura, 
                    sum(debtortrans.alloc) as totalAplicado,
                    /*(debtortrans.ovamount + debtortrans.ovgst) - (TRUNCATE(debtortrans.alloc,2) - TRUNCATE(custallocns.amt,2)) as ImpSaldoAnt,
                    ROUND((debtortrans.ovamount + debtortrans.ovgst) - TRUNCATE(debtortrans.alloc,2),6) as ImpSaldoInsoluto, 
                    debtortrans.uuid as uuidFactura,
                    debtortrans.folio as folioFactura,
                    debtortrans.c_paymentid,
                    custallocns.rate_to,
                    custallocns.currcode_to,
                    TRUNCATE(custallocns.amt,2) AS amt,*/
                 SUM(ROUND((debtortrans.ovamount + debtortrans.ovgst),2) - (ROUNd(debtortrans.alloc,2) - Round(custallocns.amt,2))) as ImpSaldoAnt,
                 SUM(ROUND((debtortrans.ovamount + debtortrans.ovgst),2) - ROUND(debtortrans.alloc,2)) as ImpSaldoInsoluto, 
                 debtortrans.uuid as uuidFactura,
                 debtortrans.folio as folioFactura,
                 debtortrans.c_paymentid,
                 custallocns.rate_to,
                 custallocns.currcode_to, 
                 SUM(ROUND(custallocns.amt,2)) AS amt,

                    (SELECT COUNT(*) 
                        FROM custallocns par 
                        LEFT JOIN debtortrans ON debtortrans.id=par.transid_allocfrom
                        WHERE par.transid_allocto = custallocns.transid_allocto  AND par.amt>0
                        AND debtortrans.type IN(12,200)
                    ) AS numParcialidad,
                    CONCAT(custallocns.datealloc, 'T', '12:00:00') as FechaPago,
                    debtortransRecibo.ovamount as subtotalRecibo, 
                    debtortransRecibo.ovgst as ivaRecibo, 
                    ROUND( (debtortransRecibo.ovamount + debtortransRecibo.ovgst) - (debtortransRecibo.ovamount + debtortransRecibo.ovgst) * IFNULL(relacion_factoraje.porcentaje/100,0) ,2) as totalRecibo,
                    debtortransRecibo.CtaOrdenante, 
                    debtortransRecibo.uuid,
                    debtortransRecibo.NumOperacion,
                    debtortransRecibo.codesat as paymentid,
                    custallocns.rate_from,
                    custallocns.currcode_from,
                    custallocns.transid_allocto,
                    custbranch.nocuenta,
                    currencies.decimalplaces,
                    banks.bank_shortdescription,
                    banks.taxid,
                    debtortrans.type as tipoPagare,
                    debtortrans.id as idPagare,
                    banktrans_info.rfcbeneficiario,
                    banktrans_info.ctabeneficiario,
                    banktrans_info.c_TipoCadena,
                    banktrans_info.cadenaPago,
                    banktrans_info.selloPago,
                    banktrans_info.certificadoPago,
                    DATE_FORMAT(banktrans_info.fechaDeposito, '%Y-%m-%dT%H:%i:%s' ) AS FechaPagoR,
                   DATE(debtortransRecibo.origtrandate),
                   cambiorecibo.rate AS cambioAplicado,
                   debtortrans.alloc /((debtortrans.alloc/custallocns.rate_to) - diffonexch_alloc) as tipocambioDR

                FROM custallocns
                LEFT JOIN debtortrans ON debtortrans.id = custallocns.transid_allocto
                LEFT JOIN debtortrans debtortransRecibo ON debtortransRecibo.id = custallocns.transid_allocfrom
                LEFT JOIN paymentmethodssat ON paymentmethodssat.paymentname = debtortransRecibo.paymentname
                LEFT JOIN custbranch ON custbranch.debtorno = debtortransRecibo.debtorno and custbranch.branchcode = debtortransRecibo.branchcode
                LEFT JOIN currencies ON currencies.currabrev = custallocns.currcode_from
                LEFT JOIN banks ON banks.bank_id = debtortransRecibo.bank_id
                LEFT JOIN banktrans_info ON banktrans_info.transid=debtortransRecibo.transno AND banktrans_info.type=debtortransRecibo.type
                LEFT JOIN (
                    SELECT  DISTINCT tipocambio.rate, tipocambio.currency
                    FROM debtortrans
                    INNER JOIN tipocambio ON DATE(tipocambio.fecha)=DATE(debtortrans.origtrandate)
                    WHERE  id='".$iddocto."'
                ) AS cambiorecibo ON cambiorecibo.currency =custallocns.currcode_to
                WHERE 
                custallocns.transid_allocfrom = '".$iddocto."'";

                


                $SQL = "SELECT sum(debtortrans.ovamount) as subtotalFactura,
                sum(debtortrans.ovgst) as ivaFactura, sum(debtortrans.ovamount + debtortrans.ovgst) as totalFactura,
                sum(debtortrans.alloc) as totalAplicado,
                sum(ROUND((debtortrans.ovamount + debtortrans.ovgst),2) - (ROUNd(debtortrans.alloc,2) - Round(custallocns.amt,2))) as ImpSaldoAnt,
                sum(ROUND((debtortrans.ovamount + debtortrans.ovgst),2) - ROUND(debtortrans.alloc,2)) as ImpSaldoInsoluto, debtortrans.uuid as uuidFactura,
                debtortrans.folio as folioFactura, debtortrans.c_paymentid, custallocns.rate_to, custallocns.currcode_to,
                sum(ROUND((custallocns.amt) - (custallocns.amt * IFNULL(relacion_factoraje.porcentaje/100,0)),2)) AS amt,
                CASE WHEN debtortrans.type = 70
                THEN 
                 (SELECT   count( DISTINCT parci.transid_allocfrom ) 
                     FROM custallocns parci 
                     LEFT JOIN debtortrans deb1 ON deb1.id=parci.transid_allocfrom 
                     LEFT JOIN custallocns fac ON fac.transid_allocfrom = parci.transid_allocto
                     WHERE fac.transid_allocto =(SELECT transid_allocto FROM custallocns pag WHERE pag.transid_allocfrom=debtortrans.id  LIMIT 1)
                     
                      AND parci.amt>0 AND deb1.type IN(12,200))
                ELSE
                (SELECT COUNT(*) FROM custallocns par 
                LEFT JOIN debtortrans as deb1 ON deb1.id=par.transid_allocfrom WHERE par.transid_allocto = custallocns.transid_allocto AND par.amt>0 AND deb1.type IN(12,200) ) END
                 +
                (SELECT COUNT(*) 
                     FROM relacion_factoraje 
                     INNER JOIN custallocns c ON relacion_factoraje.iddebtortrans = c.transid_allocfrom
                     WHERE c.transid_allocto = custallocns.transid_allocto AND c.amt>0 AND c.transid_allocfrom<>'".$iddocto."')

                 AS numParcialidad,
                CONCAT(custallocns.datealloc, 'T', '12:00:00') as FechaPago, debtortransRecibo.ovamount as subtotalRecibo, debtortransRecibo.ovgst as ivaRecibo,
                 ROUND( (debtortransRecibo.ovamount + debtortransRecibo.ovgst) - (debtortransRecibo.ovamount + debtortransRecibo.ovgst) * IFNULL(relacion_factoraje.porcentaje/100,0) ,2) as totalRecibo,
                debtortransRecibo.CtaOrdenante, debtortransRecibo.uuid, debtortransRecibo.NumOperacion,
                debtortransRecibo.codesat as paymentid, custallocns.rate_from,
                custallocns.currcode_from,
                custallocns.transid_allocto,
                custbranch.nocuenta, currencies.decimalplaces,
                banks.bank_shortdescription,
                banks.taxid, debtortrans.type as tipoPagare,
                debtortrans.id as idPagare,
                banktrans_info.rfcbeneficiario,
                banktrans_info.ctabeneficiario,
                banktrans_info.c_TipoCadena,
                banktrans_info.cadenaPago,
                banktrans_info.selloPago,
                banktrans_info.certificadoPago,
                DATE_FORMAT(banktrans_info.fechaDeposito, '%Y-%m-%dT%H:%i:%s' ) AS FechaPagoR,
                DATE(debtortransRecibo.origtrandate), cambiorecibo.rate AS cambioAplicado,
                debtortrans.alloc /((debtortrans.alloc/custallocns.rate_to) - diffonexch_alloc) as tipocambioDR,
                1 as tipoPago,
                IF (relacion_factoraje.iddebtortrans is not null,1, 0) as factoraje,
                '0' AS importeOrigen,
                CASE WHEN debtortrans.type= 70 THEN
                (SELECT transid_allocto FROM custallocns pag WHERE pag.transid_allocfrom=debtortrans.id )
                ELSE
                debtortrans.id
                  END AS idfactura
                   
         FROM custallocns 
         LEFT JOIN debtortrans ON debtortrans.id = custallocns.transid_allocto
         LEFT JOIN  relacion_factoraje ON custallocns.transid_allocfrom = relacion_factoraje.`iddebtortrans`
         LEFT JOIN debtortrans debtortransRecibo ON debtortransRecibo.id = custallocns.transid_allocfrom 
         LEFT JOIN paymentmethodssat ON paymentmethodssat.paymentname = debtortransRecibo.paymentname 
         LEFT JOIN custbranch ON custbranch.debtorno = debtortransRecibo.debtorno and custbranch.branchcode = debtortransRecibo.branchcode 
         LEFT JOIN currencies ON currencies.currabrev = custallocns.currcode_from 
         LEFT JOIN banks ON banks.bank_id = debtortransRecibo.bank_id 
         LEFT JOIN banktrans_info ON banktrans_info.transid=debtortransRecibo.transno AND banktrans_info.type=debtortransRecibo.type 
         LEFT JOIN ( SELECT DISTINCT tipocambio.rate, tipocambio.currency 
            FROM debtortrans INNER JOIN tipocambio ON DATE(tipocambio.fecha)=DATE(debtortrans.origtrandate) WHERE id='".$iddocto."') AS cambiorecibo ON cambiorecibo.currency =custallocns.currcode_to 
         WHERE custallocns.transid_allocfrom = '".$iddocto."'


         GROUP BY idfactura
         UNION ALL
         
         SELECT debtortrans.ovamount as subtotalFactura,
            debtortrans.ovgst as ivaFactura, (debtortrans.ovamount + debtortrans.ovgst) as totalFactura,
            debtortrans.alloc as totalAplicado,
            ROUND((debtortrans.ovamount + debtortrans.ovgst),2) - (ROUNd(debtortrans.alloc,2) - Round(custallocns.amt,2)) as ImpSaldoAnt,
            ROUND((debtortrans.ovamount + debtortrans.ovgst),2) - ROUND(debtortrans.alloc,2) as ImpSaldoInsoluto,
            debtortrans.uuid as uuidFactura, debtortrans.folio as folioFactura,
            debtortrans.c_paymentid, custallocns.rate_to, custallocns.currcode_to,
            round((custallocns.amt * IFNULL(relacion_factoraje.porcentaje/100,0)),2) as amt,
            CASE WHEN debtortrans.type = 70
                THEN 
                 (SELECT   count( DISTINCT parci.transid_allocfrom ) 
                     FROM custallocns parci 
                     LEFT JOIN debtortrans deb1 ON deb1.id=parci.transid_allocfrom 
                     LEFT JOIN custallocns fac ON fac.transid_allocfrom = parci.transid_allocto
                     WHERE fac.transid_allocto =(SELECT transid_allocto FROM custallocns pag WHERE pag.transid_allocfrom=debtortrans.id  LIMIT 1)
                     
                      AND parci.amt>0 AND deb1.type IN(12,200))
                ELSE
                (SELECT COUNT(*) FROM custallocns par 
                LEFT JOIN debtortrans as deb1 ON deb1.id=par.transid_allocfrom WHERE par.transid_allocto = custallocns.transid_allocto AND par.amt>0 AND deb1.type IN(12,200) ) END
                +
            (SELECT COUNT(*) 
                     FROM relacion_factoraje 
                     INNER JOIN custallocns c ON relacion_factoraje.iddebtortrans = c.transid_allocfrom
                     WHERE c.transid_allocto = custallocns.transid_allocto AND c.amt>0)  AS numParcialidad,
            CONCAT(custallocns.datealloc, 'T', '12:00:00') as FechaPago, debtortransRecibo.ovamount as subtotalRecibo,
            debtortransRecibo.ovgst as ivaRecibo,

             
             ROUND((debtortransRecibo.ovamount + debtortransRecibo.ovgst) * IFNULL(relacion_factoraje.porcentaje/100,0) ,2) as totalRecibo,
            debtortransRecibo.CtaOrdenante,
            debtortransRecibo.uuid,
            debtortransRecibo.NumOperacion,
            paymentmethodssat.paymentid as paymentid,
            custallocns.rate_from,
            custallocns.currcode_from,
            custallocns.transid_allocto,
            custbranch.nocuenta,
            currencies.decimalplaces,
            banks.bank_shortdescription,
            banks.taxid,
            debtortrans.type as tipoPagare,
            debtortrans.id as idPagare,
            banktrans_info.rfcbeneficiario,
            banktrans_info.ctabeneficiario,
            banktrans_info.c_TipoCadena,
            banktrans_info.cadenaPago,
            banktrans_info.selloPago,
            banktrans_info.certificadoPago,
            DATE_FORMAT(banktrans_info.fechaDeposito, '%Y-%m-%dT%H:%i:%s' ) AS FechaPagoR,
            DATE(debtortransRecibo.origtrandate),
            cambiorecibo.rate AS cambioAplicado,
            debtortrans.alloc /((debtortrans.alloc/custallocns.rate_to) - diffonexch_alloc) as tipocambioDR,
            2 as tipoPago,
            IF (relacion_factoraje.iddebtortrans is not null,1, 0) as factoraje,
           /* ROUND(custallocns.amt,2) - (custallocns.amt * IFNULL(relacion_factoraje.porcentaje/100,0)) AS importeOrigen*/
            ROUND(custallocns.amt - (custallocns.amt * IFNULL(relacion_factoraje.porcentaje/100,0)),2) AS importeOrigen,

            CASE WHEN debtortrans.type= 70 THEN
                (SELECT transid_allocto FROM custallocns pag WHERE pag.transid_allocfrom=debtortrans.id )
                ELSE
                debtortrans.id
                  END AS idfactura
         FROM custallocns
         INNER JOIN  relacion_factoraje ON custallocns.transid_allocfrom = relacion_factoraje.`iddebtortrans`
         LEFT JOIN debtortrans ON debtortrans.id = custallocns.transid_allocto
         LEFT JOIN debtortrans debtortransRecibo ON debtortransRecibo.id = custallocns.transid_allocfrom 
         LEFT JOIN paymentmethodssat ON paymentmethodssat.paymentid= '17' 
         LEFT JOIN custbranch ON custbranch.debtorno = debtortransRecibo.debtorno and custbranch.branchcode = debtortransRecibo.branchcode 
         LEFT JOIN currencies ON currencies.currabrev = custallocns.currcode_from 
         LEFT JOIN banks ON banks.bank_id = debtortransRecibo.bank_id 
         LEFT JOIN banktrans_info ON banktrans_info.transid=debtortransRecibo.transno AND banktrans_info.type=debtortransRecibo.type 
         LEFT JOIN ( SELECT DISTINCT tipocambio.rate, tipocambio.currency 
         FROM debtortrans INNER JOIN tipocambio ON DATE(tipocambio.fecha)=DATE(debtortrans.origtrandate) WHERE id='".$iddocto."' ) AS cambiorecibo ON cambiorecibo.currency =custallocns.currcode_to 
         WHERE custallocns.transid_allocfrom = '".$iddocto."'


         GROUP BY idfactura

         ";

                

       /* }else{
            $SQL = "SELECT 
                debtortrans.ovamount as subtotalFactura, 
                debtortrans.ovgst as ivaFactura, 
                (debtortrans.ovamount + debtortrans.ovgst) as totalFactura, 
                debtortrans.alloc as totalAplicado,
                (debtortrans.ovamount + debtortrans.ovgst) - (debtortrans.alloc - custallocns.amt) as ImpSaldoAnt,
                (debtortrans.ovamount + debtortrans.ovgst) - (debtortrans.alloc) as ImpSaldoInsoluto, 
                debtortrans.uuid as uuidFactura,
                debtortrans.folio as folioFactura,
                debtortrans.c_paymentid,
                custallocns.rate_to,
                custallocns.currcode_to,
                custallocns.amt,
                (SELECT COUNT(*) 
                    FROM custallocns par 
                    LEFT JOIN debtortrans ON debtortrans.id=par.transid_allocfrom
                    WHERE par.transid_allocto = custallocns.transid_allocto  AND par.amt>0
                    AND debtortrans.type IN(12,200)) as numParcialidad,
                CONCAT(custallocns.datealloc, 'T', '12:00:00') as FechaPagoR,
                debtortransRecibo.ovamount as subtotalRecibo, 
                debtortransRecibo.ovgst as ivaRecibo, 
                debtortransRecibo.ovamount + debtortransRecibo.ovgst as totalRecibo, 
                debtortransRecibo.CtaOrdenante, 
                debtortransRecibo.uuid,
                debtortransRecibo.NumOperacion,
                debtortransRecibo.codesat as paymentid,
                custallocns.rate_from,
                custallocns.currcode_from,
                custallocns.transid_allocto,
                custallocns.amt,
                custbranch.nocuenta,
                currencies.decimalplaces,
                banks.bank_shortdescription,
                banks.taxid,
                debtortrans.type as tipoPagare,
                debtortrans.id as idPagare,
                banktrans_info.rfcbeneficiario,
                banktrans_info.ctabeneficiario,
                DATE_FORMAT(banktrans_info.fechaDeposito, '%Y-%m-%dT%H:%i:%s' ) AS FechaPagoR2
                FROM custallocns
                LEFT JOIN debtortrans ON debtortrans.id = custallocns.transid_allocto
                LEFT JOIN debtortrans debtortransRecibo ON debtortransRecibo.id = custallocns.transid_allocfrom
                LEFT JOIN paymentmethodssat ON paymentmethodssat.paymentname = debtortransRecibo.paymentname
                LEFT JOIN custbranch ON custbranch.debtorno = debtortransRecibo.debtorno and custbranch.branchcode = debtortransRecibo.branchcode
                LEFT JOIN currencies ON currencies.currabrev = custallocns.currcode_from
                LEFT JOIN banks ON banks.bank_id = debtortransRecibo.bank_id
                LEFT JOIN banktrans_info ON banktrans_info.transid=debtortransRecibo.transno AND banktrans_info.type=debtortransRecibo.type
                WHERE 
                custallocns.transid_allocfrom = '".$iddocto."'";
        }*/

        if($_SESSION['UserID'] == 'desarrollo'){
            echo  "<br><pre> ".$SQL;

        }
        $result = DB_query($SQL, $db);
        $resultFac =  DB_query($SQL, $db);
        $tipoPago = "";
        if (DB_num_rows($result) > 0) {

            
            //$totalRegistros = DB_num_rows($result);
            $Pagos = $xml->createElement("pago10:Pagos");
                    $Pagos = $complemento->appendChild($Pagos);

                    cargaAtt($Pagos, array(
                        "Version" => "1.0"
                    ));
            $totalRows = DB_num_rows($result);
            while ( $row = DB_fetch_array($result) ) {
                if($row['factoraje'] == 1){
                    $totalRegistros = $totalRows /2;
                }else{
                    $totalRegistros = $totalRows;
                }
                

                if($row['tipoPago']!=$tipoPago){
                    $tipoPago = $row['tipoPago'];
                    
                    
                    $Pago = $xml->createElement("pago10:Pago");
                    $Pago = $Pagos->appendChild($Pago);
                    $totalPagoRegistrado = $row['totalRecibo'];
                    $numRegistros = 1;
                    $vrTotalMonto = 0;
                    $MonedaDR = "";
                }
                
                $MonedaP = $row['currcode_from'];
                $MonedaDR = $row['currcode_to'];



                //Cuando se aplica un recibo a un pagare , obtenemos la factura relacionada con el pagare.
                if($row['tipoPagare'] == '70'){
                    $SQL = "SELECT custallocns.currcode_to,
                        debtortrans.uuid as uuidFactura,
                        debtortrans.folio as folioFactura
                        FROM custallocns
                        LEFT JOIN debtortrans ON debtortrans.id = custallocns.transid_allocto
                        WHERE 
                        custallocns.transid_allocfrom = '".$row['idPagare']."'";
                        //echo "<pre>".$SQL;
                        $result2 = DB_query($SQL, $db);
                        $row2 = DB_fetch_array($result2) ;
                        //echo "<br>sdads: ".$row2['uuidFactura'];
                        $row['uuidFactura'] =$row2['uuidFactura'];
                        $row['folioFactura'] = $row2['folioFactura'];
                        $MonedaDR = $row2['currcode_to'];
                }
                
                if ($numRegistros == 1) {

                    if(empty($row['paymentid']) or $row['paymentid']==""){
                        $row['paymentid'] = '01';
                    }
                    $transid_allocto = $row['transid_allocto'];
                    $FechaPago = $row['FechaPagoR'];
                    $FormaDePagoP = $row['paymentid'];
                    $Monto = $row['amt'];
                    //$CtaOrdenante = $row['nocuenta'];
                    $totalFactura = $row['totalFactura'];
                    $decimalplaces = $row['decimalplaces'];
                    $RfcEmisorCtaOrd = $row['taxid'];
                    if($RfcEmisorCtaOrd  == 'XEXX010101000'){
                        
                        $NomBancoOrdExt = $row['bank_shortdescription'];
                    }else{
                        $NomBancoOrdExt = '';
                    }
                    $CtaOrdenante = $row['CtaOrdenante'];
                    $NumOperacion = $row['NumOperacion'];
                    $RfcEmisorCtaBen=$row['rfcbeneficiario'];
                    $CtaBeneficiario=$row['ctabeneficiario'];
                    $c_TipoCadena=$row['c_TipoCadena'];
                    $cadenaPago=$row['cadenaPago'];
                    $selloPago=$row['selloPago'];
                    $certificadoPago=$row['certificadoPago'];

                    $TipoCambioP = ($row['currcode_from'] != "MXN" ? (1/$row['rate_from']) : "1");
                    if($FormaDePagoP=="02" OR $FormaDePagoP=="03" OR $FormaDePagoP=="04" OR $FormaDePagoP=="05" OR $FormaDePagoP=="06" OR $FormaDePagoP=="28" OR $FormaDePagoP=="29"){
                        //$patronRFC='/[A-Z&Ñ]{3,4}[0-9]{2}(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])[A-Z0-9]{2}[0-9A]/';
                        //$patronncuenta='/[0-9]{10,18}/';
    
                        cargaAtt($Pago, array(
                            "FechaPago" => $FechaPago,
                            "FormaDePagoP" => $FormaDePagoP,
                            "MonedaP" => $MonedaP,
                            "NumOperacion" => $NumOperacion
                        ));
                        if(!empty($RfcEmisorCtaOrd)){
                            cargaAtt($Pago, array(
                                "RfcEmisorCtaOrd" =>$RfcEmisorCtaOrd 
                            ));
                        }
                        if(!empty($NomBancoOrdExt)){
                            cargaAtt($Pago, array(
                                "NomBancoOrdExt" =>$NomBancoOrdExt 
                            ));
                        }
                        if(!empty($CtaOrdenante)){
                            cargaAtt($Pago, array(
                                "CtaOrdenante" =>$CtaOrdenante 
                            ));
                        }
                        if(!empty($c_TipoCadena)){
                            cargaAtt($Pago, array(
                                "TipoCadPago" =>$c_TipoCadena 
                            ));
                        }
                        if(!empty($cadenaPago)){
                            cargaAtt($Pago, array(
                                "CadPago" =>$cadenaPago 
                            ));
                        }
                        if(!empty($selloPago)){
                            cargaAtt($Pago, array(
                                "SelloPago" =>$selloPago 
                            ));
                        }
                        if(!empty($certificadoPago)){
                            cargaAtt($Pago, array(
                                "CertPago" =>$certificadoPago 
                            ));
                        }
                        if(!empty($RfcEmisorCtaBen)){
                            cargaAtt($Pago, array(
                                "RfcEmisorCtaBen" =>$RfcEmisorCtaBen 
                            ));
                        }
                        if(!empty($CtaBeneficiario)){
                            cargaAtt($Pago, array(
                                "CtaBeneficiario" =>$CtaBeneficiario 
                            ));
                        }
                    }else{
                        cargaAtt($Pago, array(
                            "FechaPago" => $FechaPago,
                            "FormaDePagoP" => $FormaDePagoP,
                            "MonedaP" => $MonedaP,
                            "NumOperacion" => $NumOperacion
                        ));   
                    }
                    
                    if ($MonedaP != "MXN") {
                    // if ($MonedaP!= $row['currcode_to']) {
                        // Si las monedas con diferentes poner tipo de cambio
                        cargaAtt($Pago, array(
                            "TipoCambioP" => number_format(abs($TipoCambioP), 4, '.', '') //$TipoCambioP,
                        ));
                    }
                }

                $NumParcialidad = $row['numParcialidad'];

                $decimalplaces = $row['decimalplaces'];

                $DoctoRelacionado = $xml->createElement("pago10:DoctoRelacionado");
                $DoctoRelacionado = $Pago->appendChild($DoctoRelacionado);

                $ImpSaldoAnt =  $row['ImpSaldoAnt'];
                $ImpPagado = $row['amt'];
                $ImpSaldoInsoluto = $row['ImpSaldoInsoluto'];

                if($tipoPago == 2){
                    $ImpSaldoAnt =  fnDecimalFormat(exp_to_dec(abs($row['ImpSaldoAnt'])),2) - fnDecimalFormat(exp_to_dec(abs($row['importeOrigen'])),2);

                }

                //echo "<br> saldo IN".$ImpSaldoInsoluto;
                
                $TipoCambioDR = ($row['currcode_to'] != $row['currcode_from'] ? ($row['tipocambioDR']) : "");

                if (empty($row['c_paymentid'])) {
                    // Si va vacio es factura 3.2
                    $row['c_paymentid'] = 'PPD';
                }
                $ImpSaldoInsoluto = $ImpSaldoAnt - $ImpPagado;
                if($ImpSaldoAnt < $ImpPagado){
                    $ImpSaldoAnt = $ImpPagado;
                }

                
                $ImpSaldoAnt=fnDecimalFormat(exp_to_dec(abs($ImpSaldoAnt)),2);
                $ImpPagado = fnDecimalFormat(exp_to_dec(abs($ImpPagado)),2);
                //$ImpSaldoInsoluto = fnDecimalFormat(exp_to_dec(abs($ImpSaldoInsoluto)),2);
                
                //echo "<br> SALDO IN:".(Round($ImpSaldoAnt - $ImpPagado,4));
                $ImpSaldoInsoluto = fnDecimalFormat(exp_to_dec(abs( Round($ImpSaldoAnt - $ImpPagado,2))),2);

                if($MonedaP != $row['currcode_to']){
                    $vrCambioT = ($row['currcode_to'] != "MXN" ? (1/$row['cambioAplicado']) : "1");
                    $vrTotalMonto +=abs($ImpPagado) / $row['tipocambioDR'];
                }else{
                    $vrTotalMonto +=abs($ImpPagado);
                }

                if($row['currcode_to'] == $row['currcode_from']){
                
                    if($totalRegistros == $numRegistros){
                        // validar si existe diferencia entre el total de recibo
                        // Si existe diferencia se realizará el ajuste para que salga como se registro en el sistema en el ultimo documento relacionado
                        $diferencia = Round(abs($totalPagoRegistrado) - abs($vrTotalMonto),2); 
                        //echo "<br> diferencia:".$diferencia;
                        $vrTotalMonto = $totalPagoRegistrado;
                        if($MonedaP == $row['currcode_to']){

                            $ImpPagado = $ImpPagado+ $diferencia; 
                            //echo "<br> pagado:".$ImpPagado;         
                            
                            if( abs($ImpSaldoAnt) < abs($ImpPagado)){
                                $ImpSaldoAnt = $ImpPagado;
                            }
                            // echo "<br> insoluto:".$ImpSaldoInsoluto; 
                            // echo "<br> Pagado:".$ImpPagado; 

                            $ImpSaldoInsoluto = $ImpSaldoAnt - $ImpPagado;

                            // echo "<br> INSOL:".$ImpSaldoInsoluto; 
                            // echo "<br> PAG:".$ImpPagado;
                            // echo "<br> ANT:".$ImpSaldoAnt;

                            $ImpSaldoAnt=number_format(abs($ImpSaldoAnt), $decimalplaces, '.', ''); //fnDecimalFormat(exp_to_dec(abs($ImpSaldoAnt)),2);
                            $ImpPagado = number_format(abs($ImpPagado), $decimalplaces, '.', ''); //fnDecimalFormat(exp_to_dec(abs($ImpPagado)),2);
                            $ImpSaldoInsoluto =  number_format(abs($ImpSaldoInsoluto), $decimalplaces, '.', '');// fnDecimalFormat(exp_to_dec(abs()),2);
                        }else{

                            //echo "<br> entra:";
                        }

                        //echo "<br> total Moto: ".$vrTotalMonto;
                        //echo "<br> Pagado:".$ImpSaldoInsoluto; 
                        
                    }
                }else{
                     $vrTotalMonto = $totalPagoRegistrado;

                     //echo "<br> total Moto: ".$vrTotalMonto;
                }
                // obtener serie y folio de facturas
                $datosFolio = explode("|", $row['folioFactura']);
                $serieDocumento = $datosFolio[0];
                $folioDocumento = $datosFolio[1];
                cargaAtt($DoctoRelacionado, array(
                    "IdDocumento" => $row['uuidFactura'],
                    "Serie" => $serieDocumento,
                    "Folio" => $folioDocumento,
                    "MonedaDR" => $MonedaDR,
                    
                    "MetodoDePagoDR" => $row['c_paymentid'],
                    "NumParcialidad" => $NumParcialidad,
                    //"ImpSaldoAnt" => number_format(abs($ImpSaldoAnt), $decimalplaces, '.', ''), //$ImpSaldoAnt,
                   // "ImpPagado" => number_format(abs($ImpPagado), $decimalplaces, '.', ''), //$ImpPagado,
                   // "ImpSaldoInsoluto" => number_format(abs($ImpSaldoInsoluto), $decimalplaces, '.', '') //$ImpSaldoInsoluto
                    "ImpSaldoAnt" => $ImpSaldoAnt,
                    "ImpPagado" => $ImpPagado,
                    "ImpSaldoInsoluto" => $ImpSaldoInsoluto
                ));

               // echo "<br> Moneda doc: ".$row['currcode_from']. " Monedad doc ".$row['currcode_from'];
                // Se agregó validación para que se agregué el tipo de cambio siempre y cuando la moneda del pago sea diferente  al del documento relacionado
                //if ($row['currcode_to'] != "MXN") {
                //$row['currcode_to'] = 'MXN';
                if ($row['currcode_to'] != $row['currcode_from']) {
                    // Si las monedas con diferentes poner tipo de cambio
                    //echo "<br> tOTAl".$vrTotalMonto." dads .".Round($ImpPagado / $TipoCambioDR,4)." Pagado: ".$ImpPagado." TipoCambioDR: ".$TipoCambioDR;

                    
                    while(abs($vrTotalMonto)<abs(Round($ImpPagado / round($TipoCambioDR,6),2))){
                        //echo "total monto:  ".$TipoCambioDR;
                        $TipoCambioDR = $TipoCambioDR +(0.000001);
                    }
                    cargaAtt($DoctoRelacionado, array(
                        "TipoCambioDR" => number_format(abs($TipoCambioDR), 6, '.', '') //$TipoCambioDR,
                    ));
                }

                //echo "<br> total monto;".$vrTotalMonto;

                if($totalRegistros == $numRegistros){
                    $Pago->SetAttribute("Monto",number_format(abs($vrTotalMonto), $decimalplaces, '.', ''));
                }/*else{

                    echo "SFsdsdf";
                }*/
                    

                $numRegistros ++;
                
            }
            
            
        }
    }


    
    //  for ($cad = 0; $cad <= count($arraycadena) - 1; $cad ++) {
    $cadenaINE = "";
    if ($cad >= 4 and $datos [0] == '10') {
        /*ini_set('display_errors', 1);
        ini_set('log_errors', 1);
        ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
        error_reporting(E_ALL);
        */
        /*
        if($datos [1]==1){
            $TipoProceso = 'Ordinario';
        }elseif($datos [1]==2){
            $TipoProceso = utf8_decode('Precampaña');
        }elseif($datos [1]==3){
            $TipoProceso = utf8_decode('Campaña') ;
        }*/
            
        $TipoProceso = utf8_decode($datos [1]);
        $TipoProceso =  str_replace(chr(63),chr(241), $TipoProceso);
        $tipocomite = $datos [2];
        /*
        if($datos [6]==1){
            $ambito = "Federal";
        }elseif($datos [6]==2){
            $ambito = "Local";
        }elseif($datos [6]==3){
            $ambito = utf8_decode('Campaña') ;
        }*/
         
        $ambito = $datos [6];
        // if ($banderacomplemento == false) {
        //     // $complemento = $xml->createElement("cfdi:Complemento");
        //     $complemento = $root->appendChild($complemento);
        //     $banderacomplemento = true;
        // }
        $ine = $xml->createElement("ine:INE");
        $ine = $complemento->appendChild($ine);
        cargaAtt($ine, array(
            "xmlns:ine" => "http://www.sat.gob.mx/ine",
            "Version" => "1.1",
            "TipoProceso" =>$TipoProceso,
            "TipoComite" => $tipocomite
        ));
        $complementoINEentidad = $xml->createElement("ine:Entidad");
        $complementoINEentidad = $ine->appendChild($complementoINEentidad);
        cargaAtt($complementoINEentidad, array(
            "ClaveEntidad" => $datos [3],
            "Ambito"=>$ambito
        ));
        /*
        $complementoINEentidadConta = $xml->createElement("ine:Contabilidad");
        $complementoINEentidadConta = $complementoINEentidad->appendChild($complementoINEentidadConta);
        cargaAtt($complementoINEentidadConta, array(
            "IdContabilidad" => $datos [5]
            
        ));
        */
        if ($ambito != ""){
            $ambito = "|" . $ambito;
        }else{
            $ambito = "";
        }
        
        if($datos [4] != ""){
            $complementoINEentidadConta = $xml->createElement("ine:Contabilidad");
            $complementoINEentidadConta = $complementoINEentidad->appendChild($complementoINEentidadConta);
            cargaAtt($complementoINEentidadConta, array(
                "IdContabilidad" => $datos [4]
                
            ));
            $cadenaINE.= '|1.1|' . ($TipoProceso) . '|' . $tipocomite . '|' . ($datos [3]) . ($ambito) . '|' . ($datos [4]);
        }else{
            $cadenaINE.= '|1.1|' . ($TipoProceso) . '|' . $tipocomite . '|' . ($datos [3]) . ($ambito);
        }
        
        if ($_SESSION ['UserID'] == 'saplicaciones' ) {
            //echo "<br>1:" . $cadenaINE;
        }
    }

    // $banderaimpuestoslocales = true;
    if ($banderaimpuestoslocales == true) {

        cargaAtt($impLocal, array(
            "xmlns:implocal" => "http://www.sat.gob.mx/implocal",
            "version" => "1.0",
            "TotaldeRetenciones" => number_format(abs($tRet), $decimalplaces, '.', ''),
            "TotaldeTraslados" => number_format(abs($tTras), $decimalplaces, '.', ''),
            "xsi:schemaLocation" => "http://www.sat.gob.mx/implocal http://www.sat.gob.mx/sitio_internet/cfd/implocal/implocal.xsd"
        ));

        $cadimp = utf8_encode("|1.0|" . number_format(abs($tRet), $decimalplaces, '.', '') . "|" . number_format(abs($tTras), $decimalplaces, '.', '') . trim($cadimp));
    }
    $complemento = $root->appendChild($complemento);
    // echo 'addenda pemex:'.htmlentities($addenda);

    $cadenaINE = mb_convert_encoding($cadenaINE, "UTF-8", "ISO-8859-1");
    
    // agregado porque la cdena original debe ser codificada en utf8 segun anexo 20 del sat**
    $cadenasellarx = $cadenasellar;
    // inicializa y termina la cadena original con el doble ||
    
    // Se agrega generacion de Cadena usando el XSLT
    $xmlcadena = $xml->saveXML();
    
    $cadenasellar = generarCadena($xmlcadena);

    if ($_SESSION ['UserID'] == 'desarrollo' ) {
       //echo "<br>cadenasellar:" . htmlentities($cadenasellar);
    }
    // echo "\n cadenasellarx: \n".$cadenasellarx;
    // echo "\n cadenasellar: \n".$cadenasellar;
    // exit();

    if ($_SESSION ['DatabaseName'] == 'erpmservice' or $_SESSION ['DatabaseName'] == 'erpmservice_CAPA' or $_SESSION ['DatabaseName'] == 'erpmservice_DES') {
        $aprobaxfolio = TraeAprobacionxFolio($rfccliente, $serie, $folio, $db);
        $aprobacionfolios = explode('|', $aprobaxfolio);
        $certificado = $aprobacionfolios [0];
        $Noaprobacion = $aprobacionfolios [1];
        $anioAprobacion = $aprobacionfolios [2];
    } else {
        $certificado = $myrowtags ['FileSAT'];
    }
    
    $crypttext="";
    $maquina = trim(`uname -n`);
    //$ruta = "/var/www/html" . dirname($_SERVER ['PHP_SELF']) . "/companies/" . $_SESSION ['DatabaseName'] . "/SAT/" . str_replace('.', '', str_replace(' ', '', $legalname)) . "/";
    $arrpath = explode('/', dirname($_SERVER ['PHP_SELF']));
    $rootdirectory = $arrpath[1];
    /*if($_SESSION['DatabaseName']=='erpmatelpuente' || $_SESSION['DatabaseName']=='erpmatelpuente_CAPA' || $_SESSION['DatabaseName']=='erpmatelpuente_DES' ){
        if($_SESSION['UserID']== 'desarrollo'){
            echo "<br> legal: ".$legaid;
        }
        $legalname = str_replace("&ntilde;", "n", $legalname);
        $legalname = str_replace("Ã±", "n", $legalname);
        if($legaid == 4){
           $legalname = "JuanaOrdunaAguilar";
        }
        //$legalname = str_replace("Ñ", "n", $legalname);
        if($_SESSION['UserID']== 'desarrollo'){
            echo "<br> prueba: ".$legalname;
        }
        
    }*/
    
    $ruta = "/var/www/html" . "/" . $rootdirectory . "/companies/" . $_SESSION ['DatabaseName'] . "/SAT/" . str_replace(',', '', str_replace('.', '', str_replace(' ', '', $legalname))) . "/";
    $file = $ruta . $certificado . ".key.pem"; // Ruta al archivo
    
    $file = utf8_encode($file);

    if (file_exists($file)) {
        $pkeyid = openssl_get_privatekey(file_get_contents($file));
        // openssl_sign($cadenasellar, $crypttext, $pkeyid, OPENSSL_ALGO_SHA256);
        openssl_sign($cadenasellar, $crypttext, $pkeyid, "sha256");
        openssl_free_key($pkeyid);
    }else{
        $pkeyid = "";
    }
    
    $sello = base64_encode($crypttext); // lo codifica en formato base64

    $root->setAttribute("Sello", $sello);
     
    $file = $ruta . $certificado . ".cer.pem"; // Ruta al archivo
    $file = utf8_encode($file);
    $certificado = "";
    $carga = false;
        
    if (file_exists($file)) {
        $datos = file($file);
        
        for ($i = 0; $i < sizeof($datos); $i ++) {
            if (strstr($datos [$i], "END CERTIFICATE")){
                $carga = false;
            }
            if ($carga){
                $certificado .= trim($datos [$i]);
            }
            if (strstr($datos [$i], "BEGIN CERTIFICATE")){
                $carga = true;
            }
        }
    }

    
    $root->setAttribute("Certificado", $certificado);

    
    if($complemento->hasChildNodes() === true){
        // Se elimina el complento en caso de que no tenga nodos 
        
    }else{
        $root->removeChild($complemento);
    }
    $xml->formatOutput = true;
    $todo = $xml->saveXML();

    $dir = "/var/www/html/" . dirname($_SERVER ['PHP_SELF']) . "/companies/" . $_SESSION ['DatabaseName'] . "/SAT/" . str_replace('.', '', str_replace(' ', '', $legalname)) . "/XML/" . $carpeta . "/";
    // echo "dir: " . $dir . "<br/>";
    if ($dir != "/dev/null") {
        // $xml->formatOutput = true;
        // $xml->save($dir.$nufa.".xml");
    } else {
        $paso = $todo;
        $conn->replace("cfdsello", array(
            "selldocu" => $nufa,
            "sellcade" => $cadena_original,
            "sellxml" => $paso
                ), "selldocu", true);
    }
    // guardamos la cadena y sello en la base de datos
    $sql = "UPDATE debtortrans
            SET sello='" . $sello . "',
                cadena='" . utf8_decode( addslashes($cadenasellar) ) . "'
            WHERE id=" . $iddocto;
    //echo "<br>function generaXMLCFDI final: ".$cadenasellar." <br>";
    $ErrMsg = _('El Sql que fallo fue');
    $DbgMsg = _('No se pudo actualizar el sello y cadena del documento');
    $Result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
    //echo '<pre>XML '.__FILE__.'---'.__LINE__;
    //
    

        /*
        
         */
               
     
    if ($_SESSION ['UserID'] == 'saplicaciones' || $_SESSION ['UserID'] == 'desarrollo') {
        echo "<br>algo generaXMLCFDI3_3: \n" .  htmlentities($todo)."\n";
    }
    $addendaXML = "";

    $array ["xml"] = "$todo";
    $array ["cadenaOriginal"] = "$cadenasellar";
    $array ["cantidadLetra"] = $cantidadletra;
    $array ["xmladdenda"] = "$addendaXML";
    // return($todo);
    
    //exit();
    
    return $array;


}
/* 
    Función fnComplementoServicesp agrega complemento Servicios parciales si existe la información la bd mhp
*/
function fnComplementoServicesp($xml, $orderno, $db) {
    $NumPerLicoAut="";
    $servicioparcial="";
    $servicioparcialinmueble="";
    
    $SQL = "SELECT debtorcomplement.idcomplement, fieldcomplement.xmlnode, debtorcomplement.valor
            FROM debtorcomplement
            INNER JOIN fieldcomplement  ON fieldcomplement.fieldid=debtorcomplement.idcomplement
            WHERE fieldcomplement.idcomplement=1 AND debtorno= '" . $orderno . "'";

    $Result = DB_query($SQL, $db);
    
    if(DB_num_rows($Result)>0){
        $servicioparcial = $xml->createElement("servicioparcial:parcialesconstruccion");

        $servicioparcialinmueble = $xml->createElement("servicioparcial:Inmueble");
        while($myrowpag = DB_fetch_array($Result)){
            $idcomplement = $myrowpag ['id'];
            $complementfile = $myrowpag ['complementfile'];
            $valorNodo=utf8_encode($myrowpag['valor']);
            
            if(trim($valorNodo)!=""){
                switch ($myrowpag['xmlnode']) {
                    case 'NumPerLicoAut':
                        $NumPerLicoAut=$valorNodo;
                        break;
                    case 'Calle':
                    case 'NoExterior':
                    case 'NoInterior':
                    case 'Colonia':
                    case 'Localidad':
                    case 'Referencia':
                    case 'Municipio':
                    case 'Estado':
                    case 'CodigoPostal':
                        $servicioparcialinmueble->setAttribute($myrowpag['xmlnode'], $valorNodo);
                        break;
                }
            }
        }
        carga_att($servicioparcial, array(
            "xmlns:servicioparcial" => "http://www.sat.gob.mx/servicioparcialconstruccion",
            "Version" => "1.0",
            "NumPerLicoAut" => $NumPerLicoAut,
        ));
        $servicioparcial->appendChild($servicioparcialinmueble);
    }
    return $servicioparcial;
}
/*
    Funcion generaXMLCFDI_Impresion para agregar los nodos faltantes a la impresion
    La verison 3.3 no cuenta con esos nodos por lo tanto se debe agregar

    redrogo
*/
function generaXMLCFDI_Impresion($cadena_original, $XMLElectronico, $tagref, $db, $type=0){
    // **************************** //
    // No dejar echo en la funcion //
    // Afecta al punto de venta   //
    // **************************** //
    global $xml, $cadena;
    $cadena = str_replace(chr(13) . chr(10) . '0', '@%', $cadena_original);

    $arraycadena = array();
    $arraycadena = explode('@%', $cadena);

    $calle = "";
    $noExterior = "";
    $colonia = "";
    $empresa = "";
    $municipio = "";
    $estado = "";
    $pais = "MEXICO";
    $cp = "";
    $calleexpedido = "";
    $noExteriorexpedido = "";
    $coloniaexpedido = "";
    $tagname = "";
    $municipioexpedido = "";
    $estadoexpedido = "";
    $paisexpedido = "";
    $codigoPostalExpedido = "";
    $regimenfiscal = "";
    $decimalesPartidas = 2;
    if($_SESSION['DatabaseName'] =='erpmatelpuente' OR $_SESSION['DatabaseName'] =='erpmatelpuente_CAPA' OR $_SESSION['DatabaseName'] =='erpmatelpuente_DES'){
        $decimalesPartidas = 6;
    }

    $agregarNodosElminados = 0;
    if ($type == 12 || $type == 200) {
        // Agregar nodos elminados para el complemento de pago
        $agregarNodosElminados = 1;
    }

    $SQL = " SELECT l.taxid,l.address5,t.tagname,t.areacode,l.legalid,l.legalname as empresa,a.areadescription as legalname,
            l.address1 as calle,
            l.address2 as colonia,
            l.address3 as localidad,
            l.address3 as municipio,
            l.address4 as estado,
            l.address5 as cp,
            t.address1 as calleexpedido,t.address2 as noExteriorexpedido,
            t.address3 as coloniaexpedido,
            t.address4 as localidadexpedido,
            t.address4 as municipioexpedido,
            t.address5 as estadoexpedido,
            t.cp as codigoPostalExpedido,
            t.address6 as paisexpedido,
            a.Anioaprobacion,
            a.Noaprobacion,
            a.Nocertificado,
            l.FileSAT,
            l.regimenfiscal,
            '' as noExterior
            FROM areas a, tags t, legalbusinessunit l
            WHERE a.areacode=t.areacode
            and l.legalid=t.legalid
            AND tagref='" . $tagref . "'";
    $ErrMsg = _('El Sql que fallo fue');
    $DbgMsg = _('No se pudo obtener los datos de la unidad de negocio');
    // echo $SQL;
    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
    if (DB_num_rows($Result) == 1) {
        $myrowtags = DB_fetch_array($Result);

     //   $calle = $myrowtags ['calle'];
        $noExterior = $myrowtags ['noExterior'];
     //   $colonia = $myrowtags ['colonia'];
        $empresa = $myrowtags ['empresa'];
        $municipio = $myrowtags ['municipio'];
        $estado = $myrowtags ['estado'];
        $cp = $myrowtags ['cp'];
        $calleexpedido = $myrowtags ['calleexpedido'];
        $noExteriorexpedido = $myrowtags ['noExteriorexpedido'];
        $coloniaexpedido = $myrowtags ['coloniaexpedido'];
        $tagname = $myrowtags ['tagname'];
        $municipioexpedido = $myrowtags ['municipioexpedido'];
        $estadoexpedido = $myrowtags ['estadoexpedido'];
        $paisexpedido = $myrowtags ['paisexpedido'];
      //  $codigoPostalExpedido = $myrowtags ['codigoPostalExpedido'];
        $regimenfiscal = "(".$myrowtags ['regimenfiscal'].")";
    }

    $xml = new DOMdocument ();
    $xml->loadXml($XMLElectronico);
    $root = new DOMXPath($xml);
    $root->registerNamespace('cfdi', "http://www.sat.gob.mx/cfd/3");
    $root->registerNamespace('tfd', "http://www.sat.gob.mx/TimbreFiscalDigital");
    $comprobante = $root->query("/cfdi:Comprobante");
    $ncomprobante = $comprobante->item(0);

    $emisor = $root->query("/cfdi:Comprobante/cfdi:Emisor");
    $receptor = $root->query("/cfdi:Comprobante/cfdi:Receptor");
    $rfccliente = $receptor->item(0)->getAttribute("Rfc");

    $version = $ncomprobante->getAttribute('Version');
    $ncomprobante->removeAttribute('Version');
    $ncomprobante->setAttribute("version", $version);

    $serie = $ncomprobante->getAttribute('Serie');
    $ncomprobante->removeAttribute('Serie');
    $ncomprobante->setAttribute("serie", $serie);

    $folio = $ncomprobante->getAttribute('Folio');
    $ncomprobante->removeAttribute('Folio');
    $ncomprobante->setAttribute("folio", $folio);

    $fecha = $ncomprobante->getAttribute('Fecha');
    $ncomprobante->removeAttribute('Fecha');
    $ncomprobante->setAttribute("fecha", $fecha);

    $noCertificado = $ncomprobante->getAttribute('NoCertificado');
    $ncomprobante->removeAttribute('NoCertificado');
    $ncomprobante->setAttribute("noCertificado", $noCertificado);

    $total = $ncomprobante->getAttribute('Total');
    if($total ==""){
        $total = "0.00";
    }
    $ncomprobante->removeAttribute('Total');
    $ncomprobante->setAttribute("total", $total);


    $subTotal = $ncomprobante->getAttribute('SubTotal');
    if($subTotal ==""){
        $subTotal = "0.00";
    }
    $ncomprobante->removeAttribute('SubTotal');
    $ncomprobante->setAttribute("subTotal", $subTotal);

    $descuento = $ncomprobante->getAttribute('Descuento');
    $ncomprobante->removeAttribute('Descuento');
    $ncomprobante->setAttribute("descuento", $descuento);

    $tipoDeComprobante = $ncomprobante->getAttribute('TipoDeComprobante');
    $ncomprobante->removeAttribute('TipoDeComprobante');
    $ncomprobante->setAttribute("tipoDeComprobante", $tipoDeComprobante);


    $emisor = $emisor->item(0);
    $rfc = $emisor->getAttribute('Rfc');
    $emisor->removeAttribute('Rfc');
    $emisor->setAttribute("rfc", $rfc);

    $nombre = $emisor->getAttribute('Nombre');
    $emisor->removeAttribute('Nombre');
    $emisor->setAttribute("nombre", $nombre);    

    $receptor = $receptor->item(0);

    $rfc = $receptor->getAttribute('Rfc');
    $receptor->removeAttribute('Rfc');
    $receptor->setAttribute("rfc", $rfc);

    $nombre = $receptor->getAttribute('Nombre');
    $receptor->removeAttribute('Nombre');
    $receptor->setAttribute("nombre", $nombre);

    for ($cad = 0; $cad <= count($arraycadena) - 1; $cad ++) {
        $linea = $arraycadena [$cad];
        $datos = explode('|', $linea);
        if ($cad == 0) {
            // Agreg
            $linea = $arraycadena [$cad];
            $datos = explode('|', $linea);
            $datosdos = explode('|', $arraycadena [1]);
            $ncomprobante->setAttribute("NumCtaPago",  $datosdos [5]);
            if ($agregarNodosElminados == 1) {
                $ncomprobante->setAttribute("FormaPago", "");
                $ncomprobante->setAttribute("Descuento", 0);
            }
        }else if ($cad == 1) {
            $domfis = $xml->createElement("cfdi:DomicilioFiscal");

            $domfis = $emisor->appendChild($domfis);
            cargaAtt($domfis, array(
                "calle" => $calle,
                "noExterior" => $noExterior,
                "noInterior" => "",
                "colonia" => $colonia,
                "referencia" => $empresa,
                "municipio" => $municipio,
                "estado" => $estado,
                "pais" => $pais,
                "codigoPostal" => $cp
            ));

            $expedido = $xml->createElement("cfdi:ExpedidoEn");

            $expedido = $emisor->appendChild($expedido);
            // cargaAtt($expedido, array(
            //     "calle" => $calleexpedido,
            //     "noExterior" => $noExteriorexpedido,
            //     "noInterior" => "",
            //     "colonia" => $coloniaexpedido,
            //     "referencia" => $tagname,
            //     "municipio" => $municipioexpedido,
            //     "estado" => $estadoexpedido,
            //     "pais" => $paisexpedido,
            //     "codigoPostal" => $codigoPostalExpedido
            // ));

            // regimen fiscal
            $regimenfiscalNodo = $xml->createElement("cfdi:RegimenFiscal");
            $regimenfiscalNodo = $emisor->appendChild($regimenfiscalNodo);
            cargaAtt($regimenfiscalNodo, array(
                "Regimen" => $regimenfiscal
            ));

        //} elseif ($cad == 2) {
        } elseif ($datos [0] == '4') {
            //$cad = $cad + 1;
            $linea = $arraycadena [$cad];
            $datos = explode('|', $linea);
            $coloniarecep = $datos [8];
            $telrecep = $datos [9];
            // echo '<br>'.ReglasXCadena($coloniarecep).'<br>';
            $domicilio = $xml->createElement("cfdi:Domicilio");
            $domicilio = $receptor->appendChild($domicilio);
            //"colonia" => trim($coloniarecep),
            if($_SESSION['UserID']=='desarrollo'){
               // echo '<br/>datos [10]: '.$datos [9];
               // echo '<br/>telefono: '.$telrecep;
            }
            if ($rfccliente != 'XAXX010101000') {
                cargaAtt($domicilio, array(
                    "calle" => trim($datos [4]),
                    "noExterior" => trim($datos [5]),
                    "noInterior" => trim($datos [6]),
                    "colonia" => trim($datos [7]),
                    "referencia" => trim($telrecep),
                    "localidad" => "",
                    "municipio" => trim($datos [10]),
                    "estado" => trim($datos [11]),
                    "codigoPostal" => trim($datos [12]),
                    "pais" => trim($datos [3])
                ));
            } else {
                cargaAtt($domicilio, array(
                    "calle" => "",
                    "noExterior" => "",
                    "colonia" => "",
                    "referencia" => "",
                    "localidad" => "",
                    "municipio" => "",
                    "estado" => "",
                    "pais" => "MEXICO",
                    "codigoPostal" => ""
                ));
            }
        }
    }

    $conceptos = $root->query('/cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto');
    for ($i = 0; $i < $conceptos->length; $i ++) {
        $concepto = $conceptos->item($i);

        if ($agregarNodosElminados == 1) {
            $concepto->setAttribute("NoIdentificacion", "");
            $concepto->setAttribute("Unidad", "");
            $concepto->setAttribute("Descuento", 0);
        }

        $noIdentificacion = $concepto->getAttribute('NoIdentificacion');
        $concepto->removeAttribute('NoIdentificacion');
        $concepto->setAttribute("noIdentificacion", $noIdentificacion);

        $cantidad = $concepto->getAttribute('Cantidad');
        $concepto->removeAttribute('cantidad');
        $concepto->setAttribute("cantidad", $cantidad);

        $unidad = $concepto->getAttribute('Unidad');
        $concepto->removeAttribute('Unidad');
        $concepto->setAttribute("unidad", $unidad);

        $descripcion = $concepto->getAttribute('Descripcion');
        $concepto->removeAttribute('Descripcion');
        $concepto->setAttribute("descripcion", $descripcion);

        $valorUnitario = $concepto->getAttribute('ValorUnitario');
        //echo "<br>valorUnitario_impresion:".$valorUnitario;
        $concepto->removeAttribute('ValorUnitario');
        $concepto->setAttribute("ValorUnitario", fnDecimalFormat($valorUnitario,$decimalesPartidas));

        $importe = $concepto->getAttribute('Importe');
        $concepto->removeAttribute('Importe');
        $concepto->setAttribute("importe", fnDecimalFormat($importe,$decimalesPartidas));

        $ImporteDescuento = $concepto->getAttribute('Descuento');
        $concepto->removeAttribute('Descuento');
        $concepto->setAttribute("Descuento", fnDecimalFormat($ImporteDescuento,$decimalesPartidas));
    }

    if ($agregarNodosElminados == 1) {
        $impuestos = $xml->createElement("cfdi:Impuestos");
        $impuestos = $comprobante->item(0)->appendChild($impuestos);

        $impuestos->SetAttribute("TotalImpuestosTrasladados", 0);
        $impuestos->SetAttribute("TotalImpuestosRetenidos", 0);

        $traslados = $xml->createElement("cfdi:Traslados");
        $traslados = $impuestos->appendChild($traslados);

        $traslado = $xml->createElement("cfdi:Traslado");
        $traslado = $traslados->appendChild($traslado);
        cargaAtt($traslado, array(
            "Impuesto" => "",
            "TasaOCuota" => 0,
            "Importe" => 0,
            "TipoFactor" => ""
        ));
    }else{
        $impuestos = $root->query('/cfdi:Comprobante/cfdi:Impuestos');
        $nimpuestos = $impuestos->item(0);

        $totalImpuestosTrasladados = $nimpuestos->getAttribute('TotalImpuestosTrasladados');
        $nimpuestos->removeAttribute('TotalImpuestosTrasladados');
        $nimpuestos->setAttribute("totalImpuestosTrasladados", $totalImpuestosTrasladados);

        $totalImpuestosRetenidos = $nimpuestos->getAttribute('TotalImpuestosRetenidos');
        $nimpuestos->removeAttribute('TotalImpuestosRetenidos');
        $nimpuestos->setAttribute("totalImpuestosRetenidos", $totalImpuestosRetenidos);
    }

    $timbre = $root->query('/cfdi:Comprobante/cfdi:Complemento/tfd:TimbreFiscalDigital');

    if (!empty($timbre->item(0))) {
        $ntimbre = $timbre->item(0);

        $noCertificadoSAT = $ntimbre->getAttribute('NoCertificadoSAT');
        $ntimbre->removeAttribute('NoCertificadoSAT');
        $ntimbre->setAttribute("noCertificadoSAT", $noCertificadoSAT);

        $selloCFD = $ntimbre->getAttribute('SelloCFD');
        $ntimbre->removeAttribute('SelloCFD');
        $ntimbre->setAttribute("selloCFD", $selloCFD);

        $selloSAT = $ntimbre->getAttribute('SelloSAT');
        $ntimbre->removeAttribute('SelloSAT');
        $ntimbre->setAttribute("selloSAT", $selloSAT);
    }

    $xml->formatOutput = true;
    $XMLElectronico = $xml->saveXML();

    return $XMLElectronico;
}

// +-------------------------------------------------------------------------------+
// | Funcion para generacion de archivo de cancelacion de CFDI |
// +-------------------------------------------------------------------------------+
function generaXMLCancelCFDI($UIID, $tipocomprobante, $tagref, $serie, $folio, $iddocto, $carpeta, $fechaorigen, $db) {
    // **************************** //
    // No dejar echo en la funcion //
    // Afecta al punto de venta   //
    // **************************** //

    // echo $cadena_original;
    global $xml, $cadena, $conn, $sello, $cadenasellar, $totalimporte;
    $xml = new DOMdocument('1.0', 'UTF-8');
    $root = $xml->createElement("CancelaCFD");
    $root = $xml->appendChild($root);

    cargaAtt($root, array(
        "xmlns" => "http://cancelacfd.sat.gob.mx",
        "xmlns:soapenv" => "http://schemas.xmlsoap.org/soap/envelope/"
    ));
    // CGA agregar telefonos 23-09-2016
    $SQL = " SELECT l.taxid,a.address5,t.tagname,t.areacode,l.legalid,l.legalname as empresa,a.areadescription as legalname,
            a.address1 as calle,a.address2 as noExterior,a.address3 as colonia,
            a.address4 as localidad,a.address4 as municipio,a.address5 as estado,
            a.cp as cp,
            t.address1 as calleexpedido,t.address2 as noExteriorexpedido,
            t.address3 as coloniaexpedido,
            t.address4 as localidadexpedido,
            t.address4 as municipioexpedido,
            t.address5 as estadoexpedido,
            t.cp as codigoPostalExpedido,
            t.address6 as paisexpedido,
            a.Anioaprobacion,
            a.Noaprobacion,
            a.Nocertificado,
            l.FileSAT,
            l.regimenfiscal

            FROM areas a, tags t, legalbusinessunit l
            WHERE a.areacode=t.areacode
            and l.legalid=t.legalid
            AND tagref='" . $tagref . "'";
    $ErrMsg = _('El Sql que fallo fue');
    $DbgMsg = _('No se pudo obtener los datos de la unidad de negocio');
    // echo $SQL;
    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
    if (DB_num_rows($Result) == 1) {
        $myrowtags = DB_fetch_array($Result);
        $rfc = strtoupper($myrowtags ['taxid']);
        $rfc = trim($rfc);
        $keyfact = $myrowtags ['address5'];
        $nombre = $myrowtags ['tagname'];
        $area = $myrowtags ['areacode'];
        $legaid = $myrowtags ['legalid'];
        $legalname = $myrowtags ['empresa'];
    }

    $Cancelacion = $xml->createElement("Cancelacion");
    $Cancelacion = $root->appendChild($Cancelacion);
    cargaAtt($Cancelacion, array(
        "xmlns:xsd" => "http://www.w3.org/2001/XMLSchema",
        "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
        "fecha" => str_replace(' ', 'T', str_replace('/', '-', $fechaorigen)),
        "RfcEmisor" => trim($rfc)
    ));
    $cadenasellar = $cadenasellar . "|" . str_replace(' ', 'T', str_replace('/', '-', $fechaorigen)) . "|" . trim($rfc);

    $Folio = $xml->createElement("Folios");
    $Folio = $Cancelacion->appendChild($Folio);

    $UUID_ = $xml->createElement("UUID");
    $UUID_->appendChild($xml->createTextNode($UIID));
    $Folio->appendChild($UUID_);

    $cadenasellar = $cadenasellar . "|" . trim($UIID);

    $Signature = $xml->createElement("Signature");
    $Signature = $Cancelacion->appendChild($Signature);
    cargaAtt($Signature, array(
        "xmlns" => "http://www.w3.org/2000/09/xmldsig#"
    ));

    $SignedInfo = $xml->createElement("SignedInfo");
    $SignedInfo = $Signature->appendChild($SignedInfo);

    $CanonicalizationMethod = $xml->createElement("CanonicalizationMethod");
    $CanonicalizationMethod = $SignedInfo->appendChild($CanonicalizationMethod);
    cargaAtt($CanonicalizationMethod, array(
        "Algorithm" => "http://www.w3.org/TR/2001/REC-xml-c14n-20010315"
    ));

    $SignatureMethod = $xml->createElement("SignatureMethod");
    $SignatureMethod = $SignedInfo->appendChild($SignatureMethod);
    cargaAtt($SignatureMethod, array(
        "Algorithm" => "http://www.w3.org/2000/09/xmldsig#rsa-sha1"
    ));

    $Reference = $xml->createElement("Reference");
    $Reference = $SignedInfo->appendChild($Reference);
    cargaAtt($Reference, array(
        "URI" => ""
    ));

    $Transforms = $xml->createElement("Transforms");
    $Transforms = $Reference->appendChild($Transforms);

    $Transform = $xml->createElement("Transform");
    $Transform = $Transforms->appendChild($Transform);

    cargaAtt($Transform, array(
        "Algorithm" => "http://www.w3.org/2000/09/xmldsig#enveloped-signature"
    ));

    $DigestMethod = $xml->createElement("DigestMethod");
    $DigestMethod = $Reference->appendChild($DigestMethod);
    cargaAtt($DigestMethod, array(
        "Algorithm" => "http://www.w3.org/2000/09/xmldsig#sha1"
    ));

    $DigestValue = $xml->createElement("DigestValue");
    $DigestValue = $Reference->appendChild($DigestValue);
    // $DigestValue->appendChild($xml->createTextNode("DigestValue"));

    $SignatureValue = $xml->createElement("SignatureValue");
    $SignatureValue = $Signature->appendChild($SignatureValue);
    // $SignatureValue->appendChild($xml->createTextNode("SignatureValue"));

    if ($_SESSION ['DatabaseName'] == 'erpmservice' or $_SESSION ['DatabaseName'] == 'erpmservice_CAPA' or $_SESSION ['DatabaseName'] == 'erpmservice_DES') {
        $aprobaxfolio = TraeAprobacionxFolio($rfc, $serie, $folio, $db);
        $aprobacionfolios = explode('|', $aprobaxfolio);
        $certificado = $aprobacionfolios [0];
        $Noaprobacion = $aprobacionfolios [1];
        $anioAprobacion = $aprobacionfolios [2];
    } else {
        $certificado = $myrowtags ['FileSAT'];
    }
    $maquina = trim(`uname -n`);
    $ruta = "/var/www/html" . dirname($_SERVER ['PHP_SELF']) . "/companies/" . $_SESSION ['DatabaseName'] . "/SAT/" . str_replace('.', '', str_replace(' ', '', $legalname)) . "/";
    $file = $ruta . $certificado . ".key.pem"; // Ruta al archivo

    $fileKeyPem = $file;

    $pkeyid = openssl_get_privatekey(file_get_contents($file));
    // echo $file;
    openssl_sign($cadenasellar, $crypttext, $pkeyid, OPENSSL_ALGO_SHA1);
    openssl_free_key($pkeyid);
    $sello = base64_encode($crypttext); // lo codifica en formato base64
    // $root->setAttribute("sello",$sello);

    $root->setAttribute("sello", $sello);
    $file = $ruta . $certificado . ".cer.pem"; // Ruta al archivo

    $fileCerPem = $file;

    /*
     * $DigestValue = $xml->createElement("DigestValue");
     * $DigestValue = $Reference->appendChild($DigestValue);
     * $DigestValue->SetAttribute("DigestValue",$impuestofact);
     * $SignatureValue = $xml->createElement("SignatureValue");
     * $SignatureValue = $Cancelacion->appendChild($DigestValue);
     * $SignatureValue->SetAttribute("SignatureValue",$sello);
     */

    $KeyInfo = $xml->createElement("KeyInfo");
    $KeyInfo = $Signature->appendChild($KeyInfo);

    $X509Data = $xml->createElement("X509Data");
    $X509Data = $KeyInfo->appendChild($X509Data);

    $datos = file($file);
    $certificado = "";
    $carga = false;
    for ($i = 0; $i < sizeof($datos); $i ++) {
        if (strstr($datos [$i], "END CERTIFICATE"))
            $carga = false;
        if ($carga)
            $certificado .= trim($datos [$i]);
        if (strstr($datos [$i], "BEGIN CERTIFICATE"))
            $carga = true;
    }

    $X509Certificate = $xml->createElement("X509Certificate");
    // $X509Certificate->appendChild($xml->createTextNode($certificado));
    $X509Data->appendChild($X509Certificate);

    $xml->formatOutput = true;
    $nufa = $serie . $folio;
    $todo = $xml->saveXML();
    $dir = "/var/www/html/" . dirname($_SERVER ['PHP_SELF']) . "/companies/" . $_SESSION ['DatabaseName'] . "/SAT/" . str_replace('.', '', str_replace(' ', '', $legalname)) . "/XML/" . $carpeta . "/";
    if ($dir != "/dev/null") {
        $xml->formatOutput = true;
        $xml->save($dir . $nufa . "_ACUSE.xml");
    } else {
        $paso = $todo;
        $conn->replace("cfdsello", array(
            "selldocu" => $nufa,
            "sellcade" => $cadena_original,
            "sellxml" => $paso
                ), "selldocu", true);
    }

    // printf ("<pre>%s</pre>", htmlentities ($todo));

    return ($todo);
}

function TraeDatosCFD($cfdi, $campo) {
    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, $cfdi, $tags);
    xml_parser_free($parser); //

    $elements = array(); // the currently filling [child] XmlElement array
    $stack = array();
    $DatosCFDI = array();
    foreach ($tags as $tag) {
        $index = count($elements);
        if ($tag ['type'] == "complete" || $tag ['type'] == "open") {
            $elements [$index] = new XmlElement ();
            $elements [$index]->name = $tag ['tag'];
            $elements [$index]->attributes = $tag ['attributes'];
            $tmpname = str_ireplace('cfdi:', '', $elements [$index]->name);
            if ($tmpname == $campo) {
                $DatosCFDI = $tag ['attributes'];
                // echo '<br>atributos: '.var_dump($tag['attributes']).'<br>index:'.$index;
            }
            $elements [$index]->content = $tag ['value'];
            if ($tag ['type'] == "open") { // push
                $elements [$index]->children = array();
                $stack [count($stack)] = &$elements;
                $elements = &$elements [$index]->children;
            }
        }
        if ($tag ['type'] == "close") { // pop
            $elements = &$stack [count($stack) - 1];
            unset($stack [count($stack) - 1]);
        }
    }
    return ($DatosCFDI);
}

function generarCadena($xml) {
    $rutaXslt = "./xml_validator/lib/sat/v3.3/xslt/cadenaoriginal_3_3.xslt";

    if ($_SESSION['facturaPuntoVenta'] == 1) {
        // Ruta para Punto de Venta
        $rutaXslt = "../../../xml_validator/lib/sat/v3.3/xslt/cadenaoriginal_3_3.xslt";
    }

    $cadena = "";
    
    try {
        $xmlObj = new DOMDocument();
        $xmlObj->loadXML($xml, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);

        $xsl = new DOMDocument();
        $xsl->load($rutaXslt, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);

        $proc = new XSLTProcessor();

        libxml_use_internal_errors(true);
        $result = $proc->importStyleSheet($xsl);
        if (!$result) {
            foreach (libxml_get_errors() as $error) {
                echo "Libxml error: {$error->message}\n";
            }
        }
        libxml_use_internal_errors(false);

        if ($result) {
            $cadena = $proc->transformToXML($xmlObj);
        }

        return $cadena;

    } catch (Exception $exp) {
        return $exp->getMessage();
    }
}


function fnFormatDecimalseis($Monto){

    $cadena= strval($Monto);
    
    $arrayCadena = explode(".", $cadena);
    $strDecimales="";
    $strInteros="";
    $MontoFormat="";
    
    if(isset($arrayCadena[0])){
        $strInteros = $arrayCadena[0];
    }else{
        $strInteros="0";
    }

    if(isset($arrayCadena[1])){
        switch (strlen($arrayCadena[1])) {
            case '1':
                $strDecimales = $arrayCadena[1] ."00000";
                break;
            case '2':
                $strDecimales = $arrayCadena[1] ."0000";
                break;
            case '3':
                $strDecimales = $arrayCadena[1] ."000";
                break;
            case '4':
                $strDecimales = $arrayCadena[1] ."00";
                break;
            case '5':
                $strDecimales = $arrayCadena[1] ."0";
                break;
            case '6':
                $strDecimales = $arrayCadena[1];
                break;
            
            default:
                if(strlen($arrayCadena[1]) >=7){
                    $strDecimales = substr($arrayCadena[1], 0, 6);
                }else{
                     $strDecimales="000000";
                }

                break;
        }

    }

    $MontoFormat=$strInteros.".".$strDecimales;
    return $MontoFormat;
}

//3.3 Se agrego funcino para truncar los decimales para la facturacion para no redondear
// ya que los montos no coincidian en la busqueda de pedido de venta con lo que mostraba el PDF
function fnDecimalFormat($Amount,$Decimal){

    $cadena= strval($Amount);
    $arrayCadena = explode(".", $cadena);
    $strDecimales="";
    $strInteros="";
    $MontoFormat="";
    
    if(isset($arrayCadena[0])){
        $strInteros = $arrayCadena[0];
    }else{
        $strInteros="0";
    }

    if(isset($arrayCadena[1])){

        for ($i=strlen($arrayCadena[1]); $i < $Decimal; $i++) { 
            $strDecimales = $strDecimales ."0";  
        }

        if($strDecimales !=""){
            $strDecimales = $arrayCadena[1] . $strDecimales;
        }
        
        if(strlen($arrayCadena[1]) >= ($Decimal )){    
            $strDecimales = substr($arrayCadena[1], 0, $Decimal);
        }

        if($strDecimales == "" and strlen($arrayCadena[1]) ==0){
            
            for ($i=strlen($arrayCadena[1]); $i < $Decimal; $i++) { 
                $strDecimales = $strDecimales ."0";  
            }

            $strDecimales = $arrayCadena[1] . $strDecimales;
        }

    }else {
        for ($i=0; $i < $Decimal; $i++) { 
            $strDecimales = $strDecimales ."0";  
        }
    }

    $MontoFormat=$strInteros.".".$strDecimales;


    return $MontoFormat;
}

function fnFormatDecimal($Monto){
    $Monto = floor($Monto * 100) / 100;

    return $Monto;
}

function exp_to_dec($float_str)
// formats a floating point number string in decimal notation, supports signed floats, also supports non-standard formatting e.g. 0.2e+2 for 20
// e.g. '1.6E+6' to '1600000', '-4.566e-12' to '-0.000000000004566', '+34e+10' to '340000000000'
// Author: Bob
{
    // make sure its a standard php float string (i.e. change 0.2e+2 to 20)
    // php will automatically format floats decimally if they are within a certain range
    $float_str = (string)((float)($float_str));

    // if there is an E in the float string
    if(($pos = strpos(strtolower($float_str), 'e')) !== false)
    {
        // get either side of the E, e.g. 1.6E+6 => exp E+6, num 1.6
        $exp = substr($float_str, $pos+1);
        $num = substr($float_str, 0, $pos);
        
        // strip off num sign, if there is one, and leave it off if its + (not required)
        if((($num_sign = $num[0]) === '+') || ($num_sign === '-')) $num = substr($num, 1);
        else $num_sign = '';
        if($num_sign === '+') $num_sign = '';
        
        // strip off exponential sign ('+' or '-' as in 'E+6') if there is one, otherwise throw error, e.g. E+6 => '+'
        if((($exp_sign = $exp[0]) === '+') || ($exp_sign === '-')) $exp = substr($exp, 1);
        else trigger_error("Could not convert exponential notation to decimal notation: invalid float string '$float_str'", E_USER_ERROR);
        
        // get the number of decimal places to the right of the decimal point (or 0 if there is no dec point), e.g., 1.6 => 1
        $right_dec_places = (($dec_pos = strpos($num, '.')) === false) ? 0 : strlen(substr($num, $dec_pos+1));
        // get the number of decimal places to the left of the decimal point (or the length of the entire num if there is no dec point), e.g. 1.6 => 1
        $left_dec_places = ($dec_pos === false) ? strlen($num) : strlen(substr($num, 0, $dec_pos));
        
        // work out number of zeros from exp, exp sign and dec places, e.g. exp 6, exp sign +, dec places 1 => num zeros 5
        if($exp_sign === '+') $num_zeros = $exp - $right_dec_places;
        else $num_zeros = $exp - $left_dec_places;
        
        // build a string with $num_zeros zeros, e.g. '0' 5 times => '00000'
        $zeros = str_pad('', $num_zeros, '0');
        
        // strip decimal from num, e.g. 1.6 => 16
        if($dec_pos !== false) $num = str_replace('.', '', $num);
        
        // if positive exponent, return like 1600000
        if($exp_sign === '+') return $num_sign.$num.$zeros;
        // if negative exponent, return like 0.0000016
        else return $num_sign.'0.'.$zeros.$num;
    }
    // otherwise, assume already in decimal notation and return
    else return $float_str;
}


?>