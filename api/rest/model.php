<?php 

#[\AllowDynamicProperties] //permet l'ajout d'un attribut
class userModel{
    public $email;
    public $password; 
    public $accountType; 
    public $token;
    
    public function __construct($email, $password, $accountType, $token = null){
        $this->email = $email;
        $this->password = $password;
        $this->accountType = $accountType; 
        $this->token = $token;
    }


}

#[\AllowDynamicProperties] //permet l'ajout d'un attribut
class apartmentModel{
    public $name;
    public $description;
    public $area;
    public $capacity;
    public $address;
    public $disponibility;
    public $price;
    public $ownerId;
    

    public function __construct($name,$description,$area,$capacity,$address,$disponibility,$price,$ownerId)
    {
        $this->name = $name;
        $this->description = $description;
        $this->area = $area;
        $this->capacity = $capacity;
        $this->address = $address;
        $this->disponibility = $disponibility;
        $this->price = $price;
        $this->ownerId = $ownerId;
        
    }
}

#[\AllowDynamicProperties] //permet l'ajout d'un attribut
class miniApartmentModel{
    public $id;
    public $name;
    public $address;
    public $disponibility;
    

    public function __construct($id,$name,$address,$disponibility)
    {
        $this->id = $id;
        $this->name = $name;
        $this->address = $address;
        $this->disponibility = $disponibility;
    }
}

#[\AllowDynamicProperties] //permet l'ajout d'un attribut
class bookingModel{
    public $startAt;
    public $endAt;
    public $totalPrice;
    public $apartId;
   

    public function __construct($startAt,$endAt,$totalPrice,$apartId)
    {
        $this->startAt = $startAt;
        $this->endAt = $endAt;
        $this->totalPrice = $totalPrice;
        $this->apartId = $apartId;
     
    }
}



?>