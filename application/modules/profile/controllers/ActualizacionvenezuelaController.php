<?php

/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 14/03/14
 * Time: 11:40 AM
 */
class Profile_ActualizacionvenezuelaController extends Zend_Controller_Action
{


    public function init()
    {
        Zend_Loader::loadClass('Forms_actualizacionvnzla');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_BibliotecaAgregar');
        Zend_Loader::loadClass('Models_DbView_actualizacionvnzla');

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->Bd = new Models_DbView_actualizacionvnzla();
        $this->auth = Zend_Auth::getInstance();
        $this->Usuarios = new Models_DbTable_Usuarios();
        $this->Biblioteca = new Models_DbTable_BibliotecaAgregar();
        $this->SwapBytes_Jquery_Mask = new SwapBytes_Jquery_Mask();
        $this->SwapBytes_Jquery = new SwapBytes_Jquery();
        $this->SwapBytes_Crud_Form = new SwapBytes_Crud_Form();
        $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->SwapBytes_Form = new SwapBytes_Form();
        $this->SwapBytes_Ajax = new SwapBytes_Ajax();

        $this->view->form = new Forms_actualizacionvnzla();


    }

    function preDispatch()
    {

        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }
    }


    public function indexAction()
    {
        $this->view->title = "Actualizacion de Banco Venezuela";
        $this->view->SwapBytes_Ajax = $this->SwapBytes_Ajax;
        $this->form->SwapBytes_Ajax = $this->SwapBytes_Ajax;
        $this->view->SwapBytes_Jquery_Mask = $this->SwapBytes_Jquery_Mask;

        /*Llenar el formulario con lo contenido en la base de datos
         * */
        $array = $this->Usuarios->getPerson($this->AuthSpace->userId);
        $act = $this->Bd->getActualizado($this->AuthSpace->userId);

        if ($act[0]['actualizado']) {
            echo "<script> $('#act').dialog({ autoOpen: true, resizable: false, closeOnEscape: false,  modal: true, position: ['middle', 350], buttons:{ Aceptar:function(){ $(this).dialog('close');}, Cancelar:function(){location.href = \"/MiUNE2/inicio\";}}}); </script>";
        }

        $a = $this->Bd->getOtherInfo($this->AuthSpace->userId);

        if ($a[0]['actualizado']) {

            $datos['pk_usuario'] = $a[0]['pk_usuario'];
            $datos['nombre'] = $a[0]['nombres'];
            $datos['primer_apellido'] = $a[0]['primer_apellido'];
            $datos['segundo_apellido'] = $a[0]['segundo_apellido'];
            $datos['fechanacimiento'] = $a[0]['fecha_nacimiento'];
            $datos['correo'] = $a[0]['correo'];
            $datos['codigo_telefono'] = $a[0]['codigo_area'];
            $datos['telefono'] = $a[0]['telefono'];
            $datos['telefono_movil'] = $a[0]['telefono_movil'];
            $datos['codigo_fax'] = $a[0]['codigo_fax'];
            $datos['numero_fax'] = $a[0]['numero_fax'];
            $datos['tipo_ocupacion'] = $a[0]['ocupacion'];
            $datos['tipo_via'] = $a[0]['tipo_via'];
            $datos['descripcion_via'] = $a[0]['descripcion_via'];
            $datos['tipo_vivienda'] = $a[0]['tipo_vivienda'];
            $datos['nombre_vivienda'] = $a[0]['nombre_vivienda'];
            $datos['punto_referencia'] = $a[0]['punto_referencia_vivienda'];
            $datos['tipo_nucleo'] = $a[0]['tipo_nucleo'];
            $datos['descripcion_nucleo'] = $a[0]['descripcion_nucleo'];
            $datos['estado'] = $a[0]['estado_vivienda'];
            $datos['ciudad'] = $a[0]['cuidad_vivienda'];
            $datos['municipio'] = $a[0]['municipio_vivienda'];
            $datos['zona_postal'] = $a[0]['zona_postal_vivienda'];
            $datos['codigo_fax'] = $a[0]['codigo_area_fax'];
            $datos['numero_fax'] = $a[0]['fax'];
            $datos['cargo_empresa'] = $a[0]['cargo_empresa'];
            $datos['actividad_economica'] = $a[0]['actividad_economica'];
            $datos['tipo_empleado'] = $a[0]['tipo_empleado'];
            $datos['codigo_area_telf_oficina'] = $a[0]['codigo_area_oficina'];
            $datos['numero_oficina'] = $a[0]['telefono_oficina'];
            $datos['codigo_oficina_banco'] = $a[0]['codigo_oficina'];

        }else{

        $datos['pk_usuario'] = $this->AuthSpace->userId;
        $datos['nombre'] = $array[0]['nombre'];
        $datos['primer_apellido'] = $array[0]['primer_apellido'];
        $datos['segundo_apellido'] = $array[0]['segundo_apellido'];
        $datos['fechanacimiento'] = $array[0]['fecha_nacimiento'];
        $datos['correo'] = $array[0]['correo'];
        $datos['codigo_telefono'] = substr($array[0]['telefono'], 1, 3);
        $datos['telefono'] = substr($array[0]['telefono'], 4);
        $datos['telefono_movil'] = substr($array[0]['telefono_movil'], 1);
        $datos['codigo_fax'] = substr($array[0]['telefono'], 1, 3);
        $datos['numero_fax'] = substr($array[0]['telefono'], 4);
        }

        $this->view->form->populate($datos);

    }

    public function verificarAction()
    {
        $this->SwapBytes_Ajax->setHeader();

        $data[] = $this->_getAllParams();

        $data[0]['telefono'] = $this->SwapBytes_Jquery_Mask->unmaskShortPhone($data[0]['telefono']);
        $data[0]['numero_fax'] = $this->SwapBytes_Jquery_Mask->unmaskShortPhone($data[0]['numero_fax']);
        $data[0]['numero_oficina'] = $this->SwapBytes_Jquery_Mask->unmaskShortPhone($data[0]['numero_oficina']);

        $soloInput[0]['nombre'] = $data[0]['nombre'];
        $soloInput[0]['primer_apellido'] = $data[0]['primer_apellido'];
        $soloInput[0]['descripcion_via'] = $data[0]['descripcion_via'];
        $soloInput[0]['punto_referencia'] = $data[0]['punto_referencia'];
        $soloInput[0]['descripcion_nucleo'] = $data[0]['descripcion_nucleo'];
        $soloInput[0]['municipio'] = $data[0]['municipio'];

        $soloInput[1]['numero_fax'] = $data[0]['numero_fax'];
        $soloInput[1]['numero_oficina'] = $data[0]['numero_oficina'];
        $soloInput[1]['telefono'] = $data[0]['telefono'];
        $soloInput[1]['telefono_movil'] = $data[0]['telefono_movil'];
        $soloInput[1]['fechanacimiento'] = $data[0]['fechanacimiento'];

        $soloInput[2]['tipo_vivienda'] = $data[0]['tipo_vivienda'];
        $soloInput[2]['tipo_ocupacion'] = $data[0]['tipo_ocupacion'];
        $soloInput[2]['tipo_via'] = $data[0]['tipo_via'];
        $soloInput[2]['tipo_nucleo'] = $data[0]['tipo_nucleo'];
        $soloInput[2]['estado'] = $data[0]['estado'];
        $soloInput[2]['ciudad'] = $data[0]['ciudad'];
        $soloInput[2]['zona_postal'] = $data[0]['zona_postal'];
        $soloInput[2]['codigo_fax'] = $data[0]['codigo_fax'];
        $soloInput[2]['cargo_empresa'] = $data[0]['cargo_empresa'];
        $soloInput[2]['actividad_economica'] = $data[0]['actividad_economica'];
        $soloInput[2]['tipo_empleado'] = $data[0]['tipo_empleado'];
        $soloInput[2]['codigo_area_telf_oficina'] = $data[0]['codigo_area_telf_oficina'];
        $soloInput[2]['codigo_oficina_banco'] = $data[0]['codigo_oficina_banco'];
        $soloInput[2]['codigo_telefono'] = $data[0]['codigo_telefono'];

        $this->enviarJson($this->validarData($soloInput[0], $soloInput[1], $soloInput[2], $data[0]['correo'], $data[0]['segundo_apellido'], $data[0]['nombre_vivienda']));

    }

    public function actualizarAction()
    {

        $this->SwapBytes_Ajax->setHeader();

        $data[] = $this->_getAllParams();

        $data[0]['telefono'] = $this->SwapBytes_Jquery_Mask->unmaskShortPhone($data[0]['telefono']);
        $data[0]['numero_fax'] = $this->SwapBytes_Jquery_Mask->unmaskShortPhone($data[0]['numero_fax']);
        $data[0]['numero_oficina'] = $this->SwapBytes_Jquery_Mask->unmaskShortPhone($data[0]['numero_oficina']);

        $datos['nombre'] = $data[0]['nombre'];
        $datos['segundo_apellido'] = $data[0]['segundo_apellido'];
        $datos['primer_apellido'] = $data[0]['primer_apellido'];
        $datos['descripcion_via'] = $data[0]['descripcion_via'];
        $datos['nombre_vivienda'] = $data[0]['nombre_vivienda'];
        $datos['punto_referencia'] = $data[0]['punto_referencia'];
        $datos['descripcion_nucleo'] = $data[0]['descripcion_nucleo'];
        $datos['municipio'] = $data[0]['municipio'];

        $datos['numero_fax'] = $data[0]['numero_fax'];
        $datos['numero_oficina'] = $data[0]['numero_oficina'];
        $datos['telefono'] = $data[0]['telefono'];
        $datos['telefono_movil'] = $this->SwapBytes_Jquery_Mask->unmaskZeroPhone($data[0]['telefono_movil']);
        $datos['correo'] = $data[0]['correo'];

        $datos['cedula'] = $this->AuthSpace->userId;
        $datos['fechanacimiento'] = date_format(date_create_from_format('d/m/Y', $data[0]['fechanacimiento']), 'Y-m-d');
        $datos['tipo_vivienda'] = $data[0]['tipo_vivienda'];
        $datos['tipo_ocupacion'] = $data[0]['tipo_ocupacion'];
        $datos['tipo_via'] = $data[0]['tipo_via'];
        $datos['tipo_nucleo'] = $data[0]['tipo_nucleo'];
        $datos['estado'] = $data[0]['estado'];
        $datos['ciudad'] = $data[0]['ciudad'];
        $datos['zona_postal'] = $data[0]['zona_postal'];
        $datos['codigo_fax'] = $data[0]['codigo_fax'];
        $datos['codigo_telefono'] = $data[0]['codigo_telefono'];
        $datos['cargo_empresa'] = $data[0]['cargo_empresa'];
        $datos['actividad_economica'] = $data[0]['actividad_economica'];
        $datos['tipo_empleado'] = $data[0]['tipo_empleado'];
        $datos['codigo_area_telf_oficina'] = $data[0]['codigo_area_telf_oficina'];
        $datos['codigo_oficina_banco'] = $data[0]['codigo_oficina_banco'];

        $this->Bd->updateUser($datos);

        $json[] = "$('#modal').dialog('close')";
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->getResponse()->setBody(Zend_Json::encode($json));

    }

    public function validarData($data, $data2, $data3, $correo, $segundo_apellido, $casa)
    {
        $bool = array();


        //validar textbox
        foreach ($data as $key => $dato) {

            if ($dato == null) {
                $b[] = $key;
                $b[] = "El campo no puede estar vacio";
                $bool[] = $b;
            } elseif (preg_match('#[0-9]#', $dato)) {
                $b[] = $key;
                $b[] = "El campo no puede tener numeros";
                $bool[] = $b;

            }
            $b = null;
        }

        //Validacion de segundo apellido
        if($segundo_apellido != null)
        {
            if(preg_match('#[0-9]#', $segundo_apellido))
            {
                $b[] = "segundo_apellido";
                $b[] = "El campo no puede tener numeros";
                $bool[] = $b;
            }
        }else
        {
            $b[] = "segundo_apellido";
            $b[] = "El campo no puede estar vacio";
            $bool[] = $b;
        }

        $b = null;

        //validar numeros de telefono
        foreach ($data2 as $key => $dato) {

            if ($dato == NULL) {
                $b[] = $key;
                $b[] = "El campo no puede estar vacio";
                $bool[] = $b;
            }
            $b = null;

        }

        //validacion de correo
        if ($correo != null) {
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $b[] = "correo";
                $b[] = "Ej: nombredeusuario@ejemplo.com";
                $bool[] = $b;
            }
        }else
        {
            $b[] = "correo";
            $b[] = "El campo no puede estar vacio";
            $bool[] = $b;
        }

        $b = null;

        if($casa == null)
        {
            $b[] = "correo";
            $b[] = "El campo no puede estar vacio";
            $bool[] = $b;
        }

        $b = null;

        //validacion de combobox en la primera opcion
        foreach($data3 as $key => $dato)
        {
            if ($dato == "Seleccione una opcion") {

                $b[] = $key;
                $b[] = "Por favor, seleccione una opcion";
                $bool[] = $b;
            }
            $b = null;
        }

        //agrego todos los array en uno para retornarlo
        return $bool;

    }

    public function enviarJson($key)
    {
        if ($key != null) {
            foreach ($key as $id => $seg)
            {
                $json[] = "$('#" . $seg['0'] . "-element').append('<p class=\'error\'><br> " . $seg['1'] . " </p>')";
            }

            $this->SwapBytes_Crud_Form->setJson($json);
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

}
