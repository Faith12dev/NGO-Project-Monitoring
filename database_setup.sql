CREATE DATABASE IF NOT EXISTS NGO;
USE NGO;

-- Donor Table
CREATE TABLE Donor (
    DonorID INT AUTO_INCREMENT PRIMARY KEY,
    DonorName VARCHAR(100) NOT NULL,
    Email VARCHAR(100),
    Phonenumber VARCHAR(20),
    Address VARCHAR(200),
    Country VARCHAR(50),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Location Table
CREATE TABLE Location (
    LocationID INT AUTO_INCREMENT PRIMARY KEY,
    District VARCHAR(100),
    Region VARCHAR(100),
    Country VARCHAR(50),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projects Table
CREATE TABLE Projects (
    ProjectID INT AUTO_INCREMENT PRIMARY KEY,
    ProjectName VARCHAR(150) NOT NULL,
    Description TEXT,
    StartDate DATE,
    EndDate DATE,
    Budget DECIMAL(12,2),
    DonorID INT,
    LocationID INT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (DonorID) REFERENCES Donor(DonorID) ON DELETE SET NULL,
    FOREIGN KEY (LocationID) REFERENCES Location(LocationID) ON DELETE SET NULL,
    INDEX idx_donor (DonorID),
    INDEX idx_location (LocationID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Beneficiary Table
CREATE TABLE Beneficiary (
    BeneficiaryID INT AUTO_INCREMENT PRIMARY KEY,
    BeneficiaryName VARCHAR(150),
    BeneficiaryType VARCHAR(50),
    ProjectID INT,
    NoOfPeople INT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ProjectID) REFERENCES Projects(ProjectID) ON DELETE CASCADE,
    INDEX idx_project (ProjectID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Expenditure Table
CREATE TABLE Expenditure (
    ExpenditureID INT AUTO_INCREMENT PRIMARY KEY,
    ProjectID INT,
    Date DATE,
    Category VARCHAR(100),
    AmountSpent DECIMAL(12,2),
    Remarks TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ProjectID) REFERENCES Projects(ProjectID) ON DELETE CASCADE,
    INDEX idx_project (ProjectID),
    INDEX idx_date (Date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Outcome Table
CREATE TABLE Outcome (
    OutcomeID INT AUTO_INCREMENT PRIMARY KEY,
    ProjectID INT,
    TargetValue DECIMAL(10,2),
    AchievedValue DECIMAL(10,2),
    ReportDate DATE,
    Comments TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ProjectID) REFERENCES Projects(ProjectID) ON DELETE CASCADE,
    INDEX idx_project (ProjectID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Staff Table
CREATE TABLE Staff (
    StaffID INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(150) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    Phone VARCHAR(20),
    Role VARCHAR(100) NOT NULL,
    Gender VARCHAR(100),
    Password VARCHAR(255),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (Email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Field Report Table (for Field Officers to submit updates)
CREATE TABLE FieldReport (
    ReportID INT AUTO_INCREMENT PRIMARY KEY,
    ProjectID INT NOT NULL,
    StaffID INT NOT NULL,
    ReportDate DATE NOT NULL,
    Activities TEXT,
    Challenges TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ProjectID) REFERENCES Projects(ProjectID) ON DELETE CASCADE,
    FOREIGN KEY (StaffID) REFERENCES Staff(StaffID) ON DELETE CASCADE,
    INDEX idx_project (ProjectID),
    INDEX idx_staff (StaffID),
    INDEX idx_date (ReportDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password Reset Table (for password recovery functionality)
CREATE TABLE PasswordReset (
    ResetID INT AUTO_INCREMENT PRIMARY KEY,
    StaffID INT NOT NULL,
    ResetCode VARCHAR(100) UNIQUE NOT NULL,
    Email VARCHAR(100) NOT NULL,
    ExpiresAt DATETIME NOT NULL,
    IsUsed BOOLEAN DEFAULT FALSE,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (StaffID) REFERENCES Staff(StaffID) ON DELETE CASCADE,
    INDEX idx_code (ResetCode),
    INDEX idx_email (Email),
    INDEX idx_expires (ExpiresAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Data
INSERT INTO Staff (FullName, Email, Phone, Role, Gender, Password) VALUES
('John Doe', 'mushabedavid2002@gmail.com', '+254712345678', 'admin', 'Male', '$2y$10$N9qo8uLOickgx2ZMRZoXyejMHVmHaJlJUlWZ0XMrp2H8qVYV1Z1Ei'),
('Jane Smith', 'jane@ngo.com', '+254787654321', 'project_manager', 'Female', '$2y$10$N9qo8uLOickgx2ZMRZoXyejMHVmHaJlJUlWZ0XMrp2H8qVYV1Z1Ei'),
('Peter Johnson', 'peter@ngo.com', '+254712111111', 'field_officer', 'Male', '$2y$10$N9qo8uLOickgx2ZMRZoXyejMHVmHaJlJUlWZ0XMrp2H8qVYV1Z1Ei'),
('Mary Williams', 'mary@ngo.com', '+254787222222', 'donor_liaison', 'Female', '$2y$10$N9qo8uLOickgx2ZMRZoXyejMHVmHaJlJUlWZ0XMrp2H8qVYV1Z1Ei'),
('David Brown', 'david@ngo.com', '+254712333333', 'accountant', 'Male', '$2y$10$N9qo8uLOickgx2ZMRZoXyejMHVmHaJlJUlWZ0XMrp2H8qVYV1Z1Ei'),
('Sarah Wilson', 'sarah@ngo.com', '+254712444444', 'supervisor', 'Female', '$2y$10$N9qo8uLOickgx2ZMRZoXyejMHVmHaJlJUlWZ0XMrp2H8qVYV1Z1Ei');

INSERT INTO Donor (DonorName, Email, Phonenumber, Address, Country) VALUES
('Global Aid Foundation', 'contact@globalaid.org', '+1-555-0001', '123 Main Street, New York', 'USA'),
('African Development Fund', 'info@adf.org', '+254712000000', 'Nairobi', 'Kenya'),
('Red Cross Society', 'support@redcross.org', '+254787111111', 'Kampala', 'Uganda');

INSERT INTO Location (District, Region, Country) VALUES
('Nairobi', 'Central', 'Kenya'),
('Kisumu', 'Western', 'Kenya'),
('Mombasa', 'Coastal', 'Kenya'),
('Nakuru', 'Rift Valley', 'Kenya');

INSERT INTO Projects (ProjectName, Description, StartDate, EndDate, Budget, DonorID, LocationID) VALUES
('Water Supply Project', 'Installing clean water access in rural areas', '2024-01-15', '2024-12-31', 500000.00, 1, 1),
('Education Initiative', 'Building school infrastructure', '2024-02-01', '2025-01-31', 750000.00, 2, 2),
('Healthcare Program', 'Mobile health clinics', '2024-03-15', '2024-09-30', 300000.00, 3, 3);

INSERT INTO Beneficiary (BeneficiaryName, BeneficiaryType, ProjectID, NoOfPeople) VALUES
('Kamiti Village Community', 'Community', 1, 1500),
('Local Schools Network', 'Organization', 2, 3000),
('Health Centers Association', 'Organization', 3, 5000);

INSERT INTO Expenditure (ProjectID, Date, Category, AmountSpent, Remarks) VALUES
(1, '2024-02-10', 'Materials', 50000.00, 'Pipes and fittings'),
(1, '2024-03-15', 'Labor', 30000.00, 'Installation labor'),
(2, '2024-02-28', 'Transport', 20000.00, 'Equipment transportation'),
(2, '2024-04-01', 'Materials', 100000.00, 'Construction materials');

INSERT INTO Outcome (ProjectID, TargetValue, AchievedValue, ReportDate, Comments) VALUES
(1, 100, 85, '2024-06-30', 'Good progress on installation'),
(2, 500, 450, '2024-05-31', 'School buildings 90% complete'),
(3, 1000, 950, '2024-07-15', 'Clinics operational and serving communities');

INSERT INTO FieldReport (ProjectID, StaffID, ReportDate, Activities, Challenges) VALUES
(1, 3, '2024-08-15', 'Completed water point construction at Kamiti Village. Community participated in the installation process.', 'Delayed delivery of some materials'),
(2, 3, '2024-08-20', 'Conducted teacher training workshop on new curriculum at Kisumu School. Attended by 25 teachers.', 'Limited attendance due to school exams period'),
(1, 3, '2024-09-01', 'Commenced beneficiary sensitization on water management and hygiene practices.', 'Some beneficiaries skeptical about water charge system');

-- Audit Log Table (for tracking edit/delete actions)
CREATE TABLE AuditLog (
    AuditID INT AUTO_INCREMENT PRIMARY KEY,
    StaffID INT NOT NULL,
    StaffEmail VARCHAR(100) NOT NULL,
    Action VARCHAR(50) NOT NULL,
    TableName VARCHAR(100) NOT NULL,
    RecordID INT NOT NULL,
    RecordName VARCHAR(255),
    OldValue LONGTEXT,
    NewValue LONGTEXT,
    IPAddress VARCHAR(50),
    UserAgent VARCHAR(255),
    ActionTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (StaffID) REFERENCES Staff(StaffID) ON DELETE CASCADE,
    INDEX idx_staff (StaffID),
    INDEX idx_action (Action),
    INDEX idx_table (TableName),
    INDEX idx_time (ActionTime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SELECT * FROM Staff WHERE Email = 'john@ngo.com';
SELECT DISTINCT Role FROM Staff;
SELECT Email, Password FROM Staff WHERE Email = 'john@ngo.com';

-- Run this in phpMyAdmin or MySQL command line:

DELETE FROM Staff;  -- Clear old data first

INSERT INTO Staff (FullName, Email, Phone, Role, Gender, Password) VALUES
('John Doe', 'mushabedavid2002@gmail.com', '+254712345678', 'admin', 'Male', '$2y$10$N9qo8uLOickgx2ZMRZoXyejMHVmHaJlJUlWZ0XMrp2H8qVYV1Z1Ei'),
('Jane Smith', 'jane@ngo.com', '+254787654321', 'project_manager', 'Female', '$2y$10$N9qo8uLOickgx2ZMRZoXyejMHVmHaJlJUlWZ0XMrp2H8qVYV1Z1Ei'),
('Peter Johnson', 'peter@ngo.com', '+254712111111', 'field_officer', 'Male', '$2y$10$N9qo8uLOickgx2ZMRZoXyejMHVmHaJlJUlWZ0XMrp2H8qVYV1Z1Ei'),
('Mary Williams', 'mary@ngo.com', '+254787222222', 'donor_liaison', 'Female', '$2y$10$N9qo8uLOickgx2ZMRZoXyejMHVmHaJlJUlWZ0XMrp2H8qVYV1Z1Ei'),
('David Brown', 'david@ngo.com', '+254712333333', 'accountant', 'Male', '$2y$10$N9qo8uLOickgx2ZMRZoXyejMHVmHaJlJUlWZ0XMrp2H8qVYV1Z1Ei'),
('Sarah Wilson', 'sarah@ngo.com', '+254712444444', 'supervisor', 'Female', '$2y$10$N9qo8uLOickgx2ZMRZoXyejMHVmHaJlJUlWZ0XMrp2H8qVYV1Z1Ei');
