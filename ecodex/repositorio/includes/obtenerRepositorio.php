<?php
            include '../lib/nusoap.php';
            include_once '../class/Seguridad.class.php';
            include_once '../class/Repositorio.class.php';

            $integrador = "2b3a8764-d586-4543-9b7e-82834443f219";
            $rfc = $_POST['rfc'];
            $UUID = $_POST['UUID'];
            if(empty ($_POST['trsID']))
            {$trsID = 0;
            }else{$trsID = $_POST['trsID'];}
            $log = "No. de Integrador: ".$integrador."<br>";
            $log .= "RFC: ".$rfc."<br>";
            $log .= "UUID: ".$UUID."<br>";

/*
 * Seguridad
 */
                $token = new Seguridad();
                $trsIDN = rand(1, 10000);
                $log .= "Transaction Id: ".$trsIDN."<br>";
                $generaToken = $token->setToken($rfc, $trsIDN, $integrador);
                $getToken = $token->getToken();
                $log .= "Token: ".$getToken."<br>";               

if($generaToken)
{
            
                $Repositorio = new Repositorio();
                $trsIDN = rand(1, 10000);
                $timbre = $Repositorio->getComprobante( $rfc, $getToken, $trsIDN, $trsID, $UUID);
                //$log .= "Timbre: ".$timbre."<br>";
                

         
}
?>
