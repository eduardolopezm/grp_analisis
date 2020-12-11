<?php

/* $Revision: 4.0 $ */
/* ARCHIVO MODIFICADO POR: Alejandra Rosas*/
/* FECHA DE MODIFICACION: 11/Enero/2010*/
/* CAMBIOS: Altas, Bajas y Modificaciones de la tabla de sysDocumentIndex*/
/* ARCHIVO MODIFICADO POR: Isabel Estrada*/
/* FECHA DE MODIFICACION: 13/Agosto/2010*/
/* 1.- */
/* FIN DE CAMBIOS*/
include('includes/session.inc');
$title = _('Modificaciones de Folios de Documentos');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$funcion=403;
include('includes/SecurityFunctions.inc');

if (isset($_POST['bustag'])) {
	$bustag= $_POST['bustag'];
} else {
	$bustag="";
}
if (isset($_POST['busrazon'])) {
	$busrazon= $_POST['busrazon'];
} else {
	$busrazon="";
}
if (isset($_POST['busarea'])) {
	$busarea= $_POST['busarea'];
} else {
	$busarea="";
}
if (isset($_POST['bustype'])) {
	$bustype= $_POST['bustype'];
} else {
	$bustype="";
}
if (isset($_POST['departamento'])) {
	$departamento= $_POST['departamento'];
} else {
	$departamento="";
}
$num_reg=100;

if (isset($_POST['num_reg'])) {
	$num_reg = $_POST['num_reg'];
}
//esta es la variable con la que nos guiamos para saber ssi es la primera vez que entran
//y para poder mostrar los datos del seleccionado cuando van a modificar
if (isset($_GET['areacode'])) {
	$areacode = $_GET['areacode'];
} elseif (isset($_POST['areacode'])) {
	$areacode = $_POST['areacode'];
}
if (isset($_GET['typeid'])) {
	$typeid = $_GET['typeid'];
} elseif (isset($_POST['typeid'])) {
	$typeid = $_POST['typeid'];
}
if (isset($_GET['tagref'])) {
	$tagref = $_GET['tagref'];
} elseif (isset($_POST['tagref'])) {
	$tagref = $_POST['tagref'];
}

$InputError = 0;
if (isset($_POST['enviar']) || isset($_GET['borrar']) || isset($_POST['modificar']) ) {
		if (isset($_POST['typename']) && strlen($_POST['typename'])<3){
		$InputError = 1;
		prnMsg(_('typename debe ser de al menos 3 caracteres de longitud'),'error');
		}
	unset($sql);
	if (isset($_POST['modificar'])and ($InputError != 1)) {
		$sql = "UPDATE sysDocumentIndex SET typename='".$_POST['typename']."', typeabbrev='".$_POST['typeabbrev']."',typeno='".$_POST['typeno']."' where typeid=".$typeid." and areacode='".$areacode."' and tagref=".$tagref." ";
		$result=DB_query($sql, $db);
		$ErrMsg = _('La actualización fracaso porque');
		prnMsg( _('').' ' .$_POST['typename'] . ' ' . _(' se ha actualizado.'),'info');
	} elseif (isset($_GET['borrar'])and ($InputError != 1)) {
			$sql="DELETE FROM sysDocumentIndex where typeid=".$_GET['typeid']." and areacode=".$_GET['areacode']." and tagref=".$_GET['tagref']."";
			$result=DB_query($sql, $db);
			prnMsg(_('A sido eliminada ') . '!','info');
	} /*elseif (isset($_POST['enviar'])and ($InputError != 1)) {
			//$sql= "select count(*) from sysDocumentIndex where typeid=".$_POST['typeid']." and areacode=".$_POST['areacode']." and tagref=".$_POST['tagref']."";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('No se da de alta por que ya hay un registro guardado'),'error');
		} else {
			$sql = "INSERT INTO sysDocumentIndex (typeid,typename,areacode,tagref,loccode,typeabbrev,typeno,folio)
			VALUES ('".$_POST['typeid']."','".$_POST['typename']."','".$_POST['areacode']."',
			'".$_POST['tagref']."','".$_POST['loccode']."','".$_POST['typeabbrev']."','".$_POST['typeno']."',
			'".$_POST['folio']."')";
			$ErrMsg = _('La inserccion fracaso porque');
			prnMsg( _('').' ' .$_POST['typename'] . ' ' . _('se ha creado.'),'info');
		}
	}*/
	unset($_POST['typeabbrev']);
	unset($_POST['typename']);
	unset($_POST['folio']);
	unset($_POST['loccode']);
	unset($_POST['typeno']);
	unset($_POST['typeid']);
	unset($_POST['tagref']);
	unset($_POST['areacode']);
	unset($tagref);
	unset($typeid);
	unset($areacode);	
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

if (!isset($areacode) ) {
	echo '<table><tr>';
	//Busqueda por Razon social
	echo '<td>' . _('Razón Social') . '</td>';
	echo'<td><select Name="busrazon"><br>';
	echo '<option  VALUE="" selected>Seleccionar';
	   $sql = "SELECT legalid, legalname  FROM legalbusinessunit order by legalid";
	    $grupo= DB_query($sql,$db);
	      while ($myrowgrupo=DB_fetch_array($grupo,$db)){
		$grupobase=$myrowgrupo['legalid'];
		if ($_POST['busrazon']==$grupobase)
		  { 
		    echo '<option  VALUE="' . $myrowgrupo['legalid'] .  '  " selected>' .sprintf("%02d",$myrowgrupo['legalid'])."-".$myrowgrupo['legalname'];
		  }
		else
		  {
		    echo '<option  VALUE="' . $myrowgrupo['legalid'] .  '" >' .sprintf("%02d",$myrowgrupo['legalid'])."-".$myrowgrupo['legalname'];
		  }
	      }
	//Busqueda por Area
	echo '<td>&nbsp;&nbsp;&nbsp; ' . _('Area') . '</td>';
	echo'<td><select Name="busarea"><br>';
	echo '<option  VALUE="" selected>Seleccionar';
	   $sql = "SELECT areacode, areadescription  FROM areas order by areacode";
	    $grupo= DB_query($sql,$db);
	      while ($myrowgrupo=DB_fetch_array($grupo,$db)){
		$grupobase=$myrowgrupo['areacode'];
		if ($_POST['busarea']==$grupobase)
		  { 
		    echo '<option  VALUE="'. $myrowgrupo['areacode'] .'" selected>' .$myrowgrupo['areadescription'];
		  }
		else
		  {
		    echo '<option  VALUE="' . $myrowgrupo['areacode'] .  '" >' .$myrowgrupo['areadescription'];
		  }
	      }
	      echo "</td></tr>";
	//Busqueda por departamento
	echo '<tr><td>' . _('Departamento') . '</td>';
	echo'<td><select Name="departamento"><br>';
	echo '<option  VALUE="" selected>Seleccionar';
	   $sql = "SELECT * FROM departments order by department";
	    $grupo= DB_query($sql,$db);
	      while ($myrowgrupo=DB_fetch_array($grupo,$db)){
		$grupobase=$myrowgrupo['u_department'];
		if ($_POST['departamento']==$grupobase)
		  { 
		    echo '<option  VALUE="' . $myrowgrupo['u_department'] .  '  " selected>' .$myrowgrupo['department'];
		  }
		else
		  {
		    echo '<option  VALUE="' . $myrowgrupo['u_department'] .  '" >' .$myrowgrupo['department'];
		  }
	      }  
	echo '</td>';
	//Busqueda por Unidad de Negocio
	echo '<td>&nbsp&nbsp&nbsp&nbsp;' . _('Unidad De Negocio') . '</td>';
	echo'<td><select Name="bustag"><br>';
	echo '<option  VALUE="" selected>Seleccionar';
	   $sql = "SELECT tagref as code, tagdescription as tag FROM tags order by tagdescription";
	    $grupo= DB_query($sql,$db);
	      while ($myrowgrupo=DB_fetch_array($grupo,$db)){
		$grupobase=$myrowgrupo['code'];
		if ($_POST['bustag']==$grupobase)
		  { 
		    echo '<option  VALUE="' . $myrowgrupo['code'] .  '  " selected>' .$myrowgrupo['tag'];
		  }
		else
		  {
		    echo '<option  VALUE="' . $myrowgrupo['code'] .  '" >' .$myrowgrupo['tag'];
		  }
	      }  
	echo '</td></tr>';
	//Busqueda por Tipo de documento   
	echo '<tr><td>' . _('Formato') . '</td>';
	echo'<td><select Name="bustype"><br>';
        echo '<option  VALUE="" selected>Seleccionar';
	   $sql = "SELECT typeid as id, typename as type FROM systypescat order by id";
	    $grupo= DB_query($sql,$db);
	      while ($myrowgrupo=DB_fetch_array($grupo,$db)){
		$grupobase=$myrowgrupo['id'];
		 
		if ($_POST['bustype']==$grupobase)
		  { 
		    echo '<option  VALUE="' . $myrowgrupo['id'] .  '  " selected>' .sprintf("%03d",$myrowgrupo['id'])."-".$myrowgrupo['type'];
		  }
		else
		  {
		    echo '<option  VALUE="' . $myrowgrupo['id'] .  '" >' .sprintf("%03d",$myrowgrupo['id'])."-".$myrowgrupo['type'];
		  }
	      }
	echo '</td>';
	echo'<td valign=bottom>
		<input type=submit name=buscar value=' . _('Buscar') . '>
	</td></tr></table>';
	echo "<div class='centre'><hr width=95%></div><br>";
	if ($Offset==0) {
		$numfuncion=1;
	} else {
		$numfuncion=$num_reg*$Offset+1;
	}
	$Offsetpagina=1;
//esta parte sirve para mostrar la primera tabla con todos los registros existentes
	$sql = "SELECT 	sysDocumentIndex.typename,sysDocumentIndex.tagprint as tagref,tags.tagdescription,
	sysDocumentIndex.typeabbrev,sysDocumentIndex.typeno,sysDocumentIndex.areacode ,
	sysDocumentIndex.typeid,sysDocumentIndex.tagref ,sysDocumentIndex.legalid
		FROM sysDocumentIndex, tags , systypescat, legalbusinessunit
		WHERE sysDocumentIndex.tagprint=tags.tagref
		AND sysDocumentIndex.legalid=legalbusinessunit.legalid
		AND sysDocumentIndex.typeid=systypescat.typeid";
	
	if (strlen($busrazon)>=1) {
	$sql=$sql.' AND sysDocumentIndex.legalid='.$busrazon.'';
	}
	
	if (strlen($busarea)>=1) {
	$sql=$sql.' AND sysDocumentIndex.areacode='.$busarea.'';
	}
	if (strlen($bustag)>=1) {
	$sql=$sql.' AND sysDocumentIndex.tagprint='.$bustag.'';
	}
	
	if (strlen($bustype)>=1) {
	$sql=$sql.' AND sysDocumentIndex.typeid='.$bustype.'';
	}
	if (strlen($departamento)>=1) {
	$sql=$sql.' AND sysDocumentIndex.u_department='.$departamento.'';
	}
	$result=DB_query($sql,$db);
	//$ListCount=DB_num_rows($result);
	//$ListPageMax=ceil($ListCount/$num_reg);
	//echo "consulta".$sql;
	$sql = "SELECT 	sysDocumentIndex.typename,sysDocumentIndex.tagprint as tagref,tags.tagdescription,
	sysDocumentIndex.typeabbrev,sysDocumentIndex.typeno,sysDocumentIndex.areacode ,
	sysDocumentIndex.typeid,sysDocumentIndex.tagref,sysDocumentIndex.legalid 
		FROM sysDocumentIndex, tags , systypescat
		WHERE sysDocumentIndex.tagprint=tags.tagref
		AND sysDocumentIndex.typeid=systypescat.typeid";
	if (strlen($busrazon)>=1) {
	$sql=$sql.' AND sysDocumentIndex.legalid='.$busrazon.'';
	}
	if (strlen($busarea)>=1) {
	$sql=$sql.' AND sysDocumentIndex.areacode='.$busarea.'';
	}
	if (strlen($bustag)>=1) {
	$sql=$sql.' AND sysDocumentIndex.tagprint='.$bustag.'';
	}
	if (strlen($bustype)>=1) {
	$sql=$sql.' AND sysDocumentIndex.typeid='.$bustype.'';
	}
	if (strlen($departamento)>=1) {
	$sql=$sql.' AND sysDocumentIndex.u_department='.$departamento.'';
	}
	$result=DB_query($sql,$db);
	$ListCount=DB_num_rows($result);
	//echo $ListCount;
	$ListPageMax=ceil($ListCount/$num_reg);
	
	
	$sql = $sql . " LIMIT ".$num_reg." OFFSET ". ($Offset * $num_reg) ;
	$result = DB_query($sql,$db);
	//echo "sql2:".$sql;
	if (!isset($areacode)) {
		echo "<div class='centre'>" ._('LISTADO DE FOLIOS DE DOCUMENTOS'). "</div>";
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
			<td><input type="text" name="num_reg" size=3 value="' .$num_reg. '"></td>
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
	
	echo '<table border=1 width=90%>';
	echo "<tr><th>" . _('Documento') . "</th>
	<th>" . _('Unid. Negocio') . "</th>
	<th>" . _('legal_razon') . "</th>
	<th>" . _('code') . "</th>
	<th>" . _('URL') . "</th>
	<th>" . _('No. Docto') . "</th>
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
		//echo $myrow['tagref'];
		//<td style='text-align:center;'><a href=\"&legalid=%s&areacode=%s&typeid=%s&tagref=%s\">Modificar</a></td>
		//<td style='text-align:center;'><a href=\"%s&areacode=%s&typeid=%s&tagref=%s&borrar=1\">Eliminar</a></td>
		printf("<td style='font-size:8pt;'>%s</td>
			<td style='font-size:8pt;'>%s</td>
			<td style='font-size:8pt;'>%s</td>
			<td style='font-size:8pt;'>%s</td>
			<td style='font-size:8pt;'>%s</td>
			<td  style='text-align:right'>%s</td>
			<td style='text-align:center;'><a href=\"%s&legalid=%s&areacode=%s&typeid=%s&tagref=%s\">Modificar</a></td>
			</tr>",
			$myrow['typename'],
			$myrow['tagdescription'],
			$myrow['legalid'],
			$myrow['areacode'],
			$myrow['typeabbrev'],
			$myrow['typeno'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['legalid'],
			$myrow['areacode'],
			$myrow['typeid'],
			$myrow['tagref'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['legalid'],
			$myrow['areacode'],
			$myrow['typeid'],
			$myrow['tagref']
			);
		$numfuncion=$numfuncion+1;
	} 
	echo '</table>';
}
if (isset($areacode)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('FOLIOS DE DOCUMENTOS EXISTENTES') . '</a></div>';
}

//esta parte sirve para mostrar los datos del registro seleccionado para modificar
if (isset($areacode)) {
	$sql = "SELECT *
		FROM sysDocumentIndex
		WHERE typeid=".$typeid." and tagref=".$tagref."
		and areacode='".$areacode."'";
		
	$result = DB_query($sql, $db);
	if ( DB_num_rows($result) == 0 ) {
		prnMsg( _('No hay registros.'),'warn');
	} else {
		$myrow = DB_fetch_array($result);
		$_POST['id']=$myrow['id'];
		//echo $_POST['id'];
		$_POST['legalid']=$myrow['legalid'];
		$_POST['typename']=$myrow['typename'];
		$_POST['tagref'] = $myrow['tagref'];
		$_POST['typeabbrev']=$myrow['typeabbrev'];
		$_POST['areacode'] = $myrow['areacode'];
		$_POST['typeid'] = $myrow['typeid'];
		$_POST['typeno'] = $myrow['typeno'];
		//echo $_POST['typeno'];
		$_POST['u_department']=$myrow['u_department'];
	}
}

echo '<br>';
if(isset($_POST['areacode'])) {
	echo "<input type=hidden name='areacode' VALUE='" . $_POST['areacode'] . "'>";
	echo "<input type=hidden name='typeid' VALUE='" . $_POST['typeid'] . "'>";
	echo "<input type=hidden name='tagref' VALUE='" . $_POST['tagref'] . "'>";
}
if (isset($areacode)) {
echo "<div class='centre'><hr width=60%>" ._('MODIFICACION'). "</div><br>";
	echo '<table>';
	/*echo'<tr><td>' . _('Typeid') . ":</td>
	<td><input type='text' name='typeid' size=40 maxlength=100 VALUE='" .$_POST['typeid'] . "'></td></tr>
	<tr>";*/
	echo'<tr><td>' . _('Nombre Documento') . ":</td>
	<td><input type='text' name='typename' size=40 maxlength=100 VALUE='" .$_POST['typename'] . "'></td></tr>
	<tr>";
	/*echo '<td>' . _('Areacode') . '</td>';
	echo'<td><select Name="areacode"><br>';
	   $sql = "SELECT areacode as code, areadescription as area FROM areas order by areadescription";
	    $grupo= DB_query($sql,$db);
	      while ($myrowgrupo=DB_fetch_array($grupo,$db)){
		$grupobase=$myrowgrupo['code'];
		if ($_POST['areacode']==$grupobase)
		  { 
		    echo '<option  VALUE="' . $myrowgrupo['code'] .  '  " selected>' .$myrowgrupo['area'];
		  }
		else
		  {
		    echo '<option  VALUE="' . $myrowgrupo['code'] .  '" >' .$myrowgrupo['area'];
		  }
	      }
	echo '</td>';
	echo '<tr><td>' . _('Tagref') . ":</td>
	<td><input type='text' name='tagref' size=40 maxlength=100 VALUE='" .$_POST['tagref'] . "'></td></tr>
	<tr>";
	echo '<tr><td>' . _('Loccode') . ":</td>
	<td><input type='text' name='loccode' size=40 maxlength=100 VALUE='" .$_POST['loccode'] . "'></td></tr>
	<tr>";*/
	echo "<td>" . _('URL') . "</td>
	<td><input type='text' name='typeabbrev' size=40 maxlength=100 VALUE='" .$_POST['typeabbrev'] . "'></td></tr>
	</td>";
	echo "<td>" . _('No. Documento') . "</td>
	<td><input type='text' name='typeno' class='number' size=40 maxlength=100 VALUE='" .$_POST['typeno'] . "'></td></tr>
	</td>";
	/*echo "<td>" . _('Folio') . "</td>
	<td><input type='text' name='folio' size=40 maxlength=100 VALUE='" .$_POST['folio'] . "'></td></tr>
	</td>";
	echo '</tr>';*/
	echo'</table>';
	//aqui verifica si esta vacia la variable si lo esta el valor de la accion sera enviar 
	if (!isset($areacode)) {
		echo "<div class='centre'><input type='Submit' name='enviar' value='" . _('Enviar') . "'></div>";
	}
	//aqui verifica que este llena la variable si lo esta el valor de la accion sera modificar
	//AND (tags.tagref=s.tagprint or )
	elseif (isset($areacode)) {
		echo "<br><div class='centre'>" . _('SI ACTUALIZAS EL No.DOCUMENTO SE MODIFICARAN <BR> LAS SIGUIENTES UNIDADES') . '</div>';
			$sqlc="Select tags.tagdescription, s.id
			FROM tags JOIN sysDocumentIndex s ON tags.tagref=s.tagprint or tags.tagref=s.tagref 
			WHERE s.legalid='".$_POST['legalid']."'
			AND s.typeid='".$_POST['typeid']."'
			AND s.areacode='".$_POST['areacode']."'
			AND s.tagref='".$_POST['tagref']."'
			AND (s.id='".$_POST['id']."' or s.id!='".$_POST['id']."')
			AND (s.u_department is Null or s.u_department='".$_POST['u_department']."')
			";
			//echo $sqlc;
			$resultc=DB_query($sqlc,$db);
			echo '<table>';
			//echo '<tr><td style="text-color:red;">SI ACTUALIZAS EL No.DOCUMENTO TAMBIÉN SE MODIFICARIAN <BR> LAS SIGUIENTES UNIDADES</td></tr>';
			while ($myrow = DB_fetch_array($resultc)) {
				if($myrow['id']==$_POST['id']){
					echo '<tr><td><b> Unidad: </b></td>';
					echo '<td><b>'.$myrow['tagdescription'].'-'.$myrow['id'].'<b></td></td><tr>';
				}
				else{
					echo '<tr><td><b> Unidad: </b></td>';
					echo '<td>'.$myrow['tagdescription'].'-'.$myrow['id'].'</td></td><tr>';
				}
				
			}
			echo '<br></table>';
		echo "<br><div class='centre'><input type='Submit' name='modificar' value='" . _('Actualizar') . "'></div>";
	}
}
echo '</form>';
include('includes/footer.inc');
?>