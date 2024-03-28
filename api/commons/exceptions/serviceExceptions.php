<?php 

class ServiceUnvalidLogin extends Exception{
    public function __construct($message = "Mot de passe ou email incorrect."){
        parent::__construct($message);
    }
}


class ServiceExistingAccount extends Exception{
    public function __construct($message = "Cet email est déjà utilisé."){
        parent::__construct($message);
    }
}

class ServiceUnvalidDatas extends Exception{
    public function __construct($message = "Les données mises ne sont pas correctes."){
        parent::__construct($message);
    }
}

class ServiceNotAvailable extends Exception{
    public function __construct($message = "Cet appartement n'est pas disponible."){
        parent::__construct($message);
    }
}


class ServiceNotOwner extends Exception{
    public function __construct($message = "Cette réservation ne vous appartient pas, vous ne pouvez pas la supprimer."){
        parent::__construct($message);
    }
}


