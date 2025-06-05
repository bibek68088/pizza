-- Complete Crust Pizza Database Schema
-- All deliverables implementation
DROP DATABASE IF EXISTS crust_pizza;

CREATE DATABASE crust_pizza;

USE crust_pizza;

-- Users table for customer accounts
CREATE TABLE
    users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        date_of_birth DATE,
        is_active BOOLEAN DEFAULT TRUE,
        email_verified BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

-- Staff table for employees
CREATE TABLE
    staff (
        staff_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role ENUM ('kitchen', 'delivery', 'counter', 'admin') NOT NULL,
        store_id INT,
        is_active BOOLEAN DEFAULT TRUE,
        hire_date DATE,
        salary DECIMAL(10, 2),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

-- Stores table for different locations
CREATE TABLE
    stores (
        store_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        address TEXT NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(100),
        opening_hours JSON,
        is_active BOOLEAN DEFAULT TRUE,
        manager_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

-- Categories for menu items
CREATE TABLE
    categories (
        category_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        description TEXT,
        image_url VARCHAR(255),
        sort_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

-- Ingredients table
CREATE TABLE
    ingredients (
        ingredient_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        category ENUM (
            'crust',
            'sauce',
            'cheese',
            'meat',
            'vegetable',
            'other'
        ) NOT NULL,
        price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        cost DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        stock_quantity INT DEFAULT 0,
        min_stock_level INT DEFAULT 10,
        is_available BOOLEAN DEFAULT TRUE,
        is_vegan BOOLEAN DEFAULT FALSE,
        is_gluten_free BOOLEAN DEFAULT FALSE,
        allergens TEXT,
        nutritional_info JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

-- Pizza base templates
CREATE TABLE
    pizzas (
        pizza_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        category_id INT,
        image_url VARCHAR(255),
        base_price_small DECIMAL(10, 2) NOT NULL,
        base_price_medium DECIMAL(10, 2) NOT NULL,
        base_price_large DECIMAL(10, 2) NOT NULL,
        cost_small DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        cost_medium DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        cost_large DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        prep_time_minutes INT DEFAULT 15,
        calories_small INT,
        calories_medium INT,
        calories_large INT,
        is_available BOOLEAN DEFAULT TRUE,
        is_featured BOOLEAN DEFAULT FALSE,
        is_vegan BOOLEAN DEFAULT FALSE,
        is_gluten_free_available BOOLEAN DEFAULT FALSE,
        allergens TEXT,
        popularity_score INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories (category_id)
    );

-- Pizza ingredients relationship
CREATE TABLE
    pizza_ingredients (
        pizza_ingredient_id INT AUTO_INCREMENT PRIMARY KEY,
        pizza_id INT,
        ingredient_id INT,
        is_default BOOLEAN DEFAULT TRUE,
        quantity DECIMAL(5, 2) DEFAULT 1.00,
        FOREIGN KEY (pizza_id) REFERENCES pizzas (pizza_id) ON DELETE CASCADE,
        FOREIGN KEY (ingredient_id) REFERENCES ingredients (ingredient_id)
    );

-- Non-pizza menu items
CREATE TABLE
    menu_items (
        menu_item_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        category_id INT,
        price DECIMAL(10, 2) NOT NULL,
        cost DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        image_url VARCHAR(255),
        prep_time_minutes INT DEFAULT 5,
        calories INT,
        is_available BOOLEAN DEFAULT TRUE,
        is_featured BOOLEAN DEFAULT FALSE,
        is_vegan BOOLEAN DEFAULT FALSE,
        is_gluten_free BOOLEAN DEFAULT FALSE,
        allergens TEXT,
        stock_quantity INT DEFAULT 0,
        popularity_score INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories (category_id)
    );

-- Orders table
CREATE TABLE
    orders (
        order_id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(20) UNIQUE NOT NULL,
        user_id INT,
        store_id INT,
        order_type ENUM ('delivery', 'pickup') NOT NULL,
        status ENUM (
            'pending',
            'confirmed',
            'preparing',
            'prepared',
            'out_for_delivery',
            'ready_for_pickup',
            'delivered',
            'completed',
            'cancelled'
        ) DEFAULT 'pending',
        priority ENUM ('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
        subtotal DECIMAL(10, 2) NOT NULL,
        tax DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        delivery_fee DECIMAL(10, 2) DEFAULT 0.00,
        discount_amount DECIMAL(10, 2) DEFAULT 0.00,
        total DECIMAL(10, 2) NOT NULL,
        payment_method ENUM ('cash', 'card', 'online', 'paypal', 'apple_pay') NOT NULL,
        payment_status ENUM (
            'pending',
            'paid',
            'failed',
            'refunded',
            'partial'
        ) DEFAULT 'pending',
        payment_reference VARCHAR(100),
        customer_name VARCHAR(100) NOT NULL,
        customer_phone VARCHAR(20) NOT NULL,
        customer_email VARCHAR(100),
        delivery_address TEXT,
        delivery_instructions TEXT,
        estimated_prep_time INT DEFAULT 20,
        estimated_delivery_time TIMESTAMP NULL,
        actual_delivery_time TIMESTAMP NULL,
        assigned_staff_id INT,
        special_requests TEXT,
        rating INT CHECK (
            rating >= 1
            AND rating <= 5
        ),
        review TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (user_id),
        FOREIGN KEY (store_id) REFERENCES stores (store_id),
        FOREIGN KEY (assigned_staff_id) REFERENCES staff (staff_id)
    );

-- Order items
CREATE TABLE
    order_items (
        order_item_id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        item_type ENUM ('pizza', 'menu_item') NOT NULL,
        pizza_id INT NULL,
        menu_item_id INT NULL,
        size ENUM ('small', 'medium', 'large') NULL,
        quantity INT NOT NULL DEFAULT 1,
        unit_price DECIMAL(10, 2) NOT NULL,
        total_price DECIMAL(10, 2) NOT NULL,
        special_instructions TEXT,
        FOREIGN KEY (order_id) REFERENCES orders (order_id) ON DELETE CASCADE,
        FOREIGN KEY (pizza_id) REFERENCES pizzas (pizza_id),
        FOREIGN KEY (menu_item_id) REFERENCES menu_items (menu_item_id)
    );

-- Custom pizza ingredients
CREATE TABLE
    order_item_ingredients (
        order_item_ingredient_id INT AUTO_INCREMENT PRIMARY KEY,
        order_item_id INT,
        ingredient_id INT,
        quantity DECIMAL(5, 2) DEFAULT 1.00,
        price DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (order_item_id) REFERENCES order_items (order_item_id) ON DELETE CASCADE,
        FOREIGN KEY (ingredient_id) REFERENCES ingredients (ingredient_id)
    );

-- Order status history
CREATE TABLE
    order_status_history (
        history_id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        status ENUM (
            'pending',
            'confirmed',
            'preparing',
            'prepared',
            'out_for_delivery',
            'ready_for_pickup',
            'delivered',
            'completed',
            'cancelled'
        ) NOT NULL,
        changed_by INT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders (order_id) ON DELETE CASCADE,
        FOREIGN KEY (changed_by) REFERENCES staff (staff_id)
    );

-- Shopping cart
CREATE TABLE
    cart_items (
        cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        session_id VARCHAR(255),
        item_type ENUM ('pizza', 'menu_item') NOT NULL,
        pizza_id INT NULL,
        menu_item_id INT NULL,
        size ENUM ('small', 'medium', 'large') NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE,
        FOREIGN KEY (pizza_id) REFERENCES pizzas (pizza_id),
        FOREIGN KEY (menu_item_id) REFERENCES menu_items (menu_item_id)
    );

-- Cart item ingredients
CREATE TABLE
    cart_item_ingredients (
        cart_item_ingredient_id INT AUTO_INCREMENT PRIMARY KEY,
        cart_item_id INT,
        ingredient_id INT,
        quantity DECIMAL(5, 2) DEFAULT 1.00,
        FOREIGN KEY (cart_item_id) REFERENCES cart_items (cart_item_id) ON DELETE CASCADE,
        FOREIGN KEY (ingredient_id) REFERENCES ingredients (ingredient_id)
    );

-- Coupons and discounts
CREATE TABLE
    coupons (
        coupon_id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE NOT NULL,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        discount_type ENUM ('percentage', 'fixed_amount') NOT NULL,
        discount_value DECIMAL(10, 2) NOT NULL,
        minimum_order_amount DECIMAL(10, 2) DEFAULT 0.00,
        max_discount_amount DECIMAL(10, 2),
        usage_limit INT DEFAULT 1,
        used_count INT DEFAULT 0,
        valid_from TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        valid_until TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

-- User addresses
CREATE TABLE
    user_addresses (
        address_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        address_type ENUM ('home', 'work', 'other') DEFAULT 'home',
        address_line_1 VARCHAR(255) NOT NULL,
        address_line_2 VARCHAR(255),
        suburb VARCHAR(100) NOT NULL,
        state VARCHAR(50) NOT NULL,
        postcode VARCHAR(10) NOT NULL,
        country VARCHAR(50) DEFAULT 'Australia',
        is_default BOOLEAN DEFAULT FALSE,
        delivery_instructions TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
    );

-- User favorites
CREATE TABLE
    user_favorites (
        favorite_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        item_type ENUM ('pizza', 'menu_item') NOT NULL,
        pizza_id INT NULL,
        menu_item_id INT NULL,
        size ENUM ('small', 'medium', 'large') NULL,
        custom_ingredients JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE,
        FOREIGN KEY (pizza_id) REFERENCES pizzas (pizza_id),
        FOREIGN KEY (menu_item_id) REFERENCES menu_items (menu_item_id)
    );

-- Loyalty program
CREATE TABLE
    loyalty_points (
        point_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        order_id INT,
        points_earned INT DEFAULT 0,
        points_redeemed INT DEFAULT 0,
        transaction_type ENUM ('earned', 'redeemed', 'expired', 'bonus') NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE,
        FOREIGN KEY (order_id) REFERENCES orders (order_id)
    );

-- Notifications
CREATE TABLE
    notifications (
        notification_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        staff_id INT,
        type ENUM ('order_update', 'promotion', 'system', 'reminder') NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        action_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE,
        FOREIGN KEY (staff_id) REFERENCES staff (staff_id) ON DELETE CASCADE
    );

-- System settings
CREATE TABLE
    system_settings (
        setting_id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        setting_type ENUM ('string', 'number', 'boolean', 'json') DEFAULT 'string',
        description TEXT,
        is_public BOOLEAN DEFAULT FALSE,
        updated_by INT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (updated_by) REFERENCES staff (staff_id)
    );

-- Add foreign key constraints
ALTER TABLE staff ADD FOREIGN KEY (store_id) REFERENCES stores (store_id);

ALTER TABLE stores ADD FOREIGN KEY (manager_id) REFERENCES staff (staff_id);

-- Create indexes for performance
CREATE INDEX idx_orders_user_id ON orders (user_id);

CREATE INDEX idx_orders_store_id ON orders (store_id);

CREATE INDEX idx_orders_status ON orders (status);

CREATE INDEX idx_orders_created_at ON orders (created_at);

CREATE INDEX idx_orders_order_number ON orders (order_number);

CREATE INDEX idx_order_items_order_id ON order_items (order_id);

CREATE INDEX idx_cart_items_user_id ON cart_items (user_id);

CREATE INDEX idx_cart_items_session_id ON cart_items (session_id);

CREATE INDEX idx_staff_role ON staff (role);

CREATE INDEX idx_staff_store_id ON staff (store_id);

CREATE INDEX idx_pizzas_category_id ON pizzas (category_id);

CREATE INDEX idx_pizzas_is_available ON pizzas (is_available);

CREATE INDEX idx_menu_items_category_id ON menu_items (category_id);

CREATE INDEX idx_ingredients_category ON ingredients (category);

CREATE INDEX idx_user_addresses_user_id ON user_addresses (user_id);

CREATE INDEX idx_notifications_user_id ON notifications (user_id);

CREATE INDEX idx_loyalty_points_user_id ON loyalty_points (user_id);

-- Insert comprehensive sample data
INSERT INTO
    categories (name, description, sort_order)
VALUES
    (
        'Signature',
        'Our award-winning signature pizzas',
        1
    ),
    ('Classic', 'Traditional pizza favorites', 2),
    ('Vegan', 'Plant-based pizza options', 3),
    ('Meat Lovers', 'For the carnivores', 4),
    ('Gourmet', 'Premium ingredient combinations', 5),
    ('Sides', 'Appetizers and side dishes', 6),
    ('Drinks', 'Beverages and refreshments', 7),
    ('Desserts', 'Sweet treats to finish your meal', 8);

-- Sample stores
INSERT INTO
    stores (name, address, phone, email, opening_hours)
VALUES
    (
        'Crust Pizza Annandale',
        '123 Parramatta Rd, Annandale NSW 2038',
        '(02) 9560 1234',
        'annandale@crustpizza.com.au',
        '{"monday": "11:00-22:00", "tuesday": "11:00-22:00", "wednesday": "11:00-22:00", "thursday": "11:00-22:00", "friday": "11:00-23:00", "saturday": "11:00-23:00", "sunday": "11:00-22:00"}'
    ),
    (
        'Crust Pizza Richmond',
        '456 Swan St, Richmond VIC 3121',
        '(03) 9428 5678',
        'richmond@crustpizza.com.au',
        '{"monday": "11:00-22:00", "tuesday": "11:00-22:00", "wednesday": "11:00-22:00", "thursday": "11:00-22:00", "friday": "11:00-23:00", "saturday": "11:00-23:00", "sunday": "11:00-22:00"}'
    ),
    (
        'Crust Pizza Bondi',
        '789 Campbell Parade, Bondi Beach NSW 2026',
        '(02) 9365 9999',
        'bondi@crustpizza.com.au',
        '{"monday": "11:00-22:00", "tuesday": "11:00-22:00", "wednesday": "11:00-22:00", "thursday": "11:00-22:00", "friday": "11:00-23:00", "saturday": "11:00-23:00", "sunday": "11:00-22:00"}'
    );

-- Comprehensive ingredients
INSERT INTO
    ingredients (
        name,
        category,
        price,
        cost,
        stock_quantity,
        is_vegan,
        is_gluten_free,
        allergens
    )
VALUES
    -- Crusts
    (
        'Thin Crust',
        'crust',
        0.00,
        1.50,
        100,
        1,
        0,
        'Gluten'
    ),
    (
        'Classic Crust',
        'crust',
        0.00,
        1.80,
        100,
        1,
        0,
        'Gluten'
    ),
    (
        'Thick Crust',
        'crust',
        2.00,
        2.20,
        100,
        1,
        0,
        'Gluten'
    ),
    (
        'Gluten Free Crust',
        'crust',
        3.00,
        3.50,
        50,
        1,
        1,
        ''
    ),
    (
        'Cauliflower Crust',
        'crust',
        4.00,
        4.50,
        30,
        1,
        1,
        ''
    ),
    -- Sauces
    ('Tomato Base', 'sauce', 0.00, 0.50, 200, 1, 1, ''),
    ('BBQ Sauce', 'sauce', 1.00, 0.60, 150, 1, 1, ''),
    ('Pesto', 'sauce', 1.50, 1.00, 100, 0, 1, 'Nuts'),
    ('Garlic Base', 'sauce', 1.00, 0.70, 120, 1, 1, ''),
    (
        'Buffalo Sauce',
        'sauce',
        1.50,
        0.80,
        80,
        1,
        1,
        ''
    ),
    (
        'White Sauce',
        'sauce',
        1.50,
        0.90,
        90,
        0,
        1,
        'Dairy'
    ),
    -- Cheeses
    (
        'Mozzarella',
        'cheese',
        0.00,
        2.00,
        200,
        0,
        1,
        'Dairy'
    ),
    (
        'Extra Mozzarella',
        'cheese',
        2.50,
        3.00,
        150,
        0,
        1,
        'Dairy'
    ),
    (
        'Vegan Cheese',
        'cheese',
        3.00,
        3.50,
        80,
        1,
        1,
        ''
    ),
    (
        'Parmesan',
        'cheese',
        2.00,
        2.50,
        100,
        0,
        1,
        'Dairy'
    ),
    ('Feta', 'cheese', 2.50, 3.00, 70, 0, 1, 'Dairy'),
    (
        'Ricotta',
        'cheese',
        2.00,
        2.30,
        60,
        0,
        1,
        'Dairy'
    ),
    -- Meats
    ('Pepperoni', 'meat', 2.50, 3.00, 150, 0, 1, ''),
    ('Chicken', 'meat', 3.50, 4.00, 120, 0, 1, ''),
    ('Ham', 'meat', 2.50, 3.00, 100, 0, 1, ''),
    ('Bacon', 'meat', 3.00, 3.50, 80, 0, 1, ''),
    (
        'Italian Sausage',
        'meat',
        3.00,
        3.50,
        90,
        0,
        1,
        ''
    ),
    ('Prosciutto', 'meat', 4.00, 5.00, 50, 0, 1, ''),
    ('Salami', 'meat', 2.50, 3.00, 70, 0, 1, ''),
    ('Anchovies', 'meat', 2.00, 2.50, 40, 0, 1, 'Fish'),
    -- Vegetables
    (
        'Mushrooms',
        'vegetable',
        1.50,
        1.00,
        100,
        1,
        1,
        ''
    ),
    (
        'Capsicum',
        'vegetable',
        1.50,
        1.00,
        120,
        1,
        1,
        ''
    ),
    (
        'Red Onion',
        'vegetable',
        1.00,
        0.70,
        150,
        1,
        1,
        ''
    ),
    ('Olives', 'vegetable', 2.00, 1.50, 80, 1, 1, ''),
    (
        'Cherry Tomatoes',
        'vegetable',
        2.00,
        1.30,
        90,
        1,
        1,
        ''
    ),
    (
        'Pineapple',
        'vegetable',
        2.00,
        1.20,
        70,
        1,
        1,
        ''
    ),
    (
        'Baby Spinach',
        'vegetable',
        1.50,
        1.00,
        60,
        1,
        1,
        ''
    ),
    (
        'Roasted Eggplant',
        'vegetable',
        2.50,
        1.80,
        50,
        1,
        1,
        ''
    ),
    (
        'Sun-dried Tomatoes',
        'vegetable',
        2.50,
        2.00,
        40,
        1,
        1,
        ''
    ),
    (
        'Caramelized Onions',
        'vegetable',
        2.00,
        1.50,
        60,
        1,
        1,
        ''
    ),
    (
        'Jalapeños',
        'vegetable',
        1.50,
        1.00,
        80,
        1,
        1,
        ''
    ),
    (
        'Artichokes',
        'vegetable',
        3.00,
        2.50,
        30,
        1,
        1,
        ''
    );

-- Sample pizzas with comprehensive data
INSERT INTO
    pizzas (
        name,
        description,
        category_id,
        base_price_small,
        base_price_medium,
        base_price_large,
        cost_small,
        cost_medium,
        cost_large,
        prep_time_minutes,
        calories_small,
        calories_medium,
        calories_large,
        is_featured,
        is_gluten_free_available,
        allergens,
        popularity_score
    )
VALUES
    (
        'Peri Peri Chicken',
        'Award-winning pizza with peri peri chicken, capsicum, red onion, and mozzarella',
        1,
        18.90,
        24.90,
        29.90,
        8.50,
        11.20,
        14.50,
        18,
        580,
        780,
        980,
        1,
        1,
        'Gluten, Dairy',
        95
    ),
    (
        'Margherita',
        'Classic pizza with fresh basil, mozzarella, and tomato sauce',
        2,
        15.90,
        21.90,
        26.90,
        6.50,
        8.90,
        11.20,
        15,
        520,
        720,
        920,
        1,
        1,
        'Gluten, Dairy',
        88
    ),
    (
        'Vegan Supreme',
        'Plant-based pepperoni, mushrooms, capsicum, olives, and vegan cheese',
        3,
        19.90,
        25.90,
        30.90,
        9.20,
        12.50,
        15.80,
        20,
        480,
        680,
        880,
        1,
        1,
        'Gluten',
        75
    ),
    (
        'Meat Lovers',
        'Pepperoni, ham, bacon, Italian sausage, and mozzarella',
        4,
        21.90,
        27.90,
        32.90,
        10.50,
        14.20,
        18.50,
        22,
        680,
        920,
        1180,
        1,
        1,
        'Gluten, Dairy',
        92
    ),
    (
        'Hawaiian',
        'Ham, pineapple, and mozzarella on tomato base',
        2,
        17.90,
        23.90,
        28.90,
        7.80,
        10.50,
        13.20,
        16,
        550,
        750,
        950,
        0,
        1,
        'Gluten, Dairy',
        70
    ),
    (
        'Supreme',
        'Pepperoni, mushrooms, capsicum, olives, and mozzarella',
        2,
        19.90,
        25.90,
        30.90,
        9.20,
        12.50,
        15.80,
        20,
        600,
        800,
        1000,
        1,
        1,
        'Gluten, Dairy',
        85
    ),
    (
        'BBQ Chicken',
        'BBQ sauce, chicken, red onion, capsicum, and mozzarella',
        2,
        18.90,
        24.90,
        29.90,
        8.50,
        11.20,
        14.50,
        18,
        590,
        790,
        990,
        0,
        1,
        'Gluten, Dairy',
        80
    ),
    (
        'Prosciutto & Rocket',
        'Prosciutto, rocket, cherry tomatoes, parmesan, and mozzarella',
        5,
        22.90,
        28.90,
        33.90,
        11.50,
        15.20,
        19.50,
        20,
        620,
        820,
        1020,
        1,
        1,
        'Gluten, Dairy',
        78
    );

-- Menu items with comprehensive data
INSERT INTO
    menu_items (
        name,
        description,
        category_id,
        price,
        cost,
        prep_time_minutes,
        calories,
        is_featured,
        is_vegan,
        is_gluten_free,
        allergens,
        stock_quantity,
        popularity_score
    )
VALUES
    -- Sides
    (
        'Garlic Bread',
        'Fresh baked bread with garlic butter',
        6,
        8.90,
        3.50,
        8,
        320,
        1,
        0,
        0,
        'Gluten, Dairy',
        50,
        85
    ),
    (
        'Vegan Garlic Bread',
        'Fresh baked bread with vegan garlic spread',
        6,
        9.90,
        4.00,
        8,
        300,
        0,
        1,
        0,
        'Gluten',
        30,
        60
    ),
    (
        'Chicken Wings (6pc)',
        'Crispy wings with your choice of sauce',
        6,
        12.90,
        6.50,
        12,
        480,
        1,
        0,
        1,
        '',
        40,
        90
    ),
    (
        'Chicken Wings (12pc)',
        'Crispy wings with your choice of sauce',
        6,
        22.90,
        11.50,
        15,
        960,
        0,
        0,
        1,
        '',
        30,
        75
    ),
    (
        'Potato Wedges',
        'Crispy seasoned potato wedges with sour cream',
        6,
        9.90,
        4.20,
        10,
        380,
        0,
        0,
        1,
        'Dairy',
        40,
        70
    ),
    (
        'Caesar Salad',
        'Fresh romaine lettuce with caesar dressing and croutons',
        6,
        11.90,
        5.50,
        5,
        280,
        0,
        0,
        0,
        'Gluten, Dairy, Eggs',
        25,
        65
    ),
    (
        'Mozzarella Sticks (6pc)',
        'Crispy mozzarella sticks with marinara sauce',
        6,
        10.90,
        5.20,
        8,
        420,
        0,
        0,
        0,
        'Gluten, Dairy',
        35,
        80
    ),
    -- Drinks
    (
        'Coca Cola 375ml',
        'Classic soft drink',
        7,
        3.50,
        1.20,
        1,
        140,
        0,
        1,
        1,
        '',
        100,
        95
    ),
    (
        'Coca Cola 1.25L',
        'Classic soft drink family size',
        7,
        5.50,
        2.00,
        1,
        560,
        0,
        1,
        1,
        '',
        50,
        70
    ),
    (
        'Sprite 375ml',
        'Lemon-lime soft drink',
        7,
        3.50,
        1.20,
        1,
        135,
        0,
        1,
        1,
        '',
        80,
        75
    ),
    (
        'Orange Juice 375ml',
        'Fresh orange juice',
        7,
        4.50,
        2.00,
        1,
        160,
        0,
        1,
        1,
        '',
        60,
        60
    ),
    (
        'Water 600ml',
        'Still water',
        7,
        2.50,
        0.80,
        1,
        0,
        0,
        1,
        1,
        '',
        100,
        50
    ),
    (
        'Sparkling Water 375ml',
        'Sparkling mineral water',
        7,
        3.00,
        1.00,
        1,
        0,
        0,
        1,
        1,
        '',
        70,
        40
    ),
    -- Desserts
    (
        'Chocolate Brownie',
        'Rich chocolate brownie with vanilla ice cream',
        8,
        8.90,
        3.50,
        5,
        420,
        1,
        0,
        0,
        'Gluten, Dairy, Eggs, Nuts',
        20,
        85
    ),
    (
        'Tiramisu',
        'Classic Italian dessert',
        8,
        9.90,
        4.20,
        3,
        380,
        0,
        0,
        0,
        'Gluten, Dairy, Eggs',
        15,
        70
    ),
    (
        'Gelato (2 scoops)',
        'Choice of vanilla, chocolate, or strawberry',
        8,
        6.90,
        2.80,
        2,
        250,
        0,
        0,
        1,
        'Dairy',
        30,
        75
    );

-- Sample staff members
INSERT INTO
    staff (
        username,
        email,
        password_hash,
        full_name,
        role,
        store_id,
        hire_date,
        salary
    )
VALUES
    (
        'admin',
        'admin@crustpizza.com.au',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'System Administrator',
        'admin',
        1,
        '2023-01-01',
        75000.00
    ),
    (
        'kitchen1',
        'kitchen1@crustpizza.com.au',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'Mario Rossi',
        'kitchen',
        1,
        '2023-02-15',
        55000.00
    ),
    (
        'delivery1',
        'delivery1@crustpizza.com.au',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'James Wilson',
        'delivery',
        1,
        '2023-03-01',
        45000.00
    ),
    (
        'counter1',
        'counter1@crustpizza.com.au',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'Sarah Johnson',
        'counter',
        1,
        '2023-02-20',
        48000.00
    );

-- Sample customer
INSERT INTO
    users (
        username,
        email,
        password_hash,
        full_name,
        phone,
        address,
        date_of_birth,
        email_verified
    )
VALUES
    (
        'customer1',
        'customer@example.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'John Customer',
        '0412345678',
        '123 Test Street, Sydney NSW 2000',
        '1990-05-15',
        1
    );

-- Sample coupons
INSERT INTO
    coupons (
        code,
        name,
        description,
        discount_type,
        discount_value,
        minimum_order_amount,
        usage_limit,
        valid_until
    )
VALUES
    (
        'WELCOME10',
        'Welcome Discount',
        '10% off your first order',
        'percentage',
        10.00,
        25.00,
        1,
        '2024-12-31 23:59:59'
    ),
    (
        'PIZZA20',
        'Pizza Special',
        '$20 off orders over $50',
        'fixed_amount',
        20.00,
        50.00,
        100,
        '2024-12-31 23:59:59'
    ),
    (
        'STUDENT15',
        'Student Discount',
        '15% off with valid student ID',
        'percentage',
        15.00,
        20.00,
        1000,
        '2024-12-31 23:59:59'
    );

-- System settings
INSERT INTO
    system_settings (
        setting_key,
        setting_value,
        setting_type,
        description,
        is_public
    )
VALUES
    (
        'site_name',
        'Crust Pizza',
        'string',
        'Website name',
        1
    ),
    (
        'delivery_fee',
        '5.50',
        'number',
        'Standard delivery fee',
        1
    ),
    (
        'free_delivery_threshold',
        '35.00',
        'number',
        'Minimum order for free delivery',
        1
    ),
    ('tax_rate', '0.10', 'number', 'GST tax rate', 0),
    (
        'max_delivery_distance',
        '15',
        'number',
        'Maximum delivery distance in km',
        0
    ),
    (
        'order_prep_time',
        '20',
        'number',
        'Average order preparation time in minutes',
        1
    ),
    (
        'loyalty_points_rate',
        '1',
        'number',
        'Points earned per dollar spent',
        1
    ),
    (
        'points_redemption_value',
        '0.01',
        'number',
        'Dollar value per loyalty point',
        1
    );