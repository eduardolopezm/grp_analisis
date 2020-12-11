// objetos de configuración
	window.uePorUsuario = new Array();
	window.cuentasTotales = new Array();
	window.cuentasBancarias = new Array();

	if(typeof(Storage)!=="undefined"){
		window.uePorUsuario = ( typeof(window.localStorage.uePorUsuario)!=="undefined" ? JSON.parse(window.localStorage.uePorUsuario) : window.uePorUsuario );
		//window.cuentasTotales = ( typeof(window.localStorage.cuentasTotales)!=="undefined" ? JSON.parse(window.localStorage.cuentasTotales) : window.cuentasTotales );
		//window.cuentasBancarias = ( typeof(window.localStorage.cuentasBancarias)!=="undefined" ? JSON.parse(window.localStorage.cuentasBancarias) : window.cuentasBancarias );
	}
	window.busquedaPorUsuario = false;
	window.listadoHabilitado = true;

	window.buscadores = [ "stockact", "accountegreso" ];
	window.buscadoresConfiguracion = {};
	$.each(window.buscadores,function(index,valor){
		window.buscadoresConfiguracion[valor] = {};
	});
	window.buscadoresConfiguracion.stockact.origenDatos = "cuentasTotales";
	window.buscadoresConfiguracion.accountegreso.origenDatos = "cuentasTotales";

	window.propiedadesResize = {
									widthsEspecificos: {
										categoryid:		"7%",
										nu_tipo_gasto:	"5%",
										modificar:		"7%"
									},
									encabezadosADosRenglones: {
										categoryid:			"Partida<br />Genérica",
										nu_tipo_gasto:		"Tipo<br />Gasto"
									},
									camposConWidthAdicional: window.buscadores
								};

	window.tiposDeProgramas = [];

function validacionExtraPresupuestal(){
	$('#categoryid').attr('readonly', false);
	$('#categorydescription').attr('readonly', true);
	if($('#textoPrograma').html().substring(0,2)=="PE"){
		if($('#categoryid').val()==""){
			$('#categorydescription').attr('readonly', false);
		}
		if($('#categorydescription').val()&&!$('#categoryid').val()){
			$('#categoryid').attr('readonly', true);
		}
	}
}

// asignación de eventos
$(document).ready(function() {
	// llamado de función inicial
	inicioPanel();
	// comportamiento del campo PP
	$('#txtpp').on('change',function(){
		$('#textoPrograma').html( ( window.tiposDeProgramas[$(this).val()]===undefined ? "PP" : window.tiposDeProgramas[$(this).val()].toUpperCase() )+":");
		validacionExtraPresupuestal();
	});
	// comportamiento del campo Descripción
	$('#categorydescription').on('keyup',function(){
		validacionExtraPresupuestal();
	});
	// comportamiento del botón nuevo
	$('#nuevo').on('click',function(){
		fbCamposABloquear(false);
		if(!($('#categorydescription').val()&&!$('#categoryid').val())){
			fnTraeInformacionPartida(document.getElementById("categoryid")); // Línea adicional para este módulo
		}else{
			$('#txtpp').trigger('change');
		}
		if($("#ind_activo").val()=="-1"){
			$('#ind_activo').multiselect('select', 1);
		}
		AlinearSelectsDerecha();
		$('#tituloModal').html('<i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Nuevo Registro');
		$('#modalUsoGeneral').modal('show');
	});
	// comportamiento del botón de guardado
	$('#guardar').on('click',function(){
		var	params = getParams(idForma), campos = getMatchets(idForma), msg = '',
			nombreCampo = {
				'selectUnidadNegocio':'UR',
				'selectUnidadEjecutora':'UE',
				'txtpp':'Programa Presupuestario',
				'ind_activo':'Estatus',
				'categoryid':'Partida Genérica',
				'categorydescription':'Descripción',
				'factesquemadoancho':'Origen',
				'ordendesplegar':'Clave',
				'stockact':'Cuenta Cargo',
				'accountegreso':'Cuenta Abono',
				'nu_tipo_gasto':'Tipo de Gasto',
				'factesquemadoalto':'Proceso'
			},
			method = params.hasOwnProperty('identificador')?'update':'store';
		$.extend(params,{method:method,'valid':1});
		$.each(campos,function(index, el) {
			if($('#textoPrograma').html().substring(0,2)=="PE"&&el=="categoryid"){ return; }
			if(el.substr(0,13)=="textoOculto__"){ return; }
			if( (el=="stockact"||el=="accountegreso")&&$("#sugerencia-"+el).is(":visible") ){ // Línea adicional para este módulo
				msg += 'Por favor haga clic en alguna de las cuentas del listado '+nombreCampo[el]+'.<br />';
				return;
			}
			if(el=='ind_activo'&&params[el]!=-1){ return; } // Línea adicional para este módulo
			if($("#"+el).is(":disabled")){ return; }
			if(el=="selectUnidadEjecutora"&&$("#"+el).val()=="00"){ return; } // Línea adicional para este módulo
			if(!params.hasOwnProperty(el)){ return; }
			// if(esepcion.indexOf(el)!==-1){ return; }
			if(params[el]!=0&&params[el]!=-1){ return; }
			msg += 'El campo '+nombreCampo[el]+' no puede ir vacío.<br />';
		});
		//// Se reemplaza muestraModalGeneral(3,'Error de datos',msg) por muestraMensajeTiempo(msg, 1, 'msjValidacion', 5000);
		if(msg!=''){ muestraMensajeTiempo(msg, 3, 'msjValidacion', 5000); return;}
		muestraCargandoGeneral();
		var	zIndexModal = $("#modalUsoGeneral").zIndex();
		$("#modalUsoGeneral").zIndex("750");
		setTimeout(function(){
			$.ajaxSetup({async: false, cache: false});
			$.post(modelo, params).then(function(res){
				////var	titulo=res.success?'Operación Exitosa':'Error de Datos';
				var	titulo='<i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información';
				if(res.success){
					var	valorUR = $('#selectUnidadNegocio').val();
					fnLimpiarCamposFormConHidden(idForma);
					$('#selectUnidadNegocio').multiselect('select', valorUR);
					$('#modalUsoGeneral').modal('hide');
					muestraModalGeneral(3,titulo,res.msg);
				}else{
					muestraMensajeTiempo(res.msg, 3, 'msjValidacion', 5000);
				}
				ocultaCargandoGeneral();
				$("#modalUsoGeneral").zIndex(zIndexModal);
				// Se reemplaza la siguiente línea y se agrega la subsecuente, se remueve el resultado de res.content del modelo
				//llenaTabla(res.content);
				fnMostrarDatos();
			});
		}, 500);
	});
	// comportamiento de modificación de registro
	$(document).on('cellselect','#tablaGrid',function(e){
		var	ln_clave = "", CamposABloquear = false; // Línea adicional para este módulo
		// declaración de variables
		var	index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
		// confirmación de evento a lanzar
		if(campo != 'modificar'){ return false; }
		muestraCargandoGeneral();
		// declaración de variables secundarias para evitar carga innecesaria
		var	row = $(this).jqxGrid('getrowdata', index);
		// se extrae la información de los datos a modificar de base de datos
		setTimeout(function(){
			$.ajaxSetup({async: false, cache: false});
			$.post(modelo, { method:'edit', identificador: row.identificador })
			.then(function(res){
				// declaración de variables
				var	titulo = 'Error de Datos', $spanContent = $('<span>');
				// comprobación de éxito
				if(res.success){
					var	valorUR = $('#selectUnidadNegocio').val();
					fnLimpiarCamposFormConHidden(idForma);
					$('#selectUnidadNegocio').multiselect('select', valorUR);
					$.each(res.content, function(index, val) {
						if(index == 'identificador'){
							$('#forma').append('<input type="text" name="identificador" id="identificador" value="'+val+'" class="hidden"/>');
							CamposABloquear = true; // Línea adicional para este módulo
						}else{
							if(index=='ln_clave'){
								ln_clave = val;
							}
							if(index == 'categoryid'){ // Línea adicional para este módulo
								$('#forma').append('<input type="text" name="CampoLlave" id="CampoLlave" value="'+val+'" class="hidden"/>');
							}
							if($('#'+index).is("select")){
								$('#'+index).multiselect('select', val);
							}else{
								$('#'+index).val(val);
								if(index=="stockact"||index=="accountegreso"){
									if(!window.buscadoresConfiguracion[index].arregloEstatico&&window.buscadoresConfiguracion[index].origenDatos.substr(0,7)=="cuentas"&&(!window.buscadoresConfiguracion[index].valorAMostrar||window.buscadoresConfiguracion[index].valorAMostrar=="id")){
										for(var c=0;c<val.length;c++){
											if(val.substr(c,1)=="."){
												administraArregloBusqueda(val.substr(0,c+1),index);
											}
										}
									}
									$('#textoVisible__'+index).val(val);
								}
							}
						}
					});
					if(ln_clave){
						var	ValoresDiferenciador = ln_clave.split('-');

						$('#txtpp').multiselect('select', ValoresDiferenciador.pop());
						$('#selectUnidadEjecutora').multiselect('select', ValoresDiferenciador.pop());
						$('#selectUnidadNegocio').multiselect('select', ValoresDiferenciador.pop());
						$('#txtpp').trigger('change');
					}
					if(!($('#categorydescription').val()&&!$('#categoryid').val())){
						fnTraeInformacionPartida(document.getElementById("categoryid"));
					}
					fbCamposABloquear(CamposABloquear);
					AlinearSelectsDerecha();
					setTimeout(function(){
						$('#modalUsoGeneral #tituloModal').html('<i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Modificar Registro');
						$('#modalUsoGeneral').modal('show');
						$.each(window.buscadores,function(index, valor){
							var	x = $("#textoVisible__"+valor).val();
							$("#textoVisible__"+valor).val('');
							$("#textoVisible__"+valor).trigger('focusout');
							$("#textoVisible__"+valor).val(x);
							$("#textoVisible__"+valor).trigger('focusout');
						});
						ocultaCargandoGeneral();
					}, 500);
				}else{
					setTimeout(function(){
						ocultaCargandoGeneral();
					}, 500);
				}
			});
		}, 500);
	})
	// comportamiento de eliminación del registro
	.on('cellselect','#tablaGrid',function(e){
		// declaración de variables
		var	index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
		// validación de evento a lanzar
		if(campo != 'eliminar'){ return false; }
		// declaración de variables secundarias para evitar carga innecesaria
		var	row = $(this).jqxGrid('getrowdata', index), $spanContent = $('<span>'),
			content = '¿Realmente desea eliminar el elemento <strong>'+row.categoryid+'</strong>?',
			btnElimina = $('<button>',{ class : 'btn btn-primary btn-sm bgc8', html : 'Aceptar',
				click : function(){
					$.ajaxSetup({async: false, cache: false});
					$.post(modelo, {method:'destroy', identificador:row.identificador})
					.then(function(res){
						var	titulo = 'Error de Datos';
						if(res.success){
							titulo = 'Operación Exitosa';
							llenaTabla(res.content);
						}
						muestraModalGeneral(3, titulo, res.msg);
					});
				}
			});
		// agregado de los botones de acción necesarios
		$spanContent.append(btnElimina).append(btnCancel);
		// ejecución de render de Vue en caso de que se tengan componentes
		muestraModalGeneralConfirmacion(3, 'Confirmación', content, $spanContent);
	});
	// comportamiento de partida en filtro de búsqueda
	$("#busquedaConcepto").keyup(function(event){
		DescripcionPartida = ( this.value.length==3 ? fnInformacionPartidaGenericaGeneralMatrices(this.value) : "Sin partida seleccionada" );
		$( "#DescripcionPartida" ).html( DescripcionPartida ? DescripcionPartida : "La partida genérica no existe" );
	});
	// Estilo para que los elementos se desplieguen bien en vista colapsada, y que además los multi select sobresalagan de los modales en la vista completa
	$('html > head').append( '<style type="text/css"> .OverdeSelectsenModales{ overflow-y: auto; overflow-y: auto; } @media (min-width: 992px){ .OverdeSelectsenModales{ overflow-x: visible !important; overflow-y: visible !important; } } </style>' );
});
/*********************************** FUNCIONES DE EJECUCCIÓN ***********************************/
/**
 * Función de configuración inicial, donde se generan e inician las variables que serán
 * usadas en el programa
 */
function inicioPanel() {
	// variables globales del sistema
	this.root = window;
	this.rootFi = 0;
	this.rootFu = 0;
	this.url = getUrl();
	this.modelo = this.url+'/modelo/matriz_extra_presupuestales_Modelo.php';
	this.modeloComponentes = this.url+'/modelo/componentes_modelo.php';
	this.idForma = 'forma';
	this.btnCancel = $('<button>',{ class:'btn btn-primary btn-sm bgc8',html:'Cancelar','data-dismiss':'modal' });
	this.baseOption = '<option value="0">Seleccione una opción</option>';
	// funciones principales del sistema
	llenaTabla();
	//cargaInicial();
	// colocación de estilos en mensaje de modal
	$('#'+this.idForma).css({ 'max-height':'600px', 'min-height':'200px' });
	// comportamiento de apertura y cierre de la modal de captura
	$('#modalUsoGeneral').on('hidden.bs.modal',function(){
		//var	limpiarSelects = ['partida','tg','ff'];
		//$.each(limpiarSelects,function(index,el){ $('#'+el).multiselect('select',0).multiselect('rebuild'); });
		if($('#modalUsoGeneral').find('#identificador').size()){
			var	valorUR = $('#selectUnidadNegocio').val();
			fnLimpiarCamposFormConHidden(idForma);
			$('#selectUnidadNegocio').multiselect('select', valorUR);
		}
		$('#modalUsoGeneral').find('#identificador').remove();
		$('#modalUsoGeneral').find('#CampoLlave').remove(); // Línea adicional para este módulo
		$('#msjValidacion').empty();
	});
	// mensaje de confirmación de inicio
	console.log('listo el panel matriz extra presupuestal');

	// Líneas del archivo original
	cargaSelects();
	$('#categorydescription').attr('readonly', true); // Línea adicional para este módulo
}
/**
 * Función para el llenado de la tabla principal con los datos
 * enviados como parámetro
 * @param {Array} data Contenido que será cargado en la tabla
 */
function llenaTabla(data) {
	// declaración de variables principales
	var	data = data||[], el = 'contenedorTabla', tabla = 'tablaGrid', nameExcel = 'Matriz Extra Presupuestal'
		, tblObj, tblTitulo, tblExcel=[0,1,2,3,4,5,6,7,8,9,10,11], tblVisual=[0,1,2,3,4,5,6,7,8,9,10,11];
	tblObj = [
		{ name: 'ln_clave', type: 'string'},// 0
		{ name: 'categoryid', type: 'string'},// 1
		{ name: 'ordendesplegar', type: 'string'},// 2
		{ name: 'descripcionOrigen', type: 'string'},// 3
		{ name: 'descripcionProceso', type: 'string'},// 4
		{ name: 'categorydescription', type: 'string'},// 5
		{ name: 'nu_tipo_gasto', type: 'string'},// 6
		{ name: 'stockact', type: 'string'},// 7
		{ name: 'nombreCargo', type: 'string'},// 8
		{ name: 'accountegreso', type: 'string'},// 9
		{ name: 'nombreAbono', type: 'string'},// 10
		{ name: 'estatus', type: 'string'},// 11
		{ name: 'modificar', type: 'string'},// 12
		{ name: 'eliminar', type: 'string'},// 13
		{ name: 'identificador', type: 'string'},// 14
	];
	tblTitulo = [
		{ text: 'Diferenciador', datafield: 'ln_clave', editable: false, width: '11%', cellsalign: 'center', align: 'center' },// 0
		{ text: 'Partida Genérica', datafield: 'categoryid', editable: false, width: '11%', cellsalign: 'center', align: 'center' },// 1
		{ text: 'Descripción', datafield: 'categorydescription', editable: false, width: '29%', cellsalign: 'center', align: 'center' },// 5
		{ text: 'Clave', datafield: 'ordendesplegar', editable: false, width: '11%', cellsalign: 'center', align: 'center' },// 2
		{ text: 'Origen', datafield: 'descripcionOrigen', editable: false, width: '7%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Proceso', datafield: 'descripcionProceso', editable: false, width: '7%', cellsalign: 'center', align: 'center' },// 4
		{ text: 'Tipo Gasto', datafield: 'nu_tipo_gasto', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 6
		{ text: 'Cargo', datafield: 'stockact', editable: false, width: '6%', cellsalign: 'center', align: 'center' },// 7
		{ text: 'Cuenta Cargo', datafield: 'nombreCargo', editable: false, width: '19%', cellsalign: 'center', align: 'center' },// 8
		{ text: 'Abono', datafield: 'accountegreso', editable: false, width: '6%', cellsalign: 'center', align: 'center' },// 9
		{ text: 'Cuenta Abono', datafield: 'nombreAbono', editable: false, width: '19%', cellsalign: 'center', align: 'center' },// 10
		{ text: 'Estatus', datafield: 'estatus', editable: false, width: '7%', cellsalign: 'center', align: 'center' },// 11
		{ text: 'Modificar', datafield: 'modificar', editable: false, width: '7%', cellsalign: 'center', align: 'center' },// 12
		//{ text: 'Eliminar', datafield: 'eliminar', editable: false, width: '5%', cellsalign: 'center', align: 'center' },// 13
	];
	// llamado de limpieza de la tabla
	fnLimpiarTabla(el,tabla);
	// render de la tabla
	fnAgregarGrid_Detalle_nostring(data, tblObj, tblTitulo, tabla, ' ', 1, tblExcel, false, true, "", tblVisual, nameExcel);

	// resize del ancho de las columnas del Grid cada vez que hay cambio de página
	$("#"+tabla).bind("pagechanged", function (event) {
		if(typeof window.propiedadesResize != "undefined"){
			fnGridResize(tabla,window.propiedadesResize);
		}
	});
	if(typeof window.propiedadesResize != "undefined"){
		fnGridResize(tabla,window.propiedadesResize);
	}
}
/**
 * Función de búsqueda de datos inicial
 */
function cargaInicial() {
	// solicitud al servidor de la información para la configuración
	$.ajaxSetup({async: false, cache: false});
	$.post(this.modelo, {method:'show'}).then(function(res){
		llenaTabla(res.content);
	});
}
/************************* HELPERS *************************/
/**
 * Función para obtener la base de la url sin importar
 * en donde se encuentra el sistema
 * @return {String} Url encontrada según el proceso de filtrado
 */
function getUrl() {
	// declaración de variables prinsipales
	var	url = this.location.href.split('/');
	url.splice(url.length - 1);
	// retorno de información
	return url.join('/');
}

/* Funciones del archivo original */
function fbCamposABloquear(tipoAccion){
	var	CamposABloquear = [{
			'selectUnidadNegocio':'UR',
			'selectUnidadEjecutora':'UE',
			'txtpp':'Programa Presupuestario',
			'categoryid':'Partida Genérica',
			'textoVisible__stockact':'Recepción Cargo',
			'textoVisible__accountegreso':'Recepción Abono'
		}];

	$.each(CamposABloquear[0],function(index, el) {
		if($('#'+index).is("select")){
			$('#'+index).multiselect( tipoAccion ? "disable" : "enable" );
		}else{
			//$('#'+index).prop('readonly', tipoAccion);
			$('#'+index).attr('readonly', tipoAccion);
			if(!tipoAccion){
				$('#'+index).removeAttr("readonly");
			}
		}
	});
}

function fnMostrarDatos(origenDeAccion=false){
	window.busquedaPorUsuario = ( !window.busquedaPorUsuario&&origenDeAccion ? true : window.busquedaPorUsuario );
	if(window.busquedaPorUsuario){
		muestraCargandoGeneral();
		setTimeout(function(){
			$.ajaxSetup({async: false, cache: false});
			$.post(this.modelo, {method:'show', info: getParams('frmFiltroActivos')}).then(function(res){
				llenaTabla(res.content);
			});
			ocultaCargandoGeneral();
		}, 500);
	}
}

function cargaSelects(){
	var	Elemento = "";
	$.ajaxSetup({async: false, cache: false});
	$.post(this.modelo, {method:'datosselectOrigen'}).then(function(res){
		$.each(res.content, function(index, val) {
			$('#factesquemadoancho').append(new Option(val.label, val.value));
		});
		fnFormatoSelectGeneral(".factesquemadoancho");
	});
	$.ajaxSetup({async: false, cache: false});
	$.post(this.modelo, {method:'datosselectProceso'}).then(function(res){
		$.each(res.content, function(index, val) {
			$('#factesquemadoalto').append(new Option(val.label, val.value));
		});
		fnFormatoSelectGeneral(".factesquemadoalto");
	});
	$.ajaxSetup({async: false, cache: false});
	$.post(this.modelo, {method:'datosselectTipoPrograma'}).then(function(res){
		$.each(res.content, function(index, val) {
			window.tiposDeProgramas[val.value] = val.label;
		});
	});
	fnFormatoSelectGeneral(".busquedaEstatus");
	fnFormatoSelectGeneral(".ind_activo");
}

function AlinearSelectsDerecha(){
	if($("#ColumnaDerecha").find("ul[class='multiselect-container dropdown-menu']").size()){
		$("#ColumnaDerecha").find("ul[class='multiselect-container dropdown-menu']").addClass("pull-right");
	}
}

/**
 * Función para obtener la descripción de la Partida Genérica
 * @param {[type]} elemento Recibe la caja de texto
 * @return {[type]}		[description]
 */
function fnTraeInformacionPartida(elemento) {
	// Obtener la descripcion de la partida generica
	if (elemento.value.length == 3) {
		// Traer descripción de la partida generica
		var	descripcionPartida = fnInformacionPartidaGenericaGeneralMatrices(elemento.value);
		$("#categorydescription").val(""+descripcionPartida);
		if(!descripcionPartida){
			elemento.value = "";
		}
		//$("#CategoryDescriptionVisual").val(""+descripcionPartida);
	} else {
		// Si no es partida generica
		$("#categorydescription").val("");
		//$("#CategoryDescriptionVisual").val("");
		elemento.value = "";
	}
	validacionExtraPresupuestal();
}

/**
 * Función para obtener la descripción de la partida genérica
 * @param	{[type]}	partidaGenerica Partida Genérica a 3
 * @return	{[type]}	Descripción de la Partida Genérica
 */
function fnInformacionPartidaGenericaGeneralMatrices(partidaGenerica) {
	// Obtiene la descripcion de la partida generica
	var	descripcionPartida = '';
	dataObj = { 
		option: 'muestraDesPartidaGenerica',
		partidaGenerica: partidaGenerica
	};
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/componentes_modelo.php",
		data:dataObj
	})
	.done(function( data ) {
		if(data.result){
			descripcionPartida = data.contenido;
		}else{
			descripcionPartida = '';
		}
	})
	.fail(function(result) {
		//console.log("ERROR");
		//console.log( result );
	});

	return descripcionPartida;
}
