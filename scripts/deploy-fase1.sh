#!/bin/bash
# PYME Vulnerable Lab - Fase 1 (A01, A02, A05 OWASP 2025)
# TFM Ciberseguridad CEU - Jose Tomas Torre

set -e

echo "🚀 Desplegando PYME Vulnerable Lab - Fase 1..."
echo "OWASP: A01 Broken Access Control | A02 Security Misconfig | A05 Injection"

# 1. Update & paquetes base
apt update && apt upgrade -y
apt install -y apache2 mysql-server php libapache2-mod-php php-mysql php-curl php-gd php-xml wget unzip git curl

# 2. A02: Security Misconfiguration
echo "🔧 Configurando misconfigs OWASP A02..."

# PHP: display_errors ON, expose_php ON
sed -i 's/display_errors = Off/display_errors = On/' /etc/php/8.1/apache2/php.ini
sed -i 's/expose_php = Off/expose_php = On/' /etc/php/8.1/apache2/php.ini

# Apache: ServerTokens Full, mod_status expuesto
echo 'ServerTokens Full' >> /etc/apache2/conf-available/security.conf
a2enmod status
echo '<Location /server-status>' > /etc/apache2/conf-available/server-status.conf
echo '    SetHandler server-status' >> /etc/apache2/conf-available/server-status.conf
echo '    Require ip 127.0.0.1 192.168.56.0/24' >> /etc/apache2/conf-available/server-status.conf
echo '</Location>' >> /etc/apache2/conf-available/server-status.conf
a2enconf server-status

# .htaccess y .git expuestos
rm -f /var/www/html/.htaccess
touch /var/www/html/.htaccess
echo "Options Indexes FollowSymLinks" > /var/www/html/.htaccess

# 3. A05: MySQL vulnerable (root sin pass)
echo "🗄️ Configurando MySQL vulnerable OWASP A05..."
systemctl start mysql
mysql -e "CREATE DATABASE pyme_db;"
mysql -e "CREATE USER 'pyme_user'@'%' IDENTIFIED BY 'password123';"
mysql -e "GRANT ALL PRIVILEGES ON pyme_db.* TO 'pyme_user'@'%';"
mysql -e "FLUSH PRIVILEGES;"

# 4. A01: DVWA con Broken Access Control
echo "🌐 Desplegando DVWA con Access Control roto..."
cd /var/www/html
wget https://github.com/digininja/DVWA/archive/master.zip
unzip master.zip
mv DVWA-master dvwa
chown -R www-data:www-data dvwa
chmod -R 755 dvwa

# Config DVWA vulnerable
cat > dvwa/config/config.inc.php << 'EOF'
<?php
\$GLOBALS['DB']['server'] = '127.0.0.1';
\$GLOBALS['DB']['user'] = 'pyme_user';
\$GLOBALS['DB']['password'] = 'password123';
\$GLOBALS['DB']['database'] = 'pyme_db';
\$GLOBALS['DVWA']['default_security_level'] = 'low';
?>
EOF

# 5. App PYME custom (A01 + A05)
echo "🏢 Creando app PYME vulnerable..."
mkdir -p /var/www/html/pyme
cat > /var/www/html/pyme/index.php << 'EOF'
<?php
// A01: Broken Access Control - ID directo en URL
// A05: SQL Injection sin prepared statements
if (isset($_GET['client_id'])) {
    $client_id = $_GET['client_id'];
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=pyme_db', 'pyme_user', 'password123');
    $stmt = $pdo->query("SELECT * FROM clients WHERE id = $client_id");  // SQLi!
    $client = $stmt->fetch();
    echo "<h1>Cliente: " . $client['name'] . "</h1>";
    echo "<p>Email: " . $client['email'] . "</p>";
    echo "<p>CC: " . $client['cc_number'] . "</p>";  // DATA LEAK!
} else {
    echo "<h1>PYME Startup - Gestión Clientes</h1>";
    echo '<a href="?client_id=1">Ver Cliente 1</a> | ';
    echo '<a href="?client_id=2">Ver Cliente 2</a>';
}
?>
EOF

# Datos de prueba SQLi + Access Control
mysql pyme_db -u pyme_user -ppassword123 << 'EOF'
CREATE TABLE clients (
    id INT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    cc_number VARCHAR(20)
);
INSERT INTO clients VALUES 
(1, 'Juan Perez', 'juan@empresa.com', '4532123456789012'),
(2, 'Maria Gomez', 'maria@competencia.com', '4532987654321098');
EOF

# 6. Restart servicios
systemctl restart apache2 mysql
a2enmod rewrite
a2dissite 000-default
a2ensite 000-default

echo "✅ Fase 1 completada!"
echo "🌐 URLs vulnerables:"
echo "   DVWA: http://192.168.56.10/dvwa"
echo "   PYME: http://192.168.56.10/pyme/?client_id=1"
echo "   Status: http://192.168.56.10/server-status"
echo ""
echo "💥 Explotaciones listas:"
echo "   A05 SQLi: http://192.168.56.10/pyme/?client_id=1 OR 1=1"
echo "   A01 Access: http://192.168.56.10/pyme/?client_id=2 (ver otros clientes)"