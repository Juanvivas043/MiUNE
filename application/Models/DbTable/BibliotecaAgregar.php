<?php
class Models_DbTable_BibliotecaAgregar extends Zend_Db_Table { 

          private $materia   = 78; // materia local 73 , omicron 78
          private $autores   = 76;  // autores local 72 , omicron 76
          private $editorial = 77; // editorial local 74 , omicron 77
          
    public function init() {
        $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }
    
    public function getSQLCount($sede) {
        
        $SQL = "SELECT COUNT(pk_libro)
		FROM tbl_libros
                WHERE fk_sede = {$sede}";

        return $this->_db->fetchOne($SQL);
    }
    
    public function getDataLibro($cota){
        
        $SQL = "SELECT l.pk_libro , l.cota , l.titulo,ali.fk_autor,a1.valor as autor,ali.principal,l.fk_editorial, a5.valor as editorial,
                ml.fk_materiabiblioteca , a2.valor as materia,a3.pk_atributo as fk_pais , a3.valor as pais,a4.pk_atributo as fk_ciudad,a4.valor as ciudad,
                l.ano,l.pagina,l.nota,l.ejemplar,l.volumen,l.coleccion,l.numero
                FROM tbl_libros l  
                JOIN tbl_autoreslibros ali ON ali.fk_libro = l.pk_libro 
                JOIN tbl_atributos a1 ON a1.pk_atributo = ali.fk_autor
                LEFT JOIN tbl_materiaslibros ml ON ml.fk_libro = l.pk_libro
                LEFT JOIN tbl_atributos a2 ON a2.pk_atributo = ml.fk_materiabiblioteca 
                JOIN tbl_atributos a3 ON a3.pk_atributo = l.fk_pais
                JOIN tbl_atributos a4 ON a4.pk_atributo = l.fk_ciudad
                JOIN tbl_atributos a5 ON a5.pk_atributo = l.fk_editorial   
                WHERE l.cota ilike '{$cota}'";
                    
        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }
   
    public function getpais(){
        $SQL = "SELECT a.pk_atributo , a.valor as pais 
                FROM tbl_atributos a
                JOIN tbl_atributostipos ati ON ati.pk_atributotipo = a.fk_atributotipo
                WHERE ati.nombre ilike '%país%' 
                ORDER BY 2";
        
       
         $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }


    public function getbypais($pais){
        $SQL = "SELECT a.pk_atributo , a.valor as pais 
                FROM tbl_atributos a
                JOIN tbl_atributostipos ati ON ati.pk_atributotipo = a.fk_atributotipo
                WHERE a.valor ilike '{$pais}' 
                and ati.pk_atributotipo = 33
                ORDER BY 2";
        
       
         $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }    
    
    public function getsinpais(){
        $SQL = "SELECT a.pk_atributo , a.valor as pais 
                FROM tbl_atributos a
                JOIN tbl_atributostipos ati ON ati.pk_atributotipo = a.fk_atributotipo
                WHERE a.valor ilike 's.p.' 
                and ati.pk_atributotipo = 33
                ORDER BY 2";
        
       
         $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    } 

    public function getsedeform($pk){
        $SQL = "SELECT pk_estructura , nombre as sede 
                FROM vw_sedes
                WHERE pk_estructura = {$pk};";
        
       
         $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }
    
    public function getAutorlibro($limit){
                $SQL = "SELECT pk_atributo , valor as autor 
                        FROM tbl_atributos 
                        WHERE fk_atributotipo = {$this->autores}
                        ORDER BY valor DESC ";
                if($limit){        
                $SQL.= "limit 1"; 
                }       
                       
        
       
         $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
        
    }
    
    public function getciudad($pais){
        
        $SQL = "SELECT a.pk_atributo , a.valor as ciudad 
                FROM tbl_atributos a
                WHERE fk_atributo = {$pais}
                order by 2";
        
       
         $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }


    public function getbyciudad($pais,$ciudad){
        
        $SQL = "SELECT a.pk_atributo , a.valor as ciudad 
                FROM tbl_atributos a
                WHERE fk_atributo = {$pais}
                and a.valor ilike '{$ciudad}'
                order by 2";
        
       
         $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    } 

    public function getsinciudad(){
        
        $SQL = "SELECT a.pk_atributo , a.valor as ciudad 
                FROM tbl_atributos          a
                join tbl_atributostipos     at      on      at.pk_atributotipo = a.fk_atributotipo
                WHERE at.pk_atributotipo = 34
                and a.valor ilike 's.c.'
                order by 2";
        
       
         $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    } 

    public function getciudadbynombre($ciudad){
        
        $SQL = "SELECT a.pk_atributo , a.valor as ciudad, a1.valor as pais
                FROM tbl_atributos a
                JOIN tbl_atributos a1   on      a1.pk_atributo = a.fk_atributo
                WHERE a.valor ilike  '{$ciudad}'
                limit 1";
        
       
         $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }    

    
    public function getEditorial($b){
        if(empty($b)){
           $buscar = '';
           $limit = "Limit 1" ;
        }else{
           $buscar =  "AND valor ilike '%{$b}%'";
           $limit = 'Limit 10' ;
        }  
        $SQL ="SELECT pk_atributo , valor as editorial
               FROM tbl_atributos
               WHERE fk_atributotipo = {$this->editorial}
               $buscar
               ORDER BY valor ASC
               $limit;";
       
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
    }
    
    public function getEditoriales($editorial){
         $SQL ="SELECT pk_atributo  
               FROM tbl_atributos
               WHERE fk_atributotipo = {$this->editorial}
               AND valor = '{$editorial}'";
      
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
    }
    
    public function getMateria($b){  
        if(empty($b)){
           $buscar = '';
           $limit = "Limit 1" ;
        }else{
           $buscar =  "AND valor ilike '%{$b}%'";
           $limit = 'Limit 10' ;
        }
        $SQL ="SELECT pk_atributo , valor as materia
               FROM tbl_atributos
               WHERE fk_atributotipo = {$this->materia}
               $buscar
               ORDER BY valor ASC
               $limit;";
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
    }
    
    public function getMaterias($materia){
          
        $SQL ="SELECT pk_atributo  
               FROM tbl_atributos
               WHERE fk_atributotipo = {$this->materia}
               AND valor = '{$materia}'";
       
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
    }
    
    public function getAutor($autor){
          
        $SQL ="SELECT pk_atributo  
               FROM tbl_atributos
               WHERE fk_atributotipo = {$this->autores}
               AND valor = '{$autor}'";
        
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
    }

    public function getAutores($b){
        if(empty($b)){
           $buscar = '';
           $limit = "Limit 1" ;
        }else{
           $buscar =  "AND valor ilike '%{$b}%'";
           $limit = 'Limit 10' ;
        }
        $SQL ="SELECT pk_atributo , valor as autor
               FROM tbl_atributos
               WHERE fk_atributotipo = {$this->autores}
               $buscar
               ORDER BY valor ASC
               $limit;";
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
        
    }
    
    public function getPk_libro(){
    $SQL ="   SELECT pk_libro
              FROM tbl_libros
              ORDER by 1 desc
              limit 1";
   $results = $this->_db->query($SQL);
   return (array) $results->fetchAll();
   }
   
    public function getlibro($itemPerPage, $pageNumber ,$sede,$cota){
        $pageNumber = ($pageNumber - 1) * $itemPerPage;

        if(!empty($cota)){
            $filtro_cota = " AND l.cota ilike '{$cota}' ";
        }else{
            $filtro_cota = "  ";
        }

        $SQL = "SELECT pk_libro , cota , titulo ,fk_editorial ,a1.valor as editorial ,a2.valor as ciudad,
                ''as autor_principal,''as autor_otro
                FROM tbl_libros l
                JOIN tbl_atributos a1 ON l.fk_editorial = a1.pk_atributo
                JOIN tbl_atributos a2 ON l.fk_ciudad = a2.pk_atributo
                WHERE l.fk_sede = {$sede} ".$filtro_cota." 
                ORDER BY pk_libro DESC LIMIT {$itemPerPage} OFFSET {$pageNumber};";
      
      $results = $this->_db->query($SQL);
      return (array) $results->fetchAll();        
        
    }
    
    public function getlibro_cota($cota,$sede){

        if(!empty($sede)){
            $filtro_sede = "AND l.fk_sede = {$sede}";
        }else{
            $filtro_sede = " ";
        }
        
         $SQL = "select pk_libro , cota , titulo , edicion , fk_pais , fk_ciudad , a1.valor as ciudad , a4.valor as editorial,
                 fk_editorial, ano, pagina, volumen, ejemplar , nota , coleccion , numero, a2.pk_estructura as sede,a3.valor as pais
                 from tbl_libros l
                 JOIN tbl_atributos a1 ON a1.pk_atributo = l.fk_ciudad
                 JOIN vw_sedes a2 ON a2.pk_estructura = l.fk_sede
                 JOIN tbl_atributos a3 ON a3.pk_atributo = l.fk_pais
                 JOIN tbl_atributos a4 on a4.pk_atributo = l.fk_editorial
                 WHERE cota ilike '{$cota}' ". $filtro_sede ." order by 1 desc ";
               
       $results = $this->_db->query($SQL);
      return (array) $results->fetchAll();  
    }
    
    public function getlibro_cotas($cota,$sede){
        $SQL = "select distinct cota
                from tbl_libros
                where fk_sede = {$sede}
                and cota ilike '{$cota}'";

        return $this->_db->fetchOne($SQL);
        }

    public function get_autor($pk_libro ,$tipo){
        $SQL = "select pk_libro , btrim(autor::varchar,'NULL{}"."')as autor
                    from(
                    SELECT l.pk_libro,
                        (case when a1.valor = 'N/A' then ''
                         else a1.valor
                         end) as autor
                    FROM tbl_libros l
                    JOIN tbl_autoreslibros aul ON l.pk_libro     = aul.fk_libro
                    JOIN tbl_atributos a1      ON a1.pk_atributo = aul.fk_autor
                    WHERE principal = '{$tipo}'  
                    AND l.pk_libro in({$pk_libro})
                    group by 1,2) as sub_sqt";
        $results = $this->_db->query($SQL);
       return (array) $results->fetchAll();   
        
    }
    
    public function get_autor_tesis($pk_tesis){
        $SQL = "SELECT pk_tesis , btrim(autor::varchar,'NULL{}"."')as autor
	FROM (
        select t.pk_tesis,array_agg((u.primer_apellido || ', '||u.primer_nombre)) as autor  
        from tbl_tesis          t  
        join tbl_datostesis     dt      on      dt.pk_Datotesis     = t.fk_datotesis
        JOIN tbl_autorestesis   ts      ON      ts.fk_datotesis      = dt.pk_Datotesis       and ts.renuncia = false
        JOIN tbl_usuariosgrupos ug      ON      ug.pk_usuariogrupo  = ts.fk_usuariogrupo
        JOIN tbl_usuarios       u       ON      u.pk_usuario        = ug.fk_usuario
        WHERE t.pk_tesis in({$pk_tesis})
        GROUP BY 1 )AS sqt";

        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();   
    }

    public function getRowAutor($pk) {
         $SQL = "SELECT aul.pk_autorlibro,aul.fk_libro,fk_autor,a1.valor as autor,
                 CASE WHEN principal = 't' THEN 1 
                 WHEN principal      = 'f' THEN 2 end as principal
                 FROM tbl_libros l
                 JOIN tbl_autoreslibros aul ON aul.fk_libro      = l.pk_libro
                 JOIN tbl_atributos a1      ON a1.pk_atributo    = aul.fk_autor
                 WHERE pk_libro = {$pk};";
                
        $results = $this->_db->query($SQL);
       return (array) $results->fetchAll();   
    }
    
    public function getRowmateria($pk) {
      
        $SQL = "SELECT l.pk_libro,mal.fk_materiabiblioteca,mal.pk_materialibro,a1.valor as materia
                FROM tbl_libros l
                JOIN tbl_materiaslibros mal ON l.pk_libro = mal.fk_libro
                JOIN tbl_atributos a1       ON a1.pk_atributo = mal.fk_materiabiblioteca
                WHERE pk_libro = {$pk};";
        
                
        $results = $this->_db->query($SQL);
       return (array) $results->fetchAll();   
    }

    public function getRowLibro($id){
        
         $SQL = "select l.pk_libro ,l.cota , l.titulo , l.edicion , l.fk_pais, a1.valor as pais , l.fk_ciudad , a2.valor as ciudad,
	 l.fk_editorial , a3.valor as editorial , l.volumen , l.ejemplar ,l.pagina,
	 l.nota , l.coleccion , l.numero ,l.ano,l.fk_sede,se.nombre as sede
         from tbl_libros l
         JOIN tbl_atributos a1                    ON l.fk_pais = a1.pk_atributo
         JOIN tbl_atributos a2                    ON l.fk_ciudad = a2.pk_atributo
         JOIN tbl_atributos a3                    ON l.fk_editorial = a3.pk_atributo
         JOIN vw_sedes      se                    ON l.fk_sede      = se.pk_estructura
         where l.pk_libro = {$id};";
               
       $results = $this->_db->query($SQL);
       return (array) $results->fetchAll();        
    }
    
    public function updatelibro($id,$cota,$titulo,$edicion,$pais,$ciudad,$editorial,$ano,$pagina,$volumen,$ejemplar,$nota,$coleccion,$numero){
       
        if(empty($edicion)){
            $edicion = 'NULL';
        }
        
        if(empty($ano) || $ano == 'NULL'){
            $ano = 0;
        }
         if(empty($pagina)){
            $pagina = 0;
        }
         if(empty($volumen)){
            $volumen = 'NULL';
        }
         if(empty($ejemplar)){
            $ejemplar = 'NULL';
        }
         if(empty($nota)){
            $nota = 'NULL';
        }
         if(empty($coleccion)){
            $coleccion = 'NULL';
        }
         if(empty($numero)){
            $numero = 0;
        } 


        $SQL = "UPDATE tbl_libros
               SET cota='{$cota}', titulo='{$titulo}', edicion='{$edicion}', fk_pais={$pais}, fk_ciudad={$ciudad}, 
                   fk_editorial='{$editorial}', ano='{$ano}', pagina='{$pagina}', volumen='{$volumen}', ejemplar='{$ejemplar}', nota='{$nota}', 
                   coleccion='{$coleccion}', numero={$numero}
                   WHERE pk_libro = {$id};";
               
       $this->_db->query($SQL);
    }
  
    public function InsertLibro($cota,$titulo,$edicion,$pais,$ciudad,$editorial,$ano,$pagina,$volumen,$ejemplar,$nota,$coleccion,$numero,$sede){
        
        
        if(empty($edicion)){
            $edicion = 'NULL';
        }
        
        if(empty($ano)){
            $ano = 'NULL';
        }
         if(empty($pagina)){
            $pagina = 'NULL';
        }
         if(empty($volumen)){
            $volumen = 'NULL';
        }
         if(empty($ejemplar)){
            $ejemplar = 'NULL';
        }
        
         if(empty($nota)){
            $nota = 'NULL';
        }
         if(empty($coleccion)){
            $coleccion = 'NULL';
        }
         if(empty($numero)){
            $numero = 'NULL';
        }

         $SQL ="INSERT INTO tbl_libros(
            cota, titulo, edicion, fk_pais, fk_ciudad, fk_editorial, 
            ano, pagina, volumen, ejemplar, nota, coleccion, numero,fk_sede)
            VALUES ('{$cota}','{$titulo}','{$edicion}',{$pais},{$ciudad},{$editorial},{$ano}, 
            {$pagina},{$volumen},{$ejemplar},'{$nota}','{$coleccion}',{$numero},{$sede});";
      
       
         $this->_db->query($SQL); 
    } 
    
    public function InsertAutores($fk_libro,$fk_autor,$principal){
        if($principal=='1'){
            $principal = 'TRUE';
        }else{
             $principal = 'FALSE';
        }
         $SQL ="INSERT INTO tbl_autoreslibros(
              fk_autor, fk_libro, principal)
              VALUES ({$fk_autor},{$fk_libro},{$principal});";
         
         $this->_db->query($SQL); 
    }
   
    public function updateAutores($pk_autorlibro,$fk_autor,$fk_libro,$principal){
       if($principal=='1'){
            $principal = 'TRUE';
        }else{
            $principal = 'FALSE';
        }
      
         $SQL ="UPDATE tbl_autoreslibros
               SET fk_autor={$fk_autor}, fk_libro={$fk_libro}, principal={$principal}
               WHERE  pk_autorlibro = {$pk_autorlibro};";
        
         
         
         $this->_db->query($SQL);
    }

    public function InsertMateria($fk_libro,$fk_materia){
         $SQL ="INSERT INTO tbl_materiaslibros(
                fk_libro, fk_materiabiblioteca)
                VALUES ({$fk_libro},{$fk_materia});";
                
         $this->_db->query($SQL); 
    }
    
    public function InsertnewEditorial($pk,$editorial){
        $SQL = "INSERT INTO tbl_atributos(
                pk_atributo, fk_atributotipo, valor, id, fk_atributo)
                VALUES ({$pk}, $this->editorial, '{$editorial}', NULL, NULL);";
               
                   
       $this->_db->query($SQL);
       
   }
    
    public function InsertnewAutores($pk,$autor){
        $SQL = "INSERT INTO tbl_atributos(
                pk_atributo, fk_atributotipo, valor, id, fk_atributo)
                VALUES ({$pk}, $this->autores, '{$autor}', NULL, NULL);";
               
                   
       $this->_db->query($SQL);
       
   }
   
    public function InsertnewMateria($pk,$materia){
        $SQL = "INSERT INTO tbl_atributos(
                pk_atributo, fk_atributotipo, valor, id, fk_atributo)
                VALUES ({$pk}, $this->materia, '{$materia}', NULL, NULL);";
               
                   
       $this->_db->query($SQL);
       
   }


    public function InsertnewPais($pk,$pais){
        $SQL = "INSERT INTO tbl_atributos(
                pk_atributo, fk_atributotipo, valor, id, fk_atributo)
                VALUES ({$pk}, 33, '{$pais}', NULL, NULL);";
               
                   
       $this->_db->query($SQL);
       
   }


    public function InsertnewCiudad($pk,$ciudad,$fk_pais){
        $SQL = "INSERT INTO tbl_atributos(
                pk_atributo, fk_atributotipo, valor, id, fk_atributo)
                VALUES ({$pk}, 34, '{$ciudad}', NULL, {$fk_pais});";
               
                   
       $this->_db->query($SQL);
       
   } 

   
    public function updateMateria($pk_materialibro,$fk_libro,$fk_materiabiblioteca){
         
         $SQL ="UPDATE tbl_materiaslibros
                SET fk_libro = {$fk_libro}, fk_materiabiblioteca = {$fk_materiabiblioteca}
                WHERE pk_materialibro={$pk_materialibro};";
                
         $this->_db->query($SQL); 
    }

    public function deleteRow($id){
        //eliminar el autor
        $SQL1 =  "DELETE FROM tbl_autoreslibros
                 WHERE fk_libro = {$id}";         
        $this->_db->query($SQL1);
        
        //eliminar las materias
        $SQL2 =  "DELETE FROM tbl_materiaslibros
                 WHERE fk_libro = {$id}";
        $this->_db->query($SQL2);
        
        $SQL3 = "DELETE FROM tbl_libros
                WHERE pk_libro = {$id};";
        $this->_db->query($SQL3);
              
    }
    
    public function deleteAutores($pk_autorlibro){
        $SQL ="DELETE FROM tbl_autoreslibros
               WHERE pk_autorlibro = {$pk_autorlibro};";
        $this->_db->query($SQL);
    }
    
     public function deleteallAutores($id){
        $SQL ="DELETE FROM tbl_autoreslibros
               WHERE fk_libro = {$id};";
        $this->_db->query($SQL);
    }
    
    public function deleteallMaterias($id){
         $SQL =  "DELETE FROM tbl_materiaslibros
                 WHERE fk_libro = {$id}";
        $this->_db->query($SQL);
    }

    public function deleteMateria($pk_materialibro){
        $SQL ="DELETE FROM tbl_materiaslibros
               WHERE pk_materialibro = {$pk_materialibro};";
        $this->_db->query($SQL);
    }

    public function getSede($pk_libro){
         $SQL ="SELECT fk_sede
               FROM tbl_libros
               WHERE pk_libro = {$pk_libro};";
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
    }

    public function get_pkAtributo(){
       
       $SQL = "SELECT (pk_atributo +1) as pk_atributo
               FROM tbl_atributos 
               ORDER by 1 desc limit 1;";
               
                   
        $results = $this->_db->query($SQL);
        
        return (array) $results->fetchAll();
       
   }
   
   public function get_libro_prestado($id){
        $SQL = "select pk_prestamo  
                from tbl_prestamos pre
                JOIN tbl_prestamosarticulos art ON pre.pk_prestamo =  art.fk_prestamo
                JOIN tbl_libros l on l.cota = art.cota
                where l.pk_libro = {$id}
                limit 1;";
               
                   
        $results = $this->_db->query($SQL);
        
        return (array) $results->fetchAll();
   }
    
}
