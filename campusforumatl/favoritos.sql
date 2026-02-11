CREATE TABLE saved_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_saved_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_saved_post
        FOREIGN KEY (post_id) REFERENCES posts(id)
        ON DELETE CASCADE,

    UNIQUE KEY unique_favorite (user_id, post_id)
);

