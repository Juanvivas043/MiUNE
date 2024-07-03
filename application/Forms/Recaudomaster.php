<?php 

class Forms_Recaudomaster extends Zend_Form {

    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {
        $this->setMethod('post');
        $this->setName('Recaudos');
        $this->setAction("");
        $this->setAttrib('enctype', 'multipart/form-data');

        $id = new Zend_Form_Element_Hidden('id');
        $id->removeDecorator('Label')
                ->removeDecorator('DtDdWrapper')
                ->removeDecorator('HtmlTag');

//        $filtro = new Zend_Form_Element_Hidden('filtro');
//        $filtro->removeDecorator('Label')
//                ->removeDecorator('DtDdWrapper')
//                ->removeDecorator('HtmlTag');
//
//        $page = new Zend_Form_Element_Hidden('page');
//        $page->removeDecorator('Label')
//                ->removeDecorator('DtDdWrapper')
//                ->removeDecorator('HtmlTag')
//                ->setValue(1);

        $fk_nombre_recaudo_alt = new Zend_Form_Element_Hidden('fk_nombre_recaudo_alt');
        $fk_nombre_recaudo_alt->removeDecorator('Label')
                ->removeDecorator('DtDdWrapper')
                ->removeDecorator('HtmlTag');
        
        $pasante = new Zend_Form_Element_Select('fk_inscripcion');
        $pasante->setLabel('Pasante:')
                ->removeDecorator('DtDdWrapper')
                ->setAttrib('style', 'width: 250px');
        
        $tipo = new Zend_Form_Element_Select('fk_nombre_recaudo');
        $tipo->setLabel('Recaudo:')
                ->removeDecorator('DtDdWrapper')
                ->setAttrib('style', 'width: 250px');

        $upload = new Zend_Form_Element_Hidden('recaudo');
        $upload->removeDecorator('Label')
                ->removeDecorator('DtDdWrapper')
                ->removeDecorator('HtmlTag');
//               ->setDestination('/home/fayala/uploads');
//               ->addValidator('Count', false, 1);
        
//        $download = new Zend_Form_Element_Button('Descarga');
       // $download->removeDecorator('Label');
           
        $this->addElements(array($id,
            $filtro,
            $page,
            $pasante,
            $tipo,
//            $download,
            $upload,
            $fk_tipo_alt
        ));

        $this->addElement(
                'hidden', 'dummy', array(
            'required' => false,
            'ignore' => true,
            'autoInsertNotEmptyValidator' => false,
            'decorators' => array(
                array(
                    'HtmlTag', array(
                        'tag' => 'div',
                        'id' => 'file-uploader'
                    )
                )
            )
                )
        );
        $this->dummy->clearValidators();
    }

}
