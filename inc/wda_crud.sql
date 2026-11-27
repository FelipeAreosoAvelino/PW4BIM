SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET NAMES utf8mb4;

DROP DATABASE IF EXISTS wda_crud;
CREATE DATABASE wda_crud CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wda_crud;

CREATE TABLE IF NOT EXISTS enfermeiros (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nome varchar(255) NOT NULL,
  coren varchar(7) NOT NULL,
  cep varchar(9) NOT NULL,
  phone varchar(20) NOT NULL,
  foto varchar(255) NOT NULL DEFAULT '',
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
  foto varchar(255) NOT NULL DEFAULT '',
  created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS usuarios (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nome varchar(255) NOT NULL,
  user varchar(50) NOT NULL,
  password varchar(255) NOT NULL,
  foto varchar(255) NOT NULL DEFAULT '',
  nivel varchar(20) NOT NULL DEFAULT 'user',
  created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_usuarios_user (user)
);

INSERT INTO enfermeiros (nome, coren, cep, phone, foto, birthdate)
VALUES ('Emerson Fagundes', '000032', '12345680', '15998760925', '', '2000-10-10');

INSERT INTO customers (nome, birthdate, cep, phone, foto)
VALUES ('Emerson Fagundes', '2000-10-10', '12345680', '15998760925', '');

-- Senha: password123  (hash gerado com password_hash)
INSERT INTO usuarios (nome, user, password, foto, nivel)
VALUES ('John Doe', 'johndoe', '$2y$10$4TsdFkSYPBqXFpka07cBzOULpL2wewoF57BP/gMdkL412fi2hBC5.', '', 'admin');