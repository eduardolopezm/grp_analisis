<?php
//error_reporting(E_ALL);
//error_reporting(-1);
//ini_set('display_errors', 1);
/* 04 - NOV -2014 *****************************
 * Craar ABC de Rubro
 * Reberiano Ramirez**************************/

include('includes/session.inc');
$title = _('Altas, Bajas y Modificaciones de Rubros');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
//include('includes/MiscFunctions.inc');
// $funcion = ;
// include('includes/SecurityFunctions.inc');

// Variable de error
$InputError = 0;

if (isset($_POST['Offset1'])) {
    $Offset=$_POST['Offset1'];
}else{
    $Offset=0;
}
// Variable numero de registros que se muestran
$num_reg = 30;
if (isset ( $_POST ['num_reg'] )) {
	$num_reg = $_POST ['num_reg'];
} else if (isset ( $_GET ['num_reg'] )) {
	$num_reg = $_GET ['num_reg'];
}

// Id de la tabla
$id = null;
if (isset ( $_GET ['id'] )) {
	$id = $_GET ['id'];
} else if (isset ( $_POST ['id'] )) {
	$id = $_POST ['id'];
}

if (isset ( $_GET ['rubro'] )) {
	$rub = $_GET ['rubro'];
} else if (isset ( $_POST ['rubro'] )) {
	$rub = $_POST ['rubro'];
}

// Borrar registro
if (empty ( $_GET ['Delete'] ) == false) {
	if (empty ( $id ) == false) {
		DB_query ( "DELETE FROM g_cat_rubro_ingreso WHERE id_cri = '$id'", $db );
		prnMsg ( _ ( 'Se ha eliminado el registro.' ), 'info' );
		$id = null;
	}
}

// Insertar o actualizar registro
if (isset ( $_POST ['enviar'] ) || isset ( $_POST ['modificar'] )) {
        $rubro_array=$_POST['nombre'];
        $rubro=$rubro_array[0];
        $tipo=$rubro_array[1];
        $clase=$rubro_array[2].$rubro_array[3];
        $concepto=$rubro_array[4].$rubro_array[5];
        $nivel_5=$rubro_array[6];
        $nivel_6=$_POST['n6'];
        $nivel_7=$_POST['n7'];
        $nivel_8=$_POST['n8'];
        $nivel_9=$_POST['n9'];
        $nivel_10=$_POST['n10'];
        $str_descripcion=$_POST['desc'];
        $str_shor_des=$_POST['sdesc'];
        $str_ley=$_POST['ldesc'];
        $str_observaciones=$_POST['observaciones'];
        $str_tiporubro=$_POST['tiporubro'];
        $sntitulo=$_POST ['titulo'];
	$sntiposervicio=$_POST ['tiposerv'];
	$snpresupuestado=$_POST ['pres'];
        $strtiporubro=$_POST ['tiporubro'];
	$idimptoadicional=$_POST ['impto'];
	$idstatus=$_POST ['estatus'];
        $snvalidado=$_POST ['validado'];
        $strdescocncepto=$_POST ['concepto'];
        $activo=$_POST ['activo'];
	if(isset ( $_POST ['modificar'] ))
        {
        $rubro=$_POST ['nombre'];
        $tipo=$_POST ['tipo'];
        $clase=$_POST ['clase'];
        $concepto=$_POST ['conceptor'];
        $nivel_5=$_POST ['n5'];
        }
	if (isset ( $_POST ['nombre'] ) and strlen ( $_POST ['nombre'] ) < 7 and !isset($id)) {
		$InputError = 1;
		prnMsg ( _ ( 'El rubro debe ser de 7 caracteres de longitud' ), 'error' );
	}else if(strlen ( $_POST ['nombre'].$_POST ['tipo'].$_POST ['clase'].$_POST ['concepto'].$_POST ['n5'])<7 and !isset($id)){
                $InputError = 1;
		prnMsg ( _ ( 'El rubro debe ser de 7 caracteres de longitud' ), 'error' );
        }
 //       echo 'Rubro: ['.$_POST ['nombre'].$_POST ['tipo'].$_POST ['clase'].$_POST ['conceptor'].$_POST ['n5']."]";
   //        echo 'Rubro: ['.$_POST ['nombre']."-",$_POST ['tipo']."-",$_POST ['clase']."-",$_POST ['conceptor']."-",$_POST ['n5']."]";
        if ($_POST ['tiporubro']==='*') {
		$InputError = 1;
		prnMsg ( _ ( 'Debe seleccionar un tipo de rubro' ), 'error' );
	}
	
	unset ( $sql );
	
	if (isset ( $_POST ['modificar'] ) and ($InputError != 1)) {
		
		$sql = "UPDATE g_cat_rubro_ingreso SET rubro='".$rubro."', tipo='".$tipo."',  clase='".$clase."' , concepto='".$concepto."', 
                        nivel_5='".$nivel_5."',  nivel_6='".$nivel_6."', nivel_7='".$nivel_7."', nivel_8='".$nivel_8."', nivel_9='".$nivel_9."', nivel_10='".$nivel_10."',
                        str_descripcion='".$str_descripcion."', str_desc_corta='".$str_shor_des."', str_desc_ley='".$str_ley."', str_observaciones='".$str_observaciones."',
                        sn_titulo='".$sntitulo."', id_tipo_servicio='".$sntiposervicio."', sn_presupuestado='".$snpresupuestado."', str_tipo_rubro='".$strtiporubro."',
                        id_impto_adicional='".$idimptoadicional."', id_estatus='".$idstatus."', sn_validado='".$snvalidado."', str_desc_concepto='".$strdescocncepto."',
                        sn_activo='".$activo."' WHERE id_cri = $id";
		$ErrMsg = _ ( 'La actualizacion fracaso porque' );
		prnMsg ( _ ( 'El rubro' ) . ' ' . $_POST ['nombre'] . ' ' . _ ( ' se ha actualizado.' ), 'info' );
	} else if (isset ( $_POST ['enviar'] ) and ($InputError != 1)) {
		
		$sql = "SELECT (CONCAT(rubro,tipo,clase,concepto,nivel_5)) AS rubro FROM g_cat_rubro_ingreso HAVING rubro='{$_POST['nombre']}'";
		$result = DB_query ( $sql, $db );
		$myrow = DB_fetch_row ( $result );
		if ($myrow ['rubro'] > 0) {
			prnMsg ( _ ( 'No se ha dado de alta el rubro por que ya hay un registro guardado con esos datos' ), 'error' );
		} else {
			$sql = "INSERT INTO g_cat_rubro_ingreso (rubro, tipo, clase, concepto, nivel_5, nivel_6, nivel_7, nivel_8, nivel_9, nivel_10, str_descripcion, str_desc_corta, str_desc_ley, str_observaciones, sn_titulo, id_tipo_servicio, sn_presupuestado, str_tipo_rubro, id_impto_adicional, id_estatus, sn_validado, str_desc_concepto, sn_activo)
				VALUES ('".$rubro."', '".$tipo."', '".$clase."', '".$concepto."', '".$nivel_5."', '".$nivel_6."', '".$nivel_7."', '".$nivel_8."', '".$nivel_9."', '".$nivel_10."', '".$str_descripcion."', '".$str_shor_des."', '".$str_ley."', '".$str_observaciones."', '".$sntitulo."', '".$sntiposervicio."', '".$snpresupuestado."', '".$str_tiporubro."', '".$idimptoadicional."', '".$idstatus."', '".$snvalidado."', '".$strdescocncepto."', '".$activo."' )";
			$ErrMsg = _ ( 'La insercion del rubro fracaso porque' );
			prnMsg ( _ ( 'El rubro' ) . ' ' . $rubro.$tipo.$clase.$concepto.$nivel_5. ' ' . _ ( 'se ha creado.' ), 'info' );
		}
                //echo '<br>Ejecucion =D de la consulta:'.$sql;
	}
	
//	unset ( $_POST ['nombre'] );
//	unset ( $_POST ['n6'] );
//	unset ( $_POST ['n7'] );
//        unset ( $_POST ['n8'] );
//        unset ( $_POST ['n9'] );
//        unset ( $_POST ['n10'] );
//        unset ( $_POST ['str_desc'] );
//        unset ( $_POST ['str_short_des'] );
//        unset ( $_POST ['str_ley'] );
//        unset ( $_POST ['observaciones'] );
//        unset ( $_POST ['tipo'] );
//        unset ( $_POST ['titulo'] );
//        unset ( $_POST ['tiposerv'] );
//        unset ( $_POST ['pres'] );
//        unset ( $_POST ['tiporubro'] );
//        unset ( $_POST ['impto'] );
//        unset ( $_POST ['estatus'] );
//        unset ( $_POST ['validado'] );
//        unset ( $_POST ['concepto'] );
//        unset ( $_POST ['activo'] );
//	unset ( $id );
}


if (isset ( $sql ) && $InputError != 1) {
	DB_query ( $sql, $db );
        //echo '<br>Ejecucion de la consulta:'.$sql;
}
               
echo "<form method='post' name='forma' action=" . $_SERVER ['PHP_SELF'] . "?" . SID . ">";

if ((empty( $_GET['id']) and empty($_POST['btnnew'])) OR $_GET['Delete']==1) {
    
	$sql = "SELECT id_cri, (CONCAT(rubro,tipo,clase,concepto,nivel_5)) AS rubro,rubro as rubrono, tipo,clase,concepto
                , nivel_6, nivel_7, nivel_8, nivel_9,nivel_10,
                str_descripcion, str_desc_corta,str_desc_ley, str_observaciones, sn_titulo,
                id_tipo_servicio,sn_presupuestado,str_tipo_rubro, id_impto_adicional,id_estatus,
                 sn_validado, str_desc_concepto, sn_activo
		FROM g_cat_rubro_ingreso WHERE 1=1 ";
        
        if (!empty($_POST['searchrubro'])) {
            $sql.=" HAVING rubro like '".$_POST['searchrubro']."%'";
        }
        if (!empty($_POST['searchtipo'])) {
            $sql.=" AND tipo like '".$_POST['searchtipo']."%'";
        }
        if (!empty($_POST['searchclase'])) {
            $sql.=" AND clase like '".$_POST['searchclase']."%'";
        }
        if (!empty($_POST['searchconcepto'])) {
            $sql.=" AND concepto like '".$_POST['searchconcepto']."%'";
        }
        if (!empty($_POST['searchn5'])) {//
            $sql.=" AND nivel_5 like '".$_POST['searchn5']."%'";
        }
        if (!empty($_POST['searchn6'])) {
            $sql.=" AND nivel_6 like '".$_POST['searchn6']."%'";
        }
        if (!empty($_POST['searchn6'])) {
            $sql.=" AND nivel_6 like '".$_POST['searchn6']."%'";
        }
        if (!empty($_POST['searchn7'])) {
            $sql.=" AND nivel_7 like '".$_POST['searchn7']."%'";
        }
        if (!empty($_POST['searchn8'])) {
            $sql.=" AND nivel_8 like '".$_POST['searchn8']."'";
        }
        if (!empty($_POST['searchn9'])) {
            $sql.=" AND nivel_9 like '".$_POST['searchn9']."%'";
        }
        if (!empty($_POST['searchn10'])) {
            $sql.=" AND nivel_10 like '".$_POST['searchn10']."%'";
        }
        if (!empty($_POST['searchdesc'])) {
            $sql.=" AND str_descripcion like '%".$_POST['searchdesc']."%'";
        }
        $sql.=" AND id_cri<>'0'  ORDER BY id_cri DESC LIMIT " . $num_reg . " OFFSET " . ($Offset * $num_reg);
	//echo '<pre>'.$sql; 
        
        $Offsetpagina = 1;
	
	$sqlcount = "SELECT COUNT(id_cri) FROM g_cat_rubro_ingreso";
      
	$resultcount = DB_query ( $sqlcount, $db );
	$ListCount = 0;
	if ($row = DB_fetch_row ( $resultcount )) {
		$ListCount = $row [0];
	}
	
//	$ListPageMax = ceil ( $ListCount / $num_reg );

	$result = DB_query ( $sql, $db );
	
	if (! isset ( $id )) {
		
		echo "<table border=0 style='text-align:center;margin-left:auto;margin-right:auto;' border='0' nowrap> ";
		echo '<tr>';
		echo '<td align="center" colspan=2 class="texto_lista">';
		echo '<p align="center">';
		echo '<img src="images/imgs/configuracion.png" height=25" width="25" title="' . _ ( 'Altas, Bajas y Modificaciones de Rubros' ) . '" alt="">' . ' ' . $title . '<br />';
		echo '</td>';
		echo '</tr>';
		echo '</table>';
//Seccion de busqueda                
echo '<div width="70%" style="text-align:center;margin-left:auto;margin-right:auto;">';
        echo '<fieldset>';
        echo '<legend>Opciones de buqueda:</legend>';
        echo '<table width="" border=0 style="text-align:center;margin-left:auto;margin-right:auto;border-color:lightgray;">';
        echo '<tr>';
            echo '<th align="center" width="" colspan=5 style="text-align:center;"></th>';
            echo '<th align="center" width="" colspan=5 style="text-align:center;">Nivel</th>';
        echo '</tr>';
        echo '<tr>';
            echo '<td align="center" width="" colspan=1 style="text-align:center;">'.("Rubro").'</td>';
            echo '<td align="center" width="" colspan=1 style="text-align:center;">'.("Tipo").'</td>';
            echo '<td align="center" width="" colspan=1 style="text-align:center;">'.("Clase").'</td>';
            echo '<td align="center" width="" colspan=1 style="text-align:center;">'.("Concepto").'</td>';
            echo '<td align="center" width="" colspan=1 style="text-align:center;">'.("Niv 5").'</td>';
            
            echo '<td align="center" width="" colspan=1 style="text-align:center;">6</td>';
            echo '<td align="center" width="" colspan=1 style="text-align:center;">7</td>';
            echo '<td align="center" width="" colspan=1 style="text-align:center;">8</td>';
            echo '<td align="center" width="" colspan=1 style="text-align:center;">9</td>';
            echo '<td align="center" width="" colspan=1 style="text-align:center;">10</td>';
        echo '</tr>';
        echo '<tr>';
            echo '<td align="center" width="" colspan=1 style="text-align:center;"><input class="number" type="text" name="searchrubro" maxlength=6 size=6 value="'.$_POST['searchrubro'].'"></td>';
            echo '<td align="center" width="" colspan=1 style="text-align:center;"><input class="number" type="text" name="searchtipo" maxlength=1 size=1 value="'.$_POST['searchtipo'].'"></td>';
            echo '<td align="center" width="" colspan=1 style="text-align:center;"><input class="number" type="text" name="searchclase" maxlength=2 size=2 value="'.$_POST['searchclase'].'"></td>';
            echo '<td align="center" width="" colspan=1 style="text-align:center;"><input class="number" type="text" name="searchconcepto" maxlength=2 size=2 value="'.$_POST['searchconcepto'].'"></td>';
            echo '<td align="center" width="" colspan=1 style="text-align:center;"><input class="number" type="text" name="searchn5" maxlength=2 size=2 value="'.$_POST['searchn5'].'"></td>';
            
            echo '<td align="center" width="" colspan=1 style="text-align:center;"><input class="number" type="text" name="searchn6" maxlength=2 size=2 size="5" value="'.$_POST['searchn6'].'"></td>';
            echo '<td align="center" width="" colspan=1 style="text-align:center;"><input class="number" type="text" name="searchn7" maxlength=2 size=2 size="5" value="'.$_POST['searchn7'].'"></td>';
            echo '<td align="center" width="" colspan=1 style="text-align:center;"><input class="number" type="text" name="searchn8" maxlength=2 size=2 size="5" value="'.$_POST['searchn8'].'"></td>';
            echo '<td align="center" width="" colspan=1 style="text-align:center;"><input class="number" type="text" name="searchn9" maxlength=2 size=2 size="5" value="'.$_POST['searchn9'].'"></td>';
            echo '<td align="center" width="" colspan=1 style="text-align:center;"><input class="number" type="text" name="searchn10" maxlength=2 size=2 size="5" value="'.$_POST['searchn10'].'"></td>';
        echo '</tr>';
        echo '<tr>';
            echo '<td align="left" width="" colspan=9 style="text-align:left;">'.("Descripcion").'</td>';
        echo '</tr>';
        echo '<tr>';
            echo '<td align="left" width="" colspan=4 style="text-align:left;"><input type="text" name="searchdesc" maxlength=45 size=45 value="'.$_POST['searchdesc'].'"></td>';
            echo '<td align="left" width="" colspan=5 style="text-align:left;"><input type="submit" name="btnsearch" value="'._('Busqueda').'">';
            echo '<input type="submit" name="btnnew" value="'._('Nuevo Rubro').'"></td>';
        echo '</tr>';
        
        echo '</table>';
        echo '</fieldset>';
 echo '</div>';
                
		echo '<table align="center" style="width:500px">';
		echo '<tr>';
		
		if ($ListPageMax >= 0) {
			if ($Offset == 0) {
				$Offsetpagina = 1;
			} else {
				$Offsetpagina = $Offset + 1;
			}
			echo '<td>' . $Offsetpagina . ' ' . _ ( 'de' ) . ' ' . $ListPageMax . ' ' . _ ( 'Paginas' ) . '. ' . _ ( 'Ir a la Pagina' ) . ':';
			echo '<select name="Offset1">';
			$ListPage = 0;
			while ( $ListPage < $ListPageMax ) {
				if ($ListPage == $Offset) {
					echo '<option VALUE=' . $ListPage . ' selected>' . ($ListPage + 1) . '</option>';
				} else {
					echo '<option VALUE=' . $ListPage . '>' . ($ListPage + 1) . '</option>';
				}
				$ListPage ++;
				$Offsetpagina = $Offsetpagina + 1;
			}
			echo '</select></td>
				<td><input type="text" name="num_reg" size=1 value="' . $num_reg . '"></td>
				<td>
				<input type=submit name="Go1" VALUE="' . _ ( 'Ver Lineas' ) . '">
				</td>';
			
			if ($Offset > 0) {
				echo '<td align=center cellpadding=3 >
					<input type="hidden" name="previous" value=' . number_format ( $Offset - 1 ) . '>';
                                //echo '<input tabindex=' . number_format ( $j + 7 ) . ' type="submit" name="Prev" value="' . _ ( 'Anterior' ) . '">
	                echo '</td>';
			}
			;
			if ($Offset != $ListPageMax - 1) {
				echo '<td style="text-align:right">
					<input type="hidden" name="nextlist" value=' . number_format ( $Offset + 1 ) . '>';
				//echo '<input tabindex=' . number_format ( $j + 9 ) . ' type="submit" name="Next" value="' . _ ( 'Siguiente' ) . '">
	                echo '</td>';
			}
		}
		
		echo "</tr>";
		echo "</table>";
	}
        
	echo '<table width="100%" cellspacing="0" border="1" bordercolor="lightgray" cellpadding="0" colspan="0" style="margin-top:0">';
	
	echo "<tr>";
	echo "<td colspan='23' class='titulo_azul'>";
	echo _ ( "Listado de Rubros" );
	echo "</td>";
	echo "</tr>";
	
	echo "<tr>";
	echo '<td></td>';
	// Columna id
	echo "<td class='titulos_principales'>";
	echo _ ( 'ID' );
	echo "</td>";
	
	// Columna Rubro
	echo "<td class='titulos_principales'>";
	echo _ ( 'Rubro' );
	echo "</td>";
//        
//        // Columna Tipo
//	echo "<td class='titulos_principales'>";
//	echo _ ( 'Tipo' );
//	echo "</td>";
//        
//        // Columna clase
//	echo "<td class='titulos_principales'>";
//	echo _ ( 'clase' );
//	echo "</td>";
//        
//        // Columna concepto
//	echo "<td class='titulos_principales'>";
//	echo _ ( 'Concepto' );
//	echo "</td>";
//        
//        // Columna nivel 5
//	echo "<td class='titulos_principales'>";
//	echo _ ( 'Nivel 5' );
//	echo "</td>";
        
        // Columna nivel 6
	echo "<td class='titulos_principales'>";
	echo _ ( 'Nivel 6' );
	echo "</td>";
        
        // Columna nivel 7
	echo "<td class='titulos_principales'>";
	echo _ ( 'Nivel 7' );
	echo "</td>";
        
        // Columna nivel 8
	echo "<td class='titulos_principales'>";
	echo _ ( 'Nivel 8' );
	echo "</td>";
        
        // Columna nivel 9
	echo "<td class='titulos_principales'>";
	echo _ ( 'Nivel 9' );
	echo "</td>";
        
        // Columna nivel 10
	echo "<td class='titulos_principales'>";
	echo _ ( 'Nivel 10' );
	echo "</td>";
        
        // Columna descripcion
	echo "<td class='titulos_principales'>";
	echo _ ( 'Descripcion' );
	echo "</td>";
	
        echo "<td class='titulos_principales'>";
	echo _ ( 'Descripcion corta' );
	echo "</td>";
        
        echo "<td class='titulos_principales'>";
	echo _ ( 'Ley' );
	echo "</td>";
        
        echo "<td class='titulos_principales'>";
	echo _ ( 'Observaciones' );
	echo "</td>";
        
        echo "<td class='titulos_principales'>";
	echo _ ( 'Titulo' );
	echo "</td>";
        
        echo "<td class='titulos_principales'>";
	echo _ ( 'Tipo de servicio' );
	echo "</td>";
        
        echo "<td class='titulos_principales'>";
	echo _ ( 'Presupuestado' );
	echo "</td>";
        
        echo "<td class='titulos_principales'>";
	echo _ ( 'Tipo de rubro' );
	echo "</td>";
        
        echo "<td class='titulos_principales'>";
	echo _ ( 'Impuesto adicional' );
	echo "</td>";
        
        echo "<td class='titulos_principales'>";
	echo _ ( 'Validado' );
	echo "</td>";
        
	echo "<td class='titulos_principales'>";
	echo _ ( 'Descripcion de concepto' );
	echo "</td>";
        
        echo "<td class='titulos_principales'>";
	echo _ ( 'Estatus' );
	echo "</td>";
        
        echo "<td class='titulos_principales'>";
	echo _ ( 'Activo' );
	echo "</td>";
	// Columna modificar
	echo "<td class='titulos_principales'>";
	echo "</td>";
	
	// Columna borrar
	echo "<td class='titulos_principales'>";
	echo "</td>";
	
	echo "</tr>";
	
	$k = 0; // row colour counter
	$regno=0;
	while ( $myrow = DB_fetch_array ( $result ) ) {
		
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		echo '<td style="background-color:white;" >'.++$regno.'</td>';
		if ($myrow ['sn_activo'] == 1) {
			$myrow ['sn_activo'] = 'Si';
		} else {
			$myrow ['sn_activo'] = 'No';
		}
                
                if ($myrow ['sn_presupuestado'] == 1) {
			$myrow ['sn_presupuestado'] = 'Si';
		} else {
			$myrow ['sn_presupuestado'] = 'No';
		}
                
                if ($myrow ['id_estatus'] == 1) {
			$myrow ['id_estatus'] = 'Si';
		} else {
			$myrow ['id_estatus'] = 'No';
		}
                
                if ($myrow ['sn_validado'] == 1) {
			$myrow ['sn_validado'] = 'Si';
		} else {
			$myrow ['sn_validado'] = 'No';
		}
                
		
            printf ( "
            <td class='numero_normal'>%s</td>
            <td class='texto_normal'>%s</td>
            <td class='texto_normal'>%s</td>
            <td class='texto_normal'>%s</td>
            <td class='texto_normal'>%s</td>
            <td class='texto_normal'>%s</td>
            <td class='texto_normal'>%s</td>
            <td class='texto_normal' style='text-align:left'>%s</td>
            <td class='texto_normal' style='text-align:left'>%s</td>
            <td class='texto_normal' style='text-align:left'>%s</td>
            <td class='texto_normal'>%s</td>
            <td class='texto_normal'>%s</td>
            <td class='texto_normal'>%s</td>
            <td class='texto_normal'>%s</td>
            <td class='texto_normal'>%s</td>
            <td class='texto_normal'>%s</td>
            <td class='texto_normal'>%s</td>
            <td class='texto_normal'>%s</td>
            <td class='texto_normal'>%s</td>
            <td class='texto_normal'>%s</td>
            <td class='numero_normal'><a href=\"%s&id=%s&Offset=%s&num_reg=%s\">" . _ ( "Modificar" ) . "</a></td>
            <td class='numero_normal'><a onclick='borrar(this.href); return false;' href=\"%s&id=%s&Offset=%s&num_reg=%s&Delete=1\">" . _ ( "Eliminar" ) . "</a></td>
            </tr>", $myrow ['id_cri'], $myrow ['rubro'], 
                    $myrow ['nivel_6'], $myrow ['nivel_7'], $myrow ['nivel_8'], 
                    $myrow ['nivel_9'], $myrow ['nivel_10'], $myrow ['str_descripcion'], 
                    $myrow ['str_desc_corta'], $myrow ['str_desc_ley'], $myrow ['str_observaciones'], 
                    $myrow ['sn_titulo'], $myrow ['id_tipo_servicio'],  
                    $myrow ['sn_presupuestado'], $myrow ['str_tipo_rubro'], $myrow ['id_impto_adicional'], 
                    $myrow ['sn_validado'], $myrow ['str_desc_concepto'],$myrow ['id_estatus'],$myrow ['sn_activo'],
                    $_SERVER ['PHP_SELF'] . '?' . SID, $myrow ['id_cri']."&rubro=".$myrow ['rubro'], $Offset, $num_reg,
                    $_SERVER ['PHP_SELF'] . '?' . SID, $myrow ['id_cri']."&rubro=".$myrow ['rubro'], $Offset, $num_reg );
	}
	
	echo "</table>";
}

if (isset ( $id ) or isset($_POST['btnnew'])) {
	echo "<div class='centre'><a href='" . $_SERVER ['PHP_SELF'] . "?" . SID . "'>" . _ ( 'Rubros Existentes' ) . '</a></div>';
}

// Esta parte sirve para mostrar los datos del registro seleccionado para modificar
if (isset ( $id ) and strlen ( $id ) > 0) {
	
	$sql = "SELECT id_cri, (CONCAT(rubro,tipo,clase,concepto,nivel_5)) AS rubro
                , nivel_6, nivel_7, nivel_8, nivel_9,nivel_10,
                str_descripcion, str_desc_corta,str_desc_ley, str_observaciones, sn_titulo,
                id_tipo_servicio,sn_presupuestado,str_tipo_rubro, id_impto_adicional,id_estatus,
                 sn_validado, str_desc_concepto, sn_activo
		FROM g_cat_rubro_ingreso WHERE id_cri<>'0' and id_cri='".$_GET['id']."' HAVING rubro='".$_GET['rubro']."' ORDER BY id_cri";
	//echo "<br>".$sql;
	$result = DB_query ( $sql, $db );
        
	if (DB_num_rows ( $result ) == 0) {
		prnMsg ( _ ( 'No hay registros.' ), 'warn' );
	} else {
		$myrow = DB_fetch_array ( $result );
                $rub=$myrow ['rubro'];
		$_POST ['id'] = $myrow ['id_cri'];
		$_POST ['nombre'] = $rub[0];
                $_POST ['tipo'] = $rub[1];
                $_POST ['clase'] = $rub[2].$rub[3];
                $_POST ['conceptor'] = $rub[4].$rub[5];
                $_POST ['n5'] = $rub[6];
                $_POST ['n6'] = $myrow ['nivel_6'];
		$_POST ['n7'] = $myrow ['nivel_7'];
		$_POST ['n8'] = $myrow ['nivel_8'];
                $_POST ['n9'] = $myrow ['nivel_9'];
		$_POST ['n10'] = $myrow ['nivel_10'];
		$_POST ['desc'] = $myrow ['str_descripcion'];
                $_POST ['sdesc'] = $myrow ['str_desc_corta'];
		$_POST ['ldesc'] = $myrow ['str_desc_ley'];
		$_POST ['observaciones'] = $myrow ['str_observaciones'];
                $_POST ['titulo'] = $myrow ['sn_titulo'];
		$_POST ['tiposerv'] = $myrow ['id_tipo_servicio'];
		$_POST ['pres'] = $myrow ['sn_presupuestado'];
                $_POST ['tiporubro'] = $myrow ['str_tipo_rubro'];
		$_POST ['impto'] = $myrow ['id_impto_adicional'];
		$_POST ['estatus'] = $myrow ['id_estatus'];
                $_POST ['validado'] = $myrow ['sn_validado'];
		$_POST ['concepto'] = $myrow ['str_desc_concepto'];
		$_POST ['activo'] = $myrow ['sn_activo'];
	}
}

echo '<br />';
if (isset ( $_POST ['id'] )) {
	echo "<input type='hidden' name='id' value='" . $_POST ['id'] . "'>";
        echo "<input type='hidden' name='rubro' value='" . $_POST ['rubro'] . "'>";
	echo "<input type='hidden' name='Offset' value='" . $_POST ['Offset'] . "'>";
	echo "<input type='hidden' name='num_reg' value='" . $num_reg . "'>";
}

if (isset($_POST['Next'])) {
    $_POST['Offset1']+=1;
}
if (isset($_POST['Prev'])) {
    $_POST['Offset1']-=1;
}
if (isset($_POST['btnnew']) or isset($_GET['id'])) {
        echo "<div class='texto_status'>" . _ ( 'Alta/Modificacion de Rubros' ) . "</div><br />";
        echo "<table style='text-align:center; margin:0 auto;'>";

        // Campo nombre rubro
        echo "<tr>";
        if (isset ( $id ) and strlen ( $id ) > 0) {
            echo "<td class='texto_lista'>" . _ ( 'Rubro: ' ) . "<input type='text' class='number' name='nombre' size='2' maxlength='1' value='" . $_POST ['nombre'] . "'></td>";

            echo "<td class='texto_lista'>" . _ ( 'Tipo: ' ) . "<input type='text' name='tipo' size='2' maxlength='1' value='" . $_POST ['tipo'] . "'></td>";

            echo "<td class='texto_lista'>" . _ ( 'Clase: ' ) . "<input type='text' name='clase' size='2' maxlength='2' value='" . $_POST ['clase'] . "'></td>";

            echo "<td class='texto_lista'>" . _ ( 'Concepto: ' ) . "<input type='text' name='conceptor' size='2' maxlength='2' value='" . $_POST ['conceptor'] . "'></td>";

            echo "<td class='texto_lista'>" . _ ( 'Nivel 5: ' ) . "<input type='text' name='n5' size='2' maxlength='1' value='" . $_POST ['n5'] . "'></td>";
        }else{
            echo "<td class='texto_lista'>" . _ ( 'Rubro: ' ) . "</td><td><input type='text' class='number' name='nombre' size='7' maxlength='7' value='" . $_POST ['nombre'] . "'></td>";
        }
        echo "</tr>";

        echo "<tr>";
        echo "<td class='' colspan=5>" . _ ( 'Nivel 6: ' ) . "<input type='text' name='n6' size='2' maxlength='2' value='" . $_POST ['n6'] . "'>"
         ._ ( 'Nivel 7: ' ) . "<input type='text' name='n7' size='2' maxlength='2' value='" . $_POST ['n7'] . "'>"
        ._ ( 'Nivel 8: ' ) . "<input type='text' name='n8' size='2' maxlength='2' value='" . $_POST ['n8'] . "'>"
        ._ ( 'Nivel 9: ' ) . "<input type='text' name='n9' size='2' maxlength='2' value='" . $_POST ['n9'] . "'>"
        ._ ( 'Nivel 10: ' ) . "<input type='text' name='n10' size='2' maxlength='2' value='" . $_POST ['n10'] . "'></td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td class='texto_lista'>" . _ ( 'Descripcion: ' ) . "</td>";
        echo "<td><input type='text' name='desc' size='40' maxlength='100' value='" . $_POST ['desc'] . "'></td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td class='texto_lista'>" . _ ( 'Descripcion corta: ' ) . "</td>";
        echo "<td><input type='text' name='sdesc' size='40' maxlength='100' value='" . $_POST ['sdesc'] . "'></td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td class='texto_lista'>" . _ ( 'Ley: ' ) . "</td>";
        echo "<td><input type='text' name='ldesc' size='40' maxlength='100' value='" . $_POST ['ldesc'] . "'></td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td class='texto_lista'>" . _ ( 'Observaciones : ' ) . "</td>";
        echo "<td><input type='text' name='observaciones' size='40' maxlength='100' value='" . $_POST ['observaciones'] . "'></td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td class='texto_lista'>" . _ ( 'Titulo: ' ) . "</td>";
        echo "<td><input type='text' class='number' name='titulo' size='40' maxlength='100' value='" . $_POST ['titulo'] . "'></td>";
        echo "</tr>";
        $selec='';
        echo "<tr>";
        echo "<td class='texto_lista'>" . _ ( 'Tipo rubro: ' ) . "</td>";
        echo "<td><select name='tiporubro'>";
            if (!isset($_POST['tiporubro'])) {
                echo '<option  selected  value="*">Seleccione una opcion</option>';
            }else{
                echo '<option value="*">Seleccione una opcion</option>';
            }

            if ($_POST['tiporubro']==='ESTATAL') {
                echo '<option  selected  value="ESTATAL">Estatal</option>';
            }else{
                echo '<option   value="ESTATAL">Estatal</option>';
            }

            if ($_POST['tiporubro']==='FEDERAL') {
                echo '<option selected value="FEDERAL">Federal</option>';
            }else{
                echo '<option   value="FEDERAL">Federal</option>';
            }
        //echo "<td><input type='text' name='tiporubro' size='40' maxlength='100' value='" . $_POST ['tiporubro'] . "'></td>";
        echo "</select></td></tr>";

        echo "<tr>";
        echo "<td class='texto_lista'>" . _ ( 'Presupuestado: ' ) . "</td><td>";
        if($_POST['pres']==1){echo '<input name="pres" type="checkbox" checked="checked" value="1"/>';}
        else{ echo '<input name="pres" type="checkbox" value="1"/>';};
        //echo "<td><input type='text' name='pres' size='40' maxlength='100' value='" . $_POST ['pres'] . "'></td>";
        echo "</td></tr>";

        echo "<tr>";
        echo "<td class='texto_lista'>" . _ ( 'Tipo Servicio: ' ) . "</td>";
        echo "<td><input type='text' class='number'  name='tiposerv' size='40' maxlength='100' value='" . $_POST ['tiposerv'] . "'></td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td class='texto_lista'>" . _ ( 'Impuesto: ' ) . "</td>";
        echo "<td><input type='text' class='number' name='impto' size='40' maxlength='100' value='" . $_POST ['impto'] . "'></td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td class='texto_lista'>" . _ ( 'Estatus: ' ) . "</td><td>";
        if($_POST['estatus']==1){echo '<input name="estatus" type="checkbox" checked="checked" value="1"/>';}
        else{ echo '<input name="estatus" type="checkbox" value="1"/>';}
        //echo "<td><input type='text' name='estatus' size='40' maxlength='100' value='" . $_POST ['estatus'] . "'></td>";
        echo "</td></tr>";

        echo "<tr>";
        echo "<td class='texto_lista'>" . _ ( 'Validado: ' ) . "</td><td>";
        if($_POST['validado']==1){echo '<input name="validado" type="checkbox" checked="checked" value="1"/>';}
        else{ echo '<input name="validado" type="checkbox" value="1"/>';}
        //echo "<td><input type='text' name='validado' size='40' maxlength='100' value='" . $_POST ['validado'] . "'></td>";
        echo "</td></tr>";

        echo "<tr>";
        echo "<td class='texto_lista'>" . _ ( 'Concepto: ' ) . "</td>";
        echo "<td><input type='text' name='concepto' size='40' maxlength='100' value='" . $_POST ['concepto'] . "'></td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td class='texto_lista'>" . _ ( 'Activo: ' ) . "</td><td>";
        if($_POST['activo']==1){echo '<input name="activo" type="checkbox" checked="checked" value="1"/>';}
        else{ echo '<input name="activo" type="checkbox" value="1"/>';};
        //echo "<td><input type='text' name='activo' size='40' maxlength='100' value='" . $_POST ['activo'] . "'></td>";
        echo "</td></tr>";


        echo "<td colspan='2'>";
        if (! isset ( $id )) {

                echo "<div class='centre'><input type='Submit' name='enviar' value='" . _ ( 'Enviar' ) . "'></div>";
        } else if (isset ( $id )) {

                echo "<div class='centre'><input type='Submit' name='modificar' value='" . _ ( 'Actualizar' ) . "'></div>";
        }
        echo "</td>";
        echo "</tr>";
        echo "</table>";
    }    
echo "</form>";
?>
<script type="text/javascript">
function borrar(href) {
	if(confirm("Desea borrar el registro?")) {
		window.location = href;
	}
	return false;
}
</script>
<?php
include ('includes/footer.inc');
?>
