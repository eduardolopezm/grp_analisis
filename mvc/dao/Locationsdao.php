<?php
 
class Locationsdao{
    
    function Getlocations($tagref){
            $pathprefix = "../.././";
            require_once $pathprefix . 'core/ModeloBase.php';

            $UserID = $_SESSION["UserID"];
            $success = true;
            $msgerror = "";
            $typeerror = "";
            $codeerror = 0;
            $sql = "";
            $arrlocations = array();

            $modelo = new ModeloBase;

            $sql = "SELECT l.loccode, l.locationname 
                    FROM locations l
                    INNER JOIN tags t ON l.tagref = t.tagref
                    INNER JOIN sec_loccxusser s ON l.loccode= s.loccode 
                    WHERE s.userid = '". $_SESSION["UserID"] ."'";
                    //AND t.tagref= '".$tagref."'";
            $sql = "SELECT locations.loccode, CONCAT(locations.loccode, ' - ',locations.locationname) as locationname
            FROM locations
            WHERE locations.estatus = 'Activo' AND locations.tipo = 'ObjetoPrincipal'";
            
            $resp = $modelo->ejecutarsql($sql);
            $message = "";

            if ($resp == true and !is_array($resp)){
                    $success = false;
                    $message = _('No existen localidades asignadas para el Usuario ' . $_SESSION["UserID"]);

            }else{
                    if ($resp == false){
                            $msgerror = "ERROR EN LA CONSULTA";
                            $typeerror = "MYSQL ERROR";
                            $codeerror = "001";

                    }else{

                            for($xx = 0; $xx < count($resp); $xx++){
                                    $arrlocation = array(
                                            "loccode" => $resp[$xx]['loccode'],
                                            "locationname" => $resp[$xx]['locationname'],
                                    );
                                    //$response['data'][] = $arrdebtor;		
                                    array_push($arrlocations, $arrlocation);
                            }
                    }

            }

            $response['success'] = $success;
            $response['error']['msgerror'] = $msgerror;
            $response['error']['typeerror'] = $typeerror;
            //$response['error']['codeerror'] = $codeerror;
            $response['error']['codeerror'] = $sql;
            $response['data']['message'] = $message;
            $response['data'] = $arrlocations;

            //var_dump($custbranch);
            //header('Content-type: application/json; charset=utf-8');
            //return json_encode($response, JSON_FORCE_OBJECT);
            return $response;


    }

    function GetlocationsPayment($loccode){
        $pathprefix = "../.././";
        require_once $pathprefix . 'core/ModeloBase.php';

        $UserID = $_SESSION["UserID"];
        $success = true;
        $msgerror = "";
        $typeerror = "";
        $codeerror = 0;
        $sql = "";
        $arrlocations = array();

        $modelo = new ModeloBase;

        $sql = "SELECT
        locations.id_pago as metodoPago,
        sat_paymentmethodssat.paymentname as metodoPagoName
        FROM locations
        LEFT JOIN sat_paymentmethodssat ON sat_paymentmethodssat.paymentid = locations.id_pago
        WHERE
        locations.loccode = '".$loccode."'";
        
        $resp = $modelo->ejecutarsql($sql);
        $message = "";

        if ($resp == true and !is_array($resp)){
            $success = false;
            $message = _('No existen metodos de pago para '.$loccode);
        }else{
            if ($resp == false){
                $msgerror = "ERROR EN LA CONSULTA";
                $typeerror = "MYSQL ERROR";
                $codeerror = "010";
            }else{
                for($xx = 0; $xx < count($resp); $xx++){
                    $arrlocation = array(
                        "metodoPago" => $resp[$xx]['metodoPago'],
                        "metodoPagoName" => $resp[$xx]['metodoPagoName'],
                    );
                    //$response['data'][] = $arrdebtor;     
                    array_push($arrlocations, $arrlocation);
                }
            }
        }

        $response['success'] = $success;
        $response['error']['msgerror'] = $msgerror;
        $response['error']['typeerror'] = $typeerror;
        //$response['error']['codeerror'] = $codeerror;
        $response['error']['codeerror'] = $sql;
        $response['data']['message'] = $message;
        $response['data'] = $arrlocations;

        //var_dump($custbranch);
        //header('Content-type: application/json; charset=utf-8');
        //return json_encode($response, JSON_FORCE_OBJECT);
        return $response;
    }

    function getLocationbyId($loccode){
        $pathprefix = "../.././";
        require_once $pathprefix . 'core/ModeloBase.php';
        
        $UserID = $_SESSION['UserID'];
        $success = true;
        $msgerror = "";
        $typeerror = "";
        $codeerror = 0;
        $sql = "";
        $arrlocations = array();
        $modelo = new ModeloBase;

        $sql = "SELECT l.loccode, l.locationname 
                FROM locations l
                WHERE l.loccode = '" . $loccode . "'"; 

        $resp = $modelo->ejecutarsql($sql);
        $message = "";

        if ($resp == true and !is_array($resp)){
            $success = false;
            $message = _('No existen localidades asignadas para el Usuario '.$UserID);
        }else{
            if ($resp == false){
                $msgerror = "ERROR EN LA CONSULTA";
                $typeerror = "MYSQL ERROR";
                $codeerror = "001";
            }else{
                for($xx = 0; $xx < count($resp); $xx++){
                    $arrlocation = array(
                            "loccode" => $resp[$xx]['loccode'],
                            "locationname" => $resp[$xx]['locationname'],
                    );
                    //$response['data'][] = $arrdebtor;		
                    array_push($arrlocations, $arrlocation);
                }
            }
        }

        $response['success'] = $success;
        $response['error']['msgerror'] = $msgerror;
        $response['error']['typeerror'] = $typeerror;
        $response['error']['codeerror'] = $sql;
        $response['data']['message'] = $message;
        $response['data'] = $arrlocations;

        return $response;
    }

}


?>