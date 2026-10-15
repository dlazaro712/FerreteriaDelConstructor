-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 28, 2026 at 03:39 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ferreteria_constructor`
--

-- --------------------------------------------------------

--
-- Table structure for table `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `descripcion` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `descripcion`) VALUES
(2, 'Accesorios de Ferretería'),
(1, 'Herramientas Eléctricas'),
(3, 'Limpieza y Presión');

-- --------------------------------------------------------

--
-- Table structure for table `estadodetalle_pedido`
--

CREATE TABLE `estadodetalle_pedido` (
  `id_detalle` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `estadodetalle_pedido`
--

INSERT INTO `estadodetalle_pedido` (`id_detalle`, `id_pedido`, `id_producto`, `cantidad`, `precio`) VALUES
(1, 1, 1, 1, 850.00),
(2, 2, 2, 3, 1100.00),
(3, 3, 3, 5, 550.00),
(4, 4, 4, 7, 1400.00),
(5, 5, 5, 2, 450.00),
(6, 6, 6, 4, 1850.00),
(7, 7, 7, 8, 2200.00),
(8, 8, 8, 6, 320.00),
(9, 9, 9, 1, 180.00),
(10, 10, 10, 2, 95.00);

-- --------------------------------------------------------

--
-- Table structure for table `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `estado` enum('Pendiente','Procesando','Completado','Cancelado') DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_usuario`, `fecha`, `total`, `estado`) VALUES
(1, 2, '2026-09-22 02:43:58', 850.00, 'Completado'),
(2, 3, '2026-09-22 02:43:58', 1100.00, 'Procesando'),
(3, 4, '2026-09-22 02:43:58', 550.00, 'Pendiente'),
(4, 5, '2026-09-22 02:43:58', 1400.00, 'Completado'),
(5, 6, '2026-09-22 02:43:58', 450.00, 'Pendiente'),
(6, 7, '2026-09-22 02:43:58', 1850.00, 'Completado'),
(7, 8, '2026-09-22 02:43:58', 2200.00, 'Procesando'),
(8, 9, '2026-09-22 02:43:58', 320.00, 'Completado'),
(9, 10, '2026-09-22 02:43:58', 275.00, 'Pendiente'),
(10, 11, '2026-09-22 02:43:58', 1610.00, 'Completado'),
(11, 2, '2026-09-26 19:27:23', 2200.00, 'Pendiente'),
(12, 12, '2026-09-26 22:21:07', 1650.00, 'Pendiente'),
(13, 3, '2026-09-27 17:40:23', 220.00, 'Pendiente'),
(14, 3, '2026-09-27 17:40:47', 220.00, 'Cancelado');

-- --------------------------------------------------------

--
-- Table structure for table `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `productos`
--

INSERT INTO `productos` (`id_producto`, `id_categoria`, `nombre`, `descripcion`, `precio`, `cantidad`, `imagen`, `estado`) VALUES
(1, 1, 'Taladro Atornillador Inalámbrico', 'Taladro percutor a batería de litio con torque ajustable.', 850.00, 13, 'Taladro-Atornillador-Inalambrico.png', 1),
(2, 1, 'Sierra Circular 7-1/4\"', 'Sierra eléctrica de alta potencia para cortes precisos en madera.', 1100.00, 10, 'Sierra-Circular-7-1-4.png', 1),
(3, 1, 'Amoladora Angular 4-1/2\"', 'Esmeriladora ideal para desbaste y corte de metal.', 550.00, 20, 'Amoladora-Angular-4-1-2.png', 1),
(4, 1, 'Rotomartillo SDS Plus', 'Rotomartillo perforador y demoledor de uso rudo.', 1400.00, 8, 'Rotomartillo-Sds-Plus.png', 1),
(5, 1, 'Lijadora Orbital', 'Lijadora compacta para acabados finos en superficies de madera.', 450.00, 12, 'Lijadora-Orbital.png', 1),
(6, 3, 'Hidrolavadora a Presión', 'Limpiadora de alta presión ideal for limpieza profunda exteriores.', 1850.00, 6, 'Hidrolavadora-a-Presión.png', 1),
(7, 3, 'Compresor de Aire 25L', 'Compresor portátil de aire comprimido para herramientas neumáticas.', 2200.00, 5, 'Compresor-de-Aire-25L.png', 1),
(8, 2, 'Caja de Herramientas Organizador', 'Organizador de plástico reforzado con charola interior.', 320.00, 25, 'Caja-de-Herramientas-Organizador.png', 1),
(9, 2, 'Juego de Brocas Mixtas (16 pzs)', 'Brocas para concreto, madera y metal de alta durabilidad.', 180.00, 30, 'Juego-de-Brocas-Mixtas-16-pzs.png', 1),
(10, 2, 'Discos de Corte Abrasivos (Pack 5)', 'Discos de corte delgado para metal y acero inoxidable.', 95.00, 50, 'Discos-de-Corte-Abrasivos-Pack-5.png', 1),
(11, 2, 'Puntas para Atornillador (Set)', 'Puntas magnéticas de acero al cromo vanadio.', 120.00, 40, 'Puntas-para-Atornillador-Set.png', 1),
(12, 2, 'Juego de Sierras Copo (Holesaws)', 'Set de sierras copo bimetálicas para madera y paneles.', 290.00, 18, 'Juego-de-Sierras-Copo-Holesaws.png', 1),
(13, 2, 'Producto novedoso', 'Accesorios', 2000.00, 5, 'f4ef1dafb1931949a623482d746a8ee7.jpeg', 1),
(14, 1, 'Herramientas', 'Herramientas para el constructor', 1200.00, 7, 'default.jpg', 0),
(15, 1, 'Herramientas', 'Herramientas para el constructor', 1200.00, 7, 'default.jpg', 0);

-- --------------------------------------------------------

--
-- Table structure for table `resenas`
--

CREATE TABLE `resenas` (
  `id_resena` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `calificacion` tinyint(4) DEFAULT NULL CHECK (`calificacion` between 1 and 5),
  `comentario` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `resenas`
--

INSERT INTO `resenas` (`id_resena`, `id_usuario`, `id_producto`, `calificacion`, `comentario`, `fecha`) VALUES
(1, 2, 1, 5, 'Excelente taladro, la batería dura bastante y tiene muy buen torque para trabajos pesados.', '2026-09-22 02:43:58'),
(2, 3, 2, 4, 'La sierra corta muy bien la madera, aunque el manual podría ser más claro.', '2026-09-22 02:43:58'),
(3, 4, 3, 5, 'La amoladora es ligera y fácil de manipular. Muy recomendada para herrería.', '2026-09-22 02:43:58'),
(4, 5, 4, 5, 'Potencia impresionante para romper concreto, superó mis expectativas.', '2026-09-22 02:43:58'),
(5, 6, 5, 4, 'Deja los acabados en madera muy lisos y sin vibraciones molestas.', '2026-09-22 02:43:58'),
(6, 7, 6, 5, 'La presión del agua es perfecta para lavar patios y vehículos rápidamente.', '2026-09-22 02:43:58'),
(7, 8, 7, 4, 'Buen compresor, carga rápido el tanque y no hace tanto ruido como otros.', '2026-09-22 02:43:58'),
(8, 9, 8, 5, 'El material de la caja es muy resistente y tiene buen espacio organizador.', '2026-09-22 02:43:58'),
(9, 10, 9, 3, 'Las brocas de metal son buenas, pero las de concreto se desgastan un poco rápido.', '2026-09-22 02:43:58'),
(10, 11, 10, 5, 'Los discos de corte duran bastante y no se rompen con facilidad.', '2026-09-22 02:43:58'),
(11, 2, 7, 5, 'BUen producto', '2026-09-26 03:29:37'),
(12, 3, 4, 4, 'El equipo funciona como se esperaba.', '2026-09-26 04:14:08'),
(13, 4, 1, 5, 'El taladro esta funcionando correctamente.', '2026-09-26 04:21:24'),
(14, 5, 6, 5, 'La hidrolavadora funciona muy bien.', '2026-09-26 04:30:00'),
(15, 6, 11, 5, 'Excelente producto', '2026-09-26 04:37:33');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `direccion` text NOT NULL,
  `tipo_usuario` enum('cliente','admin') DEFAULT 'cliente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `correo`, `password`, `telefono`, `direccion`, `tipo_usuario`) VALUES
(1, 'Douglas', 'Administrador', 'admin@ferreteria.com', '$2y$10$xO1mFbXIzDDJvvPgazRLQO6/aME5f1Baioo5Zxsg98qNNg0ZdHvpG', '5551-2345', 'Zona 1, Ciudad de Guatemala', 'admin'),
(2, 'Juan', 'Gómez', 'juan.gomez@gmail.com', '$2y$10$Yx1ofwx/cw97/EeJ5L77lumpih0rOu/JyoQ3ri1t0uvDzRjAfdycy', '4123-8901', 'Zona 4, Escuintla', 'cliente'),
(3, 'María', 'Rodríguez', 'maria.rodriguez@yahoo.com', '$2y$10$Yx1ofwx/cw97/EeJ5L77lumpih0rOu/JyoQ3ri1t0uvDzRjAfdycy', '5234-1122', 'Zona 2, Mixco', 'cliente'),
(4, 'Luis', 'Martínez', 'luis.martinez@hotmail.com', '$2y$10$Yx1ofwx/cw97/EeJ5L77lumpih0rOu/JyoQ3ri1t0uvDzRjAfdycy', '3145-7788', 'Zona 9, Ciudad de Guatemala', 'cliente'),
(5, 'Ana', 'López', 'ana.lopez@gmail.com', '$2y$10$Yx1ofwx/cw97/EeJ5L77lumpih0rOu/JyoQ3ri1t0uvDzRjAfdycy', '4789-2233', 'San Lucas Sacatepéquez', 'cliente'),
(6, 'Pedro', 'Castillo', 'pedro.castillo@outlook.com', '$2y$10$Yx1ofwx/cw97/EeJ5L77lumpih0rOu/JyoQ3ri1t0uvDzRjAfdycy', '5987-3344', 'Antigua Guatemala', 'cliente'),
(7, 'Lucía', 'Morales', 'lucia.morales@gmail.com', '$2y$10$Yx1ofwx/cw97/EeJ5L77lumpih0rOu/JyoQ3ri1t0uvDzRjAfdycy', '3322-1144', 'Villa Nueva', 'cliente'),
(8, 'Jorge', 'Hernández', 'jorge.hernandez@yahoo.com', '$2y$10$Yx1ofwx/cw97/EeJ5L77lumpih0rOu/JyoQ3ri1t0uvDzRjAfdycy', '4455-6677', 'Zona 12, Ciudad de Guatemala', 'cliente'),
(9, 'Sofía', 'Pérez', 'sofia.perez@gmail.com', '$2y$10$Yx1ofwx/cw97/EeJ5L77lumpih0rOu/JyoQ3ri1t0uvDzRjAfdycy', '5111-9988', 'Amatitlán', 'cliente'),
(10, 'David', 'Ramírez', 'david.ramirez@hotmail.com', '$2y$10$Yx1ofwx/cw97/EeJ5L77lumpih0rOu/JyoQ3ri1t0uvDzRjAfdycy', '4888-2211', 'Puerto San José, Escuintla', 'cliente'),
(11, 'Elena', 'Vargas', 'elena.vargas@gmail.com', '$2y$10$Yx1ofwx/cw97/EeJ5L77lumpih0rOu/JyoQ3ri1t0uvDzRjAfdycy', '3555-4422', 'Santa Lucía Cotzumalguapa', 'cliente'),
(12, 'Tino', 'Yasquez', 'ty@gmail.com', '$2y$10$V21p8JXYaHsD1xViPn32dObNiCBK9FRoHnNMx4nvoiUQoIJoPlwIq', '55558888', 'Ciudad', 'cliente'),
(13, 'Bpo', 'COmp', 'bc@company.com', '$2y$10$IRYpKWxIY/fPH.EIe5uy/Oo4rFSHKaLte0kAsDQQiocSqqDhNQIj6', '55557777', 'CIudad', 'cliente');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id_wishlist` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id_wishlist`, `id_usuario`, `id_producto`) VALUES
(1, 2, 1),
(2, 3, 3),
(3, 4, 4),
(4, 5, 6),
(5, 6, 7),
(6, 7, 8),
(7, 8, 9),
(8, 9, 10),
(9, 10, 11),
(10, 11, 12);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD KEY `idx_categoria_desc` (`descripcion`);

--
-- Indexes for table `estadodetalle_pedido`
--
ALTER TABLE `estadodetalle_pedido`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `fk_detalle_producto` (`id_producto`),
  ADD KEY `idx_det_pedido` (`id_pedido`);

--
-- Indexes for table `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `idx_ped_usuario` (`id_usuario`);

--
-- Indexes for table `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `idx_prod_cat` (`id_categoria`),
  ADD KEY `idx_prod_nombre` (`nombre`);

--
-- Indexes for table `resenas`
--
ALTER TABLE `resenas`
  ADD PRIMARY KEY (`id_resena`),
  ADD KEY `fk_resena_usuario` (`id_usuario`),
  ADD KEY `idx_res_producto` (`id_producto`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `idx_correo` (`correo`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id_wishlist`),
  ADD UNIQUE KEY `uq_usuario_producto` (`id_usuario`,`id_producto`),
  ADD KEY `fk_wishlist_producto` (`id_producto`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `estadodetalle_pedido`
--
ALTER TABLE `estadodetalle_pedido`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `resenas`
--
ALTER TABLE `resenas`
  MODIFY `id_resena` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id_wishlist` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `estadodetalle_pedido`
--
ALTER TABLE `estadodetalle_pedido`
  ADD CONSTRAINT `fk_detalle_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;

--
-- Constraints for table `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedido_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Constraints for table `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_producto_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON DELETE CASCADE;

--
-- Constraints for table `resenas`
--
ALTER TABLE `resenas`
  ADD CONSTRAINT `fk_resena_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_resena_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `fk_wishlist_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_wishlist_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
