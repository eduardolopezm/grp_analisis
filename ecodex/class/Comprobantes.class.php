<?php
class Comprobantes {
    private static $url_wsComprobante="https://servicios.ecodex.com.mx:4043/ServicioComprobantes.svc"; //---> SSL
    private static $proxy_wsComprobante = "http://pruebas.ecodex.com.mx:2045/ServicioComprobantes.svc?wsdl"; //---> proxy    
    private static $XMLSellado;

    public function  __construct() {
        //echo "Se creo instancia de SellaTimbraXML";

    }

    public function getXMLSellado(){                
        return self::$XMLSellado;
    }

    public function setXMLSellado($ComprobanteXML,$rfc,$trsID,$token){
		    

    	try{
            $client = new nusoap_client(self::$url_wsComprobante,self::$proxy_wsComprobante);
            $err = $client->getError();
            if ($err)
            {
                self::$XMLSellado = 'No se pudo acceder al WebService de SellaTimbraXML ' . $err;
                return false;
            }else{
	        $aParametros = array("ComprobanteXML" => array("DatosXML"=>$ComprobanteXML) ,"Token" => $token,"RFC" => $rfc,"TransaccionID"=>$trsID);
	        $aRespuesta = $client->call("SellaTimbraXML", $aParametros);
                /*LOG*/
                //echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
                //echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
                /**/
	        
	        if(isset ($aRespuesta ["ComprobanteXML"]["DatosXML"]))
	        {                       
	            self::$XMLSellado =utf8_encode($aRespuesta ["ComprobanteXML"]["DatosXML"]);
                    return true;
	        }
	        elseif(isset ($aRespuesta["detail"]["FallaSesion"]["Descripcion"]))
	        {
	            // En caso de que falle la sesion Muestra el Error
	            self::$XMLSellado = $aRespuesta["detail"]["FallaSesion"]["Descripcion"];
                    return false;
	        }
	        elseif(isset ($aRespuesta["detail"]["FallaValidacion"]["Descripcion"]))
	        {
	            //En caso de que falle Validacion del XML muestra el Error
	            self::$XMLSellado = $aRespuesta["detail"]["FallaValidacion"]["Descripcion"];
                    return false;
	        }  
			elseif(isset ($aRespuesta["detail"]["FallaAplicacion"]["Descripcion"]))
	        {
	            //En caso de que falle Validacion del XML muestra el Error
	            self::$timbrado = $aRespuesta["detail"]["FallaAplicacion"]["Descripcion"];
                    return false;
	        }
			elseif (isset ($aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"])) {
                    //Error SOAP InnerException Message
                    self::$XMLSellado = $aRespuesta["detail"]["ExceptionDetail"]["InnerException"]["Message"];
                    return false;
                }
                else{
                    if(isset ($aRespuesta["faultstring"]))
                    {
	            // Error Generico
                    $arr = $aRespuesta["faultstring"];
                    $arr = array_values($arr);
                    self::$XMLSellado=$arr[1];
                    }else{
	            self::$XMLSellado= "Ocurrio algun error...";}
                    return false;
	        }                            
            }
        }
        catch(Exception $ex){self::$XMLSellado= $ex->getMessage(); return false;}
    }
    
}


?>
