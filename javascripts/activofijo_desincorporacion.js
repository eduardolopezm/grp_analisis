var cargartagreff="";
primeraVezSet2 = true;
var asignadosrows = [];


function fnDesincorporar () {

}



function fnImprimir(aFolio) {
  aDato = "PrintSituacionFinanciera.php?Folio="+aFolio+"&reporte=desincorporacion";
   $("#viewReporte").html('<object data="'+ aDato+'" width="100%" height="800px" type="application/pdf">         <embed src="'+ aDato+'" type="application/pdf" />     </object>');  
}

function fnCargarPrimeraVezSet2(aFiltro) {
  muestraCargandoGeneral();
  $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/activofijo_modelo.php",
            data: { cargarinicio_desincorporacion2tab: true },
            async: false
        })
        .done(function(data) {
            if(data.result){
            //Si trae informacion
            //info=data.contenido.datosCatalogo;
            
            //fnAgregarGridv2(info, 'divCatalogo', "busqueda");
            dataReasignacionJason = data.contenido.infolistadeactivos;
            columnasNombres = data.contenido.columnasNombres;
            columnasNombresGrid = data.contenido.columnasNombresGrid;
            var nombreExcel = data.contenido.nombreExcel;
            var columnasExcel= [1, 2, 3, 4];
            var columnasVisuales= [0, 1, 2, 3, 4, 5, 6];
            fnLimpiarTabla('divTabla2', 'divDesincorporacion');

            
            fnAgregarGrid_Detalle(dataReasignacionJason, columnasNombres, columnasNombresGrid, 'divDesincorporacion', ' ', 1, columnasExcel, false, false, "", columnasVisuales, nombreExcel);
            if (aFiltro!="") {

              var checkExist = setInterval(function() {
   if ($('#searchFielddivResguardos').length) {
      $("#searchFielddivResguardos").val("Resguardo-"+aFiltro);
      $("#searchFielddivResguardos").keydown();
      
      clearInterval(checkExist);
   }
}, 100);
              

             

            }
            
            
        }
        });
  ocultaCargandoGeneral();
}

function Edit(row, event) {
    that.editrow = row;
    $("#grid").jqxGrid('beginrowedit', row);
    if (event) {
        if (event.preventDefault) {
            event.preventDefault();
        }
    }
    return false;
}

function Update(row, event) {
    that.editrow = -1;
    $("#grid").jqxGrid('endrowedit', row);
    if (event) {
        if (event.preventDefault) {
            event.preventDefault();
        }
    }
    return false;
}

function Cancel(row, event) {
    that.editrow = -1;
    $("#grid").jqxGrid('endrowedit', row, true);
    if (event) {
        if (event.preventDefault) {
            event.preventDefault();
        }
    }
    return false;
}




function fnBuscar() {

	 $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/activofijo_modelo.php",
            data: { cargarinicio_desincorporacion: true, fechainicial: "", fechafinal: "" },
            async: false
        })
        .done(function(data) {
            if (data.result) {
                //Si trae informacion
                //
                var dataReasignacionJason = data.contenido.infolistadeactivos;
                var tipoActivos = data.contenido.tipoActivos;
                var categoriaactivos = data.contenido.infocatalogoactivos;
                var activoslocations = data.contenido.activoslocations;
                var selectOrigen = data.contenido.selectOrigen;
                var selectTipoDepreciacion = data.contenido.selectTipoDepreciacion;
                var selectUnidaddenegocio = data.contenido.selectUnidaddenegocio;
                var procesoscontabilizar = data.contenido.infoprocesoscontabilizar

                //fnDatosSelet();
                //
                var columnasNombres = data.contenido.columnasNombres;
	            var columnasNombresGrid = data.contenido.columnasNombresGrid;
	            var nombreExcel = data.contenido.nombreExcel;
	            var columnasExcel= [1, 2, 3, 4];
	            var columnasVisuales= [0, 1, 2, 3, 4, 5, 6];
	            fnLimpiarTabla('adentro', 'viewListaDeActivos');
	            fnAgregarGrid_Detalle(dataReasignacionJason, columnasNombres, columnasNombresGrid, 'viewListaDeActivos', ' ', 1, columnasExcel, false, true, "", columnasVisuales, nombreExcel);
            
                

            

                //construye todos las opciones del catalogo de cuentas


                //fnFiltrarPorCuenta(tipoActivos, null, '#selectTipoActivo');
                //fnFiltrarPorCuenta(categoriaactivos, null, '#selectCategoriaActivo');
                //fnFiltrarPorCuenta(categoriaactivos, null, '#selectClaveCabms');
                //fnFiltrarPorCuenta(activoslocations, null, '#selectLocationsActivo');
                //fnFiltrarPorCuenta(selectOrigen, null, '#selectOrigen');
                //fnFiltrarPorCuenta(selectUnidaddenegocio, null, '#selectUnidaddenegocio');
                //fnFiltrarPorCuenta(selectTipoDepreciacion, null, '#selectTipoDepreciacion');

                //fnCrearDatosSelect(procesoscontabilizar, "#selectProcesoContabilizarActivo")


                /*if (ActivoFijoID != 0) {

                	fnCargarActivoFijo(ActivoFijoID);

                }*/


            }
        });


}



function fnCambioRazonSocial() {
    muestraCargandoGeneral();
    //console.log("fnObtenerUnidadNegocio");
    // Inicio Unidad de Negocio

    legalid = $("#selectRazonSocial").val();
    //Opcion para operacion
    dataObj = { 
          option: 'mostrarUnidadNegocio',
          legalid: legalid
        };
    //Obtener datos de las bahias
    $.ajax({
        method: "POST",
        dataType:"json",
        url: "modelo/imprimirreportesconac_modelo.php",
        data:dataObj
    })
    .done(function( data ) {
      //console.log("Bien");
      if(data.result){
          //Si trae informacion
          
          dataJson = data.contenido.datos;
          //console.log( "dataJson: " + JSON.stringify(dataJson) );
          //alert(JSON.stringify(dataJson));
          var contenido = "<option value='0'>Seleccionar...</option>";
          for (var info in dataJson) {
            contenido += "<option value='"+dataJson[info].tagref+"'>"+dataJson[info].tagdescription+"</option>";
          }
        $('#selectUnidadNegocio').empty();
        $('#selectUnidadNegocio').append(contenido);
        $('#selectUnidadNegocio').multiselect('rebuild');
        ocultaCargandoGeneral();

        if (cargartagreff != "") 
            $("#selectUnidadNegocio").selectpicker('val',cargartagreff);
            $("#selectUnidadNegocio").multiselect('refresh');
            $(".selectUnidadNegocio").css("display", "none");

      }else{
          // console.log("ERROR Modelo");
          // console.log( JSON.stringify(data) ); 
          ocultaCargandoGeneral();
      }
    })
    .fail(function(result) {
      // console.log("ERROR");
      // console.log( result );
      ocultaCargandoGeneral();
    });
    // Fin Unidad de Negocio
}


function fnCambioUnidadNegocio(){

}


function fnDesincorporarActivoFijo(aID, aIDRow, aEr=false){

  if ($('#selectEmpleado').val() == 0) {

    muestraMensaje('Seleccione primero un empleado.', 3, 'mensajesValidaciones', 5000);
    
    return;
  }

    var rowid = $('#divCatalogo').jqxGrid('getrowid', aIDRow);
    var data = $('#divCatalogo').jqxGrid('getrowdatabyid', rowid);
    //
    $("#divCatalogo").jqxGrid('beginrowedit', rowid);
    $("#divCatalogo").jqxGrid('setcellvalue', rowid, 'Modificar', '<a>Asignado</a>' );
    var data = $('#divCatalogo').jqxGrid('getrowdata', rowid);
    
    asignadosrows.push(data.assetid);

    $("#divCatalogo").jqxGrid('endrowedit', rowid);
}




$(document).ready(function() {
    //Mostrar Catalogo



    $("ul.nav-tabs a").click(function(e) {
        e.preventDefault();
        $(this).tab('show');
    });



     $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
      var target = $(e.target).attr("href") // activated tab

      if (target == '#set2') {
        if (primeraVezSet2) {
          primeraVezSet2 = false;
          fnCargarPrimeraVezSet2("");
        }
      }
    });


   fnBuscar();

    //fnMostrarDatos('');

    //fnCambioUnidadNegocio();

    //fnFiltrarPorCuenta(info, '2.2.2', '#selectSituacionFinancieraDocumentosporPagaraLargoPlazo');


});