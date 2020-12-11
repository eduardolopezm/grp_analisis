// asignación de eventos
$(document).ready(function() {
	// llamado de función inicial
	inicioPanel();
	// comportamiento del botón nuevo
	$('#nuevo').on('click',function(){
		fbCamposABloquear(false);
		AlinearSelectsDerecha();
		$('#tituloModal').html('<i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Nuevo Almacén');
		$('#modalUsoGeneral').modal('show');
	});
	// comportamiento del botón de guardado
	$('#guardar').on('click',function(){
		var params = getParams(idForma), campos = getMatchets(idForma), msg = '',
			nombreCampo = {
				'selectUnidadNegocio':'UR',
				'selectUnidadEjecutora':'UE',
				'LocCode':'Código de Almacén'
			},
			method = params.hasOwnProperty('identificador')?'update':'store';
		$.extend(params,{method:method,'valid':1});
		$.each(campos,function(index, el) {
			////if(el=='ind_activo'&&params[el]!=-1){ return; } // Línea adicional para este módulo
			if($("#"+el).is(":disabled")){ return; }
			if(el=="selectUnidadEjecutora"&&$("#"+el).val()=="00"){ return; } // Línea adicional para este módulo
			if(!params.hasOwnProperty(el)){ return; }
			// if(esepcion.indexOf(el)!==-1){ return; }
			if(params[el]!=0&&params[el]!=-1){ return; }
			if(nombreCampo[el]===undefined){ return; }
			msg += 'El campo '+nombreCampo[el]+' no puede ir vacío.<br />';
		});
		if(msg!=''){ muestraModalGeneral(3,'Error de datos',msg); return;}
		$.post(modelo, params).then(function(res){
			var titulo=res.success?'Operación Exitosa':'Error de Datos';
			if(res.success){
				fnLimpiarCamposFormConHidden(idForma);
			}
			llenaTabla(res.content);
			muestraModalGeneral(3,titulo,res.msg);
		});
	});
	// comportamiento de modificación de registro
	$(document).on('cellselect','#tablaGrid',function(e){
		var CamposABloquear = false; // Línea adicional para este módulo
		// declaración de variables
		var index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
		// confirmación de evento a lanzar
		if(campo != 'modificar'){ return false; }
		// declaración de variables secundarias para evitar carga innecesaria
		var row = $(this).jqxGrid('getrowdata', index);
		// se extrae la información de los datos a modificar de base de datos
		$.post(modelo, { method:'edit', identificador: row.identificador })
		.then(function(res){
			// declaración de variables
			var titulo = 'Error de Datos', $spanContent = $('<span>');
			// comprobación de éxito
			if(res.success){
				fnLimpiarCamposFormConHidden(idForma);
				$.each(res.content, function(index, val) {
					if(index == 'identificador'){
						$('#forma').append('<input type="text" name="identificador" id="identificador" value="'+val+'" class="hidden"/>');
						CamposABloquear = true; // Línea adicional para este módulo
					}else{
						if($('#'+index).is("select")){
							$('#'+index).multiselect('select', val);
						}else{
							$('#'+index).val(val);
						}
					}
				});
				fbCamposABloquear(CamposABloquear);
				AlinearSelectsDerecha();
				$('#modalUsoGeneral #tituloModal').html('<i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Edición de Almacén');
				$('#modalUsoGeneral').modal('show');
			}
		});
	})
	// comportamiento de eliminación del registro
	.on('cellselect','#tablaGrid',function(e){
		// declaración de variables
		var index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
		// validación de evento a lanzar
		if(campo != 'eliminar'){ return false; }
		// declaración de variables secundarias para evitar carga innecesaria
		var row = $(this).jqxGrid('getrowdata', index), $spanContent = $('<span>'),
			content = '¿Realmente desea eliminar el elemento <strong>'+row.loccode+" - "+row.locationname+'</strong>?',
			btnElimina = $('<button>',{ class : 'btn btn-primary btn-sm bgc8', html : 'Aceptar',
				click : function(){
					$.post(modelo, {method:'destroy', identificador:row.identificador})
					.then(function(res){
						var titulo = 'Error de Datos';
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
	this.modelo = this.url+'/modelo/Locations_Modelo.php';
	this.modeloComponentes = this.url+'/modelo/componentes_modelo.php';
	this.idForma = 'forma';
	this.btnCancel = $('<button>',{ class:'btn btn-primary btn-sm bgc8',html:'Cancelar','data-dismiss':'modal' });
	this.baseOption = '<option value="0">Seleccione una opción</option>';
	// funciones principales del sistema
	llenaTabla();
	cargaInicial();
	// colocación de estilos en mensaje de modal
	$('#'+this.idForma).css({ 'max-height':'600px', 'min-height':'200px' });
	// comportamiento de apertura y cierre de la modal de captura
	$('#modalUsoGeneral').on('hidden.bs.modal',function(){
		//var limpiarSelects = ['partida','tg','ff'];
		//$.each(limpiarSelects,function(index,el){ $('#'+el).multiselect('select',0).multiselect('rebuild'); });
		if($('#modalUsoGeneral').find('#identificador').size()){
			fnLimpiarCamposFormConHidden(idForma);
		}
		$('#modalUsoGeneral').find('#identificador').remove();
	});
	// mensaje de confirmación de inicio
	console.log('listo el panel almacén');

	// Líneas del archivo original
	cargaSelects();
}
/**
 * Función para el llenado de la tabla principal con los datos
 * enviados como parámetro
 * @param {Array} data Contenido que será cargado en la tabla
 */
function llenaTabla(data) {
	// declaración de variables principales
	var data = data||[], el = 'contenedorTabla', 
		tabla = 'tablaGrid', 
		nameExcel = 'Almacenes'
		, tblObj, tblTitulo, tblExcel=[0,1,2,3,4,5], tblVisual=[0,1,2,3,4,5,6,7];
	tblObj = [
		{ name: 'loccode', type: 'string'},// 0
		{ name: 'tagdescription', type: 'string'},// 1
		{ name: 'uedescription', type: 'string'},// 2
		{ name: 'locationname', type: 'string'},// 3
		{ name: 'managed', type: 'string'},// 4
		{ name: 'temploc', type: 'string'},// 5
		{ name: 'estatus', type: 'string'},// 6
		{ name: 'modificar', type: 'string'},// 7
		{ name: 'eliminar', type: 'string'},// 8
		{ name: 'identificador', type: 'string'},// 9
	];
	tblTitulo = [
		{ text: 'Código', datafield: 'loccode', editable: false, width: '6%', cellsalign: 'center', align: 'center' },// 0
		{ text: 'UR', datafield: 'tagdescription', editable: false, width: '20%', cellsalign: 'center', align: 'center' },// 1
		{ text: 'UE', datafield: 'uedescription', editable: false, width: '29%', cellsalign: 'center', align: 'center' },// 2
		{ text: 'Descripción Almacén', datafield: 'locationname', editable: false, width: '19%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Inv', datafield: 'managed', editable: false, width: '6%', cellsalign: 'center', align: 'center' },// 4
		{ text: 'Temp', datafield: 'temploc', editable: false, width: '6%', cellsalign: 'center', align: 'center' },// 5
		//{ text: 'Estatus', datafield: 'estatus', editable: false, width: '7%', cellsalign: 'center', align: 'center' },// 6
		{ text: 'Modificar', datafield: 'modificar', editable: false, width: '7%', cellsalign: 'center', align: 'center' },// 7
		{ text: 'Eliminar', datafield: 'eliminar', editable: false, width: '7%', cellsalign: 'center', align: 'center' },// 8
	];
	// llamado de limpieza de la tabla
	fnLimpiarTabla(el,tabla);
	// render de la tabla
	fnAgregarGrid_Detalle_nostring(data, tblObj, tblTitulo, tabla, ' ', 1, tblExcel, false, true, "", tblVisual, nameExcel);
}
/**
 * Función de búsqueda de datos inicial
 */
function cargaInicial() {
	// solicitud al servidor de la información para la configuración
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
	// declaración de variables principales
	var url = this.location.href.split('/');
	url.splice(url.length - 1);
	// retorno de información
	return url.join('/');
}

/* Funciones del archivo original */
function fbCamposABloquear(tipoAccion){
	/*
			'selectUnidadNegocio':'UR',
			'selectUnidadEjecutora':'UE',
	*/
	var	CamposABloquear = [{
			'LocCode':'Código de Almacén'
		}];

	$.each(CamposABloquear[0],function(index, el) {
		if($('#'+index).is("select")){
			$('#'+index).
			multiselect( tipoAccion ? "disable" : "enable" );
		}else{
			//$('#'+index).prop('readonly', tipoAccion);
			$('#'+index).attr('readonly', tipoAccion);
			if(!tipoAccion){
				$('#'+index).removeAttr("readonly");
			}
		}
	});
}

function fnMostrarDatos(){
	$.post(this.modelo, {method:'show', info: getParams('frmFiltroActivos')}).then(function(res){
		llenaTabla(res.content);
	});
}

function cargaSelects(){
	var Elemento = "";
	$.post(this.modelo, {method:'datosselectImpuestos'}).then(function(res){
		$.each(res.content, function(index, val) {
			$('#TaxProvince').append(new Option(val.label, val.value));
		});
		fnFormatoSelectGeneral(".TaxProvince");
	});
}

function AlinearSelectsDerecha(){
	if($("#ColumnaDerecha").find("ul[class='multiselect-container dropdown-menu']").size()){
		$("#ColumnaDerecha").find("ul[class='multiselect-container dropdown-menu']").addClass("pull-right");
	}
}

function fnValidaDatos() {
	var mensaje= "";
	var notifica= false;

	if ($("#CategoryID").val() == "" || $("#CategoryID").val() == "0") {
		mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp; Debe capturar código de categoria para poder continuar.</p>'; 

		notifica= true;
	}

	if ($("#CategoryDescription").val() == "" || $("#CategoryDescription").val() == "0") {
		mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp; Debe capturar descripción de categoria para poder continuar.</p>'; 
		notifica= true;
	}

	if ($("#StockAct").val() == $("#accountegreso").val()) {
		mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp; Las cuentas de cargo y abono no pueden ser iguales.</p>'; 

		notifica= true;
	}

	if (notifica) {
		muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', mensaje);	

		return false;
	} else {
		return true;
	}
}
