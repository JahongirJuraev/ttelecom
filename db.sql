-- ============================================================
--  TT Map — Полная схема БД (v5)
--  Запустить: mysql -u root < db.sql
--  Если БД уже есть — запустить только блок "НОВЫЕ ТАБЛИЦЫ"
-- ============================================================

CREATE DATABASE IF NOT EXISTS telecom_map
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE telecom_map;

-- ── СУЩЕСТВУЮЩАЯ ТАБЛИЦА (из v4) ──────────────────────────
CREATE TABLE IF NOT EXISTS objects (
    id            INT           NOT NULL AUTO_INCREMENT PRIMARY KEY,
    type          VARCHAR(20)   NOT NULL DEFAULT 'other',
    address       TEXT          NOT NULL,
    internet_type VARCHAR(10)   NOT NULL DEFAULT 'нет',
    custom_date   DATE          NULL,
    lat           DECIMAL(10,8) NOT NULL DEFAULT 0,
    lng           DECIMAL(11,8) NOT NULL DEFAULT 0,
    photo         VARCHAR(255)  NULL,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── НОВЫЕ ТАБЛИЦЫ (муфты) ─────────────────────────────────

-- 1. Муфты сети (точки на карте с буквой M)
CREATE TABLE IF NOT EXISTS joints (
    id         INT           NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)  NOT NULL,
    lat        DECIMAL(10,8) NOT NULL,
    lng        DECIMAL(11,8) NOT NULL,
    address    TEXT          NULL,
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Кабели (входят в муфту)
CREATE TABLE IF NOT EXISTS cables (
    id          INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    joint_id    INT          NOT NULL,
    name        VARCHAR(100) NOT NULL,
    fiber_count INT          NOT NULL DEFAULT 48,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (joint_id) REFERENCES joints(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Модули (трубки внутри кабеля, 8 жил каждый)
CREATE TABLE IF NOT EXISTS modules (
    id        INT         NOT NULL AUTO_INCREMENT PRIMARY KEY,
    cable_id  INT         NOT NULL,
    number    INT         NOT NULL,
    color     VARCHAR(20) NOT NULL,
    color_hex VARCHAR(7)  NOT NULL,
    name      VARCHAR(50) NULL,
    FOREIGN KEY (cable_id) REFERENCES cables(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Жилы (TIA-598, статус: free/used/damaged)
CREATE TABLE IF NOT EXISTS fibers (
    id        INT         NOT NULL AUTO_INCREMENT PRIMARY KEY,
    module_id INT         NOT NULL,
    cable_id  INT         NOT NULL,
    number    INT         NOT NULL,
    color     VARCHAR(20) NOT NULL,
    color_hex VARCHAR(7)  NOT NULL,
    status    ENUM('free','used','damaged') NOT NULL DEFAULT 'free',
    notes     TEXT        NULL,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    FOREIGN KEY (cable_id)  REFERENCES cables(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Стороны/направления муфты (Дом А, К Муфте-3...)
CREATE TABLE IF NOT EXISTS sides (
    id              INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    joint_id        INT          NOT NULL,
    name            VARCHAR(100) NOT NULL,
    linked_joint_id INT          NULL,
    position_angle  FLOAT        NULL,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (joint_id)        REFERENCES joints(id) ON DELETE CASCADE,
    FOREIGN KEY (linked_joint_id) REFERENCES joints(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Жилы назначенные на сторону
CREATE TABLE IF NOT EXISTS side_fibers (
    id       INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    side_id  INT NOT NULL,
    fiber_id INT NOT NULL,
    UNIQUE KEY uq_fiber (fiber_id),
    FOREIGN KEY (side_id)  REFERENCES sides(id)  ON DELETE CASCADE,
    FOREIGN KEY (fiber_id) REFERENCES fibers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Соединения жил внутри муфты
CREATE TABLE IF NOT EXISTS connections (
    id           INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    joint_id     INT NOT NULL,
    fiber_id_in  INT NOT NULL,
    fiber_id_out INT NOT NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_conn_in  (fiber_id_in),
    UNIQUE KEY uq_conn_out (fiber_id_out),
    FOREIGN KEY (joint_id)     REFERENCES joints(id) ON DELETE CASCADE,
    FOREIGN KEY (fiber_id_in)  REFERENCES fibers(id) ON DELETE CASCADE,
    FOREIGN KEY (fiber_id_out) REFERENCES fibers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── ТЕСТОВЫЕ ДАННЫЕ ───────────────────────────────────────
INSERT IGNORE INTO joints (id, name, lat, lng, address) VALUES
(1, 'Муфта №1 (Центр)',  40.2850, 69.6320, 'ул. Ленина 5'),
(2, 'Муфта №2 (Север)',  40.2900, 69.6350, 'пр. Истиклол 12'),
(3, 'Муфта №3 (Восток)', 40.2830, 69.6400, 'ул. Садриддин 3');
