"""
Simulation de l'opérateur Mobile Money.

Envoie quatre callbacks au webhook pour démontrer :
1. un callback valide,
2. un rejeu du même callback (idempotence),
3. une signature falsifiée,
4. un montant falsifié.
"""

import hashlib
import hmac
import json

import httpx

SECRET = b"secret-de-test"  # Doit être identique au secret de main.py
URL = "http://127.0.0.1:8001/api/v1/payment/callback"


def send_callback(payload: dict, secret: bytes = SECRET) -> None:
    """Sérialise le corps une seule fois, le signe, puis l'envoie tel quel."""
    body = json.dumps(payload, separators=(",", ":")).encode()
    signature = hmac.new(secret, body, hashlib.sha256).hexdigest()

    response = httpx.post(
        URL,
        content=body,
        headers={"Content-Type": "application/json", "X-Signature": signature},
    )
    try:
        data = response.json()
    except ValueError:
        data = response.text  # Affiche le message d'erreur brut du serveur
    print(response.status_code, data)


if __name__ == "__main__":
    payment = {
        "order_id": "CMD-001",
        "transaction_id": "MOMO-98765",
        "amount": 15000,
        "status": "SUCCESS",
    }


    print("1. Callback valide")
    send_callback(payment)

    print("\n2. Rejeu du même callback (idempotence)")
    send_callback(payment)

    print("\n3. Signature falsifiée")
    send_callback(payment, secret=b"mauvais-secret")

    print("\n4. Montant falsifié")
    send_callback({**payment, "order_id": "CMD-002", "amount": 1})
