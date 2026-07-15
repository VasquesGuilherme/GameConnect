-- Deleta o banco se já existir e cria um novo
DROP DATABASE IF EXISTS gameconnect;
CREATE DATABASE gameconnect;
USE gameconnect;

-- Tabela de usuários
CREATE TABLE usuarios (
    id INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    foto VARCHAR(255),
    contato VARCHAR(100),
    data_nascimento DATE,
    descricao TEXT,
    plataforma ENUM('PC', 'Xbox', 'PlayStation', 'Android', 'iOS') DEFAULT NULL,
    genero ENUM('masculino', 'feminino', 'outro', 'nao_binario') DEFAULT NULL,
    genero_preferencia ENUM('homem', 'mulher', 'ambos') DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- Tabela de jogos
CREATE TABLE games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rawg_id INT UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    image TEXT,
    genres VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de jogos favoritos/top do usuário
CREATE TABLE usuario_top_jogos (
    usuario_id INT NOT NULL,
    jogo_id INT NOT NULL,
    PRIMARY KEY (usuario_id, jogo_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (jogo_id) REFERENCES games(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela de curtidas entre usuários
CREATE TABLE curtidas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_origem INT NOT NULL,
    usuario_destino INT NOT NULL,
    liked BOOLEAN NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(usuario_origem, usuario_destino),
    FOREIGN KEY (usuario_origem) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_destino) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela de matches entre usuários
CREATE TABLE matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario1 INT NOT NULL,
    usuario2 INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(usuario1, usuario2),
    FOREIGN KEY (usuario1) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario2) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==================== INSERTS DE TESTE ====================
-- SENHA: etec123 (criptografada com bcrypt)
-- Hash: $2y$10$4i9Ixd2rJvCZiNHyLq8vJejkkZ7yEI9UxzL2l.kVcXQ0Nc3w8nHwG

-- Usuários de teste (Apresentação TCC)
INSERT INTO usuarios (id, nome, email, senha, foto, contato, data_nascimento, descricao, plataforma, genero, genero_preferencia, created_at) VALUES
(1, 'Tiago Xboxer', 'tiago@tcc.com', '$2y$10$4i9Ixd2rJvCZiNHyLq8vJejkkZ7yEI9UxzL2l.kVcXQ0Nc3w8nHwG', '', 'Xbox: TiagoPro', '1995-10-12', 'Focado em conquistas no Xbox. Gosto muito de jogos de tiro e ação.', 'Xbox', 'masculino', 'ambos', NOW()),
(2, 'Camila Souza', 'camila@tcc.com', '$2y$10$4i9Ixd2rJvCZiNHyLq8vJejkkZ7yEI9UxzL2l.kVcXQ0Nc3w8nHwG', '', 'PSN: MilaS', '2001-04-25', 'Platinadora de plantão! RPGs são a minha vida. Adoro conversar durante o jogo.', 'PlayStation', 'feminino', 'homem', NOW()),
(3, 'Leo Mobile', 'leo@tcc.com', '$2y$10$4i9Ixd2rJvCZiNHyLq8vJejkkZ7yEI9UxzL2l.kVcXQ0Nc3w8nHwG', '', 'WhatsApp: 11955554444', '2005-01-30', 'Jogo mais no celular indo pro curso. Busco galera pra partidas rápidas.', 'Android', 'masculino', NULL, NOW()),
(4, 'Mariana PC', 'mariana@tcc.com', '$2y$10$4i9Ixd2rJvCZiNHyLq8vJejkkZ7yEI9UxzL2l.kVcXQ0Nc3w8nHwG', '', 'Steam: MariPCMaster', '1999-11-11', 'Só jogo no teclado e mouse. Tryhard no Overwatch e valorant.', 'PC', 'feminino', 'ambos', NOW()),
(5, 'Alex Indie', 'alex@tcc.com', '$2y$10$4i9Ixd2rJvCZiNHyLq8vJejkkZ7yEI9UxzL2l.kVcXQ0Nc3w8nHwG', '', 'Discord: AlexIndie#111', '2002-07-07', 'Jogos indie são arte. Jogo de tudo no meu tempo livre.', 'PC', 'nao_binario', 'ambos', NOW()),
(6, 'Ricardo Casual', 'ricardo@tcc.com', '$2y$10$4i9Ixd2rJvCZiNHyLq8vJejkkZ7yEI9UxzL2l.kVcXQ0Nc3w8nHwG', '', 'Xbox: RickCasual', '1990-05-20', 'Pai de família que joga quando dá. Curto simuladores e jogos relaxantes.', 'Xbox', 'masculino', 'mulher', NOW()),
(7, 'Bianca iOS', 'bianca@tcc.com', '$2y$10$4i9Ixd2rJvCZiNHyLq8vJejkkZ7yEI9UxzL2l.kVcXQ0Nc3w8nHwG', '', 'Discord: BiaApple#222', '2004-12-05', 'Amante de jogos casuais e puzzle no iPhone.', 'iOS', 'feminino', 'homem', NOW());

-- Jogos (Apresentação TCC)
INSERT INTO games (id, rawg_id, name, image, genres, created_at) VALUES
(1, 3498, 'Hades', 'https://media.rawg.io/media/screenshots/3e0/3e0d4e66261d0e8e73bdc04b7e84e22e.jpg', 'Action, Indie, RPG', NOW()),
(2, 3272, 'Elden Ring', 'https://media.rawg.io/media/screenshots/f11/f113f2ba28c5e95ed0b21f6ef6cf5afb.jpg', 'Action, Adventure, RPG', NOW()),
(3, 422, 'Terraria', 'https://media.rawg.io/media/screenshots/4ca/4ca04d9f6f13c5e6edbdf0a107ce8e25.jpg', 'Action, Adventure, Indie', NOW()),
(4, 47, 'Overwatch', 'https://media.rawg.io/media/screenshots/fc6/fc615b4e844c99fa7b0ebfef7a4fd3b6.jpg', 'Action, Sport, Shooter', NOW()),
(5, 326, 'The Legend of Zelda: Breath of the Wild', 'https://media.rawg.io/media/screenshots/2c5/2c50c9651b9cda29abce158a2e6dd317.jpg', 'Action, Adventure, RPG', NOW()),
(6, 3439, 'Stardew Valley', 'https://media.rawg.io/media/screenshots/768/768b7b9fb9d13d72e3b23d6a0c6e7c5b.jpg', 'Indie, Simulation, RPG', NOW()),
(7, 430, 'Left 4 Dead 2', 'https://media.rawg.io/media/screenshots/2be/2be4813c59a9a123c4a87aea5a16c7e5.jpg', 'Action, Shooter', NOW()),
(8, 3672, 'Celeste', 'https://media.rawg.io/media/screenshots/16d/16d1149c73cae37326f0b02db15b8bbb.jpg', 'Indie, Platformer', NOW()),
(9, 5286, 'Cyberpunk 2077', 'https://media.rawg.io/media/screenshots/4f3/4f3f7e7b01e0c0e0b0e0c0e0b0e0c0e0.jpg', 'Action, Adventure, RPG', NOW()),
(10, 3070, 'Fall Guys: Ultimate Knockout', 'https://media.rawg.io/media/screenshots/6d2/6d2f5e5e5e5e5e5e5e5e5e5e5e5e5e5e.jpg', 'Action, Shooter, Sports', NOW());

-- Associação de Top 3 Jogos para cada Usuário
INSERT INTO usuario_top_jogos (usuario_id, jogo_id) VALUES
-- Tiago (1)
(1, 9), -- Cyberpunk
(1, 7), -- Left 4 Dead
(1, 4), -- Overwatch

-- Camila (2)
(2, 2), -- Elden Ring
(2, 1), -- Hades
(2, 8), -- Celeste

-- Leo Mobile (3)
(3, 3), -- Terraria
(3, 6), -- Stardew Valley
(3, 10), -- Fall Guys

-- Mariana PC (4)
(4, 4), -- Overwatch
(4, 7), -- Left 4 Dead 2
(4, 10), -- Fall Guys

-- Alex Indie (5)
(5, 8), -- Celeste
(5, 1), -- Hades
(5, 6), -- Stardew Valley

-- Ricardo Casual (6)
(6, 6), -- Stardew Valley
(6, 5), -- Zelda
(6, 10), -- Fall Guys

-- Bianca iOS (7)
(7, 3), -- Terraria
(7, 6), -- Stardew Valley
(7, 8); -- Celeste

-- Likes de exemplo para agilizar matches na apresentação
INSERT INTO curtidas (usuario_origem, usuario_destino, liked) VALUES
(1, 4, 1), -- Tiago curtiu Mariana
(4, 1, 1), -- Mariana curtiu Tiago
(2, 5, 1), -- Camila curtiu Alex
(5, 2, 1); -- Alex curtiu Camila

-- Matches gerados automaticamente na demonstração
INSERT INTO matches (usuario1, usuario2) VALUES
(1, 4), -- Tiago e Mariana
(2, 5); -- Camila e Alex

