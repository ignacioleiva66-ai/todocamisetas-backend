cat > README.md << 'EOF'
# TodoCamisetas — API RESTful PHP Puro

API de gestión de inventario y clientes B2B para TodoCamisetas.
PHP 8.2 puro (sin frameworks) + MySQL + Apache + Docker.

## Levantar el proyecto

```bash
docker compose up -d --build
```

- API: http://localhost:8000/api
- phpMyAdmin: http://localhost:8080
- Swagger UI: http://localhost:8081

## Endpoints

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | /api/camisetas | Listar camisetas |
| POST | /api/camisetas | Crear camiseta |
| GET | /api/camisetas/{id} | Ver camiseta |
| PUT/PATCH | /api/camisetas/{id} | Actualizar |
| DELETE | /api/camisetas/{id} | Eliminar |
| GET | /api/camisetas/{id}/precio?cliente_id= | Precio final |
| GET | /api/clientes | Listar clientes |
| POST | /api/clientes | Crear cliente |
| GET | /api/clientes/{id}/camisetas | Camisetas del cliente |
| GET | /api/tallas | Listar tallas |
