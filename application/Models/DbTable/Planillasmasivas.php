<?php
class Models_DbTable_Planillasmasivas extends Zend_Db_Table {
        
    
    public function init() {
        $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }
    
    
    public function getCedulasOnline($periodo , $estructura, $escuela ,$pensum ){
            $SQL = "SELECT u.pk_usuario
                FROM tbl_usuarios u 
                JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                JOIN tbl_inscripciones  i  ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                JOIN tbl_pensums        p  ON p.pk_pensum       = i.fk_pensum
                WHERE i.fk_periodo        = {$periodo} 
                AND   i.fk_estructura     = {$estructura} 
                AND   i.fk_atributo       = {$escuela} 
                AND   p.codigopropietario = {$pensum}
                AND   i.online = 't'";
                
                
        
       
         $results = $this->_db->query($SQL);
         $results = $results->fetchAll();
        return  $results;
    }
    
     public function getCedulasTodos($periodo , $estructura, $escuela ,$pensum ){
            $SQL = "SELECT u.pk_usuario
                FROM tbl_usuarios u 
                JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                JOIN tbl_inscripciones  i  ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                JOIN tbl_pensums        p  ON p.pk_pensum       = i.fk_pensum
                WHERE i.fk_periodo        = {$periodo} 
                AND   i.fk_estructura     = {$estructura}
                AND   i.fk_atributo       = {$escuela} 
                AND   p.codigopropietario = {$pensum}";
        
       
         $results = $this->_db->query($SQL);
         $results = $results->fetchAll();
        return  $results;
    }
    
    public function getCedulasNuevos($periodo , $estructura, $escuela ,$pensum ){
            $SQL = "select distinct u.pk_usuario, u.correo
                    from tbl_recordsacademicos   ra
                    join tbl_inscripciones       i   on i.pk_inscripcion = ra.fk_inscripcion
                    join tbl_usuariosgrupos      ug  on ug.pk_usuariogrupo = i.fk_usuariogrupo
                    join tbl_usuarios            u   on u.pk_usuario = ug.fk_usuario
                    join tbl_asignaturas         a   on a.pk_asignatura = ra.fk_asignatura
                    join tbl_pensums             pe  on pe.pk_pensum = a.fk_pensum
                    where i.fk_periodo = {$periodo}
                    and i.fk_estructura = {$estructura}
                    and i.fk_atributo = {$escuela}
                    and pe.codigopropietario = {$pensum}
                    and u.pk_usuario not in (
                                    select distinct u.pk_usuario
                                    from tbl_recordsacademicos   ra
                                    join tbl_inscripciones       i   on i.pk_inscripcion = ra.fk_inscripcion
                                    join tbl_usuariosgrupos      ug  on ug.pk_usuariogrupo = i.fk_usuariogrupo
                                    join tbl_usuarios            u   on u.pk_usuario = ug.fk_usuario
                                    join tbl_asignaturas         a   on a.pk_asignatura = ra.fk_asignatura
                                    join tbl_pensums             pe  on pe.pk_pensum = a.fk_pensum
                                    where i.fk_periodo < {$periodo}
                                                                    )";
        
       
         $results = $this->_db->query($SQL);
         $results = $results->fetchAll();
        return  $results;
    }
    
    
   
}

