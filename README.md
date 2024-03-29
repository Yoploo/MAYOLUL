# Apartment Rental Management


[![License](https://img.shields.io/badge/license-GPLv3-blue)](https://www.gnu.org/licenses/gpl-3.0.en.html)
[![Contributions welcome](https://img.shields.io/badge/contributions-welcome-brightgreen.svg)](CONTRIBUTING.md)
[![Version](https://img.shields.io/badge/version-1.0-blue)](https://github.com/Yoploo/MAYOLUL/milestones)
[![PHP Version](https://img.shields.io/badge/PHP-7.4-blue)](your-php-version-link)





This project is an apartment rental management application designed to simplify the process of managing rental properties.


You can follow the advencement of the project on the [CHANGELOG](CHANGELOG.md).

## Installation

Before getting started, make sure you have Docker installed on your system. You can find installation instructions for Docker [here](https://docs.docker.com/get-docker/).

1. Clone this repository to your machine:

    ```
    git clone https://github.com/Yoploo/MAYOLUL.git
    ```

2. Navigate to the project directory:

    ```
    cd MAYOLUL
    ```

3. Build and start Docker containers using Docker Compose:

    ```
    docker-compose up -d
    ```

These commands will build and start Docker containers for the application and PostgreSQL database.

## Usage

### User Management
- **User Registration:** `POST http://localhost:8081/register`
- **User Login:** `POST http://localhost:8081/login`
- **Delete User:** `DELETE http://localhost:8081/user/{userId}`
- **Modify User:** `PATCH http://localhost:8081/user/{userId}`
- **Get All Users:** `GET http://localhost:8081/user`

### Apartment Management
- **Add Apartment:** `POST http://localhost:8081/apartments`
- **Delete Apartment:** `DELETE http://localhost:8081/apartment/{apartmentId}`
- **Modify Apartment:** `PATCH http://localhost:8081/apartment/{apartmentId}`
- **Get All Apartments:** `GET http://localhost:8081/apartments`
- **Get Apartment by ID:** `GET http://localhost:8081/apartment/{apartmentId}`
- **Modify Apartment Availability:** `PATCH http://localhost:8081/apartment/dispo/{apartmentId}`

### Reservation Management
- **Add Reservation for Apartment:** `POST http://localhost:8081/apartment/{apartmentId}`
- **Delete Reservation:** `DELETE http://localhost:8081/booking/{bookingId}`
- **Get Reservation by ID:** `GET http://localhost:8081/booking/{bookingId}`
- **Get All Reservations:** `GET http://localhost:8081/bookings`
- **Get Reservations for Apartment:** `GET http://localhost:8081/apartbookings/{apartmentId}`
- **Modify Reservation:** `PATCH http://localhost:8081/booking/{bookingId}`

To perform these requests, you can use tools such as [Insomnia](https://insomnia.rest/) or [Postman](https://www.postman.com/). These tools allow you to easily test your API by sending HTTP requests to your endpoints and visualizing the responses. They also offer advanced features such as environment management, request collection export, etc.

You need to use admin@gmail.com to create the admin account.

### Import Requests

You can import the example request file `request-example.json` into Insomnia. This file contains all the requests for your convenience.


## Contributions

Contributions to this project are welcome! If you would like to contribute, please follow the contribution guidelines [Contribution Guidelines](CONTRIBUTING.md)

## License

This project is licensed under the [GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007](https://www.gnu.org/licenses/gpl-3.0.en.html).
