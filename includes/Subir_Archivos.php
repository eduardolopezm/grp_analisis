<?php
/**
 * Modelo para subir_archivos.php
 *
 * @category     modelo para subir archivos
 * @package      ap_grp
 * @author       Arturo Lopez Peña <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 10/08/2017
 * Fecha Modificación: 11/08/2017
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

set_time_limit(6000);
set_include_path(implode(PATH_SEPARATOR, array(realpath('../lib/PHPExcel-1.8/Classes/PHPExcel/'), get_include_path(),)));
require_once "../lib/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php";
//require_once "../modelo/consolidacionBancariaModelo.php";
$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
require $PathPrefix . 'config.php';
require $PathPrefix . 'includes/ConnectDB.inc';
$funcion=0;
//require $PathPrefix.'includes/SecurityFunctions.inc';
include($PathPrefix.'includes/SecurityFunctionsHeader.inc');
require $PathPrefix.'includes/SQL_CommonFunctions.inc';

$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";
$info= array();
$datosCotizacion= array();
$cadenaDatosInsertar='';
$descripcion='';
$data='';

//header('Content-type: text/html; charset=latin1');
header('Content-Type: text/html; charset=utf-8');
//header('Content-type: text/html; charset=ISO-8859-1');
//header('Content-Type: application/json');
$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);
/************************************************* INICIO MODIFICACION PARA ANEXO 09.01.18 *************************************************/
/**
 * Funcion para generacion de informacion de los anexos en la tabla tb_cnfg_anexo_tecnico
 * mediante un documeto csv que es cargado desde el panel de anexoTecnico.php
 * @param  DB      $db        Instancia de la base de datos
 * @param  array   $contenido Arreglo con las linea del archivo csv
 * @param  string  $folio     folio del anexo extraido de GetNextTransNo
 * @param  integer $type      Tipo de movimiento en este caso se espera 51
 * @return array              Arreglo con la respues ta de ejecucion
 */
function fnValidAnexoTecnico($db, $contenido, $folio, $type)
{
    $data = array('mensaje'=>'Ocurrio un incidente en la validacion de la informacion.','validacion'=>false);
    $tbl = 'tb_cnfg_anexo_tecnico';
    $campos = 'nu_tagref, nu_ue, nu_partida, txt_bien_serevicio, txt_desc_bien_serevicio';
    // $campos .= ', nu_cantidad, amt_costo, amt_total, nu_garantia, nu_anexo, nu_type, txt_informacion_creacion';
    # cambios de cantidad de datos @date:24.04.18
    $campos .= ', nu_cantidad, nu_garantia, nu_anexo, nu_type, txt_informacion_creacion';
    // $sql = 'INSERT INTO %s (%s) VALUES %s';
    $flag = 0;
    foreach ($contenido as $k => $row) {
        if ($k>1) { // B
            $agregar = [ 'folio'=>$folio, 'type'=>$type, 'info'=>'usuario alta: '.$_SESSION['UserID'].' por carga archivo'];
            $row = addToArray($row, $agregar);
            if (!validaRelacion($db, $row)) {
                $data['mensaje'] = 'Uno de los productos no coincide con el su partida. Favor de revisar los campos Partida y Clave, cerca de la linea '.($k);
                $data['validacion'] = true;
                return $data;
            }
            $valores = getValues($row);
            if (empty($valores)) {
                continue;
            }
            // $sql = sprintf($sql, $tbl, $campos, $valores); // NOTE: extrañamente cuando se genera una referencia a los datos que se generan
            $sql = "INSERT INTO $tbl ($campos) VALUES $valores";
            $result = DB_query($sql, $db);
            if ($result!=true) {
                $data['validacion'] = false;
                $data['mensaje'] = 'No se puedo generar el detalle del anexo # ' . $folio
                    . ' debido a la información de la linea # ' . $k . '<br>' . DB_error_msg($db);
                $flag = 1;
                break;
            }
        }// B
    }
    if ($flag==0) {
        $data = [
            'mensaje' => 'Se genero correctamente el anexo #' . $folio,
            'validacion' => true
        ];
    }
    return $data;
}
/**
 * Funcion para la comprobacion de los datos que se solicitan para guaradar
 * con una relación de partida y clave del producto.
 * @param  {DBInstance} $db  Instacia de la base de datos
 * @param  {Array} $row Arreglo con los taso que se estan procesando
 * @return {Boolean}      Regresa verdadero si se enceuntran datos caso contrario manda un  false
 */
function validaRelacion($db, $row)
{
    $sql = "SELECT DISTINCT sm.stockid as id, sm.description as des, sm.units as um, tcpp.partidacalculada as partida
        FROM tb_cat_partidaspresupuestales_partidaespecifica as tcpp
        INNER JOIN tb_partida_articulo as tpa ON tcpp.partidacalculada= tpa.partidaEspecifica
        INNER JOIN stockmaster as sm ON tpa.eq_stockid= sm.eq_stockid
        WHERE tcpp.ccap IN(2,3) AND tcpp.partidacalculada NOT IN (22106,26103) AND tcpp.partidacalculada = '".$row[2]."'
        AND sm.stockid = '".$row[3]."'
        ORDER BY id, partida";
    $result = DB_query($sql, $db);
    return DB_num_rows($result) != 0;
}

/**
 * Funcion para la generacion del string con los datos que seran agregados
 * a la base de datos conforme a lo esperado arr(0=>6+folio+type)
 * @param  array $row Arreglo con los datos para generar la cadena
 * @return string     cadena con los valores
 */
function getValues($row)
{
    $txt='';
    $strings = [0, 1, 2, 3, 4];
    $numbers = [5, 6];
    $flag = 0;
    $newFlag = 0;
    foreach ($row as $key => $value) {
        if ($key <= 5) {
            if (empty($value)) {
                $newFlag++;
            }
        }
    }

    if ($newFlag != 0) {
        return '';
    }

    foreach ($row as $key => $value) {
        if (!empty($value) || $value === 0) {
            if ($flag!=0) {
                $txt .= ', ';
            }
            if (in_array($key, $strings)) {
                $txt .= " '$value' ";
            } elseif (in_array($key, $numbers)) {
                $txt .= " $value ";
            } else {
                $txt .= (/*$key=='folio' ||*/ $key =='info')? " '$value' " : " $value ";
            }
            $flag++;
        }
    }
    return "(" . $txt . ")";
}

/**
 * Funcion para agregar datos de un arreglo a otro utilizando la funcion array_marge
 * que proporciona php
 * @param array $origin  Arreglo principal
 * @param array  $toMarge Areglo Secundareo
 */
function addToArray($origin, $toMarge=[])
{
    // NOTE: se pueden agregar mas baidaciones en caso de ser necesario
    if (empty($origin)) {
        return $origin;
    }
    return array_merge($origin, $toMarge);
}

/************************************************* FIN MODIFICACION PARA ANEXO 09.01.18 *************************************************/

function fnDescarga($fichero)
{
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($fichero).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($fichero));
    readfile($fichero);
    exit;
}
function fnDescargaArchivos($db, $where='')
{
    //$parent_dir = dirname( dirname( __FILE__ ) );
    $contenido='';
    $origen='../archivos/'; //130.png'; //funciona
     $destino = '../archivostemporales/'; //130.png'; //funciona
     $SQL='';
    $valores='';
    $archivosDentroZip=array();
    $archivos=array();
    // mkdir(dirname($destino), 0777, true);
    //copy($origen,$destino); //funciona

    if (isset($_POST['archivos'])) {
        $archivos=$_POST['archivos'];
        for ($a=0; $a<count($archivos);$a++) {
            $valores.="'".$archivos[$a]."',";
        }

        $valores=substr($valores, 0, -1);


        $SQL="SELECT ln_nombre_interno_archivo FROM tb_archivos where nu_id_documento In(".$valores.")";
    } else {
        $where=$_POST['donde'];
        $SQL="SELECT * FROM tb_archivos ".$where;
    }
    
    $ErrMsg = "No hay  archivos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $contador=0;
    $nombreAux='';
    while ($myrow = DB_fetch_array($TransResult)) {
        $archivosDentroZip[] =$origen.$myrow['ln_nombre_interno_archivo'];
        $nombreAux=$origen.$myrow['ln_nombre_interno_archivo'];
        $contador++;
    }

    $dia=strtotime(date('d-m-Y'));
    $ale=rand(0, 31);
    $ale.=chr(rand(65, 90));


    $nombreArch='';
    if ($contador>1) {
        $nombreArch="Descarga-archivos-".$ale."-".date("Y-m-d", $dia);
    } else {
        $nombreArch= str_replace(".csv", "", $nombreAux);
    }

    $zip = new ZipArchive;
    $zip->open($destino.$nombreArch.'.zip', ZipArchive::CREATE);
    foreach ($archivosDentroZip as $archivoZip) {
        $zip->addFile($archivoZip);
    }
    $zip->close();
    /*
    $otro=array('../archivos/130.png');
    $dia=strtotime(date('d-m-Y'));
    $nombreArch="Descarga-archivos-".date("Y-m-d h:i:sa", $dia);

    $zip = new ZipArchive;
    $zip->open($destino.$nombreArch.'.zip', ZipArchive::CREATE);
    foreach ($otro as $archivoZip)
    {
    $zip->addFile($archivoZip);
    }
    $zip->close(); */
    /*
    $info='';
    for($a=0; $a<count($archivosDentroZip);$a++)
    {

    $info.=$archivosDentroZip[$a]."<br>";
    } */

    /*
    header("Content-type: application/zip");
    header("Content-Disposition: attachment; filename=$nombreArch");
    header("Pragma: no-cache");
    header("Expires: 0");
    readfile("$nombreArch"); */

    $contenido ='<a id="enlaceArchivo" class="btn bgc8" style="color:#fff !important;" href="archivostemporales/'.$nombreArch.'.zip"> Click aqui para descargar </a><br/> <br>'; //"<a href=".$destino.$nombreArch.">Descargar </a>';
    return $contenido;
}
function fnCorreo($para, $asunto, $mensaje, $patharch, $nombrearch)
{
    $enviado=false;
    include_once('phpmailer/class.phpmailer.php');
    include_once('phpmailer/class.smtp.php');
    //Recibir todos los parámetros del formulario
    /*$para= $_POST['email'];
    $asunto = $_POST['asunto'];
    $mensaje = $_POST['mensaje'];
    $archivo = $_FILES['adjunto']; */

    //Este bloque es importante
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->SMTPDebug  = 0;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = "tls";
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 587;
    $mail->From = "sagarpa.desarrollo@gmail.com";
    $mail->FromName = "SAGARPA";

    //Nuestra cuenta
    $mail->Username ='sagarpa.desarrollo@gmail.com';
    $mail->Password = 'lxskeiaansyrkkri'; //Su password

    //Agregar destinatario
    $mail->AddAddress($para);
    $mail->Subject = $asunto;
    $mail->Body = $mensaje;
    //Para adjuntar archivo
    $mail->AddAttachment($patharch, $nombrearch);
    $mail->MsgHTML($mensaje);
    $mail->CharSet = 'UTF-8';
    //Avisar si fue enviado o no y dirigir al index
    if ($mail->Send()) {
        $enviado=true;
    } else {
        $enviado=false;
    }

    return  $enviado;
}
function fnObtenerContenidoArchivo($archivo)
{
    $arreglo=array();

    try {
        $inputFileType = PHPExcel_IOFactory::identify($archivo);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($archivo);

        $hoja = $objPHPExcel->getSheet(0);
        $filasTotales = $hoja->getHighestDataRow();
        $columnasTotales = $hoja->getHighestDataColumn();
        $columnaIndex = PHPExcel_Cell::columnIndexFromString($columnasTotales);
        $contenidoFila='';

        for ($fila = 0; $fila <= $filasTotales; $fila++) {
            for ($columna=0;$columna<=$columnaIndex;$columna++) {
                $col=$hoja->getCellByColumnAndRow($columna, $fila)->getCalculatedValue();
                $arreglo[$fila][$columna]=$col;
            }
        }
        return $arreglo;
    } catch (Exception $e) {
        die('Error loading file "'.pathinfo($archivo, PATHINFO_BASENAME).'": '.$e->getMessage());
    }
}//fin funcion fnObtenerContenidoArchivos
//function fnCrearExcelConCeldasBloqueadas($email,$nombre){
function cellColor($cells, $color)
{
    $objPHPExcel = new PHPExcel;

    $objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()->applyFromArray(array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array(
             'rgb' => $color
        )
    ));
}


function fnCrearExcelConCeldasBloqueadas($info, $datosRequisicion, $requision, $db)
{
    $nombre='';
    $contador=0;
    $idProvedor='';
    $cadenaInsertar='';
    for ($a=0;$a<count($info);$a++) {


        // creando
        $contador=0;
        $objPHPExcel = new PHPExcel;
        // set default font
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
        // set default font size
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(14);
        // create the writer
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");

        /**
         * Define currency and number format.
         */
        // currency format, € with < 0 being in red color
        $currencyFormat = '#,\$ #0.0##;[Red]-#,\$ #0.0##';
        // number format, with thousands separator and two decimal points.
        $numberFormat = '#,#0.##;[Red]-#,#0.##';

        // writer already created the first sheet for us, let's get it
        $objSheet = $objPHPExcel->getActiveSheet();

        //logo  sagarpa

        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('Logo');
        $objDrawing->setDescription('Logo');
        $objDrawing->setPath('./../images/logo_sagarpa_01.jpg');
        $objDrawing->setCoordinates('A1');
        $objDrawing->setHeight(100);
        $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        //fin logo sagarpa

        // rename the sheet
        $objSheet->setTitle('Plantilla de cotizacion');

        // let's bold and size the header font and write the header
        // as you can see, we can specify a range of cells, like here: cells from A1 to A4
        $objSheet->getStyle('A1:D1')->getFont()->setBold(true)->setSize(12);

        // encabezados
        $objSheet->getCell('A7')->setValue('Email');
        $objSheet->getCell('B7')->setValue('Nombre');
        $objSheet->getCell('C7')->setValue('Calle');
        $objSheet->getCell('D7')->setValue('Colonia');
        $objSheet->getCell('E7')->setValue('Ciudad');
        $objSheet->getCell('F7')->setValue('Estado');
        $objSheet->getCell('G7')->setValue('Codigo Postal');
        $objSheet->getCell('H7')->setValue('Telefono');

        //cellColor('A7:H7', 'F28A8C');
        $objPHPExcel->getActiveSheet()->getStyle('A7:H7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('336633');

        $phpColor = new PHPExcel_Style_Color();
        $phpColor->setRGB('f5f5f5');

        $objPHPExcel->getActiveSheet()->getStyle('A7:H7')->getFont()->setColor($phpColor);


        foreach ($info[$a] as $key => $value) {
            // echo $key . " : " . $value . "";

            if ($key=='email') {
                $objSheet->getCell('A8')->setValue($value);
            }
            if ($key=='nombre') {
                $objSheet->getCell('B8')->setValue($value);
                $nombre=str_replace(' ', '_', $value);
            }

            if ($key=='ad1') {
                $objSheet->getCell('C8')->setValue($value);
            }

            if ($key=='ad2') {
                $objSheet->getCell('D8')->setValue($value);
            }
            if ($key=='ad3') {
                $objSheet->getCell('E8')->setValue($value);
            }
            if ($key=='ad4') {
                $objSheet->getCell('F8')->setValue($value);
            }

            if ($key=='ad5') {
                $objSheet->getCell('G8')->setValue($value);
            }
            if ($key=='ad6') {
                $objSheet->getCell('H8')->setValue($value);
            }
            if ($key=='provedor') {
                $objSheet->getCell('E6')->setValue($value);
            }
            /*
            $objSheet->getCell('D2')->setValue('=B2*C2');
            $objSheet->getCell('D3')->setValue('=B3*C3'); */
        }


        $objSheet->getCell('C6')->setValue('Datos de la empresa');
        /*
        $objPHPExcel->getStyle('C5')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); */
        //$objPHPExcel->getActiveSheet()->getStyle('C6')->getFont()->setColor($phpColor);

        $objPHPExcel->getActiveSheet()->getStyle('C6')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('e1e1e1');

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('C6:D6');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:A6');

        $objSheet->getCell('C12')->setValue('Datos de Requisición');
        $objSheet->getCell('C11')->setValue('Número de Requisición ');
        $objSheet->getCell('D11')->setValue($requision);

        $objPHPExcel->getActiveSheet()->getStyle('C12')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('e1e1e1');

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('C12:D12');

        $objSheet->getCell('A13')->setValue('Partida');
        $objSheet->getCell('B13')->setValue('Renglon');
        $objSheet->getCell('C13')->setValue('Descripción partida');
        $objSheet->getCell('D13')->setValue('Codigo Artículo');
        $objSheet->getCell('E13')->setValue('Descripción Artículo');
        $objSheet->getCell('F13')->setValue('Unidad');
        $objSheet->getCell('G13')->setValue('Precio');
        $objSheet->getCell('H13')->setValue('Cantidad solicitada');
        $objSheet->getCell('I13')->setValue('Total');
        $objSheet->getCell('J13')->setValue('Orden');
        $objSheet->getCell('k13')->setValue('Cotización');

        $objPHPExcel->getActiveSheet()->getColumnDimension('A:J')->setAutoSize(true);

        $objPHPExcel->getActiveSheet()->getStyle('A13:J13')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('336633');

        $objPHPExcel->getActiveSheet()->getStyle('K13')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ff0000');


        /*
        $phpColor = new PHPExcel_Style_Color();
        $phpColor->setRGB('f5f5f5');
        */

        $b=14;
        $objPHPExcel->getActiveSheet()->getStyle('A13:K13')->getFont()->setColor($phpColor);

        for ($d=0;$d<count($datosRequisicion);$d++) {

            //  partida ,renglón, descripción, partida, articulo, unidad, precio, cantidad, total, existencia, orden
            foreach ($datosRequisicion[$d] as $key => $value) {
                // echo $key . " : " . $value . "";

                if ($key=='idPartida') {
                    $objSheet->getCell(('A'.$b))->setValue($value);
                }
                if ($key=='renglon') {
                    $objSheet->getCell(('B'.$b))->setValue($value);
                }

                if ($key=='descPartida') {
                    $objSheet->getCell(('C'.$b))->setValue($value);
                }

                if ($key=='idItem') {
                    $objSheet->getCell(('D'.$b))->setValue($value);
                }
                if ($key=='descItem') {
                    $objSheet->getCell(('E'.$b))->setValue($value);
                }
                if ($key=='unidad') {
                    $objSheet->getCell(('F'.$b))->setValue($value);
                }

                if ($key=='precio') {
                    $objSheet->getCell(('G'.$b))->setValue($value);
                }
                if ($key=='cantidad') {
                    $objSheet->getCell(('H'.$b))->setValue($value);
                }
                if ($key=='total') {
                    $objSheet->getCell(('I'.$b))->setValue($value);
                }
                /*if($key=='existencia'){
                 $objSheet->getCell(('H'.$b))->setValue($value);
            }*/
                if ($key=='orden') {
                    $objSheet->getCell(('J'.$b))->setValue($value);
                }
                /*
                $objSheet->getCell('D3')->setValue('=B3*C3'); */
            }
            $b++;
        }
        /*

        $objSheet->getCell('D4')->setValue('=B4*C4');

        $objSheet->getCell('A5')->setValue('TOTAL');
        $objSheet->getCell('B5')->setValue('=SUM(B2:B4)');
        $objSheet->getCell('C5')->setValue('-');
        $objSheet->getCell('D5')->setValue('=SUM(D2:D4)'); */

        // bold and resize the font of the last row
        $objSheet->getStyle('A1:H1')->getFont()->setBold(true)->setSize(12);

        /*
        // set number and currency format to columns
        $objSheet->getStyle('B2:B5')->getNumberFormat()->setFormatCode($numberFormat);
        $objSheet->getStyle('C2:D5')->getNumberFormat()->setFormatCode($currencyFormat);

        // bordes

        $objSheet->getStyle('A1:D5')->getBorders()->
        getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

        $objSheet->getStyle('A1:D5')->getBorders()->
        getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);

        $objSheet->getStyle('A5:D5')->getBorders()->
        getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);

        $objSheet->getStyle('A1:D1')->getBorders()->
        getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM); */

        // ajuste de columnas
        $objSheet->getColumnDimension('A')->setAutoSize(true);
        $objSheet->getColumnDimension('B')->setAutoSize(true);
        $objSheet->getColumnDimension('C')->setAutoSize(true);
        $objSheet->getColumnDimension('D')->setAutoSize(true);

        //$objSheet->getStyle('K')->getNumberFormat()->setFormatCode($numberFormat);


        //$objSheet->protectCells('A1:C5', 'PHP');
        $objSheet->getProtection()->setPassword('sagarpa1A12DqQqw2121212');
        $objSheet->getProtection()->setSheet(true); // para proteger hoja
        $objSheet->getStyle(('k14:k'.$b))->getProtection()->setLocked(\PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);

        //foreach($info as $x => $value)
        //for($a=0;$a<count($info);$a++){

        /*foreach($info[$a] as $key => $value){
              // echo $key . " : " . $value . "";
              $nombre='';
              if($key=='nombre'){
                 $nombre=str_replace(' ','_',$value); */
        $objWriter->save('../archivos/'.$nombre.'.xlsx');

        $cadenaInsertar.="('". $_SESSION['UserID']."','".$nombre.".xlsx','archivos/".$nombre.".xlsx','"."2424"."','1','"."2424"."','"."2424"."','".$nombre.".xlsx','"."1"."'),";
        //}
        foreach ($info[$a] as $key => $value) {
            if ($key=='email') {
                $mensaje='Proveedor <b>'.$nombre.'</b><br> Por este medio se le informa que se'. 'necesita la cotizaci&oacute;n de los productos anexos en el presente archivo Excel el'. 'cual debe ser llenado en los campos indicados, as&iacute; como su reenv&iacute;o.';

                fnCorreo($value, "Solicitud cotización", $mensaje, '../archivos/'.$nombre.'.xlsx', $nombre.'.xlsx');
            }
        }

        //}
    }
    //archivos/
    $cadenaInsertar= substr($cadenaInsertar, 0, -1);

    return $cadenaInsertar;
}// fin
/////

function fnGuardarEstadoCuentaBanco($datosContenido, $lineas, $db)
{
    $validacion=true;
    $SQL='';
    // $SQL = "SET NAMES 'utf8'";
    // $TransResult = DB_query($SQL, $db);
    $data='';
    $datos='';
    $separador=',';
    $mensaje='Estado de cuenta cargado correctamente';
    $infoConsolidacion='';

    $tieneerrores = 0;
    $lineatitulo  = 0;
    $mincolumnas  = 4;

    $columnafecha    = 0;
    $columnaconcepto = 1;
    $columnaretiro   = 2;
    $columnadeposito = 3;
    $totalRegistrosInsertados=0;
    $registrosMATCH1=0;
    $registrosMULTIMATCH1=0;
    $registrosNOMATCH1=0;
    $registrosMATCH2=0;

    $consolidacionTabla='';
    $cbTabla='';

    // $data.= "<table border='1'>";
    // for($f=0;$f<count($datosContenido);$f++) {
    //     $data.= "<tr>";
    //     for($c=0;$c<count($datosContenido[$f]);$c++) {
    //         $data.="<td>".$datosContenido[$f][$c]."</td>";

    //     }
    //     $data."</tr>";

    // }
    // $data.= "</table>";

    if (isset($_POST['anio'])) {
        $ToYear = $_POST['anio'];
    } else {
        $ToYear = date('Y');
    }

    if (isset($_POST['mes'])) {
        $ToMes = $_POST['mes'];
    } else {
        $ToMes = date('m');
    }

    if (isset($_POST['dia'])) {
        $ToDia = $_POST['dia'];
    } else {
        $ToDia = date('d');
    }

    $fechafin  = rtrim($ToYear) . '-' . rtrim($ToMes) . '-' . rtrim($ToDia);

    $fechafin  = rtrim($ToYear) . '-' . add_ceros(rtrim($ToMes), 2) . '-' . add_ceros(rtrim($ToDia), 1) . ' 23:59:59';

    //$fechafinc = mktime(23, 59, 59, rtrim($ToMes), rtrim($ToDia), rtrim($ToYear)); para ser que no se utiliza

    $InputError = 0;

    foreach ($lineas as $line_num => $line) {
        //while (($data = fgetcsv($lineas, 1000, ",")) !== FALSE)
        //{

        // $datos = array_map("utf8_encode", $data);
        // $datos = $data;
        /**
         * REEMPLAZA CARACTERES NO VALIDOS
         */
        $consolidacionTabla.='<tr>';
        $line = str_replace("'", "", $line); // QUITA COMILLA SIMPLE
        $line = str_replace('"', '', $line); // QUITA DOBLE COMILLA

        $datos         = explode($separador, $line); // Convierte en array cada una de las lineas( asi se obtien las columnas)
        // $datos=array_map("utf8_decode", $datos);

        $columnaslinea = count($datos); // Obtiene el numero de columnas de la linea en base al separador

        // if ($columnaslinea < $mincolumnas) {
        //     $tieneerrores = 1;
        //     $error        = 'El número mínimo de columnas requeridas no se cumple en la línea : ' . intval($line_num + 1);
        //     $error .= '<br>' .'La estructura del archivo debe de tener al menos ' . $mincolumnas . ' datos separados por "' . $separador . '"';
        //    $mensaje=$error;
        //     exit();

        // } else {

        // }
        $codigofecha = trim($datos[$columnafecha]);
        if ($codigofecha != '') {
            if (SUBSTR($codigofecha, 2, 1) == '/' or SUBSTR($codigofecha, 2, 1) == '-') {
                //if (($ToMes * 1 == SUBSTR($codigofecha, 3, 2) * 1) AND (SUBSTR($codigofecha, 6, 4)==$ToYear) ){ // f2


                $fechatrans = SUBSTR($codigofecha, 6, 4) . '-' . SUBSTR($codigofecha, 3, 2) . '-' . SUBSTR($codigofecha, 0, 2);
                $consolidacionTabla.='<td>'.($fechatrans).'</td>';
                $consolidacionTabla.='<td>'.($datos[$columnaconcepto]) .'</td>';
                $codigoretiro = '';

                if (is_numeric(trim($datos[$columnaretiro]))) {
                    $codigoretiro = trim($datos[$columnaretiro]) * 1;
                    $consolidacionTabla.='<td>'.($codigoretiro).'</td>';
                } else {
                    $codigoretiro = 0;
                    $consolidacionTabla.='<td>'.($codigoretiro).'</td>';
                }

                $codigodeposito = '';

                if (is_numeric(trim($datos[$columnadeposito]))) {
                    $codigodeposito = trim($datos[$columnadeposito]) * 1;
                    $consolidacionTabla.='<td>'. ($codigodeposito).'</td>';
                } else {
                    $codigodeposito = 0;
                    $consolidacionTabla.='<td>'. ($codigodeposito).'</td>';
                }
                // $SQL = "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'";
                // $TransResult = DB_query($SQL, $db);
                // var_dump( $datos[$columnaconcepto]) ;

                //   exit();
                $SQL = "INSERT INTO estadoscuentabancarios (
                    Fecha,
                    Concepto,
                    cuenta,
                    Retiros,
                    depositos,
                    usuario)
                    VALUES(
                    '" . $fechatrans . "',
                    '" . str_replace('"', '', utf8_encode($datos[$columnaconcepto])) . "',
                    '" . $_POST['bank'] . "',
                    '" . $codigoretiro . "',
                    '" . $codigodeposito . "',
                    '" . $_SESSION['UserID'] . "')";

                //print_r($SQL);
                //screen_debug($sSQL, $ejecutar_debug, 'string', __LINE__, __FILE__);
                if ((abs($codigoretiro) + abs($codigodeposito)) > 0) {
                    $result = DB_query($SQL, $db);

                    $totalRegistrosInsertados = $totalRegistrosInsertados + 1;

                    $esteid = $_SESSION['LastInsertId'];
                    /* AQUI VOY A PONER LA LOGICA PARA QUE AUTOMATICAMENTE CHEQUE MOVIMIENTOS QUE HACEN MATCH UNICO Y EXACTO !!! */
                    //  empieza consiliacion bancaria
                    if ($codigoretiro > 0) {
                        $SQL = "SELECT count(*) AS unico,
                              max(banktransid) AS banktransid,
                              max(tagref) AS tagref,
                              transdate
                              FROM banktrans
                              WHERE amount = " . ($codigoretiro * -1) . "
                              AND transdate <= '" . $fechatrans . "'
                              AND bankact='" . $_POST["bank"] . "'
                              AND amountcleared = 0
                              GROUP BY banktransid,tagref,transdate
                              ";
                        //screen_debug($ssql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                        $result = DB_query($SQL, $db);
                        //print_r($SQL);
                        // print_r($SQL);
                        //print("2");
                        $myrow = DB_fetch_array($result);
                        // print_r($myrow);
                        if ($myrow) {
                            if ($myrow['unico'] == 1) {
                                // prnMsg(_('ENCONTRE MATCH UNICO ! ID:'). $myrow['banktransid'] ,'success');
                                //  $consolidacion[]="consolidacion";

                                $registrosMATCH1 = $registrosMATCH1 + 1;
                                $consolidacionTabla.='<td>'.('Conciliado').'</td>';
                                $TransNo = GetNextTransNo(600, $db);
                                // selecciona el periodo al que pertenece la fecha
                                $SQL = "SELECT periodno,
                                  lastdate_in_period
                                  FROM periods
                                  WHERE YEAR(lastdate_in_period) = " . $ToYear . "
                                  AND MONTH(lastdate_in_period) = " . $ToMes;
                                //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                                $ErrMsg= ('No se puede obtener informacion');
                                $resultCC       = DB_query($SQL, $db, $ErrMsg);
                                $myrowCC        = DB_fetch_array($resultCC);
                                $periododeMatch = $myrowCC[0];
                                // print_r($SQL);
                                //    print("3");

                                $SQL = "UPDATE banktrans SET
                             amountcleared= " . ($codigoretiro * -1) . ",
                             usuario = '" . $_SESSION['UserID'] . "-AUT',
                             fechacambio = NOW(),
                             fechabanco='" . $fechatrans . "',
                             batchconciliacion= " . $TransNo . ",
                             matchperiodno = " . $periododeMatch . "
                                WHERE banktransid=" . $myrow['banktransid'];
                                //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                                //
                                $ErrMsg   = ('Hubo un error al crear match');
                                $resultCC = DB_query($SQL, $db, $ErrMsg);
                                // print_r($SQL);
                                //  print("4");

                                $SQL = "UPDATE estadoscuentabancarios SET
                             conciliado= " . ($codigoretiro) . ",
                             usuario = '" . $_SESSION['UserID'] . "-AUT',
                             fechacambio = NOW(),
                             tagref = '" . $myrow['tagref'] . "',
                             fechacontable='" . $myrow['transdate'] . "',
                             batchconciliacion= " . $TransNo . "
                             WHERE banktransid=" . $esteid;
                                //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                                $ErrMsg = ('Hubo un error al crear match.');
                                $resultCC = DB_query($SQL, $db, $ErrMsg);
                            } elseif ($myrow['unico'] > 1) {
                                // si encunetra mas de una coincidencia
                                //prnMsg(_('ENCONTRE MULTIPLES MATCH ! NUMERO:'). $myrow['unico'] ,'warn');

                                /* INTENTA BUSCAR REGISTROS DENTRO DE LOS DIEZ DIAS MAS CERCANOS !! */
                                $consolidacionTabla.='<td>'.('No conciliado').'</td>';
                                $registrosMULTIMATCH1 = $registrosMULTIMATCH1 + 1;

                                $numeroAutorizacion = trim($datos[$columnaconcepto]);
                                $numeroAutorizacion = str_replace(".", " ", $numeroAutorizacion);
                                $numeroAutorizacion = str_replace("-", " ", $numeroAutorizacion);
                                $numeroAutorizacion = str_replace("/", " ", $numeroAutorizacion);
                                $numeroAutorizacion = str_replace("&", " ", $numeroAutorizacion);

                                /* EXPLOTA CADA PARTE SEPARADA POR ESPACIOS EN UN ELEMENTO DEL ARREGLO */
                                // para veirificar si hay alguna concidencia del concepto en el banktrans
                                $arregloDePalabras = explode(" ", $numeroAutorizacion);

                                $likeStr = "";
                                $sientro = 0;
                                for ($rept = 0; $rept < count($arregloDePalabras); $rept++) {
                                    if (is_numeric($arregloDePalabras[$rept])) {
                                        if (strlen($arregloDePalabras[$rept]) >= 4) {
                                            $sientro = $sientro + 1;
                                            if ($sientro > 1) {
                                                $likeStr = $likeStr . " OR ref like '%" . $arregloDePalabras[$rept] . "%'";
                                            } else {
                                                $likeStr = $likeStr . "ref like '%" . $arregloDePalabras[$rept] . "%'";
                                            }
                                        }
                                    }
                                }// fin for

                                if ($sientro > 0) {
                                    $likeStr = "AND (" . $likeStr;
                                    $likeStr = $likeStr . ")";
                                }

                                $SQL = "SELECT count(*) AS unico,
                             max(banktransid) AS banktransid,
                             max(tagref) AS tagref,
                             transdate
                            FROM banktrans
                            WHERE amount = " . ($codigoretiro * -1) . "
                            AND transdate <= '" . $fechatrans . "'
                            AND transdate >= '" . $fechatrans . "'
                            AND bankact='" . $_POST["bank"] . "'
                            AND amountcleared = 0 " . $likeStr;
                                //screen_debug($ssql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                                $result = DB_query($SQL, $db);
                                //          print_r($SQL);
                                // print("5");

                                //print_r($SQL);
                                if ($myrow = DB_fetch_array($result)) {
                                    if ($myrow['unico'] == 1) {
                                        // prnMsg(_('ENCONTRE MATCH UNICO **** 2 INTENTO ***** ! ID:') . $myrow['banktransid'], 'success');
                                        // prnMsg($likeStr, 'success');
                                        // prnMsg($line, 'success');

                                        $registrosMATCH2      = $registrosMATCH2 + 1;
                                        $registrosMULTIMATCH1 = $registrosMULTIMATCH1 - 1;

                                        $TransNo              = GetNextTransNo(600, $db);

                                        $sql = "SELECT periodno,
                                      lastdate_in_period
                                      FROM periods
                                      WHERE YEAR(lastdate_in_period) = " . $ToYear . "
                                     AND MONTH(lastdate_in_period) = " . $ToMes;
                                        //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                                        $ErrMsg         = _('Could not retrieve transaction information');
                                        $resultCC       = DB_query($sql, $db, $ErrMsg);
                                        $myrowCC        = DB_fetch_array($resultCC);
                                        $periododeMatch = $myrowCC[0];
                                        //                      print_r($SQL);
                                        // print("6");

                                        $sql = "UPDATE banktrans SET
                                       amountcleared= " . ($codigoretiro * -1) . ",
                                       usuario = '" . $_SESSION['UserID'] . "-AUT',
                                       fechacambio = NOW(),
                                       fechabanco='" . $fechatrans . "',
                                       batchconciliacion= " . $TransNo . ",
                                       matchperiodno = " . $periododeMatch . "
                                       WHERE banktransid=" . $myrow['banktransid'];
                                        //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                                        $ErrMsg   = _('Could not match off this payment because');
                                        $resultCC = DB_query($sql, $db, $ErrMsg);
                                        //                   print_r($SQL);
                                        // print("7");

                                        $sql = "UPDATE estadoscuentabancarios SET
                                            conciliado= " . ($codigoretiro) . ",
                                            usuario = '" . $_SESSION['UserID'] . "-AUT',
                                            fechacambio = NOW(),
                                            tagref = '" . $myrow['tagref'] . "',
                                            batchconciliacion= " . $TransNo . "
                                            WHERE banktransid=" . $esteid;
                                        //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                                        $ErrMsg = _('Could not match off this payment because');
                                        $resultCC = DB_query($sql, $db, $ErrMsg);
                                    }
                                }
                            } else {
                                //screen_debug($line, $ejecutar_debug, 'string', __LINE__, __FILE__, 'SIN MATCH');
                                $registrosNOMATCH1 = $registrosNOMATCH1 + 1;
                                $consolidacionTabla.='<td>'.('No conciliado').'</td>';
                                //                       print_r($SQL);
                                // print("8");
                            }
                        } else {
                            $consolidacionTabla.='<td>'.('No conciliado').'</td>';
                        }// fin primer. busqueda de codifo unico
                    }// fin codigo retiro
                    if ($codigodeposito > 0) {
                        $ssql = "SELECT count(*) AS unico,
                              max(banktransid) AS banktransid,
                              max(tagref) AS tagref,
                              transdate
                              FROM banktrans
                              WHERE amount = " . ($codigodeposito) . "
                              AND transdate <= '" . $fechatrans . "'
                              AND bankact='" . $_POST["bank"] . "'
                              AND amountcleared = 0
                              GROUP BY banktransid,tagref,transdate";
                        //screen_debug($ssql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                        $result = DB_query($ssql, $db);
                        //    print_r($SQL);
                        // print("9");


                        $myrow = DB_fetch_array($result);
                        // print_r($myrow);
                        if ($myrow) {
                            if ($myrow['unico'] == 1) {

                                //prnMsg(_('ENCONTRE MATCH UNICO ! ID:'). $myrow['banktransid'] ,'success');
                                // prnMsg($line ,'success');

                                $registrosMATCH1 = $registrosMATCH1 + 1;

                                $TransNo = GetNextTransNo(600, $db);

                                $sql = "SELECT periodno,
                                lastdate_in_period
                                FROM periods
                                WHERE YEAR(lastdate_in_period) = " . $ToYear . "
                                AND MONTH(lastdate_in_period) = " . $ToMes;
                                //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                                $ErrMsg         = _('Could not retrieve transaction information');
                                $resultCC       = DB_query($sql, $db, $ErrMsg);
                                $myrowCC        = DB_fetch_array($resultCC);
                                $periododeMatch = $myrowCC[0];

                                $sql = "UPDATE banktrans
                                SET amountcleared= " . ($codigodeposito) . ",
                                usuario = '" . $_SESSION['UserID'] . "-AUT',
                                fechacambio = NOW(),
                                fechabanco='" . $fechatrans . "',
                                batchconciliacion= " . $TransNo . ",
                                matchperiodno = " . $periododeMatch . "
                                WHERE banktransid=" . $myrow['banktransid'];
                                //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                                $ErrMsg   = _('Could not match off this payment because');
                                $resultCC = DB_query($sql, $db, $ErrMsg);
                                //      print_r($SQL);
                                // print("10 "."--");

                                $consolidacionTabla.='<td>'.('Conciliado').'</td>';
                                $sql = "UPDATE estadoscuentabancarios
                                SET conciliado= " . ($codigodeposito * -1) . ",
                                usuario = '" . $_SESSION['UserID'] . "-AUT',
                                fechacambio = NOW(),
                                tagref = '" . $myrow['tagref'] . "',
                                fechacontable='" . $myrow['transdate'] . "',
                                batchconciliacion= " . $TransNo . "
                                WHERE banktransid=" . $esteid;
                                $ErrMsg = _('Could not match off this payment because');
                                //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                                $resultCC = DB_query($sql, $db, $ErrMsg);
                            //              print_r($SQL);
                                // print("11 "."--");
                            } elseif ($myrow['unico'] > 1) {
                                $consolidacionTabla.='<td>'.('No conciliado').'</td>';
                                // prnMsg(_('ENCONTRE MULTIPLES MATCH ! NUMERO:'). $myrow['unico'] ,'warn');
                                // prnMsg($line ,'warn');
                                /* CON MULTIPLES MATCH */

                                $registrosMULTIMATCH1 = $registrosMULTIMATCH1 + 1;

                                $ssql = "SELECT count(*) as unico,
                                       max(banktransid) as banktransid,
                                       max(tagref) as tagref,transdate
                                    FROM banktrans
                                    WHERE amount = " . ($codigodeposito) . "
                                    AND transdate <= '" . $fechatrans . "'
                                    AND transdate >= DATE_SUB('" . $fechatrans . "', INTERVAL 10 DAY)
                                    AND bankact='" . $_POST["bank"] . "'
                                    AND  amountcleared = 0";
                                //screen_debug($ssql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                                $result = DB_query($ssql, $db);

                                //      print_r($SQL);
                                // print("12"."--");
                                if ($myrow = DB_fetch_array($result)) {
                                    if ($myrow['unico'] == 1) {
                                        // prnMsg(_('ENCONTRE MATCH UNICO **** 2 INTENTO ***** ! ID:'). $myrow['banktransid'] ,'success');
                                        // prnMsg($line ,'success');

                                        $registrosMATCH2      = $registrosMATCH2 + 1;
                                        $registrosMULTIMATCH1 = $registrosMULTIMATCH1 - 1;
                                        $TransNo              = GetNextTransNo(600, $db);

                                        $sql = "SELECT periodno,
                                   lastdate_in_period
                                   FROM periods
                                    WHERE YEAR(lastdate_in_period) = " . $ToYear . "
                                    AND MONTH(lastdate_in_period) = " . $ToMes;
                                        //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                                        $ErrMsg         = _('Could not retrieve transaction information');
                                        $resultCC       = DB_query($sql, $db, $ErrMsg);

                                        //          print_r($SQL);
                                        // print("13 "."--");
                                        $myrowCC        = DB_fetch_array($resultCC);
                                        $periododeMatch = $myrowCC[0];

                                        $sql = "UPDATE banktrans SET
                                    amountcleared= " . ($codigodeposito) . ",
                                    usuario = '" . $_SESSION['UserID'] . "-AUT',
                                    fechacambio = NOW(),
                                    batchconciliacion= " . $TransNo . ",
                                    fechabanco='" . $fechatrans . "',
                                    matchperiodno = " . $periododeMatch . "
                                    WHERE banktransid=" . $myrow['banktransid'];
                                        $ErrMsg   = _('Could not match off this payment because');
                                        //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                                        $resultCC = DB_query($sql, $db, $ErrMsg);
                                        //           print_r($SQL);
                                        // print("14 "."--");
                                        $sql = "UPDATE estadoscuentabancarios
                                     SET conciliado= " . ($codigodeposito * -1) . ",
                                     usuario = '" . $_SESSION['UserID'] . "-AUT',
                                     fechacambio = NOW(),
                                     tagref = '" . $myrow['tagref'] . "',
                                     fechacontable='" . $myrow['transdate'] . "',
                                     batchconciliacion= " . $TransNo . "
                                     WHERE banktransid=" . $esteid;
                                        $ErrMsg = _('Could not match off this payment because');

                                        //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                                        $resultCC = DB_query($sql, $db, $ErrMsg);
                                        //          print_r($SQL);
                                        // print("15 "."--");
                                    }
                                }
                            } else {
                                //screen_debug($line, $ejecutar_debug, 'string', __LINE__, __FILE__, 'SIN MATCH');
                                $consolidacionTabla.='<td>'.('No conciliado').'</td>';
                                $registrosNOMATCH1 = $registrosNOMATCH1 + 1;
                            }
                        } else {
                            $consolidacionTabla.='<td>'.('No conciliado').'</td>';
                        }
                    }
                    // fin empieza consiliacion bancaria
                    //
                }// fin deposito + retiro mayor a cero


                //}// fin validacion fecha  f2
            }// fin validacion fecha
        }// fin codigo fecha
        $consolidacionTabla.='</tr>';
    }// fin foreach
    $cbTabla= utf8_encode($consolidacionTabla);
    if ($totalRegistrosInsertados>0) {
        $infoConsolidacion.="Se cargaron exitosamente al sistema " . $totalRegistrosInsertados . " transacciones bancarias de estado de cuenta<br>";
        $infoConsolidacion.="Registros conciliados en automático  primer intento : <b>" . $registrosMATCH1 . "</b> <br>";
        $infoConsolidacion.="Registros conciliados en automático  segundo intento <b>:" . $registrosMATCH2 . "</b><br>";
        $infoConsolidacion.="Registros con multiples matchs automáticos <b>:" . $registrosMULTIMATCH1. "</b><br>";
        //$infoConsolidacion.="registros sin matchs automáticos:" . $registrosNOMATCH1. "<br>";
    }

    //print_r($lineas);
    //$lineas1=$lineas;

    //$data
    //print_r($cbTabla);
    $datosVal=array('mensaje' =>$mensaje, 'validacion' => $validacion,'infoConsolidacion' =>$infoConsolidacion, 'cbtabla'=>$cbTabla,"SQL"=>$SQL);
    // $datosVal=array('mensaje' =>'Archivo subido correctamente', 'validacion' => true);
    //print_r($datosVal);
    //exit();
    return $datosVal;
}
////
function fnObtenerContenidoCotizacion($archivo)
{

    //$objPHPExcel->setActiveSheetIndex(0)->rangeToArray('A1:C3');

    $arreglo=array();
    $datos=array();

    try {
        $inputFileType = PHPExcel_IOFactory::identify($archivo);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($archivo);

        $hoja = $objPHPExcel->getSheet(0);
        $filasTotales = $hoja->getHighestDataRow();
        $columnasTotales = $hoja->getHighestDataColumn();
        $columnaIndex = PHPExcel_Cell::columnIndexFromString($columnasTotales);


        /*for ($fila = 0; $fila <= $filasTotales; $fila++){
            for($columna=0;$columna<=$columnaIndex;$columna++){
                $col=$hoja->getCellByColumnAndRow($columna, $fila)->getCalculatedValue();
                $arreglo[$fila][$columna]=$col;

            }
        }*/
        $provedor=$objPHPExcel->getActiveSheet()->getCell('E6')->getCalculatedValue();
        $numerorequi=$objPHPExcel->getActiveSheet()->getCell('D11')->getCalculatedValue();
        for ($fila = 13; $fila <$filasTotales; $fila++) {
            $partida = $objPHPExcel->getActiveSheet()->getCell('A'.$fila)->getCalculatedValue();
            $codigoArticulo = $objPHPExcel->getActiveSheet()->getCell('D'.$fila)->getCalculatedValue();
            $descArt = $objPHPExcel->getActiveSheet()->getCell('E'.$fila)->getCalculatedValue();
            $cotizacion = $objPHPExcel->getActiveSheet()->getCell('K'.$fila)->getCalculatedValue();

            //echo $partida;
            //$arreglo[$fila][]=$partida;
            //$arreglo[]=$partida."-".$codigoArticulo."-".$descArt;
            if (is_null($cotizacion)) {
                $cotizacion=0;
            }
            $arreglo[] = array( 'partida' => $partida, 'codArt' => $codigoArticulo,'descripcion'=> $descArt,'cotizacion'=>$cotizacion );
        }

        $datos[]=$provedor;
        $datos[]=$arreglo;
        $datos[]=$numerorequi;
        return $datos;
    } catch (Exception $e) {
        die('Error loading file "'.pathinfo($archivo, PATHINFO_BASENAME).'": '.$e->getMessage());
    }
}
if (isset($_FILES['archivos'])) {
    $arregloContenido=array();
    $funcion=$_POST['funcion'];
    $tipo=$_POST['tipo'];
    $trans=$_POST['trans'];
    $no=$_POST['nopermitidos'];
    $esmultiple=$_POST['esmultiple'];
    $no=str_replace(" ", "", $no);
    $nopermitidos='';
    $nopermitidos= explode(',', $no);
    $datosVal=[];


    $vallayout='0';
    if ($tipo=='19' || $tipo=='250' || $tipo=='20') {
        $vallayout='1';
    }

    foreach ($_FILES['archivos']['tmp_name'] as $key => $tmp_name) {
        if ($esmultiple==0) {
            switch ($tipo) {
            case '20':
                $datosVal=array('mensaje' =>'Layouts de Tesorería', 'validacion' => true);
                break;
            case '250':
                $arregloContenido= fnObtenerContenidoArchivo($_FILES['archivos']['tmp_name'][$key]);
                $datosVal=fnValidaLayoutPresupuesto($arregloContenido, $db);

                break;

            case '19':
                    $idanexoGlobal=null;
                    $urGlobal=null;
                    $tipoGlobal=null;
                    $idrequisicionGlobal=null;

                    if ((isset($_POST['idanexoGlobal'])) && (isset($_POST['urGlobal'])) && (isset($_POST['tipo'])) && (isset($_POST['idrequisicionGlobal']))) {
                        $idanexoGlobal=$_POST['idanexoGlobal'];
                        $urGlobal=$_POST['urGlobal'];
                        $tipoGlobal=$_POST['tipo'];
                        $idrequisicionGlobal=$_POST['idrequisicionGlobal'];

                        //print_r($idrequisicionGlobal);
                    }
                    $SQL='SELECT MAX(nu_id_documento) as id from tb_archivos';
                    $ErrMsg='Fallo al obtener id para anexo';

                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                    while ($myrow = DB_fetch_array($TransResult)) {
                        $idanexoGlobal= $myrow['id'];
                    }
                    //$myrow = DB_fetch_array($TransResult   //alternativa //sin while
                    $trans=$idrequisicionGlobal;
                     $arregloContenido= fnObtenerContenidoArchivo($_FILES['archivos']['tmp_name'][$key]);
                    $datosVal=fnValidaLayoutRequisicion($arregloContenido, $db, ($idanexoGlobal+1), $urGlobal, $tipoGlobal, $idrequisicionGlobal);
                break;

            case 'cotizacion':
                $datosCotizacion= fnObtenerContenidoCotizacion($_FILES['archivos']['tmp_name'][$key]);
                break;


            case '51':
                try {
                    $arregloContenido= fnObtenerContenidoArchivo($_FILES['archivos']['tmp_name'][$key]);
                    $typeAnexo = 51;
                    $trans = $folio = GetNextTransNo($typeAnexo, $db);
                    $datosVal = fnValidAnexoTecnico($db, $arregloContenido, $folio, $typeAnexo);
                    $datosVal['folio'] = $folio;
                    // var_dump($datosVal);
                } catch (Exception $e) {
                    $ErrMsg .= $e->getMessage();
                }
            break;
            case '600':
                try {
                    $lineas = file($_FILES['archivos']['tmp_name'][$key]);
                    // $lineas =utf8_encode($lineas);
                    //print_r($lineas);
                    $datosVal= fnGuardarEstadoCuentaBanco($arregloContenido, $lineas, $db);
                    // $filename = $_FILES['archivos']['tmp_name'][$key];
                // $handle = fopen($filename, "r");
                // $datosVal= fnGuardarEstadoCuentaBanco($arregloContenido,$handle,$db);

               // $datosVal=array('mensaje' =>'Estado de cuenta subido correctamente', 'validacion' => true);
                } catch (Exception $e) {
                    $ErrMsg .= $e->getMessage();
                }
            break;
            case '2373':
            // print_r($_POST);
            // print_r($_FILES['archivos']);
            //  DB_Txn_Begin($db);
            // try {
            //     $begin=date("Y-m-d H:i:s", strtotime($_POST['begin']));
            //     $end=date("Y-m-d H:i:s", strtotime($_POST['end']));
                
                
            //         $SQL = "INSERT INTO tb_cat_esenario_paaas(id_nu_folio_esenario,id_nu_ur,id_nu_ue,dtm_fecha_inicio,dtm_fecha_termino,id_nu_dependencia,ind_estatus,dtm_fecha_consumo) 
            //             VALUES ('". $_POST['folio'] ."','".$_POST['ur']."','".$_POST['ue']."','".$begin."','".$end."','".$_POST['legal']."','1','".$end."')";
                      
                   
            //         $TransResult = DB_query($SQL, $db);

            //         if($TransResult == true){
            //             DB_Txn_Commit($db);
                      
            //         }else{
            //             DB_Txn_Rollback($db);
            //         }
            //     } catch (Exception $e) {
            //         $ErrorMsg= $e->getMessage();
            //         DB_Txn_Rollback($db);
            //     }
            $datosVal=array('mensaje' =>'Layouts de Tesorería', 'validacion' => true);
            break;
            default:
                    $datosVal=array('mensaje' =>'Archivo subido correctamente', 'validacion' => true);
                break;
            }
        }
        //print_r($datosVal);
        // solo un archivo
        if (($esmultiple==0) && ($datosVal['validacion']==true)) {
            $ncoicidencia=0;
            $file_name = $key.$_FILES['archivos']['name'][$key];
            $file_size =$_FILES['archivos']['size'][$key];
            $file_tmp =$_FILES['archivos']['tmp_name'][$key];
            $file_type=$_FILES['archivos']['type'][$key];
            $file_name=str_replace(" ", "", $file_name);

            if (!empty($no)) {
                for ($j=0;$j<count($nopermitidos);$j++) {
                    $coincidencia = strpos($file_name, $nopermitidos[$j]);
                    if ($coincidencia!==false) {
                        $ncoicidencia+=1;
                    }
                }
                // fin buscar coincidencias
                if ($ncoicidencia==0) {
                    move_uploaded_file($file_tmp, "../archivos/".$file_name);
                    //$info[]=($file_name);
                    $cadenaDatosInsertar.="('". $_SESSION['UserID']."','".$file_type."','".$file_name."','archivos/".$file_name."','1','".$tipo."','".$trans."','".$file_name."','". $vallayout."','".$funcion."'),";
                }
            } else {
                move_uploaded_file($file_tmp, "../archivos/".$file_name);
                //$info[]=($file_name);
                $cadenaDatosInsertar.="('". $_SESSION['UserID']."','".$file_type."','".$file_name."','archivos/".$file_name."','1','".$tipo."','".$trans."','".$file_name."','". $vallayout."','".$funcion."'),";
            }//
        }//fin un solo archivo
         // varios archivos
        if ($esmultiple==1) {
            //cuando puedas subir varios archivos
            $ncoicidencia=0;
            $file_name = $key.$_FILES['archivos']['name'][$key];
            $file_size =$_FILES['archivos']['size'][$key];
            $file_tmp =$_FILES['archivos']['tmp_name'][$key];
            $file_type=$_FILES['archivos']['type'][$key];
            $file_name=str_replace(" ", "", $file_name);
            if ($funcion=="all"&&($tipo=="all")) { // cuando se ativa visualizador de todos
                $funcion=-1;
                $tipo=-1;
                $trans=-1;
            }
            if($tipo=='2373'){
                $tipo='285';
            }
            if (!empty($no)) {
                for ($j=0;$j<count($nopermitidos);$j++) {
                    $coincidencia = strpos($file_name, $nopermitidos[$j]);
                    if ($coincidencia!==false) {
                        $ncoicidencia+=1;
                    }
                }
                // fin buscar coincidencias
                if ($ncoicidencia==0) {
                    move_uploaded_file($file_tmp, "../archivos/".$file_name);
                    //$info[]=($file_name);
                    $cadenaDatosInsertar.="('". $_SESSION['UserID']."','".$file_type."','".$file_name."','archivos/".$file_name."','".$funcion."','1','".$tipo."','".$trans."','".$file_name."','". $vallayout."'),";
                }
            } else {
                move_uploaded_file($file_tmp, "../archivos/".$file_name);
                //$info[]=($file_name);
                $cadenaDatosInsertar.="('". $_SESSION['UserID']."','".$file_type."','".$file_name."','archivos/".$file_name."','".$funcion."','1','".$tipo."','".$trans."','".$file_name."','". $vallayout."'),";
            }//
        }  //fin varios archivos
    }// fin  foreach para cada archivo

    // SET  new  escene in PAAS
        $existeUe=0;
        $transnoEscenario=0;
        if($tipo=='2373'){

            $ur=$_POST['ur'];
            $ue=$_POST['ue'];
            
            if((!empty($ur)) &&(!empty($ue))){

                 $SQL="  SELECT COUNT(*) AS total FROM  tb_cat_esenario_paaas  WHERE id_nu_ur='".$ur."' AND id_nu_ue='".$ue."' AND nu_anio='2018' AND ind_estatus!='5'";
                $TransResult = DB_query($SQL, $db);

                  while ($row = DB_fetch_array($TransResult)) {
                        $existeUe=$row['total'];
                  }

            }
           if($existeUe==0){


            DB_Txn_Begin($db);
            try {
                $transnoEscenario= GetNextTransNo('285', $db);
                $begin=date("Y-m-d H:i:s", strtotime($_POST['begin']));
                $end=date("Y-m-d H:i:s", strtotime($_POST['end']));
                $folio=$_POST['oficio'];
                $year=$_POST['year'];
                $obs=$_POST['comments'];
                
                $SQL = "INSERT INTO tb_cat_esenario_paaas(id_nu_folio_esenario,id_nu_ur,id_nu_ue,dtm_fecha_inicio,dtm_fecha_termino,id_nu_dependencia,ind_estatus,nu_anio,ln_oficio,ln_comments) 
                        VALUES ('".$transnoEscenario."','".$_POST['ur']."','".$_POST['ue']."','".$begin."','".$end."','".$_POST['legal']."','1','".$year."','".$folio."','".$obs."')";
                      
                   
                $TransResult = DB_query($SQL, $db);

                if($TransResult == true){
                    DB_Txn_Commit($db);
                      
                }else{
                    DB_Txn_Rollback($db);
                    }
                } catch (Exception $e) {
                    $ErrorMsg= $e->getMessage();
                    DB_Txn_Rollback($db);
                }
            }
        }

    if ($tipo!='cotizacion') {
        //print_r("entro");
        if (($esmultiple==0) && ($datosVal['validacion']==true)) {
            $cadenaDatosInsertar= substr($cadenaDatosInsertar, 0, -1);

              $cadenaDatosInsertar= substr($cadenaDatosInsertar, 0, -1);
            if($tipo=='2373' ){
                 $aux=explode(",", $cadenaDatosInsertar);
                 if($transnoEscenario==0){

                        if(!empty($_POST['trans'])) {
                            $transnoEscenario=$_POST['trans'];
                        }

                 }
                 $cadenaDatosInsertar=$aux[0].",".$aux[1].",".$aux[2].",".$aux[3].",".$aux[5].",".$aux[4].",'285'".",".$aux[7].",".$aux[8].",'".$transnoEscenario."')";
               
            }

            $SQL = "INSERT INTO tb_archivos (`ln_userid`, `sn_tipo`, `ln_nombre_interno_archivo`,`txt_url`,`nu_funcion`,`ind_active`,`nu_tipo_sys`,`ln_nombre`,`ind_es_layout`,`nu_trasnno`) VALUES ".$cadenaDatosInsertar;

            
            $ErrMsg = "Problema al cargar documento";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            // print_r($SQL);
            if ($tipo=='51') {
                $data = $datosVal;
            } elseif ($tipo=='600') {
                $data = $datosVal;
            } else {
                $data= $datosVal;//$datosVal['mensaje'];
            }
        }

        if ($esmultiple==1) {
            $cadenaDatosInsertar= substr($cadenaDatosInsertar, 0, -1);

            $SQL = "INSERT INTO tb_archivos (`ln_userid`, `sn_tipo`, `ln_nombre_interno_archivo`,`txt_url`,`nu_funcion`,`ind_active`,`nu_tipo_sys`,`nu_trasnno`,`ln_nombre`,`ind_es_layout`) VALUES ".$cadenaDatosInsertar;
            $ErrMsg = "Problema al cargar documento";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            //print_r($SQL);
            /*
          $data.= "<table border='1'>";
          for($f=0;$f<count($arregloContenido);$f++) {
          $data.= "<tr>";
          for($c=0;$c<count($arregloContenido[$f]);$c++) {
           $data.="<td>". $arregloContenido[$f][$c]."</td>";

          }
          $data."</tr>";

          }
          $data.= "</table>"; */
        }
        

        if($tipo=='2373'){
            if($existeUe==0){

             $data=array( 'msg'=>"Se guardo exitosamente el oficio <b>'".$folio."'</b> con folio <b>".$transnoEscenario.'</b>','folio'=>$transnoEscenario);
            }else{
                $numeroEscena="";
              if(isset($_POST['numberScene'])){
                $numeroEscena=$_POST['numberScene'];
              }
              if($numeroEscena!=""){
               
                $begin=date("Y-m-d H:i:s", strtotime($_POST['begin']));
                $end=date("Y-m-d H:i:s", strtotime($_POST['end']));
                $folio=$_POST['oficio'];
                $year=$_POST['year'];
                $obs=$_POST['comments'];
                
                
                $SQL="UPDATE tb_cat_esenario_paaas SET dtm_fecha_inicio='".$begin."', dtm_fecha_termino='".$end."',ln_comments='".$obs."'  WHERE id_nu_folio_esenario='".$numeroEscena."'";
                DB_query($SQL, $db);

                $data=array( 'msg'=>"Se actualizó el rango de fechas",'folio'=>$numeroEscena);
              }else{
                $data=array( 'msg'=>"Ya existe un escenario para la UE seleccionada",'folio'=>'undefined');
              }
              
            }
        }else{
             $data='Archivo guardado correctamente';
        }
       
    }// if different cotizacion
    if ($tipo!='cotizacion') {
        $contenido =$data;
    } else {
        $contenido=$datosCotizacion;
    }
    $result = true;

    //fnInsertPruebasDocumentos($db, 'text/csv', $nombreArchivoGeneral, $linkDescarga, $type, $funcion, $datosTransno[$i], 'Layout Generado', 0);
} //fin si existe archivos

if (isset($_POST['proceso'])) {
    $proceso=$_POST['proceso'];

    switch ($proceso) {

        case 'obtenerDatosArchivos':
            $funcion=$_POST['funcion'];
            $tipo=$_POST['tipo'];
            $transno=$_POST['trans'];
            $SQLtransno=" ";

            if ($transno!="all") {
                $SQLtransno=" AND nu_trasnno = '".$transno."'" ;
            }

           if ($transno=="all") {
               $SQLtransno="";
           }
            if (empty($transno)||$transno=="") {
                $SQLtransno=" AND nu_trasnno = '"."0"."'" ;
            }

            if (($funcion!="all") && ($tipo!="all")) {
                //$SQL= "SELECT `nu_id_documento`,`ln_userid`, `sn_tipo`, `ln_nombre`,`txt_url`,`dtm_fecharegistro` FROM tb_archivos WHERE nu_funcion='".$funcion."' AND ind_active='1'";
                $SQL="SELECT nu_id_documento,ln_userid,sn_tipo,ln_nombre,txt_url,dtm_fecharegistro,nu_funcion,sec_functions.title,nu_tipo_sys,systypesinvtrans.typename FROM tb_archivos
                    INNER JOIN sec_functions ON tb_archivos.nu_funcion=sec_functions.functionid
                    INNER JOIN systypesinvtrans ON tb_archivos.nu_tipo_sys=systypesinvtrans.typeid
                    WHERE ind_active='1' AND  nu_funcion>0";

                $SQL.=" AND nu_tipo_sys='".$tipo."'".$SQLtransno;
                $SQL.='ORDER BY dtm_fecharegistro DESC';
            } else {
                //$SQL= "SELECT `nu_id_documento`,`ln_userid`, `sn_tipo`, `ln_nombre`,`txt_url`,`dtm_fecharegistro` FROM tb_archivos WHERE ind_active='1'";
                $SQL="SELECT nu_id_documento,ln_userid,sn_tipo,ln_nombre,txt_url,dtm_fecharegistro,nu_funcion,sec_functions.title,nu_tipo_sys,systypesinvtrans.typename FROM tb_archivos
                    INNER JOIN sec_functions ON tb_archivos.nu_funcion=sec_functions.functionid
                    INNER JOIN systypesinvtrans ON tb_archivos.nu_tipo_sys=systypesinvtrans.typeid
                    WHERE ind_active='1' AND  nu_funcion>0
                    ORDER BY dtm_fecharegistro DESC";
            }

            $ErrMsg = "Sin archivos";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            while ($myrow = DB_fetch_array($TransResult)) {
                /*$info[] = array( 'id,2%,,'=>'<input type="checkbox" value="'.$myrow['nu_id_documento'].'" class="datosArchivos" name="datoArchivo">','tipo,20%,TIPO,' => $myrow ['sn_tipo'],'nombre,40%,NOMBRE ARCHIVO,' => $myrow ['ln_nombre'],'usuario,40%,USUARIO,' => $myrow ['ln_userid'],'fecha'=>$myrow ['ln_userid']); */
                $tipo=explode(".", $myrow ['txt_url']);
                $tipo=$tipo[1]; //'<input type="checkbox" value="'.$myrow['nu_id_documento'].'" class="datosArchivos" name="datoArchivo">'
                $info[] = array( 'cajacheckbox'=>false,'id'=>$myrow['nu_id_documento'],'tipo' =>   $tipo,'nombre' => $myrow ['ln_nombre'],'funcion'=>$myrow['title'],'tipo_doc'=>$myrow['typename'],'usuario' => $myrow ['ln_userid'],'fecha'=>date("d-m-Y", strtotime($myrow['dtm_fecharegistro'])));
            }
            $contenido = array('DatosArchivos' => $info);
            $result = true;
            break;

        case 'eliminarArchivosSubidos':
            if (!empty($_POST['archivos'])) {
                $valores='(';
                $archivos=$_POST['archivos'];

                for ($a=0; $a<count($archivos);$a++) {
                    $valores.=$archivos[$a].",";
                }

                $valores=substr($valores, 0, -1);
                $valores.=')';

                $SQL= "UPDATE  tb_archivos SET ind_active='0' where nu_id_documento IN ".$valores;
                $ErrMsg = "No se eliminó ";
                $TransResult = DB_query($SQL, $db, $ErrMsg);


                if (isset($_POST['requisicion'])) {
                    $SQL= "DELETE FROM  tb_cnfg_anexo_tecnico where nu_anexo IN ".$valores;
                    $ErrMsg = "No se eliminó ";
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                }


                $contenido = "Se eliminaron los archivos.";
                $result = true;
            }

            break;

        case 'recuperarLayouts':
            $funcion=$_POST['funcion'];
            $tipo=$_POST['tipo'];
            $transno=$_POST['transno'];

            $SQLtransno=" ";
            if ($transno!="all") {
                $SQLtransno=" AND nu_trasnno = '".$transno."'" ;
            }

            $SQL= "SELECT `nu_id_documento`,`ln_userid`, `sn_tipo`, `ln_nombre`,`txt_url`,`ind_permiso_active`,`nu_trasnno` FROM tb_archivos WHERE nu_funcion='".$funcion."' AND ind_active='1'";
            $SQL.="AND nu_tipo_sys='".$tipo."' ".$SQLtransno;
            $SQL.="ORDER BY dtm_fecharegistro DESC";
            $ErrMsg = "Sin archivos";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            while ($myrow = DB_fetch_array($TransResult)) {
                $info[] = array( 'id,2%,,'=>'<div class="datosLayouts"><input class="layoutRef" type="checkbox" value="'.$myrow['nu_id_documento'].'"  name="datoLayout"><div class="permitidoLayout" style="display:none;">'.$myrow ['nu_trasnno'].' </div></div> ','tipo,20%,TIPO,' => $myrow ['sn_tipo'],'nombre,40%,NOMBRE ARCHIVO,' => $myrow ['ln_nombre'],'usuario,40%,USUARIO,' => $myrow ['ln_userid']);
            }

            $contenido = array('DatosLayouts' => $info);
            $result = true;
            break;

        case 'eliminarArchivosSubidos':
            if (!empty($_POST['archivos'])) {
                $valores='(';
                $archivos=$_POST['archivos'];

                for ($a=0; $a<count($archivos);$a++) {
                    $valores.=$archivos[$a].",";
                }

                $valores=substr($valores, 0, -1);
                $valores.=')';

                $SQL= "UPDATE  tb_archivos SET ind_active='0' where nu_id_documento IN ".$valores;
                $ErrMsg = "No se eliminó ";
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $contenido = "Se eliminaron los archivos.";
                $result = true;
            }

            break;

        case 'eliminarLayouts':
            if (!empty($_POST['archivos'])) {

                // valores eliminar
                $valores='(';
                $archivos=$_POST['archivos'];
                for ($a=0; $a<count($archivos);$a++) {
                    $valores.=$archivos[$a].",";
                }
                $valores=substr($valores, 0, -1);
                $valores.=')';
                //fin valores eliminar

                // como nada mas es un archivo se puede hacer esto
                $SQL="SELECT ind_permiso_active FROM tb_archivos WHERE nu_id_documento IN".$valores;
                $ErrMsg="Error al ver permiso de elminación";
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $resultado = DB_fetch_array($TransResult);

                switch ($resultado) {
                    case '0':

                $SQL= "UPDATE  tb_archivos SET ind_active='0' where nu_id_documento IN ".$valores;
                $ErrMsg = "No se eliminó ";
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $contenido = "Se eliminó el archivo.";
                        break;

                        case '1':
                            $contenido = "No se puede elminar el Layout esta vinculado a un proceso.";
                        break;

                    default:
                        # code...
                        break;
                }


                $result = true;
            }

            break;

    case 'descargarArchivos':

        $contenido =fnDescargaArchivos($db, $where=''); //'<a id="enlaceArchivo" class="btn bgc8" style="color:#fff !important;" href="archivostemporales/'.$nombreArch.'.zip"> Click aqui para descargar </a><br/> <br>'; //"<a href=".$destino.$nombreArch.">Descargar </a>';
        $result = true;
        break;

        case 'generarExcelCompraNet':
            $datosprove=$_POST['proveedores'];
            $requidatos=$_POST['datosrequi'];

            $val='';
            //print_r($requidatos);
            for ($a=0;$a<count($datosprove);$a++) {
                $val.="'".$datosprove[$a]."',";
            }
            $val=substr($val, 0, -1);

         $SQL="SELECT email,suppname,address1,address2,address3,address4,address5,address6,supplierid as provedor FROM  suppliers  where supplierid in(".$val.")";


            $ErrMsg = "No hay  archivos";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            while ($myrow = DB_fetch_array($TransResult)) {
                $info[]= array('email' =>$myrow['email'],
                    'nombre' =>$myrow['suppname'],
                    'ad1' =>$myrow['address1'],
                    'ad2' =>$myrow['address2'],
                    'ad3' =>$myrow['address3'],
                    'ad4' =>$myrow['address4'],
                    'ad5' =>$myrow['address5'],
                    'ad6' =>$myrow['address6'],
                    'provedor' =>$myrow['provedor']

                  );
                $nombre=str_replace(' ', "_", $myrow['suppname']);

                //fnCrearExcelConCeldasBloqueadas($myrow['email'],$nombre);
            }

            $cadenaInsertar=  fnCrearExcelConCeldasBloqueadas($info, $requidatos, $db);

            $SQL="INSERT INTO tb_archivos (ln_userid,ln_nombre_interno_archivo,txt_url,nu_funcion,ind_active,nu_tipo_sys,nu_trasnno,ln_nombre,ind_es_layout) VALUES ".$cadenaInsertar;
            //print_r($SQL);
            $ErrMsg = "Problema al cargar documento";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $contenido ='';
            $result = true;

        break;
        //danger keep calm dont delete
        case 'enviarCotizacionV2':
          try {
              $requisiones= $_POST['requis'];
              $partidas=  array();
              $infoProvSug=array();
              $cadenaRequis='';
              //por cada  requisicion
              for ($a=0;$a<count($requisiones);$a++) {
                  $cadenaRequis.="'".$requisiones[$a]."',";
                  /// ir por datos requi
                  $datosRequis =fnGetDatosRequi($requisiones[$a], $db);

                  for ($d=0;$d<count($datosRequis[$d]);$d++) {
                      foreach ($datosRequis[$d] as $key => $value) {
                          if ($key=='idPartida') {
                              $partidas[]=$value;
                          }
                      }
                  }
                  ///fin  ir por datos  requi

                  //voy por provsug
                  // echo $requisiones[$a];
                  $infoProvSug=fnGetProvsugeridos($partidas, $db);
                  fnGenerarMultipleExcel($infoProvSug, $datosRequis, $requisiones[$a], $db);


                  //print_r($infoProvSug);
                  //print_r($datosRequis );

                //fin voy por provsug

               //envio correo
               //
              }//fin  de  cada  requisicion
              //$cadenaInsertar= substr($cadenaInsertar, 0, -1);
              $cadenaRequis=$cadenaRequis.substr($cadenaRequis, 0, -1);
              $SQL2="UPDATE tb_proceso_compra_prov_sugeridos SET ln_nombre_estatus='Solicitud de Cotización'  WHERE nu_requi IN (".$cadenaRequis.")";

              $ErrMsg = "No se guardo información";
              $TransResult = DB_query($SQL2, $db, $ErrMsg);

              $contenido = array('datosPro'=>$info);
              $result = true;
          } catch (Exception $excepcion) {
              $ErrMsg .= $excepcion->getMessage();
          }
            break;
        default:
            break;
    } // fin switch
}// fin if isset
function fnGenerarMultipleExcel($datosprove, $requidatos, $requisicion, $db)
{
    $val='';
    //print_r($requidatos);
    for ($a=0;$a<count($datosprove);$a++) {
        $val.="'".$datosprove[$a]."',";
    }
    $val=substr($val, 0, -1);

    $SQL="SELECT email,suppname,address1,address2,address3,address4,address5,address6,supplierid as provedor FROM  suppliers  where supplierid in(".$val.")";


    $ErrMsg = "No hay  archivos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[]= array('email' =>$myrow['email'],
                    'nombre' =>$myrow['suppname'],
                    'ad1' =>$myrow['address1'],
                    'ad2' =>$myrow['address2'],
                    'ad3' =>$myrow['address3'],
                    'ad4' =>$myrow['address4'],
                    'ad5' =>$myrow['address5'],
                    'ad6' =>$myrow['address6'],
                    'provedor' =>$myrow['provedor']

                  );
        $nombre=str_replace(' ', "_", $myrow['suppname']);

        //fnCrearExcelConCeldasBloqueadas($myrow['email'],$nombre);
    }

    $cadenaInsertar=  fnCrearExcelConCeldasBloqueadas($info, $requidatos, $requisicion, $db);

    $SQL="INSERT INTO tb_archivos (ln_userid,ln_nombre_interno_archivo,txt_url,nu_funcion,ind_active,nu_tipo_sys,nu_trasnno,ln_nombre,ind_es_layout) VALUES ".$cadenaInsertar;
    //print_r($SQL);
    $ErrMsg = "Problema al cargar documento";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido ='Se enviaron los correos'; //array('datosPro'=>$info);
    $result = true;
}
$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $TransResult, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
//echo json_encode($dataObj, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
 header('Content-Type: application/json');
echo json_encode($dataObj);
