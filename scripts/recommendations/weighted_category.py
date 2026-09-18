"""Generate the second-layer category-preference recommendations.

The score combines a customer's order frequency for a category with each
menu item's overall popularity in that category. The five highest-scoring
items for each customer are stored in ``weighted_recommendation``.
"""

from __future__ import annotations

import sys
from collections import defaultdict
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))
from common.database import connect  # noqa: E402


CATEGORY_WEIGHT_EXPONENT = 1.7
TOP_N = 5


def main() -> None:
    connection = connect()
    cursor = connection.cursor(dictionary=True)

    try:
        cursor.execute(
            """
            SELECT o.user_id, mc.category_id, SUM(oi.quantity) AS order_count
            FROM `order` o
            JOIN orderitem oi
              ON oi.order_id = o.order_id
            JOIN contains c
              ON c.order_id = oi.order_id AND c.sqNo = oi.sqNo
            JOIN menuitem_category mc
              ON mc.item_id = c.item_id
            GROUP BY o.user_id, mc.category_id
            """
        )
        user_category_counts = cursor.fetchall()

        cursor.execute(
            """
            SELECT mc.category_id, c.item_id, SUM(oi.quantity) AS popularity
            FROM orderitem oi
            JOIN contains c
              ON c.order_id = oi.order_id AND c.sqNo = oi.sqNo
            JOIN menuitem_category mc
              ON mc.item_id = c.item_id
            GROUP BY mc.category_id, c.item_id
            """
        )
        category_items = defaultdict(list)
        for row in cursor.fetchall():
            category_items[int(row["category_id"])].append(
                (int(row["item_id"]), int(row["popularity"]))
            )

        scores: dict[int, dict[int, float]] = defaultdict(dict)
        for row in user_category_counts:
            user_id = int(row["user_id"])
            category_id = int(row["category_id"])
            category_weight = float(row["order_count"]) ** CATEGORY_WEIGHT_EXPONENT

            for item_id, popularity in category_items[category_id]:
                score = category_weight * popularity
                scores[user_id][item_id] = max(
                    score,
                    scores[user_id].get(item_id, 0.0),
                )

        cursor.execute("DELETE FROM weighted_recommendation")
        insert_sql = """
            INSERT INTO weighted_recommendation (user_id, item_id, score)
            VALUES (%s, %s, %s)
        """
        row_count = 0
        for user_id, item_scores in scores.items():
            ranked_items = sorted(
                item_scores.items(), key=lambda pair: pair[1], reverse=True
            )[:TOP_N]
            cursor.executemany(
                insert_sql,
                [(user_id, item_id, score) for item_id, score in ranked_items],
            )
            row_count += len(ranked_items)

        connection.commit()
        print(f"Stored {row_count} category-weighted recommendations.")
    except Exception:
        connection.rollback()
        raise
    finally:
        cursor.close()
        connection.close()


if __name__ == "__main__":
    main()
