<?php 
//se instancia un objeto llamando a la clase
    require_once(__ROOT__."/models/mcdonalds.class.php");
    
    //pos
    $req = $_REQUEST['req'];

     $selPos = new pos(
        $_REQUEST ['status'],
        //if 
        isset($_REQUEST['status'])?$_REQUEST ['status']:1
     );

    switch($req){
        case "alta":
            $res = $selPos->altaPos();
            break;
        case "baja":
            break;
        case "cambios":
            break;
        case "mostrar":
            break;

        default:
        echo"Metodo incorrecto ";
        header("refresh:5; url=".__ROOT__. "/views/pos.php");
        break;
    }

    /*-------por si se llega a necesitar en un futuro
      
    //combo
      
        $_REQUEST ['precio'],
        $_REQUEST['nombre'],
        $_REQUEST ['descripcion'],


    //orden

        $_REQUEST ['turno'],
        $_REQUEST['estado'],
        $_REQUEST ['total'],
        isset($_REQUEST['estado'])?$_REQUEST ['estado']:1
    
        ..::si sale error checar mcdonalds.class, en __construct estado=0::..
       



        //empleado

        $_REQUEST ['nombre'],
        $_REQUEST['apat'],
        $_REQUEST ['amat'],
        
       


        //puesto

        $_REQUEST ['puesto'],
        


    
    
    
    
    */

?>