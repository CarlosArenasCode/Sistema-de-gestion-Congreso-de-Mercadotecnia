# ⚡ INICIO RÁPIDO - Corrección Oracle

## 🚀 Ejecuta UNO de estos scripts:

### 🥇 OPCIÓN 1: Más Simple (RECOMENDADO para Windows)

**Doble clic en:**
```
aplicar-correcciones.bat
```

O ejecuta en CMD:
```cmd
aplicar-correcciones.bat
```

---

### 🥈 OPCIÓN 2: PowerShell Simple

```powershell
.\aplicar-correcciones-simple.ps1
```

---

### 🥉 OPCIÓN 3: PowerShell Completo (con más verificaciones)

```powershell
.\aplicar-correcciones-oracle.ps1
```

*Nota: Si da error de sintaxis, usa la Opción 1 o 2*

---

## ⏱️ ¿Cuánto tarda?

- **Primera vez**: 3-5 minutos
- **Siguientes veces**: 2-3 minutos

---

## ✅ ¿Qué hace el script?

1. ⏹️ Detiene contenedores actuales
2. 🏗️ Reconstruye imágenes con código corregido
3. ▶️ Inicia todos los servicios (Oracle, Web, WhatsApp)
4. ⏳ Espera a que Oracle esté listo
5. ✅ Muestra el estado final

---

## 🌐 URLs después de ejecutar:

- **Aplicación**: http://localhost:8080
- **Oracle EM**: http://localhost:5500/em  
- **WhatsApp**: http://localhost:3001

---

## 🆘 Si algo falla:

### Ver logs de Oracle:
```
docker-compose logs -f oracle_db
```

### Ver logs del servidor web:
```
docker-compose logs -f web
```

### Reintentar desde cero:
```
docker-compose down -v
aplicar-correcciones.bat
```

---

## 📚 Más información:

- **REPORTE_PROBLEMAS_ORACLE.md** - Qué se encontró y corrigió
- **GUIA_RAPIDA_CORRECCION.md** - Instrucciones detalladas
- **RESUMEN_CAMBIOS.md** - Comparación antes/después

---

## ✨ ¡Eso es todo!

Ejecuta el script y espera 5 minutos.  
Luego abre: **http://localhost:8080**

🎉 **¡Listo para usar!**
