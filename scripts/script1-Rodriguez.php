<?php

/**
 * Este documento ha salido de una tarea de la asignatura DWES hecha por mi el curso 21/22.
 * Este script contiene 2 funciones usadas para crear y buscar dentro de una base de datos.
 * 
 * En este script se usa la etiqueta link y copyright
 * 
 * @author Juan Enrique Rodriguez Pretel
 * @version 1.0.1
 */


/**
 * Crea una reserva nueva usando los datos pasados por el array. Primero se 
 * asegura de que no se solapan sus horarios con los de otras reservas.
 * 
 * @param type $reserva array con los datos user_id, zona_id,fecha, inicio y fin
 * @return int valor numérico que indica el estado final de la operación
 * @copyright Juan Enrique Rodriguez Pretel
 * @version 1.0.1
 */
function crearReserva($reserva){
   
    // filtramos los datos
    $user= filter_var($reserva->user, FILTER_VALIDATE_INT);
    
    $zona= filter_var($reserva->zona, FILTER_VALIDATE_INT);
    
    
    //controlamos las horas------------------------------------------------------------------------------------------
    $inicio= filter_var($reserva->tramo->horaInicio, FILTER_VALIDATE_REGEXP,array("options" => ['regexp'=>self::HREGEX]));
    $fin= filter_var($reserva->tramo->horaFin, FILTER_VALIDATE_REGEXP,array("options" => ['regexp'=>self::HREGEX]));
    $horas=true;
    
    //Verificamos que la hora de inicio sea anterior a la hora de fin
    if ($inicio && $fin)
    {
        list($h,$m)=explode(":",$inicio);
        $inicioAux=$h*60+$m;
        list($h,$m)=explode(":",$fin);
        $finAux=$h*60+$m;
        if ($inicioAux>$finAux || $finAux>1439)
        {
            $horas=true;

        }else{
            $horas= false;
        }
    }
    
    
    //controlamos la fecha-----------------------------------------------------------------------------------------------
    $fecha= filter_var($reserva->fecha, FILTER_VALIDATE_REGEXP,array("options" => ['regexp'=> self::FREGEX]));
    
    //Comprobamos la fecha con checkdate
    $matches=[];
    if (is_string($fecha) && preg_match(self::FREGEX,$fecha,$matches))
    {
        list ($f,$d,$m,$a)=$matches;
        if (checkdate($m,$d,$a))
        {
            //Si la fecha es correcta cambiamos su formato al formato de la base de datos
            $fechaAux=new DateTime();
            $fechaAux->setDate($a,$m,$d);
            //Almacenamos la fecha en el formato que necesita la base de datos--------Importante
            $fecha=$fechaAux->format(self::FORMATO_FECHA_BD);
        } else {
            $fecha=false;
        }
    }
    else
    {
        $fecha=false;
    }
    
    //controlamos que los datos son existen
    if(is_int($user) && is_int($zona)){
        
        //pasamos a las operaciones y resultados
        if(is_null($this->conexion)){//si la conexión ha fallado

            $resultado=1;        
            _l("Crear reserva. ERROR: Problema con la conexión a la base de datos.");

        }else if($inicio===false || $fin===false || $horas){// si las horas no se corresponden al patrón o fin es mayor que inicio

            $resultado=2;
            _l("Crear reserva. ERROR: Problemas con el tramo horario.");

        }else if(!$fecha){//si hay algún problema con la fecha

            $resultado=3;    
            _l("Crear reserva. ERROR: La fecha no es válida.");

        }else{
                $arrayDatos= ['user_id'=>$user,'zona_id'=>$zona,'fecha'=>$fecha, 'inicio'=>$inicio, 'fin'=>$fin];
                
                $insercion= insertarReserva($this->conexion, $arrayDatos);

            if($insercion > 0){//todo correcto

                $resultado=5;
                _l("Crear reserva. Operación realizada correctamente.");


            }else if($insercion === false ){//no ha podido realizar la inserción

                $resultado=1;        
                _l("Crear reserva. ERROR: No ha podido realizar la insercion. Problema desde el método de eliminacion.");


            } else {//existe solapamiento

                $resultado=4; 
                _l("Crear reserva. ERROR: Existe un solapamiento con otra reserva.");

            }



        }
    
    }else{
        $resultado=6;
        _l("Error con los datos de usuario o zona.");
    }

    return $resultado;
}


/**
 * Busca la reserva indicada por los datos de array idreservas y si existe lo elimina.
 * 
 * @param type $idreserva idreservas array de datos que contiene zona, fecha y hora de inicio a buscar
 * @return int valor numérico que indica el estado final de la operación
 * @link http://localhost Puesto solo para el ejercicio
 * @version 1.0.1
 */
function eliminarReserva($idreserva){
    
    // filtramos los datos
    
    $zona= filter_var($idreserva->zona, FILTER_VALIDATE_INT);
    
    
    //controlamos las horas------------------------------------------------------------------------------------------
    $inicio= filter_var($idreserva->horaInicio, FILTER_VALIDATE_REGEXP,array("options" => ['regexp'=>self::HREGEX]));
    
    //controlamos la fecha-----------------------------------------------------------------------------------------------
    $fecha= filter_var($idreserva->fecha, FILTER_VALIDATE_REGEXP,array("options" => ['regexp'=> self::FREGEX]));
    
    //Comprobamos la fecha con checkdate
    $matches=[];
    if (is_string($fecha) && preg_match(self::FREGEX,$fecha,$matches))
    {
        list ($f,$d,$m,$a)=$matches;
        if (checkdate($m,$d,$a))
        {
            //Si la fecha es correcta cambiamos su formato al formato de la base de datos
            $fechaAux=new DateTime();
            $fechaAux->setDate($a,$m,$d);
            //Almacenamos la fecha en el formato que necesita la base de datos--------Importante
            $fecha=$fechaAux->format(self::FORMATO_FECHA_BD);
        } else {
            $fecha=false;
        }
    }
    else
    {
        $fecha=false;
    }
    
    //controlamos que los datos son existen
    if(is_int($zona) && is_string($fecha) && is_string($inicio)){
        
        //pasamos a las operaciones y resultados
        if(is_null($this->conexion)){//si la conexión ha fallado

            $resultado=1;        
            _l("Eliminar reserva. ERROR: Problema con la conexión a la base de datos.");

        
        }else{
                $arrayDatos= ['zona_id'=>$zona,'fecha_actual'=>$fecha, 'inicio_actual'=>$inicio];
                
                $borrado= eliminarReserva($this->conexion, $arrayDatos);

            if($borrado > 0){//todo correcto

                $resultado=4;
                _l("Eliminar reserva. Operación realizada correctamente.");


            }else {//no ha podido realizar el borrado

                $resultado=3;        
                _l("Eliminar reserva. ERROR: No ha podido realizar el borrado. Puede no existir el registro.");


            }



        }
    
    }else{
        $resultado=2;
        _l("Eliminar reserva. ERROR: Problemas con los datos, pueden no ser correctos.");
    }

    return $resultado;
}

