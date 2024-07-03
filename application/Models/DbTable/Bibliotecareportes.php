<?php

class Models_DbTable_Bibliotecareportes extends Zend_Db_Table {

    private $Prestamoval   = 8242;
    private $Moraval       = 8244;
    private $Devueltoval   = 8243;
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
    
    public function getRow($id) {
        $id = (int)$id;
        $row = $this->fetchRow($this->_primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
    }
    
    public function getlistreport(){
         $SQL = "SELECT solicitud,pk_usuario,nombre,apellido,fecha_prestamo,perfil,estado,cota         
          FROM (
                SELECT solicitud, pk_usuario, nombre, apellido, perfil,correo, estado,fecha_prestamo,cota 
                FROM (SELECT solicitud, pk_usuario, nombre, apellido,correo, perfil,fecha_prestamo,cota,
                CASE WHEN mora > 0 THEN 'Mora'
                WHEN mora = 0 AND prestamo > 0 THEN 'Transito'
                WHEN mora = 0 AND prestamo = 0 AND devuelto > 0 THEN 'Solvente'
                ELSE 'Vacio' END as estado
                FROM(
                    SELECT solicitud, pk_usuario, nombre, apellido,correo, perfil,fecha_prestamo,cota,
                        SUM(mora) as mora,
                        SUM(prestamo) as prestamo,
                        SUM(devuelto) as devuelto
                        FROM(
                        SELECT solicitud, pk_usuario, nombre, apellido,correo, perfil,fecha_prestamo,cota,
                        CASE WHEN fk_asignacion = $this->Moraval THEN 1 ELSE 0 END as mora,
                        CASE WHEN fk_asignacion = $this->Prestamoval THEN 1 ELSE 0 END as prestamo,
                        CASE WHEN fk_asignacion = $this->Devueltoval THEN 1 ELSE 0 END as devuelto

                            FROM(
                                 SELECT p.pk_prestamo as solicitud , u.pk_usuario , u.nombre , u.apellido ,u.correo,gr.grupo as perfil, p.fecha_prestamo , preart.fk_asignacion , preart.cota
                                 FROM tbl_usuarios u 
                                 JOIN tbl_usuariosgrupos gp ON gp.fk_usuario = u.pk_usuario
                                 JOIN tbl_prestamos p ON p.fk_usuariogrupo = gp.pk_usuariogrupo
                                 left outer join tbl_prestamosarticulos preart ON preart.fk_prestamo = p.pk_prestamo
                                 JOIN vw_grupos gr ON gr.pk_atributo = gp.fk_grupo
                                 GROUP BY 1,2,3,4,5,6,7,8,9
                                 ) as sqt) as sqt2
        GROUP BY 1,2,3,4,5,6,7,8) as sqt3) as sqt4) as sqt5
        WHERE 1=1 
        ORDER BY 5,1;";
        
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
    }













    public function setSearch($searchData) {
        $this->searchData = $searchData;
    }
    
   

   
}


