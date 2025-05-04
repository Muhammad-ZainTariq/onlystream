<?php
require 'db_connection.php';

function signin_user($pdo, $email) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    return $stmt->fetch();
}

function has_user_liked($pdo, $item_id, $user_id, $content_type) {
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = :user_id AND item_id = :item_id AND content_type = :content_type");
    $stmt->execute([':user_id' => $user_id, ':item_id' => $item_id, ':content_type' => $content_type]);
    return $stmt->fetch() !== false;
}

function get_likes_count($pdo, $item_id, $content_type) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as like_count FROM likes WHERE item_id = :item_id AND content_type = :content_type");
    $stmt->execute([':item_id' => $item_id, ':content_type' => $content_type]);
    $row = $stmt->fetch(); 
    return $row['like_count'];
}

function has_user_followed($pdo, $follower_id, $followed_id) {
    $stmt = $pdo->prepare("SELECT id FROM follows WHERE follower_id = :follower_id AND followed_id = :followed_id");
    $stmt->execute([':follower_id' => $follower_id, ':followed_id' => $followed_id]);
    return $stmt->fetch() !== false;
}

function get_follower_count($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as follower_count FROM follows WHERE followed_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $row = $stmt->fetch(); 
    return $row['follower_count'];
}

function like_item($pdo, $item_id, $user_id, $content_type) {
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = :user_id AND item_id = :item_id AND content_type = :content_type");
    $stmt->execute([':user_id' => $user_id, ':item_id' => $item_id, ':content_type' => $content_type]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = :user_id AND item_id = :item_id AND content_type = :content_type");
        $stmt->execute([':user_id' => $user_id, ':item_id' => $item_id, ':content_type' => $content_type]);
        return "Like removed successfully.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO likes (user_id, item_id, content_type) VALUES (:user_id, :item_id, :content_type)");
        $stmt->execute([':user_id' => $user_id, ':item_id' => $item_id, ':content_type' => $content_type]);
        return "Like added successfully.";
    }
}

function follow_user($pdo, $follower_id, $followed_id) {
    $stmt = $pdo->prepare("SELECT id FROM follows WHERE follower_id = :follower_id AND followed_id = :followed_id");
    $stmt->execute([':follower_id' => $follower_id, ':followed_id' => $followed_id]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("DELETE FROM follows WHERE follower_id = :follower_id AND followed_id = :followed_id");
        $stmt->execute([':follower_id' => $follower_id, ':followed_id' => $followed_id]);
        return "Unfollowed successfully.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO follows (follower_id, followed_id) VALUES (:follower_id, :followed_id)");
        $stmt->execute([':follower_id' => $follower_id, ':followed_id' => $followed_id]);
        return "Followed successfully.";
    }
}

function add_comment($pdo, $item_id, $user_id, $comment_text, $content_type) {
    $stmt = $pdo->prepare("INSERT INTO comments (item_id, user_id, comment_text, content_type, created_at) VALUES (:item_id, :user_id, :comment_text, :content_type, NOW())");
    $stmt->execute([
        ':item_id' => $item_id,
        ':user_id' => $user_id,
        ':comment_text' => $comment_text,
        ':content_type' => $content_type
    ]);
    return "Comment added successfully.";
}


function add_reply($pdo, $item_id, $user_id, $parent_comment_id, $reply_text, $content_type) {
    $stmt = $pdo->prepare("INSERT INTO comments (item_id, user_id, comment_text, content_type, parent_comment_id, created_at) VALUES (:item_id, :user_id, :reply_text, :content_type, :parent_id, NOW())");
    $stmt->execute([
        ':item_id' => $item_id,
        ':user_id' => $user_id,
        ':reply_text' => $reply_text,
        ':content_type' => $content_type,
        ':parent_id' => $parent_comment_id
    ]);
    return "Reply added successfully.";
}


function edit_comment($pdo, $comment_id, $user_id, $new_comment_text, $content_type) {
    $stmt = $pdo->prepare("UPDATE comments SET comment_text = :new_text WHERE id = :comment_id AND user_id = :user_id AND content_type = :content_type");
    $stmt->execute([':new_text' => $new_comment_text, ':comment_id' => $comment_id, ':user_id' => $user_id, ':content_type' => $content_type]);
    return "Comment updated successfully.";
}




function delete_comment($pdo, $comment_id, $user_id, $content_type) {
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = :comment_id AND user_id = :user_id AND content_type = :content_type");
    $stmt->execute([':comment_id' => $comment_id, ':user_id' => $user_id, ':content_type' => $content_type]);
    return "Comment deleted successfully.";
}


function report_item($pdo, $item_id, $user_id, $reason, $details, $item_type) {
    $full_reason = $reason;
    if (!empty($details)) {
        $full_reason .= ': ' . $details;
    }
    $stmt = $pdo->prepare("INSERT INTO reports (reporter_id, item_type, item_id, reason, created_at) VALUES (:user_id, :item_type, :item_id, :reason, NOW())");
    $stmt->execute([
        ':user_id' => $user_id,
        ':item_type' => $item_type,
        ':item_id' => $item_id,
        ':reason' => $full_reason
    ]);
    return "Report submitted successfully.";
}


function share_item($pdo, $item_id, $content_type) {
    $base_url = "http://localhost/onlystream/";
    if ($content_type === 'video') {
        $url = "{$base_url}retreive_videos.php?id={$item_id}";
    } else {
        $url = "{$base_url}display_audio.php?id={$item_id}";
    }
    return "Link copied to clipboard! Paste it anywhere to share";
}


function get_video_categories($pdo) {
    $stmt = $pdo->prepare("SELECT DISTINCT category FROM videos ORDER BY category");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function get_videos($pdo, $current_user_id, $selected_category) {
    $query = "SELECT videos.*, users.first_name, users.last_name, users.profile_picture FROM videos JOIN users ON videos.user_id = users.id";
    $params = [];
    if ($current_user_id) {
        $query .= " WHERE videos.user_id NOT IN (SELECT blocker_id FROM blocked_users WHERE blocked_id = :current_user_id)";
        $params[':current_user_id'] = $current_user_id;
    }
    if ($selected_category) {
        $query .= $current_user_id ? " AND videos.category = :category" : " WHERE videos.category = :category";
        $params[':category'] = $selected_category;
    }
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function is_user_blocked($pdo, $blocker_id, $blocked_id) {
    $stmt = $pdo->prepare("SELECT id FROM blocked_users WHERE blocker_id = :blocker_id AND blocked_id = :blocked_id");
    $stmt->execute([':blocker_id' => $blocker_id, ':blocked_id' => $blocked_id]);
    return $stmt->fetch() !== false;
}

function get_comments($pdo, $item_id, $content_type) {
    $stmt = $pdo->prepare("
        SELECT c.*, u.first_name, u.last_name
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.item_id = :item_id AND c.content_type = :content_type AND c.parent_comment_id IS NULL
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([':item_id' => $item_id, ':content_type' => $content_type]);
    return $stmt->fetchAll();
}

function get_replies($pdo, $parent_comment_id, $content_type) {
    $stmt = $pdo->prepare("
        SELECT c.*, u.first_name, u.last_name
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.parent_comment_id = :parent_id AND c.content_type = :content_type
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([':parent_id' => $parent_comment_id, ':content_type' => $content_type]);
    return $stmt->fetchAll();
}

function mark_message_as_read($pdo, $message_id, $user_id) {
    $stmt = $pdo->prepare("UPDATE messages SET is_read = TRUE WHERE id = :message_id AND receiver_id = :user_id");
    $stmt->execute([':message_id' => $message_id, ':user_id' => $user_id]);
    return "Message marked as read successfully!";
}

function get_user_queries($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT contact_us.*, CONCAT(staff.first_name, ' ', staff.last_name) AS responder_name
        FROM contact_us
        LEFT JOIN staff ON contact_us.responded_by = staff.id
        WHERE contact_us.user_id = :user_id
        ORDER BY contact_us.created_at DESC
    ");
    $stmt->execute([':user_id' => $user_id]);
    return $stmt->fetchAll();
}

function get_report_messages($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT m.*, CONCAT(s.first_name, ' ', s.last_name) AS sender_name
        FROM messages m
        LEFT JOIN staff s ON m.sender_id = s.id
        WHERE m.receiver_id = :user_id
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([':user_id' => $user_id]);
    return $stmt->fetchAll();
}

function get_all_queries($pdo) {
    $stmt = $pdo->prepare("
        SELECT contact_us.*, CONCAT(staff.first_name, ' ', staff.last_name) AS responder_name
        FROM contact_us
        LEFT JOIN staff ON contact_us.responded_by = staff.id
        ORDER BY contact_us.created_at DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function respond_to_query($pdo, $query_id, $staff_id, $response) {
    $response = htmlspecialchars(trim($response));
    if (!$query_id || !$response) {
        return "Query ID or response is missing.";
    }
    $stmt = $pdo->prepare("UPDATE contact_us SET response = :response, responded_by = :staff_id, response_date = NOW() WHERE id = :query_id");
    $stmt->execute([':response' => $response, ':staff_id' => $staff_id, ':query_id' => $query_id]);
    return "Response submitted successfully!";
}

function get_upload_categories() {
    return ['Funny Videos', 'Memes', 'Podcasts', 'MMA', 'Gamingn videos', 'Travel videos', 'Tutorials', 'Discipline vides', 'Music Videos', 'Short Films'];
}

function upload_video($pdo, $user_id, $file, $video_title, $description, $category, $uploadDir = 'video_storage/') {
    $user = get_user_membership($pdo, $user_id);
    if ($user['video_uploads_left'] <= 0) {
        return "You've reached your video upload limit. Please upgrade your membership!";
    }
    $fileName = basename($file['name']);
    $targetFilePath = $uploadDir . $fileName;
    $video_title = trim($video_title);
    $description = trim($description);
    $category = trim($category);
    if (empty($video_title) || empty($description) || empty($category)) {
        return "All fields (title, description, category) are required.";
    }
    if (!in_array($file['type'], ['video/mp4', 'video/avi', 'video/mpeg'])) {
        return "Invalid file type. Please upload a valid video file (MP4, AVI, MPEG).";
    }
    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        $stmt = $pdo->prepare("INSERT INTO videos (video_title, video_url, user_id, description, category) VALUES (:title, :url, :user_id, :desc, :cat)");
        $stmt->execute([':title' => $video_title, ':url' => $targetFilePath, ':user_id' => $user_id, ':desc' => $description, ':cat' => $category]);
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("UPDATE users SET video_uploads_left = video_uploads_left - 1 WHERE id = :user_id");
            $stmt->execute([':user_id' => $user_id]);
            return "Video uploaded successfully!";
        } else {
            return "Failed to save video information into the database.";
        }
    } else {
        return "Failed to upload the video file.";
    }
}
function upload_audio($pdo, $user_id, $file, $title, $artist, $album, $genre, $uploadDir = 'audio_storage/') {
    $user = get_user_membership($pdo, $user_id);
    if ($user['music_uploads_left'] <= 0) {
        return "You've reached your audio upload limit. Please upgrade your membership!";
    }

    $title = trim($title);
    $artist = trim($artist);
    $album = trim($album);
    $genre = trim($genre);
    if (empty($title) || empty($genre)) {
        return "Title and genre are required.";
    }

    $fileName = basename($file['name']);
    $targetFilePath = $uploadDir . $fileName;

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav', 'audio/wave']; /* The MIME types used for audio file validation were referenced from  https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/MIME_types/Common_types */
    if (!in_array($mimeType, $allowedTypes)) {
        return "Invalid file type. Please upload a valid audio file (MP3, WAV).";
    }

    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        $stmt = $pdo->prepare("
            INSERT INTO audio_files (title, artist, album, genre, file_path, user_id) 
            VALUES (:title, :artist, :album, :genre, :file_path, :user_id)
        ");
        $stmt->execute([
            ':title' => $title,
            ':artist' => $artist ?: null,
            ':album' => $album ?: null,
            ':genre' => $genre,
            ':file_path' => $targetFilePath,
            ':user_id' => $user_id
        ]);

        if ($stmt->rowCount() > 0) {
            $pdo->prepare("UPDATE users SET music_uploads_left = music_uploads_left - 1 WHERE id = :user_id")
                ->execute([':user_id' => $user_id]);
            return true;
        }
        return "Failed to save audio information into the database.";
    }
    return "Failed to upload the audio file.";
}
function get_audio_genres($pdo) {
    $stmt = $pdo->prepare("SELECT DISTINCT genre FROM audio_files WHERE genre IS NOT NULL AND genre != '' ORDER BY genre");
    $stmt->execute();
    $genres = $stmt->fetchAll();
    $genre_list = [];
    foreach ($genres as $row) {
        $genre_list[] = $row['genre'];
    }
    return $genre_list;
}

function get_predefined_audio_genres() {
    return ['Pop', 'Bollywood', 'Jazz', 'Classical', 'Hip-Hop'];
}


function get_audio_files($pdo, $current_user_id, $selected_genre) {
    $query = "SELECT audio_files.*, users.first_name, users.last_name, users.profile_picture FROM audio_files JOIN users ON audio_files.user_id = users.id";
    $params = [];
    if ($current_user_id) {
        $query .= " WHERE audio_files.user_id NOT IN (SELECT blocker_id FROM blocked_users WHERE blocked_id = :current_user_id)";
        $params[':current_user_id'] = $current_user_id;
    }
    if ($selected_genre) {
        $query .= $current_user_id ? " AND audio_files.genre = :genre" : " WHERE audio_files.genre = :genre";
        $params[':genre'] = $selected_genre;
    }
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_security_questions($pdo) {
    $stmt = $pdo->query("SELECT question_id, question_text FROM security_questions");
    return $stmt->fetchAll();
}

function validate_password($password, $confirm_password) {
    $errors = [];
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one digit.";
    }
    if (strpos($password, '@') === false) {
        $errors[] = "Password must contain the @ symbol.";
    }
    return $errors;
}

function signup_staff($pdo, $first_name, $last_name, $email, $password, $security_question_id, $security_answer) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $answer_hash = password_hash($security_answer, PASSWORD_DEFAULT);
    $is_admin = 0;

    $stmt = $pdo->prepare("
        INSERT INTO staff_requests (first_name, last_name, email, password, is_admin, question_id, answer_hash) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    if ($stmt->execute([$first_name, $last_name, $email, $password_hash, $is_admin, $security_question_id, $answer_hash])) {
        return "Your request has been submitted and is pending admin approval.";
    } else {
        return "Staff signup failed. Please try again.";
    }
}

function signup_user($pdo, $first_name, $last_name, $email, $phone, $password, $security_question_id, $security_answer) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $answer_hash = password_hash($security_answer, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, password, email, phone) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$first_name, $last_name, $password_hash, $email, $phone])) {
        $user_id = $pdo->lastInsertId();
        $stmt2 = $pdo->prepare("INSERT INTO user_security_answers (user_id, question_id, answer_hash) VALUES (?, ?, ?)");
        if ($stmt2->execute([$user_id, $security_question_id, $answer_hash])) {
            return "Sign up successful! Please log in.";
        } else {
            return "Failed to save security question—please try again.";
        }
    } else {
        return "Sign up failed—please try again.";
    }
}

function signin_staff($pdo, $email, $password) {
    $stmt = $pdo->prepare("SELECT * FROM staff WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $staff = $stmt->fetch();
    
    if ($staff && password_verify($password, $staff['password'])) {
        $_SESSION['staff_id'] = $staff['id'];
        $_SESSION['staff_first_name'] = $staff['first_name'];
        $_SESSION['staff_last_name'] = $staff['last_name'];
        $_SESSION['is_admin'] = $staff['is_admin'];
        return "Logged in successfully!";
    } else {
        return "Invalid email or password.";
    }
}

function get_user_profile($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT first_name, last_name, email, profile_picture FROM users WHERE id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    return $stmt->fetch();
}

function update_user_profile($pdo, $user_id, $first_name, $last_name, $email, $new_password, $pictureBlob) {
    $sql = "UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email";
    $params = [
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':email' => $email,
        ':user_id' => $user_id
    ];

    if (!empty($new_password)) {
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $sql .= ", password = :password";
        $params[':password'] = $password_hash;
    }

    if ($pictureBlob !== null) {
        $sql .= ", profile_picture = :picture";
        $params[':picture'] = $pictureBlob;
    }

    $sql .= " WHERE id = :user_id";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($params)) {
        return "Profile updated successfully.";
    } else {
        return "Failed to update profile.";
    }
}

function search_content($pdo, $searchQuery) {
    $searchQuery = "%" . trim($searchQuery) . "%";
    $results = [];

    $sqlVideos = "SELECT id AS item_id, video_title AS title, 'Video' AS type FROM videos WHERE video_title LIKE :query";
    $stmtVideos = $pdo->prepare($sqlVideos);
    $stmtVideos->execute([':query' => $searchQuery]);
    $videoResults = $stmtVideos->fetchAll();
    foreach ($videoResults as $video) {
        $results[] = $video;
    }

    $sqlAudio = "SELECT audio_id AS item_id, title AS title, 'Audio' AS type FROM audio_files WHERE title LIKE :query";
    $stmtAudio = $pdo->prepare($sqlAudio);
    $stmtAudio->execute([':query' => $searchQuery]);
    $audioResults = $stmtAudio->fetchAll();
    foreach ($audioResults as $audio) {
        $results[] = $audio;
    }

    return $results;
}


function check_password_match($new_password, $confirm_password) {
    return $new_password === $confirm_password ? "" : "Passwords do not match. Please try again.";
}

function verify_security_answer($pdo, $user_id, $question_id, $provided_answer, $table) {
    $stmt = $pdo->prepare("SELECT answer_hash FROM $table WHERE " . ($table === 'user_security_answers' ? 'user_id' : 'staff_id') . " = ? AND question_id = ?");
    $stmt->execute([$user_id, $question_id]);
    $result = $stmt->fetch();
    
    if ($result && password_verify($provided_answer, $result['answer_hash'])) {
        return true;
    } else {
        return "Security answer is incorrect.";
    }
}

function update_password($pdo, $user_id, $new_password, $table) {
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE $table SET password = ? WHERE id = ?");
    if ($stmt->execute([$new_password_hash, $user_id])) {
        return "Password updated successfully! Please log in.";
    } else {
        return "Failed to update password. Please try again later.";
    }
}



function get_video_likes($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as likes FROM likes WHERE content_type = 'video' AND item_id IN (SELECT id FROM videos WHERE user_id = ?)");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    if ($result) {
        return $result['likes'];
    }
    return 0;
}

function get_audio_likes($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as likes FROM likes WHERE content_type = 'audio' AND item_id IN (SELECT audio_id FROM audio_files WHERE user_id = ?)");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    if ($result) {
        return $result['likes'];
    }
    return 0;
}

function get_latest_payment($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT tier, name_on_card, address, phone_number, postcode, created_at FROM user_payments WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result ?: null;
}
function get_user_content($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT id, video_title AS title, 'video' AS type, video_url AS media_path FROM videos WHERE user_id = ?
        UNION
        SELECT audio_id AS id, title, 'audio' AS type, file_path AS media_path FROM audio_files WHERE user_id = ?
        ORDER BY title
    ");
    $stmt->execute([$user_id, $user_id]);
    return $stmt->fetchAll() ?: [];
}

function delete_content($pdo, $user_id, $content_id, $content_type) {
    if ($content_type === 'video') {
        $stmt = $pdo->prepare("SELECT video_url AS file_path FROM videos WHERE id = ? AND user_id = ?");
        $delete_stmt = $pdo->prepare("DELETE FROM videos WHERE id = ? AND user_id = ?");
    } else {
        $stmt = $pdo->prepare("SELECT file_path FROM audio_files WHERE audio_id = ? AND user_id = ?");
        $delete_stmt = $pdo->prepare("DELETE FROM audio_files WHERE audio_id = ? AND user_id = ?");
    }
    $stmt->execute([$content_id, $user_id]);
    $file = $stmt->fetch();
    if ($file && file_exists(__DIR__ . '/' . $file['file_path'])) {
        unlink(__DIR__ . '/' . $file['file_path']);
    }
    $delete_stmt->execute([$content_id, $user_id]);
}

function get_user_membership($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT membership_tier, membership_expires_at, video_uploads_left, music_uploads_left FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    if ($result) {
        return $result;
    }
    return null;
}


function edit_content($pdo, $user_id, $content_id, $content_type, $new_title) {
    if ($content_type === 'video') {
        $stmt = $pdo->prepare("UPDATE videos SET video_title = ? WHERE id = ? AND user_id = ?");
    } else {
        $stmt = $pdo->prepare("UPDATE audio_files SET title = ? WHERE audio_id = ? AND user_id = ?");
    }
    $stmt->execute([$new_title, $content_id, $user_id]);
}

function block_user($pdo, $blocker_id, $blocked_id) {
    $stmt = $pdo->prepare("SELECT id FROM blocked_users WHERE blocker_id = ? AND blocked_id = ?");
    $stmt->execute([$blocker_id, $blocked_id]);
    if ($stmt->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO blocked_users (blocker_id, blocked_id) VALUES (?, ?)");
        $stmt->execute([$blocker_id, $blocked_id]);
    }
}

function unblock_user($pdo, $blocker_id, $blocked_id) {
    $stmt = $pdo->prepare("DELETE FROM blocked_users WHERE blocker_id = ? AND blocked_id = ?");
    $stmt->execute([$blocker_id, $blocked_id]);
}

function add_reply_to_comment($pdo, $user_id, $comment_id, $reply_text) {
    $reply_text = trim($reply_text);
    if (!$reply_text) {
        return "Reply cannot be empty.";
    }
    $stmt = $pdo->prepare("SELECT item_id, content_type, parent_comment_id FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);
    $parent = $stmt->fetch();
    if (!$parent) {
        return "Parent comment not found.";
    }
    if ($parent['parent_comment_id'] !== null) {
        return "Cannot reply to a reply.";
    }
    $stmt = $pdo->prepare("
        INSERT INTO comments (user_id, item_id, content_type, comment_text, parent_comment_id, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$user_id, $parent['item_id'], $parent['content_type'], $reply_text, $comment_id]);
    if ($stmt->rowCount() > 0) {
        return "Reply added successfully.";
    }
    return "Failed to add reply.";
}


function get_comments_on_user_content($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT c.*, u.first_name, u.last_name, 
               v.video_title AS video_title, a.title AS audio_title
        FROM comments c
        JOIN users u ON c.user_id = u.id
        LEFT JOIN videos v ON c.content_type = 'video' AND c.item_id = v.id AND v.user_id = ?
        LEFT JOIN audio_files a ON c.content_type = 'audio' AND c.item_id = a.audio_id AND a.user_id = ?
        WHERE (v.user_id = ? OR a.user_id = ?) AND c.parent_comment_id IS NULL
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$user_id, $user_id, $user_id, $user_id]);
    $comments = $stmt->fetchAll();
    if ($comments) {
        return $comments;
    }
    return array();
}


function get_replies_on_user_content($pdo, $user_id, $parent_comment_id) {
    $stmt = $pdo->prepare("
        SELECT c.*, u.first_name, u.last_name
        FROM comments c
        JOIN users u ON c.user_id = u.id
        LEFT JOIN videos v ON c.content_type = 'video' AND c.item_id = v.id AND v.user_id = ?
        LEFT JOIN audio_files a ON c.content_type = 'audio' AND c.item_id = a.audio_id AND a.user_id = ?
        WHERE c.parent_comment_id = ? AND (v.user_id = ? OR a.user_id = ?)
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([$user_id, $user_id, $parent_comment_id, $user_id, $user_id]);
    $replies = $stmt->fetchAll();
    if ($replies) {
        return $replies;
    }
    return array();
}

function delete_user_content_comment($pdo, $user_id, $comment_id) {
    $stmt = $pdo->prepare("
        SELECT c.id
        FROM comments c
        LEFT JOIN videos v ON c.content_type = 'video' AND c.item_id = v.id AND v.user_id = ?
        LEFT JOIN audio_files a ON c.content_type = 'audio' AND c.item_id = a.audio_id AND a.user_id = ?
        WHERE c.id = ? AND (v.user_id = ? OR a.user_id = ?)
    ");
    $stmt->execute([$user_id, $user_id, $comment_id, $user_id, $user_id]);
    if (!$stmt->fetch()) {
        return "Comment not found or not on your content.";
    }
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);
    return "Comment deleted successfully.";
}


function get_blocked_users($pdo, $blocker_id) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.first_name, u.last_name
        FROM blocked_users bu
        JOIN users u ON bu.blocked_id = u.id
        WHERE bu.blocker_id = ?
    ");
    $stmt->execute([$blocker_id]);
    return $stmt->fetchAll();
}



function find_account_by_email($pdo, $email, $table = 'users') {
    $stmt = $pdo->prepare("SELECT id, first_name FROM $table WHERE email = :email");
    $stmt->execute([':email' => $email]);
    return $stmt->fetch();
}

function get_account_security_question($pdo, $account_id, $table = 'users') {
    $id_field = $table === 'users' ? 'user_id' : 'staff_id';
    $stmt = $pdo->prepare("
        SELECT sa.question_id, sq.question_text 
        FROM " . ($table === 'users' ? 'user_security_answers' : 'staff_security') . " sa
        JOIN security_questions sq ON sa.question_id = sq.question_id 
        WHERE sa.$id_field = :id
    ");
    $stmt->execute([':id' => $account_id]);
    return $stmt->fetch();
}


function require_login($error_message = "Please log in.") {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = $error_message;
        header("Location: login.php");
        exit();
    }
    return $_SESSION['user_id'];
}

function check_membership_expiration($pdo, $user_id, $tier, $expires_at, $redirect = false) {
    if ($expires_at && new DateTime() > new DateTime($expires_at)) {
        $stmt = $pdo->prepare("UPDATE users SET membership_tier = 0, video_uploads_left = 0, music_uploads_left = 0, membership_expires_at = NULL WHERE id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['message'] = "Your membership has expired. Please renew it!";
        if ($redirect) {
            header("Location: membership.php");
            exit();
        }
    }
}


function delete_user($pdo, $user_id) {
    if (!$user_id) {
        return "Invalid user ID.";
    }
    $pdo->prepare("DELETE FROM audio_files WHERE user_id = ?")->execute([$user_id]);
    $pdo->prepare("DELETE FROM videos WHERE user_id = ?")->execute([$user_id]);
    $pdo->prepare("DELETE FROM comments WHERE user_id = ?")->execute([$user_id]);
    $pdo->prepare("DELETE FROM likes WHERE user_id = ?")->execute([$user_id]);
    $pdo->prepare("DELETE FROM reports WHERE reporter_id = ?")->execute([$user_id]);
    $pdo->prepare("DELETE FROM follows WHERE follower_id = ? OR followed_id = ?")->execute([$user_id, $user_id]);
    $pdo->prepare("DELETE FROM messages WHERE sender_id = ? OR receiver_id = ?")->execute([$user_id, $user_id]);
    $pdo->prepare("DELETE FROM user_payments WHERE user_id = ?")->execute([$user_id]);
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    if ($stmt->rowCount() > 0) {
        return "User deleted successfully.";
    }
    return "Failed to delete user.";
}

function delete_staff($pdo, $staff_id, $current_staff_id) {
    if (!$staff_id) {
        return "Invalid staff ID.";
    }
    if ($staff_id === $current_staff_id) {
        return "You cannot delete your own admin account.";
    }
    $pdo->prepare("DELETE FROM messages WHERE sender_id = ? OR receiver_id = ?")->execute([$staff_id, $staff_id]);
    $stmt = $pdo->prepare("DELETE FROM staff WHERE id = ?");
    $stmt->execute([$staff_id]);
    if ($stmt->rowCount() > 0) {
        return "Staff member deleted successfully.";
    }
    return "Failed to delete staff.";
}

function get_users_with_counts($pdo) {
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll();
    $userData = [];
    foreach ($users as $user) {
        $user_id = $user['id'];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM videos WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $video_count = $stmt->fetchColumn();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM audio_files WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $audio_count = $stmt->fetchColumn();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $comment_count = $stmt->fetchColumn();
        $userData[] = [
            'user' => $user,
            'video_count' => $video_count,
            'audio_count' => $audio_count,
            'comment_count' => $comment_count
        ];
    }
    return $userData;
}

function get_staff_with_counts($pdo) {
    $stmt = $pdo->query("SELECT id, first_name, last_name, email FROM staff");
    $staffMembers = $stmt->fetchAll();
    $staffData = [];
    foreach ($staffMembers as $staff) {
        $staff_id = $staff['id'];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE sender_id = ? OR receiver_id = ?");
        $stmt->execute([$staff_id, $staff_id]);
        $message_count = $stmt->fetchColumn();
        $staffData[] = [
            'staff' => [
                'id' => $staff['id'],
                'name' => $staff['first_name'] . ' ' . $staff['last_name'],
                'email' => $staff['email']
            ],
            'message_count' => $message_count
        ];
    }
    return $staffData;
}

function submit_contact_query($pdo, $user_id, $name, $email, $contact_number, $query) {
    $name = htmlspecialchars(trim($name));
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    $contact_number = htmlspecialchars(trim($contact_number));
    $query = htmlspecialchars(trim($query));

    if ($name && $email && $query) {
        $stmt = $pdo->prepare("INSERT INTO contact_us (user_id, name, email, contact_number, query) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $name, $email, $contact_number, $query]);
        return "Your query has been submitted. Thank you for reaching out!";
    } else {
        return "Please fill out all required fields with valid information.";
    }
}


function get_staff_name($pdo, $staff_id) {
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM staff WHERE id = ?");
    $stmt->execute([$staff_id]);
    $staff = $stmt->fetch();
    if ($staff) {
        return htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']);
    }
    return '';
}

function get_all_videos($pdo) {
    $stmt = $pdo->query("SELECT * FROM videos");
    $videos = $stmt->fetchAll();
    return $videos;
}

function get_all_audio_files($pdo) {
    $stmt = $pdo->query("SELECT * FROM audio_files");
    $audio_files = $stmt->fetchAll();
    return $audio_files;
}

function delete_video($pdo, $video_id) {
    if (!$video_id) {
        return "Invalid video ID.";
    }
    $stmt = $pdo->prepare("SELECT video_url FROM videos WHERE id = ?");
    $stmt->execute([$video_id]);
    $video = $stmt->fetch();
    if ($video && file_exists(__DIR__ . '/' . $video['video_url'])) {
        unlink(__DIR__ . '/' . $video['video_url']);
    }
    $pdo->prepare("DELETE FROM likes WHERE item_id = ? AND content_type = 'video'")->execute([$video_id]);
    $pdo->prepare("DELETE FROM comments WHERE item_id = ? AND content_type = 'video'")->execute([$video_id]);
    $pdo->prepare("DELETE FROM reports WHERE item_id = ? AND item_type = 'video'")->execute([$video_id]);
    $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
    $stmt->execute([$video_id]);
    return "Video deleted successfully.";
}

function delete_audio($pdo, $audio_id) {
    if (!$audio_id) {
        return "Invalid audio ID.";
    }
    $stmt = $pdo->prepare("SELECT file_path FROM audio_files WHERE audio_id = ?");
    $stmt->execute([$audio_id]);
    $audio = $stmt->fetch();
    if ($audio && file_exists(__DIR__ . '/' . $audio['file_path'])) {
        unlink(__DIR__ . '/' . $audio['file_path']);
    }
    $pdo->prepare("DELETE FROM likes WHERE item_id = ? AND content_type = 'audio'")->execute([$audio_id]);
    $pdo->prepare("DELETE FROM comments WHERE item_id = ? AND content_type = 'audio'")->execute([$audio_id]);
    $pdo->prepare("DELETE FROM reports WHERE item_id = ? AND item_type = 'audio'")->execute([$audio_id]);
    $stmt = $pdo->prepare("DELETE FROM audio_files WHERE audio_id = ?");
    $stmt->execute([$audio_id]);
    return " Audio deleted successfully.";
}


function delete_admin_comment($pdo, $comment_id, $content_id, $content_type) {
    if (!$comment_id || !$content_id || !$content_type) {
        return "Invalid comment or content details.";
    }
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);
    return "Comment deleted successfully.";
}

function get_content_comments($pdo, $content_id, $content_type) {
    $stmt = $pdo->prepare("
        SELECT comments.*, users.first_name, users.last_name, 
               parent_comments.comment_text AS parent_comment_text
        FROM comments 
        JOIN users ON comments.user_id = users.id 
        LEFT JOIN comments AS parent_comments ON comments.parent_comment_id = parent_comments.id
        WHERE comments.item_id = ? AND comments.content_type = ?
    ");
    $stmt->execute([$content_id, $content_type]);
    $comments = $stmt->fetchAll();
    if ($comments) {
        return $comments;
    }
    return array();
}




function delete_reported_item($pdo, $item_id, $item_type) {
    
    if (!$item_id) {
        return "Invalid item ID.";
    }

    $table = '';
    $id_field = '';
    $file_field = '';
    $content_type = '';
    if ($item_type === 'video') {
        $table = 'videos';
        $id_field = 'id';
        $file_field = 'video_url';
        $content_type = 'video';
    }
    else {
        $table = 'audio_files';
        $id_field = 'audio_id';
        $file_field = 'file_path';
        $content_type = 'audio';
    }
    
    $stmt = $pdo->prepare("SELECT $file_field FROM $table WHERE $id_field = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();
    if ($item) {
        $file_path = __DIR__ . '/' . $item[$file_field];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    $stmt = $pdo->prepare("DELETE FROM comments WHERE item_id = ? AND content_type = ?");
    $stmt->execute([$item_id, $content_type]);
    
    $stmt = $pdo->prepare("DELETE FROM likes WHERE item_id = ? AND content_type = ?");
    $stmt->execute([$item_id, $content_type]);
    
    $stmt = $pdo->prepare("DELETE FROM reports WHERE item_id = ? AND item_type = ?");
    $stmt->execute([$item_id, $item_type]);
    
    $stmt = $pdo->prepare("DELETE FROM $table WHERE $id_field = ?");
    $stmt->execute([$item_id]);
    
    if ($stmt->rowCount() > 0) {
        return "Item deleted successfully.";
    }
    else {
        return "Failed to delete item.";
    }
}

function send_admin_message($pdo, $sender_id, $receiver_id, $message_text) {
    if (empty($message_text)) {
        return "Message cannot be empty.";
    }

    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message_text, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$sender_id, $receiver_id, $message_text]);

    if ($stmt->rowCount() > 0) {
        return "Message sent successfully.";
    }
    else {
        return "Failed to send message.";
    }
}

function update_report_status($pdo, $report_id, $new_status) {
    if (!$report_id) {
        return "Invalid report ID.";
    }
    $stmt = $pdo->prepare("UPDATE reports SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $report_id]);

    if ($stmt->rowCount() > 0) {
        return "Report status updated successfully.";
    }
    else {
        return "Failed to update report status.";
    }
}


function get_reported_items($pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN r.item_type = 'video' THEN v.id 
                WHEN r.item_type = 'music' THEN a.audio_id 
            END AS item_id,
            CASE 
                WHEN r.item_type = 'video' THEN v.video_title 
                WHEN r.item_type = 'music' THEN a.title 
            END AS title,
            CASE 
                WHEN r.item_type = 'video' THEN v.video_url 
                WHEN r.item_type = 'music' THEN a.file_path 
            END AS url,
            CASE 
                WHEN r.item_type = 'video' THEN v.user_id 
                WHEN r.item_type = 'music' THEN a.user_id 
            END AS uploader_id,
            u.first_name AS uploader_first_name, 
            u.last_name AS uploader_last_name,
            r.id AS report_id, 
            r.reporter_id, 
            r.reason, 
            r.created_at AS report_created_at,
            ru.first_name AS reporter_first_name, 
            ru.last_name AS reporter_last_name,
            r.status AS report_status,
            r.item_type
        FROM reports r
        LEFT JOIN videos v ON r.item_type = 'video' AND r.item_id = v.id
        LEFT JOIN audio_files a ON r.item_type = 'music' AND r.item_id = a.audio_id
        JOIN users u ON (r.item_type = 'video' AND v.user_id = u.id) OR (r.item_type = 'music' AND a.user_id = u.id)
        JOIN users ru ON r.reporter_id = ru.id
        WHERE r.status = 'pending'
        ORDER BY r.created_at DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll() ?: [];
}


function is_staff_admin($pdo, $staff_id) {
    $stmt = $pdo->prepare("SELECT is_admin FROM staff WHERE id = ?");
    $stmt->execute([$staff_id]);
    $staff = $stmt->fetch(); 
    return $staff && $staff['is_admin'] == 1;
}


function get_pending_staff_requests($pdo) {
    $stmt = $pdo->query("SELECT * FROM staff_requests ORDER BY created_at DESC");
    
    if ($stmt) {
        $rows = $stmt->fetchAll();
        return $rows;
    }
    else {
        return array();
    }
}

function approve_staff_request($pdo, $request_id) {
    if (!$request_id) {
        return "Invalid request ID.";
    }

    $stmt = $pdo->prepare("SELECT * FROM staff_requests WHERE id = ?");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch();

    if (!$request) {
        return "Request not found.";
    }

    $stmt = $pdo->prepare("
        INSERT INTO staff (first_name, last_name, email, password, created_at, is_admin) 
        VALUES (?, ?, ?, ?, NOW(), 0)
    ");
    $stmt->execute([
        $request['first_name'],
        $request['last_name'],
        $request['email'],
        $request['password']
    ]);
    if ($stmt->rowCount() == 0) {
        return "Failed to approve staff.";
    }
    $new_staff_id = $pdo->lastInsertId();

   
    if (!empty($request['question_id']) && !empty($request['answer_hash'])) {
        $stmt = $pdo->prepare("
            INSERT INTO staff_security (staff_id, question_id, answer_hash) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$new_staff_id, $request['question_id'], $request['answer_hash']]);
        if ($stmt->rowCount() == 0) {
            return "Failed to set security question.";
        }
    }


    $stmt = $pdo->prepare("DELETE FROM staff_requests WHERE id = ?");
    $stmt->execute([$request_id]);
    
    return "Staff approved successfully.";
}
function reject_staff_request($pdo, $request_id) {
    if (!$request_id) {
        return "Invalid request ID.";
    }
    $stmt = $pdo->prepare("DELETE FROM staff_requests WHERE id = ?");
    $stmt->execute([$request_id]);
    return $stmt->rowCount() > 0 ? "Staff request rejected successfully!" : "Failed to reject staff request.";
}



function log_content_view($pdo, $item_id, $content_type, $user_id = null) {
    
    $stmt = $pdo->prepare("INSERT INTO content_views (item_id, content_type, user_id, viewed_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$item_id, $content_type, $user_id]);
    return true;
}

function get_item_views($pdo, $item_id, $content_type) {
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM content_views WHERE item_id = ? AND content_type = ?");
    $stmt->execute([$item_id, $content_type]);
    return $stmt->fetchColumn();
}
function get_creator_analytics($pdo, $user_id) {
    $analytics = [
        'views' => [
            ['content_type' => 'video', 'views' => 0],
            ['content_type' => 'audio', 'views' => 0]
        ],
        'video_likes' => 0,
        'audio_likes' => 0,
        'comments' => 0,
        'followers' => 0
    ];

   
    $stmt = $pdo->prepare("
        SELECT cv.content_type, COUNT(*) as views
        FROM content_views cv
        JOIN videos v ON cv.item_id = v.id AND cv.content_type = 'video' AND v.user_id = ?
        UNION
        SELECT cv.content_type, COUNT(*) as views
        FROM content_views cv
        JOIN audio_files a ON cv.item_id = a.audio_id AND cv.content_type = 'audio' AND a.user_id = ?
    ");
    $stmt->execute([$user_id, $user_id]);
    $views = $stmt->fetchAll(); 


    foreach ($views as $view) {
        foreach ($analytics['views'] as &$analytic_view) {
            if ($analytic_view['content_type'] === $view[0]) { 
                $analytic_view['views'] = $view[1]; 
            }
        }
    }

    $analytics['video_likes'] = get_video_likes($pdo, $user_id);
    $analytics['audio_likes'] = get_audio_likes($pdo, $user_id);

    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as comment_count
        FROM comments c
        JOIN videos v ON c.item_id = v.id AND c.content_type = 'video' AND v.user_id = ?
        UNION
        SELECT COUNT(*) as comment_count
        FROM comments c
        JOIN audio_files a ON c.item_id = a.audio_id AND c.content_type = 'audio' AND a.user_id = ?
    ");
    $stmt->execute([$user_id, $user_id]);
    $comment_counts = $stmt->fetchAll(); 
    $total_comments = 0;
    foreach ($comment_counts as $count) {
        $total_comments += $count[0]; 
    }
    $analytics['comments'] = $total_comments;

    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE followed_id = ?");
    $stmt->execute([$user_id]);
    $follower_count = $stmt->fetch();
    $analytics['followers'] = $follower_count[0]; 

    return $analytics;
}