<?php
/**
 * Visualizar Reportes
 *
 * @category Panel
 * @package ap_grp
 * @author Desarrollo <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 0
 * Visualizar reportes conac y ldf
 */

 
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
include('config.php');
include('includes/session.inc');
$PrintPDF = $_GET ['PrintPDF'];
$_POST ['PrintPDF'] = $PrintPDF;
include('jasper/JasperReport.php');
include('includes/SQL_CommonFunctions.inc');


include $PathPrefix . "includes/SecurityUrl.php";
$enc = new Encryption;

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

// if($_SESSION['UserID'] == 'desarrollo'){
//     ini_set('display_errors', 1);
//     ini_set('log_errors', 1);
// }

ini_set('memory_limit', '3068M');
set_time_limit(600);

$jreport= "";

if(empty($_GET["nombreArchivo"])){
    $_GET["nombreArchivo"] = "report";
}
if($_GET["entepublico"] == 'Seleccionar'){
    $_GET["entepublico"] = '';
}
if($_GET["entepublico"] == 'Seleccionar...'){
    $_GET["entepublico"] = '';
}

$_GET['nombreArchivo'] = str_replace(' ', '_', $_GET['nombreArchivo']);

if (empty($_GET["fechainicial"])) {
    $_GET["fechainicial"]= "01-01-".$_GET["anio"];
}

if (empty($_GET["fechafinal"])) {
    $_GET["fechafinal"]= "31-12-".$_GET["anio"];
}

$anioReporte = date('Y');
if(isset($_GET["fechainicial"])){
   $arrAnio = explode('-', $_GET["fechainicial"]);
   $anioReporte =$arrAnio['2'];
}


/*echo $_GET["tagref"];
echo $_GET["ue"];
echo $_GET["entepublico"];
exit();*/
//echo "tagref despues";


$XLS = (  empty($_GET["tipoDescarga"]) ? "" : ( strtolower($_GET["tipoDescarga"])=="x" ? "_xls" : "" )  );

$path = realpath(dirname(__FILE__)) . '/';

$JasperReport = new JasperReport($confJasper);

//Obtener descripcion de la ue
if(isset($_GET['tagref'])&&isset($_GET['ue'])){
    $arrUE = explode(",", $_GET["ue"]);

    if(count($arrUE)>1){
        $gerenciaDescripcion = "Consolidado de gerencias ($_GET[ue])";

        if($_GET['totalue']==count($arrUE)){
            $gerenciaDescripcion = "Consolidado";
        }
    }else{
        $SQL = "SELECT concat(`ue`,' ',`desc_ue`) as desc_ue  FROM `tb_cat_unidades_ejecutoras` WHERE `ur` = '$_GET[tagref]' AND ue = '$_GET[ue]'";
        $result = DB_query($SQL,$db);
        if($result){
            $myrow = DB_fetch_array($result);
            $gerenciaDescripcion = $myrow['desc_ue'];
        }
    }
}
if($_GET['tagref'] != ''){
    $SQLIMG = " SELECT legalbusinessunit.logo
    FROM legalbusinessunit
    INNER JOIN tags on legalbusinessunit.legalid = tags.legalid
    WHERE tags.tagref = '".$_GET['tagref']. "'";
    $rs = DB_query($SQLIMG,$db);
    $myrows = DB_fetch_array($rs);
    $logo_legal = $myrows['logo'];
}else{
    $logo_legal = $_SESSION['LogoFile']; //'images/logo_sagarpa_01.jpg';
}
//Obtener imagen 


$gerenciaDescripcion = ( $gerenciaDescripcion ? $gerenciaDescripcion : " " );

if ($_GET["reporte"] == "resguardo") {
    $rutaReporte = "../jasper/activofijo/rpt_resguardo";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
      $jreport = $JasperReport->compilerReport($rutaReporte);
      //$JasperReport->addParameter("fechainicial", $newDateStringInicial);
      //$JasperReport->addParameter("fechafinal", $newDateStringFinal);
      //
      $ruta= $JasperReport->getPathFile()."/".$logo_legal;
        //echo $ruta;
        $pathImagen=str_replace('/jasper/', '', $ruta);
      //$pathImagen = "../".$logo_legal;

//      $pathImagen=str_replace('/jasper/','', $pathImagen);
      //echo $pathImagen;
      $JasperReport->addParameter("imagen", $pathImagen);
      $JasperReport->addParameter("folioresguardo", $_GET["Folio"]);
}

if ($_GET["reporte"] == "desincorporacion") {
    $rutaReporte = "../jasper/activofijo/rpt_desincorporacion";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
      $jreport = $JasperReport->compilerReport($rutaReporte);
      //$JasperReport->addParameter("fechainicial", $newDateStringInicial);
      //$JasperReport->addParameter("fechafinal", $newDateStringFinal);
      //
      $ruta= $JasperReport->getPathFile()."/".$logo_legal;
        //echo $ruta;
        $pathImagen=str_replace('/jasper/', '', $ruta);
      //$pathImagen = "../".$logo_legal;

//      $pathImagen=str_replace('/jasper/','', $pathImagen);
      //echo $pathImagen;
      $JasperReport->addParameter("imagen", $pathImagen);
      $JasperReport->addParameter("folioresguardo", $_GET["Folio"]);
}

if ($_GET["reporte"] == "libro_diario") {
    $rutaReporte = "../jasper/conac/rpt_libro_diario";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
      $jreport = $JasperReport->compilerReport($rutaReporte);
      $myDateTime = DateTime::createFromFormat("d-m-Y", $_GET["fechainicial"]);
      $newDateStringInicial = $myDateTime->format('Y-m-d');
      $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
      $newDateStringFinal = $myDateTime->format('Y-m-d');
      $JasperReport->addParameter("fechainicial", $newDateStringInicial);
      $JasperReport->addParameter("fechafinal", $newDateStringFinal);
      $JasperReport->addParameter("mes_string", fnGetMes($myDateTime->format('m')));
      $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
      $JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/estadoTamp.jpeg"))));
      $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");

    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $fechaInicialNew = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $fechaFinalNew = $myDateTime->format('Y-m-d');
    
    $JasperReport->addParameter("fechainicialnew", $fechaInicialNew." 00:00:00");
    $JasperReport->addParameter("fechafinalnew", $fechaFinalNew. " 23:59:59");
    $JasperReport->addParameter("usuario", $_SESSION['UserID']);
}

if ($_GET["reporte"] == "rptDepreciacionActivoFijo") {
    $rutaReporte = "../jasper/conac/rptDepreciacionActivoFijo";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $myDateTime = DateTime::createFromFormat("d-m-Y", $_GET["fechainicial"]);
    $newDateStringInicial = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $newDateStringFinal = $myDateTime->format('Y-m-d');
    $JasperReport->addParameter("fechainicial", $newDateStringInicial);
    $JasperReport->addParameter("fechafinal", $newDateStringFinal);
    $JasperReport->addParameter("mes_string", fnGetMes($myDateTime->format('m')));
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");
    $JasperReport->addParameter("usuario", $_SESSION['UserID']);
}



if ($_GET["reporte"] == "libro_mayor") {

    $jreport = $JasperReport->compilerReport("../jasper/conac/rpt_libro_mayor");
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $newDateStringInicial = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $newDateStringFinal = $myDateTime->format('Y-m-d');
    $JasperReport->addParameter("fechainicial", $newDateStringInicial);
    $JasperReport->addParameter("fechafinal", $newDateStringFinal);
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
    $JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/estadoTamp.jpeg"))));
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");

    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $fechaInicialNew = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $fechaFinalNew = $myDateTime->format('Y-m-d');
    
    $JasperReport->addParameter("fechainicialnew", $fechaInicialNew." 00:00:00");
    $JasperReport->addParameter("fechafinalnew", $fechaFinalNew. " 23:59:59");
    $JasperReport->addParameter("usuario", $_SESSION['UserID']);
}
   

if ($_GET["reporte"] == "libro_inventario_c1") {
    $rutaReporte = "../jasper/conac/rpt_inventario_c1";
    $rutaReporte = ( !$XLS ? $rutaReporte : (file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );

    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");

    $jreport = $JasperReport->compilerReport($rutaReporte);
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $newDateStringInicial = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $newDateStringFinal = $myDateTime->format('Y-m-d');
    // $JasperReport->addParameter("fechainicial", "21");
    // $JasperReport->addParameter("fechafinal", "20");
    $JasperReport->addParameter("ur", $_GET["tagref"]);
    $JasperReport->addParameter("ue", $_GET["ue"] );
   
    // $JasperReport->addParameter("concepto", "concepto");
      $JasperReport->addParameter("anioreporte", $anioReporte);
    //   $JasperReport->addParameter("entepublico", "ente");
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));

    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $fechaInicialNew = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $fechaFinalNew = $myDateTime->format('Y-m-d');

    $JasperReport->addParameter("fechainicialnew", $fechaInicialNew." 00:00:00");
    $JasperReport->addParameter("fechafinalnew", $fechaFinalNew. " 23:59:59");
    $JasperReport->addParameter("usuario", $_SESSION['UserID']);
}


if ($_GET["reporte"] == "situacionfinanciera") {
    $rutaReporte = "../jasper/conac/rptsituacion_financiera";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
    $JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/estadoTamp.jpeg"))));
    
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");
    
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $fechaInicialNew = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $fechaFinalNew = $myDateTime->format('Y-m-d');
    
    $JasperReport->addParameter("fechainicialnew", $fechaInicialNew." 00:00:00");
    $JasperReport->addParameter("fechafinalnew", $fechaFinalNew. " 23:59:59");
    $JasperReport->addParameter("usuario", $_SESSION['UserID']);
}

if ($_GET["reporte"] == "estadodeactividades") {
    $rutaReporte = "../jasper/conac/rpt_estado_de_actividades";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
    $JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/estadoTamp.jpeg"))));

    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $fechaInicialNew = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $fechaFinalNew = $myDateTime->format('Y-m-d');
        
    $JasperReport->addParameter("fechainicialnew", $fechaInicialNew." 00:00:00");
    $JasperReport->addParameter("fechafinalnew", $fechaFinalNew. " 23:59:59");
    $JasperReport->addParameter("usuario", $_SESSION['UserID']);
}


if ($_GET["reporte"] == "estadohaciendapublica") {
    /* Se agrego esta funcionalidad, ya que la estructura del reporte lo necesitan */
    include('rptEsatdoVariacionHaciendaPublicaXML.php');
    exit();
}

if ($_GET["reporte"] == "estadoanaliticodelactivo") {
    $rutaReporte = "../jasper/conac/rpt_estadoanaliticodelactivo";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
    $JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/estadoTamp.jpeg"))));
    $JasperReport->addParameter("EntePublico", "".$_GET["entepublico"]);

   // setlocale(LC_ALL,'es_MX');

    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $newDateStringInicial = $myDateTime->format('d-m-Y');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $newDateStringFinal = $myDateTime->format('d-m-Y');

    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $fechaInicialNew = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $fechaFinalNew = $myDateTime->format('Y-m-d');

     ///////////////////////////////
    $JasperReport->addParameter("fechainicialnew", $fechaInicialNew." 00:00:00");
    $JasperReport->addParameter("fechafinalnew", $fechaFinalNew. " 23:59:59");
    $JasperReport->addParameter("usuario", $_SESSION['UserID']);
    $JasperReport->addParameter("ur", $_GET["tagref"]);
    $JasperReport->addParameter("ue", $_GET["ue"]);

   // $JasperReport->addParameter("fechainicial", $newDateStringInicial);
   // $JasperReport->addParameter("fechafinal", $newDateStringFinal);
    $JasperReport->addParameter("parRangoDeFechas", "Desde ".$newDateStringInicial." hasta ".$newDateStringFinal);
}

if ($_GET["reporte"] == "rpt_estado_analitico_ingresos") {
    $rutaReporte = "../jasper/conac/rpt_estadoanaliticodelingreso";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
    $JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/estadoTamp.jpeg"))));
    $JasperReport->addParameter("EntePublico", "".$_GET["entepublico"]);

   // setlocale(LC_ALL,'es_MX');

    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $newDateStringInicial = $myDateTime->format('d-m-Y');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $newDateStringFinal = $myDateTime->format('d-m-Y');

    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $fechaInicialNew = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $fechaFinalNew = $myDateTime->format('Y-m-d');

     ///////////////////////////////
    $JasperReport->addParameter("fechainicialnew", $fechaInicialNew." 00:00:00");
    $JasperReport->addParameter("fechafinalnew", $fechaFinalNew. " 23:59:59");
    $JasperReport->addParameter("usuario", $_SESSION['UserID']);
    $JasperReport->addParameter("ur", $_GET["tagref"]);
    $JasperReport->addParameter("ue", $_GET["ue"]);
    // echo "ur->".$_GET["tagref"];
    // echo "ur->".$_GET["ue"];
    // echo "fechaInicialNew->".$fechaInicialNew;
    // echo "fechaFinalNew->".$fechaFinalNew;
   // $JasperReport->addParameter("fechainicial", $newDateStringInicial);
   // $JasperReport->addParameter("fechafinal", $newDateStringFinal);
    $JasperReport->addParameter("parRangoDeFechas", "Desde ".$newDateStringInicial." hasta ".$newDateStringFinal);
}


if ($_GET["reporte"] == "rpt_analitico_deudapasivos") {
    $rutaReporte = "../jasper/conac/rptAnaliticoDeudaPasivo";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
    $JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/estadoTamp.jpeg"))));
    $JasperReport->addParameter("parEntePublico", "".$_GET["entepublico"]);

    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $newDateStringInicial = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $newDateStringFinal = $myDateTime->format('Y-m-d');
    $JasperReport->addParameter("fechainicial", $newDateStringInicial);
    $JasperReport->addParameter("fechafinal", $newDateStringFinal);
    $JasperReport->addParameter("parRangoDeFechas", "Desde ".$newDateStringInicial." hasta ".$newDateStringFinal);
    

    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $fechaInicialNew = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $fechaFinalNew = $myDateTime->format('Y-m-d');
    
    $JasperReport->addParameter("fechainicialnew", $fechaInicialNew." 00:00:00");
    $JasperReport->addParameter("fechafinalnew", $fechaFinalNew. " 23:59:59");
    $JasperReport->addParameter("usuario", $_SESSION['UserID']);

    $rutaReporte = "../jasper/conac/rptanalitico_deudapasivo";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    // $jreport = $JasperReport->compilerReport($rutaReporte);
    // $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    // $newDateStringInicial = $myDateTime->format('Y-m-d');
    // $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    // $newDateStringFinal = $myDateTime->format('Y-m-d');
    // $JasperReport->addParameter("fechainicial", $newDateStringInicial);
    // $JasperReport->addParameter("fechafinal", $newDateStringFinal);
    // $JasperReport->addParameter("concepto", $_GET["concepto"]);
    // $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
}


if ($_GET["reporte"] == "cambiossituacionfinanciera") {
    $rutaReporte = "../jasper/conac/rptcambiosituacion_financiera";
    //$rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $newDateStringInicial = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $newDateStringFinal = $myDateTime->format('Y-m-d');
    $JasperReport->addParameter("fechainicial", $newDateStringInicial);
    $JasperReport->addParameter("fechafinal", $newDateStringFinal);
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
    $JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/estadoTamp.jpeg"))));

    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $fechaInicialNew = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $fechaFinalNew = $myDateTime->format('Y-m-d');
    
    $JasperReport->addParameter("fechainicialnew", $fechaInicialNew. " 00:00:00");
    $JasperReport->addParameter("fechafinalnew", $fechaFinalNew. " 23:59:59");
    $JasperReport->addParameter("usuario", $_SESSION['UserID']);
}

if ($_GET["reporte"] == "rpt_flujoefectivo") {

    $rutaReporte = "../jasper/conac/rpt_flujoefectivo";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
    $JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/estadoTamp.jpeg"))));
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $fechaInicialNew = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $fechaFinalNew = $myDateTime->format('Y-m-d');
    
    $JasperReport->addParameter("fechainicialnew", $fechaInicialNew. " 00:00:00");
    $JasperReport->addParameter("fechafinalnew", $fechaFinalNew. " 23:59:59");
    $JasperReport->addParameter("usuario", $_SESSION['UserID']);

    $sqlUr = "";
    if (isset($_GET['tagref']) && !empty($_GET['tagref'])) {
        $sqlUr = " AND gltrans.tag = '".$_GET["tagref"]."' ";
    }

    $sqlUe = "";
    if (isset($_GET['ue']) && !empty($_GET['ue'])) {
        $sqlUe = " AND CASE WHEN '".$_GET["ue"]."' = '' THEN 1 = 1 ELSE gltrans.ln_ue IN ('".$_GET["ue"]."') END ";
    }
    
    $SQLCuentas="select 
            coalesce(sum(case when dtOtrosInversiones.account ='1.2.6' then Periodo1Cargos else Periodo1Abono end),0) as otrosOrigenesInversion1,
            coalesce(sum(case when dtOtrosInversiones.account ='1.2.6' then Periodo2Cargos else Periodo2Abono end),0) as otrosOrigenesInversion2,
            coalesce(sum(case when dtOtrosInversiones.account ='1.2.6' then 0 else Periodo1Cargos end),0) as otrosAplicacionesInversion1,
            coalesce(sum(case when dtOtrosInversiones.account ='1.2.6' then 0 else Periodo2Cargos end),0) as otrosAplicacionesInversion2
            from (
            SELECT SUBSTRING_INDEX(account, '.', '3') as account,
                    IFNULL(SUM(case when trandate between '".$fechaInicialNew."' and  '".$fechaFinalNew."' then gltrans.amount else 0 end), 0)  as Periodo1,
                    IFNULL(SUM(case when trandate between date_add('".$fechaInicialNew."', INTERVAL -1 year) and date_add('".$fechaFinalNew."', INTERVAL -1 year) then gltrans.amount else 0 end), 0)  as Periodo2,
                    
                    IFNULL(SUM(case when trandate between '".$fechaInicialNew."' and  '".$fechaFinalNew."' and gltrans.amount<0  and gltrans.type !=0 then gltrans.amount else 0 end), 0)  as Periodo1Abono,
                    IFNULL(SUM(case when trandate between date_add('".$fechaInicialNew."', INTERVAL -1 year) and date_add('".$fechaFinalNew."', INTERVAL -1 year) and gltrans.amount<0  and gltrans.type !=0 then gltrans.amount else 0 end), 0)  as Periodo2Abono,
                    
                    IFNULL(SUM(case when trandate between '".$fechaInicialNew."' and  '".$fechaFinalNew."' and gltrans.amount >=0  and gltrans.type !=0  then gltrans.amount else 0 end), 0)  as Periodo1Cargos,
                    IFNULL(SUM(case when trandate between date_add('".$fechaInicialNew."', INTERVAL -1 year) and date_add('".$fechaFinalNew."', INTERVAL -1 year) and gltrans.amount>=0  and gltrans.type !=0 then gltrans.amount else 0 end), 0)  as Periodo2Cargos
            FROM  gltrans
            INNER JOIN sec_unegsxuser ON gltrans.tag = sec_unegsxuser.tagref AND sec_unegsxuser.userid ='".$_SESSION['UserID']."'
            INNER JOIN (SELECT SUBSTRING_INDEX(config_reportes_.valor, '.', 3)  AS valor
                        FROM config_reportes_
                        WHERE reporte ='FlujoEfectivo2'
                        GROUP BY SUBSTRING_INDEX(config_reportes_.valor, '.', 3)
                        ) configReport ON SUBSTRING_INDEX(account, '.', '3') = configReport.valor
            WHERE 1 = 1
                    AND gltrans.account != ''
                    AND gltrans.posted = 1
                    AND gltrans.periodno not LIKE '%.5'
                    ".$sqlUr.$sqlUe."
            GROUP BY SUBSTRING_INDEX(account, '.', '3')) dtOtrosInversiones;";

    $result = DB_query($SQLCuentas,$db);

    $otrosOrigenesInversion1 = 0;
    $otrosOrigenesInversion2 = 0;
    $otrosAplicacionesInversion1 = 0;
    $otrosAplicacionesInversion2 = 0;

    while ($myrowCuentas = DB_fetch_array($result)) {
         $JasperReport->addParameter("otrosOrigenesInversion1", $myrowCuentas['otrosOrigenesInversion1']);
         $JasperReport->addParameter("otrosOrigenesInversion2", $myrowCuentas['otrosOrigenesInversion2']);
         $JasperReport->addParameter("otrosAplicacionesInversion1", $myrowCuentas['otrosAplicacionesInversion1']);
         $JasperReport->addParameter("otrosAplicacionesInversion2", $myrowCuentas['otrosAplicacionesInversion2']);
         $otrosOrigenesInversion1 = $myrowCuentas['otrosOrigenesInversion1'];
         $otrosOrigenesInversion2 = $myrowCuentas['otrosOrigenesInversion2'];
         $otrosAplicacionesInversion1 = $myrowCuentas['otrosAplicacionesInversion1'];
         $otrosAplicacionesInversion2 = $myrowCuentas['otrosAplicacionesInversion2'];
    }

    $sqlUr = "";
    if (isset($_GET['tagref']) && !empty($_GET['tagref'])) {
        $sqlUr = " AND gltrans.tag = '".$_GET["tagref"]."' ";
    }

    $sqlUe = "";
    if (isset($_GET['ue']) && !empty($_GET['ue'])) {
        $sqlUe = " AND CASE WHEN '".$_GET["ue"]."' = '' THEN 1 = 1 ELSE gltrans.ln_ue IN ('".$_GET["ue"]."') END ";
    }

    $SQLInicialEjercicio = "SELECT SUBSTRING_INDEX(account, '.', '3') as account,
                                    IFNULL(SUM(case when trandate between '".$fechaInicialNew."' and  '".$fechaFinalNew."' then gltrans.amount else 0 end), 0)  as Periodo1,
                                    IFNULL(SUM(case when trandate between date_add('".$fechaInicialNew."' , INTERVAL -1 year) and date_add('".$fechaFinalNew."', INTERVAL -1 year) then gltrans.amount else 0 end), 0)  as Periodo2
                            FROM  gltrans
                            INNER JOIN sec_unegsxuser ON gltrans.tag = sec_unegsxuser.tagref AND sec_unegsxuser.userid ='".$_SESSION['UserID']."'
                            WHERE   1 = 1
                            AND (SUBSTRING_INDEX(account, '.', '3') = '1.1.1' || SUBSTRING_INDEX(account, '.', '3') = '1.1.2')
                                    AND gltrans.account != ''
                                    AND gltrans.posted = 1
                                AND gltrans.periodno not LIKE '%.5'
                                ".$sqlUr.$sqlUe."
                            ;";
                            // GROUP BY SUBSTRING_INDEX(account, '.', '3')
    // echo "<pre>".$SQLInicialEjercicio;exit();
    $resultInicio = DB_query($SQLInicialEjercicio,$db);
    $totalInicioEjercicio1 = 0;
    $totalInicioEjercicio2 = 0;
    while ($myrowInicio = DB_fetch_array($resultInicio)) {
        $JasperReport->addParameter("totalInicioEjercicio1", $myrowInicio['Periodo1']);
        $JasperReport->addParameter("totalInicioEjercicio2", $myrowInicio['Periodo2']);
        $totalInicioEjercicio1 = $myrowInicio['Periodo1'];
        $totalInicioEjercicio2 = $myrowInicio['Periodo2'];
    } 

    $totalCuentas1=0;                       
    $totalCuentas2=0;

    $SQLCuentas2 = "SELECT  
            grupo1id,
            grupo1desc,
            grupo2id,
            grupo2desc,
            sum(b.PERIODO1) as PERIODO1,
            sum(b.PERIODO2) as PERIODO2,
            sum(b.PERIODO1ABONO) as PERIODO1ABONO,
            sum(b.PERIODO2ABONO) as PERIODO2ABONO,
            sum(b.PERIODO1CARGO) as PERIODO1CARGO,
            sum(b.PERIODO2CARGO) as PERIODO2CARGO
            FROM 
                    (select n.*, d.descripcion, d.clasificacionid 
                     from  (select c.clasificacionid grupo1id,
                                c.descripcion grupo1desc, b.clasificacionid as grupo2id, b.descripcion as grupo2desc,
                                c.reporte
                            from tb_cat_guia_cumplimiento c
                            left outer join tb_cat_guia_cumplimiento b on b.padreid = c.clasificacionid and c.reporte = b.reporte
                            where c.padreid is null and (c.reporte =  'Flujo de efectivo' or b.reporte = 'Flujo de efectivo') and c.descripcion not like 'Flujos Netos%' and b.clasificacionid is not null
                           ) n
                    left outer join tb_cat_guia_cumplimiento d on d.padreid = n.grupo2id and d.reporte = n.reporte) gg
                    left outer join (SELECT cuentas.accountcode, cuentas.accountname AS cuenta_mayor,
                                                    gltrans.Periodo1  as 'PERIODO1',
                                                    gltrans.Periodo2  as 'PERIODO2',
                                                    case when SUBSTRING_INDEX(cuentas.accountcode, '.', '1') = '1' then gltrans.Periodo1Abono else 0 end  as 'PERIODO1ABONO',
                                                    case when SUBSTRING_INDEX(cuentas.accountcode, '.', '1') = '1' then gltrans.Periodo2Abono else 0 end  as 'PERIODO2ABONO',
                                                    case when SUBSTRING_INDEX(cuentas.accountcode, '.', '1') = '1' then gltrans.Periodo1Cargos else 0 end as 'PERIODO1CARGO',
                                                    case when SUBSTRING_INDEX(cuentas.accountcode, '.', '1') = '1' then gltrans.Periodo2Cargos else 0 end as 'PERIODO2CARGO'
                                             FROM config_reportes_
                                             INNER JOIN chartmaster ON config_reportes_.valor= chartmaster.accountcode
                                              -- LEFT JOIN gltrans ON config_reportes_.valor= gltrans.account and gltrans.tag = config_reportes_.tagref
                                             LEFT JOIN (SELECT SUBSTRING_INDEX(account, '.', '3') as account,
                                                                IFNULL(SUM(case when trandate between '".$fechaInicialNew."' and  '".$fechaFinalNew."' then gltrans.amount else 0 end), 0)  as Periodo1,
                                                                IFNULL(SUM(case when trandate between date_add('".$fechaInicialNew."', INTERVAL -1 year) and date_add('".$fechaFinalNew."', INTERVAL -1 year) then gltrans.amount else 0 end), 0)  as Periodo2,
                                                                
                                                                IFNULL(SUM(case when trandate between '".$fechaInicialNew."' and  '".$fechaFinalNew."' and gltrans.amount<0  and gltrans.type !=0 then gltrans.amount else 0 end), 0)  as Periodo1Abono,
                                                                IFNULL(SUM(case when trandate between date_add('".$fechaInicialNew."', INTERVAL -1 year) and date_add('".$fechaFinalNew."', INTERVAL -1 year) and gltrans.amount<0  and gltrans.type !=0 then gltrans.amount else 0 end), 0)  as Periodo2Abono,
                                                                
                                                                IFNULL(SUM(case when trandate between '".$fechaInicialNew."' and  '".$fechaFinalNew."' and gltrans.amount >=0  and gltrans.type !=0 then gltrans.amount else 0 end), 0)  as Periodo1Cargos,
                                                                IFNULL(SUM(case when trandate between date_add('".$fechaInicialNew."', INTERVAL -1 year) and date_add('".$fechaFinalNew."', INTERVAL -1 year) and gltrans.amount>=0  and gltrans.type !=0 then gltrans.amount else 0 end), 0)  as Periodo2Cargos
                                                        FROM  gltrans
                                                        INNER JOIN sec_unegsxuser ON gltrans.tag = sec_unegsxuser.tagref AND sec_unegsxuser.userid ='".$_SESSION['UserID']."'
                                                        INNER JOIN (SELECT SUBSTRING_INDEX(config_reportes_.valor, '.', 3)  AS valor
                                                                    FROM config_reportes_
                                                                    WHERE reporte ='FlujoEfectivo'
                                                                    GROUP BY SUBSTRING_INDEX(config_reportes_.valor, '.', 3)
                                                                    ) configReport ON SUBSTRING_INDEX(account, '.', '3') = configReport.valor
                                                        WHERE gltrans.tag = '".$_GET["tagref"]."'
                                                                AND CASE WHEN '".$_GET["ue"]."' = '' THEN 1 = 1 ELSE gltrans.ln_ue IN ('".$_GET["ue"]."') END
                                                                AND gltrans.account != ''
                                                                AND gltrans.posted = 1
                                                                AND gltrans.periodno not LIKE '%.5'
                                                        GROUP BY SUBSTRING_INDEX(account, '.', '3')
                                                        ) gltrans ON SUBSTRING_INDEX(config_reportes_.valor, '.', 3) = gltrans.account
                                                        
                                             
                                                        
                                             LEFT JOIN chartmaster cuentas ON substr(config_reportes_.valor, 1, 5)= cuentas.accountcode
                                             WHERE config_reportes_.reporte = 'FlujoEfectivo' and config_reportes_.tagref = '".$_GET["tagref"]."'
                                             GROUP BY substr(config_reportes_.valor, 1, 5), cuentas.accountcode, cuentas.accountname) b on b.cuenta_mayor = gg.descripcion

            GROUP BY grupo2id                                
            ORDER BY 1, 3, 7;";

    $result2 = DB_query($SQLCuentas2,$db);

    $primeraParte1=0;
    $primeraParte2=0;
    $segundaAbonoParte1=0;
    $segundaAbonoParte2=0;
    $segundaCargoParte1=0;
    $segundaCargoParte2=0;
    $terceraParte1=0;
    $terceraParte2=0;
    while ($myrowCuentas2 = DB_fetch_array($result2)) {
        
        if($myrowCuentas2['grupo1id'] == "1"){
            $primeraParte1 += $myrowCuentas2['PERIODO1'];
            $primeraParte2 += $myrowCuentas2['PERIODO2'];
        }

        if($myrowCuentas2['grupo1id'] == "31"){
            if($myrowCuentas2['grupo2id'] == "32"){
                $segundaAbonoParte1+=$myrowCuentas2['PERIODO1ABONO'];
                $segundaAbonoParte2+=$myrowCuentas2['PERIODO2ABONO'];
            }elseif($myrowCuentas2['grupo2id'] == "36"){
                $segundaCargoParte1+=$myrowCuentas2['PERIODO1CARGO'];
                $segundaCargoParte2+=$myrowCuentas2['PERIODO2CARGO'];
            }
        }

        if($myrowCuentas2['grupo1id'] == "41"){
            $terceraParte1 += $myrowCuentas2['PERIODO1'];
            $terceraParte2 += $myrowCuentas2['PERIODO2'];
        }

    }

    // $JasperReport->addParameter("totalCuentas1", ($primeraParte1));
    // $JasperReport->addParameter("totalCuentas2", ($primeraParte2));

    $totalCuentas1 = ($primeraParte1) +  (($segundaAbonoParte1 + $otrosOrigenesInversion1) + ($segundaCargoParte1 + $otrosAplicacionesInversion1));
    $totalCuentas2 = ($primeraParte2) +  (($segundaAbonoParte2 + $otrosOrigenesInversion2) + ($segundaCargoParte2 + $otrosAplicacionesInversion2));
    $JasperReport->addParameter("totalCuentas1",$totalCuentas1);
    $JasperReport->addParameter("totalCuentas2", $totalCuentas2);
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");
}


// if ($_GET["reporte"] == "rpt_estado_analitico_ingresos") {
//     $rutaReporte = "../jasper/conac/rpt_estado_analitico_ingresos";
//     $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
//     $jreport = $JasperReport->compilerReport($rutaReporte);
//     $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
//     $newDateStringInicial = $myDateTime->format('Y-m-d');
//     $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
//     $newDateStringFinal = $myDateTime->format('Y-m-d');
//     $JasperReport->addParameter("fechainicial", $newDateStringInicial);
//     $JasperReport->addParameter("fechafinal", $newDateStringFinal);
//     $JasperReport->addParameter("concepto", $_GET["concepto"]);
//     $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");
//     $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
// }



if ($_GET["reporte"] == "conac_analiticodelejercicio") {
    $rutaReporte = "../jasper/conac/rpt_estado_analitico_del_ejercicio_presupuestoegresos";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("parEntePublico", "".$_GET["entepublico"]);
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");
    $myDateTime = DateTime::createFromFormat("d-m-Y", $_GET["fechainicial"]);
    $newDateStringInicial = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $newDateStringFinal = $myDateTime->format('Y-m-d');
    $JasperReport->addParameter("fechainicial", $newDateStringInicial);
    $JasperReport->addParameter("fechafinal", $newDateStringFinal);
    $JasperReport->addParameter("parRangoDeFechas", "Desde ".$newDateStringInicial." hasta ".$newDateStringFinal);
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
    $JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/estadoTamp.jpeg"))));
    $anioReporte = $_GET['anio'];

    if($_GET["ue"]==''||!isset($_GET["ue"])){
        $_GET["ue"]='';
    }
}


if ($_GET["reporte"] == "conac_programatico_categoriaprogramatica") {
    $rutaReporte = "../jasper/conac/rpt_estadoeinforme_programatico_categprogramatica";
    //$rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("parEntePublico", "".$_GET["entepublico"]);
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
    $JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/estadoTamp.jpeg"))));

    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $fechaInicialNew = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $fechaFinalNew = $myDateTime->format('Y-m-d');
    
    $JasperReport->addParameter("fechaIni", $fechaInicialNew." 00:00:00");
    $JasperReport->addParameter("fechaFin", $fechaFinalNew. " 23:59:59");
    $JasperReport->addParameter("usuario", $_SESSION['UserID']);

    if($_GET["ue"]==''||!isset($_GET["ue"])){
        $_GET["ue"]='';
    }
}

if ($_GET["reporte"] == "conac_posturafiscal") {
    $rutaReporte = "../jasper/conac/rpt_indicadoresdeposturafiscal";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("parEntePublico", "".$_GET["entepublico"]);
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $newDateStringInicial = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $newDateStringFinal = $myDateTime->format('Y-m-d');
    $JasperReport->addParameter("fechainicial", $newDateStringInicial." 00:00:00");
    $JasperReport->addParameter("fechafinal", $newDateStringFinal. " 23:59:59");
    $JasperReport->addParameter("parRangoDeFechas", "Desde ".$newDateStringInicial." hasta ".$newDateStringFinal);
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
    $JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/estadoTamp.jpeg"))));
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");
    if($_GET["ue"]==''||!isset($_GET["ue"])){
        $_GET["ue"]='';
    }

}
    if ($_GET["reporte"] == "ldf_reporte_1") {
    $rutaReporte = "../jasper/conac/rptsituacion_financieraDetallado";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
    $JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/estadoTamp.jpeg"))));
    
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");
    
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $fechaInicialNew = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $fechaFinalNew = $myDateTime->format('Y-m-d');
    
    $JasperReport->addParameter("fechainicialnew", $fechaInicialNew." 00:00:00");
    $JasperReport->addParameter("fechafinalnew", $fechaFinalNew. " 23:59:59");
    $JasperReport->addParameter("usuario", $_SESSION['UserID']);
       
    }

if ($_GET["reporte"] == "ldf_reporte_01") {
    $rutaReporte = "../jasper/conac/rpt_ldf_1";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");
    $JasperReport->addParameter("parEntePublico", "".$_GET["entepublico"]);
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $newDateStringInicial = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $newDateStringFinal = $myDateTime->format('Y-m-d');
    $JasperReport->addParameter("fechainicial", $newDateStringInicial);
    $JasperReport->addParameter("fechafinal", $newDateStringFinal);
    $JasperReport->addParameter("parRangoDeFechas", "Desde ".$newDateStringInicial." hasta ".$newDateStringFinal);
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
}
if ($_GET["reporte"] == "ldf_reporte_2") {
    $rutaReporte = "../jasper/conac/rpt_ldf_2";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
    $JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/estadoTamp.jpeg"))));
    $JasperReport->addParameter("parEntePublico", "".$_GET["entepublico"]);

    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $newDateStringInicial = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $newDateStringFinal = $myDateTime->format('Y-m-d');
    $JasperReport->addParameter("fechainicial", $newDateStringInicial);
    $JasperReport->addParameter("fechafinal", $newDateStringFinal);
    $JasperReport->addParameter("parRangoDeFechas", "Desde ".$newDateStringInicial." hasta ".$newDateStringFinal);
    

    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $fechaInicialNew = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $fechaFinalNew = $myDateTime->format('Y-m-d');
    
    $JasperReport->addParameter("fechainicialnew", $fechaInicialNew." 00:00:00");
    $JasperReport->addParameter("fechafinalnew", $fechaFinalNew. " 23:59:59");
    $JasperReport->addParameter("usuario", $_SESSION['UserID']);

    $rutaReporte = "../jasper/conac/rptanalitico_deudapasivo";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    // $rutaReporte = "../jasper/conac/rpt_ldf_21";
    // $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    // $jreport = $JasperReport->compilerReport($rutaReporte);
    // $JasperReport->addParameter("parEntePublico", "".$_GET["entepublico"]);
    // $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");

    // $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    // $newDateStringInicial = $myDateTime->format('Y-m-d');
    // $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    // $newDateStringFinal = $myDateTime->format('Y-m-d');
    // $JasperReport->addParameter("fechainicial", $newDateStringInicial);
    // $JasperReport->addParameter("fechafinal", $newDateStringFinal);
    // $JasperReport->addParameter("parRangoDeFechas", "Desde ".$newDateStringInicial." hasta ".$newDateStringFinal);
    // $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
}

if ($_GET["reporte"] == "ldf_reporte_3") {
    $rutaReporte = "../jasper/conac/rpt_ldf_3";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("parEntePublico", "".$_GET["entepublico"]);
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
    $JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/estadoTamp.jpeg"))));
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $newDateStringInicial = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $newDateStringFinal = $myDateTime->format('Y-m-d');
    $JasperReport->addParameter("fechainicial", $newDateStringInicial);
    $JasperReport->addParameter("fechafinal", $newDateStringFinal);
    $JasperReport->addParameter("parRangoDeFechas", "Desde ".$newDateStringInicial." hasta ".$newDateStringFinal);
    
}

if ($_GET["reporte"] == "ldf_reporte_5") {
    $rutaReporte = "../jasper/conac/rpt_ldf_5";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $newDateStringInicial = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $newDateStringFinal = $myDateTime->format('Y-m-d');
    $JasperReport->addParameter("fechainicial", $newDateStringInicial);
    $JasperReport->addParameter("fechafinal", $newDateStringFinal);
    $JasperReport->addParameter("parRangoDeFechas", "Desde ".$newDateStringInicial." hasta ".$newDateStringFinal);
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
}

if ($_GET["reporte"] == "ldf_reporte_6") {
    $rutaReporte = "../jasper/conac/rpt_ldf_6";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $newDateStringInicial = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $newDateStringFinal = $myDateTime->format('Y-m-d');
    $JasperReport->addParameter("fechainicial", $newDateStringInicial);
    $JasperReport->addParameter("fechafinal", $newDateStringFinal);
    $JasperReport->addParameter("parRangoDeFechas", "Desde ".$newDateStringInicial." hasta ".$newDateStringFinal);
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
}

if ($_GET["reporte"] == "ldf_reporte_7") {
    $rutaReporte = "../jasper/conac/rpt_ldf_7";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("parEntePublico", "".$_GET["entepublico"]);
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
}

if ($_GET["reporte"] == "ldf_reporte_7b") {
    $rutaReporte = "../jasper/conac/rpt_ldf_7b";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("parEntePublico", "".$_GET["entepublico"]);
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
}

if ($_GET["reporte"] == "ldf_reporte_7c") {
    $rutaReporte = "../jasper/conac/rpt_ldf_7c";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("parEntePublico", "".$_GET["entepublico"]);
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
}

if ($_GET["reporte"] == "ldf_reporte_7d") {
    $rutaReporte = "../jasper/conac/rpt_ldf_7d";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("parEntePublico", "".$_GET["entepublico"]);
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
}

if ($_GET["reporte"] == "ldf_reporte_8") {
    $rutaReporte = "../jasper/conac/rpt_ldf_8main";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("parEntePublico", "".$_GET["entepublico"]);
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
}

if ($_GET["reporte"] == "ldf_guia") {
    $rutaReporte = "../jasper/conac/rpt_ldf_guiamain";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("parEntePublico", "".$_GET["entepublico"]);
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
}

if ($_GET["reporte"] == "activo_reporteetiquetas") {
    $rutaReporte = "activofijo/Activo Fijo Etiqueta";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("parEntePublico", "".$_GET["entepublico"]);
}

// Código para cargar todos los LDF's, necesitan tener el formato rpt_ldf_##, donde rpt_ldf_##_xls es el nombre del reporte en Jasper
if(substr($_GET["reporte"],0,8)=="rpt_ldf_"){
    $rutaReporte = "../jasper/conac/$_GET[reporte]_xls";
    $rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
    $jreport = $JasperReport->compilerReport($rutaReporte);
    $JasperReport->addParameter("parEntePublico", "".$_GET["entepublico"]);

    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");

    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
    $JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/estadoTamp.jpeg"))));

    if($_GET["ue"]==''||!isset($_GET["ue"])){
        $_GET["ue"]='-1';
    }
    $JasperReport->addParameter("anioreporte", $_SESSION['ejercicioFiscal']);
}else{
    if($_GET["reporte"] == "rpt_estado_analitico_ingresos"){
        $JasperReport->addParameter("anioreporte", $_SESSION['ejercicioFiscal']);
    }else{
        $JasperReport->addParameter("anioreporte", $anioReporte);
    }
}



//echo "tagref antes";
if (isset($_GET["tagref"])) {
    $JasperReport->addParameter("tagref", $_GET["tagref"]);
}

// Campos agregados para reportes LDF
if(isset($_GET["fechainicial"])){
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
    $newDateStringInicial = $myDateTime->format('Y-m-d');
    $JasperReport->addParameter("fechaIni", $newDateStringInicial." 00:00:00");
}

// Campos agregados para reportes LDF
if(isset($_GET["fechafinal"])){
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $newDateStringFinal = $myDateTime->format('Y-m-d');
    $JasperReport->addParameter("fechaFin", $newDateStringFinal." 23:59:59");
}

if (isset($_GET["ue"])) {
    if ($_GET["ue"] == '') {
        $_GET["ue"] = '';
    }
    $JasperReport->addParameter("ue", $_GET["ue"]);
}

if($gerenciaDescripcion!=""){
    $JasperReport->addParameter("descripcionUE", $gerenciaDescripcion);
}



//echo $_GET["tagref"];
//echo "tagref despues";
$JasperReport->addParameter("entepublico", "".$_GET["entepublico"]);
$JasperReport->addParameter("parEntePublico", "".$_GET["entepublico"]);
$JasperReport->addParameter("fechaReporteNew", "" . fnFormatoFecha($_GET["fechainicial"],$_GET["fechafinal"]));
$JasperReport->addParameter("fechaReporteNewFin", "" . fnFormatoFechaFin($_GET["fechafinal"]));
//$JasperReport->addParameter ( "parRangoDeFechas", "Desde 1 Enero del " . $_GET["anio"] . " al 31 de diciembre del " . $_GET["anio"]);


function fnFormatoFecha($fechaIni, $fechaFinal){
    //fecha recibe 23-10-2018  
    $fecha ="La fecha ".$fechaIni." reporte ".$_GET["fechafinal"] ;

    $arrFechaIni = explode('-', $fechaIni);
    $arrFechaFin = explode('-', $fechaFinal);

    $meses = array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Juulio',
               'Agosto','Septiembre','Octubre','Noviembre','Diciembre');

    $strFechaIni = $arrFechaIni[0]. " de " . $meses[$arrFechaIni[1] -1 ] . " de " .$arrFechaIni[2];
    $strFechaFin = $arrFechaFin[0]. " de " . $meses[$arrFechaFin[1] -1 ] . " de " .$arrFechaFin[2];

    $fecha = "Del " . $strFechaIni . ' al ' . $strFechaFin;


    return $fecha;
}

function fnFormatoFechaFin($fechaFinal){
    $arrFechaFin = explode('-', $fechaFinal);
    $meses = array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Juulio',
               'Agosto','Septiembre','Octubre','Noviembre','Diciembre');
    $strFechaFin = $arrFechaFin[0]. " de " . $meses[$arrFechaFin[1] -1 ] . " de " .$arrFechaFin[2];
    $fecha = "Al " . $strFechaFin;
    return $fecha;
}

$conexion = $JasperReport->getConexionDB($_SESSION ["DatabaseName"], $dbuser, $dbpassword);
$jPrint = $JasperReport->fillReport($jreport, $JasperReport->getParameters(), $conexion);
$pdfBytes = ( $XLS ? $JasperReport->exportReportXLS($jPrint) : $JasperReport->exportReportPDF($jPrint) );

header('Content-type: application/'.( $XLS ? "vnd.ms-excel" : "pdf" ));
header('Content-Length: ' . strlen($pdfBytes));
header('Content-Disposition: inline; filename='."$_GET[nombreArchivo].".( $XLS ? "xls" : "pdf" ));

echo $pdfBytes;

//echo "$XLS<br>\njasper/conac/rpt_libro_diario$XLS<br>\n".( $XLS ? '$JasperReport->exportReportXLS($jPrint)' : '$JasperReport->exportReportPDF($jPrint)' )."<br>\n".'Content-type: application/'.( $XLS ? "vnd.ms-excel" : "pdf" )."<br>\n".'Content-Disposition: inline; filename='."report.".( $XLS ? "xls" : "pdf" );
