-- ============================================
-- Esame di Stato - Academy Aziendale
-- Versione per PostgreSQL (Supabase)
-- ============================================

-- ============================================
-- 1. Tipo Enum per i ruoli e stati
-- ============================================
CREATE TYPE user_role AS ENUM ('dipendente', 'referente');
CREATE TYPE stato_assegnazione AS ENUM ('Assegnato', 'Completato', 'Scaduto', 'Annullato');

-- ============================================
-- 2. Tabella: users (utenti)
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role user_role NOT NULL DEFAULT 'dipendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- Utenti di test
-- ============================================
-- Admin (referente), pwd: admin123
INSERT INTO users (nome, cognome, email, password, role) VALUES (
    'Mario', 'Rossi', 'admin@academy.it', '$2y$10$yakGqvIeIBGcKYeL8XedAucrzEWYcZqp5kcFWuVOgtp6XxKKNtY2K', 'referente'
);
-- User (dipendente), pwd: admin123
INSERT INTO users (nome, cognome, email, password, role) VALUES (
    'Luigi', 'Verdi', 'dipendente@academy.it', '$2y$10$yakGqvIeIBGcKYeL8XedAucrzEWYcZqp5kcFWuVOgtp6XxKKNtY2K', 'dipendente'
);

-- ============================================
-- 3. Tabella: corsi
-- ============================================
CREATE TABLE IF NOT EXISTS corsi (
    id SERIAL PRIMARY KEY,
    titolo VARCHAR(255) NOT NULL,
    descrizione TEXT DEFAULT NULL,
    categoria VARCHAR(100) NOT NULL,
    durata_ore INT NOT NULL,
    obbligatorio SMALLINT NOT NULL DEFAULT 0,
    attivo SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed corsi
INSERT INTO corsi (titolo, descrizione, categoria, durata_ore, obbligatorio, attivo) VALUES
('Sicurezza sul Lavoro', 'Corso base sulla sicurezza negli ambienti di lavoro', 'Sicurezza', 4, 1, 1),
('Sviluppo Web Avanzato', 'Corso approfondito su React e Node.js', 'IT', 40, 0, 1),
('Privacy e GDPR', 'Trattamento dei dati personali in azienda', 'Compliance', 8, 1, 1);

-- ============================================
-- 4. Tabella: assegnazioni
-- ============================================
CREATE TABLE IF NOT EXISTS assegnazioni (
    id SERIAL PRIMARY KEY,
    corso_id INT NOT NULL,
    utente_id INT NOT NULL,
    data_assegnazione DATE NOT NULL,
    data_scadenza DATE NOT NULL,
    stato stato_assegnazione NOT NULL DEFAULT 'Assegnato',
    data_completamento DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (corso_id) REFERENCES corsi(id) ON DELETE RESTRICT,
    FOREIGN KEY (utente_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Seed assegnazioni
INSERT INTO assegnazioni (corso_id, utente_id, data_assegnazione, data_scadenza, stato) VALUES
(1, 2, CURRENT_DATE, CURRENT_DATE + INTERVAL '30 day', 'Assegnato'),
(2, 2, CURRENT_DATE - INTERVAL '10 day', CURRENT_DATE + INTERVAL '60 day', 'Assegnato');
