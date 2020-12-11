<?php 
class UsuariosModel extends ModeloBase{
	private $table;

	public function __construct($table){
		$this->table = "usuarios";
		parent::_construct($this-table);
	}

	public function getUnUsuario(){
		$query = "SELECT * FROM usuarios WHERE email = 'victor@victor.com'";
		$usuario = $this->ejecutarSql($query);
		return $usuario;
	}

}

?>