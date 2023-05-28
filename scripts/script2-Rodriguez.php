<?php
/**
 * Este documento ha salido de una tarea de la asignatura DWES hecha por mi el curso 21/22.
 * La funcionalidad del script está descrita en la descripción de la clase.
 * 
 * En este script se usa la etiqueta throws y since
 * 
 * @author Juan Enrique Rodriguez Pretel
 * @version 1.0.0
 */


/**
 * Clase que facilita la gestión de parámetros recibidos vía GET y POST. 
 * Ejemplo:
 * 
 * Peticion $p=new Peticion();
 * if ($p->has('a','b')) ... //Comprobar si se han recibido los parámetros 'a' y 'b'
 * 
 * $a=$p->getInt('a'); //Obtener el parámetro 'a' como un entero.
 * 
 * @since 1.0.0
 */
class Peticion {

    const GET=1;
    const POST=2;
    const BOTH=3;
    
    private $params;
    private $URI;
    private $method;
    
    public function __construct($opt=Peticion::BOTH) {
        switch ($opt)
        {
            case Peticion::GET:
                $this->params = $_GET;
            break;
            case Peticion::POST:
                $this->params = $_POST;
            break;
            case Peticion::BOTH:
            default:
                $this->params = array_merge($_POST, $_GET);
            break;                            
        }
        $this->URI=$_SERVER['REQUEST_URI'];    
        $this->method=$_SERVER['REQUEST_METHOD'];
    }

    /**
     * Obtiene la ruta de la petición HTTP. Por ejemplo, si la ruta es:
     * 
     * http://localhost/DWES04/test
     * 
     * retornará /DWES04/test
     * 
     * @param string $rootPath Raíz que se eliminará de la ruta. Por ejemplo, si la ruta completa es '/DWES04/test' y $basePath es '/DWES04' se retornará solo '/test'. 
     * Esto sirve generalmente para poder hacer aplicaciones web que se puedan mover de un directorio a otro sin problemas.
     * 
     * @return string Ruta de la URL
     * 
     * @version 1.0.0
     */
    public function getPath(string $rootPath = '')
    {
        $rootPath=trim($rootPath);                
        $rootPath=preg_replace('/\/+$/','',$rootPath);    
        return preg_replace('/^'.preg_quote($rootPath,'/').'/i','',$this->URI);        
    }

    /**
     * Permite obtener el método con el que se ha realizado la petición HTTP.
     * @return string Cadena con el método.
     * @version 1.0.0
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Retorna true si el método es GET.
     * @return boolean true si el método es GET, false en caso contrario.
     * @version 1.0.0
     */
    public function isGet()
    {
        return $this->method==='GET';
    }

    /**
     * Retorna true si el método es POST.
     * @return boolean true si el método es POST, false en caso contrario.
     * @version 1.0.0
     */
    public function isPost()
    {
        return $this->method==='POST';
    }

    /**
     * Función que permite verificar si en la petición HTTP se han 
     * recibido uno o más parámetros. 
     * Uso:
     * Peticion $p=new Peticion();
     * if ($p->has('a','b')) ...
     * 
     * @param mixed $... todos los parámetros a verificar (admite multiples argumentos).
     * @return true si existen todos los parámetros y false en caso contrario.
     * @version 1.0.0
     */
    public function has ()
    {        
        $res=true;
        for($i=0;$i<func_num_args() && $res;$i++)
        {
            $res&=isset($this->params[func_get_arg($i)]);
        }
        return $res;
    }        
    
    private function get($name)
    {
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }
        
    
    /**
     * Obtiene un parámetro tipo entero.
     * @param type $paramName nombre del parámetro
     * @return type int
     * @throws Exception Se lanza excepción si no existe el parámetro o si
     * el parámetro no es un entero.
     * @version 1.0.0
     */
    public function getInt($paramName) {
        
        if (!$this->has($paramName)) 
        {
            throw new \Exception("No existe $paramName.");
        }
        
        $value=trim($this->get($paramName));
        
        if (!preg_match('/^[0-9]+/', $value))
        {
            throw new \Exception("$paramName no es un número entero.");
        }        
        
        return (int) $value;
    }

    /**
     * Obtiene el parámetro del nombre $paramName y lo procesa para obtener 
     * un tipo de dato double.
     * @param string $paramName Tipo del parámetro
     * @param string $locale Nombre del locale del número decimal.
     * @return type double
     * @throws Exception Se lanza excepción si no existe el parámetro o si
     * el parámetro no es un número.
     * @version 1.0.0
     */
    public function getDouble($paramName, $locale='es_ES'){
        setlocale(LC_NUMERIC, $locale);
        if (!$this->has($paramName)) 
        {
            throw new \Exception("No existe $paramName.");
        }          
        $value=trim($this->get($paramName));
        if (!is_numeric($value))
        {
            throw new \Exception("$paramName no es un número con decimales.");
        }
        return floatval($value);
    }
    
    /**
     * Obtiene el parámetro del nombre $name y lo procesa para obtener 
     * un tipo de dato string.
     * @param string $param Tipo del parámetro
     * @param boolean $trim Si es true, se eliminan espacios anteriores y 
     * posteriores.
     * @return type string
     * @throws Exception Se lanza excepción si no existe el parámetro.
     * @version 1.0.0
     */
    public function getUnsafeString($name, $trim=true) {
    
        if (!$this->has($name)) 
        {
            throw new \Exception("No existe $name.");
        } 
        $value = (string) $this->get($name);
        
        if($trim) {
            $value=trim($value);
        }
        
        return $value;    
    }
    
    /**
     * Obtiene un parámetro tipo string añadiendole barras de escape
     * de carácteres especiales (para su uso en consultas SQL).
     * @param string $name Nombre del parámetro a obtener.
     * @param boolean $trim Si se debe o no eliminar espacios antes y después de la cadena.
     * @return string cadena contenida el el parámetro.
     * @throws ParamException si el parámetro no existe.
     * @version 1.0.0
     */
    public function getString($name, $trim=true)
    {
        return addslashes($this->getUnsafeString($name,$trim));
    }
    
    /**
     * Obtiene un parámetros que se reciben en formato array $param[...] 
     * (normamente usado en checkbox y otros casos).
     * @param string $name nombre del parámetro 
     * @param boolean $trim si se limpia de espacios cada elemento del array
     * @return array[string]
     * @throws Exception LAnza una excepción si no existe el parámetro o 
     * si no es un array.
     * @version 1.0.0
     */
    public function getArrayOfUnsafeStrings($name,$trim=true) 
    {
        if (!$this->has($name)) 
        {
            throw new \Exception("No existe $name.");
        }           
        elseif (!is_array($this->get($name)))
        {
            throw new \Exception("No es un array $name.");
        }
        $ret=$this->get($name);
        $trim && array_walk ($ret,function (&$val) {$val=trim($val);});
        return $ret;
    }
    /**
     * Obtiene un parámetros que se reciben en formato array $param[...] 
     * (normamente usado en checkbox y otros casos). A cada elemento del
     * array se le aplica addslashes para escapar las comillas y comillas dobles.
     * 
     * @param string $name nombre del parámetro 
     * @param boolean $trim si se limpia de espacios cada elemento del array
     * @return array[string]
     * @throws Exception LAnza una excepción si no existe el parámetro o 
     * si no es un array.
     * @version 1.0.0
     */
    public function getArrayOfStrings($name,$trim=true)
    {                
        $r=(array)$this->getArrayOfUnsafeStrings($name,$trim);        
        array_walk ($r,
                function (&$val) {$val= addslashes($val);});        
        return $r;
    }
}
