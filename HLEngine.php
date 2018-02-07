<?php
class HLEngine {

}
class autoloader {
  public static $loader;
  public static function init() {
    if (self::$loader == NULL)
      self::$loader = new self ();
    return self::$loader;
  }
  public function __construct() {
    spl_autoload_register ( array ($this, 'model' ) );
    spl_autoload_register ( array ($this, 'helper' ) );
    spl_autoload_register ( array ($this, 'controller' ) );
    spl_autoload_register ( array ($this, 'library' ) );
  }
  public function library($class) {
    set_include_path ( get_include_path () . PATH_SEPARATOR . '/lib/' );
    spl_autoload_extensions ( '.library.php' );
    spl_autoload ( $class );
  }
  public function controller($class) {
    $class = preg_replace ( '/_controller$/ui', '', $class );
    set_include_path ( get_include_path () . PATH_SEPARATOR . '/controller/' );
    spl_autoload_extensions ( '.controller.php' );
    spl_autoload ( $class );
  }
  public function model($class) {
    $class = preg_replace ( '/_model$/ui', '', $class );
    set_include_path ( get_include_path () . PATH_SEPARATOR . '/model/' );
    spl_autoload_extensions ( '.model.php' );
    spl_autoload ( $class );
  }
  public function helper($class) {
    $class = preg_replace ( '/_helper$/ui', '', $class );
    set_include_path ( get_include_path () . PATH_SEPARATOR . '/helper/' );
    spl_autoload_extensions ( '.helper.php' );
    spl_autoload ( $class );
  }
}
//call
autoloader::init ();

function core_autoload($class_name) {

    $prefix = substr($class_name,0,2);
    switch($prefix){
        case 'm_':
            $file_name = ROOT_PATH . '/app/models/' . substr($class_name, 2) . '.php';
        break;
        case 'a_':
            $file_name = ROOT_PATH . '/app/actions/' . substr($class_name, 2) . '.php';
        break;
        case 'u_':
            $file_name = ROOT_PATH . '/app/lib/usr/' . substr($class_name, 2) . '.php';
        break;
        default:
            $file_name =  get_include_path() . str_replace('_', '/', $class_name).'.php';
    }

    if( file_exists($file_name) )
            require_once $file_name;
    else spl_autoload($class_name);
}
spl_autoload_register('core_autoload');
