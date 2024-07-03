<?php 
class Forms_Mensaje extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {
        $this->setMethod('post');
        $this->setName('Mensaje');
        $this->setAction("");
        $this->setAttrib('enctype', 'multipart/form-data');

        $id     = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');
                
        $fk_tipo     = new Zend_Form_Element_Hidden('fk_tipo');
		$fk_tipo->removeDecorator('label')
           ->removeDecorator('HtmlTag');
		
        $filtro = new Zend_Form_Element_Hidden('filtro');
		$filtro->removeDecorator('label')
               ->removeDecorator('HtmlTag');
		
        $page   = new Zend_Form_Element_Hidden('page');
		$page->removeDecorator('label')
             ->removeDecorator('HtmlTag');
                
//        $fk_tipo_alt   = new Zend_Form_Element_Hidden('fk_tipo_alt');
//		$page->removeDecorator('label')
//             ->removeDecorator('HtmlTag');
        $titulo = new Zend_Form_Element_Textarea('titulo');
        $titulo->setLabel('Titulo:')
                    ->setRequired(true)
                    //->addFilter('StripTags')
                    ->addFilter('StringTrim')
                    ->setAttrib('rows', 1)
                    ->setAttrib('maxlength', 140)
                    ->setAttrib('cols', 45);

        $adjuntos = new Zend_Form_Element_Hidden('adjuntos');
        
        $adjuntos->setLabel('Adjunto:')
                ->setAttrib('style', 'height :50px;')
                ->removeDecorator('HtmlTag');;
        
        $this->addElement(
            'hidden',
            'dummy',
            array(
                'required' => false,
                'ignore' => true,
                'autoInsertNotEmptyValidator' => false,
                'decorators' => array(
                    array(
                        'HtmlTag', array(
                            'tag'  => 'div',
                            'id'   => 'file-uploader'
                        )
                    )
                )
            )
        );
        $this->dummy->clearValidators();
        
        $usuario = new Zend_Form_Element_Textarea('usuarios');
        $usuario->setLabel('Usuarios:')
                ->setAttrib('rows', 1)
                ->setAttrib('maxlength', 140)
                ->setAttrib('cols', 45);
//                ->setAttrib('disabled', 'disabled')
//                ->setAttrib('style', 'width :300px;');
        $grupos = new Zend_Form_Element_Textarea('grupos');
        $grupos->setLabel('Listas:')
                ->setAttrib('rows', 1)
                ->setAttrib('maxlength', 140)
                ->setAttrib('cols', 45);
        
        $asignaciones = new Zend_Form_Element_Textarea('asignaciones');
        $asignaciones->setLabel('Cursos:')
                ->setAttrib('rows', 1)
                ->setAttrib('maxlength', 140)
                ->setAttrib('cols', 45);

        $contenido = new Zend_Form_Element_Textarea('contenido');
        $contenido->setLabel('Contenido:')
                  ->setRequired(false)
                  //->addFilter('StringTrim')
                  ->setAttrib('rows', 7)
                  ->setAttrib('cols', 45);
        
        $enviar = new Zend_Form_Element_Radio('enviar');
        $enviar->setLabel('Enviar:')
            ->setRequired(true)
            ->addMultiOptions(array('t' => ' Si',
                                    'f' => ' No'))
            ->addErrorMessage('Debe escoger el estatus. ')
            ->setValue('t');

//        $tipo = new Zend_Form_Element_Select('fk_tipo');
//        $tipo->setLabel('T. Recurso:')
//           ->setAttrib('style', 'width: 250px');
//        
//        $publico = new Zend_Form_Element_Radio('publico');
//        $publico->setLabel('Público:')
//            ->setRequired(true)
//            ->addMultiOptions(array('t' => ' Si',
//                                    'f' => ' No'))
//            ->addErrorMessage('Debe escoger el estatus. ');
        $asignacion = new Zend_Form_Element_Hidden('asignacion');
        		$asignacion->removeDecorator('label')
           ->removeDecorator('HtmlTag');
        $dia = new Zend_Form_Element_Hidden('dia');
        		$dia->removeDecorator('label')
           ->removeDecorator('HtmlTag');
        $fecha1 = new Zend_Form_Element_Hidden('fecha1');
        		$fecha1->removeDecorator('label')
           ->removeDecorator('HtmlTag');
        $fecha2 = new Zend_Form_Element_Hidden('fecha2');
        		$fecha2->removeDecorator('label')
           ->removeDecorator('HtmlTag');
        $fk_mensaje = new Zend_Form_Element_Hidden('fk_mensaje');
        		$fk_mensaje->removeDecorator('label')
           ->removeDecorator('HtmlTag');
        $pk_mensaje = new Zend_Form_Element_Hidden('pk_mensaje');
        		$pk_mensaje->removeDecorator('label')
           ->removeDecorator('HtmlTag');
        $reenviar = new Zend_Form_Element_Hidden('reenviar');
        		$reenviar->removeDecorator('label')
           ->removeDecorator('HtmlTag');

        $this->addElements(array($id,
            $fk_tipo,
            $filtro,
            $page,
            $titulo,
            $adjuntos,
//            $destino,
            $usuario,
            $grupos,
            $asignaciones,
//            $descripcion,
            $contenido,
            $enviar,
            $asignacion,
            $dia,
            $fecha1,
            $fecha2,
            $fk_mensaje,
            $pk_mensaje,
            $reenviar
//            $publico,
//            $fk_tipo_alt
        ));
        
//        $this->addElement(
//            'hidden',
//            'dummy',
//            array(
//                'required' => false,
//                'ignore' => true,
//                'autoInsertNotEmptyValidator' => false,
//                'decorators' => array(
//                    array(
//                        'HtmlTag', array(
//                            'tag'  => 'div',
//                            'id'   => 'file-uploader'
//                        )
//                    )
//                )
//            )
//        );
//        $this->dummy->clearValidators();
       

    }

    
}
