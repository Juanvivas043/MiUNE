<?php
class Models_DbTable_Atributostipos extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_atributostipos';
    protected $_primary  = 'pk_atributotipo';
    protected $_sequence = false;

    private $searchParams = array('a.pk_atributotipo', 'a.valor');
    
    public function init() {
        $this->SwapBytes_Array = new SwapBytes_Array();
         $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }

public function setSearch($searchData) {
        $this->searchData = $searchData;
    }

    
    /**
     * Obtiene un registro en especifico.
     *
     * @param int $id Clave primaria del registro.
     * @return array
     */
    public function getRow($id) {
        $id = (int)$id;
        $row = $this->fetchRow($this->_primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
    }
    
    public function getatributostipos() {
        $SQL = "SELECT DISTINCT atr.pk_atributotipo, atr.nombre AS atributotipo
                  FROM tbl_atributostipos atr;";
           
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }
    
    public function getListaTipoHorarios() {
     
 $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);        
        
        $SQL = "SELECT 	a.pk_atributo,
                        a.valor AS tipohorario 
                  FROM tbl_atributos a
                 WHERE fk_atributotipo = 37
                  {$whereSearch}
                  ORDER BY 2,1;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
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
}
