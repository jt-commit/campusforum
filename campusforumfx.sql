-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 28/01/2026 às 15:29
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `campusforumfx`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `created_at`) VALUES
(1, 10, 3, 'Tenho dúvida se ficou muito espelhado', '2026-01-28 13:40:30'),
(3, 10, 2, 'eu prefiro os do macOS', '2026-01-28 13:46:14');

-- --------------------------------------------------------

--
-- Estrutura para tabela `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `title`, `content`, `image`, `created_at`) VALUES
(8, 2, 'Por do sol?', 'Vocês gostaram?', 'post_1769600095_34f88fe6.jpg', '2026-01-28 11:34:55'),
(9, 1, 'Alguém pode me ajudar com esse código?', 'Lorem ipsum dolor sit amet. Ut placeat nihil est sunt quisquam ab quis consequatur. Vel eius minima sit totam sunt est vero saepe. Ad sequi soluta quo accusantium ducimus sit atque ratione cum omnis possimus rem quod magni quo mollitia repellat!\r\n\r\nAut aliquid accusamus et distinctio nihil vel quibusdam commodi et debitis assumenda et sunt veniam qui nobis sunt. Et ullam rerum id doloremque ipsa sit reprehenderit aliquam qui dolores officiis sit impedit quia et tempora galisum? In internos aspernatur eos galisum quia rem quibusdam omnis et velit voluptas At omnis voluptas At nihil voluptate. Ea magnam molestias eum voluptatem officia eos laudantium quibusdam?', 'post_1769601437_fa1c4ec4.png', '2026-01-28 11:57:17'),
(10, 3, 'Esses ícones estão bons?', 'A Interação Humano-Computador (IHC) é uma área interdisciplinar que estuda a comunicação entre usuários e sistemas computacionais. Seu foco principal está na criação de interfaces que sejam eficientes, seguras, acessíveis e fáceis de usar. A IHC integra conhecimentos de ciência da computação, psicologia cognitiva, design, ergonomia e sociologia.', 'post_1769601543_00637e58.png', '2026-01-28 11:59:03'),
(13, 3, 'Gostaria de compartilhar uma dúvida:', 'Essa imagem tem qual conceito de design?', 'post_1769610494_055d5244.jpg', '2026-01-28 14:28:14');

-- --------------------------------------------------------

--
-- Estrutura para tabela `saved_posts`
--

CREATE TABLE `saved_posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'caros', 'carlos@ibm', '$2y$10$.aOJod90VsWybqCgeTa9SewP4N/PvGfHHU3a.q38Lji.xTm.rQwVq', '2026-01-26 14:07:36'),
(2, 'Kauê', 'kaue@google', '$2y$10$mzmGgXw9aweGwfG8F.fPdORFHM98MCCUVbjbJ0SU2LZ22sIV.ukpC', '2026-01-26 14:33:35'),
(3, 'jack', 'jack@ibm', '$2y$10$JDI7xyzHh6bIXSj/BdCpSu4.26VfyKj3bBaia.IBj6Z0IYfdDHEJK', '2026-01-28 11:58:13');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post_id` (`post_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Índices de tabela `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_posts_user` (`user_id`),
  ADD KEY `idx_posts_title` (`title`),
  ADD KEY `idx_posts_content` (`content`(768)),
  ADD KEY `idx_posts_created` (`created_at`);

--
-- Índices de tabela `saved_posts`
--
ALTER TABLE `saved_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_post` (`user_id`,`post_id`),
  ADD KEY `idx_saved_user` (`user_id`),
  ADD KEY `idx_saved_post` (`post_id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `saved_posts`
--
ALTER TABLE `saved_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `saved_posts`
--
ALTER TABLE `saved_posts`
  ADD CONSTRAINT `fk_saved_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_saved_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
