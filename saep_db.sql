CREATE DATABASE bioterio;
USE bioterio;

CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR (255),
    email VARCHAR (255) UNIQUE,
    senha VARCHAR(255)
);

CREATE TABLE ratos (
    idratos INT PRIMARY KEY AUTO_INCREMENT,
    quantidade INT,
    especie VARCHAR (255)
);

CREATE TABLE camundongos (
    idcamun INT PRIMARY KEY AUTO_INCREMENT,
    quantidade INT,
    especie VARCHAR (255)
);

INSERT INTO usuarios (nome, email, senha) 
VALUES ('Natally', 'natally@email.com', SHA2('1234',256)),
('Sandra', 'sandra@email.com', SHA2('1234',256)),
('Rayara', 'rayray@email.com', SHA2('1234',256));

UPDATE usuarios SET senha = '$2y$10$5CRhCVyvllzfNG4bgtMzSum4RYTVPBw.8NLGTOvJOvnPcra7IhbA2' WHERE email = 'natally@email.com';
UPDATE usuarios SET senha = '$2y$10$Sc3rtmSr2DPt9NSechrKxOQuj27I/EzoqbIxlSnP2Bu8GJMzDXHsS' WHERE email = 'sandra@email.com';
UPDATE usuarios SET senha = '$2y$10$MKTyVImFgH/Qyqrnu8AW4.w1bFZVyUzOaoCQZFPdphhJbAL4sGk2i' WHERE email = 'rayray@email.com';

UPDATE usuarios SET senha = LEFT(senha, 60) WHERE LENGTH(senha) = 61;

INSERT INTO ratos (quantidade, especie) 
VALUES ('50','Rato Preto'),
('20', 'Ratazana'),
('30', 'Rato de Esgoto');

INSERT INTO camundongos (quantidade, especie) 
VALUES ('30','Doméstico'),
('40', 'Mus spretus'),
('30', 'Camundongo do Campo');