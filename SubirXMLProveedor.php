<?php
/**
 * Partidas Factura de Compra
 *
 * @category Pdf
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/09/2017
 * Fecha Modificación: 01/09/2017
 * Selección de partidas para alta de factura de compra
 */

include('includes/session.inc');
$funcion=2313;
include "includes/SecurityUrl.php";
include('includes/SecurityFunctions.inc');
$title = _('Validacion XML de Proveedores');
include('includes/SimpleImage.php');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/SendInvoicingV6_0.php');
include('xml_validator/lib/Main.class.php');
include('Numbers/Words.php');
$theme = "default";

if ($_SESSION['UserID'] == 'desarrollo') {
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);
    // ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
}

echo '<link href="/css/'. $theme . '/default.css" rel="stylesheet" type="text/css"/>';
echo '<link href="/css/css_lh.css" rel="stylesheet" type="text/css"/>';
           
$maxarchivos = 1;

if (isset($_GET['NoOrden'])) {
    $_POST['NoOrden'] = $_GET['NoOrden'];
} elseif (isset($_POST['NoOrden'])) {
    $_POST['NoOrden'] = $_POST['NoOrden'];
}
if (isset($_GET['Tagref'])) {
    $_POST['Tagref'] = $_GET['Tagref'];
} elseif (isset($_POST['Tagref'])) {
    $_POST['Tagref'] = $_POST['Tagref'];
}

if (isset($_GET['idxml'])) {
    $_POST['idxml'] = $_GET['idxml'];
} elseif (isset($_POST['idxml'])) {
    $_POST['idxml'] = $_POST['idxml'];
}

if (isset($_GET['propietarioid'])) {
    $_POST['propietarioid'] = $_GET['propietarioid'];
} elseif (isset($_POST['propietarioid'])) {
    $_POST['propietarioid'] = $_POST['propietarioid'];
}

// Se sube el archivo
if (isset($_POST ['enviar'])) {
    for ($i = 1; $i <= $maxarchivos; $i ++) {
        $posicion = 0;
        $nombre = "";
        $extension = "";
        $nombre_archivo = $_FILES ['archivo' . $i] ['name'];
        if ($nombre_archivo == null) {
            echo '<div style="color:#FF0000; font-size:11px;">Selecciona un archivo!</div>';
        } else {
            $path_parts = pathinfo($nombre_archivo);
            $nombre = $path_parts ['filename'];
            $extension = $path_parts ['extension'];
            $Hora = date('H');
            $Minuto = date('i');
            $Segundo = date('s');
            $nombre=str_replace(' ', '_', $nombre);
            $nombre_archivo = $nombre . $Hora . $Minuto . $Segundo . '.' . $extension;
            $tipo_archivo = $_FILES ['archivo' . $i] ['type'];
            $tamano_archivo = $_FILES ['archivo' . $i] ['size'];
            // compruebo si las caracter�sticas del archivo son las que deseo
            if (($tamano_archivo > 10485760)) {
                echo '<div style="color:#FF0000; font-size:11px;">El tama�o del archivo es incorrecto!</div>';
            } elseif (strtoupper($extension) != 'XML') {
                echo '<div style="color:#FF0000; font-size:11px;">El archivo seleccionado debe de ser XML!</div>';
            } else {
                //if(orden de compra existe y esta autorizada
                /*$sqlE="SELECT * FROM purchorders WHERE status in ('Authorised','Completed') and orderno='".$_POST ['NoOrden']."'";
				$resultE = DB_query($sqlE,$db);
				if (DB_num_rows($resultE) > 0) 
				{*/
                
                $arrextension = explode(".", $nombre_archivo);
                $extension = $arrextension [1];
                 
                $ruta = "XMLProveedores/";
                $nombre_archivo = str_replace(' ', '_', $nombre_archivo);
                $filename = $ruta . $nombre_archivo;
                if (move_uploaded_file($_FILES ['archivo' . $i] ['tmp_name'], $filename)) {
                    //guaardar datos del archivo subido
                    $sql4 = "INSERT INTO XmlsProveedores(
                                                                            orderno,
                                                                            fecharegistro,
                                                                            usuarioregistro,
                                                                            archivoxml,
                                                                            nombrearchivo,
                                                                            proveedor)
	                				VALUES('" . $_POST ['NoOrden'] . "',
	                       					now(),
	                   	    				'".$_SESSION ['UserID']."',
                                                                '" . $filename . "',
                                                                '".$nombre_archivo."',
                                                                '".$_POST['propietarioid']."'
                                                            )";
                    $red = DB_query($sql4, $db);
                    $doctoIDOrig = DB_Last_Insert_ID($db, "XmlsProveedores", "idXmls");
                    
                    echo '<div style="color:#32CD32; font-size:11px;">Archivo subido con exito</div>';
                    
                    $manager = Main::getValidationManager($ruta, $nombre_archivo, $db);
                    $manager->validate();
                    
                    $errors = $manager->getErrors();
                    $comprobante = $manager->getComprobante();
                    
                    if ($comprobante == null) {
                        foreach ($errors as $error) {
                            $codigo = "";
                            $linea = "";
                            $mensaje = "";
                            $level = "";
                            $class = "";
                            $valor = "";
                            $elemento = "";
                            $atributo = "";
                            $tipo = "";
                            $idprovfactura = 0;
                            $codigo = $error->getCode();
                            $linea = $error->getLine();
                            $mensaje = $error->getMessage();
                            $level = $error->getLevel();
                            if (empty($level)) {
                                $level = 4;
                            }
                            $class = $error->getClass();
                            $valor = $error->getValue();
                            $elemento = $error->getNode();
                            $atributo = $error->getAttribute();
                            $tipo = $error->getType();
                            
                            $sql = "INSERT INTO xml_observaciones (
									idprovfactura,
									elemento,
									atributo,
									codigoerror,
									valor,
									class,
									linea,
									mensaje,
									tipo,
									idxml,
									level
								) VALUES(
									'".$_POST ['NoOrden']."',
									'".$elemento."',
									'".$atributo."',
									'".$codigo."',
									'".$valor."',
									'".$class."',
									'".$linea."',
									'".DB_escape_string($db, $mensaje)."',
									'".$tipo."',
									'".$doctoIDOrig."',
									'".$level."'
								)";
                            
                            DB_query($sql, $db);
                            
                            $qry = "SELECT * FROM xml_catalogo_error
								WHERE tipo = '".$tipo."'
								AND CodigoError = '".$codigo."'";
                            $r = DB_query($qry, $db);
                            ;
                            
                            if (DB_num_rows($r) == 0) {
                                if ($level > 1) {
                                    $flagstatus = 3;
                                    $statusxml = 2;
                                    $status = "Error";
                                    $icono = "error.jpg";
                                } else if ($level == 1) {
                                    $flagstatus = 2;
                                    $statusxml = 5;
                                    $status = "Warning";
                                    $icono = "precaucion.jpg";
                                }
                                
                                $sql = "INSERT INTO xml_catalogo_error (
										CodigoError,
										mensaje,
										status,
										icono,
										tipo,
										statusxml,
										flagstatus
									) VALUES (
										'".$codigo."',
										'".DB_escape_string($db, $mensaje)."',
										'".$status."',
										'".$icono."',
										'".$tipo."',
										'".$statusxml."',
										'".$flagstatus."'
									)";
                                
                                $result = DB_query($sql, $db);
                            
                                $sql = "INSERT INTO xml_comentarios (
										xmlcomestatusant,
										xmlcomestatusactual,
										comentarios,
										comentariossistema,
										idprovfactura,
										idxml,
										idsolicitud
									) VALUES (
										0,
										0,
										'Xml Proveedor',
										'No estaba configurado correctamente el xml',
										'".$_POST ['NoOrden']."',
										'".$doctoIDOrig."',
										'0'
									)";
                                
                                $result = DB_query($sql, $db);
                            }
                        }
                    } else {
                        $monedaMNarray = array (
                            "MXN" => "MXN",
                            "mxn" => "mxn",
                            "mxn" => "mxn",
                            "Mxn" => "Mxn",
                            "MXP" => "MXP",
                            "mxp" => "mxp",
                            "Mxp" => "Mxp",
                            "Nuevo Peso" => "Nuevo Peso",
                            "Peso" => "Peso",
                            "Peso Mexicano" => "Peso Mexicano",
                            "Pesos" => "Pesos",
                            "PESOS" => "PESOS",
                            "pesos" => "pesos"
                        );
                            
                        $monedaUSarray = array (
                            "USD" => "USD",
                            "Usd" => "Usd",
                            "Dolar" => "Dolar",
                            "DOLAR" => "DOLAR",
                            "DOLARES" => "DOLARES",
                            "Dolares" => "Dolares",
                            "usd" => "usd",
                            "dolar" => "dolar",
                            "dolares" => "dolares"
                        );
                            
                        $monedaEURarray = array (
                            "EUR" => "EUR",
                            "Eur" => "Eur",
                            "Euros" => "Euros",
                            "EUROS" => "EUROS",
                            "eur" => "eur",
                            "euros" => "euros",
                            "Euro" => "Euro",
                            "EURO" => "EURO",
                            "euro" => "euro"
                        );
                        
                        // Incia Obtiene Datos del emisor
                        $emisor = $comprobante->getEmisor();
                        $rfcreceptor = $emisor->getRfc();
                        $nombre = $emisor->getNombre();
                        $domiciliofiscal = $emisor->getDomicilioFiscal();
                        $calle = $domiciliofiscal->getCalle();
                        $colonia = $domiciliofiscal->getColonia();
                        $telefono = $domiciliofiscal->getReferencia();
                        $localidad = $domiciliofiscal->getLocalidad();
                        $municipio = $domiciliofiscal->getMunicipio();
                        $estado = $domiciliofiscal->getEstado();
                        $cp = $domiciliofiscal->getCodigoPostal();
                        $pais = $domiciliofiscal->getPais();
                        
                        // Incia Obtiene Datos Timbrador
                        $timbrado = $comprobante->getTimbreFiscal();
                        $uuid = $timbrado->getUuid();
                        $selloSAT  = $timbrado->getSelloSAT();
                        // Termina Obtiene Datos Timbrador
                            
                        // Incia Obtiene Datos Comprobante
                            
                        $subtotal = $comprobante->getSubTotal();
                        $total = $comprobante->getTotal();
                        $moneda = $comprobante->getMoneda();
                        $monedaconver = strtoupper($moneda);
                        $moneda = strtolower($monedaconver);
                        $cadenaOriginal = $comprobante->getCadenaOriginal();
                        $tpocomprobante = $comprobante->getTipoDeComprobante();
                        if (strtolower($tpocomprobante) == "ingreso") {
                            $tipodecomprobante = 0;
                        } else {
                            $tipodecomproante = 1;
                        }
                        
                        // Termina Obtiene Datos Comprobante
                        $fechaemision = $comprobante->getFecha();
                        $nocertificado = $comprobante->getNoCertificado();
                        $descuento = $comprobante->getDescuento();
                        $certificado = $comprobante->getCertificado();
                        $sello = $comprobante->getSello();
                        $serie = $comprobante->getSerie();
                        $folio = $comprobante->getFolio();
                        $version = $comprobante->getVersion();
                        $mitxt = $ruta.$nombre_archivo;
                        $cfd=file_get_contents($mitxt);
                        $total = number_format($total, 2, '.', '');
                        $separa=explode(".", $total);
                        $montoletra = $separa[0];
                        $montoctvs2 = substr($separa[1], 0, 2);
                        $montoletra = Numbers_Words::toWords($montoletra, 'es');
                        $totalDoc = $comprobante->getTotal();
                            
                        if (in_array($moneda, $monedaMNarray)) {
                            $moneda = "M.N.";
                            $textoMoneda = "Pesos";
                        } else if (in_array($moneda, $monedaUSarray)) {
                            $moneda = "USD";
                            $textoMoneda = "Dolares";
                        } else if (in_array($moneda, $monedaEURarray)) {
                            $moneda = "EUR";
                            $textoMoneda = "Euros";
                        } else {
                            $moneda = "M.N.";
                            $textoMoneda = " ";
                        }
                            
                        $montoletra = ucwords($montoletra) . " " . $textoMoneda . " " . $montoctvs2 ." /100 " . $moneda;
                        $tasaiva = 0;
                        $impiva = 0;
                        $tasaieps = 0;
                        $impieps = 0;
                        $millar1 = 0;
                        $millar2 = 0;
                        $millar5 = 0;
                        $unete = 0;
                        $icic = 0;
                        $obs = 0;
                        $importeRetenciones = 0;
                        
                        $impuestos = $comprobante->getImpuestos();
                        $impuestosTotalRetenidos = $impuestos->getTotalImpuestosRetenidos();
                        $impuestosTotalTrasladados = $impuestos->getTotalImpuestosTrasladados();
                        
                        $impuestos = $comprobante->getImpuestos();
                        $trasladados = $impuestos->getTraslados();
                        
                        foreach ($trasladados as $traslado) {
                            $impuesto = $traslado->getImpuesto();
                            
                            switch ($impuesto) {
                                case "IVA":
                                    if ($tasaiva == 0) {
                                        $tasaiva = $traslado->getTasa();
                                    }
                                    $impiva += $traslado->getImporte();
                                    break;
                                case "IEPS":
                                    $tasaieps =  $traslado->getTasa();
                                    $impieps = $traslado->getImporte();
                            }
                        }
                        
                        $impuestoslocales = $comprobante->getImpuestosLocales();
                        $retencionesLocales = $impuestoslocales->getRetenciones();
                        $impuestosTotalTrasladados += $impuestoslocales->getTotalImpuestosTrasladados();
                        $retencionesVariantes = array();
                        $impretenidos = 0;
                        
                        if ($retencionesLocales != null) {
                            $sql = "SELECT LOWER(texto) AS texto, id_impuesto FROM impuestos_variantes";
                            $rsVariantesImp = DB_query($sql, $db);
                            $variantesImp = array();
                            $variantesImpTemp = array();
                            while ($rowImp = DB_fetch_array($rsVariantesImp)) {
                                $variantesImp[$rowImp['id_impuesto']][] = $rowImp['texto'];
                                $variantesImpTemp[] = $rowImp['texto'];
                            }
                        
                            foreach ($retencionesLocales as $retencion) {
                                $impLocRetenido = strtolower($retencion->getImpuesto());
                                $importeVariante = $retencion->getImporte();
                        
                                $impretenidos += $importeVariante;
                        
                                // $millarCol['1'] el numero 1 corresponde al id de la tabla impuestos para saber las variantes 5 al millar
                                // 5 al millar
                                if (in_array($impLocRetenido, $variantesImp['1'])) {
                                    $millar5 = $importeVariante;
                                }
                                // 2 al millar
                                if (in_array($impLocRetenido, $variantesImp['2'])) {
                                    $millar2 = $importeVariante;
                                }
                                // 1 al millar
                                if (in_array($impLocRetenido, $variantesImp['3'])) {
                                    $millar1 = $importeVariante;
                                }
                                // ICIC
                                if (in_array($impLocRetenido, $variantesImp['4'])) {
                                    $icic = $importeVariante;
                                }
                                // OBS
                                if (in_array($impLocRetenido, $variantesImp['5'])) {
                                    $obs = $importeVariante;
                                }
                                // Unete
                                if (in_array($impLocRetenido, $variantesImp['6'])) {
                                    $unete = $importeVariante;
                                }
                                if (in_array($impLocRetenido, $variantesImpTemp) == false) {
                                    $importeRetenciones += $importeVariante;
                                    $retencionesVariantes[] = $retencion;
                                }
                            }
                        }
                        
                        $impuestosTotalRetenidos = $impuestosTotalRetenidos + $impretenidos;//
                        $arrayfecha = explode(" ", $fechaemision);
                        $fechaemisionTemp = $fechaemision;
                        $fechaemision = $arrayfecha[0];
                        $dias = (strtotime($fechaemision) - strtotime(date("Y-m-d"))) / 86400;
                        $dias = abs($dias);
                        $dias = floor($dias);
                                
                        /*$sql = "SELECT * FROM suppliers
							WHERE taxid = '$rfcreceptor'";
						
						$Result = DB_query($sql,$db);
								
						if (DB_num_rows($Result) == 0) {
						
							$sql = "SELECT max(cast(supplierid as UNSIGNED )) + 1  FROM suppliers";
							$result = DB_query($sql,$db);
							$myrow = DB_fetch_row($result);
							var_dump('insertsss :');
							var_dump($myrow);
							$SupplierID = add_ceros($myrow['0'], 5);
						var_dump($SupplierID);
							$sql = "INSERT INTO suppliers (
									supplierid,
									suppname,
									taxid,
									address1,
									address2,
									address3,
									address4,
									address5,
									address6,
									suppliersince,
									active
								) VALUES (
									'".$SupplierID."',
									'".$nombre."',
									'".$rfcreceptor."',
									'".$calle."',
									'".$colonia."',
									'".$localidad."',
									'".$municipio."',
									'".$estado."',
									'".$pais."',
									'".date("Y-m-d")."',
									1
								)"; 
							
								$result =DB_query($sql,$db);
								$idproveedor = $SupplierID;
								
							} else {
								if ($row = DB_fetch_array($Result)) {
									$idproveedor = $row['supplierid'];
								}
							}*/
                            
                            //
                            $sql = "INSERT INTO proveedor_factura (
									serie,
									folio,
									uuid,
									subtotal,
									imptrasladado,
									impretenido,
									idstatus,
									tagref,
									proveedorid,
									fechaalta,
									fechaemision,
									idxml,
									idsolicitud,
									noCertificado,
									certificado,
									sello,
									5millar,
									2millar,
									1millar,
									importeicic,
									importeunete,
									importeobs,
									importeretenciones,
									descuento,
									selloSat,
									version,
									tipodecomprobante,
									no_aprobacion,
									anio_aprobacion,
									tasaiva,
									importeieps,
									total
								) VALUES (
									'".$serie."',
									'".$folio."',
									'".$uuid."',
									'".$subtotal."',
									'".$impuestosTotalTrasladados."',
									'".$impuestosTotalRetenidos."',
									1,
									'".$_POST['Tagref']."',
									'".$idproveedor."',
									'".date("Y-m-d")."',
									'".$fechaemisionTemp."',
									'".$doctoIDOrig."',
									'".$_POST ['NoOrden']."',
									'".$nocertificado."',
									'".$certificado."',
									'".$sello."',
									'".$millar5."',
									'".$millar2."',
									'".$millar1."',
									'".$icic."',
									'".$unete."',
									'".$obs."',
									'".$importeRetenciones."',
									'".$descuento."',
									'".$selloSAT."',
									'".$version."',
									'".$tipodecomprobante."',
									'".$comprobante->getNoAprobacion()."',
									'".$comprobante->getAnoAprobacion()."',
									'".$tasaiva."',
									'".$impieps."',
									'".$totalDoc."'
								)";
                            
                            $result = DB_query($sql, $db);
                            $idprovfactura = DB_Last_Insert_ID($db, "proveedor_factura", "id");
                            //$idprovfactura = mysqli_insert_id($dblocal);
                            $impuestosretenidos = $impuestos->getRetenciones();//
                                                                                
                        foreach ($impuestosretenidos as $impuestoretenido) {
                            $sql = "INSERT INTO proveedor_factura_Impuestos (
										idsolicitud,
										idprovfactura,
										idxml,
										impuesto,
										tasa,
										importe,
										tipoimpuesto
									) VALUES (
										'0',
										'".$idprovfactura."',
										'".$doctoIDOrig."',
										'".$impuestoretenido->getImpuesto()."',
										'".$impuestoretenido->getTasa()."',
										'".$impuestoretenido->getImporte()."',
										'Retenido'
									)";
                                    
                            $result = DB_query($sql, $db);
                        }
                            
                            $impuestostrasladados = $impuestos->getTraslados();//
                                                                                
                        foreach ($impuestostrasladados as $impuestotrasladado) {
                            $sql = "INSERT INTO proveedor_factura_Impuestos (
										idsolicitud,
										idprovfactura,
										idxml,
										impuesto,
										tasa,
										importe,
										tipoimpuesto
									) VALUES (
										'0',
										'".$idprovfactura."',
										'".$doctoIDOrig."',
										'".$impuestotrasladado->getImpuesto()."',
										'".$impuestotrasladado->getTasa()."',
										'".$impuestotrasladado->getImporte()."',
										'Trasladado'
									)";
                                
                            $result = DB_query($sql, $db);
                        }
                                                                                
                        foreach ($retencionesVariantes as $retencion) {
                            $sql = "INSERT INTO impuestos_variantes_prov_fact (
										id_proveedor_factura,
										impuesto,
										importe,
										tasa
									) VALUES (
										'$idprovfactura',
										'{$retencion->getImpuesto()}',
										'{$retencion->getImporte()}',
										'{$retencion->getTasa()}'
									)";
                        
                            $result = DB_query($sql, $db);
                        }
                            
                        if (empty($errors) == false) {
                            foreach ($errors as $error) {
                                $codigo = "";
                                $linea = "";
                                $mensaje = "";
                                $level = "";
                                $class = "";
                                $valor = "";
                                $elemento = "";
                                $atributo = "";
                                $tipo = "";
                                $codigo = $error->getCode();
                                $linea = $error->getLine();
                                $mensaje = $error->getMessage();
                                $level = $error->getLevel();
                                if (empty($level)) {
                                    $level = 4;
                                }
                                
                                $class = $error->getClass();
                                $valor = $error->getValue();
                                $elemento = $error->getNode();
                                $atributo = $error->getAttribute();
                                $tipo = $error->getType();
                                    
                                $sql = "INSERT INTO xml_observaciones (
										idprovfactura,
										elemento,
										atributo,
										codigoerror,
										valor,
										class,
										linea,
										mensaje,
										tipo,
										idxml,
										level
									) VALUES(
										'".$idprovfactura."',
										'".$elemento."',
										'".$atributo."',
										'".$codigo."',
										'".$valor."',
										'".$class."',
										'".$linea."',
										'".DB_escape_string($db, $mensaje)."',
										'".$tipo."',
										'".$doctoIDOrig."',
										'".$level."'
									)";
                                
                                $result = DB_query($sql, $db);
                                
                                $qry = "SELECT * FROM xml_catalogo_error
									WHERE tipo = '".$tipo."'
									AND CodigoError = '".$codigo."'";
                                
                                $r = DB_query($qry, $db);
                                if (DB_num_rows($r) == 0) {
                                    if ($level > 1) {
                                        $flagstatus = 3;
                                        $statusxml = 2;
                                        $status = "Error";
                                        $icono = "error.jpg";
                                    } else if ($level == 1) {
                                        $flagstatus = 2;
                                        $statusxml = 5;
                                        $status = "Warning";
                                        $icono = "precaucion.jpg";//
                                    }
                                    $sql = "INSERT INTO xml_catalogo_error (
											CodigoError,
											mensaje,
											status,
											icono,
											tipo,
											statusxml,
											flagstatus
										) VALUES (
											'".$codigo."',
											'".DB_escape_string($db, $mensaje)."',
											'".$status."',
											'".$icono."',
											'".$tipo."',
											'".$statusxml."',
											'".$flagstatus."'
										)";
                                    
                                    $result = DB_query($sql, $db);
                                }
                            }
                        } else {
                            $sql = "INSERT INTO xml_observaciones (
									idprovfactura,
									codigoerror,
									tipo,
									idxml,
									level
								) VAlUES (
									'".$idprovfactura."',
									'0',
									'PCIB',
									'".$doctoIDOrig."',
									0
								)";
                            
                            $result = DB_query($sql, $db);
                        }
                        
                        $qry = "SELECT xml_observaciones.level
							FROM xml_observaciones
							WHERE xml_observaciones.idprovfactura = '".$idprovfactura."'
							ORDER BY xml_observaciones.level desc
							LIMIT 1";
                        
                        $Result =DB_query($qry, $db);
                        $rowry = DB_fetch_array($Result);
                        if ($rowry['level'] == 3) {
                            $levelry = 2;
                        } else {
                            $levelry = $rowry['level'];
                        }
                        if ($levelry == 0) {
                            $qryes = "SELECT Estatus_factura.idstatus FROM Estatus_factura
								WHERE Estatus_factura.flagcorrecto = 1";
                            $Resultes = DB_query($qryes, $db);
                            $rowes = DB_fetch_array($Resultes);
                            $statusfactura = $rowes['idstatus'];
                        } else {
                            $qryes = "SELECT Estatus_factura.idstatus
								FROM Estatus_factura
								WHERE Estatus_factura.flagerrorxml = '".$levelry."'";
                            $Resultes = DB_query($qryes, $db);
                            $rowes = DB_fetch_array($Resultes);
                            $statusfactura = $rowes['idstatus'];
                        }
                            
                        $sql = "UPDATE proveedor_factura
							SET proveedor_factura.idstatus = '".$statusfactura."'
							WHERE proveedor_factura.id = '".$idprovfactura."'";
                        
                        $result = DB_query($sql, $db);
                            
                        $sql = "INSERT INTO xml_comentarios (
								xmlcomestatusant,
								xmlcomestatusactual,
								comentarios,
								comentariossistema,
								idprovfactura,
								idxml,
								idsolicitud
							) VALUES (
								'".$statusfactura."',
								'".$statusfactura."',
								'Xml Proveedor',
								'Se agrego el xml con el estatus. ".$statusfactura."',
								'".$idprovfactura."',
								'".$doctoIDOrig."',
								'0'
							)";
                        
                        $result =  DB_query($sql, $db);
                            
                        $txtinput = "";
                        $orderNo = 0;
                        $tipofacturacionxtag = 0;
                        $tagref  = $_POST['Tagref'];
                        $debtorId = $idproveedor;
                        $xmlimpresion = generaXMLIntermedio($txtinput, $manager->getXmlString(), $cadenaOriginal, $montoletra, $orderNo, $db, $tipofacturacionxtag, $_POST['Tagref'], $typeinvoice, $transnoinvoice, $debtorId = 0);
                        
                        $sql = "INSERT INTO Xmls_proveedor (
								transNo,
								type,
								rfcEmisor,
								fechaEmision,
								xmlSat,
								xmlImpresion,
								fiscal
							) VALUES (
								'".$idprovfactura."',
								0,
								'',
								'".date("Y-m-d")."',
								'".addslashes(utf8_decode($manager->getXmlString()))."',
								'".addslashes(utf8_decode($xmlimpresion['xmlImpresion']))."',
								0
							)";
                        
                        $result =  DB_query($sql, $db);
                    }
                } else {
                    echo '<div style="color:#32CD32; font-size:10px;">Ocurrio algun error al subir el archivo. No pudo guardarse</div>';
                }
            /*}
			else
				echo '<div style="color:#FF0000; font-size:10px;">Su Orden de Compra no a sido Autorizada!!</div>';
			*/
            }
        }
    }
    // destruccion de variables alamcenadoras de datos
    unset($_POST ['doctoid']);
    unset($_POST ['fecharegistro']);
    unset($_POST ['userid']);
    unset($_POST ['tipo']);
    unset($_POST ['archivo']);
    unset($_POST ['descripcion']);
    unset($_POST ['NoProveedor']);
    unset($doctoid);// FIN DE ENVIAR
} elseif (isset($_GET['action']) and $_GET['action'] == "cancel") {
    $sql="SELECT *
        FROM XmlsProveedores
        WHERE idXmls='". $_POST['idxml']."'";

    $result = DB_query($sql, $db);
    $myrow = DB_fetch_array($result);
    if ($myrow['archivoxml']!="") {   // Si hay un nombre de archivo guardado
        // Revisa si existe el archivo
        $borra_archivo = $myrow['archivoxml'];
        if (file_exists($borra_archivo)) { // Si existe ese archivo lo borra
                unlink($borra_archivo) or die("Error al borrar el archivo $borra_archivo");
        }
        
        //consulta que elimina los datos que concuerdan con la variable identificador seleccionada
        $sql="DELETE
            FROM XmlsProveedores
            WHERE idXmls='" . $_POST['idxml']."'";

        //echo $sql;
        $result = DB_query($sql, $db);
        //impresion de mensaje de eliminacion exitosa
        prnMsg(_('El archivo a sido eliminado ') . '!', 'info');
    }
}

echo "<head>";
echo "</head>";

echo "<form method='post' action=" . $_SERVER ['PHP_SELF'] . "?" . SID . " enctype=multipart/form-data>";
echo "<div class='centre'  style='font-size:12px'></div><br>";

echo '<table align="center" cellspacing=0 border=0 bordercolor=lightgray cellpadding=0 colspan=0 style="margin-top:0">';

echo "<tr>";
echo "<td colspan='4' class='titulos_principales'>" . _('Alta de XML') . "</td>";
echo "</tr>";

echo "<tr>";
echo "<td colspan=4>&nbsp</td>";
echo "</tr>";

echo "<tr>";
echo "</tr>";

echo "<tr>";
echo "<td class='texto_lista'>" . _('No. Orden:') . "</td>";

if ($_POST ['NoOrden'] == "-1") {
    echo "<td><input type='text' name='NoOrden' VALUE=''></td>";
} else {
    echo "<td><input type='text' name='NoOrden' VALUE='" . $_POST ['NoOrden'] . "'></td>";
}

echo "</tr>";

echo "<tr>";
echo "<td colspan=4>&nbsp;</td>";
echo "</tr>";

for ($i = 1; $i <= $maxarchivos; $i++) {
    echo "<tr>";
    echo "<td class='texto_lista'>" . _('Archivo XML') . ":</td>";
    echo "<td class='texto_rojo'><input type='file' size=20 name='archivo" . $i . "'></td>";
    echo "</tr>";
}

echo "<input type='hidden' name='SupplierID' VALUE='" . $SupplierID . "'>";
echo "<input type='hidden' name='tipopropietarioid' VALUE='" . $_POST ['tipopropietarioid'] . "'>";
echo "<input type='hidden' name='propietarioid' value='".$_POST['propietarioid']."'>";
echo "<tr><td colspan=4>&nbsp;</td></tr>";
echo "<tr><td colspan=4 style='text-align:center'><button style='border:0; background-color:transparent;' name='enviar'><img src='images/b_subir.png' width='70' title='BUBIR ARCHIVOS SELECCIONADOS'><br><font class='texto_indice'>Subir Archivos Seleccionados</font></button></td></tr>";
echo '</table>';

/* Despliega Errores XML */
echo '<table cellspacing=0 border=0 align="center" width=90% bordercolor=#aeaeae cellpadding=3 colspan=0 style="margin:auto">';
echo "<tr>";
echo "<th rowspan= 2 class='titulos_principales'>" . _('Fecha') . "</th>
		  <th rowspan= 2 class='titulos_principales' nowrap>"._('Id')."</th>
		  <th rowspan= 2 class='titulos_principales'>" . _('Nombre') . "</th>
		  <th rowspan= 2 class='titulos_principales'>" . _('Usuario') . "</th>
		 <th rowspan= 2 class='titulos_principales' colspan=2>" . _('Estado Validaci&oacute;n') . "</th>
		  <th rowspan= 1 colspan= 3 class='titulos_principales'>" . _('Acciones') . "</th>";
echo "<tr>
			 <th rowspan= 1 class='titulos_principales'>XML</th>
			 <th rowspan= 1 class='titulos_principales'>PDF</th>
			 <th rowspan= 1 class='titulos_principales'><img width=20 height=20 src='images/cancel.gif' title='Elimina'></th>
		 </tr>";
echo "</tr>";

$countXML=0;
$k=0; //row colour counter//

// Consulta para sacar los documentos cargados de acuerdo a la orden
$sql = "SELECT XmlsProveedores.idXmls,
            DATE_FORMAT(XmlsProveedores.fecharegistro, '%d-%m-%Y') as fecha,
            XmlsProveedores.usuarioregistro,
            XmlsProveedores.nombrearchivo,
            XmlsProveedores.archivoxml,
            www_users.realname,
            proveedor_factura.id,
			Estatus_factura.flagprintpdf,
			Estatus_factura.flagcorrecto,
			proveedor_factura.uuid
        FROM XmlsProveedores
        INNER JOIN www_users ON www_users.userid = XmlsProveedores.usuarioregistro
        LEFT JOIN proveedor_factura ON XmlsProveedores.idXmls = proveedor_factura.idxml
		LEFt JOIN Estatus_factura ON Estatus_factura.idstatus = proveedor_factura.idstatus
        WHERE XmlsProveedores.orderno ='".$_POST['NoOrden']."' and id>0";

//echo "<pre>".$sql;

$result = DB_query($sql, $db);

// Recorre Datos de la consulta
while ($myrow = DB_fetch_array($result)) {
    if ($k==1) {
            echo '<tr class="EvenTableRows">';
            $k=0;
    } else {
            echo '<tr class="OddTableRows">';
            $k=1;
    }
    
    $countXML++;

    $linkpdf = "<a TARGET=_blank href='PDFInvoice.php?Transno=".$myrow['id']."'>     <img alt='Ver PDF' border=0 width=20 height=20 src='".$rootpath."/images/PDF.jpg' title='Ver PDF'></a>";
    $linkxml = "<a TARGET=_blank href='XMLInvoice.php?Transno=".$myrow['idXmls']."'><img alt='Ver XML' border=0 width=20 height=20 src='".$rootpath."/images/validado1.png' title='Ver XML'></a>";

    $sql2 = "SELECT COUNT(idxmlobserv) as twarnings
                FROM xml_observaciones
                WHERE xml_observaciones.idxml = '".$myrow['idXmls']."'
                AND xml_observaciones.level = 1";
    $result2 = DB_query($sql2, $db);
    $row2 = DB_fetch_array($result2);

    $totalwarnings = $row2['twarnings'];
    $sql2 = "SELECT COUNT(idxmlobserv) as terrores
                FROM xml_observaciones
                WHERE xml_observaciones.idxml ='".$myrow['idXmls']."'
                AND xml_observaciones.level IN (2,3)";
    $result2 = DB_query($sql2, $db);
    $row2 = DB_fetch_array($result2);
    $totalerrores = $row2['terrores'];

    if ($myrow['flagcorrecto'] == 1) {
        $logo = $myrow['logo'];
    } else {
        $sql3 = "SELECT idprovfactura
                        FROM xml_observaciones
                        WHERE xml_observaciones.idxml ='".$myrow['idXmls']."'";
        $result3 = DB_query($sql3, $db);
        $row3 = DB_fetch_array($result3);
        if ($row3['idprovfactura'] == 0) {
            $logo = "invalidado.png";
        } else {
            $logo = $myrow['logo'];
        }
    }
    echo "<td nowrap>".$myrow['fecha']."</td>";
    echo "<td>".$myrow['idXmls']."</td>";
    echo "<td><a href='".$myrow['archivoxml']."' target=blank>".$myrow['nombrearchivo']."</a></td>";
    echo "<td>".$myrow['realname']."</td>";
    echo "<td><font class='texto_normal' style='color:#FFBF00;'>W.".$totalwarnings." </font><br><font class='texto_normal' style='color:#FF0000;'>E.".$totalerrores." </font>";
    echo"</td>";
    echo "<td style='text-align:center' nowrap><a href='DespliegaValidacionXml.php?idprovfactura=".$myrow['id']."&NoOrden=".$_POST['NoOrden']."&tagref=".$_POST['Tagref']."&tipopropietarioid=".$_POST['tipopropietarioid']."&idXMl=".$myrow['idXmls']."'\><img height=20 width=20 src='images/valido_23X23.png'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "</a></td>";
    echo "<td class='texto_normal' nowrap><p align=center>".$linkxml."</td>";
    if ($myrow['flagprintpdf'] == 1) {
        echo "<td class='texto_normal' nowrap><p align=center>".$linkpdf."</td>";
    } else {
        echo "<td></td>";
    }

    echo "<td><a href='SubirXMLCPOC.php?NoOrden=".$_POST['NoOrden']."&tipodocto=25&action=cancel&Tagref=".$_POST['Tagref']."&idxml=".$myrow['idXmls']."'><img src='".$rootpath."/images/cancel.gif' title='" . _('Eliminar') . "' alt='Imprimir'></a>";
    $numfuncion=$numfuncion+1;
    echo "</tr>";
}

echo "<tr>";
echo "<th colspan=10 class='titulos_principales'>" . _('Cantidad de XML: ') . "".$countXML;
echo "<input type='hidden' name='renglones' id='totrows' value='".$countXML."'></th>";
echo "</tr>";//
//(<a href=".$_SERVER['PHP_SELF'] . '?' . SID."&doctoid=".$myrow['doctoid']."&idsolicitud=".$_POST['idsolicitud']."&tipopropietarioid=".$_POST['tipopropietarioid']."&tagref=".$_POST['tagref']."&tmpborrar=1&elimina=multiple\">
echo "<tr>";
//echo "<td colspan=10>
//			<button style='border:0; background-color:transparent;' name='elimarmultiple' onclick='confirmaEliminar();'><img src='images/boton_eliminar.png' ALT='Eliminar Archivos Seleccionados'></button>";
//echo "</tr>";

echo '</table>';
/* Despliega Errores XML */
echo '</form>';

include('includes/footer.inc');
