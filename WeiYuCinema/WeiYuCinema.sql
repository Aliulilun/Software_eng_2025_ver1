-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： localhost
-- 產生時間： 2025 年 12 月 04 日 17:47
-- 伺服器版本： 10.4.28-MariaDB
-- PHP 版本： 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `WeiYuCinema`
--

-- --------------------------------------------------------

--
-- 資料表結構 `bookingRecord`
--

CREATE TABLE `bookingRecord` (
  `orderNumber` varchar(30) NOT NULL,
  `memberId` varchar(10) NOT NULL,
  `showingId` varchar(6) NOT NULL,
  `time` varchar(30) NOT NULL,
  `seat` varchar(100) NOT NULL,
  `chooseMeal` text DEFAULT NULL,
  `ticketTypeId` int(11) NOT NULL,
  `ticketNums` int(11) NOT NULL,
  `orderStatusId` int(11) NOT NULL,
  `totalPrice` int(11) NOT NULL,
  `getTicketNum` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `bookingRecord`
--

INSERT INTO `bookingRecord` (`orderNumber`, `memberId`, `showingId`, `time`, `seat`, `chooseMeal`, `ticketTypeId`, `ticketNums`, `orderStatusId`, `totalPrice`, `getTicketNum`) VALUES
('ORD202412010001', 'M0001', 'S00101', '2025-12-04 10:30:00', 'A1,A2', '{\"M001\":{\"name\":\"大爆米花\",\"price\":150,\"quantity\":1,\"subtotal\":150},\"M003\":{\"name\":\"可口可樂(大)\",\"price\":70,\"quantity\":2,\"subtotal\":140}}', 0, 2, 1, 590, 123456),
('ORD202412010002', 'M0002', 'S00102', '2025-12-04 14:20:00', 'C5,C6,C7', '{\"M005\":{\"name\":\"雙人套餐A\",\"price\":299,\"quantity\":1,\"subtotal\":299}}', 0, 3, 1, 899, 234567),
('ORD202412010003', 'M0003', 'S00201', '2025-12-04 16:45:00', 'B3,B4', '', 0, 2, 1, 400, 345678);

-- --------------------------------------------------------

--
-- 資料表結構 `cinema`
--

CREATE TABLE `cinema` (
  `cinemaId` varchar(2) NOT NULL,
  `cinemaAddress` varchar(80) NOT NULL,
  `cinemaName` varchar(20) NOT NULL,
  `cinemaTele` varchar(15) NOT NULL,
  `cinemaImg` varchar(50) NOT NULL,
  `cinemaBusTwo` varchar(300) NOT NULL,
  `cinemaInfo` varchar(500) NOT NULL,
  `googleMap` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `cinema`
--

INSERT INTO `cinema` (`cinemaId`, `cinemaAddress`, `cinemaName`, `cinemaTele`, `cinemaImg`, `cinemaBusTwo`, `cinemaInfo`, `googleMap`) VALUES
('01', '台北市信義區松壽路20號', '台北信義威秀影城', '02-8780-5566', 'cinema01.jpg', '捷運象山站步行10分鐘', '位於信義商圈核心的大型影城。', 'https://maps.google.com/xxx'),
('02', '新北市板橋區新站路28號', '板橋大遠百威秀影城', '02-2952-6789', 'cinema02.jpg', '板橋車站步行5分鐘', '交通便利，影廳數量多。', 'https://maps.google.com/yyy'),
('03', '新北市林口區文化三路一段356號', '林口威秀影城', '02-2608-1234', 'linkou.jpg', '公車直達三井outlet', '近三井outlet人氣影城。', 'https://maps.google.com/zzz'),
('04', '台中市西屯區台灣大道三段301號', '台中大遠百威秀影城', '04-2258-1345', 'taichung.jpg', '台中大遠百8樓', '台中人氣影城。', 'https://maps.google.com/abc'),
('05', '高雄市前鎮區中安路1號', '高雄大遠百威秀影城', '07-334-5566', 'kaohsiung.jpg', '捷運三多商圈站步行5分鐘', '南部規模最大威秀之一。', 'https://maps.google.com/def');

-- --------------------------------------------------------

--
-- 資料表結構 `grade`
--

CREATE TABLE `grade` (
  `gradeId` int(11) NOT NULL,
  `gradeName` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `grade`
--

INSERT INTO `grade` (`gradeId`, `gradeName`) VALUES
(1, '普遍級'),
(2, '保護級'),
(3, '輔12'),
(4, '輔15'),
(5, '限制級');

-- --------------------------------------------------------

--
-- 資料表結構 `meals`
--

CREATE TABLE `meals` (
  `mealsId` varchar(10) NOT NULL,
  `mealsName` varchar(20) NOT NULL,
  `mealsPrice` varchar(10) NOT NULL,
  `mealsTypeId` varchar(10) NOT NULL,
  `mealsPhoto` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `meals`
--

INSERT INTO `meals` (`mealsId`, `mealsName`, `mealsPrice`, `mealsTypeId`, `mealsPhoto`) VALUES
('M001', '大爆米花', '150', 'T01', 'pop1.jpg'),
('M002', '中爆米花', '130', 'T01', 'pop2.jpg'),
('M003', '可口可樂(大)', '70', 'T02', 'cola1.jpg'),
('M004', '雪碧(大)', '70', 'T02', 'sprite.jpg'),
('M005', '雙人套餐A', '299', 'T03', 'setA.jpg'),
('M006', '雙人套餐B', '349', 'T03', 'setB.jpg'),
('M007', '小爆米花', '100', 'T01', 'pop3.jpg'),
('M008', '焦糖爆米花', '180', 'T01', 'pop4.jpg'),
('M009', '雪碧(中)', '60', 'T02', 'sprite2.jpg'),
('M010', '柳橙汁', '80', 'T02', 'orange.jpg'),
('M011', '熱狗', '120', 'T03', 'hotdog.jpg'),
('M012', '薯條', '90', 'T03', 'fries.jpg'),
('M013', '單人套餐', '199', 'T03', 'setC.jpg'),
('M014', '情侶套餐', '399', 'T03', 'setD.jpg');

-- --------------------------------------------------------

--
-- 資料表結構 `mealsType`
--

CREATE TABLE `mealsType` (
  `mealsTypeId` varchar(10) NOT NULL,
  `mealsTypeName` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `mealsType`
--

INSERT INTO `mealsType` (`mealsTypeId`, `mealsTypeName`) VALUES
('T01', '爆米花'),
('T02', '飲料'),
('T03', '套餐');

-- --------------------------------------------------------

--
-- 資料表結構 `memberCashCard`
--

CREATE TABLE `memberCashCard` (
  `memberId` varchar(10) NOT NULL,
  `balance` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `memberCashCard`
--

INSERT INTO `memberCashCard` (`memberId`, `balance`) VALUES
('M0001', 1500),
('M0002', 800),
('M0003', 2300),
('M0004', 1200),
('M0005', 99999);

-- --------------------------------------------------------

--
-- 資料表結構 `memberProfile`
--

CREATE TABLE `memberProfile` (
  `memberId` varchar(10) NOT NULL,
  `memberName` varchar(20) NOT NULL,
  `member` varchar(50) NOT NULL COMMENT '電子信箱',
  `memberPwd` varchar(50) NOT NULL,
  `memberPhone` varchar(10) NOT NULL,
  `memberBirth` varchar(10) NOT NULL,
  `memberPayAccount` varchar(14) NOT NULL,
  `memberConfirm` varchar(6) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `memberProfile`
--

INSERT INTO `memberProfile` (`memberId`, `memberName`, `member`, `memberPwd`, `memberPhone`, `memberBirth`, `memberPayAccount`, `memberConfirm`, `role_id`) VALUES
('M0001', '王小明', 'ming123@gmail.com', 'pw1234', '0912345678', '2001-05-01', 'ACC0012345678', 'yes', 0),
('M0002', '陳美麗', 'meili88@gmail.com', 'abc5678', '0922333444', '2000-08-11', 'ACC0099441122', 'yes', 0),
('M0003', '張大偉', 'wei5566@gmail.com', 'xyz7788', '0933555666', '1999-01-22', 'ACC0077889900', 'yes', 0),
('M0004', '林育成', 'yucheng77@gmail.com', 'pass2020', '0988222666', '2002-09-07', 'ACC0033665599', 'yes', 0),
('M0005', '管理員', 'admin@weiyucinema.com', 'admin', '0900000000', '1990-01-01', 'ACC0000000001', 'yes', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `movie`
--

CREATE TABLE `movie` (
  `movieId` int(11) NOT NULL,
  `movieName` varchar(35) NOT NULL,
  `movieTime` varchar(8) NOT NULL,
  `gradeId` int(11) NOT NULL,
  `movieStart` varchar(10) NOT NULL,
  `movieInfo` varchar(1000) NOT NULL,
  `movieTypeId` int(11) NOT NULL,
  `director` varchar(40) NOT NULL,
  `actors` varchar(100) DEFAULT NULL,
  `movieImg` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `movie`
--

INSERT INTO `movie` (`movieId`, `movieName`, `movieTime`, `gradeId`, `movieStart`, `movieInfo`, `movieTypeId`, `director`, `actors`, `movieImg`) VALUES
(101, '沙丘：第二部', '166分鐘', 4, '2024-03-01', '保羅踏上命運旅程，全面對抗哈克南家族。', 1, 'Denis Villeneuve', 'Timothée Chalamet, Zendaya', 'movie101.jpg'),
(102, '可可夜總會', '105分鐘', 1, '2024-01-10', '一段關於家族與音樂的奇幻冒險旅程。', 3, 'Lee Unkrich', 'Anthony Gonzalez', 'movie102.jpg'),
(103, '航海王：紅髮歌姬', '115分鐘', 1, '2024-02-15', '路飛與歌姬烏塔的全新冒險。', 3, '谷口悟朗', '田中真弓, 名塚佳織', 'movie103.jpg'),
(104, '鬼修女2', '110分鐘', 5, '2024-05-13', '邪靈再臨，恐怖全面升級。', 5, 'Michael Chaves', 'Taissa Farmiga', 'nun2.jpg'),
(105, '芭比', '114分鐘', 1, '2024-07-21', '芭比踏入現實世界展開成長冒險。', 4, 'Greta Gerwig', 'Margot Robbie, Ryan Gosling', 'barbie.jpg');

-- --------------------------------------------------------

--
-- 資料表結構 `movieType`
--

CREATE TABLE `movieType` (
  `movieTypeId` int(11) NOT NULL,
  `movieTypeName` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `movieType`
--

INSERT INTO `movieType` (`movieTypeId`, `movieTypeName`) VALUES
(1, '動作'),
(2, '愛情'),
(3, '動畫'),
(4, '劇情'),
(5, '驚悚');

-- --------------------------------------------------------

--
-- 資料表結構 `orderStatus`
--

CREATE TABLE `orderStatus` (
  `orderStatusId` int(11) NOT NULL,
  `orderStatusName` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `orderStatus`
--

INSERT INTO `orderStatus` (`orderStatusId`, `orderStatusName`) VALUES
(1, '已完成'),
(2, '已取消'),
(3, '已退票'),
(4, '處理中');

-- --------------------------------------------------------

--
-- 資料表結構 `playVersion`
--

CREATE TABLE `playVersion` (
  `versionId` int(11) NOT NULL,
  `versionName` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `playVersion`
--

INSERT INTO `playVersion` (`versionId`, `versionName`) VALUES
(1, '數位2D'),
(2, '數位3D'),
(3, 'IMAX'),
(4, 'Dolby Atmos');

-- --------------------------------------------------------

--
-- 資料表結構 `role`
--

CREATE TABLE `role` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `role`
--

INSERT INTO `role` (`role_id`, `role_name`) VALUES
(0, '一般會員'),
(1, '管理者');

-- --------------------------------------------------------

--
-- 資料表結構 `seatCondition`
--

CREATE TABLE `seatCondition` (
  `showingId` varchar(6) NOT NULL,
  `seatNumber` varchar(10) NOT NULL,
  `seatEmpty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `seatCondition`
--

INSERT INTO `seatCondition` (`showingId`, `seatNumber`, `seatEmpty`) VALUES
('S00101', 'A1', 1),
('S00101', 'A10', 1),
('S00101', 'A2', 1),
('S00101', 'A3', 1),
('S00101', 'A4', 1),
('S00101', 'A5', 0),
('S00101', 'A6', 0),
('S00101', 'A7', 1),
('S00101', 'A8', 1),
('S00101', 'A9', 1),
('S00101', 'B1', 1),
('S00101', 'B10', 1),
('S00101', 'B2', 1),
('S00101', 'B3', 1),
('S00101', 'B4', 1),
('S00101', 'B5', 0),
('S00101', 'B6', 0),
('S00101', 'B7', 1),
('S00101', 'B8', 1),
('S00101', 'B9', 1),
('S00101', 'C1', 1),
('S00101', 'C10', 1),
('S00101', 'C2', 1),
('S00101', 'C3', 1),
('S00101', 'C4', 1),
('S00101', 'C5', 1),
('S00101', 'C6', 1),
('S00101', 'C7', 0),
('S00101', 'C8', 0),
('S00101', 'C9', 1),
('S00101', 'D1', 1),
('S00101', 'D10', 1),
('S00101', 'D2', 1),
('S00101', 'D3', 1),
('S00101', 'D4', 1),
('S00101', 'D5', 1),
('S00101', 'D6', 1),
('S00101', 'D7', 1),
('S00101', 'D8', 1),
('S00101', 'D9', 1),
('S00101', 'E1', 1),
('S00101', 'E10', 1),
('S00101', 'E2', 1),
('S00101', 'E3', 1),
('S00101', 'E4', 1),
('S00101', 'E5', 1),
('S00101', 'E6', 1),
('S00101', 'E7', 1),
('S00101', 'E8', 1),
('S00101', 'E9', 1),
('S00102', 'A1', 0),
('S00102', 'A10', 1),
('S00102', 'A2', 0),
('S00102', 'A3', 1),
('S00102', 'A4', 1),
('S00102', 'A5', 1),
('S00102', 'A6', 1),
('S00102', 'A7', 1),
('S00102', 'A8', 1),
('S00102', 'A9', 1),
('S00102', 'B1', 0),
('S00102', 'B10', 1),
('S00102', 'B2', 0),
('S00102', 'B3', 1),
('S00102', 'B4', 1),
('S00102', 'B5', 1),
('S00102', 'B6', 1),
('S00102', 'B7', 1),
('S00102', 'B8', 1),
('S00102', 'B9', 1),
('S00102', 'C1', 1),
('S00102', 'C10', 1),
('S00102', 'C2', 1),
('S00102', 'C3', 1),
('S00102', 'C4', 1),
('S00102', 'C5', 1),
('S00102', 'C6', 1),
('S00102', 'C7', 1),
('S00102', 'C8', 1),
('S00102', 'C9', 1),
('S00102', 'D1', 1),
('S00102', 'D10', 1),
('S00102', 'D2', 1),
('S00102', 'D3', 1),
('S00102', 'D4', 1),
('S00102', 'D5', 0),
('S00102', 'D6', 0),
('S00102', 'D7', 1),
('S00102', 'D8', 1),
('S00102', 'D9', 1),
('S00102', 'E1', 1),
('S00102', 'E10', 1),
('S00102', 'E2', 1),
('S00102', 'E3', 1),
('S00102', 'E4', 1),
('S00102', 'E5', 0),
('S00102', 'E6', 0),
('S00102', 'E7', 1),
('S00102', 'E8', 1),
('S00102', 'E9', 1),
('S00201', 'A1', 1),
('S00201', 'A10', 1),
('S00201', 'A2', 1),
('S00201', 'A3', 1),
('S00201', 'A4', 1),
('S00201', 'A5', 1),
('S00201', 'A6', 1),
('S00201', 'A7', 1),
('S00201', 'A8', 1),
('S00201', 'A9', 1),
('S00201', 'B1', 1),
('S00201', 'B10', 1),
('S00201', 'B2', 1),
('S00201', 'B3', 1),
('S00201', 'B4', 1),
('S00201', 'B5', 1),
('S00201', 'B6', 1),
('S00201', 'B7', 1),
('S00201', 'B8', 1),
('S00201', 'B9', 1),
('S00201', 'C1', 1),
('S00201', 'C10', 1),
('S00201', 'C2', 1),
('S00201', 'C3', 1),
('S00201', 'C4', 1),
('S00201', 'C5', 0),
('S00201', 'C6', 0),
('S00201', 'C7', 0),
('S00201', 'C8', 1),
('S00201', 'C9', 1),
('S00201', 'D1', 1),
('S00201', 'D10', 1),
('S00201', 'D2', 1),
('S00201', 'D3', 1),
('S00201', 'D4', 1),
('S00201', 'D5', 0),
('S00201', 'D6', 0),
('S00201', 'D7', 0),
('S00201', 'D8', 1),
('S00201', 'D9', 1),
('S00201', 'E1', 1),
('S00201', 'E10', 1),
('S00201', 'E2', 1),
('S00201', 'E3', 1),
('S00201', 'E4', 1),
('S00201', 'E5', 1),
('S00201', 'E6', 1),
('S00201', 'E7', 1),
('S00201', 'E8', 1),
('S00201', 'E9', 1),
('S00202', 'A1', 1),
('S00202', 'A10', 1),
('S00202', 'A2', 1),
('S00202', 'A3', 0),
('S00202', 'A4', 0),
('S00202', 'A5', 1),
('S00202', 'A6', 1),
('S00202', 'A7', 1),
('S00202', 'A8', 1),
('S00202', 'A9', 1),
('S00202', 'B1', 1),
('S00202', 'B10', 1),
('S00202', 'B2', 1),
('S00202', 'B3', 0),
('S00202', 'B4', 0),
('S00202', 'B5', 1),
('S00202', 'B6', 1),
('S00202', 'B7', 1),
('S00202', 'B8', 1),
('S00202', 'B9', 1),
('S00202', 'C1', 1),
('S00202', 'C10', 1),
('S00202', 'C2', 1),
('S00202', 'C3', 1),
('S00202', 'C4', 1),
('S00202', 'C5', 1),
('S00202', 'C6', 1),
('S00202', 'C7', 1),
('S00202', 'C8', 1),
('S00202', 'C9', 1),
('S00202', 'D1', 1),
('S00202', 'D10', 1),
('S00202', 'D2', 1),
('S00202', 'D3', 1),
('S00202', 'D4', 1),
('S00202', 'D5', 1),
('S00202', 'D6', 1),
('S00202', 'D7', 1),
('S00202', 'D8', 1),
('S00202', 'D9', 1),
('S00202', 'E1', 0),
('S00202', 'E10', 1),
('S00202', 'E2', 0),
('S00202', 'E3', 1),
('S00202', 'E4', 1),
('S00202', 'E5', 1),
('S00202', 'E6', 1),
('S00202', 'E7', 1),
('S00202', 'E8', 1),
('S00202', 'E9', 1),
('S00301', 'A1', 1),
('S00301', 'A10', 1),
('S00301', 'A2', 1),
('S00301', 'A3', 1),
('S00301', 'A4', 1),
('S00301', 'A5', 1),
('S00301', 'A6', 1),
('S00301', 'A7', 1),
('S00301', 'A8', 1),
('S00301', 'A9', 1),
('S00301', 'B1', 1),
('S00301', 'B10', 1),
('S00301', 'B2', 1),
('S00301', 'B3', 1),
('S00301', 'B4', 1),
('S00301', 'B5', 1),
('S00301', 'B6', 1),
('S00301', 'B7', 1),
('S00301', 'B8', 1),
('S00301', 'B9', 1),
('S00301', 'C1', 1),
('S00301', 'C10', 1),
('S00301', 'C2', 1),
('S00301', 'C3', 1),
('S00301', 'C4', 1),
('S00301', 'C5', 1),
('S00301', 'C6', 1),
('S00301', 'C7', 1),
('S00301', 'C8', 1),
('S00301', 'C9', 1),
('S00301', 'D1', 1),
('S00301', 'D10', 1),
('S00301', 'D2', 1),
('S00301', 'D3', 1),
('S00301', 'D4', 1),
('S00301', 'D5', 1),
('S00301', 'D6', 1),
('S00301', 'D7', 1),
('S00301', 'D8', 1),
('S00301', 'D9', 1),
('S00301', 'E1', 1),
('S00301', 'E10', 1),
('S00301', 'E2', 1),
('S00301', 'E3', 1),
('S00301', 'E4', 1),
('S00301', 'E5', 1),
('S00301', 'E6', 1),
('S00301', 'E7', 1),
('S00301', 'E8', 1),
('S00301', 'E9', 1),
('S00302', 'A1', 1),
('S00302', 'A10', 1),
('S00302', 'A2', 1),
('S00302', 'A3', 1),
('S00302', 'A4', 1),
('S00302', 'A5', 1),
('S00302', 'A6', 1),
('S00302', 'A7', 1),
('S00302', 'A8', 1),
('S00302', 'A9', 1),
('S00302', 'B1', 1),
('S00302', 'B10', 1),
('S00302', 'B2', 1),
('S00302', 'B3', 1),
('S00302', 'B4', 1),
('S00302', 'B5', 1),
('S00302', 'B6', 1),
('S00302', 'B7', 1),
('S00302', 'B8', 1),
('S00302', 'B9', 1),
('S00302', 'C1', 1),
('S00302', 'C10', 1),
('S00302', 'C2', 1),
('S00302', 'C3', 1),
('S00302', 'C4', 1),
('S00302', 'C5', 1),
('S00302', 'C6', 1),
('S00302', 'C7', 1),
('S00302', 'C8', 1),
('S00302', 'C9', 1),
('S00302', 'D1', 1),
('S00302', 'D10', 1),
('S00302', 'D2', 1),
('S00302', 'D3', 1),
('S00302', 'D4', 1),
('S00302', 'D5', 1),
('S00302', 'D6', 1),
('S00302', 'D7', 1),
('S00302', 'D8', 1),
('S00302', 'D9', 1),
('S00302', 'E1', 1),
('S00302', 'E10', 1),
('S00302', 'E2', 1),
('S00302', 'E3', 1),
('S00302', 'E4', 1),
('S00302', 'E5', 1),
('S00302', 'E6', 1),
('S00302', 'E7', 1),
('S00302', 'E8', 1),
('S00302', 'E9', 1),
('S00401', 'A1', 1),
('S00401', 'A10', 1),
('S00401', 'A2', 1),
('S00401', 'A3', 1),
('S00401', 'A4', 1),
('S00401', 'A5', 1),
('S00401', 'A6', 1),
('S00401', 'A7', 1),
('S00401', 'A8', 1),
('S00401', 'A9', 1),
('S00401', 'B1', 1),
('S00401', 'B10', 1),
('S00401', 'B2', 1),
('S00401', 'B3', 1),
('S00401', 'B4', 1),
('S00401', 'B5', 1),
('S00401', 'B6', 1),
('S00401', 'B7', 1),
('S00401', 'B8', 1),
('S00401', 'B9', 1),
('S00401', 'C1', 1),
('S00401', 'C10', 1),
('S00401', 'C2', 1),
('S00401', 'C3', 1),
('S00401', 'C4', 1),
('S00401', 'C5', 1),
('S00401', 'C6', 1),
('S00401', 'C7', 1),
('S00401', 'C8', 1),
('S00401', 'C9', 1),
('S00401', 'D1', 1),
('S00401', 'D10', 1),
('S00401', 'D2', 1),
('S00401', 'D3', 1),
('S00401', 'D4', 1),
('S00401', 'D5', 1),
('S00401', 'D6', 1),
('S00401', 'D7', 1),
('S00401', 'D8', 1),
('S00401', 'D9', 1),
('S00401', 'E1', 1),
('S00401', 'E10', 1),
('S00401', 'E2', 1),
('S00401', 'E3', 1),
('S00401', 'E4', 1),
('S00401', 'E5', 1),
('S00401', 'E6', 1),
('S00401', 'E7', 1),
('S00401', 'E8', 1),
('S00401', 'E9', 1),
('S00402', 'A1', 1),
('S00402', 'A10', 1),
('S00402', 'A2', 1),
('S00402', 'A3', 1),
('S00402', 'A4', 1),
('S00402', 'A5', 1),
('S00402', 'A6', 1),
('S00402', 'A7', 1),
('S00402', 'A8', 1),
('S00402', 'A9', 1),
('S00402', 'B1', 1),
('S00402', 'B10', 1),
('S00402', 'B2', 1),
('S00402', 'B3', 1),
('S00402', 'B4', 1),
('S00402', 'B5', 1),
('S00402', 'B6', 1),
('S00402', 'B7', 1),
('S00402', 'B8', 1),
('S00402', 'B9', 1),
('S00402', 'C1', 1),
('S00402', 'C10', 1),
('S00402', 'C2', 1),
('S00402', 'C3', 1),
('S00402', 'C4', 1),
('S00402', 'C5', 1),
('S00402', 'C6', 1),
('S00402', 'C7', 1),
('S00402', 'C8', 1),
('S00402', 'C9', 1),
('S00402', 'D1', 1),
('S00402', 'D10', 1),
('S00402', 'D2', 1),
('S00402', 'D3', 1),
('S00402', 'D4', 1),
('S00402', 'D5', 1),
('S00402', 'D6', 1),
('S00402', 'D7', 1),
('S00402', 'D8', 1),
('S00402', 'D9', 1),
('S00402', 'E1', 1),
('S00402', 'E10', 1),
('S00402', 'E2', 1),
('S00402', 'E3', 1),
('S00402', 'E4', 1),
('S00402', 'E5', 1),
('S00402', 'E6', 1),
('S00402', 'E7', 1),
('S00402', 'E8', 1),
('S00402', 'E9', 1),
('S00501', 'A1', 1),
('S00501', 'A10', 1),
('S00501', 'A2', 1),
('S00501', 'A3', 1),
('S00501', 'A4', 1),
('S00501', 'A5', 1),
('S00501', 'A6', 1),
('S00501', 'A7', 1),
('S00501', 'A8', 1),
('S00501', 'A9', 1),
('S00501', 'B1', 1),
('S00501', 'B10', 1),
('S00501', 'B2', 1),
('S00501', 'B3', 1),
('S00501', 'B4', 1),
('S00501', 'B5', 1),
('S00501', 'B6', 1),
('S00501', 'B7', 1),
('S00501', 'B8', 1),
('S00501', 'B9', 1),
('S00501', 'C1', 1),
('S00501', 'C10', 1),
('S00501', 'C2', 1),
('S00501', 'C3', 1),
('S00501', 'C4', 1),
('S00501', 'C5', 1),
('S00501', 'C6', 1),
('S00501', 'C7', 1),
('S00501', 'C8', 1),
('S00501', 'C9', 1),
('S00501', 'D1', 1),
('S00501', 'D10', 1),
('S00501', 'D2', 1),
('S00501', 'D3', 1),
('S00501', 'D4', 1),
('S00501', 'D5', 1),
('S00501', 'D6', 1),
('S00501', 'D7', 1),
('S00501', 'D8', 1),
('S00501', 'D9', 1),
('S00501', 'E1', 1),
('S00501', 'E10', 1),
('S00501', 'E2', 1),
('S00501', 'E3', 1),
('S00501', 'E4', 1),
('S00501', 'E5', 1),
('S00501', 'E6', 1),
('S00501', 'E7', 1),
('S00501', 'E8', 1),
('S00501', 'E9', 1),
('S00502', 'A1', 1),
('S00502', 'A10', 1),
('S00502', 'A2', 1),
('S00502', 'A3', 1),
('S00502', 'A4', 1),
('S00502', 'A5', 1),
('S00502', 'A6', 1),
('S00502', 'A7', 1),
('S00502', 'A8', 1),
('S00502', 'A9', 1),
('S00502', 'B1', 1),
('S00502', 'B10', 1),
('S00502', 'B2', 1),
('S00502', 'B3', 1),
('S00502', 'B4', 1),
('S00502', 'B5', 1),
('S00502', 'B6', 1),
('S00502', 'B7', 1),
('S00502', 'B8', 1),
('S00502', 'B9', 1),
('S00502', 'C1', 1),
('S00502', 'C10', 1),
('S00502', 'C2', 1),
('S00502', 'C3', 1),
('S00502', 'C4', 1),
('S00502', 'C5', 1),
('S00502', 'C6', 1),
('S00502', 'C7', 1),
('S00502', 'C8', 1),
('S00502', 'C9', 1),
('S00502', 'D1', 1),
('S00502', 'D10', 1),
('S00502', 'D2', 1),
('S00502', 'D3', 1),
('S00502', 'D4', 1),
('S00502', 'D5', 1),
('S00502', 'D6', 1),
('S00502', 'D7', 1),
('S00502', 'D8', 1),
('S00502', 'D9', 1),
('S00502', 'E1', 1),
('S00502', 'E10', 1),
('S00502', 'E2', 1),
('S00502', 'E3', 1),
('S00502', 'E4', 1),
('S00502', 'E5', 1),
('S00502', 'E6', 1),
('S00502', 'E7', 1),
('S00502', 'E8', 1),
('S00502', 'E9', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `showing`
--

CREATE TABLE `showing` (
  `showingId` varchar(6) NOT NULL,
  `movieId` int(11) NOT NULL,
  `theaterId` varchar(6) NOT NULL,
  `versionId` int(11) NOT NULL,
  `showingDate` varchar(10) NOT NULL,
  `startTime` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `showing`
--

INSERT INTO `showing` (`showingId`, `movieId`, `theaterId`, `versionId`, `showingDate`, `startTime`) VALUES
('S00101', 101, '01A01', 1, '2025-12-04', '14:00'),
('S00102', 101, '01A02', 3, '2025-12-05', '19:00'),
('S00201', 102, '02A01', 1, '2025-12-04', '13:30'),
('S00202', 102, '02A02', 4, '2025-12-05', '18:20'),
('S00301', 103, '03A01', 1, '2025-12-04', '15:10'),
('S00302', 103, '03A02', 2, '2025-12-05', '20:00'),
('S00401', 104, '04A01', 1, '2025-12-06', '16:40'),
('S00402', 104, '04A02', 3, '2025-12-07', '21:10'),
('S00501', 105, '05A01', 1, '2025-12-06', '12:50'),
('S00502', 105, '05A02', 4, '2025-12-07', '17:30');

-- --------------------------------------------------------

--
-- 資料表結構 `theater`
--

CREATE TABLE `theater` (
  `theaterId` varchar(6) NOT NULL,
  `cinemaId` varchar(10) NOT NULL,
  `theaterName` varchar(30) NOT NULL,
  `seatNumber` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `theater`
--

INSERT INTO `theater` (`theaterId`, `cinemaId`, `theaterName`, `seatNumber`) VALUES
('01A01', '01', '1廳', 120),
('01A02', '01', '2廳', 150),
('02A01', '02', '1廳', 110),
('02A02', '02', '2廳', 140),
('03A01', '03', '1廳', 100),
('03A02', '03', '2廳', 130),
('04A01', '04', '1廳', 160),
('04A02', '04', '2廳', 200),
('05A01', '05', '1廳', 140),
('05A02', '05', '2廳', 180);

-- --------------------------------------------------------

--
-- 資料表結構 `ticketClass`
--

CREATE TABLE `ticketClass` (
  `ticketClassId` int(11) NOT NULL,
  `ticketClassName` varchar(10) NOT NULL,
  `ticketClassPrice` int(11) NOT NULL,
  `ticketTypeId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `ticketClass`
--

INSERT INTO `ticketClass` (`ticketClassId`, `ticketClassName`, `ticketClassPrice`, `ticketTypeId`) VALUES
(1, '全票', 330, 0),
(2, '優待票', 280, 1),
(3, '敬老票', 250, 1);

-- --------------------------------------------------------

--
-- 資料表結構 `ticketType`
--

CREATE TABLE `ticketType` (
  `ticketTypeId` int(11) NOT NULL,
  `ticketTypeName` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `ticketType`
--

INSERT INTO `ticketType` (`ticketTypeId`, `ticketTypeName`) VALUES
(0, '一般票'),
(1, '特殊票');

-- --------------------------------------------------------

--
-- 資料表結構 `topupTransaction`
--

CREATE TABLE `topupTransaction` (
  `transactionId` varchar(20) NOT NULL COMMENT '交易編號',
  `memberId` varchar(10) NOT NULL COMMENT '會員編號',
  `transactionType` varchar(10) NOT NULL COMMENT '交易類型：TOPUP=儲值, CONSUME=消費',
  `amount` int(11) NOT NULL COMMENT '交易金額',
  `balanceBefore` int(11) NOT NULL COMMENT '交易前餘額',
  `balanceAfter` int(11) NOT NULL COMMENT '交易後餘額',
  `transactionDate` datetime NOT NULL DEFAULT current_timestamp() COMMENT '交易時間',
  `description` varchar(100) NOT NULL COMMENT '交易描述',
  `status` varchar(10) NOT NULL DEFAULT 'SUCCESS' COMMENT '交易狀態：SUCCESS=成功, FAILED=失敗'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `topupTransaction`
--

INSERT INTO `topupTransaction` (`transactionId`, `memberId`, `transactionType`, `amount`, `balanceBefore`, `balanceAfter`, `transactionDate`, `description`, `status`) VALUES
('T20251201001', 'M0001', 'TOPUP', 1000, 0, 1000, '2025-12-01 10:30:00', '線上儲值 (信用卡)', 'SUCCESS'),
('T20251201002', 'M0001', 'CONSUME', 200, 1000, 800, '2025-12-01 14:20:00', '購買電影票 - 沙丘：第二部', 'SUCCESS'),
('T20251201003', 'M0002', 'TOPUP', 1500, 0, 1500, '2025-12-01 11:00:00', '線上儲值 (信用卡)', 'SUCCESS'),
('T20251201004', 'M0002', 'CONSUME', 280, 1500, 1220, '2025-12-01 16:30:00', '購買電影票 - 可可夜總會', 'SUCCESS'),
('T20251201005', 'M0003', 'TOPUP', 2000, 0, 2000, '2025-12-01 15:45:00', '線上儲值 (銀行轉帳)', 'SUCCESS'),
('T20251201006', 'M0003', 'CONSUME', 330, 2000, 1670, '2025-12-01 21:00:00', '購買電影票 - 航海王：紅髮歌姬', 'SUCCESS'),
('T20251201007', 'M0005', 'TOPUP', 100000, 0, 100000, '2025-12-01 00:00:01', '系統測試儲值', 'SUCCESS'),
('T20251201008', 'M0005', 'CONSUME', 1, 100000, 99999, '2025-12-01 00:00:02', '系統測試消費', 'SUCCESS'),
('T20251202001', 'M0001', 'TOPUP', 500, 800, 1300, '2025-12-02 09:15:00', '線上儲值 (行動支付)', 'SUCCESS'),
('T20251202002', 'M0001', 'CONSUME', 150, 1300, 1150, '2025-12-02 19:45:00', '購買餐點 - 大爆米花+可樂', 'SUCCESS'),
('T20251202003', 'M0002', 'CONSUME', 420, 1220, 800, '2025-12-02 18:15:00', '購買電影票+雙人套餐A', 'SUCCESS'),
('T20251202004', 'M0003', 'TOPUP', 1000, 1670, 2670, '2025-12-02 12:30:00', '線上儲值 (行動支付)', 'SUCCESS'),
('T20251202005', 'M0003', 'CONSUME', 370, 2670, 2300, '2025-12-02 20:45:00', '購買電影票+餐點', 'SUCCESS'),
('T20251202006', 'M0004', 'TOPUP', 1500, 0, 1500, '2025-12-02 08:20:00', '線上儲值 (信用卡)', 'SUCCESS'),
('T20251203001', 'M0001', 'TOPUP', 1000, 1150, 2150, '2025-12-03 08:00:00', '線上儲值 (金融卡)', 'SUCCESS'),
('T20251203002', 'M0001', 'CONSUME', 650, 2150, 1500, '2025-12-03 20:30:00', '購買電影票+餐點套餐', 'SUCCESS'),
('T20251203003', 'M0004', 'CONSUME', 300, 1500, 1200, '2025-12-03 19:10:00', '購買電影票+餐點', 'SUCCESS'),
('T20251203004', 'M0001', 'TOPUP', 5000, 1500, 1500, '2025-12-03 22:00:00', '線上儲值 (信用卡) - 付款失敗', 'FAILED'),
('T20251203005', 'M0002', 'CONSUME', 1000, 800, 800, '2025-12-03 22:30:00', '購買電影票 - 餘額不足', 'FAILED'),
('TXN20251204001', 'M0001', 'CONSUME', 590, 2000, 1410, '2025-12-04 10:30:00', '購票消費 - 訂單號碼: ORD202412010001', 'SUCCESS'),
('TXN20251204002', 'M0002', 'CONSUME', 899, 1500, 601, '2025-12-04 14:20:00', '購票消費 - 訂單號碼: ORD202412010002', 'SUCCESS'),
('TXN20251204003', 'M0003', 'CONSUME', 400, 800, 400, '2025-12-04 16:45:00', '購票消費 - 訂單號碼: ORD202412010003', 'SUCCESS');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `bookingRecord`
--
ALTER TABLE `bookingRecord`
  ADD PRIMARY KEY (`orderNumber`),
  ADD KEY `memberId` (`memberId`),
  ADD KEY `showingId` (`showingId`),
  ADD KEY `ticketTypeId` (`ticketTypeId`),
  ADD KEY `orderStatusId` (`orderStatusId`);

--
-- 資料表索引 `cinema`
--
ALTER TABLE `cinema`
  ADD PRIMARY KEY (`cinemaId`);

--
-- 資料表索引 `grade`
--
ALTER TABLE `grade`
  ADD PRIMARY KEY (`gradeId`);

--
-- 資料表索引 `meals`
--
ALTER TABLE `meals`
  ADD PRIMARY KEY (`mealsId`),
  ADD KEY `mealsTypeId` (`mealsTypeId`);

--
-- 資料表索引 `mealsType`
--
ALTER TABLE `mealsType`
  ADD PRIMARY KEY (`mealsTypeId`);

--
-- 資料表索引 `memberCashCard`
--
ALTER TABLE `memberCashCard`
  ADD PRIMARY KEY (`memberId`);

--
-- 資料表索引 `memberProfile`
--
ALTER TABLE `memberProfile`
  ADD PRIMARY KEY (`memberId`),
  ADD KEY `role_id` (`role_id`);

--
-- 資料表索引 `movie`
--
ALTER TABLE `movie`
  ADD PRIMARY KEY (`movieId`),
  ADD KEY `gradeId` (`gradeId`),
  ADD KEY `movieTypeId` (`movieTypeId`);

--
-- 資料表索引 `movieType`
--
ALTER TABLE `movieType`
  ADD PRIMARY KEY (`movieTypeId`);

--
-- 資料表索引 `orderStatus`
--
ALTER TABLE `orderStatus`
  ADD PRIMARY KEY (`orderStatusId`);

--
-- 資料表索引 `playVersion`
--
ALTER TABLE `playVersion`
  ADD PRIMARY KEY (`versionId`);

--
-- 資料表索引 `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`);

--
-- 資料表索引 `seatCondition`
--
ALTER TABLE `seatCondition`
  ADD PRIMARY KEY (`showingId`,`seatNumber`);

--
-- 資料表索引 `showing`
--
ALTER TABLE `showing`
  ADD PRIMARY KEY (`showingId`),
  ADD KEY `movieId` (`movieId`),
  ADD KEY `theaterId` (`theaterId`),
  ADD KEY `versionId` (`versionId`);

--
-- 資料表索引 `theater`
--
ALTER TABLE `theater`
  ADD PRIMARY KEY (`theaterId`),
  ADD KEY `cinemaId` (`cinemaId`);

--
-- 資料表索引 `ticketClass`
--
ALTER TABLE `ticketClass`
  ADD PRIMARY KEY (`ticketClassId`),
  ADD KEY `ticketTypeId` (`ticketTypeId`);

--
-- 資料表索引 `ticketType`
--
ALTER TABLE `ticketType`
  ADD PRIMARY KEY (`ticketTypeId`);

--
-- 資料表索引 `topupTransaction`
--
ALTER TABLE `topupTransaction`
  ADD PRIMARY KEY (`transactionId`),
  ADD KEY `memberId` (`memberId`);

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `bookingRecord`
--
ALTER TABLE `bookingRecord`
  ADD CONSTRAINT `bookingrecord_ibfk_1` FOREIGN KEY (`memberId`) REFERENCES `memberProfile` (`memberId`),
  ADD CONSTRAINT `bookingrecord_ibfk_2` FOREIGN KEY (`showingId`) REFERENCES `showing` (`showingId`),
  ADD CONSTRAINT `bookingrecord_ibfk_3` FOREIGN KEY (`ticketTypeId`) REFERENCES `ticketType` (`ticketTypeId`),
  ADD CONSTRAINT `bookingrecord_ibfk_4` FOREIGN KEY (`orderStatusId`) REFERENCES `orderStatus` (`orderStatusId`);

--
-- 資料表的限制式 `meals`
--
ALTER TABLE `meals`
  ADD CONSTRAINT `meals_ibfk_1` FOREIGN KEY (`mealsTypeId`) REFERENCES `mealsType` (`mealsTypeId`);

--
-- 資料表的限制式 `memberCashCard`
--
ALTER TABLE `memberCashCard`
  ADD CONSTRAINT `membercashcard_ibfk_1` FOREIGN KEY (`memberId`) REFERENCES `memberProfile` (`memberId`);

--
-- 資料表的限制式 `memberProfile`
--
ALTER TABLE `memberProfile`
  ADD CONSTRAINT `memberprofile_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`);

--
-- 資料表的限制式 `movie`
--
ALTER TABLE `movie`
  ADD CONSTRAINT `movie_ibfk_1` FOREIGN KEY (`gradeId`) REFERENCES `grade` (`gradeId`),
  ADD CONSTRAINT `movie_ibfk_2` FOREIGN KEY (`movieTypeId`) REFERENCES `movieType` (`movieTypeId`);

--
-- 資料表的限制式 `seatCondition`
--
ALTER TABLE `seatCondition`
  ADD CONSTRAINT `seatcondition_ibfk_1` FOREIGN KEY (`showingId`) REFERENCES `showing` (`showingId`);

--
-- 資料表的限制式 `showing`
--
ALTER TABLE `showing`
  ADD CONSTRAINT `showing_ibfk_1` FOREIGN KEY (`movieId`) REFERENCES `movie` (`movieId`),
  ADD CONSTRAINT `showing_ibfk_2` FOREIGN KEY (`theaterId`) REFERENCES `theater` (`theaterId`),
  ADD CONSTRAINT `showing_ibfk_3` FOREIGN KEY (`versionId`) REFERENCES `playVersion` (`versionId`);

--
-- 資料表的限制式 `theater`
--
ALTER TABLE `theater`
  ADD CONSTRAINT `theater_ibfk_1` FOREIGN KEY (`cinemaId`) REFERENCES `cinema` (`cinemaId`);

--
-- 資料表的限制式 `ticketClass`
--
ALTER TABLE `ticketClass`
  ADD CONSTRAINT `ticketclass_ibfk_1` FOREIGN KEY (`ticketTypeId`) REFERENCES `ticketType` (`ticketTypeId`);

--
-- 資料表的限制式 `topupTransaction`
--
ALTER TABLE `topupTransaction`
  ADD CONSTRAINT `topuptransaction_ibfk_1` FOREIGN KEY (`memberId`) REFERENCES `memberProfile` (`memberId`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
