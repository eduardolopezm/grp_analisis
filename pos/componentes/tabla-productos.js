var React = require('react');
var update = require('react-addons-update');
var ReactBsTable = require('react-bootstrap-table');
var BootstrapTable = ReactBsTable.BootstrapTable;
var TableHeaderColumn = ReactBsTable.TableHeaderColumn;
var Tooltip = require('react-bootstrap').Tooltip;
var OverlayTrigger = require('react-bootstrap').OverlayTrigger;
////
import {FormattedNumber} from 'react-intl';
import axios from 'axios';

var veces = 0;

const tooltipRealizarTraspaso = (
    <Tooltip id="tooltip">Realizar Traspaso</Tooltip>
);

const tooltipActualizarExistencias = (
    <Tooltip id="tooltip">Actualizar Existencias</Tooltip>
);

const tooltipAnticipos = (
    <Tooltip id="tooltip">Anticipos</Tooltip>
);

const tooltipImpresionTraspaso = (
    <Tooltip id="tooltip">Impresión Vale Traspaso</Tooltip>
);

const tooltipImpresionInformacion = (
    <Tooltip id="tooltip">Información Traspaso</Tooltip>
);

var TablaProductos = React.createClass({

    getInitialState: function() {
        return {
            txtDescuentoMasivo: 0
        };
    },

    priceFormatter: function (cell, row){
        return <FormattedNumber value={cell} style="currency" currency="USD" />;
    },

    discountFormatter: function (cell, row){
        return  cell + ' %';
    },

    onAfterSaveCell: function (row, cellName, cellValue) { 

        var row_index = 0;

        var obj = this.props.productos.filter(function(element, index, array) {
            if(element.stockid === row.stockid) {
                row_index = index;
            }
            return element;
        });

        var iva = row.taxrate;
        var row_subtotal = Number(row.quantity) * Number(row.price);
        var row_discount = row_subtotal * (row.discount / 100)
        row_subtotal = row_subtotal - row_discount;
        var row_iva = row_subtotal * Number(iva);
        var row_total = Number(row_subtotal) + Number(row_iva);

        //operaciones a mostrar tabla por producto
        var updated_row = update(this.props.productos[row_index], {
            $cellName: {
                $set: Number(cellValue)
            },
            subtotal: {
                $set: row_subtotal
            },
            iva: {
                $set: row_iva
            },
            total: {
                $set: row_total
            }
        }); 

        var newRowData = update(this.props.productos, {
            $splice: [[row_index, 1, updated_row]]
        });

        this.props.parent.setState({
            productos: newRowData 
        });
    },

    onBeforeSaveCell: function (row, cellName, cellValue) {
        if (cellValue < 0 && row.disminuye_ingreso == '0') {
            //si no es positivo
            return false;
        }

        if (cellValue > 0 && row.disminuye_ingreso == '1' && cellName == 'quantity') {
            //si es positivo y disminuye ingreso
            return false;
        }

        if (cellName == 'price' && Number(row.idcontrato) > Number(0)) {
            // Es precio y viene de contrato
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = "No es posible cambiar el precio, esta definido en el contrato";
            this.props.abrirAlerta(title, body);

            return false;
        }

        console.log("***********");
        console.log("row: "+JSON.stringify(row));
        console.log("cellName: "+JSON.stringify(cellName));
        console.log("cellValue: "+JSON.stringify(cellValue));
        if (cellName == "discount") {
            //si la columna es descuento
            // if (cellValue <= this.props.descuentoMaximo) {
                if ((cellValue < 0) || (cellValue > 100)) {
                    //si no es positivo
                    return false;
                }else{
                    //deja poner descuento
                    return true;
                }
            // }else{
            //     return false;
            // }
        }else{
            var is_number = true;
            // No es un numero no lo guardes
            if (isNaN(cellValue)) {
                is_number = false;
            }
            return is_number;
        }
    },

    customConfirm: function (next, dropRowKeys) {
        // String con los codigos a eliminar 
        // en caso de querer usarlo en la alerta
        if (this.props.valBloquearOperacion) {
            this.props.abrirAlerta("Mensaje", "No se pueden eliminar los "+this.props.nomArticuloVentaPlural+" de un pase de cobro ya generado");
            return false;
        }
        const dropRowKeysStr = dropRowKeys.join(',');
        if (confirm('¿Estas seguro que quieres eliminar los '+this.props.nomArticuloVentaPlural+' seleccionados?')) {
            
            next();
        }
    },

    botonTraspaso: function() {
        var stockid = this.refs.table.state.selectedRowKeys[0];
        if (stockid != null) {
            this.props.abrirTraspaso(stockid);
        }else{
            this.props.abrirAlerta("Traspaso", "Seleccionar un "+this.props.nomArticuloVenta+" para poder realizar la operación");
        }
    },

    botonActualizarExistencias: function() {
        // var stockid = this.refs.table.state.selectedRowKeys[0];
        // console.log("productos antes: "+JSON.stringify(this.props.productos));
        // console.log("tagref: - "+this.props.tagref);
        // console.log("location: - "+this.props.location);
        // console.log("typeabbrev: - "+this.props.typeabbrev);

        if (this.props.productos.length == 0) {
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = "No tienes "+this.props.nomArticuloVentaPlural+" agregados.";
            this.props.abrirAlerta(title, body);
            return true;
        }

        var self = this;

        self.props.mostrarCargando("mostrar");

        //Obtener si existe sesion iniciada
        axios.get('./../mvc/api/v1/getsession')
        .then(function (response) {
            self.props.mostrarCargando("cerrar");
            if(response.data.success) {
                //si hay sesion realiza operacion
                // console.log("Tiene sesion");
                var errorActualicacion = 0;

                var obj = self.props.productos.filter(function(element, index, array) {
                    console.log("stockid: - "+element.stockid+" - index: "+index);
                    axios.get('./../mvc/api/v1/getproducts/' + element.stockid + '/' + self.props.tagref + '/'+ self.props.location + '/MXN/'+ self.props.typeabbrev)
                    .then(function (response) {
                        if(response.data.success) {
                            var datosProducto = response.data.data[0];
                            console.log("*** producto ***");
                            console.log("stockid: "+datosProducto.stockid);
                            console.log("available: "+datosProducto.available);
                            // self.props.parent.state.productos[info].available = datosProducto.available;

                            //operaciones a mostrar tabla por producto
                            var updated_row = update(self.props.productos[index], {
                                available: {
                                    $set: datosProducto.available
                                }
                            }); 

                            var newRowData = update(self.props.productos, {
                                $splice: [[index, 1, updated_row]]
                            });

                            self.props.parent.setState({
                                productos: newRowData 
                            });
                        } else {
                            errorActualicacion = 1;
                        }
                    })
                    .catch(function (error) { 
                        // console.log(error);
                        errorActualicacion = 1;
                    });
                });
                // console.log("productos despues: "+JSON.stringify(self.props.state.productos));
                if (errorActualicacion == 1) {
                    var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                    var body = "Ocurrio un error al actualizar disponible";
                    self.props.abrirAlerta(title, body);
                }

                return true;
            } else {
                //si no hay sesion no realiza la operacion
                //console.log("No tiene sesion");
                var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                var body = <div>
                                <p>Sesion actual terminada, iniciar sesion nuevamente</p>
                                <p>Al iniciar sesion regresa a esta pestaña y da click en aceptar</p>
                                <p><a href="../../ap_grp_demo/" target="_blank">Iniciar Sesion</a></p>
                            </div>;
                self.props.abrirAlerta(title, body);
                return false;
            }
        })
        .catch(function (error) {
            console.log("Error Obtener Sesion");
            //si sale error no realiza la operacion
            self.props.mostrarCargando("cerrar");
            var err = '';
            if (error.response) {
                err = error.response.status;
            } else {
                err = error.message;
            }
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = "Ocurrio un error en la peticion: " + err;
            self.props.abrirAlerta(title, body);
            return false;
        });
    },

    botonDescuentoMasivo: function(event) {
        // console.log("fn botonDescuentoMasivo");
        // console.log("productos:"+JSON.stringify(this.props.productos));
        if(this.props.productos.length > 0) {
            var newRowData = this.props.productos;
            for (var info in newRowData) {
                console.log("stockid: "+newRowData[info].stockid);
                newRowData[info].discount = this.state.txtDescuentoMasivo;
            }
            this.props.parent.setState({
                productos: newRowData
            });

            this.setState({
                txtDescuentoMasivo: 0
            });
        } else {
            var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
            var body = "No tienes "+this.props.nomArticuloVentaPlural+" agregados.";
            this.props.abrirAlerta(title, body);
        }
    },

    cambioDescuentoMasivo: function(event) {
        // console.log("fn cambioDescuentoMasivo");
        if (isNaN(event.target.value)) {
            // console.log("no es numero");
        } else {
            // console.log("es numero");
            if ((Number(event.target.value) >= Number(0)) && (Number(event.target.value) <= Number(100))) {
                this.setState({
                    txtDescuentoMasivo: event.target.value
                });
            }
        }
    },

    abrirImpresionValeTraspaso: function(){
        //Abrir ruta de impresion
        if (this.props.mensajeImprimirValeTraspaso != "") {
            window.open(""+this.props.RutaImpresionValeTraspaso);
        }else{
            this.props.abrirAlerta("Traspaso", "Realizar un Traspaso para poder realizar la impresión");
        }
    },

    render: function() {

        const cellEditProp = {
            mode: "click",
            blurToSave: true,
            beforeSaveCell: this.onBeforeSaveCell,
            afterSaveCell: this.onAfterSaveCell
        };

        const selectRowProp = {
            mode: "checkbox",
            clickToSelect: true
        };

        const options = {
            deleteText: "Eliminar",
            handleConfirmDeleteRow: this.customConfirm,
            onDeleteRow: this.props.onDeleteRow
        };

        var disabled = this.props.disabledImpresionValeTraspaso;

        var valDescuento = {"display": "none"};
        if (this.props.permisoDescuento) {
            valDescuento = {"display": "block"};
        }

        // style={{"display": this.props.disabledImpresionValeTraspaso}}
        /*
        <OverlayTrigger placement="top" overlay={tooltipAnticipos}>
            <span onClick={this.props.abrirAnticipo} className="badge pull-right cursor" style={{"margin-left": "5px"}}><a><i aria-hidden="true" className="fa fa-info-circle"></i></a> Anticipos</span>
        </OverlayTrigger>
         */
        return ( 
            <div className="col-md-12">
                <div className="panel panel-default">
                    <div className="panel-heading">
                        {this.props.nomArticuloVentaPlural}
                        <OverlayTrigger placement="top" overlay={tooltipImpresionInformacion}>
                            <span className="badge pull-right cursor" style={{"margin-left": "5px", "color": "red", "display": "none"}}><a><i aria-hidden="true" className="fa fa-info-circle"></i></a> {this.props.mensajeImprimirValeTraspaso}</span>
                        </OverlayTrigger>
                        <OverlayTrigger placement="top" overlay={tooltipImpresionTraspaso}>
                            <span onClick={this.abrirImpresionValeTraspaso} className="badge pull-right cursor" style={{"margin-left": "5px", "display": "none"}}><a><i aria-hidden="true" className="fa fa-print"></i></a> Impresión Vale Traspaso</span>
                        </OverlayTrigger>
                        <OverlayTrigger placement="top" overlay={tooltipRealizarTraspaso}>
                            <span onClick={this.botonTraspaso} className="badge pull-right cursor" style={{"margin-left": "5px", "display": "none"}}><a><i aria-hidden="true" className="fa fa-exchange"></i></a> Realizar Traspaso</span>
                        </OverlayTrigger>
                        <OverlayTrigger placement="top" overlay={tooltipActualizarExistencias}>
                            <span onClick={this.botonActualizarExistencias} className="badge pull-right cursor" style={{"margin-left": "5px", "display": "none"}}><a><i aria-hidden="true" className="fa fa-info-circle"></i></a> Actualizar Existencias</span>
                        </OverlayTrigger>
                    </div>
                    <div className="panel-body panel-productos" id="panel-productos">
                        <div className="row">
                            <div className="col-md-8 centrar-contenido">
                            </div>
                            <div className="col-md-4 pull-right" style={valDescuento}>
                                <div className="input-group">
                                    <input id="txtDescuentoMasivo" className="form-control" onChange={this.cambioDescuentoMasivo} title="Descuento" placeholder="Descuento" value={this.state.txtDescuentoMasivo}></input>
                                    <span className="input-group-btn"><button type="button" className="btn btn-primary btn" onClick={this.botonDescuentoMasivo}><i className="fa fa-check" aria-hidden="true"></i> Aplicar Descuento</button></span>
                                </div>
                            </div>
                            <div className="row"></div>
                            <div className="col-md-12">
                                <div>
                                    <BootstrapTable data={ this.props.productos } hover={true} bordered={false} height='340px' deleteRow={true} selectRow={selectRowProp} options={options} cellEdit={ cellEditProp } ref='table'>
                                        <TableHeaderColumn dataField='NumRow' isKey={ true } width='30' editable={false} style={{"display": "none"}}>No.</TableHeaderColumn>
                                        <TableHeaderColumn dataField='stockid' width='120' editable={false}>Código</TableHeaderColumn>
                                        <TableHeaderColumn width='300' dataField='description' editable={false}>Descripción</TableHeaderColumn>
                                        <TableHeaderColumn dataField='available' width='60' editable={false} hidden={true}>Exis.</TableHeaderColumn>                    
                                        <TableHeaderColumn dataField='quantity' width='150'>Cant.</TableHeaderColumn>
                                        <TableHeaderColumn dataField='price' width='150' dataFormat={this.priceFormatter} editable={this.props.permisoPrecio} onClick={this.restar}>Precio</TableHeaderColumn>
                                        <TableHeaderColumn dataField='discount' width='60' dataFormat={this.discountFormatter} editable={this.props.permisoDescuento}>Desc.</TableHeaderColumn>
                                        <TableHeaderColumn dataField='subtotal' width='100' dataFormat={this.priceFormatter} editable={false} hidden={true}>SubTotal</TableHeaderColumn>
                                        <TableHeaderColumn dataField='iva' width='100' dataFormat={this.priceFormatter} editable={false} hidden={true}>Iva</TableHeaderColumn>
                                        <TableHeaderColumn dataField='total' width='150' dataFormat={this.priceFormatter} editable={false}>Total</TableHeaderColumn>
                                    </BootstrapTable>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

});

module.exports = TablaProductos;

/*
                <p>
                    <button className="btn btn-primary" onClick={this.botonTraspaso}>Realizar Traspaso</button>
                    <button className="btn btn-primary" onClick={this.abrirImpresionValeTraspaso} disabled={disabled} style={{"margin-left": "5px"}}>Impresion Vale Traspaso</button>
                    <font color="red">{this.props.mensajeImprimirValeTraspaso}</font>
                </p>
 */

/*
<p>
    <button className="btn btn-primary" onClick={this.botonTraspaso}>Realizar Traspaso</button>
    <font color="red" style={{"display": this.props.disabledImpresionValeTraspaso}}><a target="_blank" href={this.props.RutaImpresionValeTraspaso} className="btn btn-primary">Impresion Vale Traspaso</a>
    Imprimir Vale antes de Cotizar y/o Facturar</font>
</p>
*/