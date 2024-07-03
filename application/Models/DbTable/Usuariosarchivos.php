<?php

/**
 * Models for table tbl_usuariosarchivos
 *
 * @author      Alton Bell Smythe ( abellsmythe@gmail.com )
 * @version     1.0 ( 14/09/2016 )
 * @package     Models_DbTable_Usuariosarchivos
 */
class Models_DbTable_Usuariosarchivos extends Zend_Db_Table
{

    protected   $_schema = 'produccion';
    protected   $_name = 'tbl_usuariosarchivos';
    protected   $_primary = 'pk_usuarioarchivo';
    protected   $_sequence = false;
    private     $searchParams = array('fk_usuario','fecha',"LTRIM(TO_CHAR(fk_usuario, '99\".\"999\".\"999')::varchar, '0. ')");

    /**
     *
     *  init function  
     *
     * @param   null
     * @return  null
     */
    public function init()
    {
        $this->AuthSpace                = new Zend_Session_Namespace('Zend_Auth');
        $this->SwapBytes_Array          = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table  = new SwapBytes_Crud_Db_Table();
    }

    /**
     *
     * set global search  
     *
     * @param   null
     * @return  null
     */
    public function setSearch($searchData)
    {
        $this->searchData = $searchData;
    }

    /**
     *
     * count rows on table
     *
     * @param   int (type default 20117 => Curriculum Vitae)
     * @return  int
     */
    public function getSQLCount($type = 20117)
    {
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

        $SQL = "SELECT COUNT(pk_usuarioarchivo)
                FROM tbl_usuariosarchivos
                WHERE fk_tipo = {$type}
                {$whereSearch}";

        return $this->_db->fetchOne($SQL);
    }

    /**
     *
     * obtiene un registro en especifico  
     *
     * @param   null
     * @return  array
     */
    public function getRow($id)
    {
        $id = (int)$id;
        $row = $this->fetchRow($this->$_primary . ' = ' . $id);

        if (isset($row)) {
            return $row->toArray();
        }
    }

    /**
     *
     * agregar un registro  
     *
     * @param   array
     * @return  int (affected rows)
     */
    public function addRow($data)
    {
        $data = array_filter($data);
        $affected = $this->insert($data);

        return $affected;
    }

    /**
     *
     * actulizar un registro en especifico
     *
     * @param   array
     * @return  int (affected rows)
     */
    public function updateRow($id, $data)
    {
        $data = array_filter($data);
        $affected = $this->update($data, $this->_primary . ' = ' . (int)$id);

        return $affected;
    }

    /**
     *
     * elimina un registro en especifico 
     *
     * @param   array
     * @return  int (affected rows)
     */
    public function deleteRow($id)
    {
        $affected = $this->delete($this->_primary . ' = ' . (int)$id);

        return $affected;
    }

    /**
     *
     * obtiene los registros de tipo Curriculum Vitae
     *
     * @param   null
     * @return  array
     */
    public function getCurriculums($itemPerPage, $pageNumber)
    {
        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

        $SQL = "SELECT  ta.pk_usuarioarchivo,
                        ta.fk_usuario AS cedula,
                        tu.apellido,
                        tu.nombre,
                        ta.ruta,
                        ta.fecha
                FROM tbl_usuariosarchivos ta
                JOIN tbl_usuarios tu ON ta.fk_usuario = tu.pk_usuario
                WHERE fk_tipo = 20117
                {$whereSearch}
                ORDER BY ta.fk_usuario
                LIMIT {$itemPerPage} OFFSET {$pageNumber};";

        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }

    public function getCurriculumRow($id)
    {
        $SQL = "SELECT ta.pk_usuarioarchivo,
                        ta.ruta,
                        ta.fk_usuario,
                        ta.fk_tipo,
                        ta.fecha
                FROM tbl_usuariosarchivos ta
                WHERE ta.pk_usuarioarchivo = {$id}
                AND ta.fk_tipo = 20117";

        $results = $this->_db->query($SQL);
        $return  = (array)$results->fetchAll();
        return $return[0];
    }

    public function setData($Data, $Keys) {
        $Keys = array_fill_keys($Keys, null);
        $Data = array_intersect_key($Data, $Keys);
        $Where = array(' AND  ug.fk_grupo        = ' => $Data['Perfil']);
        $Where = array_filter($Where);
        $Where = $this->SwapBytes_Array->implode(' ', $Where);
        $Where = ltrim($Where, ' AND ');
        $this->Where = $Where;
    }

    public function countCV($id){
        $SQL = "SELECT COUNT(ta.pk_usuarioarchivo)
                FROM tbl_usuariosarchivos ta
                WHERE ta.fk_usuario = {$id}
                AND ta.fk_tipo = 20117;";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();
        return $results[0]['count'];
    }

    public function getDocument($type,$id){
        $SQL = "SELECT ta.pk_usuarioarchivo,
                        ta.ruta,
                        ta.fk_usuario,
                        ta.fk_tipo,
                        ta.fecha
                FROM tbl_usuariosarchivos ta
                WHERE ta.fk_usuario = {$id}
                AND ta.fk_tipo = {$type};";

        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }

}

?>