<?php

class Models_DbTable_Repositorio extends Zend_Db_Table
{

    protected $_schema = 'produccion';
    protected $_name = 'tbl_recursos';
    protected $_primary = 'pk_recurso';
    protected $_sequence = false;
    private $searchParams = array('pk_recurso', 'titulo', 'nombre');

    public function init()
    {
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }

    public function setSearch($searchData) {   

        $this->searchData = $searchData;
        
    }

    public function getRow($id){

        $id = (int)$id;

        $row = $this->fetchRow('pk_recurso' . ' = ' . $id);

        if (isset($row)) {
            return $row->toArray();
        }

    }

    public function addRow($data)
    {
        $data = array_filter($data);
        $affected = $this->insert($data);

        return $affected;
    }

    public function deleteRow($id)
    {
        $affected = $this->delete('pk_recurso' . ' = ' . (int)$id);

        return $sexo = 'penes';
    }

    public function updateRow($id, $data)
    {
        $data = array_filter($data);
        $affected = $this->update($data, 'pk_recurso'. ' = ' . (int)$id);

        return $affected;
    }

    public function getRecursos($itemPerPage, $pageNumber){
        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

            $SQL =  "SELECT re.pk_recurso, re.titulo, u.pk_usuario AS cedula, concat(u.nombre,' ',u.apellido) AS nombre, tr.valor AS tiporecurso, c.valor AS coleccion, estado.valor as estado
                        FROM tbl_recursos re
                        JOIN tbl_usuariosgrupos ug ON re.fk_usuariogrupo = ug.pk_usuariogrupo
                        JOIN tbl_usuarios u ON ug.fk_usuario = u.pk_usuario
                        JOIN tbl_atributos tr ON re.fk_tiporecurso = tr.pk_atributo
                        JOIN tbl_atributos c ON re.fk_coleccion = c.pk_atributo 
                        JOIN tbl_atributos estado on re.fk_estado = estado.pk_atributo
                        {$whereSearch}
                        LIMIT $itemPerPage OFFSET $pageNumber";

            $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
        }

    public function getRecursosTipo($itemPerPage, $pageNumber, $tiporecurso){
        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

            $SQL =  "SELECT re.pk_recurso, re.titulo, u.pk_usuario AS cedula, concat(u.nombre,' ',u.apellido) AS nombre, tr.valor AS tiporecurso, c.valor AS coleccion, estado.valor as estado
                        FROM tbl_recursos re
                        JOIN tbl_usuariosgrupos ug ON re.fk_usuariogrupo = ug.pk_usuariogrupo
                        JOIN tbl_usuarios u ON ug.fk_usuario = u.pk_usuario
                        JOIN tbl_atributos tr ON re.fk_tiporecurso = tr.pk_atributo
                        JOIN tbl_atributos c ON re.fk_coleccion = c.pk_atributo 
                        JOIN tbl_atributos estado on re.fk_estado = estado.pk_atributo
                        WHERE tr.pk_atributo = $tiporecurso
                        {$whereSearch}
                        LIMIT $itemPerPage OFFSET $pageNumber";

            $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
        }

    public function getRecursosTipoEscuela($itemPerPage, $pageNumber, $tiporecurso, $coleccion){

        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

            $SQL =  "SELECT re.pk_recurso, re.titulo, u.pk_usuario AS cedula, concat(u.nombre,' ',u.apellido) AS nombre, tr.valor AS tiporecurso, c.valor AS coleccion, estado.valor as estado
                        FROM tbl_recursos re
                        JOIN tbl_usuariosgrupos ug ON re.fk_usuariogrupo = ug.pk_usuariogrupo
                        JOIN tbl_usuarios u ON ug.fk_usuario = u.pk_usuario
                        JOIN tbl_atributos tr ON re.fk_tiporecurso = tr.pk_atributo
                        JOIN tbl_atributos c ON re.fk_coleccion = c.pk_atributo 
                        JOIN tbl_atributos estado on re.fk_estado = estado.pk_atributo
                        WHERE tr.pk_atributo = $tiporecurso AND c.pk_atributo = $coleccion 
                        {$whereSearch}
                        LIMIT $itemPerPage OFFSET $pageNumber";

            $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
        }

    public function getCountRecursos(){

            $SQL =  "SELECT COUNT(distinct re.pk_recurso)
                        FROM tbl_recursos re
                        JOIN tbl_usuariosgrupos ug ON re.fk_usuariogrupo = ug.pk_usuariogrupo
                        JOIN tbl_usuarios u ON ug.fk_usuario = u.pk_usuario
                        JOIN tbl_atributos tr ON re.fk_tiporecurso = tr.pk_atributo
                        JOIN tbl_atributos c ON re.fk_coleccion = c.pk_atributo";
    
            $results = $this->_db->query($SQL);
            

        return $results->fetchAll();
    }

    public function getCountRecursosTipo($tiporecurso){

        $SQL =  "SELECT COUNT(distinct re.pk_recurso)
                    FROM tbl_recursos re
                    JOIN tbl_usuariosgrupos ug ON re.fk_usuariogrupo = ug.pk_usuariogrupo
                    JOIN tbl_usuarios u ON ug.fk_usuario = u.pk_usuario
                    JOIN tbl_atributos tr ON re.fk_tiporecurso = tr.pk_atributo
                    JOIN tbl_atributos c ON re.fk_coleccion = c.pk_atributo
                    WHERE tr.pk_atributo = $tiporecurso";

        $results = $this->_db->query($SQL);
        

        return $results->fetchAll();
    }

    public function getCountRecursosTipoEscuela($tiporecurso, $coleccion){

        $SQL =  "SELECT COUNT(distinct re.pk_recurso)
                    FROM tbl_recursos re
                    JOIN tbl_usuariosgrupos ug ON re.fk_usuariogrupo = ug.pk_usuariogrupo
                    JOIN tbl_usuarios u ON ug.fk_usuario = u.pk_usuario
                    JOIN tbl_atributos tr ON re.fk_tiporecurso = tr.pk_atributo
                    JOIN tbl_atributos c ON re.fk_coleccion = c.pk_atributo
                    WHERE tr.pk_atributo = $tiporecurso AND c.pk_atributo = $coleccion";

        $results = $this->_db->query($SQL);
        

        return $results->fetchAll();
    }

    public function getCedula($pk_usuario){

        $SQL =  "SELECT pk_usuario 
                    FROM tbl_recursos re 
                    JOIN tbl_usuariosgrupos ug ON re.fk_usuariogrupo = ug.pk_usuariogrupo
                    JOIN tbl_usuarios u ON ug.fk_usuario = u.pk_usuario
                    WHERE re.pk_recurso = $pk_usuario";
            
        return $this->_db->fetchOne($SQL);
    }

}