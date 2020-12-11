
function fnAgregarCatalogoModal(){

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




function fnModificarActivoFijo(aID, aEr=false){
    

    stringid = aID.toString();

    if (aEr == true) {
      //stringurl = "<object type='text/html' data='activofijo.php?modal=true&new=false&aEr=true&AssetId="+ stringid + "'' width='100%' height='100%' ></object>";
      //stringtitulo = "Eliminar activo fijo";
      window.location.href = "activofijo.php?new=false&aEr=true&AssetId="+ stringid;
    } else {
      //stringurl = "<object type='text/html' data='activofijo.php?modal=true&new=false&aEr=false&AssetId="+ stringid + "'' width='100%' height='100%' ></object>";
      //stringtitulo = "Editar activo fijo";
      window.location.href = "activofijo.php?new=false&aEr=false&AssetId="+ stringid;
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

    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/activofijo_modelo.php",
            data: { cargarinicio_list: true },
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
            fnLimpiarTabla('divTabla', 'divCatalogo');
            fnAgregarGrid_Detalle(dataReasignacionJason, columnasNombres, columnasNombresGrid, 'divCatalogo', ' ', 1, columnasExcel, false, false, "", columnasVisuales, nombreExcel);
            
            
        }
        });

    //fnMostrarDatos('');

    //fnCambioUnidadNegocio();

    //fnFiltrarPorCuenta(info, '2.2.2', '#selectSituacionFinancieraDocumentosporPagaraLargoPlazo');


});