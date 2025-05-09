# Descripción
Proyecto de pruebas API Rest en Laravel 11

# Documentación de la API (Swagger)
http://server:port/api/documentation
- Ejemplo en desarrollo: http://127.0.0.1:8000/api/documentation

# Guía de Despliegue
Esta guía cubre los procesos de despliegue para diferentes entornos tanto en desarrollo como producción.

## Tabla de Contenidos
- [Despliegue Local](#despliegue-local)
- [Hosting Compartido/VPS](#hosting-compartidovps)
- [Servicios en la Nube](#servicios-en-la-nube)
- [Consideraciones Generales](#consideraciones-generales)

## Despliegue Local
### Desarrollo
**Clonar repositorio**
- git clone https://github.com/tu-usuario/tu-repositorio.git
- cd tu-repositorio

**Instalar dependencias (sin optimizaciones)**
- composer install

**Configurar entorno (Apartir del .env.example crear el .env)**
- cp .env.example .env
- php artisan key:generate

**Configurar base de datos local en .env**
- nano .env

**Ejecutar migraciones y seeders**
- php artisan migrate --seed

**Iniciar servidor de desarrollo**
- php artisan serve

### Producción (Local)
**Optimizar para entorno de producción**
- composer install --no-dev --optimize-autoloader
- php artisan config:cache
- php artisan route:cache
- php artisan view:cache

**Ejecutar sin debug**

**Asegúrate de APP_DEBUG=false en .env**

## Hosting Compartido/VPS
### Desarrollo
**Clonar repositorio**
- git clone -b desarrollo https://github.com/tu-usuario/tu-repositorio.git
- cd tu-repositorio

**Configurar permisos**
chmod -R 775 storage bootstrap/cache

**Configurar .env para desarrollo**

**Ejecutar migraciones específicas para desarrollo**
- php artisan migrate --seed

### Producción
**Clonar repositorio**
- git clone -b main https://github.com/tu-usuario/tu-repositorio.git
- cd tu-repositorio

**Instalar dependencias de producción**
- composer install --no-dev --optimize-autoloader --no-interaction

**Configuración de producción**
- php artisan config:cache
- php artisan route:cache
- php artisan view:cache

**Configurar permisos**
- chmod -R 755 storage bootstrap/cache

**Configurar cron jobs**
- cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1

**Configurar colas (si se usan)**
- php artisan queue:work --daemon

## Consideraciones Generales
Para todos los entornos de producción:
- Establecer APP_DEBUG=false en .env
- Configurar APP_ENV=production
- Implementar HTTPS
- Configurar backup automático de base de datos
- Monitorear logs regularmente

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
