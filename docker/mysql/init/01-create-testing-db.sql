CREATE DATABASE IF NOT EXISTS `book_rental_testing`
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON `book_rental_testing`.* TO 'book_rental'@'%';

FLUSH PRIVILEGES;
