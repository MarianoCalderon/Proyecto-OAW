CREATE TABLE IF NOT EXISTS channels (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    feed_url VARCHAR(512) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS  feed_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    channel_id INT NOT NULL,
    title TEXT,
    description TEXT,
    FULLTEXT (description, title),
    link VARCHAR(512) NOT NULL UNIQUE,
    pub_date DATE NOT NULL,
    FOREIGN KEY (channel_id) REFERENCES channels(id) ON DELETE CASCADE
);