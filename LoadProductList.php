<?php

/*
ini_set('display_errors', 1); 
ini_set('log_errors', 1); 
ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); 
error_reporting(E_ALL);
*/
$PageSecurity = 5;

include('includes/session.inc');

$title = _('Cargar Lista de Productos');

include('includes/header.inc');
$funcion=754;
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
if (isset($_POST['subir']))
{
    $subir = $_POST['subir'];
}

//CONDICIONES AL LEER EL ARCHIVO
if ((isset($_POST['mostrar']) || isset($_POST['subir'])) and $separador<>''){
    if (!isset($_POST['archivo'])) {
        $nombre_archivo = $_FILES['userfile']['name'];
        $tipo_archivo = $_FILES['userfile']['type'];
        $tamano_archivo = $_FILES['userfile']['size'];

        $filename = 'pricelists/'.$nombre_archivo;
        echo $tipo_archivo;
         
        if ($tipo_archivo=='text/plain' OR $tipo_archivo=='application/vnd.ms-excel' OR $tipo_archivo=='text/csv')
        {
            if (move_uploaded_file($_FILES['userfile']['tmp_name'], $filename))
            {
                # UNA VEZ QUE EL ARCHIVO SE CARGO, ENTRA A LEER EL CONTENIDO
                #Declara variables
                $tieneerrores=0;
                $lineatitulo=0;
                $mincolumnas=5;
                $columnacodigo=0;
                $columnadescorta=1;
                $columnadeslarga=2;
                $columnacodcategoria=3;
                $columnacodclase=4;
                $columnaunimedida=5;
                $columnatipo=6;
                $columnactivo=7;
                $columnacontrol=8;
                $columnaserializado=9;
                $columnaperecedero=10;
                $columnacatimpuestos=11;
                $columnastockminimo=13;
                $columnastockmaximo=14;
                $columnaspartidaesp=15;
            }
        }
    } else {
        $filename = 'pricelists/'.$_POST['archivo'];
    }

    $lineas = file($filename);
    //RECORRE CADA UNA DE LAS LINEAS DEL ARCHIVO 
    $cont=0;
    $j=0;
    $flagerror=false;
    $categorias= array();
    $productos= array();
    $unidadesmedida= array();
    $clavesCABMS= array();
    $categoriaimpuestos= array();
    $arrErrores= array();

    // consultar categorias existentes para validar
    $sql= "SELECT DISTINCT categoryid FROM stockcategory ORDER BY categoryid";
    $result = DB_query($sql,$db);

    while ($myrow = DB_fetch_array($result)) {
        $categorias[$myrow["categoryid"]]= $myrow["categoryid"];
    }

    // Consultar catalogo de productos actual para validar 
    $sql= "SELECT stockid FROM stockmaster";
    $result = DB_query($sql,$db);

    while ($myrow = DB_fetch_array($result)) {
        $productos[$myrow["stockid"]]= $myrow["stockid"];
    }

    // Consultar catalogo de unidades de medida para validar
    $sql= "SELECT unitname FROM unitsofmeasure";
    $result = DB_query($sql,$db);

    while ($myrow = DB_fetch_array($result)) {
        $unidadesmedida[$myrow["unitname"]]= $myrow["unitname"];
    }

    // Consultar catalogo CABMS para validar
    $sql= "SELECT DISTINCT eq_stockid FROM tb_partida_articulo";
    $result = DB_query($sql,$db);

    while ($myrow = DB_fetch_array($result)) {
        $clavesCABMS[$myrow["eq_stockid"]]= $myrow["eq_stockid"];
    }

    $sql= "SELECT taxcatid FROM taxcategories";
    $result = DB_query($sql,$db);

    while ($myrow = DB_fetch_array($result)) {
        $categoriaimpuestos[$myrow["taxcatid"]]= $myrow["taxcatid"];
    }

    foreach ($lineas as $line_num =>$line)
    {
        $datos = explode($separador, $line); # Convierte en array cada una de las lineas
        $columnaslinea = count($datos);
        $codigo=trim($datos[0]);
		
        if ($columnaslinea < 12){
			$error = _('EL NUMERO MINIMO DE COLUMNAS ES 12, VERIFICA LINEA '.$line);
            $arrErrores[$codigo].= "<font color='red'>".$error."</font><br>";
			//prnMsg($error,'error');
			$flagerror=true;
		}
		
        # Obtiene el numero de columnas de la linea en base al separador
        if($j>0){
            $codcategoria=trim($datos[3]);
            //VALIDACION PARA CODIGO SI VIENE VACIO Y SI EXISTE EL CODIGO EN LA TABLA
            $columnaslinea = count($datos);
            
            if($codcategoria != ''){
				//VALIDACION PARA SABER SI LA CATEGORIA EXISTE                                                      
				if (empty($categorias[$codcategoria]))
				{
				    $error = _('LA CATEGORIA "' . $codcategoria. '" NO ESTA REGISTRADA EN EL SISTEMA. Verificar la linea no. ') . $j;
                    $arrErrores[$codigo].= "<font color='red'>".$error."</font><br>";
				    //prnMsg($error,'error');
				    $flagerror=true;
				}
            }
                                
            if($codigo != ''){
                if (!empty($productos[$codigo])) {
                    $error = _('EL PRODUCTO "' . $codigo . '" YA ESTA REGISTRADO EN EL SISTEMA. Verificar la linea no. ') .$j;
                    $arrErrores[$codigo].= "<font color='red'>".$error."</font><br>";
                    //prnMsg($error,'error');
                    $flagerror=true;
                }
            }else{
                $error = _('EL CODIGO DEL PRODUCTO VIENE VACIO. Verificar la linea no. ') .$j;
                $arrErrores[$codigo].= "<font color='red'>".$error."</font><br>";
                //prnMsg($error,'error');
                $flagerror=true;
            }

            //VALIDACION SI LA DESCRIPCION CORTA  VIENE VACIA
            $descorta=trim($datos[1]);
            if($descorta == ''){
            	$descorta = $codigo;
            }
            
            //VALIDACION SI LA DESCRIPCION LARGA  VIENE VACIA
            $deslarga=trim($datos[2]);
            if($deslarga == ''){
				$deslarga = $codigo;
            }
            
            $codclase=trim($datos[4]);
            if (empty($clavesCABMS[$codclase])) {
                /*$error = _('La clave CABMS que del registro no existe en el catalogo.') .$j;
                $arrErrores[$codigo].= "<font color='red'>".$error."</font><br>";
                //prnMsg($error,'error');
                $flagerror=true;*/

                $consulta= "SELECT * FROM tb_partida_articulo WHERE eq_stockid='".$codclase."'";
                $resultado= DB_query($consulta, $db);

                if (!DB_fetch_array($resultado)){
                    $instruccion="INSERT INTO tb_partida_articulo (eq_stockid, niv, partidaEspecifica, descPartidaEspecifica, sn_partida_ant)
                        VALUES ('".$codclase."', 
                        '5', 
                        '".trim($datos[15])."',
                        '".trim($datos[1])."', 
                        '".trim($datos[15])."')";

                    DB_query($instruccion, $db);
                }
            }

            if($codclase == '') {
                $error = _('La clave CABMS VIENE VACIA. Verificar la linea no. ') .$j;
                $arrErrores[$codigo].= "<font color='red'>".$error."</font><br>";
                //prnMsg($error,'error');
                $flagerror=true;
            }
            
            //VALIDACION SI LA UNIDAD DE MEDIDA  VIENE VACIA//
            $unimedida=trim($datos[5]);
            
            if($unimedida == '' || empty($unidadesmedida[$unimedida])) {
                /*$error = _('LA UNIDAD DE MEDIDA VIENE VACIA O NO ESTA ESCRITA CORRECTAMENTE Verificar la linea no. ') .$j;
                $arrErrores[$codigo].= "<font color='red'>".$error."</font><br>";
                //prnMsg($error,'error');
                $flagerror=true;*/

                $consulta= "SELECT * FROM unitsofmeasure WHERE unitname='".$unimedida."'";
                $resultado= DB_query($consulta, $db);

                if (!DB_fetch_array($resultado) && $unimedida!= 0){
                    $instruccion="INSERT INTO unitsofmeasure (unitname, unitdecimal, mbflag)
                              VALUES ('".$unimedida."', '0', '".trim($datos[6])."')";

                    DB_query($instruccion, $db);
                }
            }
            
            //VALIDACION SI EL TIPO DE PRODUCTO VIENE VACIA O NO ESTA BIEN ESCRITO
            $tipo=trim($datos[6]);
            /*
            $sql= "select idcategorytype from categorytype where idcategorytype='".$tipo."'";
            $result = DB_query($sql,$db);
            $myrow = DB_fetch_row($result);*/
            
            if($tipo == ''){
                $error = _('EL TIPO DE PRODUCTO VIENE VACIO O NO ESTA ESCRITO CORRECTAMENTE. Verificar la linea no. ') .$j;
                $arrErrores[$codigo].= "<font color='red'>".$error."</font><br>";
                //prnMsg($error,'error');
                $flagerror=true;
            }
            
            //VALIDACION SI EL PRODUCTO VIENE VACIO O ESTA ACTIVO (1) U OBSOLETO(0)
            $activo=trim($datos[7]);
                                                    
            if($activo == ''){
                $error = _('EL CAMPO ACTIVO VIENE VACIO. Verificar la linea no. ') .$j;
                $arrErrores[$codigo].= "<font color='red'>".$error."</font><br>";
                //prnMsg($error,'error');
                $flagerror=true;
            }else{
                if($activo!='1' and $activo!='0'){
                    $error = _('EL CAMPO ACTIVO ES DIFERENTE DE 0 Y 1. Verificar la linea no. ') .$j;
                    $arrErrores[$codigo].= "<font color='red'>".$error."</font><br>";
                    //prnMsg($error,'error');
                    $flagerror=true;
                }
            }
            
            //VALIDACION SI EL PRODUCTO VIENE VACIO O ESTA CONTROLADO (1) O NO CONTROLADO(0)
            $control=trim($datos[8]);
                                                    
            if($control == ''){
                $error = _('EL CAMPO CONTROL VIENE VACIO. Verificar la linea no. ') .$j;
                $arrErrores[$codigo].= "<font color='red'>".$error."</font><br>";
                //prnMsg($error,'error');
                 $flagerror=true;
            }else{
                if($control != '1' and $control!='0'){
                    $error = _('EL CAMPO CONTROL ES DIFERENTE DE 1 Y 0. Verificar la linea no. ') .$j;
                    $arrErrores[$codigo].= "<font color='red'>".$error."</font><br>";
                    //prnMsg($error,'error');
                    $flagerror=true;
                }
                
            }
            
            //VALIDACION SI EL PRODUCTO VIENE VACIO O ES SERIALIZADO (1) O NO SERIALIZADO(0)
            $serializado=trim($datos[9]);
                                                
            if($serializado == ''){
                $error = _('EL CAMPO SERIALIZADO VIENE VACIO. Verificar la linea no. ') .$j;
                $arrErrores[$codigo].= "<font color='red'>".$error."</font><br>";
                //prnMsg($error,'error');
                $flagerror=true;
            }else{
                if($serializado != 1 and $serializado!=0){
                    $error = _('EL CAMPO SERIALIZADO ES DIFERENTE DE 0 Y 1. Verificar la linea no. ') .$j;
                    $arrErrores[$codigo].= "<font color='red'>".$error."</font><br>";
                    //prnMsg($error,'error');
                    $flagerror=true;
                }
            }

            //VALIDACION SI EL PRODUCTO VIENE VACIO O ES PERECEDERO (1) O NO PERECEDERO(0)
            $perecedero=trim($datos[10]);
                                                   
            if($perecedero == ''){
                $error = _('EL CAMPO PERECEDERO VIENE VACIO. Verificar la linea no. ') .$j;
                $arrErrores[$codigo].= "<font color='red'>".$error."</font><br>";
                //prnMsg($error,'error');
                $flagerror=true;
            }else{
                if($perecedero != 1 and $perecedero!=0){
                    $error = _('EL CAMPO PERECEDERO ES DIFERENTE DE 0 Y 1. Verificar la linea no. ') .$j;
                    $arrErrores[$codigo].= "<font color='red'>".$error."</font><br>";
                    //prnMsg($error,'error');
                    $flagerror=true;
                }
            }
            
            //VALIDACION SI EL TIPO DE PRODUCTO VIENE VACIA Y SU TIPO DE CAT. DE IMPUESTOS
            $catimpuestos=trim($datos[11]);
            if($catimpuestos == '' || empty($categoriaimpuestos[$catimpuestos])) {
                $error = _('LA CATEGORIA DE IMPUESTOS VIENE VACIA O NO ESTA ESCRITA CORRECTAMENTE. Verificar la linea no. ') .$j;
                $arrErrores[$codigo].= "<font color='red'>".$error."</font><br>";
                //prnMsg($error,'error');
                $flagerror=true;
            }

            $partidaespecifica=trim($datos[15]);

            if (empty($partidaespecifica)) {
                $error = _('El campo de partida especifica es obligatorio') .$j;
                $arrErrores[$codigo].= "<font color='red'>".$error."</font><br>";
                //prnMsg($error, 'error');
                $flagerror=true;
            }
        }

        $j++;//incremento de numeros de linea

        /*if ($j == 10){
            break;
        }*/
    }
}
//FIN DE CONDICIONES

if(isset($_GET['subir'])){
    $subir='nosubir';
}elseif(isset($_POST['subir'])){
    $subir='sisubir';
}else{
    $subir='';
}

//ALTA DE INFORMACION
if ($subir=='sisubir')
{
    $nombre_archivo = $_POST['archivo'];
    $filename = 'pricelists/'.$nombre_archivo;
    $columnacodigo=0;
    $columnadescorta=1;
    $columnadeslarga=2;
    $columnacodcategoria=3;
    $columnacodclase=4;
    $columnaunimedida=5;
    $columnatipo=6;
    $columnactivo=7;
    $columnacontrol=8;
    $columnaserializado=9;
    $columnaperecedero=10;
    $columnacatimpuestos=11;
    $columnastockminimo=13;
    $columnastockmaximo=14;
    $columnaspartidaesp=15;
    $j=0;  
    $lineas = file($filename);
    
    foreach($lineas as $line_num =>$line)
    {
        $datos = explode($separador, $line); # Convierte en array cada una de las lineas
        $columnaslinea = count($datos);      # Obtiene el numero de columnas de la linea en base al separador

        if($j>0){
			$datos[1] = trim($datos[1],"\"");
			$datos[2] = trim($datos[2],"\"");
			
			//ASIGNACION DE CADA POSICION DEL ARREGLO A UNA VARIABLE
			$codigo=trim($datos[0]);

            if (empty($productos[$codigo])){
    			if (trim($datos[1]==""))
    				$datos[1] = $codigo;
    				
    			if (trim($datos[2]==""))
    				$datos[2] = $codigo;
    				
    			$descorta=str_replace("'","",trim($datos[1]));
    			$deslarga=str_replace("'","",trim($datos[2]));
    			$codcategoria=trim($datos[3]);
    			$codclase=trim($datos[4]);
    			$unimedida=trim($datos[5]);
    			$tipo=trim($datos[6]);
    			$activo=trim($datos[7]);
    			$control=trim($datos[8]);
    			$serializado=trim($datos[9]);
    			$perecedero=trim($datos[10]);
    			$catimpuestos=trim($datos[11]);
    			$costoavg=trim($datos[12]);
    			$stockminimo=trim($datos[13]);
    			$stockmaximo=trim($datos[14]);
    				
    			//Convertimos a mayusculas el valor de las variables
    			$tipo=strtoupper($tipo);
    			$codcategoria=strtoupper($codcategoria);
    			//Convertimos a minusculas es valor de las variables
    			$unimedida = strtolower ($unimedida);
    	   
    			//revisar si el codigo esta en la BD
    			$qry = "SELECT * 
                        FROM stockmaster
    					WHERE stockid = '$codigo'";

    			$r = DB_query($qry,$db);

    			if (DB_num_rows($r) == 0){			
    					$sql = "INSERT INTO stockmaster (stockid,
    					description,
    					longdescription,
    					categoryid,
    					units,
    					mbflag,
    					discontinued,
    					controlled,
    					serialised,
    					perishable,
    					taxcatid,
    					idclassproduct,
                        eq_stockid,
    					decimalplaces,flagcommission)
    				VALUES ('".$codigo."',
    					'" .$descorta. "',
    					'" .$deslarga. "',
    					'" .$codcategoria. "',
    					'" .$unimedida. "',
    					'" .$tipo. "',
    					 '" .$activo. "',
    					 '" .$control. "',
    					 '" .$serializado. "',
    					 '" .$perecedero. "',
    					 '" .$catimpuestos. "',
    					'1',
                        '".$codclase."',
    					'2', 0)";

    				$result = DB_query($sql,$db);
    							
    			   // insertar en lockstock   aqui se agrega stock maximo y stock minimo
    				 $sql="INSERT INTO locstock(stockid,loccode,reorderlevel,minimumlevel)
    				  SELECT '$codigo', loccode, '".$stockmaximo."','".$stockminimo."'
    				  FROM locations";
    				  
                      $result = DB_query($sql,$db);
    			  
    			  // insertar en stockcostxlegal
    				 $sql="INSERT INTO stockcostsxlegal (stockid, avgcost, legalid, lastupdatedate, trandate, lastpurchaseqty)
    				  SELECT '$codigo','$costoavg', legalid, NOW(), NOW(), 0
    				  FROM legalbusinessunit";

    				  $result = DB_query($sql,$db);

                      $sql="INSERT INTO stockcostsxlegalnew (stockid, avgcost, legalid, lastupdatedate, trandate)
                      SELECT '$codigo','$costoavg', legalid, NOW(), NOW()
                      FROM legalbusinessunit";

                      $result = DB_query($sql,$db);
    			}//if existe
            }
        }

        $j++;            
    }
    
    prnMsg("SE CARGARON EXITOSAMENTE LOS PRODUCTOS AL SISTEMA ",'sucess');
}//FIN DE ALTA DE INFORMACION

echo "<form method='post' enctype='multipart/form-data' action=" . $_SERVER['PHP_SELF'] . '?' . SID . ' name="form">';
  
echo "<div>";
echo "<font size=2 color=Darkblue><b>"._('ALTA MASIVA DE PRODUCTOS')."</b></font><br>";
echo "<font size=2 color=Darkblue><b>"._('Lista de Productos')."</b></font>";
echo "</div>";
    
if(isset($_POST['mostrar'])){
    echo "<div class='table-responsive'>";
    echo '<table class="table table-bordered">';
	echo "<tr>
		<th>" . _('Línea') . "</th>
        <th>" . _('Codigo') . "</th>
		<th>" . _('Descripción Corta') . "</th>
		<th>" . _('Descripción Larga') . "</th>
		<th>" . _('Codigo Categoria') . "</th>
		<th>" . _('Codigo Clase') . "</th>
        <th>" . _('Unidad Medida') . "</th>
        <th>" . _('Tipo') . "</th>
        <th>" . _('Activo/Obsoleto') . "</th>
        <th>" . _('Controlado/Sin Control') . "</th>
        <th>" . _('Serializado') . "</th>
        <th>" . _('Perecedero') . "</th>
        <th>" . _('Categoría Impuestos') . "</th>
        <th>" . _('Partida Especifica') . "</th>
	   </tr>";
        
   
    $k=0; //row colour counter
    $j=0;

    if(file($filename)!=''){
        $lineas = file($filename);
        $contador= 1;
        
        //RECORRE CADA UNA DE LAS LINEAS DEL ARCHIVO 
        foreach ($lineas as $line_num =>$line){
            $datos = explode($separador, $line); # Convierte en array cada una de las lineas
            $columnaslinea = count($datos);      # Obtiene el numero de columnas de la linea en base al separador
            
            if($j>0){
                //ASIGNACION DE CADA POSICION DEL ARREGLO A UNA VARIABLE
                $codigo=$datos[$columnacodigo];
                $descorta=trim($datos[$columnadescorta],"\"");
                $deslarga=trim($datos[$columnadeslarga],"\"");
                $codcategoria=$datos[$columnacodcategoria];
                $codclase=$datos[$columnacodclase];
                $unimedida=$datos[$columnaunimedida];
                $tipo=$datos[$columnatipo];
                $activo=$datos[$columnactivo];
                $control=$datos[$columnacontrol];
                $serializado=$datos[$columnaserializado];
                $perecedero=$datos[$columnaperecedero];
                $catimpuestos=$datos[$columnacatimpuestos];
                $partidaEsp=$datos[$columnaspartidaesp];

                if (empty($productos[$codigo])){
                    //Convertimos a mayusculas el valor de las variables
                    $tipo=strtoupper($tipo);
                    $codcategoria=strtoupper($codcategoria);
                    //Convertimos a minusculas es valor de las variables
                    $unimedida = strtolower ($unimedida);
                    
            		if ($k==1){
            			echo '<tr class="EvenTableRows">';                                         
            			$k=0;
            		} else {
            			echo '<tr class="OddTableRows">';
            			$k=1;
            		}
                   
                    printf("<td><font size=1>%s</td>
            		<td><font size=1>%s</td>
            		<td><font size=1>%s</td>
            		<td><font size=1>%s</td>
            		<td><font size=1>%s</td>
            		<td><font size=1>%s</td>
                            <td><font size=1>%s</td>
            		<td><font size=1>%s</td>
                            <td><font size=1>%s</td>
            		<td><font size=1>%s</td>
            		<td><font size=1>%s</td>
                            <td><font size=1>%s</td>
            		<td><font size=1>%s</td>
                    <td><font size=1>%s</td>
            		</tr>",
                            $contador,
                            $codigo,
                            $descorta,
                            $deslarga,
                            $codcategoria,
                            $codclase,
                            $unimedida,
                            $tipo,
                            $activo,
                            $control,
                            $serializado,
                            $perecedero,
                            $catimpuestos,
                            $partidaEsp);

                    if (!empty($arrErrores[$codigo])){
                        echo "<td colspan=15>".$arrErrores[$codigo]."</td>";
                    }

                    $contador++;
                }
            }
            
            $j++;//numeracion de lineas

            /*if ($j==10){
                break;
            }*/
        }
    }
            
    echo '</table>';
    echo "</div>";
}

if(!isset($_POST['mostrar'])){
   echo "<table class='table table-bordered'>";
   echo "<tr>
	  	<td colspan='15' style='text-align:center'><b>Formato de columnas del archivo a subir (Sensible a mayusculas y minusculas)</b></td>
		</tr>
		<tr valign='top'>
			<td><b>Codigo<br>del Prod</b></td>
			<td><b>Descrip<br>corta</b></td>
			<td><b>Descrip<br>larga</b></td>
			<td><b>Codigo<br>categoria prod</b></td>
			<td><b>Codigo<br>clase prod</b></td>
			<td><b>Descripcion<br>Unidad de medida</b></td>
			<td><b>Tipo</b><br>A - Ensamblado<br>K - Kit<br>M - Fabricado<br>G - Phantom<br>B - Comprado<br>D - Servicio/Mano de Obr</td>
			<td><b>Activo/Obsoleto</b><br>0 - Activo<br>1 - Obsoleto</td>
			<td><b>Controlado</b><br>1(si)  0(no)</td>
			<td><b>Serializado</b><br>1(si)  0(no)</td>
			<td><b>Perecedero</b><br>1(si)  0(no)</td>
			<td><b>Categoria Impuestos</b><br>1 - Tasa 15%<br>2 - Tasa 0%<br>3 - Freight<br>4 - Tasa 16%</td>
			<td><b>Costo</td>
			<td><b>Stock Minimo</td>
			<td><b>Stock Maximo</td>
		</tr>	
	  <tr><td colspan='15'>&nbsp;</td></tr>";	
	
    echo "<tr>";
      echo "<td colspan=15 style='text-align:center;'><br><br>";
        echo "<font size=2 >"._('Caracter Separador')." : </font>&nbsp;";
        echo "<input style='text-align:center;' type='text' size=1 maxlength=1 name='separador' value='".$separador."'>&nbsp;&nbsp;";
        echo "&nbsp;&nbsp;<font size=2>". _('Archivo (.csv o .txt)') . " : </font>&nbsp;";
        echo "<input type='file' name='userfile' size=50 >&nbsp;";        
      echo "</td>";
    echo "</tr>";

    echo "<tr>";
      echo "<td style='text-align:center;' colspan=15><br>";

        echo "<input type='submit' name='mostrar' value='MOSTRAR INFORMACION'>";
        
      echo "</td>";
    echo "</tr>"; 
    echo "</table>";
}
else{
    echo "<table>";
    echo "<tr>";
    echo "<td style='text-align:center;' colspan=2><br><br>";
    echo "<input type='submit' name='subir' value='SUBIR INFORMACION'><br><br>";
    echo "<input  type='hidden'  name='archivo' value='".$nombre_archivo."'>";
    echo "<input  type='hidden'  name='separador' value='".$separador."'>";
    echo '<a href="' . $rootpath . '/LoadProductList.php?subir=nosubir'.'">' . _('Cargar Nuevamente Información') . '</a><br>' . "\n";
    echo "</td>";
    echo "</tr>"; 

    echo "</table>";
}

echo "</form>";
    
?>