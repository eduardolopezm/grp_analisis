<?php
class Repositorio {
    private static $url_wsRepositorio="https://servicios.ecodex.com.mx:4043/ServicioRepositorio.svc"; //---> SSL
    private static $proxy_wsRepositorio = "https://servicios.ecodex.com.mx:4043/ServicioRepositorio.svc"; //---> proxy
    private static $codigo;
    private static $descripcion;
    private static $qr;
    private static $cancelaResult;
    private static $comprobante;

    public function getQR()
    {
        return self::$qr;
    }
    public function getCancela()
    {
        return self::$cancelaResult;
    }
    
    public function getCodigoEstatus()
    {
    return self::$codigo;
    }

    public function getDescripcionEstatus()
    {
    return self::$descripcion;
    }

    public function setQR($rfc, $token, $trsID, $UUID)
    {
         try
            {
                //$ExceptionWs = new WebServiceException();
                $client = new nusoap_client(self::$url_wsRepositorio,self::$proxy_wsRepositorio);
                $err = $client->getError();
                if ($err)
                {
                   self::$qr = 'No se pudo acceder al WebService de Comprobante ' . $err;
                   return false;

                }else{
                        $aParametros = array("RFC" => $rfc,"Token" => $token,"TransaccionOriginal"=>$trsID,"UUID"=>$UUID);
                        $aRespuesta = $client->call("ObtenerQR", $aParametros);
                        /*LOG*/
                        //echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
                        //echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
                        /**/

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
                }
                }catch(Exception $ex)
                {
                    self::$qr = $ex->getMessage();
                    return false;
                }                
    }

    public function setCancela($rfc,$token,$trsID,$UUID)
    {
    try
    {
        $client = new nusoap_client(self::$url_wsRepositorio,self::$proxy_wsRepositorio);
            $err = $client->getError();
            if ($err)
            {
                self::$cancelaResult = 'No se pudo acceder al WebService de Comprobante ' . $err;
                return false;
            }
            else{
                $aParametros=array("RFC"=>$rfc,"Token"=>$token,"TransaccionID"=>$trsID,"UUID"=>$UUID);
                $aRespuesta = $client->call("CancelaComprobante",$aParametros);
                /*LOG*/
                //echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
                //echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
                /**/
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
	            self::$cancelaResult= "No fue posible cancelar el comprobante";
                    }
                    return false;
                }                
            }
    }
    catch (Exception $ex)
    {
        self::$cancelaResult = $ex->getMessage();
        return false;
    }
    }

    public function setStatusComprobante($rfc,$token,$trsIDN,$trsID,$UUID)
    {
        try
        {
            $client = new nusoap_client(self::$url_wsRepositorio,self::$proxy_wsRepositorio);
            $err = $client->getError();
            if ($err)
            {
                self::$descripcion = 'No se pudo acceder al WebService de Repositorio ' . $err;
                return false;
            }else{
                $aParametros = array("RFC" =>$rfc,"Token"=>$token,"TransaccionID"=>$trsIDN,"TransaccionOriginal"=>$trsID,"UUID"=>$UUID);
                $aRespuesta = $client->call("EstatusComprobante",$aParametros);
                /*LOG*/
                //echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
                //echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
                /**/

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
                    self::$descripcion = "No sepudo obtener el estado del comprobante";
                    return false;
                }                
            }
        }catch(Exception $ex){self::$descripcion = $ex->getMessage(); return false;}
    }

    public function getComprobante($rfc,$token,$trsIDN,$trsID,$UUID)
        {
            try
            {
               $client = new nusoap_client(self::$url_wsRepositorio,self::$proxy_wsRepositorio);
            $err = $client->getError();
            if ($err)
            {
               self::$comprobante = 'No se pudo acceder al WebService de Comprobante ' . $err;

            }else{
                    $aParametros = array("RFC" => $rfc,"Token" => $token,"TransaccionID"=>$trsIDN,"TransaccionOriginal"=>$trsID,"UUID"=>$UUID);
                    $aRespuesta = $client->call("ObtenerComprobante", $aParametros);
                    /*LOG*/
                    //echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
                    //echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
                    /**/
                    if (isset($aRespuesta ["ComprobanteXML"]["DatosXML"]))
                    {
                    self::$comprobante =$aRespuesta ["ComprobanteXML"]["DatosXML"];
                    }
                    elseif(isset ($aRespuesta["detail"]["FallaSesion"]["Descripcion"]))
	        {
	            // En caso de que falle la sesion Muestra el Error
	            self::$comprobante = $aRespuesta["detail"]["FallaSesion"]["Descripcion"];
	        }
	        elseif (isset ($aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"])) {
                    //Error SOAP InnerException
                    self::$comprobante = $aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"];
                }
                elseif(isset ($aRespuesta["detail"]["ExceptionDetail"]["Type"])){
                    if ($aRespuesta["detail"]["ExceptionDetail"]["Type"] == "Ecodex.WS.BusinessEntities.FallaSesion")
                        self::$comprobante="Falla de Sesion";
                }
                else{
                    if(isset ($aRespuesta["faultstring"]))
                    {
	            // Error Generico
                    $arr = $aRespuesta["faultstring"];
                    $arr = array_values($arr);
                    self::$comprobante=$arr[1];
                    }else{
	            self::$comprobante= "No se encontro el comprobante solicitado.";
                    }
                }                    
            }
            }catch(Exception $ex)
            {
                self::$comprobante = $ex->getMessage();
            }
            return self::$comprobante;
        }
}
?>
