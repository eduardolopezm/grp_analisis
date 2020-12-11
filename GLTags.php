<?php

/*
desarrollo18/JUNIO/2011 - Corregi problema de que si no habia Departamentos no se podian editar las Unidades de Negocios,
      Utilice LEFT JOINS
/*
 * AHA
* 6-Nov-2014
* Cambio de ingles a espa�ol los mensajes de usuario de tipo error,info,warning, y success.
*/
      
/* FIN DE CAMBIOS*/

$PageSecurity = 10;

include('includes/session.inc');
$title = _('Unidades de Negocio');

include('includes/header.inc');
$funcion=139;
include('includes/SecurityFunctions.inc');
$InputError=0;

if (isset($_GET['SelectedTag'])) {
  
  $sql='SELECT tags.tagref,
      tags.tagdescription,
      tags.areacode,
      tags.legalid as legalid,
      areas.areadescription,
      legalbusinessunit.legalname,
      tags.u_department,
      departments.department,
      tags.address1,tags.address2,tags.address3,tags.address4,tags.address5,tags.cp,
      tags.tagsupplier,tags.tagdebtorno,tags.typeinvoice
    FROM tags LEFT JOIN areas ON tags.areacode = areas.areacode
        LEFT JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
        LEFT JOIN departments ON tags.u_department = departments.u_department
    where tagref='.$_GET['SelectedTag'].' ORDER BY tagdescription';
      
  $result= DB_query($sql,$db);
  $myrow = DB_fetch_array($result,$db);
  $ref=$myrow[0];
  $description=$myrow[1];
  $depto=$myrow[6];
  
  if (isset($_POST['areacode'])){
      $areacode = $_POST['areacode'];
      $legalid = $_POST['legalid'];
      $address1=$_POST['address1'];
      $address2=$_POST['address2'];
      $address3=$_POST['address3'];
      $address4=$_POST['address4'];
      $address5=$_POST['address5'];
      $cp=$_POST['cp'];
      $typeinvoice= $_POST['tipofact']; 
      $tagdebtorno=$_POST['tagdebtorno'];
      $tagsupplier=$_POST['tagsupplier'];
            
    }else{
      $areacode = $myrow['areacode'];
      $legalid = $myrow['legalid'];
      $address1=$myrow['address1'];
      $address2=$myrow['address2'];
      $address3=$myrow['address3'];
      $address4=$myrow['address4'];
      $address5=$myrow['address5'];
      $cp=$myrow['cp'];
      $typeinvoice= $myrow['typeinvoice'];
      $tagdebtorno=$myrow['tagdebtorno'];
      $tagsupplier=$myrow['tagsupplier'];
      //echo "PASO3".$legalid;
    }

} else {
  $description='';
  $areacode='';
  $legalid=0;
  $depto=0;
  $_GET['SelectedTag']='';
}

if (isset($_POST['legalid'])){
  $legalid=$_POST['legalid'];
  $areacode=$_POST['areacode'];
  
}
if (isset($_POST['u_department'])){
  $depto=$_POST['u_departament'];
  $depto2=$_POST['department'];
  
}

if (isset($_POST['description']) && strlen($_POST['description'])<3){
  //si fue menor a tres letras se llena la variable de error y muestra mensaje      
  $InputError = 1;
  prnMsg(_('El nombre de la unidad de negocio debe ser de al menos 3 caracteres de longitud'),'error');
}


if (isset($_POST['submit'])and ($InputError != 1)) {
  $pais="MEXICO";
  $sql= "select count(*) from tags where tagdescription='".$_POST['description']."'";
      $result = DB_query($sql,$db);
      $myrow = DB_fetch_row($result);
    if ($myrow[0]>0){
      prnMsg( _('No se da de alta la unidad de negocio por que ya hay un registro guardado'),'error');
    }else {
      $sql = "insert into tags (tagref,tagdescription,legalid,areacode,u_department,tagname,address1,address2,address3,address4,address5,address6,cp,typeinvoice,tagdebtorno,tagsupplier)
      values('";
                        $sql.=$_POST['clave']."',";
                        $sql.="'".$_POST['description']."','".$_POST['legalid']."','".$_POST['areacode']."','".$_POST['u_department']."','".$_POST['description']."','".$_POST['address1']
      ."','".$_POST['address2']."','".$_POST['address3']."','".$_POST['address4']."','".$_POST['address5']."','".$pais."','".$_POST['cp']."','".$_POST["tipofact"]."','".$_POST['tagdebtorno']."','".$_POST['tagsupplier']."')";
      $ErrMsg = _('La insercion de la unidad de negocio fracaso porque');
      prnMsg( _('La unidad de negocio').' ' .$_POST['description'] . ' ' . _('se ha creado.'),'info');
    }
  $areacode=0;
  $legalid=0;
  $depto=0;
  
}
if (isset($sql) && $InputError != 1 ) {
  $result = DB_query($sql,$db,$ErrMsg);
  // inserta en tabla de seguridad
  $tagxuser = DB_Last_Insert_ID($db,'tags','tagref');
  if($tagxuser!=0){
    $sql="insert into sec_unegsxuser (userid,tagref)";
    $sql=$sql." values('".$_SESSION['UserID']."','".$tagxuser."')";
    $ErrMsg = _('Las operaciones sobre las unidades de negocio para este registro no han sido posibles por que ');
    $DbgMsg = _('El SQL utilizado es:');
    $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
  }
  
}

//$_POST['u_department'] = $myrow['u_department'];
if (isset($_POST['update'])) {
  $sql = 'update tags set tagref="'.$_POST['clave'].'" ,tagdescription="'.$_POST['description'].'",'.
    ' areacode="'.$_POST['areacode'].'",'.
    ' u_department="'.$_POST['u_department'].'",'.
    ' legalid='.$_POST['legalid'].' '.
    ' ,u_department="'.$_POST['u_department'].'",'.
    ' address1="'.$_POST['address1'].'",'.
    ' address2="'.$_POST['address2'].'",'.
    ' address3="'.$_POST['address3'].'",'.
    ' address4="'.$_POST['address4'].'",'.
    ' address5="'.$_POST['address5'].'",'.
    ' cp="'.$_POST['cp'].'",'.
    ' typeinvoice= "'.$_POST['tipofact'].'",'.
    ' tagsupplier="'.$_POST['tagsupplier'].'",'.
    ' tagdebtorno="'.$_POST['tagdebtorno'].'"'.
    ' where tagref="'.$_POST['reference'].'"';
    
  $ErrMsg = _('La modificacion de la unidad de negocio fracaso porque');
  prnMsg( _('La unidad de negocio').' ' .$_POST['description'] . ' ' . _('ha sido modificada.'),'info');
  $result= DB_query($sql,$db);
  $areacode=0;
  $legalid=0;
  $depto=0;
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . ' name="form">';
echo '<br><table>';
if ($_GET['Action']==='edit') {
   echo '<tr><td colspan="2" style="text-align:center"><a href="'.$rootpath.'/GLTags.php"><-Mostrar Unidades de Negocio Actuales</a></td></tr>'; 
   echo '<tr><td><br></td></tr>';
   echo '<tr></tr>';
}

//Clave editable de tagref
if($_GET['Action']==='edit'){
    echo '<tr><td>'.('CLAVE').':</td>';
    echo '<td><label>'.$ref.'</label></td></tr>';
}
else{
    echo '<tr><td>'.('CLAVE').':</td>';
    echo '<td><input class="number" size="2" maxlength="2" type="text" name="clave"  value="'.$ref.'"></td></tr>';
}

//SELECCIONA LA RAZON SOCIAL
echo '<tr><td>' . _('RAZON SOCIAL') . ':</td>';
echo '<td><select name="legalid">';

$SQL = 'SELECT legalid,
  legalname
  FROM legalbusinessunit
  ORDER BY legalid';

$result=DB_query($SQL,$db);
echo '<option value=0>seleccione alguna...';
while ($myrow=DB_fetch_array($result)){
  if ($legalid==$myrow['legalid']){
    echo '<option selected value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
  } else {
    echo '<option value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
  }
}
echo '</select></td></tr>';
//END RAZON SOCIAL


//SELECCIONA EL AREA
echo '<tr><td>' . _('SUCURSAL') . ':</td>';
echo '<td><select name="areacode">';

$SQL = 'SELECT areacode,
  areadescription
  FROM areas
  ORDER BY areacode';

$result=DB_query($SQL,$db);
echo '<option value=0>seleccione alguna...';
while ($myrow=DB_fetch_array($result,$db)){
  $base=$myrow['areacode'];
  if ($areacode==$base){
    echo '<option value=' . $myrow['areacode'] . ' selected>' . $myrow['areacode'].' - ' .$myrow['areadescription'];
    
  } else {
    echo '<option value=' . $myrow['areacode'] . '>' . $myrow['areacode'].' - ' .$myrow['areadescription'];
    
  }
}
echo '</select></td></tr>';
//END AREA
    
echo '<tr><td>'. _('Descripcion UN') . ':</td>
    <td><input type="text" size=50 maxlength=50 name="description" value="'.$description.'">
    <input type="hidden" name="reference" value="'.$_GET['SelectedTag'].'"></td></tr>';
    
//DEPARTAMENTO

/*if (!isset($_POST['u_department'])) {$_POST['u_department']='';}*/
/*$SQL = 'SELECT 
  u_department,
  department
  FROM departments
  ORDER BY u_department';

$result=DB_query($SQL,$db);
echo '<tr><td>' . _('DEPARTAMENTO') . ':</td>';
echo '<td><select name="u_department">';
while ($myrow = DB_fetch_array($result)){
    if (isset($_POST['u_department']) and $myrow[0]==$_POST['u_department']){
      echo '<OPTION SELECTED VALUE=';
    } else {
      echo '<option value=';
    }
    echo $myrow[0] ."'>". $myrow[0]."-".$myrow[1];
  }
  
  echo '</select></TD</TR>';*/

echo '<tr><td>' . _('DEPARTAMENTO') . ':</td>';
//echo 'Depto='.$depto;
echo '<td><select name="u_department">';

$SQL = 'SELECT u_department,
  department
  FROM departments
  ORDER BY u_department';

$result=DB_query($SQL,$db);
echo '<option value=0>seleccione alguna...';
while ($myrow=DB_fetch_array($result)){
  if ($depto==$myrow['u_department']){
    echo '<option selected value=' . $myrow['u_department'] . '>' . $myrow['u_department'].' - ' .$myrow['department'];
  } else {
    echo '<option value=' . $myrow['u_department'] . '>' . $myrow['u_department'].' - ' .$myrow['department'];
  }
}
echo '</select></td></tr>';

    
////fin departamento

echo '<tr><td>'. _('Calle') . ':</td>
    <td>
      <input type="text" size=50 maxlength=255 name="address1" value="'.$address1.'">
    </td></tr>';
  
echo '<tr><td>'. _('No.Ext') . ':</td>
    <td>
      <input type="text" size=50 maxlength=255 name="address2" value="'.$address2.'">
    </td></tr>';

echo '<tr><td>'. _('Colonia') . ':</td>
    <td>
      <input type="text" size=50 maxlength=255 name="address3" value="'.$address3.'">
    </td></tr>';

echo '<tr><td>'. _('Municipio') . ':</td>
    <td>
      <input type="text" size=50 maxlength=255 name="address4" value="'.$address4.'">
    </td></tr>';
    

echo '<tr><td>'. _('Estado') . ':</td>
    <td>
      <input type="text" size=50 maxlength=255 name="address5" value="'.$address5.'">
    </td></tr>';

echo '<tr><td>'. _('Cp') . ':</td>
    <td>
      <input type="text" size=50 maxlength=255 name="cp" value="'.$cp.'">
    </td></tr>';
echo '<tr><td>'. _('Proveedor Default Tienda') . ':</td>
    <td>
      <input type="text" size=50 maxlength=255 name="tagsupplier" value="'.$tagsupplier.'">
    </td></tr>';
echo '<tr><td>'. _('Cliente Default Tienda') . ':</td>
    <td>
      <input type="text" size=50 maxlength=255 name="tagdebtorno" value="'.$tagdebtorno.'">
    </td></tr>';

echo '<tr><td>'. _('Tipo de facturacion') . ':</td>
    <td>
      <select name="tipofact">';

$sql = 'Select typeinvoice, nameinvoice From config_typeinvoice Where active= 1 Order by typeinvoice';
$result = DB_query($sql, $db);

while ($myrow = DB_fetch_array($result)) 
{
  if ($typeinvoice == $myrow['typeinvoice']){
    echo '<option selected value=' . $myrow['typeinvoice'] . '>' . $myrow['nameinvoice'] . '</option>';
  } else {
    echo '<option value=' . $myrow['typeinvoice'] . '>' . $myrow['nameinvoice'] . '</option>';
  }
} 

echo '</select></td></tr>';

echo '<tr><td><input type="hidden" name="reference" value="'.$_GET['SelectedTag'].'">';

if (isset($_GET['Action']) and $_GET['Action']=='edit') {
  echo '<input type=Submit name=update value="' . _('Actualiza Unidad de Negocio') . '">';
} else {
  echo '<input type=Submit name=submit value="' . _('Alta Unidad de Negocio') . '">';
}

echo '</td></tr>';

echo '</table><p></p>';

echo '</form>';

echo '<table>';
echo '<tr><th>'. _('Clave') .'</th>';
echo '<th>'. _('Nombre Unidad Negocio'). '</th>';
echo '<th>'. _('Razon Social'). '</th>';
echo '<th>'. _('Sucursal'). '</th>';
echo '<th>'. _('Departamento'). '</th>';
echo '<th></th>';


$sql='SELECT tags.tagref, tags.tagdescription, tags.areacode, tags.legalid,
      areas.areadescription, legalbusinessunit.legalname, departments.department
    FROM tags LEFT JOIN departments ON tags.u_department = departments.u_department
      , areas, legalbusinessunit 
    where 
      tags.areacode = areas.areacode and
      tags.legalid = legalbusinessunit.legalid 
      ORDER BY tagdescription';

$result= DB_query($sql,$db);

$linea= 0;
while ($myrow = DB_fetch_array($result,$db))
{
  if ($linea == 1){
    echo "<tr style='background:#FAFAFA;'>";
    $linea= 0;
  } else {
    echo "<tr style='background:#FFFFFF;'>";
    $linea= 1;
  }
  
  echo '<td>'.$myrow[0].'</td><td>'.$myrow[1].'</td><td>'.$myrow[5].'</td><td>'.$myrow[4].'</td><td>'.$myrow[6].'</td><td><a href="' .
    $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedTag=' . $myrow[0] . '&Action=edit">' . _('Modificar') . '</a></td>';
  echo "</tr>";
}

echo '</table><p></p>';

echo "<script>defaultControl(document.form.description);</script>";

include('includes/footer.inc');

?>