# Gestion de Locations d'Appartements

Ce projet est une application de gestion des locations d'appartements, conçue pour simplifier le processus de gestion des propriétés locatives.

## Installation

Avant de commencer, assurez-vous d'avoir Docker installé sur votre système. Vous pouvez trouver des instructions d'installation pour Docker [ici](https://docs.docker.com/get-docker/).

1. Clonez ce dépôt sur votre machine :

    ```
    git clone https://github.com/Yoploo/MAYOLUL.git
    ```

2. Accédez au répertoire du projet :

    ```
    cd MAYOLUL
    ```

3. Créez et démarrez les conteneurs Docker à l'aide de Docker Compose :

    ```
    docker-compose up -d
    ```

Ces commandes construiront et démarreront les conteneurs Docker pour l'application et la base de données PostgreSQL.

## Utilisation

### Gestion des utilisateurs
- **Enregistrement d'un utilisateur :** `POST /register`
- **Connexion d'un utilisateur :** `POST /login`
- **Suppression d'un utilisateur :** `DELETE /user/{userId}`
- **Modification d'un utilisateur :** `PATCH /user/{userId}`
- **Récupération de tous les utilisateurs :** `GET /user`

### Gestion des appartements
- **Ajout d'un appartement :** `POST /apartments`
- **Suppression d'un appartement :** `DELETE /apartment/{apartmentId}`
- **Modification d'un appartement :** `PATCH /apartment/{apartmentId}`
- **Récupération de tous les appartements :** `GET /apartments`
- **Récupération d'un appartement par ID :** `GET /apartment/{apartmentId}`
- **Modification de la disponibilité d'un appartement :** `PATCH /apartment/dispo/{apartmentId}`

### Gestion des réservations
- **Ajout d'une réservation pour un appartement :** `POST /apartment/{apartmentId}`
- **Suppression d'une réservation :** `DELETE /booking/{bookingId}`
- **Récupération d'une réservation par ID :** `GET /booking/{bookingId}`
- **Récupération de toutes les réservations :** `GET /bookings`
- **Récupération des réservations pour un appartement :** `GET /apartbookings/{apartmentId}`
- **Modification d'une réservation :** `PATCH /booking/{bookingId}`

Pour effectuer ces requêtes, vous pouvez utiliser des outils tels que [Insomnia](https://insomnia.rest/) ou [Postman](https://www.postman.com/). Ces outils vous permettent de tester facilement votre API en envoyant des requêtes HTTP à vos endpoints et en visualisant les réponses. Ils offrent également des fonctionnalités avancées telles que la gestion des environnements, l'exportation de collections de requêtes, etc.



## Contributions

Les contributions à ce projet sont les bienvenues ! Si vous souhaitez contribuer, veuillez soumettre une pull request avec vos modifications.

## Licence

Ce projet est sous licence [MIT](LICENSE).
