// asignación de eventos


var isValidProduct = false;
var fechaIni;
var fechaFin;
var contratoID;
var selects = [];


$(document).ready(function() {
	$("#isRango").change(function(){
		$('#importe').val('');
		$('#rangoFinal').val('');
		$('#rangoInicial').val('');
	});
	// fnBusquedaContribuyente();

	contratoID = $('#contratosID').val();
	
	// llamado de función inicial
	inicioPanel();

	
	
	// comportamiento del botón de guardado
	$('#guardar').on('click',function(){
			var params = getParams(idForma), campos = getMatchets(idForma), msg = '',
				nombreCampo = {
					'importe':'Valor',
					'rangoInicial':'Rango Inicial',
					'rangoFinal':'Rango Final'
				},
				method = params.hasOwnProperty('identificador')?'update':'store';
			$.extend(params,{method:method,'valid':1});
			
			$.post(modelo, params).then(function(res){
				var titulo=res.success?'Operación Exitosa':'Error de Datos';
				if(res.success){
					fnLimpiarCamposFormConHidden(idForma);
				}
				// llenaTabla(res.content);
				muestraModalGeneral(3,titulo,res.msg);
			});
			$('#modalUsoGeneral').modal('hide');
			$('#productDescription').text('Sin Producto/Servicio seleccionado');
			$('#productDescription').css('color', 'black');

		
	});
	// comportamiento de modificación de registro
	// comportamiento de eliminación del registro
	// Estilo para que los elementos se desplieguen bien en vista colapsada, y que además los multi select sobresalagan de los modales en la vista completa
	$('html > head').append( '<style type="text/css"> .OverdeSelectsenModales{ overflow-y: auto; overflow-y: auto; } @media (min-width: 992px){ .OverdeSelectsenModales{ overflow-x: visible !important; overflow-y: visible !important; } } </style>' );

	$("#isRango").click(function() {  
		this.value = this.checked ? 1 : 0;
		if($("#isRango").is(':checked')) { 
			$('#rangoInicial').val('');
			$('#rangoFinal').val('');
			$('#rangoInicial').removeAttr('readonly');
			$('#rangoFinal').removeAttr('readonly');
			$('#importe').attr('readonly', true);
		} else {  
			$('#rangoInicial').attr('readonly', true);
			$('#rangoFinal').attr('readonly', true);
			$('#importe').removeAttr('readonly');
			$('#importe').val('');
		}  
   });  

	


   

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
	this.modelo = this.url+'/modelo/historialPaseCobroModelo.php';
	this.modeloComponentes = this.url+'/modelo/componentes_modelo.php';
	this.idForma = 'forma';
	this.btnCancel = $('<button>',{ class:'btn btn-primary btn-sm bgc8',html:'Cancelar','data-dismiss':'modal' });
	this.baseOption = '<option value="0">Seleccione una opción</option>';
	llenaTabla();
	cargaInicial();

	// funciones principales del sistema
	// cargaInicial();
	// cargaProductos();
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
	fechaIni =$('#dtFechaInicio').val();
	fechaFin =$('#dtFechaVigencia').val();


	// Líneas del archivo original
	// cargaSelects();
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
		, tblObj, tblTitulo, tblExcel=[0,1,2,3], tblVisual=[0,1,2,3,4,5];
	tblObj = [
		// { name: 'checkbox', type: 'string'},// 3
		{ name: 'imprimir', type: 'string'},// 7
		{ name: 'clave', type: 'string'},// 3
		{ name: 'contrato', type: 'string'},// 1
		{ name: 'contribuyente', type: 'string'},// 4
		{ name: 'periodo', type: 'string'},// 7
		{ name: 'objetoPrincipal', type: 'string'},// 0
		{ name: 'objetoParcial', type: 'string'},// 0
		{ name: 'cantidad', type: 'string'},// 0
		{ name: 'importe', type: 'string'},// 0
		{ name: 'descuento', type: 'string'},// 0
		{ name: 'total', type: 'string'},// 0
		{ name: 'vencimiento', type: 'string'},// 0
		{ name: 'cobro', type: 'string'},// 0
		{ name: 'recibo', type: 'string'},// 0
		{ name: 'cajero', type: 'string'},// 0
		{ name: 'fechaEfectiva', type: 'string'},// 0
		{ name: 'fechaPago', type: 'string'},// 0 
		{ name: 'identificador', type: 'string'}// 9
	];
	tblTitulo = [
		// { text: 'Seleccionar', datafield: 'checkbox', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Imprimir', datafield: 'imprimir', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Clave', datafield: 'clave', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Periodo', datafield: 'periodo', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Pase Cobro', datafield: 'cobro', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 0
		{ text: 'Contrato', datafield: 'contrato', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Contribuyente', datafield: 'contribuyente', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Objeto Principal', datafield: 'objetoPrincipal', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Pbjeto Parcial', datafield: 'objetoParcial', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Cantidad', datafield: 'cantidad', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Importe', datafield: 'importe', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Descuento', datafield: 'descuento', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Total', datafield: 'total', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Vencimiento', datafield: 'vencimiento', editable: false, width: '20%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Recibo', datafield: 'recibo', editable: false, width: '15%', cellsalign: 'center', align: 'center' },// 4
		{ text: 'Cajero', datafield: 'cajero', editable: false, width: '15%', cellsalign: 'center', align: 'center' },// 7
		{ text: 'Fecha Efectiva', datafield: 'fechaEfectiva', editable: false, width: '15%', cellsalign: 'center', align: 'center' },// 7
		{ text: 'Fecha Pago', datafield: 'fechaPago', editable: false, width: '15%', cellsalign: 'center', align: 'center' },// 7

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
	$.post(this.modelo, {method:'show', contrato_id : contratoID}).then(function(res){
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
















 function openModalIframe(idContrato, idconfContrato,contribuyente,configuracion){

	console.log(contribuyente);
	
	var ruta = "abcPropiedadesAtributos.php?modal=true&id_contratos="+idContrato+"&id_configuracion="+idconfContrato+"&name="+contribuyente+"&conf="+configuracion;
	var contenido = '<div style="width: 100%; height: 400;"> <iframe className="" src="'+ruta+'" width="100%" height="400" frameBorder="0"></iframe> </div>';

	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Atributos del contrato</p></h3>';
	muestraModalGeneral(4, titulo, contenido);
}

function openModalIframeDos(idContrato, idconfContrato, contribuyente){
	
	var ruta = "abcObjetosParcialesContrato.php?modal=true&id_contratos="+idContrato+"&id_configuracion="+idconfContrato+"&name="+contribuyente;
	var contenido = '<div style="width: 100%; height: 400;"> <iframe className="" src="'+ruta+'" width="100%" height="400" frameBorder="0"></iframe> </div>';

	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Detalle de cobro</p></h3>';
	muestraModalGeneral(4, titulo, contenido);
}

function fnImprimir(folio,id){
	
	var ruta = "historialPaseCobro.php?modal=true&id_contratos="+folio;
	var contenido = '<div style="width: 100%; height: 400;"> <iframe className="" src="'+ruta+'" width="100%" height="400" frameBorder="0"></iframe> </div>';

	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i>Historial de Pases de Cobro</p></h3>';
	muestraModalGeneral(4, titulo, contenido);
    
}
