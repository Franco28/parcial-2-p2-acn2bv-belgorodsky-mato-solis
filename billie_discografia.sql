-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 20-11-2025 a las 23:02:44
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `billie_discografia`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `canciones`
--

CREATE TABLE `canciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `album` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `canciones`
--

INSERT INTO `canciones` (`id`, `titulo`, `album`, `descripcion`, `imagen`, `creado_en`) VALUES
(1, 'bad guy', 'WHEN WE ALL FALL ASLEEP, WHERE DO WE GO?', 'Track icónico del primer álbum de estudio de Billie, con base minimalista y bajo marcado.', 'img/when_we_all_fall_asleep.jpg', '2025-11-20 18:55:39'),
(2, 'bury a friend', 'WHEN WE ALL FALL ASLEEP, WHERE DO WE GO?', 'Tema oscuro y experimental, con producción llena de sonidos poco convencionales.', 'img/when_we_all_fall_asleep.jpg', '2025-11-20 18:55:39'),
(3, 'when the party\'s over', 'WHEN WE ALL FALL ASLEEP, WHERE DO WE GO?', 'Balada íntima y melancólica, centrada en la voz y la armonía vocal.', 'img/when_we_all_fall_asleep.jpg', '2025-11-20 18:55:39'),
(4, 'Happier Than Ever', 'Happier Than Ever', 'Tema que arranca calmo y termina en un clímax potente con guitarras distorsionadas.', 'img/happier_than_ever.jpg', '2025-11-20 18:55:39'),
(5, 'NDA', 'Happier Than Ever', 'Canción con base electrónica oscura y letra sobre la exposición y la privacidad.', 'img/happier_than_ever.jpg', '2025-11-20 18:55:39'),
(6, 'Your Power', 'Happier Than Ever', 'Tema acústico que critica el abuso de poder, con un sonido muy íntimo.', 'img/happier_than_ever.jpg', '2025-11-20 18:55:39'),
(7, 'LUNCH', 'HIT ME HARD AND SOFT', 'Tema con groove bailable y producción moderna, parte del álbum más reciente.', 'img/hit_me_hard_and_soft.jpg', '2025-11-20 18:55:39'),
(8, 'CHIHIRO', 'HIT ME HARD AND SOFT', 'Canción atmosférica y progresiva, con capas de sintetizadores y cambios sutiles.', 'img/hit_me_hard_and_soft.jpg', '2025-11-20 18:55:39'),
(9, 'BIRDS OF A FEATHER', 'HIT ME HARD AND SOFT', 'Balada emocional con estructura pop y detalles de producción muy pulidos.', 'img/hit_me_hard_and_soft.jpg', '2025-11-20 18:55:39');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `canciones`
--
ALTER TABLE `canciones`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `canciones`
--
ALTER TABLE `canciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
