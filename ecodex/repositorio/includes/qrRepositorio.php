<?php
            include '../lib/nusoap.php';
            include_once '../class/Seguridad.class.php';
            include_once '../class/Repositorio.class.php';

            $integrador = "2b3a8764-d586-4543-9b7e-82834443f219";
            $rfc = $_POST['rfc'];
            $UUID = $_POST['UUID'];
            $trsID;
            $log = "No. de Integrador: ".$integrador."<br>";
            $log .= "RFC: ".$rfc."<br>";
            $log .= "UUID: ".$UUID."<br>";

/*
 * Seguridad
 */
                $token = new Seguridad();
                $trsID = rand(1, 10000);
                $log .= "Transaction Id: ".$trsID."<br>";
                $generaToken = $token->setToken($rfc, $trsID, $integrador);
                $getToken = $token->getToken();
                $log .= "Token: ".$getToken."<br>";               

if($generaToken)
{

                $Repositorio = new Repositorio();
                $trsID = rand(1, 10000);                
                
                $generaqr = $Repositorio->setQR($rfc, $getToken, $trsID, $UUID);
                $qr = $Repositorio->getQR();
                $log .="QR: ".$qr;
                if($generaqr)
                {
                $folder = "img_QRs".DIRECTORY_SEPARATOR;
                    $s_Filename = "$folder"."$UUID.png";
                    file_put_contents($s_Filename, base64_decode($qr));
                }
}

?>
