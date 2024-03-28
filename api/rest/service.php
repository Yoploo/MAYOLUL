<?php
include_once 'repository.php';
include_once 'model.php';
include_once './commons/middlewares/json_middlewares.php';
include_once './commons/exceptions/serviceExceptions.php';

class Service
{
    private $repository;

    function __construct()
    {
        $this->repository = new Repository();
    }

    /* GESTION DONNÉES */

    //vérifie que l'utitlisateur ne rentre pas n'importe quoi comme $key
    public function isInArray($array, $array2)
    {
        foreach ($array2 as $key => $element) {
            if (!in_array($key, $array)) {
                throw new ServiceUnvalidDatas("Les éléments que vous essayez de mettre ne sont pas valides");
            }
        }
    }

    //vérifie que l'utilisateur rentre un type de compte valable
    public function validAccountType($accountType)
    {
        $array = ["i", "p", "c"];

        if (!in_array($accountType, $array))
            throw new ServiceUnvalidDatas("Vous devez mettre un type de compte tel que c (client), p (propriétaire)");
    }

    //vérifie que l'utilisateur ne met pas une valeur vide
    public function isEmpty($array)
    {
        foreach ($array as $key => $element) {
            if (empty($element))
                throw new ServiceUnvalidDatas("Veuillez remplir tous les champs.");
        }
    }

    public function validDispoType($disp)
    {
        $array = ["t", "f"];

        if (!in_array($disp, $array))
            throw new ServiceUnvalidDatas("Vous devez mettre une disponibilité tel que t (true), f (false)");
    }

    /* GESTION DE L'USER */
    function register(stdClass $user)
    {
        $array = ["email", "password", "accountType"];
        $this->isInArray($array, $user);
        $this->isEmpty($user);

        if(!isset($user->email))
            throw new ServiceUnvalidDatas("Vous devez nous passer un email !");
        if(!isset($user->password))
            throw new ServiceUnvalidDatas("Vous devez nous passer un mot de passe !");

        $exists = $this->repository->verifyUser($user->email);

        //vérifie si l'email n'existe pas deja
        if ($exists)
            throw new ServiceExistingAccount();

        if ($user->accountType == 'i' && $user->email != "admin@gmail.com")
            throw new ControllerUnvalidRights();

        $this->validAccountType($user->accountType);

        //vérifie si le mail mis est correct
        if (!filter_var($user->email, FILTER_VALIDATE_EMAIL))
            throw new ServiceUnvalidDatas("Email invalide. Veuillez mettre une adresse valide.");

        return $this->repository->register($user);
    }


    function login($user)
    {
        $array = ["email", "password"];
        $this->isInArray($array, $user);
        $this->isEmpty($user);

        if(!isset($user->email))
            throw new ServiceUnvalidDatas("Vous devez nous passer un email !");
        if(!isset($user->password))
            throw new ServiceUnvalidDatas("Vous devez nous passer un mot de passe !");

        $exists = $this->repository->verifyUser($user->email);

        //vérifie si l'email existe, s'il n'existe pas -> erreur
        if (!$exists)
            throw new ServiceUnvalidLogin();

        $password = $this->repository->checkPassword($user->email, $user->password);

        //vérifier si le mot de passe est le bon
        if (!$password)
            throw new ServiceUnvalidLogin();

        return $this->repository->login($user);
    }

    function deleteUser($userId)
    {
        $ownerId = $this->repository->getOwnerId();
        $token = new tokenManagement();
        if (!$token->checkAccountValidity("i") && $userId != $ownerId) {
            throw new ServiceNotOwner("Ce compte ne vous appartient pas vous ne pouvez pas le supprimer !");
        }
        return $this->repository->deleteUser($userId);
    }

    function getUsers()
    {
        return $this->repository->getUsers();
    }

    function patchUser($user, $userId)
    {
        $array = ["email", "password", "accountType"];
        $this->isInArray($array, $user);
        $this->isEmpty($user);
        if (isset($user->accountType))
            $this->validAccountType($user->accountType);

        $ownerId = $this->repository->getownerId();
        $token = new tokenManagement();

        if (!$token->checkAccountValidity("i") && $userId != $ownerId)
            throw new ServiceNotOwner("Ce compte ne vous appartient pas vous ne pouvez pas modifier ses données");

        if (isset($user->email) && $user->email != NULL) {
            $exists = $this->repository->verifyUser($user->email);
            if (!filter_var($user->email, FILTER_VALIDATE_EMAIL))
                throw new ServiceUnvalidDatas("Email invalide. Veuillez mettre une adresse valide.");

            if ($exists)
                throw new ServiceExistingAccount();
        }

        if ($user->accountType == 'i' && !$token->checkAccountValidity("i"))
            throw new ControllerUnvalidRights("Vous ne pouvez pas vous mettre en tant qu'interne.");


        return $this->repository->patchUser($user, $userId);
    }


    /* GESTION DES APPARTEMENTS */
    function addApart(stdClass $apartment)
    {
        $array = ["name", "description", "area", "capacity", "address", "price"];
        $this->isInArray($array, $apartment);
        $this->isEmpty($apartment);
        if(!isset($apartment->name))
            throw new ServiceUnvalidDatas("Vous devez nous passer un nom !");
        if(!isset($apartment->description))
            throw new ServiceUnvalidDatas("Vous devez nous passer une description !");
        if(!isset($apartment->area))
            throw new ServiceUnvalidDatas("Vous devez nous passer une surface");
        if(!isset($apartment->capacity))
            throw new ServiceUnvalidDatas("Vous devez nous passer une capacité");
        if(!isset($apartment->address))
            throw new ServiceUnvalidDatas("Vous devez nous passer une adresse");
        if(!isset($apartment->price))
            throw new ServiceUnvalidDatas("Vous devez nous passer un prix");
        
        return $this->repository->addApart($apartment);
    }


    function deleteApart($apartmentId)
    {
        return $this->repository->deleteApart($apartmentId);
    }


    function patchApart(stdClass $apartment, $apartmentId)
    {
        $array = ["name", "description", "area", "disponibility", "capacity", "address", "price"];
        $this->isInArray($array, $apartment);
        $this->isEmpty($apartment);

        if (isset($apartment->disponibility))
            $this->validDispoType($apartment->disponibility);

        if ($apartment == NULL) {
            throw new ServiceUnvalidDatas("il faut envoyer quelque chose à modifier !");
        }

        return $this->repository->patchApart($apartment, $apartmentId);
    }

    function getAparts()
    {
        return $this->repository->getAparts();
    }


    function getApartById($apartmentId)
    {
        return $this->repository->getApartById($apartmentId);
    }

    public function getApartBySort($sort, $value)
    {
        $allowedColumns = ['price', 'capacity', 'area'];
        if (!in_array($sort, $allowedColumns))
            throw new InvalidArgumentException("Vous ne pouvez trier que par prix (price), capacité (capacity) ou espace (area)");

        return $this->repository->GetApartBySort($sort, $value);
    }

    public function getApartByCriteria($criteria)
    {
        $allowedColumns = ['price', 'capacity', 'area'];

        if (!in_array($criteria, $allowedColumns))
            throw new InvalidArgumentException("Vous ne pouvez trier que par prix (price), capacité (capacity) ou espace (area)");

        return $this->repository->getApartByCriteria($criteria);
    }

    public function modifyDisponibility($apartmentId, $disp)
    {
        $array = ["disponibility"];
        $this->isInArray($array, $disp);
        $this->isEmpty($disp);
        $this->validDispoType($disp->disponibility);

        $ownerId = $this->repository->getownerId();
        $token = new tokenManagement();
        if (!$this->repository->checkOwnerId($apartmentId, $ownerId) && !$token->checkAccountValidity('i'))
            throw new ServiceNotOwner("Vous ne pouvez pas modifier la disponibilité.");

        return $this->repository->modifyDisponibility($apartmentId, $disp->disponibility);
    }


    /* GESTION DES RÉSERVATIONS */

    public function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    public function addBooking($booking, $apartmentId)
    {
        if(!isset($booking->startAt))
            throw new ServiceUnvalidDatas("Vous devez nous passer une date de début !");
        if(!isset($booking->endAt))
            throw new ServiceUnvalidDatas("Vous devez nous passer une date de fin !");

        $array = ["startAt", "endAt"];
        $this->isInArray($array, $booking);
        $this->isEmpty($booking);
        $id = $this->repository->getOwnerId();

        if ($this->repository->checkDisponibility($apartmentId) == "f")
            throw new ServiceNotAvailable();

        if (!$this->validateDate($booking->startAt) || !$this->validateDate($booking->endAt))
            throw new ServiceUnvalidDatas("Veuillez rentrer les dates sous le format yyyy-mm-dd");

        if ($booking->startAt > $booking->endAt)
            throw new ServiceUnvalidDatas("Votre date de début ne peux pas être après votre date de fin !");

        $result = $this->repository->getBookingByDate($booking->startAt, $booking->endAt, $apartmentId, $id);
        if ($result)
            throw new ServiceNotAvailable("Ces dates ne sont pas disponibles !");

        return $this->repository->addBooking($booking, $apartmentId);
    }


    public function deleteBooking($bookingId)
    {
        $token = new tokenManagement();
        $ownerId = $this->repository->getownerId();

        if (!$this->repository->checkOwnerBookingId($bookingId, $ownerId) && !$token->checkAccountValidity('i') && !$this->repository->checkOwnerId($this->repository->getApartIdFromBooking($bookingId), $ownerId)) {
            throw new ServiceNotOwner();
        }

        $this->repository->modifyDisponibility($this->repository->getApartIdFromBooking($bookingId), "t");

        return $this->repository->deleteBooking($bookingId);
    }


    public function getBookingById($bookingId)
    {
        $token = new tokenManagement();
        $ownerId = $this->repository->getownerId();

        if (!$this->repository->checkOwnerBookingId($bookingId, $ownerId) && !$token->checkAccountValidity('i') && !$this->repository->checkOwnerId($this->repository->getApartIdFromBooking($bookingId), $ownerId)) {
            throw new ServiceNotOwner("Cette réservation ne vous appartient pas, vous ne pouvez pas la consulter.");
        }

        return $this->repository->getBookingById($bookingId);
    }


    function getBookings()
    {
        return $this->repository->getBookings();
    }
    function getApartBookings($apartId)
    {
        $token = new tokenManagement();
        $ownerId = $this->repository->getownerId();
        if (!$token->checkAccountValidity("i") && !$this->repository->checkOwnerId($apartId, $ownerId))
            throw new ServiceNotOwner("Cet appartement ne vous appartient pas, vous ne pouvez pas consulter toutes les reservations de celui ci.");
        return $this->repository->getApartBookings($apartId);
    }

    function patchBooking($booking, $bookingId)
    {
        $array = ["startAt", "endAt"];
        $this->isInArray($array, $booking);
        $this->isEmpty($booking);

        $token = new tokenManagement();
        $ownerId = $this->repository->getOwnerId();

        if (!$token->checkAccountValidity("i") && !$this->repository->checkOwnerBookingId($bookingId, $ownerId))
            throw new ServiceNotOwner("Cette réservation ne vous appartient pas.");

        if (isset($booking->startAt) && !$this->validateDate($booking->startAt))
            throw new ServiceUnvalidDatas("Veuillez rentrer les dates sous le format yyyy-mm-dd");

        if (isset($booking->endAt) && !$this->validateDate($booking->endAt))
            throw new ServiceUnvalidDatas("Veuillez rentrer les dates sous le format yyyy-mm-dd");

        if ($booking->startAt > $booking->endAt)
            throw new ServiceUnvalidDatas("Votre date de début ne peux pas être après votre date de fin !");

        $oldbooking = $this->repository->getBookingById($bookingId);

        if (!isset($booking->endAt))
            $result = $this->repository->verifyDateBooking($booking->startAt, $oldbooking->endAt, $oldbooking->apartId, $bookingId);
        if (!isset($booking->startAt))
            $result = $this->repository->verifyDateBooking($oldbooking->startAt, $booking->endAt, $oldbooking->apartId, $bookingId);

        if (isset($booking->endAt) && isset($booking->startAt))
            $result = $this->repository->verifyDateBooking($booking->startAt, $booking->endAt, $oldbooking->apartId, $bookingId);
        if ($result)
            throw new ServiceNotAvailable("Ces dates ne sont pas disponibles !");
        return $this->repository->patchBooking($booking, $bookingId);
    }
}
