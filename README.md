# Bienvenido al Proyecto Sistema de gestión Congreso de Mercadotecnia

¡Hola equipo!

Este documento es nuestra guía fundamental para colaborar en este proyecto. Su propósito es establecer un flujo de trabajo claro y consistente utilizando Git y GitHub. Seguir estas reglas nos ayudará a mantener un código de alta calidad, evitar errores y trabajar de manera más eficiente.

## Principios Fundamentales

Nuestra filosofía de trabajo se basa en dos ideas clave:

1. **El trabajo se organiza a través de Issues.** Todo desarrollo, corrección o mejora comienza con un Issue asignado. Esto nos permite tener un registro claro de las tareas pendientes y completadas.
2. **La rama main es sagrada. 🛡️** La rama main siempre debe contener código estable, funcional y listo para ser desplegado. Nadie, bajo ninguna circunstancia, debe subir cambios directamente a main.

## Nuestro Flujo de Trabajo (Paso a Paso)

### 0. 📝 Recibe tu Tarea (Issue)
Todo comienza en la pestaña "Issues" de GitHub. Se te asignará un Issue con un número único (ej: #42). Este número es tu referencia para todo el trabajo relacionado con esa tarea.

### 1. 🌿 Crea una Rama desde main
Antes de escribir código, asegúrate de tener la versión más reciente de main y crea una nueva rama para tu tarea, incluyendo el número del Issue en el nombre.

**Nomenclatura de Ramas:**
- feature/#123-nombre-descriptivo
- bugfix/#123-descripcion-del-bug
- hotfix/#123-arreglo-urgente

```bash
# 1. Asegúrate de estar en la rama principal y tenerla actualizada
git checkout main
git pull origin main

# 2. Crea tu nueva rama y muévete a ella (ejemplo con el Issue #42)
git checkout -b feature/#42-login-con-google
```

### 2. 💾 Trabaja y Haz Commits Atómicos
Realiza tu trabajo en la nueva rama. Haz commits pequeños y frecuentes. Cada mensaje de commit debe hacer referencia al Issue que estás resolviendo.

**Formato para mensajes de commit:**
- tipo: Mensaje descriptivo (#issue)
- tipo: feat (nueva funcionalidad), fix (corrección de error), docs (documentación), style (formato), refactor, test, chore (tareas de mantenimiento).

Ejemplo:
```bash
# 1. Añade los archivos que has modificado
git add .

# 2. Crea el commit con el formato correcto
git commit -m "feat: Agrega validación de formulario en el login (#42)"
```

### 3. 🚀 Sube tu Rama y Abre un Pull Request (PR)
Cuando termines, sube tu rama a GitHub y crea un Pull Request.

```bash
# Sube tu rama al repositorio remoto
git push -u origin feature/#42-login-con-google
```

Luego, ve a GitHub para crear el Pull Request.

**Título del PR:** Debe ser claro e incluir el número del Issue. Ej: feat: Implementar Login con Google (#42)

**Descripción del PR:** Usa las "palabras mágicas" de GitHub para vincular y cerrar el Issue automáticamente cuando el PR se fusione.

Ejemplo de descripción de un PR:

```
## Descripción
Este PR implementa la funcionalidad de login usando la API de Google, como se detalla en el issue #42.

## Cambios
- Se agregó el SDK de Google.
- Se creó el componente `BotonGoogle`.
- Se actualizó el estado de autenticación.

Closes #42
```

(Usar Closes, Fixes o Resolves seguido del #numero-issue cerrará el Issue automáticamente al hacer merge)

### 4. 💬 Proceso de Revisión de Código
Un miembro del equipo revisará tu PR. Atiende los comentarios realizando nuevos commits en tu rama (recuerda seguir el formato de mensajes de commit). GitHub actualizará el PR automáticamente.

### 5. ✨ Fusión (Merge) y Limpieza
Una vez que tu PR sea aprobado, el administrador del repositorio lo fusionará con main. Esto automáticamente cerrará el Issue vinculado.

Finalmente, por limpieza, elimina tu rama:

```bash
# 1. Vuelve a la rama principal
git checkout main

# 2. Borra la rama local que ya no necesitas
git branch -d feature/#42-login-con-google
```

## 📜 Reglas de Nuestra Rama main
Para garantizar la calidad y estabilidad de nuestro código, hemos configurado las siguientes reglas de protección en nuestra rama main. Es vital que entiendas por qué existen:

### Configuración Esencial
- ✅ Require a pull request before merging
- ✅ Require approvals
- ✅ Do not allow bypassing the above settings

### Mejores Prácticas de Calidad (Muy Recomendado)
- ✅ Dismiss stale pull request approvals when new commits are pushed
- ✅ Require conversation resolution before merging
- ✅ Require branches to be up to date before merging
- 🔘 Require status checks to pass before merging

## Recursos para Aprender Git y GitHub
- [Guía oficial de GitHub](https://docs.github.com/es/get-started)
- [Curso básico de Git en YouTube](https://www.youtube.com/watch?v=JFPw4l6y7eY)
- [Documentación de Git](https://git-scm.com/doc)

## Glosario Rápido
- **Issue:** Tarea o problema a resolver.
- **Branch (Rama):** Línea de desarrollo separada.
- **Pull Request (PR):** Solicitud para fusionar una rama con main.
- **Merge:** Acción de combinar ramas.

## Herramientas recomendadas
- [GitHub Desktop](https://desktop.github.com/)
- [Visual Studio Code](https://code.visualstudio.com/) (con extensión de Git)

## Errores Comunes y Soluciones
- **Conflictos de merge:** Sigue las instrucciones de Git, resuelve los archivos en conflicto y haz un commit.
- **Olvidé referenciar el Issue:** Puedes editar el mensaje del commit con `git commit --amend` o agregar la referencia en el PR.

---

¡Sigamos este flujo y lograremos un trabajo profesional y colaborativo!