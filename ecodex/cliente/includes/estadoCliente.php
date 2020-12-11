<?php
            include '../lib/nusoap.php';
            include '../class/Seguridad.class.php';
            include '../class/Cliente.class.php';

            $integrador = "2b3a8764-d586-4543-9b7e-82834443f219";
            $rfc = $_POST['rfc'];            
            //$log = "No. de Integrador: ".$integrador."<br>";
            $log = "RFC: ".$rfc."<br>";
            //$log .= "UUID: ".$UUID."<br>";

/*
 * Seguridad
 */
                $token = new Seguridad();
                $trsID = rand(1, 10000);
                $log .= "Transaction Id: ".$trsID."<br>";
                $generaToken = $token->setToken($rfc, $trsID, $integrador);
                $getToken = $token->getToken();
                $log .= "Token: ".$getToken."<br>";                

/*
 * Servicio Cliente
 */
            if($generaToken)
            {

                    $Cliente = new Cliente();
                    $trsID = rand(1, 10000);
                    $setStatus = $Cliente->setStatusClient($rfc, $getToken, $trsID);
                    $status = $Cliente->getStatusClient();
                    //print_r( $status);
                    if($setStatus)
                    {
                        $assignedEstatus=$status["assigned"];
                        $remainingEstatus=$status["remaining"];
                        $usedEstatus=$status["used"];
                        $startDateEstatus=$status["startDate"];
                        $endDateEstatus=$status["endDate"];
                        $descriptionEstatus=$status["description"];                        
                    }else if(!$setStatus){                        
                        $errorMensaje = $Cliente->getErrorMensaje();
                        $log .= "Estado Cliente:<br> ". $errorMensaje;
                    }                             
            }
?>
