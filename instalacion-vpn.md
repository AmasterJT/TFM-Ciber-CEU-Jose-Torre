Una vez instalamos Ubuntu server

## CONFIGURACIÓN DEL HARDWARE VIRTUAL DE LA VPN

En VMware debemos agregar una interfaz de red adicional para porder tener conexion bidirecional con el servido web (maquina victima)

La maquina victima se encuenta bajo una red Host-Only (VMnet10) que es la misma red a la que debemos conectar la VPN

### ⚙️ Confiuracion VPN

En VMware

- Selecionar el servidor vpn
- VM -> Settings -> add -> Network Adapter
- Seleccionar Network Adapter 2 -> Custom -> VMnet10

---

## 🚀 1. Actualizar el sistema

```bash
sudo apt update && sudo apt upgrade -y
```

## 📦 2. Instalar OpenVPN + Easy-RSA

```bash
sudo apt install openvpn easy-rsa -y
```

👉 `easy-rsa` se usa para generar certificados (clave en VPN segura).

## 📁 3. Preparar infraestructura de certificados (PKI)

```bash
mkdir ~/openvpn-ca
cp -r /usr/share/easy-rsa ~/openvpn-ca
cd ~/openvpn-ca
```

### Editar configuración básica

```bash
nano vars
```

```
set_var EASYRSA_REQ_COUNTRY    "ES"
set_var EASYRSA_REQ_PROVINCE   "Madrid"
set_var EASYRSA_REQ_CITY       "Madrid"
set_var EASYRSA_REQ_ORG        "LabRedTeam"
set_var EASYRSA_REQ_EMAIL      "admin@lab.local"
```

## 🔐 4. Crear CA (Autoridad Certificadora)

```
./easyrsa init-pki
./easyrsa build-ca
```

👉 Se pedirá:

- una passphrase (recomendado ponerla): vpn123
- un Common Name → puedes dejar Easy-RSA CA o poner algo como:

```bash
VPN-LAB-CA
```

## 🖥️ 5. Crear certificado del servidor

```bash
./easyrsa build-server-full server nopass
```

👉 Esto genera:

- server.crt
- server.key

✔️ nopass → evita pedir contraseña al arrancar OpenVPN (recomendado en servidor)

## 👤 6. Crear certificado de cliente

```bash
./easyrsa build-client-full kali1 nopass
```

👉 Se Puede crear varios:

```bash
./easyrsa build-client-full kali2 nopass
```

## 🔑 7. Generar parámetros DH

```bash
./easyrsa gen-dh
```

## 🛡️ 8. Generar clave TLS (anti ataques)

```bash
openvpn --genkey --secret ta.key
```

## 📂 9. Copiar archivos al directorio de OpenVPN

```bash
sudo cp pki/ca.crt /etc/openvpn/
sudo cp pki/private/server.key /etc/openvpn/
sudo cp pki/issued/server.crt /etc/openvpn/
sudo cp pki/dh.pem /etc/openvpn/
sudo cp ta.key /etc/openvpn/
```

## 📁 10. Archivos para el cliente (IMPORTANTE)

Para Kali se necesita copiar el contenido de:

```bash
pki/ca.crt
pki/issued/kali1.crt
pki/private/kali1.key
ta.key
```
👉 Luego los meteremos en un `.ovpn`

---

Llegado a este punto fala:

- Crear server.conf 
- Activar IP forwarding
- Configurar NAT (iptables) 
- Crear .ovpn para Kali

## ⚙️ 11. Crear server.conf

Editar el sioguiente fichero del servidor:

```bash
sudo nano /etc/openvpn/server.conf
```

Contenido recomendado (adaptado a la red):

```bash
port 1194
proto udp
dev tun

ca ca.crt
cert server.crt
key server.key
dh dh.pem

tls-auth ta.key 0

server 10.8.0.0 255.255.255.0

# Red interna de tu laboratorio
push "route 192.168.66.0 255.255.255.0"

keepalive 10 120

cipher AES-256-CBC
auth SHA256

user nobody
group nogroup

persist-key
persist-tun

status /var/log/openvpn-status.log
log /var/log/openvpn.log

verb 3
```

## 🔄 12. Activar IP Forwarding (CRÍTICO)

Editar

```bash
sudo nano /etc/sysctl.conf
```

Buscar y descomentar:

```bash
net.ipv4.ip_forward=1
```

Aplicamos los cambios:

```bash
sudo sysctl -p
```

## 🔥 13. Configurar NAT (iptables)

👉 Primero identificamod la interfaz interna:

```bash
ip a
```

Ejemplo:

- `eth0` → internet
- `eth1` → red interna (192.168.66.0/24)

### Añadir NAT:

```bash
sudo iptables -t nat -A POSTROUTING -s 10.8.0.0/24 -o eth1 -j MASQUERADE
```

👉 Ajustar `eth1` según tu caso.

### Permitir forwarding:

```bash 
sudo iptables -A FORWARD -s 10.8.0.0/24 -d 192.168.66.0/24 -j ACCEPT
sudo iptables -A FORWARD -d 10.8.0.0/24 -m state --state ESTABLISHED,RELATED -j ACCEPT
```

### Guardar reglas

```bash 
sudo apt install iptables-persistent -y
sudo netfilter-persistent save
```

## ▶️ 14. Arrancar OpenVPN

```bash
sudo systemctl start openvpn@server
sudo systemctl enable openvpn@server
```

Comprobar:

```bash
sudo systemctl status openvpn@server
```

## 📁 15. Crear archivo .ovpn para Kali

En el servidor:

```bash
nano kali1.ovpn
```

``` bash 
client
dev tun
proto udp

remote <IP_DEL_SERVIDOR> 1194

resolv-retry infinite
nobind

persist-key
persist-tun

remote-cert-tls server

cipher AES-256-CBC
auth SHA256

verb 3

<ca>
# pega aquí ca.crt
</ca>

<cert>
# pega aquí kali1.crt
</cert>

<key>
# pega aquí kali1.key
</key>

<tls-auth>
# pega aquí ta.key
</tls-auth>

key-direction 1
```

### 📌 Cómo rellenar los bloques

Ejemplo:

```bash
cat pki/ca.crt
```

👉 Copias TODO dentro de <ca>

## 📤 16. Pasar el .ovpn a Kali

Opciones:

- scp (recomendable para comprobar la conexión ssh)
- copiar/pegar
- carpeta compartida VMware

Ejemplo:

```bash
scp kali1.ovpn kali@IP_KALI:/home/kali/
```

## 🧪 17. Conectar desde Kali

```bash
sudo openvpn --config kali1.ovpn
```

## ✅ 18. Verificar

En Kali:

```bash
ip a
```

👉 Debes ver:

```
10.8.0.x
```

### Probar conexión

```bash
ping 192.168.66.10
```

👉 Tu máquina vulnerable

---

# 🧠 Resultado final

Tendremos:

```Kali → VPN → servidor → red interna → víctima```

👉 EXACTAMENTE un escenario de Red Team distribuido
