"""Generate the third-layer item-based collaborative recommendations.

Reviews in the course prototype belong to an entire order. The script maps
that score to every menu item in the order, trains Surprise KNNBasic with
cosine item similarity, predicts unseen menu items, and stores five results
per customer in ``recommendation``.
"""

from __future__ import annotations

import sys
from collections import defaultdict
from pathlib import Path

import pandas as pd
from surprise import Dataset, KNNBasic, Reader

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))
from common.database import connect  # noqa: E402


TOP_N = 5


def main() -> None:
    connection = connect()

    ratings = pd.read_sql_query(
        """
        SELECT r.user_id, c.item_id, r.rating
        FROM review r
        JOIN `order` o
          ON o.order_id = r.order_id AND o.user_id = r.user_id
        JOIN orderitem oi
          ON oi.order_id = o.order_id
        JOIN contains c
          ON c.order_id = oi.order_id AND c.sqNo = oi.sqNo
        WHERE r.rating BETWEEN 1 AND 5
        """,
        connection,
    )

    if ratings.empty:
        connection.close()
        print("No review data found; recommendation table was not changed.")
        return

    # If an item appears in several reviewed orders for one customer, use the
    # mean score so Surprise receives one User-Item-Rating value per pair.
    ratings = ratings.groupby(["user_id", "item_id"], as_index=False)["rating"].mean()
    ratings["user_id"] = ratings["user_id"].astype(str)
    ratings["item_id"] = ratings["item_id"].astype(str)

    data = Dataset.load_from_df(
        ratings[["user_id", "item_id", "rating"]],
        Reader(rating_scale=(1, 5)),
    )
    trainset = data.build_full_trainset()
    model = KNNBasic(
        sim_options={"name": "cosine", "user_based": False},
        verbose=False,
    )
    model.fit(trainset)

    cursor = connection.cursor(dictionary=True)
    cursor.execute("SELECT user_id FROM customer")
    customer_ids = [str(row["user_id"]) for row in cursor.fetchall()]
    cursor.execute("SELECT item_id FROM menuitem")
    item_ids = [str(row["item_id"]) for row in cursor.fetchall()]

    rated_items: dict[str, set[str]] = defaultdict(set)
    for row in ratings.itertuples(index=False):
        rated_items[row.user_id].add(row.item_id)

    recommendations: list[tuple[int, int, float]] = []
    for user_id in customer_ids:
        candidates = []
        for item_id in item_ids:
            if item_id in rated_items[user_id]:
                continue
            estimate = model.predict(user_id, item_id).est
            candidates.append((item_id, estimate))

        candidates.sort(key=lambda pair: pair[1], reverse=True)
        recommendations.extend(
            (int(user_id), int(item_id), float(score))
            for item_id, score in candidates[:TOP_N]
        )

    try:
        cursor.execute("DELETE FROM recommendation")
        cursor.executemany(
            """
            INSERT INTO recommendation (user_id, item_id, score)
            VALUES (%s, %s, %s)
            """,
            recommendations,
        )
        connection.commit()
        print(f"Stored {len(recommendations)} item-based recommendations.")
    except Exception:
        connection.rollback()
        raise
    finally:
        cursor.close()
        connection.close()


if __name__ == "__main__":
    main()
