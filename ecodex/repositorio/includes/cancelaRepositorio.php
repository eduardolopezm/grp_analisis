<?php
            include '../lib/nusoap.php';
            include_once '../class/Seguridad.class.php';
            include_once '../class/Repositorio.class.php';

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
 * Servicio Repositorio
 */
            if($generaToken)
            {

                    $Repositorio = new Repositorio();
                    $trsID = rand(1, 10000);
                    $Repositorio->setCancela( $rfc, $getToken, $trsID, $UUID);
                    $cancelaResultado = $Repositorio->getCancela();
                    $log .= "Resultado Cancela: ".$cancelaResultado."<br>";
              
            }else{
                $log .= "No se pudo continuar con el  proceso.";
            }

?>
