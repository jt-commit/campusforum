-- Migração: Adicionar coluna image à tabela posts
-- Data: 23 de janeiro de 2026

USE campus_forum;

-- Adicionar coluna image se ela não existir
ALTER TABLE posts ADD COLUMN image VARCHAR(255) NULL DEFAULT NULL;

-- Confirmar a alteração
SHOW COLUMNS FROM posts;
