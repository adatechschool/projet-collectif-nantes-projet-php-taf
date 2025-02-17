SET
  SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET
  time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;

/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;

/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;

/*!40101 SET NAMES utf8mb4 */;

DROP TABLE IF EXISTS `dechets_collectes`;

DROP TABLE IF EXISTS `benevoles_collectes`;

DROP TABLE IF EXISTS `benevoles`;

DROP TABLE IF EXISTS `collectes`;

CREATE TABLE
  `benevoles` (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `nom` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE KEY,
    `mot_de_passe` VARCHAR(255) NOT NULL,
    `role` ENUM ('admin', 'participant') NOT NULL,
    INDEX `idx_nom` (`nom`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE
  `collectes` (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `date_collecte` DATE NOT NULL,
    `lieu` VARCHAR(255) NOT NULL,
    INDEX `idx_collecte_date` (`date_collecte`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE
  `benevoles_collectes` (
    `id_benevole` INT DEFAULT NULL,
    `id_collecte` INT DEFAULT NULL,
    CONSTRAINT `benevoles_collectes_ibfk_1` FOREIGN KEY (`id_benevole`) REFERENCES `benevoles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `benevoles_collectes_ibfk_2` FOREIGN KEY (`id_collecte`) REFERENCES `collectes` (`id`) ON DELETE CASCADE,
    PRIMARY KEY (`id_benevole`, `id_collecte`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE
  `dechets_collectes` (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `id_collecte` INT DEFAULT NULL,
    `type_dechet` VARCHAR(50) NOT NULL,
    `quantite_kg` FLOAT NOT NULL,
    CONSTRAINT `dechets_collectes_ibfk_1` FOREIGN KEY (`id_collecte`) REFERENCES `collectes` (`id`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO
  `benevoles` (`nom`, `email`, `mot_de_passe`, `role`)
VALUES
  (
    'Alice Dupont',
    'alice.dupont@example.com',
    '5504b4f70ca78f97137ff8ad5f910248',
    'admin'
  ),
  (
    'Bob Martin',
    'bob.martin@example.com',
    '2e248e7a3b4fbaf2081b3dff10ee402b',
    'participant'
  ),
  (
    'Charlie Dubois',
    'charlie.dubois@example.com',
    '9148b120a413e9e84e57f1231f04119a',
    'participant'
  );

INSERT INTO
  `collectes` (`date_collecte`, `lieu`)
VALUES
  ('2024-02-01', 'Parc Central'),
  ('2024-02-05', 'Plage du Sud'),
  ('2024-02-10', 'Quartier Nord'),
  ('2025-01-04', 'paris'),
  ('3058-06-25', 'lyon'),
  ('2029-04-07', 'toulon'),
  ('2026-04-25', 'lille'),
  ('2028-05-10', 'toulouse'),
  ('0008-02-02', 'vertou');

INSERT INTO
  `benevoles_collectes` (`id_benevole`, `id_collecte`)
VALUES
  (1, 1),
  (1, 2),
  (1, 3),
  (1, 7),
  (1, 9),
  (2, 1),
  (2, 2),
  (2, 3),
  (3, 1),
  (3, 2),
  (3, 3),
  (3, 4),
  (3, 5),
  (3, 6),
  (3, 8);

INSERT INTO
  `dechets_collectes` (`id_collecte`, `type_dechet`, `quantite_kg`)
VALUES
  (1, 'plastique', 5.2),
  (1, 'verre', 3.1),
  (2, 'métal', 2.4),
  (2, 'papier', 1.7),
  (3, 'organique', 6.5),
  (3, 'plastique', 4.3);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;

/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
