-- Run once on the server after creating the database
-- mysql -u <user> -p evefitzart < /var/www/evefitzart/admin/setup.sql

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `images` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `url` VARCHAR(500) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `site_content` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `content_key` VARCHAR(100) NOT NULL UNIQUE,
    `content_value` TEXT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `site_content` (`content_key`, `content_value`) VALUES
('hero_title',       'Eve Fitzsimons'),
('hero_subtitle',    'Art Portfolio'),
('hero_description', ''),
('featured_image_1', ''),
('featured_image_2', ''),
('featured_image_3', ''),
('about_bio',        '<p>Eve Fitzsimons is a passionate artist who creates stunning and heartfelt pieces that resonate with her audience. Her work is inspired by the beauty of the world around her and the emotions that connect us all. Through her art, Eve aims to bring joy, inspiration, and a sense of wonder to everyone who experiences it.</p>'),
('about_photo',      '../pictures/beatas1.png');
