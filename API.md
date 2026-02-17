# Mai Journey Mobile API

Base URL (local): `http://127.0.0.1:8000/api`

## Auth

### Login
`POST /api/login`

Body:
```json
{
  "username": "admin",
  "password": "your-password"
}
```

Response:
```json
{
  "token": "plain-token",
  "token_type": "Bearer",
  "expires_at": "2026-03-19T00:00:00+00:00",
  "user": {
    "id": 1,
    "name": "Admin",
    "username": "admin"
  }
}
```

Use header for protected endpoints:
`Authorization: Bearer <token>`

### Logout
`POST /api/logout`

## Posts

### List posts
`GET /api/posts?per_page=10`

### Show single post
`GET /api/posts/{id}`

### Create post
`POST /api/posts`
```json
{
  "title": "My story",
  "content": "Long text...",
  "excerpt": "Optional short text"
}
```

### Update post
`PUT /api/posts/{id}`

### Delete post
`DELETE /api/posts/{id}`

## Gallery

### List gallery items
`GET /api/gallery?per_page=10`

### Upload gallery item
`POST /api/gallery` (multipart/form-data)
- `caption` (string, required)
- `image` (file, required)

Server behavior:
- accepts upload up to 10MB
- compresses JPG/PNG/WEBP to max ~1MB
- GIF must be <= 1MB

### Delete gallery item
`DELETE /api/gallery/{id}`
