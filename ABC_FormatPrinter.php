<?php 

include('includes/session.inc');
$title = _('Altas, Bajas y Modificaciones de Consultas');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$funcion=386;
include('includes/SecurityFunctions.inc');


if (isset($_POST['edodes'])) {
	$edodes= $_POST['edodes'];
} else {
	$edodes="";
}	

//$num_reg=50;

if (isset($_POST['num_reg'])) {
	$num_reg = $_POST['num_reg'];
}
//esta es la variable con la que nos guiamos para saber si es la primera vez que entran
//y para poder mostrar los datos del seleccionado cuando van a modificar
if (isset($_GET['Prodlineid'])) {
	$Prodlineid = trim($_GET['Prodlineid']);
} elseif (isset($_POST['Prodlineid'])) {
	$Prodlineid = trim($_POST['Prodlineid']);
}

/*if (isset($_GET['typeidi'])) {
	$typeidi = trim($_GET['typeidi']);
} elseif (isset($_POST['typeid'])) {
	$typeidi = trim($_POST['typeidi']);
}*/

//esta es la variable que guarda el error
$InputError = 0;
// Verifica si el usuario ya dio click en el boton de Enviar o en el link de modificar o borrar registro
if (isset($_POST['enviar']) || isset($_GET['borrar']) || isset($_POST['modificar']) ) {
		if (isset($_POST['']) && strlen($_POST['desline'])<3){
		$InputError = 1;
		prnMsg(_('El nombre del formato debe ser de al menos 3 caracteres de longitud'),'error');
		}
	unset($sql);
//aqui empieza a realizar las acciones que corresponda de acuerdo al boton o link seleccionado
	if (isset($_POST['modificar'])and ($InputError != 1)) {
		
		$sql = "UPDATE conf_printdocs SET clave='" .$_POST['clave']."',
		titulo='" .$_POST['titulo']."',
		pos_iniciodetalle=" .$_POST['posini'].",
		tipo=" .$_POST['tipo'].",
		typeid=" .$_POST['formato']."
		where u_conf=".$Prodlineid;
		$result = DB_query($sql,$db);
		
		$url=$_POST['url'].$_POST['clave'];
		$total=$_POST['total'];
		for ($x=0;$x<$total;$x++) {
			if ($_POST['unineg'.$x]==true) {
				$tag=$_POST['tagref'.$x];
				$SQL="update sysDocumentIndex set typeabbrev='".$url."'
				where typeid='".$_POST['formato']."'
				and tagref=".$tag;
				$resultag = DB_query($SQL,$db);
			}
		}
		$ErrMsg = _('La actualización de la Matriz fracaso porque');
		prnMsg( _('EL formato').' ' .$_POST['titulo'] . ' ' . _(' se ha actualizado.'),'info');
	} /*elseif (isset($_GET['borrar'])and ($InputError != 1)) {
		$sql="select * from sysDocumentIndex where url=";
			$sql="DELETE FROM conf_printdocs WHERE u_conf='" . $_GET['Prodlineid']."'";
			$result = DB_query($sql,$db);
			$sql="DELETE FROM conf_printdocsdetails where u_conf='" . $_GET['Prodlineid']."'";
			$result = DB_query($sql,$db);
			prnMsg(_('El formato a sido eliminado ') . '!','info');	
	}*/ elseif (isset($_POST['enviar'])and ($InputError != 1)) {
			$sql= "select count(*) from conf_printdocs where clave='".$_POST['clave']."'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('No se da de alta el formato por que ya hay una clave guardada'),'error');
		} else {
			$confID = DB_Last_Insert_ID($db,'conf_printdocs','u_conf');
			$sql="insert conf_printdocs
				select ".$confID.",
					'".$_POST['clave']."',
					'".$_POST['titulo']."',sql_,parametro,sql2_,
					'".$_POST['posini']."',
					".$_POST['tipo'].",
					".$_POST['formato']."
				from conf_consulta where typeid=".$_POST['formato'];
			$result = DB_query($sql,$db);
			
			$sql="select tipo,tipovalor,posvalor,valor,
				posx,posy,longitud,font_size,
				activo,formato,decimales
				from conf_consultadetails
				where typeid=".$_POST['formato'];
			$conde= DB_query($sql,$db);
			$confID = DB_Last_Insert_ID($db,'conf_printdocs','u_conf');
			while ($myrowg=DB_fetch_array($conde,$db)){
				$sql="insert into conf_printdocsdetails(u_conf,tipo,tipovalor,
				posvalor,valor,posx,posy,longitud,font_size,activo,formato,decimales)";
				$sql=$sql." values('".$confID."','".$myrowg['tipo']."','".$myrowg['tipovalor']."',
				'".$myrowg['posvalor']."','".$myrowg['valor']."','".$myrowg['posx']."',
				'".$myrowg['posy']."','".$myrowg['longitud']."','".$myrowg['font_size']."',
				'".$myrowg['activo']."','".$myrowg['formato']."','".$myrowg['decimales']."')";
				$resultdetail= DB_query($sql,$db);
			}
			
			$url=$_POST['url'].$_POST['clave'];
			$total=$_POST['total'];
			for ($x=0;$x<$total;$x++) {
				if ($_POST['unineg'.$x]==true) {
					$tag=$_POST['tagref'.$x];
					$sql="update sysDocumentIndex set typeabbrev='".$url."'
					where typeid='".$_POST['formato']."'
					and tagref=".$tag;
					$result = DB_query($sql,$db);
				}
			}
		}
	}
//aqui se inicializan las variables en vacio
	unset($_POST['clave']);
	unset($_POST['titulo']);
	unset($_POST['sql_']);
	unset($_POST['parametro']);
	unset($_POST['sql2_']);
	unset($_POST['posini']);
	unset($_POST['tipo']);
	unset($_POST['formato']);
	unset($_POST['typeid']);
	unset($_POST['Prodlineid']);
	unset($Prodlineid);	
}

if (isset($sql) && $InputError != 1 ) {
	$result = DB_query($sql,$db,$ErrMsg);
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

echo "<form method='post' name='forma' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";
if (!isset($Prodlineid) ) {
	echo '<table><tr>
	<td>' . _('Formato:') . '</td>
	<td><select Name="edodes">';
	$sql = "SELECT typeid as type,formato as forma FROM conf_consulta order by formato";
	$categoria = DB_query($sql,$db);
	echo '<option VALUE="' . $myrowestado['typeid'].'" selected> Seleccionar' ;
	while ($myrowcategoria=DB_fetch_array($categoria,$db)){
            $categoria_base=$myrowcategoria['type'];
            if ($_POST['edodes']==$categoria_base){
		
                echo '<option  VALUE="' . $myrowcategoria['type'] .  '  " selected>' .ucwords(strtolower($myrowcategoria['forma']));
            }else{
                echo '<option  VALUE="' . $myrowcategoria['type'] .  '" >' .ucwords(strtolower($myrowcategoria['forma']));
            }
	}
	echo '<td valign=bottom>
		<input type=submit name=buscar value=' . _('Buscar') . '>
	</td></tr></table>';
	
	echo "<div class='centre'><hr width=50%></div><br>";
	if ($Offset==0) {
		$numfuncion=1;
	} else {
		$numfuncion=$num_reg*$Offset+1;
	}
	$Offsetpagina=1;
	//esta parte sirve para mostrar la primera tabla con todos los registros existentes
	$sql = "SELECT *
	FROM conf_printdocs as cp, conf_consulta as cc
	where cp.typeid=cc.typeid ";
	if (strlen($edodes)>=1) {
	$sql=$sql.' and cc.typeid='.$edodes;
	} 
	$result = DB_query($sql,$db);
	//$ListCount=DB_num_rows($result);
	//$ListPageMax=ceil($ListCount/$num_reg);

	$sql = "SELECT *
	FROM conf_printdocs as cp, conf_consulta as cc
	where cp.typeid=cc.typeid";
	if (strlen($edodes)>=1) {
	$sql=$sql.' and cc.typeid='.$edodes;
	}
	//$sql = $sql . " LIMIT ".$num_reg." OFFSET ". ($Offset * $num_reg) ;
	$result = DB_query($sql,$db);
	
	if (!isset($Prodlineid)) {
		echo "<div class='centre'>" ._('LISTADO DE FORMATOS'). "</div>";
		/*echo '<table width=50%>';
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
		</table>';*/
	}
	
	echo '<table border=1 width=50%>';
	echo "<tr>  <th>" . _('Formato') . "</th>
	<th>" . _('Titulo') . "</th>
        <th>" . _('Clave') . "</th>
	<th>" . _('Posicion inicial detalle') . "</th>
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
		if ($myrow['activo']==1) {
			$myrow['activo']='Si';
		} else {
			$myrow['activo']='No';
		}
		//<td style='text-align:center;'><a href=\"%s&Prodlineid=%s&borrar=1\">Eliminar</a></td>
		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td style='text-align:center;'><a href=\"%s&Prodlineid=%s&\">Modificar</a></td>
			</tr>",
			ucwords($myrow['formato']),
			$myrow['titulo'],
			$myrow['clave'],
			$myrow['pos_iniciodetalle'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['u_conf'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['u_conf']
			);
		$numfuncion=$numfuncion+1;
	} 
	echo '</table>';
}
if (isset($Prodlineid)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Formatos Existentes') . '</a></div>';
}

//esta parte sirve para mostrar los datos del registro seleccionado para modificar
if (isset($Prodlineid)) {
			
	$sql="SELECT *
		FROM conf_printdocs as cp, conf_consulta as cc,
		syscatprinted as sp,sysDocumentIndex as sd
		WHERE cp.typeid=cc.typeid and
		cp.tipo=sp.typeprinted and
		cp.typeid=sd.typeid and
		cp.typeid=cc.typeid and
		u_conf=". $Prodlineid ." order by tagref";
	$result = DB_query($sql, $db);
	if ( DB_num_rows($result) == 0 ) {
		prnMsg( _('No hay registros.'),'warn');
	} else {
		while ($myrow=DB_fetch_array($result,$db)) {
			if ($_POST['Prodlineid']==0){
			$_POST['formato'] = $myrow['typeid'];
			}
			$_POST['Prodlineid']= $myrow['u_conf'];
			$_POST['tipo'] = $myrow['typeprinted'];
			$_POST['tagref'] = $myrow['tagref'];
			$_POST['clave'] = $myrow['clave'];
			$_POST['titulo'] = $myrow['titulo'];
			$_POST['posini'] = $myrow['pos_iniciodetalle'];
			$_POST['url'] = $myrow['url'];
			$con++;
		}
	}
	
}

echo '<br>';
if(isset($_POST['Prodlineid'])) {
	echo "<input type=hidden name='Prodlineid' VALUE='" . $_POST['Prodlineid'] . "'>";
}
echo "<div class='centre'><hr width=60%>" ._('ALTA/MODIFICACION DE FORMATOS'). "</div><br>";
	echo '<table>';
	echo '<tr>
	<td>' . _('Formato:') . '</td>
	<td><select Name="formato" onchange="forma.submit()">';
	$sql = "SELECT * FROM conf_consulta order by formato";
	$categoria = DB_query($sql,$db);
	echo '<option VALUE="" selected> Seleccionar' ;
	while ($myrowcategoria=DB_fetch_array($categoria,$db)){
            $categoria_base=$myrowcategoria['typeid'];
            if (intval($_POST['formato'])==intval($categoria_base)) {
                echo '<option  VALUE="' . $myrowcategoria['typeid'] .  '  " selected>' .ucwords(strtolower($myrowcategoria['formato']));
            }else{
                echo '<option  VALUE="' . $myrowcategoria['typeid'] .  '" >' .ucwords(strtolower($myrowcategoria['formato']));
            }
	}
	
	if ($_POST['formato']>0)
	{
		$sql="SELECT *
		FROM conf_consulta
		WHERE typeid=".$_POST['formato'];
		$result = DB_query($sql, $db);
		if ( DB_num_rows($result) == 0 ) {
			prnMsg( _('No hay registros.'),'warn');
		} else {
			$myrow = DB_fetch_array($result);
			$_POST['sql_']=$myrow['sql_'];
			$_POST['parametro']=$myrow['parametro'];
			$_POST['sql2_']=$myrow['sql2_'];
			$_POST['url']=$myrow['url'];
			echo "<input type=hidden name='sql_' VALUE='" . $_POST['sql_'] . "'>";
			echo "<input type=hidden name='parametro' VALUE='" . $_POST['parametro'] . "'>";
			echo "<input type=hidden name='sql2_' VALUE='" . $_POST['sql2_'] . "'>";
			echo "<input type=hidden name='url' VALUE='" . $_POST['url'] . "'>";
		}
	}
	echo'<tr><td>' . _('Clave') . ":</td>
	<td><input type='text' name='clave' size=40 maxlength=100 VALUE='" .$_POST['clave'] . "'></td></tr>
	<tr>";
	echo'<tr><td>' . _('Titulo') . ":</td>
	<td><input type='text' name='titulo' size=40 maxlength=100 VALUE='" .$_POST['titulo'] . "'></td></tr>
	<tr>";
	echo '<tr><td>' . _('Pos inicial') . ":</td>
	<td><input type='text' name='posini' class=number size=3 maxlength=3 VALUE='" .$_POST['posini'] . "'></td></tr>
	<tr>";
	echo '<tr><td>' . _('Tipo impresion') . '</td>';
	echo'<td><select Name="tipo"><br>';
	echo '<option  VALUE="" selected>Seleccionar';
	   $sql = "SELECT typeprinted as typeprinted, printed as printed
	   FROM syscatprinted order by printed";
	    $grupo= DB_query($sql,$db);
	      while ($myrowgrupo=DB_fetch_array($grupo,$db)){
		$grupobase=$myrowgrupo['typeprinted'];
		if ($_POST['tipo']==$grupobase)
		  { 
		    echo '<option  VALUE="' . $myrowgrupo['typeprinted'] .  '  " selected>' .$myrowgrupo['printed'];
		  }
		else
		  {
		    echo '<option  VALUE="' . $myrowgrupo['typeprinted'] .  '" >' .$myrowgrupo['printed'];
		  }
	      }
	echo '</selected></td></tr>';
	if (strlen($_POST['formato'])==0){
		$_POST['formato']=0;
	}
	echo '<tr><td>' . _('Unidad de Negocio') . '</td>';
	   $SQL = "SELECT distinct t.tagref as tagref,t.tagdescription as tagdescription, conf_printdocs.typeid as tipo
		   FROM sec_unegsxuser u,tags t, sysDocumentIndex d
		    left join conf_printdocs on conf_printdocs.clave= reverse(left(REVERSE(typeabbrev),INSTR(REVERSE(typeabbrev),'=')-1))
		     and clave='".$_POST['clave']."' and conf_printdocs.typeid=".$_POST['formato']."
		     WHERE u.tagref = t.tagref and d.tagref=t.tagref ";
		     if ($_POST['formato']!=0){
			$SQL=$SQL. " and d.typeid=".$_POST['formato'];
		     }
		   $SQL=$SQL." AND u.userid = '" . $_SESSION['UserID'] . "'
		   ORDER BY t.tagref ";
		//echo $SQL;
		$gru= DB_query($SQL,$db);
		$contador=0;
		while ($myrowgru=DB_fetch_array($gru,$db)){
			$grubase=$myrowgru['tagref'];
			$tiposelect=$myrowgru['tipo'];
			
				if (!is_null($tiposelect))
				{
					echo "<tr class='EvenTableRows'><td colspan=2><input type='checkbox' checked='checked' name=unineg".$contador.">".$myrowgru['tagdescription']."</td></tr>";
					echo "<input type=hidden name=tagref".$contador." VALUE='".$myrowgru['tagref']."'>";
				} else {
					echo "<tr class='OddTableRows'><td colspan=2><input type='checkbox' name=unineg".$contador.">".$myrowgru['tagdescription']."</td></tr>";
					echo "<input type=hidden name=tagref".$contador." VALUE='".$myrowgru['tagref']."'>";
			}
			$contador++;
		}
		echo "<input type=hidden name='total' VALUE=" .$contador. ">";
	echo '</td>';
	echo '</tr></table>';
	//aqui verifica si esta vacia la variable si lo esta el valor de la accion sera enviar 
	if (!isset($Prodlineid)) {
		echo "<div class='centre'><input type='Submit' name='enviar' value='" . _('Enviar') . "'></div>";
	}
	//aqui verifica que este llena la variable si lo esta el valor de la accion sera modificar
	elseif (isset($Prodlineid)) {
		echo "<div class='centre'><input type='Submit' name='modificar' value='" . _('Actualizar') . "'></div>";
	}
echo '</form>';
include('includes/footer_Index.inc');
?>