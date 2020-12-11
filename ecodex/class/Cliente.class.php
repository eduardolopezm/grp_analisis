<?php
class Cliente {
    private static $url_wsCliente="https://servicios.ecodex.com.mx:4043/ServicioClientes.svc?wsdl"; //---> SSL
    private static $proxy_wsCliente = "https://servicios.ecodex.com.mx:4043/ServicioClientes.svc"; //---> proxy
    private static $results = array("assigned"=>"","remaining"=>"","used"=>"","startDate"=>"","endDate"=>"","description"=>"");
    private static $errorMensaje;
    private static $avisos;


    public static function getStatusClient()
    {
        return self::$results;
    }

    public static function getAvisosClient()
    {
        if(!empty(self::$avisos))
            return self::$avisos;
        else
            return false;
    }

    public static function getErrorMensaje()
    {
        if(!empty(self::$errorMensaje))
            return self::$errorMensaje;
        else
            return false;
    }

    public static function setStatusClient($rfc,$token,$trsID)
    {
      try
        {
            $client = new nusoap_client(self::$url_wsCliente,self::$proxy_wsCliente);
            $err = $client->getError();
            if ($err)
            {
                self::$errorMensaje = 'No se pudo acceder al WebService de Cliente ' . $err;
                return false;
            }else{
                $aParametros = array("RFC" =>$rfc,"Token"=>$token,"TransaccionID"=>$trsID);
                $aRespuesta = $client->call("EstatusCuenta",$aParametros);
                /*LOG*/
                //echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
                //echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
                /**/
                
                if(isset($aRespuesta["Estatus"]))
                {
                self::$results["assigned"]=$aRespuesta["Estatus"]["TimbresAsignados"];
                self::$results["remaining"]=$aRespuesta["Estatus"]["TimbresDisponibles"];
                self::$results["used"]= $aRespuesta["Estatus"]["TimbresAsignados"] - $aRespuesta["Estatus"]["TimbresDisponibles"];
                self::$results["startDate"]=$aRespuesta["Estatus"]["FechaInicio"];
                self::$results["endDate"]=$aRespuesta["Estatus"]["FechaFin"];
                self::$results["description"]=$aRespuesta["Estatus"]["Descripcion"];
                return true;
                }
                elseif(isset ($aRespuesta["detail"]["FallaSesion"]["Descripcion"]))
	        {
	            // En caso de que falle la sesion Muestra el Error                    
	            self::$errorMensaje = "Description: ". $aRespuesta["detail"]["FallaSesion"]["Descripcion"];
                    self::$errorMensaje .= "\r\nEstatus: ". $aRespuesta["detail"]["FallaSesion"]["Estatus"];                    
                    return false;
	        }
                elseif (isset ($aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"])) {
                    //Error SOAP InnerException
                    self::$errorMensaje = $aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"];
                    return false;
                }
                else{
                    if(isset ($aRespuesta["faultstring"]))
                    {
	            // Error Generico
                    $arr = $aRespuesta["faultstring"];
                    $arr = array_values($arr);
                    self::$errorMensaje=$arr[1];
                    }else{
	            self::$errorMensaje= "Ocurrio algun error...";
                    return false;
                    }
                }                                                 
            }
        }
        catch (Exception $ex)
        {
            self::$errorMensaje = $ex->getMessage();
            return false;
        }

    }

    public static function setAvisosClient($rfc,$token,$trsID)
    {
      try
        {
            $client = new nusoap_client(self::$url_wsCliente,self::$proxy_wsCliente);
            $err = $client->getError();
            if ($err)
            {
                self::$descripcion = 'No se pudo acceder al WebService de Cliente ' . $err;
                return false;
            }else{
                $aParametros = array("RFC" =>$rfc,"Token"=>$token,"TransaccionID"=>$trsID);
                $aRespuesta = $client->call("AvisosNuevos",$aParametros);
                /*LOG*/
                //echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
                //echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
                /**/

                if(isset($aRespuesta["Avisos"]))
                {                    
                    self::$avisos = $aRespuesta["Avisos"];
                return true;
                }
                elseif(isset ($aRespuesta["detail"]["FallaSesion"]["Descripcion"]))
	        {
	            // En caso de que falle la sesion Muestra el Error                    
	            self::$errorMensaje = "Description: ". $aRespuesta["detail"]["FallaSesion"]["Descripcion"];
                    self::$errorMensaje .= "\r\nEstatus: ". $aRespuesta["detail"]["FallaSesion"]["Estatus"];                    
                    return false;
	        }
                elseif (isset ($aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"])) {
                    //Error SOAP InnerException
                    self::$errorMensaje = $aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"];
                    return false;
                }
                else{
                    if(isset ($aRespuesta["faultstring"]))
                    {
	            // Error Generico
                    $arr = $aRespuesta["faultstring"];
                    $arr = array_values($arr);
                    self::$errorMensaje=$arr[1];
                    }else{
	            self::$errorMensaje= "Ocurrio algun error...";
                    return false;
                    }
                }
            }
        }
        catch (Exception $ex)
        {
            self::$errorMensaje = $ex->getMessage();
            return false;
        }

    }
}
?>
