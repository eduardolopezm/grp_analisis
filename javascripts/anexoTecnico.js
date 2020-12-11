$(document).ready(function() {
    console.log('listo panel');
    var url = window.location.href.split('/');
    url.splice(url.length - 1);
    window.urlGrp = url.join('/');
    window.tblDatosRoot = [];
    window.filesRoot = [];
    window.limitRoot = 50;
    // llamadas iniciales
    renderTable();
    renderTableUpload();
    getStatus();

    // prevencion de envio por submit
    $(document).on('click', 'button', function(e) {
        e.preventDefault();
    });
    // comportamiento del filtro
    $('#btn-search').on('click', function() {
        var params = getParams('form-search');
        params.method = 'getAnexos';
        params.limit = window.limitRoot;
        muestraPrevioCargandoGeneral()
        $.post(window.urlGrp + '/modelo/anexoTecnicoModelo.php', params)
            .then(function(res) {
                var opt = 3;
                if (res.success) {
                    renderTable(res.content);
                    opt = 1;
                    window.tblDatosRoot = res.content;
                } else {
                    renderTable();
                }
                window.lista = res.lista;
            });
    });
    // comportamiento nuevo
    $('#nuevo').on('click', function() {
        window.location.href = window.urlGrp + '/anexoTecnicoDetalle.php';
    });
    // comportamineto de carga de archivos
    $('#btn-modal-upload').on('click', function() {
        $('#modal-upload').modal('show');
    });
    // comportamiento de nuevo a partir de existente
    $('#from-existing').on('click', function() {
        var forma = '';
        forma += `
		<div class="form-inline row">
			<div class="col-md-3" style="vertical-align: middle;">
				<span><label>Anexo Técnico: </label></span>
			</div>
			<div class="col-md-9">
				<select id="anexoTecnicoPartida" name="anexoTecnicoPartida" class="form-control"></select>
			</div>
		</div>
		`;
        // compocicion del footer de confirmación
        var pie = `
			<button class="btn botonVerde" onclick="processFromExiting()" data-dismiss="modal">Si</button>
			<button class="btn botonVerde" data-dismiss="modal">No</button>
		`;
        muestraModalGeneral(3, 'Generación de Nuevo Anexo Técnico', forma, pie);
        fnFormatoSelectGeneral('#anexoTecnicoPartida');
        $('#anexoTecnicoPartida').multiselect('dataprovider', window.lista);
    });
    /*************************** COMPORTAMIENTO DE LA CARGA DE ARCHIVOS ***************************/
    $('#label-inot-upload').on('click', cleanTblFiles);
    $('#inpt-upload').on('change', renderTableFiles);
    $('#btn-download').on('click', function() {
        var transno = [{
                transno: 0
            }],
            strElements, $elements, urlDoc;
        strElements = fnGenerarArchivoLayoutSinModal(nuFuncion, 51, transno, 1, 'Layout_Anexo_tecnico');
        $elements = $(strElements);
        link = $elements[1];
        open(link.href);
    });
    $('#btn-upload').on('click', function() {
        var $imptFiles = $('#inpt-upload'),
            forma = new FormData(),
            files, multipe;
        // files  = $imptFiles.prop('files');
        files = window.filesRoot;
        if (files.length) {
            // comprobacion de si es multiple o no
            forma.append('esmultiple', (typeof $imptFiles.attr('multipe') !== "undefined" ? 1 : 0));
            // cargado de de tipo
            forma.append('tipo', 51);
            // carga de funcion
            forma.append('funcion', nuFuncion);
            // carga de no permitidos
            forma.append('nopermitidos', 0); // se espera que siempre sea cero
            // carga de numero de transaccion
            forma.append('trans', 0);
            // carga de archivos
            $.each(files, function(index, val) {
                forma.append('archivos[]', val);
            });
            $.ajax({
                    url: "includes/Subir_Archivos.php",
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: forma,
                    type: 'post',
                })
                .then(function(res) {
                    var tipo = 3;
                    if (res.result) {
                        tipo = 1;
                        custonDatosDeArchivosSubidos(res.contenido.folio);
                    }
                    muestraMensajeTiempo(res.contenido.mensaje, tipo, 'modalMsg', 2000);
                });
        } else {
            muestraMensajeTiempo('Es necesario selección algún archivo para realizar la carga', 2, 'modalMsg', 2000);
        }
    });
    $(document).on('click', '.rm-file', function() {
        var $el = $(this),
            $parent;
        $parent = $el.parent('tr');
        window.filesRoot.splice($parent.attr('id'), 1);
        $parent.remove();
    });
    /*************************** COMPORTAMIENTO DE LA CARGA DE BOTONES ***************************/
    // obtenerBotones('areaBotones');
    fnObtenerBotones_Funcion('areaBotones', nuFuncion);
    $(document).on('click', '#Rechazar, #Avanzar, #Autorizar, #Cancelar', changeNewStatus);

    // cambio de scroll en los multiselect
    $('.multiselect-container').css({
        'max-height': "200px"
    });
    $('.multiselect-container').css({
        'overflow-y': "scroll"
    });

});
// sobre escritura
function fnCambiarEstatus() {}

function renderTable(data) {
    var data = data || [],
		el = 'datos',
        tabla = 'tabla',
        visualcolumn = [0, 1, 2, 3, 5, 6, 7],
        columntoexcel = [1, 2, 3, 4, 6, 7],
        nameexcel = 'anexo tecnico',
        datafields = '',
        columns = '';
    // datos y comportamiento de los datos
    datafields = [{
            name: 'check',
            type: 'bool'
        }, // 0
        {
            name: 'folio',
            type: 'string'
        }, // 1
        {
            name: 'fecha',
            type: 'string'
        }, // 2
        {
            name: 'ur',
            type: 'string'
        }, // 3
        {
            name: 'descripcion',
            type: 'string'
        }, // 4
        {
            name: 'sSta',
            type: 'string'
        }, // 5
        {
            name: 'url',
            type: 'string'
        }, // 6
        {
            name: 'status',
            type: 'string'
        }, // 7
        {
            name: 'nUR',
            type: 'string'
        }, // 8
        {
            name: 'iFolio',
            type: 'string'
        }, // 9
        {
            name: 'ue',
            type: 'string'
        }, // 10
        {
            name: 'nUE',
            type: 'string'
        }, // 11
    ];
    // titulos y estilos de las columnas
    // UR	UE	Fecha	Folio	Referencia	Estatus
    columns = [{
            text: 'Sel',
            datafield: 'check',
            columntype: 'checkbox',
            width: '5%',
            cellsalign: 'center',
            align: 'center'
        }, // 0
        {
            text: 'UR',
            // datafield: 'ur',
            datafield: 'nUR',
            editable: false,
            width: '10%',
            cellsalign: 'center',
            align: 'center'
        }, // 1
        {
            text: 'UE',
            // datafield: 'ue',
            datafield: 'nUE',
            editable: false,
            width: '10%',
            cellsalign: 'center',
            align: 'center'
        }, // 2
        {
            text: 'Fecha',
            datafield: 'fecha',
            editable: false,
            width: '10%',
            cellsalign: 'center',
            align: 'center'
        }, // 3
        {
            text: 'Folio',
            datafield: 'iFolio',
            editable: false,
            width: '10%',
            cellsalign: 'center',
            align: 'center',
            hidden: true
        }, // 4
        {
            text: 'Folio',
            datafield: 'folio',
            editable: false,
            width: '10%',
            cellsalign: 'center',
            align: 'center'
        }, // 5
        {
            text: 'Referencia',
            datafield: 'descripcion',
            editable: false,
            width: '45%',
            cellsalign: 'center',
            align: 'center'
        }, // 6
        {
            text: 'Estatus',
            datafield: 'sSta',
            editable: false,
            width: '10%',
            cellsalign: 'center',
            align: 'center'
        }, // 7
        {
            text: 'status',
            datafield: 'status',
            editable: false,
            width: '10%',
            cellsalign: 'center',
            hidden: true,
            align: 'center'
        }, // 8
    ];

    // llamado de limpiesa de la tabla
    fnLimpiarTabla(tabla, el);
    // renderisado de la tabla
    fnAgregarGrid_Detalle_nostring(data, datafields, columns, el, ' ', 1, columntoexcel, false, true, "", visualcolumn, nameexcel);
}

function renderTableUpload(data) {
    var data = data || [],
		el = 'files-upload',
        tabla = 'content-files',
        visualcolumn = [0, 2, 3, 4, 5],
        columntoexcel = [2, 3, 4, 5],
        nameexcel = 'anexo tecnico',
        datafields = '',
        columns = '';
    // datos y comportamiento de los datos
    datafields = [{
            name: 'cajacheckbox',
            type: 'bool'
        },
        {
            name: 'id',
            type: 'string'
        },
        {
            name: 'tipo',
            type: 'string'
        },
        {
            name: 'nombre',
            type: 'string'
        },
        {
            name: 'funcion',
            type: 'string'
        },
        {
            name: 'tipo_doc',
            type: 'string'
        },
        {
            name: 'usuario',
            type: 'string'
        },
        {
            name: 'fecha',
            type: 'string'
        }
    ];

    // titulos y estilos de las columnas
    columns = [{
            text: '',
            datafield: 'cajacheckbox',
            width: '4%',
            cellsalign: 'center',
            align: 'center',
            columntype: 'checkbox',
            hidden: false
        },
        {
            text: 'id',
            datafield: 'id',
            width: '10%',
            cellsalign: 'center',
            align: 'center',
            hidden: true
        },
        {
            text: 'Extensión',
            datafield: 'tipo',
            width: '5%',
            cellsalign: 'center',
            align: 'center',
            hidden: false
        },
        {
            text: 'Nombre archivo',
            datafield: 'nombre',
            width: '19%',
            align: 'center',
            hidden: false,
            cellsalign: 'left'
        },
        {
            text: 'Función',
            datafield: 'funcion',
            width: '18%',
            align: 'center',
            hidden: false,
            cellsalign: 'left'
        },
        {
            text: 'Tipo documento',
            datafield: 'tipo_doc',
            width: '18%',
            align: 'center',
            hidden: false,
            cellsalign: 'left'
        },
        {
            text: 'Usuario',
            datafield: 'usuario',
            width: '18%',
            align: 'center',
            hidden: false,
            cellsalign: 'center'
        },
        {
            text: 'Fecha',
            datafield: 'fecha',
            width: '18%',
            cellsalign: 'center',
            align: 'center',
            hidden: false
        }
    ];

    // llamado de limpiesa de la tabla
    fnLimpiarTabla(tabla, el);
    // renderisado de la tabla
    fnAgregarGrid_Detalle_nostring(data, datafields, columns, el, '', 1, columntoexcel, false, true, "", visualcolumn, nameexcel);
}

function renderTableFiles() {
    var $el = $(this),
        $tbl = $('#tbl-filesToUp'),
        len = 0,
        files = [],
        $tbody;
    files = $el.prop('files');
    len = files.length;
    $tbody = $tbl.find('tbody');
    $('#showFiles').removeClass('hidden');
    if (len) {
        // agregado a variable global
        $.each(files, function(index, val) {
            window.filesRoot.push(val);
            var aceptedType = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'],
                type = val.type;
            if (aceptedType.indexOf(val.type) !== -1) {
                type = 'xlsx';
            }
            var row = [
                generateItem('td', {
                    html: val.name
                }),
                generateItem('td', {
                    html: val.size
                }),
                generateItem('td', {
                    html: type
                }),
                generateItem('span', {
                    class: 'btn btn-sm btn-primary bgc8 rm-file'
                }, generateItem('span', {
                    class: 'glyphicon glyphicon-remove'
                }))
            ];

            $tbody.append(generateItem('tr', {
                id: index
            }, row));
        });
    }
}

function linkrenderer(row, column, value) {
    var format = {
            target: '"_self"'
        },
        link = window.urlGrp + '/anexoTecnicoDetalle.php',
        row = $('#datos').jqxGrid('getrowdata', row),
        txt = '';
    if (!$.isEmptyObject(row.url)) {
        txt = '<div class="jqx-grid-cell-middle-align" style="margin-top: 7px;"><a href="' + link + '?URL=' + row.url + '"><span>' + value + '</span></a></div>';
    } else {
        txt = '<div class="jqx-grid-cell-middle-align" style="margin-top: 7px;"><a href="#"><span>' + value + '</span></a></div>';
    }
    return txt;
}

function custonDatosDeArchivosSubidos(folio) {
    muestraPrevioCargandoGeneral();
    $.ajax({
            url: "includes/Subir_Archivos.php",
            dataType: 'json',
            type: 'POST',
            data: {
                proceso: 'obtenerDatosArchivos',
                funcion: nuFuncion,
                tipo: 51,
                trans: folio
            },
        })
        .then(function(res) {
            if (res.contenido.DatosArchivos.length) {
                renderTableUpload(res.contenido.DatosArchivos);
            } else {
                muestraMensajeTiempo('No se puede obtener la información de los documentos', 2, 'modalMsg', 2000);
            }
        });
}

function obtenerBotones(el) {
	var el = el || 'areaBotones';
    muestraPrevioCargandoGeneral();
    $.post('modelo/anexoTecnicoModelo.php', {
            method: 'getBotones',
            fn: nuFuncion
        })
        .then(function(res) {
            if (res.success) {
                var content = res.content,
                    btns = '';
                content.forEach(function(el, i) {
                    if (el.statusname != 3)
                        btns += ' <component-button id="' + el.statusname + '" value="' + el.namebutton + '" title="' + el.name + '" class="' + el.clases + '" ></component-button>';
                });
            }
            $('#' + el).append(btns);
            fnEjecutarVueGeneral(el);
        });
}

function changeNewStatus() {
    var el = $(this),
        selections = getSelects('datos');
    if (selections.length != 0) {
        if (el.attr('id') == 'Autorizar') {
            var noChange = [2, 3, 5],
                flag = 0;
            $.each(selections, function(ind, row) {
                if (noChange.indexOf(row.status) !== -1) {
                    flag++;
                    muestraModalGeneral(3, 'Error de datos', 'Los anexos con estatus Por Asignar, Asignados y Cancelados no pueden ser Autorizados.', '<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                }
            });
            if (flag == 0) {
                var titulo = 'Confirmación',
                    mensaje = '';
                $.each(selections, function(index, val) {
                    mensaje += '¿Estas seguro de autorizar el anexo técnico ' + val.iFolio + '? <br />';
                });
                muestraModalGeneralConfirmacionAnexo(3, titulo, mensaje, '', 'autorizaGeneral()');
            }
        }
        // cancelacion
        else if (el.attr('id') == 'Cancelar') {
            var noCancel = window.noPermitidos,
                flag = 0;
            $.each(selections, function(ind, row) {
                if (noCancel.indexOf(row.status) !== -1) {
                    flag++;
                    muestraModalGeneral(3, 'Error de datos', 'No cuenta con los permisos para cancelar los anexos solicitados.', '<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                }
            });
            if (flag == 0) {
                var titulo = 'Confirmación',
                    mensaje = '';
                $.each(selections, function(index, val) {
                    mensaje += '¿Estas seguro de cancelar el anexo técnico ' + val.iFolio + '? <br />';
                });
                muestraModalGeneralConfirmacionAnexo(3, titulo, mensaje, '', 'cancelaGeneral()');
            }
        }
        // rechazo
        else if (el.attr('id') == 'Rechazar') {
            var noChange = window.noPermitidos,
                flag = 0;
            noChange.push(1);
            if (profile == 11) {
                // se retiran los datos eventuales
                noChange.splice(noChange.indexOf(2), 1);
            }
            $.each(selections, function(index, row) {
                if (noChange.indexOf(row.status) !== -1) {
                    flag++;
                    muestraModalGeneral(3, 'Error de datos', 'No puede rechazar los anexos indicados.', '<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                }
            });
            // se retiran los datos eventuales
            noChange.splice(noChange.indexOf(1), 1);
            if (flag == 0) {
                var titulo = 'Confirmación',
                    mensaje = '';
                $.each(selections, function(index, val) {
                    mensaje += '¿Estas seguro de rechazar el anexo técnico ' + val.iFolio + '? <br />';
                });
                muestraModalGeneralConfirmacionAnexo(3, titulo, mensaje, '', 'rechazaGeneral()');
            }
        }
        // avnazar
        else if (el.attr('id') == 'Avanzar') {
            var noChange = window.noPermitidos,
                flag = 0;
            $.each(selections, function(index, row) {
                if (noChange.indexOf(row.status) !== -1) {
                    flag++;
                    muestraModalGeneral(3, 'Error de datos', 'No puede avanzar los anexos indicados.', '<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                }
            });
            if (flag == 0) {
                var titulo = 'Confirmación',
                    mensaje = '';
                $.each(selections, function(index, val) {
                    mensaje += '¿Estas seguro de avanzar el anexo técnico ' + val.iFolio + '? <br />';
                });
                muestraModalGeneralConfirmacionAnexo(3, titulo, mensaje, '', 'avanzarGeneral()');
            }
        }
    } else {
        muestraModalGeneral(3, 'Error de datos', 'No ha seleccionado ningún elemento.', '<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
    }
}

// depreciada
function changeStatus() {
    return;
    var rows = getSelects('datos'),
        tipo = $(this).attr('id'),
        tipoString;
    if (rows.length) {
        var tipoString = (
            tipo == 1 ? 'CAPTURADO' :
            tipo == 2 ? 'SIN ASIGNAR' :
            tipo == 3 ? 'ASIGNADO' : 'SIN ESTATUS');
        $.post('modelo/anexoTecnicoModelo.php', {
                method: 'updateStatus',
                type: tipo,
                rows: rows
            })
            .then(function(res) {
                var tipoAl = 3;
                if (res.success) {
                    tipoAl = 1;
                    rows.forEach(function(el) {
                        tblDatosRoot[el.boundindex].sSta = tipoString;
                        tblDatosRoot[el.boundindex].status = tipo;
                    });
                    renderTable(tblDatosRoot);
                }
                muestraMensajeTiempo(res.msg, tipoAl, 'mensaje', 3000);
            });
    } else {
        muestraMensajeTiempo('Es necesario seleccionar mínimo un dato ', 2, 'mensaje', 2000);
    }
}

function getSelects(tbl, filedata) {
    var filedata = filedata || 'check',
		$tbl = $('#' + tbl),
        rows = [],
        len = i = 0,
        infTbl;
    infTbl = $tbl.jqxGrid('getdatainformation');
    len = infTbl.rowscount;
    for (; i < len; i++) {
        var data = $tbl.jqxGrid('getrowdata', i);
        if (data[filedata]) {
            rows.push(data);
        }
    }
    return rows;
}

function getStatus() {
    var $status = $('#status'),
        options = [{
            label: 'Seleccione una opción',
            title: 'Seleccione una opción',
            value: ''
        }];
    $status.multiselect({
        enableFiltering: true,
        filterBehavior: 'text',
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        numberDisplayed: 1,
        includeSelectAllOption: true
    });
    muestraPrevioCargandoGeneral();
    $.post('modelo/anexoTecnicoModelo.php', {
            method: 'getStatus',
            functionid: nuFuncion
        })
        .then(function(res) {
            if (res.success) {
                $status.multiselect('dataprovider', res.content);
            } else {
                $status.multiselect('dataprovider', options);
            }
            // encaso de cambiar la forma de inicio la busqueda modificar la funcion
            autoSearch();
        });
}

function autoSearch() {
    var params = getParams('form-search');
    params.method = 'getAnexos';
    params.limit = window.limitRoot;
    $.post(window.urlGrp + '/modelo/anexoTecnicoModelo.php', params)
        .then(function(res) {
            var opt = 3;
            if (res.success) {
                renderTable(res.content);
                opt = 1;
                window.tblDatosRoot = res.content;
            } else {
                renderTable();
            }
            window.profile = res.profile;
            window.noPermitidos = res.noPermitidos;
            window.lista = res.lista;
        });
}

function cleanTblFiles() {
    var $tbl = $('#tbl-filesToUp'),
        $tbody;
    $tbody = $tbl.find('tbody');
    $tbody.html('');
    window.filesRoot = [];
}

function avanzarGeneral() {
    var selections = getSelects('datos');
    muestraPrevioCargandoGeneral();
    var estotusPerfil = profile == 9 ? 6 : (profile == 10 ? 7 : (profile == 11 ? 2 : 2));
    $.post('modelo/anexoTecnicoModelo.php', {
            method: 'updateStatus',
            rows: selections,
            type: estotusPerfil
        })
        .then(function(res) {
            if (res.success) {
                var tipoAl = 1,
                    mensaje = '',
                    links = res.links,
                    nuevoEstatus = res.nuevoEstatus;
                selections.forEach(function(el) {
                    mensaje += 'Se avanzó el Anexo Técnico ' + el.iFolio + ' ' + (profile == 9 ? 'al Validador.' : profile == 10 ? 'al Autorizador' : '') + '<br />';
                    tblDatosRoot[el.boundindex].sSta = nuevoEstatus[el.iFolio][1];
                    tblDatosRoot[el.boundindex].status = nuevoEstatus[el.iFolio][0];
                    tblDatosRoot[el.boundindex].folio = links[el.iFolio];
                });
                $('.modal-backdrop.fade').removeClass('modal-backdrop in');
                renderTable(tblDatosRoot);
                muestraModalGeneralAnexo(3, 'Operacion Exitosa', mensaje, '<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
            }
        });
}

function rechazaGeneral() {
    var selections = getSelects('datos');
    muestraPrevioCargandoGeneral()
    var estotusPerfil = 1,
        tipo = 0;
    if (profile == 11) {
        // procesamiento de las solicitudes
        $.each(selections, function(index, element) {
            if (element.status == 2) {
                tipo = 1;
            }
        });
        if (tipo) {
            estotusPerfil = 7;
        } else {
            estotusPerfil = 6;
        }
    }
    $.post('modelo/anexoTecnicoModelo.php', {
            method: 'updateStatus',
            rows: selections,
            type: estotusPerfil
        })
        .then(function(res) {
            var mensaje = '';
            if (res.success) {
                var links = res.links,
                    nuevoEstatus = res.nuevoEstatus;
                selections.forEach(function(el) {
                    tblDatosRoot[el.boundindex].sSta = nuevoEstatus[el.iFolio][1];
                    tblDatosRoot[el.boundindex].status = nuevoEstatus[el.iFolio][0];
                    tblDatosRoot[el.boundindex].folio = links[el.iFolio];
                    mensaje += 'Se ha rechazado el Anexo Técnico ' + el.iFolio + ' ' + (profile == 11 ? (tipo ? 'al estatus Por Autorizar' : 'al Validador.') : profile == 10 ? 'al Capturista' : '') + '<br />';
                });
                $('.modal-backdrop.fade').removeClass('modal-backdrop in');
                renderTable(tblDatosRoot);
                muestraModalGeneralAnexo(3, 'Operacion Exitosa', mensaje, '<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
            }
        });
}

function cancelaGeneral() {
    var selections = getSelects('datos');
    muestraPrevioCargandoGeneral();
    $.post('modelo/anexoTecnicoModelo.php', {
            method: 'updateStatus',
            rows: selections,
            type: 5
        })
        .then(function(res) {
            if (res.success) {
                var links = res.links,
                    mensaje = '';
                selections.forEach(function(el) {
                    tblDatosRoot[el.boundindex].sSta = 'Cancelado';
                    tblDatosRoot[el.boundindex].status = 5;
                    tblDatosRoot[el.boundindex].folio = links[el.iFolio];
                    mensaje += 'Se ha cancelado el Anexo Técnico ' + el.iFolio + '. <br />';
                });
                renderTable(tblDatosRoot);
                $('.modal-backdrop.fade').removeClass('modal-backdrop in');
                muestraModalGeneralAnexo(3, 'Operación Exitosa', mensaje, '<button type="button" class="btn botonVerde" data-dismiss="modal" onClick="delFadein()">Aceptar</button>');
            }
        });
}

function autorizaGeneral() {
    var selections = getSelects('datos');
    muestraPrevioCargandoGeneral();
    $.post('modelo/anexoTecnicoModelo.php', {
            method: 'updateStatus',
            rows: selections,
            type: 2
        })
        .then(function(res) {
            if (res.success) {
                var links = res.links,
                    mensaje = '';
                selections.forEach(function(el) {
                    tblDatosRoot[el.boundindex].sSta = 'Por Asignar';
                    tblDatosRoot[el.boundindex].status = 2;
                    tblDatosRoot[el.boundindex].folio = links[el.iFolio];
                    mensaje += 'El Anexo Técnico ' + el.iFolio + ' ha sido autorizado. <br />';
                });
                renderTable(tblDatosRoot);
                $('.modal-backdrop.fade').removeClass('modal-backdrop in');
                muestraModalGeneralAnexo(3, 'Operación Exitosa', mensaje, '<button type="button" class="btn botonVerde" data-dismiss="modal" onClick="delFadein()">Aceptar</button>');
            }
        });
}

function muestraPrevioCargandoGeneral() {
    // $('.modal-backdrop.fade').removeClass('modal-backdrop in');
}

function delFadein() {
    $('.modal-backdrop.fade').removeClass('modal-backdrop in');
}

function processFromExiting() {
    var params = getParams('ModalGeneral_Mensaje');
    params.method = 'processFromExiting';
    if (params.anexoTecnicoPartida == '') {
        muestraModalGeneral(3, 'Error de Datos', 'Es necesario que indique el anexo técnico del cual desea partir.');
        return;
    }
    $.post(window.urlGrp + '/modelo/anexoTecnicoModelo.php', params)
        .then(function(res) {
            muestraModalGeneral(3, (res.success ? 'Operación Exitosa' : 'Error de Datos'), res.msg);
            autoSearch();
        });
}

function muestraModalGeneralConfirmacionAnexo(tam, titulo, mensaje, pie, funcion) {
    var pie = `
		<button class="btn botonVerde" onclick="${funcion}" data-dismiss="modal" onclick="delFadein();">Si</button>
		<button class="btn botonVerde" data-dismiss="modal">No</button>
	`;
    muestraModalGeneralAnexo(tam, titulo, mensaje, pie);
}

function muestraModalGeneralAnexo(tam, titulo, mensaje, pie) {
    if ($('#ModalGeneral').data('bs.modal')) {
        if ($('#ModalGeneral').data('bs.modal').isShown) {
            $('#ModalGeneral').modal('hide');
        }
    }

    setTimeout(function() {
        muestraModalGeneral(tam, titulo, mensaje, pie);
    }, 500);
}
