<?php
class Models_DbTable_Mensajes extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_mensajes';
    protected $_primary  = 'pk_mensaje';
    protected $_sequence = false;

    public function init() {
        $this->SwapBytes_Array = new SwapBytes_Array();
    }

    /**
     * Permite crear una clausula WHERE dependiendo de los parametros que se envien.
     *
     * @param <type> $Data
     * @param <type> $Keys
     */
    public function setData($Data) {
        $Where = array('AND fk_clase = ' => $Data['pk_clase']);

        $Where = array_filter($Where);
        $Where = $this->SwapBytes_Array->implode(' ', $Where);
        $Where = ltrim($Where, ' AND ');

        $this->Where = $Where;
    }

    public function addRow($data) {
        $data     = array_filter($data);
        $affected = $this->insert($data);

        return $affected;
    }
    
    /**
     * Obtiene un registro en especifico.
     *
     * @param int $id Clave primaria del registro.
     * @return array
     */
    public function getAdjunto($id){
            $SQL = " 
         SELECT *
        FROM tbl_adjuntos
              WHERE  pk_adjunto = $id;     
         ";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();  
    }
    public function getRow($id) {
		if(!isset($id)) return;

      $SQL = " 
         SELECT 
               msj.pk_mensaje,
               msj.fk_mensaje,
               msj.fk_tipo,
               msj.fk_atributo,
               msj.fk_usuario as emisor,
               emi.nombre || ', ' || emi.apellido as nombre_emisor,
               rcp.nombre || ', ' || rcp.apellido as nombre_receptor,                
               msj.fk_clase,
               msj.fecha_creacion,
               msj.calificacion,
               msj.titulo,
               msj.contenido,
               dst.pk_destinatario,
               dst.fk_lista,
               dst.fk_usuario as receptor,
               dst.fechahora,
               dst.fecha_envio,
               dst.fecha_revision,
               adj.pk_adjunto,
               adj.fk_tipo as tipo_adjunto,
               adj.descripcion as descripcion_adjunto,
               adj.dir_archivo,
              emsj.valor as estado_mensaje,
              tmsj.valor as tipo_mensaje,
              tadj.valor as tipo_adjunto,
               (SELECT array_to_string(array(SELECT DISTINCT COALESCE(rcp.nombre || ' ' || rcp.apellido, grp.grupo, mat.materia) 
                  FROM tbl_destinatarios dst1
                  LEFT OUTER JOIN tbl_usuarios          rcp ON rcp.pk_usuario  = dst1.fk_usuario
                  LEFT OUTER JOIN vw_grupos             grp ON grp.pk_atributo = dst1.fk_grupo
                  LEFT OUTER JOIN tbl_asignaciones      asg ON asg.pk_asignacion = dst1.fk_asignacion
                  LEFT OUTER JOIN tbl_asignaturas      asi ON asi.pk_asignatura = asg.fk_asignatura
                  LEFT OUTER JOIN vw_materias          mat ON mat.pk_atributo = asi.fk_materia
                  WHERE dst1.fk_mensaje = msj.pk_mensaje
               ), ', ')) as receptores
            FROM tbl_mensajes msj 
                          JOIN tbl_destinatarios dst ON msj.pk_mensaje  = dst.fk_mensaje
               LEFT OUTER JOIN tbl_adjuntos      adj ON adj.fk_mensaje  = msj.pk_mensaje
               LEFT OUTER JOIN tbl_atributos    emsj ON msj.fk_atributo = emsj.pk_atributo
                          JOIN tbl_atributos    tmsj ON msj.fk_tipo     = tmsj.pk_atributo
               LEFT OUTER JOIN tbl_atributos    tadj ON adj.fk_tipo     = tadj.pk_atributo
                          JOIN tbl_usuarios          emi ON emi.pk_usuario  = msj.fk_usuario
               LEFT OUTER JOIN tbl_usuarios          rcp ON rcp.pk_usuario  = dst.fk_usuario
            WHERE msj.pk_mensaje = $id;     
         ";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function updateRow($id, $data) {
        $data     = array_filter($data);
        $affected = $this->update($data, 'pk_mensaje ' . ' = ' . (int)$id);

        return $affected;
    }

    public function deleteRow($id) {
		if(!is_numeric($id)) return null;
		
        $affected = $this->delete('pk_mensaje ' . ' = ' . (int) $id);

        return $affected;
    }

    public function getMensajesReceptor($id){
	   if(!is_numeric($id)) return null;
      $SQL = " 
            SELECT DISTINCT 
               msj.pk_mensaje,
               msj.fk_mensaje,
               msj.fk_tipo,
               msj.fk_atributo,
               msj.fk_usuario as emisor,
               emi.nombre || ', ' || emi.apellido as nombre_emisor,
               rcp.nombre || ', ' || rcp.apellido as nombre_receptor,
               msj.fk_clase,
               msj.fecha_creacion,
               msj.calificacion,
               msj.titulo,
               msj.contenido,
              emsj.valor as estado_mensaje,
              tmsj.valor as tipo_mensaje,
            cmsj.fechahora as carpeta_fecha
            FROM tbl_mensajes msj 
                          JOIN tbl_destinatarios     dst ON msj.pk_mensaje  = dst.fk_mensaje
               LEFT OUTER JOIN tbl_adjuntos          adj ON adj.fk_mensaje  = msj.pk_mensaje
               LEFT OUTER JOIN tbl_atributos    emsj ON msj.fk_atributo = emsj.pk_atributo
                          JOIN tbl_atributos        tmsj ON msj.fk_tipo     = tmsj.pk_atributo
               LEFT OUTER JOIN tbl_atributos        tadj ON adj.fk_tipo     = tadj.pk_atributo
                          JOIN tbl_carpetasmensajes cmsj ON cmsj.fk_mensaje = msj.pk_mensaje
                          JOIN tbl_usuarios          emi ON emi.pk_usuario  = msj.fk_usuario
               LEFT OUTER JOIN tbl_usuarios          rcp ON rcp.pk_usuario  = dst.fk_usuario
            WHERE (cmsj.fk_usuario = $id OR dst.fk_usuario = $id)
            AND    msj.fk_tipo   != 1727
            AND   cmsj.fk_carpeta = 1
            ORDER BY cmsj.fechahora;      
         ";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    
    }

    public function getCarpetas(){
      $SQL = " 
         SELECT *
            FROM tbl_carpetas
            WHERE fk_tipo = 1737;      
         ";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    
    }

    public function getMensajesEmisor($id, $cid){
	   if(!is_numeric($id)) return null;
      $SQL = " 
            SELECT DISTINCT 
               msj.pk_mensaje,
               msj.fk_mensaje,
               msj.fk_tipo,
               msj.fk_atributo,
               msj.fk_usuario as emisor,
               emi.nombre || ' ' || emi.apellido as nombre_emisor,
               msj.fk_clase,
               msj.fecha_creacion,
               msj.calificacion,
               msj.titulo,
               msj.contenido,
              emsj.valor as estado_mensaje,
              tmsj.valor as tipo_mensaje,
            cmsj.fechahora as carpeta_fecha,
               (SELECT array_to_string(array(SELECT DISTINCT COALESCE(rcp.nombre || ' ' || rcp.apellido, grp.grupo, mat.materia) 
                  FROM tbl_destinatarios dst1
                  LEFT OUTER JOIN tbl_usuarios          rcp ON rcp.pk_usuario  = dst1.fk_usuario
                  LEFT OUTER JOIN vw_grupos             grp ON grp.pk_atributo = dst1.fk_grupo
                  LEFT OUTER JOIN tbl_asignaciones      asg ON asg.pk_asignacion = dst1.fk_asignacion
                  LEFT OUTER JOIN tbl_asignaturas      asi ON asi.pk_asignatura = asg.fk_asignatura
                  LEFT OUTER JOIN vw_materias          mat ON mat.pk_atributo = asi.fk_materia
                  WHERE dst1.fk_mensaje = msj.pk_mensaje
               ), ', ')) as receptores
            FROM tbl_mensajes msj 
               LEFT OUTER JOIN tbl_adjuntos          adj ON adj.fk_mensaje  = msj.pk_mensaje
               LEFT OUTER JOIN tbl_atributos    emsj ON msj.fk_atributo = emsj.pk_atributo
                          JOIN tbl_atributos        tmsj ON msj.fk_tipo     = tmsj.pk_atributo
               LEFT OUTER JOIN tbl_atributos        tadj ON adj.fk_tipo     = tadj.pk_atributo
                          JOIN tbl_carpetasmensajes cmsj ON cmsj.fk_mensaje = msj.pk_mensaje
                          JOIN tbl_usuarios          emi ON emi.pk_usuario  = msj.fk_usuario
            WHERE (cmsj.fk_usuario = $id
                OR cmsj.fk_carpeta = $cid)
           AND msj.fk_tipo != 1727
            ORDER BY cmsj.fechahora;      
         ";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    
    }

    public function getAnuncios(){
        $this->AuthSpace      = new Zend_Session_Namespace('Zend_Auth');
      $SQL = " 
         SELECT DISTINCT
               msj.pk_mensaje,
               msj.fk_usuario as emisor,
               msj.titulo,
               msj.fecha_creacion,
               --emi.nombre || ' ' || emi.apellido as nombre_emisor
               emi.nombre as nombre_emisor
            FROM tbl_mensajes msj 
                        --  JOIN tbl_destinatarios dst ON msj.pk_mensaje  = dst.fk_mensaje
               LEFT OUTER JOIN tbl_atributos    emsj ON msj.fk_atributo = emsj.pk_atributo
                          JOIN tbl_atributos    tmsj ON msj.fk_tipo     = tmsj.pk_atributo
                          JOIN tbl_usuarios          emi ON emi.pk_usuario  = msj.fk_usuario
            WHERE msj.fk_tipo = 1727
            ORDER BY msj.pk_mensaje DESC;      
         ";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    
    }
    
    public function getAnunciosUsuario(){
      $SQL = " 
         SELECT DISTINCT
               msj.pk_mensaje,
               msj.fk_usuario as emisor,
               msj.titulo,
               msj.fecha_creacion,
              -- emi.nombre || ' ' || emi.apellido as nombre_emisor
               emi.nombre as nombre_emisor
            FROM tbl_mensajes msj 
                         -- JOIN tbl_destinatarios dst ON msj.pk_mensaje  = dst.fk_mensaje
               LEFT OUTER JOIN tbl_atributos    emsj ON msj.fk_atributo = emsj.pk_atributo
                          JOIN tbl_atributos    tmsj ON msj.fk_tipo     = tmsj.pk_atributo
                          JOIN tbl_usuarios          emi ON emi.pk_usuario  = msj.fk_usuario
            WHERE msj.fk_tipo = 1727
--            AND msj.fk_usuario = {$this->AuthSpace->userId}
            ORDER BY msj.pk_mensaje DESC;      
         ";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    
    }
    
    
        public function getMime($ext){
	   if(!is_string($ext)) return null;
      $SQL = " 
         SELECT * FROM tbl_mimes WHERE extension ilike '$ext' LIMIT 1;
         
         ";


	return (array)$results->fetchAll();
    
    }
    
    public function deleteMensaje($id, $carpeta){
        if(!is_numeric($id)) return null;
        $this->AuthSpace      = new Zend_Session_Namespace('Zend_Auth');
        $sql="UPDATE tbl_carpetasmensajes
                   SET fk_carpeta=16
                 WHERE fk_mensaje={$id} AND fk_usuario={$this->AuthSpace->userId} AND fk_carpeta = {$carpeta};";
                  
        $results = $this->_db->query($sql);

	return $results;
    }
    
    public function addNuevoMensaje($data){
        $loggerFB = Zend_Registry::get('loggerFB');
        $this->AuthSpace      = new Zend_Session_Namespace('Zend_Auth');
        $this->_db->beginTransaction();
        
        if(empty($data['pk_mensaje']) || isset($data['fk_mensaje']) || $data['fk_tipo'] == 1727){
        $table = "tbl_mensajes";
        $pkcolum = "pk_mensaje";
        $columnas = "fk_tipo, fk_usuario, titulo";
        $values = array($data['fk_tipo'], $this->AuthSpace->userId, $data['titulo']);
        
        if(!empty($data['contenido'])){
            $columnas .= ", contenido";
            array_push($values, $data['contenido']);
        }
        if(!empty($data['fk_clase'])){
            $columnas .= ", fk_clase";
            array_push($values, $data['fk_clase']);
        }
        if(!empty($data['calificacion'])){
            $columnas .= ", calificacion";
            array_push($values, $data['calificacion']);
        }
        if(!empty($data['fk_mensaje'])){
            $columnas .= ", fk_mensaje";
            array_push($values, $data['fk_mensaje']);
        }
        
        $Pcolumnas = explode(", ", $columnas);
        $count = count($Pcolumnas);
        
        $inters = "";
        for($i=0; $i<$count; $i++){
            if($i==0)
                $inter = "?";
            $inters .= $inter;
            $inter = ", ?";
        }
        
        
        $loggerFB->log($values, Zend_Log::INFO);
        
        $sql = "INSERT INTO {$table}($columnas) VALUES ({$inters}) RETURNING {$pkcolum};";
        $mensaje = $this->_db->prepare($sql);
//        $mensaje->execute($values);
        $status = $mensaje->execute($values);
        $pk_mensaje = $mensaje->fetch(PDO::FETCH_ASSOC);
        $pk_mensaje = $pk_mensaje['pk_mensaje'];
        $mensaje->closeCursor();
        
        if($data['fk_tipo'] == 1727){
            $this->_db->commit();
            return true;
        }
        }else{
            $pk_mensaje = $data['pk_mensaje'];
            $loggerFB->log($pk_mensaje, Zend_Log::INFO);
        }    
        // FIN de insert de mensajes
        // 
        if(isset($data['usuarios']) || isset($data['grupos']) || isset($data['asignaciones']) || isset($data['asignacion'])){
         if(empty($data['fk_mensaje'])){
        if(!empty($data['usuarios'])){
            $lista_usuarios = array();
            $usuarios = explode('/ ', $data['usuarios']);
            $loggerFB->log($usuarios, Zend_Log::INFO);
//            $columnas .= ", fk_grupo";
            $sql_usuario = "SELECT pk_usuario FROM tbl_usuarios WHERE pk_usuario =";
            $insert_usuario = " INSERT INTO tbl_destinatarios(fk_mensaje, fecha_envio, fk_usuario) VALUES (?, ?, ?);";
            $destinousuario = $this->_db->prepare($insert_usuario);
            foreach($usuarios as $usuario){
                if(is_numeric($usuario)){
                    $resultado = $this->_db->query($sql_usuario.$usuario);
                    if(count($resultado->fetchAll()) > 0){
                        $fecha_envio = null;
                        if($data['enviar'] === t)
                            $fecha_envio = 'now()';
                        $destinousuario->execute(array($pk_mensaje, $fecha_envio, $usuario));
                        $lista_usuarios[] = array('pk_usuario' => $usuario);
                        $loggerFB->log($lista_usuarios, Zend_Log::ERR);
                    }else{
                        $errores_usuarios .= $usuario;
                    }
                }else if($usuario != ''){
                    $errores_usuarios .= $usuario;
                }
            }
          $destinousuario->closeCursor();
        }
        
        if(!empty($data['grupos'])){
            $lista_grupos = array();
            $grupos = explode('/ ', $data['grupos']);
            $loggerFB->log($grupos, Zend_Log::INFO);
//            $columnas .= ", fk_grupo";
            $sql_grupo = "SELECT pk_atributo FROM vw_grupos WHERE pk_atributo =";
            $insert_grupo = " INSERT INTO tbl_destinatarios(fk_mensaje, fecha_envio, fk_grupo) VALUES (?, ?, ?);";
            $destinogrupo = $this->_db->prepare($insert_grupo);
            foreach($grupos as $grupo){
                if(is_numeric($grupo)){
                    $resultado = $this->_db->query($sql_grupo.$grupo);
                    if(count($resultado->fetchAll()) > 0){
                        $fecha_envio = null;
                        if($data['enviar'] === t)
                            $fecha_envio = 'now()';
                        $destinogrupo->execute(array($pk_mensaje, $fecha_envio, $grupo));
                        $lista_grupos[] = $grupo;
                    }else{
                        $errores_grupo .= $grupo;
                    }
                }else if($grupo != ''){
                    $errores_grupo .= $grupo;
                }
            }
          $destinogrupo->closeCursor();
        }
            $loggerFB->log(!(empty($data['asignaciones'])), Zend_Log::INFO);
            $loggerFB->log(!(empty($data['asignacion'])), Zend_Log::INFO);
        if(isset($data['asignaciones']) || isset($data['asignacion'])){
            $loggerFB->log('Asignacion', Zend_Log::INFO);
            $asigs = explode('/ ', $data['asignaciones']);
            $loggerFB->log($asigs, Zend_Log::INFO);
//            $columnas .= ", fk_grupo";
            $sql_asig = "SELECT pk_asignacion FROM tbl_asignaciones WHERE pk_asignacion =";
            $insert_asig = " INSERT INTO tbl_destinatarios(fk_mensaje, fecha_envio, fk_asignacion) VALUES (?, ?, ?);";
            $destinoasig = $this->_db->prepare($insert_asig);
            $loggerFB->log("entro", Zend_Log::ERR);
            $loggerFB->log(isset($data['asignacion']), Zend_Log::ERR);
            $loggerFB->log($data['asignacion'], Zend_Log::ERR);
            if(isset($data['asignacion'])){
                $loggerFB->log("entro", Zend_Log::ERR);
                $asign = $data['asignacion'];
                if(is_numeric($asign)){
                $loggerFB->log("num entro", Zend_Log::ERR);
                    $resultado = $this->_db->query($sql_asig.$asign);
                    if(count($resultado->fetchAll()) > 0){
                        $loggerFB->log("asig valida", Zend_Log::ERR);
                        $fecha_envio = null;
                        if($data['enviar'] === t)
                            $fecha_envio = 'now()';
                        $destinoasig->execute(array($pk_mensaje, $fecha_envio, $asign));
                        $loggerFB->log($asign, Zend_Log::ERR);
                        $lista_asignaciones[] = $asign;
                        $loggerFB->log($lista_asignaciones, Zend_Log::ERR);
                    }else{
                        $errores_asig .= $asign;
                    }
                }else if($asig != ''){
                    $errores_asig .= $asign;
                }
            }
            if(is_array($asigs)){
            foreach($asigs as $asig){
                if(is_numeric($asig)){
                    $resultado = $this->_db->query($sql_asig.$asig);
                    if(count($resultado->fetchAll()) > 0){
                        $fecha_envio = null;
                        if($data['enviar'] === t)
                            $fecha_envio = 'now()';
                        $destinoasig->execute(array($pk_mensaje, $fecha_envio, $asig));
                        $lista_asignaciones[] = $asig;
                    }else{
                        $errores_asig .= $asig;
                    }
                }else if($asig != ''){
                    $errores_asig .= $asig;
                }
            }
            }
          $destinoasig->closeCursor();
        }
        
        
        //Fin insert destinatarios
        //
        }else{
            $sql = "SELECT fk_usuario FROM tbl_mensajes WHERE pk_mensaje = {$data['fk_mensaje']}";
            $resultado = $this->_db->query($sql);
            $resultado = $resultado->fetchAll();
            $insert_usuario = " INSERT INTO tbl_destinatarios(fk_mensaje, fecha_envio, fk_usuario) VALUES (?, ?, ?);";
            $destinousuario = $this->_db->prepare($insert_usuario);
            $loggerFB->log($resultado, Zend_Log::ERR);
            $destinousuario->execute(array($pk_mensaje, 'now()', $resultado[0]['fk_usuario']));
            $loggerFB->log('Respondido', Zend_Log::ERR);
                        $loggerFB->log(array($pk_mensaje, 'now()', $resultado[0]['fk_usuario']), Zend_Log::ERR);
            $lista_usuarios[] = array('pk_usuario' => $resultado[0]['fk_usuario']);
            
        }
        }
        
        
        
        $table = "tbl_carpetasmensajes";
        $pkcolum = "pk_carpetamensaje";
        $columnas = "fk_mensaje, fk_carpeta, fk_usuario";

        $values = array();
        $destino = array();
        if(count($lista_usuarios) > 0)
            $destino = array_merge ($destino, $lista_usuarios);
                        $loggerFB->log('$lista_usuarios', Zend_Log::ERR);
                        $loggerFB->log($lista_usuarios, Zend_Log::ERR);

        if(count($lista_grupos) > 0){
                $consulta = "SELECT DISTINCT 
                        fk_usuario as pk_usuario
                FROM tbl_usuariosgrupos
                WHERE fk_grupo =";
            foreach ($lista_grupos as $value) {
                $resultado = $this->_db->query($consulta.$value);
                $resultado = $resultado->fetchAll();
                $loggerFB->log($resultado, Zend_Log::ERR);
                if(count($resultado) > 0){
                    array_merge($destino, $resultado);
                }
                
            }
        }
        
        $loggerFB->log('$destino', Zend_Log::ERR);
        $loggerFB->log($destino, Zend_Log::ERR);
        $loggerFB->log('lista_asignaciones', Zend_Log::ERR);
        $loggerFB->log($lista_asignaciones, Zend_Log::ERR);
        if(count($lista_asignaciones) > 0){
                $consulta = "SELECT DISTINCT 
                        ue.pk_usuario
                FROM tbl_recordsacademicos ra
                JOIN tbl_inscripciones      i ON  i.pk_inscripcion   = ra.fk_inscripcion
                JOIN tbl_asignaciones      ac ON ac.pk_asignacion    = ra.fk_asignacion
                JOIN tbl_asignaturas       ag ON ag.pk_asignatura    = ra.fk_asignatura
                JOIN tbl_usuariosgrupos   uge ON uge.pk_usuariogrupo = i.fk_usuariogrupo
                JOIN tbl_usuarios          ue ON ue.pk_usuario       = uge.fk_usuario
                WHERE ac.pk_asignacion =";
            foreach ($lista_asignaciones as $value) {
                $resultado = $this->_db->query($consulta.$value);
                $resultado = $resultado->fetchAll();
                $loggerFB->log($resultado, Zend_Log::ERR);
                if(count($resultado) > 0){
                    $destino = array_merge($destino, $resultado);
                }
            }  
        }
        
        $loggerFB->log('Destino', Zend_Log::ERR);
        $loggerFB->log($destino, Zend_Log::ERR);
//        $destino = array_unique($destino);
        $loggerFB->log('Cedulas', Zend_Log::ERR);
        $loggerFB->log($destino, Zend_Log::ERR);
        $values = array();
        if($data['enviar'] === 't'){
        $loggerFB->log('ENVIAR', Zend_Log::WARN);
            if(!empty($destino)){
                reset($destino);
                foreach ($destino as $recep) {
                $loggerFB->log((int)$recep['pk_usuario'], Zend_Log::WARN);
                $loggerFB->log($values, Zend_Log::ERR);
                    array_push($values, array($pk_mensaje, 1, (int)$recep['pk_usuario']));
                }
            }
        array_push($values, array($pk_mensaje, 2, $this->AuthSpace->userId));
        }else{
        array_push($values, array($pk_mensaje, 3, $this->AuthSpace->userId));
        }

        $Pcolumnas = explode(", ", $columnas);
        $count = count($Pcolumnas);

        $inters = "";
        for($i=0; $i<$count; $i++){
            if($i==0)
                $inter = "?";
            $inters .= $inter;
            $inter = ", ?";
        }

        $loggerFB->log('$values', Zend_Log::ERR);
        $loggerFB->log($values, Zend_Log::INFO);

        $sql = "INSERT INTO {$table}($columnas) VALUES ({$inters});";
        $loggerFB->log($sql, Zend_Log::INFO);
        $carpetas = $this->_db->prepare($sql);
//        $mensaje->execute($values);
        $desfail = null;
        foreach($values as $value){
            $loggerFB->log($value, Zend_Log::WARN);
            if(!$carpetas->execute($value))
                    $destfail .= $value[1];
        }

        $loggerFB->log($status, Zend_Log::ERR);

        //Adjuntos
        if(!empty($data['adjuntos'])){
            $table = "tbl_adjuntos";
            $pkcolum = "pk_adjunto";
            $columnas = "fk_mensaje, fk_tipo, dir_archivo";
            $default_dir = "/Users/nieldm/Sites/MiUNECDE/public/uploads/mensajes/{$this->AuthSpace->userId}/";

            $values = array();
            $adjuntos = explode('}, ', $data['adjuntos']);
            foreach($adjuntos as $adjunto){
                $adjunto = explode(', ', $adjunto);
                $loggerFB->log($adjunto, Zend_Log::ERR);
                array_push($values, array($pk_mensaje, 1722, $adjunto[1]));
            }
        
        $Pcolumnas = explode(", ", $columnas);
        $count = count($Pcolumnas);
//
        $inters = "";
        for($i=0; $i<$count; $i++){
            if($i==0)
                $inter = "?";
            $inters .= $inter;
            $inter = ", ?";
        }
//
        $loggerFB->log($values, Zend_Log::INFO);
//
        $sql = "INSERT INTO {$table}($columnas) VALUES ({$inters});";
        $loggerFB->log($sql, Zend_Log::INFO);
        $tbladjuntos = $this->_db->prepare($sql);
        $desfail = null;
        foreach($values as $value){
            $loggerFB->log($value, Zend_Log::WARN);
            if(!$tbladjuntos->execute($value))
                    $destfail .= $value[1];
        }

        $loggerFB->log($status, Zend_Log::ERR);
        $tbladjuntos->closeCursor();
        }
        
//        Fin adjuntos!!!!!!!!!!!
//
//
//        $this->_db->execute();
            $this->_db->commit();
//        $this->_db->rollBack();
        return true;
        
    }
    
      public function getFiltros($per = 121, $sede, $escuela, $semestre, $materia){
      $this->AuthSpace      = new Zend_Session_Namespace('Zend_Auth');

      $SQL = "SELECT DISTINCT
                       es3.pk_estructura as pk_sede,
                       es3.nombre as sede,
                       vwe.pk_atributo as pk_escuela,
                       vwe.escuela as escuela,
                       vws.pk_atributo as pk_semestre,
                       vws.id as semestre,
                       vwmt.pk_atributo as pk_materia,
                       vwmt.materia as materia,
                       vwse.pk_atributo as pk_seccion,
                       asg.pk_asignacion as pk_seccion,
                       vwse.valor as seccion,
                       ast.codigopropietario as codigo
                FROM   tbl_asignaciones asg
                JOIN tbl_usuariosgrupos usg ON usg.pk_usuariogrupo = asg.fk_usuariogrupo
                JOIN tbl_usuarios        us ON us.pk_usuario       = usg.fk_usuario
                JOIN tbl_asignaturas    ast ON ast.pk_asignatura   = asg.fk_asignatura
                JOIN tbl_estructuras    es1 ON es1.pk_estructura   = asg.fk_estructura
                JOIN tbl_estructuras    es2 ON es2.pk_estructura   = es1.fk_estructura
                JOIN tbl_estructuras    es3 ON es3.pk_estructura   = es2.fk_estructura
                JOIN tbl_pensums         pp ON pp.pk_pensum        = ast.fk_pensum
                JOIN vw_materias       vwmt ON vwmt.pk_atributo    = ast.fk_materia
                JOIN vw_escuelas       vwe  ON vwe.pk_atributo     = pp.fk_escuela
                JOIN vw_semestres       vws ON vws.pk_atributo     = asg.fk_semestre
                JOIN vw_secciones      vwse ON vwse.pk_atributo    = asg.fk_seccion
                JOIN tbl_recordsacademicos  ra ON asg.pk_asignacion   = ra.fk_asignacion
                JOIN tbl_inscripciones  ins ON ins.pk_inscripcion   = ra.fk_inscripcion
                JOIN tbl_usuariosgrupos usgE ON usgE.pk_usuariogrupo = ins.fk_usuariogrupo
                JOIN tbl_usuarios        usE ON usE.pk_usuario       = usgE.fk_usuario
                WHERE (usE.pk_usuario  = {$this->AuthSpace->userId} OR us.pk_usuario = {$this->AuthSpace->userId})
                 AND ins.fk_periodo = $per";
    if(!empty($sede) && is_numeric($sede))
       $SQL .= "AND es3.pk_estructura = $sede";
    if(!empty($escuela) && is_numeric($escuela))
       $SQL .= "AND vwe.pk_atributo = $escuela";
    if(!empty($semestre) && is_numeric($semestre))
       $SQL .= "AND vws.pk_atributo = $semestre";
    if(!empty($materia) && is_numeric($materia))
       $SQL .= "AND vwmt.pk_atributo = $materia";
       $SQL .= "ORDER  BY 8;
";
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

   }
   
      public function getFiltrosDirEscuela($per = 121){

      $SQL = "SELECT DISTINCT
                       es3.pk_estructura as pk_sede,
                       es3.nombre as sede,
                       vwe.pk_atributo as pk_escuela,
                       vwe.escuela as escuela,
                       vws.pk_atributo as pk_semestre,
                       vws.id as semestre,
                       vwmt.pk_atributo as pk_materia,
                       vwmt.materia as materia,
                       vwse.pk_atributo as pk_seccion,
                       asg.pk_asignacion as pk_seccion,
                       vwse.valor as seccion,
                       ast.codigopropietario as codigo
                FROM   tbl_asignaciones asg
                JOIN tbl_usuariosgrupos usg ON usg.pk_usuariogrupo = asg.fk_usuariogrupo
                JOIN tbl_usuarios        us ON us.pk_usuario       = usg.fk_usuario
                JOIN tbl_asignaturas    ast ON ast.pk_asignatura   = asg.fk_asignatura
                JOIN tbl_estructuras    es1 ON es1.pk_estructura   = asg.fk_estructura
                JOIN tbl_estructuras    es2 ON es2.pk_estructura   = es1.fk_estructura
                JOIN tbl_estructuras    es3 ON es3.pk_estructura   = es2.fk_estructura
                JOIN tbl_pensums         pp ON pp.pk_pensum        = ast.fk_pensum
                JOIN vw_materias       vwmt ON vwmt.pk_atributo    = ast.fk_materia
                JOIN vw_escuelas       vwe  ON vwe.pk_atributo     = pp.fk_escuela
                JOIN vw_semestres       vws ON vws.pk_atributo     = asg.fk_semestre
                JOIN vw_secciones      vwse ON vwse.pk_atributo    = asg.fk_seccion
                JOIN tbl_recordsacademicos  ra ON asg.pk_asignacion   = ra.fk_asignacion
                JOIN tbl_inscripciones  ins ON ins.pk_inscripcion   = ra.fk_inscripcion
                JOIN tbl_usuariosgrupos usgE ON usgE.pk_usuariogrupo = ins.fk_usuariogrupo
                JOIN tbl_usuarios        usE ON usE.pk_usuario       = usgE.fk_usuario
                WHERE ins.fk_periodo = $per
                ORDER  BY 8;
                ";
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

   }
   
      public function getListaUsuarios($grupos = 855){

        $SQL = "SELECT DISTINCT usu.pk_usuario,
                     usu.apellido || ' ' || usu.nombre as nombre
                FROM tbl_usuarios usu
                JOIN tbl_usuariosgrupos ug ON usu.pk_usuario = ug.fk_usuario
              WHERE ug.fk_grupo IN ({$grupos})
              ORDER BY 2;";
              
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

   }
   
      public function getListaGrupos($grupos = 855){

        $SQL = "SELECT DISTINCT ug.fk_grupo, grvw.grupo
                FROM tbl_usuarios usu
                JOIN tbl_usuariosgrupos ug ON usu.pk_usuario = ug.fk_usuario
                JOIN vw_grupos grvw ON grvw.pk_atributo = ug.fk_grupo
              WHERE ug.fk_grupo IN ({$grupos})
              ORDER BY 2;";
              
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

   }

}
