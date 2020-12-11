<?php

include('includes/session.inc');
$title = _('Configura Documentos PDF');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$funcion=381;
include('includes/SecurityFunctions.inc');


if (isset($_POST['edodes'])) {
	$edodes= $_POST['edodes'];
} else {
	$edodes="";
}	

$num_reg=50;

if (isset($_POST['num_reg'])) {
	$num_reg = $_POST['num_reg'];
}
//esta es la variable con la que nos guiamos para saber ssi es la primera vez que entran
//y para poder mostrar los datos del seleccionado cuando van a modificar
if (isset($_GET['u_conf'])) {
	$u_conf = $_GET['u_conf'];
} elseif (isset($_POST['u_conf'])) {
	$u_conf = $_POST['u_conf'];
}

/*if (isset($edodes) and $edodes!=0) {
	$error=0;
} else {
	$error=1;
	if ($u_conf==''){
		prnMsg(_('Debes de elegir un documento'),'error');
	} else {
		
	}
}*/
if (isset($edodes) and $_POST['edodes']==0 and $edodes!=''){
		$InputError = 1;
		prnMsg(_('Debe de seleccionar un documento'),'error');
	}
$InputError = 0;
if (isset($_POST['enviar']) || isset($_GET['borrar']) || isset($_POST['modificar']) ) {
	
	if (isset($_POST['desline']) && strlen($_POST['desline'])<3){
		$InputError = 1;
		prnMsg(_('El nombre del Grupo por categoria debe ser de al menos 3 caracteres de longitud'),'error');
	}
	unset($sql);
	if (isset($_POST['modificar'])and ($InputError != 1)) {
		$sql = "UPDATE conf_printdocs SET clave='" .$_POST['clave']." ' , titulo='" .$_POST['desline']." ' , sql_='".$_POST['sql']."',
		parametro='".$_POST['parametro']."',sql2_='".$_POST['sql2']."',pos_iniciodetalle='".$_POST['pos_iniciodetalle']."',pos_iniciopie='".$_POST['pos_iniciopie']."',nopagina='".$_POST['nopagina']."' where u_conf='".$u_conf."'";
		$ErrMsg = _('La actualización del Titulo fracaso porque');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		
	} elseif (isset($_GET['borrar']) and ($InputError != 1)) {
		$sql="DELETE FROM conf_printdocs WHERE u_conf='" . $_GET['u_conf']."'";
		$result = DB_query($sql,$db,$ErrMsg);
		prnMsg(_('El Titulo a sido eliminado ') . '!','info');

	} elseif (isset($_POST['enviar'])and ($InputError != 1)) {
		$sql= "select count(*) from conf_printdocs where titulo='".$_POST['desline']."'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('No se da de alta el Titulo por que ya hay un registro guardado'),'error');
		} else {
			$sql = "INSERT INTO conf_printdocs (clave,titulo,sql_,parametro,sql2_,pos_iniciodetalle,pos_iniciopie,nopagina)
			VALUES ('".$_POST['clave']."','".$_POST['desline']."','".$_POST['sql']."','".$_POST['parametro']."','".$_POST['sql2']."','".$_POST['pos_iniciodetalle']."','".$_POST['pos_iniciopie']."','".$_POST['nopagina']."')";
			prnMsg( _('El Titulo').' ' .$_POST['desline'] . ' ' . _('se ha creado.'),'info');
			$result = DB_query($sql,$db,$ErrMsg);
		}
	}
	unset($_POST['desline']);
	unset($_POST['sql']);
	unset($_POST['clave']);
	unset($_POST['parametro']);
	unset($_POST['sql2']);
	unset($_POST['pos_iniciodetalle']);
	unset($_POST['pos_iniciopie']);
	unset($_POST['u_conf']);
	unset($u_conf);
	unset($_POST['nopagina']);
	
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

if (!isset($u_conf) ) {
	echo '<table><tr>';
	echo'<td>' . _('Documento:') . '
	<select Name="edodes">';
	$sql = "SELECT * FROM conf_printdocs order by titulo";
	$categoria = DB_query($sql,$db);
	echo '<option VALUE=0 selected> Seleccionar' ;
	while ($myrowcategoria=DB_fetch_array($categoria,$db)){
            $categoria_base=$myrowcategoria['u_conf'];
            if ($edodes==intval($categoria_base)){ 
                echo '<option  VALUE="' . $myrowcategoria['u_conf'] .  '  " selected>' .ucwords(strtolower($myrowcategoria['clave']) . " - " . strtolower($myrowcategoria['titulo']));
            }else{
                echo '<option  VALUE="' . $myrowcategoria['u_conf'] .  '" >' .ucwords(strtolower($myrowcategoria['clave']) . " - " . strtolower($myrowcategoria['titulo']));
            }
	}
	echo'</td>
		<td valign=bottom>
		<input type=submit name=buscar value=' . _('Buscar') . '>
		</td></tr>';
	
	echo'</table>';
	echo "<div class='centre'><hr width=50%></div><br>";
	if ($Offset==0) {
		$numfuncion=1;
	} else {
		$numfuncion=$num_reg*$Offset+1;
	}
	$Offsetpagina=1;
//esta parte sirve para mostrar la primera tabla con todos los registros existentes
	$sql = "SELECT *	
		FROM conf_printdocs
		where u_conf<>''";
	if (strlen($edodes)>=1) {
	$sql=$sql.' and u_conf='.$edodes;
	} 
	$result = DB_query($sql,$db);
	//$ListCount=DB_num_rows($result);
	//$ListPageMax=ceil($ListCount/$num_reg);

	$sql = "SELECT *	
		FROM conf_printdocs
		where u_conf<>''";
	if (strlen($edodes)>=1) {
	$sql=$sql.' and u_conf='.$edodes;
	} 
	//$sql = $sql . " LIMIT ".$num_reg." OFFSET ". ($Offset * $num_reg) ;
 
	$result = DB_query($sql,$db);
	
	if (!isset($u_conf)) {
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
	}
	if ($edodes!=0 and $InputError!=1){
		echo '<table border=1 width=50%>';
		echo "<tr><th>" . _('No.') . "</th>
		<th>" . _('Clave') . "</th>
		<th>" . _('Titulo') . "</th>
		<th>". _('Pos. Ini. Detalle') . "</th>
		<th>". _('Pos. Ini. Pie') . "</th>
		<th>". _('Num. de Pagina') . "</th>
		<th></th>
		</tr>";
	
		$k=0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {
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
				<td style='text-align:right;'>%s</td>
				<td style='text-align:center;'><a href=\"%s&u_conf=%s\">Modificar</a></td>
				</tr>",
				$numfuncion,
				$myrow['clave'],
				$myrow['titulo'],
				$myrow['pos_iniciodetalle'],
				$myrow['pos_iniciopie'],
				$myrow['nopagina'],
				$_SERVER['PHP_SELF'] . '?' . SID,
				$myrow['u_conf']
				);
			$numfuncion=$numfuncion+1;
		} 
		echo '</table>';
	} 
}
if (isset($u_conf)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Titulos Existentes') . '</a></div>';
}
//esta parte sirve para mostrar los datos del registro seleccionado para modificar
if (isset($u_conf)) {
	$sql = "SELECT u_conf,
			clave,
			titulo,
			sql_,
			parametro,
			sql2_,
			pos_iniciodetalle,
			pos_iniciopie,
			nopagina
		FROM conf_printdocs
		WHERE u_conf='".$u_conf."'" ;
	$result = DB_query($sql, $db);
	if ( DB_num_rows($result) == 0 ) {
		prnMsg( _('No hay registros.'),'warn');
	} else {
		while ($myrow=DB_fetch_array($result,$db)) {
			$_POST['u_conf']=$myrow['u_conf'];
			$_POST['clave']=$myrow['clave'];
			$_POST['desline'] = $myrow['titulo'];
			$_POST['sql'] = $myrow['sql_'];
			$_POST['parametro']=$myrow['parametro'];
			$_POST['pos_iniciodetalle']=$myrow['pos_iniciodetalle'];
			$_POST['pos_iniciopie']=$myrow['pos_iniciopie'];
			$_POST['nopagina']=$myrow['nopagina'];
			$_POST['sql2']=$myrow['sql2_'];
		}
	}
}
echo '<br>';
if(isset($_POST['u_conf'])) {
	echo "<input type=hidden name='u_conf' VALUE='" . $_POST['u_conf'] . "'>";
}
echo "<div class='centre'><hr width=60%>" ._('ALTA/MODIFICACION DE TITULOS'). "</div><br>";
	echo '<table>';
	echo '<tr>';
	echo'<td>' . _('Clave:') . '</td>';
	echo"<td><input type='text' name='clave' size=40 maxlength=100 VALUE='" .$_POST['clave'] . "'></td></tr>";
	echo '<tr>';
	echo'<td>' . _('Titulo:') . '</td>';
	echo"<td><input type='text' name='desline' size=40 maxlength=100 VALUE='" .$_POST['desline'] . "'></td>";
	
	echo "<tr><td>" . _('SQL') . ":</td>";
	$_POST['sql']=$_POST['sql'];
	echo"<td><textarea name='sql' rows='5' cols='50'>".$_POST['sql']."</textarea></td>";
	echo "</tr>";
	echo '<tr>';
	echo'<td>' . _('Parametro:') . '</td>';
	echo"<td><input type='text' name='parametro' size=40 maxlength=100 VALUE='" .$_POST['parametro'] . "'></td></tr>";
	echo "<tr><td>" . _('SQL2') . ":</td>";
	$_POST['sql2']=$_POST['sql2'];
	echo"<td><textarea name='sql2' rows='5' cols='50'>".$_POST['sql2']."</textarea></td>";
	echo "</tr>";
	echo '<tr>';
	echo'<td>' . _('Pos. Ini. Detalle:') . '</td>';
	echo"<td><input class=number type='text' name='pos_iniciodetalle' size=10 maxlength=100 VALUE='" .$_POST['pos_iniciodetalle'] . "'></td></tr>";
	echo'<td>' . _('Pos. Ini. Pie:') . '</td>';
	echo"<td><input class=number type='text' name='pos_iniciopie' size=10 maxlength=100 VALUE='" .$_POST['pos_iniciopie'] . "'></td></tr>";	
	echo'<td>' . _('Número de Paginas:') . '</td>';
	echo"<td><input class=nocopias type='text' name='nopagina' size=10 maxlength=100 VALUE='" .$_POST['nopagina'] . "'></td></tr>";	
	echo '</table>';
	//aqui verifica si esta vacia la variable si lo esta el valor de la accion sera enviar 
	if (!isset($u_conf)) {
		echo "<div class='centre'><input type='Submit' name='enviar' value='" . _('Enviar') . "'></div>";
	}
	//aqui verifica que este llena la variable si lo esta el valor de la accion sera modificar
	elseif (isset($u_conf)) {
		echo "<div class='centre'><input type='Submit' name='modificar' value='" . _('Actualizar') . "'></div>";
	}
echo '</form>';
include('includes/footer_Index.inc');
?>