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

	// fnBusquedaContribuyente();
	ConfiguracionDefault();

	$('#btnBuscarContribuyente').click(function(){
		lookUpData($('#contribuyente'));
	});

	$('#permissParka').click(function(){
		content = '<div style ="display:block; margin:auto; width: 220px;"><input type ="password" class="form-control" id="valPassword"><button style="display: block; margin: auto; margin-top: 6%;" class="btn btn-default botonVerde" type="button" onclick ="enviarValidacion();">Enviar</button></div>';
		$('#modalUsoGeneral').modal('hide');
		muestraModalGeneral(3,'Ingrese la contraseña',content);

	});

	

	$('#contribuyente').keypress(function (e)
    {
       if (e.which == 13)
       {
          lookUpData($(this));
       }
       else
       {
           $("#contribuyente").autocomplete("disable");
       }
   	});

	function lookUpData(autocompleteField)
	{
		if(isFirstSearch){
			fnBusquedaContribuyente();
			isFirstSearch = false;
		}
		autocompleteField.autocomplete("enable");
		autocompleteField.autocomplete("search");
	}

	$('#myInput').on('keypress', function (e) {
		var search = $('.searching')[0];
		if(e.which === 13){
			$('#sortable2').append(search);
			obj['parcials'].push({
				"id": $(search).attr('id'),
				'price': $(search).attr('data-price')
			});
			updatePrices();
			$(this).val('');
			myFunction();
		}
  	});

	$( "#sortable1").sortable({
		connectWith: ".connectedSortable"
	}).disableSelection();
	$( "#sortable2" ).sortable({
		connectWith: ".connectedSortable",
		receive: function( event, ui ) {
			obj['parcials'].push({
				"id": ui.item[0].id,
				'price': ui.item[0].dataset.price
			});
			totalParciales += parseFloat(ui.item[0].dataset.price);
			updatePrices();
		},
		remove: function( event, ui ) {
			totalParciales -= parseFloat(ui.item[0].dataset.price);
			if(totalParciales.toFixed(2) < 0)
				totalParciales = 0;
			updatePrices();
			deleteParcial(ui.item[0].id);

		},
		update: function( event, ui ) {
			updatePrices();
		}

	}).disableSelection();

	function updatePrices(){
		var total = 0;
		$('#sortable3').empty();
		$.each($('#sortable2').find('li'), function(index, value){
			$('#sortable3').append('<li class="ui-sortable-handle">'+numeral($(this).attr('data-price')).format('$0,0.00')+' </li>');
			total += parseFloat($(this).attr('data-price'));
		});
		$('#total').text(numeral(total.toFixed(2)).format('$0,0.00'));

	}

	function deleteParcial(id){
		$.each(obj.parcials, function(index, value){
			if(value.id == id){
				obj.parcials.splice(index, 1);
				return;
			}
		});
		parcials = JSON.stringify(obj);
		
	}
	inicioPanel();

	$('#btnPase').on('click',GenerarPase);
	$(document).on("click", "#btnPaseYes",GenerarPase);
	

	function GenerarPase(){
		if (selects.length == 0) {
			var titulo = 'Información';
			muestraModalGeneral(3, titulo, 'Selecionar Información');
			return false;
		}
		$("#btnPase").attr("disabled", true);
		muestraCargandoGeneral();
		$.post(modelo, { method:'posPase', selects_id: selects, placa: placas })
		.then(function(res){
			// declaración de variables
			var titulo = 'Información', $spanContent = $('<span>');
			// comprobación de éxito
			if(res.success){
				titulo = 'Datos Guardados';
				// cargaInicial();
			
			}
			$('#modalUsoGeneral').modal('hide');
			
			muestraModalGeneral(3,titulo,res.msg);
			// $('#modalUsoGeneral').modal('hide');
			$.each($('#tablaGrid').jqxGrid('getrows'), function( index, value ) {
				$('#tablaGrid').jqxGrid('setcellvalue', index, 'selected', false);
			});
			$("#btnPase").attr("disabled", false);
			selects = [];
			placas = [];

		});
		
		selects = [];

		ocultaCargandoGeneral();
	}

	$('#nuevo').on('click',function(){
		fbCamposABloquear(false);
		AlinearSelectsDerecha();
		$('#tituloModal').html('<i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Nueva Multa de Tránsito');
		$('#idconfContrato').val(""+confContrato);
		GetObjetosParciales();
		obj['parcials'] = [];
		parcials = JSON.stringify(obj);
		obj2['atributos'] = [];
		atributos = JSON.stringify(obj2);
		$('#txtFechaInicial').val(fechaInicial);
		$('#total').text(numeral(0).format('$0,0.00'));
		$( "#contribuyente" ).val(contribuyenteDefault);
		$( "#contribuyenteID" ).val(contribuyenteIDDefault);
		$( "#selectUnidadEjecutora" ).val(selectUnidadEjecutora);
		$( "#selectUnidadEjecutora" ).multiselect('rebuild');
		
		$('#modalUsoGeneral').modal('show');
	});
	// comportamiento del botón de guardado
	$('#guardar').on('click',function(){
		$("#guardar").attr("disabled", true);

		muestraCargandoGeneral();

		parcials = JSON.stringify(obj);
		obj2['atributos'] = [];
		atributos = JSON.stringify(obj2);
		$('.atributo').each(function( index ) {
			obj2['atributos'].push({
				"id": $( this ).attr('data-atributo'),
				'value': $( this ).find('input').val()
			});

		  });
		  atributos = JSON.stringify(obj2);

			var params = getParams(idForma), campos = getMatchets(idForma), msg = '',
				nombreCampo = {
					'selectUnidadEjecutora':'Unidad Ejecutora',
					'contribuyenteID': 'Contribuyente',
					'folio':'Folio',
					'placa':'Placa',
					'receptor':'Receptor',
					'garantia':'Garantía',
					'infractor':'Infractor',
					'hora':'Hora'
				},
				method = params.hasOwnProperty('identificador')?'update':'store';
			$.extend(params,{ method:method,'valid':1, parcials: parcials, atributos: atributos});
			$.each(campos,function(index, el) {
				if(nombreCampo[el] != undefined){
					if($("#"+el).val()==""){
						msg += 'El campo '+nombreCampo[el]+' no puede ir vacío<br />';
					}
				}
				
			});
			if(obj['parcials'].length == 0){ 
				msg = 'Seleccione al menos un objeto parcial<br />';
			
			}
			if(msg!=''){ muestraModalGeneral(3,'Error de datos',msg); 
				ocultaCargandoGeneral();
				$("#guardar").attr("disabled", false);
				
				return;
			}
			$.post(modelo, params).then(function(res){
				var titulo=res.success?'Operación Exitosa':'Información';
				if(res.success){
					isFirst = true;
					selects.push(res.contratoID);
					selectUnidadEjecutora = $('#selectUnidadEjecutora').val();
					fnLimpiarCamposFormConHidden(idForma);
					GetObjetosParciales();
					llenaTabla('');

				}
				// llenaTabla(res.content);
				muestraModalGeneral(3,titulo,res.msg);
			});
			$("#guardar").attr("disabled", false);

			ocultaCargandoGeneral();
			$('#modalUsoGeneral').modal('hide');
			$('#productDescription').text('Sin Producto/Servicio seleccionado');
			$('#productDescription').css('color', 'black');

		selects = [];
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
			var titulo = 'Información', $spanContent = $('<span>');
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
				GetObjetosParciales(true);
				updatePrices();
				AlinearSelectsDerecha();
				$('#modalUsoGeneral #tituloModal').html('<i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Edición de Multas de Tránsito');
				$('#modalUsoGeneral').modal('show');

			}
		});
	})
	.on('cellselect','#tablaGrid',function(e){
		var index = e.args.rowindex, campo = e.args.datafield;
		var row = $(this).jqxGrid('getrowdata', index);
		if(campo != 'selected'){ return false; }

		if(isFirst){
			selects = [];
			placas = [];
			isFirst = false;
		}
		$(this).jqxGrid('setcellvalue', index, campo, !(row.selected));
		if(row.selected){
			selects.push(row.clave);
			placas.push(row.placa);

		}else{
			var index = selects.indexOf(row.clave);
			if (index > -1) {
				selects.splice(index, 1);
				placas.splice(index, 1);
			}
			contrato_id = '';

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
						var titulo = 'Información';
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
	

	   GetObjetosParciales();
	   $('#printStatus').click(function(){
			var atributo = '';
			var typeAttr = 'PLACA';
			atributo = $('#txtPlaca').val();

		   	if(atributo == ''){
				atributo = $('#folioFiltro').val();
				typeAttr = 'FOLIO DE BOLETA';
			}

			var fechaIni = $('#txtFechaInicialFilter').val();
			var fechaFin = $('#txtFechaFinal').val();
		
			if(atributo != '')
				window.open(getUrl()+'/PDFEstado_contrato.php?placa='+atributo+'&fechaInicio='+fechaIni+'&fechaFin='+fechaFin+'&confContrato=4&typeAtributo='+typeAttr, '_blank');
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
	this.modelo = this.url+'/modelo/multasTransitoModelo.php';
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
		{ name: 'selected', type: 'bool'},// 1
		{ name: 'clave', type: 'string'},// 1
		{ name: 'paseCobro', type: 'string'},// 0
		{ name: 'unidadEjecutora', type: 'string'},// 1
		{ name: 'fechaInicio', type: 'string'},// 3
		{ name: 'hora', type: 'string'},// 4
		{ name: 'placa', type: 'string'},// 5
		{ name: 'folio', type: 'string'},// 5
		{ name: 'garantia', type: 'string'},// 5
		{ name: 'receptor', type: 'string'},// 5
		{ name: 'infractor', type: 'string'},// 5
		{ name: 'importe', type: 'string'},// 7
		{ name: 'recargos', type: 'string'},// 7
		{ name: 'modificar', type: 'string'},// 7
		{ name: 'estatus', type: 'string'},// 7
		{ name: 'eliminar', type: 'string'},// 8
		{ name: 'identificador', type: 'string'}// 9
	];
	tblTitulo = [
		{ text: 'PC', datafield: 'selected', editable: false, width: '5%', cellsalign: 'center', align: 'center', columntype: 'checkbox' },// 1
		{ text: 'Contrato', datafield: 'clave', editable: false, width: '5%', cellsalign: 'center', align: 'center' },// 1
		{ text: 'Pase Cobro', datafield: 'paseCobro', editable: false, width: '4%', cellsalign: 'center', align: 'center' },// 0
		// { text: 'UE', datafield: 'unidadEjecutora', editable: false, width: '15%', cellsalign: 'center', align: 'center' },// 0
		{ text: 'Fecha', datafield: 'fechaInicio', editable: false, width: '8%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Hora', datafield: 'hora', editable: false, width: '8%', cellsalign: 'center', align: 'center' },// 4
		{ text: 'Placa', datafield: 'placa', editable: false, width: '8%', cellsalign: 'center', align: 'center' },// 5
		{ text: 'Folio', datafield: 'folio', editable: false, width: '8%', cellsalign: 'center', align: 'center' },// 5
		{ text: 'Garantia', datafield: 'garantia', editable: false, width: '8%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Receptor', datafield: 'receptor', editable: false, width: '8%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Infractor', datafield: 'infractor', editable: false, width: '8%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Importe', datafield: 'importe', editable: false, width: '8%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Recargos', datafield: 'recargos', editable: false, width: '8%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Estatus', datafield: 'estatus', editable: false, width: '8%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Modificar', datafield: 'modificar', editable: false, width: '8%', cellsalign: 'center', align: 'center' },// 7
		{ text: 'Cancelar', datafield: 'eliminar', editable: false, width: '6%', cellsalign: 'center', align: 'center' }// 8
	];
	// llamado de limpieza de la tabla
	fnLimpiarTabla(el,tabla);
	// render de la tabla
	fnAgregarGrid_Detalle_nostring(data, tblObj, tblTitulo, tabla, ' ', 1, tblExcel, false, true, "", tblVisual, nameExcel);
	// $("#tablaGrid").jqxGrid({
	// 	selectionmode: 'checkbox',
	// 	altrows: true,
	// });
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
	selects = [];
	
	muestraCargandoGeneral();
	$.post(this.modelo, {method:'show', info: getParams('frmFiltroActivos')}).then(function(res){
		llenaTabla(res.content);
	});
	ocultaCargandoGeneral();
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

function GetObjetosParciales(isEdit = false){
	$( "#sortable1").empty();
	$( "#sortable2").empty();
	$('#sortable3').empty();
	var isParcialsSelect = false;
	
	dataObj = {
		option: 'mostrarObjetosParcialesContratos2',
		id_configContratos: confContrato
	};
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType: "json",
		url: "modelo/componentes_modelo.php",
		data: dataObj
	}).done(function(data) {
		if (data.result) {
			dataJson = data.contenido.datos;
			var contenido = "", contenido2 ="";
			for (var info in dataJson) {
				if(isEdit){
					$.each(obj['parcials'], function(index, val) {
						if (val.id == dataJson[info].clave){
							isParcialsSelect = true;
							return;
						}
					});
					if(isParcialsSelect)
						contenido2 += '<li class="ui-sortable-handle" id="'+dataJson[info].clave+'" data-price="'+dataJson[info].price+'"><a href="javascript:;">'+dataJson[info].nombre+'</a></li>';
					else
						contenido += '<li class="ui-sortable-handle" id="'+dataJson[info].clave+'" data-price="'+dataJson[info].price+'"><a href="javascript:;">'+dataJson[info].nombre+'</a></li>';
					isParcialsSelect = false;
				}	
				else
					contenido += '<li class="ui-sortable-handle" id="'+dataJson[info].clave+'" data-price="'+dataJson[info].price+'"><a href="javascript:;">'+dataJson[info].nombre+'</a></li>';
			}
			$('#sortable1').append(contenido);
			if(contenido2 != "")
				$('#sortable2').append(contenido2);

		} else {
			console.log("ERROR Modelo");
			console.log( JSON.stringify(data) ); 
		}
	}).fail(function(result) {
		console.log("ERROR");
		console.log( result );
	});
 }


 function ConfiguracionDefault() {
    dataObj = { 
			option: 'mostrarConfiguracionDefault',
			confContrato_id: confContrato
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
        console.log('default:',data.contenido.datos);
        if(data.result) {
			if(data.contenido.datos.length > 0){
				$( "#contribuyente" ).val( data.contenido.datos[0].value+' - '+data.contenido.datos[0].texto);
				$( "#contribuyenteID" ).val( data.contenido.datos[0].value);
				contribuyenteDefault = data.contenido.datos[0].value+' - '+data.contenido.datos[0].texto;
				contribuyenteIDDefault = data.contenido.datos[0].value;

			}else{
				$( "#contribuyente" ).val('');
				$( "#contribuyenteID" ).val('');
			}
			
        }
    })
    .fail(function(result) {
        console.log( result );
    });
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

function enviarValidacion(){
	muestraCargandoGeneral();
	var password = $('#valPassword').val();
	$.post(modelo, { method:'parkaValidacion', selects_id: selects, password: password })
	.then(function(res){
		// declaración de variables
		var titulo = 'Información', $spanContent = $('<span>');
		// comprobación de éxito
		if(res.success){
			titulo = 'Datos Guardados';
			$('#modalUsoGeneral').modal('hide');
		}
		
		muestraModalGeneral(3,titulo,res.msg);

	});
	


	ocultaCargandoGeneral();
}