<?php
/**
 * Plan de Cuentas
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 08/08/2017
 * Fecha Modificación: 08/08/2017
 * Vista para el proceso del Plan de Cuentas
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 5;
$funcion = 128;
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db); //_('Mantenimiento al Catalogo de Cuentas');

include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

//Librerias GRID
include('javascripts/libreriasGrid.inc');

# incliciones extra
include('./Numbers/Words.php');

// Validar Identicador
$validarIdentificador = 1;

$urDefaultIde = fnURDefaultIdentificador($db);

////////////////////////////////////////////
// funciones creadas apartir del 20.04.18 //
////////////////////////////////////////////

function obtenNivelDesagregacion($value = '')
{
	global $db;
	$html = '';
	$numberToWordInstance = new Numbers_Words();
	$sql = "SELECT MAX(`nu_nivel`) as niveles FROM `chartmaster`";
	$result = DB_query($sql, $db);
	$maxNivel = DB_fetch_array($result)['niveles'];
	for ($i=1; $i <= $maxNivel; $i++) {
		if($i == $value){
			$html .= '<option value="'.$i.'" selected>'.ucfirst(obtenNombreNuemroOrdinal($i)).' Nivel</option>';

		}else{
			$html .= '<option value="'.$i.'">'.ucfirst(obtenNombreNuemroOrdinal($i)).' Nivel</option>';

		}
	}
	return $html;
}

function obtenNombreNuemroOrdinal($numero)
{
	$arr = [
		1=>'primer','segundo','tercer','cuarto', 'quinto', 'sexto','séptimo','octavo','noveno','décimo','undécimo','duodécimo','decimotercero','decimocuarto',
		'decimoquinto','decimosexto','decimoséptimo','decimoctavo','decimonoveno','vigésimo','vigésimo primero','vigésimo segundo',
		'vigésimo tercero','vigésimo cuarto','vigésimo quinto','vigésimo sexto','vigésimo séptimo','vigésimo octavo',
		'vigésimo noveno','trigésimo'
	];
	return $arr[$numero];
}

?>

<script type="text/javascript">
	var validarIdentificador = '<?php echo $validarIdentificador; ?>';
	var urDefaultIde = '<?php echo $urDefaultIde; ?>';
</script>

<link rel="stylesheet" href="css/listabusqueda.css" />

<div id="divMensajeOperacion" name="divMensajeOperacion"></div>

<!-- Filtros -->
<div class="row">
	<div class="panel-group">
		<div class="panel panel-default">
			<div class="panel-heading h35">
				<h4 class="panel-title">
					<!-- <a data-toggle="collapse" href="#closeTab"> <strong>CRITERIOS DE BÚSQUEDA</strong> </a> -->
					<div class="fl text-left">
						<a role="button" data-toggle="collapse" data-parent="#accordion" href="#closeTab" aria-expanded="true" aria-controls="collapseOne">
							<b>Filtros de Búsqueda</b>
						</a>
					</div>
				</h4>
			</div><!-- .panel-heading -->
			<div id="closeTab" class="panel-collapse collapse in">
				<div class="panel-body">
					<form id="frmFiltroActivos">
						<div class="row">
							<input type="hidden" name="buscarConFiltros" id="buscarConFiltros" value="0">
							<!-- UR, UE -->
							<div class="col-md-6">
								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>UR: </label></span>
									</div>
									<div class="col-md-9">
										<select id="selectUnidadNegocioFiltro" name="selectUnidadNegocioFiltro" class="form-control selectUnidadNegocio" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutoraFiltro')">
											<option value="-1">Seleccionar...</option>
										</select>
									</div>
								</div>
								<br>

								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>UE: </label></span>
									</div>
									<div class="col-md-9">
										<select id="selectUnidadEjecutoraFiltro" name="selectUnidadEjecutoraFiltro" class="form-control selectUnidadEjecutora" multiple="multiple">
										</select>
									</div>
								</div>
								<br>

								<div class="form-inline row hide">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>PP: </label></span>
									</div>
									<div class="col-md-9">
										<select id="busquedaPP" name="busquedaPP" class="form-control selectProgramaPresupuestario" multiple="multiple">
										</select>
									</div>
								</div>

								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label> Nivel de Desagregación:</label></span>
									</div>
									<div class="col-md-9">
										<select id="NivelDesagregacion" class="form-control mb10" name="NivelDesagregacion[]" multiple="true" data-todos="true">
											<?php
												# se agrega nivel de dessagregacion dinamico
												echo obtenNivelDesagregacion($_POST['NivelDesagregacion']); ?>
										</select>
									</div>
								</div>
								<br>

								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Naturaleza: </label></span>
									</div>
									<div class="col-md-9">
										<select id="selectNaturalezaFiltro" name="selectNaturalezaFiltro[]" 
										class="form-control selectNaturaleza"  multiple="true" data-todos="true">
											<option value="1">Deudora</option>
											<option value="-1">Acreedora</option>
											<option value="2">Deudora/Acreedora</option>
										</select>
									</div>
								</div>
								<br>

							</div><!-- -col-md-6 -->

							<!-- nivel, naturaleza 
							<div class="col-md-6">
								

							</div>-->

							<div class="col-md-6">
								<div class="form-inline row hide">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Género: </label></span>
									</div>
									<div class="col-md-9">
										<select id="busquedaGenero" name="busquedaGenero" class="form-control busquedaGenero" multiple="multiple">
										</select>
									</div>
								</div>
								<!-- <br> -->

								<component-listado-label label="Cuenta desde:" id="cuentaDesde" name="cuentaDesde" placeholder="Cuenta desde"></component-listado-label>
								<br>

								<component-listado-label label="Cuenta hasta:" id="cuentaHasta" name="cuentaHasta" placeholder="Cuenta hasta"></component-listado-label>
								<br>

								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Estatus: </label></span>
									</div>
									<div class="col-md-9">
										<select id="EstatusFiltro" name="EstatusFiltro" class="form-control EstatusFiltro">
											<option value="-1">Seleccionar...</option>
											<option value="1">Activo</option>
											<option value="2">Inactivo</option>
										</select>
									</div>
								</div>
								<br>

							</div><!-- -col-md-6 -->

							
						</div>

						<!-- Botones -->
						<br>
						<div class="row">
							<div class="col-xs-12">
								<component-button type="button" id="btnBuscar" name="btnBuscar" class="glyphicon glyphicon-search" value="Filtrar" onclick="fnMostrarDatos()"></component-button>
							</div>
						</div>
					</form>
				</div><!-- .panel-body -->
			</div><!-- .panel-collapse -->
		</div><!-- .panel -->
	</div><!-- .panel-group -->
</div><!-- / Encabezado -->

<div align="left">
	<div name="divTabla" id="divTabla" class="infoCuentaContable">
		<div name="divContenidoTabla" id="divContenidoTabla"></div>
	</div>
</div>

<div align="center">
	<component-button type="button" id="btnAgregar" name="btnAgregar" onclick="fnAgregarModificarCuenta()" value="Nueva" class="glyphicon glyphicon-plus"></component-button>
	<br><br>
</div>

<script type="text/javascript" src="javascripts/GLAccounts.js?<?php echo rand(); ?>"></script>

<?php
include 'includes/footer_Index.inc';
?>