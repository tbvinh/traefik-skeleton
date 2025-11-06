docker compose --env-file .env.prod up -d
docker exec -it traefik-ssl-php-fpm-1 php -i | grep opcache.validate_timestamps

#Tạo cert nội bộ
certs/ssl.conf
[req]
default_bits       = 2048
prompt             = no
default_md         = sha256
req_extensions     = req_ext
distinguished_name = dn

[dn]
CN = nail360prod.com

[req_ext]
subjectAltName = @alt_names

[alt_names]
DNS.1 = nail360prod.com
DNS.2 = *.nail360prod.com

#chay

openssl req -x509 -nodes -days 365 \
  -newkey rsa:2048 \
  -keyout nail360.key \
  -out nail360.crt \
  -config ssl.conf \
  -extensions req_ext

# tạo file: dynamic/tls.yml
# sửa file hosts: <ip address> nail360prod.com api.nail360prod.com

tls:
  certificates:
    - certFile: "/certs/nail360.crt"
      keyFile: "/certs/nail360.key"

#
docker-compose down
docker-compose up -d

# docker-compose.yml

services:
  traefik:
    image: "traefik:v3.4"
    container_name: "traefik"
    restart: unless-stopped
    security_opt:
      - no-new-privileges:true
    networks:
      - proxy
    command:
      - "--api.insecure=false"
      - "--api.dashboard=true"
      - "--providers.docker=true"
      - "--providers.docker.exposedbydefault=false"
      - "--providers.docker.network=proxy"
      - "--providers.file.directory=/etc/traefik/dynamic"
      - "--entryPoints.web.address=:80"
      - "--entryPoints.websecure.address=:443"
      - "--entryPoints.websecure.http.tls=true"
    ports:
      - "80:80"
      - "443:443"
      - "8080:8080"
    volumes:
      - "/var/run/docker.sock:/var/run/docker.sock:ro"
      - "./certs:/certs:ro"
      - "./dynamic:/etc/traefik/dynamic:ro"
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.dashboard.rule=Host(`nail360prod.com`)"
      - "traefik.http.routers.dashboard.entrypoints=websecure"
      - "traefik.http.routers.dashboard.service=api@internal"
      - "traefik.http.routers.dashboard.tls=true"

  whoami:
    image: "traefik/whoami"
    restart: unless-stopped
    networks:
      - proxy
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.whoami.rule=Host(`nail360prod.com`) && PathPrefix(`/whoami`)"
      - "traefik.http.routers.whoami.entrypoints=websecure"
      - "traefik.http.routers.whoami.tls=true"

  whoami-api:
    image: "traefik/whoami"
    restart: unless-stopped
    networks:
      - proxy
    environment:
      - WHOAMI_NAME=API Service
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.whoami-api.rule=Host(`api.nail360prod.com`)"
      - "traefik.http.routers.whoami-api.entrypoints=websecure"
      - "traefik.http.routers.whoami-api.tls=true"

networks:
  proxy:
    name: proxy
#
