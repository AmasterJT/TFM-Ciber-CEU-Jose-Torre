cd /opt/tfm-lab

sudo docker compose down -v --remove-orphans
sudo docker system prune -af
sudo rm -rf /opt/tfm-lab
sudo rm -rf /opt/scripts
sudo rm -f /etc/sudoers.d/tfm-lab-dev
sudo deluser --remove-home dev 2>/dev/null
sudo deluser --remove-home victima 2>/dev/null
sudo groupdel developers 2>/dev/null
sudo systemctl restart ssh


sudo groupadd developers 2>/dev/null
sudo useradd -m -s /bin/bash -g developers victima
echo 'victima:victima123' | sudo chpasswd

sudo mkdir -p /home/victima
sudo chown victima:developers /home/victima
sudo chmod 755 /home/victima