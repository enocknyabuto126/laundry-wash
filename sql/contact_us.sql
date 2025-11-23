-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 24, 2023 at 03:55 AM
-- Server version: 5.7.36
-- PHP Version: 7.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `contact_us`
--

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(30) NOT NULL COMMENT 'Full name of user',
  `email` varchar(30) NOT NULL COMMENT 'email address of user',
  `message` varchar(2000) NOT NULL COMMENT 'query posted b user',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time and date the comment was raised',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `fullname`, `email`, `message`, `created`) VALUES
(1, 'Fritz Haber ', 'fritzxhaber1@gmail.com', 'dfhj', '2023-03-03 09:14:11'),
(4, 'Owen Mworia', 'owenmworia60@gmail.com', 'Hello! I just stumbled upon your website and I must say, I\'m really impressed with the content and layout. The articles are informative and engaging, and the design is visually appealing and easy to navigate. Keep up the great work!', '2023-03-03 09:29:33'),
(9, 'Fritz', 'kelvinmakory@yahoo.com', 'hello', '2023-03-10 07:42:23'),
(10, 'Eva mwangi', 'Nyawoira@123.com', 'Amazing stuff', '2023-03-23 15:42:21'),
(11, 'Derek Mbogo', 'rizzmbogo@gmail.com', 'the experience was good', '2023-03-23 17:29:28'),
(12, 'Richard Maina', 'rmaina@gmail.com', 'Wow, your laundry services are a lifesaver! I\'ve never seen my clothes so clean and fresh before. The convenience of being able to drop off my dirty laundry and pick it up perfectly folded and smelling amazing is unbeatable. Keep up the great work', '2023-04-03 11:30:59'),
(8, 'Cynthia Kingatua', 'ck23@gmail.com', 'I have to say that I was disappointed with my experience on your website. I found the navigation to be confusing and the content to be lackluster. I was hoping to find more detailed information about your products and services, but unfortunately, I didn\'t find what I was looking for. I think there is room for improvement in terms of the user experience and the quality of the content. I hope you take this feedback into consideration and make the necessary changes to enhance your website.', '2023-03-03 09:49:18');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
