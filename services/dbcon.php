<?php

    require_once(__ROOT__ . "/config/db.php");

    $dbc = null;
    function conectar(){
        //pdo maneja objetos, abre y cierra, en automatico puede darse de baja       
        
        try{
        $dbc = new PDO(
            'mysql:host='.DBHOST.
            ';PORT='.DBPORT.
            ';dbname='.DBNAME,
            DBUSER,
            DBPASS,
            array(PDO::ATTR_PERSISTENT => false));
            
             echo "<script>console.log('conexion establecida');</script>";
             return $dbc;
        }
        //catch(tipo de exepcion variable donde se guarda)
        catch (PDOException $e) {
            echo "<srcipt>
            console.log(".$e->getMessage() . ")
            </script>";

            return null;
            //si hay un return en un if else o try catch, se debe un return en cada uno
        }
    }


    function desconectar(){
        $dbc = null;
        return $dbc;

    }

?>