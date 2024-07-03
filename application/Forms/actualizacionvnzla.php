<?php

/**
 * Created by PhpStorm.
 * User: andreas
 * Date: 14/03/14
 * Time: 11:57 AM
 */
class Forms_actualizacionvnzla extends Zend_Form
{

    public function init()
    {
        include('../public/fillSelect.php');
        $this->setMethod("POST");
        $this->setName("ActualizacionVenezuela");
        $this->setAttrib("id", "actualizacionvnzla");
        $this->setOptions(array('escape' => true));


        //$firsttitle = new Zend_Form_Decorator_Label('Informacion principal');

        $cedula = new Zend_Form_Element_Text('pk_usuario');
        $cedula->setLabel('Cedula:')
            ->setAttrib('width', 130)
            ->addValidator('Digits')
            ->addValidator('NotEmpty')
            ->setAttrib('maxLength', 8)
            ->setAttrib('disabled', 'disabled')
            ->setAttrib('class', 'formulario');


        $nombre = new Zend_Form_Element_Text('nombre');
        $nombre->setLabel('Nombres:')
            ->setRequired(true)
            ->addFilters(array('StripTags', 'StringTrim'))
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array(2, 45))
            ->setAttrib('class', 'formulario')
            ->setAttrib('Required', 'Required');

        $primer_apellido = new Zend_Form_Element_Text('primer_apellido');
        $primer_apellido->setLabel('Primer apellido:')
            ->setRequired(true)
            ->addFilters(array('StripTags', 'StringTrim'))
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array(2, 45))
            ->setAttrib('class', 'formulario')
            ->setAttrib('Required', 'Required');

        $segundo_apellido = new Zend_Form_Element_Text('segundo_apellido');
        $segundo_apellido->setLabel('Segundo apellido:')
            ->setRequired(true)
            ->addFilters(array('StripTags', 'StringTrim'))
            ->addValidator('StringLength', true, array(2, 45))
            ->setAttrib('class', 'formulario');

        $fechanacimiento = new Zend_Form_Element_Text('fechanacimiento');
        $fechanacimiento->setLabel('Fecha de nacimiento:')
            ->setRequired(true)
            ->addFilters(array('StripTags', 'StringTrim'))
            ->addValidator('Date', true, array('dd/MM/YYYY'))
            ->setAttrib('class', 'formulario')
            ->setAttrib('Required', 'Required')
            ->setAttrib('maxlength', '8');

        $correo = new Zend_Form_Element_Text('correo');
        $correo->setLabel('E-mail:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', true, array(11, 255))
            ->setAttrib('class', 'formulario')
            ->setAttrib('Required', 'Required')
            ->setAttrib("data-help", "Ejemplo: nombredeusuario@ejemplo.com");

        $codigo_telefono = new Zend_Form_Element_Select('codigo_telefono');
        $codigo_telefono->setLabel('Codigo de area')
                         ->setAttrib('class', 'formulario')
                         ->addMultiOptions(getCodigoArea());


        $telefono = new Zend_Form_Element_Text("telefono");
        $telefono->setLabel("Teléfono:")
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', true, array(15, 15))
            ->setAttrib('class', 'formulario')
            ->setAttrib('Required', 'Required');

        $telefono_movil = new Zend_Form_Element_Text("telefono_movil");
        $telefono_movil->setLabel("Teléfono celular:")
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', true, array(15, 15))
            ->setAttrib('class', 'formulario')
            ->setAttrib('Required', 'Required')
            ->setAttrib('data-help', 'Ej: (414)123.45.67');


        //BOTON ENVIAR
        $submitButton = new Zend_Form_Element_Button('submitButton');
        $submitButton->setLabel('Enviar')
            ->setAttrib('value', 'Enviar')
            ->setAttrib('class', 'ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only')
            ->setAttrib('id', 'submitButton');

        $tipo_ocupacion = new Zend_Form_Element_Select('tipo_ocupacion');
        $tipo_ocupacion->setLabel('Tipo de ocupación:');
        $tipo_ocupacion->addMultiOptions(getOcupacion())
            ->setAttrib('class', 'formulario')
            ->setAttrib('Required', 'Required');

        $tipo_via = new Zend_Form_Element_Select('tipo_via');
        $tipo_via->setLabel('Tipo de vía:');
        $tipo_via->addMultiOptions(getVia())
            ->setAttrib('class', 'formulario');

        $descripcion_via = new Zend_Form_Element_Text('descripcion_via');
        $descripcion_via->setLabel('Descripción de vìa:')
            ->addFilter('StripTags')
            ->addValidator('NotEmpty')
            ->setAttrib('class', 'formulario')
            ->setAttrib('data-help', 'Ejemplo: Libertador, refiriendose a Av. Libertador')
            ->setAttrib('Required', 'Required');


        $tipo_vivienda = new Zend_Form_Element_Select('tipo_vivienda');
        $tipo_vivienda->setLabel('Tipo de vivienda')
                      ->setAttrib('class', 'formulario')
                      ->addMultiOptions(getVivienda());

        $nombre_vivienda = new Zend_Form_Element_Text('nombre_vivienda');
        $nombre_vivienda->setLabel('Nombre de vivienda:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->setAttrib('class', 'formulario')
            ->setAttrib('data-help', 'Ejemplo: "25" refiriendose a la casa numero 25 o "Estoril Palace" refiriendose al Edificio Estoril Palace')
            ->setAttrib('Required', 'Required');

        $punto_referencia = new Zend_Form_Element_Text('punto_referencia');
        $punto_referencia->setLabel('Punto de referencia:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->setAttrib('class', 'formulario')
            ->setAttrib('data-help', 'Ejemplo: Panaderia Nugantina')
            ->setAttrib('Required', 'Required')
            ->setAttrib('data-help', 'Coloque el sitio mas cercano a su domicilio');

        $tipo_nucleo = new Zend_Form_Element_Select('tipo_nucleo');
        $tipo_nucleo->setLabel('Tipo de núcleo:')
            ->addMultiOptions(getNucleo())
            ->setAttrib('class', 'formulario')
            ->setAttrib('Required', 'Required');

        $descripcion_nucleo = new Zend_Form_Element_Text('descripcion_nucleo');
        $descripcion_nucleo->setLabel('Descripcion del núcleo:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->setAttrib('class', 'formulario')
            ->setAttrib('data-help', 'Ejemplo: Nueva Casarapa, refiriendose a la Urbanizacion Nueva Casarapa')
            ->setAttrib('Required', 'Required');

        $estado = new Zend_Form_Element_Select('estado');
        $estado->setLabel('Estado:')
            ->setAttrib('class', 'formulario')
            ->setAttrib('Required', 'Required')
            ->addMultiOptions(getEstado());

        $ciudad = new Zend_Form_Element_Select('ciudad');
        $ciudad->setLabel('Ciudad:')
            ->setAttrib('class', 'formulario')
            ->setAttrib('Required', 'Required')
            ->addMultiOptions(getCiudad());

        $municipio = new Zend_Form_Element_Text('municipio');
        $municipio->setLabel('Municipio:')
            ->setAttrib('class', 'formulario')
            ->setAttrib('Required', 'Required');

        $zona_postal = new Zend_Form_Element_Select('zona_postal');
        $zona_postal->setLabel('Zona Postal:')
            ->addMultiOptions(getPostal())
            ->setAttrib('class', 'formulario')
            ->setAttrib('Required', 'Required');

        $codigo_fax = new Zend_Form_Element_Select('codigo_fax');
        $codigo_fax->setLabel('Código de Fax: ')
            ->addMultiOptions(getCodigoArea())
            ->setAttrib('class', 'formulario')
            ->setAttrib('Required', 'Required');

        $numero_fax = new Zend_Form_Element_Text('numero_fax');
        $numero_fax->setLabel('Número de Fax: ')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('Digits')
            ->addValidator('StringLength', true, array(15, 15))
            ->setAttrib('class', 'formulario')
            ->setAttrib('Required', 'Required')
            ->setAttrib('id', 'numero_fax')
            ->setAttrib("data-help", "Sede Los Naranjos: 987.14.12. Sede Centro: 484.50.53");

        $cargo_empresa = new Zend_Form_Element_Select('cargo_empresa');
        $cargo_empresa->setLabel('Cargo en Empresa: ')
            ->addMultiOptions(getCargo())
            ->setAttrib('class', 'formulario')
            ->setAttrib('Required', 'Required');

        $actividad_economica = new Zend_Form_Element_Select('actividad_economica');
        $actividad_economica->setLabel('Actividad Económica:')
            ->addMultiOptions(getActividad())
            ->setAttrib('class', 'formulario');


        $tipo_empleado = new Zend_Form_Element_Select('tipo_empleado');
        $tipo_empleado->setLabel('Tipo empleado:')
            ->addMultiOptions(getEmpleado())
            ->setAttrib('class', 'formulario')
            ->setAttrib('Required', 'Required');

        $codigo_area_telf_oficina = new Zend_Form_Element_Select('codigo_area_telf_oficina');
        $codigo_area_telf_oficina->setLabel('Código Área teléfono de Oficna:')
            ->addMultiOptions(array("212" => "212"))
            ->setAttrib('class', 'formulario');

        $numero_oficina = new Zend_Form_Element_Text('numero_oficina');
        $numero_oficina->setLabel('Número de oficina:')
            ->setAttrib('id', 'numero_oficina')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', true, array(15, 15))
            ->setAttrib('class', 'formulario')
            ->setAttrib('data-help', 'Sede Los Naranjos: 985.29.36. Sede Centro: 484.99.77');

        $codigo_oficina_banco = new Zend_Form_Element_Select('codigo_oficina_banco');
        $codigo_oficina_banco->setLabel('Oficina de Banco Venezuela:')
            ->addMultiOptions(getOficinaBanco())
            ->setAttrib('class', 'formulario')
            ->setAttrib('Required', 'Required')
            ->setAttrib('data-help', 'Coloque la oficina mas cercana a su domicilio');

        $this->addElements(
            array
            (
                $cedula,
                $nombre,
                $primer_apellido,
                $segundo_apellido,
                $fechanacimiento,
                $correo,
                $codigo_telefono,
                $telefono,
                $telefono_movil,
                $tipo_via,
                $descripcion_via,
                $tipo_vivienda,
                $nombre_vivienda,
                $punto_referencia,
                $tipo_nucleo,
                $descripcion_nucleo,
                $estado,
                $ciudad,
                $municipio,
                $zona_postal,
                $codigo_fax,
                $numero_fax,
                $cargo_empresa,
                $tipo_empleado,
                $actividad_economica,
                $tipo_ocupacion,
                $codigo_area_telf_oficina,
                $numero_oficina,
                $codigo_oficina_banco,
                $submitButton
            )
        );

    }

}