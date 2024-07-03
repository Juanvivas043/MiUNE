<?php

class Models_DbTable_Pasantes extends Zend_Db_Table {

    protected $_schema = 'produccion';
    protected $_name = 'tbl_usuarios u';
    protected $_primary = 'u.pk_usuario';
    protected $_sequence = false;
    private $searchParams = array('u.pk_usuario', 'u.nombre', 'u.apellido', "LTRIM(TO_CHAR(pk_usuario, '99\".\"999\".\"999')::varchar, '0. ')");

    public function init() {
        $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');

        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table(); 
    }

    

    
   

    public function setSearch($searchData) {
        $this->searchData = $searchData;
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
  public function getPasantes($Periodo,$Sede,$Escuela,$Pensum,$itemPerPage, $pageNumber){
      
      
       $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
      
      $SQL ="SELECT DISTINCT u.pk_usuario, u.nombre, u.apellido
               FROM tbl_inscripciones ins
               JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion  = ins.pk_inscripcion
               JOIN tbl_asignaturas      asi ON asi.pk_asignatura  = ra.fk_asignatura
               JOIN tbl_pensums           pe ON pe.pk_pensum    =   asi.fk_pensum
               JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
               JOIN tbl_usuarios             u ON u.pk_usuario       = ug.fk_usuario
               WHERE asi.fk_materia IN (716,848,717,9716,9896,9859) -- profesional I, profesional I y II, profesional II, Practica profesional, Practica profesional, Practica profesional(2012)
               AND ins.fk_periodo = {$Periodo} AND ins.fk_atributo = {$Escuela} AND pe.pk_pensum = {$Pensum}
               AND ins.fk_estructura ={$Sede} {$whereSearch}
               ORDER BY u.apellido LIMIT {$itemPerPage} OFFSET {$pageNumber};";
               
      $results = $this->_db->query($SQL);
      return (array) $results->fetchAll();
      
      
  }

    public function getPasantesCertificacion($Periodo,$itemPerPage, $pageNumber){
      

      $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
      
       $pageNumber = ($pageNumber - 1) * $itemPerPage;
      
      $SQL ="SELECT DISTINCT u.pk_usuario, u.nombre, u.apellido
               FROM tbl_inscripciones ins
               JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion  = ins.pk_inscripcion
               JOIN tbl_asignaturas      asi ON asi.pk_asignatura  = ra.fk_asignatura
               JOIN tbl_pensums           pe ON pe.pk_pensum    =   asi.fk_pensum
               JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
               JOIN tbl_usuarios             u ON u.pk_usuario       = ug.fk_usuario
               WHERE asi.fk_materia IN (716,848,717,9716,9896,9859) -- profesional I, profesional I y II, profesional II, Practica profesional, Practica profesional, Practica profesional(2012)
               AND ins.fk_periodo = {$Periodo} {$whereSearch}
               ORDER BY u.apellido LIMIT {$itemPerPage} OFFSET {$pageNumber};";
               
      $results = $this->_db->query($SQL);
      return  $results->fetchAll();
      
      
  }


      public function getPasantesCertificacionCount($Periodo){
      
      $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
      
      $SQL ="SELECT COUNT(DISTINCT u.pk_usuario)
               FROM tbl_inscripciones ins
               JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion  = ins.pk_inscripcion
               JOIN tbl_asignaturas      asi ON asi.pk_asignatura  = ra.fk_asignatura
               JOIN tbl_pensums           pe ON pe.pk_pensum    =   asi.fk_pensum
               JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
               JOIN tbl_usuarios             u ON u.pk_usuario       = ug.fk_usuario
               WHERE asi.fk_materia IN (716,848,717,9716,9896,9859) -- profesional I, profesional I y II, profesional II, Practica profesional, Practica profesional, Practica profesional(2012)
               AND ins.fk_periodo = {$Periodo} {$whereSearch}";
               
      return $this->_db->fetchOne($SQL);
      
      
      
  }


    public function getPasantesPeriodo($Periodo){
      
      $SQL ="SELECT DISTINCT u.pk_usuario, u.nombre, u.apellido
               FROM tbl_inscripciones ins
               JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion  = ins.pk_inscripcion
               JOIN tbl_asignaturas      asi ON asi.pk_asignatura  = ra.fk_asignatura
               JOIN tbl_pensums           pe ON pe.pk_pensum    =   asi.fk_pensum
               JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
               JOIN tbl_usuarios             u ON u.pk_usuario       = ug.fk_usuario
               WHERE asi.fk_materia IN (716,848,717,9716,9896,9859) -- profesional I, profesional I y II, profesional II, Practica profesional, Practica profesional, Practica profesional(2012)
               AND ins.fk_periodo = {$Periodo} {$whereSearch}
               ORDER BY u.apellido";
               
      $results = $this->_db->query($SQL);
      return (array) $results->fetchAll();
      
      
  }

  public function getPasanteEscuela($cedula, $periodo)
  {
    $SQL = "SELECT i.fk_atributo, ve.escuela
            from tbl_inscripciones  i
            join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = i.fk_usuariogrupo
            join vw_escuelas        ve on ve.pk_atributo = i.fk_atributo
            where ug.fk_usuario = {$cedula}
            and i.fk_periodo = {$periodo}";

            $results = $this->_db->query($SQL);
            return (array) $results->fetchAll();
  }

  public function getInfoPasantePeriodo($cedula,$Periodo){
      
      
      $SQL ="SELECT DISTINCT u.pk_usuario, u.nombre, u.apellido, ins.fk_periodo as periodo, ve.escuela as escuela, ins.fk_estructura
               FROM tbl_inscripciones ins
               JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion  = ins.pk_inscripcion
               JOIN tbl_asignaturas      asi ON asi.pk_asignatura  = ra.fk_asignatura
               JOIN tbl_pensums           pe ON pe.pk_pensum    =   asi.fk_pensum
               JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
               JOIN tbl_usuarios             u ON u.pk_usuario       = ug.fk_usuario
               JOIN vw_escuelas           ve ON ve.pk_atributo = ins.fk_atributo
               WHERE asi.fk_materia IN (716,848,717,9716,9896,9859) -- profesional I, profesional I y II, profesional II, Practica profesional, Practica profesional, Practica profesional(2012)
               AND ins.fk_periodo = {$Periodo}
               AND u.pk_usuario = {$cedula} {$whereSearch}";
      $results = $this->_db->query($SQL);
      return (array) $results->fetchAll();
      
      
  }
  
   public function totalpasantes($Periodo , $Sede, $Escuela, $Pensum) {
         $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
        $SQL = "SELECT COUNT (DISTINCT ug.fk_usuario)
               FROM tbl_inscripciones ins
               JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion  = ins.pk_inscripcion
               JOIN tbl_asignaturas      asi ON asi.pk_asignatura  = ra.fk_asignatura
               JOIN tbl_pensums           pe ON pe.pk_pensum    =   asi.fk_pensum
               JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
               JOIN tbl_usuarios             u ON u.pk_usuario       = ug.fk_usuario
               WHERE asi.fk_materia IN (716,848,717,9716,9896,9859) -- profesional I, profesional I y II, profesional II, Practica profesional, Practica profesional, Practica profesional(2012)
               AND ins.fk_periodo = {$Periodo} AND ins.fk_estructura = {$Sede} AND ins.fk_atributo = {$Escuela} AND pe.pk_pensum = {$Pensum} {$whereSearch};";
               

        return $this->_db->fetchOne($SQL);
    }

   public function getEstudiantes($itemPerPage, $pageNumber) {
        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

        $SQL = "SELECT u.pk_usuario, LTRIM(TO_CHAR(u.pk_usuario, '99\".\"999\".\"999')::varchar, '0. ') as ci, u.nombre, u.apellido
		        FROM tbl_usuarios u
		        JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
		        WHERE ug.fk_grupo = 855
                          {$whereSearch}
		        ORDER BY pk_usuario LIMIT {$itemPerPage} OFFSET {$pageNumber};";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }

   
    
        public function getTutores($itemPerPage, $pageNumber,$grupo) {
        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

        $SQL = "SELECT pk_usuario, LTRIM(TO_CHAR(pk_usuario, '99\".\"999\".\"999')::varchar, '0. ') as ci, 
                       CASE WHEN length(segundo_nombre) > 0  THEN primer_nombre || ' ' || segundo_nombre ELSE primer_nombre END AS nombre, 
                       CASE WHEN length(segundo_apellido) > 0 THEN primer_apellido || ' ' || segundo_apellido ELSE primer_apellido END AS apellido
		        FROM tbl_usuarios u
                        JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
		        WHERE ug.fk_grupo = {$grupo}
                          {$whereSearch}
		        ORDER BY pk_usuario LIMIT {$itemPerPage} OFFSET {$pageNumber};";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }
    

    public function getPensumPasante($cedula, $escuela, $periodo)
    {
      $SQL = "select distinct  pe.pk_pensum, pe.codigopropietario
              from tbl_usuarios u
              join tbl_usuariosgrupos ug  on  ug.fk_usuario = u.pk_usuario
              join tbl_inscripciones  i on  i.fk_usuariogrupo = ug.pk_usuariogrupo
              join tbl_pensums  pe  on  pe.pk_pensum = i.fk_pensum
              join vw_escuelas  ve  on  ve.pk_atributo = i.fk_atributo
              where u.pk_usuario = {$cedula}
              and i.fk_periodo = {$periodo}
              and ve.pk_atributo = {$escuela}
              order by 2 desc limit 1";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }
    
	/**
	 * @todo que hice?
	 * @return <type>
	 */
    public function getSQLCount() {
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

        $SQL = "SELECT COUNT(pk_usuario)
		FROM tbl_usuarios u
		JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
		WHERE ug.fk_grupo = 855
                  {$whereSearch}";

        return $this->_db->fetchOne($SQL);
    }

    public function totalusuarios() {
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
        
        if (empty($this->Where))
	  return;
        
        $SQL = "SELECT COUNT(pk_usuario)
		FROM tbl_usuarios u
		JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
		WHERE {$this->Where}
                  {$whereSearch}";

        return $this->_db->fetchOne($SQL);
    }
    
        public function totalusuariosdelsearch($grupo) {
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

        $SQL = "SELECT COUNT(pk_usuario)
		FROM tbl_usuarios u
		JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
		WHERE ug.fk_grupo = {$grupo}
                  {$whereSearch}";

        return $this->_db->fetchOne($SQL);
    }
    
    /**
     * Cuenta cuantos registros existen bajo un pk_usuario, adicionalmente si se
     * desea, permite restringir la busqueda.
     *
     * @param int $fk_usuario
     * @param string $where WHERE en SQL.
     * @return int
     */
    public function getCount($fk_usuario, $where = null) {
        if(empty($fk_usuario)) return;

        $SQL = "SELECT COUNT({$this->_primary}) AS count
                FROM {$this->_name}
                WHERE {$this->_primary} = {$fk_usuario} {$where};";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results[0]['count'];
    }

    /**
     * Obtiene un registro en especifico.
     *
     * @param int $id Clave primaria del registro.
     * @return array
     */
    public function getRow($id) {
        $id = (int) $id;

        $row = $this->fetchRow($this->_primary . ' = ' . $id . ' AND pk_usuario >= 10000');

        if (isset($row)) {
            return $row->toArray();
        }
    }

    public function addRow($data) {
        $data     = array_filter($data);
        $affected = $this->insert($data);

        return $affected;
    }

    public function updateRow($id, $data) {
        $data     = array_filter($data);
        $affected = $this->update($data, $this->_primary . ' = ' . (int)$id);

        return $affected;
    }

    public function deleteRow($id) {
        $affected = $this->delete($this->_primary . ' = ' . (int) $id);

        return $affected;
    }

    public function checkPasswordOperacionesEspeciales($grupo, $password) {
        $grupo = (int) $grupo;

        if (isset($grupo)) {
            $SQL = "SELECT count(pk_usuario) = 1
                    FROM tbl_usuarios        u
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                    JOIN vw_grupos          vg ON vg.pk_atributo = ug.fk_grupo
                    WHERE vg.pk_atributo   = {$grupo}
                      AND u.passwordoehash = '{$password}'";

            return (boolean)$this->_db->fetchOne($SQL);
        }
    }

	public function changePk($old, $new) {
		$this->_db->beginTransaction();

		// Copiamos el usuario.
		$SQL = "INSERT INTO tbl_usuarios (pk_usuario, status, nacionalidad, sexo, nombre, apellido, direccion, fechanacimiento, correo, passwordhash, deleted, telefono, foto, telefono_movil, passwordoehash)
                SELECT {$new}, status, nacionalidad, sexo, nombre, apellido, direccion, fechanacimiento, correo, MD5('{$new}'), deleted, telefono, foto, telefono_movil, passwordoehash
                FROM tbl_usuarios
                WHERE pk_usuario = {$old}";
		
        $return += $this->_db->query($SQL);

		// Cambiamos el usuario el la tabla de usuariogrupo, asi evitamos cambiar
		// todos los registros relacionados al viejo usuario.
        $SQL = "UPDATE tbl_usuariosgrupos
                   SET fk_usuario = {$new}
                 WHERE fk_usuario = {$old}";

        $return += $this->_db->query($SQL);

		// Eliminamos la afinidad del usuario para el uso de las tarjetas de Banesco.
		$SQL = "DELETE FROM tbl_usuariosafinidades
				WHERE fk_usuario = {$old}";

		$return += $this->_db->query($SQL);

		// Eliminamos el usuario antiguo.
		$SQL = "DELETE FROM tbl_usuarios
                WHERE pk_usuario = {$old}";

        $return += $this->_db->query($SQL);

		$this->_db->commit();

		return $return;
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

	public function setPhoto($id, $image) {
		if(!is_numeric($id)) return;

		$config   = $this->_db->getConfig();
		$conn     = pg_connect("user={$config['username']} password={$config['password']} dbname={$config['dbname']} host={$config['host']}");
		$image    = pg_escape_bytea($image);
		$query    = pg_query($conn, "UPDATE tbl_usuarios SET foto = '{$image}' WHERE pk_usuario = {$id}");
		$affected = pg_affected_rows($query);

		pg_close($conn);

		return $affected;
	}

        public function getProfile($id){
            if(!is_numeric($id)) return;

            $SQL = "SELECT initcap(lower(nombre)) || ' ' || initcap(lower(apellido)) as nombre,
                        correo as correo,
                        TO_CHAR(fechanacimiento,'DD-MM-YYYY') as nacimiento,
                        telefono as tlf,
                        telefono_movil as cel,
                        LOWER(direccion) as dir
                    FROM tbl_usuarios u
                    WHERE u.pk_usuario = {$id};";

            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();

        }
}
