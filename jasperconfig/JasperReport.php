<?php
include_once "http://localhost:8181/JavaBridge/java/Java.inc";
include_once realpath(dirname(__FILE__)) . '/JasperReportInterface.php';
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

class JasperReport implements JasperReportInterface
{
    private $pathFile;
    private $parameters;
    private $oututStream;
    private $host;
    private $dbuser;
    private $dbpassword;


    public function __construct($conf)
    {
        $this->pathFile= realpath(dirname(__FILE__)).'/';
        $this->parameters = new Java("java.util.HashMap");
        $this->oututStream = new Java("java.io.ByteArrayOutputStream");
        $this->host = $conf['host'];
        $this->dbuser = $conf['dbuser'];
        $this->dbpassword = $conf['dbpassword'];
    }
    public function compilerReport($fileName)
    {
        $sJcm = new JavaClass("net.sf.jasperreports.engine.JasperCompileManager");
        $jasperReport = $sJcm->compileReport($this->pathFile . $fileName . ".jrxml");
        return $jasperReport;
    }
    public function fillReport($report, $params, $datasource)
    {
        $sJfm = new Java("net.sf.jasperreports.engine.JasperFillManager");
        $jasperPrint = $sJfm->fillReport($report, $params, $datasource);
        return $jasperPrint;
    }
    public function fill($report, $params)
    {
        $sJfm = new Java("net.sf.jasperreports.engine.JasperFillManager");
        $jasperPrint = $sJfm->fillReport($report, $params);
        return $jasperPrint;
    }
    public function getInputStream($input)
    {
        $inputStream = new Java("java.io.ByteArrayInputStream", $input);
        return $inputStream;
    }
    public function getConexionDB($db, $user, $pass)
    {
        java('java.lang.Class')->forName('com.mysql.jdbc.Driver');
        // echo "jdbc:mysql://localhost/" . $db;
        // @NOTE: Se realiza el cambio del host para las consultas a las bases de datos de los reportes
        // @DATE: 26.04.18
        // @author: Desarrollo
        // $connection = java('java.sql.DriverManager')->getConnection("jdbc:mysql://23.111.130.190/" . $db, 'desarrollo', 'p0rtAli70s');
        // var_dump($this->host."/".$db."/".$this->dbuser."/".$this->dbpassword);
        $connection = java('java.sql.DriverManager')->getConnection("jdbc:mysql://".$this->host."/" . $db, $this->dbuser, $this->dbpassword);
        // $connection = java('java.sql.DriverManager')->getConnection("jdbc:mysql://23.111.130.190/".$db, 'desarrollo', 'p0rtAli70s');
        // var_dump($connection);
        return $connection;
    }
    public function addParameter($nameParam, $valueParam)
    {
        $this->parameters->put($nameParam, $valueParam);
    }
    public function getPathFile()
    {
        return $this->pathFile;
    }
    public function exportReportPDF($jasperPrint)
    {
        try {
            $exportManager = new Java("net.sf.jasperreports.engine.JasperExportManager");
            $exportManager->exportReportToPdfStream($jasperPrint, $this->oututStream);
            return $this->oututStream->toByteArray();
        } catch (JavaException $ex) {
            $trace = new Java("java.io.ByteArrayOutputStream");
            $ex->printStackTrace(new Java("java.io.PrintStream", $trace));
            print "java stack trace: $trace\n";
        }
    }
    public function exportReportXLS($jasperPrint)
    {
        try {
            $exportManager = new Java("net.sf.jasperreports.engine.export.JRXlsExporter");
            $exportManager->setParameter(java("net.sf.jasperreports.engine.export.JRXlsExporterParameter")->JASPER_PRINT, $jasperPrint);
            $exportManager->setParameter(java("net.sf.jasperreports.engine.export.JRXlsExporterParameter")->OUTPUT_STREAM, $this->oututStream);
            $exportManager->setParameter(java("net.sf.jasperreports.engine.export.JRXlsExporterParameter")->IS_ONE_PAGE_PER_SHEET, java("java.lang.Boolean")->TRUE);
            $exportManager->setParameter(java("net.sf.jasperreports.engine.export.JRXlsExporterParameter")->IS_COLLAPSE_ROW_SPAN, java("java.lang.Boolean")->TRUE);
            $exportManager->exportReport();

            return $this->oututStream->toByteArray();
        } catch (JavaException $ex) {
            $trace = new Java("java.io.ByteArrayOutputStream");
            $ex->printStackTrace(new Java("java.io.PrintStream", $trace));
            print "java stack trace: $trace\n";
        }
    }
    public function exportReportHTML($jasperPrint, $name)
    {
        try {
            $outputPath = realpath(".") . "/" . $name . ".html";
            // echo "Path:".$outputPath;
            $hasMap = new Java("java.util.HashMap");
            $exportManager = new Java("net.sf.jasperreports.engine.export.JRHtmlExporter");
            $exportManager->setParameter(java("net.sf.jasperreports.engine.export.JRHtmlExporterParameter")->JASPER_PRINT, $jasperPrint);
            $exportManager->setParameter(java("net.sf.jasperreports.engine.export.JRHtmlExporterParameter")->OUTPUT_FILE, new Java("java.io.File", $outputPath));
            $exportManager->setParameter(java("net.sf.jasperreports.engine.export.JRHtmlExporterParameter")->IS_USING_IMAGES_TO_ALIGN, true);

            // $exportManager->setParameter(java("net.sf.jasperreports.engine.export.JRHtmlExporterParameter")->OUTPUT_STREAM, $this->oututStream);
            // $exportManager->setParameter(java("net.sf.jasperreports.engine.export.JRHtmlExporterParameter")->IMAGES_DIR_NAME, "./images/");
            // $exportManager->setParameter(java("net.sf.jasperreports.engine.export.JRHtmlExporterParameter")->IS_OUTPUT_IMAGES_TO_DIR, new Java("java.lang.Boolean",true));
            // $exportManager->setParameter(java("net.sf.jasperreports.engine.export.JRHtmlExporterParameter")->IMAGES_MAP, $hasMap);
            // $_SESSION['IMAGES_MAP']=$hasMap;
            // $exportManager->setParameter(java("net.sf.jasperreports.engine.export.JRHtmlExporterParameter")->IMAGES_URI,"images?image=");
            $exportManager->exportReport();
            return $outputPath;
        } catch (JavaException $ex) {
            $trace = new Java("java.io.ByteArrayOutputStream");
            $ex->printStackTrace(new Java("java.io.PrintStream", $trace));
            print "java stack trace: $trace\n";
        }
    }
    public function getParameters()
    {
        return $this->parameters;
    }
    public function getDataSource($input)
    {
        try {
            $string = new Java("java.lang.String", $input);
            $inputStream = new Java("java.io.ByteArrayInputStream", $string->getBytes("UTF-8"));
            $JRXmlUtils = Java("net.sf.jasperreports.engine.util.JRXmlUtils");
            $document = $JRXmlUtils->parse($inputStream);
            $rootPath = "/" . $document->getChildNodes()->item(0)->getNodeName();
            $JRXml = new Java("net.sf.jasperreports.engine.data.JRXmlDataSource", $document, $rootPath);
            return $JRXml;
        } catch (JavaException $ex) {
            $trace = new Java("java.io.ByteArrayOutputStream");
            $ex->printStackTrace(new Java("java.io.PrintStream", $trace));
            print "java stack trace: $trace\n";
        }
    }
    public function getDataSourceSQL($result)
    {
        try {
            $JRDataSource = new Java("net.sf.jasperreports.engine.JRResultSetDataSource", $result);
            return $JRDataSource;
        } catch (JavaException $ex) {
            $trace = new Java("java.io.ByteArrayOutputStream");
            $ex->printStackTrace(new Java("java.io.PrintStream", $trace));
            print "java stack trace: $trace\n";
        }
    }
    public function getDataSourceXML($input, $xpath){
        try{
            $string= new Java("java.lang.String",$input);
            $inputStream= new Java("java.io.ByteArrayInputStream",$string->getBytes("UTF-8"));
            $JRXmlUtils= Java("net.sf.jasperreports.engine.util.JRXmlUtils");
            $document= $JRXmlUtils->parse($inputStream);
            //$rootPath= "/".$document->getChildNodes()->item(0)->getNodeName();
            $rootPath= $xpath;
            $JRXml= new Java("net.sf.jasperreports.engine.data.JRXmlDataSource", $document, $rootPath);
            return $JRXml;
        }catch (JavaException $ex){
            $trace= new Java("java.io.ByteArrayOutputStream");
            $ex->printStackTrace(new Java("java.io.PrintStream", $trace));
            print "java stack trace: $trace\n";
        }
    }
}
