<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

            include '../lib/nusoap.php';
            include '../class/Seguridad.class.php';
            //include '../class/Cliente.class.php';
            include_once '../class/Timbrado.class.php';

            //$integrador = "2b3a8764-d586-4543-9b7e-82834443f219";
            $integrador = "4f7735ae-4449-4c5a-88d9-2f4299240bb1";
            $rfc = $_POST['rfc'];  
            $timbresAsignar= $_POST['timbresAsignar'];       
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
                    $Timbra = new Timbrado();
                    $asignar= $Timbra->setFolios($rfc, $trsID, $getToken,$timbresAsignar);
                    if($asignar)
                    {
	                    $getAsignacion= $Timbra->getAsignacion();
	                    $log .=  "<p><TEXTAREA ID='' cols='100' rows='15'>Timbres solicitados..".$getAsignacion."</TEXTAREA></p>";
                    }else{
                    	$log .= "ERROR:".$asignar;
                    }
            }
?>
