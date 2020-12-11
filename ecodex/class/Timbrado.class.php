<?php
class Timbrado{
    private static $url_wsTimbrado="http://servicios.ecodex.com.mx:4040/ServicioTimbrado.svc?wsdl";
    private static $proxy_wsTimbrado="https://servicios.ecodex.com.mx:4043/ServicioTimbrado.svc";
    private static $url_wsClientes="http://servicios.ecodex.com.mx:4040/ServicioClientes.svc?wsdl";
    private static $proxy_wsClientes="https://servicios.ecodex.com.mx:4043/ServicioClientes.svc";
    //---> proxy SSL
    //private static $url_wsTimbrado="http://pruebas.ecodex.com.mx:2044/ServicioTimbrado.svc?wsdl";
    //private static $proxy_wsTimbrado="https://pruebas.ecodex.com.mx:2045/ServicioTimbrado.svc?wsdl";//---> proxy SSL
    private static $timbrado;
    private static $clientes;
    private static $timbre;    
    private static $codigo;
    private static $descripcion;
    private static $cancelaResult;
    private static $qr;
   

    function  __construct() {
        //echo "Se creo instancia Timbrado";
    }
    
    public function getQR()
    {
    return self::$qr;
    }

    public function getTimbrado()
    {
    return self::$timbrado;
    }

    public function getCodigoEstatus()
    {
    return self::$codigo;
    }

    public function getDescripcionEstatus()
    {
    return self::$descripcion;
    }
    public function getCancela()
    {
        return self::$cancelaResult;
    }

    public function getAsignacion()
    {
    	return self::$clientes;
    }
    public function setTimbrado($ComprobanteXML,$rfc,$trsID,$token){
        try
        {
            $client = new nusoap_client(self::$url_wsTimbrado,self::$proxy_wsTimbrado);
            $err = $client->getError();
            if ($err)
            {
                self::$timbrado = 'No se pudo acceder al WebService de Timbrado ' . $err;
                return false;

            }else{
                $aParametros = array("ComprobanteXML" => array("DatosXML"=>$ComprobanteXML) ,"RFC" => $rfc,"Token" => $token,"TransaccionID"=>$trsID);
                $aRespuesta = $client->call("TimbraXML", $aParametros);
				//echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
                //echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
                if (isset($aRespuesta ["ComprobanteXML"]["DatosXML"]))
                {
                 self::$timbrado =$aRespuesta ["ComprobanteXML"]["DatosXML"];
                 return true;
                }
                elseif(isset ($aRespuesta["detail"]["FallaSesion"]["Descripcion"]))
	        {
	            // En caso de que falle la sesion Muestra el Error
	            self::$timbrado = $aRespuesta["detail"]["FallaSesion"]["Descripcion"];
                    return false;
	        }
	        elseif(isset ($aRespuesta["detail"]["FallaValidacion"]["Descripcion"]))
	        {
	            //En caso de que falle Validacion del XML muestra el Error
	            self::$timbrado = $aRespuesta["detail"]["FallaValidacion"]["Descripcion"];
                    return false;
	        }  
			elseif(isset ($aRespuesta["detail"]["FallaAplicacion"]["Descripcion"]))
	        {
	            //En caso de que falle Validacion del XML muestra el Error
	            self::$timbrado = $aRespuesta["detail"]["FallaAplicacion"]["Descripcion"];
                    return false;
	        }
			elseif (isset ($aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"])) {
                    //Error SOAP InnerException
                    self::$timbrado = $aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"];
                    return false;
                }
                else{
                    if(isset ($aRespuesta["faultstring"]))
                    {
	            // Error Generico
                    $arr = $aRespuesta["faultstring"];
                    $arr = array_values($arr);
                    self::$timbrado=$arr[1];
                    }else{
	            self::$timbrado= "No fue posible timbrar el comprobante.";
	            //echo "TIMBRADO DEBUG: " . $client->getDebug();
                    }
                    return false;
	        }

                /*LOG*/
                //echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
                //echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
            }
        }catch(Exception $ex){self::$timbrado= "Exception: ".$ex->getMessage(); return false;}

    }

    public function setStatusTimbre($rfc,$token,$trsIDN,$trsID,$UUID)
    {
        try
        {            
            $client = new nusoap_client(self::$url_wsTimbrado,self::$proxy_wsTimbrado);
            $err = $client->getError();
            if ($err)
            {                
                self::$descripcion = 'No se pudo acceder al WebService de Timbrado ' . $err;
                return false;
            }else{
                $aParametros = array("RFC" =>$rfc,"Token"=>$token,"TransaccionID"=>$trsIDN,"TransaccionOriginal"=>$trsID,"UUID"=>$UUID);
                $aRespuesta = $client->call("EstatusTimbrado",$aParametros);

                if(isset($aRespuesta["Estatus"]["Codigo"])&&isset($aRespuesta["Estatus"]["Descripcion"]))
                {
                self::$codigo = $aRespuesta["Estatus"]["Codigo"];
                self::$descripcion = $aRespuesta["Estatus"]["Descripcion"];
                return true;

                }elseif (isset ($aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"])) {
                    //Error SOAP InnerException Message
                    self::$descripcion = $aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"];
                    return false;
                }elseif(isset ($aRespuesta["detail"]["ExceptionDetail"]["Type"])){
                    if ($aRespuesta["detail"]["ExceptionDetail"]["Type"] == "Ecodex.WS.BusinessEntities.FallaSesion")
                        self::$descripcion="Falla de Sesion";
                    return false;
                }
                elseif(isset ($aRespuesta["faultstring"]))
                    {
                        // Error Generico
                        $arr = $aRespuesta["faultstring"];
                        $arr = array_values($arr);
                        self::$codigo=$arr[1];
                        self::$descripcion=$arr[1];
                        return false;
                    }
                else{
                    self::$descripcion = "No sepudo obtener el estado del timbrado";
                    return false;
                }
                /*LOG*/
                //echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
                //echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
            }
        }catch(Exception $ex){self::$descripcion = $ex->getMessage(); return false;}
    }
    
    public function setCancela($rfc,$token,$trsID,$UUID)
    {
    try
    {
        $client = new nusoap_client(self::$url_wsTimbrado,self::$proxy_wsTimbrado);
            $err = $client->getError();
            if ($err)
            {
                self::$cancelaResult = 'No se pudo acceder al WebService de Timbrado ' . $err;
                return false;
            }
            else{
                $aParametros=array("RFC"=>$rfc,"Token"=>$token,"TransaccionID"=>$trsID,"UUID"=>$UUID);                
                $aRespuesta = $client->call("CancelaTimbrado",$aParametros);
                if(isset ($aRespuesta["Cancelada"]))
                 {
                    self::$cancelaResult=$aRespuesta["Cancelada"];
                    return true;
                 }
                 elseif(isset ($aRespuesta["detail"]["FallaSesion"]["Descripcion"]))
	        {
	            // En caso de que falle la sesion Muestra el Error
	            self::$cancelaResult = $aRespuesta["detail"]["FallaSesion"]["Descripcion"];
                    return false;
	        }
	        elseif (isset ($aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"])) {
                    //Error SOAP InnerException
                    self::$cancelaResult = $aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"];
                    return false;
                }
                elseif(isset ($aRespuesta["detail"]["ExceptionDetail"]["Type"])){
                    if ($aRespuesta["detail"]["ExceptionDetail"]["Type"] == "Ecodex.WS.BusinessEntities.FallaSesion")
                        self::$cancelaResult="Falla de Sesion";
                    return false;
                }
                else{
                    if(isset ($aRespuesta["faultstring"]))
                    {
	            // Error Generico
                    $arr = $aRespuesta["faultstring"];
                    $arr = array_values($arr);
                    self::$cancelaResult=$arr[1];
                    }else{
	            self::$cancelaResult= "No fue posible cancelar el timbrado";
                    }
                    return false;
                }
                /*LOG*/
                echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
                echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
            }
    }
    catch (Exception $ex)
    {
        self::$cancelaResult = $ex->getMessage();
        return false;
    }
    }

    public function getTimbre($rfc,$token,$trsIDN,$trsID,$UUID)
        {
            try
            {
                $client = new nusoap_client(self::$url_wsTimbrado,self::$proxy_wsTimbrado);
                $err = $client->getError();
                if ($err)
                {
                   self::$timbre = 'No se pudo acceder al WebService de Timbrado ' . $err;                   
                }else{
                        $aParametros = array("RFC" => $rfc,"Token" => $token,"TransaccionID"=>$trsIDN,"TransaccionOriginal"=>$trsID,"UUID"=>$UUID);
                        $aRespuesta = $client->call("ObtenerTimbrado", $aParametros);
                        if (isset($aRespuesta ["ComprobanteXML"]["DatosXML"]))
                        {
                        self::$timbre =$aRespuesta ["ComprobanteXML"]["DatosXML"];
                        }
                        elseif(isset ($aRespuesta["detail"]["FallaSesion"]["Descripcion"]))
                        {
                        // En caso de que falle la sesion Muestra el Error
                        self::$timbre = $aRespuesta["detail"]["FallaSesion"]["Descripcion"];
                        }
                        elseif (isset ($aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"])) {
                        //Error SOAP InnerException
                        self::$timbre = $aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"];
                        }
                        elseif(isset ($aRespuesta["detail"]["ExceptionDetail"]["Type"])){

                            if ($aRespuesta["detail"]["ExceptionDetail"]["Type"] == "Ecodex.WS.BusinessEntities.FallaSesion")
                            self::$timbre="Falla de Sesion";
                            
                        }
                        else{
                            if(isset ($aRespuesta["faultstring"]))
                            {
                            // Error Generico
                            $arr = $aRespuesta["faultstring"];
                            $arr = array_values($arr);
                            self::$timbre=$arr[1];                            
                            }
                            else{
                            self::$timbre= "No se encontro el comprobante solicitado";                            
                            }
                        }
                        /*LOG*/
                        //echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
                        //echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
                    }
            }catch(Exception $ex)
            {
                self::$timbre = $ex->getMessage();                
            }
            return self::$timbre;
        }

    public function setQR($rfc, $token, $trsID, $UUID)
    {
         try
            {
                //$ExceptionWs = new WebServiceException();
                $client = new nusoap_client(self::$url_wsTimbrado,self::$proxy_wsTimbrado);
                $err = $client->getError();
                if ($err)
                {
                   self::$qr = 'No se pudo acceder al WebService de Timbrado ' . $err;
                   return false;

                }else{
                        $aParametros = array("RFC" => $rfc,"Token" => $token,"TransaccionOriginal"=>$trsID,"UUID"=>$UUID);
                        $aRespuesta = $client->call("ObtenerQRTimbrado", $aParametros);
                        
                        if (isset($aRespuesta ["QR"]["Imagen"]))
                        {
                        self::$qr =$aRespuesta ["QR"]["Imagen"];
                        return true;
                        }
                        elseif(isset ($aRespuesta["detail"]["FallaSesion"]["Descripcion"]))
                        {                            
                            // En caso de que falle la sesion Muestra el Error
                            self::$qr = $aRespuesta["detail"]["FallaSesion"]["Descripcion"];
                            return false;
                        }
                        elseif (isset ($aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"])) {
                            //Error SOAP InnerException
                            self::$qr= $aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"];
                            return false;
                        }
                        elseif(isset ($aRespuesta["detail"]["ExceptionDetail"]["Type"])){
                            if ($aRespuesta["detail"]["ExceptionDetail"]["Type"] == "Ecodex.WS.BusinessEntities.FallaSesion")
                            self::$qr="Falla de Sesion";
                            return false;
                        }
                        else{
                            if(isset ($aRespuesta["faultstring"]))
                            {
                            // Error Generico
                            $arr = $aRespuesta["faultstring"];
                            $arr = array_values($arr);
                            self::$qr=$arr[1];
                            }else{
                            self::$qr= "No se pudo generar el código QR del comprobante";
                            }
                            return false;
                        }
                        /*LOG*/
                        //echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
                        //echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
                }
                }catch(Exception $ex)
                {
                    self::$qr = $ex->getMessage();
                    return false;
                }                
    }
    
    public function setFolios($rfc,$trsID,$token,$timbresAsignar){
    	try
    	{
    		$client = new nusoap_client(self::$url_wsClientes);
    		$err = $client->getError();
    		if ($err)
    		{
    			self::$clientes = 'No se pudo acceder al WebService de Timbrado - Asignacion folios' . $err;
    			return false;
    
    		}else{
    			//$aParametros = array("cfdi:SolicitudAsignacionTimbres" => array("cfdi:RFC" => $rfc,"cfdi:TimbresAsignar" => $timbresAsignar, "cfdi:Token" => $token,"cfdi:TransaccionID"=>$trsID));
    			$aParametros = array("cfdi:RFC" => $rfc,"cfdi:TimbresAsignar" => $timbresAsignar, "cfdi:Token" => $token,"cfdi:TransaccionID"=>$trsID);
    			$aRespuesta = $client->call("cfdi:SolicitudAsignacionTimbres", $aParametros);
    			echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    			echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    			
    			/*if (isset($aRespuesta ["ComprobanteXML"]["DatosXML"]))
    			{
    				self::$clientes =$aRespuesta ["ComprobanteXML"]["DatosXML"];
    				return true;
    			}
    						elseif(isset ($aRespuesta["detail"]["FallaSesion"]["Descripcion"]))
    			{
    			// En caso de que falle la sesion Muestra el Error
    					self::$clientes = $aRespuesta["detail"]["FallaSesion"]["Descripcion"];
    					return false;
    			}
    			elseif(isset ($aRespuesta["detail"]["FallaValidacion"]["Descripcion"]))
    			{
    			//En caso de que falle Validacion del XML muestra el Error
    			self::$clientes = $aRespuesta["detail"]["FallaValidacion"]["Descripcion"];
    				return false;
    			}
    			elseif(isset ($aRespuesta["detail"]["FallaAplicacion"]["Descripcion"]))
    			{
    			//En caso de que falle Validacion del XML muestra el Error
    			self::$clientes = $aRespuesta["detail"]["FallaAplicacion"]["Descripcion"];
    					return false;
    		}
			elseif (isset ($aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"])) {
    			//Error SOAP InnerException
    			self::$clientes = $aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"];
    			return false;
    		}
    		else{
    		if(isset ($aRespuesta["faultstring"]))
    		{
    				// Error Generico
    				$arr = $aRespuesta["faultstring"];
    				$arr = array_values($arr);
    				self::$clientes=$arr[1];
    		}else{
    						self::$clientes= "No fue posible timbrar el comprobante.";
    						//echo "TIMBRADO DEBUG: " . $client->getDebug();
    						}
    							return false;
    						}
    
    								/*LOG*/
    								//echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    										//echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    		}
    	}catch(Exception $ex){self::$clientes= "Exception: ".$ex->getMessage(); return false;}
    
    }
}
?>
