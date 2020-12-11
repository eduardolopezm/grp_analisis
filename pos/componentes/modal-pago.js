var React = require('react')
var Modal = require('react-bootstrap').Modal
var Button = require('react-bootstrap').Button

import {FormattedNumber} from 'react-intl';
import Moneda from './moneda';
import ContenedorMonedas from './contenedor-monedas';
import ComboGeneral from './combo-general';

import Multiselect from './multi-select';

var contenedorMonedas;

var ModalPago = React.createClass({

    getInitialState: function() {
        return {
            payment: 0
        };
    },
    
    getPayment: function (payment) {
        this.setState({
            payment:  payment
        });
    },

    calcularTotales: function () {
        var subtotal = 0;
        var iva = 0;
        var total = 0;
        for (var producto in this.props.productos) {
            if(this.props.productos.hasOwnProperty(producto)) {
                var sub = this.props.productos[producto].price * this.props.productos[producto].quantity;
                var sub_dis = (sub - (sub * (this.props.productos[producto].discount / 100)));
                subtotal += sub_dis;
            }
        }

        iva = subtotal * this.props.operacionIva;
        total = subtotal + iva;

        this.props.parent.state.totalGeneralIngreso = total;

        return {
            subtotal: subtotal,
            iva: iva,
            total: total
        }
    },

    seleccionarMetodosPago: function(MetodosCode, MetodosName){
        //actualizar metodo de pago
        //console.log("Metodos modal-pago code: "+MetodosCode+" - Name: "+MetodosName);
        this.props.parent.setState({
            paymentterm: MetodosCode,
            paymentmethod: MetodosName 
        });
        this.props.parent.state.paymentterm = MetodosCode;
        this.props.parent.state.paymentmethod = MetodosName;
        //console.log("Var: "+this.props.parent.state.paymentterm+" - "+this.props.parent.state.paymentmethod);
    },

    seleccionarReferencia: function(event) {
        //console.log("input referencia value: "+event.target.value);
        this.props.parent.state.paymentReferencia = event.target.value;
        //console.log("Referencia: "+this.props.parent.state.paymentReferencia);
    },

    seleccionarTipoComprobante: function (value, label){
        //si cambia el almacen
        //console.log("entro seleccionarTipoComprobante value: "+value);
        this.props.parent.setState({
            tipoComprobante: value,
            tipoComprobanteName: label 
        });
        this.props.parent.state.tipoComprobante = value;
        this.props.parent.state.tipoComprobanteName = label;
        //console.log("tipoComprobante: "+this.props.parent.state.tipoComprobante);
    },

    seleccionarUsoCFDI: function (value, label){
        //si cambia el almacen
        //console.log("entro seleccionarUsoCFDI value: "+value);
        this.props.parent.setState({
            usoCFDI: value,
            usoCFDIName: label 
        });
        this.props.parent.state.usoCFDI = value;
        this.props.parent.state.usoCFDIName = label;
        //console.log("usoCFDI: "+this.props.parent.state.usoCFDI);
    },

    seleccionarMetodoPago: function (value, label){
        //si cambia el almacen
        //console.log("entro seleccionarMetodoPago value: "+value);
        this.props.parent.setState({
            metodoPago: value,
            metodoPagoName: label 
        });
        this.props.parent.state.metodoPago = value;
        this.props.parent.state.metodoPagoName = label;
        //console.log("metodoPago: "+this.props.parent.state.metodoPago);
    },

    seleccionarClaveConfirmacion: function(event) {
        //console.log("input clave Confirmacion value: "+event.target.value);
        this.props.parent.state.claveConfirmacion = event.target.value;
        //console.log("Clave Confirmacion: "+this.props.parent.state.claveConfirmacion);
    },

	render: function() { 

        const selectValueCiontacto =  '';
        const selectLabelContacto = '';
        const toolTipTextContacto = 'Cambiar Contacto';
        const apiContacto = {
            name: 'Contactos',
            func: 'getsalesman',
            value: 'salesmancode',
            label: 'salesmanname',
        };

        const toolTipTextTipoComprobante = 'Cambiar Tipo de Comprobante';
        const apiTipoComprobante = {
            name: 'Tipo de Comprobante',
            func: 'getTipoComprobante',
            value: 'tipoComprobante',
            label: 'descripcion',
        };

        const toolTipTextUsoCFDI = 'Cambiar Uso de CFDI';
        const apiUsoCFDI = {
            name: 'Uso de CFDI',
            func: 'getUsoCFDI',
            value: 'usoCFDI',
            label: 'descripcion',
        };

        const toolTipTextMetodoPago = 'Cambiar Metodo de Pago';
        const apiMetodoPago = {
            name: 'Metodo de Pago',
            func: 'getMetodoPago',
            value: 'metodoPago',
            label: 'descripcion',
        };

        var cantidades = this.calcularTotales();

        var cambio = this.state.payment - cantidades.total;

        var transparent = "rgba(255,255,255,0.5)"; 

		return (
			<Modal show={this.props.show} onHide={this.props.hideModal} bsSize="large" backdrop={false} style={{backgroundColor: transparent}}>
          		<Modal.Header closeButton>
            		<Modal.Title>Pagar</Modal.Title>
          		</Modal.Header>
          		<Modal.Body className="cuerpo-modal-pago">

                    <div className="panel panel-default ocultar">
                        <div className="panel-body">
                            <div className="row">
                                <div className="col-md-12">
                                    <ComboGeneral selectValue={selectValueCiontacto} selectLabel={selectLabelContacto} toolTipText={toolTipTextContacto} obtenerSession={this.props.obtenerSession} api={apiContacto} />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="panel panel-default ocultar">
                        <div className="panel-body">
                            <div className="row">
                                <div className="col-md-12">
                                    <ContenedorMonedas getPayment={this.getPayment} totalAPagar={cantidades.total} currencydenominations={this.props.currencydenominations} />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="panel panel-default">
                        <div className="panel-body">
                            
                                <div className="col-md-12">
                                    <label htmlFor="vendedor" className="control-label">Forma de Pago</label>
                                    <Multiselect seleccionarMetodosPago={this.seleccionarMetodosPago} />
                                </div>
                                <div className="row">
                                </div>
                                <div className="col-md-12">
                                    <br/>
                                    <label htmlFor="vendedor" className="control-label">Referencia</label>
                                    <input type="text" className="form-control" title="Referencia" placeholder="Referencia" id="txtReferencia" name="txtReferencia" onChange={this.seleccionarReferencia} />
                                </div>
                                <div className="row">
                                </div>
                                <div className="col-md-3">
                                    <br/>
                                    <label htmlFor="vendedor" className="control-label">Tipo de Comprobante</label>
                                </div>
                                <div className="col-md-3">
                                    <br/>
                                    <ComboGeneral 
                                        selectValue={this.props.parent.state.tipoComprobante} 
                                        selectLabel={this.props.parent.state.tipoComprobanteName} 
                                        toolTipText={toolTipTextTipoComprobante} 
                                        seleccionarTipoComprobante={this.seleccionarTipoComprobante} 
                                        obtenerSession={this.props.obtenerSession} 
                                        api={apiTipoComprobante} />
                                </div>
                                <div className="col-md-3">
                                    <br/>
                                    <label htmlFor="vendedor" className="control-label">Uso de CFDI</label>
                                </div>
                                <div className="col-md-3">
                                    <br/>
                                    <ComboGeneral 
                                        selectValue={this.props.parent.state.usoCFDI} 
                                        selectLabel={this.props.parent.state.usoCFDIName} 
                                        toolTipText={toolTipTextUsoCFDI} 
                                        seleccionarUsoCFDI={this.seleccionarUsoCFDI} 
                                        obtenerSession={this.props.obtenerSession} 
                                        api={apiUsoCFDI} />
                                </div>
                                <div className="row">
                                </div>
                                <div className="col-md-3">
                                    <br/>
                                    <label htmlFor="vendedor" className="control-label">Método de Pago</label>
                                </div>
                                <div className="col-md-3">
                                    <br/>
                                    <ComboGeneral 
                                        selectValue={this.props.parent.state.metodoPago} 
                                        selectLabel={this.props.parent.state.metodoPagoName} 
                                        toolTipText={toolTipTextMetodoPago} 
                                        seleccionarMetodoPago={this.seleccionarMetodoPago} 
                                        obtenerSession={this.props.obtenerSession} 
                                        api={apiMetodoPago} />
                                </div>
                                <div className="col-md-3">
                                    <br/>
                                    <label htmlFor="vendedor" className="control-label">Clave Confirmación</label>
                                </div>
                                <div className="col-md-3">
                                    <br/>
                                    <input type="text" className="form-control" title="Clave Confirmación" placeholder="Clave Confirmación" id="txtClaveConfirmacion" name="txtClaveConfirmacion" onChange={this.seleccionarClaveConfirmacion} />
                                </div>
                            
                        </div>
                    </div>

                    <div className="row">
                        <div className="col-md-6">
                            <div className="panel panel-default">
                                <div className="panel-body">
                                    <div className="row pago-acciones-fila">
                                        <div className="col-md-12">
                                            <button className="btn btn-primary info-button" onClick={this.props.guardarRemision} type="button" disabled={this.props.parent.state.btnFacturaDisabled}>Pagar</button>
                                        </div>
                                    </div>
                                    <div className="row pago-acciones-fila">
                                        <div className="col-md-12">
                                            <button className="btn btn-primary info-button ocultar" onClick={this.props.guardarFactura} type="button" disabled={this.props.parent.state.btnFacturaDisabled} >Pagar y Facturar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="col-md-6">
                            <div className="panel panel-default"> 
                                <div className="panel-body">
                                    <table className="table tabla-totales">
                                        <tbody>
                                            <tr>
                                                <td className="izquierda">Total a Pagar</td>
                                                <td className="numero"><FormattedNumber value={cantidades.total} style="currency" currency="USD" /></td>
                                            </tr>
                                            <tr style={{"display": "none"}}>
                                                <td className="izquierda border_bottom">Su Pago</td>
                                                <td className="numero border_bottom"><FormattedNumber value={this.state.payment} style="currency" currency="USD" /></td>
                                            </tr>
                                            <tr style={{"display": "none"}}>
                                                <td className="izquierda total-carrito">Cambio</td>
                                                <td className="numero total-carrito"><FormattedNumber value={cambio} style="currency" currency="USD" /></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <img src="assets/img/loading.gif" width="200" height="150" className="imgCargando" style={{"display": this.props.parent.state.imgCargandoMostrar}}/>
                    
          		</Modal.Body>
        	</Modal>
		);
	}
})

module.exports = ModalPago


//multi select con checkbox
/*
var MultiselectReact = require('react-select-multiple');
<div className="col-md-12">
    <MultiselectReact selectors={true} selectAllLabel='Selecionar Todos' list={this.state.datosPayement}/>
</div>
*/


