<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
	        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">        
        <title></title>
    
<?php
        if(isset($_POST["carga_archivo"]))
          {
/*
 * Cargar Archivo
 */
            //Datos del arhivo
            $nombre_archivo = $_FILES['userfile']['name'];
            $tipo_archivo = $_FILES['userfile']['type'];
            //--
            if (!(strpos($tipo_archivo, "xml"))) {
                    echo "<br>El archivo debe ser XML";
					echo '<script type="text/javascript">
                        window.location ="index.php";
                        </script>';
            }else
             {
                echo "Cargando Archivo...<br>";
                 if (isset($_FILES["userfile"]) && is_uploaded_file($_FILES['userfile']['tmp_name']))
                 {
                     //Leer y almacenar cadena de archivo XML
                    $fp = fopen($_FILES['userfile']['tmp_name'],"r") or die("No se pudo leer el archivo");
                    $comprobante="";
                    while($line = fgets($fp))
                    {
                        $comprobante .= $line;
                    }                    
					if(SearchBOM($comprobante))
					{
						$comprobante = rmBOM($comprobante);
					}
					
                    fclose($fp);
                    //---
                    echo "Archivo Procesado...<br>";
                 }
                 else
                     {
                     echo "Ocurrio algún error al Cargar el archivo, intentelo nuevamente...";
                     echo '<script type="text/javascript">
                        window.location ="index.php";
                        </script>';
                     }
             }
         }else{echo '<script type="text/javascript">
                        window.location ="index.php";
                        </script>';}
/* * */
?>

</head>
    <body>
<?php

/*
 * Se implemento la herramienta NuSOAP para consumir el WebService
 * http://sourceforge.net/projects/nusoap/
 */

include_once 'lib/nusoap.php';
include_once 'class/Seguridad.class.php';
include_once 'class/Comprobantes.class.php';
include_once 'class/Timbrado.class.php';


if(isset($_POST["integrador"]))
{
	$integrador=$_POST["integrador"];
}else{
	$integrador = "2b3a8764-d586-4543-9b7e-82834443f219";
	//$integrador = "4f7735ae-4449-4c5a-88d9-2f4299240bb1";
}

if(isset($_POST["rfc"]))
{
	$rfc =$_POST["rfc"];
}else{
	$rfc = "AAA010101AAA";
}

echo 'Integrador :'.$integrador;
echo "<br>";
echo 'RFC :'.$rfc.'<br>';

/*
 * Generar Token
 */
$token = new Seguridad();
$trsID = rand(1, 10000);
$generaToken = $token->setToken($rfc, $trsID, $integrador);
$getToken = $token->getToken();

echo "Token Generado: ";
print_r($getToken);

if($generaToken)
{
            
if($_POST['opcion']==1){


                $ComprobanteXML = $comprobante;                
                                
                $SellaTimbra = new Comprobantes();
                $trsID = rand(1, 10000);
                $sellar = $SellaTimbra->setXMLSellado( $ComprobanteXML, $rfc, $trsID, $getToken);                                
                $getXmlSellado = $SellaTimbra->getXMLSellado();

                echo "<p><TEXTAREA ID='cadenaXML' cols='100' rows='15'>".$getXmlSellado."</TEXTAREA></p>";
                
                if($sellar)
                {
                $cabeceraXML = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
                $getXmlSellado = $cabeceraXML.$getXmlSellado;
                $archivo_sellado = "temp".DIRECTORY_SEPARATOR."Sellado-".$nombre_archivo;
                $file = @fopen($archivo_sellado, "w+");
                $string = utf8_encode($getXmlSellado);
                @fwrite($file,$string);
                @fclose($file);

                echo '
                    <form action="'.$archivo_sellado.'" method="post" enctype="multipart/form-data target="_blank"">
                    <input type="submit" value="Descargar">
                    </form>
                    ';
                }
}
elseif ($_POST["opcion"]==2) {

                $ComprobanteXML = $comprobante;
                $Timbra = new Timbrado();
                $trsID = rand(1, 10000);
                $timbrar = $Timbra->setTimbrado($ComprobanteXML, $rfc, $trsID, $getToken);                               
                $getTimbrado = $Timbra->getTimbrado();
                
                echo "<p><TEXTAREA ID='cadenaXML' cols='100' rows='15'>".$getTimbrado."</TEXTAREA></p>";

                if($timbrar)
                {
					$cabeceraXML = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
					$getTimbrado = $cabeceraXML.$getTimbrado;
					$archivo_timbrado = "temp".DIRECTORY_SEPARATOR."Timbrado-".$nombre_archivo;
					$file = @fopen($archivo_timbrado, "w+");
					$string = $getTimbrado;
					$write = @fputs($file, $string);
					@fclose($file);

					echo '
						<form action="'.$archivo_timbrado.'" method="post" enctype="multipart/form-data target="_blank"">
						<input type="submit" value="Descargar">
						</form>
						';
                }
    }

    //opcion3 = Asignar folios
if($_POST['opcion']==3){
	$Timbra = new Timbrado();
	$asignar= $Timbra->setFolios($rfc, $trsID, $getToken,$timbresAsignar);
	$getAsignacion= $Timbra->getAsignacion();
	echo "<p><TEXTAREA ID='' cols='100' rows='15'>Timbres solicitados..".$getAsignacion."</TEXTAREA></p>";
}
    
}

function SearchBOM($string) { 
    if(substr($string,0,3) == pack("CCC",0xef,0xbb,0xbf)) return true;
    return false; 
}
function rmBOM($string) { 
    if(substr($string, 0,3) == pack("CCC",0xef,0xbb,0xbf)) { 
        $string=substr($string, 3); 
    } 
    return $string; 
}
?>
 </body>
</html>