# Sistema de microservicios: gestión de productos de un minimarket

Sistema con tres componentes independientes:

| Componente | Tecnología | Puerto |
|---|---|---|
| Base de datos | MySQL 8.0 en contenedor Docker | 3306 |
| Backend (API REST) | PHP 8.4 | 8000 |
| Frontend | PHP 8.4 | 8001 |

Flujo: `Navegador -> Frontend (8001) -> API REST (8000) -> MySQL (3306)`

El frontend no se conecta a MySQL: solo consume la API por HTTP.

## ESTRUCTURA

```
minimarket/
backend/            API REST (index.php, ├──Database.php)
├── frontend/           Interfaz web (index, crear, editar, helpers)
├── database/init.sql   Script que crea la tabla Producto
├── docker-compose.yml  Contenedor MySQL
├── .env.example        Variables para Docker
└── README.md
```

## Requisitos

- Docker Desktop y Docker Compose
- PHP 8.x con la extensión `pdo_mysql` habilitada
- Git

## Variables de entorno

Copiar los archivos de ejemplo y ajustar las contraseñas:

```powershell
copy .env.example .env
copy backend\.env.example backend\.env
copy frontend\.env.example frontend\.env
```

- `.env` (raíz): credenciales de MySQL para Docker.
- `backend/.env`: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`
  (deben coincidir con los valores del `.env` de la raíz).
- `frontend/.env`: `API_URL=http://localhost:8000`

## Puesta en marcha

Abrir tres ventanas de PowerShell.

**1. Base de datos (desde la raíz del proyecto)**

```powershell
docker compose up -d
```

La primera vez se crea la base `db_minimarket_pro413` y la tabla `Producto`
con 3 productos de ejemplo. Los datos persisten en el volumen `mysql_data`.
Para reiniciar desde cero: `docker compose down -v`.

**2. Backend**

```powershell
cd backend
php -S localhost:8000 index.php
```

**3. Frontend**

```powershell
cd frontend
php -S localhost:8001
```

Abrir en el navegador: http://localhost:8001

## Endpoints de la API

| Método | Ruta | Descripción | Respuesta |
|---|---|---|---|
| GET | /productos | Lista productos activos | 200 |
| GET | /productos/{id} | Obtiene un producto activo | 200 / 404 |
| POST | /productos | Crea un producto | 201 / 422 |
| PUT | /productos/{id} | Edita un producto | 200 / 404 / 422 |
| DELETE | /productos/{id} | Eliminación lógica (`eliminado = 1`) | 200 / 404 |

Cuerpo JSON para POST y PUT:

```json
{
  "nombre": "Pan de molde",
  "proveedor": "Panificadora Sol",
  "fecha_vencimiento": "2026-11-05",
  "precio": 9.5,
  "stock": 30
}
```

## Verificar la eliminación lógica en MySQL

```powershell
docker exec -it minimarket_mysql mysql -u<usuario> -p<clave> db_minimarket_pro413 -e "SELECT * FROM Producto;"
```

Los productos eliminados siguen en la tabla con `eliminado = 1`,
pero no aparecen en el listado de la web.