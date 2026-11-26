SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

SET NAMES utf8;
drop database wda_crud;
CREATE DATABASE wda_crud;
USE wda_crud;

CREATE TABLE IF NOT EXISTS enfermeiros (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nome varchar(255) NOT NULL,
  coren int(7) NOT NULL,
  cep varchar(9) NOT NULL,
  phone varchar(20) NOT NULL,
  foto varchar(255) not null,
  birthdate date NOT NULL,
  created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS customers (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nome varchar(255) NOT NULL,
  birthdate date NOT NULL,
  cep varchar(9) NOT NULL,
  phone varchar(20) NOT NULL,
  foto varchar(255) not null,
  created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


Create table if not exists usuarios (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nome varchar(255) NOT NULL,
  user varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  foto varchar(255) not null,
  nivel varchar(20) not null default 'user',
  created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO `enfermeiros` (`nome`, `coren`, `cep`, `phone`, `foto`) 
VALUES ('Emerson Fagundes', 032, 12345680, 15998760925, 'foto_6b0647237780f8.95613248.png');


INSERT INTO `customers` (`nome`, `birthdate`, `cep`, `phone`, `foto`) 
VALUES ('Emerson Fagundes', '2000-10-10', 12345680, 15998760925, 'foto_6b0647237780f8.95613248.png');


insert into `usuarios` (`nome`, `user`, `password`, `foto`, `nivel`) 
values ('John Doe', 'johndoe', 'password123', 'johndoe.jpg', 'admin');