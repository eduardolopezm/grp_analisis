var React = require('react');
import {FormattedNumber} from 'react-intl';

var carrito = React.createClass({

	calcularTotales: function () {
        var subtotal = 0;
        var iva = 0;
        var total = 0;
        var totalAnticipo = 0;

        for (var producto in this.props.productos) {
            if(this.props.productos.hasOwnProperty(producto)) {
                var sub = this.props.productos[producto].price * this.props.productos[producto].quantity;
                var sub_dis = (sub - (sub * (this.props.productos[producto].discount / 100)));
                subtotal += sub_dis;
            }
        }

        iva = subtotal * this.props.operacionIva;
        total = subtotal + iva;
        
        for (var anticipo in this.props.anticipoCliente) {
            if(this.props.anticipoCliente.hasOwnProperty(anticipo)) {
                totalAnticipo += Number(this.props.anticipoCliente[anticipo].MontoAnticipo);
            }
        }
        
        return {
            subtotal: subtotal,
            iva: iva,
            total: total,
            totalAnticipo: totalAnticipo
        }
    }, 

	render: function() {

		var articulos = this.props.productos.length;
		var cantidades = this.calcularTotales();
 
		return (
			<div>
				<div className="panel panel-default">
                    <div className="panel-heading">
                        {this.props.nomArticuloVentaPlural} <span className="badge pull-right">{articulos}</span>
                    </div>
                    <div className="panel-body">
                        <div className="col-md-12" style={{"display": "none"}}>
                            <div className="col-md-6 pull-right">
                                <FormattedNumber value={cantidades.totalAnticipo} style="currency" currency="USD" />
                            </div>
                            <div className="col-md-6 pull-right">
                                <label>Total Anticipo</label>
                            </div>
                        </div>
                        <div className="col-md-12">
                            <div className="col-md-6 pull-right">
                                <FormattedNumber value={cantidades.subtotal} style="currency" currency="USD" />
                            </div>
                            <div className="col-md-6 pull-right">
                                <label>SubTotal</label>
                            </div>
                        </div>
                        <div className="col-md-12" style={{"display": "none"}}>
                            <div className="col-md-6 pull-right">
                                 <FormattedNumber value={cantidades.iva} style="currency" currency="USD" />
                            </div>
                            <div className="col-md-6 pull-right">
                               <label>Iva</label>
                            </div>
                        </div>
                        <div className="col-md-12">
                            <div className="col-md-6 pull-right">
                                <FormattedNumber value={cantidades.total} style="currency" currency="USD" />
                            </div>
                            <div className="col-md-6 pull-right">
                                <label>Total</label>
                            </div>
                        </div>
                    </div>
                </div>
			</div>
		);
	}

});

module.exports = carrito;

/*<table className="table tabla-totales">
                            <tbody>
                                <tr>
                                    <td className="izquierda">SubTotal</td>
                                    <td className="numero"><FormattedNumber value={cantidades.subtotal} style="currency" currency="USD" /></td>
                                </tr>
                                <tr>
                                    <td className="izquierda">Iva</td>
                                    <td className="numero"><FormattedNumber value={cantidades.iva} style="currency" currency="USD" /></td>
                                </tr>
                                <tr>
                                    <td className="izquierda total-carrito">Total</td>
                                    <td className="numero total-carrito"><FormattedNumber value={cantidades.total} style="currency" currency="USD" /></td>
                                </tr>
                            </tbody>
                        </table>*/