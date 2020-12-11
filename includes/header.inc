<?php
  include('token_generate.php');
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="TecnoAplicada">
    <meta id="tokens" name="tokens" content="<?= generateFormToken('send_message',$db) ?>"> <!-- Meta Creador de Token -->
   <!--  <link rel="icon" href="../../favicon.ico"> -->

<?php
/*error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/<error_log class="txt"></error_log>');*/
//
$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

?>
<title><?php echo $title; ?></title>
<?php

//obtenerRecursos($db, 'css', $funcion);
//$xajax->printJavascript('ajaxresponse/xajax/');

$iconosmenu = array('Menu' => '<i class="fa fa-home fa-2x"', 'Salir' => '<i class="fa fa-sign-out fa-2x"');
$PathPrefix = (isset($PathPrefix) && !empty($PathPrefix)) ? $PathPrefix : '';

?>

    <link href="css/Captura_Requisicion.css" rel="stylesheet" type="text/css" />
    <link href="css/estilos_generales.css" rel="stylesheet" type="text/css" />
    <link href="css/css_lh.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="css/v3/librerias/jquery-ui/jquery-ui-1.11.4/jquery-ui.min.css">
    <link rel="stylesheet" type="text/css" href="css/v3/librerias/bootstrap-3.3.7/css/bootstrap.min.css">
    <!-- <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css"> -->
    <!-- <link rel="stylesheet" type="text/css" href="css/v3/librerias/font-awesome-4.6.3/css/font-awesome.css"> -->
    <link rel="stylesheet" type="text/css" href="css/v3/librerias/font-awesome-4.7.0/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="css/v3/css/estilos_V3_0.css">
    <link rel="stylesheet" href="includes/jquery-ui-1.11.2.custom/jquery-ui.css">
  
 
    <!--Librerias Calendario-->
    <link rel="stylesheet" type="text/css" href="css/v3/librerias/datepicker/css/bootstrap-datepicker.css">


    <script src="javascripts/jquery-1.10.2.js"></script>
    <script src="includes/jquery-ui-1.11.2.custom/jquery-ui.js"></script>
    <script src="css/v3/js/jquery.blockUI.js" type="text/javascript"></script>
    <!--Estilo del Spiner-->
    <link rel="stylesheet" type="text/css" href="css/Spiner.css">


    <script type="text/javascript">
      function mostrarOcultarFavoritos()
      {
        $('#NavPanel1').fadeToggle("slow", "linear");
          console.log('ocultar');
      }
      if(!!document.getElementsByTagName('meta')["tokens"]){
        $.ajaxSetup({
          async: false,
          cache: false,
          headers: {'X-CSRF-TOKEN': document.getElementsByTagName('meta')["tokens"].content},
          dataFilter: function(data){
            if(typeof data==='string'){
              if(data.substring(0,20)=="Error de seguridad #"){
                muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', "Su usuario inició una nueva sesión, vuelva a autenticarse por favor.");
              }
            }
            return data;
          }
        });
        $('meta[name=tokens]').remove();
      }
      
    </script>
  </head>

  <?php
  if ( isset($_GET['modal']) && ($_GET['modal']==true) ){

} else {

  // $_SESSION['ejercicioFiscal'] = 2018;

  ?>

  <body style="overflow-y:scroll;" id="idBodyGeneral" name="idBodyGeneral">
    <script type="text/javascript">
      //Se agrega funcion para desbloqueo de pantalla
      // $.blockUI({ message: '<i class="fa fa-cog fa-spin fa-3x fa-fw"></i><span class="sr-only">Cargando...</span>' });
    </script>
    <header id="divGeneralHeader" name="divGeneralHeader">
      <!-- Fixed navbar -->
      <nav class="navbar navbar-inverse navbar-static-top">
        <div class="col-md-12 menu-usuario">
          <div>
            <i class="fa fa-desktop pull-left fts12 pt3" aria-hidden="true"></i><span class="pull-left"><?php echo $_SESSION["Version"]; ?></span>
            <span class="pull-left" style="margin-left: 15px;"><?php echo 'Ejercicio Fiscal '.$_SESSION['ejercicioFiscal']; ?></span>
			<span class="pull-left" style="margin-left: 15px;"><?php echo 'Empresa: '.$_SESSION['DatabaseName']; ?></span>
			<i class="fa fa-user icono-usuario" aria-hidden="true"></i> &nbsp; <span id="username"><?php echo stripslashes($_SESSION['UsersRealName']); ?></span>
            <a href="<?php echo $rootpath . '/UserSettings.php?' . SID; ?>"> &nbsp; <i class="fa fa-cog" aria-hidden="true" style="color: white;"></i> </a>
          </div>
        </div>

        <div class="container">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#"><img src="images/logo_tampico_02.png" style="height: 55px;" class="img-responsive" alt="GRP" title="GRP" /></a>
          </div>
          <div id="navbar" class="navbar-collapse collapse">
            <div class="nav navbar-nav">
              <div class="title-header col-xs-12 col-md-12" id="txtTituloEncabezadoPagina"><?php echo $title; ?></div>
            </div>
            <ul class="nav navbar-nav navbar-right">
              <li><a href="javascript:;" onclick="mostrarOcultarFavoritos(event);"> <i class="fa fa-star-o" aria-hidden="true"></i></a> </li>
              <li><a href="index.php"><i class="fa fa-home" aria-hidden="true"></i></a></li>
              <li><a href="Logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i></a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>

      </nav>

      <!-- menu favoritos -->
      <div style="background: rgb(136, 136, 136); margin-top: -20px;" >
          <div class="linea-verde"></div>
          <div class="container" style="padding:5px; display:none;" id="NavPanel1">
              <div>
              <?php
                  //BUSCAR OPCIONES PREFERENTES PARA ESTE USUARIO DE TRANSACCIONES
                  $sql = "SELECT sec_functions.shortdescription, sec_functions.functionid, sec_functions.url
                  FROM sec_favxuser JOIN sec_functions_new as sec_functions ON sec_favxuser.functionid = sec_functions.functionid
                  WHERE sec_favxuser.userid = '" . $_SESSION['UserID'] . "' and sec_favxuser.type = 1
                  ORDER BY sec_functions.title";

                  $ReFuntion = DB_query($sql, $db);

                  if (DB_num_rows($ReFuntion) > 0) {

                      echo '<div class="col-md-6 col-sm-6" style="padding: 0px;"> ';
                      while ($ResFuntion = DB_fetch_array($ReFuntion)) {
                          echo '<div class="col-md-4" style="color:#FFF; font-size:12px;">
                                  <i class="fa fa-exchange" title="' . _('Eliminar de Favoritos') . '" onclick="location.href=\'index.php?Oper=Eliminar&functionfav=' . $ResFuntion['functionid'] . '\'" style="cursor:pointer"></i>&nbsp;
                                  <a href="' . $ResFuntion['url'] . '" class="" style="color:#FFF; font-size:12px;">' . $ResFuntion['shortdescription'] . '</a>
                              </div>';
                      }
                      echo '</div>';
                  }

                  //BUSCAR OPCIONES PREFERENTES PARA ESTE USUARIO DE TRANSACCIONES
                  $sql = "SELECT sec_functions.shortdescription, sec_functions.functionid, sec_functions.url
                  FROM sec_favxuser JOIN sec_functions_new as sec_functions ON sec_favxuser.functionid = sec_functions.functionid
                  WHERE sec_favxuser.userid = '" . $_SESSION['UserID'] . "' and sec_favxuser.type = 2
                  ORDER BY sec_functions.title";
                  
                  $ReFuntion = DB_query($sql, $db);

                  $index      = 0;
                  $bordeLinea = '';
                  if (DB_num_rows($ReFuntion) > 0) {
                      echo '<div class="col-md-6 col-sm-6" style="border-left: 1px solid #f5f5f5; padding: 0px;"> ';
                      while ($ResFuntion = DB_fetch_array($ReFuntion)) {
                          echo '<div class="col-md-4" style="color:#FFF; font-size:12px;">
                                  <i class="fa fa-line-chart" title="' . _('Eliminar de Favoritos') . '" onclick="location.href=\'index.php?Oper=Eliminar&functionfav=' . $ResFuntion['functionid'] . '\'" style="cursor:pointer"></i>&nbsp;
                                  <a href="' . $ResFuntion['url'] . '" class="" style="color:#FFF; font-size:12px;">' . $ResFuntion['shortdescription'] . '</a>
                               </div>';

                          $index++;
                      }
                      echo '</div>';
                  }
                  ?>
              </div>
            </div>
        </div>
        <!-- menu favoritos  -->
    </header>

    <?php  
  }

  ?>
    <!--Modal para Imagen de Cargando, Funciones ejecutando: muestraCargandoGeneral() y ocultaCargandoGeneral()-->
    <div class="modal " id="ModalSpinerGeneral" name="ModalSpinerGeneral" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="background: black;  opacity: 0.5;">
      <div id="divProcesandoGeneral" name="divProcesandoGeneral" style=" position: fixed; top:50%; left:50%; z-index: 1000; transform: translate(-50%, -50%);">
        <img src="images/loading.gif" width="40" height="40" style="vertical-align: center;"></div>
    </div>


   

   
    
         <?php
  if ( isset($_GET['modal']) && ($_GET['modal']==true) ){
    echo '<div id="appVue" name="appVue">';

} else {

  ?>
  <div class="" id="divContenidoGeneral" name="divContenidoGeneral" style="background-color: white;">
      <!--Se necesita para los componentes de VUE, No eliminar appVue-->
      <div id="appVue" name="appVue" class="container-fluid">

        <?php
  }
  
    ?>

    <!--Modal para mensajes y detalles General-->
    <div class="modal fade" id="ModalGeneral" name="ModalGeneral" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
      <div class="modal-dialog" role="document" name="ModalGeneralTam" id="ModalGeneralTam">
        <div class="modal-content">
          <div class="navbar navbar-inverse navbar-static-top">
            <!--Contenido Encabezado-->
            <div class="col-md-12 menu-usuario">
              <span class="glyphicon glyphicon-remove" data-dismiss="modal" id="btnCerrarIcono"></span>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
              <div class="nav navbar-nav">
                <div class="title-header">
                  <div id="ModalGeneral_Titulo" name="ModalGeneral_Titulo"></div>
                </div>
              </div>
            </div>
            <div class="linea-verde"></div>
          </div>
          <div class="modal-body">
            <!--Mensajes Dentro del Modal-->
            <div id="ModalGeneral_Advertencia" name="ModalGeneral_Advertencia"></div>
            <!--Mensaje o contenido-->
            <div id="ModalGeneral_Mensaje" name="ModalGeneral_Mensaje"></div>
          </div>
          <div class="modal-footer">
            <div id="ModalGeneral_Pie" name="ModalGeneral_Pie"></div>
          </div>
        </div>
      </div>
    </div>

<br>    
<div align="center" id="divContenidoGeneralFuncion" name="divContenidoGeneralFuncion">
    <?php
    $dataJsonMesesGenSistema = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
/**
 * Función para validar las cuentas contables para movimientos presupuestales
 * @param  [type] $db                Base de Datos
 * @param  string $funcion           Función del proceso o validación general
 * @return [type]                    Información del proceso
 */
function fnValidarCuentasPrespuestales($db, $funcion = 0) 
{
    // Validar que se encuentre configuradas las cuentas para movimientos presupuetales
    $respuesta['success'] = true;

    $respuesta['mensaje'] = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
    $respuesta['mensaje'] .= '<table class="table table-bordered">';

    $estilo = 'style="text-align: center;"';

    // Encabezado
    $respuesta['mensaje'] .= '<tr class="header-verde">';
    $respuesta['mensaje'] .= '<td colspan="2" '.$estilo.'></td>';
    $respuesta['mensaje'] .= '<td colspan="5" '.$estilo.'>Ingresos</td>';
    $respuesta['mensaje'] .= '<td colspan="7" '.$estilo.'>Egresos</td>';
    $respuesta['mensaje'] .= '</tr>';

    $respuesta['mensaje'] .= '<tr class="header-verde">';
    $respuesta['mensaje'] .= '<td '.$estilo.'>UR</td>';
    $respuesta['mensaje'] .= '<td '.$estilo.'>UE</td>';
    $respuesta['mensaje'] .= '<td '.$estilo.'>ESTIMADO</td>';
    $respuesta['mensaje'] .= '<td '.$estilo.'>POR EJECUTAR</td>';
    $respuesta['mensaje'] .= '<td '.$estilo.'>MODIFICADO</td>';
    $respuesta['mensaje'] .= '<td '.$estilo.'>DEVENGADO</td>';
    $respuesta['mensaje'] .= '<td '.$estilo.'>RECAUDADO</td>';

    $respuesta['mensaje'] .= '<td '.$estilo.'>APROBADO</td>';
    $respuesta['mensaje'] .= '<td '.$estilo.'>POR EJERCER</td>';
    $respuesta['mensaje'] .= '<td '.$estilo.'>MODIFICADO</td>';
    $respuesta['mensaje'] .= '<td '.$estilo.'>COMPROMETIDO</td>';
    $respuesta['mensaje'] .= '<td '.$estilo.'>DEVENGADO</td>';
    $respuesta['mensaje'] .= '<td '.$estilo.'>DEVENGADO</td>';
    $respuesta['mensaje'] .= '<td '.$estilo.'>PAGADO</td>';
    $respuesta['mensaje'] .= '</tr>';

    $SQL = "SELECT
    DISTINCT
    chartdetailsbudgetbytag.tagref,
    tb_cat_unidades_ejecutoras.ue,
    ln_presupuestalingreso,
    ln_presupuestalingresoEjecutar,
    ln_presupuestalingresoModificado,
    ln_presupuestalingresoDevengado,
    ln_presupuestalingresoRecaudado,
    ln_presupuestalegreso, 
    ln_presupuestalegresoEjercer, 
    ln_presupuestalegresoModificado,
    ln_presupuestalegresocomprometido, 
    ln_presupuestalegresodevengado, 
    ln_presupuestalegresoejercido,
    ln_presupuestalegresopagado
    FROM chartdetailsbudgetbytag
    JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ln_aux1 = chartdetailsbudgetbytag.ln_aux1
    LEFT JOIN tb_momentos_presupuestales ON tb_momentos_presupuestales.ln_ur = chartdetailsbudgetbytag.tagref AND tb_momentos_presupuestales.ln_ue = tb_cat_unidades_ejecutoras.ue
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = chartdetailsbudgetbytag.tagref AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = chartdetailsbudgetbytag.tagref AND tb_sec_users_ue.ue = tb_cat_unidades_ejecutoras.ue AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."'
    ORDER BY chartdetailsbudgetbytag.tagref ASC, tb_cat_unidades_ejecutoras.ue ASC";
    $ErrMsg = "No se encontro información para validar";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $respuesta['mensaje'] .= '<tr>';
        $respuesta['mensaje'] .= '<td '.$estilo.'>'.$myrow['tagref'].'</td>';
        $respuesta['mensaje'] .= '<td '.$estilo.'>'.$myrow['ue'].'</td>';
        
        // Ingresos
        $icono = '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i></p>';
        if (empty($myrow['ln_presupuestalingreso'])) {
            $respuesta['success'] = false;
            $icono = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i></p>';
        }
        $respuesta['mensaje'] .= '<td '.$estilo.'>'.$icono.'</td>';

        $icono = '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i></p>';
        if (empty($myrow['ln_presupuestalingresoEjecutar'])) {
            $respuesta['success'] = false;
            $icono = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i></p>';
        }
        $respuesta['mensaje'] .= '<td '.$estilo.'>'.$icono.'</td>';

        $icono = '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i></p>';
        if (empty($myrow['ln_presupuestalingresoModificado'])) {
            $respuesta['success'] = false;
            $icono = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i></p>';
        }
        $respuesta['mensaje'] .= '<td '.$estilo.'>'.$icono.'</td>';

        $icono = '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i></p>';
        if (empty($myrow['ln_presupuestalingresoDevengado'])) {
            $respuesta['success'] = false;
            $icono = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i></p>';
        }
        $respuesta['mensaje'] .= '<td '.$estilo.'>'.$icono.'</td>';

        $icono = '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i></p>';
        if (empty($myrow['ln_presupuestalingresoRecaudado'])) {
            $respuesta['success'] = false;
            $icono = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i></p>';
        }
        $respuesta['mensaje'] .= '<td '.$estilo.'>'.$icono.'</td>';

        // Egresos
        $icono = '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i></p>';
        if (empty($myrow['ln_presupuestalegreso'])) {
            $respuesta['success'] = false;
            $icono = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i></p>';
        }
        $respuesta['mensaje'] .= '<td '.$estilo.'>'.$icono.'</td>';

        $icono = '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i></p>';
        if (empty($myrow['ln_presupuestalegresoEjercer'])) {
            $respuesta['success'] = false;
            $icono = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i></p>';
        }
        $respuesta['mensaje'] .= '<td '.$estilo.'>'.$icono.'</td>';

        $icono = '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i></p>';
        if (empty($myrow['ln_presupuestalegresoModificado'])) {
            $respuesta['success'] = false;
            $icono = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i></p>';
        }
        $respuesta['mensaje'] .= '<td '.$estilo.'>'.$icono.'</td>';

        $icono = '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i></p>';
        if (empty($myrow['ln_presupuestalegresocomprometido'])) {
            $respuesta['success'] = false;
            $icono = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i></p>';
        }
        $respuesta['mensaje'] .= '<td '.$estilo.'>'.$icono.'</td>';

        $icono = '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i></p>';
        if (empty($myrow['ln_presupuestalegresodevengado'])) {
            $respuesta['success'] = false;
            $icono = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i></p>';
        }
        $respuesta['mensaje'] .= '<td '.$estilo.'>'.$icono.'</td>';

        $icono = '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i></p>';
        if (empty($myrow['ln_presupuestalegresoejercido'])) {
            $respuesta['success'] = false;
            $icono = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i></p>';
        }
        $respuesta['mensaje'] .= '<td '.$estilo.'>'.$icono.'</td>';

        $icono = '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i></p>';
        if (empty($myrow['ln_presupuestalegresopagado'])) {
            $respuesta['success'] = false;
            $icono = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i></p>';
        }
        $respuesta['mensaje'] .= '<td '.$estilo.'>'.$icono.'</td>';

        $respuesta['mensaje'] .= '</tr>';
    }

    $respuesta['mensaje'] .= '</table>';

    $respuesta['mensaje'] .= '<h5><p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Es necesario realizar la configuración para movimientos contables presupuetales</p></h5>';
    
    return $respuesta;
}
$validarCuentas = 1;
$respuesta = fnValidarCuentasPrespuestales($db, $funcion);
if (!$respuesta['success'] && $funcion != 87) {
    // Falta configuración
    // echo "<br>";
    echo $respuesta['mensaje'];
    // echo "<br>";
    include 'includes/footer_Index.inc';
    exit();
}

?>