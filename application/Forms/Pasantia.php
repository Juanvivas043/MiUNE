<?php 
class Forms_Pasantia extends Zend_Form { 
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() { 
        $this->setMethod('post'); 
        $this->setName('pasantia');

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
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 35)
            ->setAttrib('id', 'nombre');

        $apellido = new Zend_Form_Element_Text('apellido');
        $apellido->setLabel('Apellido:')
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 35);

        $periodo = new Zend_Form_Element_Text('periodo');
        $periodo->setLabel('Periodo:')
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 3);

        $escuela = new Zend_Form_Element_Select('fk_atributo');
        $escuela->setLabel('Escuela:')
            ->setRequired(false)
            ->addValidator('NotEmpty')
            ->setAttrib('style', 'width: 150px');
        
        $responsable = new Zend_Form_Element_Text('responsable');
        $responsable->setLabel('Responsable:')
            ->setAttrib('size',35);
        
        $departamento = new Zend_Form_Element_Text('departamento');
        $departamento->setLabel('Departamento:')
                ->setAttrib('size', 35);

        $empresa = new Zend_Form_Element_Text('empresa');
        $empresa->setLabel('Empresa:')
            ->setAttrib('size',35);
        
        /*$imprimir = new Zend_Form_Element_Button('imprimir');*/

           
          $this->addElements(array($id,
//            $page,
            $ci,
            $nombre,
            $apellido,
            $periodo,
            $escuela,
            $responsable,
            $departamento,
            $empresa
            /*$imprimir*/
            ));
    }
}