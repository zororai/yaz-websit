-- ============================================================
-- Youth Advocates Zimbabwe — Database Setup
-- Import via phpMyAdmin or: mysql -u root -p < database.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS ya_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ya_website;

-- Programs table
CREATE TABLE IF NOT EXISTS programs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255)             NOT NULL,
    description TEXT,
    year        INT                      NOT NULL DEFAULT 2024,
    image_path  VARCHAR(500)             DEFAULT '',
    detail_url  VARCHAR(500)             DEFAULT '',
    status      ENUM('active','draft')   DEFAULT 'active',
    created_at  TIMESTAMP                DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP                DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Newsletter subscribers
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(255),
    phone          VARCHAR(30)   NOT NULL,
    email          VARCHAR(255),
    active         TINYINT(1)    DEFAULT 1,
    subscribed_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_phone (phone)
);

-- Admin users
CREATE TABLE IF NOT EXISTS admin_users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- Pre-load existing programs from the website
-- ============================================================
INSERT INTO programs (title, description, year, image_path, detail_url) VALUES
('Vocational Skills & Economic Strengthening',
 'YA innovative approach links integrated SRHR/GBV/HIV information and services with skill building and entrepreneurship for young people.',
 2024, 'assets/img/portfolio/baking.jpg', 'all programs.html#vocational-skills-economic-strengthening'),

('Step Up 4 Adolescents SRHR Access',
 'Collaborating for equitable SRHR in East and Southern Africa.',
 2024, 'assets/img/portfolio/SRHR.png', 'all programs.html#step-up-srhr-access'),

('Every Hour Matters Campaign',
 'Collaborating for equitable SRHR in East & Southern Africa — an engagement initiative creating lasting impact.',
 2024, 'assets/img/portfolio/ehm.png', 'all programs.html#every-hour-matters'),

('Building Bridges Through Positive Norms to Address Violence Against Children',
 '',
 2024, 'assets/img/portfolio/furamera.png', 'all programs.html#positive-norms-violence'),

('Break the Outbreak',
 'By embodying the six C''s of leadership, individuals can become effective leaders who inspire and drive positive change.',
 2024, 'assets/img/portfolio/vz.png', 'all programs.html#break-the-outbreak'),

('Gold Youth Peer Education Programme',
 'The deputy director for reproductive health championed this peer education initiative.',
 2022, 'assets/img/portfolio/goldyouth.png', 'all programs.html#gold-youth-peer'),

('DREAMS Program',
 'In the Hutsanana NdoLife Campaign, Youth Advocates mounted murals which encourage positive health behaviors.',
 2023, 'assets/img/portfolio/dreams.png', 'all programs.html#dreams-program'),

('Safe Schools for a Healthier Population Initiative',
 'Our campaign to bolster school health and create safe environments for young people.',
 2024, 'assets/img/portfolio/kids.png', 'all programs.html#safe-schools'),

('Youth Helpline Empowering Lives',
 'A confidential helpline empowering youth with knowledge and connecting them to essential health services.',
 2021, 'assets/img/portfolio/helpline.png', 'all programs.html#youth-helpline-empowering'),

('Amplifying Adolescents & Key Vulnerable Populations in Community Led Monitoring',
 'Together, parents, community workers and health advocates amplify the voices of vulnerable youth.',
 2022, 'assets/img/portfolio/CLM.png', 'all programs.html#amplifying-community-led-monitoring'),

('Enhancing Access to Integrated HIV Services Through m-Health in Southern Africa',
 'Using mobile technology to expand access to HIV services for adolescents and young people across southern Africa.',
 2023, 'assets/img/portfolio/HIV.png', 'all programs.html#mhealth-hiv-services'),

('Ending Child Marriages; Transforming Futures',
 'Together, parents, community workers and governments create a world where young people understand and exercise their rights.',
 2024, 'assets/img/portfolio/pregnant.png', 'all programs.html#ending-child-marriages-1'),

('Youth Enterprise Graduate Model',
 'An innovative model empowering young graduates with entrepreneurship skills and economic opportunities.',
 2025, 'assets/img/portfolio/oak.jpeg', 'all programs.html#ending-child-marriages-2'),

('Mental Beats',
 'A mental health awareness and support program for young people in communities.',
 2025, 'assets/img/portfolio/mental.png', 'all programs.html#ending-child-marriages-3'),

('Gold Youth',
 'Empowering the golden generation of youth advocates and changemakers.',
 2025, 'assets/img/portfolio/gold youth.jpeg', 'all programs.html#ending-child-marriages-4');
