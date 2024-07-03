<?php

class Models_DbTable_Prestamo extends Zend_Db_Table {

    private $Prestamoval   = 8242;
    private $Moraval       = 8244;
    private $Devueltoval   = 8243;
    private $Esperaval     = 19619;
    
    protected $_schema = 'produccion';
    protected $_name = 'tbl_prestamos';
    protected $_primary = 'pk_prestamo';
    protected $_sequence = false;
    private $searchParams = array('solicitud','pk_usuario', 'nombre', 'apellido','perfil', 'estado','fecha_prestamo','numeroart','cota','fk_sede');

    public function init() {
        $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');

        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }

    public function getSedes(){
         $SQL = "select pk_estructura, nombre
                from tbl_estructuras
                where pk_estructura in (7,8)";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }    

    public function getListUsuarioprestamo($val,$sede = 7){
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
       
        $ord = $this->getorden($val); 
        $SQL = "SELECT *, CASE 
          WHEN estado = 'Vacio' then 1
          WHEN estado = 'Mora' then 2
          WHEN estado = 'Espera' then 3  
          WHEN estado = 'Transito'then 4 
          WHEN estado = 'Solvente' then 5 end as orden 
          
          FROM (
                SELECT solicitud, pk_usuario, nombre, apellido, perfil,correo, estado,fecha_prestamo,btrim(cota::varchar,'NULL{}"."')as cota,numeroart,fk_sede
                FROM (SELECT solicitud, pk_usuario, nombre, apellido,correo, perfil,fecha_prestamo,cota,numeroart,
                CASE WHEN mora > 0 THEN 'Mora'
                WHEN mora = 0 AND prestamo > 0 THEN 'Transito' 
                WHEN mora = 0 AND prestamo = 0 AND devuelto > 0 THEN 'Solvente'
                WHEN mora = 0 AND prestamo = 0 AND devuelto = 0 AND espera >0 THEN 'Espera'
                ELSE 'Vacio' END as estado,
                fk_sede
                FROM(
                    SELECT solicitud, pk_usuario, nombre, apellido,correo, perfil,fecha_prestamo,cota,numeroart,
                        SUM(mora) as mora,
                        SUM(prestamo) as prestamo,
                        SUM(devuelto) as devuelto,
                        SUM(espera)   as espera,
                        fk_sede
                        FROM(
                        SELECT solicitud, pk_usuario, nombre, apellido,correo, perfil,fecha_prestamo,cota,numeroart,
                        CASE WHEN fk_asignacion = 8244 THEN 1 ELSE 0 END as mora,
                        CASE WHEN fk_asignacion = 8242 THEN 1 ELSE 0 END as prestamo,
                        CASE WHEN fk_asignacion = 8243 THEN 1 ELSE 0 END as devuelto,
			CASE WHEN fk_asignacion = 19619 THEN 1 ELSE 0 END as espera,
      fk_sede
                            FROM(
                                 SELECT p.pk_prestamo as solicitud , u.pk_usuario , u.nombre , u.apellido, u.correo , gr.grupo as perfil , p.fecha_prestamo , preart.fk_asignacion , sqt0.cota ,count(preart.pk_prestamoarticulo)as numeroart
                                 ,sqt0.fk_sede
				 FROM(
				 SELECT p.pk_prestamo, array_agg(cota) as cota,fk_sede
                                 FROM tbl_prestamos p
				 left outer join tbl_prestamosarticulos preart ON preart.fk_prestamo = p.pk_prestamo
         
				 GROUP by 1,3)as sqt0
				 JOIN tbl_prestamos p ON sqt0.pk_prestamo = p.pk_prestamo
				 JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = p.fk_usuariogrupo
			         JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
				left outer join tbl_prestamosarticulos preart ON preart.fk_prestamo = p.pk_prestamo
				JOIN vw_grupos gr ON gr.pk_atributo = ug.fk_grupo
        where sqt0.fk_sede = {$sede}
				GROUP BY 1,2,3,4,5,6,7,8,9,11
        
                                 ) as sqt) as sqt2
        GROUP BY 1,2,3,4,5,6,7,8,9,14) as sqt3) as sqt4) as sqt5
        WHERE 1=1 {$whereSearch} {$ord}
        ORDER BY 11  ASC  , 8 DESC;";
        
        $results = $this->_db->query($SQL);
        
        return (array) $results->fetchAll();
    }    
    
    public function getSQLCount() {
        
        $SQL = "SELECT COUNT(pk_prestamo)
		FROM tbl_prestamos";

        return $this->_db->fetchOne($SQL);
    }
    
    public function getUsuarioprestamo($ci){
         $SQL = "SELECT u.pk_usuario , u.apellido , u.nombre ,u.direccion , u.telefono 
                 FROM tbl_usuarios u 
                 WHERE u.pk_usuario = $ci ;";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }
    
    public function getRow($id) {
        $id = (int)$id;
        $row = $this->fetchRow($this->_primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
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
    
    public function deleteRow($id) {
		if(!is_numeric($id)) return null;

        $affected = $this->delete($this->_primary . ' = ' . (int) $id);

        return $affected;
    }

   public function getperfil($ci){
        $SQL = "SELECT gp.pk_usuariogrupo ,g.grupo as perfil 
                FROM tbl_usuarios u 
                JOIN tbl_usuariosgrupos gp ON gp.fk_usuario =u.pk_usuario
                JOIN vw_grupos g ON g.pk_atributo = gp.fk_grupo
                WHERE u.pk_usuario = $ci AND (g.pk_atributo = 855 OR g.pk_atributo = 854 OR g.pk_atributo =1745)";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
}

   public function getPhoto($id) {
		if(!is_numeric($id)) return;
		
		$config = $this->_db->getConfig();
		$conn   = pg_connect("user={$config['username']} password={$config['password']} dbname={$config['dbname']} host={$config['host']}");
		$query  = pg_query($conn, "SELECT foto FROM tbl_usuarios WHERE pk_usuario = {$id}");
		$row    = pg_fetch_row($query);
		$image  = pg_unescape_bytea($row[0]);

		pg_close($conn);

		/*
		 * En caso de que no exista la imagen en la DB, se procede a cargar una
		 * imagen generica desde el sistema de archivos:
		 */
		if(empty($image)) {
			$image = file_get_contents(APPLICATION_PATH . '/../public/images/empty_profile.jpg');
		}
		
		return $image;
	}
        
   public function getview($id){
               $SQL = "SELECT *,CASE
                       WHEN pk_atributo = 1745 then 0
                       WHEN pk_atributo = 854  then (  select es2.fk_estructura
                       from tbl_asignaciones a
                       join tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = a.fk_usuariogrupo
                       join tbl_estructuras es ON es.pk_estructura = a.fk_estructura
                       join tbl_estructuras es2 ON es.fk_estructura = es2.pk_estructura
                       where ug.fk_usuario = pk_usuario limit 1)
                       WHEN pk_atributo = 855  then (SELECT e.pk_estructura 
                       FROM tbl_inscripciones i 
                       JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                       JOIN tbl_estructuras e ON e.pk_estructura = i.fk_estructura
                       WHERE fk_usuario = pk_usuario 
                      ORDER BY i.fk_periodo DESC limit 1)end estructura
                       FROM(
                       SELECT *,CASE
                       WHEN pk_atributo = 1745 then 'No Aplica'
                       WHEN pk_atributo = 854  then 'No Aplica'
                       WHEN pk_atributo = 855  then (select a.valor 
		       FROM tbl_inscripciones i 
                       JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                       JOIN tbl_atributos a       ON a.pk_atributo      = i.fk_atributo 
                       WHERE ug.fk_usuario = pk_usuario
                       ORDER BY i.fk_periodo DESC limit 1)
                       END as Escuela FROM(
                       SELECT u.pk_usuario ,u.nombre , u.apellido ,u.direccion ,u.telefono, u.telefono_movil, u.correo,gp.pk_usuariogrupo ,vg.pk_atributo, vg.grupo as perfil
                       FROM tbl_prestamos p
                       JOIN tbl_usuariosgrupos gp ON gp.pk_usuariogrupo = p.fk_usuariogrupo
                       JOIN tbl_usuarios u ON u.pk_usuario = gp.fk_usuario 
                       JOIN vw_grupos vg ON gp.fk_grupo = vg.pk_atributo
                       WHERE pk_prestamo = {$id})sqt1)sqt2;";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
        }
     
   public function getHorarioPersona($id, $periodo, $quien, $sede){

        if($quien == 'prof') {

            $SQL ="select foo1.pk_atributo, foo1.horainicio, foo.dia, foo.lugar, foo.materia,diaint
                    FROM
                    (select pk_atributo, ho.horainicio
                     from vw_dias
                     CROSS JOIN tbl_horarios ho
                     where ho.fk_atributo < 892
                       and pk_atributo < 7
                     order by pk_atributo, ho.horainicio) as foo1 left outer join
                    (
                    select hora,
                           dia,
                           lugar,
                           TRIM( both ' ' FROM REPLACE(trim(both '{}' FROM materia::text), '\"' , ' ')) as materia,
                           diaint,
                           horaint

                    FROM fn_xrxx_incio_horario_profesor({$periodo}, {$id}) as (hora time, dia VARCHAR, lugar TEXT, sede integer, materia text[], diaint bigint, horaint bigint)
                    order by 5,6
                    ) as foo ON (foo1.horainicio = foo.hora AND foo1.pk_atributo = foo.diaint)

                    order by 6;";


        }elseif($quien == 'est') {

        $SQL = "SELECT hora,
                               dia,
                               lugar,
                               TRIM( both ' ' FROM REPLACE(trim(both '{}' FROM materia::text), '\"' , ' ')) as materia,
                               diaint,
                               horaint,
                               turnoreal,
                               prof

                        FROM fn_xrxx_inicio_horario_estudiante({$periodo}, {$id}, {$sede})
                     as (hora time, dia VARCHAR, lugar TEXT, materia text[], diaint bigint, horaint bigint, turnoreal integer, prof TEXT)
                        order by 5;";
        }


        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;

    }    
        
   public function getUltimoPeriodo(){
        $SQL = "SELECT pk_periodo
                FROM tbl_periodos 
                ORDER by pk_periodo DESC limit 1;";
        
        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
   } 
    
   public function updateEstado(){
      
      $SQL ="UPDATE tbl_prestamosarticulos
            SET fk_asignacion = 
            ( CASE WHEN fecha_devolucion < current_date AND fk_asignacion <> $this->Devueltoval AND fk_asignacion <> $this->Esperaval
              THEN $this->Moraval
       
             WHEN fecha_devolucion >= current_date AND fk_asignacion <> $this->Devueltoval AND fk_asignacion <> $this->Esperaval
             THEN $this->Prestamoval
            
             WHEN fk_asignacion = $this->Esperaval
             THEN $this->Esperaval
              
            ELSE $this->Devueltoval
            END)

            WHERE fk_asignacion <>  $this->Devueltoval ;"; 

       
            $this->_db->query($SQL);
       
   } 
   
   public function getestadoArticulo($id){
         $SQL = "SELECT fk_asignacion
                 From tbl_prestamosarticulos
                 WHERE fk_prestamo = $id;";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
   }
   
   public function contArticulo($id){
        $SQL = "SELECT COUNT (fk_asignacion)
                 From tbl_prestamosarticulos
                 WHERE fk_prestamo = $id;";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
   }
   
   public function getMORA($pk_usuario){
        $SQL = "SELECT pk_prestamo 
                FROM tbl_prestamos p
                JOIN tbl_usuariosgrupos gp ON gp.pk_usuariogrupo = p.fk_usuariogrupo
                JOIN tbl_usuarios u ON u.pk_usuario = gp.fk_usuario
                JOIN tbl_prestamosarticulos pa ON pa.fk_prestamo = p.pk_prestamo
                WHERE u.pk_usuario = $pk_usuario AND(pa.fk_asignacion = $this->Moraval);";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
   }
   
   public function getPrestamo($pk_usuario){
        $SQL = "SELECT pk_prestamo 
                FROM tbl_prestamos p
                JOIN tbl_usuariosgrupos gp ON gp.pk_usuariogrupo = p.fk_usuariogrupo
                JOIN tbl_usuarios u ON u.pk_usuario = gp.fk_usuario
                JOIN tbl_prestamosarticulos pa ON pa.fk_prestamo = p.pk_prestamo
                WHERE u.pk_usuario = $pk_usuario AND(pa.fk_asignacion = $this->Prestamoval);";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
   }
   
   public function getDevuelto($pk_usuario){
        $SQL = "SELECT pk_prestamo 
                FROM tbl_prestamos p
                JOIN tbl_usuariosgrupos gp ON gp.pk_usuariogrupo = p.fk_usuariogrupo
                JOIN tbl_usuarios u ON u.pk_usuario = gp.fk_usuario
                JOIN tbl_prestamosarticulos pa ON pa.fk_prestamo = p.pk_prestamo
                WHERE u.pk_usuario = $pk_usuario AND(pa.fk_asignacion = $this->Devueltoval);";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
   }
    
   public function getEspera($pk_usuario){
        $SQL = "SELECT pk_prestamo 
                FROM tbl_prestamos p
                JOIN tbl_usuariosgrupos gp ON gp.pk_usuariogrupo = p.fk_usuariogrupo
                JOIN tbl_usuarios u ON u.pk_usuario = gp.fk_usuario
                JOIN tbl_prestamosarticulos pa ON pa.fk_prestamo = p.pk_prestamo
                WHERE u.pk_usuario = $pk_usuario AND(pa.fk_asignacion = $this->Esperaval);";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
   }
   
   public function getUsuarioinfo($pk_prestamo){
       $SQL ="SELECT p.pk_prestamo ,u.pk_usuario as ci , u.nombre , u.apellido , wg.grupo , p.fecha_prestamo as fecha_solicitud  
              FROM tbl_prestamos p
              JOIN tbl_usuariosgrupos gp ON p.fk_usuariogrupo = gp.pk_usuariogrupo 
              JOIN vw_grupos wg ON wg.pk_atributo = gp.fk_grupo
              JOIN tbl_usuarios u ON u.pk_usuario = gp.fk_usuario 
              WHERE p.pk_prestamo = $pk_prestamo;";
       $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
   }
   
   public function getDataUsuario($pk_usuario){
        $SQL ="Select *,CASE
               WHEN fk_grupo = 855  THEN 1 
               WHEN fk_grupo = 854  THEN 2 
               WHEN fk_grupo = 1745 THEN 3 end as orden 
               FROM(
                    select *,CASE 
                    WHEN fk_grupo = 855 
                    then(select a.valor
                         from tbl_inscripciones i
                         join tbl_atributos a ON a.pk_atributo = i.fk_atributo 
                         where i.fk_usuariogrupo = pk_usuariogrupo
                         order by i.fk_periodo DESC limit 1 )
                         WHEN fk_grupo = 854
                         then 'No Aplica'
                         WHEN fk_grupo = 1745
                         then 'No Aplica'   end as escuela
                         from (
                            select u.pk_usuario,u.nombre,u.apellido,u.direccion,u.telefono,u.telefono_movil,u.correo,ug.pk_usuariogrupo,ug.fk_grupo,gg.grupo
                            from tbl_usuarios u
                            join tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                            join vw_grupos gg ON gg.pk_atributo = ug.fk_grupo
                            where (ug.fk_grupo = 854 OR ug.fk_grupo = 855 or ug.fk_grupo = 1745)
                                   and u.pk_usuario = {$pk_usuario}) as sqt1)as sqt2
                            order by orden asc limit 1;";

       $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
       
   }

   public function getUltimopk(){
        $SQL ="SELECT pk_prestamo
               FROM tbl_prestamos
               ORDER BY 1 DESC limit 1;";
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
   }
   
   public function dias_suspencion(){
       $SQL ="SELECT *
              FROM(
                   SELECT * , ((suspencion+1) - no_supendido)as total
                   FROM (
                        SELECT *,(current_date - fecha_devolucion)*2 as suspencion , (current_date - fecha_entrega)*3 as no_supendido
                        FROM(
                            SELECT u.pk_usuario,ug.pk_usuariogrupo ,ug.fk_grupo, pa.fecha_devolucion , pa.fecha_entrega 
                            FROM tbl_usuarios u 
                            JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                            JOIN tbl_prestamos p ON p.fk_usuariogrupo = ug.pk_usuariogrupo
                            JOIN tbl_prestamosarticulos pa ON pa.fk_prestamo = p.pk_prestamo)AS sql1) AS sql2) AS sql3 
                            WHERE total > 0 and fecha_entrega > fecha_devolucion AND fk_grupo = 855
                            GROUP BY 1,2,3,4,5,6,7,8
                            ORDER BY 8 DESC";
       $results = $this->_db->query($SQL);
       return (array) $results->fetchAll();
   }

   public function setSearch($searchData) {
       $searchData = str_replace("~","/",$searchData);
        $this->searchData = $searchData;
    }
    
    public function getorden($val){
       
        if($val == 1 || $val ==""){
            $orden = "AND (estado = 'Mora' OR estado = 'Transito' OR estado = 'Vacio')";
        }
        if($val == 2){
           $orden = "AND (estado = 'Solvente') "; 
            
        }
        if($val == 3){
         $orden ="AND (estado = 'Mora' OR estado = 'Transito' OR estado = 'Vacio' OR estado = 'Solvente')";
         
        }
        
        if($val == 4){
          $orden = "AND (estado = 'Espera') ";   
        }
        return $orden;
    }
}

