-- Update events for Cameroon with FCFA currency and local locations

-- Clear existing events
DELETE FROM events;

-- Insert Cameroonian events with FCFA pricing
INSERT INTO events (name, description, date, time, venue, location, organizer_contact, image, price, ticket_types, max_capacity, status) VALUES 

('Cameroon Tech Summit 2025', 'Annual technology summit featuring innovations in fintech, agritech, and digital transformation across Central Africa.', '2025-08-15', '09:00:00', 'Palais des Congrès', 'Yaoundé', 'info@camtechsummit.cm', 'tech-summit.jpg', 25000, '{"general": 25000, "vip": 50000, "student": 15000}', 500, 'active'),

('Festival Ngondo 2025', 'Traditional Sawa cultural festival celebrating the heritage of coastal Cameroon with music, dance, and local cuisine.', '2025-09-20', '16:00:00', 'Bonanjo Cultural Center', 'Douala', 'contact@ngondo.cm', 'ngondo-festival.jpg', 5000, '{"general": 5000, "vip": 15000, "family": 18000}', 2000, 'active'),

('Entrepreneurship Workshop Bamenda', 'Intensive workshop on starting and scaling businesses in the Northwest region, featuring successful local entrepreneurs.', '2025-07-25', '10:00:00', 'Ayaba Hotel Conference Hall', 'Bamenda', 'workshop@nwentrepreneurs.cm', 'entrepreneur-workshop.jpg', 12000, '{"general": 12000, "premium": 20000}', 150, 'active'),

('Cameroon Fashion Week', 'Showcase of contemporary African fashion featuring designers from across Cameroon and Central Africa.', '2025-10-05', '19:00:00', 'Hilton Hotel Yaoundé', 'Yaoundé', 'info@camfashionweek.cm', 'fashion-week.jpg', 8000, '{"general": 8000, "vip": 25000, "student": 5000}', 300, 'active'),

('Littoral Food Festival', 'Celebration of Cameroonian cuisine featuring traditional dishes, cooking competitions, and local chefs.', '2025-11-12', '17:30:00', 'Akwa Palace Hotel', 'Douala', 'events@littoralfood.cm', 'food-festival.jpg', 7500, '{"general": 7500, "couples": 12000, "family": 20000}', 400, 'active'),

('Mount Cameroon Marathon', 'Annual charity marathon around the base of Mount Cameroon to raise funds for local education projects.', '2025-12-15', '07:00:00', 'Limbe Town Green', 'Limbe', 'run@mountcammarathon.cm', 'marathon.jpg', 3000, '{"5k": 3000, "10k": 5000, "marathon": 8000}', 1500, 'active'),

('Bafoussam Agricultural Fair', 'West Region agricultural exhibition showcasing farming innovations, livestock, and agribusiness opportunities.', '2025-08-30', '08:00:00', 'Municipal Stadium', 'Bafoussam', 'info@westregionagri.cm', 'agri-fair.jpg', 2000, '{"general": 2000, "exhibitor": 15000, "student": 1000}', 800, 'active'),

('Garoua Business Conference', 'Northern Cameroon business conference focusing on trade opportunities with Chad and Central African Republic.', '2025-09-10', '09:30:00', 'Ribadu Square Conference Center', 'Garoua', 'conference@northbusiness.cm', 'business-conf.jpg', 18000, '{"general": 18000, "vip": 35000, "group": 60000}', 250, 'active');
