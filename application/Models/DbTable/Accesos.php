<?php
class Models_DbTable_Accesos extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_accesos';
    protected $_primary  = 'pk_acceso';
    protected $_sequence = true;

    public function init() {
    }

    public function getRows($aplicacion) {
        $SQL = "SELECT * FROM {$this->_name} WHERE fk_aplicacion = {$aplicacion} ORDER BY ordinal, nombre;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    /**
     * Retorna una lista de accesos para generar el menu, la lista varia segun
     * la permisologia del usuario y la aplicación a la que se esta conectado.
     * 
     * @param int $userId     Codigo del usuario.
     * @param int $aplicacion Codigo de la aplicacion.
     * @return array
     */
    public function getMenu($userId, $aplicacion) {
       
        $aClientIP = explode('.', $_SERVER['REMOTE_ADDR']);

        $SQL = "SELECT DISTINCT a.pk_acceso, a.nombre, a.include, a.fk_acceso, a.ordinal
                FROM tbl_usuarios              u
                JOIN tbl_usuariosgrupos ug ON ug.fk_usuario  =  u.pk_usuario
                JOIN vw_grupos           g ON  g.pk_atributo = ug.fk_grupo
                JOIN tbl_accesosgrupos  ag ON ag.fk_grupo    =  g.pk_atributo
                JOIN tbl_accesos         a ON  a.pk_acceso   = ag.fk_acceso
     LEFT OUTER JOIN tbl_accesosip      ip ON ip.fk_acceso   = a.pk_acceso
                WHERE u.pk_usuario    = {$userId}
                  AND a.fk_aplicacion = {$aplicacion}
                  AND ag.visibility = true
                  AND (ip.client_ip = '0.0.0.0' 
                       OR ip.client_ip IS NULL 
                       OR ip.client_ip = '{$aClientIP[0]}.0.0.0'
                       OR ip.client_ip = '{$aClientIP[0]}.{$aClientIP[1]}.0.0'
                       OR ip.client_ip = '{$aClientIP[0]}.{$aClientIP[1]}.{$aClientIP[2]}.0'
                       OR ip.client_ip = '{$aClientIP[0]}.0.{$aClientIP[2]}.0'
                       OR ip.client_ip = '{$aClientIP[0]}.0.0.{$aClientIP[3]}'
                       OR ip.client_ip = '{$aClientIP[0]}.{$aClientIP[1]}.0.{$aClientIP[3]}'
                       OR ip.client_ip = '{$aClientIP[0]}.{$aClientIP[1]}.{$aClientIP[2]}.{$aClientIP[3]}')
                ORDER BY a.ordinal, a.nombre";

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

    public function getVisibility($pk_acceso){
        $SQL = "SELECT visibility FROM tbl_accesosgrupos WHERE pk_accesogrupo = {$pk_acceso}";

        $results = $this->_db->fetchOne($SQL);
        return $results;
    }

    public function setVisibility($pk_acceso, $visibility){

        $SQL = "UPDATE tbl_accesosgrupos SET visibility = {$visibility} WHERE pk_accesogrupo = {$pk_acceso}";

        $results = $this->_db->fetchOne($SQL);
        return $results;
    }
}
