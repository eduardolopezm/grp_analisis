<?php
            include '../lib/nusoap.php';
            include_once '../class/Seguridad.class.php';
            include_once '../class/Timbrado.class.php';           

            $integrador = "2b3a8764-d586-4543-9b7e-82834443f219";
            $rfc = $_POST['rfc'];
            $UUID = $_POST['UUID'];
            $log = "No. de Integrador: ".$integrador."<br>";
            $log .= "RFC: ".$rfc."<br>";
            $log .= "UUID: ".$UUID."<br>";

/*
 * Servicio Seguridad
 */

                $token = new Seguridad();
                $trsID = rand(1, 10000);
                $log .= "Transaction Id: ".$trsID."<br>";
                $generaToken = $token->setToken($rfc, $trsID, $integrador);
                $getToken = $token->getToken();
                $log .= "Token: ".$getToken."<br>";                
            

/*
 * Servicio Timbrado
 */
            if($generaToken)
            {

                    $Timbra = new Timbrado();
                    $trsID = rand(1, 10000);
                    $Timbra->setCancela( $rfc, $getToken, $trsID, $UUID);                    
                    $cancelaResultado = $Timbra->getCancela();
                    $log .= "Resultado Cancela: ".$cancelaResultado."<br>";
              
            }else{
                $log .= "No se pudo continuar con el  proceso.";
            }

?>
