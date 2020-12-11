<?php

include('includes/session.inc');
$title = _('Configura Documentos PDF al Detalle');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$funcion=382;
include('includes/SecurityFunctions.inc');


if (isset($_POST['edodes'])) {
	$edodes= $_POST['edodes'];
} else {
	$edodes="";
}
if (isset($_POST['titulo'])) {
	$titulo= $_POST['titulo'];
}

$num_reg=100;

if (isset($_POST['num_reg'])) {
	$num_reg = $_POST['num_reg'];
}
//esta es la variable con la que nos guiamos para saber ssi es la primera vez que entran
//y para poder mostrar los datos del seleccionado cuando van a modificar
if (isset($_GET['u_confdetail'])) {
	$u_confdetail = $_GET['u_confdetail'];
} elseif (isset($_POST['u_confdetail'])) {
	$u_confdetail = $_POST['u_confdetail'];
}

	if (isset($_POST['edodes']) && $_POST['edodes']<>'' && $_POST['edodes']<1){
		$InputError = 1;
		prnMsg(_('Debe de seleccionar un documento'),'error');
	}
$InputError = 0;
if (isset($_POST['enviar']) || isset($_GET['borrar']) || isset($_POST['modificar']) ) {
	if (isset($_POST['tipo'])) {
		$tipo= $_POST['tipo'];
	} else {
		$tipo=" ";
	}
	if (isset($_POST['tipovalor'])) {
		$tipovalor= $_POST['tipovalor'];
	} else {
		$tipovalor=" ";
	}
	if (isset($_POST['formato'])) {
		$formato= $_POST['formato'];
	} else {
		$formato=" ";
	}
	if (isset($_POST['u_conf']) && $_POST['u_conf']<>'' && $_POST['u_conf']<1){
		$InputError = 1;
		prnMsg(_('Debe de seleccionar un documento'),'error');
	}
	
	if (isset($_POST['valor']) && strlen($_POST['valor'])<1 && $InputError <> 1){
		$InputError = 1;
		prnMsg(_('El Valor debe de contener al menos tener 1 caracteres de longitud'),'error');
	}
	unset($sql);
	if (isset($_POST['modificar'])and ($InputError != 1)) {
		$sql = "UPDATE conf_printdocsdetails SET u_conf='" .$_POST['u_conf']." ' , tipo='".$tipo."' ,
		tipovalor='".$tipovalor."',posvalor='".$_POST['posvalor']."',valor='".$_POST['valor']."', posx='".$_POST['PosX']."', posy='".$_POST['PosY']."',
		longitud='".$_POST['longitud']."',font_size='".$_POST['font_size']."', formato='".$formato."',decimales='".$_POST['decimales']."' where u_confdetail='".$u_confdetail."'";
		$ErrMsg = _('La actualización del Documento fracaso porque');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		
	} elseif (isset($_GET['borrar']) and $_GET['var']=="Deshabilitar" and ($InputError != 1)) {
		$sql="UPDATE conf_printdocsdetails SET activo=0 where u_confdetail='".$u_confdetail."'";
		$result = DB_query($sql,$db,$ErrMsg);
		prnMsg(_('El Documento a sido Deshabilitado ') . '!','info');
	} elseif (isset($_GET['borrar']) and $_GET['var']=="Habilitar" and ($InputError != 1)) {
		$sql="UPDATE conf_printdocsdetails SET activo=1 where u_confdetail='".$u_confdetail."'";
		$result = DB_query($sql,$db,$ErrMsg);
		prnMsg(_('El Documento a sido Habilitado ') . '!','info');
	} elseif (isset($_POST['enviar'])and ($InputError != 1)) {
		$sql= "select count(*) from conf_printdocsdetails where u_conf='".$_POST['u_conf']."' and tipo='".$tipo."' and valor='".$_POST['valor']."'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('No se da de alta el Documento por que ya hay un registro guardado'),'error');
		} else {
			$sql = "INSERT INTO conf_printdocsdetails (u_conf,tipo,tipovalor,posvalor,valor,posx,posy,longitud,font_size,activo,formato,decimales)
			VALUES ('".$_POST['u_conf']."','".$tipo."','".$tipovalor."','".$_POST['posvalor']."','".$_POST['valor']."','".$_POST['PosX']."','".$_POST['PosY']."','".$_POST['longitud']."','".$_POST['font_size']."',1,'".$formato."','".$_POST['decimales']."')";
			prnMsg( _('El Documento').' ' .$_POST[''] . ' ' . _('se ha creado.'),'info');
			$result = DB_query($sql,$db,$ErrMsg);
		}
	}
	unset($tipo);
	unset($tipovalor);
	unset($formato);
	unset($_POST['valor']);
	unset($_POST['PosX']);
	unset($_POST['PosY']);
	unset($_POST['u_conf']);
	unset($_POST['longitud']);
	unset($_POST['posvalor']);
	unset($_POST['font_size']);
	unset($_POST['decimales']);
	unset($_POST['u_confdetail']);
	unset($u_confdetail);
}
if (isset($_POST['Go1'])) {
	$Offset = $_POST['Offset1'];
	$_POST['Go1'] = '';
}

if (!isset($_POST['Offset'])) {
	$_POST['Offset'] = 0;
} else {
	if ($_POST['Offset']==0) {
		$_POST['Offset'] = 0;
	}
}

if (isset($_POST['Next'])) {
	$Offset = $_POST['nextlist'];
}

if (isset($_POST['Prev'])) {
	$Offset = $_POST['previous'];
}

echo "<form method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";

if (!isset($u_confdetail)) {
	echo '<table><tr>';
	echo'<td>' . _('Documento:') . '</td>
	<td><select Name="edodes">';
	$sql = "SELECT * FROM conf_printdocs order by titulo";
	$categoria = DB_query($sql,$db);
	echo '<option VALUE=0 selected> Seleccionar' ;
	while ($myrowcategoria=DB_fetch_array($categoria,$db)){
            $categoria_base=$myrowcategoria['u_conf'];
            if (intval($edodes)==intval($categoria_base)){ 
                echo '<option  VALUE="' . $myrowcategoria['u_conf'] .  '  " selected>' .ucwords(strtolower($myrowcategoria['titulo']));
		$titulo=$myrowcategoria['titulo'];

            }else{
                echo '<option  VALUE="' . $myrowcategoria['u_conf'] .  '" >' .ucwords(strtolower($myrowcategoria['titulo']));
            }
	}
	echo'</tr>';
	
			echo'<td valign=bottom>
			<input type=submit name=buscar value=' . _('Buscar') . '>
		</td>
	</tr></table>';
	echo "<div class='centre'><hr width=50%></div><br>";
	if ($Offset==0) {
		$numfuncion=1;
	} else {
		$numfuncion=$num_reg*$Offset+1;
	}
	$Offsetpagina=1;
//esta parte sirve para mostrar la primera tabla con todos los registros existentes
	$sql = "SELECT *	
		FROM conf_printdocsdetails  as cpd
		left join conf_printdocs as cp
		on cpd.u_conf=cp.u_conf
		WHERE cpd.u_confdetail<>''";
	if (strlen($edodes)>=1) {
	$sql=$sql.' and cpd.u_conf='.$edodes;
	} 
	$result = DB_query($sql,$db);
	//$ListCount=DB_num_rows($result);
	//$ListPageMax=ceil($ListCount/$num_reg);

	$sql = "SELECT *	
		FROM conf_printdocsdetails  as cpd
		left join conf_printdocs as cp
		on cpd.u_conf=cp.u_conf
		WHERE cpd.u_confdetail<>''";
	if (strlen($edodes)>=1) {
	$sql=$sql.' and cpd.u_conf='.$edodes;
	} 
	//$sql = $sql . " LIMIT ".$num_reg." OFFSET ". ($Offset * $num_reg) ;
 
	$result = DB_query($sql,$db);
	//if (!isset($u_confdetail) and $edodes<>'') {
	if(isset($_POST['buscar']))
	{
		if (!isset($u_confdetail)) {
		echo "<div class='centre'>" ._('LISTADO'). "</div>";
		
		echo '<table width=50%>';
		echo '	<tr>';
		if ($ListPageMax >1) {
			if ($Offset==0) {
				$Offsetpagina=1;	
			} else {
				$Offsetpagina=$Offset+1;
		        }
			echo '<td>'.$Offsetpagina. ' ' . _('de') . ' ' . $ListPageMax . ' ' . _('Paginas') . '. ' . _('Ir a la Pagina') . ':';
			echo '<select name="Offset1">';
		        $ListPage=0;
			while($ListPage < $ListPageMax) {	
				if ($ListPage == $Offset) {
					echo '<option VALUE=' . $ListPage . ' selected>' . ($ListPage+1) . '</option>';
				} else {
					echo '<option VALUE=' . $ListPage . '>' . ($ListPage+1) . '</option>';
				}
			$ListPage++;
			$Offsetpagina=$Offsetpagina+1;
			}
			echo '</select></td>
			<td><input type="text" name="num_reg" size=1 value="' .$num_reg. '"></td>
			<td>
				<input type=submit name="Go1" VALUE="' . _('Ir') . '">
			</td>';
			if ($Offset>0){
			echo '<td align=center cellpadding=3 >
				<input type="hidden" name="previous" value='.number_format($Offset-1).'>
				<input tabindex='.number_format($j+7).' type="submit" name="Prev" value="'._('Anterior').'">
			</td>';
			};
			if ($Offset<>$ListPageMax-1) {
			echo'<td style="text-align:right">
				<input type="hidden" name="nextlist" value='.number_format($Offset+1).'>
				<input tabindex='.number_format($j+9).' type="submit" name="Next" value="'._('Siguiente').'">
			</td>';
			}
		}
		echo'</tr>
		</table>';
		
	echo '<table border=1 width=50%>';
	echo "<tr><th colspan='13' >" . _('Documento:') . "".$titulo."</th></tr>";
	echo "<tr><th>" . _('No.') . "</th>
	<th>". _('Tipo') . "</th>
	<th>". _('TipoValor') . "</th>
	<th>". _('Posicion Valor') . "</th>
	<th>". _('Valor') . "</th>
	<th>". _('PosX') . "</th>
	<th>". _('PosY') . "</th>
	<th>". _('Longitud') . "</th>
	<th>". _('Tamaño Letra') . "</th>
	<th>". _('Formato') . "</th>
	<th>". _('Decimales') . "</th>
	<th colspan='2'></th>
	
        </tr>";
	
	$k=0; //row colour counter
	while ($myrow = DB_fetch_array($result)) {
	
	
		if ($myrow['activo']==1){
			$var="Deshabilitar";
		} elseif ($myrow['activo']==0){
			$var="Habilitar";
		}
		
		
			
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		printf("<td style='text-align:center;'>%s</td>
			
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td style='text-align:center;'><a href=\"%s&u_confdetail=%s\">Modificar</a></td>
			<td style='text-align:center;'><a href=\"%s&u_confdetail=%s&borrar=1&var=".$var."&u_conf=%s&activo=%s\">" . $var . "</a></td>
			</tr>",
			
			$numfuncion,
			$myrow[2],
			$myrow['tipovalor'],
			$myrow['posvalor'],
			$myrow['valor'],
			$myrow['posx'],
			$myrow['posy'],
			$myrow['longitud'],
			$myrow['font_size'],
			$myrow['formato'],
			$myrow['decimales'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['u_confdetail'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['u_confdetail'],
			$myrow['u_conf'],
			$myrow['activo']
			);
		$numfuncion=$numfuncion+1;
		
	}
	//}
	echo '</table>';
	}
}
}
if (isset($u_confdetail)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Documentos Existentes') . '</a></div>';
}
//esta parte sirve para mostrar los datos del registro seleccionado para modificar
if (isset($u_confdetail)) {
	$sql = "SELECT *
		FROM conf_printdocsdetails
		WHERE u_confdetail='".$u_confdetail."'" ;
	$result = DB_query($sql, $db);
	if ( DB_num_rows($result) == 0 ) {
		prnMsg( _('No hay registros.'),'warn');
	} else {
		while ($myrow=DB_fetch_array($result,$db)) {
			$_POST['u_confdetail']=$myrow['u_confdetail'];
			$_POST['u_conf']=$myrow['u_conf'];
			$_POST['valor'] = $myrow['valor'];
			$_POST['PosX'] = $myrow['posx'];
			$_POST['PosY'] = $myrow['posy'];
			$_POST['posvalor'] = $myrow['posvalor'];
			$_POST['longitud'] = $myrow['longitud'];
			$_POST['font_size'] = $myrow['font_size'];
			$_POST['decimales'] = $myrow['decimales'];
			if ($myrow['formato']=='numerico') {
				$formato=1;
			} elseif ($myrow['formato']=='fecha') {
				$formato=2;
			} else {
				$formato='';
			} 
			
			if ($myrow['tipovalor']=='Label') {
				$tipovalor=1;
			} elseif ($myrow['tipovalor']=='DB') {
				$tipovalor=2;
			} else {
				$tipovalor='';
			}
			
			if ($myrow[2]=='detalle') {
				$tipo=1;
			} elseif ($myrow[2]=='encabezado') {
				$tipo=2;
			}elseif ($myrow[2]=='pie') {
				$tipo=3;
			} else {
				$tipo='';
			}
		}
	}
}else{
	$tipovalor="";
}
echo '<br>';
if(isset($_POST['u_confdetail'])) {
	echo "<input type=hidden name='u_confdetail' VALUE='" . $_POST['u_confdetail'] . "'>";
}
echo "<div class='centre'><hr width=60%>" ._('ALTA/MODIFICACION DE DOCUMENTOS'). "</div><br>";
	echo '<table>';
	echo '<tr>';
	echo'<td>' . _('Documento:') . '</td>
	<td><select Name="u_conf">';
	$sql = "SELECT * FROM conf_printdocs order by titulo";
	$categoria = DB_query($sql,$db);
	echo '<option VALUE=0 selected> Seleccionar' ;
	while ($myrowcategoria=DB_fetch_array($categoria,$db)){
            $categoria_base=$myrowcategoria['u_conf'];
            if (intval($_POST['u_conf'])==intval($categoria_base)){ 
                echo '<option  VALUE="' . $myrowcategoria['u_conf'] .  '  " selected>' .ucwords(strtolower($myrowcategoria['titulo']));
            }else{
                echo '<option  VALUE="' . $myrowcategoria['u_conf'] .  '" >' .ucwords(strtolower($myrowcategoria['titulo']));
            }
	}
	echo'</tr>';
	echo'<tr><td>' . _('Tipo:').'</td>';
	echo'<td><select Name="tipo">';
	if($tipo==1) {
		echo '<option  VALUE=0>Seleccionar';
		echo '<option  VALUE=detalle selected>Detalle';
		echo '<option  VALUE=encabezado >Encabezado' ;
		echo '<option  VALUE=pie >Pie' ;
	}
	if ($tipo==2) {
		echo '<option  VALUE=0>Seleccionar';
		echo '<option  VALUE=detalle >Detalle';
		echo '<option  VALUE=encabezado selected>Encabezado' ;
		echo '<option  VALUE=pie >Pie' ;
	}
	if ($tipo==3) {
		echo '<option  VALUE=0>Seleccionar';
		echo '<option  VALUE=detalle >Detalle';
		echo '<option  VALUE=encabezado >Encabezado' ;
		echo '<option  VALUE=pie selected>Pie' ;
	}
	if ($tipo=='') {
		echo '<option  VALUE=0 selected>Seleccionar';
		echo '<option  VALUE=detalle >Detalle';
		echo '<option  VALUE=encabezado >Encabezado' ;
		echo '<option  VALUE=pie >Pie' ;
	}
	echo '</select></td></tr>';
	echo'<tr><td>' . _('Tipo Valor:').'</td>';
	echo'<td><select Name="tipovalor">';
	if($tipovalor==1) {
		echo '<option  VALUE=0>Seleccionar';
		echo '<option  VALUE=Label selected>Label';
		echo '<option  VALUE=DB >DB' ;
	}
	if ($tipovalor==2) {
		echo '<option  VALUE=0>Seleccionar';
		echo '<option  VALUE=Label >Label';
		echo '<option  VALUE=DB selected>DB' ;
	}
	if ($tipovalor=='') {
		echo '<option  VALUE=0 selected>Seleccionar';
		echo '<option  VALUE=Label >Label';
		echo '<option  VALUE=DB >DB' ;
	}
	echo '</select></td></tr>';
	echo "<tr><td style='text-align:right;'>" . _('Posicion Valor') . ":</td>";
	echo"<td><input class='number' type='text' name='posvalor' size=12 maxlength=100 VALUE='" .$_POST['posvalor'] . "'></td>";
	echo "</tr>";
	echo "<tr><td>" . _('Valor') . ":</td>";
	echo"<td><input type='text' name='valor' size=40 maxlength=100 VALUE='" .$_POST['valor'] . "'></td>";
	echo "</tr>";
	echo "<tr><td>" . _('PosX') . ":</td>";
	echo"<td><input class='number' type='text' name='PosX' size=12 maxlength=100 VALUE='" .$_POST['PosX'] . "'></td>";
	echo "</tr>";
	echo "<tr><td>" . _('PosY') . ":</td>";
	echo"<td><input class='number' type='text' name='PosY' size=12 maxlength=100 VALUE='" .$_POST['PosY'] . "'></td>";
	echo "</tr>";
	echo "<tr><td>" . _('Longitud') . ":</td>";
	echo"<td><input class='number' type='text' name='longitud' size=12 maxlength=100 VALUE='" .$_POST['longitud'] . "'></td>";
	echo "</tr>";
	echo "<tr><td>" . _('Tamaño Letra') . ":</td>";
	echo"<td><input class='number' type='text' name='font_size' size=12 maxlength=100 VALUE='" .$_POST['font_size'] . "'></td>";
	echo "</tr>";
	echo'<tr><td>' . _('Formato:').'</td>';
	echo'<td><select Name="formato">';
	if($formato==1) {
		echo '<option >';
		echo '<option  VALUE=numerico selected>Numerico';
		echo '<option  VALUE=fecha >Fecha';
	} elseif($formato==2) {
		echo '<option>';
		echo '<option VALUE=numerico>Numerico';
		echo '<option VALUE=fecha selected>Fecha';
	} elseif($formato=='') {
		echo '<option selected>';
		echo '<option VALUE=numerico>Numerico';
		echo '<option VALUE=fecha>Fecha';
	}
	echo '</select></td></tr>';
	echo "<tr><td>" . _('Decimales') . ":</td>";
	echo"<td><input class='number' type='text' name='decimales' size=2 maxlength=20 VALUE='" .$_POST['decimales'] . "'></td>";
	echo "</tr>";
	echo '</table>';	
	//aqui verifica si esta vacia la variable si lo esta el valor de la accion sera enviar 
	if (!isset($u_confdetail)) {
		echo "<div class='centre'><input type='Submit' name='enviar' value='" . _('Enviar') . "'></div>";
	}
	//aqui verifica que este llena la variable si lo esta el valor de la accion sera modificar
	elseif (isset($u_confdetail)) {
		echo "<div class='centre'><input type='Submit' name='modificar' value='" . _('Actualizar') . "'></div>";
	}
echo '</form>';
include('includes/footer_Index.inc');
?>