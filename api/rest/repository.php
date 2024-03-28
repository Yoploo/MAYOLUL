<?php
include_once 'model.php';
require 'commons/middlewares/jwt/vendor/autoload.php';
include_once './commons/exceptions/repositoryExceptions.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


#[\AllowDynamicProperties]
class Repository
{
    private $connection = null;

    function __construct()
    {
        try {
            $this->connection = pg_connect("host=database port=5432 dbname=rest user=root password=password");
            if ($this->connection === false) {
                throw new BDDException("La connexion à la base de données n'a pas pu se faire. Veuillez réessayer.");
            }
        } catch (Exception $e) {
            throw new BDDException("Connexion impossible à la bdd :" . $e->getMessage());
        }
    }

    /* GESTION DE L'USER */


    //Création de token JWT
    public function token($user)
    {
        $hashed = password_hash($user[1], PASSWORD_DEFAULT);
        $date = new dateTime('+2 hours', new DateTimeZone('Europe/Paris'));

        $payload = array(
            'email' => $user[0],
            'password' => $hashed,
            'accountType' => $user[2],
            'exp' => $date->format('Y-m-d H:i:s')
        );

        return JWT::encode($payload, '5ldj1a', 'HS256');
    }

    //Décode le token
    public function decodeToken($token)
    {
        return JWT::decode($token, new Key('5ldj1a', 'HS256'));
    }

    //Prend la date et heure à l'instant
    public function getDate()
    {
        $date = new dateTime("", new DateTimeZone('Europe/Paris'));
        return $date->format('Y-m-d H:i:s');
    }

    //Création d'un utilisateur
    public function register(stdClass $user)
    {
        pg_prepare($this->connection, "register", "INSERT INTO \"user\"(email, password, token, accountType) VALUES ($1, $2, $3, $4)");

        $hashed = password_hash($user->password, PASSWORD_DEFAULT);

        $datas = array($user->email, $hashed, $user->accountType);
        $encode = $this->token($datas);

        pg_execute($this->connection, "register", [$user->email, $hashed, $encode, $user->accountType]);

        return new userModel($user->email, null, $user->accountType, $encode);
    }


    //Vérifie si le mail n'existe pas déjà
    public function verifyUser($email)
    {
        pg_prepare($this->connection, "verifyUser", "SELECT email FROM \"user\" WHERE email = $1");
        $result = pg_execute($this->connection, "verifyUser", [$email]);
        return pg_fetch_assoc($result);
    }

    //Check le mot de passe
    public function checkPassword($email, $password)
    {
        pg_prepare($this->connection, "checkPassword", "SELECT password FROM \"user\" WHERE email = $1");
        $result = pg_execute($this->connection, "checkPassword", [$email]);
        return password_verify($password, pg_fetch_assoc($result)['password']);
    }


    //Login qui renvoie le token (et le refresh si expiré)

    public function login(stdClass $user)
    {
        pg_prepare($this->connection, "login", "SELECT id, token, password, accountType FROM \"user\" WHERE email = $1");

        $result = pg_execute($this->connection, "login", [$user->email]);

        $row = pg_fetch_assoc($result);

        $token = $row['token'];
        $password = $row['password'];
        $accountType = $row['accounttype'];
        $id = $row['id'];

        $decode = $this->decodeToken($token);

        $date = $this->getDate();

        if ($date > $decode->exp) {
            pg_prepare($this->connection, "changeToken", "UPDATE \"user\" SET token = $1 WHERE email = $2");

            $userDatas = array($user->email, $password, $accountType);

            $encode = $this->token($userDatas);

            $token = $encode;

            pg_execute($this->connection, "changeToken", [$encode, $user->email]);
        }

        $model = new userModel($id, null, null, $token);
        $model->id = $id;

        return $model;
    }

    function deleteUser($userId){
        pg_prepare($this->connection, "", "SELECT id FROM \"user\" WHERE id=$1");
        $result = pg_execute($this->connection, "", [$userId]);

        if (pg_num_rows($result) === 0)
            throw new BDDNotFoundException("cet utilisateur n'existe pas !");

        pg_prepare($this->connection, "DeleteUser", "DELETE FROM \"user\" WHERE id=$1");
        $result = pg_execute($this->connection, "DeleteUser", [$userId]);
     
        return;         
    }

    public function getUsers()
    {
        pg_prepare($this->connection, "GetUsers", "SELECT * FROM \"user\"");
        $result = pg_execute($this->connection, "GetUsers", []);

        if (pg_num_rows($result) === 0)
            throw new BDDNotFoundException("Il n'y a aucun utilisateur.");

        while ($row = pg_fetch_assoc($result)) {
            $user = new userModel($row["email"], NULL, $row["accounttype"], $row["token"]);
            $user->id = $row["id"];
            $Users[] = $user;
        }

        return ($Users);
    }

    public function patchUser($user, $userId)
    {
        #verif si il existe bien
        pg_prepare($this->connection, "verifUser", "SELECT id FROM \"user\" WHERE id=$1");
        $result = pg_execute($this->connection, "verifUser", [$userId]);

        if (pg_num_rows($result) === 0)
            throw new BDDNotFoundException("cet utilisateur n'existe pas !");
        #verif si on change le password

        if (isset($user->password) && $user->password != NULL) {
            $hashed = password_hash($user->password, PASSWORD_DEFAULT);
            $user->password = $hashed;
        }
        #creer le string pour changer que ce qu'on lui met en parametre
        $setStatements = [];
        foreach ($user as $field => $value) {
            if ($value != NULL) {
                $setStatements[] = "$field = '$value'";
            }
        }
        $setstring = implode(', ', $setStatements);
        #change les paramètres
        $query = "UPDATE \"user\" SET $setstring WHERE id = $userId";
        pg_query($this->connection, $query);

        #recupère tout le user meme ce qu'on a pas changer
        pg_prepare($this->connection, "getUser", "SELECT * FROM \"user\" WHERE id=$1");
        $result = pg_execute($this->connection, "getUser", [$userId]);
        $row = pg_fetch_assoc($result);
        $email = $row["email"];
        $password = $row["password"];
        $accountType = $row["accounttype"];

        #creer le nouveau token et le met dans la base de données
        pg_prepare($this->connection, "changeToken", "UPDATE \"user\" SET token = $1 WHERE email = $2");
        $userDatas[] = $email;
        $userDatas[] = $password;
        $userDatas[] = $accountType;
        $encode = $this->token($userDatas);
        $token = $encode;
        pg_execute($this->connection, "changeToken", [$encode, $email]);

        #renvoie le user
        return $token;
    }

    /* GESTION DES APPARTEMENTS */


    public function getOwnerId()
    {
        $token = trim(trim(apache_request_headers()['Authorization'], "Bearer"), " ");
        pg_prepare($this->connection, "", "SELECT id from \"user\" WHERE token=$1");
        $result = pg_execute($this->connection, "", [$token]);

        $row = pg_fetch_assoc($result);

        if (!$row)
            throw new BDDNotFoundException("Cet utilisateur n'existe pas!");

        return $row['id'];
    }

    public function getLastApartmentId()
    {
        pg_prepare($this->connection, "GetLastApartment", "SELECT * FROM \"apartment\" ORDER BY id DESC LIMIT 1");
        $result = pg_execute($this->connection, "GetLastApartment", []);
        $row = pg_fetch_assoc($result);
        $id = $row["id"];
        return $id;
    }

    public function checkOwnerId($apartmentId, $ownerId)
    {
        pg_prepare($this->connection, "CheckownerId", "SELECT ownerid from \"apartment\" WHERE id=$1");
        $result = pg_execute($this->connection, "CheckownerId", [$apartmentId]);

        $row = pg_fetch_assoc($result);

        if (!$row)
            throw new BDDNotFoundException("Cette appartement n'existe plus.");

        $id = $row["ownerid"];
        return $id == $ownerId;
    }

    public function addApart(stdClass $apartment)
    {
        $ownerId = $this->getOwnerId();
        pg_prepare($this->connection, "AddApartment", "INSERT INTO \"apartment\"(name, description, area, capacity,address,disponibility,price,ownerid) VALUES ($1, $2, $3, $4, $5, $6, $7,$8)");
        pg_execute($this->connection, "AddApartment", [$apartment->name, $apartment->description, $apartment->area, $apartment->capacity, $apartment->address, "t", $apartment->price, $ownerId]);
        return $this->getLastApartmentId();
    }


    public function deleteApart($apartmentId)
    {
        pg_prepare($this->connection, "getApartment", "SELECT id FROM \"apartment\" WHERE id=$1");
        $result = pg_execute($this->connection, "getApartment", [$apartmentId]);

        if (pg_num_rows($result) === 0)
            throw new BDDNotFoundException("Cet appartement n'existe plus.");

        pg_prepare($this->connection, "DeleteApartment", "DELETE FROM \"apartment\" WHERE id=$1");
        pg_execute($this->connection, "DeleteApartment", [$apartmentId]);

        return;
    }

    //verifier id de l'user
    public function patchApart($apartment, $apartmentId)
    {
        pg_prepare($this->connection, "verifApartment", "SELECT id FROM \"apartment\" WHERE id=$1");
        $result = pg_execute($this->connection, "verifApartment", [$apartmentId]);

        if (pg_num_rows($result) === 0)
            throw new BDDNotFoundException("cet appartement n'existe pas !");

        $setStatements = [];
        foreach ($apartment as $field => $value) {
            if ($value != NULL) {
                $escapedValue = pg_escape_string($this->connection, $value);
                $setStatements[] = "$field = '$escapedValue'";
            }
        }
        $setstring = implode(', ', $setStatements);

        $query = "UPDATE \"apartment\" SET $setstring WHERE id = $apartmentId";
        pg_query($this->connection, $query);

        return;
    }


    public function getAparts()
    {
        pg_prepare($this->connection, "GetAparts", "SELECT id, name, address, disponibility FROM apartment");
        $result = pg_execute($this->connection, "GetAparts", []);

        if (pg_num_rows($result) === 0)
            throw new BDDNotFoundException("Il n'y a aucun appartement.");

        while ($row = pg_fetch_assoc($result)) {
            $apart = new miniApartmentModel($row["id"], $row["name"], $row["address"], $row["disponibility"]);
            $apartments[] = $apart;
        }

        return ($apartments);
    }


    public function getApartById($apartmentId)
    {
        pg_prepare($this->connection, "GetApartById", "SELECT * FROM \"apartment\" WHERE id  = $1");
        $result = pg_execute($this->connection, "GetApartById", [$apartmentId]);

        if (pg_num_rows($result) === 0)
            throw new BDDNotFoundException("Cet appartement n'existe pas.");


        $row = pg_fetch_assoc($result);

        return new apartmentModel($row["name"], $row["description"], $row["area"], $row["capacity"], $row["address"], $row["disponibility"], $row["price"], $row["ownerid"]);
    }

    public function getApartBySort($sort, $value)
    {

        $query = "SELECT * FROM \"apartment\" WHERE $sort BETWEEN 0 AND $1";
        pg_prepare($this->connection, "getApartBySort", $query);
        $result = pg_execute($this->connection, "getApartBySort", [$value]);

        if (pg_num_rows($result) === 0)
            throw new BDDNotFoundException("Il n'y a pas d'appartement qui remplissent vos conditions !");


        while ($row = pg_fetch_assoc($result)) {
            $sort = "" . $sort . "";
            $apart = new miniApartmentModel($row["id"], $row["name"], $row["address"], $row["disponibility"]);
            $apart->$sort = $row[$sort];
            $apartments[] = $apart;
        }

        return ($apartments);
    }

    public function getApartByCriteria($criteria)
    {
        $query = "SELECT * FROM \"apartment\" ORDER BY ". $criteria. " ASC";
        pg_prepare($this->connection, "", $query);

        $result = pg_execute($this->connection, "", []);

        if (pg_num_rows($result) === 0) 
            throw new BDDNotFoundException("Il n'y a pas d'appartement qui remplissent vos conditions !");

        $apartments = [];
        while ($row = pg_fetch_assoc($result)) {
            $apart = new miniApartmentModel($row["id"], $row["name"], $row["address"], $row["disponibility"]);
            $apart->{$criteria} = $row[$criteria];
            $apartments[] = $apart;
        }

        return $apartments;
    }


    public function modifyDisponibility($apartmentId, $disp)
    {
        pg_prepare($this->connection, "getDispo", "SELECT disponibility FROM \"apartment\" WHERE id = $1");
        $result = pg_execute($this->connection, "getDispo", [$apartmentId]);

        if (pg_num_rows($result) === 0)
            throw new BDDNotFoundException();

        pg_prepare($this->connection, "updateDispo", "UPDATE \"apartment\" SET disponibility = $1 WHERE id = $2");
        $result = pg_execute($this->connection, "updateDispo", [$disp, $apartmentId]);

        return;
    }



    /* GESTION DES RÉSERVATIONS */

    public function checkOwnerBookingId($bookingId, $ownerid)
    {
        pg_prepare($this->connection, "CheckOwnerBookingId", "SELECT idUser from \"booking\" WHERE id=$1");
        $result = pg_execute($this->connection, "CheckOwnerBookingId", [$bookingId]);


        $row = pg_fetch_assoc($result);

        if (!$row)
            throw new BDDNotFoundException("Cette réservation n'existe plus.");

        $id = $row["iduser"];
        return $id == $ownerid;
    }
    public function getPriceById($apartmentId)
    {
        pg_prepare($this->connection, "GetPriceById", "SELECT price FROM \"apartment\" WHERE id = $1");
        $result = pg_execute($this->connection, "GetPriceById", [$apartmentId]);
        $row = pg_fetch_assoc($result);

        if (!$row)
            throw new Exception();

        $price = $row["price"];

        return $price;
    }

    public function getApartIdFromBooking($bookingId)
    {
        pg_prepare($this->connection, "", "SELECT idApart from \"booking\" WHERE id=$1");
        $result = pg_execute($this->connection, "", [$bookingId]);


        $row = pg_fetch_assoc($result);

        if (!$row)
            throw new BDDNotFoundException("Cette réservation n'existe plus.");

        $id = $row["idapart"];

        return $id;
    }

    public function getLastBookingId()
    {
        pg_prepare($this->connection, "GetLastBookingId", "SELECT id FROM \"booking\" ORDER BY id DESC LIMIT 1");
        $result = pg_execute($this->connection, "GetLastBookingId", []);
        $row = pg_fetch_assoc($result);

        if (!$row)
            throw new Exception();

        $id = $row["id"];

        return $id;
    }

    public function checkDisponibility($apartmentId)
    {
        pg_prepare($this->connection, "", "SELECT disponibility FROM \"apartment\" WHERE id = $1");
        $result = pg_execute($this->connection, "", [$apartmentId]);

        if (pg_num_rows($result) === 0)
            throw new BDDNotFoundException("Cet appartement n'existe pas.");

        $disponibility = pg_fetch_assoc($result)["disponibility"];

        return $disponibility;
    }

    public function getBookingByDate($startAt, $endAt, $apartId)
    {
        pg_prepare($this->connection, "getBookingByDate", "SELECT * FROM booking WHERE idapart = $1 AND
        (startAt BETWEEN $2 AND $3 OR endAt BETWEEN $2 AND $3 OR (startAt <= $2 AND endAt >= $3))");
        $result = pg_execute($this->connection, "getBookingByDate", [$apartId, $startAt, $endAt]);
        return pg_fetch_assoc($result);
    }

    public function verifyDateBooking($startAt, $endAt, $apartId, $bookingId)
    {
        pg_prepare($this->connection, "verifyDateBooking", "SELECT * FROM booking WHERE idapart = $1 AND id != $4 AND
        (startAt BETWEEN $2 AND $3 OR endAt BETWEEN $2 AND $3 OR (startAt <= $2 AND endAt >= $3))");
        $result = pg_execute($this->connection, "verifyDateBooking", [$apartId, $startAt, $endAt, $bookingId]);
        $row = pg_fetch_assoc($result);
        return $row;
    }

    public function addBooking($booking, $apartmentId)
    {
        $price = $this->getPriceById($apartmentId);
        $idUser = $this->getOwnerId();

        $diff = strtotime($booking->endAt) - strtotime($booking->startAt);
        $days = floor($diff / (60 * 60 * 24)) + 1;

        pg_prepare($this->connection, "BookApartment", "INSERT INTO \"booking\"(startat, endat, price, iduser, idapart) VALUES ($1, $2, $3, $4, $5)");
        pg_execute($this->connection, "BookApartment", [$booking->startAt, $booking->endAt, $price * $days, $idUser, $apartmentId]);
        $bookingid = $this->getLastBookingId();

        return $bookingid;
    }

    public function deleteBooking($bookingId)
    {
        pg_prepare($this->connection, "DeleteBooking", "DELETE FROM \"booking\" WHERE id=$1");
        pg_execute($this->connection, "DeleteBooking", [$bookingId]);

        return;
    }

    public function getBookingById($bookingId)
    {
        pg_prepare($this->connection, "", "SELECT * FROM \"booking\" WHERE id  = $1");
        $result = pg_execute($this->connection, "", [$bookingId]);

        $row = pg_fetch_assoc($result);

        if (!$row)
            throw new BDDNotFoundException("Cette réservation n'existe pas !");

        return new bookingModel($row["startat"], $row["endat"], $row["price"], $row["idapart"]);
    }


    public function getBookings()
    {
        pg_prepare($this->connection, "GetBookings", "SELECT * FROM \"booking\"");
        $result = pg_execute($this->connection, "GetBookings", []);

        if (pg_num_rows($result) === 0)
            throw new BDDNotFoundException("Il n'y a aucune réservation.");

        while ($row = pg_fetch_assoc($result)) {
            $booking = new bookingModel($row["startat"], $row["endat"], $row["price"], $row["idapart"]);
            $booking->id = $row["id"];
            $Users[] = $booking;
        }

        return ($Users);
    }

    public function getApartBookings($apartId)
    {
        pg_prepare($this->connection, "GetBookings", "SELECT * FROM \"booking\" WHERE idapart =$1");
        $result = pg_execute($this->connection, "GetBookings", [$apartId]);
        if (pg_num_rows($result) === 0)
            throw new BDDNotFoundException("Il n'y a aucune réservation.");
        while ($row = pg_fetch_assoc($result)) {

            $booking = new bookingModel($row["startat"], $row["endat"], $row["price"], $row["idapart"]);
            $booking->id = $row["id"];
            $Users[] = $booking;
        }

        return ($Users);
    }


    function patchBooking($booking, $bookingId)
    {
        pg_prepare($this->connection, "verifBooking", "SELECT id, idapart FROM \"booking\" WHERE id=$1");
        $result = pg_execute($this->connection, "verifBooking", [$bookingId]);
        
        if (pg_num_rows($result) === 0)
            throw new BDDNotFoundException("Cette réservation n'existe pas!");
    
        $setStatements = [];
        foreach ($booking as $field => $value) {
            if ($value !== NULL) {
                $setStatements[] = "$field = '$value'";
            }
        }
        $setString = implode(', ', $setStatements);
    
        $query = "UPDATE \"booking\" SET $setString WHERE id = $bookingId";
        pg_query($this->connection, $query);
    
        $idapart = pg_fetch_assoc($result)['idapart'];
    
        $query2 = "SELECT price FROM \"apartment\" WHERE id = $idapart";
        $result2 = pg_query($this->connection, $query2);
        
        if ($result2) {
            $price = pg_fetch_assoc($result2)['price'];
            
            $diff = strtotime($booking->endAt) - strtotime($booking->startAt);
            $days = floor($diff / (60 * 60 * 24)) + 1;
    
            $query3 = "UPDATE \"booking\" SET price = ($days * $price) WHERE id = $bookingId";
            pg_query($this->connection, $query3);
        }
    
        return $this->getBookingById($bookingId);
    }
    
    
    
}
