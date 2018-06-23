-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le :  ven. 25 mai 2018 à 20:28
-- Version du serveur :  10.1.26-MariaDB
-- Version de PHP :  7.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `phpblog`
--

-- --------------------------------------------------------

--
-- Structure de la table `comments`
--

CREATE TABLE `comments` (
  `comment_id` smallint(5) UNSIGNED NOT NULL,
  `comment_creationDate` datetime NOT NULL,
  `comment_nickName` varchar(50) NOT NULL,
  `comment_email` varchar(255) NOT NULL,
  `comment_title` varchar(255) NOT NULL,
  `comment_content` text NOT NULL,
  `comment_isValidated` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `comment_isPublished` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `comment_postId` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `contacts`
--

CREATE TABLE `contacts` (
  `contact_id` smallint(6) NOT NULL,
  `contact_sendingDate` datetime NOT NULL,
  `contact_familyName` varchar(70) NOT NULL,
  `contact_firstName` varchar(50) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `contact_message` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='"contacts" Store valid messages from contact form.';

-- --------------------------------------------------------

--
-- Structure de la table `images`
--

CREATE TABLE `images` (
  `image_id` smallint(5) UNSIGNED NOT NULL,
  `image_creationDate` datetime NOT NULL,
  `image_updateDate` datetime DEFAULT NULL,
  `image_name` varchar(255) NOT NULL,
  `image_extension` char(4) NOT NULL,
  `image_dimensions` char(9) NOT NULL,
  `image_size` mediumint(7) UNSIGNED NOT NULL,
  `image_creatorId` tinyint(4) UNSIGNED NOT NULL,
  `image_postId` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `posts`
--

CREATE TABLE `posts` (
  `post_id` smallint(5) UNSIGNED NOT NULL,
  `post_creationDate` datetime NOT NULL,
  `post_updateDate` datetime DEFAULT NULL,
  `post_title` varchar(255) NOT NULL,
  `post_intro` text NOT NULL,
  `post_content` text NOT NULL,
  `post_slug` varchar(255) NOT NULL,
  `post_isSlugCustomized` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `post_isValidated` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `post_isPublished` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `post_userId` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `user_id` tinyint(4) UNSIGNED NOT NULL,
  `user_creationDate` datetime NOT NULL,
  `user_familyName` varchar(255) NOT NULL,
  `user_firstName` varchar(255) NOT NULL,
  `user_nickName` varchar(50) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_passwordUpdateToken` char(15) NOT NULL,
  `user_passwordUpdateDate` datetime DEFAULT NULL,
  `user_activationCode` varchar(255) NOT NULL,
  `user_activationDate` datetime NOT NULL,
  `user_isActivated` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `user_userTypeId` tinyint(2) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `userTypes`
--

CREATE TABLE `userTypes` (
  `userType_id` tinyint(2) UNSIGNED NOT NULL,
  `userType_creationDate` datetime NOT NULL,
  `userType_label` varchar(50) NOT NULL,
  `userType_slugName` varchar(55) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `fk_comment_postId` (`comment_postId`);

--
-- Index pour la table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`contact_id`);

--
-- Index pour la table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `fk_image_postId` (`image_postId`) USING BTREE,
  ADD KEY `fk_image_creatorId` (`image_creatorId`) USING BTREE;

--
-- Index pour la table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `fk_post_userId` (`post_userId`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `fk_user_userTypeId` (`user_userTypeId`);

--
-- Index pour la table `userTypes`
--
ALTER TABLE `userTypes`
  ADD PRIMARY KEY (`userType_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `contact_id` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=383;

--
-- AUTO_INCREMENT pour la table `images`
--
ALTER TABLE `images`
  MODIFY `image_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT pour la table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` tinyint(4) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `userTypes`
--
ALTER TABLE `userTypes`
  MODIFY `userType_id` tinyint(2) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `fk_comment_postId` FOREIGN KEY (`comment_postId`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `images`
--
ALTER TABLE `images`
  ADD CONSTRAINT `images_ibfk_1` FOREIGN KEY (`image_postId`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `images_ibfk_2` FOREIGN KEY (`image_creatorId`) REFERENCES `users` (`user_id`) ON DELETE NO ACTION;

--
-- Contraintes pour la table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_post_userId` FOREIGN KEY (`post_userId`) REFERENCES `users` (`user_id`);

--
-- Contraintes pour la table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_userTypeId` FOREIGN KEY (`user_userTypeId`) REFERENCES `userTypes` (`userType_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
