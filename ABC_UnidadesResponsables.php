<?php
/**
 * ABC de Unidades Responsables
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 02/08/2017
 * Se realizan operación pero el Alta, Baja y Modificación
 */

$PageSecurity = 5;
include 'includes/session.inc';
$funcion = 2241;
//alfredo include "includes/SecurityUrl.php";
$title = traeNombreFuncion($funcion, $db);
include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
//Librerias GRID
include('javascripts/libreriasGrid.inc');

$ErrMsg = _('');

$permisoMod = Havepermission($_SESSION['UserID'], 2279, $db); // Modificar datos de la Unidad Responsable

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$InputError=0;
?>

<script type="text/javascript" src="javascripts/ABC_UnidadesResponsables.js?v=<? rand();?>"></script>


  <!--Modal/Modificar-->
<!--Modal/Modificar-->
<div class="modal fade" id="ModalUR" name="ModalUR" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document" name="ModalGeneralTam" id="ModalGeneralTam">
    <div class="modal-content">
      <div class="navbar navbar-inverse navbar-static-top">

          <!--Contenido Encabezado-->
      <div class="col-md-12 menu-usuario">
        <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
      </div>
      <div id="navbar" class="navbar-collapse collapse">
        <div class="nav navbar-nav">
          <div class="title-header">
            <div id="ModalUR_Titulo" name="ModalUR_Titulo"></div>
          </div>
        </div>
      </div>
      <div class="linea-verde"></div>
    </div>
    <!-- <div class="modal-body" id="ModalUR_Mensaje" name="ModalUR_Mensaje">

      <div class="modal-body" id="ModalUR_Mensaje" name="ModalUR_Mensaje">
        <div class="panel-body" style="text-align: left;">
          <div class="col-md-4 col-xs-12">
                <?php
                if (isset($_GET['Action']) and $_GET['Action']==='edit') {
                    ?>
                    <component-label-text label="UR:" id="txtClave" name="txtClave" value=""></component-label-text>
                    <input type="hidden" name="clave" id="clave" value="" />
                    <?php
                } else {
                    ?>
                    <component-text-label label="UR:" id="clave" name="clave"
                      placeholder="UR" title="UR"
                      value="" maxlength="3"></component-text-label>
                    <?php
                }
                ?>
              <component-text-label label="Cliente Default Tienda:" id="tagdebtorno" name="tagdebtorno"
                placeholder="Cliente Default Tienda" title="Cliente Default Tienda"
                value="<?php echo $tagdebtorno; ?>" maxlength="255" style="display: none;"></component-text-label>
              <br>

          </div>
          <div class="col-md-4 col-xs-12">
            <input type="hidden" name="reference" id="reference" value="" />
            <component-text-label label="Nombre UR:" id="description" name="description"
                placeholder="Nombre UR" title="Nombre UR"
                value="" maxlength="50"></component-text-label>
            <br>
            <div class="form-inline row" style="display: none;">
                <div class="col-md-3">
                    <span><label>Tipo de Facturación: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="tipofact" name="tipofact" class="form-control tipofact">
                      <option value="0">Seleccionar...</option>
                        <?php
                        $SQL = "SELECT typeinvoice, CONCAT(typeinvoice, ' - ', nameinvoice) as nameinvoice
                      FROM config_typeinvoice WHERE active= 1 ORDER BY typeinvoice ";
                        $Result=  DB_query($SQL, $db);
                        while ($myrow = DB_fetch_array($Result)) {
                            $selected = "";
                            if (isset($typeinvoice) and $myrow['typeinvoice']==$typeinvoice) {
                                $selected = "selected";
                            }
                            echo '<option value="'.$myrow['typeinvoice'] . '" '.$selected.'>' . ($myrow['nameinvoice'])."</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
          </div>
          <div class="col-md-4 col-xs-12">
            <div class="form-inline row" style="display: none;">
                <div class="col-md-3">
                    <span><label>Sucursal: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="areacode" name="areacode" class="form-control areacode">
                        <?php
                        $SQL = "SELECT areas.areacode, CONCAT(areas.areacode,' - ',areas.areadescription) as name
                      FROM areas
                      JOIN tags ON tags.areacode = areas.areacode
                      JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
                      WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
                      GROUP BY areas.areacode, areas.areadescription";
                        $Result=  DB_query($SQL, $db);
                        while ($myrow = DB_fetch_array($Result)) {
                            $selected = "";
                            if (isset($areacode) and $myrow['areacode']==$areacode) {
                                $selected = "selected";
                            }
                            echo '<option value="'.$myrow['areacode'] . '" '.$selected.'>' . ($myrow['name'])."</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-inline row" style="display: none;">
                <div class="col-md-3">
                    <span><label>Departamento: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="u_department" name="u_department" class="form-control u_department">
                        <?php
                        $SQL = "SELECT u_department, CONCAT(u_department, ' - ', department) as department  FROM departments ORDER BY u_department";
                        $Result=  DB_query($SQL, $db);
                        while ($myrow = DB_fetch_array($Result)) {
                            $selected = "";
                            if (isset($depto) and $myrow['u_department']==$depto) {
                                $selected = "selected";
                            }
                            echo '<option value="'.$myrow['u_department'] . '" '.$selected.'>' . ($myrow['department'])."</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-inline row">
                <div class="col-md-3">
                    <span><label>Tipo: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="cmbTipo" name="cmbTipo" class="form-control cmbTipo">
                      <option value="0">Seleccionar...</option>
                      <option value="Central">Central</option>
                      <option value="Estatal">Estatal</option>
                    </select>
                </div>
            </div>
            <component-text-label label="Proveedor Default Tienda:" id="tagsupplier" name="tagsupplier"
              placeholder="Proveedor Default Tienda" title="Proveedor Default Tienda"
              value="<?php echo $tagsupplier; ?>" maxlength="255" style="display: none;"></component-text-label>
          </div>


                  <div class="row" style="text-align: left;">

                    <div class="col-xs-12 col-md-12" >
                  <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                      <span><label>Dependencia: </label></span>
                  </div>
                  <div class="col-xs-9 col-md-9">
                      <select id="legalid" name="legalid" class="form-control legalid">
                        <option value="0">Seleccionar...</option>
                            <?php
                            $SQL = "SELECT legalbusinessunit.legalid, CONCAT(legalbusinessunit.legalid, ' - ', legalbusinessunit.legalname) as legalname
                        FROM sec_unegsxuser u, tags t
                        JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid
                        WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "'
                        GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY legalbusinessunit.legalid ";
                            $Result=  DB_query($SQL, $db);
                            $num = 1;
                            while ($myrow = DB_fetch_array($Result)) {
                                $selected = "";
                                if (isset($legalid) and $myrow['legalid']==$legalid) {
                                    $selected = "selected";
                                } else if ($num == 1) {
                                    $selected = "selected";
                                }
                                echo '<option  value="'.$myrow['legalid'] . '" '.$selected.'>' . ($myrow['legalname'])."</option>";
                                $num ++;
                            }
                            ?>
                      </select>
                  </div>
              </div>
              </div>

          <div class="col-xs-12 col-md-12" style="text-align: center;"> <h3>Domicilio</h3> </div>

          <div class="col-md-12 col-xs-12">

            <div class="col-xs-12 col-md-4">
            <component-text-label label="Calle." id="address1" name="address1"
                placeholder="Calle" title="Calle"
                value="" maxlength="255"></component-text-label>
            </div>
              <div class="col-xs-12 col-md-4">
            <component-number-label label="Número Exterior:" id="address2" name="address2"
                placeholder="Número Exterior" title="Número Exterior"
                value="" maxlength="255"></component-number-label>
              </div>

            <div class="col-xs-12 col-md-4">
                 <component-number-label label="Número Interior:" id="txtNumInterior" name="txtNumInterior"
                placeholder="Número Interior" title="Número Interior"
                value="" maxlength="255"></component-number-label>
            </div>


          </div>

          <div class="col-xs-12 col-md-12">
            <br>
            <div class="col-xs-4 col-md-4">
              <component-text-label label="Colonia:" id="address3" name="address3"
              placeholder="Colonia" title="Colonia"
              value="" maxlength="255"></component-text-label>
              </div>

              <div class="col-xs-8 col-md-8">
                <component-text-label label="Delegación /Municipio:" id="address4" name="address4"
              placeholder="Delegación/Municipio" title="Delegación/Municipio"
              value="" maxlength="255"></component-text-label>
            </div>

          </div>
          <div class="col-xs-4 col-md-4">
         <br>
            <component-text-label label="Estado:" id="address5" name="address5"
                placeholder="Estado" title="Estado"
                value="" maxlength="255"></component-text-label>
          </div>

           <div class="col-xs-4 col-md-4">
            <br>
                <component-number-label label="Código Postal:" id="cp" name="cp"
              placeholder="Código Postal" title="Código Postal"
              value="" maxlength="255"></component-number-label>
            </div>

        </div>
        <div class="modal-footer">
          <component-button type="button" id="btn" name="btn" data-dismiss="modal" onclick="if (fnAgregar()==false) { $('#ModalUR').modal('show'); }" value="Guardar"></component-button>
          <component-button type="button" id="btn" name="btn" data-dismiss="modal" value="Cerrar"></component-button>
        </div>
      </div>
    </div> -->
    <div name="divMensajeOperacion" id="divMensajeOperacion" class="m10"></div>
    <div class="modal-body" id="ModalUR_Mensaje" name="ModalUR_Mensaje">

      <div class="modal-body" id="ModalUR_Mensaje" name="ModalUR_Mensaje">
        <div class="panel-body" style="text-align: left;">
          <div class="col-md-12 col-xs-12">
                <?php
                if (isset($_GET['Action']) and $_GET['Action']==='edit') {
                    ?>
                    <component-label-text label="UR:" id="txtClave" name="txtClave" value=""></component-label-text>
                    <input type="hidden" name="clave" id="clave" value="" />
                    <?php
                } else {
                    ?>
                    <component-text-label label="UR:" id="clave" name="clave"
                      placeholder="UR" title="UR"
                      value="" maxlength="5"></component-text-label>
                    <?php
                }
                ?>
          </div>
          <div class="col-md-12 col-xs-12 mt10">
            <input type="hidden" name="reference" id="reference" value="" />
            <component-text-label label="Descripción:" id="description" name="description"
                placeholder="Descripción" title="Descripción"
                value="" maxlength="50"></component-text-label>
          </div>
        </div>
        <div class="modal-footer">
          <component-button type="button" id="btn" name="btn" onclick="if (fnAgregar()==false) { $('#ModalUR').modal('show'); }" value="Guardar"></component-button>
          <component-button type="button" id="btn" name="btn" data-dismiss="modal" value="Cerrar"></component-button>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
<?php

if (isset($_GET['Action']) and $_GET['Action']=='edit' and $permisoMod == 1) {
    ?>
        <div class="col-md-12 col-xs-12" align="center">
            <component-button type="submit" id="update" name="update" value="Actualiza URG"></component-button>
        </div>
    <?php
} else {
    ?>

<!-- tabla de busqueda -->
<div class="row">
  <div name="divTabla" id="divTabla">
    <div id="divCatalogo" name="divCatalogo"></div>
  </div>
</div><!-- .row -->

<!-- botones de accion -->
<div class="row pt10">
  <div class="panel panel-default">
    <div align="center">
      <component-button type="button" id="btnAgregar" name="btnAgregar" onclick="fnAgregarCatalogoModal()" value="Nuevo" class="glyphicon glyphicon-plus"></component-button>
      <br>
      <br>
    </div>
  </div>
</div><!-- .row --><!-- .row -->

<!--Modal Eliminar -->
<div class="modal fade" id="ModalUREliminar" name="ModalUREliminar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document" name="ModalGeneralTam" id="ModalGeneralTam">
    <div class="modal-content">
      <div class="navbar navbar-inverse navbar-static-top">
        <!--Contenido Encabezado-->
        <div class="col-md-12 menu-usuario">
          <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <div class="nav navbar-nav">
            <div class="title-header">
              <h3><span class="glyphicon glyphicon-info-sign"></span> Información</h3>
            </div>
          </div>
        </div>
        <div class="linea-verde"></div>
      </div>
      <div class="modal-body" id="ModalUREliminar_Mensaje" name="ModalUREliminar_Mensaje">
        <!--Mensaje o contenido-->
      </div>
      <div class="modal-footer">
        <component-text type="hidden" label="UE: " id="txtClaveEliminar" name="txtClaveEliminar" placeholder="UE"></component-text>
        <component-button type="button" id="btn" name="btn" data-dismiss="modal" onclick="fnEliminarEjecuta()" value="Eliminar"></component-button>
        <component-button type="button" id="btn" name="btn" data-dismiss="modal" value="Cerrar"></component-button>
      </div>
    </div>
  </div>
</div>


    <?php
}




//echo "<script>defaultControl(document.form.description);</script>";

include 'includes/footer_Index.inc';
?>

<script type="text/javascript">
  // Aplicar formato del SELECT
fnFormatoSelectGeneral(".legalid");
fnFormatoSelectGeneral(".areacode");
fnFormatoSelectGeneral(".u_department");
fnFormatoSelectGeneral(".tipofact");
fnFormatoSelectGeneral(".cmbTipo");
</script>
