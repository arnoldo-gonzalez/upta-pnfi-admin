# Aplicación Web para la Gestión Administrativa de la Coordinación de Proyectos Socio-Tecnológicos del PNFI

**Universidad Politécnica Territorial del Estado Aragua "Federico Brito Figueroa" (UPTA) - Extensión Maracay**
*Coordinación de Creación Intelectual y Desarrollo Socio Productivo*
*Programa Nacional de Formación en Informática (PNFI)*

## 📖 Descripción del Proyecto

Este proyecto consiste en el desarrollo de una aplicación web orientada a centralizar y optimizar la gestión administrativa de la Coordinación de Proyectos Socio-Tecnológicos del PNFI. Actualmente, procesos como el registro de protocolos, la asignación de tutores y la gestión de evaluaciones se realizan mediante dinámicas manuales y herramientas de oficina locales, lo que genera retrasos y desorganización.

La aplicación busca sustituir estos flujos manuales por un ecosistema digital que permita a coordinadores, docentes y estudiantes interactuar de manera eficiente, transparente y centralizada. El proyecto se desarrolla como requisito académico para el **Trayecto II** de la carrera de Ingeniería en Informática, bajo la metodología ágil **Scrum**.

## ✨ Módulos Principales

El sistema está estructurado en cuatro procesos funcionales clave:

1.  **Gestión de Protocolos:** Registro de propuestas de investigación, validación de requisitos académicos, y revisión por parte de la coordinación (aprobación o devolución con observaciones).
2.  **Inscripción de Proyectos:** Formalización del proyecto una vez aprobado el protocolo. Generación de un código único de identificación y emisión del acta de inscripción digital en formato PDF.
3.  **Gestión de Tutores:** Selección de tutores académicos por parte de los estudiantes, aceptación por parte del docente, y habilitación de una bitácora digital para el registro de minutas y avances de asesorías.
4.  **Gestión de Evaluaciones:** Asignación de jurados expertos, programación de socializaciones, registro de veredictos y calificaciones, y generación de actas de evaluación digitales.

## 🛠️ Stack Tecnológico

*   **Backend:** Laravel (PHP)
*   **Base de Datos:** MySQL 8.0+
*   **Frontend:** Tailwind CSS, JavaScript, HTML, CSS (compilado con Vite)
*   **Control de Versiones:** Git & GitHub
*   **Gestión de Proyecto:** Scrum (Tablero Kanban en GitHub Projects)

## ⚙️ Requisitos Previos

Asegúrate de tener instalado lo siguiente en tu entorno de desarrollo:

*   [PHP >= 8.3](https://www.php.net/)
*   [Composer](https://getcomposer.org/)
*   [Node.js y NPM](https://nodejs.org/)
*   [MySQL 8.0+](https://dev.mysql.com/downloads/)
*   [Git](https://git-scm.com/)

## 🚀 Instalación y Configuración Local

Sigue estos pasos para levantar el proyecto en tu máquina:

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/tu-usuario/nombre-del-repositorio.git
   cd nombre-del-repositorio
   ```

2. **Instalar dependencias de PHP:**
   ```bash
   composer install
   ```

3. **Instalar dependencias de Frontend:**
   ```bash
   npm install
   ```

4. **Configurar el entorno:**
   Copia el archivo de ejemplo y genera la clave de la aplicación:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configurar la Base de Datos:**
   Abre el archivo `.env` y actualiza las credenciales de tu base de datos MySQL:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nombre_de_tu_bd
   DB_USERNAME=tu_usuario
   DB_PASSWORD=tu_contraseña
   DB_CHARSET=utf8mb4
   DB_COLLATION=utf8mb4_unicode_ci
   ```

6. **Ejecutar las migraciones (y seeders si están disponibles):**
   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```
   *(Nota: El flag `--seed` es opcional, úsalo si necesitas poblar la base de datos con datos de prueba. Los usuarios de prueba usan la contraseña `password`; cámbiala en producción).*

7. **Compilar los assets y levantar el servidor:**
   En dos terminales distintas, ejecuta:
   ```bash
   # Terminal 1 (Vite)
   npm run dev

   # Terminal 2 (Servidor Laravel)
   php artisan serve
   ```

8. **Acceder a la aplicación:**
   Abre tu navegador e ingresa a `http://localhost:8000`

9. **Configuración inicial (obligatoria para operar):**
   Con la cuenta de administrador (`admin@uptamcy.com` / `password`):
   - Sube la plantilla `.docx` del protocolo en **Administración → Plantilla de protocolo**. Sin plantilla activa no se pueden enviar protocolos.
   - Crea el trimestre y las ventanas de `protocol_submission` y `tutor_request` en **Calendario académico**. Sin ventana vigente, el sistema considera el plazo cerrado.
   - Crea la sección y asígnale un asesor en **Secciones**.

> Zona horaria: el sistema usa `APP_TIMEZONE=America/Caracas` por defecto. Ajusta `APP_URL` si se despliega en un subdirectorio.

## 🧪 Pruebas

Para ejecutar las pruebas automatizadas del sistema (usan SQLite en memoria, no tocan la base de datos real):
```bash
php artisan test
```

## 👥 Equipo de Desarrollo

*   **Arnoldo González** - CI: 32.077.908
*   **Sebastián Bautista** - CI: 32.733.660
*   **Yugerby Méndez** - CI: 32.368.696

**Asesor Académico:** Luis Eduardo Aponte Izaguirre

## 📄 Licencia

Este proyecto fue desarrollado con fines académicos para la Universidad Politécnica Territorial del Estado Aragua "Federico Brito Figueroa". Todos los derechos reservados a sus autores.