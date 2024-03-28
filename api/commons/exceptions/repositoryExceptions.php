<?php 

    class BDDException extends Exception{
            public function __construct($message = "Une erreur est survenue dans la base de données. Veuillez réessayer."){
                parent::__construct($message);
            }
    }

    class BDDNotFoundException extends Exception{
        public function __construct($message = "L'élément recherché n'a pas été trouvé."){
            parent::__construct($message);
        }
    }


