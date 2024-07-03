<?php

class Models_DbTable_Carnets extends Zend_Db_Table {

    protected $_schema = 'produccion';
    protected $_name = 'tbl_usuarios';
    protected $_primary = 'pk_usuario';
    protected $_sequence = false;
    private $searchParams = array('pk_usuario', 'nombre', 'apellido', "LTRIM(TO_CHAR(pk_usuario, '99\".\"999\".\"999')::varchar, '0. ')");

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

    public function getUsuariosCarnets($itemPerPage, $pageNumber) {
        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
       

        $SQL = "SELECT DISTINCT  pk_usuario,pk_carnet, LTRIM(TO_CHAR(pk_usuario, '99\".\"999\".\"999')::varchar, '0. ') as ci, nombre, apellido, atr.valor as emisiones, atr2.valor as autorizacion, atr3.valor as afinidad
		        FROM tbl_usuarios u
		        JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
                        JOIN tbl_usuariosafinidades ua ON ua.fk_usuario = u.pk_usuario
                        JOIN tbl_carnets ca ON ca.fk_usuariogrupo = ug.pk_usuariogrupo
                        JOIN tbl_atributos atr ON atr.pk_atributo = ca.fk_razon
                        JOIN tbl_atributos atr2 ON atr2.pk_atributo = ua.fk_autorizacion
                        JOIN tbl_atributos atr3 ON atr3.pk_atributo = ua.fk_afinidad
                          {$whereSearch}
                        GROUP BY 1,2,3,4,5,6,7,8
		        ORDER BY pk_usuario LIMIT {$itemPerPage} OFFSET {$pageNumber};";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }
    
    public function getUsuariosEmision($ci) {
        
                $SQL = "SELECT fk_razon, atr.valor, ca.*
                        FROM tbl_usuariosgrupos ug
                        JOIN tbl_carnets ca ON ca.fk_usuariogrupo = ug.pk_usuariogrupo
                        JOIN tbl_atributos atr ON atr.pk_atributo = ca.fk_razon
                        WHERE fk_usuario = (
                                    SELECT usug.fk_usuario 
                                    FROM tbl_carnets car 
                                    JOIN tbl_usuariosgrupos usug ON car.fk_usuariogrupo = usug.pk_usuariogrupo
                                    WHERE pk_carnet = {$ci}
                                            )
                        ORDER BY fecha_emision DESC limit 1";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
        
        
    }
    
    public function getEmisiones($pk_carnet) {
        
                 $SQL = "SELECT u.pk_usuario, emm.pk_emision, emm.nombre, case WHEN (SELECT DISTINCT atr.pk_atributo
                         FROM tbl_usuarios u
                         JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
                         JOIN tbl_carnets ca ON ca.fk_usuariogrupo = ug.pk_usuariogrupo
                         JOIN tbl_atributos atr ON atr.pk_atributo = ca.fk_razon
                         WHERE ca.pk_carnet = {$pk_carnet}) = emm.pk_emision then '1' else '0' end as orden
                         FROM tbl_usuarios u
                         JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
                         JOIN tbl_carnets ca ON ca.fk_usuariogrupo = ug.pk_usuariogrupo
                         CROSS JOIN vw_pandaid_carnets_emisiones emm
                         WHERE u.pk_usuario = (
                SELECT usug.fk_usuario 
                FROM tbl_carnets car 
                JOIN tbl_usuariosgrupos usug ON car.fk_usuariogrupo = usug.pk_usuariogrupo
                WHERE pk_carnet = {$pk_carnet}
                        )
                         GROUP BY 1,2,3,4
                         ORDER BY 4 DESC";

                         
        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
        
        
    }
    
    public function getAutorizaciones($pk_carnet) {
                 $SQL = "SELECT u.pk_usuario, pau.pk_atributo, pau.autorizacion, case WHEN (SELECT DISTINCT atr.pk_atributo
                       FROM tbl_usuarios u
                       JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
                       JOIN tbl_carnets ca ON ca.fk_usuariogrupo = ug.pk_usuariogrupo
                       JOIN tbl_usuariosafinidades ua ON ua.fk_usuario = u.pk_usuario
                       JOIN tbl_atributos atr ON atr.pk_atributo = ua.fk_autorizacion
                       WHERE u.pk_usuario = (
                                    SELECT usug.fk_usuario 
                                    FROM tbl_carnets car 
                                    JOIN tbl_usuariosgrupos usug ON car.fk_usuariogrupo = usug.pk_usuariogrupo
                                    WHERE pk_carnet = {$pk_carnet}
                                            )) = pau.pk_atributo then '1' else '0' end as orden
                       FROM tbl_usuarios u
                       JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
                       JOIN tbl_carnets ca ON ca.fk_usuariogrupo = ug.pk_usuariogrupo
                       JOIN tbl_usuariosafinidades ua ON ua.fk_usuario = u.pk_usuario
                       CROSS JOIN vw_pandaid_autorizaciones pau  
                    WHERE u.pk_usuario = (
                                    SELECT usug.fk_usuario 
                                    FROM tbl_carnets car 
                                    JOIN tbl_usuariosgrupos usug ON car.fk_usuariogrupo = usug.pk_usuariogrupo
                                    WHERE pk_carnet = {$pk_carnet}
                                            )
                    GROUP BY 1,2,3,4
                    ORDER BY 4 DESC";



        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
        
        
    }
    
    public function setAutorizaciones($pk_carnet, $fk_autorizacion) {
        
        $SQL = "UPDATE tbl_usuariosafinidades SET fk_autorizacion = {$fk_autorizacion} WHERE fk_usuario = (
                                    SELECT usug.fk_usuario 
                                    FROM tbl_carnets car 
                                    JOIN tbl_usuariosgrupos usug ON car.fk_usuariogrupo = usug.pk_usuariogrupo
                                    WHERE pk_carnet = {$pk_carnet}
                                            );";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
        
        
    }
    
    public function setEmision($pk_carnet, $fk_razon) {
        
                 $SQL = "UPDATE tbl_carnets 
                 SET fk_razon = {$fk_razon} 
                 WHERE pk_carnet = {$pk_carnet}";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
        
        
    }
    
    public function getAfinidad($ci) {
        
                 $SQL = "SELECT DISTINCT ua.*, atr.valor, atr1.valor as afinidad
                FROM tbl_usuarios u
                JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
                JOIN tbl_usuariosafinidades ua ON ua.fk_usuario = u.pk_usuario
                JOIN tbl_atributos atr ON atr.pk_atributo = ua.fk_autorizacion
                JOIN tbl_atributos atr1 ON atr1.pk_atributo = fk_afinidad
                WHERE ug.fk_usuario = (
                SELECT usug.fk_usuario 
                FROM tbl_carnets car 
                JOIN tbl_usuariosgrupos usug ON car.fk_usuariogrupo = usug.pk_usuariogrupo
                WHERE pk_carnet = {$ci}
                );";

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
                FROM (
                SELECT DISTINCT pk_usuario
                FROM tbl_usuarios u
                JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
                JOIN tbl_usuariosafinidades ua ON ua.fk_usuario = u.pk_usuario) as sqt";

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


    public function splitnames($ci){

        $SQL = "SELECT * FROM fn_cxux_cortar_nombres({$ci});";

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

        $SQL = "SELECT usu.*
                FROM tbl_carnets car 
                JOIN tbl_usuariosgrupos usug ON usug.pk_usuariogrupo = car.fk_usuariogrupo
                JOIN tbl_usuarios usu ON usu.pk_usuario = usug.fk_usuario
                WHERE pk_carnet = {$id}";
        
        $results = $this->_db->query($SQL);
        $result =   (array) $results->fetchAll();

        return $result[0];

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

      
}
