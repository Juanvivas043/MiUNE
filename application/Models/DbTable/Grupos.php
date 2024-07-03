<?php

class Models_DbTable_Grupos extends Zend_Db_Table {

    public function init() {
        
    }

    public function getUsuarioestudiante($ci) {

        $SQL ="SELECT u.nombre , u.apellido , a.valor
               FROM tbl_usuarios u
               JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
               JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
               JOIN tbl_atributos a ON a.pk_atributo = i.fk_atributo
               WHERE u.pk_usuario = $ci
               GROUP BY a.valor , u.nombre , u.apellido;";
       
        
       
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function getUsuariodocente($ci){
        
        $SQL ="SELECT u.nombre , u.apellido
               FROM tbl_usuarios u
               JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
               WHERE u.pk_usuario = $ci
               GROUP BY  u.nombre , u.apellido;";
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function getCuadro($ci) {

        $SQL = "SELECT gr.grupo , gr.pk_atributo
               FROM tbl_usuarios u 
               JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
               JOIN vw_grupos gr ON gr.pk_atributo = ug.fk_grupo
               WHERE pk_usuario =$ci
               ORDER BY gr.grupo = 'Estudiante'DESC ;";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function getCuadron($ci) {

        $SQL = "SELECT pk_atributo , grupo  
               FROM vw_grupos
               WHERE pk_atributo NOT IN (SELECT gr.pk_atributo
               FROM tbl_usuarios u 
               JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
               JOIN vw_grupos gr ON gr.pk_atributo = ug.fk_grupo
               WHERE pk_usuario =$ci)
               ORDER BY grupo = 'Docente'DESC;";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function getCambio_s($ci, $value) {

        $SQL = "INSERT INTO tbl_usuariosgrupos(fk_usuario,fk_grupo)
                VALUES ($ci,$value);";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function getCambio_n($ci, $value) {


        $SQL = "DELETE FROM tbl_usuariosgrupos
                WHERE fk_usuario = $ci and fk_grupo = $value
                AND pk_usuariogrupo         NOT IN (SELECT i.fk_usuariogrupo
                                            from tbl_inscripciones i
                                            JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                                            WHERE ug.fk_usuario = $ci)
                AND pk_usuariogrupo         NOT IN (SELECT a.fk_usuariogrupo
                                            from tbl_asignaciones a
                                            JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = a.fk_usuariogrupo
                                            WHERE ug.fk_usuario = $ci);";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    
    public function getCambiopass($ci){
      $SQL = " UPDATE tbl_usuarios
               SET passwordhash = MD5('$ci')
               WHERE pk_usuario = $ci ;";
      
      $results = $this->_db->query($SQL);
      $results = $results->fetchAll();

        return $results;
    }
}

?>
