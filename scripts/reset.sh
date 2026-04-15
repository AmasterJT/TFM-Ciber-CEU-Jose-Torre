#!/bin/bash
# PYME Vulnerable Lab - Reset a estado inicial
# TFM Ciberseguridad CEU - Jose Tomas Torre

set -e

echo "🔄 Resetando PYME Vulnerable Lab a estado Fase 1..."
echo "Esto restaura DB, archivos y configs vulnerables"

cd /var/www/html

# 1. Reset DVWA (A01 Access Control)
echo "🔧 Restaurando DVWA..."
rm -rf dvwa
wget -q https://github.com/digininja/DVWA/archive/master.zip
unzip -q master.zip
mv DVWA-master dvwa
chown -R www-data:www-data dvwa
chmod -R 755 dvwa

# Config DVWA low security
cat > dvwa/config/config.inc.php << 'EOF'
<?php
$GLOBALS['DB']['server'] = '127.0.0.1';
$GLOBALS['DB']['user'] = 'pyme_user';
$GLOBALS['DB']['password'] = 'password123';
$GLOBALS['DB']['database'] = 'pyme_db';
$GLOBALS['DVWA']['default_security_level'] = 'low';
?>
EOF

# 2. Reset app PYME (A05 SQLi + A01 Access Control)
echo "🗄️ Restaurando app PYME..."
rm -rf pyme
mkdir pyme
cat > pyme/index.php << 'EOF'
<?php
if (isset($_GET['client_id'])) {
    $client_id = $_GET['client_id'];
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=pyme_db', 'pyme_user', 'password123');
    $stmt = $pdo->query("SELECT * FROM clients WHERE id = $client_id");
    $client = $stmt->fetch();
    echo "<h1>Cliente: " . $client['name'] . "</h1>";
    echo "<p>Email: " . $client['email'] . "</p>";
    echo "<p>CC: " . $client['cc_number'] . "</p>";
} else {
    echo "<h1>PYME Startup - Gestión Clientes</h1>";
    echo '<a href="?client_id=1">Ver Cliente 1</a> | ';
    echo '<a href="?client_id=2">Ver Cliente 2</a>';
}
?>
EOF'
chown -R www-data:www-data pyme

# 3. Reset MySQL DB (A05 SQLi)
echo "🗄️ Restaurando base de datos..."
mysql -u root -e "DROP DATABASE IF EXISTS pyme_db;"
mysql -u root -e "CREATE DATABASE pyme_db;"
mysql -u root -e "GRANT ALL PRIVILEGES ON pyme_db.* TO 'pyme_user'@'%';"
mysql -u root -e "FLUSH PRIVILEGES;"

mysql pyme_db -u pyme_user -ppassword123 << 'EOF'
CREATE TABLE clients (
    id INT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    cc_number VARCHAR(20)
);
INSERT INTO clients VALUES 
(1, 'Juan Perez', 'juan@empresa.com', '4532123456789012'),
(2, 'Maria Gomez', 'maria@competencia.com', '4532987654321098'),
(3, 'Pedro Lopez', 'pedro@cliente3.com', '4532778899001122');
EOF

# 4. Verificar configs Apache/PHP (A02 Misconfig)
echo "🔍 Verificando misconfigs A02..."
grep -q "display_errors = On" /etc/php/8.1/apache2/php.ini || echo "⚠️ PHP display_errors OFF!"
grep -q "expose_php = On" /etc/php/8.1/apache2/php.ini || echo "⚠️ PHP expose_php OFF!"
a2enmod status 2>/dev/null || echo "✅ mod_status activo"

# 5. Restart servicios
systemctl restart apache2 mysql

# 6. Test rápido
echo "✅ Reset completado!"
echo ""
echo "🌐 URLs listas para explotar:"
echo "   PYME SQLi:     http://192.168.56.10/pyme/?client_id=1"
echo "   SQLi Exploit:  http://192.168.56.10/pyme/?client_id=1 OR 1=1"
echo "   DVWA Low:      http://192.168.56.10/dvwa/"
echo "   Server Status: http://192.168.56.10/server-status"
echo ""
echo "💥 Prueba rápida SQLi:"
curl -s "http://192.168.56.10/pyme/?client_id=1 OR 1=1" | grep -i "cliente"