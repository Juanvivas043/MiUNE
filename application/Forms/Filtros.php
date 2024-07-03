<?php 
class Forms_Filtros extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {
        $this->setMethod('post');
        $this->setName('estudiante');
		$this->setOptions(array('escape' => true));
		
		$changeCI = "$.getJSON(urlAjax + 'exists/data/' + escape($('#estudiante').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});";
//		$changeCI = addslashes($changeCI);


        $id   = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');
        //$page = new Zend_Form_Element_Hidden('page');

		$foto = new Zend_Form_Element_Image('foto');
		$foto->setLabel('Foto:')
		     ->setAttrib('width', 120)
		     ->setAttrib('height', 161)
			 ->setAttrib('src', '../images/empty_profile.jpg')
			 ->setAttrib('disabled', 'disabled');

        $ci = new Zend_Form_Element_Text('pk_usuario');
        $ci->setLabel('C.I.:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('Digits')
            ->addValidator('StringLength', true, array(4, 8))
            ->setAttrib('size', 9)
            ->setAttrib('maxlength', 8)
            ->setAttrib('onchange', $changeCI);

        $nacionalidad = new Zend_Form_Element_Radio('nacionalidad');
        $nacionalidad->setLabel('Nacionalidad:')
            ->setRequired(true)
            ->addMultiOptions(array('t' => ' Venezolano',
                                    'f' => ' Extranjero'))
            ->addErrorMessage('Escoja una opción.');

        $genero = new Zend_Form_Element_Radio('sexo');
        $genero->setLabel('Genero:')
            ->setRequired(true)
            ->addMultiOptions(array('t' => ' Femenino',
                                    'f' => ' Masculino'))
            ->addErrorMessage('Escoja una opción.');

        $nombre = new Zend_Form_Element_Text('nombre');
        $nombre->setLabel('Nombre:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array(2, 45))
            ->setAttrib('size', 45)
            ->setAttrib('maxlength', 45);

        $apellido = new Zend_Form_Element_Text('apellido');
        $apellido->setLabel('Apellido:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array(2, 45))
            ->setAttrib('size', 45)
            ->setAttrib('maxlength', 45);

        $direccion = new Zend_Form_Element_Textarea('direccion');
        $direccion->setLabel('Dirección:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('rows', 4)
            ->setAttrib('cols', 39);

        $correo = new Zend_Form_Element_Text('correo');
        $correo->setLabel('e-mail:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', true, array(11, 255))
            ->setAttrib('size', 45)
            ->setAttrib('maxlength', 255);

        $fecha_nacimiento = new Zend_Form_Element_Text('fechanacimiento');
        $fecha_nacimiento->setLabel('Fecha de nacimiento:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
			->addValidator('NotEmpty')
            ->addValidator('Date', true, array('dd/MM/YYYY'))
            ->addValidator('StringLength', true, array( 'min' => 10, 'max' => 10 ) )
            ->setAttrib('size', 11);

        $telefono = new Zend_Form_Element_Text('telefono');
        $telefono->setLabel('Teléfono:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', true, array(15, 15))
            ->setAttrib('size', 16)
            ->setAttrib('maxlength', 15);

        $this->addElements(array($id,
            //$page,
			$foto,
            $ci,
            $nacionalidad,
            $genero,
            $nombre,
            $apellido,
            $direccion,
            $correo,
            $fecha_nacimiento,
            $telefono));
    }
}