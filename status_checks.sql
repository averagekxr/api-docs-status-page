-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 04 Şub 2025, 01:52:27
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `apistat`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `status_checks`
--

CREATE TABLE `status_checks` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `status` enum('operational','degraded','outage') NOT NULL,
  `response_time` decimal(8,2) NOT NULL,
  `checked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `status_checks`
--

INSERT INTO `status_checks` (`id`, `service_id`, `status`, `response_time`, `checked_at`) VALUES
(1, 1, 'outage', 1.06, '2025-02-03 23:57:05'),
(2, 2, 'operational', 0.09, '2025-02-03 23:57:05'),

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `status_checks`
--
ALTER TABLE `status_checks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `status_checks`
--
ALTER TABLE `status_checks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=361;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `status_checks`
--
ALTER TABLE `status_checks`
  ADD CONSTRAINT `status_checks_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
