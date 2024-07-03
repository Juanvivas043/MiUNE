<?php
class Models_DbTable_Bibliotecaestudiante extends Zend_Db_Table {

          private $materia   = 78; // materia local 73 , omicron 78
          private $autores   = 76;  // autores local 72 , omicron 76
          private $fk_asignacion = 19619;   //19617    // 19177
          
    public function init() {
        $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }
       
    public function get_autor($sede){
        
        $SQL ="select  pk_atributo,valor 
               from tbl_libros l
               JOIN tbl_autoreslibros li ON l.pk_libro = li.fk_libro
               JOIN tbl_atributos ati    ON ati.pk_atributo = li.fk_autor
               WHERE l.fk_sede = {$sede}
               group by 1,2
               order by 2;";
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
    }
    
    public function get_materia($tipo){
         $SQL ="select pk_atributo , valor
               from tbl_atributos
               where fk_atributotipo = {$this->materia};";
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
    }

    public function get_libro($itemPerPage, $pageNumber,$sede,$buscar){
        $pageNumber = ($pageNumber - 1) * $itemPerPage;
         if($buscar != ""){
            $Order = "";
        }else{
            $Order = " ORDER BY  pk_libro DESC LIMIT {$itemPerPage} OFFSET {$pageNumber}";
        }
        $SQL ="SELECT pk_libro , cota , titulo ,fk_editorial ,a1.valor as editorial ,a2.valor as ciudad,l.ano,l.pagina,
                ''as autor_principal,''as autor_otro
                FROM tbl_libros l
                JOIN tbl_atributos a1 ON l.fk_editorial = a1.pk_atributo
                JOIN tbl_atributos a2 ON l.fk_ciudad = a2.pk_atributo
                JOIN tbl_autoreslibros li ON l.pk_libro = li.fk_libro
                JOIN tbl_materiaslibros mat ON l.pk_libro = mat.fk_libro
                WHERE l.fk_sede = {$sede} 
                GROUP BY 1,2,3,4,5,6,7,8,9,10
                ORDER BY pk_libro DESC LIMIT {$itemPerPage} OFFSET {$pageNumber};";
         
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
         
    }
    
    public function get_tesis($itemPerPage, $pageNumber,$sede,$buscar){
        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        if($buscar != ""){
            $Order = "";
        }else{
            $Order = " ORDER BY pk_tesis DESC LIMIT {$itemPerPage} OFFSET {$pageNumber}";
        }
        $SQL ="
              SELECT pk_tesis,cota,dt.titulo,pagina,calificacion,ve.escuela,
                  (
                    select distinct btrim(array_agg(u_sub.primer_apellido || ', ' || u_sub.primer_nombre)::varchar,'NULL{}.')
                    from tbl_datostesis     dt_sub
                    join tbl_tesis        t_sub     on    t_sub.fk_datotesis    = dt_sub.pk_datotesis
                    join tbl_autorestesis     at_sub    on    at_sub.fk_datotesis   =   dt_sub.pk_datotesis     and at_sub.renuncia = false
                    join tbl_usuariosgrupos   ug_sub    on    ug_sub.pk_usuariogrupo  = at_sub.fk_usuariogrupo
                    join tbl_usuarios       u_sub     on    u_sub.pk_usuario    =   ug_sub.fk_usuario
                    where dt_sub.pk_datotesis = dt.pk_datotesis
                  ) as autor
              from tbl_datostesis           dt 
              left outer join tbl_tesis         t       on    t.fk_datotesis    =   dt.pk_datotesis
              left outer join tbl_tutorestesis    tt      on    tt.fk_datotesis   =   dt.pk_datotesis     and tt.renuncia = false
              left outer join vw_escuelas       ve      on    ve.pk_atributo    =   t.fk_escuela
              where dt.fk_estado = (
                          SELECT distinct a.pk_atributo
                                from tbl_atributos      a 
                                join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                where at.pk_atributotipo = (select pk_atributotipo from tbl_atributostipos where nombre ilike 'Estado Tesis' )
                                and a.valor ilike 'Aprobado')
              and tt.fk_estado = (select distinct a.pk_atributo
                                from tbl_atributos      a 
                                join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                where at.nombre ilike '%Estado Tutores%'
                                and a.valor ilike 'Aprobado%')
               " . $Order;
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
        
    }
    
    public function get_usuariogrupo ($cedula){
        $SQL ="SELECT ug.pk_usuariogrupo , g.grupo,
                CASE WHEN fk_grupo = 855 then 1 WHEN fk_grupo = 1745 then 2 WHEN fk_grupo = 854 then 3 end as orden  
                FROM tbl_usuariosgrupos ug
                JOIN vw_grupos g ON g.pk_atributo = ug.fk_grupo
                WHERE ug.fk_usuario = {$cedula}
                AND fk_grupo in (855,854,1745)
                ORDER by orden desc
                LIMIT 1;";
       $results = $this->_db->query($SQL);
       return (array) $results->fetchAll();         
    }
    
    public function insertar_fichaprestamo($usuariogrupo){
     $SQL ="   INSERT INTO tbl_prestamos(fk_usuariogrupo, fecha_prestamo)
                VALUES ({$usuariogrupo}, current_date)";
     $this->_db->query($SQL);            
    }
    
    public function get_pkprestamo($usuariogrupo){
       $SQL =" select pk_prestamo
               from tbl_prestamos
               where fk_usuariogrupo = {$usuariogrupo}
               order by 1 desc
               limit 1 ;";
      $results = $this->_db->query($SQL);
      return (array) $results->fetchAll();          
    }
    
    public function insert_prestamo($fk_prestamo ,$cota){
         $SQL ="INSERT INTO tbl_prestamosarticulos(
                    fk_prestamo,fecha_devolucion, fecha_entrega, 
                    cota, fk_asignacion, comentario, fk_tipo_interno)
                    VALUES ({$fk_prestamo},current_date,null, '$cota', $this->fk_asignacion,null,null);";
     $this->_db->query($SQL);    
    }
    
    public function getSQLCountLibros($sede) {
       // $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

        $SQL = "SELECT COUNT(pk_libro)
		FROM tbl_libros l
                WHERE l.fk_sede = {$sede}";

        return $this->_db->fetchOne($SQL);
    }
    
    public function getSQLCountTesis() {
       // $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

        $SQL = "SELECT COUNT(pk_tesis)
		FROM tbl_tesis t";

        return $this->_db->fetchOne($SQL);
    }
       
    public function getFilter($autor ,$materia){
        $where = "";
        if($autor != -1 && $materia != -1){
         $where = " AND fk_autor = {$autor} AND li.principal = true AND fk_materiabiblioteca = {$materia} ";
        }else{
            if($autor != -1){
              $where =" AND fk_autor = {$autor} AND li.principal = true";
            }
             if($materia != -1){
              $where =" AND fk_materiabiblioteca = {$materia}";  
            }
        }
        return $where;
    }
}

