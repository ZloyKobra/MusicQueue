from fastapi import FastAPI, WebSocket, WebSocketDisconnect, HTTPException, Query
from fastapi.middleware.cors import CORSMiddleware
from sqlalchemy import create_engine, text
from sqlalchemy.orm import sessionmaker
import redis.asyncio as redis
import asyncio
import json
import os
from typing import List, Dict
from datetime import datetime
from collections import defaultdict
from dotenv import load_dotenv

load_dotenv()

# Конфигурация
DATABASE_URL = os.getenv("DATABASE_URL", "mysql+pymysql://student:student@127.0.0.1:3306/musicqueue")
REDIS_URL = os.getenv("REDIS_URL", "redis://127.0.0.1:6379/0")

print(f"🔌 Connecting to Redis: {REDIS_URL}")
print(f"🗄️  Database: {DATABASE_URL}")

# Инициализация
app = FastAPI(title="Live Queue API")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# База данных
engine = create_engine(DATABASE_URL, pool_pre_ping=True, echo=False)
SessionLocal = sessionmaker(bind=engine, autoflush=False, autocommit=False)

# Redis
redis_client = redis.from_url(REDIS_URL, decode_responses=True)

# WebSocket менеджер
class ConnectionManager:
    def __init__(self):
        self.active_connections: Dict[int, List[WebSocket]] = defaultdict(list)

    async def connect(self, ws: WebSocket, playlist_id: int):
        await ws.accept()
        self.active_connections[playlist_id].append(ws)
        print(f"✅ Client connected to playlist {playlist_id}")

    def disconnect(self, ws: WebSocket, playlist_id: int):
        if ws in self.active_connections[playlist_id]:
            self.active_connections[playlist_id].remove(ws)
            print(f"❌ Client disconnected from playlist {playlist_id}")
            if not self.active_connections[playlist_id]:
                del self.active_connections[playlist_id]

    async def broadcast(self, playlist_id: int, message: dict):
        dead = []
        for ws in self.active_connections.get(playlist_id, []):
            try:
                await ws.send_json(message)
            except:
                dead.append(ws)
        for ws in dead:
            if ws in self.active_connections.get(playlist_id, []):
                self.active_connections[playlist_id].remove(ws)

manager = ConnectionManager()

# Redis subscriber
async def redis_subscriber(playlist_id: int):
    pubsub = redis_client.pubsub()
    channel = f"playlist.{playlist_id}"
    
    await pubsub.subscribe(channel)
    print(f"📡 Subscribed to {channel}")
    
    try:
        async for message in pubsub.listen():
            if message["type"] == "message":
                data = json.loads(message["data"])
                print(f"📨 Received from Redis: {data}")
                await manager.broadcast(playlist_id, data)
    finally:
        await pubsub.unsubscribe(channel)
        await pubsub.close()
        print(f"🔕 Unsubscribed from {channel}")

# REST API
@app.get("/")
async def root():
    return {"status": "ok", "service": "Live Queue API"}

@app.get("/api/playlists")
async def get_playlists(limit: int = 10, offset: int = 0):
    with SessionLocal() as session:
        result = session.execute(text("""
            SELECT p.id, p.title, p.slug, p.description, p.is_public, 
                   p.created_at, u.name as owner_name
            FROM playlists p
            JOIN users u ON u.id = p.user_id
            WHERE p.is_public = 1
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset
        """), {"limit": limit, "offset": offset})
        
        playlists = [dict(row) for row in result.mappings()]
        return {"data": playlists}

@app.get("/api/playlists/{playlist_id}/queue")
async def get_queue(playlist_id: int):
    with SessionLocal() as session:
        result = session.execute(text("""
            SELECT qi.id, qi.position, qi.status, qi.votes_up,
                   t.id as track_id, t.title, t.artist, t.track_url,
                   u.id as added_by_id, u.name as added_by_name
            FROM queue_items qi
            JOIN tracks t ON t.id = qi.track_id
            JOIN users u ON u.id = qi.added_by
            WHERE qi.playlist_id = :pid
              AND qi.status IN ('pending', 'playing')
            ORDER BY qi.votes_up DESC, qi.position ASC
        """), {"pid": playlist_id})
        
        queue = [dict(row) for row in result.mappings()]
    
    return {"playlist_id": playlist_id, "queue": queue}

@app.get("/api/tracks")
async def get_tracks(search: str = None, limit: int = 20):
    with SessionLocal() as session:
        if search:
            result = session.execute(text("""
                SELECT id, title, artist, track_url, cover_url
                FROM tracks
                WHERE title LIKE :search OR artist LIKE :search
                LIMIT :limit
            """), {"search": f"%{search}%", "limit": limit})
        else:
            result = session.execute(text("""
                SELECT id, title, artist, track_url, cover_url
                FROM tracks
                LIMIT :limit
            """), {"limit": limit})
        
        tracks = [dict(row) for row in result.mappings()]
    
    return {"tracks": tracks}

# WebSocket
@app.websocket("/ws/playlist/{playlist_id}")
async def websocket_endpoint(ws: WebSocket, playlist_id: int):
    await manager.connect(ws, playlist_id)
    
    # Запускаем Redis subscriber
    if not any(task.get_name() == f"redis-{playlist_id}" 
               for task in asyncio.all_tasks()):
        task = asyncio.create_task(
            redis_subscriber(playlist_id),
            name=f"redis-{playlist_id}"
        )
    
    try:
        while True:
            await ws.receive_text()
    except WebSocketDisconnect:
        manager.disconnect(ws, playlist_id)
    except Exception as e:
        print(f"WebSocket error: {e}")
        manager.disconnect(ws, playlist_id)

if __name__ == "__main__":
    import uvicorn
    uvicorn.run("main:app", host="127.0.0.1", port=8001, reload=True)
