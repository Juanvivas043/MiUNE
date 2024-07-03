<?php
class Zend_Validate_CedulaMatch extends Zend_Validate_Abstract
{
    protected $_options;

    const NOT_MATCH = 'cedulaMatch';

    protected $_messageTemplates = array(
        self::NOT_MATCH => "La cedula del estudiante no existe.",
    );

    protected $grupo;

    public function __construct()
    {
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        $this->grupo = new Models_DbTable_UsuariosGrupos();
    }

    public function isValid($value)
    {
        $cedula = $this->grupo->isUserEstudiante($value);

        if ($cedula == false) {
            $this->_error(self::NOT_MATCH);
            return false;
        }

        return true;
    }
}

