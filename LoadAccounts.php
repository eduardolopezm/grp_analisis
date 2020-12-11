<?php
/**
 * Pagina de alta masiva de cuentas contables
 *
 * @category ABC
 * @package ap_grp
 * @author Armando Barrientos Martinez <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/09/2017
 * Fecha Modificación: 02/09/2017
 */

$PageSecurity = 5;
$funcion=751;
include('includes/session.inc');
$title = traeNombreFuncion($funcion, $db);
include('includes/header.inc');
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

// Validar Identicador
$validarIdentificador = 1;

#recupera el signo que servira para hacer la separacion de cada valor
$separador = ",";
if (isset($_POST['separador'])) {
  $separador = $_POST['separador'];
} else if (isset($_GET['separador'])){
  $separador = $_GET['separador'];
}

$omitFirstRow = false;
if (isset ( $_POST ['omitFirstRow'] )) {
  $omitFirstRow = true;
}

#si la accion es cargar el archivo y la variable que sirve de separador de valores es diferente de vacio
if (isset($_POST['cargar']) and !empty($separador)){

  $nombre_archivo = $_FILES['userfile']['name'];
  $tipo_archivo = $_FILES['userfile']['type'];
  $tamano_archivo = $_FILES['userfile']['size']; 
  $filename = 'archivos/'.$nombre_archivo;

  echo "EL TIPO DE ARCHIVO ES:".$tipo_archivo;
     
    if ($tipo_archivo=='text/plain' OR $tipo_archivo=='application/vnd.ms-excel' OR $tipo_archivo=='text/csv'  OR $tipo_archivo=='application/octet-stream'){
      //echo 'entra:'.$columnaslinea;
        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $filename)){
            echo 'lineas:'.$columnaslinea;
            $tieneerrores=0;
            $mincolumnas=6;
            if ($validarIdentificador == 1) {
              $mincolumnas = 6;
            }
            $grupopadre = 0;
            $codigocuenta=1;
            $nombrecuenta=2;
            $naturalezacuenta=3;
            $tipocuenta=4;
            $diferenciador=5;
            # ABRE EL ARCHIVO Y LO ALMACENA EN UN OBJETO    
            $lineas = file($filename);
            
            # ****************************
            # **** RECORRE CADA UNA DE LAS LINEAS DEL ARCHIVO ****
            # ****************************
            
            echo "<table width=100% cellpadding=3 border=1>";
            echo "<tr>";
              echo "<td style='text-align:center;' colspan=12>";
                echo "<font size=2 color=Darkblue><b>"._('RESULTADOS DE LA CARGA DE LAS CUENTAS')."</b></font>";
              echo "</td>";
            echo "</tr>";
           
            $cont=0;
            foreach ($lineas as $line_num => $line){
               
                $datos = explode($separador, $line); # Convierte en array cada una de las lineas
          $columnaslinea = count($datos);      # Obtiene el numero de columnas de la linea en base al separador
          //echo '<br>lineas:'.$columnaslinea;
          //echo '<br><br>'.$line;
                # **** columnas de la linea menores que la minimas requeridas?***
                if ($columnaslinea<$mincolumnas){
                  $tieneerrores = 1;
                  $error = _('EL NUMERO MINIMO DE COLUMNAS REQUERIDAS NO SE CUMPLE EN LA LINEA NO ') . intval($line_num+1);
                  $error .= '<br>'. _('La estructura del archivo debe de tener al menos '.$mincolumnas.' columnas separadas por "'.$separador.'" y tiene solo '.$columnaslinea. ' columnas' );
                  prnMsg($error,'error');
                  exit;
                }
        if ($line_num > 0){ //primera linea son titulos y no se procesa
              //verificar ultima linea
            if ($datos[$codigocuenta]=='' && $datos[$nombrecuenta]=='' &&  $datos[$naturalezacuenta]==''){
                    echo "</table>";
                    prnMsg("SE CARGARON EXITOSAMENTE AL SISTEMA ".$line_num." CUENTAS AUTORIZADAS.",'sucess');
              exit;
            }
            
            /*if ($datos[$grupopadre]==''){
                            $error = _('EL GRUPO PADRE DE LA CUENTA NO PUEDE IR VACIO .<br> Verificar la linea no. ') . intval($line_num+1);
                            prnMsg($error,'error');
                            exit;                              
                        }
            $codigogrupopadre = $datos[$grupopadre];
            $qry = "Select * FROM accountgroups where groupname = '".$codigogrupopadre."'";
            $res = DB_query($qry,$db);
            if (DB_num_rows($res) == 0){
                            $error = _('EL GRUPO PADRE DE LA CUENTA NO EXISTE EN EL CATALOGO DE GRUPOS.<br> Verificar la linea no. ') . intval($line_num+1);
                            prnMsg($error,'error');
                            exit;                                         
            }*/
          
            if ($datos[$codigocuenta]==''){
              $error = _('EL CODIGO DE LA CUENTA NO PUEDE IR VACIO .<br> Verificar la linea no. ') . intval($line_num+1);
              prnMsg($error,'error');
              exit;
            } 
            #verifica que el dato sea numerico
            if (!is_long((integer)$datos[$codigocuenta])) {
                $InputError = 1;
                prnMsg(_('EL CóDIGO DE LA CUENTA TIENE QUE SER NUMERICO'),'error');
                exit;
            } 
            $codigovalido = 1;
            $codigocodigocuenta = trim($datos[$codigocuenta]);                                   

            if ($datos[$nombrecuenta]==''){
              $error = _('El Nombre de la Cuenta se encuentra vacío.<br> Verificar la línea No. ') . intval($line_num+1);
              prnMsg($error,'error');
              exit;  
            } 

            if (strlen($datos[$nombrecuenta]) > 500) {
              $InputError = 1;
              prnMsg( _('El Nombre de la Cuenta debe tener menos de 100 caracteres.'),'error');
              exit;
            } 
            $codigovalido = 1;
            $codigonombrecuenta = trim($datos[$nombrecuenta]);
            if ($datos[$naturalezacuenta]==''){    
                    $error = _('La Naturaleza de la Cuenta se encuentra vacía.<br> Verificar la línea No. ') . intval($line_num+1);
                    prnMsg($error,'error');
                    exit;  
            } 
            if (strtoupper($datos[$naturalezacuenta])!=1 && strtoupper($datos[$naturalezacuenta])!=2){
                $error = _('La Naturaleza de la Cuenta debe ser Deudora o Acreedora ('.$datos[$naturalezacuenta].') .<br> Verificar la línea No. ') . intval($line_num+1);
                prnMsg($error,'error');
                exit;             
            }
            $codigonaturalezacuenta = strtoupper(trim($datos[$naturalezacuenta]));
                          
                        
            if ($datos[$tipocuenta]==''){    
                $error = _('El Tipo de Cuenta se encuentra vacío.<br> Verificar la línea No. ') . intval($line_num+1);
                prnMsg($error,'error');
                exit;  
            }
            $codigotipocuenta = trim($datos[$tipocuenta]);

            if ($validarIdentificador == 1 && $datos[$diferenciador]==''){    
                $error = _('El Diferenciador se encuentra vacío.<br> Verificar la línea No. ') . intval($line_num+1);
                prnMsg($error,'error');
                exit;  
            }
            $codigoDiferenciador = "";
            if ($validarIdentificador == 1) {
                $codigoDiferenciador = trim($datos[$diferenciador]);
            }

            echo "<tr>";
            echo "<td style='text-align:right;'>";
            echo "<font size=2 color=Darkblue><b>".$codigogrupopadre."</b></font>";
            echo "</td>";
            echo "<td style='text-align:right;'>";
            echo "<font size=2 color=Darkblue><b>".$codigocodigocuenta."</b></font>";
            echo "</td>";
            echo "<td style='text-align:right;'>";
            echo "<font size=2 color=Darkblue><b>".utf8_encode(str_replace("@#", ",", $codigonombrecuenta))."</b></font>";
            echo "</td>";
            echo "<td style='text-align:right;'>";
            echo "<font size=2 color=Darkblue><b>".$codigonaturalezacuenta."</b></font>";
            echo "</td>";
            echo "<td style='text-align:right;'>";
            echo "<font size=2 color=Darkblue><b>".$codigotipocuenta."</b></font>";
            echo "</td>";
            if ($validarIdentificador == 1) {
                echo "<td style='text-align:right;'>";
                echo "<font size=2 color=Darkblue><b>".$codigoDiferenciador."</b></font>";
                echo "</td>";
            }
            echo "</tr>";

            if ($codigonaturalezacuenta==1){
              $codigonaturalezacuenta=1;
            }elseif($codigonaturalezacuenta==2){
              $codigonaturalezacuenta=-1;
            }
            
            $sql= "SELECT COUNT(*)
                  FROM chartmaster
                  WHERE accountcode='".$codigocodigocuenta."'";
              $result = DB_query($sql,$db);
              $myrow = DB_fetch_row($result);

              $arrayCuenta=explode('.', $codigocodigocuenta);
              $num = 1;
              $groupcode = "";
              for ($i=0; $i<count($arrayCuenta); $i++) {
                  if (($i + 1) < count($arrayCuenta)) {
                      if (empty($groupcode)) {
                          $groupcode .= "".$arrayCuenta[$i];
                      } else {
                          $groupcode .= ".".$arrayCuenta[$i];
                      }
                  }

                  $num ++;
              }

              if ($myrow[0]==0){
                $sql = "INSERT INTO chartmaster (accountcode,
                                  accountname,
                                  group_,
                                  naturaleza,
                                  tipo,
                                  ln_clave,
                                  groupcode,
                                  nu_nivel)
                              VALUES ('" . $codigocodigocuenta . "',
                                  '" . str_replace("@#", ",", $codigonombrecuenta) . "',
                                  '" . $codigogrupopadre . "',
                                  '" . $codigonaturalezacuenta . "',
                                  '" . $codigotipocuenta . "',
                                  '" . $codigoDiferenciador . "',
                                  '" . $groupcode . "',
                                  '" . fnNivelesCuentaContableGeneral($db, $codigocodigocuenta) . "')";
                  $result = DB_query($sql,$db,$ErrMsg);
                  $ErrMsg = _('No pude agregar el registro de DETALLE de cuenta');

                  $SQL = "DELETE FROM chartmasterxlegal WHERE accountcode = '" . $codigocodigocuenta . "'";
                  $ErrMsg = "No se Elimino Cuenta Proceso de Resposteo";
                  $TransResult = DB_query($SQL, $db, $ErrMsg);

                  $SQL = "INSERT INTO chartmasterxlegal (accountcode, legalid)
                  SELECT '".$codigocodigocuenta."', legalid FROM legalbusinessunit";
                  $ErrMsg = "No se Agrego Cuenta Proceso de Resposteo";
                  $TransResult = DB_query($SQL, $db, $ErrMsg);
          
                  $xsql = "SELECT tagref FROM tags";
                  $ErrMsg = _('El catalogo de unidades de negocio no pudo ser recuperado porque');
                  $xresult = DB_query($xsql,$db,$ErrMsg);
          
                  while ($xmyrow=DB_fetch_array($xresult)) {
                      $sql = "INSERT INTO chartdetails (accountcode, period, tagref)
                              SELECT '".$codigocodigocuenta."', periods.periodno, '" .  $xmyrow['tagref'] . "'
                                  FROM  periods  
                              ";
                      //  $result = DB_query($sql,$db,$ErrMsg);
                      
                  }
              } else {
                  $sql = "UPDATE chartmaster SET accountname='" . $codigonombrecuenta . "',
                        group_ = '" . $codigogrupopadre . "',
                        naturaleza = " . $codigonaturalezacuenta . ",
                        tipo = '" .$codigotipocuenta . "',
                        ln_clave = '".$codigoDiferenciador."',
                        groupcode = '".$groupcode."'
                    WHERE accountcode = '".$codigocodigocuenta."'";

                  $ErrMsg = _('No pude actualizar la cuenta porque');
                  $result = DB_query($sql,$db,$ErrMsg);
                  prnMsg (_('La cuenta contable ha sido actualizada'),'success');
                  
              }
              
        }//if primer linea
        
                   
              } # Fin del for que recorre cada linea
            
            echo "</table>";
            prnMsg("SE CARGARON EXITOSAMENTE AL SISTEMA ".$line_num." CUENTAS AUTORIZADAS.",'sucess');

        }else{ 
           echo "Ocurrió algún error al subir el fichero. No pudo guardarse."; 
        };
    };
}

echo "<form method='post' enctype='multipart/form-data' action=" . $_SERVER['PHP_SELF'] . '?' . SID . ' name="form">';

$checked = '';
if ($omitFirstRow) {
    $checked = 'checked';
}

?>
<div style="overflow-x:scroll; width: 100%;">
  
  <table class="table table-bordered">
    <thead>
      <tr class="header-verde">
        <th style="text-align:center;">Código del Género</th>
        <th style="text-align:center;">Cuenta Contable</th>
        <th style="text-align:center;">Nombre</th>
        <th style="text-align:center;">Naturaleza</th>
        <th style="text-align:center;">Código de la Unidad Responsable</th>
        <th style="text-align:center;">Código de la Unidad Ejecutora</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td></td>
        <td></td>
        <td></td>
        <td>Deudora = 1 <br> Acreedora = 2</td>
        <td></td>
        <td></td>
      </tr>
    </tbody>
  </table>

</div>

<div class='row'></div>

<div align="left">

  <div class="col-md-4">
    <div class="form-group">
      <label for="exampleInputEmail1">Caracter Separador</label>
      <input class="form-control" type="text" id="separador" name="separador" placeholder="Caracter Separador" value="<?php echo $separador; ?>" size="1" maxlength="1" />
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="exampleInputEmail1">Archivo (.csv o .txt)</label>
      <input class="form-control" type="file" id="userfile" name="userfile" size="50" />
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="exampleInputEmail1">Omitir Primera Fila</label>
      <br>
      <input type="checkbox" id="omitFirstRow" name="omitFirstRow" <?php echo $checked; ?> />
    </div>
  </div>

</div>

<div class='row'></div>

<div align='center'>
  <component-button type="submit" id="cargar" name="cargar" class="glyphicon glyphicon-circle-arrow-up" value="Subir Información"></component-button>
  <br><br>
</div>

<div class='row'></div>

<?php

echo "</form>";

include('includes/footer_Index.inc');

?>