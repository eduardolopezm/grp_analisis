<?php
/**
 * Impresion Suficiencia ManuaL y Automática
 *
 * @category Pdf
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/11/2017
 * Fecha Modificación: 02/11/2017
 * Impresion Suficiencia ManuaL y Automática
 */

$PageSecurity = 1;
session_start();
include('config.php');
include('includes/session.inc');
$PrintPDF = $_GET ['PrintPDF'];
$_POST ['PrintPDF'] = $PrintPDF;
include('jasper/JasperReport.php');
include("includes/SecurityUrl.php");

set_time_limit(6000);

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

if (isset($_GET['PrintExcel'])) {
    // Si es Excel
    // echo "<br>generar Excel";
    // echo "<br>type: ".$_GET['type'];
    // echo "<br>transno: ".$_GET['transno'];
    
    set_include_path(implode(PATH_SEPARATOR, array(realpath('lib/PHPExcel-1.8/Classes/PHPExcel/'), get_include_path(),)));
    require_once("lib/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php");

    $objPHPExcel = new PHPExcel;
    // set default font
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
    // set default font size
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
    // create the writer
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");

    // writer already created the first sheet for us, let's get it
    $objSheet = $objPHPExcel->getActiveSheet();

    // rename the sheet
    $objSheet->setTitle('Excel');

    // Estilo para bordes
    $styleArray = array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    );

    // Tamaño columna
    $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
    // Ajustar texto
    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

    // Encabezado INICIO
    $objSheet->getCell('B1')->setValue('Dirección General de Programación y Presupuesto B de la SHCP');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B1:B1');
    $objPHPExcel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY);
    $objPHPExcel->getActiveSheet()->getStyle('B1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_JUSTIFY);

    $objSheet->getCell('B2')->setValue('Presente.');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B2:B2');
    $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY);
    $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_JUSTIFY);

    $objSheet->getCell('B3')->setValue('De Conformidad con las disponibles legales vigentes, y de considerarlo procedente, sirvase autorizar las siguientes afectaciones al presupuesto de egresos en vigor');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B3:B6');
    $objPHPExcel->getActiveSheet()->getStyle('B3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY);
    $objPHPExcel->getActiveSheet()->getStyle('B3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('D1')->setValue('LIC. MARCELO LÓPEZ SANCHEZ');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('D1:J1');
    $objPHPExcel->getActiveSheet()->getStyle('D1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY);
    $objPHPExcel->getActiveSheet()->getStyle('D1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_JUSTIFY);

    $objSheet->getCell('D2')->setValue('Presente');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('D2:J2');
    $objPHPExcel->getActiveSheet()->getStyle('D2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY);
    $objPHPExcel->getActiveSheet()->getStyle('D2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_JUSTIFY);

    $objSheet->getCell('D3')->setValue('De acuerdo con las disposiciones legales vigentes se autorizan las siguientes afectaciones al presupuesto de egresos en vigor');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('D3:J6');
    $objPHPExcel->getActiveSheet()->getStyle('D3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY);
    $objPHPExcel->getActiveSheet()->getStyle('D3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_JUSTIFY);


    $objSheet->getCell('L1')->setValue('Solicitud');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('L1:M1');
    $objPHPExcel->getActiveSheet()->getStyle('L1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objSheet->getCell('N1')->setValue('De Fecha');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('N1:P1');
    $objPHPExcel->getActiveSheet()->getStyle('N1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objSheet->getCell('Q1')->setValue('Recibido DGP');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('Q1:S1');
    $objPHPExcel->getActiveSheet()->getStyle('Q1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objSheet->getCell('T1')->setValue('Hoja');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('T1:U1');
    $objPHPExcel->getActiveSheet()->getStyle('T1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objSheet->getCell('L2')->setValue('');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('L2:M3');

    $objSheet->getCell('N2')->setValue('Día');
    $objPHPExcel->getActiveSheet()->getStyle('N2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objSheet->getCell('O2')->setValue('Mes');
    $objPHPExcel->getActiveSheet()->getStyle('O2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objSheet->getCell('P2')->setValue('Año');
    $objPHPExcel->getActiveSheet()->getStyle('P2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objSheet->getCell('N3')->setValue(''.date('d'));
    $objPHPExcel->getActiveSheet()->getStyle('N3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objSheet->getCell('O3')->setValue(''.date('m'));
    $objPHPExcel->getActiveSheet()->getStyle('O3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objSheet->getCell('P3')->setValue(''.date('Y'));
    $objPHPExcel->getActiveSheet()->getStyle('P3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objSheet->getCell('Q2')->setValue('');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('Q2:Q3');

    $objSheet->getCell('R2')->setValue('');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('R2:R3');

    $objSheet->getCell('S2')->setValue('');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('S2:S3');

    $objSheet->getCell('T2')->setValue('No.');
    $objPHPExcel->getActiveSheet()->getStyle('T2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('T2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('U2')->setValue('De');
    $objPHPExcel->getActiveSheet()->getStyle('U2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('U2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('L4')->setValue('Dirección General de Programación, Presupuesto y Finanzas');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('L4:U4');

    $objSheet->getCell('L5')->setValue('Fecha');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('L5:N5');
    $objPHPExcel->getActiveSheet()->getStyle('L5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objSheet->getCell('O5')->setValue('No. de Oficio');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('O5:Q5');
    $objPHPExcel->getActiveSheet()->getStyle('O5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objSheet->getCell('R5')->setValue('Tipo Doc');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('R5:S5');
    $objPHPExcel->getActiveSheet()->getStyle('R5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objSheet->getCell('T5')->setValue('Entidad');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('T5:U5');
    $objPHPExcel->getActiveSheet()->getStyle('T5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objSheet->getCell('L6')->setValue('');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('L6:N6');

    $objSheet->getCell('O6')->setValue('');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('O6:Q6');

    $objSheet->getCell('R6')->setValue('M');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('R6:S6');
    $objPHPExcel->getActiveSheet()->getStyle('R6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('R6')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('T6')->setValue('08');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('T6:U6');
    $objPHPExcel->getActiveSheet()->getStyle('T6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('T6')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objPHPExcel->getActiveSheet()->getStyle('L1:U6')->applyFromArray($styleArray);

    // Tabla de información
    $objSheet->getCell('A7')->setValue('No. Secuencia');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A7:A10');
    $objPHPExcel->getActiveSheet()->getStyle('A7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('A7')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('B7')->setValue('Clave Presupuestaria');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B7:B10');
    $objPHPExcel->getActiveSheet()->getStyle('B7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('B7')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('C7')->setValue('Tipo de Operación');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('C7:C10');
    $objPHPExcel->getActiveSheet()->getStyle('C7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('C7')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('D7')->setValue('Clave de Regular');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('D7:E8');
    $objPHPExcel->getActiveSheet()->getStyle('D7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('D7')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('D9')->setValue('Tipo');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('D9:D10');
    $objPHPExcel->getActiveSheet()->getStyle('D9')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('D9')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('E9')->setValue('Justificación');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('E9:E10');
    $objPHPExcel->getActiveSheet()->getStyle('E9')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('E9')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('F7')->setValue('Importe');
    $objPHPExcel->getActiveSheet()->getStyle('F7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    
    $objSheet->getCell('F8')->setValue('Total de Operación');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('F8:F10');
    $objPHPExcel->getActiveSheet()->getStyle('F8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('F8')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('G7')->setValue('Calendario');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('G7:K7');
    $objPHPExcel->getActiveSheet()->getStyle('G7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objSheet->getCell('G8')->setValue('Periodo de Aut.');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('G8:J8');
    $objPHPExcel->getActiveSheet()->getStyle('G8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('G8')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('G9')->setValue('De');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('G9:H9');
    $objPHPExcel->getActiveSheet()->getStyle('G9')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('G9')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('I9')->setValue('A');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('I9:J9');
    $objPHPExcel->getActiveSheet()->getStyle('I9')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('I9')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('G10')->setValue('Día');
    $objPHPExcel->getActiveSheet()->getStyle('G10')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('G10')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('H10')->setValue('Mes');
    $objPHPExcel->getActiveSheet()->getStyle('H10')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('H10')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('I10')->setValue('Día');
    $objPHPExcel->getActiveSheet()->getStyle('I10')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('I10')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('J10')->setValue('Mes');
    $objPHPExcel->getActiveSheet()->getStyle('J10')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('J10')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('K8')->setValue('Importe Específico por Mes');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('K8:L10');
    $objPHPExcel->getActiveSheet()->getStyle('K8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('K8')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('M7')->setValue('Horas');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('M7:M10');
    $objPHPExcel->getActiveSheet()->getStyle('M7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('M7')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('N7')->setValue('Categoría');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('N7:O10');
    $objPHPExcel->getActiveSheet()->getStyle('N7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('N7')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('P7')->setValue('No. de Plazas');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('P7:Q7');
    $objPHPExcel->getActiveSheet()->getStyle('P7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('P7')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('P8')->setValue('De a');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('P8:P10');
    $objPHPExcel->getActiveSheet()->getStyle('P8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('P8')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('Q8')->setValue('Total');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('Q8:Q10');
    $objPHPExcel->getActiveSheet()->getStyle('Q8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('Q8')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('R7')->setValue('RY/OC');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('R7:R10');
    $objPHPExcel->getActiveSheet()->getStyle('R7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('R7')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('S7')->setValue('Importe');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('S7:U10');
    $objPHPExcel->getActiveSheet()->getStyle('S7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('S7')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objPHPExcel->getActiveSheet()->getStyle('A7:U10')->applyFromArray($styleArray);
    // Tabla de información
    // Encabezado FIN
    
    // INICIO Información
    $SQL = "SELECT 
	chartdetailsbudgetlog.idmov,
	chartdetailsbudgetlog.qty,
	chartdetailsbudgetlog.cvefrom,
	chartdetailsbudgetlog.period,
	periods.lastdate_in_period,
	DATE_FORMAT(periods.lastdate_in_period,'%m') as mesOpe,
	CASE WHEN chartdetailsbudgetlog.qty < 0 THEN 'Reducción' ELSE 'Ampliación' END as tipoOpe,
	chartdetailsbudgetlog.txt_justificacion,
    chartdetailsbudgetlog.nu_afectacion,
    chartdetailsbudgetlog.nu_tipo_reg,
    chartdetailsbudgetlog.nu_cat_jusr,
    chartdetailsbudgetlog.tagref
	FROM chartdetailsbudgetlog
	JOIN periods ON periods.periodno = chartdetailsbudgetlog.period
	WHERE
	chartdetailsbudgetlog.type = '".$_GET['type']."'
	AND chartdetailsbudgetlog.transno = '".$_GET['transno']."'
    AND chartdetailsbudgetlog.nu_afectacion IS NOT NULL
	ORDER BY tipoOpe DESC, idmov ASC";
    $ErrMsg = "No se obtuvo información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $tituloAnt = '';
    $claveAnt = '';
    $totalClaveAnt = 0;
    $numRenglonTotalClave = 0;
    $numRenglon = 12;
    $numRegistro = 1;
    $totalTipo = 0;
    $justificacion = '';
    $tagref = '';
    while ($myrow = DB_fetch_array($TransResult)) {
        $justificacion = $myrow['txt_justificacion'];
        $tagref = $myrow['tagref'];
        // Informacion del cuerpo del mensaje
        if (($tituloAnt == '') || ($tituloAnt != '' && $tituloAnt != $myrow['tipoOpe'])) {
            // Si es el primer tipo o es un tipo diferente al primero
            if ($tituloAnt != '') {
                // Total tipo anterior
                $objSheet->getCell('B'.$numRenglon)->setValue('Total '.$tituloAnt);
                $objPHPExcel->getActiveSheet()->getStyle('B'.$numRenglon)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('B'.$numRenglon)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $objSheet->getCell('F'.$numRenglon)->setValue(''.number_format(abs($totalTipo), $_SESSION['DecimalPlaces'], '.', ','));
                $objPHPExcel->getActiveSheet()->getStyle('F'.$numRenglon)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle('F'.$numRenglon)->getNumberFormat()->setFormatCode("#,##0.00");
                
                $totalTipo = 0;
                $numRenglon ++;
                $numRenglon ++;
            }
            // Poner titulo de la operación
            $objSheet->getCell('B'.$numRenglon)->setValue(''.$myrow['tipoOpe']);
            $objPHPExcel->getActiveSheet()->getStyle('B'.$numRenglon)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $numRenglon ++;
            $claveAnt = '';
        }

        if (($claveAnt == '') || ($claveAnt != '' && $claveAnt != $myrow['cvefrom'])) {
            // Si es la primera clave o es un tipo diferente al primero
            if ($totalClaveAnt > 0) {
                // Total por clave
                $objSheet->getCell('F'.$numRenglonTotalClave)->setValue(''.number_format(abs($totalClaveAnt), $_SESSION['DecimalPlaces'], '.', ','));
                $objPHPExcel->getActiveSheet()->getStyle('F'.$numRenglonTotalClave)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle('F'.$numRenglonTotalClave)->getNumberFormat()->setFormatCode("#,##0.00");
                $totalClaveAnt = 0;
            }
            
            if ($claveAnt == '') {
                $claveAnt = $myrow['cvefrom'];
            }

            $numRegistroVisual = $numRegistro;
            if (strlen($numRegistroVisual) == 1) {
                $numRegistroVisual = '0'.$numRegistroVisual;
            }

            $objSheet->getCell('A'.$numRenglon)->setValue(''.$numRegistroVisual);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$numRenglon)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objSheet->getCell('B'.$numRenglon)->setValue(''.$claveAnt);
            $numRenglonTotalClave = $numRenglon;
            $numRenglon ++;
            $numRegistro ++;
        }

        $tituloAnt = $myrow['tipoOpe'];
        $claveAnt = $myrow['cvefrom'];
        $totalTipo = $totalTipo + abs($myrow['qty']);
        $totalClaveAnt = $totalClaveAnt + abs($myrow['qty']);

        $objSheet->getCell('C'.$numRenglon)->setValue(''.$myrow['nu_afectacion']);
        $objPHPExcel->getActiveSheet()->getStyle('C'.$numRenglon)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objSheet->getCell('D'.$numRenglon)->setValue(''.$myrow['nu_tipo_reg']);
        $objPHPExcel->getActiveSheet()->getStyle('D'.$numRenglon)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objSheet->getCell('E'.$numRenglon)->setValue(''.$myrow['nu_cat_jusr']);
        $objPHPExcel->getActiveSheet()->getStyle('E'.$numRenglon)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objSheet->getCell('H'.$numRenglon)->setValue(''.$myrow['mesOpe']);
        $objPHPExcel->getActiveSheet()->getStyle('H'.$numRenglon)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objSheet->getCell('K'.$numRenglon)->setValue(''.number_format(abs($myrow['qty']), $_SESSION['DecimalPlaces'], '.', ','));
        $objPHPExcel->getActiveSheet()->getStyle('K'.$numRenglon)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle('K'.$numRenglon)->getNumberFormat()->setFormatCode("#,##0.00");

        $numRenglon ++;
    }

    // Total ultimo tipo de operación
    $objSheet->getCell('B'.$numRenglon)->setValue('Total '.$tituloAnt);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$numRenglon)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$numRenglon)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $objSheet->getCell('F'.$numRenglon)->setValue(''.number_format(abs($totalTipo), $_SESSION['DecimalPlaces'], '.', ','));
    $objPHPExcel->getActiveSheet()->getStyle('F'.$numRenglon)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    $objPHPExcel->getActiveSheet()->getStyle('F'.$numRenglon)->getNumberFormat()->setFormatCode("#,##0.00");

    // Total por clave ultimo registro
    $objSheet->getCell('F'.$numRenglonTotalClave)->setValue(''.number_format(abs($totalClaveAnt), $_SESSION['DecimalPlaces'], '.', ','));
    $objPHPExcel->getActiveSheet()->getStyle('F'.$numRenglonTotalClave)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    $objPHPExcel->getActiveSheet()->getStyle('F'.$numRenglonTotalClave)->getNumberFormat()->setFormatCode("#,##0.00");

    // Formato por fila, solo bordes en izquierda y derecha
    $style = array(
        'borders' => array(
            'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
            'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
        ),
    );
    $dataJsonMeses = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U');
    for ($i=0; $i < count($dataJsonMeses); $i++) {
        $objPHPExcel->getActiveSheet()->getStyle($dataJsonMeses[$i].'11:'.$dataJsonMeses[$i].$numRenglon)->applyFromArray($style);
    }
    // FIN Información
    
    // INICIO Pie
    
    //logo  sagarpa
    $sqllogo = "SELECT legalbusinessunit.logo
    FROM tags
    INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
    WHERE tagref='" . $tagref . "'";
    $resutlogo = DB_query($sqllogo, $db);
    $rowlogo = DB_fetch_array($resutlogo);
    $logo = $rowlogo ['logo'];
    if (!empty($logo)) {
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('Logo');
        $objDrawing->setDescription('Logo');
        $objDrawing->setPath(''.$logo);
        $objDrawing->setCoordinates('A1');
        $objDrawing->setResizeProportional(false);
        $objDrawing->setWidth(60);
        $objDrawing->setHeight(40);
        $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
    }
    //fin logo sagarpa
    
    $numRenglon ++;

    if (empty($justificacion)) {
        // Esta vacio
        $justificacion = '';
    }

    $inicialNum = $numRenglon;
    $objSheet->getCell('A'.$numRenglon)->setValue('Justificación: '.$justificacion);
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$numRenglon.':F'.($numRenglon + 3));
    $objPHPExcel->getActiveSheet()->getStyle('A'.$numRenglon)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY);
    $objPHPExcel->getActiveSheet()->getStyle('A'.$numRenglon)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

    $objPHPExcel->getActiveSheet()->getStyle('A'.$numRenglon.':F'.($numRenglon + 3))->applyFromArray($styleArray);

    $objSheet->getCell('G'.$numRenglon)->setValue('Solicita: El Director General de Programación, Presupuesto y Finanzas');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('G'.$numRenglon.':M'.($numRenglon + 2));
    $objPHPExcel->getActiveSheet()->getStyle('G'.$numRenglon)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('G'.$numRenglon)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

    $style = array(
        'borders' => array(
            'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
            'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
            'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
        ),
    );
    $objPHPExcel->getActiveSheet()->getStyle('G'.$numRenglon.':M'.($numRenglon + 2))->applyFromArray($style);

    $objSheet->getCell('G'.($numRenglon + 3))->setValue('Lic. VICENTE DEL ARENAL VIDAL');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('G'.($numRenglon + 3).':M'.($numRenglon + 3));
    $objPHPExcel->getActiveSheet()->getStyle('G'.($numRenglon + 3))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $style = array(
        'borders' => array(
            'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
            'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
            'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
        ),
    );
    $objPHPExcel->getActiveSheet()->getStyle('G'.($numRenglon + 3).':M'.($numRenglon + 3))->applyFromArray($style);

    $objSheet->getCell('N'.$numRenglon)->setValue('Autoriza: Dirección General de Programación y Presupuesto B de la SHCP');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('N'.$numRenglon.':U'.($numRenglon + 3));
    $objPHPExcel->getActiveSheet()->getStyle('N'.$numRenglon)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('N'.$numRenglon)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

    $objPHPExcel->getActiveSheet()->getStyle('N'.$numRenglon.':U'.($numRenglon + 3))->applyFromArray($styleArray);
    // FIN Pie

    // Set page orientation and size
    $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

    // Rename first worksheet
    $objPHPExcel->getActiveSheet()->setTitle('Hoja1');

    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $objPHPExcel->setActiveSheetIndex(0);

    $Nombre_Archivo = 'Ade.xls';
    $Nombre_Archivo = "Adecuación_".$_GET['transno']."_".date('dmY').".xls";

    //Codigo para descargalo
    // Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename=".$Nombre_Archivo."");
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');

    // If you're serving to IE over SSL, then the following may be needed
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header('Pragma: public'); // HTTP/1.0

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
} else {
    // Si es PDF
    $jreport= "";
    $JasperReport = new JasperReport($confJasper);
    $jreport = $JasperReport->compilerReport("adecuacion_presupuestal");

    $sqllogo = "SELECT legalbusinessunit.logo
	FROM chartdetailsbudgetlog
	INNER JOIN tags ON tags.tagref = chartdetailsbudgetlog.tagref
	INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
	WHERE chartdetailsbudgetlog.type = '".$_GET["type"]."' AND chartdetailsbudgetlog.transno = '".$_GET["transno"]."'";

    $resutlogo = DB_query($sqllogo, $db);
    $rowlogo = DB_fetch_array($resutlogo);
    $logo = $rowlogo ['logo'];

    $JasperReport->addParameter("transno", $_GET["transno"]);
    $JasperReport->addParameter("type", $_GET["type"]);
    // $JasperReport->addParameter("imagen", $JasperReport->getPathFile()."/images/logo_sagarpa_01.jpg");
    // $ruta = $JasperReport->getPathFile()."images/logo_sagarpa_01.jpg";
    $ruta = $JasperReport->getPathFile()."".$rowlogo ['logo'];
    $ruta = str_replace('jasper/', '', $ruta);
    $ruta = str_replace('jasperconfig/', '', $ruta);
    $JasperReport->addParameter("imagen", $ruta);
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/");
    //echo $JasperReport->getPathFile();
    //exit;
    $conexion = $JasperReport->getConexionDB($_SESSION ["DatabaseName"], $dbuser, $dbpassword);
    $jPrint = $JasperReport->fillReport($jreport, $JasperReport->getParameters(), $conexion);
    $pdfBytes = $JasperReport->exportReportPDF($jPrint);

    header('Content-type: application/pdf');
    header('Content-Length: ' . strlen($pdfBytes));
    header('Content-Disposition: inline; filename=report.pdf');

    echo $pdfBytes;
}
