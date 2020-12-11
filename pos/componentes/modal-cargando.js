var React = require('react')
var Modal = require('react-bootstrap').Modal
var Button = require('react-bootstrap').Button

var ModalCargando = React.createClass({

  render: function() {
    
    var transparent = "rgba(255,255,255,0.5)";
    var transparent2 = "none";
    //<img src="assets/img/loading.gif" width="100" height="70"/>
    //backdrop={false}
    return (
      <Modal show={this.props.show} bsSize="sm"  style={{backgroundColor: transparent}}>
          <Modal.Body style={{background: transparent2}}>
                {this.props.body}
          </Modal.Body>
      </Modal>
    );
  }
})

module.exports = ModalCargando