<?php
class Models_DbTable_UsuariosGrupos extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_usuariosgrupos';
    protected $_primary  = 'pk_usuariogrupo';
    protected $_sequence = false;

    /**
     * Obtiene un registro en especifico.
     *
     * @param int $id Clave primaria del registro.
     * @return array
     */
    public function getRow($id) {
        if(empty($id)) return;

        $id = (int)$id;
        $row = $this->fetchRow($this->_primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
    }

    /**
     * Cuenta cuantos registros existen bajo un fk_usuario, adicionalmente si se
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
                WHERE fk_usuario = {$fk_usuario} {$where};";

//        $results = $this->_db->query($SQL);
//        $results = $results->fetchAll();

        return $this->_db->fetchOne($SQL);
    }

    /**
     * Busca la clave primaria según un grupo de valores foraneas.
     *
     * @param string $usuario
     * @param string $where
     * @return integer
     */
    public function getPK($usuario, $where = null) {
        if(empty($usuario)) return;

        $SQL = "SELECT {$this->_primary}
                FROM {$this->_name}
                WHERE fk_usuario = {$usuario} {$where};";

        return $this->_db->fetchOne($SQL);
    }

    /**
     * Busca la clave primaria según un grupo de valores foraneas.
     *
     * @param string $usuario
     * @param string $where
     * @return integer
     */
    public function getEstudiante($usuario) {
        if(empty($usuario)) return;

        $SQL = "SELECT {$this->_primary}
                FROM {$this->_name}
                WHERE fk_usuario = {$usuario} AND fk_grupo = 855;";

        return $this->_db->fetchOne($SQL);
    }

    public function getusuariostutorespp($grupo) {
        $SQL = "SELECT ug.pk_usuariogrupo ,
                       u.apellido || ', ' || u.nombre  as nombre
                  FROM tbl_usuariosgrupos ug
                  JOIN tbl_usuarios       u  ON u.pk_usuario = ug.fk_usuario
                  WHERE fk_grupo = {$grupo}
                  ORDER BY 2 ASC";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }

    /**
     * Agrega un nuevo registro.
     *
     * @param <type> $fk_usuario
     * @param <type> $fk_grupo
     * @return <type>
     */
    public function addRow($fk_usuario, $fk_grupo) {
        if(empty($fk_usuario)) return;
        if(empty($fk_grupo))   return;

        $data = array(
            'fk_usuario' => $fk_usuario,
            'fk_grupo'   => $fk_grupo
        );

        $this->insert($data);
    }

    /**
     * Actualiza un registro en especifico.
     *
     * @param <type> $id
     * @param <type> $fk_usuario
     * @param <type> $fk_grupo
     * @return <type>
     */
    public function updateRow($id, $fk_usuario, $fk_grupo) {
        if(empty($fk_usuario)) return;
        if(empty($fk_grupo))   return;

        $data = array(
            'fk_usuario' => $fk_usuario,
            'fk_grupo'   => $fk_grupo
        );

        $this->update($data, $this->_primary . ' = ' . (int)$id);
    }

    /**
     * Permite eliminar un registro dependiendo de las condiciones que son
     * enviadas como parametros.
     *
     * @param int $id         Clave primaria del registro.
     * @param int $fk_usuario Clave foranea del usuario.
     * @param int $fk_grupo   Clave foranea del grupo.
     * @return int
     */
    public function deleteRow($id, $fk_usuario = null, $fk_grupo = null) {
        $where[] = (is_numeric($id        ))? $this->_primary . ' = ' . $id         : null;
        $where[] = (is_numeric($fk_usuario))? 'fk_usuario = '         . $fk_usuario : null;
        $where[] = (is_numeric($fk_grupo  ))? 'fk_grupo = '           . $fk_grupo   : null;

        $where        = trim(str_replace(' AND  AND ', ' AND ', implode(' AND ', $where)), ' AND ');
        $rowsAffected = $this->delete($where);

        return $rowsAffected;
    }

    public function haveAccessToApp($UserID, $Applicacion) {
        if(!isset($UserID))      return;
        if(!isset($Applicacion)) return;

        $SQL = "SELECT COUNT(a.pk_acceso) > 0 AS have
                FROM tbl_usuarios u
                JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
                JOIN vw_grupos g ON g.pk_atributo = ug.fk_grupo
                JOIN tbl_accesosgrupos ag ON ag.fk_grupo = g.pk_atributo
                JOIN tbl_accesos a ON a.pk_acceso = ag.fk_acceso
                JOIN vw_aplicaciones ap ON ap.pk_atributo = a.fk_aplicacion
                WHERE  u.pk_usuario = {$UserID}
                  AND ap.nombre     = '{$Applicacion}'";

        return $this->_db->fetchOne($SQL);
    }

    public function haveAccessToModule() {
        $this->AuthSpace      = new Zend_Session_Namespace('Zend_Auth');
        $this->Request        = Zend_Controller_Front::getInstance()->getRequest();
        $this->moduleName     = $this->Request->getModuleName();
        $this->controllerName = $this->Request->getControllerName();

        $Applicacion = 'MiUNE Control De Estudios';
        $UserID      = $this->AuthSpace->userId;
        $Uri         = $this->moduleName . '/' . $this->controllerName;
        $aClientIP = explode('.', $_SERVER['REMOTE_ADDR']);

        //10671 = Inactivo

        $SQL = "SELECT case when 10671 in (select fk_grupo from tbl_usuariosgrupos where fk_usuario = {$UserID}) 
                then false
                else (Select COUNT(a.pk_acceso) > 0
                FROM tbl_usuarios u
                JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
                JOIN vw_grupos g ON g.pk_atributo = ug.fk_grupo
                JOIN tbl_accesosgrupos ag ON ag.fk_grupo = g.pk_atributo
                JOIN tbl_accesos a ON a.pk_acceso = ag.fk_acceso
                JOIN vw_aplicaciones ap ON ap.pk_atributo = a.fk_aplicacion
                LEFT JOIN tbl_accesosip ip ON ip.fk_acceso = a.pk_acceso
                WHERE  u.pk_usuario = {$UserID}
                  AND ap.nombre     = '{$Applicacion}'
                  AND  a.include    = '{$Uri}'
                  AND (ip.client_ip = '0.0.0.0'
                       OR ip.client_ip IS NULL
                       OR ip.client_ip = '{$aClientIP[0]}.0.0.0'
                       OR ip.client_ip = '{$aClientIP[0]}.{$aClientIP[1]}.0.0'
                       OR ip.client_ip = '{$aClientIP[0]}.{$aClientIP[1]}.{$aClientIP[2]}.0'
                       OR ip.client_ip = '{$aClientIP[0]}.0.{$aClientIP[2]}.0'
                       OR ip.client_ip = '{$aClientIP[0]}.0.0.{$aClientIP[3]}'
                       OR ip.client_ip = '{$aClientIP[0]}.{$aClientIP[1]}.0.{$aClientIP[3]}'
                       OR ip.client_ip = '{$aClientIP[0]}.{$aClientIP[1]}.{$aClientIP[2]}.{$aClientIP[3]}'))
                end AS have;";
//$this->logger = Zend_Registry::get('logger');
//$this->logger->log($SQL,ZEND_LOG::WARN);


        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();
        // var_dump($results);
        // if($results[0]['ip'] === false)
        //    $results[0]['have'] = false;
        return $results[0]['have'];
    }

    public function checkConstraintInscripciones($ci, $grupo){
        $SQL = "SELECT COUNT(*)
                FROM tbl_usuariosgrupos usug
                JOIN tbl_inscripciones ins ON usug.pk_usuariogrupo = ins.fk_usuariogrupo
                WHERE fk_usuario = {$ci}
                AND fk_grupo = {$grupo}
                ";
        $results = $this->_db->query($SQL);
        $count = (array)$results->fetchAll();
        return $count[0]['count'];
    }
    public function checkConstraintAsignaciones($ci,$grupo){
        $SQL= "SELECT COUNT(*)
                FROM tbl_usuariosgrupos usug
                JOIN tbl_asignaciones asig ON usug.pk_usuariogrupo = asig.fk_usuariogrupo
                WHERE fk_usuario = {$ci}
                AND fk_grupo = {$grupo}";
        $results = $this->_db->query($SQL);
        $count = (array)$results->fetchAll();
        return $count[0]['count'];
    }
    public function getGrupos(){
        $this->AuthSpace      = new Zend_Session_Namespace('Zend_Auth');

        $SQL="
            SELECT
                  grp.pk_atributo::INTEGER as pk_grupo,
                  grp.grupo
               FROM tbl_usuariosgrupos ug
               JOIN vw_grupos grp ON grp.pk_atributo = ug.fk_grupo
               WHERE fk_usuario = {$this->AuthSpace->userId}
            ;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

         public function getPkafinidad($grupo){
     //parametros grupo perfil
        //return grupo fkafinidad
        $SQL = "SELECT att.pk_atributo
                    FROM tbl_atributos att
                        JOIN vw_grupos gr On gr.grupo = att.valor
                         Where fk_atributotipo = 24
                            and gr.pk_atributo = {$grupo}
                                limit 1;";



        return $this->_db->fetchOne($SQL);

    }

    public function getGruposContent($id){
        $SQL="
            SELECT DISTINCT
                  pk_usuariogrupo,
                  usu.pk_usuario,
                  usu.nombre || ' ' || usu.apellido as nombre
               FROM tbl_usuariosgrupos ug
               JOIN vw_grupos grp ON grp.pk_atributo = ug.fk_grupo
               JOIN tbl_usuarios usu ON usu.pk_usuario = ug.fk_usuario
               WHERE grp.pk_atributo = $id
            ;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

        public function getGruposFiltros(){
        $SQL="
            SELECT *
            FROM vw_grupos
            ;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

     public function getTutoresAcademicos() {
        $SQL = "SELECT DISTINCT ug.pk_usuariogrupo,
                                u.apellido || ',' || u.nombre AS profesor,
                                CASE WHEN u.apellido = 'N/A' THEN 1 ELSE 2 END AS orden
                  FROM tbl_usuariosgrupos ug
                   --JOIN tbl_asignaciones asig ON ug.pk_usuariogrupo = asig.fk_usuariogrupo
                   JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
                 WHERE ug.fk_grupo in (854)

                   ORDER BY orden ASC, profesor ASC;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

      public function getGruposperfiles($perfil, $Ignore = null) {
        $Ignore = (isset($Ignore))? "AND valor NOT IN ({$Ignore})" : null;
        $this->AuthSpace      = new Zend_Session_Namespace('Zend_Auth');

        $SQL = "SELECT DISTINCT sqt.pk_grupo, sqt.grupo
                  FROM
                  (SELECT CASE WHEN ug.fk_grupo = 1425 THEN (SELECT pk_atributo FROM vw_grupos WHERE pk_atributo = 854)
                     ELSE CASE WHEN ug.fk_grupo = 1417 THEN (SELECT pk_atributo FROM vw_grupos WHERE pk_atributo = 1745)
                     ELSE CASE WHEN ug.fk_grupo = 1253 THEN (SELECT pk_atributo FROM vw_grupos WHERE pk_atributo = 855)
                     ELSE CASE WHEN ug.fk_grupo = 853 THEN (SELECT pk_atributo FROM vw_grupos WHERE pk_atributo = 855)
                     END END END END AS pk_grupo,
                     CASE WHEN ug.fk_grupo = 1425 THEN (SELECT grupo FROM vw_grupos WHERE pk_atributo = 854)
                     ELSE CASE WHEN ug.fk_grupo = 1417 THEN (SELECT grupo FROM vw_grupos WHERE pk_atributo = 1745)
                     ELSE CASE WHEN ug.fk_grupo = 1253 THEN (SELECT grupo FROM vw_grupos WHERE pk_atributo = 855)
                     ELSE CASE WHEN ug.fk_grupo = 853 THEN (SELECT grupo FROM vw_grupos WHERE pk_atributo = 855)
                     END END END END AS grupo
                    FROM tbl_usuariosgrupos   ug
                    JOIN vw_grupos   g ON g.pk_atributo = ug.fk_grupo
                    WHERE ug.fk_usuario = {$this->AuthSpace->userId}
                    AND ug.fk_grupo = {$perfil} {$Ignore}
                    AND ug.fk_grupo  IN (1253,1425,1417,853)) AS sqt
                    ORDER BY 2 ASC;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

       public function getPKGruposperfiles() {
        $this->AuthSpace      = new Zend_Session_Namespace('Zend_Auth');

        $SQL = "SELECT DISTINCT sqt.pk_grupo
                  FROM
                  (SELECT CASE WHEN ug.fk_grupo = 1425 THEN (SELECT pk_atributo FROM vw_grupos WHERE pk_atributo = 854)
                     ELSE CASE WHEN ug.fk_grupo = 1417 THEN (SELECT pk_atributo FROM vw_grupos WHERE pk_atributo = 1745)
                     ELSE CASE WHEN ug.fk_grupo = 1253 THEN (SELECT pk_atributo FROM vw_grupos WHERE pk_atributo = 855)
                     ELSE CASE WHEN ug.fk_grupo = 853 THEN (SELECT pk_atributo FROM vw_grupos WHERE pk_atributo = 855)
                     END END END END AS pk_grupo
                    FROM tbl_usuariosgrupos   ug
                    JOIN vw_grupos		   g ON g.pk_atributo = ug.fk_grupo
                    WHERE ug.fk_usuario = {$this->AuthSpace->userId}
                    AND ug.fk_grupo  IN (1253,1425,1417,853)) AS sqt
                    ORDER BY 1 ASC;";


        $results = $this->_db->query($SQL);

        return $results->fetchAll();
    }

    public function getUsuariogrupoEst($ci){

        $SQL = "select *
                from tbl_usuariosgrupos ug
                WHERE ug.fk_usuario = {$ci}
                and ug.fk_grupo = 855;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }


    public function getUsuariogrupoAdministrativo($ci){

        $SQL = "select *
                from tbl_usuariosgrupos ug
                WHERE ug.fk_usuario = {$ci}
                and ug.fk_grupo = 1745;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getVehiculosAdministrativos(){

        $SQL = "select u.pk_usuario,
                       u.nombre,
                       u.apellido
                FROM tbl_usuariosgrupos ug
                JOIN tbl_usuariosvehiculos uv ON uv.fk_usuario = ug.fk_usuario
                JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
                WHERE ug.fk_grupo = 1745
                order by ug.fk_usuario;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getpkgrupoEstudiante($ci){

        $SQL = "select pk_usuariogrupo
                FROM tbl_usuariosgrupos
                WHERE fk_usuario = {$ci};";

        return $this->_db->fetchOne($SQL);

    }

    public function isEgresado(){
        $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');
        
        $is_egresado = false;

        $SQL = "SELECT fk_grupo FROM tbl_usuariosgrupos 
        WHERE fk_usuario = {$this->AuthSpace->userId} AND fk_grupo = 20029";

        $result = $this->_db->fetchOne($SQL);

        if(!$result == null){
            $is_egresado = true;
        }
        
        return $is_egresado;
    }

    public function isBibliotecaria(){
        $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');
        
        $is_bibliotecaria = false;

        $SQL = "SELECT fk_grupo FROM tbl_usuariosgrupos 
        WHERE fk_usuario = {$this->AuthSpace->userId} AND fk_grupo = 8245";

        $result = $this->_db->fetchOne($SQL);

        if(!$result == null){
            $is_bibliotecaria = true;
        }
        
        return $is_bibliotecaria;
    }

    public function isUserEstudiante($cedula) {


        if (is_numeric($cedula)) {
            $isuserestudiante = false;

            $SQL = "SELECT pk_usuariogrupo
                    FROM tbl_usuariosgrupos ug 
                    WHERE fk_usuario = $cedula AND fk_grupo = 855";

            $result = $this->_db->fetchOne($SQL);
            
            if(!$result == null) {

                $isuserestudiante = true;

            }

            return $isuserestudiante;
        } else {
            
            return false;

        }
      }

    public function getTutores() {

        $SQL = "SELECT u.pk_usuario, concat(u.nombre, ' ', u.apellido) FROM tbl_usuariosgrupos ug 
                JOIN tbl_usuarios u ON ug.fk_usuario = u.pk_usuario
                WHERE fk_grupo = 19976;";

        return $this->_db->fetchOne($SQL);
    }


    
}
