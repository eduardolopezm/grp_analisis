<?php

/**
 * @author carlos
 *
 */
interface JasperReportInterface{

	/**
	 * @param string $pathFileTemplate
	 */
	 function compilerReport($fileName);

	/**
	 * @param jasperReport $report
	 * @param Map $params
	 * @param Datasource/Connection $datasource
	*/
	 function fillReport( $report, $params, $datasource);
	/**
	 * @param string $db
	 * @param string $user
	 * @param string $pass
	*/
	 function getConexionDB($db,$user,$pass);
	/**
	 * @param string $input
	*/
	 function getInputStream($input);
	/**
	 * @param jasperPrint $jasperPrint
	*/
	 function exportReportPDF($jasperPrint);
	/**
	 * @param jasperPrint $jasperPrint
	*/
	 function exportReportXLS($jasperPrint);

	 function exportReportHTML($jasperPrint,$name);

	 function getDataSource($input);


	/**
	 * @param String
	 * @param Objec
	 */
	 function addParameter($nameParam,$valueParam);
}

?>