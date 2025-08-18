CREATE DATABASE  IF NOT EXISTS `ucc_registry` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `ucc_registry`;
-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: localhost    Database: ucc_registry
-- ------------------------------------------------------
-- Server version	8.0.41

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `course_enrollment`
--

DROP TABLE IF EXISTS `course_enrollment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_enrollment` (
  `enrollment_id` int NOT NULL AUTO_INCREMENT,
  `course_code` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `semester` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `year` int DEFAULT NULL,
  `section` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `student_id` int DEFAULT NULL,
  `coursework_grade` decimal(5,2) DEFAULT NULL,
  `final_exam_grade` decimal(5,2) DEFAULT NULL,
  `student_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`enrollment_id`),
  UNIQUE KEY `unique_student_course` (`student_id`,`course_code`),
  UNIQUE KEY `unique_enrollment` (`student_id`,`course_code`,`semester`,`year`,`section`),
  KEY `course_code` (`course_code`),
  CONSTRAINT `course_enrollment_ibfk_1` FOREIGN KEY (`course_code`) REFERENCES `courses` (`course_code`),
  CONSTRAINT `course_enrollment_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_enrollment`
--

LOCK TABLES `course_enrollment` WRITE;
/*!40000 ALTER TABLE `course_enrollment` DISABLE KEYS */;
INSERT INTO `course_enrollment` VALUES (15,'ITT301','Summer',2025,'A',20224375,89.00,89.40,'Ralston Campbell'),(17,'IT101','Fall',2025,'A',20250001,85.50,90.00,'John Smith'),(18,'MTH101','Fall',2025,'A',20250001,78.00,82.50,'John Smith'),(19,'GSB101','Fall',2025,'A',20250001,92.00,88.00,'John Smith'),(20,'BUS201','Fall',2025,'A',20250002,88.00,91.50,'Emma Johnson'),(21,'MTH101','Fall',2025,'A',20250002,75.00,80.00,'Emma Johnson'),(22,'GSB101','Fall',2025,'A',20250002,82.00,85.00,'Emma Johnson'),(23,'IT101','Fall',2025,'A',20250003,90.00,93.50,'Michael Williams'),(24,'IT201','Fall',2025,'A',20250003,87.00,84.00,'Michael Williams'),(25,'MTH101','Fall',2025,'A',20250003,79.00,81.00,'Michael Williams'),(26,'BUS201','Fall',2025,'A',20250004,91.00,88.50,'Sophia Brown'),(27,'BUS301','Fall',2025,'A',20250004,84.00,86.00,'Sophia Brown'),(28,'MTH101','Fall',2025,'A',20250004,77.00,75.00,'Sophia Brown'),(29,'BUS201','Fall',2025,'A',20250005,89.00,92.00,'James Davis'),(30,'GSB101','Fall',2025,'A',20250005,94.00,90.00,'James Davis'),(31,'MTH101','Fall',2025,'A',20250005,81.00,79.00,'James Davis'),(32,'IT201','Fall',2025,'A',20250001,NULL,NULL,'John Smith'),(33,'MTH201','Fall',2025,'A',20250001,NULL,NULL,'John Smith'),(34,'BUS301','Fall',2025,'A',20250002,NULL,NULL,'Emma Johnson'),(35,'LAW201','Fall',2025,'A',20250002,NULL,NULL,'Emma Johnson'),(36,'IT301','Fall',2025,'A',20250003,NULL,NULL,'Michael Williams'),(37,'MTH201','Fall',2025,'A',20250003,NULL,NULL,'Michael Williams'),(38,'BUS401','Fall',2025,'A',20250004,NULL,NULL,'Sophia Brown'),(39,'LAW201','Fall',2025,'A',20250004,NULL,NULL,'Sophia Brown'),(40,'BUS301','Fall',2025,'A',20250005,NULL,NULL,'James Davis'),(41,'THM201','Fall',2025,'A',20250005,66.00,78.00,'James Davis'),(42,'GSB101','Fall',2025,'A',20221839,88.00,98.00,'Geordi Duncan'),(43,'IT101','Fall',2025,'A',20221839,99.00,92.00,'Geordi Duncan'),(44,'IT201','Fall',2025,'A',20221839,78.00,88.00,'Geordi Duncan'),(48,'MTH101','Fall',2025,'A',20214229,78.00,88.00,NULL),(49,'IT101','Fall',2025,'A',20214229,67.00,56.00,NULL);
/*!40000 ALTER TABLE `course_enrollment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_schedule`
--

DROP TABLE IF EXISTS `course_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_schedule` (
  `schedule_id` int NOT NULL AUTO_INCREMENT,
  `course_code` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `semester` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `year` int DEFAULT NULL,
  `section` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lecturers` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lecturer_id` int DEFAULT NULL,
  `day` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `time` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `location` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`schedule_id`),
  KEY `course_code` (`course_code`),
  KEY `lecturer_id` (`lecturer_id`),
  CONSTRAINT `course_schedule_ibfk_1` FOREIGN KEY (`course_code`) REFERENCES `courses` (`course_code`),
  CONSTRAINT `course_schedule_ibfk_2` FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers` (`lecturer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_schedule`
--

LOCK TABLES `course_schedule` WRITE;
/*!40000 ALTER TABLE `course_schedule` DISABLE KEYS */;
INSERT INTO `course_schedule` VALUES (3,'ITT301','Summer',2025,'A','Otis',NULL,'Sunday','7 - 10 PM','Online'),(5,'IT101','Fall',2025,'A','Dr. Michael Johnson',NULL,'Monday','9:00 AM - 11:30 AM','Lab 101'),(6,'IT201','Fall',2025,'A','Ms. Jennifer Davis',NULL,'Tuesday','1:00 PM - 3:30 PM','Lab 102'),(7,'IT301','Fall',2025,'A','Dr. Michael Johnson',NULL,'Wednesday','9:00 AM - 12:30 PM','Lab 103'),(8,'IT401','Fall',2025,'A','Mr. Patricia Thomas',NULL,'Thursday','2:00 PM - 5:30 PM','Lab 104'),(9,'IT501','Fall',2025,'A','Dr. Michael Johnson',NULL,'Friday','4:00 PM - 6:30 PM','Room 201'),(10,'BUS201','Fall',2025,'A','Prof. Sarah Williams',NULL,'Monday','1:00 PM - 3:30 PM','Room 202'),(11,'BUS301','Fall',2025,'A','Dr. Emily Thompson',NULL,'Tuesday','9:00 AM - 11:30 AM','Room 203'),(12,'BUS401','Fall',2025,'A','Prof. Sarah Williams',NULL,'Wednesday','1:00 PM - 4:30 PM','Room 204'),(13,'BUS501','Fall',2025,'A','Dr. Emily Thompson',NULL,'Thursday','5:00 PM - 7:30 PM','Room 205'),(14,'MTH101','Fall',2025,'A','Dr. David Brown',NULL,'Monday','11:00 AM - 1:30 PM','Room 301'),(15,'MTH201','Fall',2025,'A','Dr. David Brown',NULL,'Tuesday','3:00 PM - 6:30 PM','Room 302'),(16,'MTH301','Fall',2025,'A','Dr. David Brown',NULL,'Wednesday','9:00 AM - 11:30 AM','Room 303'),(17,'LAW201','Fall',2025,'A','Prof. Daniel Martinez',NULL,'Thursday','9:00 AM - 11:30 AM','Room 401'),(18,'LAW301','Fall',2025,'A','Prof. Daniel Martinez',NULL,'Friday','1:00 PM - 4:30 PM','Room 402'),(19,'THM201','Fall',2025,'A','Ms. Laura Anderson',NULL,'Monday','3:00 PM - 5:30 PM','Room 501'),(20,'GSB101','Fall',2025,'A','Mr. Robert Wilson',NULL,'Tuesday','11:00 AM - 1:30 PM','Room 601'),(21,'IT101','Spring',2026,'A','Dr. Michael Johnson',NULL,'Monday','9:00 AM - 11:30 AM','Lab 101'),(22,'IT201','Spring',2026,'A','Ms. Jennifer Davis',NULL,'Tuesday','1:00 PM - 3:30 PM','Lab 102'),(23,'IT301','Spring',2026,'A','Dr. Michael Johnson',NULL,'Wednesday','9:00 AM - 12:30 PM','Lab 103'),(24,'IT401','Spring',2026,'A','Mr. Patricia Thomas',NULL,'Thursday','2:00 PM - 5:30 PM','Lab 104'),(25,'BUS201','Spring',2026,'A','Prof. Sarah Williams',NULL,'Monday','1:00 PM - 3:30 PM','Room 202'),(26,'BUS301','Spring',2026,'A','Dr. Emily Thompson',NULL,'Tuesday','9:00 AM - 11:30 AM','Room 203'),(27,'MTH101','Spring',2026,'A','Dr. David Brown',NULL,'Monday','11:00 AM - 1:30 PM','Room 301'),(28,'MTH201','Spring',2026,'A','Dr. David Brown',NULL,'Tuesday','3:00 PM - 6:30 PM','Room 302'),(29,'LAW201','Spring',2026,'A','Prof. Daniel Martinez',NULL,'Thursday','9:00 AM - 11:30 AM','Room 401'),(30,'THM201','Spring',2026,'A','Ms. Laura Anderson',NULL,'Monday','3:00 PM - 5:30 PM','Room 501'),(31,'GSB101','Spring',2026,'A','Mr. Robert Wilson',NULL,'Tuesday','11:00 AM - 1:30 PM','Room 601'),(32,'ITT419','Spring',2025,'A','Niel Williams',NULL,'Friday','8:00 PM - 10:00 PM','ROOM A12');
/*!40000 ALTER TABLE `course_schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courses` (
  `course_code` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `credits` int DEFAULT NULL,
  `degree_level` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `prerequisites` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`course_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES ('BUS201','Principles of Management',3,'Undergraduate',NULL),('BUS301','Business Ethics',3,'Undergraduate','BUS201'),('BUS401','Strategic Management',4,'Undergraduate','BUS301'),('BUS501','Corporate Leadership',3,'Graduate',NULL),('GSB101','Introduction to Psychology',3,'Undergraduate',NULL),('IT101','Introduction to Programming',3,'Undergraduate',NULL),('IT201','Web Development',3,'Undergraduate','IT101'),('IT301','Database Management Systems',4,'Undergraduate','IT201'),('IT401','Software Engineering',4,'Undergraduate','IT301'),('IT501','Advanced Software Development',3,'Graduate',NULL),('ITT301','IA1',3,'Undergraduate','Programming Tech'),('ITT419','Human Computer Interface and Controls',3,'Undergraduate','ITT201'),('LAW201','Introduction to Law',3,'Undergraduate',NULL),('LAW301','Criminal Law',4,'Undergraduate','LAW201'),('MTH101','College Algebra',3,'Undergraduate',NULL),('MTH201','Calculus I',4,'Undergraduate','MTH101'),('MTH301','Statistics',3,'Undergraduate','MTH101'),('THM201','Hospitality Management',3,'Undergraduate',NULL);
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `department_id` varchar(10) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `faculty` varchar(100) NOT NULL,
  PRIMARY KEY (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lecturers`
--

DROP TABLE IF EXISTS `lecturers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lecturers` (
  `lecturer_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `department` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `position` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Adjunct Lecturer',
  `lecturer_email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`lecturer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20222001 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lecturers`
--

LOCK TABLES `lecturers` WRITE;
/*!40000 ALTER TABLE `lecturers` DISABLE KEYS */;
INSERT INTO `lecturers` VALUES (9979,'MS','Allen','Vicks','Law','Staff Lecturer','AV@jm.com'),(10001,'Dr.','Michael','Johnson','Information Technology','Staff Lecturer','mjohnson@ucc.edu.jm'),(10002,'Prof.','Sarah','Williams','Business Administration','Staff Lecturer','swilliams@ucc.edu.jm'),(10003,'Dr.','David','Brown','Mathematics','Staff Lecturer','dbrown@ucc.edu.jm'),(10004,'Ms.','Jennifer','Davis','Information Technology','Adjunct Lecturer','jdavis@ucc.edu.jm'),(10005,'Mr.','Robert','Wilson','General Studies and Behavioural','Adjunct Lecturer','rwilson@ucc.edu.jm'),(10006,'Dr.','Emily','Thompson','Business Administration','Staff Lecturer','ethompson@ucc.edu.jm'),(10007,'Prof.','Daniel','Martinez','Law','Staff Lecturer','dmartinez@ucc.edu.jm'),(10008,'Ms.','Laura','Anderson','Tourism and Hospitality','Adjunct Lecturer','landerson@ucc.edu.jm'),(10009,'Dr.','James','Taylor','College of Graduate Studies and Research','Staff Lecturer','jtaylor@ucc.edu.jm'),(10010,'Mr.','Patricia','Thomas','Information Technology','Adjunct Lecturer','pthomas@ucc.edu.jm'),(20909,'Mr','Mark','Ben','Law','','MB@ucc.edu.jm'),(9845990,'Mr','Ava','Pink','Finance and Accounting','Adjunct Lecturer','AP@utech.edu.jm'),(20201212,'Mrs','Marcella','Brown','Business Studies','Staff Lecturer','MB@ucc.edu.jm');
/*!40000 ALTER TABLE `lecturers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programs`
--

DROP TABLE IF EXISTS `programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `programs` (
  `program_id` varchar(10) NOT NULL,
  `program_name` varchar(100) NOT NULL,
  `degree_level` varchar(50) NOT NULL,
  `department_id` varchar(10) NOT NULL,
  `description` text,
  PRIMARY KEY (`program_id`),
  KEY `idx_prog_dept` (`department_id`),
  CONSTRAINT `programs_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programs`
--

LOCK TABLES `programs` WRITE;
/*!40000 ALTER TABLE `programs` DISABLE KEYS */;
/*!40000 ALTER TABLE `programs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `student_id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `middle_name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `personal_email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `student_email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mobile` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `home` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `work` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `home_address` text COLLATE utf8mb4_general_ci,
  `next_of_kin` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kin_contact` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `program` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gpa` decimal(3,2) DEFAULT NULL,
  `next_of_kin_contact` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `work_number` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `home_number` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20250006 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (20214229,'Joel','S','Dawkins','jdawkins@gmail.com','jdawkins@stu.uss.edu.jm','8752345432',NULL,NULL,'1 Road lane Kgn 8','Natasha Dawkins',NULL,'BSc Information Technology',3.17,'876-987-2314','',''),(20221839,'Geordi','Mark','Duncan','geordiduncan@gmail.com','geordi@stu.ucc.edu.jm','8764257385',NULL,NULL,'8 Brompton Road Apt D4','Montoya',NULL,'BSc Business Administration',3.89,'876-234-5678','',''),(20224375,'Ralston','Ric','Campbell','Ralston@gmail.com','Rcampbell45@stu.ucc.edu.jm','876-3871750',NULL,NULL,'na','na',NULL,'BSc Information Technology',3.67,'na','',''),(20250001,'John',NULL,'Smith','jsmith@gmail.com','jsmith@stu.ucc.edu.jm','876-555-1001',NULL,NULL,'123 Main St, Kingston','Mary Smith',NULL,'BSc Information Technology',3.72,'876-555-2001',NULL,NULL),(20250002,'Emma','Jane','Johnson','ejohnson@gmail.com','ejohnson@stu.ucc.edu.jm','876-555-1002',NULL,NULL,'456 Oak Ave, Montego Bay','James Johnson',NULL,'BSc Business Administration',3.61,'876-555-2002',NULL,NULL),(20250003,'Michael',NULL,'Williams','mwilliams@gmail.com','mwilliams@stu.ucc.edu.jm','876-555-1003',NULL,NULL,'789 Pine Rd, Ocho Rios','Sarah Williams',NULL,'BSc Computer Science',3.72,'876-555-2003',NULL,NULL),(20250004,'Sophia','Rose','Brown','sbrown@gmail.com','sbrown@stu.ucc.edu.jm','876-555-1004',NULL,NULL,'101 Cedar Ln, Mandeville','Robert Alan Brown',NULL,'BSc Accounting',3.72,'876-555-2004','',''),(20250005,'James','Thomas','Davis','jdavis@gmail.com','jdavis@stu.ucc.edu.jm','876-555-1005',NULL,NULL,'202 Elm Blvd, Portmore','Lisa Davis',NULL,'BSc Human Resource Management',3.67,'876-555-2005',NULL,NULL);
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-07  0:23:09
