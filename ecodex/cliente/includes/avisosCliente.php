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
                    $setAvisos = $Cliente->setAvisosClient($rfc, $getToken, $trsID);
                    $getAvisos = $Cliente->getAvisosClient();
                    //print_r( $status);
                    if($setAvisos)
                    {
                        if(!empty ($getAvisos))
                        {
                            $displayAvisos="";
                            $num_avisos = count($getAvisos["Aviso"]);
                            foreach ($getAvisos as $avisos)
                                {
                                    if($num_avisos==0)
                                        $displayAvisos .="No tiene Avisos";

                                    if($num_avisos==1){
                                    $displayAvisos .= "Vigencia: ". $avisos["Vigencia"]."\t||\tMensaje: ".$avisos["Mensaje"]."\n";
                                    }
                                    elseif($num_avisos>1){

                                    foreach ($avisos as $aviso)
                                        {
                                            $displayAvisos .="  Vigencia: ". $aviso["Vigencia"]."\t||\tMensaje: ".$aviso["Mensaje"]."\n";
                                        }
                                    }
                                }
                        }
                    }else if(!$setAvisos){
                        $errorMensaje = $Cliente->getErrorMensaje();
                        $log .= "Avisos Cliente:<br> ". $errorMensaje;
                    }                             
            }
?>
