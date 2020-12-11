var urlModelo="modelo/mantenimientoActivoFijoModelo.php";
var tituloGeneral = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';

$(document).ready(function (){

	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	//!!                 CARGA INICIAL.                !!
	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	$('#selectUsuarioAsignado, #selectEstatusMantenimiento, #selectPrioridad, #selectUsuarioResponsable').multiselect({
         enableFiltering: true,
         filterBehavior: 'text',
         enableCaseInsensitiveFiltering: true,
         buttonWidth: '100%',
         numberDisplayed: 1,
         includeSelectAllOption: true
    });
	
	// Estilos para el diseño del select  
	$('.multiselect-container').css({ 'max-height': "200px" });
	$('.multiselect-container').css({ 'overflow-y': "scroll" });

	fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora');
	fnObtenerMantenimientos();

	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	//!!                 EVENTOS CHANGE.               !!
	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

	$('#selectUnidadNegocio').change(function(){
		fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora');
	});

	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	//!!                 EVENTOS CLICK.                !!
	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

	$('#btnBuscar').click(function(){
		fnObtenerMantenimientos();
	});

	$('#btnNuevo').click(function(){
       	window.location.href="mantenimientoActivoFijoDetalle.php";
    });

});

$(document).on('click','#btnCancelar',changeNewStatus);


function getSelects(tbl,filedata='idCHK') {
    var $tbl = $('#'+tbl), rows = [], len = i=0, infTbl;
    infTbl =  $tbl.jqxGrid('getdatainformation');
    len  = infTbl.rowscount;
    for (;i<len;i++) {
        var data = $tbl.jqxGrid('getrowdata',i);
        if(data[filedata]){ rows.push(data); }
    }
    return rows;
}

function getNombreSeleccion(datos, filedata='id') {
    var nombres = '';
    $.each(datos, function(index, val) {
        nombres += (index!=0?', ':'') + val[filedata];
    });
    return nombres;
}

function changeNewStatus() {
    var el = $(this), selections = getSelects('datosMantenimiento','idCHK'), $flag = 0;
    var idFolio = getNombreSeleccion(selections,'folio');
    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>'; 
    var varPorValidar = "";

    console.log(selections);
    console.log(idFolio);

    if(selections.length!=0){
        if(el.attr('id') == 'btnCancelar'){
            var noChange=[];
            
            noChange = [2,3];
            
            $.each(selections, function(ind, row) {
                if(noChange.indexOf(parseInt(row.idstatus))!==-1){
                    $flag++;

                    muestraModalGeneral(3,titulo,'El mantenimiento con estatus Finalizado y/o Cancelado no se pueden cancelar.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                }
            });

            if($flag == 0){
                var mensaje="";
                
                if(selections.length > 1){
                     mensaje = '¿Está seguro de cancelar los mantenimientos seleccionados?';
                }else{
                     mensaje = '¿Está seguro de cancelar el mantenimiento seleccionado?';
                }
                
                muestraModalGeneralConfirmacion(3,titulo,mensaje,'','fnModificarEstatusMantenimiento(\''+idFolio+'\')');
            }
        }
    }else{
        muestraModalGeneral(3,tituloGeneral,'No ha seleccionado ningún elemento.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
    }
}


function fnModificarEstatusMantenimiento(folios){
	dataObj = {
        option : 'cambiarEstatusMantenimiento',
        folio : folios,
        estatus : '3',
        leyenda : 'cancelado',
        leyenda2 : 'cancelaron',
        requisicion: ''
    };

    $.ajax({
        async: false,
        method: "POST",
        dataType: "json",
        url: "modelo/mantenimientoActivoFijoDetalleModelo.php",
        data: dataObj
    })
    .done(function(data) {
        if (data.result) {
            muestraModalGeneral(3,tituloGeneral,''+data.Mensaje);
            fnObtenerMantenimientos();
        }
    })
    .fail(function(result) {
        muestraMensaje('<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Error al cancelar<p>', 3, 'mensajesValidaciones', 5000);
        console.log("ERROR");
        console.log(result);
    }); 
}

function fnObtenerMantenimientos(){
	
	var fd = new FormData();

	fd.append("option", "obtenerMantenimientos");
	fd.append("txtUR", fnObtenerOption('selectUnidadNegocio'));
	fd.append("txtUE", fnObtenerOption('selectUnidadEjecutora'));
	fd.append("txtFolio", $('#txtFolio').val());
	fd.append("dpDesde", $("#dpDesde").val());
	fd.append("dpHasta", $("#dpHasta").val());
	fd.append("txtStatus", fnObtenerOption('selectEstatusMantenimiento'));
	fd.append("selectTipoMantenimiento", fnObtenerOption('selectTipoMantenimiento'));
	fd.append("selectTipoBien", fnObtenerOption('selectTipoBien'));


    $.ajax({
	    async:false,
	    url:urlModelo,
	    type:'POST',
	    data: fd, 
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: 'json',
	    success: function (data) {
	        if (data.result) {

	        	var datosMantenimientos = data.contenido.datos;
	            var columnasNombres = data.contenido.columnasNombres;
	            var columnasNombresGrid = data.contenido.columnasNombresGrid;

	            var nombreExcel = data.contenido.nombreExcel;

	            fnLimpiarTabla('tablaMantenimiento', 'datosMantenimiento');
	            asignadosrows = [];
	            eliminadosrows = [];
	            var columnasExcel= [1,2,3,4,6,7,8,10,11];
            	var columnasVisuales= [0,1,2,3,5,6,7,8,10,11];
	            
	            fnAgregarGrid_Detalle(datosMantenimientos, columnasNombres, columnasNombresGrid, 'datosMantenimiento', ' ', 1, columnasExcel, false, true, "", columnasVisuales, nombreExcel);

	            ocultaCargandoGeneral();
	        } else {
	            ocultaCargandoGeneral();
	            // console.log("ERROR Modelo");
	            // console.log( JSON.stringify(data) );
	        }
	    }
	}); 
}

function fnObtenerOption(componenteSelect){
	var valores = "";
    var select = document.getElementById(''+componenteSelect);

    for ( var i = 0; i < select.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (select.selectedOptions[i].value != '-1') {
            // Que no se opcion por default
            if (i == 0) {
                valores = "'"+select.selectedOptions[i].value+"'";
            }else{
                valores = valores+", '"+select.selectedOptions[i].value+"'";
            }
        }
    }
    return valores;
}