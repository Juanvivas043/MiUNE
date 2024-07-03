<?php 

class Une_Helpers_ModalMessage {

	public function __construct() {

        $this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Jquery = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Uri = new SwapBytes_Uri();
        $this->SwapBytes_Html = new SwapBytes_Html();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
    }

    /**
    * @param $mensaje string set the string of the messsage
    * @param $timer integer that set the duration of the message
    **/
    public function quickAlert($mensaje, $timer){
    	return "$('#aviso').text('$mensaje').fadeIn().delay($timer).fadeOut()";
    }

}