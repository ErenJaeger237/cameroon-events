-- Online Event Booking System Database Schema
CREATE DATABASE IF NOT EXISTS event_booking;
USE event_booking;

-- Users table for authentication and user management
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Events table for storing event information
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    time TIME NOT NULL,
    venue VARCHAR(200) NOT NULL,
    location VARCHAR(200) NOT NULL,
    organizer_contact VARCHAR(100),
    image VARCHAR(255),
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    ticket_types JSON, -- Store different ticket types and prices
    max_capacity INT DEFAULT 100,
    current_bookings INT DEFAULT 0,
    status ENUM('active', 'cancelled', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Cart table for temporary storage of user selections
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    ticket_type VARCHAR(50) DEFAULT 'general',
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- Bookings table for confirmed reservations
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    ticket_type VARCHAR(50) DEFAULT 'general',
    quantity INT NOT NULL DEFAULT 1,
    total_amount DECIMAL(10,2) NOT NULL,
    attendee_name VARCHAR(100) NOT NULL,
    booking_reference VARCHAR(20) UNIQUE NOT NULL,
    status ENUM('confirmed', 'cancelled', 'completed') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- Payments table for payment tracking (simulation)
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_events_date ON events(date);
CREATE INDEX idx_events_location ON events(location);
CREATE INDEX idx_events_status ON events(status);
CREATE INDEX idx_bookings_user ON bookings(user_id);
CREATE INDEX idx_bookings_event ON bookings(event_id);
CREATE INDEX idx_bookings_reference ON bookings(booking_reference);
CREATE INDEX idx_cart_user ON cart(user_id);

-- Insert sample data for testing

-- Insert admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@eventbooking.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample regular users (password: user123)
INSERT INTO users (name, email, password, role) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Mike Johnson', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Insert sample events
INSERT INTO events (name, description, date, time, venue, location, organizer_contact, image, price, ticket_types, max_capacity) VALUES
('Tech Conference 2024', 'Annual technology conference featuring latest trends in AI, Web Development, and Cloud Computing.', '2024-03-15', '09:00:00', 'Convention Center', 'New York, NY', 'info@techconf.com', 'tech-conference.jpg', 99.99, '{"general": 99.99, "vip": 199.99, "student": 49.99}', 500),
('Music Festival Summer', 'Three-day music festival featuring top artists from around the world.', '2024-06-20', '18:00:00', 'Central Park', 'New York, NY', 'contact@musicfest.com', 'music-festival.jpg', 149.99, '{"general": 149.99, "vip": 299.99, "early_bird": 99.99}', 10000),
('Business Workshop', 'Intensive workshop on entrepreneurship and business development strategies.', '2024-04-10', '10:00:00', 'Business Center', 'Los Angeles, CA', 'workshop@business.com', 'business-workshop.jpg', 79.99, '{"general": 79.99, "premium": 129.99}', 100),
('Art Exhibition Opening', 'Grand opening of contemporary art exhibition featuring local and international artists.', '2024-05-05', '19:00:00', 'Art Gallery', 'Chicago, IL', 'gallery@art.com', 'art-exhibition.jpg', 25.00, '{"general": 25.00, "member": 15.00}', 200),
('Food & Wine Tasting', 'Exclusive food and wine tasting event with renowned chefs and sommeliers.', '2024-07-12', '17:30:00', 'Grand Hotel', 'San Francisco, CA', 'events@grandhotel.com', 'food-wine.jpg', 89.99, '{"general": 89.99, "couples": 159.99}', 150),
('Charity Run Marathon', 'Annual charity marathon to raise funds for local community projects.', '2024-08-25', '07:00:00', 'City Park', 'Boston, MA', 'run@charity.org', 'charity-run.jpg', 35.00, '{"5k": 35.00, "10k": 45.00, "marathon": 65.00}', 1000);