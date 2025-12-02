-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 19, 2025 at 03:43 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `movie`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `booking_date` datetime NOT NULL,
  `slot_id` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `movie_id`, `quantity`, `booking_date`, `slot_id`, `status`, `price`) VALUES
(74, 1, 23, 3, '2025-04-19 14:57:34', 24, 'pending', 0.00),
(75, 1, 19, 3, '2025-04-19 15:39:31', 30, 'pending', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `movies`
--

CREATE TABLE `movies` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` mediumtext NOT NULL,
  `release_date` year(4) NOT NULL,
  `photo` varchar(500) NOT NULL,
  `genre` varchar(50) DEFAULT 'Uncategorized',
  `duration` int(11) DEFAULT 120,
  `avg_rating` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`id`, `name`, `description`, `release_date`, `photo`, `genre`, `duration`, `avg_rating`) VALUES
(19, 'Pushpa 2: The Rule', 'A smuggling kingpin faces off against a vengeful rival while manipulating politics, making big deals, and navigating tense confrontations. A public apology leads to a dramatic showdown, ending with challenge.', '2024', 'uploads/67def461ae65d.jpg', 'action', 120, 3),
(21, 'Captain America: Brave New World', 'After meeting with newly elected U.S. President Thaddeus Ross, Sam finds himself in the middle of an international incident. He must discover the reason behind a nefarious global plot before the true mastermind has the entire world seeing red.', '2025', 'uploads/67de59ec36b0d.jpg', 'action', 120, 3),
(22, 'Venom: The Last Dance', 'Eddie and Venom are on the run. Hunted by both of their worlds and with the net closing in, the duo are forced into a devastating decision that will bring the curtains down on Venom and Eddie’s last dance', '2024', 'uploads/67de5a17ae8d3.jpg', 'action', 120, NULL),
(23, 'The Shawshank Redemption', 'Framed in the 1940s for the double murder of his wife and her lover, upstanding banker Andy Dufresne begins a new life at the Shawshank prison, where he puts his accounting skills to work for an amoral warden. During his long stretch in prison, Dufresne comes to be admired by the other inmates — including an older prisoner named Red — for his integrity and unquenchable sense of hope.', '1994', 'uploads/67de5a36cff4f.jpg', 'drama', 120, 5),
(24, 'Novocaine', 'When the girl of his dreams is kidnapped, a man incapable of feeling physical pain turns his rare condition into an unexpected advantage in the fight to rescue her.', '2025', 'uploads/67def58c7a412.jpg', 'thriller', 120, NULL),
(25, 'How to Lose a Guy in 10 Days', 'Benjamin Barry is an advertising executive and ladies\' man who, to win a big campaign, bets that he can make a woman fall in love with him in 10 days.', '2003', 'uploads/67e5e4545cc11.jpg', 'romance', 120, NULL),
(26, 'Me Before You', 'A girl in a small town forms an unlikely bond with a recently-paralyzed man she\'s taking care of.', '2016', 'uploads/67e5e4dbcd0b1.jpg', 'romance', 120, NULL),
(27, '50 First Dates', 'Henry Roth is a man afraid of commitment until he meets the beautiful Lucy. They hit it off and Henry think he\'s finally found the girl of his dreams until discovering she has short-term memory loss and forgets him the next day.', '2004', 'uploads/67e5e57a0a3dc.jpg', 'romance', 120, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `movie_comments`
--

CREATE TABLE `movie_comments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `movie_slots`
--

CREATE TABLE `movie_slots` (
  `id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `slot_date` date NOT NULL,
  `slot_time` time NOT NULL,
  `available` tinyint(1) DEFAULT 1,
  `total_seats` int(11) NOT NULL,
  `booked_seats` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movie_slots`
--

INSERT INTO `movie_slots` (`id`, `movie_id`, `slot_date`, `slot_time`, `available`, `total_seats`, `booked_seats`) VALUES
(24, 23, '2025-04-27', '09:00:00', 1, 0, 0),
(25, 27, '2025-04-28', '09:00:00', 1, 0, 0),
(26, 21, '2025-04-29', '09:00:00', 1, 0, 0),
(27, 25, '2025-04-30', '09:00:00', 1, 0, 0),
(28, 26, '2025-05-01', '09:00:00', 1, 0, 0),
(29, 24, '2025-05-02', '09:00:00', 1, 0, 0),
(30, 19, '2025-05-04', '09:00:00', 1, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `user_id`, `movie_id`, `rating`, `created_at`) VALUES
(1, 1, 21, 5, '2025-03-22 15:37:10'),
(2, 1, 22, 4, '2025-03-22 15:42:48'),
(3, 1, 23, 5, '2025-03-22 15:48:13'),
(4, 1, 19, 5, '2025-03-22 15:48:34'),
(5, 2, 22, 1, '2025-03-22 17:17:31'),
(6, 2, 19, 1, '2025-03-22 17:21:40'),
(7, 2, 21, 1, '2025-03-24 06:48:35');

-- --------------------------------------------------------

--
-- Table structure for table `seat_bookings`
--

CREATE TABLE `seat_bookings` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `seat_id` int(11) NOT NULL,
  `slot_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seat_bookings`
--

INSERT INTO `seat_bookings` (`id`, `booking_id`, `seat_id`, `slot_id`, `created_at`) VALUES
(78, 74, 654, 24, '2025-04-19 12:58:04'),
(79, 74, 655, 24, '2025-04-19 12:58:04'),
(80, 74, 656, 24, '2025-04-19 12:58:04'),
(81, 75, 653, 30, '2025-04-19 13:39:51'),
(82, 75, 654, 30, '2025-04-19 13:39:51'),
(83, 75, 655, 30, '2025-04-19 13:39:51');

-- --------------------------------------------------------

--
-- Table structure for table `seat_types`
--

CREATE TABLE `seat_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seat_types`
--

INSERT INTO `seat_types` (`id`, `name`, `price`, `created_at`) VALUES
(1, 'Regular', 300.00, '2025-03-22 06:21:29'),
(2, 'VIP', 450.00, '2025-03-22 06:21:29');

-- --------------------------------------------------------

--
-- Table structure for table `theater_seats`
--

CREATE TABLE `theater_seats` (
  `id` int(11) NOT NULL,
  `row_name` char(1) NOT NULL,
  `seat_number` int(11) NOT NULL,
  `seat_type_id` int(11) NOT NULL,
  `status` enum('available','maintenance') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `theater_seats`
--

INSERT INTO `theater_seats` (`id`, `row_name`, `seat_number`, `seat_type_id`, `status`, `created_at`) VALUES
(641, 'A', 1, 1, 'available', '2025-04-19 12:55:44'),
(642, 'A', 2, 1, 'available', '2025-04-19 12:55:44'),
(643, 'A', 3, 1, 'available', '2025-04-19 12:55:44'),
(644, 'A', 4, 1, 'available', '2025-04-19 12:55:44'),
(645, 'A', 5, 1, 'available', '2025-04-19 12:55:44'),
(646, 'A', 6, 2, 'available', '2025-04-19 12:55:44'),
(647, 'A', 7, 2, 'available', '2025-04-19 12:55:44'),
(648, 'A', 8, 2, 'available', '2025-04-19 12:55:44'),
(649, 'A', 9, 2, 'available', '2025-04-19 12:55:44'),
(650, 'A', 10, 2, 'available', '2025-04-19 12:55:44'),
(651, 'B', 1, 1, 'available', '2025-04-19 12:55:44'),
(652, 'B', 2, 1, 'available', '2025-04-19 12:55:44'),
(653, 'B', 3, 1, 'available', '2025-04-19 12:55:44'),
(654, 'B', 4, 1, 'available', '2025-04-19 12:55:44'),
(655, 'B', 5, 1, 'available', '2025-04-19 12:55:44'),
(656, 'B', 6, 2, 'available', '2025-04-19 12:55:44'),
(657, 'B', 7, 2, 'available', '2025-04-19 12:55:44'),
(658, 'B', 8, 2, 'available', '2025-04-19 12:55:44'),
(659, 'B', 9, 2, 'available', '2025-04-19 12:55:44'),
(660, 'B', 10, 2, 'available', '2025-04-19 12:55:44'),
(661, 'C', 1, 1, 'available', '2025-04-19 12:55:44'),
(662, 'C', 2, 1, 'available', '2025-04-19 12:55:44'),
(663, 'C', 3, 1, 'available', '2025-04-19 12:55:44'),
(664, 'C', 4, 1, 'available', '2025-04-19 12:55:44'),
(665, 'C', 5, 1, 'available', '2025-04-19 12:55:44'),
(666, 'C', 6, 1, 'available', '2025-04-19 12:55:44'),
(667, 'C', 7, 1, 'available', '2025-04-19 12:55:44'),
(668, 'C', 8, 1, 'available', '2025-04-19 12:55:44'),
(669, 'C', 9, 1, 'available', '2025-04-19 12:55:44'),
(670, 'C', 10, 1, 'available', '2025-04-19 12:55:44'),
(671, 'D', 1, 1, 'available', '2025-04-19 12:55:44'),
(672, 'D', 2, 1, 'available', '2025-04-19 12:55:44'),
(673, 'D', 3, 1, 'available', '2025-04-19 12:55:44'),
(674, 'D', 4, 1, 'available', '2025-04-19 12:55:44'),
(675, 'D', 5, 1, 'available', '2025-04-19 12:55:44'),
(676, 'D', 6, 1, 'available', '2025-04-19 12:55:44'),
(677, 'D', 7, 1, 'available', '2025-04-19 12:55:44'),
(678, 'D', 8, 1, 'available', '2025-04-19 12:55:44'),
(679, 'D', 9, 1, 'available', '2025-04-19 12:55:44'),
(680, 'D', 10, 1, 'available', '2025-04-19 12:55:44'),
(681, 'E', 1, 1, 'available', '2025-04-19 12:55:44'),
(682, 'E', 2, 1, 'available', '2025-04-19 12:55:44'),
(683, 'E', 3, 1, 'available', '2025-04-19 12:55:44'),
(684, 'E', 4, 1, 'available', '2025-04-19 12:55:44'),
(685, 'E', 5, 1, 'available', '2025-04-19 12:55:44'),
(686, 'E', 6, 1, 'available', '2025-04-19 12:55:44'),
(687, 'E', 7, 1, 'available', '2025-04-19 12:55:44'),
(688, 'E', 8, 1, 'available', '2025-04-19 12:55:44'),
(689, 'E', 9, 1, 'available', '2025-04-19 12:55:44'),
(690, 'E', 10, 1, 'available', '2025-04-19 12:55:44'),
(691, 'F', 1, 1, 'available', '2025-04-19 12:55:44'),
(692, 'F', 2, 1, 'available', '2025-04-19 12:55:44'),
(693, 'F', 3, 1, 'available', '2025-04-19 12:55:44'),
(694, 'F', 4, 1, 'available', '2025-04-19 12:55:44'),
(695, 'F', 5, 1, 'available', '2025-04-19 12:55:44'),
(696, 'F', 6, 1, 'available', '2025-04-19 12:55:44'),
(697, 'F', 7, 1, 'available', '2025-04-19 12:55:44'),
(698, 'F', 8, 1, 'available', '2025-04-19 12:55:44'),
(699, 'F', 9, 1, 'available', '2025-04-19 12:55:44'),
(700, 'F', 10, 1, 'available', '2025-04-19 12:55:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(100) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `role`) VALUES
(1, 'prasanna karanjit', 'prasannakaranjit@gmail.com', '$2y$10$pjUNdWiVL6y6ZB8UB68x5.sJ5onQxiUGZdGGkigAtl2/hSY8Fa3YC', '2025-02-19 11:11:18', 'user'),
(2, 'abc', 'abc@gmail.com', '$2y$10$uJ4j3yj28csxBDRih7B5guWrDXy2.TExf/Z5lCRb7MxiTsb1DNKHG', '2025-02-19 11:39:50', 'user'),
(3, 'admin', 'admin@gmail.com', '$2y$10$e56I0SMqcy0TSEjhRHgwmOZmIGn2SmRaTr.u3cLuL5yx0XABkDkEO', '2025-02-19 12:20:23', 'admin'),
(6, 'bcd', 'bcd@gmail.com', '$2y$10$Na3d.OSxk9LxtjKQuiMmL.9Lh0SGeFdAdYH5C0H/KPlxXEG.iJQBa', '2025-03-24 06:44:21', 'user'),
(10, '123@', '123@123.com', '$2y$10$TokxUKmcsU1eJkVKtiyFxeoKGW2nTIUEic647t2iNh5N64/lDOA8i', '2025-03-28 02:59:18', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `movie_id` (`movie_id`);

--
-- Indexes for table `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `movie_comments`
--
ALTER TABLE `movie_comments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `movie_slots`
--
ALTER TABLE `movie_slots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movie_id` (`movie_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`movie_id`),
  ADD KEY `movie_id` (`movie_id`);

--
-- Indexes for table `seat_bookings`
--
ALTER TABLE `seat_bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_seat_booking` (`seat_id`,`slot_id`),
  ADD KEY `slot_id` (`slot_id`),
  ADD KEY `seat_bookings_ibfk_1` (`booking_id`);

--
-- Indexes for table `seat_types`
--
ALTER TABLE `seat_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `theater_seats`
--
ALTER TABLE `theater_seats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_seat` (`row_name`,`seat_number`),
  ADD KEY `seat_type_id` (`seat_type_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `movie_comments`
--
ALTER TABLE `movie_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `movie_slots`
--
ALTER TABLE `movie_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `seat_bookings`
--
ALTER TABLE `seat_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `seat_types`
--
ALTER TABLE `seat_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `theater_seats`
--
ALTER TABLE `theater_seats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=701;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `movie_comments`
--
ALTER TABLE `movie_comments`
  ADD CONSTRAINT `movie_comments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `movie_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `movie_slots`
--
ALTER TABLE `movie_slots`
  ADD CONSTRAINT `movie_slots_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`);

--
-- Constraints for table `seat_bookings`
--
ALTER TABLE `seat_bookings`
  ADD CONSTRAINT `seat_bookings_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `seat_bookings_ibfk_2` FOREIGN KEY (`seat_id`) REFERENCES `theater_seats` (`id`),
  ADD CONSTRAINT `seat_bookings_ibfk_3` FOREIGN KEY (`slot_id`) REFERENCES `movie_slots` (`id`);

--
-- Constraints for table `theater_seats`
--
ALTER TABLE `theater_seats`
  ADD CONSTRAINT `theater_seats_ibfk_1` FOREIGN KEY (`seat_type_id`) REFERENCES `seat_types` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
