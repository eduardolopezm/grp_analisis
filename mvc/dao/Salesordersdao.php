<?php
////
class Salesordersdao{
    private $pathprefix = "../.././";
        
    function Setdefaultsalesorder(){
        $pathprefix = "../.././";
        require_once $pathprefix . 'core/ModeloBase.php';
        require_once $pathprefix . 'dao/Custbranchdao.php';

        $UserID = $_SESSION["UserID"];
        $success = true;
        $msgerror = "";
        $typeerror = "";
        $codeerror = 0;
        $sql = "";

        /*VALORES DEFAULT*/
        $defaulttagref = '';
        $defaulttagrefname = '';
        $defaultlocation = '';
        $defaultlocationname = '';
        $defaultbranch = ''; //Carga cliente generico 1846
        $defaultcurrency = 'MXN';
        $branchname= "";
        $branchtaxid="";
        $branchemail= "";
        $branchaddress = "";
        $descuentoMaximo = "0"; //descuento maximo del usuario
        $versionPuntoVenta = "1.0.0";
        $salesmanCodeUser = "";
        $salesmanNameUser = "";
        $versionFactura = "3.3";

        $modelo = new ModeloBase;
        $sql = "SELECT l.loccode, l.locationname , t.tagref, t.tagdescription
                FROM locations l
                INNER JOIN tags t ON l.tagref = t.tagref
                INNER JOIN sec_loccxusser s ON l.loccode= s.loccode
                WHERE s.userid = '" . $_SESSION["UserID"] . "'"; 

        $resp = $modelo->ejecutarsql($sql);
        $message = "";

        if ($resp == true and !is_array($resp)){
            // $success = false;
            $message = _('No existen localidades asignadas para el Usuario ' . $_SESSION["UserID"]);
        }else{
            if ($resp == false){
                $msgerror = "ERROR EN LA CONSULTA";
                $typeerror = "MYSQL ERROR";
                $codeerror = "001";
            }else{
                for($xx = 0; $xx < count($resp); $xx++){
                    $defaultlocation = $resp[$xx]['loccode'];
                    $defaultlocationname = $resp[$xx]['locationname'];
                    $defaulttagref = $resp[$xx]['tagref'];
                    $defaulttagrefname = $resp[$xx]['tagdescription'];
                }
            }
        }

        $sql = "SELECT www_users.userid, www_users.realname, www_users.discount1, www_users.discount2, www_users.discount3, salesman.salesmancode, salesman.salesmanname
                FROM www_users
                LEFT JOIN salesman ON salesman.usersales = www_users.userid AND salesman.status = 'Active' AND salesman.type = 1
                WHERE www_users.userid = '" . $_SESSION["UserID"] . "'";

        $resp = $modelo->ejecutarSql($sql);
        $message = "";

        if ($resp == true and !is_array($resp)){
            $success = false;
            $message = _('No existe registros para la busqueda de usuario ' . $_SESSION["UserID"]);

        }else{
            if ($resp == false){
                $msgerror = "ERROR EN LA CONSULTA";
                $typeerror = "MYSQL ERROR";
                $codeerror = "001";

            }else{
                for($xx = 0; $xx < count($resp); $xx++){
                    $UserID = $resp[$xx]['userid'];
                    $varrealname = $resp[$xx]['realname'];
                    $descuentoMaximo = $resp[$xx]['discount1'] * 100;
                    $salesmanCodeUser = $resp[$xx]['salesmancode'];
                    $salesmanNameUser = $resp[$xx]['salesmanname'];
                }
            }
        }
        if (empty($salesmanCodeUser)) {
            //No enviar valor null
            $salesmanCodeUser = "";
        }
        if (empty($salesmanNameUser)) {
            //No enviar valor null
            $salesmanNameUser = "";
        }

        $sql = "SELECT custbranch.brname,
                    custbranch.branchcode,
                    custbranch.debtorno,
                    debtorsmaster.name,
                    custbranch.taxid,
                    custbranch.email,
                    debtorsmaster.address1,
                    debtorsmaster.address2,
                    debtorsmaster.address3,
                    debtorsmaster.address4,
                    debtorsmaster.address5,
                    debtorsmaster.address6
                FROM custbranch 
                LEFT JOIN debtorsmaster ON custbranch.debtorno = debtorsmaster.debtorno
                LEFT JOIN www_users ON www_users.userid = '" . $_SESSION['UserID'] . "'
                LEFT JOIN areas ON areas.areacode = www_users.defaultarea
                WHERE (custbranch.taxid = 'XAXX010101000' or custbranch.taxid = 'XAXX010101000')
                    and custbranch.area = www_users.defaultarea and debtorsmaster.name like '%Mostrador%'";

        $resp = $modelo->ejecutarSql($sql);
        $message = "";

        if ($resp == true and !is_array($resp)){
            //$success = false;
            //$message = _('No existe registros para la busqueda de sucursal ' . $defaultbranch);
        }else{
            if ($resp == false){
                $msgerror = "ERROR EN LA CONSULTA";
                $typeerror = "MYSQL ERROR";
                $codeerror = "001";

            }else{
                for($xx = 0; $xx < count($resp); $xx++){
                    $defaultbranch= $resp[$xx]['branchcode'];
                    $branchname= $resp[$xx]['brname'];
                    $branchtaxid= $resp[$xx]['taxid'];
                    $branchemail= $resp[$xx]['email'];
                    $branchaddress = $resp[$xx]['address1']." Col. ".$resp[$xx]['address2'].", ".$resp[$xx]['address3'].", ".$resp[$xx]['address4'];
                }
            }
        }
        //permiso modificar precio $this->Insertsalesorders($salesorders, $modelo);
        $perPrecio = $this->Havepermission($_SESSION ['UserID'], 1012, $_SESSION['DatabaseName']);
        $permisoPrecio = true;
        if ($perPrecio == 0) {
            $permisoPrecio = false;
        }
        $permisoPrecio = true; //Permite a todos

        //permiso modificar descuento
        $perDescuento = $this->Havepermission($_SESSION ['UserID'], 1777, $_SESSION['DatabaseName']);
        $permisoDescuento = true;
        if ($perDescuento == 0) {
            $permisoDescuento = false;
        }

        //Obtener version del punto de venta y version de factura
        $sql = "SELECT version, versionFactura FROM salesversionespuntoventa WHERE active = '1'";

        $resp = $modelo->ejecutarSql($sql);
        $message = "";

        if ($resp == true and !is_array($resp)){
            //$success = false;
            //$message = _('No existe registros para la busqueda de sucursal ' . $defaultbranch);
        }else{
            if ($resp == false){
                $msgerror = "ERROR EN LA CONSULTA";
                $typeerror = "MYSQL ERROR";
                $codeerror = "005";

            }else{
                for($xx = 0; $xx < count($resp); $xx++){
                    $versionPuntoVenta= $resp[$xx]['version'];
                    $versionFactura = $resp[$xx]['versionFactura'];
                }
            }
        }

        // Nombre ventana - tabla config
        $nomVentanaPuntoVenta = $_SESSION['nomVentanaPuntoVenta'];
        // Nombre empresa
        $nombreEmpresa = '';
        $sql = "SELECT coyname FROM companies";
        $resp = $modelo->ejecutarSql($sql);
        $message = "";
        if ($resp == true and !is_array($resp)){
            //$success = false;
            //$message = _('No existe registros para la busqueda de sucursal ' . $defaultbranch);
        }else{
            if ($resp == false){
                $msgerror = "ERROR EN LA CONSULTA";
                $typeerror = "MYSQL ERROR";
                $codeerror = "006";

            }else{
                for($xx = 0; $xx < count($resp); $xx++){
                    $nombreEmpresa = $resp[$xx]['coyname'];
                }
            }
        }
        // Tipo de comprobante
        $tipoComprobante = '';
        $tipoComprobanteName = '';
        $sql = "SELECT c_TipoDeComprobante, CONCAT(c_TipoDeComprobante, ' - ', descripcion) as descripcion FROM sat_tiposcomprobante WHERE invoiceuse = 1 AND c_TipoDeComprobante = 'I'";
        $resp = $modelo->ejecutarSql($sql);
        $message = "";
        if ($resp == true and !is_array($resp)){
            //$success = false;
            //$message = _('No existe registros para la busqueda de sucursal ' . $defaultbranch);
        }else{
            if ($resp == false){
                $msgerror = "ERROR EN LA CONSULTA";
                $typeerror = "MYSQL ERROR";
                $codeerror = "007";

            }else{
                for($xx = 0; $xx < count($resp); $xx++){
                    $tipoComprobante = $resp[$xx]['c_TipoDeComprobante'];
                    $tipoComprobanteName = $resp[$xx]['descripcion'];
                }
            }
        }

        // Nombre cliente
        $nomClienteVenta = 'Contribuyente';
        $nomAlmacenVenta = 'Objeto Principal';
        $nomAlmacenVentaPlural = 'Objetos Principales';
        $operacionIva = 0;
        $nomArticuloVenta = 'Objeto Parcial';
        $nomArticuloVentaPlural = 'Objetos Parciales';
        $nomUniNegVenta = 'UR';
        $nomUniNegVentaPlural = 'UR';
        $nomCotizacionVenta = 'Pase de Cobro';
        // False mostrar, true ocultar
        $valBtnPagar = true; // 1773
        $valBtnCotizacion = true; // 1968

        if ($this->Havepermission($_SESSION ['UserID'], 1968, $_SESSION['DatabaseName']) == 1) {
            // Permiso para pase de cobro
            $valBtnCotizacion = false; // 1968
        }

        if ($this->Havepermission($_SESSION ['UserID'], 1773, $_SESSION['DatabaseName']) == 1) {
            // Permiso para recibo de pago
            $valBtnPagar = false; // 1773
        }
        
        $arrdebtor = array(
                        "branchcode" => $defaultbranch,
                        "name" => $branchname,
                        "taxid" => $branchtaxid,
                        "email" => $branchemail,
                        "address" => $branchaddress,
                        "tagref" => $defaulttagref,
                        "tagrefname" => $defaulttagrefname,
                        "location" => $defaultlocation,
                        "locationname" => $defaultlocationname,
                        "currency" => $defaultcurrency,
                        "username" => $varrealname,
                        "userid" => $_SESSION['UserID'],
                        "permisoPrecio" => $permisoPrecio,
                        "permisoDescuento" => $permisoDescuento,
                        "descuentoMaximo" => $descuentoMaximo,
                        "version" => $versionPuntoVenta,
                        "versionmsj" => " - Nueva Version ".$versionPuntoVenta,
                        "salesmancode" => $salesmanCodeUser,
                        "salesmanname" => $salesmanNameUser,
                        "versionFactura" => $versionFactura,
                        "msjAreaConfigurada" => 'El usuario no tiene asignado un area por default',
                        "nomVentanaPuntoVenta" => $nomVentanaPuntoVenta,
                        "nombreEmpresa" => $nombreEmpresa,
                        "nomClienteVenta" => $nomClienteVenta,
                        "operacionIva" => $operacionIva,
                        "nomAlmacenVenta" => $nomAlmacenVenta,
                        "nomAlmacenVentaPlural" => $nomAlmacenVentaPlural,
                        "nomArticuloVenta" => $nomArticuloVenta,
                        "nomArticuloVentaPlural" => $nomArticuloVentaPlural,
                        "nomUniNegVenta" => $nomUniNegVenta,
                        "nomUniNegVentaPlural" => $nomUniNegVentaPlural,
                        "nomCotizacionVenta" => $nomCotizacionVenta,
                        "tipoComprobante" => $tipoComprobante,
                        "tipoComprobanteName" => $tipoComprobanteName,
                        "valBtnPagar" => $valBtnPagar,
                        "valBtnCotizacion" => $valBtnCotizacion,
                        "dataBase" => $_SESSION['DatabaseName']
                    );
        
        $response['data'][] = $arrdebtor;

        $response['success'] = $success;
        $response['error']['msgerror'] = $msgerror;
        $response['error']['typeerror'] = $typeerror;
        $response['error']['codeerror'] = $codeerror;
        $response['error']['codeerror'] = $sql;
        $response['data']['message'] = $message;

        //header('Content-type: application/json; charset=utf-8');
        //return json_encode($response, JSON_FORCE_OBJECT);

        return $response;
    }

    function Setpaymentmethods(){
        $pathprefix = "../.././";
        require_once $pathprefix . 'core/ModeloBase.php';

        $UserID = $_SESSION["UserID"];
        $success = true;
        $msgerror = "";
        $typeerror = "";
        $codeerror = 0;
        $arrPaymentmethods = array();
        $arrPayment = array();
        
        $modelo = new ModeloBase;


        //$descrip = str_replace(' ', '%', $descrip);
        $sql = "SELECT paymentid, paymentname 
                FROM paymentmethodssat WHERE invoiceuse = '1' ";

        $resp = $modelo->ejecutarsql($sql);
        $message = "";

        if ($resp == true and !is_array($resp)){
            $success = false; 
            $message = _('No existe registros para la busqueda de vendedor');
        }else{
            if ($resp == false){
                $msgerror = "ERROR EN LA CONSULTA";
                $typeerror = "MYSQL ERROR";
                $codeerror = "001";

            }else{
                for($xx = 0; $xx < count($resp); $xx++){
                    
                    $arrPayment = array(
                            "paymentid" => ($resp[$xx]['paymentid']),
                            "paymentname" => ($resp[$xx]['paymentname'])
                        );

                    //$response['data'][] = array("0"=>$arrproduct);
                    //$arrproducts[] = $arrproduct;
                    array_push($arrPaymentmethods, $arrPayment);

                }
            }
            
        }
        
        $response['success'] = $success;
        $response['error']['msgerror'] = $msgerror;
        $response['error']['typeerror'] = $typeerror;
        $response['error']['codeerror'] = $sql;
        $response['data']['message'] = $message;
        $response['data'] = $arrPaymentmethods;
        
        //header('Content-type: application/json; charset=utf-8');
        //return json_encode($response, JSON_FORCE_OBJECT);
        //test
        
        return $response;
    }

    function SetTipoComprobante(){
        $pathprefix = "../.././";
        require_once $pathprefix . 'core/ModeloBase.php';

        $UserID = $_SESSION["UserID"];
        $success = true;
        $msgerror = "";
        $typeerror = "";
        $codeerror = 0;
        $arrInformacion = array();
        $arrPayment = array();
        
        $modelo = new ModeloBase;


        //$descrip = str_replace(' ', '%', $descrip);
        $sql = "SELECT c_TipoDeComprobante, descripcion FROM sat_tiposcomprobante WHERE invoiceuse = '1' and active = '1'";

        $resp = $modelo->ejecutarsql($sql);
        $message = "";

        if ($resp == true and !is_array($resp)){
            $success = false; 
            $message = _('No existe registros para la busqueda de Tipo de Comprobante');
        }else{
            if ($resp == false){
                $msgerror = "ERROR EN LA CONSULTA";
                $typeerror = "MYSQL ERROR";
                $codeerror = "002";

            }else{
                for($xx = 0; $xx < count($resp); $xx++){
                    
                    $arrDatos = array(
                            "tipoComprobante" => ($resp[$xx]['c_TipoDeComprobante']),
                            "descripcion" => ($resp[$xx]['descripcion'])
                        );

                    //$response['data'][] = array("0"=>$arrproduct);
                    //$arrproducts[] = $arrproduct;
                    array_push($arrInformacion, $arrDatos);

                }
            }
            
        }
        
        $response['success'] = $success;
        $response['error']['msgerror'] = $msgerror;
        $response['error']['typeerror'] = $typeerror;
        $response['error']['codeerror'] = $sql;
        $response['data']['message'] = $message;
        $response['data'] = $arrInformacion;
        
        //header('Content-type: application/json; charset=utf-8');
        //return json_encode($response, JSON_FORCE_OBJECT);
        //test
        
        return $response;
    }

    function SetUsoCFDI(){
        $pathprefix = "../.././";
        require_once $pathprefix . 'core/ModeloBase.php';

        $UserID = $_SESSION["UserID"];
        $success = true;
        $msgerror = "";
        $typeerror = "";
        $codeerror = 0;
        $arrInformacion = array();
        $arrPayment = array();
        
        $modelo = new ModeloBase;


        //$descrip = str_replace(' ', '%', $descrip);
        $sql = "SELECT c_UsoCFDI, descripcion FROM sat_usocfdi WHERE invoiceuse = '1' and active = '1' ORDER BY c_UsoCFDI asc";

        $resp = $modelo->ejecutarsql($sql);
        $message = "";

        if ($resp == true and !is_array($resp)){
            $success = false; 
            $message = _('No existe registros para la busqueda de Tipo de Comprobante');
        }else{
            if ($resp == false){
                $msgerror = "ERROR EN LA CONSULTA";
                $typeerror = "MYSQL ERROR";
                $codeerror = "003";

            }else{
                for($xx = 0; $xx < count($resp); $xx++){
                    
                    $arrDatos = array(
                            "usoCFDI" => ($resp[$xx]['c_UsoCFDI']),
                            "descripcion" => ($resp[$xx]['descripcion'])
                        );

                    //$response['data'][] = array("0"=>$arrproduct);
                    //$arrproducts[] = $arrproduct;
                    array_push($arrInformacion, $arrDatos);

                }
            }
            
        }
        
        $response['success'] = $success;
        $response['error']['msgerror'] = $msgerror;
        $response['error']['typeerror'] = $typeerror;
        $response['error']['codeerror'] = $sql;
        $response['data']['message'] = $message;
        $response['data'] = $arrInformacion;
        
        //header('Content-type: application/json; charset=utf-8');
        //return json_encode($response, JSON_FORCE_OBJECT);
        //test
        
        return $response;
    }

    function SetMetodoPago(){
        $pathprefix = "../.././";
        require_once $pathprefix . 'core/ModeloBase.php';

        $UserID = $_SESSION["UserID"];
        $success = true;
        $msgerror = "";
        $typeerror = "";
        $codeerror = 0;
        $arrInformacion = array();
        $arrPayment = array();
        
        $modelo = new ModeloBase;


        //$descrip = str_replace(' ', '%', $descrip);
        $sql = "SELECT paymentid, paymentname FROM sat_paymentmethodssat WHERE pv_active = '1' and active = '1' ORDER BY paymentid desc";

        $resp = $modelo->ejecutarsql($sql);
        $message = "";

        if ($resp == true and !is_array($resp)){
            $success = false; 
            $message = _('No existe registros para la busqueda de Tipo de Comprobante');
        }else{
            if ($resp == false){
                $msgerror = "ERROR EN LA CONSULTA";
                $typeerror = "MYSQL ERROR";
                $codeerror = "004";

            }else{
                for($xx = 0; $xx < count($resp); $xx++){
                    
                    $arrDatos = array(
                            "metodoPago" => ($resp[$xx]['paymentid']),
                            "descripcion" => ($resp[$xx]['paymentname'])
                        );

                    //$response['data'][] = array("0"=>$arrproduct);
                    //$arrproducts[] = $arrproduct;
                    array_push($arrInformacion, $arrDatos);

                }
            }
            
        }
        
        $response['success'] = $success;
        $response['error']['msgerror'] = $msgerror;
        $response['error']['typeerror'] = $typeerror;
        $response['error']['codeerror'] = $sql;
        $response['data']['message'] = $message;
        $response['data'] = $arrInformacion;
        
        //header('Content-type: application/json; charset=utf-8');
        //return json_encode($response, JSON_FORCE_OBJECT);
        //test
        
        return $response;
    }

    function SetAnticipoCliente($branchcode,$tagref,$currency){
        $pathprefix = "../.././";
        require_once $pathprefix . 'core/ModeloBase.php';
        require_once $pathprefix . 'dao/Functions.php';

        $UserID = $_SESSION["UserID"];
        $success = true;
        $msgerror = "";
        $typeerror = "";
        $codeerror = 0;
        $arrInformacion = array();
        $arrPayment = array();
        
        $modelo = new ModeloBase;

        $sql = fnObtenerAnticiposCliente($modelo->getLink(), $branchcode, $currency, $tagref, 0);

        $funcionesGenerales = new Functions();
        $arrInformacion = $funcionesGenerales->fnObtenerAnticiposClientePV($sql);

        // $resp = $modelo->ejecutarsql($sql);
        $message = "";

        // if ($resp == true and !is_array($resp)){
        //     // $success = false; 
        //     $message = _('No existe registros para la busqueda de Anticipos de Cliente');
        // }else{
        //     if ($resp == false){
        //         $success = false;
        //         $msgerror = "ERROR EN LA CONSULTA";
        //         $typeerror = "MYSQL ERROR";
        //         $codeerror = "006";
        //     }else{
        //         for($xx = 0; $xx < count($resp); $xx++){
        //             $folio = "";
        //             $tasa = "";
        //             if($resp[$xx]['IVA'] == 1){
        //                 $tasa = ", Tasa IVA 16%";
        //             }else{
        //                 $tasa = ", Tasa IVA 0%";
        //             }
        //             $folio = 'ERP: '.$resp[$xx]['transno'].", Factura Anticipo: ".$resp[$xx]['factura'].$tasa;
        //             $arrDatos = array(
        //                 "type" => ($resp[$xx]['type']),
        //                 "transno" => ($resp[$xx]['transno']),
        //                 "id" => ($resp[$xx]['id']),
        //                 "factura" => ($resp[$xx]['factura']),
        //                 "idfactura" => ($resp[$xx]['idfactura']),
        //                 "IVA" => ($resp[$xx]['IVA']),
        //                 "pendiente" => ($resp[$xx]['pendiente']),
        //                 "MontoAnticipo" => 0,
        //                 "typename" => ($resp[$xx]['typename']),
        //                 "invtext" => ($resp[$xx]['invtext']),
        //                 "monto" => ($resp[$xx]['monto']),
        //                 "folio" => ($folio)
        //             );
        //             array_push($arrInformacion, $arrDatos);

        //         }
        //     }
            
        // }
        
        $response['success'] = $success;
        $response['error']['msgerror'] = $msgerror;
        $response['error']['typeerror'] = $typeerror;
        $response['error']['codeerror'] = $sql;
        $response['data']['message'] = $message;
        $response['data'] = $arrInformacion;
        
        //header('Content-type: application/json; charset=utf-8');
        //return json_encode($response, JSON_FORCE_OBJECT);
        //test
        
        return $response;
    }

    //**Reemplaza los caracteres del xml despues de facturar**//
    function caracteresEspecialesFactura($xml){
        //Minusculas
        $xml = str_replace("&#xE1;", "á", $xml);
        $xml = str_replace("&#xE9;", "é", $xml);
        $xml = str_replace("&#xED;", "í", $xml);
        $xml = str_replace("&#xF3;", "ó", $xml);
        $xml = str_replace("&#xFA;", "ú", $xml);

        $xml = str_replace("&#xC3;&#xB3;", "ó", $xml);

        //Mayusculas
        $xml = str_replace("&#xC1;", "Á", $xml);
        $xml = str_replace("&#xC9;", "É", $xml);
        $xml = str_replace("&#xCD;", "Í", $xml);
        $xml = str_replace("&#xD3;", "Ó", $xml);
        $xml = str_replace("&#xDA;", "Ú", $xml);
        //Letra ñ Ñ
        $xml = str_replace("&#xF1;", "ñ", $xml);
        $xml = str_replace("&#xD1;", "Ñ", $xml);
        $xml = str_replace("Ã", "Ñ", $xml);

        //Comilla "
        $xml = str_replace("&amp;quot;", "&quot;", $xml);

        return $xml;
    }

    //**Reemplaza los caracteres del xml antes de facturar**//
    function caracteresEspecialesFacturaAntes($xml){
        //Minusculas
        $xml = str_replace("á", "&#xE1;", $xml);
        $xml = str_replace("é", "&#xE9;", $xml);
        $xml = str_replace("í", "&#xED;", $xml);
        $xml = str_replace("ó", "&#xF3;", $xml);
        $xml = str_replace("ú", "&#xFA;", $xml);
        //Mayusculas
        $xml = str_replace("Á", "&#xC1;", $xml);
        $xml = str_replace("É", "&#xC9;", $xml);
        $xml = str_replace("Í", "&#xCD;", $xml);
        $xml = str_replace("Ó", "&#xD3;", $xml);
        $xml = str_replace("Ú", "&#xDA;", $xml);
        //Letra ñ
        $xml = str_replace("ñ", "&#xF1;", $xml);
        $xml = str_replace("Ñ", "&#xD1;", $xml);

        return $xml;
    }

    /*insert sales orders*/
    function Setsalesorders($params){
        //$pathprefix = "../.././";
        
        require_once $this->pathprefix . 'core/ModeloBase.php';
        require_once $this->pathprefix . 'model/Salesorder.php';
        require_once $this->pathprefix . 'model/Custbranch.php';
        require_once $this->pathprefix . 'dao/Custbranchdao.php';
        require_once $this->pathprefix . 'model/Salesorderdetails.php';
        require_once $this->pathprefix . 'dao/Stockmasterdao.php';
        require_once $this->pathprefix . 'model/Stockmastershort.php';

        require_once $this->pathprefix . 'dao/Functions.php';

        $success = true;
        $msgerror = "";
        $typeerror = "";
        $codeerror = 0;
        $sql= "sql";
        $resp = "0";
        $message = "";

        $modelo = new ModeloBase();

        $UserID = $_SESSION["UserID"];
        $branchcode = $params->branchcode;
        $tagref =  $params->tagref;
        // $nu_ue = $params->unidadEjecutora;
        $separacion = explode('_99_', $tagref);
        $tagref = $separacion[0];
        $nu_ue = $separacion[1];
        $location = $params->location;
        $currency = $params->currency;
        $salesman = $params->salesman;
        $paytermsindicator = "1";
        $paymentmethodcode = "";
        $paymentmethodcode = $params->paymentterm;
        $paymentmethodname = $params->paymentmethod;
        $paymentReferencia = $params->paymentReferencia;
        $totalAnticipo = $params->totalAnticipo;
        $nomArticuloVenta = $params->nomArticuloVenta;

        $generarRecibo = 1;

        if (empty($totalAnticipo)) {
            $totalAnticipo = 0;
        }

        $comments = "";
        $txtPagadorGen = "";
        $ordertype= "";

        $tipodocto = $params->type;

        // reemplazar comillas
        $paymentReferencia = str_replace('"', '', $paymentReferencia);
        $paymentReferencia = str_replace("'", "", $paymentReferencia);

        if (isset($params->comments)) {
            //Si vienen comentarios
            $comments = $params->comments;
            // reemplazar comillas
            $comments = str_replace('"', '', $comments);
            $comments = str_replace("'", "", $comments);
        }

        if (isset($params->txtPagadorGen)) {
            //Si vienen comentarios
            $txtPagadorGen = $params->txtPagadorGen;
            // reemplazar comillas
            $txtPagadorGen = str_replace('"', '', $txtPagadorGen);
            $txtPagadorGen = str_replace("'", "", $txtPagadorGen);
        }

        $tipoComprobante = "";
        if (isset($params->tipoComprobante)) {
            $tipoComprobante = $params->tipoComprobante;
        }

        $usoCFDI = "";
        if (isset($params->usoCFDI)) {
            $usoCFDI = $params->usoCFDI;
        }

        $metodoPago = "";
        if (isset($params->metodoPago)) {
            $metodoPago = $params->metodoPago;
        }

        $claveConfirmacion = "";
        if (isset($params->claveConfirmacion)) {
            $claveConfirmacion = $params->claveConfirmacion;
        }

        $versionFactura = "3.3";
        //Obtener version del punto de venta y version de factura
        $sql = "SELECT version, versionFactura FROM salesversionespuntoventa WHERE active = '1'";
        $resp = $modelo->ejecutarSql($sql);
        for($xx = 0; $xx < count($resp); $xx++){
            //$versionPuntoVenta= $resp[$xx]['version'];
            $versionFactura = $resp[$xx]['versionFactura'];
        }

        if ($versionFactura == "3.3" && $tipodocto == 110) {
            // Validaciones 3.3
            $errorVal = 0;
            
            if (empty($tipoComprobante)) {
                $errorVal = 1;
                // $message .= '<p><i className="fa fa-certificate text-danger" aria-hidden="true"></i> Tipo de Comprobante</p>';
                $message .= 'Tipo de Comprobante. ';
            }

            if (empty($usoCFDI)) {
                $errorVal = 1;
                // $message .= '<p><i className="fa fa-certificate text-danger" aria-hidden="true"></i> Uso de CFDI</p>';
                $message .= 'Uso de CFDI. ';
            }

            if (empty($metodoPago)) {
                $errorVal = 1;
                // $message .= '<p><i className="fa fa-certificate text-danger" aria-hidden="true"></i> Método de Pago</p>';
                $message .= 'Método de Pago. ';
            }

            // Validar existencia
            // echo "\n location ".$location;
            foreach ($params->products as $product){
                // echo "\n stockid ".$product->stockid;
                // echo "\n quantity ".$product->quantity;
                $sql = "SELECT IFNULL(quantity - ontransit,0) as available FROM locstock 
                WHERE loccode = '".$location."' AND stockid = '".$product->stockid."'";
                $resp = $modelo->ejecutarSql($sql);
                for($xx = 0; $xx < count($resp); $xx++){
                    if ($resp[$xx]['available'] < $product->quantity) {
                        // No valir existencias ya que se manejan en este tipo de ingresos
                        // $errorVal = 1;
                        // $message .= '<p><i className="fa fa-certificate text-danger" aria-hidden="true"></i> Al artículo '.$product->stockid.' tiene '.$resp[$xx]['available'].' de existencia</p>';
                        // $message .= 'Al artículo '.$product->stockid.' tiene '.$resp[$xx]['available'].' de existencia. ';
                    }
                }
            }

            if ($errorVal == 1) {
                $success = false;
                $response['success'] = $success;
                $response['error']['msgerror'] = $msgerror;
                $response['error']['typeerror'] = $typeerror;
                $response['error']['codeerror'] = $codeerror;
                //$response['error']['codeerror'] = "";
                $response['message'] = $message;
                $response['orderno'] = $params->orderno;
                $response['facturacion'] = 0; //si ya se facturo
                $response['versionFactura'] = $versionFactura;
                
                return $response;
            }
        }

        // Validar matriz de conversión y contabilidad para movimientos
        $errorVal = 0;
        foreach ($params->products as $product){
            $sql = "SELECT
            tb_cat_objeto_detalle.clave_presupuestal,
            tb_cat_objeto_detalle.cuenta_banco,
            tb_cat_objeto_detalle.cuenta_abono,
            tb_cat_objeto_detalle.cuenta_cargo,
            chartdetailsbudgetbytag.rtc,
            tb_matriz_conv_ingresos.stockact,
            tb_matriz_conv_ingresos.accountegreso
            FROM tb_cat_objeto_detalle
            LEFT JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = tb_cat_objeto_detalle.clave_presupuestal
            LEFT JOIN tb_matriz_conv_ingresos ON tb_matriz_conv_ingresos.categoryid = chartdetailsbudgetbytag.rtc AND tb_matriz_conv_ingresos.ind_activo = 1
            WHERE
            tb_cat_objeto_detalle.estatus = 1
            AND tb_cat_objeto_detalle.stockid = '".$product->stockid."'
            AND tb_cat_objeto_detalle.loccode = '".$location."'";
            $resp = $modelo->ejecutarSql($sql);
            $numRegis = 0;
            if ($resp == true and !is_array($resp)){
                // $errorVal = 1;
                // $message .= $nomArticuloVenta.' '.$product->stockid.' no tiene configuración de detalle. Realizar la configuración en la función 2507';
            }else{
                if ($resp == false){
                    $msgerror = "ERROR EN LA CONSULTA";
                    $typeerror = "MYSQL ERROR";
                    $codeerror = "010";
                }else{
                    for($xx = 0; $xx < count($resp); $xx++){
                        if (!empty($resp[$xx]['clave_presupuestal'])) {
                            // Validar matriz de conversión
                            if (empty($resp[$xx]['stockact']) || empty($resp[$xx]['accountegreso'])) {
                                $errorVal = 1;
                                $message .= $nomArticuloVenta.' '.$product->stockid.' no tiene configuración en la Matriz del Ingreso con el CRI '.$resp[$xx]['rtc'].'. Realizar la configuración en la función 2506';
                            }
                        } elseif (empty($resp[$xx]['cuenta_abono']) || empty($resp[$xx]['cuenta_cargo'])) {
                            // Validar detalle
                            $errorVal = 1;
                            $message .= $nomArticuloVenta.' '.$product->stockid.' no tiene configuración de detalle. Realizar la configuración en la función 2507';
                        }
                        $numRegis ++;
                    }
                }
            }

            if ($numRegis == 0) {
                // No tiene detalle configurado
                $errorVal = 1;
                $message .= $nomArticuloVenta.' '.$product->stockid.' no tiene configuración de detalle. Realizar la configuración en la función 2507';
            }
        }
        //Validar si se hizo el corte
        $orderno = $params->orderno;
        $sql = "SELECT DISTINCT
        DATE_FORMAT(debtortrans.trandate, '%Y-%m-%d') as fecha,
        COUNT(debtortrans.trandate) as contt
        FROM debtortrans
        WHERE DATE_FORMAT(debtortrans.trandate, '%Y-%m-%d') < '".date('Y-m-d')."'
        AND debtortrans.type = 12
        AND debtortrans.nu_foliocorte = 0
        AND (debtortrans.ovamount + debtortrans.ovgst) <> 0
        AND debtortrans.userid = '".$_SESSION['UserID']."'";
        $resp = $modelo->ejecutarSql($sql);
        if($resp[0]['contt'] > 0){
                $message = "No se pueda hacer cobros si el corte del día anterior no se cerro";
                $errorVal = 1;
            }

        $sql = "SELECT DISTINCT
        DATE_FORMAT(debtortrans.trandate, '%Y-%m-%d') as fecha,
        COUNT(debtortrans.trandate) as contt
        FROM debtortrans
        WHERE DATE_FORMAT(debtortrans.trandate, '%Y-%m-%d') = '".date('Y-m-d')."'
        AND debtortrans.type = 12
        AND debtortrans.nu_foliocorte != 0
        AND (debtortrans.ovamount + debtortrans.ovgst) <> 0
        AND debtortrans.userid = '".$_SESSION['UserID']."'";
        $resp = $modelo->ejecutarSql($sql);
        if($resp[0]['contt'] > 0){
                $message = "No se pueden hacer mas cobros en el día si la caja ya fue cerrada";
                $errorVal = 1;
            }


        //Validar si existe un adeudo anterior no dejar pagar
        // $sql = "SELECT DISTINCT
	    // GROUP_CONCAT(salesDetail.id_administracion_contratos) as selecteds,
        // MAX(salesDetail.id_administracion_contratos) as max
        // FROM salesorderdetails  salesDetail
        // WHERE orderno = '".$orderno."'";
        // $resp = $modelo->ejecutarSql($sql);
        // if($resp[0]['selecteds'] != '' ){
        //     $selectedsID = $resp[0]['selecteds'];
        //     $max = $resp[0]['max'];
        //     $sql = "SELECT DISTINCT
        //     adminConts.id_periodo as periodo,
        //     adminCont.id_contrato as idContrato
        //     FROM salesorderdetails  salesDetail
        //     LEFT JOIN tb_administracion_contratos adminCont ON  adminCont.id_administracion_contratos = salesDetail.id_administracion_contratos
        //     LEFT JOIN tb_administracion_contratos adminConts ON  adminConts.id_contrato = adminCont.id_contrato AND adminConts.id_administracion_contratos < ".$max." AND adminConts.folio_recibo = ''
        //     WHERE orderno = '".$orderno."'
        //     AND adminConts.id_administracion_contratos NOT IN (".$selectedsID.")";
        //     $resp2 = $modelo->ejecutarSql($sql);

        //     if($resp2[0]['idContrato'] != ''){
        //         $message = "El Pase de Cobro No. ".$orderno." no se puede pagar. El folio: ".$resp2[0]['idContrato']." tiene adeudos pendientes";
        //         $errorVal = 1;
        //     }
        // }

        // for($xx = 0; $xx < count($resp); $xx++){
        //     $CotizacionStatus = $resp[$xx]['estatusprocesing'];
        //     $CotizacionQuotation = $resp[$xx]['quotation'];
        // }

        // if ($CotizacionStatus != 0 or $CotizacionQuotation != 1) {
        //     //si ya esta en proceso la cotizacion manda mensaje de error
        //     $message = "El Pase de Cobro No. ".$orderno." se encuentra en proceso de pago o ya ha sido pagado";

        //     $response['success'] = $success;
        //     $response['error']['msgerror'] = $msgerror;
        //     $response['error']['typeerror'] = $typeerror;
        //     $response['error']['codeerror'] = $codeerror;
        //     //$response['error']['codeerror'] = "";
        //     $response['message'] = $message;
        //     $response['orderno'] = $orderno;
        //     $response['facturacion'] = 1; //si ya se facturo
        //     $response['versionFactura'] = $versionFactura;
            
        //     return $response;
        // }
        //fin

        if ($errorVal == 1) {
            $success = false;
            $response['success'] = $success;
            $response['error']['msgerror'] = $msgerror;
            $response['error']['typeerror'] = $typeerror;
            $response['error']['codeerror'] = $codeerror;
            //$response['error']['codeerror'] = "";
            $response['message'] = $message;
            $response['orderno'] = $params->orderno;
            $response['facturacion'] = 0; //si ya se facturo
            $response['versionFactura'] = $versionFactura;
            
            return $response;
        }

        
        
        if (($tipodocto == 110) || ($tipodocto == 119)) {
            // Validar Anticipos
            $errorVal = 0;
            $funcionesGenerales = new Functions();

            foreach ($params->anticipoCliente as $anticipo){
                // echo "\n **********";
                // echo "\n id: ".$anticipo->id;
                if (abs($anticipo->MontoAnticipo) > 0) {
                    // Si tiene monto a aplicar
                    $sql = fnObtenerAnticiposCliente($modelo->getLink(), $branchcode, $currency, $tagref, $anticipo->id);
                    $resp = $funcionesGenerales->fnObtenerAnticiposClientePV($sql);
                    $pendiente = 0;
                    for($xx = 0; $xx < count($resp); $xx++){
                        $pendiente = $resp[$xx]['pendiente'];
                    }
                    // echo "\n MontoAnticipo: ".$anticipo->MontoAnticipo;
                    // echo "\n pendiente: ".$pendiente;
                    if (abs($anticipo->MontoAnticipo) > $pendiente) {
                        $errorVal = 1;
                        $message .= $anticipo->folio.' tiene $'.number_format($pendiente, 2, '.', ',').' pendiente por aplicar y se esta aplicando $'.number_format(abs($anticipo->MontoAnticipo), 2, '.', ',').'. ';
                    }
                }
            }

            if ($errorVal == 1) {
                $success = false;
                $response['success'] = $success;
                $response['error']['msgerror'] = $msgerror;
                $response['error']['typeerror'] = $typeerror;
                $response['error']['codeerror'] = $codeerror;
                //$response['error']['codeerror'] = "";
                $response['message'] = $message;
                $response['orderno'] = $params->orderno;
                $response['facturacion'] = 0; //si ya se facturo
                $response['versionFactura'] = $versionFactura;
                
                return $response;
            }
        }
        // echo "\n todo bien";
        // exit();

        // echo "\n ****************";
        // echo "\n products: ";
        // print_r($params->products);
        // echo "\n ****************";
        // echo "\n anticipoCliente: ";
        // print_r($params->anticipoCliente);
        // echo "\n tam: ".count($params->anticipoCliente);
        // exit();

        $orderno = $params->orderno;
        $nuevoPedCot = "cotizacion";
        if (empty($orderno)) {
            //si no trae orderno es pedido nuevo
            $nuevoPedCot = "nuevo";
            $orderno = $modelo->getdocumentnumber(30);
        }

        //if ($params->typeabbrev) {
            //Si vienen comentarios
          //  $ordertype= $params->typeabbrev;
        //}

        $nocuenta = "No Identificado";
        
        //$ordertype= $params->typeabbrev;
        $totalFac = 0;

        $Custbranch = new Custbranch;
        $Custbranchdao = new Custbranchdao;

        $Custbranch = $Custbranchdao->Custbranchbyid($branchcode);

        $salesorders = new Salesorder;

        $CotizacionStatus = 0;
        $CotizacionQuotation = 1;

        if ($nuevoPedCot == 'cotizacion') {
            //comparar si no esta en proceso de facturacion
            $sql = "SELECT estatusprocesing, quotation FROM salesorders WHERE orderno = '".$orderno."'";
            $resp = $modelo->ejecutarSql($sql);

            for($xx = 0; $xx < count($resp); $xx++){
                $CotizacionStatus = $resp[$xx]['estatusprocesing'];
                $CotizacionQuotation = $resp[$xx]['quotation'];
            }

            if ($CotizacionStatus == 0 and $CotizacionQuotation == 1) {
                $sql = "UPDATE salesorders SET estatusprocesing='1' WHERE orderno=" . $orderno;
                $resp = $modelo->ejecutarSql($sql);
            }
        }
        

        if ($CotizacionStatus != 0 or $CotizacionQuotation != 1) {
            //si ya esta en proceso la cotizacion manda mensaje de error
            $message = "El Pase de Cobro No. ".$orderno." se encuentra en proceso de pago o ya ha sido pagado";

            $response['success'] = $success;
            $response['error']['msgerror'] = $msgerror;
            $response['error']['typeerror'] = $typeerror;
            $response['error']['codeerror'] = $codeerror;
            //$response['error']['codeerror'] = "";
            $response['message'] = $message;
            $response['orderno'] = $orderno;
            $response['facturacion'] = 1; //si ya se facturo
            $response['versionFactura'] = $versionFactura;
            
            return $response;
        }

        $buyername = "";
        $freightcost = 0;
    
        $sql = "SELECT d.debtorno, d.name, d.salestype, d.currcode, d.holdreason, d.paymentterms, t.gl_accountsreceivable
                FROM debtorsmaster d
                    LEFT JOIN chartdebtortype t ON d.typeid = t.typedebtorid
                WHERE  debtorno = '" . $branchcode . "'";

        $resp = $modelo->ejecutarSql($sql);
        $message = "";

        $ctacxc = "";
        if ($resp == true and !is_array($resp)){
            $success = false;
            $message = _('No existe registros para la busqueda del cliente ' . $branchcode);
        }else{
            if ($resp == false){
                $msgerror = "ERROR EN LA CONSULTA";
                $typeerror = "MYSQL ERROR";
                $codeerror = "002";
            }else{
                for($xx = 0; $xx < count($resp); $xx++){
                    //$ordertype = $resp[$xx]['salestype'];
                    if (empty($ordertype)){
                        $ordertype = $resp[$xx]['salestype'];
                    }
                    $ctacxc = $resp[$xx]['gl_accountsreceivable'];
                    $paytermsindicator = $resp[$xx]['paymentterms'];
                }
            }
        }

        $salesorders->setOrderno($orderno); 
        $salesorders->setDebtorno($Custbranch->getDebtorno());
        $salesorders->setBranchcode($branchcode);
        $salesorders->setCustomerref($Custbranch->getTaxid());
        $salesorders->setBuyername($buyername);
        $salesorders->setComments($comments);
        $salesorders->setPagador($txtPagadorGen);
        //$salesorders->setOrddate($orddate); //FALTA FECHA PROCESO
        $salesorders->setOrdertype($ordertype);
        $salesorders->setShipvia($Custbranch->getDefaultshipvia());
        $salesorders->setDeladd1($Custbranch->getBraddress1());
        $salesorders->setDeladd2($Custbranch->getBraddress2());
        $salesorders->setDeladd3($Custbranch->getBraddress3());
        $salesorders->setDeladd4($Custbranch->getBraddress4());
        $salesorders->setDeladd5($Custbranch->getBraddress5());
        $salesorders->setDeladd6($Custbranch->getBraddress6());
        $salesorders->setContactphone($Custbranch->getPhoneno());
        $salesorders->setContactemail($Custbranch->getEmail());
        $salesorders->setDeliverto($Custbranch->getBrname());
        $salesorders->setDeliverblind($Custbranch->getDeliverblind());
        $salesorders->setFreightcost($freightcost);
        $salesorders->setFromstkloc($location);

        $salesorders->setPrintedpackingslip(0);
        $salesorders->setDatepackingslipprinted('1900-01-01');
                
        if ($tipodocto == 110){
            //factura
            $salesorders->setQuotation(4);
        } elseif ($tipodocto == 119){ 
            //remision
            $salesorders->setQuotation(5);
        }else {
            //cotizacion
            $salesorders->setQuotation(1);
        }

        if ($nuevoPedCot == "nuevo") {
            //ingresa la orden
            $salesorders->setTagref($tagref);
            $salesorders->setLn_ue($nu_ue);

            $salesorders->setLn_tagref_pase($tagref);
            $salesorders->setLn_ue_pase($nu_ue);
        }
        
        if ($nuevoPedCot == "cotizacion" && ($tipodocto == 110 || $tipodocto == 119)) {
            //actualiza la orden
            $salesorders->setTagref($tagref);
            $salesorders->setLn_ue($nu_ue);
        } else{
            $salesorders->setTagref($tagref);
            $salesorders->setLn_ue($nu_ue);

            $salesorders->setLn_tagref_pase($tagref);
            $salesorders->setLn_ue_pase($nu_ue);
        } 

        $salesorders->setPlaca("");
        $salesorders->setSerie("");
        $salesorders->setKilometraje("");
        $salesorders->setSalesman($salesman);
        $salesorders->setTaxtotal(0);
        $salesorders->setTotaltaxret(0);
        $salesorders->setCurrcode($currency);
        $salesorders->setPaytermsindicator($paytermsindicator);
        $salesorders->setAdvance(0);
        $salesorders->setUserregister($UserID);
        $salesorders->setRefundpercentsale(0);
        $salesorders->setVehicleno(0);
        $salesorders->setIdtarea(0);
        $salesorders->setNopedido("");
        $salesorders->setNoentrada("");
        $salesorders->setExtratext("");
        $salesorders->setNoremision("");
        $salesorders->setContract_Type("");
        $salesorders->setTypeorder("");
        $salesorders->setCodigobarras("");
        $salesorders->setContid(0);
        $salesorders->setIdprospect(0);
        $salesorders->setPuestaenmarcha("");
        $salesorders->setPaymentname($paymentmethodname);
        $salesorders->setNocuenta($nocuenta);
        $salesorders->setDeliverytext("");
        $salesorders->setTotalrefundpercentsale(0);
        $salesorders->setEstatusprocesing(0);
        $salesorders->setServiceorder("");
        $salesorders->setUsetype(0);
        $salesorders->setStatuscancel(0);
        $salesorders->setFromcr("");
        $salesorders->setOrdenprioridad(0);
        $salesorders->setDiscountcard("");
        $salesorders->setPayreference("");

        $stockmasterdao = new Stockmasterdao;
        $x = -1;
        $arrsalesorderdetails = array();
        $arrstockmastershort = array();
        $taxtotal = 0;
        foreach ($params->products as $product){
            $x++;
            $salesorderdetails = new Salesorderdetails;
            
            $salesorderdetails->setOrderlineno($x);
            $salesorderdetails->setOrderno($orderno);
            $salesorderdetails->setStkcode($product->stockid);
            $salesorderdetails->setFromstkloc($location);
            $salesorderdetails->setQtyinvoiced(0);
            $salesorderdetails->setUnitprice(number_format($product->price, 2, '.', ''));
            $salesorderdetails->setQuantity(number_format($product->quantity, 2, '.', ''));
            $salesorderdetails->setAlto(0);
            $salesorderdetails->setAncho(0);
            $salesorderdetails->setCalculatepricebysize(0);
            $salesorderdetails->setLargo(0);
            $salesorderdetails->setQuantitydispatched(number_format($product->quantity, 2, '.', ''));
            $salesorderdetails->setQtylost(0);
            $salesorderdetails->setDatelost('1900-01-01');
            $salesorderdetails->setRefundpercent(0);
            $salesorderdetails->setSaletype(0);
            $salesorderdetails->setEstimate(0);
            $salesorderdetails->setDiscountpercent($product->discount/100);
            $salesorderdetails->setDiscountpercent1(0);
            $salesorderdetails->setDiscountpercent2(0);
            //$salesorderdetails->setActualdispatchdate($actualdispatchdate); 
            $salesorderdetails->setCompleted(0);

            //obtener narrative del producto
            $Narrative = "";
            $seriesventa = "";
            //$Narrative = $branchcode." - ".$product->stockid." | ".$product->description." x ".$product->quantity." @ ".number_format($product->price, 2, ',', ' ')." | ".$seriesventa;
            //AGREGAR NARRATIVA  LAS POLIZAS CGA 092016
            $Narra = "";
            //$Narrative .= ' -' . $Narra . '- '; 
            if ($_SESSION['UserID'] == 'admin') {
                //$Narrative = $Narrative . "  & - á - é - í - ó - ú - Á - É - Í - Ó - Ú  - ´ - > - < - ñ - Ñ";
            }

            $salesorderdetails->setNarrative($Narrative);
            //$salesorderdetails->setItemdue($itemdue);
            $salesorderdetails->setPoline("");
            $salesorderdetails->setWarranty(0);
            $salesorderdetails->setSalestype($ordertype);
            $salesorderdetails->setServicestatus(0);
            $salesorderdetails->setPocost(number_format($product->price, 2, '.', ''));
            $salesorderdetails->setIdtarea(0);
            $salesorderdetails->setTotalrefundpercent(0);
            $salesorderdetails->setShowdescrip(1);
            $salesorderdetails->setCashdiscount(0);
            $salesorderdetails->setReadonlyvalues(0);
            $salesorderdetails->setModifiedpriceanddiscount(0);
            $salesorderdetails->setWoline("");
            $salesorderdetails->setStkmovid(0);
            $salesorderdetails->setUserlost("");
            $salesorderdetails->setAdevengar(0);
            $salesorderdetails->setFacturado(0);
            $salesorderdetails->setDevengado(0);
            $salesorderdetails->setXfacturar(0);
            $salesorderdetails->setAfacturar(0);
            $salesorderdetails->setXdevengar(0);
            $salesorderdetails->setNummes(0);
            $salesorderdetails->setLocalidad("");
            $salesorderdetails->setIdContrato($product->idcontrato);

            $arrsalesorderdetails[] = $salesorderdetails;

            //Obtiene datos del producto

            $stockmastershort = new Stockmastershort;
            $stockmastershort = $stockmasterdao->Getstockbyid($product->stockid, $modelo);
            
            $descuento = ($salesorderdetails->getUnitprice() * $salesorderdetails->getQuantity()) * ($salesorderdetails->getDiscountpercent());
            $stockmastershort->setTaxamount((($salesorderdetails->getUnitprice() * $salesorderdetails->getQuantity()) - $descuento) * $stockmastershort->getTaxrate());
            $taxtotal += $stockmastershort->getTaxamount();

            $arrstockmastershort[] = $stockmastershort;
        }

        if ($nuevoPedCot == "nuevo") {
            //ingresa la orden
            $salesorders->setTaxtotal($taxtotal);
            $this->Insertsalesorders($salesorders, $modelo);
        }

        if ($nuevoPedCot == "cotizacion") {
            //actualiza la orden
            $salesorders->setTaxtotal($taxtotal);
            $this->Updatesalesorders($salesorders, $modelo);
        }

        // $sql = "UPDATE salesorders SET ln_ue = '".$nu_ue."' WHERE orderno = '".$orderno."'";
        // $resp = $modelo->ejecutarSql($sql);

        //borrar productos y agregar nuevos
        $sql = "DELETE FROM salesorderdetails WHERE orderno = '".$orderno."'";
        $modelo->ejecutarSql($sql);

        for($xx=0; $xx < count($arrsalesorderdetails); $xx++){
            $this->Insertsalesorderdetails($arrsalesorderdetails[$xx], $modelo);
            if ($tipodocto != 0){
                $arrsalesorderdetails[$xx]->setQtyinvoiced($arrsalesorderdetails[$xx]->getQuantity());
            }
        }

        //Relacion de factura/cotizacion con los traspasos
        foreach ($params->referenciasTraspaso as $refTraspaso){
            //guardar no orden
            $sql = "UPDATE loctransfers SET description = '".$orderno."' WHERE reference = '".$refTraspaso->reference."'";
            $modelo->ejecutarSql($sql);
        }

        // Validaciones 3.3
        if ($tipodocto == 110 && $versionFactura == "3.3") {
            $response = ValidacionesCFDI3_3($orderno, $tipoComprobante, $usoCFDI, $metodoPago, 100, $claveConfirmacion, $modelo->getLink());
            if (!$response['success']) {
                // Si encontro error, se guarda como cotización
                $message =  "Se generó cotización por falta de configuración. ".str_replace('<br>', ' ', $response['message']);
                $tipodocto = 0;
            }
        }

        if (($tipodocto == 110) || ($tipodocto == 119)){
            /*FACTURA*/
            /*debtortrans*/

            require_once $this->pathprefix . 'model/Debtortranscls.php';
            require_once $this->pathprefix . 'dao/Debtortransdao.php';
            
            require_once $this->pathprefix . 'model/Stockmovescls.php';
            require_once $this->pathprefix . 'dao/Stockmovesdao.php';

            require_once $this->pathprefix . 'dao/Gltransdao.php';
            require_once $this->pathprefix . 'model/Gltransshort.php';

            $gltransshort = new Gltransshort;
            $gltransdao = new Gltransdao;

            $debtortrans = new Debtortranscls;
                        //$debtortrans_recibo = new Debtortranscls;
            $debtortransdao = new Debtortransdao;
            
            $debtortrans = $debtortransdao->Setdebtortrans($salesorders, $tipodocto, $paymentmethodcode, $paymentReferencia, $comments, $tipoComprobante, $usoCFDI, $metodoPago, $claveConfirmacion, $modelo);
                        //$debtortrans_recibo = $debtortransdao->Setdebtortrans($salesorders, 12, $modelo);   // recibo
                
            $stockmovesdao = new Stockmovesdao;
            $arrstockmoves = $stockmovesdao->Setstockmoves($arrsalesorderdetails, $debtortrans);

            /*Obtener Empresa Fiscal */
            $EmpresaFiscal = "";
            $sql = "SELECT empresafiscal
                FROM tags
                INNER JOIN legalbusinessunit ON tags.legalid=legalbusinessunit.legalid
                WHERE tagref='" . $debtortrans->getTagref() . "'";

            $resp = $modelo->ejecutarSql($sql);
            if (is_array($resp)){
                for($xx = 0; $xx < count($resp); $xx++){
                    $EmpresaFiscal = $resp[$xx]['empresafiscal'];
                }
            }

            $ovamount = 0;
            $ovgst = $taxtotal;
            for($xx=0; $xx < count($arrstockmoves); $xx++){
                $stockmovesdao->Insertstockmoves($arrstockmoves[$xx], $modelo);

                //Obtener el stkmoveno y categoria de impuesto del producto
                $stkmoveno = "";
                $prodTaxCategory = "";
                $trandate = "";
                $sql = "SELECT stockmoves.stkmoveno, stockmoves.trandate, stockmaster.taxcatid
                    FROM stockmoves 
                    LEFT JOIN stockmaster ON stockmaster.stockid = '".$arrstockmoves[$xx]->getStockid()."'
                    WHERE stockmoves.stockid = '".$arrstockmoves[$xx]->getStockid()."' 
                    AND stockmoves.type = '".$arrstockmoves[$xx]->getType()."' 
                    AND stockmoves.transno = '".$arrstockmoves[$xx]->getTransno()."' 
                    AND stockmoves.narrative = '".$arrstockmoves[$xx]->getNarrative()."'";
                $resp = $modelo->ejecutarSql($sql);
                for($xxStockCat = 0; $xxStockCat < count($resp); $xxStockCat++){
                    $stkmoveno = $resp[$xxStockCat]['stkmoveno'];
                    $prodTaxCategory = $resp[$xxStockCat]['taxcatid'];
                }

                if ($EmpresaFiscal == 0 and $_SESSION['FlagFiscal'] == 1) {
                }else{
                    //Agregar impuesto del producto

                    //Obtener informacion del impuesto del producto
                    $TaxGroup = 1;
                    $DispatchTaxProvince = 1;
                    $sql = "SELECT taxgrouptaxes.calculationorder,
                                taxauthorities.description,
                                taxgrouptaxes.taxauthid,
                                taxauthorities.taxglcode,
                                taxgrouptaxes.taxontax,
                                taxauthrates.taxrate,
                                taxauthorities.taxglcodediscount
                            FROM taxauthrates
                            INNER JOIN taxgrouptaxes ON taxauthrates.taxauthority=taxgrouptaxes.taxauthid
                            INNER JOIN taxauthorities ON taxauthrates.taxauthority=taxauthorities.taxid
                            WHERE taxgrouptaxes.taxgroupid='" . $TaxGroup . "'
                            AND taxauthrates.dispatchtaxprovince='" . $DispatchTaxProvince . "'
                            AND taxauthrates.taxcatid = " . $prodTaxCategory . "
                            ORDER BY taxgrouptaxes.calculationorder";

                    $resp = $modelo->ejecutarSql($sql);
                    for($xxAdd = 0; $xxAdd < count($resp); $xxAdd++){
                        //Agregar el producto con el impuesto
                        $sql = "INSERT INTO stockmovestaxes (stkmoveno,
                                    taxauthid,
                                    taxrate,
                                    taxcalculationorder,
                                    taxontax)
                                VALUES ('" . $stkmoveno . "',
                                    '" . $resp[$xxAdd]['taxauthid'] . "',
                                    '" . $resp[$xxAdd]['taxrate'] . "',
                                    '" . $resp[$xxAdd]['calculationorder'] . "',
                                    '" . $resp[$xxAdd]['taxontax'] . "');";
                        $modelo->ejecutarSql($sql);
                    }
                }
                                
                 // Restar la cantidad traspasada del disponible del almacen Origen
                $sql = "UPDATE locstock SET quantity = quantity - ".abs($arrstockmoves[$xx]->getQty())." 
                        WHERE stockid = '".$arrstockmoves[$xx]->getStockid()."' AND loccode = '".$arrstockmoves[$xx]->getLoccode()."'";

                $modelo->ejecutarSql($sql);

                $descuento = (($arrstockmoves[$xx]->getQty()*-1) * $arrstockmoves[$xx]->getPrice()) * ($arrstockmoves[$xx]->getDiscountpercent());
                $ovamount += (($arrstockmoves[$xx]->getQty()*-1) * $arrstockmoves[$xx]->getPrice()) - $descuento;

                $gltransshort = $this->Setgltransshort($arrstockmoves[$xx]->getType(), $arrstockmoves[$xx]->getTransno(), $arrstockmoves[$xx]->getTrandate(),
                        '',$arrstockmoves[$xx]->getPrd(), $arrstockmastershort[$xx]->getGlcode(), $arrstockmoves[$xx]->getNarrative(), $arrstockmoves[$xx]->getTagref(),
                        '', 1, $arrstockmoves[$xx]->getDebtorno(), $arrstockmoves[$xx]->getBranchcode(), $arrstockmoves[$xx]->getStockid(),
                        $arrstockmoves[$xx]->getQty(), 0, $arrstockmoves[$xx]->getStandardcost(), $arrstockmoves[$xx]->getLoccode(),
                        '', '', 0, 0, ($arrstockmoves[$xx]->getQty() * $arrstockmoves[$xx]->getStandardcost()*-1), 0, '', '', '', '', 0, '', '');

                // $gltransdao->Insertgltransshort($gltransshort, $modelo);

                $gltransshort = $this->Setgltransshort($arrstockmoves[$xx]->getType(), $arrstockmoves[$xx]->getTransno(), $arrstockmoves[$xx]->getTrandate(),
                        '',$arrstockmoves[$xx]->getPrd(), $arrstockmastershort[$xx]->getStockact(), $arrstockmoves[$xx]->getNarrative(), $arrstockmoves[$xx]->getTagref(),
                        '', 1, $arrstockmoves[$xx]->getDebtorno(), $arrstockmoves[$xx]->getBranchcode(), $arrstockmoves[$xx]->getStockid(),
                        $arrstockmoves[$xx]->getQty(), 0, $arrstockmoves[$xx]->getStandardcost(), $arrstockmoves[$xx]->getLoccode(),
                        '', '', 0, 0, (($arrstockmoves[$xx]->getQty() * $arrstockmoves[$xx]->getStandardcost())), 0, '', '', '', '', 0, '', '');

                // $gltransdao->Insertgltransshort($gltransshort, $modelo);

                $gltransshort = $this->Setgltransshort($arrstockmoves[$xx]->getType(), $arrstockmoves[$xx]->getTransno(), $arrstockmoves[$xx]->getTrandate(),
                        '',$arrstockmoves[$xx]->getPrd(), $arrstockmastershort[$xx]->getSalesglcode(), $arrstockmoves[$xx]->getNarrative(), $arrstockmoves[$xx]->getTagref(),
                        '', 1, $arrstockmoves[$xx]->getDebtorno(), $arrstockmoves[$xx]->getBranchcode(), $arrstockmoves[$xx]->getStockid(),
                        $arrstockmoves[$xx]->getQty(), 0, $arrstockmoves[$xx]->getStandardcost(), $arrstockmoves[$xx]->getLoccode(),
                        '', '', 0, 0, ((($arrstockmoves[$xx]->getPrice() * $arrstockmoves[$xx]->getQty()) / $debtortrans->getRate())), 0, '', '', '', '', 0, '', '');

                // $gltransdao->Insertgltransshort($gltransshort, $modelo);

                if ($arrstockmoves[$xx]->getDiscountpercent() != 0) {
                    $descuento = abs(((($arrstockmoves[$xx]->getPrice() * $arrstockmoves[$xx]->getQty()) / $debtortrans->getRate())) * ($arrstockmoves[$xx]->getDiscountpercent()));

                    $gltransshort = $this->Setgltransshort($arrstockmoves[$xx]->getType(), $arrstockmoves[$xx]->getTransno(), $arrstockmoves[$xx]->getTrandate(),
                        '',$arrstockmoves[$xx]->getPrd(), $arrstockmastershort[$xx]->getDiscountglcode(), $arrstockmoves[$xx]->getNarrative(), $arrstockmoves[$xx]->getTagref(),
                        '', 1, $arrstockmoves[$xx]->getDebtorno(), $arrstockmoves[$xx]->getBranchcode(), $arrstockmoves[$xx]->getStockid(),
                        $arrstockmoves[$xx]->getQty(), 0, $arrstockmoves[$xx]->getStandardcost(), $arrstockmoves[$xx]->getLoccode(),
                        '', '', 0, 0, $descuento, 0, '', '', '', '', 0, '', '');

                    // $gltransdao->Insertgltransshort($gltransshort, $modelo);
                }

                /*INSERTA MOVIMIENTOS DE IVAS*/
                $Narrative = "";
                $Narrative = $Custbranch->getBranchcode()." @".$Custbranch->getBrname();

                $gltransshort = $this->Setgltransshort($debtortrans->getType(), $debtortrans->getTransno(), $debtortrans->getTrandate(),
                            '',$debtortrans->getPrd(), $arrstockmastershort[$xx]->getTaxglcode(), $Narrative, $debtortrans->getTagref(),
                            '', 1, $debtortrans->getDebtorno(), $debtortrans->getBranchcode(), '',
                            0, 0, 0, 0,
                            '', '', 0, 0, ($arrstockmastershort[$xx]->getTaxamount() * - 1), 0, '', '', '', '', 0, '', '');

                // $gltransdao->Insertgltransshort($gltransshort, $modelo);

                /*$gltransshort = $this->Setgltransshort($debtortrans->getType(), $debtortrans->getTransno(), $debtortrans->getTrandate(),
                            '',$debtortrans->getPrd(), $arrstockmastershort[$xx]->getTaxglcodePaid(), $Narrative, $debtortrans->getTagref(),
                            '', 1, $debtortrans->getDebtorno(), $debtortrans->getBranchcode(), '',
                            0, 0, 0, 0,
                            '', '', 0, 0, ($arrstockmastershort[$xx]->getTaxamount()), 0, '', '', '', '', 0, '', '');

                $gltransdao->Insertgltransshort($gltransshort, $modelo);*/          
            }

            /*INSERTA CXC*/
            $Narrative = "";
            $Narrative = $Custbranch->getBranchcode()." @".$Custbranch->getBrname();
            $gltransshort = $this->Setgltransshort($debtortrans->getType(), $debtortrans->getTransno(), $debtortrans->getTrandate(),
                        '',$debtortrans->getPrd(), $ctacxc, $Narrative, $debtortrans->getTagref(),
                        '', 1, $debtortrans->getDebtorno(), $debtortrans->getBranchcode(), '',
                        0, 0, 0, 0,
                        '', '', 0, 0, ($ovamount+$ovgst), 0, '', '', '', '', 0, '', '');


            // $gltransdao->Insertgltransshort($gltransshort, $modelo);

            $debtortrans->setOvamount(number_format($ovamount, 2, '.', ''));
            $debtortrans->setOvgst(number_format($ovgst, 2, '.', ''));

            if (number_format($totalAnticipo, 2, '.', '') == number_format($ovamount, 2, '.', '')) {
                $generarRecibo = 0;
            }


            $totalFac = number_format ( $ovamount + $ovgst , 2 );
                        
            //agregar movimiento de recibo
            //$DebtorTransID1= 0;
            //$DebtorTransID1 = $debtortransdao->Insertdebtortrans($debtortrans_recibo, $modelo);

            $DebtorTransID = $debtortransdao->Insertdebtortrans($debtortrans, $modelo);
                        
            /*************************FACTURA ELECTRONICA***********************/
            $tipodefacturacion = $tipodocto;

            $sql = "SELECT l.taxid,l.address5,t.tagname,t.areacode,l.legalid,t.typeinvoice,l.legalname
                    FROM legalbusinessunit l, tags t
                    WHERE l.legalid=t.legalid 
                                AND tagref='" . $debtortrans->getTagref() . "'";
                        
            $resp = $modelo->ejecutarSql($sql);
            $message = "";

            if ($resp == true and !is_array($resp)){
                $success = false;
                $message = _('No existe registros para la busqueda del tagref ' . $debtortrans->getTagref());
            }else{
                if ($resp == false){
                    $msgerror = "ERROR EN LA CONSULTA";
                    $typeerror = "MYSQL ERROR";
                    $codeerror = "001";
                }else{
                    for($xx = 0; $xx < count($resp); $xx++){
                        $rfc = $resp[$xx]['taxid'];
                        $keyfact = $resp[$xx]['address5'];
                        $nombre = $resp[$xx]['tagname'];
                        $area = $resp[$xx]['areacode'];
                        $legaid = $resp[$xx]['legalid'];
                        $tipofacturacionxtag = $resp[$xx]['typeinvoice'];
                        $legalname = $resp[$xx]['legalname'];
                    }
                }
            }
            $InvoiceNoTAG = "|";
            if ($tipodocto == 110){
                $InvoiceNoTAG = $modelo->getDocumentNext(10, $debtortrans->getTagref(), $area, $legaid);
            }else{
                $InvoiceNoTAG = $modelo->getDocumentNext($tipodocto, $debtortrans->getTagref(), $area, $legaid);
            }

            $separa = explode('|', $InvoiceNoTAG);
            $serie = $separa [1];
            $folio = $separa [0];

            $sql = "UPDATE debtortrans
                    SET folio='" . $serie."|".$folio . "', nu_ue = '".$nu_ue."'
                    WHERE id=" . $DebtorTransID;

            $resp = $modelo->ejecutarSql($sql);

            $tipofacturacion = $tipodocto;
            $factelectronica = $modelo->getXSAInvoicing($debtortrans->getTransno(), $debtortrans->getOrder_(), $debtortrans->getDebtorNo(), $tipofacturacion, $debtortrans->getTagref(), $serie, $folio);
            $factelectronica = utf8_decode(utf8_encode($factelectronica));
            
            /**********************************************/
            $success = false;
            $config = $_SESSION;
            
            $arrayGeneracion = "";
            if ($versionFactura == "3.3" && $tipofacturacion == 110) {
                $_SESSION['facturaPuntoVenta'] = 1;
                $arrayGeneracion = $modelo->getgeneraXMLCFDI3_3($factelectronica, 'ingreso', $debtortrans->getTagref(), $serie, $folio, $DebtorTransID, 'Facturas', $debtortrans->getOrder_());
            }else{
                $arrayGeneracion = $modelo->getgeneraXMLCFDI($factelectronica, 'ingreso', $debtortrans->getTagref(), $serie, $folio, $DebtorTransID, 'Facturas', $debtortrans->getOrder_());
            }
            
            $XMLElectronico = $arrayGeneracion ["xml"];
            $xmladdenda = $arrayGeneracion ["xmladdenda"];
                
            $xmladdenda = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xmladdenda);
            $XMLElectronico = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $XMLElectronico);
            $XMLElectronico = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $XMLElectronico);
            
            if ($tipofacturacion == 110) {
                // trae archivo para proceso de timbrado de documentos
                include_once '../../.././timbradores/TimbradorFactory.php';
                $timbrador = TimbradorFactory::getTimbrador($config, $DebtorTransID);
                if ($timbrador != null) {
                    $timbrador->setRfcEmisor($rfc);
                    
                    $timbrador->setDb($modelo->getLink());
                    //if ($_SESSION['UserID'] == 'admin') {
                    //$XMLElectronico = utf8_decode($XMLElectronico);
                    //$XMLElectronico = caracteresEspecialesFacturaAntes(($XMLElectronico));
                    // echo "<pre>".($XMLElectronico);
                    // exit();
                    //$cfdi = $timbrador->timbrarDocumento(($XMLElectronico)); //utf8_decode

                    //echo "<pre>".utf8_decode($cfdi);
                    //}else{
                    $XMLElectronico = $timbrador->timbrarDocumento($XMLElectronico);
                    //}

                    $success = ($timbrador->tieneErrores() == false);
                    foreach ($timbrador->getErrores() as $error) {
                        //prnMsg($error, 'error');
                        $message .= $error.". ";
                    }
                } else {
                    //prnMsg(_('No hay un timbrador configurado en el sistema, reintente enviar factura desde busqueda de pedidos'), 'error');
                }
            }

            if ($tipofacturacion == 119) {
                $success = true;
            }
                
            if ($success) {
                // leemos la informacion del cfdi en un arreglo
                $DatosCFDI = TraeTimbreCFDI($XMLElectronico);
                
                if ((strlen($DatosCFDI ['FechaTimbrado']) > 0) || ($tipofacturacion == 119)) {
                    $cadenatimbre = '';
                    
                    if ($tipofacturacion == 110) {
                        $selloSAT = '';
                        if (isset($DatosCFDI ['SelloSAT'])) {
                            $selloSAT = $DatosCFDI ['SelloSAT'];
                        }elseif (isset($DatosCFDI ['selloSAT'])) {
                            $selloSAT = $DatosCFDI ['selloSAT'];
                        }
                        
                        $selloCDF = '';
                        if (isset($DatosCFDI ['SelloCFD'])) {
                            $selloCDF = $DatosCFDI ['SelloCFD'];
                        }elseif (isset($DatosCFDI ['selloCFD'])) {
                            $selloCDF = $DatosCFDI ['selloCFD'];
                        }

                        $certificadoSat = '';
                        if (isset($DatosCFDI ['NoCertificadoSAT'])) {
                            $certificadoSat = $DatosCFDI ['NoCertificadoSAT'];
                        }elseif (isset($DatosCFDI ['noCertificadoSAT'])) {
                            $certificadoSat = $DatosCFDI ['noCertificadoSAT'];
                        }
                    
                        $cadenatimbre = '||1.0|' . $DatosCFDI ['UUID'] . '|' . $DatosCFDI ['FechaTimbrado'] . '|' . $selloCDF . '|' . $certificadoSat . '||';

                        // guardamos el timbre fiscal en la base de datos para efectos de impresion de datos
                        $sql = "UPDATE debtortrans
                        SET fechatimbrado='" . $DatosCFDI ['FechaTimbrado'] . "',
                        uuid='" . $DatosCFDI ['UUID'] . "',
                        timbre='" . $selloSAT . "',
                        cadenatimbre='" . $cadenatimbre . "'
                        WHERE id=" . $DebtorTransID;
                        $resp = $modelo->ejecutarSql($sql);
                        $message = "";

                        $cadena = explode(' ', trim($sql));
                        if ($resp == true and !is_array($resp) and ($cadena[0] != 'INSERT') and ($cadena[0] != 'UPDATE') and ($cadena[0] != 'DELETE')){
                            $success = false;
                            $message = _('No se registraron datos de timbrado');
                        }else{
                            if ($resp == false){
                                $msgerror = "ERROR EN LA CONSULTA";
                                $typeerror = "MYSQL ERROR";
                                $codeerror = "001";
                            }else{
                                $message = _('Se actualizaron datos de timbrado');
                            }
                        }

                         // Actualizar fecha de Facturacion
                        $sql = "UPDATE salesdate SET fecha_facturado = '" . $DatosCFDI ['FechaTimbrado'] . "', userfacturado = '".$_SESSION['UserID']."' WHERE orderno = '".$orderno."'";
                        $modelo->ejecutarSql($sql);
                        // Actualizar fecha de Facturacion
                        
                        $XMLElectronico = $cfdi;

                        // Se almacena XML en file system
                        $carpeta = 'Facturas';
                        $dir = "../../companies/" . $_SESSION ['DatabaseName'] . "/SAT/" . str_replace('.', '', str_replace(' ', '', $legalname)) . "/XML/" . $carpeta . "/";
                        $nufa = $serie . $folio;
                        $mitxt = $dir . $nufa . ".xml";
                        if (file_exists($mitxt)){
                            unlink($mitxt); 
                        }
                        
                        // Agrega informacion de addenda para cfdi
                        $XMLElectronico = $modelo->getAgregaAddendaXML($XMLElectronico, $debtortrans->getDebtorno(), $DebtorTransID);
                        // Agrega informacion de complementos
                        $XMLElectronico = $modelo->getAgregaComplementoXML($XMLElectronico, $debtortrans->getDebtorno(), $DebtorTransID);
                        
                        $posend = strrpos($mitxt, '/', -1);
                        if (is_dir(substr($mitxt, 1, $posend))){
                            $fp = fopen($mitxt, "x");
                            fwrite($fp, $XMLElectronico);
                            fclose($fp); //
                        }
                    }

                    //Quitar caracteres raros, los regresa despues de timbrar
                    $XMLElectronico = caracteresEspecialesFactura(($XMLElectronico));
                    
                    $xmlImpresion = "";
                    $rfcEmisor = "";
                    $fechaEmision = date("Y-m-d H:i:s"); 
                    $fechaEmision2 = date("Y-m-d"); 

                    //echo "\n transno: ".$debtortrans->getTransno()."\n";

                    // if ($versionFactura == "3.3") {
                    //     $xmlImpresion = $modelo->generaXMLCFDI_Impresion($factelectronica, $XMLElectronico, $debtortrans->getTagref());
                    // }else{
                    //     $xmlImpresion = $XMLElectronico; 
                    // }
                    // echo "\n XMLElectronico: ".$XMLElectronico;
                    // echo "\n\n\n";
                    
                    // $array = $modelo->getgeneraXMLIntermedio(
                    //     $factelectronica, 
                    //     $xmlImpresion, 
                    //     $cadenatimbre, 
                    //     utf8_encode($arrayGeneracion ["cantidadLetra"]), 
                    //     $debtortrans->getOrder_(), 
                    //     $debtortrans->getTagref(), 
                    //     $tipofacturacion, 
                    //     $debtortrans->getTransno());

                    // $xmlImpresion = ($array["xmlImpresion"]);

                    // $xmlImpresion = str_replace('ine:', '', $xmlImpresion);

                    // $xmlImpresion = caracteresEspecialesFactura($xmlImpresion);
                    // $xmlImpresion = str_replace("&amp;", "&", $xmlImpresion);

                    $sql = "SELECT idXml FROM Xmls WHERE transno=" . $debtortrans->getTransno() . " and type=" . $tipofacturacion;
                    //var_dump($sql);
                    $resp = $modelo->ejecutarSql($sql);
                    $message = "";

                    if ($resp == true and !is_array($resp)){
                        // $success = false;
                        $message = _('No existe registros para la busqueda del xml ');

                        $flagsendfiscal= 1;
                        if ($tipofacturacion == 119) {
                            $flagsendfiscal= 0;
                        }
                        $cadena = explode(' ', trim($sql));
                        // utf8_decode(str_replace("&", "&amp;", addslashes($XMLElectronico)))
                        $sql = "INSERT INTO Xmls(transno,type,rfcEmisor,fechaEmision,xmlSat,xmlImpresion,fiscal)
                        VALUES(" . $debtortrans->getTransno() . "," . $tipofacturacion . ",'" . $rfc . "','" . $fechaEmision . "','" . (addslashes($XMLElectronico)) . "','" . (addslashes($xmlImpresion)) . "'," . $flagsendfiscal . ");";

                        $modelo->ejecutarSql($sql);
                        if ($resp == true and !is_array($resp) and ($cadena[0] != 'INSERT') and ($cadena[0] != 'UPDATE') and ($cadena[0] != 'DELETE')){
                            $success = false;
                            $message = _('No se inserto registro en tabla de xmls');
                        }else{
                            if ($resp == false){
                                $msgerror = "ERROR EN LA CONSULTA";
                                $typeerror = "MYSQL ERROR";
                                $codeerror = "001";
                            }else{
                                $message = _('Se inserto registro en la tabla de xmls');
                            }
                        }

                        $cadena = explode(' ', trim($sql));

                        if ($resp == true and !is_array($resp) and ($cadena[0] != 'INSERT') and ($cadena[0] != 'UPDATE') and ($cadena[0] != 'DELETE')){
                            $success = false;
                            $message = _('No se inserto registro en tabla de xmls');
                        }else{
                            if ($resp == false){
                                $msgerror = "ERROR EN LA CONSULTA";
                                $typeerror = "MYSQL ERROR";
                                $codeerror = "001";
                            }else{
                                $message = _('Se inserto registro en la tabla de xmls');
                            }
                        }
                    }else{
                        if ($resp == false){
                            $msgerror = "ERROR EN LA CONSULTA";
                            $typeerror = "MYSQL ERROR";
                            $codeerror = "001";
                        }else{
                        }
                    }

                    // $quotation = 4;
                    // if ($tipofacturacion == 119) {
                    //     $quotation = 5;
                    // }
                    // //actualizar status a factura
                    // $sql = "UPDATE salesorders SET quotation = '".$quotation."' WHERE orderno = '".$orderno."'";
                    // $modelo->ejecutarSql($sql);

                    if (count($params->anticipoCliente) > 0) {
                        // Si tiene anticipos agregar movimientos
                        foreach ($params->anticipoCliente as $anticipo){
                            // echo "\n **********";
                            // echo "\n id: ".$anticipo->id;
                            if (abs($anticipo->MontoAnticipo) > 0) {
                                $sql = "INSERT INTO salesinvoiceadvance (transidinvoice, transidanticipo, trandate, userid)
                                VALUES
                                ('".$DebtorTransID."', '".$anticipo->id."', NOW(),'".$_SESSION['UserID']."'); ";
                                $resp = $modelo->ejecutarSql($sql);
                            }
                        }

                        foreach ($params->anticipoCliente as $anticipo){
                            // echo "\n **********";
                            // echo "\n id: ".$anticipo->id;                            
                            if (abs($anticipo->MontoAnticipo) > 0) {
                                // echo "\n Monto antes: ".$anticipo->MontoAnticipo;
                                if($anticipo->IVA == '1'){
                                    $anticipo->MontoAnticipo = round($anticipo->MontoAnticipo + ($anticipo->MontoAnticipo * 0.16),2);
                                }else{
                                    $anticipo->MontoAnticipo = round($anticipo->MontoAnticipo,2);
                                }

                                $sql = "SELECT * FROM debtortrans WHERE id = ".$DebtorTransID;
                                $resp = $modelo->ejecutarSql($sql);
                                $datosFactura = 0;
                                for($xx = 0; $xx < count($resp); $xx++){
                                    $datosFactura = $resp[$xx]['ovgst'];
                                }

                                $ivaFactura = 0;
                                if(abs($datosFactura) > 0){
                                    $ivaFactura = 1;
                                }

                                // echo "\n CurrencyRate: ".$debtortrans->getRate();
                                // echo "\n Monto despues: ".$anticipo->MontoAnticipo;
                                // echo "\n Tagref: ".$debtortrans->getTagref();
                                // echo "\n CurrAbrev: ".$debtortrans->getCurrcode();
                                // echo "\n fechaEmision: ".$fechaEmision2;
                                // echo "\n DebtorNo: ".$debtortrans->getDebtorno();
                                // echo "\n DebtorTransID: ".$DebtorTransID;
                                // echo "\n IVA: ".$anticipo->IVA;
                                // echo "\n TransID: ".$anticipo->id;
                                // echo "\n ivaFactura: ".$ivaFactura;
                                
                                RegistrarNotasAnticipo(
                                    $debtortrans->getRate(),
                                    $anticipo->MontoAnticipo,
                                    $debtortrans->getTagref(),
                                    $debtortrans->getCurrcode(),
                                    $fechaEmision2, 
                                    $debtortrans->getDebtorno(),
                                    $DebtorTransID,  
                                    $anticipo->IVA,
                                    $anticipo->id,
                                    $ivaFactura, 
                                    $modelo->getLink(),
                                    1,
                                    $paymentReferencia,
                                    $Custbranch->getBrname()
                                );
                            }
                        }
                    }

                    $_SESSION['facturaPuntoVenta'] = 0;

                    // echo "\n ***** todo bien";
                    // exit();
                    
                    /*
                        $result = DB_query($query, $modelo->getLink(), $ErrMsg, $DbgMsg, true);
                        if (DB_num_rows($result) > 0) {
                            $query = "UPDATE Xmls SET xmlImpresion='" . htmlentities( htmlspecialchars_decode(str_replace("&", "&amp;", addslashes($xmlImpresion)), ENT_NOQUOTES) ) . "' where transno=" . $InvoiceNo . " and type=" . $tipofacturacion;
                            $Result = DB_query($query, $modelo->getLink(), $ErrMsg, $DbgMsg, true);
                        } else {
                            $query = "INSERT INTO Xmls(transno,type,rfcEmisor,fechaEmision,xmlSat,xmlImpresion,fiscal)
                                        VALUES(" . $debtortrans->getTransno() . "," . $tipofacturacion . ",'" . $rfc . "','" . $fechaemision . "','" . htmlentities( htmlspecialchars_decode(str_replace("&", "&amp;", addslashes($XMLElectronico)), ENT_NOQUOTES) ) . "','" . htmlentities( htmlspecialchars_decode(str_replace("&", "&amp;", addslashes($xmlImpresion)), ENT_NOQUOTES) ) . "'," . $flagsendfiscal . ");";
                            $Result = DB_query($query, $modelo->getLink(), $ErrMsg, $DbgMsg, true);

                            $XMLElectronico = str_replace("&amp;", "ñ", $XMLElectronico);
                            
                            $query = "INSERT INTO XmlsPrueba(transno,type,rfcEmisor,fechaEmision,xmlSat,xmlImpresion,fiscal)
                                        VALUES(" . $debtortrans->getTransno() . "," . $tipofacturacion . ",'" . $rfc . "','" . $fechaemision . "','" . htmlentities( htmlspecialchars_decode(str_replace("&", "&amp;", addslashes($XMLElectronico)), ENT_NOQUOTES) ) . "','" . htmlentities( htmlspecialchars_decode(str_replace("&", "&amp;", addslashes($xmlImpresion)), ENT_NOQUOTES) ) . "'," . $flagsendfiscal . ");";
                            $Result = DB_query($query, $modelo->getLink(), $ErrMsg, $DbgMsg, true);
                        }
                    */
                } else {
                    $message = "Ocurrio un error al crear la Factura No. ".$orderno.". Realizar una Reimpresion de la Funcion 602";
                    $success = false;
                    $response['success'] = $success;
                    $response['error']['msgerror'] = $msgerror;
                    $response['error']['typeerror'] = $typeerror;
                    $response['error']['codeerror'] = $codeerror;
                    //$response['error']['codeerror'] = "";
                    $response['message'] = $message;
                    $response['orderno'] = $orderno;
                    $response['facturacion'] = 0; //si ya se facturo
                    $response['versionFactura'] = $versionFactura;
                    
                    return $response;
                }
            }else{
                $success = false;
                $response['success'] = $success;
                $response['error']['msgerror'] = $msgerror;
                $response['error']['typeerror'] = $typeerror;
                $response['error']['codeerror'] = $codeerror;
                //$response['error']['codeerror'] = "";
                $response['message'] = "Ocurrio un error al crear la Factura No. ".$orderno.". Realizar una Reimpresion de la Funcion 602. ".$message;
                $response['orderno'] = $orderno;
                $response['facturacion'] = 0; //si ya se facturo
                $response['versionFactura'] = $versionFactura;
                
                return $response;
            }
        }

        // Sales Obtener 

        if (($tipodocto == 110) || ($tipodocto == 119)) {
            $success= true;

            //metodos de pago nombre
            $sql = "SELECT paymentid, paymentname 
                FROM paymentmethodssat WHERE paymentid in (".$paymentmethodcode.") ";
            $resp = $modelo->ejecutarSql($sql);
            $paymentmethodname = "";
            $separa = "";
            for($xx = 0; $xx < count($resp); $xx++){
                $paymentmethodname = $paymentmethodname.$separa." ".$resp[$xx]['paymentname'];
                if ($xx == 0) {
                    $separa = ",";
                }
            }

            $arrquotations = array();
            $arrquotation = array();

            $arrquotation = array(
                            "OrderNo" => $debtortrans->getOrder_(),
                            "transnofac" => $debtortrans->getTransno(),
                            "typeinvoice" => $tipofacturacion,
                            "debtorno" => $debtortrans->getDebtorno(),
                            "branchcode" => $debtortrans->getBranchcode(),
                            "tag" => $debtortrans->getTagref(),
                            "id" => $DebtorTransID,
                            "shippinglogid" => $_SESSION['UserID'],
                            "shippingno" => "",
                            "serie" => $serie,
                            "folio" => $serie."|".$folio,
                            "tipo" => "10",
                            "FolioFiscal" => $serie."|".$folio,
                            "totalFac" => $totalFac,
                            "paymentmethodname" => $paymentmethodname,
                            "generarRecibo" => $generarRecibo
                        );
            array_push($arrquotations, $arrquotation);

            $response['data'] = $arrquotations;
        }else if($tipodocto == 0){
            $success= true;
            $sql = "SELECT l.taxid,l.address5,t.tagname,t.areacode,l.legalid,t.typeinvoice,l.legalname
                FROM legalbusinessunit l, tags t
                WHERE l.legalid=t.legalid 
                AND tagref='" . $tagref . "'";
            $resp = $modelo->ejecutarSql($sql);
            $legaid = "";
            for($xx = 0; $xx < count($resp); $xx++){
                $legaid = $resp[$xx]['legalid'];
            }

            $arrquotations = array();
            $arrquotation = array();

            $arrquotation = array(
                            "orderno" => $orderno,
                            "TransNo" => $orderno,
                            "Tagref" => $tagref,
                            "legalid" => $legaid,
                            "branchcode" => $branchcode
                        );

            array_push($arrquotations, $arrquotation);

            $response['data'] = $arrquotations;
        }
        
        $response['success'] = $success;
        $response['error']['msgerror'] = $msgerror;
        $response['error']['typeerror'] = $typeerror;
        $response['error']['codeerror'] = $codeerror;
        //$response['error']['codeerror'] = "";
        $response['message'] = $message;
        $response['orderno'] = $orderno;
        $response['facturacion'] = 0;
        $response['versionFactura'] = $versionFactura;
        $response['tipoDocumento'] = $tipodocto;
        $response['nombreDocumento'] = ($tipodocto == 0 ? 'Cotización' : 'Factura');
        
        return $response;
    }

    function Searchquotationsbydesc($descrip){

        require_once $this->pathprefix . 'core/ModeloBase.php';

        $UserID = $_SESSION['UserID'];
        $success = true;
        $msgerror = "";
        $typeerror = "";
        $codeerror = 0;
        $arrquotations = array();
        $modelo = new ModeloBase;


        $descrip = str_replace(' ', '%', $descrip);

        $sql = "SELECT s.orderno, d.debtorno, d.name, s.orddate, s.tagref, s.fromstkloc
        , d.address1, d.address2, d.address3,d.address4,d.address5, d.address6
        , c.branchcode, c.taxid, c.email, salesman.salesmancode
        , salesman.salesmanname, s.comments, s.txt_pagador, tb_cat_unidades_ejecutoras.desc_ue as tagdescription, locations.locationname
        , s.ordertype, 
        d.usoCFDI,
        sat_usocfdi.descripcion as usoCFDIName,
        locations.id_pago as metodoPago,
        sat_paymentmethodssat.paymentname as metodoPagoName,
        s.ln_ue
        FROM salesorders s
        JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ur = s.tagref AND tb_cat_unidades_ejecutoras.ue = s.ln_ue
        INNER JOIN debtorsmaster d ON s.debtorno = d.debtorno
        LEFT JOIN custbranch c ON c.debtorno = s.debtorno
        LEFT JOIN salesman ON salesman.salesmancode = s.salesman
        LEFT JOIN tags ON tags.tagref = s.tagref
        LEFT JOIN locations ON locations.loccode = s.fromstkloc
        LEFT JOIN sat_usocfdi ON sat_usocfdi.c_UsoCFDI = d.usoCFDI
        LEFT JOIN sat_paymentmethodssat ON sat_paymentmethodssat.paymentid = locations.id_pago
        WHERE s.quotation = 1
        and (s.orderno = '" . $descrip . "'
        OR d.debtorno = '" . $descrip . "'
        OR d.name like '%" . $descrip . "%'
        ) and s.tagref in (SELECT tagref FROM sec_unegsxuser WHERE userid = '".$_SESSION["UserID"]."')";

        $resp = $modelo->ejecutarSql($sql);
        $message = "";

        if ($resp == true and !is_array($resp)){
            $success = false;
            $message = _('No existe registros para la busqueda de ' . $descrip);
        }else{
            if ($resp == false){
                $msgerror = "ERROR EN LA CONSULTA";
                $typeerror = "MYSQL ERROR";
                $codeerror = "001";

            }else{
                for($xx = 0; $xx < count($resp); $xx++){
                    $branchaddress = $resp[$xx]['address1']." Col. ".$resp[$xx]['address2'].", ".$resp[$xx]['address3'].", ".$resp[$xx]['address4'];
                    $arrquotation = array(
                            "orderno" => $resp[$xx]['orderno'],
                            "debtorno" => $resp[$xx]['debtorno'],
                            "namedebtor" => $resp[$xx]['name'],
                            "orddate" => $resp[$xx]['orddate'],
                            "tagref" => $resp[$xx]['tagref']."_99_".$resp[$xx]['ln_ue'],
                            "tagname" => $resp[$xx]['tagdescription'],
                            "unidadEjecutora" => $resp[$xx]['ln_ue'],
                            "fromstkloc" => $resp[$xx]['fromstkloc'],
                            "locationname" => $resp[$xx]['locationname'],
                            "branchcode" => $resp[$xx]['branchcode'],
                            "taxid" => $resp[$xx]['taxid'],
                            "email" => $resp[$xx]['email'],
                            "address" => $branchaddress,
                            "vacio" => "", //dato vacio
                            "salesmancode" => $resp[$xx]['salesmancode'],
                            "salesmanname" => $resp[$xx]['salesmanname'],
                            "comments" => ($resp[$xx]['comments']),
                            "txt_pagador" => $resp[$xx]['txt_pagador'],
                            "ordertype" => $resp[$xx]['ordertype'],
                            "usoCFDI" => ($resp[$xx]['usoCFDI']),
                            "usoCFDIName" => ($resp[$xx]['usoCFDIName']),
                            "metodoPago" => ($resp[$xx]['metodoPago']),
                            "metodoPagoName" => ($resp[$xx]['metodoPagoName'])
                        );

                    //$response['data'][] = array("0"=>$arrproduct);
                    //$arrproducts[] = $arrproduct;
                    array_push($arrquotations, $arrquotation);

                }
            }
            
        }
        
        $response['success'] = $success;
        $response['error']['msgerror'] = $msgerror;
        $response['error']['typeerror'] = $typeerror;
        $response['error']['codeerror'] = $sql;
        $response['data']['message'] = $message;
        $response['data'] = $arrquotations;
        
        //header('Content-type: application/json; charset=utf-8');
        //return json_encode($response, JSON_FORCE_OBJECT);
        
        return $response;
    }

    function Setgltransshort($type, $typeno, $trandate, $cat_cuenta, $periodno, $account, $narrative, $tag, $userid, $rate, $debtorno, $branchno,
        $stockid, $qty, $grns, $standardcost, $loccode, $dateadded, $suppno, $purchno, $chequeno, $amount, $jobref, $bancodestino,
        $rfcdestino, $cuentadestino, $uuid, $posted, $lastusermod, $lastdatemod){
        
        require_once $this->pathprefix . 'dao/Gltransdao.php';
        require_once $this->pathprefix . 'model/Gltransshort.php';

        $gltransshort = new Gltransshort;

        $gltransshort->setType($type);
        $gltransshort->setTypeno($typeno);
        $gltransshort->setTrandate($trandate);
        $gltransshort->setCat_Cuenta($cat_cuenta);
        $gltransshort->setPeriodno($periodno);
        $gltransshort->setAccount($account);
        $gltransshort->setNarrative($narrative);
        $gltransshort->setTag($tag);
        $gltransshort->setUserid($userid);
        $gltransshort->setRate($rate);
        $gltransshort->setDebtorno($debtorno);
        $gltransshort->setBranchno($branchno);
        $gltransshort->setStockid($stockid);
        $gltransshort->setQty($qty);
        $gltransshort->setGrns($grns);
        $gltransshort->setStandardcost($standardcost);
        $gltransshort->setLoccode($loccode);
        $gltransshort->setDateadded($dateadded);
        $gltransshort->setSuppno($suppno);
        $gltransshort->setPurchno($purchno);
        $gltransshort->setChequeno($chequeno);
        $gltransshort->setAmount($amount);
        $gltransshort->setJobref($jobref);
        $gltransshort->setBancodestino($bancodestino);
        $gltransshort->setRfcdestino($rfcdestino);
        $gltransshort->setCuentadestino($cuentadestino);
        $gltransshort->setUuid($uuid);
        $gltransshort->setPosted ($posted );
        $gltransshort->setLastusermod($lastusermod);
        $gltransshort->setLastdatemod($lastdatemod);

        return $gltransshort;
    }

    function Insertsalesorders($salesorders, $modelo){

        $pathprefix = "../.././";
        //require_once $pathprefix . 'core/ModeloBase.php';**

        $UserID = $_SESSION['UserID'];
        $success = true;
        $msgerror = "";
        $typeerror = "";
        $codeerror = 0;
        $arrproducts = array();
        $sql = "";

        //$modelo = new ModeloBase;


        $sql = "INSERT INTO salesorders(
        orderno,
        debtorno,
        branchcode,
        customerref,
        comments,
        orddate,
        ordertype,
        shipvia,
        deliverto,
        deladd1,
        deladd2,
        deladd3,
        deladd4,
        deladd5,
        deladd6,
        contactphone,
        contactemail,
        freightcost,
        fromstkloc,
        deliverydate,
        quotedate,
        confirmeddate,
        quotation,
        deliverblind,
        salesman,
        placa,
        serie,
        kilometraje,
        tagref,
        taxtotal,
        totaltaxret,
        currcode,
        paytermsindicator,
        advance,
        UserRegister,
        puestaenmarcha,
        paymentname,
        nocuenta,
        extratext,
        nopedido,
        noentrada,
        noremision,
        idprospect,
        contid,
        typeorder,
        deliverytext,
        estatusprocesing,
        usetype,
        discountcard,
        ln_ue,
        ln_tagref_pase,
        ln_ue_pase,
        txt_pagador
        )
        VALUES (
        '" . $salesorders->getOrderno() . "',
        '" . $salesorders->getDebtorno() . "',
        '" . $salesorders->getBranchcode() . "',
        '" . $salesorders->getCustomerref() . "',
        '" . $salesorders->getComments() . "',
        '" . date("Y-m-d H:i:s") . "',
        '" . $salesorders->getOrdertype() . "',
        '" . $salesorders->getShipvia() . "',
        '" . $salesorders->getDeliverto() . "',
        '" . $salesorders->getDeladd1() . "',
        '" . $salesorders->getDeladd2() . "',
        '" . $salesorders->getDeladd3() . "',
        '" . $salesorders->getDeladd4() . "',
        '" . $salesorders->getDeladd5() . "',
        '" . $salesorders->getDeladd6() . "',
        '" . $salesorders->getContactphone() . "',
        '" . $salesorders->getContactemail() . "',
        '" . $salesorders->getFreightcost() . "',
        '" . $salesorders->getFromstkloc() . "',
        '" . date("Y-m-d H:i:s") . "',
        '" . date("Y-m-d H:i:s") . "',
        '" . date("Y-m-d H:i:s") . "',
        '" . $salesorders->getQuotation() . "',
        '" . $salesorders->getDeliverblind() . "',
        '" . $salesorders->getSalesman() . "',
        '" . $salesorders->getPlaca() . "',
        '" . $salesorders->getSerie() . "',
        '" . $salesorders->getKilometraje() . "',
        '" . $salesorders->getTagref() . "',
        '" . $salesorders->getTaxtotal() . "',
        '" . $salesorders->getTotaltaxret() . "',
        '" . $salesorders->getCurrcode() . "',
        '" . $salesorders->getPaytermsindicator() . "',
        '" . $salesorders->getAdvance() . "',
        '" . $salesorders->getUserregister() . "',
        '" . $salesorders->getPuestaenmarcha() . "',
        '" . $salesorders->getPaymentname() . "',
        '" . $salesorders->getNocuenta() . "',
        '" . $salesorders->getExtratext() . "',
        '" . $salesorders->getNopedido() . "',
        '" . $salesorders->getNoentrada() . "',
        '" . $salesorders->getNoremision() . "',
        '" . $salesorders->getIdprospect() . "',
        '" . $salesorders->getContid() . "',
        '" . $salesorders->getTypeorder() . "',
        '" . $salesorders->getDeliverytext() . "',
        '" . $salesorders->getEstatusprocesing() . "',
        '" . $salesorders->getUsetype() . "',
        '" . $salesorders->getDiscountcard() . "',
        '" . $salesorders->getLn_ue() . "',
        '" . $salesorders->getLn_tagref_pase() . "',
        '" . $salesorders->getLn_ue_pase() . "',
        '" . $salesorders->getPagador() . "'
        )";
        $resp = $modelo->ejecutarSql($sql);
        $message = "";


        $cadena = explode(' ', trim($sql));
        if ($resp == true and !is_array($resp) and ($cadena[0] != 'INSERT') and ($cadena[0] != 'UPDATE') and ($cadena[0] != 'DELETE')){
            $success = false;
            $message = _('No se inserto registro en  Pedido de Venta');
        }else{
            if ($resp == false){
                $msgerror = "ERROR EN LA CONSULTA";
                $typeerror = "MYSQL ERROR";
                $codeerror = "001";
            }else{
                $message = _('Se inserto registro en salesorders ' . $salesorders->getOrderno());

                /*INSERTA REGISTRO EN salesdate*/
                
                $sql = "INSERT INTO salesdate(
                orderno,
                fecha_solicitud,
                usersolicitud,
                fecha_cotizacion,
                usercotizacion
                )
                VALUES(
                '" . $salesorders->getOrderno() . "',
                '" . date("Y-m-d H:i:s") . "',
                '" . $UserID . "',
                '" . date("Y-m-d H:i:s") . "',
                '" . $UserID . "'
                )";
                $resp = $modelo->ejecutarSql($sql);
                $message = "";
                $cadena = explode(' ', trim($sql));
                if ($resp == true and !is_array($resp) and ($cadena[0] != 'INSERT') and ($cadena[0] != 'UPDATE') and ($cadena[0] != 'DELETE')){
                    $success = false;
                    $message = _('No se inserto registro en  Pedido de Venta');
                }else{
                    if ($resp == false){
                        $msgerror = "ERROR EN LA CONSULTA";
                        $typeerror = "MYSQL ERROR";
                        $codeerror = "001";

                    }else{
                        $message = _('Se inserto registro en Log de Pedido de Venta ' . $salesorders->getOrderno());
                    }
                }
                

            }
            
        }

        $response['success'] = $success;
        $response['error']['msgerror'] = $msgerror;
        $response['error']['typeerror'] = $typeerror;
        $response['error']['codeerror'] = $codeerror;
        $response['error']['codeerror'] = $sql;
        $response['data']['message'] = $message;
        
        //return $response;
    }

    function Insertsalesorderdetails($salesorderdetails, $modelo){

        $pathprefix = "../.././";
        //require_once $pathprefix . 'core/ModeloBase.php';
        
        $UserID = $_SESSION['UserID'];
        $success = true;
        $msgerror = "";
        $typeerror = "";
        $codeerror = 0;
        $arrproducts = array();
        $sql = "";

        //$modelo = new ModeloBase;

        $sql = "INSERT INTO salesorderdetails (
                    orderlineno,
                    orderno,
                    stkcode,
                    unitprice,
                    quantity,
                    alto,
                    largo,
                    ancho,
                    calculatepricebysize,
                    discountpercent,
                    discountpercent1,
                    discountpercent2,
                    narrative,
                    poline,
                    itemdue,
                    fromstkloc,
                    salestype,
                    warranty,
                    servicestatus,
                    refundpercent,
                    quantitydispatched,
                    stkmovid,
                    showdescrip,
                    id_administracion_contratos)
                VALUES (
                    '" . $salesorderdetails->getOrderlineno() . "',
                    '" . $salesorderdetails->getOrderno() . "',
                    '" . $salesorderdetails->getStkcode() . "',
                    '" . $salesorderdetails->getUnitprice() . "',
                    '" . $salesorderdetails->getQuantity() . "',
                    '" . $salesorderdetails->getAlto() . "',
                    '" . $salesorderdetails->getLargo() . "',
                    '" . $salesorderdetails->getAncho() . "',
                    '" . $salesorderdetails->getCalculatepricebysize() . "',
                    '" . $salesorderdetails->getDiscountpercent() . "',
                    '" . $salesorderdetails->getDiscountpercent1() . "',
                    '" . $salesorderdetails->getDiscountpercent2() . "',
                    '" . $salesorderdetails->getNarrative() . "',
                    '" . $salesorderdetails->getPoline() . "',
                    '" . date("Y-m-d H:i:s") . "',
                    '" . $salesorderdetails->getFromstkloc() . "',
                    '" . $salesorderdetails->getSalestype() . "',
                    '" . $salesorderdetails->getWarranty() . "',
                    '" . $salesorderdetails->getServicestatus() . "',
                    '" . $salesorderdetails->getRefundpercent() . "',
                    '" . $salesorderdetails->getQuantitydispatched() . "',
                    '" . $salesorderdetails->getStkmovid() . "',
                    '" . $salesorderdetails->getShowdescrip() . "',
                    '" . $salesorderdetails->getIdContrato() . "'
                )";
        
        $resp = $modelo->ejecutarSql($sql);
        $message = "";

        $cadena = explode(' ', trim($sql));
        if ($resp == true and !is_array($resp) and ($cadena[0] != 'INSERT') and ($cadena[0] != 'UPDATE') and ($cadena[0] != 'DELETE')){
            $success = false;
            $message = _('No se inserto registro en Detalle de Pedido de Venta');
        }else{
            if ($resp == false){
                $msgerror = "ERROR EN LA CONSULTA";
                $typeerror = "MYSQL ERROR";
                $codeerror = "001";
            }else{
                $message = _('Se inserto registro en Detalle de Pedido de Venta ' . $salesorderdetails->getOrderno());
            }
        }

        $response['success'] = $success;
        $response['error']['msgerror'] = $msgerror;
        $response['error']['typeerror'] = $typeerror;
        $response['error']['codeerror'] = $codeerror;
        $response['error']['codeerror'] = $sql;
        $response['data']['message'] = $message;
        
        //return $response;
    }

    function Updatesalesorders($salesorders, $modelo){
        $pathprefix = "../.././";
        require_once $pathprefix . 'core/ModeloBase.php';
        
        $UserID = $_SESSION['UserID'];
        $success = true;
        $msgerror = "";
        $typeerror = "";
        $codeerror = 0;
        $arrproducts = array();
        $sql = "";

        //$modelo = new ModeloBase;
        

        $sqlUpdatePaseUr = "";
        // if (!empty($salesorders->getLn_tagref_pase()) && !empty($salesorders->getLn_ue_pase())) {
        //     $sqlUpdatePaseUr = "
        //     ln_tagref_pase = '".$salesorders->getLn_tagref_pase()."',
        //     ln_ue_pase = '".$salesorders->getLn_ue_pase()."', ";
        // }

        $sql = "UPDATE salesorders
        SET debtorno = '" . $salesorders->getDebtorno() . "',
        branchcode = '" . $salesorders->getBranchcode() . "',
        customerref = '" . $salesorders->getCustomerref() . "',
        comments = '" . $salesorders->getComments() . "',
        puestaenmarcha = '" . $salesorders->getPuestaenmarcha() . "',
        ordertype = '" . $salesorders->getOrdertype() . "',
        shipvia = '" . $salesorders->getShipvia() . "',
        deliverto = '" . $salesorders->getDeliverto() . "',
        deladd1 = '" . $salesorders->getDeladd1() . "',
        deladd2 = '" . $salesorders->getDeladd2() . "',
        deladd3 = '" . $salesorders->getDeladd3() . "',
        deladd4 = '" . $salesorders->getDeladd4() . "',
        deladd5 = '" . $salesorders->getDeladd5() . "',
        deladd6 = '" . $salesorders->getDeladd6() . "',
        contactphone = '" . $salesorders->getContactphone() . "',
        contactemail = '" . $salesorders->getContactemail() . "',
        freightcost = '" . $salesorders->getFreightcost() . "',
        fromstkloc = '" . $salesorders->getFromstkloc() . "',
        deliverydate = '" . $salesorders->getDeliverydate() . "',
        deliverblind = '" . $salesorders->getDeliverblind() . "',
        quotation = '" . $salesorders->getQuotation (). "',
        placa = '" . $salesorders->getPlaca() . "',
        salesman = '" . $salesorders->getSalesman() . "',
        serie = '" . $salesorders->getSerie() . "',
        kilometraje = '" . $salesorders->getKilometraje() . "',
        taxtotal = '" . $salesorders->getTaxtotal() . "',
        totaltaxret = '" . $salesorders->getTotaltaxret() . "',
        advance = '" . $salesorders->getAdvance() . "',
        currcode = '" . $salesorders->getCurrcode() . "',
        paytermsindicator = '" . $salesorders->getPaytermsindicator() . "',
        paymentname = '" . $salesorders->getPaymentname() . "',
        extratext = '" . $salesorders->getExtratext() . "',
        nocuenta = '" . $salesorders->getNocuenta() . "',
        nopedido = '" . $salesorders->getNopedido() . "',
        noentrada = '" . $salesorders->getNoentrada() . "',
        noremision = '" . $salesorders->getNoremision() . "',
        contid = '" . $salesorders->getContid() . "',
        typeorder = '" . $salesorders->getTypeorder() . "',
        deliverytext = '" . $salesorders->getDeliverytext() . "',
        payreference = '" . $salesorders->getPayreference() . "',
        estatusprocesing = '" . $salesorders->getEstatusprocesing() . "',
        ".$sqlUpdatePaseUr."
        tagref = '" . $salesorders->getTagref() . "',
        ln_ue = '" . $salesorders->getLn_ue() . "',
        txt_pagador = '" . $salesorders->getPagador() . "'
        WHERE orderno = '" . $salesorders->getOrderno() . "'";

        $resp = $modelo->ejecutarSql($sql);
        $message = "";

        if ($resp == true and !is_array($resp)){
            $success = false;
            $message = _('No se actualizo el registro en  Pedido de Venta');
        }else{
            if ($resp == false){
                $msgerror = "ERROR EN LA CONSULTA";
                $typeerror = "MYSQL ERROR";
                $codeerror = "001";
            }else{
                $message = _('Se actualizo registro en Pedido de Venta ' . $salesorders->getOrderno());
            }
        }

        $response['success'] = $success;
        $response['error']['msgerror'] = $msgerror;
        $response['error']['typeerror'] = $typeerror;
        $response['error']['codeerror'] = $codeerror;
        $response['error']['codeerror'] = $sql;
        $response['data']['message'] = $message;
        
        return $response;
    }

    function Updatesalesordersdetails($salesorderdetails, $modelo){
        $pathprefix = "../.././";
        require_once $pathprefix . 'core/ModeloBase.php';
        
        $UserID = $_SESSION['UserID'];
        $success = true;
        $msgerror = "";
        $typeerror = "";
        $codeerror = 0;
        $arrproducts = array();
        $sql = "";

        //$modelo = new ModeloBase;

        $sql = "UPDATE salesorderdetails
                SET unitprice = '" . $salesorderdetails->getUnitprice() . "',
                    quantity = '" . $salesorderdetails->getQuantity() . "',
                    discountpercent = '" . $salesorderdetails->getDiscountpercent() . "',
                    poline = '" . $salesorderdetails->getPoline() . "',
                    pocost = '" . $salesorderdetails->getPocost() . "',
                    narrative = '" . $salesorderdetails->getNarrative() . "',
                    warranty = '" . $salesorderdetails->getWarranty() . "',
                    servicestatus = '" . $salesorderdetails->getServicestatus() . "',
                    refundpercent = '" . $salesorderdetails->getRefundpercent() . "',
                    showdescrip = '" . $salesorderdetails->getShowdescrip() . "',
                    stkmovid = '" . $salesorderdetails->getStkmovid() . "',
                    itemdue = '" . $salesorderdetails->getItemdue() . "',
                WHERE orderno='" . $salesorderdetails->getOrderno() . "'
                    AND orderlineno='" . $salesorderdetails->getOrderlineno() . "'";
        $resp = $modelo->ejecutarSql($sql);
        $message = "";

        if ($resp == true and !is_array($resp)){
            $success = false;
            $message = _('No se actualizo el registro en detalle de Pedido de Venta');
        }else{
            if ($resp == false){
                $msgerror = "ERROR EN LA CONSULTA";
                $typeerror = "MYSQL ERROR";
                $codeerror = "001";
            }else{
                $message = _('Se actualizo registro en detalle de Pedido de Venta ' . $salesorderdetails->getOrderno());
            }
        }

        $response['success'] = $success;
        $response['error']['msgerror'] = $msgerror;
        $response['error']['typeerror'] = $typeerror;
        $response['error']['codeerror'] = $codeerror;
        $response['error']['codeerror'] = $sql;
        $response['data']['message'] = $message;
        
        return $response;
    }

    function Havepermission($consultausuario, $funcionvalida, $db) {
    
        // OBTIENE SI EL USUARIO TIENE O NO ACCESO A LA FUNCIONALIDAD DE LA PAGINA
        // DEVOLVIENDO 1 O 0 DE ACUERDO AL PERMISO QUE OBTIENE DE LA TABLA DE FUNCIONES POR USUARIO
        // Y/0 DE LAS FUNCIONES POR PERFIL DE USUARIO

        $modelo = new ModeloBase;

        $secFunctionTable = "";
        if(empty($_SESSION['SecFunctionTable'])) {
            $secFunctionTable = "sec_functions";
        } else {
            $secFunctionTable = $_SESSION['SecFunctionTable'];
        }
        $sql=" SELECT  1 as permiso
        FROM sec_modules s, sec_submodules sm, www_users u,
        sec_profilexuser PU, sec_funxprofile FP,
        $secFunctionTable FuxP, sec_categories C
        WHERE s.moduleid=sm.moduleid and s.active=1
        and FP.profileid=PU.profileid and FuxP.submoduleid=sm.submoduleid and C.categoryid=FuxP.categoryid
        and u.userid=PU.userid and PU.userid='".$consultausuario."'
        and u.userid=PU.userid and FuxP.functionid=FP.functionid
        and  FP.functionid=".$funcionvalida."
        and FuxP.active=1
        and FuxP.functionid not in (select funCtionid from sec_funxuser where userid='".$consultausuario."' and permiso = 1)
        UNION
        SELECT  PU.permiso as permiso
        FROM sec_modules s, sec_submodules sm, www_users u,
        $secFunctionTable FuxP, sec_categories C, sec_funxuser PU
        WHERE s.moduleid=sm.moduleid and s.active=1
        and FuxP.submoduleid=sm.submoduleid and C.categoryid=FuxP.categoryid
        and u.userid=PU.userid and PU.userid='".$consultausuario."'
        and u.userid=PU.userid and FuxP.functionid=PU.functionid
        and FuxP.functionid=".$funcionvalida."
        and FuxP.active=1
        and PU.permiso = 1";

        $resp = $modelo->ejecutarSql($sql);

        if ($resp == true and !is_array($resp)){
            //no existe permiso
            return 0;
        }else{
            if ($resp == false){
                return 0;
            }else{
                $permiso = 0;
                for($xx = 0; $xx < count($resp); $xx++){
                    $permiso = $resp[$xx]['permiso'];
                }
                return $permiso;
            }
        }
    }
}
?>