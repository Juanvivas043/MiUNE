<?php

class Models_DbTable_Prestamoart extends Zend_Db_Table {

    private $Prestamoval   = 8242;//8223;  // local 8242  // omicron 8223
    private $Moraval       = 8244;//8224;  // local 8244  // omicron 8224
    private $Devueltoval   = 8243;//8225;  // local 8243  // omicron 8225
    protected $_schema = 'produccion';
    protected $_name = 'tbl_prestamosarticulos';
    protected $_primary = 'pk_prestamoarticulo';
    protected $_sequence = false;
    private $searchParams = array('pa.cota','pa.fecha_devolucion', 'pa.fecha_entrega', 'a.valor','pa.comentario');

    public function init() {
        $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');

        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }

    public function getListArticuloprestamo($id){
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
       $SQL=" SELECT pa.pk_prestamoarticulo ,pa.comentario , pa.cota , pa.fecha_devolucion , pa.fecha_entrega ,
                p.fecha_prestamo as fecha_solicitud, a.valor as estado, pa.hora, pa.hora_retorno, 
                (u_reg.primer_apellido ||', '||u_reg.primer_nombre) as usuario_registro, 
                (u_ret.primer_apellido ||', '||u_ret.primer_nombre) as usuario_retorno
                FROM tbl_prestamosarticulos         pa
                JOIN tbl_atributos                  a       ON a.pk_atributo            = pa.fk_asignacion
                JOIN tbl_prestamos                  p       ON p.pk_prestamo            = pa.fk_prestamo
                LEFT OUTER JOIN tbl_usuariosgrupos  ug_reg  ON ug_reg.pk_usuariogrupo   = pa.usuario_registro
                LEFT OUTER JOIN tbl_usuarios        u_reg   ON u_reg.pk_usuario         = ug_reg.fk_usuario
                LEFT OUTER JOIN tbl_usuariosgrupos  ug_ret  ON ug_ret.pk_usuariogrupo   = pa.usuario_retorno
                LEFT OUTER JOIN tbl_usuarios        u_ret   ON u_ret.pk_usuario         = ug_ret.fk_usuario
                WHERE pa.fk_prestamo = $id {$whereSearch};";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }

    public function insertarart($fk_prestamo , $fecha_devolucion , $cota , $comentario){
        $SQL = "  INSERT INTO tbl_prestamosarticulos(fk_prestamo,fecha_devolucion,cota,fk_asignacion,comentario)
                  VALUES ($fk_prestamo , '$fecha_devolucion' , '$cota' ,$this->Prestamoval, '$comentario' );";
       
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();

        
    }
    
    public function getSQLCount() {
        
        $SQL = "SELECT COUNT(pk_prestamoarticulo)
		FROM tbl_prestamosarticulos";

        return $this->_db->fetchOne($SQL);
    }
    
    public function retorno ($pk_prestamoarticulo , $fecha, $usuario_retorno,$hora_retorno){
         $SQL = " UPDATE tbl_prestamosarticulos
                 SET fecha_entrega='$fecha', 
                 usuario_retorno = {$usuario_retorno},
                 hora_retorno = '{$hora_retorno}', 
                 fk_asignacion= $this->Devueltoval
                 WHERE pk_prestamoarticulo = $pk_prestamoarticulo;";
       
        $results = $this->_db->query($SQL);
        return $results;
    }
    
    public function prestamo ($pk_prestamoarticulo){
         $SQL = " UPDATE tbl_prestamosarticulos
                 SET fk_asignacion= $this->Prestamoval
                 WHERE pk_prestamoarticulo = {$pk_prestamoarticulo};";
       
        $results = $this->_db->query($SQL);
        return $results;
    }
    
    
    public function getRow($id) {
        $id = (int)$id;
        $row = $this->fetchRow($this->_primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
    }
    
    public function updateRow($id, $data) {  
        
        $data     = array_filter($data);

        $affected = $this->update($data, $this->_primary . ' = ' . (int)$id);
        
        return $affected;
    }

    public function addRow($data) {
        $data     = array_filter($data);
        $affected = $this->insert($data);

        return $affected;
    }
    
    public function deleteRow($id) {
		if(!is_numeric($id)) return null;

        $affected = $this->delete($this->_primary . ' = ' . (int) $id);

        return $affected;
    }
    
    public function miupdate($id){
        $SQL = "UPDATE tbl_prestamosarticulos
                SET fecha_entrega = NULL
                WHERE pk_prestamoarticulo = $id;";
        $results = $this->_db->query($SQL);
    }

    public function getCountarticulo($id){
               $SQL = "SELECT COUNT(pk_prestamoarticulo)
                       FROM tbl_prestamosarticulos
                       WHERE fk_prestamo = $id AND fk_asignacion <> $this->Devueltoval;";
                
                return $this->_db->fetchOne($SQL);
    }
    
    public function setSearch($searchData) {
        $this->searchData = $searchData;
    }
    
    public function getUsuarioprestamo($ci){
      $SQL ="SELECT pa.cota , pa.fk_asignacion , a.valor as estado
             FROM tbl_usuarios u 
             JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
             JOIN tbl_prestamos p ON p.fk_usuariogrupo = ug.pk_usuariogrupo
             JOIN tbl_prestamosarticulos pa ON pa.fk_prestamo = p.pk_prestamo
             JOIN tbl_atributos a ON a.pk_atributo = pa.fk_asignacion
             WHERE u.pk_usuario = {$ci} AND pa.fk_asignacion <> $this->Devueltoval";  
             
               $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }

    public function getprestamointerno(){
          $SQL ="SELECT pk_atributo , valor as interno
                 FROM tbl_atributos
                 WHERE fk_atributotipo = 71";  
             
               $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }
    
    public function gettipoprestamo($id){ 
        $SQL ="SELECT fk_tipo_interno
               FROM tbl_prestamosarticulos
               WHERE pk_prestamoarticulo = $id";  
             
               $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }

    public function get_libro($cota){
        $SQL ="SELECT pk_libro , cota , titulo , edicion , fk_editorial , ano , pagina , volumen, ejemplar,nota,coleccion, fk_sede
               FROM tbl_libros l 
               WHERE cota ilike '{$cota}'
               order by fk_sede desc";
             
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
    }
    
    public function get_tesis($cota){
         $SQL ="select pk_tesis , cota ,dt.titulo , pagina, fk_sede
                from tbl_tesis          t 
                join tbl_datostesis     dt      on  dt.pk_datotesis         =       t.fk_datotesis
                where cota ilike '{$cota}'";
               
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
    }

   public function getUsuariogrupo($cedula,$grupo = null){
         
              
       if(empty($cedula))return;
       
       ($grupo != null)? $filtro_grupo = " and fk_grupo in ({$grupo}) ":$filtro_grupo = "";
       
        $SQL ="select distinct pk_usuariogrupo
        from tbl_usuariosgrupos
        where fk_usuario = {$cedula} 
       " .$filtro_grupo."
         limit 1";

        return $this->_db->fetchOne($SQL);
      
  }    
    
}

