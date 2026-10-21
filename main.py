import hashlib
import hmac
import json
import os
import sqlite3
import uuid
from pathlib import Path

from fastapi import FastAPI, Header, HTTPException, Request
from fastapi.responses import FileResponse
from fastapi.staticfiles import StaticFiles
from pydantic import BaseModel

BASE_DIR = Path(__file__).resolve().parent
STATIC_DIR = BASE_DIR / "static"

app = FastAPI(title="Simulation paiement Mobile Money")
app.mount("/static", StaticFiles(directory=STATIC_DIR), name="static")

# Secret partagé entre l'opérateur et le marchand.
# En production, il doit venir d'une variable d'environnement et jamais être écrit dans le code.
WEBHOOK_SECRET = os.environ.get("MOMO_WEBHOOK_SECRET", "secret-de-test").encode()
DB_PATH = os.environ.get("DB_PATH", "shop.db")


class PaymentCallback(BaseModel):
    """Structure attendue du corps JSON envoyé par l'opérateur."""

    order_id: str
    transaction_id: str
    amount: int
    status: str  # "SUCCESS" ou "FAILED"


def get_db() -> sqlite3.Connection:
    """Ouvre une connexion. isolation_level=None : chaque requête est commitée automatiquement."""
    conn = sqlite3.connect(DB_PATH, isolation_level=None)
    conn.row_factory = sqlite3.Row
    return conn


def verify_signature(raw_body: bytes, received_signature: str) -> bool:
    """Calcule HMAC-SHA256 sur le corps brut et le compare en temps constant."""
    expected = hmac.new(WEBHOOK_SECRET, raw_body, hashlib.sha256).hexdigest()
    # Accepte le préfixe "sha256=" utilisé par certains fournisseurs.
    received = received_signature.strip().lower().removeprefix("sha256=")
    return hmac.compare_digest(expected, received)


def process_callback(raw_body: bytes, x_signature: str | None) -> dict:
    """Cœur du webhook : vérifie la signature, valide le contenu, met à jour la commande."""

    # 1. Authentifier AVANT de lire le contenu.
    if not x_signature:
        raise HTTPException(status_code=401, detail="En-tête de signature manquant")
    if not verify_signature(raw_body, x_signature):
        raise HTTPException(status_code=401, detail="Signature invalide")

    # 2. Valider la structure du JSON, seulement après authentification.
    try:
        payload = PaymentCallback.model_validate_json(raw_body)
    except ValueError as exc:
        raise HTTPException(status_code=422, detail=f"Payload invalide : {exc}")

    new_status = "PAID" if payload.status == "SUCCESS" else "FAILED"

    # 3. Mise à jour atomique : une seule instruction conditionnelle.
    #    Elle ne réussit que si la commande est encore PENDING et que le montant correspond.
    conn = get_db()
    try:
        cur = conn.execute(
            """
            UPDATE orders
               SET status = ?, transaction_id = ?, updated_at = CURRENT_TIMESTAMP
             WHERE id = ? AND status = 'PENDING' AND amount = ?
            """,
            (new_status, payload.transaction_id, payload.order_id, payload.amount),
        )

        if cur.rowcount == 1:
            return {"received": True, "order_id": payload.order_id, "status": new_status}

        # Aucune ligne modifiée : on cherche la raison pour répondre correctement.
        row = conn.execute(
            "SELECT amount, status FROM orders WHERE id = ?", (payload.order_id,)
        ).fetchone()

        if row is None:
            raise HTTPException(status_code=404, detail="Commande introuvable")
        if row["amount"] != payload.amount:
            raise HTTPException(status_code=409, detail="Montant incohérent")

        # Commande déjà traitée : 200 pour que l'opérateur arrête les relances (idempotence).
        return {
            "received": True,
            "order_id": payload.order_id,
            "status": row["status"],
            "detail": "Callback déjà traité",
        }
    finally:
        conn.close()


# ---------------------------------------------------------------------------
# Pages web
# ---------------------------------------------------------------------------

@app.get("/", include_in_schema=False)
def page_accueil():
    return FileResponse(STATIC_DIR / "index.html")


@app.get("/commandes", include_in_schema=False)
def page_commandes():
    return FileResponse(STATIC_DIR / "commandes.html")


@app.get("/commandes/{order_id}", include_in_schema=False)
def page_commande(order_id: str):
    # La page lit l'identifiant dans l'URL côté navigateur.
    return FileResponse(STATIC_DIR / "commande.html")


# ---------------------------------------------------------------------------
# API
# ---------------------------------------------------------------------------

@app.post("/api/v1/payment/callback")
async def payment_callback(
    request: Request,
    x_signature: str | None = Header(default=None),
):
    # Lire le corps BRUT : toute re-sérialisation changerait les octets signés.
    raw_body = await request.body()
    return process_callback(raw_body, x_signature)


@app.get("/api/v1/orders")
def list_orders():
    """Liste toutes les commandes, utilisée par les pages de l'interface."""
    conn = get_db()
    try:
        rows = conn.execute("SELECT * FROM orders ORDER BY id").fetchall()
    finally:
        conn.close()
    return [dict(row) for row in rows]


@app.get("/api/v1/orders/{order_id}")
def get_order(order_id: str):
    """Détail d'une commande."""
    conn = get_db()
    try:
        row = conn.execute("SELECT * FROM orders WHERE id = ?", (order_id,)).fetchone()
    finally:
        conn.close()

    if row is None:
        raise HTTPException(status_code=404, detail="Commande introuvable")
    return dict(row)


@app.post("/api/v1/demo/simulate/{order_id}", tags=["Démonstration"])
def demo_simulate(order_id: str, outcome: str = "SUCCESS"):
    """
    Simule l'opérateur depuis le serveur : construit un callback, le signe avec le
    secret, puis le fait traiter par le même code que le webhook réel.
    Route réservée à la démonstration, à supprimer en production.
    """
    if outcome not in ("SUCCESS", "FAILED"):
        raise HTTPException(status_code=400, detail="outcome doit valoir SUCCESS ou FAILED")

    conn = get_db()
    try:
        row = conn.execute("SELECT amount FROM orders WHERE id = ?", (order_id,)).fetchone()
    finally:
        conn.close()

    if row is None:
        raise HTTPException(status_code=404, detail="Commande introuvable")

    payload = {
        "order_id": order_id,
        "transaction_id": f"DEMO-{uuid.uuid4().hex[:8].upper()}",
        "amount": row["amount"],
        "status": outcome,
    }
    body = json.dumps(payload, separators=(",", ":")).encode()
    signature = hmac.new(WEBHOOK_SECRET, body, hashlib.sha256).hexdigest()
    return process_callback(body, signature)