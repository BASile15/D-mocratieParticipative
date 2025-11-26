-- phpMyAdmin SQL Dump
-- version 5.2.1deb1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : ven. 10 jan. 2025 à 03:34
-- Version du serveur : 10.11.6-MariaDB-0+deb12u1-log
-- Version de PHP : 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `saes3-bmonod`
--

DELIMITER $$
--
-- Procédures
--
CREATE DEFINER=`saes3-bmonod`@`%` PROCEDURE `cederGroupe` (IN `idAdmin` INT, IN `userId` INT, IN `groupId` INT)   BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM appartient
        WHERE idUtilisateur = userId AND idGroupe = groupId
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'L\'utilisateur n\'est pas membre de ce groupe.';
    ELSE
        UPDATE appartient
        SET idRole = 1
        WHERE idUtilisateur = idAdmin AND idGroupe = groupId;
        UPDATE appartient
        SET idRole = 2
        WHERE idUtilisateur = userId AND idGroupe = groupId;
    END IF;
END$$

CREATE DEFINER=`saes3-bmonod`@`%` PROCEDURE `ChangeUserRole` (IN `userId` INT, IN `groupId` INT, IN `newRoleId` INT)   BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM appartient
        WHERE idUtilisateur = userId AND idGroupe = groupId
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'L\'utilisateur n\'est pas membre de ce groupe.';
    ELSE
        UPDATE appartient
        SET idRole = newRoleId
        WHERE idUtilisateur = userId AND idGroupe = groupId;
    END IF;
END$$

--
-- Fonctions
--
CREATE DEFINER=`saes3-bmonod`@`%` FUNCTION `GetRoleUtilisateurDansGroupe` (`id_utilisateur` INTEGER, `id_Groupe` INTEGER) RETURNS INT(11)  BEGIN
    DECLARE role INTEGER;

    -- Récupérer l'id du rôle
    SELECT r.idRole
    INTO role
    FROM Role r
    INNER JOIN appartient apt ON apt.role = r.role
    INNER JOIN Groupe g ON g.idGroupe = apt.idGroupe
    INNER JOIN Utilisateur u ON u.idUtilisateur = apt.idUtilisateur
    WHERE u.idUtilisateur = id_utilisateur AND g.idGroupe = id_Groupe
    LIMIT 1;

    -- Retourner le rôle
    RETURN role;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `appartient`
--

CREATE TABLE `appartient` (
  `idUtilisateur` int(11) NOT NULL,
  `idGroupe` int(11) NOT NULL,
  `idRole` int(11) NOT NULL,
  `dateAttribution` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `appartient`
--

INSERT INTO `appartient` (`idUtilisateur`, `idGroupe`, `idRole`, `dateAttribution`) VALUES
(1, 1, 2, NULL),
(1, 3, 6, '2024-12-17 10:23:13'),
(1, 4, 1, '2024-12-17 10:22:55'),
(1, 10, 2, '2025-01-09 23:10:12'),
(2, 2, 2, '2024-11-15 13:17:26'),
(3, 3, 2, '2024-11-15 13:17:26'),
(4, 1, 4, '2025-01-10 00:53:35'),
(4, 4, 2, '2024-11-15 13:17:26'),
(5, 4, 3, '2024-11-15 13:17:26'),
(6, 4, 1, '2024-11-15 13:17:26'),
(7, 4, 1, '2024-11-15 13:17:26'),
(8, 4, 1, '2024-11-15 13:17:26'),
(9, 3, 1, '2024-11-15 13:17:26'),
(10, 3, 1, '2024-11-15 13:17:26'),
(11, 3, 3, '2024-11-15 13:17:26'),
(12, 3, 1, '2024-11-15 13:17:26'),
(13, 2, 1, '2024-11-15 13:17:26'),
(14, 2, 3, '2024-11-15 13:17:26'),
(22, 1, 3, '2025-01-07 13:05:38'),
(22, 8, 2, '2025-01-07 16:13:37'),
(23, 7, 2, '2025-01-07 08:56:13');

--
-- Déclencheurs `appartient`
--
DELIMITER $$
CREATE TRIGGER `AutoAdmin` BEFORE INSERT ON `appartient` FOR EACH ROW BEGIN
	DECLARE present int;
  SELECT COUNT(*) INTO present
  FROM appartient
  WHERE NEW.idGroupe=idGroupe;
  IF present < 1 THEN
    SET NEW.idRole = 2;
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `UniqueAdministateur` BEFORE UPDATE ON `appartient` FOR EACH ROW BEGIN
    DECLARE adminCount INT;
    IF NEW.idRole = 2 THEN
        SELECT COUNT(*) INTO adminCount
        FROM appartient
        WHERE idGroupe = NEW.idGroupe
          AND idRole = 2
          AND idUtilisateur != NEW.idUtilisateur;

        IF adminCount > 0 THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Erreur : Il ne peut y avoir qu''un seul administrateur par groupe.';
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `doubleRoleDansUnGroupe` BEFORE INSERT ON `appartient` FOR EACH ROW BEGIN
    DECLARE roleExistant INT;
    SELECT COUNT(*) INTO roleExistant
    FROM appartient
    WHERE idUtilisateur = NEW.idUtilisateur AND idGroupe = NEW.idGroupe;
    IF roleExistant > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Erreur : Un utilisateur ne peut avoir qu''un seul rôle par groupe.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `Commentaire`
--

CREATE TABLE `Commentaire` (
  `idCommentaire` int(11) NOT NULL,
  `idProposition` int(11) NOT NULL,
  `idUtilisateur` int(11) NOT NULL,
  `contenu` varchar(250) NOT NULL,
  `dateCommentaire` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Commentaire`
--

INSERT INTO `Commentaire` (`idCommentaire`, `idProposition`, `idUtilisateur`, `contenu`, `dateCommentaire`) VALUES
(2, 2, 15, 'Je connais un petit groupe sympa, si vous voulez !', '2024-11-15 13:31:39'),
(3, 3, 14, 'Je suis plutot d\'accord, mais il faudrait demander a la mairie pour le financement.', '2024-11-15 13:31:39'),
(4, 4, 2, 'Oui, je suis d\'accord !!.', '2024-11-15 13:31:39'),
(5, 5, 3, 'Je ne pense pas qu\'ajouter des policiers soit une bonne solution...', '2024-11-15 13:31:39'),
(6, 6, 11, 'J\'aime beaucoup l\'idee, j\'approuve.', '2024-11-15 13:31:39'),
(7, 6, 12, 'Je pense que c\'est une idee stupide.', '2024-11-15 13:31:39'),
(8, 7, 5, 'Si on en ajoute aussi dans les ecoles, je suis d\'accord.', '2024-11-15 13:31:39'),
(9, 8, 6, 'Je suis un cycliste regulier, donc cette proposition me semble tres benefique pour la ville.', '2024-11-15 13:31:39');

--
-- Déclencheurs `Commentaire`
--
DELIMITER $$
CREATE TRIGGER `Commentaires_INC` BEFORE INSERT ON `Commentaire` FOR EACH ROW BEGIN
	DECLARE leMax INT;
  SELECT COALESCE(MAX(idCommentaire), 0) + 1
  INTO leMax
  FROM Commentaire;
  IF NEW.idCommentaire IS NULL THEN
    SET NEW.idCommentaire = leMax;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `Commentaire_Like`
--

CREATE TABLE `Commentaire_Like` (
  `idCommentaireLike` int(11) NOT NULL,
  `idUtilisateur` int(11) DEFAULT NULL,
  `idCommentaire` int(11) DEFAULT NULL,
  `typeAction` enum('like','dislike') NOT NULL,
  `dateAction` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Commentaire_Like`
--

INSERT INTO `Commentaire_Like` (`idCommentaireLike`, `idUtilisateur`, `idCommentaire`, `typeAction`, `dateAction`) VALUES
(3, 2, 3, 'like', '2024-11-15 13:28:55'),
(4, 14, 4, 'like', '2024-11-15 13:28:55'),
(5, 9, 5, 'like', '2024-11-15 13:28:55'),
(6, 3, 6, 'like', '2024-11-15 13:28:55'),
(7, 9, 7, 'dislike', '2024-11-15 13:28:55'),
(8, 10, 7, 'dislike', '2024-11-15 13:28:55'),
(9, 11, 7, 'dislike', '2024-11-15 13:28:55'),
(10, 6, 8, 'like', '2024-11-15 13:28:55'),
(11, 5, 9, 'like', '2024-11-15 13:28:55');

--
-- Déclencheurs `Commentaire_Like`
--
DELIMITER $$
CREATE TRIGGER `Commentaire_Like_INC` BEFORE INSERT ON `Commentaire_Like` FOR EACH ROW BEGIN
	DECLARE leMax INT;
  SELECT COALESCE(MAX(idCommentaireLike), 0) + 1
  INTO leMax
  FROM Commentaire_Like;
  IF NEW.idCommentaireLike IS NULL THEN
    SET NEW.idCommentaireLike = leMax;
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `verifCommentaireLike` BEFORE INSERT ON `Commentaire_Like` FOR EACH ROW BEGIN
    DECLARE count_likes INT;
    SELECT COUNT(*) INTO count_likes
    FROM Commentaire_Like
    WHERE idUtilisateur = NEW.idUtilisateur
      AND idCommentaire = NEW.idCommentaire;
    IF count_likes > 0 THEN
        DELETE FROM Commentaire_Like
        WHERE idUtilisateur = NEW.idUtilisateur
          AND idCommentaire = NEW.idCommentaire;
    END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `Groupe`
--

CREATE TABLE `Groupe` (
  `idGroupe` int(11) NOT NULL,
  `nomGroupe` varchar(40) NOT NULL,
  `MDP` varchar(255) NOT NULL,
  `budgetGlobal` float DEFAULT NULL,
  `description` varchar(120) DEFAULT NULL,
  `imageGroupe` varchar(255) DEFAULT NULL,
  `couleurGroupe` varchar(64) DEFAULT NULL,
  `dateCreation` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Groupe`
--

INSERT INTO `Groupe` (`idGroupe`, `nomGroupe`, `MDP`, `budgetGlobal`, `description`, `imageGroupe`, `couleurGroupe`, `dateCreation`) VALUES
(1, 'Groupe des habitants de Dourdan', 'GHD', 10000, 'Propositions pour ameliorer la ville.', 'citoyen.png', '#33FF57', '2024-11-15 13:17:26'),
(2, 'Comite d\'Antony', 'CA', NULL, 'Discussion sur l\'entretien des espaces publics de la ville.', 'entretien.png', '#FF5733', '2024-11-15 13:17:26'),
(3, 'Democratie participative Orsay', 'DPO', NULL, 'Discussions pour ameliorer la securite dans la ville.', 'securite.png', '#5733FF', '2024-11-15 13:17:26'),
(4, 'Les parisiens', 'LP', NULL, 'Initiatives pour l\'environnement et la gestion des dechets.', 'ecolo.png', '#33FF73', '2024-11-15 13:17:26'),
(7, 'trgr', '$2y$10$5xEriiiLryTjCfCnrUh5Ben3QvMXH.HaOWzH30r0tZTdyqUjI4h.6', 400, 'rtgtr', 'the-wok.gif', 'regr', '2025-01-07 08:56:13'),
(8, 'testFinal', '$2y$10$Evq43aif3HuGnRvN/G.0WeXL.7qbMBPXnOogxaCwEjyRhPydWzOR2', 123456, 'test', '', 'test', '2025-01-07 16:13:37'),
(10, 'MAMA', '$2y$10$tGPDEQRto0qr4usqgK36ouJYUeEvzvHNLbdF23.Ev3HSc6IKDK/Qe', 0, 'MAMA', '', '#a700b3', '2025-01-09 23:10:12');

--
-- Déclencheurs `Groupe`
--
DELIMITER $$
CREATE TRIGGER `Groupe_INC` BEFORE INSERT ON `Groupe` FOR EACH ROW BEGIN
	DECLARE leMax INT;
  SELECT COALESCE(MAX(idGroupe), 0) + 1
  INTO leMax
  FROM Groupe;
  IF NEW.idGroupe IS NULL THEN
    SET NEW.idGroupe = leMax;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `Invitation`
--

CREATE TABLE `Invitation` (
  `idInvitation` int(11) NOT NULL,
  `emailInvite` varchar(150) NOT NULL,
  `idGroupe` int(11) NOT NULL,
  `idUtilisateur` int(64) NOT NULL,
  `token` varchar(255) NOT NULL,
  `etatInvitation` varchar(64) NOT NULL,
  `dateEnvoi` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Invitation`
--

INSERT INTO `Invitation` (`idInvitation`, `emailInvite`, `idGroupe`, `idUtilisateur`, `token`, `etatInvitation`, `dateEnvoi`) VALUES
(1, 'alexis@gmail.com', 2, 1, 'Comite d\'Antony', 'refusée', '2025-01-05 14:22:09'),
(2, 'root@test.fr', 1, 17, 'Groupe des habitants de Dourdan', 'refusée', '2025-01-05 14:30:31'),
(4, 'test@root.com', 1, 0, 'Groupe des habitants de Dourdan', 'en attente', NULL),
(5, 'monodbasile@gmail.com', 1, 22, 'Groupe des habitants de Dourdan', 'refusée', NULL),
(6, 'monodbasile@gmail.com', 1, 22, 'Groupe des habitants de Dourdan', 'acceptée', NULL),
(8, 'premier.ministe@gmail.com', 1, 4, 'Groupe des habitants de Dourdan', 'acceptée', '2025-01-10 00:28:27');

--
-- Déclencheurs `Invitation`
--
DELIMITER $$
CREATE TRIGGER `Invitation_INC` BEFORE INSERT ON `Invitation` FOR EACH ROW BEGIN
	DECLARE leMax INT;
  SELECT COALESCE(MAX(idInvitation), 0) + 1
  INTO leMax
  FROM Invitation;
  IF NEW.idInvitation IS NULL THEN
    SET NEW.idInvitation = leMax;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `Notification`
--

CREATE TABLE `Notification` (
  `idNotification` int(11) NOT NULL,
  `idUtilisateur` int(11) NOT NULL,
  `contenu` varchar(40) NOT NULL,
  `dateNotification` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Notification`
--

INSERT INTO `Notification` (`idNotification`, `idUtilisateur`, `contenu`, `dateNotification`) VALUES
(2, 2, 'Un vote commence pour une proposition.', '2024-11-15 13:17:26');

--
-- Déclencheurs `Notification`
--
DELIMITER $$
CREATE TRIGGER `Notification_INC` BEFORE INSERT ON `Notification` FOR EACH ROW BEGIN
	DECLARE leMax INT;
  SELECT COALESCE(MAX(idNotification), 0) + 1
  INTO leMax
  FROM Notification;
  IF NEW.idNotification IS NULL THEN
    SET NEW.idNotification = leMax;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `Proposition`
--

CREATE TABLE `Proposition` (
  `idProposition` int(11) NOT NULL,
  `idUtilisateur` int(11) NOT NULL,
  `idTheme` int(11) NOT NULL,
  `titreProposition` varchar(200) NOT NULL,
  `cout` float DEFAULT NULL,
  `description` varchar(300) NOT NULL,
  `dateSoumission` timestamp NULL DEFAULT current_timestamp(),
  `dureeDiscussion` int(11) DEFAULT NULL,
  `enVote` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Proposition`
--

INSERT INTO `Proposition` (`idProposition`, `idUtilisateur`, `idTheme`, `titreProposition`, `cout`, `description`, `dateSoumission`, `dureeDiscussion`, `enVote`) VALUES
(1, 15, 1, 'Amelioration des parcs', 200, 'Je propose d\'ameliorer en priorite les parcs pour enfants.', '2024-11-15 13:17:26', 20, 0),
(2, 16, 2, 'Organisation d\'un concert', 200, 'Je propose d\'organiser un consert sur la place principale de la ville pour feter la fete de la musique', '2024-11-15 13:17:26', 30, 0),
(3, 2, 3, 'Eboueurs', NULL, 'Je propose d\'aumener le nombre d\'eboueurs afin de rendre les lieux publiques plus sains.', '2024-11-15 13:17:26', 25, 0),
(4, 13, 4, 'Ajout de poubelles', NULL, 'Je propose d\'ajouter des poubelles dans les lieux publiques.', '2024-11-15 13:17:26', 35, 0),
(5, 9, 5, 'recruter des policiers', NULL, 'Je propose de recruer des policiers afin de surveiller plus efficacement la ville.', '2024-11-15 13:17:26', 50, 0),
(6, 10, 6, 'Ajout des evenements', NULL, 'Je propose d\'ajouter un espace Evenement sur le site de la ville, afin de se tenir informer des derni�res nouvelles', '2024-11-15 13:17:26', 40, 0),
(7, 4, 7, 'Ajouter des poubelles', NULL, 'Ajouter plus de poubelles dans les lieux publiques.', '2024-11-21 13:17:26', 60, 0),
(8, 4, 8, 'Ajouter des velibs', NULL, 'Je propose d\'ajouter des velos en libre service pour faciliter les deplacements.', '2024-11-22 15:27:28', 60, 0),
(9, 1, 9, 'La masterclasse de basile', 0, 'Ceci est une vrai masterclasse Basile le goat que tu pense être', '2025-01-09 17:19:42', 2, 0),
(10, 1, 9, 't2', 500, 't2', '2025-01-09 17:46:42', 2, 0),
(11, 1, 1, 'test', 500, '500', '2025-01-09 23:45:07', 20, 0);

--
-- Déclencheurs `Proposition`
--
DELIMITER $$
CREATE TRIGGER `NotifGroupeDelPropo` AFTER DELETE ON `Proposition` FOR EACH ROW BEGIN
    DECLARE user_id INT;
    DECLARE terminer INT DEFAULT 0;
    DECLARE listeUser CURSOR FOR 
        SELECT u.idUtilisateur
        FROM appartient apt
        JOIN Utilisateur u ON apt.idUtilisateur = u.idUtilisateur
        WHERE apt.idGroupe = (SELECT idGroupe FROM Proposition WHERE idProposition = OLD.idProposition);
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET terminer = 1;
    OPEN listeUser;
    read_loop: LOOP
        FETCH listeUser INTO user_id;
        IF terminer THEN
            LEAVE read_loop;
        END IF;
        INSERT INTO Notification (idUtilisateur, contenu, dateNotification)
        VALUES (user_id, CONCAT('La proposition "', OLD.titreProposition, '" a été supprimée.'), NOW());
    END LOOP;
    CLOSE listeUser;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `Proposition_INC` BEFORE INSERT ON `Proposition` FOR EACH ROW BEGIN
	DECLARE leMax INT;
  SELECT COALESCE(MAX(idProposition), 0) + 1
  INTO leMax
  FROM Proposition;
  IF NEW.idProposition IS NULL THEN
    SET NEW.idProposition = leMax;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `PropositionsParTheme`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `PropositionsParTheme` (
`nomTheme` varchar(100)
,`titreProposition` varchar(200)
,`description` varchar(300)
);

-- --------------------------------------------------------

--
-- Structure de la table `Proposition_Like`
--

CREATE TABLE `Proposition_Like` (
  `idPropositionLike` int(11) NOT NULL,
  `idUtilisateur` int(11) DEFAULT NULL,
  `idProposition` int(11) DEFAULT NULL,
  `typeAction` enum('like','dislike') NOT NULL,
  `dateAction` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Proposition_Like`
--

INSERT INTO `Proposition_Like` (`idPropositionLike`, `idUtilisateur`, `idProposition`, `typeAction`, `dateAction`) VALUES
(1, 16, 1, 'like', '2024-11-15 13:28:55'),
(3, 15, 2, 'like', '2024-11-15 13:28:55'),
(5, 13, 3, 'like', '2024-11-15 13:28:55'),
(6, 14, 3, 'like', '2024-11-15 13:28:55'),
(7, 2, 4, 'dislike', '2024-11-15 13:28:55'),
(8, 14, 4, 'like', '2024-11-15 13:28:55'),
(9, 3, 5, 'like', '2024-11-15 13:28:55'),
(10, 10, 5, 'like', '2024-11-15 13:28:55'),
(11, 11, 5, 'like', '2024-11-15 13:28:55'),
(12, 3, 6, 'like', '2024-11-15 13:28:55'),
(13, 11, 6, 'like', '2024-11-15 13:28:55'),
(14, 12, 6, 'dislike', '2024-11-15 13:28:55'),
(15, 5, 7, 'like', '2024-11-15 13:28:55'),
(16, 6, 7, 'like', '2024-11-15 13:28:55'),
(17, 7, 7, 'like', '2024-11-15 13:28:55'),
(18, 8, 7, 'like', '2024-11-15 13:28:55'),
(19, 5, 8, 'like', '2024-11-15 13:28:55'),
(20, 6, 8, 'like', '2024-11-15 13:28:55'),
(21, 7, 8, 'like', '2024-11-15 13:28:55'),
(22, 8, 8, 'like', '2024-11-15 13:28:55');

--
-- Déclencheurs `Proposition_Like`
--
DELIMITER $$
CREATE TRIGGER `Proposition_like_INC` BEFORE INSERT ON `Proposition_Like` FOR EACH ROW BEGIN
	DECLARE leMax INT;
  SELECT COALESCE(MAX(idPropositionLike), 0) + 1
  INTO leMax
  FROM Proposition_Like;
  IF NEW.idPropositionLike IS NULL THEN
    SET NEW.idPropositionLike = leMax;
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `verifPropositionLike` BEFORE INSERT ON `Proposition_Like` FOR EACH ROW BEGIN
    DECLARE count_likes INT;
    SELECT COUNT(*) INTO count_likes
    FROM Proposition_Like
    WHERE idUtilisateur = NEW.idUtilisateur
      AND idProposition = NEW.idProposition;
    IF count_likes > 0 THEN
        DELETE FROM Proposition_Like
        WHERE idUtilisateur = NEW.idUtilisateur
          AND idProposition = NEW.idProposition;
    END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `Role`
--

CREATE TABLE `Role` (
  `idRole` int(11) NOT NULL,
  `nomRole` varchar(50) NOT NULL,
  `descriptionRole` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Role`
--

INSERT INTO `Role` (`idRole`, `nomRole`, `descriptionRole`) VALUES
(1, 'Membre', 'Participe aux discussions et votes.'),
(2, 'Administrateur', 'Gare les groupes et utilisateurs.'),
(3, 'Moderateur', 'Modere les commentaires et propositions.'),
(4, 'Organisateur', 'Organise les votes.'),
(5, 'Scrutateur', 'Supervise et valide les votes.'),
(6, 'Decideur', 'Prend des decisions basees sur les votes.');

--
-- Déclencheurs `Role`
--
DELIMITER $$
CREATE TRIGGER `Role_INC` BEFORE INSERT ON `Role` FOR EACH ROW BEGIN
	DECLARE leMax INT;
  SELECT COALESCE(MAX(idRole), 0) + 1
  INTO leMax
  FROM Role;
  IF NEW.idRole IS NULL THEN
    SET NEW.idRole = leMax;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `Signalement`
--

CREATE TABLE `Signalement` (
  `idSignalement` int(11) NOT NULL,
  `idUtilisateur` int(11) NOT NULL,
  `idCommentaire` int(11) DEFAULT NULL,
  `idProposition` int(11) DEFAULT NULL,
  `raison` varchar(255) NOT NULL,
  `dateSignalement` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Signalement`
--

INSERT INTO `Signalement` (`idSignalement`, `idUtilisateur`, `idCommentaire`, `idProposition`, `raison`, `dateSignalement`) VALUES
(1, 9, 7, NULL, 'Propos innapropies', '2024-11-15 14:31:39'),
(2, 10, 7, NULL, 'Commentaire mechant pour rien...', '2024-11-15 14:31:39'),
(3, 11, 7, NULL, 'Insultant', '2024-11-15 14:31:39'),
(4, 1, NULL, 8, 'pas de velo ici', '2025-01-10 02:57:24');

--
-- Déclencheurs `Signalement`
--
DELIMITER $$
CREATE TRIGGER `Signalement_INC` BEFORE INSERT ON `Signalement` FOR EACH ROW BEGIN
	DECLARE leMax INT;
  SELECT COALESCE(MAX(idSignalement), 0) + 1
  INTO leMax
  FROM Signalement;
  IF NEW.idSignalement IS NULL THEN
    SET NEW.idSignalement = leMax;
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `verifContrainteSignalement` BEFORE INSERT ON `Signalement` FOR EACH ROW BEGIN
    IF (NEW.idCommentaire IS NOT NULL AND NEW.idProposition IS NOT NULL) OR
       (NEW.idCommentaire IS NULL AND NEW.idProposition IS NULL) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Erreur : Proposition ou commentaire signalé en simultané ou aucun des deux.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `Theme`
--

CREATE TABLE `Theme` (
  `idTheme` int(11) NOT NULL,
  `idGroupe` int(11) NOT NULL,
  `nomTheme` varchar(100) NOT NULL,
  `budgetLimite` int(8) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Theme`
--

INSERT INTO `Theme` (`idTheme`, `idGroupe`, `nomTheme`, `budgetLimite`, `description`) VALUES
(1, 1, 'Entretien des espaces publics', 5000, 'Ameliorer l\'entretien des parcs, rues et espaces publics de la ville.'),
(2, 1, 'Culture et evenements', 3000, 'Democratie participative pour les evenements culturels et artistiques de la ville.'),
(3, 2, 'Entretien des lieux publics', NULL, 'Assurer l\'entretien regulier des batiments publics et infrastructures de la ville.'),
(4, 2, 'Proprete urbaine', NULL, 'Ameliorer de la proprete des rues, parcs et espaces publics.'),
(5, 3, 'Securite urbaine', NULL, 'Discussions sur l\'amelioration de la securite dans les rues et espaces publics.'),
(6, 3, 'Site pour la ville', NULL, 'Creer un site internet dedie a notre belle ville.'),
(7, 4, 'Gestion des dechets', NULL, 'Gerer mieux le recyclage et reduire les dechets urbains.'),
(8, 4, 'Mobilite durable', NULL, 'Amelioration de la mobilite douce et reduire la pollution en ville.'),
(9, 1, 'test', 5000, 'ceci est un test'),
(10, 1, 'masterclasse', NULL, 'test');

--
-- Déclencheurs `Theme`
--
DELIMITER $$
CREATE TRIGGER `Theme_INC` BEFORE INSERT ON `Theme` FOR EACH ROW BEGIN
	DECLARE leMax INT;
  SELECT COALESCE(MAX(idTheme), 0) + 1
  INTO leMax
  FROM Theme;
  IF NEW.idTheme IS NULL THEN
    SET NEW.idTheme = leMax;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `Utilisateur`
--

CREATE TABLE `Utilisateur` (
  `idUtilisateur` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `motDePasse` varchar(255) NOT NULL,
  `adressePostale` varchar(255) DEFAULT NULL,
  `ville` varchar(40) DEFAULT NULL,
  `dateInscription` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Utilisateur`
--

INSERT INTO `Utilisateur` (`idUtilisateur`, `nom`, `prenom`, `email`, `motDePasse`, `adressePostale`, `ville`, `dateInscription`) VALUES
(1, 'Tirant', 'Alexis', 'alexis@gmail.com', '$2y$10$KJ48fSzykDeXliJuPVwN6..MNcxB/bJocCQJMgCS4IsdTdNhlpV4O', '3 rue des pommes', 'dourdan', '2024-12-13 14:26:12'),
(2, 'Monod', 'Basile', 'basilemonod15@gmail.com', '$2y$10$2IqBl.2A7qtyhXxDsqUaiut9C6gS3SiZXyxFl27aGkl3F6PKWwcgK', '9 rue Pierre Vermeir', 'Antony', '2024-11-15 13:17:26'),
(3, 'Rabehaja', 'Iry', 'iry.rabehadja@gmail.com', '$2y$10$2IqBl.2A7qtyhXxDsqUaiut9C6gS3SiZXyxFl27aGkl3F6PKWwcgK', '789 Boulevard Central', 'Orsay', '2024-11-15 13:17:26'),
(4, 'Barnier', 'Michel', 'premier.ministe@gmail.com', '$2y$10$2IqBl.2A7qtyhXxDsqUaiut9C6gS3SiZXyxFl27aGkl3F6PKWwcgK', '12 Rue des Lilas', 'Paris', '2024-11-15 13:17:26'),
(5, 'Bernier', 'Alexis', 'Zen.rl@gmail.com', '$2y$10$2IqBl.2A7qtyhXxDsqUaiut9C6gS3SiZXyxFl27aGkl3F6PKWwcgK', '16 rue de Rivoli', 'Paris', '2024-11-15 13:17:26'),
(6, 'Rogez', 'Evan', 'evan.rogEZ@gmail.com', '$2y$10$2IqBl.2A7qtyhXxDsqUaiut9C6gS3SiZXyxFl27aGkl3F6PKWwcgK', '22 rue Montorgueil', 'Paris', '2024-11-15 13:17:26'),
(7, 'Morel', 'Luc', 'luc.morel@gmail.com', '$2y$10$2IqBl.2A7qtyhXxDsqUaiut9C6gS3SiZXyxFl27aGkl3F6PKWwcgK', '56 Impase des Pins', 'Paris', '2024-11-15 13:17:26'),
(8, 'Nguyen', 'Thi', 'thi.nguyen@gmail.com', '$2y$10$2IqBl.2A7qtyhXxDsqUaiut9C6gS3SiZXyxFl27aGkl3F6PKWwcgK', '21 Rue de Daglan', 'Paris', '2024-11-15 13:17:26'),
(9, 'Roux', 'Elise', 'eliseroux1234@gmail.com', '$2y$10$2IqBl.2A7qtyhXxDsqUaiut9C6gS3SiZXyxFl27aGkl3F6PKWwcgK', '101 Rue Victor Hugo', 'Orsay', '2024-11-15 13:17:26'),
(10, 'Lopez', 'Marc', 'marc.lopez@gmail.com', '$2y$10$2IqBl.2A7qtyhXxDsqUaiut9C6gS3SiZXyxFl27aGkl3F6PKWwcgK', '98 Avenue de l\'eglise', 'Orsay', '2024-11-15 13:17:26'),
(11, 'Darmon', 'R�mi', 'remi.darmon@gmail.com', '$2y$10$2IqBl.2A7qtyhXxDsqUaiut9C6gS3SiZXyxFl27aGkl3F6PKWwcgK', '2 Place du general Leclerc', 'Orsay', '2024-11-15 13:17:26'),
(12, 'Daniel', 'Anoine', 'anoine.daniel@gmail.com', '$2y$10$2IqBl.2A7qtyhXxDsqUaiut9C6gS3SiZXyxFl27aGkl3F6PKWwcgK', '50 rue de l\'universite', 'Orsay', '2024-11-16 13:17:26'),
(13, 'Giraud', 'Sophie', 'sophie.giraud@gmail.com', '$2y$10$2IqBl.2A7qtyhXxDsqUaiut9C6gS3SiZXyxFl27aGkl3F6PKWwcgK', '34 Rue Bleue', 'Antony', '2024-11-15 13:17:26'),
(14, 'Senant', 'Jean-Yves', 'JYves.Senant@gmail.com', '$2y$10$2IqBl.2A7qtyhXxDsqUaiut9C6gS3SiZXyxFl27aGkl3F6PKWwcgK', '1 Place de l\'hotel de ville', 'Antony', '2024-11-15 13:17:26'),
(15, 'Garcia', 'Laura', 'laura.garcia@gmail.com', '$2y$10$2IqBl.2A7qtyhXxDsqUaiut9C6gS3SiZXyxFl27aGkl3F6PKWwcgK', '17 Rue Marron', 'Dourdan', '2024-11-15 13:17:26'),
(16, 'Petit', 'Julien', 'julien.petit@gmail.com', '$2y$10$2IqBl.2A7qtyhXxDsqUaiut9C6gS3SiZXyxFl27aGkl3F6PKWwcgK', '8 Rue Rouge', 'Dourdan', '2024-11-15 13:17:26'),
(17, 'root', 'root', 'root@test.fr', '$2y$10$2IqBl.2A7qtyhXxDsqUaiut9C6gS3SiZXyxFl27aGkl3F6PKWwcgK', 'root', 'root', '2024-12-13 14:11:29'),
(20, 'Monod', 'Basile', 'basilemonod@gmail.com', '$2y$10$oNvtzQUsp9dObyU/d.wujerQ1mO4tbUY.mkxuQJokz1yqAzP9H3fa', '9 rue Pierre Vermeir', 'Antony', '2024-12-16 14:27:06'),
(21, 'durand', 'ines', 'fantome@gmail.com', '$2y$10$x9Wrm8yLZpa1c.JNHn9LO.aDjr6YwIt.iQi7xog8VPLNMxdbqfKZi', 'sdygfjd', 'sdfs', '2024-12-31 15:36:41'),
(22, 'Monod', 'Basile', 'monodbasile@gmail.com', '$2y$10$immlHan2Yv4fZKannPNEeO0XDA3pOaXqdJ0Jtq4Q1sfNNKQrmXB0C', '9 rue pierre vermeir', 'Antony', '2025-01-07 09:39:14'),
(23, 'Patrick', 'Cabillaud', 'root@root', '$2y$10$SNH776WjmLzeJhvM5M5ITe1CBsckVBbl.z3vlQco4FvjuI50Bngem', '20 rue Paris', 'Paris', '2025-01-07 09:54:29');

--
-- Déclencheurs `Utilisateur`
--
DELIMITER $$
CREATE TRIGGER `Utilisateur_INC` BEFORE INSERT ON `Utilisateur` FOR EACH ROW BEGIN
	DECLARE leMax INT;
  SELECT COALESCE(MAX(idUtilisateur), 0) + 1
  INTO leMax
  FROM Utilisateur;
  IF NEW.idUtilisateur IS NULL THEN
    SET NEW.idUtilisateur = leMax;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `Vote`
--

CREATE TABLE `Vote` (
  `idVote` int(11) NOT NULL,
  `idProposition` int(11) NOT NULL,
  `idUtilisateur` int(11) NOT NULL,
  `choix` enum('Pour','Contre','Abstention') NOT NULL,
  `dateVote` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déclencheurs `Vote`
--
DELIMITER $$
CREATE TRIGGER `Vote_INC` BEFORE INSERT ON `Vote` FOR EACH ROW BEGIN
	DECLARE leMax INT;
  SELECT COALESCE(MAX(idVote), 0) + 1
  INTO leMax
  FROM Vote;
  IF NEW.idVote IS NULL THEN
    SET NEW.idVote = leMax;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `VueCommentaireSignalement`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `VueCommentaireSignalement` (
`idSignalement` int(11)
,`idCommentaire` int(11)
,`contenu` varchar(250)
,`raison` varchar(255)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `VueGroupesUtilisateur`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `VueGroupesUtilisateur` (
`idUtilisateur` int(11)
,`idGroupe` int(11)
,`nomGroupe` varchar(40)
,`descriptionGroupe` varchar(120)
,`imageGroupe` varchar(255)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `VueLikesDislikes`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `VueLikesDislikes` (
`idProposition` int(11)
,`titreProposition` varchar(200)
,`nbLikes` decimal(22,0)
,`nbDislikes` decimal(22,0)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `VueNBComPropositions`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `VueNBComPropositions` (
`idProposition` int(11)
,`description` varchar(300)
,`nbCommentaires` bigint(21)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `VuePropositionSignalement`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `VuePropositionSignalement` (
`idSignalement` int(11)
,`idProposition` int(11)
,`description` varchar(300)
,`raison` varchar(255)
);

-- --------------------------------------------------------

--
-- Structure de la vue `PropositionsParTheme`
--
DROP TABLE IF EXISTS `PropositionsParTheme`;

CREATE ALGORITHM=UNDEFINED DEFINER=`saes3-bmonod`@`%` SQL SECURITY DEFINER VIEW `PropositionsParTheme`  AS SELECT `t`.`nomTheme` AS `nomTheme`, `p`.`titreProposition` AS `titreProposition`, `p`.`description` AS `description` FROM (`Proposition` `p` join `Theme` `t` on(`p`.`idTheme` = `t`.`idTheme`)) ;

-- --------------------------------------------------------

--
-- Structure de la vue `VueCommentaireSignalement`
--
DROP TABLE IF EXISTS `VueCommentaireSignalement`;

CREATE ALGORITHM=UNDEFINED DEFINER=`saes3-bmonod`@`%` SQL SECURITY DEFINER VIEW `VueCommentaireSignalement`  AS SELECT `s`.`idSignalement` AS `idSignalement`, `s`.`idCommentaire` AS `idCommentaire`, `c`.`contenu` AS `contenu`, `s`.`raison` AS `raison` FROM (`Commentaire` `c` left join `Signalement` `s` on(`s`.`idCommentaire` = `c`.`idCommentaire`)) WHERE `s`.`idSignalement` is not null ;

-- --------------------------------------------------------

--
-- Structure de la vue `VueGroupesUtilisateur`
--
DROP TABLE IF EXISTS `VueGroupesUtilisateur`;

CREATE ALGORITHM=UNDEFINED DEFINER=`saes3-bmonod`@`%` SQL SECURITY DEFINER VIEW `VueGroupesUtilisateur`  AS SELECT `u`.`idUtilisateur` AS `idUtilisateur`, `g`.`idGroupe` AS `idGroupe`, `g`.`nomGroupe` AS `nomGroupe`, `g`.`description` AS `descriptionGroupe`, `g`.`imageGroupe` AS `imageGroupe` FROM ((`Groupe` `g` join `appartient` on(`appartient`.`idGroupe` = `g`.`idGroupe`)) join `Utilisateur` `u` on(`u`.`idUtilisateur` = `appartient`.`idUtilisateur`)) WHERE 1 ORDER BY `u`.`idUtilisateur` DESC ;

-- --------------------------------------------------------

--
-- Structure de la vue `VueLikesDislikes`
--
DROP TABLE IF EXISTS `VueLikesDislikes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`saes3-bmonod`@`%` SQL SECURITY DEFINER VIEW `VueLikesDislikes`  AS SELECT `p`.`idProposition` AS `idProposition`, `p`.`titreProposition` AS `titreProposition`, sum(case when `pl`.`typeAction` = 'like' then 1 else 0 end) AS `nbLikes`, sum(case when `pl`.`typeAction` = 'dislike' then 1 else 0 end) AS `nbDislikes` FROM (`Proposition` `p` left join `Proposition_Like` `pl` on(`p`.`idProposition` = `pl`.`idProposition`)) GROUP BY `p`.`idProposition`, `p`.`titreProposition` ;

-- --------------------------------------------------------

--
-- Structure de la vue `VueNBComPropositions`
--
DROP TABLE IF EXISTS `VueNBComPropositions`;

CREATE ALGORITHM=UNDEFINED DEFINER=`saes3-bmonod`@`%` SQL SECURITY DEFINER VIEW `VueNBComPropositions`  AS SELECT `p`.`idProposition` AS `idProposition`, `p`.`description` AS `description`, count(`c`.`idCommentaire`) AS `nbCommentaires` FROM (`Proposition` `p` left join `Commentaire` `c` on(`p`.`idProposition` = `c`.`idProposition`)) GROUP BY `p`.`idProposition` ;

-- --------------------------------------------------------

--
-- Structure de la vue `VuePropositionSignalement`
--
DROP TABLE IF EXISTS `VuePropositionSignalement`;

CREATE ALGORITHM=UNDEFINED DEFINER=`saes3-bmonod`@`%` SQL SECURITY DEFINER VIEW `VuePropositionSignalement`  AS SELECT `s`.`idSignalement` AS `idSignalement`, `p`.`idProposition` AS `idProposition`, `p`.`description` AS `description`, `s`.`raison` AS `raison` FROM (`Proposition` `p` left join `Signalement` `s` on(`s`.`idProposition` = `p`.`idProposition`)) WHERE `s`.`idSignalement` is not null ;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `appartient`
--
ALTER TABLE `appartient`
  ADD PRIMARY KEY (`idUtilisateur`,`idGroupe`,`idRole`),
  ADD UNIQUE KEY `unique_user_group` (`idUtilisateur`,`idGroupe`),
  ADD KEY `appartient_fk_2` (`idGroupe`),
  ADD KEY `appartient_fk_3` (`idRole`);

--
-- Index pour la table `Commentaire`
--
ALTER TABLE `Commentaire`
  ADD PRIMARY KEY (`idCommentaire`),
  ADD KEY `Commentaire_fk_1` (`idProposition`),
  ADD KEY `Commentaire_fk_2` (`idUtilisateur`);

--
-- Index pour la table `Commentaire_Like`
--
ALTER TABLE `Commentaire_Like`
  ADD PRIMARY KEY (`idCommentaireLike`),
  ADD KEY `Commentaire_Like_fk_1` (`idCommentaire`),
  ADD KEY `Commentaire_Like_fk_2` (`idUtilisateur`);

--
-- Index pour la table `Groupe`
--
ALTER TABLE `Groupe`
  ADD PRIMARY KEY (`idGroupe`);

--
-- Index pour la table `Invitation`
--
ALTER TABLE `Invitation`
  ADD PRIMARY KEY (`idInvitation`),
  ADD KEY `Invitation_fk_1` (`idGroupe`);

--
-- Index pour la table `Notification`
--
ALTER TABLE `Notification`
  ADD PRIMARY KEY (`idNotification`),
  ADD KEY `Notification_fk_1` (`idUtilisateur`);

--
-- Index pour la table `Proposition`
--
ALTER TABLE `Proposition`
  ADD PRIMARY KEY (`idProposition`),
  ADD KEY `Proposition_fk_1` (`idUtilisateur`),
  ADD KEY `Proposition_fk_2` (`idTheme`);

--
-- Index pour la table `Proposition_Like`
--
ALTER TABLE `Proposition_Like`
  ADD PRIMARY KEY (`idPropositionLike`),
  ADD KEY `Proposition_Like_fk_1` (`idUtilisateur`),
  ADD KEY `Proposition_Like_fk_2` (`idProposition`);

--
-- Index pour la table `Role`
--
ALTER TABLE `Role`
  ADD PRIMARY KEY (`idRole`),
  ADD UNIQUE KEY `nomRole` (`nomRole`);

--
-- Index pour la table `Signalement`
--
ALTER TABLE `Signalement`
  ADD PRIMARY KEY (`idSignalement`),
  ADD KEY `Signalement_fk_1` (`idUtilisateur`),
  ADD KEY `Signalement_fk_2` (`idProposition`),
  ADD KEY `Signalement_fk_3` (`idCommentaire`);

--
-- Index pour la table `Theme`
--
ALTER TABLE `Theme`
  ADD PRIMARY KEY (`idTheme`),
  ADD KEY `Theme_fk_1` (`idGroupe`);

--
-- Index pour la table `Utilisateur`
--
ALTER TABLE `Utilisateur`
  ADD PRIMARY KEY (`idUtilisateur`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `Vote`
--
ALTER TABLE `Vote`
  ADD PRIMARY KEY (`idVote`),
  ADD KEY `FK_Vote_Proposition` (`idProposition`),
  ADD KEY `FK_Vote_Utilisateur` (`idUtilisateur`);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `appartient`
--
ALTER TABLE `appartient`
  ADD CONSTRAINT `appartient_fk_1` FOREIGN KEY (`idUtilisateur`) REFERENCES `Utilisateur` (`idUtilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `appartient_fk_2` FOREIGN KEY (`idGroupe`) REFERENCES `Groupe` (`idGroupe`) ON DELETE CASCADE,
  ADD CONSTRAINT `appartient_fk_3` FOREIGN KEY (`idRole`) REFERENCES `Role` (`idRole`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Commentaire`
--
ALTER TABLE `Commentaire`
  ADD CONSTRAINT `Commentaire_fk_1` FOREIGN KEY (`idProposition`) REFERENCES `Proposition` (`idProposition`) ON DELETE CASCADE,
  ADD CONSTRAINT `Commentaire_fk_2` FOREIGN KEY (`idUtilisateur`) REFERENCES `Utilisateur` (`idUtilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Commentaire_Like`
--
ALTER TABLE `Commentaire_Like`
  ADD CONSTRAINT `Commentaire_Like_fk_1` FOREIGN KEY (`idCommentaire`) REFERENCES `Commentaire` (`idCommentaire`) ON DELETE CASCADE,
  ADD CONSTRAINT `Commentaire_Like_fk_2` FOREIGN KEY (`idUtilisateur`) REFERENCES `Utilisateur` (`idUtilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Invitation`
--
ALTER TABLE `Invitation`
  ADD CONSTRAINT `Invitation_fk_1` FOREIGN KEY (`idGroupe`) REFERENCES `Groupe` (`idGroupe`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Notification`
--
ALTER TABLE `Notification`
  ADD CONSTRAINT `Notification_fk_1` FOREIGN KEY (`idUtilisateur`) REFERENCES `Utilisateur` (`idUtilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Proposition`
--
ALTER TABLE `Proposition`
  ADD CONSTRAINT `Proposition_fk_1` FOREIGN KEY (`idUtilisateur`) REFERENCES `Utilisateur` (`idUtilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `Proposition_fk_2` FOREIGN KEY (`idTheme`) REFERENCES `Theme` (`idTheme`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Proposition_Like`
--
ALTER TABLE `Proposition_Like`
  ADD CONSTRAINT `Proposition_Like_fk_1` FOREIGN KEY (`idUtilisateur`) REFERENCES `Utilisateur` (`idUtilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `Proposition_Like_fk_2` FOREIGN KEY (`idProposition`) REFERENCES `Proposition` (`idProposition`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Signalement`
--
ALTER TABLE `Signalement`
  ADD CONSTRAINT `Signalement_fk_1` FOREIGN KEY (`idUtilisateur`) REFERENCES `Utilisateur` (`idUtilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `Signalement_fk_2` FOREIGN KEY (`idProposition`) REFERENCES `Proposition` (`idProposition`) ON DELETE CASCADE,
  ADD CONSTRAINT `Signalement_fk_3` FOREIGN KEY (`idCommentaire`) REFERENCES `Commentaire` (`idCommentaire`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Theme`
--
ALTER TABLE `Theme`
  ADD CONSTRAINT `Theme_fk_1` FOREIGN KEY (`idGroupe`) REFERENCES `Groupe` (`idGroupe`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Vote`
--
ALTER TABLE `Vote`
  ADD CONSTRAINT `FK_Vote_Proposition` FOREIGN KEY (`idProposition`) REFERENCES `Proposition` (`idProposition`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_Vote_Utilisateur` FOREIGN KEY (`idUtilisateur`) REFERENCES `Utilisateur` (`idUtilisateur`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
