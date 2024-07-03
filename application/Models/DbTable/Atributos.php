<?php
class Models_DbTable_Atributos extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_atributos';
    protected $_primary  = 'pk_atributo';
    protected $_sequence = true;

    
    public function init() {
        $this->SwapBytes_Array = new SwapBytes_Array();
         $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }

public function setSearch($searchData) {
        $this->searchData = $searchData;
    }

    /**
     *
     * @param int $Tipo
     * @param int $Ignore
     * @return array
     */
    public function getSelect($Tipo, $ColumnValue = 'valor', $ColumnOrder = 'valor', $Ignore = null) {
        $Ignore = (isset($Ignore))? "AND valor NOT IN ({$Ignore})" : null;

        $SQL = "SELECT DISTINCT {$this->_primary}, {$ColumnValue}
                FROM {$this->_name}
                WHERE fk_atributotipo = {$Tipo} {$Ignore}
                ORDER BY {$ColumnOrder} ASC;";

        
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }


    public function getWithIn($Tipo,$in){

        $SQL = "SELECT DISTINCT {$this->_primary}, valor
                FROM {$this->_name}
                WHERE fk_atributotipo = {$Tipo}
                 and pk_atributo  IN ({$in})
                ORDER BY valor ASC;";


        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
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

    public function getByPk($pk){

        $SQL = "SELECT a.pk_atributo,
                       a.valor
                  FROM tbl_atributos a
                 WHERE pk_atributo = {$pk}
                  ;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function gettipohorario() {
        $SQL = "SELECT DISTINCT a.pk_atributo, a.valor AS tipohorario
                  FROM tbl_atributos a
                  WHERE a.fk_atributotipo = 37
                  ORDER BY 2 ASC;";
           
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function getTipes($tipo, $ignore){
        
        if(is_array($ignore)){
            foreach ($ignore as $key => $value) {
               $Ignore .= (isset($value))? " AND a.pk_atributo NOT IN ({$value})" : null;
            }
            $Ignore .= " order by 1 ";
        }else{
            $Ignore .= (isset($ignore))? " AND a.pk_atributo NOT IN ({$ignore})" : null;
            $Ignore .= " order by 2 "; 
        }
         
        

        $SQL = "SELECT DISTINCT a.pk_atributo, a.valor
                  FROM tbl_atributos a
                  WHERE a.fk_atributotipo = {$tipo} {$Ignore}
                  ";
                  
        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();

    }
    
    public function getListaTipoHorarios() {
     

        
        $SQL = "SELECT 	a.fk_atributotipo,
                        a.pk_atributo,
                        a.valor AS tipohorario
                  FROM tbl_atributos a
                 WHERE fk_atributotipo = 37
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

    public function getChilds($pk){

        $SQL = "SELECT a2.pk_atributo, a2.valor
                FROM tbl_atributos a1
                JOIN tbl_atributos a2 ON a1.pk_atributo = a2.fk_atributo
                WHERE a1.pk_atributo = {$pk}
                and a2.valor not ilike '%-%'
                order by 2;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getRendimiento(){

          $SQL = "SELECT  a.pk_atributo,a.valor
                    FROM tbl_atributos a
                    WHERE fk_atributotipo = 102
                    ORDER BY 1;";

          $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

      }
}
