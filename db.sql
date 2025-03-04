CREATE TABLE Donors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(20),
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    birthday Date,
    gender ENUM('Male', 'Female', 'Other'),
    occupation VARCHAR(255),
    employer VARCHAR(255),
    partner VARCHAR(255),
    total_donation DECIMAL(10,2) DEFAULT 0 NOT NULL,
    largest_donation DECIMAL(10,2) DEFAULT 0 NOT NULL,
    average_donation DECIMAL(10,2) DEFAULT 0 NOT NULL,
    last_donation_date Date,
    preferred_donation_name VARCHAR(255),
    anonymous_donation BOOLEAN NOT NULL DEFAULT FALSE, 
    thank_you_sent_date Date,
    opted_in_newsletter BOOLEAN NOT NULL DEFAULT FALSE,
    active_status BOOLEAN NOT NULL DEFAULT TRUE, 
    notes VARCHAR(255)
);

CREATE TABLE ContactInfo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone_type ENUM('Home', 'Work', 'Mobile'),
    phone_number VARCHAR(20),
    mailing_address TEXT NOT NULL,
    preferred_contact ENUM('Email', 'Phone'),
    language_preference VARCHAR(50) DEFAULT 'English',
    FOREIGN KEY (donor_id) REFERENCES Donors(id) ON DELETE CASCADE
);

CREATE TABLE Donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT,
    donation_date Date DEFAULT (CURRENT_DATE) NOT NULL,
    donation_type ENUM('One-time', 'Recurring', 'In-kind', 'Pledged'),
    event VARCHAR(255),
    amount DECIMAL(10,2) DEFAULT 0 NOT NULL,
    payment_method VARCHAR(50),
    notes VARCHAR(255),
    FOREIGN KEY (donor_id) REFERENCES Donors(id) ON DELETE SET NULL
);

CREATE TABLE Engagement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    join_method VARCHAR(255),
    member_status VARCHAR(255),
    corporate_affiliation VARCHAR(255),
    event_attendance VARCHAR(255),
    volunteer_hours DECIMAL(10,2),
    organization_interaction VARCHAR(255),
    FOREIGN KEY (donor_id) REFERENCES Donors(id) ON DELETE CASCADE
);

INSERT INTO Donors (title, first_name, last_name, birthday, gender) VALUES ('Mr.', 'John', 'Do', '2000-01-30', 'Male');
