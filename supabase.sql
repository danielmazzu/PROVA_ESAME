-- ============================================
-- Esame di Stato - Academy Aziendale
-- Versione per PostgreSQL (Supabase)
-- Script Completo con Dati di Popolamento
-- ============================================

-- Pulizia iniziale per permettere l'esecuzione multipla
DROP TABLE IF EXISTS assegnazioni CASCADE;
DROP TABLE IF EXISTS corsi CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TYPE IF EXISTS user_role CASCADE;
DROP TYPE IF EXISTS stato_assegnazione CASCADE;

-- ============================================
-- 1. Tipo Enum per i ruoli e stati
-- ============================================
CREATE TYPE user_role AS ENUM ('dipendente', 'referente');
CREATE TYPE stato_assegnazione AS ENUM ('Assegnato', 'Completato', 'Scaduto', 'Annullato');

-- ============================================
-- 2. Tabella: users (utenti)
-- ============================================
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role user_role NOT NULL DEFAULT 'dipendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 3. Tabella: corsi (catalogo)
-- ============================================
CREATE TABLE corsi (
    id SERIAL PRIMARY KEY,
    titolo VARCHAR(255) NOT NULL,
    descrizione TEXT,
    durata_ore INT NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    attivo SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 4. Tabella: assegnazioni (stato dei corsi per utente)
-- ============================================
CREATE TABLE assegnazioni (
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

-- ============================================
-- SEEDING (POPOLAMENTO DEL DATABASE)
-- ============================================

-- --------------------------------------------
-- Utenti (10 Utenti)
-- Tutti con password 'admin123' (hash: $2y$10$yakGqvIeIBGcKYeL8XedAucrzEWYcZqp5kcFWuVOgtp6XxKKNtY2K)
-- --------------------------------------------
INSERT INTO users (nome, cognome, email, password, role) VALUES 
('Mario', 'Rossi', 'admin@academy.it', '$2y$10$yakGqvIeIBGcKYeL8XedAucrzEWYcZqp5kcFWuVOgtp6XxKKNtY2K', 'referente'),
('Luigi', 'Verdi', 'dipendente@academy.it', '$2y$10$yakGqvIeIBGcKYeL8XedAucrzEWYcZqp5kcFWuVOgtp6XxKKNtY2K', 'dipendente'),
('Giulia', 'Bianchi', 'giulia.bianchi@academy.it', '$2y$10$yakGqvIeIBGcKYeL8XedAucrzEWYcZqp5kcFWuVOgtp6XxKKNtY2K', 'dipendente'),
('Andrea', 'Romano', 'andrea.romano@academy.it', '$2y$10$yakGqvIeIBGcKYeL8XedAucrzEWYcZqp5kcFWuVOgtp6XxKKNtY2K', 'dipendente'),
('Sofia', 'Colombo', 'sofia.colombo@academy.it', '$2y$10$yakGqvIeIBGcKYeL8XedAucrzEWYcZqp5kcFWuVOgtp6XxKKNtY2K', 'dipendente'),
('Marco', 'Ricci', 'marco.ricci@academy.it', '$2y$10$yakGqvIeIBGcKYeL8XedAucrzEWYcZqp5kcFWuVOgtp6XxKKNtY2K', 'dipendente'),
('Francesca', 'Marino', 'francesca.marino@academy.it', '$2y$10$yakGqvIeIBGcKYeL8XedAucrzEWYcZqp5kcFWuVOgtp6XxKKNtY2K', 'dipendente'),
('Alessandro', 'Greco', 'alessandro.greco@academy.it', '$2y$10$yakGqvIeIBGcKYeL8XedAucrzEWYcZqp5kcFWuVOgtp6XxKKNtY2K', 'dipendente'),
('Elena', 'Gallo', 'elena.gallo@academy.it', '$2y$10$yakGqvIeIBGcKYeL8XedAucrzEWYcZqp5kcFWuVOgtp6XxKKNtY2K', 'dipendente'),
('Matteo', 'Conti', 'matteo.conti@academy.it', '$2y$10$yakGqvIeIBGcKYeL8XedAucrzEWYcZqp5kcFWuVOgtp6XxKKNtY2K', 'dipendente');

-- --------------------------------------------
-- Corsi (20 Corsi)
-- --------------------------------------------
INSERT INTO corsi (titolo, descrizione, durata_ore, categoria, attivo) VALUES
('Sicurezza sul Lavoro', 'Corso obbligatorio sulla sicurezza e salute nei luoghi di lavoro (D.Lgs 81/08).', 8, 'Sicurezza', 1),
('Sviluppo Web Avanzato', 'Corso intensivo su framework moderni, React, Vue e best practices.', 40, 'Informatica', 1),
('Privacy e GDPR', 'Fondamenti sul trattamento dei dati personali e regolamento europeo.', 4, 'Normativa', 1),
('Inglese B2 - Business', 'Migliorare la comunicazione aziendale in lingua inglese.', 20, 'Lingue', 1),
('Leadership e Gestione', 'Tecniche di team building e gestione del personale.', 16, 'Soft Skills', 1),
('Cybersecurity Basics', 'Come riconoscere phishing, malware e proteggere i dati aziendali.', 6, 'Informatica', 1),
('Excel Avanzato', 'Uso di macro, VBA e tabelle pivot per l''analisi dei dati.', 12, 'Informatica', 1),
('Time Management', 'Imparare a gestire il proprio tempo in modo efficace per aumentare la produttività.', 4, 'Soft Skills', 1),
('Comunicazione Efficace', 'Migliorare le capacità relazionali e di esposizione in pubblico.', 8, 'Soft Skills', 1),
('Python per Data Science', 'Analisi dei dati, Pandas, NumPy e fondamenti di Machine Learning.', 30, 'Informatica', 1),
('Anticorruzione e Trasparenza', 'Formazione obbligatoria sui modelli organizzativi 231.', 4, 'Normativa', 1),
('Marketing Digitale', 'SEO, SEM e strategie per i social media aziendali.', 15, 'Marketing', 1),
('Gestione dello Stress', 'Tecniche di mindfulness e work-life balance.', 6, 'Soft Skills', 1),
('Primo Soccorso', 'Formazione per addetti al primo soccorso aziendale.', 12, 'Sicurezza', 1),
('Antincendio Rischio Basso', 'Prevenzione incendi e procedure di evacuazione.', 4, 'Sicurezza', 1),
('Public Speaking', 'Arte del parlare in pubblico con disinvoltura.', 10, 'Soft Skills', 1),
('Cloud Computing AWS', 'Introduzione ai servizi cloud di Amazon Web Services.', 24, 'Informatica', 1),
('Agile & Scrum', 'Metodologie agili per lo sviluppo del software e la gestione dei progetti.', 16, 'Management', 1),
('Intelligenza Artificiale', 'Introduzione a ChatGPT, Prompt Engineering e AI generativa.', 8, 'Informatica', 1),
('Design Thinking', 'Approccio innovativo alla risoluzione dei problemi e sviluppo prodotto.', 10, 'Management', 0); -- 0 = disattivo

-- --------------------------------------------
-- Assegnazioni (40 Assegnazioni Miste)
-- --------------------------------------------
INSERT INTO assegnazioni (corso_id, utente_id, data_assegnazione, data_scadenza, stato, data_completamento) VALUES
-- Utente 2 (Luigi Verdi)
(1, 2, CURRENT_DATE - INTERVAL '60 day', CURRENT_DATE - INTERVAL '30 day', 'Completato', CURRENT_DATE - INTERVAL '35 day'),
(3, 2, CURRENT_DATE - INTERVAL '20 day', CURRENT_DATE + INTERVAL '10 day', 'Assegnato', NULL),
(5, 2, CURRENT_DATE - INTERVAL '90 day', CURRENT_DATE - INTERVAL '10 day', 'Scaduto', NULL),
(19, 2, CURRENT_DATE - INTERVAL '5 day', CURRENT_DATE + INTERVAL '25 day', 'Assegnato', NULL),

-- Utente 3 (Giulia Bianchi)
(2, 3, CURRENT_DATE - INTERVAL '10 day', CURRENT_DATE + INTERVAL '50 day', 'Assegnato', NULL),
(7, 3, CURRENT_DATE - INTERVAL '40 day', CURRENT_DATE - INTERVAL '10 day', 'Completato', CURRENT_DATE - INTERVAL '12 day'),
(12, 3, CURRENT_DATE - INTERVAL '15 day', CURRENT_DATE + INTERVAL '15 day', 'Assegnato', NULL),
(18, 3, CURRENT_DATE - INTERVAL '80 day', CURRENT_DATE - INTERVAL '20 day', 'Scaduto', NULL),
(6, 3, CURRENT_DATE - INTERVAL '2 day', CURRENT_DATE + INTERVAL '30 day', 'Annullato', NULL),

-- Utente 4 (Andrea Romano)
(1, 4, CURRENT_DATE - INTERVAL '120 day', CURRENT_DATE - INTERVAL '90 day', 'Completato', CURRENT_DATE - INTERVAL '100 day'),
(14, 4, CURRENT_DATE - INTERVAL '50 day', CURRENT_DATE + INTERVAL '10 day', 'Assegnato', NULL),
(15, 4, CURRENT_DATE - INTERVAL '40 day', CURRENT_DATE - INTERVAL '10 day', 'Scaduto', NULL),
(4, 4, CURRENT_DATE - INTERVAL '10 day', CURRENT_DATE + INTERVAL '20 day', 'Assegnato', NULL),
(11, 4, CURRENT_DATE - INTERVAL '60 day', CURRENT_DATE - INTERVAL '30 day', 'Completato', CURRENT_DATE - INTERVAL '40 day'),

-- Utente 5 (Sofia Colombo)
(8, 5, CURRENT_DATE - INTERVAL '20 day', CURRENT_DATE + INTERVAL '10 day', 'Assegnato', NULL),
(9, 5, CURRENT_DATE - INTERVAL '30 day', CURRENT_DATE - INTERVAL '2 day', 'Completato', CURRENT_DATE - INTERVAL '5 day'),
(16, 5, CURRENT_DATE - INTERVAL '100 day', CURRENT_DATE - INTERVAL '40 day', 'Scaduto', NULL),

-- Utente 6 (Marco Ricci)
(1, 6, CURRENT_DATE - INTERVAL '10 day', CURRENT_DATE + INTERVAL '20 day', 'Assegnato', NULL),
(3, 6, CURRENT_DATE - INTERVAL '15 day', CURRENT_DATE + INTERVAL '15 day', 'Assegnato', NULL),
(10, 6, CURRENT_DATE - INTERVAL '40 day', CURRENT_DATE - INTERVAL '10 day', 'Completato', CURRENT_DATE - INTERVAL '15 day'),
(17, 6, CURRENT_DATE - INTERVAL '5 day', CURRENT_DATE + INTERVAL '55 day', 'Assegnato', NULL),

-- Utente 7 (Francesca Marino)
(2, 7, CURRENT_DATE - INTERVAL '90 day', CURRENT_DATE - INTERVAL '30 day', 'Completato', CURRENT_DATE - INTERVAL '40 day'),
(6, 7, CURRENT_DATE - INTERVAL '10 day', CURRENT_DATE + INTERVAL '20 day', 'Assegnato', NULL),
(12, 7, CURRENT_DATE - INTERVAL '50 day', CURRENT_DATE - INTERVAL '20 day', 'Scaduto', NULL),
(19, 7, CURRENT_DATE - INTERVAL '2 day', CURRENT_DATE + INTERVAL '28 day', 'Assegnato', NULL),
(20, 7, CURRENT_DATE - INTERVAL '100 day', CURRENT_DATE - INTERVAL '50 day', 'Annullato', NULL),

-- Utente 8 (Alessandro Greco)
(1, 8, CURRENT_DATE - INTERVAL '50 day', CURRENT_DATE - INTERVAL '20 day', 'Completato', CURRENT_DATE - INTERVAL '25 day'),
(3, 8, CURRENT_DATE - INTERVAL '40 day', CURRENT_DATE - INTERVAL '10 day', 'Completato', CURRENT_DATE - INTERVAL '15 day'),
(5, 8, CURRENT_DATE - INTERVAL '10 day', CURRENT_DATE + INTERVAL '20 day', 'Assegnato', NULL),
(8, 8, CURRENT_DATE - INTERVAL '5 day', CURRENT_DATE + INTERVAL '25 day', 'Assegnato', NULL),
(11, 8, CURRENT_DATE - INTERVAL '60 day', CURRENT_DATE - INTERVAL '30 day', 'Scaduto', NULL),

-- Utente 9 (Elena Gallo)
(4, 9, CURRENT_DATE - INTERVAL '30 day', CURRENT_DATE + INTERVAL '30 day', 'Assegnato', NULL),
(7, 9, CURRENT_DATE - INTERVAL '40 day', CURRENT_DATE - INTERVAL '10 day', 'Completato', CURRENT_DATE - INTERVAL '20 day'),
(13, 9, CURRENT_DATE - INTERVAL '15 day', CURRENT_DATE + INTERVAL '15 day', 'Assegnato', NULL),
(16, 9, CURRENT_DATE - INTERVAL '80 day', CURRENT_DATE - INTERVAL '50 day', 'Completato', CURRENT_DATE - INTERVAL '60 day'),

-- Utente 10 (Matteo Conti)
(1, 10, CURRENT_DATE - INTERVAL '10 day', CURRENT_DATE + INTERVAL '20 day', 'Assegnato', NULL),
(10, 10, CURRENT_DATE - INTERVAL '60 day', CURRENT_DATE - INTERVAL '30 day', 'Scaduto', NULL),
(14, 10, CURRENT_DATE - INTERVAL '40 day', CURRENT_DATE - INTERVAL '10 day', 'Completato', CURRENT_DATE - INTERVAL '12 day'),
(18, 10, CURRENT_DATE - INTERVAL '5 day', CURRENT_DATE + INTERVAL '25 day', 'Assegnato', NULL),
(19, 10, CURRENT_DATE - INTERVAL '20 day', CURRENT_DATE + INTERVAL '10 day', 'Assegnato', NULL);
