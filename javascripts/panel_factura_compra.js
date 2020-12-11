/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 * @Fecha 01 de Diciembre del 2017
 */

var url= "modelo/panel_factura_compra_modelo.php";

$(document).ready(function () {
    /**
     * Muestra la información del catalogo completo o de forma individual
     * @param  {String} ue Código del Registro para obtener la información
     */
    $("#btnBuscarOrdenes").click(function() {
        muestraCargandoGeneral();

        var pFechaini = $("#txtFechaInicio").val(),
            pFechafin = $("#txtFechaFin").val(),
            pDependencia = $("#selectRazonSocial").val(),
            pUnidadResposable = $("#selectUnidadNegocio").val(),
            pRequisicion= $("#txtNumeroRequisicion").val(),
            pCodigoProveedor= $("#txtCodigoProveedor").val(),
            pNombreProveedor= $("#txtNombreProveedor").val(),
            pEstatus= '',
            pFuncion= $("#PanelOrdenesCompra").data("funcion"),
            noOrdenCompra = $("#txtOrdenCompra").val();

        var columnasNombres= "", columnasNombresGrid= "";

        // configurar columna de resumen
        var colResumenTotal= ", aggregates: [{'<b>Total</b>' :"+
                            "function (aggregatedValue, currentValue) {"+
                                "var total = currentValue;"+
                                "return aggregatedValue + total;"+
                            "}"+
                        "}] ";

        //Parametros para la extraccion de datos
        dataObj = { 
            option: 'traeOrdenesCompras',
            fechainicio: pFechaini,
            fechafin: pFechafin,
            dependencia: pDependencia,
            unidadres: pUnidadResposable,
            requisicion: pRequisicion,
            idproveedor: pCodigoProveedor,
            nomproveedor: pNombreProveedor,
            estatus: pEstatus,
            funcion: pFuncion,
            noOrdenCompra: noOrdenCompra
        };
        
        //Se manda a llamar metodo que trae los datos
        $.ajax({
            async:false,
            cache:false,
            method: "POST",
            dataType: "json",
            url: url,
            data: dataObj

        }).done(function(data) {
            //console.log("Bien");
            
            ocultaCargandoGeneral();

            fnLimpiarTabla('divTabla', 'divCatalogo');

            // $info[] = array(
            //     "idrequisicion"=> "<span>".$registro["requisitionno"]."</span>",
            //     "numerorequisicion"=> $registro["requisitionno"],
            //     "idproveedor" => $registro["supplierid"],
            //     "nombreproveedor" => ($registro["suppname"]),
            //     "estatus" => $registro["sn_nombre_secundario"],
            //     "totalrequisicion" => $registro["ordervalue"],
            //     "seleccionar" => $seleccionar,
            //     "fecharequerida" => $registro["fecharequerida"],
            //     "observaciones" => $registro["comments"],
            //     "orderno" => $registro["orderno"],
            //     "ordencompra" => $registro["realorderno"],
            //     "tagdescription" => $registro["tagdescription"],
            //     "compra" => $opciones
            // );

            if (data.result) {
                // Columnas para el GRID
    		    columnasNombres += "[";
    		    //columnasNombres += "{ name: 'seleccionar', type: 'string' },";
                columnasNombres += "{ name: 'idrequisicion', type: 'string' },";
                columnasNombres += "{ name: 'ordencompra', type: 'string' },";
                columnasNombres += "{ name: 'fecharequerida', type: 'string' },";
    		    columnasNombres += "{ name: 'idproveedor', type: 'string' },";
    		    columnasNombres += "{ name: 'nombreproveedor', type: 'string' },";
    		    columnasNombres += "{ name: 'estatus', type: 'string' },";
                columnasNombres += "{ name: 'tagdescription', type: 'string' },";
    		    columnasNombres += "{ name: 'totalrequisicion', type: 'number' },";
                columnasNombres += "{ name: 'totalrequisicion2', type: 'string' },";
                columnasNombres += "{ name: 'observaciones', type: 'string' },";
                columnasNombres += "{ name: 'compra', type: 'string' }";
    		    columnasNombres += "]";

    		    // Columnas para el GRID
    		    columnasNombresGrid += "[";
                //columnasNombresGrid += " { text: 'Sel', datafield: 'seleccionar', width: '3%', hidden: false },";
                columnasNombresGrid += " { text: 'Requisicion', datafield: 'idrequisicion', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
                columnasNombresGrid += " { text: 'Orden Compra', datafield: 'ordencompra', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    		    columnasNombresGrid += " { text: 'Fecha Req.', datafield: 'fecharequerida', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    		    columnasNombresGrid += " { text: 'Código Proveedor', datafield: 'idproveedor', width: '7%', hidden: false },";
    		    columnasNombresGrid += " { text: 'Nombre Proveedor', datafield: 'nombreproveedor', width: '17%', hidden: false },";
    		    columnasNombresGrid += " { text: 'Estatus', datafield: 'estatus', width: '9%', cellsalign: 'center', align: 'center', hidden: false},";
                columnasNombresGrid += " { text: 'UR', datafield: 'tagdescription', width: '15%', cellsalign: 'center', align: 'center', hidden: false},";
    		    columnasNombresGrid += " { text: 'Total', datafield: 'totalrequisicion', width: '10%', cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false"+colResumenTotal+"},";
                columnasNombresGrid += " { text: 'Total', datafield: 'totalrequisicion2', width: '10%', cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: true},";
    			columnasNombresGrid += " { text: 'Observaciones', datafield: 'observaciones', width: '17%', hidden: false, rendered: tooltiprenderer },";
                columnasNombresGrid += " { text: 'Compra', datafield: 'compra', width: '8%', cellsalign: 'center', align: 'center', hidden: false, rendered: tooltiprenderer }";
                columnasNombresGrid += "]";

                // arreglo que guarda las columnas que se ocultan para exportar a excel
                var columnasExcel= [0, 1, 2, 3, 4, 5, 6, 8, 9];
                var columnasVisuales= [0, 1, 2, 3, 4, 5, 6, 7, 9, 10];
                var nombreExcel = data.contenido.nombreExcel;

                info = data.contenido.datos;

                //fnAgregarGrid(info, columnasNombres, columnasNombresGrid, 'divCatalogo', 'nombreproveedor', 1);
                fnAgregarGrid_Detalle(info, columnasNombres, columnasNombresGrid, 'divCatalogo', ' ', 1, columnasExcel, true, false, "", columnasVisuales, nombreExcel);
            }
        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
            ocultaCargandoGeneral();
        });
    });
    // Funcion que dispara evento con la opcion de ventana nueva para la captura
    // de requisicion
    $("#btnNuevaOrden").click(function (){
        window.open("PO_Header.php?NewOrder=Yes", "_blank");
    });

    $("#btnBuscarOrdenes").click();

    // Datos botones
    fnObtenerBotones_Funcion('divBotones', $("#PanelOrdenesCompra").data("funcion"));
  $('#btnConsolidarRequisiciones').click(function(){
         window.open("consolidaciones.php","_self");
  })
});
