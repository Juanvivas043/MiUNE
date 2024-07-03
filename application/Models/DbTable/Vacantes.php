<?php

class Models_DbTable_Vacantes extends Zend_Db_Table {

  protected $_schema   = 'produccion';
  protected $_name     = 'tbl_vacantes';
  protected $_primary  = 'pk_vacante';
  protected $_sequence = false;

  private $searchParams = array('tv.fk_institucion', 'tv.title', 'tv.fk_contrato', 'tv.vacantes', 'tv.fk_sexo', 'tv.edad', 'tv.fecha_publicacion','tv.fecha_culminacion','tv.descripcion','tv.requisitos','tv.beneficios','tv.direccion');
  
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

  public function getCount(){
    $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
    $SQL = "SELECT COUNT(tv.pk_vacante)
              FROM tbl_vacantes tv
              WHERE tv.fecha_culminacion >= (SELECT current_date)
              {$whereSearch}";
    return $this->_db->fetchOne($SQL);
  }

  public function getSQLCount($desde = null, $hasta = null, $empresa){
    $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
    $SQL = "SELECT COUNT(tv.pk_vacante)
              FROM tbl_vacantes tv
              WHERE tv.fecha_culminacion >= (SELECT current_date)
              AND tv.fk_institucion = {$empresa}";
    if($desde != null and $hasta != null){
      $SQL .= " AND tv.fecha_culminacion BETWEEN '{$desde}'::DATE AND '{$hasta}'::DATE";
    }
    $SQL .= "{$whereSearch}";

    return $this->_db->fetchOne($SQL);
  }

  public function getDateCount($desde = null, $hasta = null){
    $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
    $SQL = "SELECT COUNT(tv.pk_vacante)
              FROM tbl_vacantes tv
              WHERE tv.fecha_culminacion >= (SELECT current_date)";
    if($desde != null and $hasta != null){
      $SQL .= "AND tv.fecha_culminacion BETWEEN '{$desde}'::DATE AND '{$hasta}'::DATE";
    }
    $SQL .= "{$whereSearch}";

    return $this->_db->fetchOne($SQL);
  }

  public function getVacantes($itemPerPage, $pageNumber, $desde = null, $hasta = null) {
    $pageNumber = ($pageNumber - 1) * $itemPerPage;
    $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
    $SQL = "SELECT tv.pk_vacante, 
                    tv.title, 
                    tv.fk_institucion, 
                    ti.nombre AS empresa, 
                    tv.fk_contrato, 
                    tt.valor AS contrato, 
                    tv.vacantes, 
                    tv.fk_sexo, 
                    (CASE WHEN tv.fk_sexo = 20114 THEN 'Femenino' WHEN tv.fk_sexo = 20115 THEN 'Masculino' ELSE 'Indistinto' END) AS sexo, 
                    tv.edad, 
                    tv.fecha_publicacion AS publicacion, 
                    tv.fecha_culminacion AS culminacion,
                    tv.descripcion,
                    tv.requisitos, 
                    tv.beneficios,
                    tv.direccion,
                    (SELECT COUNT(tp.pk_postulacion)
                    FROM tbl_vacantes tv1
                    JOIN tbl_postulaciones tp ON tv1.pk_vacante = tp.fk_vacante
                    JOIN tbl_usuariosarchivos tua ON tp.fk_usuario = tua.fk_usuario
                    WHERE tv1.pk_vacante = tv.pk_vacante
                    AND tv.fk_institucion = ti.pk_institucion
                    AND tua.fk_tipo = 20117) AS postulados
              FROM tbl_vacantes tv
              JOIN tbl_instituciones ti ON tv.fk_institucion = ti.pk_institucion
              JOIN tbl_atributos tt ON tv.fk_contrato = tt.pk_atributo
              WHERE tv.fecha_culminacion >= (SELECT current_date)";
    if($desde != null and $hasta != null){
      $SQL .= "AND tv.fecha_culminacion BETWEEN '{$desde}'::DATE AND '{$hasta}'::DATE";
    }
    $SQL .= "{$whereSearch}
            ORDER BY tv.fecha_publicacion DESC LIMIT {$itemPerPage} OFFSET {$pageNumber};";
    $results = $this->_db->query($SQL);

    return (array)$results->fetchAll();
  }

  public function getVacante($id) {
    $SQL = "SELECT tv.pk_vacante as id_vacante, 
                  tv.title, 
                  ti.nombre as empresa, 
                  ti.pk_institucion as id,
                  tv.fk_contrato, 
                  tt.valor as contrato, 
                  tv.vacantes, 
                  tv.fk_sexo,
                  tv.edad, 
                  tv.fecha_publicacion as publicacion, 
                  tv.fecha_culminacion as culminacion, 
                  tv.descripcion, 
                  tv.requisitos, 
                  tv.beneficios, 
                  tv.direccion
            FROM tbl_vacantes tv
            JOIN tbl_instituciones ti ON tv.fk_institucion = ti.pk_institucion
            JOIN tbl_atributos tt ON tv.fk_contrato = tt.pk_atributo
            WHERE tv.pk_vacante = {$id};";

    $results = $this->_db->query($SQL);

    return (array)$results->fetchAll();
  }

  public function getVacantesByEmpresa($empresa){
    $SQL = "SELECT tv.pk_vacante, 
                   tv.title, 
                   tv.fk_institucion, 
                   tv.fk_contrato, 
                   tv.fk_sexo, 
                   tv.edad, 
                   tv.fecha_publicacion, 
                   tv.fecha_culminacion, 
                   tv.descripcion, 
                   tv.requisitos, 
                   tv.beneficios, 
                   tv.direccion
            FROM tbl_vacantes tv
            JOIN tbl_instituciones ti ON tv.fk_institucion = ti.pk_institucion
            WHERE ti.pk_institucion = {$empresa}
            GROUP BY tv.pk_vacante, tv.title";
    $results = $this->_db->query($SQL);

    return (array) $results->fetchAll();
  }

  public function getMisVacantes($itemPerPage, $pageNumber, $empresa, $desde = null, $hasta = null){
    $pageNumber = ($pageNumber - 1) * $itemPerPage;
    $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
    $SQL = "SELECT tv.pk_vacante, 
                      tv.title, 
                      tv.fk_institucion, 
                      ti.nombre as empresa, 
                      tv.fk_contrato, 
                      tt.valor as contrato, 
                      tv.vacantes, 
                      tv.fk_sexo, 
                      (CASE WHEN tv.fk_sexo = 20114 THEN 'Femenino' WHEN tv.fk_sexo = 20115 THEN 'Masculino' ELSE 'Indistinto' END) as sexo, 
                      tv.edad, 
                      tv.fecha_publicacion as publicacion, 
                      tv.fecha_culminacion as culminacion,
                      tv.descripcion,
                      tv.requisitos, 
                      tv.beneficios,
                      tv.direccion
                FROM tbl_vacantes tv
                JOIN tbl_instituciones ti ON tv.fk_institucion = ti.pk_institucion
                JOIN tbl_atributos tt ON tv.fk_contrato = tt.pk_atributo
                WHERE tv.fecha_culminacion >= (SELECT current_date)
                AND ti.pk_institucion = {$empresa}";
    if($desde != null and $hasta != null){
      $SQL .= "AND tv.fecha_culminacion BETWEEN '{$desde}'::DATE AND '{$hasta}'::DATE";
    }
    $SQL .= "{$whereSearch}
             ORDER BY tv.fecha_publicacion DESC LIMIT {$itemPerPage} OFFSET {$pageNumber};";

    $results = $this->_db->query($SQL);

    return (array) $results->fetchAll();
  }

}

?>