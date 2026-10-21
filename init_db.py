"""
Initialisation de la base de données.

Crée la table `orders` et insère quatre commandes de test.
À exécuter avant de lancer le serveur, ou pour remettre les données à zéro.
"""

import sqlite3

DB_NAME = "shop.db"

COMMANDES = [
    ("CMD-001", 15000),
    ("CMD-002", 7500),
    ("CMD-003", 12000),
    ("CMD-004", 10000),
]


def init_database() -> None:
    conn = sqlite3.connect(DB_NAME)
    conn.execute("DROP TABLE IF EXISTS orders")
    conn.execute("""
        CREATE TABLE orders (
            id             TEXT PRIMARY KEY,
            amount         INTEGER NOT NULL,
            status         TEXT NOT NULL DEFAULT 'PENDING',  -- PENDING | PAID | FAILED
            transaction_id TEXT,
            updated_at     TEXT DEFAULT CURRENT_TIMESTAMP
        )
        
    """)
    conn.executemany("INSERT INTO orders (id, amount) VALUES (?, ?)", COMMANDES)
    conn.commit()
    conn.close()
    print(f"Base initialisée dans le fichier '{DB_NAME}' ({len(COMMANDES)} commandes).")


if __name__ == "__main__":
    init_database()