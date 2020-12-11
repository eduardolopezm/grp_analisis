<?php
ini_set('memory_limit', '1028M');
set_time_limit ( 60 * 4 ); //dos minutos de tiempo
// $fp = fopen('data.txt', 'w');
// fwrite($fp, '$_POST');
// fwrite($fp, var_export($_POST, true));
// fwrite($fp, '$_GET');
// fwrite($fp, var_export($_GET, true));
// fwrite($fp, '$_REQUEST');
// fwrite($fp, var_export($_REQUEST, true));
// fwrite($fp, '$_SERVER');
// fwrite($fp, var_export($_SERVER, true));
// fwrite($fp, '$_SESSION');
// fwrite($fp, var_export($_SESSION, true));
// fwrite($fp, 'STRIP_TAGS');
// fwrite($fp, var_export(strip_tags( $_POST['content']),true));
// fclose($fp);

$fp = fopen('data.tsv', 'w');
fwrite($fp, $_POST['content']);
fclose($fp);

header('Content-type: application/vnd.ms-excel');
//header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: inline; filename=\"" . $_POST['filename'] . ".xls\"");

require_once '../lib/PHPExcel-1.8/Classes/PHPExcel.php';
require_once '../lib/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';

$_POST['content'] = str_replace("Currency", "[$$-80A]#,##0.00;[RED]-[$$-80A]#,##0.00", $_POST['content']);
//$_POST['content'] = str_replace("$", "", $_POST['content']);

$fn = tempnam ('/tmp', 'conta-xml_xlsdata-');

if ( $_POST['format']=='csv' || $_POST['format']=='html' || $_POST['format']=='xls' ) {
	$f = fopen ($fn, 'w+');
	fwrite($f, $_POST['content']);
	fclose($f);
}

$objPHPExcel = new PHPExcel();
$objPHPExcel->getCalculationEngine()->enableCalculationCache();

switch ( $_POST['format'] ) 
{
	case 'csv':
		$objReader = PHPExcel_IOFactory::createReader('CSV');
		$objPHPExcel = $objReader->load($fn);
		break;
		//no funciona
	case 'html':
		$objReader = PHPExcel_IOFactory::createReader('HTML');
		$objPHPExcel = $objReader->load($fn);
		break;
	case 'xls':
		$objReader = PHPExcel_IOFactory::createReader('Excel2003XML');
		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load($fn);
			// $objPHPExcel = PHPExcel_IOFactory::load($fn);
		//$objPHPExcel->removeSheetByIndex(1);
		break;
	case 'tsv':
		$objSheet = $objPHPExcel->getActiveSheet();
		$NoRen=1;
		$datos = explode("\n", $_POST['content']);
/*		
		foreach ($datos as $fila)
		{
			$col = 'A';
			
			$fila = str_replace( array("\r","\n"), array("",""), $fila );
			
			//$row = explode("\t", $fila);
			$row = str_getcsv( $fila, "\t", '"' );		//esta marca error $row = str_getcsv( $fila, "\t", '"', "\\" ); al parecer mete caracteres de mas y son interpretados como otra cosa
			
			foreach($row as  $valor)
			{
				if ( $valor != '' )
				{
					if ( preg_match("/\\d+\\/\\d+\\/\\d+/", $valor) )
					{
						$objSheet->setCellValue( $col.$NoRen, $valor);
						
// 							//$objSheet->setCellValue( $col.$NoRen, PHPExcel_Shared_Date::PHPToExcel( new DateTime() ) );
// 						list($day, $month, $year) = explode('/', $valor);
// 						$year	=	(int)$year; 
// 						$month	=	(int)$month;
// 						$day	=	(int)$day;
						
// 						if ( $year<35 )
// 						{
// 							list($year, $month, $day) = explode('/', $valor);
// 							$year	=	(int)$year;
// 							$month	=	(int)$month;
// 							$day	=	(int)$day;
// 						}
						
// 						$objSheet->setCellValue( $col.$NoRen, PHPExcel_Shared_Date::FormattedPHPToExcel( $year, $month, $day ) );
// 						$objSheet->getStyle($col.$NoRen)->getNumberFormat()->setFormatCode( '[$-C09]dd/mm/yyyy;@' );
// 						//$objSheet->getStyle($col.$NoRen)->getNumberFormat()->setFormatCode( '[$-C09]d mmm yyyy;@' );
					}
					else
					if( preg_match("/\\$\\d+/", $valor) )
					{
						$valor = str_replace( array("$",","), array("",""), $valor );
						$objSheet->setCellValue( $col.$NoRen, $valor );
						$objSheet->getStyle($col.$NoRen)->getNumberFormat()->setFormatCode("$#,##0.00_);[Red]($#,##0.00)");
					}
					else
					{
						$valor_temp = str_replace( array(","), array(""), $valor );
						if ( is_numeric($valor_temp) )
						{
							$objRichText = new PHPExcel_RichText();
							$objRichText->createText( (string)$valor_temp );
							$objSheet->setCellValue( $col.$NoRen, $objRichText );
							unset($objRichText);
						}
						else
							$objSheet->setCellValue( $col.$NoRen, $valor);
					}
				}
				$col++;
				unset($valor);
			}
*/			
		$_n_renglones = count($datos);
		for($j=0; $j<$_n_renglones;  $j++)
		{
			$col = 'A';
				
			$fila = str_replace( array("\r","\n"), array("",""), $datos[$j] );
			$row = str_getcsv( $fila, "\t", '"' );		//esta marca error $row = str_getcsv( $fila, "\t", '"', "\\" ); al parecer mete caracteres de mas y son interpretados como otra cosa

			$_n_columnas = count($row);
			for($k=0; $k<$_n_columnas; $k++)
			{
				$valor = $row[$k];
				if ( $valor != '' )
				{
					if( preg_match("/\\$[\\-]*\\d+/", $valor) )
					{
						$valor = str_replace( array("$",","), array("",""), $valor );
						$objSheet->setCellValue( $col.$NoRen, $valor );
						$objSheet->getStyle($col.$NoRen)->getNumberFormat()->setFormatCode("$#,##0.00_);[Red]($#,##0.00)");
					}
					else
					{
						$valor_temp = str_replace( array(","), array(""), $valor );
						if ( preg_match("/^(\\d+|\\d+\\.\\d*)/", $valor_temp) )
							$objSheet->setCellValue( $col.$NoRen, $valor_temp );
						else
							$objSheet->setCellValue( $col.$NoRen, $valor);
					}
				}
				$col++;
				unset($valor);
			}
			unset($row);
			$NoRen++;
			unset($fila);
		}
		break;		
	default:
		break;
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5' /*'Excel2007'*/);
$objWriter->setPreCalculateFormulas(true);
$objWriter->save('php://output');

@unlink ($fn);
