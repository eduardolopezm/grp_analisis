<?php
/**
 * Detalle de Póliza Manual
 *
 * @category Detalles
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 06/11/2017
 * Fecha Modificación: 06/11/2017
 * Detalles de Póliza Manual (Archivos)
 */

//ini_set('display_errors', 1); 
//ini_set('log_errors', 1); 
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); 
//error_reporting(E_ALL); 

$debug_sql = false;

$PageSecurity = 10;

include ('includes/session.inc');
$funcion = 105;
include ('includes/SecurityFunctions.inc');
include ('includes/SQL_CommonFunctions.inc');

$title = _ ( 'Archivos De Pólizas Contables' );
include ('includes/header.inc');
include "includes/SecurityUrl.php";

$type = "";
if (isset ( $_POST ['type'] )) {
	$type = $_POST ['type'];
} elseif (isset ( $_GET ['type'] )) {
	$type = $_GET ['type'];
}

$typeno = "";
if (isset ( $_POST ['typeno'] )) {
	$typeno = $_POST ['typeno'];
} elseif (isset ( $_GET ['typeno'] )) {
	$typeno = $_GET ['typeno'];
}

$tagref = "";
if (isset ( $_POST ['tagref'] )) {
	$tagref = $_POST ['tagref'];
} elseif (isset ( $_GET ['tagref'] )) {
	$tagref = $_GET ['tagref'];
}

$action ="ReporteGLJournal_Files.php?type=".$type."&typeno=".$typeno."&tagref=".$tagref;

$PolizaRutaArchivos="";


if(isset($_POST['SendFile'])) {

	$PolizaRutaArchivos=$_POST['txtPolizaRutaArchivos'];

	if(!file_exists($PolizaRutaArchivos)) {
		//Si no existe carpeta, crear
	    if(!mkdir($PolizaRutaArchivos, 0777, true)) {
	    	//Si no se crea la carpeta
	        prnMsg(_('No se pudo crear la carpeta para almacenar los archivos cargados'), 'error');
	    }
	}

	if(isset($_POST['txtPolizaRutaArchivos']) and $_POST['txtPolizaRutaArchivos'] !=""){

		$File_Arcchivos = $_FILES['file_mul']['name'][0];
		if ( $File_Arcchivos != '') {
			foreach ($_FILES['file_mul']['name'] as $i => $name) {
				
				if (strlen($_FILES['file_mul']['name'][$i]) > 1) {
					
					//Guardamos el archivo en la carpeta, nombre normal
					if (move_uploaded_file($_FILES['file_mul']['tmp_name'][$i], $PolizaRutaArchivos.$name)) {

						$datosExt = explode(".", $name);
						$extencion = $datosExt [1];

						$ArchivoXmlCfdi = "";
						if ($extencion == "xml") {
							//Si es xml obtener informacion
							$ArchivoXmlCfdi = file_get_contents($PolizaRutaArchivos.$name, true);
						}

						$SQL = "INSERT INTO gltrans_files (type, typeno, tagref, ruta, nombre, fecha, userid, xml)
								VALUES (".$type.", ".$typeno.", ".$tagref.", '".$PolizaRutaArchivos."', '".$name."', NOW(), '".$_SESSION['UserID']."', '".$ArchivoXmlCfdi."') ";
						$ErrMsg = _ ( 'No pude insertar el archivo porque' );
						$DbgMsg = _ ( 'El SQL que fallo para insertar el archivo fue' );
						//echo $SQL;
						$result = DB_query ( $SQL, $db, $ErrMsg, $DbgMsg, true );
					}else{
						prnMsg(_('No se pudo almacenar '.$name), 'error');
					}
				}
			}
		}
	}
}


$SQLCmbPoliza = "SELECT  t.typeid, t.typename FROM systypescat t ORDER BY t.typeid";
$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
$ResulCmbPoliza = DB_query ( $SQLCmbPoliza, $db, $ErrMsg );

$SQLDatosPoliza = "SELECT type, gltrans.typeno, gltrans.tag,account, narrative, amount, tag, counterindex, typename, 
		DATE_FORMAT(gltrans.trandate, '%d/%m/%Y') AS trandate, tags.tagdescription,legalbusinessunit.legalname
		FROM gltrans 
		LEFT JOIN systypescat on gltrans.type=systypescat.typeid
		LEFT JOIN tags on tags.tagref = gltrans.tag
		left join legalbusinessunit on tags.legalid=legalbusinessunit.legalid
		WHERE gltrans.typeno= '" . $typeno . "'
		AND type= '" . $type . "' 
		AND tag= '" . $tagref . "' ";
$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
$ResulDatosPoliza = DB_query ( $SQLDatosPoliza, $db, $ErrMsg );

$SQLDatosXML = "SELECT 
			gltrans_files.* 
			FROM gltrans_files
			WHERE 
			gltrans_files.typeno= '".$typeno."'
			AND gltrans_files.type= '".$type."' 
			AND gltrans_files.tagref= '".$tagref."'
			AND (gltrans_files.xml is not null and gltrans_files.xml <> '')";
$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
$ResulDatosXML = DB_query ( $SQLDatosXML, $db, $ErrMsg );

$SQLDatosPDF = "SELECT 
			gltrans_files.* 
			FROM gltrans_files
			WHERE 
			gltrans_files.typeno= '".$typeno."'
			AND gltrans_files.type= '".$type."' 
			AND gltrans_files.tagref= '".$tagref."'
			AND (gltrans_files.xml is null or gltrans_files.xml = '')";
$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
$ResulDatosPDF = DB_query ( $SQLDatosPDF, $db, $ErrMsg );

?>
<!--Libeerias Bootstrap y jquery-->
<!-- <script src="lib/jquery/js/2.1.1/jquery.min.js" type="text/javascript"></script>
<link href="lib/bootstrap/css/3.3.6/bootstrap.min.css" rel="stylesheet">
<script src="lib/bootstrap/js/3.3.6/bootstrap.min.js" type="text/javascript"></script> -->

<!-- <script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css"> -->

<!-- Archivos para input file, ocupa bootstrap -->
<link href="lib/inputfile_archivos/css/fileinput.css" media="all" rel="stylesheet" type="text/css" />
<script src="lib/inputfile_archivos/js/fileinput.js" type="text/javascript"></script>
<script src="lib/inputfile_archivos/js/fileinput_locale_es.js" type="text/javascript"></script>

<!--div class="container-fluid">
	<div class="col-md-4">
			<div class="input-group">
				<span>Desde:</span>
				<input type='text' name='txtFechaInicio' value='<?php echo $_POST['txtFechaInicio']; ?>' size='12' readonly class='datepickerRECH form-control' />
			</div>
		</div>
		<div class="col-md-4">
			<div class="input-group">
				<span>Hasta:</span>
				<input type='text' name='txtFechaFin' value='<?php echo $_POST['txtFechaFin']; ?>' size='12' readonly class='datepickerRECH form-control' />
			</div>
		</div>
	<div class="col-md-4">
		<div class="input-group">
			<span>Tipo Poliza:</span>
			<select class="form-control" name="type" id="type">
				<?php while ( $myrow = DB_fetch_array ( $ResulCmbPoliza ) ) {
					if ($myrow ['typeid'] == $type) {
						echo "<option value'".$myrow['typeid']."'>".$myrow['typeid']." ".$myrow['typename']."</option>";
					}else{
						echo "<option value'".$myrow['typeid']."'>".$myrow['typeid']." ".$myrow['typename']."</option>";
					}
				 } ?>
			</select>
		</div>
	</div>
	<div class="col-md-4">
		<div class="input-group">
			<span>Poliza No.:</span>
			<input class="form-control" type="text" name="typeno" id="typeno" value="<?php echo $typeno; ?>">
		</div>
	</div>
</div-->
<br>
<div class="container-fluid">
	<div class="panel panel-default">
		<!-- Default panel contents -->
		<div class="panel-heading" align="center"> Número de Póliza: <?php echo $typeno; ?></div>
		<!--<div class="panel-body">
		</div>-->
		<!-- Table -->
		<table class="table table-hover">
			<tr>
				<th style='text-align:center;'><b>UR</b></th>
				<th style='text-align:center;'><b>Tipo</b></th>
				<th style='text-align:center;'><b>Cuenta</b></th>
				<th style='text-align:center;'><b>Cargo</b></th>
				<th style='text-align:center;'><b>Abono</b></th>
				<th style='text-align:center;'><b>Concepto</b></th>
			</tr>
			<?php 
				$tagname="";
				$legalname="";

				while ( $myrow = DB_fetch_array ( $ResulDatosPoliza ) ) {
					echo "<tr>";
					echo "<td style='text-align:center;'>".$myrow['tagdescription']."</td>";
					echo "<td style='text-align:center;'>".$myrow['typename']."</td>";
					echo "<td style='text-align:center;'>".$myrow['account']."</td>";
					if ($myrow ['amount'] >= 0) {
						echo "<td style='text-align:center;'>".$myrow['amount']."</td>";
						echo "<td style='text-align:center;'>&nbsp;</td>";
					}else{
						echo "<td style='text-align:center;'>&nbsp;</td>";
						echo "<td style='text-align:center;'>".abs($myrow['amount'])."</td>";
					}
					echo "<td style='text-align:center;'>".$myrow['narrative']."</td>";
					echo "</tr>";

					$tagname=$myrow['tagdescription'];
					$legalname=$myrow['legalname'];
				}

				$carpeta = 'Polizas';
				$dir     = "/var/www/html" . dirname($_SERVER['PHP_SELF']) . "/companies/" . $_SESSION['DatabaseName'] . "/SAT/" . str_replace('.', '', str_replace(' ', '', $legalname)) . "/XML/" . $carpeta . "/" . str_replace('.', '', str_replace(' ', '', $tagname)) . "/";
				$carpetaPoliza = "Poliza_".$type."_".$typeno."_".$tagref."/"; // Carpeta de los archivos poliza

				$PolizaRutaArchivos = $dir.$carpetaPoliza; // Ruta completa de la carpeta Archivos
			?>
		</table>
	</div>
</div>
<br>

<div class="container-fluid">
	<div class="panel panel-default">
		<!-- Default panel contents -->
		<div class="panel-heading" align="center"> XML y PDF</div>
		<!--<div class="panel-body">
		</div>-->
		<!-- Table -->
		<table class="table table-hover">
			<?php 
				while ( $myrow = DB_fetch_array ( $ResulDatosXML ) ) {
					$ArchivoXmlCfdi = file_get_contents($myrow['ruta'].$myrow['nombre'], true);
					$DatosXml = DatosXmlCFDI($ArchivoXmlCfdi);

					$rutaCompleta = $myrow['ruta'].$myrow['nombre'];
	                $pos = strpos($rutaCompleta, 'companies/');
	                $rutaEnlace = substr($rutaCompleta, $pos, strlen($rutaCompleta));

	                //$botonEliminarFile="<button class='btn btn-danger btn-xs' data-id='".$myrow['id']."' data-file='".$myrow['nombre']."' data-toggle='modal' data-target='#mdlDeleteFile' type='button'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></button>";
	                $botonEliminarFile = "<button class='btn btn-danger btn-xs' name='btnEliminarAr_".$myrow['id']."' id='btnEliminarAr_".$myrow['id']."' type='button' onclick='fnEliminarArchivoSel(".$myrow['id'].", \"".$myrow['nombre']."\")'><span class='glyphicon glyphicon-trash'></span></button>";

					echo "<tr class='bg-info'><td colspan='6' style='text-align:left;'><b><a TARGET='_blank' href='".$rutaEnlace."'>Folio: " . $DatosXml['folio'] . "</a></b></td><td>".$botonEliminarFile."</td></tr>";

					echo "<tr>
							<td style='text-align:center;'><b>Uuid</b></td>
							<td style='text-align:center;'><b>Fecha Timbrado</b></td>
							<td style='text-align:center;'><b>Moneda</b></td>
							<td style='text-align:center;'><b>Descuento</b></td>
							<td style='text-align:center;'><b>Subtotal</b></td>
							<td style='text-align:center;'><b>Total</b></td>
							<td style='text-align:center;'><b>Total Impuestos</b></td>

						</tr>";

					echo "<tr>";
					echo "<td style='text-align:center;'>".$DatosXml['UUID']."</td>";
					echo "<td style='text-align:center;'>".$DatosXml['FechaTimbrado']."</td>";
					echo "<td style='text-align:center;'>".$DatosXml['Moneda']."</td>";
					echo "<td style='text-align:center;'>".$DatosXml['descuento']."</td>";
					echo "<td style='text-align:center;'>".$DatosXml['subTotal']."</td>";
					echo "<td style='text-align:center;'>".$DatosXml['total']."</td>";
					echo "<td style='text-align:center;'>".$DatosXml['totalImpuestosTrasladados']."</td>";
					echo "</tr>";

					echo "<tr class=''><td colspan='7' style='text-align:left;'><b> Emisor</b></td></tr>";

					echo "<tr>
							<td style='text-align:center;'><b>Rfc</b></td>
							<td style='text-align:center;'><b>Nombre</b></td>
							<td style='text-align:center;'><b>Calle</b></td>
							<td style='text-align:center;'><b>Colonia</b></td>
							<td style='text-align:center;'><b>Municipio</b></td>
							<td style='text-align:center;'><b>Estado</b></td>
							<td style='text-align:center;'><b>Pais</b></td>
						</tr>";
					
					foreach ($DatosXml['Emisor'] as $Emisor) {
						echo "<tr>";
						echo "<td style='text-align:center;'>".$Emisor['rfc']."</td>";
						echo "<td style='text-align:center;'>".$Emisor['nombre']."</td>";
						echo "<td style='text-align:center;'>".$Emisor['calle']."</td>";
						echo "<td style='text-align:center;'>".$Emisor['colonia']."</td>";
						echo "<td style='text-align:center;'>".$Emisor['municipio']."</td>";
						echo "<td style='text-align:center;'>".$Emisor['estado']."</td>";
						echo "<td style='text-align:center;'>".$Emisor['pais']."</td>";
						echo "</tr>";
					}

					echo "<tr class=''><td colspan='7' style='text-align:left;'><b> Receptor</b></td></tr>";

					echo "<tr>
							<td style='text-align:center;'><b>Rfc</b></td>
							<td style='text-align:center;'><b>Nombre</b></td>
							<td style='text-align:center;'><b>Calle</b></td>
							<td style='text-align:center;'><b>Colonia</b></td>
							<td style='text-align:center;'><b>Municipio</b></td>
							<td style='text-align:center;'><b>Estado</b></td>
							<td style='text-align:center;'><b>Pais</b></td>
						</tr>";
					
					foreach ($DatosXml['Receptor'] as $Receptor) {
						echo "<tr>";
						echo "<td style='text-align:center;'>".$Receptor['rfc']."</td>";
						echo "<td style='text-align:center;'>".$Receptor['nombre']."</td>";
						echo "<td style='text-align:center;'>".$Receptor['calle']."</td>";
						echo "<td style='text-align:center;'>".$Receptor['colonia']."</td>";
						echo "<td style='text-align:center;'>".$Receptor['municipio']."</td>";
						echo "<td style='text-align:center;'>".$Receptor['estado']."</td>";
						echo "<td style='text-align:center;'>".$Receptor['pais']."</td>";
						echo "</tr>";
					}

					echo "<tr class=''><td colspan='7' style='text-align:left;'><b> Conceptos</b></td></tr>";

					echo "<tr>
							<td style='text-align:center;'><b>Cantidad</b></td>
							<td style='text-align:center;'><b>Unidad</b></td>
							<td style='text-align:center;'><b>No. Identificacion</b></td>
							<td style='text-align:center;' colspan='2'><b>Descripcion</b></td>
							<td style='text-align:center;'><b>Valor Unitario</b></td>
							<td style='text-align:center;'><b>Importe</b></td>
						</tr>";

					foreach ($DatosXml['Conceptos'] as $Conceptos) {
						echo "<tr>";
						echo "<td style='text-align:center;'>".$Conceptos['cantidad']."</td>";
						echo "<td style='text-align:center;'>".$Conceptos['unidad']."</td>";
						echo "<td style='text-align:center;'>".$Conceptos['noIdentificacion']."</td>";
						echo "<td style='text-align:center;' colspan='2'>".$Conceptos['descripcion']."</td>";
						echo "<td style='text-align:center;'>".$Conceptos['valorUnitario']."</td>";
						echo "<td style='text-align:center;'>".$Conceptos['importe']."</td>";
						echo "</tr>";
					}

					echo "<tr class=''><td colspan='7' style='text-align:left;'><b> Impuestos</b></td></tr>";

					echo "<tr>
							<td style='text-align:center;'><b>Impuesto</b></td>
							<td style='text-align:center;'><b>Tasa</b></td>
							<td style='text-align:center;'><b>Importe</b></td>
							<td style='text-align:center;'><b></b></td>
							<td style='text-align:center;'><b></b></td>
							<td style='text-align:center;'><b></b></td>
							<td style='text-align:center;'><b></b></td>
						</tr>";

					foreach ($DatosXml['Impuestos'] as $Impuestos) {
						echo "<tr>";
						echo "<td style='text-align:center;'>".$Impuestos['impuesto']."</td>";
						echo "<td style='text-align:center;'>".$Impuestos['tasa']."</td>";
						echo "<td style='text-align:center;'>".$Impuestos['importe']."</td>";
						echo "<td style='text-align:center;'></td>";
						echo "<td style='text-align:center;'></td>";
						echo "<td style='text-align:center;'></td>";
						echo "<td style='text-align:center;'></td>";
						echo "</tr>";
					}

				}
				if (DB_num_rows ( $ResulDatosPDF ) > 0) {
					while ( $myrow = DB_fetch_array ( $ResulDatosPDF ) ) {
						//$botonEliminarFile="<button class='btn btn-danger btn-xs' data-id='".$myrow['id']."' data-file='".$myrow['nombre']."' data-toggle='modal' data-target='#mdlDeleteFile' type='button'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></button>";
						$botonEliminarFile = "<button class='btn btn-danger btn-xs' name='btnEliminarAr_".$myrow['id']."' id='btnEliminarAr_".$myrow['id']."' type='button' onclick='fnEliminarArchivoSel(".$myrow['id'].", \"".$myrow['nombre']."\")'><span class='glyphicon glyphicon-trash'></span></button>";

						$rutaCompleta = $myrow['ruta'].$myrow['nombre'];
		                $pos = strpos($rutaCompleta, 'companies/');
		                $rutaEnlace = substr($rutaCompleta, $pos, strlen($rutaCompleta));
						echo "<tr id='trfile_".$myrow['id']."' class='bg-success'>";
						echo "<td colspan='6' style='text-align:left;'><b><a TARGET='_blank' href='".$rutaEnlace."'>".$myrow['nombre']."</a></b></td><td>".$botonEliminarFile."</td></tr>";
					}
				}
			?>
		</table>
	</div>
</div>
<br>

<div class="container-fluid">
<form action="<?php echo $action ?>" method="POST" name="form" enctype="multipart/form-data">
<center>
	<div align='center' style=" width: 50%;">
		<!-- class="file file-loading" -->
		<input type="file" id="file_mul" name="file_mul[]" title="Seleccionar Archivos" multiple="true" placeholder="Seleccionar archivos..." />
		<br>
		<input type="hidden" name="txtPolizaRutaArchivos" id="txtPolizaRutaArchivos" value="<?php echo $PolizaRutaArchivos ?>">
		<br>
		<!-- <input type='submit' class='btn btn-primary' name='SendFile' value='Agregar Archivos'> -->
		<component-button type="submit" id="SendFile" name="SendFile" value="Agregar Archivos" class=""></component-button>
	</div>
</center>
</form>
</div>
<br>

<!-- Modal -->

<div class="modal fade" id="mdlDeleteFile" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><strong> Eliminar archivo relacionado</strong></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <strong> Confirmar Acci&oacute;n</strong>
        <br>
        <br>
        <p id="namefile"></p>
        <input type="hidden" id="txtidfile" name="txtidfile">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" id="btnDeleteFile" class="btn btn-primary" data-dismiss="modal">Confirmar</button>
      </div>
    </div>
  </div>
</div>

<script language="JavaScript">
	$( document ).ready(function() {
		$('#mdlDeleteFile').on('show.bs.modal',function(event){
			button = $(event.relatedTarget); // Button that triggered the modal
			idfile = button.data('id');
			file = button.data('file');

			$("#txtidfile").val('');
			$("#txtidfile").val(idfile);

			$('#namefile').text('');
			$('#namefile').text('Nombre del archivo: '+file);
			
		});

		$('#btnDeleteFile').click(function (){
			dataObj = {
					idFile: $('#txtidfile').val(),
					option: 'DeleteFile'
				};
				$.ajax({
					method: "POST",
					dataType:"json",
					url: "GLJournalV2_Model.php",
					data:dataObj
				})
				.done(function( data ) {
					console.log(data);
		            if(data.result){
		            	//$('#trfile_'+$('#txtidfile').val()).remove();
						alert('Se elimino correctamente, se actualizara la pagina');
						location.reload(true);
		               	
		            }
		        })
		        .fail(function(result) {
					console.log('fue error:')
				});

		});

        $("#file_mulFormat").fileinput({
            language: 'es', //Lenguaje
            browseLabel: 'Seleccionar Archivos &hellip;', //titulo boton seleccionar
            uploadUrl: "subir_archivos.php", //null quita iconos elimiar y subir
            uploadAsync: false,
            maxFileSize: 0,  //tamaño maximo de archivo
            minFileCount: 1, //numero minimo de archivos
            //maxFileCount: 5, //numero maximo de archivos
            //allowedFileExtensions : ['jpg', 'png','gif','pdf','docx'], //extenciones de archivos
            showPreview: true, //panel de visualizacion
            browseClass: "btn btn-default btn-md",
            showRemove: false, //boton eliminar en input
            showUpload: false, //boton subir en input
            overwriteInitial: false, //en true oculta los iniciales al cargar mas
            layoutTemplates: {actionUpload: ''}, //quitar boton subir en la imagen
            showAjaxErrorDetails: null, //mostrar o quitar errores
            /*initialPreview: [
                <?php if($_SESSION ['JournalDetail']->PolizaRutaArchivos != "") { ?>
                    <?php foreach ($images as $image) { ?>
                        "<img src='<?php echo $image; ?>' >",
                    <?php } ?>
                <?php } ?>
            ],*/
            initialPreviewAsData: true, // identify if you are sending preview data only and not the raw markup
            initialPreviewFileType: 'pdf', // image is the default and can be overridden in config below
            /*initialPreviewConfig: [
                <?php if($_SESSION ['JournalDetail']->PolizaRutaArchivos != "") { ?>
                    <?php foreach ($images as $image) {
                        $n_carp = 0;
                        $caracteres = preg_split('//', $image, -1, PREG_SPLIT_NO_EMPTY);
                        $nom_arch = "";
                        for ($x=0; $x < strlen($image); $x++) { 
                            $caracter = $caracteres[$x];
                            if ($caracter == '/') {
                                $n_carp++;
                            }
                            if ($n_carp == 3) {
                                if ($caracter != '/') {
                                    $nom_arch = $nom_arch.$caracter;
                                }
                            }
                        }
                    ?>
                        {caption: "<?php echo $nom_arch; ?>", url: "borrar_archivos.php", key: "<?php echo $image; ?>"}, //caption: "<?php echo $image; ?>", //nombre de archivo
                    <?php } ?>
                <?php } ?>
            ],*/
            purifyHtml: false, // this by default purifies HTML data for preview
            uploadExtraData: {
                img_key: "1000",
                img_keywords: "happy, places",
            },
        });
	});

function fnEliminarArchivoSel(idFile, nameFile) {
    //console.log("idFile: "+idFile+" - nameFile: "+nameFile);
    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
    var mensaje = '<h4>Se va a eliminar el Archivo '+nameFile+'</h4>';
    muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnEliminarArchivoSeleccion(\''+idFile+'\')');
}

function fnEliminarArchivoSeleccion(idFile) {
    //console.log("eliminar "+idFile);
    dataObj = {
        idFile: idFile,
        option: 'DeleteFile'
    };
    $.ajax({
        method: "POST",
        dataType:"json",
        url: "modelo/GLJournal_modelo.php",
        data:dataObj
    })
    .done(function( data ) {
        console.log(data);
        if(data.result){
            $('#trfile_'+idFile).remove();
        }
    })
    .fail(function(result) {
        console.log('fue error:')
    });
}
</script>


<?php
include 'includes/footer_Index.inc';
?>


