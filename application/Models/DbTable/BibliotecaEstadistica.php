<?php
class Models_DbTable_BibliotecaEstadistica extends Zend_Db_Table {
        
    
    public function init() {
     
    }
    
    public function getSQLCount() {
        
        $SQL = "SELECT COUNT(pk_libro)
		FROM tbl_libros";

        return $this->_db->fetchOne($SQL);
    }
    
    public function getData($Grupo, $Estado, $tipo,$fecha_ini,$fecha_fin) {
      
      if($Grupo > 0){
          $fk_grupo = " AND fk_grupo = {$Grupo} ";
      }ELSE{
          $fk_grupo= " ";
      }  
        
      if($Estado > 0){
          $fk_asignacion = " And art.fk_asignacion = {$Estado} ";
      }ELSE{
          $fk_asignacion= " ";
      }
      
      
        if ($tipo == '1'){
         
            $SQL = "
            SELECT *,(administracion + computacion + diseno + electronica + turismo + na) as total
            FROM(
            SELECT count(administracion) as administracion ,count(computacion) as computacion,
                    count(diseno) as diseno ,count(civil) as civil,count(electronica) as electronica,
                    count(turismo) as turismo ,count(na) as na   
                FROM(
                SELECT 		        CASE WHEN fk_escuela = 11  then 1 end as administracion,
                                        CASE WHEN fk_escuela = 12  then 1 end as computacion,
                                        CASE WHEN fk_escuela = 13  then 1 end as diseno,
                                        CASE WHEN fk_escuela = 14  then 1 end as civil,
                                        CASE WHEN fk_escuela = 15  then 1 end as electronica,
                                        CASE WHEN fk_escuela = 16  then 1 end as turismo,
                                        CASE WHEN fk_escuela = 920 then 1 end as na
                FROM (
                SELECT *
                ,CASE WHEN fk_grupo = 855 THEN (SELECT fk_atributo 
                                                        FROM tbl_inscripciones i
                                                        WHERE fk_usuariogrupo = sqt1.fk_usuariogrupo 
                                                        ORDER BY fk_periodo DESC
                                                        limit 1)

                                                        WHEN fk_grupo =  854  THEN (select p.fk_escuela
                                                                                    from tbl_asignaciones asi
                                                                                    JOIN tbl_asignaturas asig ON asig.pk_asignatura = asi.fk_asignatura
                                                                                    JOIN tbl_pensums p ON p.pk_pensum = asig.fk_pensum
                                                                                    WHERE fk_usuariogrupo = sqt1.fk_usuariogrupo
                                                                                    limit 1 
                                                                                    )
                                                        WHEN  fk_grupo = 1745  THEN 920 end as fk_escuela
                FROM(
                SELECT art.cota,pre.fecha_prestamo,art.fecha_entrega,ug.fk_grupo,a1.valor as grupo ,a2.valor as estado,ug.fk_usuario as Cedula ,pre.fk_usuariogrupo
                FROM tbl_prestamos pre
                JOIN tbl_prestamosarticulos art ON art.fk_prestamo = pre.pk_prestamo
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = pre.fk_usuariogrupo
                JOIN tbl_atributos a1 ON a1.pk_atributo = ug.fk_grupo
                JOIN tbl_atributos a2 ON a2.pk_atributo = art.fk_asignacion
                WHERE pre.fecha_prestamo 
                BETWEEN '{$fecha_ini}' and '{$fecha_fin}' 
                $fk_asignacion
                $fk_grupo
                AND art.cota not ilike 'TG%'
                )AS sqt1
                )AS sqt2
                )AS sqt3
                )AS sqt4
                ";
        }else{
           
           $SQL = "SELECT *,(administracion + computacion + diseno + electronica + turismo + na) as total
            FROM(
            SELECT count(administracion) as administracion ,count(computacion) as computacion,
                    count(diseno) as diseno ,count(civil) as civil,count(electronica) as electronica,
                    count(turismo) as turismo ,count(na) as na   
                FROM(
                SELECT 		        CASE WHEN fk_escuela = 11  then 1 end as administracion,
                                        CASE WHEN fk_escuela = 12  then 1 end as computacion,
                                        CASE WHEN fk_escuela = 13  then 1 end as diseno,
                                        CASE WHEN fk_escuela = 14  then 1 end as civil,
                                        CASE WHEN fk_escuela = 15  then 1 end as electronica,
                                        CASE WHEN fk_escuela = 16  then 1 end as turismo,
                                        CASE WHEN fk_escuela = 920 then 1 end as na
                FROM (
                SELECT *
                ,CASE WHEN fk_grupo = 855 THEN (SELECT fk_atributo 
                                                        FROM tbl_inscripciones i
                                                        WHERE fk_usuariogrupo = sqt1.fk_usuariogrupo 
                                                        ORDER BY fk_periodo DESC
                                                        limit 1)

                                                        WHEN fk_grupo =  854  THEN (select p.fk_escuela
                                                                                    from tbl_asignaciones asi
                                                                                    JOIN tbl_asignaturas asig ON asig.pk_asignatura = asi.fk_asignatura
                                                                                    JOIN tbl_pensums p ON p.pk_pensum = asig.fk_pensum
                                                                                    WHERE fk_usuariogrupo = sqt1.fk_usuariogrupo 
                                                                                    limit 1 
                                                                                    )
                                                        WHEN  fk_grupo = 1745  THEN 920 end as fk_escuela
                FROM(
                SELECT art.cota,pre.fecha_prestamo,art.fecha_entrega,ug.fk_grupo,a1.valor as grupo ,a2.valor as estado,ug.fk_usuario as Cedula ,pre.fk_usuariogrupo
                FROM tbl_prestamos pre
                JOIN tbl_prestamosarticulos art ON art.fk_prestamo = pre.pk_prestamo
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = pre.fk_usuariogrupo
                JOIN tbl_atributos a1 ON a1.pk_atributo = ug.fk_grupo
                JOIN tbl_atributos a2 ON a2.pk_atributo = art.fk_asignacion
                WHERE pre.fecha_prestamo 
                BETWEEN '{$fecha_ini}' and '{$fecha_fin}'
                $fk_asignacion
                $fk_grupo
                AND art.cota ilike 'TG%'
                )AS sqt1
                )AS sqt2
                )AS sqt3
                )As sqt4
                ";
            
        }
      
      // echo $SQL;
        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }
   
 
}
?>