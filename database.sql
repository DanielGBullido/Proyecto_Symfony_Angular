CREATE DATABASE IF NOT EXISTS videos_application;
USE videos_application;

CREATE TABLE users (
  id        INT(255) AUTO_INCREMENT NOT NULL,
  role      VARCHAR(20),
  name      VARCHAR(255),
  surname   VARCHAR(255),
  email     VARCHAR(255),
  password  VARCHAR(255),
  image     VARCHAR(255),
  create_at DATETIME,
  CONSTRAINT pk_users PRIMARY KEY (id)
)
  ENGINE = InnoDb;

CREATE TABLE videos (
  id          INT(255) AUTO_INCREMENT NOT NULL,
  user_id     INT(255)                NOT NULL,
  title       VARCHAR(255),
  description TEXT,
  status      VARCHAR(20),
  image       VARCHAR(255),
  video_path  VARCHAR(255),
  create_at   DATETIME DEFAULT NULL,
  update_at   DATETIME DEFAULT NULL,
  CONSTRAINT pk_videos PRIMARY KEY (id),
  CONSTRAINT fk_videos_users FOREIGN KEY (user_id) REFERENCES users (id)
)
  ENGINE = InnoDb;

CREATE TABLE comments (
  id        INT(255) AUTO_INCREMENT NOT NULL,
  video_id  INT(255)                NOT NULL,
  user_id   INT(255)                NOT NULL,
  body      TEXT,
  create_at DATETIME DEFAULT NULL,
  CONSTRAINT pk_comment PRIMARY KEY (id),
  CONSTRAINT fk_comments_video FOREIGN KEY (video_id) REFERENCES videos (id),
  CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users (id)
)
  ENGINE = InnoDb;