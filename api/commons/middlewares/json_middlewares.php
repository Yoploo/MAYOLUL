<?php
include_once 'commons/requests.php';
include_once 'commons/response.php';
include_once 'commons/exceptions/controllerExceptions.php';
require 'jwt/vendor/autoload.php';


class tokenManagement
{
    function isConnected()
    {
        if(empty(trim(trim(apache_request_headers()['Authorization'], "Bearer"), " ")) || trim(trim(apache_request_headers()['Authorization'], "Bearer"), " ") == "undefined") {
            throw new ControllerTokenNotFound();
        }
    }

    function checkTokenValidity()
    {
        $respository = new Repository();
        $date = $respository->getDate();
        $decode = $respository->decodeToken(trim(trim(apache_request_headers()['Authorization'], "Bearer"), " "));

        if ($date > $decode->exp)
            return 0;
        else
            return 1;
    }

    function checkAccountValidity($accountType)
    {
        $respository = new Repository();
        $decode = $respository->decodeToken(trim(trim(apache_request_headers()['Authorization'], "Bearer"), " "));
        
        if ($decode->accountType != $accountType)
            return 0;
        else
            return 1;
    }
}
function json_middleware(&$req, &$res)
{
    $array = ["login", "register", "apartment", "apartments", "booking", "user", "bookings", "apartbookings"];

    if($req->getPathAt(2) == null || !in_array($req->getPathAt(2), $array)){
        return;
    }

    // On vérifie que le content-type de la requête existe
    if (!isset($req->getHeaders()["Content-Type"]) && $req->getMethod() != "DELETE" && $req->getMethod() != "GET") {
        throw new BadRequestException("Le content-type n'est pas défini");
    }

    if ($req->getHeaders()["Content-Type"] != "application/json" && $req->getMethod() != "DELETE" && $req->getMethod() != "GET") {
        throw new BadRequestException("Le type du content-type doit être : \"application/json\"", 400);
    }

    $parsed = json_decode($req->getBody());

    if (!is_object($parsed) && $req->getMethod() != "DELETE" && $req->getMethod() != "GET") {
        throw new BadRequestException("JSON incorrect.", 400);
    }

    $tokenManagement = new tokenManagement();
    //Vérifier les token
    if ($req->getPathAt(2) !== "login" && $req->getPathAt(2) !== "register") {
        $tokenManagement->isConnected();

        $tokenValid = $tokenManagement->checkTokenValidity();
        if(!$tokenValid)
            throw new ControllerUnvalidToken();
    }

    if ($req->getMethod() == "GET") {
        switch ($req->getPathAt(2)) {
            case 'user':
                if(!$tokenManagement->checkAccountValidity('i'))
                    throw new ControllerUnvalidRights();
            case 'bookings':
                if(!$tokenManagement->checkAccountValidity('i'))
                    throw new ControllerUnvalidRights();
            case 'apartbookings':
                if(!$tokenManagement->checkAccountValidity('i') && !$tokenManagement->checkAccountValidity('p'))
                    throw new ControllerUnvalidRights();    
        }
    }
    
    if ($req->getMethod() == "POST") {
        switch ($req->getPathAt(2)) {
            case 'apartments':
                if(!$tokenManagement->checkAccountValidity('p') && !$tokenManagement->checkAccountValidity('i'))
                    throw new ControllerUnvalidRights();
        }
    }

    if($req->getMethod() == "DELETE"){
        switch ($req->getPathAt(2)) {
            case 'apartment':
                if(!$tokenManagement->checkAccountValidity('i'))
                    throw new ControllerUnvalidRights("Si vous êtes propriétaire de cet appartement, veuillez contacter un interne.");
        }
    }  

    if($req->getMethod() == "PATCH"){
        switch ($req->getPathAt(2)) {
            case 'apartment':
                if($req->getPathAt(3) != 'dispo'){
                    if(!$tokenManagement->checkAccountValidity('i'))
                        throw new ControllerUnvalidRights("Si vous êtes propriétaire de cet appartement, veuillez contacter un interne.");
                }

                elseif($req->getPathAt(3) == 'dispo'){
                    if(!$tokenManagement->checkAccountValidity('i') && !$tokenManagement->checkAccountValidity('p'))
                        throw new ControllerUnvalidRights("Vous ne pouvez pas modifier les données.");
                }
        }
    }  


    $req->setBody($parsed);
}
