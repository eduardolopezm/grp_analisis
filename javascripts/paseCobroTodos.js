// asignación de eventos


var isValidProduct = false;
var totalParciales = 0;
var parcials = '{"parcials":[]}';
var obj = JSON.parse(parcials);
var atributos = '{"atributos":[]}';
var obj2 = JSON.parse(atributos);
var confContrato;
var selects = [];
var placas = [];
var fechaInicial;
var contribuyenteDefault; 
var contribuyenteIDDefault;
var selectUnidadNegocio;
var selectUnidadEjecutora;
var isFirst = true;
var isFirstSearch = true;


$(document).ready(function() {
	confContrato = $('#idconfContrato').val();
	fechaInicial = $('#txtFechaInicial').val();
	selectUnidadEjecutora = $('#selectUnidadEjecutora').val();

	

	inicioPanel();

	$('#btnPase').on('click',GenerarPase);

	function GenerarPase(){
		muestraCargandoGeneral();
		
		$("#btnPase").attr("disabled", true);
		$.post(modelo, { method:'posPase', selects_id: selects, placa: placas })
		.then(function(res){
			// declaración de variables
			var titulo = 'Información de Datos', $spanContent = $('<span>');
			// comprobación de éxito
			if(res.success){
				titulo = 'Datos Guardados';
				// cargaInicial();
				selects = [];
				placas = [];
			
			}
			$('#modalUsoGeneral').modal('hide');
			
			muestraModalGeneral(3,titulo,res.msg);
			// $('#modalUsoGeneral').modal('hide');
			// $.each($('#tablaGrid').jqxGrid('getrows'), function( index, value ) {
			// 	$('#tablaGrid').jqxGrid('setcellvalue', index, 'selected', false);
			// });
			$("#btnPase").attr("disabled", false);
			

		});
		
		ocultaCargandoGeneral();
	}

	

	
	// comportamiento del botón de guardado
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
			var titulo = 'Información de Datos', $spanContent = $('<span>');
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
							$('#'+index).multiselect('rebuild');
							$('#'+index).trigger('change')

							if(index == 'selectObjetoPrincipal'){
								$('#'+index).multiselect('disable');
							}
							if(index == 'selectObjetoParcial'){
								$('#'+index).multiselect('disable');
							}
						}else{
							$('#'+index).val(val);
						}
					}
				});
				fbCamposABloquear(CamposABloquear);
				obj['parcials'] = [];
				parcials = JSON.stringify(obj);
				obj2['atributos'] = [];
				atributos = JSON.stringify(obj2);
				if(res.content.parcials){
					$.each(res.content.parcials, function(index, val) {
						obj['parcials'].push({
							"id": val.id,
							'price': val.price
						});
					});
				}
				parcials = JSON.stringify(obj);
				updatePrices();
				AlinearSelectsDerecha();
				$('#modalUsoGeneral #tituloModal').html('<i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Edición de Multas de Tránsito');
				$('#modalUsoGeneral').modal('show');

			}
		});
	})
	.on('rowselect','#tablaGrid',function(e){

		var rowindexes = $('#tablaGrid').jqxGrid('getselectedrowindexes');
		selects = [];
		placas = [];

		$(rowindexes).each(function( index, val ) {
			selects.push($("#tablaGrid").jqxGrid('getcellvalue', val, 'clave'));
			placas.push($("#tablaGrid").jqxGrid('getcellvalue', val, 'placa'));

		});

		// var index = e.args.rowindex, campo = e.args.datafield;
		// var row = $(this).jqxGrid('getrowdata', index);
		// if(campo != 'selected'){ return false; }

		// if(isFirst){
		// 	selects = [];
		// 	placas = [];
		// 	isFirst = false;
		// }
		// $(this).jqxGrid('setcellvalue', index, campo, !(row.selected));
		// if(row.selected){
		// 	selects.push(row.clave);
		// 	placas.push(row.placa);

		// }else{
		// 	var index = selects.indexOf(row.clave);
		// 	if (index > -1) {
		// 		selects.splice(index, 1);
		// 		placas.splice(index, 1);
		// 	}
		// 	contrato_id = '';

		// }
	})
	.on('rowunselect','#tablaGrid',function(event){
		if(event.args.row != undefined)
		var index = selects.indexOf(event.args.row.clave);
		if (index > -1) {
			selects.splice(index, 1);
		}
		var index = placas.indexOf(event.args.row.placa);
		if (index > -1) {
			placas.splice(index, 1);
		}
	})
	//comportamiento de eliminación del registro
	.on('cellselect','#tablaGrid',function(e){
		// declaración de variables
		var index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
		// validación de evento a lanzar
		if(campo != 'eliminar'){ return false; }
		// declaración de variables secundarias para evitar carga innecesaria
		var row = $(this).jqxGrid('getrowdata', index), $spanContent = $('<span>'),
			content = '¿Realmente desea cancelar el elemento <strong>'+row.identificador+'</strong>?',
			btnElimina = $('<button>',{ class : 'btn btn-primary btn-sm bgc8', html : 'Aceptar',
				click : function(){
					$.post(modelo, {method:'destroy', identificador:row.identificador})
					.then(function(res){
						var titulo = 'Información de Datos';
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
	

	   $('#printStatus').click(function(){
		var atributo = $('#txtPlaca').val();
		var fechaIni = $('#txtFechaInicialFilter').val();
		var fechaFin = $('#txtFechaFinal').val();
		if(atributo != '')
			window.open(getUrl()+'/PDFEstado_contrato.php?placa='+atributo+'&fechaInicio='+fechaIni+'&fechaFin='+fechaFin, '_blank');
		else{
			muestraModalGeneral(3, 'No se puede mostrar', '<p>No se ha ingresado ninguna placa</p>');

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
	this.modelo = this.url+'/modelo/paseCobroTodosModelo.php';
	this.modeloComponentes = this.url+'/modelo/componentes_modelo.php';
	this.idForma = 'forma';
	this.btnCancel = $('<button>',{ class:'btn btn-primary btn-sm bgc8',html:'Cancelar','data-dismiss':'modal' });
	this.baseOption = '<option value="0">Seleccione una opción</option>';
	// funciones principales del sistema
	llenaTabla();
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
		, tblObj, tblTitulo, tblExcel=[0,1,2,3,4,5,6,], tblVisual=[0,1,2,3,4,5,6,7,8];
	tblObj = [
		// { name: 'selected', type: 'bool'},// 1
		{ name: 'clave', type: 'string'},// 1
		{ name: 'paseCobro', type: 'string'},// 0
		{ name: 'unidadEjecutora', type: 'string'},// 1
		{ name: 'fechaInicio', type: 'string'},// 3
		{ name: 'hora', type: 'string'},// 4
		{ name: 'placa', type: 'string'},// 5
		{ name: 'folio', type: 'string'},// 5
		{ name: 'importe', type: 'string'},// 7
		{ name: 'recargos', type: 'string'},// 7
		{ name: 'estatus', type: 'string'},// 7
		{ name: 'identificador', type: 'string'}// 9
	];
	tblTitulo = [
		// { text: 'PC', datafield: 'selected', editable: false, width: '5%', cellsalign: 'center', align: 'center', columntype: 'checkbox' },// 1
		{ text: 'Contrato', datafield: 'clave', editable: false, width: '12%', cellsalign: 'center', align: 'center' },// 1
		{ text: 'Pase Cobro', datafield: 'paseCobro', editable: false, width: '12%', cellsalign: 'center', align: 'center' },// 0
		// { text: 'UE', datafield: 'unidadEjecutora', editable: false, width: '15%', cellsalign: 'center', align: 'center' },// 0
		{ text: 'Fecha', datafield: 'fechaInicio', editable: false, width: '12%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Placa', datafield: 'placa', editable: false, width: '14%', cellsalign: 'center', align: 'center' },// 5
		{ text: 'Folio', datafield: 'folio', editable: false, width: '14%', cellsalign: 'center', align: 'center' },// 5
		{ text: 'Importe', datafield: 'importe', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Recargos', datafield: 'recargos', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Estatus', datafield: 'estatus', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 3
	];
	// llamado de limpieza de la tabla
	fnLimpiarTabla(el,tabla);
	// render de la tabla
	fnAgregarGrid_Detalle_nostring(data, tblObj, tblTitulo, tabla, ' ', 1, tblExcel, false, true, "", tblVisual, nameExcel);
	$("#tablaGrid").jqxGrid({
		selectionmode: 'checkbox',
		altrows: false,
	});
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

$('#btnBuscar').click(function(){

	fnMostrarDatos();
});

function fnMostrarDatos(){
	muestraCargandoGeneral();
	var msg = 'Por favor ingrese una placa';
	if($('#txtPlaca').val() == ''){
		muestraModalGeneral(3,'Información de datos',msg);
		ocultaCargandoGeneral();
		return;
	}
	$.post(this.modelo, {method:'show', info: getParams('frmFiltroActivos')}).then(function(res){
		llenaTabla(res.content);
		ocultaCargandoGeneral();

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

function myFunction() {
    var input, filter, ul, li, a, i, txtValue;
    input = document.getElementById("myInput");
    filter = input.value.toUpperCase();
    ul = document.getElementById("sortable1");
    li = ul.getElementsByTagName("li");
    for (i = 0; i < li.length; i++) {
        a = li[i].getElementsByTagName("a")[0];
        txtValue = a.textContent || a.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
			li[i].style.display = "";
			li[i].classList.add('searching');
			
        } else {
			li[i].classList.remove('searching');
            li[i].style.display = "none";
        }
    }
}


function fnBusquedaContribuyente() {

    dataObj = { 
            option: 'mostrarContribuyentes'
          };
    $.ajax({
      async:false,
      cache:false,
      method: "POST",
      dataType:"json",
      url: "modelo/componentes_modelo.php",
      data: dataObj
    })
    .done(function( data ) {

        //console.log(data);
        if(data.result) {
			fnBusquedaFiltroFormato(data.contenido.datos);
        }else{
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se Obtuvieron los Contribuyentes</p>');
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}


function fnBusquedaFiltroFormato(jsonData) {
    $( "#contribuyente").autocomplete({
		minLength: 4,
		source: jsonData,
		disabled: true,
        select: function( event, ui ) {
            
            $( this ).val( ui.item.value + "");
			$( "#contribuyente" ).val( ui.item.value );
            $( "#contribuyenteID" ).val( ui.item.texto );
			

            return false;
        }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {

        return $( "<li>" )
        .append( "<a>"+ item.value + "</a>" )
        .appendTo( ul );

	};
	
}
