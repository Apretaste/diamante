--
-- Structure
--

CREATE TABLE `__diamante_raffle` (
  `id` int(11) NOT NULL,
  `description` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `winner_id` int(11) DEFAULT NULL,
  `inserted` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes
--

ALTER TABLE `__diamante_raffle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `winner_id` (`winner_id`);

ALTER TABLE `__diamante_raffle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;