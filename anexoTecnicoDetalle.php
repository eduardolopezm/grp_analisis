<?php
/**
 * Panel de anexo técnico.
 *
 * @category     Panel
 * @package      ap_grp
 * @version      0.1
 * @link /anexoTecnicoDetalle.php
 * Fecha Creación: 29.12.17
 * Se genera el presente programa para la visualización de la información
 * de los anexos técnicos que se generan para las inquisiciones.
 */
/* DECLARACION DE VARIABLES */
$PageSecurity = 1;
$PathPrefix = './';
// $funcion = 2323;
$funcion = 2322;
/* INCLUCION DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . "includes/SecurityUrl.php");
include($PathPrefix . 'includes/ConnectDB.inc');
require($PathPrefix . 'includes/session.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
$title= traeNombreFuncion($funcion, $db,'Generación de Anexo Técnico');
require 'includes/header.inc';
require 'javascripts/libreriasGrid.inc';

# comprobacion de datos enviados
$folio = !empty($_GET['folio'])?$_GET['folio']:'';
$estatusAnexo = !empty($_GET['status'])?$_GET['status']:'';
$permisoAutorizador = Havepermission($_SESSION['UserID'], 2330, $db );

$ocultaDepencencia = 'hidden';
?>
<script src="javascripts/anexoTecnicoDetalle.js?v=<?= rand(); ?>"></script>
<script>
    window.folio = "<?= $folio; ?>";
    window.estatusAnexo = "<?= $estatusAnexo; ?>";
    window.permisoAutorizador = "<?= $permisoAutorizador; ?>";
</script>
<div class="row">
    <div class="col-sm-6 col-sm-offset-3" id="mensaje"></div>
</div>
<div class="row">
    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading h35">
                <h4 class="panel-title">
                    <div class="fl text-left">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#closeTab" aria-expanded="true" aria-controls="collapseOne">
                            <!-- <b>Criterios de Filtrado</b> -->
                            <?php
                                # if(!empty($_GET['folio'])){ echo '<strong>MODIFICACIONES DE FOLIO : '.$_GET['folio'].'</strong>'; }
                                # else{ echo "<strong>CAPTURA DE ANEXO TÉCNICO</strong>"; }
                                // if(!empty($folio) && empty($estatusAnexo)){ echo '<strong>Modificaciones de Folio : '.$_GET['folio'].'</strong>'; }
                                // else if(!empty($folio) && !empty($estatusAnexo)){ echo '<strong>Visualización de Folio : '.$_GET['folio'].'</strong>'; }
                                // else{ echo "<strong>Captura de Anexo Técnico</strong>"; }
                                echo "<strong>Encabezado</strong>";
                            ?>
                        </a>
                    </div>
                </h4>
            </div><!-- .panel-heading -->
            <div class="panel-body">
                <div class="row" id="form-add">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="col-md-6">
                                <div class="form-inline form-group row <?= $ocultaDepencencia ?>">
                                    <div class="col-md-3">
                                        <span><label class="control-label">Dependencia: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnTraeUnidadesResponsables(this.value, 'selectUnidadNegocio')"></select>
                                    </div>
                                </div><!-- form-inline form-group row -->
                                <div class="form-inline form-group row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label class="control-label">UR: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="selectUnidadNegocio" name="selectUnidadNegocio"  data-todos="true" class="form-control selectUnidadNegocio"  onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')" ></select>
                                        <!-- <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" multiple="multiple" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')"></select> -->
                                    </div>
                                </div><!-- form-inline form-group row -->
                                <div class="form-inline form-group row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label class="control-label">UE: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora" data-todos="true"></select>
                                    </div>
                                </div><!-- form-inline form-group row -->
                            </div><!-- -col-md-4 -->
                            <div class="col-md-6">
                                <div class="form-inline form-group row">
                                    <div class="col-md-3">
                                        <span><label class="control-label">Folio: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <label  id="folioLabel" class="wA bgc12 mt5 fts22 ftc8 borderGray borderRadius plr10 hidden"></label>
                                        <input type="number" class="form-control w100p hidden" id="folio" name="folio" maxlength="11"
                                            <?php
                                                if(empty($_GET['folio'])){ echo 'readonly placeholder="Sera generado"'; }
                                                else{ echo 'readonly value="' . $_GET['folio'] . '"'; }
                                            ?>
                                        />
                                    </div>
                                </div><!-- form-inline form-group row -->
                                <div class="form-inline form-group row">
                                    <div class="col-md-3">
                                        <span><label class="control-label">Referencia: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <component-textarea id="antecedentes" name="antecedentes" rows="3" class="w100p"></component-textarea>
                                    </div>
                                </div><!-- form-inline form-group row -->
                            </div><!-- -col-md-4 -->
                        </div><!-- .col-sm-12 -->
                    </div><!-- .row -->
                </div><!-- #form-add -->

            </div><!-- .panel-body -->
        </div><!-- .panel -->
    </div><!-- .panel-group -->
</div><!-- .row -->

<div class="row">
    <nav class="row nav bgc8 fts10 borderGray w100p ftc2">
        <div class="col-lg-12 col-md-12 col-sm-12 p0 m0">
            <?php if(empty($estatusAnexo)){ ?>
                <div class="w3p pt3  falsaCabeceraTabla">
                        <span id="add" class="btn btn-default btn-xs glyphicon glyphicon-plus"></span>
                </div>
            <?php } else if ($estatusAnexo == 6 && $permisoAutorizador == 1){ ?>
                <div class="w3p pt3  falsaCabeceraTabla">
                        <span id="add" class="btn btn-default btn-xs glyphicon glyphicon-plus"></span>
                </div>
            <?php }  // if $estatusAnexo ?>
            <div class="w3p  falsaCabeceraTabla"> # </div>
            <div class="w10p  falsaCabeceraTabla"><label>Partida</label></div>
            <div class="w12p  falsaCabeceraTabla"><label>Clave</label></div>
            <div class="w14p  falsaCabeceraTabla"><label>Bien/Servicio</label></div>
            <div class="w40p  falsaCabeceraTabla"><label>Especificación</label></div>
            <div class="w5p  falsaCabeceraTabla"><label>Unidad</label></div>
            <div class="w7p  falsaCabeceraTabla"><label>Cantidad</label></div>
            <!-- <div class="w6p  falsaCabeceraTabla"><label>Costo U</label></div>
            <div class="w7p  falsaCabeceraTabla"><label>Total</label></div> -->
            <div class="w6p  falsaCabeceraTabla"><label>Garantía</label></div>
        </div>
    </nav>
    <div id="tbl-products"></div>
</div>

<!-- botones de acción -->
<div class="row <?php if(empty($_GET['folio'])){ echo 'pt20'; } ?>">
    <?php if(!empty($folio) && empty($estatusAnexo)){ ?>
        <component-button type="button" id="btn-update" class="glyphicon glyphicon-floppy-disk"  value="Guardar"></component-button>
        <component-button type="button" class="glyphicon glyphicon-trash regresar"  value="Cancelar"></component-button>
    <?php }else if(!empty($folio) && $estatusAnexo == 6 && $permisoAutorizador != 0 ){ ?>
        <component-button type="button" id="btn-update" class="glyphicon glyphicon-floppy-disk"  value="Guardar"></component-button>
        <component-button type="button" class="glyphicon glyphicon-trash regresar"  value="Cancelar"></component-button>
    <?php }else if(empty($folio) && empty($estatusAnexo)){ ?>
        <component-button type="button" id="btn-add" class="glyphicon glyphicon-floppy-disk"  value="Guardar"></component-button>
        <component-button type="button" class="glyphicon glyphicon-trash regresar"  value="Cancelar"></component-button>
    <?php }// end if ?>
        <component-button type="button" class="glyphicon glyphicon-home regresar"  value="Regresar"></component-button>
    <!-- <buttton class="btn btn-primary btn-green" id="regresar"><i class="fa fa-reply"></i> Regresar</buttton> -->
</div>

<div class="row">
    <div id="tabla">
    </div>
</div><!-- .row -->

<?php require 'includes/footer_Index.inc'; ?>
