<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once 'Excel/reader.php';

$data = new Spreadsheet_Excel_Reader();
$data->setUTFEncoder('iconv');
$data->setOutputEncoding('UTF-8');
$data->read('refacciones.xls');
/*
$col_code = 1;
$col_row_start = 20;
foreach($data->sheets as $sheet) {
	for($col_row = 1; $col_row <= $sheet['numRows']; $col_row++) {
		if($col_row >= $col_row_start) {
			if(empty($sheet['cells'][$col_row][$col_code]) == FALSE) {
				echo $sheet['cells'][$col_row][$col_code];
				echo "<br/>";
			} else {
				break;
			}
		}
	}
	break;
}
*/
?>
