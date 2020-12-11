<?php
class Seguridad {    
	
    private static  $url_wsSeguridad= "http://servicios.ecodex.com.mx:4040/ServicioSeguridad.svc?wsdl"; //---> SSL
    private static $proxy_wsSeguridad = 'https://servicios.ecodex.com.mx:4043/ServicioSeguridad.svc'; //---> proxy SSL
	//private static  $url_wsSeguridad= "http://pruebas.ecodex.com.mx:2044/ServicioSeguridad.svc?wsdl"; //---> SSL
	//private static $proxy_wsSeguridad = 'https://servicios.ecodex.com.mx:2045/ServicioSeguridad.svc?wsdl'; //---> proxy SSL
    private static $token;  


    public function  __construct() {
        //echo "Se creo instancia de Token...<br>";
    }

    public function getToken(){
        return self::$token;
    }


    public function setToken($rfc,$trsID,$integrador){
        try
        {
            $client = new nusoap_client(self::$url_wsSeguridad,self::$proxy_wsSeguridad);
            $err = $client->getError();
            if ($err)
            {
                self::$token = 'No se pudo acceder al WebService Token '. $err;                
                return false;
            }else
            {
                $aParametros = array("RFC" => $rfc,"TransaccionID"=>$trsID);
                $aRespuesta = $client->call("ObtenerToken", $aParametros);
                /*LOG*/
                //echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
                //echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
                /**/
                if (isset($aRespuesta["Token"]))
                {
                $tohash = $integrador."|".$aRespuesta["Token"];
                $tohash = utf8_encode($tohash);
                $toHash2 = sha1($tohash);
                self::$token = $toHash2;
                return true;
                }
                elseif(isset ($aRespuesta["detail"]["FallaSesion"]["Descripcion"]))
	        {
	            // En caso de que falle la sesion Muestra el Error
	            self::$token = $aRespuesta["detail"]["FallaSesion"]["Descripcion"];                    
                    return false;
	        }
	        elseif (isset ($aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"])) {
                    //Error SOAP InnerException
                    self::$token = $aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"];                    
                    return false;
                }
                else{
                    if(isset ($aRespuesta["faultstring"]))
                    {
	            // Error Generico
                    $arr = $aRespuesta["faultstring"];
                    $arr = array_values($arr);
                    self::$token=$arr[1];                    
                    }else{
	            self::$token= "No fue posible generar el Token";
                return false;
                    }
	        }
            }            
        }catch(Exception $ex)
        {
            self::$token= "Exception: ".$ex->getMessage();            
            return false;
        }
        
    }

}
?>
