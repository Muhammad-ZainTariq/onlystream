-- Create the onlystream schema
CREATE SCHEMA onlystream;

-- Use the onlystream schema
USE onlystream;


-- 1. security_questions (No dependencies)
CREATE TABLE security_questions (
    question_id INT(11) NOT NULL AUTO_INCREMENT,
    question_text VARCHAR(255) NOT NULL,
    PRIMARY KEY (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. users (No dependencies, referenced by many)
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(50) DEFAULT NULL,
    last_name VARCHAR(50) DEFAULT NULL,
    password VARCHAR(500) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    profile_picture BLOB DEFAULT NULL,
    membership_tier TINYINT(4) DEFAULT 0,
    video_uploads_left INT(11) DEFAULT 0,
    music_uploads_left INT(11) DEFAULT 0,
    membership_expires_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. staff (No dependencies, referenced by others)
CREATE TABLE staff (
    id INT(11) NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    password VARCHAR(500) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_admin TINYINT(1) DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. staff_requests (No foreign keys, standalone)
CREATE TABLE staff_requests (
    id INT(11) NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    question_id INT(11) NOT NULL,
    answer_hash TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. videos (Depends on users)
CREATE TABLE videos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    video_title VARCHAR(255) DEFAULT NULL,
    video_url VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id INT(11) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    CONSTRAINT videos_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. user_security_answers (Depends on security_questions, users not constrained)
CREATE TABLE user_security_answers (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) DEFAULT NULL,
    question_id INT(11) NOT NULL,
    answer_hash VARCHAR(500) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY fk_security_question (question_id),
    CONSTRAINT fk_security_question FOREIGN KEY (question_id) REFERENCES security_questions (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. user_payments (Depends on users)
CREATE TABLE user_payments (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    tier INT(11) NOT NULL,
    name_on_card VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    card_number VARCHAR(500) NOT NULL,
    cvv VARCHAR(255) NOT NULL,
    phone_number VARCHAR(255) NOT NULL,
    postcode VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    CONSTRAINT user_payments_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 8. staff_security (Depends on staff and security_questions)
CREATE TABLE staff_security (
    id INT(11) NOT NULL AUTO_INCREMENT,
    staff_id INT(11) NOT NULL,
    question_id INT(11) NOT NULL,
    answer_hash TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY staff_id (staff_id),
    KEY question_id (question_id),
    CONSTRAINT staff_security_ibfk_1 FOREIGN KEY (staff_id) REFERENCES staff (id) ON DELETE CASCADE,
    CONSTRAINT staff_security_ibfk_2 FOREIGN KEY (question_id) REFERENCES security_questions (question_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 9. reports (Depends on users)
CREATE TABLE reports (
    id INT(11) NOT NULL AUTO_INCREMENT,
    reporter_id INT(11) NOT NULL,
    item_type ENUM('music', 'video') NOT NULL DEFAULT 'video',
    item_id INT(11) NOT NULL,
    reason VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'resolved', 'dismissed') DEFAULT 'pending',
    PRIMARY KEY (id),
    KEY reporter_id (reporter_id),
    KEY item_type (item_type, item_id),
    CONSTRAINT reports_ibfk_1 FOREIGN KEY (reporter_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 10. messages (Depends on staff and users)
CREATE TABLE messages (
    id INT(11) NOT NULL AUTO_INCREMENT,
    sender_id INT(11) NOT NULL,
    receiver_id INT(11) NOT NULL,
    message_text TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_read TINYINT(1) DEFAULT 0,
    PRIMARY KEY (id),
    KEY sender_id (sender_id),
    KEY receiver_id (receiver_id),
    CONSTRAINT messages_sender_staff_fk FOREIGN KEY (sender_id) REFERENCES staff (id),
    CONSTRAINT messages_receiver_user_fk FOREIGN KEY (receiver_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 11. likes (Depends on users)
CREATE TABLE likes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    item_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    content_type ENUM('video', 'audio') NOT NULL DEFAULT 'video',
    PRIMARY KEY (id),
    UNIQUE KEY unique_like (content_type, item_id, user_id),
    KEY user_id (user_id),
    CONSTRAINT likes_ibfk_2 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 12. follows (Depends on users)
CREATE TABLE follows (
    id INT(11) NOT NULL AUTO_INCREMENT,
    follower_id INT(11) NOT NULL,
    followed_id INT(11) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY follower_id (follower_id, followed_id),
    KEY followed_id (followed_id),
    CONSTRAINT follows_ibfk_1 FOREIGN KEY (follower_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT follows_ibfk_2 FOREIGN KEY (followed_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 13. contact_us (Depends on users and staff)
CREATE TABLE contact_us (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    contact_number VARCHAR(15) DEFAULT NULL,
    query TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    response TEXT DEFAULT NULL,
    responded_by INT(11) DEFAULT NULL,
    response_date DATETIME DEFAULT NULL,
    user_id INT(11) NOT NULL,
    PRIMARY KEY (id),
    KEY fk_responded_by_staff (responded_by),
    KEY fk_user_id_contact_us (user_id),
    CONSTRAINT fk_responded_by_staff FOREIGN KEY (responded_by) REFERENCES staff (id) ON DELETE SET NULL,
    CONSTRAINT fk_user_id_contact_us FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 14. comments (Depends on users, self-referential)
CREATE TABLE comments (
    id INT(11) NOT NULL AUTO_INCREMENT,
    item_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    comment_text TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    content_type ENUM('video', 'audio') NOT NULL DEFAULT 'video',
    updated_at DATETIME DEFAULT NULL,
    parent_comment_id INT(11) DEFAULT NULL,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY parent_comment_id (parent_comment_id),
    CONSTRAINT comments_ibfk_2 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT comments_ibfk_3 FOREIGN KEY (parent_comment_id) REFERENCES comments (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 15. blocked_users (Depends on users)
CREATE TABLE blocked_users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    blocker_id INT(11) NOT NULL,
    blocked_id INT(11) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY blocker_blocked_unique (blocker_id, blocked_id),
    KEY blocker_id (blocker_id),
    KEY blocked_id (blocked_id),
    CONSTRAINT blocked_users_ibfk_1 FOREIGN KEY (blocker_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT blocked_users_ibfk_2 FOREIGN KEY (blocked_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 16. audio_files (Depends on users)
CREATE TABLE audio_files (
    audio_id INT(11) NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    artist VARCHAR(255) DEFAULT NULL,
    album VARCHAR(255) DEFAULT NULL,
    genre VARCHAR(100) DEFAULT NULL,
    upload_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    file_path VARCHAR(500) NOT NULL,
    user_id INT(11) NOT NULL,
    PRIMARY KEY (audio_id),
    KEY user_id (user_id),
    CONSTRAINT audio_files_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 17. content_views (Depends on users)
CREATE TABLE content_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    content_type ENUM('video', 'audio') NOT NULL,
    user_id INT DEFAULT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO users (first_name, last_name, password, email, phone, created_at, profile_picture, membership_tier, video_uploads_left, music_uploads_left, membership_expires_at)
VALUES ('user', '1', '$2y$10$VmybZh3CcK353JPK121.XOn4B6yBldX5P4vYgXCNpppbJ0GEdWu1S', 'user@gmail.com', '01111111111', '2025-04-26 11:00:32', NULL, 0, 0, 0, NULL);

INSERT INTO staff (password, email, is_admin, first_name, last_name)
VALUES ('$2y$10$VuymgceymvLQjlAoudoqsOxEu6HfBoPmaBVW8d69JtqRrOMIOkkxe', 'admin@gmail.com', 1, 'admin', '1');


INSERT INTO audio_files (audio_id, title, artist, album, genre, upload_date, file_path, user_id)
VALUES (30, 'collidee', 'Justine Skye', 'no album', 'Pop', '2025-04-14 11:03:28', 'audio_storage/like that.mp3', 1);

INSERT INTO videos (video_title, video_url, user_id, description, category)
VALUES ('funny', 'video_storage/funny.mp4', 1, 'funny', 'Funny Videos');

INSERT INTO security_questions (question_text)
VALUES ('What is the name of your first pet?');
