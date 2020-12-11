// objetos de configuración
	window.listaClavesProducto = new Array();

	window.listadoHabilitado = true;

	window.buscadores = [ "claveprod" ];
	window.buscadoresConfiguracion = {};
	$.each(window.buscadores,function(index,valor){
		window.buscadoresConfiguracion[valor] = {};
	});
	window.buscadoresConfiguracion.claveprod.origenDatos = "listaClavesProducto";

	window.buscadoresConfiguracion.claveprod.valoresAlfanumericos = true;
	window.buscadoresConfiguracion.claveprod.valorAMostrar = "concatenado";
	window.buscadoresConfiguracion.claveprod.tipoBusqueda = "incluye";

	window.buscadoresConfiguracion.claveprod.arregloEstatico = true;

// asignación de eventos
$(document).ready(function() {
	// llamado de función inicial
	inicioPanel();
	$(document).on('change','#selPartida',function(){
		fnSeleccionaDesdeListado("vacio","claveprod");
	});
	$(".campoListado").keyup(function(){
		if(!$(this).attr('id')){ return; }
		var id = $(this).attr('id').replace("textoVisible__",""),
			idDiv = "#sugerencia-"+id;

		fnConfirmarConfiguracionDeListado(id);

		if(!window.listadoHabilitado){
			return;
		}

		if($(this).val()!=''){
			var buscar = $(this).val();
			if(window.buscadoresConfiguracion[id].origenDatos.substr(0,7)=="cuentas"&&(!window.buscadoresConfiguracion[id].valorAMostrar||window.buscadoresConfiguracion[id].valorAMostrar=="id")){
				while(buscar.indexOf("..")>0){
					buscar = buscar.split("..").join(".");
				}
			}

			var retorno = "<ul id='articulos-lista-consolida'>",
				buscarCoincidencia = fnListadoTipoBusqueda(buscar,id),
				arregloBusqueda = window[window.buscadoresConfiguracion[id].origenDatos];

			if(!window.buscadoresConfiguracion[id].arregloEstatico&&window.buscadoresConfiguracion[id].origenDatos.substr(0,7)=="cuentas"&&(!window.buscadoresConfiguracion[id].valorAMostrar||window.buscadoresConfiguracion[id].valorAMostrar=="id")){
				administraArregloBusqueda(buscar,id);
			}

			var arr = jQuery.map(arregloBusqueda, function (value,index) {
				var esValido = ( !window.buscadoresConfiguracion[id].cuentasAprobadas||(window.buscadoresConfiguracion[id].cuentasAprobadas&&!!window.buscadoresConfiguracion[id].valorAMostrar&&window.buscadoresConfiguracion[id].valorAMostrar!="id") ? true : false );
				if(!esValido){
					$.each(window.buscadoresConfiguracion[id].cuentasAprobadas,function(ind, valorAprobado){
						var comienzaCon = value.valor.length>valorAprobado.length ? valorAprobado : value.valor,
							contiene = value.valor.length>valorAprobado.length ? value.valor : valorAprobado;
						esValido = ( contiene.substr(0,comienzaCon.length)==comienzaCon ? true : esValido );
					});
				}
				return esValido&&fnListadoValorMostrado(value,id).match(buscarCoincidencia) ? ( nivelDeCuenta(value.valor)<6 ? index : window.uePorUsuario.includes(value.valor.split('.')[5]) ? index : null ) : null;
			});

			for(a=0;a<arr.length;a++){
				val = arr[a];
				if($("#selPartida").val().indexOf(arregloBusqueda[val].partida)!="-1"){
					retorno+="<li onClick='fnSeleccionaDesdeListado(\""+val+"\",\""+id+"\")'><a"+( !window.buscadoresConfiguracion[id].sinHREF ? " href='#'" : "" )+">"+arregloBusqueda[val].valor+" - "+arregloBusqueda[val].texto+"</a></li>";
				}
			}

			retorno+="</ul>";

			$.each(window.buscadores,function(index, valor){
				if(idDiv!="#sugerencia-"+valor){
					$("#sugerencia-"+valor).hide();
					$("#sugerencia-"+valor).empty();
				}
			});

			$(idDiv).show();
			$(idDiv).empty();
			$(idDiv).append(retorno);
		}else{
			$(idDiv).hide();
			$(idDiv).empty();
		}
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
	this.modelo = this.url+'/modelo/GeneralInventoryToDateV2_Modelo.php';
	this.modeloComponentes = this.url+'/modelo/componentes_modelo.php';
	this.idForma = 'forma';
	this.btnCancel = $('<button>',{ class:'btn btn-primary btn-sm bgc8',html:'Cancelar','data-dismiss':'modal' });
	this.baseOption = '<option value="0">Seleccione una opción</option>';
	// funciones principales del sistema
	llenaTabla();
	//cargaInicial();
	// colocación de estilos en mensaje de modal
	$('#'+this.idForma).css({ 'max-height':'600px', 'min-height':'200px' });
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
function llenaTabla(data,totales) {
	// declaración de variables principales
	var data = data||[], totales = totales||[], el = 'contenedorTabla', tabla = 'tablaGrid', nameExcel = 'Reporte General de Estado de Existencias'
		, tblObj, tblTitulo, tblExcel=[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14], tblVisual=[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14];
	tblObj = [
		{ name: 'partidaespecifica', type: 'string'},// 0
		{ name: 'clave', type: 'string'},// 1
		{ name: 'descripcion', type: 'string'},// 2
		{ name: 'almacen', type: 'string'},// 3
		{ name: 'unidadmedida', type: 'string'},// 4
		{ name: 'inventarioinicial', type: 'string'},// 5
		{ name: 'entradas', type: 'string'},// 6
		{ name: 'salidas', type: 'string'},// 7
		{ name: 'existencias', type: 'string'},// 8
		{ name: 'entransito', type: 'string'},// 9
		{ name: 'disponibles', type: 'string'},// 10
		{ name: 'semaforo', type: 'string'},// 10
		{ name: 'costopromedio', type: 'string'},// 11
		{ name: 'ultimocosto', type: 'string'},// 12
		{ name: 'costomasalto', type: 'string'},// 13
		{ name: 'valorinventario', type: 'string'},// 14
	];
	tblTitulo = [
		{ text: 'Partida Específica', datafield: 'partidaespecifica', editable: false, width: '6%', cellsalign: 'center', align: 'center' },// 0
		{ text: 'Clave', datafield: 'clave', editable: false, width: '12%', cellsalign: 'center', align: 'center' },// 1
		{ text: 'Descripción', datafield: 'descripcion', editable: false, width: '29%', cellsalign: 'center', align: 'center' },// 2
		{ text: 'Almacén', datafield: 'almacen', editable: false, width: '15%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Unidad Medida', datafield: 'unidadmedida', editable: false, width: '6%', cellsalign: 'center', align: 'center', 
		aggregates: [{ "": function (aggregatedValue, currentValue) {
			return "";
			} 
		}], 
		aggregatesrenderer: function (aggregates, column, element) {
			return Object.keys(totales).length ? '<div style="text-align: right;"><strong>TOTALES</strong></div>' : "";
		} },// 4
		{ text: 'Inventario Inicial', datafield: 'inventarioinicial', editable: false, width: '6%', cellsalign: 'right', align: 'center', 
		aggregates: [{ "": function (aggregatedValue, currentValue) {
			return '';
			} 
		}], 
		aggregatesrenderer: function (aggregates, column, element) {
			return totales.inventarioinicial;
		} },// 5
		{ text: 'Entradas', datafield: 'entradas', editable: false, width: '7%', cellsalign: 'right', align: 'center', 
		aggregates: [{ "": function (aggregatedValue, currentValue) {
			return '';
			} 
		}], 
		aggregatesrenderer: function (aggregates, column, element) {
			return totales.entradas;
		} },// 6
		{ text: 'Salidas', datafield: 'salidas', editable: false, width: '7%', cellsalign: 'right', align: 'center', 
		aggregates: [{ "": function (aggregatedValue, currentValue) {
			return '';
			} 
		}], 
		aggregatesrenderer: function (aggregates, column, element) {
			return totales.salidas;
		} },// 7
		{ text: 'Exis', datafield: 'existencias', editable: false, width: '7%', cellsalign: 'right', align: 'center', 
		aggregates: [{ "": function (aggregatedValue, currentValue) {
			return '';
			} 
		}], 
		aggregatesrenderer: function (aggregates, column, element) {
			return totales.existencias;
		} },// 8
		{ text: 'Trans', datafield: 'entransito', editable: false, width: '7%', cellsalign: 'right', align: 'center', 
		aggregates: [{ "": function (aggregatedValue, currentValue) {
			return '';
			} 
		}], 
		aggregatesrenderer: function (aggregates, column, element) {
			return totales.entransito;
		} },// 9
		{ text: 'Disp', datafield: 'disponibles', editable: false, width: '7%', cellsalign: 'right', align: 'center', 
		aggregates: [{ "": function (aggregatedValue, currentValue) {
			return '';
			} 
		}], 
		aggregatesrenderer: function (aggregates, column, element) {
			return totales.disponibles;
		} },// 10
		{ text: 'Aviso', datafield: 'semaforo', editable: false, width: '7%', cellsalign: 'center', align: 'center' },
		{ text: 'Promedio', datafield: 'costopromedio', editable: false, width: '7%', cellsalign: 'right', align: 'center' },// 11
		{ text: 'Último', datafield: 'ultimocosto', editable: false, width: '7%', cellsalign: 'right', align: 'center' },// 12
		{ text: '+ Alto', datafield: 'costomasalto', editable: false, width: '7%', cellsalign: 'right', align: 'center' },// 13
		{ text: 'Valor Inv', datafield: 'valorinventario', editable: false, width: '10%', cellsalign: 'right', align: 'center', 
		aggregates: [{ "": function (aggregatedValue, currentValue) {
			return '';
			} 
		}], 
		aggregatesrenderer: function (aggregates, column, element) {
			return totales.valorinventario;
		} },// 14
	];
	// llamado de limpieza de la tabla
	fnLimpiarTabla(el,tabla);
	// render de la tabla
	fnAgregarGrid_Detalle_nostring(data, tblObj, tblTitulo, tabla, ' ', 1, tblExcel, false, true, "", tblVisual, nameExcel);

	// Resize de todas las columnas
	$("#"+tabla).jqxGrid('columnsheight', '33px');
	$("#"+tabla).jqxGrid('setcolumnproperty', 'partidaespecifica', 'renderer', function () {
		return '<div style="margin-top: 0px; margin-left: 0px; text-align: center;">Partida<br />Específica</div>';
	});
	$("#"+tabla).jqxGrid('setcolumnproperty', 'unidadmedida', 'renderer', function () {
		return '<div style="margin-top: 0px; margin-left: 0px; text-align: center;">Unidad<br />Medida</div>';
	});
	$("#"+tabla).jqxGrid('setcolumnproperty', 'inventarioinicial', 'renderer', function () {
		return '<div style="margin-top: 0px; margin-left: 0px; text-align: center;">Inventario<br />Inicial</div>';
	});
}
/**
 * Función de búsqueda de datos inicial
 */
function cargaInicial() {
	// solicitud al servidor de la información para la configuración
	$.post(this.modelo, {method:'show'}).then(function(res){
		llenaTabla(res.content,res.totales);
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
function fnMostrarDatos(){
	$.post(this.modelo, {method:'show', info: getParams('frmFiltroActivos')}).then(function(res){
		llenaTabla(res.content,res.totales);
	});
}

// Función para cargar datos en selects y buscadores de texto. También convierte los selects a multiselects.
function cargaSelects(){
	$.post(this.modelo, {method:'datosselectPartidaEspecifica'}).then(function(res){
		$.each(res.content, function(index, val) {
			$('#selPartida').append(new Option(val.label, val.value));
		});
		fnFormatoSelectGeneral(".selPartida");
	});
	$.post(this.modelo, {method:'datosselectAlmacen'}).then(function(res){
		$.each(res.content, function(index, val) {
			$('#selAlmacen').append(new Option(val.label, val.value));
		});
		fnFormatoSelectGeneral(".selAlmacen");
	});
	if(!!window.buscadores){
		if(window.buscadores.length){
			if(!window.listaClavesProducto.length){
				$.ajaxSetup({async: false});
				$.post(this.modelo, {method:'datosListaCuentasCargo'}).then(function(res){
					window.listaClavesProducto = res.registrosEncontrados;
					window.listaClavesProducto.sort(ordenamientoDinamico("valor"));
				});
			}
		}
	}
	fnFormatoSelectGeneral(".SoloExistencias");
}

// Cambia el formato de los multiselects, para que las opciones flotantes se alineén a la derecha del campo en lugar de hacerlo a la izquierda.
function AlinearSelectsDerecha(){
	if($("#ColumnaDerecha").find("ul[class='multiselect-container dropdown-menu']").size()){
		$("#ColumnaDerecha").find("ul[class='multiselect-container dropdown-menu']").addClass("pull-right");
	}
}
