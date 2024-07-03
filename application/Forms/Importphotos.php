<?php
class Forms_Importphotos extends Zend_Form {
    public function init() {
        $this->setName('importphotos');
		$this->setAction('');
        $this->setAttrib('enctype', 'multipart/form-data');

        $file = new Zend_Form_Element_File('file');
        $file->setLabel('Archivo')
             ->setRequired(true)
			 ->addValidator('Extension', false, 'zip')
			 ->addValidator('Count', false, 1)
			 ->addValidator('Size', false, 209715200) // 200Mb.
			 ->setMaxFileSize(209715200)
             ->addValidator('NotEmpty')
		     ->removeDecorator('Label')
		     ->removeDecorator('DtDdWrapper')
			 ->removeDecorator('HtmlTag');

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Importar')
               ->setAttrib('id', 'submitbutton')
			   ->removeDecorator('DtDdWrapper')
			   ->removeDecorator('HtmlTag');

        $this->addElements(array($file, $submit));
    }
}
?>
