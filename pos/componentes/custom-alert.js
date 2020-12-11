var React = require('react')
var Modal = require('react-bootstrap').Modal
var Button = require('react-bootstrap').Button

var CustomAlert = React.createClass({

	render: function() {
    
    var transparent = "rgba(255,255,255,0.5)";

    var disabled = this.props.btnAlertaAceptar;

    //<Modal.Header closeButton>

		return (
			<Modal show={this.props.show} onHide={this.props.hideModal} bsSize={this.props.bsSize} backdrop={false} style={{backgroundColor: transparent}}>
      		<Modal.Header>
        		<Modal.Title>{this.props.title}</Modal.Title>
      		</Modal.Header>
      		<Modal.Body>
                {this.props.body}
      		</Modal.Body>
      		<Modal.Footer>
                <Button onClick={this.props.hideModal} disabled={disabled}>Aceptar</Button>
      		</Modal.Footer>
    	</Modal>
		);
	}
})

module.exports = CustomAlert  