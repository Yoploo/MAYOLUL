<?php 


 class ControllerMissingArgument extends Exception{
        public function __construct($message = "Veuillez passer un argument."){
            parent::__construct($message);
        }
    }
    
    class HTTPException extends Exception {

        public function __construct($message = "An error occured.", $code = 500) {
            parent::__construct($message, $code);
        }
    }
    
    
    class NotFoundException extends HTTPException {
        public function __construct($message = "Not Found") {
            parent::__construct(message: $message, code: 404);
        }
    }
    
    class BadRequestException extends HTTPException {
        public function __construct($message = "Bad Request") {
            parent::__construct(message: $message, code: 400);
        }
    }

    class ControllerTokenNotFound extends Exception{
        public function __construct($message = "Veuillez vous connecter."){
            parent::__construct($message);
        }
    }
    
    class ControllerUnvalidToken extends Exception{
        public function __construct($message = "Token invalide, veuillez vous reconnecter"){
            parent::__construct($message);
        }
    }
    
    class ControllerUnvalidRights extends Exception{
        public function __construct($message = "Vous n'avez pas les droits nécéssaires pour effectuer cette action."){
            parent::__construct($message);
        }
    }