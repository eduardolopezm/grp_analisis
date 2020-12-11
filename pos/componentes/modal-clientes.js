var React = require('react')
var Modal = require('react-bootstrap').Modal
var Button = require('react-bootstrap').Button

var ClientesModal = React.createClass({

	close: function() {
		alert('accion del boton cerrar');
	},

	render: function() {
		return (
			<Modal {...this.props} >
          		<Modal.Header closeButton>
            		<Modal.Title>Nuevo Cliente</Modal.Title>
          		</Modal.Header>
          		<Modal.Body>

                    <div className="row">
                        <div className="col-md-12">
                            <div className="row">
                                <div className="col-md-2">
                                    <label htmlFor="cliente_codigo">Codigo</label>
                                </div>
                                <div className="col-md-3">
                                    <input type="text" className="form-control" id="cliente_codigo" placeholder="00001" />
                                </div>
                                <div className="col-md-2">
                                    <label htmlFor="cliente_nombre">Nombre</label>
                                </div>
                                <div className="col-md-3">
                                    <input type="text" className="form-control" id="cliente_nombre" placeholder="Nombre del Cliente" />
                                </div>
                                <div className="col-md-2">
                                    <button className="btn btn-default"><i className="fa fa-search" aria-hidden="true"></i></button>
                                </div> 
                            </div>  
                        </div>
                    </div>
          		</Modal.Body>
          		<Modal.Footer>
            		<Button onClick={this.close}>Cerrar</Button>
          		</Modal.Footer>
        	</Modal>
		);
	}
})

module.exports = ClientesModal 