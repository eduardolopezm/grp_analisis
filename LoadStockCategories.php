<?php
/**
 * ABC Categoría de Inventario
 *
 * @category Alta
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 25/09/2017
 * Fecha Modificación: 25/09/2017
 * Se realiza operaciones para el alta masiva de categorías de inventario
 */

$PageSecurity = 5;
include('includes/session.inc');
$title = _('Cargar Categorias de Inventario');
include('includes/header.inc');
$funcion=753;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

// Validar Identicador
$validarIdentificador = 1;

if (isset($_POST['separador'])) {
	$separador = $_POST['separador'];
} else {
	if (isset($_GET['separador'])){
	    $separador = $_GET['separador'];
	} else{
	    $separador = ",";
	}
}

if (isset($_POST['mostrar']) || (isset($_POST['cargar'])))
{
    if (isset($_POST['mostrar']))
    {
	$nombre_archivo = $_FILES['userfile']['name']; 
	$tipo_archivo = $_FILES['userfile']['type']; 
	$tamano_archivo = $_FILES['userfile']['size'];
	
	$filename = 'archivos/'.$nombre_archivo;
     //le decimos que archivo queremos leer
     //$xl_reader->read($nombre_archivo);
    //echo "EL TIPO DE ARCHIVO ES:".$tipo_archivo;
    echo "<CENTER><font size=2 color=Darkblue><b>"._('DATOS QUE CONTIENE EL ARCHIVO')."</b></font></CENTER>";
    if($tamano_archivo>0){
	if ($tipo_archivo == 'text/csv' OR $tipo_archivo=='text/plain' OR $tipo_archivo=='application/vnd.ms-excel' OR $tipo_archivo=='application/octet-stream')
	{
	    if (move_uploaded_file($_FILES['userfile']['tmp_name'], $filename))
	    {
		# UNA VEZ QUE EL ARCHIVO SE CARGO, ENTRA A LEER EL CONTENIDO
		#Declara variables
		$tieneerrores=0;
		
		$codcategoriainv = 0;
		$descripcion= 1;
		$tipoproducto = 2;
		$lineaproducto = 3;
		$descripcionenpedidosdeventa = 4;
		$ctainventarios = 5;
		$ctatrabajosenproceso = 6;
		$ctaajustesinventario = 7;
		$diferenciador=8;
		//$ctavariacionesdeprecio = 8;
		//$ctavariacionesdeuso = 9;
		$errorcolumna[]='';
		$errorresxcol[]='';
		$lineatitulo=0; //-1 para que no verifique columnas del titulo
		$mincolumnas=8;
		if ($validarIdentificador == 1) {
			$mincolumnas = 9;
		}

	    # ABRE EL ARCHIVO Y LO ALMACENA EN UN OBJETO
	    
	    $lineas = file($filename);
	    $cont=0;
	    $con=0;
	    echo "<table width=600  class='table table-striped table-bordered'>";
		
	    $arrLineas = array();
	    $lineaerror = false;

		foreach ($lineas as $line_num => $line)
		{
		    $lineaerror = false;
		    $arrLineas[$line_num+1] = 0;
		    $colerror = -1;
		    
		    if ($line_num == $lineatitulo) {
			echo "<thead class='bgc8' style='color:#fff !important;'>";
			echo "<th>ID</td>";
			echo "<th>Codigo de categoria de inventario</td>";
			echo "<th>Descripcion</td>";
			echo "<th>Tipo producto</td>";
			echo "<th>Linea Producto</td>";
			echo "<th>Descripcion en pedidos de venta</td>";
			echo "<th>Cuenta de Cargo</td>";
			echo "<th>Cuenta de Abono</td>";
			echo "<th>Tipo de Gasto</td>";
			if ($validarIdentificador == 1) {
				echo "<th>Diferenciador</td>";
			}
			//echo "<td>Cuenta de variaciones de precio</td>";
			//echo "<td>Cuenta de variaciones de uso</td>";
			echo "</thead>";
		    } else
		    {
			unset($datos);
			
			$datos = explode($separador, $line); # Convierte en array cada una de las lineas
			$columnaslinea = count($datos);           # Obtiene el numero de columnas de la linea en base al separador
			if ($columnaslinea<$mincolumnas)
			{
			    $tieneerrores = 1;
			    echo "<br>";
			    $error = _('EL NUMERO MINIMO DE COLUMNAS REQUERIDAS NO SE CUMPLE EN LA LINEA NO.. ') . intval($line_num+1);
			    $error .= '<br>'. _('La estructura del archivo debe de tener al menos '.$mincolumnas.' datos separados por "'.$separador.'" y tiene '.$columnaslinea );
			    prnMsg($error,'error');
			    $con+=1;
			} else
			{
				    # *** RECORRE LAS LINEAS DE DATOS
								    
				    # *****COLUMNA CODIGO DE CATEGORIA DE INVENTARIO******
				    # si el codigo tiene más de 6 caracteres
				if(strlen($datos[$codcategoriainv])>6){
				    
				    //$error=_('El codigo de Categoria de Inventario debe ser maximo de seis caracteres y minimo de uno. <br> Verificar la linea no. '). intval($line_num+1);
				    //prnMsg($error,'error');
				    $codigocatinv = trim($datos[$codcategoriainv]);
				    $arrLineas[$line_num+1] = 1;
				    $lineaerror = 1;
				    $con+=1;
				    $errorcolumna[0]='#FF0000';
				    $erroresxcol[0]='El codigo de Categoria de Inventario debe ser maximo de seis caracteres y minimo de uno';
				    //$colerror = $codcategoriainv;
				}
				  # si el codigo viene vacio
				if(strlen($datos[$codcategoriainv])==0){
				    //$error=_('El codigo de Categoria de Inventario debe ser por lo menos un caracter y menos de siete. <br> Verificar la linea no. ').intval($line_num+1);
				    //prnMsg($error,'error');
				    $arrLineas[$line_num+1] = 1;
				    $lineaerror = 1;
				    $con+=1;
				    $errorcolumna[0]='#FF0000';
				    $erroresxcol[0]='El codigo de Categoria de Inventario debe ser por lo menos un caracter y menos de siete';
				}
				 # si el codigo cumple de 1 a 6 caracteres
				if(strlen($datos[$codcategoriainv])>0 AND strlen($datos[$codcategoriainv])<=6){
				    $codigocatinv = trim($datos[$codcategoriainv]);
				    $errorcolumna[0]='#FFFFFF';
				    $erroresxcol[0]='Dato correcto';
				    if ($arrLineas[$line_num+1] == 0){
					$arrLineas[$line_num+1] = 0;
					$lineaerror = 0;
				    }
				}
				
			    # *****COLUMNA DESCRIPCION******
			    # si la descripcion tiene mas de 50 caracteres
				if(strlen($datos[$descripcion])>255){
				    //$error=_('La descripcion de la Categoria de Inventario debe de tener 50 caracteres o menos. <br> Verificar la linea no. ').intval($line_num+1);
				    //prnMsg($error,'error');
				    $descripcion1 = trim($datos[$descripcion]);
				    $arrLineas[$line_num+1] = 1;
				    $lineaerror = 1;
				    $con+=1;
				    $errorcolumna[1]='#FF0000';
				    $erroresxcol[1]='La descripcion de la Categoria de Inventario debe de tener 255 caracteres o menos';
				}
				if(strlen($datos[$descripcion])==0){
				    //$error=_('La descripcion de la Categoria de Inventario no puede ir vacia. <br> Verificar la linea no. ').intval($line_num+1);
				    //prnMsg($error,'error');
				    $descripcion1 = trim($datos[$descripcion]);
				    $arrLineas[$line_num+1] = 1;
				    $lineaerror = 1;
				    $con+=1;
				    $errorcolumna[1]='#FF0000';
				    $erroresxcol[1]='La descripcion de la Categoria de Inventario no puede ir vacia';
				}
				# si la descripcion viene vacia o tiene maximo 50 caracteres
				if(strlen($datos[$descripcion])>0 AND strlen($datos[$descripcion])<=255){
				    $descripcion1 = trim($datos[$descripcion]);
				    	$errorcolumna[1]='#FFFFFF';
					$erroresxcol[1]='Dato correcto';
				    if ($arrLineas[$line_num+1] == 0){
					$arrLineas[$line_num+1] = 0;
					$lineaerror = 0;
				    }
				}	
				       
				    # *****COLUMNA TIPO PRODUCTO******
				    # si el tipo de inventario no cumple con las caracteristicas
				    $datos[$tipoproducto]=strtoupper($datos[$tipoproducto]);
				    
				if($datos[$tipoproducto]!='D' AND $datos[$tipoproducto]!='L' AND $datos[$tipoproducto]!='F' AND $datos[$tipoproducto]!='M'){
				    //$error=_('El tipo de inventario debe ser uno de: '. 'D - Dummy item, '.'L - Servicios, '.'F - Productos terminados o '. 'M - Materia Prima'.' <br> Verificar la linea no. ').intval($line_num+1);
				    //prnMsg($error,'error');
				    $tpoprod = trim($datos[$tipoproducto]);
				    $arrLineas[$line_num+1] = 1;
				    $lineaerror = 1;
				    $con+=1;
				    $errorcolumna[2]='#FF0000';
				    $erroresxcol[2]='El tipo de inventario debe ser uno de: '. 'D - Dummy item, '.'L - Servicios, '.'F - Productos terminados o '. 'M - Materia Prima';
				}
				    # si el tipo de inventario cumple con las caracteristicas
				    $datos[$tipoproducto]=strtoupper($datos[$tipoproducto]);
				if($datos[$tipoproducto]=='D' OR $datos[$tipoproducto]=='L' OR $datos[$tipoproducto]=='F' OR $datos[$tipoproducto]=='M'){
				    $tpoprod = trim($datos[$tipoproducto]);
				    $errorcolumna[2]='#FFFFFF';
				    $erroresxcol[2]='Dato correcto';
				    if ($arrLineas[$line_num+1] == 0){
					$arrLineas[$line_num+1] = 0;
					$lineaerror = 0;
				    }
				}
				
										
				    # *****COLUMNA LINEA PRODUCTO******
				//Realiza la consulta para ver si se encuentra la linea de producto que escribio
				$lineprod=trim($datos[$lineaproducto]);
				if($lineprod=='')
				{
				            $lineproducto='';
					    if ($arrLineas[$line_num+1] == 0){
					    $arrLineas[$line_num+1] = 0;
					    $lineaerror = 0;
					    }
					    $errorcolumna[3]='#FFFFFF';
					    $erroresxcol[3]='Dato correcto';
				}
				else{
					$sql="SELECT Prodlineid
					FROM ProdLine
					WHERE Prodlineid='".$lineprod."'";
					$result = DB_query($sql,$db);
					if($myrow=DB_fetch_array($result)) {
                                            $lineproducto =trim($datos[$lineaproducto]);
					    if ($arrLineas[$line_num+1] == 0){
					    $arrLineas[$line_num+1] = 0;
					    $lineaerror = 0;
					    }
					    $errorcolumna[3]='#FFFFFF';
					    $erroresxcol[3]='Dato correcto';
					} 
					else {
                                            $lineproducto =trim($datos[$lineaproducto]);
                                            //$error = _('La linea de producto "' . $lineproducto . '" NO FUE ENCONTRADA EN EL SISTEMA. Verificar la linea no. ') . intval($line_num+1);
                                            //prnMsg($error,'error');
					    $arrLineas[$line_num+1] = 1;
					    $lineaerror = 1;
					    $con+=1;
					    $errorcolumna[3]='#FF0000';
					    $erroresxcol[3]='La linea de producto "' . $lineproducto . '" NO FUE ENCONTRADA EN EL SISTEMA';
					}
				}
				
				
				    # *****COLUMNA DESCRIPCION EN PEDIDOS DE VENTA******
				    
				    # si la descripcion en pedidos de venta no cumple con las caracteristicas
				if($datos[$descripcionenpedidosdeventa]!=1 AND $datos[$descripcionenpedidosdeventa]!=0 OR $datos[$descripcionenpedidosdeventa]==''){
				    //$error=_('La descripcion en pedidos de venta debe ser '. '1 - Permitir Texto Narrativo en Pedido Venta o '.' 0 - Sin Texto Narrativo' .' Verificar la linea no. ').intval($line_num+1);
				    //prnMsg($error,'error');
				    $descpedvta = trim($datos[$descripcionenpedidosdeventa]);
				    $arrLineas[$line_num+1] = 1;
				    $lineaerror = 1;
				    $con+=1;
				    $errorcolumna[4]='#FF0000';
				    $erroresxcol[4]=('La descripcion en pedidos de venta debe ser '. '1 - Permitir Texto Narrativo en Pedido Venta o '.' 0 - Sin Texto Narrativo');
				}
				    # si la descripcion en pedidos de venta cumple con las caracteristicas
				if($datos[$descripcionenpedidosdeventa]==1 OR $datos[$descripcionenpedidosdeventa]==0){
				    $descpedvta = trim($datos[$descripcionenpedidosdeventa]);
				    $errorcolumna[4]='#FFFFFF';
				    $erroresxcol[4]='Dato correcto';
				    if ($arrLineas[$line_num+1] == 0){
					$arrLineas[$line_num+1] = 0;
					$lineaerror = 0;
				    }
				}
				    

				
    				    # *****COLUMNA CUENTA DE INVENTARIOS******
    				$ctainv=trim($datos[$ctainventarios]);
				if($ctainv==''){
					$ctainventario =trim($datos[$ctainventarios]);
                                            //$error = _('La Cuenta de inventario NO PUEDE IR VACIA. Verificar la linea no. ') . intval($line_num+1);
                                            //prnMsg($error,'error');
					    $arrLineas[$line_num+1] = 1;
					    $lineaerror = 1;
					    $con+=1;
					    $errorcolumna[5]='#FF0000';
				            $erroresxcol[5]='La Cuenta de inventario NO PUEDE IR VACIA';
				}
				else{
				    //Realiza la consulta para ver si se encuentra la cuenta de inventario que escribio
				    $sql = "SELECT accountcode,
				    accountname
				    FROM chartmaster,
				    accountgroups
				    WHERE chartmaster.group_=accountgroups.groupname 
				     and accountcode='".$ctainv."'";
				    $BSAccountsResult = DB_query($sql,$db);
				
				    $sql = "SELECT accountcode,
				    accountname
				    FROM chartmaster,accountgroups
				    WHERE chartmaster.group_=accountgroups.groupname 
				     and accountcode='".$ctainv."'";
				    $PnLAccountsResult = DB_query($sql,$db);		    
				    if (isset($tpoprod) and $tpoprod=='L') {
					$Result = $PnLAccountsResult;
				    } else {
					$Result = $BSAccountsResult;
				    }       

				    if($myrow=DB_fetch_array($Result)) {
                                            $ctainventario =trim($datos[$ctainventarios]);
					    $errorcolumna[5]='#FFFFFF';
				            $erroresxcol[5]='Dato correcto';
					    if ($arrLineas[$line_num+1] == 0){
					    $arrLineas[$line_num+1] = 0;
					    $lineaerror = 0;
					    
					    }
				    } 
				    else {
					    $ctainventario =trim($datos[$ctainventarios]);
                                            //$error = _('La Cuenta de inventario "' . $ctainventario . '" NO FUE ENCONTRADA EN EL SISTEMA. Verificar la linea no. ') . intval($line_num+1);
                                            //prnMsg($error,'error');
					    $arrLineas[$line_num+1] = 1;
					    $lineaerror = 1;
					    $con+=1;
					    $errorcolumna[5]='#FF0000';
				            $erroresxcol[5]='La Cuenta de inventario "' . $ctainventario . '" NO FUE ENCONTRADA EN EL SISTEMA';
				    }    
				    DB_data_seek($PnLAccountsResult,0);
				    DB_data_seek($BSAccountsResult,0);
				}
				
										
				    # *****COLUMNA CUENTA DE TRABAJOS EN PROCESO******
				$ctatrabproc=trim($datos[$ctatrabajosenproceso]);
				$ctatrabproc =trim($datos[$ctatrabajosenproceso]);
				// if($ctatrabproc==''){
				// $ctatrabproc =trim($datos[$ctatrabajosenproceso]);
				//                         //$error= _('La Cuenta de trabajos en proceso NO PUEDE IR VACIA. Verificar la linea no. ') . intval($line_num+1);
				//                         //prnMsg($error,'error');
				//     $arrLineas[$line_num+1]=1;
				//     $lineaerror=1;
				//     $con+=1;
				//     $errorcolumna[6]='#FF0000';
				//         $erroresxcol[6]=_('La Cuenta de trabajos en proceso NO PUEDE IR VACIA. Verificar la linea no.').intval($line_num+1);
				// }
				// else{
				// //Realiza la consulta para ver si se encuentra la cuenta de inventario que escribio
				// $sql = "SELECT accountcode,
				// accountname
				// FROM chartmaster,
				// accountgroups
				// WHERE chartmaster.group_=accountgroups.groupname 
				//  and accountcode='".$ctatrabproc."'";
				// $BSAccountsResult = DB_query($sql,$db);

				// if($myrow=DB_fetch_array($BSAccountsResult)) {
				//                         $ctatrabproc =trim($datos[$ctatrabajosenproceso]);
				//     $errorcolumna[6]='#FFFFFF';
				//         $erroresxcol[6]='Dato correcto';
				//     if ($arrLineas[$line_num+1] == 0){
				//     $arrLineas[$line_num+1] = 0;
				//     $lineaerror = 0;
				//     }
				// } 
				// else {
				//                         $ctatrabproc =trim($datos[$ctatrabajosenproceso]);
				//                         //$error = _('La Cuenta de trabajos en proceso "' . $ctatrabproc . '" NO FUE ENCONTRADA EN EL SISTEMA. Verificar la linea no. ').intval($line_num+1);
				//                         //prnMsg($error,'error');
				//     $arrLineas[$line_num+1]=1;
				//     $lineaerror = 1;
				//     $con+=1;
				//     $errorcolumna[6]='#FF0000';
				//         $erroresxcol[6]=_('La Cuenta de trabajos en proceso "' . $ctatrabproc . '" NO FUE ENCONTRADA EN EL SISTEMA. Verificar la linea no. ').intval($line_num+1);
				// }
				// DB_data_seek($BSAccountsResult,0);
				// }
				
				    # *****COLUMNA CUENTA DE AJUSTES DE INVENTARIO******
				$ctajustinv=trim($datos[$ctaajustesinventario]);
				$ctajusteinv =trim($datos[$ctaajustesinventario]);

				$datoDiferenciador = trim($datos[$diferenciador]);
				// if($ctajustinv==''){
				//             $ctajusteinv =trim($datos[$ctaajustesinventario]);
				//                             //$error = _('La Cuenta de ajustes de inventario NO PUEDE IR VACIA. Verificar la linea no. ') . intval($line_num+1);
				//                             //prnMsg($error,'error');
				// 	    $arrLineas[$line_num+1] = 1;
				// 	    $lineaerror = 1;
				// 	    $con+=1;
				// 	    $errorcolumna[7]='#FF0000';
				//             $erroresxcol[7]='La Cuenta de ajustes de inventario NO PUEDE IR VACIA';
				// }
				// else{
				//     //Realiza la consulta para ver si se encuentra la cuenta de inventario que escribio
				//     $sql = "SELECT accountcode,
				//     accountname
				//     FROM chartmaster,accountgroups
				//     WHERE chartmaster.group_=accountgroups.groupname 
				//     -- and accountgroups.pandl!=0 
				//     and accountcode='".$ctajustinv."'";

				//     $PnLAccountsResult = DB_query($sql,$db);

				//     if($myrow=DB_fetch_array($PnLAccountsResult)) {
				//                             $ctajusteinv =trim($datos[$ctaajustesinventario]);
				// 	    $errorcolumna[7]='#FFFFFF';
				//             $erroresxcol[7]='Dato correcto';
				// 	    if ($arrLineas[$line_num+1] == 0){
				// 	    $arrLineas[$line_num+1] = 0;
				// 	    $lineaerror = 0;
				// 	    }
				//     } 
				//     else {
				//                             $ctajusteinv =trim($datos[$ctaajustesinventario]);
				//                             //$error = _('La Cuenta de ajustes de inventario "' . $ctajustinv . '" NO FUE ENCONTRADA EN EL SISTEMA. Verificar la linea no. ') . intval($line_num+1);
				//                             //prnMsg($error,'error');
				// 	    $arrLineas[$line_num+1] = 1;
				// 	    $lineaerror = 1;
				// 	    $con+=1;
				// 	    $errorcolumna[7]='#FF0000';
				//             $erroresxcol[7]='La Cuenta de ajustes de inventario "' . $ctajustinv . '" NO FUE ENCONTRADA EN EL SISTEMA';
				//      }    
				//     DB_data_seek($PnLAccountsResult,0);
				// }   
				
				    # *****COLUMNA CUENTA DE VARIACIONES DE PRECIO******
				// $ctavarprec=trim($datos[$ctavariacionesdeprecio]);
				// if($ctavarprec==''){
				//                         $ctavarprecio =trim($datos[$ctavariacionesdeprecio]);
				//                         //$error = _('La Cuenta de ajustes de variaciones de precio NO PUEDE IR VACIA. Verificar la linea no. ') . intval($line_num+1);
				//                         //prnMsg($error,'error');
				//     $arrLineas[$line_num+1] = 1;
				//     $lineaerror = 1;
				//     $con+=1;
				//     $errorcolumna[8]='#FF0000';
				//         $erroresxcol[8]='La Cuenta de ajustes de variaciones de precio NO PUEDE IR VACIA';
				// }
				// else{
				// //Realiza la consulta para ver si se encuentra la cuenta de inventario que escribio
				// $sql = "SELECT accountcode,
				// accountname
				// FROM chartmaster,accountgroups
				// WHERE chartmaster.group_=accountgroups.groupname 
				// -- and accountgroups.pandl!=0 
				// and accountcode='".$ctavarprec."'";

				// $PnLAccountsResult = DB_query($sql,$db);

				// if($myrow=DB_fetch_array($PnLAccountsResult)) {
				//                         $ctavarprecio =trim($datos[$ctavariacionesdeprecio]);
				//     $errorcolumna[8]='#FFFFFF';
				//         $erroresxcol[8]='Dato correcto';
				//     if ($arrLineas[$line_num+1] == 0){
				//     $arrLineas[$line_num+1] = 0;
				//     $lineaerror = 0;
				//     }
				// } 
				// else {
				//                         $ctavarprecio =trim($datos[$ctavariacionesdeprecio]);
				//                         //$error = _('La Cuenta de ajustes de variaciones de precio "' . $ctavarprecio . '" NO FUE ENCONTRADA EN EL SISTEMA. Verificar la linea no. ') . intval($line_num+1);
				//                         //prnMsg($error,'error');
				//     $arrLineas[$line_num+1] = 1;
				//     $lineaerror = 1;
				//     $con+=1;
				//     $errorcolumna[8]='#FF0000';
				//         $erroresxcol[8]='La Cuenta de ajustes de variaciones de precio "' . $ctavarprecio . '" NO FUE ENCONTRADA EN EL SISTEMA';
				// }    
				// DB_data_seek($PnLAccountsResult,0);
				// }
				
				    # *****COLUMNA CUENTA DE VARIACIONES DE USO******
				// $ctavaruso=trim($datos[$ctavariacionesdeuso]);
				// if($ctavaruso==''){
				// 	    $ctavariacionuso =trim($datos[$ctavariacionesdeuso]);
				//                         //$error = _('La Cuenta de variaciones de uso NO PUEDE IR VACIA. Verificar la linea no. ') . intval($line_num+1);
				//                         //prnMsg($error,'error');
				//     $arrLineas[$line_num+1] = 1;
				//     $lineaerror = 1;
				//     $con+=1;
				//     $errorcolumna[9]='#FF0000';
				//         $erroresxcol[9]='La Cuenta de variaciones de uso NO PUEDE IR VACIA';
				// }
				// else{
				// //Realiza la consulta para ver si se encuentra la cuenta de inventario que escribio
				// $sql = "SELECT accountcode,
				// accountname
				// FROM chartmaster,accountgroups
				// WHERE chartmaster.group_=accountgroups.groupname 
				// -- and accountgroups.pandl!=0
				//  and accountcode='".$ctavaruso."'";

				// $PnLAccountsResult = DB_query($sql,$db);

				// if($myrow=DB_fetch_array($PnLAccountsResult)) {
				//                         $ctavariacionuso =trim($datos[$ctavariacionesdeuso]);
				//     $errorcolumna[9]='#FFFFFF';
				//         $erroresxcol[9]='Dato correcto';
				//     if ($arrLineas[$line_num+1] == 0){
				//     $arrLineas[$line_num+1] = 0;
				//     $lineaerror = 0;
				//     }
				// } 
				// else {
				//     $ctavariacionuso =trim($datos[$ctavariacionesdeuso]);
				//                         //$error = _('La Cuenta de variaciones de uso "' . $ctavaruso . '" NO FUE ENCONTRADA EN EL SISTEMA. Verificar la linea no. ') . intval($line_num+1);
				//                         //prnMsg($error,'error');
				//     $arrLineas[$line_num+1] = 1;
				//     $lineaerror = 1;
				//     $con+=1;
				//     $errorcolumna[9]='#FF0000';
				//         $erroresxcol[9]='La Cuenta de variaciones de uso "' . $ctavaruso . '" NO FUE ENCONTRADA EN EL SISTEMA';
				// }    
				// DB_free_result($PnLAccountsResult);
				// }
				    # Inserta registro en la tabla "stockcategory"
				    
				if ($arrLineas[$line_num+1] == 1){
				    $bgcolor = "#FFFF00";
				}else{
				    $bgcolor = "#FFFFFF";
				}
				    $tpoprod=strtoupper($tpoprod);
				    $descripcion1=strtoupper($descripcion1);
				    echo "<tr style='background-color:" . $bgcolor ."'>";
					echo "<td style='text'>" . ($line_num+1) . "</td>";
					echo "<td style='text' bgcolor='".$errorcolumna[0]."'>";
					echo "<font size=2 color=Darkblue><b><a href='#' title='". $erroresxcol[0] . "'>".$codigocatinv."</a></b></font>";
					echo "</td>";
					echo "<td style='text' bgcolor='".$errorcolumna[1]."'>";
					echo "<font size=2 color=Darkblue><b><a href='#' title='".$erroresxcol[1]."'>".$descripcion1."</a></b></font>";
					echo "</td>";
					echo "<td style='text' bgcolor='".$errorcolumna[2]."'>";
					echo "<font size=2 color=Darkblue><b><a href='#' title='".$erroresxcol[2]."'>".$tpoprod."</a></b></font>";
					echo "</td>";
					echo "<td style='text' bgcolor='".$errorcolumna[3]."'>";
					echo "<font size=2 color=Darkblue><b><a href='#' title='".$erroresxcol[3]."'>".$lineproducto."</a></b></font>";
					echo "</td>";
					echo "<td style='text' bgcolor='".$errorcolumna[4]."'>";
					echo "<font size=2 color=Darkblue><b><a href='#' title='".$erroresxcol[4]."'>".$descpedvta."</a></b></font>";
					echo "</td>";
					echo "<td style='text' bgcolor='".$errorcolumna[5]."'>";
					echo "<font size=2 color=Darkblue><b><a href='#' title='".$erroresxcol[5]."'>".$ctainventario."</a></b></font>";
					echo "</td>";
					echo "<td style='text' bgcolor='".$errorcolumna[6]."'>";
					echo "<font size=2 color=Darkblue><b><a href='#' title='".$erroresxcol[6]."'>".$ctatrabproc."</a></b></font>";
					echo "</td>";
					echo "<td style='text' bgcolor='".$errorcolumna[7]."'>";
					echo "<font size=2 color=Darkblue><b><a href='#' title='".$erroresxcol[7]."'>".$ctajusteinv."</a></b></font>";
					echo "</td>";
					
					if ($validarIdentificador == 1) {
						echo "<td style='text' bgcolor='".$errorcolumna[8]."'>";
						echo "<font size=2 color=Darkblue><b><a href='#' title='".$erroresxcol[8]."'>".$datoDiferenciador."</a></b></font>";
						echo "</td>";
					}
					// 		echo "<td style='text' bgcolor='".$errorcolumna[8]."'>";
				 //    echo "<font size=2 color=Darkblue><b><a href='#' title='".$erroresxcol[8]."'>".$ctavarprecio."</a></b></font>";
					// echo "</td>";
					// 		echo "<td style='text' bgcolor='".$errorcolumna[9]."'>";
				 //    echo "<font size=2 color=Darkblue><b><a href='#' title='".$erroresxcol[9]."'>".$ctavariacionuso."</a></b></font>";
					
				    echo "</tr>";
				
			}// FIN DEL ELSE QUE RECORRE LAS LINEAS DE DATOS
				    unset($line_num);
				    unset($codigocatinv);
				    unset($descripcion1);
				    unset($tpoprod);
				    unset($lineproducto);
				    unset($descpedvta);
				    unset($ctainventario);
				    unset($ctatrabproc);
				    unset($ctajusteinv);
				    unset($ctavarprecio);
				    unset($ctavariacionuso);
				    unset($errorcolumna);
				    unset($erroresxcol);
				    unset($datoDiferenciador);
		    }		   
		}

		$con=0;

		// FIN DEL FOR QUE RECORRE CADA LINEA
		    // SI NO HAY ERROR CON LA VARIABLE "CON"
		    if ($con<1)
		    {
			echo "<tr><td colspan='11' style='text-align:center;'>";
			echo "<form method='post' enctype='multipart/form-data' action=" . $_SERVER['PHP_SELF'] . '?' . SID . ' name="form2">';
			echo "<br>";
			echo "<input type='submit' name='cancel' value='CANCELAR' class='btn bgc8' style='color:#fff;'>";
			echo "&nbsp;&nbsp;";
			echo "<input type='submit' name='cargar' value='SUBIR ARCHIVO' class='btn bgc8' style='color:#fff;'>";
			echo "<input type='hidden' name='nombrearchivo' value='" . $nombre_archivo . "'>";
			echo "<input type='hidden' name='separador' value='" . $separador . "'>";
			echo "<input type='hidden' name='codigovalido' value='" . $codigovalido . "'>";
			echo '</form>';
			echo "</td></tr>";   
		    }else{
			//nada
		    }
	    }else{
		echo "<br><center>";
		prnMsg(_('Ocurrió algun error, el fichero no pudo guardarse'),'error');
		}
	}
    }
	else{
	    echo "<br><center>";
	    prnMsg(_('La extensión del archivo no es correcta o subiste un archivo vacio'),'error');
	    //echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath ."/LoadStockcategories.php?".SID. "'>";
	    //exit;
	}
    }// FIN DE SELECCION DE MOSTRAR

if (isset($_POST['cargar']) and $separador<>'')
	{
	    $nombre_archivo = $_POST['nombrearchivo']; 
	    //$tipo_archivo = $_FILES['userfile']['type']; 
	    //$tamano_archivo = $_FILES['userfile']['size'];
	    
	    $filename = 'archivos/'.$nombre_archivo;
	    
		# UNA VEZ QUE EL ARCHIVO SE CARGO, ENTRA A LEER EL CONTENIDO
		#Declara variables
		
		$tieneerrores=0;
		
		$codcategoriainv = 0;
		$descripcion= 1;
		$tipoproducto = 2;
		$lineaproducto = 3;
		$descripcionenpedidosdeventa = 4;
		$ctainventarios = 5;
		$ctatrabajosenproceso = 6;
		$ctaajustesinventario = 7;
		$diferenciador=8;
		// $ctavariacionesdeprecio = 8;
		// $ctavariacionesdeuso = 9;
		
		$lineatitulo=0; //-1 para que no verifique columnas del titulo
		$mincolumnas=8;
		if ($validarIdentificador == 1) {
			$mincolumnas = 9;
		}
		
		# ABRE EL ARCHIVO Y LO ALMACENA EN UN OBJETO    
		$lineas = file($filename);
		
		$cont=0;
		
		echo "<table width=600 border=1>";
		
		foreach ($lineas as $line_num => $line)
		{
		    if ($line_num == $lineatitulo) {
			echo "<tr>";
			echo "<td>Codigo de categoria de inventario</td>";
			echo "<td>Descripcion</td>";
			echo "<td>Tipo producto</td>";
			echo "<td>Linea Producto</td>";
			echo "<td>Descripcion en pedidos de venta</td>";
			echo "<td>Cuenta de Cargo</td>";
			echo "<td>Cuenta de Abono</td>";
			echo "<td>Tipo de Gasto</td>";
			if ($validarIdentificador == 1) {
				echo "<td>Diferenciador</td>";
			}
			//echo "<td>Cuenta de variaciones de precio</td>";
			//echo "<td>Cuenta de variaciones de uso</td>";
			echo "</tr>";
		    }
		    else
		    {
			$datos = explode($separador, $line); # Convierte en array cada una de las lineas
			$columnaslinea = count($datos);           # Obtiene el numero de columnas de la linea en base al separador
				
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
				    # *** RECORRE LAS LINEAS DE DATOS
								    
				     # *****COLUMNA CODIGO DE CATEGORIA DE INVENTARIO******

				 # si el codigo cumple de 1 a 6 caracteres
				if(strlen($datos[$codcategoriainv])>0 AND strlen($datos[$codcategoriainv])<=6){
				    $codigovalido = 1;
				    $codigocatinv = trim($datos[$codcategoriainv]); 
				}
				     
				
				    # *****COLUMNA DESCRIPCION******
				    # si la descripcion tiene mas de 50 caracteres
				# si la descripcion viene vacia o tiene maximo 50 caracteres
				if(strlen($datos[$descripcion])>=0 AND strlen($datos[$descripcion])<=255){
				    $codigovalido = 1;
				    $descripcion1 = trim($datos[$descripcion]);
				}	     
				     
				 # *****COLUMNA TIPO PRODUCTO******
				    # si el tipo de inventario cumple con las caracteristicas
				    $datos[$tipoproducto]=strtoupper($datos[$tipoproducto]);
				if($datos[$tipoproducto]=='D' OR $datos[$tipoproducto]=='L' OR $datos[$tipoproducto]=='F' OR $datos[$tipoproducto]=='M'){
				    $codigovalido = 1;
				    $tpoprod = trim($datos[$tipoproducto]);
				}     
				     
				    # *****COLUMNA LINEA PRODUCTO******
				//Realiza la consulta para ver si se encuentra la linea de producto que escribio
				$lineprod=trim($datos[$lineaproducto]);
				$sql="SELECT Prodlineid
					FROM ProdLine
					WHERE Prodlineid='".$lineprod."'";
				$result = DB_query($sql,$db);
				if($myrow=DB_fetch_array($result)) {
                                            $lineproducto =trim($datos[$lineaproducto]);
					    $codigovalido = 1;
                                } 
				
				    # *****COLUMNA DESCRIPCION EN PEDIDOS DE VENTA******
				    # si la descripcion en pedidos de venta cumple con las caracteristicas
				if($datos[$descripcionenpedidosdeventa]==1 OR $datos[$descripcionenpedidosdeventa]==0){
				    $codigovalido = 1;
				    $descpedvta = trim($datos[$descripcionenpedidosdeventa]);
				}
				
				    # *****COLUMNA CUENTA DE INVENTARIOS******
    				$ctainv=trim($datos[$ctainventarios]);
				
				//Realiza la consulta para ver si se encuentra la cuenta de inventario que escribio
				$sql = "SELECT accountcode,
				accountname
				FROM chartmaster,
				accountgroups
				WHERE chartmaster.group_=accountgroups.groupname 
				-- and accountgroups.pandl=0 
				and accountcode='".$ctainv."'";
				$BSAccountsResult = DB_query($sql,$db);
				
				$sql = "SELECT accountcode,
				accountname
				FROM chartmaster,accountgroups
				WHERE chartmaster.group_=accountgroups.groupname and accountcode='".$ctainv."'";
				$PnLAccountsResult = DB_query($sql,$db);		    

				if (isset($tpoprod) and $tpoprod=='L') {
				$Result = $PnLAccountsResult;
				} else {
				$Result = $BSAccountsResult;
				}       

				if($myrow=DB_fetch_array($Result)) {
					    $codigovalido = 1;
                                            $ctainventario =trim($datos[$ctainventarios]);
                                } 
				DB_data_seek($PnLAccountsResult,0);
				DB_data_seek($BSAccountsResult,0);
				
				    # *****COLUMNA CUENTA DE TRABAJOS EN PROCESO******
				$ctatrabproc=trim($datos[$ctatrabajosenproceso]);
				
				//Realiza la consulta para ver si se encuentra la cuenta de inventario que escribio
				// $sql = "SELECT accountcode,
				// accountname
				// FROM chartmaster,
				// accountgroups
				// WHERE chartmaster.group_=accountgroups.groupname 
				//  and accountcode='".$ctatrabproc."'";
				// $BSAccountsResult = DB_query($sql,$db);
				
				// if($myrow=DB_fetch_array($BSAccountsResult)) {
				// 	    $codigovalido = 1;
    //                                         $ctatrabproc =trim($datos[$ctatrabajosenproceso]);
					    
    //                             } 
				// DB_data_seek($BSAccountsResult,0);
				
				    # *****COLUMNA CUENTA DE AJUSTES DE INVENTARIO******
				// $ctajustinv=trim($datos[$ctaajustesinventario]);
				
				// //Realiza la consulta para ver si se encuentra la cuenta de inventario que escribio
				// $sql = "SELECT accountcode,
				// accountname
				// FROM chartmaster,accountgroups
				// WHERE chartmaster.group_=accountgroups.groupname 
				//  and accountcode='".$ctajustinv."'";
				// $PnLAccountsResult = DB_query($sql,$db);
				
				// if($myrow=DB_fetch_array($PnLAccountsResult)) {
				// 	    $codigovalido = 1;
    //                                         $ctajusteinv =trim($datos[$ctaajustesinventario]);
    //                             } 
				// DB_data_seek($PnLAccountsResult,0);
				$ctajusteinv =trim($datos[$ctaajustesinventario]);
				
				//     # *****COLUMNA CUENTA DE VARIACIONES DE PRECIO******
				// $ctavarprec=trim($datos[$ctavariacionesdeprecio]);
				
				// //Realiza la consulta para ver si se encuentra la cuenta de inventario que escribio
				// $sql = "SELECT accountcode,
				// accountname
				// FROM chartmaster,accountgroups
				// WHERE chartmaster.group_=accountgroups.groupname 
				//  and accountcode='".$ctavarprec."'";
				// $PnLAccountsResult = DB_query($sql,$db);
				
				// if($myrow=DB_fetch_array($PnLAccountsResult)) {
				// 	    $codigovalido = 1;
    //                                         $ctavarprecio =trim($datos[$ctavariacionesdeprecio]);
    //                             } 
				// DB_data_seek($PnLAccountsResult,0);
				
				    # *****COLUMNA CUENTA DE VARIACIONES DE USO******
				$ctavaruso=trim($datos[$ctavariacionesdeuso]);
				
				// //Realiza la consulta para ver si se encuentra la cuenta de inventario que escribio
				// $sql = "SELECT accountcode,
				// accountname
				// FROM chartmaster,accountgroups
				// WHERE chartmaster.group_=accountgroups.groupname 
				//  and accountcode='".$ctavaruso."'";
				// $PnLAccountsResult = DB_query($sql,$db);
				
				// if($myrow=DB_fetch_array($PnLAccountsResult)) {
				// 	    $codigovalido = 1;
    //                                         $ctavariacionuso =trim($datos[$ctavariacionesdeuso]);
    //                             } 
				
				$datoDiferenciador = "";
				if ($validarIdentificador == 1) {
					$datoDiferenciador = trim($datos[$diferenciador]);
				}

				DB_free_result($PnLAccountsResult);	
			    if ($codigovalido==1)
			    {
				    # Inserta registro en la tabla "stockcategory"
					echo "<td style='text'>";
					echo "<font size=2 color=Darkblue><b>".$codigocatinv."</b></font>";
					echo "</td>";
					echo "<td style='text'>";
					echo "<font size=2 color=Darkblue><b>".$descripcion1."</b></font>";
					echo "</td>";
					echo "<td style='text'>";
					echo "<font size=2 color=Darkblue><b>".$tpoprod."</b></font>";
					echo "</td>";
					echo "<td style='text'>";
					echo "<font size=2 color=Darkblue><b>".$lineproducto."</b></font>";
					echo "</td>";
					echo "<td style='text'>";
					echo "<font size=2 color=Darkblue><b>".$descpedvta."</b></font>";
					echo "</td>";
					echo "<td style='text'>";
					echo "<font size=2 color=Darkblue><b>".$ctainventario."</b></font>";
					echo "</td>";
					echo "<td style='text'>";
					echo "<font size=2 color=Darkblue><b>".$ctatrabproc."</b></font>";
					echo "</td>";
					echo "<td style='text'>";
					echo "<font size=2 color=Darkblue><b>".$ctajusteinv."</b></font>";
					echo "</td>";
					if ($validarIdentificador == 1) {
						echo "<td style='text'>";
						echo "<font size=2 color=Darkblue><b>".$datoDiferenciador."</b></font>";
						echo "</td>";
					}
					// 		echo "<td style='text'>";
					//    echo "<font size=2 color=Darkblue><b>".$ctavarprecio."</b></font>";
					// echo "</td>";
					// 		echo "<td style='text'>";
					//    echo "<font size=2 color=Darkblue><b>".$ctavariacionuso."</b></font>";
					echo "</td>";
				    echo "</tr>";

				$descripcion1=strtoupper($descripcion1); 
				$sql = "INSERT INTO stockcategory
					(categoryid, categorydescription, stocktype, stockact, nu_tipo_gasto, purchpricevaract, materialuseagevarac, accountegreso, allowNarrativePOLine, prodLineId, ln_clave)
				VALUES ('$codigocatinv','$descripcion1','$tpoprod','$ctainventario',
					'$ctajusteinv','$ctavarprecio','$ctavariacionuso','$ctatrabproc','$descpedvta','$lineproducto', '$datoDiferenciador')";
				$result = DB_query($sql,$db);
			    }
			}//fin de ELSE revisar lineas
		    }//fin else
		} // Fin del for que recorre cada linea
	    prnMsg("SE CARGOÓ EXITOSAMENTE AL SISTEMA.",'sucess');		    
	    echo "</table>";
	}//fin de cargar archivo
}//fin de mostrar o cargar

echo "<form method='post' enctype='multipart/form-data' action=" . $_SERVER['PHP_SELF'] . '?' . SID . ' name="form">';
  echo "<br>";
  echo "<table width=100% cellpadding=3 class='table table-striped table-bordered'>";
  echo "<tr><br><br><td></td></tr>";
    echo "<tr>";
      echo "<td style='text-align:center;' colspan=2>";
        echo "<font size=2 color=Darkblue><b>"._('CARGA DE CATEGORIAS DE INVENTARIO')."</b></font>";
        echo "<br><br><br><br>";
      echo "</td>";
	echo "</tr>";
	echo "<tr>";
	  echo "<td style='text-align:center;' colspan=2>";
        echo "Formato del archivo a subir";
        echo "<br>";
      echo "</td>";
    echo "</tr>";
    echo "<tr>";
      echo "<td style='text-align:center;' colspan=2>";
        echo '<table cellpadding=2>';
          echo '<tr>';
            echo '<td style="text-align:center;"><font size=1>Codigo de categoria de inventario</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Descripcion</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Tipo producto</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
	    	echo '<td style="text-align:center;"><font size=1>Linea Producto</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Descripcion en pedidos de venta</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Cuenta de Cargo</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Cuenta de Abono</font></td>';
		    echo '<td style="text-align:center;"><font size=1>,</font></td>';
		    echo '<td style="text-align:center;"><font size=1>Tipo de Gasto</font></td>';
		    if ($validarIdentificador == 1) {
		    	echo '<td style="text-align:center;"><font size=1>,</font></td>';
		    	echo '<td style="text-align:center;"><font size=1>Diferenciador</font></td>';
		    }
	    // echo '<td style="text-align:center;"><font size=1>,</font></td>';
	    // echo '<td style="text-align:center;"><font size=1>Cuenta de variaciones de precio</font></td>';
	    // echo '<td style="text-align:center;"><font size=1>,</font></td>';
	    // echo '<td style="text-align:center;"><font size=1>Cuenta de variaciones de uso</font></td>';
	  echo '</tr>';
        echo '</table>';
      echo "</td>";
    echo "</tr>";	
    echo "<tr>";
      echo "<td style='text-align:center;'><br><br>";
        echo "<font size=2 >"._('Caracter Separador')." : </font>&nbsp;";
        echo "<input style='text-align:center;' type='text' size=1 maxlength=1 name='separador' value='".$separador."'>&nbsp;&nbsp;";
        echo "&nbsp;&nbsp;<font size=2>". _('Archivo (.csv o .txt)') . " : </font>&nbsp;";
        echo "<input type='file' name='userfile' size=50 >&nbsp;";        
      echo "</td>";
    echo "</tr>";
    echo"<tr>";
      echo "<td style='text-align:center;' colspan=2>";
        echo "<input type='submit' name='mostrar' value='MOSTRAR INFORMACION' class='btn bgc8' style='color:#fff;'>";
      echo "</td>";
    echo "</tr>";
  echo "</table>";
echo "</form>";
?>