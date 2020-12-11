<?php
/**
 * configuracion orden de compra
 * //configuracion_orden_compra.php
 * @category     proceso de. compra
 * @package      ap_grp
 * @author       Arturo Lopez Peña  <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 12/12/2017
 * Fecha Modificación: 12/12/2017
 * Se crea la consoliacion de las requisiciones
 */
 // ini_set('display_errors', 1);
 //  ini_set('log_errors', 1);
 //  error_reporting(E_ALL);
 //  
 
//include_once('includes/phpmailer/class.phpmailer.php');

 /*$mail = new PHPMailer;
//$mail->setFrom('mano@peluda.com', 'GHOST');
$mai->From = $from;
$mail->addAddress('eduardolopez.sis@hotmail.com', 'My Friend');
$mail->Subject  = 'PARA EL HELIS';
$mail->Body     = 'queria. ver si funciona esto imaginate si se pidiera mandar. anonimo un correo. asi. le echaba carrilla a la helida de lo suyo.';
if(!$mail->send()) {
  echo 'ENVIADO.';
  echo 'ERROR: ' . $mail->ErrorInfo;
} else {
  echo 'ENVIADOt.';
}*/
//
//
//
/*
$mensaje="queria. ver";
      $from = "soporte@tecnoaplicada.com";
      $senderName = "Sistema de prueba para DOn lalo";
      
      $asunto="PAra el HELIDO";
      
      $mailObj = new PHPMailer();
              
   
      $mailObj->From = $from;
      $mailObj->FromName = $senderName;         
      $mailObj->Subject = $asunto;          
      $mailObj->MsgHTML($mensaje);        
      $email="adrianph24@gmail.com;";
      $to = array_unique(explode(';', $email));
          
          
      foreach($to as $mail) {
        //if(IsEmailAddress($mail)) {
          $mailObj->AddAddress($mail,''); 
        //}
      }
      $mailObj->Send(); 

echo "enviadno"; */



$PageSecurity = 5;
require 'includes/session.inc';
$funcion = 2292;
$title= traeNombreFuncion($funcion, $db, 'Configuracion Proceso de Compra');
//$title = _('');
//$tituloAlternativo='Consolidaciones';
require 'includes/header.inc';

require 'includes/SecurityFunctions.inc';
require 'includes/SQL_CommonFunctions.inc';

//Librerias GRID
require 'javascripts/libreriasGrid.inc';
$permisoAlmacenista= Havepermission($_SESSION ['UserID'], 2293, $db);//2293 permisos almacenista


      /*$mensaje="queria. ver si funciona esto imaginate si se pidiera mandar. anonimo un correo. asi. le echaba carrilla a la helida de lo suyo";
      $from = "queondas@helidoLALLO.com";
      $senderName = "Sistema de prueba para DOn lalo";
      
      $asunto="PAra el HELIDO";
      
      $mailObj = new PHPMailer();
              
   
      $mailObj->From = $from;
      $mailObj->FromName = $senderName;         
      $mailObj->Subject = $asunto;          
      $mailObj->MsgHTML($mensaje);        
      $email="eduardolopez.sis@hotmail.com";
      $to = array_unique(explode(';', $email));
          
      foreach($to as $mail) {
        if(IsEmailAddress($mail)) {
          $mailObj->AddAddress($mail,''); 
        }
      }
      $mailObj->Send(); */

?>
      <link rel="stylesheet" href="css/proceso_compra.css">
<link href='https://fonts.googleapis.com/css?family=PT+Sans+Caption:400,700' rel='stylesheet' type='text/css'>
  


<div class="row text-left">
 <!-- work flow-->
 <div class="container_work_flow_grp"> 
   <!-- <div class="workFlow_grp">  -->
    <div class="col-md-12 col-xs-12">
    <div class="arrow-steps_grp espacio_work_flow">

       <!-- <div class="step " id="pasoInicio" > <span> Paso 1. Definir tipo adjudicación</span> </div>
        <div class="step" id="pasoProvesug"> <span>Paso 2. Proveedores Sugeridos</span> </div>-->
      <!--  <div class="step" id="solCoti"> <span>Paso 3. Solicitud de Cotización</span> </div>      
        <div class="step" id="recepCoti"> <span>Paso 4. Cuadro Comparativo</span> </div>-->
       
</div>
</div>
 </div> <!-- wor flow-->

<!-- datos  tabla-->
<div id="tablaTipos">
    <div id="datosTipos"> </div>
</div>

<br>
<div class="col-xs-12 col-md-12 text-center">
<button id="nuevoTipoAdjudicacion" class="glyphicon glyphicon-copy btn btn-default botonVerde" style="color: #fff;"> Nueva</button>
</div>


<!--- fin datos  tabla-->
 <!-- <button class="btn btn bgc8" style="color:#fff;"></button>-->
  <br> <br>


 <div id="datosFormuNuevaAdj" style="display:none;">
  <div class="text-center"><h4>Crear nueva adjudicación</h4></div>
  <form method="post" action="" name="adjudicacionFormu" id="adjudicacionFormu">

        <component-text-label label="Descripcion de tipo de adjudicacion" id="descripcionadjudicacion" Name="descripcionadjudicacion" placeholder="Descripcion del tipo de la adjudicacion" maxlength="50"
        value=""></component-text-label>

        <component-decimales-label label="Rango incial:" id="rangoinicial" Name="rangoinicial" placeholder="Rango inicial" maxlength="50"
        value=""></component-decimales-label>
           <component-decimales-label label="Rango tope:" id="rangotope" Name="rangotope" placeholder="Rango Tope" maxlength="50"
        value=""></component-decimales-label>

           <component-text-label label="Id de la adjudicacion" id="idadjudicacion" Name="idadjudicacion" placeholder="Id de la adjudicacion" maxlength="50"
        value=""></component-text-label>

  <br><br>
   </form>

   <div class="text-center">
  <button class="btn btn  bgc8" style="color:#fff;" id="alta">Crear adjudicación </button>
  <button class="btn btn  bgc8" style="color:#fff;" id="atras">Atrás </button>
  </div>
  </div>
<!--
    <component-text-label label="Tipo de:" id="tipoadjudicacion" Name="tipoadjudicacion" placeholder="Tipo adjudicacion" maxlength="50"
        value=""></component-text-label>

           <component-text-label label="Id d:" id="idadjudicacion" Name="idadjudicacion" placeholder="Id de adjudicacion" maxlength="50"
        value=""></component-text-label>

           <component-text-label label="Orden" id="orden" Name="orden" placeholder="Id de la adjudicacion)" maxlength="50"
        value=""></component-text-label>
  <br><br>

  <button class="btn btn  bgc8" style="color:#fff;">PEDIR DATOS POR ADJUDICACION</button>
-->
 
<!--
<component-text-label label="Tipo del campo:" id="tipocampo" Name="tipocampo" placeholder="Tipo campo" maxlength="50"
        value=""></component-text-label>-->

<div class="col-xs-12 col-md-12" style="display:none;" id="camposFormuAdjudicacion">
<form  method="post" action="" name="addCampoFormu" id="addCampoFormu">
<h4>Campos para tipo de adjudicación<span id="titulotipoAdju">  </span> </h4>
<div class="col-xs-12 col-md-4 text-left">
    <b>Tipo de campo</b>
</div>

<div class="col-xs-12 col-md-8"  >
    <select class="tipoCampoCl" name="tipocampo">

        <option value="text">Caja de texto</option>
        <option value="number">Número</option>
        <option value="textarea">Área de texto (Descripciones largas)</option>
        <option value="file">Archivo</option>
        <option value="date">Fecha</option>

    </select>
</div>




<component-text-label label="Obligatorio:" id="obligatorio" Name="obligatorio" placeholder="Obligatorio" maxlength="50"
                      value=""></component-text-label>
<!--
<component-text-label label="Tipo de adjudicacion" id="typead" Name="tuypead" placeholder="tipo de adjudicacion " maxlength="50"
                      value=""></component-text-label><!--  select del tipo de adjudicacion-->

                      <input type="hidden" value=""  name="typead" id="typead"/>


<component-text-label label="Nombre del campo" id="namecol" Name="nombrecampo" placeholder="nombre del campo" maxlength="50"
                      value=""></component-text-label>


<component-text-label label="Leyenda para etiqueta" id="commentlabel" Name="leyenda" placeholder="leyenda para etiquetea" maxlength="50"
                      value=""></component-text-label>



<component-text-label label="Maximo permitido" id="maxlen" Name="maximo" placeholder="Maximo permitido" maxlength="50"
                      value=""></component-text-label><!--- depende del tipo de  componenete  -->



<component-text-label label="Orden del elemento" id="order" Name="orden" placeholder="Ordern del elemento" maxlength="50"
                      value=""></component-text-label><!--- depende del tipo de  componenete  -->


<!--
<component-text-label label="Tipo  de parametro de evaluación " id="typeeva" Name="typeeva" placeholder="Tipo  de parametro de evaluación" maxlength="50"
                      value=""></component-text-label>--><!--- depende del tipo de  componenete  -->



</form>


<!--  <input type="submit"  value="Cargar datos">-->
<div class="text-center">
    <button class="btn btn  bgc8" style="color:#fff;" id="addCampo">Agregar campo </button>
</div>

<br>

</div>
 
</div><!--  fin. row-->

<!--
<div id="OperacionMensaje" name="OperacionMensaje"></div>
<br> <br>
<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-4 col-xs-4 text-left">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelCriteriosBusqueda" aria-expanded="true" aria-controls="collapseOne">
            <b>Criterios para inicciar proceso de compra</b>
          </a>
        </div>
      </h4>
    </div>

    <div id="PanelCriteriosBusqueda" name="PanelCriteriosBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">
      <div class="panel-body">
       <div class="col-xs-12 col-md-12">

       <br> <br> <br>
       

        <div class="row"> 
            <br>
            <div class="col-xs-12 col-md-3  text-left"> dejar comentado  -->
           
        
        

         <!--  <div class="col-xs-12  col-md-12  text-left"> <br>
              <component-number-label label="Cantidad Total Requisición:" id="txtTotalRequi" name="txtTotalRequi" maxlength="20" value=""></component-number-label>
           </div><br>

       </div>
        
     

      </div>
    fin panel body -->

    </div>
</div>

<br>
  



      <!--  <div class="wrap">
            <header>
                Enviar mail desde localhost con PHP Mailer
            </header>
 
           <section id="principal">
                <form id="formulario" method="post" action="correo.php" enctype="multipart/form-data">
                    <div class="campos">
                        <label>Para:</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="campos">
                        <label>Asunto:</label>
                        <input type="text" name="asunto">
                    </div>
                    <div class="campos">
                        <label>Mensaje:</label>
                        <textarea name="mensaje"></textarea>
                    </div>
 
                    <label>Archivo:</label>
                    <input type="file" name="adjunto" id="imagen" />
 
                    <input id="submit" type="submit" name="enviar" value="Enviar mail">
                </form>
 
            </section>
        </div>-->
 
   

<!--
</div>

          <form action="leerExcelCotizacion.php" method="post"> 
               <input type="file" name="cotizacion" id="leercotizacion" />
              <input id="submit" type="submit" name="enviar" value="Subir">
          </form>-->
<!--no borrar es parte para pintar el pie de pagina-->

<!--archivos-->

<!-- fin de archivos-->

<?php
require 'includes/footer_Index.inc';
?>
<script type="text/javascript" src="javascripts/configuracion_orden_compra.js"></script>



