<?php
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 5;
$funcion = 408;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db);
include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

//Seleccion de nuevo men� o viejo menu
if ($_SESSION['ShowIndex']!=0) {
    $sec_functions = "sec_functions_new";
} else {
    $sec_functions = "sec_functions";
}

if (isset($_GET['Select'])) {
    $_SESSION['FunctionID']=$_GET['FunctionID'];
}
if (!isset($_SESSION['FunctionID'])) { //initialise if not already done
    $_SESSION['FunctionID']="";
}

//echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="' . _('Funciones') . '" alt="">' . ' ' . _('FUNCIONES') . '</p>';

$msg="";

if (isset($_POST['Go1']) or isset($_POST['Go2'])) {
    $_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
    $_POST['Go'] = '';
}

if (!isset($_POST['PageOffset'])) {
    $_POST['PageOffset'] = 1;
} else {
    if ($_POST['PageOffset']==0) {
        $_POST['PageOffset'] = 1;
    }
}


if (isset($_POST['registro']) and strlen($_POST['registro'])>0) {
    //eliminamos los permisos por perfil
    $sql="Delete from sec_funxprofile WHERE functionid = ".$_SESSION['FunctionID'];
    $ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
    $DbgMsg = _('El SQL utilizado es:');
    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
    if (isset($_POST['total'])) {
        $totalfunciones=$_POST['total'];
        for ($funciones = 0; $funciones <= $totalfunciones; $funciones++) {
            if ($_POST['funcion'.$funciones]==true) {
                $idfuncionr=$_POST['fun'.$funciones];
                $sql="insert into sec_funxprofile (profileid,functionid)";
                $sql=$sql." values(".$idfuncionr.",".$_SESSION['FunctionID'].")";
                $ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
                $DbgMsg = _('El SQL utilizado es:');
                $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
            }
        }
    }
    prnMsg(_('Se ha realizado la asignacion de Funcion Numero: '.$_SESSION['FunctionID']), 'success');
    unset($_SESSION['FunctionID']);
    unset($_POST['Select']);
    unset($_POST['Search']);
    unset($_POST['Previous']);
    unset($_POST['Go']);
    unset($_POST['Next']);
    unset($_POST['registro']);
}

//echo '<table border=1 width=100% valign=top>';
  
  # *************************************************
  # ***** TRAE INFO DE UNA FUNCION SELECCIONADA ****
  # *************************************************
/*
if ($_POST['Select']!="" or $_SESSION['FunctionID']!="") {
    if ($_POST['Select']!="") {
        $SQL = "SELECT * from $sec_functions where  functionid='" . $_POST['Select'] . "'";
        $_SESSION['FunctionID'] = $_POST['Select'];
    } else {
        $SQL = "SELECT * from $sec_functions where  functionid='" . $_SESSION['FunctionID'] . "'";
    }
    $ErrMsg = _('El numero de funcion no se encuentra, por que ');
    $result = DB_query($SQL, $db, $ErrMsg);
    if ($myrow=DB_fetch_row($result)) {
        $funcionname = $myrow[2];
        $_SESSION['FunctionID']=$myrow[0];
    }
    unset($result);
  
    echo '<tr><td valign="top"><form action='.$_SERVER['PHP_SELF'] . "?" . SID. ' method=post>';
    echo '<table cellpadding=3 border=1 width="100%">
    <tr>
      <th colspan=2>' . _('FUNCION: '). $funcionname . '</th>
      </td>';
      echo '</tr>';
                        // trae funciones
                $sql = "SELECT distinct P.profileid as profilea,
           FP.functionid as functiona,
           P.name as title
          
      FROM sec_profiles P left join  sec_funxprofile FP on P.profileid = FP.profileid
               and FP.functionid=".  $myrow[0].", $sec_functions F
      WHERE F.functionid=".$myrow[0]."
      order by  P.profileid,P.name asc ";
      
                    $ReFuntion = DB_query($sql, $db);
                    $condfuntion=false;
        $z=0;
    if (DB_num_rows($ReFuntion)>0) {
            echo "<table width=50% CELLPADDING=0 CELLSPACING=0 border=1 bordercolor=black>";
            $condfuntion=true;
        echo '<tr>
          <td>
            <font style=font-size:10px;>'._('PERFILES').'</font>
          </td>
        </tr>';
    }
        $nombrecategoryant = "";
    while ($ResFuntion = DB_fetch_array($ReFuntion)) {
            $Funtionid=$ResFuntion['profilea'];
            $nameFuntion=strtolower($ResFuntion['title']);
        $Funtionval=$ResFuntion['functiona'];
          
        if (is_null($Funtionval)) {
            echo '<tr>
          <td>
              <input type=checkbox name=funcion'.$z.'>
            <font style=font-size:10px;>'.$Funtionid.' '.ucwords($nameFuntion).'</font>
            <input type=hidden name=fun'.$z.' value=' .$Funtionid . '>
          </td>
          </tr>';
        } else {
            echo '<tr>
          <td >
               <input type=checkbox name=funcion'.$z.' checked>
            <font style=font-size:10px;>'.str_repeat('0', 3-strlen($Funtionid)).$Funtionid.' '.ucwords($nameFuntion).'</font>
            <input type=hidden name=fun'.$z.' value=' .$Funtionid . '>
          </td>
              </tr>';
        }
        $nombrecategoryant=$nombrecategoria;
            $z=$z+1;
    }//Fin de while para extraer funciones
    echo '</table>';
    echo '<div class="centre"><input type="hidden" name="total" value="' .$z . '"></div>';
    echo '<div class="centre"><input type="submit" name="registro" value="' . _('Registra Informacion') . '"></div></form>';
    echo '</table>';
    echo '</td></tr>';
       
    
}*/

/**********************************************************/
/******************* INICIO SQL BASICO*********************/
/**********************************************************/
$BASICSQL =  "SELECT s.functionid, s.title,p.title as module, s.comments
       FROM $sec_functions s, sec_submodules p where p.submoduleid=s.submoduleid";
       
/**********************************************************/
/******************* ***FIN SQL BASICO*********************/
/**********************************************************/
if (isset($_POST['Search'])) {
    unset($_SESSION['FunctionID']);
};

if (isset($_POST['Search']) or isset($_SESSION['FunctionID']) or isset($_POST['CSV']) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
    if (isset($_POST['Search'])) {
        $_POST['PageOffset'] = 1;
    }
    if ($_POST['Keywords'] or (($_POST['CustCode']) or ($_POST['CustTaxid']) or ($_POST['pagina']) or ($_POST['comentarios']))) {
        $msg=_('Resultados de la busqueda...') . '<br>';
        $_POST['Keywords'] = strtoupper($_POST['Keywords']);
        $_POST['CustCode'] = strtoupper($_POST['CustCode']);
        $_POST['CustTaxid'] = strtoupper($_POST['CustTaxid']);
  
        if (strlen($_POST['Keywords'])>0) {
            $_POST['Keywords'] = strtoupper(trim($_POST['Keywords']));

          //insert wildcard characters in spaces

            $i=0;
            $SearchString = "%";

            while (strpos($_POST['Keywords'], " ", $i)) {
                $wrdlen=strpos($_POST['Keywords'], " ", $i) - $i;
                $SearchString=$SearchString . substr($_POST['Keywords'], $i, $wrdlen) . "%";
                $i=strpos($_POST['Keywords'], " ", $i) +1;
            }
            $SearchString = $SearchString . substr($_POST['Keywords'], $i)."%";

            $SQL = $BASICSQL ." AND s.title " . LIKE . " '$SearchString' ";
        } elseif (strlen($_POST['CustCode'])>0) {
            $_POST['CustCode'] = strtoupper(trim($_POST['CustCode']));
            $SQL = $BASICSQL ." AND s.functionid " . LIKE  . " '%" . $_POST['CustCode'] . "%'";
        } elseif (strlen($_POST['CustTaxid'])>0) {
            $_POST['CustTaxid'] = strtoupper(trim($_POST['CustTaxid']));
            $SQL = $BASICSQL ." AND s.submoduleid " . LIKE  . " '%" . $_POST['CustTaxid'] . "%'";
        } elseif (strlen($_POST['pagina'])>0) {
            $SQL = $BASICSQL ." AND s.url " . LIKE  . " '%" . $_POST['pagina'] . "%'";
        } elseif (strlen($_POST['comentarios'])>0) {
            $SQL = $BASICSQL ." AND s.comments " . LIKE . "'%".$_POST['comentarios'] . "%'";
        }

        $SQL .= ' and s.active=1 ORDER BY s.submoduleid, s.type, s.title';
        $ErrMsg = _('La busqueda de funciones no puedo ser competada por que ');
  
        $resultbusqueda = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($resultbusqueda)==1) {
            $myrow=DB_fetch_array($resultbusqueda);
            $_POST['Select'] = $myrow['functionid'];
            $resultbusqueda = DB_query($SQL, $db, $ErrMsg);
          //unset($result);
        } elseif (DB_num_rows($resultbusqueda)==0) {
            prnMsg(_('No existe esta funcion, intentelo nuevamente'), 'info');
            echo '<br>';
        }
    } //one of keywords or custcode or custphone was more than a zero length string
    else {
        unset($result);
    };
} //end of if search


if (!isset($_POST['Select'])) {
    $_POST['Select']="";
}
  /***********************************************/
  /*********INICIO*busqueda***********************/
  /***********************************************/

echo '<form action=' . $_SERVER ['PHP_SELF'] . ' method=post name="form" enctype="multipart/form-data">';
?>
<div class="panel panel-default">
  <div class="panel-heading" role="tab" id="headingOne">
    <h4 class="panel-title row">
        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
         <div class="text-left"><span class="glyphicon glyphicon-chevron-down"></span> Buscar </div>
        </a>
    </h4>
  </div>
  <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
    <div class="panel-body">
     <div class="container">

     <div class="col-xs-12 col-md-7 text-left" style="float: none;">  
     <!--Titulo-->
     <?php
              if (isset($_POST['Keywords'])) {
                  ?>
                <!--<input type="Text" name="Keywords" value="<?php //echo $_POST['Keywords']?>" size=40 maxlength=50>-->
                 <component-text-label label="Título" id="Keywords" Name="Keywords" placeholder="Título" maxlength="50"
        value="<?php echo $_POST['Keywords']?>"></component-text-label><br>
              <?php
              } else {
                  ?>
               <!-- <input type="Text" name="Keywords" size=40 maxlength=50>-->
    <component-text-label label="Título" id="Keywords" Name="Keywords" placeholder="Título" maxlength="50"
        value=""></component-text-label><br>
                  <?php
              }
              ?>

     <!--funcion-->
      <?php
              if (isset($_POST['CustCode'])) {
                  ?>
               <!-- <input type="Text" name="CustCode" value="<?php //echo $_POST['CustCode'] ?>" size=15 maxlength=18>-->
            <component-text-label label="Función" id="CustCode" Name="CustCode" placeholder="Función" maxlength="50"
        value="<?php echo $_POST['CustCode'] ?>"></component-text-label><br>

              <?php
              } else {
                  ?>
               <!-- <input type="Text" name="CustCode" size=15 maxlength=18>-->
               <component-text-label label="Función" id="CustCode" Name="CustCode" placeholder="Función" maxlength="50"
        value=""></component-text-label><br>
                  <?php
              }
              ?>
              <!--pagina-->
               <?php
              if (isset($_POST['pagina'])) {
              ?>
              <!--<input type="Text" name="pagina" value="<?php //echo $_POST['pagina'] ?>" size=40 maxlength=50>-->
              <component-text-label label="Página:" id="pagina" Name="pagina" placeholder="Página" maxlength="50"
        value="<?php echo $_POST['pagina']; ?>"></component-text-label><br>
              <?php
              } else {
              ?>
              <!--<input type="Text" name="pagina" size=30 maxlength=35>-->
               <component-text-label label="Página:" id="pagina" Name="pagina" placeholder="Página" maxlength="50"
        value=""></component-text-label><br>
              <?php
              }
              ?>
              <!--comentarios -->

               <?php
              if (isset($_POST['comentarios'])) {
              ?>
             <!-- <input type="Text" name="comentarios" value="<?php //echo $_POST['comentarios'] ?>" size=40 maxlength=50>-->
             <component-text-label label="Comentarios" id="comentarios" name="comentarios" placeholder="Comentarios" maxlength="50"
        value="<?php echo $_POST['comentarios']; ?>"></component-text-label><br>
              <?php
              } else {
              ?>
              <!--<input type="Text" name="comentarios" size=30 maxlength=35>-->
               <component-text-label label="Comentarios" id="comentarios" name="comentarios" placeholder="Comentarios" maxlength="50"
        value=""></component-text-label><br>
              <?php
              }
              ?>
             
               <component-button type="submit" id="Search" name="Search" value="Filtrado"></component-button>
        </div>
      </div><!--fin container -->
    </div>
  </div>
</div>
<?php
echo '</form>';
?>


        <!--<div class="container">-->
          <?php
          
          if (isset($resultbusqueda) and (!isset($_SESSION['FunctionID']) or isset($_POST['Search']) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous']))) {
              $ListCount=DB_num_rows($resultbusqueda);
            //echo "sql:".$SQL;
              $ListPageMax=ceil($ListCount/$_SESSION['DisplayRecordsMax']);
              if (!isset($_POST['CSV'])) {
                  if (isset($_POST['Next'])) {
                      if ($_POST['PageOffset'] < $ListPageMax) {
                          $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
                      }
                  }
          
                  if (isset($_POST['Previous'])) {
                      if ($_POST['PageOffset'] > 1) {
                          $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
                      }
                  }
          
                  echo "<input type=\"hidden\" name=\"PageOffset\" VALUE=\"". $_POST['PageOffset'] ."\"/>";
          
                  if ($ListPageMax >1) {
                      echo "<p><div class=centre>&nbsp;&nbsp;" . $_POST['PageOffset'] . ' ' . _('de') . ' ' . $ListPageMax . ' ' . _('Paginas') . '. ' . _('Ir a la Pagina') . ': ';
          
                      echo '<select name="PageOffset1">';
          
                      $ListPage=1;
                      while ($ListPage <= $ListPageMax) {
                          if ($ListPage == $_POST['PageOffset']) {
                              echo '<option VALUE=' . $ListPage . ' selected>' . $ListPage . '</option>';
                          } else {
                              echo '<option VALUE=' . $ListPage . '>' . $ListPage . '</option>';
                          }
                          $ListPage++;
                      }
                      echo '</select>
                  <input type=submit name="Go1" VALUE="' . _('Ir') . '">
                  <input type=submit name="Previous" VALUE="' . _('Anterior') . '">
                  <input type=submit name="Next" VALUE="' . _('Siguiente') . '">';
                      echo '</div>';
                  }
                  echo '<form action=' . $_SERVER ['PHP_SELF'] . ' method=post name="form" enctype="multipart/form-data">';
                  echo '<table width="100%" cellpadding=1 colspan=7 class="table table-striped table-bordered">';
                  //echo '<tr><td colspan="7" style="text-align:center;"><b><div class="centre">'. $msg . '</div></b></td></tr>';
                  $TableHeader = '<tr>
              
                  <th class="text-center bgc8" style="color:#fff;">' . _('NO. FUNCIÓN') . '</th>
                  <th class="text-center bgc8" style="color:#fff;">' . _('NOMBRE') . '</th>
                  <th class="text-center bgc8" style="color:#fff;">' . _('SUBMÓDULO') . '</th>
                  <th class="text-center bgc8" style="color:#fff;">' . _('COMENTARIOS'). '</th>
                  
                </tr>';
          
                  echo $TableHeader;
                  $j = 1;
                  $k = 0; //row counter to determine background colour
                  $RowIndex = 0;
              }
            
              if (DB_num_rows($resultbusqueda)>0) {
                  while (($myrow=DB_fetch_array($resultbusqueda))) {
                      if ($k==1) {
                         // echo '<tr class="EvenTableRows">';
                          $k=0;
                      } else {
                         // echo '<tr class="OddTableRows">';
                          $k=1;
                      }

                      if ($modulo != $myrow['module']) {
                          $modulo=$myrow['module'];
                          $TableHeaderModulo='<tr class="text-left"> <td colspan="4"><b>'.$modulo.'</b></td> </tr>';
                          echo $TableHeaderModulo;
                      }

                      echo ("<tr><td> <input class='btn bgc8' style='font-size:12px;height:24px; color:#fff' type='submit' name='Select' VALUE='".$myrow['functionid']."'/></td>");
                     // echo('<tr ><td style="color:#fff;"><component-button type="submit" id="Select" name="Select" value="'.$myrow['functionid'].'"></component-button></td>');
                      echo ("<td nowrap style=font-size:12px;font-weight:normal;>".$myrow['title']."</font></td>");
                      echo ("<td style=font-size:12px;font-weight:normal;>".$myrow['module']."</font></td>");
                      echo ("<td style=font-size:12px;font-weight:normal;>".$myrow['comments']."</font></td>");
                
                      $j++;
                    //if ($j == 11 AND ($RowIndex+1 != $_SESSION['DisplayRecordsMax'])){
                      if ($j == 11) {
                          $j=1;
                          echo $TableHeader;
                      }
          
                      $RowIndex++;
              //end of page full new headings if
                  }
                //end of while loop
              }
              echo '</table></form>';
          }
          //end if results to show
          if (!isset($_POST['CSV'])) {
              if (isset($ListPageMax) and $ListPageMax>1) {
                  echo "<p><div class=centre>&nbsp;&nbsp;" . $_POST['PageOffset'] . ' ' . _('de') . ' ' . $ListPageMax . ' ' . _('Paginas') . '. ' . _('Ir a la Pagina') . ': ';
          
                  echo '<select name="PageOffset2">';
          
                  $ListPage=1;
                  while ($ListPage <= $ListPageMax) {
                      if ($ListPage == $_POST['PageOffset']) {
                          echo '<option VALUE=' . $ListPage . ' selected>' . $ListPage . '</option>';
                      } else {
                          echo '<option VALUE=' . $ListPage . '>' . $ListPage . '</option>';
                      }
                      $ListPage++;
                  }
                  echo '</select>
                <input type=submit name="Go2" VALUE="' . _('Ir') . '">
                <input type=submit name="Previous" VALUE="' . _('Anterior') . '">
                <input type=submit name="Next" VALUE="' . _('Siguiente') . '">';
              }
            //end if results to show
            //  echo '</div>';
          }
          ?>
        </td>
      </tr>
    </table>
</form>

<?php   
if (isset($resultbusqueda) and (!isset($_SESSION['FunctionID']) OR isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))) {
  $ListCount=DB_num_rows($resultbusqueda);
  //echo "sql:".$SQL;
  $ListPageMax=ceil($ListCount/$_SESSION['DisplayRecordsMax']);
  if (!isset($_POST['CSV'])) {
    if (isset($_POST['Next'])) {
      if ($_POST['PageOffset'] < $ListPageMax) {
        $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
      }
    }

    if (isset($_POST['Previous'])) {
      if ($_POST['PageOffset'] > 1) {
        $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
      }
    }

    echo "<input type=\"hidden\" name=\"PageOffset\" VALUE=\"". $_POST['PageOffset'] ."\"/>";

    if ($ListPageMax >1) {
      echo "<p><div class=centre>&nbsp;&nbsp;" . $_POST['PageOffset'] . ' ' . _('de') . ' ' . $ListPageMax . ' ' . _('Paginas') . '. ' . _('Ir a la Pagina') . ': ';

      echo '<select name="PageOffset1">';

      $ListPage=1;
      while($ListPage <= $ListPageMax) {
        if ($ListPage == $_POST['PageOffset']) {
          echo '<option VALUE=' . $ListPage . ' selected>' . $ListPage . '</option>';
        } else {
          echo '<option VALUE=' . $ListPage . '>' . $ListPage . '</option>';
        }
        $ListPage++;
      }
      echo '</select>
        <input type=submit name="Go1" VALUE="' . _('Ir') . '">
        <input type=submit name="Previous" VALUE="' . _('Anterior') . '">
        <input type=submit name="Next" VALUE="' . _('Siguiente') . '">';
      echo '</div>';
    }
    //echo '<table width="90%" cellpadding=1 colspan=7 BORDER=0 cellspacing=1>';
    //echo '<tr><td colspan="7" style="text-align:center;"><b><div class="centre">'. $msg . '</div></b></td></tr>';
    $TableHeader = '<tr>
    
        <th>' . _('NO. FUNCIÓN') . '</th>
        <th>' . _('NOMBRE') . '</th>
        <th>' . _('SUBMÓDULO') . '</th>
        <th>' . _('COMENTARIOS'). '</th>
        
      </tr>';

    //echo $TableHeader;
    $j = 1;
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    
    
  }
  
  if (DB_num_rows($resultbusqueda)>0){

    while (($myrow=DB_fetch_array($resultbusqueda)) ) {

      if ($k==1){
        echo '<tr class="EvenTableRows">';
        $k=0;
      } else {
          echo '<tr class="OddTableRows">';
        $k=1;
      }
      
      if ($modulo != $myrow['module']){
        $modulo=$myrow['module'];
        $TableHeaderModulo='<tr class="OddTableRows"> 
                    <th colspan="4">' . _(''.$modulo.'') . '</th>
                  </tr>';
        echo $TableHeaderModulo;
      }
      
      printf("<td>
              <input style=font-size:9px;height:20px; type=submit name='Select' VALUE='%s'</td>
        <td nowrap style=font-size:10px;font-weight:normal;>%s</font></td>
        <td style=font-size:10px;font-weight:normal;>%s</font></td>
        <td style=font-size:10px;font-weight:normal;>%s</font></td>
        </tr>",
        $myrow['functionid'],
        $myrow['title'],
        $myrow['module'],
        $myrow['comments']
        );
      
      $j++;
      //if ($j == 11 AND ($RowIndex+1 != $_SESSION['DisplayRecordsMax'])){
      if ($j == 11 ){
        $j=1;
        echo $TableHeader;
      }

    $RowIndex++;
    //end of page full new headings if
    }
    //end of while loop
    
  }
  echo '</table>';
}

if (!isset($_POST['CSV'])) {
  if (isset($ListPageMax) and $ListPageMax>1) {
    echo "<p><div class=centre>&nbsp;&nbsp;" . $_POST['PageOffset'] . ' ' . _('de') . ' ' . $ListPageMax . ' ' . _('Paginas') . '. ' . _('Ir a la Pagina') . ': ';

    echo '<select name="PageOffset2">';

    $ListPage=1;
    while($ListPage <= $ListPageMax) {
      if ($ListPage == $_POST['PageOffset']) {
        echo '<option VALUE=' . $ListPage . ' selected>' . $ListPage . '</option>';
      } else {
        echo '<option VALUE=' . $ListPage . '>' . $ListPage . '</option>';
      }
      $ListPage++;
    }
    echo '</select>
      <input type=submit name="Go2" VALUE="' . _('Ir') . '">
      <input type=submit name="Previous" VALUE="' . _('Anterior') . '">
      <input type=submit name="Next" VALUE="' . _('Siguiente') . '">';
  }
  //end if results to show
 
}

if ($_POST['Select']!="" or $_SESSION['FunctionID']!="") {
    if ($_POST['Select']!="") {
        $SQL = "SELECT * from $sec_functions where  functionid='" . $_POST['Select'] . "'";
        $_SESSION['FunctionID'] = $_POST['Select'];
    } else {
        $SQL = "SELECT * from $sec_functions where  functionid='" . $_SESSION['FunctionID'] . "'";
    }
    $ErrMsg = _('El numero de funcion no se encuentra, por que ');
    $result = DB_query($SQL, $db, $ErrMsg);
    if ($myrow=DB_fetch_row($result)) {
        $funcionname = $myrow[2];
        $_SESSION['FunctionID']=$myrow[0];
    }
    unset($result);
  
    //echo '<tr><td valign="top"><form action='.$_SERVER['PHP_SELF'] . "?" . SID. ' method=post>';
    echo '<form action='.$_SERVER['PHP_SELF'] . "?" . SID. ' method=post>';
    //echo '<table cellpadding=3 border=1 width="100%">
    //<tr>
      //<th colspan=2>' . _('FUNCION: '). $funcionname . '</th>
      //</td>';
      //echo '</tr>';
    echo "<h4>". _('FUNCIÓN: '). $funcionname."</h4>" ;
                        /* trae funciones*/
                $sql = "SELECT distinct P.profileid as profilea,
           FP.functionid as functiona,
           P.name as title
          
      FROM sec_profiles P left join  sec_funxprofile FP on P.profileid = FP.profileid
               and FP.functionid=".  $myrow[0].", $sec_functions F
      WHERE F.functionid=".$myrow[0]."
      order by  P.profileid,P.name asc ";
      
                    $ReFuntion = DB_query($sql, $db);
                    $condfuntion=false;
        $z=0;
    if (DB_num_rows($ReFuntion)>0) {
            echo "<table width=50% class='table table-striped table-bordered'>";
            $condfuntion=true;
        echo '<thead>
          <th class="bgc8" style="color:#fff">
            <font style=font-size:14px;>'._('PERFILES').'</font>
          </th>
        </tehad>';
    }
        $nombrecategoryant = "";
    while ($ResFuntion = DB_fetch_array($ReFuntion)) {
            $Funtionid=$ResFuntion['profilea'];
            $nameFuntion=strtolower($ResFuntion['title']);
        $Funtionval=$ResFuntion['functiona'];
          
        if (is_null($Funtionval)) {
            echo '<tr>
          <td>
              <input type=checkbox name=funcion'.$z.'>
            <font style=font-size:12px;>'.$Funtionid.' '.ucwords($nameFuntion).'</font>
            <input type=hidden name=fun'.$z.' value=' .$Funtionid . '>
          </td>
          </tr>';
        } else {
            echo '<tr>
          <td >
               <input type=checkbox name=funcion'.$z.' checked>
            <font style=font-size:12px;>'.str_repeat('0', 3-strlen($Funtionid)).$Funtionid.' '.ucwords($nameFuntion).'</font>
            <input type=hidden name=fun'.$z.' value=' .$Funtionid . '>
          </td>
              </tr>';
        }
        $nombrecategoryant=$nombrecategoria;
            $z=$z+1;
    }//Fin de while para extraer funciones
    echo '</table>';
    echo '<div class="centre"><input type="hidden" name="total" value="' .$z . '"></div>';
    echo '<div class="centre"><input type="submit" class="btn bgc8" style="color:#fff;" name="registro" value="' . _('Registrar') . '"></div></form>';
    //echo '<component-button type="submit" id="registro" name="registro"  value="Registrar"></component-button>';
    //echo '</table>';
    echo '</td></tr>';
       
    # *********************************************************
    # ********************************************************
}
 echo '</div>';
?>

<?php
    include('includes/footer_Index.inc');
?>