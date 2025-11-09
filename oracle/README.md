# Scripts de Inicialización de Oracle Database

Este directorio contiene los scripts SQL necesarios para inicializar y configurar la base de datos Oracle para el Sistema de Gestión del Congreso de Mercadotecnia.

## Estructura de Carpetas

```
oracle/
├── init/          # Scripts que se ejecutan durante la configuración inicial (solo la primera vez)
│   ├── 01_create_user.sql       # Crear usuario y tablespace
│   └── 02_create_schema.sql     # Crear tablas y estructura de BD
└── startup/       # Scripts que se ejecutan cada vez que inicia el contenedor
    └── (vacío por ahora)
```

## Orden de Ejecución

Los scripts en la carpeta `init/` se ejecutan automáticamente en orden alfabético cuando el contenedor se inicia por primera vez:

1. **01_create_user.sql**: Crea el usuario `congreso_user` y el tablespace `congreso_data`
2. **02_create_schema.sql**: Crea todas las tablas del sistema

## Conexión a la Base de Datos

Después de que los contenedores estén corriendo, puedes conectarte usando:

### Desde el contenedor web (PHP):
```php
$dsn = "oci:dbname=//oracle_db:1521/FREEPDB1;charset=AL32UTF8";
$user = "congreso_user";
$pass = "congreso_pass";
```

### Desde SQL*Plus:
```bash
docker exec -it congreso_oracle_db sqlplus congreso_user/congreso_pass@FREEPDB1
```

### Desde Adminer (Web):
- URL: http://localhost:8081
- Sistema: Oracle
- Servidor: oracle_db:1521/FREEPDB1
- Usuario: congreso_user
- Contraseña: congreso_pass

## Credenciales

### Usuario de Aplicación:
- **Usuario**: congreso_user
- **Contraseña**: congreso_pass
- **Tablespace**: congreso_data

### Usuarios Administrativos de Oracle:
- **SYS** (como SYSDBA): OraclePass123!
- **SYSTEM**: OraclePass123!
- **PDBADMIN**: OraclePass123!

## Notas Importantes

- Los scripts en `init/` solo se ejecutan si el volumen de datos está vacío
- Si necesitas reinicializar la BD, elimina el volumen: `docker-compose -f docker-compose.oracle.yml down -v`
- El primer inicio puede tardar 3-5 minutos mientras Oracle se configura
