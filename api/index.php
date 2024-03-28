<?php 
include_once 'rest/controller.php';
include_once 'commons/exceptions/controllerExceptions.php';
include_once 'commons/exceptions/repositoryExceptions.php';
include_once 'commons/exceptions/serviceExceptions.php';
include_once 'commons/response.php';
include_once 'commons/requests.php';
include_once 'commons/middlewares/json_middlewares.php';

class generalController {
    function dispatch (Request $req,Response $res): void {
        $res->setMessage("Bienvenue sur notre plateforme de reservation d'appartement");
    }
}

function router(Request $req, Response $res): void {
    $controller = null;
    switch($req->getPathAt(2)) {
        
        case(null):
            throw new NotFoundException("Ce point d'entrée n'existe pas !");

        case 'register':
            $controller = new Controller();
            break;
        
        case 'login':
            $controller = new Controller();
            break;

        case 'apartments':
            $controller = new Controller();
            break;
    
        case "apartment":
            $controller = new Controller();
            break; 

        case "booking":
            $controller = new Controller();
            break;

        case "user" :
            $controller = new Controller();
            break;

        case "bookings" :
            $controller = new Controller();
            break;

        case "apartbookings" :
            $controller = new Controller();
            break;
            
        default:
            throw new NotFoundException("Ce point d'entrée n'existe pas !");
    }
        $controller->dispatch($req, $res);
}

$res = new Response();
$req = new Request();

try {
    json_middleware($req, $res);

    router($req, $res);  
} catch (ControllerTokenNotFound | BDDNotFoundException | ControllerMissingArgument | NotFoundException $e) {
    $res->setMessage($e->getMessage(), 404);
} catch (ControllerUnvalidToken | ControllerUnvalidRights | ServiceUnvalidLogin | ServiceNotAvailable | InvalidArgumentException | ServiceNotOwner $e){
    $res->setMessage($e->getMessage(), 401);
} catch (ServiceExistingAccount $e) {
    $res->setMessage($e->getMessage(), 409);
} catch (ServiceUnvalidDatas | BadRequestException $e) {
    $res->setMessage($e->getMessage(), 400);
}catch (Exception $e) {
    $res->setMessage("An error occured with the server.", 500);
} 



$res->send();

?>