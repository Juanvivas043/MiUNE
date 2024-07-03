<?php 
class Forms_Recordacademico extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {
        $this->setMethod('post');
        $this->setName('recordacademico');

        $id   = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');

//        $page   = new Zend_Form_Element_Hidden('page');
//		$page->removeDecorator('label')
//             ->removeDecorator('HtmlTag');

        $ci = new Zend_Form_Element_Text('pk_usuario');
        $ci->setLabel('C.I.:')
            ->setAttrib('size', 8)
            ->setAttrib('maxlength', 8);

        $nombre = new Zend_Form_Element_Text('nombre');
        $nombre->setLabel('Nombre:')
            ->setAttrib('size', 45)
            ->setAttrib('maxlength', 45)
            ->setAttrib('id', 'nombre');

        $apellido = new Zend_Form_Element_Text('apellido');
        $apellido->setLabel('Apellido:')
            ->setAttrib('size', 45)
            ->setAttrib('maxlength', 45);

        $codigo = new Zend_Form_Element_Text('codigopropietario');
        $codigo->setLabel('Codigo:')
            ->setAttrib('size', 8)
            ->setAttrib('maxlength', 8);

        $materia = new Zend_Form_Element_Text('materia');
        $materia->setLabel('Materia:')
            ->setAttrib('size', 45)
            ->setAttrib('maxlength', 45);

        $calificacion = new Zend_Form_Element_Text('calificacion');
        $calificacion->setLabel('Calificación:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('Digits')
            ->addValidator('Between', true, array(0, 20))
            ->addValidator('StringLength', true, array(1, 2))
            ->setAttrib('size', 2)
            ->setAttrib('maxlength', 2);

        $estado = new Zend_Form_Element_Select('estado');
        $estado->setLabel('Estado:')
            ->setRequired(true)
                
            ->setAttrib('style', 'width: 150px');
         
       $secciones = new Zend_Form_Element_Select('fk_seccion');
        $secciones->setLabel('Sección:')
            ->setRequired(false)
            ->addValidator('NotEmpty')
            ->setAttrib('style', 'width: 150px');
        
           
          $this->addElements(array($id,
//            $page,
            $ci,
            $nombre,
            $apellido,
            $codigo,
            $materia,
            $calificacion,
            $estado,
            $secciones  
            ));
    }
}