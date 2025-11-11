-- ===========================================
--  SharpLync Facilities Database v1
--  Linked to CRM customers
--  Created by Max (Project Manager / Designer-in-Chief)
-- ===========================================

-- ==============================
--  1. Facility Sites
-- ==============================
CREATE TABLE facility_sites (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    crm_customer_id BIGINT NOT NULL,
    site_name VARCHAR(255) NOT NULL,
    address_line1 VARCHAR(255),
    address_line2 VARCHAR(255),
    suburb VARCHAR(100),
    state VARCHAR(50),
    postcode VARCHAR(20),
    contact_person VARCHAR(255),
    contact_phone VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (crm_customer_id) REFERENCES sharplync_crm.customers(id)
);

-- ==============================
--  2. Facility Projects
-- ==============================
CREATE TABLE facility_projects (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    site_id BIGINT NOT NULL,
    project_name VARCHAR(255),
    description TEXT,
    status ENUM('planned', 'active', 'completed', 'on_hold') DEFAULT 'planned',
    start_date DATE,
    end_date DATE,
    budget DECIMAL(12,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES facility_sites(id)
);

-- ==============================
--  3. Facility Assets
-- ==============================
CREATE TABLE facility_assets (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    site_id BIGINT NOT NULL,
    asset_name VARCHAR(255),
    asset_type VARCHAR(100),
    serial_number VARCHAR(100),
    purchase_date DATE,
    warranty_expiry DATE,
    status ENUM('active', 'inactive', 'retired') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES facility_sites(id)
);

-- ==============================
--  4. Maintenance Tasks
-- ==============================
CREATE TABLE maintenance_tasks (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    asset_id BIGINT,
    project_id BIGINT,
    description TEXT,
    assigned_to VARCHAR(255),
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    due_date DATE,
    completed_at DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES facility_assets(id),
    FOREIGN KEY (project_id) REFERENCES facility_projects(id)
);

-- ==============================
--  5. Fleet Vehicles
-- ==============================
CREATE TABLE fleet_vehicles (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    crm_customer_id BIGINT NOT NULL,
    registration_number VARCHAR(50) UNIQUE,
    make VARCHAR(100),
    model VARCHAR(100),
    year YEAR,
    vin_number VARCHAR(100),
    odometer INT,
    service_due_date DATE,
    assigned_driver VARCHAR(255),
    status ENUM('active', 'in_service', 'retired') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (crm_customer_id) REFERENCES sharplync_crm.customers(id)
);