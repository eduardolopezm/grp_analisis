<?php
include 'includes/session.inc';
$funcion = 2262;
$title = traeNombreFuncion($funcion, $db);
include 'includes/header.inc';

include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
//Librerias GRID
include 'javascripts/libreriasGrid.inc';
?>
<script type="text/javascript" src="javascripts/grp_configuracion_reportes.js"></script>
<!-- Nav tabs -->

<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
            Búsqueda
          </a>
        </div>
      </h4>
    </div>
    <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
  
        <div class="col-md-7">
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Dependencía: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnCambioRazonSocial()">
                    <option value='0'>Seleccionar...</option>
                  </select>
              </div>
          </div>
          <br>
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>UR: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadNegocio()">
                  </select>
              </div>
          </div>
          <br>
          
      </div>
    </div>
  </div>


<div class="tabbable boxed parentTabs">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#set1" style="width: '50%'">Reportes CONAC</a>
        </li>
        <li><a href="#set2">Reportes LDF</a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade active in" id="set1" style="width: '50%'">
            <div class="tabbable">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#conacSituacionFinanciera">Situación Financiera</a>
                    </li>
                    <li><a href="#sub12">Estado de actividades</a>
                    </li>
                    <li><a href="#sub13">Estado de Variación en la Hacienda Pública</a>
                    </li>
                    <li><a href="#sub14">Flujo de Efectivo</a>
                    </li>
                    <li><a href="#sub15">Estado analítico de ingresos</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade active in" id="conacSituacionFinanciera">
                        <div>
                            <H2 >  </H2>
                                    <H3> ACTIVO </H3>
                                    <H4> Activo circulante </H4>
                                    <div class="input-group">
                                      <div id="R1P001" class="col-md-3">
                                        <span class="input-group-addon" style="background: none; border: none;"> Efectivo y Equivalentes </span>
                                      </div>
                                      <div id="R1P001" class="col-md-9">
                                        <select id="selectSituacionFinancieraEfectivosyEquivalentes" name="selectSituacionFinancieraEfectivosyEquivalentes[]" class="form-control selectSituacionFinancieraEfectivosyEquivalentes" multiple="multiple">
                                        </select>
                                      </div">
                                      
                                    </div>
                                    </div>
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P002"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Derechos a Recibir Efectivo o Equivalentes </span>
                                      <select id="selectSituacionFinancieraDerechoARecibirEfectivosyEquivalentes" name="selectSituacionFinancieraDerechoARecibirEfectivosyEquivalentes[]" class="form-control selectSituacionFinancieraDerechoARecibirEfectivosyEquivalentes" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                    
                                    <div class="input-group">
                                      <div id="R1P003"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Derechos a Recibir Bienes o Servicios </span>
                                      <select id="selectSituacionFinancieraDerechosaRecibirBienesoServicios" name="selectSituacionFinancieraDerechosaRecibirBienesoServicios[]" class="form-control selectSituacionFinancieraDerechosaRecibirBienesoServicios" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>

                                    <div class="input-group">
                                      <div id="R1P004"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Inventarios </span>
                                      <select id="selectSituacionFinancieraInventarios" name="selectSituacionFinancieraInventarios[]" class="form-control selectSituacionFinancieraInventarios" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>

                                    <div class="input-group">
                                      <div id="R1P005"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Almacenes </span>
                                      <select id="selectSituacionFinancieraAlmacenes" name="selectSituacionFinancieraAlmacenes[]" class="form-control selectSituacionFinancieraAlmacenes" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P006"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Estimación por Pérdida o Deterioro de Activos Circulantes </span>
                                      <select id="selectSituacionFinancieraEstimacionporPerdidaoDeteriorodeActivosCirculantes" name="selectSituacionFinancieraEstimacionporPerdidaoDeteriorodeActivosCirculantes[]" class="form-control selectSituacionFinancieraEstimacionporPerdidaoDeteriorodeActivosCirculantes" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P007"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Otros Activos Circulantes </span>
                                      <select id="selectSituacionFinancieraOtrosActivosCirculantes" name="selectSituacionFinancieraOtrosActivosCirculantes[]" class="form-control selectSituacionFinancieraOtrosActivosCirculantes" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                    <br>
                                    <H4> Activo no circulante </H4>
                                    <br>
                                    <br>
                                    
                                    <div class="input-group">
                                      <div id="R1P008"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Inversiones Financieras a Largo Plazo </span>
                                      <select id="selectSituacionFinancieraInversionesFinancierasaLargoPlazo" name="selectSituacionFinancieraInversionesFinancierasaLargoPlazo[]" class="form-control selectSituacionFinancieraInversionesFinancierasaLargoPlazo" multiple="multiple">
                                      </select>
                                    </div>
                                    
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P009"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Derechos a Recibir Efectivo o Equivalentes a Largo Plazo</span>
                                      <select id="selectSituacionFinancieraDerechosaRecibirEfectivooEquivalentesaLargoPlazo" name="selectSituacionFinancieraDerechosaRecibirEfectivooEquivalentesaLargoPlazo[]" class="form-control selectSituacionFinancieraDerechosaRecibirEfectivooEquivalentesaLargoPlazo" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>

                                    <div class="input-group">
                                      <div id="R1P010"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Bienes Inmuebles, Infraestructura y Construcciones</span>
                                      <select id="selectSituacionFinancieraBienesInmueblesInfraestructurayConstruccionesenProceso" name="selectSituacionFinancieraBienesInmueblesInfraestructurayConstruccionesenProceso[]" class="form-control selectSituacionFinancieraBienesInmueblesInfraestructurayConstruccionesenProceso" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>

                                    <div class="input-group">
                                      <div id="R1P011"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Bienes Muebles</span>
                                      <select id="selectSituacionFinancieraBienesMuebles" name="selectSituacionFinancieraBienesMuebles[]" class="form-control selectSituacionFinancieraBienesMuebles" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P012"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Activos Intangibles</span>
                                      <select id="selectSituacionFinancieraActivosIntangibles" name="selectSituacionFinancieraActivosIntangibles[]" class="form-control selectSituacionFinancieraActivosIntangibles" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P013"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Depreciación, Deterioro y Amortización Acumulada de Bienes</span>
                                      <select id="selectSituacionFinancieraDepreciacionDeterioroyAmortizacionAcumuladadeBienes" name="selectSituacionFinancieraDepreciacionDeterioroyAmortizacionAcumuladadeBienes[]" class="form-control selectSituacionFinancieraDepreciacionDeterioroyAmortizacionAcumuladadeBienes" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P014"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Activos Diferidos</span>
                                      <select id="selectSituacionFinancieraActivosDiferidos" name="selectSituacionFinancieraActivosDiferidos[]" class="form-control selectSituacionFinancieraActivosDiferidos" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P015"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Estimación por Pérdida o Deterioro de Activos no Circulantes</span>
                                      <select id="selectSituacionFinancieraEstimacionporPerdidaoDeteriorodeActivosnoCirculantes" name="selectSituacionFinancieraEstimacionporPerdidaoDeteriorodeActivosnoCirculantes[]" class="form-control selectSituacionFinancieraEstimacionporPerdidaoDeteriorodeActivosnoCirculantes" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>

                                      
                                    

                                    <div class="input-group">
                                      <div id="R1P016"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Otros Activos no Circulantes </span>
                                      <select id="selectSituacionFinancieraOtrosActivosnoCirculantes" name="selectSituacionFinancieraOtrosActivosnoCirculantes[]" class="form-control selectSituacionFinancieraOtrosActivosnoCirculantes" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>


                                     <H3> PASIVO </H3>
                                    <H4> Pasivo circulante </H4>
                                    <div class="input-group">
                                      <div id="R1P021"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Cuentas por Pagar a Corto Plazo</span>
                                      <select id="selectSituacionFinancieraCuentasporPagaraCortoPlazo" name="selectSituacionFinancieraCuentasporPagaraCortoPlazo[]" class="form-control selectSituacionFinancieraCuentasporPagaraCortoPlazo" multiple="multiple">
                                      </select>
                                    </div>
                                  
                                  
                                    <br>

                                    <!--<div class="input-group">
                                      <div id="R1P022"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Todos</span>
                                      <select id="selectTodos" name="selectTodos[]" class="form-control selectTodos" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>-->

                                    <div class="input-group">
                                      <div id="R1P023"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Documentos por Pagar a Corto Plazo</span>
                                      <select id="selectSituacionFinancieraDocumentosporPagaraCortoPlazo" name="selectSituacionFinancieraDocumentosporPagaraCortoPlazo[]" class="form-control selectSituacionFinancieraDocumentosporPagaraCortoPlazo" multiple="multiple">
                                      </select>
                                    </div>
                                  
                                  
                                    <br>

                                    <div class="input-group">
                                      <div id="R1P024"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Porción a Corto Plazo de la Deuda Pública a Largo Plazo</span>
                                      <select id="selectSituacionFinancieraPorcionaCortoPlazodelaDeudaPublicaaLargoPlazo" name="selectSituacionFinancieraPorcionaCortoPlazodelaDeudaPublicaaLargoPlazo[]" class="form-control selectSituacionFinancieraPorcionaCortoPlazodelaDeudaPublicaaLargoPlazo" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P025"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Títulos y Valores a Corto Plazo</span>
                                      <select id="selectSituacionFinancieraTitulosyValoresaCortoPlazo" name="selectSituacionFinancieraTitulosyValoresaCortoPlazo[]" class="form-control selectSituacionFinancieraTitulosyValoresaCortoPlazo" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P026"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Pasivos Diferidos a Corto Plazo</span>
                                      <select id="selectSituacionFinancieraPasivosDiferidosaCortoPlazo" name="selectSituacionFinancieraPasivosDiferidosaCortoPlazo[]" class="form-control selectSituacionFinancieraPasivosDiferidosaCortoPlazo" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P027"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Fondos y Bienes de Terceros en Garantía y/o Administración a Corto Plazo</span>
                                      <select id="selectSituacionFinancieraFondosyBienesdeTercerosenGarantiayoAdministracionaCortoPlazo" name="selectSituacionFinancieraFondosyBienesdeTercerosenGarantiayoAdministracionaCortoPlazo[]" class="form-control selectSituacionFinancieraFondosyBienesdeTercerosenGarantiayoAdministracionaCortoPlazo" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P028"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Provisiones a Corto Plazo</span>
                                      <select id="selectSituacionFinancieraProvisionesaCortoPlazo" name="selectSituacionFinancieraProvisionesaCortoPlazo[]" class="form-control selectSituacionFinancieraProvisionesaCortoPlazo" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P029"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Otros Pasivos a Corto Plazo</span>
                                      <select id="selectSituacionFinancieraOtrosPasivosaCortoPlazo" name="selectSituacionFinancieraOtrosPasivosaCortoPlazo[]" class="form-control selectSituacionFinancieraOtrosPasivosaCortoPlazo" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>

                                    <H4> Pasivo no Circulante </H4>
                                    <br>
                                    <br>
                                    
                                    <div class="input-group">
                                      <div id="R1P024"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Cuentas por Pagar a Largo Plazo </span>
                                      <select id="selectSituacionFinancieraCuentasporPagaraLargoPlazo" name="selectSituacionFinancieraCuentasporPagaraLargoPlazo[]" class="form-control selectSituacionFinancieraCuentasporPagaraLargoPlazo" multiple="multiple">
                                      </select>
                                    </div>
                                    
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P026"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Documentos por Pagar a Largo Plazo</span>
                                      <select id="selectSituacionFinancieraDocumentosporPagaraLargoPlazo" name="selectSituacionFinancieraDocumentosporPagaraLargoPlazo[]" class="form-control selectSituacionFinancieraDocumentosporPagaraLargoPlazo" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P027"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Deuda Pública a Largo Plazo </span>
                                      <select id="selectSituacionFinancieraDeudaPublicaaLargoPlazo" name="selectSituacionFinancieraDeudaPublicaaLargoPlazo[]" class="form-control selectSituacionFinancieraDeudaPublicaaLargoPlazo" multiple="multiple">
                                      </select>
                                    </div>
                                    
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P028"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Pasivos Diferidos a Largo Plazo</span>
                                      <select id="selectSituacionFinancieraPasivosDiferidosaLargoPlazo" name="selectSituacionFinancieraPasivosDiferidosaLargoPlazo[]" class="form-control selectSituacionFinancieraPasivosDiferidosaLargoPlazo" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P029"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Fondos y Bienes de Terceros en Garantía y/o en Administración a Largo Plazo </span>
                                      <select id="selectSituacionFinancieraFondosyBienesdeTercerosenGarantiayoenAdministracionaLargoPlazo" name="selectSituacionFinancieraFondosyBienesdeTercerosenGarantiayoenAdministracionaLargoPlazo[]" class="form-control selectSituacionFinancieraFondosyBienesdeTercerosenGarantiayoenAdministracionaLargoPlazo" multiple="multiple">
                                      </select>
                                    </div>
                                    
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P030"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Provisiones a Largo Plazo</span>
                                      <select id="selectSituacionFinancieraProvisionesaLargoPlazo" name="selectSituacionFinancieraProvisionesaLargoPlazo[]" class="form-control selectSituacionFinancieraProvisionesaLargoPlazo" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                    
                                    <H3> HACIENDA PÚBLICA/PATRIMONIO </H3>
                                    <H4> Hacienda Pública/Patrimonio Contribuido </H4>
                                    <div class="input-group">
                                      <div id="R1P018"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Aportaciones</span>
                                      <select id="selectSituacionFinancieraAportaciones" name="selectSituacionFinancieraAportaciones[]" class="form-control selectSituacionFinancieraAportaciones" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P019"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Donaciones de Capital</span>
                                      <select id="selectSituacionFinancieraDonacionesdeCapital" name="selectSituacionFinancieraDonacionesdeCapital[]" class="form-control selectSituacionFinancieraDonacionesdeCapital" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                     <div class="input-group">
                                      <div id="R1P020"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Actualización de la Hacienda Pública/Patrimonio</span>
                                      <select id="selectSituacionFinancieraActualizaciondelaHaciendaPublicaPatrimonio" name="selectSituacionFinancieraActualizaciondelaHaciendaPublicaPatrimonio[]" class="form-control selectSituacionFinancieraActualizaciondelaHaciendaPublicaPatrimonio" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                    <H4>Hacienda Pública/Patrimonio Generado </H4>

                                    <div class="input-group">
                                      <div id="R1P021"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Resultados de Ejercicios (Ahorro/ Desahorro)</span>
                                      <select id="selectSituacionFinancieraResultadosdeEjerciciosAhorroDesahorro" name="selectSituacionFinancieraResultadosdeEjerciciosAhorroDesahorro[]" class="form-control selectSituacionFinancieraResultadosdeEjerciciosAhorroDesahorro" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                     <div class="input-group">
                                      <div id="R1P021"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Resultados de Ejercicios Anteriores</span>
                                      <select id="selectSituacionFinancieraResultadosdeEjerciciosAnteriores" name="selectSituacionFinancieraResultadosdeEjerciciosAnteriores[]" class="form-control selectSituacionFinancieraResultadosdeEjerciciosAnteriores" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P022"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Revalúos</span>
                                      <select id="selectSituacionFinancieraRevaluos" name="selectSituacionFinancieraRevaluos[]" class="form-control selectSituacionFinancieraRevaluos" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                    <div class="input-group">
                                      <div id="R1P023"></div>
                                      <span class="input-group-addon" style="background: none; border: none;">Reservas</span>
                                      <select id="selectSituacionFinancieraReservas" name="selectSituacionFinancieraReservas[]" class="form-control selectSituacionFinancieraReservas" multiple="multiple">
                                      </select>
                                    </div>
                                    <br>
                                  



                                    

                                    <div id="mensajesValidacionesR1"> </div>
                                    <component-button type="button" id="btnGrabar" name="btnGrabar" onclick="fnGrabarConfiguracionSituacionFinanciera()" value="Grabar configuración Reporte de Situación Financiera"></component-button>
                                    <component-button type="button" id="btnImprimir" name="btnImprimir" onclick="window.open('PrintSituacionFinanciera.php?PrintPDF=1&reporte=situacionfinanciera')" value="Imprimir Reporte de Situación Financiera"></component-button>

                            </div>
                    </div>
                    <div class="tab-pane fade" id="sub12">
                        <p>Tab 1.2</p> <!--inicio -->

                        <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Impuestos</span> 
                              <select id="selectEstadoDeActividadesImpuestos" name="selectEstadoDeActividadesImpuestos[]" class="form-control selectEstadoDeActividadesImpuestos" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Cuotas y Aportaciones de Seguridad Social</span> 
                              <select id="selectEstadoDeActividadesCuotasyAportacionesdeSeguridadSocial" name="selectEstadoDeActividadesCuotasyAportacionesdeSeguridadSocial[]" class="form-control selectEstadoDeActividadesCuotasyAportacionesdeSeguridadSocial" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Contribuciones de Mejoras</span> 
                              <select id="selectEstadoDeActividadesContribucionesdeMejoras" name="selectEstadoDeActividadesContribucionesdeMejoras[]" class="form-control selectEstadoDeActividadesContribucionesdeMejoras" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Derechos</span> 
                              <select id="selectEstadoDeActividadesDerechos" name="selectEstadoDeActividadesDerechos[]" class="form-control selectEstadoDeActividadesDerechos" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Productos de Tipo Corriente</span> 
                              <select id="selectEstadoDeActividadesProductosdeTipoCorriente" name="selectEstadoDeActividadesProductosdeTipoCorriente[]" class="form-control selectEstadoDeActividadesProductosdeTipoCorriente" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Aprovechamientos de Tipo Corriente</span> 
                              <select id="selectEstadoDeActividadesAprovechamientosdeTipoCorriente" name="selectEstadoDeActividadesAprovechamientosdeTipoCorriente[]" class="form-control selectEstadoDeActividadesAprovechamientosdeTipoCorriente" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Ingresos por Venta de Bienes y Servicios</span> 
                              <select id="selectEstadoDeActividadesIngresosporVentadeBienesyServicios" name="selectEstadoDeActividadesIngresosporVentadeBienesyServicios[]" class="form-control selectEstadoDeActividadesIngresosporVentadeBienesyServicios" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Ingresos no Comprendidos en las Fracciones de la Ley de Ingresos Causados en Ejercicios Fiscales Anteriores Pendientes de Liquidacion o Pago</span> 
                              <select id="selectEstadoDeActividadesIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores" name="selectEstadoDeActividadesIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores[]" class="form-control selectEstadoDeActividadesIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br>
                               <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Pendientes de Liquidacion o Pago</span> 
                              <select id="selectEstadoDeActividadesPendientesdeLiquidacionoPago" name="selectEstadoDeActividadesPendientesdeLiquidacionoPago[]" class="form-control selectEstadoDeActividadesPendientesdeLiquidacionoPago" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Participaciones y Aportaciones</span> 
                              <select id="selectEstadoDeActividadesParticipacionesyAportaciones" name="selectEstadoDeActividadesParticipacionesyAportaciones[]" class="form-control selectEstadoDeActividadesParticipacionesyAportaciones" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Transferencia, Asignaciones, Subsidios y Otras Ayudas</span> 
                              <select id="selectEstadoDeActividadesTransferenciaAsignacionesSubsidiosyOtrasAyudas" name="selectEstadoDeActividadesTransferenciaAsignacionesSubsidiosyOtrasAyudas[]" class="form-control selectEstadoDeActividadesTransferenciaAsignacionesSubsidiosyOtrasAyudas" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Ingresos Financieros</span> 
                              <select id="selectEstadoDeActividadesIngresosFinancieros" name="selectEstadoDeActividadesIngresosFinancieros[]" class="form-control selectEstadoDeActividadesIngresosFinancieros" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Incremento por Variación de Inventarios</span> 
                              <select id="selectEstadoDeActividadesIncrementoporVariaciondeInventarios" name="selectEstadoDeActividadesIncrementoporVariaciondeInventarios[]" class="form-control selectEstadoDeActividadesIncrementoporVariaciondeInventarios" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Disminución del Exceso de Estimaciones por Pérdida o Deterioro u Obsolescencia </span> 
                              <select id="selectEstadoDeActividadesDisminuciondelExcesodeEstimacionesporPerdidaoDeteriorouObsolescencia" name="selectEstadoDeActividadesDisminuciondelExcesodeEstimacionesporPerdidaoDeteriorouObsolescencia[]" class="form-control selectEstadoDeActividadesDisminuciondelExcesodeEstimacionesporPerdidaoDeteriorouObsolescencia" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Disminución del Exceso de Provisiones</span> 
                              <select id="selectEstadoDeActividadesDisminuciondelExcesodeProvisiones" name="selectEstadoDeActividadesDisminuciondelExcesodeProvisiones[]" class="form-control selectEstadoDeActividadesDisminuciondelExcesodeProvisiones" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Otros Ingresos y Beneficios Varios</span> 
                              <select id="selectEstadoDeActividadesOtrosIngresosyBeneficiosVarios" name="selectEstadoDeActividadesOtrosIngresosyBeneficiosVarios[]" class="form-control selectEstadoDeActividadesOtrosIngresosyBeneficiosVarios" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Servicios Personales</span> 
                              <select id="selectEstadoDeActividadesServiciosPersonales" name="selectEstadoDeActividadesServiciosPersonales[]" class="form-control selectEstadoDeActividadesServiciosPersonales" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Materiales y Suministros</span> 
                              <select id="selectEstadoDeActividadesMaterialesySuministros" name="selectEstadoDeActividadesMaterialesySuministros[]" class="form-control selectEstadoDeActividadesMaterialesySuministros" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Servicios Generales</span> 
                              <select id="selectEstadoDeActividadesServiciosGenerales" name="selectEstadoDeActividadesServiciosGenerales[]" class="form-control selectEstadoDeActividadesServiciosGenerales" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Transferencias Internas y Asignaciones al Sector Publico</span> 
                              <select id="selectEstadoDeActividadesTransferenciasInternasyAsignacionesalSectorPublico" name="selectEstadoDeActividadesTransferenciasInternasyAsignacionesalSectorPublico[]" class="form-control selectEstadoDeActividadesTransferenciasInternasyAsignacionesalSectorPublico" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Transferencias al Resto del Sector Publico</span> 
                              <select id="selectEstadoDeActividadesTransferenciasalRestodelSectorPublico" name="selectEstadoDeActividadesTransferenciasalRestodelSectorPublico[]" class="form-control selectEstadoDeActividadesTransferenciasalRestodelSectorPublico" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Subsidios y Subvenciones</span> 
                              <select id="selectEstadoDeActividadesSubsidiosySubvenciones" name="selectEstadoDeActividadesSubsidiosySubvenciones[]" class="form-control selectEstadoDeActividadesSubsidiosySubvenciones" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Ayudas Sociales</span> 
                              <select id="selectEstadoDeActividadesAyudasSociales" name="selectEstadoDeActividadesAyudasSociales[]" class="form-control selectEstadoDeActividadesAyudasSociales" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Pensiones y Jubilaciones</span> 
                              <select id="selectEstadoDeActividadesPensionesyJubilaciones" name="selectEstadoDeActividadesPensionesyJubilaciones[]" class="form-control selectEstadoDeActividadesPensionesyJubilaciones" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Transferencias a Fideicomisos, Mandatos y Contratos Analogos</span> 
                              <select id="selectEstadoDeActividadesTransferenciasaFideicomisosMandatosyContratosAnalogos" name="selectEstadoDeActividadesTransferenciasaFideicomisosMandatosyContratosAnalogos[]" class="form-control selectEstadoDeActividadesTransferenciasaFideicomisosMandatosyContratosAnalogos" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Transferencias a la Seguridad Social</span> 
                              <select id="selectEstadoDeActividadesTransferenciasalaSeguridadSocial" name="selectEstadoDeActividadesTransferenciasalaSeguridadSocial[]" class="form-control selectEstadoDeActividadesTransferenciasalaSeguridadSocial" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Donativos</span> 
                              <select id="selectEstadoDeActividadesDonativos" name="selectEstadoDeActividadesDonativos[]" class="form-control selectEstadoDeActividadesDonativos" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Transferencias al Exterior</span> 
                              <select id="selectEstadoDeActividadesTransferenciasalExterior" name="selectEstadoDeActividadesTransferenciasalExterior[]" class="form-control selectEstadoDeActividadesTransferenciasalExterior" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Participaciones</span> 
                              <select id="selectEstadoDeActividadesParticipaciones" name="selectEstadoDeActividadesParticipaciones[]" class="form-control selectEstadoDeActividadesParticipaciones" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Aportaciones</span> 
                              <select id="selectEstadoDeActividadesAportaciones" name="selectEstadoDeActividadesAportaciones[]" class="form-control selectEstadoDeActividadesAportaciones" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Convenios</span> 
                              <select id="selectEstadoDeActividadesConvenios" name="selectEstadoDeActividadesConvenios[]" class="form-control selectEstadoDeActividadesConvenios" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Intereses de la Deuda Publica</span> 
                              <select id="selectEstadoDeActividadesInteresesdelaDeudaPublica" name="selectEstadoDeActividadesInteresesdelaDeudaPublica[]" class="form-control selectEstadoDeActividadesInteresesdelaDeudaPublica" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Comisiones de la Deuda Publica</span> 
                              <select id="selectEstadoDeActividadesComisionesdelaDeudaPublica" name="selectEstadoDeActividadesComisionesdelaDeudaPublica[]" class="form-control selectEstadoDeActividadesComisionesdelaDeudaPublica" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Gastos de la Deuda Publica</span> 
                              <select id="selectEstadoDeActividadesGastosdelaDeudaPublica" name="selectEstadoDeActividadesGastosdelaDeudaPublica[]" class="form-control selectEstadoDeActividadesGastosdelaDeudaPublica" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Costo por Coberturas</span> 
                              <select id="selectEstadoDeActividadesCostoporCoberturas" name="selectEstadoDeActividadesCostoporCoberturas[]" class="form-control selectEstadoDeActividadesCostoporCoberturas" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Apoyos Financieros</span> 
                              <select id="selectEstadoDeActividadesApoyosFinancieros" name="selectEstadoDeActividadesApoyosFinancieros[]" class="form-control selectEstadoDeActividadesApoyosFinancieros" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Estimaciones, Depreciaciones, Deterioros, Obsolescencia y Amortizaciones</span> 
                              <select id="selectEstadoDeActividadesEstimacionesDepreciacionesDeteriorosObsolescenciayAmortizaciones" name="selectEstadoDeActividadesEstimacionesDepreciacionesDeteriorosObsolescenciayAmortizaciones[]" class="form-control selectEstadoDeActividadesEstimacionesDepreciacionesDeteriorosObsolescenciayAmortizaciones" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Provisiones</span> 
                              <select id="selectEstadoDeActividadesProvisiones" name="selectEstadoDeActividadesProvisiones[]" class="form-control selectEstadoDeActividadesProvisiones" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Disminución de Inventarios</span> 
                              <select id="selectEstadoDeActividadesDisminuciondeInventarios" name="selectEstadoDeActividadesDisminuciondeInventarios[]" class="form-control selectEstadoDeActividadesDisminuciondeInventarios" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Aumento por Insuficiencia de Estimaciones por Perdida o Deterioro y Obsolescencia</span> 
                              <select id="selectEstadoDeActividadesAumentoporInsuficienciadeEstimacionesporPerdidaoDeterioroyObsolescencia" name="selectEstadoDeActividadesAumentoporInsuficienciadeEstimacionesporPerdidaoDeterioroyObsolescencia[]" class="form-control selectEstadoDeActividadesAumentoporInsuficienciadeEstimacionesporPerdidaoDeterioroyObsolescencia" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Aumento por Insuficiencia de Provisiones</span> 
                              <select id="selectEstadoDeActividadesAumentoporInsuficienciadeProvisiones" name="selectEstadoDeActividadesAumentoporInsuficienciadeProvisiones[]" class="form-control selectEstadoDeActividadesAumentoporInsuficienciadeProvisiones" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Otros Gastos</span> 
                              <select id="selectEstadoDeActividadesOtrosGastos" name="selectEstadoDeActividadesOtrosGastos[]" class="form-control selectEstadoDeActividadesOtrosGastos" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> <div class="input-group"> 
                             <div id="R1P001"></div>
                             <span class="input-group-addon" style="background: none; border: none;"> Inversión Publica no Capitalizable</span> 
                              <select id="selectEstadoDeActividadesInversionPublicanoCapitalizable" name="selectEstadoDeActividadesInversionPublicanoCapitalizable[]" class="form-control selectEstadoDeActividadesInversionPublicanoCapitalizable" multiple="multiple"> 
                              </select> 
                              </div> 
                              <br> 

                              <div id="mensajesValidacionesCONACR2"> </div>
                                    <component-button type="button" id="btnGrabar" name="btnGrabar" onclick="fnGrabarConfiguracionEstadoDeActividades()" value="Grabar configuración Reporte de Estado de Actividades "></component-button>
                                    <component-button type="button" id="btnImprimir" name="btnImprimir" onclick="window.open('PrintSituacionFinanciera.php?PrintPDF=1&reporte=estadodeactividades')" value="Imprimir Reporte de Estado de Actividades "></component-button>


                        <!-- fin tab 1.2 -->

                    </div>

                    <div class="tab-pane fade" id="sub13">
                        <p>Tab 1.3</p> <!--inicio -->
                        <h3> Nota: La configuración de este reporte solo se muestra de manera informativa, porque las cuentas que conforman el reporte se seleccionan desde los reportes de: </h3>
                        <h3><li>"Situacion Financiera"</li> y <li>"Estado de actividades"</li></h3>

                        <h3>"Situacion Financiera"</h3> 
                        <br> 
                        <h3>"Estado de actividades"</h3>
                    </div>

                    <div class="tab-pane fade" id="sub14">
                          <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Impuestos</span> 
                                <select id="selectflujoefectivoImpuestos" name="selectflujoefectivoImpuestos[]" class="form-control selectflujoefectivoImpuestos" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br> <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Cuotas y Aportaciones de Seguridad Social</span> 
                                <select id="selectflujoefectivoCuotasyAportacionesdeSeguridadSocial" name="selectflujoefectivoCuotasyAportacionesdeSeguridadSocial[]" class="form-control selectflujoefectivoCuotasyAportacionesdeSeguridadSocial" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br> <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Contribuciones de mejoras</span> 
                                <select id="selectflujoefectivoContribucionesdemejoras" name="selectflujoefectivoContribucionesdemejoras[]" class="form-control selectflujoefectivoContribucionesdemejoras" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br> <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Derechos</span> 
                                <select id="selectflujoefectivoDerechos" name="selectflujoefectivoDerechos[]" class="form-control selectflujoefectivoDerechos" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br> <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Productos de Tipo Corriente</span> 
                                <select id="selectflujoefectivoProductosdeTipoCorriente" name="selectflujoefectivoProductosdeTipoCorriente[]" class="form-control selectflujoefectivoProductosdeTipoCorriente" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br> <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Aprovechamientos de Tipo Corriente</span> 
                                <select id="selectflujoefectivoAprovechamientosdeTipoCorriente" name="selectflujoefectivoAprovechamientosdeTipoCorriente[]" class="form-control selectflujoefectivoAprovechamientosdeTipoCorriente" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br> <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Ingresos por Venta de Bienes y Servicios</span> 
                                <select id="selectflujoefectivoIngresosporVentadeBienesyServicios" name="selectflujoefectivoIngresosporVentadeBienesyServicios[]" class="form-control selectflujoefectivoIngresosporVentadeBienesyServicios" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br> <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Ingresos no Comprendidos en las Fracciones de la Ley de Ingresos Causadosen Ejercicios Fiscales Anteriores</span> 
                                <select id="selectflujoefectivoIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores" name="selectflujoefectivoIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores[]" class="form-control selectflujoefectivoIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br> 
                                <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Pendientes de Liquidación o Pago</span> 
                                <select id="selectflujoefectivoPendientesdeLiquidacionoPago" name="selectflujoefectivoPendientesdeLiquidacionoPago[]" class="form-control selectflujoefectivoPendientesdeLiquidacionoPago" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br> <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Participaciones y Aportaciones</span> 
                                <select id="selectflujoefectivoParticipacionesyAportaciones" name="selectflujoefectivoParticipacionesyAportaciones[]" class="form-control selectflujoefectivoParticipacionesyAportaciones" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br> <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Transferencias Asignaciones y Subsidios y Otras Ayudas</span> 
                                <select id="selectflujoefectivoTransferenciasAsignacionesySubsidiosyOtrasAyudas" name="selectflujoefectivoTransferenciasAsignacionesySubsidiosyOtrasAyudas[]" class="form-control selectflujoefectivoTransferenciasAsignacionesySubsidiosyOtrasAyudas" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br> <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Otros Origenes de Operacion</span> 
                                <select id="selectflujoefectivoOtrosOrigenesdeOperacion" name="selectflujoefectivoOtrosOrigenesdeOperacion[]" class="form-control selectflujoefectivoOtrosOrigenesdeOperacion" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br> <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Servicios Personales</span> 
                                <select id="selectflujoefectivoServiciosPersonales" name="selectflujoefectivoServiciosPersonales[]" class="form-control selectflujoefectivoServiciosPersonales" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br> <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Materiales y Suministros</span> 
                                <select id="selectflujoefectivoMaterialesySuministros" name="selectflujoefectivoMaterialesySuministros[]" class="form-control selectflujoefectivoMaterialesySuministros" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br> <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Servicios Generales</span> 
                                <select id="selectflujoefectivoServiciosGenerales" name="selectflujoefectivoServiciosGenerales[]" class="form-control selectflujoefectivoServiciosGenerales" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br>
                                 <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Transferencias Internas y Asignaciones al Sector Público</span> 
                                <select id="selectflujoefectivoTransferenciasInternasyAsignacionesalSectorPublico" name="selectflujoefectivoTransferenciasInternasyAsignacionesalSectorPublico[]" class="form-control selectflujoefectivoTransferenciasInternasyAsignacionesalSectorPublico" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br>
                                 <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Transferencias al resto del Sector Público</span> 
                                <select id="selectflujoefectivoTransferenciasalrestodelSectorPublico" name="selectflujoefectivoTransferenciasalrestodelSectorPublico[]" class="form-control selectflujoefectivoTransferenciasalrestodelSectorPublico" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br>

                                 <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Subsidios y Subvenciones</span> 
                                <select id="selectflujoefectivoSubsidiosySubvenciones" name="selectflujoefectivoSubsidiosySubvenciones[]" class="form-control selectflujoefectivoSubsidiosySubvenciones" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br> <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Ayudas Sociales</span> 
                                <select id="selectflujoefectivoAyudasSociales" name="selectflujoefectivoAyudasSociales[]" class="form-control selectflujoefectivoAyudasSociales" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br> <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Pensiones y Jubilaciones</span> 
                                <select id="selectflujoefectivoPensionesyJubilaciones" name="selectflujoefectivoPensionesyJubilaciones[]" class="form-control selectflujoefectivoPensionesyJubilaciones" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br> <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Transferencias a Fideicomisos, Mandatos y Contratos Analogos</span> 
                                <select id="selectflujoefectivoTransferenciasaFideicomisosMandatosyContratosAnalogos" name="selectflujoefectivoTransferenciasaFideicomisosMandatosyContratosAnalogos[]" class="form-control selectflujoefectivoTransferenciasaFideicomisosMandatosyContratosAnalogos" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br>

                                <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;">Transferencias a la Seguridad Social</span> 
                                <select id="selectflujoefectivoTransferenciasalaSeguridadSocial" name="selectflujoefectivoTransferenciasalaSeguridadSocial[]" class="form-control selectflujoefectivoTransferenciasalaSeguridadSocial" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br>

                                 <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Donativos</span> 
                                <select id="selectflujoefectivoDonativos" name="selectflujoefectivoDonativos[]" class="form-control selectflujoefectivoDonativos" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br> <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Transferencias al Exterior Participaciones</span> 
                                <select id="selectflujoefectivoTransferenciasalExteriorParticipaciones" name="selectflujoefectivoTransferenciasalExteriorParticipaciones[]" class="form-control selectflujoefectivoTransferenciasalExteriorParticipaciones" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br> <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Aportaciones</span> 
                                <select id="selectflujoefectivoAportaciones" name="selectflujoefectivoAportaciones[]" class="form-control selectflujoefectivoAportaciones" multiple="multiple"> 
                                </select> 
                                </div> 
                                <br> <div class="input-group"> 
                               <div id="R1P001"></div>
                               <span class="input-group-addon" style="background: none; border: none;"> Convenios</span> 
                                <select id="selectflujoefectivoConvenios" name="selectflujoefectivoConvenios[]" class="form-control selectflujoefectivoConvenios" multiple="multiple"> 
                                </select> 
                                </div> 

                                <div class="input-group"> 
                                   <div id="R1P001"></div>
                                   <span class="input-group-addon" style="background: none; border: none;"> Bienes Inmuebles, Infraestructura y Construcciones</span> 
                                    <select id="selectflujoefectivoBienesInmueblesInfraestructurayConstruccionesenProceso" name="selectflujoefectivoBienesInmueblesInfraestructurayConstruccionesenProceso[]" class="form-control selectflujoefectivoBienesInmueblesInfraestructurayConstruccionesenProceso" multiple="multiple"> 
                                    </select> 
                                    </div> 
                                    <br> <div class="input-group"> 
                                   <div id="R1P001"></div>
                                   <span class="input-group-addon" style="background: none; border: none;"> Bienes Muebles</span> 
                                    <select id="selectflujoefectivoBienesMuebles" name="selectflujoefectivoBienesMuebles[]" class="form-control selectflujoefectivoBienesMuebles" multiple="multiple"> 
                                    </select> 
                                    </div> 
                                    <br> <div class="input-group"> 
                                   <div id="R1P001"></div>
                                   <span class="input-group-addon" style="background: none; border: none;"> Otros Origenes de Inversion</span> 
                                    <select id="selectflujoefectivoOtrosOrigenesdeInversion" name="selectflujoefectivoOtrosOrigenesdeInversion[]" class="form-control selectflujoefectivoOtrosOrigenesdeInversion" multiple="multiple"> 
                                    </select> 
                                    </div> 
                                    <br> 


                                <div id="mensajesValidacionesRFlujo"> </div>
                                <component-button type="button" id="btnGrabar" name="btnGrabar" onclick="fnGrabarConfiguracionflujoefectivo()" value="Grabar configuración Reporte de Flujo de Efectivo"></component-button>



                                <br> 
                    </div>

                    <div class="tab-pane fade" id="sub15">
                         <div class="input-group"> 
                           <div id="R1P001"></div>
                           <span class="input-group-addon" style="background: none; border: none;">  Impuestos</span> 
                            <select id="selectanaliticoIngresosImpuestos" name="selectanaliticoIngresosImpuestos[]" class="form-control selectanaliticoIngresosImpuestos" multiple="multiple"> 
                            </select> 
                            </div> 
                            <br> <div class="input-group"> 
                           <div id="R1P001"></div>
                           <span class="input-group-addon" style="background: none; border: none;"> Cuotas Y Aportaciones De Seguridad Social</span> 
                            <select id="selectanaliticoIngresosCuotasYAportacionesDeSeguridadSocial" name="selectanaliticoIngresosCuotasYAportacionesDeSeguridadSocial[]" class="form-control selectanaliticoIngresosCuotasYAportacionesDeSeguridadSocial" multiple="multiple"> 
                            </select> 
                            </div> 
                            <br> <div class="input-group"> 
                           <div id="R1P001"></div>
                           <span class="input-group-addon" style="background: none; border: none;"> Contribuciones Y Mejoras</span> 
                            <select id="selectanaliticoIngresosContribucionesYMejoras" name="selectanaliticoIngresosContribucionesYMejoras[]" class="form-control selectanaliticoIngresosContribucionesYMejoras" multiple="multiple"> 
                            </select> 
                            </div> 
                            <br> <div class="input-group"> 
                           <div id="R1P001"></div>
                           <span class="input-group-addon" style="background: none; border: none;"> Derechos</span> 
                            <select id="selectanaliticoIngresosDrechos" name="selectanaliticoIngresosDrechos[]" class="form-control selectanaliticoIngresosDrechos" multiple="multiple"> 
                            </select> 
                            </div> 
                            <br> <div class="input-group"> 
                           <div id="R1P001"></div>
                           <span class="input-group-addon" style="background: none; border: none;"> Productos</span> 
                            <select id="selectanaliticoIngresosProductos" name="selectanaliticoIngresosProductos[]" class="form-control selectanaliticoIngresosProductos" multiple="multiple"> 
                            </select> 
                            </div> 
                            <br> <div class="input-group"> 
                           <div id="R1P001"></div>
                           <span class="input-group-addon" style="background: none; border: none;"> Aprovechamientos</span> 
                            <select id="selectanaliticoIngresosAprovechamientos" name="selectanaliticoIngresosAprovechamientos[]" class="form-control selectanaliticoIngresosAprovechamientos" multiple="multiple"> 
                            </select> 
                            </div> 
                            <br> <div class="input-group"> 
                           <div id="R1P001"></div>
                           <span class="input-group-addon" style="background: none; border: none;"> Ingresos Por Ventas de Bienes Y Servicios</span> 
                            <select id="selectanaliticoIngresosIngresosPorVentasdeBienesYServicios" name="selectanaliticoIngresosIngresosPorVentasdeBienesYServicios[]" class="form-control selectanaliticoIngresosIngresosPorVentasdeBienesYServicios" multiple="multiple"> 
                            </select> 
                            </div> 
                            <br> <div class="input-group"> 
                           <div id="R1P001"></div>
                           <span class="input-group-addon" style="background: none; border: none;"> Participaciones Y Aportaciones</span> 
                            <select id="selectanaliticoIngresosParticipacionesYAportaciones" name="selectanaliticoIngresosParticipacionesYAportaciones[]" class="form-control selectanaliticoIngresosParticipacionesYAportaciones" multiple="multiple"> 
                            </select> 
                            </div> 
                            <br> <div class="input-group"> 
                           <div id="R1P001"></div>
                           <span class="input-group-addon" style="background: none; border: none;"> Transferencias Asignaciones Subsidios Y Otras Ayudas</span> 
                            <select id="selectanaliticoIngresosTransferenciasAsignacionesSubsidiosYOtrasAyudas" name="selectanaliticoIngresosTransferenciasAsignacionesSubsidiosYOtrasAyudas[]" class="form-control selectanaliticoIngresosTransferenciasAsignacionesSubsidiosYOtrasAyudas" multiple="multiple"> 
                            </select> 
                            </div> 
                            <br> <div class="input-group"> 
                           <div id="R1P001"></div>
                           <span class="input-group-addon" style="background: none; border: none;"> Ingresos Derivados De Financiamientos</span> 
                            <select id="selectanaliticoIngresosIngresosDerivadosDeFinanciamientos" name="selectanaliticoIngresosIngresosDerivadosDeFinanciamientos[]" class="form-control selectanaliticoIngresosIngresosDerivadosDeFinanciamientos" multiple="multiple"> 
                            </select> 


                            </div> 
                            <br> 

                            <div id="mensajesValidacionesR15"> </div>
                                    <component-button type="button" id="btnGrabar" name="btnGrabar" onclick="fnGrabarConfiguracionAnaliticoDelIngreso()" value="Grabar configuración Reporte de Situación Financiera"></component-button>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="set2">
            <div class="tabbable">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#sub21">Reporte LDF - 1</a>
                    </li>
                    <li><a href="#sub22">Reporte LDF - 2</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade active in" id="sub21">
                        <h3> Nota: La configuración de este reporte solo se muestra de manera informativa, porque las cuentas que conforman el reporte se seleccionan desde los reportes de: 

                        </h3>
                        <h3>
                          <ul class="nav nav-tabs">
                            <li>"Situacion Financiera"</li>
                            <li>"Estado de actividades"</li>
                          </ul>
                        </h3>
                    </div>
                    <div class="tab-pane fade" id="sub22">
                        <p>Reporte LDF - 2</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--
-->

<?php
include 'includes/footer_Index.inc';
?>