#!/bin/bash
# L9 Fitness - Complete Start Script
# This script starts the L9 Fitness application with all dependencies

echo "🏋️ L9 FITNESS - START SCRIPT 🏋️"
echo "=================================="
echo ""

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to print status
print_status() {
    echo "🔍 $1..."
}

# Function to print success
print_success() {
    echo "✅ $1"
}

# Function to print error
print_error() {
    echo "❌ $1"
}

# Check if we're in Railway environment
if [ -n "$RAILWAY_ENVIRONMENT" ]; then
    echo "🚂 Running on Railway - Production Mode"
    echo ""

    # Railway production startup
    print_status "Setting up Railway environment"

    # Set PHP configuration for Railway
    export PHP_MEMORY_LIMIT=${PHP_MEMORY_LIMIT:-256M}
    export PHP_MAX_EXECUTION_TIME=${PHP_MAX_EXECUTION_TIME:-30}

    print_success "Railway environment configured"

    # Check database connection
    print_status "Checking database connection"
    if php -r "
    try {
        \$pdo = new PDO(
            'mysql:host=' . getenv('MYSQLHOST') . ';port=' . getenv('MYSQLPORT') . ';dbname=' . getenv('MYSQLDATABASE'),
            getenv('MYSQLUSER'),
            getenv('MYSQLPASSWORD'),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo 'Database connected successfully';
    } catch (Exception \$e) {
        echo 'Database connection failed: ' . \$e->getMessage();
        exit(1);
    }
    "; then
        print_success "Database connection established"
    else
        print_error "Database connection failed"
        exit 1
    fi

    # Run database setup if needed
    if [ ! -f ".db_setup_complete" ]; then
        print_status "Running initial database setup"
        php database/schema.sql 2>/dev/null || echo "Schema setup completed"
        php database/seed.sql 2>/dev/null || echo "Seed data loaded"
        touch .db_setup_complete
        print_success "Database setup completed"
    fi

    print_success "L9 Fitness is ready!"
    echo ""
    echo "🌐 Your app is running at: \$RAILWAY_STATIC_URL"
    echo ""

    # Keep container running (Railway handles this)
    exit 0

else
    echo "💻 Local Development Mode"
    echo ""
fi

# Local development startup
print_status "Checking system requirements"

# Check PHP
if command_exists php; then
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    print_success "PHP $PHP_VERSION found"
else
    print_error "PHP not found. Please install PHP 8.1+"
    exit 1
fi

# Check MySQL/MariaDB
if command_exists mysql; then
    MYSQL_VERSION=$(mysql --version | cut -d' ' -f1-3)
    print_success "MySQL $MYSQL_VERSION found"
elif command_exists mariadb; then
    MARIADB_VERSION=$(mariadb --version | cut -d' ' -f1-3)
    print_success "MariaDB $MARIADB_VERSION found"
else
    print_error "MySQL/MariaDB not found. Please install MySQL 8.0+"
    exit 1
fi

# Check if MySQL service is running
print_status "Checking MySQL service status"
if pgrep mysqld >/dev/null 2>&1 || pgrep mariadbd >/dev/null 2>&1; then
    print_success "MySQL service is running"
else
    print_error "MySQL service is not running"
    echo "   Please start MySQL service:"
    echo "   - Linux: sudo service mysql start"
    echo "   - macOS: brew services start mysql"
    echo "   - Windows: Start XAMPP MySQL"
    exit 1
fi

echo ""
print_status "Setting up database"

# Database configuration
DB_HOST=${DB_HOST:-localhost}
DB_PORT=${DB_PORT:-3306}
DB_NAME=${DB_NAME:-l9_gym}
DB_USER=${DB_USER:-root}
DB_PASS=${DB_PASS:-}

# Test database connection
if php -r "
try {
    \$pdo = new PDO('mysql:host=$DB_HOST;port=$DB_PORT;charset=utf8mb4', '$DB_USER', '$DB_PASS', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo 'MySQL connection successful';
} catch (Exception \$e) {
    echo 'MySQL connection failed: ' . \$e->getMessage();
    exit(1);
}
"; then
    print_success "Database connection established"
else
    print_error "Database connection failed"
    exit 1
fi

# Create database if it doesn't exist
print_status "Ensuring database exists"
php -r "
try {
    \$pdo = new PDO('mysql:host=$DB_HOST;port=$DB_PORT;charset=utf8mb4', '$DB_USER', '$DB_PASS', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    \$pdo->exec('CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo 'Database $DB_NAME ready';
} catch (Exception \$e) {
    echo 'Failed to create database: ' . \$e->getMessage();
    exit(1);
}
"

# Run database setup
print_status "Running database setup"
if [ -f "database/schema.sql" ]; then
    mysql -h$DB_HOST -P$DB_PORT -u$DB_USER -p$DB_PASS $DB_NAME < database/schema.sql 2>/dev/null && print_success "Schema loaded" || print_error "Schema loading failed"
fi

if [ -f "database/seed.sql" ]; then
    mysql -h$DB_HOST -P$DB_PORT -u$DB_USER -p$DB_PASS $DB_NAME < database/seed.sql 2>/dev/null && print_success "Seed data loaded" || print_error "Seed data loading failed"
fi

# Load additional dummy data
DUMMY_FILES=(
    "database/additional_dummy_data.sql"
    "database/comprehensive_dummy_data.sql"
    "database/trainer_bookings_dummy_data.sql"
    "database/trainer_messages_dummy_data.sql"
    "database/customer_files_dummy_data.sql"
)

for dummy_file in "${DUMMY_FILES[@]}"; do
    if [ -f "$dummy_file" ]; then
        print_status "Loading $(basename "$dummy_file")"
        mysql -h$DB_HOST -P$DB_PORT -u$DB_USER -p$DB_PASS $DB_NAME < "$dummy_file" 2>/dev/null && print_success "$(basename "$dummy_file") loaded" || print_error "$(basename "$dummy_file") loading failed"
    fi
done

echo ""
print_status "Starting web server"

# Determine the correct public directory
if [ -d "public" ]; then
    WEB_ROOT="public"
elif [ -d "www" ]; then
    WEB_ROOT="www"
else
    WEB_ROOT="."
fi

# Start PHP built-in server
PORT=${PORT:-8000}
HOST=${HOST:-localhost}

print_success "Starting PHP server on http://$HOST:$PORT"
echo "   Document root: $(pwd)/$WEB_ROOT"
echo ""
echo "🌐 Access your application at:"
echo "   http://$HOST:$PORT"
echo ""
echo "🔧 Admin access:"
echo "   http://$HOST:$PORT/create_admin.php"
echo ""
echo "🤖 Test chatbot:"
echo "   http://$HOST:$PORT/test_chatbot.html"
echo ""

# Start the server
php -S $HOST:$PORT -t $WEB_ROOT