<?php
include_once 'service.php';
include_once 'controller.php';
include_once './commons/exceptions/controllerExceptions.php';
include_once './commons/requests.php';
include_once './commons/response.php';
include_once './commons/middlewares/json_middlewares.php';

class Controller
{
    private $service;


    function __construct()
    {
        $this->service = new Service();
    }

    function dispatch(Request $req, Response $res): void
    {

        /* GESTION DE L'USER */
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $req->getPathAt(2) === 'register') {
            $result = $this->service->register($req->getBody());
            $res->setMessage("Votre compte a bien été crée. Voici votre token : " . $result->token);
        } 
        
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $req->getPathAt(2) === 'login') {
            $result = $this->service->login($req->getBody());
            $res->setMessage("Bonjour ! Votre id est : " . $result->id . " et votre token : " . $result->token);
        }
        
        elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $req->getPathAt(2) == 'user') {
            if ($req->getPathAt(3) == NULL)
                throw new ControllerMissingArgument("Veuillez passer votre id en argument (visible lors de la connexion)");
            $userId = $req->getPathAt(3);
            $result = $this->service->deleteUser($userId);
            $link = "http://localhost:8081/index.php/register/";
            $res->setMessage("Votre utilisateur a bien été supprimé.");
            $res->setElement($link, "link");
        } 

        elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH' && $req->getPathAt(2) == 'user') {
            if ($req->getPathAt(3) == NULL)
                return;
            $userid = $req->getPathAt(3);
            $result = $this->service->patchUser($req->getBody(), $userid);
            $link = "http://localhost:8081/index.php/user/" . $userid;
            $res->setMessage("Votre compte a bien été modifié. Voici votre nouveau token " . $result);
            $res->setElement($link, "link");
        } 

        elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $req->getPathAt(2) == 'user') {
            $result = $this->service->getusers();
            $res->setContent($result);
        } 
        

        /* GESTION DES APPARTEMENTS */ 
        
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $req->getPathAt(2) === 'apartments') {
            $result = $this->service->addApart($req->getBody());
            $link = "http://localhost:8081/index.php/apartment/" . $result;
            $res->setMessage("Votre appartement a bien été ajouté !");
            $res->setElement($link, "lien");
        } 
        
        elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $req->getPathAt(2) === 'apartment') {
            if ($req->getPathAt(3) == NULL)
                throw new ControllerMissingArgument();
            $result = $this->service->deleteApart($req->getPathAt(3));
            $link = "http://localhost:8081/index.php/apartments";
            $res->setMessage("L'appartement a bien été supprimé !");
            $res->setElement($link, "lien");
        } 
        
        elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH' && $req->getPathAt(2) === 'apartment' && $req->getPathAt(3) !== "dispo") {
            if ($req->getPathAt(3) == NULL)
                throw new ControllerMissingArgument();
            $result = $this->service->PatchApart($req->getBody(), $req->getPathAt(3));
            $link = "http://localhost:8081/index.php/apartment/" . $req->getPathAt(3);
            $res->setMessage("L'appartement a bien été modifié !");
            $res->setElement($link, "lien");
        } 
        
        elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $req->getPathAt(2) == 'apartments') {
            if($req->getPathAt(3) != NULL && $req->getPathAt(4) != NULL)
                $result = $this->service->getApartBySort($req->getPathAt(3), $req->getPathAt(4));
            elseif($req->getPathAt(3) != NULL && $req->getPathAt(4) == NULL)
                $result = $this->service->getApartByCriteria($req->getPathAt(3));
            else $result = $this->service->GetAparts();

            foreach ($result as $apart) {
                $link = "http://localhost:8081/index.php/apartment/" . $apart->id;
                $apart->link = $link;
            }
            $res->setContent($result);
        }
        
        elseif ($_SERVER['REQUEST_METHOD'] === 'GET' &&  $req->getPathAt(2) == 'apartment') {
            if ($req->getPathAt(3) == NULL)
                throw new ControllerMissingArgument("Veuillez entrer vos éléments à afficher");
            $result = $this->service->GetApartById($req->getPathAt(3));

            $tab = $res->apartmentToArray($result);

            foreach ($tab[1] as &$elements) {
                foreach ($elements as $key => $value) {
                    $link = "http://localhost:8081/index.php/apartments/" . $key . "/" . $value . "";
                    $elements['link'] = $link;
                }
            }

            $link = "http://localhost:8081/index.php/apartment/" . $req->getPathAt(3);
            $res->setContent($tab);
            $res->setElement($link, "link");
        }
        
        elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH' &&  $req->getPathAt(2) == 'apartment' && $req->getPathAt(3) == 'dispo') {
            if ($req->getPathAt(4) == NULL)
                throw new ControllerMissingArgument("Veuillez entrer votre élément a modifier");
            $this->service->modifyDisponibility($req->getPathAt(4), $req->getBody());

            $link = "http://localhost:8081/index.php/apartment/" . $req->getPathAt(4);

            $res->setMessage("La disponibilité de votre appartement a bien été modifiée !");
            $res->setElement($link, "link");
        }
        /* GESTION DES RÉSERVATIONS */ 
        
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $req->getPathAt(2) == 'apartment') {
            if ($req->getPathAt(3) == NULL) {
                throw new ControllerMissingArgument();
            }
            $result = $this->service->addBooking($req->getBody(), $req->getPathAt(3));
            $link = "http://localhost:8081/index.php/booking/" . $result;
            $res->setMessage("Votre réservation a bien été faite.");
            $res->setElement($link, "link");
        } 
        
        elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $req->getPathAt(2) == 'booking') {
            $bookingid = $req->getPathAt(3);
            if ($req->getPathAt(3) == NULL)
                throw new ControllerMissingArgument();
            $result = $this->service->deleteBooking($bookingid);
            $link = "http://localhost:8081/index.php/apartments/";
            $res->setMessage("Votre réservation a bien été supprimée.");
            $res->setElement($link, "link");
        } 
        
        elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $req->getPathAt(2) == 'booking') {
            if ($req->getPathAt(3) == NULL)
                throw new ControllerMissingArgument();
            $bookingid = $req->getPathAt(3);
            $result = $this->service->getBookingById($bookingid);
            $res->setContent($result);

            $link = "http://localhost:8081/index.php/apartment/" . $result->apartId;
            $res->setElement($link, "link");
        } 
    
        
        elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $req->getPathAt(2) == 'bookings') {
            $result = $this->service->getBookings();
            $res->setContent($result);
        } 
        
        elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $req->getPathAt(2) == 'apartbookings') {
            if ($req->getPathAt(3) == NULL)
                throw new ControllerMissingArgument();
            $apartId = $req->getPathAt(3);
            $result = $this->service->getApartBookings($apartId);
            $res->setContent($result);
        } 
        
        elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH' && $req->getPathAt(2) == 'booking') {
            if ($req->getPathAt(3) == NULL)
                throw new ControllerMissingArgument();
            $bookingId = $req->getPathAt(3);
            $result = $this->service->patchBooking($req->getBody(), $bookingId);
            $link = "http://localhost:8081/index.php/booking/" . $bookingId;
            $res->setContent($result);
            $res->setElement($link, "link");
        } 
        
        
        else {
            throw new HTTPException();
        }
    }
}
