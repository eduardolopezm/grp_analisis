/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */

$( document ).ready(function() {
	//Mostrar Catalogo
	fnMostrarDatos('');
}); 

var proceso = "";

//console.log("data: "+JSON.stringify(data)); 

/**
 * Muestra la información del catalogo completo o de forma individual
 * @param  {String} desc_ramo Código del Registro para obtener la información
 */
function fnMostrarDatos(desc_ramo){
	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        desc_ramo: desc_ramo
	      };
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABCConfigModelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datosCatalogo;

	    	var columnasNombres= "", columnasNombresGrid= "";
	    	// Columnas para el GRID
		    columnasNombres += "[";
		    columnasNombres += "{ name: 'Clave', type: 'string' },";
            columnasNombres += "{ name: 'Descripcion', type: 'string' },";
            columnasNombres += "{ name: 'Modificar', type: 'string' }";
		    columnasNombres += "]";

		    // Columnas para el GRID
		    columnasNombresGrid += "[";
            columnasNombresGrid += " { text: 'Valor', datafield: 'Clave', width: '13%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Descripción', datafield: 'Descripcion', width: '80%', cellsalign: 'left', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false }";
            columnasNombresGrid += "]";

	    	fnLimpiarTabla('divTabla', 'divContenidoTabla');

			var nombreExcel = data.contenido.nombreExcel;
			var columnasExcel= [0, 1];
			var columnasVisuales= [0, 1, 2];
			fnAgregarGrid_Detalle(info, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}

/**
 * Muestra formulario para modificar la información del registro seleccionado
 * @param  {String} id Código del Registro para obtener la información
 */
function fnModificar(id, desc, valor){
	// Mostrar formualrio
	var mensaje = '<div class="row clearfix" id="divFormularioModal" name="divFormularioModal">\
                    <div class="col-md-12 col-xs-12">\
                        <input type="hidden" id="clave" name="clave" value="'+id+'" />\
                        <input type="hidden" id="txtDesc" name="txtDesc" value="'+desc+'" />\
                        <component-text-label label="'+desc+'" id="txtValor" name="txtValor" placeholder="'+desc+'" title="'+desc+'" value="'+valor+'"></component-text-label>\
                    </div>\
	                <div align="center">\
	                	<component-button type="button" onclick="fnGuardarInformacion()" id="btnGuardar" name="btnGuardar" class="glyphicon glyphicon-floppy-disk" value="Guardar"></component-button>\
	                </div>\
                </div>\
                ';
    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
    muestraModalGeneral(3, titulo, mensaje);

    fnEjecutarVueGeneral('divFormularioModal');
}

/** Agregar nuevo registro validando que no existan campos vacios */
function fnGuardarInformacion(){
	muestraCargandoGeneral();

	if ($('#txtValor').val().trim() == '' || $('#txtValor').val().trim() == null) {
		// Si esta vacío la justificación
		ocultaCargandoGeneral();
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Agregar Valor para continuar con el proceso</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	dataObj = { 
	        option: 'AgregarCatalogo',
	        claveId: $('#clave').val(),
	        txtDesc: $('#txtDesc').val(),
	        valor: $('#txtValor').val()
	      };
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABCConfigModelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
	    if(data.result){
	    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		    muestraModalGeneral(3, titulo, data.Mensaje);

	    	fnLimpiarTabla('divTabla', 'divCatalogo');
	    	fnMostrarDatos('');
	    }else{
	    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		    muestraModalGeneral(3, titulo, data.Mensaje);
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}
