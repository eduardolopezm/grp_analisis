var React = require('react')
var Modal = require('react-bootstrap').Modal
var Button = require('react-bootstrap').Button

var update = require('react-addons-update');
var ReactBsTable = require('react-bootstrap-table');
var BootstrapTable = ReactBsTable.BootstrapTable;
var TableHeaderColumn = ReactBsTable.TableHeaderColumn;
////
import {FormattedNumber} from 'react-intl';
import ComboGeneral from './combo-general';
import TablaTraspaso from './tabla-traspaso';

import BuscarProductos from './buscar-productos';

import axios from 'axios';

var ModalTraspaso = React.createClass({

    LimpiarCotizadorRapido: function () {
        this.props.parent.setState({
            productosCotizadorRapido: [],
            NumRow: 1
        });
    },

    seleccionCotizadorRapido : function (event) {        
        //console.log("seleccionCotizadorRapido: "+event.target.value);
        //Actualizar datos de array
        var value = event.target.value;
        var datos = value.split("/-/");

        var stockid = datos[0];
        var precio = datos[1];

        var row_index = 0;

        var obj = this.props.productosCotizadorRapido.filter(function(element, index, array) {
            if(element.stockid === stockid) {
                row_index = index;
            }
            return element;
        });

        //operaciones a mostrar tabla por producto
        var updated_row = update(this.props.productosCotizadorRapido[row_index], {
            price: {
                $set: precio
            },
            priceFormat: {
                $set: precio
            },
        });

        var newRowData = update(this.props.productosCotizadorRapido, {
            $splice: [[row_index, 1, updated_row]]
        });

        this.props.parent.setState({
            productosCotizadorRapido: newRowData
        });

        this.props.agregarProducto(updated_row, 'productosCotizadorRapidoSeleccion');
        console.log("productosCotizadorRapidoSeleccion: "+JSON.stringify(this.props.parent.state.productosCotizadorRapidoSeleccion));
        //console.log( "Datos CotizadorRapido Ppdate: " + JSON.stringify(this.props.parent.state.productosCotizadorRapido) );
    },

    crearTablaCotizadorRapido: function() { 
        //console.log("crearTablaCotizadorRapido");
        //Recorrer datos mostrar
        var Header = [];
        var Body = [];
        var Title = 0;
        var datos = this.props.parent.state.productosCotizadorRapido;
        for (var info in datos) {
            //Recorrer productos
            var datosList = datos[info].listasPrecio;
            var BodyList = [];
            for (var infoList in datosList) {
                //Recorrer listas precios
                if (Title == 0) {
                    //Poner solo una vez el titulo
                    Header.push( <th><b>{datosList[infoList].sales_type}</b></th> );
                }
                var valor = datos[info].stockid + "/-/" + datosList[infoList].price;
                BodyList.push( <td> <button type="button" className="btn btn-default" value={valor} onClick={this.seleccionCotizadorRapido}>${datosList[infoList].price}</button> </td> );
            }
            //Agregar informacion
            Body.push( 
                <tr>
                    <td>{datos[info].stockid}</td>
                    <td>{datos[info].description}</td>
                    {BodyList}
                </tr>
            );

            Title = 1;
        }

        var table = <table className="table table-hover">
                        <thead>
                            <tr>
                                <th><b>Código</b></th>
                                <th><b>Descripción</b></th>
                                {Header}
                            </tr>
                        </thead>
                        <tbody>
                            {Body}
                        </tbody>
                    </table>;

        this.props.parent.setState({
            TablaCotizadorRapido: table
        });
    },

    priceFormatter: function (cell, row){
        return <FormattedNumber value={cell} style="currency" currency="USD" />;
    },

    discountFormatter: function (cell, row){
        return  cell + ' %';
    },

    onAfterSaveCell: function (row, cellName, cellValue) { 

        var row_index = 0;

        var obj = this.props.productosCotizadorRapidoSeleccion.filter(function(element, index, array) {
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
        var updated_row = update(this.props.productosCotizadorRapidoSeleccion[row_index], {
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

        var newRowData = update(this.props.productosCotizadorRapidoSeleccion, {
            $splice: [[row_index, 1, updated_row]]
        });

        this.props.parent.setState({
            productosCotizadorRapidoSeleccion: newRowData 
        });
    },

    onBeforeSaveCell: function (row, cellName, cellValue) {
        if (cellValue < 0) {
            //si no es positivo
            return false;
        }

        if (cellName == "discount") {
            //si la columna es descuento
            if (cellValue <= this.props.descuentoMaximo) {
                if (cellValue < 0) {
                    //si no es positivo
                    return false;
                }else{
                    //deja poner descuento
                    return true;
                }
            }else{
                return false;
            }
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
        const dropRowKeysStr = dropRowKeys.join(',');
        if (confirm('¿Estas seguro que quieres eliminar los '+this.props.nomArticuloVenta+' seleccionados?')) {
            
            next();
        }
    },

    calcularTotales: function () {
        var subtotal = 0;
        var iva = 0;
        var total = 0;
        for (var producto in this.props.productosCotizadorRapidoSeleccion) {
            if(this.props.productosCotizadorRapidoSeleccion.hasOwnProperty(producto)) {
                var sub = this.props.productosCotizadorRapidoSeleccion[producto].price * this.props.productosCotizadorRapidoSeleccion[producto].quantity;
                var sub_dis = (sub - (sub * (this.props.productosCotizadorRapidoSeleccion[producto].discount / 100)));
                subtotal += sub_dis;
            }
        }

        iva = subtotal * 0.16;
        total = subtotal + iva;

        return {
            subtotal: subtotal,
            iva: iva,
            total: total
        }
    }, 

    onChangeDescripcionCotizadorRapido: function (event) {
        this.props.parent.setState({
            DescripcionCotizadorRapido: event.target.value 
        });
        console.log("DescripcionCotizadorRapido: "+this.props.parent.state.DescripcionCotizadorRapido);
    },

    BuscarProductosCotizadorRapido: function () {
        console.log("BuscarProductosCotizadorRapido");
        var self = this;

        if(self.props.parent.state.DescripcionCotizadorRapido.length > 3) {
            if(self.props.locationname == "" || self.props.locationname == ""){
                if (self.props.mensajeUnidadAlmacen == 0) {
                    //mostrar mensaje
                    self.props.parent.setState({
                        mensajeUnidadAlmacen: 1
                    });
                    //si no selecciono unidad de negocio o almacen
                    var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                    var body = "Seleccionar "+self.props.nomUniNegVenta+" y/o "+self.props.nomAlmacenVenta+" para poder seleccionar un "+self.props.nomArticuloVenta;
                    self.props.abrirAlerta(title, body);
                }
            }else{
                self.props.parent.setState({
                    mensajeUnidadAlmacen: 0
                });

                //obtener si existe sesion, si no tiene sesion termina el proceso
                self.props.obtenerSession();
                
                axios.get('./../mvc/api/v1/getproducts/' + self.props.parent.state.DescripcionCotizadorRapido + '/' + self.props.tagref + '/'+ self.props.location + '/MXN/' + self.props.typeabbrev)
                .then(function (response) {

                    if(response.data.success) {
                        var datos = response.data.data;
                        //console.log( "Datos CotizadorRapido: " + JSON.stringify(datos) );
                        self.LimpiarCotizadorRapido(); //Limpiar array
                        for (var info in datos) {
                            self.props.agregarProducto(datos[info], 'productosCotizadorRapido');
                        }
                        self.crearTablaCotizadorRapido(); //Crear tabla a mostrar
                    }
                })
                .catch(function (error) { 
                    // console.log(error);
                });
            }
        }
    },

    render: function() {

        var transparent = "rgba(255,255,255,0.5)";

        var disabled = this.props.btnAlertaAceptar;

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
            onDeleteRow: this.props.onDeleteRowCotizadorRapidoSeleccion
        };

        var disabled = this.props.disabledImpresionValeTraspaso;

        var articulos = this.props.productosCotizadorRapidoSeleccion.length;
        var cantidades = this.calcularTotales();

        return (

            <Modal show={this.props.show} onHide={this.props.hideModal} bsSize="large" backdrop={false} style={{backgroundColor: transparent}}>
                <Modal.Header closeButton>
                    <Modal.Title>Cotizador Rápido</Modal.Title>
                </Modal.Header>
                <Modal.Body className="cuerpo-modal-pago">
                    <div className="panel panel-default">
                        <div className="panel-body">
                            <div className="row">
                                <div className="col-md-12">
                                    <div className="col-md-2">
                                        <label>SubTotal</label>
                                        <br/>
                                        <label>Iva</label>
                                        <br/>
                                        <label>Total</label>
                                    </div>
                                    <div className="col-md-2">
                                        <FormattedNumber value={cantidades.subtotal} style="currency" currency="USD" />
                                        <br/>
                                        <FormattedNumber value={cantidades.iva} style="currency" currency="USD" />
                                        <br/>
                                        <FormattedNumber value={cantidades.total} style="currency" currency="USD" />
                                    </div>
                                </div>
                            </div>
                            <div className="row">
                                <div className="col-md-12">
                                    <div className="col-md-6">
                                        <textarea id="Descripcion" className="form-control" rows="1" onChange={this.onChangeDescripcionCotizadorRapido} value={this.props.parent.state.DescripcionCotizadorRapido}></textarea>
                                    </div>
                                    <div className="col-md-6">
                                        <button className="btn btn-primary" type="button" onClick={this.BuscarProductosCotizadorRapido}>Buscar</button>
                                    </div>
                                </div>
                            </div>
                            <div className="row">
                                <div className="col-md-12">
                                    {this.props.parent.state.TablaCotizadorRapido}
                                </div>
                            </div>
                            <div className="row">
                                <div className="col-md-12">
                                    <BootstrapTable data={ this.props.productosCotizadorRapidoSeleccion } hover={true} bordered={false} height='340px' deleteRow={true} selectRow={selectRowProp} options={options} cellEdit={ cellEditProp } ref='table'>
                                        <TableHeaderColumn dataField='NumRow' isKey={ true } width='25' editable={false} style={{"display": "none"}}>No.</TableHeaderColumn>
                                        <TableHeaderColumn dataField='stockid' width='130' editable={false}>Codigo</TableHeaderColumn>
                                        <TableHeaderColumn width='300' dataField='description' editable={false}>Descripcion</TableHeaderColumn>
                                        <TableHeaderColumn dataField='available' width='60' editable={false}>Exis.</TableHeaderColumn>                    
                                        <TableHeaderColumn dataField='quantity' width='50'>Cant.</TableHeaderColumn>
                                        <TableHeaderColumn dataField='price' width='100' dataFormat={this.priceFormatter} editable={this.props.permisoPrecio} onClick={this.restar}>Precio</TableHeaderColumn>
                                        <TableHeaderColumn dataField='discount' width='60' dataFormat={this.discountFormatter} editable={true}>Desc.</TableHeaderColumn>
                                        <TableHeaderColumn dataField='subtotal' width='100' dataFormat={this.priceFormatter} editable={false}>SubTotal</TableHeaderColumn>
                                        <TableHeaderColumn dataField='iva' width='100' dataFormat={this.priceFormatter} editable={false}>Iva</TableHeaderColumn>
                                        <TableHeaderColumn dataField='total' width='100' dataFormat={this.priceFormatter} editable={false}>Total</TableHeaderColumn>
                                    </BootstrapTable>
                                </div>
                            </div>
                            <div className="row">
                                <div className="col-md-12">
                                    <h6><button className="btn btn-default info-button" onClick={this.props.agregarProductosCotizadorRapido} type="button">Agregar {this.props.nomArticuloVentaPlural} Seleccionados</button></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </Modal.Body>
                <Modal.Footer>
                    <Button onClick={this.props.hideModal} disabled={disabled}>Aceptar</Button>
                </Modal.Footer>
            </Modal>
        );
    }
});
 
module.exports = ModalTraspaso 

/*
handleChange: function (event) {
    if (event.target.value.length > 3) {
        console.log("buscar-productos");
    }
},
<input type="text" className="input-group-btn form-control" onChange={this.handleChange} />

<span className="input-group-btn"><button className="btn btn-primary" type="button">Productos:</button></span>
<BuscarProductos 
    agregarProducto={this.props.agregarProducto} 
    tagref={this.props.tagref} 
    tagrefname={this.props.tagrefname} 
    locationname={this.props.locationname} 
    location={this.props.location} 
    mensajeUnidadAlmacen={this.props.mensajeUnidadAlmacen} 
    abrirAlerta={this.props.abrirAlerta} 
    obtenerSession={this.props.obtenerSession} 
    parent={this.props.parent}
    CotizadorRapido="1"
    LimpiarCotizadorRapido={this.LimpiarCotizadorRapido}
    crearTablaCotizadorRapido={this.crearTablaCotizadorRapido} />

<BootstrapTable data={ this.props.productosCotizadorRapido } hover={true} bordered={false} height='340px' deleteRow={true} selectRow={selectRowProp} options={options} cellEdit={ cellEditProp } ref='table'>
    <TableHeaderColumn dataField='NumRow' isKey={ true } width='25' editable={false} style={{"display": "none"}}>No.</TableHeaderColumn>
    <TableHeaderColumn dataField='stockid' width='130' editable={false}>Codigo</TableHeaderColumn>
    <TableHeaderColumn width='300' dataField='description' editable={false}>Descripcion</TableHeaderColumn>
    <TableHeaderColumn dataField='available' width='60' editable={false}>Exis.</TableHeaderColumn>                    
    <TableHeaderColumn dataField='quantity' width='50'>Cant.</TableHeaderColumn>
    <TableHeaderColumn dataField='price' width='100' dataFormat={this.priceFormatter} editable={this.props.permisoPrecio} onClick={this.restar}>Precio</TableHeaderColumn>
    <TableHeaderColumn dataField='discount' width='60' dataFormat={this.discountFormatter} editable={true}>Desc.</TableHeaderColumn>
    <TableHeaderColumn dataField='subtotal' width='100' dataFormat={this.priceFormatter} editable={false}>SubTotal</TableHeaderColumn>
    <TableHeaderColumn dataField='iva' width='100' dataFormat={this.priceFormatter} editable={false}>Iva</TableHeaderColumn>
    <TableHeaderColumn dataField='total' width='100' dataFormat={this.priceFormatter} editable={false}>Total</TableHeaderColumn>
</BootstrapTable>
*/