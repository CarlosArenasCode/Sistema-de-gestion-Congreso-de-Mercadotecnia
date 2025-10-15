# Servicio de Verificación por WhatsApp

FROM node:18-alpine

# Información del mantenedor
LABEL maintainer="GJA Team"
LABEL description="Servicio de verificación de códigos por WhatsApp"

# Instalar dependencias del sistema
RUN apk add --no-cache \
    git \
    chromium \
    nss \
    freetype \
    harfbuzz \
    ca-certificates \
    ttf-freefont

# Variables de entorno para Puppeteer
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium-browser

# Crear directorio de trabajo
WORKDIR /app

# Copiar archivos de dependencias
COPY package*.json ./

# Instalar dependencias
RUN npm install --omit=dev

# Copiar código fuente
COPY . .

# Crear directorio para sesiones de WhatsApp
RUN mkdir -p /app/.wwebjs_auth && \
    mkdir -p /app/.wwebjs_cache && \
    chmod -R 777 /app/.wwebjs_auth && \
    chmod -R 777 /app/.wwebjs_cache

# Exponer puerto
EXPOSE 3001

# Comando de inicio
CMD ["node", "index.js"]
