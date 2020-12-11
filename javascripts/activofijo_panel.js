var procesoscontabilizar = "";

function fnAgregarCatalogoModal() {

  muestraModalGeneral(4, "Editar activo fijo", "<object type='text/html' data='activofijo.php?modal=true&new=true' width='100%' height='100%' ></object>");


  /*$.ajax({
      data:  {},
      url:   'activofijo.php',
      type:  'post',
      beforeSend: function () {
          // document.getElementById('tareas').innerHTML = "<br><br><br><br><br><div style='width:1000px;height:100px;margin:auto;text-align:center'> <img src='images/enProceso.gif' width=50 height=50 align='center'  /></div><br>";
      },
      success:  function (response) {
           //$("#MyProducto").html(response);
         muestraModalGeneral(4, "Editar activo fijo", response);
      }
  });*/
}

//onchange="fnSeleccionarProceso()"

function fnSeleccionarProceso() {

  $.ajax({
      method: "POST",
      dataType: "json",
      url: "modelo/activofijo_modelo.php",
      data: {
        cargarcargoyabono: true,
        procesocontabilizar: $('#selectProcesoContabilizarActivo').val()
      },
      async: false
    })
    .done(function(data) {
      if (data.result) {
        //Si trae informacion
        //info=data.contenido.datosCatalogo;

        //fnAgregarGridv2(info, 'divCatalogo', "busqueda");

        fnCrearDatosSelect(data.contenido.infocargo, "#selectCargoContabilizarActivo")
        fnCrearDatosSelect(data.contenido.infoabono, "#selectAbonoContabilizarActivo")



      }
    });


}

function fnContabilizarActivo() {
  if ($("#selectProcesoContabilizarActivo").val() == 0) alert('Seleccione el proceso');
  else
  if ($("#selectCargoContabilizarActivo").val() == 0) alert('Seleccione la cuenta de Cargo');
  else
  if ($("#selectAbonoContabilizarActivo").val() == 0) alert('Seleccione la cuenta de Abono');
  else {
    $.ajax({
        method: "POST",
        dataType: "json",
        url: "modelo/activofijo_modelo.php",
        data: {
          contabilizar: true,
          procesocontabilizar: $('#selectProcesoContabilizarActivo').val(),
          cuentaactivo: $('#selectCargoContabilizarActivo').val(),
          cuentapasivo: $('#selectAbonoContabilizarActivo').val()
        },
        async: false
      })
      .done(function(data) {
        if (data.result) {
          //Si trae informacion
          //info=data.contenido.datosCatalogo;

          //fnAgregarGridv2(info, 'divCatalogo', "busqueda");

          alert('se contabilizó');



        }
      });

  }

}

function fnGenerarContabilidad() {
  var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Contabilizar el activo fijo</p></h3>';
  muestraModalGeneral(3, titulo, `<div name="divcontabilizar" id="divcontabilizar">
     <div class="col-md-12">
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Proceso: </label></span>
              </div>
              <div class="col-md-9"> 
                  <select id="selectProcesoContabilizarActivo" name="selectProcesoContabilizarActivo" class="form-control selectProcesoContabilizarActivo" onclick="fnSeleccionarProceso()" required="">
                    <option value='0'>Seleccionar...</option>
                  </select>
              </div>
          </div>
    </div>
    <br>
     <div class="col-md-12">
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Cargo: </label></span>
              </div>
              <div class="col-md-9"> 
                  <select id="selectCargoContabilizarActivo" name="selectCargoContabilizarActivo" class="form-control selectCargoContabilizarActivo" >
                    <option value='0'>Seleccionar...</option>
                  </select>
              </div>
          </div>
    </div>
    <br>
     <div class="col-md-12">
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Abono: </label></span>
              </div>
              <div class="col-md-9"> 
                  <select id="selectAbonoContabilizarActivo" name="selectAbonoContabilizarActivo" class="form-control selectAbonoContabilizarActivo" >
                    <option value='0'>Seleccionar...</option>
                  </select>
              </div>
          </div>
    </div>
    <br>

    <div id="botoncito">

     <component-button type="button" id="btnContabilizarActivo" name="btnContabilizarActivo" value="Contabilizar Activo"></component-button>
     </div>

</div>`);


  //alert(procesoscontabilizar);

  fnCrearDatosSelect(procesoscontabilizar, "#selectProcesoContabilizarActivo")


  $("#selectProcesoContabilizarActivo").on("change", fnSeleccionarProceso);


  fnEjecutarVueGeneral('botoncito');
  $("#btnContabilizarActivo").on("click", fnContabilizarActivo);

}

function fnModificarActivoFijo(aID, aEr = false) {


  stringid = aID.toString();

  if (aEr == true) {
    //stringurl = "<object type='text/html' data='activofijo.php?modal=true&new=false&aEr=true&AssetId="+ stringid + "'' width='100%' height='100%' ></object>";
    //stringtitulo = "Eliminar activo fijo";
    window.location.href = "activofijo.php?new=false&aEr=true&AssetId=" + stringid;
  } else {
    //stringurl = "<object type='text/html' data='activofijo.php?modal=true&new=false&aEr=false&AssetId="+ stringid + "'' width='100%' height='100%' ></object>";
    //stringtitulo = "Editar activo fijo";
    window.location.href = "activofijo.php?new=false&aEr=false&AssetId=" + stringid;
  }



  //muestraModalGeneral(4, stringtitulo, stringurl);
  //$(".modal-footer")[0].style.display = 'none';



  /*$.ajax({
      data:  {},
      url:   'activofijo.php',
      type:  'post',
      beforeSend: function () {
          // document.getElementById('tareas').innerHTML = "<br><br><br><br><br><div style='width:1000px;height:100px;margin:auto;text-align:center'> <img src='images/enProceso.gif' width=50 height=50 align='center'  /></div><br>";
      },
      success:  function (response) {
           //$("#MyProducto").html(response);
         muestraModalGeneral(4, "Editar activo fijo", response);
      }
  });*/
}

$(document).ready(function() {
  //Mostrar Catalogo

  $("ul.nav-tabs a").click(function(e) {
    e.preventDefault();
    $(this).tab('show');
  });

  $(' #selectAlmacen').multiselect({
        enableFiltering: true,
        filterBehavior: 'text',
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        numberDisplayed: 1,
        includeSelectAllOption: true
    });

    $('.multiselect-container').css({
        'max-height': "200px"
    });
    $('.multiselect-container').css({
        'overflow-y': "scroll"
    });

  fnMostrarDatos();

  fnCambioUnidadResponsableGeneral('selectUnidadNegocio', 'selectUnidadEjecutora');
  //fnObtenerAlmacenes(fnObtenerOption('selectUnidadNegocio'), '', 'selectAlmacen');

  $('#selectUnidadNegocio').change(function() {
        fnCambioUnidadResponsableGeneral('selectUnidadNegocio', 'selectUnidadEjecutora');
        
  });

  $('#selectUnidadEjecutora').change(function() {
    fnObtenerAlmacenes(fnObtenerOption('selectUnidadNegocio'), fnObtenerOption('selectUnidadEjecutora'), 'selectAlmacen');
  });

  //fnMostrarDatos('');

  //fnCambioUnidadNegocio();

  //fnFiltrarPorCuenta(info, '2.2.2', '#selectSituacionFinancieraDocumentosporPagaraLargoPlazo');


});


function fnMostrarDatos() {

  var fd = new FormData(document.getElementById('frmFiltroActivos'));
  fd.append('option', 'cargarinicio_panel');

  $.ajax({
    async: false,
    url: "modelo/activofijo_modelo.php",
    type: 'POST',
    data: fd,
    cache: false,
    contentType: false,
    processData: false,
    dataType: 'json',
    success: function(data) {
      if (data.result) {
        //Si trae informacion
        //info=data.contenido.datosCatalogo;

        //fnAgregarGridv2(info, 'divCatalogo', "busqueda");
        procesoscontabilizar = data.contenido.infoprocesoscontabilizar
        var dataReasignacionJason = data.contenido.infolistadeactivos;
        var columnasNombres = data.contenido.columnasNombres;
        var columnasNombresGrid = data.contenido.columnasNombresGrid;
        var nombreExcel = data.contenido.nombreExcel;

        var columnasExcel = [0,1, 2, 3, 4, 5, 6,7,8];
        var columnasVisuales = [0,1, 2, 3, 4, 5, 6,7,8,9,10];

        fnLimpiarTabla('divTabla', 'divCatalogo');
        fnAgregarGrid_Detalle(dataReasignacionJason, columnasNombres, columnasNombresGrid, 'divCatalogo', ' ', 1, columnasExcel, false, false, "", columnasVisuales, nombreExcel);

      }
    }
  });

}


function fnObtenerOption(componenteSelect, intComillas = 0){
  console.log(componenteSelect);
    var valores = "";
    var comillas="'";
    var select = document.getElementById(''+componenteSelect);

    for ( var i = 0; i < select.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (select.selectedOptions[i].value != '-1') {
            if(intComillas == 1){
                comillas="";
            }

            if (i == 0) {
                valores = ""+comillas+select.selectedOptions[i].value+comillas+"";
            }else{
                valores = valores+", "+comillas+select.selectedOptions[i].value+comillas+"";
            }
        }
    }

    return valores;
}



function fnAdjuntos(id_bienes){
     
  console.log(id_bienes);
	var ruta = "abcAdjuntos.php?modal=true&id_bienes="+id_bienes;
	var contenido = '<div style="width: 100%; height: 400;"> <iframe className="" src="'+ruta+'" width="100%" height="400" frameBorder="0"></iframe> </div>';

	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i>Archivos Adjuntos</p></h3>';
	muestraModalGeneral(4, titulo, contenido);
    
}