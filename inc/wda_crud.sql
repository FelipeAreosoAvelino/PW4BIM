SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

SET NAMES utf8;

CREATE DATABASE wda_crud;
USE wda_crud;

CREATE TABLE IF NOT EXISTS enfermeiros (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name varchar(255) NOT NULL,
  coren int(7) NOT NULL,
  cep varchar(9) NOT NULL,
  phone int(20) NOT NULL,
  foto varchar(255) not null
);

CREATE TABLE IF NOT EXISTS customers (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name varchar(255) NOT NULL,
  birthdate date NOT NULL,
  cep varchar(9) NOT NULL,
  phone int(20) NOT NULL,
  foto varchar(255) not null
);


Create table if not exists usuarios (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nome varchar(255) NOT NULL,
  user varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  foto varchar(255) not null
);

INSERT INTO `enfermeiros` (`name`, `coren`, `cep`, `phone`) 
VALUES ('Emerson Fagundes', 032, 12345680, 15998760925);

insert into 'usuarios' ('nome', 'user', 'password', 'foto') 
values ('John Doe', 'johndoe', 'password123', 'johndoe.jpg');