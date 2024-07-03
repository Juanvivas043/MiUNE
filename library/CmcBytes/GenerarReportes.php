<?php

class CmcBytes_GenerarReportes {
   private $reporte;
   private $tipo_reporte;
   private $nombre_reporte;
   private $dbhost;
   private $dbuser;
   private $dbname;
   private $dbpass;

   public function __construct() {
      $this->Request = Zend_Controller_Front::getInstance()->getRequest();
      $config = Zend_Registry::get('config');
      $this->dbname = $config->database->params->dbname;
      $this->dbuser = $config->database->params->username;
      $this->dbpass = $config->database->params->password;
      $this->dbhost = $config->database->params->host;
      $this->m = new Memcached();
      $this->m->addServer("127.0.0.1", 11211) or die("cannot connect");
   }

   public function preparar($id, $report, $params, $filetype, $filename = "", $descargar = true){
        ini_set("memory_limit","150M");     
        $cmd = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D PGSQL -h {$this->dbhost} -u {$this->dbuser} -d {$this->dbname} -p {$this->dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
        
        $outstream = exec($cmd);
        if($descargar){
           return base64_decode($outstream);
        } else {

           if($this->m->replace("reporte$id", $outstream, time() + 600)){
              $this->m->replace("cmd$id", $cmd, time() + 600);
              $this->m->replace("nombre_reporte$id", $filename, time() + 600);
              $this->m->replace("tipo_reporte$id", $filetype, time() + 600);
              
           }else{
              $this->m->set("cmd$id", $cmd, time() + 600);
              $this->m->set("reporte$id", $outstream, time() + 600);
              $this->m->set("nombre_reporte$id", $filename, time() + 600);
              $this->m->set("tipo_reporte$id", $filetype, time() + 600);
              
           }
        }
   }

   public function getNombre_Reporte($id){
      return $this->m->get("nombre_reporte$id");
   }

   public function getTipo_Reporte($id){
      return $this->m->get("tipo_reporte$id");
   }

   public function descargar($id){
      return $this->m->get("reporte$id");
   }

   public function descargarConNombre($nombre){
      return $this->m->get($nombre);
   }

   public function getCommand($id){
      return $this->m->get("cmd$id");
   }
   

}
?>
