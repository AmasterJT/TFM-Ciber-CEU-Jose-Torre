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