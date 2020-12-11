<?php

$PageSecurity = 5;
include('includes/session.inc');
$title = _('Cargar Inventario Autorizado...');
include('includes/header.inc');
$funcion=400;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
//include('includes/MiscFunctions.inc');

if (isset($_POST['separador']))
{
    $separador = $_POST['separador'];
}
else{
    if (isset($_GET['separador'])){
        $separador = $_GET['separador'];
    }
    else{
        $separador = ",";
    }
}

if (isset($_POST['cargar']) and $separador<>'')
{

    $nombre_archivo = $_FILES['userfile']['name'];
    $tipo_archivo = $_FILES['userfile']['type'];
    $tamano_archivo = $_FILES['userfile']['size'];

    $filename = 'pricelists/'.$nombre_archivo;

    echo "EL TIPO DE ARCHIVO ES:".$tipo_archivo;

    if ($tipo_archivo=='text/plain' OR $tipo_archivo=='application/vnd.ms-excel' OR $tipo_archivo=='application/octet-stream' OR $tipo_archivo=='text/csv')
    {
        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $filename)){

            # UNA VEZ QUE EL ARCHIVO SE CARGO, ENTRA A LEER EL CONTENIDO

            #Declara variables

            $tieneerrores=0;
            $lineatitulo=-1; //-1 para que no verifique columnas del titulo
            $mincolumnas=5;


            $columnaproducto=0;
            $columnaalmacen=1;
            $columnacantidad=2;
            $columnaminimumlevel=3;
            $columnaubicacion=4;
            $columnapreciosFin=0;

            # ABRE ERL ARCHIVO Y LO ALMACENA EN UN OBJETO
            $lineas = file($filename);

            # ****************************
            # **** RECORRE CADA UNA DE LAS LINEAS DEL ARCHIVO ****
            # ****************************

            if ($_POST['metodo'] == 1) {

                $sSQL= "UPDATE locstock";
                $sSQL.= " SET reorderlevel = 0,
                              minimumlevel=0 ";
                $result = DB_query($sSQL,$db);
            }

            echo "<table width=100% cellpadding=3 border=1>";
            echo "<tr>";
              echo "<td style='text-align:center;' colspan=6>";
                echo "<font size=2 color=Darkblue><b>"._('RESULTADOS DE CARGA LISTA DE INVENTARIO AUTORIZADO')."</b></font>";
              echo "</td>";
            echo "</tr>";

            $cont=0;
            foreach ($lineas as $line_num => $line)
              {
                $datos = explode($separador, $line); # Convierte en array cada una de las lineas
                $columnaslinea = count($datos);           # Obtiene el numero de columnas de la linea en base al separador

                # ****************************
                # **** PRIMERA VALIDACION ****
                # **** columnas de la linea menores que la minimas requeridas?***
                # ****************************
                if ($columnaslinea<$mincolumnas)
                {
                  $tieneerrores = 1;
                  $error = _('EL NUMERO MINIMO DE COLUMNAS REQUERIDAS NO SE CUMPLE EN LA LINEA NO.. ') . intval($line_num+1);
                  $error .= '<br>'. _('La estructura del archivo debe de tener al menos '.$mincolumnas.' datos separados por "'.$separador.'" y tiene '.$columnaslinea );
                  prnMsg($error,'error');
                  exit;
                }
                else
                {
                    # ************************************************************
                    # *** ENTRA A VALIDAR LOS TITULOS DE LAS LISTAS DE PRECIOS ***
                    # ************************************************************

                    if ($line_num==$lineatitulo)
                    {

                    }
                    else
                    {
                        # ********************************
                        # *** RECORRE LAS LINEAS DE DATOS
                        # ********************************



                        # *****************************************************************************
                        # *** COLUMNA CODIGO DEL PRODUCTO ***
                        # *****************************************************************************
                          # si viene vacio el codigo del producto
                          if ($datos[$columnaproducto]=='')
                          {
                              $error = _('EL CODIGO DEL PRODUCTO NO PUEDE IR VACIO .<br> Verificar la linea no. ') . intval($line_num+1);
                              prnMsg($error,'error');
                              exit;

                          } else {

                                $codigovalido = 1;
                                $codigoproducto = trim(str_replace('"',' ', $datos[$columnaproducto]));
                                $sql= "select count(*) from stockmaster where stockid='".$codigoproducto."'";
                                $result = DB_query($sql,$db);
                                $myrow = DB_fetch_row($result);

                                # ****************************
                                # **** TERCERA VALIDACION ****
                                # **** existe el codigo del producto ?? ***
                                # ****************************
                                if ($myrow[0]==0)
                                {
                                    $error = _('EL CODIGO DEL PRODUCTO "' . $codigoproducto . '" NO ESTA REGISTRADO EN EL SISTEMA. Verificar la linea no. ') . intval($line_num+1);
                                    prnMsg($error,'error');
                                    $codigovalido = 0;
                                    //exit;  no salir necesariamente, solo brincarse los productos que no encuentra.

                                }; # Fin de if existe el codigo de producto en tabla stockmaster
                          }; # Fin de if trae codigo de producto

                          # *****************************************************************************
                        # *** COLUMNA CODIGO DEL ALMACEN ***
                        # *****************************************************************************
                          # si viene vacio el codigo del producto
                          if ($datos[$columnaalmacen]=='')
                          {

                              $error = _('EL CODIGO DEL ALMACEN NO PUEDE IR VACIO .<br> Verificar la linea no. ') . intval($line_num+1);
                              prnMsg($error,'error');
                              exit;

                          } else {

                                $codigovalido = 1;
                                $codigoalmacen = trim($datos[$columnaalmacen]);
                                $codigoalmacen = str_replace('"','',$codigoalmacen);
                                $sql= "select loccode, locationname from locations where loccode='".$codigoalmacen."'";
                                $result = DB_query($sql,$db);

                                if ($myrow = DB_fetch_array($result)) {
                                    $descalmacen = $myrow['locationname'];
                                } else {
                                    $descalmacen = '';
                                    $error = _('EL CODIGO DEL ALMACEN "' . $codigoalmacen . '" NO ESTA REGISTRADO EN EL SISTEMA. Verificar la linea no. ') . intval($line_num+1);
                                    prnMsg($error.' SQL:'.$sql,'error');
                                    $codigovalido = 0;
                                    //exit;  no salir necesariamente, solo brincarse los productos que no encuentra.
                                }

                          }; # Fin de if trae codigo de producto

                          # *****************************************************************************
                          # *** COLUMNAS DE CANTIDAD AUTORIZADA
                          # *****************************************************************************

                            # VALIDA QUE EL CODIGO DEL PRODUCTO FUE VALIDO; SI NO SOLO IGNORAR ESTE PRODUCTO.
                            if ($codigovalido==1) {

                                    # Elimina el registro que ya exista e la tabla prices



                                    if ($_POST['metodo'] == 2) {

                                    }
                                    /*
                                    $sSQL= "DELETE FROM prices";
                                    $sSQL.= " WHERE stockid='".$codigoproducto."'";
                                    $sSQL.= " AND typeabbrev='".$codigolistaprecios[$k]."'";
                                    $sSQL.= " AND currabrev='".$codigomoneda."'";
                                    $sSQL.= " AND debtorno= '".$codigocliente."'";
                                    $sSQL.= " AND areacode= ''";
                                    $result = DB_query($sSQL,$db);
                                    */

                                    # Inserta registro en la tabla prices

                                    echo "<tr>";
                                    echo "<td style='text-align:left;'>";
                                    echo "<font size=2 color=red>".$cont."</font>";
                                    echo "</td>";
                                    echo "<td style='text-align:left;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigoproducto."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:left;'>";
                                    echo "<font size=2 color=Darkblue>".$codigoalmacen."</font>";
                                    echo "</td>";
                                    echo "<td style='text-align:left;'>";
                                    echo "<font size=2 color=Darkblue>".$descalmacen."</font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$datos[$columnacantidad]."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$datos[$columnaminimumlevel]."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$datos[$columnaubicacion]."</b></font>";
                                    echo "</td>";
                                    echo "</tr>";

                                    $sSQL= "UPDATE locstock
                                            SET reorderlevel = ".$datos[$columnacantidad].",
                                                minimumlevel = ".$datos[$columnaminimumlevel].",
                                                		localidad = '".$datos[$columnaubicacion]."'
                                            WHERE stockid = '".$codigoproducto."'
                                                    AND loccode = '".$codigoalmacen."'";
                                    $result = DB_query($sSQL,$db);

                                    $cont = $cont + 1;

                                    $k=$k+1;
                            };
                    };
                }; # Fin condicion columnas < mincolumnas
              }; # Fin del for que recorre cada linea

            echo "</table>";

            prnMsg("SE CARGARON EXITOSAMENTE AL SISTEMA ".$cont." INVENTARIOS AUTORIZADOS DE PRODUCTOS.",'sucess');

        }else{
           echo "Ocurrió algún error al subir el fichero. No pudo guardarse.";
        };
    };

}

echo "<form method='post' enctype='multipart/form-data' action=" . $_SERVER['PHP_SELF'] . '?' . SID . ' name="form">';
  echo "<br>";
  echo "<table width=100% cellpadding=3>";
    echo "<tr>";
      echo "<td style='text-align:center;' colspan=2>";
        echo "<font size=2 color=Darkblue><b>"._('CARGAR LISTA DE INVENTARIO AUTORIZADO')."</b></font>";
        echo "<br><br><font size=2 color=A00000><b>*** "._('La información que se cargue aplicará para todas las sucursales')." ***</b></font><br><br>";
      echo "</td>";
    echo "</tr>";

    echo "<tr>";
      echo "<td style='text-align:center;' colspan=2>";
        echo "<font size=2 color=Darkblue><b>"._('ESTE ES EL FORMATO DEL ARCHIVO A SUBIR')."</b></font>";
        echo "<br><br>";
      echo "</td>";
    echo "</tr>";

    echo "<tr>";
      echo "<td style='text-align:center;' colspan=2>";
        echo '<table cellpadding=2>';
          echo '<tr>';
            echo '<td style="text-align:center;"><font size=1>'._('codigo producto').'</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>'._('codigo almacen').'</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>'._('cantidad autorizada').'</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>'._('cantidad minima').'</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>'._('Ubicacion').'</font></td>';
          echo '</tr>';
        echo '</table>';
      echo "</td>";
    echo "</tr>";

    echo "<tr><td style='text-align:center;' colspan=2><font size=2><br><br>" . _('Metodo de Actualizacion') . ":&nbsp;
                <select name='metodo'>";

    echo "<option selected value=0>Solo cambia optimos que cambien con esta carga...</OPTION>";
    echo "<option          value=1>Elimina Todos los Optimos Antes de Subir este archivo...</OPTION>";
    echo "</select></td></tr>";


/*
echo "<tr><td style='text-align:center;' colspan=2><font size=2>" . _('Para la Sucursal') . ":&nbsp;
<select name='area'>";

$sql = 'SELECT distinct a.areacode, a.areadescription';
$sql = $sql.' FROM areas a, tags t, sec_unegsxuser uxu';
$sql = $sql.' WHERE a.areacode=t.areacode';
$sql = $sql.' AND t.tagref=uxu.tagref';
$sql = $sql.' AND uxu.userid="'.$_SESSION['UserID'].'"';
$result = DB_query($sql,$db);
if (DB_num_rows($result)>0){
	echo "<option selected value='0'> TODAS </OPTION>";

	while ($myrow=DB_fetch_array($result)){

		if (isset($_POST['DefaultLocation']) and $myrow['areacode'] == $_POST['DefaultLocation']){

			echo "<option selected value='" . $myrow['areacode'] . "'>" . $myrow['areadescription'];

		} else {
			echo "<option Value='" . $myrow['areacode'] . "'>" . $myrow['areadescription'];

		}

	}
}

    echo "</select></td></tr>";

*/

    echo "<tr>";
      echo "<td style='text-align:center;'><br><br>";
        echo "<font size=2 >"._('Caracter Separador')." : </font>&nbsp;";
        echo "<input style='text-align:center;' type='text' size=1 maxlength=1 name='separador' value='".$separador."'>&nbsp;&nbsp;";
        echo "&nbsp;&nbsp;<font size=2>". _('Archivo (.csv o .txt)') . " : </font>&nbsp;";
        echo "<input type='file' name='userfile' size=50 >&nbsp;";
      echo "</td>";
    echo "</tr>";

    echo "<tr>";
      echo "<td style='text-align:center;' colspan=2>";

        echo "<br><br><input type='submit' name='cargar' value='SUBIR INFORMACION'>";
      echo "</td>";
    echo "</tr>";

  echo "</table>";

echo "</form>";

?>
