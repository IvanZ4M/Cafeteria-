<?php
    require_once(__ROOT__ ."/services/dbcon.php");

//-----pos
class pos implements JsonSerializable{
    private $status;
    private $id_pos;
    
    function __construct($status = 1,$id_pos = 0){
        $this -> status = $status;
        $this -> id_pos= $id_pos;
    }
    //alta
    function altaPos(){
            
        $dbc = conectar();

        if($dbc != null){

        /*status???*/
        $query = "INSERT INTO pos
        (Status) values (
        ".$this->status.");";

        $consulta = $dbc -> prepare($query);
//execute hace los cambios
        if($consulta->execute()){
            return true;
        }
        else{
            return false;
        }
    }
    //no es necesario poner un try catch en el modelo ya que lo tenemos en la conexiocn por lo que aqui lo cambiamos a un if
    else{ 
        return null;
    }
 }
    //baja
    //cambios
    //

    function JsonSerialize(): mixed
    {
       return get_object_vars($this);
    }

}

//-----pos_com--------------muy posiblemente se elimine
class pos_com implements JsonSerializable{
    private $id_pos;
    private $id_combo;
    
    function __construct($id_pos = 0, $id_combo = 0){
        
        $this -> id_pos= $id_pos;
        $this -> id_combo = $id_combo;
    }
    //alta
   function altapos_com(){
            
        $dbc = conectar();

        if($dbc != null){

        //en orden para arreglar bien los campos
        $query = "INSERT INTO pos_com
        (ID_Combo,ID_Pos) values (
        ".$this->id_pos .",
        ".$this->id_combo . ");";

        $consulta = $dbc -> prepare($query);
//execute hace los cambios
        if($consulta->execute()){
            return true;
        }
        else{
            return false;
        }
    }
    //no es necesario poner un try catch en el modelo ya que lo tenemos en la conexiocn por lo que aqui lo cambiamos a un if
    else{ 
        return null;
    }
 }
    //baja
    //cambios
    //

    function JsonSerialize(): mixed
    {
       return get_object_vars($this);
    }

}

//-----combo
class combo implements JsonSerializable {
  
    private $precio;
    private $nombre;
    private $descripcion;
    //private $imagen_url;-----a decidir ya que no esta en la base de datos
    private $id_combo;

    function __construct($precio, $nombre, 
                        $descripcion, $imagen_url = "", $id_combo = 0) {

        $this->precio = $precio;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
       // $this->imagen_url = $imagen_url;
        $this->id_combo = $id_combo;
    }

    //---alta------............posiblemente solo se quede bajas y eliminemos los demas ya que manejamos esta tabla ocultamente
    function altaCombo(){
            
        $dbc = conectar();

        if($dbc != null){

        $query = "INSERT INTO combo 
        (Precio,Nombre,Productos) values (
        ".$this-> precio . ",
         \"".$this->nombre ."\",
        \"".$this->descripcion . "\");";

        $consulta = $dbc -> prepare($query);

        if($consulta->execute()){
            return true;
        }
        else{
            return false;
        }
    }
    else{ 
        return null;
    }
 }
    //---baja
    //---cambios
    //---

    function JsonSerialize(): mixed
    {
       return get_object_vars($this);
    }
    
}

//-----orden
class orden implements JsonSerializable {
    private $turno;
    private $estado;
    private $total;
    private $id_orden;
    private $id_pos;
    
       
        function __construct($id_orden = 0, $turno = 0, $id_pos = 0, $estado = 0, $total = 0.0) {
            $this->id_orden = $id_orden;
            $this->turno = $turno;
            $this->id_pos = $id_pos;
            $this->estado = $estado;
            $this->total = $total;
        }
    
        
    //alta
    function altaOrden(){
            
        $dbc = conectar();

        if($dbc != null){

        $query = "INSERT INTO orden 
        (Turno,ID_Pos,Estado,total) values (
        ".$this->turno .",
        ".$this->id_pos . ",
        \"".$this->estado . "\",
        ".$this->total . ");";

        $consulta = $dbc -> prepare($query);

        if($consulta->execute()){
            return true;
        }
        else{
            return false;
        }
    }
    
    else{ 
        return null;
    }
 }
    //baja
    //cambios
    //

    function JsonSerialize(): mixed
    {
       return get_object_vars($this);
    }

}

//-----ord_emp
class ord_emp implements JsonSerializable {
    private $id_emp;
    private $id_orden;
    private $fechahora;
    
        function __construct( $id_emp = 0,$id_orden = 0, $fechahora = "") {
            
            $this->id_emp = $id_emp;
            $this->id_orden = $id_orden;
            $this->fechahora = $fechahora;
            
        }
    
        
    //alta
    function altaOrd_emp(){
            
        $dbc = conectar();

        if($dbc != null){

        
        $query = "INSERT INTO ord_emp 
        (ID_Empleado,ID_Orden,FechaHora
        ) values (
        ".$this->id_emp .",
        ".$this->id_orden . ",
        \"".$this->fechahora . "\");";

        $consulta = $dbc -> prepare($query);

        if($consulta->execute()){
            return true;
        }
        else{
            return false;
        }
    }
    
    else{ 
        return null;
    }
 }
    //baja
    //cambios
    //

    function JsonSerialize(): mixed
    {
       return get_object_vars($this);
    }

}

//-----empleado
class empleado implements JsonSerializable{
       
    private $nombre;
    private $apat;
    private $amat;
    private $id_puesto;
    private $id_empleado;

    function __construct( $nombre, $apat, $amat, $id_puesto = 0, $id_empleado = 0){

        $this -> nombre = $nombre;
        $this -> apat = $apat;
        $this -> amat = $amat;
        $this -> id_puesto = $id_puesto;
        $this -> id_empleado = $id_empleado;
    }
    

     //alta
     function altaEmpleado(){
            
        $dbc = conectar();

        if($dbc != null){

        
        $query = "INSERT INTO empleados 
        (ID_Puesto,Nombre,AP,AM,
        ) values (
        \"".$this->nombre . "\",
        ".$this->id_puesto .",
        \"".$this->apat . "\",
        \"".$this->amat . "\");";

        $consulta = $dbc -> prepare($query);
        
        if($consulta->execute()){
            return true;
        }
        else{
            return false;
        }
    }
    else{ 
        return null;
    }
 }
    //baja
    //cambios
    //
     function JsonSerialize(): mixed
     {
        return get_object_vars($this);
       //json= json_encode(objetos);
     }

    
}

//-----puesto
class puesto implements JsonSerializable{
    private $puesto;
    private $id_puesto;
    
    function __construct($puesto = 1,$id_puesto = 0){
        $this -> puesto = $puesto;
        $this -> id_puesto= $id_puesto;
    }
    //alta
    function altaPuesto(){
            
        $dbc = conectar();

        if($dbc != null){

       $query = "INSERT INTO puesto 
        (puesto) values (
        \"".$this->puesto ."\");";

        $consulta = $dbc -> prepare($query);

        if($consulta->execute()){
            return true;
        }
        else{
            return false;
        }
    }
    else{ 
        return null;
    }
 }
    //baja
    //cambios
    //

    function JsonSerialize(): mixed
    {
       return get_object_vars($this);
    }

}



  

    
?>