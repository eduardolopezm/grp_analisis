<?php
 
class Usuario extends EntidadBase{
	private $id, $nombre, $apellido, $email, $password;

	public function __construct($table){
		$table = "usuarios";
		parent::__construct ($table);
	}	


	public function getId(){
		return $this->id;
	}	
	public function getNombre(){
		return $this->nombre;
	}
	public function getApellido(){
		return $this->apellido;
	}
	public function getEmail(){
		return $this->email;
	}
	public function getPassword(){
		return $this->password;
	}

	public function setId($id){
		$this->id = $id;
	}
	public function setNombre($nombre){
		$this->nombre = $nombre;
	}
	public function setApellido($apellido){
		$this->apellido = $apellido;
	}
	public function setEmail($email){
		$this->email = $email;
	}
	public function setPassword($password){
		$this->password = $password;
	}


	public function save(){
		$query="INSERT INTO usuarios(id, nombre, apellido, email, password)"
			. "VALUES (NULL,"
			. "'" . $this->nombre . "',"
			. "'" . $this->apellido . "',"
			. "'" . $this->email . "',"
			. "'" . $this->password . "',"
			. ");";
		$save = $this->db()->query($query);
		return $save;
	}

}