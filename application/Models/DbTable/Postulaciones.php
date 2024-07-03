<?php

class Models_DbTable_Postulaciones extends Zend_Db_Table {

  protected $_schema   = 'produccion';
  protected $_name     = 'tbl_postulaciones';
  protected $_primary  = 'pk_postulacion';
  protected $_sequence = false;

  private $searchParams = array('tp.fk_usuario','tp.fecha');
  
  public function init() {
      $this->SwapBytes_Array = new SwapBytes_Array();
      $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
  }

  public function setSearch($searchData) {
      $this->searchData = $searchData;
  }
    
  public function addRow($data) {
    $data     = array_filter($data);
    $affected = $this->insert($data);

    return $affected;
  }

  public function getRow($id) {
		if(!isset($id)) return;
		
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

  public function deleteRow($id) {
		if(!is_numeric($id)) return null;
    $affected = $this->delete($this->_primary . ' = ' . (int) $id);

    return $affected;
  }

  public function getSQLCount($empresa,$vacante,$desde,$hasta){
    $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
     $SQL = "SELECT COUNT(tp.pk_postulacion)
            FROM tbl_postulaciones tp
            JOIN tbl_vacantes tv ON tv.pk_vacante = tp.fk_vacante
            WHERE tv.pk_vacante = {$vacante}
            AND tv.fk_institucion = {$empresa}";
    if(!empty($desde) and !empty($hasta)){
      $SQL .= "AND tp.fecha BETWEEN '{$desde}' AND '{$hasta}'
            {$whereSearch}";
    }
    $SQL .= "{$whereSearch}";

    return $this->_db->fetchOne($SQL);
  }

  public function getCount($fk_vacante, $where = null){
    if (empty($fk_vacante)) return;
    $SQL = "SELECT COUNT(tp.fk_vacante) AS count
            FROM tbl_postulaciones tp
            WHERE tp.fk_vacante = {$fk_vacante} {$where};";

    $results = $this->_db->query($SQL);
    $results = $results->fetchAll();
    return $results[0]['count'];
  }

  public function getPostulaciones($itemPerPage, $pageNumber,$empresa,$vacante,$desde,$hasta) {
    //Si el Usuario no tiene asociado un CV no sera listado
    $pageNumber = ($pageNumber - 1) * $itemPerPage;
    $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
    $SQL = "SELECT pk_postulacion,
            cedula,
            fecha_postulacion,
            cv,
            postulado,
            correo,
            CASE WHEN escuela IS NULL THEN 'N/A' ELSE escuela END
           FROM (SELECT tp.pk_postulacion,
                          tp.fk_usuario AS cedula,
                          tp.fecha AS fecha_postulacion,
                          tua.ruta as cv,
                          tu.nombre || ' ' || tu.apellido AS postulado,
                          tu.correo,
                          (SELECT tt.valor
                          FROM  tbl_usuariosgrupos tg 
                          JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                          JOIN tbl_atributos tt ON ti.fk_atributo = tt.pk_atributo
                          WHERE tg.fk_usuario = tu.pk_usuario
                          ORDER BY ti.fk_periodo DESC 
                          LIMIT 1) AS escuela
                        FROM tbl_vacantes tv
                        JOIN tbl_postulaciones tp ON tv.pk_vacante = tp.fk_vacante
                        JOIN tbl_usuarios tu ON tp.fk_usuario = tu.pk_usuario
                        JOIN tbl_usuariosarchivos tua ON tp.fk_usuario = tua.fk_usuario
                        WHERE tv.pk_vacante = {$vacante}
                        AND tv.fk_institucion = {$empresa}
                        AND tua.fk_tipo = 20117";
    if(!empty($desde) and !empty($hasta)){
      $SQL .= "AND tp.fecha BETWEEN '{$desde}'::DATE AND '{$hasta}'::DATE";
    }
    $SQL .= "{$whereSearch}
              ORDER BY tv.fecha_publicacion,tv.pk_vacante LIMIT {$itemPerPage} OFFSET {$pageNumber})as sqt;";
    $results = $this->_db->query($SQL);

    return (array)$results->fetchAll();
  }

  public function getPostulacion($id){
    $SQL = "SELECT tp.pk_postulacion,
              tp.fk_usuario AS cedula,
              tp.fecha AS fecha_postulacion,
              tua.ruta AS cv,
              tu.nombre || ' ' || tu.apellido AS postulado,
              tu.correo,
              tu.telefono, 
              tu.telefono_movil AS celular,
              CASE WHEN tu.sexo = true THEN 'Hombre' ELSE 'Mujer' END AS sexo,
              (SELECT tt.valor
              FROM  tbl_usuariosgrupos tg 
              JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
              JOIN tbl_atributos tt ON ti.fk_atributo = tt.pk_atributo
              WHERE tg.fk_usuario = tu.pk_usuario
              ORDER BY ti.fk_periodo DESC 
              LIMIT 1) AS escuela
            FROM tbl_vacantes tv
            JOIN tbl_postulaciones tp ON tv.pk_vacante = tp.fk_vacante
            JOIN tbl_usuarios tu ON tp.fk_usuario = tu.pk_usuario
            JOIN tbl_usuariosarchivos tua ON tp.fk_usuario = tua.fk_usuario
            WHERE tp.pk_postulacion = {$id}
            AND tua.fk_tipo = 20117";
    $results = $this->_db->query($SQL);

    return (array)$results->fetchAll();
  }

  public function getPostulado($cedula,$vacante){
    $SQL = "SELECT COUNT(tp.pk_postulacion)
            FROM tbl_vacantes tv
            JOIN tbl_postulaciones tp ON tv.pk_vacante = tp.fk_vacante
            WHERE tp.fk_usuario = {$cedula}
            AND tv.pk_vacante = {$vacante}";
    $results = $this->_db->query($SQL);
    $results = (array)$results->fetchAll();

    return $results[0]['count'];
  }

  public function getPostulacionCedula($cedula,$vacante){
    $SQL = "SELECT tp.pk_postulacion,
            tp.fk_vacante,
            tp.fk_usuario,
            tp.fecha
            FROM tbl_vacantes tv
            JOIN tbl_postulaciones tp ON tv.pk_vacante = tp.fk_vacante
            WHERE tp.fk_usuario = {$cedula}
            AND tv.pk_vacante = {$vacante}";
    $results = $this->_db->query($SQL);
    $results = (array)$results->fetchAll();

    return $results[0];
  }
}

?>