CREATE TABLE "user"
(
    id SERIAL NOT NULL,
    email VARCHAR(255),
    password VARCHAR(255),
    token TEXT,
    accountType VARCHAR(1),
    PRIMARY KEY (id) 
    
);

CREATE TABLE apartment
(
    id SERIAL NOT NULL,
    name VARCHAR(30),
    description VARCHAR(255),
    area INT,
    capacity INT,
    address VARCHAR(100),
    disponibility BOOLEAN,
    price INT,
    ownerId INT,
    FOREIGN KEY (ownerId) REFERENCES "user"(id) ON DELETE CASCADE,
    PRIMARY KEY(id)
);

CREATE TABLE booking
(
    id SERIAL NOT NULL,
    startAt DATE,
    endAt DATE,
    price INT,
    idUser INT,
    idApart INT,
    FOREIGN KEY (idUser) REFERENCES "user"(id) ON DELETE CASCADE,
    FOREIGN KEY (idApart) REFERENCES "apartment"(id) ON DELETE CASCADE,
    PRIMARY KEY (id)
);
