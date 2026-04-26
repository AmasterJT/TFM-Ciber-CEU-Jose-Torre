# Despliegue rápido

Copiar el contenido en `/var/www/html/portal_pyme/` y cargar la base de datos:

```bash
sudo mysql < /var/www/html/portal_pyme/config/portal_pyme.sql
sudo mysql -e "CREATE USER IF NOT EXISTS 'portal'@'127.0.0.1' IDENTIFIED BY 'portal123';"
sudo mysql -e "GRANT ALL PRIVILEGES ON portal_pyme.* TO 'portal'@'127.0.0.1'; FLUSH PRIVILEGES;"
sudo chown -R www-data:www-data /var/www/html/portal_pyme/uploads
```

Nota: `perfil.php` se conserva igual que en tu versión actual.
